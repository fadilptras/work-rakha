<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

/**
 * Isi product_name_clean yang masih kosong dari product_name.
 *
 * Aturan transformasi:
 *  - Rapikan spasi ganda.
 *  - '*' -> 'x' (dimensi).
 *  - Title case untuk kata biasa ('RAKHA Kasa Katun Premium').
 *  - Kata dengan kapital tengah / merek (EasyFoam, XRay, dst) dipertahankan.
 *  - Suffix divisi (MUA, SL, NT, GMI, GL, AMI, IV, FR, EDTA, dst) tetap kapital.
 *  - 'NN L'/'NN LT' -> 'NN Liter' hanya pada nama NON-Kasa (konteks volume).
 *  - Nama berawalan 'Kasa' -> diberi prefiks 'RAKHA ' (kecuali sudah RAKHA).
 */
class AutoCleanProductNamesCommand extends Command
{
    protected $signature = 'product:auto-clean-names {--dry-run : Tampilkan perubahan tanpa menyimpan}';
    protected $description = 'Generate product_name_clean dari product_name untuk produk yang masih kosong';

    /** Token yang dipertahankan apa adanya (merek/suffix divisi). */
    protected const PRESERVE = [
        'RAKHA', 'MUA', 'SL', 'NT', 'GMI', 'GL', 'AMI', 'IV', 'FR',
        'EDTA', 'MEDITUELL', 'REAL', 'XRAY', 'XRay', 'Xray', 'X-Ray',
        'EasyFoam', 'T', 'K3',
    ];

    /** Satuan yang tetap huruf kecil (tidak dikapitalkan). */
    protected const LOWERCASE = [
        'cm', 'mm', 'ml', 'kg', 'cc', 'pc', 'pcs', 'inch',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $products = Product::active()
            ->where(fn ($q) => $q->whereNull('product_name_clean')->orWhere('product_name_clean', ''))
            ->orderBy('product_code')
            ->get();

        $preview = [];
        $applied = 0;

        foreach ($products as $product) {
            $clean = self::clean($product->product_name);
            if ($clean === '' || $clean === null) {
                continue;
            }

            $changed = $clean !== $product->product_name;
            $preview[] = [
                'code' => $product->product_code,
                'raw' => $product->product_name,
                'clean' => $clean,
                'changed' => $changed,
            ];

            if (!$dryRun) {
                Product::where('id', $product->id)->update([
                    'product_name_clean' => $clean,
                    'match_key' => Product::buildMatchKey($clean),
                ]);
                $applied++;
            }
        }

        foreach ($preview as $row) {
            $mark = $row['changed'] ? '   ' : '=  ';
            $this->line($mark . '[' . $row['code'] . '] ' . $row['raw']);
            $this->line('    -> ' . $row['clean']);
        }

        $this->newLine();
        $this->info(($dryRun ? 'Dry-run: ' : 'Selesai: ') . $applied . ' produk di-update dari ' . count($preview) . ' yang diproses.');

        return self::SUCCESS;
    }

    public static function clean(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        // 1. Rapikan spasi ganda.
        $s = preg_replace('/\s+/', ' ', trim($name));

        // 2. '*' -> 'x' (sebelum tokenisasi, biar '25*10m' -> '25x10m').
        $s = str_replace('*', 'x', $s);

        // 3. Tokenisasi per kata (spasi), transformasi per segmen kata.
        $tokens = explode(' ', $s);
        $out = [];
        foreach ($tokens as $tok) {
            if ($tok === '') {
                continue;
            }
            $out[] = self::transformToken($tok);
        }
        $s = implode(' ', $out);

        // 4. Prefix RAKHA untuk nama berawalan 'Kasa' (kecuali sudah RAKHA).
        if (preg_match('/^Kasa/i', $s) && !preg_match('/^RAKHA /i', $s)) {
            $s = 'RAKHA ' . $s;
        }

        // 5. Konversi 'NN L'/'NN LT' -> 'NN Liter' HANYA untuk nama non-Kasa (volume).
        if (!preg_match('/Kasa/i', $s)) {
            $s = preg_replace('/\b(\d+(?:[.,]\d+)?)\s*(?:LT|L)\b/', '$1 Liter', $s);
        }

        return $s !== '' ? $s : null;
    }

    /**
     * Transformasi satu token (bisa berisi tanda baca/angka), per segmen kata.
     */
    private static function transformToken(string $tok): string
    {
        // 'x' sebagai pengali dimensi tetap kecil.
        if ($tok === 'x') {
            return 'x';
        }

        // Token yang mengandung angka dipertahankan apa adanya ('10cm', '250ml', 'P3', '2,5', '158mm', '6,5-MUA').
        if (preg_match('/[0-9]/', $tok)) {
            return $tok;
        }

        // Token utuh yang masuk daftar pertahankan (merek/suffix divisi).
        if (in_array($tok, self::PRESERVE, true)) {
            return $tok;
        }

        // Pecah token menjadi segmen kata + pemisah (tanda baca).
        $segments = preg_split('/([^A-Za-z]+)/', $tok, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $out = '';
        foreach ($segments as $seg) {
            if (!preg_match('/^[A-Za-z]+$/', $seg)) {
                $out .= $seg; // pemisah/tanda baca dipertahankan.
                continue;
            }

            $letters = $seg;
            $lower = strtolower($letters);

            // Merek: kapital di tengah kata (EasyFoam, XRay) dipertahankan.
            if (preg_match('/[a-z][A-Z]/', $letters)) {
                $out .= $letters;
                continue;
            }

            // Suffix divisi di tengah token (mis. SL/AMI).
            if (in_array($letters, self::PRESERVE, true)) {
                $out .= $letters;
                continue;
            }

            // Satuan (cm, ml, kg, dst) tetap kecil.
            if (in_array($lower, self::LOWERCASE, true)) {
                $out .= $lower;
                continue;
            }

            // Kata semua kapital (SAFETY, BOX) -> title case.
            // Kata tunggal huruf (ukuran M/S/L) -> kapital.
            $out .= ucfirst($lower);
        }

        return $out;
    }
}