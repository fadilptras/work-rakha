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

class AktivitasExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents
{
    protected $records;
    protected $filterInfo;
    protected $startDate;
    protected $endDate;

    public function __construct($records, $filterInfo, $startDate, $endDate)
    {
        $this->records = collect($records);
        $this->filterInfo = $filterInfo;
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
    }

    public function collection()
    {
        return $this->records;
    }

    public function map($record): array
    {
        static $no = 1;

        $nama = $record->user->name ?? 'User Dihapus';
        $divisi = $record->user->divisi ?? '-';
        $tanggalWaktu = Carbon::parse($record->created_at)->format('d/m/Y H:i');
        
        // Strip HTML tags for Excel, just in case there are any, though it should be plain text
        $aktivitasText = strip_tags($record->aktivitas);

        return [
            $no++,
            $tanggalWaktu,
            $nama,
            $divisi,
            $aktivitasText
        ];
    }

    public function headings(): array
    {
        $companyName = "PT RAKHA NUSANTARA MEDIKA";
        $title = "LAPORAN AKTIVITAS KARYAWAN";
        $period = "Periode: " . $this->startDate->format('d M Y') . " s/d " . $this->endDate->format('d M Y');
        $filter = "Filter: " . $this->filterInfo;

        return [
            [$companyName],
            [$title],
            [$period],
            [$filter],
            [''],
            ['No', 'Tanggal & Waktu', 'Nama Karyawan', 'Divisi', 'Deskripsi Aktivitas']
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 30,
            'D' => 20,
            'E' => 60,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true, 'size' => 10]],
            4 => ['font' => ['bold' => true, 'size' => 10, 'italic' => true]],
            6 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Merge & Align Header
                $sheet->mergeCells("A1:E1");
                $sheet->mergeCells("A2:E2");
                $sheet->mergeCells("A3:E3");
                $sheet->mergeCells("A4:E4");
                $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Styling Table Header (Row 6)
                $sheet->getStyle('A6:E6')->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FF1E3A8A'); // Blue-900
                $sheet->getStyle('A6:E6')->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                      ->setVertical(Alignment::VERTICAL_CENTER);

                // Border untuk Data
                $styleBorder = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
                ];
                
                if ($lastRow >= 6) {
                    $sheet->getStyle("A6:E{$lastRow}")->applyFromArray($styleBorder);
                    // Center align No, Tanggal, Divisi
                    $sheet->getStyle("A7:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D7:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Wrap text for description
                    $sheet->getStyle("E7:E{$lastRow}")->getAlignment()->setWrapText(true);
                    
                    // Zebra striping
                    for ($row = 7; $row <= $lastRow; $row++) {
                        if ($row % 2 != 0) { // odd rows after header (7, 9, 11)
                            $sheet->getStyle("A{$row}:E{$row}")->getFill()
                                  ->setFillType(Fill::FILL_SOLID)
                                  ->getStartColor()->setARGB('FFF8FAFC'); // slate-50
                        }
                    }
                }
            },
        ];
    }
}
