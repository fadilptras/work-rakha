@extends('admin.exports.pdf.layout')

@section('judul-dokumen', 'Form Rekap Absen')
@section('margin-pdf', '5mm 5mm')
@section('kop-judul', 'Form Rekap Absen')
@section('kop-periode', \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') . ' - ' . \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY'))
@section('kop-info', 'Divisi: ' . ($divisi ?: 'Semua Divisi'))

@section('gaya-tambahan')
<style>
    /* KHUSUS REKAP ABSENSI */
    table.sum-table thead th { font-size: 10px; padding: 7px 6px; }
    table.sum-table tbody td { font-size: 11px; }

    .col-stat { width: 6%; text-align: center; font-weight: 800; }
    .col-telat { width: 18%; text-align: center; }

    .status-text { display: block; font-weight: 800; font-size: 12px; line-height: 1.2; }
    .jam-text { display: block; color: #475569; font-size: 9px; line-height: 1.3; }
    .late-text { display: block; font-size: 9px; font-weight: bold; color: #b91c1c; line-height: 1.3; }

    .st-h { color: #0f5132; } .st-s { color: #842029; } .st-i { color: #664d03; }
    .st-c { color: #084298; } .st-a { color: #41464b; } .st-dash { color: #9ca3af; }

    .telat-total { font-weight: bold; color: #b91c1c; font-size: 11px; text-align: center; }
    .telat-count { display: block; font-size: 8.5px; font-weight: normal; color: #b91c1c; }

    th.th-blank { background-color: #9ca3af !important; }
    td.cell-blank { background-color: #f3f4f6 !important; }
</style>
@endsection

@section('konten')
    <div class="section-title">A. Ringkasan Kehadiran per Karyawan</div>
    <table class="report sum-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-nama" style="text-align: left; padding-left: 10px;">Karyawan</th>
                <th class="col-stat" style="color:#bbf7d0;">H</th>
                <th class="col-stat" style="color:#fecaca;">S</th>
                <th class="col-stat" style="color:#fde68a;">I</th>
                <th class="col-stat" style="color:#bfdbfe;">C</th>
                <th class="col-stat" style="color:#e5e7eb;">A</th>
                <th class="col-telat">Total Terlambat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapData as $i => $data)
            <tr>
                <td class="col-no">{{ $i + 1 }}</td>
                <td class="col-nama" style="padding-left: 10px;">
                    @include('admin.exports.pdf.components.employee-cell', ['nama' => $data['user']->name ?? 'User Dihapus', 'sub' => $data['user']->jabatan ?? $data['user']->divisi ?? '-'])
                </td>
                <td class="col-stat" style="color:#166534;">{{ $data['summary']['H'] }}</td>
                <td class="col-stat" style="color:#dc2626;">{{ $data['summary']['S'] }}</td>
                <td class="col-stat" style="color:#d97706;">{{ $data['summary']['I'] }}</td>
                <td class="col-stat" style="color:#2563eb;">{{ $data['summary']['C'] }}</td>
                <td class="col-stat" style="color:#374151;">{{ $data['summary']['A'] }}</td>
                <td class="telat-total">
                    {{ $data['summary']['terlambat_formatted'] }}
                    @if(($data['summary']['terlambat_count'] ?? 0) > 0)
                        <span class="telat-count">{{ $data['summary']['terlambat_count'] }} kali telat</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 14px; font-style: italic; color: #666;">
                    Data tidak ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">B. Rincian Kehadiran per Minggu</div>
    @foreach ($weeks as $week)
        <div class="week-title">{{ $week['label'] }}</div>
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
                    <th class="col-telat">Telat Minggu Ini</th>
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
                        @if(is_null($d))
                            <td class="cell-blank"></td>
                        @else
                        @php
                            $cell = $row['cells'][$d->format('Y-m-d')];
                            $st = $cell['status'];
                            $stClass = $st === 'H' ? 'st-h' : ($st === 'S' ? 'st-s' : ($st === 'I' ? 'st-i' : ($st === 'C' ? 'st-c' : ($st === 'A' ? 'st-a' : 'st-dash'))));
                            $jamTeks = null;
                            if (!empty($cell['time']) && ($cell['time']['masuk'] || $cell['time']['keluar'])) {
                                $jamTeks = str_replace(':', '.', $cell['time']['masuk'] ?? '?') . ' - ' . str_replace(':', '.', $cell['time']['keluar'] ?? '?');
                            }
                        @endphp
                        <td style="text-align: center;">
                            <span class="status-text {{ $stClass }}">{{ $st }}</span>
                            @if($jamTeks)
                                <span class="jam-text">{{ $jamTeks }}</span>
                            @endif
                            @if(!empty($cell['late']))
                                <span class="late-text">Telat {{ $cell['late']['short'] }}</span>
                            @endif
                        </td>
                        @endif
                    @endforeach
                    <td class="telat-total">
                        @if($row['late_count'] > 0)
                            {{ $row['late_formatted'] }}
                            <span class="telat-count">{{ $row['late_count'] }} kali</span>
                        @else
                            <span style="color:#9ca3af;">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
@endsection

@section('nomor-dokumen', 'FORM-HR-04-002/Rev. 01')
