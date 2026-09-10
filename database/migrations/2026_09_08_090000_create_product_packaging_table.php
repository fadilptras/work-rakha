<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master satuan packaging (data-driven, satu sumber kebenaran).
 *
 * Kolom yang diisi user hanya `packaging` + `pack`. `type` dihitung otomatis:
 * pack kosong -> single, 1 item -> fixed, 2+ item -> flex.
 * Tabel ini TIDAK memakai soft delete (penghapusan permanen).
 *
 * Catatan: skema dibuat manual via bootstrap script (bukan artisan migrate).
 * Migration ini sebagai sumber kebenaran skema & seed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_packaging')) {
            DB::statement('
                CREATE TABLE product_packaging (
                    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    packaging  VARCHAR(100) NOT NULL,
                    type       ENUM("single","flex","fixed") NOT NULL DEFAULT "flex",
                    pack       JSON NOT NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    UNIQUE KEY product_packaging_packaging_unique (packaging)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        }

        $count = DB::table('product_packaging')->count();
        if ($count === 0) {
            $rows = [
                ['Pcs', '[]'],
                ['Roll', '[]'],
                ['Botol', '[]'],
                ['Pack', '["Pcs"]'],
                ['Box', '["Pcs","Pasang"]'],
                ['Polybag', '["Pcs"]'],
                ['Bag', '["Pcs"]'],
                ['Pouches', '["Pcs"]'],
                ['Karton', '["Pcs"]'],
            ];

            $now = now();
            foreach ($rows as [$packaging, $pack]) {
                DB::table('product_packaging')->insert([
                    'packaging' => $packaging,
                    'type' => \App\Models\ProductPackaging::typeFromPack(json_decode($pack, true)),
                    'pack' => $pack,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS product_packaging');
    }
};