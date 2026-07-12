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
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('nama_produk');
            $table->string('lokasi')->nullable();
            $table->string('peserta')->nullable();
            $table->enum('jenis_transaksi', ['IN', 'OUT', 'ENTERTAIN']);
            $table->decimal('nilai_kontribusi', 15, 2)->default(0.00);
            $table->date('tanggal_interaksi');
            $table->text('catatan')->nullable();
            $table->integer('nilai_sales')->nullable();
            $table->double('komisi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
