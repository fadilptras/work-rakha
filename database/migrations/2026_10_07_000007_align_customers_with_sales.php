<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Selaraskan master customers dengan nama-nama di tabel sales:
 * 1. Varian ejaan -> isi sales_customer_name (alias) bila unik.
 * 2. Nama sales tanpa customer -> buatkan baris (tipe terinferensi).
 * 3. Isi customer_type yang masih kosong (aturan pola nama).
 *
 * Idempoten: baris/alias yang sudah ada dilewati. Ambigu hanya dilaporkan.
 */
return new class extends Migration
{
    private function norm(?string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower(trim((string) $s)));
    }

    private function inferType(string $name): string
    {
        $u = mb_strtoupper($name);
        if (preg_match('/^\s*RSUD\b/', $u)) return 'RSUD';
        if (str_contains($u, 'RSPAD') || str_contains($u, 'BHAYANGKARA') || str_contains($u, 'TNI') || str_contains($u, 'POLRI')) return 'RS TNI-Polri';
        if (str_contains($u, 'KLINIK')) return 'Klinik';
        if (str_contains($u, 'PUSKESMAS') || str_starts_with($u, 'PKM')) return 'Puskesmas';
        if (str_contains($u, 'APOTEK')) return 'Apotek';
        if (str_contains($u, 'LAB')) return 'Lab';
        if (preg_match('/^\s*(PT|CV)\b/', $u)) return 'Distributor';
        if (str_contains($u, 'HOSPITAL') || preg_match('/^\s*RS\b/', $u) || str_starts_with($u, 'RSU')) return 'RS Swasta';

        return 'Lainnya';
    }

    public function up(): void
    {
        // Peta customer: normalized key -> ids.
        $custByKey = [];
        foreach (DB::table('customers')->select('id', 'customer_name')->get() as $c) {
            $custByKey[$this->norm($c->customer_name)][] = $c->id;
        }

        $salesNames = DB::table('sales')
            ->select('customer_name', DB::raw('COUNT(*) AS c'))
            ->whereNotNull('customer_name')->where('customer_name', '!=', '')
            ->groupBy('customer_name')->orderByDesc(DB::raw('COUNT(*)'))->get();

        $aliased = 0;
        $created = 0;
        $ambiguous = [];
        foreach ($salesNames as $s) {
            // 1. Cocok persis -> sudah selaras.
            if (DB::table('customers')->where('customer_name', $s->customer_name)->exists()) {
                continue;
            }
            // 2. Cocok normalisasi unik -> isi alias.
            $key = $this->norm($s->customer_name);
            $candidates = $custByKey[$key] ?? [];
            if (count($candidates) === 1) {
                $cid = $candidates[0];
                $cur = DB::table('customers')->where('id', $cid)->value('sales_customer_name');
                if (!$cur) {
                    DB::table('customers')->where('id', $cid)->update([
                        'sales_customer_name' => $s->customer_name,
                        'updated_at' => now(),
                    ]);
                    $aliased++;
                    echo "ALIAS {$s->customer_name} ({$s->c} trx)\n";
                }
                continue;
            }
            if (count($candidates) > 1) {
                $ambiguous[] = $s->customer_name . ' (ambigu: ' . count($candidates) . ' customer)';
                continue;
            }
            // 3. Tidak ada -> buatkan baris skeleton.
            $id = DB::table('customers')->insertGetId([
                'customer_name' => $s->customer_name,
                'customer_type' => $this->inferType($s->customer_name),
                'is_deleted' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $custByKey[$key][] = $id;
            $created++;
            echo "BUAT {$s->customer_name} ({$s->c} trx)\n";
        }

        // 4. Isi tipe kosong pada customer lama.
        $typed = 0;
        foreach (DB::table('customers')->select('id', 'customer_name')->whereNull('customer_type')->get() as $c) {
            DB::table('customers')->where('id', $c->id)->update([
                'customer_type' => $this->inferType($c->customer_name),
                'updated_at' => now(),
            ]);
            $typed++;
        }

        echo "OK align: {$aliased} alias, {$created} baru, {$typed} tipe dilengkapi\n";
        foreach ($ambiguous as $a) {
            echo "AMBIGU: {$a}\n";
        }
    }

    public function down(): void
    {
        echo "SKIP down align customers (data dipertahankan)\n";
    }
};
