<x-layout-users :title="$title">

    {{-- Library Chart & Axios --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    @push('styles')
    <style>
        /* ===== REKAP ABSENSI — MOBILE FIRST STYLES ===== */

        .rekap-page-wrapper {
            padding: 16px 16px 48px;
        }
        @media (min-width: 768px) {
            .rekap-page-wrapper { padding: 24px 24px 56px; }
        }

        /* Back Button */
        .rekap-back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 18px;
            background: #fff;
            border: 1.5px solid #dbeafe;
            border-radius: 999px;
            color: #1d4ed8;
            font-size: 0.82rem; font-weight: 600;
            text-decoration: none;
            transition: all 0.18s;
            margin-bottom: 16px;
        }
        .rekap-back-btn:hover { background: #eff6ff; }

        /* Header Card */
        .rekap-header-card {
            background: linear-gradient(135deg, #001BB7 0%, #0c2dc2 60%, #1e40af 100%);
            border-radius: 24px;
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 27, 183, 0.15);
            margin-bottom: 16px;
        }
        @media (min-width: 768px) {
            .rekap-header-card { padding: 32px; }
        }
        .rekap-header-card::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 12px;
        }
        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            }
        }
        @media (min-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(6, 1fr);
            }
        }

        /* Stats Card */
        .stats-card {
            border-radius: 16px;
            padding: 16px;
            color: #fff;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 94px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .stats-card:hover { transform: translateY(-2px); }
        .stats-card::after {
            content: '';
            position: absolute;
            top: -10px; right: -10px;
            width: 50px; height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
        }
        .stats-card-label {
            font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em;
            opacity: 0.9;
        }
        .stats-card-value {
            font-size: 2.2rem; font-weight: 900;
            line-height: 1;
        }
        .stats-card-icon {
            position: absolute;
            bottom: 12px; right: 12px;
            font-size: 1.6rem;
            opacity: 0.35;
        }

        /* Card Colors */
        .stats-hadir   { background: #10b981; }
        .stats-sakit   { background: #f43f5e; }
        .stats-izin    { background: #f59e0b; }
        .stats-cuti    { background: #9333ea; }
        .stats-lembur  { background: #4f46e5; }
        .stats-alpa    { background: #64748b; }

        /* Terlambat Bar */
        .terlambat-bar {
            background: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
            border-radius: 16px;
            padding: 14px 20px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2);
            margin-bottom: 20px;
            transition: all 0.2s;
        }
        .terlambat-label {
            font-size: 0.78rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.05em;
            display: flex; align-items: center; gap: 8px;
        }
        .terlambat-value {
            font-size: 1.3rem; font-weight: 900;
        }

        /* Container Card */
        .rekap-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 20px;
            margin-bottom: 16px;
            border: 1px solid #f1f5f9;
        }
        .rekap-card-title {
            font-size: 1rem; font-weight: 800; color: #111827;
            margin-bottom: 14px;
            display: flex; align-items: center; gap: 8px;
        }

        /* Filter Form Controls */
        .filter-select {
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            color: #1f2937;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.88rem; font-weight: 700;
            outline: none;
            cursor: pointer;
        }
        .filter-btn {
            width: 100%;
            padding: 12px;
            background: #3b82f6;
            color: #fff;
            font-size: 0.88rem; font-weight: 700;
            border: none; border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(59,130,246,0.25);
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.2s;
        }
        .filter-btn:hover { background: #2563eb; }

        /* Daily Logs */
        .log-item {
            background: #fff;
            border-radius: 16px;
            border: 1.5px solid #f1f5f9;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            margin-bottom: 12px;
            position: relative;
            overflow: hidden;
            transition: border-color 0.15s;
        }
        .log-item:hover { border-color: #bfdbfe; }
        .log-indicator {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 5px;
        }
        .log-header {
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f8fafc;
        }
        .log-date-box {
            display: flex; align-items: baseline; gap: 6px;
        }
        .log-day {
            font-size: 1.15rem; font-weight: 800; color: #1f2937;
        }
        .log-month {
            font-size: 0.78rem; font-weight: 600; color: #6b7280;
            text-transform: uppercase;
        }
        .log-weekday {
            font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase;
        }
        .log-badge {
            font-size: 0.7rem; font-weight: 800;
            padding: 4px 10px; border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        /* Log Body */
        .log-body {
            padding: 14px 16px;
        }
        .log-time-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .log-time-card {
            background: #fff;
            padding: 10px;
            text-align: center;
        }
        .log-time-label {
            display: block;
            font-size: 0.65rem; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; margin-bottom: 2px;
        }
        .log-time-val {
            font-size: 0.95rem; font-weight: 800; font-family: monospace;
        }

        /* Log Info */
        .log-info-box {
            margin-top: 10px;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
            font-size: 0.78rem; color: #475569;
            display: flex; align-items: flex-start; gap: 8px;
            line-height: 1.4;
        }

        .log-action-btn {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.78rem; font-weight: 700;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .log-action-btn:hover { background: #dbeafe; }
    </style>
    @endpush

    <div class="bg-gray-50 sm:bg-gradient-to-br sm:from-sky-50 sm:to-blue-100 min-h-screen">
        <div class="max-w-5xl mx-auto rekap-page-wrapper">

            {{-- TOMBOL KEMBALI --}}
            <a href="{{ route('dashboard') }}" class="rekap-back-btn">
                <i class="fas fa-arrow-left text-xs"></i>
                Kembali
            </a>

            {{-- 2. HEADER CARD --}}
            <div class="rekap-header-card">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-5 relative z-10">
                    <div>
                        <div class="mb-2">
                            <span class="bg-white/20 backdrop-blur-md text-white text-[9px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-white/20">
                                Rekapitulasi
                            </span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white mb-1">
                            Riwayat Absensi
                        </h1>
                        <p class="text-blue-100 opacity-90 text-[12px] md:text-sm max-w-md leading-relaxed">
                            Pantau catatan kehadiran, keterlambatan, lembur, dan aktivitas harian Anda dalam satu periode.
                        </p>
                    </div>

                    {{-- Form Filter --}}
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl w-full lg:w-auto min-w-[280px]">
                        <form method="GET" action="{{ route('rekap_absen.index') }}">
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[9px] uppercase font-bold text-blue-200 mb-1">Bulan</label>
                                        <select name="bulan" class="filter-select">
                                            @foreach($daftarBulan as $num => $nama)
                                                <option value="{{ $num }}" {{ $num == $bulanDipilih ? 'selected' : '' }}>{{ $nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[9px] uppercase font-bold text-blue-200 mb-1">Tahun</label>
                                        <select name="tahun" class="filter-select">
                                            @foreach($daftarTahun as $tahun)
                                                <option value="{{ $tahun }}" {{ $tahun == $tahunDipilih ? 'selected' : '' }}>{{ $tahun }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="filter-btn">
                                    <i class="fas fa-filter text-xs"></i> Tampilkan Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 3. GRID STATISTIK --}}
            <div class="stats-grid">
                {{-- Card Hadir --}}
                <div class="stats-card stats-hadir">
                    <p class="stats-card-label">Hadir</p>
                    <span class="stats-card-value">{{ $rekap['hadir'] }}</span>
                    <i class="fas fa-check-circle stats-card-icon"></i>
                </div>
                {{-- Card Sakit --}}
                <div class="stats-card stats-sakit">
                    <p class="stats-card-label">Sakit</p>
                    <span class="stats-card-value">{{ $rekap['sakit'] }}</span>
                    <i class="fas fa-clinic-medical stats-card-icon"></i>
                </div>
                {{-- Card Izin --}}
                <div class="stats-card stats-izin">
                    <p class="stats-card-label">Izin</p>
                    <span class="stats-card-value">{{ $rekap['izin'] }}</span>
                    <i class="fas fa-envelope-open-text stats-card-icon"></i>
                </div>
                {{-- Card Cuti --}}
                <div class="stats-card stats-cuti">
                    <p class="stats-card-label">Cuti</p>
                    <span class="stats-card-value">{{ $rekap['cuti'] }}</span>
                    <i class="fas fa-plane-departure stats-card-icon"></i>
                </div>
                {{-- Card Lembur --}}
                <div class="stats-card stats-lembur">
                    <p class="stats-card-label">Lembur</p>
                    <span class="stats-card-value">{{ $rekap['lembur'] }}</span>
                    <i class="fas fa-business-time stats-card-icon"></i>
                </div>
                {{-- Card Alpa --}}
                <div class="stats-card stats-alpa">
                    <p class="stats-card-label">Alpa</p>
                    <span class="stats-card-value">{{ $rekap['alpa'] }}</span>
                    <i class="fas fa-user-slash stats-card-icon"></i>
                </div>
            </div>

            {{-- Card Terlambat (Orange Bar) --}}
            <div class="terlambat-bar">
                <div class="terlambat-label">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Total Terlambat</span>
                </div>
                @php
                    $parts = explode(' ', $rekap['terlambat_formatted']);
                    $jam = isset($parts[0]) ? $parts[0] : 0;
                    $menit = isset($parts[2]) ? $parts[2] : 0;
                @endphp
                <span class="terlambat-value">{{ $jam }}j {{ $menit }}m</span>
            </div>

            {{-- 4. CHART SECTION --}}
            <div class="rekap-card">
                <h3 class="rekap-card-title">
                    <i class="fas fa-chart-pie text-blue-500"></i>
                    <span>Visualisasi Kehadiran</span>
                </h3>
                <div class="w-full h-60 relative mt-4">
                    <canvas id="rekapAbsensiChart"></canvas>
                </div>
            </div>

            {{-- 5. DETAIL HARIAN --}}
            <div class="rekap-card" style="padding: 0; overflow: hidden; background: transparent; box-shadow: none; border: none;">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 p-1.5 rounded-lg text-xs"><i class="fas fa-list"></i></span>
                        Detail Harian
                    </h3>
                </div>

                {{-- DESKTOP VIEW (TABLE) --}}
                <div class="hidden md:block bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 uppercase font-bold text-[10px] tracking-widest border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Jam Kerja</th>
                                <th class="px-6 py-4 text-center">Aktivitas</th>
                                <th class="px-6 py-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($detailHarian as $item)
                                @php
                                    $isLate = false;
                                    if ($item->status == 'hadir' && $item->jam_masuk) {
                                         $waktuMasuk = \Carbon\Carbon::parse($item->jam_masuk, 'Asia/Jakarta');
                                         $batas = \Carbon\Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                                         $isLate = $waktuMasuk->gt($batas);
                                    }
                                    
                                    $rowClass = $item->is_weekend ? 'bg-slate-50/70' : 'hover:bg-blue-50/20 transition';
                                    
                                    $statusStyle = match(strtolower($item->status)) {
                                        'hadir'  => 'background:#dcfce7; color:#16a34a; border-color:#bbf7d0;',
                                        'sakit'  => 'background:#fee2e2; color:#dc2626; border-color:#fecaca;',
                                        'izin'   => 'background:#fef9c3; color:#ca8a04; border-color:#fef08a;',
                                        'cuti'   => 'background:#f3e8ff; color:#9333ea; border-color:#e9d5ff;',
                                        'lembur' => 'background:#e0e7ff; color:#4f46e5; border-color:#c7d2fe;',
                                        default  => 'background:#f3f4f6; color:#4b5563; border-color:#e5e7eb;',
                                    };
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-800">{{ $item->tanggal->format('d') }}</span>
                                            <span class="text-xs text-gray-400 font-medium">{{ $item->tanggal->translatedFormat('F Y') }}</span>
                                            <span class="text-[9px] font-bold uppercase mt-0.5 {{ $item->is_weekend ? 'text-red-400' : 'text-blue-500' }}">
                                                {{ $item->tanggal->translatedFormat('l') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase border" style="{{ $statusStyle }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <div class="inline-flex items-center bg-gray-50 rounded-lg px-3 py-1.5 border border-gray-100 shadow-sm">
                                            <span class="font-mono font-bold text-emerald-600 text-xs">{{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '--:--' }}</span>
                                            <span class="text-gray-300 mx-2">|</span>
                                            <span class="font-mono font-bold text-red-500 text-xs">{{ $item->jam_keluar ? \Carbon\Carbon::parse($item->jam_keluar)->format('H:i') : '--:--' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        @if($item->jumlah_aktivitas > 0)
                                            <button onclick="openModalAktivitas('{{ $item->tanggal->toDateString() }}')" class="group inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all">
                                                <i class="fas fa-clipboard-list mr-1.5"></i> {{ $item->jumlah_aktivitas }} Log
                                            </button>
                                        @else
                                            <span class="text-gray-300 text-xs italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <div class="text-xs text-gray-600 truncate max-w-xs" title="{{ $item->keterangan }}">
                                            @if($isLate)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-orange-100 text-orange-700 mr-1 border border-orange-200">Terlambat</span>
                                            @endif
                                            {{ $item->keterangan ?? '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <i class="far fa-calendar-times text-2xl mb-2 opacity-50"></i>
                                            <span>Tidak ada data absensi untuk periode ini.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE VIEW (CLEAN COMPACT CARDS) --}}
                <div class="block md:hidden space-y-3">
                    @forelse($detailHarian as $item)
                        @php
                            $isLate = false;
                            if ($item->status == 'hadir' && $item->jam_masuk) {
                                $waktuMasuk = \Carbon\Carbon::parse($item->jam_masuk, 'Asia/Jakarta');
                                $batas = \Carbon\Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                                $isLate = $waktuMasuk->gt($batas);
                            }

                            $logColor = match(strtolower($item->status)) {
                                'hadir'  => '#10b981',
                                'sakit'  => '#f43f5e',
                                'izin'   => '#f59e0b',
                                'cuti'   => '#9333ea',
                                'lembur' => '#4f46e5',
                                default  => '#64748b',
                            };

                            $badgeStyle = match(strtolower($item->status)) {
                                'hadir'  => 'background:#dcfce7; color:#16a34a;',
                                'sakit'  => 'background:#fee2e2; color:#dc2626;',
                                'izin'   => 'background:#fef9c3; color:#ca8a04;',
                                'cuti'   => 'background:#f3e8ff; color:#9333ea;',
                                'lembur' => 'background:#e0e7ff; color:#4f46e5;',
                                default  => 'background:#f3f4f6; color:#4b5563;',
                            };
                        @endphp
                        
                        <div class="log-item">
                            <div class="log-indicator" style="background: {{ $logColor }}"></div>
                            
                            {{-- Header --}}
                            <div class="log-header">
                                <div class="log-date-box">
                                    <span class="log-day">{{ $item->tanggal->format('d') }}</span>
                                    <span class="log-month">{{ $item->tanggal->translatedFormat('M Y') }}</span>
                                    <span class="log-weekday {{ $item->is_weekend ? 'text-red-500' : 'text-blue-600' }}">
                                        &bull; {{ $item->tanggal->translatedFormat('l') }}
                                    </span>
                                </div>
                                <span class="log-badge" style="{{ $badgeStyle }}">
                                    {{ $item->status }}
                                </span>
                            </div>

                            {{-- Body --}}
                            <div class="log-body">
                                <div class="log-time-grid">
                                    <div class="log-time-card">
                                        <span class="log-time-label">Masuk</span>
                                        <span class="log-time-val text-emerald-600">
                                            {{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '--:--' }}
                                        </span>
                                    </div>
                                    <div class="log-time-card">
                                        <span class="log-time-label">Keluar</span>
                                        <span class="log-time-val text-red-500">
                                            {{ $item->jam_keluar ? \Carbon\Carbon::parse($item->jam_keluar)->format('H:i') : '--:--' }}
                                        </span>
                                    </div>
                                </div>

                                @if($item->keterangan || $isLate)
                                    <div class="log-info-box">
                                        <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                                        <div>
                                            @if($isLate) <span class="text-orange-600 font-bold block mb-0.5">Terlambat</span> @endif
                                            {{ $item->keterangan ?? 'Tidak ada keterangan.' }}
                                        </div>
                                    </div>
                                @endif

                                @if($item->jumlah_aktivitas > 0)
                                    <button onclick="openModalAktivitas('{{ $item->tanggal->toDateString() }}')" class="log-action-btn">
                                        <i class="fas fa-eye text-xs"></i> Lihat {{ $item->jumlah_aktivitas }} Catatan Aktivitas
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="riwayat-empty" style="background:#fff; border: 2px dashed #e2e8f0; border-radius: 16px; padding: 32px 16px; text-align: center;">
                            <i class="far fa-calendar-times text-3xl text-gray-300 mb-2 block"></i>
                            <p class="text-gray-500 text-sm font-medium">Tidak ada data absensi untuk periode ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL AKTIVITAS --}}
    <div id="modalAktivitas" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity backdrop-blur-sm" onclick="closeModalAktivitas()"></div>
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                <div class="bg-blue-600 px-5 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="fas fa-list-alt"></i> Detail Aktivitas</h3>
                    <button onclick="closeModalAktivitas()" class="text-blue-100 hover:text-white transition bg-white/10 w-8 h-8 rounded-full flex items-center justify-center"><i class="fas fa-times"></i></button>
                </div>
                <div class="bg-blue-50 px-5 py-3 border-b border-blue-100 flex items-center gap-2">
                    <i class="far fa-calendar-alt text-blue-500"></i>
                    <p class="text-sm font-bold text-blue-800" id="modal-date">Memuat tanggal...</p>
                </div>
                <div class="px-5 py-5 max-h-[60vh] overflow-y-auto bg-gray-50 custom-scrollbar">
                    <div id="loading-aktivitas" class="hidden text-center py-8">
                        <div class="inline-block w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-2"></div>
                        <p class="text-xs text-gray-500 font-medium">Sedang memuat data...</p>
                    </div>
                    <div id="list-aktivitas" class="space-y-4"></div>
                </div>
                <div class="bg-white px-5 py-4 sm:flex sm:flex-row-reverse border-t border-gray-100">
                    <button type="button" onclick="closeModalAktivitas()" class="w-full inline-flex justify-center rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-200 sm:w-auto transition">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // --- CHART LOGIC ---
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('rekapAbsensiChart');
                if (ctx) {
                    const rekapData = @json($rekap);
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Hadir', 'Sakit', 'Izin', 'Cuti', 'Alpa', 'Lembur'],
                            datasets: [{
                                data: [rekapData.hadir, rekapData.sakit, rekapData.izin, rekapData.cuti, rekapData.alpa, rekapData.lembur],
                                backgroundColor: ['#10B981', '#F43F5E', '#F59E0B', '#9333EA', '#64748B', '#4F46E5'],
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 10, family: "'Inter', sans-serif" } } } }
                        }
                    });
                }
            });

            // --- MODAL LOGIC (FIXED URL) ---
            const modal = document.getElementById('modalAktivitas');
            const listContainer = document.getElementById('list-aktivitas');
            const loadingSpinner = document.getElementById('loading-aktivitas');
            const modalDate = document.getElementById('modal-date');

            function openModalAktivitas(date) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                listContainer.innerHTML = ''; 
                loadingSpinner.classList.remove('hidden');
                
                const d = new Date(date);
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                modalDate.textContent = d.toLocaleDateString('id-ID', options);

                axios.get('{{ route('aktivitas.getJson') }}', {
                    params: { start: date, user_id: '{{ auth()->id() }}' }
                })
                .then(function (response) {
                    loadingSpinner.classList.add('hidden');
                    const data = response.data;

                    if (data.length === 0) {
                        listContainer.innerHTML = `
                            <div class="text-center py-8 bg-white rounded-xl border border-dashed border-gray-300">
                                <i class="far fa-sticky-note text-2xl text-gray-300 mb-2"></i>
                                <p class="text-gray-400 text-sm">Tidak ada catatan aktivitas.</p>
                            </div>`;
                    } else {
                        data.forEach(item => {
                            const props = item.extendedProps;
                            const photoHtml = props.photo_url 
                                ? `<div class="mt-3 rounded-lg overflow-hidden border border-gray-200 relative group">
                                     <img src="${props.photo_url}" class="w-full h-40 object-cover transform transition group-hover:scale-105">
                                     <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
                                   </div>` 
                                : '';
                            
                            const html = `
                                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative pl-5">
                                    <div class="absolute left-0 top-4 bottom-4 w-1 bg-blue-500 rounded-r"></div>
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-gray-800 text-sm leading-tight">${item.title}</h4>
                                        <span class="flex-shrink-0 text-[10px] bg-gray-100 px-2 py-1 rounded text-gray-500 font-mono border border-gray-200">
                                            ${new Date(item.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 whitespace-pre-wrap leading-relaxed">${props.keterangan}</p>
                                    ${photoHtml}
                                </div>
                            `;
                            listContainer.innerHTML += html;
                        });
                    }
                })
                .catch(function (error) {
                    loadingSpinner.classList.add('hidden');
                    listContainer.innerHTML = '<div class="p-4 bg-red-50 text-red-600 rounded-lg text-sm text-center">Gagal memuat data aktivitas.</div>';
                });
            }

            function closeModalAktivitas() {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        </script>
        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        </style>
    @endpush
</x-layout-users>