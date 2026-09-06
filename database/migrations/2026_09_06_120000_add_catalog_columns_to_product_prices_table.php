<?php

use App\Models\Barang;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadikan product_prices self-contained untuk katalog jual dengan menyalin
     * data form tambah (kode, nama, satuan, sediaan/presentation, kuantitas isi/pack_qty)
     * ke tabel pricing. pack_qty disimpan sebagai angka multiplier, bukan di-parse
     * dari string presentation lagi.
     */
    public function up(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->string('product_code', 50)->nullable()->after('barang_id');
            $table->string('product_name', 255)->nullable()->after('product_code');
            $table->string('unit', 50)->nullable()->after('product_name');
            $table->string('presentation', 100)->nullable()->after('unit');
            $table->unsignedInteger('pack_qty')->default(1)->after('presentation');
        });

        // Backfill kolom baru dari data barangs yang sudah ada.
        $rows = DB::table('product_prices')
            ->join('barangs', 'barangs.id', '=', 'product_prices.barang_id')
            ->select(
                'product_prices.id',
                'barangs.product_code',
                'barangs.product_name',
                'barangs.unit',
                'barangs.presentation'
            )
            ->get();

        foreach ($rows as $row) {
            DB::table('product_prices')->where('id', $row->id)->update([
                'product_code' => $row->product_code,
                'product_name' => $row->product_name,
                'unit' => $row->unit,
                'presentation' => $row->presentation,
                'pack_qty' => max(1, Barang::packCount($row->presentation)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->dropColumn(['product_code', 'product_name', 'unit', 'presentation', 'pack_qty']);
        });
    }
};