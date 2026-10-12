@php
    $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') ?: '0';
    $filterText = collect($filter ?? [])->filter()->map(fn ($v, $k) => $k . '=' . $v)->implode('; ');
@endphp
@extends('pdf.layouts.export-document')

@section('title', 'Rekap Mutasi Barang')
@section('form-title', 'REKAP MUTASI BARANG')

@section('subtitle')
    {{ $filterText !== '' ? 'Filter: ' . $filterText : 'Seluruh data' }} · Dicetak {{ date('d M Y H:i') }} oleh {{ $printedBy ?? '-' }}
@endsection

@section('extra-style')
    table.export-table th, table.export-table td { border: 1px solid #000; }
    table.export-table th { font-size: 7pt; padding: 3px 2px; background-color: #f1f5f9; color: #1e293b; font-weight: bold; }
    table.export-table td { font-size: 7pt; padding: 2px 3px; }
    table.export-table td.num { text-align: right; }
    table.export-table td.ctr { text-align: center; }
@endsection

@section('content')
<table class="export-table" width="100%">
    <thead>
        <tr>
            <th style="width:3%">No</th>
            <th style="width:8%">Tanggal</th>
            <th style="width:11%">No. SJ</th>
            <th style="width:6%">Arah</th>
            <th style="width:22%">Barang</th>
            <th style="width:8%">Batch</th>
            <th style="width:8%" class="num">Masuk</th>
            <th style="width:8%" class="num">Keluar</th>
            <th style="width:26%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $i => $m)
            @php
                $masuk = $isMasuk($m);
                $nama = $m->product->product_name_clean ?? $m->product->product_name ?? '-';
            @endphp
            <tr>
                <td class="ctr">{{ $i + 1 }}</td>
                <td>{{ $m->created_at ? \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $m->doc_no ?? '-' }}</td>
                <td class="ctr">{{ $masuk ? 'Masuk' : 'Keluar' }}</td>
                <td>{{ $nama }}</td>
                <td class="ctr">{{ $m->batch_no ?? '-' }}</td>
                <td class="num">{{ $masuk ? $fmt($m->qty) : '-' }}</td>
                <td class="num">{{ $masuk ? '-' : $fmt($m->qty) }}</td>
                <td>{{ $m->reason ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="ctr">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
