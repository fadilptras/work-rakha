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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('pic_1')->nullable();
            $table->string('pic_2')->nullable();
            $table->string('kontak_pic1')->nullable();
            $table->string('kontak_pic2')->nullable();
            $table->text('alamat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['pic_1', 'pic_2', 'kontak_pic1', 'kontak_pic2', 'alamat']);
        });
    }
};
