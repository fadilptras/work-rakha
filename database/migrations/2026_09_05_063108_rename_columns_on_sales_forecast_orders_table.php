<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sales_forecast_orders ke Bahasa Inggris (English).
     */
    public function up(): void
    {
        Schema::table('sales_forecast_orders', function (Blueprint $table) {
            $table->dropUnique('forecast_order_unique');

            $table->renameColumn('tahun', 'year');
            $table->renameColumn('bulan_acuan', 'month');
            $table->renameColumn('nama_produk', 'product_name');

            $table->unsignedSmallInteger('year')->change();
            $table->string('month', 20)->change();

            $table->unique(['year', 'month', 'product_name'], 'forecast_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_forecast_orders', function (Blueprint $table) {
            $table->dropUnique('forecast_order_unique');

            $table->renameColumn('year', 'tahun');
            $table->renameColumn('month', 'bulan_acuan');
            $table->renameColumn('product_name', 'nama_produk');

            $table->integer('tahun')->change();
            $table->string('bulan_acuan', 255)->change();

            $table->unique(['tahun', 'bulan_acuan', 'nama_produk'], 'forecast_order_unique');
        });
    }
};