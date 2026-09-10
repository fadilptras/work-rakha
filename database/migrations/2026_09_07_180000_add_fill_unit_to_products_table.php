<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cols = array_column(DB::select('SHOW COLUMNS FROM products'), 'Field');

        if (!in_array('fill_unit', $cols)) {
            DB::statement("ALTER TABLE products ADD COLUMN fill_unit VARCHAR(50) NULL DEFAULT 'Pcs' AFTER pcs_per_unit");
        }

        // Konversi stok pecahan (mis. 250 pcs / 100 = 2.50) harus presisi.
        $stockCol = DB::selectOne('SHOW COLUMNS FROM products WHERE Field = "stock_in_sell_unit"');
        if ($stockCol && stripos($stockCol->Type, 'decimal') === false) {
            DB::statement('ALTER TABLE products CHANGE COLUMN stock_in_sell_unit stock_in_sell_unit DECIMAL(12,2) NULL');
        }
    }

    public function down(): void
    {
        $cols = array_column(DB::select('SHOW COLUMNS FROM products'), 'Field');

        if (in_array('fill_unit', $cols)) {
            DB::statement('ALTER TABLE products DROP COLUMN fill_unit');
        }
    }
};