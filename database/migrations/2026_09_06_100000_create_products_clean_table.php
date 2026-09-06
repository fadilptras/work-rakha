<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products_clean', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_id')->unique();
            $table->string('source_raw_name', 255)->nullable();
            $table->string('clean_name', 255)->nullable();
            $table->string('clean_code', 50)->nullable();
            $table->string('source_unit', 50)->nullable();
            $table->string('sell_unit', 50)->nullable();
            $table->unsignedInteger('pcs_per_sell_unit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('barang_id')
                ->references('id')
                ->on('barangs')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_clean');
    }
};