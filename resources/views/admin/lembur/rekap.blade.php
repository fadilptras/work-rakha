<x-layout-admin>
    <x-slot:title>Rekap Lembur Bulanan</x-slot:title>

    @php
        $baseQuery = request()->except(['tab', 'page']);
        $isHarian = ($tab ?? 'harian') === 'harian';
    @endphp

    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-white">Rekap Lembur Bulanan</h1>
        {{-- Tab internal: tetap di route yang sama, sidebar tidak berpindah halaman --}}
        <div class="bg-zinc-800 p-1 rounded-lg inline-flex shadow-sm border border-zinc-700">
            <a href="{{ route('admin.lembur.rekap', array_merge($baseQuery, ['tab' => 'harian'])) }}"
               class="px-4 py-2 rounded-md text-sm font-bold transition-all {{ $isHarian ? 'bg-indigo-600 text-white shadow' : 'text-zinc-400 hover:text-white hover:bg-zinc-700' }}">
                <i class="fas fa-clock mr-2"></i> Lembur Harian
            </a>
            <a href="{{ route('admin.lembur.rekap', array_merge($baseQuery, ['tab' => 'rekap'])) }}"
               class="px-4 py-2 rounded-md text-sm font-bold transition-all {{ !$isHarian ? 'bg-indigo-600 text-white shadow' : 'text-zinc-400 hover:text-white hover:bg-zinc-700' }}">
                <i class="fas fa-calendar-alt mr-2"></i> Rekap Bulanan
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="my-6 p-4 bg-zinc-800 rounded-lg shadow-md border border-zinc-700">
        <form method="GET" action="{{ route('admin.lembur.rekap') }}" class="flex flex-wrap items-end gap-4 w-full">
            <input type="hidden" name="tab" value="{{ $isHarian ? 'harian' : 'rekap' }}">
            @if(!$isHarian)
                <input type="hidden" name="minggu" value="{{ $minggu }}">
            @endif
            <div class="flex-1 min-w-[130px] max-w-[200px]">
                <label for="month" class="block text-sm font-medium text-zinc-300 mb-1">Bulan</label>
                <select name="month" id="month" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
                    @foreach($months as $num => $nama)
                        <option value="{{ $num }}" {{ (int) $month === (int) $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[130px] max-w-[200px]">
                <label for="year" class="block text-sm font-medium text-zinc-300 mb-1">Tahun</label>
                <select name="year" id="year" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ (int) $year === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px] max-w-[250px]">
                <label for="divisi" class="block text-sm font-medium text-zinc-300 mb-1">Divisi</label>
                <select name="divisi" id="divisi" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
                    <option value="">Semua Divisi</option>
                    @foreach($divisions as $d)
                        <option value="{{ $d }}" {{ $divisi == $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px] max-w-[250px]">
                <label for="user_id" class="block text-sm font-medium text-zinc-300 mb-1">Karyawan (Perorangan)</label>
                <select name="user_id" id="user_id" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
                    <option value="">Semua Karyawan</option>
                    @foreach($usersList as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap items-end gap-2 flex-none ml-auto">
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="{{ route('admin.lembur.rekap', ['tab' => $isHarian ? 'harian' : 'rekap']) }}" class="bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-colors">
                    Reset
                </a>
                <div class="w-px h-8 bg-zinc-600 mx-1"></div>
                <a href="{{ route('admin.lembur.rekap.downloadPdf', request()->except('tab', 'page')) }}"
                   class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105" title="Download PDF">
                    <i class="fas fa-file-pdf mr-2"></i> PDF
                </a>
                <a href="{{ route('admin.lembur.rekap.downloadExcel', request()->except('tab', 'page')) }}"
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105" title="Download Excel">
                    <i class="fas fa-file-excel mr-2"></i> Excel
                </a>
            </div>
        </form>
    </div>

    @if($isHarian)
    {{-- SECTION HARIAN: daftar transaksi detail, bahasa jelas --}}
    <div class="overflow-x-auto bg-zinc-800 rounded-lg shadow-lg border border-zinc-700">
        <table class="min-w-full text-sm text-left text-zinc-300">
            <thead class="bg-zinc-700 text-xs uppercase font-semibold text-zinc-200">
                <tr>
                    <th class="px-4 py-3 w-[50px]">No</th>
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Jam Lembur</th>
                    <th class="px-4 py-3">Durasi</th>
                    <th class="px-4 py-3">Keterangan</th>
                    <th class="px-4 py-3">Lampiran & Lokasi</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-700">
                @forelse ($lemburHarian as $i => $record)
                @php $info = $record->info; @endphp
                <tr class="hover:bg-zinc-700/30">
                    <td class="px-4 py-3 text-zinc-400">{{ $lemburHarian->firstItem() + $i }}</td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-white">{{ $record->user->name ?? 'User Dihapus' }}</p>
                        <p class="text-xs text-zinc-400">{{ $record->user->divisi ?? '-' }}</p>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($record->tanggal)->isoFormat('dddd, D MMMM YYYY') }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-white whitespace-nowrap">
                        {{ $info['label'] }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($info['status'] === 'ok')
                            <span class="font-bold text-purple-300">{{ $info['durasi_full'] }}</span>
                        @elseif($info['status'] === 'incomplete')
                            <span class="font-bold text-amber-400">{{ $info['durasi_full'] }}</span>
                        @else
                            <span class="font-bold text-red-400">{{ $info['durasi_full'] }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3" style="min-width: 220px; max-width: 340px;">
                        <span class="block" style="white-space: normal; overflow-wrap: anywhere;">{{ $record->keterangan ?? '-' }}</span>
                    </td>
                    <td class="px-4 py-3 space-y-1 whitespace-nowrap">
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
                            <span class="text-zinc-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($info['status'] === 'ok')
                            <span class="inline-block px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">Selesai</span>
                        @elseif($info['status'] === 'incomplete')
                            <span class="inline-block px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">Tidak Absen Keluar</span>
                        @else
                            <span class="inline-block px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">Perlu Dicek</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-zinc-400">
                        Tidak ada data lembur pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $lemburHarian->links() }}
    </div>
    @else
    {{-- SECTION REKAP: matriks per minggu (maks 7 kolom tanggal) agar muat 1 layar tanpa scroll --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow-lg border border-zinc-300 relative">
        {{-- Kolom tanggal berbagi sisa lebar secara merata (fixed layout) tanpa tergantung hasil build Tailwind --}}
        <table class="text-sm text-left text-zinc-800 border-collapse" style="table-layout: fixed; width: 100%;">
            <colgroup>
                <col style="width: 200px;">
                @foreach($weekDates as $date)
                    <col>
                @endforeach
                <col style="width: 48px;">
                <col style="width: 130px;">
            </colgroup>
            <thead class="bg-zinc-100 text-xs uppercase font-semibold text-zinc-700 sticky top-0 z-20">
                <tr class="border-b border-zinc-300">
                    <th class="px-4 py-3 bg-zinc-100 border-r border-zinc-300 sticky left-0 z-20" style="width: 200px;">
                        No. & Karyawan
                    </th>
                    <th class="px-2 py-2 text-center border-r border-zinc-300" colspan="{{ count($weekDates) }}">
                        @php
                            $totalMinggu = count($weeksWeb);
                            $mingguPrev = $minggu - 1;
                            $mingguNext = $minggu + 1;
                        @endphp
                        <span class="inline-flex items-center justify-center gap-2">
                            @if($mingguPrev >= 1)
                                <a href="{{ route('admin.lembur.rekap', array_merge($baseQuery, ['tab' => 'rekap', 'minggu' => $mingguPrev])) }}"
                                   title="Minggu sebelumnya"
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-zinc-200 text-zinc-700 hover:bg-zinc-300 transition-colors">
                                    <i class="fas fa-chevron-left text-[10px]"></i>
                                </a>
                            @else
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-zinc-100 text-zinc-300 cursor-not-allowed">
                                    <i class="fas fa-chevron-left text-[10px]"></i>
                                </span>
                            @endif
                            <span>{{ $weeksWeb[$minggu - 1]['label'] ?? \Carbon\Carbon::create($year, $month, 1)->isoFormat('MMMM YYYY') }}</span>
                            @if($mingguNext <= $totalMinggu)
                                <a href="{{ route('admin.lembur.rekap', array_merge($baseQuery, ['tab' => 'rekap', 'minggu' => $mingguNext])) }}"
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
                    <th class="px-4 py-3 text-center border-zinc-300" colspan="2">Total Sebulan</th>
                </tr>
                <tr class="border-b border-zinc-300">
                    <th class="px-4 py-3 bg-zinc-100 border-r border-zinc-300 sticky left-0 z-20"></th>
                    @foreach($weekDates as $date)
                        @php
                            $isSunday = $date->isSunday();
                            $isSaturday = $date->isSaturday();
                            $isHoliday = isset($holidays[$date->toDateString()]);
                            $titleText = $isHoliday ? ($holidays[$date->toDateString()] ?? 'Libur Nasional') : ($isSunday ? 'Hari Minggu' : ($isSaturday ? 'Sabtu' : $date->isoFormat('dddd')));
                            $textColor = 'text-zinc-700';
                            $bgColor = 'bg-zinc-100';
                            if ($isSunday || $isHoliday) {
                                $textColor = 'text-red-600';
                                $bgColor = 'bg-red-100';
                            } elseif ($isSaturday) {
                                $bgColor = 'bg-zinc-200';
                            }
                        @endphp
                        <th title="{{ $titleText }}" class="px-1 py-2 text-center border border-zinc-300 {{ $textColor }} {{ $bgColor }}">
                            <span class="block leading-tight" style="font-size: 13px;">{{ $date->day }}</span>
                            <span class="block leading-tight font-normal" style="font-size: 9px;">{{ $date->isoFormat('ddd') }}</span>
                        </th>
                    @endforeach
                    <th class="px-2 py-2 text-center text-purple-600 font-bold border border-zinc-300">Hari</th>
                    <th class="px-2 py-2 text-center text-zinc-700 font-bold border border-zinc-300">Total Jam</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-300">
                @forelse ($rekapData as $index => $data)
                <tr class="hover:bg-sky-50 transition-colors group">
                    <td class="px-4 py-3 border border-zinc-300 sticky left-0 z-10 bg-white transition-colors group-hover:bg-sky-50" style="width: 200px;">
                        <p class="font-semibold text-zinc-800 truncate">{{ $index + 1 }}. {{ $data['user']->name ?? 'User Dihapus' }}</p>
                        <p class="text-xs text-zinc-500 truncate">{{ $data['user']->jabatan ?? $data['user']->divisi ?? '-' }}</p>
                    </td>

                    @foreach($weekDates as $date)
                        @php
                            $isSunday = $date->isSunday();
                            $isSaturday = $date->isSaturday();
                            $isHoliday = isset($holidays[$date->toDateString()]);
                            $bgClass = '';
                            if ($isSunday || $isHoliday) {
                                $bgClass = 'bg-red-50';
                            } elseif ($isSaturday) {
                                $bgClass = 'bg-zinc-50';
                            }
                            $cell = $data['daily'][$date->toDateString()] ?? null;
                            $tip = $date->isoFormat('dddd, D MMM YYYY');
                            if ($cell) {
                                $tip .= ': ' . $cell['label'] . ' (' . ($cell['durasi_full'] ?? $cell['durasi']) . ')';
                                if (!empty($cell['keterangan'])) $tip .= ' — ' . $cell['keterangan'];
                            }
                        @endphp
                        <td title="{{ $tip }}" class="px-1 py-1 text-center border border-zinc-300 align-middle overflow-hidden {{ $bgClass }}">
                            @if($cell)
                                @php $durasiTeks = $cell['durasi_full'] ?? $cell['durasi']; @endphp
                                @if($cell['status'] === 'ok')
                                    <div class="font-bold text-purple-700 leading-tight whitespace-nowrap" style="font-size: 11px;">{{ $cell['label'] }}</div>
                                    <div class="text-zinc-500 leading-tight whitespace-nowrap" style="font-size: 10px;">{{ $durasiTeks }}</div>
                                @elseif($cell['status'] === 'incomplete')
                                    <div class="font-bold text-amber-600 leading-tight whitespace-nowrap" style="font-size: 11px;">{{ $cell['label'] }}</div>
                                    <div class="text-amber-600 leading-tight whitespace-nowrap" style="font-size: 10px;">{{ $durasiTeks }}</div>
                                @else
                                    <div class="font-bold text-red-600 leading-tight whitespace-nowrap" style="font-size: 11px;">{{ $cell['label'] }}</div>
                                    <div class="text-red-600 leading-tight whitespace-nowrap" style="font-size: 10px;">{{ $durasiTeks }}</div>
                                @endif
                            @else
                                <span class="{{ ($isSunday || $isHoliday) ? 'text-red-300' : 'text-zinc-300' }}">-</span>
                            @endif
                        </td>
                    @endforeach

                    <td class="px-2 py-2 text-center font-bold text-purple-600 border border-zinc-300 bg-purple-50">{{ $data['summary']['total_hari'] }}</td>
                    <td class="px-2 py-2 text-center font-semibold text-zinc-700 border border-zinc-300 bg-zinc-50 text-xs">{{ $data['summary']['total_formatted'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($weekDates) + 3 }}" class="px-4 py-8 text-center text-zinc-500 italic">
                        Tidak ada data lembur yang cocok dengan filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 p-4 bg-zinc-800 border border-zinc-700 rounded-lg shadow-md flex flex-wrap gap-4 items-center text-sm">
        <span class="text-white font-semibold mr-2">Keterangan:</span>
        <div class="flex items-center gap-1.5"><span class="px-2 h-4 rounded bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-[10px]">19.00 - 21.30</span> <span class="text-zinc-300">Jam lembur + durasi penuh (misal 2 Jam 30 Menit)</span></div>
        <div class="flex items-center gap-1.5"><span class="px-2 h-4 rounded bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-[10px]">Tidak Absen Keluar</span> <span class="text-zinc-300">Jam keluar lembur tidak diisi</span></div>
        <div class="flex items-center gap-1.5"><span class="px-2 h-4 rounded bg-red-100 text-red-700 flex items-center justify-center font-bold text-[10px]">Cek jam keluar</span> <span class="text-zinc-300">Jam keluar < jam masuk</span></div>
        <div class="flex items-center gap-1.5"><i class="fas fa-mouse-pointer text-zinc-400 text-xs"></i> <span class="text-zinc-300">Arahkan kursor ke sel untuk melihat rincian + keterangan lembur</span></div>
    </div>
    @endif
</x-layout-admin>
