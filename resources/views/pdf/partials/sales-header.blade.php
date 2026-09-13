@php
    $docType = trim($__env->yieldContent('documentType'));
    if (!$docType && isset($documentType)) $docType = $documentType;
    $isPricelist = $docType === 'pricelist';
    $isSph = $docType === 'sph';
@endphp

@if($isPricelist)
    {{-- Pricelist header --}}
    <div style="text-align: center; margin: 15px 0 10px;">
        <div style="font-family: 'Times New Roman', Times, serif; font-size: 18px; font-weight: bold; color: #2b5b84; letter-spacing: 1px; margin: 0 0 5px;">DAFTAR HARGA</div>
        <div style="font-family: 'Times New Roman', Times, serif; font-size: 14px; font-weight: bold; color: #2b5b84; margin: 0;">PT. RAKHA NUSANTARA MEDIKA</div>
    </div>
    <div style="text-align: right; margin-bottom: 4px;">
        <span style="font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000;">Update Bulan {{ now()->translatedFormat('F Y') }}</span>
    </div>
@elseif($isSph)
    {{-- SPH header is handled inside sph-document content (Nomor/Perihal), keep empty here --}}
@else
    {{-- Generic sales header --}}
    <div style="text-align: center; margin: 12px 0 8px;">
        <div style="font-family: 'Times New Roman', Times, serif; font-size: 16px; font-weight: bold; color: #2b5b84;">PT. RAKHA NUSANTARA MEDIKA</div>
    </div>
@endif
