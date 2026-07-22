# รายงานทดสอบ DHDC4 Database Installer v4.0.1

วันที่ตรวจรับ: 22 กรกฎาคม 2569

ระบบทดสอบ: Windows, MariaDB 12.2.2, PHP 8.2.12

ฐานต้นฉบับ: `dhdc4` ที่พอร์ต 33061

ฐานทดสอบ: MariaDB instance แยกที่พอร์ต 33063 และ datadir แยกจากฐานต้นฉบับ

## ชุดส่งมอบ

| รายการ | ค่า |
|---|---:|
| Archive | `output/database-installer/dhdc4-database-installer-v4.0.1.zip` |
| Archive size | 56,621,660 bytes |
| Archive SHA-256 | `8ddb28938e0c8293ccb83b7f6589e25483a5b2a4656b0b118e74df3f266e9562` |
| SQL source size | 305,814,399 bytes |
| SQL source SHA-256 | `cc71cc6fb4718baec9f2ca0aa00d675ba24e4682ea5ff8c472ca4ffd044d24a2` |
| SQL parts | 15 files |
| SQL parts size รวม | 305,818,026 bytes |
| ไฟล์ทั้งหมดหลังแตก | 25 files |

ขนาด SQL หลังแบ่งมากกว่าต้นฉบับ 3,627 bytes เนื่องจากเปลี่ยน definer ให้ชัดเจน 514 จุดและแทน body ของ `z_update_definer` รุ่นเก่าด้วย procedure ตรวจแจ้งแบบปลอดภัย โดยไม่มีข้อมูลตั้งต้นเพิ่ม

## ผลทดสอบตัวสร้างแพ็กเกจ

| รายการ | ผล |
|---|---|
| อ่าน SQL จาก ZIP แบบ streaming | ผ่าน |
| ตรวจ UTF-8 ทุกบรรทัด | ผ่าน |
| แบ่งเฉพาะขอบเขต SQL statement และ delimiter | ผ่าน |
| จำนวน definer ที่แปลง | 514 จุด |
| `DEFINER=CURRENT_USER` คงเหลือ | 0 จุด |
| definer เก่าของ `root` หรือ `dhdc_app` ในชุด SQL | 0 จุด |
| สร้างซ้ำจาก source เดิม | ผ่าน: manifest hash และ hash ของ SQL parts ตรงกันทุกไฟล์ |
| Windows PowerShell syntax | ผ่าน |
| Linux Bash syntax | ผ่านด้วย Git Bash |
| Windows dry-run | ผ่าน |
| Linux dry-run | ผ่าน รวมการตรวจ SHA256SUMS ทุกไฟล์ |
| คู่มือผู้ดูแลระบบ | ผ่าน: แยก `INSTALL-WINDOWS-TH.md` และ `INSTALL-LINUX-TH.md` ชัดเจน |
| Exact final ZIP | ผ่าน: แตก ZIP, ตรวจ sidecar/internal SHA-256 และ dry-run จากไฟล์ที่แตกจริง |

Manifest ที่สร้างซ้ำทั้งสองรอบมี SHA-256 `e880f0798dfc1e38fd44dedc7bf376ea271d2407c32f8280d7be3236e276174c` และไม่พบความแตกต่างของ path, size, statement count หรือ SHA-256 ของ SQL part

## ผลติดตั้งบนฐานว่างรอบที่หนึ่ง

ตัวติดตั้ง Windows ตรวจ checksum, สร้างบัญชี `'dhdc4'@'localhost'`, ให้สิทธิ์เฉพาะ schema `dhdc4`, นำเข้า SQL 15 parts ภายใน MariaDB session เดียว และรัน verification สำเร็จ

ผลลัพธ์หลัก:

```text
DHDC4_VERIFY PASS 821 512 560 0 43 0
```

| Invariant | ผลจริง | สถานะ |
|---|---:|---|
| Base tables | 820 | ผ่าน |
| Views | 1 | ผ่าน |
| Functions | 103 | ผ่าน |
| Procedures | 409 | ผ่าน |
| Routines รวม | 512 | ผ่าน |
| Events | 1 | ผ่าน |
| Triggers | 0 | ผ่าน |
| ตารางและวิวรวม | 821 | ผ่าน |
| ตารางข้อมูลเป้าหมายว่าง | 560 | ผ่าน |
| ตารางเป้าหมายที่มีข้อมูลค้าง | 0 | ผ่าน |
| `sys_files` | 43 rows | ผ่าน |
| Application users | 0 rows | ผ่าน |
| Definer `'dhdc4'@'localhost'` | 514 objects | ผ่าน |
| Definer mismatch | 0 objects | ผ่าน |
| Database charset/collation | `utf8mb3` / `utf8mb3_general_ci` | ผ่าน |

ทดสอบบัญชี owner แล้วสามารถเชื่อมต่อเป็น `dhdc4@localhost`, เรียก function `AddZero` ได้ผล `007` และเรียก `z_update_definer` รุ่นปลอดภัยได้โดยไม่เขียน `mysql.proc`

ตัวติดตั้งบังคับรหัสผ่านของ `'dhdc4'@'localhost'` อย่างน้อย 32 ตัวอักษร และ SQL สำหรับสร้าง user ให้สิทธิเฉพาะ `dhdc4`.* โดยไม่มี global privilege หรือ `GRANT OPTION`

## เปรียบเทียบข้อมูลกับ Production Master

