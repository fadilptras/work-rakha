@php
    $nama = $product->product_name_clean ?: $product->product_name;
    $satuan = $product->unit ?: ($product->fill_unit ?: 'Pcs');
    $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') ?: '0';
@endphp
@extends('pdf.layouts.export-document')

@section('title', 'Kartu Stock - ' . $product->product_code)
@section('form-title', 'KARTU STOCK')

@section('subtitle')
    Periode {{ \Carbon\Carbon::parse($from)->format('d M Y') }} s.d. {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
@endsection

@section('extra-style')
    table.info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
    table.info-table > tbody > tr > td { border: 1px solid #000; padding: 5px 7px; font-size: 8.5pt; vertical-align: top; }
    table.info-inner { width: 100%; border-collapse: collapse; }
    table.info-inner td { border: none; padding: 1.5px 2px; font-size: 8.5pt; }
    table.export-table th, table.export-table td { border: 1px solid #000; }
    table.export-table th { font-size: 7pt; padding: 3px 2px; background-color: #f1f5f9; color: #1e293b; font-weight: bold; }
    table.export-table td { font-size: 7pt; padding: 2px 3px; }
    table.export-table td:nth-child(2) { text-align: center; }
    table.export-table td:nth-child(4) { text-align: left; }
    table.export-table td.num { text-align: center; }
    .form-number { text-align: right; font-size: 8.5pt; color: #334155; margin: 8px 0 0; }
    .export-footer { display: none; }
@endsection

@section('content')
<table class="info-table">
    <tbody>
        <tr>
            <td style="width: 76%;">
                <table class="info-inner">
                    <tr>
                        <td style="width: 38%; font-weight: bold;">Kode Barang / Tipe Barang</td>
                        <td style="width: 3%;">:</td>
                        <td>{{ $product->product_code }} / {{ $tipeBarang ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Nama Barang</td>
                        <td>:</td>
                        <td>{{ $nama }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Nomor NIE</td>
                        <td>:</td>
                        <td>{{ $noAkdAkl ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 24%;">
                <table class="info-inner">
                    <tr>
                        <td style="font-weight: bold;">No. Kartu</td>
                        <td style="width: 5%;">:</td>
                        <td>{{ $noKartu }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Stok Awal</td>
                        <td>:</td>
                        <td>{{ $fmt($saldoAwal) }} {{ $satuan }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Stok Kini</td>
                        <td>:</td>
                        <td>{{ $fmt($product->stock) }} {{ $satuan }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>

<table class="export-table">
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 13%;">Tanggal</th>
            <th style="width: 14%;">Batch No./SN</th>
            <th style="width: 33%;">Keterangan</th>
            <th style="width: 12%;">Masuk</th>
            <th style="width: 12%;">Keluar</th>
            <th style="width: 12%;">Sisa</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td style="font-weight: bold;">{{ $saldoLabel }}</td>
            <td class="num">-</td>
            <td class="num">-</td>
            <td class="num" style="font-weight: bold;">{{ $fmt($saldoAwal) }}</td>
        </tr>
        @forelse($rows as $i => $r)
            @php $m = $r['move']; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y') }}</td>
                <td>{{ $m->batch_no ?? '-' }}</td>
                <td>{{ $m->reason ?: ($m->ref_type ? $m->ref_type . ($m->ref_id ? ' #' . $m->ref_id : '') : '-') }}</td>
                <td class="num">{{ $r['masuk'] > 0 ? $fmt($r['masuk']) : '-' }}</td>
                <td class="num">{{ $r['keluar'] > 0 ? $fmt($r['keluar']) : '-' }}</td>
                <td class="num" style="font-weight: bold;">{{ $fmt($r['sisa']) }}</td>
            </tr>
        @empty
            <tr><td colspan="7">Belum ada mutasi pada periode ini.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="form-number"><strong>FORM-GD-03-001, Rev. 2</strong></div>
@if(!empty($forPrint))
<script>
    window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 500); });
</script>
@endif
@endsection
