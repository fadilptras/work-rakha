<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_incentive_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->string('type', 20); // 'bulan' atau 'triwulan'
            $table->decimal('min_achievement', 5, 2); // e.g. 95.00
            $table->decimal('incentive_value', 15, 2); // rate % untuk bulan, flat amount untuk triwulan
            $table->timestamps();

            $table->unique(['tahun', 'type', 'min_achievement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_incentive_settings');
    }
};
