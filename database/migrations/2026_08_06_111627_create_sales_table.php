<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('sales', function (Blueprint $table) {
        $table->id();
        $table->date('tanggal')->nullable();
        $table->string('nama_customer')->nullable();
        $table->string('nama_produk')->nullable();
        $table->integer('qty')->nullable();
        $table->string('satuan')->nullable();
        $table->decimal('hna', 15, 2)->nullable();
        $table->decimal('diskon', 15, 2)->nullable();
        $table->decimal('harga_nett', 15, 2)->nullable();
        $table->string('bulan')->nullable();
        $table->string('ps')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
