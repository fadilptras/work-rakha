<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sales_incentive_settings ke Bahasa Inggris (English).
     */
    public function up(): void
    {
        Schema::table('sales_incentive_settings', function (Blueprint $table) {
            $table->dropUnique('sales_incentive_settings_unique_new');

            $table->renameColumn('tahun', 'year');
            $table->renameColumn('bulan', 'month');

            $table->unique(['year', 'month', 'type', 'basis', 'min_achievement'], 'sales_incentive_settings_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_incentive_settings', function (Blueprint $table) {
            $table->dropUnique('sales_incentive_settings_unique');

            $table->renameColumn('year', 'tahun');
            $table->renameColumn('month', 'bulan');

            $table->unique(['tahun', 'bulan', 'type', 'basis', 'min_achievement'], 'sales_incentive_settings_unique_new');
        });
    }
};