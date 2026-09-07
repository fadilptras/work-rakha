<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Barang::orderBy('product_name')->get();
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

    public function map($barang): array
    {
        return [
            $barang->product_code,
            $barang->product_name,
            $barang->unit,
            $barang->stock,
            $barang->stock_po,
        ];
    }
}
