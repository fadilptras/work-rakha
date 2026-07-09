<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class RekapAbsensiExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents
{
    protected $rekapData;
    protected $allDates;
    protected $startDate;
    protected $endDate;
    protected $holidays; // [BARU] Property Holidays
    protected $totalDays;

    // Start Kolom Tanggal (E = 5)
    protected $startDateColIndex = 5; 

    // [UPDATE] Constructor menerima $holidays
    public function __construct($rekapData, $allDates, $startDate, $endDate, $holidays)
    {
        $this->rekapData = collect($rekapData);
        $this->allDates = $allDates;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->holidays = $holidays; // Simpan data libur
        $this->totalDays = iterator_count($allDates);
    }

    public function collection()
    {
        return $this->rekapData;
    }

    public function map($row): array
    {
        static $no = 1;

        $dailyData = [];
        foreach ($this->allDates as $date) {
            $val = $row['daily'][$date->toDateString()] ?? '-';
            $dailyData[] = ($val === '' || $val === null) ? '-' : $val;
        }

        $summaryData = [
            $this->formatZero($row['summary']['H'] ?? 0),
            $this->formatZero($row['summary']['S'] ?? 0),
            $this->formatZero($row['summary']['I'] ?? 0),
            $this->formatZero($row['summary']['C'] ?? 0),
            $this->formatZero($row['summary']['A'] ?? 0),
            $this->formatZero($row['summary']['L'] ?? 0),
        ];

        $terlambat = $row['summary']['terlambat_formatted'] ?? '-';
        if ($terlambat === '0 Jam 0 Menit') $terlambat = '-';

        return array_merge(
            [
                $no++,
                $row['user']->name ?? '-',
                $row['user']->divisi ?? '-',
                $row['user']->jabatan ?? '-',
            ],
            $dailyData,
            $summaryData,
            [$terlambat]
        );
    }

    private function formatZero($value)
    {
        return ($value === 0 || $value === '0') ? '-' : $value;
    }

    public function headings(): array
    {
        $companyName = "PT RAKHA NUSANTARA MEDIKA";
        $title = "REKAP ABSENSI KARYAWAN - " . Carbon::parse($this->startDate)->isoFormat('MMMM YYYY');
        $period = "Periode: " . Carbon::parse($this->startDate)->format('d M Y') . " s/d " . Carbon::parse($this->endDate)->format('d M Y');

        $headerGroup = ['No', 'Nama Karyawan', 'Divisi', 'Jabatan']; 
        
        for ($i = 0; $i < $this->totalDays; $i++) {
            $headerGroup[] = ($i === 0) ? Carbon::parse($this->startDate)->isoFormat('MMMM YYYY') : '';
        }
        
        $headerGroup = array_merge($headerGroup, ['Rekap Kehadiran', '', '', '', '', '']);
        $headerGroup[] = 'Evaluasi';

        $headerColumns = ['', '', '', ''];
        
        foreach ($this->allDates as $date) {
            $headerColumns[] = $date->day;
        }
        
        $headerColumns = array_merge($headerColumns, ['H', 'S', 'I', 'C', 'A', 'L']);
        $headerColumns[] = 'Terlambat';

        return [
            [$companyName],
            [$title],
            [$period],
            [''],
            $headerGroup,
            $headerColumns
        ];
    }

    public function columnWidths(): array
    {
        $columns = ['A' => 5, 'B' => 30, 'C' => 20, 'D' => 20];
        $currentColIndex = $this->startDateColIndex;

        for ($i = 0; $i < $this->totalDays; $i++) {
            $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIndex);
            $columns[$colString] = 4;
            $currentColIndex++;
        }

        for ($j = 0; $j < 6; $j++) {
            $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIndex);
            $columns[$colString] = 5;
            $currentColIndex++;
        }

        $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIndex);
        $columns[$colString] = 25;

        return $columns;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true, 'size' => 10]],
            5 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $totalCols = 4 + $this->totalDays + 6 + 1;
                $lastColStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
                $lastRow = $sheet->getHighestRow();

                // Merge & Align Header
                $sheet->mergeCells("A1:{$lastColStr}1");
                $sheet->mergeCells("A2:{$lastColStr}2");
                $sheet->mergeCells("A3:{$lastColStr}3");
                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A5:A6');
                $sheet->mergeCells('B5:B6');
                $sheet->mergeCells('C5:C6');
                $sheet->mergeCells('D5:D6');

                $startDateCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->startDateColIndex);
                $endDateCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->startDateColIndex + $this->totalDays - 1);
                $sheet->mergeCells("{$startDateCol}5:{$endDateCol}5");

                $startSumColIndex = $this->startDateColIndex + $this->totalDays;
                $startSumCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startSumColIndex);
                $endSumCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startSumColIndex + 5);
                $sheet->mergeCells("{$startSumCol}5:{$endSumCol}5");

                // Border & Styling Dasar
                $styleBorder = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ];
                $sheet->getStyle("A5:{$lastColStr}{$lastRow}")->applyFromArray($styleBorder);

                $sheet->getStyle("A5:{$lastColStr}6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A5:{$lastColStr}6")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEEEEEE'); 

                $sheet->getStyle("B7:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A7:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("{$startDateCol}7:{$lastColStr}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- LOGIC PEWARNAAN ---
                
                $currColIndex = $this->startDateColIndex; // Kolom ke-5 (E)

                foreach ($this->allDates as $date) {
                    $colStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currColIndex);
                    
                    // [BARU] Cek Libur / Minggu
                    $isHoliday = isset($this->holidays[$date->toDateString()]);
                    $isSunday = $date->isSunday();

                    // Jika Libur/Minggu: Warnai kolom dari baris 6 (Header Tanggal) s/d Akhir Data
                    if ($isHoliday || $isSunday) {
                        $sheet->getStyle("{$colStr}6:{$colStr}{$lastRow}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFEBEB'); // Warna Merah Muda (mirip bg-red-50)
                        
                        // Khusus Header (Baris 6), beri teks merah juga agar jelas
                        $sheet->getStyle("{$colStr}6")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFF0000'));
                    }

                    // Loop pewarnaan Status (H, S, I...)
                    for ($row = 7; $row <= $lastRow; $row++) {
                        $cellValue = $sheet->getCell("{$colStr}{$row}")->getValue();
                        $color = null;
                        
                        if ($cellValue === 'S') $color = 'FFFF0000'; // Merah
                        elseif ($cellValue === 'I') $color = 'FFFFA500'; // Orange
                        elseif ($cellValue === 'C') $color = 'FF0000FF'; // Biru
                        elseif ($cellValue === 'A') $color = 'FF000000'; // Hitam (Bold)
                        elseif ($cellValue === 'L') $color = 'FF800080'; // Ungu

                        if ($color) {
                            $sheet->getStyle("{$colStr}{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($color));
                            $sheet->getStyle("{$colStr}{$row}")->getFont()->setBold(true);
                        }
                    }
                    $currColIndex++;
                }
            },
        ];
    }
}