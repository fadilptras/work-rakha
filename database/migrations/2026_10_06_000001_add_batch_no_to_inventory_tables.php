<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kartu Stock: nomor batch manual per penerimaan.
     * Idempoten: dicek hasColumn dulu agar aman di-jalankan ulang.
     */
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('product_batches', 'batch_no')) {
                $table->string('batch_no', 100)->nullable()->after('product_id');
            }
        });

        Schema::table('stock_moves', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_moves', 'batch_no')) {
                $table->string('batch_no', 100)->nullable()->after('product_id');
            }
        });

        // Backfill data lama agar tidak kosong: PB-{id}.
        if (Schema::hasColumn('product_batches', 'batch_no')) {
            DB::table('product_batches')->whereNull('batch_no')->orderBy('id')->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('product_batches')->where('id', $row->id)->update(['batch_no' => 'PB-' . $row->id]);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            if (Schema::hasColumn('product_batches', 'batch_no')) {
                $table->dropColumn('batch_no');
            }
        });

        Schema::table('stock_moves', function (Blueprint $table) {
            if (Schema::hasColumn('stock_moves', 'batch_no')) {
                $table->dropColumn('batch_no');
            }
        });
    }
};
