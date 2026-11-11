<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot dokumen per baris (cermin ws_mutation, untuk tampilan cepat):
     * - ship_address : snapshot alamat kirim/terima.
     * - expedition   : ekspedisi pengiriman (arah keluar).
     *
     * Idempoten: cek hasColumn.
     */
    public function up(): void
    {
        Schema::table('stock_moves', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_moves', 'ship_address')) {
                $table->text('ship_address')->nullable()->after('po_no');
            }
            if (!Schema::hasColumn('stock_moves', 'expedition')) {
                $table->string('expedition', 100)->nullable()->after('ship_address');
            }
        });
        echo "OK stock_moves: kolom ship_address/expedition dipastikan\n";
    }

    public function down(): void
    {
        Schema::table('stock_moves', function (Blueprint $table) {
            $drop = [];
            foreach (['ship_address', 'expedition'] as $col) {
                if (Schema::hasColumn('stock_moves', $col)) {
                    $drop[] = $col;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
        echo "ROLLBACK kolom ship_address/expedition\n";
    }
};
