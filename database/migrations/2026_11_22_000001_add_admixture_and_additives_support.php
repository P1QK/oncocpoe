<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. สร้างตาราง Master สำหรับสารเติมแต่งที่ผสมลงในขวดยาเคมีบำบัด (chemo_additives)
        if (!Schema::hasTable('chemo_additives')) {
            Schema::create('chemo_additives', function (Blueprint $table) {
                $table->id();
                $table->string('ad_code', 20)->unique();
                $table->string('ad_name', 150);
                $table->string('default_dose', 30)->default('');
                $table->string('default_unit', 30)->default('mL');
                $table->decimal('default_vol_ml', 8, 2)->default(0);
                $table->integer('sort_order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. เพิ่มคอลัมน์ bag_group และ additives ใน reg_d, order_d และ onc_dayrecord
        if (Schema::hasTable('reg_d')) {
            Schema::table('reg_d', function (Blueprint $table) {
                if (!Schema::hasColumn('reg_d', 'rd_bag_group')) {
                    $table->string('rd_bag_group', 20)->nullable()->after('rd_orderno')->comment('กลุ่มขวด/ถุงที่ผสมรวมกัน เช่น BAG-A');
                }
                if (!Schema::hasColumn('reg_d', 'rd_additives')) {
                    $table->json('rd_additives')->nullable()->after('rd_solvol')->comment('สารเติมแต่งที่ผสมในขวดเดียวกัน (JSON)');
                }
            });
        }

        if (Schema::hasTable('order_d')) {
            Schema::table('order_d', function (Blueprint $table) {
                if (!Schema::hasColumn('order_d', 'or_bag_group')) {
                    $table->string('or_bag_group', 20)->nullable()->after('or_orderno')->comment('กลุ่มขวด/ถุงที่ผสมรวมกัน เช่น BAG-A');
                }
                if (!Schema::hasColumn('order_d', 'or_additives')) {
                    $table->json('or_additives')->nullable()->after('or_solvol')->comment('สารเติมแต่งที่ผสมในขวดเดียวกัน (JSON)');
                }
            });
        }

        if (Schema::hasTable('onc_dayrecord')) {
            Schema::table('onc_dayrecord', function (Blueprint $table) {
                if (!Schema::hasColumn('onc_dayrecord', 'or_bag_group')) {
                    $table->string('or_bag_group', 20)->nullable()->after('or_orderno')->comment('กลุ่มขวด/ถุงที่ผสมรวมกัน เช่น BAG-A');
                }
                if (!Schema::hasColumn('onc_dayrecord', 'or_additives')) {
                    $table->json('or_additives')->nullable()->after('or_solvol')->comment('สารเติมแต่งที่ผสมในขวดเดียวกัน (JSON)');
                }
            });
        }

        // 3. Seed สารเติมแต่งมาตรฐาน (chemo_additives)
        $now = now();
        $additives = [
            ['ad_code' => 'NAHCO3',  'ad_name' => '7.5% NaHCO3',            'default_dose' => '50',  'default_unit' => 'mEq', 'default_vol_ml' => 50.00,  'sort_order' => 1],
            ['ad_code' => 'MANN20',  'ad_name' => '20% Mannitol',           'default_dose' => '100', 'default_unit' => 'mL',  'default_vol_ml' => 100.00, 'sort_order' => 2],
            ['ad_code' => 'KCL',     'ad_name' => 'KCl (Potassium Chloride)','default_dose' => '20',  'default_unit' => 'mEq', 'default_vol_ml' => 10.00,  'sort_order' => 3],
            ['ad_code' => 'MGSO4',   'ad_name' => '50% MgSO4',              'default_dose' => '1',   'default_unit' => 'g',   'default_vol_ml' => 2.00,   'sort_order' => 4],
            ['ad_code' => 'HYDRO',   'ad_name' => 'Hydrocortisone inj.',    'default_dose' => '100', 'default_unit' => 'mg',  'default_vol_ml' => 2.00,   'sort_order' => 5],
            ['ad_code' => 'MESNA',   'ad_name' => 'MESNA inj.',             'default_dose' => '400', 'default_unit' => 'mg',  'default_vol_ml' => 4.00,   'sort_order' => 6],
            ['ad_code' => 'CAGLUC',  'ad_name' => '10% Calcium Gluconate',  'default_dose' => '1',   'default_unit' => 'g',   'default_vol_ml' => 10.00,  'sort_order' => 7],
            ['ad_code' => 'HEPARIN', 'ad_name' => 'Heparin inj.',           'default_dose' => '500', 'default_unit' => 'U',   'default_vol_ml' => 0.10,   'sort_order' => 8],
        ];

        foreach ($additives as $ad) {
            DB::table('chemo_additives')->updateOrInsert(
                ['ad_code' => $ad['ad_code']],
                array_merge($ad, ['is_active' => true, 'created_at' => $now, 'updated_at' => $now])
            );
        }

        // 4. เพิ่ม Vincristine (VINC) และ Etoposide (ETOP) ใน medcyto เพื่อรองรับสูตรผสมหลายตัวยา เช่น DA-EPOCH
        $extraMeds = [
            [
                'dg_code'       => 'VINC',
                'dg_name'       => 'Vincristine Sulfate',
                'dg_brand'      => 'Oncovin',
                'dg_price'      => 180.00,
                'dg_saleprc'    => 250.00,
                'dg_conc'       => 1.00,
                'dg_vol'        => 1.00,
                'dg_recon'      => 'สารละลายพร้อมใช้ 1 mg/mL (1 mg / 1 mL)',
                'dg_precaution' => '[FATAL IF GIVEN INTRATHECALLY!] สำหรับ IV เท่านั้น! ระวัง Vesicant เก็บ 2-8°C พ้นแสง',
                'dg_exprecon'   => 24,
                'dg_expdilute'  => 24,
                'dg_time'       => 'ชั่วโมง',
                'dg_fullname'   => 'Vincristine Sulfate 1 mg / 1 mL Injection (1 mg/mL)',
            ],
            [
                'dg_code'       => 'ETOP',
                'dg_name'       => 'Etoposide',
                'dg_brand'      => 'Vepesid',
                'dg_price'      => 260.00,
                'dg_saleprc'    => 340.00,
                'dg_conc'       => 20.00,
                'dg_vol'        => 5.00,
                'dg_recon'      => 'สารละลายเข้มข้น 20 mg/mL (100 mg / 5 mL)',
                'dg_precaution' => 'ความเข้มข้นหลังเจือจางต้องไม่เกิน 0.4 mg/mL (ตกตะกอนง่าย) ห้ามแช่เย็น หยดอย่างน้อย 30-60 นาที',
                'dg_exprecon'   => 24,
                'dg_expdilute'  => 24,
                'dg_time'       => 'ชั่วโมง',
                'dg_fullname'   => 'Etoposide 100 mg / 5 mL Injection (20 mg/mL)',
            ],
        ];

        foreach ($extraMeds as $em) {
            DB::table('medcyto')->updateOrInsert(['dg_code' => $em['dg_code']], $em);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('onc_dayrecord')) {
            Schema::table('onc_dayrecord', function (Blueprint $table) {
                if (Schema::hasColumn('onc_dayrecord', 'or_bag_group')) $table->dropColumn('or_bag_group');
                if (Schema::hasColumn('onc_dayrecord', 'or_additives')) $table->dropColumn('or_additives');
            });
        }
        if (Schema::hasTable('order_d')) {
            Schema::table('order_d', function (Blueprint $table) {
                if (Schema::hasColumn('order_d', 'or_bag_group')) $table->dropColumn('or_bag_group');
                if (Schema::hasColumn('order_d', 'or_additives')) $table->dropColumn('or_additives');
            });
        }
        if (Schema::hasTable('reg_d')) {
            Schema::table('reg_d', function (Blueprint $table) {
                if (Schema::hasColumn('reg_d', 'rd_bag_group')) $table->dropColumn('rd_bag_group');
                if (Schema::hasColumn('reg_d', 'rd_additives')) $table->dropColumn('rd_additives');
            });
        }
        Schema::dropIfExists('chemo_additives');
    }
};
