<x-layout-admin>
    <x-slot:title>Lembur</x-slot:title>

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-white">Lembur Harian Karyawan</h1>
        {{-- Tab Navigasi (Absensi / Lembur) --}}
        <div class="bg-zinc-800 p-1 rounded-lg inline-flex shadow-sm border border-zinc-700">
            <a href="{{ route('admin.absensi.index') }}" 
               class="px-4 py-2 rounded-md text-sm font-bold transition-all {{ Route::is('admin.absensi.index') ? 'bg-indigo-600 text-white shadow' : 'text-zinc-400 hover:text-white hover:bg-zinc-700' }}">
                <i class="fas fa-calendar-check mr-2"></i> Absensi
            </a>
            <a href="{{ route('admin.lembur.index') }}" 
               class="px-4 py-2 rounded-md text-sm font-bold transition-all {{ Route::is('admin.lembur.index') ? 'bg-indigo-600 text-white shadow' : 'text-zinc-400 hover:text-white hover:bg-zinc-700' }}">
                <i class="fas fa-clock mr-2"></i> Lembur
            </a>
        </div>
    </div>

    {{-- FILTER & ACTIONS SECTION --}}
    <div class="my-6 p-4 bg-zinc-800 rounded-lg shadow-md border border-zinc-700">
        <form method="GET" action="{{ route('admin.lembur.index') }}" id="filter-form">
            <div class="flex flex-wrap items-end gap-4">

                {{-- Filter Tanggal (Single Date) --}}
                <div class="w-full sm:w-auto">
                    <label for="tanggal" class="block text-sm font-medium text-zinc-300">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" 
                           value="{{ request('tanggal') }}" 
                           class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                {{-- Filter Divisi --}}
                <div class="w-full sm:w-auto">
                    <label for="divisi" class="block text-sm font-medium text-zinc-300">Divisi</label>
                    <select name="divisi" id="divisi" 
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm min-w-[150px]">
                        <option value="">Semua Divisi</option>
                        @foreach($divisions as $d)
                            <option value="{{ $d }}" {{ request('divisi') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Karyawan (User) --}}
                <div class="w-full sm:w-auto">
                    <label for="user_id" class="block text-sm font-medium text-zinc-300">Karyawan</label>
                    <select name="user_id" id="user_id" 
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm min-w-[200px]">
                        <option value="">Semua Karyawan</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ACTION BUTTONS (Filter, Reset, PDF) --}}
                <div class="flex items-end gap-2">
                    {{-- Tombol Filter --}}
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>

                    {{-- Tombol Reset --}}
                    <a href="{{ route('admin.lembur.index') }}" 
                       class="bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-colors">
                        Reset
                    </a>

                    {{-- Divider Kecil --}}
                    <div class="w-px h-8 bg-zinc-600 mx-1 hidden sm:block"></div>

                    {{-- Tombol Download PDF --}}
                    <a href="{{ route('admin.lembur.downloadPdf', request()->query()) }}" 
                       class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105" 
                       title="Download Laporan Lembur">
                        <i class="fas fa-file-pdf mr-2"></i> PDF
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- Tabel Rekap Lembur --}}
    <div class="overflow-x-auto bg-zinc-800 rounded-lg shadow-lg border border-zinc-700 mt-6">
        <table class="min-w-full text-sm text-left text-zinc-300">
            <thead class="bg-zinc-700 text-xs uppercase font-semibold text-zinc-200">
                <tr>
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Waktu Lembur</th>
                    <th class="px-4 py-3">Keterangan</th>
                    <th class="px-4 py-3">Lampiran & Lokasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-700">
                @forelse ($lemburRecords as $record)
                <tr class="hover:bg-zinc-700/30">
                    <td class="px-4 py-3 flex items-center gap-3">
                        <img src="{{ isset($record->user->profile_picture) ? asset('storage/' . $record->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($record->user->name ?? 'U').'&background=4f46e5&color=e0e7ff' }}"
                             alt="{{ $record->user->name ?? '' }}" class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <p class="font-semibold text-white">{{ $record->user->name ?? 'User Dihapus' }}</p>
                            <p class="text-xs text-zinc-400">{{ $record->user->divisi ?? '-' }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        {{ \Carbon\Carbon::parse($record->tanggal)->isoFormat('dddd, D MMMM YYYY') }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($record->jam_masuk_lembur && $record->jam_keluar_lembur)
                            <span class="font-semibold text-white">{{ \Carbon\Carbon::parse($record->jam_masuk_lembur)->format('H:i') }} - {{ \Carbon\Carbon::parse($record->jam_keluar_lembur)->format('H:i') }}</span>
                        @else
                            <span class="font-semibold text-zinc-400">--:--</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        {{ $record->keterangan ?? '-' }}
                    </td>
                    <td class="px-4 py-3 space-y-1">
                        @php $hasLink = false; @endphp
                        @if ($record->lampiran_masuk)
                            <a href="{{ asset('storage/' . $record->lampiran_masuk) }}" target="_blank"
                               class="text-indigo-400 hover:text-indigo-300 underline text-xs font-medium">
                                Lampiran Masuk
                            </a><br>
                            @php $hasLink = true; @endphp
                        @endif
                        @if ($record->lampiran_keluar)
                            <a href="{{ asset('storage/' . $record->lampiran_keluar) }}" target="_blank"
                               class="text-indigo-400 hover:text-indigo-300 underline text-xs font-medium">
                                Lampiran Keluar
                            </a><br>
                            @php $hasLink = true; @endphp
                        @endif
                        @if ($record->latitude_masuk && $record->longitude_masuk)
                            <a href="https://maps.google.com/?q={{ $record->latitude_masuk }},{{ $record->longitude_masuk }}" target="_blank"
                               class="text-indigo-400 hover:text-indigo-300 underline text-xs font-medium">
                                Lokasi Masuk
                            </a><br>
                            @php $hasLink = true; @endphp
                        @endif
                        @if ($record->latitude_keluar && $record->longitude_keluar)
                            <a href="https://maps.google.com/?q={{ $record->latitude_keluar }},{{ $record->longitude_keluar }}" target="_blank"
                               class="text-indigo-400 hover:text-indigo-300 underline text-xs font-medium">
                                Lokasi Keluar
                            </a>
                            @php $hasLink = true; @endphp
                        @endif
                        @if (!$hasLink)
                            <span>-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-zinc-400">
                        Tidak ada data lembur yang tersedia.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-6">
        {{ $lemburRecords->links() }}
    </div>

</x-layout-admin>