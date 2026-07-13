<x-layout-admin>
    <x-slot:title>Pengaturan Jatah Cuti</x-slot:title>

    <div class="p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">
                    Pengaturan Jatah Cuti Karyawan (Tahun {{ \Carbon\Carbon::now()->year }})
                </h1>
                <p class="text-sm text-zinc-400 mt-1">Atur kuota cuti tahunan dan pantau sisa cuti karyawan.</p>
            </div>
            
            <a href="{{ route('admin.cuti.downloadPengaturanPDF') }}" class="mt-4 md:mt-0 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition flex items-center gap-2">
                <i class="fas fa-file-pdf"></i>
                <span>Download Laporan</span>
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-500/10 border-l-4 border-green-500 text-green-400 p-4 rounded-md mb-6" role="alert">
                <p class="font-bold">Berhasil!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-zinc-800 rounded-lg shadow-lg border border-zinc-700">
            {{-- LOGIC: Route diarahkan ke updatePengaturan sesuai web.php Anda --}}
            <form action="{{ route('admin.cuti.updatePengaturan') }}" method="POST">
                @csrf
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-zinc-300">
                        <thead class="bg-zinc-700 text-xs uppercase font-semibold text-zinc-200">
                            <tr>
                                <th class="px-6 py-4">Nama Karyawan</th>
                                <th class="px-6 py-4 text-center text-amber-500">Terpakai (Thn Ini)</th>
                                <th class="px-6 py-4 text-center text-emerald-400">Sisa Cuti</th>
                                <th class="px-6 py-4">Jatah Cuti Tahunan (Input)</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-700">
                            @forelse ($users as $user)
                                <tr class="hover:bg-zinc-700/30">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-white">{{ $user->name }}</div>
                                        <div class="text-xs text-zinc-400">{{ $user->jabatan ?? 'Jabatan tidak diatur' }}</div>
                                    </td>

                                    {{-- LOGIC: Mengambil variabel hasil perhitungan otomatis dari Controller --}}
                                    <td class="px-6 py-4 text-center font-mono">
                                        {{ $user->cuti_terpakai }} Hari
                                    </td>

                                    {{-- LOGIC: Mengambil sisa_cuti real-time dari tabel database users --}}
                                    <td class="px-6 py-4 text-center font-bold font-mono {{ $user->sisa_cuti < 0 ? 'text-red-500' : 'text-emerald-400' }}">
                                        {{ $user->sisa_cuti }} Hari
                                    </td>

                                    <td class="px-6 py-4">
                                        <input 
                                            type="number" 
                                            name="jatah_cuti[{{ $user->id }}]" 
                                            value="{{ $user->jatah_cuti ?? 12 }}" 
                                            class="w-32 bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm text-center font-mono" 
                                            min="0"
                                            placeholder="12">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-10 text-zinc-400">
                                        <i class="fas fa-users-slash fa-2x mb-2"></i>
                                        <p>Belum ada data karyawan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-zinc-800 border-t border-zinc-700 flex justify-end rounded-b-lg">
                    <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout-admin>