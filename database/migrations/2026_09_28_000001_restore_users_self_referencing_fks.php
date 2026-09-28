<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membangun ulang FK self-reference tabel users yang hilang
     * (pernah dibuat manual lalu terhapus dari production).
     *
     * - Idempoten: kolom yang tidak ada dilewati, FK yang sudah ada tidak dibuat ulang.
     * - Resilien: tiap kolom dicoba dengan beberapa varian (index eksplisit,
     *   nama standar/pendek, rule CASCADE/RESTRICT). Kegagalan satu kolom
     *   dicatat dan dilewati TANPA menggugurkan kolom lain.
     * - Sebelum membuat FK, nilai yatim dibersihkan ke NULL agar ADD CONSTRAINT
     *   tidak ditolak.
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

        foreach ($columns as $column) {
            if (! Schema::hasTable('users') || ! Schema::hasColumn('users', $column)) {
                $this->note("SKIP {$column}: kolom tidak ada");
                continue;
            }

            if ($this->foreignKeyExists('users', $column)) {
                $this->note("SKIP {$column}: FK sudah ada");
                continue;
            }

            // Bersihkan nilai yatim lebih dulu agar FK bisa dibuat.
            DB::statement(
                "UPDATE `users` AS u LEFT JOIN `users` AS p ON p.`id` = u.`{$column}` " .
                "SET u.`{$column}` = NULL WHERE u.`{$column}` IS NOT NULL AND p.`id` IS NULL"
            );

            // Pastikan ada index pendukung eksplisit (hindari auto-create bermasalah).
            if (! $this->indexExists('users', $column)) {
                try {
                    DB::statement("ALTER TABLE `users` ADD INDEX `idx_users_{$column}` (`{$column}`)");
                } catch (\Throwable $e) {
                    // abaikan: lanjut ke pembuatan FK
                }
            }

            // Varian 1: nama standar Laravel + CASCADE (default framework).
            // Varian 2: nama pendek custom (jaga-jaga konflik nama di shared hosting).
            // Varian 3: rule RESTRICT saat update (menyamai FK manual lama fk_*).
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
     * Reverse the migrations.
     * Hanya menghapus FK yang mungkin dibuat oleh migration ini
     * (nama kandidatnya saja) — FK bawaan lain tidak disentuh.
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
                    // abaikan: tidak ada atau sudah dihapus manual
                }
            }
        }
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

    /**
     * Output aman di semua konteks (artisan maupun tinker).
     * Migration base class tidak punya line()/command.
     */
    protected function note(string $msg): void
    {
        echo $msg . PHP_EOL;
    }
};
