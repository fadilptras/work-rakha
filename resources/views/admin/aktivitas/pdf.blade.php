<!DOCTYPE html>
<html>
<head>
    <title>Laporan Aktivitas Harian</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #333; }
        
        /* Header Laporan */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; color: #1e3a8a; }
        .header p { margin: 2px 0; color: #555; font-size: 9pt; }
        
        /* Info Filter */
        .meta-info { margin-bottom: 15px; font-size: 10pt; }
        
        /* Tabel Data */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        
        /* Header Tabel Biru */
        th { 
            background-color: #2563eb; /* Blue-600 */
            color: #ffffff; 
            padding: 8px; 
            text-align: left; 
            font-weight: bold;
            font-size: 9pt;
            border: 1px solid #1e40af;
        }
        
        td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            font-size: 9pt;
            vertical-align: top;
        }
        
        /* Efek Zebra Khas Admin */
        tr:nth-child(even) { background-color: #f8fafc; }
        
        /* Badge Info Lampiran */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8pt;
            font-weight: bold;
            background-color: #e0f2fe;
            color: #0369a1;
            border-radius: 4px;
            margin-bottom: 3px;
        }
        .text-small { font-size: 8pt; color: #666; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Aktivitas Harian Karyawan</h2>
        <p>Sistem Manajemen Dashboard Admin Kelola-in</p>
    </div>

    <div class="meta-info">
        <table style="width: 100%; border: none; margin: 0;">
            <tr style="background-color: transparent;">
                <td style="border: none; padding: 2px; width: 80px;"><strong>Filter</strong></td>
                <td style="border: none; padding: 2px;">: {{ $filterInfo }}</td>
            </tr>
            <tr style="background-color: transparent;">
                <td style="border: none; padding: 2px; width: 80px;"><strong>Tanggal</strong></td>
                <td style="border: none; padding: 2px;">
                    : {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">No</th>
                <th style="width: 170px;">Nama / Divisi</th>
                <th>Aktivitas Yang Dilaporkan</th>
                <th style="width: 100px;">Lampiran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aktivitas as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>
                    <div class="text-bold">{{ $item->user->name ?? 'N/A' }}</div>
                    <div class="text-small">{{ $item->user->divisi ?? '-' }}</div>
                    {{-- FIXED: Menambahkan format hari dan tanggal sebelum jam menit --}}
                    <div class="text-small" style="color: #888; margin-top: 4px;">
                        {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('l, d F Y - H:i') }} WIB
                    </div>
                </td>
                <td>
                    <div class="text-bold">{{ $item->title ?? 'Tidak ada judul' }}</div>
                </td>
                <td>
                    @php $hasAttachment = false; @endphp

                    @if($item->lampiran)
                        <div class="badge">Ada Foto</div>
                        @php $hasAttachment = true; @endphp
                    @endif
                    
                    @if($item->latitude && $item->longitude)
                        <div class="badge">Ada Lokasi</div>
                        @php $hasAttachment = true; @endphp
                    @endif

                    @if(!$hasAttachment)
                        <span class="text-small">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 15px; color: #777;">
                    Tidak ada data aktivitas yang ditemukan pada tanggal ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right; font-size: 8pt; color: #777; border-top: 1px solid #ddd; padding-top: 5px;">
        Dicetak otomatis oleh sistem pada: {{ now()->translatedFormat('d F Y H:i') }} WIB
    </div>

</body>
</html>