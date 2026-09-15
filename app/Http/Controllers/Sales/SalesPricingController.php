<?php

namespace App\Http\Controllers\Sales;

use App\Models\Barang;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use App\Exports\PricelistExport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class SalesPricingController extends BaseSalesController
{
    public function pricing(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Product Price & SPH.');
        }

        $title = 'Product Price & SPH Form';

        // Katalog jual dibaca langsung dari product_prices (aktif saja).
        $products = ProductPrice::active()
            ->orderBy('product_name', 'asc')
            ->get()
            ->map(fn ($item) => $this->serializeProduct($item));

        // Dipakai di view untuk menampilkan/menyembunyikan tab "SPH Form"
        // -> hanya hasFullSalesAccess yang boleh membuat/edit SPH.
        $hasFullAccess = $this->hasFullSalesAccess();

        // Daftar nama produk untuk rekomendasi (datalist) form Tambah Barang di halaman pricing.
        // Nama diambil dari kolom kurasi products (product_name_clean) yang BELUM
        // terdaftar di product_prices aktif, supaya tidak bentrok dengan katalog jual.
        $catalogNames = ProductPrice::active()
            ->whereNotNull('product_name')
            ->pluck('product_name');

        $nameSuggestions = Product::active()
            ->whereNotNull('product_name_clean')
            ->where('product_name_clean', '!=', '')
            ->whereNotIn('product_name_clean', $catalogNames)
            ->distinct()
            ->orderBy('product_name_clean')
            ->pluck('product_name_clean')
            ->unique()
            ->values();

        return view('users.sales.pricing', compact('title', 'products', 'hasFullAccess', 'nameSuggestions'));
    }

    /**
     * Return daftar barang jual sebagai JSON (untuk refresh setelah manage data).
     */
    public function listBarang()
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403);
        }

        $products = ProductPrice::active()
            ->orderBy('product_name', 'asc')
            ->get()
            ->map(fn ($item) => $this->serializeProduct($item));

        return response()->json($products);
    }

    /**
     * Tambah produk ke katalog jual. Data disimpan di product_prices.
     * Nama duplikat katalog -> ditolak. Khusus hasFullSalesAccess.
     */
    public function storeBarang(Request $request)
    {
        if (!$this->hasFullSalesAccess()) {
            abort(403, 'Hanya pemegang akses penuh yang dapat menambah barang.');
        }

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'presentation' => 'nullable|string|max:100',
            'pack_qty' => 'nullable|integer|min:0',
            'base_price' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $packQty = (int) ($validated['pack_qty'] ?? 0);
        if ($packQty < 1) {
            $packQty = Barang::packCount($validated['presentation'] ?? null);
        }

        $data = [
            'product_name' => $validated['product_name'],
            'presentation' => $validated['presentation'] ?? null,
            'pack_qty' => max(1, $packQty),
            'base_price' => $validated['base_price'],
            'unit_price' => $validated['unit_price'],
        ];

        // Tombstone (baris soft-delete) dengan nama sama -> hidupkan kembali
        // supaya rekap & master tidak menumpuk dua baris nama yang sama.
        $tombstone = ProductPrice::where('product_name', $validated['product_name'])
            ->trashed()
            ->first();

        if ($tombstone) {
            $tombstone->restore($data);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil ditambahkan. Baris lama dihidupkan kembali.',
                'data' => $this->serializeProduct($tombstone->fresh()),
            ]);
        }

        $exists = ProductPrice::where('product_name', $validated['product_name'])
            ->active()
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Nama barang sudah terdaftar di daftar pricing.',
                'errors' => ['product_name' => ['Nama barang sudah terdaftar di daftar pricing.']],
            ], 422);
        }

        $price = ProductPrice::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan ke daftar pricing.',
            'data' => $this->serializeProduct($price),
        ]);
    }

    /**
     * Update data / harga produk di product_prices. Khusus hasFullSalesAccess.
     */
    public function updateBarang(Request $request, ProductPrice $barang)
    {
        if (!$this->hasFullSalesAccess()) {
            abort(403, 'Hanya pemegang akses penuh yang dapat mengubah data barang.');
        }

        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:255', Rule::unique('product_prices', 'product_name')->ignore($barang->id)],
            'presentation' => 'nullable|string|max:100',
            'pack_qty' => 'nullable|integer|min:0',
            'base_price' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
        ]);

        // Edit form tidak mengirim pack_qty eksplisit -> hitung dari kolom sediaan (presentation).
        $packQty = (int) ($validated['pack_qty'] ?? 0);
        if ($packQty < 1) {
            $packQty = Barang::packCount($validated['presentation'] ?? $barang->presentation);
        }

        $barang->update([
            'product_name' => $validated['product_name'],
            'presentation' => $validated['presentation'] ?? $barang->presentation,
            'pack_qty' => max(1, $packQty),
            'base_price' => $validated['base_price'],
            'unit_price' => $validated['unit_price'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diperbarui.',
            'data' => $this->serializeProduct($barang->fresh()),
        ]);
    }

    /**
     * Hapus produk dari daftar pricing (product_prices). Khusus hasFullSalesAccess.
     * Pakai soft delete supaya rekap tetap utuh; tambah ulang dengan nama sama
     * akan menghidupkan kembali barisnya.
     */
    public function destroyBarang(ProductPrice $barang)
    {
        if (!$this->hasFullSalesAccess()) {
            abort(403, 'Hanya pemegang akses penuh yang dapat menghapus barang.');
        }

        $barang->softDelete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus dari daftar pricing.',
        ]);
    }

    /**
     * Export Daftar Harga sebagai PDF (DomPDF).
     */
    public function exportPdf()
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Product Price & SPH.');
        }

        $products = $this->soldProducts();

        $pdf = Pdf::loadView('pdf.sales.pricelist-document', compact('products'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Daftar_Harga_PT_Rakha_Nusantara_Medika.pdf');
    }

    /**
     * Export Daftar Harga sebagai Excel (Maatwebsite/Laravel-Excel).
     */
    public function exportExcel()
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Product Price & SPH.');
        }

        $products = $this->soldProducts();

        return Excel::download(
            new PricelistExport($products),
            'Daftar_Harga_PT_Rakha_Nusantara_Medika.xlsx'
        );
    }

    /**
     * Daftar produk yang dijual (data dari product_prices), urut alfabetis nama.
     * Koreksi unit_price on-the-fly agar Excel/PDF tidak ngaco bila DB lama selisih.
     */
    private function soldProducts()
    {
        return ProductPrice::active()
            ->orderBy('product_name', 'asc')
            ->get()
            ->each(function ($item) {
                $pack = max(1, (int) ($item->pack_qty ?: Barang::packCount($item->presentation)));
                $base = (float) ($item->base_price ?? 0);
                $item->pack_qty = $pack;
                $item->unit_price = $pack > 0 ? (int) round($base / $pack) : (int) round($base);
                // presentation fallback biar tidak kosong di export
                if (empty($item->presentation)) {
                    $item->presentation = 'General';
                }
            });
    }

    /**
     * Normalisasi payload produk untuk frontend (konsisten di semua endpoint pricing).
     * HNA / PCS selalu dihitung ulang dari HNA / pack_qty agar export & display tidak ngaco
     * meski data lama unit_price di DB sempat tidak sinkron (selisih 19-500 ditemukan di 6 baris).
     */
    private function serializeProduct(ProductPrice $item): array
    {
        $pack = max(1, (int) ($item->pack_qty ?: Barang::packCount($item->presentation)));
        $base = (float) ($item->base_price ?? 0);
        // HNA/PCS = HNA / isi, bulat ke rupiah terdekat (konsisten dengan form manage)
        $unit = $pack > 0 ? (int) round($base / $pack) : (int) round($base);

        return [
            'id' => $item->id,
            'product_name' => $item->product_name,
            'raw_product_name' => $item->product_name,
            'presentation' => $item->presentation ?: 'General',
            'pack_qty' => $pack,
            'base_price' => $base,
            'unit_price' => $unit,
            'price_id' => $item->id,
        ];
    }
}