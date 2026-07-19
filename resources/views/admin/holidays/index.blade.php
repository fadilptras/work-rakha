<x-layout-admin>
    <x-slot:title>Manajemen Hari Libur</x-slot:title>

    {{-- Container Utama dengan Alpine Data untuk Modal & Drawer --}}
    <div x-data="{ 
            showDrawer: false,
            isEdit: false,
            drawerTitle: '',
            formUrl: '',
            tanggalValue: '',
            keteranganValue: '',
            isCutiValue: false,
            openCreate() {
                this.isEdit = false;
                this.drawerTitle = 'Tambah Hari Libur Baru';
                this.formUrl = '{{ route('admin.holidays.store') }}';
                this.tanggalValue = '';
                this.keteranganValue = '';
                this.isCutiValue = false;
                this.showDrawer = true;
            },
            openEdit(id, tanggal, keterangan, isCuti) {
                this.isEdit = true;
                this.drawerTitle = 'Edit Hari Libur';
                this.formUrl = '{{ route('admin.holidays.update', ':id') }}'.replace(':id', id);
                this.tanggalValue = tanggal;
                this.keteranganValue = keterangan;
                this.isCutiValue = isCuti == 1; // Convert to boolean
                this.showDrawer = true;
            }
         }">

        {{-- HEADER SECTION --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Manajemen Hari Libur</h1>
                <p class="text-zinc-400 text-sm mt-1">Atur daftar hari libur nasional dan cuti bersama perusahaan.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-zinc-800 border border-zinc-700 rounded-lg shadow-sm flex items-center gap-3">
                    <div>
                        <span class="block text-[9px] text-zinc-500 uppercase font-bold tracking-wider">Total Libur</span>
                        <span class="text-lg font-bold text-white leading-none">{{ $holidays->total() }} Hari</span>
                    </div>
                </div>
                <button @click="openCreate()" 
                    class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105 text-sm">
                    <i class="fas fa-plus mr-2"></i> Tambah Hari Libur
                </button>
            </div>
        </div>

        {{-- NOTIFIKASI SUKSES --}}
        

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

        {{-- TABEL DATA (Lebar Penuh / Full-Width) --}}
        <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700/50 overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-zinc-700/30 border-b border-zinc-700 flex justify-between items-center">
                <h2 class="text-sm font-bold text-sky-400 flex items-center">
                    <i class="fas fa-list mr-2.5"></i> Daftar Hari Libur
                </h2>
                <span class="bg-sky-600/10 text-sky-400 border border-sky-600/30 text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider">
                    Aktif
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-zinc-900/50 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-700">
                        <tr>
                            <th scope="col" class="px-6 py-4 w-16 text-center">No</th>
                            <th scope="col" class="px-6 py-4">Tanggal & Hari</th>
                            <th scope="col" class="px-6 py-4">Keterangan</th>
                            <th scope="col" class="px-6 py-4 text-center w-48">Jenis</th>
                            <th scope="col" class="px-6 py-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-700/50">
                        @forelse ($holidays as $index => $holiday)
                        <tr class="hover:bg-zinc-700/30 transition-colors group">
                            {{-- Nomor --}}
                            <td class="px-6 py-4 text-center text-zinc-500 font-mono text-xs">
                                {{ $holidays->firstItem() + $index }}
                            </td>
                            
                            {{-- Tanggal --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-white font-medium text-sm">
                                        {{ $holiday->tanggal->translatedFormat('d F Y') }}
                                    </span>
                                    <span class="text-zinc-500 text-[10px] mt-0.5 uppercase tracking-wider font-semibold">
                                        {{ $holiday->tanggal->translatedFormat('l') }}
                                    </span>
                                </div>
                            </td>

                            {{-- Keterangan --}}
                            <td class="px-6 py-4 text-zinc-300 font-medium text-xs">
                                {{ $holiday->keterangan }}
                            </td>

                            {{-- Label Jenis --}}
                            <td class="px-6 py-4 text-center">
                                @if($holiday->is_cuti_bersama)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-medium bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                        <span class="w-1 h-1 rounded-full bg-purple-400"></span>
                                        Cuti Bersama
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                        <span class="w-1 h-1 rounded-full bg-red-400"></span>
                                        Libur Nasional
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    
                                    {{-- TOMBOL EDIT (Trigger Drawer) --}}
                                    <button type="button" 
                                            @click="openEdit('{{ $holiday->id }}', '{{ $holiday->tanggal->format('Y-m-d') }}', '{{ addslashes($holiday->keterangan) }}', '{{ $holiday->is_cuti_bersama }}')"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-sky-400 hover:bg-sky-500/10 transition-all border border-transparent hover:border-sky-500/20 text-xs"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- TOMBOL HAPUS --}}
                                    <form action="{{ route('admin.holidays.destroy', $holiday->id) }}" method="POST" 
                                          onsubmit="confirmSubmit(event, 'Apakah Anda yakin ingin menghapus data libur ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-red-400 hover:bg-red-500/10 transition-all border border-transparent hover:border-red-500/20 text-xs"
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
                                        <i class="far fa-calendar-times text-2xl text-zinc-650"></i>
                                    </div>
                                    <h3 class="text-zinc-300 font-medium text-sm">Belum ada data libur</h3>
                                    <p class="text-zinc-500 text-xs mt-1">Silakan tambahkan hari libur dengan menekan tombol diatas.</p>
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

        {{-- BACKDROP OVERLAY DRAWER --}}
        <div x-show="showDrawer" style="display: none;" 
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40"
             x-transition:enter="transition ease-out duration-350"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-250"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showDrawer = false"></div>

        {{-- RIGHT SLIDE-OVER DRAWER: FORM TAMBAH/EDIT --}}
        <div x-show="showDrawer" style="display: none;" 
             class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-zinc-800 border-l border-zinc-700 shadow-2xl flex flex-col h-full"
             x-transition:enter="transition ease-out duration-350 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-250 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">
            
            {{-- Drawer Header --}}
            <div class="px-6 py-5 border-b border-zinc-700 bg-zinc-800 flex justify-between items-center flex-shrink-0">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas text-sky-400" :class="isEdit ? 'fa-edit' : 'fa-plus-circle'"></i>
                    <span x-text="drawerTitle"></span>
                </h3>
                <button @click="showDrawer = false" class="text-zinc-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            
            {{-- Drawer Form Body --}}
            <div class="p-6 overflow-y-auto custom-scrollbar flex-grow space-y-6">
                {{-- INFO BOX --}}
                <div class="bg-sky-900/30 border border-sky-700 rounded-lg p-4 flex items-start gap-3 shadow-inner">
                    <div class="text-sky-400 mt-0.5"><i class="fas fa-info-circle text-xl"></i></div>
                    <div>
                        <h4 class="text-xs font-bold text-sky-300">Kalender Kerja Operasional</h4>
                        <p class="text-[10px] text-gray-300 mt-1">Menginput tanggal libur nasional dan cuti bersama akan secara otomatis menonaktifkan sistem kehadiran mandiri karyawan pada tanggal tersebut.</p>
                    </div>
                </div>

                <form id="holiday-form" :action="formUrl" method="POST" class="space-y-5">
                    @csrf
                    
                    {{-- Inject Method PUT jika Edit --}}
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    {{-- Input Tanggal --}}
                    <div class="space-y-1.5">
                        <label for="tanggal_input" class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Tanggal Libur</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar text-zinc-500 text-sm"></i>
                            </div>
                            <input type="date" name="tanggal" id="tanggal_input" required x-model="tanggalValue"
                                   class="pl-10 w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2.5 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-xs">
                        </div>
                    </div>

                    {{-- Input Keterangan --}}
                    <div class="space-y-1.5">
                        <label for="keterangan_input" class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Keterangan Libur</label>
                        <input type="text" name="keterangan" id="keterangan_input" placeholder="Contoh: Hari Raya Idul Fitri" required x-model="keteranganValue"
                               class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-xs">
                    </div>

                    {{-- Checkbox Cuti Bersama --}}
                    <div class="pt-2">
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="checkbox" name="is_cuti_bersama" value="1" x-model="isCutiValue"
                                   class="w-5 h-5 rounded border-zinc-600 bg-zinc-900 text-sky-600 focus:ring-sky-500 focus:ring-offset-zinc-800 transition-colors">
                            <span class="ml-3 text-sm text-zinc-300 group-hover:text-white transition-colors">Cuti Bersama?</span>
                        </label>
                    </div>
                </form>
            </div>
            
            {{-- Drawer Footer --}}
            <div class="p-4 border-t border-zinc-700 bg-zinc-900/50 flex justify-end space-x-3 flex-shrink-0">
                <button type="button" @click="showDrawer = false" class="px-4 py-2 bg-zinc-700 text-gray-300 rounded-lg hover:bg-zinc-650 transition-colors text-xs">Batal</button>
                <button type="submit" form="holiday-form" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 shadow-md transition-colors text-xs font-semibold flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan Data
                </button>
            </div>
        </div>

    </div> {{-- End x-data --}}
</x-layout-admin>