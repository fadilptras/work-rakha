<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyamakan nama PS di tabel sales dengan nama lengkap akun karyawan di tabel users
     * dengan divisi 'Marketing dan Operasional', agar pencocokan PS baru konsisten.
     */
    public function up(): void
    {
        $map = [
            'Arief'      => 'Arief Natanael Haryanto',
            'Eko'        => 'Eko Sigit Nugroho',
            'Hendra'     => 'Rusiman Hendra Dipraja',
            'Karsono'    => 'Karsono Nu Haeman',
        ];

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'ps')) {
            foreach ($map as $short => $full) {
                DB::table('sales')->where('ps', $short)->update(['ps' => $full]);
            }
        }

        // Selaraskan akun karyawan Hendra di tabel users (R -> Rusiman)
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'name')) {
            $adaRusiman = DB::table('users')->where('name', 'Rusiman Hendra Dipraja')->exists();
            if (!$adaRusiman) {
                DB::table('users')->where('name', 'R Hendra Dipraja')->update(['name' => 'Rusiman Hendra Dipraja']);
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

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'ps')) {
            foreach ($map as $full => $short) {
                DB::table('sales')->where('ps', $full)->update(['ps' => $short]);
            }
        }

        // Kembalikan nama akun karyawan Hendra (Rusiman -> R)
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'name')) {
            DB::table('users')->where('name', 'Rusiman Hendra Dipraja')->update(['name' => 'R Hendra Dipraja']);
        }
    }
};
