# รายการตรวจสอบความพร้อมก่อนนำระบบ DHDC4 ขึ้นใช้งานจริง

> รูปแบบเอกสาร: TH SarabunPSK, หัวข้อระดับ 1 ขนาด 18 pt ตัวหนา, หัวข้อระดับ 2 ขนาด 16 pt ตัวหนา, เนื้อหา 14 pt
>
> สถานะการประเมิน ณ วันที่ 21 กรกฎาคม 2569: **ผ่านสำหรับ localhost production-readiness** ที่ `https://localhost:18443` และ `https://localhost:18444` ส่วนการติดตั้งเครื่อง production จริงถูกเลื่อนตามคำสั่งเจ้าของระบบและยังไม่ถือว่าได้รับอนุมัติ Go-Live ภายนอกเครื่องนี้

## 1. รายการที่ดำเนินการแล้ว

- [x] ปิดการเข้าถึงไฟล์โครงการและไฟล์ข้อมูลผ่าน DocumentRoot เดิม เช่น `composer.json`, `.git`, log, updater และโฟลเดอร์รับส่งข้อมูลสุขภาพ
- [x] ปิด legacy updater เดิมและตัดออกจาก release archive
- [x] เพิ่ม Access Control และข้อกำหนด HTTP method ให้หน้าจัดการโรงพยาบาล หน้าข้อผิดพลาดการนำเข้า และงาน Population
- [x] เปลี่ยนจุด SQL ที่รับค่าจากผู้ใช้ในขอบเขตที่ตรวจพบให้ใช้ bound parameters
- [x] ย้าย cookie validation key และตัวอย่างค่าฐานข้อมูลออกจาก source code ไปใช้ environment variable หรือไฟล์ local ที่ถูก ignore
- [x] เพิ่ม production initializer และทดสอบสร้าง production config จาก clean copy สำเร็จ
- [x] เพิ่มตัวอย่าง Apache VirtualHost สำหรับแยก frontend/backend, บังคับ HTTPS และ HSTS
- [x] เพิ่มตัวอย่างคำสั่งสร้าง Database service account แบบจำกัดขอบเขต
- [x] เพิ่ม CI, production-readiness gate, OWASP regression check, HTTP security smoke test และ unit test
- [x] ปรับ smoke test หน้ารายงาน HDC/HDC Exchange ให้ตรวจแบบกระจาย 12 รายงาน บน mobile และ desktop โดยมีโหมด full สำหรับตรวจทุกรายงาน
- [x] หมุนเวียนรหัสผ่านผู้ดูแลฐานข้อมูลและ cookie validation key ทั้ง frontend/backend ที่เคยปรากฏใน Git history โดยไม่บันทึกค่าจริงลง log หรือเอกสาร
- [x] เพิ่มเครื่องมือหมุน secret แบบ fail-closed และเครื่องมือสร้าง/ลบบัญชีทดสอบ release แบบจำกัดชื่อและบทบาท
- [x] เพิ่ม HSTS จากตัวแอปเมื่อเชื่อมต่อผ่าน HTTPS และเพิ่มการตรวจ HSTS ใน HTTP security verifier

## 2. รายการบังคับก่อนอนุมัติขึ้นระบบ

