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
     * Build forecast data (satu sumber kebenaran via ForecastService).
     *
     * @return array [$stockForecast, $bulanReferensi, $bulanAktif, $labelStokRealtime, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi]
     */
    protected function buildForecastData($tahun, $bulanAktif = null)
    {
        return app(\App\Services\ForecastService::class)->build($tahun, $bulanAktif);
    }

    public function forecast(Request $request)
    {
        if (!$this->hasForecastAccess()) {
            abort(403, 'You do not have access to the Sales Forecast page.');
        }

        $tahun = $request->input('tahun', date('Y'));
        $bulanTersediaUrut = $this->urutanBulan;

        [$stockForecast, $bulanReferensi, $bulanAktif, $labelStokRealtime, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi] =
            $this->buildForecastData($tahun, $request->input('bulan_akhir'));

        return view('users.sales.forecast', [
            'title' => 'Sales Forecast & Stock Estimation',
            'stockForecast' => $stockForecast,
            'bulanReferensi' => $bulanReferensi,
            'bulanTersediaUrut' => $bulanTersediaUrut,
            'bulanAktif' => $bulanAktif,
            'monthTranslations' => $monthTranslations,
            'tahun' => $tahun,
            'labelStokRealtime' => $labelStokRealtime,
            'activePercentage' => $activePercentage, 
            'activeRefMonths' => $activeRefMonths,
            'activeDoi' => $activeDoi,
            'hasFullAccess' => $this->hasFullSalesAccess(), 
            'listTahun' => Sales::selectRaw('DISTINCT YEAR(date) as tahun')->orderBy('tahun', 'desc')->pluck('tahun')->toArray()
        ]);
    }

    /**
     * Data tabel forecast sebagai JSON (untuk popup referensi di form pengajuan barang).
     * Memakai buildForecastData yang sama dengan halaman forecast (satu sumber kebenaran),
     * hanya field yang dibutuhkan form yang dikirim agar payload ringan.
     */
    public function forecastData(Request $request)
    {
        if (!$this->hasForecastAccess()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke data forecast.'], 403);
        }

        $tahun = $request->input('tahun', date('Y'));

        [$stockForecast, $bulanReferensi, $bulanAktif, $labelStokRealtime, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi] =
            $this->buildForecastData($tahun, $request->input('bulan_akhir'));

        $rows = array_map(fn($item) => [
            'nama_produk'   => $item['nama_produk'],
            'satuan_sales'  => $item['satuan_sales'],
            'satuan_stok'   => $item['satuan_stok'],
            'avg_qty'       => $item['avg_qty'],
            'forecast_qty'  => $item['forecast_qty'],
            'buffer_qty'    => $item['buffer_qty'],
            'order_qty'     => $item['order_qty'],
            'stok_tersedia' => $item['stok_tersedia'],
            'doi_qty'       => $item['doi_qty'],
        ], $stockForecast);

        return response()->json([
            'success' => true,
            'data' => $rows,
            'meta' => [
                'tahun' => $tahun,
                'bulan_aktif' => $bulanAktif,
                'ref_months' => $activeRefMonths,
                'percentage' => $activePercentage,
                'doi' => $activeDoi,
                'label_stok' => $labelStokRealtime,
                'total' => count($rows),
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        if (!$this->hasForecastAccess()) {
            abort(403, 'You do not have access to the Sales Forecast page.');
        }

        $tahun = $request->input('tahun', date('Y'));

        [$stockForecast, $bulanReferensi, $bulanAktif, $labelStokRealtime, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi] =
            $this->buildForecastData($tahun, $request->input('bulan_akhir'));

        $filename = 'Sales_Forecast_' . $bulanAktif . '_' . $tahun . '.xlsx';

        return Excel::download(
            new ForecastExport($stockForecast, $bulanReferensi, $bulanAktif, $tahun, $labelStokRealtime, $activePercentage, $activeRefMonths, $activeDoi, $monthTranslations),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        if (!$this->hasForecastAccess()) {
            abort(403, 'You do not have access to the Sales Forecast page.');
        }

        $tahun = $request->input('tahun', date('Y'));

        [$stockForecast, $bulanReferensi, $bulanAktif, $labelStokRealtime, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi] =
            $this->buildForecastData($tahun, $request->input('bulan_akhir'));

        $pdf = Pdf::loadView('pdf.sales.forecast', [
            'stockForecast' => $stockForecast,
            'bulanReferensi' => $bulanReferensi,
            'bulanAktif' => $bulanAktif,
            'tahun' => $tahun,
            'labelStokRealtime' => $labelStokRealtime,
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
