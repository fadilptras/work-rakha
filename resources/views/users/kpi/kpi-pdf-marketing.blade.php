<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KPI Evaluasi - {{ $evaluation->user->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header { margin-bottom: 20px; }
        .logo-container { text-align: center; margin-bottom: 15px; }
        .company-title { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 20px; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px 0; }
        .info-label { width: 100px; font-weight: bold; }
        
        .section-title { font-size: 12px; font-weight: bold; margin-bottom: 8px; text-transform: uppercase; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px; }
        table.data-table th { background-color: #8daed6; font-weight: bold; text-align: center; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .keterangan-nilai { font-size: 10px; margin-top: 10px; line-height: 1.3; }
        .keterangan-box { background-color: #fbc02d; font-weight: bold; padding: 2px 5px; display: inline-block; margin-bottom: 3px; }
        
        .notes-section { border: 1px solid #000; padding: 10px; margin-bottom: 20px; min-height: 50px; }
        .notes-title { font-weight: bold; margin-bottom: 5px; }
        
        .signature-table { width: 100%; margin-top: 40px; text-align: center; }
        .signature-table td { width: 50%; padding-top: 60px; }
        .signature-line { border-top: 1px solid #000; width: 150px; margin: 0 auto; margin-bottom: 5px; }
    </style>
</head>
<body>

    @include('pdf.partials.kop-surat')

    <div class="company-title">
        FORM KEY PERFORMANCE INDICATORS<br>
        PT RAKHA NUSANTARA MEDIKA
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">NAMA</td>
            <td>: {{ $evaluation->user->name }}</td>
        </tr>
        <tr>
            <td class="info-label">JABATAN</td>
            <td>: {{ $evaluation->user->jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">TANGGAL</td>
            <td>: {{ \Carbon\Carbon::parse($evaluation->evaluation_date)->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <div class="section-title">A. PENILAIAN KINERJA</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="20%">Nama KPI</th>
                <th width="30%">Definisi</th>
                <th width="10%">Target</th>
                <th width="10%">Achievment</th>
                <th width="10%">Hasil</th>
                <th width="5%">Evaluasi Penilaian</th>
                <th width="5%">Bobot</th>
                <th width="5%">Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $kinerjaItems = $evaluation->items->filter(function($item) {
                    return $item->indicator->category === 'kinerja';
                });
            @endphp
            
            @forelse($kinerjaItems as $index => $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->indicator->name }}</td>
                <td>{{ $item->indicator->definition }}</td>
                <td class="text-center">{{ $item->target_value ?? $item->indicator->target }}</td>
                <td class="text-center">{{ $item->achievement_value }}</td>
                <td class="text-center">{{ $item->hasil_value }}</td>
                <td class="text-center">{{ $item->result_index }}</td>
                <td class="text-center">{{ (float)$item->indicator->weight_percentage }}%</td>
                <td class="text-center font-bold">{{ $item->final_score }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data indikator kinerja.</td>
            </tr>
            @endforelse
            <tr>
                <td colspan="7" class="text-right font-bold">TOTAL</td>
                <td class="text-center font-bold">100%</td>
                <td class="text-center font-bold">{{ $evaluation->total_score ?? '0.00' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="keterangan-nilai">
        <div class="keterangan-box">Keterangan Nilai:</div><br>
        5 = 90 &le; x &le; 100<br>
        4 = 80 &le; x &lt; 90<br>
        3 = 60 &le; x &lt; 80<br>
        2 = 50 &le; x &lt; 60<br>
        1 = &lt; 50
    </div>

    <div style="margin-top: 30px;">
        <div class="notes-section">
            <div class="notes-title">Catatan Evaluasi:</div>
            {{ $evaluation->evaluation_notes ?? '-' }}
        </div>
        
        <div class="notes-section">
            <div class="notes-title">Rencana Tindak Lanjut:</div>
            {{ $evaluation->action_plan ?? '-' }}
        </div>
    </div>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-line"></div>
                Atasan Langsung
            </td>
            <td>
                <div class="signature-line"></div>
                Karyawan Ybs.
            </td>
        </tr>
    </table>

</body>
</html>
