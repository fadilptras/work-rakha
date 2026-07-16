<x-layout-users :title="$title">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        html { scroll-behavior: smooth; }

        .mesh-bg {
            background-color: #f0f6fc;
            background-image:
                radial-gradient(at 40% 20%, rgba(147, 197, 253, 0.45) 0px, transparent 50%),
                radial-gradient(at 80% 0%, rgba(167, 139, 250, 0.35) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(191, 219, 254, 0.45) 0px, transparent 50%),
                radial-gradient(at 80% 50%, rgba(139, 92, 246, 0.25) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(221, 214, 254, 0.4) 0px, transparent 50%),
                radial-gradient(at 80% 100%, rgba(96, 165, 250, 0.35) 0px, transparent 50%),
                radial-gradient(at 0% 0%, rgba(238, 242, 255, 0.6) 0px, transparent 50%);
            background-attachment: fixed;
        }
        @keyframes float {
            0%   { transform: translateY(0px) rotate(0deg); }
            50%  { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float         { animation: float 8s ease-in-out infinite; }
        .animate-float-delayed { animation: float 10s ease-in-out infinite; animation-delay: 2s; }

        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.9); border-radius: 9999px;
            color: #1e293b; font-size: 0.9rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-bottom: 24px; width: fit-content;
        }
        .btn-back-modern:hover { background: rgba(255,255,255,0.95); box-shadow: 0 10px 15px -3px rgba(59,130,246,0.15); transform: translateY(-2px); color: #1d4ed8; }
        .btn-back-modern .icon-circle { width: 32px; height: 32px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #3b82f6; font-size: 0.85rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: transform 0.3s ease; }
        .btn-back-modern:hover .icon-circle { transform: translateX(-3px); background: #EFF6FF; }

        .glass-card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,1);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            padding: 28px;
        }

        /* Filter Chips */
        .filter-chip {
            display: inline-flex; align-items: center;
            padding: 7px 18px; border-radius: 9999px;
            font-size: 0.75rem; font-weight: 800;
            border: 2px solid #e2e8f0;
            background: rgba(255,255,255,0.9);
            color: #475569; text-decoration: none;
            transition: all 0.18s;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .filter-chip:hover { border-color: #93c5fd; color: #1d4ed8; background: #eff6ff; }
        .filter-chip.active { background: #1d4ed8; border-color: #1d4ed8; color: #fff; box-shadow: 0 4px 12px rgba(29,78,216,0.25); }

        /* Notif item */
        .notif-item {
            display: flex; align-items: flex-start; gap: 14px;
            padding: 16px 20px;
            border-bottom: 1px solid rgba(241,245,249,0.8);
            text-decoration: none;
            transition: background 0.15s;
            position: relative;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: rgba(239,246,255,0.6); }
        .notif-item.unread { background: rgba(239,246,255,0.5); }
        .notif-item.unread::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #2563eb, #60a5fa);
            border-radius: 0 3px 3px 0;
        }

        .notif-icon-box {
            width: 44px; height: 44px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 1rem;
        }
        .notif-content { flex: 1; min-width: 0; }
        .notif-title-row {
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: 8px;
            margin-bottom: 4px;
        }
        .notif-title {
            font-size: 0.85rem; font-weight: 800;
            color: #1e293b; line-height: 1.35; flex: 1;
        }
        .notif-time {
            font-size: 0.68rem; color: #94a3b8;
            white-space: nowrap; flex-shrink: 0;
            padding-top: 2px; font-weight: 700;
        }
        .notif-message {
            font-size: 0.78rem; color: #64748b;
            line-height: 1.55; font-weight: 500;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .notif-unread-dot {
            width: 8px; height: 8px;
            background: #2563eb; border-radius: 50%;
            flex-shrink: 0; margin-top: 7px;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
        }

        /* Group header */
        .notif-group-header {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 20px;
            background: rgba(248,250,252,0.8);
            border-bottom: 1px solid rgba(241,245,249,1);
        }
        .notif-group-header span {
            font-size: 0.68rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8;
        }

        @media (min-width: 640px) {
            .notif-item { padding: 18px 24px; gap: 16px; }
            .notif-icon-box { width: 48px; height: 48px; }
            .notif-title { font-size: 0.9rem; }
            .notif-message { font-size: 0.82rem; }
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">
        {{-- BG Animations --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[10%] left-[5%] w-32 h-32 bg-white/40 backdrop-blur-md border border-white/50 rounded-full animate-float"></div>
            <div class="absolute bottom-[15%] right-[10%] w-48 h-48 bg-white/30 backdrop-blur-md border border-white/40 rounded-full animate-float-delayed"></div>
            <div class="absolute inset-0" style="background-image: radial-gradient(rgba(100, 116, 139, 0.1) 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col">

            {{-- TOMBOL KEMBALI --}}
            <a href="{{ route('dashboard') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Dashboard
            </a>

            {{-- HEADER CARD --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-3xl p-6 md:p-8 shadow-xl mb-6 overflow-hidden border border-white/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute right-20 -bottom-10 w-24 h-24 bg-white/10 rounded-full blur-lg pointer-events-none"></div>

                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-5">
                        <div class="h-14 w-14 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                            <i class="fas fa-bell text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-xl md:text-2xl font-black tracking-tight text-white uppercase">Notifikasi Anda</h1>
                            <p class="text-blue-100 text-xs md:text-sm mt-1 font-medium leading-relaxed max-w-xl">
                                Pantau aktivitas terbaru, pengumuman, dan pembaruan status pengajuan Anda.
                            </p>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider bg-white/90 border border-white text-blue-700 shadow-sm">
                            <i class="fas fa-bell text-blue-500"></i>
                            Pusat Informasi
                        </span>
                    </div>
                </div>
            </div>

            {{-- GLASS CARD: FILTER + LIST --}}
            <div class="glass-card mb-8">

                {{-- Filter Chips --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/60" style="padding-bottom: 18px; margin-bottom: 24px;">
                    <div class="flex items-center gap-5">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                            <i class="fas fa-filter"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-800">Daftar Notifikasi</h3>
                            <p class="text-xs text-slate-500 font-semibold" style="margin-top: 6px;">Filter berdasarkan kategori notifikasi yang ingin ditampilkan.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <a href="{{ route('notifikasi.index') }}"
                           class="filter-chip {{ $filterType === 'semua' ? 'active' : '' }}">
                            <i class="fas fa-th-large mr-1.5 text-[10px]"></i> Semua
                        </a>
                        @foreach ($availableTypes as $type)
                            <a href="{{ route('notifikasi.index', ['type' => $type]) }}"
                               class="filter-chip {{ $filterType === $type ? 'active' : '' }}">
                                {{ ucfirst($type) }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Notifikasi List --}}
                <div class="overflow-hidden" style="border-radius: 16px;">

                    @forelse ($groupOrder as $groupName)
                        @if (isset($groupedNotifications[$groupName]) && $groupedNotifications[$groupName]->isNotEmpty())

                            <div class="notif-group-header">
                                <i class="far fa-clock text-slate-300 text-[10px]"></i>
                                <span>{{ $groupName }}</span>
                            </div>

                            @foreach ($groupedNotifications[$groupName] as $notification)
                                @php
                                    $isUnread = !$notification->read_at;
                                    $titleLower = strtolower($notification->data['title'] ?? '');
                                    if (str_contains($titleLower, 'error') || str_contains($titleLower, 'gagal')) {
                                        $iconBg = 'background:#fee2e2;'; $iconColor = 'color:#dc2626;';
                                    } elseif (str_contains($titleLower, 'sukses') || str_contains($titleLower, 'berhasil') || str_contains($titleLower, 'disetujui')) {
                                        $iconBg = 'background:#d1fae5;'; $iconColor = 'color:#059669;';
                                    } elseif (str_contains($titleLower, 'warning') || str_contains($titleLower, 'peringatan') || str_contains($titleLower, 'ditolak')) {
                                        $iconBg = 'background:#ffedd5;'; $iconColor = 'color:#ea580c;';
                                    } else {
                                        $iconBg = 'background:#dbeafe;'; $iconColor = 'color:#2563eb;';
                                    }
                                    $timeStr = \Carbon\Carbon::parse($notification->created_at)->translatedFormat('d M, H:i');
                                @endphp

                                <a href="{{ $notification->data['url'] ?? '#' }}"
                                   class="notif-item {{ $isUnread ? 'unread' : '' }}">

                                    <div class="notif-icon-box" style="{{ $iconBg }}">
                                        <i class="{{ $notification->data['icon'] ?? 'fas fa-info' }}" style="{{ $iconColor }}"></i>
                                    </div>

                                    <div class="notif-content">
                                        <div class="notif-title-row">
                                            <span class="notif-title">{{ $notification->data['title'] ?? 'Notifikasi Baru' }}</span>
                                            <span class="notif-time">{{ $timeStr }}</span>
                                        </div>
                                        <p class="notif-message">{{ $notification->data['message'] ?? '-' }}</p>
                                    </div>

                                    @if ($isUnread)
                                        <div class="notif-unread-dot"></div>
                                    @endif
                                </a>
                            @endforeach

                        @endif
                    @empty
                    @endforelse

                    {{-- EMPTY STATE --}}
                    @php $allEmpty = collect($groupOrder)->every(fn($g) => !isset($groupedNotifications[$g]) || $groupedNotifications[$g]->isEmpty()); @endphp
                    @if($allEmpty)
                        <div class="flex flex-col items-center justify-center text-center py-12 px-6">
                            <div class="w-16 h-16 rounded-full bg-slate-100/80 flex items-center justify-center mb-4">
                                <i class="fas fa-bell-slash text-2xl text-slate-300"></i>
                            </div>
                            <h3 class="text-sm font-black text-slate-600 mb-1.5">Tidak ada notifikasi</h3>
                            <p class="text-xs text-slate-400 font-semibold max-w-xs leading-relaxed">
                                @if($filterType !== 'semua')
                                    Tidak ada notifikasi untuk kategori "<strong class="text-slate-500">{{ $filterType }}</strong>".
                                @else
                                    Anda sudah melihat semua pembaruan terbaru.
                                @endif
                            </p>
                            @if($filterType !== 'semua')
                                <a href="{{ route('notifikasi.index') }}"
                                   class="mt-4 inline-flex items-center gap-1.5 px-4 py-1.5 bg-blue-600 text-white text-[11px] font-black rounded-full hover:bg-blue-700 transition shadow-sm uppercase tracking-wider">
                                    <i class="fas fa-th-large text-[9px]"></i> Lihat Semua
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <p class="text-[11px] text-slate-400 font-semibold text-center" style="margin-top: 18px;">Menampilkan notifikasi 30 hari terakhir.</p>
            </div>

        </div>
    </div>

</x-layout-users>
