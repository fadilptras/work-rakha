<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>CV & Data Karyawan - {{ $user->name }}</title>
    <style>
        /* Konfigurasi Halaman */
        @page { margin: 15mm 15mm 20mm 15mm; }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #374151; /* Tailwind gray-700 */
        }
        
        /* Utilitas Dasar */
        .w-100 { width: 100%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-gray { color: #6B7280; } /* Tailwind gray-500 */
        .font-bold { font-weight: bold; }
        .text-primary { color: #1E40AF; } /* Tailwind blue-800 */
        
        /* Bagian Header (Foto & Identitas) */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1E40AF;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-name {
            font-size: 24px;
            color: #111827; /* Tailwind gray-900 */
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 5px 0;
            letter-spacing: 1px;
        }
        .header-position {
            font-size: 14px;
            color: #1E40AF;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }
        .header-contact {
            font-size: 10px;
            color: #4B5563; /* Tailwind gray-600 */
            margin: 0;
        }
        .profile-img-container {
            width: 110px;
            text-align: right;
            vertical-align: top;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px; /* Sudut sedikit membulat, lebih elegan dari lingkaran penuh di CV */
            border: 1px solid #D1D5DB; /* Tailwind gray-300 */
        }

        /* Judul Section */
        h2.section-title {
            font-size: 13px;
            color: #1E40AF;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #E5E7EB; /* Tailwind gray-200 */
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        /* Tabel Data 2 Kolom (Borderless) */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-table td.label {
            width: 38%; /* Diperlebar agar teks panjang seperti Tanggal Lahir tidak mepet */
            padding-right: 10px; /* Menambahkan jarak aman dengan value */
            color: #6B7280;
            font-size: 10px;
            text-transform: uppercase;
        }
        .info-table td.value {
            width: 62%;
            color: #111827;
            font-weight: bold;
        }

        /* Container untuk layout 2 kolom berdampingan */
        .grid-container {
            width: 100%;
        }
        .grid-column {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .grid-spacer {
            width: 2%;
            display: inline-block;
        }

        /* Tabel Riwayat (Pendidikan & Pekerjaan) */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 15px;
        }
        .history-table th {
            background-color: #F3F4F6; /* Tailwind gray-100 */
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #D1D5DB; /* Tailwind gray-300 */
            text-transform: uppercase;
        }
        .history-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #E5E7EB;
            vertical-align: top;
        }
        /* Mencegah baris terpotong saat pindah halaman */
        .history-table tr {
            page-break-inside: avoid;
        }

        .no-data {
            font-style: italic;
            color: #9CA3AF;
            font-size: 10px;
            margin-bottom: 15px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -5mm;
            left: 0;
            right: 0;
            width: 100%;
            text-align: right;
            font-size: 8px;
            color: #9CA3AF;
            border-top: 1px solid #F3F4F6;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    {{-- FOOTER GLOBAL --}}
    <div class="footer">
        Dicetak pada: {{ $tanggal_cetak }} oleh {{ Auth::user()->name }} | Profil Karyawan PT Rakha Nusantara Medika
    </div>

    {{-- HEADER CV --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <h1 class="header-name">{{ $user->name ?? '-' }}</h1>
                <p class="header-position">{{ $user->jabatan ?? 'Karyawan' }} @if($user->divisi) - {{ $user->divisi }} @endif</p>
                <p class="header-contact">
                    <strong>Email:</strong> {{ $user->email ?? '-' }} &nbsp;|&nbsp; 
                    <strong>Telepon:</strong> {{ $user->nomor_telepon ?? '-' }}
                </p>
                <p class="header-contact" style="margin-top: 3px;">
                    <strong>Domisili:</strong> {{ $user->alamat_domisili ?: ($user->alamat_ktp ?: '-') }}
                </p>
            </td>
            <td class="profile-img-container">
                @php
                    $imgPath = $user->profile_picture ? storage_path('app/public/' . $user->profile_picture) : public_path('images/default-avatar.png');
                    if (!file_exists($imgPath)) { $imgPath = public_path('images/default-avatar.png'); }
                @endphp
                <img src="{{ $imgPath }}" alt="Foto Profil" class="profile-img">
            </td>
        </tr>
    </table>

    {{-- SPLIT COLUMN: PRIBADI & KETENAGAKERJAAN --}}
    <div class="grid-container">
        {{-- Kolom Kiri: Personal --}}
        <div class="grid-column">
            <h2 class="section-title">Data Pribadi</h2>
            <table class="info-table">
                <tr><td class="label">Tempat, Tanggal Lahir</td><td class="value">{{ $user->tempat_lahir ?? '-' }}, {{ $user->tanggal_lahir ? $user->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td></tr>
                <tr><td class="label">Jenis Kelamin</td><td class="value">{{ $user->jenis_kelamin ?? '-' }}</td></tr>
                <tr><td class="label">Agama</td><td class="value">{{ $user->agama ?? '-' }}</td></tr>
                <tr><td class="label">Gol. Darah</td><td class="value">{{ $user->golongan_darah ?? '-' }}</td></tr>
                <tr><td class="label">Pernikahan</td><td class="value">{{ $user->status_pernikahan ?? '-' }}</td></tr>
                <tr><td class="label">Alamat KTP</td><td class="value">{{ $user->alamat_ktp ?? '-' }}</td></tr>
            </table>
        </div>
        
        <div class="grid-spacer"></div>

        {{-- Kolom Kanan: Perusahaan --}}
        <div class="grid-column">
            <h2 class="section-title">Ketenagakerjaan</h2>
            <table class="info-table">
                <tr><td class="label">NIP</td><td class="value">{{ $user->nip ?? '-' }}</td></tr>
                <tr><td class="label">Status</td><td class="value">{{ $user->status_karyawan ?? '-' }}</td></tr>
                <tr><td class="label">Tgl Bergabung</td><td class="value">{{ $user->tanggal_bergabung ? $user->tanggal_bergabung->translatedFormat('d F Y') : '-' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- DOKUMEN & ADMINISTRASI --}}
    <h2 class="section-title">Administrasi & Kontak Darurat</h2>
    <div class="grid-container">
        <div class="grid-column">
            <table class="info-table">
                <tr><td class="label">NIK (KTP)</td><td class="value">{{ $user->nik ?? '-' }}</td></tr>
                <tr><td class="label">NPWP</td><td class="value">{{ $user->npwp ?? '-' }}</td></tr>
                <tr><td class="label">BPJS Kes</td><td class="value">{{ $user->bpjs_kesehatan ?? '-' }}</td></tr>
                <tr><td class="label">BPJS TK</td><td class="value">{{ $user->bpjs_ketenagakerjaan ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="grid-spacer"></div>
        <div class="grid-column">
            <table class="info-table">
                <tr><td class="label">Bank</td><td class="value">{{ $user->nama_bank ?? '-' }}</td></tr>
                <tr><td class="label">No. Rekening</td><td class="value">{{ $user->nomor_rekening ?? '-' }}</td></tr>
                <tr><td class="label">A.N Rekening</td><td class="value">{{ $user->pemilik_rekening ?? '-' }}</td></tr>
                <tr><td class="label">K. Darurat</td><td class="value" style="color: #DC2626;">{{ $user->kontak_darurat_nama ?? '-' }} ({{ $user->kontak_darurat_hubungan ?? '-' }})<br>{{ $user->kontak_darurat_nomor ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- RIWAYAT PENGALAMAN KERJA --}}
    <h2 class="section-title">Pengalaman Kerja</h2>
    @if($user->riwayatPekerjaan && $user->riwayatPekerjaan->count() > 0)
        <table class="history-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Perusahaan</th>
                    <th style="width: 25%;">Posisi</th>
                    <th style="width: 20%;">Periode</th>
                    <th style="width: 30%;">Deskripsi Singkat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($user->riwayatPekerjaan->sortByDesc('tanggal_selesai') as $pekerjaan)
                <tr>
                    <td class="font-bold">{{ $pekerjaan->nama_perusahaan ?? '-' }}</td>
                    <td style="color: #1E40AF; font-weight: bold;">{{ $pekerjaan->posisi ?? '-' }}</td>
                    <td class="text-gray">
                        {{ $pekerjaan->tanggal_mulai ? \Carbon\Carbon::parse($pekerjaan->tanggal_mulai)->format('M Y') : '-' }} 
                        - 
                        {{ $pekerjaan->tanggal_selesai ? \Carbon\Carbon::parse($pekerjaan->tanggal_selesai)->format('M Y') : 'Sekarang' }}
                    </td>
                    <td>{{ $pekerjaan->deskripsi_pekerjaan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">Belum ada data pengalaman kerja yang didaftarkan.</p>
    @endif

    {{-- RIWAYAT PENDIDIKAN --}}
    <h2 class="section-title">Riwayat Pendidikan</h2>
    @if($user->riwayatPendidikan && $user->riwayatPendidikan->count() > 0)
        <table class="history-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Jenjang</th>
                    <th style="width: 40%;">Institusi Pendidikan</th>
                    <th style="width: 30%;">Program Studi / Jurusan</th>
                    <th style="width: 15%;">Tahun Lulus</th>
                </tr>
            </thead>
            <tbody>
                @foreach($user->riwayatPendidikan->sortByDesc('tahun_lulus') as $pendidikan)
                <tr>
                    <td class="font-bold text-center">{{ $pendidikan->jenjang ?? '-' }}</td>
                    <td class="font-bold">{{ $pendidikan->nama_institusi ?? '-' }}</td>
                    <td>{{ $pendidikan->jurusan ?? '-' }}</td>
                    <td class="text-center text-gray">{{ $pendidikan->tahun_lulus ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">Belum ada data riwayat pendidikan yang didaftarkan.</p>
    @endif

</body>
</html>