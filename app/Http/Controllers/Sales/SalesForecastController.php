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

class SalesForecastController extends BaseSalesController
{
    /**
     * Hitung data forecast (dipakai bersama untuk view & export agar konsisten).
     *
     * @return array [$stockForecast, $tigaBulanTerakhir, $bulanAktif, $teksStokAkhir, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi]
     */
    protected function buildForecastData($tahun, $bulanAktif = null)
    {
        $bulanTersediaUrut = $this->urutanBulan;

        // 1. Tentukan bulan acuan (End Month yang dipilih)
        $namaBulan = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $defaultBulanAktif = ($tahun == date('Y')) ? $namaBulan[(int)date('n')] : 'September';

        $bulanAktif = ($bulanAktif && in_array($bulanAktif, $bulanTersediaUrut)) ? $bulanAktif : $defaultBulanAktif;

        // Ambil persentase kustom dari database, default 20% jika belum diatur
        $settingPersen = \App\Models\SalesForecastSetting::where('year', $tahun)
            ->where('month', $bulanAktif)
            ->value('percentage');
        
        $activePercentage = $settingPersen !== null ? (float)$settingPersen : 20.00;
        $multiplier = 1 + ($activePercentage / 100);

        // 2. Ambil N bulan KE BELAKANG SEBELUM bulan aktif yang dipilih (ref_months fleksibel, default 3)
        $settingRefMonths = \App\Models\SalesForecastSetting::where('year', $tahun)
            ->where('month', $bulanAktif)
            ->value('ref_months');
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

        // 2b. Ambil DOI (Days of Inventory, hari) dari database, default 30 jika belum diatur
        $settingDoi = \App\Models\SalesForecastSetting::where('year', $tahun)
            ->where('month', $bulanAktif)
            ->value('doi');
        $activeDoi = $settingDoi !== null ? (int)$settingDoi : 30;
        $activeDoi = max(1, min($activeDoi, 365));

        // 3. Fetch actual sales data
        $salesRaw = Sales::whereYear('date', $tahun)
            ->whereIn('month', $tigaBulanTerakhir)
            ->whereNotNull('product_name')
            ->where('product_name', '!=', '')
            ->select('product_name', 'ps', 'month', DB::raw('SUM(qty) as total_qty'), DB::raw('MAX(unit) as satuan'))
            ->groupBy('product_name', 'ps', 'month')
            ->get();

        // Normalisasi nama bulan agar sesuai (case-sensitive) saat diproses oleh Collection PHP
        $salesRaw->transform(function ($item) {
            $item->month = ucfirst(strtolower(trim($item->month)));
            return $item;
        });

        $groupedByProduk = $salesRaw->groupBy('product_name');
        
        // 4. Fetch Stock Data per tanggal akhir bulan acuan (snapshot harian terakhir ≤ akhir bulan).
        //    Fallback: stok real-time terbaru jika belum ada snapshot.
        $stokSaatIni = [];
        $satuanStok = [];
        $namaProdukArray = $groupedByProduk->keys()->toArray();

        // Tanggal akhir bulan terakhir pada 3 bulan referensi (bukan $bulanAktif yang dipilih user)
        $namaBulanIdx = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6,
            'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12,
        ];
        $bulanReferensiTerakhir = end($tigaBulanTerakhir); // bulan terakhir dari 3 bulan acuan
        $bulanAkhirTanggal = ($tahun && isset($namaBulanIdx[$bulanReferensiTerakhir]))
            ? \Carbon\Carbon::create((int)$tahun, $namaBulanIdx[$bulanReferensiTerakhir], 1)->endOfMonth()->format('Y-m-d')
            : date('Y-m-d');

        $products = \App\Models\Product::active()->whereIn('product_name', $namaProdukArray)->get();

        if ($products->isNotEmpty()) {
            // Ambil snapshot harian terakhir per produk dengan tanggal ≤ akhir bulan acuan
            $snapshots = DB::table('daily_stock_histories')
                ->whereIn('product_id', $products->pluck('id'))
                ->whereNotNull('product_id')
                ->where('tanggal', '<=', $bulanAkhirTanggal)
                ->select('product_id', 'tanggal', 'stok')
                ->orderBy('product_id')
                ->orderByDesc('tanggal')
                ->get();

            $snapshotTerakhir = $snapshots->groupBy('product_id')->map(fn($rows) => $rows->first());

            foreach ($products as $p) {
                $snap = $snapshotTerakhir->get($p->id);
                $stokSaatIni[$p->product_name] = $snap ? (int)$snap->stok : (int)$p->stock;
                $satuanStok[$p->product_name] = $p->unit ?? 'Pcs';
            }
        }

        // 5. Fetch Existing Saved Suggested Orders from Database
        $savedOrders = DB::table('sales_forecast_orders')
            ->where('year', $tahun)
            ->where('month', $bulanAktif)
            ->pluck('suggested_qty', 'product_name')
            ->toArray();

