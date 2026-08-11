<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\SalesTarget;
use App\Imports\SalesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    /**
     * Urutan bulan standar (dipakai untuk sorting & label, karena kolom `bulan` disimpan sbg string).
     */
    protected array $urutanBulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    /**
     * Menampilkan halaman daftar sales dan form input/upload.
     */
    public function index()
    {
        $sales = Sales::orderBy('id', 'desc')->paginate(40);

        return view('users.sales.index', [
            'title' => 'Input Data Sales',
            'sales' => $sales
        ]);
    }

    /**
     * Menyimpan data dari form input manual.
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'tanggal'       => 'nullable|date',
            'nama_customer' => 'nullable|string|max:255',
            'nama_produk'   => 'nullable|string|max:255',
            'qty'           => 'nullable|integer|min:1',
            'satuan'        => 'nullable|string|max:50',
            'hna'           => 'nullable|numeric|min:0',
            'diskon'        => 'nullable|numeric|min:0',
            'harga_nett'    => 'nullable|numeric|min:0',
            'bulan'         => 'nullable|string|max:50',
            'ps'            => 'nullable|string|max:255',
        ]);

        Sales::create($request->all());

        return redirect()->back()->with('success', 'Data sales manual berhasil disimpan!');
    }

    /**
     * Menyimpan data dari upload file Excel.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240' // max 10MB
        ]);

        try {
            Excel::import(new SalesImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data sales dari Excel berhasil diimpor!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Upload Excel Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Mengunduh template excel kosong.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'tanggal', 'nama_customer', 'nama_produk', 'qty', 'satuan', 'hna', 'diskon', 'harga_nett', 'bulan', 'ps'
            ]);
            fputcsv($file, [
                '2026-08-01', 'PT. Sejahtera', 'Obat A', '10', 'Box', '50000', '0', '50000', 'Agustus', 'PS1'
            ]);
            fclose($file);
        };

        return response()->streamDownload($callback, 'Template_Sales.csv', $headers);
    }

    /* =========================================================================
     |  HALAMAN MONITORING
     |  ========================================================================= */

    public function target(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        
        $listPs = Sales::whereNotNull('ps')
            ->where('ps', '!=', '')
            ->distinct()
            ->orderBy('ps', 'asc')
            ->pluck('ps')
            ->toArray();

        // 1. Data Monthly Achievement Rate (Umum)
        $targets = SalesTarget::where('tahun', $tahun)->get();
        
        // Sales tahun berjalan per bulan & per PS
        $salesCurrent = Sales::whereYear('tanggal', $tahun)
            ->select('bulan', 'ps', DB::raw('SUM(harga_nett) as total_sales'))
            ->groupBy('bulan', 'ps')
            ->get();
            
        // Sales tahun lalu per bulan (untuk Growth Rate)
        $tahunLalu = (int)$tahun - 1;
        $salesLastYear = Sales::whereYear('tanggal', $tahunLalu)
            ->select('bulan', DB::raw('SUM(harga_nett) as total_sales'))
            ->groupBy('bulan')
            ->pluck('total_sales', 'bulan')
            ->toArray();

        // Menyusun pivot array
        $monthlyAll = [];
        $monthlyPerPs = []; // Struktur: [Bulan => [All => %, PS1 => %, PS2 => %]]
        $allPsAchievement = []; // Struktur: [PS => ['target' => X, 'sales' => Y, 'rate' => Z]]

        foreach ($listPs as $ps) {
            $allPsAchievement[$ps] = ['target' => 0, 'sales' => 0];
        }

        foreach ($this->urutanBulan as $bulan) {
            // Kalkulasi Semua PS (All)
            $targetAll = $targets->where('bulan', $bulan)->sum('target_amount');
            $salesAll = $salesCurrent->where('bulan', $bulan)->sum('total_sales');
            $salesPrev = $salesLastYear[$bulan] ?? 0;
            
            $achievementRate = $targetAll > 0 ? round(($salesAll / $targetAll) * 100) : 0;
            $growthRate = $salesPrev > 0 ? round((($salesAll - $salesPrev) / $salesPrev) * 100) : 0;

            $monthlyAll[$bulan] = [
                'target' => $targetAll,
                'sales' => $salesAll,
                'achievement_rate' => $achievementRate,
                'growth_rate' => $growthRate,
                'sales_last_year' => $salesPrev
            ];

            // Kalkulasi per PS di bulan ini
            $monthlyPerPs[$bulan] = ['All' => $achievementRate];
            foreach ($listPs as $ps) {
                // Prioritaskan target spesifik PS, jika tidak ada fallback ke target general jika perlu (tergantung rule, 
                // tapi karena input target sudah per PS, kita asumsikan ambil yang where('ps', $ps)).
                $targetPs = $targets->where('bulan', $bulan)->where('ps', $ps)->sum('target_amount');
                $salesPs = $salesCurrent->where('bulan', $bulan)->where('ps', $ps)->sum('total_sales');
                
                $ratePs = $targetPs > 0 ? round(($salesPs / $targetPs) * 100) : 0;
                $monthlyPerPs[$bulan][$ps] = $ratePs;

                // Akumulasi total All PS
                $allPsAchievement[$ps]['target'] += $targetPs;
                $allPsAchievement[$ps]['sales'] += $salesPs;
            }
        }

        // Kalkulasi rate untuk allPsAchievement
        foreach ($allPsAchievement as $ps => $data) {
            $allPsAchievement[$ps]['rate'] = $data['target'] > 0 ? round(($data['sales'] / $data['target']) * 100) : 0;
        }

        $listTahun = Sales::whereNotNull('tanggal')
            ->selectRaw('DISTINCT YEAR(tanggal) as tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
            
        if (!in_array(date('Y'), $listTahun)) {
            array_unshift($listTahun, date('Y'));
        }

        $urutanBulan = $this->urutanBulan;

        return view('users.sales.target', compact(
            'tahun', 'tahunLalu', 'listPs', 'urutanBulan', 
            'monthlyAll', 'monthlyPerPs', 'allPsAchievement', 'listTahun'
        ));
    }

    public function storeTarget(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|string',
            'ps' => 'required|string',
            'target_amount' => 'required|numeric|min:0'
        ]);

        $bulanAngka = array_search($request->bulan, $this->urutanBulan) + 1;

        SalesTarget::updateOrCreate(
            [
                'tahun' => $request->tahun,
                'bulan' => $request->bulan,
                'ps' => $request->ps,
            ],
            [
                'bulan_angka' => $bulanAngka,
                'target_amount' => $request->target_amount
            ]
        );

        return redirect()->back()->with('success', 'Target berhasil disimpan!');
    }

    /**
     * Menampilkan halaman monitoring (shell + opsi filter).
     * Data grafik/tabel diambil lewat AJAX ke monitoringData() supaya filter
     * bisa ganti-ganti tanpa reload halaman.
     */
    public function monitoring(Request $request)
    {
        $listPs = Sales::whereNotNull('ps')
            ->where('ps', '!=', '')
            ->distinct()
            ->orderBy('ps', 'asc')
            ->pluck('ps');

        $listCustomer = Sales::whereNotNull('nama_customer')
            ->where('nama_customer', '!=', '')
            ->distinct()
            ->orderBy('nama_customer', 'asc')
            ->pluck('nama_customer');

        $listProduk = Sales::whereNotNull('nama_produk')
            ->where('nama_produk', '!=', '')
            ->distinct()
            ->orderBy('nama_produk', 'asc')
            ->pluck('nama_produk');

        // Urutkan bulan yang benar-benar ada di data sesuai urutan kalender
        $bulanAda = Sales::whereNotNull('bulan')->distinct()->pluck('bulan')->toArray();
        $bulanAda = array_map(fn($b) => ucfirst(strtolower($b)), $bulanAda);
        $listBulan = array_values(array_intersect($this->urutanBulan, $bulanAda));
        
        // Hide future months from the filter dropdown
        $currentMonthIndex = date('n');
        $validMonths = array_slice($this->urutanBulan, 0, $currentMonthIndex);
        $listBulan = array_values(array_intersect($listBulan, $validMonths));

        $tahunAda = Sales::whereNotNull('tanggal')
            ->selectRaw('DISTINCT YEAR(tanggal) as tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return view('users.sales.monitoring', [
            'title'        => 'Monitoring Sales',
            'listCustomer' => $listCustomer,
            'listPs'       => $listPs,
            'listProduk'   => $listProduk,
            'listBulan'    => $listBulan,
            'listTahun'    => $tahunAda,
        ]);
    }

    /**
     * Endpoint AJAX: mengembalikan semua data agregat (JSON) sesuai filter yang dikirim.
     * Query params: tahun, bulan[], ps[], nama_customer[], nama_produk[]
     */
    public function monitoringData(Request $request)
    {
        $tahun         = $request->input('tahun');
        $bulanFilter   = array_filter((array) $request->input('bulan', []));
        $psFilter      = array_filter((array) $request->input('ps', []));
        $customerFilter = array_filter((array) $request->input('nama_customer', []));
        $produkFilter  = array_filter((array) $request->input('nama_produk', []));

        $baseQuery = Sales::query();

        if ($tahun) {
            $baseQuery->whereYear('tanggal', $tahun);
        }
        if (!empty($bulanFilter)) {
            $baseQuery->whereIn('bulan', $bulanFilter);
        }
        if (!empty($psFilter)) {
            $baseQuery->whereIn('ps', $psFilter);
        }
        if (!empty($customerFilter)) {
            $baseQuery->whereIn('nama_customer', $customerFilter);
        }
        if (!empty($produkFilter)) {
            $baseQuery->whereIn('nama_produk', $produkFilter);
        }

        // ---------- Summary Cards ----------
        $summaryQuery = clone $baseQuery;
        $totalNett = (clone $summaryQuery)->sum('harga_nett');
        $totalQty  = (clone $summaryQuery)->sum('qty');

        // ---------- Trend Sales Nett per Bulan ----------
        $trendRawDb = (clone $baseQuery)
            ->select('bulan', DB::raw('SUM(harga_nett) as total'))
            ->groupBy('bulan')
            ->pluck('total', 'bulan');
            
        $trendRaw = [];
        foreach ($trendRawDb as $k => $v) {
            $trendRaw[ucfirst(strtolower($k))] = $v;
        }

        $bulanUrut = $this->urutanBulan;

        $trend = [];
        foreach ($bulanUrut as $b) {
            if (isset($trendRaw[$b])) {
                $trend[] = ['bulan' => $b, 'total' => (float) $trendRaw[$b]];
            }
        }

        // ---------- Target vs Achievement per Bulan ----------
        $targetQuery = SalesTarget::query();
        if ($tahun) {
            $targetQuery->where('tahun', $tahun);
        }
        // Target keseluruhan tim (ps null) dipakai kalau tidak filter PS spesifik,
        // kalau filter PS spesifik & jumlahnya 1, coba pakai target per-PS itu (jika ada), fallback ke target tim.
        if (count($psFilter) === 1) {
            $targetQuery->where(function ($q) use ($psFilter) {
                $q->where('ps', $psFilter[array_key_first($psFilter)])->orWhereNull('ps');
            });
        } else {
            $targetQuery->whereNull('ps');
        }
        $targetRawDb = $targetQuery->pluck('target_amount', 'bulan');
        
        $targetRaw = [];
        foreach ($targetRawDb as $k => $v) {
            $targetRaw[ucfirst(strtolower($k))] = $v;
        }

        $targetVsAchievement = [];
        foreach ($bulanUrut as $b) {
            $actual = (float) ($trendRaw[$b] ?? 0);
            $target = (float) ($targetRaw[$b] ?? 0);
            $targetVsAchievement[] = [
                'bulan'  => $b,
                'target' => $target,
                'actual' => $actual,
                'rate'   => $target > 0 ? round(($actual / $target) * 100, 2) : null,
            ];
        }

        // Achievement rate bulan terakhir yang ada datanya (untuk summary card)
        $achievementRate = null;
        if (!empty($targetVsAchievement)) {
            $last = end($targetVsAchievement);
            $achievementRate = $last['rate'];
        }

        // ---------- Menentukan List Bulan Dinamis ----------
        $listBulanDinamic = $this->urutanBulan;
        if (!empty($bulanFilter)) {
            $listBulanDinamic = array_values(array_intersect($this->urutanBulan, array_map(fn($b) => ucfirst(strtolower($b)), $bulanFilter)));
        } else {
            // Hide future months if querying the current year or no year selected
            if (!$tahun || $tahun == date('Y')) {
                $listBulanDinamic = array_slice($this->urutanBulan, 0, date('n'));
            } else {
                $listBulanDinamic = $this->urutanBulan;
            }
        }

        $formatMatrix = function ($query, $nameField, $subGroupField = null) use ($listBulanDinamic) {
            $selects = [$nameField, 'bulan', DB::raw('SUM(harga_nett) as total_nett'), DB::raw('SUM(qty) as total_qty'), DB::raw('MAX(satuan) as satuan')];
            if ($subGroupField) {
                $selects[] = $subGroupField;
            }
            
            $queryObj = $query->select($selects)
                ->whereNotNull($nameField)
                ->where($nameField, '!=', '');
                
            if ($subGroupField) {
                $queryObj->groupBy($nameField, $subGroupField, 'bulan');
            } else {
                $queryObj->groupBy($nameField, 'bulan');
            }
            
            $raw = $queryObj->get();
                
            $result = [];
            foreach ($raw as $row) {
                $name = $row->{$nameField};
                $bulan = ucfirst(strtolower($row->bulan));
                if (!isset($result[$name])) {
                    $result[$name] = [
                        'nama' => $name,
                        'satuan' => $nameField === 'nama_produk' ? ($row->satuan ?? '') : '',
                        'total_nett' => 0,
                        'total_qty' => 0,
                        'bulanan' => [],
                    ];
                    if ($subGroupField) $result[$name]['sub'] = [];
                    foreach ($listBulanDinamic as $b) {
                        $result[$name]['bulanan'][$b] = ['nett' => 0, 'qty' => 0];
                    }
                }
                $result[$name]['total_nett'] += (float)$row->total_nett;
                $result[$name]['total_qty'] += (int)$row->total_qty;
                if (in_array($bulan, $listBulanDinamic)) {
                    $result[$name]['bulanan'][$bulan]['nett'] += (float)$row->total_nett;
                    $result[$name]['bulanan'][$bulan]['qty'] += (int)$row->total_qty;
                }
                
                if ($subGroupField) {
                    $subName = $row->{$subGroupField};
                    if ($subName) {
                        if (!isset($result[$name]['sub'][$subName])) {
                            $result[$name]['sub'][$subName] = [
                                'nama' => $subName,
                                'satuan' => $subGroupField === 'nama_produk' ? ($row->satuan ?? '') : '',
                                'total_nett' => 0,
                                'total_qty' => 0,
                                'bulanan' => [],
                            ];
                            foreach ($listBulanDinamic as $b) {
                                $result[$name]['sub'][$subName]['bulanan'][$b] = ['nett' => 0, 'qty' => 0];
                            }
                        }
                        $result[$name]['sub'][$subName]['total_nett'] += (float)$row->total_nett;
                        $result[$name]['sub'][$subName]['total_qty'] += (int)$row->total_qty;
                        if (in_array($bulan, $listBulanDinamic)) {
                            $result[$name]['sub'][$subName]['bulanan'][$bulan]['nett'] += (float)$row->total_nett;
                            $result[$name]['sub'][$subName]['bulanan'][$bulan]['qty'] += (int)$row->total_qty;
                        }
                    }
                }
            }
            $result = array_values($result);
            if ($subGroupField) {
                foreach ($result as &$res) {
                    if (isset($res['sub']) && !empty($res['sub'])) {
                        $res['sub'] = array_values($res['sub']);
                        usort($res['sub'], fn($a, $b) => $b['total_nett'] <=> $a['total_nett']);
                    }
                }
            }
            usort($result, fn($a, $b) => $b['total_nett'] <=> $a['total_nett']);
            return $result;
        };

        // ---------- Sales per RS/Customer ----------
        $perCustomer = $formatMatrix(clone $baseQuery, 'nama_customer');

        // ---------- Sales per RS/Customer & Produk ----------
        $perCustomerProduk = $formatMatrix(clone $baseQuery, 'nama_customer', 'nama_produk');

        // ---------- Sales per Produk ----------
        $perProduk = $formatMatrix(clone $baseQuery, 'nama_produk');

        // ---------- Sales per Produk (Khusus Pivot / Cross-Tab) ----------
        $pivotProdukPs = $formatMatrix(clone $baseQuery, 'nama_produk', 'ps');

        // ---------- List Seluruh PS aktif untuk kolom Pivot ----------
        $listPsPivot = (clone $baseQuery)
            ->whereNotNull('ps')
            ->where('ps', '!=', '')
            ->distinct()
            ->orderBy('ps', 'asc')
            ->pluck('ps')
            ->toArray();

        // ---------- Sales per PS ----------
        $perPs = $formatMatrix(clone $baseQuery, 'ps');

        // ---------- Stock Forecast (SF) ----------
        // Formula: rata-rata qty per produk selama 7 bulan terakhir yang ADA datanya, lalu +20%.
        // Menghormati filter produk/customer/ps yang sedang aktif, tapi mengambil 7 bulan terakhir
        // berdasarkan urutan kalender (bukan cuma bulan yang difilter) agar rata-ratanya representatif.
        $sfQuery = Sales::query();
        if ($tahun) {
            $sfQuery->whereYear('tanggal', $tahun);
        }
        if (!empty($psFilter)) {
            $sfQuery->whereIn('ps', $psFilter);
        }
        if (!empty($customerFilter)) {
            $sfQuery->whereIn('nama_customer', $customerFilter);
        }
        if (!empty($produkFilter)) {
            $sfQuery->whereIn('nama_produk', $produkFilter);
        }

        // Ambil qty per produk per bulan
        $qtyPerProdukBulan = $sfQuery
            ->select('nama_produk', 'bulan', DB::raw('SUM(qty) as total_qty'))
            ->whereNotNull('nama_produk')
            ->groupBy('nama_produk', 'bulan')
            ->get()
            ->groupBy('nama_produk');

        // Ambil 7 bulan kalender terakhir yang benar-benar punya data (dari urutanBulan)
        $tujuhBulanTerakhir = array_slice($this->urutanBulan, 0, 7); // fallback default Jan-Jul
        $bulanTersedia = Sales::whereNotNull('bulan')->distinct()->pluck('bulan')->toArray();
        $bulanTersediaUrut = array_values(array_intersect($this->urutanBulan, $bulanTersedia));
        if (count($bulanTersediaUrut) > 0) {
            $tujuhBulanTerakhir = array_slice($bulanTersediaUrut, -7);
        }

        $stockForecast = [];
        foreach ($qtyPerProdukBulan as $produk => $rows) {
            $rowsByBulan = $rows->pluck('total_qty', 'bulan');
            $nilaiTerpakai = [];
            foreach ($tujuhBulanTerakhir as $b) {
                if (isset($rowsByBulan[$b])) {
                    $nilaiTerpakai[] = (float) $rowsByBulan[$b];
                }
            }
            $jumlahBulanTerpakai = count($nilaiTerpakai);
            if ($jumlahBulanTerpakai === 0) {
                continue;
            }
            $avg = array_sum($nilaiTerpakai) / $jumlahBulanTerpakai;
            $forecast = $avg * 1.2;

            $stockForecast[] = [
                'nama_produk'    => $produk,
                'avg_qty'        => round($avg, 2),
                'forecast_qty'   => (int) ceil($forecast),
                'jumlah_bulan'   => $jumlahBulanTerpakai,
            ];
        }
        usort($stockForecast, fn($a, $b) => $b['forecast_qty'] <=> $a['forecast_qty']);

        return response()->json([
            'summary' => [
                'total_nett'       => (float) $totalNett,
                'total_qty'        => (int) $totalQty,
                'achievement_rate' => $achievementRate,
            ],
            'list_bulan'            => $listBulanDinamic,
            'trend'                 => $trend,
            'target_vs_achievement' => $targetVsAchievement,
            'per_customer'          => $perCustomer,
            'per_customer_produk'   => $perCustomerProduk,
            'per_produk'            => $perProduk,
            'per_ps'                => $perPs,
            'pivot_produk_ps'       => $pivotProdukPs,
            'list_ps_pivot'         => $listPsPivot,
            'stock_forecast'        => $stockForecast,
        ]);
    }

    /* =========================================================================
     |  HALAMAN RIWAYAT & KELOLA DATA
     |  ========================================================================= */

    public function history(Request $request)
    {
        $query = Sales::query();

        // Pencarian (search)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_customer', 'like', "%{$search}%")
                  ->orWhere('nama_produk', 'like', "%{$search}%")
                  ->orWhere('ps', 'like', "%{$search}%");
            });
        }

        // Filter by Bulan (using array for multiple or just single string)
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->input('bulan'));
        }

        // Filter by Tahun (using year from tanggal)
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->input('tahun'));
        }

        // Filter by Customer
        if ($request->filled('nama_customer')) {
            $query->where('nama_customer', $request->input('nama_customer'));
        }

        // Filter by Produk
        if ($request->filled('nama_produk')) {
            $query->where('nama_produk', $request->input('nama_produk'));
        }

        // Filter by PS
        if ($request->filled('ps')) {
            $query->where('ps', $request->input('ps'));
        }

        $sales = $query->orderBy('tanggal', 'desc')->paginate(30)->withQueryString();

        // Data for filters
        $bulanAda = Sales::whereNotNull('bulan')->distinct()->pluck('bulan')->toArray();
        $bulanAda = array_map(fn($b) => ucfirst(strtolower($b)), $bulanAda);
        $listBulan = array_values(array_intersect($this->urutanBulan, $bulanAda));
        
        $tahunAda = Sales::whereNotNull('tanggal')
            ->selectRaw('DISTINCT YEAR(tanggal) as tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $listCustomer = Sales::whereNotNull('nama_customer')->where('nama_customer', '!=', '')->distinct()->orderBy('nama_customer', 'asc')->pluck('nama_customer');
        $listProduk = Sales::whereNotNull('nama_produk')->where('nama_produk', '!=', '')->distinct()->orderBy('nama_produk', 'asc')->pluck('nama_produk');
        $listPs = Sales::whereNotNull('ps')->where('ps', '!=', '')->distinct()->orderBy('ps', 'asc')->pluck('ps');

        return view('users.sales.history', [
            'title'     => 'Riwayat Data Sales',
            'sales'     => $sales,
            'listBulan' => $listBulan,
            'listTahun' => $tahunAda,
            'listCustomer' => $listCustomer,
            'listProduk' => $listProduk,
            'listPs' => $listPs,
        ]);
    }

    public function update(Request $request, Sales $sale)
    {
        $request->validate([
            'tanggal'       => 'nullable|date',
            'nama_customer' => 'nullable|string|max:255',
            'nama_produk'   => 'nullable|string|max:255',
            'qty'           => 'nullable|integer|min:1',
            'satuan'        => 'nullable|string|max:50',
            'hna'           => 'nullable|numeric|min:0',
            'diskon'        => 'nullable|numeric|min:0',
            'harga_nett'    => 'nullable|numeric|min:0',
            'bulan'         => 'nullable|string|max:50',
            'ps'            => 'nullable|string|max:255',
        ]);

        $sale->update($request->all());

        return redirect()->back()->with('success', 'Data sales berhasil diperbarui!');
    }

    public function destroy(Sales $sale)
    {
        $sale->delete();

        return redirect()->back()->with('success', 'Data sales berhasil dihapus!');
    }

    /* =========================================================================
     |  HALAMAN VISUALISASI ANALYTICS (POWER BI STYLE)
     |  ========================================================================= */

    public function visualisasi(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulanTerpilih = $request->input('bulan', ''); // '' = semua bulan
        $psTerpilih = $request->input('ps', ''); // '' = semua PS

        $listPs = Sales::whereNotNull('ps')
            ->where('ps', '!=', '')
            ->distinct()
            ->orderBy('ps', 'asc')
            ->pluck('ps')
            ->toArray();

        $listTahun = Sales::whereNotNull('tanggal')
            ->selectRaw('DISTINCT YEAR(tanggal) as tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (!in_array(date('Y'), $listTahun)) {
            array_unshift($listTahun, date('Y'));
        }

        $analytics = $this->getVisualisasiDataPayload($tahun, $bulanTerpilih, $psTerpilih, $listPs);

        return view('users.sales.visualisasi', array_merge([
            'title'         => 'Visualisasi Analytics Sales (Power BI)',
            'tahun'         => $tahun,
            'bulanTerpilih' => $bulanTerpilih,
            'psTerpilih'    => $psTerpilih,
            'listTahun'     => $listTahun,
            'listBulan'     => $this->urutanBulan,
            'listPs'        => $listPs,
        ], $analytics));
    }

    public function visualisasiData(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulanTerpilih = $request->input('bulan', '');
        $psTerpilih = $request->input('ps', '');

        $listPs = Sales::whereNotNull('ps')
            ->where('ps', '!=', '')
            ->distinct()
            ->orderBy('ps', 'asc')
            ->pluck('ps')
            ->toArray();

        $analytics = $this->getVisualisasiDataPayload($tahun, $bulanTerpilih, $psTerpilih, $listPs);

        return response()->json($analytics);
    }

    private function getVisualisasiDataPayload($tahun, $bulanTerpilih, $psTerpilih, $listPs)
    {
        $tahunLalu = (int)$tahun - 1;

        // 1. Ambil target tahun ini
        $targetsQuery = SalesTarget::where('tahun', $tahun);
        if ($psTerpilih) {
            $targetsQuery->where('ps', $psTerpilih);
        }
        $targets = $targetsQuery->get();

        // Target tahun lalu
        $targetsLastYear = SalesTarget::where('tahun', $tahunLalu)->get();

        // 2. Sales tahun ini
        $salesCurrentQuery = Sales::whereYear('tanggal', $tahun);
        if ($psTerpilih) {
            $salesCurrentQuery->where('ps', $psTerpilih);
        }
        $salesCurrent = $salesCurrentQuery
            ->select('bulan', 'ps', 'nama_produk', DB::raw('SUM(harga_nett) as total_sales'), DB::raw('SUM(qty) as total_qty'))
            ->groupBy('bulan', 'ps', 'nama_produk')
            ->get();

        // 3. Sales tahun lalu
        $salesLastYearQuery = Sales::whereYear('tanggal', $tahunLalu);
        if ($psTerpilih) {
            $salesLastYearQuery->where('ps', $psTerpilih);
        }
        $salesLastYear = $salesLastYearQuery
            ->select('bulan', 'ps', DB::raw('SUM(harga_nett) as total_sales'))
            ->groupBy('bulan', 'ps')
            ->get();

        // A. Monthly Achievement Rate & YoY Growth
        $monthlyOverview = [];
        $totalTargetYear = 0;
        $totalSalesYear = 0;
        $totalSalesLastYear = 0;

        foreach ($this->urutanBulan as $b) {
            $tVal = $targets->where('bulan', $b)->sum('target_amount');
            $sVal = $salesCurrent->where('bulan', $b)->sum('total_sales');
            $sPrevVal = $salesLastYear->where('bulan', $b)->sum('total_sales');

            $achRate = $tVal > 0 ? round(($sVal / $tVal) * 100, 1) : 0;
            $growthRate = $sPrevVal > 0 ? round((($sVal - $sPrevVal) / $sPrevVal) * 100, 1) : 0;

            $monthlyOverview[$b] = [
                'target'           => (float)$tVal,
                'sales'            => (float)$sVal,
                'achievement_rate' => $achRate,
                'sales_last_year'  => (float)$sPrevVal,
                'growth_rate'      => $growthRate,
            ];

            $totalTargetYear += $tVal;
            $totalSalesYear += $sVal;
            $totalSalesLastYear += $sPrevVal;
        }

        // B. Per PS Performance (Monthly / Selected Month)
        $targetBulan = $bulanTerpilih;
        if (!$targetBulan) {
            $lastSalesMonth = $salesCurrent->pluck('bulan')->last();
            $targetBulan = $lastSalesMonth ?: 'Juli';
        }

        $bulanPrevIndex = array_search($targetBulan, $this->urutanBulan);
        $bulanPrevName = $bulanPrevIndex > 0 ? $this->urutanBulan[$bulanPrevIndex - 1] : null;

        $psPerformance = [];
        foreach ($listPs as $ps) {
            $tPs = $targets->where('bulan', $targetBulan)->where('ps', $ps)->sum('target_amount');
            $sPs = $salesCurrent->where('bulan', $targetBulan)->where('ps', $ps)->sum('total_sales');
            
            $sPsPrevMonth = $bulanPrevName ? $salesCurrent->where('bulan', $bulanPrevName)->where('ps', $ps)->sum('total_sales') : 0;
            
            $achPs = $tPs > 0 ? round(($sPs / $tPs) * 100, 1) : 0;
            $momGrowth = $sPsPrevMonth > 0 ? round((($sPs - $sPsPrevMonth) / $sPsPrevMonth) * 100, 1) : 0;

            $psPerformance[$ps] = [
                'target'            => (float)$tPs,
                'sales'             => (float)$sPs,
                'achievement_rate'  => $achPs,
                'sales_last_month'  => (float)$sPsPrevMonth,
                'growth_last_month' => $momGrowth,
            ];
        }

        // C. Cumulative Achievement Rate per PS
        $limitBulanIndex = $bulanTerpilih ? array_search($bulanTerpilih, $this->urutanBulan) : (date('Y') == $tahun ? date('n') - 1 : 11);
        $bulanAkumulasi = array_slice($this->urutanBulan, 0, max(1, $limitBulanIndex + 1));

        $psCumulative = [];
        foreach ($listPs as $ps) {
            $cumTarget = $targets->whereIn('bulan', $bulanAkumulasi)->where('ps', $ps)->sum('target_amount');
            $cumSales = $salesCurrent->whereIn('bulan', $bulanAkumulasi)->where('ps', $ps)->sum('total_sales');
            $cumSalesLastYear = $salesLastYear->whereIn('bulan', $bulanAkumulasi)->where('ps', $ps)->sum('total_sales');

            $cumAchRate = $cumTarget > 0 ? round(($cumSales / $cumTarget) * 100, 1) : 0;
            $cumGrowthRate = $cumSalesLastYear > 0 ? round((($cumSales - $cumSalesLastYear) / $cumSalesLastYear) * 100, 1) : 0;

            $monthlySalesPs = [];
            foreach ($this->urutanBulan as $b) {
                $monthlySalesPs[$b] = (float)$salesCurrent->where('bulan', $b)->where('ps', $ps)->sum('total_sales');
            }

            $psCumulative[$ps] = [
                'cum_target'      => (float)$cumTarget,
                'cum_sales'       => (float)$cumSales,
                'cum_ach_rate'    => $cumAchRate,
                'cum_growth_rate' => $cumGrowthRate,
                'monthly_sales'   => $monthlySalesPs
            ];
        }

        // D. Sales by Product Category per PS
        $productsQuery = Sales::whereYear('tanggal', $tahun);
        if ($bulanTerpilih) {
            $productsQuery->where('bulan', $bulanTerpilih);
        }
        if ($psTerpilih) {
            $productsQuery->where('ps', $psTerpilih);
        }

        $productSalesRaw = $productsQuery
            ->select('nama_produk', 'ps', DB::raw('SUM(harga_nett) as total_nett'), DB::raw('SUM(qty) as total_qty'))
            ->whereNotNull('nama_produk')
            ->where('nama_produk', '!=', '')
            ->groupBy('nama_produk', 'ps')
            ->get();

        $productCategoryPs = [];
        foreach ($productSalesRaw as $row) {
            $prod = $row->nama_produk;
            $psName = $row->ps ?: 'Other';
            if (!isset($productCategoryPs[$prod])) {
                $productCategoryPs[$prod] = [
                    'nama_produk' => $prod,
                    'total_nett'  => 0,
                    'per_ps'      => []
                ];
                foreach ($listPs as $p) {
                    $productCategoryPs[$prod]['per_ps'][$p] = 0;
                }
            }
            $productCategoryPs[$prod]['total_nett'] += (float)$row->total_nett;
            if (isset($productCategoryPs[$prod]['per_ps'][$psName])) {
                $productCategoryPs[$prod]['per_ps'][$psName] += (float)$row->total_nett;
            }
        }
        usort($productCategoryPs, fn($a, $b) => $b['total_nett'] <=> $a['total_nett']);
        $topProductCategoryPs = array_slice($productCategoryPs, 0, 15);

        $overallAchievement = $totalTargetYear > 0 ? round(($totalSalesYear / $totalTargetYear) * 100, 1) : 0;
        $overallGrowth = $totalSalesLastYear > 0 ? round((($totalSalesYear - $totalSalesLastYear) / $totalSalesLastYear) * 100, 1) : 0;

        return [
            'summary' => [
                'total_target'        => (float)$totalTargetYear,
                'total_sales'         => (float)$totalSalesYear,
                'overall_achievement' => $overallAchievement,
                'overall_growth'      => $overallGrowth,
                'bulan_aktif'         => $targetBulan,
            ],
            'monthlyOverview'      => $monthlyOverview,
            'psPerformance'        => $psPerformance,
            'psCumulative'         => $psCumulative,
            'topProductCategoryPs' => $topProductCategoryPs,
            'allProductsCount'     => count($productCategoryPs),
        ];
    }
}