| ลำดับ | รายการ | สถานะปัจจุบัน | เกณฑ์ผ่าน | ผู้รับผิดชอบ/หลักฐาน |
|---|---|---|---|---|
| 1 | เปลี่ยนบัญชีฐานข้อมูลของระบบ | ผ่าน: ระบบเชื่อมต่อด้วย `dhdc_app@127.0.0.1` และจำกัดสิทธิ์ไว้ที่ `dhdc4.*` | ผล `SHOW GRANTS` ไม่มี global privilege อื่นนอกจาก `USAGE` และไม่มี `GRANT OPTION` | [x] ตรวจสิทธิ์และทดสอบเชื่อมต่อ/temporary table สำเร็จเมื่อ 21 กรกฎาคม 2569 โดยไม่บันทึกรหัสผ่าน |
| 2 | รัน QC ให้ทัน Transform ล่าสุด | ผ่าน: รัน `CALL err_all()` สำเร็จ และ `last_err_check` อัปเดตเป็น `2026-07-21 11:04:55` | `last_err_check >= last_transform`, process ไม่ค้าง และ QC จบโดยไม่มี error | [x] ผู้ใช้อนุญาตให้ปรับฐานตั้งต้น; ตรวจแล้ว `is_running=false`, `sys_check_process=end` และ read-only smoke ผ่าน |
| 3 | หมุนเวียนข้อมูลลับที่เคยอยู่ใน Git history | ผ่าน: หมุนรหัสผ่านผู้ดูแลฐานข้อมูล 1 ค่าและ cookie validation key 2 ค่าแล้ว; ค่าปัจจุบันไม่ตรงกับ fingerprint เดิมและ credential เดิมใช้ไม่ได้ | เปลี่ยนรหัสผ่าน/กุญแจทุกค่าที่เคย commit; พิจารณาล้าง history ตามนโยบายหน่วยงาน | [x] ตรวจ Git history ครบ 3 commits และยืนยันสถานะโดยไม่บันทึกค่าจริง; การล้าง history ยังเป็นการตัดสินใจเชิงนโยบาย ไม่ใช่เงื่อนไขด้านการใช้งานหลังหมุน secret |
| 4 | ติดตั้ง Apache/TLS สำหรับ localhost | ผ่าน: frontend `https://localhost:18443`, backend `https://localhost:18444`; HTTP พอร์ต 18080/18081 redirect แบบ 301; certificate chain และ hostname ผ่าน; HSTS ทำงาน; TLS 1.2/1.3 ผ่านและ TLS 1.1 ถูกปฏิเสธ | frontend/backend ใช้ HTTPS, HTTP redirect ไป HTTPS, HSTS ทำงาน, DocumentRoot ชี้เฉพาะโฟลเดอร์ `web` | [x] ใช้ local CA ที่ติดตั้งใน Current User trust store; private key อยู่นอก repository และจำกัด ACL เฉพาะผู้ใช้ปัจจุบัน, SYSTEM และ Administrators |
| 5 | ทดสอบด้วยบัญชี User และ Admin จริง | ผ่าน: สร้างบัญชีชั่วคราวในฐานจริงด้วยรหัสผ่านสุ่ม ทดสอบ login/RBAC ทั้ง frontend/backend แล้วลบทิ้ง | workflow User และ Admin ที่สำคัญผ่าน; route ที่ไม่มีสิทธิ์ต้องได้ 403/redirect ตามที่กำหนด | [x] User ผ่าน 4 route และถูกปฏิเสธ route Admin ด้วย 403; Admin authenticated smoke ผ่าน; ตรวจแล้วไม่มีบัญชี `dhdc_release_*` ตกค้าง |
| 6 | ทดสอบกู้คืนข้อมูลสำรอง | ผ่าน: กู้ baseline รุ่น `database-production-baseline-20260721-b2` ที่สร้างหลังหมุน secret ไปยัง datadir แยก เปิด MariaDB แบบ read-only ที่พอร์ต 33062 และตรวจ checksum สำเร็จ | กู้ฐานข้อมูลและไฟล์อัปโหลดลงสภาพแวดล้อมแยกสำเร็จ พร้อมบันทึกเวลาและผลตรวจความครบถ้วน | [x] `dhdc4` ครบ 891 ตาราง, root 4 บัญชีใช้ secret ใหม่, `dhdc_app`/QC ตรงกับฐานปัจจุบัน, `mariadb-check --quick` ผ่าน, application checksum ตรง และปิด instance แยกแล้ว |
| 7 | จัดทำ release จาก working tree ที่สะอาด | ผ่าน: ตรวจทานและทดสอบชุดแก้ไขแล้ว พร้อมจัดทำ release commit และ annotated tag `v4.0.0` หลังล้าง secret จาก Git history | ผ่าน code review, commit แล้ว และ `php tools/verify-production-readiness.php --strict-release` คืนค่า exit code 0 | [x] ใช้ tag `v4.0.0`; ตรวจ strict gate บน working tree สะอาดก่อนส่งมอบ |

