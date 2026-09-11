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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Sales/PS pemilik data client
            $table->string('area', 100)->nullable();
            $table->string('ps', 100)->nullable();
            // Contact person
            $table->string('client_name');
            $table->string('email')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->text('contact_address')->nullable();
            $table->date('contact_birth_date')->nullable();
            $table->string('contact_position', 100)->nullable();
            $table->string('contact_hobby', 255)->nullable();
            // Pharmacist / commission
            $table->string('pharmacist_name', 255)->nullable();
            $table->string('pharmacist_license_no', 255)->nullable();
            $table->string('pharmacist_phone', 50)->nullable();
            $table->decimal('commission_rate', 8, 2)->nullable()->default(0.00);
            // Company
            $table->string('customer_name');
            $table->string('sales_customer_name', 255)->nullable();
            $table->date('company_founded_date')->nullable();
            $table->text('company_address')->nullable();
            // Bank
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_name', 100)->nullable();
            $table->decimal('opening_balance', 15, 2)->nullable()->default(0.00);
            // Soft-delete flag (pola SoftDeletesFlag seperti products)
            $table->boolean('is_deleted')->default(false);
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
