# แผนมาตรฐาน SQL รายงาน HDC/Data-Exchange

วันที่ตรวจล่าสุด: 2026-07-08

## สรุปสถานะ

- แก้ที่ชั้น runtime แล้วโดยย้าย logic กรอง `HOSPCODE` ของ HDC ไปไว้ที่ `components\ReportSqlHelper`.
- หน้า HDC `report-id` ใช้ helper กลางจุดเดียว แทน closure ที่เคยฝังอยู่ใน view.
- เพิ่ม automated audit ถาวรผ่าน `npm run audit:report-sql`.
- ยังไม่ได้ bulk update SQL ต้นฉบับในฐานข้อมูลรายงาน.

## Root Cause

ปัญหา `Unknown column 't.HOSPCODE' in 'ORDER BY'` เกิดจากการประกอบ filter เดิมต่อท้าย SQL เดิมโดยไม่เข้าใจโครงสร้าง query ระดับบนสุด ทำให้บางรายงานที่มี `ORDER BY`, `GROUP BY`, derived table หรือหลาย statement ถูกเติม `AND t.HOSPCODE IN (...)` ผิดตำแหน่ง หรืออ้าง alias ที่ไม่มีใน scope นั้น.

## สิ่งที่แก้แล้ว

- ใช้ `ReportSqlHelper::applyHospcodeFilter()` เพื่อแทรก filter ก่อน `GROUP BY`, `HAVING`, `ORDER BY`, `LIMIT`.
- หา expression ของ `HOSPCODE` ตาม context ของ SQL เช่น alias ใน `FROM`, derived table alias และ alias ใน select list.
- รองรับ SQL ที่มีหลาย statement โดยกรองเฉพาะ statement สุดท้ายที่เป็น query หลักของรายงาน.
- เพิ่ม `ReportSqlHelper::classifySql()` สำหรับ audit risk class ของ SQL รายงาน.
- เพิ่ม `tools\audit-report-queries.php` ให้สร้างและเรียก temp procedure จาก SQL รายงานจริง รวมทั้ง HDC SQL ที่ผ่าน helper filter แล้ว.

## ผล Audit ล่าสุด

คำสั่ง:

```powershell
npm run audit:report-sql
```

รายงาน:

- `output\query-audit\report-query-audit-20260708-165717.json`
- `output\query-audit\report-query-audit-latest.md`

ผลรวม:

| Module | ผลตรวจ |
| --- | ---: |
| HDC | ok 200 |
| Data-Exchange | ok 138 |
| Population | ok 6 |
| SQL Query | ok 2 |
| EHR | ok 6 |
| Import | ok 8 |
| QC | ok 33 |

Failure count: 0

Risk class ที่ยังควรใช้จัดลำดับปรับ SQL ต้นฉบับ HDC:

| Risk class | จำนวน |
| --- | ---: |
| `multiple_statements` | 157 |
| `no_top_level_where` | 90 |
| `no_hospcode` | 31 |
| `top_level_order_by` | 6 |
| `unresolved_hospcode_expression` | 3 |

## Candidate Report สำหรับปรับ SQL ต้นฉบับ

สร้างรายงาน candidate รายการที่ควร review ก่อนปรับ SQL ต้นฉบับแล้ว โดยยังไม่แก้ฐานข้อมูลจริง:

```powershell
npm run audit:report-sql:candidates
```

ผลลัพธ์ล่าสุด:

- `output\query-audit\report-sql-standardization-candidates-20260708-173157.json`
- `output\query-audit\report-sql-standardization-candidates-latest.json`
- `output\query-audit\report-sql-standardization-candidates-latest.md`

ไฟล์ JSON มีข้อมูลรายรายการครบตามเกณฑ์ก่อนแตะฐานข้อมูลจริง:

- `module`, `record_key`, `sql_field`, `title`
- `risk_level`, `risks`, `reasons`
- `before_sql`
- `after_sql_preview`
- `before_checksum`, `after_preview_checksum`
- `proposed_action`

สรุป candidate ล่าสุด:

| Module | Risk level | Count |
| --- | --- | ---: |
| HDC | high | 87 |
| HDC | low | 3 |
| HDC | medium | 109 |
| Data-Exchange | high | 52 |
| Data-Exchange | medium | 17 |
| SQL Query | medium | 2 |

หมายเหตุ: `after_sql_preview` เป็นผล dry-run จาก helper และ sample HOSPCODE เพื่อช่วย review เท่านั้น ห้ามนำไป bulk update ฐานข้อมูลโดยตรง ต้องผ่าน owner review, backup/export, temp procedure dry-run และ rollback ต่อรายการก่อนเสมอ.

## มาตรฐาน SQL ที่ควรปรับในฐานข้อมูลรายงาน

- SQL รายงานควรมี query หลักเป็น `SELECT ... FROM ...` เดียวที่ชัดเจน.
- ถ้ารายงานต้องกรองหน่วยบริการ ต้อง expose `HOSPCODE` ใน scope ระดับบนสุด หรือกำหนด alias ให้ชัดเจน.
- ถ้ามี `WHERE` ควรใช้ pattern `WHERE 1=1` เพื่อให้ระบบเติม filter เพิ่มได้ง่าย.
- `ORDER BY`, `GROUP BY`, `HAVING`, `LIMIT` ต้องอยู่ท้าย query หลักเท่านั้น.
- หลีกเลี่ยงหลาย statement ในช่อง SQL เดียว หากจำเป็นต้องใช้ ควรแยกเป็น preparation statement และ final select อย่างชัดเจน.

## แผน Backup, Dry-Run และ Rollback ก่อนแก้ SQL ต้นฉบับ

1. Export ตารางที่เกี่ยวข้องก่อนแก้:
   - `hdc_rpt_sql`
   - `sys_data_exchange`
   - ตารางรายงานอื่นที่พบใน audit scope
2. สร้าง staging table เช่น `hdc_rpt_sql_standardize_staging` เก็บ `rpt_id`, SQL เดิม, SQL ใหม่, checksum เดิม, checksum ใหม่ และผู้แก้ไข.
3. รัน dry-run โดยสร้าง temp procedure จาก SQL ใหม่ทุกตัว และเปรียบเทียบ schema ของ result set กับ SQL เดิม.
4. อนุมัติรายการเปลี่ยนแปลงเป็น batch เล็กตาม risk class ไม่ update ทั้งหมดพร้อมกัน.
5. หลัง deploy ให้รัน `npm run audit:report-sql` และตรวจ UI HDC/Data-Exchange ทุก viewport.
6. Rollback โดย restore SQL เดิมจาก staging/backup เฉพาะ `rpt_id` หรือ `ex_id` ที่ผิดพลาด.

## แผนตรวจซ้ำ

- รัน `npm run audit:report-sql` ก่อน merge และหลัง deploy.
- รัน `npm run verify:ui-layout -- --base=http://127.0.0.1:18170 --out=output/playwright/layout-<date>`.
- Sweep หน้า HDC `report-id` ทุกตัวใน viewport mobile, tablet, desktop, wide.
- ตรวจ Data-Exchange `report-id` และ `report-list` อย่างน้อยหนึ่งรอบทุก viewport.
- ถ้า audit พบ `failure_count > 0` ให้แก้ SQL/helper ก่อนปล่อยใช้งาน.