ห้ามดำเนินการ Go-Live หากข้อ 1-7 ยังไม่ครบ แม้ automated test ฝั่ง source code จะผ่านแล้วก็ตาม

## 3. หลักฐานการทดสอบล่าสุด

| ชุดตรวจ | ผลล่าสุด |
|---|---|
| PHP syntax lint | ผ่าน 484 ไฟล์; PowerShell parser ผ่าน 5 ไฟล์ และ Node syntax ผ่าน 16 ไฟล์ |
| Codeception unit test | ผ่าน 3 tests, 8 assertions |
| UI layout regression | ผ่าน 56 กรณี |
| HDC report smoke | ผ่าน 12 รายงาน x 2 viewport |
| HDC Exchange report smoke | ผ่าน 12 รายงาน x 2 viewport |
| OWASP regression verifier | ผ่าน |
| HTTP/HTTPS security smoke | ผ่านทั้ง frontend/backend บน localhost โดยตรวจ certificate ด้วย local CA, HSTS, CSP, security headers, RBAC redirect/403 และการป้องกันโฟลเดอร์ข้อมูล |
| Localhost TLS | certificate chain/hostname `localhost` ผ่าน; HTTP 18080/18081 redirect ไป HTTPS 18443/18444; TLS 1.2/1.3 ผ่าน, TLS 1.1 ถูกปฏิเสธ และ HSTS มีเพียงหนึ่ง header |
| Composer/NPM vulnerability audit | ไม่พบ vulnerability advisory |
| Production initializer จากโครงสร้างว่าง | ผ่าน: สร้าง runtime/assets 4 โฟลเดอร์และ cookie key แยกกันสำเร็จ |
| Clean-release simulation | ผ่าน strict production-readiness gate จาก clean snapshot 599 ไฟล์ (`clean-release-verification-20260721-b7`) |
| Database least-privilege | ผ่าน: ใช้ `dhdc_app@127.0.0.1`, สิทธิ์เฉพาะ `dhdc4.*`, ไม่มี `GRANT OPTION` และ smoke test ปฏิเสธบัญชี `root` |
| Production-readiness gate แบบ non-strict | ผ่าน โดยมีคำเตือน working tree ไม่สะอาดและ SwiftMailer ที่ติดตั้งทางอ้อม |
| QC ฐานข้อมูลตั้งต้น | ผ่าน: `last_err_check >= last_transform`, `is_running=false`, ตารางผล QC 30 ตาราง และไม่มี routine ขาดหาย |
| 43-file workflow read-only smoke | ผ่านด้วย service account จริง รวม public route, local User session, protected-route redirect และ database invariants |
| Real User/Admin RBAC smoke | ผ่านด้วยบัญชีชั่วคราวในฐานจริง; User ถูกจำกัดสิทธิ์ถูกต้อง, Admin workflow ผ่าน และลบบัญชีทดสอบครบ |
| Secret rotation | ผ่าน: root 4 บัญชีตรงกับ secret ใหม่, orphan account ถูกลบ, cookie key frontend/backend ถูกหมุนและจำกัด ACL; เครื่องมือหมุน root ผ่าน orphan preflight แบบไม่เปลี่ยนข้อมูล |
| Backup/restore drill | baseline `database-production-baseline-20260721-b2` ขนาด 4.29 GB prepare สำเร็จ; กู้แล้วพบ `dhdc4` 891 ตาราง, root 4 บัญชีใช้ secret ใหม่, `dhdc_app`, QC ล่าสุด และ `mariadb-check --quick` ผ่าน; application checksum และ cookie key ตรงกัน |

คำเตือน SwiftMailer เป็น dependency ทางอ้อมจากแพ็กเกจผู้ใช้เดิม ส่วน active mailer ของระบบเปลี่ยนเป็น SymfonyMailer แล้ว ให้ติดตามการอัปเกรด dependency ต่อไป แต่ไม่ใช่เหตุขัดขวาง Go-Live หากข้อบังคับอื่นผ่านครบ

## 4. คำสั่งตรวจรับก่อน Go-Live

