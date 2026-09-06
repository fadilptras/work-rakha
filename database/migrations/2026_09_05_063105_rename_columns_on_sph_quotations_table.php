<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sph_quotations ke Bahasa Inggris (English).
     * Plus backfill key JSON pada kolom items.
     */
    public function up(): void
    {
        Schema::table('sph_quotations', function (Blueprint $table) {
            $table->dropForeign(['created_by']);

            $table->renameColumn('sph_date', 'date');
            $table->renameColumn('ps_name', 'ps');
            $table->renameColumn('ppn_percent', 'vat_percent');
            $table->renameColumn('created_by', 'user_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Backfill: ubah key JSON items (code/name/category -> product_code/product_name/presentation)
        $rows = DB::table('sph_quotations')->select('id', 'items')->get();

        foreach ($rows as $row) {
            $decoded = json_decode($row->items, true);
            if (!is_array($decoded)) {
                continue;
            }

            $mapped = array_map(function ($item) {
                return [
                    'product_code' => $item['code'] ?? null,
                    'product_name' => $item['name'] ?? null,
                    'presentation' => $item['category'] ?? null,
                    'unit_price'   => $item['unit_price'] ?? 0,
                    'discount'     => $item['discount'] ?? 0,
                    'qty'          => $item['qty'] ?? 0,
                ];
            }, $decoded);

            DB::table('sph_quotations')->where('id', $row->id)->update([
                'items' => json_encode($mapped),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sph_quotations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Restore JSON items keys kembali.
        $rows = DB::table('sph_quotations')->select('id', 'items')->get();

        foreach ($rows as $row) {
            $decoded = json_decode($row->items, true);
            if (!is_array($decoded)) {
                continue;
            }

            $restored = array_map(function ($item) {
                return [
                    'code'       => $item['product_code'] ?? null,
                    'name'       => $item['product_name'] ?? null,
                    'category'   => $item['presentation'] ?? null,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount'   => $item['discount'] ?? 0,
                    'qty'        => $item['qty'] ?? 0,
                ];
            }, $decoded);

            DB::table('sph_quotations')->where('id', $row->id)->update([
                'items' => json_encode($restored),
            ]);
        }

        Schema::table('sph_quotations', function (Blueprint $table) {
            $table->renameColumn('date', 'sph_date');
            $table->renameColumn('ps', 'ps_name');
            $table->renameColumn('vat_percent', 'ppn_percent');
            $table->renameColumn('user_id', 'created_by');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};