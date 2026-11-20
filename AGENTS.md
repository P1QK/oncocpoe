# COPS (Chemotherapy Ordering & Preparation System) - Agent Guidelines

## Environment & MAMP Configuration (macOS)

โปรเจกต์นี้รันอยู่บนสภาพแวดล้อม **MAMP** บน macOS โดยมีพาธของ PHP, Composer และ MySQL ดังนี้:

- **PHP Binary Path:** `/Applications/MAMP/bin/php/php/bin/php` (PHP 8.3.30)
- **Composer Command:** `/Applications/MAMP/bin/php/php/bin/php /Applications/MAMP/bin/php/composer`
- **MySQL Socket:** `/Applications/MAMP/tmp/mysql/mysql.sock` (Port `8889`, Database `oncospr`)

## กฎการรันคำสั่ง `php artisan serve` และคำสั่งฐานข้อมูลใน Antigravity

1. **ห้ามเรียก `php` แบบไม่ระบุพาธเต็ม:** เนื่องจาก `php` ของ MAMP ไม่ได้อยู่ใน default system `$PATH` ของ Non-interactive shell ให้ใช้พาธเต็ม `/Applications/MAMP/bin/php/php/bin/php` เสมอ
2. **การรัน `php artisan serve`:**
   - ต้องรันด้วยคำสั่ง `/Applications/MAMP/bin/php/php/bin/php artisan serve`
   - ต้องตั้งค่า **`BypassSandbox: true`** และ **`IsDaemon: true`** ทันทีตั้งแต่ครั้งแรก เพื่อให้ PHP Development Server สามารถเชื่อมต่อกับ MAMP MySQL Socket (`/Applications/MAMP/tmp/mysql/mysql.sock`) ได้โดยไม่เกิดข้อผิดพลาด `SQLSTATE[HY000] [2002] Operation not permitted`
3. **การรัน `php artisan migrate` หรือ `db:seed`:**
   - ใช้คำสั่ง `/Applications/MAMP/bin/php/php/bin/php artisan migrate` พร้อมตั้งค่า **`BypassSandbox: true`** เพื่อเข้าถึง MySQL Socket ของ MAMP
4. **คำสั่งที่ไม่ใช้ฐานข้อมูล (เช่น เช็ค Syntax `php -l` หรือ `artisan view:clear` / `view:cache`):**
   - สามารถรันด้วย `/Applications/MAMP/bin/php/php/bin/php` ในโหมดปกติ (`BypassSandbox: false`) ได้ทันที

## กฎการอัปเดตเอกสารและสไลด์เผยแพร่ทุกครั้งที่มีการเพิ่มหรือแก้ไขระบบ

ทุกครั้งที่มีการเพิ่มฟีเจอร์ ปรับปรุงหน้าจอ หรือแก้ไขโครงสร้างฐานข้อมูลของโปรเจกต์ COPS ต้องอัปเดตไฟล์เอกสารทั้ง 2 ไฟล์นี้ควบคู่ไปด้วยเสมอ:
1. **เอกสารงานพัฒนาระบบ & ฐานข้อมูล:** `docs/COPS_System_Documentation.md` (อัปเดตตารางฐานข้อมูล/ฟีเจอร์ และเพิ่มรายการลงในส่วน Changelog)
2. **ชุดสไลด์เผยแพร่:** `docs/COPS_Presentation_Slides.html` (อัปเดตสไลด์ที่เกี่ยวข้องและสไลด์ Development Log)
