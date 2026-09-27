<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StockExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    public function collection()
    {
        return Product::active()->orderBy('product_name')->get();
    }

    public function headings(): array
    {
        return [
            ['PT RAKHA NUSANTARA MEDIKA'],
            ['DATA STOK BARANG'],
            ['Per Tanggal: ' . now()->format('d M Y')],
            [''],
            [
                'Kode Barang',
                'Nama Barang',
                'Satuan',
                'Stok',
                'Stok PO',
            ],
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
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');
                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:A3')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
                $sheet->getStyle('A5:E5')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF1E3A8A');
                $sheet->freezePane('A6');
            },
        ];
    }

    public function map($product): array
    {
        return [
            $product->product_code,
            $product->product_name,
            $product->unit,
            $product->stock,
            $product->stock_po,
        ];
    }
}