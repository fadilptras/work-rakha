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
            $table->string('lampiran_keluar')->nullable()->after('keterangan_keluar');
            $table->string('latitude_keluar')->nullable()->after('lampiran_keluar');
            $table->string('longitude_keluar')->nullable()->after('latitude_keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['lampiran_keluar', 'latitude_keluar', 'longitude_keluar']);
        });
    }
};