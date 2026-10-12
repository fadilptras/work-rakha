@extends('pdf.layouts.export-document')

@section('title', 'Rekap Surat Jalan')
@section('form-title', 'REKAP SURAT JALAN')

@section('subtitle')
    Dicetak {{ date('d M Y H:i') }} oleh {{ $printedBy ?? '-' }}
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
            <th style="width:9%">Tanggal</th>
            <th style="width:13%">No. SJ</th>
            <th style="width:7%">Arah</th>
            <th style="width:22%">Pihak</th>
            <th style="width:12%">Keperluan</th>
            <th style="width:7%" class="num">Baris</th>
            <th style="width:10%">Status</th>
            <th style="width:17%">Ekspedisi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($docs as $i => $d)
            <tr>
                <td class="ctr">{{ $i + 1 }}</td>
                <td>{{ $d->mutation_date ? \Carbon\Carbon::parse($d->mutation_date)->format('d/m/Y') : '-' }}</td>
                <td>{{ $d->doc_no }}</td>
                <td class="ctr">{{ $d->direction === 'in' ? 'Masuk' : 'Keluar' }}</td>
                <td>{{ $d->customer?->customer_name ?? $d->supplier?->supplier_name ?? 'Pemakaian Internal' }}</td>
                <td>{{ $d->purpose ?? '-' }}</td>
                <td class="num">{{ $d->moves_count }}</td>
                <td class="ctr">{{ ucfirst($d->status) }}</td>
                <td>{{ $d->expedition ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="ctr">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
