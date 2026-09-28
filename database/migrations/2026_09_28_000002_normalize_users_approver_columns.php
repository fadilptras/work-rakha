<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lanjutan 000001: menangani kolom yang ADD CONSTRAINT-nya gagal errno 150
     * (terbukti di production: approver_dana_1/2/3_id — tipe kolom tidak identik
     * dengan users.id, sisa pembuatan manual lama).
     *
     * - Idempoten: kolom yang tipenya sudah cocok dilewati, FK yang sudah ada dilewati.
     * - Tipe acuan diambil dinamis dari users.id (bukan hardcoded).
     * - Resilien: tiap kolom dicoba dengan beberapa varian nama/rule;
     *   kegagalan dicatat per kolom tanpa menggugurkan yang lain.
     */
    public function up(): void
    {
        $columns = [
            'approver_dana_1_id', 'approver_dana_2_id', 'approver_dana_3_id', 'approver_dana_4_id',
            'approver_cuti_1_id', 'approver_cuti_2_id', 'approver_cuti_3_id', 'approver_cuti_4_id',
            'approver_barang_1_id', 'approver_barang_2_id', 'approver_barang_3_id', 'approver_barang_4_id',
            'kpi_evaluator_id', 'kpi_approver_1_id', 'kpi_approver_2_id',
            'approver_1_id', 'approver_2_id', 'manager_keuangan_id', 'atasan_id',
        ];

        $parent = $this->columnInfo('users', 'id');
        if (! $parent) {
            $this->note('BATAL: tidak bisa membaca definisi users.id');
            return;
        }
        $this->note("Acuan: users.id = {$parent->type} NULLABLE={$parent->nullable}");

        foreach ($columns as $column) {
            if (! Schema::hasTable('users') || ! Schema::hasColumn('users', $column)) {
                $this->note("SKIP {$column}: kolom tidak ada");
                continue;
            }

            if ($this->foreignKeyExists('users', $column)) {
                $this->note("SKIP {$column}: FK sudah ada");
                continue;
            }

            $info = $this->columnInfo('users', $column);
            $this->note("CEK {$column}: {$info->type} NULLABLE={$info->nullable}");

            if ($info->nullable !== 'YES') {
                $this->note("LEWATI {$column}: kolom NOT NULL (butuh keputusan manual, tidak diubah otomatis)");
                continue;
            }

            // Normalisasi tipe agar identik dengan users.id (mis. bigint signed -> bigint unsigned).
            if (strtolower($info->type) !== strtolower($parent->type)) {
                try {
                    DB::statement("ALTER TABLE `users` MODIFY COLUMN `{$column}` {$parent->type} NULL");
                    $this->note("NORMALISASI {$column}: {$info->type} -> {$parent->type}");
                } catch (\Throwable $e) {
                    $this->note("FAIL normalisasi {$column}: " . $this->shortError($e));
                    continue;
                }
            }

            // Bersihkan nilai yatim agar ADD CONSTRAINT tidak ditolak.
            DB::statement(
                "UPDATE `users` AS u LEFT JOIN `users` AS p ON p.`id` = u.`{$column}` " .
                "SET u.`{$column}` = NULL WHERE u.`{$column}` IS NOT NULL AND p.`id` IS NULL"
            );

            if (! $this->indexExists('users', $column)) {
                try {
                    DB::statement("ALTER TABLE `users` ADD INDEX `idx_users_{$column}` (`{$column}`)");
                } catch (\Throwable $e) {
                    // abaikan: lanjut ke pembuatan FK
                }
            }

            $attempts = [
                ["users_{$column}_foreign", 'CASCADE'],
                ['fk_users_' . substr($column, 0, 20), 'CASCADE'],
                ["users_{$column}_foreign", 'RESTRICT'],
            ];

            $done = false;
            foreach ($attempts as [$name, $updateRule]) {
                try {
                    DB::statement(
                        "ALTER TABLE `users` ADD CONSTRAINT `{$name}` " .
                        "FOREIGN KEY (`{$column}`) REFERENCES `users` (`id`) " .
                        "ON DELETE SET NULL ON UPDATE {$updateRule}"
                    );
                    $done = true;
                    $this->note("OK {$column} -> {$name} (ON UPDATE {$updateRule})");
                    break;
                } catch (\Throwable $e) {
                    $this->note("FAIL {$column} -> {$name}: " . $this->shortError($e));
                }
            }

            if (! $done) {
                $this->note("LEWATI {$column}: semua varian gagal, perlu diagnosis manual");
            }
        }
    }

    /**
     * Reverse the migrations: hapus FK yang dibuat migration ini/000001
     * (hanya nama kandidat, FK bawaan lain tidak disentuh).
     */
    public function down(): void
    {
        $columns = [
            'approver_dana_1_id', 'approver_dana_2_id', 'approver_dana_3_id', 'approver_dana_4_id',
            'approver_cuti_1_id', 'approver_cuti_2_id', 'approver_cuti_3_id', 'approver_cuti_4_id',
            'approver_barang_1_id', 'approver_barang_2_id', 'approver_barang_3_id', 'approver_barang_4_id',
            'kpi_evaluator_id', 'kpi_approver_1_id', 'kpi_approver_2_id',
            'approver_1_id', 'approver_2_id', 'manager_keuangan_id', 'atasan_id',
        ];

        foreach ($columns as $column) {
            foreach (["users_{$column}_foreign", 'fk_users_' . substr($column, 0, 20)] as $name) {
                try {
                    DB::statement("ALTER TABLE `users` DROP FOREIGN KEY `{$name}`");
                    $this->note("DROP FK {$name}");
                } catch (\Throwable $e) {
                    // abaikan
                }
            }
        }
    }

    protected function columnInfo(string $table, string $column): ?object
    {
        $row = DB::selectOne(
            "SELECT COLUMN_TYPE AS type, IS_NULLABLE AS nullable FROM INFORMATION_SCHEMA.COLUMNS " .
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        );

        return $row ?: null;
    }

    protected function foreignKeyExists(string $table, string $column): bool
    {
        $row = DB::selectOne(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE " .
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? " .
            "AND REFERENCED_TABLE_NAME = 'users' AND REFERENCED_COLUMN_NAME = 'id'",
            [$table, $column]
        );

        return $row && (int) $row->c > 0;
    }

    protected function indexExists(string $table, string $column): bool
    {
        $row = DB::selectOne(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.STATISTICS " .
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        );

        return $row && (int) $row->c > 0;
    }

    protected function shortError(\Throwable $e): string
    {
        $msg = $e->getMessage();
        $first = strtok($msg, "\n");
        if (preg_match('/CONSTRAINT `([^`]+)`/', $msg, $m)) {
            return $first . " [constraint: {$m[1]}]";
        }

        return mb_substr($first, 0, 200);
    }

    protected function note(string $msg): void
    {
        echo $msg . PHP_EOL;
    }
};
