<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // 1. เพิ่มยา Ifosfamide (IFOS) ในตาราง medcyto (หากยังไม่มี) เพื่อรองรับการสั่งยาคู่กับ MESNA
        DB::table('medcyto')->updateOrInsert(
            ['dg_code' => 'IFOS'],
            [
                'dg_code'       => 'IFOS',
                'dg_name'       => 'Ifosfamide',
                'dg_brand'      => 'Holoxan',
                'dg_price'      => 1250.00,
                'dg_saleprc'    => 1550.00,
                'dg_conc'       => 50.00,
                'dg_vol'        => 20.00,
                'dg_recon'      => 'ละลายผงยา 1,000 mg ด้วย SWI 20 mL ได้ความเข้มข้น 50 mg/mL',
                'dg_precaution' => '[High-Alert / Urotoxic] ต้องให้ร่วมกับ MESNA (60-120% แบ่งให้ 3 ครั้ง ที่ 0-3-6 ชม.) และ Hydration เสมอเพื่อป้องกัน Hemorrhagic Cystitis',
                'dg_exprecon'   => 24,
                'dg_expdilute'  => 24,
                'dg_time'       => 'ชั่วโมง',
                'dg_fullname'   => 'Ifosfamide 1,000 mg Vial (50 mg/mL after recon)',
            ]
        );

        // 2. เพิ่มรายการ MESNA ในตาราง supportive_meds (category = post_medication)
        DB::table('supportive_meds')->updateOrInsert(
            ['sm_code' => 'POST_MESNA'],
            [
                'sm_code'             => 'POST_MESNA',
                'sm_name'             => 'MESNA inj.',
                'category'            => 'post_medication',
                'default_dose'        => '60% (แบ่งให้ 3 ครั้ง)',
                'dose_options'        => json_encode(['60%', '80%', '100%', '120%']),
                'default_route'       => 'IV',
                'default_instruction' => 'แบ่งให้ 3 ครั้ง ที่ 0, 3, 6 ชั่วโมง (ชั่วโมง 0 เลือกผสมในขวดยาเลย หรือแยกผสม NSS ต่างหาก)',
                'sort_order'          => 1,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('supportive_meds')->where('sm_code', 'POST_MESNA')->delete();
    }
};
