<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>FORM PENILAIAN KINERJA KARYAWAN (UMUM)</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; font-weight: bold; }
        .header h2 { margin: 0 0 5px; font-size: 14px; }
        .header h3 { margin: 0; font-size: 12px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        
        .bg-gray { background-color: #e2e8f0; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .info-table th, .info-table td { border: 1px solid #000; padding: 4px 6px; }
        .info-table th { width: 25%; text-align: left; background-color: white;}
        .info-table td { width: 25%; }
        
        .signature-table { border: none; margin-top: 30px; }
        .signature-table th, .signature-table td { border: none; text-align: center; padding: 10px; }
        .signature-line { margin-top: 60px; border-bottom: 1px solid #000; width: 80%; display: inline-block; }
    </style>
</head>
<body>
    <div class="header">
        <h2>FORM PENILAIAN KINERJA KARYAWAN</h2>
        <h3>PT. RAKHA NUSANTARA MEDIKA</h3>
    </div>

    <table class="info-table">
        <tr>
            <th>Divisi</th>
            <td>{{ ucfirst($evaluation->user->divisi ?? '-') }}</td>
            <th>Tgl. Berlaku</th>
            <td>{{ \Carbon\Carbon::parse($evaluation->evaluation_date)->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Periode</th>
            <td>{{ $evaluation->period }}</td>
            <th rowspan="2">Diketahui Atasan Langsung</th>
            <td rowspan="2" class="text-center">V</td>
        </tr>
        <tr>
            <th>Nama</th>
            <td>{{ $evaluation->user->name }}</td>
        </tr>
    </table>

    <table style="margin-top: -10px;">
        <tr class="bg-gray">
            <td colspan="5">1. PENILAIAN KINERJA (60%)</td>
        </tr>
        <tr>
            <th width="5%">No</th>
            <th width="35%">Kualitas / Kuantitas / Waktu</th>
            <th width="20%">Indeks (1-5)</th>
            <th width="20%">Bobot</th>
            <th width="20%">Nilai Akhir</th>
        </tr>
        @php 
            $kinerja = $evaluation->items->filter(function($item) {
                return $item->indicator->category == 'kinerja';
            });
            $totalKinerja = 0;
            $no = 1;
        @endphp
        @foreach($kinerja as $item)
            @php $totalKinerja += $item->final_score; @endphp
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $item->indicator->name }}</td>
                <td class="text-center">{{ $item->result_index }}</td>
                <td class="text-center">{{ (float)$item->indicator->weight_percentage }}%</td>
                <td class="text-center">{{ $item->final_score }}</td>
            </tr>
        @endforeach
        <tr class="bg-gray">
            <td colspan="4" class="text-right">Total Kinerja (100% dari 60%)</td>
            <td class="text-center">{{ number_format($totalKinerja, 2) }}</td>
        </tr>
    </table>

    <table>
        <tr class="bg-gray">
            <td colspan="5">2. PENILAIAN PERILAKU (20%)</td>
        </tr>
        <tr>
            <th width="5%">No</th>
            <th width="35%">Aspek Perilaku</th>
            <th width="20%">Indeks (1-4)</th>
            <th width="20%">Bobot</th>
            <th width="20%">Nilai Akhir</th>
        </tr>
        @php 
            $perilaku = $evaluation->items->filter(function($item) {
                return in_array($item->indicator->category, ['perilaku_terbaik', 'perilaku_profesional', 'perilaku_peduli']);
            });
            $totalPerilaku = 0;
            $countPerilaku = 0;
            $no = 1;
        @endphp
        @foreach($perilaku as $item)
            @php 
                $totalPerilaku += $item->result_index;
                $countPerilaku++;
            @endphp
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $item->indicator->name }}</td>
                <td class="text-center">{{ $item->result_index }}</td>
                <td class="text-center">{{ (float)$item->indicator->weight_percentage }}%</td>
                <td class="text-center">{{ $item->result_index }}</td>
            </tr>
        @endforeach
        <tr class="bg-gray">
            <td colspan="4" class="text-right">Rata-rata Perilaku</td>
            <td class="text-center">{{ $countPerilaku > 0 ? number_format($totalPerilaku/$countPerilaku, 2) : 0 }}</td>
        </tr>
    </table>

    <table>
        <tr class="bg-gray">
            <td colspan="4">3. PENILAIAN KEHADIRAN (20%)</td>
        </tr>
        <tr>
            <th width="5%">No</th>
            <th width="45%">Aspek Kehadiran</th>
            <th width="25%">Indeks (1-4)</th>
            <th width="25%">Nilai Akhir</th>
        </tr>
        @php 
            $kehadiran = $evaluation->items->filter(function($item) {
                return $item->indicator->category == 'kehadiran';
            });
            $totalKehadiran = 0;
            $countKehadiran = 0;
            $no = 1;
        @endphp
        @foreach($kehadiran as $item)
            @php 
                $totalKehadiran += $item->result_index;
                $countKehadiran++;
            @endphp
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $item->indicator->name }}</td>
                <td class="text-center">{{ $item->result_index }}</td>
                <td class="text-center">{{ $item->result_index }}</td>
            </tr>
        @endforeach
    </table>

    <table style="width: 50%; margin-left: 50%;">
        <tr class="bg-gray">
            <th width="50%">TOTAL SKOR AKHIR</th>
            <th width="50%">{{ $evaluation->total_score }}</th>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            <td width="33%">
                <strong>PENILAI</strong><br>
                (Atasan Langsung)<br><br><br>
                <span class="signature-line"></span><br>
                {{ $evaluation->evaluator->name ?? '_____________________' }}
            </td>
            <td width="33%">
                <strong>MENYETUJUI</strong><br>
                (Direktur)<br><br><br>
                <span class="signature-line"></span><br>
                Tuah Maujana S
            </td>
            <td width="33%">
                <strong>MENGETAHUI</strong><br>
                (Karyawan Ybs)<br><br><br>
                <span class="signature-line"></span><br>
                {{ $evaluation->user->name }}
            </td>
        </tr>
    </table>
    
    <div style="text-align: right; font-size: 10px; margin-top: 20px;">
        FORM-HR-05-002/Rev. 01
    </div>
</body>
</html>
