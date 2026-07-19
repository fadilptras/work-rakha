<x-layout-users :title="$title">

    {{-- Library Chart & Axios --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    @push('styles')
    <style>
        /* ===== REKAP ABSENSI â€” MOBILE FIRST STYLES ===== */

        .rekap-page-wrapper {
            padding: 16px 16px 48px;
        }
        @media (min-width: 768px) {
            .rekap-page-wrapper { padding: 24px 24px 56px; }
        }

        /* == Modern Mesh Background == */
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

        /* Float animation */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float-delayed { animation: float 10s ease-in-out infinite; animation-delay: 2s; }

        /* == Modern Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.9rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
            width: fit-content;
        }
        .btn-back-modern:hover { 
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
            color: #1d4ed8;
        }
        .btn-back-modern .icon-circle {
            width: 32px; height: 32px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6;
            font-size: 0.85rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover .icon-circle {
            transform: translateX(-3px);
            background: #EFF6FF;
        }

        /* Header Card */
        .rekap-header-card {
            background: linear-gradient(135deg, #001BB7 0%, #0c2dc2 60%, #1e40af 100%);
            border-radius: 24px;
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 27, 183, 0.15);
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.1);
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
            margin-bottom: 20px;
        }
        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
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
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 20px;
            padding: 16px;
            color: #1e293b;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stats-card:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            background: #fff;
        }
        .stats-card::after {
            content: '';
            position: absolute;
            top: -15px; right: -15px;
            width: 65px; height: 65px;
            border-radius: 50%;
            background: rgba(0,0,0,0.02);
            pointer-events: none;
        }
        .stats-card-label {
            font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: #64748b;
        }
        .stats-card-value {
            font-size: 2rem; font-weight: 800;
            line-height: 1.1;
            color: #1e293b;
            margin-top: 4px;
        }
        .stats-card-icon {
            position: absolute;
            bottom: 14px; right: 14px;
            font-size: 1.8rem;
            transition: all 0.25s;
        }
        .stats-card:hover .stats-card-icon {
            transform: scale(1.15) rotate(-5deg);
        }

        /* Card Colors & Hover states */
        .stats-hadir { border-left: 4px solid #10b981; }
        .stats-hadir .stats-card-icon { color: #10b981; opacity: 0.25; }
        .stats-hadir:hover { border-color: #10b981; }
        
        .stats-sakit { border-left: 4px solid #f43f5e; }
        .stats-sakit .stats-card-icon { color: #f43f5e; opacity: 0.25; }
        .stats-sakit:hover { border-color: #f43f5e; }
        
        .stats-izin { border-left: 4px solid #f59e0b; }
        .stats-izin .stats-card-icon { color: #f59e0b; opacity: 0.25; }
        .stats-izin:hover { border-color: #f59e0b; }
        
        .stats-cuti { border-left: 4px solid #9333ea; }
        .stats-cuti .stats-card-icon { color: #9333ea; opacity: 0.25; }
        .stats-cuti:hover { border-color: #9333ea; }
        
        .stats-lembur { border-left: 4px solid #4f46e5; }
        .stats-lembur .stats-card-icon { color: #4f46e5; opacity: 0.25; }
        .stats-lembur:hover { border-color: #4f46e5; }
        
        .stats-alpa { border-left: 4px solid #e11d48; }
        .stats-alpa .stats-card-icon { color: #e11d48; opacity: 0.25; }
        .stats-alpa:hover { border-color: #e11d48; }

        /* Glass Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 24px;
            margin-bottom: 20px;
        }
        .glass-card-title {
            font-size: 1.05rem; font-weight: 800; color: #1e293b;
            margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }

        /* == Subpage Navigation Buttons == */
        .subpage-nav-btn {
            background: transparent;
            color: #64748b;
            cursor: pointer;
            border: none;
            outline: none;
            transition: all 0.2s ease;
        }
        .subpage-nav-btn:hover {
            background: rgba(255, 255, 255, 0.6);
            color: #1e293b;
        }
        .subpage-nav-btn.active {
            background: #2563eb;
            color: #fff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        /* Tab Filter Buttons */
        .tab-btn {
            background: #fff;
            color: #64748b;
            border: 1.5px solid #e2e8f0;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            padding: 8px 16px;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .tab-btn:hover {
            background: #f8fafc;
            color: #1e293b;
            border-color: #cbd5e1;
        }
        .tab-btn.active {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
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

        /* == Riwayat Item == */
        .riwayat-item {
            display: flex; align-items: center; gap: 14px; padding: 16px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 18px;
            text-decoration: none; margin-bottom: 12px; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.01);
        }
        .riwayat-item:hover { 
            border-color: #bfdbfe; 
            background: #fff; 
            transform: translateY(-2px); 
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.08); 
        }
        .riwayat-badge { 
            font-size: 0.75rem; 
            font-weight: 800; 
            padding: 6px 12px; 
            border-radius: 999px; 
            white-space: nowrap; 
            margin-left: auto; 
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">
        {{-- Background Decorations --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            
            
            
        </div>

        <div class="relative z-10 w-full max-w-5xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col rekap-page-wrapper">

            {{-- TOMBOL KEMBALI MODERN --}}
            <a href="{{ route('dashboard') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Dashboard
            </a>

            {{-- Navigasi Halaman (Sub-tabs) --}}
            <div class="flex items-center gap-2 mb-6 bg-white/60 backdrop-blur-md p-1.5 rounded-2xl border border-white max-w-sm shadow-sm relative z-20">
                <button type="button" onclick="switchSubPage('ringkasan')" class="subpage-nav-btn active flex-1 py-2.5 text-xs font-bold rounded-xl transition duration-200" id="subnav-ringkasan">
                    <i class="fas fa-chart-line mr-1.5"></i> Ringkasan
                </button>
                <button type="button" onclick="switchSubPage('riwayat')" class="subpage-nav-btn flex-1 py-2.5 text-xs font-bold rounded-xl transition duration-200" id="subnav-riwayat">
                    <i class="fas fa-history mr-1.5"></i> Detail Riwayat
                </button>
            </div>

            {{-- SUB-PAGE CONTENT: RINGKASAN --}}
            <div id="subpage-content-ringkasan" class="space-y-6">
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
                                    <button type="submit" class="w-full py-2.5 bg-white text-blue-700 font-bold text-xs rounded-xl shadow-md hover:bg-blue-50 transition duration-200 flex items-center justify-center gap-2 border-none cursor-pointer">
                                        <i class="fas fa-filter text-[10px]"></i> Tampilkan Data
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

                @php
                    $parts = explode(' ', $rekap['terlambat_formatted']);
                    $jam = isset($parts[0]) ? $parts[0] : 0;
                    $menit = isset($parts[2]) ? $parts[2] : 0;
                    $hariKerjaAktif = collect($detailHarian)->filter(fn($item) => !in_array(strtolower($item->status), ['libur']))->count();
                @endphp

                {{-- 4. CHART & SUMMARY BLOCK --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                    {{-- Chart Card --}}
                    <div class="glass-card lg:col-span-2 mb-0 flex flex-col justify-between h-full">
                        <h3 class="glass-card-title mb-2">
                            <i class="fas fa-chart-pie text-blue-500"></i>
                            <span>Visualisasi Kehadiran</span>
                        </h3>
                        <div class="w-full h-44 relative mt-2">
                            <canvas id="rekapAbsensiChart"></canvas>
                        </div>
                    </div>

                    {{-- Informasi & Ringkasan Kehadiran --}}
                    <div class="glass-card mb-0 flex flex-col justify-between h-full">
                        <div>
                            <h3 class="glass-card-title mb-3">
                                <i class="fas fa-info-circle text-blue-500"></i>
                                <span>Informasi Kehadiran</span>
                            </h3>
                            
                            {{-- Row Widgets --}}
                            <div class="grid grid-cols-2 gap-3 mt-2">
                                {{-- Akumulasi Terlambat --}}
                                <div class="bg-orange-50/60 border border-orange-100 rounded-xl p-3 flex flex-col justify-between min-h-[90px] shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[9px] uppercase font-bold text-orange-700 tracking-wider">Terlambat</span>
                                        <span class="text-orange-500 text-xs"><i class="fas fa-clock"></i></span>
                                    </div>
                                    <div>
                                        <span class="text-base font-extrabold text-slate-800">{{ $jam }}</span> <span class="text-[10px] font-bold text-slate-400">J</span>
                                        <span class="text-base font-extrabold text-slate-800 ml-1">{{ $menit }}</span> <span class="text-[10px] font-bold text-slate-400">M</span>
                                    </div>
                                    <p class="text-[8px] text-orange-600 font-semibold">Bulan ini</p>
                                </div>

                                {{-- Total Hari Kerja --}}
                                <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-3 flex flex-col justify-between min-h-[90px] shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[9px] uppercase font-bold text-blue-700 tracking-wider">Hari Kerja</span>
                                        <span class="text-blue-500 text-xs"><i class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <div>
                                        <span class="text-base font-extrabold text-slate-800">{{ $hariKerjaAktif }}</span> <span class="text-[10px] font-bold text-slate-400">Hari</span>
                                    </div>
                                    <p class="text-[8px] text-blue-600 font-semibold">Periode ini</p>
                                </div>
                            </div>

                            @php
                                $hadirLembur = $rekap['hadir'] + $rekap['lembur'];
                                $persentaseKehadiran = $hariKerjaAktif > 0 ? min(100, round(($hadirLembur / $hariKerjaAktif) * 100)) : 0;

                                // Hitung data jam masuk rata-rata
                                $checkIns = collect($detailHarian)->filter(fn($item) => $item->jam_masuk && in_array(strtolower($item->status), ['hadir', 'lembur']));
                                $terlambatCount = 0;
                                $rataMasuk = '-';
                                if ($checkIns->isNotEmpty()) {
                                    $totalMinutes = $checkIns->map(function($item) {
                                        $time = \Carbon\Carbon::parse($item->jam_masuk);
                                        return ($time->hour * 60) + $time->minute;
                                    })->average();
                                    $avgHour = floor($totalMinutes / 60);
                                    $avgMin = round($totalMinutes % 60);
                                    $rataMasuk = sprintf('%02d:%02d WIB', $avgHour, $avgMin);
                                    
                                    // Hitung keterlambatan riil
                                    $terlambatCount = $checkIns->filter(function($item) {
                                         $waktuMasuk = \Carbon\Carbon::parse($item->jam_masuk, 'Asia/Jakarta');
                                         $batas = \Carbon\Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                                         return $waktuMasuk->gt($batas);
                                    })->count();
                                }
                                $tepatWaktuCount = max(0, $rekap['hadir'] - $terlambatCount);
                            @endphp

                            {{-- Persentase Kehadiran --}}
                            <div class="mt-4 bg-gray-50/50 border border-gray-100 rounded-xl p-3">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Tingkat Kehadiran</span>
                                    <span class="text-xs font-extrabold text-blue-600">{{ $persentaseKehadiran }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    
                                </div>
                            </div>

                            {{-- Analisis Ketepatan Waktu --}}
                            <div class="mt-4">
                                <span class="text-[9px] uppercase font-extrabold text-slate-400 tracking-wider block mb-2">Analisis Ketepatan Waktu</span>
                                <div class="space-y-2">
                                    {{-- Rata-rata jam masuk --}}
                                    <div class="flex items-center justify-between bg-slate-50/60 px-3 py-2 rounded-xl border border-slate-100">
                                        <span class="text-[10px] font-bold text-slate-500 flex items-center gap-1.5">
                                            <i class="fas fa-business-time text-slate-400"></i> Rata-rata Jam Masuk
                                        </span>
                                        <span class="text-xs font-black text-slate-700 font-mono">{{ $rataMasuk }}</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="flex items-center justify-between bg-emerald-50/40 px-2.5 py-1.5 rounded-lg border border-emerald-100/30">
                                            <span class="text-[10px] font-semibold text-emerald-700 flex items-center gap-1.5">
                                                <i class="fas fa-check-circle text-[8px]"></i> Tepat Waktu
                                            </span>
                                            <span class="text-[10px] font-bold text-emerald-700">{{ $tepatWaktuCount }} Hari</span>
                                        </div>
                                        <div class="flex items-center justify-between bg-orange-50/40 px-2.5 py-1.5 rounded-lg border border-orange-100/30">
                                            <span class="text-[10px] font-semibold text-orange-700 flex items-center gap-1.5">
                                                <i class="fas fa-exclamation-circle text-[8px]"></i> Terlambat
                                            </span>
                                            <span class="text-[10px] font-bold text-orange-700">{{ $terlambatCount }} Kali</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-[9px] text-slate-400 leading-relaxed border-t border-slate-100 pt-2 mt-4">
                            Hari kerja aktif mengecualikan akhir pekan & libur nasional.
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUB-PAGE CONTENT: RIWAYAT --}}
            <div id="subpage-content-riwayat" class="hidden space-y-6">
                {{-- 5. DETAIL HARIAN --}}
                <div class="glass-card" style="padding: 24px; overflow: hidden;">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-6">
                        <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-600 p-1.5 rounded-lg text-xs"><i class="fas fa-list"></i></span>
                            Detail Harian
                        </h3>
                        
                        {{-- Tabs Filter --}}
                        <div class="flex items-center gap-2 overflow-x-auto pb-1.5 scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-transparent -mx-4 px-4 md:mx-0 md:px-0 max-w-full">
                            <button type="button" onclick="filterAbsen('semua')" class="tab-btn active shrink-0" id="tab-semua">
                                <i class="fas fa-calendar-days text-[10px]"></i> Semua ({{ count($detailHarian) }})
                            </button>
                            <button type="button" onclick="filterAbsen('hadir')" class="tab-btn shrink-0" id="tab-hadir">
                                <i class="fas fa-user-check text-[10px]"></i> Hadir/Lembur ({{ $rekap['hadir'] + $rekap['lembur'] }})
                            </button>
                            <button type="button" onclick="filterAbsen('izin')" class="tab-btn shrink-0" id="tab-izin">
                                <i class="fas fa-file-signature text-[10px]"></i> Izin/Sakit/Cuti ({{ $rekap['izin'] + $rekap['sakit'] + $rekap['cuti'] }})
                            </button>
                            <button type="button" onclick="filterAbsen('alpa')" class="tab-btn shrink-0" id="tab-alpa">
                                <i class="fas fa-user-times text-[10px]"></i> Alpa ({{ $rekap['alpa'] }})
                            </button>
                            <button type="button" onclick="filterAbsen('libur')" class="tab-btn shrink-0" id="tab-libur">
                                <i class="fas fa-mug-hot text-[10px]"></i> Libur ({{ collect($detailHarian)->filter(fn($item) => strtolower($item->status) === 'libur')->count() }})
                            </button>
                        </div>
                    </div>
                         {{-- UNIFIED RIWAYAT LIST (DESKTOP & MOBILE) --}}
                    <div class="space-y-3 mt-4">
                        @forelse($detailHarian as $item)
                            @php
                                $status = strtolower($item->status);
                                $isLate = false;
                                if ($status === 'hadir' && $item->jam_masuk) {
                                     $waktuMasuk = \Carbon\Carbon::parse($item->jam_masuk, 'Asia/Jakarta');
                                     $batas = \Carbon\Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                                     $isLate = $waktuMasuk->gt($batas);
                                }
                                
                                $iconClass = match($status) {
                                    'hadir'  => 'fas fa-check text-xs',
                                    'lembur' => 'fas fa-business-time text-xs',
                                    'sakit'  => 'fas fa-briefcase-medical text-xs',
                                    'izin'   => 'fas fa-envelope text-xs',
                                    'cuti'   => 'fas fa-plane text-xs',
                                    'alpa'   => 'fas fa-user-slash text-xs',
                                    'libur'  => 'fas fa-bed text-xs',
                                    default  => 'fas fa-info text-xs',
                                };
                                $iconBox = match($status) {
                                    'hadir'  => 'bg-emerald-100 text-emerald-600',
                                    'lembur' => 'bg-indigo-100 text-indigo-600',
                                    'sakit'  => 'bg-rose-100 text-rose-600',
                                    'izin'   => 'bg-amber-100 text-amber-600',
                                    'cuti'   => 'bg-purple-100 text-purple-600',
                                    'alpa'   => 'bg-red-100 text-red-600',
                                    'libur'  => 'bg-slate-100 text-slate-500',
                                    default  => 'bg-slate-100 text-slate-600',
                                };
                                $badgeStyle = match($status) {
                                    'hadir'  => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                    'lembur' => 'bg-indigo-100 text-indigo-700 border border-indigo-200',
                                    'sakit'  => 'bg-rose-100 text-rose-700 border border-rose-200',
                                    'izin'   => 'bg-amber-100 text-amber-700 border border-amber-200',
                                    'cuti'   => 'bg-purple-100 text-purple-700 border border-purple-200',
                                    'alpa'   => 'bg-red-100 text-red-700 border border-red-200',
                                    'libur'  => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    default  => 'bg-slate-100 text-slate-700 border border-slate-200',
                                };
                            @endphp
                            
                            <div class="riwayat-item absen-row flex flex-col md:flex-row md:items-center gap-3 md:gap-4 p-4 bg-white/80 hover:bg-white border border-slate-100 hover:border-blue-200 transition-all rounded-2xl shadow-sm hover:shadow-md" data-status="{{ $status }}">
                                {{-- Header Row (Icon, Date, Badge for Mobile) --}}
                                <div class="flex items-center justify-between w-full md:w-auto">
                                    <div class="flex items-center gap-3">
                                        {{-- Status Icon --}}
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $iconBox }} flex-shrink-0 shadow-inner">
                                            <i class="{{ $iconClass }}"></i>
                                        </div>
                                        {{-- Date --}}
                                        <div>
                                            <p class="font-extrabold text-slate-800 text-sm tracking-tight">
                                                {{ $item->tanggal->translatedFormat('l, d F Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Right badge status (Mobile only) --}}
                                    <span class="riwayat-badge {{ $badgeStyle }} md:hidden text-[10px] font-black py-1 px-2.5 rounded-full uppercase tracking-wider">
                                        {{ $item->status }}
                                    </span>
                                </div>
                                
                                {{-- Content / Details Row (Times, Activity Logs) --}}
                                <div class="flex-grow min-w-0 pl-[52px] md:pl-0 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full md:w-auto">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if(in_array($status, ['hadir', 'lembur']) && ($item->jam_masuk || $item->jam_keluar))
                                            <span class="text-xs text-slate-500 flex flex-wrap items-center gap-1.5">
                                                <span class="bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100/50 text-emerald-800">
                                                    Masuk: <strong class="font-mono text-emerald-900 font-extrabold">{{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '--:--' }}</strong>
                                                </span>
                                                <span class="bg-rose-50 px-2.5 py-1 rounded-md border border-rose-100/50 text-rose-800">
                                                    Keluar: <strong class="font-mono text-rose-900 font-extrabold">{{ $item->jam_keluar ? \Carbon\Carbon::parse($item->jam_keluar)->format('H:i') : '--:--' }}</strong>
                                                </span>
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400 font-medium italic">
                                                @if($status === 'libur')
                                                    {{ $item->keterangan ?? 'Hari Libur / Akhir Pekan' }}
                                                @elseif($status === 'cuti')
                                                    Cuti Tahunan
                                                @elseif($status === 'sakit')
                                                    Sakit / Istirahat
                                                @elseif($status === 'izin')
                                                    Izin Tidak Masuk
                                                @elseif($status === 'alpa')
                                                    Alpa (Tanpa Keterangan)
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        @endif
                                        
                                        {{-- Keterangan Terlambat --}}
                                        @if($isLate)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-wider bg-orange-100 text-orange-700 border border-orange-200">
                                                Terlambat
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Activity Logs link --}}
                                    <div class="mt-1 sm:mt-0">
                                        @if($item->jumlah_aktivitas > 0)
                                            <button type="button" onclick="openModalAktivitas('{{ $item->tanggal->toDateString() }}')" class="inline-flex items-center gap-1.5 text-xs text-blue-600 font-bold hover:underline bg-blue-50/50 hover:bg-blue-50 px-3.5 py-1.5 rounded-full border border-blue-100 transition-all cursor-pointer">
                                                <i class="fas fa-clipboard-list text-[10px]"></i>
                                                Lihat {{ $item->jumlah_aktivitas }} Aktivitas
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Right badge status (Desktop only) --}}
                                <span class="riwayat-badge {{ $badgeStyle }} hidden md:inline-block text-[10px] font-black py-1 px-3 rounded-full uppercase tracking-wider">
                                    {{ $item->status }}
                                </span>
                            </div>
                        @empty
                            <div class="riwayat-empty" style="background:#fff; border: 2px dashed #e2e8f0; border-radius: 16px; padding: 32px 16px; text-align: center;">
                                <i class="far fa-calendar-times text-3xl text-gray-300 mb-2 block"></i>
                                <p class="text-gray-500 text-sm font-medium">Tidak ada data absensi untuk periode ini.</p>
                            </div>
                        @endforelse
                        
                        <div id="empty-state-row" class="hidden riwayat-empty" style="background:#fff; border: 2px dashed #e2e8f0; border-radius: 16px; padding: 32px 16px; text-align: center;">
                            <i class="far fa-calendar-times text-3xl text-gray-300 mb-2 block"></i>
                            <p class="text-gray-500 text-sm font-medium">Tidak ada data absensi untuk filter ini.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL AKTIVITAS --}}
    <div id="modalAktivitas" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
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
                        
                        <p class="text-xs text-gray-500 font-medium">Sedang memuat data...</p>
                    </div>
                    
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
                                     
                                   </div>` 
                                : '';
                            
                            const html = `
                                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative pl-5">
                                    
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

            function switchSubPage(tabName) {
                // 1. Update navigation tab active class
                document.querySelectorAll('.subpage-nav-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                const activeNav = document.getElementById('subnav-' + tabName);
                if (activeNav) {
                    activeNav.classList.add('active');
                }

                // 2. Hide/Show page contents
                const ringkasanContent = document.getElementById('subpage-content-ringkasan');
                const riwayatContent = document.getElementById('subpage-content-riwayat');
                
                if (tabName === 'ringkasan') {
                    ringkasanContent.classList.remove('hidden');
                    riwayatContent.classList.add('hidden');
                } else if (tabName === 'riwayat') {
                    ringkasanContent.classList.add('hidden');
                    riwayatContent.classList.remove('hidden');
                }
            }

            function filterAbsen(type) {
                // 1. Update active tab styling
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                const activeBtn = document.getElementById('tab-' + type);
                if (activeBtn) {
                    activeBtn.classList.add('active');
                }
                
                // 2. Filter unified list items
                const rows = document.querySelectorAll('.absen-row');
                let visibleRows = 0;
                rows.forEach(row => {
                    const status = row.getAttribute('data-status');
                    let show = false;
                    if (type === 'semua') {
                        show = true;
                    } else if (type === 'hadir') {
                        show = (status === 'hadir' || status === 'lembur');
                    } else if (type === 'izin') {
                        show = (status === 'izin' || status === 'sakit' || status === 'cuti');
                    } else if (type === 'alpa') {
                        show = (status === 'alpa');
                    } else if (type === 'libur') {
                        show = (status === 'libur');
                    }
                    
                    if (show) {
                        row.style.display = 'flex';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                const emptyRow = document.getElementById('empty-state-row');
                if (emptyRow) {
                    if (visibleRows === 0 && rows.length > 0) {
                        emptyRow.classList.remove('hidden');
                    } else {
                        emptyRow.classList.add('hidden');
                    }
                }
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
