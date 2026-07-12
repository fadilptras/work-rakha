<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Memperbaiki tabel agendas agar sesuai spec:
     * - user_id: nullable + onDelete set null (bukan cascade)
     * - end_time: nullable
     */
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            // Drop FK constraint lama dulu
            $table->dropForeign(['user_id']);
        });

        // Ubah user_id menjadi nullable dan end_time nullable via raw SQL
        \DB::statement('ALTER TABLE agendas MODIFY COLUMN user_id BIGINT UNSIGNED NULL');
        \DB::statement('ALTER TABLE agendas MODIFY COLUMN end_time DATETIME NULL');

        Schema::table('agendas', function (Blueprint $table) {
            // Tambah FK baru dengan nullOnDelete
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        \DB::statement('ALTER TABLE agendas MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL');
        \DB::statement('ALTER TABLE agendas MODIFY COLUMN end_time DATETIME NOT NULL');

        Schema::table('agendas', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
