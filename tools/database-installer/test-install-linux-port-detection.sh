#!/usr/bin/env bash
set -Eeuo pipefail

script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
installer="$script_dir/install-linux.sh"
test_root=$(mktemp -d)
cleanup() {
  case "$test_root" in
    /tmp/tmp.*) rm -rf -- "$test_root" ;;
    *) printf 'Refusing to remove unexpected test directory: %s\n' "$test_root" >&2 ;;
  esac
}
trap cleanup EXIT

package_dir="$test_root/package"
fake_bin="$test_root/bin"
mkdir -p "$package_dir/admin" "$package_dir/sql" "$fake_bin"
printf '{}\n' >"$package_dir/manifest.json"
printf 'sql/00-test.sql\n' >"$package_dir/install-order.txt"
printf 'SELECT 1;\n' >"$package_dir/sql/00-test.sql"
printf 'SELECT 1;\n' >"$package_dir/admin/create-owner-and-grants.sql"
printf '%s\n' '--VERIFY_INSTALL' 'SELECT 1;' >"$package_dir/admin/verify-install.sql"
(cd -- "$package_dir" && sha256sum manifest.json >SHA256SUMS)

cat >"$fake_bin/php" <<'EOF'
#!/usr/bin/env bash
printf '%s\n' 'PHP preflight: version=8.2.0 extensions=PASS'
EOF
cat >"$fake_bin/httpd" <<'EOF'
#!/usr/bin/env bash
printf '%s\n' 'Server version: Apache/2.4 test'
EOF
cat >"$fake_bin/mariadb-dump" <<'EOF'
#!/usr/bin/env bash
exit 0
EOF
cat >"$fake_bin/mariadb" <<'EOF'
#!/usr/bin/env bash
printf '%s\n' "$*" >>"$DHDC4_TEST_CLIENT_LOG"
if [[ -v MYSQL_PWD ]]; then
  printf '%s\n' 'MYSQL_PWD_WAS_SET' >>"$DHDC4_TEST_CLIENT_LOG"
fi
if [[ " $* " == *' --version '* ]]; then
  printf '%s\n' 'mariadb Ver 15.1 Distrib 12.2.2-MariaDB'
  exit 0
fi
if [[ "$*" == *'DHDC4_PORT='* ]]; then
  printf '%s\n' 'DHDC4_PORT=3407' 'DHDC4_SOCKET=/run/mariadb/custom.sock'
  exit 0
fi
input=$(cat)
if [[ " $* " == *' --user=dhdc4 '* ]]; then
  printf '%s\n' 'dhdc4@localhost' '007' 'Legacy direct mysql.proc updates are disabled'
  exit 0
fi
case "$input" in
  *--VERIFY_INSTALL*) printf '%s\n' 'DHDC4_VERIFY PASS 821 512 560 0 43 0' ;;
  *information_schema.SCHEMATA*) printf '%s\n' '0' ;;
  *@@GLOBAL.local_infile*) printf '%s\n' '1|OFF' ;;
  *) printf '%s\n' '0' ;;
esac
EOF
chmod 700 "$fake_bin/php" "$fake_bin/httpd" "$fake_bin/mariadb" "$fake_bin/mariadb-dump"

export PATH="$fake_bin:$PATH"
export DHDC4_PACKAGE_DIR="$package_dir"
export DHDC4_INSTALL_LOG_DIR="$test_root/logs"
export DHDC4_DB_ROOT_PASSWORD=''
export DHDC4_TEST_CLIENT_LOG="$test_root/client.log"

output=$("$installer" --check-connection)
grep -q 'MariaDB connection verified: protocol=socket host=localhost port=3407' <<<"$output"
grep -q "Use DHDC_DB_PORT='3407'" <<<"$output"
grep -q 'Connection check passed:' <<<"$output"
grep -q -- '--protocol=socket' "$DHDC4_TEST_CLIENT_LOG"
if grep -q 'MYSQL_PWD_WAS_SET' "$DHDC4_TEST_CLIENT_LOG"; then
  printf '%s\n' 'Empty MYSQL_PWD was exported during passwordless socket authentication.' >&2
  exit 1
fi

if DHDC4_DB_PORT=3306 "$installer" --check-connection >"$test_root/mismatch.log" 2>&1; then
  printf '%s\n' 'Expected configured/detected port mismatch to fail.' >&2
  exit 1
fi
grep -q 'does not match the running MariaDB port 3407' "$test_root/mismatch.log"

if env -u DHDC4_DB_PORT DHDC4_DB_PROTOCOL=tcp "$installer" --check-connection >"$test_root/tcp-missing.log" 2>&1; then
  printf '%s\n' 'Expected TCP mode without a port to fail.' >&2
  exit 1
fi
grep -q 'DHDC4_DB_PORT is required' "$test_root/tcp-missing.log"

: >"$DHDC4_TEST_CLIENT_LOG"
output=$(DHDC4_DB_SOCKET=/run/mariadb/custom.sock "$installer" --check-connection)
grep -q 'protocol=socket' <<<"$output"
grep -q -- '--socket=/run/mariadb/custom.sock' "$DHDC4_TEST_CLIENT_LOG"

: >"$DHDC4_TEST_CLIENT_LOG"
output=$(DHDC4_DB_PROTOCOL=tcp DHDC4_DB_HOST=127.0.0.1 DHDC4_DB_PORT=3407 "$installer" --check-connection)
grep -q 'protocol=tcp host=127.0.0.1 port=3407' <<<"$output"
grep -q -- '--protocol=tcp --host=127.0.0.1 --port=3407' "$DHDC4_TEST_CLIENT_LOG"

: >"$DHDC4_TEST_CLIENT_LOG"
output=$(DHDC4_DB_OWNER_PASSWORD='0123456789abcdefghijklmnopqrstuv' "$installer")
grep -q 'DHDC4_VERIFY PASS 821 512 560 0 43 0' <<<"$output"
grep -q 'completed successfully on localhost:3407 via socket' <<<"$output"
grep -q -- '--protocol=socket' "$DHDC4_TEST_CLIENT_LOG"

printf '%s\n' 'INSTALL_LINUX_PORT_DETECTION_TEST PASS'
