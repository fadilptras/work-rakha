<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Baris lama yang dibatalkan dengan penanda teks tetap dinormalkan.
        if (Schema::hasTable('stock_moves') && Schema::hasColumn('stock_moves', 'status')) {
            DB::table('stock_moves')
                ->where('reason', 'like', '%[dibatalkan]%')
                ->update(['status' => 'cancelled']);
        }

        // Dokumen tanpa baris aktif tidak lagi tampil di arsip.
        if (Schema::hasTable('ws_mutation') && Schema::hasTable('stock_moves')) {
            DB::table('ws_mutation')->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('stock_moves')
                    ->whereColumn('stock_moves.ref_id', 'ws_mutation.id')
                    ->where('stock_moves.ref_type', 'ws_mutation')
                    ->where('stock_moves.status', '!=', 'cancelled');
            })->update(['status' => 'void']);
        }

        if (Schema::hasTable('stock_moves')
            && Schema::hasColumn('stock_moves', 'ref_type')
            && Schema::hasColumn('stock_moves', 'ref_id')
            && ! Schema::hasIndex('stock_moves', 'stock_moves_ref_type_ref_id_index')) {
            Schema::table('stock_moves', function (Blueprint $table) {
                $table->index(['ref_type', 'ref_id'], 'stock_moves_ref_type_ref_id_index');
            });
        }
    }

    public function down(): void
    {
        // Backfill audit tidak dibalik.
    }
};
