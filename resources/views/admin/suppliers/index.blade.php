<x-layout-admin>
    <x-slot:title>Manajemen Supplier</x-slot:title>

    {{-- Container Utama dengan Alpine Data untuk Modal & Drawer --}}
    <div x-data="{ 
            showDrawer: false,
            isEdit: false,
            drawerTitle: '',
            formUrl: '',
            
            // Form Values
            namaValue: '',
            pic1Value: '',
            kontak1Value: '',
            pic2Value: '',
            kontak2Value: '',
            alamatValue: '',
            
            openCreate() {
                this.isEdit = false;
                this.drawerTitle = 'Tambah Supplier Baru';
                this.formUrl = '{{ route('admin.suppliers.store') }}';
                
                this.namaValue = '';
                this.pic1Value = '';
                this.kontak1Value = '';
                this.pic2Value = '';
                this.kontak2Value = '';
                this.alamatValue = '';
                
                this.showDrawer = true;
            },
            openEdit(id, nama, pic1, kontak1, pic2, kontak2, alamat) {
                this.isEdit = true;
                this.drawerTitle = 'Edit Data Supplier';
                this.formUrl = '{{ route('admin.suppliers.update', ':id') }}'.replace(':id', id);
                
                this.namaValue = nama;
                this.pic1Value = pic1;
                this.kontak1Value = kontak1;
                this.pic2Value = pic2;
                this.kontak2Value = kontak2;
                this.alamatValue = alamat;
                
                this.showDrawer = true;
            }
         }">

        {{-- HEADER SECTION --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Manajemen Supplier</h1>
                <p class="text-zinc-400 text-sm mt-1">Atur daftar supplier dan partner penyedia barang/jasa perusahaan.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button @click="openCreate()" 
                    class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105 text-sm">
                    <i class="fas fa-plus mr-2"></i> Tambah Supplier
                </button>
            </div>
        </div>

        {{-- ERROR VALIDASI & SUKSES --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-lg shadow-lg">
                {{ session('success') }}
            </div>
        @endif

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
                    <i class="fas fa-list mr-2.5"></i> Daftar Supplier
                </h2>
                <span class="bg-sky-600/10 text-sky-400 border border-sky-600/30 text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider">
                    Total: {{ $suppliers->count() }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-zinc-900/50 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-700">
                        <tr>
                            <th scope="col" class="px-6 py-4 w-16 text-center">No</th>
                            <th scope="col" class="px-6 py-4">Nama Supplier</th>
                            <th scope="col" class="px-6 py-4">PIC 1 & Kontak</th>
                            <th scope="col" class="px-6 py-4">PIC 2 & Kontak</th>
                            <th scope="col" class="px-6 py-4">Alamat</th>
                            <th scope="col" class="px-6 py-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-700/50">
                        @forelse ($suppliers as $index => $supplier)
                        <tr class="hover:bg-zinc-700/30 transition-colors group">
                            {{-- Nomor --}}
                            <td class="px-6 py-4 text-center text-zinc-500 font-mono text-xs">
                                {{ $index + 1 }}
                            </td>
                            
                            {{-- Nama --}}
                            <td class="px-6 py-4">
                                <span class="text-white font-medium text-sm">
                                    {{ $supplier->nama_supplier }}
                                </span>
                            </td>

                            {{-- PIC 1 --}}
                            <td class="px-6 py-4 text-zinc-300 font-medium text-xs">
                                <div class="flex flex-col gap-1">
                                    <span><i class="fas fa-user text-zinc-500 w-4"></i> {{ $supplier->pic_1 ?: '-' }}</span>
                                    @if($supplier->kontak_pic1)
                                        <span class="text-sky-400"><i class="fas fa-phone-alt text-zinc-500 w-4"></i> {{ $supplier->kontak_pic1 }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- PIC 2 --}}
                            <td class="px-6 py-4 text-zinc-300 font-medium text-xs">
                                <div class="flex flex-col gap-1">
                                    <span><i class="fas fa-user text-zinc-500 w-4"></i> {{ $supplier->pic_2 ?: '-' }}</span>
                                    @if($supplier->kontak_pic2)
                                        <span class="text-sky-400"><i class="fas fa-phone-alt text-zinc-500 w-4"></i> {{ $supplier->kontak_pic2 }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Alamat --}}
                            <td class="px-6 py-4 text-zinc-400 text-xs">
                                {{ $supplier->alamat ?: '-' }}
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    
                                    {{-- TOMBOL EDIT (Trigger Drawer) --}}
                                    <button type="button" 
                                            @click="openEdit('{{ $supplier->id }}', '{{ addslashes($supplier->nama_supplier) }}', '{{ addslashes($supplier->pic_1) }}', '{{ addslashes($supplier->kontak_pic1) }}', '{{ addslashes($supplier->pic_2) }}', '{{ addslashes($supplier->kontak_pic2) }}', '{{ preg_replace('/\r|\n/', ' ', addslashes($supplier->alamat)) }}')"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-sky-400 hover:bg-sky-500/10 transition-all border border-transparent hover:border-sky-500/20 text-xs"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- TOMBOL HAPUS --}}
                                    <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data supplier ini?');">
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
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-box-open text-4xl mb-3 text-zinc-600"></i>
                                    <p class="text-sm">Belum ada data supplier.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- BACKDROP OVERLAY DRAWER --}}
        <div x-show="showDrawer" style="display: none;" 
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showDrawer = false"></div>

        {{-- RIGHT SLIDE-OVER DRAWER: FORM TAMBAH/EDIT --}}
        <div x-show="showDrawer" style="display: none;" 
             class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-zinc-800 border-l border-zinc-700 shadow-2xl flex flex-col h-full"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
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
                
                <form id="supplier-form" :action="formUrl" method="POST" class="space-y-4">
                    @csrf
                    
                    {{-- Inject Method PUT jika Edit --}}
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    {{-- Input Nama Supplier --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Nama Supplier / PT <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_supplier" required x-model="namaValue" placeholder="Contoh: PT. Sumber Makmur"
                               class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                    </div>

                    {{-- Input PIC 1 --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">PIC 1 (Nama Kontak)</label>
                        <input type="text" name="pic_1" x-model="pic1Value" placeholder="Contoh: Bpk. Budi"
                               class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                    </div>
                    
                    {{-- Input Kontak PIC 1 --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Kontak PIC 1</label>
                        <input type="text" name="kontak_pic1" x-model="kontak1Value" placeholder="Contoh: 081234567890"
                               class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                    </div>

                    {{-- Input PIC 2 --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">PIC 2 (Opsional)</label>
                        <input type="text" name="pic_2" x-model="pic2Value" placeholder="Contoh: Ibu Ani"
                               class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                    </div>
                    
                    {{-- Input Kontak PIC 2 --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Kontak PIC 2 (Opsional)</label>
                        <input type="text" name="kontak_pic2" x-model="kontak2Value" placeholder="Contoh: 089876543210"
                               class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                    </div>

                    {{-- Input Alamat --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Alamat Lengkap</label>
                        <textarea name="alamat" x-model="alamatValue" rows="3" placeholder="Masukkan alamat lengkap supplier..."
                                  class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm custom-scrollbar"></textarea>
                    </div>

                </form>
            </div>
            
            {{-- Drawer Footer --}}
            <div class="p-4 border-t border-zinc-700 bg-zinc-900/50 flex justify-end space-x-3 flex-shrink-0">
                <button type="button" @click="showDrawer = false" class="px-4 py-2 bg-zinc-700 text-gray-300 rounded-lg hover:bg-zinc-650 transition-colors text-xs font-semibold">Batal</button>
                <button type="submit" form="supplier-form" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 shadow-md transition-colors text-xs font-bold flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan Data
                </button>
            </div>
            
        </div>
    </div>
</x-layout-admin>
