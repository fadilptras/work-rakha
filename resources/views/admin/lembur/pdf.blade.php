<!DOCTYPE html>
<html>
<head>
    <title>Laporan Lembur Karyawan</title>
    <style>
        @page {
            size: landscape;
            margin: 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }

        /* --- HEADER DOKUMEN --- */
        .header-doc {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 10px;
        }
        .header-doc h1 { 
            margin: 0; 
            font-size: 20px; 
            text-transform: uppercase; 
            color: #1e3a8a; 
            font-weight: 800;
        }
        .header-doc p { 
            margin: 2px 0; 
            font-size: 11px; 
            color: #555; 
        }

        /* --- TABEL --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: white;
            table-layout: fixed; /* Agar kolom rapi */
        }
        
        th, td {
            border: 1px solid #d1d5db; /* Border abu halus */
            padding: 8px 6px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        /* HEADER TABEL */
        th {
            background-color: #1e3a8a; /* Royal Blue */
            color: white;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            padding: 10px 6px;
            text-align: center;
        }

        /* Zebra Striping */
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* PENGATURAN LEBAR KOLOM */
        .col-no { width: 5%; text-align: center; }
        .col-nama { width: 20%; text-align: left; }
        .col-tanggal { width: 15%; text-align: center; }
        .col-waktu { width: 10%; text-align: center; }
        .col-durasi { width: 12%; text-align: center; }
        .col-ket { width: 28%; text-align: left; }

        /* TYPOGRAPHY */
        .name-text { 
            font-weight: bold; 
            color: #1e3a8a; 
            font-size: 11px;
        }
        .date-text { font-weight: bold; color: #444; }
        .durasi-text { font-weight: bold; color: #1e3a8a; }

        /* FOOTER */
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 8px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header-doc">
        <h1>Laporan Lembur Karyawan</h1>
        <p>PT RAKHA NUSANTARA MEDIKA</p>
        <p>Periode: <strong>{{ $dateForDays->isoFormat('MMMM YYYY') }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-nama" style="padding-left: 10px;">Karyawan</th>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-waktu">Mulai</th>
                <th class="col-waktu">Selesai</th>
                <th class="col-durasi">Durasi</th>
                <th class="col-ket">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lemburRecords as $index => $record)
                @php
                    $jamMasuk = $record->jam_masuk_lembur ? \Carbon\Carbon::parse($record->jam_masuk_lembur) : null;
                    $jamKeluar = $record->jam_keluar_lembur ? \Carbon\Carbon::parse($record->jam_keluar_lembur) : null;
                    $durasiLembur = '-';

                    if ($jamMasuk && $jamKeluar) {
                        $diff = $jamKeluar->diff($jamMasuk);
                        // Format: 2 Jam 30 Menit (atau 45 Menit jika 0 jam)
                        $parts = [];
                        if ($diff->h > 0) $parts[] = "{$diff->h} Jam";
                        if ($diff->i > 0) $parts[] = "{$diff->i} Menit";
                        $durasiLembur = implode(' ', $parts);
                    }
                @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="padding-left: 10px;">
                    <span class="name-text">{{ $record->user->name ?? 'User Dihapus' }}</span>
                </td>
                <td style="text-align: center;">
                    <span class="date-text">{{ \Carbon\Carbon::parse($record->tanggal)->isoFormat('D MMMM YYYY') }}</span>
                </td>
                <td style="text-align: center;">{{ $jamMasuk ? $jamMasuk->format('H:i') : '-' }}</td>
                <td style="text-align: center;">{{ $jamKeluar ? $jamKeluar->format('H:i') : '-' }}</td>
                <td style="text-align: center;">
                    <span class="durasi-text">{{ $durasiLembur }}</span>
                </td>
                <td>{{ $record->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; font-style: italic; color: #666;">
                    Tidak ada data lembur untuk periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh sistem pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>