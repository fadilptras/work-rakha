<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');           // contoh: 2026
            $table->unsignedTinyInteger('bulan_angka');       // 1-12 (biar mudah query/urut)
            $table->string('bulan', 20);                      // "Januari", "Februari", dst (biar cocok dgn kolom bulan di tabel sales)
            $table->string('ps')->nullable();                 // null = target keseluruhan tim, diisi = target khusus PS tsb
            $table->decimal('target_amount', 15, 2);
            $table->timestamps();

            $table->unique(['tahun', 'bulan_angka', 'ps']);   // 1 target per bulan per PS (atau per bulan utk tim jika ps null)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_targets');
    }
};
