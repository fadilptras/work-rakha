<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prices = DB::table('barangs')
            ->where('base_price', '>', 0)
            ->orWhere('unit_price', '>', 0)
            ->get(['id', 'base_price', 'unit_price'])
            ->map(function ($row) {
                return [
                    'barang_id' => $row->id,
                    'base_price' => $row->base_price,
                    'unit_price' => $row->unit_price,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->all();

        if (!empty($prices)) {
            DB::table('product_prices')->insert($prices);
        }

        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn(['base_price', 'unit_price']);
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->decimal('base_price', 15, 2)->default(0)->after('presentation');
            $table->decimal('unit_price', 15, 2)->default(0)->after('base_price');
        });

        DB::table('product_prices')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('barangs')
                        ->where('id', $row->barang_id)
                        ->update([
                            'base_price' => $row->base_price,
                            'unit_price' => $row->unit_price,
                        ]);
                }
            });
    }
};