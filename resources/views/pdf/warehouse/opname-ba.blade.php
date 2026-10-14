@extends('pdf.layouts.export-document')

@section('title', 'Berita Acara Stock Opname ' . ($move->doc_no ?? $move->id))
@section('form-title', 'BERITA ACARA STOCK OPNAME')
@section('page-orientation', 'portrait')

@section('subtitle')
    No: {{ $move->doc_no ?? '-' }}
@endsection

@section('extra-style')
    table.export-table th, table.export-table td { border: 1px solid #000; }
    table.export-table th { font-size: 8pt; padding: 4px 3px; background-color: #f1f5f9; color: #1e293b; font-weight: bold; }
    table.export-table td { font-size: 8pt; padding: 3px 4px; }
    table.export-table td.num { text-align: right; }
    table.export-table td.ctr { text-align: center; }
    table.export-table td.lft { text-align: left; }
    .ba-ttd { width: 100%; border-collapse: collapse; margin-top: 18px; }
    .ba-ttd td { width: 33.33%; text-align: center; font-size: 9pt; vertical-align: top; }
    .ba-ttd .nama { margin-top: 56px; font-weight: bold; text-decoration: underline; }
@endsection

@section('meta')
<table>
    <tr>
        <td style="width:15%">Tanggal</td>
        <td style="width:2%">:</td>
        <td style="width:38%">{{ $move->created_at ? $move->created_at->translatedFormat('d F Y H:i') : '-' }}</td>
        <td style="width:15%">Dicetak oleh</td>
        <td style="width:2%">:</td>
        <td>{{ $printedBy ?? '-' }}</td>
    </tr>
    <tr>
        <td>Produk</td>
        <td>:</td>
        <td><b>{{ $move->product->product_code ?? '-' }} — {{ $move->product->product_name ?? '-' }}</b></td>
        <td>Divalidasi oleh</td>
        <td>:</td>
        <td>{{ $move->validator->name ?? $move->user->name ?? '-' }}</td>
    </tr>
</table>
@endsection

@section('content')
<table class="export-table" width="100%">
    <thead>
        <tr>
            <th style="width:34%">Tercatat Sistem</th>
            <th style="width:33%">Terhitung Fisik</th>
            <th style="width:33%">Selisih</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="num">{{ $tercatat !== null ? $tercatat : '-' }}</td>
            <td class="num">{{ $terhitung !== null ? $terhitung : '-' }}</td>
            <td class="num">{{ $selisih !== null ? ($selisih > 0 ? '+' : '') . $selisih : '-' }}</td>
        </tr>
    </tbody>
</table>

<p style="font-size: 8.5pt; margin-top: 10px;"><b>Catatan penyebab:</b> {{ $catatan ?: '-' }}</p>
<p style="font-size: 8.5pt;">Selisih di atas telah disesuaikan ke stok resmi dan menjadi saldo awal Kartu Stock berikutnya.</p>

<table class="ba-ttd">
    <tr>
        <td>Pembuat BA<div class="nama">( {{ $pembuat ?? '...........................' }} )</div></td>
        <td>Pemeriksa<div class="nama">( {{ $pemeriksa ?? '...........................' }} )</div></td>
        <td>Atasan<div class="nama">( ........................... )</div></td>
    </tr>
</table>
@endsection
