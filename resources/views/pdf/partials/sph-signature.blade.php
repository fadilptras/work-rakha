@php
    $cap = 'images/cap rakha.png';
    $capPath = public_path($cap);
    $fontPath = public_path('fonts/GreatVibes-Regular.ttf');

    $signedName = $signedName ?? 'Tuah Maujana Sinaga';
    $signedRole = $signedRole ?? 'Manager Operasional';
    $signedCity = $signedCity ?? 'Bogor';
    $signedDate = $signedDate ?? now();
@endphp

<style>
    @font-face {
        font-family: 'GreatVibes';
        src: url('{{ $fontPath }}') format('truetype');
        font-weight: normal;
        font-style: normal;
    }
</style>

<div style="width: 100%; margin-top: 20px;">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="border: none; width: 60%;"></td>
            <td style="border: none; text-align: center; position: relative;">
                <p style="font-family: 'Times New Roman', Times, serif; font-size: 12px; margin-bottom: 4px;">
                    {{ $signedCity }}, {{ $signedDate->translatedFormat('d F Y') ? $signedDate->translatedFormat('d F Y') : $signedDate->format('d F Y') }}
                </p>
                <p style="font-family: 'Times New Roman', Times, serif; font-size: 12px; margin-bottom: 8px;">Hormat kami,</p>

                {{-- Area tanda tangan + cap (overlay) --}}
                <div style="height: 90px; position: relative;">
                    {{-- Cap stempel digeser ke kanan (left: 65%) --}}
                    @if(file_exists($capPath))
                        <img src="{{ public_path($cap) }}"
                             style="position: absolute; left: 65%; top: 50%; transform: translate(-50%,-50%) rotate(-8deg); width: 95px; height: auto; opacity: 0.9; z-index: -1;">
                    @endif
                    
                    {{-- Tanda tangan menggunakan font GreatVibes lokal dengan ukuran proporsional (12px - 22px) --}}
                    <div style="position: absolute; left: 50%; top: 50%; transform: translate(-50%,-55%); width: 100%;">
                        <span style="font-family: 'GreatVibes', cursive; font-size: 22px; color: #1a2b4a; letter-spacing: 0.5px;">
                            {{ $signedName }}
                        </span>
                    </div>
                </div>

                <div style="border-bottom: 1px solid #000; width: 150px; margin: 2px auto 4px;"></div>
                <p style="font-family: 'Times New Roman', Times, serif; font-size: 12px; font-weight: bold; margin: 0;">{{ $signedName }}</p>
                <p style="font-family: 'Times New Roman', Times, serif; font-size: 12px; margin: 0;">{{ $signedRole }}</p>
            </td>
        </tr>
    </table>
</div>