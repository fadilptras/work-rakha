<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 11px; color: #1e293b; line-height: 1.4; }
        .container { width: 95%; margin: 0 auto; position: relative; }
        .full-width-ornament { position: absolute; top: -85px; left: -45px; width: calc(100% + 90px); z-index: -10; }

        table.items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.items-table th, table.items-table td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; }
        table.items-table th { background-color: #d9edf7; text-align: center; font-weight: bold; }
        table.items-table td { vertical-align: top; }

        .doc-header { text-align: center; margin: 15px 0 10px; }
        .doc-header-title { font-size: 18px; font-weight: bold; color: #2b5b84; letter-spacing: 1px; margin: 0 0 4px; }
        .doc-header-subtitle { font-size: 14px; font-weight: bold; color: #2b5b84; margin: 0; }
        .doc-meta { text-align: right; margin-bottom: 6px; font-size: 11px; }

        @yield('extra-style')
    </style>
</head>
<body>
    <div class="container">
        @php $ornament = 'images/orname.png'; $ornamentPath = public_path($ornament); @endphp
        @if(file_exists($ornamentPath))
            <img src="{{ public_path($ornament) }}" class="full-width-ornament" alt="Ornamen">
        @endif

        {{-- Sales header (used by pricelist & sph) --}}
        @hasSection('sales-header')
            @yield('sales-header')
        @else
            @include('pdf.partials.sales-header')
        @endif

        @yield('content')

        @include('pdf.partials.sph-signature', [
            'signedName' => $signedName ?? 'Tuah Maujana Sinaga',
            'signedRole' => $signedRole ?? 'Manager Operasional',
            'signedCity' => $signedCity ?? 'Bogor',
            'signedDate' => $signedDate ?? now(),
        ])
    </div>
</body>
</html>
