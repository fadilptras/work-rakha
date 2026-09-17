<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Product;
use App\Models\ProductPackaging;
use App\Support\PackagingCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AdminBarangController extends Controller
{
    /**
     * Halaman Data Barang — gabungan master barang (barangs) dengan
     * kurasi produk (name bersih + satuan jual di tabel products).
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $search = trim((string) $request->get('search'));

        $barangs = Barang::orderBy('product_name')->get();
        $productMap = Product::whereIn('product_code', $barangs->pluck('product_code'))
            ->get()
            ->keyBy('product_code');

        // Status kurasi: belum/sudah berdasarkan isi product_name_clean di products.
        $isPending = fn ($barang) => ($productMap[$barang->product_code] ?? null)
            && ($productMap[$barang->product_code]->product_name_clean === null);

        $filtered = $barangs->filter(function ($barang) use ($filter, $isPending) {
            if ($filter === 'pending') {
                return $isPending($barang);
            }
            if ($filter === 'done') {
                return !$isPending($barang);
            }
            return true;
        });

        if ($search !== '') {
            $filtered = $filtered->filter(function ($barang) use ($search, $productMap) {
                $clean = ($productMap[$barang->product_code] ?? null)?->product_name_clean ?? null;
                $haystack = strtolower(implode(' ', array_filter([
                    $barang->product_code,
                    $barang->product_name,
                    $clean,
                ])));

                return str_contains($haystack, strtolower($search));
            });
        }

        $total = $barangs->count();
        $pending = $barangs->filter($isPending)->count();
        $done = $total - $pending;

        $catalog = PackagingCatalog::toArray();

        return view('admin.barangs.index', compact(
            'filtered',
            'productMap',
            'total',
            'pending',
            'done',
            'filter',
            'search',
            'catalog'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_code' => 'nullable|string|max:50|unique:barangs,product_code',
            'product_name' => 'required|string|max:255|unique:barangs,product_name',
            'unit' => 'nullable|string|max:50',
            'product_name_clean' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'product_name_clean'),
            ],
            'pcs_per_unit' => 'nullable|integer|min:0|max:100000',
            'fill_unit' => 'nullable|string|max:50',
        ]);

        $barang = Barang::create($request->all());

        // Restore otomatis: kode yang sama dengan tombstone (produk soft-delete)
        // mengaktifkan kembali produk tsb supaya rekap & stok tersambung lagi.
        $restored = $this->restoreTombstone($barang->product_code, $barang->product_name, $barang->unit);

        // Kurasi produk (nama bersih + satuan jual) disimpan sekaligus di tabel products.
        $this->saveKurasi($barang, $request);

        Cache::forget('barang_list_dropdown');

        $message = $restored
            ? 'Barang berhasil ditambahkan. Produk terkait dihidupkan kembali.'
            : 'Barang berhasil ditambahkan.';

        return redirect()->route('admin.barangs.index')->with('success', $message);
    }

    public function update(Request $request, Barang $barang)
    {
        // Target kurasi adalah produk dengan kode barang (kode baru bila diubah).
        $newCode = trim((string) $request->get('product_code'));
        $targetProduct = $newCode !== '' ? Product::where('product_code', $newCode)->first() : null;

        $request->validate([
            'product_code' => 'nullable|string|max:50|unique:barangs,product_code,' . $barang->id,
            'product_name' => 'required|string|max:255|unique:barangs,product_name,' . $barang->id,
            'unit' => 'nullable|string|max:50',
            'product_name_clean' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'product_name_clean')->ignore($targetProduct?->id),
            ],
            'pcs_per_unit' => 'nullable|integer|min:0|max:100000',
            'fill_unit' => 'nullable|string|max:50',
        ]);

        $barang->update($request->all());

        // Bila kode diubah menjadi kode tombstone, hidupkan kembali produk tsb
        // supaya tidak ada tombstone dengan kode yang sama dengan barang aktif.
        $restored = $this->restoreTombstone($barang->product_code, $barang->product_name, $barang->unit);

        // Kurasi produk disimpan sekaligus.
        $this->saveKurasi($barang, $request);

        Cache::forget('barang_list_dropdown');

        $message = $restored
            ? 'Barang berhasil diperbarui. Produk terkait dihidupkan kembali.'
            : 'Barang berhasil diperbarui.';

        return redirect()->route('admin.barangs.index')->with('success', $message);
    }

    /**
     * Simpan kurasi produk (penulisan bersih + isi per kemasan)
     * pada produk yang berkode sama dengan barang. Bila produk belum ada
     * (barang baru / kode berubah), dibuatkan row baru supaya tetap 1:1.
     */
    private function saveKurasi(Barang $barang, Request $request): void
    {
        $product = Product::where('product_code', $barang->product_code)->first();

        if (!$product) {
            $product = Product::create([
                'product_code' => $barang->product_code,
                'product_name' => $barang->product_name,
                'unit' => $barang->unit,
                'match_key' => Product::buildMatchKey($barang->product_name),
                'stock' => 0,
                'stock_po' => 0,
            ]);
        }

        $clean = trim((string) $request->input('product_name_clean'));
        $fillUnit = trim((string) $request->input('fill_unit'));
        $pcsPerUnit = $request->input('pcs_per_unit');
        $unit = $barang->unit !== '' && $barang->unit !== null ? $barang->unit : null;

        // Selaraskan fill_unit dengan daftar pack (isi) milik packaging/unit.
        // Satuan tunggal (single) tidak punya isi -> fill_unit di-null-kan, display ambil dari unit.
        $packUnits = $unit !== null ? PackagingCatalog::unitsFor($unit) : [];
        if (PackagingCatalog::isSingle($unit ?? '')) {
            $fillUnit = null;
            $pcsPerUnit = null;
        } elseif ($fillUnit === '' && isset($packUnits[0])) {
            $fillUnit = $packUnits[0];
        }

        // Pastikan fill_unit termasuk dalam daftar pack packaging tsb (jika dikenal).
        if (!empty($packUnits)) {
            $fillKey = ProductPackaging::normalize($fillUnit);
            $matched = array_values(array_filter(
                $packUnits,
                fn ($u) => ProductPackaging::normalize($u) === $fillKey
            ));
            $fillUnit = $matched[0] ?? $packUnits[0];
        }

        // Isi per kemasan (pack qty) berdasar satuan master (unit) + isi yang diinput.
        $packQty = PackagingCatalog::resolvePackQty($unit, (int) ($pcsPerUnit ?? 0));

        $data = [
            'product_name' => $barang->product_name,
            'unit' => $unit,
            'product_name_clean' => $clean !== '' ? $clean : null,
            'pcs_per_unit' => $packQty,
            'fill_unit' => $fillUnit !== '' ? $fillUnit : 'Pcs',
        ];

        // match_key ikut ragam yang dipakai (nama bersih bila ada, fallback nama kotor).
        $data['match_key'] = Product::buildMatchKey(
            $data['product_name_clean'] ?: $barang->product_name
        );

        $product->update($data);
    }

    public function destroy(Barang $barang)
    {
        // Soft delete di tabel products supaya rekap tetap utuh (is_deleted = 1).
        Product::where('product_code', $barang->product_code)->first()?->softDelete();

        $barang->delete();
        Cache::forget('barang_list_dropdown');

        return redirect()->route('admin.barangs.index')->with('success', 'Barang berhasil dihapus.');
    }

    /**
     * JSON endpoints Aturan Packaging (master product_packaging).
     * Otorisasi ditangani middleware admin; tanpa guard sales ala StockController.
     */
    public function packagings()
    {
        $rows = ProductPackaging::orderBy('id')->get();
        return response()->json([
            'data' => $rows->map(fn ($r) => [
                'id' => $r->id,
                'packaging' => $r->packaging,
                'type' => ProductPackaging::typeFromPack($r->pack),
                'pack' => $r->pack,
            ]),
        ]);
    }

    public function storePackaging(Request $request)
    {
        $data = $this->validatePackagingData($request);
        $row = ProductPackaging::create([
            'packaging' => $data['packaging'],
            'pack' => $data['pack'],
            'type' => ProductPackaging::typeFromPack($data['pack']),
        ]);
        PackagingCatalog::flush();
        return response()->json([
            'message' => 'Aturan packaging "' . $row->packaging . '" ditambahkan.',
            'data' => [
                'id' => $row->id,
                'packaging' => $row->packaging,
                'type' => ProductPackaging::typeFromPack($row->pack),
                'pack' => $row->pack,
            ],
        ], 201);
    }

    public function updatePackaging(Request $request, ProductPackaging $packaging)
    {
        $data = $this->validatePackagingData($request, $packaging);
        $packaging->update([
            'packaging' => $data['packaging'],
            'pack' => $data['pack'],
            'type' => ProductPackaging::typeFromPack($data['pack']),
        ]);
        PackagingCatalog::flush();
        return response()->json([
            'message' => 'Aturan packaging "' . $packaging->packaging . '" diperbarui.',
            'data' => [
                'id' => $packaging->id,
                'packaging' => $packaging->packaging,
                'type' => ProductPackaging::typeFromPack($packaging->pack),
                'pack' => $packaging->pack,
            ],
        ]);
    }

    public function destroyPackaging(ProductPackaging $packaging)
    {
        $name = $packaging->packaging;
        $packaging->delete();
        PackagingCatalog::flush();
        return response()->json(['message' => 'Aturan packaging "' . $name . '" dihapus.']);
    }

    private function validatePackagingData(Request $request, ?ProductPackaging $ignore = null): array
    {
        $validated = $request->validate([
            'packaging' => ['required', 'string', 'max:100', Rule::unique('product_packaging', 'packaging')->ignore($ignore?->id)],
            'pack' => ['present', 'array'],
            'pack.*' => ['required', 'string', 'max:50'],
        ]);
        return [
            'packaging' => trim($validated['packaging']),
            'pack' => array_values(array_filter(array_map(fn ($u) => trim((string) $u), $validated['pack'] ?? []), fn ($u) => $u !== '')),
        ];
    }

    /**
     * Aktifkan kembali tombstone (produk dengan is_deleted = 1) yang kodenya
     * cocok, lalu sinkronkan data dasarnya dengan barangs yang baru.
     *
     * @return bool false jika tidak ada tombstone yang cocok.
     */
    private function restoreTombstone(?string $productCode, ?string $productName, ?string $unit): bool
    {
        $tombstone = Product::where('product_code', $productCode)->trashed()->first();

        if (! $tombstone) {
            return false;
        }

        $tombstone->restore([
            'product_name' => $productName,
            'unit' => $unit,
            'match_key' => Product::buildMatchKey($productName),
        ]);

        return true;
    }
}