รันจาก project root โดยตั้ง environment variable ที่จำเป็นแล้ว ห้ามส่งรหัสผ่านผ่าน command-line argument หรือบันทึกลง log

```powershell
composer install --no-dev --classmap-authoritative --no-interaction
npm ci --ignore-scripts
composer audit --abandoned=ignore --format=plain
npm audit --audit-level=moderate
php tools/verify-production-readiness.php --strict-release
powershell -NoProfile -ExecutionPolicy Bypass -File tools/smoke-ui-readonly.ps1
php tools/verify-http-security.php --frontend-url=https://localhost:18443 --backend-url=https://localhost:18444 --ca-file=D:/xampp/apache/conf/ssl.dhdc4-localhost/ca.crt
```

สำหรับ authenticated release test ให้กำหนดบัญชีทดสอบ User/Admin ผ่านช่องทางลับของหน่วยงาน แล้วรัน `tools/verify-release.ps1` โดยไม่บันทึกรหัสผ่านลงเอกสารหรือ repository

## 5. ขั้นตอนติดตั้งบนเครื่องเป้าหมาย

- [ ] สำรองฐานข้อมูลและไฟล์อัปโหลดก่อนเริ่มงาน พร้อมทดสอบว่าไฟล์สำรองอ่านได้
- [ ] สร้าง release artifact จาก commit/tag ที่ผ่าน strict gate และต้องไม่มีโฟลเดอร์ `update`
- [ ] ตั้งค่า production ด้วย `php init --env=Production --overwrite=All`
- [ ] กำหนด `DHDC_DB_DSN`, `DHDC_DB_USER`, `DHDC_DB_PASSWORD`, `DHDC_MAILER_DSN` และ cookie validation key ผ่าน secret store หรือไฟล์ local ที่จำกัดสิทธิ์
- [ ] ติดตั้ง Apache ตาม [apache-dhdc4.conf.example](apache-dhdc4.conf.example) และแก้ domain/certificate ให้ตรงกับเครื่องจริง
- [ ] ใช้บัญชีฐานข้อมูลตาม [database-service-account.sql.example](database-service-account.sql.example) โดยเปลี่ยน placeholder เป็นรหัสผ่านสุ่มจริง
- [ ] รัน migration/ขั้นตอนฐานข้อมูลที่ได้รับอนุมัติและบันทึกผล
- [ ] รัน read-only smoke test และ authenticated release test
- [ ] ตรวจ log ของ frontend, backend, Apache และฐานข้อมูลว่าไม่มี error ใหม่
- [ ] ให้ผู้ดูแลระบบ ผู้ดูแลข้อมูล และเจ้าของระบบลงนามอนุมัติ Go-Live

## 6. แผนย้อนกลับ

- [ ] ระบุ release เดิมที่สามารถย้อนกลับได้
- [ ] ระบุไฟล์ backup ฐานข้อมูลและไฟล์อัปโหลดที่ผ่านการตรวจแล้ว
- [ ] กำหนดผู้มีอำนาจสั่ง rollback และช่องทางติดต่อระหว่างช่วงติดตั้ง
- [ ] กำหนดเงื่อนไข rollback เช่น login ไม่ได้, Transform/QC ผิดพลาด, ข้อมูลรายงานคลาดเคลื่อน หรือเกิด error ต่อเนื่อง
- [ ] หลัง rollback ให้รัน smoke test และตรวจความครบถ้วนของข้อมูลซ้ำ

## 7. การอนุมัติ

| บทบาท | ชื่อ/ผู้รับผิดชอบ | ผลการพิจารณา | วันที่ |
|---|---|---|---|
| ผู้ดูแลระบบ |  | [ ] อนุมัติ [ ] ไม่อนุมัติ |  |
| ผู้ดูแลฐานข้อมูล/ข้อมูลสุขภาพ |  | [ ] อนุมัติ [ ] ไม่อนุมัติ |  |
| ผู้ทดสอบระบบ |  | [ ] อนุมัติ [ ] ไม่อนุมัติ |  |
| เจ้าของระบบ |  | [ ] อนุมัติ [ ] ไม่อนุมัติ |  |
