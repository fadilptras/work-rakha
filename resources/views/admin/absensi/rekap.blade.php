<x-layout-admin>
    <x-slot:title>Rekap Absensi Bulanan</x-slot:title>

    {{-- Header Judul --}}
    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-white">Rekap Absensi Bulanan</h1>
        <div class="bg-zinc-800 p-1 rounded-lg inline-flex shadow-sm border border-zinc-700">
            <a href="{{ route('admin.absensi.index') }}"
               class="px-4 py-2 rounded-md text-sm font-bold transition-all text-zinc-400 hover:text-white hover:bg-zinc-700">
                <i class="fas fa-calendar-check mr-2"></i> Absensi Harian
            </a>
            <a href="{{ route('admin.absensi.rekap') }}"
               class="px-4 py-2 rounded-md text-sm font-bold transition-all bg-sky-600 text-white shadow">
                <i class="fas fa-calendar-alt mr-2"></i> Rekap Bulanan
            </a>
        </div>
    </div>

    {{-- Filter & Tombol Download --}}
    <div class="my-6 p-4 bg-zinc-800 rounded-lg shadow-md border border-zinc-700">
        <form method="GET" action="{{ route('admin.absensi.rekap') }}" class="flex flex-wrap items-end gap-4 w-full">
            <input type="hidden" name="minggu" value="{{ $minggu }}">
            {{-- Input Bulan --}}
            <div class="flex-1 min-w-[130px] max-w-[200px]">
                <label for="month" class="block text-sm font-medium text-zinc-300 mb-1">Bulan</label>
                <div class="relative">
                    <select name="month" id="month" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
                        @foreach($months as $num => $nama)
                            <option value="{{ $num }}" {{ (int) $month === (int) $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
            {{-- Input Tahun --}}
            <div class="flex-1 min-w-[130px] max-w-[200px]">
                <label for="year" class="block text-sm font-medium text-zinc-300 mb-1">Tahun</label>
                <div class="relative">
                    <select name="year" id="year" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ (int) $year === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
            {{-- Input Divisi --}}
            <div class="flex-1 min-w-[150px] max-w-[250px]">
                <label for="divisi" class="block text-sm font-medium text-zinc-300 mb-1">Divisi</label>
                <div class="relative">
                    <select name="divisi" id="divisi" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
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
            {{-- Input User --}}
            <div class="flex-1 min-w-[150px] max-w-[250px]">
                <label for="user_id" class="block text-sm font-medium text-zinc-300 mb-1">Karyawan (Perorangan)</label>
                <div class="relative">
                    <select name="user_id" id="user_id" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
                        <option value="">Semua Karyawan</option>
                        @foreach($usersList as $u)
                            <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Tombol Actions --}}
            <div class="flex flex-wrap items-end gap-2 flex-none ml-auto">
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                
                <a href="{{ route('admin.absensi.rekap') }}" class="bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-colors">
                    Reset
                </a>

                <div class="w-px h-8 bg-zinc-600 mx-1"></div>

                <a href="{{ route('admin.absensi.rekap.downloadPdf', request()->except('minggu')) }}"
                   class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105" title="Download PDF">
                    <i class="fas fa-file-pdf mr-2"></i> PDF
                </a>

                <a href="{{ route('admin.absensi.rekap.downloadExcel', request()->except('minggu')) }}"
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105" title="Download Excel">
                    <i class="fas fa-file-excel mr-2"></i> Excel
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Rekap Absensi (per minggu agar muat 1 layar, sel lega untuk jam & telat) --}}
    @php $baseQueryAbsen = request()->except(['minggu']); @endphp
    <div class="overflow-x-auto bg-white rounded-lg shadow-lg border border-zinc-300 relative">
        <table class="text-sm text-left text-zinc-800 border-collapse" style="table-layout: fixed; width: 100%;">
            <colgroup>
                <col style="width: 200px;">
                @foreach($weekDates as $date)
                    <col>
                @endforeach
                <col style="width: 40px;">
                <col style="width: 40px;">
                <col style="width: 40px;">
                <col style="width: 40px;">
                <col style="width: 40px;">
                <col style="width: 160px;">
            </colgroup>
            <thead class="bg-zinc-100 text-xs uppercase font-semibold text-zinc-700 sticky top-0 z-20">
                <tr class="border-b border-zinc-300">
                    <th class="px-4 py-3 bg-zinc-100 border-r border-zinc-300 sticky left-0 z-20" style="width: 200px;">
                        No. & Karyawan
                    </th>
                    <th class="px-2 py-2 text-center border-r border-zinc-300" colspan="{{ count($weekDates) }}">
                        @php
                            $totalMingguAbsen = count($weeksWeb);
                            $mingguPrevAbsen = $minggu - 1;
                            $mingguNextAbsen = $minggu + 1;
                        @endphp
                        <span class="inline-flex items-center justify-center gap-2">
                            @if($mingguPrevAbsen >= 1)
                                <a href="{{ route('admin.absensi.rekap', array_merge($baseQueryAbsen, ['minggu' => $mingguPrevAbsen])) }}"
                                   title="Minggu sebelumnya"
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-zinc-200 text-zinc-700 hover:bg-zinc-300 transition-colors">
                                    <i class="fas fa-chevron-left text-[10px]"></i>
                                </a>
                            @else
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-zinc-100 text-zinc-300 cursor-not-allowed">
                                    <i class="fas fa-chevron-left text-[10px]"></i>
                                </span>
                            @endif
                            <span>{{ $weeksWeb[$minggu - 1]['label'] ?? '' }}</span>
                            @if($mingguNextAbsen <= $totalMingguAbsen)
                                <a href="{{ route('admin.absensi.rekap', array_merge($baseQueryAbsen, ['minggu' => $mingguNextAbsen])) }}"
                                   title="Minggu berikutnya"
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-zinc-200 text-zinc-700 hover:bg-zinc-300 transition-colors">
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </a>
                            @else
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-zinc-100 text-zinc-300 cursor-not-allowed">
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </span>
                            @endif
                        </span>
                    </th>
                    <th class="px-4 py-3 text-center border-r border-zinc-300" colspan="5">Rekap Kehadiran</th>
                    <th class="px-4 py-3 text-center border-zinc-300">Evaluasi</th>
                </tr>
                <tr class="border-b border-zinc-300">
                    <th class="px-4 py-3 bg-zinc-100 border-r border-zinc-300 sticky left-0 z-20"></th>

                    {{-- Loop Header Tanggal (1 minggu, kolom lega) --}}
                    @foreach($weekDates as $date)
                        @php
                            $isSunday = $date->isSunday();
                            $isSaturday = $date->isSaturday();
                            // [UPDATE] Cek apakah tanggal merah
                            $isHoliday = isset($holidays[$date->toDateString()]);

                            $titleText = $date->isoFormat('dddd');
                            $textColor = 'text-zinc-700';
                            $bgColor = 'bg-zinc-100';

                            if ($isSunday || $isHoliday) {
                                $textColor = 'text-red-600';
                                $bgColor = 'bg-red-100'; // Merah terang
                                $titleText = $isHoliday ? ($holidays[$date->toDateString()] ?? 'Libur Nasional') : 'Hari Minggu';
                            } elseif ($isSaturday) {
                                $bgColor = 'bg-zinc-200';
                                $titleText = 'Sabtu';
                            }
                        @endphp
                        <th title="{{ $titleText }}" class="px-1 py-2 text-center border border-zinc-300 {{ $textColor }} {{ $bgColor }}">
                            <span class="block leading-tight" style="font-size: 13px;">{{ $date->day }}</span>
                            <span class="block leading-tight font-normal" style="font-size: 9px;">{{ $date->isoFormat('ddd') }}</span>
                        </th>
                    @endforeach

                    <th class="px-2 py-2 text-center text-green-600 font-bold border border-zinc-300">H</th>
                    <th class="px-2 py-2 text-center text-red-600 font-bold border border-zinc-300">S</th>
                    <th class="px-2 py-2 text-center text-orange-600 font-bold border border-zinc-300">I</th>
                    <th class="px-2 py-2 text-center text-blue-600 font-bold border border-zinc-300">C</th>
                    <th class="px-2 py-2 text-center text-zinc-700 font-bold border border-zinc-300">A</th>
                    <th class="px-2 py-2 text-center border-l border-zinc-300">Terlambat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-300">
                @forelse ($rekapData as $index => $data)
                <tr class="hover:bg-sky-50 transition-colors group">
                    <td class="px-4 py-3 border border-zinc-300 sticky left-0 z-10 bg-white transition-colors group-hover:bg-sky-50" style="width: 200px;">
                        <p class="font-semibold text-zinc-800 truncate">{{ $index + 1 }}. {{ $data['user']->name ?? 'User Dihapus' }}</p>
                        <p class="text-xs text-zinc-500 truncate">{{ $data['user']->jabatan ?? '-' }}</p>
                    </td>

                    @foreach($weekDates as $date)
                        @php
                            $isSunday = $date->isSunday();
                            $isSaturday = $date->isSaturday();
                            // [UPDATE] Cek Holiday
                            $isHoliday = isset($holidays[$date->toDateString()]);

                            $mainStatus = $data['daily'][$date->toDateString()] ?? '-';

                            $colorClass = 'text-zinc-400';
                            switch ($mainStatus) {
                                case 'H': $colorClass = 'text-green-600'; break;
                                case 'S': $colorClass = 'text-red-600'; break;
                                case 'I': $colorClass = 'text-orange-600'; break;
                                case 'C': $colorClass = 'text-blue-600'; break;
                                case 'A': $colorClass = 'text-zinc-800 font-bold'; break;
                                case '-': $colorClass = ($isSunday || $isHoliday) ? 'text-red-300' : 'text-zinc-300'; break;
                            }

                            // [UPDATE] Background Merah jika Minggu ATAU Libur Nasional
                            $bgClass = '';
                            if ($isSunday || $isHoliday) {
                                $bgClass = 'bg-red-50';
                            } elseif ($isSaturday) {
                                $bgClass = 'bg-zinc-50';
                            }
                            $late = $data['late'][$date->toDateString()] ?? null;
                            $time = $data['time'][$date->toDateString()] ?? null;
                            $jamTeks = null;
                            if ($time && ($time['masuk'] || $time['keluar'])) {
                                $jamTeks = str_replace(':', '.', $time['masuk'] ?? '?') . ' - ' . str_replace(':', '.', $time['keluar'] ?? '?');
                            }
                            $tip = $date->isoFormat('dddd, D MMM YYYY') . ': ' . $mainStatus;
                            if ($jamTeks) $tip .= ' (' . $jamTeks . ')';
                            if ($late) $tip .= ' — Terlambat ' . $late['full'];
                        @endphp
                        <td title="{{ $tip }}" class="px-1 py-1 text-center font-bold border border-zinc-300 align-middle overflow-hidden {{ $bgClass }}">
                            <div class="flex flex-col items-center justify-center h-full w-full leading-tight" style="min-height: 52px;">
                                <span class="{{ $colorClass }}" style="font-size: 13px;">{{ $mainStatus }}</span>
                                @if($jamTeks)
                                    <span class="text-zinc-500 font-normal whitespace-nowrap" style="font-size: 10px;">{{ $jamTeks }}</span>
                                @endif
                                @if($late)
                                    <span class="font-bold text-red-600 whitespace-nowrap" style="font-size: 10px;">Telat {{ $late['short'] }}</span>
                                @endif
                            </div>
                        </td>
                    @endforeach

                    <td class="px-2 py-2 text-center font-bold text-green-600 border border-zinc-300 bg-green-50">{{ $data['summary']['H'] }}</td>
                    <td class="px-2 py-2 text-center font-bold text-red-600 border border-zinc-300 bg-red-50">{{ $data['summary']['S'] }}</td>
                    <td class="px-2 py-2 text-center font-bold text-orange-600 border border-zinc-300 bg-orange-50">{{ $data['summary']['I'] }}</td>
                    <td class="px-2 py-2 text-center font-bold text-blue-600 border border-zinc-300 bg-blue-50">{{ $data['summary']['C'] }}</td>
                    <td class="px-2 py-2 text-center font-bold text-zinc-800 border border-zinc-300 bg-zinc-100">{{ $data['summary']['A'] }}</td>
                    <td class="px-2 py-2 text-center font-semibold text-red-600 text-xs border-l border-zinc-300">
                        @if($data['summary']['terlambat'] > 0)
                            <span class="block">{{ $data['summary']['terlambat_formatted'] }}</span>
                            <span class="block font-normal" style="font-size: 10px;">{{ $data['summary']['terlambat_count'] }} kali telat</span>
                        @else
                            <span class="text-zinc-400">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($weekDates) + 7 }}" class="px-4 py-8 text-center text-zinc-500 italic">
                        Tidak ada data absensi yang cocok dengan filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Keterangan / Legend --}}
    <div class="mt-6 p-4 bg-zinc-800 border border-zinc-700 rounded-lg shadow-md flex flex-wrap gap-4 items-center text-sm">
        <span class="text-white font-semibold mr-2">Keterangan:</span>
        <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-green-100 text-green-600 flex items-center justify-center font-bold text-[10px]">H</span> <span class="text-zinc-300">Hadir</span></div>
        <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-red-100 text-red-600 flex items-center justify-center font-bold text-[10px]">S</span> <span class="text-zinc-300">Sakit</span></div>
        <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-[10px]">I</span> <span class="text-zinc-300">Izin</span></div>
        <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-[10px]">C</span> <span class="text-zinc-300">Cuti</span></div>
        <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded bg-zinc-200 text-zinc-800 flex items-center justify-center font-bold text-[10px]">A</span> <span class="text-zinc-300">Alpha</span></div>
        <div class="flex items-center gap-1.5"><span class="px-1 h-4 rounded bg-red-50 text-red-600 flex items-center justify-center font-bold text-[10px]">Telat 1J 20M</span> <span class="text-zinc-300">Telat hari itu + jam masuk-keluar di bawah status (arahkan kursor ke sel untuk rincian)</span></div>
    </div>
</x-layout-admin>