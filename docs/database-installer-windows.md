# ติดตั้ง DHDC4 บน Windows PC หรือ Windows Server

คู่มือนี้เขียนสำหรับผู้ดูแลระบบมือใหม่ ใช้กับ Apache บน XAMPP หรือ Apache ที่ติดตั้งแยกบน Windows Server ให้ทำตามลำดับและหยุดทันทีเมื่อพบข้อความสีแดงหรือคำว่า `failed`

## 1. เตรียมไฟล์และโปรแกรม

ต้องมีสิ่งต่อไปนี้ในเครื่องเดียวกัน:

- ชุดโปรแกรม DHDC4 รุ่น `v4.0.2`
- ชุดฐานข้อมูล `dhdc4-database-installer-v4.0.2.zip` และไฟล์ `.zip.sha256`
- Apache
- PHP CLI 8.1 ขึ้นไป พร้อม `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql` และ `zip`
- MariaDB Server และ MariaDB client รุ่น 12.2 หรือรุ่นที่ผ่านการทดสอบความเข้ากันได้
- พื้นที่ว่างอย่างน้อยสามเท่าของขนาดแพ็กเกจหลังแตก ZIP

ตัวอย่างตำแหน่งเมื่อใช้ XAMPP:

```text
C:\xampp\htdocs\dhdc4
C:\DHDC4-Install\dhdc4-database-installer-v4.0.2
```

อย่าแตกชุดฐานข้อมูลไว้ใน `htdocs` เพราะ SQL และ backup ต้องไม่ถูกดาวน์โหลดผ่านเว็บ

## 2. ตั้งค่า MariaDB ก่อนนำเข้าฐาน

เปิดไฟล์ `my.ini` ของ MariaDB ด้วยสิทธิ Administrator แล้วตรวจให้มีค่าในส่วน `[mysqld]` ดังนี้:

```ini
[mysqld]
character-set-server=utf8mb3
collation-server=utf8mb3_general_ci
local-infile=1
event-scheduler=OFF
```

บันทึกไฟล์แล้ว restart MariaDB จาก XAMPP Control Panel หรือ Windows Services

ตรวจพอร์ตจริงด้วยบัญชีผู้ดูแล MariaDB และจดค่าที่แสดง ห้ามสมมติว่าเป็น `3306`:

```powershell
& 'C:\xampp\mysql\bin\mariadb.exe' --host=localhost --user=<บัญชีผู้ดูแล> --password `
  --batch --skip-column-names -e "SELECT @@port; SELECT @@socket;"
