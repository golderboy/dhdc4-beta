# ติดตั้ง DHDC4 บน Linux Apache

คู่มือนี้ใช้กับ AlmaLinux 9 หรือระบบตระกูล RHEL ที่ใช้ Apache (`httpd`) และ PHP-FPM คำสั่งบางชื่ออาจต่างกันใน Ubuntu/Debian จึงไม่ควรคัดลอกคำสั่ง `dnf` ไปใช้ข้ามตระกูล Linux

## 1. เตรียมเครื่อง

ต้องติดตั้ง Apache, PHP CLI/FPM 8.1 ขึ้นไป, MariaDB Server/client 12.2 หรือรุ่นที่ผ่านการทดสอบ และคำสั่ง `sha256sum` ให้เรียบร้อย PHP ต้องมี extension ต่อไปนี้:

```text
curl fileinfo gd intl mbstring openssl pdo_mysql zip
```

ตัวอย่างติดตั้งส่วนประกอบบน AlmaLinux 9 หลังตั้ง repository ของ PHP และ MariaDB รุ่นที่ต้องการแล้ว:

```bash
sudo dnf install -y httpd php php-cli php-fpm php-mysqlnd php-mbstring php-intl php-gd php-curl php-xml php-zip policycoreutils-python-utils
sudo systemctl enable --now mariadb php-fpm
mariadb --version
php -v
php -m
```

อย่าเริ่ม Apache จนกว่าจะติดตั้งและตรวจระบบเสร็จ

## 2. ตั้งค่า MariaDB ก่อนนำเข้าฐาน

สร้างไฟล์ `/etc/my.cnf.d/dhdc4.cnf`:

```ini
[mariadb]
character-set-server=utf8mb3
collation-server=utf8mb3_general_ci
character-set-collations=utf8mb3=utf8mb3_general_ci
local-infile=1
event-scheduler=OFF
```

restart และตรวจค่า:

```bash
sudo systemctl restart mariadb
sudo mariadb -e "SELECT @@version, @@global.local_infile, @@global.event_scheduler;"
```

ค่า `local_infile` ต้องเป็น `1` และ `event_scheduler` ต้องเป็น `OFF`

## 3. วางและตรวจชุดติดตั้งฐานข้อมูล

วาง ZIP ไว้นอก `/var/www` ตัวอย่าง `/opt/dhdc4-install`:

```bash
sudo install -d -m 0700 /opt/dhdc4-install
cd /opt/dhdc4-install
sha256sum -c dhdc4-database-installer-v4.0.1.zip.sha256
unzip dhdc4-database-installer-v4.0.1.zip
cd dhdc4-database-installer-v4.0.1
chmod 700 install-linux.sh
./install-linux.sh --dry-run
```

ต้องจบด้วย `Dry-run completed` หากไม่ผ่าน ให้แก้ตามข้อความก่อนทำขั้นต่อไป

## 4. ติดตั้งฐานข้อมูลและสร้าง MariaDB user

ถ้า MariaDB `root` ใช้ socket authentication ให้เข้าระบบเป็น root ก่อนแล้วรันสคริปต์:

```bash
sudo -i
cd /opt/dhdc4-install/dhdc4-database-installer-v4.0.1
./install-linux.sh
```

เมื่อถามรหัสผ่าน MariaDB root ให้กด Enter หากเครื่องใช้ socket authentication จากนั้นตั้งรหัสผ่านใหม่สำหรับ `'dhdc4'@'localhost'` อย่างน้อย 32 ตัวอักษรและเก็บใน password manager

ตัวติดตั้งจะทำ `CREATE USER`, `ALTER USER` และ `GRANT` ให้อัตโนมัติ เมื่อติดตั้งเสร็จต้องเห็น `DHDC4_VERIFY PASS`

หากต้องติดตั้งทับฐานเดิม สคริปต์จะ backup ก่อนลบฐานและขอคำยืนยัน:

