<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan nilai 'tidak hadir' ke enum status absensi.
     */
    public function up(): void
    {
        // Menggunakan raw SQL karena ->change() pada enum memerlukan doctrine/dbal
        // dan tidak selalu konsisten di semua versi Laravel/MySQL
        \DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir','sakit','izin','cuti','tidak hadir') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE absensi MODIFY COLUMN status ENUM('hadir','sakit','izin','cuti') NOT NULL");
    }
};
