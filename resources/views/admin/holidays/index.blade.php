<x-layout-admin>
    <x-slot:title>Manajemen Hari Libur</x-slot:title>

    {{-- Container Utama dengan Alpine Data untuk Modal --}}
    <div x-data="{ 
            showEditModal: false,
            editId: '',
            editTanggal: '',
            editKeterangan: '',
            editIsCuti: false,
            editUrl: '',
            openEdit(id, tanggal, keterangan, isCuti) {
                this.editId = id;
                this.editTanggal = tanggal;
                this.editKeterangan = keterangan;
                this.editIsCuti = isCuti == 1; // Convert to boolean
                this.editUrl = '{{ route('admin.holidays.update', ':id') }}'.replace(':id', id);
                this.showEditModal = true;
            }
         }">

        {{-- HEADER SECTION --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Manajemen Hari Libur</h1>
                <p class="text-zinc-400 text-sm mt-1">Atur daftar hari libur nasional dan cuti bersama.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg shadow-sm">
                    <span class="block text-xs text-zinc-500 uppercase font-bold">Total Libur</span>
                    <span class="text-lg font-bold text-white">{{ $holidays->total() }}</span>
                </div>
            </div>
        </div>

        {{-- NOTIFIKASI SUKSES --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-400 hover:text-emerald-300"><i class="fas fa-times"></i></button>
        </div>
        @endif

        {{-- ERROR VALIDASI --}}
        @if ($errors->any())
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg shadow-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- CARD: FORM TAMBAH DATA --}}
        <div class="mb-8 bg-zinc-800 rounded-xl shadow-lg border border-zinc-700/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-700/50 bg-zinc-800/50 flex items-center gap-2">
                <div class="p-1.5 bg-indigo-500/10 rounded-lg text-indigo-400">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h2 class="font-semibold text-white">Tambah Hari Libur Baru</h2>
            </div>
            
            <div class="p-6">
                <form action="{{ route('admin.holidays.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                        
                        {{-- Input Tanggal --}}
                        <div class="md:col-span-3 space-y-1.5">
                            <label for="tanggal" class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Tanggal</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar text-zinc-500"></i>
                                </div>
                                <input type="date" name="tanggal" id="tanggal" required
                                       class="pl-10 w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2.5 text-white placeholder-zinc-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm">
                            </div>
                        </div>

                        {{-- Input Keterangan --}}
                        <div class="md:col-span-5 space-y-1.5">
                            <label for="keterangan" class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Keterangan Libur</label>
                            <input type="text" name="keterangan" id="keterangan" placeholder="Contoh: Hari Raya Idul Fitri" required
                                   class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm">
                        </div>

                        {{-- Checkbox Cuti Bersama --}}
                        <div class="md:col-span-2 pb-3">
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="checkbox" name="is_cuti_bersama" value="1" 
                                       class="w-5 h-5 rounded border-zinc-600 bg-zinc-900 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-zinc-800 transition-colors">
                                <span class="ml-3 text-sm text-zinc-300 group-hover:text-white transition-colors">Cuti Bersama?</span>
                            </label>
                        </div>

                        {{-- Tombol Simpan --}}
                        <div class="md:col-span-2">
                            <button type="submit" 
                                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 px-4 rounded-lg shadow-lg hover:shadow-indigo-500/20 transition-all duration-200 flex justify-center items-center gap-2 transform active:scale-95">
                                <i class="fas fa-save"></i>
                                <span>Simpan</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABEL DATA --}}
        <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700/50 overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-zinc-900/50 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-700">
                        <tr>
                            <th scope="col" class="px-6 py-4 w-16 text-center">No</th>
                            <th scope="col" class="px-6 py-4">Tanggal & Hari</th>
                            <th scope="col" class="px-6 py-4">Keterangan</th>
                            <th scope="col" class="px-6 py-4 text-center">Jenis</th>
                            <th scope="col" class="px-6 py-4 text-center w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-700/50">
                        @forelse ($holidays as $index => $holiday)
                        <tr class="hover:bg-zinc-700/30 transition-colors group">
                            {{-- Nomor --}}
                            <td class="px-6 py-4 text-center text-zinc-500 font-mono">
                                {{ $holidays->firstItem() + $index }}
                            </td>
                            
                            {{-- Tanggal --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-white font-medium text-base">
                                        {{ $holiday->tanggal->translatedFormat('d F Y') }}
                                    </span>
                                    <span class="text-zinc-500 text-xs mt-0.5">
                                        {{ $holiday->tanggal->translatedFormat('l') }}
                                    </span>
                                </div>
                            </td>

                            {{-- Keterangan --}}
                            <td class="px-6 py-4 text-zinc-300 font-medium">
                                {{ $holiday->keterangan }}
                            </td>

                            {{-- Label Jenis --}}
                            <td class="px-6 py-4 text-center">
                                @if($holiday->is_cuti_bersama)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                                        Cuti Bersama
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                        Libur Nasional
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    
                                    {{-- TOMBOL EDIT (Trigger Modal) --}}
                                    <button type="button" 
                                            @click="openEdit('{{ $holiday->id }}', '{{ $holiday->tanggal->format('Y-m-d') }}', '{{ addslashes($holiday->keterangan) }}', '{{ $holiday->is_cuti_bersama }}')"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-amber-400 hover:bg-amber-500/10 transition-all border border-transparent hover:border-amber-500/20"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- TOMBOL HAPUS --}}
                                    <form action="{{ route('admin.holidays.destroy', $holiday->id) }}" method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data libur ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-red-400 hover:bg-red-500/10 transition-all border border-transparent hover:border-red-500/20"
                                                title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-zinc-800 rounded-full flex items-center justify-center mb-4">
                                        <i class="far fa-calendar-times text-3xl text-zinc-600"></i>
                                    </div>
                                    <h3 class="text-zinc-300 font-medium text-lg">Belum ada data libur</h3>
                                    <p class="text-zinc-500 text-sm mt-1">Silakan tambahkan hari libur melalui form di atas.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($holidays->hasPages())
            <div class="px-6 py-4 border-t border-zinc-700 bg-zinc-800/50">
                {{ $holidays->links() }}
            </div>
            @endif
        </div>

        {{-- MODAL EDIT (Hidden by default, shown via Alpine) --}}
        <div x-show="showEditModal" style="display: none;" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-zinc-800 rounded-xl border border-zinc-700 shadow-2xl w-full max-w-md overflow-hidden"
                 @click.away="showEditModal = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-zinc-700 bg-zinc-800 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fas fa-edit text-amber-500"></i> Edit Hari Libur
                    </h3>
                    <button @click="showEditModal = false" class="text-zinc-400 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Modal Body (Form Edit) --}}
                <form :action="editUrl" method="POST" class="p-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-4">
                        {{-- Edit Tanggal --}}
                        <div>
                            <label class="block text-xs font-semibold text-zinc-400 uppercase mb-1">Tanggal</label>
                            <input type="date" name="tanggal" x-model="editTanggal" required
                                   class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        </div>

                        {{-- Edit Keterangan --}}
                        <div>
                            <label class="block text-xs font-semibold text-zinc-400 uppercase mb-1">Keterangan</label>
                            <input type="text" name="keterangan" x-model="editKeterangan" required
                                   class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        </div>

                        {{-- Edit Cuti Bersama --}}
                        <div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_cuti_bersama" value="1" x-model="editIsCuti"
                                       class="w-5 h-5 rounded border-zinc-600 bg-zinc-900 text-amber-500 focus:ring-amber-500 focus:ring-offset-zinc-800">
                                <span class="ml-2 text-sm text-zinc-300">Cuti Bersama?</span>
                            </label>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showEditModal = false" 
                                class="px-4 py-2 rounded-lg text-sm font-medium text-zinc-300 hover:text-white hover:bg-zinc-700 transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 rounded-lg text-sm font-bold bg-amber-600 hover:bg-amber-500 text-white shadow-lg shadow-amber-500/20 transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div> {{-- End x-data --}}
</x-layout-admin>