```bash
sudo systemctl stop httpd
DHDC4_DB_BACKUP_DIR=/var/backups/dhdc4 ./install-linux.sh --recreate
```

### คำสั่ง MariaDB แบบทำเอง

ใช้เฉพาะเมื่อตัวติดตั้งสร้าง user ไม่สำเร็จ:

```bash
sudo mariadb
```

พิมพ์ SQL ด้านล่าง โดยเปลี่ยน `รหัสผ่านยาวอย่างน้อย32ตัว` เป็นรหัสจริง:

```sql
CREATE USER IF NOT EXISTS 'dhdc4'@'localhost' IDENTIFIED BY 'รหัสผ่านยาวอย่างน้อย32ตัว';
ALTER USER 'dhdc4'@'localhost' IDENTIFIED BY 'รหัสผ่านยาวอย่างน้อย32ตัว';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, REFERENCES, INDEX, ALTER,
  CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW, SHOW VIEW,
  CREATE ROUTINE, ALTER ROUTINE, EVENT, TRIGGER, SHOW CREATE ROUTINE
  ON `dhdc4`.* TO 'dhdc4'@'localhost';
SHOW GRANTS FOR 'dhdc4'@'localhost';
EXIT;
```

บัญชีนี้ต้องไม่มีสิทธิ global และไม่มี `GRANT OPTION`

## 5. วางโปรแกรมและกำหนดสิทธิไฟล์

แตก application release ไปที่ `/var/www/dhdc4` แล้วกำหนดสิทธิ:

```bash
sudo chown -R root:apache /var/www/dhdc4
sudo find /var/www/dhdc4 -type d -exec chmod 0750 {} \;
sudo find /var/www/dhdc4 -type f -exec chmod 0640 {} \;
sudo chown -R apache:apache /var/www/dhdc4/frontend/runtime /var/www/dhdc4/backend/runtime /var/www/dhdc4/console/runtime /var/www/dhdc4/frontend/web/assets /var/www/dhdc4/backend/web/assets
```

ถ้าโฟลเดอร์ runtime หรือ assets ยังไม่มี ให้รัน Production initializer ในข้อ 6 ก่อน แล้วกลับมารันคำสั่ง `chown` บรรทัดสุดท้ายอีกครั้ง

## 6. ตั้งค่า Yii2 และ PHP-FPM

สร้างโฟลเดอร์สำหรับค่า secret:

```bash
sudo install -d -o root -g apache -m 0750 /etc/dhdc4
sudo install -o root -g apache -m 0640 /dev/null /etc/dhdc4/dhdc4.env
sudoedit /etc/dhdc4/dhdc4.env
```

ใส่ค่าต่อไปนี้โดยแทนข้อความตัวอย่างด้วยค่าจริง ห้ามนำไฟล์นี้เข้า Git:

```bash
DHDC_DB_DSN='mysql:host=localhost;dbname=dhdc4;port=3306'
DHDC_DB_HOST='localhost'
DHDC_DB_PORT='3306'
DHDC_DB_NAME='dhdc4'
DHDC_DB_USER='dhdc4'
DHDC_DB_PASSWORD='ใส่รหัสผ่านฐานข้อมูลจริง'
DHDC_MAILER_DSN='ใส่ DSN ของ SMTP จริง'
```

โหลดตัวแปรและตั้งค่าโปรแกรม:

```bash
set -a
source /etc/dhdc4/dhdc4.env
set +a
cd /var/www/dhdc4
php tools/configure-database.php
php init --env=Production --overwrite=All
composer install --no-dev --classmap-authoritative --no-interaction
php requirements.php
php yii help
```

เพื่อให้ PHP-FPM อ่านค่า environment ให้สร้าง systemd drop-in:

```bash
sudo systemctl edit php-fpm
```

ใส่ข้อความนี้แล้วบันทึก:

```ini
[Service]
EnvironmentFile=/etc/dhdc4/dhdc4.env
```

