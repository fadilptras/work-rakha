<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\StockMove;
use App\Services\StockMoveService;
use App\Support\PackagingCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Data Product: kelola master produk + stok realtime.
 * Isi: tambah, edit, hapus + catat stok awal.
 * Route: warehouse.product.*
 */
class WsProductController extends WsBaseController
{

    // Data Product: gudang / sales.

    public function barangIndex(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Data Product.');
        }

        $search = trim((string) $request->get('search', ''));

        $products = Product::active()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('product_code', 'like', '%' . $search . '%')
                        ->orWhere('product_name', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name), ""), product_code))')
            ->get();

        $items = $products->map(function (Product $product) {
            $pcs = (int) ($product->pcs_per_unit ?? 0);
            $isSingle = $pcs <= 1;
            $fill = $product->fill_unit ?: 'Pcs';

            return [
                'id' => $product->id,
                'kode' => $product->product_code ?? '-',
                'nama' => $product->product_name,
                // Stok tampil dalam satuan isi; "= N kemasan" bila multi-pack.
                'satuan' => $isSingle ? ($product->unit ?: $fill) : $fill,
                'kemasan' => $product->unit,
                'stok' => (float) ($product->stock ?? 0),
                'pcs_per_unit' => $pcs,
                'fill_unit' => $fill,
                'is_single' => $isSingle,
                // Teks kemasan: "Box/ 100 Pcs".
                'kemasan_full' => PackagingCatalog::packagingPreview($product->unit, $product->pcs_per_unit, $fill),
            ];
        });

        return view('users.warehouse.manage-barang', [
            'title' => 'Data Product',
            'items' => $items,
            'search' => $search,
            'canManageStock' => $this->canManageStock(),
            'packCatalog' => PackagingCatalog::toArray(),
        ]);
    }

    public function storeBarang(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }
        if (!$this->canManageStock()) {
            return redirect()->route('warehouse.product.index')->withErrors(['msg' => 'Anda tidak memiliki hak untuk menambah produk.']);
        }

        $validated = $request->validate([
            'product_code' => 'nullable|string|max:50|unique:products,product_code',
            'product_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'pcs_per_unit' => 'nullable|integer|min:0|max:100000',
            'fill_unit' => 'nullable|string|max:50',
            'stock' => 'nullable|numeric|min:0|max:100000000',
        ]);

        $code = trim((string) ($validated['product_code'] ?? '')) ?: null;
        $name = trim((string) $validated['product_name']);
        $unit = trim((string) ($validated['unit'] ?? '')) ?: null;
        // Samakan pack/qty/fill ikut aturan katalog.
        [$unit, $pcs, $fill] = $this->applyPackRules($unit, $validated['pcs_per_unit'] ?? null, $validated['fill_unit'] ?? null);
        $stock = (float) ($validated['stock'] ?? 0);

        DB::transaction(function () use ($code, $name, $unit, $pcs, $fill, $stock) {
            // Restore produk terhapus bila kode sama, else buat baru.
            $trashed = $code
                ? Product::where('product_code', $code)->trashed()->first()
                : null;

            if ($trashed) {
                $trashed->restore([
                    'product_name' => $name,
                    'unit' => $unit,
                    'match_key' => Product::buildMatchKey($name),
                ]);
                $product = $trashed->fresh();
                $product->update(['stock' => 0, 'pcs_per_unit' => $pcs, 'fill_unit' => $fill]);
            } else {
                $product = Product::create([
                    'product_code' => $code,
                    'product_name' => $name,
                    'unit' => $unit,
                    'pcs_per_unit' => $pcs,
                    'fill_unit' => $fill,
                    'match_key' => Product::buildMatchKey($name),
                    'stock' => 0,
                    'stock_po' => 0,
                ]);
            }

            // Stok awal dicatat sebagai mutasi receipt (audit).
            if ($stock > 0) {
                $service = app(StockMoveService::class);
                $move = $service->createMove([
                    'product_id' => $product->id,
                    'qty' => $stock,
                    'type' => StockMove::TYPE_RECEIPT,
                    'reason' => 'Stok awal manual dari Data Product',
                    'ref_type' => 'master_stock',
                ], auth()->user());
                $service->validateMove($move, auth()->user());
            }
        });

        Cache::forget('barang_list_dropdown');

        return redirect()->route('warehouse.product.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function updateBarang(Request $request, Product $product)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }
        if (!$this->canManageStock()) {
            return redirect()->route('warehouse.product.index')->withErrors(['msg' => 'Anda tidak memiliki hak untuk mengubah produk.']);
        }

        $validated = $request->validate([
            'product_code' => 'nullable|string|max:50|unique:products,product_code,' . $product->id,
            'product_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'pcs_per_unit' => 'nullable|integer|min:0|max:100000',
            'fill_unit' => 'nullable|string|max:50',
        ]);

        $code = trim((string) ($validated['product_code'] ?? '')) ?: null;
        $name = trim((string) $validated['product_name']);
        $unit = trim((string) ($validated['unit'] ?? '')) ?: null;
        // Samakan pack/qty/fill ikut aturan katalog.
        [$unit, $pcs, $fill] = $this->applyPackRules($unit, $validated['pcs_per_unit'] ?? null, $validated['fill_unit'] ?? null);

        DB::transaction(function () use ($product, $code, $name, $unit, $pcs, $fill) {
            $product->update([
                'product_code' => $code,
                'product_name' => $name,
                'unit' => $unit,
                'pcs_per_unit' => $pcs,
                'fill_unit' => $fill,
                'match_key' => Product::buildMatchKey($name),
            ]);
        });

        Cache::forget('barang_list_dropdown');

        return redirect()->route('warehouse.product.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroyBarang(Product $product)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }
        if (!$this->canManageStock()) {
            abort(403, 'Unauthorized');
        }

        $product->softDelete();

        Cache::forget('barang_list_dropdown');

        return redirect()->route('warehouse.product.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Samakan pack/qty/fill ikut aturan katalog.
     * Single: pcs+fill null. Fixed/flex: isi dari katalog.
     *
     * @return array{0: ?string, 1: ?int, 2: ?string}
     */
    private function applyPackRules(?string $unit, mixed $pcsPerUnit, mixed $fillUnit): array
    {
        $unit = ($unit !== '' && $unit !== null) ? $unit : null;
        $fill = trim((string) $fillUnit);
        $pcs = ($pcsPerUnit !== null && $pcsPerUnit !== '') ? (int) $pcsPerUnit : null;

        $packUnits = $unit !== null ? PackagingCatalog::unitsFor($unit) : [];
        if (PackagingCatalog::isSingle($unit ?? '')) {
            return [$unit, null, null];
        }
        if ($fill === '' && isset($packUnits[0])) {
            $fill = $packUnits[0];
        }
        if (!empty($packUnits)) {
            $fillKey = ProductPackaging::normalize($fill);
            $matched = array_values(array_filter($packUnits, fn ($u) => ProductPackaging::normalize($u) === $fillKey));
            $fill = $matched[0] ?? $packUnits[0];
        }
        if ($fill === '') {
            $fill = 'Pcs';
        }

        return [$unit, PackagingCatalog::resolvePackQty($unit, $pcs), $fill];
    }
}
