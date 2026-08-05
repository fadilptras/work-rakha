@extends('pdf.layouts.approval-document')

@section('title', 'Pengajuan Barang - ' . $pengajuanBarang->nomor_surat)
@section('form-title', 'SURAT PERMINTAAN BARANG (SPB)')

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
$nomorDokumen = $pengajuanBarang->nomor_surat;

// normalisasi ke format generic
$approvers = [
['label' => 'Tahap 1', 'status' => $pengajuanBarang->status_appr_1,
'nama' => $pengajuanBarang->approver1->name ?? null,
'jabatan' => $pengajuanBarang->approver1->jabatan ?? 'Atasan',
'tanggal' => $pengajuanBarang->tanggal_approved_1?->translatedFormat('d F Y, H.i \W\I\B')],
['label' => 'Tahap 2', 'status' => $pengajuanBarang->status_appr_2,
'nama' => $pengajuanBarang->approver2->name ?? null,
'jabatan' => $pengajuanBarang->approver2->jabatan ?? 'Direktur',
'tanggal' => $pengajuanBarang->tanggal_approved_2?->translatedFormat('d F Y, H.i \W\I\B')],
['label' => 'Tahap 3', 'status' => $pengajuanBarang->status_appr_3,
'nama' => $pengajuanBarang->approver3->name ?? null,
'jabatan' => $pengajuanBarang->approver3->jabatan ?? 'Finance',
'tanggal' => $pengajuanBarang->tanggal_approved_3?->translatedFormat('d F Y, H.i \W\I\B')],
];
if (!empty($pengajuanBarang->approver_barang_4_id)) {
$approvers[] = ['label' => 'Tahap Final', 'status' => $pengajuanBarang->status_appr_4,
'nama' => $pengajuanBarang->approver4->name ?? null,
'jabatan' => $pengajuanBarang->approver4->jabatan ?? 'Admin',
'tanggal' => $pengajuanBarang->tanggal_approved_4?->translatedFormat('d F Y, H.i \W\I\B')];
}
@endphp

@section('content')
<div style="margin-top: 8px; font-size: 12px; font-family: 'Times New Roman', Times, serif;">
    <p style="margin-bottom: 5px;">Yang Bertanda Tangan di bawah ini :</p>
    <table style="width: 100%; margin-bottom: 15px; border-collapse: collapse; border: none;">
        <tr>
            <td style="width: 80px; border: none; padding: 3px 0;">Nama</td>
            <td style="width: 15px; border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $pengajuanBarang->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Divisi</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $pengajuanBarang->user->divisi ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Perihal</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $pengajuanBarang->judul_pengajuan }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Tanggal</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $pengajuanBarang->created_at->translatedFormat('d F Y') }}</td>
        </tr>
    </table>
    
    <p style="text-align: justify; margin-bottom: 15px; line-height: 1.4;">
        Bermaksud untuk melakukan permohonan pengadaan peralatan/barang kebutuhan penunjang kerja dengan spesifikasi sebagai berikut :
    </p>
</div>

<table class="items-table" style="font-family: 'Times New Roman', Times, serif; font-size: 12px;">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="30%">Nama Barang</th>
            <th width="20%">Suplier</th>
            <th width="10%">Satuan</th>
            <th width="10%">Jumlah</th>
            <th width="25%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pengajuanBarang->rincian_barang as $i => $item)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}</td>
            <td class="text-center">{{ $item['supplier'] ?? '-' }}</td>
            <td class="text-center">{{ $item['satuan'] ?? '-' }}</td>
            <td class="text-center">{{ $item['jumlah'] ?? 0 }}</td>
            <td>{{ $item['keterangan'] ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">Tidak ada rincian barang.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<p style="font-family: 'Times New Roman', Times, serif; font-size: 12px; margin-top: 10px; font-style: italic;">
    <strong>Catatan:</strong> {{ !empty($pengajuanBarang->catatan_pemohon) ? $pengajuanBarang->catatan_pemohon : '-' }}
</p>

<p style="font-family: 'Times New Roman', Times, serif; font-size: 12px; margin-top: 10px; text-align: justify;">
    Demikian surat pengajuan barang ini dibuat, besar harapan kami pihak terkait dapat merealisasikannya. Terimakasih atas waktu dan pengertiannya.
</p>

<div style="font-family: 'Times New Roman', Times, serif; font-size: 12px; margin-top: 10px;">
    @include('pdf.partials.signature-block', ['approvers' => $approvers])
</div>

@if($pengajuanBarang->catatan_approver_1 || $pengajuanBarang->catatan_approver_2 || $pengajuanBarang->catatan_approver_3 || $pengajuanBarang->catatan_approver_4)
<div style="margin-top: 20px; font-family: 'Times New Roman', Times, serif; font-size: 12px;">
    <p style="margin-bottom: 5px; font-weight: bold;">Catatan Persetujuan:</p>
    <ul style="margin-top: 0; padding-left: 20px;">
        @if($pengajuanBarang->catatan_approver_1)
            <li style="margin-bottom: 3px;"><strong>Tahap 1:</strong> <i>{{ $pengajuanBarang->catatan_approver_1 }}</i></li>
        @endif
        @if($pengajuanBarang->catatan_approver_2)
            <li style="margin-bottom: 3px;"><strong>Tahap 2:</strong> <i>{{ $pengajuanBarang->catatan_approver_2 }}</i></li>
        @endif
        @if($pengajuanBarang->catatan_approver_3)
            <li style="margin-bottom: 3px;"><strong>Tahap 3:</strong> <i>{{ $pengajuanBarang->catatan_approver_3 }}</i></li>
        @endif
        @if($pengajuanBarang->catatan_approver_4)
            <li style="margin-bottom: 3px;"><strong>Final (Admin):</strong> <i>{{ $pengajuanBarang->catatan_approver_4 }}</i></li>
        @endif
    </ul>
</div>
@endif

@endsection