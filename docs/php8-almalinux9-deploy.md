# DHDC4 PHP 8 / AlmaLinux 9 Deployment Runbook

เอกสารนี้ใช้สำหรับนำระบบ DHDC4 ที่ปรับให้รองรับ PHP 8 ไปติดตั้งบนเครื่องพัฒนา XAMPP/Apache และเป้าหมาย Production แบบ AlmaLinux 9 + httpd Apache + PHP 8 โดยไม่เปลี่ยน business logic, URL, RBAC, session หรือ CSRF เดิม

## เป้าหมายขั้นต่ำ

- PHP 8.2 ขึ้นไป พร้อม extension ที่ Yii2 ใช้ เช่น `pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `fileinfo`, `openssl`
- MariaDB/MySQL ที่เปิด `local_infile`
- Database charset/collation หลักเป็น `utf8mb3` / `utf8mb3_general_ci`
- Apache document root ชี้ไปที่ `frontend/web` และ `backend/web` ตาม virtual host ที่แยกกัน
- Composer dependencies ติดตั้งจาก `composer.lock`

## Database Compatibility สำหรับ 43 แฟ้ม

หลัง import database หรือ restore backup ให้รัน migration ของโปรเจค:

```bash
php yii migrate/up --interactive=0
```

Migration `m260707_162500_php8_43file_compatibility` จะทำสิ่งต่อไปนี้:

- ตั้ง database default เป็น `utf8mb3_general_ci`
- เพิ่มคอลัมน์ที่จำเป็นสำหรับไฟล์ 43 รุ่นปัจจุบัน เช่น `WEIGHT`, `HSUB`, `PROVIDER`, `LENGTH`, `HEADCIRCUM`, `HEIGHT`, `CHRONICFUPLACE`
- เพิ่มทั้งตาราง raw และ `dhdc_tmp_*` ที่เกี่ยวข้อง
- ไม่ลบข้อมูลเดิม และไม่แก้ business logic

## Restore Stored Procedures / Functions

ไฟล์ `db_function.sql` ถูกปรับให้ใช้ได้กับ MariaDB รุ่นใหม่แล้ว:

- normalize definer เป็น `root@localhost`
- แก้ function `substrCount` ไม่ให้ใช้ตัวแปรชื่อ `offset` ซึ่งชน reserved keyword

ให้ source ด้วย session mode เดียวกับ legacy workflow:

```bash
mariadb --host="$DHDC_DB_HOST" --port="$DHDC_DB_PORT" --user="$DHDC_DB_USER" --password --database="$DHDC_DB_NAME" --default-character-set=utf8 \
  --init-command="SET SESSION sql_mode=''; SET NAMES utf8 COLLATE utf8_general_ci; SET SESSION character_set_collations='utf8mb3=utf8mb3_general_ci,utf8mb4=utf8mb4_general_ci'" \
  --execute="source D:/xampp/htdocs/dhdc4/db_function.sql"
