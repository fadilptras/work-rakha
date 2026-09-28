@extends('pdf.layouts.approval-document')

@section('title', 'Form Pengajuan Izin / Cuti - ' . $cuti->nomor_surat)
@section('form-title', 'Form Pengajuan Izin / Cuti')

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
    table.items-table td {
        text-align: center !important;
        vertical-align: middle !important;
    }
@endsection

@php
    $nomorDokumen = $cuti->nomor_surat;

    // Hanya slot approver yang terisi yang tampil (fleksibel 1-4 kolom).
    // Label jabatan menempel ke slot approver, bukan posisi kolom:
    // mis. slot 3 selalu "HRD" walau tampil di kolom 2 karena slot 2 kosong.
    $slotJabatan = [1 => 'Atasan Langsung', 2 => 'Manager Divisi', 3 => 'HRD', 4 => 'Admin'];
    $approvers = [];
    foreach ([1, 2, 3, 4] as $n) {
        if (empty($cuti->{'approver_cuti_' . $n . '_id'})) continue;
        $relasi = $cuti->{'approver' . $n};
        $tanggal = $cuti->{'tanggal_approve_' . $n} ?? null;
        $approvers[] = [
            'label' => $n === 4 ? 'Tahap Final' : 'Tahap ' . (count($approvers) + 1),
            'status' => $cuti->{'status_approver_' . $n},
            'nama' => $relasi->name ?? null,
            'jabatan' => $slotJabatan[$n],
            'tanggal' => $tanggal ? \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y, H.i \W\I\B') : null,
        ];
    }
@endphp

@section('content')
<div style="margin-top: 8px; font-size: 12px; font-family: 'Times New Roman', Times, serif;">
    <p style="margin-bottom: 5px;">Yang Bertanda Tangan di bawah ini :</p>
    <table style="width: 100%; margin-bottom: 15px; border-collapse: collapse; border: none;">
        <tr>
            <td style="width: 120px; border: none; padding: 3px 0;">Nama Lengkap</td>
            <td style="width: 15px; border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $cuti->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Divisi</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $cuti->user->divisi ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Jabatan</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $cuti->user->jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Sisa Cuti Tahunan</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;"><strong>{{ isset($sisaCuti) ? $sisaCuti : '-' }} Hari</strong></td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Tanggal Pengajuan</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $cuti->created_at->translatedFormat('d F Y') }}</td>
        </tr>
    </table>
    
    <p style="text-align: justify; margin-bottom: 15px; line-height: 1.4;">
        Bermaksud untuk mengajukan permohonan <strong>{{ strtoupper($cuti->jenis_cuti) }}</strong> dengan detail sebagai berikut :
    </p>
</div>

<table class="items-table" style="font-family: 'Times New Roman', Times, serif; font-size: 12px; margin-bottom: 30px;">
    <thead>
        <tr>
            <th width="25%">Tanggal Mulai</th>
            <th width="25%">Tanggal Selesai</th>
            <th width="20%">Lama Izin / Cuti</th>
            <th width="30%">Alasan Cuti</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">{{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('d F Y') }}</td>
            <td class="text-center">{{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->translatedFormat('d F Y') }}</td>
            <td class="text-center"><strong>{{ $cuti->total_hari }} Hari Kerja</strong></td>
            <td>{{ $cuti->alasan }}</td>
        </tr>
    </tbody>
</table>

@if(count($approvers))
    @include('pdf.partials.signature-block', ['approvers' => $approvers])
@endif

<div style="text-align: right; font-size: 10px; margin-top: 20px; font-family: 'Times New Roman', Times, serif;">
    FORM-HR-04-001
</div>

@php
    $statusFinal = strtolower($cuti->status ?? 'diajukan');
@endphp

@endsection