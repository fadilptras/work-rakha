<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    @push('styles')
    <style>
        /* ===== AKTIVITAS PAGE — MOBILE FIRST STYLES ===== */

        .aktivitas-page-wrapper {
            padding: 16px 16px 48px;
        }
        @media (min-width: 768px) {
            .aktivitas-page-wrapper { padding: 24px 24px 56px; }
        }

        /* Back Button */
        .aktivitas-back-btn {
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
        .aktivitas-back-btn:hover { background: #eff6ff; }

        /* Container Card */
        .aktivitas-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 20px 18px;
            margin-bottom: 14px;
            border: 1px solid #f1f5f9;
        }
        .aktivitas-card-title {
            font-size: 1.15rem; font-weight: 800; color: #111827;
            margin-bottom: 16px;
        }

        /* Date Time Card */
        .aktivitas-date-card {
            background: #eff6ff;
            border: 1.5px solid #bfdbfe;
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .aktivitas-date-val {
            font-size: 0.85rem; font-weight: 800; color: #1e3a8a;
        }
        .aktivitas-time-badge {
            background: #fff;
            color: #1d4ed8;
            font-size: 0.78rem; font-weight: 800;
            padding: 4px 10px; border-radius: 8px;
            box-shadow: 0 2px 6px rgba(29, 78, 216, 0.08);
        }

        /* Form Controls */
        .aktivitas-label {
            display: block;
            font-size: 0.78rem; font-weight: 700; color: #475569;
            margin-bottom: 6px;
            text-transform: uppercase; letter-spacing: 0.03em;
        }
        .aktivitas-textarea {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.88rem;
            color: #1e293b;
            outline: none;
            transition: all 0.15s;
            box-sizing: border-box;
        }
        .aktivitas-textarea:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Camera Box */
        .camera-box {
            position: relative;
            aspect-ratio: 1 / 1;
            border-radius: 16px;
            overflow: hidden;
            background: #0f172a;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 12px;
        }
        .camera-video {
            width: 100%; height: 100%; object-fit: cover;
        }

        /* Camera UI Controls */
        .snap-control-ui {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            display: flex; justify-content: center;
            padding: 16px;
            background: linear-gradient(to top, rgba(0,0,0,0.5), transparent);
        }
        .snap-btn {
            background: #3b82f6;
            color: #fff;
            border-radius: 50%;
            width: 48px; height: 48px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: transform 0.15s;
        }
        .snap-btn:active { transform: scale(0.92); }
        .snap-btn:disabled { background: #94a3b8; }

        .flip-btn {
            position: absolute;
            top: 12px; right: 12px;
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem; cursor: pointer;
            transition: background 0.15s;
        }
        .flip-btn:hover { background: rgba(15, 23, 42, 0.8); }

        .preview-overlay {
            position: absolute; inset: 0;
            background: #000;
        }
        .preview-img { width: 100%; height: 100%; object-fit: cover; }
        .preview-btn-row {
            position: absolute; bottom: 0; left: 0; right: 0;
            display: flex; justify-content: center; gap: 10px;
            padding: 16px;
            background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);
        }
        .preview-action-btn {
            padding: 8px 16px; border-radius: 8px;
            font-size: 0.8rem; font-weight: 700;
            color: #fff; border: none; cursor: pointer;
            display: flex; align-items: center; gap: 6px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
        }
        .preview-btn-retake { background: #ef4444; }
        .preview-btn-use    { background: #10b981; }

        /* Submit Button */
        .submit-aktivitas-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #fff; font-size: 0.92rem; font-weight: 700;
            border: none; border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(37,99,235,0.3);
            transition: all 0.2s;
        }
        .submit-aktivitas-btn:disabled {
            background: #cbd5e1; color: #64748b;
            box-shadow: none; cursor: not-allowed;
        }

        /* Tab Switcher */
        .tab-switcher {
            display: flex;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 16px;
        }
        .tab-btn {
            flex: 1;
            padding: 9px;
            font-size: 0.85rem; font-weight: 700;
            border: none; border-radius: 9px;
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
            background: #fff;
            border-radius: 14px;
            border: 1.5px solid #f1f5f9;
            padding: 14px;
            margin-bottom: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            transition: border-color 0.15s;
        }
        .log-item-card:hover { border-color: #bfdbfe; }
        .log-item-desc {
            font-size: 0.85rem; color: #374151; line-height: 1.5;
        }
        .log-item-footer {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f8fafc;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .log-item-time {
            font-size: 0.78rem; font-weight: 700; color: #64748b;
            display: flex; align-items: center; gap: 6px;
        }
        .log-item-link-row {
            display: flex; gap: 12px;
        }
        .log-item-link {
            font-size: 0.75rem; font-weight: 700; color: #2563eb;
            text-decoration: none;
        }
        .log-item-link:hover { text-decoration: underline; }

        /* Tim grid buttons */
        .tim-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .tim-member-btn {
            display: flex; flex-direction: column; align-items: center;
            padding: 12px 8px;
            border-radius: 12px;
            background: #fff;
            border: 1.5px solid #f1f5f9;
            cursor: pointer;
            transition: all 0.15s;
        }
        .tim-member-btn:hover {
            border-color: #bfdbfe;
            background: #f0f7ff;
        }
        .tim-member-avatar {
            width: 44px; height: 44px;
            border-radius: 50%; object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            margin-bottom: 6px;
        }
        .tim-member-name {
            font-size: 0.78rem; font-weight: 700; color: #1e293b;
            text-align: center; width: 100%;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .tim-member-role {
            font-size: 0.68rem; color: #94a3b8; font-weight: 500;
        }

        /* Empty State */
        .empty-log-state {
            padding: 32px 16px;
            text-align: center;
            border: 2px dashed #e2e8f0;
            border-radius: 14px;
        }

        /* Modal styling */
        #modal-pantau {
            z-index: 1000 !important;
            backdrop-filter: blur(8px);
            background: rgba(15, 23, 42, 0.4);
        }
        .tim-detail-card {
            background: #fff;
            border-radius: 14px;
            border: 1.5px solid #f1f5f9;
            padding: 12px 14px;
            margin-bottom: 10px;
        }
    </style>
    @endpush

    <div class="bg-gray-50 sm:bg-gradient-to-br sm:from-sky-50 sm:to-blue-100 min-h-screen">
        <div class="max-w-5xl mx-auto aktivitas-page-wrapper">

            {{-- NOTIFIKASI --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl text-sm" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl text-sm" role="alert">
                    <p class="font-bold mb-1">Terjadi Kesalahan</p>
                    <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif

            {{-- TOMBOL KEMBALI --}}
            <a href="{{ route('dashboard') }}" class="aktivitas-back-btn">
                <i class="fas fa-arrow-left text-xs"></i>
                Kembali ke Dashboard
            </a>

            {{-- GRID UTAMA --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 pb-10">

                {{-- KOLOM KIRI & TENGAH (FORM INPUT) --}}
                <div class="lg:col-span-2">
                    <form action="{{ route('aktivitas.store') }}" method="POST" enctype="multipart/form-data" id="form-aktivitas">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input name="lampiran" id="lampiran" type="file" class="hidden" accept="image/*" />

                        <div class="aktivitas-card">
                            <h2 class="aktivitas-card-title">Catat Aktivitas</h2>

                            {{-- Waktu Card --}}
                            <div class="aktivitas-date-card">
                                <span class="aktivitas-date-val">{{ \Carbon\Carbon::now()->translatedFormat('l, j F Y') }}</span>
                                <span class="aktivitas-time-badge" id="jam-realtime">--:-- WIB</span>
                            </div>

                            {{-- Keterangan --}}
                            <div class="mb-4">
                                <label for="keterangan" class="aktivitas-label">Keterangan <span class="text-red-500">*</span></label>
                                <textarea name="keterangan" id="keterangan" rows="4" class="aktivitas-textarea" placeholder="Keterangan singkat aktivitas Anda" required>{{ old('keterangan') }}</textarea>
                                @error('keterangan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Kamera --}}
                            <div class="mb-4">
                                <label class="aktivitas-label">Foto Aktivitas <span class="text-red-500">*</span></label>
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

                                <div id="photo-success-msg" class="hidden text-center text-xs text-green-600 font-bold p-2 bg-green-50 rounded-lg">
                                    <i class="fas fa-check-circle"></i> Foto berhasil diambil.
                                </div>
                                @error('lampiran') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" id="submit-button" class="submit-aktivitas-btn">
                                Buat Aktivitas
                            </button>
                        </div>
                    </form>
                </div>

                {{-- KOLOM KANAN (TAB NAVIGASI) --}}
                <div class="lg:col-span-1">
                    <div class="aktivitas-card">
                        
                        {{-- Tab Switcher --}}
                        <div class="tab-switcher">
                            <button type="button" id="tab-riwayat-btn" class="tab-btn active">
                                Riwayat Saya
                            </button>
                            <button type="button" id="tab-rekan-btn" class="tab-btn">
                                Aktivitas Rekan
                            </button>
                        </div>

                        {{-- VIEW 1: RIWAYAT SAYA --}}
                        <div id="view-riwayat" class="space-y-3">
                            @if(isset($aktivitasHariIni) && $aktivitasHariIni->count() > 0)
                                @foreach($aktivitasHariIni as $event)
                                    <div class="log-item-card">
                                        <p class="log-item-desc">{{ $event->keterangan ?? '' }}</p>
                                        <div class="log-item-footer">
                                            <span class="log-item-time">
                                                <i class="fas fa-clock text-blue-400"></i>
                                                {{ $event->created_at->format('H:i') }}
                                            </span>
                                            <div class="log-item-link-row">
                                                @if($event->photo_url)
                                                    <a href="{{ $event->photo_url }}" class="log-item-link" target="_blank">Foto</a>
                                                @endif
                                                @if($event->latitude && $event->longitude)
                                                    <a href="https://www.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}" target="_blank" class="log-item-link">Lokasi</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="empty-log-state">
                                    <i class="fas fa-calendar-check text-3xl text-gray-300 mb-2 block"></i>
                                    <p class="text-gray-400 text-xs font-semibold">Belum ada aktivitas hari ini.</p>
                                </div>
                            @endif
                        </div>

                        {{-- VIEW 2: AKTIVITAS REKAN --}}
                        <div id="view-rekan" class="hidden">
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
                                    <i class="fas fa-users-slash text-3xl text-gray-300 mb-2 block"></i>
                                    <p class="text-gray-400 text-xs font-semibold">Tidak ada anggota tim.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL PANTAU TIM --}}
    <div id="modal-pantau" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div id="modal-pantau-backdrop" class="fixed inset-0"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full mx-4 max-h-[80vh] flex flex-col overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b bg-gray-50">
                <h3 class="text-base font-bold text-gray-800" id="modal-pantau-title">Memuat...</h3>
                <button type="button" id="modal-pantau-close-btn" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4 space-y-4 overflow-y-auto bg-gray-50 flex-grow custom-scrollbar" id="modal-pantau-content">
                {{-- Isi modal --}}
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
                        video: { facingMode: currentFacingMode, width: { ideal: 1280 }, height: { ideal: 720 } } 
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
            navigator.geolocation.getCurrentPosition(
                (pos) => { latitudeInput.value = pos.coords.latitude; longitudeInput.value = pos.coords.longitude; isLocationReady = true; checkFormReadiness(); },
                () => { alert('Gagal ambil lokasi.'); isLocationReady = false; checkFormReadiness(); },
                { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
            );
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
            modalPantauContent.innerHTML = `<div class="text-center text-gray-500 py-10"><i class="fas fa-spinner fa-spin text-2xl"></i><p class="mt-2">Memuat...</p></div>`;
            openModalPantau();
 
            try {
                const response = await fetch(`{{ route('aktivitas.getJson') }}?user_id=${userId}`);
                if (!response.ok) throw new Error('Gagal memuat');
                const data = await response.json();
 
                if (data && data.length > 0) {
                    let htmlContent = '<div class="space-y-4">';
                    data.forEach(item => {
                        const time = new Date(item.start).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        const photo = item.extendedProps.photo_url ? `<a href="${item.extendedProps.photo_url}" class="text-blue-500 text-xs font-semibold" target="_blank">Foto</a>` : '';
                        const loc = (item.extendedProps.latitude) ? `<a href="https://www.google.com/maps?q=${item.extendedProps.latitude},${item.extendedProps.longitude}" target="_blank" class="text-blue-500 text-xs font-semibold">Lokasi</a>` : '';
                        
                        htmlContent += `
                            <div class="tim-detail-card">
                                <p class="font-medium text-gray-800 text-sm">${item.title}</p>
                                <p class="text-gray-500 text-xs mt-1 leading-relaxed">${item.extendedProps.keterangan || ''}</p>
                                <div class="flex justify-between items-center mt-2.5 pt-2 border-t border-gray-100">
                                    <span class="text-[11px] font-bold text-gray-400"><i class="fas fa-clock"></i> ${time} WIB</span>
                                    <div class="space-x-3">${photo} ${loc}</div>
                                </div>
                            </div>
                        `;
                    });
                    htmlContent += '</div>';
                    modalPantauContent.innerHTML = htmlContent;
                } else {
                    modalPantauContent.innerHTML = `<div class="text-center py-10 text-gray-400"><i class="fas fa-calendar-times text-4xl mb-2"></i><p class="text-sm font-semibold">Belum ada aktivitas hari ini.</p></div>`;
                }
            } catch (error) {
                modalPantauContent.innerHTML = `<div class="text-center text-red-500 py-5 text-sm">Gagal memuat data.</div>`;
            }
        };
 
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.tim-card-button');
            if(btn) {
                fetchAktivitasTim(btn.dataset.userid, btn.dataset.username);
            }
        });
 
    });
    </script>
    @endpush
</x-layout-users>