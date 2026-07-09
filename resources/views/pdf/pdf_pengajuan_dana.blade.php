<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pengajuan Dana - {{ str_pad($pengajuanDana->id, 4, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* Gaya Inti */
        body { font-family: 'Helvetica', sans-serif; font-size: 9px; color: #333; line-height: 1.2; }
        .container { width: 95%; margin: 15px auto; }
        
        /* Header */
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; color: #003366; font-weight: bold; } 
        .header p { margin: 4px 0; font-size: 12px; }

        /* Tabel Data (Detail & Rincian) - Pakai Garis */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 7px; text-align: left; vertical-align: top; }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; }

        /* Judul Bagian */
        .section-title {
            background-color: #eaf2f8;
            padding: 9px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #3498db;
            color: #003366;
        }

        /* Status Box Final */
        .status-box {
            padding: 10px;
            margin-top: 30px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            border: 1px solid #ccc;
        }
        .status-selesai { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .status-ditolak { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .status-diproses { background-color: #cce5ff; color: #004085; border-color: #b8daff; }
        .status-dibatalkan { background-color: #e2e3e5; color: #383d41; border-color: #d6d8db; }

        /* --- STYLE TANDA TANGAN (MIRIP PENGAJUAN BARANG) --- */
        .signatures { 
            width: 100%; 
            margin-top: 10px; 
            border: none; /* HILANGKAN BORDER TABEL UTAMA */
            table-layout: fixed; 
        }
        .signatures td { 
            width: 33.33%; 
            border: none; /* HILANGKAN BORDER CELL */
            text-align: center; 
            vertical-align: top; 
            padding: 10px; 
        }

        /* Judul Kolom TTD */
        .ttd-header { margin-bottom: 25px; font-size: 11px; color: #333; }

        /* Status Text (Hijau/Merah) */
        .st-approved { 
            color: #28a745; /* Hijau */
            font-weight: bold; 
            font-style: italic; 
            font-size: 12px; 
            margin-bottom: 20px;
        }
        .st-rejected { 
            color: #dc3545; /* Merah */
            font-weight: bold; 
            font-style: italic; 
            font-size: 12px; 
            margin-bottom: 20px;
        }

        /* Placeholder Garis Putus-putus (Untuk Skipped/Pending) */
        .st-placeholder {
            margin: 20px 0;
            border-bottom: 1px dotted #aaa;
            color: #aaa;
            font-style: italic;
            font-size: 10px;
            padding-bottom: 5px;
        }

        /* Detail Nama & Tanggal */
        .ttd-nama { 
            font-weight: bold; 
            text-decoration: underline; 
            font-size: 12px; 
            color: #000;
        }
        .ttd-jabatan { 
            font-size: 11px; 
            color: #444; 
            margin-top: 2px;
        }
        .ttd-tanggal { 
            font-weight: bold; 
            color: #555; 
            font-size: 11px; 
            margin-top: 5px; 
        }

        /* Catatan */
        .catatan {
            background: #f9f9f9;
            border-left: 3px solid #ccc;
            padding: 8px;
            margin-top: 5px;
            font-style: italic;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">

        {{-- HEADER --}}
        <div class="header">
            <h1>PT RAKHA NUSANTARA MEDIKA</h1>
            <p style="font-weight: bold;">FORMULIR PENGAJUAN DANA</p>
            <p style="font-size: 10px; font-weight: bold;">ID Pengajuan: {{ str_pad($pengajuanDana->id, 4, '0', STR_PAD_LEFT) }}</p>
        </div>

        {{-- I. DETAIL PENGAJUAN --}}
        <div class="section-title">I. DETAIL PENGAJUAN</div>
        <table class="data-table">
            <tr>
                <th width="15%">Tanggal</th>
                <td width="35%">{{ $pengajuanDana->created_at->translatedFormat('l, d F Y') }}</td>
                <th width="15%">Pemohon</th>
                <td width="35%">{{ $pengajuanDana->user->name }}</td>
            </tr>
            <tr>
                <th>Divisi</th>
                <td>{{ $pengajuanDana->divisi }}</td>
                <th>Jabatan</th>
                <td>{{ $pengajuanDana->user->jabatan ?? '-' }}</td>
            </tr>
            <tr>
                <th>Judul</th>
                {{-- colspan="3" agar judul yang panjang bisa memenuhi sisa baris --}}
                <td colspan="3">{{ $pengajuanDana->judul_pengajuan }}</td>
            </tr>
        </table>

        {{-- II. INFORMASI TRANSFER --}}
        <div class="section-title">II. INFORMASI BANK</div>
        <table class="data-table">
            <tr>
                <th width="15%">Bank Tujuan</th>
                <td width="35%">{{ $pengajuanDana->nama_bank }}</td>
                <th width="15%">No. Rekening</th>
                <td width="35%">{{ $pengajuanDana->no_rekening }}</td>
                <tr>
                    <th>Atas Nama (A/N)</th>
                    <td colspan="3" style="font-weight: bold;">{{ $pengajuanDana->nama_rek }}</td>
                </tr>
            </tr>
        </table>

        {{-- III. RINCIAN --}}
        <div class="section-title">III. RINCIAN PENGGUNAAN DANA</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 5%;">No</th>
                    <th>Keterangan / Deskripsi</th>
                    <th style="text-align: right; width: 25%;">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengajuanDana->rincian_dana as $index => $item)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $item['deskripsi'] ?? '-' }}</td>
                        <td style="text-align: right;">{{ number_format($item['jumlah'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align: center;">Data rincian tidak tersedia.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold; background-color: #f9f9f9;">TOTAL PENGAJUAN</td>
                    <td style="text-align: right; font-weight: bold; background-color: #f9f9f9;">Rp {{ number_format($pengajuanDana->total_dana, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- IV. PERSETUJUAN (LAYOUT MIRIP PENGAJUAN BARANG) --}}
        <div class="section-title">IV. LEMBAR PERSETUJUAN</div>
        <table class="signatures">
            <tr>
                {{-- KOLOM 1: APPROVER 1 --}}
                <td>
                    <div class="ttd-header">Disetujui oleh (Approver 1),</div>
                    
                    @if($pengajuanDana->approver_1_status == 'disetujui')
                        <div class="st-approved">[ DISETUJUI ]</div>
                        <div class="ttd-nama">{{ $pengajuanDana->approver1->name ?? 'Approver 1' }}</div>
                        <div class="ttd-jabatan">{{ $pengajuanDana->approver1->jabatan ?? 'Atasan' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanDana->approver_1_approved_at?->translatedFormat('l, d F Y H:i') }} WIB</div>
                    
                    @elseif($pengajuanDana->approver_1_status == 'ditolak')
                        <div class="st-rejected">[ DITOLAK ]</div>
                        <div class="ttd-nama">{{ $pengajuanDana->approver1->name ?? 'Approver 1' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanDana->approver_1_approved_at?->translatedFormat('l, d F Y H:i') }} WIB</div>

                    @elseif($pengajuanDana->approver_1_status == 'skipped')
                        <div class="st-placeholder">(Dilewati / Auto-Approve)</div>
                        <div class="ttd-nama">{{ $pengajuanDana->user->name }}</div>
                        <div class="ttd-jabatan">Pemohon</div>

                    @else
                        <div class="st-placeholder">( Menunggu Persetujuan )</div>
                    @endif
                </td>

                {{-- KOLOM 2: APPROVER 2 --}}
                <td>
                    <div class="ttd-header">Disetujui oleh (Approver 2),</div>

                    @if($pengajuanDana->approver_2_status == 'disetujui')
                        <div class="st-approved">[ DISETUJUI ]</div>
                        <div class="ttd-nama">{{ $pengajuanDana->approver2->name ?? 'Approver 2' }}</div>
                        <div class="ttd-jabatan">{{ $pengajuanDana->approver2->jabatan ?? 'Manager' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanDana->approver_2_approved_at?->translatedFormat('l, d F Y H:i') }} WIB</div>

                    @elseif($pengajuanDana->approver_2_status == 'ditolak')
                        <div class="st-rejected">[ DITOLAK ]</div>
                        <div class="ttd-nama">{{ $pengajuanDana->approver2->name ?? 'Approver 2' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanDana->approver_2_approved_at?->translatedFormat('l, d F Y H:i') }} WIB</div>

                    @elseif($pengajuanDana->approver_2_status == 'skipped')
                        <div class="st-placeholder">(Dilewati / Auto-Approve)</div>

                    @else
                        <div class="st-placeholder">( Menunggu Persetujuan )</div>
                    @endif
                </td>

                {{-- KOLOM 3: FINANCE --}}
                <td>
                    <div class="ttd-header">Diproses oleh (Finance),</div>

                    @if($pengajuanDana->payment_status == 'selesai')
                        <div class="st-approved">[ SELESAI ]</div>
                        <div class="ttd-nama">{{ $pengajuanDana->financeProcessor->name ?? 'Admin Finance' }}</div>
                        <div class="ttd-jabatan">{{ $pengajuanDana->financeProcessor->jabatan ?? 'Finance & Accounting' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanDana->updated_at->translatedFormat('l, d F Y H:i') }} WIB</div>

                    @elseif($pengajuanDana->payment_status == 'diproses')
                        <div class="st-approved">[ DIPROSES ]</div>
                        <div class="ttd-nama">{{ $pengajuanDana->financeProcessor->name ?? 'Admin Finance' }}</div>
                        <div class="ttd-jabatan">{{ $pengajuanDana->financeProcessor->jabatan ?? 'Finance & Accounting' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanDana->finance_processed_at?->translatedFormat('l, d F Y H:i') }} WIB</div>
                    
                    @elseif($pengajuanDana->status == 'ditolak') 
                        <div class="st-placeholder">-</div>

                    @else
                        <div class="st-placeholder">( Menunggu Proses )</div>
                        <div class="ttd-nama" style="text-decoration: none; color: #999; font-weight: normal;">
                            {{ $pengajuanDana->user->managerKeuangan->name ?? 'Finance' }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- V. CATATAN --}}
        @if($pengajuanDana->approver_1_catatan || $pengajuanDana->approver_2_catatan || $pengajuanDana->catatan_finance)
            <div class="section-title">V. CATATAN TAMBAHAN</div>
            <table class="data-table">
                @if($pengajuanDana->approver_1_catatan)
                    <tr><td width="25%">Catatan Approver 1</td><td><div class="catatan">{{ $pengajuanDana->approver_1_catatan }}</div></td></tr>
                @endif
                @if($pengajuanDana->approver_2_catatan)
                    <tr><td>Catatan Approver 2</td><td><div class="catatan">{{ $pengajuanDana->approver_2_catatan }}</div></td></tr>
                @endif
                @if($pengajuanDana->catatan_finance)
                    <tr><td>Catatan Finance</td><td><div class="catatan">{{ $pengajuanDana->catatan_finance }}</div></td></tr>
                @endif
            </table>
        @endif
        
        {{-- STATUS FINAL --}}
        @php
            $statusFinal = $pengajuanDana->status;
            $classFinal = 'status-diproses';
            $labelFinal = 'DIPROSES';

            if ($statusFinal == 'selesai') { 
                $classFinal = 'status-selesai'; $labelFinal = 'SELESAI'; 
            } elseif ($statusFinal == 'ditolak') { 
                $classFinal = 'status-ditolak'; $labelFinal = 'DITOLAK'; 
            } elseif ($statusFinal == 'dibatalkan') { 
                $classFinal = 'status-dibatalkan'; $labelFinal = 'DIBATALKAN'; 
            }
        @endphp
        <div class="status-box {{ $classFinal }}">
            STATUS AKHIR: {{ $labelFinal }}
        </div>

    </div>
</body>
</html>