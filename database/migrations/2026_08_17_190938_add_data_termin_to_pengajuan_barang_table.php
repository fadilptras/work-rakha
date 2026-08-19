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
        Schema::table('pengajuan_barang', function (Blueprint $table) {
            $table->json('data_termin')->nullable()->after('riwayat_monitoring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_barang', function (Blueprint $table) {
            $table->dropColumn('data_termin');
        });
    }
};
