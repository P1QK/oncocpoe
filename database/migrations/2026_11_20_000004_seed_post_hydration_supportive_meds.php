<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $postHydrations = [
            [
                'sm_code'             => 'POST_HYD_NSS',
                'sm_name'             => '0.9% NaCl (NSS) — Post-hydration',
                'category'            => 'post_hydration',
                'default_dose'        => '1,000 mL',
                'dose_options'        => json_encode(['500 mL', '1,000 mL']),
                'default_route'       => 'IV drip',
                'default_instruction' => 'IV drip in 2-4 hrs หลังให้ยาเคมีบำบัด (ป้องกัน Nephrotoxicity)',
                'sort_order'          => 1,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'POST_HYD_D5NSS',
                'sm_name'             => '5% D/NSS (หรือ 5% D/N/2) — Post-hydration',
                'category'            => 'post_hydration',
                'default_dose'        => '1,000 mL',
                'dose_options'        => json_encode(['500 mL', '1,000 mL']),
                'default_route'       => 'IV drip',
                'default_instruction' => 'IV drip in 4-6 hrs หลังให้ยาเคมีบำบัด',
                'sort_order'          => 2,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'POST_HYD_KCL',
                'sm_name'             => 'KCl (Potassium Chloride) inj. — Post',
                'category'            => 'post_hydration',
                'default_dose'        => '20 mEq',
                'dose_options'        => json_encode(['10 mEq', '20 mEq', '40 mEq']),
                'default_route'       => 'IV drip (ผสมในสารน้ำ)',
                'default_instruction' => 'ผสมในสารน้ำ Post-hydration (ป้องกัน Hypokalemia)',
                'sort_order'          => 3,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'POST_HYD_MGSO4',
                'sm_name'             => 'MgSO4 (Magnesium Sulfate) inj. — Post',
                'category'            => 'post_hydration',
                'default_dose'        => '1 g',
                'dose_options'        => json_encode(['1 g', '2 g']),
                'default_route'       => 'IV drip (ผสมในสารน้ำ)',
                'default_instruction' => 'ผสมในสารน้ำ Post-hydration (ป้องกัน Hypomagnesemia)',
                'sort_order'          => 4,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'POST_HYD_FURO',
                'sm_name'             => 'Furosemide (Lasix) inj. — Post',
                'category'            => 'post_hydration',
                'default_dose'        => '20 mg',
                'dose_options'        => json_encode(['20 mg', '40 mg']),
                'default_route'       => 'IV push',
                'default_instruction' => 'IV push (PRN หาก Urine output < 100 mL/hr หรือมีภาวะน้ำเกินหลังให้สารน้ำ)',
                'sort_order'          => 5,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'POST_HYD_ORAL',
                'sm_name'             => 'Oral Hydration (แนะนำดื่มน้ำหลังรับยา)',
                'category'            => 'post_hydration',
                'default_dose'        => '2,000 mL/day',
                'dose_options'        => json_encode(['1,500 mL/day', '2,000 mL/day', '3,000 mL/day']),
                'default_route'       => 'PO',
                'default_instruction' => 'ดื่มน้ำสะอาดอย่างน้อย 2-3 ลิตร/วัน ต่อเนื่อง 2-3 วันหลังรับยาเคมีบำบัด',
                'sort_order'          => 6,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
        ];

        foreach ($postHydrations as $item) {
            DB::table('supportive_meds')->updateOrInsert(['sm_code' => $item['sm_code']], $item);
        }

        // ผูก Post-hydration เริ่มต้นกับสูตรยาที่มีความเสี่ยงต่อไต / กระเพาะปัสสาวะ
        $regimenPostHydrations = [
            'Cisplatin+5FU(PF)' => [
                ['sm_code' => 'POST_HYD_NSS',   'override_dose' => '1,000 mL',    'override_instruction' => 'IV drip in 2-4 hrs หลังให้ Cisplatin เสร็จสิ้น'],
                ['sm_code' => 'POST_HYD_KCL',   'override_dose' => '20 mEq',      'override_instruction' => 'ผสมใน NSS Post-hydration 1,000 mL'],
                ['sm_code' => 'POST_HYD_MGSO4', 'override_dose' => '1 g',         'override_instruction' => 'ผสมใน NSS Post-hydration 1,000 mL'],
                ['sm_code' => 'POST_HYD_ORAL',  'override_dose' => '2,000 mL/day','override_instruction' => 'ดื่มน้ำอย่างน้อย 2 ลิตร/วัน ต่อเนื่อง 3 วันหลังรับยา'],
            ],
            'Gem+Cisplatin(GC)' => [
                ['sm_code' => 'POST_HYD_NSS',   'override_dose' => '1,000 mL',    'override_instruction' => 'IV drip in 2-4 hrs หลังให้ Cisplatin เสร็จสิ้น'],
                ['sm_code' => 'POST_HYD_KCL',   'override_dose' => '20 mEq',      'override_instruction' => 'ผสมใน NSS Post-hydration 1,000 mL'],
                ['sm_code' => 'POST_HYD_MGSO4', 'override_dose' => '1 g',         'override_instruction' => 'ผสมใน NSS Post-hydration 1,000 mL'],
                ['sm_code' => 'POST_HYD_ORAL',  'override_dose' => '2,000 mL/day','override_instruction' => 'ดื่มน้ำอย่างน้อย 2 ลิตร/วัน ต่อเนื่อง 3 วันหลังรับยา'],
            ],
            'AC (Breast)' => [
                ['sm_code' => 'POST_HYD_NSS',   'override_dose' => '500 mL',      'override_instruction' => 'IV drip in 1-2 hrs หลังให้ Cyclophosphamide'],
                ['sm_code' => 'POST_HYD_ORAL',  'override_dose' => '2,000 mL/day','override_instruction' => 'ดื่มน้ำมากๆ และปัสสาวะบ่อยๆ หลังรับยา Cyclophosphamide'],
            ],
        ];

        foreach ($regimenPostHydrations as $rhCode => $items) {
            $reg = DB::table('reg_h')->where('rh_code', $rhCode)->first();
            if ($reg) {
                foreach ($items as $item) {
                    DB::table('regimen_supportive_meds')->updateOrInsert(
                        ['rh_id' => $reg->rh_id, 'sm_code' => $item['sm_code']],
                        [
                            'override_dose'        => $item['override_dose'] ?? null,
                            'override_instruction' => $item['override_instruction'] ?? null,
                            'is_default'           => true,
                            'created_at'           => $now,
                            'updated_at'           => $now,
                        ]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        $codes = ['POST_HYD_NSS', 'POST_HYD_D5NSS', 'POST_HYD_KCL', 'POST_HYD_MGSO4', 'POST_HYD_FURO', 'POST_HYD_ORAL'];
        DB::table('regimen_supportive_meds')->whereIn('sm_code', $codes)->delete();
        DB::table('supportive_meds')->whereIn('sm_code', $codes)->delete();
    }
};
