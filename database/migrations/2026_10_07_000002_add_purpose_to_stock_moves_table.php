<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keperluan barang keluar (tidak selalu penjualan: sample, bonus, dll).
     * Header-level, disimpan per baris (snapshot) agar riwayat tetap jelas.
     */
    public function up(): void
    {
        Schema::table('stock_moves', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_moves', 'purpose')) {
                $table->string('purpose', 50)->nullable()->after('po_no');
            }
        });
        echo "OK stock_moves: kolom purpose dipastikan\n";
    }

    public function down(): void
    {
        Schema::table('stock_moves', function (Blueprint $table) {
            if (Schema::hasColumn('stock_moves', 'purpose')) {
                $table->dropColumn('purpose');
            }
        });
        echo "DROP kolom purpose (bila ada)\n";
    }
};
