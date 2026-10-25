<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Idempoten: DB saat ini sudah dalam keadaan konvergen (kolom English year/month,
     * unique sales_incentive_settings_unique_new). Operasi era Indonesia hanya
     * dijalankan bila kolom lama masih ada.
     */
    public function up(): void
    {
        $hasTahun = Schema::hasColumn('sales_incentive_settings', 'tahun');
        $hasBulan = Schema::hasColumn('sales_incentive_settings', 'bulan');

        if (! $hasBulan && $hasTahun) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->string('bulan', 20)->after('tahun')->default('Januari');
            });
            $hasBulan = true;
        }

        // Drop old unique key and add new one — hanya bila relevan.
        if ($this->indexExists('sales_incentive_settings', 'sales_incentive_settings_tahun_type_min_achievement_unique')) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->dropUnique('sales_incentive_settings_tahun_type_min_achievement_unique');
            });
        }

        if ($hasTahun && $hasBulan && ! $this->indexExists('sales_incentive_settings', 'sales_incentive_settings_unique')) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->unique(['tahun', 'bulan', 'type', 'min_achievement'], 'sales_incentive_settings_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('sales_incentive_settings', 'sales_incentive_settings_unique')) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->dropUnique('sales_incentive_settings_unique');
            });
        }
        if (Schema::hasColumn('sales_incentive_settings', 'bulan')) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->dropColumn('bulan');
            });
        }
        if (Schema::hasColumn('sales_incentive_settings', 'tahun')
            && ! $this->indexExists('sales_incentive_settings', 'sales_incentive_settings_tahun_type_min_achievement_unique')) {
            Schema::table('sales_incentive_settings', function (Blueprint $table) {
                $table->unique(['tahun', 'type', 'min_achievement']);
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
