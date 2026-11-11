<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom dokumen ala surat jalan untuk mutasi manual (WsMutation):
     * - doc_no  : nomor surat jalan (supplier untuk masuk, kita untuk keluar)
     * - partner : snapshot nama supplier / customer saat pencatatan
     * - po_no   : nomor PO / referensi (opsional)
     *
     * Idempoten: setiap kolom dicek hasColumn lebih dulu.
     */
    public function up(): void
    {
        Schema::table('stock_moves', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_moves', 'doc_no')) {
                $table->string('doc_no', 100)->nullable()->after('batch_no');
            }
            if (! Schema::hasColumn('stock_moves', 'partner')) {
                $table->string('partner', 255)->nullable()->after('doc_no');
            }
            if (! Schema::hasColumn('stock_moves', 'po_no')) {
                $table->string('po_no', 100)->nullable()->after('partner');
            }
        });
        echo "OK stock_moves: kolom doc_no/partner/po_no dipastikan\n";
    }

    public function down(): void
    {
        Schema::table('stock_moves', function (Blueprint $table) {
            $drop = [];
            foreach (['doc_no', 'partner', 'po_no'] as $col) {
                if (Schema::hasColumn('stock_moves', $col)) {
                    $drop[] = $col;
                }
            }
            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
        echo "DROP kolom doc_no/partner/po_no (bila ada)\n";
    }
};
