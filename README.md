# DHDC4

## ติดตั้งแบบย่อที่สุด

สรุปสำหรับผู้ที่เคยติดตั้งระบบบน Linux แล้ว:

1. ตรวจว่า PHP 8.2, MariaDB, Apache, PHP-FPM, Composer และ Git พร้อมใช้งาน
2. ดาวน์โหลด DHDC4 และเตรียมโปรแกรม:

```bash
sudo git clone --branch v4.0.2 --depth 1 https://github.com/golderboy/dhdc4-beta.git /var/www/dhdc4
sudo chown -R "$(id -un):$(id -gn)" /var/www/dhdc4
cd /var/www/dhdc4
php init --env=Production --overwrite=All
composer install --no-dev --classmap-authoritative --no-interaction
```

3. ดาวน์โหลดชุดฐานข้อมูล `v4.0.2` จากหน้า [GitHub Release](https://github.com/golderboy/dhdc4-beta/releases/tag/v4.0.2) แตกไฟล์ แล้วรัน:

```bash
./install-linux.sh --dry-run
./install-linux.sh --check-connection
export DHDC4_DB_OWNER_PASSWORD="$(php -r 'echo bin2hex(random_bytes(16));')"
printf 'รหัสผ่าน dhdc4@localhost: %s\n' "$DHDC4_DB_OWNER_PASSWORD"
./install-linux.sh
unset DHDC4_DB_OWNER_PASSWORD
```

4. ใส่พอร์ตและรหัสผ่านจริงใน `/etc/dhdc4/dhdc4.env`
5. รัน `php tools/configure-database.php`, `php yii migrate/up --interactive=0` และสร้างผู้ดูแลระบบ
6. ตั้งสิทธิ์โฟลเดอร์ ตั้ง Apache แล้วตรวจให้พบ `DHDC4_VERIFY PASS` ก่อนเปิดใช้งาน

รายละเอียดและคำอธิบายทุกขั้นตอนอยู่ด้านล่าง

## 1. รู้จัก DHDC4

DHDC4 เป็นระบบสำหรับนำเข้า ตรวจสอบ และแสดงรายงานข้อมูลสุขภาพ 43 แฟ้ม คู่มือนี้อธิบายการติดตั้ง DHDC4 รุ่น `v4.0.2` บน Linux ที่ใช้ Apache และ PHP-FPM

ตัวอย่างคำสั่งเฉพาะระบบในคู่มือนี้ใช้ Debian หรือ Ubuntu หากเครื่องใช้ Linux แบบอื่น ชื่อบริการ ผู้ใช้ของ Apache และตำแหน่งไฟล์ตั้งค่าอาจต่างกัน

> คู่มือนี้ไม่สอนติดตั้ง PHP, MariaDB, Apache หรือ Composer หากตรวจแล้วพบว่ายังไม่พร้อม ให้ติดตั้งส่วนที่ขาดหรือติดต่อผู้ดูแลเครื่องก่อนทำขั้นตอนต่อไป

## 2. สิ่งที่ต้องเตรียม

เครื่องต้องมีรายการต่อไปนี้พร้อมใช้งาน:

- Linux พร้อมสิทธิ์ `sudo`
- Apache และ PHP-FPM
- PHP 8.2 ขึ้นไป
- MariaDB Server และโปรแกรม `mariadb`
- Composer 2
- Git, cURL, Unzip และ `sha256sum`
- ชื่อเว็บไซต์จริง 2 ชื่อ สำหรับหน้าใช้งานและหน้าผู้ดูแล
- ใบรับรอง HTTPS สำหรับชื่อเว็บไซต์ทั้ง 2 ชื่อ
- อีเมลและรหัสผ่านใหม่สำหรับผู้ดูแลระบบคนแรก

ชุดฐานข้อมูลรุ่นนี้ผ่านการทดสอบกับ MariaDB 12.2.2 หากใช้รุ่นอื่น ควรทดลองติดตั้งในเครื่องทดสอบก่อนเปิดใช้งานจริง

## 3. ตรวจว่า Linux พร้อมหรือไม่

### 3.1 ตรวจโปรแกรมพื้นฐาน

คำสั่งต่อไปนี้ใช้ดูเวอร์ชันเท่านั้น และไม่เปลี่ยนแปลงเครื่อง:

```bash
git --version
composer --version
php -v
mariadb --version
apache2ctl -v
curl --version
unzip -v | head -n 1
sha256sum --version | head -n 1
```

PHP ต้องเป็นรุ่น 8.2 ขึ้นไป ตรวจส่วนเสริมที่ DHDC4 ใช้ด้วยคำสั่งนี้:

```bash
php -r '$required=["curl","dom","fileinfo","gd","intl","mbstring","openssl","pdo_mysql","simplexml","xml","xmlreader","xmlwriter","zip"];$missing=array_values(array_filter($required,static fn($name)=>!extension_loaded($name)));echo $missing ? "FAIL: ขาด ".implode(", ",$missing).PHP_EOL : "PASS: PHP พร้อมใช้งาน".PHP_EOL;exit($missing?1:0);'
```

ผลที่พร้อมใช้งานต้องขึ้นต้นด้วย `PASS` หากขึ้นต้นด้วย `FAIL` ให้ติดตั้งส่วนเสริมที่แสดงก่อน

### 3.2 ตรวจบริการที่กำลังทำงาน

ตัวอย่างนี้ใช้ชื่อบริการของ Debian หรือ Ubuntu:

```bash
systemctl is-active apache2
systemctl is-active mariadb
systemctl list-units 'php*-fpm.service' --state=running
```

Apache และ MariaDB ต้องแสดง `active` และต้องพบ PHP-FPM อย่างน้อย 1 รายการ

ตรวจว่า Apache เปิดส่วนที่ใช้กับ DHDC4 แล้ว:

```bash
apache2ctl -M | grep -E 'rewrite|headers|ssl|proxy_fcgi'
```

หากรายการไม่ครบ ให้ติดต่อผู้ดูแลเครื่องก่อน

### 3.3 ตรวจ MariaDB

คำสั่งนี้อ่านค่าเท่านั้น หากบัญชี MariaDB ของเครื่องไม่อนุญาต ให้ผู้ดูแลฐานข้อมูลเป็นผู้รัน:

```bash
sudo mariadb --protocol=socket --batch --skip-column-names -e "SELECT @@version; SELECT @@port; SELECT @@socket; SELECT @@global.local_infile; SELECT @@global.event_scheduler; SELECT @@global.character_set_collations;"
```

ตรวจผลดังนี้:

- จดหมายเลขพอร์ตจาก `@@port` เพื่อใช้ในขั้นตอนตั้งค่า
- `local_infile` ต้องเป็น `1` หรือ `ON`
- ระหว่างติดตั้ง `event_scheduler` ควรเป็น `OFF`
- `character_set_collations` ต้องรองรับ `utf8mb3=utf8mb3_general_ci`

ถ้าค่าไม่ตรง ให้หยุดและติดต่อผู้ดูแล MariaDB ไม่ควรแก้ค่าโดยเดา

## 4. ดาวน์โหลด DHDC4 จาก GitHub

โครงการใช้ GitHub นี้:

<https://github.com/golderboy/dhdc4-beta>

สร้างโฟลเดอร์และดาวน์โหลดรุ่น `v4.0.2`:

```bash
sudo git clone --branch v4.0.2 --depth 1 https://github.com/golderboy/dhdc4-beta.git /var/www/dhdc4
```

เปลี่ยนเจ้าของชั่วคราวให้ผู้ติดตั้ง เพื่อให้เตรียมโปรแกรมได้โดยไม่ต้องรัน Composer ด้วยสิทธิ์สูง:

```bash
sudo chown -R "$(id -un):$(id -gn)" /var/www/dhdc4
```

เข้าโฟลเดอร์ DHDC4:

```bash
cd /var/www/dhdc4
```

ตรวจว่าได้รุ่นถูกต้อง:

```bash
git describe --tags --exact-match
```

ผลต้องเป็น `v4.0.2`

## 5. เตรียมไฟล์และตั้งค่าโครงการ

สร้างไฟล์สำหรับใช้งานจริง โฟลเดอร์ชั่วคราว และกุญแจป้องกัน Cookie:

```bash
cd /var/www/dhdc4
php init --env=Production --overwrite=All
```

ติดตั้งส่วนประกอบตาม `composer.lock` โดยไม่รวมเครื่องมือสำหรับนักพัฒนา:

```bash
composer install --no-dev --classmap-authoritative --no-interaction
```

ตรวจว่าส่วนประกอบตรงกับ PHP ในเครื่อง:

```bash
composer check-platform-reqs --no-dev
```

ทุกรายการต้องแสดง `success` หากพบข้อผิดพลาด ให้แก้ PHP หรือส่วนเสริมที่ขาดก่อน

## 6. สร้างและนำเข้าฐานข้อมูล

ฐานข้อมูลหลักไม่ได้เก็บไว้ใน Git เพราะไฟล์มีขนาดใหญ่ ให้ดาวน์โหลดชุดติดตั้งฐานข้อมูลจากหน้า GitHub Release:

<https://github.com/golderboy/dhdc4-beta/releases/tag/v4.0.2>

### 6.1 ดาวน์โหลดและตรวจไฟล์

เข้าใช้งานด้วยสิทธิ์ผู้ดูแลเครื่อง:

```bash
sudo -i
```

สร้างโฟลเดอร์ที่บุคคลอื่นเปิดอ่านไม่ได้:

```bash
install -d -m 0700 /opt/dhdc4-install
cd /opt/dhdc4-install
```

ดาวน์โหลดชุดฐานข้อมูลและไฟล์ตรวจสอบ:

```bash
curl -fLO https://github.com/golderboy/dhdc4-beta/releases/download/v4.0.2/dhdc4-database-installer-v4.0.2.zip
curl -fLO https://github.com/golderboy/dhdc4-beta/releases/download/v4.0.2/dhdc4-database-installer-v4.0.2.zip.sha256
```

ตรวจว่าไฟล์ดาวน์โหลดมาครบ:

```bash
sha256sum -c dhdc4-database-installer-v4.0.2.zip.sha256
```

ผลต้องมีคำว่า `OK` จากนั้นจึงแตกไฟล์:

```bash
unzip dhdc4-database-installer-v4.0.2.zip
cd dhdc4-database-installer-v4.0.2
chmod 0700 install-linux.sh
```

### 6.2 ตรวจชุดติดตั้งก่อนนำเข้า

ตรวจไฟล์โดยยังไม่เชื่อมต่อหรือเปลี่ยนฐานข้อมูล:

```bash
./install-linux.sh --dry-run
```

ผลต้องลงท้ายด้วย `Dry-run completed`

ตรวจการเชื่อมต่อ MariaDB โดยยังไม่เปลี่ยนฐานข้อมูล:

```bash
./install-linux.sh --check-connection
```

หากเครื่องใช้ Unix socket และถามรหัสผ่านบัญชีผู้ดูแล MariaDB ให้กด Enter ตัวติดตั้งจะแสดงพอร์ตจริงของ MariaDB ให้จดพอร์ตนี้ไว้

### 6.3 นำเข้าฐานข้อมูล

สร้างรหัสผ่านแบบสุ่ม 32 ตัวสำหรับบัญชี `dhdc4@localhost`:

```bash
export DHDC4_DB_OWNER_PASSWORD="$(php -r 'echo bin2hex(random_bytes(16));')"
printf 'รหัสผ่าน dhdc4@localhost: %s\n' "$DHDC4_DB_OWNER_PASSWORD"
```

คัดลอกรหัสที่แสดงไปเก็บในโปรแกรมจัดการรหัสผ่าน รหัสนี้ต้องนำไปใส่ใน `/etc/dhdc4/dhdc4.env` ภายหลัง

เริ่มนำเข้าฐานข้อมูล ตัวติดตั้งจะใช้รหัสที่สร้างไว้โดยอัตโนมัติ:

```bash
./install-linux.sh
```

เมื่อติดตั้งสำเร็จ ต้องพบข้อความ:

```text
DHDC4_VERIFY PASS
```

ล้างรหัสออกจากตัวแปรใน shell:

```bash
unset DHDC4_DB_OWNER_PASSWORD
```

หากไม่พบข้อความนี้ ให้หยุดขั้นตอนติดตั้งและตรวจไฟล์บันทึกในโฟลเดอร์ `install-logs`

ออกจากสิทธิ์ผู้ดูแลเครื่อง:

```bash
exit
```

### 6.4 ตั้งค่าการเชื่อมต่อ

บน Debian หรือ Ubuntu กลุ่มของ Apache คือ `www-data` สร้างไฟล์เก็บค่าที่ไม่ควรเปิดเผย:

```bash
sudo install -d -o root -g www-data -m 0750 /etc/dhdc4
sudo install -o root -g www-data -m 0640 /dev/null /etc/dhdc4/dhdc4.env
sudoedit /etc/dhdc4/dhdc4.env
```

ใส่ค่าต่อไปนี้ โดยเปลี่ยนพอร์ตและรหัสผ่านให้ตรงกับค่าจริง:

```bash
DHDC_DB_DSN='mysql:host=localhost;dbname=dhdc4;port=ใส่พอร์ตจริง'
DHDC_DB_HOST='localhost'
DHDC_DB_PORT='ใส่พอร์ตจริง'
DHDC_DB_NAME='dhdc4'
DHDC_DB_USER='dhdc4'
DHDC_DB_PASSWORD='ใส่รหัสผ่านฐานข้อมูลจริง'
DHDC_MAILER_DSN='null://null'
```

ค่า `null://null` หมายถึงยังไม่ส่งอีเมล หากต้องการส่งอีเมล ให้เปลี่ยนเป็นค่า SMTP ที่ได้รับจากผู้ดูแลระบบ

โหลดค่าและสร้างไฟล์เชื่อมต่อที่เครื่องมือตั้งต้นใช้:

```bash
sudo -i
set -a
source /etc/dhdc4/dhdc4.env
set +a
cd /var/www/dhdc4
php tools/configure-database.php
```

ปรับโครงสร้างฐานข้อมูลให้เป็นรุ่นล่าสุด:

```bash
php yii migrate/up --interactive=0
```

### 6.5 สร้างผู้ดูแลระบบคนแรก

กำหนดชื่อผู้ใช้เป็น `admin` แล้วกรอกอีเมลจริง:

```bash
export DHDC_BOOTSTRAP_ADMIN_USERNAME='admin'
read -rp 'อีเมลผู้ดูแลระบบ: ' DHDC_BOOTSTRAP_ADMIN_EMAIL
export DHDC_BOOTSTRAP_ADMIN_EMAIL
```

กรอกรหัสผ่านใหม่อย่างน้อย 20 ตัวอักษร และต้องไม่มีคำว่า `admin`:

```bash
read -rsp 'รหัสผ่านผู้ดูแลระบบ: ' DHDC_BOOTSTRAP_ADMIN_PASSWORD
echo
export DHDC_BOOTSTRAP_ADMIN_PASSWORD
```

ทดลองตรวจโดยยังไม่สร้างบัญชี:

```bash
php tools/bootstrap-admin.php --dry-run --confirm=CREATE-INITIAL-ADMIN
```

ถ้าพบ `Initial Admin dry-run passed` ให้สร้างบัญชีจริง:

```bash
php tools/bootstrap-admin.php --confirm=CREATE-INITIAL-ADMIN
```

ล้างรหัสผ่านออกจาก shell และกลับสู่ผู้ใช้เดิม:

```bash
unset DHDC_BOOTSTRAP_ADMIN_USERNAME DHDC_BOOTSTRAP_ADMIN_EMAIL DHDC_BOOTSTRAP_ADMIN_PASSWORD
exit
```

## 7. ตั้งค่าสิทธิ์ของโฟลเดอร์

ให้ Apache อ่านไฟล์โครงการได้ แต่เขียนได้เฉพาะโฟลเดอร์ที่จำเป็น:

```bash
sudo chown -R root:www-data /var/www/dhdc4
sudo find /var/www/dhdc4 -type d -exec chmod 0750 {} \;
sudo find /var/www/dhdc4 -type f -exec chmod 0640 {} \;
sudo chmod 0750 /var/www/dhdc4/yii
```

สร้างและกำหนดสิทธิ์โฟลเดอร์ที่ DHDC4 ต้องเขียนข้อมูล:

```bash
sudo install -d -o www-data -g www-data -m 0770 \
  /var/www/dhdc4/frontend/runtime \
  /var/www/dhdc4/backend/runtime \
  /var/www/dhdc4/console/runtime \
  /var/www/dhdc4/frontend/web/assets \
  /var/www/dhdc4/backend/web/assets \
  /var/www/dhdc4/frontend/web/fortythree \
  /var/www/dhdc4/frontend/web/fortythreebackup \
  /var/www/dhdc4/frontend/web/unzip \
  /var/www/dhdc4/frontend/web/sql_upload_file
```

## 8. ตั้งค่าเว็บเซิร์ฟเวอร์

ขั้นตอนนี้เป็นตัวอย่างสำหรับ Debian หรือ Ubuntu ที่ใช้ Apache และ PHP-FPM

### 8.1 ส่งค่าการเชื่อมต่อให้ PHP-FPM

อ่านหมายเลขรุ่น PHP และชื่อบริการ:

```bash
PHP_SHORT_VERSION="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
PHP_FPM_SERVICE="php${PHP_SHORT_VERSION}-fpm"
systemctl status "$PHP_FPM_SERVICE" --no-pager
```

ถ้าไม่พบบริการ ให้หยุดและติดต่อผู้ดูแลเครื่อง

เปิดไฟล์ตั้งค่าของบริการ:

```bash
sudo systemctl edit "$PHP_FPM_SERVICE"
```

ใส่ข้อความนี้แล้วบันทึก:

```ini
[Service]
EnvironmentFile=/etc/dhdc4/dhdc4.env
```

สร้างไฟล์ส่งค่าจากบริการไปยัง PHP:

```bash
PHP_FPM_POOL_DIR="/etc/php/${PHP_SHORT_VERSION}/fpm/pool.d"
sudoedit "$PHP_FPM_POOL_DIR/dhdc4-env.conf"
```

ใส่ข้อความนี้แล้วบันทึก:

```ini
[www]
env[DHDC_DB_DSN] = $DHDC_DB_DSN
env[DHDC_DB_USER] = $DHDC_DB_USER
env[DHDC_DB_PASSWORD] = $DHDC_DB_PASSWORD
env[DHDC_MAILER_DSN] = $DHDC_MAILER_DSN
```

ตรวจและเริ่ม PHP-FPM ใหม่:

```bash
sudo php-fpm${PHP_SHORT_VERSION} -t
sudo systemctl daemon-reload
sudo systemctl restart "$PHP_FPM_SERVICE"
sudo systemctl is-active "$PHP_FPM_SERVICE"
```

ผลบรรทัดสุดท้ายต้องเป็น `active`

### 8.2 ตั้งค่า Apache

คัดลอกไฟล์ตัวอย่างที่มากับโครงการ:

```bash
sudo cp /var/www/dhdc4/docs/apache-dhdc4.conf.example /etc/apache2/sites-available/dhdc4.conf
sudoedit /etc/apache2/sites-available/dhdc4.conf
```

ก่อนบันทึก ต้องเปลี่ยนค่าต่อไปนี้ให้ตรงกับเครื่องจริง:

- `dhdc.example.go.th` เป็นชื่อเว็บไซต์สำหรับผู้ใช้งาน
- `dhdc-admin.example.go.th` เป็นชื่อเว็บไซต์สำหรับผู้ดูแล
- ตำแหน่งไฟล์ใบรับรองและกุญแจ HTTPS
- ช่วง IP ที่อนุญาตให้เปิดหน้าผู้ดูแล
- เปลี่ยน `logs/ชื่อไฟล์.log` เป็น `${APACHE_LOG_DIR}/ชื่อไฟล์.log`

อย่าเปลี่ยน `DocumentRoot` ไปที่ `/var/www/dhdc4` หน้าใช้งานต้องชี้ไปที่ `frontend/web` และหน้าผู้ดูแลต้องชี้ไปที่ `backend/web`

เปิดส่วนที่ Apache ต้องใช้:

```bash
PHP_SHORT_VERSION="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
sudo a2enmod rewrite headers ssl proxy_fcgi setenvif
sudo a2enconf "php${PHP_SHORT_VERSION}-fpm"
sudo a2ensite dhdc4.conf
```

ตรวจไฟล์ตั้งค่าก่อนเริ่มใช้งาน:

```bash
sudo apache2ctl configtest
```

ผลต้องเป็น `Syntax OK` จากนั้นจึงโหลดค่าใหม่:

```bash
sudo systemctl reload apache2
sudo systemctl is-active apache2
```

ผลบรรทัดสุดท้ายต้องเป็น `active`

## 9. เปิดใช้งานครั้งแรก

ให้ผู้ดูแล DNS ชี้ชื่อเว็บไซต์ทั้ง 2 ชื่อมายังเครื่อง DHDC4 จากนั้นเปิดชื่อเว็บไซต์สำหรับผู้ใช้งานผ่าน HTTPS

เปิดหน้าผู้ดูแลผ่านชื่อที่กำหนดไว้ใน Apache แล้วเข้าสู่ระบบด้วยชื่อผู้ใช้ `admin` และรหัสผ่านที่สร้างในขั้นตอนก่อนหน้า

หน้าผู้ดูแลถูกจำกัดตาม IP ที่ตั้งไว้ หากเปิดไม่ได้ ให้ตรวจว่าเครื่องของผู้ใช้มาจากเครือข่ายที่ได้รับอนุญาต

## 10. ตรวจว่าติดตั้งสำเร็จ

โหลดค่าการเชื่อมต่อก่อนตรวจโปรแกรม:

```bash
sudo -i
set -a
source /etc/dhdc4/dhdc4.env
set +a
cd /var/www/dhdc4
```

ตรวจ PHP และส่วนประกอบ:

```bash
php requirements.php
composer check-platform-reqs --no-dev
```

ตรวจคำสั่งของ DHDC4 และรายการปรับฐานข้อมูล:

```bash
php yii help
php yii migrate/new
```

ไม่ควรพบข้อผิดพลาด และไม่ควรมีรายการปรับฐานข้อมูลค้างอยู่

ออกจากสิทธิ์ผู้ดูแล:

```bash
exit
```

ตรวจหน้าเว็บไซต์จากเครื่องแม่ข่าย โดยเปลี่ยนชื่อเว็บไซต์ให้ตรงกับค่าจริง:

```bash
curl -I https://ชื่อเว็บไซต์จริง
curl -I https://ชื่อเว็บไซต์ผู้ดูแลจริง
```

ผลควรเป็น HTTP `200`, `301` หรือ `302` และต้องไม่เป็น `500`

ตรวจไฟล์บันทึกของ Apache:

```bash
sudo tail -n 50 /var/log/apache2/dhdc4-frontend-error.log
sudo tail -n 50 /var/log/apache2/dhdc4-backend-error.log
```

ไม่ควรพบข้อผิดพลาดใหม่เกี่ยวกับสิทธิ์ไฟล์ การเชื่อมต่อฐานข้อมูล หรือ PHP

## 11. ปัญหาที่พบบ่อย

### เปิดเว็บไซต์แล้วพบ HTTP 500

ตรวจไฟล์บันทึก:

```bash
sudo tail -n 100 /var/log/apache2/dhdc4-frontend-error.log
sudo tail -n 100 /var/log/apache2/dhdc4-backend-error.log
```

สาเหตุที่พบบ่อยคือ PHP-FPM ไม่ได้รับค่าจาก `/etc/dhdc4/dhdc4.env` หรือรหัสผ่านฐานข้อมูลไม่ถูกต้อง

### ขึ้นข้อความเกี่ยวกับ `DHDC_DB_DSN`

ตรวจว่าไฟล์ค่าลับมีตัวแปรครบ:

```bash
sudo grep -E '^(DHDC_DB_DSN|DHDC_DB_USER|DHDC_DB_PASSWORD|DHDC_MAILER_DSN)=' /etc/dhdc4/dhdc4.env
```

จากนั้นเริ่ม PHP-FPM ใหม่:

```bash
PHP_FPM_SERVICE="php$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')-fpm"
sudo systemctl restart "$PHP_FPM_SERVICE"
```

### Apache แสดง `Permission denied`

กำหนดสิทธิ์โฟลเดอร์ที่ต้องเขียนใหม่ โดยใช้คำสั่งในหัวข้อ “ตั้งค่าสิทธิ์ของโฟลเดอร์” หลีกเลี่ยงการใช้สิทธิ์ `0777`

### ลิงก์ในเว็บไซต์เปิดแล้วเป็น 404

ตรวจว่า Apache เปิด `rewrite` และอนุญาต `.htaccess`:

```bash
apache2ctl -M | grep rewrite
sudo apache2ctl configtest
```

### ตัวติดตั้งฐานข้อมูลไม่แสดง `DHDC4_VERIFY PASS`

อย่าเปิดระบบให้ผู้ใช้งาน ตรวจไฟล์บันทึกของชุดติดตั้ง:

```bash
sudo find /opt/dhdc4-install -maxdepth 4 -path '*/install-logs/*' -type f -print
```

แก้สาเหตุตามข้อความในไฟล์บันทึก แล้วจึงเริ่มติดตั้งใหม่ตามคู่มือของชุดฐานข้อมูล

### นำเข้าไฟล์ 43 แฟ้มไม่ได้

ตรวจสิทธิ์โฟลเดอร์รับไฟล์:

```bash
sudo -u www-data test -w /var/www/dhdc4/frontend/web/fortythree
sudo -u www-data test -w /var/www/dhdc4/frontend/web/fortythreebackup
sudo -u www-data test -w /var/www/dhdc4/frontend/web/unzip
```

ถ้าคำสั่งใดไม่ผ่าน ให้กำหนดเจ้าของและสิทธิ์ตามหัวข้อ “ตั้งค่าสิทธิ์ของโฟลเดอร์”

## เอกสารเพิ่มเติม

- [คู่มือติดตั้งฐานข้อมูลบน Linux](docs/database-installer-linux.md)
- [ภาพรวมชุดติดตั้งฐานข้อมูล](docs/database-installer.md)
- [ตัวอย่างการตั้งค่า Apache](docs/apache-dhdc4.conf.example)
- [รายการตรวจความพร้อมก่อนเปิดใช้งานจริง](docs/production-readiness-checklist.md)
