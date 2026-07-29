<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
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

        /* == Glass Cards == */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 24px;
        }

        /* Aligned Date Time Badge */
        .aktivitas-date-card {
            background: #f5f3ff; /* light violet/indigo */
            border: 1px solid #ddd6fe;
            border-radius: 12px;
            padding: 6px 12px;
            display: inline-flex;
            align-items: center;
            color: #6d28d9;
            box-shadow: 0 2px 4px rgba(109, 40, 217, 0.03);
        }
        .aktivitas-date-val {
            font-size: 0.78rem; font-weight: 800;
        }
        .aktivitas-time-badge {
            background: #ffffff;
            color: #6d28d9;
            font-size: 0.72rem; font-weight: 800;
            padding: 3px 8px; border-radius: 8px;
            border: 1px solid #ddd6fe;
        }

        /* Form Controls */
        .aktivitas-label {
            display: block; font-size: 0.8rem; font-weight: 700; color: #475569; margin-bottom: 8px;
        }
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

        /* Camera Box */
        .camera-box {
            position: relative;
            aspect-ratio: 16 / 9;
            border-radius: 20px;
            overflow: hidden;
            background: #0f172a;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.15);
            border: 2px solid #fff;
        }
        .camera-video {
            width: 100%; height: 100%; object-fit: cover;
        }

        /* Camera UI Controls */
        .snap-control-ui {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            display: flex; justify-content: center;
            padding: 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        }
        .snap-btn {
            background: #2563eb;
            color: #fff;
            border-radius: 50%;
            width: 56px; height: 56px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .snap-btn:hover:not(:disabled) { transform: scale(1.08); background: #1d4ed8; }
        .snap-btn:active { transform: scale(0.92); }
        .snap-btn:disabled { background: #94a3b8; border-color: #cbd5e1; cursor: not-allowed; }

        .flip-btn {
            position: absolute;
            top: 14px; right: 14px;
            width: 40px; height: 40px;
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.25);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; cursor: pointer;
            transition: all 0.2s;
        }
        .flip-btn:hover { background: rgba(15, 23, 42, 0.85); transform: rotate(180deg); }

        .preview-overlay {
            position: absolute; inset: 0;
            background: #000;
        }
        .preview-img { width: 100%; height: 100%; object-fit: cover; }
        .preview-btn-row {
            position: absolute; bottom: 0; left: 0; right: 0;
            display: flex; justify-content: center; gap: 12px;
            padding: 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        }
        .preview-action-btn {
            padding: 10px 20px; border-radius: 12px;
            font-size: 0.85rem; font-weight: 700;
            color: #fff; border: none; cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: all 0.2s;
        }
        .preview-action-btn:hover { transform: translateY(-2px); }
        .preview-btn-retake { background: #ef4444; }
        .preview-btn-retake:hover { background: #dc2626; }
        .preview-btn-use    { background: #10b981; }
        .preview-btn-use:hover { background: #059669; }

        /* Submit Button */
        .submit-aktivitas-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff; font-size: 1rem; font-weight: 800;
            border: none; border-radius: 14px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(37,99,235,0.3);
            transition: all 0.2s;
        }
        .submit-aktivitas-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37,99,235,0.4);
        }
        .submit-aktivitas-btn:disabled {
            background: #cbd5e1; color: #64748b;
            box-shadow: none; cursor: not-allowed;
        }

        /* Tab Switcher */
        .tab-switcher {
            display: flex;
            background: rgba(241, 245, 249, 0.8);
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 4px;
        }
        .tab-btn {
            flex: 1;
            padding: 10px;
            font-size: 0.85rem; font-weight: 700;
            border: none; border-radius: 10px;
            cursor: pointer;
            background: transparent;
            color: #64748b;
            transition: all 0.2s;
        }
        .tab-btn.active {
            background: #fff;
            color: #1d4ed8;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }

        /* Log List Cards */
        .log-item-card {
            background: linear-gradient(135deg, rgba(239, 246, 255, 0.9), rgba(245, 243, 255, 0.9));
            border-radius: 16px;
            border: 1.5px solid rgba(191, 219, 254, 0.6);
            padding: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
            transition: all 0.2s;
            margin-bottom: 12px;
        }
        .log-item-card:hover { 
            border-color: #93c5fd; 
            background: linear-gradient(135deg, rgba(219, 234, 254, 0.95), rgba(233, 213, 255, 0.95)); 
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
        .log-item-desc {
            font-size: 0.88rem; color: #334155; line-height: 1.5; font-weight: 600;
        }
        .log-item-footer {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .log-item-time {
            font-size: 0.78rem; font-weight: 800; color: #64748b;
            display: flex; align-items: center; gap: 6px;
        }
        .log-item-link-row {
            display: flex; gap: 8px;
        }
        .log-item-link {
            font-size: 0.72rem; font-weight: 800;
            padding: 4px 10px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #1e293b;
            text-decoration: none;
            transition: all 0.15s;
            border: 1px solid #e2e8f0;
            cursor: pointer;
        }
        .log-item-link:hover { 
            background: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
        }

        /* Tim grid buttons */
        .tim-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .tim-member-btn {
            display: flex; flex-direction: column; align-items: center;
            padding: 16px 12px;
            border-radius: 16px;
            background: rgba(255,255,255,0.7);
            border: 1.5px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tim-member-btn:hover {
            border-color: #bfdbfe;
            background: #eff6ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.08);
        }
        .tim-member-avatar {
            width: 52px; height: 52px;
            border-radius: 50%; object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            margin-bottom: 8px;
            transition: transform 0.2s;
        }
        .tim-member-btn:hover .tim-member-avatar {
            transform: scale(1.05);
        }
        .tim-member-name {
            font-size: 0.85rem; font-weight: 800; color: #1e293b;
            text-align: center; width: 100%;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .tim-member-role {
            font-size: 0.72rem; color: #64748b; font-weight: 600; margin-top: 2px;
        }

        /* Tim Detail Card inside modal */
        .tim-detail-card {
            background: #fff;
            border-radius: 16px;
            border: 1.5px solid #e2e8f0;
            padding: 16px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        /* Balanced Empty State */
        .empty-log-state {
            min-height: 380px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 2px dashed #cbd5e1;
            border-radius: 20px;
            background: rgba(248, 250, 252, 0.5);
            padding: 24px;
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">
        {{-- Background Animations --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[10%] left-[5%] w-32 h-32 bg-white/40 backdrop-blur-md border border-white/50 rounded-full animate-float"></div>
            <div class="absolute bottom-[15%] right-[10%] w-48 h-48 bg-white/30 backdrop-blur-md border border-white/40 rounded-full animate-float-delayed"></div>
            <div class="absolute inset-0" style="background-image: radial-gradient(rgba(100, 116, 139, 0.1) 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col">

            {{-- NOTIFIKASI --}}
            
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm" role="alert">
                    <p class="font-bold mb-1 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs font-semibold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif

            {{-- TOMBOL KEMBALI MODERN --}}
            <a href="{{ route('dashboard') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Dashboard
            </a>

            {{-- CARD ATAS PENJELAS HALAMAN (Sesuai Cuti & Simple Jelas) --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-3xl p-6 md:p-8 shadow-xl mb-6 overflow-hidden border border-white/20">
                {{-- Decorative circles --}}
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute right-20 -bottom-10 w-24 h-24 bg-white/10 rounded-full blur-lg pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-5">
                        <div class="h-14 w-14 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                            <i class="fas fa-running text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-xl md:text-2xl font-black tracking-tight text-white uppercase">Aktivitas Harian</h1>
                            <p class="text-blue-100 text-xs md:text-sm mt-1 font-medium leading-relaxed max-w-xl">
                                Catat kegiatan kerja harian Anda secara real-time dengan foto dan lokasi GPS, serta pantau aktivitas rekan kerja Anda.
                            </p>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider bg-white/90 border border-white text-blue-700 shadow-sm">
                            <i class="fas fa-check-circle text-green-500"></i>
                            {{ isset($aktivitasHariIni) ? $aktivitasHariIni->count() : 0 }} Tercatat
                        </span>
                    </div>
                </div>
            </div>

            {{-- GRID LAYOUT 2 KOLOM --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 pb-10 items-stretch">

                {{-- KOLOM KIRI: FORM CATAT AKTIVITAS --}}
                <div class="h-full">
                    <form action="{{ route('aktivitas.store') }}" method="POST" enctype="multipart/form-data" id="form-aktivitas" class="m-0 h-full">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input name="lampiran" id="lampiran" type="file" class="hidden" accept="image/*" />

                        <div class="glass-card space-y-5 h-full flex flex-col justify-between">
                            <div class="space-y-5">
                                {{-- Judul dan Tanggal Sejajar dengan Warna Kotak Berbeda (Ungu/Violet) --}}
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2 flex-shrink-0">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0"><i class="fas fa-pen-nib text-lg"></i></div>
                                        <h3 class="text-lg font-black text-slate-800 leading-none">Catat Aktivitas</h3>
                                    </div>
                                    <div class="aktivitas-date-card flex-shrink-0">
                                        <span class="aktivitas-date-val text-[11px] font-black mr-2">{{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}</span>
                                        <span class="aktivitas-time-badge text-[10px] font-black" id="jam-realtime">--:-- WIB</span>
                                    </div>
                                </div>

                                {{-- 1. Kamera Terlebih Dahulu (di atas) --}}
                                <div>
                                    <label class="aktivitas-label">Ambil Foto Aktivitas <span class="text-red-500">*</span></label>
                                    <div class="camera-box" id="camera-container">
                                        <video id="video" class="camera-video" style="transform: scaleX(-1);" autoplay playsinline muted></video>
                                        <canvas id="canvas" class="hidden"></canvas>

                                        <div id="flip-camera-ui" class="hidden z-10">
                                            <button type="button" id="flip-camera-btn" class="flip-btn">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>

                                        <div id="snap-ui" class="snap-control-ui hidden">
                                            <button type="button" id="snap" class="snap-btn" disabled>
                                                <i class="fas fa-camera"></i>
                                            </button>
                                        </div>

                                        <div id="preview-ui" class="preview-overlay hidden">
                                            <img id="preview-image" src="" class="preview-img" alt="Pratinjau Foto"/>
                                            <div class="preview-btn-row" id="preview-buttons">
                                                <button type="button" id="retake-btn" class="preview-action-btn preview-btn-retake"><i class="fas fa-sync-alt"></i>Ulang</button>
                                                <button type="button" id="use-photo-btn" class="preview-action-btn preview-btn-use"><i class="fas fa-check"></i>Gunakan</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="photo-success-msg" class="hidden text-center text-xs text-green-700 font-bold p-3 bg-green-50 rounded-xl border border-green-200 mt-2">
                                        <i class="fas fa-check-circle"></i> Foto berhasil diambil & siap dikirim.
                                    </div>
                                    @error('lampiran') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                                </div>

                                {{-- 2. Keterangan di bawah (Input 1 baris ramping) --}}
                                <div>
                                    <label for="keterangan" class="aktivitas-label">Keterangan Aktivitas <span class="text-red-500">*</span></label>
                                    <input type="text" name="keterangan" id="keterangan" class="modern-input" placeholder="Tuliskan keterangan singkat pekerjaan Anda..." required value="{{ old('keterangan') }}">
                                    @error('keterangan') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <button type="submit" id="submit-button" class="submit-aktivitas-btn mt-4">
                                Buat Aktivitas
                            </button>
                        </div>
                    </form>
                </div>

                {{-- KOLOM KANAN: RIWAYAT & REKAN DALAM 1 CARD --}}
                <div class="h-full">
                    <div class="glass-card flex flex-col h-full justify-between">
                        <div class="space-y-5">
                            {{-- Tab Switcher --}}
                            <div class="tab-switcher">
                                <button type="button" id="tab-riwayat-btn" class="tab-btn active">
                                    Riwayat Saya ({{ isset($aktivitasHariIni) ? $aktivitasHariIni->count() : 0 }})
                                </button>
                                @if(isset($timYangDipantau) && $timYangDipantau->count() > 0)
                                <button type="button" id="tab-rekan-btn" class="tab-btn">
                                    Rekan Kerja ({{ $timYangDipantau->count() }})
                                </button>
                                @endif
                            </div>

                            {{-- VIEW 1: RIWAYAT SAYA --}}
                            <div id="view-riwayat" class="space-y-3 overflow-y-auto max-h-[380px] pr-1 scrollbar-thin">
                                @forelse($aktivitasHariIni as $event)
                                    <div class="log-item-card">
                                        <p class="log-item-desc">{{ $event->keterangan ?? '' }}</p>
                                        <div class="log-item-footer">
                                            <span class="log-item-time">
                                                <i class="fas fa-clock text-blue-500"></i>
                                                {{ $event->created_at->format('H:i') }} WIB
                                            </span>
                                            <div class="log-item-link-row">
                                                @if($event->photo_url)
                                                    <a href="#" data-img-url="{{ $event->photo_url }}" class="log-item-link view-photo-trigger">
                                                        Foto
                                                    </a>
                                                @endif
                                                @if($event->latitude && $event->longitude)
                                                    <a href="https://www.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}" target="_blank" class="log-item-link">
                                                        Lokasi
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-log-state">
                                        <i class="fas fa-tasks text-3xl text-slate-300 mb-2 block"></i>
                                        <p class="text-slate-500 text-xs font-semibold">Belum ada aktivitas hari ini.</p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- VIEW 2: AKTIVITAS REKAN --}}
                            <div id="view-rekan" class="hidden overflow-y-auto max-h-[380px] pr-1 scrollbar-thin">
                                @if(isset($timYangDipantau) && $timYangDipantau->count() > 0)
                                    <div class="tim-grid">
                                        @foreach($timYangDipantau as $tim)
                                            <button type="button" 
                                                    class="tim-member-btn tim-card-button"
                                                    data-userid="{{ $tim->id }}"
                                                    data-username="{{ $tim->name }}">
                                                
                                                <img src="{{ $tim->profile_picture ? asset('storage/' . $tim->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($tim->name ?? 'U').'&background=random&color=fff&size=64' }}" 
                                                     alt="{{ $tim->name }}" 
                                                     class="tim-member-avatar">
                                                <p class="tim-member-name">{{ $tim->name }}</p>
                                                <span class="tim-member-role">{{ $tim->jabatan ?? 'Staf' }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-log-state">
                                        <i class="fas fa-users-slash text-3xl text-slate-300 mb-2 block"></i>
                                        <p class="text-slate-500 text-xs font-semibold">Tidak ada anggota tim.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Footer Info --}}
                        <div class="flex items-center gap-3 p-4 mt-4 bg-blue-50/70 border border-blue-100 rounded-2xl text-xs text-blue-700 font-semibold leading-relaxed flex-shrink-0">
                            <div class="w-6 h-6 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                                <i class="fas fa-info-circle text-xs"></i>
                            </div>
                            <p class="flex-1 text-xs">Data diperbarui secara real-time. Klik nama rekan kerja untuk melihat rincian lokasi dan foto aktivitasnya.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL PANTAU TIM --}}
    <div id="modal-pantau" class="fixed inset-0 z-[1000] flex items-center justify-center bg-slate-900/60 backdrop-blur-md hidden" style="z-index: 9999;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div id="modal-pantau-backdrop" class="fixed inset-0"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full mx-4 max-h-[80vh] flex flex-col overflow-hidden border border-slate-100 z-10">
            <div class="flex justify-between items-center p-5 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-user-friends"></i></div>
                    <h3 class="text-base font-black text-slate-800" id="modal-pantau-title">Memuat...</h3>
                </div>
                <button type="button" id="modal-pantau-close-btn" class="text-slate-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="p-5 space-y-4 overflow-y-auto bg-slate-50 flex-grow" id="modal-pantau-content">
                {{-- Isi modal --}}
            </div>
        </div>
    </div>

    {{-- MODAL PRATINJAU FOTO --}}
    <div id="modal-foto" class="fixed inset-0 z-[1100] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm hidden" style="z-index: 10000;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div id="modal-foto-backdrop" class="fixed inset-0"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl max-w-2xl w-full mx-4 overflow-hidden border border-slate-100 z-10 flex flex-col">
            <div class="flex justify-between items-center p-4 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-image"></i></div>
                    <h3 class="text-sm font-black text-slate-800">Pratinjau Foto Aktivitas</h3>
                </div>
                <button type="button" id="modal-foto-close-btn" class="text-slate-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="p-4 bg-slate-950 flex items-center justify-center min-h-[300px] max-h-[70vh]">
                <img id="modal-foto-img" src="" class="max-w-full max-h-[60vh] object-contain rounded-xl shadow-lg border border-white/10" alt="Foto Lampiran Aktivitas"/>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
 
        // --- LOGIKA TAB SWITCHER ---
        const tabRiwayat = document.getElementById('tab-riwayat-btn');
        const tabRekan = document.getElementById('tab-rekan-btn');
        const viewRiwayat = document.getElementById('view-riwayat');
        const viewRekan = document.getElementById('view-rekan');
 
        if(tabRiwayat && tabRekan) {
            tabRiwayat.addEventListener('click', () => {
                tabRiwayat.classList.add('active');
                tabRekan.classList.remove('active');
                viewRiwayat.classList.remove('hidden');
                viewRekan.classList.add('hidden');
            });
 
            tabRekan.addEventListener('click', () => {
                tabRekan.classList.add('active');
                tabRiwayat.classList.remove('active');
                viewRekan.classList.remove('hidden');
                viewRiwayat.classList.add('hidden');
            });
        }
 
        // --- JAM REALTIME ---
        const jamElement = document.getElementById('jam-realtime');
        if(jamElement) {
            function updateJam() {
                jamElement.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
            }
            setInterval(updateJam, 1000);
            updateJam();
        }
 
        // --- KAMERA LOGIC ---
        window.cameraInstances = {};
        function setupCameraLogic(prefix) {
            const cameraContainer = document.getElementById(`camera-container${prefix}`);
            if (!cameraContainer) return;
 
            const fileInput = document.getElementById(`lampiran${prefix}`);
            const video = document.getElementById(`video${prefix}`);
            const canvas = document.getElementById(`canvas${prefix}`);
            const flipUI = document.getElementById(`flip-camera-ui${prefix}`);
            const flipButton = document.getElementById(`flip-camera-btn${prefix}`);
            const snapUI = document.getElementById(`snap-ui${prefix}`);
            const snapButton = document.getElementById(`snap${prefix}`);
            const previewUI = document.getElementById(`preview-ui${prefix}`);
            const previewImage = document.getElementById(`preview-image${prefix}`);
            const previewButtons = document.getElementById(`preview-buttons${prefix}`);
            const retakeButton = document.getElementById(`retake-btn${prefix}`);
            const usePhotoButton = document.getElementById(`use-photo-btn${prefix}`);
            
            let stream;
            let currentFacingMode = 'user';
 
            const startCamera = async () => {
                if (stream) { stream.getTracks().forEach(track => track.stop()); }
                snapButton.disabled = true;
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            facingMode: currentFacingMode,
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        } 
                    });
                    video.srcObject = stream;
                    video.style.transform = currentFacingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';
                    video.classList.remove('hidden');
                    snapUI.classList.remove('hidden');
                    flipUI.classList.remove('hidden');
                    previewUI.classList.add('hidden');
                    cameraContainer.classList.remove('hidden');
                    video.oncanplay = async () => { try { await video.play(); snapButton.disabled = false; } catch (e) {} };
                } catch (err) {
                    console.error(err);
                    if (err.name === 'NotFoundError' || err.name === 'OverconstrainedError') { flipUI.classList.add('hidden'); }
                }
            };
            
            const stopCamera = () => { if (stream) { stream.getTracks().forEach(track => track.stop()); } snapButton.disabled = true; flipUI.classList.add('hidden'); };
 
            snapButton.addEventListener("click", function() {
                canvas.width = video.videoWidth; canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.save();
                if (currentFacingMode === 'user') { ctx.scale(-1, 1); ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height); } 
                else { ctx.drawImage(video, 0, 0, canvas.width, canvas.height); }
                ctx.restore();
                previewImage.src = canvas.toDataURL('image/png');
                video.classList.add('hidden'); snapUI.classList.add('hidden'); flipUI.classList.add('hidden');
                previewUI.classList.remove('hidden'); previewButtons.classList.remove('hidden');
            });
 
            retakeButton.addEventListener('click', function() {
                video.classList.remove('hidden'); snapUI.classList.remove('hidden'); flipUI.classList.remove('hidden');
                previewUI.classList.add('hidden');
                document.getElementById('photo-success-msg').classList.add('hidden');
                document.dispatchEvent(new CustomEvent(`photoReady${prefix}`, { detail: { isReady: false } }));
            });
 
            usePhotoButton.addEventListener('click', function() {
                previewButtons.classList.add('hidden');
                canvas.toBlob(function(blob) {
                    const file = new File([blob], `aktivitas_${Date.now()}.png`, { type: "image/png" });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    stopCamera();
                    document.dispatchEvent(new CustomEvent(`photoReady${prefix}`, { detail: { isReady: true } }));
                }, 'image/png');
            });
            
            flipButton.addEventListener('click', function() {
                currentFacingMode = (currentFacingMode === 'user') ? 'environment' : 'user';
                startCamera(); 
            });
 
            window.cameraInstances[prefix] = { startCamera, stopCamera };
        }
        setupCameraLogic('');
 
        // --- VALIDASI FORM ---
        const submitButton = document.getElementById('submit-button');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        const photoSuccessMsg = document.getElementById('photo-success-msg');
        const keteranganInput = document.getElementById('keterangan'); 
        let isLocationReady = false;
        let isPhotoReady = false;
 
        document.addEventListener('photoReady', e => {
            isPhotoReady = e.detail.isReady;
            photoSuccessMsg.classList.toggle('hidden', !isPhotoReady);
            checkFormReadiness();
        });
 
        const checkFormReadiness = () => {
            const isKeteranganReady = keteranganInput.value.trim() !== '';
            if (isLocationReady && isPhotoReady && isKeteranganReady) { 
                submitButton.disabled = false; submitButton.textContent = 'Buat Aktivitas';
            } else {
                submitButton.disabled = true;
                let errors = [];
                if (!isKeteranganReady) errors.push('Isi Keterangan');
                if (!isPhotoReady) errors.push('Ambil Foto');
                if (!isLocationReady) errors.push('Izinkan Lokasi');
                submitButton.textContent = 'Mohon ' + errors.join(' & ');
            }
        };
 
        if(keteranganInput) { keteranganInput.addEventListener('input', checkFormReadiness); }
 
        const getLocation = () => {
            isLocationReady = false; checkFormReadiness();
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => { latitudeInput.value = pos.coords.latitude; longitudeInput.value = pos.coords.longitude; isLocationReady = true; checkFormReadiness(); },
                    () => { alert('Gagal ambil lokasi.'); isLocationReady = false; checkFormReadiness(); },
                    { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
                );
            }
        };
        
        window.cameraInstances[''].startCamera();
        getLocation();
        checkFormReadiness(); 
 
        document.getElementById('form-aktivitas').addEventListener('submit', function(e) {
            if (submitButton.disabled) { e.preventDefault(); alert('Lengkapi data.'); } 
            else { submitButton.disabled = true; submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...'; }
        });
 
        // --- MODAL PANTAU TIM ---
        const modalPantau = document.getElementById('modal-pantau');
        const modalPantauCloseBtn = document.getElementById('modal-pantau-close-btn');
        const modalPantauBackdrop = document.getElementById('modal-pantau-backdrop');
        const modalPantauTitle = document.getElementById('modal-pantau-title');
        const modalPantauContent = document.getElementById('modal-pantau-content');
 
        const openModalPantau = () => modalPantau.classList.remove('hidden');
        const closeModalPantau = () => modalPantau.classList.add('hidden');
 
        modalPantauCloseBtn.addEventListener('click', closeModalPantau);
        modalPantauBackdrop.addEventListener('click', closeModalPantau);
 
        const fetchAktivitasTim = async (userId, userName) => {
            modalPantauTitle.textContent = `${userName}`;
            modalPantauContent.innerHTML = `<div class="text-center text-slate-500 py-10"><i class="fas fa-spinner fa-spin text-2xl"></i><p class="mt-2">Memuat...</p></div>`;
            openModalPantau();
 
            try {
                const response = await fetch(`{{ route('aktivitas.getJson') }}?user_id=${userId}`);
                if (!response.ok) throw new Error('Gagal memuat');
                const data = await response.json();
 
                if (data && data.length > 0) {
                    let htmlContent = '<div class="space-y-4">';
                    data.forEach(item => {
                        const time = new Date(item.start).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        const photo = item.extendedProps.photo_url ? `<a href="#" data-img-url="${item.extendedProps.photo_url}" class="text-blue-600 text-xs font-bold bg-blue-50 border border-blue-100 rounded-lg px-2.5 py-1.5 hover:bg-blue-100 transition-colors view-photo-trigger">Foto</a>` : '';
                        const loc = (item.extendedProps.latitude) ? `<a href="https://www.google.com/maps?q=${item.extendedProps.latitude},${item.extendedProps.longitude}" target="_blank" class="text-red-600 text-xs font-bold bg-red-50 border border-red-100 rounded-lg px-2.5 py-1.5 hover:bg-red-100 transition-colors">Lokasi</a>` : '';
                        
                        htmlContent += `
                            <div class="tim-detail-card">
                                <p class="font-bold text-slate-800 text-sm">${item.title}</p>
                                <p class="text-slate-600 text-xs mt-2.5 leading-relaxed bg-slate-50/50 p-3 rounded-xl border border-slate-100">"${item.extendedProps.keterangan || ''}"</p>
                                <div class="flex justify-between items-center mt-3 pt-3 border-t border-slate-100">
                                    <span class="text-[11px] font-black text-slate-400"><i class="fas fa-clock"></i> ${time} WIB</span>
                                    <div class="space-x-2">${photo} ${loc}</div>
                                </div>
                            </div>
                        `;
                    });
                    htmlContent += '</div>';
                    modalPantauContent.innerHTML = htmlContent;
                } else {
                    modalPantauContent.innerHTML = `<div class="text-center py-10 text-slate-400"><i class="fas fa-calendar-times text-4xl mb-2"></i><p class="text-sm font-semibold">Belum ada aktivitas hari ini.</p></div>`;
                }
            } catch (error) {
                modalPantauContent.innerHTML = `<div class="text-center text-red-500 py-5 text-sm">Gagal memuat data.</div>`;
            }
        };
 
        // --- MODAL PRATINJAU FOTO ---
        const modalFoto = document.getElementById('modal-foto');
        const modalFotoCloseBtn = document.getElementById('modal-foto-close-btn');
        const modalFotoBackdrop = document.getElementById('modal-foto-backdrop');
        const modalFotoImg = document.getElementById('modal-foto-img');

        const openModalFoto = (imgUrl) => {
            modalFotoImg.src = imgUrl;
            modalFoto.classList.remove('hidden');
        };

        const closeModalFoto = () => {
            modalFoto.classList.add('hidden');
            modalFotoImg.src = '';
        };

        modalFotoCloseBtn.addEventListener('click', closeModalFoto);
        modalFotoBackdrop.addEventListener('click', closeModalFoto);
 
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.tim-card-button');
            if(btn) {
                fetchAktivitasTim(btn.dataset.userid, btn.dataset.username);
            }

            const photoTrigger = e.target.closest('.view-photo-trigger');
            if (photoTrigger) {
                e.preventDefault();
                const imgUrl = photoTrigger.getAttribute('data-img-url');
                if (imgUrl) {
                    openModalFoto(imgUrl);
                }
            }
        });
 
    });
    </script>
    @endpush
</x-layout-users>