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
        Schema::table('cutis', function (Blueprint $table) {
            $table->index('tanggal_mulai');
            $table->index('tanggal_selesai');
            $table->index('status');
        });

        Schema::table('absensi', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cutis', function (Blueprint $table) {
            $table->dropIndex(['tanggal_mulai']);
            $table->dropIndex(['tanggal_selesai']);
            $table->dropIndex(['status']);
        });

        Schema::table('absensi', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['status']);
        });
    }
};
