<x-layout-admin>
    <x-slot:title>Kelola Pengajuan Dokumen</x-slot:title>

    <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700">
        <div class="p-6 border-b border-zinc-700">
            <h2 class="text-xl font-bold text-white">Rekap Pengajuan Dokumen</h2>
        </div>
        
        {{-- --- FORM FILTER BARU --- --}}
        <div class="p-6">
            <form method="GET" action="{{ route('admin.pengajuan-dokumen.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-zinc-400 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="w-full bg-zinc-700 border-zinc-600 rounded-lg text-white px-3 py-2">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-zinc-400 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="w-full bg-zinc-700 border-zinc-600 rounded-lg text-white px-3 py-2">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-lg">Filter</button>
                        <a href="{{ route('admin.pengajuan-dokumen.index') }}" class="w-full bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg text-center px-3 py-2">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-zinc-300">
                {{-- ... header tabel ... --}}
                <thead class="text-xs text-zinc-400 uppercase bg-zinc-700/50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Tanggal</th>
                        <th scope="col" class="px-6 py-3">Nama Karyawan</th>
                        <th scope="col" class="px-6 py-3">Jenis Dokumen</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajuanDokumens as $dokumen)
                    <tr class="bg-zinc-800 border-b border-zinc-700 hover:bg-zinc-700/50">
                        <td class="px-6 py-4">{{ $dokumen->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 font-medium text-white">{{ $dokumen->user->name }}</td>
                        <td class="px-6 py-4">{{ $dokumen->jenis_dokumen }}</td>
                        <td class="px-6 py-4">{{ ucfirst($dokumen->status) }}</td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.pengajuan-dokumen.show', $dokumen) }}" class="font-medium text-amber-400 hover:underline">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-zinc-500">Tidak ada data untuk ditampilkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout-admin>