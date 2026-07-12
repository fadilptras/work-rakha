<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Melengkapi tabel cutis sesuai spec:
     * - Tambah 'cuti bersama' ke jenis_cuti
     * - Tambah 'proses_finalisasi' ke status
     * - Tambah kolom approval 4 level (approver_cuti_1..4_id, status_approver_1..4, tanggal_approve, catatan_approver)
     * - Tambah catatan_approval dan total_hari
     */
    public function up(): void
    {
        // 1. Perbaiki enum jenis_cuti dan status via raw SQL
        \DB::statement("ALTER TABLE cutis MODIFY COLUMN jenis_cuti ENUM('tahunan','sakit','cuti bersama') NOT NULL");
        \DB::statement("ALTER TABLE cutis MODIFY COLUMN status ENUM('diajukan','disetujui','ditolak','dibatalkan','proses_finalisasi') NOT NULL DEFAULT 'diajukan'");

        // 2. Tambah kolom-kolom yang hilang
        Schema::table('cutis', function (Blueprint $table) {
            $table->text('catatan_approval')->nullable()->after('lampiran');

            // Approver 4 level
            $table->unsignedBigInteger('approver_cuti_1_id')->nullable()->after('approver_id');
            $table->unsignedBigInteger('approver_cuti_2_id')->nullable()->after('approver_cuti_1_id');
            $table->unsignedBigInteger('approver_cuti_3_id')->nullable()->after('approver_cuti_2_id');
            // approver_cuti_4_id tidak ada FK constraint di SQL asli
            $table->unsignedBigInteger('approver_cuti_4_id')->nullable()->after('approver_cuti_3_id');

            // Status approver
            $table->enum('status_approver_1', ['menunggu', 'disetujui', 'ditolak', 'skipped'])
                ->default('menunggu')->after('approver_cuti_4_id');
            $table->enum('status_approver_2', ['menunggu', 'disetujui', 'ditolak', 'skipped'])
                ->default('menunggu')->after('status_approver_1');
            $table->enum('status_approver_3', ['menunggu', 'disetujui', 'ditolak', 'skipped'])
                ->nullable()->default('menunggu')->after('status_approver_2');
            // status_approver_4 di SQL asli adalah varchar(50) bukan enum
            $table->string('status_approver_4', 50)->nullable()->default('skipped')->after('status_approver_3');

            // Tanggal approve
            $table->timestamp('tanggal_approve_1')->nullable()->after('status_approver_4');
            $table->timestamp('tanggal_approve_2')->nullable()->after('tanggal_approve_1');
            $table->dateTime('tanggal_approve_3')->nullable()->after('tanggal_approve_2');

            // Catatan approver
            $table->text('catatan_approver_1')->nullable()->after('tanggal_approve_3');
            $table->text('catatan_approver_2')->nullable()->after('catatan_approver_1');
            $table->text('catatan_approver_3')->nullable()->after('catatan_approver_2');
            $table->string('catatan_approver_4')->nullable()->after('catatan_approver_3');

            // Total hari
            $table->integer('total_hari')->nullable()->after('catatan_approver_4');
        });

        // 3. Tambah FK untuk approver_cuti_1..3 (approver_cuti_4 tidak ada FK di SQL asli)
        Schema::table('cutis', function (Blueprint $table) {
            $table->foreign('approver_cuti_1_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_cuti_2_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_cuti_3_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cutis', function (Blueprint $table) {
            $table->dropForeign(['approver_cuti_1_id']);
            $table->dropForeign(['approver_cuti_2_id']);
            $table->dropForeign(['approver_cuti_3_id']);

            $table->dropColumn([
                'catatan_approval',
                'approver_cuti_1_id',
                'approver_cuti_2_id',
                'approver_cuti_3_id',
                'approver_cuti_4_id',
                'status_approver_1',
                'status_approver_2',
                'status_approver_3',
                'status_approver_4',
                'tanggal_approve_1',
                'tanggal_approve_2',
                'tanggal_approve_3',
                'catatan_approver_1',
                'catatan_approver_2',
                'catatan_approver_3',
                'catatan_approver_4',
                'total_hari',
            ]);
        });

        \DB::statement("ALTER TABLE cutis MODIFY COLUMN jenis_cuti ENUM('tahunan','sakit') NOT NULL");
        \DB::statement("ALTER TABLE cutis MODIFY COLUMN status ENUM('diajukan','disetujui','ditolak','dibatalkan') NOT NULL DEFAULT 'diajukan'");
    }
};
