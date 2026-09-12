<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom DOI (Days of Inventory, satuannya hari) pada tabel sales_forecast_settings.
     */
    public function up(): void
    {
        Schema::table('sales_forecast_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('doi')->default(30)->after('ref_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_forecast_settings', function (Blueprint $table) {
            $table->dropColumn('doi');
        });
    }
};