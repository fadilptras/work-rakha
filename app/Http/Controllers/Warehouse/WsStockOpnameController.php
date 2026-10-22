<?php

namespace App\Http\Controllers\Warehouse;

use App\Exports\OpnameRekapExport;
use App\Models\Product;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Services\DocumentNumberService;
use App\Services\StockMoveService;
use App\Services\StockOpnameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Opname & Rejected: hitung fisik berkala + catat barang rusak.
 * Alur: Rekap → Catat (simpan draf) → Selesaikan (stok diset + BA).
 */
class WsStockOpnameController extends WsBaseController
{
    /** Toleransi wajar: di atas max(5 pcs, 10% tercatat) wajib catatan + hitung ulang. */
    public const TOL_ABS = 5;

    public const TOL_PCT = 0.10;

    /**
     * Halaman Rekap Opname: kartu per-bulan + popup detail per bulan.
     */
    public function opnameIndex(Request $request, StockOpnameService $opnames)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Opname.');
        }

        $canManage = $this->canManage();

        $tahun = (int) $request->get('tahun', now()->year);
        if ($tahun < 2020 || $tahun > now()->year + 1) {
            $tahun = now()->year;
        }
        $bulan = (string) $request->get('bulan', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            $bulan = now()->format('Y-m');
        }
        $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->locale('id')->translatedFormat('F Y');
        $listTahun = [(string) $tahun => (string) $tahun];

        $productOptions = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get(['id', 'product_code', 'product_name', 'unit', 'stock']);

        $totalSku = Product::active()->count();

        $rekapPerBulan = [];
        $rekapDetailPerBulan = [];
        $listBulan = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = sprintf('%04d-%02d', $tahun, $m);
            $listBulan[$key] = \Carbon\Carbon::parse($key . '-01')->locale('id')->translatedFormat('F Y');
        }

        if (Schema::hasTable('stock_opnames')) {
            $rows = StockOpname::with(['product', 'creator', 'stockMove'])
                ->where('period', 'like', $tahun . '-%')
                ->orderByDesc('id')
                ->get()
                ->groupBy('period');

            foreach ($listBulan as $key => $label) {
                $inMonth = $rows->get($key, collect());
                $stats = $this->statsFromRows($inMonth);

                $rekapPerBulan[] = [
                    'key' => $key,
                    'label' => $label,
                    'sudah' => $stats['tersimpan'],
                    'tersimpan' => $stats['tersimpan'],
                    'selesai' => $stats['selesai'],
                    'menunggu' => $stats['menunggu'],
                    'sesuai' => $stats['sesuai'],
                    'selisih' => $stats['selisih'],
                    'total' => $totalSku,
                    'entries' => $stats['entries'],
                    'selected' => $key === $bulan,
                ];

                $rekapDetailPerBulan[$key] = $inMonth->map(function (StockOpname $o) {
                    return [
                        'produk' => ($o->product->product_code ?? '-') . ' — ' . ($o->product->product_name ?? '-'),
                        'tercatat' => (float) $o->recorded_qty,
                        'terhitung' => (float) $o->counted_qty,
                        'selisih' => (float) $o->diff_qty,
                        'waktu' => ($o->opname_at ?? $o->created_at)?->format('d M Y H:i') ?? '-',
                        'dok' => $o->stockMove->doc_no ?? '-',
                        'oleh' => $o->creator->name ?? '-',
                        'catatan' => $o->note ?: '-',
                        'pending' => $o->isDraft(),
                        'selesai' => $o->isDone(),
                        'pembuat' => $o->isDone() ? $o->ba_creator : null,
                        'pemeriksa' => $o->isDone() ? $o->ba_checker : null,
                        'ba' => $o->isDone() ? route('warehouse.stockopname.print', $o) : null,
                    ];
                })->values()->all();
            }

            $tahunDb = StockOpname::selectRaw('DISTINCT substr(period, 1, 4) as y')
                ->orderByDesc('y')->pluck('y')->filter()->map(fn ($y) => (string) $y)->all();
            $semuaTahun = array_unique(array_merge([(string) now()->year], $tahunDb, [(string) $tahun]));
            rsort($semuaTahun);
            $listTahun = array_combine($semuaTahun, $semuaTahun);
        }

        return view('users.warehouse.opname', compact(
            'canManage', 'productOptions', 'bulan', 'namaBulan',
            'rekapPerBulan', 'rekapDetailPerBulan', 'tahun', 'listTahun'
        ));
    }

    /**
     * Halaman Catat Opname (fokus pendataan). Query: ?tanggal=YYYY-MM-DD&q=...
     */
    public function catatIndex(Request $request, StockOpnameService $opnames)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Opname.');
        }
        if (! $this->canManage()) {
            abort(403, 'Hanya pengelola stok yang bisa input opname.');
        }

        $tanggal = $this->resolveCatatDate($request);
        $bulan = substr($tanggal, 0, 7);
        $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->locale('id')->translatedFormat('F Y');
        $namaTanggal = \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y');
        $q = trim((string) $request->get('q', ''));
        $qLike = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);

        $productOptions = Product::active()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('product_code', 'like', "%{$qLike}%")
                ->orWhere('product_name', 'like', "%{$qLike}%")))
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get(['id', 'product_code', 'product_name', 'unit', 'stock']);

        $savedMap = [];
        $pendingSet = [];
        $sesuaiSet = [];
        $sudahSet = [];
        if (Schema::hasTable('stock_opnames')) {
            $rows = StockOpname::with(['creator', 'stockMove'])
                ->where('period', $bulan)
                ->orderByDesc('id')
                ->get()
                ->groupBy('product_id');

            foreach ($rows as $pid => $group) {
                $row = $group->first();
                $pid = (int) $pid;
                $isPending = $row->isDraft();
                $isDone = $row->isDone();
                $diff = (float) $row->diff_qty;

                if ($isPending) {
                    $pendingSet[$pid] = true;
                } elseif (abs($diff) < 0.005) {
                    $sesuaiSet[$pid] = true;
                } else {
                    $sudahSet[$pid] = true;
                }

                $savedMap[$pid] = [
                    'tercatat' => (float) $row->recorded_qty,
                    'terhitung' => (float) $row->counted_qty,
                    'selisih' => $diff,
                    'catatan' => $row->note ?: '-',
                    'waktu' => ($row->opname_at ?? $row->created_at)?->format('d M Y H:i') ?? '-',
                    'dok' => $row->stockMove->doc_no ?? '-',
                    'oleh' => $row->creator->name ?? '-',
                    'status' => $row->status,
                    'pending' => $isPending,
                    'revision' => (int) $row->revision,
                    'pembuat' => $isDone ? $row->ba_creator : null,
                    'pemeriksa' => $isDone ? $row->ba_checker : null,
                ];
            }
        }

        $opnamedSet = array_fill_keys(array_map('intval', array_keys($savedMap)), true);
        $totalSku = Product::active()->count();
        $tersimpan = count($savedMap);
        $menunggu = count($pendingSet);
        $sesuai = count($sesuaiSet);
        $sudah = count($sudahSet);
        $belum = max(0, $totalSku - $tersimpan);
        $sesuaiTampil = 0;
        $selisihTampil = 0;
        foreach ($savedMap as $saved) {
            if (abs((float) $saved['selisih']) < 0.005) {
                $sesuaiTampil++;
            } else {
                $selisihTampil++;
            }
        }
        $baAvailable = $opnames->baAvailable($bulan);

        $lastBaRow = StockOpname::where('period', $bulan)
            ->whereNotNull('ba_creator')
            ->whereNotNull('ba_checker')
            ->orderByDesc('validated_at')
            ->orderByDesc('id')
            ->first();
        $baCreator = trim((string) ($lastBaRow->ba_creator ?? ''));
        $baChecker = trim((string) ($lastBaRow->ba_checker ?? ''));
        $hasExistingBa = $baCreator !== '' && $baChecker !== '';

        return view('users.warehouse.opname-catat', [
            'canManage' => true,
            'bulan' => $bulan,
            'tanggal' => $tanggal,
            'backRoute' => strtolower(trim((string) $request->get('from', ''))) === 'dashboard'
                ? route('warehouse.dashboard')
                : route('warehouse.stockopname.index', ['bulan' => $bulan]),
            'backText' => strtolower(trim((string) $request->get('from', ''))) === 'dashboard'
                ? 'Back to Warehouse Dashboard'
                : 'Back to Opname Recap',
            'namaBulan' => $namaBulan,
            'namaTanggal' => $namaTanggal,
            'q' => $q,
            'productOptions' => $productOptions,
            'opnamedSet' => $opnamedSet,
            'pendingSet' => $pendingSet,
            'sesuaiSet' => $sesuaiSet,
            'sudahSet' => $sudahSet,
            'savedMap' => $savedMap,
            'tersimpan' => $tersimpan,
            'sudah' => $sudah,
            'sesuai' => $sesuai,
            'sesuaiTampil' => $sesuaiTampil,
            'selisihTampil' => $selisihTampil,
            'menunggu' => $menunggu,
            'totalSku' => $totalSku,
            'belum' => $belum,
            'baAvailable' => $baAvailable,
            'baCreator' => $baCreator,
            'baChecker' => $baChecker,
            'hasExistingBa' => $hasExistingBa,
        ]);
    }

    /**
     * Simpan hasil hitung sebagai DRAF (stok belum berubah).
     * Selesaikan = stok pada tanggal opname diset + BA terbit.
     */
    public function opnameStore(Request $request, StockOpnameService $opnames)
    {
        if (! $this->canManage()) {
            abort(403);
        }
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'counted_qty' => ['required', 'numeric'],
            'expected_qty' => ['nullable', 'numeric'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'recount' => ['nullable'],
            'tanggal' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'bulan' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $recordedNow = round((float) $product->stock, 2);
        $counted = round((float) $validated['counted_qty'], 2);
        $isJson = $request->expectsJson();

        $todayStr = now()->format('Y-m-d');
        $tanggalIn = $validated['tanggal'] ?? null;
        if (! empty($tanggalIn)) {
            $dt = \Carbon\Carbon::createFromFormat('Y-m-d', $tanggalIn);
            if (! $dt || $dt->format('Y-m-d') !== $tanggalIn || $tanggalIn > $todayStr) {
                return $this->opnameError('Tanggal pelaksanaan tidak valid atau belum berjalan.', $isJson);
            }
            $tanggal = $tanggalIn;
        } else {
            $bulanIn = $validated['bulan'] ?? now()->format('Y-m');
            if ($bulanIn > now()->format('Y-m')) {
                return $this->opnameError('Bulan belum berjalan — pilih bulan berjalan atau sebelumnya.', $isJson);
            }
            $tanggal = $bulanIn === now()->format('Y-m')
                ? $todayStr
                : \Carbon\Carbon::parse($bulanIn . '-01')->endOfMonth()->format('Y-m-d');
        }
        $bulanKey = substr($tanggal, 0, 7);
        $namaBulanIsi = \Carbon\Carbon::parse($bulanKey . '-01')->locale('id')->translatedFormat('F Y');

        // Snapshot guard: stok berubah sejak form dibuka.
        if ($request->filled('expected_qty') && abs((float) $validated['expected_qty'] - $recordedNow) > 0.004) {
            return $this->opnameError(
                "Stok {$product->product_name} berubah saat Anda menghitung (kini {$recordedNow}, form menampilkan {$validated['expected_qty']}). Ulangi hitung dan simpan kembali.",
                $isJson,
                ['stock' => $recordedNow]
            );
        }

        // Baris opname yang sudah ada memakai qty tercatat saat pertama dihitung.
        // Jangan bandingkan dengan stok live yang mungkin sudah bergerak setelahnya.
        $existingRow = StockOpname::where('product_id', $product->id)
            ->where('period', substr($tanggal, 0, 7))
            ->first();
        $recordedBase = $existingRow ? (float) $existingRow->recorded_qty : $recordedNow;

        $diffNow = round($counted - $recordedBase, 2);
        $tolerance = self::toleranceFor($recordedBase);
        $note = trim((string) ($validated['reason'] ?? ''));
        if ($note === '-' || $note === '') {
            $note = '';
        }

        if (abs($diffNow) >= 0.005 && $note === '') {
            return $this->opnameError(
                "Ada selisih {$diffNow}: wajib isi keterangan penyebab sebelum menyimpan.",
                $isJson
            );
        }
        if (abs($diffNow) > $tolerance && empty($validated['recount'])) {
            return $this->opnameError(
                "Selisih {$diffNow} melebihi toleransi wajar (±{$tolerance}). Hitung ulang fisik, lalu centang konfirmasi hitung ulang.",
                $isJson
            );
        }

        try {
            $row = $opnames->saveResult($product, $tanggal, $counted, $note ?: null, Auth::user());
        } catch (\Throwable $e) {
            report($e);
            $msg = $e instanceof \InvalidArgumentException
                ? $e->getMessage()
                : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return $this->opnameError($msg, $isJson);
        }

        $diff = (float) $row->diff_qty;
        $status = $row->isDone() ? 'Selesai' : 'Draf tersimpan';
        $msg = "{$status}: {$product->product_name} — fisik {$counted}, selisih {$diff}. "
            . ($row->isDraft()
                ? "Klik Selesaikan untuk menerapkan ke stok tanggal {$namaBulanIsi} + menerbitkan BA."
                : 'BA sudah terbit.');

        if ($isJson) {
            return response()->json([
                'ok' => true,
                'draft' => $row->isDraft(),
                'done' => $row->isDone(),
                'message' => $msg,
                'stock' => $recordedNow,
                'recorded' => (float) $row->recorded_qty,
                'counted' => (float) $row->counted_qty,
                'diff' => $diff,
                'product_id' => $product->id,
                'status' => $row->status,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Selesaikan opname sebulan: stok pada tanggal opname diset ke hasil hitung,
     * semua draf menjadi DONE, dan BA terbit.
     */
    public function opnameFinish(
        Request $request,
        StockMoveService $svc,
        StockOpnameService $opnames,
        DocumentNumberService $docs
    ) {
        if (! $this->canManage()) {
            abort(403);
        }
        $validated = $request->validate([
            'bulan' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'pembuat_ba' => ['nullable', 'string', 'max:100'],
            'pemeriksa_ba' => ['nullable', 'string', 'max:100'],
        ]);
        $bulanKey = $validated['bulan'] ?? now()->format('Y-m');
        $namaBulan = \Carbon\Carbon::parse($bulanKey . '-01')->locale('id')->translatedFormat('F Y');
        $isJson = $request->expectsJson();

        $lastBaRow = StockOpname::where('period', $bulanKey)
            ->whereNotNull('ba_creator')
            ->whereNotNull('ba_checker')
            ->orderByDesc('validated_at')
            ->orderByDesc('id')
            ->first();
        $pembuatBa = trim((string) ($validated['pembuat_ba'] ?? ''));
        $pemeriksaBa = trim((string) ($validated['pemeriksa_ba'] ?? ''));
        if ($pembuatBa === '') {
            $pembuatBa = trim((string) ($lastBaRow->ba_creator ?? ''));
        }
        if ($pemeriksaBa === '') {
            $pemeriksaBa = trim((string) ($lastBaRow->ba_checker ?? ''));
        }

        if ($pembuatBa === '' || $pemeriksaBa === '') {
            $msg = 'Nama pembuat dan pemeriksa BA wajib diisi untuk opname pertama.';
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->withErrors([
                'pembuat_ba' => $msg,
            ])->withInput();
        }

        $drafts = StockOpname::where('period', $bulanKey)
            ->where('status', StockOpname::STATUS_DRAFT)->count();
        $doneRows = StockOpname::where('period', $bulanKey)
            ->where('status', StockOpname::STATUS_DONE)->count();

        if ($drafts === 0) {
            $msg = $doneRows > 0
                ? "Semua hasil opname {$namaBulan} sudah selesai. BA tersedia."
                : "Belum ada hasil opname {$namaBulan} yang perlu diselesaikan.";
            if ($isJson) {
                return response()->json([
                    'ok' => true,
                    'done' => 0,
                    'message' => $msg,
                    'ba_url' => $opnames->baAvailable($bulanKey)
                        ? route('warehouse.stockopname.print-bulan', ['bulan' => $bulanKey]) : null,
                ]);
            }

            return back()->with('success', $msg);
        }

        try {
            $done = $opnames->finishPeriod($bulanKey, $pembuatBa, $pemeriksaBa, Auth::user(), $svc, $docs);
        } catch (\Throwable $e) {
            report($e);
            $msg = $e instanceof \InvalidArgumentException
                ? $e->getMessage()
                : 'Terjadi kesalahan sistem saat menyelesaikan opname. Coba lagi.';
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg)->withInput();
        }

        $msg = "Opname {$namaBulan} diselesaikan: {$done} produk diset sesuai hasil hitung. BA siap diunduh.";
        if ($isJson) {
            return response()->json([
                'ok' => true,
                'done' => $done,
                'message' => $msg,
                'ba_url' => route('warehouse.stockopname.print-bulan', ['bulan' => $bulanKey]),
            ]);
        }

        return back()->with('success', $msg);
    }

    /** Scrap: barang rusak/buang + alasan, langsung tervalidasi. */
    public function scrapStore(Request $request, StockMoveService $svc)
    {
        if (! $this->canManage()) {
            abort(403);
        }
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $move = $svc->createMove([
                'product_id' => $validated['product_id'],
                'qty' => $validated['qty'],
                'type' => StockMove::TYPE_SCRAP,
                'reason' => $validated['reason'],
            ], Auth::user());
            $svc->validateMove($move, Auth::user());
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return back()->with('error', $errMsg)->withInput();
        }

        return back()->with('success', 'Rejected dicatat dan stok dikurangi.');
    }

    /** Rekap draf sebulan (JSON) untuk popup konfirmasi Selesaikan. */
    public function draftList(Request $request)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Opname.');
        }
        $bulan = (string) $request->get('bulan', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            $bulan = now()->format('Y-m');
        }

        $drafts = StockOpname::with(['product', 'creator'])
            ->where('period', $bulan)
            ->where('status', StockOpname::STATUS_DRAFT)
            ->orderBy('id')->get();

        $items = $drafts
            ->filter(fn (StockOpname $o) => abs((float) $o->diff_qty) >= 0.005)
            ->map(function (StockOpname $o) {
                $live = $o->product ? (float) $o->product->stock : 0;

                return [
                    'produk' => ($o->product->product_code ?? '-') . ' — ' . ($o->product->product_name ?? '-'),
                    'tercatat' => (float) $o->recorded_qty,
                    'terhitung' => (float) $o->counted_qty,
                    'selisih' => (float) $o->diff_qty,
                    'catatan' => $o->note ?: '-',
                    'dok' => '-',
                    'oleh' => $o->creator->name ?? '-',
                    'stok_kini' => $live,
                    'moved_after' => abs($live - (float) $o->recorded_qty) > 0.004,
                ];
            })->values();

        return response()->json([
            'ok' => true,
            'bulan' => $bulan,
            'namaBulan' => \Carbon\Carbon::parse($bulan . '-01')->locale('id')->translatedFormat('F Y'),
            'count' => $items->count(),
            'total_drafts' => $drafts->count(),
            'same_count' => $drafts->count() - $items->count(),
            'items' => $items,
        ]);
    }

    /** Cetak Berita Acara gabungan sebulan. */
    public function opnamePrintBulan(Request $request, StockOpnameService $opnames)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Opname.');
        }
        $bulan = (string) $request->get('bulan', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            $bulan = now()->format('Y-m');
        }
        if (! $opnames->baAvailable($bulan)) {
            abort(403, 'BA tersedia setelah opname bulan ini diselesaikan.');
        }
        $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->locale('id')->translatedFormat('F Y');

        $rows = StockOpname::with(['product', 'creator', 'validator'])
            ->where('period', $bulan)
            ->where('status', StockOpname::STATUS_DONE)
            ->orderBy('id')->get();
        if ($rows->isEmpty()) {
            abort(404, 'Belum ada opname selesai pada bulan ' . $namaBulan . '.');
        }

        $items = $rows
            ->filter(fn (StockOpname $o) => abs((float) $o->diff_qty) >= 0.005)
            ->map(fn (StockOpname $o) => [
                'produk' => ($o->product->product_code ?? '-') . ' — ' . ($o->product->product_name ?? '-'),
                'tercatat' => (float) $o->recorded_qty,
                'terhitung' => (float) $o->counted_qty,
                'selisih' => (float) $o->diff_qty,
                'catatan' => $o->note ?: '-',
            ])->values();

        $withBa = $rows->sortByDesc('validated_at')->first(fn ($o) => $o->ba_creator || $o->ba_checker);
        $pembuat = $withBa->ba_creator ?? null;
        $pemeriksa = $withBa->ba_checker ?? null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.warehouse.opname-ba-bulan', [
            'bulan' => $bulan,
            'namaBulan' => $namaBulan,
            'hari' => now()->translatedFormat('j'),
            'bulanTahun' => now()->locale('id')->translatedFormat('F Y'),
            'items' => $items,
            'noDifference' => $items->isEmpty(),
            'pembuat' => $pembuat,
            'pemeriksa' => $pemeriksa,
            'printedBy' => Auth::user()->name ?? 'System',
        ])->setPaper('a4', 'portrait');

        return $pdf->download('BA_Opname_' . $bulan . '.pdf');
    }

    /**
     * Export rekap seluruh hasil opname satu periode.
     * Format Excel dan PDF memakai data yang sama dari opnameRekapData().
     */
    public function opnameExportRekap(string $bulan, string $format)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Opname.');
        }
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            abort(404, 'Periode opname tidak valid.');
        }

        $format = strtolower($format);
        if (! in_array($format, ['excel', 'pdf'], true)) {
            abort(404, 'Format export tidak didukung.');
        }

        $data = $this->opnameRekapData($bulan);
        $filename = 'Rekap_Opname_' . $bulan;

        if ($format === 'excel') {
            return Excel::download(new OpnameRekapExport($data), $filename . '.xlsx');
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.warehouse.opname-rekap-bulan', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename . '.pdf');
    }

    /** Cetak Berita Acara stock opname per produk/periode. */
    public function opnamePrint(StockOpname $opname)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Opname.');
        }
        if (! $opname->isDone()) {
            abort(403, 'Berita Acara tersedia setelah opname diselesaikan (status masih draf).');
        }
        $opname->load(['product', 'creator', 'validator', 'stockMove']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.warehouse.opname-ba', [
            'opname' => $opname,
            'move' => $opname->stockMove,
            'tercatat' => (float) $opname->recorded_qty,
            'terhitung' => (float) $opname->counted_qty,
            'catatan' => $opname->note,
            'selisih' => (float) $opname->diff_qty,
            'pembuat' => $opname->ba_creator,
            'pemeriksa' => $opname->ba_checker,
            'printedBy' => Auth::user()->name ?? 'System',
        ])->setPaper('a4', 'portrait');

        $name = $opname->stockMove?->doc_no
            ?? ('SOP-' . $opname->period . '-' . $opname->product_id);
        $name = str_replace(['/', ' '], '-', $name);

        return $pdf->download('BA_Opname_' . $name . '.pdf');
    }

    /** Batas toleransi wajar angka tercatat. */
    public static function toleranceFor(float $recorded): float
    {
        return round(max(self::TOL_ABS, abs($recorded) * self::TOL_PCT), 2);
    }

    /** Tanggal catat: eksplisit > simpanan terakhir (cookie) > bulan > hari ini. */
    private function resolveCatatDate(Request $request): string
    {
        $today = now()->format('Y-m-d');
        $cookieKey = 'opname_last_date_' . (Auth::id() ?? 'guest');
        $last = (string) $request->cookie($cookieKey, '');
        if (! $this->isValidCatatDate($last, $today)) {
            $last = '';
        }

        $tanggalIn = (string) $request->get('tanggal', '');
        $bulanIn = (string) $request->get('bulan', '');
        $hasBulan = (bool) preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulanIn);

        if ($this->isValidCatatDate($tanggalIn, $today)) {
            $tanggal = $tanggalIn;
        } elseif ($last !== '' && (! $hasBulan || substr($last, 0, 7) === $bulanIn)) {
            $tanggal = $last;
        } elseif ($hasBulan) {
            $tanggal = $bulanIn === now()->format('Y-m')
                ? $today
                : min(\Carbon\Carbon::parse($bulanIn . '-01')->endOfMonth()->format('Y-m-d'), $today);
        } elseif ($last !== '') {
            $tanggal = $last;
        } else {
            $tanggal = $today;
        }

        Cookie::queue($cookieKey, $tanggal, 60 * 24 * 365, '/', null, null, true, false, 'lax');

        return $tanggal;
    }

    private function isValidCatatDate(string $value, string $today): bool
    {
        if ($value === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        try {
            $dt = \Carbon\Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable $e) {
            return false;
        }

        return $dt && $dt->format('Y-m-d') === $value
            && $value <= $today
            && $value >= '2020-01-01';
    }

    /** Ringkasan status dari koleksi baris opname. */
    /**
     * Bentuk data rekap yang dipakai bersama oleh export Excel dan PDF.
     *
     * @return array{bulan:string,namaBulan:string,rows:array,summary:array}
     */
    private function opnameRekapData(string $bulan): array
    {
        $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->locale('id')->translatedFormat('F Y');

        $records = StockOpname::with(['product', 'creator', 'stockMove'])
            ->where('period', $bulan)
            ->orderBy('id')
            ->get()
            ->sortBy(fn (StockOpname $row) => mb_strtolower(
                ($row->product->product_code ?? '') . ' ' . ($row->product->product_name ?? '')
            ))
            ->values();

        $rows = $records->map(function (StockOpname $row, int $index) {
            return [
                'no' => $index + 1,
                'kode' => $row->product->product_code ?? '-',
                'produk' => $row->product->product_name ?? '-',
                'satuan' => $row->product->unit ?? '-',
                'tercatat' => $row->recorded_qty !== null ? (float) $row->recorded_qty : null,
                'terhitung' => $row->counted_qty !== null ? (float) $row->counted_qty : null,
                'selisih' => $row->diff_qty !== null ? (float) $row->diff_qty : null,
                'status' => $row->isDone() ? 'Selesai' : 'Draf',
                'selesai' => $row->isDone(),
                'dok' => $row->stockMove->doc_no ?? '-',
                'waktu' => ($row->opname_at ?? $row->created_at)?->format('d M Y H:i') ?? '-',
                'oleh' => $row->creator->name ?? '-',
                'catatan' => $row->note ?: '-',
            ];
        })->all();

        $done = $records->filter(fn (StockOpname $row) => $row->isDone());
        $same = $done->filter(fn (StockOpname $row) => abs((float) $row->diff_qty) < 0.005)->count();

        return [
            'bulan' => $bulan,
            'namaBulan' => $namaBulan,
            'rows' => $rows,
            'summary' => [
                'total' => count($rows),
                'selesai' => $done->count(),
                'draf' => $records->count() - $done->count(),
                'sesuai' => $same,
                'selisih' => $done->count() - $same,
            ],
        ];
    }

    private function statsFromRows($rows): array
    {
        $done = $rows->where('status', StockOpname::STATUS_DONE);
        $draft = $rows->where('status', StockOpname::STATUS_DRAFT);

        return [
            'tersimpan' => $rows->pluck('product_id')->unique()->count(),
            'selesai' => $done->pluck('product_id')->unique()->count(),
            'menunggu' => $draft->pluck('product_id')->unique()->count(),
            'sesuai' => $done->filter(fn ($r) => abs((float) $r->diff_qty) < 0.005)->pluck('product_id')->unique()->count(),
            'selisih' => $done->filter(fn ($r) => abs((float) $r->diff_qty) >= 0.005)->pluck('product_id')->unique()->count(),
            'entries' => $rows->count(),
        ];
    }

    private function opnameError(string $message, bool $isJson, array $extra = [])
    {
        if ($isJson) {
            return response()->json(array_merge(['ok' => false, 'message' => $message], $extra), 422);
        }

        return back()->with('error', $message)->withInput();
    }

    /** Baca: divisi gudang/sales + manajemen. */
    protected function hasAccess(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        if (\App\Models\User::isSuperUserEmail($user->email ?? '')) {
            return true;
        }
        if ($user->role === 'admin') {
            return true;
        }
        $jabatan = strtolower(trim($user->jabatan ?? ''));
        $divisi = strtolower(trim($user->divisi ?? ''));
        if (\Illuminate\Support\Str::contains($jabatan, 'direktur') || $divisi === 'top management') {
            return true;
        }
        if (\Illuminate\Support\Str::contains($divisi, 'gudang')
            || \Illuminate\Support\Str::contains($jabatan, 'gudang')) {
            return true;
        }

        return in_array($divisi, ['marketing dan operasional', 'finance dan gudang', 'fianance dan gudang'])
            || \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang'
            || \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing') || \Illuminate\Support\Str::contains($jabatan, 'purchasing');
    }

    /** Tulis: pengelola stok + divisi gudang. */
    protected function canManage(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        if (\App\Models\User::isSuperUserEmail($user->email ?? '')) {
            return true;
        }
        if ($user->role === 'admin') {
            return true;
        }
        $jabatan = strtolower(trim($user->jabatan ?? ''));
        $divisi = strtolower(trim($user->divisi ?? ''));

        $isDivisiGudang = in_array($divisi, ['finance dan gudang', 'fianance dan gudang'], true)
            || \Illuminate\Support\Str::contains($divisi, 'gudang')
            || \Illuminate\Support\Str::contains($jabatan, 'gudang');

        return \Illuminate\Support\Str::contains($jabatan, 'test')
            || \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang'
            || \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing')
            || $isDivisiGudang;
    }
}