```

บน AlmaLinux ให้เปลี่ยน path และ credential ให้ตรงกับ server จริง เช่น `/var/www/dhdc4/db_function.sql`

## Production secrets

ห้ามเก็บรหัสผ่านฐานข้อมูลไว้ใน Git หรือส่งรหัสผ่านผ่าน command line ให้กำหนด environment variables อย่างน้อยดังนี้:

```bash
DHDC_DB_DSN='mysql:host=127.0.0.1;dbname=dhdc4;port=3306'
DHDC_DB_HOST='127.0.0.1'
DHDC_DB_PORT='3306'
DHDC_DB_NAME='dhdc4'
DHDC_DB_USER='dhdc_app'
DHDC_DB_PASSWORD='<load-from-secret-store>'
DHDC_MAILER_DSN='smtps://user:password@smtp.example.go.th:465'
DHDC_FRONTEND_COOKIE_VALIDATION_KEY='<generate-at-least-32-random-characters>'
DHDC_BACKEND_COOKIE_VALIDATION_KEY='<generate-a-different-random-key>'
DHDC_GOOGLE_MAPS_API_KEY='<new-restricted-browser-key>'
# Optional map services; omit a layer when no verified HTTPS endpoint is available.
DHDC_RAIN_RADAR_BASE_URL='https://maps.example.go.th/radar'
DHDC_FLOOD_WMS_BASE_URL='https://maps.example.go.th/flood/wms'
DHDC_FLOOD_PERCENT_WMS_BASE_URL='https://maps.example.go.th/flood-percent/wms'
# Smart-card service must use HTTPS, or HTTP on localhost/127.0.0.1/::1 only.
DHDC_SMARTCARD_BASE_URL='http://127.0.0.1:8080/smartcard'
```

Google Maps key ต้องเป็น key ใหม่หลังเพิกถอน key ที่เคยฝังใน source code และต้องจำกัดทั้ง HTTP referrer ของ frontend จริงและ API scope ที่ระบบใช้ ห้ามนำ key เดิมกลับมาใช้ ส่วนชั้นข้อมูลแผนที่ที่ไม่มี HTTPS จะถูกปิดโดยอัตโนมัติแทนการโหลด mixed content

การ rewrite และ force-push ทำให้ branch/tag หลักไม่อ้างถึงประวัติเก่า แต่ clone, fork, pull request และ cached view อาจยังเก็บ object เดิม ผู้ร่วมพัฒนาต้องทิ้ง clone เก่าหรือ rebase จากประวัติใหม่ และเจ้าของ repository ต้องติดต่อ GitHub Support หากต้องล้าง reference/cached view ที่ยังอ้างถึง secret

ระบบตั้งค่า application log ไม่ให้บันทึก `$_GET`, `$_POST`, `$_COOKIE`, `$_SESSION` และ `$_SERVER` อัตโนมัติแล้ว หากเพิ่ม log เองต้องไม่บันทึกรหัสผ่าน token cookie เลขบัตรประชาชน หรือข้อมูลสุขภาพทั้งก้อน

บัญชี `DHDC_DB_USER` ต้องเป็น service account เฉพาะฐานข้อมูล DHDC4 และต้องไม่มี global privilege หรือ `GRANT OPTION`
ใช้ `docs/database-service-account.sql.example` เป็น template สำหรับสร้างบัญชี จากนั้นตรวจ `SHOW GRANTS` ก่อนเปิดระบบ

ก่อนติดตั้ง dependency หรือรัน migration ให้ initialize production template เพื่อสร้าง cookie validation keys ใหม่สำหรับเครื่องนั้น:

```bash
php init --env=Production --overwrite=All
composer install --no-dev --classmap-authoritative --no-interaction
```

## Apache / PHP

Production ต้องเปิดผ่าน HTTPS เท่านั้น ใช้ `docs/apache-dhdc4.conf.example` เป็น template แล้วแทน hostname และ certificate path ให้ตรงกับเครื่องจริง ห้ามตั้ง `DocumentRoot` เป็น `/var/www/dhdc4`

Template บังคับ TLS 1.2/1.3, ซ่อนรายละเอียดเวอร์ชัน Apache, redirect HTTP ไป HTTPS และจำกัด backend ไว้ที่ loopback/private network สำหรับ production ต้องแทน private ranges ด้วย CIDR ของเครือข่ายโรงพยาบาล/VPN ที่อนุมัติจริงก่อนเปิดบริการ

ตัวอย่าง virtual host:

```apache
<VirtualHost *:80>
    ServerName dhdc4.local
    DocumentRoot /var/www/dhdc4/frontend/web

    <Directory /var/www/dhdc4/frontend/web>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:80>
    ServerName dhdc4-admin.local
    DocumentRoot /var/www/dhdc4/backend/web

    <Directory /var/www/dhdc4/backend/web>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

ค่าที่ควรตรวจใน MariaDB/PHP:

- `local_infile=ON`
- `max_allowed_packet` เพียงพอกับ ZIP/43 แฟ้ม
- `character_set_collations` รองรับ `utf8mb3=utf8mb3_general_ci`
- PHP upload/post limits ใหญ่พอสำหรับไฟล์ ZIP จริง
- สิทธิ์เขียนได้ที่ `frontend/runtime`, `backend/runtime`, `console/runtime`, `frontend/web/assets`, `backend/web/assets`, `frontend/web/fortythree`, `frontend/web/fortythreebackup`, `frontend/web/unzip`

## Verification Checklist

หลัง deploy ให้ตรวจตามลำดับนี้:

1. `php yii migrate/new` ต้องไม่พบ migration ค้าง
2. `php -l` ผ่านสำหรับ controller ที่แก้ไข
3. หน้า `/import/upload/index` และ `/import/count-file/index` เปิดได้
4. import ZIP 43 แฟ้มจริงผ่านหน้าเว็บ และ `sys_upload_fortythree.note2 = OK`
5. `sys_count_import_file` มีรายการครบตามไฟล์ `.txt` ใน ZIP
6. backend `/exec/transform/exec` ตอบ `ประมวลผลเสร็จสมบูรณ์`
7. backend `/exec/qc/exec` ตอบ `ประมวลผลเสร็จสมบูรณ์`
8. `sys_process_running.is_running = false`
9. `last_transform.last_time` และ `last_err_check.last_time` เป็นเวลาปัจจุบัน
10. หน้า `/qc/default/index` และ `/hdc/default/index` เปิดได้

## UI Non-Regression Gate

