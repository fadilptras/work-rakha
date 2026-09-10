<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Soft delete hanya untuk produk (tabel products) — master packaging tidak
 * memakai soft delete.
 *
 * Alur:
 *  - Hapus barang (admin.barangs.destroy) -> produk terkait softDelete()
 *    (is_deleted = 1), barangs di-hard-delete.
 *  - Halaman Aturan Produk (admin.products) menampilkan hanya Product::active().
 *  - Tambah/ubah barang dengan kode yang cocok dengan tombstone -> products
 *    di-restore (is_deleted = 0) + reset kolom kurasi.
 *
 * Catatan: skema dibuat manual via bootstrap script (bukan artisan migrate).
 * Migration ini sebagai sumber kebenaran skema.
 */
return new class extends Migration
{
    public function up(): void
    {
        $cols = array_column(DB::select('SHOW COLUMNS FROM products'), 'Field');

        if (!in_array('is_deleted', $cols)) {
            DB::statement('ALTER TABLE products ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER stock_po');
        }
    }

    public function down(): void
    {
        $cols = array_column(DB::select('SHOW COLUMNS FROM products'), 'Field');

        if (in_array('is_deleted', $cols)) {
            DB::statement('ALTER TABLE products DROP COLUMN is_deleted');
        }
    }
};