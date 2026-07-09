<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pengajuan Barang - {{ str_pad($pengajuanBarang->id, 4, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* Gaya Inti */
        body { font-family: 'Helvetica', sans-serif; font-size: 9px; color: #333; line-height: 1.2; }
        .container { width: 95%; margin: 15px auto; }
        
        /* Header */
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; color: #003366; font-weight: bold; } 
        .header p { margin: 4px 0; font-size: 12px; }

        /* Tabel Data */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 7px; text-align: left; vertical-align: top; }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; }

        /* Tabel Rincian Barang */
        table.items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items-table th, table.items-table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        table.items-table th { background-color: #f8f9fa; font-weight: bold; text-align: center; }
        table.items-table td.text-center { text-align: center; }

        /* Judul Bagian */
        .section-title {
            background-color: #eaf2f8;
            padding: 8px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #3498db;
            color: #003366;
            text-transform: uppercase;
        }

        /* Status Box */
        .status-box {
            padding: 10px;
            margin-top: 25px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            border: 1px solid #ccc;
        }
        .status-selesai { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .status-ditolak { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .status-diproses { background-color: #fff3cd; color: #856404; border-color: #ffeeba; }
        .status-dibatalkan { background-color: #e2e3e5; color: #383d41; border-color: #d6d8db; }

        /* Tanda Tangan (Diubah Jadi 4 Kolom) */
        .signatures { 
            width: 100%; 
            margin-top: 20px; 
            border: none; 
            table-layout: fixed; 
        }
        .signatures td { 
            width: 25%; /* Presisi dibagi 4 */
            border: none; 
            text-align: center; 
            vertical-align: top; 
            padding: 4px; 
        }

        .ttd-header { margin-bottom: 15px; font-size: 10px; color: #333; }
        .st-approved { color: #28a745; font-weight: bold; font-style: italic; font-size: 10px; margin-bottom: 15px; }
        .st-rejected { color: #dc3545; font-weight: bold; font-style: italic; font-size: 10px; margin-bottom: 15px; }
        .st-placeholder { margin: 15px 0; border-bottom: 1px dotted #aaa; color: #aaa; font-style: italic; font-size: 9px; padding-bottom: 5px; }

        .ttd-nama { font-weight: bold; text-decoration: underline; font-size: 10px; color: #000; }
        .ttd-jabatan { font-size: 9px; color: #444; margin-top: 2px; }
        .ttd-tanggal { font-weight: bold; color: #555; font-size: 8px; margin-top: 4px; }

        .catatan { background: #f9f9f9; border-left: 3px solid #ccc; padding: 6px; margin-top: 2px; font-style: italic; font-size: 9px; }
    </style>
</head>
<body>
    <div class="container">

        {{-- HEADER --}}
        <div class="header">
            <h1>PT RAKHA NUSANTARA MEDIKA</h1>
            <p style="font-weight: bold;">FORMULIR PENGAJUAN BARANG</p>
            <p>ID Pengajuan: {{ str_pad($pengajuanBarang->id, 4, '0', STR_PAD_LEFT) }}</p>
        </div>

        {{-- I. DATA PEMOHON --}}
        <div class="section-title">I. DATA PEMOHON</div>
        <table class="data-table">
            <tr>
                <th width="25%">Nama Lengkap</th>
                <td>{{ $pengajuanBarang->user->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Divisi</th>
                <td>{{ $pengajuanBarang->user->divisi ?? '-' }}</td>
            </tr>
            <tr>
                <th>Judul Pengajuan</th>
                <td><strong>{{ $pengajuanBarang->judul_pengajuan }}</strong></td>
            </tr>
            <tr>
                <th>Tanggal Pengajuan</th>
                <td>{{ $pengajuanBarang->created_at->translatedFormat('l, d F Y - H:i') }} WIB</td>
            </tr>
        </table>

        {{-- II. RINCIAN BARANG --}}
        <div class="section-title">II. RINCIAN KEBUTUHAN BARANG</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th width="8%">No</th>
                    <th width="70%">Deskripsi / Nama Barang</th>
                    <th width="22%">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuanBarang->rincian_barang as $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item['deskripsi'] }}</td>
                    <td class="text-center"><strong>{{ $item['jumlah'] }} {{ $item['satuan'] }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center" style="font-style: italic; color: #888;">Tidak ada rincian barang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- III. LEMBAR PERSETUJUAN (4 KOLOM) --}}
        <div class="section-title">III. LEMBAR PERSETUJUAN</div>
        <table class="signatures">
            <tr>
                {{-- KOLOM 1: APPROVER 1 --}}
                <td>
                    <div class="ttd-header">Tahap 1,</div>
                    @if($pengajuanBarang->status_appr_1 == 'disetujui')
                        <div class="st-approved">[ DISETUJUI ]</div>
                        <div class="ttd-nama">{{ $pengajuanBarang->approver1->name ?? 'Tahap 1' }}</div>
                        <div class="ttd-jabatan">{{ $pengajuanBarang->approver1->jabatan ?? 'Atasan' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanBarang->tanggal_approved_1 ? \Carbon\Carbon::parse($pengajuanBarang->tanggal_approved_1)->translatedFormat('d/m/Y H:i') : '' }} WIB</div>
                    @elseif($pengajuanBarang->status_appr_1 == 'ditolak')
                        <div class="st-rejected">[ DITOLAK ]</div>
                        <div class="ttd-nama">{{ $pengajuanBarang->approver1->name ?? 'Tahap 1' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanBarang->tanggal_approved_1 ? \Carbon\Carbon::parse($pengajuanBarang->tanggal_approved_1)->translatedFormat('d/m/Y H:i') : '' }} WIB</div>
                    @elseif($pengajuanBarang->status_appr_1 == 'skipped')
                        <div class="st-placeholder">( Dilewati )</div>
                    @else
                        <div class="st-placeholder">( Menunggu )</div>
                    @endif
                </td>

                {{-- KOLOM 2: APPROVER 2 --}}
                <td>
                    <div class="ttd-header">Tahap 2,</div>
                    @if($pengajuanBarang->status_appr_2 == 'disetujui')
                        <div class="st-approved">[ DISETUJUI ]</div>
                        <div class="ttd-nama">{{ $pengajuanBarang->approver2->name ?? 'Tahap 2' }}</div>
                        <div class="ttd-jabatan">{{ $pengajuanBarang->approver2->jabatan ?? 'Manajer' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanBarang->tanggal_approved_2 ? \Carbon\Carbon::parse($pengajuanBarang->tanggal_approved_2)->translatedFormat('d/m/Y H:i') : '' }} WIB</div>
                    @elseif($pengajuanBarang->status_appr_2 == 'ditolak')
                        <div class="st-rejected">[ DITOLAK ]</div>
                        <div class="ttd-nama">{{ $pengajuanBarang->approver2->name ?? 'Tahap 2' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanBarang->tanggal_approved_2 ? \Carbon\Carbon::parse($pengajuanBarang->tanggal_approved_2)->translatedFormat('d/m/Y H:i') : '' }} WIB</div>
                    @elseif($pengajuanBarang->status_appr_2 == 'skipped')
                        <div class="st-placeholder">( Dilewati )</div>
                    @else
                        <div class="st-placeholder">( Menunggu )</div>
                    @endif
                </td>

                {{-- KOLOM 3: APPROVER 3 --}}
                <td>
                    <div class="ttd-header">Tahap 3,</div>
                    @if($pengajuanBarang->status_appr_3 == 'disetujui')
                        <div class="st-approved">[ DISETUJUI ]</div>
                        <div class="ttd-nama">{{ $pengajuanBarang->approver3->name ?? 'Tahap 3' }}</div>
                        <div class="ttd-jabatan">{{ $pengajuanBarang->approver3->jabatan ?? 'General Manager' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanBarang->tanggal_approved_3 ? \Carbon\Carbon::parse($pengajuanBarang->tanggal_approved_3)->translatedFormat('d/m/Y H:i') : '' }} WIB</div>
                    @elseif($pengajuanBarang->status_appr_3 == 'ditolak')
                        <div class="st-rejected">[ DITOLAK ]</div>
                        <div class="ttd-nama">{{ $pengajuanBarang->approver3->name ?? 'Tahap 3' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanBarang->tanggal_approved_3 ? \Carbon\Carbon::parse($pengajuanBarang->tanggal_approved_3)->translatedFormat('d/m/Y H:i') : '' }} WIB</div>
                    @elseif($pengajuanBarang->status_appr_3 == 'skipped')
                        <div class="st-placeholder">( Dilewati )</div>
                    @else
                        <div class="st-placeholder">( Menunggu )</div>
                    @endif
                </td>

                {{-- KOLOM 4: APPROVER 4 (ADMIN FINAL) --}}
                <td>
                    <div class="ttd-header">Tahap Final,</div>
                    @if($pengajuanBarang->status_appr_4 == 'disetujui')
                        <div class="st-approved">[ DISETUJUI ]</div>
                        <div class="ttd-nama">{{ $pengajuanBarang->approver4->name ?? 'Admin / Direktur' }}</div>
                        <div class="ttd-jabatan">{{ $pengajuanBarang->approver4->jabatan ?? 'Admin / Direktur' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanBarang->tanggal_approved_4 ? \Carbon\Carbon::parse($pengajuanBarang->tanggal_approved_4)->translatedFormat('d/m/Y H:i') : '' }} WIB</div>
                    @elseif($pengajuanBarang->status_appr_4 == 'ditolak')
                        <div class="st-rejected">[ DITOLAK ]</div>
                        <div class="ttd-nama">{{ $pengajuanBarang->approver4->name ?? 'Admin / Direktur' }}</div>
                        <div class="ttd-tanggal">{{ $pengajuanBarang->tanggal_approved_4 ? \Carbon\Carbon::parse($pengajuanBarang->tanggal_approved_4)->translatedFormat('d/m/Y H:i') : '' }} WIB</div>
                    @elseif($pengajuanBarang->status_appr_4 == 'skipped')
                        <div class="st-placeholder">( Dilewati )</div>
                    @else
                        <div class="st-placeholder">( Menunggu )</div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- IV. CATATAN APPROVER --}}
        @if($pengajuanBarang->catatan_approver_1 || $pengajuanBarang->catatan_approver_2 || $pengajuanBarang->catatan_approver_3 || $pengajuanBarang->catatan_approver_4)
            <div class="section-title">IV. CATATAN APPROVER</div>
            <table class="data-table">
                @if($pengajuanBarang->catatan_approver_1)
                    <tr><td width="25%">Catatan Tahap 1</td><td><div class="catatan">{{ $pengajuanBarang->catatan_approver_1 }}</div></td></tr>
                @endif
                @if($pengajuanBarang->catatan_approver_2)
                    <tr><td width="25%">Catatan Tahap 2</td><td><div class="catatan">{{ $pengajuanBarang->catatan_approver_2 }}</div></td></tr>
                @endif
                @if($pengajuanBarang->catatan_approver_3)
                    <tr><td width="25%">Catatan Tahap 3</td><td><div class="catatan">{{ $pengajuanBarang->catatan_approver_3 }}</div></td></tr>
                @endif
                @if($pengajuanBarang->catatan_approver_4)
                    <tr><td width="25%">Catatan Final (Admin)</td><td><div class="catatan">{{ $pengajuanBarang->catatan_approver_4 }}</div></td></tr>
                @endif
            </table>
        @endif                                                              

        {{-- STATUS FINAL --}}
        @php
            $statusFinal = strtolower($pengajuanBarang->status ?? 'diproses');
            $finalStatusClass = 'status-diproses'; 
            $statusLabel = 'DIPROSES';
            if($statusFinal == 'selesai' || $statusFinal == 'disetujui') { $finalStatusClass = 'status-selesai'; $statusLabel = 'SELESAI'; } 
            else if($statusFinal == 'ditolak') { $finalStatusClass = 'status-ditolak'; $statusLabel = 'DITOLAK'; } 
            else if($statusFinal == 'dibatalkan') { $finalStatusClass = 'status-dibatalkan'; $statusLabel = 'DIBATALKAN'; }
        @endphp

        <div class="status-box {{ $finalStatusClass }}">
            STATUS AKHIR: {{ $statusLabel }}
        </div>

    </div>
</body>
</html>