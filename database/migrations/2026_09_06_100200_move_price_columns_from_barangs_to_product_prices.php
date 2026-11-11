<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Memindahkan kolom harga barangs -> product_prices.
     * Idempoten & disesuaikan skema akhir: product_prices berkunci product_name
     * (tanpa barang_id). Harga barangs yang belum ada di katalog disalin berdasarkan
     * product_name tanpa menimpa harga katalog yang sudah ada, lalu kolom harga
     * barangs di-drop bila masih ada dan tidak dipakai kode mana pun.
     */
    public function up(): void
    {
        if (Schema::hasColumn('barangs', 'base_price') || Schema::hasColumn('barangs', 'unit_price')) {
            $rows = DB::table('barangs')
                ->when(Schema::hasColumn('barangs', 'base_price'), fn ($q) => $q->where('base_price', '>', 0))
                ->when(
                    Schema::hasColumn('barangs', 'unit_price') && ! Schema::hasColumn('barangs', 'base_price'),
                    fn ($q) => $q->where('unit_price', '>', 0)
                )
                ->get(['product_name', 'presentation', 'base_price', 'unit_price']);

            foreach ($rows as $row) {
                $row = (array) $row;
                if (empty($row['product_name'])) {
                    continue;
                }
                $exists = DB::table('product_prices')->where('product_name', $row['product_name'])->exists();
                if ($exists) {
                    continue;
                }
                DB::table('product_prices')->insert([
                    'product_name' => $row['product_name'],
                    'presentation' => $row['presentation'] ?? null,
                    'base_price' => $row['base_price'] ?? 0,
                    'unit_price' => $row['unit_price'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $drop = array_values(array_filter(
            ['base_price', 'unit_price'],
            fn ($c) => Schema::hasColumn('barangs', $c)
        ));
        if (! empty($drop)) {
            Schema::table('barangs', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('barangs', 'base_price')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->decimal('base_price', 15, 2)->default(0)->after('presentation');
            });
        }
        if (! Schema::hasColumn('barangs', 'unit_price')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->decimal('unit_price', 15, 2)->default(0)->after('base_price');
            });
        }
    }
};
