<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Sisa Cuti Karyawan</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #003366; text-transform: uppercase; }
        .header p { margin: 5px 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .danger { color: #c53030; font-weight: bold; }
        .safe { color: #047857; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9px; color: #777; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT RAKHA NUSANTARA MEDIKA</h1>
        <p>Laporan Status Jatah & Sisa Cuti Karyawan</p>
        <p>Periode Tahun: <strong>{{ $tahun }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 35%">Nama Karyawan</th>
                <th style="width: 20%">Jabatan / Divisi</th>
                <th style="width: 13%">Jatah Tahunan</th>
                <th style="width: 13%">Terpakai</th>
                <th style="width: 14%">Sisa Cuti</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->jabatan ?? '-' }} {{ $user->divisi ? '('.$user->divisi.')' : '' }}</td>
                    <td class="text-center">{{ $user->jatah_cuti ?? 0 }}</td>
                    <td class="text-center">{{ $user->cuti_terpakai }}</td>
                    <td class="text-center">
                        <span class="{{ $user->sisa_cuti < 0 ? 'danger' : 'safe' }}">
                            {{ $user->sisa_cuti }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Belum ada data karyawan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}
    </div>
</body>
</html>