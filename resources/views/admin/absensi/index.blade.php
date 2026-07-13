<x-layout-admin>
<x-slot:title>Aktivitas</x-slot:title>

<div class="flex justify-between items-center mb-6 flex-wrap gap-4">
    <h1 class="text-2xl font-bold text-white">Absensi & Lembur Harian Karyawan</h1>
</div>

{{-- FILTER & ACTIONS SECTION (SEBARIS) --}}
    <div class="mb-6 p-4 bg-zinc-800 rounded-lg shadow-md border border-zinc-700">
        <form method="GET" action="{{ route('admin.absensi.index') }}" class="flex flex-wrap items-end gap-4 w-full" id="filter-form">
            
            {{-- Input Bulan --}}
            <div class="flex-1 min-w-[120px] max-w-[200px]">
                <label for="month" class="block text-sm font-medium text-zinc-300 mb-1">Bulan</label>
                <div class="relative">
                    <select name="month" id="month" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white text-sm shadow-sm focus:ring-sky-500 focus:border-sky-500 cursor-pointer">
                        @foreach($months as $key => $name)
                            <option value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Input Tahun --}}
            <div class="flex-1 min-w-[100px] max-w-[150px]">
                <label for="year" class="block text-sm font-medium text-zinc-300 mb-1">Tahun</label>
                <div class="relative">
                    <select name="year" id="year" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white text-sm shadow-sm focus:ring-sky-500 focus:border-sky-500 cursor-pointer">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Input Divisi --}}
            <div class="flex-[2] min-w-[150px]">
                <label for="divisi" class="block text-sm font-medium text-zinc-300 mb-1">Divisi</label>
                <div class="relative">
                    <select name="divisi" id="divisi" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white text-sm shadow-sm focus:ring-sky-500 focus:border-sky-500 cursor-pointer">
                        <option value="">Semua Divisi</option>
                        @foreach($divisions as $d)
                            <option value="{{ $d }}" {{ $divisi == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Input Status (Checkboxes) --}}
            <div class="flex-none">
                <label class="block text-sm font-medium text-zinc-300 mb-2">Status</label>
                <div class="flex flex-wrap gap-4 items-center">
                    @php
                        $availableStatuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'cuti' => 'Cuti', 'lembur' => 'Lembur'];
                    @endphp
                    @foreach($availableStatuses as $val => $label)
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status[]" value="{{ $val }}" 
                                   {{ in_array($val, is_array($status) ? $status : []) ? 'checked' : '' }}
                                   class="form-checkbox h-4 w-4 text-sky-600 bg-zinc-700 border-zinc-600 rounded focus:ring-sky-500 focus:ring-offset-zinc-800 status-checkbox">
                            <span class="ml-2 text-sm text-zinc-300">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- BUTTON GROUP --}}
            <div class="flex flex-wrap items-end gap-2 flex-none ml-auto">
                {{-- Tombol Filter --}}
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform hover:scale-105">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>

                {{-- Tombol Reset --}}
                <a href="{{ route('admin.absensi.index') }}" class="bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-colors">
                    <i class="fas fa-undo mr-2"></i> Reset
                </a>

                {{-- Divider --}}
                <div class="w-px h-8 bg-zinc-600 mx-1 hidden sm:block"></div>

                {{-- Tombol Download PDF --}}
                <a href="{{ route('admin.absensi.downloadPdfHarian', request()->query()) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform hover:scale-105" title="Download PDF Harian">
                    <i class="fas fa-file-pdf mr-2"></i> PDF
                </a>
                
                {{-- Tombol Download Excel --}}
                <a href="{{ route('admin.absensi.downloadExcelHarian', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform hover:scale-105" title="Download Excel Harian">
                    <i class="fas fa-file-excel mr-2"></i> Excel
                </a>
            </div>
            
        </form>
    </div>

<div class="mb-4">
    <h2 class="text-white font-semibold">Pilih Tanggal: </h2>
</div>

<div class="mt-6 flex justify-start items-center flex-wrap">
    @for ($i = 1; $i <= $daysInMonth; $i++)
        <a href="{{ route('admin.absensi.index', array_merge(request()->except('day'), ['day' => $i])) }}"
           class="m-1 px-3 py-1 text-sm rounded-md transition-colors duration-200
           @if ($i == $day) bg-sky-600 text-white font-bold
           @else bg-zinc-700 text-zinc-300 hover:bg-zinc-600 @endif">
            {{ $i }}
        </a>
    @endfor
</div>

