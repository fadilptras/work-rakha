<?php

namespace App\Http\Controllers\Warehouse;

use App\Exports\ProductListExport;
use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Services\StockMoveService;
use App\Services\StockOpnameService;
use App\Support\PackagingCatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller Data Product: Master data produk dan saldo stok awal.
 */
class WsProductController extends WsBaseController
{
    /**
     * Redirect ke halaman utama warehouse tab Data Product.
     */
    public function barangIndex(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Data Product.');
        }

        $params = array_merge(['tab' => 'data-product'], $request->all());

        return redirect()->route('warehouse.index', $params);
    }

    /**
     * Export daftar produk ke format PDF.
     */
    public function exportPdf()
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Data Product.');
        }

        $pdf = Pdf::loadView('pdf.warehouse.product-list', [
            'items' => $this->exportProducts(),
            'printedBy' => auth()->user()->name ?? 'System',
        ])->setPaper('a4', 'portrait');

        return $pdf->download('List Produk.pdf');
    }

    /**
     * Export daftar produk ke format Excel.
     */
    public function exportExcel()
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Data Product.');
        }

        return Excel::download(new ProductListExport(), 'List Produk.xlsx');
    }

    /**
     * Ambil seluruh produk aktif berurutan nama/kode.
     */
    private function exportProducts()
    {
        return Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name), ""), product_code))')
            ->get();
    }

    /**
     * Tambah produk baru beserta pencatatan mutasi stok awal.
     */
    public function storeBarang(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }
        if (!$this->canManageStock()) {
            return redirect()->route('warehouse.index', ['tab' => 'data-product'])->withErrors(['msg' => 'Anda tidak memiliki hak untuk menambah produk.']);
        }

        $validated = $request->validate([
            'product_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'product_code')->where(fn ($q) => $q->where('is_deleted', 0)),
            ],
            'product_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'pcs_per_unit' => 'nullable|integer|min:0|max:100000',
            'fill_unit' => 'nullable|string|max:50',
            'stock' => 'nullable|numeric|min:0|max:100000000',
        ]);

        $code = trim((string) ($validated['product_code'] ?? '')) ?: null;
        $name = trim((string) $validated['product_name']);
        $unit = trim((string) ($validated['unit'] ?? '')) ?: null;
        [$unit, $pcs, $fill] = $this->applyPackRules($unit, $validated['pcs_per_unit'] ?? null, $validated['fill_unit'] ?? null);
        $stock = (float) ($validated['stock'] ?? 0);

        // Cek duplikasi nama produk aktif
        $activeSameName = Product::active()
            ->whereRaw('LOWER(product_name) = ?', [mb_strtolower($name)])
            ->first();
        if ($activeSameName) {
            return redirect()->route('warehouse.index', ['tab' => 'data-product'])
                ->withErrors(['product_name' => "Produk dengan nama '{$name}' sudah ada di Data Product (Kode: " . ($activeSameName->product_code ?? '-') . "). Silakan gunakan tombol edit pada produk tersebut."])
                ->withInput();
        }

        DB::transaction(function () use ($code, $name, $unit, $pcs, $fill, $stock) {
            // Restore jika produk sebelumnya pernah dihapus
            $trashed = null;
            if ($code) {
                $trashed = Product::where('product_code', $code)->trashed()->first();
            }
            if (! $trashed && $name !== '') {
                $trashed = Product::whereRaw('LOWER(product_name) = ?', [mb_strtolower($name)])->trashed()->first();
            }

            if ($trashed) {
                $trashed->restore([
                    'product_code' => $code,
                    'product_name' => $name,
                    'unit' => $unit,
                    'pcs_per_unit' => $pcs,
                    'fill_unit' => $fill,
                    'match_key' => Product::buildMatchKey($name),
                ]);
                $product = $trashed->fresh();

                // Hitung penyesuaian stok baru saat restore
                $currentStock = (float) $product->stock;
                $delta = round($stock - $currentStock, 2);
                if (abs($delta) >= 0.005) {
                    $service = app(StockMoveService::class);
                    $move = $service->createMove([
                        'product_id' => $product->id,
                        'qty' => abs($delta),
                        'type' => StockMove::TYPE_ADJUSTMENT,
                        'from_location' => $delta > 0 ? StockMove::LOC_ADJUSTMENT : StockMove::LOC_STOCK,
                        'to_location' => $delta > 0 ? StockMove::LOC_STOCK : StockMove::LOC_ADJUSTMENT,
                        'reason' => 'Stok awal saat pemulihan produk terhapus',
                        'ref_type' => 'master_stock',
                    ], auth()->user());
                    $service->validateMove($move, auth()->user(), true);
                }
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

                // Stok awal dicatat sebagai mutasi penerimaan perdana (tanpa membuat stock opname)
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
            }
        });

        Cache::forget('barang_list_dropdown');

        $targetTab = in_array($request->input('tab'), ['data-product', 'produk', 'mutasi', 'keluar', 'masuk'], true)
            ? $request->input('tab') : 'produk';

        return redirect()->route('warehouse.index', ['tab' => $targetTab])->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Perbarui data master produk dan koreksi stok awal in-place (sebelum opname).
     */
    public function updateBarang(Request $request, Product $product)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }
        if (!$this->canManageStock()) {
            return redirect()->route('warehouse.index', ['tab' => 'data-product'])->withErrors(['msg' => 'Anda tidak memiliki hak untuk mengubah produk.']);
        }

        $validated = $request->validate([
            'product_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'product_code')->ignore($product->id)->where(fn ($q) => $q->where('is_deleted', 0)),
            ],
            'product_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'pcs_per_unit' => 'nullable|integer|min:0|max:100000',
            'fill_unit' => 'nullable|string|max:50',
            'stock' => 'nullable|numeric|min:0|max:100000000',
        ]);

        $code = trim((string) ($validated['product_code'] ?? '')) ?: null;
        $name = trim((string) $validated['product_name']);
        $unit = trim((string) ($validated['unit'] ?? '')) ?: null;
        [$unit, $pcs, $fill] = $this->applyPackRules($unit, $validated['pcs_per_unit'] ?? null, $validated['fill_unit'] ?? null);

        $targetTab = in_array($request->input('tab'), ['data-product', 'produk', 'mutasi', 'keluar', 'masuk'], true)
            ? $request->input('tab') : 'produk';

        try {
            DB::transaction(function () use ($product, $code, $name, $unit, $pcs, $fill, $request) {
                $product->update([
                    'product_code' => $code,
                    'product_name' => $name,
                    'unit' => $unit,
                    'pcs_per_unit' => $pcs,
                    'fill_unit' => $fill,
                    'match_key' => Product::buildMatchKey($name),
                ]);

                // Edit stok awal hanya diizinkan jika produk BELUM pernah melewati opname resmi
                $isOpnamed = (method_exists($product, 'hasBeenOpnamed') ? $product->hasBeenOpnamed() : false)
                    || StockOpname::where('product_id', $product->id)
                        ->where('status', StockOpname::STATUS_DONE)
                        ->where(function ($q) {
                            $q->whereNull('note')
                                ->orWhere('note', 'not like', '%stok awal%')
                                ->orWhere('revision', '>', 1);
                        })
                        ->exists()
                    || StockMove::where('product_id', $product->id)
                        ->where('reason', 'like', 'Opname%')
                        ->exists();

                if (! $isOpnamed && $request->has('stock') && $request->input('stock') !== null && $request->input('stock') !== '') {
                    $newInitialStock = round((float) $request->input('stock'), 2);

                    // Ambil mutasi stok awal pendaftaran asli
                    $initialMove = StockMove::where('product_id', $product->id)
                        ->where('status', StockMove::STATUS_DONE)
                        ->where(function ($q) {
                            $q->where('ref_type', 'master_stock')
                                ->orWhere('reason', 'like', 'Stok awal%');
                        })
                        ->orderBy('created_at')
                        ->orderBy('id')
                        ->first();

                    $oldInitialStock = $initialMove ? (float) $initialMove->qty : 0.0;
                    $delta = round($newInitialStock - $oldInitialStock, 2);

                    if (abs($delta) >= 0.005) {
                        $currentRealtime = (float) $product->stock;
                        $projectedRealtime = round($currentRealtime + $delta, 2);

                        // Pagar pengaman: perubahan stok awal tidak boleh membuat sisa stok realtime minus
                        if ($projectedRealtime < 0) {
                            $minAllowed = round($oldInitialStock - $currentRealtime, 2);
                            throw new \InvalidArgumentException(
                                "Stok awal tidak boleh kurang dari {$minAllowed} " . ($product->unit ?: 'Pcs') .
                                " karena produk ini telah mengalami mutasi keluar sebanyak {$minAllowed} " . ($product->unit ?: 'Pcs') . "."
                            );
                        }

                        $userName = auth()->user()->name ?? 'User';
                        $reasonUpdate = "Stok awal manual (diperbarui dari {$oldInitialStock} menjadi {$newInitialStock} oleh {$userName})";

                        if ($initialMove) {
                            // Update in-place pada baris stok awal pendaftaran tanpa baris baru dan tanggal tetap
                            $initialMove->update([
                                'qty' => $newInitialStock,
                                'reason' => $reasonUpdate,
                            ]);
                        } elseif ($newInitialStock > 0) {
                            $service = app(StockMoveService::class);
                            $createdMove = $service->createMove([
                                'product_id' => $product->id,
                                'qty' => $newInitialStock,
                                'type' => StockMove::TYPE_RECEIPT,
                                'reason' => $reasonUpdate,
                                'ref_type' => 'master_stock',
                            ], auth()->user());
                            $service->validateMove($createdMove, auth()->user());
                        }

                        // Sinkronkan sisa stok berjalan master produk
                        $product->update([
                            'stock' => max(0, $projectedRealtime),
                        ]);

                        // Bersihkan jika sebelumnya pernah ada dummy opname stok awal
                        StockOpname::where('product_id', $product->id)
                            ->where('note', 'like', '%stok awal%')
                            ->delete();
                    }
                }
            });
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('warehouse.index', ['tab' => $targetTab])
                ->withErrors(['stock' => $e->getMessage()])
                ->withInput();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('warehouse.index', ['tab' => $targetTab])
                ->withErrors(['msg' => 'Gagal memperbarui produk: ' . $e->getMessage()])
                ->withInput();
        }

        Cache::forget('barang_list_dropdown');

        return redirect()->route('warehouse.index', ['tab' => $targetTab])->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Hapus satu produk secara soft-delete.
     */
    public function destroyBarang(Product $product)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }
        if (!$this->canManageStock()) {
            return redirect()->route('warehouse.index', ['tab' => 'data-product'])->withErrors(['msg' => 'Anda tidak memiliki hak untuk menghapus produk.']);
        }

        $product->softDelete();

        Cache::forget('barang_list_dropdown');

        return redirect()->route('warehouse.index', ['tab' => 'data-product'])->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Hapus massal produk secara soft-delete.
     */
    public function bulkDestroyBarang(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }
        if (!$this->canManageStock()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'integer|distinct|exists:products,id',
        ]);

        $deleted = 0;
        DB::transaction(function () use ($validated, &$deleted) {
            $products = Product::whereIn('id', $validated['ids'])->get();
            foreach ($products as $product) {
                if ($product->softDelete()) {
                    $deleted++;
                }
            }
        });

        Cache::forget('barang_list_dropdown');

        $targetTab = $request->input('target_tab', 'data-product');
        return redirect()->route('warehouse.index', ['tab' => $targetTab])
            ->with('success', $deleted . ' produk berhasil dihapus.');
    }

    /**
     * Normalisasi aturan kemasan (single/fixed/flex) sesuai katalog packaging.
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
