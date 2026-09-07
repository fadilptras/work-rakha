@php
    $ornament = 'images/orname.png';
    $ornamentPath = public_path($ornament);
@endphp

<style>
    .full-width-ornament {
        position: absolute;
        top: -85px; 
        left: -45px;
        width: calc(100% + 90px);
        z-index: -10;
    }
</style>

@if(file_exists($ornamentPath))
    <img src="{{ public_path($ornament) }}" class="full-width-ornament" alt="Ornamen Atas">
@endif

{{-- Blok Judul Tengah --}}
<div style="text-align: center; margin: 15px 0 10px;">
    <div style="font-family: 'Times New Roman', Times, serif; font-size: 18px; font-weight: bold; color: #2b5b84; letter-spacing: 1px; margin: 0 0 5px;">
        DAFTAR HARGA
    </div>
    <div style="font-family: 'Times New Roman', Times, serif; font-size: 14px; font-weight: bold; color: #2b5b84; margin: 0;">
        PT. RAKHA NUSANTARA MEDIKA
    </div>
</div>

{{-- Keterangan Bulan Rata Kanan & Rapat ke Tabel --}}
<div style="text-align: right; margin-bottom: 4px;">
    <span style="font-family: 'Times New Roman', Times, serif; font-size: 12px; color: #000;">
        Update Bulan {{ now()->translatedFormat('F Y') }}
    </span>
</div>