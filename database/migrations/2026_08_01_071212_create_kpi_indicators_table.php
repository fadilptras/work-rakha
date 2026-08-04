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
        Schema::create('kpi_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('category')->comment('kinerja, perilaku, kehadiran');
            $table->string('name');
            $table->text('definition')->nullable();
            $table->string('target')->nullable();
            $table->decimal('weight_percentage', 5, 2)->default(0);
            $table->string('type')->default('marketing')->comment('marketing, etc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_indicators');
    }
};
