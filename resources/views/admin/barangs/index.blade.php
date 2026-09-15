<x-layout-admin>
    <x-slot:title>Data Barang</x-slot:title>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(39, 39, 42, 0.5); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #52525b; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #71717a; }
        .chip { transition: all .15s ease; }
        .chip:hover { transform: translateY(-1px); }
    </style>
    @endpush

    {{-- Data aturan package -> Alpine global (sumber sama dgn form pricing) --}}
    <script>
        window.packagingCatalog = @js($catalog['catalog']);
        window.packagingSuggestions = @js($catalog['suggestions']);
        window.packagingPresets = @js($catalog['presets']);
    </script>

    <div x-data="{
            showDrawer: false,
            isEdit: false,
            drawerTitle: '',
            formUrl: '',

            // Form Values (master)
            kodeValue: '',
            namaValue: '',
            satuanValue: '',

            // Form Values (kurasi produk) — dipakai di drawer Tambah/Edit
            cat: window.packagingCatalog,
            suggestions: window.packagingSuggestions,
            presets: window.packagingPresets,
            cleanValue: '',
            pcsPerUnit: 1,
            fillUnit: 'Pcs',

            metaFor(u) { return this.cat[(u || '').trim().toLowerCase()] || { type: 'flex', units: ['Pcs'] }; },
            typeFor(u) { return this.metaFor(u).type; },
            canFill(u) { return this.typeFor(u) !== 'single'; },
            onUnitChange() {
                const meta = this.metaFor(this.satuanValue);
                if (this.typeFor(this.satuanValue) === 'single') this.pcsPerUnit = 1;
                if (meta.units.length > 0 && !meta.units.includes(this.fillUnit)) this.fillUnit = meta.units[0];
            },
            onClearUnit() {
                this.pcsPerUnit = 1;
                this.fillUnit = 'Pcs';
            },
            autofillClean() {
                const ref = (this.namaValue || '').trim();
                if (!ref) return;
                const typed = (this.cleanValue || '').trim();
                if (typed === '' || ref.toLowerCase().startsWith(typed.toLowerCase())) {
                    this.cleanValue = ref;
                }
            },
            isi() { const v = parseInt(this.pcsPerUnit, 10); return isNaN(v) ? 0 : v; },
            packQty() { const isi = this.isi(); return (!this.canFill(this.satuanValue) || isi < 1) ? 1 : isi; },
            previewPackaging() {
                const pkg = (this.satuanValue || '').trim();
                const isi = this.isi();
                const fill = this.fillUnit || 'Pcs';
                if (!pkg) return isi > 0 ? isi + ' ' + fill : 'General';
                if (this.typeFor(pkg) === 'single' || isi < 1) return pkg;
                return pkg + '/ ' + isi + ' ' + fill;
            },

            openCreate() {
                this.isEdit = false;
                this.drawerTitle = 'Tambah Barang';
                this.formUrl = '{{ route('admin.barangs.store') }}';

                this.kodeValue = '';
                this.namaValue = '';
                this.satuanValue = '';

                this.cleanValue = '';
                this.pcsPerUnit = 1;
                this.fillUnit = 'Pcs';

                this.showDrawer = true;
            },
            openEdit(id, kode, nama, satuan, clean, pcs, fillUnit) {
                this.isEdit = true;
                this.drawerTitle = 'Edit Barang';
                this.formUrl = '{{ route('admin.barangs.update', ':id') }}'.replace(':id', id);

                this.kodeValue = kode;
                this.namaValue = nama;
                this.satuanValue = satuan;

                this.cleanValue = clean;
                this.pcsPerUnit = (pcs >= 1) ? pcs : 1;
                this.fillUnit = fillUnit;

                this.showDrawer = true;
            },

            // ===== Aturan Packaging (master product_packaging) =====
            showPackagingModal: false,
            packagingLoading: false,
            packagingSaving: false,
            packagingEditId: null,
            packagingName: '',
            packInputs: [''],
            packRows: [],

            packagingTypeValue() {
                const items = this.packInputs.map(v => String(v || '').trim()).filter(v => v !== '');
                if (items.length === 0) return 'single';
                if (items.length === 1) return 'fixed';
                return 'flex';
            },

            packagingTypeHint() {
                const items = this.packInputs.map(v => String(v || '').trim()).filter(v => v !== '');
                if (items.length === 0) return 'isi otomatis 1';
                if (items.length === 1) return 'wajib isi';
                return 'bisa isi (pilih salah satu)';
            },

            packTypeLabel(pack) {
                const items = (pack || []).filter(v => String(v || '').trim() !== '');
                if (items.length === 0) return 'single';
                if (items.length === 1) return 'fixed';
                return 'flex';
            },

            openPackagingModal() {
                this.showPackagingModal = true;
                this.resetPackagingForm();
                this.loadPackagings();
            },

            closePackagingModal() {
                this.showPackagingModal = false;
            },

            resetPackagingForm() {
                this.packagingEditId = null;
                this.packagingName = '';
                this.packInputs = [''];
            },

            async loadPackagings() {
                this.packagingLoading = true;
                try {
                    const res = await fetch('{{ route('admin.barangs.packagings.index') }}', {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) }
                    });
                    const json = await res.json();
                    this.packRows = json.data || [];
                } catch (e) {
                    console.error('load packagings error', e);
                } finally {
                    this.packagingLoading = false;
                }
            },

            addPackInput() {
                this.packInputs.push('');
            },

            removePackInput(index) {
                if (this.packInputs.length > 1) this.packInputs.splice(index, 1);
            },

            editPackaging(row) {
                this.packagingEditId = row.id;
                this.packagingName = row.packaging;
                this.packInputs = (row.pack && row.pack.length > 0) ? [...row.pack] : [''];
            },

            async savePackaging() {
                const pack = this.packInputs.map(v => String(v || '').trim()).filter(v => v !== '');
                if (!this.packagingName.trim()) {
                    Swal.fire('Perhatian', 'Nama kemasan wajib diisi.', 'warning');
                    return;
                }
                this.packagingSaving = true;
                const isEdit = this.packagingEditId !== null;
                const url = isEdit
                    ? '{{ route('admin.barangs.packagings.update', ':id') }}'.replace(':id', this.packagingEditId)
                    : '{{ route('admin.barangs.packagings.store') }}';
                try {
                    const res = await fetch(url, {
                        method: isEdit ? 'PUT' : 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @js(csrf_token())
                        },
                        body: JSON.stringify({ packaging: this.packagingName.trim(), pack })
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        const msg = json.errors ? Object.values(json.errors).flat().join(', ') : (json.message || 'Gagal menyimpan.');
                        Swal.fire('Error', msg, 'error');
                        return;
                    }
                    this.resetPackagingForm();
                    await this.loadPackagings();
                    Swal.fire('Berhasil', json.message, 'success');
                } catch (e) {
                    Swal.fire('Error', 'Terjadi kesalahan saat menyimpan.', 'error');
                } finally {
                    this.packagingSaving = false;
                }
            },

            async deletePackaging(row) {
                const confirmRes = await Swal.fire({
                    title: 'Hapus Aturan Packaging?',
                    text: 'Aturan packaging akan dihapus permanen. Data produk terkait tidak terpengaruh.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                });
                if (!confirmRes.isConfirmed) return;
                try {
                    const res = await fetch('{{ route('admin.barangs.packagings.destroy', ':id') }}'.replace(':id', row.id), {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) }
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        Swal.fire('Error', json.message || 'Gagal menghapus.', 'error');
                        return;
                    }
                    await this.loadPackagings();
                    Swal.fire('Berhasil', json.message, 'success');
                } catch (e) {
                    Swal.fire('Error', 'Terjadi kesalahan saat menghapus.', 'error');
                }
            }
        }">

        {{-- HEADER --}}
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Data Barang</h1>
                <p class="text-zinc-400 text-sm mt-1">
                    Master barang beserta aturan produk (penulisan bersih &amp; satuan jual).
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="openPackagingModal()"
                    class="bg-zinc-700 hover:bg-zinc-600 text-white font-bold py-2.5 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105 text-sm">
                    <i class="fas fa-boxes mr-2"></i> Aturan Packaging
                </button>
                <button @click="openCreate()"
                    class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105 text-sm">
                    <i class="fas fa-plus mr-2"></i> Tambah Barang
                </button>
            </div>
        </div>

        {{-- RINGKASAN + SEARCH (satu baris toolbar) --}}
        <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.barangs.index') }}" class="flex items-center gap-2 w-full lg:flex-1 lg:max-w-2xl">
                <div class="relative flex-1 min-w-[240px]">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-sm pointer-events-none"></i>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari kode / nama / nama bersih..."
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-lg pl-11 pr-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                </div>
                <button type="submit" class="bg-zinc-700 hover:bg-zinc-600 text-white font-bold py-2.5 px-4 rounded-lg text-sm transition-colors flex-shrink-0">Cari</button>
                @if($search !== '')
                    <a href="{{ route('admin.barangs.index') }}" class="text-zinc-500 hover:text-white text-sm font-semibold flex-shrink-0">Reset</a>
                @endif
            </form>

            <div class="inline-flex items-center gap-2 bg-zinc-800 border border-zinc-700/50 rounded-lg px-4 py-2 flex-shrink-0 self-start lg:self-auto">
                <div class="w-8 h-8 rounded-lg bg-sky-500/10 border border-sky-600/30 flex items-center justify-center text-sky-400 flex-shrink-0">
                    <i class="fas fa-layer-group text-xs"></i>
                </div>
                <p class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Total Barang</p>
                <p class="text-base font-bold text-white leading-none">{{ $total }}</p>
            </div>
        </div>

        {{-- FLASH --}}
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

        {{-- TABEL DATA --}}
        <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700/50 overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-zinc-700/30 border-b border-zinc-700 flex justify-between items-center">
                <h2 class="text-sm font-bold text-sky-400 flex items-center">
                    <i class="fas fa-box mr-2.5"></i> Daftar Barang
                </h2>
                <span class="bg-sky-600/10 text-sky-400 border border-sky-600/30 text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider">
                    Total: {{ count($filtered) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left table-fixed">
                    <thead class="bg-zinc-900/50 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-700">
                        <tr>
                            <th scope="col" class="px-6 py-4 w-14 text-center">No</th>
                            <th scope="col" class="px-6 py-4 w-36">Kode Barang</th>
                            <th scope="col" class="px-6 py-4 w-56">Nama Barang <span class="text-zinc-600 normal-case">(Accurate)</span></th>
                            <th scope="col" class="px-6 py-4 w-56">Nama Barang <span class="text-zinc-600 normal-case">(Bersih)</span></th>
                            <th scope="col" class="px-6 py-4 w-40">Packaging</th>
                            <th scope="col" class="px-6 py-4 text-right w-32">Stock</th>
                            <th scope="col" class="px-6 py-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-700/50">
                        @forelse ($filtered as $index => $barang)
                            @php
                                $product = $productMap[$barang->product_code] ?? null;
                                $pcsPerUnit = (int) ($product->pcs_per_unit ?? 0);
                                $fillUnit = ($product->fill_unit ?? 'Pcs') ?: 'Pcs';
                                $stockValue = $product->stock ?? $barang->stock ?? 0;
                                $pkgUnit = $barang->unit ?: ($product->unit ?? null);
                                $isSingleUnit = $pkgUnit && \App\Support\PackagingCatalog::isSingle($pkgUnit);
                                $stockUnit = $isSingleUnit ? $pkgUnit : $fillUnit;
                                $showPackQty = $barang->unit && !$isSingleUnit && $pcsPerUnit > 1;
                                $stockDisplay = number_format($stockValue, 0, ',', '.') . ' ' . $stockUnit;
                            @endphp
                        <tr class="hover:bg-zinc-700/30 transition-colors group align-top">
                            <td class="px-6 py-4 text-center text-zinc-500 font-mono text-xs">
                                {{ $index + 1 }}
                            </td>

                            {{-- Accurate: Kode Barang --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center bg-zinc-900/70 border border-zinc-700 text-zinc-300 text-[11px] font-mono px-2 py-1 rounded break-all">
                                    {{ $barang->product_code ?: '—' }}
                                </span>
                            </td>

                            {{-- Accurate: Nama Barang --}}
                            <td class="px-6 py-4 text-zinc-400 break-words">
                                {{ $barang->product_name }}
                            </td>

                            {{-- Nama Barang (bersih) - bold --}}
                            <td class="px-6 py-4 break-words">
                                @if($product && $product->product_name_clean)
                                    <span class="text-white font-semibold break-words">{{ $product->product_name_clean }}</span>
                                @else
                                    <span class="text-zinc-600 italic text-xs">—</span>
                                @endif
                            </td>

                            {{-- Packaging (Package + QTY/Package digabung) --}}
                            <td class="px-6 py-4 text-zinc-300">
                                @if($barang->unit)
                                    <span class="inline-flex items-center gap-1 bg-sky-500/10 text-sky-400 border border-sky-600/30 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                        {{ $barang->unit }}
                                        @if($showPackQty)
                                            <span class="text-sky-400/50">/</span> {{ $pcsPerUnit }} {{ $fillUnit }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-zinc-600 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Stock --}}
                            <td class="px-6 py-4 text-right text-white font-semibold">
                                {{ $stockDisplay }}
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                            @click="openEdit('{{ $barang->id }}', '{{ addslashes($barang->product_code) }}', '{{ addslashes($barang->product_name) }}', '{{ addslashes($barang->unit) }}', '{{ addslashes($product->product_name_clean ?? '') }}', {{ (int) ($product->pcs_per_unit ?? 1) }}, '{{ addslashes($product->fill_unit ?? 'Pcs') }}')"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-sky-400 hover:bg-sky-500/10 transition-all border border-transparent hover:border-sky-500/20 text-xs"
                                            title="Edit (master + aturan produk)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.barangs.destroy', $barang) }}" method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data barang ini?');">
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
                            <td colspan="7" class="px-6 py-12 text-center text-zinc-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-box-open text-4xl mb-3 text-zinc-600"></i>
                                    <p class="text-sm">{{ $search !== '' ? 'Tidak ada barang yang cocok.' : 'Belum ada data barang.' }}</p>
                                    @if($search !== '' || $filter !== 'all')
                                        <a href="{{ route('admin.barangs.index') }}" class="mt-3 text-sky-400 hover:text-sky-300 text-xs font-bold">Reset filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <datalist id="atp-packaging-suggestions">
            @foreach($catalog['suggestions'] as $sug)
                <option value="{{ $sug }}"></option>
            @endforeach
        </datalist>

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

        {{-- RIGHT SLIDE-OVER DRAWER: FORM TAMBAH/EDIT BARANG + ATURAN PRODUK --}}
        <div x-show="showDrawer" style="display: none;"
             class="fixed inset-y-0 right-0 z-50 w-full max-w-lg bg-zinc-800 border-l border-zinc-700 shadow-2xl flex flex-col h-full"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">

            <div class="px-6 py-5 border-b border-zinc-700 bg-zinc-800 flex justify-between items-center flex-shrink-0">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fas text-sky-400" :class="isEdit ? 'fa-edit' : 'fa-plus-circle'"></i>
                    <span x-text="drawerTitle"></span>
                </h3>
                <button @click="showDrawer = false" class="text-zinc-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>

            <div class="px-6 py-5 overflow-y-auto custom-scrollbar flex-grow">

                <form id="barang-form" :action="formUrl" method="POST" class="space-y-6">
                    @csrf

                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- ============ DATA ACCURATE ============ --}}
                    <div>
                        <h4 class="text-xs font-bold text-zinc-300 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="fas fa-database text-sky-400"></i> Data Accurate
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Kode Barang</label>
                                <input type="text" name="product_code" x-model="kodeValue" placeholder="Contoh: BRG-001"
                                       class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Nama Barang <span class="text-red-500">*</span></label>
                                <input type="text" name="product_name" required x-model="namaValue" placeholder="Contoh: Kertas A4"
                                       class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-zinc-700"></div>

                    {{-- ============ ATURAN PRODUK (KURASI) ============ --}}
                    <div>
                        <h4 class="text-xs font-bold text-zinc-300 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="fas fa-tag text-sky-400"></i> Aturan Produk
                            <span class="normal-case font-medium text-zinc-500 text-[10px]">(opsional, untuk penawaran harga &amp; SPH)</span>
                        </h4>

                        <div class="space-y-4">
                            {{-- 1. Nama Produk (bersih) --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Nama Produk</label>
                                <div class="relative rounded-lg bg-zinc-900 border border-zinc-700 focus-within:border-sky-500 focus-within:ring-1 focus-within:ring-sky-500">
                                    <span class="pointer-events-none absolute inset-y-0 left-4 right-4 flex items-center truncate text-sm text-zinc-500"
                                          x-text="namaValue || ''"
                                          x-show="namaValue && cleanValue !== namaValue"></span>
                                    <input type="text" name="product_name_clean" x-model="cleanValue"
                                           @keydown.tab="autofillClean()"
                                           :placeholder="namaValue ? ' ' : 'Contoh: Nama barang tanpa singkatan/typo'"
                                           class="relative w-full bg-transparent px-4 py-2 text-white placeholder-zinc-500 text-sm focus:outline-none">
                                </div>
                            </div>

                            {{-- 2. Packaging (unit) --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Packaging</label>
                                <div class="relative">
                                    <input type="text" name="unit" x-model="satuanValue" @input="onUnitChange()" list="atp-packaging-suggestions"
                                           placeholder="Pcs / Pack / Box / Roll / Polybag..."
                                           class="w-full bg-zinc-900 border border-zinc-700 rounded-lg pl-4 pr-9 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                                    <button type="button" x-show="satuanValue" x-cloak @click="onClearUnit()" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300" title="Hapus packaging">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="pkg in suggestions" :key="pkg">
                                        <button type="button" @click="satuanValue = pkg; onUnitChange()"
                                                :class="satuanValue === pkg ? 'bg-sky-600 text-white border-sky-600' : 'bg-zinc-700/50 text-zinc-400 border-zinc-600/50 hover:bg-sky-500/20 hover:text-sky-400'"
                                                class="chip px-2 py-0.5 rounded border text-[10px] font-bold" x-text="pkg"></button>
                                    </template>
                                </div>
                            </div>

                            {{-- 3. QTY / Pack --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider flex items-center justify-between">
                                    <span>QTY / Pack</span>
                                    <span x-show="!canFill(satuanValue)" class="normal-case text-[10px] text-zinc-500">satuan tunggal → otomatis 1</span>
                                </label>
                                <div x-show="canFill(satuanValue)">
                                    <div class="flex items-center gap-1.5">
                                        <input type="number" x-model.number="pcsPerUnit" name="pcs_per_unit"
                                               min="0" placeholder="e.g. 100"
                                               class="w-28 shrink-0 border rounded-lg px-3 py-2 text-white bg-zinc-900 border-zinc-700 focus:outline-none focus:ring-1 focus:ring-sky-500 text-sm text-right font-bold">
                                        <span class="text-zinc-600 text-sm font-bold">×</span>
                                        <template x-if="metaFor(satuanValue).units.length > 1">
                                            <select x-model="fillUnit" name="fill_unit" class="flex-1 min-w-0 bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-sky-500 text-sm">
                                                <template x-for="u in metaFor(satuanValue).units" :key="u">
                                                    <option :value="u" x-text="u"></option>
                                                </template>
                                            </select>
                                        </template>
                                        <template x-if="metaFor(satuanValue).units.length <= 1">
                                            <input type="hidden" name="fill_unit" :value="fillUnit || 'Pcs'">
                                        </template>
                                        <span x-show="metaFor(satuanValue).units.length <= 1" class="flex-1 text-sm font-bold text-zinc-400 uppercase" x-text="metaFor(satuanValue).units[0] || 'Pcs'"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        <template x-for="qty in presets" :key="qty">
                                            <button type="button" @click="pcsPerUnit = qty"
                                                    :class="pcsPerUnit === qty ? 'bg-sky-600 text-white border-sky-600' : 'bg-zinc-700/50 text-zinc-400 border-zinc-600/50 hover:bg-sky-500/20 hover:text-sky-400'"
                                                    class="chip px-2 py-0.5 rounded border text-[10px] font-bold" x-text="qty"></button>
                                        </template>
                                    </div>
                                </div>
                                <p x-show="!canFill(satuanValue)" class="text-[10px] text-sky-400 font-semibold">Satuan tunggal (Pcs / Roll / Botol) — isi otomatis 1.</p>
                                <p class="text-[10px] text-sky-400 font-bold hidden sm:block">
                                    Stored: <span class="text-zinc-300" x-text="previewPackaging()"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-4 border-t border-zinc-700 bg-zinc-900/50 flex items-center justify-between space-x-3 flex-shrink-0">
                <p class="text-[11px] text-sky-400 font-bold hidden sm:block">
                    <span class="text-zinc-600">Kemasan:</span> <span x-text="previewPackaging()"></span>
                </p>
                <div class="flex items-center space-x-3">
                    <button type="button" @click="showDrawer = false" class="px-4 py-2 bg-zinc-700 text-gray-300 rounded-lg hover:bg-zinc-600 transition-colors text-xs font-semibold">Batal</button>
                    <button type="submit" form="barang-form" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 shadow-md transition-colors text-xs font-bold flex items-center">
                        <i class="fas fa-save mr-2"></i> Simpan Data
                    </button>
                </div>
            </div>

        </div>

        {{-- BACKDROP OVERLAY MODAL ATURAN PACKAGING --}}
        <div x-show="showPackagingModal" style="display: none;"
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60]"
             x-transition.opacity
             @click="closePackagingModal()"></div>

        {{-- MODAL ATURAN PACKAGING (master product_packaging) --}}
        <div x-show="showPackagingModal" style="display: none;"
             class="fixed inset-0 z-[70] flex items-center justify-center p-4 pointer-events-none"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="pointer-events-auto w-full max-w-2xl bg-zinc-800 rounded-xl shadow-2xl border border-zinc-700 flex flex-col max-h-[90vh]">

                <div class="px-6 py-4 border-b border-zinc-700 bg-zinc-800 flex justify-between items-center flex-shrink-0">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fas fa-boxes text-sky-400"></i>
                        Aturan Packaging
                    </h3>
                    <button @click="closePackagingModal()" class="text-zinc-400 hover:text-white text-2xl leading-none">&times;</button>
                </div>

                <div class="p-6 overflow-y-auto custom-scrollbar flex-grow space-y-6">

                    {{-- FORM TAMBAH/EDIT --}}
                    <div class="bg-zinc-900/60 border border-zinc-700 rounded-lg p-4 space-y-4">
                        <h4 class="text-xs font-bold text-sky-400 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas" :class="packagingEditId ? 'fa-edit' : 'fa-plus-circle'"></i>
                            <span x-text="packagingEditId ? 'Edit Aturan Packaging' : 'Tambah Aturan Packaging Baru'"></span>
                        </h4>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider">Nama Kemasan <span class="text-red-500">*</span></label>
                            <input type="text" x-model="packagingName" placeholder="Contoh: Strip, Dus, Sachet, ..."
                                   class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider flex items-center justify-between">
                                <span>Satuan Isi (pack)</span>
                                <button type="button" @click="addPackInput()"
                                        class="text-xs font-bold text-sky-400 hover:text-sky-300 flex items-center gap-1">
                                    <i class="fas fa-plus mr-1"></i> Tambah Satuan Isi
                                </button>
                            </label>
                            <template x-for="(val, idx) in packInputs" :key="idx">
                                <div class="flex items-center gap-2">
                                    <input type="text" x-model="packInputs[idx]" placeholder="Contoh: Pcs, Pasang, Kg, Gram"
                                           class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-4 py-2 text-white placeholder-zinc-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                                    <button type="button" @click="removePackInput(idx)"
                                            x-show="packInputs.length > 1"
                                            class="w-9 h-9 rounded-lg flex items-center justify-center text-zinc-400 hover:text-red-400 hover:bg-red-500/10 border border-transparent hover:border-red-500/20 text-sm flex-shrink-0">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full border"
                                  :class="packagingTypeValue() === 'single' ? 'text-zinc-400 border-zinc-600' : (packagingTypeValue() === 'fixed' ? 'text-amber-400 border-amber-500/30 bg-amber-500/10' : 'text-sky-400 border-sky-600/30 bg-sky-500/10')"
                                  x-text="packagingTypeValue()"></span>
                            <span class="text-zinc-400" x-text="'(' + packagingTypeHint() + ')'"></span>
                        </div>

                        <div class="flex justify-end space-x-3 pt-1">
                            <button type="button" @click="resetPackagingForm()" x-show="packagingEditId"
                                    class="px-4 py-2 bg-zinc-700 text-gray-300 rounded-lg hover:bg-zinc-600 transition-colors text-xs font-semibold">
                                Batal
                            </button>
                            <button type="button" @click="savePackaging()" :disabled="packagingSaving"
                                    class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 shadow-md transition-colors text-xs font-bold flex items-center disabled:opacity-60 disabled:cursor-not-allowed">
                                <i class="fas fa-save mr-2"></i>
                                <span x-text="packagingSaving ? 'Menyimpan...' : (packagingEditId ? 'Simpan Perubahan' : 'Tambah Aturan')"></span>
                            </button>
                        </div>
                    </div>

                    {{-- LIST ATURAN PACKAGING --}}
                    <div class="overflow-x-auto rounded-lg border border-zinc-700">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-zinc-900/50 text-xs uppercase font-bold text-zinc-400 border-b border-zinc-700">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Kemasan</th>
                                    <th scope="col" class="px-4 py-3">Satuan Isi (pack)</th>
                                    <th scope="col" class="px-4 py-3">Type</th>
                                    <th scope="col" class="px-4 py-3 text-center w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-700/50">
                                <template x-if="packagingLoading">
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center text-zinc-500">
                                            <i class="fas fa-circle-notch fa-spin mr-2"></i> Memuat data...
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!packagingLoading && packRows.length === 0">
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center text-zinc-500">Belum ada aturan packaging.</td>
                                    </tr>
                                </template>
                                <template x-for="row in packRows" :key="row.id">
                                    <tr class="hover:bg-zinc-700/30">
                                        <td class="px-4 py-3 text-white font-semibold" x-text="row.packaging"></td>
                                        <td class="px-4 py-3 text-zinc-300">
                                            <template x-if="row.pack && row.pack.length > 0">
                                                <div class="flex flex-wrap gap-1.5">
                                                    <template x-for="u in row.pack" :key="u">
                                                        <span class="bg-zinc-700 text-zinc-300 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase" x-text="u"></span>
                                                    </template>
                                                </div>
                                            </template>
                                            <span x-show="!row.pack || row.pack.length === 0" class="text-zinc-500 text-xs">—</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full border"
                                                  :class="packTypeLabel(row.pack) === 'single' ? 'text-zinc-400 border-zinc-600' : (packTypeLabel(row.pack) === 'fixed' ? 'text-amber-400 border-amber-500/30 bg-amber-500/10' : 'text-sky-400 border-sky-600/30 bg-sky-500/10')"
                                                  x-text="packTypeLabel(row.pack)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button" @click="editPackaging(row)"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-sky-400 hover:bg-sky-500/10 transition-all border border-transparent hover:border-sky-500/20 text-xs"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" @click="deletePackaging(row)"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-400 hover:text-red-400 hover:bg-red-500/10 transition-all border border-transparent hover:border-red-500/20 text-xs"
                                                        title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-layout-admin>