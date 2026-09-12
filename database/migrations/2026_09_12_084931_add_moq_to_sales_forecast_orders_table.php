<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom MOQ (Minimum Order Quantity) pada tabel sales_forecast_orders.
     */
    public function up(): void
    {
        Schema::table('sales_forecast_orders', function (Blueprint $table) {
            $table->decimal('moq', 12, 2)->default(1)->after('suggested_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_forecast_orders', function (Blueprint $table) {
            $table->dropColumn('moq');
        });
    }
};