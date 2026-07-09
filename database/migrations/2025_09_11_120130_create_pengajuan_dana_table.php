<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_dana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('judul_pengajuan');
            $table->string('divisi');
            $table->string('nama_bank');
            $table->string('no_rekening');
            $table->decimal('total_dana', 15, 2);
            $table->string('lampiran')->nullable();
            $table->json('rincian_dana');
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak'])->default('diajukan');
            $table->enum('status_atasan', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_atasan')->nullable();
            $table->enum('status_hrd', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_hrd')->nullable();
            $table->enum('status_direktur', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_direktur')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_dana');
    }
};