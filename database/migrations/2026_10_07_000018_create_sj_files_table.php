<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Arsip berkas PDF surat jalan per dokumen ws_mutation.
     * Murni penyimpanan file — tanpa logika stok.
     */
    public function up(): void
    {
        if (Schema::hasTable('sj_files')) {
            echo "SKIP sj_files: tabel sudah ada\n";
            return;
        }

        Schema::create('sj_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ws_mutation_id')->constrained('ws_mutation')->cascadeOnDelete();
            $table->string('file_path', 255);
            $table->string('original_name', 255);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['ws_mutation_id']);
        });
        echo "OK sj_files: dibuat\n";
    }

    public function down(): void
    {
        Schema::dropIfExists('sj_files');
        echo "DROP tabel sj_files\n";
    }
};
