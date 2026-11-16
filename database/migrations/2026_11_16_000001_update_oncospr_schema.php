<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Convert existing tables to utf8mb4 so Thai characters and symbols work properly
        $tables = [
            'reg_h', 'reg_d', 'order_h', 'order_d', 'onc_dayrecord',
            'medcyto', 'solutions', 'routes', 'timing', 'units', 'wardtbls'
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl)) {
                DB::statement("ALTER TABLE `{$tbl}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
        }

        // 2. Add new columns to order_h for BSA/GFR calculation & dose adjustment reason
        if (Schema::hasTable('order_h')) {
            Schema::table('order_h', function (Blueprint $table) {
                if (!Schema::hasColumn('order_h', 'or_age')) {
                    $table->integer('or_age')->nullable()->after('or_patname');
                }
                if (!Schema::hasColumn('order_h', 'or_sex')) {
                    $table->string('or_sex', 10)->nullable()->after('or_age');
                }
                if (!Schema::hasColumn('order_h', 'or_scr')) {
                    $table->decimal('or_scr', 6, 2)->nullable()->after('or_bsa')->comment('Serum Creatinine (mg/dL)');
                }
                if (!Schema::hasColumn('order_h', 'or_gfr')) {
                    $table->decimal('or_gfr', 8, 2)->nullable()->after('or_scr')->comment('GFR / CrCl (mL/min)');
                }
                if (!Schema::hasColumn('order_h', 'or_reg_code')) {
                    $table->string('or_reg_code', 30)->nullable()->after('or_gfr')->comment('Regimen Code');
                }
                if (!Schema::hasColumn('order_h', 'or_cycle')) {
                    $table->integer('or_cycle')->default(1)->after('or_reg_code')->comment('Cycle number');
                }
                if (!Schema::hasColumn('order_h', 'or_adjust_percent')) {
                    $table->decimal('or_adjust_percent', 5, 2)->default(0)->after('or_confirm')->comment('% การปรับลดขนาดยา');
                }
                if (!Schema::hasColumn('order_h', 'or_adjust_reason')) {
                    $table->text('or_adjust_reason')->nullable()->after('or_adjust_percent')->comment('เหตุผลในการปรับลดขนาดยา');
                }
                if (!Schema::hasColumn('order_h', 'or_doctor_id')) {
                    $table->unsignedBigInteger('or_doctor_id')->nullable()->after('or_adjust_reason');
                }
                if (!Schema::hasColumn('order_h', 'or_doctor_name')) {
                    $table->string('or_doctor_name', 150)->nullable()->after('or_doctor_id');
                }
                if (!Schema::hasColumn('order_h', 'or_pharmacist_id')) {
                    $table->unsignedBigInteger('or_pharmacist_id')->nullable()->after('or_doctor_name');
                }
                if (!Schema::hasColumn('order_h', 'or_pharmacist_name')) {
                    $table->string('or_pharmacist_name', 150)->nullable()->after('or_pharmacist_id');
                }
                if (!Schema::hasColumn('order_h', 'or_confirmed_at')) {
                    $table->timestamp('or_confirmed_at')->nullable()->after('or_pharmacist_name');
                }
            });
        }

        // 3. Add helper columns to order_d for initial calculated dose, reduction %, and duration
        if (Schema::hasTable('order_d')) {
            Schema::table('order_d', function (Blueprint $table) {
                if (!Schema::hasColumn('order_d', 'or_std_dose')) {
                    $table->decimal('or_std_dose', 8, 2)->nullable()->after('or_medcode')->comment('ขนาดยาตามสูตรคูณ BSA/GFR ก่อนลด %');
                }
                if (!Schema::hasColumn('order_d', 'or_reduce_pct')) {
                    $table->decimal('or_reduce_pct', 5, 2)->default(0)->after('or_std_dose')->comment('% ลดขนาดยา');
                }
                if (!Schema::hasColumn('order_d', 'or_duration')) {
                    $table->integer('or_duration')->default(1)->after('or_stop')->comment('จำนวนวันที่ให้ยา');
                }
            });
        }
    }

    public function down(): void
    {
        // Keep existing schema intact
    }
};
