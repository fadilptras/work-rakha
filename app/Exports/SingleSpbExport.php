<?php

namespace App\Exports;

use App\Models\PengajuanBarang;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SingleSpbExport implements FromView, ShouldAutoSize, WithTitle, WithStyles
{
    protected PengajuanBarang $pengajuanBarang;

    public function __construct(PengajuanBarang $pengajuanBarang)
    {
        $this->pengajuanBarang = $pengajuanBarang;
    }

    public function view(): View
    {
        $this->pengajuanBarang->loadMissing(['user', 'approver1', 'approver2', 'approver3', 'approver4']);
        return view('exports.excel.spb-single', [
            'pengajuan' => $this->pengajuanBarang,
        ]);
    }

    public function title(): string
    {
        $clean = preg_replace('/[^A-Za-z0-9\-]/', '_', $this->pengajuanBarang->nomor_surat ?? 'SPB');
        return substr($clean, 0, 30);
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}
