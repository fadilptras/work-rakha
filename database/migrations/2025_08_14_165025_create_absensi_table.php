<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Terhubung ke tabel users
            $table->date('tanggal');
            $table->time('jam_masuk');
            $table->enum('status', ['hadir', 'sakit', 'izin', 'cuti']);
            $table->text('keterangan')->nullable();
            $table->string('lampiran')->nullable(); // Untuk menyimpan path file
            $table->timestamps();

            // Tambahkan unique constraint agar satu user hanya bisa absen sekali sehari
            $table->unique(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};