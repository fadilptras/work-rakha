<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ubah stock_log_details dari "1 baris per produk per log" menjadi
 * "1 baris per log" dengan JSON array `items`.
 *
 * Sebelumnya import 166 barang -> 166 baris detail; setelah ini cukup
 * 1 baris berisi array JSON [{product_id, old_stok, new_stok, old_stok_po, new_stok_po}, ...].
 */
return new class extends Migration
{
    public function up(): void
    {
        // Kumpulkan data lama lalu kelompokkan per stock_log_id (urutan id dipertahankan).
        $groups = DB::table('stock_log_details')
            ->orderBy('id')
            ->get()
            ->groupBy('stock_log_id')
            ->mapWithKeys(function ($rows, $logId) {
                $items = $rows->map(function ($r) {
                    return [
                        'product_id' => $r->product_id,
                        'old_stok' => (int) $r->old_stok,
                        'new_stok' => (int) $r->new_stok,
                        'old_stok_po' => (int) $r->old_stok_po,
                        'new_stok_po' => (int) $r->new_stok_po,
                    ];
                })->values()->toArray();

                return [$logId => $items];
            });

        // Rebuild tabel
        Schema::dropIfExists('stock_log_details');
        Schema::create('stock_log_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_log_id')->unique();
            $table->json('items')->nullable();
            $table->timestamps();

            $table->foreign('stock_log_id')->references('id')->on('stock_logs')->onDelete('cascade');
        });

        // Backfill hanya untuk log yang masih ada
        foreach ($groups as $logId => $items) {
            if (!DB::table('stock_logs')->where('id', $logId)->exists()) {
                continue;
            }

            DB::table('stock_log_details')->insert([
                'stock_log_id' => $logId,
                'items' => json_encode($items),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Kembalikan ke 1 baris per produk (expand JSON).
        $rows = DB::table('stock_log_details')->get();

        Schema::dropIfExists('stock_log_details');
        Schema::create('stock_log_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_log_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->integer('old_stok');
            $table->integer('new_stok');
            $table->integer('old_stok_po');
            $table->integer('new_stok_po');
            $table->timestamps();

            $table->foreign('stock_log_id')->references('id')->on('stock_logs')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        foreach ($rows as $row) {
            $items = is_string($row->items) ? json_decode($row->items, true) : [];

            foreach ((array) $items as $item) {
                DB::table('stock_log_details')->insert([
                    'stock_log_id' => $row->stock_log_id,
                    'product_id' => $item['product_id'] ?? null,
                    'old_stok' => $item['old_stok'] ?? 0,
                    'new_stok' => $item['new_stok'] ?? 0,
                    'old_stok_po' => $item['old_stok_po'] ?? 0,
                    'new_stok_po' => $item['new_stok_po'] ?? 0,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }
};