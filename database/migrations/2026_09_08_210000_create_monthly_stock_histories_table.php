<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel ringkasan bulanan daily_stock_histories:
 * Menyimpan min/avg/max stok per produk per bulan agar data lama bisa dipertahankan
 * tanpa menumpuk baris harian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_stock_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->integer('stok_min')->default(0);
            $table->integer('stok_max')->default(0);
            $table->decimal('stok_avg', 12, 2)->default(0);
            $table->integer('stok_po')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'year', 'month'], 'monthly_stock_histories_product_year_month_unique');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_stock_histories');
    }
};