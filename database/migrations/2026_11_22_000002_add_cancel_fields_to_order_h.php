<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_h', function (Blueprint $table) {
            if (!Schema::hasColumn('order_h', 'or_cancel')) {
                $table->tinyInteger('or_cancel')->default(0)->after('or_confirm');
            }
            if (!Schema::hasColumn('order_h', 'or_cancel_reason')) {
                $table->text('or_cancel_reason')->nullable()->after('or_cancel');
            }
            if (!Schema::hasColumn('order_h', 'or_cancelled_by')) {
                $table->string('or_cancelled_by', 150)->nullable()->after('or_cancel_reason');
            }
            if (!Schema::hasColumn('order_h', 'or_cancelled_at')) {
                $table->timestamp('or_cancelled_at')->nullable()->after('or_cancelled_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_h', function (Blueprint $table) {
            $cols = array_filter(
                ['or_cancel', 'or_cancel_reason', 'or_cancelled_by', 'or_cancelled_at'],
                fn ($c) => Schema::hasColumn('order_h', $c)
            );
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
