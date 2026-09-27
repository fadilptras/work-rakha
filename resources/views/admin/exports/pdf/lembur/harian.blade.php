@extends('admin.exports.pdf.layout')

@section('judul-dokumen', 'Laporan Lembur Karyawan')
@section('kop-judul', 'Laporan Lembur Karyawan')
@section('kop-periode', $dateLabel)
@section('kop-info', '')

@section('gaya-tambahan')
<style>
    /* KHUSUS LEMBUR HARIAN */
    table.harian-table { table-layout: fixed; }
    /* zebra diambil dari style bersama (table.report) */

    .col-no { width: 5%; text-align: center; }
    .col-nama { width: 20%; text-align: left; }
    .col-tanggal { width: 15%; text-align: center; }
    .col-waktu { width: 10%; text-align: center; }
    .col-durasi { width: 12%; text-align: center; }
    .col-ket { width: 28%; text-align: left; }

    .date-text { font-weight: bold; color: #444; }
    .durasi-text { font-weight: bold; color: #1e3a8a; }
</style>
@endsection

@section('konten')
    <table class="report harian-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-nama" style="text-align: left; padding-left: 10px;">Karyawan</th>
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

                    if ($jamMasuk && $jamKeluar && !$jamKeluar->lt($jamMasuk)) {
                        $totalMenit = $jamMasuk->diffInMinutes($jamKeluar);
                        $durasiLembur = floor($totalMenit / 60) . ' Jam ' . ($totalMenit % 60) . ' Menit';
                        if (floor($totalMenit / 60) == 0) $durasiLembur = ($totalMenit % 60) . ' Menit';
                    } elseif ($jamMasuk && !$jamKeluar) {
                        $durasiLembur = 'Tidak Absen Keluar';
                    } elseif ($jamMasuk && $jamKeluar) {
                        $durasiLembur = 'Cek jam keluar';
                    }
                @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="padding-left: 10px;">
                    @include('admin.exports.pdf.components.employee-cell', ['nama' => $record->user->name ?? 'User Dihapus', 'sub' => $record->user->divisi ?? '-'])
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
@endsection
