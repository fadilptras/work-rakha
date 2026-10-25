<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Idempoten: DB saat ini sudah konvergen (basis ada, unique _new ada).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('sales_incentive_settings', 'basis')
            && Schema::hasColumn('sales_incentive_settings', 'type')) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->string('basis', 20)->default('percentage')->after('type'); // 'percentage' atau 'nominal'
            });
        }

        // Drop old unique key and add new one — hanya bila kolom era Indonesia masih ada.
        $legacy = Schema::hasColumn('sales_incentive_settings', 'tahun')
            && Schema::hasColumn('sales_incentive_settings', 'bulan');

        if ($this->indexExists('sales_incentive_settings', 'sales_incentive_settings_unique')) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->dropUnique('sales_incentive_settings_unique');
            });
        }

        if ($legacy && ! $this->indexExists('sales_incentive_settings', 'sales_incentive_settings_unique_new')) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->unique(['tahun', 'bulan', 'type', 'basis', 'min_achievement'], 'sales_incentive_settings_unique_new');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('sales_incentive_settings', 'sales_incentive_settings_unique_new')) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->dropUnique('sales_incentive_settings_unique_new');
            });
        }
        if (Schema::hasColumn('sales_incentive_settings', 'basis')) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->dropColumn('basis');
            });
        }
    }

    protected function indexExists(string $table, string $index): bool
    {
        return DB::selectOne(
            "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1",
            [$table, $index]
        ) !== null;
    }
};
