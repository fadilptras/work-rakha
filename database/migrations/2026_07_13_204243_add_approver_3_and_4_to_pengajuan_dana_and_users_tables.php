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
            $table->unsignedBigInteger('approver_dana_3_id')->nullable()->after('approver_2_id');
            $table->unsignedBigInteger('approver_dana_4_id')->nullable()->after('approver_dana_3_id');
        });

        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $table->unsignedBigInteger('approver_3_id')->nullable()->after('approver_2_approved_at');
            $table->string('approver_3_status')->default('menunggu')->after('approver_3_id');
            $table->text('approver_3_catatan')->nullable()->after('approver_3_status');
            $table->timestamp('approver_3_approved_at')->nullable()->after('approver_3_catatan');

            $table->unsignedBigInteger('approver_4_id')->nullable()->after('approver_3_approved_at');
            $table->string('approver_4_status')->default('menunggu')->after('approver_4_id');
            $table->text('approver_4_catatan')->nullable()->after('approver_4_status');
            $table->timestamp('approver_4_approved_at')->nullable()->after('approver_4_catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $table->dropColumn([
                'approver_3_id', 'approver_3_status', 'approver_3_catatan', 'approver_3_approved_at',
                'approver_4_id', 'approver_4_status', 'approver_4_catatan', 'approver_4_approved_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['approver_dana_3_id', 'approver_dana_4_id']);
        });
    }
};
