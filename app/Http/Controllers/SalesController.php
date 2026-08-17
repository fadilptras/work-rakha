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
    // akses untuk direktur, kepala divisi marketing, admin support, dan test
    private function hasFullSalesAccess()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) return false;

        $jabatan = strtolower($user->jabatan ?? '');
        $divisi = strtolower($user->divisi ?? '');

        $isTopManagement = \Illuminate\Support\Str::contains($jabatan, 'direktur') || $divisi === 'top management';
        $isKepalaDivisiMO = (($user->is_kepala_divisi == 1) || \Illuminate\Support\Str::contains($jabatan, 'kepala')) && in_array($divisi, ['marketing dan operasional']);
        $isAdminMarketing = \Illuminate\Support\Str::contains($jabatan, 'admin support');
        $isTest = \Illuminate\Support\Str::contains($jabatan, 'test');

        return $isTopManagement || $isKepalaDivisiMO || $isAdminMarketing || $isTest;
    }

    // akses div marketing & operasional
    private function hasAnySalesAccess()
    {
        if ($this->hasFullSalesAccess()) return true;

        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) return false;

        $divisi = strtolower($user->divisi ?? '');
        return in_array($divisi, ['marketing dan operasional']);
    }

    // urutan bulan standar untuk sorting dan label
    protected array $urutanBulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    public function index(Request $request)
    {
        return view('users.sales.dashboard')->with([
            'title' => 'Sales Command Center',
            'hasFullAccess' => $this->hasFullSalesAccess(),
            'hasAnyAccess' => $this->hasAnySalesAccess()
        ]);
    }

    public function analytics(Request $request)
    {
        if (!$this->hasFullSalesAccess()) abort(403, 'Anda tidak memiliki hak akses ke halaman Analitik Penjualan.');

        // data filter dashboard
        $listPs = Sales::whereNotNull('ps')->where('ps', '!=', '')->distinct()->orderBy('ps', 'asc')->pluck('ps')->toArray();
        $listCustomer = Sales::whereNotNull('nama_customer')->where('nama_customer', '!=', '')->distinct()->orderBy('nama_customer', 'asc')->pluck('nama_customer');
        $listProduk = Sales::whereNotNull('nama_produk')->where('nama_produk', '!=', '')->distinct()->orderBy('nama_produk', 'asc')->pluck('nama_produk');

        $bulanAda = Sales::whereNotNull('bulan')->distinct()->pluck('bulan')->toArray();
        $bulanAda = array_map(fn($b) => ucfirst(strtolower($b)), $bulanAda);
        $listBulan = array_values(array_intersect($this->urutanBulan, $bulanAda));
        
        $currentMonthIndex = date('n');
        $validMonths = array_slice($this->urutanBulan, 0, $currentMonthIndex);
        $listBulan = array_values(array_intersect($listBulan, $validMonths));

        $listTahun = Sales::whereNotNull('tanggal')->selectRaw('DISTINCT YEAR(tanggal) as tahun')->orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        if (!in_array(date('Y'), $listTahun)) {
            array_unshift($listTahun, date('Y'));
        }

        // data target sales
        $tahun = $request->input('tahun', date('Y'));
        $tahunLalu = (int)$tahun - 1;
        
        $targets = SalesTarget::where('tahun', $tahun)->get();
        $salesCurrent = Sales::whereYear('tanggal', $tahun)
            ->select('bulan', 'ps', DB::raw('SUM(harga_nett) as total_sales'))
            ->groupBy('bulan', 'ps')->get();
            
        $salesCurrent->transform(function ($item) {
            $item->bulan = ucfirst(strtolower($item->bulan));
            return $item;
        });

        $salesLastYearRaw = Sales::whereYear('tanggal', $tahunLalu)
            ->select('bulan', DB::raw('SUM(harga_nett) as total_sales'))
            ->groupBy('bulan')->get();
            
        $salesLastYear = [];
        foreach ($salesLastYearRaw as $row) {
            $b = ucfirst(strtolower($row->bulan));
            $salesLastYear[$b] = ($salesLastYear[$b] ?? 0) + $row->total_sales;
        }

        $monthlyAll = [];
        $monthlyPerPs = [];
        $allPsAchievement = [];
        foreach ($listPs as $ps) {
            $allPsAchievement[$ps] = ['target' => 0, 'sales' => 0];
        }

        foreach ($this->urutanBulan as $bulan) {
            $targetAll = $targets->where('bulan', $bulan)->sum('target_amount');
            $salesAll = $salesCurrent->where('bulan', $bulan)->sum('total_sales');
            $salesPrev = $salesLastYear[$bulan] ?? 0;
            
            $achievementRate = $targetAll > 0 ? round(($salesAll / $targetAll) * 100, 2) : 0;
            $growthRate = $salesPrev > 0 ? round((($salesAll - $salesPrev) / $salesPrev) * 100, 2) : 0;

            $monthlyAll[$bulan] = [
                'target' => $targetAll,
                'sales' => $salesAll,
                'achievement_rate' => $achievementRate,
                'growth_rate' => $growthRate,
                'sales_last_year' => $salesPrev
            ];

            $monthlyPerPs[$bulan] = ['All' => $achievementRate];
            foreach ($listPs as $ps) {
                $targetPs = $targets->where('bulan', $bulan)->where('ps', $ps)->sum('target_amount');
                $salesPs = $salesCurrent->where('bulan', $bulan)->where('ps', $ps)->sum('total_sales');
                
                $ratePs = $targetPs > 0 ? round(($salesPs / $targetPs) * 100, 2) : 0;
                $monthlyPerPs[$bulan][$ps] = [
                    'rate' => $ratePs,
                    'target' => $targetPs,
                    'sales' => $salesPs
                ];

                $allPsAchievement[$ps]['target'] += $targetPs;
                $allPsAchievement[$ps]['sales'] += $salesPs;
            }
        }
        foreach ($allPsAchievement as $ps => $data) {
            $allPsAchievement[$ps]['rate'] = $data['target'] > 0 ? round(($data['sales'] / $data['target']) * 100, 2) : 0;
        }

        // data visualisasi power bi
        $bulanTerpilih = $request->input('bulan', '');
        $psTerpilih = $request->input('ps', '');
        $analyticsData = $this->getVisualisasiDataPayload($tahun, $bulanTerpilih, $psTerpilih, $listPs);

        $urutanBulan = $this->urutanBulan;
        $historySales = Sales::select(
            DB::raw('YEAR(tanggal) as tahun'),
            DB::raw('SUM(CASE WHEN bulan = "Januari" THEN harga_nett ELSE 0 END) as jan'),
            DB::raw('SUM(CASE WHEN bulan = "Februari" THEN harga_nett ELSE 0 END) as feb'),
            DB::raw('SUM(CASE WHEN bulan = "Maret" THEN harga_nett ELSE 0 END) as mar'),
            DB::raw('SUM(CASE WHEN bulan = "April" THEN harga_nett ELSE 0 END) as apr'),
            DB::raw('SUM(CASE WHEN bulan = "Mei" THEN harga_nett ELSE 0 END) as mei'),
            DB::raw('SUM(CASE WHEN bulan = "Juni" THEN harga_nett ELSE 0 END) as jun'),
            DB::raw('SUM(CASE WHEN bulan = "Juli" THEN harga_nett ELSE 0 END) as jul'),
            DB::raw('SUM(CASE WHEN bulan = "Agustus" THEN harga_nett ELSE 0 END) as agu'),
            DB::raw('SUM(CASE WHEN bulan = "September" THEN harga_nett ELSE 0 END) as sep'),
            DB::raw('SUM(CASE WHEN bulan = "Oktober" THEN harga_nett ELSE 0 END) as okt'),
            DB::raw('SUM(CASE WHEN bulan = "November" THEN harga_nett ELSE 0 END) as nov'),
            DB::raw('SUM(CASE WHEN bulan = "Desember" THEN harga_nett ELSE 0 END) as des')
        )->whereNotNull('tanggal')->groupBy(DB::raw('YEAR(tanggal)'))->orderBy(DB::raw('YEAR(tanggal)'), 'desc')->get();

        return view('users.sales.analytics', array_merge(compact(
            'listPs', 'listCustomer', 'listProduk', 'listBulan', 'listTahun',
            'tahun', 'tahunLalu', 'monthlyAll', 'monthlyPerPs', 'allPsAchievement', 'urutanBulan',
            'bulanTerpilih', 'psTerpilih', 'targets', 'historySales'
        ), $analyticsData))->with('title', 'Sales Analytics & Target');
    }

    public function monthly(Request $request)
    {
        if (!$this->hasAnySalesAccess()) abort(403, 'Anda tidak memiliki hak akses ke halaman Monthly Monitoring.');
        $tahun = $request->input('tahun', date('Y'));
        return view('users.sales.monthly', compact('tahun'));
    }

    // simpan data dari form manual
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
            'ps'            => 'nullable|string|max:255',
        ]);

        $data = $request->except('bulan');
        if ($request->filled('tanggal')) {
            $data['bulan'] = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'][date('m', strtotime($request->tanggal))];
        }

        Sales::create($data);

        return redirect()->back()->with('success', 'Data sales manual berhasil disimpan!');
    }

    // import data dari excel
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240' // max 10MB
        ]);

        try {
            $import = new SalesImport();
            Excel::import($import, $request->file('file'));
            
            $months = implode(', ', $import->refreshedMonths);
            $count = number_format($import->importedCount, 0, ',', '.');
            
            if ($import->importedCount > 0) {
                return redirect()->back()->with('success', "Sukses! $count baris data telah diimpor, me-refresh data untuk periode: $months.");
            } else {
                return redirect()->back()->with('success', 'File berhasil diproses namun tidak ada baris data valid yang diimpor.');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Upload Excel Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return redirect()->back()->with('error', 'Gagal mengimpor data! Pastikan format tanggal dan angka di Excel sudah benar. (Info sistem: ' . $e->getMessage() . ')');
        }
    }

    // download template excel/csv
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            
            // Baris 1: Header Kolom Asli (Wajib ada)
            fputcsv($file, [
                'tanggal', 'nama_customer', 'nama_produk', 'qty', 'satuan', 'hna', 'diskon', 'harga_nett', 'ps'
            ]);
            
            // Baris 2: Petunjuk Format (Akan otomatis di-skip oleh sistem import karena tanggal tidak valid)
            fputcsv($file, [
                'FORMAT WAJIB: Bln/Tgl/Tahun', 
                'Wajib Diisi', 
                'Wajib Diisi', 
                'Angka', 
                'Teks', 
                'Format Bebas (Cth: Rp 529.500)', 
                'Format Bebas (Cth: 12.69%)', 
                'Format Bebas (Cth: Rp 32.361.500)', 
                'Teks (Cth: Arief)'
            ]);
            
            // Baris 3: Contoh Data Benar (Bisa langsung Anda timpa/hapus)
            fputcsv($file, [
                '8/18/2026', 
                'RSUD SAYANG', 
                'RAKHA Kasa Katun Premium', 
                '70', 
                'Polybag', 
                'Rp 529.500', 
                '12.69%', 
                'Rp 32.361.500', 
                'Arief'
            ]);
            
            fclose($file);
        };

        return response()->streamDownload($callback, 'Template_Import_Sales_'.date('Ymd').'.csv', $headers);
    }

    // export data ke csv
    public function export(Request $request)
    {
        if (!$this->hasFullSalesAccess()) abort(403, 'Anda tidak memiliki hak akses untuk export Data Sales.');
        $query = Sales::query();

        // pencarian (search)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_customer', 'like', "%{$search}%")
                  ->orWhere('nama_produk', 'like', "%{$search}%")
                  ->orWhere('ps', 'like', "%{$search}%");
            });
        }

        // filter tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        // filter bulan
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->input('bulan'));
        }

        // filter tahun
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->input('tahun'));
        }

        // filter customer
        if ($request->filled('nama_customer')) {
            $query->where('nama_customer', $request->input('nama_customer'));
        }

        // filter produk
        if ($request->filled('nama_produk')) {
            $query->where('nama_produk', $request->input('nama_produk'));
        }

        // filter ps
        if ($request->filled('ps')) {
            $query->where('ps', $request->input('ps'));
        }

        $sort = $request->input('sort', 'terbaru');
        if ($sort === 'terlama') {
            $query->orderBy('tanggal', 'asc');
        } elseif ($sort === 'tertinggi') {
            $query->orderBy('harga_nett', 'desc');
        } elseif ($sort === 'terendah') {
            $query->orderBy('harga_nett', 'asc');
        } else {
            $query->orderBy('tanggal', 'desc');
        }

        $sales = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
        ];

        $callback = function () use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'tanggal', 'nama_customer', 'nama_produk', 'qty', 'satuan', 'hna', 'diskon', 'harga_nett', 'ps'
            ]);
            
            foreach ($sales as $item) {
                $diskon_val = $item->diskon;
                if (is_numeric($diskon_val) && $diskon_val > 0 && $diskon_val <= 1) {
                    $diskon_val = $diskon_val * 100;
                }
                
                fputcsv($file, [
                    $item->tanggal ? date('Y-m-d', strtotime($item->tanggal)) : '',
                    $item->nama_customer ?? '',
                    $item->nama_produk ?? '',
                    $item->qty ?? '',
                    $item->satuan ?? '',
                    isset($item->hna) ? 'Rp ' . number_format($item->hna, 0, ',', '.') : '',
                    (isset($item->diskon) && $item->diskon !== '') ? $diskon_val . '%' : '',
                    isset($item->harga_nett) ? 'Rp ' . number_format($item->harga_nett, 0, ',', '.') : '',
                    $item->ps ?? ''
                ]);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'Export_Data_Sales_'.date('YmdHis').'.csv', $headers);
    }

    // ajax endpoint untuk data agregat dashboard
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
            if (in_array('Sales Team', $psFilter) && in_array('Office', $psFilter)) {
                // ALL, do nothing
            } elseif (in_array('Sales Team', $psFilter)) {
                $baseQuery->where('ps', '!=', 'Office');
            } elseif (in_array('Office', $psFilter)) {
                $baseQuery->where('ps', 'Office');
            }
        }
        if (!empty($customerFilter)) {
            $baseQuery->whereIn('nama_customer', $customerFilter);
        }
        if (!empty($produkFilter)) {
            $baseQuery->whereIn('nama_produk', $produkFilter);
        }

        // summary cards
        $summaryQuery = clone $baseQuery;
        $totalNett = (clone $summaryQuery)->sum('harga_nett');
        $totalQty  = (clone $summaryQuery)->sum('qty');
        $totalCustomer = (clone $summaryQuery)->whereNotNull('nama_customer')->where('nama_customer', '!=', '')->distinct('nama_customer')->count('nama_customer');
        $totalProduk = (clone $summaryQuery)->whereNotNull('nama_produk')->where('nama_produk', '!=', '')->distinct('nama_produk')->count('nama_produk');

        // trend sales per bulan
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

        // target vs achievement per bulan
        $targetQuery = SalesTarget::query();
        if ($tahun) {
            $targetQuery->where('tahun', $tahun);
        }
        
        if (!empty($psFilter)) {
            if (in_array('Sales Team', $psFilter) && in_array('Office', $psFilter)) {
                // ALL, do nothing
            } elseif (in_array('Sales Team', $psFilter)) {
                $targetQuery->where('ps', '!=', 'Office');
            } elseif (in_array('Office', $psFilter)) {
                $targetQuery->where('ps', 'Office');
            }
        }
        
        // Sum targets per month since they are stored per PS
        $targetRawDb = $targetQuery->select('bulan', DB::raw('SUM(target_amount) as total_target'))
            ->groupBy('bulan')
            ->pluck('total_target', 'bulan');
        
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

        // list bulan dinamis
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

        // achievement rate keseluruhan
        $sumTarget = 0;
        $sumActual = 0;
        foreach ($listBulanDinamic as $b) {
            $sumTarget += (float) ($targetRaw[$b] ?? 0);
            $sumActual += (float) ($trendRaw[$b] ?? 0);
        }
        $achievementRate = $sumTarget > 0 ? round(($sumActual / $sumTarget) * 100, 2) : 0;

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

        // sales per customer
        $perCustomer = $formatMatrix(clone $baseQuery, 'nama_customer');

        // sales per customer & produk
        $perCustomerProduk = $formatMatrix(clone $baseQuery, 'nama_customer', 'nama_produk');

        // sales per produk
        $perProduk = $formatMatrix(clone $baseQuery, 'nama_produk');

        // sales per produk (pivot)
        $pivotProdukPs = $formatMatrix(clone $baseQuery, 'nama_produk', 'ps');

        // list ps aktif (pivot)
        $listPsPivot = (clone $baseQuery)
            ->whereNotNull('ps')
            ->where('ps', '!=', '')
            ->distinct()
            ->orderBy('ps', 'asc')
            ->pluck('ps')
            ->toArray();

        // sales per ps
        $perPs = $formatMatrix(clone $baseQuery, 'ps');

        // stock forecast (rata-rata 7 bulan terakhir + 20%)
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

        // qty per produk per bulan
        $qtyPerProdukBulan = $sfQuery
            ->select('nama_produk', 'bulan', DB::raw('SUM(qty) as total_qty'))
            ->whereNotNull('nama_produk')
            ->groupBy('nama_produk', 'bulan')
            ->get()
            ->groupBy('nama_produk');

        // 7 bulan terakhir yang ada data
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
                'total_customer'   => $totalCustomer,
                'total_produk'     => $totalProduk,
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

    // --- kelola data (crud, import, export) ---
    public function manage(Request $request)
    {
        if (!$this->hasFullSalesAccess()) abort(403, 'Anda tidak memiliki hak akses ke halaman Kelola Data Sales.');
        $query = Sales::query();

        // pencarian (search)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_customer', 'like', "%{$search}%")
                  ->orWhere('nama_produk', 'like', "%{$search}%")
                  ->orWhere('ps', 'like', "%{$search}%");
            });
        }

        // filter tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        // filter bulan
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->input('bulan'));
        }

        // filter tahun
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->input('tahun'));
        }

        // filter customer
        if ($request->filled('nama_customer')) {
            $query->where('nama_customer', $request->input('nama_customer'));
        }

        // filter produk
        if ($request->filled('nama_produk')) {
            $query->where('nama_produk', $request->input('nama_produk'));
        }

        // filter ps
        if ($request->filled('ps')) {
            $query->where('ps', $request->input('ps'));
        }

        $sort = $request->input('sort', 'terbaru');
        if ($sort === 'terlama') {
            $query->orderBy('tanggal', 'asc');
        } elseif ($sort === 'tertinggi') {
            $query->orderBy('harga_nett', 'desc');
        } elseif ($sort === 'terendah') {
            $query->orderBy('harga_nett', 'asc');
        } else {
            $query->orderBy('tanggal', 'desc');
        }

        $sales = $query->paginate(30)->withQueryString();

        // data untuk filter
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
        $listSatuan = Sales::whereNotNull('satuan')->where('satuan', '!=', '')->distinct()->orderBy('satuan', 'asc')->pluck('satuan');

        return view('users.sales.manage', [
            'title'     => 'Kelola Data Sales',
            'sales'     => $sales,
            'listBulan' => $listBulan,
            'listTahun' => $tahunAda,
            'listCustomer' => $listCustomer,
            'listProduk' => $listProduk,
            'listPs' => $listPs,
            'listSatuan' => $listSatuan,
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
            'ps'            => 'nullable|string|max:255',
        ]);

        $data = $request->except('bulan');
        if ($request->filled('tanggal')) {
            $data['bulan'] = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'][date('m', strtotime($request->tanggal))];
        }

        $sale->update($data);

        return redirect()->back()->with('success', 'Data sales berhasil diperbarui!');
    }

    public function destroy(Sales $sale)
    {
        $sale->delete();

        return redirect()->back()->with('success', 'Data sales berhasil dihapus!');
    }

    // --- target & visualisasi helpers ---
    public function storeTarget(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'nullable|string',
            'targets' => 'nullable|array',
            'ps' => 'nullable|string',
            'target_amount' => 'nullable|numeric|min:0',
            'sales_last_year_amount' => 'nullable|numeric|min:0',
            'form_type' => 'nullable|string'
        ]);

        $targetTahun = $request->form_type === 'history' ? (int)$request->tahun + 1 : $request->tahun;
        $ps = $request->ps;

        if ($request->has('targets') && is_array($request->targets)) {
            // input multiple bulan (form target)
            foreach ($request->targets as $bulan => $amount) {
                $bulanAngka = array_search($bulan, $this->urutanBulan) + 1;
                // clean up amount from non-numeric characters if necessary, but we format via JS and store raw hidden
                $targetAmount = $amount !== null && $amount !== '' ? (float)$amount : 0;
                
                $existing = SalesTarget::where([
                    'tahun' => $targetTahun,
                    'bulan' => $bulan,
                    'ps' => $ps,
                ])->first();

                // simpan riwayat tahun lalu
                $lastYearAmount = $existing->sales_last_year_amount ?? 0;

                SalesTarget::updateOrCreate(
                    [
                        'tahun' => $targetTahun,
                        'bulan' => $bulan,
                        'ps' => $ps,
                    ],
                    [
                        'bulan_angka' => $bulanAngka,
                        'target_amount' => $targetAmount,
                        'sales_last_year_amount' => $lastYearAmount
                    ]
                );
            }
        } else {
            // input single bulan (form history)
            $bulanAngka = array_search($request->bulan, $this->urutanBulan) + 1;

            $existing = SalesTarget::where([
                'tahun' => $targetTahun,
                'bulan' => $request->bulan,
                'ps' => $ps,
            ])->first();

            $targetAmount = $request->has('target_amount') && $request->target_amount !== null ? $request->target_amount : ($existing->target_amount ?? 0);
            $lastYearAmount = $request->has('sales_last_year_amount') && $request->sales_last_year_amount !== null ? $request->sales_last_year_amount : ($existing->sales_last_year_amount ?? 0);

            SalesTarget::updateOrCreate(
                [
                    'tahun' => $targetTahun,
                    'bulan' => $request->bulan,
                    'ps' => $ps,
                ],
                [
                    'bulan_angka' => $bulanAngka,
                    'target_amount' => $targetAmount,
                    'sales_last_year_amount' => $lastYearAmount
                ]
            );
        }

        $msg = $request->form_type == 'history' ? 'Riwayat Sales Tahun Lalu berhasil disimpan!' : 'Target berhasil disimpan!';
        $activeTab = $request->form_type == 'history' ? 'tab-history' : 'tab-tgt';
        return redirect()->back()->with('success', $msg)->with('active_tab', $activeTab);
    }

    public function monthlyDetailData(Request $request)
    {
        if (!$this->hasAnySalesAccess()) abort(403, 'Unauthorized');

        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan');

        if (!$bulan) {
            return response()->json(['error' => 'Bulan is required'], 400);
        }

        $query = Sales::whereYear('tanggal', $tahun)->where('bulan', $bulan);
        
        // filter ps office untuk user dengan akses parsial
        if (!$this->hasFullSalesAccess()) {
            $query->whereRaw("LOWER(ps) != 'office'");
        }

        // group 1: pdu (ps -> tanggal -> customer -> produk)
        $pduRaw = (clone $query)->select('ps', 'tanggal', 'nama_customer', 'nama_produk', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(harga_nett) as total_nett'))
            ->groupBy('ps', 'tanggal', 'nama_customer', 'nama_produk')
            ->orderBy('ps')->orderBy('tanggal')->orderBy('nama_customer')->orderBy('nama_produk')
            ->get();

        $pdu = [];
        foreach ($pduRaw as $row) {
            $ps = $row->ps ?: 'Lainnya';
            $tgl = date('d/m/Y', strtotime($row->tanggal));
            $cust = $row->nama_customer ?: 'Unknown';
            $prod = $row->nama_produk ?: 'Unknown';

            if (!isset($pdu[$ps])) $pdu[$ps] = ['nama' => $ps, 'total_qty' => 0, 'total_nett' => 0, 'tanggal' => []];
            if (!isset($pdu[$ps]['tanggal'][$tgl])) $pdu[$ps]['tanggal'][$tgl] = ['nama' => $tgl, 'total_qty' => 0, 'total_nett' => 0, 'customer' => []];
            if (!isset($pdu[$ps]['tanggal'][$tgl]['customer'][$cust])) $pdu[$ps]['tanggal'][$tgl]['customer'][$cust] = ['nama' => $cust, 'total_qty' => 0, 'total_nett' => 0, 'produk' => []];

            $pdu[$ps]['tanggal'][$tgl]['customer'][$cust]['produk'][] = [
                'nama' => $prod,
                'qty' => (int)$row->total_qty,
                'nett' => (float)$row->total_nett
            ];

            $pdu[$ps]['tanggal'][$tgl]['customer'][$cust]['total_qty'] += $row->total_qty;
            $pdu[$ps]['tanggal'][$tgl]['customer'][$cust]['total_nett'] += $row->total_nett;
            
            $pdu[$ps]['tanggal'][$tgl]['total_qty'] += $row->total_qty;
            $pdu[$ps]['tanggal'][$tgl]['total_nett'] += $row->total_nett;
            
            $pdu[$ps]['total_qty'] += $row->total_qty;
            $pdu[$ps]['total_nett'] += $row->total_nett;
        }

        $targetData = \App\Models\SalesTarget::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->get()
            ->keyBy('ps');

        $bulanIndex = array_search($bulan, $this->urutanBulan);
        if ($bulanIndex === false) $bulanIndex = 0;
        
        $tahunPrevMonth = $tahun;
        if ($bulanIndex == 0) { // Januari
            $bulanPrev = 'Desember';
            $tahunPrevMonth = $tahun - 1;
        } else {
            $bulanPrev = $this->urutanBulan[$bulanIndex - 1];
        }

        $salesPrevMonthQuery = Sales::whereYear('tanggal', $tahunPrevMonth)->where('bulan', $bulanPrev);
        if (!$this->hasFullSalesAccess()) {
            $salesPrevMonthQuery->whereRaw("LOWER(ps) != 'office'");
        }
        $salesPrevMonth = $salesPrevMonthQuery->select('ps', DB::raw('SUM(harga_nett) as total_sales'))->groupBy('ps')->get()->keyBy('ps');

        $pduList = array_values($pdu);
        foreach ($pduList as &$psData) {
            $psData['tanggal'] = array_values($psData['tanggal']);
            foreach ($psData['tanggal'] as &$tglData) {
                $tglData['customer'] = array_values($tglData['customer']);
            }
            $targetObj = $targetData->get($psData['nama']);
            $psData['target_amount'] = $targetObj ? (float)$targetObj->target_amount : 0;
            
            $sPrevValActual = isset($salesPrevMonth[$psData['nama']]) ? (float)$salesPrevMonth[$psData['nama']]->total_sales : 0;
            
            $sVal = $psData['total_nett'];
            $growthRate = $sPrevValActual > 0 ? round((($sVal - $sPrevValActual) / $sPrevValActual) * 100, 1) : 0;
            if ($sPrevValActual == 0 && $sVal > 0) $growthRate = 100;
            $psData['growth_rate'] = $growthRate;
        }

        // group 2: by outlet (ps -> customer)
        $outletRaw = (clone $query)->select('ps', 'nama_customer', DB::raw('SUM(harga_nett) as total_nett'))
            ->groupBy('ps', 'nama_customer')
            ->orderBy('ps')->orderBy('total_nett', 'desc')
            ->get();
        $outlet = [];
        foreach ($outletRaw as $row) {
            $ps = $row->ps ?: 'Lainnya';
            $cust = $row->nama_customer ?: 'Unknown';
            if (!isset($outlet[$ps])) $outlet[$ps] = ['nama' => $ps, 'total_nett' => 0, 'customer' => []];
            $outlet[$ps]['customer'][] = [
                'nama' => $cust,
                'nett' => (float)$row->total_nett
            ];
            $outlet[$ps]['total_nett'] += $row->total_nett;
        }

        // group 3: by product (ps -> produk)
        $productRaw = (clone $query)->select('ps', 'nama_produk', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(harga_nett) as total_nett'))
            ->groupBy('ps', 'nama_produk')
            ->orderBy('ps')->orderBy('total_nett', 'desc')
            ->get();
        $product = [];
        foreach ($productRaw as $row) {
            $ps = $row->ps ?: 'Lainnya';
            $prod = $row->nama_produk ?: 'Unknown';
            if (!isset($product[$ps])) $product[$ps] = ['nama' => $ps, 'total_qty' => 0, 'total_nett' => 0, 'produk' => []];
            $product[$ps]['produk'][] = [
                'nama' => $prod,
                'qty' => (int)$row->total_qty,
                'nett' => (float)$row->total_nett
            ];
            $product[$ps]['total_qty'] += $row->total_qty;
            $product[$ps]['total_nett'] += $row->total_nett;
        }

        return response()->json([
            'pdu' => $pduList,
            'outlet' => array_values($outlet),
            'product' => array_values($product)
        ]);
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

    private function applyPsFilter($query, ?string $psTerpilih)
    {
        if ($psTerpilih) {
            if ($psTerpilih === 'Sales Team') {
                $query->where('ps', '!=', 'Office');
            } else {
                $query->where('ps', $psTerpilih);
            }
        }
        return $query;
    }

    private function getVisualisasiDataPayload($tahun, ?string $bulanTerpilih, ?string $psTerpilih, array $listPs)
    {
        $tahunLalu = (int)$tahun - 1;

        // 1. Ambil target tahun ini
        $targetsQuery = SalesTarget::where('tahun', $tahun);
        $this->applyPsFilter($targetsQuery, $psTerpilih);
        $targets = $targetsQuery->get();

        // Target tahun lalu
        $targetsLastYear = SalesTarget::where('tahun', $tahunLalu)->get();

        // 2. Sales tahun ini
        $salesCurrentQuery = Sales::whereYear('tanggal', $tahun);
        $this->applyPsFilter($salesCurrentQuery, $psTerpilih);
        $salesCurrent = $salesCurrentQuery
            ->select('bulan', 'ps', 'nama_produk', DB::raw('SUM(harga_nett) as total_sales'), DB::raw('SUM(qty) as total_qty'))
            ->groupBy('bulan', 'ps', 'nama_produk')
            ->get();
            
        $salesCurrent->transform(function ($item) {
            $item->bulan = ucfirst(strtolower($item->bulan));
            return $item;
        });

        // 3. Sales tahun lalu
        $salesLastYearQuery = Sales::whereYear('tanggal', $tahunLalu);
        $this->applyPsFilter($salesLastYearQuery, $psTerpilih);
        $salesLastYear = $salesLastYearQuery
            ->select('bulan', 'ps', DB::raw('SUM(harga_nett) as total_sales'))
            ->groupBy('bulan', 'ps')
            ->get();
            
        $salesLastYear->transform(function ($item) {
            $item->bulan = ucfirst(strtolower($item->bulan));
            return $item;
        });

        // A. Monthly Achievement Rate & YoY Growth
        $monthlyOverview = [];
        $totalTargetYear = 0;
        $totalSalesYear = 0;
        $totalSalesLastYear = 0;

        foreach ($this->urutanBulan as $b) {
            $tVal = $targets->where('bulan', $b)->sum('target_amount');
            $sVal = $salesCurrent->where('bulan', $b)->sum('total_sales');
            
            $sPrevValActual = $salesLastYear->where('bulan', $b)->sum('total_sales');
            $sPrevVal = $sPrevValActual;

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
        $targetBulan = $bulanTerpilih;
        if (!$targetBulan) {
            $lastSalesMonth = $salesCurrent->pluck('bulan')->last();
            $targetBulan = $lastSalesMonth ?: 'Juli';
        }

        $psPerformance = [];
        foreach ($listPs as $ps) {
            $tPs = $targets->where('ps', $ps)->sum('target_amount');
            $sPs = $salesCurrent->where('ps', $ps)->sum('total_sales');
            
            $achPs = $tPs > 0 ? round(($sPs / $tPs) * 100, 1) : 0;

            $psPerformance[$ps] = [
                'target'            => (float)$tPs,
                'sales'             => (float)$sPs,
                'achievement_rate'  => $achPs,
                'sales_last_month'  => 0,
                'growth_last_month' => 0,
            ];
        }

        // New Dataset: PS Performance per Month for local filtering
        $allPsPerformanceByMonth = [];
        foreach ($this->urutanBulan as $b) {
            $allPsPerformanceByMonth[$b] = [];
            foreach ($listPs as $ps) {
                $tPsM = $targets->where('bulan', $b)->where('ps', $ps)->sum('target_amount');
                $sPsM = $salesCurrent->where('bulan', $b)->where('ps', $ps)->sum('total_sales');
                $bulanPrevIndex = array_search($b, $this->urutanBulan);
                $bulanPrevName = $bulanPrevIndex > 0 ? $this->urutanBulan[$bulanPrevIndex - 1] : null;
                $sPrevTotal = $bulanPrevName ? $salesCurrent->where('bulan', $bulanPrevName)->where('ps', $ps)->sum('total_sales') : 0;
                
                $achPsM = $tPsM > 0 ? round(($sPsM / $tPsM) * 100, 1) : 0;
                $yoyGrowthM = $sPrevTotal > 0 ? round((($sPsM - $sPrevTotal) / $sPrevTotal) * 100, 1) : 0;

                $allPsPerformanceByMonth[$b][$ps] = [
                    'target'            => (float)$tPsM,
                    'sales'             => (float)$sPsM,
                    'achievement_rate'  => $achPsM,
                    'growth_rate'       => $yoyGrowthM,
                ];
            }
        }

        // C. Cumulative Achievement Rate per PS
        $limitBulanIndex = $bulanTerpilih ? array_search($bulanTerpilih, $this->urutanBulan) : (date('Y') == $tahun ? date('n') - 1 : 11);
        $bulanAkumulasi = array_slice($this->urutanBulan, 0, max(1, $limitBulanIndex + 1));

        $psCumulative = [];
        foreach ($listPs as $ps) {
            $cumTarget = $targets->whereIn('bulan', $bulanAkumulasi)->where('ps', $ps)->sum('target_amount');
            $cumSales = $salesCurrent->whereIn('bulan', $bulanAkumulasi)->where('ps', $ps)->sum('total_sales');
            
            $cumSalesLastYearActual = $salesLastYear->whereIn('bulan', $bulanAkumulasi)->where('ps', $ps)->sum('total_sales');
            $cumSalesLastYear = $cumSalesLastYearActual;

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
            if ($psTerpilih === 'Sales Team') {
                $productsQuery->where('ps', '!=', 'Office');
            } else {
                $productsQuery->where('ps', $psTerpilih);
            }
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
        $topProductCategoryPs = $productCategoryPs;

        // E. Top Customers Contribution
        $customerQuery = Sales::whereYear('tanggal', $tahun);
        if ($bulanTerpilih) {
            $customerQuery->where('bulan', $bulanTerpilih);
        }
        $this->applyPsFilter($customerQuery, $psTerpilih);
        $customerSalesRaw = $customerQuery
            ->select('nama_customer', DB::raw('SUM(harga_nett) as total_nett'))
            ->whereNotNull('nama_customer')
            ->where('nama_customer', '!=', '')
            ->groupBy('nama_customer')
            ->orderBy('total_nett', 'desc')
            ->limit(10)
            ->get();
        
        $topCustomers = $customerSalesRaw->map(function($c) {
            return [
                'nama_customer' => $c->nama_customer,
                'total_nett' => (float) $c->total_nett
            ];
        })->toArray();

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
            'allPsPerformanceByMonth' => $allPsPerformanceByMonth,
            'psCumulative'         => $psCumulative,
            'topProductCategoryPs' => $topProductCategoryPs,
            'topCustomers'         => $topCustomers,
            'allProductsCount'     => count($productCategoryPs),
        ];
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:sales,id',
        ]);

        try {
            $count = count($request->ids);
            Sales::whereIn('id', $request->ids)->delete();
            return redirect()->back()->with('success', "Berhasil menghapus $count data sales terpilih.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Bulk Delete Sales Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data terpilih.');
        }
    }
}

