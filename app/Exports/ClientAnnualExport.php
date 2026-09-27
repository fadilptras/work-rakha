<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientAnnualExport implements FromView, ShouldAutoSize, WithColumnFormatting, WithStyles
{
    protected $client;
    protected $recap;
    protected $year;
    protected $totals;

    public function __construct($client, $recap, $year, $totals)
    {
        $this->client = $client;
        $this->recap = $recap;
        $this->year = $year;
        $this->totals = $totals;
    }

    public function view(): View
    {
        return view('exports.client_recap', [
            'client' => $this->client,
            'recap' => $this->recap,
            'year' => $this->year,
            'yearlyTotals' => $this->totals
        ]);
    }

    /**
     * 1. FORMAT ANGKA
     */
    public function columnFormats(): array
    {
        return [
            'B' => '#,##0', // Sales (In)
            'D' => '#,##0', // Value (Net)
            'E' => '#,##0', // Usage (Out)
            'F' => '#,##0', // Saldo
        ];
    }

    /**
     * 2. STYLING & OVERRIDE
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // --- FIX KHUSUS NO REKENING ---
            // Baris kop perusahaan disisip paling atas: No. Rekening geser E6 -> E7.
            'E7' => ['numberFormat' => ['formatCode' => NumberFormat::FORMAT_TEXT]],

            // --- STYLE LAINNYA ---

            // Baris 1-3 (Kop + Judul Utama)
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true, 'size' => 12]],

            // Baris 5 (Header Section Info: DATA KLIEN & DATA BANK)
            5 => ['font' => ['bold' => true, 'color' => ['rgb' => '0000FF']]],

            // Baris 15 (Header Tabel Data: BULAN, SALES, dll)
            15 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'EEEEEE'], // Abu-abu
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                ],
            ],
        ];
    }
}