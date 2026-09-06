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
     * @return array [$stockForecast, $tigaBulanTerakhir, $bulanAktif, $teksStokAkhir, $activePercentage, $monthTranslations]
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

        // 2. Ambil 3 bulan KE BELAKANG SEBELUM bulan aktif yang dipilih
        $indexAktif = array_search($bulanAktif, $bulanTersediaUrut);
        if ($indexAktif !== false && $indexAktif >= 3) {
            $tigaBulanTerakhir = array_slice($bulanTersediaUrut, $indexAktif - 3, 3);
        } else {
            $tigaBulanTerakhir = array_slice($bulanTersediaUrut, max(0, $indexAktif - 3), $indexAktif);
            if (empty($tigaBulanTerakhir)) {
                $tigaBulanTerakhir = ['June', 'July', 'August'];
            }
        }

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
        
        // 4. Fetch Real-time Stock Data & Unit from Barang Model
        $stokSaatIni = [];
        $satuanStok = [];
        if (class_exists('\App\Models\Barang')) {
            $namaProdukArray = $groupedByProduk->keys()->toArray();
            $barangs = \App\Models\Barang::whereIn('product_name', $namaProdukArray)->get();
            foreach ($barangs as $brg) {
                $stokSaatIni[$brg->product_name] = $brg->stock;
                $satuanStok[$brg->product_name] = $brg->unit ?? 'Pcs';
            }
        }

        // 5. Fetch Existing Saved Suggested Orders from Database
        $savedOrders = DB::table('sales_forecast_orders')
            ->where('year', $tahun)
            ->where('month', $bulanAktif)
            ->pluck('suggested_qty', 'product_name')
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
                $stockForecast[] = [
                    'nama_produk'    => $produk,
                    'satuan_sales'   => $satuanSales,
                    'satuan_stok'    => $satuanStok[$produk] ?? 'Pcs',
                    'total_qty'      => (int) $total3Bulan,
                    'avg_qty'        => round($avg, 2),
                    'forecast_qty'   => $forecast,
                    'detail_bulan'   => $detailBulan,
                    'ps_breakdown'   => $psBreakdown,
                    'stok_tersedia'  => $stokSaatIni[$produk] ?? 0, 
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

        return [$stockForecast, $tigaBulanTerakhir, $bulanAktif, $teksStokAkhir, $activePercentage, $monthTranslations];
    }

    public function forecast(Request $request)
    {
        if (!$this->hasForecastAccess()) {
            abort(403, 'You do not have access to the Sales Forecast page.');
        }

        $tahun = $request->input('tahun', date('Y'));
        $bulanTersediaUrut = $this->urutanBulan;

        [$stockForecast, $tigaBulanTerakhir, $bulanAktif, $teksStokAkhir, $activePercentage, $monthTranslations] =
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

        [$stockForecast, $tigaBulanTerakhir, $bulanAktif, $teksStokAkhir, $activePercentage, $monthTranslations] =
            $this->buildForecastData($tahun, $request->input('bulan_akhir'));

        $filename = 'Sales_Forecast_' . $bulanAktif . '_' . $tahun . '.xlsx';

        return Excel::download(
            new ForecastExport($stockForecast, $tigaBulanTerakhir, $bulanAktif, $tahun, $teksStokAkhir, $activePercentage, $monthTranslations),
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
            'percentage' => 'required|numeric|min:0|max:500',
        ]);

        \App\Models\SalesForecastSetting::updateOrCreate(
            [
                'year' => $request->tahun,
                'month' => $request->bulan_acuan,
            ],
            [
                'percentage' => $request->percentage,
            ]
        );

        return redirect()->back()->with('success', 'Persentase buffer forecast berhasil diperbarui!');
    }
}
