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
    Schema::table('absensi', function (Blueprint $table) {
        // Tambahkan kolom untuk jam keluar
        $table->time('jam_keluar')->nullable()->after('jam_masuk');
        
        // Tambahkan kolom untuk keterangan saat pulang
        $table->text('keterangan_keluar')->nullable()->after('lampiran');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            //
        });
    }
};
