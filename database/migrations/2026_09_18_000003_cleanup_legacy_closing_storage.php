<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bersih-bersih setelah pindah ke sales_closings:
     * - hapus baris estimasi di sales (sudah disalin ke sales_closings)
     * - lepas kolom is_estimate / estimate_note
     * - hapus tabel legacy sales_outlet_closings (backup: backup_sales_outlet_closings_20260918)
     *
     * JALANKAN SETELAH: 2026_09_18_000002_create_sales_closings_table
     */
    public function up(): void
    {
        if (Schema::hasTable('sales_outlet_closings')
            && DB::table('sales_outlet_closings')->count() > 0
            && (!Schema::hasTable('sales_closings') || DB::table('sales_closings')->count() === 0)) {
            throw new \RuntimeException('Batal: jalankan dulu 2026_09_18_000002_create_sales_closings_table sebelum cleanup.');
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'is_estimate')) {
            DB::table('sales')->where('is_estimate', true)->delete();
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex('sales_is_estimate_index');
                $table->dropColumn(['is_estimate', 'estimate_note']);
            });
        }

        Schema::dropIfExists('sales_outlet_closings');
    }

    public function down(): void
    {
        // Dibuat ulang kosong; restore data dari backup_sales_outlet_closings_20260918 bila perlu.
        Schema::create('sales_outlet_closings', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->string('month');
            $table->string('ps')->nullable();
            $table->string('customer_name');
            $table->decimal('closing_rate', 15, 2)->nullable();
            $table->integer('closing_count')->nullable();
            $table->timestamps();
            $table->unique(['year', 'month', 'ps', 'customer_name'], 'uniq_sales_outlet_closing');
        });
    }
};
