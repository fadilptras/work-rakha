<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    @push('styles')
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #93c5fd; border-radius: 10px; }
    </style>
    @endpush

    <div class="bg-gradient-to-br from-sky-50 to-blue-100 min-h-screen">
        
        {{-- Notifikasi --}}
        <div class="max-w-7xl mx-auto mb-4 px-0 md:px-0">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-4" role="alert">
                    <p class="font-bold">Terjadi Kesalahan</p>
                    <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif
        </div>
        
        {{-- GRID UTAMA --}}
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 px-0 md:px-0 pb-10">

            {{-- =============================================================== --}}
            {{-- KOLOM KIRI & TENGAH (FORM INPUT) --}}
            {{-- =============================================================== --}}
            <div class="lg:col-span-2">
                <form action="{{ route('aktivitas.store') }}" method="POST" enctype="multipart/form-data" id="form-aktivitas">
                    @csrf
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input name="lampiran" id="lampiran" type="file" class="hidden" accept="image/*" />

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        {{-- KOTAK 1: KETERANGAN --}}
                        <div class="lg:col-span-1 space-y-6">
                            {{-- PERUBAHAN 1: Padding diubah jadi p-4 (mobile) dan md:p-8 (desktop) agar lebih hemat tempat --}}
                            <div class="bg-white p-4 md:p-8 rounded-xl shadow-sm h-full flex flex-col"> 
                                
                                <div class="mb-4">
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-blue-600 transition-colors">
                                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
                                    </a>
                                </div>

                                <h2 class="text-2xl font-bold text-gray-800 mb-6">Catat Aktivitas</h2>
                                
                                <div class="space-y-4 md:space-y-6 flex-grow max-h-[70vh]"> 
                                    
                                    {{-- [PERUBAHAN] TAMPILAN WAKTU COMPACT DI MOBILE --}}
                                    <div class="bg-blue-100 p-3 md:p-4 rounded-lg">
                                        {{-- Label hanya muncul di Desktop --}}
                                        <label class="hidden md:block text-sm text-gray-500 mb-1">Waktu Saat Ini</label>
                                        
                                        {{-- Mobile: Flex Row (Satu Baris), Desktop: Block (Tumpuk) --}}
                                        <div class="flex md:block justify-between items-center">
                                            {{-- Tanggal --}}
                                            <p class="font-bold text-md md:text-lg text-gray-800">
                                                {{ \Carbon\Carbon::now()->translatedFormat('l, j F Y') }}
                                            </p>
                                            
                                            {{-- Jam --}}
                                            <p class="font-bold text-sm md:text-lg text-blue-600 md:text-gray-800 md:mt-1 bg-white md:bg-transparent px-2 py-1 md:p-0 rounded shadow-sm md:shadow-none" id="jam-realtime">
                                                </p>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan <span class="text-red-500">*</span></label>
                                        
                                        {{-- PERUBAHAN 2: Ditambahkan class h-16 (pendek di HP) dan md:h-auto (normal di Desktop) --}}
                                        <textarea name="keterangan" id="keterangan" rows="3" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 h-16 md:h-auto" placeholder="Keterangan singkat aktivitas Anda" required>{{ old('keterangan') }}</textarea>
                                        
                                        @error('keterangan') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KOTAK 2: KAMERA --}}
                        <div class="lg:col-span-1 space-y-4">
                            <label class="text-sm font-medium text-gray-700 mb-2">Foto Aktivitas <span class="text-red-500">*</span></label>
                            <div id="camera-container" class="relative aspect-square rounded-xl overflow-hidden bg-gray-900 shadow-sm">
                                <video id="video" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay playsinline muted></video>
                                <canvas id="canvas" class="hidden"></canvas>
                                
                                <div id="flip-camera-ui" class="absolute top-0 right-0 p-3 hidden z-10">
                                    <button type="button" id="flip-camera-btn" class="bg-gray-700 bg-opacity-50 text-white rounded-full h-10 w-10 flex items-center justify-center text-lg hover:bg-opacity-75 transition-all">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>

                                <div id="snap-ui" class="absolute bottom-0 left-0 right-0 flex justify-center p-4 bg-black bg-opacity-25 hidden">
                                    <button type="button" id="snap" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400" disabled>
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                
                                <div id="preview-ui" class="absolute inset-0 hidden">
                                    <img id="preview-image" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-black bg-opacity-40" id="preview-buttons">
                                        <button type="button" id="retake-btn" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                        <button type="button" id="use-photo-btn" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-check mr-1.5"></i>Gunakan</button>
                                    </div>
                                </div>
                            </div>
                            <div id="photo-success-msg" class="hidden text-center text-sm text-green-600 font-semibold p-2 bg-green-50 rounded-lg">
                                <i class="fas fa-check-circle"></i> Foto berhasil diambil.
                            </div>
                             @error('lampiran') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            
                            <div class="pt-2">
                                <button type="submit" id="submit-button" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition-all disabled:bg-gray-400 disabled:cursor-not-allowed">
                                    Buat Aktivitas
                                </button>
                            </div>
                        </div>
                    </div>
                </form> 
            </div>

            {{-- =============================================================== --}}
            {{-- KOLOM 3 (KANAN) - TAB NAVIGASI --}}
            {{-- =============================================================== --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 md:p-8 rounded-xl shadow-sm h-full flex flex-col">
                    
                    {{-- TOMBOL TAB --}}
                    <div class="flex p-1 bg-blue-100 rounded-lg mb-6">
                        <button type="button" id="tab-riwayat-btn" class="flex-1 py-2 px-4 text-sm font-semibold rounded-md shadow-sm bg-white text-blue-600 transition-all">
                            Riwayat Saya
                        </button>
                        <button type="button" id="tab-rekan-btn" class="flex-1 py-2 px-4 text-sm font-semibold rounded-md text-gray-500 hover:text-gray-700 transition-all">
                            Aktivitas Rekan
                        </button>
                    </div>
                    
                    {{-- KONTEN 1: RIWAYAT SAYA --}}
                    <div id="view-riwayat" class="flex-grow flex flex-col overflow-hidden transition-opacity duration-300">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex-shrink-0 hidden">Riwayat Hari Ini</h3>
                        <div class="overflow-y-auto pr-2 max-h-[70vh] flex-grow space-y-4">
                            @if(isset($aktivitasHariIni) && $aktivitasHariIni->count() > 0)
                                @foreach($aktivitasHariIni as $event)
                                <div class="p-4 rounded-xl bg-gradient-to-br from-sky-50 to-blue-100 border border-gray-200 shadow-sm">
                                    <div class="flex-grow min-w-0">
                                        <p class="text-sm text-gray-500 break-words">{{ $event->keterangan ?? '' }}</p>
                                    </div>
                                    <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                                        <span class="text-sm font-semibold text-gray-700">
                                            <i class="fas fa-clock mr-1.5 text-blue-500"></i>
                                            {{ $event->created_at->format('H:i') }}
                                        </span>
                                        <div class="flex space-x-4">
                                            @if($event->photo_url)
                                                <a href="{{ $event->photo_url }}" class="text-xs text-blue-500 hover:underline font-medium">Lihat Foto</a>
                                            @endif
                                            @if($event->latitude && $event->longitude)
                                                <a href="https://www.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}" target="_blank" class="text-xs text-blue-500 hover:underline font-medium">Lihat Lokasi</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="h-full flex flex-col items-center justify-center text-center text-gray-500 mt-10">
                                    <i class="fas fa-calendar-check text-4xl text-gray-300"></i>
                                    <p class="mt-4 font-semibold">Belum ada aktivitas</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- KONTEN 2: AKTIVITAS REKAN --}}
                    <div id="view-rekan" class="hidden flex-grow flex-col overflow-hidden transition-opacity duration-300">
                        @if(isset($timYangDipantau) && $timYangDipantau->count() > 0)
                            <div class="overflow-y-auto pr-2 max-h-[70vh] flex-grow">
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($timYangDipantau as $tim)
                                    <button type="button" 
                                            class="tim-card-button flex flex-col items-center justify-center text-center p-3 rounded-lg border border-gray-100 hover:bg-blue-50 hover:border-blue-200 transition-all group"
                                            data-userid="{{ $tim->id }}"
                                            data-username="{{ $tim->name }}">
                                        
                                        <div class="relative">
                                            @if($tim->profile_picture)
                                                <img src="{{ Storage::url($tim->profile_picture) }}" alt="{{ $tim->name }}" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm group-hover:scale-105 transition-transform">
                                            @else
                                                <span class="w-12 h-12 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-xl border-2 border-white shadow-sm group-hover:scale-105 transition-transform">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-2 text-xs font-semibold text-gray-700 group-hover:text-blue-700 truncate w-full">{{ $tim->name }}</p>
                                        <span class="text-[10px] text-gray-400">{{ $tim->jabatan ?? 'Staf' }}</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="h-full flex flex-col items-center justify-center text-center text-gray-500 mt-10">
                                <i class="fas fa-users-slash text-4xl text-gray-300"></i>
                                <p class="mt-4 font-semibold">Tidak ada anggota tim</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div> {{-- Penutup Grid Utama --}}

        {{-- =================================================================== --}}
        {{-- MODAL PANTAU TIM --}}
        {{-- =================================================================== --}}
        <div id="modal-pantau" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div id="modal-pantau-backdrop" class="fixed inset-0"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[80vh] flex flex-col animate-fade-in-up">
                <div class="flex justify-between items-center p-5 border-b rounded-t-xl bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800" id="modal-pantau-title">Memuat...</h3>
                    <button type="button" id="modal-pantau-close-btn" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-5 space-y-4 overflow-y-auto bg-gray-50" id="modal-pantau-content">
                    {{-- Isi modal --}}
                </div>
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
                tabRiwayat.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
                tabRiwayat.classList.remove('text-gray-500');
                tabRekan.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
                tabRekan.classList.add('text-gray-500');
                viewRiwayat.classList.remove('hidden');
                viewRekan.classList.add('hidden');
            });

            tabRekan.addEventListener('click', () => {
                tabRekan.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
                tabRekan.classList.remove('text-gray-500');
                tabRiwayat.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
                tabRiwayat.classList.add('text-gray-500');
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
                        const photo = item.extendedProps.photo_url ? `<a href="${item.extendedProps.photo_url}" class="text-blue-500 text-xs">Foto</a>` : '';
                        const loc = (item.extendedProps.latitude) ? `<a href="https://www.google.com/maps?q=${item.extendedProps.latitude},${item.extendedProps.longitude}" target="_blank" class="text-blue-500 text-xs">Lokasi</a>` : '';
                        
                        htmlContent += `
                            <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                                <p class="font-medium text-gray-800">${item.title}</p>
                                <p class="text-gray-500 text-sm mt-1">${item.extendedProps.keterangan || ''}</p>
                                <div class="flex justify-between items-center mt-2 pt-2 border-t border-gray-100">
                                    <span class="text-xs text-gray-500"><i class="fas fa-clock"></i> ${time}</span>
                                    <div class="space-x-2">${photo} ${loc}</div>
                                </div>
                            </div>
                        `;
                    });
                    htmlContent += '</div>';
                    modalPantauContent.innerHTML = htmlContent;
                } else {
                    modalPantauContent.innerHTML = `<div class="text-center py-10 text-gray-400"><i class="fas fa-calendar-times text-4xl mb-2"></i><p>Belum ada aktivitas hari ini.</p></div>`;
                }
            } catch (error) {
                modalPantauContent.innerHTML = `<div class="text-center text-red-500 py-5">Gagal memuat data.</div>`;
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