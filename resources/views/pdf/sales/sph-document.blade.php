@extends('pdf.layouts.approval-document')

@section('title', 'SPH - ' . $sph->sph_number)
@section('form-title', '') {{-- Dikosongkan agar teks "Surat Penawaran Harga" hilang --}}

@section('extra-style')
    p, td, th {
        font-family: "Times New Roman", Times, serif !important;
    }

    /* Sembunyikan "No. -" bawaan dari layout utama */
    .doc-number,
    .doc-title {
        display: none !important;
    }

    /* Styling agar ornamen menabrak ujung kertas */
    .full-width-ornament {
        position: absolute;
        top: -85px; 
        left: -45px;
        width: calc(100% + 90px);
        z-index: -10;
    }

    /* Penyesuaian Tabel SPH */
    table.items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
    }
    table.items-table th,
    table.items-table td {
        border: 1px solid #000 !important;
        padding: 6px 8px;
    }
    table.items-table th {
        /* Warna biru muda untuk header tabel */
        background-color: #d9edf7 !important; 
        text-align: center;
        font-weight: bold;
    }
@endsection

@section('content')
@php
    $ornament = 'images/orname.png';
    $ornamentPath = public_path($ornament);
@endphp

{{-- Ornamen selebar kertas di bagian paling atas --}}
@if(file_exists($ornamentPath))
    <img src="{{ public_path($ornament) }}" class="full-width-ornament" alt="Ornamen Atas">
@endif

<div style="margin-top: 8px; font-size: 14px; font-family: 'Times New Roman', Times, serif;">

    {{-- Nomor & Perihal --}}
    <table style="width: 100%; margin-bottom: 30px; border: none;">
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

    {{-- Tujuan SPH --}}
    <p style="margin-bottom: {{ $sph->customer_name ? '30px' : '35px' }};">
        Kepada Yth :<br>
        <strong>{{ $sph->customer_company }}</strong><br>
        Di Tempat
    </p>

    @if($sph->customer_name)
        <p style="margin-bottom: 30px;">UP : {{ $sph->customer_name }}</p>
    @endif

    {{-- Salam Pembuka --}}
    <p style="text-align: justify; margin-bottom: 30px; line-height: 1.5;">
        Dengan Hormat,<br>
        Kami PT. Rakha Nusantara Medika, salah satu penyedia alat kesehatan habis pakai,
        mengajukan Penawaran Harga sebagai berikut :
    </p>
</div>

{{-- Tabel Item --}}
<table class="items-table" style="font-family: 'Times New Roman', Times, serif; font-size: 12px;">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th>Nama Produk</th>
            <th width="13%">Sediaan</th>
            <th width="15%">HNA Price</th>
            <th width="15%">HNA / Pcs</th>
            <th width="11%">Diskon</th>
            <th width="20%">Net+PPN/PCS</th> {{-- Kolom diubah --}}
        </tr>
    </thead>
    <tbody>
        @forelse($sph->items as $i => $item)
            @php
                $hna = (float) ($item['base_price'] ?? $item['unit_price']);
                $packQty = (int) ($item['pack_qty'] ?? 1);
                $discount = (float) ($item['discount'] ?? 0);
                
                // Kalkulasi per Pcs
                $hnaPcs = $hna / max(1, $packQty);
                // Kalkulasi Net + PPN khusus untuk harga per Pcs
                $netPpnPcs = $hnaPcs * (1 - $discount / 100) * (1 + ($sph->vat_percent / 100));
                
                $discountPct = rtrim(rtrim(number_format($discount, 2, ',', '.'), '0'), ',');
            @endphp
            <tr>
                <td style="text-align: center;">{{ $i + 1 }}</td>
                <td>{{ $item['product_name'] }}</td>
                <td>{{ $item['presentation'] ?? '-' }}</td>
                <td style="text-align: right;">
                    Rp {{ number_format($hna, 0, ',', '.') }}
                </td>
                <td style="text-align: right;">
                    Rp {{ number_format($hnaPcs, 0, ',', '.') }}
                </td>
                <td style="text-align: center;">
                    {{ $discountPct }}%
                </td>
                <td style="text-align: right;">
                    Rp {{ number_format($netPpnPcs, 0, ',', '.') }} {{-- Memanggil hasil kalkulasi per Pcs --}}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center;">Data item tidak tersedia.</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Informasi Kontak --}}
<div style="font-size: 12px; font-family: 'Times New Roman', Times, serif;">
    <p style="margin-bottom: 15px;">Untuk informasi pembelian produk tersebut dapat menghubungi kami :</p>
    
    <table style="width: 100%; margin-bottom: 30px; border: none;">
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
            <td style="border: none; padding: 2px 0;"><strong>{{ $sph->ps_phone ?? '+62 813-2191-9149' }}</strong></td>
        </tr>
    </table>

    {{-- Penutup --}}
    <p style="margin-bottom: 25px;">Demikian Penawaran ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

    {{-- Tanda Tangan + Cap --}}
    @include('pdf.partials.sph-signature', [
        'signedName' => 'Tuah Maujana Sinaga',
        'signedRole' => 'Manager Operasional',
        'signedCity' => 'Bogor',
        'signedDate' => $sph->date ?: now(),
    ])
</div>
@endsection