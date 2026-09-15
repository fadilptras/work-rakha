<x-layout-users title="Product Master">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        body { background-color: #ede9fe; }

        .mesh-bg {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0;
            background-color: #ede9fe;
            background-image:
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            pointer-events: none;
        }

        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1rem; padding: 0 1.5rem; color: white;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3); position: relative; overflow: hidden;
        }
        .page-header::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(30deg); pointer-events: none;
        }
        .header-content { position: relative; z-index: 1; }

        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 6px 16px 6px 6px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px; color: #1e293b;
            font-size: 0.85rem; font-weight: 700; text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            width: fit-content;
        }
        .btn-back-modern:hover { background: rgba(255, 255, 255, 0.95); transform: translateY(-2px); color: #1d4ed8; }
        .btn-back-modern:active { transform: translateY(0) scale(0.98); }
        .btn-back-modern .icon-circle {
            width: 28px; height: 28px; background: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6; font-size: 0.8rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }

        .glass-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0; border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        /* Scroll performance - modal edit */
        .glass-panel { overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }
        table { contain: layout paint; }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .chip { transition: transform .12s ease; will-change: transform; }
        .chip:hover { transform: translateY(-1px); }
        /* Modal scroll enteng */
        #pm-modal-root-drawer .overflow-y-auto { scroll-behavior: auto; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; will-change: scroll-position; contain: content; }
    </style>
    @endpush

    @php
        $bulkRows = $filtered->values()->map(function ($barang) use ($productMap) {
            $product = $productMap[$barang->product_code] ?? null;
            return [
                'id' => $barang->id,
                'kode' => $barang->product_code ?? '—',
                'nama' => $barang->product_name,
                'name' => ($product?->product_name_clean ?? null) ?: '',
                'unit' => $barang->unit ?? '',
                'pcs' => (int) ($product?->pcs_per_unit ?? 1),
                'fill' => ($product?->fill_unit ?? 'Pcs') ?: 'Pcs',
            ];
        })->all();
    @endphp
    <script>
        window.packagingCatalog = @js($catalog['catalog']);
        window.packagingSuggestions = @js($catalog['suggestions']);
        window.packagingPresets = @js($catalog['presets']);
        window.canManageBarang = @js($canManageStock);
        window.bulkBarangRows = @js($bulkRows);
        window.barangHealth = @js($health);
    </script>

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16"
         x-data="{
            showDrawer: false,
            isEdit: false,
            drawerTitle: '',
            formUrl: '',
            canManage: window.canManageBarang,

            kodeValue: '',
            namaValue: '',
            satuanValue: '',

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
            onClearUnit() { this.pcsPerUnit = 1; this.fillUnit = 'Pcs'; },
            autofillClean() {
                const ref = (this.namaValue || '').trim();
                if (!ref) return;
                const typed = (this.cleanValue || '').trim();
                if (typed === '' || ref.toLowerCase().startsWith(typed.toLowerCase())) this.cleanValue = ref;
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
                if (!this.canManage) return;
                this.isEdit = false;
                this.drawerTitle = 'Add Item';
                this.formUrl = '{{ route('sales.stock.barang.store') }}';
                this.kodeValue = ''; this.namaValue = ''; this.satuanValue = '';
                this.cleanValue = ''; this.pcsPerUnit = 1; this.fillUnit = 'Pcs';
                this.showDrawer = true;
            },
            openEdit(id, kode, nama, satuan, clean, pcs, fillUnit) {
                if (!this.canManage) return;
                this.isEdit = true;
                this.drawerTitle = 'Edit Item';
                this.formUrl = '{{ route('sales.stock.barang.update', ':id') }}'.replace(':id', id);
                this.kodeValue = kode ?? ''; this.namaValue = nama ?? ''; this.satuanValue = satuan ?? '';
                this.cleanValue = clean ?? '';
                this.pcsPerUnit = (pcs >= 1) ? pcs : 1;
                this.fillUnit = fillUnit || 'Pcs';
                // Sinkronkan fillUnit dengan katalog agar tidak mismatch setelah edit
                const meta = this.metaFor(this.satuanValue);
                if (this.typeFor(this.satuanValue) === 'single') this.pcsPerUnit = 1;
                if (meta.units.length > 0 && !meta.units.includes(this.fillUnit)) this.fillUnit = meta.units[0];
                this.showDrawer = true;
            },

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
                if (items.length === 0) return 'auto quantity 1';
                if (items.length === 1) return 'content required';
                return 'content optional (pick one)';
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
            closePackagingModal() { this.showPackagingModal = false; },
            resetPackagingForm() { this.packagingEditId = null; this.packagingName = ''; this.packInputs = ['']; },
            async loadPackagings() {
                this.packagingLoading = true;
                try {
                    const res = await fetch('{{ route('sales.stock.barang.packagings.index') }}', {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) }
                    });
                    const json = await res.json();
                    this.packRows = json.data || [];
                } catch (e) { console.error('load packagings error', e); }
                finally { this.packagingLoading = false; }
            },
            addPackInput() { this.packInputs.push(''); },
            removePackInput(index) { if (this.packInputs.length > 1) this.packInputs.splice(index, 1); },
            editPackaging(row) {
                if (!this.canManage) return;
                this.packagingEditId = row.id;
                this.packagingName = row.packaging;
                this.packInputs = (row.pack && row.pack.length > 0) ? [...row.pack] : [''];
            },
            async savePackaging() {
                if (!this.canManage) return;
                const pack = this.packInputs.map(v => String(v || '').trim()).filter(v => v !== '');
                if (!this.packagingName.trim()) { Swal.fire('Warning', 'Packaging name is required.', 'warning'); return; }
                this.packagingSaving = true;
                const isEdit = this.packagingEditId !== null;
                const url = isEdit
                    ? '{{ route('sales.stock.barang.packagings.update', ':id') }}'.replace(':id', this.packagingEditId)
                    : '{{ route('sales.stock.barang.packagings.store') }}';
                try {
                    const res = await fetch(url, {
                        method: isEdit ? 'PUT' : 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) },
                        body: JSON.stringify({ packaging: this.packagingName.trim(), pack })
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        const msg = json.errors ? Object.values(json.errors).flat().join(', ') : (json.message || 'Failed to save.');
                        Swal.fire('Error', msg, 'error'); return;
                    }
                    this.resetPackagingForm();
                    await this.loadPackagings();
                    Swal.fire('Success', json.message, 'success');
                } catch (e) { Swal.fire('Error', 'Something went wrong while saving.', 'error'); }
                finally { this.packagingSaving = false; }
            },
            async deletePackaging(row) {
                if (!this.canManage) return;
                const confirmRes = await Swal.fire({
                    title: 'Delete Packaging Rule?',
                    text: 'The packaging rule will be permanently deleted. Related product data is not affected.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonText: 'Yes, Delete', cancelButtonText: 'Cancel',
                });
                if (!confirmRes.isConfirmed) return;
                try {
                    const res = await fetch('{{ route('sales.stock.barang.packagings.destroy', ':id') }}'.replace(':id', row.id), {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) }
                    });
                    const json = await res.json();
                    if (!res.ok) { Swal.fire('Error', json.message || 'Failed to delete.', 'error'); return; }
                    await this.loadPackagings();
                    Swal.fire('Success', json.message, 'success');
                } catch (e) { Swal.fire('Error', 'Something went wrong while deleting.', 'error'); }
            },

            // ===== Edit Massal =====
            showBulkModal: false,
            bulkSaving: false,
            bulkSearch: '',
            bulkRows: [],

            openBulkModal() {
                this.bulkRows = (window.bulkBarangRows || []).map(r => ({
                    id: r.id, kode: r.kode, nama: r.nama, name: r.name || '',
                    unit: r.unit || '', pcs: r.pcs, fill: r.fill,
                    fillOptions: this.fillOptionsFor(r.unit || ''),
                }));
                this.bulkSearch = '';
                this.showBulkModal = true;
            },
            closeBulkModal() { this.showBulkModal = false; },
            fillOptionsFor(u) {
                const meta = this.metaFor(u || '');
                return (meta.units && meta.units.length > 0) ? meta.units : ['Pcs'];
            },
            onBulkUnitChange(row) {
                row.fillOptions = this.fillOptionsFor(row.unit);
                if (this.typeFor(row.unit || '') === 'single') {
                    row.pcs = 1;
                    row.fill = row.fillOptions[0] || 'Pcs';
                    return;
                }
                if (!row.fillOptions.includes(row.fill)) row.fill = row.fillOptions[0];
            },
            get filteredBulkRows() {
                const q = (this.bulkSearch || '').trim().toLowerCase();
                if (!q) return this.bulkRows;
                return this.bulkRows.filter(r => ((r.kode || '') + ' ' + (r.nama || '') + ' ' + (r.name || '')).toLowerCase().includes(q));
            },
            bulkPreview(row) {
                const pkg = (row.unit || '').trim();
                const isi = parseInt(row.pcs, 10) || 0;
                const fill = row.fill || 'Pcs';
                if (!pkg) return isi > 0 ? isi + ' ' + fill : 'General';
                if (this.typeFor(pkg) === 'single' || isi < 1) return pkg;
                return pkg + '/ ' + isi + ' ' + fill;
            },
            async saveBulk() {
                if (!this.canManage || this.bulkSaving) return;
                this.bulkSaving = true;
                try {
                    const res = await fetch('{{ route('sales.stock.barang.bulk_update') }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) },
                        body: JSON.stringify({
                            items: this.bulkRows.map(r => ({
                                id: r.id,
                                name: (r.name || '').trim() || null,
                                unit: (r.unit || '').trim() || null,
                                pcs_per_unit: parseInt(r.pcs, 10) || 0,
                                fill_unit: r.fill || null,
                            }))
                        })
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        const msg = json.errors ? Object.values(json.errors).flat().join(', ') : (json.message || 'Failed to save.');
                        Swal.fire('Error', msg, 'error');
                        return;
                    }
                    if (json.failed && json.failed.length > 0) {
                        Swal.fire('Partially Failed', json.message + ' Failed IDs: ' + json.failed.map(f => f.id).join(', '), 'warning');
                        return;
                    }
                    this.showBulkModal = false;
                    await Swal.fire('Success', json.message, 'success');
                    location.reload();
                } catch (e) { Swal.fire('Error', 'Something went wrong while saving.', 'error'); }
                finally { this.bulkSaving = false; }
            },

            // ===== Data Health =====
            showHealthModal: false,
            health: window.barangHealth || { missingPackaging: [], missingClean: [], missingQty: [], duplicates: [] },

            openHealthModal() { this.showHealthModal = true; },
            closeHealthModal() { this.showHealthModal = false; },
            openBulkFromHealth() {
                if (!this.canManage) return;
                this.showHealthModal = false;
                this.openBulkModal();
            },
            healthScore() {
                const total = this.bulkRows.length || 0;
                if (!total) return 100;
                const flagged = new Set();
                this.health.missingPackaging.forEach(r => flagged.add(r.kode + '|' + r.nama));
                this.health.missingQty.forEach(r => flagged.add(r.kode + '|' + r.nama));
                this.health.missingClean.forEach(r => flagged.add(r.kode + '|' + r.nama));
                return Math.round((total - flagged.size) / total * 100);
            }
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', showDrawer || showPackagingModal || showBulkModal || showHealthModal)">

        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-10 space-y-4">

            <div class="w-full flex justify-start">
                <a href="{{ route('sales.gudang.dashboard') }}" class="btn-back-modern shrink-0">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    Back to Warehouse Dashboard
                </a>
            </div>

            {{-- Top Section: Header & Toolbar --}}
            <div class="flex flex-col gap-4 w-full mb-4">
                
                {{-- Card 1: Header --}}
                <div class="page-header h-24 w-full flex flex-row items-center justify-between shadow-md">
                    <div class="header-content flex-1">
                        <h1 class="text-2xl font-bold tracking-tight text-white leading-tight">Product Master</h1>
                        <p class="text-blue-100 text-sm mt-1 leading-snug font-light">
                            Warehouse item master — codes, packaging, &amp; stock.
                        </p>
                    </div>
                    <div class="header-content shrink-0 ml-4">
                        <i class="fas fa-box text-5xl opacity-20 mr-1"></i>
                    </div>
                </div>

                {{-- Card 2: Toolbar (Navy Blue dengan Form Search Putih Kontras) --}}
                <div class="h-24 bg-blue-900 rounded-2xl px-6 flex items-center justify-between gap-6 shadow-[0_10px_30px_rgba(29,78,216,0.25)] border border-blue-700/50 overflow-x-auto w-full relative" style="background-color: #1e3a8a !important; border: 1px solid rgba(255,255,255,0.15) !important;">
                    
                    {{-- Search Form (Dibuat Putih Terang Agar Jelas) --}}
                    <form method="GET" action="{{ route('sales.stock.barang.index') }}" class="relative flex-1 max-w-xl shrink-0 min-w-[200px] z-10">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Search code / name..."
                            class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-sm text-slate-800 placeholder-slate-400 shadow-md transition-all">
                        @if($search !== '')
                            <a href="{{ route('sales.stock.barang.index') }}" class="absolute inset-y-0 right-3 flex items-center text-blue-600 hover:text-blue-800 text-sm font-bold transition-colors">Reset</a>
                        @endif
                    </form>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2.5 shrink-0 z-10">
                        <button type="button" @click="openHealthModal()"
                            class="px-4 py-2.5 bg-rose-50 text-rose-700 text-sm font-semibold rounded-xl border border-rose-200 hover:bg-rose-100 flex items-center gap-2 shadow-sm transition-colors">
                            <i class="fas fa-heart-pulse text-rose-500"></i> Check Data
                        </button>

                        @if($canManageStock)
                        <a href="{{ route('sales.stock.barang.export') }}"
                            class="px-4 py-2.5 bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-xl border border-emerald-200 hover:bg-emerald-100 flex items-center gap-2 shadow-sm transition-colors">
                            <i class="fas fa-file-export text-emerald-500"></i> Export
                        </a>
                        <button type="button" @click="openPackagingModal()"
                            class="px-4 py-2.5 bg-purple-50 text-purple-700 text-sm font-semibold rounded-xl border border-purple-200 hover:bg-purple-100 flex items-center gap-2 shadow-sm transition-colors">
                            <i class="fas fa-boxes text-purple-500"></i> Manage Packaging
                        </button>
                        <button type="button" @click="openBulkModal()"
                            class="px-4 py-2.5 bg-amber-50 text-amber-700 text-sm font-semibold rounded-xl border border-amber-200 hover:bg-amber-100 flex items-center gap-2 shadow-sm transition-colors">
                            <i class="fas fa-table-list text-amber-500"></i> Edit Product
                        </button>
                        <button @click="openCreate()"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl shadow-md flex items-center gap-2 transition-colors ml-1">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-xl relative shadow-md">
                    <ul class="list-disc list-inside text-sm font-semibold">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Tabel Data --}}
            <div class="glass-panel border-t-4 border-t-blue-500 !p-0 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-list text-blue-500"></i> Item List
                    </h2>
                    <div class="flex items-center gap-2">
                        @if($search !== '' || $filter !== 'all')
                            <span class="text-[11px] text-slate-400 font-semibold">Showing {{ count($filtered) }}</span>
                        @endif
                        <span class="inline-flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-full pl-2.5 pr-3 py-1">
                            <i class="fas fa-layer-group text-blue-500 text-[10px]"></i>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Items</span>
                            <span class="text-sm font-black text-slate-800 leading-none">{{ $total }}</span>
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left table-fixed min-w-[860px]">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <tr class="text-[11px] uppercase tracking-wider">
                                <th scope="col" class="px-4 py-3 w-12 text-center">#</th>
                                <th scope="col" class="px-4 py-3 w-32">Item Code</th>
                                <th scope="col" class="px-4 py-3 w-52">Product Name <span class="text-slate-400 normal-case">(Accurate)</span></th>
                                <th scope="col" class="px-4 py-3 w-52">Clean Name</th>
                                <th scope="col" class="px-4 py-3 w-40">Packaging</th>
                                <th scope="col" class="px-4 py-3 text-right w-28">Stock</th>
                                @if($canManageStock)
                                <th scope="col" class="px-4 py-3 text-center w-24">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($filtered as $index => $barang)
                                @php
                                    $product = $productMap[$barang->product_code] ?? null;
                                    $pcsPerUnit = (int) ($product?->pcs_per_unit ?? 0);
                                    $fillUnit = ($product?->fill_unit ?? 'Pcs') ?: 'Pcs';
                                    $stockValue = $product?->stock ?? $barang->stock ?? 0;
                                    $pkgUnit = $barang->unit ?: ($product?->unit ?? null);
                                    $isSingleUnit = $pkgUnit && \App\Support\PackagingCatalog::isSingle($pkgUnit);
                                    $stockUnit = $isSingleUnit ? $pkgUnit : $fillUnit;
                                    $showPackQty = $barang->unit && !$isSingleUnit && $pcsPerUnit > 1;
                                    $stockDisplay = number_format($stockValue, 0, ',', '.') . ' ' . $stockUnit;
                                @endphp
                            <tr class="hover:bg-slate-50 transition-colors align-top">
                                <td class="px-4 py-3 text-center text-slate-400 font-mono text-xs">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center bg-slate-100 border border-slate-200 text-slate-600 text-[11px] font-mono px-2 py-1 rounded break-all">
                                        {{ $barang->product_code ?: '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500 break-words">{{ $barang->product_name }}</td>
                                <td class="px-4 py-3 break-words">
                                    @if($product && $product->product_name_clean)
                                        <span class="text-slate-800 font-bold break-words">{{ $product->product_name_clean }}</span>
                                    @else
                                        <span class="text-slate-400 italic text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($barang->unit)
                                        <span class="inline-flex items-center gap-1 bg-sky-50 text-sky-700 border border-sky-200 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                            {{ $barang->unit }}
                                            @if($showPackQty)
                                                <span class="text-sky-400">/</span> {{ $pcsPerUnit }} {{ $fillUnit }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-slate-800 font-bold">{{ $stockDisplay }}</td>
                                @if($canManageStock)
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                                @click="openEdit({{ $barang->id }}, @js($barang->product_code), @js($barang->product_name), @js($barang->unit), @js($product?->product_name_clean ?? ''), {{ (int) ($product?->pcs_per_unit ?? 1) }}, @js($product?->fill_unit ?? 'Pcs'))"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-transform hover:scale-110 active:scale-95 text-xs"
                                                title="Edit (master + product rules)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('sales.stock.barang.destroy', $barang) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this item?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-transform hover:scale-110 active:scale-95 text-xs"
                                                    title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $canManageStock ? 7 : 6 }}" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-box-open text-4xl mb-3 text-slate-300"></i>
                                        <p class="text-sm font-medium">{{ $search !== '' ? 'No matching items.' : 'No items yet.' }}</p>
                                        @if($search !== '' || $filter !== 'all')
                                            <a href="{{ route('sales.stock.barang.index') }}" class="mt-3 text-blue-600 hover:text-blue-500 text-xs font-bold">Reset filter</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden space-y-2">
                @forelse ($filtered as $barang)
                    @php
                        $product = $productMap[$barang->product_code] ?? null;
                        $mPcs = (int) ($product?->pcs_per_unit ?? 0);
                        $mFill = ($product?->fill_unit ?? 'Pcs') ?: 'Pcs';
                        $mStock = $product?->stock ?? $barang->stock ?? 0;
                        $mPkg = $barang->unit ?: ($product?->unit ?? null);
                        $mSingle = $mPkg && \App\Support\PackagingCatalog::isSingle($mPkg);
                        $mStockUnit = $mSingle ? $mPkg : $mFill;
                    @endphp
                    <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm {{ $mStock == 0 ? 'border-l-4 !border-l-rose-500' : 'border-l-4 !border-l-blue-500' }}">
                        <div class="flex items-start gap-2.5">
                            <div class="min-w-0 flex-1">
                                <div class="text-[9px] font-bold text-blue-500 uppercase tracking-wide">{{ $barang->product_code ?: '—' }}</div>
                                <div class="text-[13px] font-bold text-slate-800 leading-snug break-words mt-0.5">{{ $product?->product_name_clean ?: $barang->product_name }}</div>
                                @if($mPkg)
                                    <span class="inline-flex items-center gap-1 bg-sky-50 text-sky-700 border border-sky-200 text-[10px] font-bold px-2 py-0.5 rounded-full mt-1.5">
                                        {{ $mPkg }}@if($barang->unit && !$mSingle && $mPcs > 1)<span class="text-sky-400">/</span> {{ $mPcs }} {{ $mFill }}@endif
                                    </span>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-black text-lg leading-tight {{ $mStock == 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ number_format($mStock, 0, ',', '.') }} <span class="text-xs font-bold text-slate-400">{{ $mStockUnit }}</span></div>
                                @if($canManageStock)
                                <div class="flex items-center justify-end gap-1 mt-1.5">
                                    <button type="button"
                                            @click="openEdit({{ $barang->id }}, @js($barang->product_code), @js($barang->product_name), @js($barang->unit), @js($product?->product_name_clean ?? ''), {{ (int) ($product?->pcs_per_unit ?? 1) }}, @js($product?->fill_unit ?? 'Pcs'))"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 text-xs transition-transform hover:scale-110 active:scale-95">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                        <form action="{{ route('sales.stock.barang.destroy', $barang) }}" method="POST" onsubmit="return confirm('Delete this item?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 text-xs transition-transform hover:scale-110 active:scale-95">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-sm font-medium">No items found.</div>
                @endforelse
            </div>

            <datalist id="gudang-packaging-suggestions">
                @foreach($catalog['suggestions'] as $sug)<option value="{{ $sug }}"></option>@endforeach
            </datalist>

            {{-- MODAL GROUP: FORM DATA BARANG (teleported to <body> via x-teleport) --}}
            <template x-teleport="body">
            <div id="pm-modal-root-drawer">
            {{-- BACKDROP MODAL FORM DATA BARANG --}}
            <div x-show="showDrawer" style="display: none; z-index: 999998 !important;"
                 class="fixed inset-0 bg-slate-900/50 z-[999998]"
                 x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-80" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="showDrawer = false"></div>

            {{-- CENTERED MODAL FORM DATA BARANG --}}
            <div x-show="showDrawer" style="display: none; z-index: 999999 !important;"
                 class="fixed inset-0 z-[999999] flex items-center justify-center p-4 sm:p-6 pointer-events-none"
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-120" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

                <div class="pointer-events-auto w-full max-w-4xl bg-white rounded-2xl shadow-lg flex flex-col max-h-[85vh] overflow-hidden" style="will-change: transform; transform: translateZ(0);">
                    
                    {{-- Modal Header --}}
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas text-blue-500" :class="isEdit ? 'fa-edit' : 'fa-plus-circle'"></i>
                            <span x-text="drawerTitle"></span>
                        </h3>
                        <button type="button" @click="showDrawer = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-rose-500 hover:bg-rose-50 transition-colors"><i class="fas fa-times text-sm"></i></button>
                    </div>

                    {{-- Modal Body (Grid Side-by-Side) - scroll enteng --}}
                    <div class="p-6 overflow-y-auto grow bg-white overscroll-contain" style="scroll-behavior: auto; -webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
                        <form id="barang-form" x-bind:action="formUrl" method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            @csrf
                            <input type="hidden" name="_method" value="PUT" x-show="isEdit" x-bind:disabled="!isEdit">

                            {{-- Left Column: Accurate Data --}}
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 space-y-4 h-full flex flex-col shadow-sm">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2 border-b border-slate-200 pb-2.5">
                                    <i class="fas fa-database text-blue-500"></i> Accurate Data
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Item Code</label>
                                        <input type="text" name="product_code" x-model="kodeValue" placeholder="e.g. BRG-001"
                                               class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm transition-all">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Item Name <span class="text-rose-500">*</span></label>
                                        <input type="text" name="product_name" required x-model="namaValue" placeholder="e.g. A4 Paper"
                                               class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm transition-all">
                                    </div>
                                </div>
                            </div>

                            {{-- Right Column: Product Rules --}}
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 space-y-4 h-full flex flex-col shadow-sm">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between border-b border-slate-200 pb-2.5">
                                    <span class="flex items-center gap-2"><i class="fas fa-tag text-blue-500"></i> Product Rules</span>
                                    <span class="normal-case font-semibold text-slate-400 text-[10px]">(optional)</span>
                                </h4>
                                
                                <div class="space-y-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Clean Name</label>
                                        <div class="relative rounded-lg bg-white border border-slate-300 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100 transition-all overflow-hidden">
                                            <span class="pointer-events-none absolute inset-y-0 left-3 right-3 flex items-center truncate text-sm text-slate-400"
                                                  x-text="namaValue || ''" x-show="namaValue && cleanValue !== namaValue"></span>
                                            <input type="text" name="product_name_clean" x-model="cleanValue" @keydown.tab="autofillClean()"
                                                   :placeholder="namaValue ? ' ' : 'e.g. Name without typos'"
                                                   class="relative w-full bg-transparent px-3 py-2 text-slate-700 placeholder-slate-400 text-sm focus:outline-none">
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Packaging</label>
                                        <div class="relative">
                                            <input type="text" name="unit" x-model="satuanValue" @input="onUnitChange()" list="gudang-packaging-suggestions"
                                                   placeholder="Pcs / Box / Roll..."
                                                   class="w-full bg-white border border-slate-300 rounded-lg pl-3 pr-9 py-2 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm transition-all">
                                            <button type="button" x-show="satuanValue" x-cloak @click="onClearUnit()" class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-slate-400 hover:text-slate-600 bg-white hover:bg-slate-100 rounded-full border border-slate-200 transition-colors shadow-sm">
                                                <i class="fas fa-times text-[11px]"></i>
                                            </button>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5 pt-1">
                                            <template x-for="pkg in suggestions" :key="pkg">
                                                <button type="button" @click="satuanValue = pkg; onUnitChange()"
                                                        :class="satuanValue === pkg ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-blue-50 hover:text-blue-600'"
                                                        class="chip px-2.5 py-0.5 rounded border text-[10px] font-bold transition-colors" x-text="pkg"></button>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                            <span>Qty / Pack</span>
                                        </label>
                                        <div x-show="canFill(satuanValue)">
                                            <div class="flex items-center gap-2">
                                                <input type="number" x-model.number="pcsPerUnit" name="pcs_per_unit" min="0" placeholder="0"
                                                       class="w-24 shrink-0 bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm text-center font-bold transition-all">
                                                <span class="text-slate-400 font-bold text-sm shrink-0">&times;</span>
                                                <template x-if="metaFor(satuanValue).units.length > 1">
                                                    <select x-model="fillUnit" name="fill_unit" class="flex-1 min-w-0 bg-white border border-slate-300 rounded-lg px-3 py-2 text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm font-semibold transition-all cursor-pointer">
                                                        <template x-for="u in metaFor(satuanValue).units" :key="u"><option :value="u" x-text="u"></option></template>
                                                    </select>
                                                </template>
                                                <template x-if="metaFor(satuanValue).units.length <= 1">
                                                    <input type="hidden" name="fill_unit" :value="fillUnit || 'Pcs'">
                                                </template>
                                                <span x-show="metaFor(satuanValue).units.length <= 1" class="flex-1 text-sm font-bold text-slate-500 uppercase bg-slate-100 border border-slate-200 rounded-lg px-3 py-2" x-text="metaFor(satuanValue).units[0] || 'Pcs'"></span>
                                            </div>
                                            <div class="flex flex-wrap gap-1.5 mt-2 pt-1">
                                                <template x-for="qty in presets" :key="qty">
                                                    <button type="button" @click="pcsPerUnit = qty"
                                                            :class="pcsPerUnit === qty ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-blue-50 hover:text-blue-600'"
                                                            class="chip px-2.5 py-0.5 rounded border text-[10px] font-bold transition-colors" x-text="qty"></button>
                                                </template>
                                            </div>
                                        </div>
                                        <p x-show="!canFill(satuanValue)" class="text-[10px] text-blue-600 font-semibold bg-blue-50 px-2 py-1.5 rounded-lg border border-blue-100">
                                            Single unit (Pcs/Roll) — quantity auto-set to 1.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                        <p class="text-xs text-slate-500 font-medium hidden sm:block">
                            Stored format: <span class="font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-md" x-text="previewPackaging()"></span>
                        </p>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button type="button" @click="showDrawer = false" class="flex-1 sm:flex-none px-4 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 transition-all duration-120 font-bold text-sm active:scale-95 shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200">Cancel</button>
                            <button type="submit" form="barang-form" class="flex-1 sm:flex-none px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-md shadow-blue-500/20 transition-all duration-120 font-bold text-sm flex items-center justify-center hover:-translate-y-0.5 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="fas fa-save mr-2"></i> Save Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            </template>

            {{-- MODAL GROUP: PACKAGING --}}
            <template x-teleport="body">
            <div id="pm-modal-root-packaging">
            {{-- BACKDROP MODAL PACKAGING --}}
            <div x-show="showPackagingModal" style="display: none; z-index: 999998 !important;" class="fixed inset-0 bg-slate-900/50 z-[999998]" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-80" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closePackagingModal()"></div>

            {{-- MODAL ATURAN PACKAGING --}}
            <div x-show="showPackagingModal" style="display: none; z-index: 999999 !important;"
                 class="fixed inset-0 z-[999999] flex items-center justify-center p-4 pointer-events-none"
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-120" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div class="pointer-events-auto w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[85vh]">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0 rounded-t-2xl">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-boxes text-blue-500"></i> Packaging Rules</h3>
                        <button @click="closePackagingModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-rose-500 hover:bg-rose-50 transition-colors"><i class="fas fa-times text-sm"></i></button>
                    </div>
                    <div class="p-6 overflow-y-auto custom-scrollbar grow space-y-6">
                        <div x-show="canManage" class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-4">
                            <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-2">
                                <i class="fas" :class="packagingEditId ? 'fa-edit' : 'fa-plus-circle'"></i>
                                <span x-text="packagingEditId ? 'Edit Packaging Rule' : 'Add New Packaging Rule'"></span>
                            </h4>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Packaging Name <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="packagingName" placeholder="e.g. Strip, Box, Sachet, ..."
                                       class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm transition-all">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                    <span>Content Units (pack)</span>
                                    <button type="button" @click="addPackInput()" class="text-xs font-bold text-blue-600 hover:text-blue-500 flex items-center gap-1 transition-transform hover:-translate-y-0.5 active:scale-95">
                                        <i class="fas fa-plus mr-1"></i> Add Content Unit
                                    </button>
                                </label>
                                <template x-for="(val, idx) in packInputs" :key="idx">
                                    <div class="flex items-center gap-2">
                                        <input type="text" x-model="packInputs[idx]" placeholder="e.g. Pcs, Pair, Kg, Gram"
                                            class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-sm transition-all">
                                        <button type="button" @click="removePackInput(idx)" x-show="packInputs.length > 1"
                                                class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 text-sm shrink-0 transition-transform hover:scale-110 active:scale-95">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full border"
                                      :class="packagingTypeValue() === 'single' ? 'text-slate-500 border-slate-300 bg-slate-100' : (packagingTypeValue() === 'fixed' ? 'text-amber-600 border-amber-300 bg-amber-50' : 'text-blue-600 border-blue-300 bg-blue-50')"
                                      x-text="packagingTypeValue()"></span>
                                <span class="text-slate-400" x-text="'(' + packagingTypeHint() + ')'"></span>
                            </div>
                            <div class="flex justify-end gap-2 pt-1">
                                <button type="button" @click="resetPackagingForm()" x-show="packagingEditId"
                                        class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-100 transition-all duration-120 text-xs font-bold active:scale-95">Cancel</button>
                                <button type="button" @click="savePackaging()" :disabled="packagingSaving"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-md transition-all duration-120 text-xs font-bold flex items-center disabled:opacity-60 disabled:cursor-not-allowed hover:-translate-y-0.5 active:scale-95">
                                    <i class="fas fa-save mr-2"></i>
                                    <span x-text="packagingSaving ? 'Saving...' : (packagingEditId ? 'Save Changes' : 'Add Rule')"></span>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                                    <tr class="text-[11px] uppercase tracking-wider">
                                        <th scope="col" class="px-4 py-3">Packaging</th>
                                        <th scope="col" class="px-4 py-3">Content Units (pack)</th>
                                        <th scope="col" class="px-4 py-3">Type</th>
                                        <th x-show="canManage" scope="col" class="px-4 py-3 text-center w-28">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <template x-if="packagingLoading">
                                        <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400"><i class="fas fa-circle-notch fa-spin mr-2"></i> Loading...</td></tr>
                                    </template>
                                    <template x-if="!packagingLoading && packRows.length === 0">
                                        <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No packaging rules yet.</td></tr>
                                    </template>
                                    <template x-for="row in packRows" :key="row.id">
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-3 text-slate-800 font-bold" x-text="row.packaging"></td>
                                            <td class="px-4 py-3">
                                                <template x-if="row.pack && row.pack.length > 0">
                                                    <div class="flex flex-wrap gap-1.5">
                                                        <template x-for="u in row.pack" :key="u">
                                                            <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase" x-text="u"></span>
                                                        </template>
                                                    </div>
                                                </template>
                                                <span x-show="!row.pack || row.pack.length === 0" class="text-slate-400 text-xs">—</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full border"
                                                      :class="packTypeLabel(row.pack) === 'single' ? 'text-slate-500 border-slate-300 bg-slate-100' : (packTypeLabel(row.pack) === 'fixed' ? 'text-amber-600 border-amber-300 bg-amber-50' : 'text-blue-600 border-blue-300 bg-blue-50')"
                                                      x-text="packTypeLabel(row.pack)"></span>
                                            </td>
                                            <td x-show="canManage" class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <button type="button" @click="editPackaging(row)"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 text-xs transition-transform hover:scale-110 active:scale-95" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" @click="deletePackaging(row)"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 text-xs transition-transform hover:scale-110 active:scale-95" title="Delete">
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
            </template>

            {{-- MODAL GROUP: DATA HEALTH --}}
            <template x-teleport="body">
            <div id="pm-modal-root-health">
            {{-- BACKDROP DATA HEALTH MODAL --}}
            <div x-show="showHealthModal" style="display: none; z-index: 999998 !important;" class="fixed inset-0 bg-slate-900/50 z-[999998]" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-80" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeHealthModal()"></div>

            {{-- DATA HEALTH MODAL --}}
            <div x-show="showHealthModal" style="display: none; z-index: 999999 !important;"
                 class="fixed inset-0 z-[999999] flex items-center justify-center p-4 pointer-events-none"
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-120" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div class="pointer-events-auto w-full max-w-3xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[85vh]">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0 rounded-t-2xl">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-heart-pulse text-rose-500"></i> Data Health</h3>
                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">Completeness overview of the item master.</p>
                        </div>
                        <button @click="closeHealthModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-rose-500 hover:bg-rose-50 transition-colors"><i class="fas fa-times text-sm"></i></button>
                    </div>

                    <div class="p-5 overflow-y-auto custom-scrollbar grow space-y-5">
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-center shadow-sm">
                                <div class="text-2xl font-black text-blue-700" x-text="healthScore() + '%'"></div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">Complete</div>
                            </div>
                            <div class="bg-white border border-slate-200 rounded-xl p-3 text-center shadow-sm hover:border-slate-300 transition-colors">
                                <div class="text-2xl font-black text-slate-800" x-text="health.missingPackaging.length"></div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">No Packaging</div>
                            </div>
                            <div class="bg-white border border-slate-200 rounded-xl p-3 text-center shadow-sm hover:border-slate-300 transition-colors">
                                <div class="text-2xl font-black text-slate-800" x-text="health.missingQty.length"></div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">No Qty</div>
                            </div>
                            <div class="bg-white border border-slate-200 rounded-xl p-3 text-center shadow-sm hover:border-slate-300 transition-colors">
                                <div class="text-2xl font-black text-slate-800" x-text="health.missingClean.length"></div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">No Clean Name</div>
                            </div>
                            <div class="bg-white border border-slate-200 rounded-xl p-3 text-center shadow-sm hover:border-slate-300 transition-colors">
                                <div class="text-2xl font-black text-slate-800" x-text="health.duplicates.length"></div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-0.5">Duplicates</div>
                            </div>
                        </div>

                        <div x-show="health.missingPackaging.length > 0" class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-700 flex items-center gap-2">
                                <i class="fas fa-box-open text-amber-500"></i> Missing Packaging
                                <span class="ml-auto bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="health.missingPackaging.length"></span>
                            </div>
                            <div class="max-h-44 overflow-y-auto custom-scrollbar divide-y divide-slate-100">
                                <template x-for="r in health.missingPackaging" :key="'mp-' + r.kode">
                                    <div class="px-4 py-1.5 flex items-center gap-2.5 hover:bg-slate-50 transition-colors">
                                        <span class="text-[9px] font-bold text-blue-500 uppercase shrink-0 w-16 truncate" x-text="r.kode"></span>
                                        <span class="text-xs text-slate-700 truncate" x-text="r.nama"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="health.missingQty.length > 0" class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-700 flex items-center gap-2">
                                <i class="fas fa-scale-balanced text-sky-500"></i> Packaging Without Qty
                                <span class="ml-auto bg-sky-100 text-sky-700 text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="health.missingQty.length"></span>
                            </div>
                            <div class="max-h-44 overflow-y-auto custom-scrollbar divide-y divide-slate-100">
                                <template x-for="r in health.missingQty" :key="'mq-' + r.kode">
                                    <div class="px-4 py-1.5 flex items-center gap-2.5 hover:bg-slate-50 transition-colors">
                                        <span class="text-[9px] font-bold text-blue-500 uppercase shrink-0 w-16 truncate" x-text="r.kode"></span>
                                        <span class="text-xs text-slate-700 truncate flex-1" x-text="r.nama"></span>
                                        <span class="text-[10px] font-bold text-sky-600 bg-sky-50 border border-sky-200 rounded-full px-2 py-0.5 shrink-0" x-text="r.unit"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="health.missingClean.length > 0" class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-700 flex items-center gap-2">
                                <i class="fas fa-tag text-violet-500"></i> Missing Clean Name
                                <span class="ml-auto bg-violet-100 text-violet-700 text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="health.missingClean.length"></span>
                            </div>
                            <div class="max-h-44 overflow-y-auto custom-scrollbar divide-y divide-slate-100">
                                <template x-for="r in health.missingClean" :key="'mc-' + r.kode">
                                    <div class="px-4 py-1.5 flex items-center gap-2.5 hover:bg-slate-50 transition-colors">
                                        <span class="text-[9px] font-bold text-blue-500 uppercase shrink-0 w-16 truncate" x-text="r.kode"></span>
                                        <span class="text-xs text-slate-700 truncate" x-text="r.nama"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="health.duplicates.length > 0" class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-700 flex items-center gap-2">
                                <i class="fas fa-clone text-rose-500"></i> Possible Duplicates
                                <span class="ml-auto bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="health.duplicates.length"></span>
                            </div>
                            <div class="max-h-44 overflow-y-auto custom-scrollbar divide-y divide-slate-100">
                                <template x-for="(group, gi) in health.duplicates" :key="'dup-' + gi">
                                    <div class="px-4 py-1.5 hover:bg-slate-50 transition-colors">
                                        <template x-for="r in group" :key="'dup-' + gi + '-' + r.kode">
                                            <div class="flex items-center gap-2.5 py-0.5">
                                                <span class="text-[9px] font-bold text-blue-500 uppercase shrink-0 w-16 truncate" x-text="r.kode"></span>
                                                <span class="text-xs text-slate-700 truncate" x-text="r.nama"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="health.missingPackaging.length === 0 && health.missingQty.length === 0 && health.missingClean.length === 0 && health.duplicates.length === 0"
                             class="text-center py-8">
                            <i class="fas fa-circle-check text-4xl text-emerald-400 mb-3"></i>
                            <p class="text-sm font-bold text-slate-700">All clean! No data issues found.</p>
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-between items-center shrink-0 rounded-b-2xl">
                        <span class="text-[11px] font-bold text-slate-500" x-text="'Score: ' + healthScore() + '% complete'"></span>
                        <div class="flex gap-2">
                            <button @click="closeHealthModal()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-100 transition-all duration-120 active:scale-95 shadow-sm">Close</button>
                            <button x-show="canManage" @click="openBulkFromHealth()"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all duration-120 shadow-md shadow-blue-500/20 flex items-center gap-1.5 hover:-translate-y-0.5 active:scale-95">
                                <i class="fas fa-table-list"></i> Open Bulk Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            </template>

            {{-- MODAL GROUP: BULK EDIT --}}
            <template x-teleport="body">
            <div id="pm-modal-root-bulk">
            {{-- BACKDROP BULK EDIT MODAL --}}
            <div x-show="showBulkModal" style="display: none; z-index: 999998 !important;" class="fixed inset-0 bg-slate-900/50 z-[999998]" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-80" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeBulkModal()"></div>

            {{-- BULK EDIT MODAL --}}
            <div x-show="showBulkModal" style="display: none; z-index: 999999 !important;"
                 class="fixed inset-0 z-[999999] flex items-center justify-center p-4 pointer-events-none"
                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-120" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div class="pointer-events-auto w-full max-w-6xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[85vh]">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0 rounded-t-2xl">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-table-list text-blue-500"></i> Bulk Edit Items</h3>
                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">Update clean names, packaging &amp; contents for all items at once, then save once.</p>
                        </div>
                        <button @click="closeBulkModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-rose-500 hover:bg-rose-50 transition-colors"><i class="fas fa-times text-sm"></i></button>
                    </div>

                    <div class="px-5 pt-3 shrink-0">
                        <div class="relative w-full max-w-md">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400 text-xs"></i>
                            </div>
                            <input type="text" x-model="bulkSearch" placeholder="Search code / name in this list..."
                                   class="bg-slate-50 border border-slate-300 text-slate-700 text-xs rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 block w-full pl-9 pr-8 px-3 py-2 shadow-sm transition-all">
                            <button type="button" x-show="bulkSearch.length > 0" @click="bulkSearch = ''" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas fa-times-circle text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-5 overflow-y-auto custom-scrollbar grow">
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full text-xs text-left min-w-[880px] table-fixed">
                                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200 sticky top-0">
                                    <tr class="text-[10px] uppercase tracking-wider">
                                        <th scope="col" class="px-3 py-2 w-[290px]">Product</th>
                                        <th scope="col" class="px-3 py-2 w-[150px]">Packaging</th>
                                        <th scope="col" class="px-3 py-2 w-[210px]">Qty / Pack</th>
                                        <th scope="col" class="px-3 py-2 w-[160px]">Preview</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <template x-for="row in filteredBulkRows" :key="row.id">
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-3 py-2">
                                                <div class="text-[9px] font-bold text-blue-500 uppercase leading-tight" x-text="row.kode"></div>
                                                <div class="text-[9px] text-slate-400 leading-tight truncate" x-text="row.nama" :title="row.nama"></div>
                                                <input type="text" x-model="row.name" placeholder="Clean name..."
                                                       class="mt-1.5 w-full bg-white border border-slate-300 rounded-md px-2 py-1 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-xs font-semibold transition-all shadow-sm">
                                            </td>
                                            <td class="px-3 py-2 align-top pt-3">
                                                <input type="text" x-model="row.unit" @input="onBulkUnitChange(row)" :list="'bulk-pack-' + row.id"
                                                       placeholder="— empty —"
                                                       class="w-full bg-white border border-slate-300 rounded-md px-2 py-1 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-xs transition-all shadow-sm">
                                                <datalist :id="'bulk-pack-' + row.id">
                                                    <template x-for="pkg in suggestions" :key="pkg">
                                                        <option :value="pkg"></option>
                                                    </template>
                                                </datalist>
                                            </td>
                                            <td class="px-3 py-2 align-top pt-3">
                                                <div class="flex items-center gap-1.5">
                                                    <input type="number" x-model.number="row.pcs" min="0"
                                                           :disabled="typeFor(row.unit || '') === 'single'"
                                                           class="w-16 shrink-0 bg-white border border-slate-300 rounded-md px-2 py-1 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500 text-xs text-center font-bold disabled:opacity-50 disabled:bg-slate-100 transition-all shadow-sm">
                                                    <span class="text-slate-400 text-xs font-bold">&times;</span>
                                                    <select x-model="row.fill"
                                                            :disabled="typeFor(row.unit || '') === 'single'"
                                                            class="flex-1 min-w-0 bg-white border border-slate-300 rounded-md px-1.5 py-1 text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-xs disabled:opacity-50 disabled:bg-slate-100 transition-all shadow-sm cursor-pointer">
                                                        <template x-for="u in row.fillOptions" :key="u">
                                                            <option :value="u" x-text="u"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 align-top pt-3">
                                                <span class="inline-flex items-center bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap" x-text="bulkPreview(row)"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div x-show="filteredBulkRows.length === 0" class="px-6 py-8 text-center text-slate-400 text-xs font-medium">
                                No matching items.
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-between items-center shrink-0 rounded-b-2xl">
                        <span class="text-[11px] font-bold text-slate-500" x-text="bulkRows.length + ' items ready to update.'"></span>
                        <div class="flex gap-2">
                            <button @click="closeBulkModal()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-100 transition-all duration-120 active:scale-95 shadow-sm">Cancel</button>
                            <button @click="saveBulk()" :disabled="bulkSaving"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all duration-120 shadow-md shadow-blue-500/20 flex items-center gap-1.5 disabled:opacity-60 disabled:cursor-not-allowed hover:-translate-y-0.5 active:scale-95">
                                <i class="fas fa-check-circle"></i>
                                <span x-text="bulkSaving ? 'Saving...' : 'Save All'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            </template>
        </div>
    </div>
</x-layout-users>