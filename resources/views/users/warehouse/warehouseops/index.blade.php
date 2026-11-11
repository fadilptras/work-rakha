<x-layout-users title="{{ $title ?? 'Warehouse Operations' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }
        /* Background & Layout */
        .mesh-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 0;
            pointer-events: none;
            background-color: #ede9fe;
            background-image:
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.6) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
        }
        .page-header-mobile {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1rem; padding: 0.625rem 0.875rem; color: white;
            box-shadow: 0 10px 25px -5px rgba(37,99,235,0.35);
            position: relative; overflow: hidden;
        }
        .page-header-mobile::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 60%);
            transform: rotate(30deg); pointer-events: none;
        }

        /* Root container Warehouse Operations: Fit 1 Layar Penuh pada desktop, tanpa scroll jendela luar */
        @media (min-width: 768px) {
            html.is-tab-frame,
            html.is-tab-frame body {
                overflow: hidden !important;
            }
            body:not(.is-tab-frame) {
                overflow-y: hidden !important;
            }
            .warehouse-ops-root {
                height: calc(100dvh - var(--app-topbar-h, 104px)) !important;
                max-height: calc(100dvh - var(--app-topbar-h, 104px)) !important;
                overflow: hidden !important;
            }
            html.is-tab-frame .warehouse-ops-root {
                height: 100% !important;
                max-height: 100% !important;
                overflow: hidden !important;
            }
            .manage-table-scroll {
                flex: 1 1 0% !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow-y: auto !important;
                overflow-x: auto !important;
            }
            .sj-items-wrap {
                flex: 1 1 0% !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow-y: auto !important;
                overflow-x: auto !important;
            }
        }
        @media (max-width: 767px) {
            .manage-table-scroll { overflow: auto; max-height: 55vh; }
            .sj-items-wrap { overflow: auto; max-height: 38vh; min-height: 140px; }
        }
        .manage-table-scroll table { border-collapse: separate; border-spacing: 0; }
        .manage-table-scroll thead th {
            position: sticky; top: 0; z-index: 20;
            background-clip: padding-box;
            box-shadow: 0 1px 0 #e2e8f0, 0 6px 10px -6px rgba(15,23,42,.18);
        }
        .manage-table-scroll thead th:not([class*="bg-"]) { background-color: #f8fafc; }

        /* Tooltip Kolom */
        .col-tooltip { position: relative; }
        .col-tooltip-popup {
            position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
            margin-top: 8px; width: 260px; background: #1e293b; color: #fff;
            font-size: 11px; line-height: 1.5; font-weight: 500; text-transform: none;
            letter-spacing: normal; border-radius: 10px; padding: 12px 14px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.18); z-index: 50;
            pointer-events: none; white-space: normal;
        }
        .col-tooltip-popup::after {
            content: ''; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%);
            border-width: 6px; border-style: solid; border-color: transparent transparent #1e293b transparent;
        }
        .col-tooltip-popup--right { left: auto; right: 0; transform: none; }
        .col-tooltip-popup--right::after { left: auto; right: 18px; transform: none; }
        .ks-spin { width: 28px; height: 28px; border-radius: 9999px; border: 3px solid #e2e8f0; border-top-color: #2563eb; animation: ks-spin 0.8s linear infinite; }
        @keyframes ks-spin { to { transform: rotate(360deg); } }

        /* Input Alamat */
        .addr-input {
            height: 30px !important;
            line-height: 28px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            white-space: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            text-align: left !important;
        }

        /* SPB Floating Bar */
        .ui-btn--spb { background: #65a30d !important; border-color: #65a30d !important; color: #fff !important; }
        .ui-btn--spb:hover { background: #4d7c0f !important; border-color: #4d7c0f !important; color: #fff !important; }
        .ui-btn--spb.is-in-draft { background: #047857 !important; border-color: #047857 !important; color: #fff !important; }
        .ui-btn--spb:active { transform: scale(0.88); }
        .spb-badge { display: inline-flex; align-items: center; gap: 3px; font-size: 9.5px; font-weight: 700; padding: 1px 6px; border-radius: 9999px; border: 1px solid; line-height: 1.25; white-space: nowrap; }
        .spb-badge--draft { background: #f1f5f9; border-color: #94a3b8; color: #475569; }
        .spb-badge--aktif { background: #fffbeb; border-color: #f59e0b; color: #b45309; }
        .spb-badge--selesai { background: #ecfdf5; border-color: #34d399; color: #047857; }
        .spb-badge--tolak { background: #fff1f2; border-color: #fb7185; color: #e11d48; }
        #spb-floatbar { background: rgba(15, 23, 42, 0.96) !important; position: fixed !important; bottom: 24px !important; right: 24px !important; left: auto !important; transform: none !important; z-index: 9000 !important; width: 440px !important; max-width: calc(100vw - 48px) !important; }
        #spb-floatbar .spb-fb-icon { background: rgba(132,204,22,.18) !important; border: 1px solid rgba(132,204,22,.45) !important; color: #bef264 !important; }
        #spb-floatbar .spb-fb-go { background: #84cc16 !important; color: #0f172a !important; }
        #spb-floatbar .spb-fb-go:hover { background: #a3e635 !important; }
        #spb-floatbar .spb-fb-rm { color: #94a3b8 !important; }
        #spb-floatbar .spb-fb-rm:hover { color: #fda4af !important; }
        #spb-floatbar .spb-fb-name { color: #f8fafc !important; }
        #spb-floatbar .spb-fb-title { color: #ffffff !important; }
        #spb-floatbar .spb-fb-sub { color: #cbd5e1 !important; }
    
        /* Tabel Bordered */
        .table-bordered {
            width: 100%;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border: 1px solid #cbd5e1 !important;
        }
        .table-bordered th,
        .table-bordered td {
            border-right: 1px solid #cbd5e1 !important;
            border-bottom: 1px solid #cbd5e1 !important;
        }
        .table-bordered th:last-child,
        .table-bordered td:last-child {
            border-right: none !important;
        }
        .table-bordered thead th {
            border-bottom: 2px solid #94a3b8 !important;
            background-color: #f1f5f9;
        }
        .table-bordered tbody tr:last-child td {
            border-bottom: none !important;
        }
        .table-bordered tbody tr:hover td:not([class*="bg-"]) {
            background-color: rgba(239, 246, 255, 0.65) !important;
        }
        tr.is-selected td { background-color: #dbeafe !important; }
        .row-checkbox-kelola, .row-check-all { accent-color: #2563eb; }

        /* Label filter kompak di seluruh Warehouse Ops */
        .filter-label-compact {
            display: block !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            color: #64748b !important;
            margin-bottom: 3px !important;
            line-height: 1.2 !important;
        }

        /* Tabel Data Mutation — compact, rapi & proporsional */
        .table-mutation {
            font-size: 11px !important;
        }
        .table-mutation thead th {
            font-size: 10px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.03em !important;
            padding: 6px 8px !important;
            color: #475569 !important;
            line-height: 1.2 !important;
            white-space: nowrap !important;
            background-color: #f8fafc !important;
        }
        .table-mutation tbody td {
            font-size: 11px !important;
            padding: 5px 8px !important;
            line-height: 1.25 !important;
        }
        .table-mutation .cell-sub {
            font-size: 9.5px !important;
            line-height: 1.2 !important;
            color: #94a3b8 !important;
        }
        .table-mutation .cell-badge {
            font-size: 9px !important;
            padding: 1px 4px !important;
            line-height: 1 !important;
        }

        /* Delivery Note Edit Modal Styles */
        .sj-exp-addr {
            display: grid;
            grid-template-columns: minmax(110px, 1fr) minmax(0, 2fr);
            gap: 8px;
            align-items: end;
        }
        .sj-items-wrap { overflow: auto; max-height: 38vh; min-height: 140px; }
        .sj-items-wrap table { border-collapse: separate; border-spacing: 0; }
        .sj-items-wrap thead th {
            position: sticky; top: 0; z-index: 10; background-color: #f8fafc;
            background-clip: padding-box;
            box-shadow: 0 1px 0 #e2e8f0;
        }

        /* Data Product Tab Styles */
        .mobile-product-card {
            background: #ffffff; border: 1px solid #e2e8f0; border-left: 4px solid #3b82f6; border-radius: 0.9rem;
            padding: 0.8rem 0.9rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .mobile-product-card.mpc-zero { border-left-color: #f43f5e; }
        tr.is-selected td, .row-item.is-selected td { background-color: #dbeafe !important; }
        .mobile-product-card.is-selected { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25); }
        .product-row-check { accent-color: #2563eb; }
        .product-row-check-all { accent-color: #2563eb; }
    </style>
    @endpush

    <div class="warehouse-ops-root flex flex-col w-full relative overflow-hidden text-slate-800">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 pt-3 sm:pt-3.5 pb-3 sm:pb-3.5 space-y-3 flex-1 min-h-0 flex flex-col items-stretch overflow-hidden">
            {{-- Header & Navigasi Tab --}}
            <div class="hidden md:block shrink-0">
                <x-ui.page-header title="Warehouse Operations" subtitle="Product data, mutasi, input keluar & masuk dalam satu halaman." icon="fa-warehouse" :sticky="false">
                    <x-slot:controls>
                        <div class="flex space-x-1 bg-white/10 p-1 rounded-full border border-white/20 backdrop-blur-md overflow-x-auto shrink-0 w-full xl:w-auto relative z-10">
                            <a href="{{ route('warehouse.index', ['tab' => 'data-product']) }}" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab === 'data-product' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-boxes-stacked mr-2"></i> Data Product
                            </a>
                            <a href="{{ route('warehouse.index', ['tab' => 'produk']) }}" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab === 'produk' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-chart-line mr-2"></i> Monitoring Product
                            </a>
                            <a href="{{ route('warehouse.index', ['tab' => 'mutasi']) }}" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab === 'mutasi' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-table mr-2"></i> Data Mutation
                            </a>
                            <a href="{{ route('warehouse.index', ['tab' => 'keluar']) }}" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab === 'keluar' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-arrow-up mr-2"></i> Keluar
                            </a>
                            <a href="{{ route('warehouse.index', ['tab' => 'masuk']) }}" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab === 'masuk' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-arrow-down mr-2"></i> Masuk
                            </a>
                        </div>
                    </x-slot:controls>
                </x-ui.page-header>
            </div>
            <div class="md:hidden space-y-3 shrink-0">
                <div class="page-header-mobile flex items-center justify-between gap-3">
                    <div class="relative z-10 min-w-0">
                        <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Warehouse Operations</h2>
                        <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">Produk, mutasi, keluar & masuk.</p>
                    </div>
                    <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center text-white text-sm shrink-0 relative z-10"><i class="fas fa-warehouse"></i></div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-1.5 grid grid-cols-5 gap-1">
                    <a href="{{ route('warehouse.index', ['tab' => 'data-product']) }}" class="flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold {{ $tab === 'data-product' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500' }}">
                        <i class="fas fa-boxes-stacked text-sm"></i><span class="text-[10px] uppercase">Produk</span>
                    </a>
                    <a href="{{ route('warehouse.index', ['tab' => 'produk']) }}" class="flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold {{ $tab === 'produk' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500' }}">
                        <i class="fas fa-chart-line text-sm"></i><span class="text-[10px] uppercase">Monitor</span>
                    </a>
                    <a href="{{ route('warehouse.index', ['tab' => 'mutasi']) }}" class="flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold {{ $tab === 'mutasi' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500' }}">
                        <i class="fas fa-table text-sm"></i><span class="text-[10px] uppercase">Mutasi</span>
                    </a>
                    <a href="{{ route('warehouse.index', ['tab' => 'keluar']) }}" class="flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold {{ $tab === 'keluar' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500' }}">
                        <i class="fas fa-arrow-up text-sm"></i><span class="text-[10px] uppercase">Keluar</span>
                    </a>
                    <a href="{{ route('warehouse.index', ['tab' => 'masuk']) }}" class="flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold {{ $tab === 'masuk' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500' }}">
                        <i class="fas fa-arrow-down text-sm"></i><span class="text-[10px] uppercase">Masuk</span>
                    </a>
                </div>
            </div>


            {{-- TAB CONTENTS (MODULARISASI PER TAB) --}}
            @if($tab === 'data-product')
                @include('users.warehouse.warehouseops.data-product')
            @elseif($tab === 'produk')
                @include('users.warehouse.warehouseops.monitoring-product')
            @elseif($tab === 'mutasi')
                @include('users.warehouse.warehouseops.data-mutation')
            @elseif($tab === 'keluar' || $tab === 'masuk')
                @include('users.warehouse.warehouseops.form-mutasi')
            @endif

        </div>
    </div>

            <div id="editMutasiModal" data-ui-modal class="modal-content-canvas hidden" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-transparent" onclick="closeEditMutasiModal()"></div>
                <div class="relative z-10 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-100 w-full max-w-lg my-auto mx-auto" style="max-height: calc(100% - 1rem);">
                    <form id="editMutasiForm" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-start gap-3 bg-slate-50/80 shrink-0">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-slate-800">Edit Baris Mutasi <span id="editMutasiTitleId" class="text-blue-600"></span></h3>
                                <p class="text-xs text-slate-500 font-medium mt-0.5"><span id="editMutasiBarang" class="font-bold"></span> · <span id="editMutasiArah"></span><span id="editMutasiPihak"></span> — produk, arah & pihak tidak bisa diubah.</p>
                            </div>
                            <button type="button" onclick="closeEditMutasiModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-colors flex items-center justify-center shrink-0"><i class="fas fa-times text-xs"></i></button>
                        </div>
                        <div class="p-5 space-y-3 overflow-y-auto" style="max-height: calc(85vh - 120px);">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="ui-label">Tanggal <span class="text-red-500">*</span></label>
                                    <input type="date" lang="id-ID" name="tanggal" id="editMutasiTanggal" required max="{{ today()->toDateString() }}" class="ui-input ui-input--sm !bg-white">
                                </div>
                                <div>
                                    <label class="ui-label">Qty <span class="text-red-500">*</span></label>
                                    <input type="number" name="qty" id="editMutasiQty" step="any" required class="ui-input ui-input--sm !bg-white">
                                </div>
                                <div>
                                    <label class="ui-label">No. Surat Jalan</label>
                                    <input type="text" name="doc_no" id="editMutasiDocNo" maxlength="100" class="ui-input ui-input--sm !bg-white">
                                </div>
                                <div>
                                    <label class="ui-label">No. PO / Referensi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                    <input type="text" name="po_no" id="editMutasiPoNo" maxlength="100" class="ui-input ui-input--sm !bg-white">
                                </div>
                                <div>
                                    <label class="ui-label">Ekspedisi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                    <input type="text" name="expedition" id="editMutasiExpedition" maxlength="100" class="ui-input ui-input--sm !bg-white">
                                </div>
                                <div>
                                    <label class="ui-label">Alamat Kirim / Supplier</label>
                                    <input type="text" name="ship_address" id="editMutasiShipAddress" maxlength="2000" class="ui-input ui-input--sm !bg-white">
                                </div>
                                <div>
                                    <label class="ui-label">Batch No.</label>
                                    <input type="text" name="batch_no" id="editMutasiBatchNo" maxlength="100" class="ui-input ui-input--sm !bg-white">
                                </div>
                                <div>
                                    <label class="ui-label">Expired Date</label>
                                    <input type="date" lang="id-ID" name="expiry_date" id="editMutasiExpiryDate" class="ui-input ui-input--sm !bg-white">
                                </div>
                                <div class="col-span-2">
                                    <label class="ui-label">Keterangan <span class="text-red-500">*</span></label>
                                    <input type="text" name="keterangan" id="editMutasiKeterangan" maxlength="1000" required class="ui-input ui-input--sm !bg-white">
                                </div>
                            </div>
                            <p class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 leading-relaxed">
                                <i class="fas fa-info-circle mr-1"></i> Ubah qty/tanggal/expired = reversal + mutasi koreksi baru (jejak audit utuh, stok otomatis menyesuaikan).
                            </p>
                        </div>
                        <div class="bg-slate-50 px-5 py-3 border-t border-slate-100 flex justify-end gap-2 shrink-0">
                            <button type="button" onclick="closeEditMutasiModal()" class="h-[34px] px-4 rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">Batal</button>
                            <button type="submit" class="h-[34px] px-5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition-colors">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>


    {{-- Modal Tambah / Edit Produk (Canvas Konten Terisolasi - Pilihan B) --}}
    <div id="product-modal" data-ui-modal class="modal-content-canvas hidden" role="dialog" aria-modal="true">
        <div id="product-modal-backdrop" class="absolute inset-0 bg-transparent" onclick="closeProductModal()"></div>
        <div id="product-modal-panel" class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl overflow-hidden flex flex-col z-10 opacity-0 scale-95 transition-all duration-200 my-auto mx-auto" style="max-height: calc(100% - 1rem);">
            <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm"><i class="fas fa-box-open text-xs"></i></span>
                    <div class="min-w-0">
                        <h5 id="product-modal-title" class="font-bold text-sm text-slate-800 leading-tight">Tambah Produk</h5>
                        <p id="product-modal-subtitle" class="text-[11px] text-slate-400 font-medium mt-0.5">Kode, nama, pack & qty kemasan.</p>
                    </div>
                </div>
                <button type="button" onclick="closeProductModal()" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0" title="Tutup"><i class="fas fa-times text-xs"></i></button>
            </div>

            <div class="p-5 space-y-4 overflow-y-auto flex-1">
                <form id="product-form" action="{{ route('warehouse.product.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="tab" id="product-form-tab" value="{{ $tab }}">
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
                                @foreach(($packCatalog['suggestions'] ?? []) as $sug)
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
                            <div class="flex items-center justify-between mb-1">
                                <label class="ui-label mb-0" id="stock-label">Stok Awal (Manual)</label>
                                {{-- Icon Info (i) dengan Popup Tooltip --}}
                                <div id="stock-opname-info-wrap" class="hidden relative inline-block text-left" x-data="{ open: false }">
                                    <button type="button" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open"
                                            class="inline-flex items-center justify-center w-5 h-5 rounded-full text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 transition cursor-help border border-blue-200 shadow-2xs"
                                            title="Info Pengaturan Stok">
                                        <i class="fas fa-info text-[10px] font-bold"></i>
                                    </button>
                                    <div x-show="open" x-cloak 
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         @mouseenter="open = true" @mouseleave="open = false"
                                         class="absolute right-0 bottom-full mb-2 w-64 p-3 bg-slate-900 text-white rounded-xl shadow-2xl z-50 text-left border border-slate-700 pointer-events-auto">
                                        <div class="flex items-center gap-1.5 font-bold text-amber-300 text-xs mb-1">
                                            <i class="fas fa-lock text-[11px]"></i> Stok Diatur Lewat Opname
                                        </div>
                                        <p class="text-[11px] text-slate-200 leading-relaxed">
                                            Produk ini <b>sudah pernah di-opname</b>. Nilai stok tidak dapat diubah di sini demi integritas audit stok fisik.
                                        </p>
                                        <div class="mt-2 pt-2 border-t border-slate-700/80">
                                            <a href="{{ route('warehouse.stockopname.catat') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-300 hover:text-sky-200 underline">
                                                <span>Buka Menu Catat Opname</span>
                                                <i class="fas fa-arrow-right text-[9px]"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="number" name="stock" id="f-stok" min="0" step="1" value="0" class="ui-input font-bold text-slate-800 transition-colors">
                            <p id="stock-hint" class="text-[10px] text-slate-400 mt-1 font-medium">Stok awal produk saat pertama kali didaftarkan.</p>

                            {{-- Alert Box Informasi Terkunci --}}
                            <div id="stock-opname-alert" class="hidden mt-2 p-2.5 bg-blue-50/90 border border-blue-200/80 rounded-xl flex items-start gap-2.5 text-blue-900">
                                <i class="fas fa-info-circle text-blue-600 text-xs mt-0.5 shrink-0"></i>
                                <div class="min-w-0 text-[11px] leading-relaxed">
                                    <span class="font-bold">Stok Terkunci:</span> Produk ini sudah pernah di-opname. Edit stok harus melalui menu 
                                    <a href="{{ route('warehouse.stockopname.catat') }}" class="font-bold underline text-blue-700 hover:text-blue-900 inline-flex items-center gap-1">
                                        Stock Opname <i class="fas fa-external-link-alt text-[9px]"></i>
                                    </a>.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 space-y-2 overflow-hidden">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider shrink-0"><i class="fas fa-boxes mr-1"></i>Pack:</span>
                            <div class="flex flex-wrap gap-1.5 min-w-0" id="pack-chips">
                                @foreach(($packCatalog['suggestions'] ?? []) as $sug)
                                <button type="button" onclick="setPack('{{ $sug }}')" class="px-2 py-0.5 rounded border border-slate-200 bg-white text-slate-500 hover:bg-blue-50 hover:text-blue-600 text-[10px] font-bold">{{ $sug }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5" id="qty-preset-group">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider shrink-0">Qty:</span>
                            <div class="flex flex-wrap gap-1.5 min-w-0" id="qty-presets">
                                @foreach(($packCatalog['presets'] ?? []) as $preset)
                                <button type="button" onclick="setQty({{ $preset }})" class="px-2 py-0.5 rounded border border-slate-200 bg-white text-slate-500 hover:bg-blue-50 hover:text-blue-600 text-[10px] font-bold">{{ $preset }}</button>
                                @endforeach
                            </div>
                            <p class="text-[11px] text-slate-500 w-full sm:w-auto sm:ml-auto">Kemasan: <span class="font-bold text-slate-700" id="pack-preview">-</span></p>
                        </div>
                    </div>
                </form>
            </div>

            <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex gap-2 justify-end shrink-0">
                <button type="button" onclick="closeProductModal()" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="submit" form="product-form" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- Form tersembunyi eksekusi hapus single & bulk produk --}}
    <form id="product-delete-form" action="" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    <form id="product-bulk-delete-form" action="{{ route('warehouse.product.bulk_destroy') }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
        <div id="product-bulk-delete-ids"></div>
    </form>

    {{-- Popup detail dokumen (Canvas Konten Terisolasi - Pilihan B) --}}
    <div id="docModal" data-ui-modal class="modal-content-canvas hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-transparent" onclick="closeDocModal()"></div>
        <div class="relative z-10 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-100 w-full max-w-5xl my-auto mx-auto" style="max-height: calc(100% - 1rem);">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-start gap-3 bg-slate-50/80 shrink-0">
                <div class="min-w-0">
                    <div id="docModalNo" class="font-mono text-[11px] font-bold tracking-wider text-slate-400"></div>
                    <h3 id="docModalPihak" class="text-lg font-black text-slate-800 leading-tight truncate">Dokumen</h3>
                    <p id="docModalMeta" class="text-xs text-slate-500 font-medium mt-0.5"></p>
                </div>
                <button type="button" onclick="closeDocModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center shrink-0"><i class="fas fa-times"></i></button>
            </div>
            <div id="docModalBody" class="p-6 overflow-y-auto space-y-4 flex-1"></div>
        </div>
    </div>

    {{-- Popup kartu stock (Canvas Konten Terisolasi - Pilihan B) --}}
    <div id="kartuModal" data-ui-modal class="modal-content-canvas hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-transparent" onclick="closeKartuModal()"></div>
        <div class="relative z-10 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200/90 my-auto mx-auto" style="width: 98%; max-width: 1850px; min-height: 260px; max-height: min(82vh, 640px);">
            <div class="px-5 py-2.5 border-b border-slate-200/90 bg-slate-50 flex items-center justify-between gap-3 shrink-0">
                {{-- Sisi Kiri: Kode + Nama Produk --}}
                <div class="flex items-center gap-2.5 min-w-0">
                    <span id="kartuModalKode" class="font-mono text-xs font-bold text-sky-700 bg-sky-50 border border-sky-200 px-2 py-0.5 rounded-md shrink-0"></span>
                    <h3 id="kartuModalNama" class="text-sm sm:text-base font-black text-slate-800 leading-tight truncate max-w-[280px] sm:max-w-[480px] 2xl:max-w-[720px]">Kartu Stock</h3>
                </div>

                {{-- Sisi Kanan: Stats Stok + Tombol Masuk/Keluar + Info (i) + Close [X] --}}
                <div class="flex items-center flex-wrap gap-2 shrink-0 ml-auto">
                    {{-- Stats Stok Pill --}}
                    <div class="flex items-center gap-1.5 text-xs bg-white border border-slate-200 px-2.5 py-1 rounded-lg shadow-2xs">
                        <span class="text-slate-500 font-medium">Stok:</span>
                        <span id="kartuModalStock" class="font-black text-slate-900">-</span>
                        <span id="kartuModalUnit" class="text-slate-600 font-bold text-[11px]"></span>
                        <span class="text-slate-300">·</span>
                        <span class="text-slate-500 font-medium">Awal:</span>
                        <span id="kartuModalSaldo" class="font-black text-blue-700">-</span>
                        <span class="text-slate-300">·</span>
                        <span class="text-[10px] text-slate-400 font-medium">Update: <span id="kartuModalUpdated" class="font-bold text-slate-600">-</span></span>
                    </div>

                    {{-- Tombol Cepat Masuk & Keluar --}}
                    <div class="flex items-center gap-1 pl-1 border-l border-slate-200">
                        <button type="button" onclick="triggerKartuQuickMutasi('masuk')"
                            class="h-[28px] px-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer"
                            title="Catat barang masuk untuk produk ini">
                            <i class="fas fa-plus-circle text-emerald-200 text-xs"></i> <span>Masuk</span>
                        </button>
                        <button type="button" onclick="triggerKartuQuickMutasi('keluar')"
                            class="h-[28px] px-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 active:scale-95 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer"
                            title="Catat barang keluar untuk produk ini">
                            <i class="fas fa-minus-circle text-rose-200 text-xs"></i> <span>Keluar</span>
                        </button>

                        {{-- Icon Info "i" dengan Popover Keterangan --}}
                        <div class="relative inline-flex items-center" x-data="{ showInfo: false }">
                            <button type="button" @mouseenter="showInfo = true" @mouseleave="showInfo = false" @click="showInfo = !showInfo"
                                class="w-6 h-6 rounded-full bg-slate-100 hover:bg-blue-50 text-slate-400 hover:text-blue-600 border border-slate-200 flex items-center justify-center transition-colors cursor-pointer"
                                title="Informasi Transaksi Langsung">
                                <i class="fas fa-info text-[10px]"></i>
                            </button>
                            <div x-show="showInfo" x-cloak
                                class="absolute right-0 top-full mt-2 w-72 p-3 bg-slate-900 text-white rounded-xl shadow-2xl text-[11px] leading-relaxed z-50 pointer-events-none border border-slate-700">
                                <div class="font-bold text-emerald-400 flex items-center gap-1.5 mb-1">
                                    <i class="fas fa-bolt text-xs"></i> Input Mutasi Langsung
                                </div>
                                <p class="text-slate-200 text-[11px] leading-snug">
                                    Gunakan tombol <b class="text-emerald-300">Masuk</b> atau <b class="text-rose-300">Keluar</b> untuk mencatat transaksi fisik produk ini secara instan.
                                </p>
                                <div class="mt-1.5 pt-1.5 border-t border-slate-700/80 text-[10px] text-slate-400 flex items-center gap-1">
                                    <i class="fas fa-check-circle text-emerald-400 text-[10px]"></i>
                                    <span>Saldo dan baris kartu stock langsung ter-update otomatis.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Close [X] --}}
                    <button type="button" onclick="closeKartuModal()" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center shrink-0 cursor-pointer ml-1" title="Tutup (Esc)"><i class="fas fa-times text-xs"></i></button>
                </div>
            </div>
            <div id="kartuModalToast" class="hidden mx-5 my-1 px-4 py-2 rounded-xl text-xs font-bold shrink-0"></div>
            <div id="kartuModalBody" class="p-0 overflow-hidden flex-1 flex flex-col min-h-0"></div>
        </div>
    </div>

    @include('users.warehouse.stock-card.edit-modal')
    @include('users.warehouse.stock-card.quick-mutasi-modal')
    @include('users.warehouse.warehouseops.doc-edit-modal')

    @push('scripts')
    {{-- Modal & Dialog Runtime --}}
    <script>
        // ==========================================
        // DATA PRODUCT MODAL & MANAGEMENT
        // ==========================================
        function openProductModal() {
            const modal = document.getElementById('product-modal');
            const panel = document.getElementById('product-modal-panel');
            const backdrop = document.getElementById('product-modal-backdrop');
            if (modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        backdrop?.classList.remove('opacity-0');
                        panel?.classList.remove('opacity-0', 'scale-95');
                    });
                });
            }
        }

        function closeProductModal() {
            const modal = document.getElementById('product-modal');
            const panel = document.getElementById('product-modal-panel');
            const backdrop = document.getElementById('product-modal-backdrop');
            if (modal && !modal.classList.contains('hidden')) {
                backdrop?.classList.add('opacity-0');
                panel?.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = '';
                }, 180);
            }
        }

        window.openProductModal = openProductModal;
        window.closeProductModal = closeProductModal;

        document.getElementById('product-modal')?.addEventListener('click', function (e) {
            if (e.target === this) closeProductModal();
        });

        function openProductCreate() {
            const form = document.getElementById('product-form');
            if (!form) return;
            form.action = "{{ route('warehouse.product.store') }}";
            document.getElementById('product-method').innerHTML = '';
            document.getElementById('f-kode').value = '';
            document.getElementById('f-nama').value = '';
            document.getElementById('f-satuan').value = '';
            
            const stokInput = document.getElementById('f-stok');
            stokInput.value = 0;
            stokInput.disabled = false;
            stokInput.classList.remove('bg-slate-100', 'text-slate-500', 'cursor-not-allowed');

            document.getElementById('f-qty').value = 1;
            onPackChange('Pcs');
            document.getElementById('stock-label').textContent = 'Stok Awal (Manual)';
            document.getElementById('stock-hint').textContent = 'Stok awal produk saat pertama kali didaftarkan.';
            document.getElementById('stock-awal-wrap').style.display = '';
            document.getElementById('stock-opname-info-wrap')?.classList.add('hidden');
            document.getElementById('stock-opname-alert')?.classList.add('hidden');
            document.getElementById('product-modal-title').textContent = 'Tambah Produk';
            openProductModal();
            setTimeout(() => document.getElementById('f-kode')?.focus(), 80);
        }

        function openProductEdit(btn) {
            const item = JSON.parse(btn.dataset.item || '{}');
            const form = document.getElementById('product-form');
            if (!form) return;
            form.action = "{{ route('warehouse.product.update', ':id') }}".replace(':id', item.id);
            document.getElementById('product-method').innerHTML = '@method("PUT")';
            document.getElementById('f-kode').value = item.kode === '-' ? '' : (item.kode || '');
            document.getElementById('f-nama').value = item.nama || '';
            document.getElementById('f-satuan').value = item.kemasan || '';
            document.getElementById('f-qty').value = item.pcs_per_unit >= 1 ? item.pcs_per_unit : 1;
            onPackChange(item.fill_unit || 'Pcs');

            const stokInput = document.getElementById('f-stok');
            const infoWrap = document.getElementById('stock-opname-info-wrap');
            const alertBox = document.getElementById('stock-opname-alert');
            const stokAwal = (item.stok_awal !== undefined && item.stok_awal !== null) ? item.stok_awal : (item.stok ?? 0);

            if (item.has_opname) {
                // Produk yang sudah sempat di-opname: stok dikunci dan diedit lewat Stock Opname
                stokInput.value = stokAwal;
                stokInput.disabled = true;
                stokInput.classList.add('bg-slate-100', 'text-slate-500', 'cursor-not-allowed');
                document.getElementById('stock-label').textContent = 'Stok Awal / Opname (Terkunci)';
                document.getElementById('stock-hint').textContent = 'Stok awal/opname fisik: ' + stokAwal + ' ' + (item.satuan || 'Pcs') + ' (Sisa realtime saat ini: ' + item.stok + '). Koreksi hanya dapat dilakukan melalui menu Stock Opname.';
                infoWrap?.classList.remove('hidden');
                alertBox?.classList.remove('hidden');
            } else {
                // Produk baru yang belum pernah di-opname: tampilkan angka stok awal pendaftaran (bukan sisa realtime)
                stokInput.value = stokAwal;
                stokInput.disabled = false;
                stokInput.classList.remove('bg-slate-100', 'text-slate-500', 'cursor-not-allowed');
                document.getElementById('stock-label').textContent = 'Stok Awal (Manual)';
                document.getElementById('stock-hint').textContent = 'Ubah angka ini untuk menyesuaikan stok awal pendaftaran. (Sisa stok realtime saat ini: ' + item.stok + ' ' + (item.satuan || 'Pcs') + ')';
                infoWrap?.classList.add('hidden');
                alertBox?.classList.add('hidden');
            }

            document.getElementById('stock-awal-wrap').style.display = '';
            document.getElementById('product-modal-title').textContent = 'Edit Produk';
            openProductModal();
            setTimeout(() => document.getElementById('f-nama')?.focus(), 80);
        }

        window.openEditBarangFromKartu = function (productId) {
            if (typeof closeKartuModal === 'function') closeKartuModal();
            const btn = document.querySelector(`button[data-product-id="${productId}"][onclick*="openProductEdit"]`)
                || document.querySelector(`button[data-item*='"id":${productId}'][onclick*="openProductEdit"]`)
                || document.querySelector(`button[data-item*='"id": "${productId}"'][onclick*="openProductEdit"]`);
            if (btn && typeof openProductEdit === 'function') {
                openProductEdit(btn);
            } else {
                window.location.href = "{{ route('warehouse.index', ['tab' => 'data-product']) }}?edit_id=" + productId;
            }
        };

        // Otomatis buka modal edit bila ada query edit_id
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const editId = urlParams.get('edit_id');
            if (editId) {
                setTimeout(() => {
                    const btn = document.querySelector(`button[data-product-id="${editId}"][onclick*="openProductEdit"]`)
                        || document.querySelector(`button[data-item*='"id":${editId}'][onclick*="openProductEdit"]`);
                    if (btn && typeof openProductEdit === 'function') openProductEdit(btn);
                }, 300);
            }
        });

        function openProductDelete(btn) {
            const item = JSON.parse(btn.dataset.item || '{}');
            const nama = item.nama || 'produk ini';
            uiConfirmDelete({
                title: 'Hapus produk ini?',
                text: `Produk "${nama}" akan dihapus dari Data Product.`,
                confirmText: 'Ya, hapus!',
                onConfirm: () => {
                    const form = document.getElementById('product-delete-form');
                    form.action = "{{ route('warehouse.product.destroy', ':id') }}".replace(':id', item.id);
                    form.submit();
                }
            });
        }

        const PACK_CATALOG = {!! json_encode($packCatalog['catalog'] ?? [], JSON_UNESCAPED_UNICODE) !!};
        function packMeta(u) { return PACK_CATALOG[(u || '').trim().toLowerCase()] || {type: 'flex', units: ['Pcs']}; }
        function packType(u) { return packMeta(u).type; }
        function onPackChange(preferFill) {
            const packEl = document.getElementById('f-satuan');
            if (!packEl) return;
            const pack = packEl.value || '';
            const meta = packMeta(pack);
            const fillSel = document.getElementById('f-fill');
            if (!fillSel) return;
            const cur = (preferFill || fillSel.value || 'Pcs').trim() || 'Pcs';
            fillSel.innerHTML = (meta.units || ['Pcs']).map(function (u) { return '<option value="' + u + '">' + u + '</option>'; }).join('');
            fillSel.value = (meta.units || []).includes(cur) ? cur : ((meta.units || [])[0] || 'Pcs');
            const single = packType(pack) === 'single';
            const qtyWrap = document.getElementById('qty-wrap');
            const presetGroup = document.getElementById('qty-preset-group');
            const singleNote = document.getElementById('qty-single-note');
            if (qtyWrap) qtyWrap.style.display = single ? 'none' : '';
            if (presetGroup) presetGroup.style.display = single ? 'none' : '';
            if (singleNote) singleNote.classList.toggle('hidden', !single);
            if (single) { document.getElementById('f-qty').value = 1; }
            const packClear = document.getElementById('pack-clear');
            if (packClear) packClear.style.display = pack ? '' : 'none';
            updatePackPreview();
        }
        function setPack(v) { 
            const el = document.getElementById('f-satuan'); 
            if (el) { el.value = v; onPackChange(); } 
        }
        function clearPack() { 
            const el = document.getElementById('f-satuan'); 
            if (el) { el.value = ''; onPackChange(); } 
        }
        function setQty(v) { 
            const el = document.getElementById('f-qty'); 
            if (el) { el.value = v; updatePackPreview(); } 
        }
        function updatePackPreview() {
            const packEl = document.getElementById('f-satuan');
            if (!packEl) return;
            const pack = (packEl.value || '').trim();
            const qty = parseInt(document.getElementById('f-qty')?.value || '1', 10);
            const fillSel = document.getElementById('f-fill');
            const fill = (fillSel?.value || 'Pcs').trim() || 'Pcs';
            let text = '-';
            if (pack && qty >= 1 && packType(pack) !== 'single') text = pack + '/ ' + qty + ' ' + fill;
            else if (pack) text = pack;
            const previewEl = document.getElementById('pack-preview');
            if (previewEl) previewEl.textContent = text;
        }

        // Bulk Selection Produk
        function getSelectedProductIds() {
            const values = Array.from(document.querySelectorAll('.product-row-check:checked')).map(function (cb) { return cb.value; });
            return Array.from(new Set(values));
        }
        function markProductSelection(checkbox) {
            const row = checkbox.closest('tr');
            if (row) row.classList.toggle('is-selected', checkbox.checked);
            const card = checkbox.closest('.mobile-product-card');
            if (card) card.classList.toggle('is-selected', checkbox.checked);
        }
        function syncProductCheckboxes(value, checked) {
            document.querySelectorAll('.product-row-check').forEach(function (cb) {
                if (cb.value === value && cb.checked !== checked) {
                    cb.checked = checked;
                    markProductSelection(cb);
                }
            });
        }
        function updateProductBulkSelectionUi() {
            const ids = getSelectedProductIds();
            const boxes = document.querySelectorAll('.product-row-check');
            const allValues = Array.from(new Set(Array.from(boxes).map(function (cb) { return cb.value; })));
            const checkAll = document.getElementById('product-check-all');
            if (checkAll) {
                checkAll.checked = allValues.length > 0 && ids.length === allValues.length;
                checkAll.indeterminate = ids.length > 0 && ids.length < allValues.length;
            }
            const bar = document.getElementById('product-bulk-bar');
            if (bar) {
                bar.style.display = ids.length ? 'flex' : 'none';
                const count = document.getElementById('product-bulk-bar-count');
                if (count) count.textContent = ids.length;
            }
        }
        function clearProductBulkSelection() {
            document.querySelectorAll('.product-row-check').forEach(function (cb) {
                cb.checked = false;
                markProductSelection(cb);
            });
            updateProductBulkSelectionUi();
        }
        function openProductBulkDelete() {
            const ids = getSelectedProductIds();
            if (!ids.length) return;
            uiConfirmDelete({
                title: 'Hapus ' + ids.length + ' produk terpilih?',
                text: 'Semua produk yang dipilih akan dihapus dari Data Product.',
                confirmText: 'Ya, hapus semua!',
                onConfirm: () => {
                    const form = document.getElementById('product-bulk-delete-form');
                    document.getElementById('product-bulk-delete-ids').innerHTML = ids.map(function (id) {
                        return '<input type="hidden" name="ids[]" value="' + id + '">';
                    }).join('');
                    form.submit();
                }
            });
        }

        // Modal Edit Baris Mutasi (Canvas Konten Terisolasi - Pilihan B)
        window.openEditMutasiModal = function(item) {
            const modal = document.getElementById('editMutasiModal');
            if (!modal) return;
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            const elId = document.getElementById('editMutasiTitleId');
            const elBarang = document.getElementById('editMutasiBarang');
            const elArah = document.getElementById('editMutasiArah');
            const elPihak = document.getElementById('editMutasiPihak');
            if (elId) elId.textContent = '#' + (item.id || '');
            if (elBarang) elBarang.textContent = item.barang || '';
            if (elArah) elArah.textContent = item.arah || '';
            if (elPihak) elPihak.textContent = item.pihak ? ' · ' + item.pihak : '';

            const form = document.getElementById('editMutasiForm');
            if (form) {
                form.action = '{{ url("warehouse/stock/pencatatan") }}/' + item.id;
            }
            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = (val !== null && val !== undefined) ? val : '';
            };
            setVal('editMutasiTanggal', item.tanggal);
            setVal('editMutasiQty', item.qty);
            setVal('editMutasiDocNo', item.doc_no);
            setVal('editMutasiPoNo', item.po_no);
            setVal('editMutasiExpedition', item.expedition);
            setVal('editMutasiShipAddress', item.ship_address);
            setVal('editMutasiBatchNo', item.batch_no);
            setVal('editMutasiExpiryDate', item.expiry_date);
            setVal('editMutasiKeterangan', item.keterangan);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };

        window.closeEditMutasiModal = function() {
            const modal = document.getElementById('editMutasiModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            document.body.style.overflow = '';
        };

        document.getElementById('editMutasiForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const submitBtn = form.querySelector('button[type="submit"]');
            const origText = submitBtn ? submitBtn.innerHTML : 'Simpan Perubahan';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';
            }
            try {
                const fd = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok || json.success === false) {
                    throw new Error(json.message || 'Gagal menyimpan perubahan.');
                }
                closeEditMutasiModal();
                window.location.reload();
            } catch (err) {
                alert(err.message || 'Terjadi kesalahan saat menyimpan.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origText;
                }
            }
        });

        // Bulk Selection Tab Data Mutation (Model Data Product)
        function getSelectedMutasiIds() {
            const ids = [];
            document.querySelectorAll('.row-checkbox-kelola:checked').forEach(function (cb) {
                if (cb.value) ids.push(cb.value);
            });
            return ids;
        }

        function updateBulkMutasiUi() {
            const selected = getSelectedMutasiIds();
            const bulkBar = document.getElementById('bulk-bar-mutasi');
            const bulkCount = document.getElementById('bulk-bar-mutasi-count');
            const checkAll = document.getElementById('check-all-kelola');
            const allBoxes = document.querySelectorAll('.row-checkbox-kelola');

            if (bulkCount) bulkCount.textContent = selected.length;
            if (bulkBar) bulkBar.style.display = selected.length > 0 ? 'flex' : 'none';
            if (checkAll) {
                checkAll.checked = allBoxes.length > 0 && selected.length === allBoxes.length;
                checkAll.indeterminate = selected.length > 0 && selected.length < allBoxes.length;
            }
        }

        function markMutasiSelection(cb) {
            const tr = cb.closest('tr');
            if (tr) tr.classList.toggle('is-selected', cb.checked);
        }

        function clearBulkMutasiSelection() {
            document.querySelectorAll('.row-checkbox-kelola').forEach(function (cb) {
                cb.checked = false;
                markMutasiSelection(cb);
            });
            updateBulkMutasiUi();
        }

        function openBulkDeleteMutasi() {
            const ids = getSelectedMutasiIds();
            if (!ids.length) return;
            uiConfirmDelete({
                title: 'Hapus ' + ids.length + ' baris mutasi terpilih?',
                text: 'Stok tiap baris dikembalikan via reversal. Jejak audit tetap tersimpan.',
                confirmText: 'Ya, hapus semua!',
                onConfirm: () => {
                    const form = document.getElementById('bulk-delete-mutasi-form');
                    document.getElementById('bulk-delete-mutasi-ids').innerHTML = ids.map(function (id) {
                        return '<input type="hidden" name="ids[]" value="' + id + '">';
                    }).join('');
                    form.submit();
                }
            });
        }

        document.addEventListener('change', function(e) {
            const target = e.target;
            if (!target) return;
            if (target.id === 'check-all-kelola') {
                document.querySelectorAll('.row-checkbox-kelola').forEach(function (cb) {
                    cb.checked = target.checked;
                    markMutasiSelection(cb);
                });
                updateBulkMutasiUi();
            } else if (target.classList && target.classList.contains('row-checkbox-kelola')) {
                markMutasiSelection(target);
                updateBulkMutasiUi();
            } else if (target.id === 'product-check-all') {
                document.querySelectorAll('.product-row-check').forEach(function (cb) {
                    cb.checked = target.checked;
                    markProductSelection(cb);
                });
                updateProductBulkSelectionUi();
            } else if (target.classList && target.classList.contains('product-row-check')) {
                markProductSelection(target);
                syncProductCheckboxes(target.value, target.checked);
                updateProductBulkSelectionUi();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('editMutasiModal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeEditMutasiModal();
                }
                const pModal = document.getElementById('product-modal');
                if (pModal && !pModal.classList.contains('hidden')) {
                    closeProductModal();
                }
            }
        });

        // Konfirmasi Hapus Baris Satuan
        function confirmDeletePencatatan(id) {
            uiConfirmDelete({
                title: 'Batalkan baris ini?',
                text: 'Stok akan dikembalikan via reversal. Jejak audit tetap tersimpan.',
                onConfirm: () => document.getElementById('form-del-' + id)?.submit()
            });
        }

        // Modal Kartu Stok
        let kartuCurrentUrl = null;

        function kartuToast(msg, ok = true) {
            const el = document.getElementById('kartuModalToast');
            if (!el) return;
            el.classList.remove('hidden');
            el.className = 'mx-4 mt-3 px-4 py-2.5 rounded-xl text-xs font-bold shrink-0 ' + (ok
                ? 'bg-blue-50 border border-blue-200 text-blue-700'
                : 'bg-red-50 border border-red-200 text-red-600');
            el.textContent = msg;
            clearTimeout(el._t);
            el._t = setTimeout(() => el.classList.add('hidden'), 5000);
        }

        function openKartuModal(embedUrl, nama, kode, fullUrl, productId) {
            const modal = document.getElementById('kartuModal');
            if (!modal) return;
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            if (productId) modal.dataset.productId = productId;
            document.getElementById('kartuModalNama').textContent = nama || 'Kartu Stock';
            document.getElementById('kartuModalKode').textContent = kode || '';
            const fullBtn = document.getElementById('kartuModalFull');
            if (fullBtn) fullBtn.href = fullUrl || '#';
            document.getElementById('kartuModalSaldo').textContent = '-';
            document.getElementById('kartuModalToast').classList.add('hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            window.activeKartuFilterArah = null;
            loadKartuEmbed(embedUrl);
        }

        function closeKartuModal() {
            const modal = document.getElementById('kartuModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            kartuCurrentUrl = null;
            if (window._kartuHasMutated) {
                window._kartuHasMutated = false;
                window.location.reload();
            }
        }

        function kartuLoading() {
            document.getElementById('kartuModalBody').innerHTML =
                '<div class="py-16 flex flex-col items-center justify-center gap-3 text-slate-400">' +
                '<div class="ks-spin"></div><p class="text-xs font-bold">Memuat kartu...</p></div>';
        }

        async function loadKartuEmbed(url) {
            kartuCurrentUrl = url;
            kartuLoading();
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const body = document.getElementById('kartuModalBody');
                body.innerHTML = await res.text();
                body.scrollTop = 0;
                const meta = body.querySelector('#kartuDetailMeta');
                if (meta) {
                    if (meta.dataset.nama) document.getElementById('kartuModalNama').textContent = meta.dataset.nama;
                    if (meta.dataset.kode) document.getElementById('kartuModalKode').textContent = meta.dataset.kode;
                    document.getElementById('kartuModalStock').textContent = meta.dataset.stock ?? '-';
                    document.getElementById('kartuModalUnit').textContent = meta.dataset.unit ?? '';
                    document.getElementById('kartuModalSaldo').textContent = meta.dataset.saldo ?? '-';
                    document.getElementById('kartuModalUpdated').textContent = meta.dataset.updated ?? '-';
                    const kmModal = document.getElementById('kartuModal');
                    if (kmModal && meta.dataset.productId) {
                        kmModal.dataset.productId = meta.dataset.productId;
                    }
                }
            } catch (e) {
                document.getElementById('kartuModalBody').innerHTML =
                    '<div class="py-16 text-center"><p class="text-sm font-bold text-slate-600">Gagal memuat kartu.</p>' +
                    '<button type="button" onclick="loadKartuEmbed(kartuCurrentUrl)" class="mt-3 h-[34px] px-4 rounded-lg bg-blue-600 text-white text-xs font-bold">Coba lagi</button></div>';
            }
        }

        document.addEventListener('click', function (e) {
            const card = e.target.closest('[data-kartu-modal]');
            if (card) {
                e.preventDefault();
                openKartuModal(card.dataset.embedUrl, card.dataset.nama, card.dataset.kode, card.dataset.fullUrl, card.dataset.productId);
                return;
            }
            if (e.target.closest('#kartuModal') && !e.target.closest('#kartuModal > div') && !e.target.closest('#kartuEditModal')) {
                closeKartuModal();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            const edit = document.getElementById('kartuEditModal');
            if (edit && !edit.classList.contains('hidden')) return;
            const km = document.getElementById('kartuModal');
            if (km && !km.classList.contains('hidden')) closeKartuModal();
            const dm = document.getElementById('docModal');
            if (dm && !dm.classList.contains('hidden')) closeDocModal();
        });

        document.getElementById('kartuModalBody').addEventListener('submit', async function (e) {
            const form = e.target.closest('form[data-remote]');
            if (!form) return;
            e.preventDefault();
            if (form.dataset.remote === 'filter') {
                const params = new URLSearchParams(new FormData(form));
                params.set('embed', '1');
                loadKartuEmbed(form.action + '?' + params.toString());
            }
        });

        // Modal Detail Dokumen
        function openDocModal(docId) {
            const modal = document.getElementById('docModal');
            const body = document.getElementById('docModalBody');
            if (!modal || !body) return;
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            body.innerHTML = '<div class="py-16 flex flex-col items-center justify-center gap-3 text-slate-400"><div class="ks-spin"></div><p class="text-xs font-bold">Memuat dokumen...</p></div>';
            fetch('{{ url('warehouse/stock/warehouse/dokumen') }}/' + docId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || data.success === false) throw new Error(data.message || 'Gagal memuat dokumen.');
                    renderDocModal(data.data);
                })
                .catch((err) => {
                    body.innerHTML = '<div class="py-16 text-center"><p class="text-sm font-bold text-slate-600">' + (err.message || 'Gagal memuat dokumen.') + '</p></div>';
                });
        }

        function closeDocModal() {
            const modal = document.getElementById('docModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function renderDocModal(d) {
            document.getElementById('docModalNo').textContent = (d.doc_no && d.doc_no !== '-') ? ('NO. ' + d.doc_no) : '';
            document.getElementById('docModalPihak').textContent = d.pihak || '-';
            document.getElementById('docModalMeta').textContent =
                (d.direction === 'in' ? 'Masuk' : 'Keluar') + ' · ' + d.mutation_date +
                ' · ' + (d.purpose || '-') + ' · ' + d.status + ' · oleh ' + d.creator;
            let html = '<div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">' +
                '<div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2"><div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PO / Referensi</div><div class="font-bold text-slate-700">' + (d.po_no || '-') + '</div></div>' +
                '<div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2"><div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Ekspedisi</div><div class="font-bold text-slate-700">' + (d.expedition || '-') + '</div></div>' +
                '<div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 col-span-2"><div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat</div><div class="font-bold text-slate-700">' + (d.ship_address || '-') + '</div></div>' +
                '</div>';
            if (d.can_edit) {
                html += '<div class="flex justify-end"><button type="button" onclick="closeDocModal(); openEditDocModal(' + d.id + ');" class="inline-flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 h-[30px] px-3 rounded-lg text-[11px] font-bold cursor-pointer"><i class="fas fa-pen text-[10px]"></i> Edit Dokumen</button></div>';
            }
            html += '<h4 class="text-xs font-black text-slate-800 uppercase tracking-wider mt-3 mb-1.5">Rincian Barang (' + d.lines.length + ')</h4>';
            html += '<div class="overflow-x-auto border border-slate-200 rounded-xl"><table class="w-full text-xs whitespace-nowrap"><thead class="bg-slate-50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-200"><tr>' +
                '<th class="px-4 py-3 text-left" style="width: 32%;">Barang</th>' +
                '<th class="px-4 py-3 text-center" style="width: 14%;">Qty</th>' +
                '<th class="px-4 py-3 text-center" style="width: 18%;">Batch</th>' +
                '<th class="px-4 py-3 text-center" style="width: 16%;">Expired</th>' +
                '<th class="px-4 py-3 text-center" style="width: 20%;">Keterangan</th>' +
                '</tr></thead><tbody class="divide-y divide-slate-100">';
            d.lines.forEach((ln) => {
                html += '<tr class="hover:bg-slate-50/80 transition-colors">' +
                    '<td class="px-4 py-3 text-left"><div class="font-bold text-slate-800">' + ln.barang + '</div><div class="font-mono text-[10px] text-slate-400">' + ln.kode + '</div></td>' +
                    '<td class="px-4 py-3 text-center font-black text-slate-800">' + ln.qty + ' ' + ln.unit + '</td>' +
                    '<td class="px-4 py-3 text-center font-mono text-slate-700">' + ((ln.batch_no && String(ln.batch_no).trim() && String(ln.batch_no).trim() !== '-') ? String(ln.batch_no).trim() : '-') + '</td>' +
                    '<td class="px-4 py-3 text-center text-slate-700">' + (ln.expiry_date || '-') + '</td>' +
                    '<td class="px-4 py-3 text-center text-slate-600 whitespace-normal">' + (ln.keterangan || '-') + '</td>' +
                    '</tr>';
            });
            html += '</tbody></table></div>';
            document.getElementById('docModalBody').innerHTML = html;
        }

        document.getElementById('docModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeDocModal();
        });

        if (document.querySelector('.product-row-check')) {
            updateProductBulkSelectionUi();
        }
    </script>
    @endpush
@push('scripts')
{{-- Pembersihan Draft Pasca Simpan --}}
@if(session('success'))
<script>
    try {
        localStorage.removeItem('pencatatan_draft_v3_masuk');
        localStorage.removeItem('pencatatan_draft_v3_keluar');
    } catch (e) {}
</script>
@endif
@endpush
</x-layout-users>

