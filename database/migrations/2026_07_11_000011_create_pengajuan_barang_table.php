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
        Schema::create('pengajuan_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('judul_pengajuan');
            $table->string('divisi');
            $table->json('rincian_barang');
            $table->enum('status', ['diajukan', 'diproses', 'selesai', 'ditolak', 'dibatalkan', 'proses_finalisasi'])
                ->default('diajukan');

            // Approver 1
            $table->enum('status_appr_1', ['menunggu', 'disetujui', 'ditolak', 'skipped', 'dibatalkan'])
                ->default('menunggu');
            $table->foreignId('approver_barang_1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_approver_1')->nullable();
            $table->timestamp('tanggal_approved_1')->nullable();

            // Approver 2
            $table->enum('status_appr_2', ['menunggu', 'disetujui', 'ditolak', 'skipped', 'dibatalkan'])
                ->default('menunggu');
            $table->foreignId('approver_barang_2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_approver_2')->nullable();
            $table->timestamp('tanggal_approved_2')->nullable();

            // Approver 3
            $table->foreignId('approver_barang_3_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_appr_3', ['menunggu', 'disetujui', 'ditolak', 'skipped'])
                ->nullable()->default('menunggu');
            $table->text('catatan_approver_3')->nullable();
            $table->timestamp('tanggal_approved_3')->nullable();

            // Approver 4 — onDelete: set null, onUpdate: cascade
            $table->foreignId('approver_barang_4_id')->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->enum('status_appr_4', ['menunggu', 'disetujui', 'ditolak', 'skipped'])
                ->default('skipped');
            $table->text('catatan_approver_4')->nullable();
            $table->timestamp('tanggal_approved_4')->nullable();

            // Direktur & admin
            $table->enum('status_direktur', ['menunggu', 'disetujui', 'ditolak', 'skipped'])
                ->default('skipped');
            $table->text('catatan_direktur')->nullable();
            $table->text('catatan_admin')->nullable();

            // Lampiran (JSON array of file paths)
            $table->json('lampiran')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_barang');
    }
};
