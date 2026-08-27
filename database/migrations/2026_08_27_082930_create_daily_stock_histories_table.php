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
        Schema::create('daily_stock_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_id');
            $table->date('tanggal');
            $table->integer('stok')->default(0);
            $table->integer('stok_po')->default(0);
            $table->timestamps();

            $table->foreign('barang_id')->references('id')->on('barangs')->onDelete('cascade');
            $table->unique(['barang_id', 'tanggal']); // Make sure there is only one entry per product per day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_stock_histories');
    }
};
