@extends('admin.exports.pdf.layout')

@section('judul-dokumen', 'Laporan Absensi Harian')
@section('kop-judul', 'Laporan Absensi Harian')
@section('kop-periode', \Carbon\Carbon::parse($date_for_page)->isoFormat('dddd, D MMMM YYYY'))
@section('kop-info', '')

@section('gaya-tambahan')
<style>
    /* KHUSUS ABSENSI HARIAN */
    table.harian-table { table-layout: fixed; }
    /* zebra diambil dari style bersama (table.report) */

    .col-no { width: 5%; text-align: center; }
    .col-nama { width: 25%; }
    .col-waktu { width: 13%; text-align: center; }
    .col-durasi { width: 12%; text-align: center; }
    .col-status { width: 15%; text-align: center; }
    .col-ket { width: 17%; }

    .date-text { font-weight: bold; font-size: 9px; color: #334155; display: block; margin-bottom: 2px; }
    .time-text { font-size: 10px; color: #0f172a; font-weight: bold; }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 50px;
        font-weight: bold;
        font-size: 8px;
        text-transform: capitalize;
    }
    .bg-green { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .bg-red { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .bg-amber { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .bg-purple { background-color: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
    .bg-gray { background-color: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
</style>
@endsection

@section('konten')
    <table class="report harian-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-nama" style="text-align: left; padding-left: 10px;">Karyawan</th>
                <th class="col-waktu">Waktu Masuk</th>
                <th class="col-waktu">Waktu Keluar</th>
                <th class="col-durasi">Durasi</th>
                <th class="col-status">Status</th>
                <th class="col-ket">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($absensi_harian as $index => $record)
                @php
                    $tglMasukStr = $record->tanggal;
                    $tglKeluarStr = $record->tanggal_keluar ?? $record->tanggal;

                    $waktuMasuk = $record->jam_masuk
                        ? \Carbon\Carbon::parse($tglMasukStr . ' ' . $record->jam_masuk)
                        : null;

                    $waktuKeluar = $record->jam_keluar
                        ? \Carbon\Carbon::parse($tglKeluarStr . ' ' . $record->jam_keluar)
                        : null;

                    $durasiKerja = '-';

                    if ($waktuMasuk && $waktuKeluar) {
                        // Data lama tanpa tanggal_keluar yang lembur lewat tengah malam: tambah 1 hari.
                        if (is_null($record->tanggal_keluar) && $waktuKeluar->lt($waktuMasuk)) {
                            $waktuKeluar->addDay();
                        }

                        $totalMenit = $waktuMasuk->diffInMinutes($waktuKeluar);
                        $durasiKerja = floor($totalMenit / 60) . " Jam " . ($totalMenit % 60) . " Menit";
                    }

                    $statusText = ucfirst($record->status);
                    $badgeClass = 'bg-gray';

                    if ($record->status == 'hadir') {
                        $batasWaktu = '08:00:00';
                        $jamMasukOnly = $record->jam_masuk;

                        if ($jamMasukOnly && $jamMasukOnly > $batasWaktu) {
                            $statusText = 'Hadir (Terlambat)';
                            $badgeClass = 'bg-green';
                        } else {
                            $statusText = 'Hadir';
                            $badgeClass = 'bg-green';
                        }
                    } elseif ($record->status == 'sakit') {
                        $badgeClass = 'bg-red';
                    } elseif ($record->status == 'izin') {
                        $badgeClass = 'bg-amber';
                    } elseif ($record->status == 'cuti') {
                        $badgeClass = 'bg-purple';
                    } elseif ($record->status == 'tidak hadir') {
                        $badgeClass = 'bg-gray';
                    }

                    $tglMasukFmt = \Carbon\Carbon::parse($record->tanggal)->isoFormat('D MMM Y');
                    $tglKeluarFmt = $waktuKeluar
                                ? $waktuKeluar->isoFormat('D MMM Y')
                                : '-';
                @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="padding-left: 10px;">
                    @include('admin.exports.pdf.components.employee-cell', ['nama' => $record->user->name ?? 'User Dihapus', 'sub' => $record->user->divisi ?? '-'])
                </td>
                <td style="text-align: center;">
                    @if($waktuMasuk)
                        <span class="date-text">{{ $tglMasukFmt }}</span>
                        <span class="time-text">{{ $waktuMasuk->format('H:i') }} WIB</span>
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: center;">
                    @if($waktuKeluar)
                        <span class="date-text">{{ $tglKeluarFmt }}</span>
                        <span class="time-text">{{ $waktuKeluar->format('H:i') }} WIB</span>
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: center; font-weight: bold; color: #444;">
                    {{ $durasiKerja }}
                </td>
                <td style="text-align: center;">
                    <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                </td>
                <td>{{ $record->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; font-style: italic; color: #666;">
                    Tidak ada data absensi untuk tanggal ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
