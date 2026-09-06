<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyamakan nama PS di tabel sales_targets dengan nama lengkap akun karyawan,
     * agar cocok dengan tabel sales yang sudah memakai nama lengkap
     * (pencocokan target <-> penjualan di command center konsisten).
     */
    public function up(): void
    {
        $map = [
            'Arief'   => 'Arief Natanael Haryanto',
            'Eko'     => 'Eko Sigit Nugroho',
            'Hendra'  => 'Rusiman Hendra Dipraja',
            'Karsono' => 'Karsono Nu Haeman',
        ];

        if (Schema::hasTable('sales_targets') && Schema::hasColumn('sales_targets', 'ps')) {
            foreach ($map as $short => $full) {
                DB::table('sales_targets')->where('ps', $short)->update(['ps' => $full]);
            }
        }
    }

    public function down(): void
    {
        $map = [
            'Arief Natanael Haryanto' => 'Arief',
            'Eko Sigit Nugroho'       => 'Eko',
            'Rusiman Hendra Dipraja'  => 'Hendra',
            'Karsono Nu Haeman'       => 'Karsono',
        ];

        if (Schema::hasTable('sales_targets') && Schema::hasColumn('sales_targets', 'ps')) {
            foreach ($map as $full => $short) {
                DB::table('sales_targets')->where('ps', $full)->update(['ps' => $short]);
            }
        }
    }
};
