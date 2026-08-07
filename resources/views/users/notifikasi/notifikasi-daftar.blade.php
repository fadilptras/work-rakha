<x-layout-users :title="$title">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        html { scroll-behavior: smooth; }

        .mesh-bg {
            background-color: #ede9fe; /* Warna dari halaman pengajuan dana */
        }

        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0; border-radius: 9999px;
            color: #1e293b; font-size: 0.9rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px; 
            width: 100%;
            justify-content: center;
            min-height: 44px;
        }
        @media (min-width: 768px) {
            .btn-back-modern { width: fit-content; justify-content: flex-start; }
        }
        .btn-back-modern:hover { background: #f1f5f9; border-color: #cbd5e1; transform: translateY(-1px); }
        .btn-back-modern .icon-circle { width: 32px; height: 32px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #3b82f6; font-size: 0.85rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: transform 0.2s ease; }
        .btn-back-modern:hover .icon-circle { transform: translateX(-3px); }

        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            padding: 16px;
        }
        @media (min-width: 768px) {
            .glass-card { border-radius: 24px; padding: 28px; }
        }

        /* Filter Chips */
        .filter-chip {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 8px 16px; border-radius: 9999px;
            font-size: 0.75rem; font-weight: 800;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569; text-decoration: none;
            transition: all 0.15s;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
            min-height: 44px;
        }
        @media (min-width: 768px) {
            .filter-chip { padding: 7px 18px; min-height: auto; }
        }
        .filter-chip:hover { border-color: #93c5fd; color: #1d4ed8; background: #eff6ff; }
        .filter-chip.active { background: #1d4ed8; border-color: #1d4ed8; color: #ffffff; box-shadow: 0 2px 4px rgba(29,78,216,0.2); }

        /* Notif item */
        .notif-item {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none;
            transition: background 0.15s;
            position: relative;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: #f8fafc; }
        .notif-item.unread { background: #eff6ff; }
        .notif-item.unread::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0;
            width: 4px;
            background: #3b82f6;
            border-radius: 0 3px 3px 0;
        }

        .notif-icon-box {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 1rem;
        }
        .notif-content { flex: 1; min-width: 0; }
        .notif-title-row {
            display: flex; flex-direction: column; align-items: flex-start;
            gap: 4px; margin-bottom: 4px;
        }
        @media (min-width: 480px) {
            .notif-title-row { flex-direction: row; align-items: flex-start; justify-content: space-between; gap: 8px; }
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
        }

        /* Group header */
        .notif-group-header {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 20px;
        }
        .notif-group-header span {
            font-size: 0.75rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.1em;
        }

        @media (min-width: 640px) {
            .notif-item { padding: 18px 24px; gap: 16px; }
            .notif-icon-box { width: 48px; height: 48px; }
            .notif-title { font-size: 0.9rem; }
            .notif-message { font-size: 0.82rem; }
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative">

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
                        <div class="h-12 w-12 sm:h-14 sm:w-14 rounded-xl sm:rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                            <i class="fas fa-bell text-xl sm:text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-xl md:text-2xl font-black tracking-tight text-white uppercase">Notifikasi Anda</h1>
                            <p class="text-blue-100 text-[10px] sm:text-xs md:text-sm mt-1 font-medium leading-relaxed max-w-xl">
                                Pantau aktivitas terbaru, pengumuman, dan pembaruan status pengajuan Anda.
                            </p>
                        </div>
                    </div>
                    <div class="w-full sm:w-auto mt-2 sm:mt-0 text-center sm:text-right">
                        <span class="inline-flex w-full sm:w-auto justify-center items-center gap-2 px-4 py-2 rounded-full text-[10px] md:text-xs font-black uppercase tracking-wider bg-white/90 border border-white text-blue-700 shadow-sm">
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
                    <div class="flex items-center gap-3 sm:gap-5 mb-2 sm:mb-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg sm:text-xl">
                            <i class="fas fa-filter"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-slate-800">Daftar Notifikasi</h3>
                            <p class="text-[10px] sm:text-xs text-slate-500 font-semibold mt-1">Filter berdasarkan kategori notifikasi yang ingin ditampilkan.</p>
                        </div>
                    </div>
                    
                    @php
                        $categoryIcons = [
                            'Pengajuan Dana' => 'fas fa-wallet',
                            'Pengajuan Barang' => 'fas fa-box-open',
                            'Pengajuan Cuti' => 'fas fa-calendar-alt',
                            'Lainnya' => 'fas fa-ellipsis-h',
                        ];
                    @endphp

                    <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 w-full sm:w-auto">
                        @foreach ($availableTypes as $type)
                            <a href="{{ route('notifikasi.index', ['type' => $type]) }}"
                               class="filter-chip {{ $filterType === $type ? 'active' : '' }}">
                                @if(isset($categoryIcons[$type]))
                                    <i class="{{ $categoryIcons[$type] }} mr-1.5 text-[10px]"></i>
                                @endif
                                {{ ucfirst($type) }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Notifikasi List --}}
                <div class="overflow-hidden" style="border-radius: 16px;">

                    @forelse ($groupOrder as $groupName)
                        @if (isset($groupedNotifications[$groupName]) && $groupedNotifications[$groupName]->isNotEmpty())
                            @php
                                $groupStyles = [
                                    'Pengajuan Dana' => ['icon' => 'fas fa-wallet', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 border-emerald-100', 'iconColor' => 'text-emerald-500'],
                                    'Pengajuan Barang' => ['icon' => 'fas fa-box-open', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50 border-blue-100', 'iconColor' => 'text-blue-500'],
                                    'Pengajuan Cuti' => ['icon' => 'fas fa-calendar-alt', 'color' => 'text-purple-600', 'bg' => 'bg-purple-50 border-purple-100', 'iconColor' => 'text-purple-500'],
                                    'Lainnya' => ['icon' => 'fas fa-ellipsis-h', 'color' => 'text-slate-600', 'bg' => 'bg-slate-50 border-slate-200', 'iconColor' => 'text-slate-400'],
                                ];
                                $gStyle = $groupStyles[$groupName] ?? $groupStyles['Lainnya'];
                            @endphp

                            <div class="notif-group-header {{ $gStyle['bg'] }} border-b-2">
                                <i class="{{ $gStyle['icon'] }} {{ $gStyle['iconColor'] }} text-[12px] mr-1"></i>
                                <span class="{{ $gStyle['color'] }} font-extrabold text-xs ml-1">{{ $groupName }}</span>
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
                                Tidak ada notifikasi untuk kategori "<strong class="text-slate-500">{{ $filterType }}</strong>".
                            </p>
                            <a href="{{ route('notifikasi.index') }}"
                               class="mt-4 inline-flex w-full md:w-auto min-h-[44px] justify-center items-center gap-1.5 px-4 py-1.5 bg-blue-600 text-white text-[11px] font-black rounded-full hover:bg-blue-700 transition shadow-sm uppercase tracking-wider">
                                <i class="fas fa-redo text-[9px]"></i> Refresh
                            </a>
                        </div>
                    @endif
                </div>

                <p class="text-[11px] text-slate-400 font-semibold text-center" style="margin-top: 18px;">Menampilkan notifikasi 30 hari terakhir.</p>
            </div>

        </div>
    </div>

</x-layout-users>
