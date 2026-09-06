<?php

namespace App\Http\Controllers\Sales;

use App\Models\Barang;
use App\Models\ProductPrice;
use App\Models\ProductClean;
use App\Exports\PricelistExport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class SalesPricingController extends BaseSalesController
{
    public function pricing(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Product Price & SPH.');
        }

        $title = 'Product Price & SPH Quotation';

        // Hanya produk yang memiliki harga (record di product_prices) yang masuk katalog jual.
        // Nama tampilan memakai clean_name dari products_clean (jika sudah diisi manual), fallback ke product_name.
        $products = Barang::with(['price', 'clean'])
            ->whereHas('price')
            ->orderBy('product_name', 'asc')
            ->get()
            ->map(fn ($item) => $this->serializeProduct($item));

        // Dipakai di view untuk menampilkan/menyembunyikan tab "SPH Form"
        // -> hanya hasFullSalesAccess yang boleh membuat/edit SPH.
        $hasFullAccess = $this->hasFullSalesAccess();

        // Daftar nama produk untuk rekomendasi (datalist) form Tambah Barang di halaman pricing.
        // Hanya nama barang yang BELUM punya harga di product_prices (belum masuk katalog jual)
        // yang ditampilkan, supaya saat dipilih tidak bentrok dengan unique product_name di barangs.
        // Barang yang sudah berharga bisa diedit lewat tombol Edit di tabel Price List.
        $nameSuggestions = Barang::whereNotNull('product_name')
            ->where('product_name', '!=', '')
            ->whereDoesntHave('price')
            ->distinct()
            ->orderBy('product_name')
            ->pluck('product_name')
            ->concat(
                ProductClean::whereNotNull('clean_name')
                    ->where('clean_name', '!=', '')
                    ->whereDoesntHave('barang', fn ($q) => $q->whereHas('price'))
                    ->distinct()
                    ->orderBy('clean_name')
                    ->pluck('clean_name')
            )
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

        $products = Barang::with(['price', 'clean'])
            ->whereHas('price')
            ->orderBy('product_name', 'asc')
            ->get()
            ->map(fn ($item) => $this->serializeProduct($item));

        return response()->json($products);
    }

    /**
     * Tambah barang ke katalog jual (harga).
     * - Nama yang sudah ada di barangs tapi belum punya harga -> harga di-attach ke record existing.
     * - Nama baru -> buat record barangs + product_prices.
     * - Nama yang sudah punya harga -> ditolak (duplikat katalog).
     * Khusus hasFullSalesAccess.
     */
    public function storeBarang(Request $request)
    {
        if (!$this->hasFullSalesAccess()) {
            abort(403, 'Hanya pemegang akses penuh yang dapat menambah barang.');
        }

        $validated = $request->validate([
            'product_code' => 'nullable|string|max:50|unique:barangs,product_code',
            'product_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'presentation' => 'nullable|string|max:100',
            'pack_qty' => 'nullable|integer|min:0',
            'base_price' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'stock_po' => 'nullable|integer|min:0',
        ]);

        // Jika nama sudah dipakai oleh barang yang SUDAH masuk katalog (punya harga) -> tolak.
        // Barang yang sudah ada di tabel barangs tapi belum punya harga justru diperbolehkan;
        // harga akan di-attach ke record existing, bukan membuat duplikat barangs baru.
        if (Barang::where('product_name', $validated['product_name'])->whereHas('price')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nama barang "' . $validated['product_name'] . '" sudah ada di daftar pricing. Gunakan tombol Edit untuk mengubah harganya.',
            ], 422);
        }

        $productCode = trim($validated['product_code'] ?? '') !== '' ? trim($validated['product_code']) : null;

        $barang = DB::transaction(function () use ($validated, $productCode) {
            // Barang existing tanpa harga -> lampirkan harga jual + data katalog ke product_prices.
            $existing = Barang::where('product_name', $validated['product_name'])
                ->whereDoesntHave('price')
                ->first();

            if ($existing) {
                $unit = (!empty($validated['unit']) && strtolower(trim($validated['unit'])) !== 'general')
                    ? $validated['unit'] : $existing->unit;

                $presentation = (!empty($validated['presentation']) && strtolower(trim($validated['presentation'])) !== 'general')
                    ? $validated['presentation'] : $existing->presentation;

                $packQty = (int) ($validated['pack_qty'] ?? 0);
                if ($packQty < 1) {
                    $packQty = Barang::packCount($presentation);
                }

                $finalCode = $existing->product_code ?: $productCode;

                $existing->update([
                    'product_code' => $finalCode,
                    'unit' => $unit,
                    'presentation' => $presentation,
                ]);

                ProductPrice::updateOrCreate(
                    ['barang_id' => $existing->id],
                    [
                        'product_code' => $finalCode,
                        'product_name' => $existing->product_name,
                        'unit' => $unit,
                        'presentation' => $presentation,
                        'pack_qty' => max(1, $packQty),
                        'base_price' => $validated['base_price'],
                        'unit_price' => $validated['unit_price'],
                    ]
                );

                return $existing;
            }

            // Barang benar-benar baru.
            $packQty = (int) ($validated['pack_qty'] ?? 0);
            if ($packQty < 1) {
                $packQty = Barang::packCount($validated['presentation'] ?? null);
            }

            $barang = Barang::create([
                'product_code' => $productCode,
                'product_name' => $validated['product_name'],
                'unit' => $validated['unit'] ?? null,
                'presentation' => $validated['presentation'] ?? null,
                'stock' => $validated['stock'] ?? 0,
                'stock_po' => $validated['stock_po'] ?? 0,
            ]);

            ProductPrice::create([
                'barang_id' => $barang->id,
                'product_code' => $productCode,
                'product_name' => $validated['product_name'],
                'unit' => $validated['unit'] ?? null,
                'presentation' => $validated['presentation'] ?? null,
                'pack_qty' => max(1, $packQty),
                'base_price' => $validated['base_price'],
                'unit_price' => $validated['unit_price'],
            ]);

            return $barang;
        });

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan ke daftar pricing.',
            'data' => $this->serializeProduct($barang->load(['price', 'clean'])),
        ]);
    }

    /**
     * Update data / harga Barang. Khusus hasFullSalesAccess.
     */
    public function updateBarang(Request $request, Barang $barang)
    {
        if (!$this->hasFullSalesAccess()) {
            abort(403, 'Hanya pemegang akses penuh yang dapat mengubah data barang.');
        }

        $validated = $request->validate([
            'product_code' => ['nullable', 'string', 'max:50', Rule::unique('barangs', 'product_code')->ignore($barang->id)],
            'product_name' => ['required', 'string', 'max:255', Rule::unique('barangs', 'product_name')->ignore($barang->id)],
            'unit' => 'nullable|string|max:50',
            'presentation' => 'nullable|string|max:100',
            'pack_qty' => 'nullable|integer|min:0',
            'base_price' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'stock_po' => 'nullable|integer|min:0',
        ]);

        // Edit form tidak mengirim pack_qty eksplisit -> hitung dari kolom sediaan (presentation).
        $packQty = (int) ($validated['pack_qty'] ?? 0);
        if ($packQty < 1) {
            $packQty = Barang::packCount($validated['presentation'] ?? $barang->presentation);
        }

        DB::transaction(function () use ($barang, $validated, $packQty) {
            $barang->update([
                'product_code' => $validated['product_code'] ?? null,
                'product_name' => $validated['product_name'],
                'unit' => $validated['unit'] ?? null,
                'presentation' => $validated['presentation'] ?? null,
                'stock' => $validated['stock'] ?? $barang->stock,
                'stock_po' => $validated['stock_po'] ?? $barang->stock_po,
            ]);

            ProductPrice::updateOrCreate(
                ['barang_id' => $barang->id],
                [
                    'product_code' => $barang->product_code,
                    'product_name' => $barang->product_name,
                    'unit' => $barang->unit,
                    'presentation' => $barang->presentation,
                    'pack_qty' => max(1, $packQty),
                    'base_price' => $validated['base_price'],
                    'unit_price' => $validated['unit_price'],
                ]
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diperbarui.',
            'data' => $this->serializeProduct($barang->load('price')),
        ]);
    }

    /**
     * Hapus Barang (harga di product_prices ikut terhapus via cascade). Khusus hasFullSalesAccess.
     */
    public function destroyBarang(Barang $barang)
    {
        if (!$this->hasFullSalesAccess()) {
            abort(403, 'Hanya pemegang akses penuh yang dapat menghapus barang.');
        }

        $barang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus.',
        ]);
    }

    /**
     * Export Daftar Harga sebagai PDF (DomPDF).
     */
    public function exportPdf()
    {
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
        $products = $this->soldProducts();

        return Excel::download(
            new PricelistExport($products),
            'Daftar_Harga_PT_Rakha_Nusantara_Medika.xlsx'
        );
    }

    /**
     * Daftar produk yang dijual (punya harga di product_prices), urut alfabetis nama.
     */
    private function soldProducts()
    {
        return Barang::with(['price', 'clean'])
            ->whereHas('price')
            ->orderBy('product_name', 'asc')
            ->get();
    }

    /**
     * Normalisasi payload produk untuk frontend (konsisten di semua endpoint pricing).
     * Nama tampilan memakai clean_name jika sudah diisi (products_clean), fallback ke product_name.
     */
    private function serializeProduct(Barang $item): array
    {
        $price = $item->price;

        $productName = $item->clean?->clean_name
            ?: ($price?->product_name ?: $item->product_name);

        $presentation = $price?->presentation
            ?: ($item->presentation ?? ($item->unit ?? 'General'));

        $unit = $price?->unit ?: ($item->unit ?? 'Pcs');

        return [
            'id' => $item->id,
            'product_code' => $price?->product_code ?: ($item->product_code ?? '-'),
            'product_name' => $productName,
            'raw_product_name' => $item->product_name,
            'presentation' => $presentation,
            'pack_qty' => (int) ($price?->pack_qty ?: Barang::packCount($item->presentation)),
            'unit' => $unit,
            'stock' => $item->stock,
            'stock_po' => $item->stock_po,
            'base_price' => (float) ($price?->base_price ?? 0),
            'unit_price' => (float) ($price?->unit_price ?? 0),
            'price_id' => $price?->id,
        ];
    }
}