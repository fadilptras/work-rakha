<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('barangs', 'base_price')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->decimal('base_price', 15, 2)->default(0);
            });
        }
        if (!Schema::hasColumn('barangs', 'unit_price')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->decimal('unit_price', 15, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('barangs', 'base_price')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->dropColumn('base_price');
            });
        }
        if (Schema::hasColumn('barangs', 'unit_price')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->dropColumn('unit_price');
            });
        }
    }
};