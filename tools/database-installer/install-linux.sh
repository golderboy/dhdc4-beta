#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

package_dir="${DHDC4_PACKAGE_DIR:-$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)}"
db_host="${DHDC4_DB_HOST:-localhost}"
db_port="${DHDC4_DB_PORT:-}"
db_socket="${DHDC4_DB_SOCKET:-}"
db_protocol="${DHDC4_DB_PROTOCOL:-auto}"
db_name="${DHDC4_DB_NAME:-dhdc4}"
root_user="${DHDC4_DB_ROOT_USER:-root}"
backup_dir="${DHDC4_DB_BACKUP_DIR:-$(dirname -- "$package_dir")/database-backups}"
dry_run=0
check_connection=0
recreate=0
confirm_recreate=0

usage() {
  printf '%s\n' 'Usage: ./install-linux.sh [--dry-run] [--check-connection] [--recreate] [--confirm-recreate]'
}

while (($#)); do
  case "$1" in
    --dry-run) dry_run=1 ;;
    --check-connection) check_connection=1 ;;
    --recreate) recreate=1 ;;
    --confirm-recreate) confirm_recreate=1 ;;
    --help|-h) usage; exit 0 ;;
    *) printf 'Unknown argument: %s\n' "$1" >&2; usage >&2; exit 2 ;;
  esac
  shift
done

[[ "$db_name" =~ ^[A-Za-z0-9_]+$ ]] || { printf '%s\n' 'Unsafe database name.' >&2; exit 1; }
case "$db_host" in localhost|127.0.0.1|::1) ;; *) printf '%s\n' "Installer must run against the local MariaDB server." >&2; exit 1 ;; esac
case "$db_protocol" in auto|socket|tcp) ;; *) printf '%s\n' 'DHDC4_DB_PROTOCOL must be auto, socket, or tcp.' >&2; exit 1 ;; esac
if [[ -n "$db_port" ]]; then
  [[ "$db_port" =~ ^[0-9]+$ ]] && ((db_port >= 1 && db_port <= 65535)) \
    || { printf '%s\n' 'DHDC4_DB_PORT must be an integer from 1 to 65535.' >&2; exit 1; }
