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
        Schema::create('kpi_evaluation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_indicator_id')->constrained()->cascadeOnDelete();
            $table->string('achievement_value')->nullable()->comment('Can be a number, percentage, or text');
            $table->integer('result_index')->nullable()->comment('1 to 5 index, or 1 to 4 for behavior');
            $table->decimal('final_score', 8, 2)->nullable()->comment('calculated score: result_index * weight');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_evaluation_items');
    }
};
