<x-layout-users title="{{ $title ?? 'Data Product' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }
        .mesh-bg {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0;
            background-color: #ede9fe;
            background-image:
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            pointer-events: none;
        }
        .table-header { background: #f8fafc; color: #475569; font-weight: 800; }
        .row-item { background: #ffffff; color: #1e293b; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        .row-item:hover { background: #f1f5f9; }
        .mobile-product-card {
            background: #ffffff; border: 1px solid #e2e8f0; border-left: 4px solid #3b82f6; border-radius: 0.9rem;
            padding: 0.8rem 0.9rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .mobile-product-card.mpc-zero { border-left-color: #f43f5e; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800 pb-16">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start">

            @if ($errors->any())
                <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-xl relative shadow-md" role="alert">
                    <span class="block font-bold">{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="w-full flex justify-start">
                <x-ui.back-button href="{{ route('warehouse.dashboard') }}" label="Back to Warehouse Dashboard" />
            </div>

            <div class="hidden md:block">
                <x-ui.page-header title="Data Product" subtitle="Kelola produk + stok realtime — stok otomatis via Surat Jalan & pencatatan manual." icon="fa-boxes-stacked" />
            </div>

            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Data Product</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">Kelola produk & stok realtime.</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>

            <x-ui.glass-card padding="none" class="!p-0 overflow-hidden flex flex-col w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-sm !rounded-3xl !bg-white">
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm shrink-0"><i class="fas fa-list"></i></span>
                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-slate-800 leading-tight">Product List</h2>
                            <p class="text-[11px] text-slate-500 font-medium leading-tight">{{ $items->count() }} produk</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 w-full sm:w-auto">
                        <form action="{{ route('warehouse.product.index') }}" method="GET" id="filterForm" class="flex-1 min-w-[140px] max-w-sm hidden sm:flex">
                            <x-ui.search-input name="search" value="{{ $search }}" placeholder="Cari kode / nama produk" autocomplete="off" class="flex-1" :debounce="500" />
                        </form>
                        @if($canManageStock)
                        <button type="button" onclick="openCreate()" class="shrink-0 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-sm">
                            <i class="fas fa-plus"></i><span class="hidden sm:inline">Tambah Produk</span>
                        </button>
                        @endif
                    </div>
                </div>

                <div class="sm:hidden px-3 pt-3">
                    <form action="{{ route('warehouse.product.index') }}" method="GET" id="filterFormMobile">
                        <x-ui.search-input name="search" value="{{ $search }}" placeholder="Cari produk..." :debounce="500" />
                    </form>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden px-3 pt-2 pb-3 space-y-2 max-h-[70vh] overflow-y-auto">
                    @forelse($items as $item)
                    <div class="mobile-product-card {{ $item['stok'] <= 0 ? 'mpc-zero' : '' }}">
                        <div class="flex items-start gap-2.5">
                            <div class="min-w-0 flex-1">
                                <div class="text-[9px] font-bold text-blue-500 uppercase tracking-wide">{{ $item['kode'] }}</div>
                                <div class="text-[13px] font-bold text-slate-800 leading-snug break-words mt-1">{{ $item['nama'] }}</div>
                                <div class="mt-1"><span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $item['kemasan_full'] }}</span></div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-black text-lg leading-tight {{ $item['stok'] <= 0 ? 'text-rose-600' : '' }}">
                                    {{ number_format($item['stok'], 0, ',', '.') }}
                                    <span class="text-xs font-bold text-slate-500">{{ $item['satuan'] }}</span>
                                </div>
                                @if(!$item['is_single'])
                                <div class="text-[11px] font-black text-emerald-600">= {{ number_format(floor($item['stok'] / $item['pcs_per_unit']), 0, ',', '.') }} {{ $item['kemasan'] }}</div>
                                @endif
                            </div>
                        </div>
                        @if($canManageStock)
                        <div class="flex items-center justify-end gap-2 mt-2 pt-2 border-t border-slate-100">
                            <x-ui.edit-button title="Ubah" data-item="{{ json_encode($item) }}" onclick="openEdit(this)" />
                            <x-ui.delete-button title="Hapus" data-item="{{ json_encode($item) }}" onclick="openDelete(this)" />
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-8 text-slate-400 text-sm font-medium">Tidak ada produk ditemukan.</div>
                    @endforelse
                </div>

                {{-- Desktop table --}}
                <div class="hidden md:block overflow-auto max-h-[800px] flex-1 bg-white">
                    <table class="w-full text-sm text-left">
                        <thead class="table-header sticky top-0 z-10 shadow-sm bg-slate-50/80 backdrop-blur-sm">
                            <tr>
                                <th class="px-4 py-3 text-center text-[11px] md:text-xs uppercase tracking-wider w-12">No</th>
                                <th class="px-3 py-3 text-[11px] md:text-xs uppercase tracking-wider whitespace-nowrap">Code</th>
                                <th class="px-3 py-3 text-[11px] md:text-xs uppercase tracking-wider">Product Name</th>
                                <th class="px-3 py-3 text-center text-[11px] md:text-xs uppercase tracking-wider whitespace-nowrap">Kemasan</th>
                                <th class="px-3 py-3 text-center text-[11px] md:text-xs uppercase tracking-wider whitespace-nowrap bg-blue-50/60 border-l border-blue-100 text-blue-700" style="width:200px;">Stock Realtime</th>
                                @if($canManageStock)
                                <th class="px-3 py-3 text-center text-[11px] md:text-xs uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse($items as $i => $item)
                            <tr class="row-item hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2.5 text-center text-slate-400">{{ $i + 1 }}</td>
                                <td class="px-3 py-2.5 text-slate-500 whitespace-nowrap text-xs md:text-sm">{{ $item['kode'] }}</td>
                                <td class="px-3 py-2.5 font-bold text-slate-800" style="max-width:230px;">
                                    <span class="text-xs md:text-sm block truncate" title="{{ $item['nama'] }}">{{ $item['nama'] }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $item['kemasan_full'] }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap bg-blue-50/60 border-l border-blue-100" style="width:200px;">
                                    <span class="font-black text-sm md:text-base {{ $item['stok'] <= 0 ? 'text-rose-600' : '' }}">{{ number_format($item['stok'], 0, ',', '.') }} <span class="text-xs font-bold text-slate-500">{{ $item['satuan'] }}</span></span>
                                    @if(!$item['is_single'])
                                    <span><span class="text-xs font-bold text-slate-400"> = </span><span class="font-black text-sm md:text-base text-emerald-600">{{ number_format(floor($item['stok'] / $item['pcs_per_unit']), 0, ',', '.') }} {{ $item['kemasan'] }}</span>@if(fmod($item['stok'], $item['pcs_per_unit']) > 0)<span class="text-xs font-bold text-slate-500">+{{ number_format(fmod($item['stok'], $item['pcs_per_unit']), 0, ',', '.') }} {{ $item['fill_unit'] }}</span>@endif</span>
                                    @endif
                                </td>
                                @if($canManageStock)
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <x-ui.edit-button title="Ubah" data-item="{{ json_encode($item) }}" onclick="openEdit(this)" />
                                        <x-ui.delete-button title="Hapus" data-item="{{ json_encode($item) }}" onclick="openDelete(this)" />
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-0">
                                    <x-ui.empty icon="fa-inbox" title="Tidak ada data" message="Belum ada produk. Klik Tambah Produk untuk membuat data baru." />
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.glass-card>
        </div>
    </div>

    {{-- Modal Tambah / Edit horizontal (kode, nama, pack, qty, isi kemasan) --}}
    <x-ui.modal id="product-modal" maxWidth="max-w-2xl" title="Tambah Produk" subtitle="Kode, nama, pack & qty kemasan." icon="fa-box-open">
        <form id="product-form" action="{{ route('warehouse.product.store') }}" method="POST" class="space-y-4">
            @csrf
            <span id="product-method"></span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="min-w-0">
                    <label class="ui-label">Kode Produk</label>
                    <input type="text" name="product_code" id="f-kode" maxlength="50" class="ui-input" placeholder="cth: PRD-001 (opsional)">
                </div>
                <div class="min-w-0">
                    <label class="ui-label">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="product_name" id="f-nama" maxlength="255" required class="ui-input" placeholder="cth: Paracetamol 500mg">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="min-w-0">
                    <label class="ui-label">Pack</label>
                    <div class="relative">
                        <input type="text" name="unit" id="f-satuan" maxlength="50" list="satuan-list" oninput="onPackChange()" class="ui-input pr-8" placeholder="cth: Box">
                        <button type="button" id="pack-clear" onclick="clearPack()" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" title="Hapus pack"><i class="fas fa-times-circle text-[11px]"></i></button>
                    </div>
                    <datalist id="satuan-list">
                        @foreach($packCatalog['suggestions'] as $sug)
                        <option value="{{ $sug }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="min-w-0">
                    <label class="ui-label">Qty / Pack</label>
                    <div id="qty-wrap" class="flex items-center gap-1.5">
                        <input type="number" name="pcs_per_unit" id="f-qty" min="0" step="1" value="1" oninput="updatePackPreview()" class="ui-input text-right font-bold flex-1 min-w-0">
                        <span class="text-slate-400 text-sm font-bold shrink-0">×</span>
                        <select name="fill_unit" id="f-fill" onchange="updatePackPreview()" class="ui-input flex-1 min-w-0"></select>
                    </div>
                    <p id="qty-single-note" class="hidden text-[11px] text-slate-500 font-semibold mt-1">Satuan tunggal — isi otomatis 1.</p>
                </div>
                <div class="min-w-0" id="stock-awal-wrap">
                    <label class="ui-label">Stok Awal (Manual)</label>
                    <input type="number" name="stock" id="f-stok" min="0" step="1" value="0" class="ui-input">
                </div>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 space-y-2 overflow-hidden">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider shrink-0"><i class="fas fa-boxes mr-1"></i>Pack:</span>
                    <div class="flex flex-wrap gap-1.5 min-w-0" id="pack-chips">
                        @foreach($packCatalog['suggestions'] as $sug)
                        <button type="button" onclick="setPack('{{ $sug }}')" class="px-2 py-0.5 rounded border border-slate-200 bg-white text-slate-500 hover:bg-blue-50 hover:text-blue-600 text-[10px] font-bold">{{ $sug }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-1.5" id="qty-preset-group">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider shrink-0">Qty:</span>
                    <div class="flex flex-wrap gap-1.5 min-w-0" id="qty-presets">
                        @foreach($packCatalog['presets'] as $preset)
                        <button type="button" onclick="setQty({{ $preset }})" class="px-2 py-0.5 rounded border border-slate-200 bg-white text-slate-500 hover:bg-blue-50 hover:text-blue-600 text-[10px] font-bold">{{ $preset }}</button>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-slate-500 w-full sm:w-auto sm:ml-auto">Kemasan: <span class="font-bold text-slate-700" id="pack-preview">-</span></p>
                </div>
            </div>
        </form>
        <x-slot:footer>
            <x-ui.button variant="secondary" size="sm" data-modal-close>Batal</x-ui.button>
            <x-ui.button variant="primary" size="sm" type="submit" form="product-form" icon="fa-save">Simpan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal Hapus --}}
    <x-ui.modal id="delete-modal" maxWidth="max-w-sm" title="Hapus Produk" subtitle="Tindakan ini tidak bisa dibatalkan." icon="fa-trash-can" iconClass="bg-red-600 text-white">
        <p class="text-sm text-slate-600">Hapus <span class="font-bold text-slate-900" id="d-nama">produk ini</span> dari Data Product?</p>
        <form id="delete-form" action="" method="POST">
            @csrf
            @method('DELETE')
        </form>
        <x-slot:footer>
            <x-ui.button variant="secondary" size="sm" data-modal-close>Batal</x-ui.button>
            <x-ui.button variant="danger" size="sm" type="submit" form="delete-form" icon="fa-trash-can">Ya, Hapus</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    @push('scripts')
    <script>
        function openCreate() {
            const form = document.getElementById('product-form');
            form.action = "{{ route('warehouse.product.store') }}";
            document.getElementById('product-method').innerHTML = '';
            document.getElementById('f-kode').value = '';
            document.getElementById('f-nama').value = '';
            document.getElementById('f-satuan').value = '';
            document.getElementById('f-stok').value = 0;
            document.getElementById('f-qty').value = 1;
            onPackChange('Pcs');
            document.getElementById('stock-awal-wrap').style.display = '';
            document.querySelector('#product-modal-title').textContent = 'Tambah Produk';
            uiModalOpen('product-modal');
        }
        /* Aturan packaging: single/fixed/flex. */
        const PACK_CATALOG = {!! json_encode($packCatalog['catalog'], JSON_UNESCAPED_UNICODE) !!};
        function packMeta(u) { return PACK_CATALOG[(u || '').trim().toLowerCase()] || {type: 'flex', units: ['Pcs']}; }
        function packType(u) { return packMeta(u).type; }
        function onPackChange(preferFill) {
            const packEl = document.getElementById('f-satuan');
            const pack = packEl.value || '';
            const meta = packMeta(pack);
            const fillSel = document.getElementById('f-fill');
            const cur = (preferFill || fillSel.value || 'Pcs').trim() || 'Pcs';
            fillSel.innerHTML = meta.units.map(function (u) { return '<option value="' + u + '">' + u + '</option>'; }).join('');
            fillSel.value = meta.units.includes(cur) ? cur : (meta.units[0] || 'Pcs');
            const single = packType(pack) === 'single';
            document.getElementById('qty-wrap').style.display = single ? 'none' : '';
            document.getElementById('qty-preset-group').style.display = single ? 'none' : '';
            document.getElementById('qty-single-note').classList.toggle('hidden', !single);
            if (single) { document.getElementById('f-qty').value = 1; }
            document.getElementById('pack-clear').style.display = pack ? '' : 'none';
            updatePackPreview();
        }
        function setPack(v) { document.getElementById('f-satuan').value = v; onPackChange(); }
        function clearPack() { document.getElementById('f-satuan').value = ''; onPackChange(); }
        function setQty(v) { document.getElementById('f-qty').value = v; updatePackPreview(); }
        function updatePackPreview() {
            const pack = (document.getElementById('f-satuan').value || '').trim();
            const qty = parseInt(document.getElementById('f-qty').value, 10);
            const fillSel = document.getElementById('f-fill');
            const fill = (fillSel.value || 'Pcs').trim() || 'Pcs';
            let text = '-';
            if (pack && qty >= 1 && packType(pack) !== 'single') text = pack + '/ ' + qty + ' ' + fill;
            else if (pack) text = pack;
            document.getElementById('pack-preview').textContent = text;
        }
        function openEdit(btn) {
            const item = JSON.parse(btn.dataset.item || '{}');
            const form = document.getElementById('product-form');
            form.action = "{{ route('warehouse.product.update', ':id') }}".replace(':id', item.id);
            document.getElementById('product-method').innerHTML = '@method("PUT")';
            document.getElementById('f-kode').value = item.kode === '-' ? '' : (item.kode || '');
            document.getElementById('f-nama').value = item.nama || '';
            document.getElementById('f-satuan').value = item.kemasan || '';
            document.getElementById('f-qty').value = item.pcs_per_unit >= 1 ? item.pcs_per_unit : 1;
            onPackChange(item.fill_unit || 'Pcs');
            document.getElementById('stock-awal-wrap').style.display = 'none';
            document.querySelector('#product-modal-title').textContent = 'Edit Produk';
            uiModalOpen('product-modal');
        }
        function openDelete(btn) {
            const item = JSON.parse(btn.dataset.item || '{}');
            document.getElementById('delete-form').action = "{{ route('warehouse.product.destroy', ':id') }}".replace(':id', item.id);
            document.getElementById('d-nama').textContent = item.nama || 'produk ini';
            uiModalOpen('delete-modal');
        }
    </script>
    @endpush
</x-layout-users>
