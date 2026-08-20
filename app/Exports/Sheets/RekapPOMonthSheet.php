<?php

namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPOMonthSheet implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    private $bulanTahun;
    private $pengajuanData;

    public function __construct($bulanTahun, $pengajuanData)
    {
        $this->bulanTahun = $bulanTahun;
        $this->pengajuanData = $pengajuanData;
    }

    public function view(): View
    {
        return view('admin.pengajuan-barang.exports.rekap-excel', [
            'bulanTahun' => $this->bulanTahun,
            'pengajuanBarangs' => $this->pengajuanData
        ]);
    }

    public function title(): string
    {
        return $this->bulanTahun; // Nama sheet akan menjadi "Maret 2026"
    }

    public function styles(Worksheet $sheet)
    {
        // Styling tambahan opsional jika diperlukan
        return [];
    }
}
