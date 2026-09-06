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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'kpi_evaluator_id')) {
                $table->unsignedBigInteger('kpi_evaluator_id')->nullable()->after('approver_dana_4_id');
            }
            if (!Schema::hasColumn('users', 'kpi_approver_1_id')) {
                $table->unsignedBigInteger('kpi_approver_1_id')->nullable()->after('kpi_evaluator_id');
            }
            if (!Schema::hasColumn('users', 'kpi_approver_2_id')) {
                $table->unsignedBigInteger('kpi_approver_2_id')->nullable()->after('kpi_approver_1_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('kpi_evaluator_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('kpi_approver_1_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('kpi_approver_2_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kpi_evaluator_id']);
            $table->dropForeign(['kpi_approver_1_id']);
            $table->dropForeign(['kpi_approver_2_id']);
            $table->dropColumn(['kpi_evaluator_id', 'kpi_approver_1_id', 'kpi_approver_2_id']);
        });
    }
};
