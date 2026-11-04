<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_dana', 'tipe_expend')) {
                $table->string('tipe_expend', 20)->default('UMUM')->after('divisi')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_dana', 'tipe_expend')) {
                $table->dropIndex(['tipe_expend']);
                $table->dropColumn('tipe_expend');
            }
        });
    }
};
