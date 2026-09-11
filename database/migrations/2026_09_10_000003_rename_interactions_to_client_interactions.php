<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Refactor interactions -> client_interactions (English, konsisten).
 *
 * Tabel   : interactions -> client_interactions
 * Kolom   :
 *  nama_produk      -> product_name
 *  jenis_transaksi  -> transaction_type   (nilai IN/OUT/ENTERTAIN tetap)
 *  lokasi           -> location
 *  peserta          -> participants
 *  nilai_kontribusi -> amount
 *  tanggal_interaksi-> interaction_date
 *  catatan          -> notes
 *  nilai_sales      -> sales_amount
 *  komisi           -> commission_rate    (sumber kebenaran rate, %)
 *
 * Rate: TIDAK tambah kolom baru — kolom komisi yang ada di-rename menjadi
 * commission_rate sebagai single source of truth. Backfill: baris IN yang
 * commission_rate-nya NULL tapi notes mengandung "[Rate:X%]" diisi dari notes.
 * Ke depan controller berhenti menulis prefix "[Rate:..]" ke notes; notes bersih.
 */
return new class extends Migration
{
    private array $renames = [
        'nama_produk'       => 'product_name',
        'jenis_transaksi'   => 'transaction_type',
        'lokasi'            => 'location',
        'peserta'           => 'participants',
        'nilai_kontribusi'  => 'amount',
        'tanggal_interaksi' => 'interaction_date',
        'catatan'           => 'notes',
        'nilai_sales'       => 'sales_amount',
        'komisi'            => 'commission_rate',
    ];

    private function table(): string
    {
        return Schema::hasTable('client_interactions') ? 'client_interactions' : 'interactions';
    }

    public function up(): void
    {
        if (Schema::hasTable('interactions') && ! Schema::hasTable('client_interactions')) {
            Schema::rename('interactions', 'client_interactions');
        }

        if (! Schema::hasTable('client_interactions')) {
            return;
        }

        foreach ($this->renames as $old => $new) {
            if (Schema::hasColumn('client_interactions', $old)
                && ! Schema::hasColumn('client_interactions', $new)) {
                Schema::table('client_interactions', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        // Backfill commission_rate dari prefix "[Rate:X%]" di notes (hanya IN + NULL).
        if (Schema::hasColumn('client_interactions', 'commission_rate')
            && Schema::hasColumn('client_interactions', 'notes')) {
            $rows = DB::table('client_interactions')
                ->select('id', 'notes')
                ->whereNull('commission_rate')
                ->where('transaction_type', 'IN')
                ->where('notes', 'like', '%[Rate:%')
                ->get();
            foreach ($rows as $row) {
                if (preg_match('/\[Rate:([\d\.]+)%?\]/', (string) $row->notes, $m)) {
                    DB::table('client_interactions')
                        ->where('id', $row->id)
                        ->update(['commission_rate' => (float) $m[1]]);
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_interactions')) {
            return;
        }

        foreach (array_reverse($this->renames, true) as $old => $new) {
            if (Schema::hasColumn('client_interactions', $new)
                && ! Schema::hasColumn('client_interactions', $old)) {
                Schema::table('client_interactions', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($new, $old);
                });
            }
        }

        Schema::rename('client_interactions', 'interactions');
    }
};
