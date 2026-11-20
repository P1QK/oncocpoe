<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $homeMeds = [
            [
                'sm_code'             => 'HM_DEXA',
                'sm_name'             => 'Dexamethasone 4 mg tab.',
                'category'            => 'home_med',
                'default_dose'        => '8 mg (2 tabs)',
                'dose_options'        => json_encode(['4 mg (1 tab)', '8 mg (2 tabs)']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานวันละ 1 ครั้ง หลังอาหารเช้า วันที่ 2-4 หลังรับยา (Day 2-4)',
                'sort_order'          => 1,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HM_ONDA',
                'sm_name'             => 'Ondansetron 8 mg tab.',
                'category'            => 'home_med',
                'default_dose'        => '8 mg (1 tab)',
                'dose_options'        => json_encode(['8 mg q12h', '8 mg PRN']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานครั้งละ 1 เม็ด ทุก 12 ชม. หรือเวลามีอาการคลื่นไส้-อาเจียน (Day 2-3)',
                'sort_order'          => 2,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HM_APRE',
                'sm_name'             => 'Aprepitant 80 mg cap.',
                'category'            => 'home_med',
                'default_dose'        => '80 mg (1 cap)',
                'dose_options'        => json_encode(['80 mg (Day 2-3)']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานวันละ 1 แคปซูล เช้า วันที่ 2 และ 3 (ต่อจาก Day 1 125 mg)',
                'sort_order'          => 3,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HM_OLAN',
                'sm_name'             => 'Olanzapine 5 mg tab.',
                'category'            => 'home_med',
                'default_dose'        => '5 mg (1 tab)',
                'dose_options'        => json_encode(['2.5 mg', '5 mg', '10 mg']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานวันละ 1 เม็ด ก่อนนอน วันที่ 2-4 หลังรับยา',
                'sort_order'          => 4,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HM_METO',
                'sm_name'             => 'Metoclopramide 10 mg tab.',
                'category'            => 'home_med',
                'default_dose'        => '10 mg (1 tab)',
                'dose_options'        => json_encode(['10 mg tid', '10 mg PRN']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานครั้งละ 1 เม็ด ก่อนอาหาร 30 นาที เวลามีอาการคลื่นไส้ (PRN q8h)',
                'sort_order'          => 5,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HM_DOMP',
                'sm_name'             => 'Domperidone 10 mg tab.',
                'category'            => 'home_med',
                'default_dose'        => '10 mg (1 tab)',
                'dose_options'        => json_encode(['10 mg tid', '10 mg PRN']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานครั้งละ 1 เม็ด ก่อนอาหาร 30 นาที วันละ 3 ครั้ง หรือ PRN',
                'sort_order'          => 6,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HM_LORA',
                'sm_name'             => 'Lorazepam 0.5 mg tab.',
                'category'            => 'home_med',
                'default_dose'        => '0.5 mg (1 tab)',
                'dose_options'        => json_encode(['0.5 mg hs', '0.5 mg PRN', '1 mg hs']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานครั้งละ 1 เม็ด ก่อนนอน หรือเวลามีอาการกังวล/คลื่นไส้ (Anticipatory N/V)',
                'sort_order'          => 7,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HM_OMEP',
                'sm_name'             => 'Omeprazole 20 mg cap.',
                'category'            => 'home_med',
                'default_dose'        => '20 mg (1 cap)',
                'dose_options'        => json_encode(['20 mg OD', '20 mg bid']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานครั้งละ 1 แคปซูล ก่อนอาหารเช้า 30 นาที (ป้องกันระคายเคืองกระเพาะจาก Steroid)',
                'sort_order'          => 8,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HM_LOPE',
                'sm_name'             => 'Loperamide 2 mg cap.',
                'category'            => 'home_med',
                'default_dose'        => '2 mg (PRN)',
                'dose_options'        => json_encode(['2 mg PRN']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทาน 2 แคปซูลเมื่อเริ่มถ่ายเหลว ตามด้วย 1 แคปซูลทุก 2 ชม. (สำหรับ FOLFIRI / 5-FU)',
                'sort_order'          => 9,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HM_PARA',
                'sm_name'             => 'Paracetamol 500 mg tab.',
                'category'            => 'home_med',
                'default_dose'        => '500 mg (1-2 tabs)',
                'dose_options'        => json_encode(['500 mg', '1,000 mg']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานทุก 4-6 ชม. เวลาปวดหรือมีไข้ (ห้ามรับประทานเกิน 8 เม็ด/วัน)',
                'sort_order'          => 10,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HM_GCSF',
                'sm_name'             => 'Filgrastim (G-CSF) 300 mcg inj.',
                'category'            => 'home_med',
                'default_dose'        => '300 mcg',
                'dose_options'        => json_encode(['300 mcg x 3d', '300 mcg x 5d']),
                'default_route'       => 'SC',
                'default_instruction' => 'ฉีดเข้าชั้นใต้ผิวหนัง (SC) วันละ 1 ครั้ง เริ่มหลังครบคอร์สเคมีบำบัด 24-72 ชม.',
                'sort_order'          => 11,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
        ];

        foreach ($homeMeds as $item) {
            DB::table('supportive_meds')->updateOrInsert(['sm_code' => $item['sm_code']], $item);
        }

        // ผูกยากลับบ้านเริ่มต้นตามแต่ละสูตร Regimen
        $regimenHomeMeds = [
            'AC (Breast)' => [
                ['sm_code' => 'HM_DEXA', 'override_dose' => '8 mg (2 tabs)'],
                ['sm_code' => 'HM_APRE', 'override_dose' => '80 mg (Day 2-3)'],
                ['sm_code' => 'HM_METO', 'override_dose' => '10 mg PRN'],
                ['sm_code' => 'HM_OMEP', 'override_dose' => '20 mg OD'],
            ],
            'Cisplatin+5FU(PF)' => [
                ['sm_code' => 'HM_DEXA', 'override_dose' => '8 mg (2 tabs)'],
                ['sm_code' => 'HM_APRE', 'override_dose' => '80 mg (Day 2-3)'],
                ['sm_code' => 'HM_METO', 'override_dose' => '10 mg PRN'],
                ['sm_code' => 'HM_OMEP', 'override_dose' => '20 mg OD'],
            ],
            'Gem+Cisplatin(GC)' => [
                ['sm_code' => 'HM_DEXA', 'override_dose' => '8 mg (2 tabs)'],
                ['sm_code' => 'HM_OLAN', 'override_dose' => '5 mg'],
                ['sm_code' => 'HM_METO', 'override_dose' => '10 mg PRN'],
                ['sm_code' => 'HM_OMEP', 'override_dose' => '20 mg OD'],
            ],
            'FOLFIRI' => [
                ['sm_code' => 'HM_ONDA', 'override_dose' => '8 mg PRN'],
                ['sm_code' => 'HM_LOPE', 'override_dose' => '2 mg PRN'],
                ['sm_code' => 'HM_METO', 'override_dose' => '10 mg PRN'],
            ],
            'FOLFOX-4' => [
                ['sm_code' => 'HM_ONDA', 'override_dose' => '8 mg PRN'],
                ['sm_code' => 'HM_METO', 'override_dose' => '10 mg PRN'],
                ['sm_code' => 'HM_OMEP', 'override_dose' => '20 mg OD'],
            ],
            'Pacli+Carbo(AUC5)' => [
                ['sm_code' => 'HM_ONDA', 'override_dose' => '8 mg PRN'],
                ['sm_code' => 'HM_METO', 'override_dose' => '10 mg PRN'],
                ['sm_code' => 'HM_PARA', 'override_dose' => '500 mg'],
            ],
        ];

        foreach ($regimenHomeMeds as $rhCode => $items) {
            $reg = DB::table('reg_h')->where('rh_code', $rhCode)->first();
            if ($reg) {
                foreach ($items as $item) {
                    DB::table('regimen_supportive_meds')->updateOrInsert(
                        ['rh_id' => $reg->rh_id, 'sm_code' => $item['sm_code']],
                        [
                            'override_dose' => $item['override_dose'] ?? null,
                            'is_default'    => true,
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        $codes = ['HM_DEXA', 'HM_ONDA', 'HM_APRE', 'HM_OLAN', 'HM_METO', 'HM_DOMP', 'HM_LORA', 'HM_OMEP', 'HM_LOPE', 'HM_PARA', 'HM_GCSF'];
        DB::table('regimen_supportive_meds')->whereIn('sm_code', $codes)->delete();
        DB::table('supportive_meds')->whereIn('sm_code', $codes)->delete();
    }
};
