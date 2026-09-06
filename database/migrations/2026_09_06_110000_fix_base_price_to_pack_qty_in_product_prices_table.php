<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Selaraskan HNA Price (base_price) agar selalu = HNA/Pcs (unit_price) x isi per kemasan.
     * Isi per kemasan dibaca dari kolom "sediaan" (mis. "Bag/ 100 Pcs" -> 100), 1 jika tanpa "/".
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            UPDATE product_prices pp
            JOIN barangs b ON b.id = pp.barang_id
            SET pp.base_price = ROUND(
                pp.unit_price * IF(
                    b.presentation LIKE '%/%',
                    CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(b.presentation, '/', -1), ' ', 2) AS UNSIGNED),
                    1
                ), 2
            )
        SQL);
    }

    public function down(): void
    {
        // Tidak ada rollback otomatis; data harga bersifat transaksional.
    }
};