UI ใหม่ต้องไม่กระทบ workflow เดิม โดยเฉพาะการนำเข้า 43 แฟ้ม, Transform, QC, session, RBAC และ CSRF เดิม ก่อนส่งงาน UI ให้รัน smoke test แบบ read-only:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force
.\tools\smoke-ui-readonly.ps1
```

สคริปต์นี้ตรวจโดยไม่กด import, ไม่กด Transform, ไม่กด QC และไม่แก้ข้อมูล:

- PHP lint ของ view/controller สำคัญ
- ไม่มี migration ค้าง
- มี upload 43 แฟ้มที่สำเร็จแล้ว
- `sys_count_import_file` ไม่ว่าง
- `sys_process_running = false`
- หน้า UI pilot เปิดได้และมี `dhdc-page-header` / `dhdc-stat-card`
- backend process page ยัง render ได้หรือ redirect ไป login ตาม auth เดิม
- frontend protected routes เช่น `/Unitcost/default/index`, `/student/default/index`, `/Tbmaps/default/index` และ `/hdc/default/report-id` ต้อง redirect ไปหน้า login ตาม auth เดิมเมื่อยังไม่ login
- ไม่มี `Database Exception` หรือ `PHP Warning` แสดงบนหน้า
- ไม่มี application log ระดับ `[error]` เกิดใหม่ระหว่าง smoke run

หลังปรับ UI ให้เก็บ screenshot จาก browser จริงไว้ใน `output/playwright/` สำหรับหน้าหลักที่แก้ เช่น:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force
.\tools\capture-ui-screens.ps1
```

- `import-dashboard-desktop.png`
- `import-dashboard-mobile.png`
- `plugin-dashboard-desktop.png`
- `hdc-index-desktop.png`
- `qof-dashboard-desktop.png`
- `frontend-login-desktop.png`
- `backend-login-desktop.png`
- `frontend-user-login-desktop.png`
- `backend-user-login-desktop.png`

หากหน้า protected route ต้องปรับ UI ภายใน ควรทดสอบด้วยบัญชีจริงที่มี role `User` หรือ `Admin` ก่อนส่งงาน เพื่อยืนยันว่า RBAC, session และ action ที่มี side effect ยังเหมือนเดิม

เมื่อต้องทดสอบหน้า protected route ด้วยบัญชีจริง ให้ใช้สคริปต์ authenticated smoke โดยส่ง credential ผ่านพารามิเตอร์ ไม่ hardcode ลงไฟล์:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force
.\tools\smoke-ui-authenticated.ps1 -Username "<user>" -Password "<password>"
```

## Release Gate

สร้างไฟล์ส่งมอบจาก annotated tag ที่ผ่าน strict gate ด้วยคำสั่งต่อไปนี้ สคริปต์จะตัด updater, test และ development environment ออก ติดตั้ง Composer แบบ `--no-dev` ตรวจ readiness ภายในแพ็กเกจ และสร้างไฟล์ SHA-256 คู่กับ ZIP:

```powershell
npm run verify:map-runtime
.\tools\build-release.ps1 -Tag v4.0.0
```

ก่อนส่งงานหรือ deploy ให้รัน gate รวมนี้เป็นคำสั่งหลัก โดยส่ง credential ผ่าน parameter และไม่บันทึกรหัสผ่านลงไฟล์:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force
.\tools\verify-release.ps1 -Username "<user>" -Password "<password>"
```

`verify-release.ps1` จะรัน `smoke-ui-readonly.ps1`, `smoke-ui-authenticated.ps1`, `capture-ui-screens.ps1` และตรวจ database invariant รอบสุดท้าย โดยต้องผ่านเงื่อนไขหลัก:

- import ZIP 43 แฟ้มเป้าหมายต้อง `OK`
- จำนวนไฟล์นำเข้าและจำนวน record รวมต้องตรงกับชุดทดสอบ
- Transform/QC ต้องจบแล้ว และ `sys_process_running = false`
- `hdc_log` และ `sys_check_process` ต้องจบที่ `end`
- active module routes ใน `sys_dhdc_plugin` ต้องถูกตรวจด้วย authenticated smoke
- screenshot หลักต้องถูกสร้างใน `output/playwright/`

## Rollback

- ก่อน migration หรือ source routine ให้ backup database เต็มเสมอ
- Migration นี้ไม่ทำ `safeDown` เพราะการลบ column หลัง import อาจทำให้ข้อมูล 43 แฟ้มสูญหาย
- หากต้อง rollback ให้ restore DB backup และ restore code revision ก่อน deploy
- ถ้า routine restore มีปัญหา ให้ restore routine backup หรือ source `db_function.sql` รุ่นก่อนหน้าที่สำรองไว้
