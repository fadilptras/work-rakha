<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sales_targets ke Bahasa Inggris (English).
     * Idempoten: operasi hanya bila kondisi lama masih ada.
     */
    public function up(): void
    {
        $this->dropIndexIfExists('sales_targets', 'sales_targets_tahun_bulan_angka_ps_unique');

        $pairs = [
            'tahun' => 'year',
            'bulan_angka' => 'month_number',
            'bulan' => 'month',
            'sales_last_year_amount' => 'last_year_amount',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('sales_targets', $old) && ! Schema::hasColumn('sales_targets', $new)) {
                Schema::table('sales_targets', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        $this->addUniqueIfMissing('sales_targets', ['year', 'month_number', 'ps'], 'sales_targets_year_month_number_ps_unique');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('sales_targets', 'sales_targets_year_month_number_ps_unique');

        $pairs = [
            'year' => 'tahun',
            'month_number' => 'bulan_angka',
            'month' => 'bulan',
            'last_year_amount' => 'sales_last_year_amount',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('sales_targets', $old) && ! Schema::hasColumn('sales_targets', $new)) {
                Schema::table('sales_targets', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        $this->addUniqueIfMissing('sales_targets', ['tahun', 'bulan_angka', 'ps'], 'sales_targets_tahun_bulan_angka_ps_unique');
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
