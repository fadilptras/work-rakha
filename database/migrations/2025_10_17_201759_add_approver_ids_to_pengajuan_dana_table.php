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
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $table->foreignId('atasan_id')->nullable()->after('user_id')->constrained('users');
            $table->foreignId('direktur_id')->nullable()->after('atasan_id')->constrained('users');
            $table->foreignId('finance_id')->nullable()->after('direktur_id')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $table->dropForeign(['atasan_id']);
            $table->dropForeign(['direktur_id']);
            $table->dropForeign(['finance_id']);
            $table->dropColumn(['atasan_id', 'direktur_id', 'finance_id']);
        });
    }
};