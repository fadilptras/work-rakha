<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kpi_evaluation_items', function (Blueprint $table) {
            $table->string('target_value')->nullable()->after('kpi_indicator_id')->comment('Target spesifik untuk user ini');
        });

        // Ubah enum menjadi string
        Schema::table('kpi_evaluations', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_evaluation_items', function (Blueprint $table) {
            $table->dropColumn('target_value');
        });
        
        // Cannot cleanly rollback string to enum without potential data loss, but typically:
        // Schema::table('kpi_evaluations', function (Blueprint $table) {
        //     $table->enum('status', ['draft', 'submitted', 'approved', 'acknowledged'])->default('draft')->change();
        // });
    }
};
