@extends('admin.exports.pdf.layout')

@section('judul-dokumen', 'Laporan Aktivitas Karyawan')
@section('kop-judul', 'Laporan Aktivitas Karyawan')
@section('kop-periode', \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') . ' s/d ' . \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY'))
@section('kop-info', 'Filter: ' . ($filterInfo ?? 'Semua Karyawan'))

@section('gaya-tambahan')
<style>
    /* KHUSUS LAPORAN AKTIVITAS */
    .col-tanggal { width: 18%; text-align: center; }
    .col-nama { width: 24%; }

    .tanggal-text { display: block; font-weight: bold; font-size: 10px; }
    .jam-text { display: block; color: #64748b; font-size: 9px; }
    .aktivitas-text { font-size: 10px; }
    td.cell-kosong { text-align: center; color: #9ca3af; }
</style>
@endsection

@section('konten')
    <table class="report">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-nama" style="text-align: left; padding-left: 10px;">Nama &amp; Divisi</th>
                <th style="text-align: left; padding-left: 10px;">Aktivitas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($aktivitas as $index => $item)
            <tr>
                <td class="col-no">{{ $index + 1 }}</td>
                <td class="col-tanggal">
                    <span class="tanggal-text">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</span>
                    <span class="jam-text">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }} WIB</span>
                </td>
                <td>
                    @include('admin.exports.pdf.components.employee-cell', [
                        'nama' => $item->user->name ?? 'N/A',
                        'sub' => $item->user->divisi ?? '-',
                    ])
                </td>
                <td>
                    <span class="aktivitas-text">{{ $item->keterangan ?: ($item->title ?? '-') }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="cell-kosong" style="padding: 15px;">
                    Tidak ada data aktivitas yang ditemukan pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
