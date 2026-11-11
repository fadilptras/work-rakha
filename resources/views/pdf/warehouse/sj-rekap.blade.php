@extends('pdf.layouts.export-document')

@section('title', 'Rekap Surat Jalan')
@section('form-title', 'REKAP SURAT JALAN')
@section('page-orientation', 'landscape')

@section('subtitle')
    @if(!empty($filter['date_from']) || !empty($filter['date_to']) || !empty($filter['search']) || (!empty($filter['customer']) && $filter['customer'] !== '-'))
        Filter:
        {{ !empty($filter['date_from']) ? 'dari ' . \Carbon\Carbon::parse($filter['date_from'])->format('d/m/Y') : '' }}
        {{ !empty($filter['date_to']) ? 'sampai ' . \Carbon\Carbon::parse($filter['date_to'])->format('d/m/Y') : '' }}
        {{ (!empty($filter['customer']) && $filter['customer'] !== '-') ? '| customer: ' . $filter['customer'] : '' }}
        {{ !empty($filter['search']) ? '| cari: ' . $filter['search'] : '' }}
    @else
        Seluruh dokumen
    @endif
    — dicetak {{ date('d M Y H:i') }} oleh {{ $printedBy ?? '-' }}
@endsection

@section('extra-style')
    table.export-table th, table.export-table td { border: 1px solid #000; }
    table.export-table th { font-size: 7pt; padding: 3px 2px; background-color: #f1f5f9; color: #1e293b; font-weight: bold; }
    table.export-table td { font-size: 7pt; padding: 2px 3px; }
    table.export-table td.num { text-align: right; }
    table.export-table td.ctr { text-align: center; }
    table.export-table td.lft { text-align: left; }
@endsection

@section('content')
<table class="export-table" width="100%">
    <thead>
        <tr>
            <th style="width:3%">No</th>
            <th style="width:7%">Tanggal</th>
            <th style="width:13%">No. SJ</th>
            <th style="width:10%">Keterangan</th>
            <th style="width:18%">Pihak</th>
            <th style="width:25%">Product</th>
            <th style="width:10%">Dibuat Oleh</th>
        </tr>
    </thead>
    <tbody>
        @forelse($docs as $i => $d)
            <tr>
                <td class="ctr">{{ $i + 1 }}</td>
                <td>{{ $d->mutation_date ? \Carbon\Carbon::parse($d->mutation_date)->format('d/m/Y') : '-' }}</td>
                <td>{{ $d->doc_no }}{{ $d->po_no ? ' / PO: ' . $d->po_no : '' }}{{ $d->expedition ? ' / ' . $d->expedition : '' }}</td>
                <td>{{ $d->purpose ?? '-' }}</td>
                <td class="lft">{{ $d->customer?->customer_name ?? $d->supplier?->supplier_name ?? 'Pemakaian Internal' }}</td>
                <td class="lft">
                    @forelse($d->moves as $m)
                        {{ $m->product->product_name_clean ?? $m->product->product_name ?? '-' }}
                        ({{ rtrim(rtrim(number_format((float) $m->qty, 2, '.', ''), '0'), '.') }} {{ $m->uom ?? $m->product->unit ?? '' }}){{ !$loop->last ? '; ' : '' }}
                    @empty
                        -
                    @endforelse
                </td>
                <td>{{ $d->creator?->name ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="ctr">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
