<?php

namespace App\Http\Controllers\Sales;

use App\Models\Sales;
use App\Models\SalesTarget;
use App\Models\SalesIncentiveSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesAnalyticsController extends BaseSalesController
{
    public function analytics(Request $request)
    {
        if (!$this->hasFullSalesAccess()) abort(403, 'Anda tidak memiliki hak akses ke halaman Analitik Penjualan.');

        // data filter dashboard
        $listPs = Sales::whereNotNull('ps')->where('ps', '!=', '')->distinct()->orderBy('ps', 'asc')->pluck('ps')->toArray();
        $listCustomer = Sales::whereNotNull('customer_name')->where('customer_name', '!=', '')->distinct()->orderBy('customer_name', 'asc')->pluck('customer_name');

        $listProduk = Sales::whereNotNull('product_name')
            ->where('product_name', '!=', '')
            ->distinct()
            ->orderBy('product_name', 'asc')
            ->pluck('product_name');

        $bulanAda = Sales::whereNotNull('month')->distinct()->pluck('month')->toArray();
        $bulanAda = array_map(fn($b) => ucfirst(strtolower($b)), $bulanAda);
        $listBulan = array_values(array_intersect($this->urutanBulan, $bulanAda));

        $currentMonthIndex = date('n');
        $validMonths = array_slice($this->urutanBulan, 0, $currentMonthIndex);
        $listBulan = array_values(array_intersect($listBulan, $validMonths));

        $listTahun = Sales::whereNotNull('date')->selectRaw('DISTINCT YEAR(date) as tahun')->orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        if (!in_array(date('Y'), $listTahun)) {
            array_unshift($listTahun, date('Y'));
        }

        // data target sales
        $tahun = $request->input('tahun', date('Y'));
        $tahunLalu = (int)$tahun - 1;

        $targets = SalesTarget::where('year', $tahun)->get();
        $salesCurrent = Sales::whereYear('date', $tahun)
            ->select('month', 'ps', DB::raw('SUM(net_price) as total_sales'))
            ->groupBy('month', 'ps')->get();

        $salesCurrent->transform(function ($item) {
            $item->month = ucfirst(strtolower($item->month));
            return $item;
        });

        $salesLastYearRaw = Sales::whereYear('date', $tahunLalu)
            ->select('month', DB::raw('SUM(net_price) as total_sales'))
            ->groupBy('month')->get();

        $salesLastYear = [];
        foreach ($salesLastYearRaw as $row) {
            $b = ucfirst(strtolower($row->month));
            $salesLastYear[$b] = ($salesLastYear[$b] ?? 0) + $row->total_sales;
        }

        $monthlyAll = [];
        $monthlyPerPs = [];
        $allPsAchievement = [];
        foreach ($listPs as $ps) {
            $allPsAchievement[$ps] = ['target' => 0, 'sales' => 0];
        }

        foreach ($this->urutanBulan as $bulan) {
            $targetAll = $targets->where('month', $bulan)->sum('target_amount');
            $salesAll = $salesCurrent->where('month', $bulan)->sum('total_sales');
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
                $targetPs = $targets->where('month', $bulan)->where('ps', $ps)->sum('target_amount');
                $salesPs = $salesCurrent->where('month', $bulan)->where('ps', $ps)->sum('total_sales');

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
        $triwulanTerpilih = $request->input('triwulan', '');
        $analyticsData = $this->getVisualisasiDataPayload($tahun, $bulanTerpilih, $psTerpilih, $listPs, $triwulanTerpilih);

        $urutanBulan = $this->urutanBulan;
        $historySales = Sales::select(
            DB::raw('YEAR(date) as tahun'),
            DB::raw('SUM(CASE WHEN month = "January" THEN net_price ELSE 0 END) as jan'),
            DB::raw('SUM(CASE WHEN month = "February" THEN net_price ELSE 0 END) as feb'),
            DB::raw('SUM(CASE WHEN month = "March" THEN net_price ELSE 0 END) as mar'),
            DB::raw('SUM(CASE WHEN month = "April" THEN net_price ELSE 0 END) as apr'),
            DB::raw('SUM(CASE WHEN month = "May" THEN net_price ELSE 0 END) as mei'),
            DB::raw('SUM(CASE WHEN month = "June" THEN net_price ELSE 0 END) as jun'),
            DB::raw('SUM(CASE WHEN month = "July" THEN net_price ELSE 0 END) as jul'),
            DB::raw('SUM(CASE WHEN month = "August" THEN net_price ELSE 0 END) as agu'),
            DB::raw('SUM(CASE WHEN month = "September" THEN net_price ELSE 0 END) as sep'),
            DB::raw('SUM(CASE WHEN month = "October" THEN net_price ELSE 0 END) as okt'),
            DB::raw('SUM(CASE WHEN month = "November" THEN net_price ELSE 0 END) as nov'),
            DB::raw('SUM(CASE WHEN month = "December" THEN net_price ELSE 0 END) as des')
        )->whereNotNull('date')->groupBy(DB::raw('YEAR(date)'))->orderBy(DB::raw('YEAR(date)'), 'desc')->get();

        // Avatar per PS via satu pintu (App\Support\PsAvatar): kunci map = nama
        // persis seperti di sales.ps (dipakai JS: psAvatars[item.name]),
        // pencocokan kebal varian tulisan. Dibangun dinamis dari $listPs.
        $psAvatars = \App\Support\PsAvatar::map($listPs);
        // 'Office' bukan personel — pakai logo khusus bila file-nya sudah
        // ditaruh di public/images/office.png, selain itu fallback abu-abu.
        if (isset($psAvatars['Office'])) {
            $psAvatars['Office'] = file_exists(public_path('images/office.png'))
                ? asset('images/office.png')
                : \App\Support\PsAvatar::fallback('Office', '64748b');
        }

        return view('users.sales.analytics', array_merge(compact(
            'listPs',
            'listCustomer',
            'listProduk',
            'listBulan',
            'listTahun',
            'tahun',
            'tahunLalu',
            'monthlyAll',
            'monthlyPerPs',
            'allPsAchievement',
            'urutanBulan',
            'bulanTerpilih',
            'psTerpilih',
            'triwulanTerpilih',
            'targets',
            'historySales',
            'psAvatars'
        ), $analyticsData))->with('title', 'Sales Analytics & Target');
    }

    public function monthly(Request $request)
    {
        if (!$this->hasAnySalesAccess()) abort(403, 'Anda tidak memiliki hak akses ke halaman Monthly Monitoring.');
        $tahun = $request->input('tahun', date('Y'));
        $hasFullAccess = $this->hasFullSalesAccess();
        $currentMonth = date('F'); 

        $listTahun = Sales::whereNotNull('date')
            ->selectRaw('DISTINCT YEAR(date) as tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (!in_array(date('Y'), $listTahun)) {
            array_unshift($listTahun, date('Y'));
        }

        return view('users.sales.monthly', compact('tahun', 'hasFullAccess', 'listTahun', 'currentMonth'));
    }

    public function stock(Request $request)
    {
        if (!$this->hasAnySalesAccess()) abort(403, 'Anda tidak memiliki hak akses ke halaman Monitoring Stock.');
        return view('users.stock.stock')->with('title', 'Monitoring Stock Barang');
    }

    public function monitoringData(Request $request)
    {
        $tahun         = $request->input('tahun');
        $bulanFilter   = array_filter((array) $request->input('bulan', []));
        $psFilter      = array_filter((array) $request->input('ps', []));
        $customerFilter = array_filter((array) $request->input('nama_customer', []));
        $produkFilter  = array_filter((array) $request->input('nama_produk', []));

        $baseQuery = Sales::query();

        if ($tahun) {
            $baseQuery->whereYear('date', $tahun);
        }
        if (!empty($bulanFilter)) {
            $baseQuery->whereIn('month', $bulanFilter);
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
            $baseQuery->whereIn('customer_name', $customerFilter);
        }
        if (!empty($produkFilter)) {
            $baseQuery->whereIn('product_name', $produkFilter);
        }

        // summary cards
        $summaryQuery = clone $baseQuery;
        $totalNett = (clone $summaryQuery)->sum('net_price');
        $totalQty  = (clone $summaryQuery)->sum('qty');
        $totalCustomer = (clone $summaryQuery)->whereNotNull('customer_name')->where('customer_name', '!=', '')->distinct('customer_name')->count('customer_name');
        $totalProduk = (clone $summaryQuery)->whereNotNull('product_name')->where('product_name', '!=', '')->distinct('product_name')->count('product_name');

        // trend sales per bulan
        $trendRawDb = (clone $baseQuery)
            ->select('month', DB::raw('SUM(net_price) as total'))
            ->groupBy('month')
            ->pluck('total', 'month');

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
            $targetQuery->where('year', $tahun);
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
        $targetRawDb = $targetQuery->select('month', DB::raw('SUM(target_amount) as total_target'))
            ->groupBy('month')
            ->pluck('total_target', 'month');

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
            $selects = [$nameField, 'month', DB::raw('SUM(net_price) as total_nett'), DB::raw('SUM(qty) as total_qty'), DB::raw('MAX(unit) as satuan')];
            if ($subGroupField) {
                $selects[] = $subGroupField;
            }

            $queryObj = $query->select($selects)
                ->whereNotNull($nameField)
                ->where($nameField, '!=', '');

            if ($subGroupField) {
                $queryObj->groupBy($nameField, $subGroupField, 'month');
            } else {
                $queryObj->groupBy($nameField, 'month');
            }

            $raw = $queryObj->get();

            $result = [];
            foreach ($raw as $row) {
                $name = $row->{$nameField};
                $bulan = ucfirst(strtolower($row->month));
                if (!isset($result[$name])) {
                    $result[$name] = [
                        'nama' => $name,
                        'satuan' => $nameField === 'product_name' ? ($row->satuan ?? '') : '',
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
                                'satuan' => $subGroupField === 'product_name' ? ($row->satuan ?? '') : '',
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
        $perCustomer = $formatMatrix(clone $baseQuery, 'customer_name');

        // sales per customer & produk
        $perCustomerProduk = $formatMatrix(clone $baseQuery, 'customer_name', 'product_name');

        // sales per produk
        $perProduk = $formatMatrix(clone $baseQuery, 'product_name');

        // sales per produk (pivot)
        $pivotProdukPs = $formatMatrix(clone $baseQuery, 'product_name', 'ps');

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
            $sfQuery->whereYear('date', $tahun);
        }
        if (!empty($psFilter)) {
            $sfQuery->whereIn('ps', $psFilter);
        }
        if (!empty($customerFilter)) {
            $sfQuery->whereIn('customer_name', $customerFilter);
        }
        if (!empty($produkFilter)) {
            $sfQuery->whereIn('product_name', $produkFilter);
        }

        // qty per produk per bulan
        $qtyPerProdukBulan = $sfQuery
            ->select('product_name', 'month', DB::raw('SUM(qty) as total_qty'))
            ->whereNotNull('product_name')
            ->groupBy('product_name', 'month')
            ->get()
            ->groupBy('product_name');

        // 7 bulan terakhir yang ada data
        $tujuhBulanTerakhir = array_slice($this->urutanBulan, 0, 7); // fallback default Jan-Jul
        $bulanTersedia = Sales::whereNotNull('month')->distinct()->pluck('month')->toArray();
        $bulanTersediaUrut = array_values(array_intersect($this->urutanBulan, $bulanTersedia));
        if (count($bulanTersediaUrut) > 0) {
            $tujuhBulanTerakhir = array_slice($bulanTersediaUrut, -7);
        }

        $stockForecast = [];
        foreach ($qtyPerProdukBulan as $produk => $rows) {
            $rowsByBulan = $rows->pluck('total_qty', 'month');
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

    public function monthlyDetailData(Request $request)
    {
        if (!$this->hasAnySalesAccess()) abort(403, 'Unauthorized');

        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan');

        if (!$bulan) {
            return response()->json(['error' => 'Bulan is required'], 400);
        }

        $query = Sales::whereYear('date', $tahun)->where('month', $bulan);

        if (!$this->hasFullSalesAccess()) {
            $query->where(function ($q) {
                $q->whereRaw("LOWER(ps) != 'office'")->orWhereNull('ps');
            });
        }

        // Group 1: PDU
        $pduRaw = (clone $query)->select('ps', 'date', 'customer_name', 'product_name', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(net_price) as total_nett'), DB::raw('MAX(unit) as satuan'))
            ->groupBy('ps', 'date', 'customer_name', 'product_name')
            ->orderBy('ps')->orderBy('date')->orderBy('customer_name')->orderBy('product_name')
            ->get();

        $pdu = [];
        foreach ($pduRaw as $row) {
            $ps = $row->ps ?: 'Lainnya';
            $tgl = date('d/m/Y', strtotime($row->date));
            $cust = $row->customer_name ?: 'Unknown';
            $prod = $row->product_name ?: 'Unknown';

            if (!isset($pdu[$ps])) $pdu[$ps] = ['nama' => $ps, 'total_qty' => 0, 'total_nett' => 0, 'tanggal' => []];
            if (!isset($pdu[$ps]['tanggal'][$tgl])) $pdu[$ps]['tanggal'][$tgl] = ['nama' => $tgl, 'total_qty' => 0, 'total_nett' => 0, 'customer' => []];
            if (!isset($pdu[$ps]['tanggal'][$tgl]['customer'][$cust])) $pdu[$ps]['tanggal'][$tgl]['customer'][$cust] = ['nama' => $cust, 'total_qty' => 0, 'total_nett' => 0, 'produk' => []];

            $pdu[$ps]['tanggal'][$tgl]['customer'][$cust]['produk'][] = [
                'nama' => $prod,
                'qty' => (int)$row->total_qty,
                'satuan' => $row->satuan ?? '', 
                'nett' => (float)$row->total_nett
            ];

            $pdu[$ps]['tanggal'][$tgl]['customer'][$cust]['total_qty'] += $row->total_qty;
            $pdu[$ps]['tanggal'][$tgl]['customer'][$cust]['total_nett'] += $row->total_nett;
            $pdu[$ps]['tanggal'][$tgl]['total_qty'] += $row->total_qty;
            $pdu[$ps]['tanggal'][$tgl]['total_nett'] += $row->total_nett;
            $pdu[$ps]['total_qty'] += $row->total_qty;
            $pdu[$ps]['total_nett'] += $row->total_nett;
        }

        // Ambil target dengan toleransi huruf besar/kecil
        $targetData = \App\Models\SalesTarget::where('year', $tahun)
            ->whereRaw('LOWER(month) = ?', [strtolower($bulan)])
            ->get();

        $bulanIndex = array_search($bulan, $this->urutanBulan) ?: 0;
        $tahunPrevMonth = $bulanIndex == 0 ? $tahun - 1 : $tahun;
        $bulanPrev = $bulanIndex == 0 ? 'December' : $this->urutanBulan[$bulanIndex - 1];

        $salesPrevMonthQuery = Sales::whereYear('date', $tahunPrevMonth)->where('month', $bulanPrev);
        if (!$this->hasFullSalesAccess()) {
            $salesPrevMonthQuery->where(function ($q) {
                $q->whereRaw("LOWER(ps) != 'office'")->orWhereNull('ps');
            });
        }
        $salesPrevMonth = $salesPrevMonthQuery->select('ps', DB::raw('SUM(net_price) as total_sales'))->groupBy('ps')->get()->keyBy('ps');

        // Avg YTD
        $monthsYtd = array_slice($this->urutanBulan, 0, $bulanIndex + 1);
        $pembagiYtd = count($monthsYtd);
        $salesYtdQuery = Sales::whereYear('date', $tahun)->whereIn('month', $monthsYtd);
        if (!$this->hasFullSalesAccess()) {
            $salesYtdQuery->where(function ($q) {
                $q->whereRaw("LOWER(ps) != 'office'")->orWhereNull('ps');
            });
        }
        $salesYtd = $salesYtdQuery->select('ps', DB::raw('SUM(net_price) as total_sales'))->groupBy('ps')->get()->keyBy('ps');

        $pduList = array_values($pdu);
        foreach ($pduList as &$psData) {
            $psData['tanggal'] = array_values($psData['tanggal']);
            foreach ($psData['tanggal'] as &$tglData) {
                $tglData['customer'] = array_values($tglData['customer']);
            }
            
            // Pencocokan target presisi namun mengabaikan besar/kecil huruf & spasi
            $psData['target_amount'] = $targetData->filter(function($t) use ($psData) {
                return strcasecmp(trim($t->ps), trim($psData['nama'])) === 0;
            })->sum('target_amount');

            $sPrevValActual = isset($salesPrevMonth[$psData['nama']]) ? (float)$salesPrevMonth[$psData['nama']]->total_sales : 0;
            $sVal = $psData['total_nett'];
            $growthRate = $sPrevValActual > 0 ? round((($sVal - $sPrevValActual) / $sPrevValActual) * 100, 1) : 0;
            if ($sPrevValActual == 0 && $sVal > 0) $growthRate = 100;
            $psData['growth_rate'] = $growthRate;

            $ytdVal = isset($salesYtd[$psData['nama']]) ? (float)$salesYtd[$psData['nama']]->total_sales : 0;
            $psData['avg_ytd'] = $pembagiYtd > 0 ? round($ytdVal / $pembagiYtd, 2) : 0;
        }

        // Group 2: Outlet
        $outletRaw = (clone $query)->select('ps', 'customer_name', 'product_name', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(net_price) as total_nett'), DB::raw('MAX(unit) as satuan'))
            ->groupBy('ps', 'customer_name', 'product_name')
            ->orderBy('ps')->orderBy('customer_name')->orderBy('total_nett', 'desc')
            ->get();
            
        $outlet = [];
        foreach ($outletRaw as $row) {
            $ps = $row->ps ?: 'Lainnya';
            $cust = $row->customer_name ?: 'Unknown';
            $prod = $row->product_name ?: 'Unknown';

            if (!isset($outlet[$ps])) $outlet[$ps] = ['nama' => $ps, 'total_qty' => 0, 'total_nett' => 0, 'customer' => []];
            if (!isset($outlet[$ps]['customer'][$cust])) $outlet[$ps]['customer'][$cust] = ['nama' => $cust, 'total_qty' => 0, 'nett' => 0, 'produk' => []];

            $outlet[$ps]['customer'][$cust]['produk'][] = [
                'nama' => $prod,
                'qty' => (int)$row->total_qty,
                'satuan' => $row->satuan ?? '', 
                'nett' => (float)$row->total_nett
            ];

            $outlet[$ps]['customer'][$cust]['total_qty'] += $row->total_qty;
            $outlet[$ps]['customer'][$cust]['nett'] += $row->total_nett;
            $outlet[$ps]['total_qty'] += $row->total_qty;
            $outlet[$ps]['total_nett'] += $row->total_nett;
        }

        foreach ($outlet as &$psData) {
            $psData['customer'] = array_values($psData['customer']);
            usort($psData['customer'], fn($a, $b) => $b['nett'] <=> $a['nett']);
        }

        // Group 3: Product
        $productRaw = (clone $query)->select('ps', 'product_name', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(net_price) as total_nett'), DB::raw('MAX(unit) as satuan'))
            ->groupBy('ps', 'product_name')
            ->orderBy('ps')->orderBy('total_nett', 'desc')
            ->get();
            
        $product = [];
        foreach ($productRaw as $row) {
            $ps = $row->ps ?: 'Lainnya';
            $prod = $row->product_name ?: 'Unknown';
            if (!isset($product[$ps])) $product[$ps] = ['nama' => $ps, 'total_qty' => 0, 'total_nett' => 0, 'produk' => []];
            $product[$ps]['produk'][] = [
                'nama' => $prod,
                'qty' => (int)$row->total_qty,
                'satuan' => $row->satuan ?? '', 
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
        $triwulanTerpilih = $request->input('triwulan', '');

        $listPs = Sales::whereNotNull('ps')
            ->where('ps', '!=', '')
            ->distinct()
            ->orderBy('ps', 'asc')
            ->pluck('ps')
            ->toArray();

        $analytics = $this->getVisualisasiDataPayload($tahun, $bulanTerpilih, $psTerpilih, $listPs, $triwulanTerpilih);

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

    private function applyTriwulanFilter($query, ?string $triwulanTerpilih)
    {
        if ($triwulanTerpilih) {
            $bulanFilter = [];
            if ($triwulanTerpilih == '1') $bulanFilter = ['January', 'February', 'March'];
            elseif ($triwulanTerpilih == '2') $bulanFilter = ['April', 'May', 'June'];
            elseif ($triwulanTerpilih == '3') $bulanFilter = ['July', 'August', 'September'];
            elseif ($triwulanTerpilih == '4') $bulanFilter = ['October', 'November', 'December'];

            if (!empty($bulanFilter)) {
                $query->whereIn('month', $bulanFilter);
            }
        }
        return $query;
    }

    private function getVisualisasiDataPayload($tahun, ?string $bulanTerpilih, ?string $psTerpilih, array $listPs, ?string $triwulanTerpilih = null)
    {
        $tahunLalu = (int)$tahun - 1;

        // 1. Ambil target tahun ini
        $targetsQuery = SalesTarget::where('year', $tahun);
        $this->applyPsFilter($targetsQuery, $psTerpilih);
        $this->applyTriwulanFilter($targetsQuery, $triwulanTerpilih);
        $targets = $targetsQuery->get();

        // Target tahun lalu
        $targetsLastYear = SalesTarget::where('year', $tahunLalu)->get();

        // 2. Sales tahun ini
        $salesCurrentQuery = Sales::whereYear('date', $tahun);
        $this->applyPsFilter($salesCurrentQuery, $psTerpilih);
        $this->applyTriwulanFilter($salesCurrentQuery, $triwulanTerpilih);
        $salesCurrent = $salesCurrentQuery
            ->select('month', 'ps', 'product_name', DB::raw('SUM(net_price) as total_sales'), DB::raw('SUM(qty) as total_qty'))
            ->groupBy('month', 'ps', 'product_name')
            ->get();

        $salesCurrent->transform(function ($item) {
            $item->month = ucfirst(strtolower($item->month));
            return $item;
        });

        // 3. Sales tahun lalu
        $salesLastYearQuery = Sales::whereYear('date', $tahunLalu);
        $this->applyPsFilter($salesLastYearQuery, $psTerpilih);
        $this->applyTriwulanFilter($salesLastYearQuery, $triwulanTerpilih);
        $salesLastYear = $salesLastYearQuery
            ->select('month', 'ps', DB::raw('SUM(net_price) as total_sales'))
            ->groupBy('month', 'ps')
            ->get();

        $salesLastYear->transform(function ($item) {
            $item->month = ucfirst(strtolower($item->month));
            return $item;
        });

        // A. Monthly Achievement Rate & YoY Growth
        $urutanBulanVisualisasi = $this->urutanBulan;
        if ($triwulanTerpilih) {
            if ($triwulanTerpilih == '1') $urutanBulanVisualisasi = ['January', 'February', 'March'];
            elseif ($triwulanTerpilih == '2') $urutanBulanVisualisasi = ['April', 'May', 'June'];
            elseif ($triwulanTerpilih == '3') $urutanBulanVisualisasi = ['July', 'August', 'September'];
            elseif ($triwulanTerpilih == '4') $urutanBulanVisualisasi = ['October', 'November', 'December'];
        }

        $monthlyOverview = [];
        $totalTargetYear = 0;
        $totalSalesYear = 0;
        $totalSalesLastYear = 0;

        foreach ($this->urutanBulan as $b) {
            if (!in_array($b, $urutanBulanVisualisasi)) {
                $monthlyOverview[$b] = [
                    'target'           => 0.0,
                    'sales'            => 0.0,
                    'achievement_rate' => 0,
                    'sales_last_year'  => 0.0,
                    'growth_rate'      => 0,
                ];
                continue;
            }

            $tVal = $targets->where('month', $b)->sum('target_amount');
            $sVal = $salesCurrent->where('month', $b)->sum('total_sales');

            $sPrevValActual = $salesLastYear->where('month', $b)->sum('total_sales');
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
            $lastSalesMonth = $salesCurrent->pluck('month')->last();
            $targetBulan = $lastSalesMonth ?: 'July';
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
                $tPsM = $targets->where('month', $b)->where('ps', $ps)->sum('target_amount');
                $sPsM = $salesCurrent->where('month', $b)->where('ps', $ps)->sum('total_sales');
                $bulanPrevIndex = array_search($b, $this->urutanBulan);
                $bulanPrevName = $bulanPrevIndex > 0 ? $this->urutanBulan[$bulanPrevIndex - 1] : null;
                $sPrevTotal = $bulanPrevName ? $salesCurrent->where('month', $bulanPrevName)->where('ps', $ps)->sum('total_sales') : 0;

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
            $cumTarget = $targets->whereIn('month', $bulanAkumulasi)->where('ps', $ps)->sum('target_amount');
            $cumSales = $salesCurrent->whereIn('month', $bulanAkumulasi)->where('ps', $ps)->sum('total_sales');

            $cumSalesLastYearActual = $salesLastYear->whereIn('month', $bulanAkumulasi)->where('ps', $ps)->sum('total_sales');
            $cumSalesLastYear = $cumSalesLastYearActual;

            $cumAchRate = $cumTarget > 0 ? round(($cumSales / $cumTarget) * 100, 1) : 0;
            $cumGrowthRate = $cumSalesLastYear > 0 ? round((($cumSales - $cumSalesLastYear) / $cumSalesLastYear) * 100, 1) : 0;

            $monthlySalesPs = [];
            foreach ($this->urutanBulan as $b) {
                $monthlySalesPs[$b] = (float)$salesCurrent->where('month', $b)->where('ps', $ps)->sum('total_sales');
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
        $productsQuery = Sales::whereYear('date', $tahun);
        if ($bulanTerpilih) {
            $productsQuery->where('month', $bulanTerpilih);
        }
        if ($psTerpilih) {
            if ($psTerpilih === 'Sales Team') {
                $productsQuery->where('ps', '!=', 'Office');
            } else {
                $productsQuery->where('ps', $psTerpilih);
            }
        }

        $productSalesRaw = $productsQuery
            ->select('product_name', 'ps', DB::raw('SUM(net_price) as total_nett'), DB::raw('SUM(qty) as total_qty'))
            ->whereNotNull('product_name')
            ->where('product_name', '!=', '')
            ->groupBy('product_name', 'ps')
            ->get();

        $productCategoryPs = [];
        foreach ($productSalesRaw as $row) {
            $prod = $row->product_name;
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
        $customerQuery = Sales::whereYear('date', $tahun);
        if ($bulanTerpilih) {
            $customerQuery->where('month', $bulanTerpilih);
        }
        $this->applyPsFilter($customerQuery, $psTerpilih);
        $customerSalesRaw = $customerQuery
            ->select('customer_name', DB::raw('SUM(net_price) as total_nett'))
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->groupBy('customer_name')
            ->orderBy('total_nett', 'desc')
            ->limit(10)
            ->get();

        $topCustomers = $customerSalesRaw->map(function ($c) {
            return [
                'nama_customer' => $c->customer_name,
                'total_nett' => (float) $c->total_nett
            ];
        })->toArray();

        $overallAchievement = $totalTargetYear > 0 ? round(($totalSalesYear / $totalTargetYear) * 100, 1) : 0;
        $overallGrowth = $totalSalesLastYear > 0 ? round((($totalSalesYear - $totalSalesLastYear) / $totalSalesLastYear) * 100, 1) : 0;

        // Rata-rata sales per bulan = total sales dibagi jumlah bulan berjalan
        // dalam cakupan (bulan yang sudah lewat bila tahun berjalan, semua bila lampau).
        $scopeMonths = $urutanBulanVisualisasi;
        if ((int) $tahun === (int) date('Y')) {
            $scopeMonths = array_values(array_filter(
                $scopeMonths,
                fn($b) => array_search($b, $this->urutanBulan) < date('n')
            ));
        }
        $avgMonthCount = max(1, count($scopeMonths));

        return [
            'summary' => [
                'total_target'        => (float)$totalTargetYear,
                'total_sales'         => (float)$totalSalesYear,
                'overall_achievement' => $overallAchievement,
                'overall_growth'      => $overallGrowth,
                'bulan_aktif'         => $targetBulan,
                'avg_monthly'         => (float)$totalSalesYear / $avgMonthCount,
                'avg_months'          => $avgMonthCount,
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
}