<div class="overflow-x-auto bg-zinc-800 rounded-lg shadow-lg border border-zinc-700 mt-6">
    <table class="min-w-full text-sm text-left text-zinc-300">
        <thead class="bg-zinc-700 text-xs uppercase font-semibold text-zinc-200">
            <tr>
                <th class="px-4 py-3">Karyawan</th>
                <th class="px-4 py-3">Waktu Masuk</th>
                <th class="px-4 py-3">Waktu Keluar</th>
                <th class="px-4 py-3">Durasi</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Keterangan</th>
                <th class="px-4 py-3">Lampiran & Lokasi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-700">
            @forelse ($combined_records as $record)
                @php
                    $isLembur = ($record->record_type === 'lembur');
                    $statusBadgeColor = 'bg-gray-500/10 text-gray-400';
                    $statusText = $isLembur ? 'Lembur' : $record->status;
                    
                    if ($isLembur) {
                        $jamMasuk = $record->jam_masuk_lembur ? \Carbon\Carbon::parse($record->jam_masuk_lembur) : null;
                        $jamKeluar = $record->jam_keluar_lembur ? \Carbon\Carbon::parse($record->jam_keluar_lembur) : null;
                        $tglKeluar = $record->tanggal; 
                        $statusBadgeColor = 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 shadow-[0_0_10px_rgba(234,179,8,0.2)]';
                        
                        $durasiKerja = '-';
                        if ($jamMasuk && $jamKeluar) {
                            $totalMenit = $jamMasuk->diffInMinutes($jamKeluar);
                            $durasiKerja = floor($totalMenit / 60) . ' Jam ' . ($totalMenit % 60) . ' Menit';
                        }
                    } else {
                        $jamMasuk = $record->jam_masuk ? \Carbon\Carbon::parse($record->jam_masuk) : null;
                        $jamKeluar = $record->jam_keluar ? \Carbon\Carbon::parse($record->jam_keluar) : null;
                        $tglKeluar = $record->tanggal_keluar ?? $record->tanggal;
                        $durasiKerja = $record->durasi_teks ?? '-'; 
                        
                        if ($record->status == 'hadir') {
                            $batasWaktuMasuk = \Carbon\Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                            $waktuMasukKaryawan = $jamMasuk ? \Carbon\Carbon::parse($jamMasuk, 'Asia/Jakarta') : null;

                            $isLate = $waktuMasukKaryawan && $waktuMasukKaryawan->gt($batasWaktuMasuk);
                            
                            if ($isLate) {
                                $statusText = 'Hadir (Terlambat)';
                                $statusBadgeColor = 'bg-green-500/10 text-green-400';
                            } else {
                                $statusText = 'Hadir';
                                $statusBadgeColor = 'bg-green-500/10 text-green-400';
                            }
                        } elseif ($record->status == 'sakit') {
                            $statusBadgeColor = 'bg-red-500/10 text-red-400';
                        } elseif ($record->status == 'izin') {
                            $statusBadgeColor = 'bg-amber-500/10 text-amber-400';
                        } elseif ($record->status == 'cuti') {
                            $statusBadgeColor = 'bg-purple-500/10 text-purple-400';
                        } elseif ($record->status == 'tidak hadir') {
                            $statusBadgeColor = 'bg-gray-500/10 text-gray-400';
                        }
                    }
                @endphp
                <tr class="hover:bg-zinc-700/30 transition-colors {{ $isLembur ? 'bg-yellow-900/5' : '' }}">
                    {{-- KOLOM KARYAWAN --}}
                    <td class="px-4 py-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border-2 {{ $isLembur ? 'border-yellow-500/50' : 'border-zinc-600' }} shadow-md">
                            <img src="{{ isset($record->user->profile_picture) ? asset('storage/' . $record->user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($record->user->name ?? 'U').'&background=0284c7&color=e0f2fe' }}"
                                 alt="{{ $record->user->name ?? '' }}" class="w-full h-full object-cover" loading="lazy">
                        </div>
                        <div>
                            <p class="font-semibold text-white">{{ $record->user->name ?? 'User Dihapus' }}</p>
                            <p class="text-xs text-zinc-400">{{ $record->user->divisi ?? '-' }}</p>
                        </div>
                    </td>

                    {{-- KOLOM WAKTU MASUK (TANGGAL & JAM) --}}
                    <td class="px-4 py-3">
                        <div class="flex flex-col">
                            <span class="font-semibold text-white">
                                {{ \Carbon\Carbon::parse($record->tanggal)->isoFormat('dddd, D MMM Y') }}
                            </span>
                            @if ($jamMasuk)
                                <span class="text-zinc-400 text-xs mt-1 font-mono">
                                    <i class="fas fa-sign-in-alt text-sky-400 mr-1"></i> {{ $jamMasuk->format('H:i') }} WIB
                                </span>
                            @else
                                <span class="text-zinc-500 text-xs mt-1">-</span>
                            @endif
                        </div>
                    </td>

                    {{-- KOLOM WAKTU KELUAR (TANGGAL & JAM) --}}
                    <td class="px-4 py-3">
                        @if ($jamKeluar)
                            <div class="flex flex-col">
                                <span class="font-semibold text-white">
                                    {{ \Carbon\Carbon::parse($tglKeluar)->isoFormat('dddd, D MMM Y') }}
                                </span>
                                <span class="text-zinc-400 text-xs mt-1 font-mono">
                                    <i class="fas fa-sign-out-alt text-orange-400 mr-1"></i> {{ $jamKeluar->format('H:i') }} WIB
                                </span>
                            </div>
                        @else
                            <span class="font-semibold text-zinc-500">-</span>
                        @endif
                    </td>

                    {{-- KOLOM DURASI --}}
                    <td class="px-4 py-3">
                       <span class="font-semibold text-white">{{ $durasiKerja }}</span>
                    </td>

                    {{-- KOLOM STATUS --}}
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 font-bold leading-tight rounded-full text-xs text-center capitalize inline-flex items-center gap-1.5 {{ $statusBadgeColor }}">
                            @if($isLembur)
                                <i class="fas fa-moon"></i>
                            @endif
                            {{ $statusText }}
                        </span>
                    </td>

                    {{-- KOLOM KETERANGAN --}}
                    <td class="px-4 py-3 text-xs text-zinc-300">
                        {{ $record->keterangan ?? '-' }}
                    </td>

                    {{-- KOLOM LAMPIRAN & LOKASI --}}
                    <td class="px-4 py-3 space-y-1">
                        @php
                            $hasLink = false;
                            $lampiranMasuk = $isLembur ? $record->lampiran_masuk : $record->lampiran;
                            $lampiranKeluar = $isLembur ? $record->lampiran_keluar : $record->lampiran_keluar;
                            $latMasuk = $isLembur ? $record->latitude_masuk : $record->latitude;
                            $longMasuk = $isLembur ? $record->longitude_masuk : $record->longitude;
                            $latKeluar = $isLembur ? $record->latitude_keluar : $record->latitude_keluar;
                            $longKeluar = $isLembur ? $record->longitude_keluar : $record->longitude_keluar;
                        @endphp

                        @if ($lampiranMasuk)
                            <a href="{{ asset('storage/' . $lampiranMasuk) }}" target="_blank" class="text-sky-400 hover:text-sky-300 underline text-xs font-medium flex items-center gap-1"><i class="fas fa-paperclip"></i> Lamp. Masuk</a>
                            @php $hasLink = true; @endphp
                        @endif
                        @if ($lampiranKeluar)
                            <a href="{{ asset('storage/'. $lampiranKeluar) }}" target="_blank" class="text-sky-400 hover:text-sky-300 underline text-xs font-medium flex items-center gap-1 mt-1"><i class="fas fa-paperclip"></i> Lamp. Keluar</a>
                            @php $hasLink = true; @endphp
                        @endif
                        @if ($latMasuk && $longMasuk)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $latMasuk }},{{ $longMasuk }}" target="_blank" class="text-sky-400 hover:text-sky-300 underline text-xs font-medium flex items-center gap-1 mt-1"><i class="fas fa-map-marker-alt"></i> Lok. Masuk</a>
                            @php $hasLink = true; @endphp
                        @endif
                        @if ($latKeluar && $longKeluar)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $latKeluar }},{{ $longKeluar }}" target="_blank" class="text-sky-400 hover:text-sky-300 underline text-xs font-medium flex items-center gap-1 mt-1"><i class="fas fa-map-marker-alt"></i> Lok. Keluar</a>
                            @php $hasLink = true; @endphp
                        @endif

                        @if (!$hasLink)
                            <span class="text-zinc-500 text-xs">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const divisiSelect = document.getElementById('divisi');
        const form = document.getElementById('filter-form');
        const statusCheckboxes = document.querySelectorAll('.status-checkbox');

        divisiSelect.addEventListener('change', function() {
            form.submit();
        });
        
        statusCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                form.submit();
            });
        });
    });
</script>
@endpush

</x-layout-admin>