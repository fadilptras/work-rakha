<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan self-referencing foreign keys ke tabel users (atasan, approver cuti & barang).
     * Dilakukan terpisah agar tabel users sudah terbentuk dulu sebelum FK ditambahkan.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kolom atasan (self-reference)
            $table->unsignedBigInteger('atasan_id')->nullable()->after('is_kepala_divisi');

            // Approver cuti (4 level)
            $table->unsignedBigInteger('approver_cuti_1_id')->nullable();
            $table->unsignedBigInteger('approver_cuti_2_id')->nullable();
            $table->unsignedBigInteger('approver_cuti_3_id')->nullable();
            $table->unsignedBigInteger('approver_cuti_4_id')->nullable();

            // Approver barang (4 level)
            $table->unsignedBigInteger('approver_barang_1_id')->nullable();
            $table->unsignedBigInteger('approver_barang_2_id')->nullable();
            $table->unsignedBigInteger('approver_barang_3_id')->nullable();
            $table->unsignedBigInteger('approver_barang_4_id')->nullable();
        });

        // Tambahkan FK constraints setelah kolom dibuat
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('atasan_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_cuti_1_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_cuti_2_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_cuti_3_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_cuti_4_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_barang_1_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_barang_2_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_barang_3_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_barang_4_id')
                ->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['atasan_id']);
            $table->dropForeign(['approver_cuti_1_id']);
            $table->dropForeign(['approver_cuti_2_id']);
            $table->dropForeign(['approver_cuti_3_id']);
            $table->dropForeign(['approver_cuti_4_id']);
            $table->dropForeign(['approver_barang_1_id']);
            $table->dropForeign(['approver_barang_2_id']);
            $table->dropForeign(['approver_barang_3_id']);
            $table->dropForeign(['approver_barang_4_id']);

            $table->dropColumn([
                'atasan_id',
                'approver_cuti_1_id',
                'approver_cuti_2_id',
                'approver_cuti_3_id',
                'approver_cuti_4_id',
                'approver_barang_1_id',
                'approver_barang_2_id',
                'approver_barang_3_id',
                'approver_barang_4_id',
            ]);
        });
    }
};
