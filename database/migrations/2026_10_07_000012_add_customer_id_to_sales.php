<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tautkan sales ke master customers (pengganti cocok-string):
     * - tambah customer_id nullable + indeks (customer_id, date).
     * - backfill: cocok persis customer_name -> alias customers ->
     *   normalisasi; sisanya null + dilaporkan.
     * - drop kolom yatim barang_id (tak dipakai kode mana pun).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'customer_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->after('id')
                    ->constrained('customers')->nullOnDelete();
            });
            echo "OK sales: kolom customer_id ditambah\n";
        }

        $byName = [];
        foreach (DB::table('customers')->select('id', 'customer_name')->get() as $c) {
            $byName[$c->customer_name][] = $c->id;
        }
        $byAlias = [];
        foreach (DB::table('customers')->select('id', 'sales_customer_name')->whereNotNull('sales_customer_name')->where('sales_customer_name', '!=', '')->get() as $c) {
            $byAlias[$c->sales_customer_name][] = $c->id;
        }
        $byNorm = [];
        foreach (DB::table('customers')->select('id', 'customer_name')->get() as $c) {
            $key = preg_replace('/[^a-z0-9]/', '', mb_strtolower(trim((string) $c->customer_name)));
            if ($key !== '') {
                $byNorm[$key][] = $c->id;
            }
        }
        $norm = fn ($s) => preg_replace('/[^a-z0-9]/', '', mb_strtolower(trim((string) $s)));

        $linked = 0;
        $unlinked = [];
        foreach (DB::table('sales')->select('customer_name')->whereNull('customer_id')->whereNotNull('customer_name')->where('customer_name', '!=', '')->groupBy('customer_name')->get() as $row) {
            $name = $row->customer_name;
            $ids = $byName[$name] ?? $byAlias[$name] ?? $byNorm[$norm($name)] ?? [];
            $ids = array_values(array_unique($ids));
            if (count($ids) === 1) {
                DB::table('sales')->whereNull('customer_id')->where('customer_name', $name)->update(['customer_id' => $ids[0]]);
                $linked++;
            } else {
                $unlinked[] = $name . (count($ids) > 1 ? ' (ambigu: ' . count($ids) . ')' : '');
            }
        }
        $rows = DB::table('sales')->whereNotNull('customer_id')->count();
        echo "OK sales.customer_id backfill: {$linked} nama tertaut ({$rows} baris)\n";
        foreach ($unlinked as $u) {
            echo "TANPA-PASANGAN sales: {$u}\n";
        }

        if (!self::hasIndex('sales', 'sales_customer_id_date_index')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->index(['customer_id', 'date'], 'sales_customer_id_date_index');
            });
            echo "OK sales: indeks (customer_id, date)\n";
        }

        if (Schema::hasColumn('sales', 'barang_id')) {
            $fk = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales'
                 AND COLUMN_NAME = 'barang_id' AND REFERENCED_TABLE_NAME IS NOT NULL"
            );
            if ($fk) {
                DB::statement('ALTER TABLE `sales` DROP FOREIGN KEY `' . $fk->CONSTRAINT_NAME . '`');
            }
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('barang_id');
            });
            echo "OK sales: drop kolom yatim barang_id\n";
        }
    }

    private static function hasIndex(string $table, string $name): bool
    {
        $found = DB::selectOne(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
            [$table, $name]
        );

        return ($found->c ?? 0) > 0;
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'customer_id')) {
                $table->dropConstrainedForeignId('customer_id');
            }
        });
        echo "ROLLBACK sales.customer_id\n";
    }
};
