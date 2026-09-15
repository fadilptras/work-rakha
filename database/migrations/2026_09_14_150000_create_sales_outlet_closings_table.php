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
        Schema::create('sales_outlet_closings', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->string('month');
            $table->string('ps')->nullable();
            $table->string('customer_name');
            $table->decimal('closing_rate', 15, 2)->nullable()->comment('Add. Closing (Rp) - dulu %');
            $table->integer('closing_count')->nullable();
            $table->timestamps();

            $table->unique(['year', 'month', 'ps', 'customer_name'], 'uniq_sales_outlet_closing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_outlet_closings');
    }
};
