<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    @push('styles')
    <style>
        /* ===== Modern Premium Styles ===== */
        /* Wrapper */
        .absen-page-wrapper { 
            padding: 16px 16px 40px; 
            position: relative;
            z-index: 10;
        }
        @media (min-width: 768px) {
            .absen-page-wrapper { padding: 24px 24px 48px; }
        }

        /* Modern Mesh Background */
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

        /* Modern Back Button */
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

        /* Glass Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }        .glass-card-title {
            font-size: 1.05rem; font-weight: 800; color: #1e293b;
            display: flex; align-items: center; gap: 10px;
        }

        /* Forms & Inputs */
        .modern-label { display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 8px; }
        .modern-input {
            width: 100%; background: rgba(255, 255, 255, 0.95);
            border: 2px solid #e2e8f0; border-radius: 14px;
            padding: 12px 16px; font-size: 0.95rem; color: #1e293b;
            outline: none; transition: all 0.2s ease;
        }
        .modern-input:focus {
            border-color: #3b82f6; background: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white; border: none; padding: 16px 24px; border-radius: 14px;
            font-weight: 700; font-size: 1rem;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; transition: all 0.2s;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3); cursor: pointer;
        }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4); }

        /* Custom thin scrollbar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Custom Solid Gradient backgrounds for Rekap */
        .bg-rekap-hadir { background: linear-gradient(135deg, #10b981, #0d9488) !important; color: white !important; }
        .bg-rekap-sakit { background: linear-gradient(135deg, #f43f5e, #dc2626) !important; color: white !important; }
        .bg-rekap-izin { background: linear-gradient(135deg, #f59e0b, #ea580c) !important; color: white !important; }
        .bg-rekap-cuti { background: linear-gradient(135deg, #3b82f6, #4f46e5) !important; color: white !important; }
        .bg-rekap-alpa { background: linear-gradient(135deg, #64748b, #475569) !important; color: white !important; }
        .bg-rekap-lembur { background: linear-gradient(135deg, #a855f7, #7c3aed) !important; color: white !important; }
        .bg-rekap-terlambat { background: linear-gradient(135deg, #f97316, #d97706) !important; color: white !important; }
    </style>
    @endpush
    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">
        {{-- Background Decorations --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-[10%] -left-[5%] w-[40vw] h-[40vw] max-w-[600px] max-h-[600px] bg-blue-400/20 blur-[100px] rounded-full animate-float mix-blend-multiply"></div>
            <div class="absolute top-[30%] -right-[10%] w-[35vw] h-[35vw] max-w-[500px] max-h-[500px] bg-purple-400/20 blur-[100px] rounded-full animate-float-delayed mix-blend-multiply"></div>
            <div class="absolute -bottom-[10%] left-[20%] w-[45vw] h-[45vw] max-w-[700px] max-h-[700px] bg-cyan-400/20 blur-[120px] rounded-full animate-float mix-blend-multiply" style="animation-delay: 2s;"></div>
            <div class="absolute inset-0" style="background-image: radial-gradient(rgba(100, 116, 139, 0.15) 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col absen-page-wrapper">
            
            {{-- ALERT MESSAGES --}}
            
            
            @if ($errors->any())
                <div class="bg-red-500/10 backdrop-blur-md border border-red-200/50 text-red-700 p-4 rounded-xl mb-4 text-sm" role="alert">
                    <p class="font-bold flex items-center gap-2 mb-1"><i class="fas fa-exclamation-triangle"></i> Terjadi Kesalahan</p>
                    <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif
            
            {{-- KONDISI 1: LIBUR TOTAL (Minggu / Tanggal Merah) --}}
            @if($isHoliday)
                <div class="bg-red-500/10 backdrop-blur-md border border-red-200/50 rounded-2xl p-4 flex items-start gap-3.5 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                        <i class="fas fa-calendar-times text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-red-800">Hari Libur</p>
                        <p class="text-xs text-red-700/90 leading-relaxed mt-0.5">
                            Hari ini adalah 
                            @if($holidayDb)
                                <span class="font-bold">{{ $holidayDb->keterangan }}</span>.
                            @else
                                <span class="font-bold">Hari Minggu</span>.
                            @endif
                            Absensi tetap dibuka khusus untuk petugas piket atau lembur.
                        </p>
                    </div>
                </div>

            {{-- KONDISI 2: SABTU OPSIONAL --}}
            @elseif($isSaturdayOpen)
                <div class="bg-blue-500/10 backdrop-blur-md border border-blue-200/50 rounded-2xl p-4 flex items-start gap-3.5 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                        <i class="fas fa-umbrella-beach text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-blue-800">Hari Sabtu (Opsional)</p>
                        <p class="text-xs text-blue-700/90 leading-relaxed mt-0.5">
                            Kehadiran hari ini bersifat opsional. Tidak tercatat sebagai Alpha jika tidak hadir.
                        </p>
                    </div>
                </div>
            @endif

            {{-- TOMBOL KEMBALI MODERN --}}
            <a href="{{ route('dashboard') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Dashboard
            </a>

            @if ($absensiHariIni)
                {{-- ========================================================= --}}
                {{-- KONDISI 1: SUDAH ABSEN MASUK --}}
                {{-- ========================================================= --}}
                <div class="flex flex-col lg:flex-row gap-6 items-stretch">
                    {{-- Main Card --}}
                    <div class="w-full lg:w-2/3 bg-white/90 backdrop-blur-xl border border-white/50 shadow-2xl shadow-slate-200/50 rounded-3xl flex flex-col justify-between p-6 md:p-8">
                        <div>
                            <div class="flex items-center gap-4 mb-8">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-600 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/30">
                                    <i class="fas fa-check-double text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-extrabold text-slate-800">Absensi Hari Ini</h3>
                                    <p class="text-xs text-slate-400 mt-0.5">Status: <span class="font-extrabold text-blue-600 uppercase">{{ $absensiHariIni->status }}</span></p>
                                </div>
                            </div>
                            
                            @if($absensiHariIni->keterangan)
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 mb-6 text-xs text-slate-600">
                                <span class="font-bold text-slate-700 block mb-1">Keterangan:</span>
                                {{ $absensiHariIni->keterangan }}
                            </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Absen Masuk --}}
                                <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 flex flex-col justify-between min-h-[140px]">
                                    <div>
                                        <div class="flex items-center text-emerald-800 mb-2">
                                            <i class="fas fa-sign-in-alt mr-2 text-sm"></i>
                                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Absen Masuk</p>
                                        </div>
                                        <p class="text-2xl font-black text-slate-800">{{ \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') }} <span class="text-xs font-bold text-slate-400">WIB</span></p>
                                    </div>
                                    @if($absensiHariIni->latitude && $absensiHariIni->longitude)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $absensiHariIni->latitude }},{{ $absensiHariIni->longitude }}" target="_blank" class="text-[10px] text-blue-600 font-bold hover:underline mt-4 inline-flex items-center gap-1 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100 w-fit">
                                            <i class="fas fa-map-marker-alt"></i> Lihat Lokasi
                                        </a>
                                    @endif
                                </div>

                                {{-- Absen Keluar --}}
                                <div class="bg-rose-50/50 border border-rose-100 rounded-xl p-4 flex flex-col justify-between min-h-[140px]">
                                    <div>
                                        <div class="flex items-center text-rose-800 mb-2">
                                            <i class="fas fa-sign-out-alt mr-2 text-sm"></i>
                                            <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Absen Keluar</p>
                                        </div>
                                        @if ($absensiHariIni->jam_keluar)
                                            <p class="text-2xl font-black text-slate-800">{{ \Carbon\Carbon::parse($absensiHariIni->jam_keluar)->format('H:i') }} <span class="text-xs font-bold text-slate-400">WIB</span></p>
                                            @if($absensiHariIni->latitude_keluar && $absensiHariIni->longitude_keluar)
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $absensiHariIni->latitude_keluar }},{{ $absensiHariIni->longitude_keluar }}" target="_blank" class="text-[10px] text-blue-600 font-bold hover:underline mt-4 inline-flex items-center gap-1 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100 w-fit">
                                                    <i class="fas fa-map-marker-alt"></i> Lihat Lokasi
                                                </a>
                                            @endif
                                        @else
                                            <p class="text-2xl font-black text-slate-300">--:--</p>
                                        @endif
                                    </div>
                                    @if (is_null($absensiHariIni->jam_keluar) && $absensiHariIni->status == 'hadir')
                                        <button type="button" id="btn-absen-keluar" class="w-full mt-4 bg-rose-600 text-white font-bold py-2 px-4 rounded-xl hover:bg-rose-700 transition duration-200 text-xs shadow-md shadow-rose-500/10">
                                            Absen Keluar Sekarang
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Lembur Buttons --}}
                        @if ($absensiHariIni->jam_keluar && $absensiHariIni->status == 'hadir')
                            @if (is_null($lemburHariIni))
                                <button type="button" id="btn-absen-lembur" class="w-full mt-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-3 px-4 rounded-xl hover:opacity-90 transition duration-200 text-xs shadow-lg shadow-purple-500/10">
                                    Absen Lembur Sekarang
                                </button>
                            @elseif (is_null($lemburHariIni->jam_keluar_lembur))
                                <button type="button" id="btn-absen-keluar-lembur" class="w-full mt-6 bg-rose-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-rose-700 transition duration-200 text-xs shadow-lg shadow-rose-500/10">
                                    Absen Keluar Lembur Sekarang
                                </button>
                            @else
                                 <div class="mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-center">
                                     <p class="text-xs font-bold"><i class="fas fa-check-circle mr-1"></i> Absensi Lembur Hari Ini Selesai.</p>
                                     <p class="text-[10px] text-emerald-700/80 mt-1 font-semibold">Waktu Lembur: {{ \Carbon\Carbon::parse($lemburHariIni->jam_masuk_lembur)->format('H:i') }} - {{ \Carbon\Carbon::parse($lemburHariIni->jam_keluar_lembur)->format('H:i') }}</p>
                                 </div>
                            @endif
                        @endif
                    </div>
                    
                    {{-- SIDEBAR REKAP --}}
                    <div class="w-full lg:w-1/3 flex flex-col gap-4 self-stretch min-h-full">
                        <button type="button" onclick="openRekapModal()" class="bg-white/90 backdrop-blur-xl border border-white/50 shadow-2xl shadow-slate-200/50 rounded-3xl p-5 md:p-6 flex items-center justify-between group hover:bg-blue-50/50 transition-all duration-300 w-full text-left">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/40">
                                    <i class="fas fa-calendar-alt text-xl group-hover:scale-110 transition-transform"></i>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-slate-800 text-sm">Rekap Bulan Ini</h3>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </div>
                        </button>

                        @if(isset($daftarRekan) && count($daftarRekan) > 0)
                        <div class="bg-white/90 backdrop-blur-xl border border-white/50 shadow-2xl shadow-slate-200/50 rounded-3xl flex flex-col flex-grow min-h-0 p-5 md:p-6">
                            <h3 class="glass-card-title mb-4">
                                <i class="fas fa-users text-blue-500"></i>
                                <span>Absensi Tim</span>
                            </h3>
                            <div class="space-y-3 overflow-y-auto pr-1 flex-grow scrollbar-thin" style="max-height: 450px;">
                                @foreach($daftarRekan as $rekan)
                                @php
                                    $badgeClass = match($rekan->status) {
                                        'hadir'  => 'bg-emerald-500/10 text-emerald-700 border-emerald-200/50',
                                        'sakit'  => 'bg-rose-500/10 text-rose-700 border-rose-200/50',
                                        'izin'   => 'bg-amber-500/10 text-amber-700 border-amber-200/50',
                                        default  => 'bg-slate-500/10 text-slate-700 border-slate-200/50',
                                    };
                                @endphp
                                <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 bg-white/50 transition-all duration-300 hover:bg-gradient-to-r hover:from-white hover:to-blue-50/50 hover:border-blue-200 hover:shadow-md hover:shadow-blue-500/5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $rekan->user->profile_picture ? asset('storage/' . $rekan->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($rekan->user->name ?? 'U').'&background=random&color=fff&size=64' }}"
                                             alt="{{ $rekan->user->name ?? '' }}" class="w-9 h-9 rounded-full object-cover border border-slate-100 flex-shrink-0">
                                        <span class="text-xs font-bold text-slate-700 truncate max-w-[120px]">{{ $rekan->user->name }}</span>
                                    </div>
                                    <span class="text-[9px] font-extrabold px-2.5 py-1 rounded-full border {{ $badgeClass }} uppercase">
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
                    <div class="flex flex-col lg:flex-row gap-6 items-stretch">
                        {{-- Main Card --}}
                        <div class="w-full lg:w-2/3 bg-white/90 backdrop-blur-xl border border-white/50 shadow-2xl shadow-slate-200/50 rounded-3xl flex flex-col justify-between p-6 md:p-8">
                            <div>
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/30">
                                        <i class="fas fa-exclamation-triangle text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-extrabold text-slate-800">Absen Keluar Tertunda</h3>
                                        <p class="text-xs text-slate-400 mt-0.5">Tanggal: <strong>{{ \Carbon\Carbon::parse($unfinishedAbsensi->tanggal)->translatedFormat('l, j F Y') }}</strong></p>
                                    </div>
                                </div>
                                <p class="text-xs text-amber-800 leading-relaxed bg-amber-50/50 border border-amber-100/80 p-3.5 rounded-xl mb-6 font-semibold">
                                    Anda belum melakukan absen keluar untuk hari sebelumnya. Silakan lengkapi data absensi Anda untuk melanjutkan.
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-emerald-50/50 border border-emerald-100 p-4 rounded-xl flex flex-col justify-between min-h-[120px]">
                                        <div>
                                            <div class="flex items-center text-emerald-800 mb-2">
                                                <i class="fas fa-sign-in-alt mr-2 text-sm"></i>
                                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Absen Masuk (Kemarin)</p>
                                            </div>
                                            <p class="text-2xl font-black text-slate-800">{{ \Carbon\Carbon::parse($unfinishedAbsensi->jam_masuk)->format('H:i') }} <span class="text-xs font-bold text-slate-400">WIB</span></p>
                                        </div>
                                    </div>
                                    <div class="bg-rose-50/50 border border-rose-100 p-4 rounded-xl flex flex-col justify-between min-h-[120px]">
                                        <div>
                                            <div class="flex items-center text-rose-800 mb-2">
                                                <i class="fas fa-sign-out-alt mr-2 text-sm"></i>
                                                <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Absen Keluar (Sekarang)</p>
                                            </div>
                                            <p class="text-2xl font-black text-slate-300">--:--</p>
                                        </div>
                                        <button type="button" id="btn-absen-keluar-unfinished" data-id="{{ $unfinishedAbsensi->id }}" class="w-full mt-4 bg-rose-600 text-white font-bold py-2.5 px-4 rounded-xl hover:bg-rose-700 transition duration-200 text-xs shadow-md shadow-rose-500/10">
                                            Absen Keluar Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="w-full lg:w-1/3 flex flex-col gap-4 self-stretch min-h-full">
                            <button type="button" onclick="openRekapModal()" class="bg-white/90 backdrop-blur-xl border border-white/50 shadow-2xl shadow-slate-200/50 rounded-3xl p-5 md:p-6 flex items-center justify-between group hover:bg-blue-50/50 transition-all duration-300 w-full text-left">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/40">
                                        <i class="fas fa-calendar-alt text-xl group-hover:scale-110 transition-transform"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-extrabold text-slate-800 text-sm">Rekap Bulan Ini</h3>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                                    </div>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                            </button>

                            @if(isset($daftarRekan) && count($daftarRekan) > 0)
                            <div class="bg-white/90 backdrop-blur-xl border border-white/50 shadow-2xl shadow-slate-200/50 rounded-3xl flex flex-col flex-grow min-h-0 p-5 md:p-6">
                                <h3 class="glass-card-title mb-4">
                                    <i class="fas fa-users text-blue-500"></i>
                                    <span>Absensi Tim</span>
                                </h3>
                                <div class="space-y-3 overflow-y-auto pr-1 flex-grow scrollbar-thin" style="max-height: 450px;">
                                    @foreach($daftarRekan as $rekan)
                                    @php
                                        $badgeClass = match($rekan->status) {
                                            'hadir'  => 'bg-emerald-500/10 text-emerald-700 border-emerald-200/50',
                                            'sakit'  => 'bg-rose-500/10 text-rose-700 border-rose-200/50',
                                            'izin'   => 'bg-amber-500/10 text-amber-700 border-amber-200/50',
                                            default  => 'bg-slate-500/10 text-slate-700 border-slate-200/50',
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 bg-white/50 transition-all duration-300 hover:bg-gradient-to-r hover:from-white hover:to-blue-50/50 hover:border-blue-200 hover:shadow-md hover:shadow-blue-500/5">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $rekan->user->profile_picture ? asset('storage/' . $rekan->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($rekan->user->name ?? 'U').'&background=random&color=fff&size=64' }}"
                                                 alt="{{ $rekan->user->name ?? '' }}" class="w-9 h-9 rounded-full object-cover border border-slate-100 flex-shrink-0">
                                            <span class="text-xs font-bold text-slate-700 truncate max-w-[120px]">{{ $rekan->user->name }}</span>
                                        </div>
                                        <span class="text-[9px] font-extrabold px-2.5 py-1 rounded-full border {{ $badgeClass }} uppercase">
                                            {{ str_replace('_', ' ', $rekan->status) }}
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- MODAL UNFINISHED (LAYOUT: KIRI KAMERA SQUARE, KANAN INFO) --}}
                    <div id="modal-absen-keluar-unfinished" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
                        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl transform transition-all duration-300 scale-95 opacity-0 overflow-hidden">
                            <form action="{{ route('absen.keluar', $unfinishedAbsensi->id) }}" method="POST" enctype="multipart/form-data" id="form-absen-keluar-unfinished">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="latitude_keluar" id="latitude-keluar-unfinished">
                                <input type="hidden" name="longitude_keluar" id="longitude-keluar-unfinished">
                                <div class="p-6">
                                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-sign-out-alt text-lg"></i></div>
                                        <div>
                                            <h3 class="text-base font-extrabold text-slate-800">Form Absen Keluar Tertunda</h3>
                                            <p class="text-xs text-slate-400">Ambil foto selfie untuk konfirmasi absen keluar hari sebelumnya.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                        {{-- Kamera SQUARE --}}
                                        <div>
                                            <label class="modern-label">Foto Selfie <span class="text-red-500">*</span></label>
                                            <div id="camera-container-keluar-unfinished" class="relative aspect-square rounded-2xl overflow-hidden bg-slate-900 shadow-md">
                                                <video id="video-keluar-unfinished" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                                <canvas id="canvas-keluar-unfinished" class="hidden"></canvas>
                                                <div id="snap-ui-keluar-unfinished" class="absolute inset-0 flex items-end justify-center p-4 bg-gradient-to-t from-black/60 to-transparent">
                                                    <button type="button" id="snap-keluar-unfinished" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400 transition-transform hover:scale-105" disabled>
                                                        <i class="fas fa-camera"></i>
                                                    </button>
                                                </div>
                                                <div id="preview-ui-keluar-unfinished" class="absolute inset-0 hidden bg-black">
                                                    <img id="preview-image-keluar-unfinished" src="" class="w-full h-full object-contain" alt="Pratinjau Foto"/>
                                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-gradient-to-t from-black/60 to-transparent">
                                                        <button type="button" id="retake-btn-keluar-unfinished" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center shadow-lg"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                                        <button type="button" id="use-photo-btn-keluar-unfinished" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center shadow-lg"><i class="fas fa-check mr-1.5"></i>Pakai</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="upload-label-keluar-unfinished" class="hidden">
                                                <input name="lampiran_keluar" id="lampiran-keluar-unfinished" type="file" class="hidden" accept="image/*" />
                                            </div>
                                        </div>

                                        {{-- Pesan --}}
                                        <div class="flex flex-col justify-center bg-slate-50 border border-slate-100 rounded-2xl p-4 text-xs text-slate-500 leading-relaxed font-semibold italic">
                                            <p><i class="fas fa-info-circle text-blue-500 mr-1"></i> Mohon pastikan wajah Anda terlihat jelas pada kamera sebelum mengambil gambar.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100">
                                    <button type="button" id="btn-tutup-modal-keluar-unfinished" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2.5 px-5 rounded-xl text-xs transition duration-200">Batal</button>
                                    <button type="submit" id="submit-button-keluar-unfinished" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-5 rounded-xl text-xs shadow-md shadow-rose-500/10 transition duration-200 disabled:bg-slate-300">Kirim</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- ========================================================= --}}
                    {{-- KONDISI 3: BELUM ABSEN (MAIN FORM - MODIFIKASI MOBILE) --}}
                    {{-- ========================================================= --}}
                    <div class="flex flex-col lg:flex-row gap-6 items-stretch">
                        
                        @if ($isHoliday)
                            {{-- TAMPILAN KHUSUS WEEKEND (BELUM ABSEN LEMBUR) --}}
                            <div class="w-full lg:w-2/3 bg-white/90 backdrop-blur-xl border border-white/50 shadow-2xl shadow-slate-200/50 rounded-3xl p-6 md:p-8">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-10 h-10 rounded-xl bg-purple-100/70 text-purple-600 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-umbrella-beach text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-extrabold text-slate-800">Lembur Akhir Pekan</h3>
                                        <p class="text-xs text-slate-400 mt-0.5">Hari Libur / Weekend</p>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed mb-6">
                                    Hari ini adalah hari libur atau akhir pekan. Anda dapat melakukan absensi lembur di bawah ini jika terjadwal piket atau tugas lembur.
                                </p>
                                
                                @if (is_null($lemburHariIni))
                                    <button type="button" id="btn-absen-lembur" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-3.5 px-4 rounded-xl hover:opacity-90 transition duration-200 text-xs shadow-lg shadow-purple-500/10">
                                        Absen Lembur Sekarang
                                    </button>
                                @elseif (is_null($lemburHariIni->jam_keluar_lembur))
                                    <div class="mb-6 p-4 rounded-xl border border-purple-100 bg-purple-50/50">
                                        <p class="font-bold text-xs text-purple-800"><i class="fas fa-clock mr-1"></i> Anda Sudah Masuk Lembur</p>
                                        <p class="text-2xl font-black mt-2 text-slate-800">{{ \Carbon\Carbon::parse($lemburHariIni->jam_masuk_lembur)->format('H:i') }} <span class="text-xs font-bold text-slate-400">WIB</span></p>
                                        @if($lemburHariIni->keterangan)
                                            <p class="text-xs mt-2 text-slate-500">Keterangan: {{ $lemburHariIni->keterangan }}</p>
                                        @endif
                                    </div>
                                    <button type="button" id="btn-absen-keluar-lembur" class="w-full bg-rose-600 text-white font-bold py-3.5 px-4 rounded-xl hover:bg-rose-700 transition duration-200 text-xs shadow-lg shadow-rose-500/10">
                                        Absen Keluar Lembur Sekarang
                                    </button>
                                @else
                                    <div class="p-4 rounded-xl text-center border border-emerald-100 bg-emerald-50/50 text-emerald-800">
                                        <i class="fas fa-check-circle text-2xl mb-2"></i>
                                        <p class="text-xs font-bold">Absensi Lembur Hari Ini Selesai.</p>
                                        <p class="text-[10px] text-emerald-700/80 mt-1 font-semibold">Waktu Lembur: {{ \Carbon\Carbon::parse($lemburHariIni->jam_masuk_lembur)->format('H:i') }} - {{ \Carbon\Carbon::parse($lemburHariIni->jam_keluar_lembur)->format('H:i') }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- TAMPILAN HARI BIASA (BELUM ABSEN) --}}
                             <form action="{{ route('absen.store') }}" method="POST" enctype="multipart/form-data" id="form-absen" class="w-full lg:w-2/3">
                                 @csrf
                                 <input type="hidden" name="latitude" id="latitude">
                                 <input type="hidden" name="longitude" id="longitude">

                                 {{-- KONTAINER UTAMA --}}
                                 <div class="bg-white/90 backdrop-blur-xl border border-white/50 shadow-2xl shadow-slate-200/50 rounded-3xl p-6 md:p-8">
                                     {{-- Title Header --}}
                                     <div class="flex items-center gap-4 mb-8 border-b border-slate-100/70 pb-5">
                                         <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 flex-shrink-0">
                                             <i class="fas fa-fingerprint text-xl"></i>
                                         </div>
                                         <div>
                                             <h3 class="text-base font-extrabold text-slate-800">Form Absensi Harian</h3>
                                             <p class="text-xs text-slate-400">Pilih status kehadiran dan lengkapi data absensi masuk Anda.</p>
                                         </div>
                                     </div>

                                     {{-- 1. TANGGAL & JAM (FRESH SEGMENTED LAYOUT) --}}
                                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                                         <div class="bg-white/80 border border-slate-100 rounded-2xl p-3.5 flex items-center gap-3.5 transition-all duration-300 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10 hover:-translate-y-0.5">
                                             <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center text-sm shadow-md shadow-blue-500/30"><i class="fas fa-calendar-alt"></i></div>
                                             <div>
                                                 <p class="text-[9px] uppercase font-extrabold text-slate-400 tracking-wider">Hari & Tanggal</p>
                                                 <p class="font-black text-xs text-slate-700 mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('l, j F Y') }}</p>
                                             </div>
                                         </div>
                                         <div class="bg-white/80 border border-slate-100 rounded-2xl p-3.5 flex items-center gap-3.5 transition-all duration-300 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-500/10 hover:-translate-y-0.5">
                                             <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 text-white flex items-center justify-center text-sm shadow-md shadow-indigo-500/30"><i class="fas fa-clock"></i></div>
                                             <div>
                                                 <p class="text-[9px] uppercase font-extrabold text-slate-400 tracking-wider">Jam Real-time</p>
                                                 <p class="font-black text-xs text-slate-700 mt-0.5" id="jam-realtime"></p>
                                             </div>
                                         </div>
                                     </div>

                                     {{-- 2. STATUS KEHADIRAN (MAC-OS STYLE SEGMENTED CONTROL) --}}
                                     <div class="mb-6"> 
                                         <label class="modern-label flex items-center justify-between">
                                             <span>Pilih Status Kehadiran</span>
                                             <span class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600" id="status-label">hadir</span>
                                         </label>
                                         <input type="hidden" name="status" id="status" value="hadir">
                                         <div class="bg-slate-100/70 p-1.5 rounded-2xl flex gap-1 border border-slate-200/40" id="status-buttons">
                                             <button type="button" data-status="hadir" class="status-btn flex-grow flex items-center justify-center gap-2 font-extrabold py-3 rounded-xl transition-all duration-300 text-xs shadow-sm bg-emerald-500 text-white shadow-md shadow-emerald-500/10 border-0">
                                                 <i class="fas fa-check-circle text-sm"></i> Hadir
                                             </button>
                                             <button type="button" data-status="izin" class="status-btn flex-grow flex items-center justify-center gap-2 font-extrabold py-3 rounded-xl transition-all duration-300 text-xs shadow-none bg-transparent text-slate-500 hover:bg-slate-200/50 hover:text-slate-700 border-0">
                                                 <i class="fas fa-envelope-open-text text-sm"></i> Izin
                                             </button>
                                             <button type="button" data-status="sakit" class="status-btn flex-grow flex items-center justify-center gap-2 font-extrabold py-3 rounded-xl transition-all duration-300 text-xs shadow-none bg-transparent text-slate-500 hover:bg-slate-200/50 hover:text-slate-700 border-0">
                                                 <i class="fas fa-clinic-medical text-sm"></i> Sakit
                                             </button>
                                         </div>
                                     </div>
                                     
                                     {{-- 3. KETERANGAN & KAMERA --}}
                                     <div class="mb-6">
                                         <label for="keterangan" class="modern-label">
                                             Keterangan & Lampiran <span id="keterangan-wajib" class="text-red-500 font-normal hidden">*</span>
                                         </label>
                                         
                                         <div class="grid grid-cols-1 md:grid-cols-5 gap-5">
                                             {{-- KAMERA --}}
                                             <div class="md:col-span-3">
                                                 <div id="camera-container" class="relative aspect-video rounded-3xl overflow-hidden bg-slate-900 shadow-md border border-slate-100">
                                                     <video id="video" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                                     <canvas id="canvas" class="hidden"></canvas>
                                                     <div id="snap-ui" class="absolute inset-0 flex items-end justify-center p-4 bg-gradient-to-t from-black/60 to-transparent">
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

                                                 <label for="lampiran" id="upload-label" class="flex flex-col items-center justify-center w-full h-full border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-3xl cursor-pointer bg-slate-50/50 hover:bg-slate-50 transition-all aspect-video hidden relative p-4">
                                                     <div class="flex flex-col items-center justify-center text-center p-2" id="upload-ui">
                                                         <div class="w-14 h-14 bg-blue-500/10 text-blue-600 rounded-full flex items-center justify-center mb-3 shadow-inner">
                                                             <i id="upload-icon" class="fas fa-cloud-upload-alt text-2xl"></i>
                                                         </div>
                                                         <p id="upload-text" class="text-xs text-slate-700 font-extrabold">Pilih Berkas Lampiran</p>
                                                         <p class="text-[10px] text-slate-400 mt-1.5">Mendukung format JPG, PNG, atau PDF</p>
                                                     </div>
                                                     <input name="lampiran" id="lampiran" type="file" class="hidden" accept="image/*,application/pdf" />
                                                 </label>
                                             </div>

                                             {{-- KETERANGAN --}}
                                             <div class="md:col-span-2">
                                                 <textarea name="keterangan" id="keterangan" class="modern-input h-full resize-none min-h-[140px] md:min-h-full placeholder:text-slate-400/70 text-xs placeholder:text-[11px]" placeholder="Mohon tuliskan alasan atau keterangan kehadiran Anda secara jelas... (Wajib diisi jika Anda memilih status Izin atau Sakit)">{{ old('keterangan') }}</textarea>
                                             </div>
                                         </div>
                                     </div>

                                     {{-- SUBMIT --}}
                                     <div class="pt-4 border-t border-slate-100 mt-6">
                                         <button type="submit" id="submit-button" class="btn-gradient w-full py-4 text-xs font-black tracking-wide uppercase flex items-center justify-center gap-2 rounded-xl shadow-lg shadow-blue-500/25 transition duration-300">
                                             <i class="fas fa-fingerprint text-sm"></i> Kirim Absensi Masuk
                                         </button>
                                     </div>
                                 </div>
                             </form>
                         @endif
                         
         {{-- SIDEBAR REKAP UNTUK BELUM ABSEN --}}
                        <div class="w-full lg:w-1/3 flex flex-col gap-4 self-stretch min-h-full">
                            <button type="button" onclick="openRekapModal()" class="bg-white/90 backdrop-blur-xl border border-white/50 shadow-2xl shadow-slate-200/50 rounded-3xl p-5 md:p-6 flex items-center justify-between group hover:bg-blue-50/50 transition-all duration-300 w-full text-left">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/40">
                                        <i class="fas fa-calendar-alt text-xl group-hover:scale-110 transition-transform"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-extrabold text-slate-800 text-sm">Rekap Bulan Ini</h3>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                                    </div>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                            </button>
                            @if(isset($daftarRekan) && count($daftarRekan) > 0)
                                <div class="bg-white/90 backdrop-blur-xl border border-white/50 shadow-2xl shadow-slate-200/50 rounded-3xl flex flex-col flex-grow min-h-0 p-5 md:p-6">
                                    <h3 class="glass-card-title mb-4">
                                        <i class="fas fa-users text-blue-500"></i>
                                        <span>Absensi Tim</span>
                                    </h3>
                                    <div class="space-y-3 overflow-y-auto pr-1 flex-grow scrollbar-thin" style="max-height: 450px;">
                                        @foreach($daftarRekan as $rekan)
                                        @php
                                            $badgeClass = match($rekan->status) {
                                                'hadir'  => 'bg-emerald-500/10 text-emerald-700 border-emerald-200/50',
                                                'sakit'  => 'bg-rose-500/10 text-rose-700 border-rose-200/50',
                                                'izin'   => 'bg-amber-500/10 text-amber-700 border-amber-200/50',
                                                default  => 'bg-slate-500/10 text-slate-700 border-slate-200/50',
                                            };
                                        @endphp
                                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 bg-white/50 transition-all duration-300 hover:bg-gradient-to-r hover:from-white hover:to-blue-50/50 hover:border-blue-200 hover:shadow-md hover:shadow-blue-500/5">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $rekan->user->profile_picture ? asset('storage/' . $rekan->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($rekan->user->name ?? 'U').'&background=random&color=fff&size=64' }}"
                                                     alt="{{ $rekan->user->name ?? '' }}" class="w-9 h-9 rounded-full object-cover border border-slate-100 flex-shrink-0">
                                                <span class="text-xs font-bold text-slate-700 truncate max-w-[120px]">{{ $rekan->user->name }}</span>
                                            </div>
                                            <span class="text-[9px] font-extrabold px-2.5 py-1 rounded-full border {{ $badgeClass }} uppercase">
                                                {{ str_replace('_', ' ', $rekan->status) }}
                                            </span>
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
    <div id="modal-absen-keluar" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl transform transition-all duration-300 scale-95 opacity-0 overflow-hidden">
            <form action="{{ route('absen.keluar', $absensiHariIni->id) }}" method="POST" enctype="multipart/form-data" id="form-absen-keluar">
                @csrf
                @method('PATCH')
                <input type="hidden" name="latitude_keluar" id="latitude-keluar">
                <input type="hidden" name="longitude_keluar" id="longitude-keluar">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-sign-out-alt text-lg"></i></div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-800">Form Absen Keluar</h3>
                            <p class="text-xs text-slate-400">Ambil foto selfie untuk konfirmasi absen keluar.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        {{-- Kamera SQUARE --}}
                        <div>
                            <label class="modern-label">Foto Selfie <span class="text-red-500">*</span></label>
                            <div id="camera-container-keluar" class="relative aspect-square rounded-2xl overflow-hidden bg-slate-900 shadow-md">
                                <video id="video-keluar" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                <canvas id="canvas-keluar" class="hidden"></canvas>
                                <div id="snap-ui-keluar" class="absolute inset-0 flex items-end justify-center p-4 bg-gradient-to-t from-black/60 to-transparent">
                                    <button type="button" id="snap-keluar" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400 transition-transform hover:scale-105" disabled>
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div id="preview-ui-keluar" class="absolute inset-0 hidden bg-black">
                                    <img id="preview-image-keluar" src="" class="w-full h-full object-contain" alt="Pratinjau Foto"/>
                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-gradient-to-t from-black/60 to-transparent">
                                        <button type="button" id="retake-btn-keluar" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center shadow-lg"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                        <button type="button" id="use-photo-btn-keluar" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center shadow-lg"><i class="fas fa-check mr-1.5"></i>Pakai</button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload-label-keluar" class="hidden">
                                <input name="lampiran_keluar" id="lampiran-keluar" type="file" class="hidden" accept="image/*" />
                            </div>
                        </div>
                        
                        {{-- Info --}}
                        <div class="flex flex-col justify-center bg-slate-50 border border-slate-100 rounded-2xl p-4 text-xs text-slate-500 leading-relaxed font-semibold italic">
                            <p><i class="fas fa-heart text-rose-500 mr-1"></i> Terima kasih atas kerja keras Anda hari ini. Hati-hati di jalan pulang!</p>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" id="btn-tutup-modal-keluar" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2.5 px-5 rounded-xl text-xs transition duration-200">Batal</button>
                    <button type="submit" id="submit-button-keluar" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-5 rounded-xl text-xs shadow-md shadow-rose-500/10 transition duration-200 disabled:bg-slate-300">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    
    {{-- MODAL UNTUK ABSEN LEMBUR (LAYOUT: KIRI KAMERA SQUARE, KANAN TEXTAREA) --}}
    @if ( ($absensiHariIni && $absensiHariIni->jam_keluar && $absensiHariIni->status == 'hadir') || $isHoliday )
    <div id="modal-absen-lembur" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl transform transition-all duration-300 scale-95 opacity-0 overflow-hidden">
            <form action="{{ route('absen.lembur.store') }}" method="POST" enctype="multipart/form-data" id="form-absen-lembur">
                @csrf
                <input type="hidden" name="latitude_masuk" id="latitude-lembur">
                <input type="hidden" name="longitude_masuk" id="longitude-lembur">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-history text-lg"></i></div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-800">Form Absen Lembur</h3>
                            <p class="text-xs text-slate-400">Ambil foto selfie dan isi keterangan untuk memulai lembur.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        {{-- Kamera SQUARE --}}
                        <div>
                            <label class="modern-label">Foto Selfie <span class="text-red-500">*</span></label>
                            <div id="camera-container-lembur" class="relative aspect-square rounded-2xl overflow-hidden bg-slate-900 shadow-md">
                                <video id="video-lembur" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                <canvas id="canvas-lembur" class="hidden"></canvas>
                                <div id="snap-ui-lembur" class="absolute inset-0 flex items-end justify-center p-4 bg-gradient-to-t from-black/60 to-transparent">
                                    <button type="button" id="snap-lembur" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400 transition-transform hover:scale-105" disabled>
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div id="preview-ui-lembur" class="absolute inset-0 hidden bg-black">
                                    <img id="preview-image-lembur" src="" class="w-full h-full object-contain" alt="Pratinjau Foto"/>
                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-gradient-to-t from-black/60 to-transparent">
                                        <button type="button" id="retake-btn-lembur" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center shadow-lg"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                        <button type="button" id="use-photo-btn-lembur" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center shadow-lg"><i class="fas fa-check mr-1.5"></i>Pakai</button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload-label-lembur" class="hidden">
                                <input name="lampiran_masuk" id="lampiran-lembur" type="file" class="hidden" accept="image/*" />
                            </div>
                        </div>

                        {{-- Textarea Keterangan --}}
                        <div class="flex flex-col">
                            <label for="keterangan-lembur" class="modern-label">Keterangan Lembur <span class="text-red-500">*</span></label>
                            <textarea id="keterangan-lembur" name="keterangan" class="modern-input h-full resize-none min-h-[120px]" placeholder="Menyelesaikan pekerjaan apa hari ini..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" id="btn-tutup-modal-lembur" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2.5 px-5 rounded-xl text-xs transition duration-200">Batal</button>
                    <button type="submit" id="submit-button-lembur" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-5 rounded-xl text-xs shadow-md shadow-purple-500/10 transition duration-200 disabled:bg-slate-300">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL UNTUK ABSEN KELUAR LEMBUR (LAYOUT: KIRI KAMERA SQUARE, KANAN INFO) --}}
    @if ($lemburHariIni && is_null($lemburHariIni->jam_keluar_lembur))
    <div id="modal-keluar-lembur" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl transform transition-all duration-300 scale-95 opacity-0 overflow-hidden">
            <form action="{{ route('absen.lembur.keluar', $lemburHariIni->id) }}" method="POST" enctype="multipart/form-data" id="form-keluar-lembur">
                @csrf
                @method('PATCH')
                <input type="hidden" name="latitude_keluar" id="latitude-keluar-lembur">
                <input type="hidden" name="longitude_keluar" id="longitude-keluar-lembur">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-sign-out-alt text-lg"></i></div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-800">Form Absen Keluar Lembur</h3>
                            <p class="text-xs text-slate-400">Ambil foto selfie untuk konfirmasi selesai lembur.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                         {{-- Kamera SQUARE --}}
                        <div>
                            <label class="modern-label">Foto Selfie <span class="text-red-500">*</span></label>
                            <div id="camera-container-keluar-lembur" class="relative aspect-square rounded-2xl overflow-hidden bg-slate-900 shadow-md">
                                <video id="video-keluar-lembur" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                <canvas id="canvas-keluar-lembur" class="hidden"></canvas>
                                <div id="snap-ui-keluar-lembur" class="absolute inset-0 flex items-end justify-center p-4 bg-gradient-to-t from-black/60 to-transparent">
                                    <button type="button" id="snap-keluar-lembur" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400 transition-transform hover:scale-105" disabled>
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div id="preview-ui-keluar-lembur" class="absolute inset-0 hidden bg-black">
                                    <img id="preview-image-keluar-lembur" src="" class="w-full h-full object-contain" alt="Pratinjau Foto"/>
                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-gradient-to-t from-black/60 to-transparent">
                                        <button type="button" id="retake-btn-keluar-lembur" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center shadow-lg"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                        <button type="button" id="use-photo-btn-keluar-lembur" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center shadow-lg"><i class="fas fa-check mr-1.5"></i>Pakai</button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload-label-keluar-lembur" class="hidden">
                                <input name="lampiran_keluar" id="lampiran-keluar-lembur" type="file" class="hidden" accept="image/*" />
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="flex flex-col justify-center bg-slate-50 border border-slate-100 rounded-2xl p-4 text-xs text-slate-500 leading-relaxed font-semibold italic">
                             <p><i class="fas fa-heart text-rose-500 mr-1"></i> Terima kasih sudah lembur hari ini. Selamat beristirahat!</p>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" id="btn-tutup-modal-keluar-lembur" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2.5 px-5 rounded-xl text-xs transition duration-200">Batal</button>
                    <button type="submit" id="submit-button-keluar-lembur" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-5 rounded-xl text-xs shadow-md shadow-rose-500/10 transition duration-200 disabled:bg-slate-300">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL REKAP BULAN INI --}}
    <div id="modal-rekap" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeRekapModal()"></div>
        <!-- Modal Content -->
        <div id="modal-rekap-content" class="relative bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-300">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-white/50 backdrop-blur-md">
                <h3 class="font-extrabold text-slate-800 text-lg flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-blue-500"></i> Rekap Bulan Ini
                </h3>
                <button type="button" onclick="closeRekapModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-red-100 hover:text-red-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 bg-slate-50/50">
                <p class="text-center text-slate-400 text-xs mb-5 uppercase font-bold tracking-wider">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-rekap-hadir rounded-2xl p-4 flex flex-col justify-between min-h-[80px] shadow-lg shadow-emerald-500/10 border-0 transition-transform duration-300 hover:scale-[1.02]">
                        <span class="text-[10px] uppercase font-bold tracking-wider opacity-85">Hadir</span>
                        <div class="flex items-baseline gap-1 mt-1"><span class="text-3xl font-black">{{ $rekapAbsen['hadir'] }}</span> <span class="text-[10px] font-bold opacity-80">Hari</span></div>
                    </div>
                    <div class="bg-rekap-sakit rounded-2xl p-4 flex flex-col justify-between min-h-[80px] shadow-lg shadow-rose-500/10 border-0 transition-transform duration-300 hover:scale-[1.02]">
                        <span class="text-[10px] uppercase font-bold tracking-wider opacity-85">Sakit</span>
                        <div class="flex items-baseline gap-1 mt-1"><span class="text-3xl font-black">{{ $rekapAbsen['sakit'] }}</span> <span class="text-[10px] font-bold opacity-80">Hari</span></div>
                    </div>
                    <div class="bg-rekap-izin rounded-2xl p-4 flex flex-col justify-between min-h-[80px] shadow-lg shadow-amber-500/10 border-0 transition-transform duration-300 hover:scale-[1.02]">
                        <span class="text-[10px] uppercase font-bold tracking-wider opacity-85">Izin</span>
                        <div class="flex items-baseline gap-1 mt-1"><span class="text-3xl font-black">{{ $rekapAbsen['izin'] }}</span> <span class="text-[10px] font-bold opacity-80">Hari</span></div>
                    </div>
                    <div class="bg-rekap-cuti rounded-2xl p-4 flex flex-col justify-between min-h-[80px] shadow-lg shadow-blue-500/10 border-0 transition-transform duration-300 hover:scale-[1.02]">
                        <span class="text-[10px] uppercase font-bold tracking-wider opacity-85">Cuti</span>
                        <div class="flex items-baseline gap-1 mt-1"><span class="text-3xl font-black">{{ $rekapAbsen['cuti'] }}</span> <span class="text-[10px] font-bold opacity-80">Hari</span></div>
                    </div>
                    <div class="bg-rekap-alpa rounded-2xl p-4 flex flex-col justify-between min-h-[80px] shadow-lg shadow-slate-500/10 border-0 transition-transform duration-300 hover:scale-[1.02]">
                        <span class="text-[10px] uppercase font-bold tracking-wider opacity-85">Alpa</span>
                        <div class="flex items-baseline gap-1 mt-1"><span class="text-3xl font-black">{{ $rekapAbsen['tidak hadir'] }}</span> <span class="text-[10px] font-bold opacity-80">Hari</span></div>
                    </div>
                    <div class="bg-rekap-lembur rounded-2xl p-4 flex flex-col justify-between min-h-[80px] shadow-lg shadow-purple-500/10 border-0 transition-transform duration-300 hover:scale-[1.02]">
                        <span class="text-[10px] uppercase font-bold tracking-wider opacity-85">Lembur</span>
                        <div class="flex items-baseline gap-1 mt-1"><span class="text-3xl font-black">{{ $rekapAbsen['lembur'] }}</span> <span class="text-[10px] font-bold opacity-80">Hari</span></div>
                    </div>
                </div>
                <div class="bg-rekap-terlambat rounded-2xl p-3.5 flex items-center justify-between mt-5 shadow-lg shadow-orange-500/10 border-0 transition-transform duration-300 hover:scale-[1.01]">
                    <span class="text-[11px] uppercase font-extrabold tracking-wider flex items-center gap-2 opacity-90 mb-1">
                        <i class="fas fa-exclamation-circle text-sm"></i> Terlambat
                    </span>
                    <span class="text-sm font-black">{{ $rekapAbsen['terlambat'] }}</span>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 text-center">
                <button type="button" onclick="closeRekapModal()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-5 rounded-xl text-sm shadow-md shadow-blue-500/20 transition duration-200">
                    Tutup Rekap
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    window.openRekapModal = function() {
        const modalContent = document.getElementById('modal-rekap-content');
        const modal = document.getElementById('modal-rekap');
        if(modalContent && modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
            }, 10);
        }
    };

    window.closeRekapModal = function() {
        const modalContent = document.getElementById('modal-rekap-content');
        const modal = document.getElementById('modal-rekap');
        if(modalContent && modal) {
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden'); 
                modal.classList.remove('flex');
            }, 300);
        }
    };
    
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
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            facingMode: "user",
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        } 
                    });
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
                    successMsg.className = 'success-message mt-2 text-center text-xs text-white font-bold p-2 bg-emerald-500 rounded-lg shadow-md shadow-emerald-500/20';
                    successMsg.innerHTML = `<i class="fas fa-check-circle"></i> Foto berhasil tersimpan.`;
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
                hadir: 'bg-emerald-500 text-white shadow-md shadow-emerald-500/10',
                izin: 'bg-amber-500 text-white shadow-md shadow-amber-500/10',
                sakit: 'bg-rose-500 text-white shadow-md shadow-rose-500/10'
            };

            const setActiveButton = (status) => {
                document.querySelectorAll('.status-btn').forEach(btn => {
                    btn.classList.remove(...Object.values(selectedStyles).join(' ').split(' '));
                    btn.classList.add('bg-transparent', 'text-slate-500', 'hover:bg-slate-200/50', 'hover:text-slate-700');
                });
                const activeButton = document.querySelector(`.status-btn[data-status="${status}"]`);
                if (activeButton) {
                    activeButton.classList.remove('bg-transparent', 'text-slate-500', 'hover:bg-slate-200/50', 'hover:text-slate-700');
                    activeButton.classList.add(...selectedStyles[status].split(' '));
                }
                const statusLabel = document.getElementById('status-label');
                if (statusLabel) {
                    statusLabel.textContent = status;
                    statusLabel.className = `text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md ` +
                        (status === 'hadir' ? 'bg-emerald-50 text-emerald-600' : (status === 'izin' ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600'));
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
                    successMsg.className = 'success-message mt-2 text-center text-sm text-white font-bold p-2.5 bg-emerald-500 rounded-lg shadow-md shadow-emerald-500/20';
                    successMsg.innerHTML = `<i class="fas fa-check-circle"></i> Foto berhasil tersimpan.`;
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