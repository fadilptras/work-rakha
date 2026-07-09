<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('approver_1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approver_2_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approver_1_id']);
            $table->dropForeign(['approver_2_id']);
            $table->dropColumn(['approver_1_id', 'approver_2_id']);
        });
    }
};