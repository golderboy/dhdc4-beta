# แผนปรับปรุงความปลอดภัยตาม OWASP

วันที่จัดทำ: 2026-07-09

ขอบเขต: ตรวจเฉพาะโปรเจค `D:\xampp\htdocs\dhdc4` และไม่แก้ไฟล์นอกโปรเจค

## สรุปผู้บริหาร

พบความเสี่ยงหลัก 7 รายการที่ reportable และ 1 รายการ deferred จากการตรวจแบบ `codex-security:validation` โดยใช้ static source-to-sink trace และ HTTP probe ที่ไม่เปลี่ยนข้อมูล ระบบเป็น Yii2/PHP และมีผิวโจมตีสำคัญคือ route ที่เปิด public โดยไม่ได้ตั้ง AccessControl, debug tooling เปิดอยู่, SQL ที่ต่อ string จาก request, SQL runner ที่ใช้ blacklist, CSRF ถูกปิดหลาย controller, import/archive ที่เข้าถึงได้โดยไม่ login, และ JSONP example ใต้ webroot

แผนนี้ยังไม่แก้ business flow ทันที แต่จัดลำดับปรับปรุงแบบลด blast radius: เริ่มจากปิด public/dev surface, เพิ่ม access/CSRF guard แบบ targeted, จากนั้น refactor SQL เป็น parameter binding ทีละจุดพร้อม regression test

## Findings ที่ Validate แล้ว

| ID | OWASP | ระดับ | สถานะ | หลักฐานหลัก |
| --- | --- | --- | --- | --- |
| CAND-001 | A05 Security Misconfiguration | High | reportable | `/debug` และ `/gii` HTTP 200, `frontend/web/index.php:2`, `frontend/config/main-local.php:12` |
| CAND-002 | A01 Broken Access Control | High | reportable | `modules/sqlquery/controllers/SqlscriptController.php:20`, `/sqlquery/sqlscript/index` HTTP 200 ไม่ login |
| CAND-003 | A01/A05 Data Exposure | High | reportable | `frontend/modules/hdc/controllers/DefaultController.php:16,77`, `show-sql.php:8`, unauthenticated SQL disclosure |
| CAND-004 | A03 Injection | High | reportable | `modules/ehr/controllers/DefaultController.php:14,61,99,123,140,162,183,199` |
| CAND-005 | A01/A08 Unsafe Import | High | reportable | `frontend/modules/import/controllers/AjaxController.php:14,205,236,288,313` |
| CAND-006 | A03 XSS/JSONP | Medium | reportable | `frontend/web/lib/map/leaflet-search/examples/search.php:99` |
| CAND-007 | A03 Injection + A01 | High | reportable | `modules/sqlquery/controllers/RunqueryController.php:15,66,110` |
| CAND-008 | A05 Info Disclosure | Medium | deferred | `info.php:2`, reachability depends on document root |

Validation artifact:

- `output/security/owasp-validation-20260709-094756/validation_summary.md`
- `output/security/owasp-validation-20260709-094756/candidate_ledger.jsonl`

## แผนปรับปรุงแบบไม่ทำ Flow พัง

### Phase 0: Safety Net ก่อนแก้

- เก็บ baseline route smoke test สำหรับหน้า login, dashboard, HDC, Data-Exchange, import, EHR และ SQL query
- เพิ่ม test session helper ที่ใช้เฉพาะ local/test เพื่อทดสอบ protected route โดยไม่ต้องใช้ password จริง
- ก่อนแก้ security guard ทุกจุด ให้บันทึก expected redirect/status ของ route เดิม
- ทุก change ต้องรัน lint PHP และ smoke UI เฉพาะ module ที่แตะ

### Phase 1: ปิด Public/Dev Surface ที่ไม่ควรเปิด

1. `frontend/web/index.php`
   - เปลี่ยน `YII_DEBUG` และ `YII_ENV` ให้อ่านจาก env/config ไม่ hard-code เป็น `true/dev`
   - production default ต้องเป็น `YII_DEBUG=false`, `YII_ENV=prod`

2. `frontend/config/main-local.php`
   - จำกัด debug/gii เฉพาะ local IP และเฉพาะ env dev จริง
   - ใน production ห้าม bootstrap `debug` และ `gii`

3. `info.php`
   - ลบออกจาก deploy path หรือบล็อกด้วย environment gate
   - ถ้าจำเป็นต้องเก็บเพื่อ local ให้ย้ายออกจาก webroot หรือให้ตอบ 404 ใน production

4. `frontend/web/lib/map/leaflet-search/examples/`
   - ไม่ deploy example/demo ใต้ webroot
   - ถ้ายังต้องใช้ endpoint ให้ validate callback ด้วย JavaScript identifier allowlist หรือเลิก JSONP ใช้ JSON/CORS แทน

### Phase 2: AccessControl และ CSRF

1. `modules/sqlquery/controllers/SqlscriptController.php`
   - เพิ่ม `AccessControl` ให้ทุก action ใช้ role ที่เหมาะสม
   - เปิด CSRF กลับมา
   - จำกัด upload/create/update/delete เฉพาะ Admin หรือ role เฉพาะงาน SQL

