<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Merge products (master) + product_clean (curation) into a single
     * combined products table (england naming, consistent with product_prices).
     * barangs tetap sebagai backup data mentah.
     */
    public function up(): void
    {
        $cols = fn () => array_column(DB::select('SHOW COLUMNS FROM products'), 'Field');

        // Drop legacy FK/index yang menunjuk ke barang_id
        try { DB::statement('ALTER TABLE products DROP FOREIGN KEY products_clean_barang_id_foreign'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE products DROP INDEX products_clean_barang_id_unique'); } catch (\Throwable $e) {}

        // Drop is_active (tidak terpakai di tabel ini)

        // --- Rename ke penamaan English final ---
        if (in_array('code', $cols()) && !in_array('product_code', $cols()))
            DB::statement('ALTER TABLE products CHANGE COLUMN code product_code VARCHAR(50) NULL');
        if (in_array('name_product', $cols()) && !in_array('product_name', $cols()))
            DB::statement('ALTER TABLE products CHANGE COLUMN name_product product_name VARCHAR(255) NULL');
        if (in_array('code_product', $cols()) && !in_array('product_code', $cols()))
            DB::statement('ALTER TABLE products CHANGE COLUMN code_product product_code VARCHAR(50) NULL');
        if (in_array('name_product_clean', $cols()) && !in_array('product_name_clean', $cols()))
            DB::statement('ALTER TABLE products CHANGE COLUMN name_product_clean product_name_clean VARCHAR(255) NULL');
        if (in_array('code_product_clean', $cols()) && !in_array('product_code_clean', $cols()))
            DB::statement('ALTER TABLE products CHANGE COLUMN code_product_clean product_code_clean VARCHAR(50) NULL');
        if (in_array('satuan_jual', $cols()) && !in_array('sell_unit', $cols()))
            DB::statement('ALTER TABLE products CHANGE COLUMN satuan_jual sell_unit VARCHAR(50) NULL');
        if (in_array('stock', $cols()) && !in_array('stock', $cols()))
            DB::statement('ALTER TABLE products CHANGE COLUMN stock stock INT NOT NULL DEFAULT 0');
        if (in_array('stock_unit', $cols()) && !in_array('stock', $cols()))
            DB::statement('ALTER TABLE products CHANGE COLUMN stock_unit stock INT NOT NULL DEFAULT 0');
        if (in_array('stock_unit_dalam_satuan_jual', $cols()) && !in_array('stock_in_sell_unit', $cols()))
            DB::statement('ALTER TABLE products CHANGE COLUMN stock_unit_dalam_satuan_jual stock_in_sell_unit INT NULL');

        // Drop kolom curasi lama yang tidak terpakai lagi
        foreach (['base_unit', 'presentation', 'is_active'] as $col) {
            if (in_array($col, $cols())) {
                try { DB::statement("ALTER TABLE products DROP COLUMN $col"); } catch (\Throwable $e) {}
            }
        }

        // --- Tambah kolom gabung ---
        if (!in_array('product_code_clean', $cols()))
            DB::statement("ALTER TABLE products ADD COLUMN product_code_clean VARCHAR(50) NULL AFTER product_code");
        if (!in_array('product_name_clean', $cols()))
            DB::statement("ALTER TABLE products ADD COLUMN product_name_clean VARCHAR(255) NULL AFTER product_name");
        if (!in_array('match_key', $cols()))
            DB::statement("ALTER TABLE products ADD COLUMN match_key VARCHAR(255) NULL AFTER product_name_clean");
        if (!in_array('sell_unit', $cols()))
            DB::statement("ALTER TABLE products ADD COLUMN sell_unit VARCHAR(50) NULL AFTER unit");
        if (!in_array('pcs_per_unit', $cols()))
            DB::statement("ALTER TABLE products ADD COLUMN pcs_per_unit INT UNSIGNED NULL AFTER sell_unit");
        if (!in_array('stock_in_sell_unit', $cols()))
            DB::statement("ALTER TABLE products ADD COLUMN stock_in_sell_unit INT NULL AFTER stock");

        // Unique key code + indexes
        try { DB::statement('ALTER TABLE products DROP KEY products_code_product_unique'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE products DROP KEY products_product_name_unique'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE products ADD UNIQUE KEY products_product_code_unique (product_code)'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE products ADD KEY products_product_name_index (product_name)'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE products ADD KEY products_match_key_index (match_key)'); } catch (\Throwable $e) {}

        // Backfill match_key dari product_name
        $rows = DB::table('products')->select('id', 'product_name')->get();
        foreach ($rows as $r) {
            DB::table('products')->where('id', $r->id)->update([
                'match_key' => \App\Models\Product::buildMatchKey($r->product_name),
            ]);
        }

        // Default stok konversi = stok (pcs_per_unit belum di-set)
        DB::statement('UPDATE products SET stock_in_sell_unit = stock WHERE stock_in_sell_unit IS NULL');

        // Gabung: hapus tabel curasi terpisah
        Schema::dropIfExists('product_clean');
        Schema::dropIfExists('products_clean');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_clean');
    }
};