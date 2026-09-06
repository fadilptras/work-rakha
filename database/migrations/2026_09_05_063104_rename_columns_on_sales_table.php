<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sales ke Bahasa Inggris (English).
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('tanggal', 'date');
            $table->renameColumn('nama_customer', 'customer_name');
            $table->renameColumn('nama_produk', 'product_name');
            $table->renameColumn('satuan', 'unit');
            $table->renameColumn('hna', 'base_price');
            $table->renameColumn('diskon', 'discount');
            $table->renameColumn('harga_nett', 'net_price');
            $table->renameColumn('bulan', 'month');
            $table->string('month', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('date', 'tanggal');
            $table->renameColumn('customer_name', 'nama_customer');
            $table->renameColumn('product_name', 'nama_produk');
            $table->renameColumn('unit', 'satuan');
            $table->renameColumn('base_price', 'hna');
            $table->renameColumn('discount', 'diskon');
            $table->renameColumn('net_price', 'harga_nett');
            $table->renameColumn('month', 'bulan');
        });
    }
};