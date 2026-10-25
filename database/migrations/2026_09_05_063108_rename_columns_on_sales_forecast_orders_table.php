<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sales_forecast_orders ke Bahasa Inggris (English).
     * Idempoten: DB saat ini sudah konvergen (kolom English + tipe + unique benar).
     */
    public function up(): void
    {
        $this->dropIndexIfExists('sales_forecast_orders', 'forecast_order_unique');

        $pairs = [
            'tahun' => 'year',
            'bulan_acuan' => 'month',
            'nama_produk' => 'product_name',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('sales_forecast_orders', $old) && ! Schema::hasColumn('sales_forecast_orders', $new)) {
                Schema::table('sales_forecast_orders', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        if (Schema::hasColumn('sales_forecast_orders', 'year')
            && $this->columnType('sales_forecast_orders', 'year') !== 'smallint unsigned') {
            Schema::table('sales_forecast_orders', function (Blueprint $table) {
                $table->unsignedSmallInteger('year')->change();
            });
        }

        if (Schema::hasColumn('sales_forecast_orders', 'month')
            && $this->columnType('sales_forecast_orders', 'month') !== 'varchar(20)') {
            Schema::table('sales_forecast_orders', function (Blueprint $table) {
                $table->string('month', 20)->change();
            });
        }

        $this->addUniqueIfMissing('sales_forecast_orders', ['year', 'month', 'product_name'], 'forecast_order_unique');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('sales_forecast_orders', 'forecast_order_unique');

        $pairs = [
            'year' => 'tahun',
            'month' => 'bulan_acuan',
            'product_name' => 'nama_produk',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('sales_forecast_orders', $old) && ! Schema::hasColumn('sales_forecast_orders', $new)) {
                Schema::table('sales_forecast_orders', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        $this->addUniqueIfMissing('sales_forecast_orders', ['tahun', 'bulan_acuan', 'nama_produk'], 'forecast_order_unique');
    }

    protected function columnType(string $table, string $column): ?string
    {
        $row = DB::selectOne(
            "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        );

        return $row ? ((array) $row)['COLUMN_TYPE'] : null;
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
