<?php

namespace App\Exports;

use App\Models\Barang;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BarangMasterExport implements FromCollection, WithHeadings, WithMapping
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
            'Item Code',
            'Clean Name',
            'Packaging',
            'Qty per Pack',
            'Content Unit',
            'Stock',
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
