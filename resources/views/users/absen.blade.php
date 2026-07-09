<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="bg-gradient-to-br from-sky-50 to-blue-100 p-0 md:p-0 min-h-screen">
        <div class="max-w-6xl mx-auto">
            
            {{-- ALERT MESSAGES --}}
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-4" role="alert">
                    <p class="font-bold">Terjadi Kesalahan</p>
                    <ul>
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif
            
            {{-- KONDISI 1: LIBUR TOTAL (Minggu / Tanggal Merah) --}}
            @if($isHoliday)
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl flex items-start gap-4 shadow-sm animate-fade-in-down">
                    <div class="p-3 bg-red-500/20 rounded-full text-red-500 shrink-0">
                        <i class="fas fa-calendar-times text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-red-600 text-lg">Hari Libur</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 mt-1">
                            Hari ini adalah 
                            @if($holidayDb)
                                <span class="font-semibold text-red-600">{{ $holidayDb->keterangan }}</span>.
                            @else
                                <span class="font-semibold text-red-600">Hari Minggu</span>.
                            @endif
                            Absensi tetap dibuka khusus untuk petugas piket atau lembur.
                        </p>
                    </div>
                </div>

            {{-- KONDISI 2: SABTU OPSIONAL --}}
            @elseif($isSaturdayOpen)
                <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl flex items-start gap-4 shadow-sm animate-fade-in-down">
                    <div class="p-3 bg-blue-500/20 rounded-full text-blue-600 shrink-0">
                        <i class="fas fa-umbrella-beach text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-blue-600 text-lg">Hari Sabtu (Opsional)</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 mt-1">
                            Kehadiran hari ini bersifat opsional. Tidak tercatat sebagai Alpha jika tidak hadir.
                        </p>
                    </div>
                </div>
            @endif

            {{-- TOMBOL KEMBALI KE DASHBOARD (POSISI: ATAS KIRI) --}}
            <div class="mb-4">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
                </a>
            </div>

            @if ($absensiHariIni)
                {{-- ========================================================= --}}
                {{-- KONDISI 1: SUDAH ABSEN MASUK --}}
                {{-- ========================================================= --}}
                <div class="flex flex-col lg:flex-row gap-6 items-start">
                    <div class="w-full lg:w-2/3 bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex flex-col md:flex-row items-start justify-between px-0">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Absensi Hari Ini</h2>
                                <p class="text-gray-600 mt-1">
                                    Status Kehadiran Anda : <span class="font-semibold capitalize">{{ $absensiHariIni->status }}</span>
                                </p>
                                <p class="text-gray-600 mt-0">
                                    Keterangan : <span class="font-semibold capitalize">{{ $absensiHariIni->keterangan }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-6 border-t pt-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
                                    <div class="flex items-center text-emerald-800 mb-2">
                                        <i class="fas fa-sign-in-alt mr-2"></i>
                                        <p class="font-semibold">Absen Masuk</p>
                                    </div>
                                    <p class="text-3xl font-bold text-gray-800">{{ \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') }} <span class="text-lg font-medium">WIB</span></p>
                                    @if($absensiHariIni->latitude && $absensiHariIni->longitude)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $absensiHariIni->latitude }},{{ $absensiHariIni->longitude }}" target="_blank" class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                                            <i class="fas fa-map-marker-alt mr-1"></i>Lihat Lokasi
                                        </a>
                                    @endif
                                </div>
                                <div class="bg-rose-50 p-4 rounded-lg border border-rose-200 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center text-rose-800 mb-2">
                                            <i class="fas fa-sign-out-alt mr-2"></i>
                                            <p class="font-semibold">Absen Keluar</p>
                                        </div>
                                        @if ($absensiHariIni->jam_keluar)
                                            <p class="text-3xl font-bold text-gray-800">{{ \Carbon\Carbon::parse($absensiHariIni->jam_keluar)->format('H:i') }} <span class="text-lg font-medium">WIB</span></p>
                                            @if($absensiHariIni->latitude_keluar && $absensiHariIni->longitude_keluar)
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $absensiHariIni->latitude_keluar }},{{ $absensiHariIni->longitude_keluar }}" target="_blank" class="text-xs text-blue-600 hover:underline mt-1 inline-block">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>Lihat Lokasi
                                                </a>
                                            @endif
                                        @else
                                            <p class="text-3xl font-bold text-gray-400">--:--</p>
                                        @endif
                                    </div>
                                    @if (is_null($absensiHariIni->jam_keluar) && $absensiHariIni->status == 'hadir')
                                        <button type="button" id="btn-absen-keluar" class="w-full mt-3 bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition text-sm">
                                            Absen Keluar Sekarang
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @if ($absensiHariIni->jam_keluar && $absensiHariIni->status == 'hadir')
                                @if (is_null($lemburHariIni))
                                    <button type="button" id="btn-absen-lembur" class="w-full mt-6 bg-purple-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-purple-700 transition">
                                        Absen Lembur Sekarang
                                    </button>
                                @elseif (is_null($lemburHariIni->jam_keluar_lembur))
                                    <button type="button" id="btn-absen-keluar-lembur" class="w-full mt-6 bg-red-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-red-700 transition">
                                        Absen Keluar Lembur Sekarang
                                    </button>
                                @else
                                     <div class="mt-6 p-4 rounded-lg bg-green-100 text-green-700 font-semibold text-center">
                                         Absensi Lembur Hari Ini Selesai.
                                         <p class="text-xs font-normal">Waktu Lembur: {{ \Carbon\Carbon::parse($lemburHariIni->jam_masuk_lembur)->format('H:i') }} - {{ \Carbon\Carbon::parse($lemburHariIni->jam_keluar_lembur)->format('H:i') }}</p>
                                     </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    {{-- SIDEBAR REKAP --}}
                    <div class="w-full lg:w-1/3 space-y-6">
                        <div class="bg-white p-4 rounded-xl shadow-sm">
                            <h2 class="text-lg font-bold text-gray-800 text-center mb-1">Rekap Bulan Ini</h2>
                            <p class="text-center text-gray-500 text-xs mb-3">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <div class="bg-green-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                    <p class="font-semibold text-xs text-green-700">Hadir</p>
                                    <div class="text-sm font-bold text-green-700">{{ $rekapAbsen['hadir'] }}</div>
                                </div>
                                <div class="bg-red-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                    <p class="font-semibold text-xs text-red-700">Sakit</p>
                                    <div class="text-sm font-bold text-red-700">{{ $rekapAbsen['sakit'] }}</div>
                                </div>
                                <div class="bg-yellow-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                    <p class="font-semibold text-xs text-yellow-700">Izin</p>
                                    <div class="text-sm font-bold text-yellow-700">{{ $rekapAbsen['izin'] }}</div>
                                </div>
                                <div class="bg-blue-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                    <p class="font-semibold text-xs text-blue-700">Cuti</p>
                                    <div class="text-sm font-bold text-blue-700">{{ $rekapAbsen['cuti'] }}</div>
                                </div>
                                <div class="bg-gray-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                    <p class="font-semibold text-xs text-gray-700">Alpa</p>
                                    <div class="text-sm font-bold text-gray-700">{{ $rekapAbsen['tidak hadir'] }}</div>
                                </div>
                                <div class="bg-indigo-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                    <p class="font-semibold text-xs text-indigo-700">Lembur</p>
                                    <div class="text-sm font-bold text-indigo-700">{{ $rekapAbsen['lembur'] }}</div>
                                </div>
                                <div class="bg-orange-100 px-3 py-2 rounded-lg flex items-center justify-between col-span-2 md:col-span-3">
                                    <p class="font-semibold text-xs text-orange-700">Terlambat</p>
                                    <div class="text-sm font-bold text-orange-700">{{ $rekapAbsen['terlambat'] }}</div>
                                </div>
                            </div>
                        </div>

                        @if(isset($daftarRekan) && count($daftarRekan) > 0)
                        <div class="bg-white p-4 rounded-xl shadow-sm">
                            <h2 class="text-lg font-bold text-gray-800 text-center mb-3">
                                Absensi Tim 
                            </h2>
                            <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                @foreach($daftarRekan as $rekan)
                                <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                    <div class="flex items-center">
                                        <img src="{{ $rekan->user->profile_picture ? asset('storage/' . $rekan->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($rekan->user->name ?? 'U').'&background=random&color=fff&size=32' }}" alt="{{ $rekan->user->name ?? '' }}" class="w-8 h-8 rounded-full object-cover mr-3">
                                        <p class="font-semibold text-gray-700 text-sm">{{ $rekan->user->name }}</p>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs font-semibold leading-tight rounded-full capitalize
                                        @switch($rekan->status)
                                            @case('hadir') bg-green-100 text-green-800 @break
                                            @case('sakit') bg-red-100 text-red-800 @break
                                            @case('izin') bg-yellow-100 text-yellow-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
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
                    <div class="flex flex-col lg:flex-row gap-6 items-start">
                        <div class="w-full lg:w-2/3 bg-white p-6 md:p-8 rounded-xl shadow-sm">
                            <h2 class="text-2xl font-bold text-gray-800">Selesaikan Absen Keluar Sebelumnya</h2>
                            <p class="text-gray-600 mt-2">
                                Anda belum melakukan absen keluar untuk tanggal <strong>{{ \Carbon\Carbon::parse($unfinishedAbsensi->tanggal)->translatedFormat('l, j F Y') }}</strong>.
                                Silakan lengkapi data absensi Anda untuk melanjutkan.
                            </p>
                            <div class="mt-6 border-t pt-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
                                        <div class="flex items-center text-emerald-800 mb-2">
                                            <i class="fas fa-sign-in-alt mr-2"></i>
                                            <p class="font-semibold">Absen Masuk (Hari Sebelumnya)</p>
                                        </div>
                                        <p class="text-3xl font-bold text-gray-800">{{ \Carbon\Carbon::parse($unfinishedAbsensi->jam_masuk)->format('H:i') }} <span class="text-lg font-medium">WIB</span></p>
                                    </div>
                                    <div class="bg-rose-50 p-4 rounded-lg border border-rose-200 flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-center text-rose-800 mb-2">
                                                <i class="fas fa-sign-out-alt mr-2"></i>
                                                <p class="font-semibold">Absen Keluar (Sekarang)</p>
                                            </div>
                                            <p class="text-3xl font-bold text-gray-400">--:--</p>
                                        </div>
                                        <button type="button" id="btn-absen-keluar-unfinished" data-id="{{ $unfinishedAbsensi->id }}" class="w-full mt-3 bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition text-sm">
                                            Absen Keluar Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- SIDEBAR UNTUK UNFINISHED --}}
                        <div class="w-full lg:w-1/3 space-y-6">
                            <div class="bg-white p-4 rounded-xl shadow-sm">
                                <h2 class="text-lg font-bold text-gray-800 text-center mb-1">Rekap Bulan Ini</h2>
                                <p class="text-center text-gray-500 text-xs mb-3">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    <div class="bg-green-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-green-700">Hadir</p>
                                        <div class="text-sm font-bold text-green-700">{{ $rekapAbsen['hadir'] }}</div>
                                    </div>
                                    <div class="bg-red-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-red-700">Sakit</p>
                                        <div class="text-sm font-bold text-red-700">{{ $rekapAbsen['sakit'] }}</div>
                                    </div>
                                    <div class="bg-yellow-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-yellow-700">Izin</p>
                                        <div class="text-sm font-bold text-yellow-700">{{ $rekapAbsen['izin'] }}</div>
                                    </div>
                                    <div class="bg-blue-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-blue-700">Cuti</p>
                                        <div class="text-sm font-bold text-blue-700">{{ $rekapAbsen['cuti'] }}</div>
                                    </div>
                                    <div class="bg-gray-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-gray-700">Alpa</p>
                                        <div class="text-sm font-bold text-gray-700">{{ $rekapAbsen['tidak hadir'] }}</div>
                                    </div>
                                    <div class="bg-indigo-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-indigo-700">Lembur</p>
                                        <div class="text-sm font-bold text-indigo-700">{{ $rekapAbsen['lembur'] }}</div>
                                    </div>
                                    <div class="bg-orange-100 px-3 py-2 rounded-lg flex items-center justify-between col-span-2 md:col-span-3">
                                        <p class="font-semibold text-xs text-orange-700">Terlambat</p>
                                        <div class="text-sm font-bold text-orange-700">{{ $rekapAbsen['terlambat'] }}</div>
                                    </div>
                                </div>
                            </div>
                            @if(isset($daftarRekan) && count($daftarRekan) > 0)
                            <div class="bg-white p-4 rounded-xl shadow-sm">
                                <h2 class="text-lg font-bold text-gray-800 text-center mb-3">
                                    Absensi Tim 
                                </h2>
                                <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                    @foreach($daftarRekan as $rekan)
                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                        <div class="flex items-center">
                                            <img src="{{ $rekan->user->profile_picture ? asset('storage/' . $rekan->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($rekan->user->name ?? 'U').'&background=random&color=fff&size=32' }}" alt="{{ $rekan->user->name ?? '' }}" class="w-8 h-8 rounded-full object-cover mr-3">
                                            <p class="font-semibold text-gray-700 text-sm">{{ $rekan->user->name }}</p>
                                        </div>
                                        <span class="px-2 py-0.5 text-xs font-semibold leading-tight rounded-full capitalize
                                            @switch($rekan->status)
                                                @case('hadir') bg-green-100 text-green-800 @break
                                                @case('sakit') bg-red-100 text-red-800 @break
                                                @case('izin') bg-yellow-100 text-yellow-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch">
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
                    <div id="modal-absen-keluar-unfinished" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
                        {{-- Lebar modal ditingkatkan --}}
                        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl transform transition-all duration-300 scale-95 opacity-0">
                            <form action="{{ route('absen.keluar', $unfinishedAbsensi->id) }}" method="POST" enctype="multipart/form-data" id="form-absen-keluar-unfinished">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="latitude_keluar" id="latitude-keluar-unfinished">
                                <input type="hidden" name="longitude_keluar" id="longitude-keluar-unfinished">
                                <div class="p-5">
                                    <h3 class="text-xl font-bold text-gray-800">Form Absen Keluar</h3>
                                    <p class="text-gray-500 text-sm mt-1">Ambil foto selfie untuk konfirmasi absen keluar hari sebelumnya.</p>
                                    
                                    {{-- Layout Grid 2 Kolom --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                        {{-- Kolom Kiri: Kamera SQUARE --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Selfie <span class="text-red-500">*</span></label>
                                            {{-- Aspect-square (1:1) --}}
                                            <div id="camera-container-keluar-unfinished" class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 shadow-inner">
                                                <video id="video-keluar-unfinished" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                                <canvas id="canvas-keluar-unfinished" class="hidden"></canvas>
                                                <div id="snap-ui-keluar-unfinished" class="absolute inset-0 flex items-end justify-center p-4 bg-black bg-opacity-25">
                                                    <button type="button" id="snap-keluar-unfinished" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400" disabled>
                                                        <i class="fas fa-camera"></i>
                                                    </button>
                                                </div>
                                                <div id="preview-ui-keluar-unfinished" class="absolute inset-0 hidden">
                                                    <img id="preview-image-keluar-unfinished" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-black bg-opacity-40">
                                                        <button type="button" id="retake-btn-keluar-unfinished" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                                        <button type="button" id="use-photo-btn-keluar-unfinished" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-check mr-1.5"></i>Gunakan</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="upload-label-keluar-unfinished" class="hidden">
                                                <input name="lampiran_keluar" id="lampiran-keluar-unfinished" type="file" class="hidden" accept="image/*" />
                                            </div>
                                        </div>

                                        {{-- Kolom Kanan: Pesan --}}
                                        <div class="flex flex-col justify-center text-gray-500 text-sm italic">
                                            <p>Mohon pastikan wajah terlihat jelas.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-3 rounded-b-xl">
                                    <button type="button" id="btn-tutup-modal-keluar-unfinished" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 text-sm">Batal</button>
                                    <button type="submit" id="submit-button-keluar-unfinished" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 text-sm">Kirim</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- ========================================================= --}}
                    {{-- KONDISI 3: BELUM ABSEN (MAIN FORM - MODIFIKASI MOBILE) --}}
                    {{-- ========================================================= --}}
                    <div class="flex flex-col lg:flex-row gap-6 items-start">
                        
                        @if ($isHoliday)
                            {{-- TAMPILAN KHUSUS WEEKEND (BELUM ABSEN LEMBUR) --}}
                            <div class="w-full lg:w-2/3 bg-white p-6 md:p-8 rounded-xl shadow-sm">
                                <h2 class="text-2xl font-bold text-gray-800">Absensi Lembur Akhir Pekan</h2>
                                <p class="text-gray-600 mt-1">
                                    Notifikasi akhir pekan Anda tetap muncul. Anda dapat melakukan absensi lembur di bawah ini.
                                </p>
                                <div class="mt-6 border-t pt-6">
                                    @if (is_null($lemburHariIni))
                                        <button type="button" id="btn-absen-lembur" class="w-full bg-purple-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-purple-700 transition">
                                            Absen Lembur Sekarang
                                        </button>
                                    @elseif (is_null($lemburHariIni->jam_keluar_lembur))
                                        <div class="mb-4 bg-purple-50 p-4 rounded-lg border border-purple-200">
                                            <p class="font-semibold text-lg text-purple-800">Anda Sudah Masuk Lembur</p>
                                            <p class="text-3xl font-bold text-gray-800 mt-1">{{ \Carbon\Carbon::parse($lemburHariIni->jam_masuk_lembur)->format('H:i') }} <span class="text-lg font-medium">WIB</span></p>
                                            @if($lemburHariIni->keterangan)
                                                <p class="text-gray-600 text-sm mt-1">Keterangan: {{ $lemburHariIni->keterangan }}</p>
                                            @endif
                                        </div>
                                        <button type="button" id="btn-absen-keluar-lembur" class="w-full bg-red-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-red-700 transition">
                                            Absen Keluar Lembur Sekarang
                                        </button>
                                    @else
                                        <div class="p-4 rounded-lg bg-green-100 text-green-700 font-semibold text-center">
                                            Absensi Lembur Hari Ini Selesai.
                                            <p class="text-xs font-normal">Waktu Lembur: {{ \Carbon\Carbon::parse($lemburHariIni->jam_masuk_lembur)->format('H:i') }} - {{ \Carbon\Carbon::parse($lemburHariIni->jam_keluar_lembur)->format('H:i') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- TAMPILAN HARI BIASA (BELUM ABSEN) --}}
                            {{-- GANTI FORM INI (Cari: <form action="{{ route('absen.store') }}" ... id="form-absen" ...) --}}
        <form action="{{ route('absen.store') }}" method="POST" enctype="multipart/form-data" id="form-absen" class="w-full lg:w-2/3">
            @csrf
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            {{-- KONTAINER UTAMA (CARD PUTIH) --}}
            <div class="bg-white p-6 rounded-xl shadow-sm space-y-4 min-h-[500px] flex flex-col justify-between">
                
                {{-- WRAPPER KONTEN ATAS --}}
                <div>
                    {{-- 1. TANGGAL & JAM (Tampilan Mobile Sebaris) --}}
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div class="bg-gray-100 p-2 rounded-lg">
                            <label class="text-xs text-gray-500">Hari & Tanggal</label>
                            <p class="font-bold text-sm text-gray-800">{{ \Carbon\Carbon::now()->translatedFormat('l, j F Y') }}</p>
                        </div>
                        <div class="bg-gray-100 p-2 rounded-lg">
                            <label class="text-xs text-gray-500">Jam Saat Ini</label>
                            <p class="font-bold text-sm text-gray-800" id="jam-realtime"></p>
                        </div>
                    </div>

                    {{-- 2. STATUS KEHADIRAN --}}
                    <div class="mb-6"> 
                        <label class="block text-md font-medium text-gray-700 mb-3">Pilih Status Kehadiran</label>
                        <input type="hidden" name="status" id="status" value="hadir">
                        <div class="grid grid-cols-3 md:grid-cols-3 gap-3 md:gap-4 mt-2" id="status-buttons">
                            <button type="button" data-status="hadir" class="status-btn border border-gray-200 font-semibold py-2 rounded-lg transition-all duration-200 text-sm shadow-sm hover:shadow-md">Hadir</button>
                            <button type="button" data-status="izin" class="status-btn border border-gray-200 font-semibold py-2 rounded-lg transition-all duration-200 text-sm shadow-sm hover:shadow-md">Izin</button>
                            <button type="button" data-status="sakit" class="status-btn border border-gray-200 font-semibold py-2 rounded-lg transition-all duration-200 text-sm shadow-sm hover:shadow-md">Sakit</button>
                        </div>
                    </div>
                    
                    {{-- 3. KETERANGAN & KAMERA --}}
                    <div>
                        <label for="keterangan" class="block text-md font-medium text-gray-700 mb-3">
                            Keterangan & Lampiran <span id="keterangan-wajib" class="text-red-500 font-normal hidden">*</span>
                        </label>
                        
                        {{-- GRID: Kamera (Kiri) & Keterangan (Kanan) --}}
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            
                            {{-- KOLOM KIRI (2 Bagian): KAMERA --}}
                            <div class="md:col-span-2">
                                <div id="camera-container" class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 shadow-md">
                                    <video id="video" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                    <canvas id="canvas" class="hidden"></canvas>
                                    <div id="snap-ui" class="absolute inset-0 flex items-end justify-center p-4 bg-gradient-to-t from-black/50 to-transparent">
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

                                <label for="lampiran" id="upload-label" class="flex flex-col items-center justify-center w-full h-full border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition min-h-[250px] hidden relative">
                                    <div class="flex flex-col items-center justify-center text-center p-2" id="upload-ui">
                                        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-2">
                                            <i id="upload-icon" class="fas fa-paperclip text-xl"></i>
                                        </div>
                                        <p id="upload-text" class="mt-2 text-sm text-gray-500"><span class="font-semibold">Sertakan Lampiran</span></p>
                                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF</p>
                                    </div>
                                    <input name="lampiran" id="lampiran" type="file" class="hidden" accept="image/*,application/pdf" />
                                </label>
                            </div>

                            {{-- KOLOM KANAN (3 Bagian): KETERANGAN --}}
                            <div class="md:col-span-3">
                                <textarea name="keterangan" id="keterangan" rows="1" class="w-full p-3 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition-all text-sm h-full" placeholder="Contoh: Ada keperluan keluarga.">{{ old('keterangan') }}</textarea>
                            </div>
                            
                        </div>
                    </div>
                </div>

                {{-- 4. FOOTER: TOMBOL KIRIM --}}
                <div class="pt-2 mt-0">
                    <button type="submit" id="submit-button" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed text-sm flex items-center justify-center gap-1">
                        <i class="fas fa-paper-plane"></i> Kirim Absensi
                    </button>
                </div>

            </div>
        </form>                        
        @endif {{-- End of $isWeekend check --}}
                        
                        {{-- SIDEBAR REKAP UNTUK BELUM ABSEN --}}
                        <div class="w-full lg:w-1/3 space-y-6">
                            <div class="bg-white p-4 rounded-xl shadow-sm relative">
                                <h2 class="text-lg font-bold text-gray-800 text-center mb-1">Rekap Bulan Ini</h2>
                                <p class="text-center text-gray-500 text-xs mb-3">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    <div class="bg-green-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-green-700">Hadir</p>
                                        <div class="text-sm font-bold text-green-700">{{ $rekapAbsen['hadir'] }}</div>
                                    </div>
                                    <div class="bg-red-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-red-700">Sakit</p>
                                        <div class="text-sm font-bold text-red-700">{{ $rekapAbsen['sakit'] }}</div>
                                    </div>
                                    <div class="bg-yellow-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-yellow-700">Izin</p>
                                        <div class="text-sm font-bold text-yellow-700">{{ $rekapAbsen['izin'] }}</div>
                                    </div>
                                    <div class="bg-blue-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-blue-700">Cuti</p>
                                        <div class="text-sm font-bold text-blue-700">{{ $rekapAbsen['cuti'] }}</div>
                                    </div>
                                    <div class="bg-gray-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-gray-700">Alpa</p>
                                        <div class="text-sm font-bold text-gray-700">{{ $rekapAbsen['tidak hadir'] }}</div>
                                    </div>
                                    <div class="bg-indigo-100 px-3 py-2 rounded-lg flex items-center justify-between">
                                        <p class="font-semibold text-xs text-indigo-700">Lembur</p>
                                        <div class="text-sm font-bold text-indigo-700">{{ $rekapAbsen['lembur'] }}</div>
                                    </div>
                                    <div class="bg-orange-100 px-3 py-2 rounded-lg flex items-center justify-between col-span-2 md:col-span-3">
                                        <p class="font-semibold text-xs text-orange-700">Terlambat</p>
                                        <div class="text-sm font-bold text-orange-700">{{ $rekapAbsen['terlambat'] }}</div>
                                    </div>
                                </div>
                            </div>
                            @if(isset($daftarRekan) && count($daftarRekan) > 0)
                                <div class="bg-white p-4 rounded-xl shadow-sm">
                                    <h2 class="text-lg font-bold text-gray-800 text-center mb-3">
                                        Absensi Tim 
                                    </h2>
                                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                                        @foreach($daftarRekan as $rekan)
                                        <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
                                            <div class="flex items-center">
                                                <img src="{{ $rekan->user->profile_picture ? asset('storage/' . $rekan->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($rekan->user->name ?? 'U').'&background=random&color=fff&size=32' }}" alt="{{ $rekan->user->name ?? '' }}" class="w-8 h-8 rounded-full object-cover mr-3">
                                                <p class="font-semibold text-gray-700 text-sm">{{ $rekan->user->name }}</p>
                                            </div>
                                            <span class="px-2 py-0.5 text-xs font-semibold leading-tight rounded-full capitalize
                                                @switch($rekan->status)
                                                    @case('hadir') bg-green-100 text-green-800 @break
                                                    @case('sakit') bg-red-100 text-red-800 @break
                                                    @case('izin') bg-yellow-100 text-yellow-800 @break
                                                    @default bg-gray-100 text-gray-800
                                                @endswitch">
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
    <div id="modal-absen-keluar" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        {{-- Lebar modal ditingkatkan --}}
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl transform transition-all duration-300 scale-95 opacity-0">
            <form action="{{ route('absen.keluar', $absensiHariIni->id) }}" method="POST" enctype="multipart/form-data" id="form-absen-keluar">
                @csrf
                @method('PATCH')
                <input type="hidden" name="latitude_keluar" id="latitude-keluar">
                <input type="hidden" name="longitude_keluar" id="longitude-keluar">
                <div class="p-5">
                    <h3 class="text-xl font-bold text-gray-800">Form Absen Keluar</h3>
                    <p class="text-gray-500 text-sm mt-1">Ambil foto selfie untuk konfirmasi absen keluar.</p>
                    
                    {{-- Grid 2 Kolom --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        {{-- Kiri: Kamera SQUARE --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Selfie <span class="text-red-500">*</span></label>
                            {{-- Aspect-square (1:1) --}}
                            <div id="camera-container-keluar" class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 shadow-inner">
                                <video id="video-keluar" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                <canvas id="canvas-keluar" class="hidden"></canvas>
                                <div id="snap-ui-keluar" class="absolute inset-0 flex items-end justify-center p-4 bg-black bg-opacity-25">
                                    <button type="button" id="snap-keluar" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400" disabled>
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div id="preview-ui-keluar" class="absolute inset-0 hidden">
                                    <img id="preview-image-keluar" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-black bg-opacity-40">
                                        <button type="button" id="retake-btn-keluar" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                        <button type="button" id="use-photo-btn-keluar" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-check mr-1.5"></i>Gunakan</button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload-label-keluar" class="hidden">
                                <input name="lampiran_keluar" id="lampiran-keluar" type="file" class="hidden" accept="image/*" />
                            </div>
                        </div>
                        
                        {{-- Kanan: Info --}}
                        <div class="flex flex-col justify-center text-gray-500 text-sm italic">
                             <p>Terima kasih atas kerja keras Anda hari ini. Hati-hati di jalan!</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-3 rounded-b-xl">
                    <button type="button" id="btn-tutup-modal-keluar" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 text-sm">Batal</button>
                    <button type="submit" id="submit-button-keluar" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 text-sm">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    
    {{-- MODAL UNTUK ABSEN LEMBUR (LAYOUT: KIRI KAMERA SQUARE, KANAN TEXTAREA) --}}
    @if ( ($absensiHariIni && $absensiHariIni->jam_keluar && $absensiHariIni->status == 'hadir') || $isHoliday )
    <div id="modal-absen-lembur" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        {{-- Lebar modal ditingkatkan --}}
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl transform transition-all duration-300 scale-95 opacity-0">
            <form action="{{ route('absen.lembur.store') }}" method="POST" enctype="multipart/form-data" id="form-absen-lembur">
                @csrf
                <input type="hidden" name="latitude_masuk" id="latitude-lembur">
                <input type="hidden" name="longitude_masuk" id="longitude-lembur">
                <div class="p-5">
                    <h3 class="text-xl font-bold text-gray-800">Form Absen Lembur</h3>
                    <p class="text-gray-500 text-sm mt-1">Ambil foto selfie dan isi keterangan untuk memulai lembur.</p>
                    
                    {{-- Grid 2 Kolom --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        {{-- Kiri: Kamera SQUARE --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Selfie <span class="text-red-500">*</span></label>
                            {{-- Aspect-square (1:1) --}}
                            <div id="camera-container-lembur" class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 shadow-inner">
                                <video id="video-lembur" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                <canvas id="canvas-lembur" class="hidden"></canvas>
                                <div id="snap-ui-lembur" class="absolute inset-0 flex items-end justify-center p-4 bg-black bg-opacity-25">
                                    <button type="button" id="snap-lembur" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400" disabled>
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div id="preview-ui-lembur" class="absolute inset-0 hidden">
                                    <img id="preview-image-lembur" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-black bg-opacity-40">
                                        <button type="button" id="retake-btn-lembur" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                        <button type="button" id="use-photo-btn-lembur" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-check mr-1.5"></i>Gunakan</button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload-label-lembur" class="hidden">
                                <input name="lampiran_masuk" id="lampiran-lembur" type="file" class="hidden" accept="image/*" />
                            </div>
                        </div>

                        {{-- Kanan: Textarea --}}
                        <div class="flex flex-col">
                            <label for="keterangan-lembur" class="block text-sm font-medium text-gray-700 mb-2">Keterangan Lembur <span class="text-red-500">*</span></label>
                            {{-- Tinggi textarea menyesuaikan container --}}
                            <textarea id="keterangan-lembur" name="keterangan" class="w-full p-3 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 transition-all text-sm h-full" placeholder="Contoh: Menyelesaikan laporan bulanan." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-3 rounded-b-xl">
                    <button type="button" id="btn-tutup-modal-lembur" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 text-sm">Batal</button>
                    <button type="submit" id="submit-button-lembur" class="bg-purple-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-purple-700 disabled:bg-gray-400 text-sm">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL UNTUK ABSEN KELUAR LEMBUR (LAYOUT: KIRI KAMERA SQUARE, KANAN INFO) --}}
    @if ($lemburHariIni && is_null($lemburHariIni->jam_keluar_lembur))
    <div id="modal-keluar-lembur" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        {{-- Lebar modal ditingkatkan --}}
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl transform transition-all duration-300 scale-95 opacity-0">
            <form action="{{ route('absen.lembur.keluar', $lemburHariIni->id) }}" method="POST" enctype="multipart/form-data" id="form-keluar-lembur">
                @csrf
                @method('PATCH')
                <input type="hidden" name="latitude_keluar" id="latitude-keluar-lembur">
                <input type="hidden" name="longitude_keluar" id="longitude-keluar-lembur">
                <div class="p-5">
                    <h3 class="text-xl font-bold text-gray-800">Form Absen Keluar Lembur</h3>
                    <p class="text-gray-500 text-sm mt-1">Ambil foto selfie untuk konfirmasi selesai lembur.</p>
                    
                    {{-- Grid 2 Kolom --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                         {{-- Kiri: Kamera SQUARE --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Selfie <span class="text-red-500">*</span></label>
                            {{-- Aspect-square (1:1) --}}
                            <div id="camera-container-keluar-lembur" class="relative aspect-square rounded-lg overflow-hidden bg-gray-900 shadow-inner">
                                <video id="video-keluar-lembur" class="w-full h-full object-cover" style="transform: scaleX(-1);" autoplay></video>
                                <canvas id="canvas-keluar-lembur" class="hidden"></canvas>
                                <div id="snap-ui-keluar-lembur" class="absolute inset-0 flex items-end justify-center p-4 bg-black bg-opacity-25">
                                    <button type="button" id="snap-keluar-lembur" class="bg-blue-600 text-white rounded-full h-12 w-12 flex items-center justify-center text-xl border-4 border-white shadow-lg disabled:bg-gray-400" disabled>
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                <div id="preview-ui-keluar-lembur" class="absolute inset-0 hidden">
                                    <img id="preview-image-keluar-lembur" src="" class="w-full h-full object-cover" alt="Pratinjau Foto"/>
                                    <div class="absolute inset-0 flex items-end justify-center p-4 gap-3 bg-black bg-opacity-40">
                                        <button type="button" id="retake-btn-keluar-lembur" class="bg-red-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-sync-alt mr-1.5"></i>Ulang</button>
                                        <button type="button" id="use-photo-btn-keluar-lembur" class="bg-green-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center"><i class="fas fa-check mr-1.5"></i>Gunakan</button>
                                    </div>
                                </div>
                            </div>
                            <div id="upload-label-keluar-lembur" class="hidden">
                                <input name="lampiran_keluar" id="lampiran-keluar-lembur" type="file" class="hidden" accept="image/*" />
                            </div>
                        </div>

                        {{-- Kanan: Info --}}
                        <div class="flex flex-col justify-center text-gray-500 text-sm italic">
                             <p>Terima kasih sudah lembur hari ini. Selamat beristirahat.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3 flex justify-end gap-3 rounded-b-xl">
                    <button type="button" id="btn-tutup-modal-keluar-lembur" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 text-sm">Batal</button>
                    <button type="submit" id="submit-button-keluar-lembur" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 disabled:bg-gray-400 text-sm">Kirim</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
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
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
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
                    successMsg.className = 'success-message mt-2 text-center text-xs text-green-600 font-semibold p-1 bg-green-50 rounded-lg';
                    successMsg.innerHTML = `<i class="fas fa-check-circle"></i> Foto berhasil.`;
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
                hadir: 'bg-emerald-500 text-white border-emerald-500',
                izin: 'bg-amber-500 text-white border-amber-500',
                sakit: 'bg-red-500 text-white border-red-500'
            };

            const setActiveButton = (status) => {
                document.querySelectorAll('.status-btn').forEach(btn => {
                    btn.classList.remove(...Object.values(selectedStyles).join(' ').split(' '));
                    btn.classList.add('border-gray-300', 'bg-white', 'text-gray-700');
                });
                const activeButton = document.querySelector(`.status-btn[data-status="${status}"]`);
                if (activeButton) {
                    activeButton.classList.remove('border-gray-300', 'bg-white', 'text-gray-700');
                    activeButton.classList.add(...selectedStyles[status].split(' '));
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
                    successMsg.className = 'success-message mt-2 text-center text-sm text-green-600 font-semibold p-2 bg-green-50 rounded-lg';
                    successMsg.innerHTML = `<i class="fas fa-check-circle"></i> Foto berhasil diambil.`;
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