<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Idempoten: ubah presisi hanya bila belum decimal(18,2).
     */
    public function up(): void
    {
        $type = DB::selectOne(
            "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales_incentive_settings'
             AND COLUMN_NAME = 'min_achievement'"
        );

        if ($type && ((array) $type)['COLUMN_TYPE'] !== 'decimal(18,2)') {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->decimal('min_achievement', 18, 2)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales_incentive_settings', function (Blueprint $table) {
            $table->decimal('min_achievement', 5, 2)->change();
        });
    }
};
