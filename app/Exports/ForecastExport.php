<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ForecastExport implements FromView, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $stockForecast;
    protected $bulanReferensi;
    protected $bulanAktif;
    protected $tahun;
    protected $labelStokRealtime;
    protected $activePercentage;
    protected $activeRefMonths;
    protected $activeDoi;
    protected $monthTranslations;

    public function __construct(
        $stockForecast,
        $bulanReferensi,
        $bulanAktif,
        $tahun,
        $labelStokRealtime,
        $activePercentage,
        $activeRefMonths,
        $activeDoi,
        $monthTranslations
    ) {
        $this->stockForecast = $stockForecast;
        $this->bulanReferensi = $bulanReferensi;
        $this->bulanAktif = $bulanAktif;
        $this->tahun = $tahun;
        $this->labelStokRealtime = $labelStokRealtime;
        $this->activePercentage = $activePercentage;
        $this->activeRefMonths = $activeRefMonths;
        $this->activeDoi = $activeDoi;
        $this->monthTranslations = $monthTranslations;
    }

    public function view(): View
    {
        return view('exports.excel.forecast', [
            'stockForecast' => $this->stockForecast,
            'bulanReferensi' => $this->bulanReferensi,
            'bulanAktif' => $this->bulanAktif,
            'tahun' => $this->tahun,
            'labelStokRealtime' => $this->labelStokRealtime,
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
        $n = count($this->bulanReferensi);
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
        $n = count($this->bulanReferensi);
        $lastColIndex = 2 + $n + 8;
        $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
        // Layout: baris 7 = header grup, baris 8 = header kolom, data mulai baris 9
        $groupRow = 7;
        $headerRow = 8;
        $dataStart = 9;
        $dataEnd = $dataStart + count($this->stockForecast) - 1;
        if ($dataEnd < $dataStart) $dataEnd = $dataStart;
        $hasData = count($this->stockForecast) > 0;
        $totalRow = $dataEnd + 1;

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

        // Baris header grup
        $styles[$groupRow] = [
            'font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '64748B']]],
        ];
        $sheet->getRowDimension($groupRow)->setRowHeight(20);

        // Baris TOTAL (tebal + format angka ikut)
        if ($hasData) {
            $styles["A{$totalRow}:{$lastCol}{$totalRow}"] = [
                'font' => ['bold' => true, 'size' => 8],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '94A3B8']]],
            ];
            $sheet->getRowDimension($totalRow)->setRowHeight(22);
        }

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
        // Rentang format angka mencakup baris TOTAL bila ada data
        $rowLast = $hasData ? $totalRow : $dataEnd;
        $styles["{$colTotal}{$dataStart}:{$colTotal}{$rowLast}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0']];
        $styles["{$colAvg}{$dataStart}:{$colAvg}{$rowLast}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0.00']];
        $styles["{$colForecast}{$dataStart}:{$colForecast}{$rowLast}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0'], 'font' => ['bold' => true, 'color' => ['argb' => '1E40AF']]];
        $styles["{$colBuffer}{$dataStart}:{$colBuffer}{$rowLast}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0']];
        $styles["{$colStock}{$dataStart}:{$colStock}{$rowLast}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0']];
        $styles["{$colDoi}{$dataStart}:{$colDoi}{$rowLast}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0']];
        $styles["{$colMoq}{$dataStart}:{$colMoq}{$rowLast}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0']];
        $styles["{$colOrder}{$dataStart}:{$colOrder}{$rowLast}"] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'numberFormat' => ['formatCode' => '#,##0'], 'font' => ['bold' => true, 'color' => ['argb' => '3730A3']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'EEF2FF']]];

        // Freeze and filter
        $sheet->freezePane('A9');
        $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$dataEnd}");
        $sheet->getRowDimension($headerRow)->setRowHeight(36);
        for ($r = $dataStart; $r <= $dataEnd; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(22);
        }

        return $styles;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $n = count($this->bulanReferensi);
                $sheet = $event->sheet->getDelegate();

                // Merge sel header grup (cadangan bila colspan HTML belum merge otomatis)
                $sheet->mergeCells('A7:B7');
                if ($n > 0) {
                    $c1 = Coordinate::stringFromColumnIndex(3);
                    $c2 = Coordinate::stringFromColumnIndex(2 + $n);
                    $sheet->mergeCells("{$c1}7:{$c2}7");
                }
                $colTotal = Coordinate::stringFromColumnIndex(3 + $n);
                $colAvg = Coordinate::stringFromColumnIndex(4 + $n);
                $colOrder = Coordinate::stringFromColumnIndex(10 + $n);
                $sheet->mergeCells("{$colTotal}7:{$colAvg}7");
                $colCalcStart = Coordinate::stringFromColumnIndex(5 + $n);
                $sheet->mergeCells("{$colCalcStart}7:{$colOrder}7");

                // Merge label TOTAL
                $count = count($this->stockForecast);
                if ($count > 0 && $n > 0) {
                    $totalRow = 9 + $count;
                    $cBulanAkhir = Coordinate::stringFromColumnIndex(2 + $n);
                    $sheet->mergeCells("A{$totalRow}:{$cBulanAkhir}{$totalRow}");
                }

                // Belang-belang baris data (abu sangat muda di baris genap)
                for ($r = 9; $r < 9 + $count; $r += 2) {
                    $lastCol = Coordinate::stringFromColumnIndex(2 + $n + 8);
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF8FAFC');
                }
            },
        ];
    }
}


