<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPengajuanBarangExport implements FromView, ShouldAutoSize, WithTitle, WithStyles
{
    protected Collection $items;
    protected string $periodText;

    public function __construct(Collection $items, string $periodText = 'Semua Periode')
    {
        $this->items = $items;
        $this->periodText = $periodText;
    }

    public function view(): View
    {
        return view('exports.excel.spb-all', [
            'pengajuanBarangs' => $this->items,
            'periodText' => $this->periodText,
        ]);
    }

    public function title(): string
    {
        return 'Rekap SPB';
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}
