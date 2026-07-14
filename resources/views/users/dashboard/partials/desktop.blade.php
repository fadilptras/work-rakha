<div class="hidden sm:block">
    <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-6">

        {{-- KOLOM PROFIL: Sangat Compact, Hanya Foto, Nama, dan Divisi --}}
        <div class="lg:col-span-1 flex flex-col gap-6 h-full">
            <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white p-6 rounded-3xl shadow-xl shadow-blue-600/30 relative overflow-hidden shrink-0">
                {{-- Dekorasi Latar Card --}}
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl transform translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 opacity-10 transform translate-x-1/4 translate-y-1/4">
                    <i class="fas fa-sun text-8xl"></i>
                </div>
                
                <div class="relative z-10">
                    @php
                        $hour = date('H');
                        $greeting = 'Selamat Pagi';
                        if ($hour >= 12 && $hour < 15) $greeting = 'Selamat Siang';
                        elseif ($hour >= 15 && $hour < 18) $greeting = 'Selamat Sore';
                        elseif ($hour >= 18) $greeting = 'Selamat Malam';
                        
                        // Mengambil nama depan saja
                        $firstName = explode(' ', Auth::user()->name)[0];
                    @endphp
                    
                    <p class="text-xs font-semibold text-blue-200 tracking-wider uppercase mb-2">
                        <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                    </p>
                    <h2 class="text-2xl font-extrabold mb-3 leading-tight">
                        {{ $greeting }},<br> {{ $firstName }}! 👋
                    </h2>
                    <p class="text-sm text-blue-100 font-medium mt-1">
                        Semoga harimu menyenangkan dan produktif!
                    </p>
                </div>
            </div>
            
            <div class="bg-white/60 backdrop-blur-lg border border-white/30 shadow-xl shadow-blue-500/20 p-6 rounded-3xl flex-1 flex flex-col">
                {{-- Bagian Header Profil (Horizontal) --}}
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-16 h-16 shrink-0 aspect-square overflow-hidden rounded-full border-2 border-white/50 shadow-sm">
                        <img class="w-full h-full object-cover" src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=random&color=fff&size=128' }}" alt="Foto Profil">
                    </div>
                    <div class="overflow-hidden">
                        <p class="font-bold text-lg text-gray-800 leading-tight truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs font-semibold text-blue-600 mt-0.5 truncate">{{ Auth::user()->jabatan ?? 'Karyawan' }}</p>
                    </div>
                </div>

                {{-- Tambahan Info Pribadi (Memperpanjang Card) --}}
                <div class="space-y-5 pt-5 border-t border-blue-200/50 flex-1">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-100/70 flex items-center justify-center shrink-0 text-blue-600">
                            <i class="fas fa-briefcase text-sm"></i>
                        </div>
                        <div class="overflow-hidden mt-0.5">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Divisi</p>
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->divisi ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-100/70 flex items-center justify-center shrink-0 text-indigo-600">
                            <i class="fas fa-id-card text-sm"></i>
                        </div>
                        <div class="overflow-hidden mt-0.5">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">NIP</p>
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->nip ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-sky-100/70 flex items-center justify-center shrink-0 text-sky-600">
                            <i class="fas fa-envelope text-sm"></i>
                        </div>
                        <div class="overflow-hidden mt-0.5">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Email</p>
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->email ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-100/70 flex items-center justify-center shrink-0 text-purple-600">
                            <i class="fas fa-phone text-sm"></i>
                        </div>
                        <div class="overflow-hidden mt-0.5">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">No. Telepon</p>
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->nomor_telepon ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-green-100/70 flex items-center justify-center shrink-0 text-green-600">
                            <i class="fas fa-map-marker-alt text-sm"></i>
                        </div>
                        <div class="overflow-hidden mt-0.5">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Lokasi Kerja</p>
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->lokasi_kerja ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-orange-100/70 flex items-center justify-center shrink-0 text-orange-600">
                            <i class="fas fa-user-tag text-sm"></i>
                        </div>
                        <div class="overflow-hidden mt-0.5">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Status Karyawan</p>
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->status_karyawan ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-rose-100/70 flex items-center justify-center shrink-0 text-rose-600">
                            <i class="fas fa-calendar-check text-sm"></i>
                        </div>
                        <div class="overflow-hidden mt-0.5">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Tanggal Bergabung</p>
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->tanggal_bergabung ? Auth::user()->tanggal_bergabung->format('d M Y') : '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 xl:col-span-3 space-y-6 flex flex-col h-full">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white/60 backdrop-blur-lg border border-white/30 shadow-xl shadow-blue-500/20 p-6 rounded-2xl flex flex-col">
                    <h3 class="font-bold text-gray-900 mb-8 text-xl">Absensi</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <a href="{{ route('absen') }}" class="bg-white/80 hover:bg-white p-4 rounded-xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 border border-blue-200 hover:border-blue-400 hover:shadow-lg hover:-translate-y-1"><i class="fas fa-fingerprint text-2xl text-blue-600 mb-2"></i><span class="font-semibold text-sm text-gray-700">Absen</span></a>
                            <a href="{{ route('aktivitas.index') }}" class="bg-white/80 hover:bg-white p-4 rounded-xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 border border-purple-200 hover:border-purple-400 hover:shadow-lg hover:-translate-y-1"><i class="fas fa-tasks text-2xl text-purple-600 mb-2"></i><span class="font-semibold text-sm text-gray-700">Aktivitas</span></a>
                            <a href="{{ route('cuti.create') }}" class="bg-white/80 hover:bg-white p-4 rounded-xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 border border-green-200 hover:border-green-400 hover:shadow-lg hover:-translate-y-1"><i class="fas fa-calendar-alt text-2xl text-green-600 mb-2"></i><span class="font-semibold text-sm text-gray-700">Cuti</span></a>
                            <a href="{{ route('rekap_absen.index') }}" class="bg-white/80 hover:bg-white p-4 rounded-xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 border border-yellow-200 hover:border-yellow-400 hover:shadow-lg hover:-translate-y-1"><i class="fas fa-history text-2xl text-yellow-600 mb-2"></i><span class="font-semibold text-sm text-gray-700">Rekap</span></a>
                        </div>
                </div>
                <div class="bg-gradient-to-br from-gray-900 to-slate-800 text-white p-6 rounded-2xl shadow-xl shadow-slate-900/40 border border-slate-700 flex flex-col">
                    <div class="flex justify-between items-center mb-4 flex-shrink-0">
                        <h3 class="font-bold text-white text-xl">Notifikasi</h3>
                        <a href="{{ route('notifikasi.index') }}" class="relative flex items-center space-x-2 text-gray-300 hover:text-white transition-colors duration-200">
                            <span class="text-sm font-semibold">Lihat Semua</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                            @if (Auth::user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center h-4 w-4 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>
                    </div>
                    <div class="space-y-3 flex-grow flex flex-col justify-center">
                        @forelse(Auth::user()->notifications->take(2) as $notification)
                        <a href="{{ $notification->data['url'] ?? '#' }}" class="block p-3 rounded-lg {{ $notification->read_at ? 'bg-gray-800/50' : 'bg-blue-800' }} hover:bg-gray-700/70 transition-colors duration-150">
                            <div class="flex items-start">
                                <i class="fas {{ $notification->data['icon'] ?? 'fa-info-circle' }} text-xl text-white mt-1 mr-3"></i>
                                <div>
                                    <p class="font-semibold text-sm text-gray-100">{{ $notification->data['title'] ?? 'Notifikasi Baru' }}</p>
                                    <p class="text-xs text-gray-300 line-clamp-1">{{ $notification->data['message'] ?? 'Tidak ada detail' }}</p>
                                </div>
                            </div>
                        </a>
                        @empty
                        <div class="flex-grow flex items-center justify-center"><p class="text-center text-gray-400 py-4 text-sm">Tidak ada notifikasi baru.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white/60 backdrop-blur-lg border border-white/30 shadow-xl shadow-blue-500/20 md:p-6 rounded-2xl">
                <div class="flex flex-col md:flex-row gap-4 md:gap-8">
                    <div class="w-full lg:w-3/5">
                        <div id="mini-calendar"></div>
                    </div>
                    <div class="hidden lg:block w-1 bg-blue-200"></div>
                    <div class="w-full lg:w-2/5 flex flex-col px-4 pb-4 md:px-0 md:pb-0">
                        <div class="flex justify-between items-center mb-4 flex-shrink-0">
                            <h3 id="agenda-list-title" class="font-bold text-gray-900 text-lg">Agenda Minggu Ini</h3>
                            <button id="add-agenda-btn" class="bg-gray-900 hover:bg-gray-800 text-white font-bold w-10 h-10 rounded-full transition-all duration-200 flex items-center justify-center shadow-md hover:scale-105">
                                <i class="fas fa-plus text-sm"></i>
                            </button>
                        </div>
                        <div id="agenda-list-container" class="h-80 overflow-y-auto pr-2 space-y-3 -mr-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
