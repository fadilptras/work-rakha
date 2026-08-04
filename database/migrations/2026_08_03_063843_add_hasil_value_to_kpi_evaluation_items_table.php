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
        Schema::table('kpi_evaluation_items', function (Blueprint $table) {
            $table->string('hasil_value')->nullable()->after('achievement_value')->comment('Persentase hasil / perhitungan lainnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_evaluation_items', function (Blueprint $table) {
            $table->dropColumn('hasil_value');
        });
    }
};
