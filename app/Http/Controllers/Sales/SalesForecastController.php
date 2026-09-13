<?php

namespace App\Http\Controllers\Sales;

use App\Models\Sales;
use App\Models\SalesForecastOrder;
use App\Models\SalesForecastSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ForecastExport;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesForecastController extends BaseSalesController
{
    /**
     * Build forecast data (used by view and exports).
     *
     * @return array [$stockForecast, $tigaBulanTerakhir, $bulanAktif, $teksStokAkhir, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi]
     */
    protected function buildForecastData($tahun, $bulanAktif = null)
    {
        $bulanTersediaUrut = $this->urutanBulan;

        // Active month
        $namaBulan = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $defaultBulanAktif = ($tahun == date('Y')) ? $namaBulan[(int)date('n')] : 'September';
        $bulanAktif = ($bulanAktif && in_array($bulanAktif, $bulanTersediaUrut)) ? $bulanAktif : $defaultBulanAktif;

        // Percentage
        $settingPersen = SalesForecastSetting::where('year', $tahun)->where('month', $bulanAktif)->value('percentage');
        $activePercentage = $settingPersen !== null ? (float)$settingPersen : 20.00;
        $multiplier = 1 + ($activePercentage / 100);

        // Reference months
        $settingRefMonths = SalesForecastSetting::where('year', $tahun)->where('month', $bulanAktif)->value('ref_months');
        $activeRefMonths = $settingRefMonths !== null ? (int)$settingRefMonths : 6;
        $activeRefMonths = max(1, min($activeRefMonths, 12));

        $indexAktif = array_search($bulanAktif, $bulanTersediaUrut);
        if ($indexAktif !== false && $indexAktif >= $activeRefMonths) {
            $tigaBulanTerakhir = array_slice($bulanTersediaUrut, $indexAktif - $activeRefMonths, $activeRefMonths);
        } elseif ($indexAktif !== false && $indexAktif > 0) {
            $tigaBulanTerakhir = array_slice($bulanTersediaUrut, 0, $indexAktif);
        } else {
            $tigaBulanTerakhir = array_slice($bulanTersediaUrut, 0, $activeRefMonths);
        }

        // DOI target
        $settingDoi = SalesForecastSetting::where('year', $tahun)->where('month', $bulanAktif)->value('doi');
        $activeDoi = $settingDoi !== null ? (int)$settingDoi : 30;
        $activeDoi = max(1, min($activeDoi, 365));

        // Sales data
        $salesRaw = Sales::whereYear('date', $tahun)
            ->whereIn('month', $tigaBulanTerakhir)
            ->whereNotNull('product_name')->where('product_name', '!=', '')
            ->select('product_name', 'ps', 'month', DB::raw('SUM(qty) as total_qty'), DB::raw('MAX(unit) as satuan'))
            ->groupBy('product_name', 'ps', 'month')
            ->get();

        $salesRaw->transform(function ($item) {
            $item->month = ucfirst(strtolower(trim($item->month)));
            return $item;
        });

        $groupedByProduk = $salesRaw->groupBy('product_name');
        
        // Stock snapshot (as of last reference month)
        $stokSaatIni = [];
        $satuanStok = [];
        $namaProdukArray = $groupedByProduk->keys()->toArray();

        $namaBulanIdx = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6,
            'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12,
        ];
        $bulanReferensiTerakhir = end($tigaBulanTerakhir);
        $bulanAkhirTanggal = ($tahun && isset($namaBulanIdx[$bulanReferensiTerakhir]))
            ? Carbon::create((int)$tahun, $namaBulanIdx[$bulanReferensiTerakhir], 1)->endOfMonth()->format('Y-m-d')
            : date('Y-m-d');

        $products = \App\Models\Product::active()->whereIn('product_name', $namaProdukArray)->get();

        if ($products->isNotEmpty()) {
            $snapshots = DB::table('daily_stock_histories')
                ->whereIn('product_id', $products->pluck('id'))
                ->whereNotNull('product_id')
                ->where('tanggal', '<=', $bulanAkhirTanggal)
                ->select('product_id', 'tanggal', 'stok')
                ->orderBy('product_id')->orderByDesc('tanggal')
                ->get();

            $snapshotTerakhir = $snapshots->groupBy('product_id')->map(fn($rows) => $rows->first());

            foreach ($products as $p) {
                $snap = $snapshotTerakhir->get($p->id);
                $stokSaatIni[$p->product_name] = $snap ? (int)$snap->stok : (int)$p->stock;
                $satuanStok[$p->product_name] = $p->unit ?? 'Pcs';
            }
        }

        // Saved orders and MOQ
        $savedOrders = DB::table('sales_forecast_orders')
            ->where('year', $tahun)->where('month', $bulanAktif)
            ->pluck('suggested_qty', 'product_name')->toArray();

        $savedMoqs = DB::table('sales_forecast_orders')
            ->where('year', $tahun)->where('month', $bulanAktif)
            ->pluck('moq', 'product_name')->toArray();

        // Calculate
        $stockForecast = [];
        foreach ($groupedByProduk as $produk => $rows) {
            $total3Bulan = $rows->sum('total_qty');
            $jumlahBulanTerpakai = count($tigaBulanTerakhir);
            
            $avg = $jumlahBulanTerpakai > 0 ? $total3Bulan / $jumlahBulanTerpakai : 0;
            $forecast = (int) ceil($avg * $multiplier);

            $psBreakdown = [];
            foreach ($rows->groupBy('ps') as $psName => $psRows) {
                $psBreakdown[$psName ?: 'Others'] = $psRows->sum('total_qty');
            }
            arsort($psBreakdown);

            $detailBulan = [];
            foreach ($tigaBulanTerakhir as $b) {
                $detailBulan[$b] = $rows->where('month', $b)->sum('total_qty');
            }

            $satuanRow = $rows->whereNotNull('satuan')->where('satuan', '!=', '')->first();
            $satuanSales = $satuanRow ? $satuanRow->satuan : 'Pcs';

            if ($avg > 0) {
                // Buffer = Average * (DOI / 30)
                $buffer = $avg * ($activeDoi / 30);
                $moq = max(1, (float)($savedMoqs[$produk] ?? 1));

                // Order = IF((End Stock - Forecast) < Buffer) THEN CEILING(..., MOQ) ELSE 0
                $endstock = $stokSaatIni[$produk] ?? 0;
                $bufferQty = ceil($buffer);
                if (($endstock - $forecast) < $bufferQty) {
                    $selisih = $bufferQty - ($endstock - $forecast);
                    $orderQty = (int)(ceil($selisih / $moq) * $moq);
                } else {
                    $orderQty = 0;
                }

                // DOI = (End Stock / Average) * 30
                $doiHari = $avg > 0 ? ceil(($endstock / $avg) * 30) : 0;
                $doiHari = max(0, (int)$doiHari);

                $stockForecast[] = [
                    'nama_produk'    => $produk,
                    'satuan_sales'   => $satuanSales,
                    'satuan_stok'    => $satuanStok[$produk] ?? 'Pcs',
                    'total_qty'      => (int) $total3Bulan,
                    'avg_qty'        => round($avg, 2),
                    'forecast_qty'   => $forecast,
                    'buffer_qty'     => (int) $bufferQty,
                    'doi_qty'        => $doiHari,
                    'moq'            => $moq,
                    'order_qty'      => $orderQty,
                    'detail_bulan'   => $detailBulan,
                    'ps_breakdown'   => $psBreakdown,
                    'stok_tersedia'  => $endstock, 
                    'suggested_order'=> $savedOrders[$produk] ?? ''
                ];
            }
        }
        
        usort($stockForecast, fn($a, $b) => $b['forecast_qty'] <=> $a['forecast_qty']);

        // Stock date label
        $bulanTerakhirTampil = end($tigaBulanTerakhir);
        $indeksBulanAkhirTampil = array_search($bulanTerakhirTampil, $this->urutanBulan);
        $bulanEn = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        if ($indeksBulanAkhirTampil !== false) {
            $mappingAngka = ['January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12];
            $angkaBln = $mappingAngka[$bulanTerakhirTampil] ?? 8;
            $tanggalAkhir = Carbon::create($tahun, $angkaBln, 1)->endOfMonth();
            $teksStokAkhir = $bulanEn[$angkaBln - 1] . ' ' . $tanggalAkhir->format('d');
        } else {
            $teksStokAkhir = 'August 31';
        }

        $monthTranslations = [
            'January' => 'January', 'February' => 'February', 'March' => 'March',
            'April' => 'April', 'May' => 'May', 'June' => 'June',
            'July' => 'July', 'August' => 'August', 'September' => 'September',
            'October' => 'October', 'November' => 'November', 'December' => 'December'
        ];

        return [$stockForecast, $tigaBulanTerakhir, $bulanAktif, $teksStokAkhir, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi];
    }

    public function forecast(Request $request)
    {
        if (!$this->hasForecastAccess()) {
            abort(403, 'You do not have access to the Sales Forecast page.');
        }

        $tahun = $request->input('tahun', date('Y'));
        $bulanTersediaUrut = $this->urutanBulan;

        [$stockForecast, $tigaBulanTerakhir, $bulanAktif, $teksStokAkhir, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi] =
            $this->buildForecastData($tahun, $request->input('bulan_akhir'));

        return view('users.sales.forecast', [
            'title' => 'Sales Forecast & Stock Estimation',
            'stockForecast' => $stockForecast,
            'tigaBulanTerakhir' => $tigaBulanTerakhir,
            'bulanTersediaUrut' => $bulanTersediaUrut,
            'bulanAktif' => $bulanAktif,
            'monthTranslations' => $monthTranslations,
            'tahun' => $tahun,
            'teksStokAkhir' => $teksStokAkhir,
            'activePercentage' => $activePercentage, 
            'activeRefMonths' => $activeRefMonths,
            'activeDoi' => $activeDoi,
            'hasFullAccess' => $this->hasFullSalesAccess(), 
            'listTahun' => Sales::selectRaw('DISTINCT YEAR(date) as tahun')->orderBy('tahun', 'desc')->pluck('tahun')->toArray()
        ]);
    }

    public function exportExcel(Request $request)
    {
        if (!$this->hasForecastAccess()) {
            abort(403, 'You do not have access to the Sales Forecast page.');
        }

        $tahun = $request->input('tahun', date('Y'));

        [$stockForecast, $tigaBulanTerakhir, $bulanAktif, $teksStokAkhir, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi] =
            $this->buildForecastData($tahun, $request->input('bulan_akhir'));

        $filename = 'Sales_Forecast_' . $bulanAktif . '_' . $tahun . '.xlsx';

        return Excel::download(
            new ForecastExport($stockForecast, $tigaBulanTerakhir, $bulanAktif, $tahun, $teksStokAkhir, $activePercentage, $activeRefMonths, $activeDoi, $monthTranslations),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        if (!$this->hasForecastAccess()) {
            abort(403, 'You do not have access to the Sales Forecast page.');
        }

        $tahun = $request->input('tahun', date('Y'));

        [$stockForecast, $tigaBulanTerakhir, $bulanAktif, $teksStokAkhir, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi] =
            $this->buildForecastData($tahun, $request->input('bulan_akhir'));

        $pdf = Pdf::loadView('pdf.sales.forecast', [
            'stockForecast' => $stockForecast,
            'tigaBulanTerakhir' => $tigaBulanTerakhir,
            'bulanAktif' => $bulanAktif,
            'tahun' => $tahun,
            'teksStokAkhir' => $teksStokAkhir,
            'activePercentage' => $activePercentage,
            'activeRefMonths' => $activeRefMonths,
            'activeDoi' => $activeDoi,
            'monthTranslations' => $monthTranslations,
        ])->setPaper('a4', 'landscape');

        $filename = 'Forecast_' . $bulanAktif . '_' . $tahun . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function storeSuggestedOrder(Request $request)
    {
        $user = Auth::user();
        $jabatan = strtolower($user->jabatan ?? '');
        $isAdminGudang = Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang';
        $isLegalPurchasing = Str::contains($jabatan, 'legal & purchasing') || Str::contains($jabatan, 'purchasing');

        if ($isAdminGudang || $isLegalPurchasing) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action for this role.'], 403);
        }

        if (!$this->hasForecastAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'tahun'         => 'required|integer',
            'bulan_acuan'   => 'required|string',
            'nama_produk'   => 'required|string',
            'forecast_qty'  => 'required|numeric',
            'suggested_qty' => 'nullable|numeric',
            'moq'           => 'nullable|numeric|min:1',
        ]);

        $userId = Auth::id();
        $cleanQty = $request->suggested_qty !== null && $request->suggested_qty !== '' 
                    ? str_replace('.', '', $request->suggested_qty) 
                    : 0;

        SalesForecastOrder::updateOrCreate(
            ['year' => $request->tahun, 'month' => $request->bulan_acuan, 'product_name' => $request->nama_produk],
            ['forecast_qty' => $request->forecast_qty, 'suggested_qty' => (float)$cleanQty, 'moq' => $request->filled('moq') ? (float)$request->moq : 1, 'user_id' => $userId]
        );

        return response()->json(['success' => true, 'message' => 'Saved successfully']);
    }

    public function saveForecastSettings(Request $request)
    {
        if (!$this->hasFullSalesAccess()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'tahun' => 'required|integer',
            'bulan_acuan' => 'required|string',
            'percentage' => 'nullable|numeric|min:0|max:500',
            'ref_months' => 'nullable|integer|min:1|max:12',
            'doi' => 'nullable|integer|min:1|max:365',
        ]);

        $data = [];
        if ($request->filled('percentage')) $data['percentage'] = $request->percentage;
        if ($request->filled('ref_months')) $data['ref_months'] = $request->ref_months;
        if ($request->filled('doi')) $data['doi'] = $request->doi;

        if (empty($data)) {
            return back()->with('error', 'Tidak ada pengaturan yang dikirim.');
        }

        SalesForecastSetting::updateOrCreate(
            ['year' => $request->tahun, 'month' => $request->bulan_acuan],
            $data
        );

        return redirect()->back()->with('success', 'Pengaturan forecast berhasil diperbarui!');
    }
}
