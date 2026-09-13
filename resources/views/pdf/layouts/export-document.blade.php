<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <style>
        @page { size: A4 @yield('page-orientation', 'portrait'); margin: 12mm 10mm 12mm 10mm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        .container { width: 97%; margin: 0 auto; }

        .export-title { text-align: center; font-weight: bold; font-size: 14px; color: #1e293b; margin: 2px 0 2px; letter-spacing: 0.5px; font-family: "Times New Roman", Times, serif; }
        .export-subtitle { text-align: center; font-size: 9px; color: #475569; margin: 0 0 8px; font-family: "Times New Roman", Times, serif; }
        .export-meta { font-size: 9px; color: #334155; margin-bottom: 8px; }
        .export-meta table { width: 100%; border-collapse: collapse; }
        .export-meta td { border: none; padding: 1px 4px; font-size: 9px; }

        table.export-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
        table.export-table th, table.export-table td { border: 1px solid #64748b; padding: 4px 5px; font-size: 8pt; vertical-align: middle; word-wrap: break-word; }
        table.export-table th { background-color: #1e293b; color: #fff; font-weight: bold; text-align: center; }
        table.export-table td { text-align: center; }
        table.export-table td:nth-child(2) { text-align: left; }

        .export-footer { font-size: 8px; color: #64748b; text-align: right; margin-top: 12px; border-top: 1px solid #e2e8f0; padding-top: 6px; }
        .page-number:after { content: counter(page); }

        @yield('extra-style')
    </style>
</head>
<body>
    <div class="container">
        @include('pdf.partials.kop-surat')
        <p class="export-title">@yield('form-title')</p>
        @hasSection('subtitle')
            <p class="export-subtitle">@yield('subtitle')</p>
        @endif
        @hasSection('meta')
            <div class="export-meta">@yield('meta')</div>
        @endif

        @yield('content')

        <div class="export-footer">
            Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB &nbsp;|&nbsp; Hal <span class="page-number"></span>
            @hasSection('footer-note')
                &nbsp;|&nbsp; @yield('footer-note')
            @endif
        </div>
    </div>
</body>
</html>
