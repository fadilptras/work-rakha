<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Models\User;
use App\Models\WsMutation;
use App\Services\StockOpnameService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Induk controller Warehouse: guard akses + helper kartu stok.
 */
class WsBaseController extends Controller
{
    /** Akses penuh: direksi, kepala divisi MO, admin support. */
    protected function hasFullSalesAccess()
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (User::isSuperUserEmail($user->email ?? '')) {
            return true;
        }

        $jabatan = strtolower($user->jabatan ?? '');
        $divisi = strtolower($user->divisi ?? '');

        $isTopManagement = Str::contains($jabatan, 'direktur') || $divisi === 'top management';
        $isKepalaDivisiMO = (($user->is_kepala_divisi == 1) || Str::contains($jabatan, 'kepala')) && in_array($divisi, ['marketing dan operasional']);
        $isAdminMarketing = Str::contains($jabatan, 'admin support');

        return $isTopManagement || $isKepalaDivisiMO || $isAdminMarketing;
    }

    /** Akses baca: marketing, operasional, gudang. */
    protected function hasAnySalesAccess()
    {
        if ($this->hasFullSalesAccess()) {
            return true;
        }

        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (User::isSuperUserEmail($user->email ?? '')) {
            return true;
        }

        // Admin selalu lolos.
        if ($user->role === 'admin') {
            return true;
        }

        $divisi = strtolower($user->divisi ?? '');
        $jabatan = strtolower($user->jabatan ?? '');

        $isAdminGudang = Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang';
        $isLegalPurchasing = Str::contains($jabatan, 'legal & purchasing') || Str::contains($jabatan, 'purchasing');

        return in_array($divisi, ['marketing dan operasional', 'finance dan gudang', 'fianance dan gudang']) || $isAdminGudang || $isLegalPurchasing
            || Str::contains($divisi, 'gudang');
    }

    /** Akses tulis stok: admin gudang, purchasing, divisi gudang. */
    public function canManageStock(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (User::isSuperUserEmail($user->email ?? '')) {
            return true;
        }

        // Admin selalu lolos.
        if ($user->role === 'admin') {
            return true;
        }

        $jabatan = strtolower($user->jabatan ?? '');
        $divisi = strtolower($user->divisi ?? '');

        $isTest = Str::contains($jabatan, 'test');
        $isAdminGudang = Str::contains($jabatan, 'admin gudang') || $jabatan == 'gudang';
        $isLegalPurchasing = Str::contains($jabatan, 'legal & purchasing');
        // Divisi gudang: full akses.
        $isDivisiGudang = in_array($divisi, ['finance dan gudang', 'fianance dan gudang'], true)
            || Str::contains($divisi, 'gudang');

        return $isAdminGudang || $isLegalPurchasing
        || $isTest || $isDivisiGudang;
    }

    /**
     * Opsi ekspedisi: rekomendasi tetap di depan + riwayat DB (unik, abaikan duplikat kapital).
     */
    protected function expeditionOptions(): array
    {
        $db = [];
        if (Schema::hasTable('stock_moves')) {
            $db = StockMove::whereNotNull('expedition')->where('expedition', '!=', '')
                ->distinct()->orderBy('expedition')->pluck('expedition')->all();
        }

        return collect(['Rakha Ekspedisi', 'JNE', 'J&T', 'SiCepat', 'GoSend', 'GrabExpress', 'AnterAja', 'Pos Indonesia', 'TIKI', 'Wahana', 'Paxel'])
            ->merge($db)->unique(fn ($v) => strtolower(trim((string) $v)))->values()->all();
    }

    /** Opsi nama PS (Product Specialist / Marketing) untuk barang keluar Sample. */
    protected function psOptions(): array
    {
        try {
            return User::whereNotNull('name')->where('name', '!=', '')
                ->where(function ($q) {
                    $q->where('jabatan', 'like', '%marketing%')
                        ->orWhere('jabatan', 'like', '%product spea%')
                        ->orWhere('jabatan', 'like', '%product spec%');
                })
                ->orderBy('name')->pluck('name')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Sinkronkan status dokumen ws_mutation setelah baris dibatalkan/diedit.
     * Dokumen tanpa baris aktif menjadi void dan hilang dari arsip.
     */
    protected function refreshMutationStatus(?StockMove $move): void
    {
        if (! $move || $move->ref_type !== 'ws_mutation' || ! $move->ref_id) {
            return;
        }

        $doc = WsMutation::find($move->ref_id);
        if (! $doc) {
            return;
        }

        $hasActive = StockMove::where('ref_type', 'ws_mutation')
            ->where('ref_id', $doc->id)
            ->where('status', '!=', StockMove::STATUS_CANCELLED)
            ->exists();

        $doc->update([
            'status' => $hasActive ? WsMutation::STATUS_POSTED : WsMutation::STATUS_VOID,
        ]);
    }

    /** Net mutasi done antara dua waktu (eksklusif awal, eksklusif akhir). */
    protected function netMovesBetween(int $productId, $fromAt, string $to): float
    {
        $fromAt = $fromAt ?: Carbon::parse($to)->startOfDay();
        $toAt = Carbon::parse($to)->startOfDay()->toDateTimeString();

        $net = DB::table('stock_moves')
            ->where('product_id', $productId)
            ->where('status', 'done')
            ->where(fn ($q) => $q->whereNull('ref_type')->orWhere('ref_type', '!=', 'stock_move_reversal'))
            ->where(fn ($q) => $q->whereNull('reason')->orWhere('reason', 'not like', '%[dibatalkan]%'))
            ->where('created_at', '>', $fromAt)
            ->where('created_at', '<', $toAt)
            ->selectRaw(StockOpnameService::signedNetSql().' as net')
            ->value('net');

        return round((float) ($net ?? 0), 2);
    }

    /**
     * Resolusi stok awal produk dari hasil opname fisik bulan berjalan/sebelumnya,
     * atau mutasi awal pendaftaran produk.
     * SAMA PERSIS dengan acuan di Monitoring Product & Data Product.
     *
     * @param  array<int>  $productIds
     * @return array<int, array{stok_awal: float, has_opname: bool, is_opnamed_this_month: bool}>
     */
    public function resolveStockAwalMap(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $currentPeriod = now()->format('Y-m');

        // 1. Opname selesai bulan berjalan
        $opnameDoneMap = StockOpname::where('period', $currentPeriod)
            ->where('status', StockOpname::STATUS_DONE)
            ->whereIn('product_id', $productIds)
            ->orderByDesc('opname_at')
            ->orderByDesc('opname_date')
            ->orderByDesc('id')
            ->get(['product_id', 'counted_qty', 'note', 'revision'])
            ->unique('product_id')
            ->keyBy('product_id');

        // 2. Opname selesai bulan-bulan sebelumnya (nilai fisik opname terakhir, abaikan fake opening opname)
        $pastOpnameMap = StockOpname::where('period', '<', $currentPeriod)
            ->where('status', StockOpname::STATUS_DONE)
            ->whereIn('product_id', $productIds)
            ->where(function ($q) {
                $q->whereNull('note')
                    ->orWhere('note', 'not like', '%stok awal%')
                    ->orWhere('revision', '>', 1);
            })
            ->orderByDesc('opname_at')
            ->orderByDesc('opname_date')
            ->orderByDesc('period')
            ->orderByDesc('id')
            ->get(['product_id', 'counted_qty', 'period', 'note', 'revision'])
            ->unique('product_id')
            ->keyBy('product_id');

        // 3. Move stok awal pendaftaran produk
        $initialStockMap = DB::table('stock_moves')
            ->whereIn('product_id', $productIds)
            ->where('status', StockMove::STATUS_DONE)
            ->where(function ($q) {
                $q->where('ref_type', 'master_stock')
                    ->orWhere('reason', 'like', 'Stok awal%');
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['product_id', 'qty'])
            ->unique('product_id')
            ->pluck('qty', 'product_id');

        $opnamedMoveProductIds = DB::table('stock_moves')
            ->whereIn('product_id', $productIds)
            ->where('reason', 'like', 'Opname%')
            ->pluck('product_id')
            ->flip()
            ->toArray();

        $result = [];
        foreach ($productIds as $pid) {
            if (isset($opnameDoneMap[$pid])) {
                $op = $opnameDoneMap[$pid];
                $isActual = empty($op->note) || stripos($op->note, 'stok awal') === false || (int) ($op->revision ?? 1) > 1;
                if ($isActual) {
                    $result[$pid] = [
                        'stok_awal' => (float) $op->counted_qty,
                        'has_opname' => true,
                        'is_opnamed_this_month' => true,
                    ];
                } else {
                    $result[$pid] = [
                        'stok_awal' => (float) ($initialStockMap[$pid] ?? $op->counted_qty),
                        'has_opname' => false,
                        'is_opnamed_this_month' => false,
                    ];
                }
            } elseif (isset($pastOpnameMap[$pid])) {
                $result[$pid] = [
                    'stok_awal' => (float) $pastOpnameMap[$pid]->counted_qty,
                    'has_opname' => true,
                    'is_opnamed_this_month' => false,
                ];
            } else {
                $result[$pid] = [
                    'stok_awal' => (float) ($initialStockMap[$pid] ?? 0.0),
                    'has_opname' => isset($opnamedMoveProductIds[$pid]),
                    'is_opnamed_this_month' => false,
                ];
            }
        }

        return $result;
    }

    /**
     * Resolusi stok awal / opname untuk satu produk.
     */
    public function resolveStockAwalSingle(int $productId): array
    {
        $map = $this->resolveStockAwalMap([$productId]);

        return $map[$productId] ?? [
            'stok_awal' => 0.0,
            'has_opname' => false,
            'is_opnamed_this_month' => false,
        ];
    }

    /**
     * Bangun data Kartu Stock satu produk (from s.d. to).
     * Terintegrasi dengan riwayat Stock Opname fisik berkala di setiap bulan:
     * - Default menampilkan SELURUH tanggal di tahun berjalan (01 Jan s/d 31 Des).
     * - Baris Opname fisik / Stok Awal di setiap bulan memiliki style baris tersendiri
     *   (seperti baris pertama Kartu Stock) dan me-reset sisa ke hasil hitung fisik.
     * - Rumus saldo berjalan presisi: Masuk DITAMBAH (+), Keluar DIKURANG (-).
     */
    public function buildCardData(Request $request, Product $product): array
    {
        $rawFrom = (string) $request->get('from', '');
        $rawTo = (string) $request->get('to', '');

        try {
            $fromParsed = ! empty($rawFrom) ? Carbon::parse($rawFrom) : now()->startOfYear();
        } catch (\Throwable $e) {
            $fromParsed = now()->startOfYear();
        }

        try {
            $toParsed = ! empty($rawTo) ? Carbon::parse($rawTo) : now()->endOfYear();
        } catch (\Throwable $e) {
            $toParsed = now()->endOfYear();
        }

        // Jika user salah input (Dari lebih besar dari Sampai), balik otomatis agar tidak error
        if ($fromParsed->gt($toParsed)) {
            [$fromParsed, $toParsed] = [$toParsed, $fromParsed];
        }

        $from = $fromParsed->toDateString();
        $to = $toParsed->toDateString();
        $fromStart = $fromParsed->copy()->startOfDay();
        $toEnd = $toParsed->copy()->endOfDay();
        $fromMonth = substr($from, 0, 7);
        $toMonth = substr($to, 0, 7);

        $hasBatchCol = Schema::hasColumn('stock_moves', 'batch_no');

        $saldoAwal = 0;
        $saldoLabel = 'Stock awal';
        $saldoSubtext = null;
        $saldoBadge = null;
        $saldoLabelPdf = null;
        $tanpaOpname = true;
        $opnameMoves = collect();

        // 1. Hitung Saldo Awal per awal periode $from (sebelum $fromStart)
        // Sesuai Aturan Baru: nilai stok awal selalu mengacu pada angka fisik hasil
        // stock opname terakhir yang valid (atau initial stock awal produk jika belum pernah di-opname sama sekali),
        // BUKAN nilai akumulasi mutasi setelah opname.
        if (Schema::hasTable('stock_opnames')) {
            // Ambil semua opname resmi selesai untuk produk ini (bukan rekaman stok awal)
            $allProductOpnames = StockOpname::where('product_id', $product->id)
                ->where('status', StockOpname::STATUS_DONE)
                ->where(function ($q) {
                    $q->whereNull('note')->orWhere('note', 'not like', '%stok awal%');
                })
                ->orderByDesc('opname_at')
                ->orderByDesc('opname_date')
                ->orderByDesc('id')
                ->get();

            // Cari opname terakhir SEBELUM awal periode ($fromStart)
            $lastOpnameBefore = $allProductOpnames->first(function ($o) use ($fromStart, $fromMonth) {
                if (! empty($o->period) && strcmp($o->period, $fromMonth) < 0) {
                    return true;
                }
                $opTime = $o->opname_at
                    ? Carbon::parse($o->opname_at)
                    : ($o->opname_date ? Carbon::parse($o->opname_date)->endOfDay() : Carbon::parse($o->period . '-01')->endOfMonth()->endOfDay());
                return $opTime->lt($fromStart);
            });

            if ($lastOpnameBefore) {
                // Murni angka fisik hasil stock opname terakhir yang valid (tanpa menambah net mutasi)
                $saldoAwal = round((float) $lastOpnameBefore->counted_qty, 2);
                $opnameDateStr = $lastOpnameBefore->opname_date?->locale('id')->translatedFormat('d M Y') 
                    ?? Carbon::parse($lastOpnameBefore->period . '-01')->locale('id')->translatedFormat('F Y');
                $saldoLabel = 'Saldo Awal Periode';
                $saldoBadge = 'Opname Terakhir: ' . $opnameDateStr;
                $saldoSubtext = 'Hasil fisik opname murni: ' . number_format((float) $lastOpnameBefore->counted_qty, 0, ',', '.') . ' (' . $opnameDateStr . ')';
                $saldoLabelPdf = $saldoLabel . ' (' . $saldoBadge . ')';
                $tanpaOpname = false;
                $initialMoveIdToSkip = null;
            } else {
                // Belum ada opname resmi sebelum periode ini. Jadikan Stok Awal Pendaftaran sebagai baris teratas kartu stok!
                $initialMove = DB::table('stock_moves')
                    ->where('product_id', $product->id)
                    ->where('status', 'done')
                    ->where(function ($q) {
                        $q->where('ref_type', 'master_stock')
                            ->orWhere('reason', 'like', 'Stok awal%');
                    })
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->first();

                if ($initialMove) {
                    $saldoAwal = round((float) $initialMove->qty, 2);
                    $initDate = Carbon::parse($initialMove->created_at)->locale('id')->translatedFormat('d M Y');
                    $saldoLabel = 'Stok Awal Produk';
                    $saldoBadge = 'Pendaftaran: ' . $initDate;
                    $saldoSubtext = 'Stok awal pendaftaran produk (' . number_format($saldoAwal, 0, ',', '.') . ' ' . ($product->unit ?: 'Pcs') . ')';
                    $saldoLabelPdf = $saldoLabel . ' (' . $saldoBadge . ')';
                    $initialMoveIdToSkip = $initialMove->id;
                } else {
                    $saldoAwal = 0;
                    $saldoLabel = 'Stok Awal Produk';
                    $saldoBadge = 'Pendaftaran Produk';
                    $saldoSubtext = 'Produk didaftarkan tanpa stok awal (0 ' . ($product->unit ?: 'Pcs') . ')';
                    $saldoLabelPdf = $saldoLabel;
                    $initialMoveIdToSkip = null;
                }
                $tanpaOpname = true;
            }

            $opnameMoves = $allProductOpnames->take(12);

            // 2. Ambil seluruh Stock Opname SELESAI di DALAM rentang waktu ($fromStart s.d. $toEnd)
            $opnamesInPeriod = $allProductOpnames->filter(function ($o) use ($fromStart, $toEnd) {
                $opTime = $o->opname_at
                    ? Carbon::parse($o->opname_at)
                    : ($o->opname_date ? Carbon::parse($o->opname_date)->endOfDay() : Carbon::parse($o->period . '-01')->endOfMonth()->endOfDay());
                return $opTime->gte($fromStart) && $opTime->lte($toEnd);
            })->values();

            $opnameMoveIds = $opnamesInPeriod->pluck('stock_move_id')->filter()->all();
        } else {
            $saldoAwal = 0;
            $saldoLabel = 'Stok Awal Produk';
            $saldoBadge = 'Pendaftaran Produk';
            $opnamesInPeriod = collect();
            $opnameMoveIds = [];
            $initialMoveIdToSkip = null;
        }

        // 3. Ambil mutasi stock reguler di dalam periode ($from s.d. $to)
        // KECUALIKAN mutasi adjustment opname dan mutasi stok awal pendaftaran yang sudah menjadi saldo awal teratas
        $moves = collect();
        if (Schema::hasTable('stock_moves')) {
            $moves = DB::table('stock_moves as m')
                ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
                ->leftJoin('ws_mutation as wm', function ($j) {
                    $j->on('wm.id', '=', 'm.ref_id')
                        ->where('m.ref_type', '=', 'ws_mutation');
                })
                ->leftJoin('customers as c', 'c.id', '=', 'wm.customer_id')
                ->leftJoin('suppliers as s', 's.id', '=', 'wm.supplier_id')
                ->where('m.product_id', $product->id)
                ->where('m.status', 'done')
                ->where(fn ($q) => $q->whereNull('m.ref_type')->orWhere('m.ref_type', '!=', 'stock_move_reversal'))
                ->where(fn ($q) => $q->whereNull('m.reason')->orWhere('m.reason', 'not like', '%[dibatalkan]%'))
                ->when(! empty($opnameMoveIds), fn ($q) => $q->whereNotIn('m.id', $opnameMoveIds))
                ->when($initialMoveIdToSkip, fn ($q) => $q->where('m.id', '!=', $initialMoveIdToSkip))
                ->where(fn ($q) => $q->whereNull('m.reason')->orWhere('m.reason', 'not like', 'Opname%'))
                ->whereDate('m.created_at', '>=', $from)
                ->whereDate('m.created_at', '<=', $to)
                ->orderBy('m.created_at')->orderBy('m.id')
                ->get([
                    'm.id', 'm.qty', 'm.type', 'm.from_location', 'm.to_location',
                    'm.reason', 'm.ref_type', 'm.ref_id', 'm.expiry_date', 'm.created_at',
                    DB::raw('COALESCE(NULLIF(TRIM(m.doc_no), ""), NULLIF(TRIM(wm.doc_no), "")) as doc_no'),
                    DB::raw('COALESCE(NULLIF(TRIM(m.partner), ""), NULLIF(TRIM(c.customer_name), ""), NULLIF(TRIM(s.supplier_name), "")) as partner'),
                    DB::raw('COALESCE(NULLIF(TRIM(m.purpose), ""), NULLIF(TRIM(wm.purpose), "")) as purpose'),
                    DB::raw('COALESCE(NULLIF(TRIM(m.po_no), ""), NULLIF(TRIM(wm.po_no), "")) as po_no'),
                    'u.name as user_name',
                    ...($hasBatchCol ? ['m.batch_no'] : []),
                ]);
        }

        // 4. Susun Timeline terpadu (Mutasi + Opname Bulanan / Stok Awal) secara kronologis
        $timeline = collect();

        foreach ($moves as $m) {
            $isPerubahanAwal = ($m->ref_type === 'master_stock_edit') || str_starts_with((string) $m->reason, 'Perubahan Stok Awal');
            $isStokAwalBaru = ($m->ref_type === 'master_stock') || str_starts_with((string) $m->reason, 'Stok awal');

            $timeline->push([
                'item_type' => $isPerubahanAwal ? 'stok_awal_edit' : ($isStokAwalBaru ? 'stok_awal_baru' : 'move'),
                'timestamp' => Carbon::parse($m->created_at),
                'order' => 1,
                'data' => $m,
            ]);
        }

        foreach ($opnamesInPeriod as $o) {
            $isOpeningOpname = stripos((string) $o->note, 'stok awal') !== false;
            // Jika opname adalah pembukaan stok awal produk dan sudah diwakili oleh mutasi master_stock, hindari duplikasi
            if ($isOpeningOpname && $moves->contains(fn ($mv) => ($mv->ref_type === 'master_stock' || $mv->ref_type === 'master_stock_edit'))) {
                continue;
            }

            $opTime = $o->opname_at
                ? Carbon::parse($o->opname_at)
                : ($o->opname_date ? Carbon::parse($o->opname_date)->endOfDay() : Carbon::parse($o->period . '-01')->endOfMonth()->endOfDay());

            $timeline->push([
                'item_type' => 'opname',
                'timestamp' => $opTime,
                'order' => 2, // Dieksekusi setelah transaksi di hari yang sama
                'data' => $o,
            ]);
        }

        // Urutkan berdasarkan waktu kronologis
        $sortedTimeline = $timeline->sort(function ($a, $b) {
            $cmp = $a['timestamp']->getTimestamp() <=> $b['timestamp']->getTimestamp();
            if ($cmp !== 0) {
                return $cmp;
            }
            return ($a['order'] ?? 1) <=> ($b['order'] ?? 1);
        });

        // 5. Hitung saldo berjalan secara presisi (+ masuk, - keluar, reset pada opname/stok awal)
        $rows = [];
        $sisa = (float) $saldoAwal;

        foreach ($sortedTimeline as $item) {
            $type = $item['item_type'];
            $timestamp = $item['timestamp'];

            if ($type === 'opname') {
                $o = $item['data'];
                $counted = round((float) $o->counted_qty, 2);
                $recorded = round((float) $o->recorded_qty, 2);
                $diff = round((float) $o->diff_qty, 2);
                $diffStr = $diff > 0 ? "+{$diff}" : "{$diff}";
                $namaBulan = Carbon::parse($o->period . '-01')->locale('id')->translatedFormat('F Y');

                $recQtyFmt = number_format($recorded, 0, ',', '.');
                $countQtyFmt = number_format($counted, 0, ',', '.');
                $detailAngka = "Tercatat: {$recQtyFmt} → Terhitung Fisik: {$countQtyFmt} (Selisih {$diffStr})";

                if ((int) $o->revision > 1 || ($o->updated_at && $o->created_at && $o->updated_at->gt($o->created_at->addMinutes(1)))) {
                    $updateDateStr = $o->updated_at ? $o->updated_at->locale('id')->translatedFormat('d M Y H:i') : '';
                    $revBadge = (int) $o->revision > 1 ? " (Revisi #{$o->revision})" : '';
                    $subtext = "Diperbarui pada {$updateDateStr}{$revBadge} — {$detailAngka}";
                } else {
                    $subtext = "Hasil Opname Fisik: {$detailAngka}";
                }

                if (! empty($o->note) && $o->note !== '-' && ! str_starts_with($o->note, 'Selisih')) {
                    $subtext .= " · Catatan: {$o->note}";
                }

                // SALDO DIRESET KE HASIL HITUNG FISIK OPNAME
                $sisa = $counted;

                $rows[] = [
                    'is_saldo_row' => true,
                    'kind' => 'opname',
                    'timestamp' => $timestamp,
                    'tanggal_display' => $timestamp->locale('id')->translatedFormat('d M Y H:i'),
                    'tanggal_date' => $timestamp->format('Y-m-d'),
                    'batch_no' => $o->ba_number ?? '-',
                    'label' => 'Hasil Opname Fisik — Bulan ' . $namaBulan,
                    'badge' => 'Saldo Direset dari Opname Fisik' . ((int) $o->revision > 1 ? " (Rev #{$o->revision})" : ''),
                    'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                    'subtext' => $subtext,
                    'masuk' => 0,
                    'keluar' => 0,
                    'sisa' => $sisa,
                    'keterangan' => 'Hasil Opname Fisik — Bulan ' . $namaBulan . ' (' . $detailAngka . ')',
                    'opname' => $o,
                ];
            } elseif ($type === 'stok_awal_edit') {
                $m = $item['data'];
                $isIn = $m->type === 'receipt'
                    || ($m->type === 'adjustment' && $m->to_location === 'Stock' && $m->from_location !== 'Stock');
                $isTransfer = $m->type === 'transfer';
                $masuk = ($isIn && ! $isTransfer) ? (float) $m->qty : 0;
                $keluar = (! $isIn && ! $isTransfer) ? (float) $m->qty : 0;
                $sisa = round($sisa + $masuk - $keluar, 2);

                $rows[] = [
                    'is_saldo_row' => true,
                    'kind' => 'stok_awal',
                    'timestamp' => $timestamp,
                    'tanggal_display' => $timestamp->locale('id')->translatedFormat('d M Y H:i'),
                    'tanggal_date' => $timestamp->format('Y-m-d'),
                    'batch_no' => trim((string) ($m->batch_no ?? '')) !== '' ? $m->batch_no : '-',
                    'label' => 'Perubahan Stok Awal Produk',
                    'badge' => 'Stok Awal Diperbarui',
                    'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                    'subtext' => $m->reason,
                    'masuk' => 0,
                    'keluar' => 0,
                    'sisa' => $sisa,
                    'keterangan' => $m->reason,
                    'move' => $m,
                ];
            } elseif ($type === 'stok_awal_baru') {
                $m = $item['data'];
                $masuk = (float) $m->qty;
                $keluar = 0;
                $sisa = round($sisa + $masuk, 2);

                $rows[] = [
                    'is_saldo_row' => true,
                    'kind' => 'stok_awal',
                    'timestamp' => $timestamp,
                    'tanggal_display' => $timestamp->locale('id')->translatedFormat('d M Y H:i'),
                    'tanggal_date' => $timestamp->format('Y-m-d'),
                    'batch_no' => trim((string) ($m->batch_no ?? '')) !== '' ? $m->batch_no : '-',
                    'label' => 'Stok Awal Produk Baru',
                    'badge' => 'Pendaftaran Produk',
                    'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200/80',
                    'subtext' => 'Pencatatan stok awal saat produk baru didaftarkan ke sistem',
                    'masuk' => 0,
                    'keluar' => 0,
                    'sisa' => $sisa,
                    'keterangan' => 'Stok awal pendaftaran produk (' . number_format($masuk, 0, ',', '.') . ' ' . ($product->unit ?: 'Pcs') . ')',
                    'move' => $m,
                ];
            } else {
                // Transaksi reguler (Penerimaan, Pengeluaran, Sample, Produksi, Mutasi)
                $m = $item['data'];
                $isIn = $m->type === 'receipt'
                    || ($m->type === 'adjustment' && $m->to_location === 'Stock' && $m->from_location !== 'Stock');
                $isTransfer = $m->type === 'transfer';
                $masuk = ($isIn && ! $isTransfer) ? (float) $m->qty : 0;
                $keluar = (! $isIn && ! $isTransfer) ? (float) $m->qty : 0;

                // STRICT CALCULATION: Masuk DITAMBAH (+), Keluar DIKURANG (-)
                $sisa = round($sisa + $masuk - $keluar, 2);

                $rows[] = [
                    'is_saldo_row' => false,
                    'kind' => 'mutasi',
                    'move' => $m,
                    'timestamp' => $timestamp,
                    'tanggal_display' => $timestamp->locale('id')->translatedFormat('d M Y H:i'),
                    'tanggal_date' => $timestamp->format('Y-m-d'),
                    'batch_no' => trim((string) ($m->batch_no ?? '')) !== '' ? $m->batch_no : '-',
                    'masuk' => $masuk,
                    'keluar' => $keluar,
                    'sisa' => $sisa,
                    'keterangan' => self::formatKartuKeterangan($m),
                ];
            }
        }

        $stockAwalInfo = $this->resolveStockAwalSingle($product->id);
        $stockAwalOpname = (float) ($stockAwalInfo['stok_awal'] ?? 0.0);

        return [
            'product' => $product,
            'rows' => $rows,
            'saldoAwal' => $saldoAwal,
            'stockAwalOpname' => $stockAwalOpname,
            'stockAwalInfo' => $stockAwalInfo,
            'saldoLabel' => $saldoLabel,
            'saldoBadge' => $saldoBadge,
            'saldoSubtext' => $saldoSubtext ?? null,
            'saldoLabelPdf' => $saldoLabelPdf ?? $saldoLabel,
            'tanpaOpname' => $tanpaOpname,
            'from' => $from,
            'to' => $to,
            'opnameMoves' => $opnameMoves,
        ];
    }

    /**
     * Format keterangan kartu stock agar jelas (Penjualan ke Customer, Sample untuk PS,
     * Barang Masuk dari Supplier) dan format tanggal tgl/bln/tahun (d/m/Y).
     */
    public static function formatKartuKeterangan(object $m): string
    {
        $reason = trim((string) ($m->reason ?? ''));

        // Opname & Stok Awal
        if (str_starts_with($reason, 'Opname') || str_starts_with($reason, 'Perubahan Stok Awal') || str_starts_with($reason, 'Stok awal')) {
            return $reason;
        }

        // Reversal / koreksi baris: ubah tanggal YYYY-MM-DD ke DD/MM/YYYY
        if (str_starts_with($reason, 'Hapus baris #') || str_starts_with($reason, 'Koreksi baris #') || str_starts_with($reason, 'Reversal #')) {
            return preg_replace_callback('/(\d{4})-(\d{2})-(\d{2})/', function ($matches) {
                return $matches[3].'/'.$matches[2].'/'.$matches[1];
            }, $reason);
        }

        $purpose = trim((string) ($m->purpose ?? ''));
        $partner = trim((string) ($m->partner ?? ''));
        $docNo = trim((string) ($m->doc_no ?? ''));

        // Cek jika reason memiliki awalan [docNo]
        if ($docNo === '' && preg_match('/^\[(.*?)\]/', $reason, $dMatch)) {
            $docNo = trim($dMatch[1]);
        }

        // Ambil tanggal format d/m/Y
        $tglFmt = '';
        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $reason, $tMatch)) {
            $tglFmt = $tMatch[3].'/'.$tMatch[2].'/'.$tMatch[1];
        } elseif (! empty($m->created_at)) {
            try {
                $tglFmt = Carbon::parse($m->created_at)->format('d/m/Y');
            } catch (\Throwable $e) {}
        }

        $isGeneric = preg_match('/^(\[.*?\]\s*)?Pencatatan\s+(masuk|keluar)\s+\d{4}-\d{2}-\d{2}$/i', $reason)
            || preg_match('/^(\[.*?\]\s*)?Pencatatan\s+(masuk|keluar)\s*$/i', $reason)
            || empty($reason);

        if ($isGeneric || ! empty($partner) || ! empty($purpose)) {
            $isIn = ($m->type ?? '') === 'receipt'
                || (($m->type ?? '') === 'adjustment' && ($m->to_location ?? '') === 'Stock');

            $purpLower = strtolower($purpose);
            if ($isIn) {
                if ($purpLower === 'produksi') {
                    $desc = 'Produksi'.($partner !== '' ? " ({$partner})" : '');
                } elseif ($purpLower === 'pembelian') {
                    $desc = 'Pembelian'.($partner !== '' ? " dari {$partner}" : '');
                } elseif ($purpose !== '') {
                    $desc = $purpose.($partner !== '' ? " dari {$partner}" : '');
                } else {
                    $desc = 'Barang Masuk'.($partner !== '' ? " dari {$partner}" : '');
                }
            } else {
                if ($purpLower === 'sample' || $purpLower === 'sampel') {
                    $desc = 'Sample'.($partner !== '' ? " untuk {$partner}" : '');
                } elseif ($purpLower === 'penjualan' || $purpLower === 'sale') {
                    $desc = 'Penjualan'.($partner !== '' ? " ke {$partner}" : '');
                } elseif (str_contains($purpLower, 'retur')) {
                    $desc = 'Retur Barang'.($partner !== '' ? " ke {$partner}" : '');
                } elseif ($purpLower === 'internal') {
                    $desc = 'Pemakaian Internal'.($partner !== '' ? " ({$partner})" : '');
                } elseif ($purpose !== '') {
                    $desc = $purpose.($partner !== '' ? " - {$partner}" : '');
                } else {
                    $desc = 'Pengeluaran Barang'.($partner !== '' ? " ke {$partner}" : '');
                }
            }

            // Cek jika ada catatan spesifik dari baris barang (bukan teks bawaan)
            $cleanReason = preg_replace('/^\[[^\]]+\]\s*/', '', (string) $reason);
            $cleanReason = preg_replace('/^Pencatatan (masuk|keluar)\s+\d{4}-\d{2}-\d{2}\s*/i', '', (string) $cleanReason);
            $cleanReason = trim(preg_replace('/^[-–]\s*/', '', (string) $cleanReason));
            $cleanReason = preg_replace('/\s*\(\d{2}\/\d{2}\/\d{4}\)$/', '', (string) $cleanReason);
            $cleanReason = trim((string) $cleanReason);

            if ($cleanReason !== '' && ! str_contains(strtolower($cleanReason), strtolower($desc)) && ! str_contains(strtolower($desc), strtolower($cleanReason))) {
                $desc .= ' - '.$cleanReason;
            }

            if ($tglFmt !== '') {
                $desc .= " ({$tglFmt})";
            }

            if ($docNo !== '') {
                $desc = "[{$docNo}] ".$desc;
            }

            return $desc;
        }

        // Keterangan khusus lain: pastikan format tanggal YYYY-MM-DD dikonversi jadi d/m/Y
        return preg_replace_callback('/(\d{4})-(\d{2})-(\d{2})/', function ($matches) {
            return $matches[3].'/'.$matches[2].'/'.$matches[1];
        }, $reason);
    }
}