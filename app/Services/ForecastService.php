<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sales;
use App\Models\SalesForecastSetting;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya sumber kebenaran data forecast penjualan + estimasi stok.
 * Dipakai halaman Sales Forecast, export, SPH, dan tab Monitoring Product gudang.
 */
class ForecastService
{
    /** Urutan bulan standar untuk sorting dan label. */
    public const URUTAN_BULAN = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
    ];

    /**
     * Bangun data forecast.
     *
     * @return array [$stockForecast, $bulanReferensi, $bulanAktif, $labelStokRealtime, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi]
     */
    public function build($tahun, $bulanAktif = null): array
    {
        $bulanTersediaUrut = self::URUTAN_BULAN;

        // Bulan aktif.
        $namaBulan = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $defaultBulanAktif = ($tahun == date('Y')) ? $namaBulan[(int) date('n')] : 'September';
        $bulanAktif = ($bulanAktif && in_array($bulanAktif, $bulanTersediaUrut)) ? $bulanAktif : $defaultBulanAktif;

        // Persentase.
        $settingPersen = SalesForecastSetting::where('year', $tahun)->where('month', $bulanAktif)->value('percentage');
        $activePercentage = $settingPersen !== null ? (float) $settingPersen : 20.00;
        $multiplier = 1 + ($activePercentage / 100);

        // Bulan referensi.
        $settingRefMonths = SalesForecastSetting::where('year', $tahun)->where('month', $bulanAktif)->value('ref_months');
        $activeRefMonths = $settingRefMonths !== null ? (int) $settingRefMonths : 6;
        $activeRefMonths = max(1, min($activeRefMonths, 12));

        $indexAktif = array_search($bulanAktif, $bulanTersediaUrut);
        if ($indexAktif !== false && $indexAktif >= $activeRefMonths) {
            $bulanReferensi = array_slice($bulanTersediaUrut, $indexAktif - $activeRefMonths, $activeRefMonths);
        } elseif ($indexAktif !== false && $indexAktif > 0) {
            $bulanReferensi = array_slice($bulanTersediaUrut, 0, $indexAktif);
        } else {
            $bulanReferensi = array_slice($bulanTersediaUrut, 0, $activeRefMonths);
        }

        // Target DOI.
        $settingDoi = SalesForecastSetting::where('year', $tahun)->where('month', $bulanAktif)->value('doi');
        $activeDoi = $settingDoi !== null ? (int) $settingDoi : 30;
        $activeDoi = max(1, min($activeDoi, 365));

        // Data penjualan.
        $salesRaw = Sales::whereYear('date', $tahun)
            ->whereIn('month', $bulanReferensi)
            ->whereNotNull('product_name')->where('product_name', '!=', '')
            ->select('product_name', 'ps', 'month', DB::raw('SUM(qty) as total_qty'), DB::raw('MAX(unit) as satuan'))
            ->groupBy('product_name', 'ps', 'month')
            ->get();

        $salesRaw->transform(function ($item) {
            $item->month = ucfirst(strtolower(trim($item->month)));
            return $item;
        });

        $groupedByProduk = $salesRaw->groupBy('product_name');

        // End Stock REALTIME: langsung dari products.stock yang di-update
        // StockMoveService setiap ada mutasi masuk/keluar di Warehouse Ops.
        // (Dulu pakai daily_stock_histories snapshot harian -> basi, tidak realtime.)
        $stokSaatIni = [];
        $satuanStok = [];
        $namaProdukArray = $groupedByProduk->keys()->toArray();

        $products = Product::active()->whereIn('product_name', $namaProdukArray)->get();

        if ($products->isNotEmpty()) {
            foreach ($products as $p) {
                $stokSaatIni[$p->product_name] = (int) ($p->stock ?? 0);
                $satuanStok[$p->product_name] = $p->unit ?? 'Pcs';
            }
        }

        // Order tersimpan dan MOQ.
        $savedOrders = DB::table('sales_forecast_orders')
            ->where('year', $tahun)->where('month', $bulanAktif)
            ->pluck('suggested_qty', 'product_name')->toArray();

        $savedMoqs = DB::table('sales_forecast_orders')
            ->where('year', $tahun)->where('month', $bulanAktif)
            ->pluck('moq', 'product_name')->toArray();

        // Hitung.
        $stockForecast = [];
        foreach ($groupedByProduk as $produk => $rows) {
            $totalRefBulan = $rows->sum('total_qty');
            $jumlahBulanTerpakai = count($bulanReferensi);

            $avg = $jumlahBulanTerpakai > 0 ? $totalRefBulan / $jumlahBulanTerpakai : 0;
            $forecast = (int) ceil($avg * $multiplier);

            $psBreakdown = [];
            foreach ($rows->groupBy('ps') as $psName => $psRows) {
                $psBreakdown[$psName ?: 'Others'] = $psRows->sum('total_qty');
            }
            arsort($psBreakdown);

            $detailBulan = [];
            foreach ($bulanReferensi as $b) {
                $detailBulan[$b] = $rows->where('month', $b)->sum('total_qty');
            }

            $satuanRow = $rows->whereNotNull('satuan')->where('satuan', '!=', '')->first();
            $satuanSales = $satuanRow ? $satuanRow->satuan : 'Pcs';

            if ($avg > 0) {
                // Buffer = Average * (DOI / 30)
                $buffer = $avg * ($activeDoi / 30);
                $moq = max(1, (float) ($savedMoqs[$produk] ?? 1));

                // Order = IF((End Stock - Forecast) < Buffer) THEN CEILING(..., MOQ) ELSE 0
                $endstock = $stokSaatIni[$produk] ?? 0;
                $bufferQty = ceil($buffer);
                if (($endstock - $forecast) < $bufferQty) {
                    $selisih = $bufferQty - ($endstock - $forecast);
                    $orderQty = (int) (ceil($selisih / $moq) * $moq);
                } else {
                    $orderQty = 0;
                }

                // DOI = (End Stock / Average) * 30
                $doiHasil = $avg > 0 ? ceil(($endstock / $avg) * 30) : 0;
                $doiHasil = max(0, (int) $doiHasil);

                $stockForecast[] = [
                    'nama_produk' => $produk,
                    'satuan_sales' => $satuanSales,
                    'satuan_stok' => $satuanStok[$produk] ?? 'Pcs',
                    'total_qty' => (int) $totalRefBulan,
                    'avg_qty' => round($avg, 2),
                    'forecast_qty' => $forecast,
                    'buffer_qty' => (int) $bufferQty,
                    'doi_qty' => $doiHasil,
                    'moq' => $moq,
                    'order_qty' => $orderQty,
                    'detail_bulan' => $detailBulan,
                    'ps_breakdown' => $psBreakdown,
                    'stok_tersedia' => $endstock,
                    'suggested_order' => $savedOrders[$produk] ?? '',
                ];
            }
        }

        usort($stockForecast, fn ($a, $b) => $b['forecast_qty'] <=> $a['forecast_qty']);

        // Label tanggal stok realtime: selalu hari ini karena baca products.stock langsung.
        $labelStokRealtime = now()->format('d M, Y');

        $monthTranslations = [
            'January' => 'January', 'February' => 'February', 'March' => 'March',
            'April' => 'April', 'May' => 'May', 'June' => 'June',
            'July' => 'July', 'August' => 'August', 'September' => 'September',
            'October' => 'October', 'November' => 'November', 'December' => 'December',
        ];

        return [$stockForecast, $bulanReferensi, $bulanAktif, $labelStokRealtime, $activePercentage, $activeRefMonths, $monthTranslations, $activeDoi];
    }

    /**
     * Peta ringkas + meta bulan aktif dalam sekali hitung.
     *
     * @return array{map:array, bulan:string, doi:int, percentage:float}
     */
    public function buildMapped($tahun, $bulanAktif = null): array
    {
        [$stockForecast, , $bulanAktif, , $activePercentage, , , $activeDoi] = $this->build($tahun, $bulanAktif);
        $map = [];
        foreach ($stockForecast as $item) {
            $map[$this->normalize($item['nama_produk'])] = [
                'forecast_qty' => (int) $item['forecast_qty'],
                'order_qty' => (int) $item['order_qty'],
                'doi_qty' => (int) $item['doi_qty'],
                'avg_qty' => (float) $item['avg_qty'],
            ];
        }

        return ['map' => $map, 'bulan' => $bulanAktif, 'doi' => $activeDoi, 'percentage' => $activePercentage];
    }

    /**
     * Peta ringkas per nama produk: forecast, order, DOI.
     * Kunci dinormalisasi (lowercase+trim) agar toleran beda kapital/spasi.
     *
     * @return array<string, array{forecast_qty:int, order_qty:int, doi_qty:int, avg_qty:float}>
     */
    public function mapByProduct($tahun, $bulanAktif = null): array
    {
        return $this->buildMapped($tahun, $bulanAktif)['map'];
    }

    /** Cari data forecast untuk satu produk (cocok product_name dulu, lalu product_name_clean). */
    public function forProduct(array $map, ?string $productName, ?string $cleanName = null): ?array
    {
        if ($productName !== null && isset($map[$this->normalize($productName)])) {
            return $map[$this->normalize($productName)];
        }
        if ($cleanName !== null && $cleanName !== '' && isset($map[$this->normalize($cleanName)])) {
            return $map[$this->normalize($cleanName)];
        }

        return null;
    }

    public function normalize(?string $value): string
    {
        return strtolower(trim((string) $value));
    }
}
