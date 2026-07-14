<div class="sm:hidden flex flex-col pb-4 min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-100">
    {{-- Decorative Background Elements for Mobile --}}
    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl -z-10 transform translate-x-1/3 -translate-y-1/3"></div>
    <div class="absolute bottom-40 left-0 w-48 h-48 bg-purple-400/20 rounded-full blur-3xl -z-10 transform -translate-x-1/3"></div>

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
        <div class="flex items-center gap-2 bg-white/80 backdrop-blur-sm rounded-xl px-4 py-2.5 shadow-sm border border-white/60">
            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                <i class="fas fa-briefcase text-blue-600 text-xs"></i>
            </div>
            <span class="text-sm text-gray-600">Divisi: <span class="font-semibold text-gray-800">{{ Auth::user()->divisi ?? '-' }}</span></span>
        </div>
    </div>

    {{-- Absensi Section --}}
    <div class="px-4 pb-4">
        <div class="bg-white/70 backdrop-blur-md rounded-2xl p-4 shadow-sm border border-white">
            <h3 class="font-bold text-gray-800 text-base mb-3 flex items-center gap-2">
                <i class="fas fa-clock text-indigo-500"></i> Absensi
            </h3>
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
        <div class="mobile-agenda-card bg-white/70 backdrop-blur-md shadow-sm border border-white rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fas fa-calendar-check text-rose-500"></i> Agenda Hari Ini
                </h3>
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
    <div class="px-4 pb-4">
        <div class="mobile-notif-bar bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl p-3 flex items-center justify-between shadow-lg shadow-blue-500/30">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-bell text-white text-sm"></i>
                </div>
                @php
                    $latestNotif = Auth::user()->notifications->first();
                @endphp
                <span class="text-sm font-medium truncate">
                    @if($latestNotif)
                        {{ $latestNotif->data['title'] ?? 'Notifikasi baru' }}
                    @else
                        Tidak ada notifikasi baru.
                    @endif
                </span>
            </div>
            <a href="{{ route('notifikasi.index') }}" class="text-xs font-bold bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap ml-2">Lihat &rsaquo;</a>
        </div>
    </div>

</div>
