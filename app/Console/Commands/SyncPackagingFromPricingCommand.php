<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Support\PackagingCatalog;

/**
 * Sinkronkan packaging produk dari product_prices.presentation ke products.
 *
 * Presentation ("Box/ 50 Pasang") di-parse menjadi unit / pcs_per_unit / fill_unit,
 * dengan normalisasi yang sama dengan kurasi admin (single -> fill_unit null).
 * Pencocokan berbasis product_name (lowercase trim).
 */
class SyncPackagingFromPricingCommand extends Command
{
    protected $signature = 'stock:sync-packaging-from-pricing {--dry-run : Tampilkan perubahan tanpa menyimpan}';
    protected $description = 'Isi packaging products dari product_prices.presentation berbasis nama produk';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $priceRows = ProductPrice::active()
            ->select('product_name', 'presentation', 'pack_qty')
            ->get();

        $priceByName = [];
        foreach ($priceRows as $p) {
            $key = strtolower(trim((string) $p->product_name));
            if ($key === '' || isset($priceByName[$key])) {
                continue;
            }
            $priceByName[$key] = $p;
        }

        $products = Product::active()->get();
        $updated = 0;
        $skippedNoPresentation = 0;

        foreach ($products as $product) {
            $key = strtolower(trim((string) $product->product_name));
            $price = $priceByName[$key] ?? null;

            if (!$price || $price->presentation === null || trim($price->presentation) === '') {
                $skippedNoPresentation++;
                continue;
            }

            // Parse presentation -> komponen packaging.
            $parsed = PackagingCatalog::parse($price->presentation);
            $unit = $parsed['unit'] ?: null;
            $pcsPerUnit = $parsed['pcs_per_unit'];
            $fillUnit = $parsed['fill_unit'];

            $hasFill = str_contains($price->presentation, '/');

            // Normalisasi: tidak ada isi eksplisit (tanpa '/') -> satuan tunggal.
            // Single packaging / tanpa isi -> fill_unit null, pcs_per_unit null.
            if (!$hasFill || ($unit !== null && PackagingCatalog::isSingle($unit))) {
                $fillUnit = null;
                $pcsPerUnit = null;
            } else {
                // Kapitalisasi huruf pertama fill_unit utk konsistensi ("pcs" -> "Pcs").
                if ($fillUnit !== null && $fillUnit !== '') {
                    $fillUnit = ucfirst(strtolower(trim($fillUnit)));
                }
            }

            // pcs_per_unit default 1 jika tidak didefinisikan & bukan single.
            $finalPcs = (!$hasFill || PackagingCatalog::isSingle($unit ?? ''))
                ? null
                : max(1, (int) ($pcsPerUnit ?? 1));

            $data = [
                'unit' => $unit,
                'pcs_per_unit' => $finalPcs,
                'fill_unit' => $fillUnit,
            ];

            if ($dryRun) {
                $this->line("[dry] {$product->product_code} | {$product->product_name} => unit={$unit} pcs_per_unit=" . var_export($finalPcs, true) . " fill_unit=" . var_export($fillUnit, true) . " (dari '{$price->presentation}')");
            } else {
                Product::where('id', $product->id)->update($data);
            }
            $updated++;
        }

        if ($dryRun) {
            $this->info("Dry-run: {$updated} produk cocok product_prices (tanpa disimpan).");
        } else {
            $this->info("Selesai: {$updated} produk di-update, {$skippedNoPresentation} di-skip (tidak ada presentation/cocok).");
        }

        return self::SUCCESS;
    }
}