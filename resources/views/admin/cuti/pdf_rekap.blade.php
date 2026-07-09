<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pengajuan Cuti</title>
    <style>
        /* [STYLE DIADOPSI DARI PENGAJUAN DANA] */
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 25px; }
        .header h1 { margin: 0; font-size: 22px; color: #003366; } /* Ukuran font disamakan 22px */
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; word-wrap: break-word; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; }
        
        /* Style Box Filter yang sama */
        .filter-info {
            border: 1px solid #e3e3e3;
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Style Status (Konsisten dengan Dana/Barang) */
        .status { padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; white-space: nowrap; }
        .status-selesai { background-color: #e6fffa; color: #38a169; } /* Hijau (Disetujui) */
        .status-ditolak { background-color: #fed7d7; color: #c53030; } /* Merah (Ditolak) */
        .status-proses { background-color: #ebf8ff; color: #3182ce; }   /* Biru */
        .status-dibatalkan { background-color: #e2e8f0; color: #4a5568; } /* Abu */
        .status-diajukan { background-color: #fefcbf; color: #d69e2e; } /* Kuning (Diajukan) */
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT RAKHA NUSANTARA MEDIKA</h1>
            <p>Laporan Rekapitulasi Pengajuan Cuti</p>
        </div>

        <div class="filter-info">
            <strong>Filter Data:</strong><br>
            - Status Tab: <strong>{{ strtoupper($activeTab == 'all' ? 'Semua Data' : $activeTab) }}</strong> <br>
            - Karyawan: <strong>{{ $userName }}</strong> <br>
            - Periode: <strong>{{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') : 'Awal' }}</strong> 
            s/d <strong>{{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') : 'Akhir' }}</strong>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No</th>
                    <th style="width: 12%;">Tanggal Input</th>
                    <th style="width: 20%;">Nama Karyawan</th>
                    <th style="width: 13%;">Jenis Cuti</th>
                    <th style="width: 25%;">Detail Tanggal Cuti</th>
                    <th style="width: 10%;" class="text-center">Durasi</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cutiRequests as $cuti)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $cuti->created_at->format('d M Y') }}</td>
                        <td>
                            <strong>{{ $cuti->user->name }}</strong><br>
                            <span style="font-size: 9px; color: #666;">{{ $cuti->user->divisi ?? '-' }}</span>
                        </td>
                        <td style="text-transform: capitalize;">{{ $cuti->jenis_cuti }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} s/d <br>
                            {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                        </td>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($cuti->tanggal_selesai)) + 1 }} Hari
                        </td>
                        <td>
                            {{-- Mapping Status Cuti ke Class Style Dana --}}
                            @if(in_array($cuti->status, ['disetujui', 'diterima']))
                                <span class="status status-selesai">Disetujui</span>
                            @elseif($cuti->status == 'ditolak')
                                <span class="status status-ditolak">Ditolak</span>
                            @elseif($cuti->status == 'dibatalkan')
                                <span class="status status-dibatalkan">Dibatalkan</span>
                            @else
                                <span class="status status-diajukan">Menunggu Persetujuan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">
                            Tidak ada data cuti yang ditemukan berdasarkan filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>