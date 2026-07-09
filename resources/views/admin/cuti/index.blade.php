<x-layout-admin>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">Manajemen Pengajuan Cuti Karyawan</h1>
            <p class="text-sm text-zinc-400 mt-1">Pantau cuti yang masuk, disetujui, dan ditolak.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg flex items-center gap-3 shadow-lg fade-in">
            <i class="fas fa-check-circle text-xl"></i>
            <div>
                <span class="font-bold">Berhasil!</span>
                <span class="block text-sm opacity-90">{{ session('success') }}</span>
            </div>
            {{-- Tombol Close (Opsional, menggunakan AlpineJS jika ada, atau biarkan auto-hilang) --}}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg flex items-center gap-3 shadow-lg">
            <i class="fas fa-exclamation-circle text-xl"></i>
            <div>
                <span class="font-bold">Gagal!</span>
                <span class="block text-sm opacity-90">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700">
        
        {{-- TAB NAVIGATION (Tetap sama seperti sebelumnya) --}}
        <div class="flex border-b border-zinc-700 px-6 mt-4">
            <a href="{{ route('admin.cuti.index', array_merge(request()->query(), ['tab' => 'pending', 'page' => 1])) }}" 
               class="px-4 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab == 'pending' ? 'border-amber-500 text-amber-500' : 'border-transparent text-zinc-400 hover:text-zinc-200' }}">
               <i class="fas fa-clock mr-2"></i> Perlu Persetujuan
            </a>
            <a href="{{ route('admin.cuti.index', array_merge(request()->query(), ['tab' => 'approved', 'page' => 1])) }}" 
               class="px-4 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab == 'approved' ? 'border-emerald-500 text-emerald-500' : 'border-transparent text-zinc-400 hover:text-zinc-200' }}">
               <i class="fas fa-check-circle mr-2"></i> Disetujui
            </a>
            <a href="{{ route('admin.cuti.index', array_merge(request()->query(), ['tab' => 'rejected', 'page' => 1])) }}" 
               class="px-4 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab == 'rejected' ? 'border-red-500 text-red-500' : 'border-transparent text-zinc-400 hover:text-zinc-200' }}">
               <i class="fas fa-times-circle mr-2"></i> Ditolak
            </a>
            <a href="{{ route('admin.cuti.index', array_merge(request()->query(), ['tab' => 'all', 'page' => 1])) }}" 
               class="px-4 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab == 'all' ? 'border-blue-500 text-blue-500' : 'border-transparent text-zinc-400 hover:text-zinc-200' }}">
               Semua Data
            </a>
        </div>

        {{-- Form Filter (Tetap sama seperti sebelumnya) --}}
        <div class="p-6">
            <form action="{{ route('admin.cuti.index') }}" method="GET">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-zinc-400 mb-1">Karyawan</label>
                        <div class="relative">
                            <select name="user_id" id="user_id" class="w-full appearance-none bg-zinc-700 border border-zinc-600 rounded-lg pl-3 pr-10 py-2 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm cursor-pointer">
                                <option value="">Semua Karyawan</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-zinc-400 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm [color-scheme:dark]">
                    </div>
                    <div>
                        <label for="tanggal_akhir" class="block text-sm font-medium text-zinc-400 mb-1">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm [color-scheme:dark]">
                    </div>
                    <div class="flex items-end gap-2 lg:col-span-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition">Filter</button>
                        <a href="{{ route('admin.cuti.index') }}" class="bg-zinc-600 hover:bg-zinc-500 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition text-center">Reset</a>
                        <button type="submit" formaction="{{ route('admin.cuti.downloadRekapPdf') }}" formmethod="GET" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition flex items-center justify-center gap-2 whitespace-nowrap" title="Download Rekap PDF">
                            <i class="fas fa-file-pdf"></i>
                            <span>Cetak Rekap</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabel Data --}}
        <div class="relative overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-300">
                <thead class="bg-zinc-700/50 text-zinc-400 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Nama Karyawan</th>
                        <th class="px-6 py-3">Jenis Cuti</th>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center">Aksi</th> {{-- Aksi sekarang untuk PDF & Hapus --}}
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-700">
                    @forelse($cutiRequests as $cuti)
                    <tr class="hover:bg-zinc-700/50 transition group">
                        
                        {{-- UBAH: Nama Karyawan jadi Link ke Detail --}}
                        <td class="px-6 py-4 font-medium">
                            <a href="{{ route('admin.cuti.show', $cuti) }}" class="text-white hover:text-amber-400 hover:underline transition flex flex-col">
                                <span class="text-base">{{ $cuti->user->name }}</span>
                                <span class="text-xs text-zinc-500 font-normal mt-0.5 group-hover:text-amber-500/70">Klik untuk melihat detail</span>
                            </a>
                        </td>

                        <td class="px-6 py-4 capitalize">{{ $cuti->jenis_cuti }}</td>
                        <td class="px-6 py-4">
                            {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} 
                            <span class="text-zinc-500">-</span> 
                            {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                            <br>
                            <span class="text-xs text-zinc-500">
                                ({{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($cuti->tanggal_selesai)) + 1 }} hari)
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 font-semibold leading-tight rounded-full text-xs capitalize
                                @if($cuti->status == 'disetujui' || $cuti->status == 'diterima') bg-green-500/10 text-green-400
                                @elseif($cuti->status == 'ditolak') bg-red-500/10 text-red-400
                                @else bg-yellow-500/10 text-yellow-400 @endif">
                                {{ $cuti->status }}
                            </span>
                        </td>
                        
                        {{-- UBAH: Aksi hanya PDF & Hapus --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-3">
                                
                                {{-- Tombol Download PDF --}}
                                <a href="{{ route('admin.cuti.download', $cuti) }}" class="text-zinc-400 hover:text-red-400 transition" title="Download Formulir PDF">
                                    <i class="fas fa-file-pdf text-xl"></i>
                                </a>

                                {{-- Tombol Hapus --}}
                                <form action="{{ route('admin.cuti.destroy', $cuti->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data cuti ini? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-zinc-400 hover:text-red-600 transition" title="Hapus Data">
                                        <i class="fas fa-trash text-xl"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-zinc-500">
                             @if($activeTab == 'pending')
                                Tidak ada pengajuan cuti yang perlu disetujui.
                            @elseif($activeTab == 'approved')
                                Belum ada pengajuan cuti yang disetujui.
                            @elseif($activeTab == 'rejected')
                                Belum ada pengajuan cuti yang ditolak.
                            @else
                                Tidak ada data untuk ditampilkan.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Links --}}
        <div class="mt-6 p-4 border-t border-zinc-700">
            {{ $cutiRequests->links() }}
        </div>
    </div>
</x-layout-admin>