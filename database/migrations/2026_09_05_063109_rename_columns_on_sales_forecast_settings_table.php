<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sales_forecast_settings ke Bahasa Inggris (English).
     * Idempoten: rename/change hanya bila tipe/nama lama masih ada (data bulan pendek,
     * aman untuk varchar(20)).
     */
    public function up(): void
    {
        $pairs = [
            'tahun' => 'year',
            'bulan_acuan' => 'month',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('sales_forecast_settings', $old) && ! Schema::hasColumn('sales_forecast_settings', $new)) {
                Schema::table('sales_forecast_settings', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        if (Schema::hasColumn('sales_forecast_settings', 'year')
            && $this->columnType('sales_forecast_settings', 'year') !== 'smallint unsigned') {
            Schema::table('sales_forecast_settings', function (Blueprint $table) {
                $table->unsignedSmallInteger('year')->change();
            });
        }

        if (Schema::hasColumn('sales_forecast_settings', 'month')
            && $this->columnType('sales_forecast_settings', 'month') !== 'varchar(20)') {
            $max = DB::selectOne(
                "SELECT MAX(CHAR_LENGTH(`month`)) AS m FROM sales_forecast_settings"
            );
            if (! $max || ((array) $max)['m'] <= 20) {
                Schema::table('sales_forecast_settings', function (Blueprint $table) {
                    $table->string('month', 20)->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $pairs = [
            'year' => 'tahun',
            'month' => 'bulan_acuan',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('sales_forecast_settings', $old) && ! Schema::hasColumn('sales_forecast_settings', $new)) {
                Schema::table('sales_forecast_settings', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }
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
};
