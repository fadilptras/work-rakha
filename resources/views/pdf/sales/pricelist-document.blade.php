@extends('pdf.layouts.sales-document')

@section('title', 'Daftar Harga')
@section('documentType', 'pricelist')

@section('extra-style')
    p, td, th {
        font-family: "Times New Roman", Times, serif !important;
    }
    .doc-number { display: none !important; }
    table.items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0;
    }
    table.items-table th,
    table.items-table td {
        border: 1px solid #000 !important;
        padding: 6px 8px;
    }
    table.items-table th {
        background-color: #ffffff !important;
        text-align: center;
        font-weight: bold;
        font-size: 13px !important;
    }
    table.items-table td {
        font-size: 13px !important;
    }
@endsection

@section('content')
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Produk</th>
                <th width="20%">Sediaan</th>
                <th width="18%">HNA</th>
                <th width="18%">HNA / PCS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $i => $item)
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->presentation ?: 'General' }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->base_price ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
