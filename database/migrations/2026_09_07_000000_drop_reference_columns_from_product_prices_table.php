<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
            $table->dropColumn(['barang_id', 'product_code', 'unit', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->unique()->after('id');
            $table->string('product_code', 50)->nullable()->after('barang_id');
            $table->string('unit', 50)->nullable()->after('product_name');
            $table->boolean('is_active')->default(true)->after('unit_price');

            $table->foreign('barang_id')
                ->references('id')
                ->on('barangs')
                ->onDelete('cascade');
        });
    }
};