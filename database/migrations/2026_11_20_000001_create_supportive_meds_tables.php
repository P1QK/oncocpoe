<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('supportive_meds')) {
            Schema::create('supportive_meds', function (Blueprint $table) {
                $table->id();
                $table->string('sm_code', 20)->unique();
                $table->string('sm_name', 150);
                $table->string('category', 40)->index()->comment('pre_med_antiemetic, pre_med_hypersensitivity, pre_hydration, post_hydration, home_med');
                $table->string('default_dose', 50);
                $table->json('dose_options')->nullable();
                $table->string('default_route', 40)->default('IV');
                $table->string('default_instruction', 255)->nullable();
                $table->integer('sort_order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('regimen_supportive_meds')) {
            Schema::create('regimen_supportive_meds', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rh_id')->index();
                $table->string('sm_code', 20)->index();
                $table->string('override_dose', 50)->nullable();
                $table->string('override_route', 40)->nullable();
                $table->string('override_instruction', 255)->nullable();
                $table->boolean('is_default')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('order_supportive_meds')) {
            Schema::create('order_supportive_meds', function (Blueprint $table) {
                $table->id();
                $table->string('or_id', 20)->index();
                $table->string('sm_code', 20)->nullable();
                $table->string('sm_name', 150);
                $table->string('category', 40)->index();
                $table->string('dose', 50);
                $table->string('route', 40)->nullable();
                $table->string('instruction', 255)->nullable();
                $table->integer('sort_order')->default(1);
                $table->timestamps();
            });
        }

        // Seed Pre-medication Master List (Antiemetics & Anti-Hypersensitivity)
        $now = now();
        $preMeds = [
            // 1. Antiemetics (6 items)
            [
                'sm_code'             => 'PM_ONDA',
                'sm_name'             => 'Ondansetron inj.',
                'category'            => 'pre_med_antiemetic',
                'default_dose'        => '8 mg',
                'dose_options'        => json_encode(['8 mg', '12 mg', '16 mg', '24 mg']),
                'default_route'       => 'IV',
                'default_instruction' => 'IV push / IV drip in 15 mins ก่อนให้ยาเคมีบำบัด 30 นาที',
                'sort_order'          => 1,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'PM_DEXA',
                'sm_name'             => 'Dexamethasone inj.',
                'category'            => 'pre_med_antiemetic',
                'default_dose'        => '12 mg',
                'dose_options'        => json_encode(['8 mg', '10 mg', '12 mg', '20 mg']),
                'default_route'       => 'IV',
                'default_instruction' => 'IV drip in 15 mins ก่อนให้ยาเคมีบำบัด 30 นาที',
                'sort_order'          => 2,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'PM_OLAN',
                'sm_name'             => 'Olanzapine tab.',
                'category'            => 'pre_med_antiemetic',
                'default_dose'        => '5 mg',
                'dose_options'        => json_encode(['2.5 mg', '5 mg', '10 mg']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานก่อนให้ยาเคมีบำบัด / ก่อนนอน (Day 1)',
                'sort_order'          => 3,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'PM_METO',
                'sm_name'             => 'Metoclopramide inj.',
                'category'            => 'pre_med_antiemetic',
                'default_dose'        => '10 mg',
                'dose_options'        => json_encode(['10 mg', '20 mg']),
                'default_route'       => 'IV',
                'default_instruction' => 'IV push ช้าๆ ก่อนให้ยาเคมีบำบัด 30 นาที',
                'sort_order'          => 4,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'PM_PALO',
                'sm_name'             => 'Palonosetron inj.',
                'category'            => 'pre_med_antiemetic',
                'default_dose'        => '0.25 mg',
                'dose_options'        => json_encode(['0.25 mg']),
                'default_route'       => 'IV',
                'default_instruction' => 'IV push over 30 sec ก่อนให้ยาเคมีบำบัด 30 นาที',
                'sort_order'          => 5,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'PM_APRE',
                'sm_name'             => 'Aprepitant tab.',
                'category'            => 'pre_med_antiemetic',
                'default_dose'        => '125 mg',
                'dose_options'        => json_encode(['125 mg', '80 mg']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานก่อนให้ยาเคมีบำบัด 1 ชั่วโมง (Day 1)',
                'sort_order'          => 6,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],

            // 2. Prophylaxis for Hypersensitivity / Infusion Reaction (5 items)
            [
                'sm_code'             => 'PM_FAMO',
                'sm_name'             => 'Famotidine tab.',
                'category'            => 'pre_med_hypersensitivity',
                'default_dose'        => '20 mg',
                'dose_options'        => json_encode(['20 mg', '40 mg']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานก่อนให้ยาเคมีบำบัด 30-60 นาที',
                'sort_order'          => 1,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'PM_CPM',
                'sm_name'             => 'CPM inj.',
                'category'            => 'pre_med_hypersensitivity',
                'default_dose'        => '10 mg',
                'dose_options'        => json_encode(['10 mg', '20 mg']),
                'default_route'       => 'IV',
                'default_instruction' => 'IV push ช้าๆ ก่อนให้ยาเคมีบำบัด 30 นาที',
                'sort_order'          => 2,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'PM_HYDRO',
                'sm_name'             => 'Hydrocortisone inj.',
                'category'            => 'pre_med_hypersensitivity',
                'default_dose'        => '100 mg',
                'dose_options'        => json_encode(['50 mg', '100 mg', '200 mg']),
                'default_route'       => 'IV',
                'default_instruction' => 'IV push / IV drip in 15 mins ก่อนให้ยาเคมีบำบัด 30 นาที',
                'sort_order'          => 3,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'PM_ATRO',
                'sm_name'             => 'Atropine inj.',
                'category'            => 'pre_med_hypersensitivity',
                'default_dose'        => '0.25 mg',
                'dose_options'        => json_encode(['0.25 mg', '0.5 mg']),
                'default_route'       => 'SC / IV',
                'default_instruction' => 'SC หรือ IV ก่อนให้ Irinotecan (ป้องกัน Cholinergic syndrome)',
                'sort_order'          => 4,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'sm_code'             => 'PM_PARA',
                'sm_name'             => 'Paracetamol 500 mg. tab',
                'category'            => 'pre_med_hypersensitivity',
                'default_dose'        => '500 mg',
                'dose_options'        => json_encode(['500 mg (1 tab)', '1,000 mg (2 tabs)']),
                'default_route'       => 'PO',
                'default_instruction' => 'รับประทานก่อนให้ยาเคมีบำบัด 30-60 นาที',
                'sort_order'          => 5,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
        ];

        foreach ($preMeds as $pm) {
            DB::table('supportive_meds')->updateOrInsert(['sm_code' => $pm['sm_code']], $pm);
        }

        // Seed Default Pre-medication Mappings for Existing Regimens
        $regimenMappings = [
            'FOLFOX-4' => [
                ['sm_code' => 'PM_ONDA', 'override_dose' => '8 mg'],
                ['sm_code' => 'PM_DEXA', 'override_dose' => '8 mg'],
            ],
            'AC (Breast)' => [
                ['sm_code' => 'PM_ONDA', 'override_dose' => '8 mg'],
                ['sm_code' => 'PM_DEXA', 'override_dose' => '12 mg'],
                ['sm_code' => 'PM_APRE', 'override_dose' => '125 mg'],
            ],
            'Pacli+Carbo(AUC5)' => [
                ['sm_code' => 'PM_ONDA', 'override_dose' => '8 mg'],
                ['sm_code' => 'PM_DEXA', 'override_dose' => '20 mg'],
                ['sm_code' => 'PM_CPM',  'override_dose' => '10 mg'],
                ['sm_code' => 'PM_FAMO', 'override_dose' => '20 mg'],
            ],
            'Cisplatin+5FU(PF)' => [
                ['sm_code' => 'PM_ONDA', 'override_dose' => '8 mg'],
                ['sm_code' => 'PM_DEXA', 'override_dose' => '12 mg'],
                ['sm_code' => 'PM_APRE', 'override_dose' => '125 mg'],
            ],
            'FOLFIRI' => [
                ['sm_code' => 'PM_ONDA', 'override_dose' => '8 mg'],
                ['sm_code' => 'PM_DEXA', 'override_dose' => '8 mg'],
                ['sm_code' => 'PM_ATRO', 'override_dose' => '0.25 mg'],
            ],
            'Gem+Cisplatin(GC)' => [
                ['sm_code' => 'PM_ONDA', 'override_dose' => '8 mg'],
                ['sm_code' => 'PM_DEXA', 'override_dose' => '12 mg'],
                ['sm_code' => 'PM_OLAN', 'override_dose' => '5 mg'],
            ],
        ];

        foreach ($regimenMappings as $rhCode => $items) {
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
        Schema::dropIfExists('order_supportive_meds');
        Schema::dropIfExists('regimen_supportive_meds');
        Schema::dropIfExists('supportive_meds');
    }
};
