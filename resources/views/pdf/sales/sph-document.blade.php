@extends('pdf.layouts.sales-document')

@section('title', 'SPH - ' . $sph->sph_number)
@section('documentType', 'sph')

@section('extra-style')
    p, td, th {
        font-family: "Times New Roman", Times, serif !important;
    }
    .doc-number, .doc-title { display: none !important; }
    table.items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    table.items-table th,
    table.items-table td {
        border: 1px solid #000 !important;
        padding: 6px 8px;
    }
    table.items-table th {
        background-color: #d9edf7 !important;
        text-align: center;
        font-weight: bold;
    }
@endsection

@section('content')
@php
    $items = $sph->items ?? [];
    $hasDiscount = collect($items)->contains(fn($it) => (float) ($it['discount'] ?? 0) > 0);
    $showDiscount = $hasDiscount;
    $colCount = 4 + ($showDiscount ? 1 : 0);
    $netHeader = ((int) ($sph->vat_percent ?? 0) > 0) ? 'Net+PPN/PCS' : 'Net/Pcs';
@endphp

<div style="font-size: 12px; font-family: 'Times New Roman', Times, serif;">
    <table style="width: 100%; margin-bottom: 35px; border: none;">
        <tr>
            <td style="width: 80px; border: none; padding: 1px 0;">Nomor</td>
            <td style="width: 15px; border: none; padding: 2px 0;">:</td>
            <td style="border: none; padding: 1px 0;">{{ $sph->sph_number }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 1px 0;">Perihal</td>
            <td style="border: none; padding: 2px 0;">:</td>
            <td style="border: none; padding: 1px 0; font-weight: bold;">Penawaran Harga</td>
        </tr>
    </table>

    <p style="margin-bottom: 35px; line-height: 1.5;">
        Kepada Yth :<br>
        <strong style="font-size: 14px;">{{ $sph->customer_company }}</strong><br>
        Di Tempat
    </p>

    @if($sph->customer_name)
        <p style="margin-bottom: 35px; line-height: 1.5;">UP : {{ $sph->customer_name }}</p>
    @endif

    <p style="text-align: justify; margin-bottom: 10px; line-height: 1.5;">
        Dengan Hormat,<br>
        Kami PT. Rakha Nusantara Medika, salah satu penyedia alat kesehatan habis pakai, mengajukan Penawaran Harga sebagai berikut :
    </p>
</div>

<table class="items-table" style="font-family: 'Times New Roman', Times, serif; font-size: 12px;">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th>Nama Produk</th>
            <th width="14%">Sediaan</th>
            <th width="14%">HNA Price</th>
            @if($showDiscount)<th width="8%">Diskon</th>@endif
            <th width="16%">{{ $netHeader }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sph->items as $i => $item)
            @php
                $hna = (float) ($item['base_price'] ?? $item['unit_price']);
                $packQty = (int) ($item['pack_qty'] ?? 1);
                $discount = (float) ($item['discount'] ?? 0);
                $hnaPcs = $hna / max(1, $packQty);
                $netPpnPcs = $hnaPcs * (1 - $discount / 100) * (1 + ($sph->vat_percent / 100));
                $discountPct = rtrim(rtrim(number_format($discount, 2, ',', '.'), '0'), ',');
            @endphp
            <tr>
                <td style="text-align: center;">{{ $i + 1 }}</td>
                <td>{{ $item['product_name'] }}</td>
                <td style="text-align: center;">{{ $item['presentation'] ?? '-' }}</td>
                <td style="text-align: right;">Rp {{ number_format($hna, 0, ',', '.') }}</td>
                @if($showDiscount)<td style="text-align: center;">{{ $discount > 0 ? $discountPct . '%' : '-' }}</td>@endif
                <td style="text-align: right;">Rp {{ number_format($netPpnPcs, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $colCount }}" style="text-align: center;">Data item tidak tersedia.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div style="font-size: 12px; font-family: 'Times New Roman', Times, serif;">
    <p style="margin-bottom: 10px;">Untuk informasi pembelian produk tersebut dapat menghubungi kami :</p>
    <table style="width: 100%; margin-bottom: 10px; border: none;">
        <tr>
            <td style="width: 110px; border: none; padding: 2px 0;">Perusahaan</td>
            <td style="width: 15px; border: none; padding: 2px 0;">:</td>
            <td style="border: none; padding: 2px 0;"><strong>PT Rakha Nusantara Medika</strong></td>
        </tr>
        <tr>
            <td style="border: none; padding: 2px 0;">Contact Person</td>
            <td style="border: none; padding: 2px 0;">:</td>
            <td style="border: none; padding: 2px 0;"><strong>{{ $sph->ps }}</strong></td>
        </tr>
        <tr>
            <td style="border: none; padding: 2px 0;">Telp.</td>
            <td style="border: none; padding: 2px 0;">:</td>
            <td style="border: none; padding: 2px 0;"><strong>{{ $sph->ps_phone ?? '0813-2191-9149' }}</strong></td>
        </tr>
    </table>
    <p style="margin-bottom: 15px;">Demikian Penawaran ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>
</div>
@endsection
