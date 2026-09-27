<?php

namespace App\Exports;

use App\Models\Barang;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BarangMasterExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $productMap;

    public function __construct()
    {
        $this->productMap = Product::active()->get()->keyBy('product_code');
    }

    public function collection()
    {
        return Barang::orderBy('product_name')->get();
    }

    public function headings(): array
    {
        return [
            ['PT RAKHA NUSANTARA MEDIKA'],
            ['DATA MASTER BARANG'],
            ['Per Tanggal: ' . now()->format('d M Y')],
            [''],
            [
                'Item Code',
                'Clean Name',
                'Packaging',
                'Qty per Pack',
                'Content Unit',
                'Stock',
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
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:A3')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
                $sheet->getStyle('A5:F5')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF1E3A8A');
                $sheet->freezePane('A6');
            },
        ];
    }

    public function map($barang): array
    {
        $product = $this->productMap[$barang->product_code] ?? null;

        return [
            $barang->product_code,
            ($product->product_name_clean ?? null) ?: $barang->product_name,
            $barang->unit,
            (int) ($product->pcs_per_unit ?? 0),
            ($product->fill_unit ?? 'Pcs') ?: 'Pcs',
            $product->stock ?? $barang->stock ?? 0,
        ];
    }
}
