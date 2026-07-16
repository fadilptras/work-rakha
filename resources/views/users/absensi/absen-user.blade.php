<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    @push('styles')
    <style>
        /* ===== ABSEN PAGE MOBILE STYLES ===== */

        /* Wrapper */
        .absen-page-wrapper { padding: 16px 16px 40px; }
        @media (min-width: 768px) {
            .absen-page-wrapper { padding: 24px 24px 48px; }
        }

        /* Back Button */
        .absen-back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px;
            background: #fff;
            border: 1.5px solid #dbeafe;
            border-radius: 999px;
            color: #1d4ed8;
            font-size: 0.82rem; font-weight: 600;
            text-decoration: none;
            transition: all 0.18s;
            margin-bottom: 16px;
        }
        .absen-back-btn:hover { background: #eff6ff; }

        /* Alert Card (Libur / Sabtu) */
        .absen-alert-card {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 16px;
            border: 1.5px solid;
        }
        .absen-alert-icon {
            width: 38px; height: 38px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 1rem;
        }
        .absen-alert-title { font-weight: 700; font-size: 0.9rem; margin-bottom: 2px; }
        .absen-alert-desc  { font-size: 0.8rem; line-height: 1.5; color: #374151; }

        /* Section Card (putih) */
        .absen-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 18px 16px;
            margin-bottom: 14px;
        }
        .absen-card-title {
            font-size: 1rem; font-weight: 800; color: #111827;
            margin-bottom: 4px;
        }
        .absen-card-subtitle {
            font-size: 0.8rem; color: #6b7280; margin-bottom: 16px;
        }

        /* Rekap Grid */
        .rekap-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .rekap-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 12px;
            border-radius: 10px;
        }
        .rekap-item-label { font-size: 0.78rem; font-weight: 600; }
        .rekap-item-value { font-size: 0.9rem; font-weight: 800; }
        .rekap-item.wide { grid-column: span 2; }
        .rekap-item.hadir   { background:#dcfce7; }
        .rekap-item.sakit   { background:#fee2e2; }
        .rekap-item.izin    { background:#fef9c3; }
        .rekap-item.cuti    { background:#dbeafe; }
        .rekap-item.alpa    { background:#f3f4f6; }
        .rekap-item.lembur  { background:#e0e7ff; }
        .rekap-item.terlambat { background:#ffedd5; }

        /* Terlambat row */
        .rekap-terlambat-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 12px; border-radius: 10px;
            background: #ffedd5;
            margin-top: 4px;
        }

        /* Tim Absensi */
        .tim-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .tim-item:last-child { border-bottom: none; }
        .tim-avatar {
            width: 38px; height: 38px;
            border-radius: 50%; object-fit: cover;
            margin-right: 10px; flex-shrink: 0;
        }
        .tim-name { font-size: 0.85rem; font-weight: 600; color: #1f2937; }
        .tim-badge {
            font-size: 0.7rem; font-weight: 700;
            padding: 3px 10px; border-radius: 999px;
            text-transform: capitalize;
        }

        /* Lembur Button */
        .absen-lembur-btn {
            display: block; width: 100%;
            padding: 15px 20px;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: #fff;
            font-size: 0.95rem; font-weight: 700;
            text-align: center;
            border: none; border-radius: 14px;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(109,40,217,0.35);
            transition: all 0.2s;
            letter-spacing: 0.01em;
        }
        .absen-lembur-btn:hover { opacity: 0.92; transform: translateY(-1px); }

        /* Absen Time Card */
        .absen-time-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
            margin-bottom: 14px;
        }
        .absen-time-card {
            border-radius: 12px; padding: 12px 14px;
        }
        .absen-time-label { font-size: 0.72rem; font-weight: 600; margin-bottom: 2px; }
        .absen-time-value { font-size: 1.6rem; font-weight: 800; line-height: 1.2; }
        .absen-time-sub   { font-size: 0.7rem; }

        /* Status Buttons */
        .status-btn-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
            margin-bottom: 16px;
        }
    </style>
    @endpush

    <div class="bg-gray-50 sm:bg-gradient-to-br sm:from-sky-50 sm:to-blue-100 min-h-screen">
        <div class="max-w-6xl mx-auto absen-page-wrapper">
            
            {{-- ALERT MESSAGES --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-4" role="alert">
                    <p class="font-bold">Terjadi Kesalahan</p>
                    <ul>
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif
            
            {{-- KONDISI 1: LIBUR TOTAL (Minggu / Tanggal Merah) --}}
            @if($isHoliday)
                <div class="absen-alert-card" style="background:#fff1f2; border-color:#fecaca;">
                    <div class="absen-alert-icon" style="background:#fee2e2;">
                        <i class="fas fa-calendar-times" style="color:#dc2626;"></i>
                    </div>
                    <div>
                        <p class="absen-alert-title" style="color:#dc2626;">Hari Libur</p>
                        <p class="absen-alert-desc">
                            Hari ini adalah
                            @if($holidayDb)
                                <span class="font-semibold" style="color:#dc2626;">{{ $holidayDb->keterangan }}</span>.
                            @else
                                <span class="font-semibold" style="color:#dc2626;">Hari Minggu</span>.
                            @endif
                            Absensi tetap dibuka khusus untuk petugas piket atau lembur.
                        </p>
                    </div>
                </div>

            {{-- KONDISI 2: SABTU OPSIONAL --}}
            @elseif($isSaturdayOpen)
                <div class="absen-alert-card" style="background:#eff6ff; border-color:#bfdbfe;">
                    <div class="absen-alert-icon" style="background:#dbeafe;">
                        <i class="fas fa-umbrella-beach" style="color:#2563eb;"></i>
                    </div>
                    <div>
                        <p class="absen-alert-title" style="color:#1d4ed8;">Hari Sabtu (Opsional)</p>
                        <p class="absen-alert-desc">
                            Kehadiran hari ini bersifat opsional. Tidak tercatat sebagai Alpha jika tidak hadir.
                        </p>
                    </div>
                </div>
            @endif

            {{-- TOMBOL KEMBALI KE DASHBOARD --}}
            <a href="{{ route('dashboard') }}" class="absen-back-btn">
                <i class="fas fa-arrow-left text-xs"></i>
                Kembali ke Dashboard
            </a>

            @if ($absensiHariIni)
                {{-- ========================================================= --}}
                {{-- KONDISI 1: SUDAH ABSEN MASUK --}}
                {{-- ========================================================= --}}
                <div class="flex flex-col lg:flex-row gap-6 items-start">
                    <div class="w-full lg:w-2/3 bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex flex-col md:flex-row items-start justify-between px-0">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Absensi Hari Ini</h2>
                                <p class="text-gray-600 mt-1">
                                    Status Kehadiran Anda : <span class="font-semibold capitalize">{{ $absensiHariIni->status }}</span>
                                </p>
                                <p class="text-gray-600 mt-0">
                                    Keterangan : <span class="font-semibold capitalize">{{ $absensiHariIni->keterangan }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-6 border-t pt-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
                                    <div class="flex items-center text-emerald-800 mb-2">
                                        <i class="fas fa-sign-in-alt mr-2"></i>
                                        <p class="font-semibold">Absen Masuk</p>
                                    </div>
                                    <p class="text-3xl font-bold text-gray-800">{{ \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') }} <span class="text-lg font-medium">WIB</span></p>
                                    @if($absensiHariIni->latitude && $absensiHariIni->longitude)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $absensiHariIni->latitude }},{{ $absensiHariIni->longitude }}" target="_blank" class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                                            <i class="fas fa-map-marker-alt mr-1"></i>Lihat Lokasi
                                        </a>
                                    @endif
                                </div>
                                <div class="bg-rose-50 p-4 rounded-lg border border-rose-200 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center text-rose-800 mb-2">
                                            <i class="fas fa-sign-out-alt mr-2"></i>
                                            <p class="font-semibold">Absen Keluar</p>
                                        </div>
                                        @if ($absensiHariIni->jam_keluar)
                                            <p class="text-3xl font-bold text-gray-800">{{ \Carbon\Carbon::parse($absensiHariIni->jam_keluar)->format('H:i') }} <span class="text-lg font-medium">WIB</span></p>
                                            @if($absensiHariIni->latitude_keluar && $absensiHariIni->longitude_keluar)
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $absensiHariIni->latitude_keluar }},{{ $absensiHariIni->longitude_keluar }}" target="_blank" class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>Lihat Lokasi
                                                </a>
                                            @endif
                                        @else
                                            <p class="text-3xl font-bold text-gray-400">--:--</p>
                                        @endif
                                    </div>
                                    @if (is_null($absensiHariIni->jam_keluar) && $absensiHariIni->status == 'hadir')
                                        <button type="button" id="btn-absen-keluar" class="w-full mt-3 bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition text-sm">
                                            Absen Keluar Sekarang
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @if ($absensiHariIni->jam_keluar && $absensiHariIni->status == 'hadir')
                                @if (is_null($lemburHariIni))
                                    <button type="button" id="btn-absen-lembur" class="w-full mt-6 bg-purple-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-purple-700 transition">
                                        Absen Lembur Sekarang
                                    </button>
                                @elseif (is_null($lemburHariIni->jam_keluar_lembur))
                                    <button type="button" id="btn-absen-keluar-lembur" class="w-full mt-6 bg-red-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-red-700 transition">
                                        Absen Keluar Lembur Sekarang
                                    </button>
                                @else
                                     <div class="mt-6 p-4 rounded-lg bg-green-100 text-green-700 font-semibold text-center">
                                         Absensi Lembur Hari Ini Selesai.
                                         <p class="text-xs font-normal">Waktu Lembur: {{ \Carbon\Carbon::parse($lemburHariIni->jam_masuk_lembur)->format('H:i') }} - {{ \Carbon\Carbon::parse($lemburHariIni->jam_keluar_lembur)->format('H:i') }}</p>
                                     </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    {{-- SIDEBAR REKAP --}}
                    <div class="w-full lg:w-1/3 space-y-4">
                        <div class="absen-card">
                            <h2 class="text-base font-bold text-gray-800 text-center mb-0.5">Rekap Bulan Ini</h2>
                            <p class="text-center text-gray-400 text-xs mb-3">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                            <div class="rekap-grid">
                                <div class="rekap-item hadir">
                                    <span class="rekap-item-label" style="color:#15803d;">Hadir</span>
                                    <span class="rekap-item-value" style="color:#15803d;">{{ $rekapAbsen['hadir'] }}</span>
                                </div>
                                <div class="rekap-item sakit">
                                    <span class="rekap-item-label" style="color:#dc2626;">Sakit</span>
                                    <span class="rekap-item-value" style="color:#dc2626;">{{ $rekapAbsen['sakit'] }}</span>
                                </div>
                                <div class="rekap-item izin">
                                    <span class="rekap-item-label" style="color:#b45309;">Izin</span>
                                    <span class="rekap-item-value" style="color:#b45309;">{{ $rekapAbsen['izin'] }}</span>
                                </div>
                                <div class="rekap-item cuti">
                                    <span class="rekap-item-label" style="color:#1d4ed8;">Cuti</span>
                                    <span class="rekap-item-value" style="color:#1d4ed8;">{{ $rekapAbsen['cuti'] }}</span>
                                </div>
                                <div class="rekap-item alpa">
                                    <span class="rekap-item-label" style="color:#374151;">Alpa</span>
                                    <span class="rekap-item-value" style="color:#374151;">{{ $rekapAbsen['tidak hadir'] }}</span>
                                </div>
                                <div class="rekap-item lembur">
                                    <span class="rekap-item-label" style="color:#4338ca;">Lembur</span>
                                    <span class="rekap-item-value" style="color:#4338ca;">{{ $rekapAbsen['lembur'] }}</span>
                                </div>
                            </div>
                            <div class="rekap-terlambat-row mt-2">
                                <span class="rekap-item-label" style="color:#c2410c;">Terlambat</span>
                                <span class="rekap-item-value" style="color:#c2410c;">{{ $rekapAbsen['terlambat'] }}</span>
                            </div>
                        </div>

                        @if(isset($daftarRekan) && count($daftarRekan) > 0)
                        <div class="absen-card">
                            <h2 class="text-base font-bold text-gray-800 text-center mb-3">Absensi Tim</h2>
                            <div>
                                @foreach($daftarRekan as $rekan)
                                @php
                                    $badgeStyle = match($rekan->status) {
                                        'hadir'  => 'background:#dcfce7; color:#15803d;',
                                        'sakit'  => 'background:#fee2e2; color:#dc2626;',
                                        'izin'   => 'background:#fef9c3; color:#b45309;',
                                        default  => 'background:#f3f4f6; color:#374151;',
                                    };
                                @endphp
                                <div class="tim-item">
                                    <div class="flex items-center">
                                        <img src="{{ $rekan->user->profile_picture ? asset('storage/' . $rekan->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($rekan->user->name ?? 'U').'&background=random&color=fff&size=64' }}"
                                             alt="{{ $rekan->user->name ?? '' }}" class="tim-avatar">
                                        <span class="tim-name">{{ $rekan->user->name }}</span>
                                    </div>
                                    <span class="tim-badge" style="{{ $badgeStyle }}">
                                        {{ str_replace('_', ' ', $rekan->status) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

            @else
                @if (isset($unfinishedAbsensi) && $unfinishedAbsensi)
                    {{-- ========================================================= --}}
                    {{-- KONDISI 2: SELESAIKAN ABSEN KELUAR (UNFINISHED) --}}
                    {{-- ========================================================= --}}
                    <div class="flex flex-col lg:flex-row gap-6 items-start">
                        <div class="w-full lg:w-2/3 bg-white p-6 md:p-8 rounded-xl shadow-sm">
                            <h2 class="text-2xl font-bold text-gray-800">Selesaikan Absen Keluar Sebelumnya</h2>
                            <p class="text-gray-600 mt-2">
                                Anda belum melakukan absen keluar untuk tanggal <strong>{{ \Carbon\Carbon::parse($unfinishedAbsensi->tanggal)->translatedFormat('l, j F Y') }}</strong>.
                                Silakan lengkapi data absensi Anda untuk melanjutkan.
                            </p>
                            <div class="mt-6 border-t pt-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
                                        <div class="flex items-center text-emerald-800 mb-2">
                                            <i class="fas fa-sign-in-alt mr-2"></i>
                                            <p class="font-semibold">Absen Masuk (Hari Sebelumnya)</p>
                                        </div>
                                        <p class="text-3xl font-bold text-gray-800">{{ \Carbon\Carbon::parse($unfinishedAbsensi->jam_masuk)->format('H:i') }} <span class="text-lg font-medium">WIB</span></p>
                                    </div>
                                    <div class="bg-rose-50 p-4 rounded-lg border border-rose-200 flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-center text-rose-800 mb-2">
                                                <i class="fas fa-sign-out-alt mr-2"></i>
                                                <p class="font-semibold">Absen Keluar (Sekarang)</p>
                                            </div>
                                            <p class="text-3xl font-bold text-gray-400">--:--</p>
                                        </div>
                                        <button type="button" id="btn-absen-keluar-unfinished" data-id="{{ $unfinishedAbsensi->id }}" class="w-full mt-3 bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition text-sm">
                                            Absen Keluar Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- SIDEBAR UNTUK UNFINISHED --}}
                        <div class="w-full lg:w-1/3 space-y-4">
                            <div class="absen-card">
                                <h2 class="text-base font-bold text-gray-800 text-center mb-0.5">Rekap Bulan Ini</h2>
                                <p class="text-center text-gray-400 text-xs mb-3">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                                <div class="rekap-grid">
                                    <div class="rekap-item hadir"><span class="rekap-item-label" style="color:#15803d;">Hadir</span><span class="rekap-item-value" style="color:#15803d;">{{ $rekapAbsen['hadir'] }}</span></div>
                                    <div class="rekap-item sakit"><span class="rekap-item-label" style="color:#dc2626;">Sakit</span><span class="rekap-item-value" style="color:#dc2626;">{{ $rekapAbsen['sakit'] }}</span></div>
                                    <div class="rekap-item izin"><span class="rekap-item-label" style="color:#b45309;">Izin</span><span class="rekap-item-value" style="color:#b45309;">{{ $rekapAbsen['izin'] }}</span></div>
                                    <div class="rekap-item cuti"><span class="rekap-item-label" style="color:#1d4ed8;">Cuti</span><span class="rekap-item-value" style="color:#1d4ed8;">{{ $rekapAbsen['cuti'] }}</span></div>
                                    <div class="rekap-item alpa"><span class="rekap-item-label" style="color:#374151;">Alpa</span><span class="rekap-item-value" style="color:#374151;">{{ $rekapAbsen['tidak hadir'] }}</span></div>
                                    <div class="rekap-item lembur"><span class="rekap-item-label" style="color:#4338ca;">Lembur</span><span class="rekap-item-value" style="color:#4338ca;">{{ $rekapAbsen['lembur'] }}</span></div>
                                </div>
                                <div class="rekap-terlambat-row mt-2">
                                    <span class="rekap-item-label" style="color:#c2410c;">Terlambat</span>
                                    <span class="rekap-item-value" style="color:#c2410c;">{{ $rekapAbsen['terlambat'] }}</span>
                                </div>
                            </div>
                            @if(isset($daftarRekan) && count($daftarRekan) > 0)
                            <div class="absen-card">
                                <h2 class="text-base font-bold text-gray-800 text-center mb-3">Absensi Tim</h2>
                                <div>
                                    @foreach($daftarRekan as $rekan)
                                    @php
                                        $badgeStyle = match($rekan->status) {
                                            'hadir'  => 'background:#dcfce7; color:#15803d;',
                                            'sakit'  => 'background:#fee2e2; color:#dc2626;',
                                            'izin'   => 'background:#fef9c3; color:#b45309;',
                                            default  => 'background:#f3f4f6; color:#374151;',
                                        };
                                    @endphp
                                    <div class="tim-item">
                                        <div class="flex items-center">
                                            <img src="{{ $rekan->user->profile_picture ? asset('storage/' . $rekan->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($rekan->user->name ?? 'U').'&background=random&color=fff&size=64' }}"
                                                 alt="{{ $rekan->user->name ?? '' }}" class="tim-avatar">
                                            <span class="tim-name">{{ $rekan->user->name }}</span>
                                        </div>
                                        <span class="tim-badge" style="{{ $badgeStyle }}">{{ str_replace('_', ' ', $rekan->status) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- MODAL UNFINISHED (LAYOUT: KIRI KAMERA SQUARE, KANAN INFO) --}}
                    <div id="modal-absen-keluar-unfinished" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
                        {{-- Lebar modal ditingkatkan --}}
                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl transform transition-all duration-300 scale-95 opacity-0">
                            <form action="{{ route('absen.keluar', $unfinishedAbsensi->id) }}" method="POST" enctype="multipart/form-data" id="form-absen-keluar-unfinished">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="latitude_keluar" id="latitude-keluar-unfinished">
                                <input type="hidden" name="longitude_keluar" id="longitude-keluar-unfinished">
                                <div class="p-5">
                                    <h3 class="text-xl font-bold text-gray-800">Form Absen Keluar</h3>
                                    <p class="text-gray-500 text-sm mt-1">Ambil foto selfie untuk konfirmasi absen keluar hari sebelumnya.</p>
                                    
                                    {{-- Layout Grid 2 Kolom --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                        {{-- Kolom Kiri: Kamera SQUARE --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Selfie <span class="text-red-500">*</span></label>
                                            {{-- Aspect-square (1:1) --}}
                                            <div id="camera-container-keluar-unfinished" class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 shadow-inner">
                                                <video id="video-keluar-unfinished" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                                <canvas id="canvas-keluar-unfinished" class="hidden"></canvas>
                                                <div id="snap-ui-keluar-unfinished" class="absolute inset-0 flex items-end justify-center p-4 bg-black bg-opacity-25">
                                                    <button type="button" id="snap-keluar-unfinished" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400" disabled>
                                                        <i class="fas fa-camera"></i>
                                                    </button>
                                                </div>
                                                <div id="preview-ui-keluar-unfinished" class="absolute inset-0 hidden">
                                                    <img id="preview-image-keluar-unfinished" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-black bg-opacity-40">
                                                        <button type="button" id="retake-btn-keluar-unfinished" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                                        <button type="button" id="use-photo-btn-keluar-unfinished" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-check mr-1.5"></i>Gunakan</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="upload-label-keluar-unfinished" class="hidden">
                                                <input name="lampiran_keluar" id="lampiran-keluar-unfinished" type="file" class="hidden" accept="image/*" />
                                            </div>
                                        </div>

                                        {{-- Kolom Kanan: Pesan --}}
                                        <div class="flex flex-col justify-center text-gray-500 text-sm italic">
                                            <p>Mohon pastikan wajah terlihat jelas.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-3 rounded-b-xl">
                                    <button type="button" id="btn-tutup-modal-keluar-unfinished" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 text-sm">Batal</button>
                                    <button type="submit" id="submit-button-keluar-unfinished" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 text-sm">Kirim</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- ========================================================= --}}
                    {{-- KONDISI 3: BELUM ABSEN (MAIN FORM - MODIFIKASI MOBILE) --}}
                    {{-- ========================================================= --}}
                    <div class="flex flex-col lg:flex-row gap-6 items-start">
                        
                        @if ($isHoliday)
                            {{-- TAMPILAN KHUSUS WEEKEND (BELUM ABSEN LEMBUR) --}}
                            <div class="w-full lg:w-2/3">
                                <div class="absen-card">
                                    <h2 class="absen-card-title">Absensi Lembur Akhir Pekan</h2>
                                    <p class="absen-card-subtitle">
                                        Notifikasi akhir pekan Anda tetap muncul. Anda dapat melakukan absensi lembur di bawah ini.
                                    </p>
                                    @if (is_null($lemburHariIni))
                                        <button type="button" id="btn-absen-lembur" class="absen-lembur-btn">
                                            Absen Lembur Sekarang
                                        </button>
                                    @elseif (is_null($lemburHariIni->jam_keluar_lembur))
                                        <div class="mb-4 p-4 rounded-xl border" style="background:#f5f3ff; border-color:#ddd6fe;">
                                            <p class="font-bold text-sm" style="color:#6d28d9;">Anda Sudah Masuk Lembur</p>
                                            <p class="text-3xl font-black mt-1" style="color:#1f2937;">{{ \Carbon\Carbon::parse($lemburHariIni->jam_masuk_lembur)->format('H:i') }} <span class="text-base font-medium">WIB</span></p>
                                            @if($lemburHariIni->keterangan)
                                                <p class="text-xs mt-1" style="color:#6b7280;">Keterangan: {{ $lemburHariIni->keterangan }}</p>
                                            @endif
                                        </div>
                                        <button type="button" id="btn-absen-keluar-lembur" class="w-full bg-red-600 text-white font-bold py-3.5 px-4 rounded-xl hover:bg-red-700 transition">
                                            Absen Keluar Lembur Sekarang
                                        </button>
                                    @else
                                        <div class="p-4 rounded-xl text-center" style="background:#dcfce7; color:#15803d;">
                                            <i class="fas fa-check-circle text-2xl mb-2"></i>
                                            <p class="font-bold">Absensi Lembur Hari Ini Selesai.</p>
                                            <p class="text-xs font-normal mt-1">Waktu Lembur: {{ \Carbon\Carbon::parse($lemburHariIni->jam_masuk_lembur)->format('H:i') }} - {{ \Carbon\Carbon::parse($lemburHariIni->jam_keluar_lembur)->format('H:i') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- TAMPILAN HARI BIASA (BELUM ABSEN) --}}
                            {{-- GANTI FORM INI (Cari: <form action="{{ route('absen.store') }}" ... id="form-absen" ...) --}}
        <form action="{{ route('absen.store') }}" method="POST" enctype="multipart/form-data" id="form-absen" class="w-full lg:w-2/3">
            @csrf
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            {{-- KONTAINER UTAMA (CARD PUTIH) --}}
            <div class="bg-white p-6 rounded-xl shadow-sm space-y-4 min-h-[500px] flex flex-col justify-between">
                
                {{-- WRAPPER KONTEN ATAS --}}
                <div>
                    {{-- 1. TANGGAL & JAM (Tampilan Mobile Sebaris) --}}
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div class="bg-gray-100 p-2 rounded-lg">
                            <label class="text-xs text-gray-500">Hari & Tanggal</label>
                            <p class="font-bold text-sm text-gray-800">{{ \Carbon\Carbon::now()->translatedFormat('l, j F Y') }}</p>
                        </div>
                        <div class="bg-gray-100 p-2 rounded-lg">
                            <label class="text-xs text-gray-500">Jam Saat Ini</label>
                            <p class="font-bold text-sm text-gray-800" id="jam-realtime"></p>
                        </div>
                    </div>

                    {{-- 2. STATUS KEHADIRAN --}}
                    <div class="mb-6"> 
                        <label class="block text-md font-medium text-gray-700 mb-3">Pilih Status Kehadiran</label>
                        <input type="hidden" name="status" id="status" value="hadir">
                        <div class="grid grid-cols-3 md:grid-cols-3 gap-3 md:gap-4 mt-2" id="status-buttons">
                            <button type="button" data-status="hadir" class="status-btn border border-gray-200 font-semibold py-2 rounded-lg transition-all duration-200 text-sm shadow-sm hover:shadow-md">Hadir</button>
                            <button type="button" data-status="izin" class="status-btn border border-gray-200 font-semibold py-2 rounded-lg transition-all duration-200 text-sm shadow-sm hover:shadow-md">Izin</button>
                            <button type="button" data-status="sakit" class="status-btn border border-gray-200 font-semibold py-2 rounded-lg transition-all duration-200 text-sm shadow-sm hover:shadow-md">Sakit</button>
                        </div>
                    </div>
                    
                    {{-- 3. KETERANGAN & KAMERA --}}
                    <div>
                        <label for="keterangan" class="block text-md font-medium text-gray-700 mb-3">
                            Keterangan & Lampiran <span id="keterangan-wajib" class="text-red-500 font-normal hidden">*</span>
                        </label>
                        
                        {{-- GRID: Kamera (Kiri) & Keterangan (Kanan) --}}
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            
                            {{-- KOLOM KIRI (2 Bagian): KAMERA --}}
                            <div class="md:col-span-2">
                                <div id="camera-container" class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 shadow-md">
                                    <video id="video" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                    <canvas id="canvas" class="hidden"></canvas>
                                    <div id="snap-ui" class="absolute inset-0 flex items-end justify-center p-4 bg-gradient-to-t from-black/50 to-transparent">
                                        <button type="button" id="snap" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400 transition-transform hover:scale-105" disabled>
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    </div>
                                    <div id="preview-ui" class="absolute inset-0 hidden bg-black">
                                        <img id="preview-image" src="" class="w-full h-full object-contain" alt="Pratinjau Foto"/>
                                        <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-gradient-to-t from-black/60 to-transparent">
                                            <button type="button" id="retake-btn" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center shadow-lg"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                            <button type="button" id="use-photo-btn" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center shadow-lg"><i class="fas fa-check mr-1.5"></i>Pakai</button>
                                        </div>
                                    </div>
                                </div>

                                <label for="lampiran" id="upload-label" class="flex flex-col items-center justify-center w-full h-full border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition min-h-[250px] hidden relative">
                                    <div class="flex flex-col items-center justify-center text-center p-2" id="upload-ui">
                                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-2">
                                            <i id="upload-icon" class="fas fa-paperclip text-xl"></i>
                                        </div>
                                        <p id="upload-text" class="mt-2 text-sm text-gray-500"><span class="font-semibold">Sertakan Lampiran</span></p>
                                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF</p>
                                    </div>
                                    <input name="lampiran" id="lampiran" type="file" class="hidden" accept="image/*,application/pdf" />
                                </label>
                            </div>

                            {{-- KOLOM KANAN (3 Bagian): KETERANGAN --}}
                            <div class="md:col-span-3">
                                <textarea name="keterangan" id="keterangan" rows="1" class="w-full p-3 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition-all text-sm h-full" placeholder="Contoh: Ada keperluan keluarga.">{{ old('keterangan') }}</textarea>
                            </div>
                            
                        </div>
                    </div>
                </div>

                {{-- 4. FOOTER: TOMBOL KIRIM --}}
                <div class="pt-2 mt-0">
                    <button type="submit" id="submit-button" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed text-sm flex items-center justify-center gap-1">
                        <i class="fas fa-paper-plane"></i> Kirim Absensi
                    </button>
                </div>

            </div>
        </form>                        
        @endif {{-- End of $isWeekend check --}}
                        
        {{-- SIDEBAR REKAP UNTUK BELUM ABSEN --}}
                        <div class="w-full lg:w-1/3 space-y-4">
                            <div class="absen-card">
                                <h2 class="text-base font-bold text-gray-800 text-center mb-0.5">Rekap Bulan Ini</h2>
                                <p class="text-center text-gray-400 text-xs mb-3">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                                <div class="rekap-grid">
                                    <div class="rekap-item hadir"><span class="rekap-item-label" style="color:#15803d;">Hadir</span><span class="rekap-item-value" style="color:#15803d;">{{ $rekapAbsen['hadir'] }}</span></div>
                                    <div class="rekap-item sakit"><span class="rekap-item-label" style="color:#dc2626;">Sakit</span><span class="rekap-item-value" style="color:#dc2626;">{{ $rekapAbsen['sakit'] }}</span></div>
                                    <div class="rekap-item izin"><span class="rekap-item-label" style="color:#b45309;">Izin</span><span class="rekap-item-value" style="color:#b45309;">{{ $rekapAbsen['izin'] }}</span></div>
                                    <div class="rekap-item cuti"><span class="rekap-item-label" style="color:#1d4ed8;">Cuti</span><span class="rekap-item-value" style="color:#1d4ed8;">{{ $rekapAbsen['cuti'] }}</span></div>
                                    <div class="rekap-item alpa"><span class="rekap-item-label" style="color:#374151;">Alpa</span><span class="rekap-item-value" style="color:#374151;">{{ $rekapAbsen['tidak hadir'] }}</span></div>
                                    <div class="rekap-item lembur"><span class="rekap-item-label" style="color:#4338ca;">Lembur</span><span class="rekap-item-value" style="color:#4338ca;">{{ $rekapAbsen['lembur'] }}</span></div>
                                </div>
                                <div class="rekap-terlambat-row mt-2">
                                    <span class="rekap-item-label" style="color:#c2410c;">Terlambat</span>
                                    <span class="rekap-item-value" style="color:#c2410c;">{{ $rekapAbsen['terlambat'] }}</span>
                                </div>
                            </div>
                            @if(isset($daftarRekan) && count($daftarRekan) > 0)
                                <div class="absen-card">
                                    <h2 class="text-base font-bold text-gray-800 text-center mb-3">Absensi Tim</h2>
                                    <div>
                                        @foreach($daftarRekan as $rekan)
                                        @php
                                            $badgeStyle = match($rekan->status) {
                                                'hadir'  => 'background:#dcfce7; color:#15803d;',
                                                'sakit'  => 'background:#fee2e2; color:#dc2626;',
                                                'izin'   => 'background:#fef9c3; color:#b45309;',
                                                default  => 'background:#f3f4f6; color:#374151;',
                                            };
                                        @endphp
                                        <div class="tim-item">
                                            <div class="flex items-center">
                                                <img src="{{ $rekan->user->profile_picture ? asset('storage/' . $rekan->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($rekan->user->name ?? 'U').'&background=random&color=fff&size=64' }}"
                                                     alt="{{ $rekan->user->name ?? '' }}" class="tim-avatar">
                                                <span class="tim-name">{{ $rekan->user->name }}</span>
                                            </div>
                                            <span class="tim-badge" style="{{ $badgeStyle }}">{{ str_replace('_', ' ', $rekan->status) }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- MODAL UNTUK ABSEN KELUAR (LAYOUT: KIRI KAMERA SQUARE, KANAN INFO) --}}
    @if ($absensiHariIni && is_null($absensiHariIni->jam_keluar) && $absensiHariIni->status == 'hadir')
    <div id="modal-absen-keluar" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        {{-- Lebar modal ditingkatkan --}}
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl transform transition-all duration-300 scale-95 opacity-0">
            <form action="{{ route('absen.keluar', $absensiHariIni->id) }}" method="POST" enctype="multipart/form-data" id="form-absen-keluar">
                @csrf
                @method('PATCH')
                <input type="hidden" name="latitude_keluar" id="latitude-keluar">
                <input type="hidden" name="longitude_keluar" id="longitude-keluar">
                <div class="p-5">
                    <h3 class="text-xl font-bold text-gray-800">Form Absen Keluar</h3>
                    <p class="text-gray-500 text-sm mt-1">Ambil foto selfie untuk konfirmasi absen keluar.</p>
                    
                    {{-- Grid 2 Kolom --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        {{-- Kiri: Kamera SQUARE --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Selfie <span class="text-red-500">*</span></label>
                            {{-- Aspect-square (1:1) --}}
                            <div id="camera-container-keluar" class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 shadow-inner">
                                <video id="video-keluar" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                <canvas id="canvas-keluar" class="hidden"></canvas>
                                <div id="snap-ui-keluar" class="absolute inset-0 flex items-end justify-center p-4 bg-black bg-opacity-25">
                                    <button type="button" id="snap-keluar" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400" disabled>
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div id="preview-ui-keluar" class="absolute inset-0 hidden">
                                    <img id="preview-image-keluar" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-black bg-opacity-40">
                                        <button type="button" id="retake-btn-keluar" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                        <button type="button" id="use-photo-btn-keluar" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-check mr-1.5"></i>Gunakan</button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload-label-keluar" class="hidden">
                                <input name="lampiran_keluar" id="lampiran-keluar" type="file" class="hidden" accept="image/*" />
                            </div>
                        </div>
                        
                        {{-- Kanan: Info --}}
                        <div class="flex flex-col justify-center text-gray-500 text-sm italic">
                             <p>Terima kasih atas kerja keras Anda hari ini. Hati-hati di jalan!</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-3 rounded-b-xl">
                    <button type="button" id="btn-tutup-modal-keluar" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 text-sm">Batal</button>
                    <button type="submit" id="submit-button-keluar" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 text-sm">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    
    {{-- MODAL UNTUK ABSEN LEMBUR (LAYOUT: KIRI KAMERA SQUARE, KANAN TEXTAREA) --}}
    @if ( ($absensiHariIni && $absensiHariIni->jam_keluar && $absensiHariIni->status == 'hadir') || $isHoliday )
    <div id="modal-absen-lembur" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        {{-- Lebar modal ditingkatkan --}}
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl transform transition-all duration-300 scale-95 opacity-0">
            <form action="{{ route('absen.lembur.store') }}" method="POST" enctype="multipart/form-data" id="form-absen-lembur">
                @csrf
                <input type="hidden" name="latitude_masuk" id="latitude-lembur">
                <input type="hidden" name="longitude_masuk" id="longitude-lembur">
                <div class="p-5">
                    <h3 class="text-xl font-bold text-gray-800">Form Absen Lembur</h3>
                    <p class="text-gray-500 text-sm mt-1">Ambil foto selfie dan isi keterangan untuk memulai lembur.</p>
                    
                    {{-- Grid 2 Kolom --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        {{-- Kiri: Kamera SQUARE --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Selfie <span class="text-red-500">*</span></label>
                            {{-- Aspect-square (1:1) --}}
                            <div id="camera-container-lembur" class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 shadow-inner">
                                <video id="video-lembur" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                <canvas id="canvas-lembur" class="hidden"></canvas>
                                <div id="snap-ui-lembur" class="absolute inset-0 flex items-end justify-center p-4 bg-black bg-opacity-25">
                                    <button type="button" id="snap-lembur" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400" disabled>
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div id="preview-ui-lembur" class="absolute inset-0 hidden">
                                    <img id="preview-image-lembur" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-black bg-opacity-40">
                                        <button type="button" id="retake-btn-lembur" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                        <button type="button" id="use-photo-btn-lembur" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-check mr-1.5"></i>Gunakan</button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload-label-lembur" class="hidden">
                                <input name="lampiran_masuk" id="lampiran-lembur" type="file" class="hidden" accept="image/*" />
                            </div>
                        </div>

                        {{-- Kanan: Textarea --}}
                        <div class="flex flex-col">
                            <label for="keterangan-lembur" class="block text-sm font-medium text-gray-700 mb-2">Keterangan Lembur <span class="text-red-500">*</span></label>
                            {{-- Tinggi textarea menyesuaikan container --}}
                            <textarea id="keterangan-lembur" name="keterangan" class="w-full p-3 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition-all text-sm h-full" placeholder="Contoh: Menyelesaikan laporan bulanan." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-3 rounded-b-xl">
                    <button type="button" id="btn-tutup-modal-lembur" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 text-sm">Batal</button>
                    <button type="submit" id="submit-button-lembur" class="bg-purple-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-purple-700 disabled:bg-gray-400 text-sm">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL UNTUK ABSEN KELUAR LEMBUR (LAYOUT: KIRI KAMERA SQUARE, KANAN INFO) --}}
    @if ($lemburHariIni && is_null($lemburHariIni->jam_keluar_lembur))
    <div id="modal-keluar-lembur" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        {{-- Lebar modal ditingkatkan --}}
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl transform transition-all duration-300 scale-95 opacity-0">
            <form action="{{ route('absen.lembur.keluar', $lemburHariIni->id) }}" method="POST" enctype="multipart/form-data" id="form-keluar-lembur">
                @csrf
                @method('PATCH')
                <input type="hidden" name="latitude_keluar" id="latitude-keluar-lembur">
                <input type="hidden" name="longitude_keluar" id="longitude-keluar-lembur">
                <div class="p-5">
                    <h3 class="text-xl font-bold text-gray-800">Form Absen Keluar Lembur</h3>
                    <p class="text-gray-500 text-sm mt-1">Ambil foto selfie untuk konfirmasi selesai lembur.</p>
                    
                    {{-- Grid 2 Kolom --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                         {{-- Kiri: Kamera SQUARE --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Selfie <span class="text-red-500">*</span></label>
                            {{-- Aspect-square (1:1) --}}
                            <div id="camera-container-keluar-lembur" class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 shadow-inner">
                                <video id="video-keluar-lembur" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                <canvas id="canvas-keluar-lembur" class="hidden"></canvas>
                                <div id="snap-ui-keluar-lembur" class="absolute inset-0 flex items-end justify-center p-4 bg-black bg-opacity-25">
                                    <button type="button" id="snap-keluar-lembur" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400" disabled>
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div id="preview-ui-keluar-lembur" class="absolute inset-0 hidden">
                                    <img id="preview-image-keluar-lembur" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-black bg-opacity-40">
                                        <button type="button" id="retake-btn-keluar-lembur" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                        <button type="button" id="use-photo-btn-keluar-lembur" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-check mr-1.5"></i>Gunakan</button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload-label-keluar-lembur" class="hidden">
                                <input name="lampiran_keluar" id="lampiran-keluar-lembur" type="file" class="hidden" accept="image/*" />
                            </div>
                        </div>

                        {{-- Kanan: Info --}}
                        <div class="flex flex-col justify-center text-gray-500 text-sm italic">
                             <p>Terima kasih sudah lembur hari ini. Selamat beristirahat.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-3 rounded-b-xl">
                    <button type="button" id="btn-tutup-modal-keluar-lembur" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 text-sm">Batal</button>
                    <button type="submit" id="submit-button-keluar-lembur" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 disabled:bg-gray-400 text-sm">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const jamElement = document.getElementById('jam-realtime');
        if(jamElement) {
            function updateJam() {
                jamElement.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            }
            setInterval(updateJam, 1000);
            updateJam();
        }

        // =========================================================================
        // GENERIC CAMERA LOGIC
        // =========================================================================
        window.cameraInstances = {};
        function setupCameraLogic(prefix) {
            const cameraContainer = document.getElementById(`camera-container${prefix}`);
            if (!cameraContainer) return;

            const fileInput = document.getElementById(`lampiran${prefix}`);
            const video = document.getElementById(`video${prefix}`);
            const canvas = document.getElementById(`canvas${prefix}`);
            const snapUI = document.getElementById(`snap-ui${prefix}`);
            const snapButton = document.getElementById(`snap${prefix}`);
            const previewUI = document.getElementById(`preview-ui${prefix}`);
            const previewImage = document.getElementById(`preview-image${prefix}`);
            const retakeButton = document.getElementById(`retake-btn${prefix}`);
            const usePhotoButton = document.getElementById(`use-photo-btn${prefix}`);
            
            let stream;

            const startCamera = async () => {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
                    video.srcObject = stream;
                    video.onloadedmetadata = () => { snapButton.disabled = false; };
                    video.classList.remove('hidden');
                    snapUI.classList.remove('hidden');
                    previewUI.classList.add('hidden');
                    cameraContainer.classList.remove('hidden');
                } catch (err) {
                    alert('Tidak bisa mengakses kamera. Pastikan Anda memberikan izin pada browser.');
                    cameraContainer.classList.add('hidden');
                }
            };
            
            const stopCamera = () => {
                if (stream) { stream.getTracks().forEach(track => track.stop()); }
                snapButton.disabled = true;
            };

            snapButton.addEventListener("click", function() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.save();
                ctx.scale(-1, 1);
                ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
                ctx.restore();

                previewImage.src = canvas.toDataURL('image/png');
                video.classList.add('hidden');
                snapUI.classList.add('hidden');
                previewUI.classList.remove('hidden');
                retakeButton.parentNode.classList.remove('hidden'); // Pastikan tombol terlihat
            });
            
            retakeButton.addEventListener('click', function() {
                video.classList.remove('hidden');
                snapUI.classList.remove('hidden');
                previewUI.classList.add('hidden');
            });

            usePhotoButton.addEventListener('click', function() {
                retakeButton.parentNode.classList.add('hidden'); // Sembunyikan tombol, biarkan foto
                canvas.toBlob(function(blob) {
                    const file = new File([blob], `selfie${prefix.replace('-', '_')}_${Date.now()}.png`, { type: "image/png" });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    stopCamera();
                    document.dispatchEvent(new CustomEvent(`photoReady${prefix}`, { detail: { isReady: true } }));
                }, 'image/png');
            });

            window.cameraInstances[prefix] = { startCamera, stopCamera };
        }
        
        setupCameraLogic('');
        setupCameraLogic('-keluar');
        setupCameraLogic('-keluar-unfinished');
        setupCameraLogic('-lembur');
        setupCameraLogic('-keluar-lembur');

        // =========================================================================
        // MAIN FORM LOGIC (ABSEN MASUK)
        // =========================================================================
        const formAbsen = document.getElementById('form-absen');
        if (formAbsen) {
            const hiddenStatusInput = document.getElementById('status');
            const submitButton = document.getElementById('submit-button');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const cameraContainer = document.getElementById('camera-container');
            const uploadLabel = document.getElementById('upload-label');
            let isLocationReady = false;
            let isPhotoReady = false;

            document.addEventListener('photoReady', e => {
                isPhotoReady = e.detail.isReady;
                const existingSuccessMsg = cameraContainer.parentNode.querySelector('.success-message');
                if (existingSuccessMsg) existingSuccessMsg.remove();
                
                if(isPhotoReady) {
                    const successMsg = document.createElement('div');
                    successMsg.className = 'success-message mt-2 text-center text-xs text-green-600 font-semibold p-1 bg-green-50 rounded-lg';
                    successMsg.innerHTML = `<i class="fas fa-check-circle"></i> Foto berhasil.`;
                    cameraContainer.parentNode.insertBefore(successMsg, cameraContainer.nextSibling);
                }
                checkFormReadiness();
            });

            const checkFormReadiness = () => {
                if (hiddenStatusInput.value === 'hadir') {
                    if (isLocationReady && isPhotoReady) {
                        submitButton.disabled = false;
                        submitButton.textContent = 'Kirim Absensi';
                    } else {
                        submitButton.disabled = true;
                        let text = 'Mohon ';
                        if(!isPhotoReady) text += 'Ambil Foto';
                        if(!isLocationReady && !isPhotoReady) text += ' & ';
                        if(!isLocationReady) text += 'Izinkan Lokasi';
                        submitButton.textContent = text;
                    }
                } else {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Kirim Absensi';
                }
            };

            const getLocation = () => {
                isLocationReady = false;
                checkFormReadiness();
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        latitudeInput.value = position.coords.latitude;
                        longitudeInput.value = position.coords.longitude;
                        isLocationReady = true;
                        checkFormReadiness();
                    },
                    () => { alert('Tidak bisa mendapatkan lokasi. Pastikan GPS Anda aktif dan berikan izin pada browser.'); isLocationReady = false; checkFormReadiness(); },
                    { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
                );
            };

            const selectedStyles = {
                hadir: 'bg-emerald-500 text-white border-emerald-500',
                izin: 'bg-amber-500 text-white border-amber-500',
                sakit: 'bg-red-500 text-white border-red-500'
            };

            const setActiveButton = (status) => {
                document.querySelectorAll('.status-btn').forEach(btn => {
                    btn.classList.remove(...Object.values(selectedStyles).join(' ').split(' '));
                    btn.classList.add('border-gray-300', 'bg-white', 'text-gray-700');
                });
                const activeButton = document.querySelector(`.status-btn[data-status="${status}"]`);
                if (activeButton) {
                    activeButton.classList.remove('border-gray-300', 'bg-white', 'text-gray-700');
                    activeButton.classList.add(...selectedStyles[status].split(' '));
                }
            };

            const toggleUiForStatus = (status) => {
                const keteranganWajibSpan = document.getElementById('keterangan-wajib');
                const existingSuccessMsg = cameraContainer.parentNode.querySelector('.success-message');
                if (existingSuccessMsg) existingSuccessMsg.remove();
                
                isPhotoReady = false;
                
                if (status === 'hadir') {
                    keteranganWajibSpan.classList.add('hidden');
                    uploadLabel.classList.add('hidden');
                    window.cameraInstances[''].startCamera();
                    getLocation();
                } else {
                    keteranganWajibSpan.classList.remove('hidden');
                    window.cameraInstances[''].stopCamera();
                    cameraContainer.classList.add('hidden');
                    uploadLabel.classList.remove('hidden');
                }
                checkFormReadiness();
            };
            
            document.getElementById('status-buttons').addEventListener('click', function(e) {
                // Fix: Gunakan .closest() untuk menangani klik pada icon di dalam tombol
                const btn = e.target.closest('.status-btn');
                if (btn) {
                    const selectedStatus = btn.dataset.status;
                    hiddenStatusInput.value = selectedStatus;
                    setActiveButton(selectedStatus);
                    toggleUiForStatus(selectedStatus);
                }
            });

            setActiveButton(hiddenStatusInput.value);
            toggleUiForStatus(hiddenStatusInput.value);
        }

        // =========================================================================
        // MODAL ACTIVATION LOGIC
        // =========================================================================
        function setupModalLogic(btnId, modalId, prefix) {
            const btn = document.getElementById(btnId);
            const modal = document.getElementById(modalId);
            if (!btn || !modal) return;

            const modalContent = modal.querySelector('.transform');
            const btnTutupModal = document.getElementById(`btn-tutup-modal${prefix}`);
            const submitBtn = document.getElementById(`submit-button${prefix}`);
            const latitudeInput = document.getElementById(`latitude${prefix}`);
            const longitudeInput = document.getElementById(`longitude${prefix}`);
            const keteranganInput = document.getElementById(`keterangan${prefix}`);
            
            let isLocationReady = false;
            let isPhotoReady = false;

            const checkReadiness = () => {
                const isKeteranganReady = keteranganInput ? keteranganInput.value.trim() !== '' : true;
                const allReady = isLocationReady && isPhotoReady && isKeteranganReady;
                
                submitBtn.disabled = !allReady;

                if (submitBtn.dataset.readyText) {
                    submitBtn.textContent = submitBtn.dataset.readyText;
                }
            };

            const getLocation = () => {
                isLocationReady = false;
                checkReadiness();
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        latitudeInput.value = pos.coords.latitude;
                        longitudeInput.value = pos.coords.longitude;
                        isLocationReady = true;
                        checkReadiness();
                    },
                    () => { alert('Gagal mendapatkan lokasi.'); isLocationReady = false; checkReadiness(); },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            };

            document.addEventListener(`photoReady${prefix}`, e => {
                isPhotoReady = e.detail.isReady;
                checkReadiness();
                const cameraContainer = document.getElementById(`camera-container${prefix}`);
                const existingSuccessMsg = cameraContainer.parentNode.querySelector('.success-message');
                if (existingSuccessMsg) existingSuccessMsg.remove();
                
                if(isPhotoReady) {
                    const successMsg = document.createElement('div');
                    successMsg.className = 'success-message mt-2 text-center text-sm text-green-600 font-semibold p-2 bg-green-50 rounded-lg';
                    successMsg.innerHTML = `<i class="fas fa-check-circle"></i> Foto berhasil diambil.`;
                    cameraContainer.parentNode.insertBefore(successMsg, cameraContainer.nextSibling);
                }
            });

            if (keteranganInput) {
                keteranganInput.addEventListener('input', checkReadiness);
            }

            const openModal = () => {
                modal.classList.remove('hidden'); modal.classList.add('flex');
                setTimeout(() => modalContent.classList.remove('scale-95', 'opacity-0'), 10);
                window.cameraInstances[prefix].startCamera();
                getLocation();
                submitBtn.dataset.readyText = submitBtn.textContent;
                checkReadiness(); // Jalankan sekali saat buka
            };

            const closeModal = () => {
                modalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden'); modal.classList.remove('flex');
                    window.cameraInstances[prefix].stopCamera();
                    const cameraContainer = document.getElementById(`camera-container${prefix}`);
                    const existingSuccessMsg = cameraContainer.parentNode.querySelector('.success-message');
                    if (existingSuccessMsg) existingSuccessMsg.remove();
                    if(keteranganInput) keteranganInput.value = '';
                    isLocationReady = isPhotoReady = false;
                    checkReadiness();
                }, 200);
            };

            btn.addEventListener('click', openModal);
            btnTutupModal.addEventListener('click', closeModal);
        }

        setupModalLogic('btn-absen-keluar', 'modal-absen-keluar', '-keluar');
        setupModalLogic('btn-absen-keluar-unfinished', 'modal-absen-keluar-unfinished', '-keluar-unfinished');
        setupModalLogic('btn-absen-lembur', 'modal-absen-lembur', '-lembur');
        setupModalLogic('btn-absen-keluar-lembur', 'modal-keluar-lembur', '-keluar-lembur');
    });
    </script>
    @endpush
</x-layout-users>