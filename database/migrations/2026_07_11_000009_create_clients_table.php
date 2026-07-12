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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Sales/PIC pemilik data client
            $table->string('area', 100)->nullable();
            $table->string('pic', 100)->nullable();
            $table->string('nama_user');
            $table->string('nama_perusahaan');
            $table->date('tanggal_berdiri')->nullable();
            $table->string('email')->nullable();
            $table->string('no_telpon', 50)->nullable();
            $table->text('alamat_user')->nullable();
            $table->decimal('saldo_awal', 15, 2)->nullable()->default(0.00);
            $table->string('bank', 50)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('nama_di_rekening', 70)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat_perusahaan')->nullable();
            $table->string('jabatan', 70)->nullable();
            $table->text('hobby_client')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
