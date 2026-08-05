@extends('pdf.layouts.approval-document')

@section('title', 'Pengajuan Dana - ' . $pengajuanDana->nomor_pengajuan)
@section('form-title', 'FORMULIR PENGAJUAN DANA')

@php
    $nomorDokumen = $pengajuanDana->nomor_pengajuan;

    // normalisasi ke format generic — di sinilah perbedaan nama field "diserap"
    $approvers = [
        ['label' => 'Approver 1', 'status' => $pengajuanDana->approver_1_status,
         'nama' => $pengajuanDana->approverDana1->name ?? null,
         'jabatan' => $pengajuanDana->approverDana1->jabatan ?? null,
         'tanggal' => $pengajuanDana->approver_1_approved_at?->translatedFormat('d/m/Y H:i')],
        ['label' => 'Approver 2', 'status' => $pengajuanDana->approver_2_status,
         'nama' => $pengajuanDana->approverDana2->name ?? null,
         'jabatan' => $pengajuanDana->approverDana2->jabatan ?? null,
         'tanggal' => $pengajuanDana->approver_2_approved_at?->translatedFormat('d/m/Y H:i')],
        ['label' => 'Finance', 'status' => $pengajuanDana->approver_3_status,
         'nama' => $pengajuanDana->approverDana3->name ?? null,
         'jabatan' => $pengajuanDana->approverDana3->jabatan ?? null,
         'tanggal' => $pengajuanDana->approver_3_approved_at?->translatedFormat('d/m/Y H:i')],
    ];
    if (!empty($pengajuanDana->approver_dana_4_id)) {
        $approvers[] = ['label' => 'Approver 4 (Final)', 'status' => $pengajuanDana->approver_4_status,
            'nama' => $pengajuanDana->approverDana4->name ?? null,
            'jabatan' => $pengajuanDana->approverDana4->jabatan ?? null,
            'tanggal' => $pengajuanDana->approver_4_approved_at?->translatedFormat('d/m/Y H:i')];
    }
@endphp

@section('content')
    <div class="section-title">I. DETAIL PENGAJUAN</div>
    <table class="data-table">
        <tr>
            <th width="15%">Tanggal</th><td width="35%">{{ $pengajuanDana->created_at->translatedFormat('l, d F Y') }}</td>
            <th width="15%">Pemohon</th><td width="35%">{{ $pengajuanDana->user->name }}</td>
        </tr>
        <tr>
            <th>Divisi</th><td>{{ $pengajuanDana->divisi }}</td>
            <th>Jabatan</th><td>{{ $pengajuanDana->user->jabatan ?? '-' }}</td>
        </tr>
        <tr><th>Judul</th><td colspan="3">{{ $pengajuanDana->judul_pengajuan }}</td></tr>
    </table>

    <div class="section-title">II. INFORMASI BANK</div>
    <table class="data-table">
        <tr>
            <th width="15%">Bank Tujuan</th><td width="35%">{{ $pengajuanDana->nama_bank }}</td>
            <th width="15%">No. Rekening</th><td width="35%">{{ $pengajuanDana->no_rekening }}</td>
        </tr>
        <tr><th>Atas Nama (A/N)</th><td colspan="3" style="font-weight:bold;">{{ $pengajuanDana->nama_rek }}</td></tr>
    </table>

    <div class="section-title">III. RINCIAN PENGGUNAAN DANA</div>
    <table class="items-table">
        <thead>
            <tr><th width="5%">No</th><th>Keterangan</th><th width="25%">Nominal (Rp)</th></tr>
        </thead>
        <tbody>
        @forelse ($pengajuanDana->rincian_dana as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item['deskripsi'] ?? '-' }}</td>
                <td style="text-align:right;">{{ number_format($item['jumlah'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center">Data rincian tidak tersedia.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right; font-weight:bold; background:#f9f9f9;">TOTAL PENGAJUAN</td>
                <td style="text-align:right; font-weight:bold; background:#f9f9f9;">Rp {{ number_format($pengajuanDana->total_dana, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">IV. LEMBAR PERSETUJUAN</div>
    @include('pdf.partials.signature-block', ['approvers' => $approvers])

    @include('pdf.partials.status-box', [
        'statusKey' => $pengajuanDana->status,
        'statusLabel' => match($pengajuanDana->status) {
            'selesai' => 'SELESAI', 'ditolak' => 'DITOLAK', 'dibatalkan' => 'DIBATALKAN', default => 'DIPROSES',
        },
    ])
@endsection