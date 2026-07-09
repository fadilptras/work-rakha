<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // UBAH DARI 'pengajuan_danas' MENJADI 'pengajuan_dana'
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $table->timestamp('direktur_approved_at')->nullable()->after('direktur_id');
            $table->timestamp('atasan_approved_at')->nullable()->after('atasan_id');
            $table->timestamp('finance_approved_at')->nullable()->after('finance_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // UBAH JUGA DI SINI
        Schema::table('pengajuan_dana', function (Blueprint $table) {
            $table->dropColumn(['atasan_approved_at', 'direktur_approved_at', 'finance_approved_at']);
        });
    }
};