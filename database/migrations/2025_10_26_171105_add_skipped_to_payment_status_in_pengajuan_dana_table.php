<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            // Kolom payment_status belum ada sebelumnya, tambahkan sebagai kolom baru.
            // (Migrasi ini sebelumnya mencoba ->change() pada kolom yang belum dibuat.)
            if (!Schema::hasColumn('pengajuan_dana', 'payment_status')) {
                $table->string('payment_status', 50)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_dana', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
