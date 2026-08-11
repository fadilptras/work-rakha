@extends('pdf.layouts.approval-document')

@section('title', 'Formulir Pengajuan Cuti - ' . $cuti->nomor_surat)
@section('form-title', 'SURAT PENGAJUAN CUTI')

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
    $nomorDokumen = $cuti->nomor_surat;

    // normalisasi ke format generic
    $approvers = [
        ['label' => 'Tahap 1', 'status' => $cuti->status_approver_1,
         'nama' => $cuti->approver1->name ?? null,
         'jabatan' => $cuti->approver1->jabatan ?? 'Atasan',
         'tanggal' => $cuti->tanggal_approve_1 ? \Carbon\Carbon::parse($cuti->tanggal_approve_1)->translatedFormat('d F Y, H.i \W\I\B') : null],
        ['label' => 'Tahap 2', 'status' => $cuti->status_approver_2,
         'nama' => $cuti->approver2->name ?? null,
         'jabatan' => $cuti->approver2->jabatan ?? 'Manajer',
         'tanggal' => $cuti->tanggal_approve_2 ? \Carbon\Carbon::parse($cuti->tanggal_approve_2)->translatedFormat('d F Y, H.i \W\I\B') : null],
        ['label' => 'Tahap 3', 'status' => $cuti->status_approver_3,
         'nama' => $cuti->approver3->name ?? null,
         'jabatan' => $cuti->approver3->jabatan ?? 'HRD / Keuangan',
         'tanggal' => $cuti->tanggal_approve_3 ? \Carbon\Carbon::parse($cuti->tanggal_approve_3)->translatedFormat('d F Y, H.i \W\I\B') : null],
    ];
    if (!empty($cuti->approver_cuti_4_id)) {
        $approvers[] = ['label' => 'Tahap Final', 'status' => $cuti->status_approver_4,
            'nama' => $cuti->approver4->name ?? null,
            'jabatan' => $cuti->approver4->jabatan ?? 'Admin / Direktur',
            'tanggal' => $cuti->tanggal_approve_4 ? \Carbon\Carbon::parse($cuti->tanggal_approve_4)->translatedFormat('d F Y, H.i \W\I\B') : null];
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
            <th width="20%">Lama Cuti</th>
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

@include('pdf.partials.signature-block', ['approvers' => $approvers])

@php
    $statusFinal = strtolower($cuti->status ?? 'diajukan');
@endphp

@endsection