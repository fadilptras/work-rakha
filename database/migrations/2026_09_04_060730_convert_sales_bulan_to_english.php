<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konversi nama bulan (kolom yang menyimpan nama bulan) dari Bahasa Indonesia
     * menjadi Bahasa Inggris agar konsisten dengan $urutanBulan sales module.
     * 'Triwulan I-IV' pada sales_incentive_settings tetap tidak berubah.
     */
    public function up(): void
    {
        $map = [
            'Januari'   => 'January',
            'Februari'  => 'February',
            'Maret'     => 'March',
            'April'     => 'April',
            'Mei'       => 'May',
            'Juni'      => 'June',
            'Juli'      => 'July',
            'Agustus'   => 'August',
            'September' => 'September',
            'Oktober'   => 'October',
            'November'  => 'November',
            'Desember'  => 'December',
        ];

        $targets = [
            'sales'                      => 'bulan',
            'sales_targets'              => 'bulan',
            'sales_incentive_settings'   => 'bulan',
            'sales_forecast_orders'      => 'bulan_acuan',
            'sales_forecast_settings'    => 'bulan_acuan',
        ];

        foreach ($targets as $table => $column) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, $column)) {
                continue;
            }
            foreach ($map as $indonesia => $english) {
                DB::table($table)->where($column, $indonesia)->update([$column => $english]);
            }
        }
    }

    public function down(): void
    {
        $map = [
            'January'   => 'Januari',
            'February'  => 'Februari',
            'March'     => 'Maret',
            'April'     => 'April',
            'May'       => 'Mei',
            'June'      => 'Juni',
            'July'      => 'Juli',
            'August'    => 'Agustus',
            'September' => 'September',
            'October'   => 'Oktober',
            'November'  => 'November',
            'December'  => 'Desember',
        ];

        $targets = [
            'sales'                      => 'bulan',
            'sales_targets'              => 'bulan',
            'sales_incentive_settings'   => 'bulan',
            'sales_forecast_orders'      => 'bulan_acuan',
            'sales_forecast_settings'    => 'bulan_acuan',
        ];

        foreach ($targets as $table => $column) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, $column)) {
                continue;
            }
            foreach ($map as $english => $indonesia) {
                DB::table($table)->where($column, $english)->update([$column => $indonesia]);
            }
        }
    }
};
