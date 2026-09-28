<x-layout-admin>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">Rekap Cuti Bersama</h1>
            <p class="text-sm text-zinc-400 mt-1">Hari libur cuti bersama beserta jumlah karyawan yang jatahnya terpotong otomatis.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-zinc-800 p-5 rounded-xl shadow-lg border border-zinc-700/50">
            <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Tahun</p>
            <h3 class="text-2xl font-extrabold text-white">{{ $year }}</h3>
        </div>
        <div class="bg-zinc-800 p-5 rounded-xl shadow-lg border border-zinc-700/50">
            <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Hari Cuti Bersama</p>
            <h3 class="text-2xl font-extrabold text-sky-400">{{ $holidays->count() }} <span class="text-sm font-semibold text-zinc-500">hari</span></h3>
        </div>
        <div class="bg-zinc-800 p-5 rounded-xl shadow-lg border border-zinc-700/50">
            <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Total Potongan Jatah</p>
            <h3 class="text-2xl font-extrabold text-emerald-500">{{ number_format($totalPotongan, 0, ',', '.') }} <span class="text-sm font-semibold text-zinc-500">hari-karyawan</span></h3>
        </div>
    </div>

    <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700">
        <div class="p-6">
            <form action="{{ route('admin.cuti.bersama') }}" method="GET">
                <div class="flex flex-wrap items-end gap-4 w-full">
                    <div class="flex-1 min-w-[130px] max-w-[200px]">
                        <label for="year" class="block text-sm font-medium text-zinc-400 mb-1">Tahun</label>
                        <div class="relative">
                            <select name="year" id="year" onchange="this.form.submit()" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-8 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm cursor-pointer">
                                @forelse($years as $y)
                                    <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                                @empty
                                    <option value="{{ $year }}" selected>{{ $year }}</option>
                                @endforelse
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="relative overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-300">
                <thead class="bg-zinc-700/50 text-zinc-400 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Keterangan</th>
                        <th class="px-6 py-3 text-right">Karyawan Terpotong</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-700">
                    @forelse($holidays as $holiday)
                    <tr class="hover:bg-zinc-700/50 transition">
                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 font-medium text-white">
                            {{ \Carbon\Carbon::parse($holiday->tanggal)->translatedFormat('l, d F Y') }}
                        </td>
                        <td class="px-6 py-4">{{ $holiday->keterangan ?? '-' }}</td>
                        <td class="px-6 py-4 text-right font-mono font-semibold text-emerald-500">
                            {{ number_format($terpotongPerLibur[$holiday->id] ?? 0, 0, ',', '.') }} orang
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-zinc-500">
                            Belum ada hari cuti bersama pada tahun {{ $year }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout-admin>
