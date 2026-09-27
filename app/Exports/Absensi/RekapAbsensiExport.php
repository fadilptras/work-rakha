<?php

namespace App\Exports\Absensi;

use App\Exports\Concerns\LaporanSheet;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapAbsensiExport implements WithMultipleSheets
{
    public const NOMOR_DOKUMEN = 'FORM-HR-04-002/Rev. 01';

    protected $rekapData;
    protected $allDates;
    protected $startDate;
    protected $endDate;
    protected $holidays;

    public function __construct($rekapData, $allDates, $startDate, $endDate, $holidays)
    {
        $this->rekapData = collect($rekapData);
        $this->allDates = $allDates;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->holidays = $holidays;
    }

    public function sheets(): array
    {
        $sheets = [
            new RekapAbsensiBulananSheet($this->rekapData, $this->startDate, $this->endDate),
        ];

        $no = 0;
        foreach (AttendanceService::monthWeeks($this->allDates) as $week) {
            $no++;
            $sheets[] = new RekapAbsensiMingguanSheet($this->rekapData, $week, $no, $this->holidays);
        }

        return $sheets;
    }
}

class RekapAbsensiBulananSheet implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents, WithTitle
{
    use LaporanSheet;

    protected $rekapData;
    protected $startDate;
    protected $endDate;

    public function __construct($rekapData, $startDate, $endDate)
    {
        $this->rekapData = collect($rekapData);
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Rekap Sebulan';
    }

    public function collection()
    {
        return $this->rekapData;
    }

    public function map($row): array
    {
        static $no = 1;

        $terlambat = $row['summary']['terlambat_formatted'] ?? '-';
        if ($terlambat === '0 Jam 0 Menit') {
            $terlambat = '-';
        }

        return [
            $no++,
            $row['user']->name ?? '-',
            $row['user']->divisi ?? '-',
            $row['user']->jabatan ?? '-',
            $this->formatZero($row['summary']['H'] ?? 0),
            $this->formatZero($row['summary']['S'] ?? 0),
            $this->formatZero($row['summary']['I'] ?? 0),
            $this->formatZero($row['summary']['C'] ?? 0),
            $this->formatZero($row['summary']['A'] ?? 0),
            $terlambat,
            (int) ($row['summary']['terlambat_count'] ?? 0),
        ];
    }

    private function formatZero($value)
    {
        return ($value === 0 || $value === '0') ? '0' : $value;
    }

