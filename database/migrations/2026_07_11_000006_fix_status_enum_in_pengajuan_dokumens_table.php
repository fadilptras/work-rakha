<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan nilai 'disetujui' ke enum status pengajuan_dokumens.
     */
    public function up(): void
    {
        \DB::statement("ALTER TABLE pengajuan_dokumens MODIFY COLUMN status ENUM('diajukan','diproses','disetujui','ditolak','selesai') NOT NULL DEFAULT 'diajukan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE pengajuan_dokumens MODIFY COLUMN status ENUM('diajukan','diproses','selesai','ditolak') NOT NULL DEFAULT 'diajukan'");
    }
};
