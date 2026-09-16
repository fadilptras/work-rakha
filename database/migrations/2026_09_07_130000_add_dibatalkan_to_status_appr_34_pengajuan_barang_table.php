<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pengajuan_barang MODIFY status_appr_3 ENUM('menunggu', 'disetujui', 'ditolak', 'skipped', 'dibatalkan', 'selesai')");
        DB::statement("ALTER TABLE pengajuan_barang MODIFY status_appr_4 ENUM('menunggu', 'disetujui', 'ditolak', 'skipped', 'dibatalkan', 'selesai')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pengajuan_barang MODIFY status_appr_3 ENUM('menunggu', 'disetujui', 'ditolak', 'skipped')");
        DB::statement("ALTER TABLE pengajuan_barang MODIFY status_appr_4 ENUM('menunggu', 'disetujui', 'ditolak', 'skipped')");
    }
};