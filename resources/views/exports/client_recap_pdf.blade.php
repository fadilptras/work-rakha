<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Klien</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #374151; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 18px; color: #111827; }
        .header p { margin: 5px 0; font-size: 14px; color: #4b5563; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: right; }
        
        th { 
            background-color: #f8fafc; 
            text-align: center; 
            font-weight: 700; 
            text-transform: uppercase;
            font-size: 11px;
            color: #6b7280;
        }
        
        /* Spesifik Header */
        th.col-net { color: #2563eb; }
        th.col-out { color: #dc2626; }
        th.col-saldo { color: #111827; }

        td.text-left { text-align: left; }
        td.text-center { text-align: center; font-weight: normal; }
        
        /* Baris Khusus */
        tr.saldo-awal { background-color: #fef08a; }
        tr.saldo-awal td { font-weight: bold; color: #111827; }
        
        tr.totals { background-color: #f8fafc; font-weight: bold; }
        
        /* Warna Teks Data */
        .text-gray { color: #9ca3af; }
        .text-blue { color: #2563eb; font-weight: 600; }
        .text-red { color: #dc2626; }
        .text-dark { color: #111827; font-weight: 600; }
        
        /* Ikon sederhana untuk Saldo Awal */
        .icon {
            display: inline-block;
            background-color: #facc15;
            color: #854d0e;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            font-size: 10px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Rekapitulasi Tahun {{ $year }}</h2>
        <p><strong>{{ $client->nama_perusahaan }}</strong></p>
    </div>

    @php
        $formatVal = function($val, $colorClass = '') {
            if (empty($val) || $val == 0 || $val === '-') {
                return '<span class="text-gray">-</span>';
            }
            return '<span class="' . $colorClass . '">' . number_format($val, 0, ',', '.') . '</span>';
        };
    @endphp

    <table>
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
                    <span class="icon">▶</span> <span style="font-style: italic;">Saldo Awal</span>
                </td>
                <td class="text-dark">{{ number_format($client->saldo_awal ?? 0, 0, ',', '.') }}</td>
            </tr>
            @foreach($recap as $r)
            <tr>
                <td class="text-left" style="font-weight: 600; color:#374151;">{{ $r['month_name'] }}</td>
                <td>{!! $formatVal($r['gross_in'], 'text-gray') !!}</td>
                <td class="text-center">{!! empty($r['komisi_text']) || $r['komisi_text'] === '-' ? '<span class="text-gray">-</span>' : '<span class="text-gray">'.$r['komisi_text'].'</span>' !!}</td>
                <td>{!! $formatVal($r['net_value'], 'text-blue') !!}</td>
                <td>{!! $formatVal($r['out'], 'text-red') !!}</td>
                <td class="text-dark">{{ number_format($r['saldo'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="totals">
                <td colspan="3" class="text-center">TOTAL KESELURUHAN TAHUN {{ $year }}</td>
                <td class="text-blue">{{ number_format($totals['net_value'], 0, ',', '.') }}</td>
                <td class="text-red">{{ number_format($totals['out'], 0, ',', '.') }}</td>
                <td class="text-dark">{{ number_format($totals['saldo'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
