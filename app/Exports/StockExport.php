<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::active()->orderBy('product_name')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Satuan',
            'Stok',
            'Stok PO',
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