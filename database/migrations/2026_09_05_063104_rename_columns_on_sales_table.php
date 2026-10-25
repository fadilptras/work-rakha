<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan nama kolom tabel sales ke Bahasa Inggris (English).
     * Idempoten: rename hanya bila perlu; change() month hanya bila tipe belum sesuai.
     */
    public function up(): void
    {
        $pairs = [
            'tanggal' => 'date',
            'nama_customer' => 'customer_name',
            'nama_produk' => 'product_name',
            'satuan' => 'unit',
            'hna' => 'base_price',
            'diskon' => 'discount',
            'harga_nett' => 'net_price',
            'bulan' => 'month',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('sales', $old) && ! Schema::hasColumn('sales', $new)) {
                Schema::table('sales', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }

        $type = DB::selectOne(
            "SELECT COLUMN_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'month'"
        );
        if ($type) {
            $type = (array) $type;
            if ($type['COLUMN_TYPE'] !== 'varchar(20)' || $type['IS_NULLABLE'] !== 'YES') {
                Schema::table('sales', function (Blueprint $table) {
                    $table->string('month', 20)->nullable()->change();
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
            'date' => 'tanggal',
            'customer_name' => 'nama_customer',
            'product_name' => 'nama_produk',
            'unit' => 'satuan',
            'base_price' => 'hna',
            'discount' => 'diskon',
            'net_price' => 'harga_nett',
            'month' => 'bulan',
        ];

        foreach ($pairs as $old => $new) {
            if (Schema::hasColumn('sales', $old) && ! Schema::hasColumn('sales', $new)) {
                Schema::table('sales', function (Blueprint $table) use ($old, $new) {
                    $table->renameColumn($old, $new);
                });
            }
        }
    }
};
