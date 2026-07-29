<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    @push('styles')
    <style>
        /* Sembunyikan default header di layout-users khusus untuk mobile absen */
        header { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; height: 100dvh; display: flex; flex-direction: column; overflow: hidden; background-color: #0f172a; }

        .bottom-sheet { box-shadow: 0 -10px 40px rgba(0,0,0,0.3); }
        .btn-snap { box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5); }

        /* Hide scrollbar for clean UI */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* ===== Token yang sama persis dengan absen_desktop agar selaras ===== */
        .btn-gradient {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white; border: none; padding: 16px 24px; border-radius: 16px;
            font-weight: 800; font-size: 1rem;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; transition: all 0.2s;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4); cursor: pointer;
        }
        .btn-gradient:active { transform: scale(0.97); }
        .btn-gradient:disabled { background: #cbd5e1; box-shadow: none; cursor: not-allowed; }

        .bg-rekap-hadir { background: linear-gradient(135deg, #10b981, #0d9488) !important; color: white !important; }
        .bg-rekap-sakit { background: linear-gradient(135deg, #f43f5e, #dc2626) !important; color: white !important; }
        .bg-rekap-izin { background: linear-gradient(135deg, #f59e0b, #ea580c) !important; color: white !important; }
        .bg-rekap-cuti { background: linear-gradient(135deg, #3b82f6, #4f46e5) !important; color: white !important; }
        .bg-rekap-alpa { background: linear-gradient(135deg, #64748b, #475569) !important; color: white !important; }
        .bg-rekap-lembur { background: linear-gradient(135deg, #a855f7, #7c3aed) !important; color: white !important; }
        .bg-rekap-terlambat { background: linear-gradient(135deg, #f97316, #d97706) !important; color: white !important; }

        .success-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(16, 185, 129, 0.15); color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.4);
            font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 9999px;
            backdrop-filter: blur(6px);
        }
    </style>
    @endpush

    <div class="flex flex-col h-[100dvh] w-full relative bg-slate-900 overflow-hidden">

        {{-- ========================================== --}}
        {{-- BAGIAN ATAS: KAMERA FULL WIDTH (60% Height)--}}
        {{-- ========================================== --}}
        <div class="relative flex-grow flex flex-col justify-center items-center bg-black overflow-hidden z-10" style="height: 60dvh;">

            {{-- HEADER OVERLAY --}}
            <div class="absolute top-0 left-0 right-0 p-5 pt-6 flex justify-between items-start z-30 bg-gradient-to-b from-black/80 via-black/40 to-transparent">
                <a href="{{ route('dashboard') }}" class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/30 transition shadow-lg">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="text-right text-white drop-shadow-md">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-slate-300 mb-0.5" id="jam-realtime-mobile">--:--:--</p>
                    <p class="text-base font-black leading-none">{{ \Carbon\Carbon::now()->translatedFormat('l, d M Y') }}</p>
                </div>
            </div>

            {{-- NOTIFIKASI MELAYANG JIKA ADA --}}
            

            @if ($absensiHariIni)
                 {{-- KONDISI 1: SUDAH ABSEN --}}
                 <div class="absolute inset-0 bg-gradient-to-br from-blue-900/90 to-indigo-900/90 z-20 flex flex-col items-center justify-center p-6 backdrop-blur-md">
                     <div class="w-20 h-20 rounded-full bg-white/10 flex items-center justify-center mx-auto mb-4 backdrop-blur-md border border-white/20 shadow-2xl">
                         <i class="fas fa-check-double text-4xl text-blue-400"></i>
                     </div>
                     <h2 class="text-2xl font-black text-white drop-shadow-lg">Absen Tercatat</h2>
                     <p class="text-xs text-blue-200 font-bold mb-6 mt-1 tracking-wider">STATUS: <span class="text-emerald-400 uppercase">{{ $absensiHariIni->status }}</span></p>

                     <div class="flex justify-between items-center bg-white/10 backdrop-blur-md p-4 rounded-3xl border border-white/20 w-full max-w-xs shadow-xl">
                         <div class="text-left">
                             <p class="text-[10px] font-extrabold text-blue-200 uppercase mb-1">Masuk</p>
                             <p class="text-xl font-black text-white">{{ \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') }}</p>
                             @if($absensiHariIni->latitude && $absensiHariIni->longitude)
                                 <a href="https://www.google.com/maps/search/?api=1&query={{ $absensiHariIni->latitude }},{{ $absensiHariIni->longitude }}" target="_blank" class="text-[9px] text-blue-300 font-bold inline-flex items-center gap-1 mt-1.5">
                                     <i class="fas fa-map-marker-alt"></i> Lokasi
                                 </a>
                             @endif
                         </div>
                         
                         <div class="text-right">
                             <p class="text-[10px] font-extrabold text-blue-200 uppercase mb-1">Keluar</p>
                             <p class="text-xl font-black text-white">{{ $absensiHariIni->jam_keluar ? \Carbon\Carbon::parse($absensiHariIni->jam_keluar)->format('H:i') : '--:--' }}</p>
                             @if($absensiHariIni->latitude_keluar && $absensiHariIni->longitude_keluar)
                                 <a href="https://www.google.com/maps/search/?api=1&query={{ $absensiHariIni->latitude_keluar }},{{ $absensiHariIni->longitude_keluar }}" target="_blank" class="text-[9px] text-blue-300 font-bold inline-flex items-center gap-1 mt-1.5">
                                     <i class="fas fa-map-marker-alt"></i> Lokasi
                                 </a>
                             @endif
                         </div>
                     </div>

                     @if($absensiHariIni->keterangan)
                         <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-3.5 mt-4 w-full max-w-xs text-[11px] text-blue-100 leading-relaxed">
                             <span class="font-bold text-white block mb-1">Keterangan:</span>
                             {{ $absensiHariIni->keterangan }}
                         </div>
                     @endif
                 </div>
            @else
                @if (isset($unfinishedAbsensi) && $unfinishedAbsensi)
                     {{-- KONDISI 2: ABSEN KELUAR TERTUNDA --}}
                     <div class="absolute inset-0 bg-gradient-to-br from-amber-900/90 to-orange-900/90 z-20 flex flex-col items-center justify-center p-6 backdrop-blur-md">
                         <div class="w-20 h-20 rounded-full bg-white/10 flex items-center justify-center mx-auto mb-4 backdrop-blur-md border border-white/20 shadow-2xl">
                             <i class="fas fa-exclamation-triangle text-4xl text-amber-400"></i>
                         </div>
                         <h2 class="text-2xl font-black text-white drop-shadow-lg text-center leading-tight">Absen Keluar<br>Tertunda</h2>
                         <p class="text-xs text-amber-200 font-bold mb-6 mt-2 text-center">Silakan selesaikan absen keluar Anda untuk tanggal {{ \Carbon\Carbon::parse($unfinishedAbsensi->tanggal)->translatedFormat('d M Y') }}.</p>
                     </div>
                @else
                    {{-- KONDISI 3: KAMERA AKTIF UNTUK ABSEN MASUK --}}
                    <div id="camera-container" class="absolute inset-0 w-full h-full z-10 bg-slate-900">
                        <video id="video" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay playsinline muted></video>
                        <canvas id="canvas" class="hidden"></canvas>

                        <div id="preview-ui" class="absolute inset-0 hidden bg-black z-20">
                            <img id="preview-image" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                            <div class="absolute top-20 right-4 z-30 flex flex-col items-end gap-2">
                                <span class="success-chip"><i class="fas fa-check-circle"></i> Foto Tersimpan</span>
                                <button type="button" id="retake-btn" class="bg-white/20 backdrop-blur-md text-white border border-white/50 font-bold py-2.5 px-4 rounded-full shadow-lg flex items-center gap-2 text-xs">
                                    <i class="fas fa-sync-alt"></i> Ulang
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- TOMBOL SNAP MELAYANG --}}
            @if (!$absensiHariIni && (!isset($unfinishedAbsensi) || !$unfinishedAbsensi))
            <div id="snap-ui" class="absolute bottom-6 left-0 right-0 flex justify-center z-30 pointer-events-none">
                <button type="button" id="snap" class="btn-snap pointer-events-auto bg-blue-600 text-white rounded-full h-[72px] w-[72px] flex items-center justify-center text-3xl border-[6px] border-white shadow-2xl transition-transform hover:scale-105 active:scale-95 disabled:opacity-50" disabled>
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            @endif
        </div>

        {{-- ========================================== --}}
        {{-- BAGIAN BAWAH: ACTION SHEET (40% Height)    --}}
        {{-- ========================================== --}}
        <div class="bottom-sheet bg-white rounded-t-[32px] z-40 flex flex-col relative flex-grow" style="height: 35dvh; min-height: 260px;">
            {{-- Handle Bar --}}
            <div class="w-full flex justify-center pt-3 pb-2 absolute top-0 left-0 right-0 z-50 bg-white rounded-t-3xl">
                
            </div>

            <div class="flex-grow overflow-y-auto hide-scrollbar pt-8 pb-6 px-5 flex flex-col">
                @if ($absensiHariIni)
                    {{-- TOMBOL AKSI JIKA SUDAH ABSEN --}}
                    <div class="flex flex-col gap-3 h-full justify-center mt-4">
                        @if (is_null($absensiHariIni->jam_keluar))
                            <button type="button" onclick="openCam('-keluar')" class="w-full bg-rose-600 text-white font-black py-4 rounded-2xl text-base shadow-xl shadow-rose-500/30 active:scale-95 transition-transform flex items-center justify-center gap-2">
                                <i class="fas fa-sign-out-alt"></i> Absen Keluar Sekarang
                            </button>
                        @endif

                        @if ($absensiHariIni->jam_keluar && $absensiHariIni->status == 'hadir')
                            @if (is_null($lemburHariIni))
                                <button type="button" onclick="openCam('-lembur')" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-black py-4 rounded-2xl text-base shadow-xl shadow-purple-500/30 active:scale-95 transition-transform flex items-center justify-center gap-2">
                                    <i class="fas fa-moon"></i> Absen Lembur
                                </button>
                            @elseif (is_null($lemburHariIni->jam_keluar_lembur))
                                <button type="button" onclick="openCam('-keluar-lembur')" class="w-full bg-rose-600 text-white font-black py-4 rounded-2xl text-base shadow-xl shadow-rose-500/30 active:scale-95 transition-transform flex items-center justify-center gap-2">
                                    <i class="fas fa-sign-out-alt"></i> Keluar Lembur
                                </button>
                            @else
                                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-center">
                                    <p class="text-xs font-bold"><i class="fas fa-check-circle mr-1"></i> Absensi Lembur Hari Ini Selesai.</p>
                                    <p class="text-[10px] text-emerald-700/80 mt-1 font-semibold">{{ \Carbon\Carbon::parse($lemburHariIni->jam_masuk_lembur)->format('H:i') }} - {{ \Carbon\Carbon::parse($lemburHariIni->jam_keluar_lembur)->format('H:i') }}</p>
                                </div>
                            @endif
                        @endif

                        <button type="button" onclick="openRekapModal()" class="w-full bg-slate-100 text-slate-700 font-bold py-4 rounded-2xl text-sm border border-slate-200 mt-2 flex items-center justify-center gap-2">
                            <i class="fas fa-users text-blue-500"></i> Rekap & Absensi Tim
                        </button>
                    </div>

                @elseif (isset($unfinishedAbsensi) && $unfinishedAbsensi)
                    {{-- TOMBOL AKSI JIKA ADA ABSEN KELUAR TERTUNDA --}}
                    <div class="flex flex-col gap-3 h-full justify-center mt-4">
                        <button type="button" onclick="openCam('-keluar-unfinished')" class="w-full bg-amber-500 text-white font-black py-4 rounded-2xl text-base shadow-xl shadow-amber-500/30 active:scale-95 transition-transform flex items-center justify-center gap-2">
                            <i class="fas fa-camera"></i> Selesaikan Absen Keluar
                        </button>
                    </div>

                @else
                    {{-- FORM ABSEN MASUK --}}
                    <form action="{{ route('absen.store') }}" method="POST" enctype="multipart/form-data" id="form-absen" class="flex flex-col flex-1">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="status" id="status" value="hadir">

                        @if($isHoliday)
                            <div class="bg-red-50 p-3 rounded-xl mb-4 border border-red-100 flex gap-3 items-start">
                                <i class="fas fa-info-circle text-red-500 mt-0.5"></i>
                                <p class="text-xs text-red-800 font-semibold leading-tight">Hari ini libur. Absensi khusus piket/lembur.</p>
                            </div>
                        @elseif($isSaturdayOpen)
                            <div class="bg-blue-50 p-3 rounded-xl mb-4 border border-blue-100 flex gap-3 items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                                <p class="text-xs text-blue-800 font-semibold leading-tight">Kehadiran opsional (Sabtu).</p>
                            </div>
                        @endif

                        <h3 class="text-xs font-extrabold text-slate-500 uppercase tracking-widest mb-3 text-center">Status Kehadiran</h3>
                        <div class="flex gap-2 mb-5" id="status-buttons">
                            <button type="button" data-status="hadir" class="status-btn flex-1 flex flex-col items-center justify-center gap-1.5 py-3 rounded-2xl transition-all border border-transparent shadow-md bg-emerald-500 text-white shadow-emerald-500/20">
                                <i class="fas fa-check-circle text-xl"></i>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Hadir</span>
                            </button>
                            <button type="button" data-status="izin" class="status-btn flex-1 flex flex-col items-center justify-center gap-1.5 py-3 rounded-2xl transition-all border border-slate-200 bg-slate-50 text-slate-400">
                                <i class="fas fa-envelope-open-text text-xl"></i>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Izin</span>
                            </button>
                            <button type="button" data-status="sakit" class="status-btn flex-1 flex flex-col items-center justify-center gap-1.5 py-3 rounded-2xl transition-all border border-slate-200 bg-slate-50 text-slate-400">
                                <i class="fas fa-clinic-medical text-xl"></i>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Sakit</span>
                            </button>
                        </div>

                        <div id="keterangan-container" class="hidden mb-5 transition-all">
                            <div class="bg-blue-50/50 border border-blue-100 rounded-[24px] p-4 space-y-3 shadow-sm">
                                <div class="flex items-center gap-2 mb-1 px-1">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                        <i class="fas fa-edit text-xs"></i>
                                    </div>
                                    <h4 class="text-[11px] font-extrabold text-slate-700 uppercase tracking-widest">Detail Keterangan</h4>
                                </div>
                                <textarea name="keterangan" id="keterangan" rows="2" class="w-full bg-white border border-blue-200/60 rounded-[16px] px-4 py-3 text-[13px] font-medium text-slate-700 focus:ring-blue-500 focus:border-blue-500 shadow-sm outline-none transition-all placeholder:text-slate-400" placeholder="Tulis alasan secara singkat...">{{ old('keterangan') }}</textarea>

                                <label for="lampiran" id="upload-label" class="flex flex-col items-center justify-center w-full py-4 border-2 border-dashed border-blue-300/70 rounded-[16px] bg-white hover:bg-blue-50 transition-colors cursor-pointer group">
                                    <i class="fas fa-cloud-upload-alt text-xl text-blue-400 group-hover:scale-110 group-hover:text-blue-500 transition-all mb-1.5"></i>
                                    <span id="upload-text" class="text-[10px] font-bold text-slate-500 group-hover:text-blue-600 transition-colors uppercase tracking-wide">Unggah Bukti (Opsional)</span>
                                    <input name="lampiran" id="lampiran" type="file" class="hidden" accept="image/*,.pdf" />
                                </label>
                            </div>
                        </div>

                        {{-- Tombol submit menempel langsung tanpa space tambahan --}}
                        <div class="flex flex-col gap-1 pb-2">
                            <button type="submit" id="submit-button" class="btn-gradient text-[15px] font-bold w-full py-[18px] rounded-[18px] shadow-[0_8px_20px_rgba(59,130,246,0.25)] transition-all active:scale-[0.98] disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-2" disabled>
                                <i class="fas fa-fingerprint text-lg"></i> Kirim Absensi
                            </button>
                            <button type="button" onclick="openRekapModal()" class="w-full mt-3 text-center text-slate-400 hover:text-blue-600 text-[10px] font-extrabold uppercase tracking-widest py-3 transition-colors flex items-center justify-center gap-2 rounded-xl active:bg-slate-50">
                                <i class="fas fa-users text-xs"></i> Rekap & Tim <i class="fas fa-chevron-up ml-0.5 text-[8px] opacity-70"></i>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- MODAL PULL UP: REKAP & TIM (Mobile Bottom Sheet Style) --}}
    {{-- ========================================================= --}}
    <div id="modal-rekap-tim" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-sm hidden flex-col justify-end transition-all duration-300">
        
        <div class="relative bg-slate-50 w-full rounded-t-3xl shadow-2xl flex flex-col transform translate-y-full transition-transform duration-300 h-[85dvh]" id="modal-rekap-tim-content">
            <div class="bg-white px-5 py-4 rounded-t-3xl border-b border-slate-100 flex justify-between items-center sticky top-0 z-10 shadow-sm">
                <h2 class="text-base font-black text-slate-800">Rekap & Tim</h2>
                <button type="button" onclick="closeRekapModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center hover:bg-slate-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-grow overflow-y-auto p-5 pb-10 space-y-5 hide-scrollbar">
                {{-- Card Rekap (data & warna diselaraskan dengan absen_desktop) --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
                    <h3 class="font-extrabold text-slate-800 text-xs mb-4 uppercase tracking-wider text-center">Rekap Bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-rekap-hadir rounded-2xl p-3.5 flex flex-col justify-between min-h-[72px] shadow-lg shadow-emerald-500/10">
                            <span class="text-[9px] uppercase font-bold tracking-wider opacity-85">Hadir</span>
                            <div class="flex items-baseline gap-1 mt-1"><span class="text-2xl font-black">{{ $rekapAbsen['hadir'] }}</span> <span class="text-[9px] font-bold opacity-80">Hari</span></div>
                        </div>
                        <div class="bg-rekap-sakit rounded-2xl p-3.5 flex flex-col justify-between min-h-[72px] shadow-lg shadow-rose-500/10">
                            <span class="text-[9px] uppercase font-bold tracking-wider opacity-85">Sakit</span>
                            <div class="flex items-baseline gap-1 mt-1"><span class="text-2xl font-black">{{ $rekapAbsen['sakit'] }}</span> <span class="text-[9px] font-bold opacity-80">Hari</span></div>
                        </div>
                        <div class="bg-rekap-izin rounded-2xl p-3.5 flex flex-col justify-between min-h-[72px] shadow-lg shadow-amber-500/10">
                            <span class="text-[9px] uppercase font-bold tracking-wider opacity-85">Izin</span>
                            <div class="flex items-baseline gap-1 mt-1"><span class="text-2xl font-black">{{ $rekapAbsen['izin'] }}</span> <span class="text-[9px] font-bold opacity-80">Hari</span></div>
                        </div>
                        <div class="bg-rekap-cuti rounded-2xl p-3.5 flex flex-col justify-between min-h-[72px] shadow-lg shadow-blue-500/10">
                            <span class="text-[9px] uppercase font-bold tracking-wider opacity-85">Cuti</span>
                            <div class="flex items-baseline gap-1 mt-1"><span class="text-2xl font-black">{{ $rekapAbsen['cuti'] }}</span> <span class="text-[9px] font-bold opacity-80">Hari</span></div>
                        </div>
                        <div class="bg-rekap-alpa rounded-2xl p-3.5 flex flex-col justify-between min-h-[72px] shadow-lg shadow-slate-500/10">
                            <span class="text-[9px] uppercase font-bold tracking-wider opacity-85">Alpa</span>
                            <div class="flex items-baseline gap-1 mt-1"><span class="text-2xl font-black">{{ $rekapAbsen['tidak hadir'] }}</span> <span class="text-[9px] font-bold opacity-80">Hari</span></div>
                        </div>
                        <div class="bg-rekap-lembur rounded-2xl p-3.5 flex flex-col justify-between min-h-[72px] shadow-lg shadow-purple-500/10">
                            <span class="text-[9px] uppercase font-bold tracking-wider opacity-85">Lembur</span>
                            <div class="flex items-baseline gap-1 mt-1"><span class="text-2xl font-black">{{ $rekapAbsen['lembur'] }}</span> <span class="text-[9px] font-bold opacity-80">Hari</span></div>
                        </div>
                    </div>
                    <div class="bg-rekap-terlambat rounded-2xl p-3 flex items-center justify-between mt-3 shadow-lg shadow-orange-500/10">
                        <span class="text-[10px] uppercase font-extrabold tracking-wider flex items-center gap-2 opacity-90">
                            <i class="fas fa-exclamation-circle text-sm"></i> Terlambat
                        </span>
                        <span class="text-xs font-black">{{ $rekapAbsen['terlambat'] }}</span>
                    </div>
                </div>

                {{-- Card Tim --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
                    <h3 class="font-extrabold text-slate-800 text-xs mb-4 flex items-center gap-2 uppercase tracking-wider"><i class="fas fa-users text-blue-500"></i> Rekan Tim</h3>
                    @if(isset($daftarRekan) && count($daftarRekan) > 0)
                        <div class="space-y-4">
                            @foreach($daftarRekan as $rekan)
                            @php
                                $badgeClass = match($rekan->status) {
                                    'hadir'  => 'bg-emerald-100 text-emerald-700',
                                    'sakit'  => 'bg-rose-100 text-rose-700',
                                    'izin'   => 'bg-amber-100 text-amber-700',
                                    default  => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $rekan->user->profile_picture ? asset('storage/' . $rekan->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($rekan->user->name ?? 'U').'&background=random&color=fff&size=64' }}"
                                         alt="{{ $rekan->user->name ?? '' }}" class="w-10 h-10 rounded-full object-cover border-2 border-slate-100">
                                    <div>
                                        <p class="text-xs font-bold text-slate-700">{{ $rekan->user->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-semibold">{{ $rekan->jam_masuk ? \Carbon\Carbon::parse($rekan->jam_masuk)->format('H:i') . ' WIB' : '--:--' }}</p>
                                    </div>
                                </div>
                                <span class="text-[9px] font-extrabold px-3 py-1 rounded-full {{ $badgeClass }} uppercase tracking-wider">
                                    {{ str_replace('_', ' ', $rekan->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-center text-slate-400 italic">Belum ada rekan yang absen hari ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- MODAL ABSEN KELUAR --}}
    {{-- ========================================================= --}}
    @if ($absensiHariIni)
    <div id="modal-absen-keluar" class="fixed inset-0 z-[100] bg-slate-900 hidden flex-col transition-all duration-300">
        <div class="relative flex-grow flex flex-col justify-center items-center bg-black overflow-hidden z-10" style="height: 60dvh;">
            <div class="absolute top-0 left-0 right-0 p-5 pt-6 flex justify-between items-start z-30 bg-gradient-to-b from-black/80 to-transparent">
                <button type="button" onclick="closeCam('-keluar')" class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/30 transition shadow-lg">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="text-right text-white">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-slate-300 mb-0.5">Absen Keluar</p>
                    <p class="text-base font-black leading-none">{{ \Carbon\Carbon::now()->translatedFormat('l, d M Y') }}</p>
                </div>
            </div>

            <div id="camera-container-keluar" class="absolute inset-0 w-full h-full z-10 bg-slate-900">
                <video id="video-keluar" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay playsinline muted></video>
                <canvas id="canvas-keluar" class="hidden"></canvas>

                <div id="preview-ui-keluar" class="absolute inset-0 hidden bg-black z-20">
                    <img id="preview-image-keluar" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                    <div class="absolute top-20 right-4 z-30 flex flex-col items-end gap-2">
                        <span class="success-chip"><i class="fas fa-check-circle"></i> Foto Tersimpan</span>
                        <button type="button" id="retake-btn-keluar" class="bg-white/20 backdrop-blur-md text-white border border-white/50 font-bold py-2.5 px-4 rounded-full shadow-lg flex items-center gap-2 text-xs">
                            <i class="fas fa-sync-alt"></i> Ulang
                        </button>
                    </div>
                </div>
            </div>

            <div id="snap-ui-keluar" class="absolute bottom-6 left-0 right-0 flex justify-center z-30 pointer-events-none">
                <button type="button" id="snap-keluar" class="btn-snap pointer-events-auto bg-rose-600 text-white rounded-full h-[72px] w-[72px] flex items-center justify-center text-3xl border-[6px] border-white shadow-2xl transition-transform hover:scale-105 active:scale-95 disabled:opacity-50" disabled>
                    <i class="fas fa-camera"></i>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-t-3xl z-40 flex flex-col relative flex-grow p-5" style="height: 40dvh;">
            <div class="w-full flex justify-center pt-1 pb-4">
                
            </div>
            <form action="{{ route('absen.keluar', $absensiHariIni->id) }}" method="POST" enctype="multipart/form-data" id="form-keluar" class="flex flex-col h-full">
                @csrf
                @method('PATCH')
                <input type="hidden" name="latitude_keluar" id="latitude-keluar">
                <input type="hidden" name="longitude_keluar" id="longitude-keluar">
                <input type="file" name="lampiran_keluar" id="lampiran-keluar" class="hidden" accept="image/*">

                <div class="flex-grow flex flex-col justify-center items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mb-4">
                        <i class="fas fa-sign-out-alt text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-800">Siap Pulang?</h3>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Pastikan wajah terlihat jelas dan lokasi aktif.</p>
                </div>

                <div class="mt-auto pt-4 pb-2">
                    <button type="submit" id="submit-button-keluar" class="w-full bg-rose-600 text-white font-black py-4 rounded-2xl text-base shadow-xl shadow-rose-600/30 transition-transform active:scale-95 flex items-center justify-center gap-2 disabled:bg-slate-300 disabled:shadow-none" disabled>
                        <i class="fas fa-paper-plane"></i> Kirim Absen Keluar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ========================================================= --}}
    {{-- MODAL ABSEN KELUAR TERTUNDA --}}
    {{-- ========================================================= --}}
    @if (isset($unfinishedAbsensi) && $unfinishedAbsensi)
    <div id="modal-absen-keluar-unfinished" class="fixed inset-0 z-[100] bg-slate-900 hidden flex-col transition-all duration-300">
        <div class="relative flex-grow flex flex-col justify-center items-center bg-black overflow-hidden z-10" style="height: 60dvh;">
            <div class="absolute top-0 left-0 right-0 p-5 pt-6 flex justify-between items-start z-30 bg-gradient-to-b from-black/80 to-transparent">
                <button type="button" onclick="closeCam('-keluar-unfinished')" class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/30 transition shadow-lg">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="text-right text-white">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-amber-300 mb-0.5">Absen Tertunda</p>
                    <p class="text-base font-black leading-none">{{ \Carbon\Carbon::parse($unfinishedAbsensi->tanggal)->translatedFormat('d M Y') }}</p>
                </div>
            </div>

            <div id="camera-container-keluar-unfinished" class="absolute inset-0 w-full h-full z-10 bg-slate-900">
                <video id="video-keluar-unfinished" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay playsinline muted></video>
                <canvas id="canvas-keluar-unfinished" class="hidden"></canvas>

                <div id="preview-ui-keluar-unfinished" class="absolute inset-0 hidden bg-black z-20">
                    <img id="preview-image-keluar-unfinished" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                    <div class="absolute top-20 right-4 z-30 flex flex-col items-end gap-2">
                        <span class="success-chip"><i class="fas fa-check-circle"></i> Foto Tersimpan</span>
                        <button type="button" id="retake-btn-keluar-unfinished" class="bg-white/20 backdrop-blur-md text-white border border-white/50 font-bold py-2.5 px-4 rounded-full shadow-lg flex items-center gap-2 text-xs">
                            <i class="fas fa-sync-alt"></i> Ulang
                        </button>
                    </div>
                </div>
            </div>

            <div id="snap-ui-keluar-unfinished" class="absolute bottom-6 left-0 right-0 flex justify-center z-30 pointer-events-none">
                <button type="button" id="snap-keluar-unfinished" class="btn-snap pointer-events-auto bg-amber-500 text-white rounded-full h-[72px] w-[72px] flex items-center justify-center text-3xl border-[6px] border-white shadow-2xl transition-transform hover:scale-105 active:scale-95 disabled:opacity-50" disabled>
                    <i class="fas fa-camera"></i>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-t-3xl z-40 flex flex-col relative flex-grow p-5" style="height: 40dvh;">
            <div class="w-full flex justify-center pt-1 pb-4">
                
            </div>
            {{-- Route disamakan dengan absen_desktop: form ini memakai controller updateKeluar yang sama --}}
            <form action="{{ route('absen.keluar', $unfinishedAbsensi->id) }}" method="POST" enctype="multipart/form-data" id="form-keluar-unfinished" class="flex flex-col h-full">
                @csrf
                @method('PATCH')
                <input type="hidden" name="latitude_keluar" id="latitude-keluar-unfinished">
                <input type="hidden" name="longitude_keluar" id="longitude-keluar-unfinished">
                <input type="file" name="lampiran_keluar" id="lampiran-keluar-unfinished" class="hidden" accept="image/*">

                <div class="flex-grow flex flex-col justify-center items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mb-4">
                        <i class="fas fa-exclamation-triangle text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-800">Selesaikan Absen</h3>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Absen keluar untuk tanggal {{ \Carbon\Carbon::parse($unfinishedAbsensi->tanggal)->translatedFormat('d M Y') }}.</p>
                </div>

                <div class="mt-auto pt-4 pb-2">
                    <button type="submit" id="submit-button-keluar-unfinished" class="w-full bg-amber-500 text-white font-black py-4 rounded-2xl text-base shadow-xl shadow-amber-500/30 transition-transform active:scale-95 flex items-center justify-center gap-2 disabled:bg-slate-300 disabled:shadow-none" disabled>
                        <i class="fas fa-paper-plane"></i> Kirim Absen Tertunda
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ========================================================= --}}
    {{-- MODAL ABSEN LEMBUR --}}
    {{-- ========================================================= --}}
    @if ($absensiHariIni || $isHoliday)
    <div id="modal-absen-lembur" class="fixed inset-0 z-[100] bg-slate-900 hidden flex-col transition-all duration-300">
        <div class="relative flex-grow flex flex-col justify-center items-center bg-black overflow-hidden z-10" style="height: 60dvh;">
            <div class="absolute top-0 left-0 right-0 p-5 pt-6 flex justify-between items-start z-30 bg-gradient-to-b from-black/80 to-transparent">
                <button type="button" onclick="closeCam('-lembur')" class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/30 transition shadow-lg">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="text-right text-white">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-purple-300 mb-0.5">Absen Lembur</p>
                    <p class="text-base font-black leading-none">{{ \Carbon\Carbon::now()->translatedFormat('l, d M Y') }}</p>
                </div>
            </div>

            <div id="camera-container-lembur" class="absolute inset-0 w-full h-full z-10 bg-slate-900">
                <video id="video-lembur" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay playsinline muted></video>
                <canvas id="canvas-lembur" class="hidden"></canvas>

                <div id="preview-ui-lembur" class="absolute inset-0 hidden bg-black z-20">
                    <img id="preview-image-lembur" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                    <div class="absolute top-20 right-4 z-30 flex flex-col items-end gap-2">
                        <span class="success-chip"><i class="fas fa-check-circle"></i> Foto Tersimpan</span>
                        <button type="button" id="retake-btn-lembur" class="bg-white/20 backdrop-blur-md text-white border border-white/50 font-bold py-2.5 px-4 rounded-full shadow-lg flex items-center gap-2 text-xs">
                            <i class="fas fa-sync-alt"></i> Ulang
                        </button>
                    </div>
                </div>
            </div>

            <div id="snap-ui-lembur" class="absolute bottom-6 left-0 right-0 flex justify-center z-30 pointer-events-none">
                <button type="button" id="snap-lembur" class="btn-snap pointer-events-auto bg-purple-600 text-white rounded-full h-[72px] w-[72px] flex items-center justify-center text-3xl border-[6px] border-white shadow-2xl transition-transform hover:scale-105 active:scale-95 disabled:opacity-50" disabled>
                    <i class="fas fa-camera"></i>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-t-3xl z-40 flex flex-col relative flex-grow p-5" style="height: 40dvh;">
            <div class="w-full flex justify-center pt-1 pb-4">
                
            </div>
            <form action="{{ route('absen.lembur.store') }}" method="POST" enctype="multipart/form-data" id="form-lembur" class="flex flex-col h-full">
                @csrf
                <input type="hidden" name="latitude_masuk" id="latitude-lembur">
                <input type="hidden" name="longitude_masuk" id="longitude-lembur">
                <input type="file" name="lampiran_masuk" id="lampiran-lembur" class="hidden" accept="image/*">

                <div class="flex-grow flex flex-col justify-center text-center">
                    <h3 class="text-sm font-black text-slate-800 mb-2">Mulai Lembur</h3>
                    <textarea name="keterangan" id="keterangan-lembur" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-purple-500 focus:border-purple-500" placeholder="Keterangan tugas lembur (Wajib)"></textarea>
                </div>

                <div class="mt-auto pt-4 pb-2">
                    <button type="submit" id="submit-button-lembur" class="w-full bg-purple-600 text-white font-black py-4 rounded-2xl text-base shadow-xl shadow-purple-600/30 transition-transform active:scale-95 flex items-center justify-center gap-2 disabled:bg-slate-300 disabled:shadow-none" disabled>
                        <i class="fas fa-paper-plane"></i> Kirim Absen Lembur
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ========================================================= --}}
    {{-- MODAL KELUAR LEMBUR --}}
    {{-- ========================================================= --}}
    @if ($lemburHariIni)
    <div id="modal-keluar-lembur" class="fixed inset-0 z-[100] bg-slate-900 hidden flex-col transition-all duration-300">
        <div class="relative flex-grow flex flex-col justify-center items-center bg-black overflow-hidden z-10" style="height: 60dvh;">
            <div class="absolute top-0 left-0 right-0 p-5 pt-6 flex justify-between items-start z-30 bg-gradient-to-b from-black/80 to-transparent">
                <button type="button" onclick="closeCam('-keluar-lembur')" class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/30 transition shadow-lg">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="text-right text-white">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-rose-300 mb-0.5">Keluar Lembur</p>
                    <p class="text-base font-black leading-none">{{ \Carbon\Carbon::now()->translatedFormat('l, d M Y') }}</p>
                </div>
            </div>

            <div id="camera-container-keluar-lembur" class="absolute inset-0 w-full h-full z-10 bg-slate-900">
                <video id="video-keluar-lembur" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay playsinline muted></video>
                <canvas id="canvas-keluar-lembur" class="hidden"></canvas>

                <div id="preview-ui-keluar-lembur" class="absolute inset-0 hidden bg-black z-20">
                    <img id="preview-image-keluar-lembur" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                    <div class="absolute top-20 right-4 z-30 flex flex-col items-end gap-2">
                        <span class="success-chip"><i class="fas fa-check-circle"></i> Foto Tersimpan</span>
                        <button type="button" id="retake-btn-keluar-lembur" class="bg-white/20 backdrop-blur-md text-white border border-white/50 font-bold py-2.5 px-4 rounded-full shadow-lg flex items-center gap-2 text-xs">
                            <i class="fas fa-sync-alt"></i> Ulang
                        </button>
                    </div>
                </div>
            </div>

            <div id="snap-ui-keluar-lembur" class="absolute bottom-6 left-0 right-0 flex justify-center z-30 pointer-events-none">
                <button type="button" id="snap-keluar-lembur" class="btn-snap pointer-events-auto bg-rose-600 text-white rounded-full h-[72px] w-[72px] flex items-center justify-center text-3xl border-[6px] border-white shadow-2xl transition-transform hover:scale-105 active:scale-95 disabled:opacity-50" disabled>
                    <i class="fas fa-camera"></i>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-t-3xl z-40 flex flex-col relative flex-grow p-5" style="height: 40dvh;">
            <div class="w-full flex justify-center pt-1 pb-4">
                
            </div>
            <form action="{{ route('absen.lembur.keluar', $lemburHariIni->id) }}" method="POST" enctype="multipart/form-data" id="form-keluar-lembur" class="flex flex-col h-full">
                @csrf
                @method('PATCH')
                <input type="hidden" name="latitude_keluar" id="latitude-keluar-lembur">
                <input type="hidden" name="longitude_keluar" id="longitude-keluar-lembur">
                <input type="file" name="lampiran_keluar" id="lampiran-keluar-lembur" class="hidden" accept="image/*">

                <div class="flex-grow flex flex-col justify-center text-center">
                    <h3 class="text-sm font-black text-slate-800 mb-2">Selesai Lembur</h3>
                    <textarea name="laporan" id="laporan-keluar-lembur" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:ring-rose-500 focus:border-rose-500" placeholder="Laporan hasil lembur (Opsional)"></textarea>
                </div>

                <div class="mt-auto pt-4 pb-2">
                    <button type="submit" id="submit-button-keluar-lembur" class="w-full bg-rose-600 text-white font-black py-4 rounded-2xl text-base shadow-xl shadow-rose-600/30 transition-transform active:scale-95 flex items-center justify-center gap-2 disabled:bg-slate-300 disabled:shadow-none" disabled>
                        <i class="fas fa-paper-plane"></i> Kirim Keluar Lembur
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // =========================================================================
        // JAM REALTIME
        // =========================================================================
        const updateTime = () => {
            const now = new Date();
            const jamEl = document.getElementById('jam-realtime-mobile');
            if (jamEl) jamEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        };
        setInterval(updateTime, 1000);
        updateTime();

        // =========================================================================
        // REKAP & TIM BOTTOM SHEET
        // =========================================================================
        window.openRekapModal = function () {
            const modal = document.getElementById('modal-rekap-tim');
            const content = document.getElementById('modal-rekap-tim-content');
            if (!modal || !content) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            requestAnimationFrame(() => content.classList.remove('translate-y-full'));
        };

        window.closeRekapModal = function () {
            const modal = document.getElementById('modal-rekap-tim');
            const content = document.getElementById('modal-rekap-tim-content');
            if (!modal || !content) return;
            content.classList.add('translate-y-full');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        };

        // =========================================================================
        // GENERIC CAMERA LOGIC (per prefix) â€” foto disimpan langsung ke input
        // lampiran{prefix} yang sudah bernama sesuai kebutuhan AbsenController
        // =========================================================================
        window.cameraInstances = {};

        function setupCameraLogic(prefix) {
            const cameraContainer = document.getElementById(`camera-container${prefix}`);
            if (!cameraContainer) return;

            const video = document.getElementById(`video${prefix}`);
            const canvas = document.getElementById(`canvas${prefix}`);
            const snap = document.getElementById(`snap${prefix}`);
            const snapUi = document.getElementById(`snap-ui${prefix}`);
            const previewUi = document.getElementById(`preview-ui${prefix}`);
            const previewImage = document.getElementById(`preview-image${prefix}`);
            const retakeBtn = document.getElementById(`retake-btn${prefix}`);
            const fileInput = document.getElementById(`lampiran${prefix}`);

            if (!video || !canvas || !snap) return;

            let currentStream = null;
            const isMobileUA = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

            const resetView = () => {
                previewUi.classList.add('hidden');
                video.classList.remove('hidden');
                if (snapUi) snapUi.classList.remove('hidden');
            };

            const startCamera = () => {
                resetView();
                stopCamera();
                const constraints = {
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                };

                navigator.mediaDevices.getUserMedia(constraints)
                    .then((stream) => {
                        currentStream = stream;
                        video.srcObject = stream;
                        video.play();
                        snap.disabled = false;
                    })
                    .catch((err) => {
                        console.error('Camera error:', err);
                        alert('Kamera tidak dapat diakses. Pastikan Anda telah memberikan izin kamera pada browser.');
                    });
            };

            const stopCamera = () => {
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                    currentStream = null;
                }
                snap.disabled = true;
            };

            snap.addEventListener('click', () => {
                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                context.translate(canvas.width, 0);
                context.scale(-1, 1);
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                context.setTransform(1, 0, 0, 1, 0, 0);

                const dataURL = canvas.toDataURL('image/jpeg', 0.85);
                previewImage.src = dataURL;

                video.classList.add('hidden');
                if (snapUi) snapUi.classList.add('hidden');
                previewUi.classList.remove('hidden');

                canvas.toBlob((blob) => {
                    const fileName = `selfie${prefix.replace(/-/g, '_')}_${Date.now()}.jpg`;
                    const file = new File([blob], fileName, { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    if (fileInput) fileInput.files = dt.files;

                    stopCamera();
                    document.dispatchEvent(new CustomEvent(`photoReady${prefix}`, { detail: { isReady: true } }));
                }, 'image/jpeg', 0.85);
            });

            if (retakeBtn) {
                retakeBtn.addEventListener('click', () => {
                    if (fileInput) fileInput.value = '';
                    document.dispatchEvent(new CustomEvent(`photoReady${prefix}`, { detail: { isReady: false } }));
                    startCamera();
                });
            }

            window.cameraInstances[prefix] = { startCamera, stopCamera };

            // Kamera utama (form absen masuk) langsung aktif begitu halaman dibuka
            if (prefix === '') {
                startCamera();
            }
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
            let isLocationReady = false;
            let isPhotoReady = false;

            document.addEventListener('photoReady', e => {
                isPhotoReady = e.detail.isReady;
                checkFormReadiness();
            });

            const checkFormReadiness = () => {
                if (hiddenStatusInput.value === 'hadir') {
                    if (isLocationReady && isPhotoReady) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Absensi';
                    } else {
                        submitButton.disabled = true;
                        let text = 'Mohon ';
                        if (!isPhotoReady) text += 'Ambil Foto';
                        if (!isLocationReady && !isPhotoReady) text += ' & ';
                        if (!isLocationReady) text += 'Izinkan Lokasi';
                        submitButton.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${text}`;
                    }
                } else {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Absensi';
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
                hadir: 'bg-emerald-500 text-white shadow-emerald-500/20 border-transparent',
                izin: 'bg-amber-500 text-white shadow-amber-500/20 border-transparent',
                sakit: 'bg-rose-500 text-white shadow-rose-500/20 border-transparent'
            };

            const setActiveButton = (status) => {
                document.querySelectorAll('.status-btn').forEach(btn => {
                    btn.className = 'status-btn flex-1 flex flex-col items-center justify-center gap-1.5 py-3 rounded-2xl transition-all border border-slate-200 bg-slate-50 text-slate-400';
                });
                const activeButton = document.querySelector(`.status-btn[data-status="${status}"]`);
                if (activeButton) {
                    activeButton.className = `status-btn flex-1 flex flex-col items-center justify-center gap-1.5 py-3 rounded-2xl transition-all border shadow-md ${selectedStyles[status]}`;
                }
            };

            const toggleUiForStatus = (status) => {
                const keteranganContainer = document.getElementById('keterangan-container');

                isPhotoReady = false;

                if (status === 'hadir') {
                    keteranganContainer.classList.add('hidden');
                    document.getElementById('camera-container').classList.remove('hidden');
                    document.getElementById('snap-ui').classList.remove('hidden');
                    if (window.cameraInstances['']) window.cameraInstances[''].startCamera();
                    getLocation();
                } else {
                    keteranganContainer.classList.remove('hidden');
                    document.getElementById('camera-container').classList.add('hidden');
                    document.getElementById('snap-ui').classList.add('hidden');
                    if (window.cameraInstances['']) window.cameraInstances[''].stopCamera();
                    isLocationReady = true; // lokasi tidak wajib untuk izin/sakit
                }
                checkFormReadiness();
            };

            document.getElementById('status-buttons').addEventListener('click', function (e) {
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
        // MODAL LOGIC (Absen Keluar / Keluar Tertunda / Lembur / Keluar Lembur)
        // Setiap modal punya fungsi open()/close() sendiri yang me-reset kamera,
        // lokasi, dan keterangan setiap kali dibuka â€” supaya tidak ada state basi
        // dari sesi sebelumnya (foto lama, tombol submit yang masih aktif, dst).
        // =========================================================================
        window.camModals = {};

        function setupModalLogic(modalId, prefix, options = {}) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            const submitBtn = document.getElementById(`submit-button${prefix}`);
            const latitudeInput = document.getElementById(`latitude${prefix}`);
            const longitudeInput = document.getElementById(`longitude${prefix}`);
            const keteranganInput = options.keteranganId ? document.getElementById(options.keteranganId) : null;
            const requireKeterangan = !!options.requireKeterangan;

            let isLocationReady = false;
            let isPhotoReady = false;

            const checkReadiness = () => {
                const isKeteranganReady = (requireKeterangan && keteranganInput)
                    ? keteranganInput.value.trim() !== ''
                    : true;
                if (submitBtn) submitBtn.disabled = !(isLocationReady && isPhotoReady && isKeteranganReady);
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
                    () => { alert('Gagal mendapatkan lokasi. Pastikan GPS aktif dan izin lokasi diberikan.'); isLocationReady = false; checkReadiness(); },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            };

            document.addEventListener(`photoReady${prefix}`, e => {
                isPhotoReady = e.detail.isReady;
                checkReadiness();
            });

            if (keteranganInput) {
                keteranganInput.addEventListener('input', checkReadiness);
            }

            window.camModals[prefix] = {
                open() {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    isPhotoReady = false;
                    if (window.cameraInstances[prefix]) window.cameraInstances[prefix].startCamera();
                    getLocation();
                    checkReadiness();
                },
                close() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    if (window.cameraInstances[prefix]) window.cameraInstances[prefix].stopCamera();
                    if (keteranganInput) keteranganInput.value = '';
                    isLocationReady = false;
                    isPhotoReady = false;
                    checkReadiness();
                }
            };
        }

        window.openCam = function (prefix) {
            if (window.camModals[prefix]) window.camModals[prefix].open();
        };
        window.closeCam = function (prefix) {
            if (window.camModals[prefix]) window.camModals[prefix].close();
        };

        setupModalLogic('modal-absen-keluar', '-keluar');
        setupModalLogic('modal-absen-keluar-unfinished', '-keluar-unfinished');
        setupModalLogic('modal-absen-lembur', '-lembur', { keteranganId: 'keterangan-lembur', requireKeterangan: true });
        // Laporan pada keluar-lembur bersifat opsional di backend, jadi tidak menahan tombol submit
        setupModalLogic('modal-keluar-lembur', '-keluar-lembur');
    });
    </script>
    @endpush
</x-layout-users>
