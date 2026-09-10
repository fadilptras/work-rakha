<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Soft delete untuk harga jual (tabel product_prices).
 *
 * is_deleted = 1 menandakan produk dihapus dari daftar pricing namun datanya
 * tetap tersimpan utuh untuk rekap. Tambah ulang dengan nama yang sama akan
 * menghidupkan kembali tombstone (restore).
 *
 * Catatan: skema dibuat manual via bootstrap script (bukan artisan migrate).
 * Migration ini sebagai sumber kebenaran skema.
 */
return new class extends Migration
{
    public function up(): void
    {
        $cols = array_column(DB::select('SHOW COLUMNS FROM product_prices'), 'Field');

        if (!in_array('is_deleted', $cols)) {
            DB::statement('ALTER TABLE product_prices ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER unit_price');
        }
    }

    public function down(): void
    {
        $cols = array_column(DB::select('SHOW COLUMNS FROM product_prices'), 'Field');

        if (in_array('is_deleted', $cols)) {
            DB::statement('ALTER TABLE product_prices DROP COLUMN is_deleted');
        }
    }
};