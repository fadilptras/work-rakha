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
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom setelah kolom 'password'
            $table->string('profile_picture')->nullable()->after('password'); // Path/URL gambar, boleh kosong
            $table->string('jabatan')->nullable()->after('profile_picture'); // Jabatan user, boleh kosong
            $table->date('tanggal_bergabung')->nullable()->after('jabatan'); // Tanggal bergabung, boleh kosong
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_picture', 'jabatan', 'tanggal_bergabung']);
        });
    }
};