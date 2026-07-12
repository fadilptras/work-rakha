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
        // 1. Drop foreign keys dulu sebelum drop kolom
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $table->dropForeign(['atasan_id']);
            $table->dropForeign(['direktur_id']);

            // finance_id mungkin sudah ada — cek dan drop jika ada
            if (Schema::hasColumn('pengajuan_dana', 'finance_id')) {
                $table->dropForeign(['finance_id']);
            }
        });

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

        // 3. Perbaiki enum status dan status_atasan via raw SQL
        \DB::statement("ALTER TABLE pengajuan_dana MODIFY COLUMN status ENUM('diajukan','diproses_appr_2','disetujui','proses_pembayaran','selesai','ditolak','dibatalkan') NOT NULL DEFAULT 'diajukan'");
        \DB::statement("ALTER TABLE pengajuan_dana MODIFY COLUMN status_atasan ENUM('menunggu','disetujui','ditolak','skipped') NOT NULL DEFAULT 'menunggu'");

        // Ubah lampiran dari varchar menjadi text
        \DB::statement("ALTER TABLE pengajuan_dana MODIFY COLUMN lampiran TEXT NULL");

        // 4. Tambah kolom baru sesuai spec
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            // finance
            $table->unsignedBigInteger('finance_id')->nullable()->after('user_id');
            $table->timestamp('finance_processed_at')->nullable()->after('finance_id');

            // Nama pemilik rekening
            $table->string('nama_rek', 100)->nullable()->after('no_rekening');

            // Approver 1
            $table->unsignedBigInteger('approver_1_id')->nullable()->after('catatan_atasan');
            $table->enum('approver_1_status', ['menunggu', 'disetujui', 'ditolak', 'skipped'])->nullable()->after('approver_1_id');
            $table->text('approver_1_catatan')->nullable()->after('approver_1_status');
            $table->timestamp('approver_1_approved_at')->nullable()->after('approver_1_catatan');

            // Approver 2
            $table->unsignedBigInteger('approver_2_id')->nullable()->after('approver_1_approved_at');
            $table->enum('approver_2_status', ['menunggu', 'disetujui', 'ditolak', 'skipped'])->nullable()->after('approver_2_id');
            $table->text('approver_2_catatan')->nullable()->after('approver_2_status');
            $table->timestamp('approver_2_approved_at')->nullable()->after('approver_2_catatan');

            // Payment & finance note
            $table->enum('payment_status', ['menunggu', 'diproses', 'selesai', 'skipped'])->nullable()->after('approver_2_approved_at');
            $table->text('catatan_finance')->nullable()->after('payment_status');
        });

        // 5. Tambah FK constraints
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $table->foreign('finance_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_1_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_2_id')->references('id')->on('users')->nullOnDelete();
        });
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
