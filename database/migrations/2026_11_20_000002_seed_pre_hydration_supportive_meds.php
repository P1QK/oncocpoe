<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $preHydrations = [
            [
                'sm_code'             => 'HYD_NSS1000',
                'sm_name'             => '0.9% NaCl (NSS)',
                'category'            => 'pre_hydration',
                'default_dose'        => '1,000 mL',
                'dose_options'        => json_encode(['500 mL', '1,000 mL']),
                'default_route'       => 'IV drip',
                'default_instruction' => 'IV drip in 2 hrs ก่อนให้ยาเคมีบำบัด',
                'sort_order'          => 1,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HYD_D5NSS',
                'sm_name'             => '5% D/NSS (หรือ 5% D/N/2)',
                'category'            => 'pre_hydration',
                'default_dose'        => '1,000 mL',
                'dose_options'        => json_encode(['500 mL', '1,000 mL']),
                'default_route'       => 'IV drip',
                'default_instruction' => 'IV drip in 2 hrs ก่อนให้ยาเคมีบำบัด',
                'sort_order'          => 2,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HYD_KCL',
                'sm_name'             => 'KCl (Potassium Chloride) inj.',
                'category'            => 'pre_hydration',
                'default_dose'        => '20 mEq',
                'dose_options'        => json_encode(['10 mEq', '20 mEq', '40 mEq']),
                'default_route'       => 'IV drip (ผสมในสารน้ำ)',
                'default_instruction' => 'ผสมในสารน้ำ Pre-hydration หยดร่วมก่อนให้ Cisplatin',
                'sort_order'          => 3,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HYD_MGSO4',
                'sm_name'             => 'MgSO4 (Magnesium Sulfate) inj.',
                'category'            => 'pre_hydration',
                'default_dose'        => '1 g',
                'dose_options'        => json_encode(['1 g', '2 g']),
                'default_route'       => 'IV drip (ผสมในสารน้ำ)',
                'default_instruction' => 'ผสมในสารน้ำ Pre-hydration (ป้องกัน Hypomagnesemia)',
                'sort_order'          => 4,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HYD_MANN',
                'sm_name'             => '20% Mannitol',
                'category'            => 'pre_hydration',
                'default_dose'        => '100 mL (20 g)',
                'dose_options'        => json_encode(['62.5 mL (12.5 g)', '100 mL (20 g)', '125 mL (25 g)']),
                'default_route'       => 'IV drip',
                'default_instruction' => 'IV drip in 30 mins ก่อนเริ่ม Cisplatin (Forced diuresis)',
                'sort_order'          => 5,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'HYD_FURO',
                'sm_name'             => 'Furosemide (Lasix) inj.',
                'category'            => 'pre_hydration',
                'default_dose'        => '20 mg',
                'dose_options'        => json_encode(['20 mg', '40 mg']),
                'default_route'       => 'IV push',
                'default_instruction' => 'IV push (PRN หาก Urine output < 100 mL/hr หรือมีภาวะน้ำเกิน)',
                'sort_order'          => 6,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
        ];

        foreach ($preHydrations as $item) {
            DB::table('supportive_meds')->updateOrInsert(['sm_code' => $item['sm_code']], $item);
        }

        // ผูก Pre-hydration เริ่มต้นกับสูตรยาที่ต้องให้สารน้ำก่อนเคมีบำบัด
        $regimenHydrations = [
            'Cisplatin+5FU(PF)' => [
                ['sm_code' => 'HYD_NSS1000', 'override_dose' => '1,000 mL', 'override_instruction' => 'IV drip in 2 hrs ก่อนให้ Cisplatin'],
                ['sm_code' => 'HYD_KCL',     'override_dose' => '20 mEq',   'override_instruction' => 'ผสมใน NSS Pre-hydration 1,000 mL'],
                ['sm_code' => 'HYD_MGSO4',   'override_dose' => '1 g',      'override_instruction' => 'ผสมใน NSS Pre-hydration 1,000 mL'],
                ['sm_code' => 'HYD_MANN',    'override_dose' => '100 mL (20 g)', 'override_instruction' => 'IV drip in 30 mins ก่อนเริ่ม Cisplatin'],
            ],
            'Gem+Cisplatin(GC)' => [
                ['sm_code' => 'HYD_NSS1000', 'override_dose' => '1,000 mL', 'override_instruction' => 'IV drip in 2 hrs ก่อนให้ Cisplatin'],
                ['sm_code' => 'HYD_KCL',     'override_dose' => '20 mEq',   'override_instruction' => 'ผสมใน NSS Pre-hydration 1,000 mL'],
                ['sm_code' => 'HYD_MGSO4',   'override_dose' => '1 g',      'override_instruction' => 'ผสมใน NSS Pre-hydration 1,000 mL'],
            ],
            'AC (Breast)' => [
                ['sm_code' => 'HYD_NSS1000', 'override_dose' => '500 mL',   'override_instruction' => 'IV drip in 1 hr ก่อนให้ Cyclophosphamide'],
            ],
        ];

        foreach ($regimenHydrations as $rhCode => $items) {
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
        $codes = ['HYD_NSS1000', 'HYD_D5NSS', 'HYD_KCL', 'HYD_MGSO4', 'HYD_MANN', 'HYD_FURO'];
        DB::table('regimen_supportive_meds')->whereIn('sm_code', $codes)->delete();
        DB::table('supportive_meds')->whereIn('sm_code', $codes)->delete();
    }
};
