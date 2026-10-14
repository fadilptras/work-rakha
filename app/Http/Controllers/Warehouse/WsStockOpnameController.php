<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\Product;
use App\Models\StockMove;
use App\Services\DocumentNumberService;
use App\Services\StockMoveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Opname & Rejected: hitung fisik berkala + catat barang rusak.
 * Alur: Rekap (index) → Catat draf (catat) → Selesaikan/posting (finish) + BA.
 */
class WsStockOpnameController extends WsBaseController
{
    /** Toleransi wajar: di atas max(5 pcs, 10% tercatat) wajib catatan + hitung ulang. */
    public const TOL_ABS = 5;

    public const TOL_PCT = 0.10;

    /**
     * Halaman Rekap Opname: kartu per-bulan + popup detail per bulan.
     * Pendataan pindah ke halaman Catat (catatIndex).
     */
    public function opnameIndex(Request $request)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Opname.');
        }

        $canManage = $this->canManage();

        // Filter tahun untuk kartu Jan–Des (default tahun berjalan).
        $tahun = (int) $request->get('tahun', now()->year);
        if ($tahun < 2020 || $tahun > now()->year + 1) {
            $tahun = now()->year;
        }
        // Bulan aktif (untuk tombol Catat + penanda kartu terpilih).
        $bulan = (string) $request->get('bulan', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            $bulan = now()->format('Y-m');
        }
        [$tahunBulan, $bulanAngka] = explode('-', $bulan);
        $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->locale('id')->translatedFormat('F Y');
        $listTahun = [(string) $tahun => (string) $tahun];

        $productOptions = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get(['id', 'product_code', 'product_name', 'unit', 'stock']);

        $totalSku = Product::active()->count();

        // Kartu + detail per-bulan setahun penuh.
        $rekapPerBulan = [];
        $rekapDetailPerBulan = [];
        $listBulan = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = sprintf('%04d-%02d', $tahun, $m);
            $listBulan[$key] = \Carbon\Carbon::parse($key . '-01')->locale('id')->translatedFormat('F Y');
        }
        if (Schema::hasTable('stock_moves')) {
            $moves12 = StockMove::with(['product', 'user'])
                ->where('type', StockMove::TYPE_ADJUSTMENT)
                ->where('reason', 'like', 'Opname:%')
                ->whereYear('created_at', $tahun)
                ->orderByDesc('id')
                ->get();
            $moves12->each(function ($m) {
                $parsed = self::parseOpnameReason($m->reason);
                $m->opname_tercatat = $parsed['tercatat'];
                $m->opname_terhitung = $parsed['terhitung'];
                $m->opname_catatan = $parsed['catatan'];
                $m->opname_pembuat = $parsed['pembuat'];
                $m->opname_pemeriksa = $parsed['pemeriksa'];
                $m->opname_selisih = ($parsed['tercatat'] !== null && $parsed['terhitung'] !== null)
                    ? round($parsed['terhitung'] - $parsed['tercatat'], 2)
                    : null;
            });
            foreach ($listBulan as $key => $label) {
                $inMonth = $moves12->filter(fn ($mv) => $mv->created_at->format('Y-m') === $key)->values();
                $rekapPerBulan[] = [
                    'key' => $key,
                    'label' => $label,
                    'sudah' => $inMonth->pluck('product_id')->unique()->count(),
                    'menunggu' => $inMonth->where('status', StockMove::STATUS_WAITING)->pluck('product_id')->unique()->count(),
                    'total' => $totalSku,
                    'entries' => $inMonth->count(),
                    'selected' => $key === $bulan,
                ];
                $rekapDetailPerBulan[$key] = $inMonth->map(fn ($m) => [
                    'produk' => ($m->product->product_code ?? '-') . ' — ' . ($m->product->product_name ?? '-'),
                    'tercatat' => $m->opname_tercatat,
                    'terhitung' => $m->opname_terhitung,
                    'selisih' => $m->opname_selisih,
                    'waktu' => $m->created_at->format('d M Y H:i'),
                    'dok' => $m->doc_no ?? '-',
                    'oleh' => $m->user->name ?? '-',
                    'catatan' => $m->opname_catatan ?: '-',
                    'pending' => $m->status === StockMove::STATUS_WAITING,
                    'pembuat' => $m->opname_pembuat ?? null,
                    'pemeriksa' => $m->opname_pemeriksa ?? null,
                    'ba' => route('warehouse.stockopname.print', $m),
                ])->values()->all();
            }
        }

        if (Schema::hasTable('stock_moves')) {
            $tahunDb = StockMove::where('type', StockMove::TYPE_ADJUSTMENT)
                ->where('reason', 'like', 'Opname:%')
                ->selectRaw('YEAR(created_at) as y')
                ->distinct()
                ->orderByDesc('y')
                ->pluck('y')
                ->map(fn ($y) => (string) $y)
                ->all();
            $semuaTahun = array_unique(array_merge([(string) now()->year], $tahunDb, [(string) $tahun]));
            rsort($semuaTahun);
            $listTahun = array_combine($semuaTahun, $semuaTahun);
        }

        return view('users.warehouse.opname', compact('canManage', 'productOptions', 'bulan', 'namaBulan', 'rekapPerBulan', 'rekapDetailPerBulan', 'tahun', 'listTahun'));
    }

    /**
     * Halaman Catat Opname (fokus pendataan). Query: ?tanggal=YYYY-MM-DD&q=...
     */
    public function catatIndex(Request $request)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Opname.');
        }
        if (! $this->canManage()) {
            abort(403, 'Hanya pengelola stok yang bisa input opname.');
        }

        // Tanggal pelaksanaan (atribusi bulan = 7 char pertama).
        // Legacy ?bulan=YYYY-MM dipetakan ke tanggal default bulan tsb.
        $todayStr = now()->format('Y-m-d');
        $tanggalIn = (string) $request->get('tanggal', '');
        $bulanIn = (string) $request->get('bulan', '');
        $tanggal = $todayStr;
        if ($tanggalIn !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalIn)) {
            try {
                $dt = \Carbon\Carbon::createFromFormat('Y-m-d', $tanggalIn);
            } catch (\Throwable $e) {
                $dt = null;
            }
            if ($dt && $dt->format('Y-m-d') === $tanggalIn && $tanggalIn <= $todayStr && $tanggalIn >= '2020-01-01') {
                $tanggal = $tanggalIn;
            }
        } elseif (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulanIn)) {
            $tanggal = $bulanIn === now()->format('Y-m')
                ? $todayStr
                : min(\Carbon\Carbon::parse($bulanIn . '-01')->endOfMonth()->format('Y-m-d'), $todayStr);
        }
        $bulan = substr($tanggal, 0, 7);
        [$tahunBulan, $bulanAngka] = explode('-', $bulan);
        $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->locale('id')->translatedFormat('F Y');
        $namaTanggal = \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y');
        $q = trim((string) $request->get('q', ''));
        // Escape wildcard LIKE agar %/_ di ketikan dicari harfiah.
        $qLike = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);

        $productOptions = Product::active()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('product_code', 'like', "%{$qLike}%")
                ->orWhere('product_name', 'like', "%{$qLike}%")))
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get(['id', 'product_code', 'product_name', 'unit', 'stock']);

        // SKU yang sudah ada entri opname pada bulan terpilih.
        $opnamedIds = collect();
        // Entri terakhir per SKU (ditampilkan lagi saat kartunya diklik).
        $savedMap = [];
        // Status kartu by entri TERAKHIR: draf / sesuai / sudah.
        $pendingSet = [];
        $sesuaiSet = [];
        $sudahSet = [];
        if (Schema::hasTable('stock_moves')) {
            $opnamedIds = StockMove::where('type', StockMove::TYPE_ADJUSTMENT)
                ->where('reason', 'like', 'Opname:%')
                ->whereYear('created_at', $tahunBulan)
                ->whereMonth('created_at', $bulanAngka)
                ->pluck('product_id')->unique()->map(fn ($v) => (int) $v);
            $lastMoves = StockMove::with('user')
                ->where('type', StockMove::TYPE_ADJUSTMENT)
                ->where('reason', 'like', 'Opname:%')
                ->whereYear('created_at', $tahunBulan)
                ->whereMonth('created_at', $bulanAngka)
                ->orderByDesc('id')->get()
                ->groupBy('product_id');
            foreach ($lastMoves as $pid => $group) {
                $m = $group->first();
                $parsed = self::parseOpnameReason($m->reason);
                $selisih = ($parsed['tercatat'] !== null && $parsed['terhitung'] !== null)
                    ? round($parsed['terhitung'] - $parsed['tercatat'], 2) : null;
                $isPending = $m->status === StockMove::STATUS_WAITING;
                if ($isPending) {
                    $pendingSet[(int) $pid] = true;
                } elseif ($selisih !== null && abs($selisih) < 0.005) {
                    $sesuaiSet[(int) $pid] = true;
                } else {
                    $sudahSet[(int) $pid] = true;
                }
                $savedMap[(int) $pid] = [
                    'tercatat' => $parsed['tercatat'],
                    'terhitung' => $parsed['terhitung'],
                    'selisih' => $selisih,
                    'catatan' => $parsed['catatan'] ?: '-',
                    'waktu' => $m->created_at ? $m->created_at->format('d M Y H:i') : '-',
                    'dok' => $m->doc_no ?? '-',
                    'oleh' => $m->user->name ?? '-',
                    'status' => $m->status,
                    'pending' => $isPending,
                    'pembuat' => $parsed['pembuat'] ?? null,
                    'pemeriksa' => $parsed['pemeriksa'] ?? null,
                ];
            }
        }
        $opnamedSet = $opnamedIds->flip()->all();

        $totalSku = Product::active()->count();
        $sudah = $opnamedIds->count();
        $belum = max(0, $totalSku - $sudah);

        return view('users.warehouse.opname-catat', [
            'canManage' => true,
            'bulan' => $bulan,
            'tanggal' => $tanggal,
            'namaBulan' => $namaBulan,
            'namaTanggal' => $namaTanggal,
            'q' => $q,
            'productOptions' => $productOptions,
            'opnamedSet' => $opnamedSet,
            'pendingSet' => $pendingSet,
            'sesuaiSet' => $sesuaiSet,
            'sudahSet' => $sudahSet,
            'savedMap' => $savedMap,
            'sudah' => count($sudahSet),
            'sesuai' => count($sesuaiSet),
            'menunggu' => count($pendingSet),
            'totalSku' => $totalSku,
            'belum' => $belum,
        ]);
    }

    /**
     * Catat = DRAF (stok belum berubah). Selesaikan = posting + BA.
     * Snapshot guard cegah timpa mutasi lain. Keterangan wajib tiap selisih;
     * di atas toleransi wajib centang hitung ulang.
     */
    public function opnameStore(Request $request, StockMoveService $svc, DocumentNumberService $docs)
    {
        if (! $this->canManage()) {
            abort(403);
        }
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'counted_qty' => ['required', 'numeric', 'min:0'],
            'expected_qty' => ['nullable', 'numeric'],
            'reason' => ['nullable', 'string', 'max:1000'],
            // "accepted" itu implicit: gagal saat checkbox tak dikirim. Wajib-tidaknya
            // recount ditegakkan manual di gate toleransi bawah.
            'recount' => ['nullable'],
            // Tanggal pelaksanaan (diutamakan) atau legacy bulan atribusi.
            'tanggal' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'bulan' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $recorded = (float) $product->stock;
        $counted = (float) $validated['counted_qty'];
        $isJson = $request->expectsJson();

        $todayStr = now()->format('Y-m-d');
        $bulanKey = now()->format('Y-m');
        $backdateAt = null;
        $tanggalIn = $validated['tanggal'] ?? null;
        if (! empty($tanggalIn)) {
            try {
                $dt = \Carbon\Carbon::createFromFormat('Y-m-d', $tanggalIn);
            } catch (\Throwable $e) {
                $dt = null;
            }
            if (! $dt || $dt->format('Y-m-d') !== $tanggalIn) {
                $msg = 'Tanggal pelaksanaan tidak valid.';
                if ($isJson) {
                    return response()->json(['ok' => false, 'message' => $msg], 422);
                }

                return back()->with('error', $msg)->withInput();
            }
            if ($tanggalIn > $todayStr) {
                $msg = 'Tanggal pelaksanaan belum berjalan (masa depan).';
                if ($isJson) {
                    return response()->json(['ok' => false, 'message' => $msg], 422);
                }

                return back()->with('error', $msg)->withInput();
            }
            $bulanKey = substr($tanggalIn, 0, 7);
            // Hari ini: pakai waktu kini. Hari lalu: tempel ke tanggal tsb (jam kini)
            // agar kartu bulan atribusi ikut lengkap.
            $backdateAt = $tanggalIn === $todayStr ? null : \Carbon\Carbon::parse($tanggalIn)->setTimeFrom(now());
        } else {
            $bulanIn = $validated['bulan'] ?? now()->format('Y-m');
            $bulanKey = $bulanIn;
            if ($bulanKey > now()->format('Y-m')) {
                $namaBulanTmp = \Carbon\Carbon::parse($bulanKey . '-01')->locale('id')->translatedFormat('F Y');
                $msg = "Bulan {$namaBulanTmp} belum berjalan — pilih bulan berjalan atau sebelumnya.";
                if ($isJson) {
                    return response()->json(['ok' => false, 'message' => $msg], 422);
                }

                return back()->with('error', $msg)->withInput();
            }
            $backdateAt = $bulanKey === now()->format('Y-m')
                ? null
                : \Carbon\Carbon::parse($bulanKey . '-01')->endOfMonth()->setTimeFrom(now());
        }
        $namaBulanIsi = \Carbon\Carbon::parse($bulanKey . '-01')->locale('id')->translatedFormat('F Y');

        // Snapshot guard: tolak bila stok berubah sejak form dibuka.
        if ($request->filled('expected_qty') && abs((float) $validated['expected_qty'] - $recorded) > 0.004) {
            $msg = "Stok {$product->product_name} berubah saat Anda menghitung (kini {$recorded}, form menampilkan {$validated['expected_qty']}). Ulangi hitung dan simpan kembali.";
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => $msg, 'stock' => $recorded], 422);
            }

            return back()
                ->with('error', $msg)
                ->withInput();
        }

        $diff = round($counted - $recorded, 2);
        if (abs($diff) < 0.005) {
            // SESUAI: catat bukti qty 0 (DONE, stok tetap) agar status awet.
            try {
                $docNo = $docs->formatSop($docs->nextSequence('SOP'), $backdateAt ?? now());
                $move = $svc->logExternalMove(
                    $product->id,
                    0,
                    StockMove::TYPE_ADJUSTMENT,
                    'stock_opname',
                    null,
                    Auth::user(),
                    "Opname: tercatat {$recorded}, terhitung {$counted}",
                    StockMove::LOC_ADJUSTMENT,
                    StockMove::LOC_STOCK
                );
                $move->update(['doc_no' => $docNo, 'uom' => $product->unit]);
                if ($backdateAt) {
                    // created_at tak fillable + auto-touch menimpa: matikan timestamps sesaat.
                    $move->timestamps = false;
                    $move->created_at = $backdateAt;
                    $move->updated_at = $backdateAt;
                    $move->save();
                }
            } catch (\Throwable $e) {
                report($e);
                $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';
                if ($isJson) {
                    return response()->json(['ok' => false, 'message' => $errMsg], 422);
                }

                return back()->with('error', $errMsg)->withInput();
            }
            $msg = "Stok {$product->product_name} sudah sesuai ({$recorded}). Tercatat sebagai bukti opname {$namaBulanIsi}.";
            if ($isJson) {
                return response()->json(['ok' => true, 'sesuai' => true, 'message' => $msg, 'stock' => $recorded, 'product_id' => $product->id, 'diff' => 0, 'doc_no' => $docNo]);
            }

            return back()->with('success', $msg);
        }

        // Keterangan wajib tiap selisih; di atas toleransi wajib centang hitung ulang.
        $tolerance = self::toleranceFor($recorded);
        $note = trim((string) ($validated['reason'] ?? ''));
        if ($note === '' || $note === '-') {
            $msg = "Ada selisih {$diff}: wajib isi keterangan penyebab (mis. sample tak tercatat, salah rak, SJ belum input).";
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg)
                ->withInput();
        }
        if (abs($diff) > $tolerance && empty($validated['recount'])) {
            $msg = "Selisih {$diff} melebihi toleransi wajar (±{$tolerance}). Hitung ulang fisik, lalu centang konfirmasi hitung ulang.";
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg)
                ->withInput();
        }

        try {
            $docNo = $docs->formatSop($docs->nextSequence('SOP'), $backdateAt ?? now());
            $reason = "Opname: tercatat {$recorded}, terhitung {$counted}"
                . ($note !== '' ? "; Catatan: {$note}" : '');
            // Draf = stok belum berubah; posting di opnameFinish.
            $move = $svc->createMove([
                'product_id' => $product->id,
                'qty' => abs($diff),
                'type' => StockMove::TYPE_ADJUSTMENT,
                'from_location' => $diff > 0 ? StockMove::LOC_ADJUSTMENT : StockMove::LOC_STOCK,
                'to_location' => $diff > 0 ? StockMove::LOC_STOCK : StockMove::LOC_ADJUSTMENT,
                'reason' => $reason,
                'doc_no' => $docNo,
                'occurred_at' => $backdateAt ? $backdateAt->toDateTimeString() : null,
            ], Auth::user());
            // Draf lama produk+bulan sama diganti entri terbaru (anti dobel posting).
            // Dihapus setelah draf baru tersimpan; gagal hapus diabaikan (finish hanya posting terbaru).
            try {
                StockMove::where('type', StockMove::TYPE_ADJUSTMENT)
                    ->where('status', StockMove::STATUS_WAITING)
                    ->where('reason', 'like', 'Opname:%')
                    ->where('product_id', $product->id)
                    ->whereYear('created_at', substr($bulanKey, 0, 4))
                    ->whereMonth('created_at', (int) substr($bulanKey, 5, 2))
                    ->where('id', '<>', $move->id)
                    ->delete();
            } catch (\Throwable $e) {
                // Sengaja diabaikan (lihat atas).
            }
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';
            if ($isJson) {
                return response()->json(['ok' => false, 'message' => $errMsg], 422);
            }

            return back()->with('error', $errMsg)->withInput();
        }

        $msg = "Draf opname {$product->product_name} ({$docNo}) tersimpan: selisih {$diff}, menunggu penyelesaian. Stok belum berubah."
            . ($backdateAt ? " Dicatat untuk {$namaBulanIsi}." : '');
        if ($isJson) {
            return response()->json(['ok' => true, 'pending' => true, 'message' => $msg, 'stock' => $recorded, 'product_id' => $product->id, 'diff' => $diff, 'doc_no' => $docNo, 'move_id' => $move->id]);
        }

        return back()
            ->with('success', $msg);
    }

    /**
     * Selesaikan opname sebulan: posting draf jadi DONE (stok berubah + BA terbit).
     * Draf basi (stok bergerak sejak dicatat) dilewati — wajib hitung ulang.
     */
    public function opnameFinish(Request $request, StockMoveService $svc)
    {
        if (! $this->canManage()) {
            abort(403);
        }
        $validated = $request->validate([
            'bulan' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'pembuat_ba' => ['required', 'string', 'max:100'],
            'pemeriksa_ba' => ['required', 'string', 'max:100'],
        ]);
        $bulanKey = $validated['bulan'] ?? now()->format('Y-m');
        [$tahunBulan, $bulanAngka] = explode('-', $bulanKey);
        $namaBulan = \Carbon\Carbon::parse($bulanKey . '-01')->locale('id')->translatedFormat('F Y');
        $pembuatBa = trim($validated['pembuat_ba']);
        $pemeriksaBa = trim($validated['pemeriksa_ba']);
        $isJson = $request->expectsJson();

        $drafts = StockMove::with('product')
            ->where('type', StockMove::TYPE_ADJUSTMENT)
            ->where('status', StockMove::STATUS_WAITING)
            ->where('reason', 'like', 'Opname:%')
            ->whereYear('created_at', $tahunBulan)
            ->whereMonth('created_at', $bulanAngka)
            ->orderBy('id')->get();

        // Satu produk = draf terbaru saja; sisa draf lama dibersihkan.
        $drafts = $drafts->groupBy('product_id')->map(fn ($g) => $g->sortByDesc('id')->first())->values();
        // Bersihkan sisa draf lama produk yang sama agar tidak menumpuk.
        StockMove::where('type', StockMove::TYPE_ADJUSTMENT)
            ->where('status', StockMove::STATUS_WAITING)
            ->where('reason', 'like', 'Opname:%')
            ->whereYear('created_at', $tahunBulan)
            ->whereMonth('created_at', $bulanAngka)
            ->whereNotIn('id', $drafts->pluck('id')->all())
            ->delete();

        $done = 0;
        $skipped = [];
        foreach ($drafts as $move) {
            $parsed = self::parseOpnameReason($move->reason);
            $live = $move->product ? (float) $move->product->stock : null;
            // Stok bergerak sejak draf dicatat → lewati (jangan posting angka basi).
            if ($live === null || ($parsed['tercatat'] !== null && abs($live - $parsed['tercatat']) > 0.004)) {
                $skipped[] = ($move->product->product_code ?? '-') . ' — ' . ($move->product->product_name ?? '-')
                    . " (tercatat {$parsed['tercatat']}, kini {$live}; hitung ulang)";
                continue;
            }
            try {
                $svc->validateMove($move, Auth::user());
                // Cap nama BA agar tercetak di Berita Acara.
                $move->update(['reason' => rtrim($move->reason) . "; BA oleh: {$pembuatBa}; Diperiksa: {$pemeriksaBa}"]);
                $done++;
            } catch (\Throwable $e) {
                $skipped[] = ($move->product->product_code ?? '-') . ' — ' . ($move->product->product_name ?? '-')
                    . " ({$e->getMessage()})";
            }
        }

        if ($done === 0 && count($skipped) === 0) {
            $msg = "Tidak ada draf opname {$namaBulan} yang perlu diselesaikan.";
            if ($isJson) {
                return response()->json(['ok' => true, 'done' => 0, 'message' => $msg, 'skipped' => []]);
            }

            return back()->with('success', $msg);
        }

        $msg = "Opname {$namaBulan} diselesaikan: {$done} entri diposting (stok resmi berubah + BA tersedia)."
            . (count($skipped) ? ' ' . count($skipped) . ' dilewati: ' . implode('; ', $skipped) : '');
        if ($isJson) {
            return response()->json(['ok' => true, 'done' => $done, 'message' => $msg, 'skipped' => $skipped]);
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
        [$tahunBulan, $bulanAngka] = explode('-', $bulan);
        $moves = StockMove::with(['product', 'user'])
            ->where('type', StockMove::TYPE_ADJUSTMENT)
            ->where('status', StockMove::STATUS_WAITING)
            ->where('reason', 'like', 'Opname:%')
            ->whereYear('created_at', $tahunBulan)
            ->whereMonth('created_at', $bulanAngka)
            ->orderByDesc('id')->get()
            // Satu produk satu baris terbaru (anti dobel tampil).
            ->groupBy('product_id')->map(fn ($g) => $g->first())->sortBy('id')->values();
        $items = $moves->map(function ($m) {
            $parsed = self::parseOpnameReason($m->reason);
            $selisih = ($parsed['tercatat'] !== null && $parsed['terhitung'] !== null)
                ? round($parsed['terhitung'] - $parsed['tercatat'], 2) : null;
            $live = $m->product ? (float) $m->product->stock : null;
            $stale = $live === null || ($parsed['tercatat'] !== null && abs($live - $parsed['tercatat']) > 0.004);

            return [
                'produk' => ($m->product->product_code ?? '-') . ' — ' . ($m->product->product_name ?? '-'),
                'tercatat' => $parsed['tercatat'],
                'terhitung' => $parsed['terhitung'],
                'selisih' => $selisih,
                'catatan' => $parsed['catatan'] ?: '-',
                'dok' => $m->doc_no ?? '-',
                'oleh' => $m->user->name ?? '-',
                'stale' => $stale,
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'bulan' => $bulan,
            'namaBulan' => \Carbon\Carbon::parse($bulan . '-01')->locale('id')->translatedFormat('F Y'),
            'count' => $items->count(),
            'items' => $items,
        ]);
    }

    /** Cetak Berita Acara gabungan sebulan (hanya entri DONE/selesai). */
    public function opnamePrintBulan(Request $request)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Opname.');
        }
        $bulan = (string) $request->get('bulan', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            $bulan = now()->format('Y-m');
        }
        [$tahunBulan, $bulanAngka] = explode('-', $bulan);
        $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->locale('id')->translatedFormat('F Y');
        $moves = StockMove::with(['product', 'user', 'validator'])
            ->where('type', StockMove::TYPE_ADJUSTMENT)
            ->where('status', StockMove::STATUS_DONE)
            ->where('reason', 'like', 'Opname:%')
            ->whereYear('created_at', $tahunBulan)
            ->whereMonth('created_at', $bulanAngka)
            ->orderBy('id')->get();
        if ($moves->isEmpty()) {
            abort(404, 'Belum ada opname selesai pada bulan ' . $namaBulan . '.');
        }
        $items = $moves->map(function ($m) {
            $parsed = self::parseOpnameReason($m->reason);

            return [
                'produk' => ($m->product->product_code ?? '-') . ' — ' . ($m->product->product_name ?? '-'),
                'tercatat' => $parsed['tercatat'],
                'terhitung' => $parsed['terhitung'],
                'selisih' => ($parsed['tercatat'] !== null && $parsed['terhitung'] !== null)
                    ? round($parsed['terhitung'] - $parsed['tercatat'], 2) : null,
                'catatan' => $parsed['catatan'] ?: '-',
                'dok' => $m->doc_no ?? '-',
                'oleh' => $m->user->name ?? '-',
            ];
        // Lampiran BA: hanya yang ADA selisih. Yang sesuai tidak dilampirkan.
        })->filter(fn ($it) => $it['selisih'] !== null && abs($it['selisih']) >= 0.005)->values();
        if ($items->isEmpty()) {
            abort(404, 'Tidak ada selisih opname pada bulan ' . $namaBulan . ' — semua sesuai.');
        }
        $pembuat = null;
        $pemeriksa = null;
        foreach ($moves->reverse() as $m) {
            $p = self::parseOpnameReason($m->reason);
            if ($pembuat === null && ! empty($p['pembuat'])) {
                $pembuat = $p['pembuat'];
            }
            if ($pemeriksa === null && ! empty($p['pemeriksa'])) {
                $pemeriksa = $p['pemeriksa'];
            }
            if ($pembuat !== null && $pemeriksa !== null) {
                break;
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.warehouse.opname-ba-bulan', [
            'bulan' => $bulan,
            'namaBulan' => $namaBulan,
            'hari' => now()->translatedFormat('j'),
            'bulanTahun' => now()->locale('id')->translatedFormat('F Y'),
            'items' => $items,
            'pembuat' => $pembuat,
            'pemeriksa' => $pemeriksa,
            'printedBy' => Auth::user()->name ?? 'System',
        ])->setPaper('a4', 'portrait');

        return $pdf->download('BA_Opname_' . $bulan . '.pdf');
    }

    /** Cetak Berita Acara stock opname per baris penyesuaian (PDF). */
    public function opnamePrint(StockMove $move)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Opname.');
        }
        if ($move->type !== StockMove::TYPE_ADJUSTMENT || ! str_starts_with((string) $move->reason, 'Opname:')) {
            abort(404, 'Bukan dokumen stock opname.');
        }
        // BA terbit hanya untuk yang SELESAI (draf belum ubah stok resmi).
        if ($move->status !== StockMove::STATUS_DONE) {
            abort(403, 'Berita Acara tersedia setelah opname diselesaikan (status masih draf).');
        }
        $move->load(['product', 'user', 'validator']);
        $parsed = self::parseOpnameReason($move->reason);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.warehouse.opname-ba', [
            'move' => $move,
            'tercatat' => $parsed['tercatat'],
            'terhitung' => $parsed['terhitung'],
            'catatan' => $parsed['catatan'],
            'selisih' => ($parsed['tercatat'] !== null && $parsed['terhitung'] !== null)
                ? round($parsed['terhitung'] - $parsed['tercatat'], 2)
                : null,
            'pembuat' => $parsed['pembuat'],
            'pemeriksa' => $parsed['pemeriksa'],
            'printedBy' => Auth::user()->name ?? 'System',
        ])->setPaper('a4', 'portrait');

        return $pdf->download('BA_Opname_' . ($move->doc_no ? str_replace(['/', ' '], '-', $move->doc_no) : $move->id) . '.pdf');
    }

    /** Batas toleransi wajar angka tercatat. */
    public static function toleranceFor(float $recorded): float
    {
        return round(max(self::TOL_ABS, abs($recorded) * self::TOL_PCT), 2);
    }

    /** Urai "Opname: tercatat X, terhitung Y[; Catatan: ...][; BA oleh: A; Diperiksa: B]". */
    public static function parseOpnameReason(?string $reason): array
    {
        $out = ['tercatat' => null, 'terhitung' => null, 'catatan' => null, 'pembuat' => null, 'pemeriksa' => null];
        if (! is_string($reason) || ! str_starts_with($reason, 'Opname:')) {
            return $out;
        }
        if (preg_match('/tercatat\s+(-?[\d.,]+)/i', $reason, $m)) {
            $out['tercatat'] = (float) preg_replace('/[^0-9.\-]/', '', $m[1]);
        }
        if (preg_match('/terhitung\s+(-?[\d.,]+)/i', $reason, $m)) {
            $out['terhitung'] = (float) preg_replace('/[^0-9.\-]/', '', $m[1]);
        }
        if (preg_match('/;\s*BA oleh:\s*(.+?)(?:\s*;\s*Diperiksa:.*)?\s*$/s', $reason, $m)) {
            $out['pembuat'] = trim($m[1]) ?: null;
        }
        if (preg_match('/;\s*Diperiksa:\s*(.+?)\s*$/s', $reason, $m)) {
            $out['pemeriksa'] = trim($m[1]) ?: null;
        }
        if (preg_match('/; Catatan:\s*(.+?)(?:\s*;\s*BA oleh:.*)?\s*$/s', $reason, $m)) {
            $out['catatan'] = trim($m[1]) ?: null;
        }

        return $out;
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

        // Seluruh divisi gudang: full akses baca.
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

        // Divisi gudang: full akses tanpa memandang jabatan.
        $isDivisiGudang = in_array($divisi, ['finance dan gudang', 'fianance dan gudang'], true)
            || \Illuminate\Support\Str::contains($divisi, 'gudang')
            || \Illuminate\Support\Str::contains($jabatan, 'gudang');

        return \Illuminate\Support\Str::contains($jabatan, 'test')
            || \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang'
            || \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing')
            || $isDivisiGudang;
    }

}
