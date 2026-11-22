<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onc_dayrecord', function (Blueprint $table) {
            if (!Schema::hasColumn('onc_dayrecord', 'or_dispatch')) {
                $table->tinyInteger('or_dispatch')->default(0)->after('or_prep');
            }
            if (!Schema::hasColumn('onc_dayrecord', 'or_prepared_at')) {
                $table->timestamp('or_prepared_at')->nullable()->after('or_dispatch');
            }
            if (!Schema::hasColumn('onc_dayrecord', 'or_dispatched_at')) {
                $table->timestamp('or_dispatched_at')->nullable()->after('or_prepared_at');
            }
            if (!Schema::hasColumn('onc_dayrecord', 'or_dispatched_by')) {
                $table->string('or_dispatched_by', 150)->nullable()->after('or_dispatched_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('onc_dayrecord', function (Blueprint $table) {
            $cols = array_filter(
                ['or_dispatch', 'or_prepared_at', 'or_dispatched_at', 'or_dispatched_by'],
                fn ($c) => Schema::hasColumn('onc_dayrecord', $c)
            );
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
