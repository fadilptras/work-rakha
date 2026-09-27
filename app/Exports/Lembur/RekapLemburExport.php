<?php

namespace App\Exports\Lembur;

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
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapLemburExport implements WithMultipleSheets
{
    public const NOMOR_DOKUMEN = 'FORM-HR-04-003';

    protected $rekapData;
    protected $allDates;
    protected $month;
    protected $year;
    protected $holidays;

    public function __construct($rekapData, $allDates, $month, $year, $holidays)
    {
        $this->rekapData = collect($rekapData);
        $this->allDates = $allDates;
        $this->month = $month;
        $this->year = $year;
        $this->holidays = $holidays;
    }

    public function sheets(): array
    {
        $sheets = [
            new RekapLemburBulananSheet($this->rekapData, $this->month, $this->year),
        ];

        $no = 0;
        foreach (AttendanceService::monthWeeks($this->allDates) as $week) {
            $no++;
            $sheets[] = new RekapLemburMingguanSheet($this->rekapData, $week, $no, $this->holidays);
        }

        return $sheets;
    }
}

class RekapLemburBulananSheet implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents, WithTitle
{
    use LaporanSheet;

    protected $rekapData;
    protected $month;
    protected $year;

    public function __construct($rekapData, $month, $year)
    {
        $this->rekapData = collect($rekapData);
        $this->month = $month;
        $this->year = $year;
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

        return [
            $no++,
            $row['user']->name ?? '-',
            $row['user']->divisi ?? '-',
            $row['summary']['total_hari'] ?? 0,
            $row['summary']['total_formatted'] ?? '-',
        ];
    }

    public function headings(): array
    {
        $bulan = Carbon::create($this->year, $this->month, 1);

        return array_merge(
            $this->barisKop(
                'FORM REKAP LEMBUR - ' . $bulan->isoFormat('MMMM YYYY'),
                'Periode: ' . $bulan->copy()->startOfMonth()->format('d M Y')
                    . ' s/d ' . $bulan->copy()->endOfMonth()->format('d M Y')
            ),
            [['No', 'Nama Karyawan', 'Divisi', 'Hari', 'Total Jam']]
        );
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 30, 'C' => 20, 'D' => 8, 'E' => 22];
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

                $this->terapkanKop($sheet, 5);
                $sheet->getStyle('A5:E5')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
                $sheet->getStyle('A5:E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $this->borderData($sheet, "A5:E{$lastRow}");
                $sheet->getStyle("B6:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D6:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E6:E{$lastRow}")->getAlignment()->setWrapText(true);
                $this->zebra($sheet, 6, $lastRow, 'E');

                $sheet->freezePane('C6');
                $sheet->setAutoFilter("A5:E{$lastRow}");
                $sheet->getStyle('D6:D' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');

                // Baris TOTAL
                $totalRow = $lastRow + 1;
                $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->getStyle("A{$totalRow}:C{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->setCellValue("D{$totalRow}", "=SUM(D6:D{$lastRow})");
                $menit = (int) $this->rekapData->sum('summary.total_menit');
                $sheet->setCellValue("E{$totalRow}", floor($menit / 60) . ' Jam ' . ($menit % 60) . ' Menit');
                $sheet->getStyle("A{$totalRow}:E{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$totalRow}:E{$totalRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFF6FF');
                $sheet->getStyle("D{$totalRow}:E{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
                $this->borderData($sheet, "A{$totalRow}:E{$totalRow}");

                $this->tulisNomorDokumen($sheet, 'E', $totalRow + 2, RekapLemburExport::NOMOR_DOKUMEN);
                $this->setupCetak($sheet, 5, 5, RekapLemburExport::NOMOR_DOKUMEN);
            },
        ];
    }
}

class RekapLemburMingguanSheet implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents, WithTitle
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
        return 4 + count($this->dates) + 2;
    }

