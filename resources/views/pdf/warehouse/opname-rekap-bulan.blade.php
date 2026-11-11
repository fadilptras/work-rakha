@extends('pdf.layouts.export-document')

@section('title', 'Rekap Stock Opname ' . ($namaBulan ?? ''))
@section('page-orientation', 'landscape')
@section('form-title', 'REKAP STOCK OPNAME')

@section('subtitle')
    Periode {{ $namaBulan ?? '-' }} ({{ $bulan ?? '-' }})
@endsection

@section('meta')
    <table>
        <tr>
            <td><b>Total Entri:</b> {{ $summary['total'] ?? 0 }}</td>
            <td><b>Selesai:</b> {{ $summary['selesai'] ?? 0 }}</td>
            <td><b>Draf:</b> {{ $summary['draf'] ?? 0 }}</td>
            <td><b>Sesuai:</b> {{ $summary['sesuai'] ?? 0 }}</td>
            <td><b>Selisih:</b> {{ $summary['selisih'] ?? 0 }}</td>
        </tr>
    </table>
@endsection

@section('extra-style')
    table.export-table th { font-size: 7.5pt; padding: 4px 3px; background-color: #1e293b; color: #fff; }
    table.export-table td { font-size: 7.5pt; padding: 3px 3px; }
    table.export-table td.num { text-align: right; }
    table.export-table td.ctr { text-align: center; }
    table.export-table td.lft { text-align: left; }
    table.export-table td.status-done { color: #047857; font-weight: bold; }
    table.export-table td.status-draft { color: #b45309; font-weight: bold; }
    .rekap-note { font-size: 8pt; color: #475569; margin: 6px 0 0; }
@endsection

@section('content')
<table class="export-table" width="100%">
    <thead>
        <tr>
            <th style="width:3%">No</th>
            <th style="width:8%">Kode</th>
            <th style="width:19%">Nama Barang</th>
            <th style="width:5%">Satuan</th>
            <th style="width:7%">Tercatat</th>
            <th style="width:7%">Fisik</th>
            <th style="width:7%">Selisih</th>
            <th style="width:7%">Status</th>
            <th style="width:10%">No. Dokumen</th>
            <th style="width:10%">Waktu Hitung</th>
            <th style="width:8%">Petugas</th>
            <th style="width:9%">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
        <tr>
            <td class="ctr">{{ $row['no'] }}</td>
            <td class="ctr">{{ $row['kode'] }}</td>
            <td class="lft">{{ $row['produk'] }}</td>
            <td class="ctr">{{ $row['satuan'] }}</td>
            <td class="num">{{ $row['tercatat'] !== null ? $row['tercatat'] : '-' }}</td>
            <td class="num">{{ $row['terhitung'] !== null ? $row['terhitung'] : '-' }}</td>
            <td class="num">{{ $row['selisih'] !== null ? ($row['selisih'] > 0 ? '+' : '') . $row['selisih'] : '-' }}</td>
            <td class="ctr {{ $row['selesai'] ? 'status-done' : 'status-draft' }}">{{ $row['status'] }}</td>
            <td class="ctr">{{ $row['dok'] ?: '-' }}</td>
            <td class="ctr">{{ $row['waktu'] ?: '-' }}</td>
            <td class="lft">{{ $row['oleh'] ?: '-' }}</td>
            <td class="lft">{{ $row['catatan'] ?: '-' }}</td>
        </tr>
        @empty
        <tr>
            <td class="ctr" colspan="12">Belum ada hasil stock opname pada periode ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<p class="rekap-note">Rekap ini mencakup seluruh entri opname pada periode, termasuk entri yang masih berstatus draf.</p>
@endsection