2. `frontend/modules/hdc/controllers/DefaultController.php`
   - เพิ่ม `show-sql` เข้า AccessControl
   - จำกัด `show-sql` เฉพาะ Admin/Pm หรือ role report-maintainer
   - encode output ใน `show-sql.php` ด้วย `Html::encode($show_sql)`

3. `frontend/modules/import/controllers/AjaxController.php`
   - เพิ่ม AccessControl ให้ import/update/truncate/test-correct ทั้งหมด
   - จำกัด method เป็น POST สำหรับ action ที่เปลี่ยน state
   - เปิด CSRF หรือใช้ signed one-time job token สำหรับ background import

4. Controller ที่ปิด CSRF
   - ทำ inventory จาก `rg "enableCsrfValidation = false"`
   - เปิดกลับทีละ module โดยเริ่มจาก SQL/import/EHR
   - ถ้าเป็น AJAX legacy ให้แก้ frontend ส่ง CSRF token แทนการปิดทั้ง controller

### Phase 3: Injection Controls

1. `modules/ehr/controllers/DefaultController.php`
   - เปลี่ยน raw SQL interpolation เป็น parameter binding ทุก query
   - validate `cid` เป็นเลข 13 หลัก, `hospcode` เป็นรหัสหน่วยบริการ, `seq/an` ตาม format ที่ใช้จริง
   - ยืนยัน object-level access: user หน่วยบริการทั่วไปควรเห็นเฉพาะข้อมูลหน่วยตนเอง ยกเว้น role ที่ได้รับอนุญาต

2. `modules/sqlquery/controllers/RunqueryController.php`
   - ห้ามใช้ blacklist string เป็น security boundary
   - ถ้าต้องให้ user query ได้ ให้ทำ read-only connection และ allow เฉพาะ single `SELECT`
   - ใช้ SQL parser/statement classifier หรือจำกัดเป็น saved report เท่านั้น
   - แยกสิทธิ์ “run query” ออกจาก role `User`

3. `frontend/modules/hdc` และ `frontend/modules/hdcex`
   - Parameterize route lookup (`id`, `ex_id`, `cat_id`) และ validate id format
   - ห้าม update metadata จาก GET โดยตรง เช่น `rpt_name`

### Phase 4: Import/Archive Hardening

- จำกัด filename ด้วย allowlist และ lookup จาก database record id แทนรับ path/filename ตรงจาก request
- ตรวจ zip entry ก่อน `extractTo`: reject absolute path, `..`, symlink/hardlink metadata, nested path แปลก
- extract ไป temp dir นอก webroot แล้วค่อย promote เฉพาะไฟล์ที่ผ่าน validation
- จำกัดขนาด zip, จำนวนไฟล์, recursive depth และ total uncompressed size
- ทำ import เป็น queued job พร้อม audit log แทน HTTP request ยาว

### Phase 5: Headers, Session, Logging

- เพิ่ม security headers: `Content-Security-Policy`, `X-Frame-Options` หรือ `frame-ancestors`, `X-Content-Type-Options`, `Referrer-Policy`
- ตั้ง session cookie `httpOnly`, `sameSite=Lax/Strict`, `secure` เมื่อใช้ HTTPS
- Error page production ต้องไม่แสดง stack trace
- Log security events: denied access, failed validation, SQL runner attempts, import job start/finish/fail

## ลำดับดำเนินการที่แนะนำ

1. ปิด debug/gii/info/demo endpoint ก่อน เพราะความเสี่ยงสูงและกระทบ flow ต่ำ
2. เพิ่ม AccessControl/CSRF ให้ `SqlscriptController`, `HDC show-sql`, `Import Ajax`
3. Refactor EHR SQL เป็น bound parameters พร้อม test ด้วย cid ปกติและ payload injection
4. ลดสิทธิ์ SQL runner หรือย้ายเป็น admin-only read-only flow
5. Hardening import zip extraction และ DATA_CORRECT
6. เพิ่ม headers/session hardening
7. ทำ OWASP regression checklist ใน release script

## Regression Gates

- `php -l` ทุกไฟล์ที่แก้
- smoke route: login, dashboard, HDC index/report-id, Data-Exchange index/report-id, EHR index, SQL query, import upload
- security route assertions:
  - `/debug` และ `/gii` ต้อง 404/403 ใน prod mode
  - `/hdc/default/show-sql` ต้อง redirect/403 เมื่อไม่ login
  - `/sqlquery/sqlscript/index` ต้อง redirect/403 เมื่อไม่ login
  - state-changing import/ajax routes ต้องปฏิเสธ GET และไม่มี CSRF
  - JSONP demo endpoint ต้องไม่สะท้อน callback หรือไม่ถูก deploy

## ข้อควรระวัง

- อย่าเปิด CSRF กลับทีเดียวทั้งระบบโดยไม่แก้ฟอร์ม/AJAX เพราะจะทำ flow พัง
- อย่าเปลี่ยน role mapping แบบกว้างก่อนทำ route inventory เพราะโมดูลโรงพยาบาลจำนวนมากพึ่ง RBAC เดิม
- อย่าแก้ SQL รายงาน/SQL runner ด้วย regex อย่างเดียว ให้แยก policy เป็น read-only, allowlist และ parameter binding
- อย่าลบ library folder ทั้งก้อนถ้ามี asset ที่ใช้งานจริง ให้ลบเฉพาะ examples/demo หรือ block ด้วย web server rule
