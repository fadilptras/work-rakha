<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pengajuan Barang</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 25px; }
        .header h1 { margin: 0; font-size: 22px; color: #003366; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .filter-info {
            border: 1px solid #e3e3e3;
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .text-center { text-align: center; }
        /* Style untuk status */
        .status { padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; white-space: nowrap; }
        .status-selesai { background-color: #e6fffa; color: #38a169; }
        .status-ditolak { background-color: #fed7d7; color: #c53030; }
        .status-proses { background-color: #ebf8ff; color: #3182ce; }
        .status-dibatalkan { background-color: #e2e8f0; color: #4a5568; }
        .status-diajukan { background-color: #fefcbf; color: #d69e2e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT RAKHA NUSANTARA MEDIKA</h1>
            <p>Rekap Laporan Pengajuan Barang</p>
        </div>

        <div class="filter-info">
            <strong>Filter Data:</strong><br>
            - Nama Karyawan: <strong>{{ $karyawanName }}</strong> <br>
            - Divisi: <strong>{{ $divisiName }}</strong> <br>
            - Periode: <strong>{{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') : 'Awal' }}</strong> s/d <strong>{{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') : 'Akhir' }}</strong>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Tanggal</th>
                    <th>Nama Karyawan</th>
                    <th style="width: 35%;">Judul Pengajuan</th>
                    <th style="width: 10%;" class="text-center">Total Item</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuanBarangs as $pengajuan)
                    <tr>
                        <td>{{ $pengajuan->created_at->format('d M Y') }}</td>
                        <td>{{ $pengajuan->user->name }}</td>
                        <td>{{ $pengajuan->judul_pengajuan }}</td>
                        <td class="text-center">{{ count($pengajuan->rincian_barang ?? []) }} Item</td>
                        <td>
                            @if ($pengajuan->status == 'selesai')
                                <span class="status status-selesai">Selesai</span>
                            @elseif ($pengajuan->status == 'ditolak')
                                <span class="status status-ditolak">Ditolak</span>
                            @elseif ($pengajuan->status == 'diproses')
                                <span class="status status-proses">Diproses Gudang</span>
                            @elseif ($pengajuan->status == 'dibatalkan')
                                <span class="status status-dibatalkan">Dibatalkan</span>
                            @else
                                <span class="status status-diajukan">Menunggu Atasan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">
                            Tidak ada data untuk ditampilkan berdasarkan filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>