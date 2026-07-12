<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* == Modern Glassmorphism Blue Theme == */
        
        /* Custom Scrollbar Modern */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #93c5fd; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #60a5fa; }
        
        /* == Kalender (Ukuran Asli Dipertahankan) == */
        .fc {
            border: none !important;
            background: #F0F9FF; /* bg-blue-50 */
            border-radius: 1rem;
            padding: 1rem;
        }
        .fc .fc-toolbar-title { font-size: 1.3rem; font-weight: 700; color: #111827; }
        .fc .fc-button {
            background: transparent !important; border: none !important; box-shadow: none !important;
            color: #6B7280; transition: all 0.2s; padding: 0 !important;
            width: 38px; height: 38px; display: flex; justify-content: center; align-items: center; border-radius: 9999px;
        }
        .fc .fc-button:hover { color: #111827; background: #DBEAFE !important; transform: scale(1.1); }
        .fc .fc-button .fc-icon { font-size: 1.25rem; }
        .fc .fc-col-header-cell { border: none !important; padding: 6px 0; }
        .fc .fc-col-header-cell-cushion { color: #6b7280; font-weight: 600; font-size: 0.9rem; }
        .fc .fc-daygrid-day-frame {
            display: flex; /* Aktifkan flexbox untuk kontrol layout lebih baik */
            flex-direction: column;
            align-items: center;
            padding-top: 4px;
        }
        .fc .fc-daygrid-day-number {
            width: 34px; height: 34px; line-height: 34px; text-align: center; border-radius: 9999px;
            font-weight: 500; transition: all 0.2s; font-size: 0.9rem; color: #374151;
            flex-shrink: 0; /* Pastikan nomor tidak mengecil */
        }
        .fc .fc-day-other .fc-daygrid-day-number { color: #d1d5db; }
        .fc .fc-daygrid-day:not(.fc-day-other):hover .fc-daygrid-day-number { background-color: #DBEAFE; }
        .fc .fc-day-today .fc-daygrid-day-number {
            font-weight: 700; color: #1D4ED8; background: #BFDBFE;
            box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.4);
        }
        .fc .selected-date .fc-daygrid-day-number { background: #111827; color: #fff !important; font-weight: 700; }
        
        /* Jarak Scrollbar Kalender dibuat seimbang */
        .fc .fc-view-harness {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        /* ===== MODIFIKASI TAMPILAN AGAR LEBIH RAPIH (DESKTOP) ===== */

        /* 1. Atur container agenda agar rapi di bawah tanggal */
        .fc .fc-daygrid-day-events {
            margin-top: 4px; /* Beri jarak dari angka tanggal */
            width: 100%;
            padding: 0 4px; /* Beri sedikit padding horizontal */
        }
        
        /* 2. Rapikan tampilan setiap item agenda */
        .fc-daygrid-event {
            background-color: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            border-left-width: 3px !important;
            color: #374151 !important;
            font-size: 0.7rem !important;
            font-weight: 600;
            margin: 2px 0 !important; /* Rapikan margin, hanya atas-bawah */
            padding: 3px 6px !important; /* Sedikit tambah padding vertikal */
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease-in-out;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-align: left; /* Teks rata kiri */
        }

        .fc-daygrid-event:hover {
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }
        
        /* 3. Rapikan tampilan link "+ more" */
        .fc .fc-daygrid-more-link {
            color: #4338ca;
            font-size: 0.7rem; /* Samakan font size dengan agenda */
            font-weight: 600;
            text-decoration: none;
            padding: 3px 6px;
            border-radius: 6px;
            margin: 2px auto 0 auto; /* Posisi di tengah */
            display: inline-block; /* Agar bisa di-style */
        }
        .fc .fc-daygrid-more-link:hover {
            background-color: #e0e7ff;
            color: #312e81;
        }
        
        
        /* ===== MODIFIKASI TAMPILAN AGAR LEBIH RAPIH (MOBILE) ===== */
        @media (max-width: 768px) {

            /* Atur container agenda di mobile */
            .fc .fc-daygrid-day-events {
                margin-top: 2px; /* Jarak lebih kecil untuk mobile */
                padding: 0 2px;
            }

            /* Style dasar untuk Chip/Tag di mobile */
            .fc-daygrid-event {
                display: flex !important;
                align-items: center !important;
                background-color: #eef2ff !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 9999px !important;
                padding: 3px 8px 3px 4px !important;
                margin: 2px auto !important; /* Posisikan di tengah */
                width: 95%; /* Lebar konsisten */
                max-width: 120px; /* Batasi lebar maksimal */
                justify-content: flex-start;
            }

            /* Dot berwarna di dalam Chip */
            .fc-daygrid-event::before {
                content: '';
                display: inline-block;
                width: 6px;
                height: 6px;
                border-radius: 50%;
                margin-right: 6px;
                background-color: var(--fc-event-bg-color);
                flex-shrink: 0;
            }

            /* Teks di dalam Chip */
            .fc-daygrid-event .fc-event-title {
                font-size: 0.5rem !important;
                font-weight: 600;
                color: #4338ca !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .fc-daygrid-event:hover {
                background-color: #e0e7ff !important;
                transform: none !important;
                box-shadow: none !important;
            }

            /* Penyesuaian UI lainnya untuk mobile */
            .fc .fc-toolbar-title { font-size: 1.1rem; }
            .fc .fc-button { width: 32px; height: 32px; }
            .fc .fc-col-header-cell-cushion { font-size: 0.8rem; }
            .fc .fc-daygrid-day-number { width: 28px; height: 28px; line-height: 28px; font-size: 0.8rem; }
            .fc-event-time { display: none !important; }
        }

        /* == PENGECUALIAN KHUSUS HARI LIBUR == */
        .fc-daygrid-event.holiday-event {
            background-color: var(--fc-event-bg-color) !important; /* Kembalikan ke warna asli merah/oranye */
            border: none !important; /* Hilangkan border abu-abu */
            padding: 4px 6px !important;
            border-radius: 6px !important;
        }
        
        .fc-daygrid-event.holiday-event .fc-event-title {
            color: #ffffff !important; /* Paksa teks berwarna putih */
            font-weight: 700 !important;
        }

        /* Untuk Mobile: Sembunyikan dot/titik biru khusus hari libur */
        @media (max-width: 768px) {
            .fc-daygrid-event.holiday-event::before {
                display: none !important;
            }
        }

        /* == Notifikasi Bar Mobile == */
        .mobile-notif-bar {
            background: #1a1f2e;
            color: #e2e8f0;
            border-radius: 999px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.82rem;
            gap: 8px;
        }
        .mobile-notif-bar .notif-text { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mobile-notif-bar .notif-lihat { color: #93c5fd; font-weight: 600; white-space: nowrap; flex-shrink: 0; }

        /* == Weekly Strip Kalender Mobile == */
        .weekly-strip {
            background: #fff;
            border-radius: 1rem;
            padding: 14px 16px;
            box-shadow: 0 2px 12px rgba(59,130,246,0.08);
            border: 1px solid #e0e7ef;
        }
        .weekly-strip .week-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .weekly-strip .week-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e3a5f;
            text-align: center;
            flex: 1;
        }
        .weekly-strip .week-nav-btn {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #6b7280;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.15s;
        }
        .weekly-strip .week-nav-btn:hover { background: #dbeafe; color: #1d4ed8; }
        .weekly-strip .days-row {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            text-align: center;
        }
        .weekly-strip .day-label {
            font-size: 0.72rem;
            color: #9ca3af;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .weekly-strip .day-num {
            width: 34px; height: 34px;
            line-height: 34px;
            border-radius: 50%;
            font-size: 0.85rem;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: all 0.15s;
            margin: 0 auto;
        }
        .weekly-strip .day-num:hover { background: #dbeafe; }
        .weekly-strip .day-num.today {
            background: #1d4ed8;
            color: #fff;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(29,78,216,0.3);
        }
        .weekly-strip .day-num.selected {
            background: #111827;
            color: #fff;
            font-weight: 700;
        }
        .weekly-strip .day-num.has-event::after {
            content: '';
            display: block;
            width: 4px; height: 4px;
            border-radius: 50%;
            background: #3b82f6;
            margin: 1px auto 0;
        }
        .weekly-strip .day-num.today.has-event::after { background: #fff; }

        /* == Absensi Buttons Mobile == */
        .absensi-grid-mobile {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .absensi-btn-mobile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1f2937;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 1px 6px rgba(0,0,0,0.04);
        }
        .absensi-btn-mobile:hover { box-shadow: 0 4px 14px rgba(59,130,246,0.13); transform: translateY(-1px); }
        .absensi-btn-mobile.active-btn {
            background: #1d4ed8;
            color: #fff;
            border-color: #1d4ed8;
        }
        .absensi-btn-mobile.active-btn i { color: #fff; }
        .absensi-btn-mobile .btn-icon {
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
        }
        .absensi-btn-mobile.active-btn .btn-icon { background: rgba(255,255,255,0.2); }

        /* == Welcome Card Mobile == */
        .mobile-welcome-card {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%);
            border-radius: 1rem;
            padding: 18px 20px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .mobile-welcome-card::before {
            content: '';
            position: absolute;
            right: -30px; top: -30px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }
        .mobile-welcome-card::after {
            content: '';
            position: absolute;
            right: 30px; bottom: -40px;
            width: 90px; height: 90px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .mobile-welcome-card .avatar-circle {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: rgba(255,255,255,0.18);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            position: relative; z-index: 1;
            border: 2px solid rgba(255,255,255,0.3);
        }

        /* == Agenda Hari Ini Mobile == */
        .mobile-agenda-card {
            background: #fff;
            border-radius: 1rem;
            padding: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 12px rgba(59,130,246,0.07);
        }
        .mobile-agenda-empty {
            background: #f0f4ff;
            border-radius: 0.85rem;
            padding: 28px 16px;
            text-align: center;
            border: 1px solid #dbeafe;
        }

    </style>
    @endpush

    <div class="flex flex-col h-full bg-gradient-to-br from-sky-50 to-blue-100">
        <main class="flex-1 overflow-y-auto min-h-screen p-0 lg:p-6">

            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-6 mx-6 lg:mx-0" role="alert">
                    <p class="font-bold">Sukses!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 mx-6 lg:mx-0 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 text-sm rounded-md" role="alert">
                    <p class="font-bold">Terjadi Kesalahan</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ===== TAMPILAN MOBILE (tersembunyi di desktop) ===== --}}
            <div class="sm:hidden flex flex-col bg-gray-50 pb-4">

                {{-- Welcome Card --}}
                <div class="px-4 pt-4 pb-3">
                    <div class="mobile-welcome-card">
                        <div style="position:relative;z-index:1;">
                            <p class="text-sm text-blue-100 font-medium mb-0.5">Selamat datang kembali,</p>
                            <h2 class="text-2xl font-bold text-white leading-tight">{{ Auth::user()->name }}</h2>
                        </div>
                        <div class="avatar-circle">
                            <i class="fas fa-user-tie text-2xl text-white/80"></i>
                        </div>
                    </div>
                </div>

                {{-- Divisi Info --}}
                <div class="px-4 pb-3">
                    <div class="flex items-center gap-2 bg-white rounded-xl px-4 py-2.5 shadow-sm border border-blue-50">
                        <i class="fas fa-briefcase text-blue-500 text-sm"></i>
                        <span class="text-sm text-gray-600">Divisi: <span class="font-semibold text-gray-800">{{ Auth::user()->divisi ?? '-' }}</span></span>
                    </div>
                </div>

                {{-- Absensi Section --}}
                <div class="px-4 pb-4">
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 text-base mb-3">Absensi</h3>
                        <div class="absensi-grid-mobile">
                            <a href="{{ route('absen') }}" class="absensi-btn-mobile active-btn">
                                <div class="btn-icon">
                                    <i class="fas fa-fingerprint"></i>
                                </div>
                                <span>Absen</span>
                            </a>
                            <a href="{{ route('aktivitas.index') }}" class="absensi-btn-mobile">
                                <div class="btn-icon" style="background:#f3f0ff;">
                                    <i class="fas fa-tasks" style="color:#7c3aed;"></i>
                                </div>
                                <span>Aktivitas</span>
                            </a>
                            <a href="{{ route('cuti.create') }}" class="absensi-btn-mobile">
                                <div class="btn-icon" style="background:#f0fdf4;">
                                    <i class="fas fa-calendar-times" style="color:#16a34a;"></i>
                                </div>
                                <span>Cuti</span>
                            </a>
                            <a href="{{ route('rekap_absen.index') }}" class="absensi-btn-mobile">
                                <div class="btn-icon" style="background:#fffbeb;">
                                    <i class="fas fa-history" style="color:#d97706;"></i>
                                </div>
                                <span>Rekap</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Weekly Calendar Strip --}}
                <div class="px-4 pb-4">
                    <div class="weekly-strip">
                        <div class="week-header">
                            <button class="week-nav-btn" id="mobile-prev-week"><i class="fas fa-chevron-left"></i></button>
                            <div class="week-title" id="mobile-week-title">Memuat...</div>
                            <button class="week-nav-btn" id="mobile-next-week"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="days-row" id="mobile-days-labels">
                            <div class="day-label">Min</div>
                            <div class="day-label">Sen</div>
                            <div class="day-label">Sel</div>
                            <div class="day-label">Rab</div>
                            <div class="day-label">Kam</div>
                            <div class="day-label">Jum</div>
                            <div class="day-label">Sab</div>
                        </div>
                        <div class="days-row" id="mobile-days-nums"></div>
                    </div>
                </div>

                {{-- Agenda Hari Ini --}}
                <div class="px-4 pb-4">
                    <div class="mobile-agenda-card">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-bold text-gray-800 text-base">Agenda Hari Ini</h3>
                            <button id="mobile-add-agenda-btn" class="w-9 h-9 rounded-full bg-gray-900 hover:bg-gray-800 text-white flex items-center justify-center shadow-md transition-all duration-200 hover:scale-105">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                        <div id="mobile-agenda-today-container">
                            <div class="mobile-agenda-empty">
                                <i class="fas fa-calendar-alt text-3xl text-blue-300 mb-3"></i>
                                <p class="font-semibold text-blue-700 text-sm">Tidak ada agenda</p>
                                <p class="text-xs text-blue-400 mt-1">Tap + untuk menambah jadwal.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notifikasi Bar --}}
                <div class="px-4 pb-2">
                    <div class="mobile-notif-bar">
                        <i class="fas fa-bell text-gray-400 flex-shrink-0 mr-1"></i>
                        @php
                            $latestNotif = Auth::user()->notifications->first();
                        @endphp
                        <span class="notif-text">
                            @if($latestNotif)
                                {{ $latestNotif->data['title'] ?? 'Notifikasi baru' }}
                            @else
                                Tidak ada notifikasi baru.
                            @endif
                        </span>
                        <a href="{{ route('notifikasi.index') }}" class="notif-lihat">Lihat &rsaquo;</a>
                    </div>
                </div>

            </div>
            {{-- ===== AKHIR TAMPILAN MOBILE ===== --}}

            {{-- ===== TAMPILAN DESKTOP (tersembunyi di mobile) ===== --}}
            <div class="hidden sm:block">
                <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-6">

                    {{-- KOLOM PROFIL: Sangat Compact, Hanya Foto, Nama, dan Divisi --}}
                    <div class="lg:col-span-1 space-y-4">
                        <div class="bg-gradient-to-br from-blue-600 to-blue-700 text-white p-4 rounded-2xl shadow-xl shadow-blue-500/20">
                            <h2 class="text-xl font-bold">Welcome Back, {{ Auth::user()->name }}!</h2>
                            <p class="text-xs mt-1 text-blue-100">Semoga harimu produktif.</p>
                        </div>
                        
                        <div class="bg-white/60 backdrop-blur-lg border border-white/30 shadow-xl shadow-blue-500/20 p-4 rounded-2xl">
                            {{-- Bagian Header Profil (Horizontal) --}}
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 shrink-0 aspect-square overflow-hidden rounded-full border-2 border-white/50 shadow-sm">
                                    <img class="w-full h-full object-cover" src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=random&color=fff&size=128' }}" alt="Foto Profil">
                                </div>
                                <div class="overflow-hidden">
                                    <p class="font-bold text-lg text-gray-800 leading-tight truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs font-semibold text-blue-600 mt-0.5 truncate">Divisi: {{ Auth::user()->divisi ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 xl:col-span-3 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-white/60 backdrop-blur-lg border border-white/30 shadow-xl shadow-blue-500/20 p-6 rounded-2xl flex flex-col">
                                <h3 class="font-bold text-gray-900 mb-8 text-xl">Absensi</h3>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <a href="{{ route('absen') }}" class="bg-white/80 hover:bg-white p-4 rounded-xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 border border-blue-200 hover:border-blue-400 hover:shadow-lg hover:-translate-y-1"><i class="fas fa-fingerprint text-2xl text-blue-600 mb-2"></i><span class="font-semibold text-sm text-gray-700">Absen</span></a>
                                        <a href="{{ route('aktivitas.index') }}" class="bg-white/80 hover:bg-white p-4 rounded-xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 border border-purple-200 hover:border-purple-400 hover:shadow-lg hover:-translate-y-1"><i class="fas fa-tasks text-2xl text-purple-600 mb-2"></i><span class="font-semibold text-sm text-gray-700">Aktivitas</span></a>
                                        <a href="{{ route('cuti.create') }}" class="bg-white/80 hover:bg-white p-4 rounded-xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 border border-green-200 hover:border-green-400 hover:shadow-lg hover:-translate-y-1"><i class="fas fa-calendar-alt text-2xl text-green-600 mb-2"></i><span class="font-semibold text-sm text-gray-700">Cuti</span></a>
                                        <a href="{{ route('rekap_absen.index') }}" class="bg-white/80 hover:bg-white p-4 rounded-xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 border border-yellow-200 hover:border-yellow-400 hover:shadow-lg hover:-translate-y-1"><i class="fas fa-history text-2xl text-yellow-600 mb-2"></i><span class="font-semibold text-sm text-gray-700">Rekap</span></a>
                                    </div>
                            </div>
                            <div class="bg-gradient-to-br from-gray-900 to-slate-800 text-white p-6 rounded-2xl shadow-xl shadow-slate-900/40 border border-slate-700 flex flex-col">
                                <div class="flex justify-between items-center mb-4 flex-shrink-0">
                                    <h3 class="font-bold text-white text-xl">Notifikasi</h3>
                                    <a href="{{ route('notifikasi.index') }}" class="relative flex items-center space-x-2 text-gray-300 hover:text-white transition-colors duration-200">
                                        <span class="text-sm font-semibold">Lihat Semua</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                        @if (Auth::user()->unreadNotifications->count() > 0)
                                            <span class="absolute top-0 right-0 inline-flex items-center justify-center h-4 w-4 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                                {{ Auth::user()->unreadNotifications->count() }}
                                            </span>
                                        @endif
                                    </a>
                                </div>
                                <div class="space-y-3 flex-grow flex flex-col justify-center">
                                    @forelse(Auth::user()->notifications->take(2) as $notification)
                                    <a href="{{ $notification->data['url'] ?? '#' }}" class="block p-3 rounded-lg {{ $notification->read_at ? 'bg-gray-800/50' : 'bg-blue-800' }} hover:bg-gray-700/70 transition-colors duration-150">
                                        <div class="flex items-start">
                                            <i class="fas {{ $notification->data['icon'] ?? 'fa-info-circle' }} text-xl text-white mt-1 mr-3"></i>
                                            <div>
                                                <p class="font-semibold text-sm text-gray-100">{{ $notification->data['title'] ?? 'Notifikasi Baru' }}</p>
                                                <p class="text-xs text-gray-300 line-clamp-1">{{ $notification->data['message'] ?? 'Tidak ada detail' }}</p>
                                            </div>
                                        </div>
                                    </a>
                                    @empty
                                    <div class="flex-grow flex items-center justify-center"><p class="text-center text-gray-400 py-4 text-sm">Tidak ada notifikasi baru.</p></div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/60 backdrop-blur-lg border border-white/30 shadow-xl shadow-blue-500/20 md:p-6 rounded-2xl">
                            <div class="flex flex-col md:flex-row gap-4 md:gap-8">
                                <div class="w-full lg:w-3/5">
                                    <div id="mini-calendar"></div>
                                </div>
                                <div class="hidden lg:block w-1 bg-blue-200"></div>
                                <div class="w-full lg:w-2/5 flex flex-col px-4 pb-4 md:px-0 md:pb-0">
                                    <div class="flex justify-between items-center mb-4 flex-shrink-0">
                                        <h3 id="agenda-list-title" class="font-bold text-gray-900 text-lg">Agenda Minggu Ini</h3>
                                        <button id="add-agenda-btn" class="bg-gray-900 hover:bg-gray-800 text-white font-bold w-10 h-10 rounded-full transition-all duration-200 flex items-center justify-center shadow-md hover:scale-105">
                                            <i class="fas fa-plus text-sm"></i>
                                        </button>
                                    </div>
                                    <div id="agenda-list-container" class="h-80 overflow-y-auto pr-2 space-y-3 -mr-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ===== AKHIR TAMPILAN DESKTOP ===== --}}

        </main>
    </div>

    {{-- KONTEN MODAL --}}
    <div id="agenda-modal" class="fixed inset-0 bg-black bg-opacity-60 z-40 hidden flex items-center justify-center p-4">
        <div class="bg-white/80 backdrop-blur-xl border border-white/30 rounded-2xl shadow-2xl shadow-blue-900/20 w-full max-w-3xl mx-4 p-6 flex flex-col max-h-[90vh] transform transition-all" id="agenda-modal-content">
            
            <div class="flex-shrink-0 flex justify-between items-center border-b border-black/10 pb-3 mb-6">
                <h4 class="text-xl font-bold text-gray-800">Buat Agenda Baru</h4>
                <button id="close-modal-btn" class="text-gray-500 hover:text-gray-800"><i class="fas fa-times text-2xl"></i></button>
            </div>

            <div class="flex-grow overflow-y-auto -mr-3 pr-3">
                <form id="agenda-form" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        
                        <div class="space-y-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Agenda <span class="text-red-500">*</span></label>
                                <input type="text" id="title" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white/70" placeholder="Contoh: Rapat Evaluasi Bulanan">
                                <small id="title-error" class="text-red-500 text-xs mt-1 hidden"></small>
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                <textarea id="description" name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white/70" placeholder="Jelaskan detail agenda di sini..."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Acara <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                   <div>
                                        <label for="agenda_date" class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                                        <input type="text" id="agenda_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white/70" placeholder="Pilih Tanggal">
                                   </div>
                                   <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label for="start_hour" class="block text-xs font-medium text-gray-500 mb-1">Mulai</label>
                                            <input type="text" id="start_hour" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white/70" placeholder="Jam">
                                        </div>
                                        <div>
                                            <label for="end_hour" class="block text-xs font-medium text-gray-500 mb-1">Selesai</label>
                                            <input type="text" id="end_hour" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white/70" placeholder="Jam">
                                        </div>
                                   </div>
                                </div>
                                <small id="start_time-error" class="text-red-500 text-xs mt-1 hidden"></small>
                                <small id="end_time-error" class="text-red-500 text-xs mt-1 hidden"></small>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Undang Karyawan</label>
                                <div id="guest-list-container" class="h-40 overflow-y-auto rounded-lg border bg-white/70 p-3 space-y-2">
                                    <p class="text-gray-400">Memuat karyawan...</p>
                                </div>
                            </div>
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                                <input type="text" id="location" name="location" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white/70" placeholder="Contoh: Ruang Meeting Lt. 2">
                            </div>
                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Warna Label</label>
                                <input type="color" id="color" name="color" value="#3B82F6" class="w-full h-10 px-1 py-1 border border-gray-300 rounded-lg cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <div class="flex-shrink-0 flex justify-end mt-6 pt-4 border-t border-black/10">
                        <button type="button" id="cancel-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Batal</button>
                        <button type="submit" id="save-agenda-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Simpan Agenda</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="agenda-detail-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white/90 backdrop-blur-xl border border-white/30 rounded-2xl shadow-2xl shadow-blue-900/20 w-full max-w-2xl mx-4 p-6 transform transition-all" id="agenda-detail-content">
            {{-- KONTEN DETAIL AKAN DIISI OLEH JAVASCRIPT --}}
        </div>
    </div>

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('mini-calendar');
            const agendaListContainer = document.getElementById('agenda-list-container');
            const agendaListTitle = document.getElementById('agenda-list-title');
            let selectedDateEl = null;

            const detailModal = document.getElementById('agenda-detail-modal');
            const detailContent = document.getElementById('agenda-detail-content');
            const agendaModal = document.getElementById('agenda-modal');
            const addAgendaBtn = document.getElementById('add-agenda-btn');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const agendaForm = document.getElementById('agenda-form');
            const modalTitle = agendaModal.querySelector('h4');
            const saveButton = document.getElementById('save-agenda-btn');

            const agendaDate = flatpickr("#agenda_date", { dateFormat: "Y-m-d", altInput: true, altFormat: "d F Y", locale: "id" });
            const startHour = flatpickr("#start_hour", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true });
            const endHour = flatpickr("#end_hour", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true });
            
            function formatFullDate(date) { return date.toLocaleString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }
            function formatTime(date) { return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }); }

            function updateAgendaList(selectedDate) {
                const allEvents = calendar.getEvents();
                
                const startOfWeek = new Date(selectedDate);
                startOfWeek.setDate(selectedDate.getDate() - selectedDate.getDay() + (selectedDate.getDay() === 0 ? -6 : 1));
                startOfWeek.setHours(0, 0, 0, 0);

                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(startOfWeek.getDate() + 6);
                endOfWeek.setHours(23, 59, 59, 999);
                
                const startFormatted = startOfWeek.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                const endFormatted = endOfWeek.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                agendaListTitle.textContent = `Agenda (${startFormatted} - ${endFormatted})`;

                const eventsThisWeek = allEvents.filter(event => {
                    const eventDate = new Date(event.start);
                    return eventDate >= startOfWeek && eventDate <= endOfWeek;
                });

                agendaListContainer.innerHTML = '';
                
                if (eventsThisWeek.length > 0) {
                    eventsThisWeek.sort((a, b) => a.start - b.start).forEach(event => {
                        const startTime = event.allDay ? 'Seharian' : formatTime(event.start);
                        const endTime = (!event.allDay && event.end) ? formatTime(event.end) : '';
                        
                        const agendaHTML = `
                            <div data-event-id="${event.id}" class="agenda-item-clickable flex items-center gap-4 p-4 rounded-xl bg-white/80 shadow-md shadow-blue-500/10 border border-blue-200 transition-all duration-200 hover:shadow-xl hover:border-blue-400 hover:bg-white cursor-pointer">
                                <div class="flex-shrink-0 text-center bg-blue-100 text-blue-800 rounded-lg px-3 py-2 w-20">
                                    <p class="font-bold text-sm">${startTime}</p>
                                    ${endTime ? `<p class="text-xs">${endTime}</p>` : ''}
                                </div>
                                <div class="flex-grow border-l-4 pl-4" style="border-color: ${event.backgroundColor || '#3B82F6'}">
                                    <p class="font-semibold text-gray-900 text-base">${event.extendedProps.fullTitle}</p>
                                    <p class="text-xs text-gray-500">${formatFullDate(event.start)}</p>
                                    ${event.extendedProps.location ? `<p class="text-sm text-gray-500 mt-1">${event.extendedProps.location}</p>` : ''}
                                    ${event.extendedProps.type === 'holiday' ? `<p class="text-sm font-bold mt-1" style="color: ${event.backgroundColor};">${event.extendedProps.description}</p>` : ''}
                                </div>
                            </div>`;
                        agendaListContainer.innerHTML += agendaHTML;
                    });
                } else {
                     agendaListContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full text-center text-blue-700 p-4 bg-blue-100/70 rounded-xl border border-blue-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-50 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="font-semibold">Tidak ada agenda</p>
                            <p class="text-sm opacity-80">Pilih tanggal di kalender untuk melihat.</p>
                        </div>`;
                }
                
                document.querySelectorAll('.agenda-item-clickable').forEach(item => {
                    item.addEventListener('click', () => {
                        const eventId = item.dataset.eventId;
                        const event = calendar.getEventById(eventId);
                        if (event) showAgendaDetails(event);
                    });
                });
            }
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', 
                headerToolbar: { left: 'prev', center: 'title', right: 'next' },
                locale: 'id',
                buttonText: { today: 'hari ini' },
                events: "{{ route('agendas.index') }}",
                height: 450, 
                dayMaxEvents: true,

                eventDidMount: function(info) {
                    if (info.event.extendedProps.type === 'holiday') {
                        // Paksa background menggunakan warna dari database (merah/oranye)
                        info.el.style.setProperty('background-color', info.event.backgroundColor, 'important');
                        info.el.style.setProperty('border', 'none', 'important');
                        
                        // Paksa teks menjadi putih agar terbaca
                        const titleEl = info.el.querySelector('.fc-event-title');
                        if (titleEl) {
                            titleEl.style.setProperty('color', '#ffffff', 'important');
                            titleEl.style.setProperty('font-weight', '700', 'important');
                        }
                    } else {
                        // Untuk Agenda: jadikan warna event sebagai garis border di sebelah kiri
                        info.el.style.setProperty('border-left-color', info.event.backgroundColor || '#3B82F6', 'important');
                    }
                },

                dateClick: function(info) {
                    if (selectedDateEl) {
                        selectedDateEl.classList.remove('selected-date');
                    }
                    info.dayEl.classList.add('selected-date');
                    selectedDateEl = info.dayEl;
                    
                    updateAgendaList(info.date);
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    showAgendaDetails(info.event);
                },
                eventsSet: function() {
                    updateAgendaList(calendar.getDate());

                    const urlParams = new URLSearchParams(window.location.search);
                    const agendaId = urlParams.get('agenda_id');

                    if (agendaId) {
                        const event = calendar.getEventById('agenda_' + agendaId);
                        
                        if (event) {
                            showAgendaDetails(event);
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }
                    }
                }
            });
            calendar.render();

            // Update kalender saat viewport berubah (mobile <-> desktop)
            window.addEventListener('resize', function() {
                calendar.updateSize();
            });

            function showAgendaDetails(event) {
                const props = event.extendedProps;
                const startTime = event.allDay ? 'Seharian' : formatTime(event.start);
                const endTime = (!event.allDay && event.end) ? formatTime(event.end) : '';
                const timeDisplay = event.allDay ? 'Seharian Penuh' : `${startTime} - ${endTime} WIB`;

                let organizerAndGuestsHTML = '';
                
                if (props.type === 'agenda') {
                    let guestsHTML = '<p class="text-gray-500 text-sm">Tidak ada tamu yang diundang.</p>';
                    if (props.guests && props.guests.length > 0) {
                        guestsHTML = `<div class="flex flex-wrap gap-2">${props.guests.map(guest => `<span class="bg-gray-200 text-gray-800 text-xs font-semibold px-2.5 py-1 rounded-full">${guest}</span>`).join('')}</div>`;
                    }

                    organizerAndGuestsHTML = `
                        <div>
                            <h5 class="font-bold text-gray-800 mb-2 flex items-center gap-2"><i class="fas fa-user-tie fa-fw text-gray-400"></i>Penyelenggara</h5>
                            <p class="text-gray-600">${props.organizer}</p>
                        </div>
                        <div>
                            <h5 class="font-bold text-gray-800 mb-3 flex items-center gap-2"><i class="fas fa-users fa-fw text-gray-400"></i>Tamu Undangan</h5>
                            ${guestsHTML}
                        </div>
                    `;
                }

                let actionButtonsHTML = '';
                if (props.type === 'agenda' && props.is_creator) {
                    const realId = String(event.id).replace('agenda_', ''); 
                    
                    const editButton = `<button type="button" id="edit-agenda-btn" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg">Edit</button>`;
                    const csrfToken = document.querySelector('form#agenda-form input[name="_token"]').value;
                    const deleteUrl = "{{ route('agendas.destroy', ['agenda' => ':id']) }}".replace(':id', realId);
                    const deleteForm = `
                        <form action="${deleteUrl}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda ini?')" class="ml-2">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">Hapus</button>
                        </form>
                    `;
                    actionButtonsHTML = `
                        <button id="close-detail-modal-bottom-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-auto">Tutup</button>
                        ${editButton}
                        ${deleteForm}
                    `;
                } else {
                    actionButtonsHTML = `<button id="close-detail-modal-bottom-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg ml-auto">Tutup</button>`;
                }

                const headerLabel = props.type === 'holiday' ? 'Informasi Libur' : 'Detail Agenda';

                const contentHTML = `
                    <div class="flex justify-between items-start pb-3 mb-4 border-b border-black/10">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider" style="color: ${event.backgroundColor || '#3B82F6'}">${headerLabel}</p>
                            <h4 class="text-2xl font-bold text-gray-900 mt-1">${props.fullTitle}</h4>
                        </div>
                        <button id="close-detail-modal-btn" class="text-gray-400 hover:text-gray-800 transition-colors"><i class="fas fa-times text-2xl"></i></button>
                    </div>
                    
                    <div class="max-h-[60vh] overflow-y-auto pr-3 -mr-3 space-y-5 text-sm">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-calendar-alt fa-fw text-gray-400 text-lg mt-1"></i>
                                <div>
                                    <p class="text-xs text-gray-500">Waktu & Tanggal</p>
                                    <p class="font-semibold text-gray-800">${formatFullDate(event.start)}</p>
                                    <p class="text-gray-600">${timeDisplay}</p>
                                </div>
                            </div>
                            ${props.location ? `
                            <div class="flex items-start gap-3">
                                <i class="fas fa-map-marker-alt fa-fw text-gray-400 text-lg mt-1"></i>
                                <div>
                                    <p class="text-xs text-gray-500">Lokasi</p>
                                    <p class="font-semibold text-gray-800">${props.location}</p>
                                </div>
                            </div>` : ''}
                        </div>
                        
                        ${props.description ? `
                        <div>
                            <h5 class="font-bold text-gray-800 mb-2 flex items-center gap-2"><i class="fas fa-info-circle fa-fw text-gray-400"></i>Keterangan</h5>
                            <div class="text-gray-700 bg-gray-100 p-4 rounded-lg border text-sm">${props.description.replace(/\n/g, '<br>')}</div>
                        </div>` : ''}
                        
                        ${organizerAndGuestsHTML}
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-black/10 flex items-center">
                        ${actionButtonsHTML}
                    </div>
                `;

                detailContent.innerHTML = contentHTML;
                detailModal.classList.remove('hidden');
                
                document.getElementById('close-detail-modal-btn').addEventListener('click', closeDetailModal);
                document.getElementById('close-detail-modal-bottom-btn').addEventListener('click', closeDetailModal);

                if (props.type === 'agenda' && props.is_creator) {
                    document.getElementById('edit-agenda-btn').addEventListener('click', () => openModalForEdit(event));
                }
            }
            
            function closeDetailModal() { detailModal.classList.add('hidden'); }
            detailModal.addEventListener('click', (e) => { if (e.target === detailModal) closeDetailModal(); });
            
            function openModalForCreate() {
                const existingMethodInput = agendaForm.querySelector('input[name="_method"]');
                if (existingMethodInput) existingMethodInput.remove();

                agendaForm.reset();
                agendaForm.setAttribute('action', "{{ route('agendas.store') }}"); 
                
                modalTitle.textContent = 'Buat Agenda Baru';
                saveButton.textContent = 'Simpan Agenda';
                document.getElementById('color').value = '#3B82F6';
                agendaDate.setDate(new Date());
                startHour.clear();
                endHour.clear();
                document.querySelectorAll('input[name="guests[]"]').forEach(cb => cb.checked = false);
                agendaModal.classList.remove('hidden');
            }

            function openModalForEdit(event) {
                closeDetailModal();
                const existingMethodInput = agendaForm.querySelector('input[name="_method"]');
                if (existingMethodInput) existingMethodInput.remove();

                agendaForm.reset();
                const realId = String(event.id).replace('agenda_', '');
                const updateUrl = "{{ route('agendas.update', ['agenda' => ':id']) }}".replace(':id', realId);
                agendaForm.setAttribute('action', updateUrl); 

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                agendaForm.appendChild(methodInput);
                
                modalTitle.textContent = 'Edit Agenda';
                saveButton.textContent = 'Update Agenda';
                
                document.getElementById('title').value = event.extendedProps.fullTitle;
                document.getElementById('description').value = event.extendedProps.description || '';
                document.getElementById('location').value = event.extendedProps.location || '';
                document.getElementById('color').value = event.backgroundColor || '#3B82F6';
                
                agendaDate.setDate(event.start, true, "Y-m-d");
                startHour.setDate(event.start, true, "H:i");
                if (event.end) endHour.setDate(event.end, true, "H:i");
                
                document.querySelectorAll('input[name="guests[]"]').forEach(cb => {
                    cb.checked = event.extendedProps.guest_ids.includes(parseInt(cb.value));
                });
                agendaModal.classList.remove('hidden');
            }

            function closeModal() { agendaModal.classList.add('hidden'); }

            addAgendaBtn.addEventListener('click', openModalForCreate);
            closeModalBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);
            agendaModal.addEventListener('click', (e) => { if (e.target === agendaModal) closeModal(); });

            agendaForm.addEventListener('submit', function(e) {
                this.querySelector('input[name="start_time"]')?.remove();
                this.querySelector('input[name="end_time"]')?.remove();

                const dateValue = document.getElementById('agenda_date')._flatpickr.input.value;
                const startHourValue = document.getElementById('start_hour')._flatpickr.input.value;
                const endHourValue = document.getElementById('end_hour')._flatpickr.input.value;

                if (dateValue && startHourValue) {
                    const startTimeInput = document.createElement('input');
                    startTimeInput.type = 'hidden';
                    startTimeInput.name = 'start_time';
                    startTimeInput.value = `${dateValue} ${startHourValue}`;
                    this.appendChild(startTimeInput);
                }

                if (dateValue && endHourValue) {
                    const endTimeInput = document.createElement('input');
                    endTimeInput.type = 'hidden';
                    endTimeInput.name = 'end_time';
                    endTimeInput.value = `${dateValue} ${endHourValue}`;
                    this.appendChild(endTimeInput);
                }
            });
            
            const guestContainer = document.getElementById('guest-list-container');
            fetch("{{ route('agendas.getUsers') }}")
                .then(response => response.json())
                .then(users => {
                    guestContainer.innerHTML = '';
                    if (users.length > 0) {
                        users.forEach(user => {
                            guestContainer.insertAdjacentHTML('beforeend', `
                                <div class="flex items-center">
                                    <input id="guest-${user.id}" name="guests[]" value="${user.id}" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <label for="guest-${user.id}" class="ml-3 block text-sm font-medium text-gray-700">${user.name}</label>
                                </div>
                            `);
                        });
                    } else {
                        guestContainer.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada karyawan lain untuk diundang.</p>';
                    }
                });

            // == Mobile Add Agenda Button ==
            const mobileAddAgendaBtn = document.getElementById('mobile-add-agenda-btn');
            if (mobileAddAgendaBtn) {
                mobileAddAgendaBtn.addEventListener('click', openModalForCreate);
            }
        });
    </script>

    <script>
        // ============================================================
        // == MOBILE WEEKLY CALENDAR STRIP LOGIC ==
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            const prevBtn   = document.getElementById('mobile-prev-week');
            const nextBtn   = document.getElementById('mobile-next-week');
            const titleEl   = document.getElementById('mobile-week-title');
            const daysNumEl = document.getElementById('mobile-days-nums');
            const agendaTodayContainer = document.getElementById('mobile-agenda-today-container');

            if (!prevBtn || !nextBtn || !titleEl || !daysNumEl) return;

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Awal minggu berdasarkan Senin (ISO Week) - gambar mulai dari Min
            // Kita pakai Sunday-based week seperti gambar referensi (Min–Sab)
            let currentWeekStart = getSundayOfWeek(today);

            // Nama bulan singkat
            const monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

            function getSundayOfWeek(d) {
                const day = new Date(d);
                day.setHours(0,0,0,0);
                const dayOfWeek = day.getDay(); // 0=Sun
                day.setDate(day.getDate() - dayOfWeek);
                return day;
            }

            function formatShortDate(d) {
                return d.getDate() + ' ' + monthNames[d.getMonth()];
            }

            function isSameDay(a, b) {
                return a.getFullYear() === b.getFullYear() &&
                       a.getMonth() === b.getMonth() &&
                       a.getDate() === b.getDate();
            }

            // Ambil semua events dari server
            let allEvents = [];
            fetch("{{ route('agendas.index') }}")
                .then(r => r.json())
                .then(data => {
                    allEvents = data;
                    renderWeek(currentWeekStart);
                    renderTodayAgenda();
                })
                .catch(() => {
                    renderWeek(currentWeekStart);
                });

            function getEventDates() {
                return allEvents.map(ev => {
                    const d = new Date(ev.start);
                    d.setHours(0,0,0,0);
                    return d.getTime();
                });
            }

            function renderWeek(startDate) {
                // Title: "12 Jul - 18 Jul 2026"
                const endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);

                const startStr = startDate.getDate() + ' ' + monthNames[startDate.getMonth()];
                const endStr   = endDate.getDate() + ' ' + monthNames[endDate.getMonth()] + ' ' + endDate.getFullYear();
                titleEl.textContent = startStr + ' - ' + endStr;

                const eventTimes = getEventDates();
                daysNumEl.innerHTML = '';

                for (let i = 0; i < 7; i++) {
                    const day = new Date(startDate);
                    day.setDate(startDate.getDate() + i);

                    const isToday    = isSameDay(day, today);
                    const hasEvent   = eventTimes.includes(day.getTime());

                    const div = document.createElement('div');
                    div.style.display = 'flex';
                    div.style.flexDirection = 'column';
                    div.style.alignItems = 'center';

                    const span = document.createElement('span');
                    span.className = 'day-num' + (isToday ? ' today' : '') + (hasEvent ? ' has-event' : '');
                    span.textContent = day.getDate();

                    span.addEventListener('click', () => {
                        // Highlight selected
                        daysNumEl.querySelectorAll('.day-num').forEach(el => {
                            el.classList.remove('selected');
                        });
                        if (!isToday) span.classList.add('selected');
                        renderDayAgenda(day);
                    });

                    div.appendChild(span);
                    daysNumEl.appendChild(div);
                }
            }

            function renderTodayAgenda() {
                renderDayAgenda(today);
            }

            function formatTime(dateStr) {
                const d = new Date(dateStr);
                return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
            }

            function renderDayAgenda(day) {
                if (!agendaTodayContainer) return;

                const dayEvents = allEvents.filter(ev => {
                    const evDate = new Date(ev.start);
                    evDate.setHours(0,0,0,0);
                    return isSameDay(evDate, day);
                });

                if (dayEvents.length === 0) {
                    agendaTodayContainer.innerHTML = `
                        <div class="mobile-agenda-empty">
                            <i class="fas fa-calendar-alt text-3xl text-blue-300 mb-3"></i>
                            <p class="font-semibold text-blue-700 text-sm">Tidak ada agenda</p>
                            <p class="text-xs text-blue-400 mt-1">Tap + untuk menambah jadwal.</p>
                        </div>`;
                    return;
                }

                let html = '<div class="space-y-2">';
                dayEvents.forEach(ev => {
                    const color = ev.backgroundColor || '#3B82F6';
                    const timeStr = ev.allDay ? 'Seharian' : formatTime(ev.start);
                    html += `
                        <div class="flex items-center gap-3 p-3 rounded-xl border" style="border-left: 4px solid ${color}; background:#f9fafb;">
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">${ev.title || ev.extendedProps?.fullTitle || 'Agenda'}</p>
                                <p class="text-xs text-gray-500">${timeStr}</p>
                            </div>
                        </div>`;
                });
                html += '</div>';
                agendaTodayContainer.innerHTML = html;
            }

            prevBtn.addEventListener('click', () => {
                currentWeekStart.setDate(currentWeekStart.getDate() - 7);
                renderWeek(currentWeekStart);
            });

            nextBtn.addEventListener('click', () => {
                currentWeekStart.setDate(currentWeekStart.getDate() + 7);
                renderWeek(currentWeekStart);
            });
        });
    </script>
    @endpush
</x-layout-users>