<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Product;
use App\Models\ProductPackaging;
use App\Models\StockLog;
use App\Models\StockLogDetail;
use App\Models\DailyStockHistory;
use App\Imports\StockImport;
use App\Exports\StockExport;
use App\Exports\BarangMasterExport;
use App\Support\PackagingCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    // Akses untuk direktur, kepala divisi marketing, admin support, dan test
    private function hasFullSalesAccess()
    {
        $user = Auth::user();
        if (!$user) return false;

        $jabatan = strtolower($user->jabatan ?? '');
        $divisi = strtolower($user->divisi ?? '');

        $isTopManagement = \Illuminate\Support\Str::contains($jabatan, 'direktur') || $divisi === 'top management';
        $isKepalaDivisiMO = (($user->is_kepala_divisi == 1) || \Illuminate\Support\Str::contains($jabatan, 'kepala')) && in_array($divisi, ['marketing dan operasional']);
        $isAdminMarketing = \Illuminate\Support\Str::contains($jabatan, 'admin support');
        // $isTest = \Illuminate\Support\Str::contains($jabatan, 'test');

        return $isTopManagement || $isKepalaDivisiMO || $isAdminMarketing;
    }

    // Akses div marketing & operasional & admin gudang
    private function hasAnySalesAccess()
    {
        if ($this->hasFullSalesAccess()) return true;

        $user = Auth::user();
        if (!$user) return false;

        $divisi = strtolower($user->divisi ?? '');
        $jabatan = strtolower($user->jabatan ?? '');

        $isAdminGudang = \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang';
        $isLegalPurchasing = \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing') || \Illuminate\Support\Str::contains($jabatan, 'purchasing');

        return in_array($divisi, ['marketing dan operasional', 'finance dan gudang', 'fianance dan gudang']) || $isAdminGudang || $isLegalPurchasing;
    }

    public function index(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Monitoring Stock.');
        }

        $items = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get();
        $logs = StockLog::with('user')->orderBy('created_at', 'desc')->take(4)->get();
        $canManageStock = $this->canManageStock();

        return view('users.stock.stock', compact('items', 'logs', 'canManageStock'));
    }

    public function historyIndex(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Monitoring Stock.');
        }

        $query = StockLog::with('user');

        // Filter berdasarkan kata kunci (nama editor / nama file)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_file', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(15);

        $canManageStock = $this->canManageStock();

        return view('users.stock.history', compact('logs', 'canManageStock'));
    }

    public function updateBulk(Request $request)
    {
        if (!$this->canManageStock()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $updates = $request->input('updates', []);
        $user = Auth::user();

        if (empty($updates)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data untuk diupdate']);
        }

        try {
            DB::transaction(function () use ($updates, $user) {
                // Buat stock log
                $log = StockLog::create([
                    'user_id' => $user ? $user->id : null,
                    'source' => 'manual',
                    'items_count' => count($updates),
                    'status' => 'success',
                ]);

                $detailItems = [];

                foreach ($updates as $update) {
                    $product = Product::active()->find($update['id']);
                    if ($product) {
                        $oldStok = $product->stock;
                        $oldStokPo = $product->stock_po;
                        $newStok = intval($update['stock']);
                        $newStokPo = isset($update['stock_po']) ? intval($update['stock_po']) : $oldStokPo;

                        $detailItems[] = [
                            'product_id' => $product->id,
                            'old_stok' => $oldStok,
                            'new_stok' => $newStok,
                            'old_stok_po' => $oldStokPo,
                            'new_stok_po' => $newStokPo,
                        ];

                        // Update Produk
                        $product->update([
                            'stock' => $newStok,
                            'stock_po' => $newStokPo,
                        ]);

                        // Update atau create daily stock history untuk hari ini
                        DailyStockHistory::updateOrCreate(
                            ['product_id' => $product->id, 'tanggal' => date('Y-m-d')],
                            ['stok' => $newStok, 'stok_po' => $newStokPo]
                        );
                    }
                }

                // Rincian mutasi disimpan 1 baris per log sebagai JSON array.
                StockLogDetail::create([
                    'stock_log_id' => $log->id,
                    'items' => $detailItems,
                ]);
            });

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Memproses / mengurai file Excel Accurate untuk ditampilkan di pratinjau.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function parseImport(Request $request)
    {
        if (!$this->canManageStock()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new StockImport;
            Excel::import($import, $request->file('file'));

            $extracted = $import->extractedData;
            $processed = [];

            foreach ($extracted as $data) {
                // Cari kecocokan data produk berdasarkan kode produk unik
                $product = Product::active()->where('product_code', $data['kode'])->first();

                // Hanya tampilkan di pratinjau jika produk sudah terdaftar di database
                if ($product) {
                    // Normalisasi satuan agar cocok case-insensitively dengan dropdown select di frontend.
                    // Produk yang belum dikurasi (unit kosong) dikirim sebagai '' supaya tidak menimpa
                    // kolom unit dengan default 'pcs' saat import disimpan.
                    $dbSatuan = trim((string) $product->unit);
                    $matchedSatuan = '';
                    $allowedOptions = ['pcs', 'box', 'botol', 'galon', 'Jerigen', 'karton', 'pack', 'paket', 'polybag', 'pouches', 'roll'];
                    foreach ($allowedOptions as $opt) {
                        if (strtolower($dbSatuan) === strtolower($opt)) {
                            $matchedSatuan = $opt;
                            break;
                        }
                    }

                    $processed[] = [
                        'id' => $product->id,
                        'kode' => $data['kode'],
                        'nama' => $product->product_name,
                        'satuan' => $matchedSatuan,
                        'stok_db' => $product->stock,
                        'stok_excel' => $data['stok'],
                        'po' => $product->stock_po
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'tercetak_date' => $import->tercetakDate,
                'original_filename' => $request->file('file')->getClientOriginalName(),
                'items' => $processed
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Menyimpan data stok yang telah disetujui dari modal pratinjau ke database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveImport(Request $request)
    {
        if (!$this->canManageStock()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'items' => 'required|array',
            'original_filename' => 'nullable|string',
            'tercetak_date' => 'nullable|string',
        ]);

        $user = Auth::user();
        $items = $request->input('items');
        $filename = $request->input('original_filename') ?: ($request->input('tercetak_date') ?: 'Import Accurate ' . now()->format('Y-m-d H:i'));

        try {
            DB::transaction(function () use ($items, $filename, $user) {
                // Buat log aktivitas unggah stok
                $log = StockLog::create([
                    'user_id' => $user ? $user->id : null,
                    'nama_file' => $filename,
                    'source' => 'excel',
                    'items_count' => count($items),
                    'status' => 'success',
                ]);

                $detailItems = [];

                foreach ($items as $item) {
                    // Cari produk: utamakan id hasil pratinjau, lalu kode
                    $product = null;
                    if (!empty($item['id'])) {
                        $product = Product::active()->find($item['id']);
                    }
                    if (!$product && !empty($item['kode'])) {
                        $product = Product::active()->where('product_code', $item['kode'])->first();
                    }

                    if ($product) {
                        // Isi kode produk jika di DB masih kosong tetapi di Excel terdefinisi
                        if (empty($product->product_code) && !empty($item['kode'])) {
                            $product->product_code = $item['kode'];
                        }

                        $oldStok = $product->stock;
                        $oldStokPo = $product->stock_po;
                        $newStok = intval($item['stok_excel']);

                        // Jangan perbarui satuan/unit dari import: kolom packaging (unit, fill_unit,
                        // pcs_per_unit) dikurasi via admin & sync pricing, bukan dari stok Excel.

                        $detailItems[] = [
                            'product_id' => $product->id,
                            'old_stok' => $oldStok,
                            'new_stok' => $newStok,
                            'old_stok_po' => $oldStokPo,
                            'new_stok_po' => $oldStokPo,
                        ];

                        // Perbarui jumlah stok di database produk
                        $product->update([
                            'stock' => $newStok,
                        ]);

                        // Perbarui atau buat riwayat stok harian untuk hari ini
                        DailyStockHistory::updateOrCreate(
                            ['product_id' => $product->id, 'tanggal' => date('Y-m-d')],
                            ['stok' => $newStok, 'stok_po' => $oldStokPo]
                        );
                    }
                }

                // Rincian mutasi disimpan 1 baris per log sebagai JSON array.
                StockLogDetail::create([
                    'stock_log_id' => $log->id,
                    'items' => $detailItems,
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Stok berhasil diperbarui!']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function addBarang(Request $request)
    {
        if (!$this->canManageStock()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Normalisasi kode barang terlebih dahulu agar validasi unique konsisten
        $request->merge([
            'product_code' => preg_replace('/\s+/', ' ', trim($request->input('product_code'))),
            'product_name' => trim($request->input('product_name')),
        ]);

        $request->validate([
            'product_name' => 'required|string|max:255',
            'product_code' => [
                'required', 'string', 'max:255',
                Rule::unique('products', 'product_code')->where('is_deleted', 0),
            ],
            'unit' => 'required|string|max:255',
        ]);

        try {
            $tombstone = Product::where('product_code', $request->input('product_code'))->trashed()->first();

            if ($tombstone) {
                // Kode sama dengan produk soft-delete: hidupkan kembali tombstone tsb.
                $tombstone->restore([
                    'product_name' => $request->input('product_name'),
                    'unit' => $request->input('unit'),
                    'match_key' => Product::buildMatchKey($request->input('product_name')),
                ]);
                $product = $tombstone;
            } else {
                $product = Product::create([
                    'product_name' => $request->input('product_name'),
                    'product_code' => $request->input('product_code'),
                    'unit' => $request->input('unit'),
                    'match_key' => Product::buildMatchKey($request->input('product_name')),
                    'stock' => 0,
                    'stock_po' => 0,
                ]);
            }

            // Buat history awal hari ini
            DailyStockHistory::create([
                'product_id' => $product->id,
                'tanggal' => date('Y-m-d'),
                'stok' => 0,
                'stok_po' => 0,
            ]);

            return response()->json(['success' => true, 'message' => 'Barang ' . $product->product_name . ' berhasil ditambahkan!']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function exportExcel()
    {
        if (!$this->canManageStock()) {
            abort(403, 'Unauthorized');
        }

        return Excel::download(new StockExport, 'monitoring_stok_' . date('Ymd_His') . '.xlsx');
    }

    public function downloadTemplate()
    {
        if (!$this->canManageStock()) {
            abort(403, 'Unauthorized');
        }

        // Just create a fast download of a template
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_update_stok.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['product_code', 'product_name', 'stock', 'stock_po']);
            // Put some sample active products
            $samples = Product::active()->take(5)->get();
            foreach ($samples as $sample) {
                fputcsv($file, [$sample->product_code, $sample->product_name, $sample->stock, $sample->stock_po]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function undo(Request $request, StockLog $log)
    {
        if (!$this->canManageStock()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($log->status === 'undone') {
            return response()->json(['success' => false, 'message' => 'Log sudah pernah dibatalkan sebelumnya']);
        }

        try {
            DB::transaction(function () use ($log) {
                foreach (($log->details?->items ?? []) as $item) {
                    $product = Product::active()->find($item['product_id']);
                    if ($product) {
                        // Kembalikan ke stok lama
                        $product->update([
                            'stock' => $item['old_stok'],
                            'stock_po' => $item['old_stok_po'],
                        ]);

                        // Update or create daily stock history untuk hari ini
                        DailyStockHistory::updateOrCreate(
                            ['product_id' => $product->id, 'tanggal' => date('Y-m-d')],
                            ['stok' => $item['old_stok'], 'stok_po' => $item['old_stok_po']]
                        );
                    }
                }

                // Ubah status log
                $log->update(['status' => 'undone']);
            });

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function logDetails(StockLog $log)
    {
        if (!$this->hasAnySalesAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $items = $log->details?->items ?? [];

        // Muat produk sekaligus (sekali query) untuk nama & kode.
        $productIds = array_values(array_unique(array_filter(
            array_column($items, 'product_id')
        )));
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $details = array_map(function ($item) use ($products) {
            $product = $products[$item['product_id']] ?? null;

            return [
                'id' => $item['product_id'],
                'nama' => $product->product_name ?? 'Produk Terhapus',
                'kode' => $product->product_code ?? '-',
                'old_stok' => $item['old_stok'],
                'new_stok' => $item['new_stok'],
                'old_stok_po' => $item['old_stok_po'],
                'new_stok_po' => $item['new_stok_po'],
            ];
        }, $items);

        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'tanggal' => $log->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
                'editor' => $log->user->name ?? 'System',
                'sumber' => $log->source === 'excel' ? 'Excel' : 'Manual',
                'nama_file' => $log->nama_file ?? '-',
                'status' => $log->status,
                'jumlah_barang' => $log->items_count,
            ],
            'details' => $details
        ]);
    }

    public function canManageStock(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $jabatan = strtolower($user->jabatan ?? '');

        $isTest = \Illuminate\Support\Str::contains($jabatan, 'test');
        $isAdminGudang = \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan == 'gudang';
        $isLegalPurchasing = \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing');

        return $isAdminGudang || $isLegalPurchasing
        || $isTest
        ;
    }

    public function dashboard()
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke Dashboard Gudang.');
        }

        return view('users.stock.dashboard', [
            'title' => 'Dashboard Gudang'
        ]);
    }

    // ==============================================================
    // Master Barang (Product Master) — untuk user gudang / sales
    // Route: sales.stock.barang.*
    // ==============================================================

    public function barangIndex(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Master Barang.');
        }

        $filter = $request->get('filter', 'all');
        $search = trim((string) $request->get('search', ''));

        $barangs = Barang::orderBy('product_name')->get();
        $productMap = Product::whereIn('product_code', $barangs->pluck('product_code')->filter()->unique()->values())
            ->get()
            ->keyBy('product_code');

        $isPending = fn ($barang) => ($productMap[$barang->product_code] ?? null)
            && ($productMap[$barang->product_code]->product_name_clean === null);

        $filtered = $barangs->filter(function ($barang) use ($filter, $isPending) {
            if ($filter === 'pending') return $isPending($barang);
            if ($filter === 'done') return !$isPending($barang);
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
        $catalog = PackagingCatalog::toArray();
        $canManageStock = $this->canManageStock();

        // Health summary untuk modal Check Data
        $health = $this->buildBarangHealth($barangs, $productMap);

        return view('users.stock.manage-barang', compact(
            'filtered',
            'productMap',
            'total',
            'filter',
            'search',
            'catalog',
            'canManageStock',
            'health'
        ));
    }

    private function buildBarangHealth($barangs, $productMap): array
    {
        $missingPackaging = [];
        $missingClean = [];
        $missingQty = [];
        $seen = [];
        $duplicatesMap = [];

        foreach ($barangs as $barang) {
            $product = $productMap[$barang->product_code] ?? null;
            $kode = $barang->product_code ?: '—';
            $nama = $barang->product_name;
            $unit = $barang->unit ?: ($product->unit ?? null);

            if (empty($unit)) {
                $missingPackaging[] = ['kode' => $kode, 'nama' => $nama];
            } elseif (PackagingCatalog::canFill($unit)) {
                $pcs = (int) ($product->pcs_per_unit ?? 0);
                if ($pcs < 1) {
                    $missingQty[] = ['kode' => $kode, 'nama' => $nama, 'unit' => $unit];
                }
            }

            if (!$product || $product->product_name_clean === null) {
                $missingClean[] = ['kode' => $kode, 'nama' => $nama];
            }

            $key = strtolower(trim($barang->product_code ?: $barang->product_name));
            if ($key !== '') {
                $duplicatesMap[$key][] = ['kode' => $kode, 'nama' => $nama];
            }
        }

        $duplicates = array_values(array_filter($duplicatesMap, fn ($g) => count($g) > 1));

        return [
            'missingPackaging' => $missingPackaging,
            'missingQty' => $missingQty,
            'missingClean' => $missingClean,
            'duplicates' => $duplicates,
        ];
    }

    public function storeBarang(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }
        if (!$this->canManageStock()) {
            return redirect()->route('sales.stock.barang.index')->withErrors(['msg' => 'Anda tidak memiliki hak untuk menambah barang.']);
        }

        $request->merge([
            'product_code' => trim((string) $request->input('product_code')) ?: null,
            'product_name' => trim((string) $request->input('product_name')),
            'unit' => trim((string) $request->input('unit')) ?: null,
            'product_name_clean' => trim((string) $request->input('product_name_clean')) ?: null,
            'fill_unit' => trim((string) $request->input('fill_unit')) ?: null,
        ]);

        $newCode = trim((string) $request->get('product_code'));
        $targetProduct = $newCode !== '' && $newCode !== null ? Product::where('product_code', $newCode)->first() : null;

        $request->validate([
            'product_code' => 'nullable|string|max:50|unique:barangs,product_code',
            'product_name' => 'required|string|max:255|unique:barangs,product_name',
            'unit' => 'nullable|string|max:50',
            'product_name_clean' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'product_name_clean')->ignore($targetProduct?->id),
            ],
            'pcs_per_unit' => 'nullable|integer|min:0|max:100000',
            'fill_unit' => 'nullable|string|max:50',
        ]);

        $barang = Barang::create($request->only(['product_code', 'product_name', 'unit']));

        $this->restoreTombstoneForBarang($barang->product_code, $barang->product_name, $barang->unit);
        $this->saveKurasiForBarang($barang, $request);

        Cache::forget('barang_list_dropdown');

        return redirect()->route('sales.stock.barang.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function updateBarang(Request $request, Barang $barang)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }
        if (!$this->canManageStock()) {
            return redirect()->route('sales.stock.barang.index')->withErrors(['msg' => 'Anda tidak memiliki hak untuk mengubah barang.']);
        }

        $request->merge([
            'product_code' => trim((string) $request->input('product_code')) ?: null,
            'product_name' => trim((string) $request->input('product_name')),
            'unit' => trim((string) $request->input('unit')) ?: null,
            'product_name_clean' => trim((string) $request->input('product_name_clean')) ?: null,
            'fill_unit' => trim((string) $request->input('fill_unit')) ?: null,
        ]);

        $newCode = trim((string) $request->get('product_code'));
        $targetProduct = $newCode !== '' && $newCode !== null ? Product::where('product_code', $newCode)->first() : null;

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

        $barang->update($request->only(['product_code', 'product_name', 'unit']));

        $this->restoreTombstoneForBarang($barang->product_code, $barang->product_name, $barang->unit);
        $this->saveKurasiForBarang($barang, $request);

        Cache::forget('barang_list_dropdown');

        return redirect()->route('sales.stock.barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroyBarang(Barang $barang)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }
        if (!$this->canManageStock()) {
            abort(403, 'Unauthorized');
        }

        Product::where('product_code', $barang->product_code)->first()?->softDelete();

        $barang->delete();
        Cache::forget('barang_list_dropdown');

        return redirect()->route('sales.stock.barang.index')->with('success', 'Barang berhasil dihapus.');
    }

    public function exportBarang()
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }

        return Excel::download(new BarangMasterExport, 'master_barang_' . date('Ymd_His') . '.xlsx');
    }

    public function bulkUpdateBarang(Request $request)
    {
        if (!$this->canManageStock()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:barangs,id',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.pcs_per_unit' => 'nullable|integer|min:0|max:100000',
            'items.*.fill_unit' => 'nullable|string|max:50',
        ]);

        $failed = [];
        $successCount = 0;

        foreach ($request->input('items') as $item) {
            try {
                $barang = Barang::find($item['id']);
                if (!$barang) {
                    $failed[] = ['id' => $item['id'], 'reason' => 'Not found'];
                    continue;
                }

                $newUnit = trim((string) ($item['unit'] ?? ''));
                $newName = trim((string) ($item['name'] ?? ''));

                // Update unit di barangs bila berubah
                if ($newUnit !== ($barang->unit ?? '')) {
                    $barang->unit = $newUnit !== '' ? $newUnit : null;
                    $barang->save();
                }

                // Kurasi produk
                $fakeRequest = new Request([
                    'product_name_clean' => $newName !== '' ? $newName : null,
                    'pcs_per_unit' => $item['pcs_per_unit'] ?? null,
                    'fill_unit' => $item['fill_unit'] ?? null,
                ]);
                $this->saveKurasiForBarang($barang, $fakeRequest);
                $successCount++;
            } catch (\Throwable $e) {
                $failed[] = ['id' => $item['id'], 'reason' => $e->getMessage()];
            }
        }

        Cache::forget('barang_list_dropdown');

        if (!empty($failed) && $successCount === 0) {
            return response()->json(['message' => 'Semua item gagal diperbarui.', 'failed' => $failed], 422);
        }

        $message = $successCount . ' item berhasil diperbarui.';
        if (!empty($failed)) {
            $message .= ' ' . count($failed) . ' gagal.';
        }

        return response()->json(['message' => $message, 'failed' => $failed]);
    }

    // Packaging JSON endpoints (mirip AdminProductPackagingController tapi dengan guard gudang)
    public function barangPackagings()
    {
        if (!$this->hasAnySalesAccess()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
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

    public function storeBarangPackaging(Request $request)
    {
        if (!$this->canManageStock()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
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

    public function updateBarangPackaging(Request $request, ProductPackaging $packaging)
    {
        if (!$this->canManageStock()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
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

    public function destroyBarangPackaging(ProductPackaging $packaging)
    {
        if (!$this->canManageStock()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
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

    private function saveKurasiForBarang(Barang $barang, Request $request): void
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

        $packUnits = $unit !== null ? PackagingCatalog::unitsFor($unit) : [];
        if (PackagingCatalog::isSingle($unit ?? '')) {
            $fillUnit = null;
            $pcsPerUnit = null;
        } elseif ($fillUnit === '' && isset($packUnits[0])) {
            $fillUnit = $packUnits[0];
        }

        if (!empty($packUnits)) {
            $fillKey = ProductPackaging::normalize($fillUnit);
            $matched = array_values(array_filter($packUnits, fn ($u) => ProductPackaging::normalize($u) === $fillKey));
            $fillUnit = $matched[0] ?? $packUnits[0];
        }

        $packQty = PackagingCatalog::resolvePackQty($unit, (int) ($pcsPerUnit ?? 0));

        $data = [
            'product_name' => $barang->product_name,
            'unit' => $unit,
            'product_name_clean' => $clean !== '' ? $clean : null,
            'pcs_per_unit' => $packQty,
            'fill_unit' => $fillUnit !== '' ? $fillUnit : 'Pcs',
        ];
        $data['match_key'] = Product::buildMatchKey($data['product_name_clean'] ?: $barang->product_name);

        $product->update($data);
    }

    private function restoreTombstoneForBarang(?string $productCode, ?string $productName, ?string $unit): bool
    {
        $tombstone = Product::where('product_code', $productCode)->trashed()->first();
        if (!$tombstone) return false;
        $tombstone->restore([
            'product_name' => $productName,
            'unit' => $unit,
            'match_key' => Product::buildMatchKey($productName),
        ]);
        return true;
    }
}
