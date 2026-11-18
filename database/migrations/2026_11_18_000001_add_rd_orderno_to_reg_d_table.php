<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reg_d') && !Schema::hasColumn('reg_d', 'rd_orderno')) {
            Schema::table('reg_d', function (Blueprint $table) {
                $table->integer('rd_orderno')->default(1)->after('rd_id')->comment('ลำดับการให้ยาในสูตร Regimen');
            });

            // กำหนดลำดับเริ่มต้น 1, 2, 3... ให้กับข้อมูลสูตรยาเดิมที่มีอยู่ในระบบ
            $regimenIds = DB::table('reg_d')->select('rd_id')->distinct()->pluck('rd_id');
            foreach ($regimenIds as $rhId) {
                $items = DB::table('reg_d')->where('rd_id', $rhId)->get();
                $seq = 1;
                foreach ($items as $item) {
                    DB::table('reg_d')
                        ->where('rd_id', $rhId)
                        ->where('rd_medcode', $item->rd_medcode)
                        ->where('rd_meddose', $item->rd_meddose)
                        ->where('rd_medtime', $item->rd_medtime)
                        ->update(['rd_orderno' => $seq++]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reg_d') && Schema::hasColumn('reg_d', 'rd_orderno')) {
            Schema::table('reg_d', function (Blueprint $table) {
                $table->dropColumn('rd_orderno');
            });
        }
    }
};
