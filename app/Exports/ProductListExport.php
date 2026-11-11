<?php

namespace App\Exports;

use App\Models\Product;
use App\Support\PackagingCatalog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProductListExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithTitle, WithEvents
{
    public function collection()
    {
        return Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name), ""), product_code))')
            ->get();
    }

    public function headings(): array
    {
        return [
            ['PT RAKHA NUSANTARA MEDIKA'],
            ['LIST PRODUK'],
            ['Per Tanggal: ' . now()->translatedFormat('d F Y')],
            [''],
            ['Product Name', 'Code', 'Pack / Qty'],
        ];
    }

    public function map($product): array
    {
        return [
            $product->product_name ?: '-',
            $product->product_code ?: '-',
            PackagingCatalog::packagingPreview(
                $product->unit,
                $product->pcs_per_unit,
                $product->fill_unit
            ),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 55,
            'B' => 20,
            'C' => 28,
        ];
    }

    public function title(): string
    {
        return 'List Produk';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:C1');
                $sheet->mergeCells('A2:C2');
                $sheet->mergeCells('A3:C3');
                $sheet->getStyle('A1:C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A3')->getFont()->setSize(10);

                $sheet->getStyle('A5:C5')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A5:C5')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF1E3A8A');

                $sheet->getStyle('A6:C' . max(6, $sheet->getHighestRow()))
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->freezePane('A6');
            },
        ];
    }
}