    public function headings(): array
    {
        return array_merge(
            $this->barisKop(
                'FORM REKAP ABSEN - ' . Carbon::parse($this->startDate)->isoFormat('MMMM YYYY'),
                'Periode: ' . Carbon::parse($this->startDate)->format('d M Y') . ' s/d ' . Carbon::parse($this->endDate)->format('d M Y')
            ),
            [['No', 'Nama Karyawan', 'Divisi', 'Jabatan', 'H', 'S', 'I', 'C', 'A', 'Terlambat', 'Kali']]
        );
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 30, 'C' => 20, 'D' => 20,
            'E' => 6, 'F' => 6, 'G' => 6, 'H' => 6, 'I' => 6,
            'J' => 22, 'K' => 8,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return $this->gayaKop() + [
            5 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $this->terapkanKop($sheet, 11);
                $sheet->getStyle('A5:K5')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
                $sheet->getStyle('A5:K5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $this->borderData($sheet, "A5:K{$lastRow}");
                $sheet->getStyle("B6:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E6:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J6:J{$lastRow}")->getAlignment()->setWrapText(true);
                $this->zebra($sheet, 6, $lastRow, 'K');

                $sheet->freezePane('C6');
                $sheet->setAutoFilter("A5:K{$lastRow}");

                // Baris TOTAL (rumus hidup untuk H..A dan Kali, teks untuk Terlambat)
                $totalRow = $lastRow + 1;
                $sheet->mergeCells("A{$totalRow}:D{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->getStyle("A{$totalRow}:D{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                foreach (['E', 'F', 'G', 'H', 'I', 'K'] as $col) {
                    $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}6:{$col}{$lastRow})");
                }
                $menit = (int) $this->rekapData->sum('summary.terlambat');
                $sheet->setCellValue("J{$totalRow}", $menit > 0
                    ? floor($menit / 60) . ' Jam ' . ($menit % 60) . ' Menit'
                    : '-');
                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFF6FF');
                $sheet->getStyle("E{$totalRow}:K{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $this->borderData($sheet, "A{$totalRow}:K{$totalRow}");

                foreach (['E', 'F', 'G', 'H', 'I', 'K'] as $col) {
                    $sheet->getStyle("{$col}6:{$col}{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
                }

                $this->tulisNomorDokumen($sheet, 'K', $totalRow + 2, RekapAbsensiExport::NOMOR_DOKUMEN);
                $this->setupCetak($sheet, 5, 5, RekapAbsensiExport::NOMOR_DOKUMEN);
            },
        ];
    }
}

class RekapAbsensiMingguanSheet implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents, WithTitle
{
    use LaporanSheet;

    protected $rekapData;
    protected $dates;
    protected $label;
    protected $nomor;
    protected $holidays;

    public function __construct($rekapData, array $week, int $nomor, $holidays)
    {
        $this->rekapData = collect($rekapData);
        $this->dates = $week['dates'];
        $this->label = $week['label'];
        $this->nomor = $nomor;
        $this->holidays = $holidays ?? [];
    }

    public function title(): string
    {
        return 'Minggu ' . $this->nomor;
    }

    public function collection()
    {
        return $this->rekapData;
    }

    private function totalKolom(): int
    {
        return 3 + count($this->dates) + 1;
    }

    public function map($row): array
    {
        static $no = 1;

        $warnaStatus = [
            'H' => 'FF0F5132', 'S' => 'FF842029', 'I' => 'FF664D03',
            'C' => 'FF084298', 'A' => 'FF41464B', '-' => 'FF9CA3AF',
        ];

        $cells = [];
        $lateMenit = 0;
        $lateCount = 0;
        foreach ($this->dates as $date) {
            $key = $date->toDateString();
            $status = $row['daily'][$key] ?? '-';
            if ($status === '') {
                $status = '-';
            }

            // Tiap baris sel punya gaya sendiri agar Telat tidak nyaru dengan jam.
            $baris = [
                ['teks' => $status, 'bold' => true, 'color' => $warnaStatus[$status] ?? 'FF374151', 'size' => 12],
            ];

            $time = $row['time'][$key] ?? null;
            if (!empty($time) && ($time['masuk'] || $time['keluar'])) {
                $baris[] = [
                    'teks' => str_replace(':', '.', $time['masuk'] ?? '?') . ' - ' . str_replace(':', '.', $time['keluar'] ?? '?'),
                    'color' => 'FF475569',
                    'size' => 9,
                ];
            }

            $late = $row['late'][$key] ?? null;
            if ($late) {
                $baris[] = ['teks' => 'Telat ' . $late['short'], 'bold' => true, 'color' => 'FFB91C1C', 'size' => 10];
                $lateMenit += $late['menit'];
                $lateCount++;
            }

            $cells[] = $this->richTeks($baris);
        }

        $telat = '-';
        if ($lateCount > 0) {
            $telat = floor($lateMenit / 60) . ' Jam ' . ($lateMenit % 60) . ' Menit' . " ({$lateCount}x)";
        }

        return array_merge(
            [$no++, $row['user']->name ?? '-', $row['user']->divisi ?? '-'],
            $cells,
            [$telat]
        );
    }

    public function headings(): array
    {
        $awal = $this->dates[0];
        $akhir = $this->dates[count($this->dates) - 1];

        $header = ['No', 'Nama Karyawan', 'Divisi'];
        foreach ($this->dates as $date) {
            $header[] = $date->day;
        }
        $header[] = 'Telat';

        return array_merge(
            $this->barisKop(
                'FORM REKAP ABSEN - ' . $this->label,
                'Periode: ' . $awal->format('d M Y') . ' s/d ' . $akhir->format('d M Y')
            ),
            [$header]
        );
    }

    public function columnWidths(): array
    {
        $columns = ['A' => 5, 'B' => 28, 'C' => 18];
        $idx = 4;
        foreach ($this->dates as $date) {
            $columns[Coordinate::stringFromColumnIndex($idx)] = 16;
            $idx++;
        }
        $columns[Coordinate::stringFromColumnIndex($idx)] = 22;

        return $columns;
    }

    public function styles(Worksheet $sheet)
    {
        return $this->gayaKop() + [
            5 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = Coordinate::stringFromColumnIndex($this->totalKolom());

                $this->terapkanKop($sheet, $this->totalKolom());
                $sheet->getStyle("A5:{$lastCol}5")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
                $sheet->getStyle("A5:{$lastCol}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $this->borderData($sheet, "A5:{$lastCol}{$lastRow}");
                $sheet->getStyle("B6:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D6:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $this->zebra($sheet, 6, $lastRow, $lastCol);

                $sheet->freezePane('D6');
                $sheet->setAutoFilter("A5:{$lastCol}{$lastRow}");

                // Warna akhir pekan/libur + status
                $idx = 4;
                foreach ($this->dates as $date) {
                    $col = Coordinate::stringFromColumnIndex($idx);
                    $isLibur = isset($this->holidays[$date->toDateString()]) || $date->isSunday();
                    if ($isLibur) {
                        $sheet->getStyle("{$col}6:{$col}{$lastRow}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFEBEB');
                    }
                    // Isi sel sudah RichText (font per baris), di sini hanya background.
                    for ($row = 6; $row <= $lastRow; $row++) {
                        $base = explode("\n", (string) $sheet->getCell("{$col}{$row}")->getValue())[0];
                        $bg = match ($base) {
                            'H' => 'FFD1E7DD',
                            'S' => 'FFF8D7DA',
                            'I' => 'FFFFF3CD',
                            'C' => 'FFCFE2FF',
                            'A' => 'FFE2E3E5',
                            default => null,
                        };
                        if ($bg) {
                            $sheet->getStyle("{$col}{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bg);
                        }
                    }
                    $idx++;
                }

                $this->tulisNomorDokumen($sheet, $lastCol, $lastRow + 2, RekapAbsensiExport::NOMOR_DOKUMEN);
                $this->setupCetak($sheet, 5, 5, RekapAbsensiExport::NOMOR_DOKUMEN);
            },
        ];
    }
}
