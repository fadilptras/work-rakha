<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sales_forecast_settings ke Bahasa Inggris (English).
     */
    public function up(): void
    {
        Schema::table('sales_forecast_settings', function (Blueprint $table) {
            $table->renameColumn('tahun', 'year');
            $table->renameColumn('bulan_acuan', 'month');

            $table->unsignedSmallInteger('year')->change();
            $table->string('month', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_forecast_settings', function (Blueprint $table) {
            $table->renameColumn('year', 'tahun');
            $table->renameColumn('month', 'bulan_acuan');

            $table->year('tahun')->change();
            $table->string('bulan_acuan', 255)->change();
        });
    }
};