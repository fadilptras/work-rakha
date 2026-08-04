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
            if (!Schema::hasColumn('pengajuan_barang', 'status_monitoring')) {
                $table->string('status_monitoring')->nullable()->after('status');
            }
            if (!Schema::hasColumn('pengajuan_barang', 'riwayat_monitoring')) {
                $table->json('riwayat_monitoring')->nullable()->after('status_monitoring');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_barang', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_barang', 'status_monitoring')) {
                $table->dropColumn('status_monitoring');
            }
            if (Schema::hasColumn('pengajuan_barang', 'riwayat_monitoring')) {
                $table->dropColumn('riwayat_monitoring');
            }
        });
    }
};
