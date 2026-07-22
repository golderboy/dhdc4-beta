# รายการตรวจรับ DHDC4 Master Baseline รุ่น v4.0.0

> รูปแบบเอกสาร: TH SarabunPSK, หัวข้อระดับ 1 ขนาด 18 pt ตัวหนา, หัวข้อระดับ 2 ขนาด 16 pt ตัวหนา, เนื้อหา 14 pt
>
> กลุ่มผู้อ่าน: ผู้ดูแลระบบ ผู้ดูแลฐานข้อมูล และทีมพัฒนาที่รับมอบระบบ
>
> วันที่ตรวจ: 22 กรกฎาคม 2569

## 1. วัตถุประสงค์

เอกสารนี้ใช้ตรวจรับซอร์สโค้ดและฐานข้อมูล DHDC4 รุ่น `v4.0.0` สำหรับนำไปเป็น Master Baseline โดยต้องไม่มีข้อมูลบริการสุขภาพ ข้อมูลนำเข้า ผลประมวลผล บัญชีผู้ใช้จริง หรือไฟล์นำเข้าจากหน่วยบริการเดิม แต่ยังคงโครงสร้างฐานข้อมูล รหัสอ้างอิง นิยามรายงาน Stored Routine การกำหนดบทบาท และไฟล์สำคัญที่จำเป็นต่อ Yii2 ไว้ครบถ้วน

## 2. ผลตรวจฐานข้อมูล Master

| รายการตรวจ | เกณฑ์ผ่าน | ผลตรวจ |
|---|---|---|
| ตารางข้อมูล 43 แฟ้ม | `sys_files` มีนิยาม 43 รายการ และตารางข้อมูลทุกแฟ้มมี 0 แถว | ผ่าน |
| ข้อมูลนำเข้าและข้อมูล staging | ตารางนำเข้า staging correction และโมดูลที่มีข้อมูลระดับบุคคลต้องมี 0 แถว | ผ่าน |
| ผลประมวลผล HDC/QC | ตาราง `t_*`, `s_*`, `tmp_*`, `err_*`, QOF และตารางสรุปที่เกี่ยวข้องต้องมี 0 แถว | ผ่าน |
| HDC Exchange | ไม่มีตารางหรือ Stored Procedure ชื่อ `tmp_export_exchange_<id>` ค้างอยู่ | ผ่าน |
| ประวัติการนำเข้าและประมวลผล | ไม่มี upload log, count file, process log, transform/QC timestamp หรือ report result ค้างอยู่ | ผ่าน |
| บัญชีและข้อมูลส่วนบุคคล | ตาราง user, profile, token และ RBAC assignment มี 0 แถว โดยคงนิยาม RBAC ไว้ | ผ่าน |
| สถานะกระบวนการ | `sys_process_running` มี 1 แถวและมีค่า `false` | ผ่าน |
| โครงสร้างที่ต้องเก็บ | นิยามรายงาน หมวดรายงาน รหัสอ้างอิง `chospital` และ Stored Routine ยังใช้งานได้ | ผ่าน |

คำสั่งตรวจซ้ำแบบไม่แก้ไขฐานข้อมูล:

```powershell
php tools/prepare-master-baseline.php --verify
```

หากจำเป็นต้องเตรียมฐานใหม่ ให้สำรองฐานก่อน แล้วตรวจรายการที่จะล้างด้วย dry-run ก่อนเสมอ:

```powershell
php tools/prepare-master-baseline.php
php tools/prepare-master-baseline.php --execute --confirm=CLEAR-dhdc4
php tools/prepare-master-baseline.php --verify
```

## 3. ไฟล์ฐานข้อมูลสำหรับแจกจ่าย

| รายการ | ค่า |
|---|---|
| ไฟล์ | `dhdc4-master-v4.0.0.zip` |
| รูปแบบ | Logical SQL dump, UTF-8, รวม database schema, data ตั้งต้น, views, triggers, events และ routines |
| ขนาด | 57,340,625 ไบต์ |
| SHA-256 | `371D4A955441766DD79E45A9EA3274E8C956D2DAFEEDD0F5D80C06CE1EAE81E6` |
| การจัดการ DEFINER | เปลี่ยนเป็น `CURRENT_USER` เพื่อไม่ผูกกับบัญชี MariaDB ของเครื่องต้นทาง |
| ผลกู้คืนทดสอบ | ผ่านบน MariaDB 12.2 instance แยก: 821 tables/views, 512 routines, ข้อมูลที่ต้องล้าง 0 แถว และ `mariadb-check` ผ่าน |

ไฟล์ `*.internal.sql` เป็นหลักฐานภายในที่ยังคง DEFINER ของเครื่องต้นทาง ห้ามนำไปแจกจ่าย ให้ใช้ไฟล์ ZIP ที่ระบุในตารางเท่านั้น

## 4. ไฟล์สำคัญของ Yii2 ที่ต้องมีใน Git

| หมวด | ไฟล์หรือโฟลเดอร์ที่ต้องมี |
|---|---|
| Dependency | `composer.json`, `composer.lock`, `package.json`, `package-lock.json` |
| Yii console และ initializer | `yii`, `yii.bat`, `init`, `init.bat`, `requirements.php`, `environments/index.php` |
| Production environment | ไฟล์ภายใต้ `environments/prod` รวม main-local, params-local, web index, robots และ `yii` |
| Application configuration | `common/config/bootstrap.php`, `common/config/main.php`, config ของ frontend/backend/console และไฟล์ `*.example.php` |
| Web entry point | `frontend/web/index.php`, `backend/web/index.php` และ `.htaccess` ของทั้งสอง application |
| Upload directory marker | `.gitignore` ใน `fortythree`, `fortythreebackup`, `unzip` และ `sql_upload_file` |
| Database tools | `tools/prepare-master-baseline.php`, `tools/bootstrap-admin.php` และเครื่องมือตรวจ production/release |
| Documentation | คู่มือติดตั้ง Apache/PHP/MariaDB และเอกสารตรวจรับ Master Baseline ฉบับนี้ |

