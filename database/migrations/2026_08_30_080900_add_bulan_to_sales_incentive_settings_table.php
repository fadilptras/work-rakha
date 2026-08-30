<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_incentive_settings', function (Blueprint $table) {
            $table->string('bulan', 20)->after('tahun')->default('Januari');
            
            // Drop old unique key and add new one
            $table->dropUnique('sales_incentive_settings_tahun_type_min_achievement_unique');
            $table->unique(['tahun', 'bulan', 'type', 'min_achievement'], 'sales_incentive_settings_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sales_incentive_settings', function (Blueprint $table) {
            $table->dropUnique('sales_incentive_settings_unique');
            $table->dropColumn('bulan');
            $table->unique(['tahun', 'type', 'min_achievement']);
        });
    }
};
