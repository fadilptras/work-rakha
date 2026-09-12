<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ForecastExport implements FromView, WithStyles, WithColumnWidths, WithTitle
{
    protected $stockForecast;
    protected $tigaBulanTerakhir;
    protected $bulanAktif;
    protected $tahun;
    protected $teksStokAkhir;
    protected $activePercentage;
    protected $activeRefMonths;
    protected $activeDoi;
    protected $monthTranslations;

    public function __construct(
        $stockForecast,
        $tigaBulanTerakhir,
        $bulanAktif,
        $tahun,
        $teksStokAkhir,
        $activePercentage,
        $activeRefMonths,
        $activeDoi,
        $monthTranslations
    ) {
        $this->stockForecast = $stockForecast;
        $this->tigaBulanTerakhir = $tigaBulanTerakhir;
        $this->bulanAktif = $bulanAktif;
        $this->tahun = $tahun;
        $this->teksStokAkhir = $teksStokAkhir;
        $this->activePercentage = $activePercentage;
        $this->activeRefMonths = $activeRefMonths;
        $this->activeDoi = $activeDoi;
        $this->monthTranslations = $monthTranslations;
    }

    public function view(): View
    {
        return view('exports.excel.forecast', [
            'stockForecast' => $this->stockForecast,
            'tigaBulanTerakhir' => $this->tigaBulanTerakhir,
            'bulanAktif' => $this->bulanAktif,
            'tahun' => $this->tahun,
            'teksStokAkhir' => $this->teksStokAkhir,
            'activePercentage' => $this->activePercentage,
            'activeRefMonths' => $this->activeRefMonths,
            'activeDoi' => $this->activeDoi,
            'monthTranslations' => $this->monthTranslations,
        ]);
    }

    public function title(): string
    {
        return 'Forecast ' . $this->bulanAktif . ' ' . $this->tahun;
    }

    public function columnWidths(): array
    {
        $n = count($this->tigaBulanTerakhir);
        $widths = [
            'A' => 5,
            'B' => 42,
        ];
        for ($i = 0; $i < $n; $i++) {
            $col = Coordinate::stringFromColumnIndex(3 + $i);
            $widths[$col] = 12;
        }
        $base = 3 + $n;
        $fixed = [
            0 => 13, // Total
            1 => 13, // Average
            2 => 15, // Forecast
            3 => 12, // Buffer
            4 => 15, // End Stock
            5 => 10, // DOI
            6 => 8,  // MOQ
            7 => 15, // Order
        ];
        foreach ($fixed as $offset => $w) {
            $col = Coordinate::stringFromColumnIndex($base + $offset);
            $widths[$col] = $w;
        }
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $n = count($this->tigaBulanTerakhir);
        $lastColIndex = 2 + $n + 8;
        $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
        // New simple layout: header at row 7, data at row 8
        $headerRow = 7;
        $dataStart = 8;
        $dataEnd = $dataStart + count($this->stockForecast) - 1;
        if ($dataEnd < $dataStart) $dataEnd = $dataStart;

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => '1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            2 => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['size' => 7, 'color' => ['argb' => '64748B']]],
            4 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => '1E40AF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'EFF6FF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'DBEAFE']]],
            ],
            5 => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['size' => 8, 'color' => ['argb' => '334155']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'F8FAFC']], 'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'E2E8F0']]]],
        ];

        // Header row - simple, clean
        $styles[$headerRow] = [
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '64748B']]],
        ];
        // Header fills - simple: dark for No/Product, grey for months/history, blue for Forecast, indigo for Order, rest grey
        $colNo = 'A';
        $colProduct = 'B';
        $styles["{$colNo}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '1E293B']]];
        $styles["{$colProduct}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '1E293B']]];
        if ($n > 0) {
            $c1 = Coordinate::stringFromColumnIndex(3);
            $c2 = Coordinate::stringFromColumnIndex(2 + $n);
            $styles["{$c1}{$headerRow}:{$c2}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '334155']]];
        }
        $colTotal = Coordinate::stringFromColumnIndex(3 + $n);
        $colAvg = Coordinate::stringFromColumnIndex(4 + $n);
        $colForecast = Coordinate::stringFromColumnIndex(5 + $n);
        $colBuffer = Coordinate::stringFromColumnIndex(6 + $n);
        $colStock = Coordinate::stringFromColumnIndex(7 + $n);
        $colDoi = Coordinate::stringFromColumnIndex(8 + $n);
        $colMoq = Coordinate::stringFromColumnIndex(9 + $n);
        $colOrder = Coordinate::stringFromColumnIndex(10 + $n);
        $styles["{$colTotal}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '475569']]];
        $styles["{$colAvg}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '475569']]];
        $styles["{$colForecast}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '1E40AF']]];
        $styles["{$colBuffer}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '475569']]];
        $styles["{$colStock}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '475569']]];
        $styles["{$colDoi}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '475569']]];
        $styles["{$colMoq}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '475569']]];
        $styles["{$colOrder}{$headerRow}"] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '3730A3']]];

        // Data area - simple, no per-column background except Order
        $styles["A{$dataStart}:{$lastCol}{$dataEnd}"] = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'E2E8F0']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'font' => ['size' => 8],
        ];
        $styles["A{$dataStart}:A{$dataEnd}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        if ($n > 0) {
            $styles["{$c1}{$dataStart}:{$c2}{$dataEnd}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        }
        $styles["{$colTotal}{$dataStart}:{$colTotal}{$dataEnd}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0']];
        $styles["{$colAvg}{$dataStart}:{$colAvg}{$dataEnd}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0.00']];
        $styles["{$colForecast}{$dataStart}:{$colForecast}{$dataEnd}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0'], 'font' => ['bold' => true, 'color' => ['argb' => '1E40AF']]];
        $styles["{$colBuffer}{$dataStart}:{$colBuffer}{$dataEnd}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0']];
        $styles["{$colStock}{$dataStart}:{$colStock}{$dataEnd}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0']];
        $styles["{$colDoi}{$dataStart}:{$colDoi}{$dataEnd}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0.0']];
        $styles["{$colMoq}{$dataStart}:{$colMoq}{$dataEnd}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0']];
        $styles["{$colOrder}{$dataStart}:{$colOrder}{$dataEnd}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0'], 'font' => ['bold' => true, 'color' => ['argb' => '3730A3']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'EEF2FF']]];

        // Freeze and filter
        $sheet->freezePane('A8');
        $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$dataEnd}");
        $sheet->getRowDimension($headerRow)->setRowHeight(36);
        for ($r = $dataStart; $r <= $dataEnd; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(22);
        }

        return $styles;
    }
}