    public function map($row): array
    {
        static $no = 1;

        $cells = [];
        $hari = 0;
        $menit = 0;
        foreach ($this->dates as $date) {
            $cell = $row['daily'][$date->toDateString()] ?? null;
            if (!$cell) {
                $cells[] = '-';
                continue;
            }
            $hari++;
            $menit += $cell['menit'];
            $status = $cell['status'] ?? '';
            // Jam tebal ungu, durasi abu normal, bermasalah tebal + warna per jenis.
            $baris = [
                ['teks' => $cell['label'], 'bold' => true, 'color' => 'FF6B21A8', 'size' => 11],
            ];
            if ($status === 'ok') {
                $baris[] = ['teks' => $cell['durasi_full'] ?? $cell['durasi'], 'color' => 'FF475569', 'size' => 9];
            } elseif ($status === 'incomplete') {
                $baris[] = ['teks' => $cell['durasi_full'] ?? $cell['durasi'], 'bold' => true, 'color' => 'FF92400E', 'size' => 10];
            } else {
                $baris[] = ['teks' => $cell['durasi_full'] ?? $cell['durasi'], 'bold' => true, 'color' => 'FFB91C1C', 'size' => 10];
            }
            $cells[] = $this->richTeks($baris);
        }

        $total = $hari > 0
            ? $hari . ' hari, ' . floor($menit / 60) . ' Jam ' . ($menit % 60) . ' Menit'
            : '-';

        return array_merge(
            [$no++, $row['user']->name ?? '-', $row['user']->divisi ?? '-', $row['user']->jabatan ?? '-'],
            $cells,
            [$hari, $total]
        );
    }

    public function headings(): array
    {
        $awal = $this->dates[0];
        $akhir = $this->dates[count($this->dates) - 1];

        $header = ['No', 'Nama Karyawan', 'Divisi', 'Jabatan'];
        foreach ($this->dates as $date) {
            $header[] = $date->day;
        }
        $header = array_merge($header, ['Hari', 'Total Jam']);

        return array_merge(
            $this->barisKop(
                'FORM REKAP LEMBUR - ' . $this->label,
                'Periode: ' . $awal->format('d M Y') . ' s/d ' . $akhir->format('d M Y')
            ),
            [$header]
        );
    }

    public function columnWidths(): array
    {
        $columns = ['A' => 5, 'B' => 28, 'C' => 18, 'D' => 18];
        $idx = 5;
        foreach ($this->dates as $date) {
            $columns[Coordinate::stringFromColumnIndex($idx)] = 18;
            $idx++;
        }
        $columns[Coordinate::stringFromColumnIndex($idx)] = 8;
        $idx++;
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
                $sheet->getStyle("B6:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E6:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $this->zebra($sheet, 6, $lastRow, $lastCol);

                $sheet->freezePane('E6');
                $sheet->setAutoFilter("A5:{$lastCol}{$lastRow}");

                // Warna akhir pekan/libur + status lembur
                $idx = 5;
                foreach ($this->dates as $date) {
                    $col = Coordinate::stringFromColumnIndex($idx);
                    $isLibur = isset($this->holidays[$date->toDateString()]) || $date->isSunday();
                    if ($isLibur) {
                        $sheet->getStyle("{$col}6:{$col}{$lastRow}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFEBEB');
                    }
                    // Isi sel sudah RichText (font per baris), di sini hanya background.
                    for ($row = 6; $row <= $lastRow; $row++) {
                        $teks = (string) $sheet->getCell("{$col}{$row}")->getValue();
                        if ($teks === '' || $teks === '-') {
                            continue;
                        }
                        if (str_contains($teks, 'Tidak Absen Keluar')) {
                            $bg = 'FFFFF3CD';
                        } elseif (str_contains($teks, 'Cek jam keluar') || str_contains($teks, 'Jam tidak valid')) {
                            $bg = 'FFF8D7DA';
                        } else {
                            $bg = 'FFF3E8FF';
                        }
                        $sheet->getStyle("{$col}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bg);
                    }
                    $idx++;
                }

                $this->tulisNomorDokumen($sheet, $lastCol, $lastRow + 2, RekapLemburExport::NOMOR_DOKUMEN);
                $this->setupCetak($sheet, 5, 5, RekapLemburExport::NOMOR_DOKUMEN);
            },
        ];
    }
}