        // MOQ per produk (Minimum Order Quantity)
        $savedMoqs = DB::table('sales_forecast_orders')
            ->where('year', $tahun)
            ->where('month', $bulanAktif)
            ->pluck('moq', 'product_name')
            ->toArray();

        // 6. Calculate Forecast Data
        $stockForecast = [];
        foreach ($groupedByProduk as $produk => $rows) {
            $total3Bulan = $rows->sum('total_qty');
            $jumlahBulanTerpakai = count($tigaBulanTerakhir);
            
            $avg = $jumlahBulanTerpakai > 0 ? $total3Bulan / $jumlahBulanTerpakai : 0;
            $forecast = (int) ceil($avg * $multiplier); // Dinamis menggunakan persentase dari database

            $psBreakdown = [];
            foreach ($rows->groupBy('ps') as $psName => $psRows) {
                $namaPs = $psName ?: 'Others';
                $psBreakdown[$namaPs] = $psRows->sum('total_qty');
            }
            arsort($psBreakdown);

            $detailBulan = [];
            foreach ($tigaBulanTerakhir as $b) {
                $detailBulan[$b] = $rows->where('month', $b)->sum('total_qty');
            }

            $satuanRow = $rows->whereNotNull('satuan')->where('satuan', '!=', '')->first();
            $satuanSales = $satuanRow ? $satuanRow->satuan : 'Pcs';

            if ($avg > 0) {
                $buffer = $avg * ($activeDoi / 30);
                $moq = $savedMoqs[$produk] ?? 1;
                $moq = max(1, (float)$moq);

                // Order/Produksi = IF((endstock - forecast) < buffer) THEN CEILING(buffer - (endstock - forecast), moq) ELSE 0
                $endstock = $stokSaatIni[$produk] ?? 0;
                $bufferQty = ceil($buffer);
                if (($endstock - $forecast) < $bufferQty) {
                    $selisih = $bufferQty - ($endstock - $forecast);
                    $orderQty = $moq > 0 ? (int)(ceil($selisih / $moq) * $moq) : (int)$selisih;
                } else {
                    $orderQty = 0;
                }

                // DOI (Days of Inventory) = berapa hari stok cukup dengan kecepatan penjualan saat ini
                $doiHari = $avg > 0 ? (($endstock / $avg) * 30) : 0;
                $doiHari = max(0, round($doiHari, 1));

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

        // 7. Format Teks Tanggal Available Stock
        $bulanTerakhirTampil = end($tigaBulanTerakhir);
        $indeksBulanAkhirTampil = array_search($bulanTerakhirTampil, $this->urutanBulan);
        $bulanEn = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        if ($indeksBulanAkhirTampil !== false) {
            $mappingAngka = ['January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12];
            $angkaBln = $mappingAngka[$bulanTerakhirTampil] ?? 8;
            
            $tanggalAkhir = \Carbon\Carbon::create($tahun, $angkaBln, 1)->endOfMonth();
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

    /**
     * Export hasil forecast sebagai Excel (Maatwebsite/Laravel-Excel, FromView).
     */
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

    public function storeSuggestedOrder(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $jabatan = strtolower($user->jabatan ?? '');
        $isAdminGudang = \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang';
        $isLegalPurchasing = \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing') || \Illuminate\Support\Str::contains($jabatan, 'purchasing');

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

        $userId = \Illuminate\Support\Facades\Auth::id();
        $cleanQty = $request->suggested_qty !== null && $request->suggested_qty !== '' 
                    ? str_replace('.', '', $request->suggested_qty) 
                    : 0;

        \App\Models\SalesForecastOrder::updateOrCreate(
            [
                'year'        => $request->tahun,
                'month'       => $request->bulan_acuan,
                'product_name' => $request->nama_produk,
            ],
            [
                'forecast_qty'  => $request->forecast_qty,
                'suggested_qty' => (float)$cleanQty,
                'moq'           => $request->filled('moq') ? (float)$request->moq : 1,
                'user_id'       => $userId,
            ]
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
        if ($request->filled('percentage')) {
            $data['percentage'] = $request->percentage;
        }
        if ($request->filled('ref_months')) {
            $data['ref_months'] = $request->ref_months;
        }
        if ($request->filled('doi')) {
            $data['doi'] = $request->doi;
        }

        if (empty($data)) {
            return back()->with('error', 'Tidak ada pengaturan yang dikirim.');
        }

        \App\Models\SalesForecastSetting::updateOrCreate(
            [
                'year' => $request->tahun,
                'month' => $request->bulan_acuan,
            ],
            $data
        );

        return redirect()->back()->with('success', 'Pengaturan forecast berhasil diperbarui!');
    }
}
