@extends('admin.exports.pdf.layout')

@section('judul-dokumen', 'Form Rekap Lembur')
@section('kop-judul', 'Form Rekap Lembur')
@section('kop-periode', \Carbon\Carbon::create($year, $month, 1)->isoFormat('MMMM YYYY'))
@section('kop-info', 'Divisi: ' . ($divisi ?? 'Semua Divisi') . (!empty($karyawanNama) ? ' | Karyawan: ' . $karyawanNama : ''))

@section('gaya-tambahan')
<style>
    /* KHUSUS RINCIAN MINGGUAN LEMBUR */
    .col-hari { width: 9%; text-align: center; }
    .col-jam { width: 16%; text-align: center; }

    .total-hari { font-weight: 800; color: #6b21a8; text-align: center; font-size: 11.5px; }
    .total-jam { font-weight: bold; color: #1e3a8a; text-align: center; font-size: 11px; }

    .jam-text { display: block; font-weight: bold; color: #6b21a8; font-size: 10.5px; }
    .durasi-text { display: block; color: #475569; font-size: 9px; }
    .warn-text { display: block; font-weight: bold; font-size: 9px; }
    td.cell-ok { background-color: #f5f0ff !important; }
    td.cell-incomplete { background-color: #fef3c7 !important; }
    td.cell-invalid { background-color: #fee2e2 !important; }

    .week-notes { font-size: 9px; color: #374151; margin: 0 0 10px 0; line-height: 1.5; }
    .week-notes-title { font-weight: bold; color: #1e3a8a; }
    .week-notes-list { margin: 2px 0 0 16px; padding: 0; color: #4b5563; }
    .week-notes-list li { margin-bottom: 1px; }

    .tgl-chip {
        display: inline-block;
        border: 1px solid #a78bfa;
        background-color: #f5f0ff;
        color: #5b21b6;
        font-weight: bold;
        font-size: 9px;
        border-radius: 4px;
        padding: 1px 5px;
        margin: 1px 2px 1px 0;
    }
    th.th-blank { background-color: #9ca3af !important; }
    td.cell-blank { background-color: #f3f4f6 !important; }
    tr.total-row td {
        font-weight: 800;
        background-color: #ede9fe !important;
        color: #1e3a8a;
        font-size: 10px;
    }
    .ket-ok { color: #9ca3af; }
    .ket-warn { font-weight: bold; color: #b45309; font-size: 9px; }
    .ket-bad { font-weight: bold; color: #b91c1c; font-size: 9px; }
</style>
@endsection

@section('konten')
    <div class="section-title">A. Rekap Lembur Sebulan</div>
    <table class="report week-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-nama" style="text-align: left; padding-left: 10px;">Karyawan</th>
                <th style="text-align: left; padding-left: 10px;">Tanggal Lembur</th>
                <th class="col-hari">Hari</th>
                <th class="col-jam">Total Jam</th>
                <th style="text-align: left; padding-left: 10px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $grandHari = 0; $grandMenit = 0; @endphp
            @forelse ($rekapData as $i => $data)
            @php
                $tglLembur = [];
                $takKeluar = [];
                $cekKeluar = [];
                foreach ($allDates as $dt) {
                    $c = $data['daily'][$dt->toDateString()] ?? null;
                    if (empty($c)) continue;
                    $tglLembur[] = $dt->isoFormat('D MMM');
                    if (($c['status'] ?? '') === 'incomplete') $takKeluar[] = $dt->isoFormat('D MMM');
                    if (($c['status'] ?? '') === 'invalid') $cekKeluar[] = $dt->isoFormat('D MMM');
                }
                $grandHari += $data['summary']['total_hari'];
                $grandMenit += $data['summary']['total_menit'];
            @endphp
            <tr>
                <td class="col-no">{{ $i + 1 }}</td>
                <td class="col-nama" style="padding-left: 10px;">
                    @include('admin.exports.pdf.components.employee-cell', ['nama' => $data['user']->name ?? 'User Dihapus', 'sub' => $data['user']->divisi ?? '-'])
                </td>
                <td style="padding-left: 10px;">
                    @if(empty($tglLembur))
                        <span class="ket-ok">-</span>
                    @else
                        @foreach($tglLembur as $tgl)<span class="tgl-chip">{{ $tgl }}</span>@endforeach
                    @endif
                </td>
                <td class="total-hari">{{ $data['summary']['total_hari'] }}</td>
                <td class="total-jam">{{ $data['summary']['total_formatted'] }}</td>
                <td style="padding-left: 10px;">
                    @if(!empty($takKeluar) || !empty($cekKeluar))
                        @if(!empty($takKeluar))
                            <span class="ket-warn">Tidak Absen Keluar: {{ implode(', ', $takKeluar) }}</span><br>
                        @endif
                        @if(!empty($cekKeluar))
                            <span class="ket-bad">Cek Jam Keluar: {{ implode(', ', $cekKeluar) }}</span>
                        @endif
                    @else
                        <span class="ket-ok">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 14px; font-style: italic; color: #666;">
                    Tidak ada data lembur pada periode ini.
                </td>
            </tr>
            @endforelse
            @if(count($rekapData) > 0)
            <tr class="total-row">
                <td colspan="3" style="text-align: right; padding-right: 10px;">TOTAL</td>
                <td class="total-hari">{{ $grandHari }}</td>
                <td class="total-jam">{{ floor($grandMenit / 60) }} Jam {{ $grandMenit % 60 }} Menit</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">B. Rincian Lembur per Minggu</div>
    @foreach ($weeks as $week)
        <div class="week-title">{{ $week['label'] }}</div>
        @if (count($week['rows']) === 0)
            <p class="empty-note">Tidak ada lembur pada minggu ini ({{ $week['label'] }}).</p>
        @else
        <table class="report week-table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nama" style="text-align: left; padding-left: 10px;">Karyawan</th>
                    @foreach ($week['dates7'] as $d)
                        @if(is_null($d))
                            <th class="th-blank"></th>
                        @else
                            @include('admin.exports.pdf.components.date-header', ['tanggal' => $d, 'holidays' => $holidays ?? []])
                        @endif
                    @endforeach
                    <th class="col-jam">Total Minggu Ini</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($week['rows'] as $i => $row)
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-nama" style="padding-left: 10px;">
                        @include('admin.exports.pdf.components.employee-cell', ['nama' => $row['user']->name ?? 'User Dihapus', 'sub' => $row['user']->divisi ?? '-'])
                    </td>
                    @foreach ($week['dates7'] as $d)
                        @php $cell = is_null($d) ? 'blank' : ($row['cells'][$d->format('Y-m-d')] ?? null); @endphp
                        @if ($cell === 'blank')
                            <td class="cell-blank"></td>
                        @elseif (!$cell)
                            <td class="cell-kosong">-</td>
                        @elseif ($cell['status'] === 'ok')
                            <td class="cell-ok" style="text-align: center;">
                                <span class="jam-text">{{ $cell['label'] }}</span>
                                <span class="durasi-text">{{ $cell['durasi_full'] ?? $cell['durasi'] }}</span>
                            </td>
                        @elseif ($cell['status'] === 'incomplete')
                            <td class="cell-incomplete" style="text-align: center;">
                                <span class="jam-text" style="color: #b45309;">{{ $cell['label'] }}</span>
                                <span class="warn-text" style="color: #b45309;">{{ $cell['durasi_full'] ?? $cell['durasi'] }}</span>
                            </td>
                        @else
                            <td class="cell-invalid" style="text-align: center;">
                                <span class="jam-text" style="color: #b91c1c;">{{ $cell['label'] }}</span>
                                <span class="warn-text" style="color: #b91c1c;">{{ $cell['durasi_full'] ?? $cell['durasi'] }}</span>
                            </td>
                        @endif
                    @endforeach
                    <td class="total-jam">{{ $row['total_hari'] }} hari &bull; {{ $row['total_formatted'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if (count($week['notes'] ?? []) > 0)
        <div class="week-notes">
            <span class="week-notes-title">Keterangan {{ $week['label'] }}:</span>
            <ul class="week-notes-list">
                @foreach ($week['notes'] as $note)
                    <li>{{ $note['tanggal']->isoFormat('ddd, D MMM') }} &ndash; {{ $note['user_name'] }}: {{ $note['keterangan'] }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        @endif
    @endforeach
@endsection

@section('nomor-dokumen', 'FORM-HR-04-003')
