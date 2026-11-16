<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed onc_users (Superadmin, Admin, Doctor, Pharmacist, Nurse)
        $users = [
            [
                'username' => 'superadmin',
                'name' => 'ดร. ประยุทธ์ ดูเเลระบบ (Superadmin)',
                'email' => 'superadmin@cops.local',
                'role' => 'superadmin',
                'license_no' => 'SA-0001',
                'department' => 'ศูนย์สารสนเทศและมะเร็งวิทยา',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'username' => 'admin',
                'name' => 'คุณสุดา จัดการระบบ (Admin)',
                'email' => 'admin@cops.local',
                'role' => 'admin',
                'license_no' => 'AD-0001',
                'department' => 'ศูนย์มะเร็งวิทยา',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'username' => 'doctor',
                'name' => 'นพ. สมชาย รักษาดี',
                'email' => 'doctor@cops.local',
                'role' => 'doctor',
                'license_no' => 'ว.28491',
                'department' => 'อายุรศาสตร์มะเร็งวิทยา (Medical Oncology)',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'username' => 'pharmacist',
                'name' => 'ภญ. พิมพ์ชนก เตรียมยาแม่น',
                'email' => 'pharmacist@cops.local',
                'role' => 'pharmacist',
                'license_no' => 'ภ.19482',
                'department' => 'หน่วยผลิตยาปราศจากเชื้อและเคมีบำบัด',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
            [
                'username' => 'nurse',
                'name' => 'พย. กัลยา ดูแลใกล้ชิด',
                'email' => 'nurse@cops.local',
                'role' => 'nurse',
                'license_no' => 'พ.49102',
                'department' => 'หอผู้ป่วยเคมีบำบัด / OPD Chemo',
                'is_active' => true,
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $u) {
            DB::table('onc_users')->updateOrInsert(
                ['username' => $u['username']],
                array_merge($u, ['updated_at' => now(), 'created_at' => now()])
            );
        }

        // 2. Seed units
        $units = [
            ['un_code' => '01', 'un_unit' => 'mg'],
            ['un_code' => '02', 'un_unit' => 'g'],
            ['un_code' => '03', 'un_unit' => 'mg/m2'],
            ['un_code' => '04', 'un_unit' => 'AUC'],
            ['un_code' => '05', 'un_unit' => 'mcg'],
            ['un_code' => '06', 'un_unit' => 'Units'],
        ];
        foreach ($units as $u) {
            DB::table('units')->updateOrInsert(['un_code' => $u['un_code']], $u);
        }

        // 3. Seed solutions (สารน้ำ)
        $solutions = [
            ['so_code' => 'NSS', 'so_name' => '0.9% NaCl (NSS)'],
            ['so_code' => 'D5W', 'so_name' => '5% Dextrose in Water (D5W)'],
            ['so_code' => 'D5S', 'so_name' => '5% Dextrose in NSS (D5S)'],
            ['so_code' => 'SWI', 'so_name' => 'Sterile Water for Injection'],
            ['so_code' => 'NONE', 'so_name' => 'ไม่ผสมสารน้ำ (Undiluted)'],
        ];
        foreach ($solutions as $s) {
            DB::table('solutions')->updateOrInsert(['so_code' => $s['so_code']], $s);
        }

        // 4. Seed routes (วิถีทางการให้ยา)
        $routes = [
            ['rt_code' => '01', 'rt_name' => 'IV Drip (หยดเข้าหลอดเลือดดำ)'],
            ['rt_code' => '02', 'rt_name' => 'IV Push (ฉีดเข้าหลอดเลือดดำช้าๆ)'],
            ['rt_code' => '03', 'rt_name' => 'IV Continuous (หยดต่อเนื่อง)'],
            ['rt_code' => '04', 'rt_name' => 'SC (ฉีดเข้าชั้นใต้ผิวหนัง)'],
            ['rt_code' => '05', 'rt_name' => 'IT (Intrathecal)'],
        ];
        foreach ($routes as $r) {
            DB::table('routes')->updateOrInsert(['rt_code' => $r['rt_code']], $r);
        }

        // 5. Seed timing (ระยะเวลาการให้ยา)
        $timings = [
            ['tm_code' => '01', 'tm_time' => 'in 15 mins'],
            ['tm_code' => '02', 'tm_time' => 'in 30 mins'],
            ['tm_code' => '03', 'tm_time' => 'in 1 hr'],
            ['tm_code' => '04', 'tm_time' => 'in 2 hrs'],
            ['tm_code' => '05', 'tm_time' => 'in 3 hrs'],
            ['tm_code' => '06', 'tm_time' => 'in 4 hrs'],
            ['tm_code' => '07', 'tm_time' => 'in 22 hrs (Continuous)'],
            ['tm_code' => '08', 'tm_time' => 'in 24 hrs (Continuous)'],
            ['tm_code' => '09', 'tm_time' => 'IV Push slow 3-5 mins'],
        ];
        foreach ($timings as $t) {
            DB::table('timing')->updateOrInsert(['tm_code' => $t['tm_code']], $t);
        }

        // 6. Seed wardtbls (หอผู้ป่วย)
        $wards = [
            ['wd_code' => '000', 'wd_name' => 'OPD Chemo (ศูนย์เคมีบำบัดผู้ป่วยนอก)'],
            ['wd_code' => '001', 'wd_name' => 'C-6 อายุรกรรมชาย 1'],
            ['wd_code' => '002', 'wd_name' => 'B-7 อายุรกรรมหญิง 1'],
            ['wd_code' => '003', 'wd_name' => 'D-8 ศัลยกรรมมะเร็ง'],
            ['wd_code' => '004', 'wd_name' => 'นรีเวชกรรมมะเร็ง'],
            ['wd_code' => '005', 'wd_name' => 'พิเศษ 6/1'],
            ['wd_code' => '006', 'wd_name' => 'พิเศษ 7/1'],
        ];
        foreach ($wards as $w) {
            DB::table('wardtbls')->updateOrInsert(['wd_code' => $w['wd_code']], $w);
        }

        // 7. Seed medcyto (ยาเคมีบำบัด)
        $meds = [
            [
                'dg_code' => '5FU',
                'dg_name' => 'Fluorouracil (5-FU)',
                'dg_brand' => 'Ebewe 5-FU',
                'dg_price' => 120.00,
                'dg_saleprc' => 150.00,
                'dg_conc' => 50.00,
                'dg_vol' => 20.00,
                'dg_recon' => 'สารละลายพร้อมใช้ 50 mg/mL (1,000 mg / 20 mL)',
                'dg_precaution' => 'เก็บที่อุณหภูมิห้อง (15-25°C) พ้นแสง ห้ามแช่เย็นเพราะอาจตกตะกอน',
                'dg_exprecon' => 24,
                'dg_expdilute' => 24,
                'dg_time' => 'ชั่วโมง',
                'dg_fullname' => 'Fluorouracil 1,000 mg / 20 mL Injection (50 mg/mL)',
            ],
            [
                'dg_code' => 'LV',
                'dg_name' => 'Leucovorin Calcium',
                'dg_brand' => 'Rescuvolin',
                'dg_price' => 280.00,
                'dg_saleprc' => 350.00,
                'dg_conc' => 10.00,
                'dg_vol' => 30.00,
                'dg_recon' => 'สารละลายพร้อมใช้ 10 mg/mL (300 mg / 30 mL)',
                'dg_precaution' => 'เก็บในตู้เย็น 2-8°C พ้นแสง ห้ามผสมร่วมขวดเดียวกับ 5-FU (ตกตะกอน)',
                'dg_exprecon' => 24,
                'dg_expdilute' => 24,
                'dg_time' => 'ชั่วโมง',
                'dg_fullname' => 'Leucovorin Calcium 300 mg / 30 mL Injection (10 mg/mL)',
            ],
            [
                'dg_code' => 'OXAL',
                'dg_name' => 'Oxaliplatin',
                'dg_brand' => 'Eloxatin',
                'dg_price' => 1850.00,
                'dg_saleprc' => 2200.00,
                'dg_conc' => 5.00,
                'dg_vol' => 20.00,
                'dg_recon' => 'สารละลายเข้มข้น 5 mg/mL (100 mg / 20 mL)',
                'dg_precaution' => 'ห้ามผสมใน NSS หรือสารละลายที่มี Chloride! ต้องเจือจางใน D5W เท่านั้น พ้นแสง',
                'dg_exprecon' => 24,
                'dg_expdilute' => 24,
                'dg_time' => 'ชั่วโมง',
                'dg_fullname' => 'Oxaliplatin 100 mg / 20 mL Injection (5 mg/mL)',
            ],
            [
                'dg_code' => 'DOXO',
                'dg_name' => 'Doxorubicin HCl',
                'dg_brand' => 'Adriamycin',
                'dg_price' => 450.00,
                'dg_saleprc' => 550.00,
                'dg_conc' => 2.00,
                'dg_vol' => 25.00,
                'dg_recon' => 'สารละลายพร้อมใช้สีแดง 2 mg/mL (50 mg / 25 mL)',
                'dg_precaution' => '[High-Alert / Vesicant] ระวังยารั่วออกนอกหลอดเลือด เก็บ 2-8°C พ้นแสง',
                'dg_exprecon' => 24,
                'dg_expdilute' => 24,
                'dg_time' => 'ชั่วโมง',
                'dg_fullname' => 'Doxorubicin HCl 50 mg / 25 mL Injection (2 mg/mL)',
            ],
            [
                'dg_code' => 'CYCLO',
                'dg_name' => 'Cyclophosphamide',
                'dg_brand' => 'Endoxan',
                'dg_price' => 320.00,
                'dg_saleprc' => 400.00,
                'dg_conc' => 20.00,
                'dg_vol' => 25.00,
                'dg_recon' => 'ละลายผงยา 500 mg ด้วย SWI หรือ NSS 25 mL ได้ความเข้มข้น 20 mg/mL',
                'dg_precaution' => 'เขย่าจนละลายใส เก็บในตู้เย็น 2-8°C หรือใช้ภายใน 24 ชม.',
                'dg_exprecon' => 24,
                'dg_expdilute' => 24,
                'dg_time' => 'ชั่วโมง',
                'dg_fullname' => 'Cyclophosphamide 500 mg Vial (20 mg/mL after recon)',
            ],
            [
                'dg_code' => 'PACLI',
                'dg_name' => 'Paclitaxel',
                'dg_brand' => 'Taxol / Intaxel',
                'dg_price' => 1500.00,
                'dg_saleprc' => 1850.00,
                'dg_conc' => 6.00,
                'dg_vol' => 50.00,
                'dg_recon' => 'สารละลายพร้อมใช้ 6 mg/mL (300 mg / 50 mL)',
                'dg_precaution' => 'ต้องใช้ขวด/ถุง Non-PVC และสายให้ยาที่มี Inline Filter 0.22 micron ห้ามแช่เย็น',
                'dg_exprecon' => 24,
                'dg_expdilute' => 27,
                'dg_time' => 'ชั่วโมง',
                'dg_fullname' => 'Paclitaxel 300 mg / 50 mL Injection (6 mg/mL)',
            ],
            [
                'dg_code' => 'CARBO',
                'dg_name' => 'Carboplatin',
                'dg_brand' => 'Paraplatin',
                'dg_price' => 980.00,
                'dg_saleprc' => 1200.00,
                'dg_conc' => 10.00,
                'dg_vol' => 45.00,
                'dg_recon' => 'สารละลายพร้อมใช้ 10 mg/mL (450 mg / 45 mL)',
                'dg_precaution' => 'คำนวณขนาดยาตาม Calvert Formula: Dose (mg) = AUC x (GFR + 25) พ้นแสง',
                'dg_exprecon' => 24,
                'dg_expdilute' => 24,
                'dg_time' => 'ชั่วโมง',
                'dg_fullname' => 'Carboplatin 450 mg / 45 mL Injection (10 mg/mL)',
            ],
            [
                'dg_code' => 'CISPL',
                'dg_name' => 'Cisplatin',
                'dg_brand' => 'Platinol',
                'dg_price' => 350.00,
                'dg_saleprc' => 450.00,
                'dg_conc' => 1.00,
                'dg_vol' => 50.00,
                'dg_recon' => 'สารละลายพร้อมใช้ 1 mg/mL (50 mg / 50 mL)',
                'dg_precaution' => 'ต้องเจือจางใน NSS เท่านั้น ห้ามแช่ตู้เย็นเด็ดขาด (เกิดผลึกตกตะกอน) พ้นแสง',
                'dg_exprecon' => 24,
                'dg_expdilute' => 24,
                'dg_time' => 'ชั่วโมง',
                'dg_fullname' => 'Cisplatin 50 mg / 50 mL Injection (1 mg/mL)',
            ],
            [
                'dg_code' => 'GEMCI',
                'dg_name' => 'Gemcitabine',
                'dg_brand' => 'Gemzar',
                'dg_price' => 1100.00,
                'dg_saleprc' => 1350.00,
                'dg_conc' => 38.00,
                'dg_vol' => 26.30,
                'dg_recon' => 'ละลายผงยา 1,000 mg ด้วย NSS 25 mL ได้ความเข้มข้น 38 mg/mL',
                'dg_precaution' => 'เก็บที่อุณหภูมิห้อง ห้ามแช่ตู้เย็น (ตกผลึก)',
                'dg_exprecon' => 24,
                'dg_expdilute' => 24,
                'dg_time' => 'ชั่วโมง',
                'dg_fullname' => 'Gemcitabine 1,000 mg Vial (38 mg/mL)',
            ],
            [
                'dg_code' => 'IRIN',
                'dg_name' => 'Irinotecan HCl',
                'dg_brand' => 'Campto',
                'dg_price' => 1450.00,
                'dg_saleprc' => 1750.00,
                'dg_conc' => 20.00,
                'dg_vol' => 5.00,
                'dg_recon' => 'สารละลายพร้อมใช้ 20 mg/mL (100 mg / 5 mL)',
                'dg_precaution' => 'เจือจางใน D5W หรือ NSS เก็บพ้นแสง',
                'dg_exprecon' => 24,
                'dg_expdilute' => 24,
                'dg_time' => 'ชั่วโมง',
                'dg_fullname' => 'Irinotecan HCl 100 mg / 5 mL Injection (20 mg/mL)',
            ],
        ];
        foreach ($meds as $m) {
            DB::table('medcyto')->updateOrInsert(['dg_code' => $m['dg_code']], $m);
        }

        // 8. Seed Regimens (reg_h & reg_d)
        $regimens = [
            [
                'rh_code' => 'FOLFOX-4',
                'items' => [
                    ['rd_medcode' => 'OXAL', 'rd_meddose' => 85.00, 'rd_medunit' => '03', 'rd_sol' => 'D5W', 'rd_solvol' => 250.00, 'rd_medroute' => '01', 'rd_medtime' => '04', 'rd_start' => 1, 'rd_duration' => 1],
                    ['rd_medcode' => 'LV',   'rd_meddose' => 200.00, 'rd_medunit' => '03', 'rd_sol' => 'D5W', 'rd_solvol' => 250.00, 'rd_medroute' => '01', 'rd_medtime' => '04', 'rd_start' => 1, 'rd_duration' => 2],
                    ['rd_medcode' => '5FU',  'rd_meddose' => 400.00, 'rd_medunit' => '03', 'rd_sol' => 'NONE', 'rd_solvol' => 0.00,  'rd_medroute' => '02', 'rd_medtime' => '09', 'rd_start' => 1, 'rd_duration' => 2],
                    ['rd_medcode' => '5FU',  'rd_meddose' => 600.00, 'rd_medunit' => '03', 'rd_sol' => 'NSS', 'rd_solvol' => 500.00, 'rd_medroute' => '03', 'rd_medtime' => '07', 'rd_start' => 1, 'rd_duration' => 2],
                ],
            ],
            [
                'rh_code' => 'AC (Breast)',
                'items' => [
                    ['rd_medcode' => 'DOXO',  'rd_meddose' => 60.00,  'rd_medunit' => '03', 'rd_sol' => 'NSS', 'rd_solvol' => 100.00, 'rd_medroute' => '01', 'rd_medtime' => '02', 'rd_start' => 1, 'rd_duration' => 1],
                    ['rd_medcode' => 'CYCLO', 'rd_meddose' => 600.00, 'rd_medunit' => '03', 'rd_sol' => 'NSS', 'rd_solvol' => 250.00, 'rd_medroute' => '01', 'rd_medtime' => '03', 'rd_start' => 1, 'rd_duration' => 1],
                ],
            ],
            [
                'rh_code' => 'Pacli+Carbo(AUC5)',
                'items' => [
                    ['rd_medcode' => 'PACLI', 'rd_meddose' => 175.00, 'rd_medunit' => '03', 'rd_sol' => 'NSS', 'rd_solvol' => 500.00, 'rd_medroute' => '01', 'rd_medtime' => '05', 'rd_start' => 1, 'rd_duration' => 1],
                    ['rd_medcode' => 'CARBO', 'rd_meddose' => 5.00,   'rd_medunit' => '04', 'rd_sol' => 'D5W', 'rd_solvol' => 250.00, 'rd_medroute' => '01', 'rd_medtime' => '03', 'rd_start' => 1, 'rd_duration' => 1],
                ],
            ],
            [
                'rh_code' => 'Cisplatin+5FU(PF)',
                'items' => [
                    ['rd_medcode' => 'CISPL', 'rd_meddose' => 100.00,  'rd_medunit' => '03', 'rd_sol' => 'NSS', 'rd_solvol' => 500.00,  'rd_medroute' => '01', 'rd_medtime' => '04', 'rd_start' => 1, 'rd_duration' => 1],
                    ['rd_medcode' => '5FU',   'rd_meddose' => 1000.00, 'rd_medunit' => '03', 'rd_sol' => 'NSS', 'rd_solvol' => 1000.00, 'rd_medroute' => '03', 'rd_medtime' => '08', 'rd_start' => 1, 'rd_duration' => 4],
                ],
            ],
            [
                'rh_code' => 'FOLFIRI',
                'items' => [
                    ['rd_medcode' => 'IRIN', 'rd_meddose' => 180.00, 'rd_medunit' => '03', 'rd_sol' => 'D5W', 'rd_solvol' => 250.00, 'rd_medroute' => '01', 'rd_medtime' => '03', 'rd_start' => 1, 'rd_duration' => 1],
                    ['rd_medcode' => 'LV',   'rd_meddose' => 400.00, 'rd_medunit' => '03', 'rd_sol' => 'D5W', 'rd_solvol' => 250.00, 'rd_medroute' => '01', 'rd_medtime' => '04', 'rd_start' => 1, 'rd_duration' => 1],
                    ['rd_medcode' => '5FU',  'rd_meddose' => 400.00, 'rd_medunit' => '03', 'rd_sol' => 'NONE', 'rd_solvol' => 0.00,  'rd_medroute' => '02', 'rd_medtime' => '09', 'rd_start' => 1, 'rd_duration' => 1],
                    ['rd_medcode' => '5FU',  'rd_meddose' => 2400.00,'rd_medunit' => '03', 'rd_sol' => 'NSS', 'rd_solvol' => 500.00, 'rd_medroute' => '03', 'rd_medtime' => '08', 'rd_start' => 1, 'rd_duration' => 2],
                ],
            ],
            [
                'rh_code' => 'Gem+Cisplatin(GC)',
                'items' => [
                    ['rd_medcode' => 'GEMCI', 'rd_meddose' => 1000.00, 'rd_medunit' => '03', 'rd_sol' => 'NSS', 'rd_solvol' => 250.00, 'rd_medroute' => '01', 'rd_medtime' => '02', 'rd_start' => 1, 'rd_duration' => 1],
                    ['rd_medcode' => 'CISPL', 'rd_meddose' => 70.00,   'rd_medunit' => '03', 'rd_sol' => 'NSS', 'rd_solvol' => 500.00, 'rd_medroute' => '01', 'rd_medtime' => '04', 'rd_start' => 1, 'rd_duration' => 1],
                ],
            ],
        ];

        foreach ($regimens as $reg) {
            $existing = DB::table('reg_h')->where('rh_code', $reg['rh_code'])->first();
            if (!$existing) {
                $rhId = DB::table('reg_h')->insertGetId([
                    'rh_code' => $reg['rh_code'],
                    'rh_used' => 1,
                    'rh_created' => now(),
                ]);

                foreach ($reg['items'] as $item) {
                    DB::table('reg_d')->insert(array_merge(['rd_id' => $rhId], $item));
                }
            }
        }

        // 9. Seed 1 Sample Order if order_h is empty so Doctor & Pharmacist dashboards have immediate data
        if (DB::table('order_h')->count() === 0) {
            $orId = 'OR2611160001';
            $today = now()->format('Y-m-d');
            $tomorrow = now()->addDay()->format('Y-m-d');

            DB::table('order_h')->insert([
                'or_id'             => $orId,
                'or_guid'           => (string) \Illuminate\Support\Str::uuid(),
                'or_hn'             => '6701122',
                'or_patname'        => 'นาย กิตติพงศ์ ใจดี',
                'or_age'            => 58,
                'or_sex'            => 'M',
                'or_wt'             => 62.00,
                'or_ht'             => 168.00,
                'or_bsa'            => 1.70,
                'or_scr'            => 0.95,
                'or_gfr'            => 78.60,
                'or_reg_code'       => 'FOLFOX-4',
                'or_cycle'          => 2,
                'or_date'           => $today,
                'or_prnflag'        => 1,
                'or_chkflag'        => 0,
                'or_prepflag'       => 0,
                'or_sendflag'       => 0,
                'or_ward'           => '000',
                'or_daily'          => 0,
                'or_hold'           => 0,
                'or_confirm'        => 0,
                'or_adjust_percent' => 20.00,
                'or_adjust_reason'  => 'ภาวะเม็ดเลือดขาวต่ำ (Grade 3 Neutropenia) จาก Cycle ก่อนหน้า จึงพิจารณาปรับลดขนาดยาลง 20%',
                'or_doctor_id'      => 3,
                'or_doctor_name'    => 'นพ. สมชาย รักษาดี',
            ]);

            $sampleDetails = [
                [
                    'or_id'         => $orId,
                    'or_orderno'    => 1,
                    'or_medcode'    => 'OXAL',
                    'or_std_dose'   => 144.50,
                    'or_reduce_pct' => 20.00,
                    'or_meddose'    => 115.60,
                    'or_medunit'    => '01',
                    'or_soln'       => 'D5W',
                    'or_solvol'     => 250.00,
                    'or_medroute'   => '01',
                    'or_medtime'    => '04',
                    'or_start'      => $today,
                    'or_stop'       => $today,
                    'or_duration'   => 1,
                    'or_working'    => 23.12,
                    'or_totalvol'   => 273.12,
                    'or_text'       => 'ลดขนาดยา 20% (จาก 144.50 mg)',
                ],
                [
                    'or_id'         => $orId,
                    'or_orderno'    => 2,
                    'or_medcode'    => 'LV',
                    'or_std_dose'   => 340.00,
                    'or_reduce_pct' => 20.00,
                    'or_meddose'    => 272.00,
                    'or_medunit'    => '01',
                    'or_soln'       => 'D5W',
                    'or_solvol'     => 250.00,
                    'or_medroute'   => '01',
                    'or_medtime'    => '04',
                    'or_start'      => $today,
                    'or_stop'       => $tomorrow,
                    'or_duration'   => 2,
                    'or_working'    => 27.20,
                    'or_totalvol'   => 277.20,
                    'or_text'       => 'ลดขนาดยา 20% (จาก 340.00 mg)',
                ],
                [
                    'or_id'         => $orId,
                    'or_orderno'    => 3,
                    'or_medcode'    => '5FU',
                    'or_std_dose'   => 680.00,
                    'or_reduce_pct' => 20.00,
                    'or_meddose'    => 544.00,
                    'or_medunit'    => '01',
                    'or_soln'       => 'NONE',
                    'or_solvol'     => 0.00,
                    'or_medroute'   => '02',
                    'or_medtime'    => '09',
                    'or_start'      => $today,
                    'or_stop'       => $tomorrow,
                    'or_duration'   => 2,
                    'or_working'    => 10.88,
                    'or_totalvol'   => 10.88,
                    'or_text'       => 'ลดขนาดยา 20% (จาก 680.00 mg)',
                ],
                [
                    'or_id'         => $orId,
                    'or_orderno'    => 4,
                    'or_medcode'    => '5FU',
                    'or_std_dose'   => 1020.00,
                    'or_reduce_pct' => 20.00,
                    'or_meddose'    => 816.00,
                    'or_medunit'    => '01',
                    'or_soln'       => 'NSS',
                    'or_solvol'     => 500.00,
                    'or_medroute'   => '03',
                    'or_medtime'    => '07',
                    'or_start'      => $today,
                    'or_stop'       => $tomorrow,
                    'or_duration'   => 2,
                    'or_working'    => 16.32,
                    'or_totalvol'   => 516.32,
                    'or_text'       => 'ลดขนาดยา 20% (จาก 1,020.00 mg)',
                ],
            ];

            foreach ($sampleDetails as $sd) {
                DB::table('order_d')->insert($sd);
            }
        }
    }
}
