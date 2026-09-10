<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom sell_unit dan stock_in_sell_unit dari tabel products.
     * Kolom-kolom ini sudah tidak dipakai (QTY/Package & stock kini memakai
     * unit, pcs_per_unit, fill_unit, dan stock).
     */
    public function up(): void
    {
        $cols = array_column(DB::select('SHOW COLUMNS FROM products'), 'Field');

        if (in_array('sell_unit', $cols)) {
            DB::statement('ALTER TABLE products DROP COLUMN sell_unit');
        }

        if (in_array('stock_in_sell_unit', $cols)) {
            DB::statement('ALTER TABLE products DROP COLUMN stock_in_sell_unit');
        }
    }

    public function down(): void
    {
        // Tidak memulihkan data yang sudah dihapus (destruktif).
    }
};