ไฟล์ local secret เช่น `common/config/connect_database.php`, cookie validation key, `.env`, runtime, generated assets, upload payload, backup และ output ต้องไม่ถูกติดตามใน Git

## 5. การตรวจ Git ก่อนสร้าง release

- [x] ไม่มีไฟล์ ZIP, SQL dump, RAR, database file หรือไฟล์นำเข้าถูกติดตามใน Git
- [x] Reachable history ไม่มีไฟล์นำเข้า archive, database dump เดิม หรือ local secret file
- [x] ไม่มี tracked file ขนาดตั้งแต่ 5 MB ขึ้นไป
- [x] มีเฉพาะ source asset; generated asset และ runtime ถูก ignore
- [x] โฟลเดอร์ `fortythreebackup` มี marker file และ payload ภายในถูก ignore
- [ ] Working tree ต้องสะอาดหลัง commit
- [ ] Branch `main` และ tag `v4.0.0` ต้องชี้ไปยัง commit ที่ผ่าน strict release gate
- [ ] ต้องตรวจ fresh clone และ release ZIP ซ้ำหลัง push

## 6. ผลทดสอบก่อน commit

| Test ID | กรณีทดสอบ | ผลที่คาดหวัง | สถานะ |
|---|---|---|---|
| MB-DB-01 | ตรวจ Master Database แบบ read-only | 560 ตารางข้อมูลเป้าหมายเป็นศูนย์ และไม่มี HDC Exchange temp | ผ่าน |
| MB-DB-02 | กู้ logical backup บน instance แยก | schema/routine ครบ ข้อมูลเป้าหมายเป็นศูนย์ และ database check ผ่าน | ผ่าน |
| MB-UI-01 | Public/User smoke บนฐานว่าง | Dashboard, Import, QC, HDC และหน้าที่ป้องกันสิทธิ์ทำงานโดยไม่มี Database Exception | ผ่าน |
| MB-UI-02 | Admin authenticated smoke | Login, active modules และ backend process dashboard ทำงาน | ผ่าน |
| MB-RPT-01 | Audit SQL รายงานบนฐานว่าง | HDC 200, HDC Exchange 138, Population 6, SQL Query 2, EHR 6, Import 8 และ QC 33 ผ่าน | ผ่าน |
| MB-CODE-01 | PHP lint | ไฟล์ PHP ทั้ง repository 480 ไฟล์ไม่มี syntax error | ผ่าน |
| MB-CODE-02 | Unit test | 3 tests, 8 assertions ผ่าน | ผ่าน |
| MB-SEC-01 | OWASP, Composer, NPM และ local map runtime | ไม่พบ regression หรือ vulnerability advisory | ผ่าน |
| MB-INIT-01 | Production initializer | สร้าง runtime/assets และ cookie key แยกกันได้จากโครงสร้างว่าง | ผ่าน |

ข้อสังเกต: `swiftmailer/swiftmailer` ยังถูกติดตั้งทางอ้อมจาก dependency เดิม แต่ application mailer ที่ใช้งานจริงเป็น SymfonyMailer แล้ว รายการนี้เป็นคำเตือนสำหรับแผนอัปเกรด dependency ไม่ใช่ข้อมูลตกค้างใน Master

## 7. ขั้นตอนสร้าง Admin หลังติดตั้ง

Master Baseline ไม่มีบัญชีผู้ใช้เริ่มต้นและไม่มีรหัสผ่านเริ่มต้นร่วมกัน ผู้ติดตั้งต้องกำหนดค่าต่อไปนี้ผ่าน environment ของเครื่องปลายทาง แล้วสร้าง Admin แรกเพียงครั้งเดียว:

```powershell
$env:DHDC_BOOTSTRAP_ADMIN_USERNAME = '<ชื่อผู้ใช้>'
$env:DHDC_BOOTSTRAP_ADMIN_EMAIL = '<อีเมล>'
$env:DHDC_BOOTSTRAP_ADMIN_PASSWORD = '<รหัสผ่านสุ่มอย่างน้อย 20 ตัวอักษร>'
$env:DHDC_BOOTSTRAP_ADMIN_NAME = '<ชื่อที่แสดง>'
php tools/bootstrap-admin.php --confirm=CREATE-INITIAL-ADMIN
```

เครื่องมือจะปฏิเสธการทำงานหากตาราง user ไม่ว่าง บทบาท Admin ไม่มีอยู่ หรือรหัสผ่านไม่ผ่านเงื่อนไข ห้ามบันทึกค่าจริงลง Git, เอกสาร, shell history หรือ log

## 8. เกณฑ์ส่งมอบ

การส่งมอบถือว่าสมบูรณ์เมื่อ commit/tag, strict gate, fresh clone, application release ZIP และ database Master ZIP ผ่านการตรวจ SHA-256 และไม่มีไฟล์หรือข้อมูลต้องห้ามตามเอกสารนี้ ส่วน domain, certificate และการติดตั้งเครื่อง production จริงอยู่นอกขอบเขตตามคำสั่งให้ทำ localhost ให้เสร็จก่อน
