<div>
    <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-6">

        {{-- KOLOM PROFIL: Sangat Compact, Hanya Foto, Nama, dan Divisi --}}
        <div class="lg:col-span-1 flex flex-col gap-6 h-full">
            <div class="bg-gradient-to-br from-[#001BB7] via-blue-700 to-indigo-900 text-white p-6 rounded-3xl shadow-xl shadow-blue-900/10 relative overflow-hidden shrink-0">
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
            
            <div class="bg-white/80 backdrop-blur-md border border-white shadow-xl shadow-blue-900/5 p-5 rounded-3xl flex-1 flex flex-col h-[390px]">
                {{-- Bagian Header Profil (Horizontal) --}}
                <div class="bg-gradient-to-r from-blue-500/10 to-[#001BB7]/10 rounded-2xl p-3 flex items-center gap-4 mb-3 border border-white/50 shadow-sm">
                    <div class="w-16 h-16 shrink-0 aspect-square overflow-hidden rounded-full border-2 border-white shadow-md">
                        @php
                            $words = explode(' ', Auth::user()->name);
                            $initials = '';
                            if (count($words) >= 2) {
                                $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                            } else {
                                $initials = strtoupper(substr(Auth::user()->name, 0, 2));
                            }
                        @endphp
                        @if(Auth::user()->profile_picture)
                            <img class="w-full h-full object-cover" src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Foto Profil">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-[#001BB7] to-blue-500 flex items-center justify-center text-white font-extrabold text-lg shadow-inner">
                                {{ $initials }}
                            </div>
                        @endif
                    </div>
                    <div class="overflow-hidden">
                        <p class="font-extrabold text-lg text-gray-800 leading-tight truncate">{{ Auth::user()->name }}</p>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#001BB7]/10 text-[#001BB7] mt-1.5 border border-[#001BB7]/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#001BB7] animate-pulse"></span>
                            {{ Auth::user()->jabatan ?? 'Karyawan' }}
                        </span>
                    </div>
                </div>

                {{-- Tambahan Info Pribadi (Memperpanjang Card) --}}
                <div class="flex-grow flex flex-col justify-between pt-3 border-t border-blue-100/50">
                    
                    {{-- Divisi --}}
                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-white/40 border border-white/50 shadow-sm hover:bg-white/85 hover:border-white transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-blue-100/70 flex items-center justify-center shrink-0 text-blue-600">
                                <i class="fas fa-briefcase text-xs"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-500">Divisi</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800 text-right max-w-[60%] truncate" title="{{ Auth::user()->divisi ?? '-' }}">{{ Auth::user()->divisi ?? '-' }}</span>
                    </div>

                    {{-- NIP --}}
                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-white/40 border border-white/50 shadow-sm hover:bg-white/85 hover:border-white transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-100/70 flex items-center justify-center shrink-0 text-indigo-600">
                                <i class="fas fa-id-card text-xs"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-500">NIP</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800 text-right max-w-[60%] truncate" title="{{ Auth::user()->nip ?? '-' }}">{{ Auth::user()->nip ?? '-' }}</span>
                    </div>

                    {{-- Email --}}
                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-white/40 border border-white/50 shadow-sm hover:bg-white/85 hover:border-white transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-sky-100/70 flex items-center justify-center shrink-0 text-sky-600">
                                <i class="fas fa-envelope text-xs"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-500">Email</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800 text-right max-w-[60%] truncate" title="{{ Auth::user()->email ?? '-' }}">{{ Auth::user()->email ?? '-' }}</span>
                    </div>

                    {{-- No Telepon --}}
                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-white/40 border border-white/50 shadow-sm hover:bg-white/85 hover:border-white transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-purple-100/70 flex items-center justify-center shrink-0 text-purple-600">
                                <i class="fas fa-phone text-xs"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-500">No. Telepon</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800 text-right max-w-[60%] truncate" title="{{ Auth::user()->nomor_telepon ?? '-' }}">{{ Auth::user()->nomor_telepon ?? '-' }}</span>
                    </div>

                    {{-- Lokasi Kerja --}}
                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-white/40 border border-white/50 shadow-sm hover:bg-white/85 hover:border-white transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-green-100/70 flex items-center justify-center shrink-0 text-green-600">
                                <i class="fas fa-map-marker-alt text-xs"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-500">Lokasi Kerja</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800 text-right max-w-[60%] truncate" title="{{ Auth::user()->lokasi_kerja ?? '-' }}">{{ Auth::user()->lokasi_kerja ?? '-' }}</span>
                    </div>

                    {{-- Status Karyawan --}}
                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-white/40 border border-white/50 shadow-sm hover:bg-white/85 hover:border-white transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-orange-100/70 flex items-center justify-center shrink-0 text-orange-600">
                                <i class="fas fa-user-tag text-xs"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-500">Status Karyawan</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800 text-right max-w-[60%] truncate" title="{{ Auth::user()->status_karyawan ?? '-' }}">{{ Auth::user()->status_karyawan ?? '-' }}</span>
                    </div>

                    {{-- Tanggal Bergabung --}}
                    <div class="flex items-center justify-between py-1.5 px-3 rounded-xl bg-white/40 border border-white/50 shadow-sm hover:bg-white/85 hover:border-white transition-all duration-300 hover:shadow-md">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-rose-100/70 flex items-center justify-center shrink-0 text-rose-600">
                                <i class="fas fa-calendar-check text-xs"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-500">Mulai Kerja</span>
                        </div>
                        <span class="text-xs font-bold text-gray-800 text-right max-w-[60%] truncate" title="{{ Auth::user()->tanggal_bergabung ? Auth::user()->tanggal_bergabung->format('d M Y') : '-' }}">{{ Auth::user()->tanggal_bergabung ? Auth::user()->tanggal_bergabung->format('d M Y') : '-' }}</span>
                    </div>

                </div>
            </div>
            </div>

        <div class="lg:col-span-2 xl:col-span-3 space-y-6 flex flex-col h-full">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white/80 backdrop-blur-md border border-white shadow-xl shadow-blue-900/5 p-6 rounded-3xl flex flex-col justify-between">
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-xl">Absensi</h3>
                        <p class="text-sm text-gray-500 mt-1">Pilih salah satu pintasan di bawah ini untuk mencatat kehadiran, aktivitas harian, mengajukan cuti, atau melihat rekap absensi.</p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                            <a href="{{ route('absen') }}" class="btn-absen-soft p-4 rounded-3xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 hover:scale-105 hover:-translate-y-1">
                                <i class="fas fa-fingerprint text-3xl mb-3 text-current"></i>
                                <span class="font-extrabold text-sm tracking-wide text-current">Absen</span>
                            </a>
                            <a href="{{ route('aktivitas.index') }}" class="btn-aktivitas-soft p-4 rounded-3xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 hover:scale-105 hover:-translate-y-1">
                                <i class="fas fa-tasks text-3xl mb-3 text-current"></i>
                                <span class="font-extrabold text-sm tracking-wide text-current">Aktivitas</span>
                            </a>
                            <a href="{{ route('cuti.create') }}" class="btn-cuti-soft p-4 rounded-3xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 hover:scale-105 hover:-translate-y-1">
                                <i class="fas fa-calendar-alt text-3xl mb-3 text-current"></i>
                                <span class="font-extrabold text-sm tracking-wide text-current">Cuti</span>
                            </a>
                            <a href="{{ route('rekap_absen.index') }}" class="btn-rekap-soft p-4 rounded-3xl text-center flex flex-col items-center justify-center aspect-square transition-all duration-300 hover:scale-105 hover:-translate-y-1">
                                <i class="fas fa-history text-3xl mb-3 text-current"></i>
                                <span class="font-extrabold text-sm tracking-wide text-current">Rekap</span>
                            </a>
                        </div>
                </div>
                <div class="card-navy-blue p-6 rounded-3xl flex flex-col">
                    <div class="flex justify-between items-center mb-4 flex-shrink-0">
                        <h3 class="font-extrabold text-white text-xl">Notifikasi</h3>
                        <a href="{{ route('notifikasi.index') }}" class="relative flex items-center space-x-2 text-blue-300 hover:text-blue-200 transition-colors duration-200">
                            <span class="text-sm font-semibold">Lihat Semua</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-300"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                            @if (Auth::user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center h-4 w-4 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>
                    </div>
                    <div class="space-y-3 flex-grow flex flex-col justify-center">
                        @forelse(Auth::user()->notifications->take(2) as $notification)
                        <a href="{{ $notification->data['url'] ?? '#' }}" class="block p-3 rounded-xl border {{ $notification->read_at ? 'bg-white/5 border-white/10 hover:bg-white/10 text-slate-300' : 'bg-white/10 border-white/20 hover:bg-white/15 text-white' }} transition-colors duration-150 shadow-sm">
                            <div class="flex items-start">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mr-3 {{ $notification->read_at ? 'bg-white/5 text-slate-400' : 'bg-blue-500/30 text-blue-200' }}">
                                    <i class="fas {{ $notification->data['icon'] ?? 'fa-info-circle' }} text-sm"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="font-bold text-sm leading-snug truncate {{ $notification->read_at ? 'text-slate-200' : 'text-white' }}">{{ $notification->data['title'] ?? 'Notifikasi Baru' }}</p>
                                    <p class="text-xs mt-0.5 truncate {{ $notification->read_at ? 'text-slate-400' : 'text-blue-100/90' }}">{{ $notification->data['message'] ?? 'Tidak ada detail' }}</p>
                                </div>
                            </div>
                        </a>
                        @empty
                        <div class="flex-grow flex items-center justify-center"><p class="text-center text-slate-400 py-4 text-sm font-semibold">Tidak ada notifikasi baru.</p></div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {{-- CARD KALENDER (Kiri - Lebar 3/5) --}}
                <div class="lg:col-span-3 card-pastel-green-cal p-4 rounded-3xl text-slate-800 flex flex-col h-[390px]">
                    <div id="mini-calendar" class="flex-grow h-full"></div>
                </div>

                {{-- CARD AGENDA (Kanan - Lebar 2/5) --}}
                <div class="lg:col-span-2 card-pastel-green-age p-5 rounded-3xl text-slate-800 flex flex-col h-[390px]">
                    <div class="flex justify-between items-center mb-4 flex-shrink-0">
                        <h3 id="agenda-list-title" class="font-extrabold text-emerald-950 text-lg">Agenda Minggu Ini</h3>
                        <button id="add-agenda-btn" class="bg-gradient-to-r from-emerald-600 to-green-600 hover:brightness-110 text-white font-bold w-10 h-10 rounded-full transition-all duration-200 flex items-center justify-center shadow-lg shadow-green-600/20 hover:scale-105">
                            <i class="fas fa-plus text-sm"></i>
                        </button>
                    </div>
                    <div id="agenda-list-container" class="flex-grow overflow-y-auto pr-2 space-y-3 -mr-2 h-0"></div>
                </div>
            </div>
        </div>
    </div>
</div>
