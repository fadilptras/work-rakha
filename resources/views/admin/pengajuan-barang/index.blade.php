<x-layout-admin>
    <x-slot:title>Kelola Pengajuan Barang</x-slot:title>

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">Rekap Pengajuan Barang Karyawan</h1>
            <p class="text-sm text-zinc-400 mt-1">Pantau semua permintaan barang dan alat kerja.</p>
        </div>
    </div>

    {{-- [BARU] Notifikasi Sukses/Gagal --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg flex items-center gap-3 shadow-lg fade-in">
            <i class="fas fa-check-circle text-xl"></i>
            <div>
                <span class="font-bold">Berhasil!</span>
                <span class="block text-sm opacity-90">{{ session('success') }}</span>
            </div>
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
        
        {{-- TAB NAVIGATION --}}
        <div class="flex border-b border-zinc-700 px-6 mt-4">
            <a href="{{ route('admin.pengajuan_barang.index', array_merge(request()->query(), ['tab' => 'pending', 'page' => 1])) }}" 
               class="px-4 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab == 'pending' ? 'border-amber-500 text-amber-500' : 'border-transparent text-zinc-400 hover:text-zinc-200' }}">
               <i class="fas fa-clock mr-2"></i> Diproses
            </a>
            <a href="{{ route('admin.pengajuan_barang.index', array_merge(request()->query(), ['tab' => 'approved', 'page' => 1])) }}" 
               class="px-4 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab == 'approved' ? 'border-emerald-500 text-emerald-500' : 'border-transparent text-zinc-400 hover:text-zinc-200' }}">
               <i class="fas fa-check-circle mr-2"></i> Selesai
            </a>
            <a href="{{ route('admin.pengajuan_barang.index', array_merge(request()->query(), ['tab' => 'rejected', 'page' => 1])) }}" 
               class="px-4 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab == 'rejected' ? 'border-red-500 text-red-500' : 'border-transparent text-zinc-400 hover:text-zinc-200' }}">
               <i class="fas fa-times-circle mr-2"></i> Ditolak / Dibatalkan
            </a>
            <a href="{{ route('admin.pengajuan_barang.index', array_merge(request()->query(), ['tab' => 'all', 'page' => 1])) }}" 
               class="px-4 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab == 'all' ? 'border-blue-500 text-blue-500' : 'border-transparent text-zinc-400 hover:text-zinc-200' }}">
               Semua Data
            </a>
        </div>
        
        {{-- FORM FILTER --}}
        <div class="p-6">
            <form method="GET" action="{{ route('admin.pengajuan_barang.index') }}">
                <input type="hidden" name="tab" value="{{ $activeTab }}">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    
                    {{-- Dropdown Nama --}}
                    <div class="lg:col-span-2">
                        <label for="karyawan_id" class="block text-sm font-medium text-zinc-400 mb-1">Nama Karyawan</label>
                        <div class="relative">
                            <select name="karyawan_id" id="karyawan_id" class="w-full bg-zinc-700 border-zinc-600 rounded-lg text-white pl-3 pr-10 py-2 appearance-none focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Semua Karyawan</option>
                                @foreach ($karyawanList as $karyawan)
                                    <option value="{{ $karyawan->id }}" {{ request('karyawan_id') == $karyawan->id ? 'selected' : '' }}>
                                        {{ $karyawan->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Dropdown Divisi --}}
                    <div>
                        <label for="divisi" class="block text-sm font-medium text-zinc-400 mb-1">Divisi</label>
                        <div class="relative">
                            <select name="divisi" id="divisi" class="w-full bg-zinc-700 border-zinc-600 rounded-lg text-white pl-3 pr-10 py-2 appearance-none focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Semua Divisi</option>
                                @foreach ($divisiList as $item)
                                    <option value="{{ $item->divisi }}" {{ request('divisi') == $item->divisi ? 'selected' : '' }}>
                                        {{ $item->divisi }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Input Tanggal --}}
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-zinc-400 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="w-full bg-zinc-700 border-zinc-600 rounded-lg text-white px-3 py-2 [color-scheme:dark]">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-zinc-400 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="w-full bg-zinc-700 border-zinc-600 rounded-lg text-white px-3 py-2 [color-scheme:dark]">
                    </div>
                </div>

                {{-- Tombol Filter --}}
                <div class="mt-4 flex justify-end">
                    <div class="flex items-center gap-2">
                        <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-2 px-4 rounded-lg">Filter</button>
                        <a href="{{ route('admin.pengajuan_barang.index') }}" class="bg-zinc-600 hover:bg-zinc-500 text-white text-sm font-semibold py-2 px-4 rounded-lg text-center">Reset</a>
                        <button type="submit" formaction="{{ route('admin.pengajuan_barang.downloadRekapPdf') }}" formmethod="GET" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-4 rounded-lg flex items-center justify-center gap-2" title="Download Rekap PDF">
                            <i class="fas fa-file-pdf"></i>
                            <span>Cetak Rekap</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabel Data --}}
        <div class="relative overflow-x-auto">
            <table class="w-full table-fixed text-sm text-left text-zinc-300"> 
                <thead class="text-xs text-zinc-400 uppercase bg-zinc-700/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 w-[120px]">Tanggal</th> 
                        <th scope="col" class="px-6 py-3 w-[200px]">Nama Karyawan</th> 
                        <th scope="col" class="px-6 py-3 w-auto">Judul Pengajuan</th> 
                        <th scope="col" class="px-6 py-3 w-[100px]">Total Item</th> 
                        <th scope="col" class="px-6 py-3 w-[180px]">Status Final</th> 
                        <th scope="col" class="px-6 py-3 w-[100px] text-center">Aksi</th> 
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengajuanBarangs as $pengajuan)
                    <tr class="bg-zinc-800 border-b border-zinc-700 hover:bg-zinc-700/50 transition group">
                        <td class="px-6 py-4">{{ $pengajuan->created_at->format('d M Y') }}</td>
                        
                        {{-- UBAH: Nama jadi Link --}}
                        <td class="px-6 py-4 font-medium">
                            <a href="{{ route('admin.pengajuan_barang.show', $pengajuan) }}" class="text-white hover:text-amber-400 hover:underline transition flex flex-col">
                                <span class="text-base truncate">{{ $pengajuan->user->name }}</span>
                                <span class="text-xs text-zinc-500 font-normal mt-0.5 group-hover:text-amber-500/70">Klik untuk melihat detail</span>
                            </a>
                        </td>
                        
                        <td class="px-6 py-4 truncate">{{ $pengajuan->judul_pengajuan }}</td> 
                        <td class="px-6 py-4 font-mono">{{ count($pengajuan->rincian_barang ?? []) }} Item</td>
                        <td class="px-6 py-4">
                            @if ($pengajuan->status == 'selesai')
                                <span class="font-bold bg-emerald-500/10 text-emerald-400 px-2 py-1 rounded-full text-xs">Selesai</span>
                            @elseif ($pengajuan->status == 'ditolak')
                                <span class="font-bold bg-red-500/10 text-red-400 px-2 py-1 rounded-full text-xs">Ditolak</span>
                            @elseif ($pengajuan->status == 'diproses')
                                <span class="font-bold bg-blue-500/10 text-blue-400 px-2 py-1 rounded-full text-xs">Diproses Gudang</span>
                            @elseif ($pengajuan->status == 'dibatalkan')
                                <span class="font-bold bg-zinc-500/10 text-zinc-400 px-2 py-1 rounded-full text-xs">Dibatalkan</span>
                            @else
                                <span class="font-bold bg-yellow-500/10 text-yellow-400 px-2 py-1 rounded-full text-xs">Menunggu Atasan</span>
                            @endif
                        </td>
                        
                        {{-- UBAH: Aksi PDF & Delete --}}
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-3">
                                
                                {{-- Tombol Download PDF --}}
                                <a href="{{ route('admin.pengajuan_barang.downloadPdf', $pengajuan) }}" class="text-zinc-400 hover:text-red-400 transition" title="Download Formulir PDF">
                                    <i class="fas fa-file-pdf text-xl"></i>
                                </a>

                                {{-- Tombol Hapus --}}
                                <form action="{{ route('admin.pengajuan_barang.destroy', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengajuan ini? Tindakan ini tidak dapat dibatalkan.');">
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
                        <td colspan="6" class="px-6 py-10 text-center text-zinc-500">
                            @if($activeTab == 'pending')
                                Tidak ada pengajuan barang yang perlu diproses saat ini.
                            @elseif($activeTab == 'approved')
                                Belum ada pengajuan barang yang selesai.
                            @elseif($activeTab == 'rejected')
                                Belum ada pengajuan barang yang ditolak.
                            @else
                                Tidak ada data untuk ditampilkan.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-zinc-700">
            {{ $pengajuanBarangs->links() }}
        </div>
    </div>
</x-layout-admin>