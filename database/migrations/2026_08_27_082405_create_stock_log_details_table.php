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
        Schema::create('stock_log_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_log_id');
            $table->unsignedBigInteger('barang_id');
            $table->integer('old_stok');
            $table->integer('new_stok');
            $table->integer('old_stok_po');
            $table->integer('new_stok_po');
            $table->timestamps();

            $table->foreign('stock_log_id')->references('id')->on('stock_logs')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('barangs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_log_details');
    }
};