fi
if [[ -n "$db_socket" && "$db_socket" != /* ]]; then
  printf '%s\n' 'DHDC4_DB_SOCKET must be an absolute path.' >&2
  exit 1
fi
if [[ "$db_protocol" == tcp && -z "$db_port" ]]; then
  printf '%s\n' 'DHDC4_DB_PORT is required when DHDC4_DB_PROTOCOL=tcp.' >&2
  exit 1
fi

for required in manifest.json SHA256SUMS install-order.txt admin/create-owner-and-grants.sql admin/verify-install.sql; do
  [[ -f "$package_dir/$required" ]] || { printf 'Missing package file: %s\n' "$required" >&2; exit 1; }
done
command -v mariadb >/dev/null || { printf '%s\n' 'mariadb client is required.' >&2; exit 1; }
command -v mariadb-dump >/dev/null || { printf '%s\n' 'mariadb-dump is required.' >&2; exit 1; }
command -v sha256sum >/dev/null || { printf '%s\n' 'sha256sum is required.' >&2; exit 1; }
command -v tee >/dev/null || { printf '%s\n' 'tee is required for the install log.' >&2; exit 1; }
command -v php >/dev/null || { printf '%s\n' 'PHP CLI is required.' >&2; exit 1; }
apache_command=''
for candidate in httpd apache2 apachectl; do
  if command -v "$candidate" >/dev/null; then
    apache_command=$(command -v "$candidate")
    break
  fi
done
[[ -n "$apache_command" ]] || { printf '%s\n' 'Apache HTTP Server is required.' >&2; exit 1; }

log_dir="${DHDC4_INSTALL_LOG_DIR:-$(dirname -- "$package_dir")/install-logs}"
if ! install -d -m 0700 -- "$log_dir"; then
  case "$(uname -s)" in
    MINGW*|MSYS*) mkdir -p -- "$log_dir" ;;
    *) printf 'Unable to create secure install log directory: %s\n' "$log_dir" >&2; exit 1 ;;
  esac
fi
log_file="$log_dir/dhdc4-database-install-$(date +%Y%m%d-%H%M%S).log"
exec > >(tee -a "$log_file") 2>&1
printf 'Install log: %s\n' "$log_file"

(cd -- "$package_dir" && sha256sum --check --strict SHA256SUMS)
mapfile -t sql_parts < <(sed '/^[[:space:]]*$/d' "$package_dir/install-order.txt")
((${#sql_parts[@]} > 0)) || { printf '%s\n' 'install-order.txt is empty.' >&2; exit 1; }
for relative in "${sql_parts[@]}"; do
  [[ "$relative" =~ ^sql/[A-Za-z0-9._-]+\.sql$ ]] || { printf 'Unsafe SQL part: %s\n' "$relative" >&2; exit 1; }
  [[ -f "$package_dir/$relative" ]] || { printf 'Missing SQL part: %s\n' "$relative" >&2; exit 1; }
done

available_kib=$(df -Pk -- "$package_dir" | awk 'NR==2 {print $4}')
package_kib=$(du -sk -- "$package_dir" | awk '{print $1}')
((available_kib >= package_kib * 3)) || { printf '%s\n' 'Free disk space is below three times the package size.' >&2; exit 1; }

mariadb --version
php -r '$required=["curl","fileinfo","gd","intl","mbstring","openssl","pdo_mysql","zip"];$missing=array_values(array_filter($required,static fn(string $name):bool=>!extension_loaded($name)));if(version_compare(PHP_VERSION,"8.1.0","<")||$missing){fwrite(STDERR,"PHP preflight failed: version=".PHP_VERSION." missing=".implode(",",$missing).PHP_EOL);exit(1);}echo "PHP preflight: version=".PHP_VERSION." extensions=PASS".PHP_EOL;'
"$apache_command" -v
printf 'Package verification passed: %d SQL parts, database=%s, owner=dhdc4@localhost\n' "${#sql_parts[@]}" "$db_name"
if ((dry_run)); then
  printf 'Connection configuration: protocol=%s host=%s port=%s socket=%s\n' \
    "$db_protocol" "$db_host" "${db_port:-auto-detect}" "${db_socket:-auto-detect}"
  printf '%s\n' 'Dry-run completed. No database connection, port detection, or mutation was attempted.'
  exit 0
fi

if [[ -v DHDC4_DB_ROOT_PASSWORD ]]; then
  root_password="$DHDC4_DB_ROOT_PASSWORD"
else
  read -r -s -p "MariaDB administrator password for '$root_user' (press Enter for Unix socket/passwordless authentication): " root_password
  printf '\n'
fi

client_common_args=(--user="$root_user" --default-character-set=utf8mb4 --binary-mode --batch --raw --skip-column-names --max-allowed-packet=1G)
connection_probe_output=''
connection_probe_error=''
run_with_root_password() {
  if [[ -n "$root_password" ]]; then
    MYSQL_PWD="$root_password" "$@"
  else
    env -u MYSQL_PWD "$@"
  fi
}
probe_root_transport() {
  local output
  if output=$(run_with_root_password mariadb "$@" "${client_common_args[@]}" \
    --execute="SELECT CONCAT('DHDC4_PORT=', @@port); SELECT CONCAT('DHDC4_SOCKET=', @@socket);" 2>&1); then
    connection_probe_output="$output"
    return 0
  fi
  connection_probe_error="$output"
  return 1
}

selected_protocol=''
transport_args=()
if [[ "$db_protocol" == auto || "$db_protocol" == socket ]]; then
  socket_args=(--protocol=socket --skip-ssl)
  [[ -z "$db_socket" ]] || socket_args+=(--socket="$db_socket")
  if probe_root_transport "${socket_args[@]}"; then
    selected_protocol='socket'
    transport_args=("${socket_args[@]}")
  elif [[ "$db_protocol" == socket ]]; then
    printf 'Unable to connect to MariaDB through Unix socket.\n%s\n' "$connection_probe_error" >&2
    exit 1
  fi
fi
if [[ -z "$selected_protocol" && ( "$db_protocol" == auto || "$db_protocol" == tcp ) ]]; then
  if [[ -z "$db_port" ]]; then
    printf '%s\n' 'Unix socket detection failed and no TCP port was supplied.' >&2
    printf '%s\n' 'Set DHDC4_DB_SOCKET=/absolute/path/to/mariadb.sock, or set DHDC4_DB_PROTOCOL=tcp and DHDC4_DB_PORT=<actual-port>.' >&2
    [[ -z "$connection_probe_error" ]] || printf 'Socket connection error:\n%s\n' "$connection_probe_error" >&2
    exit 1
  fi
  tcp_args=(--protocol=tcp --host="$db_host" --port="$db_port")
  if probe_root_transport "${tcp_args[@]}"; then
    selected_protocol='tcp'
    transport_args=("${tcp_args[@]}")
  else
    printf 'Unable to connect to MariaDB through TCP at %s:%s.\n%s\n' "$db_host" "$db_port" "$connection_probe_error" >&2
    exit 1
  fi
fi

detected_port=$(grep '^DHDC4_PORT=' <<<"$connection_probe_output" | tail -n 1 | cut -d= -f2-)
detected_socket=$(grep '^DHDC4_SOCKET=' <<<"$connection_probe_output" | tail -n 1 | cut -d= -f2-)
[[ "$detected_port" =~ ^[0-9]+$ ]] && ((detected_port >= 1 && detected_port <= 65535)) \
  || { printf 'MariaDB returned an invalid port: %s\n' "$detected_port" >&2; exit 1; }
if [[ -n "$db_port" && "$db_port" != "$detected_port" ]]; then
  printf 'Configured DHDC4_DB_PORT=%s does not match the running MariaDB port %s. Nothing was changed.\n' \
    "$db_port" "$detected_port" >&2
  exit 1
fi
db_port="$detected_port"
connection_args=("${transport_args[@]}" "${client_common_args[@]}")
printf 'MariaDB connection verified: protocol=%s host=%s port=%s socket=%s administrator=%s\n' \
  "$selected_protocol" "$db_host" "$db_port" "${detected_socket:-not-reported}" "$root_user"
printf "Use DHDC_DB_PORT='%s' in /etc/dhdc4/dhdc4.env.\n" "$db_port"

run_root_sql() {
  local sql="$1"
  run_with_root_password mariadb "${connection_args[@]}" <<<"$sql"
}

database_exists=$(run_root_sql "SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='$db_name';")
server_settings=$(run_root_sql "SELECT CONCAT(@@GLOBAL.local_infile, '|', @@GLOBAL.event_scheduler);")
[[ "$server_settings" == 1\|* || "$server_settings" == ON\|* ]] || { printf '%s\n' 'MariaDB local_infile must be enabled before installing DHDC4.' >&2; exit 1; }
if ((check_connection)); then
  printf 'Connection check passed: database_exists=%s local_infile=ON event_scheduler=%s. No database mutation was attempted.\n' \
    "$database_exists" "${server_settings#*|}"
  exit 0
fi

if ((database_exists > 0)); then
  ((recreate)) || { printf "Database '%s' already exists. Nothing was changed. Use --recreate to back it up and reinstall.\n" "$db_name" >&2; exit 1; }
  if ((!confirm_recreate)); then
    read -r -p "Type RECREATE-$db_name to back up and replace the database: " confirmation
    [[ "$confirmation" == "RECREATE-$db_name" ]] || { printf '%s\n' 'Confirmation did not match. Nothing was changed.' >&2; exit 1; }
  fi
fi

if [[ -v DHDC4_DB_OWNER_PASSWORD ]]; then
  owner_password="$DHDC4_DB_OWNER_PASSWORD"
else
  read -r -s -p "Password for 'dhdc4'@'localhost': " owner_password
  printf '\n'
fi
((${#owner_password} >= 32)) || { printf '%s\n' 'Password for dhdc4@localhost must contain at least 32 characters.' >&2; exit 1; }

run_root_sql 'SET GLOBAL event_scheduler=OFF;' >/dev/null
printf 'MariaDB preflight: local_infile=ON, event_scheduler changed from %s to OFF\n' "${server_settings#*|}"

if ((database_exists > 0)); then
  install -d -m 0700 -- "$backup_dir"
  backup_file="$backup_dir/${db_name}-before-recreate-$(date +%Y%m%d-%H%M%S).sql"
  run_with_root_password mariadb-dump "${transport_args[@]}" --user="$root_user" \
    --default-character-set=utf8mb4 --hex-blob --routines --events --triggers --databases "$db_name" \
    --result-file="$backup_file"
  [[ -s "$backup_file" ]] || { printf '%s\n' 'Pre-recreate backup was not created.' >&2; exit 1; }
  (cd -- "$backup_dir" && sha256sum "$(basename -- "$backup_file")" >"$(basename -- "$backup_file").sha256")
  run_root_sql "DROP DATABASE \`$db_name\`;" >/dev/null
  printf 'Existing database backup: %s\n' "$backup_file"
fi

owner_hex=$(printf '%s' "$owner_password" | od -An -tx1 | tr -d '[:space:]')
{
  printf 'SET @dhdc4_owner_password=CONVERT(0x%s USING utf8mb4);\n' "$owner_hex"
  cat -- "$package_dir/admin/create-owner-and-grants.sql"
} | run_with_root_password mariadb "${connection_args[@]}"

printf '%s\n' 'Importing SQL parts in one MariaDB session...'
{
  for relative in "${sql_parts[@]}"; do
    cat -- "$package_dir/$relative"
    printf '\n'
  done
} | run_with_root_password mariadb "${connection_args[@]}"

verification=$(run_with_root_password mariadb "${connection_args[@]}" --database="$db_name" <"$package_dir/admin/verify-install.sql")
verification_line=$(grep '^DHDC4_VERIFY[[:space:]]' <<<"$verification" | tail -n 1 || true)
[[ "$verification_line" =~ ^DHDC4_VERIFY[[:space:]]+PASS[[:space:]] ]] || { printf 'Database verification failed:\n%s\n' "$verification" >&2; exit 1; }

owner_test=$(MYSQL_PWD="$owner_password" mariadb "${transport_args[@]}" --user=dhdc4 \
  --database="$db_name" --default-character-set=utf8mb4 --batch --raw --skip-column-names \
  <<<'SELECT CURRENT_USER(); SELECT AddZero("7",3); CALL z_update_definer();')
grep -q 'dhdc4@localhost' <<<"$owner_test"
grep -q '^007$' <<<"$owner_test"
grep -q 'Legacy direct mysql.proc updates are disabled' <<<"$owner_test"

printf '%s\n' "$verification_line"
printf 'DHDC4 database installation completed successfully on %s:%s via %s.\n' "$db_host" "$db_port" "$selected_protocol"
printf '%s\n' 'MariaDB event_scheduler remains OFF; enable it only after validating event_dhdc and server timezone.'
unset root_password owner_password DHDC4_DB_ROOT_PASSWORD DHDC4_DB_OWNER_PASSWORD
