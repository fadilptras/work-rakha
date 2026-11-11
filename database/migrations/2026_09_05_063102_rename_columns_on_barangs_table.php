<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel barangs ke Bahasa Inggris (English).
     * Idempoten: rename hanya bila kolom lama ada dan kolom baru belum ada;
     * unique hanya di-drop/dibuat bila perlu (DB lama sudah konvergen).
     */
    public function up(): void
    {
        $this->dropIndexIfExists('barangs', 'barangs_kode_barang_unique');
        $this->dropIndexIfExists('barangs', 'barangs_nama_barang_unique');

        $pairs = [
            'kode_barang' => 'product_code',
            'nama_barang' => 'product_name',
            'satuan' => 'unit',
            'sediaan' => 'presentation',
            'stok' => 'stock',
            'stok_po' => 'stock_po',
            'harga' => 'base_price',
            'harga_satuan_terkecil' => 'unit_price',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('barangs', $old) && ! Schema::hasColumn('barangs', $new)) {
                Schema::table('barangs', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        $this->addUniqueIfMissing('barangs', ['product_code'], 'barangs_product_code_unique');
        $this->addUniqueIfMissing('barangs', ['product_name'], 'barangs_product_name_unique');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('barangs', 'barangs_product_code_unique');
        $this->dropIndexIfExists('barangs', 'barangs_product_name_unique');

        $pairs = [
            'product_code' => 'kode_barang',
            'product_name' => 'nama_barang',
            'unit' => 'satuan',
            'presentation' => 'sediaan',
            'stock' => 'stok',
            'stock_po' => 'stok_po',
            'base_price' => 'harga',
            'unit_price' => 'harga_satuan_terkecil',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('barangs', $old) && ! Schema::hasColumn('barangs', $new)) {
                Schema::table('barangs', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        $this->addUniqueIfMissing('barangs', ['kode_barang'], 'barangs_kode_barang_unique');
        $this->addUniqueIfMissing('barangs', ['nama_barang'], 'barangs_nama_barang_unique');
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
        Schema::table($table, function (Blueprint $table) use ($columns, $index) {
            $table->unique($columns, $index);
        });
    }
};
