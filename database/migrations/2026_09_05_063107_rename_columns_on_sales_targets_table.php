<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sales_targets ke Bahasa Inggris (English).
     */
    public function up(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropUnique('sales_targets_tahun_bulan_angka_ps_unique');

            $table->renameColumn('tahun', 'year');
            $table->renameColumn('bulan_angka', 'month_number');
            $table->renameColumn('bulan', 'month');
            $table->renameColumn('sales_last_year_amount', 'last_year_amount');

            $table->unique(['year', 'month_number', 'ps'], 'sales_targets_year_month_number_ps_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropUnique('sales_targets_year_month_number_ps_unique');

            $table->renameColumn('year', 'tahun');
            $table->renameColumn('month_number', 'bulan_angka');
            $table->renameColumn('month', 'bulan');
            $table->renameColumn('last_year_amount', 'sales_last_year_amount');

            $table->unique(['tahun', 'bulan_angka', 'ps'], 'sales_targets_tahun_bulan_angka_ps_unique');
        });
    }
};