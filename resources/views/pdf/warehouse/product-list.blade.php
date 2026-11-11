@extends('pdf.layouts.export-document')

@section('title', 'List Produk')
@section('form-title', 'LIST PRODUK')

@section('subtitle')
    Per Tanggal: {{ now()->translatedFormat('d F Y') }}
@endsection

@section('extra-style')
    table.export-table th, table.export-table td { border: 1px solid #000; }
    table.export-table th { font-size: 8pt; padding: 4px 4px; background-color: #1e3a8a; color: #ffffff; font-weight: bold; }
    table.export-table td { font-size: 8.5pt; padding: 4px 5px; }
    table.export-table td.lft { text-align: left; }
    table.export-table td.ctr { text-align: center; }
@endsection

@section('content')
<table class="export-table" width="100%">
    <thead>
        <tr>
            <th style="width:55%">Product Name</th>
            <th style="width:18%">Code</th>
            <th style="width:27%">Pack / Qty</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
            <tr>
                <td class="lft">{{ $item->product_name ?: '-' }}</td>
                <td class="ctr">{{ $item->product_code ?: '-' }}</td>
                <td class="ctr">
                    {{ \App\Support\PackagingCatalog::packagingPreview($item->unit, $item->pcs_per_unit, $item->fill_unit) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="ctr">No products found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
