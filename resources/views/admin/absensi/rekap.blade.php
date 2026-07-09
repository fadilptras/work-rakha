<x-layout-admin>
    <x-slot:title>Rekap Absensi Bulanan</x-slot:title>

    {{-- Header Judul --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Rekap Absensi Bulanan</h1>
    </div>

    {{-- Filter & Tombol Download --}}
    <div class="my-6 p-4 bg-zinc-800 rounded-lg shadow-md border border-zinc-700">
        <form method="GET" action="{{ route('admin.absensi.rekap') }}" class="flex flex-wrap items-end gap-4">
            {{-- Input Start Date --}}
            <div>
                <label for="start_date" class="block text-sm font-medium text-zinc-300">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            {{-- Input End Date --}}
            <div>
                <label for="end_date" class="block text-sm font-medium text-zinc-300">Tanggal Selesai</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            {{-- Input Divisi --}}
            <div>
                <label for="divisi" class="block text-sm font-medium text-zinc-300">Divisi</label>
                <select name="divisi" id="divisi" class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Semua Divisi</option>
                    @foreach($divisions as $d)
                        <option value="{{ $d }}" {{ $divisi == $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Input User --}}
            <div>
                <label for="user_id" class="block text-sm font-medium text-zinc-300">Karyawan (Perorangan)</label>
                <select name="user_id" id="user_id" class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Semua Karyawan</option>
                    @foreach($usersList as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol Actions --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                
                <a href="{{ route('admin.absensi.rekap') }}" class="bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-colors">
                    Reset
                </a>

                <div class="w-px h-8 bg-zinc-600 mx-1"></div>

                <a href="{{ route('admin.absensi.rekap.downloadPdf', request()->query()) }}"
                   class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105" title="Download PDF">
                    <i class="fas fa-file-pdf mr-2"></i> PDF
                </a>
                
                <a href="{{ route('admin.absensi.rekap.downloadExcel', request()->query()) }}"
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105" title="Download Excel">
                    <i class="fas fa-file-excel mr-2"></i> Excel
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Rekap Absensi --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow-lg border border-zinc-300 relative">
        <table class="w-full text-sm text-left text-zinc-800 border-collapse whitespace-nowrap">
            <thead class="bg-zinc-200 text-xs uppercase font-semibold text-zinc-700 sticky top-0 z-20">
                <tr class="border-b border-zinc-300">
                    <th class="px-4 py-3 bg-zinc-200 border-r border-zinc-300 sticky left-0 z-20 min-w-[250px]">
                        No. & Karyawan
                    </th>
                    <th class="px-4 py-3 text-center border-r border-zinc-300" colspan="{{ $allDates->count() }}">
                        Bulan {{ \Carbon\Carbon::parse($startDate)->isoFormat('MMMM YYYY') }}
                    </th>
                    <th class="px-4 py-3 text-center border-r border-zinc-300" colspan="6">Rekap Kehadiran</th>
                    <th class="px-4 py-3 text-center">Evaluasi</th>
                </tr>
                <tr class="border-b border-zinc-300">
                    <th class="px-4 py-3 bg-zinc-200 border-r border-zinc-300 sticky left-0 z-20"></th>
                    
                    {{-- Loop Header Tanggal --}}
                    @foreach($allDates as $date)
                        @php
                            $isSunday = $date->isSunday();
                            $isSaturday = $date->isSaturday();
                            // [UPDATE] Cek apakah tanggal merah
                            $isHoliday = isset($holidays[$date->toDateString()]);
                            
                            $titleText = '';
                            $textColor = '';
                            $bgColor = 'bg-zinc-200';

                            if ($isSunday || $isHoliday) {
                                $textColor = 'text-red-600';
                                $bgColor = 'bg-red-100'; // Merah
                                $titleText = $isHoliday ? ($holidays[$date->toDateString()] ?? 'Libur Nasional') : 'Hari Minggu';
                            } elseif ($isSaturday) {
                                $bgColor = 'bg-zinc-100';
                                $titleText = 'Sabtu';
                            }
                        @endphp
                        <th title="{{ $titleText }}" class="px-1 py-2 text-center border-r border-zinc-300 w-[30px] min-w-[30px] {{ $textColor }} {{ $bgColor }}">
                            {{ $date->day }}
                        </th>
                    @endforeach

                    <th class="px-2 py-2 text-center text-green-600 font-bold border-r border-zinc-300 w-[35px]">H</th>
                    <th class="px-2 py-2 text-center text-red-600 font-bold border-r border-zinc-300 w-[35px]">S</th>
                    <th class="px-2 py-2 text-center text-orange-600 font-bold border-r border-zinc-300 w-[35px]">I</th>
                    <th class="px-2 py-2 text-center text-blue-600 font-bold border-r border-zinc-300 w-[35px]">C</th>
                    <th class="px-2 py-2 text-center text-gray-600 font-bold border-r border-zinc-300 w-[35px]">A</th>
                    <th class="px-2 py-2 text-center text-purple-600 font-bold border-r border-zinc-300 w-[35px]">L</th>
                    <th class="px-4 py-3 text-left w-[150px]">Terlambat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-300">
                @forelse ($rekapData as $index => $data)
                <tr class="hover:bg-zinc-50 transition-colors">
                    <td class="px-4 py-3 border-r border-zinc-300 sticky left-0 z-10 bg-white hover:bg-zinc-50 transition-colors">
                        <p class="font-semibold text-zinc-800">{{ $index + 1 }}. {{ $data['user']->name ?? 'User Dihapus' }}</p>
                        <p class="text-xs text-zinc-500">{{ $data['user']->jabatan ?? '-' }}</p>
                    </td>

                    @foreach($allDates as $date)
                        @php
                            $isSunday = $date->isSunday();
                            $isSaturday = $date->isSaturday();
                            // [UPDATE] Cek Holiday
                            $isHoliday = isset($holidays[$date->toDateString()]);
                            
                            $statusString = $data['daily'][$date->toDateString()] ?? '-';
                            $hasLembur = str_contains($statusString, 'L');
                            $mainStatus = $hasLembur ? trim(str_replace('L', '', $statusString)) : $statusString;
                            if ($mainStatus == "") $mainStatus = 'L'; 
                            
                            $colorClass = 'text-gray-400'; 
                            switch ($mainStatus) {
                                case 'H': $colorClass = 'text-green-600'; break;
                                case 'S': $colorClass = 'text-red-600'; break;
                                case 'I': $colorClass = 'text-orange-600'; break;
                                case 'C': $colorClass = 'text-blue-600'; break;
                                case 'A': $colorClass = 'text-gray-800 font-bold'; break;
                                case 'L': $colorClass = 'text-purple-600'; $hasLembur = false; break;
                                case '-': $colorClass = ($isSunday || $isHoliday) ? 'text-red-300' : 'text-zinc-300'; break;
                            }

                            // [UPDATE] Background Merah jika Minggu ATAU Libur Nasional
                            $bgClass = '';
                            if ($isSunday || $isHoliday) {
                                $bgClass = 'bg-red-50'; 
                            } elseif ($isSaturday) {
                                $bgClass = 'bg-zinc-100';
                            }
                        @endphp
                        <td class="px-1 py-2 text-center font-bold border-r border-zinc-300 {{ $bgClass }}">
                            <span class="{{ $colorClass }}">{{ $mainStatus }}</span>
                            @if ($hasLembur)
                                <sup class="text-purple-600 text-[10px] ml-[-2px]">L</sup>
                            @endif
                        </td>
                    @endforeach

                    <td class="px-2 py-2 text-center font-bold text-green-600 border-r border-zinc-300">{{ $data['summary']['H'] }}</td>
                    <td class="px-2 py-2 text-center font-bold text-red-600 border-r border-zinc-300">{{ $data['summary']['S'] }}</td>
                    <td class="px-2 py-2 text-center font-bold text-orange-600 border-r border-zinc-300">{{ $data['summary']['I'] }}</td>
                    <td class="px-2 py-2 text-center font-bold text-blue-600 border-r border-zinc-300">{{ $data['summary']['C'] }}</td>
                    <td class="px-2 py-2 text-center font-bold text-gray-800 border-r border-zinc-300">{{ $data['summary']['A'] }}</td>
                    <td class="px-2 py-2 text-center font-bold text-purple-600 border-r border-zinc-300">{{ $data['summary']['L'] }}</td>
                    <td class="px-4 py-3 font-semibold text-red-600 text-xs">
                        @if($data['summary']['terlambat'] > 0)
                            {{ $data['summary']['terlambat_formatted'] }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $allDates->count() + 8 }}" class="px-4 py-8 text-center text-zinc-500 italic">
                        Tidak ada data absensi yang cocok dengan filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layout-admin>