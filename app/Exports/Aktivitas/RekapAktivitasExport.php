<?php

namespace App\Exports\Aktivitas;

use App\Exports\Concerns\LaporanSheet;
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

class RekapAktivitasExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents
{
    use LaporanSheet;

    protected $records;
    protected $filterInfo;
    protected $startDate;
    protected $endDate;
    // Slot nomor kontrol dokumen (diisi saat user memberikan nomornya).
    protected $nomorDokumen = null;
    protected $nomor = 1;

    public function __construct($records, $filterInfo, $startDate, $endDate, $nomorDokumen = null)
    {
        $this->records = collect($records);
        $this->filterInfo = $filterInfo;
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
        $this->nomorDokumen = $nomorDokumen;
    }

    public function collection()
    {
        return $this->records;
    }

    public function map($record): array
    {
        $nama = $record->user->name ?? 'User Dihapus';
        $divisi = $record->user->divisi ?? '-';
        $tanggalWaktu = Carbon::parse($record->created_at)->format('d/m/Y H:i');

        // Strip HTML tags for Excel, just in case there are any, though it should be plain text
        $aktivitasText = strip_tags($record->keterangan ?: ($record->title ?? '-'));

        return [
            $this->nomor++,
            $tanggalWaktu,
            $nama . "\n" . $divisi,
            $aktivitasText
        ];
    }

    public function headings(): array
    {
        return [
            ['PT RAKHA NUSANTARA MEDIKA'],
            ['LAPORAN AKTIVITAS KARYAWAN'],
            ['Periode: ' . $this->startDate->format('d M Y') . ' s/d ' . $this->endDate->format('d M Y')],
            ['Filter: ' . $this->filterInfo],
            [''],
            ['No', 'Tanggal', 'Nama & Divisi', 'Aktivitas']
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 30,
            'D' => 70,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return $this->gayaKop() + [
            4 => ['font' => ['bold' => true, 'size' => 10, 'italic' => true]],
            6 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Merge & Align Header (4 baris kop: perusahaan, judul, periode, filter)
                $this->terapkanKop($sheet, 4, 4);

                // Styling Table Header (Row 6)
                $sheet->getStyle('A6:D6')->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FF1E3A8A'); // Blue-900
                $sheet->getStyle('A6:D6')->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                      ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->freezePane('A7');
                $sheet->setAutoFilter("A6:D{$lastRow}");
                $this->setupCetak($sheet, 6, 6, $this->nomorDokumen);
                $this->tulisNomorDokumen($sheet, 'D', $lastRow + 2, $this->nomorDokumen);

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
                    $sheet->getStyle("A6:D{$lastRow}")->applyFromArray($styleBorder);
                    // Center align No dan Tanggal
                    $sheet->getStyle("A7:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Wrap text untuk Nama & Divisi dan Aktivitas
                    $sheet->getStyle("C7:D{$lastRow}")->getAlignment()->setWrapText(true);

                    // Zebra striping
                    for ($row = 7; $row <= $lastRow; $row++) {
                        if ($row % 2 != 0) { // odd rows after header (7, 9, 11)
                            $sheet->getStyle("A{$row}:D{$row}")->getFill()
                                  ->setFillType(Fill::FILL_SOLID)
                                  ->getStartColor()->setARGB('FFF8FAFC'); // slate-50
                        }
                    }
                }
            },
        ];
    }
}
