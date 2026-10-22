<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Rekap seluruh hasil stock opname satu periode.
 *
 * Data dibentuk oleh controller agar isi Excel dan PDF selalu sama;
 * class ini hanya bertugas mengubah data tersebut menjadi worksheet.
 */
class OpnameRekapExport implements FromCollection, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles
{
    private const LAST_COLUMN = 'L';

    public function __construct(private readonly array $data)
    {
    }

    public function collection()
    {
        return collect($this->data['rows'] ?? []);
    }

    public function headings(): array
    {
        $summary = $this->data['summary'] ?? [];

        return [
            ['PT RAKHA NUSANTARA MEDIKA'],
            ['REKAP STOCK OPNAME'],
            ['Periode: ' . ($this->data['namaBulan'] ?? '-') . ' (' . ($this->data['bulan'] ?? '-') . ')'],
            [
                'Total: ' . ($summary['total'] ?? 0) . ' entri'
                . ' · Selesai: ' . ($summary['selesai'] ?? 0)
                . ' · Draf: ' . ($summary['draf'] ?? 0)
                . ' · Sesuai: ' . ($summary['sesuai'] ?? 0)
                . ' · Selisih: ' . ($summary['selisih'] ?? 0),
            ],
            [''],
            [
                'No',
                'Kode Barang',
                'Nama Barang',
                'Satuan',
                'Tercatat',
                'Fisik',
                'Selisih',
                'Status',
                'No. Dokumen',
                'Waktu Hitung',
                'Petugas',
                'Catatan',
            ],
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'] ?? '',
            $row['kode'] ?? '-',
            $row['produk'] ?? '-',
            $row['satuan'] ?? '-',
            $row['tercatat'] ?? '-',
            $row['terhitung'] ?? '-',
            $row['selisih'] ?? '-',
            $row['status'] ?? '-',
            $row['dok'] ?? '-',
            $row['waktu'] ?? '-',
            $row['oleh'] ?? '-',
            $row['catatan'] ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 16,
            'C' => 30,
            'D' => 9,
            'E' => 11,
            'F' => 11,
            'G' => 11,
            'H' => 10,
            'I' => 20,
            'J' => 19,
            'K' => 20,
            'L' => 30,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 13]],
            3 => ['font' => ['bold' => true, 'size' => 10]],
            4 => ['font' => ['bold' => false, 'size' => 9]],
            6 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(7, 6 + count($this->data['rows'] ?? []));

                foreach (range(1, 4) as $row) {
                    $sheet->mergeCells('A' . $row . ':' . self::LAST_COLUMN . $row);
                }

                $sheet->getStyle('A1:' . self::LAST_COLUMN . '4')
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:' . self::LAST_COLUMN . '4')
                    ->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));

                $sheet->getStyle('A6:' . self::LAST_COLUMN . '6')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF1E3A8A');
                $sheet->getStyle('A6:' . self::LAST_COLUMN . $lastRow)
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A6:' . self::LAST_COLUMN . $lastRow)
                    ->getAlignment()->setWrapText(true);

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->freezePane('A7');
                $sheet->setAutoFilter('A6:' . self::LAST_COLUMN . '6');
            },
        ];
    }
}
