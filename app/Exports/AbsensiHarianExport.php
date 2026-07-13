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

class AbsensiHarianExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents
{
    protected $records;
    protected $dateForPage;

    public function __construct($records, $dateForPage)
    {
        $this->records = collect($records);
        $this->dateForPage = Carbon::parse($dateForPage);
    }

    public function collection()
    {
        return $this->records;
    }

    public function map($record): array
    {
        static $no = 1;

        $isLembur = ($record->record_type === 'lembur');
        $nama = $record->user->name ?? 'User Dihapus';
        $divisi = $record->user->divisi ?? '-';

        $waktuMasuk = '-';
        $waktuKeluar = '-';
        $durasi = '-';

        if ($isLembur) {
            $status = 'Lembur';
            $jamMasuk = $record->jam_masuk_lembur ? Carbon::parse($record->jam_masuk_lembur) : null;
            $jamKeluar = $record->jam_keluar_lembur ? Carbon::parse($record->jam_keluar_lembur) : null;
            
            if ($jamMasuk) $waktuMasuk = $jamMasuk->format('H:i') . ' WIB';
            if ($jamKeluar) $waktuKeluar = $jamKeluar->format('H:i') . ' WIB';
            
            if ($jamMasuk && $jamKeluar) {
                $totalMenit = $jamMasuk->diffInMinutes($jamKeluar);
                $durasi = floor($totalMenit / 60) . ' Jam ' . ($totalMenit % 60) . ' Menit';
            }
        } else {
            $jamMasuk = $record->jam_masuk ? Carbon::parse($record->jam_masuk) : null;
            $jamKeluar = $record->jam_keluar ? Carbon::parse($record->jam_keluar) : null;
            
            if ($jamMasuk) $waktuMasuk = $jamMasuk->format('H:i') . ' WIB';
            if ($jamKeluar) $waktuKeluar = $jamKeluar->format('H:i') . ' WIB';
            $durasi = $record->durasi_teks ?? '-';

            if (strtolower($record->status) == 'hadir') {
                $batasWaktuMasuk = Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                $waktuMasukKaryawan = $jamMasuk ? Carbon::parse($jamMasuk, 'Asia/Jakarta') : null;
                $isLate = $waktuMasukKaryawan && $waktuMasukKaryawan->gt($batasWaktuMasuk);
                $status = $isLate ? 'Hadir (Terlambat)' : 'Hadir';
            } else {
                $status = ucfirst($record->status);
            }
        }

        return [
            $no++,
            $nama,
            $divisi,
            $waktuMasuk,
            $waktuKeluar,
            $durasi,
            $status,
            $record->keterangan ?? '-'
        ];
    }

    public function headings(): array
    {
        $companyName = "PT RAKHA NUSANTARA MEDIKA";
        $title = "DATA ABSENSI DAN LEMBUR HARIAN";
        $period = "Tanggal: " . $this->dateForPage->isoFormat('dddd, D MMMM YYYY');

        return [
            [$companyName],
            [$title],
            [$period],
            [''],
            ['No', 'Nama Karyawan', 'Divisi', 'Waktu Masuk', 'Waktu Keluar', 'Durasi', 'Status', 'Keterangan']
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 18,
            'G' => 20,
            'H' => 40,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true, 'size' => 10]],
            5 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Merge & Align Header
                $sheet->mergeCells("A1:H1");
                $sheet->mergeCells("A2:H2");
                $sheet->mergeCells("A3:H3");
                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Styling Table Header (Row 5)
                $sheet->getStyle('A5:H5')->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FF1E3A8A'); // Blue-900
                $sheet->getStyle('A5:H5')->getAlignment()
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
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ];
                
                if ($lastRow >= 5) {
                    $sheet->getStyle("A5:H{$lastRow}")->applyFromArray($styleBorder);
                    // Center align No, Waktu Masuk, Waktu Keluar, Durasi, Status
                    $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D6:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Zebra striping
                    for ($row = 6; $row <= $lastRow; $row++) {
                        if ($row % 2 == 0) {
                            $sheet->getStyle("A{$row}:H{$row}")->getFill()
                                  ->setFillType(Fill::FILL_SOLID)
                                  ->getStartColor()->setARGB('FFF8FAFC'); // slate-50
                        }
                        
                        // Status color formatting
                        $statusVal = $sheet->getCell("G{$row}")->getValue();
                        if (str_contains($statusVal, 'Terlambat')) {
                            $sheet->getStyle("G{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFD97706')); // amber
                        } elseif ($statusVal === 'Hadir') {
                            $sheet->getStyle("G{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF166534')); // green
                        } elseif ($statusVal === 'Sakit') {
                            $sheet->getStyle("G{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFDC2626')); // red
                        } elseif ($statusVal === 'Izin') {
                            $sheet->getStyle("G{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFD97706')); // orange
                        } elseif ($statusVal === 'Cuti') {
                            $sheet->getStyle("G{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF2563EB')); // blue
                        } elseif ($statusVal === 'Lembur') {
                            $sheet->getStyle("G{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF7C3AED')); // purple
                        }
                    }
                }
            },
        ];
    }
}
