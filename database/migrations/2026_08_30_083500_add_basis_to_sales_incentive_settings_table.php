<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_incentive_settings', function (Blueprint $table) {
            $table->string('basis', 20)->default('percentage')->after('type'); // 'percentage' atau 'nominal'
            
            // Drop old unique key and add new one
            $table->dropUnique('sales_incentive_settings_unique');
            $table->unique(['tahun', 'bulan', 'type', 'basis', 'min_achievement'], 'sales_incentive_settings_unique_new');
        });
    }

    public function down(): void
    {
        Schema::table('sales_incentive_settings', function (Blueprint $table) {
            $table->dropUnique('sales_incentive_settings_unique_new');
            $table->dropColumn('basis');
            $table->unique(['tahun', 'bulan', 'type', 'min_achievement'], 'sales_incentive_settings_unique');
        });
    }
};
