@extends('pdf.layouts.approval-document')

@section('title', 'SPH - ' . $sph->sph_number)
@section('form-title', '') 

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
        margin-bottom: 10px; /* Jarak SESUDAH tabel dikurangi */
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

<div style="margin-top: 8px; font-size: 12px; font-family: 'Times New Roman', Times, serif;">

    {{-- Nomor & Perihal --}}
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

    {{-- Tujuan SPH --}}
    {{-- Menambahkan line-height dan mengatur jarak margin antar bagian --}}
    <p style="margin-bottom: {{ $sph->customer_name ? '35px' : '25px' }}; line-height: 1.5;">
        Kepada Yth :<br>
        <strong style="font-size: 14px;">{{ $sph->customer_company }}</strong><br>
        Di Tempat
    </p>

    @if($sph->customer_name)
        <p style="margin-bottom: 35px; line-height: 1.5;">UP : {{ $sph->customer_name }}</p>
    @endif

    {{-- Salam Pembuka --}}
    {{-- Margin bottom dikurangi agar jarak SEBELUM tabel tidak terlalu jauh --}}
    <p style="text-align: justify; margin-bottom: 10px; line-height: 1.5;">
        Dengan Hormat,<br>
        Kami PT. Rakha Nusantara Medika, salah satu penyedia alat kesehatan habis pakai,
        mengajukan Penawaran Harga sebagai berikut :
    </p>
</div>

{{-- Tabel Item (kolom menyesuaikan otomatis dari isi dokumen) --}}
@php
    $items = $sph->items ?? [];

    // Aturan otomatis:
    // - Kolom HNA/Pcs dihapus permanen dari PDF.
    // - Tak ada diskon di item mana pun -> kolom Diskon sembunyi.
    $hasDiscount = collect($items)->contains(fn($it) => (float) ($it['discount'] ?? 0) > 0);

    $showDiscount = $hasDiscount;

    $colCount = 4 + ($showDiscount ? 1 : 0); // No + Nama + Sediaan + HNA [+ Diskon] + Net
    $netHeader = ((int) ($sph->vat_percent ?? 0) > 0) ? 'Net+PPN/PCS' : 'Net/Pcs';
@endphp
<table class="items-table" style="font-family: 'Times New Roman', Times, serif; font-size: 12px;">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th>Nama Produk</th> {{-- Dibiarkan tanpa width agar otomatis mengisi sisa ruang menjadi lebih lebar --}}
            <th width="14%">Sediaan</th> {{-- Lebar dikurangi dari 13% ke 10% --}}
            <th width="14%">HNA Price</th>
            @if($showDiscount)<th width="8%">Diskon</th> {{-- Lebar dikurangi dari 11% ke 8% --}}@endif
            <th width="16%">{{ $netHeader }}</th> {{-- Lebar dikurangi dari 20% ke 16% --}}
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
                <td style="text-align: center;">{{ $item['presentation'] ?? '-' }}</td>
                <td style="text-align: right;">
                    Rp {{ number_format($hna, 0, ',', '.') }}
                </td>
                @if($showDiscount)<td style="text-align: center;">
                    {{ $discount > 0 ? $discountPct . '%' : '-' }}
                </td>@endif
                <td style="text-align: right;">
                    Rp {{ number_format($netPpnPcs, 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $colCount }}" style="text-align: center;">Data item tidak tersedia.</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Informasi Kontak --}}
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
            <td style="border: none; padding: 2px 0;"><strong>{{ $sph->ps_phone ?? '+62 813-2191-9149' }}</strong></td>
        </tr>
    </table>

    {{-- Penutup --}}
    <p style="margin-bottom: 15px;">Demikian Penawaran ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

    {{-- Tanda Tangan + Cap --}}
    @include('pdf.partials.sph-signature', [
        'signedName' => 'Tuah Maujana Sinaga',
        'signedRole' => 'Manager Operasional',
        'signedCity' => 'Bogor',
        'signedDate' => $sph->date ?: now(),
    ])
</div>
@endsection