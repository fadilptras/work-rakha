@php
    $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') ?: '0';
    $filterText = collect($filter ?? [])->filter()->map(fn ($v, $k) => $k . '=' . $v)->implode('; ');
    $totalMasuk = 0;
    $totalKeluar = 0;
    $bykMasuk = 0;
    $bykKeluar = 0;
    foreach ($rows as $r) {
        if ($isMasuk($r)) {
            $totalMasuk += (float) $r->qty;
            $bykMasuk++;
        } else {
            $totalKeluar += (float) $r->qty;
            $bykKeluar++;
        }
    }
    $pihakOf = function ($m) {
        $p = $m->cu_name ?: ($m->su_name ?: ($m->partner ?: null));
        if ($p) {
            return $p;
        }
        return ($m->purpose && strcasecmp((string) $m->purpose, 'Internal') === 0) ? 'Pemakaian Internal' : '-';
    };
@endphp
@extends('pdf.layouts.export-document')

@section('title', 'Rekap Mutasi Barang')
@section('page-orientation', 'landscape')
@section('form-title', 'REKAP MUTASI BARANG')

@section('subtitle')
    {{ $filterText !== '' ? 'Filter: ' . $filterText : 'Seluruh data' }}
@endsection

@section('meta')
    <table>
        <tr>
            <td><b>Total Baris:</b> {{ $rows->count() }}</td>
            <td><b>Masuk:</b> {{ $bykMasuk }} trx / {{ $fmt($totalMasuk) }}</td>
            <td><b>Keluar:</b> {{ $bykKeluar }} trx / {{ $fmt($totalKeluar) }}</td>
            <td><b>Dicetak oleh:</b> {{ $printedBy ?? '-' }}</td>
        </tr>
    </table>
@endsection

@section('extra-style')
    table.export-table th { font-size: 7.5pt; padding: 4px 3px; background-color: #1e293b; color: #fff; }
    table.export-table td { font-size: 7.5pt; padding: 3px 3px; }
    table.export-table td.num { text-align: right; }
    table.export-table td.ctr { text-align: center; }
    table.export-table td.lft { text-align: left; }
    table.export-table td.exp { color: #b91c1c; font-weight: bold; }
    table.export-table .sub { color: #64748b; font-size: 6.5pt; }
@endsection

@section('content')
<table class="export-table" width="100%">
    <thead>
        <tr>
            <th style="width:3%">No</th>
            <th style="width:9%">Tanggal</th>
            <th style="width:10%">Dokumen</th>
            <th style="width:17%">Customer / Supplier</th>
            <th style="width:21%">Barang</th>
            <th style="width:11%">Batch / ED</th>
            <th style="width:6%" class="num">Masuk</th>
            <th style="width:6%" class="num">Keluar</th>
            <th style="width:8%">Petugas</th>
            <th style="width:9%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $i => $m)
            @php
                $masuk = $isMasuk($m);
                $nama = $m->product->product_name_clean ?? $m->product->product_name ?? '-';
                $exp = $m->expiry_date;
            @endphp
            <tr>
                <td class="ctr">{{ $i + 1 }}</td>
                <td class="ctr">{{ $m->created_at ? \Carbon\Carbon::parse($m->created_at)->format('d/m/Y') : '-' }}</td>
                <td class="ctr">{{ $m->doc_no ?? $m->d_doc ?? '-' }}</td>
                <td class="lft">{{ $pihakOf($m) }}</td>
                <td class="lft">{{ $nama }}</td>
                <td class="ctr">
                    {{ trim((string)($m->batch_no ?? '')) !== '' ? $m->batch_no : '-' }}
                    <div class="{{ $exp && $exp->isPast() ? 'exp' : 'sub' }}">ED: {{ $exp?->format('d/m/Y') ?? '-' }}</div>
                </td>
                <td class="num">{{ $masuk ? $fmt($m->qty) : '-' }}</td>
                <td class="num">{{ $masuk ? '-' : $fmt($m->qty) }}</td>
                <td class="ctr">{{ \Illuminate\Support\Str::words($m->user->name ?? 'System', 2, '') }}</td>
                <td class="lft">{{ $m->purpose ?: ($m->reason ?: '-') }}</td>
            </tr>
        @empty
            <tr><td colspan="10" class="ctr">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
