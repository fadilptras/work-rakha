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
            $table->index('tanggal');
            $table->index('status');
        });

        Schema::table('cutis', function (Blueprint $table) {
            $table->index('status');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });

        Schema::table('lemburs', function (Blueprint $table) {
            $table->index('tanggal');
        });

        Schema::table('interactions', function (Blueprint $table) {
            $table->index('tanggal_interaksi');
            $table->index('jenis_transaksi');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('divisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['status']);
        });

        Schema::table('cutis', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['tanggal_mulai', 'tanggal_selesai']);
        });

        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
        });

        Schema::table('interactions', function (Blueprint $table) {
            $table->dropIndex(['tanggal_interaksi']);
            $table->dropIndex(['jenis_transaksi']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['divisi']);
        });
    }
};
