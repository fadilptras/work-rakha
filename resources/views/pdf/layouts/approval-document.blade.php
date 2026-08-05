<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 9px; color: #333; line-height: 1.2; }
        .container { width: 95%; margin: 15px auto; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 7px; text-align: left; vertical-align: top; }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; }

        table.items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items-table th, table.items-table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        table.items-table th { background-color: #f8f9fa; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }

        .section-title {
            background-color: #eaf2f8; padding: 8px; font-size: 11px; font-weight: bold;
            margin: 10px 0; border-left: 4px solid #3498db; color: #003366; text-transform: uppercase;
        }

        .doc-title { text-align: center; font-weight: bold; margin: 4px 0 2px; }
        .doc-number { text-align: center; margin: 0 0 15px; }

        .status-box { padding: 10px; margin-top: 25px; border-radius: 4px; text-align: center; font-weight: bold; font-size: 14px; border: 1px solid #ccc; }
        .status-hijau  { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .status-merah  { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .status-kuning { background-color: #fff3cd; color: #856404; border-color: #ffeeba; }
        .status-abu    { background-color: #e2e3e5; color: #383d41; border-color: #d6d8db; }

        .signatures { width: 100%; margin-top: 20px; border: none; table-layout: fixed; }
        .signatures td { border: none; text-align: center; vertical-align: top; padding: 5px; }
        .ttd-header { margin-bottom: 15px; font-size: 10px; color: #333; }
        .st-approved { color: #28a745; font-weight: bold; font-style: italic; font-size: 11px; margin-bottom: 15px; }
        .st-rejected { color: #dc3545; font-weight: bold; font-style: italic; font-size: 11px; margin-bottom: 15px; }
        .st-placeholder { margin: 15px 0; border-bottom: 1px dotted #aaa; color: #aaa; font-style: italic; font-size: 9px; padding-bottom: 5px; }
        .ttd-nama { font-weight: bold; text-decoration: underline; font-size: 11px; color: #000; }
        .ttd-jabatan { font-size: 9px; color: #444; margin-top: 2px; }
        .ttd-tanggal { font-weight: bold; color: #555; font-size: 9px; margin-top: 5px; }

        .catatan { background: #f9f9f9; border-left: 3px solid #ccc; padding: 8px; margin-top: 5px; font-style: italic; font-size: 10px; }

        @yield('extra-style')
    </style>
</head>
<body>
    <div class="container">
        @include('pdf.partials.kop-surat')
        <p class="doc-title">@yield('form-title')</p>
        <p class="doc-number">No. {{ $nomorDokumen ?? '-' }}</p>

        @yield('content')
    </div>
</body>
</html>