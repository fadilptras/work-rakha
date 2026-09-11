<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Sales Klien - {{ $client->customer_name }}</title>
    <style>
        body { font-family: 'Times New Roman', 'Times', serif; font-size: 12px; color: #111827; line-height: 1.5; }
        .doc-title { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #111827; padding-bottom: 12px; }
        .doc-title h2 { margin: 0 0 4px; padding: 0; font-size: 22px; font-weight: 700; color: #000000; }
        .doc-title p { margin: 0; font-size: 13px; font-weight: 700; color: #1f2937; }

        table.info { width: 100%; border-collapse: collapse; margin: 16px 0 6px; font-size: 12px; }
        table.info td { border: none; padding: 7px 8px; text-align: left; vertical-align: top; }
        table.info td.label { font-weight: 700; color: #000000; width: 22%; }
        table.info td.sep { width: 3%; color: #111827; font-weight: 700; }
        table.info td.value { color: #111827; font-weight: 700; width: 25%; }
        table.info td.value-wide { color: #111827; font-weight: 700; }

        .subtitle { font-size: 14px; font-weight: 700; color: #000000; margin: 18px 0 2px; }

        table.history { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.history th, table.history td { border: 1px solid #6b7280; padding: 9px 12px; text-align: right; }

        table.history th {
            background-color: #e5e7eb;
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            color: #000000;
        }

        /* Spesifik Header */
        table.history th.col-net { color: #1d4ed8; }
        table.history th.col-out { color: #b91c1c; }
        table.history th.col-saldo { color: #000000; }

        table.history td.text-left { text-align: left; }
        table.history td.text-center { text-align: center; font-weight: normal; }

        /* Kolom Value (Net) = biru pudar, Usage (Out) = merah pudar */
        table.history td.col-net { background-color: #dbeafe; }
        table.history td.col-out { background-color: #fee2e2; }

        /* Baris Khusus */
        tr.saldo-awal { background-color: #fef9c3; }
        tr.saldo-awal td { font-weight: bold; color: #111827; }

        tr.totals { background-color: #e5e7eb; font-weight: bold; }

        /* Warna Teks Data (nyata, tanpa abu) */
        .text-plain { color: #111827; }
        .text-blue { color: #1d4ed8; font-weight: 700; }
        .text-red { color: #b91c1c; font-weight: 700; }
        .text-dark { color: #000000; font-weight: 700; }

        .footer { margin-top: 24px; font-size: 11px; color: #374151; text-align: right; }
    </style>
</head>
<body>
    <div class="doc-title">
        <h2>Rekapitulasi Sales Klien</h2>
        <p>Lampiran Pengajuan Dana Support</p>
    </div>

    <table class="info">
        <tr>
            <td class="label">Nama Pengaju (PIC)</td>
            <td class="sep">:</td>
            <td class="value">{{ $submittedBy ?? '-' }}</td>
            <td class="label">Tanggal Pengajuan</td>
            <td class="sep">:</td>
            <td class="value">{{ $submittedDate ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Client</td>
            <td class="sep">:</td>
            <td class="value">{{ $client->client_name ?? '-' }}</td>
            <td class="label">Dana Diajukan</td>
            <td class="sep">:</td>
            <td class="value amount">Rp {{ number_format($submittedAmount ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Nama Customer</td>
            <td class="sep">:</td>
            <td class="value-wide" colspan="4">{{ $client->customer_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Keperluan</td>
            <td class="sep">:</td>
            <td class="value-wide" colspan="4">{{ $purpose ?? '-' }}</td>
        </tr>
    </table>

    <p class="subtitle">Riwayat Transaksi Tahun {{ $year }}</p>

    @php
        $formatVal = function($val, $colorClass = '') {
            if (empty($val) || $val == 0 || $val === '-') {
                return '<span class="text-plain">-</span>';
            }
            return '<span class="' . $colorClass . '">' . number_format($val, 0, ',', '.') . '</span>';
        };
    @endphp

    <table class="history">
        <thead>
            <tr>
                <th class="text-left">Bulan</th>
                <th>Sales (In)</th>
                <th>Komisi</th>
                <th class="col-net">Value (Net)</th>
                <th class="col-out">Usage (Out)</th>
                <th class="col-saldo">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr class="saldo-awal">
                <td colspan="5" class="text-left">
                    <span style="font-style: italic;">Saldo Awal</span>
                </td>
                <td class="text-dark">{{ number_format($client->opening_balance ?? 0, 0, ',', '.') }}</td>
            </tr>
            @foreach($recap as $r)
            <tr>
                <td class="text-left" style="font-weight: 700; color:#111827;">{{ $r['month_name'] }}</td>
                <td>{!! $formatVal($r['gross_in'], 'text-plain') !!}</td>
                <td class="text-center">{!! empty($r['commission_text']) || $r['commission_text'] === '-' ? '<span class="text-plain">-</span>' : '<span class="text-plain">'.$r['commission_text'].'</span>' !!}</td>
                <td class="col-net">{!! $formatVal($r['net_value'], 'text-blue') !!}</td>
                <td class="col-out">{!! $formatVal($r['out'], 'text-red') !!}</td>
                <td class="text-dark">{{ number_format($r['saldo'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="totals">
                <td colspan="3" class="text-center">TOTAL KESELURUHAN TAHUN {{ $year }}</td>
                <td class="col-net text-blue">{{ number_format($totals['net_value'], 0, ',', '.') }}</td>
                <td class="col-out text-red">{{ number_format($totals['out'], 0, ',', '.') }}</td>
                <td class="text-dark">{{ number_format($totals['saldo'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
