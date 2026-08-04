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
        Schema::create('kpi_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('period')->comment('e.g., Q1 2026, or July 2026');
            $table->date('evaluation_date');
            $table->text('evaluation_notes')->nullable();
            $table->text('action_plan')->nullable();
            $table->decimal('total_score', 8, 2)->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'acknowledged'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_evaluations');
    }
};
