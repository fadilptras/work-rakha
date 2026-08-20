<?php

namespace App\Exports;

use App\Models\PengajuanBarang;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class RekapPurchaseOrderExport implements WithMultipleSheets
{
    use Exportable;

    protected $bulan;

    public function __construct($bulan = null)
    {
        $this->bulan = $bulan;
    }

    public function sheets(): array
    {
        $sheets = [];

        $query = PengajuanBarang::whereIn('status', ['disetujui', 'diproses', 'selesai'])
            ->orderBy('created_at', 'asc');
            
        if ($this->bulan) {
            $year = substr($this->bulan, 0, 4);
            $month = substr($this->bulan, 5, 2);
            $query->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
        }

        $pengajuanData = $query->get();

        // Kelompokkan data berdasarkan Bulan dan Tahun (contoh: "Maret 2026")
        $groupedData = $pengajuanData->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->locale('id')->isoFormat('MMMM YYYY');
        });

        // Buat sheet baru untuk setiap bulan
        foreach ($groupedData as $bulanTahun => $data) {
            $sheets[] = new Sheets\RekapPOMonthSheet($bulanTahun, $data);
        }

        return $sheets;
    }
}
