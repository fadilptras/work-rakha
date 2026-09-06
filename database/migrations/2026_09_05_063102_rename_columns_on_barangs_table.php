<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel barangs ke Bahasa Inggris (English).
     */
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropUnique('barangs_kode_barang_unique');
            $table->dropUnique('barangs_nama_barang_unique');

            $table->renameColumn('kode_barang', 'product_code');
            $table->renameColumn('nama_barang', 'product_name');
            $table->renameColumn('satuan', 'unit');
            $table->renameColumn('sediaan', 'presentation');
            $table->renameColumn('stok', 'stock');
            $table->renameColumn('stok_po', 'stock_po');
            $table->renameColumn('harga', 'base_price');
            $table->renameColumn('harga_satuan_terkecil', 'unit_price');

            $table->unique('product_code', 'barangs_product_code_unique');
            $table->unique('product_name', 'barangs_product_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropUnique('barangs_product_code_unique');
            $table->dropUnique('barangs_product_name_unique');

            $table->renameColumn('product_code', 'kode_barang');
            $table->renameColumn('product_name', 'nama_barang');
            $table->renameColumn('unit', 'satuan');
            $table->renameColumn('presentation', 'sediaan');
            $table->renameColumn('stock', 'stok');
            $table->renameColumn('stock_po', 'stok_po');
            $table->renameColumn('base_price', 'harga');
            $table->renameColumn('unit_price', 'harga_satuan_terkecil');

            $table->unique('kode_barang', 'barangs_kode_barang_unique');
            $table->unique('nama_barang', 'barangs_nama_barang_unique');
        });
    }
};