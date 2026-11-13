<?php

namespace App\Http\Controllers\Warehouse;

use App\Services\ForecastService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Analitik gudang:
 * - Outflow barang keluar (10 Qty Terbanyak & 10 Qty Terendah dalam Grafik)
 * - Top Pengajuan Barang (SPB) berdasarkan Quantity + DOI
 * - Filter periode: Per Bulan & Per Tahun
 * Route: warehouse.analytics
 */
class WsAnalyticsController extends WsBaseController
{
    /**
     * Rentang DOI eksklusif (hari): [min, max).
     * 30–59, 60–89, 90–119, dan >=120.
     */
    private const DOI_RANGES = [
        30 => [30, 60],
        60 => [60, 90],
        90 => [90, 120],
        120 => [120, null],
    ];

    /** Nama bulan untuk dropdown. */
    private const BULAN = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];

    public function index(Request $request)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Analytics Gudang.');
        }

        $listTahun = $this->availableYears();
        $tahun = (int) $request->get('tahun', now()->year);
        if (! in_array($tahun, $listTahun, true)) {
            $tahun = $listTahun[0] ?? (int) now()->year;
        }

        // Mode periode: 'bulan' (default) atau 'tahun'
        $periode = strtolower(trim((string) $request->get('periode', 'bulan')));
        if (! in_array($periode, ['bulan', 'tahun'], true)) {
            $periode = 'bulan';
        }

        $bulan = (int) $request->get('bulan', now()->month);
        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) now()->month;
        }

        // Jika mode 'tahun', filter bulan adalah null (akumulasi 1 tahun penuh)
        $filterBulan = ($periode === 'tahun') ? null : $bulan;
        $bulanLabel = self::BULAN[$bulan] ?? '-';
        $periodeLabel = ($periode === 'tahun')
            ? "Tahun {$tahun} (Januari – Desember)"
            : "{$bulanLabel} {$tahun}";

        // Ambil data Forecast & DOI dari ForecastService (sumber kebenaran DOI sistem)
        [$stockForecast, , $bulanAktifForecast, , , $activeRefMonths, , $activeDoi] = app(ForecastService::class)->build($tahun);

        $doiMap = [];
        foreach ($stockForecast as $sf) {
            $k = mb_strtolower(trim((string) ($sf['nama_produk'] ?? '')));
            if ($k !== '') {
                $doiMap[$k] = [
                    'doi' => (int) ($sf['doi_qty'] ?? 0),
                    'avg' => (float) ($sf['avg_qty'] ?? 0),
                    'stok' => (float) ($sf['stok_tersedia'] ?? 0),
                    'nama' => $sf['nama_produk'] ?? '',
                    'satuan' => $sf['satuan_stok'] ?? 'Pcs',
                ];
            }
        }

        // 1. Data Outflow Barang Keluar (Top 10 Fast & Under 10 Slow Moving)
        [$topOut, $leastOut, $totalOutQty, $movedProducts, $totalMasterProducts] = $this->buildOutgoing($tahun, $filterBulan);

        // 2. Data Top Pengajuan Barang (SPB) by Quantity + DOI
        [$topSpb, $totalSpbQty, $spbDocCount] = $this->buildTopSpb($tahun, $filterBulan, $doiMap);

        // 3. DOI Buckets & Stats
        [$doiBuckets, $bulanAktif, $tahunAktif, $activeRefMonths, $activeDoi] = $this->buildDoiBucketsWithData(
            $stockForecast,
            $tahun,
            $bulanAktifForecast,
            $activeRefMonths,
            $activeDoi
        );

        // 4. Monthly Trend Outflow (12 bulan)
        $trend = $this->buildMonthlyTrend($tahun);

        // Ringkasan 4 Kategori DOI: >30 (30-59), >60 (60-89), >90 (90-119), dan >120 (>=120)
        $doi30Count = $doiBuckets[30]->count();
        $doi60Count = $doiBuckets[60]->count();
        $doi90Count = $doiBuckets[90]->count();
        $doi120Count = $doiBuckets[120]->count();
        $doiTotalCount = max(1, $doi30Count + $doi60Count + $doi90Count + $doi120Count);

        // Ringkasan rata-rata DOI & produk overstock
        $allDois = collect($stockForecast)->pluck('doi_qty')->filter(fn ($d) => $d !== null && $d > 0);
        $avgDoi = $allDois->count() > 0 ? (int) round($allDois->average()) : 0;
        $overstockDoiCount = $doi120Count;

        return view('users.warehouse.analytics', [
            'title' => 'Analytics Gudang & Outflow',
            'listTahun' => $listTahun,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'periode' => $periode,
            'bulanLabel' => $bulanLabel,
            'periodeLabel' => $periodeLabel,
            'listBulan' => self::BULAN,
            'topOut' => $topOut,
            'leastOut' => $leastOut,
            'totalOutQty' => $totalOutQty,
            'movedProducts' => $movedProducts,
            'totalMasterProducts' => $totalMasterProducts,
            'topSpb' => $topSpb,
            'totalSpbQty' => $totalSpbQty,
            'spbDocCount' => $spbDocCount,
            'doiBuckets' => $doiBuckets,
            'doi30Count' => $doi30Count,
            'doi60Count' => $doi60Count,
            'doi90Count' => $doi90Count,
            'doi120Count' => $doi120Count,
            'doiTotalCount' => $doiTotalCount,
            'bulanAktif' => $bulanAktif,
            'tahunAktif' => $tahunAktif,
            'activeRefMonths' => $activeRefMonths,
            'activeDoi' => $activeDoi,
            'avgDoi' => $avgDoi,
            'overstockDoiCount' => $overstockDoiCount,
            'trend' => $trend,
        ]);
    }

    /**
     * Tahun yang memiliki mutasi stock_moves (plus tahun berjalan).
     *
     * @return array<int>
     */
    private function availableYears(): array
    {
        if (! Schema::hasTable('stock_moves')) {
            return [(int) now()->year];
        }

        $years = DB::table('stock_moves')
            ->whereNotNull('created_at')
            ->selectRaw('DISTINCT YEAR(created_at) as tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn ($y) => (int) $y)
            ->filter()
            ->values()
            ->all();

        if (! in_array((int) now()->year, $years, true)) {
            array_unshift($years, (int) now()->year);
        }

        return $years;
    }

    /**
     * Total keluar per produk pada periode terpilih (bulan tertentu atau 1 tahun penuh).
     * Keluar = qty input dari mutasi delivery/scrap yang sah. Reversal, koreksi
     * opname, dan baris batal tidak dihitung agar angka tidak dobel/melembung.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection, 2: float, 3: int, 4: int}
     */
    private function buildOutgoing(int $tahun, ?int $bulan): array
    {
        if (! Schema::hasTable('stock_moves')) {
            return [collect(), collect(), 0.0, 0, 0];
        }

        $outgoingSql = $this->outgoingCaseSql('m');
        $historyByProduct = $this->outgoingHistoryByProduct($tahun, $bulan);

        $rows = DB::table('products as p')
            ->leftJoin('stock_moves as m', function ($join) use ($tahun, $bulan) {
                $join->on('m.product_id', '=', 'p.id')
                    ->where('m.status', 'done')
                    ->whereYear('m.created_at', $tahun);
                if ($bulan !== null) {
                    $join->whereMonth('m.created_at', $bulan);
                }
            })
            ->where('p.is_deleted', 0)
            ->groupBy('p.id', 'p.product_code', 'p.product_name', 'p.unit', 'p.fill_unit', 'p.stock')
            ->select([
                'p.id',
                'p.product_code',
                'p.product_name',
                'p.unit',
                'p.fill_unit',
                'p.stock',
            ])
            ->selectRaw($outgoingSql . ' as total_out')
            ->get()
            ->map(function ($row) use ($historyByProduct) {
                $productId = (int) $row->id;

                return [
                    'id' => $productId,
                    'kode' => $row->product_code ?: '-',
                    'nama' => $row->product_name ?: '-',
                    'satuan' => $row->unit ?: ($row->fill_unit ?: 'Pcs'),
                    'stok' => (float) $row->stock,
                    'total_out' => (float) $row->total_out,
                    'history' => $historyByProduct[$productId] ?? [],
                ];
            });

        $totalMasterProducts = $rows->count();
        $moved = $rows->filter(fn ($item) => $item['total_out'] > 0)->values();

        // Top 10 keluar terbanyak (Fast-moving)
        $topOut = $moved->sortByDesc('total_out')->take(10)->values();

        // 10 qty keluar terendah di antara produk yang bergerak
        $leastOut = $moved->sortBy('total_out')->take(10)->values();
        // Bila pergerakan kurang dari 10 produk, sertakan produk berstok yang belum bergerak sama sekali
        if ($leastOut->count() < 10) {
            $stagnant = $rows->filter(fn ($item) => $item['total_out'] == 0 && $item['stok'] > 0)
                ->sortByDesc('stok')
                ->take(10 - $leastOut->count())
                ->values();
            $leastOut = $leastOut->concat($stagnant)->values();
        }

        return [
            $topOut,
            $leastOut,
            (float) $moved->sum('total_out'),
            $moved->count(),
            $totalMasterProducts,
        ];
    }

    /**
     * Top Pengajuan Barang (SPB) berdasarkan quantity pada periode terpilih + DOI.
     *
     * @param int $tahun
     * @param int|null $bulan
     * @param array $doiMap
     * @return array{0: \Illuminate\Support\Collection, 1: float, 2: int}
     */
    private function buildTopSpb(int $tahun, ?int $bulan, array $doiMap): array
    {
        if (! Schema::hasTable('pengajuan_barang')) {
            return [collect(), 0.0, 0];
        }

        $query = DB::table('pengajuan_barang')
            ->whereNotIn('status', ['ditolak', 'dibatalkan'])
            ->whereYear('created_at', $tahun);

        if ($bulan !== null) {
            $query->whereMonth('created_at', $bulan);
        }

        $records = $query->select(['id', 'judul_pengajuan', 'status', 'created_at', 'rincian_barang'])->get();
        $spbDocCount = $records->count();

        // Master produk untuk sinkronisasi kode, satuan, dan stok fisik realtime
        $masterProducts = DB::table('products')
            ->where('is_deleted', 0)
            ->select(['id', 'product_code', 'product_name', 'product_name_clean', 'unit', 'fill_unit', 'stock'])
            ->get();

        $productMap = [];
        foreach ($masterProducts as $mp) {
            $entry = [
                'id' => $mp->id,
                'kode' => $mp->product_code ?: '-',
                'nama' => $mp->product_name,
                'satuan' => $mp->unit ?: ($mp->fill_unit ?: 'Pcs'),
                'stok' => (float) $mp->stock,
            ];

            $keys = $this->productMatchKeys((string) $mp->product_name);
            if (! empty($mp->product_name_clean)) {
                $keys = array_merge($keys, $this->productMatchKeys((string) $mp->product_name_clean));
            }

            foreach (array_unique($keys) as $key) {
                if (! isset($productMap[$key])) {
                    $productMap[$key] = $entry;
                }
            }
        }

        // DOI juga diindeks dengan normalisasi nama yang sama agar stok & DOI konsisten.
        $doiMapByKey = [];
        foreach ($doiMap as $dKey => $dVal) {
            foreach ($this->productMatchKeys((string) $dKey) as $key) {
                if (! isset($doiMapByKey[$key])) {
                    $doiMapByKey[$key] = $dVal;
                }
            }
        }

        $itemAgg = [];
        $totalSpbQty = 0.0;

        foreach ($records as $rec) {
            $rincian = is_string($rec->rincian_barang)
                ? json_decode($rec->rincian_barang, true)
                : $rec->rincian_barang;

            if (! is_array($rincian)) {
                continue;
            }

            foreach ($rincian as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $nama = trim((string) ($item['deskripsi'] ?? ($item['nama_barang'] ?? '')));
                if ($nama === '') {
                    continue;
                }

                $qty = (float) ($item['jumlah'] ?? 0);
                $satuan = trim((string) ($item['satuan'] ?? 'Pcs'));
                $key = mb_strtolower($nama);

                if (! isset($itemAgg[$key])) {
                    $itemAgg[$key] = [
                        'nama' => $nama,
                        'satuan' => $satuan,
                        'total_qty' => 0.0,
                        'spb_count' => 0,
                    ];
                }

                $itemAgg[$key]['total_qty'] += $qty;
                $itemAgg[$key]['spb_count'] += 1;
                $totalSpbQty += $qty;
            }
        }

        $topSpb = collect($itemAgg)->map(function ($item, $key) use ($productMap, $doiMapByKey) {
            $matchKeys = $this->productMatchKeys((string) $item['nama']);

            // Cocokkan ke master product: exact normalization dulu, baru substring.
            $master = null;
            foreach ($matchKeys as $mk) {
                if (isset($productMap[$mk])) {
                    $master = $productMap[$mk];
                    break;
                }
            }
            if (! $master) {
                foreach ($productMap as $mKey => $mVal) {
                    if (str_contains($mKey, $key) || str_contains($key, $mKey)) {
                        $master = $mVal;
                        break;
                    }
                }
            }

            $kode = $master['kode'] ?? '-';
            $stok = $master['stok'] ?? 0.0;
            $satuan = $master['satuan'] ?? $item['satuan'];

            // Cocokkan ke data ForecastService DOI dengan normalisasi yang sama.
            $doiInfo = null;
            foreach ($matchKeys as $mk) {
                if (isset($doiMapByKey[$mk])) {
                    $doiInfo = $doiMapByKey[$mk];
                    break;
                }
            }
            if (! $doiInfo) {
                foreach ($doiMapByKey as $dKey => $dVal) {
                    if (str_contains($dKey, $key) || str_contains($key, $dKey)) {
                        $doiInfo = $dVal;
                        break;
                    }
                }
            }

            $doi = $doiInfo['doi'] ?? null;
            $avgSales = $doiInfo['avg'] ?? 0.0;

            return [
                'nama' => $master['nama'] ?? $item['nama'],
                'kode' => $kode,
                'satuan' => $satuan,
                'total_qty' => $item['total_qty'],
                'spb_count' => $item['spb_count'],
                'stok' => $stok,
                'avg_sales' => $avgSales,
                'doi' => $doi,
            ];
        })
        ->sortByDesc('total_qty')
        ->take(10)
        ->values();

        return [$topSpb, $totalSpbQty, $spbDocCount];
    }

    /**
     * Kunci pencocokan nama produk yang toleran terhadap spasi, tanda baca,
     * dan prefix "RAKHA" (mis. "No 16 -MUA" vs "No 16 - MUA").
     *
     * @return array<int, string>
     */
    private function productMatchKeys(string $name): array
    {
        $base = mb_strtolower(trim($name));
        if ($base === '') {
            return [];
        }

        $spaced = preg_replace('/[^a-z0-9]+/u', ' ', $base) ?? '';
        $spaced = trim(preg_replace('/\s+/', ' ', $spaced) ?? '');

        $compact = preg_replace('/[^a-z0-9]+/u', '', $base) ?? '';
        $compact = preg_replace('/^rakha/', '', $compact) ?? $compact;

        return array_values(array_unique(array_filter([$base, $spaced, $compact])));
    }

    /**
     * Riwayat detail barang keluar per produk (tujuan, qty, dokumen, ekspedisi).
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function outgoingHistoryByProduct(int $tahun, ?int $bulan): array
    {
        if (! Schema::hasTable('stock_moves')) {
            return [];
        }

        $rows = DB::table('stock_moves as m')
            ->join('products as p', 'p.id', '=', 'm.product_id')
            ->where('p.is_deleted', 0)
            ->where('m.status', 'done')
            ->whereIn('m.type', ['delivery', 'scrap'])
            ->where(function ($q) {
                $q->whereNull('m.ref_type')->orWhere('m.ref_type', '<>', 'stock_move_reversal');
            })
            ->where(function ($q) {
                $q->whereNull('m.reason')->orWhere(function ($q2) {
                    $q2->where('m.reason', 'not like', 'Opname%')
                        ->where('m.reason', 'not like', '%[dibatalkan]%');
                });
            })
            ->whereYear('m.created_at', $tahun)
            ->when($bulan !== null, fn ($q) => $q->whereMonth('m.created_at', $bulan))
            ->orderByDesc('m.created_at')
            ->get([
                'm.product_id',
                'm.created_at',
                'm.qty',
                'm.uom',
                'm.type',
                'm.to_location',
                'm.partner',
                'm.purpose',
                'm.expedition',
                'm.doc_no',
                'm.reason',
            ]);

        return $rows->groupBy('product_id')->map(function ($moves) {
            return $moves->map(function ($move) {
                $tujuan = trim((string) $move->partner);

                // Sebagian data lama hanya menyimpan tujuan di teks alasan.
                if ($tujuan === '' && preg_match('/\bke\s+(.+?)(?:\s*\(|$)/i', (string) $move->reason, $match)) {
                    $tujuan = trim($match[1]);
                }
                if ($tujuan === '') {
                    $tujuan = $move->to_location && ! in_array(strtolower((string) $move->to_location), ['customer', 'stock'], true)
                        ? $move->to_location
                        : 'Tujuan tidak tercatat';
                }

                return [
                    'tanggal' => $move->created_at
                        ? Carbon::parse($move->created_at)->locale('id')->translatedFormat('d M Y')
                        : '-',
                    'qty' => (float) $move->qty,
                    'satuan' => $move->uom ?: 'Unit',
                    'tujuan' => $tujuan,
                    'jenis' => $move->purpose ?: ($move->type === 'scrap' ? 'Scrap' : 'Penjualan'),
                    'dok' => $move->doc_no ?: '',
                    'ekspedisi' => $move->expedition ?: '',
                ];
            })->values()->all();
        })->all();
    }

    /**
     * Ekspresi SUM qty keluar riil untuk tabel stock_moves ber-alias.
     *
     * Sumber angka hanya qty input pada baris delivery/scrap berstatus selesai.
     * Baris reversal (ref_type stock_move_reversal), koreksi opname, dan penanda
     * pembatalan dikecualikan supaya produk yang hanya dibatalkan/dikoreksi tidak
     * dianggap sebagai barang keluar dan totalnya tidak terhitung dua kali.
     */
    private function outgoingCaseSql(string $alias): string
    {
        $p = $alias . '.';

        return "SUM(CASE
            WHEN {$p}status = 'done'
                AND {$p}type IN ('delivery', 'scrap')
                AND ({$p}ref_type IS NULL OR {$p}ref_type <> 'stock_move_reversal')
                AND ({$p}reason IS NULL OR ({$p}reason NOT LIKE 'Opname%' AND {$p}reason NOT LIKE '%[dibatalkan]%'))
            THEN {$p}qty
            ELSE 0
        END)";
    }

    /**
     * Total keluar per bulan pada tahun terpilih untuk grafik tren.
     *
     * @return array<int, float> 1..12
     */
    private function buildMonthlyTrend(int $tahun): array
    {
        $trend = array_fill(1, 12, 0.0);

        if (! Schema::hasTable('stock_moves')) {
            return $trend;
        }

        // Samakan cakupan dengan buildOutgoing(): abaikan mutasi produk yang
        // sudah dihapus agar total tren = total qty keluar pada card ringkasan.
        $rows = DB::table('stock_moves as m')
            ->join('products as p', 'p.id', '=', 'm.product_id')
            ->where('p.is_deleted', 0)
            ->where('m.status', 'done')
            ->whereYear('m.created_at', $tahun)
            ->selectRaw('MONTH(m.created_at) as bulan')
            ->selectRaw($this->outgoingCaseSql('m') . ' as total')
            ->groupByRaw('MONTH(m.created_at)')
            ->pluck('total', 'bulan');

        foreach ($rows as $bulan => $total) {
            $trend[(int) $bulan] = (float) $total;
        }

        return $trend;
    }

    /**
     * Mengelompokkan data ForecastService ke dalam rentang DOI.
     *
     * @param array $stockForecast
     * @param int $tahun
     * @param string $bulanAktif
     * @param int $activeRefMonths
     * @param int $activeDoi
     * @return array{0: array, 1: string, 2: int, 3: int, 4: int}
     */
    private function buildDoiBucketsWithData(array $stockForecast, int $tahun, string $bulanAktif, int $activeRefMonths, int $activeDoi): array
    {
        $kodeMap = DB::table('products')->where('is_deleted', 0)->pluck('product_code', 'product_name')->toArray();

        $buckets = [];
        foreach (self::DOI_RANGES as $threshold => [$min, $max]) {
            $items = collect($stockForecast)
                ->filter(function ($item) use ($min, $max) {
                    $doi = (int) ($item['doi_qty'] ?? 0);

                    return $doi >= $min && ($max === null || $doi < $max);
                })
                ->sortByDesc('doi_qty')
                ->values()
                ->map(fn ($item) => [
                    'nama' => $item['nama_produk'],
                    'kode' => $kodeMap[$item['nama_produk']] ?? '-',
                    'satuan' => $item['satuan_stok'] ?? 'Pcs',
                    'stok' => (float) ($item['stok_tersedia'] ?? 0),
                    'avg' => (float) ($item['avg_qty'] ?? 0),
                    'doi' => (int) ($item['doi_qty'] ?? 0),
                ]);

            $buckets[$threshold] = $items;
        }

        return [$buckets, $bulanAktif, $tahun, $activeRefMonths, $activeDoi];
    }
}
