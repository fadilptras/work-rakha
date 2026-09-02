<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StockLog;
use App\Models\StockLogDetail;
use App\Models\DailyStockHistory;
use App\Imports\StockImport;
use App\Exports\StockExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        return in_array($divisi, ['marketing dan operasional', 'finance dan gudang', 'fianance dan gudang']) || $isAdminGudang;
    }

    public function index(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Monitoring Stock.');
        }

        $items = Barang::orderBy('nama_barang')->get();
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

                foreach ($updates as $update) {
                    $barang = Barang::find($update['id']);
                    if ($barang) {
                        $oldStok = $barang->stok;
                        $oldStokPo = $barang->stok_po;
                        $newStok = intval($update['stok']);
                        $newStokPo = isset($update['stok_po']) ? intval($update['stok_po']) : $oldStokPo;

                        // Detail Log
                        StockLogDetail::create([
                            'stock_log_id' => $log->id,
                            'barang_id' => $barang->id,
                            'old_stok' => $oldStok,
                            'new_stok' => $newStok,
                            'old_stok_po' => $oldStokPo,
                            'new_stok_po' => $newStokPo,
                        ]);

                        // Update Barang
                        $barang->update([
                            'stok' => $newStok,
                            'stok_po' => $newStokPo,
                        ]);

                        // Update atau create daily stock history untuk hari ini
                        DailyStockHistory::updateOrCreate(
                            ['barang_id' => $barang->id, 'tanggal' => date('Y-m-d')],
                            ['stok' => $newStok, 'stok_po' => $newStokPo]
                        );
                    }
                }
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
                // Cari kecocokan data barang berdasarkan kode barang unik
                $barang = Barang::where('kode_barang', $data['kode'])->first();
                if (!$barang) {
                    $barang = Barang::where('nama_barang', $data['nama'])->first();
                }

                // Hanya tampilkan di pratinjau jika barang sudah terdaftar di database
                if ($barang) {
                    // Normalisasi satuan agar cocok case-insensitively dengan dropdown select di frontend
                    $dbSatuan = trim($barang->satuan ?: 'pcs');
                    $matchedSatuan = 'pcs';
                    $allowedOptions = ['pcs', 'box', 'botol', 'galon', 'Jerigen', 'karton', 'pack', 'paket', 'polybag', 'pouches', 'roll'];
                    foreach ($allowedOptions as $opt) {
                        if (strtolower($dbSatuan) === strtolower($opt)) {
                            $matchedSatuan = $opt;
                            break;
                        }
                    }

                    $processed[] = [
                        'id' => $barang->id,
                        'kode' => $data['kode'],
                        'nama' => $barang->nama_barang,
                        'satuan' => $matchedSatuan,
                        'stok_db' => $barang->stok,
                        'stok_excel' => $data['stok'],
                        'po' => $barang->stok_po
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

                foreach ($items as $item) {
                    // Cari barang berdasarkan kode_barang yang diunggah
                    $barang = Barang::where('kode_barang', $item['kode'])->first();
                    if ($barang) {
                        // Isi kode barang jika di DB masih kosong tetapi di Excel terdefinisi
                        if (empty($barang->kode_barang) && !empty($item['kode'])) {
                            $barang->kode_barang = $item['kode'];
                        }

                        $oldStok = $barang->stok;
                        $oldStokPo = $barang->stok_po;
                        $newStok = intval($item['stok_excel']);
                        
                        // Perbarui satuan jika ada perubahan dari form pratinjau
                        if (!empty($item['satuan'])) {
                            $barang->satuan = $item['satuan'];
                        }

                        // Buat rincian log perubahan mutasi barang
                        StockLogDetail::create([
                            'stock_log_id' => $log->id,
                            'barang_id' => $barang->id,
                            'old_stok' => $oldStok,
                            'new_stok' => $newStok,
                            'old_stok_po' => $oldStokPo,
                            'new_stok_po' => $oldStokPo,
                        ]);

                        // Perbarui jumlah stok di database produk
                        $barang->update([
                            'stok' => $newStok,
                        ]);

                        // Perbarui atau buat riwayat stok harian untuk hari ini
                        DailyStockHistory::updateOrCreate(
                            ['barang_id' => $barang->id, 'tanggal' => date('Y-m-d')],
                            ['stok' => $newStok, 'stok_po' => $oldStokPo]
                        );
                    }
                }
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

        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kode_barang' => 'required|string|max:255|unique:barangs,kode_barang',
            'satuan' => 'required|string|max:255',
        ]);

        try {
            $barang = Barang::create([
                'nama_barang' => trim($request->input('nama_barang')),
                'kode_barang' => preg_replace('/\s+/', ' ', trim($request->input('kode_barang'))),
                'satuan' => $request->input('satuan'),
                'stok' => 0,
                'stok_po' => 0,
            ]);

            // Buat history awal hari ini
            DailyStockHistory::create([
                'barang_id' => $barang->id,
                'tanggal' => date('Y-m-d'),
                'stok' => 0,
                'stok_po' => 0,
            ]);

            return response()->json(['success' => true, 'message' => 'Barang ' . $barang->nama_barang . ' berhasil ditambahkan!']);
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
            fputcsv($file, ['kode_barang', 'nama_barang', 'stok', 'stok_po']);
            // Put some sample active barangs
            $samples = Barang::take(5)->get();
            foreach ($samples as $sample) {
                fputcsv($file, [$sample->kode_barang, $sample->nama_barang, $sample->stok, $sample->stok_po]);
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
                foreach ($log->details as $detail) {
                    $barang = Barang::find($detail->barang_id);
                    if ($barang) {
                        // Kembalikan ke stok lama
                        $barang->update([
                            'stok' => $detail->old_stok,
                            'stok_po' => $detail->old_stok_po,
                        ]);

                        // Update or create daily stock history untuk hari ini
                        DailyStockHistory::updateOrCreate(
                            ['barang_id' => $barang->id, 'tanggal' => date('Y-m-d')],
                            ['stok' => $detail->old_stok, 'stok_po' => $detail->old_stok_po]
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

        $details = $log->details()->with('barang')->get()->map(function($detail) {
            return [
                'nama' => $detail->barang->nama_barang ?? 'Barang Terhapus',
                'kode' => $detail->barang->kode_barang ?? '-',
                'old_stok' => $detail->old_stok,
                'new_stok' => $detail->new_stok,
                'old_stok_po' => $detail->old_stok_po,
                'new_stok_po' => $detail->new_stok_po,
            ];
        });

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

    public function stockHistoryData(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $date = $request->input('date', date('Y-m-d'));

        // Fetch daily histories for this date
        $histories = DailyStockHistory::where('tanggal', $date)->get()->keyBy('barang_id');

        // Build fallback list
        $barangs = Barang::all();
        $historyData = [];
        foreach ($barangs as $barang) {
            $history = $histories->get($barang->id);
            if ($history) {
                $historyData[$barang->id] = [
                    'stok' => $history->stok,
                    'stok_po' => $history->stok_po
                ];
            } else {
                // Find latest history <= date
                $latestBefore = DailyStockHistory::where('barang_id', $barang->id)
                    ->where('tanggal', '<=', $date)
                    ->orderBy('tanggal', 'desc')
                    ->first();
                if ($latestBefore) {
                    $historyData[$barang->id] = [
                        'stok' => $latestBefore->stok,
                        'stok_po' => $latestBefore->stok_po
                    ];
                } else {
                    // Fall back to current stock if date is today, or default 0
                    if ($date === date('Y-m-d')) {
                        $historyData[$barang->id] = [
                            'stok' => $barang->stok,
                            'stok_po' => $barang->stok_po
                        ];
                    } else {
                        // Find oldest history
                        $oldest = DailyStockHistory::where('barang_id', $barang->id)
                            ->orderBy('tanggal', 'asc')
                            ->first();
                        $historyData[$barang->id] = [
                            'stok' => $oldest ? $oldest->stok : 0,
                            'stok_po' => $oldest ? $oldest->stok_po : 0
                        ];
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'history' => $historyData
        ]);
    }

    public function canManageStock(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $jabatan = strtolower($user->jabatan ?? '');

        // $isTest = \Illuminate\Support\Str::contains($jabatan, 'test');
        $isAdminGudang = \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang';

        return $isAdminGudang
        // || $isTest
        ;
    }
}
