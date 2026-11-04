<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Restrukturisasi tabel pengajuan_dana agar sesuai dengan spec terbaru.
     *
     * Perubahan:
     * - Drop kolom lama: atasan_id, direktur_id, atasan_approved_at, direktur_approved_at,
     *   finance_approved_at, status_hrd, catatan_hrd, status_direktur, catatan_direktur
     * - Perbaiki tipe kolom: lampiran jadi text, status enum lebih lengkap, status_atasan enum lebih lengkap
     * - Tambah kolom baru: finance_id (sesuai spec), finance_processed_at, nama_rek,
     *   approver_1_id, approver_1_status, approver_1_catatan, approver_1_approved_at,
     *   approver_2_id, approver_2_status, approver_2_catatan, approver_2_approved_at,
     *   payment_status (enum), catatan_finance
     */
    public function up(): void
    {
        // 1. Drop foreign keys lama — hanya bila masih ada (DB lama).
        foreach (['atasan_id', 'direktur_id', 'finance_id'] as $column) {
            if (! $this->foreignKeyExists('pengajuan_dana', $column)) {
                continue;
            }
            Schema::table('pengajuan_dana', function (Blueprint $table) use ($column) {
                $table->dropForeign('pengajuan_dana_'.$column.'_foreign');
            });
        }

        // 2. Drop kolom lama yang tidak ada di spec baru
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $columnsToRemove = [];

            if (Schema::hasColumn('pengajuan_dana', 'atasan_id')) $columnsToRemove[] = 'atasan_id';
            if (Schema::hasColumn('pengajuan_dana', 'direktur_id')) $columnsToRemove[] = 'direktur_id';
            if (Schema::hasColumn('pengajuan_dana', 'atasan_approved_at')) $columnsToRemove[] = 'atasan_approved_at';
            if (Schema::hasColumn('pengajuan_dana', 'direktur_approved_at')) $columnsToRemove[] = 'direktur_approved_at';
            if (Schema::hasColumn('pengajuan_dana', 'finance_approved_at')) $columnsToRemove[] = 'finance_approved_at';
            if (Schema::hasColumn('pengajuan_dana', 'finance_id')) $columnsToRemove[] = 'finance_id';
            if (Schema::hasColumn('pengajuan_dana', 'status_hrd')) $columnsToRemove[] = 'status_hrd';
            if (Schema::hasColumn('pengajuan_dana', 'catatan_hrd')) $columnsToRemove[] = 'catatan_hrd';
            if (Schema::hasColumn('pengajuan_dana', 'status_direktur')) $columnsToRemove[] = 'status_direktur';
            if (Schema::hasColumn('pengajuan_dana', 'catatan_direktur')) $columnsToRemove[] = 'catatan_direktur';
            if (Schema::hasColumn('pengajuan_dana', 'payment_status')) $columnsToRemove[] = 'payment_status';

            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });

        // 3. Enum & lampiran — DILEWATI (superseded): skema DB saat ini sudah lebih baru
        // (status memakai 'diproses' yang dipakai kode; status_atasan sudah tidak ada;
        // lampiran sudah TEXT). Menjalankan MODIFY lama akan merusak data.
        // \DB::statement("ALTER TABLE pengajuan_dana MODIFY COLUMN status ...");
        // \DB::statement("ALTER TABLE pengajuan_dana MODIFY COLUMN status_atasan ...");
        // \DB::statement("ALTER TABLE pengajuan_dana MODIFY COLUMN lampiran TEXT NULL");

        // 4. Kolom era approver_1_id/finance_id — DILEWATI (superseded): kode saat ini
        // memakai approver_dana_*_id. Menambah kolom lama hanya membuat kolom mati.
        // Lihat App\Models\PengajuanDana dan PengajuanDanaPolicy.

        // 5. FK constraints era lama — DILEWATI dengan alasan yang sama seperti langkah 4.
    }

    protected function foreignKeyExists(string $table, string $column): bool
    {
        return \DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
             AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1",
            [$table, $column]
        ) !== null;
    }

    /**
     * Reverse the migrations.
     * Catatan: rollback tidak bisa sepenuhnya mengembalikan data yang sudah di-drop.
     */
    public function down(): void
    {
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $table->dropForeign(['finance_id']);
            $table->dropForeign(['approver_1_id']);
            $table->dropForeign(['approver_2_id']);

            $table->dropColumn([
                'finance_id',
                'finance_processed_at',
                'nama_rek',
                'approver_1_id',
                'approver_1_status',
                'approver_1_catatan',
                'approver_1_approved_at',
                'approver_2_id',
                'approver_2_status',
                'approver_2_catatan',
                'approver_2_approved_at',
                'payment_status',
                'catatan_finance',
            ]);
        });

        // Kembalikan kolom lama
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $table->foreignId('atasan_id')->nullable()->constrained('users');
            $table->foreignId('direktur_id')->nullable()->constrained('users');
            $table->timestamp('atasan_approved_at')->nullable();
            $table->timestamp('direktur_approved_at')->nullable();
            $table->enum('status_hrd', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_hrd')->nullable();
            $table->enum('status_direktur', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_direktur')->nullable();
            $table->string('payment_status', 50)->nullable();
        });

        \DB::statement("ALTER TABLE pengajuan_dana MODIFY COLUMN status ENUM('diajukan','disetujui','ditolak') NOT NULL DEFAULT 'diajukan'");
        \DB::statement("ALTER TABLE pengajuan_dana MODIFY COLUMN status_atasan ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu'");
    }
};
