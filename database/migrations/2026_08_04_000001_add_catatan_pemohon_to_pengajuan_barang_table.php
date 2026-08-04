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
            if (!Schema::hasColumn('pengajuan_barang', 'catatan_pemohon')) {
                $table->text('catatan_pemohon')->nullable()->after('divisi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_barang', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_barang', 'catatan_pemohon')) {
                $table->dropColumn('catatan_pemohon');
            }
        });
    }
};
