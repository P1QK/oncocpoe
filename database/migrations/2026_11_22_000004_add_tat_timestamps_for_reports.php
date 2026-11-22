<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_h', function (Blueprint $table) {
            if (!Schema::hasColumn('order_h', 'or_created_at')) {
                $table->timestamp('or_created_at')->nullable()->after('or_date');
            }
        });

        Schema::table('onc_dayrecord', function (Blueprint $table) {
            if (!Schema::hasColumn('onc_dayrecord', 'or_administered_at')) {
                $table->timestamp('or_administered_at')->nullable()->after('or_send');
            }
            if (!Schema::hasColumn('onc_dayrecord', 'or_administered_by')) {
                $table->string('or_administered_by', 150)->nullable()->after('or_administered_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_h', function (Blueprint $table) {
            if (Schema::hasColumn('order_h', 'or_created_at')) {
                $table->dropColumn('or_created_at');
            }
        });

        Schema::table('onc_dayrecord', function (Blueprint $table) {
            $cols = array_filter(
                ['or_administered_at', 'or_administered_by'],
                fn ($c) => Schema::hasColumn('onc_dayrecord', $c)
            );
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