สร้างไฟล์ `/etc/php-fpm.d/dhdc4-env.conf`:

```ini
[www]
env[DHDC_DB_DSN] = $DHDC_DB_DSN
env[DHDC_DB_USER] = $DHDC_DB_USER
env[DHDC_DB_PASSWORD] = $DHDC_DB_PASSWORD
env[DHDC_MAILER_DSN] = $DHDC_MAILER_DSN
```

จากนั้น reload service:

```bash
sudo systemctl daemon-reload
sudo systemctl restart php-fpm
sudo systemctl status php-fpm --no-pager
```

## 7. สร้าง Admin ตั้งต้น

ยังอยู่ใน shell ที่โหลด `/etc/dhdc4/dhdc4.env` แล้ว ให้กำหนดอีเมลจริงและทดสอบ:

```bash
export DHDC_BOOTSTRAP_ADMIN_EMAIL='admin@your-organization.example'
php tools/bootstrap-admin.php --use-default-credentials --dry-run --confirm=CREATE-INITIAL-ADMIN
```

ถ้าขึ้น `Initial Admin dry-run passed` ให้สร้างจริง:

```bash
php tools/bootstrap-admin.php --use-default-credentials --confirm=CREATE-INITIAL-ADMIN
unset DHDC_BOOTSTRAP_ADMIN_EMAIL
```

ข้อมูลเข้าสู่ระบบครั้งแรกคือ:

```text
Username: admin
Password: P@ssw0rd
```

เข้าสู่ระบบแล้วเปิด `/user/settings/account` เพื่อเปลี่ยนรหัสผ่านทันที ต้องเปลี่ยนก่อนเปิด Firewall หรือให้เครื่องอื่นเข้าใช้งาน

## 8. ตั้ง Apache และ SELinux

VirtualHost frontend ต้องชี้เฉพาะ `/var/www/dhdc4/frontend/web` และ backend ต้องชี้เฉพาะ `/var/www/dhdc4/backend/web` ห้ามชี้ไปที่ `/var/www/dhdc4`

```apache
DocumentRoot /var/www/dhdc4/frontend/web
<Directory /var/www/dhdc4/frontend/web>
    AllowOverride All
    Options -Indexes +FollowSymLinks
    Require all granted
</Directory>
```

กำหนด SELinux ให้เขียนได้เฉพาะ runtime และ assets:

```bash
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/dhdc4/(frontend|backend|console)/runtime(/.*)?'
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/dhdc4/(frontend|backend)/web/assets(/.*)?'
sudo restorecon -Rv /var/www/dhdc4
sudo setsebool -P httpd_can_network_connect_db 1
sudo apachectl configtest
sudo systemctl enable --now httpd
```

## 9. ตรวจรับก่อนเปิดระบบ

ตรวจให้ครบทุกข้อ:

- ตัวติดตั้งฐานข้อมูลรายงาน `DHDC4_VERIFY PASS`
- `mariadb-check --all-databases --quick` ไม่พบ error
- frontend และ backend เปิดได้ และไม่แสดง stack trace
- ล็อกอิน `admin` ได้ และเปลี่ยนรหัสผ่านตั้งต้นแล้ว
- `php yii migrate/new` ไม่พบ migration ค้าง
- ไม่มี SQL, backup, log, `.env` หรือ config ถูกเปิดผ่าน URL
- Apache ใช้ HTTPS, redirect HTTP ไป HTTPS และส่ง HSTS หลังตรวจ certificate จริงแล้ว
- MariaDB `event_scheduler` ยังเป็น `OFF` จนกว่าจะตรวจ timezone และ `event_dhdc`
- ตั้ง backup schedule, log rotation และ monitoring แล้ว

หากข้อใดไม่ผ่าน ให้หยุด Apache ด้วย `sudo systemctl stop httpd` และแก้ให้ผ่านก่อนเปิดใช้งานจริง
