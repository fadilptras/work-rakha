<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sales_incentive_settings ke Bahasa Inggris (English).
     * Idempoten: DB saat ini memakai unique _new di kolom English — konvergen ke
     * nama unique rantai-final (sales_incentive_settings_unique).
     */
    public function up(): void
    {
        $this->dropIndexIfExists('sales_incentive_settings', 'sales_incentive_settings_unique_new');

        $pairs = [
            'tahun' => 'year',
            'bulan' => 'month',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('sales_incentive_settings', $old) && ! Schema::hasColumn('sales_incentive_settings', $new)) {
                Schema::table('sales_incentive_settings', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        $this->addUniqueIfMissing(
            'sales_incentive_settings',
            ['year', 'month', 'type', 'basis', 'min_achievement'],
            'sales_incentive_settings_unique'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('sales_incentive_settings', 'sales_incentive_settings_unique');

        $pairs = [
            'year' => 'tahun',
            'month' => 'bulan',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('sales_incentive_settings', $old) && ! Schema::hasColumn('sales_incentive_settings', $new)) {
                Schema::table('sales_incentive_settings', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        $this->addUniqueIfMissing(
            'sales_incentive_settings',
            ['tahun', 'bulan', 'type', 'basis', 'min_achievement'],
            'sales_incentive_settings_unique_new'
        );
    }

    protected function indexExists(string $table, string $index): bool
    {
        return DB::selectOne(
            "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1",
            [$table, $index]
        ) !== null;
    }

    protected function dropIndexIfExists(string $table, string $index): void
    {
        if (! $this->indexExists($table, $index)) {
            return;
        }
        Schema::table($table, function (Blueprint $table) use ($index) {
            $table->dropUnique($index);
        });
    }

    protected function addUniqueIfMissing(string $table, array $columns, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }
        Schema::table($table, function (Blueprint $table) use ($columns, $index) {
            $table->unique($columns, $index);
        });
    }
};
