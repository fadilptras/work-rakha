<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * interactions.user_id dipakai di CrmController/AdminCrmController
     * (pencatat transaksi) tapi belum ada di migrasi awal, sehingga
     * mass-assignment terbuang / insert gagal di DB fresh.
     */
    public function up(): void
    {
        if (Schema::hasTable('interactions') && ! Schema::hasColumn('interactions', 'user_id')) {
            Schema::table('interactions', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')
                    ->constrained('users')->nullOnDelete();
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('interactions') && Schema::hasColumn('interactions', 'user_id')) {
            Schema::table('interactions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
