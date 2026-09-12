<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom ref_months (jumlah bulan acuan/referensi forecast).
     * Default 6 bulan sebagai acuan perhitungan.
     */
    public function up(): void
    {
        Schema::table('sales_forecast_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_forecast_settings', 'ref_months')) {
                $table->unsignedSmallInteger('ref_months')->default(6);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_forecast_settings', function (Blueprint $table) {
            if (Schema::hasColumn('sales_forecast_settings', 'ref_months')) {
                $table->dropColumn('ref_months');
            }
        });
    }
};