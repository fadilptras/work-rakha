<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('absensi', function (Blueprint $table) {
            // Taruh setelah tanggal biar rapi, default null karena saat masuk belum ada tanggal keluar
            $table->date('tanggal_keluar')->nullable()->after('tanggal'); 
        });
    }
    
    public function down()
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn('tanggal_keluar');
        });
    }
};
