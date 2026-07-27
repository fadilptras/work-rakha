<!DOCTYPE html>
<html>
<head>
    <title>Rekap Absensi Bulanan</title>
    <style>
        @page {
            size: landscape;
            margin: 5mm 5mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5px;
            color: #333;
        }

        /* HEADER DOKUMEN (KOP) */
        .header-doc {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 5px;
        }
        .header-doc h1 { 
            margin: 0; 
            font-size: 16px; 
            text-transform: uppercase; 
            color: #1e3a8a;
            font-weight: 800;
        }
        .header-doc p { 
            margin: 1px 0; 
            font-size: 9px;
            color: #555; 
        }

        /* TABEL */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 10px;
        }

        table.main-table th, table.main-table td {
            border: 1px solid #d1d5db;
            padding: 0; 
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word;
        }
        
        table.main-table td.pad-normal {
            padding: 2px 1px;
        }

        /* HEADER TABEL DEFAULT */
        table.main-table thead th {
            background-color: #1e3a8a;
            color: white;
            font-weight: bold;
            font-size: 7.5px;
            text-transform: uppercase;
            padding: 3px 1px;
        }

        table.main-table thead tr:nth-child(2) th {
            background-color: #2563eb;
        }

        /* UKURAN KOLOM */
        .col-karyawan { width: 13%; text-align: left; padding-left: 3px; }
        .col-date { width: 13.5px; } 
        .col-late { width: 8.5%; }
        .col-sum { width: auto; font-weight: bold; background-color: #eff6ff; }

        /* ISI TABEL DEFAULT */
        table.main-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        
        /* Typography */
        .name-text { font-weight: bold; color: #1e3a8a; display: block; text-align: left; font-size: 7.5px; }
        .jabatan-text { display: block; text-align: left; color: #64748b; font-style: italic; margin-top: 1px; font-size: 6.5px; }
        
        /* Status Stack */
        .status-stack { display: block; width: 100%; font-size: 6px; }
        .status-top { display: block; width: 100%; border-bottom: 1px solid #e5e7eb; padding: 1px 0; line-height: 1; }
        .status-bottom { display: block; width: 100%; padding: 1px 0; line-height: 1; }
        .status-single { display: block; padding: 3px 0; }

        /* Warna Status Cell & Text */
        .cell-h { background-color: #d1e7dd !important; color: #0f5132 !important; font-weight: bold; }
        .cell-hl { background-color: #d1e7dd !important; color: #6b21a8 !important; font-weight: bold; }
        .cell-s { background-color: #f8d7da !important; color: #842029 !important; font-weight: bold; }
        .cell-i { background-color: #fff3cd !important; color: #664d03 !important; font-weight: bold; }
        .cell-c { background-color: #cfe2ff !important; color: #084298 !important; font-weight: bold; }
        .cell-a { background-color: #e2e3e5 !important; color: #41464b !important; font-weight: bold; }
        .cell-l { background-color: #f3e8ff !important; color: #6b21a8 !important; font-weight: bold; }
        .cell-empty { color: #9ca3af; }

        /* FOOTER */
        .footer-table { width: 100%; margin-top: 5px; border: none; }
        .footer-table td { border: none; padding: 5px 0; vertical-align: top; }
        .legend-item { margin-right: 15px; font-size: 7px; }
        .timestamp { font-size: 7px; color: #555; font-style: italic; text-align: right; }

    </style>
</head>
<body>
    <div class="header-doc">
        <h1>Laporan Rekap Absensi Bulanan</h1>
        <p>PT RAKHA NUSANTARA MEDIKA</p>
        <p>Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }}</strong></p>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-karyawan" style="text-align: center;">Karyawan</th>
                <th colspan="{{ $allDates->count() }}">Bulan {{ \Carbon\Carbon::parse($startDate)->isoFormat('MMMM YYYY') }}</th>
                <th colspan="6">Rekap Kehadiran</th>
                <th rowspan="2" class="col-late">Waktu Terlambat</th>
            </tr>
            <tr>
                @foreach($allDates as $date)
                    @php
                        $isSunday = $date->isSunday();
                        $isSaturday = $date->isSaturday();
                        $dateKey = $date->format('Y-m-d'); // Format kunci yang cocok dengan Controller
                        $isHoliday = isset($holidays) && isset($holidays[$dateKey]);

                        // Inline style untuk memaksa warna di PDF
                        $thStyle = '';
                        if ($isSunday || $isHoliday) {
                            $thStyle = 'background-color: #dc2626 !important; color: white !important;'; 
                        } elseif ($isSaturday) {
                            $thStyle = 'background-color: #64748b !important; color: white !important;';
                        }
                    @endphp
                    <th class="col-date" style="{{ $thStyle }}">{{ $date->day }}</th>
                @endforeach
                
                <th class="col-sum" style="color:#166534;">H</th>
                <th class="col-sum" style="color:#dc2626;">S</th>
                <th class="col-sum" style="color:#d97706;">I</th>
                <th class="col-sum" style="color:#2563eb;">C</th>
                <th class="col-sum" style="color:#374151;">A</th>
                <th class="col-sum" style="color:#7c3aed;">L</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapData as $index => $data)
            <tr>
                <td class="col-karyawan pad-normal" style="text-align: left;">
                    <span class="name-text">{{ $data['user']->name ?? 'User Dihapus' }}</span>
                    <span class="jabatan-text">{{ $data['user']->divisi ?? '-' }}</span>
                </td>

                @foreach($allDates as $date)
                    @php
                        $isSunday = $date->isSunday();
                        $isSaturday = $date->isSaturday();
                        $dateKey = $date->format('Y-m-d'); // Format kunci yang cocok dengan Controller
                        $isHoliday = isset($holidays) && isset($holidays[$dateKey]);
                        
                        $statusString = $data['daily'][$dateKey] ?? '-';
                        $hasLembur = str_contains($statusString, 'L');
                        $mainStatus = $hasLembur ? trim(str_replace('L', '', $statusString)) : $statusString;
                        if ($mainStatus == "") $mainStatus = 'L'; 

                        $statusClass = 'cell-empty';
                        if ($statusString === 'H') {
                            $statusClass = 'cell-h';
                        } elseif ($statusString === 'H L' || $statusString === 'H  L') {
                            $statusClass = 'cell-hl';
                        } elseif ($statusString === 'S') {
                            $statusClass = 'cell-s';
                        } elseif ($statusString === 'I') {
                            $statusClass = 'cell-i';
                        } elseif ($statusString === 'C') {
                            $statusClass = 'cell-c';
                        } elseif ($statusString === 'A') {
                            $statusClass = 'cell-a';
                        } elseif ($statusString === 'L') {
                            $statusClass = 'cell-l';
                        }

                        // Inline style untuk memaksa warna background cell di PDF
                        $tdStyle = '';
                        if ($statusClass === 'cell-empty') {
                            if ($isSunday || $isHoliday) {
                                $tdStyle = 'background-color: #fef2f2 !important;'; 
                            } elseif ($isSaturday) {
                                $tdStyle = 'background-color: #f3f4f6 !important;';
                            }
                        }
                    @endphp
                    
                    <td class="{{ $statusClass }}" style="{{ $tdStyle }}">
                        @if ($hasLembur && $mainStatus != '-' && $mainStatus != 'L')
                            <div class="status-stack">
                                <span class="status-top">{{ $mainStatus }}</span>
                                <span class="status-bottom">L</span>
                            </div>
                        @else
                            <span class="status-single">{{ $mainStatus }}</span>
                        @endif
                    </td>
                @endforeach

                <td class="col-sum pad-normal cell-h">{{ $data['summary']['H'] }}</td>
                <td class="col-sum pad-normal cell-s">{{ $data['summary']['S'] }}</td>
                <td class="col-sum pad-normal cell-i">{{ $data['summary']['I'] }}</td>
                <td class="col-sum pad-normal cell-c">{{ $data['summary']['C'] }}</td>
                <td class="col-sum pad-normal cell-a">{{ $data['summary']['A'] }}</td>
                <td class="col-sum pad-normal cell-l">{{ $data['summary']['L'] }}</td>
                <td class="pad-normal" style="color: #b91c1c; font-weight: bold;">
                    {{ $data['summary']['terlambat_formatted'] }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $allDates->count() + 8 }}" class="pad-normal" style="padding: 15px; font-style: italic; color: #666;">
                    Data tidak ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td style="text-align: left; width: 70%;">
                <span style="font-weight: bold; font-size: 7.5px; display:block; margin-bottom:1px;">Keterangan Kode:</span>
                <span class="legend-item"><strong style="color: #0f5132;">H</strong> : Hadir</span>
                <span class="legend-item"><strong style="color: #842029;">S</strong> : Sakit</span>
                <span class="legend-item"><strong style="color: #664d03;">I</strong> : Izin</span>
                <span class="legend-item"><strong style="color: #084298;">C</strong> : Cuti</span>
                <span class="legend-item"><strong style="color: #41464b;">A</strong> : Alpha</span>
                <span class="legend-item"><strong style="color: #6b21a8;">L</strong> : Lembur</span>
            </td>
            <td class="timestamp">
                Dicetak otomatis oleh sistem pada: {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

</body>
</html>