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
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom setelah approver_2_id (atau sesuaikan posisinya)
            $table->foreignId('manager_keuangan_id')->nullable()->after('approver_2_id')
                  ->constrained('users') // Foreign key ke tabel users
                  ->onDelete('set null'); // Jika user finance dihapus, set null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['manager_keuangan_id']);
            // Hapus kolomnya
            $table->dropColumn('manager_keuangan_id');
        });
    }
};