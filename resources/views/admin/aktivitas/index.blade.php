<x-layout-admin>
    <x-slot:title>Aktivitas Karyawan</x-slot:title>

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Aktivitas Harian Karyawan</h1>
    </div>

    {{-- FILTER & ACTIONS --}}
    <div class="my-6 p-4 bg-zinc-800 rounded-lg shadow-md border border-zinc-700">
        <form method="GET" action="{{ route('admin.aktivitas.index') }}" id="filter-form">
            <div class="flex flex-wrap items-end gap-4 w-full">
                
                {{-- Filter Tanggal Mulai --}}
                <div class="flex-1 min-w-[130px] max-w-[200px]">
                    <label for="start_date" class="block text-sm font-medium text-zinc-300 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" 
                           value="{{ $startDate }}" 
                           class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm [color-scheme:dark]">
                </div>

                {{-- Filter Tanggal Selesai --}}
                <div class="flex-1 min-w-[130px] max-w-[200px]">
                    <label for="end_date" class="block text-sm font-medium text-zinc-300 mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" 
                           value="{{ $endDate }}" 
                           class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm [color-scheme:dark]">
                </div>

                {{-- Filter Divisi --}}
                <div class="flex-1 min-w-[150px] max-w-[250px]">
                    <label for="divisi" class="block text-sm font-medium text-zinc-300 mb-1">Divisi</label>
                    <div class="relative">
                        <select name="divisi" id="divisi" 
                                class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
                            <option value="">Semua Divisi</option>
                            @foreach ($divisions as $d)
                                <option value="{{ $d }}" {{ $divisi == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Filter Karyawan (User) --}}
                <div class="flex-1 min-w-[150px] max-w-[250px]">
                    <label for="user_id" class="block text-sm font-medium text-zinc-300 mb-1">Karyawan</label>
                    <div class="relative">
                        <select name="user_id" id="user_id" 
                                class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
                            <option value="">Semua Karyawan</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex flex-wrap items-end gap-2 flex-none ml-auto">
                    <button type="submit" 
                            class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>

                    <a href="{{ route('admin.aktivitas.index') }}" 
                       class="bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-colors">
                       Reset
                    </a>

                    <div class="w-px h-8 bg-zinc-600 mx-1 hidden sm:block"></div>

                    <a href="{{ route('admin.aktivitas.downloadPdf', request()->query()) }}" 
                       class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105" 
                       title="Download PDF">
                        <i class="fas fa-file-pdf mr-2"></i> PDF
                    </a>

                    <a href="{{ route('admin.aktivitas.downloadExcel', request()->query()) }}" 
                       class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105" 
                       title="Download Excel">
                        <i class="fas fa-file-excel mr-2"></i> Excel
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- TABEL DATA --}}
    <div class="overflow-x-auto bg-zinc-800 rounded-lg shadow-lg border border-zinc-700 mt-6">
        <table class="min-w-full text-sm text-left text-zinc-300">
            <thead class="bg-zinc-700 text-xs uppercase font-semibold text-zinc-200">
                <tr>
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">Keterangan</th>
                    <th class="px-4 py-3">Lampiran & Lokasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-700">
                
                @forelse ($aktivitasHarian as $aktivitas)
                    <tr class="hover:bg-zinc-700/30 transition-colors">
                        
                        {{-- Karyawan --}}
                        <td class="px-4 py-3 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border-2 border-zinc-600 shadow-md">
                                <img src="{{ $aktivitas->user->profile_picture ? asset('storage/' . $aktivitas->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($aktivitas->user->name ?? 'U').'&background=0284c7&color=e0f2fe' }}"
                                     alt="{{ $aktivitas->user->name ?? 'User' }}" class="w-full h-full object-cover" loading="lazy">
                            </div>
                            <div>
                                <p class="font-semibold text-white">{{ $aktivitas->user->name ?? 'User Dihapus' }}</p>
                                <p class="text-xs text-zinc-400">{{ $aktivitas->user->divisi ?? '-' }}</p>
                            </div>
                        </td>

                        {{-- Waktu (UPDATED: Menampilkan Tanggal juga karena range filter) --}}
                        <td class="px-4 py-3">
                            <div class="text-white font-medium">{{ $aktivitas->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-sky-400 font-mono mt-1">
                                <i class="far fa-clock mr-1"></i> {{ $aktivitas->created_at->format('H:i') }} WIB
                            </div>
                        </td>

                        {{-- Keterangan --}}
                        <td class="px-4 py-3">
                            <p class="text-white font-semibold">{{ $aktivitas->title ?? 'Judul tidak ada' }}</p>
                            <p class="text-xs text-zinc-400 mt-1">{{ $aktivitas->keterangan ?? '-' }}</p>
                        </td>

                        {{-- Lampiran & Lokasi --}}
                        <td class="px-4 py-3 space-y-1">
                            @php $hasLink = false; @endphp

                            @if ($aktivitas->lampiran)
                                <a href="{{ asset('storage/' . $aktivitas->lampiran) }}" target="_blank"
                                   class="text-sky-400 hover:text-sky-300 underline text-xs font-medium flex items-center gap-1">
                                    <i class="fas fa-camera"></i> Lihat Foto
                                </a>
                                @php $hasLink = true; @endphp
                            @endif

                            @if ($aktivitas->latitude && $aktivitas->longitude)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $aktivitas->latitude }},{{ $aktivitas->longitude }}" target="_blank"
                                   class="text-sky-400 hover:text-sky-300 underline text-xs font-medium flex items-center gap-1 mt-1">
                                   <i class="fas fa-map-marker-alt"></i> Lihat Lokasi
                                </a>
                                @php $hasLink = true; @endphp
                            @endif

                            @if (!$hasLink)
                                <span class="text-zinc-500 text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center justify-center text-zinc-400">
                                <i class="fas fa-inbox text-4xl mb-3 text-zinc-600"></i>
                                <p>Tidak ada data aktivitas yang cocok dengan filter.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginasi --}}
    @if ($aktivitasHarian->hasPages())
        <div class="mt-6">
            {{ $aktivitasHarian->links() }}
        </div>
    @endif
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const divisiSelect = document.getElementById('divisi');
            const userSelect = document.getElementById('user_id');
            const form = document.getElementById('filter-form');
    
            divisiSelect.addEventListener('change', function() {
                form.submit();
            });
            
            userSelect.addEventListener('change', function() {
                form.submit();
            });
        });
    </script>
    @endpush
</x-layout-admin>