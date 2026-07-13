<x-layout-users :title="$title">

    @push('styles')
    <style>
        /* ====== NOTIFIKASI PAGE - MOBILE FIRST STYLES ====== */

        /* Back Button */
        .notif-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            background: #fff;
            border: 1.5px solid #dbeafe;
            border-radius: 999px;
            color: #1d4ed8;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 1px 4px rgba(59,130,246,0.08);
        }
        .notif-back-btn:hover {
            background: #eff6ff;
            box-shadow: 0 2px 8px rgba(59,130,246,0.15);
        }

        /* Header Card */
        .notif-header-card {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%);
            border-radius: 1.25rem;
            padding: 24px 22px 28px;
            color: #fff;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .notif-header-card::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }
        .notif-header-card::after {
            content: '';
            position: absolute;
            bottom: -50px; left: -30px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(99,179,237,0.15);
        }
        .notif-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 999px;
            padding: 4px 14px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #fff;
            margin-bottom: 10px;
        }
        .notif-header-title {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-bottom: 8px;
            position: relative; z-index: 1;
        }
        .notif-header-desc {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.85);
            line-height: 1.6;
            position: relative; z-index: 1;
            max-width: 280px;
        }

        /* Filter Chips */
        .filter-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-right: 4px;
            align-self: center;
        }
        .filter-chip {
            display: inline-flex;
            align-items: center;
            padding: 7px 16px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1.5px solid #e5e7eb;
            background: #fff;
            color: #374151;
            text-decoration: none;
            transition: all 0.18s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            white-space: nowrap;
        }
        .filter-chip:hover {
            border-color: #93c5fd;
            color: #1d4ed8;
            background: #eff6ff;
        }
        .filter-chip.active {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #fff;
            box-shadow: 0 2px 8px rgba(29,78,216,0.25);
        }

        /* Notifikasi List Card */
        .notif-list-card {
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid #f0f0f0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }
        .notif-group-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
        }
        .notif-group-header span {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #94a3b8;
        }
        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid #f8fafc;
            text-decoration: none;
            transition: background 0.15s;
            position: relative;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: #f0f7ff; }
        .notif-item.unread { background: #f0f7ff; }
        .notif-item.unread::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: #2563eb;
            border-radius: 0 2px 2px 0;
        }
        .notif-icon-box {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
        }
        .notif-content { flex: 1; min-width: 0; }
        .notif-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 3px;
        }
        .notif-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.3;
            flex: 1;
        }
        .notif-time {
            font-size: 0.68rem;
            color: #9ca3af;
            white-space: nowrap;
            flex-shrink: 0;
            padding-top: 2px;
        }
        .notif-message {
            font-size: 0.78rem;
            color: #6b7280;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .notif-unread-dot {
            width: 8px; height: 8px;
            background: #2563eb;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 6px;
            box-shadow: 0 0 0 2px rgba(37,99,235,0.2);
        }

        /* Empty State */
        .notif-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            text-align: center;
        }
        .notif-empty-icon {
            width: 72px; height: 72px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
        }

        /* Desktop overrides */
        @media (min-width: 640px) {
            .notif-header-title { font-size: 2rem; }
            .notif-item { padding: 16px 20px; gap: 14px; }
            .notif-icon-box { width: 46px; height: 46px; }
            .notif-title { font-size: 0.9rem; }
            .notif-message { font-size: 0.85rem; }
        }
    </style>
    @endpush

    <div class="min-h-screen bg-gray-50 sm:bg-gradient-to-br sm:from-sky-50 sm:to-blue-100 pb-8">
        <div class="max-w-3xl mx-auto px-4 pt-5 sm:pt-8">

            {{-- TOMBOL KEMBALI --}}
            <div class="mb-5">
                <a href="{{ route('dashboard') }}" class="notif-back-btn">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Kembali</span>
                </a>
            </div>

            {{-- HEADER CARD --}}
            <div class="notif-header-card mb-5">
                <div style="position:relative;z-index:1;">
                    <div class="notif-badge">Pusat Informasi</div>
                    <h1 class="notif-header-title">Notifikasi Anda</h1>
                    <p class="notif-header-desc">
                        Pantau aktivitas terbaru, pengumuman, dan pembaruan sistem di sini.
                    </p>
                </div>
            </div>

            {{-- FILTER CHIPS --}}
            <div class="flex flex-wrap items-center gap-2 mb-5">
                <span class="filter-label">Filter:</span>

                <a href="{{ route('notifikasi.index') }}"
                   class="filter-chip {{ $filterType === 'semua' ? 'active' : '' }}">
                    Semua
                </a>

                @foreach ($availableTypes as $type)
                    <a href="{{ route('notifikasi.index', ['type' => $type]) }}"
                       class="filter-chip {{ $filterType === $type ? 'active' : '' }} capitalize">
                        {{ $type }}
                    </a>
                @endforeach
            </div>

            {{-- DAFTAR NOTIFIKASI --}}
            <div class="notif-list-card">

                @forelse ($groupOrder as $groupName)
                    @if (isset($groupedNotifications[$groupName]) && $groupedNotifications[$groupName]->isNotEmpty())

                        {{-- Header Grup --}}
                        <div class="notif-group-header">
                            <i class="far fa-clock text-slate-300 text-xs"></i>
                            <span>{{ $groupName }}</span>
                        </div>

                        @foreach ($groupedNotifications[$groupName] as $notification)
                            @php
                                $isUnread = !$notification->read_at;
                                // Tentukan warna icon
                                $titleLower = strtolower($notification->data['title'] ?? '');
                                if (str_contains($titleLower, 'error') || str_contains($titleLower, 'gagal')) {
                                    $iconBg = 'background:#fee2e2;';
                                    $iconColor = 'color:#dc2626;';
                                } elseif (str_contains($titleLower, 'sukses') || str_contains($titleLower, 'berhasil') || str_contains($titleLower, 'disetujui')) {
                                    $iconBg = 'background:#d1fae5;';
                                    $iconColor = 'color:#059669;';
                                } elseif (str_contains($titleLower, 'warning') || str_contains($titleLower, 'peringatan') || str_contains($titleLower, 'ditolak')) {
                                    $iconBg = 'background:#ffedd5;';
                                    $iconColor = 'color:#ea580c;';
                                } else {
                                    $iconBg = 'background:#dbeafe;';
                                    $iconColor = 'color:#2563eb;';
                                }
                                $timeStr = \Carbon\Carbon::parse($notification->created_at)->translatedFormat('d M, H:i');
                            @endphp

                            <a href="{{ $notification->data['url'] ?? '#' }}"
                               class="notif-item {{ $isUnread ? 'unread' : '' }}">

                                {{-- Icon --}}
                                <div class="notif-icon-box" style="{{ $iconBg }}">
                                    <i class="{{ $notification->data['icon'] ?? 'fas fa-info' }}" style="{{ $iconColor }}"></i>
                                </div>

                                {{-- Konten --}}
                                <div class="notif-content">
                                    <div class="notif-title-row">
                                        <span class="notif-title">{{ $notification->data['title'] ?? 'Notifikasi Baru' }}</span>
                                        <span class="notif-time">{{ $timeStr }}</span>
                                    </div>
                                    <p class="notif-message">{{ $notification->data['message'] ?? '-' }}</p>
                                </div>

                                {{-- Dot unread --}}
                                @if ($isUnread)
                                    <div class="notif-unread-dot"></div>
                                @endif

                            </a>
                        @endforeach

                    @endif
                @empty
                    {{-- loop kosong --}}
                @endforelse

                {{-- STATE KOSONG --}}
                @if($groupedNotifications->isEmpty())
                    <div class="notif-empty">
                        <div class="notif-empty-icon">
                            <i class="fas fa-bell-slash text-2xl text-gray-300"></i>
                        </div>
                        <h3 class="text-base font-bold text-gray-700 mb-1">Tidak ada notifikasi</h3>
                        <p class="text-sm text-gray-400 max-w-xs">
                            @if($filterType !== 'semua')
                                Tidak ada notifikasi untuk kategori "<strong class="text-gray-500">{{ $filterType }}</strong>".
                            @else
                                Anda sudah melihat semua pembaruan terbaru.
                            @endif
                        </p>
                        @if($filterType !== 'semua')
                            <a href="{{ route('notifikasi.index') }}"
                               class="mt-5 px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-full hover:bg-blue-700 transition shadow-sm">
                                Lihat Semua
                            </a>
                        @endif
                    </div>
                @endif

            </div>

            {{-- Footer --}}
            <div class="mt-5 text-center">
                <p class="text-xs text-gray-400">Menampilkan notifikasi 30 hari terakhir.</p>
            </div>

        </div>
    </div>

</x-layout-users>