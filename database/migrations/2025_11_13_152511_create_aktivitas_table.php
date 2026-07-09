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
        Schema::create('aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('keterangan');
            $table->string('lampiran'); // Ini akan menyimpan path ke foto, misal: 'public/aktivitas/foto.png'
            $table->string('latitude');
            $table->string('longitude');
            $table->timestamps(); // Ini akan membuat 'created_at' yang kita perlukan untuk rekap
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas');
    }
};