รัน `CHECKSUM TABLE` เทียบฐานต้นฉบับกับฐาน restore ครบ 820 base tables:

```text
tables_checked=820 unsupported=0 mismatches=0
```

จึงยืนยันได้ว่าการแยก SQL และจัดลำดับใหม่ไม่ทำให้ข้อมูลอ้างอิงหรือข้อมูลตั้งต้นเปลี่ยนแปลง

## ผลทดสอบรอบที่สอง

เรียกตัวติดตั้งซ้ำโดยไม่ระบุโหมด recreate ตัวติดตั้งหยุดก่อนเขียนข้อมูลและแจ้งว่าฐาน `dhdc4` มีอยู่แล้ว ค่า invariant ก่อนและหลังตรงกัน:

```text
before=821 512 43 0
after=821 512 43 0
Existing-database protection: PASS
```

## ผลทดสอบ Recreate และ backup

ทดสอบ `-Recreate -ConfirmRecreate` เฉพาะฐานทดสอบ โดยตั้ง backup directory ให้มีช่องว่างใน path เพื่อทดสอบ argument quoting ตัวติดตั้งสร้าง backup ก่อน drop ฐานและติดตั้งใหม่สำเร็จ:

| รายการ | ค่า |
|---|---|
| Backup size | 304,723,083 bytes |
| Backup SHA-256 | `9b316c756fb7871a35d95c967659dfc456a112638b20f27ff07e071132845d95` |
| SHA-256 verification | ผ่าน |
| Reinstall verification | `DHDC4_VERIFY PASS 821 512 560 0 43 0` |

จึงยืนยันว่าโหมด recreate สำรองฐานก่อนลบ รองรับ path ที่มีช่องว่าง และติดตั้งกลับได้ครบถ้วน ทั้งนี้ไม่ได้เรียกโหมดดังกล่าวกับฐานต้นฉบับพอร์ต 33061

## ผลทดสอบบัญชี Admin ตั้งต้น

ทดสอบ `tools/bootstrap-admin.php` กับ schema Master จริงครบทั้งโหมดตรวจสอบและโหมดสร้างจริง:

- `--use-default-credentials --dry-run` ผ่านต่อเนื่องสองรอบและไม่เพิ่มข้อมูล
- สร้าง username `admin` ด้วยรหัสผ่านตั้งต้นที่กำหนดได้จริง
- `password_verify`, profile และ RBAC role `Admin` ผ่าน
- การเรียกซ้ำเมื่อ `user` ไม่ว่างถูกปฏิเสธ
- ลบบัญชี integration-test ที่สร้างขึ้นแล้ว และ Master กลับมามี `user`, `profile`, `auth_assignment` เป็นศูนย์

ระหว่างทดสอบพบว่าตารางบัญชีเดิมเป็น MyISAM จึงไม่รองรับ transaction rollback สคริปต์ถูกแก้ให้ dry-run ไม่เขียนข้อมูลเลย และโหมดสร้างจริงใช้ table lock พร้อมล้าง partial rows หากเกิดข้อผิดพลาด จากนั้นทดสอบซ้ำผ่าน

## การตรวจเพิ่มเติม

- `mariadb-check --databases dhdc4 --quick --silent`: ผ่าน
- Windows/Linux preflight: PHP 8.2.12 และ extensions ที่ระบบใช้ผ่าน; Apache 2.4.58 ผ่าน
- Windows install log บันทึกเฉพาะสถานะที่กำหนดไว้; ทดสอบส่ง secret marker ผ่าน environment แล้วไม่พบ marker ใน log
- MariaDB error log: ไม่พบ error, corruption, crash หรือ assertion
- Yii2 console bootstrap และเชื่อมฐาน restore: ผ่าน ได้ค่า `821:512:43`
- Master baseline ต้นฉบับ: ผ่าน 560 ตารางว่าง, HDC Exchange result table เป็นศูนย์ และ `sys_files` 43 รายการ
- ไม่พบ Google API key, private key, JWT, local backup หรือ absolute workspace path ในแพ็กเกจ
- `output`, test datadir และ backup ยังถูก `.gitignore` ป้องกันไม่ให้เข้า Git

## ข้อจำกัดและงานก่อน Go-Live

1. ทดสอบ install จริงบน Windows/MariaDB ครบแล้ว ส่วน Linux ผ่าน Bash syntax และ dry-run แต่ควรรัน full restore อีกครั้งบน AlmaLinux 9 เครื่องเป้าหมายก่อนเปิดบริการ
2. ต้องกำหนดรหัสผ่านจริงของ `'dhdc4'@'localhost'` ผ่าน prompt หรือ secret manager ตอนติดตั้ง ห้ามใช้ค่าทดสอบหรือ commit ลง Git
3. ต้องหยุด Apache ก่อนใช้โหมด recreate โดยเฉพาะฐานที่มี MyISAM tables
4. ต้องตรวจ domain, TLS certificate/chain, HTTPS redirect และ HSTS บน URL จริงภายหลังนำขึ้นเครื่องเป้าหมาย
5. `event_scheduler` ต้องคงเป็น OFF ระหว่างติดตั้ง และเปิดหลังตรวจ timezone กับงาน `event_dhdc` แล้วเท่านั้น
6. Release tag `v4.0.0` เดิมไม่ได้ถูกแก้หรือย้าย รุ่นถัดไปที่เสนอคือ `v4.0.1` และยังไม่ได้ commit, tag หรือ push
