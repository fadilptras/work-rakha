<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Alur status cuti ikut pola pengajuan dana/barang:
     * persetujuan parsial = 'disetujui', final (approver terakhir) = 'selesai'.
     * Data lama dimigrasi: 'proses_finalisasi' -> 'disetujui',
     * 'disetujui' (dulu final) -> 'selesai'.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE cutis MODIFY COLUMN status ENUM('diajukan','disetujui','ditolak','dibatalkan','proses_finalisasi','selesai') NOT NULL DEFAULT 'diajukan'");
        // Urutan penting: final lama dulu, lalu parsial (himpunan disjoint).
        DB::table('cutis')->where('status', 'disetujui')->update(['status' => 'selesai']);
        DB::table('cutis')->where('status', 'proses_finalisasi')->update(['status' => 'disetujui']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Urutan penting: kebalikan up (himpunan disjoint).
        DB::table('cutis')->where('status', 'disetujui')->update(['status' => 'proses_finalisasi']);
        DB::table('cutis')->where('status', 'selesai')->update(['status' => 'disetujui']);
        DB::statement("ALTER TABLE cutis MODIFY COLUMN status ENUM('diajukan','disetujui','ditolak','dibatalkan','proses_finalisasi') NOT NULL DEFAULT 'diajukan'");
    }
};
