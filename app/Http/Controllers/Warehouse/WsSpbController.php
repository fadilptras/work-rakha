<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengajuanBarang;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PengajuanBarangNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WsSpbController extends Controller
{
    public function index()
    {
        $title = 'Pengajuan Barang';
        $totalPengajuan = Auth::user()->pengajuanBarangs()->count();

        // Dropdown supplier.
        $supplierList = Cache::remember('supplier_list_dropdown', 300, function () {
            if (Schema::hasTable('suppliers')) {
                $dbSuppliers = \App\Models\Supplier::active()->orderBy('supplier_name')->pluck('supplier_name')->toArray();
                if (!empty($dbSuppliers)) {
                    return $dbSuppliers;
                }
            }
            return [];
        });

        // Dropdown produk (dari products).
        $barangList = Cache::remember('barang_list_dropdown', 300, function () {
            if (Schema::hasTable('products')) {
                $dbBarangs = \App\Models\Product::where('is_deleted', 0)->orderBy('product_name')->pluck('product_name')->toArray();
                if (!empty($dbBarangs)) {
                    return $dbBarangs;
                }
            }
            return [];
        });

        // Peta nama -> kode (display saja).
        $barangKodeMap = Cache::remember('barang_kode_map', 300, function () {
            if (Schema::hasTable('products')) {
                return \App\Models\Product::where('is_deleted', 0)->orderBy('product_name')->pluck('product_code', 'product_name')->toArray();
            }
            return [];
        });

        // Preview No. SPB otomatis (readonly di form; nomor final mengikuti accessor saat data tersimpan)
        $now = Carbon::now();
        $urutanBulanan = PengajuanBarang::whereYear('created_at', $now->format('Y'))
            ->whereMonth('created_at', $now->format('m'))
            ->count() + 1;
        $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $previewNomorSpb = str_pad($urutanBulanan, 3, '0', STR_PAD_LEFT) . '/RAKHA/' . $romawi[(int) $now->format('n')] . '/' . $now->format('Y');

        // Back button berbasis asal halaman (?from=ops|dashboard), fallback role lama.
        $backFrom = strtolower(trim((string) request()->query('from', '')));
        if ($backFrom === 'ops') {
            $backRoute = route('warehouse.index', ['tab' => 'produk']);
            $backText = 'Back to Warehouse Ops';
        } elseif ($backFrom === 'dashboard') {
            $backRoute = route('warehouse.dashboard');
            $backText = 'Back to Warehouse Dashboard';
        } else {
            $u = Auth::user();
            $isWh = in_array(strtolower($u->divisi ?? ''), ['finance dan gudang', 'fianance dan gudang'])
                || \Illuminate\Support\Str::contains(strtolower($u->jabatan ?? ''), ['admin gudang', 'gudang', 'legal', 'legal & purchasing']);
            $backRoute = $isWh ? route('warehouse.dashboard') : route('dashboard');
            $backText = $isWh ? 'Back to Warehouse Dashboard' : 'Back to Dashboard';
        }

        return view('users.warehouse.spb.pengajuan-barang-form', compact('title', 'totalPengajuan', 'supplierList', 'barangList', 'barangKodeMap', 'previewNomorSpb', 'backRoute', 'backText', 'backFrom'));
    }

    public function history(Request $request)
    {
        $query = Auth::user()->pengajuanBarangs()->latest();
        if ($request->filled('status') && $request->status != 'semua') {
            if ($request->status == 'diproses') {
                $query->whereIn('status', ['diajukan', 'diproses']);
            } else {
                $query->where('status', $request->status);
            }
        }
        $pengajuanBarangs = $query->paginate(15)->appends($request->query());

        return view('users.warehouse.spb.pengajuan-barang-riwayat', [
            'title' => 'Riwayat Pengajuan Barang',
            'pengajuanBarangs' => $pengajuanBarangs,
        ]);
    }

    // Cek ringkas: apakah nama barang sudah pernah diajukan SEMUA user 30 hari terakhir (info saja)
    // Filter: semua user (tidak terbatas milik sendiri) + created_at >= 30 hari + status aktif (bukan ditolak/dibatalkan)
    // Cocok longgar (contains, case-insensitive) agar "kardus" ketemu "Kardus Rakha Med+"
    public function checkRiwayat(Request $request)
    {
        $nama = trim((string) $request->query('nama', ''));
        if (mb_strlen($nama) < 3) {
            return response()->json(['found' => false, 'count' => 0, 'last' => null]);
        }
        $key = mb_strtolower($nama);

        $candidates = PengajuanBarang::with('user:id,name')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->whereIn('status', ['diajukan', 'diproses', 'disetujui', 'selesai', 'proses_finalisasi'])
            ->latest()->limit(100)
            ->get(['id', 'user_id', 'judul_pengajuan', 'status', 'created_at', 'rincian_barang']);

        $count = 0;
        $last = null;
        foreach ($candidates as $p) {
            $items = $p->rincian_barang ?? [];
            if (!is_array($items)) continue;
            foreach ($items as $it) {
                if (!is_array($it)) continue;
                $a = mb_strtolower(trim((string) ($it['deskripsi'] ?? '')));
                $b = mb_strtolower(trim((string) ($it['nama_barang'] ?? '')));
                if ($a === '' && $b === '') continue;
                // exact ATAU contains dua arah (input "kardus" vs data "kardus rakha med+")
                $match = $this->spbNamaCocok($a, $key) || $this->spbNamaCocok($b, $key);
                if ($match) {
                    $count++;
                    if (!$last) {
                        $last = [
                            'judul' => $p->judul_pengajuan,
                            'status' => $p->status,
                            'tanggal' => $p->created_at ? $p->created_at->locale('id')->isoFormat('D MMM YYYY') : null,
                            'oleh' => $p->user->name ?? null,
                        ];
                    }
                    break;
                }
            }
        }

        return response()->json(['found' => $count > 0, 'count' => $count, 'last' => $last]);
    }

    /**
     * Status pengajuan per NAMA barang (batch) untuk kolom Produk di popup Tabel Forecast form SPB.
     *
     * POST /pengajuan-barang/status-batch-names {names: [...]}
     * - 1 query ambil SPB 30 hari terakhir semua user (limit 200), matching longgar
     *   yang sama seperti checkRiwayat, untuk semua nama sekaligus.
     * - Kunci map = nama persis seperti dikirim (JS mencocokkan string asli).
     */
    public function statusBatchNames(Request $request)
    {
        $names = $request->input('names', []);
        if (is_string($names)) {
            $names = [$names];
        }
        $names = collect(is_array($names) ? $names : [])
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => mb_strlen($v) >= 3)
            ->unique()->take(200)->values();
        if ($names->isEmpty()) {
            return response()->json(['map' => new \stdClass()]);
        }

        $keys = [];
        foreach ($names as $n) {
            $keys[$n] = mb_strtolower($n);
        }
        $sorted = collect($keys)->sort()->implode('|');
        $map = Cache::remember('spb_status_names_' . md5($sorted), 120, function () use ($names, $keys) {
            $candidates = PengajuanBarang::with('user:id,name')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->whereIn('status', ['diajukan', 'diproses', 'disetujui', 'selesai', 'proses_finalisasi'])
                ->latest()->limit(200)
                ->get(['id', 'user_id', 'judul_pengajuan', 'status', 'created_at', 'rincian_barang']);

            $out = [];
            foreach ($names as $n) {
                $out[$n] = ['found' => false, 'count' => 0, 'last' => null];
            }
            foreach ($candidates as $p) {
                $items = $p->rincian_barang ?? [];
                if (!is_array($items) || empty($items)) {
                    continue;
                }
                $itemNames = [];
                foreach ($items as $it) {
                    if (!is_array($it)) {
                        continue;
                    }
                    foreach (['deskripsi', 'nama_barang'] as $f) {
                        $s = mb_strtolower(trim((string) ($it[$f] ?? '')));
                        if ($s !== '') {
                            $itemNames[] = $s;
                        }
                    }
                }
                if (empty($itemNames)) {
                    continue;
                }
                foreach ($names as $n) {
                    $key = $keys[$n];
                    $hit = false;
                    foreach ($itemNames as $a) {
                        if ($this->spbNamaCocok($a, $key)) {
                            $hit = true;
                            break;
                        }
                    }
                    if ($hit) {
                        $out[$n]['found'] = true;
                        $out[$n]['count']++;
                        if (!$out[$n]['last']) {
                            $out[$n]['last'] = [
                                'judul' => $p->judul_pengajuan,
                                'status' => $p->status,
                                'tanggal' => $p->created_at ? $p->created_at->locale('id')->isoFormat('D MMM YYYY') : null,
                                'oleh' => $p->user->name ?? null,
                            ];
                        }
                    }
                }
            }
            return $out;
        });

        return response()->json(['map' => $map]);
    }

    /**
     * Status SPB per produk untuk badge di Monitoring Product (Warehouse Ops).
     *
     * GET /pengajuan-barang/status-batch?ids[]=1&ids[]=2
     * - Input product_ids (bukan nama) agar tak ambigu clean vs asli; server resolve namanya sendiri.
     * - 1 query ambil SPB 90 hari terakhir (semua user, limit 500) lalu matching in-memory
     *   dengan logika longgar yang sama seperti checkRiwayat (exact + contains dua arah).
     * - Angka = jumlah dokumen SPB distinct per produk (bukan qty).
     */
    public function statusBatch(Request $request)
    {
        $ids = $request->query('ids', []);
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = collect(is_array($ids) ? $ids : [])
            ->map(fn ($v) => (int) $v)->filter(fn ($v) => $v > 0)->unique()->take(500)->values();
        if ($ids->isEmpty()) {
            return response()->json(['map' => new \stdClass()]);
        }

        $products = \App\Models\Product::whereIn('id', $ids)
            ->get(['id', 'product_name', 'product_name_clean']);
        if ($products->isEmpty()) {
            return response()->json(['map' => new \stdClass()]);
        }

        $cacheKey = 'spb_status_' . now()->format('Y-m') . '_' . md5($ids->sort()->values()->implode(','));
        $map = Cache::remember($cacheKey, 120, function () use ($products) {
            $keys = [];
            foreach ($products as $p) {
                $k = [mb_strtolower(trim((string) $p->product_name))];
                $clean = mb_strtolower(trim((string) ($p->product_name_clean ?? '')));
                if ($clean !== '' && $clean !== $k[0]) {
                    $k[] = $clean;
                }
                $keys[$p->id] = array_values(array_filter($k, fn ($s) => mb_strlen($s) >= 3));
            }

            $now = Carbon::now();
            $docs = PengajuanBarang::with('user:id,name')
                ->where('created_at', '>=', $now->copy()->subDays(90))
                ->latest()->limit(500)
                ->get(['id', 'user_id', 'judul_pengajuan', 'status', 'created_at', 'rincian_barang']);

            $out = [];
            foreach ($products as $p) {
                $out[$p->id] = [
                    'aktif_bulan_ini' => 0, 'selesai_bulan_ini' => 0,
                    'total_bulan_ini' => 0, 'ditolak_90h' => false, 'last' => null,
                ];
            }

            $matchItem = fn (string $itemName, array $prodKeys): bool =>
                collect($prodKeys)->contains(fn ($key) => $this->spbNamaCocok($itemName, $key));

            foreach ($docs as $d) {
                $items = $d->rincian_barang ?? [];
                if (!is_array($items) || empty($items)) {
                    continue;
                }
                $isBulanIni = $d->created_at
                    && (int) $d->created_at->format('Y') === (int) $now->format('Y')
                    && (int) $d->created_at->format('n') === (int) $now->format('n');
                $isAktif = in_array($d->status, ['diajukan', 'diproses', 'proses_finalisasi'], true);
                $isSelesai = in_array($d->status, ['disetujui', 'selesai'], true);
                $isDitolak = in_array($d->status, ['ditolak', 'dibatalkan'], true);

                foreach ($products as $p) {
                    $prodKeys = $keys[$p->id] ?? [];
                    if (empty($prodKeys)) {
                        continue;
                    }
                    $hit = false;
                    foreach ($items as $it) {
                        if (!is_array($it)) {
                            continue;
                        }
                        $candA = (string) ($it['deskripsi'] ?? '');
                        $candB = (string) ($it['nama_barang'] ?? '');
                        if ($matchItem($candA, $prodKeys) || $matchItem($candB, $prodKeys)) {
                            $hit = true;
                            break;
                        }
                    }
                    if (!$hit) {
                        continue;
                    }
                    if ($isBulanIni) {
                        $out[$p->id]['total_bulan_ini']++;
                        if ($isAktif) {
                            $out[$p->id]['aktif_bulan_ini']++;
                        } elseif ($isSelesai) {
                            $out[$p->id]['selesai_bulan_ini']++;
                        }
                    }
                    if ($isDitolak) {
                        $out[$p->id]['ditolak_90h'] = true;
                    }
                    if (!$out[$p->id]['last']) {
                        $out[$p->id]['last'] = [
                            'judul' => $d->judul_pengajuan,
                            'status' => $d->status,
                            'tanggal' => $d->created_at ? $d->created_at->locale('id')->isoFormat('D MMM YYYY') : null,
                            'oleh' => $d->user->name ?? null,
                        ];
                    }
                }
            }

            return $out;
        });

        return response()->json(['map' => $map]);
    }

    /**
     * Rekomendasi qty dari forecast penjualan untuk form pengajuan barang.
     *
     * VITAL: seluruh angka dihitung dengan formula yang SAMA PERSIS dengan
     * SalesForecastController::buildForecastData (satu-satunya sumber kebenaran):
     *   avg      = total_qty_bulan_referensi / jumlah_bulan_referensi
     *   forecast = ceil(avg * (1 + percentage/100))
     *   buffer   = ceil(avg * (doi/30))
     *   order    = (stok - forecast) < buffer
     *              ? ceil((buffer - (stok - forecast)) / moq) * moq
     *              : 0
     *   doi      = max(0, ceil((stok / avg) * 30))
     * Stok diambil REALTIME dari products.stock (hasil mutasi Warehouse Ops),
     * lalu 0 bila produk tak ada di master.
     *
     * Perbedaan yang disengaja vs halaman forecast (terdokumentasi):
     * - Hanya satu produk (best-match dari ketikan, exact didahulukan).
     * - best-match produk master memakai nama EXACT agar stok tak tertukar produk lain.
     */
    public function checkForecast(Request $request)
    {
        $nama = trim((string) $request->query('nama', ''));
        if (mb_strlen($nama) < 3) {
            return response()->json(['found' => false]);
        }
        $key = mb_strtolower($nama);
        $tahun = (int) date('Y');

        // TTL pendek: angka selalu segar mengikuti setting forecast terbaru.
        $data = Cache::remember('forecast_suggest_' . $tahun . '_' . md5($key), 120, function () use ($key, $nama, $tahun) {
            // --- 1. Periode acuan + setting (paritas dengan buildForecastData) ---
            $urutanBulan = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            $namaBulan = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            $bulanAktif = $namaBulan[(int) date('n')];

            $setting = \App\Models\SalesForecastSetting::where('year', $tahun)->where('month', $bulanAktif)->first();
            $activePercentage = $setting && $setting->percentage !== null ? (float) $setting->percentage : 20.00;
            $activeRefMonths = $setting && $setting->ref_months !== null ? (int) $setting->ref_months : 6;
            $activeRefMonths = max(1, min($activeRefMonths, 12));
            $activeDoi = $setting && $setting->doi !== null ? (int) $setting->doi : 30;
            $activeDoi = max(1, min($activeDoi, 365));
            $multiplier = 1 + ($activePercentage / 100);

            $indexAktif = array_search($bulanAktif, $urutanBulan);
            if ($indexAktif !== false && $indexAktif >= $activeRefMonths) {
                $bulanReferensi = array_slice($urutanBulan, $indexAktif - $activeRefMonths, $activeRefMonths);
            } elseif ($indexAktif !== false && $indexAktif > 0) {
                $bulanReferensi = array_slice($urutanBulan, 0, $indexAktif);
            } else {
                $bulanReferensi = array_slice($urutanBulan, 0, $activeRefMonths);
            }

            // --- 2. Ambil penjualan bulan referensi untuk kandidat yang cocok ---
            // Escape wildcard LIKE agar ketikan user aman; limit besar supaya
            // best-match tidak terpotong sebelum dikelompokkan per produk.
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $nama);
            $rows = \App\Models\Sales::whereYear('date', $tahun)
                ->whereIn('month', $bulanReferensi)
                ->whereNotNull('product_name')->where('product_name', '!=', '')
                ->where('product_name', 'LIKE', '%' . $escaped . '%')
                ->select('product_name', 'month', DB::raw('SUM(qty) as total_qty'), DB::raw('MAX(unit) as satuan'))
                ->groupBy('product_name', 'month')
                ->limit(2000)
                ->get();

            if ($rows->isEmpty()) {
                return ['found' => false];
            }

            // Best-match: nama persis didahulukan; bila tak ada, pilih total terbesar.
            $grouped = $rows->groupBy(fn($r) => mb_strtolower(trim((string) $r->product_name)));
            $bestName = null;
            foreach ($grouped->keys() as $candidate) {
                if ($candidate === $key) {
                    $bestName = $candidate;
                    break;
                }
            }
            if (!$bestName) {
                $bestName = $grouped->map(fn($g) => $g->sum('total_qty'))->sortDesc()->keys()->first();
            }
            $bestRows = $grouped->get($bestName);
            $displayName = $bestRows->first()->product_name;

            // --- 3. Hitung avg / forecast / buffer (paritas dengan buildForecastData) ---
            // Pembagi selalu JUMLAH bulan referensi (bukan bulan yang ada datanya).
            $totalRef = $bestRows->sum('total_qty');
            $jumlahBulan = count($bulanReferensi);
            $avg = $jumlahBulan > 0 ? $totalRef / $jumlahBulan : 0;
            if ($avg <= 0) {
                return ['found' => false];
            }
            $forecast = (int) ceil($avg * $multiplier);
            $bufferQty = (int) ceil($avg * ($activeDoi / 30));

            // --- 4. Stok akhir REALTIME (paritas dengan buildForecastData) ---
            // Langsung dari products.stock (update instan tiap mutasi Warehouse Ops).
            // WAJIB nama persis di master; tanpa fallback longgar supaya stok
            // tidak tertukar milik produk lain. Bila tak ada: stok = 0.
            $product = \App\Models\Product::active()->where('product_name', $displayName)->first();
            $endstock = 0;
            $satuanStok = 'Pcs';
            if ($product) {
                $satuanStok = $product->unit ?: 'Pcs';
                $endstock = (int) ($product->stock ?? 0);
            }

            // --- 5. MOQ + order (paritas dengan buildForecastData) ---
            $moq = (float) (DB::table('sales_forecast_orders')
                ->where('year', $tahun)->where('month', $bulanAktif)
                ->where('product_name', $displayName)->value('moq') ?: 1);
            $moq = max(1, $moq);

            if (($endstock - $forecast) < $bufferQty) {
                $selisih = $bufferQty - ($endstock - $forecast);
                $orderQty = (int) (ceil($selisih / $moq) * $moq);
            } else {
                $orderQty = 0;
            }
            $doiHasil = (int) max(0, ceil(($endstock / $avg) * 30));

            $satuanRow = $bestRows->whereNotNull('satuan')->where('satuan', '!=', '')->first();
            $satuanSales = $satuanRow ? $satuanRow->satuan : ($satuanStok ?: 'Pcs');

            return [
                'found' => true,
                'product' => $displayName,
                'satuan_sales' => $satuanSales,
                'satuan_stok' => $satuanStok,
                'avg' => round($avg, 2),
                'forecast_qty' => $forecast,
                'buffer_qty' => $bufferQty,
                'stok' => $endstock,
                'doi' => $doiHasil,
                'moq' => $moq,
                'order_qty' => $orderQty,
                'suggest_qty' => $orderQty > 0 ? $orderQty : $forecast,
                'ref_months' => $activeRefMonths,
                'percentage' => $activePercentage,
                'bulan_aktif' => $bulanAktif,
                'tahun' => $tahun,
            ];
        });

        return response()->json($data);
    }

    // tampilkan semua pengajuan barang untuk pemantauan (akses approver/admin)
    public function monitoringAll(Request $request)
    {
        $user = Auth::user();
        
        // cek hak akses (hanya approver & admin)
        $isApprover = \App\Models\User::query()
            ->where('approver_barang_1_id', $user->id)
            ->orWhere('approver_barang_2_id', $user->id)
            ->orWhere('approver_barang_3_id', $user->id)
            ->orWhere('approver_barang_4_id', $user->id)
            ->exists();

        if (!($isApprover || $user->role === 'admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // query semua pengajuan
        $query = PengajuanBarang::with('user')->latest();
        
        // filter status
        if ($request->filled('status') && $request->status != 'semua') {
            if ($request->status == 'diproses') {
                $query->whereIn('status', ['diajukan', 'diproses']);
            } else {
                $query->where('status', $request->status);
            }
        }
        
        // pencarian (judul + nama pemohon; nomor_surat adalah accessor, tidak bisa di-query)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul_pengajuan', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // paginate dan return
        $pengajuanBarangs = $query->paginate(15)->appends($request->query());

        return view('users.warehouse.spb.pengajuan-barang-monitoring', [
            'title' => 'Monitoring Seluruh Pengajuan Barang',
            'pengajuanBarangs' => $pengajuanBarangs,
        ]);
    }

    // proses simpan pengajuan barang baru
    public function store(Request $request)
    {
        $user = Auth::user();

        // set divisi jika kosong
        if (!$request->filled('divisi')) {
            $request->merge(['divisi' => $user->divisi ?: 'Umum']);
        }

        // validasi input (rincian wajib minimal 1 baris terisi)
        $request->validate([
            'judul_pengajuan' => 'required|string|max:255',
            'divisi' => 'required|string|max:255',
            'rincian_deskripsi' => 'required|array|min:1',
            'rincian_deskripsi.*' => 'required|string',
            'rincian_supplier.*' => 'nullable|string',
            'rincian_jumlah.*' => 'required|integer|min:1',
            'rincian_satuan.*' => 'required|string',
            'rincian_keterangan.*' => 'nullable|string',
            'catatan_pemohon' => 'nullable|string',
            'file_pendukung' => 'nullable|array|max:10',
            'file_pendukung.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240',
        ]);

        // simpan supplier baru otomatis
        if ($request->has('rincian_supplier') && is_array($request->rincian_supplier)) {
            $hasNew = false;
            foreach ($request->rincian_supplier as $sup) {
                $supName = trim($sup ?? '');
                if (!empty($supName) && !in_array($supName, ['-', 'Lainnya'])) {
                    if (Schema::hasTable('suppliers')) {
                        \App\Models\Supplier::firstOrCreate(['supplier_name' => $supName]);
                        $hasNew = true;
                    }
                }
            }
            if ($hasNew) {
                Cache::forget('supplier_list_dropdown');
            }
        }

        // wajib punya minimal 1 approver
        if (!$user->approver_barang_1_id && !$user->approver_barang_2_id && !$user->approver_barang_3_id && !$user->approver_barang_4_id) {
            return redirect()->back()->with('error', 'Anda belum memiliki Approver Barang yang diatur oleh Admin. Silakan hubungi Admin/HRD.');
        }

        // ambil ID approver
        $app1 = $user->approver_barang_1_id;
        $app2 = $user->approver_barang_2_id;
        $app3 = $user->approver_barang_3_id;
        $app4 = $user->approver_barang_4_id;

        // inisialisasi status awal
        $st1 = $app1 ? 'menunggu' : 'skipped';
        $st2 = $app2 ? 'menunggu' : 'skipped';
        $st3 = $app3 ? 'menunggu' : 'skipped';
        $st4 = $app4 ? 'menunggu' : 'skipped';

        // buat data pengajuan
        $pengajuan = PengajuanBarang::create([
            'user_id' => $user->id,
            'judul_pengajuan' => $request->judul_pengajuan,
            'divisi' => $request->divisi,
            'catatan_pemohon' => $request->catatan_pemohon,
            'rincian_barang' => $this->parseRincian($request),
            'lampiran' => $this->uploadFiles($request),
            'status' => 'diajukan',
            'approver_barang_1_id' => $app1,
            'status_appr_1' => $st1,
            'approver_barang_2_id' => $app2,
            'status_appr_2' => $st2,
            'approver_barang_3_id' => $app3,
            'status_appr_3' => $st3,
            'approver_barang_4_id' => $app4,
            'status_appr_4' => $st4,
        ]);

        // 5. Logika Notifikasi Pertama (Cari Approver aktif pertama)
        $firstApprover = null;
        if ($pengajuan->approver1 && $st1 === 'menunggu') $firstApprover = $pengajuan->approver1;
        elseif ($pengajuan->approver2 && $st2 === 'menunggu') $firstApprover = $pengajuan->approver2;
        elseif ($pengajuan->approver3 && $st3 === 'menunggu') $firstApprover = $pengajuan->approver3;
        elseif ($pengajuan->approver4 && $st4 === 'menunggu') $firstApprover = $pengajuan->approver4;

        if ($firstApprover) {
            $firstApprover->notify(new PengajuanBarangNotification($pengajuan, 'baru'));
        }

        return redirect()->route('pengajuan_barang.index')->with('success', 'Pengajuan berhasil dikirim.');
    }

    public function show(PengajuanBarang $pengajuanBarang)
    {
        $user = Auth::user();
        $isOwner = $user->id === $pengajuanBarang->user_id;
        $isApprover = in_array($user->id, [
            $pengajuanBarang->approver_barang_1_id,
            $pengajuanBarang->approver_barang_2_id,
            $pengajuanBarang->approver_barang_3_id,
            $pengajuanBarang->approver_barang_4_id,
        ]);
        if (!($isOwner || $isApprover || $user->role === 'admin')) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }
        // Load relasi agar view tidak error saat menampilkan detail approver
        $pengajuanBarang->load(['user', 'approver1', 'approver2', 'approver3', 'approver4']);
        return view('users.warehouse.spb.pengajuan-barang-detail', compact('pengajuanBarang'));
    }

    /**
     * Update Status Barang - ALGORITMA DINAMIS (Anti-Stuck untuk 1, 2, 3, atau 4 Approver)
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'alasan' => 'nullable|string|max:255'
        ]);

        $pengajuan = PengajuanBarang::with(['user', 'approver1', 'approver2', 'approver3', 'approver4'])->findOrFail($id);
        $user = Auth::user();
        $statusInput = $request->status;

        // 1. Tentukan pada tahap ke berapa User ini bertindak
        $currentStage = null;
        if ($user->id == $pengajuan->approver_barang_1_id && $pengajuan->status_appr_1 == 'menunggu') {
            $currentStage = 1;
        } elseif ($user->id == $pengajuan->approver_barang_2_id && $pengajuan->status_appr_2 == 'menunggu') {
            if ($pengajuan->status_appr_1 == 'menunggu') return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
            $currentStage = 2;
        } elseif ($user->id == $pengajuan->approver_barang_3_id && $pengajuan->status_appr_3 == 'menunggu') {
            if (in_array('menunggu', [$pengajuan->status_appr_1, $pengajuan->status_appr_2])) return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
            $currentStage = 3;
        } elseif (($user->id == $pengajuan->approver_barang_4_id || $user->role === 'admin') && $pengajuan->status_appr_4 == 'menunggu') {
            if (in_array('menunggu', [$pengajuan->status_appr_1, $pengajuan->status_appr_2, $pengajuan->status_appr_3])) return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
            $currentStage = 4;
        } else {
            return redirect()->back()->with('error', 'Otoritas tidak valid atau urutan persetujuan salah.');
        }

        // 2. Jika DITOLAK, langsung hentikan proses & tolak pengajuan utama
        if ($statusInput == 'ditolak') {
            $pengajuan->update([
                "status_appr_{$currentStage}" => 'ditolak',
                "catatan_approver_{$currentStage}" => $request->alasan,
                "tanggal_approved_{$currentStage}" => Carbon::now(),
                'status' => 'ditolak'
            ]);
            $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'ditolak'));
            return redirect()->back()->with('success', 'Pengajuan barang telah ditolak.');
        }

        // 3. Jika DISETUJUI, update status dan tanggal approve tahap saat ini terlebih dahulu
        $pengajuan->update([
            "status_appr_{$currentStage}" => 'disetujui',
            "catatan_approver_{$currentStage}" => $request->alasan,
            "tanggal_approved_{$currentStage}" => Carbon::now(),
        ]);

        // 4. Periksa apakah setelah tahap ini masih ada Approver berikutnya yang berstatus 'menunggu' (Mendukung hingga Approver 4)
        $nextApprover = null;
        if ($currentStage < 4) {
            if ($pengajuan->status_appr_2 == 'menunggu') {
                $nextApprover = $pengajuan->approver2;
            } elseif ($pengajuan->status_appr_3 == 'menunggu') {
                $nextApprover = $pengajuan->approver3;
            } elseif ($pengajuan->status_appr_4 == 'menunggu') {
                $nextApprover = $pengajuan->approver4;
            }
        }

        // 5. Eksekusi lanjutan berdasarkan ketersediaan Approver berikutnya
        if ($nextApprover) {
            // MASIH ADA APPROVER SELANJUTNYA -> Ubah status jadi diproses & kirim notif ke approver berikutnya
            $pengajuan->update(['status' => 'diproses']);
            $nextApprover->notify(new PengajuanBarangNotification($pengajuan, 'baru'));
            $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'disetujui_parsial'));
        } else {
            if ($currentStage < 4) {
                // SEMUA APPROVER SUDAH SETUJU (Tidak ada Approver 4 yang menunggu) -> Ubah status utama menjadi disetujui untuk diproses Admin
                $pengajuan->update(['status' => 'disetujui']);
                $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'disetujui_final'));
                
                // Kirim notif ke seluruh approver (1-3) bahwa pengajuan ini berhasil disetujui
                foreach ([$pengajuan->approver1, $pengajuan->approver2, $pengajuan->approver3] as $appr) {
                    if ($appr) $appr->notify(new PengajuanBarangNotification($pengajuan, 'disetujui_semua'));
                }
            } else {
                // Jika stage 4 (Admin) menyetujui dari form ini, tapi biasanya admin update via updateMonitoring
                // Kita biarkan logika ini berjaga-jaga jika Admin menyetujui langsung
                $pengajuan->update(['status' => 'disetujui']);
                
                foreach ([$pengajuan->approver1, $pengajuan->approver2, $pengajuan->approver3, $pengajuan->approver4] as $appr) {
                    if ($appr) $appr->notify(new PengajuanBarangNotification($pengajuan, 'disetujui_semua'));
                }
            }
        }

        return redirect()->back()->with('success', 'Status pengajuan barang berhasil diperbarui.');
    }

    /**
     * Download PDF Pengajuan Barang (Mendukung hingga Approver 4)
     */
    public function download(PengajuanBarang $pengajuanBarang)
    {
        $user = Auth::user();
        $isOwner = $user->id === $pengajuanBarang->user_id;
        $isApprover = in_array($user->id, [
            $pengajuanBarang->approver_barang_1_id,
            $pengajuanBarang->approver_barang_2_id,
            $pengajuanBarang->approver_barang_3_id,
            $pengajuanBarang->approver_barang_4_id,
        ]);
        if (!($isOwner || $isApprover || $user->role === 'admin')) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }
        $pengajuanBarang->load(['user', 'approver1', 'approver2', 'approver3', 'approver4']);
        $pdf = Pdf::loadView('pdf.documents.pengajuan-barang', [
            'pengajuanBarang' => $pengajuanBarang,
        ]);
        $filename = 'SPB_' . $pengajuanBarang->nomor_surat . '_' . ($pengajuanBarang->user->name ?? 'Unknown') . '_' . $pengajuanBarang->judul_pengajuan . '.pdf';
        $filename = str_replace(['/', '\\'], '-', $filename);
        return $pdf->download($filename);
    }

    /**
     * Cocok nama item SPB vs kata kunci: exact ATAU contains dua arah (case-insensitive).
     * Reverse-match hanya untuk nama tersimpan >=3 char agar tidak false-positive.
     * Dipakai checkRiwayat, statusBatch, dan statusBatchNames agar logikanya satu sumber.
     */
    private function spbNamaCocok(string $itemName, string $key): bool
    {
        $a = mb_strtolower(trim($itemName));
        if ($a === '' || mb_strlen($a) < 2) {
            return false;
        }
        return $a === $key || str_contains($a, $key)
            || (mb_strlen($a) >= 3 && str_contains($key, $a));
    }

    /** Mengolah rincian barang dari form ke format array (JSON). */
    private function parseRincian(Request $request)
    {
        $rincian = [];
        $deskripsiList = $request->input('rincian_deskripsi', []);
        if (!is_array($deskripsiList)) {
            return $rincian;
        }
        foreach ($deskripsiList as $index => $deskripsi) {
            $rincian[] = [
                'deskripsi'  => $deskripsi,
                'supplier'   => $request->input("rincian_supplier.{$index}", '-'),
                'satuan'     => $request->input("rincian_satuan.{$index}", 'Pcs'),
                'jumlah'     => $request->input("rincian_jumlah.{$index}", 0),
                'keterangan' => $request->input("rincian_keterangan.{$index}", '-'),
            ];
        }
        return $rincian;
    }

    /** Mengurus upload banyak file lampiran (input kosong dilewati). */
    private function uploadFiles(Request $request)
    {
        $pathFiles = [];
        if (!$request->hasFile('file_pendukung')) {
            return $pathFiles;
        }
        foreach ((array) $request->file('file_pendukung') as $file) {
            if ($file && $file->isValid()) {
                $pathFiles[] = $file->store('lampiran_barang', 'public');
            }
        }
        return $pathFiles;
    }

    /**
     * Membatalkan pengajuan barang (hanya pemilik, hanya jika belum diproses final).
     */
    public function cancel(PengajuanBarang $pengajuanBarang)
    {
        if (Auth::id() !== $pengajuanBarang->user_id) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan pengajuan ini.');
        }

        // Ketika approver 1 sudah approve tidak bisa dibatalkan
        if ($pengajuanBarang->status_appr_1 === 'disetujui') {
            return redirect()->back()->with('error', 'Pengajuan sudah disetujui oleh Approver 1 dan tidak dapat dibatalkan.');
        }

        if (!in_array($pengajuanBarang->status, ['diajukan', 'diproses'])) {
            return redirect()->back()->with('error', 'Pengajuan sudah selesai diproses, tidak bisa dibatalkan.');
        }

        $pengajuanBarang->update([
            'status' => 'dibatalkan',
            'status_appr_1' => $pengajuanBarang->status_appr_1 === 'menunggu' ? 'dibatalkan' : $pengajuanBarang->status_appr_1,
            'status_appr_2' => $pengajuanBarang->status_appr_2 === 'menunggu' ? 'dibatalkan' : $pengajuanBarang->status_appr_2,
            'status_appr_3' => $pengajuanBarang->status_appr_3 === 'menunggu' ? 'dibatalkan' : $pengajuanBarang->status_appr_3,
            'status_appr_4' => $pengajuanBarang->status_appr_4 === 'menunggu' ? 'dibatalkan' : $pengajuanBarang->status_appr_4,
        ]);

        return redirect()->back()->with('success', 'Pengajuan barang berhasil dibatalkan.');
    }

    /**
     * Memperbarui status monitoring & log pengiriman/proses barang oleh Approver 4 dari User side.
     */
    public function updateMonitoring(Request $request, string $id)
    {
        $request->validate([
            'status_monitoring' => $request->filled('tandai_selesai') ? 'nullable|string|max:255' : 'required|string|max:255',
            'catatan_monitoring' => 'nullable|string|max:1000',
            'tandai_selesai' => 'nullable|boolean',
            'lampiran_monitoring' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'termin_id' => 'required|integer',
        ]);

        $pengajuan = PengajuanBarang::findOrFail($id);
        $user = Auth::user();
        
        // Ensure only approver 4 (or admin) can update this
        if ($user->id != $pengajuan->approver_barang_4_id && $user->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $statusMonitoring = $request->status_monitoring;
        if ($request->filled('tandai_selesai') && empty($statusMonitoring)) {
            $statusMonitoring = 'Selesai / Barang Diterima';
        }
        $catatan = $request->catatan_monitoring;
        $nowFormatted = Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm');

        $updateData = [];
        $lampiranPath = null;
        if ($request->hasFile('lampiran_monitoring')) {
            // Simpan di folder lampiran_barang sesuai permintaan
            $lampiranPath = $request->file('lampiran_monitoring')->store('lampiran_barang', 'public');
            
            // Tambahkan ke array lampiran utama
            $existingLampiran = is_array($pengajuan->lampiran) ? $pengajuan->lampiran : json_decode($pengajuan->lampiran, true) ?? [];
            $existingLampiran[] = $lampiranPath;
            $updateData['lampiran'] = $existingLampiran;
        }

        $riwayatEntry = [
            'status' => $statusMonitoring,
            'catatan' => $catatan ?: '-',
            'waktu' => $nowFormatted,
            'oleh' => $user->name,
            'lampiran' => $lampiranPath, 
        ];

        // Update riwayat GLOBAL (tetap dilakukan sebagai backup)
        $riwayat = $pengajuan->riwayat_monitoring ?? [];
        $riwayat[] = $riwayatEntry;

        $updateData['status_monitoring'] = $statusMonitoring;
        $updateData['riwayat_monitoring'] = $riwayat;

        // Update riwayat SPECIFIC TERMIN (ini yang ditampilkan di view UI baru)
        $terminId = $request->termin_id;
        $dataTermin = $pengajuan->data_termin ?? [];
        $terminFound = false;
        
        foreach ($dataTermin as &$termin) {
            if (isset($termin['id_termin']) && $termin['id_termin'] == $terminId) {
                $termin['status_monitoring'] = $statusMonitoring;
                if (!isset($termin['riwayat']) || !is_array($termin['riwayat'])) {
                    $termin['riwayat'] = [];
                }
                // Prepend so the newest is first? No, append so it's ordered chronologically. 
                // Or maybe the view expects it reversed? The view says: `@forelse($termin['riwayat'] ?? [] as $idx => $log)`
                // Usually we append or prepend. Let's prepend it (array_unshift) so newest is on top. 
                array_unshift($termin['riwayat'], $riwayatEntry);
                $terminFound = true;
                break;
            }
        }
        
        if ($terminFound) {
            $updateData['data_termin'] = $dataTermin;
        }

        // Jika tombol "Tandai Selesai" diklik atau status monitoring diset Selesai
        if ($request->filled('tandai_selesai') || in_array(strtolower($statusMonitoring), ['selesai', 'barang diterima', 'selesai / barang diterima'])) {
            $updateData['status'] = 'selesai';
            $updateData['status_monitoring'] = 'Selesai / Barang Diterima';
            $updateData['status_appr_4'] = 'selesai';
            $updateData['tanggal_approved_4'] = Carbon::now();
        }

        $pengajuan->update($updateData);

        // --- BLOK NOTIFIKASI ---
        if (isset($updateData['status']) && $updateData['status'] === 'selesai') {
            // Notifikasi Selesai Pengajuan
            $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'selesai_pengajuan'));
            foreach ([$pengajuan->approver1, $pengajuan->approver2, $pengajuan->approver3, $pengajuan->approver4] as $appr) {
                if ($appr) $appr->notify(new PengajuanBarangNotification($pengajuan, 'selesai_pengajuan'));
            }
        } else {
            // Notifikasi Update Pelacakan
            $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'update_pelacakan', [
                'status' => $statusMonitoring,
                'catatan' => $catatan ?: '-'
            ]));
        }
        // ------------------------

        return redirect()->back()->with('success', 'Status monitoring barang berhasil diperbarui!');
    }

    /**
     * Konfirmasi pemrosesan barang secara parsial (pengiriman bertahap) dengan Termin.
     */
    public function konfirmasiProses(Request $request, string $id)
    {
        $request->validate([
            'jumlah_diproses' => 'required|array',
            'jumlah_diproses.*' => 'nullable|numeric|min:0',
        ]);

        $pengajuan = PengajuanBarang::findOrFail($id);
        $user = Auth::user();
        
        // Ensure only approver 4 (or admin) can update this
        if ($user->id != $pengajuan->approver_barang_4_id && $user->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $rincianBarang = $pengajuan->rincian_barang ?? [];
        $jumlahDiprosesData = $request->jumlah_diproses;
        $dataTermin = $pengajuan->data_termin ?? [];

        $hasChanges = false;
        $newTerminRincian = [];
        
        foreach ($rincianBarang as $index => &$item) {
            $inputJumlah = floatval($jumlahDiprosesData[$index] ?? 0);
            
            if ($inputJumlah > 0) {
                if (!isset($item['jumlah_diproses'])) $item['jumlah_diproses'] = 0;
                
                $sisa = ($item['jumlah'] ?? 0) - $item['jumlah_diproses'];
                $diprosesSekarang = min($inputJumlah, $sisa); 
                
                if ($diprosesSekarang > 0) {
                    $item['jumlah_diproses'] += $diprosesSekarang;
                    
                    $newTerminRincian[] = [
                        'index_barang' => $index,
                        'nama_barang' => $item['nama_barang'] ?? $item['deskripsi'] ?? 'Unknown',
                        'jumlah' => $diprosesSekarang,
                        'satuan' => $item['satuan'] ?? ''
                    ];
                    $hasChanges = true;
                }
            }
        }
        unset($item); // putus referensi foreach-by-reference agar tak bocor ke kode bawah

        if ($hasChanges && count($newTerminRincian) > 0) {
            $terminId = count($dataTermin) + 1;
            $nowFormatted = Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
            
            $dataTermin[] = [
                'id_termin' => $terminId,
                'tanggal_dibuat' => $nowFormatted,
                'status_monitoring' => 'Diproses/Dipesan',
                'rincian' => $newTerminRincian,
                'riwayat' => [
                    [
                        'status' => 'Termin Dibuat',
                        'catatan' => 'Barang mulai diproses untuk termin ini.',
                        'waktu' => $nowFormatted,
                        'oleh' => Auth::user()->name,
                        'lampiran' => null
                    ]
                ]
            ];

            // Juga update riwayat global untuk memberitahu user ada termin baru
            $riwayatGlobal = $pengajuan->riwayat_monitoring ?? [];
            $riwayatGlobal[] = [
                'status' => 'Pengiriman Termin ' . $terminId,
                'catatan' => 'Admin telah membuat Termin ' . $terminId . ' untuk sebagian barang.',
                'waktu' => $nowFormatted,
                'oleh' => Auth::user()->name,
                'lampiran' => null,
            ];

            $pengajuan->update([
                'rincian_barang' => $rincianBarang,
                'data_termin' => $dataTermin,
                'riwayat_monitoring' => $riwayatGlobal,
            ]);

            return redirect()->back()->with('success', 'Termin ' . $terminId . ' berhasil dibuat.');
        }

        return redirect()->back()->with('info', 'Tidak ada data jumlah yang ditambahkan.');
    }

    /**
     * Migrasi data lama ke Termin 1 otomatis
     */
    public function migrasiTerminLama(Request $request, string $id)
    {
        $pengajuan = PengajuanBarang::findOrFail($id);
        
        if (!empty($pengajuan->data_termin)) {
            return redirect()->back()->with('error', 'Pengajuan ini sudah memiliki termin.');
        }
        
        $rincian = $pengajuan->rincian_barang ?? [];
        $rincianTermin = [];
        
        // Tandai semua barang sebagai sudah diproses penuh
        foreach ($rincian as &$item) {
            $item['jumlah_diproses'] = $item['jumlah'] ?? 0;
            
            $rincianTermin[] = [
                'nama_barang' => $item['nama_barang'] ?? $item['deskripsi'] ?? '-',
                'jumlah' => $item['jumlah_diproses'],
                'satuan' => $item['satuan'] ?? ''
            ];
        }
        
        $nowFormatted = \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
        
        $terminBaru = [
            'id_termin' => 1,
            'tanggal_dibuat' => $nowFormatted,
            'status_monitoring' => $pengajuan->status_monitoring ?? 'Proses Purchasing',
            'rincian' => $rincianTermin,
            'riwayat' => [
                [
                    'status' => 'Migrasi Sistem',
                    'catatan' => 'Sistem secara otomatis merangkum semua data lama ke dalam Termin 1.',
                    'waktu' => $nowFormatted,
                    'oleh' => 'Sistem',
                    'lampiran' => null
                ]
            ]
        ];
        
        $pengajuan->rincian_barang = $rincian;
        $pengajuan->data_termin = [$terminBaru];
        $pengajuan->save();
        
        return redirect()->back()->with('success', 'Data lama berhasil dimigrasikan ke Termin 1. Silakan lanjutkan pelacakan termin.');
    }
}