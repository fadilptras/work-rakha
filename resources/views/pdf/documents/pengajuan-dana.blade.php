@extends('pdf.layouts.approval-document')

@section('title', 'Pengajuan Dana - ' . $pengajuanDana->nomor_surat)
@section('form-title', 'SURAT PENGAJUAN DANA')

@section('extra-style')
    p.doc-title, p.doc-number {
        font-family: "Times New Roman", Times, serif !important;
    }
    
    p.doc-title {
        font-size: 14px !important;
    }

    p.doc-number {
        font-weight: bold !important;
        font-size: 13px !important;
    }
    table.items-table th, table.items-table td {
        border: 1px solid #000 !important;
    }
    table.items-table th {
        background-color: transparent !important;
    }
@endsection

@php
    $nomorDokumen = $pengajuanDana->nomor_surat;

    // normalisasi ke format generic
    $approvers = [
        ['label' => 'Approver 1', 'status' => $pengajuanDana->approver_1_status,
         'nama' => $pengajuanDana->approverDana1->name ?? null,
         'jabatan' => $pengajuanDana->approverDana1->jabatan ?? 'Approver 1',
         'tanggal' => $pengajuanDana->approver_1_approved_at?->translatedFormat('d F Y, H.i \W\I\B')],
        ['label' => 'Approver 2', 'status' => $pengajuanDana->approver_2_status,
         'nama' => $pengajuanDana->approverDana2->name ?? null,
         'jabatan' => $pengajuanDana->approverDana2->jabatan ?? 'Approver 2',
         'tanggal' => $pengajuanDana->approver_2_approved_at?->translatedFormat('d F Y, H.i \W\I\B')],
        ['label' => 'Approver 3', 'status' => $pengajuanDana->approver_3_status,
         'nama' => $pengajuanDana->approverDana3->name ?? null,
         'jabatan' => $pengajuanDana->approverDana3->jabatan ?? 'Approver 3',
         'tanggal' => $pengajuanDana->approver_3_approved_at?->translatedFormat('d F Y, H.i \W\I\B')],
    ];
    if (!empty($pengajuanDana->approver_dana_4_id)) {
        // Jika diselesaikan admin (override), approver 4 tercatat 'skipped' tapi
        // sebenarnya selesai mengikuti waktu penyelesaian admin
        $status4 = $pengajuanDana->approver_4_status;
        $tanggal4 = $pengajuanDana->approver_4_approved_at?->translatedFormat('d F Y, H.i \W\I\B');
        if ($pengajuanDana->status === 'selesai' && $status4 === 'skipped' && $pengajuanDana->approver_4_approved_at) {
            $status4 = 'disetujui';
            $tanggal4 = ($pengajuanDana->approver_4_approved_at ?? $pengajuanDana->updated_at)?->translatedFormat('d F Y, H.i \W\I\B');
        }
        $approvers[] = ['label' => 'Approver 4', 'status' => $status4,
            'nama' => $pengajuanDana->approverDana4->name ?? null,
            'jabatan' => $pengajuanDana->approverDana4->jabatan ?? 'Approver 4',
            'tanggal' => $tanggal4];
    }
@endphp

@section('content')
<div style="margin-top: 8px; font-size: 12px; font-family: 'Times New Roman', Times, serif;">
    <p style="margin-bottom: 5px;">Yang Bertanda Tangan di bawah ini :</p>
    <table style="width: 100%; margin-bottom: 15px; border-collapse: collapse; border: none;">
        <tr>
            <td style="width: 100px; border: none; padding: 3px 0;">Nama</td>
            <td style="width: 15px; border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $pengajuanDana->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Divisi</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $pengajuanDana->divisi ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Jabatan</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $pengajuanDana->user->jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Perihal</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $pengajuanDana->judul_pengajuan }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Tanggal</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $pengajuanDana->created_at->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0; vertical-align: top;">Informasi Bank</td>
            <td style="border: none; padding: 3px 0; vertical-align: top;">:</td>
            <td style="border: none; padding: 3px 0;">
                {{ $pengajuanDana->nama_bank }} - {{ $pengajuanDana->no_rekening }} (A/N {{ $pengajuanDana->nama_rek }})
            </td>
        </tr>
    </table>
    
    <p style="text-align: justify; margin-bottom: 15px; line-height: 1.4;">
        Bermaksud untuk melakukan permohonan pencairan dana dengan rincian sebagai berikut :
    </p>
</div>

<table class="items-table" style="font-family: 'Times New Roman', Times, serif; font-size: 12px; margin-bottom: 30px;">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th>Keterangan Penggunaan Dana</th>
            <th width="25%">Nominal (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pengajuanDana->rincian_dana as $i => $item)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $item['deskripsi'] ?? '-' }}</td>
            <td style="text-align: right;">{{ number_format($item['jumlah'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="text-center">Data rincian tidak tersedia.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="text-align: right; font-weight: bold; padding-right: 10px;">TOTAL</td>
            <td style="text-align: right; font-weight: bold;">Rp {{ number_format($pengajuanDana->total_dana, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

@include('pdf.partials.signature-block', ['approvers' => $approvers])

@endsection