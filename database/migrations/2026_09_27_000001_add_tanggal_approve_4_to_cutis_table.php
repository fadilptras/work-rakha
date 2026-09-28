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
        Schema::table('cutis', function (Blueprint $table) {
            // Kolom ini ditulis oleh CutiController@updateStatus &
            // AdminCutiController@updateStatus saat approver tahap 4 bertindak
            // ("tanggal_approve_{$currentStage}"). Tanpa kolom ini approval
            // tahap 4 error SQL (unknown column).
            $table->timestamp('tanggal_approve_4')->nullable()->after('tanggal_approve_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cutis', function (Blueprint $table) {
            $table->dropColumn('tanggal_approve_4');
        });
    }
};
