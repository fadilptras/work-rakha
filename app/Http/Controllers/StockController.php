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
        $isTest = \Illuminate\Support\Str::contains($jabatan, 'test');

        return $isTopManagement || $isKepalaDivisiMO || $isAdminMarketing || $isTest;
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

        return in_array($divisi, ['marketing dan operasional']) || $isAdminGudang;
    }

    public function index(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Monitoring Stock.');
        }

        $items = Barang::orderBy('nama_barang')->get();
        $logs = StockLog::with('user')->orderBy('created_at', 'desc')->take(10)->get();
        $canManageStock = $this->canManageStock();

        return view('users.sales.stock', compact('items', 'logs', 'canManageStock'));
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
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function importExcel(Request $request)
    {
        if (!$this->canManageStock()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new StockImport;
            Excel::import($import, $request->file('file'));

            if ($import->logId) {
                // Update file name in log
                $log = StockLog::find($import->logId);
                if ($log) {
                    $log->update([
                        'nama_file' => $request->file('file')->getClientOriginalName()
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Stok berhasil diunggah!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengunggah file: ' . $e->getMessage());
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
        } catch (\Exception $e) {
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

        $isTest = \Illuminate\Support\Str::contains($jabatan, 'test');
        $isAdminGudang = \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang';

        return $isTest || $isAdminGudang;
    }
}
