@extends('pdf.layouts.export-document')

@section('title', 'Surat Jalan ' . ($doc->doc_no ?? '-'))
@section('form-title', 'SURAT JALAN')
@section('page-orientation', 'portrait')

@section('subtitle')
    No: {{ $doc->doc_no ?? '-' }}
@endsection

@section('extra-style')
    table.export-table th, table.export-table td { border: 1px solid #000; }
    table.export-table th { font-size: 8pt; padding: 4px 3px; background-color: #f1f5f9; color: #1e293b; font-weight: bold; }
    table.export-table td { font-size: 8pt; padding: 3px 4px; }
    table.export-table td.num { text-align: right; }
    table.export-table td.ctr { text-align: center; }
    table.export-table td.lft { text-align: left; }
    .sj-ttd { width: 100%; border-collapse: collapse; margin-top: 18px; }
    .sj-ttd td { width: 33.33%; text-align: center; font-size: 9pt; vertical-align: top; }
    .sj-ttd .nama { margin-top: 56px; font-weight: bold; text-decoration: underline; }
@endsection

@section('meta')
<table>
    <tr>
        <td style="width:15%">Tanggal</td>
        <td style="width:2%">:</td>
        <td style="width:38%">{{ $doc->mutation_date ? \Carbon\Carbon::parse($doc->mutation_date)->translatedFormat('d F Y') : '-' }}</td>
        <td style="width:15%">No. PO</td>
        <td style="width:2%">:</td>
        <td>{{ $doc->po_no ?? '-' }}</td>
    </tr>
    <tr>
        <td>Kepada</td>
        <td>:</td>
        <td><b>{{ $pihak }}</b></td>
        <td>Ekspedisi</td>
        <td>:</td>
        <td>{{ $doc->expedition ?? '-' }}</td>
    </tr>
    <tr>
        <td>Alamat</td>
        <td>:</td>
        <td>{{ $alamat }}</td>
        <td>Keterangan</td>
        <td>:</td>
        <td>{{ $doc->purpose ?? '-' }}</td>
    </tr>
</table>
@endsection

@section('content')
<table class="export-table" width="100%">
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:36%">Nama Barang</th>
            <th style="width:11%" class="num">Qty</th>
            <th style="width:9%">Satuan</th>
            <th style="width:13%">Batch</th>
            <th style="width:11%">Expired</th>
            <th style="width:15%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($lines as $i => $ln)
            <tr>
                <td class="ctr">{{ $i + 1 }}</td>
                <td class="lft">{{ $ln['barang'] }}</td>
                <td class="num">{{ $ln['qty'] }}</td>
                <td class="ctr">{{ $ln['unit'] }}</td>
                <td class="ctr">{{ $ln['batch_no'] }}</td>
                <td class="ctr">{{ $ln['expiry_date'] }}</td>
                <td class="lft">{{ $ln['keterangan'] ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="ctr">Tidak ada barang.</td></tr>
        @endforelse
    </tbody>
</table>

<table class="sj-ttd">
    <tr>
        <td>Dibuat oleh,</td>
        <td>Mengetahui,</td>
        <td>Penerima,</td>
    </tr>
    <tr>
        <td class="nama">{{ $dibuat }}</td>
        <td class="nama">( ........................ )</td>
        <td class="nama">( ........................ )</td>
    </tr>
</table>
@endsection
