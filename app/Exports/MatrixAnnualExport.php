<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MatrixAnnualExport implements FromView, WithStyles, WithColumnWidths
{
    protected $clients;
    protected $months;
    protected $year;

    public function __construct($clients, $months, $year)
    {
        $this->clients = $clients;
        $this->months = $months;
        $this->year = $year;
    }

    public function view(): View
    {
        return view('exports.matrix_recap', [
            'clients' => $this->clients,
            'months' => $this->months,
            'year' => $this->year,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 30,  // Client
            'C' => 25,  // Hospital
            'D' => 20,  // PIC (NEW!)
            'E' => 15,  // Area (Geser dari D)
            'F' => 35,  // Product (Geser dari E)
            
            // Income (Geser +1)
            'G' => 18, 'H' => 8, 'I' => 18,
            
            // Usage (Geser +1)
            'J' => 13, 'K' => 13, 'L' => 13, 'M' => 13, 'N' => 13, 'O' => 13,
            'P' => 13, 'Q' => 13, 'R' => 13, 'S' => 13, 'T' => 13, 'U' => 13,

            // Summary (Geser +1)
            'V' => 18, 'W' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $startRow = 4;
        $dataRow = 6;
        
        // HITUNG JUMLAH BARIS DINAMIS
        $dataRowsCount = 0;
        foreach($this->clients as $client) {
            $products = $client->interactions->filter(function($i) {
                return \Carbon\Carbon::parse($i->tanggal_interaksi)->year == $this->year;
            })->groupBy(fn($item) => $item->nama_produk ?: 'General');
            $count = $products->count();
            $dataRowsCount += ($count > 0 ? $count : 1);
        }

        $totalRow = $dataRow + $dataRowsCount; 
        $lastCol = 'W'; // Kolom Terakhir jadi W

        return [
            // HEADER
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],

            "A{$startRow}:{$lastCol}5" => [
                'font' => ['bold' => true, 'size' => 10], 
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],

            // Warna Header (Range Geser +1)
            "G{$startRow}:I5" => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'DBEAFE']]], // Income Blue
            "J{$startRow}:V5" => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FEE2E2']]], // Usage Red
            "W{$startRow}:W5" => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'D1FAE5']]], // Remain Green

            // BODY
            "A{$dataRow}:{$lastCol}{$totalRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            
            "B{$dataRow}:F{$totalRow}" => ['alignment' => ['wrapText' => true]], 
            "A{$dataRow}:A{$totalRow}" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            "D{$dataRow}:D{$totalRow}" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // PIC Center
            "E{$dataRow}:E{$totalRow}" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Area Center
            "H{$dataRow}:H{$totalRow}" => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // % Center

            // Number Format (Geser +1)
            "G{$dataRow}:G{$totalRow}" => ['numberFormat' => ['formatCode' => '#,##0']],
            "I{$dataRow}:W{$totalRow}" => ['numberFormat' => ['formatCode' => '#,##0']],

            // Highlight (Geser +1)
            "I{$dataRow}:I{$totalRow}" => ['font' => ['bold' => true, 'color' => ['rgb' => '1E3A8A']]], // Net Budget
            "V{$dataRow}:V{$totalRow}" => ['font' => ['bold' => true, 'color' => ['rgb' => '991B1B']]], // Tot Usage
            "W{$dataRow}:W{$totalRow}" => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'ECFDF5']]], // Remain

            // FOOTER
            "A{$totalRow}:{$lastCol}{$totalRow}" => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '1F2937']], 
                'font' => ['color' => ['argb' => 'FFFFFF']],
                'numberFormat' => ['formatCode' => '#,##0'],
            ],
            // Footer Colors (Geser +1)
            "I{$totalRow}" => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '1D4ED8']]], 
            "V{$totalRow}" => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'B91C1C']]], 
            "W{$totalRow}" => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '047857']]], 
            "J{$totalRow}:U{$totalRow}" => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'F3F4F6']],
                'font' => ['color' => ['argb' => '000000']],
            ],
        ];
    }
}