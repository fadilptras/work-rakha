<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pindahkan peran tabel barangs ke products untuk fitur stock:
 * stock_log_details.barang_id dan daily_stock_histories.barang_id
 * menjadi product_id yang menunjuk ke tabel products.
 *
 * Data riwayat lama (barang_id id barangs 828-1001 yang sudah tidak ada)
 * dipertahankan dengan product_id NULL karena tidak memiliki mapping kode.
 */
return new class extends Migration
{
    public function up(): void
    {
        $fkExists = function (string $table, string $name): bool {
            $r = DB::selectOne(
                "SELECT COUNT(*) AS c FROM information_schema.table_constraints
                 WHERE table_schema = DATABASE() AND table_name = ? AND constraint_name = ? AND constraint_type = 'FOREIGN KEY'",
                [$table, $name]
            );

            return (int) ($r->c ?? 0) > 0;
        };

        $indexExists = function (string $table, string $name): bool {
            $r = DB::selectOne(
                "SELECT COUNT(*) AS c FROM information_schema.statistics
                 WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
                [$table, $name]
            );

            return (int) ($r->c ?? 0) > 0;
        };

        // ================= daily_stock_histories =================
        $fkDsh = 'daily_stock_histories_barang_id_foreign';
        $ukDsh = 'daily_stock_histories_barang_id_tanggal_unique';

        if ($fkExists('daily_stock_histories', $fkDsh)) {
            DB::statement("ALTER TABLE daily_stock_histories DROP FOREIGN KEY $fkDsh");
        }
        if ($indexExists('daily_stock_histories', $ukDsh)) {
            DB::statement("ALTER TABLE daily_stock_histories DROP INDEX $ukDsh");
        }

        $cols = array_column(DB::select('SHOW COLUMNS FROM daily_stock_histories'), 'Field');
        if (in_array('barang_id', $cols)) {
            DB::statement('ALTER TABLE daily_stock_histories CHANGE COLUMN barang_id product_id BIGINT UNSIGNED NULL');
        }

        // Data riwayat lama tidak memiliki mapping kode -> kosongkan agar FK ke products valid.
        DB::statement('UPDATE daily_stock_histories SET product_id = NULL WHERE product_id IS NOT NULL AND product_id NOT IN (SELECT id FROM products)');

        DB::statement('ALTER TABLE daily_stock_histories ADD CONSTRAINT daily_stock_histories_product_id_foreign FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE daily_stock_histories ADD UNIQUE KEY daily_stock_histories_product_id_tanggal_unique (product_id, tanggal)');

        // ================= stock_log_details =================
        $fkSld = 'stock_log_details_barang_id_foreign';

        if ($fkExists('stock_log_details', $fkSld)) {
            DB::statement("ALTER TABLE stock_log_details DROP FOREIGN KEY $fkSld");
        }

        $cols = array_column(DB::select('SHOW COLUMNS FROM stock_log_details'), 'Field');
        if (in_array('barang_id', $cols)) {
            DB::statement('ALTER TABLE stock_log_details CHANGE COLUMN barang_id product_id BIGINT UNSIGNED NULL');
        }

        // Data riwayat lama tidak memiliki mapping kode -> kosongkan agar FK ke products valid.
        DB::statement('UPDATE stock_log_details SET product_id = NULL WHERE product_id IS NOT NULL AND product_id NOT IN (SELECT id FROM products)');

        DB::statement('ALTER TABLE stock_log_details ADD CONSTRAINT stock_log_details_product_id_foreign FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        $fkExists = function (string $table, string $name): bool {
            $r = DB::selectOne(
                "SELECT COUNT(*) AS c FROM information_schema.table_constraints
                 WHERE table_schema = DATABASE() AND table_name = ? AND constraint_name = ? AND constraint_type = 'FOREIGN KEY'",
                [$table, $name]
            );

            return (int) ($r->c ?? 0) > 0;
        };

        $indexExists = function (string $table, string $name): bool {
            $r = DB::selectOne(
                "SELECT COUNT(*) AS c FROM information_schema.statistics
                 WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
                [$table, $name]
            );

            return (int) ($r->c ?? 0) > 0;
        };

        if ($fkExists('daily_stock_histories', 'daily_stock_histories_product_id_foreign')) {
            DB::statement('ALTER TABLE daily_stock_histories DROP FOREIGN KEY daily_stock_histories_product_id_foreign');
        }
        if ($indexExists('daily_stock_histories', 'daily_stock_histories_product_id_tanggal_unique')) {
            DB::statement('ALTER TABLE daily_stock_histories DROP INDEX daily_stock_histories_product_id_tanggal_unique');
        }

        $cols = array_column(DB::select('SHOW COLUMNS FROM daily_stock_histories'), 'Field');
        if (in_array('product_id', $cols)) {
            DB::statement('ALTER TABLE daily_stock_histories CHANGE COLUMN product_id barang_id BIGINT UNSIGNED NULL');
        }

        DB::statement('ALTER TABLE daily_stock_histories ADD CONSTRAINT daily_stock_histories_barang_id_foreign FOREIGN KEY (barang_id) REFERENCES barangs(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE daily_stock_histories ADD UNIQUE KEY daily_stock_histories_barang_id_tanggal_unique (barang_id, tanggal)');

        if ($fkExists('stock_log_details', 'stock_log_details_product_id_foreign')) {
            DB::statement('ALTER TABLE stock_log_details DROP FOREIGN KEY stock_log_details_product_id_foreign');
        }

        $cols = array_column(DB::select('SHOW COLUMNS FROM stock_log_details'), 'Field');
        if (in_array('product_id', $cols)) {
            DB::statement('ALTER TABLE stock_log_details CHANGE COLUMN product_id barang_id BIGINT UNSIGNED NULL');
        }

        DB::statement('ALTER TABLE stock_log_details ADD CONSTRAINT stock_log_details_barang_id_foreign FOREIGN KEY (barang_id) REFERENCES barangs(id) ON DELETE CASCADE');
    }
};