<x-layout-users :title="$title">

    <div class="min-h-screen bg-gradient-to-br from-sky-50 to-blue-100 font-sans text-sm pb-20">
        
        <div class="max-w-5xl mx-auto pt-0 px-0 md:px-0">

            {{-- TOMBOL KEMBALI --}}
            <div class="mb-6">
                <a href="{{ route('dashboard') }}" 
                class="inline-flex items-center justify-center w-auto h-10 px-4 rounded-lg bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-md hover:shadow-lg hover:brightness-110 transition-all gap-2"
                title="Kembali ke Dashboard">
                    <i class="fas fa-arrow-left"></i>
                    <span class="font-medium text-sm">Kembali</span>
                </a>
            </div>

            {{-- HEADER STYLE --}}
            <div class="bg-[#001BB7] rounded-3xl shadow-xl shadow-blue-900/20 mb-8 overflow-hidden relative border border-blue-900/10">
                
                {{-- Dekorasi Background --}}
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl pointer-events-none z-0"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-blue-400 opacity-20 rounded-full blur-2xl pointer-events-none z-0"></div>

                <div class="p-8 relative z-10 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-white/20 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-white/30 shadow-sm">
                                    Pusat Informasi
                                </span>
                            </div>
                            <h1 class="text-3xl font-extrabold tracking-tight text-white drop-shadow-sm mb-2">
                                Notifikasi Anda
                            </h1>
                            <p class="text-blue-100 opacity-90 text-sm max-w-xl leading-relaxed">
                                Pantau aktivitas terbaru, pengumuman, dan pembaruan sistem di sini.
                            </p>
                        </div>
                        
                        {{-- Icon Lonceng Besar --}}
                        <div class="hidden md:block bg-white/10 p-4 rounded-2xl border border-white/10 backdrop-blur-sm">
                            <i class="fas fa-bell text-4xl text-blue-200"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BAGIAN FILTER --}}
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wide mr-1">Filter:</span>
                
                {{-- Tombol Semua --}}
                <a href="{{ route('notifikasi.index') }}"
                   class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-200 shadow-sm border
                          {{ $filterType === 'semua' 
                             ? 'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-200' 
                             : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                    Semua
                </a>

                {{-- Tombol Tipe Lainnya --}}
                @foreach ($availableTypes as $type)
                    <a href="{{ route('notifikasi.index', ['type' => $type]) }}"
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-200 shadow-sm border capitalize
                              {{ $filterType === $type 
                                 ? 'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-200' 
                                 : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                        {{ $type }}
                    </a>
                @endforeach
            </div>

            {{-- CONTAINER LIST NOTIFIKASI --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden relative min-h-[300px]">
                
                @forelse ($groupOrder as $groupName)
                    @if (isset($groupedNotifications[$groupName]) && $groupedNotifications[$groupName]->isNotEmpty())

                        {{-- Header Grup Waktu --}}
                        <div class="px-6 py-3 bg-gray-50/80 border-b border-gray-100 sticky top-0 backdrop-blur-sm z-10 flex items-center">
                            <i class="far fa-folder-open text-gray-400 mr-2 text-xs"></i>
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $groupName }}</h3>
                        </div>

                        <div class="divide-y divide-gray-50">
                            @foreach ($groupedNotifications[$groupName] as $notification)
                                @php
                                    $isUnread = !$notification->read_at;
                                    // Tentukan warna icon background
                                    $iconBg = 'bg-blue-100 text-blue-600';
                                    if(str_contains(strtolower($notification->data['title'] ?? ''), 'error') || str_contains(strtolower($notification->data['title'] ?? ''), 'gagal')) {
                                        $iconBg = 'bg-red-100 text-red-600';
                                    } elseif(str_contains(strtolower($notification->data['title'] ?? ''), 'sukses') || str_contains(strtolower($notification->data['title'] ?? ''), 'berhasil')) {
                                        $iconBg = 'bg-emerald-100 text-emerald-600';
                                    } elseif(str_contains(strtolower($notification->data['title'] ?? ''), 'warning') || str_contains(strtolower($notification->data['title'] ?? ''), 'peringatan')) {
                                        $iconBg = 'bg-orange-100 text-orange-600';
                                    }
                                @endphp

                                <a href="{{ $notification->data['url'] ?? '#' }}" class="group block p-5 transition-all duration-200 hover:bg-blue-50/40 relative {{ $isUnread ? 'bg-blue-50/20' : '' }}">
                                    
                                    {{-- Indikator Unread (Garis Kiri) --}}
                                    @if ($isUnread)
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 rounded-r"></div>
                                    @endif

                                    <div class="flex items-start gap-4">
                                        {{-- Icon Box --}}
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-xl {{ $iconBg }} flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                                <i class="{{ $notification->data['icon'] ?? 'fas fa-info' }} text-lg"></i>
                                            </div>
                                        </div>

                                        {{-- Konten Text --}}
                                        <div class="flex-1 min-w-0 pt-0.5">
                                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2 sm:gap-4">
                                                <h4 class="text-sm font-bold text-gray-800 group-hover:text-blue-700 transition-colors leading-tight">
                                                    {{ $notification->data['title'] ?? 'Notifikasi Baru' }}
                                                </h4>
                                                
                                                {{-- WAKTU & TANGGAL (FIXED: Hari, Tanggal, Jam) --}}
                                                <span class="text-[10px] font-medium text-gray-500 whitespace-nowrap bg-gray-50 px-2 py-1 rounded-md border border-gray-200 flex items-center gap-1.5 w-fit">
                                                    <i class="far fa-clock text-blue-500"></i>
                                                    {{ \Carbon\Carbon::parse($notification->created_at)->translatedFormat('l, d F Y - H:i') }} WIB
                                                </span>
                                            </div>
                                            
                                            <p class="text-sm text-gray-600 mt-2 line-clamp-2 leading-relaxed">
                                                {{ $notification->data['message'] ?? '-' }}
                                            </p>
                                        </div>

                                        {{-- Indikator Dot Unread (Kanan) --}}
                                        @if ($isUnread)
                                            <div class="flex-shrink-0 self-center hidden sm:block">
                                                <span class="block w-2.5 h-2.5 bg-blue-500 rounded-full shadow ring-2 ring-blue-50" title="Belum dibaca"></span>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @empty
                    {{-- Loop kosong --}}
                @endforelse

                {{-- STATE KOSONG --}}
                @if($groupedNotifications->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 shadow-inner">
                            <i class="fas fa-bell-slash text-3xl text-gray-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-700">Tidak ada notifikasi</h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-xs mx-auto">
                            @if($filterType !== 'semua')
                                Tidak ada notifikasi untuk kategori "<span class="font-bold text-gray-600">{{ $filterType }}</span>".
                            @else
                                Anda sudah melihat semua pembaruan terbaru.
                            @endif
                        </p>
                        
                        @if($filterType !== 'semua')
                            <a href="{{ route('notifikasi.index') }}" class="mt-6 px-6 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-800 transition shadow-sm">
                                Reset Filter
                            </a>
                        @endif
                    </div>
                @endif

            </div>
            
            {{-- Footer info kecil --}}
            <div class="mt-6 text-center">
                <p class="text-xs text-blue-300/80">
                    Menampilkan notifikasi 30 hari terakhir.
                </p>
            </div>

        </div>
    </div>
</x-layout-users>