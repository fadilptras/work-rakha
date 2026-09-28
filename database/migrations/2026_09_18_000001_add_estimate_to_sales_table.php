<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penanda baris rencana closing / estimasi di tabel sales.
     * Baris is_estimate=1 ikut ke semua rekap (achievement, growth, export).
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('is_estimate')->default(false)->after('ps');
            $table->string('estimate_note', 255)->nullable()->after('is_estimate');
            $table->index('is_estimate', 'sales_is_estimate_index');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_is_estimate_index');
            $table->dropColumn(['is_estimate', 'estimate_note']);
        });
    }
};