```

## 3. ตรวจไฟล์ ZIP

เปิด PowerShell แบบ **Run as administrator** แล้วไปยังโฟลเดอร์ที่เก็บไฟล์:

```powershell
Set-Location 'C:\DHDC4-Install'
Get-FileHash -Algorithm SHA256 '.\dhdc4-database-installer-v4.0.2.zip'
Get-Content '.\dhdc4-database-installer-v4.0.2.zip.sha256'
```

ค่า SHA-256 สองรายการต้องตรงกันทุกตัว จากนั้นแตกไฟล์:

```powershell
Expand-Archive '.\dhdc4-database-installer-v4.0.2.zip' -DestinationPath '.\database' -Force
Set-Location '.\database\dhdc4-database-installer-v4.0.2'
```

## 4. ทดสอบตัวติดตั้งโดยยังไม่แก้ฐานข้อมูล

กำหนดตำแหน่งโปรแกรมให้ตรงกับเครื่อง ตัวอย่าง XAMPP ที่ไดรฟ์ C:

```powershell
$env:DHDC4_MARIADB_BIN = 'C:\xampp\mysql\bin'
$env:DHDC4_PHP_EXE = 'C:\xampp\php\php.exe'
$env:DHDC4_APACHE_EXE = 'C:\xampp\apache\bin\httpd.exe'
$env:DHDC4_DB_HOST = 'localhost'
$env:DHDC4_DB_PORT = '<พอร์ตจริงจาก SELECT @@port>'
$env:DHDC4_DB_ROOT_USER = 'root'
Set-ExecutionPolicy -Scope Process Bypass
.\install-windows.ps1 -DryRun
```

ต้องจบด้วย `Dry-run completed` หากไม่ผ่าน ให้แก้ตามข้อความก่อนทำขั้นต่อไป จากนั้นตรวจการเชื่อมต่อและยืนยันว่าพอร์ตตรงกับ MariaDB โดยยังไม่เปลี่ยนฐาน:

```powershell
.\install-windows.ps1 -CheckConnection
```

ต้องเห็น `MariaDB connection verified` และ `Connection check passed` ตัวติดตั้งจะไม่เดาพอร์ต `3306` หากไม่ได้กำหนด `DHDC4_DB_PORT` หรือ `-DbPort` จะหยุดก่อนเชื่อมต่อ

## 5. ติดตั้งฐานข้อมูลและสร้าง MariaDB user

หยุด Apache ก่อน เพื่อไม่ให้มีผู้ใช้เขียนข้อมูลระหว่างติดตั้ง แล้วรันตัวติดตั้ง:

```powershell
.\install-windows.ps1
```

สคริปต์จะถามสองรหัสผ่าน:

1. รหัสผ่าน MariaDB `root`
2. รหัสผ่านใหม่สำหรับ `'dhdc4'@'localhost'` ให้ตั้งอย่างน้อย 32 ตัวอักษรและเก็บใน password manager

ขณะพิมพ์รหัสผ่าน หน้าจอจะไม่แสดงตัวอักษร ถือเป็นอาการปกติ ตัวติดตั้งจะทำคำสั่ง `CREATE USER`, `ALTER USER` และ `GRANT` ให้อัตโนมัติ

เมื่อติดตั้งเสร็จ ต้องเห็น `DHDC4_VERIFY PASS` หากฐาน `dhdc4` มีอยู่แล้ว ตัวติดตั้งจะหยุดโดยไม่แก้ข้อมูล การติดตั้งทับต้องใช้คำสั่งต่อไปนี้และตอบคำยืนยันให้ถูกต้อง:

```powershell
.\install-windows.ps1 -Recreate -BackupDirectory 'C:\DHDC4-Backups'
```

### คำสั่ง MariaDB แบบทำเอง

ใช้หัวข้อนี้เฉพาะเมื่อตัวติดตั้งสร้าง user ไม่สำเร็จ เปิด MariaDB client ด้วยบัญชี `root`:

```powershell
& 'C:\xampp\mysql\bin\mariadb.exe' --host=localhost --port=<พอร์ตจริง> --user=root --password
```

พิมพ์ SQL ด้านล่าง โดยเปลี่ยน `รหัสผ่านยาวอย่างน้อย32ตัว` เป็นรหัสจริง ห้ามใช้ข้อความตัวอย่างเดิม:

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

## 6. ตั้งค่า Yii2 ให้เชื่อมฐานข้อมูล

ไปที่โฟลเดอร์โปรแกรมและกำหนดรหัสฐานข้อมูลเดียวกับข้อ 5:

```powershell
Set-Location 'C:\xampp\htdocs\dhdc4'
$Php = 'C:\xampp\php\php.exe'
$env:DHDC_DB_HOST = 'localhost'
$env:DHDC_DB_PORT = '<พอร์ตจริงจาก SELECT @@port>'
$env:DHDC_DB_NAME = 'dhdc4'
$env:DHDC_DB_USER = 'dhdc4'
$env:DHDC_DB_PASSWORD = 'ใส่รหัสผ่านฐานข้อมูลจริง'
& $Php 'tools\configure-database.php'
```

ต้องขึ้น `Local database configuration updated for user dhdc4.` ไฟล์รหัสผ่านจะอยู่ที่ `common/config/connect_database.php` ซึ่ง Git ignore ไว้แล้ว ห้ามส่งไฟล์นี้ให้ผู้อื่น

สำหรับ Production initializer ต้องกำหนดค่า environment ของฐานข้อมูลและ mailer ให้ครบก่อนรัน Yii2:

```powershell
$env:DHDC_DB_DSN = 'mysql:host=localhost;dbname=dhdc4;port=<พอร์ตจริงจาก SELECT @@port>'
$env:DHDC_DB_USER = 'dhdc4'
$env:DHDC_DB_PASSWORD = 'ใส่รหัสผ่านฐานข้อมูลจริง'
$env:DHDC_MAILER_DSN = 'ใส่ DSN ของ SMTP จริง'
& $Php init --env=Production --overwrite=All
composer install --no-dev --classmap-authoritative --no-interaction
& $Php requirements.php
& $Php yii help
```

อย่าเก็บรหัสผ่านจริงในไฟล์ `.ps1`, Git หรือคู่มือ เมื่อปิด PowerShell ตัวแปรข้างต้นจะหาย หากจะใช้งานเป็น service ให้ตั้งผ่าน secret store หรือ environment ของ service account

### ทำให้ Apache เห็นค่า Production หลัง restart

ค่าที่ตั้งด้วย `$env:` ข้างต้นใช้ได้เฉพาะหน้าต่าง PowerShell ปัจจุบัน ก่อนใช้งานจริงต้องตั้งให้ Apache service อ่านได้:

1. กด Start แล้วค้นหา `Edit the system environment variables`
2. เปิด `Environment Variables...`
3. ในส่วน `System variables` เพิ่ม `DHDC_DB_DSN`, `DHDC_DB_USER`, `DHDC_DB_PASSWORD` และ `DHDC_MAILER_DSN` โดยใช้ค่าเดียวกับด้านบน
4. ปิดและเปิด XAMPP Control Panel ใหม่ หรือ restart Apache service เพื่อให้ process ใหม่รับค่า
5. เปิด frontend และ backend ทดสอบ หากขึ้นข้อความว่าขาด environment variable ให้หยุด Apache และตรวจชื่อทั้งสี่รายการอีกครั้ง

ผู้ดูแลทั่วไปไม่ควรมีสิทธิแก้ System variables และห้ามจับภาพหน้าจอที่เห็นค่ารหัสผ่าน หากองค์กรมี secret manager ให้ใช้ secret manager แทนวิธีนี้

## 7. สร้าง Admin ตั้งต้น

กำหนดอีเมลจริงของผู้ดูแลแล้วทดสอบก่อน:

```powershell
$env:DHDC_BOOTSTRAP_ADMIN_EMAIL = 'admin@your-organization.example'
& $Php 'tools\bootstrap-admin.php' --use-default-credentials --dry-run --confirm=CREATE-INITIAL-ADMIN
```

ถ้าขึ้น `Initial Admin dry-run passed` ให้สร้างจริง:

```powershell
& $Php 'tools\bootstrap-admin.php' --use-default-credentials --confirm=CREATE-INITIAL-ADMIN
Remove-Item Env:\DHDC_BOOTSTRAP_ADMIN_EMAIL
```

ข้อมูลเข้าสู่ระบบครั้งแรกคือ:

```text
Username: admin
Password: P@ssw0rd
```

เข้าสู่ระบบแล้วเปิด `/user/settings/account` เพื่อเปลี่ยนรหัสผ่านทันที ต้องเปลี่ยนก่อนเปิด Firewall หรือให้เครื่องอื่นเข้าใช้งาน

## 8. ตั้ง Apache และตรวจรับ

ตั้ง VirtualHost แยก frontend และ backend:

```apache
DocumentRoot "C:/xampp/htdocs/dhdc4/frontend/web"
<Directory "C:/xampp/htdocs/dhdc4/frontend/web">
    AllowOverride All
    Options -Indexes +FollowSymLinks
    Require all granted
</Directory>
```

backend ต้องชี้ไปที่ `C:/xampp/htdocs/dhdc4/backend/web` ห้ามชี้ไปที่ `C:/xampp/htdocs/dhdc4` ตรวจ config ก่อนเปิด Apache:

```powershell
& $env:DHDC4_APACHE_EXE -t
```

ตรวจรายการต่อไปนี้ให้ครบ:

- หน้า frontend และ backend เปิดได้ผ่าน localhost
- HTTP redirect ไป HTTPS และ HSTS ทำงานใน localhost ที่ทดสอบไว้
- ล็อกอิน `admin` ได้ และเปลี่ยนรหัสผ่านตั้งต้นแล้ว
- `php yii migrate/new` ไม่พบ migration ค้าง
- ไม่มี SQL, backup, log หรือไฟล์ config ถูกเปิดผ่าน URL
- MariaDB `event_scheduler` ยังเป็น `OFF`

เมื่อตรวจครบแล้วจึงถือว่าเครื่อง localhost พร้อมสำหรับขั้นย้ายขึ้น Production Server ส่วน domain และ certificate จริงให้ตั้งเมื่อมีข้อมูลเครื่องจริง
