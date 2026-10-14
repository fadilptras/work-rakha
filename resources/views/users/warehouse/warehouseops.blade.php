<x-layout-users title="{{ $title ?? 'Warehouse Ops' }}" :fluid="true">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }
        /* == Background ala sales command center == */
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
            border-radius: 1rem; padding: 0.875rem 1rem; color: white;
            box-shadow: 0 10px 25px -5px rgba(37,99,235,0.35);
            position: relative; overflow: hidden;
        }
        .page-header-mobile::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 60%);
            transform: rotate(30deg); pointer-events: none;
        }
        .manage-table-scroll { overflow: auto; max-height: 60vh; min-height: 240px; }
        .manage-table-scroll table { border-collapse: separate; border-spacing: 0; }
        .manage-table-scroll thead th {
            position: sticky; top: 0; z-index: 20;
            background-clip: padding-box;
            box-shadow: 0 1px 0 #e2e8f0, 0 6px 10px -6px rgba(15,23,42,.18);
        }
        .manage-table-scroll thead th:not([class*="bg-"]) { background-color: #f8fafc; }
        /* Tooltip info kolom ala halaman forecast */
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
        @media (max-width: 767px) { .manage-table-scroll { max-height: 55vh; } }
        .ks-spin { width: 28px; height: 28px; border-radius: 9999px; border: 3px solid #e2e8f0; border-top-color: #2563eb; animation: ks-spin 0.8s linear infinite; }
        @keyframes ks-spin { to { transform: rotate(360deg); } }
        /* Textarea alamat: 1 baris sejajar input, teks & placeholder rata kiri */
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
        /* == Add to SPB (Monitoring Product): CSS mandiri agar tak tergantung build Tailwind == */
        .ui-btn--spb { background: #65a30d !important; border-color: #65a30d !important; color: #fff !important; }
        .ui-btn--spb:hover { background: #4d7c0f !important; border-color: #4d7c0f !important; color: #fff !important; }
        .ui-btn--spb.is-in-draft { background: #047857 !important; border-color: #047857 !important; color: #fff !important; }
        .ui-btn--spb:active { transform: scale(0.88); }
        .spb-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 9999px; border: 1px solid; line-height: 1.5; white-space: nowrap; }
        .spb-badge--draft { background: #f1f5f9; border-color: #94a3b8; color: #475569; }
        .spb-badge--aktif { background: #fffbeb; border-color: #f59e0b; color: #b45309; }
        .spb-badge--selesai { background: #ecfdf5; border-color: #34d399; color: #047857; }
        .spb-badge--tolak { background: #fff1f2; border-color: #fb7185; color: #e11d48; }
        #spb-floatbar { background: rgba(15, 23, 42, 0.96) !important; }
        #spb-floatbar { position: fixed !important; bottom: 24px !important; right: 24px !important; left: auto !important; transform: none !important; z-index: 9000 !important; width: 440px !important; max-width: calc(100vw - 48px) !important; }
        #spb-floatbar .spb-fb-icon { background: rgba(132,204,22,.18) !important; border: 1px solid rgba(132,204,22,.45) !important; color: #bef264 !important; }
        #spb-floatbar .spb-fb-go { background: #84cc16 !important; color: #0f172a !important; }
        #spb-floatbar .spb-fb-go:hover { background: #a3e635 !important; }
        #spb-floatbar .spb-fb-rm { color: #94a3b8 !important; }
        #spb-floatbar .spb-fb-rm:hover { color: #fda4af !important; }
        #spb-floatbar .spb-fb-name { color: #f8fafc !important; }
        #spb-floatbar .spb-fb-title { color: #ffffff !important; }
        #spb-floatbar .spb-fb-sub { color: #cbd5e1 !important; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 relative overflow-x-clip text-slate-800 pb-16">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start" x-data="pencatatanData()">

            <div class="w-full flex justify-start mb-3 md:mb-4">
                <x-ui.back-button href="{{ route('warehouse.dashboard') }}" label="Back to Warehouse Dashboard" />
            </div>

            {{-- Header + nav 4 tab --}}
            <div class="hidden md:block">
                <x-ui.page-header title="Warehouse Ops" subtitle="Product data, mutasi, input keluar & masuk dalam satu halaman." icon="fa-warehouse" :sticky="false">
                    <x-slot:controls>
                        <div class="flex space-x-1 bg-white/10 p-1 rounded-full border border-white/20 backdrop-blur-md overflow-x-auto shrink-0 w-full xl:w-auto relative z-10">
                            <a href="{{ route('warehouse.index', ['tab' => 'produk']) }}" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab === 'produk' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-boxes-stacked mr-2"></i> Monitoring Product
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
            <div class="md:hidden space-y-3">
                <div class="page-header-mobile flex items-center justify-between gap-3">
                    <div class="relative z-10 min-w-0">
                        <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Warehouse Ops</h2>
                        <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">Produk, mutasi, keluar & masuk.</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 relative z-10"><i class="fas fa-warehouse"></i></div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-1.5 grid grid-cols-4 gap-1">
                    <a href="{{ route('warehouse.index', ['tab' => 'produk']) }}" class="flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold {{ $tab === 'produk' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500' }}">
                        <i class="fas fa-boxes-stacked text-sm"></i><span class="text-[10px] uppercase">Monitor</span>
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


            @if($tab === 'produk')
            {{-- TAB 1: Monitoring Product --}}
            <div class="space-y-4 flex flex-col w-full">
                <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full !rounded-3xl !bg-white border border-slate-200 shadow-lg border-t-4 border-t-blue-500">
                    <form action="{{ route('warehouse.index') }}" method="GET" class="p-6 pb-5 border-b border-slate-100 bg-white">
                        <input type="hidden" name="tab" value="produk">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end">
                            <div class="lg:col-span-4 md:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari kode / nama barang</label>
                                <x-ui.search-input name="q" placeholder="cth. kasa / KSA-001" value="{{ request('q') }}" autocomplete="off" class="w-full" :debounce="500" />
                            </div>
                            <div class="lg:col-span-3 md:col-span-1">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kebutuhan</label>
                                <x-ui.filter-select name="butuh" value="{{ $kebutuhan ?? 'semua' }}" onchange="this.form.submit()" class="w-full" :options="['semua' => 'Semua SKU', 'order' => 'Perlu Order']" />
                            </div>
                            <div class="lg:col-span-3 md:col-span-1 flex items-end">
                                <x-ui.info class="w-full" icon="fa-boxes-stacked" label="Tampil:" :value="number_format($products->count(), 0, ',', '.')" />
                            </div>
                            <div class="lg:col-span-2 md:col-span-1 flex items-end">
                                <a href="{{ route('warehouse.index', ['tab' => 'produk']) }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center border border-slate-200 shadow-sm leading-none"><i class="fas fa-undo mr-1.5 text-[11px]"></i> Reset</a>
                            </div>
                        </div>
                    </form>
                    <div class="flex-1 bg-white manage-table-scroll">
                        <table class="w-full text-sm text-left text-slate-600 bg-white">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                                <tr>
                                    <th class="px-4 py-4 w-12 text-center">No</th>
                                    <th class="px-4 py-4">Kode</th>
                                    <th class="px-4 py-4">Nama Barang</th>
                                    <th class="px-4 py-4">Pack / Satuan</th>
                                    <th class="px-4 py-4 text-center bg-blue-100 border-l border-blue-200 text-blue-800 col-tooltip" style="width:200px;" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open"><span class="inline-flex items-center justify-center gap-1 cursor-help">Stock</span>
                                        <div x-show="open" x-cloak class="col-tooltip-popup text-center"><span class="font-bold text-blue-200">Stock</span> - stok realtime kini.<br>Angka resmi, sama dengan halaman Data Product.</div>
                                    </th>
                                    <th class="px-3 py-4 text-center border-l border-emerald-200 text-emerald-700 bg-emerald-100 leading-tight col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open"><span class="inline-flex items-center justify-center gap-1 cursor-help">Forecast</span><br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">{{ $forecastBulan ?? '' }}</span>
                                        <div x-show="open" x-cloak class="col-tooltip-popup text-center"><span class="font-bold text-emerald-200">Forecast</span> - estimasi kebutuhan {{ $forecastBulan ?? 'bulan ini' }}.<br><span class="text-yellow-300">Forecast = Rata-rata + {{ rtrim(rtrim(number_format($forecastPct ?? 20, 2, '.', ''), '0'), '.') }}%</span></div>
                                    </th>
                                    <th class="px-3 py-4 text-center border-l border-indigo-200 text-indigo-800 bg-indigo-100 leading-tight col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open"><span class="inline-flex items-center justify-center gap-1 cursor-help">Order</span><br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">(Production)</span>
                                        <div x-show="open" x-cloak class="col-tooltip-popup text-center"><span class="font-bold text-indigo-200">Order</span> - saran tambah stok agar tetap di atas buffer.<br><span class="text-yellow-300">0 = aman, tidak perlu order.</span></div>
                                    </th>
                                    <th class="px-3 py-4 text-center border-l border-rose-200 text-rose-700 bg-rose-100 leading-tight col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open"><span class="inline-flex items-center justify-center gap-1 cursor-help">DOI</span><br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">days</span>
                                        <div x-show="open" x-cloak class="col-tooltip-popup col-tooltip-popup--right text-center"><span class="font-bold text-rose-200">DOI</span> - daya tahan stok dalam hari.<br><span class="text-yellow-300">DOI = (Stok &divide; Rata-rata) &times; 30</span></div>
                                    </th>
                                    <th class="px-4 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($products as $index => $p)
                                    @php
                                        $stok = (float) $p->stock;
                                        $isHabis = $stok <= 0;
                                        $nama = $p->product_name_clean ?: $p->product_name;
                                        $pcsPerUnit = (int) ($p->pcs_per_unit ?? 0);
                                        $isSingle = $pcsPerUnit <= 1;
                                        $kemasan = $p->unit ?? 'Pcs';
                                        $fillUnit = $p->fill_unit ?? 'Pcs';
                                        // Satuan tampil ikut Data Product: isi (fill) bila multi-pack.
                                        $satuan = $isSingle ? ($p->unit ?: $fillUnit) : $fillUnit;
                                        $fc = $p->forecast ?? null;
                                        $namaAsli = $p->product_name;
                                        $orderQty = (int) ($fc['order_qty'] ?? 0);
                                        $forecastQty = (int) ($fc['forecast_qty'] ?? 0);
                                    @endphp
                                    <tr class="hover:bg-blue-50/60 transition-colors {{ $isHabis ? 'bg-red-50/40' : '' }}">
                                        <td class="px-4 py-3 text-center text-slate-400 font-medium">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 font-mono text-xs">{{ $p->product_code ?? '-' }}</td>
                                        <td class="px-4 py-3 font-bold text-slate-900 text-xs">{{ $nama }}
                                            <div class="spb-marks flex flex-wrap gap-1 mt-1" data-marks-for="{{ $p->id }}"></div>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-slate-600">{{ $p->unit ?? '-' }}@if($p->pcs_per_unit > 1) <span class="text-slate-400">· isi {{ $p->pcs_per_unit }} {{ $p->fill_unit ?? 'Pcs' }}</span>@endif</td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap bg-blue-50 border-l border-blue-200" style="width:200px;">
                                            <span class="font-black text-sm {{ $isHabis ? 'text-rose-600' : '' }}">{{ number_format($stok, 0, ',', '.') }} <span class="text-xs font-bold text-slate-500">{{ $satuan }}</span></span>
                                            @if(!$isSingle)
                                            <span><span class="text-xs font-bold text-slate-400"> = </span><span class="font-black text-sm text-emerald-600">{{ number_format(floor($stok / $pcsPerUnit), 0, ',', '.') }} {{ $kemasan }}</span>@if(fmod($stok, $pcsPerUnit) > 0)<span class="text-xs font-bold text-slate-500">+{{ number_format(fmod($stok, $pcsPerUnit), 0, ',', '.') }} {{ $fillUnit }}</span>@endif</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-center text-xs font-black text-emerald-600 bg-emerald-50 border-l border-emerald-200">{{ $fc ? number_format($fc['forecast_qty'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-3 py-2.5 text-center text-xs font-black text-indigo-700 bg-indigo-50 border-l border-indigo-200">{{ $fc ? number_format($fc['order_qty'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-3 py-2.5 text-center text-xs font-bold text-rose-600 bg-rose-50 border-l border-rose-200">{{ $fc ? number_format($fc['doi_qty'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-2">
                                                <x-ui.icon-button action="view" icon="fa-id-card-clip" title="Buka kartu stock" data-kartu-modal data-embed-url="{{ route('warehouse.stockcard.show', $p) . '?embed=1' }}" data-nama="{{ $nama }}" data-kode="{{ $p->product_code }}" data-full-url="{{ route('warehouse.stockcard.show', $p) }}" />
                                                <a href="{{ route('warehouse.stockcard.print', $p) }}" target="_blank" rel="noopener" class="ui-btn ui-btn--view" title="Cetak kartu stock (print browser)"><i class="fas fa-print text-[11px]"></i></a>
                                                <button type="button" class="btn-spb ui-btn ui-btn--spb" title="Tambah ke draft SPB"
                                                    data-spb-pid="{{ $p->id }}"
                                                    data-spb-nama="{{ $namaAsli }}"
                                                    data-spb-kode="{{ $p->product_code }}"
                                                    data-spb-clean="{{ $nama }}"
                                                    data-spb-satuan="{{ $kemasan }}"
                                                    data-spb-order="{{ $orderQty }}"
                                                    data-spb-forecast="{{ $forecastQty }}"><i class="fas fa-file-signature text-[11px] pointer-events-none"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="p-0"><x-ui.empty icon="fa-box-open" title="Tidak ada barang" message="Coba kata kunci lain." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3 border-t border-slate-200 bg-white flex items-center justify-end gap-3">
                        <span class="text-[11px] text-slate-400 font-semibold whitespace-nowrap">Total SKU: <b class="text-slate-700">{{ number_format($products->count(), 0, ',', '.') }}</b></span>
                    </div>
                </x-ui.glass-card>

                {{-- Floating bar draft SPB + rincian + toast (tetap di halaman, lompat ke form bila siap) --}}
                <div id="spb-floatbar" class="hidden fixed bottom-6 right-6 z-[9000] w-[440px] max-w-[92vw] bg-slate-900/95 backdrop-blur border border-white/10 shadow-2xl rounded-2xl px-4 py-3">
                    <div id="spb-floatlist" class="hidden mb-2 max-h-44 overflow-y-auto divide-y divide-white/10 border-b border-white/10 pb-2"></div>
                    <div class="flex items-center gap-3">
                        <div class="spb-fb-icon w-9 h-9 shrink-0 rounded-xl flex items-center justify-center"><i class="fas fa-file-signature text-sm"></i></div>
                        <button type="button" id="spb-float-toggle" class="flex-1 min-w-0 text-left" title="Klik untuk lihat rincian draft">
                            <p class="spb-fb-title text-[13px] font-black leading-tight">Draft SPB: <span id="spb-float-count">0</span> item <i id="spb-float-chev" class="fas fa-chevron-up text-[10px] text-slate-400 ml-1"></i></p>
                            <p id="spb-float-sub" class="spb-fb-sub text-[11px] font-medium leading-snug truncate">Siap diajukan ke form SPB.</p>
                        </button>
                        <a href="{{ route('pengajuan_barang.index', ['from' => 'ops']) }}" class="spb-fb-go shrink-0 inline-flex items-center gap-1.5 h-[34px] px-4 rounded-xl text-xs font-black transition-colors">Buka Form <i class="fas fa-arrow-right text-[10px]"></i></a>
                    </div>
                </div>
            </div>
            @push('scripts')
            <script>
                // ---- Add to SPB dari Monitoring Product (draft lokal + badge status) ----
                (function initOpsSpb() {
                    function boot() {
                    try {
                    const SPB_DRAFT_KEY = 'pengajuan_barang_draft_{{ Auth::id() }}';
                    const SPB_STATUS_URL = '{{ route("pengajuan_barang.statusBatch") }}';
                    const floatbar = document.getElementById('spb-floatbar');
                    const floatCount = document.getElementById('spb-float-count');
                    const floatSub = document.getElementById('spb-float-sub');
                    const spbBtns = Array.from(document.querySelectorAll('.btn-spb[data-spb-pid]'));
                    console.log('[ops-spb] init, tombol ditemukan:', spbBtns.length);
                    if (!spbBtns.length) return;
                    let serverMarks = {};

                    function esc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
                    function fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(Number(n) || 0)); }
                    function readDraft() {
                        try {
                            const raw = localStorage.getItem(SPB_DRAFT_KEY);
                            if (!raw) return null;
                            const d = JSON.parse(raw);
                            if (!d || !Array.isArray(d.items)) return null;
                            return d;
                        } catch (e) { return null; }
                    }
                    function draftNameSet() {
                        const d = readDraft();
                        const set = new Set();
                        (d?.items || []).forEach(it => {
                            const n = (it.deskripsi || '').trim().toLowerCase();
                            if (n) set.add(n);
                        });
                        return { draft: d, set };
                    }

                    function renderMarks() {
                        const { draft, set } = draftNameSet();
                        const items = draft?.items || [];
                        const n = items.length;
                        if (floatbar) floatbar.classList.toggle('hidden', n === 0);
                        if (floatCount) floatCount.textContent = fmt(n);
                        if (floatSub) {
                            const tot = items.reduce((a, it) => a + (parseFloat(it.jumlah) || 0), 0);
                            floatSub.textContent = n
                                ? ('Total ' + fmt(tot) + ' unit • klik untuk rincian • tersimpan otomatis.')
                                : 'Siap diajukan ke form SPB.';
                        }
                        renderFloatList(items);
                        spbBtns.forEach(btn => {
                            const nama = (btn.dataset.spbNama || '').trim().toLowerCase();
                            const inDraft = nama && set.has(nama);
                            btn.classList.toggle('is-in-draft', !!inDraft);
                            const ico = btn.querySelector('i');
                            if (ico) { ico.classList.toggle('fa-check', !!inDraft); ico.classList.toggle('fa-file-signature', !inDraft); }
                            btn.title = inDraft ? 'Sudah di draft SPB — klik lagi untuk batalkan' : 'Tambah ke draft SPB';
                            const box = document.querySelector('[data-marks-for="' + btn.dataset.spbPid + '"]');
                            if (!box) return;
                            const info = serverMarks[btn.dataset.spbPid] || {};
                            const tot = info.total_bulan_ini || 0;
                            const akt = info.aktif_bulan_ini || 0;
                            const fin = info.selesai_bulan_ini || 0;
                            let html = '';
                            if (inDraft) html += '<span class="spb-badge spb-badge--draft">● Draft</span>';
                            if (akt > 0) html += '<span class="spb-badge spb-badge--aktif">⏳ ' + akt + '× proses</span>';
                            if (fin > 0) html += '<span class="spb-badge spb-badge--selesai">✔ ' + fin + '× selesai</span>';
                            if (tot > 0 && akt === 0 && fin === 0) html += '<span class="spb-badge spb-badge--draft">• ' + tot + '× bulan ini</span>';
                            if (info.ditolak_90h) html += '<span class="spb-badge spb-badge--tolak">✖ riwayat</span>';
                            box.innerHTML = html;
                            // Keterangan lengkap: berapa kali + status + pengajuan terakhir.
                            box.title = tot > 0
                                ? ('Diajukan ' + tot + '× bulan ini (proses ' + akt + ', selesai ' + fin + '). Terakhir: "' + (info.last?.judul || '-') + '" • ' + (info.last?.status || '-') + ' • ' + (info.last?.tanggal || '-') + (info.last?.oleh ? ' • ' + info.last.oleh : ''))
                                : (inDraft ? 'Ada di draft SPB, belum diajukan.' : 'Belum pernah diajukan bulan ini.');
                        });
                    }

                    // Rincian isi draft di floating bar: nama + qty + tombol hapus per baris.
                    function renderFloatList(items) {
                        const list = document.getElementById('spb-floatlist');
                        if (!list) return;
                        if (!items.length) { list.innerHTML = ''; list.classList.add('hidden'); return; }
                        list.innerHTML = items.map((it, i) =>
                            '<div class="flex items-center gap-2 py-1.5">' +
                            '<span class="spb-fb-name flex-1 min-w-0 text-[11px] font-bold truncate">' + esc(it.deskripsi || '-') + '</span>' +
                            '<span class="shrink-0 text-[11px] font-black" style="color:#bef264;">' + esc(fmt(it.jumlah)) + ' ' + esc(it.satuan || '') + '</span>' +
                            '<button type="button" data-spb-rm="' + i + '" class="spb-fb-rm shrink-0 w-6 h-6 rounded-lg flex items-center justify-center transition-colors" title="Hapus dari draft"><i class="fas fa-times text-[10px] pointer-events-none"></i></button>' +
                            '</div>'
                        ).join('');
                    }

                    async function loadServerMarks() {
                        try {
                            const qs = spbBtns.map(b => 'ids[]=' + encodeURIComponent(b.dataset.spbPid)).join('&');
                            const res = await fetch(SPB_STATUS_URL + '?' + qs, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const json = await res.json();
                            serverMarks = json.map || {};
                        } catch (e) { serverMarks = {}; }
                        renderMarks();
                    }

                    // Delegasi klik di level dokumen: tetap kena walau tombol dirender belakangan.
                    function handleSpbClick(btn) {
                            const namaAsli = (btn.dataset.spbNama || '').trim();
                            if (!namaAsli) return;
                            const order = parseFloat(btn.dataset.spbOrder) || 0;
                            const fc = parseFloat(btn.dataset.spbForecast) || 0;
                            let qty = order > 0 ? order : fc;
                            let aman = false;
                            if (!(qty > 0)) { qty = 1; aman = true; }
                            const kode = (btn.dataset.spbKode || '').trim();
                            const clean = (btn.dataset.spbClean || '').trim();
                            const satuan = (btn.dataset.spbSatuan || 'Pcs').trim() || 'Pcs';
                            let ket = 'Dari Warehouse Ops • kode ' + (kode || '-');
                            if (clean && clean.toLowerCase() !== namaAsli.toLowerCase()) ket += ' / ' + clean;
                            if (aman) ket += ' • Stok aman (order 0), pengajuan manual';

                            const cur = readDraft() || { v: 1, judul_pengajuan: '', catatan_pemohon: '', items: [], saved_at: new Date().toISOString() };
                            const key = namaAsli.toLowerCase();
                            const foundIdx = (cur.items || []).findIndex(it => (it.deskripsi || '').trim().toLowerCase() === key);
                            if (foundIdx >= 0) {
                                // Toggle: sudah ada → batalkan (hapus dari draft, anti double).
                                cur.items.splice(foundIdx, 1);
                            } else {
                                cur.items.push({ deskripsi: namaAsli, supplier: '', jumlah: String(qty), satuan: satuan, keterangan: ket });
                            }
                            cur.v = 1;
                            cur.saved_at = new Date().toISOString();
                            try { localStorage.setItem(SPB_DRAFT_KEY, JSON.stringify(cur)); } catch (e) { console.error('[ops-spb] gagal simpan draft:', e); }
                            renderMarks();
                    }
                    document.addEventListener('click', function (e) {
                        if (!e.target || !e.target.closest) return;
                        const btn = e.target.closest('.btn-spb[data-spb-pid]');
                        if (btn) { e.preventDefault(); handleSpbClick(btn); return; }
                        // Hapus 1 baris dari rincian draft di floating bar.
                        const rm = e.target.closest('[data-spb-rm]');
                        if (rm) {
                            e.preventDefault();
                            const cur = readDraft();
                            const idx = parseInt(rm.getAttribute('data-spb-rm'), 10) || 0;
                            if (cur && cur.items && cur.items[idx]) {
                                cur.items.splice(idx, 1);
                                cur.saved_at = new Date().toISOString();
                                try { localStorage.setItem(SPB_DRAFT_KEY, JSON.stringify(cur)); } catch (err) {}
                                renderMarks();
                            }
                            return;
                        }
                        // Buka/tutup rincian draft.
                        if (e.target.closest('#spb-float-toggle')) {
                            const list = document.getElementById('spb-floatlist');
                            const chev = document.getElementById('spb-float-chev');
                            if (list) list.classList.toggle('hidden');
                            if (chev) { chev.classList.toggle('fa-chevron-up'); chev.classList.toggle('fa-chevron-down'); }
                        }
                    });

                    window.addEventListener('storage', (e) => { if (e.key === SPB_DRAFT_KEY) renderMarks(); });
                    renderMarks();
                    loadServerMarks();
                    } catch (err) { console.error('[ops-spb] gagal init:', err); }
                    }
                    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
                    else boot();
                })();
            </script>
            @endpush
            @elseif($tab === 'mutasi')
            {{-- TAB 2: Data Mutation (tabel + rekap SJ + export) --}}
            <div class="space-y-4 flex flex-col w-full">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="bg-white border border-blue-200 rounded-2xl px-4 py-3 shadow-sm">
                        <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-blue-500">Masuk (qty)</div>
                        <div class="text-2xl font-black text-blue-600 leading-tight">+{{ number_format($qtyMasuk, 0, ',', '.') }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">{{ $trxMasuk }} transaksi</div>
                    </div>
                    <div class="bg-white border border-red-200 rounded-2xl px-4 py-3 shadow-sm">
                        <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-red-500">Keluar (qty)</div>
                        <div class="text-2xl font-black text-red-600 leading-tight">-{{ number_format($qtyKeluar, 0, ',', '.') }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">{{ $trxKeluar }} transaksi</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl px-4 py-3 shadow-sm">
                        <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400">Total Transaksi</div>
                        <div class="text-2xl font-black leading-tight text-slate-800">{{ number_format(($trxMasuk ?? 0) + ($trxKeluar ?? 0), 0, ',', '.') }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">{{ $trxMasuk ?? 0 }} masuk · {{ $trxKeluar ?? 0 }} keluar</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl px-4 py-3 shadow-sm">
                        <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400">SKU Bergerak</div>
                        <div class="text-2xl font-black text-slate-800 leading-tight">{{ number_format($produkAktif ?? 0, 0, ',', '.') }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">SKU pada filter ini</div>
                    </div>
                </div>

                <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg" style="background:#ffffff;">
                    <form action="{{ route('warehouse.index') }}" method="GET" id="filterFormMutasi" class="p-6 pb-5 border-b border-slate-100 bg-white">
                        <input type="hidden" name="tab" value="mutasi">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end">
                            <div class="lg:col-span-4">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari barang / batch / keterangan</label>
                                <x-ui.search-input name="search" placeholder="cth. kasa / B-2026 / supplier X" value="{{ request('search') }}" autocomplete="off" class="w-full" :debounce="500" />
                            </div>
                            <div class="lg:col-span-3">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Barang</label>
                                <x-ui.filter-select name="product_id" onchange="document.getElementById('filterFormMutasi').submit()" value="{{ request('product_id') }}" placeholder="Semua barang" class="w-full">
                                    @foreach($productOptions as $p)
                                        <option value="{{ $p->id }}" {{ (string) request('product_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->product_name_clean ?: $p->product_name }} (stok {{ $p->stock }})</option>
                                    @endforeach
                                </x-ui.filter-select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Arah</label>
                                <x-ui.filter-select name="kelola_arah" onchange="document.getElementById('filterFormMutasi').submit()" value="{{ request('kelola_arah') }}" placeholder="Masuk + Keluar" class="w-full">
                                    <option value="masuk" {{ request('kelola_arah') === 'masuk' ? 'selected' : '' }}>Masuk saja</option>
                                    <option value="keluar" {{ request('kelola_arah') === 'keluar' ? 'selected' : '' }}>Keluar saja</option>
                                </x-ui.filter-select>
                            </div>
                            <div class="lg:col-span-3">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal</label>
                                <x-ui.filter-date name="tanggal" value="{{ request('tanggal') }}" onchange="document.getElementById('filterFormMutasi').submit()" class="w-full" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end mt-3">
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                                <x-ui.filter-select name="bulan" onchange="document.getElementById('filterFormMutasi').submit()" value="{{ request('bulan') }}" placeholder="Semua" class="w-full">
                                    @for($b = 1; $b <= 12; $b++)
                                        <option value="{{ $b }}" {{ (string) request('bulan') === (string) $b ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $b, 1)->format('F') }}</option>
                                    @endfor
                                </x-ui.filter-select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
                                <x-ui.filter-select name="tahun" onchange="document.getElementById('filterFormMutasi').submit()" value="{{ request('tahun') }}" placeholder="Semua" class="w-full">
                                    @foreach($tahunAda as $t)
                                        <option value="{{ $t }}" {{ (string) request('tahun') === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </x-ui.filter-select>
                            </div>
                            <div class="lg:col-span-4 flex items-end">
                                <x-ui.info class="w-full" icon="fa-database" label="Total Baris:" :value="number_format($kelolaMoves->total(), 0, ',', '.')" />
                            </div>
                            <div class="lg:col-span-4 flex gap-2 items-end">
                                <a href="{{ route('warehouse.export-mutasi', request()->except(['kelolaPage', 'sjPage', 'page'])) }}" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center shadow-sm leading-none" title="Export PDF rekap mutasi (filter aktif)">
                                    <i class="fas fa-file-pdf mr-1.5 text-[11px]"></i> PDF Mutasi
                                </a>
                                <a href="{{ route('warehouse.export-sj', request()->except(['kelolaPage', 'sjPage', 'page'])) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center shadow-sm leading-none" title="Export PDF rekap SJ (filter aktif)">
                                    <i class="fas fa-file-pdf mr-1.5 text-[11px]"></i> PDF SJ
                                </a>
                                <a href="{{ route('warehouse.index', ['tab' => 'mutasi']) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center border border-slate-200 shadow-sm leading-none" title="Reset Filters">
                                    <i class="fas fa-undo text-[11px]"></i>
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="flex-1 bg-white manage-table-scroll">
                        <table class="w-full text-sm text-left text-slate-600 bg-white">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                                <tr>
                                    <th class="px-4 py-4 w-10 text-center">
                                        <input type="checkbox" id="check-all-kelola" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 cursor-pointer">
                                    </th>
                                    <th class="px-4 py-4 w-12 text-center">No</th>
                                    <th class="px-4 py-4">Tanggal</th>
                                    <th class="px-4 py-4">Dokumen</th>
                                    <th class="px-4 py-4">Customer</th>
                                    <th class="px-4 py-4">Barang</th>
                                    <th class="px-4 py-4 text-center">Arah</th>
                                    <th class="px-4 py-4 text-right">Qty</th>
                                    <th class="px-4 py-4">Batch No.</th>
                                    <th class="px-4 py-4">Expired</th>
                                    <th class="px-4 py-4">Keterangan</th>
                                    <th class="px-4 py-4">Petugas</th>
                                    <th class="px-4 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($kelolaMoves as $index => $m)
                                    @php $masuk = $isMasuk($m); @endphp
                                    <tr class="hover:bg-blue-50/60 transition-colors">
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" value="{{ $m->id }}" class="row-checkbox-kelola rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 cursor-pointer">
                                        </td>
                                        <td class="px-4 py-3 text-center text-slate-400 font-medium">{{ $kelolaMoves->firstItem() + $index }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700 text-xs">{{ $m->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-mono font-bold text-slate-800 text-xs">{{ $m->doc_no ?? $m->d_doc ?? '-' }}</div>
                                            @if($m->d_source === 'faktur')<div class="mt-1"><span class="inline-flex items-center px-2 py-0.5 rounded-md bg-sky-50 border border-sky-200 text-sky-700 text-[10px] font-bold">Faktur</span></div>@endif
                                            @if($m->po_no ?? $m->d_po)<div class="font-mono text-[10px] text-slate-400">PO: {{ $m->po_no ?? $m->d_po }}</div>@endif
                                            @if($m->expedition ?? $m->d_exped)<div class="text-[10px] text-slate-400"><i class="fas fa-truck text-[9px] mr-1"></i>{{ $m->expedition ?? $m->d_exped }}</div>@endif
                                        </td>
                                        @php $pihak = $m->partner ?: ($m->cu_name ?? $m->su_name ?? 'Pemakaian Internal'); @endphp
                                        <td class="px-4 py-3 text-xs text-slate-600 max-w-[160px] truncate" title="{{ $pihak }}{{ ($m->ship_address ?? $m->d_ship) ? ' — ' . ($m->ship_address ?? $m->d_ship) : '' }}">{{ $pihak }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-900 text-xs">{{ $m->product->product_name_clean ?: ($m->product->product_name ?? 'Produk Terhapus') }}</div>
                                            <div class="font-mono text-[10px] text-slate-400">{{ $m->product->product_code ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($masuk)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-[11px] font-black"><i class="fas fa-arrow-down text-[10px]"></i> Masuk</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 border border-red-200 text-red-600 text-[11px] font-black"><i class="fas fa-arrow-up text-[10px]"></i> Keluar</span>
                                            @endif
                                            @if($m->purpose)
                                                <div class="mt-1"><span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-bold">{{ $m->purpose }}</span></div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-black {{ $masuk ? 'text-blue-600' : 'text-red-600' }}">{{ ($masuk ? '+' : '-') . number_format($m->qty, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 font-mono text-xs">{{ $m->batch_no ?? '-' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs {{ $m->expiry_date && $m->expiry_date->isPast() ? 'text-red-600 font-bold' : 'text-slate-600' }}">{{ $m->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs max-w-[240px] truncate" title="{{ $m->reason }}">{{ $m->reason ?: '-' }}</td>
                                        <td class="px-4 py-3 text-xs text-slate-500">{{ $m->user->name ?? 'System' }}</td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            @if($canManage)
                                            <div class="flex items-center justify-center gap-2">
                                                @if($m->d_id)
                                                <button type="button" data-doc="{{ $m->d_id }}" onclick="openDocModal(this.dataset.doc)" class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200" title="Detail dokumen"><i class="fas fa-eye text-xs"></i></button>
                                                @endif
                                                <button type="button" class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200" title="Edit"
                                                    data-edit="{{ json_encode(['id' => $m->id, 'tanggal' => $m->created_at?->format('Y-m-d'), 'qty' => $m->qty, 'batch_no' => $m->batch_no, 'expiry_date' => $m->expiry_date?->format('Y-m-d'), 'doc_no' => $m->doc_no ?? $m->d_doc, 'po_no' => $m->po_no ?? $m->d_po, 'ship_address' => $m->ship_address ?? $m->d_ship, 'expedition' => $m->expedition ?? $m->d_exped, 'keterangan' => $m->reason, 'arah' => $masuk ? 'Masuk' : 'Keluar', 'pihak' => $m->partner ?: ($m->cu_name ?? $m->su_name ?? 'Pemakaian Internal'), 'barang' => ($m->product->product_name_clean ?: ($m->product->product_name ?? ''))]) }}"
                                                    @click="openEditModal(JSON.parse($el.dataset.edit))"><i class="fas fa-pen text-xs"></i></button>
                                                <form action="{{ route('warehouse.mutation.destroy', $m->id) }}" method="POST" id="form-del-{{ $m->id }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="button" onclick="confirmDeletePencatatan('{{ $m->id }}')" class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg bg-red-50 hover:bg-red-100 text-red-600 border border-red-200" title="Hapus (reversal)"><i class="fas fa-trash text-xs"></i></button>
                                                </form>
                                            </div>
                                            @else
                                                <span class="text-[11px] text-slate-300">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="13" class="p-0"><x-ui.empty icon="fa-inbox" title="Belum ada pencatatan" message="Semua barang yang diinput dari tab Masuk / Keluar akan muncul di sini." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-3">
                        <button type="button" onclick="confirmBulkKelola()" id="btn-bulk-kelola" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition shadow-sm hidden">
                            <i class="fas fa-trash-alt mr-1"></i> Batalkan (<span id="kelola-selected-count">0</span>)
                        </button>
                        @if($kelolaMoves->hasPages())
                        <div class="w-full sm:w-auto">{{ $kelolaMoves->links() }}</div>
                        @endif
                    </div>
                </x-ui.glass-card>
            </div>
            @elseif($tab === 'keluar' || $tab === 'masuk')
{{-- Form input Barang Masuk / Keluar tab Warehouse Ops (inline, tanpa partial) --}}
<div class="w-full flex-1 min-h-0 flex flex-col space-y-4">
    {{-- Pengingat opname: menetap sampai semua SKU diopname bulan ini --}}
    @if(($belumOpnameOps ?? 0) > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-2 shadow-sm" role="alert">
        <div class="flex items-center gap-2 text-amber-800 text-xs font-bold">
            <i class="fas fa-bell"></i>
            <span>{{ number_format($belumOpnameOps, 0, ',', '.') }} dari {{ number_format($totalAktifOps ?? 0, 0, ',', '.') }} SKU belum diopname bulan {{ $namaBulanOps ?? '' }}. Stok awal kartu memakai data terakhir — lakukan opname agar akurat.</span>
        </div>
        <a href="{{ route('warehouse.stockopname.index') }}" class="shrink-0 inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold px-3 py-2 rounded-xl transition shadow-sm sm:ml-auto">Buka halaman Opname <i class="fas fa-arrow-right text-[10px]"></i></a>
    </div>
    @else
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-2.5 text-emerald-700 text-xs font-bold shadow-sm"><i class="fas fa-check-circle mr-1"></i>Semua SKU sudah diopname bulan {{ $namaBulanOps ?? 'ini' }}.</div>
    @endif
    {{-- Toast pengingat opname: sekali sehari per browser --}}
    <div id="opsToast" class="hidden fixed bottom-6 right-6 z-[9999] w-[340px] max-w-[85vw] bg-slate-900/90 backdrop-blur border border-white/10 shadow-2xl rounded-2xl p-4 flex items-start gap-3" role="alert">
        <div class="w-8 h-8 shrink-0 rounded-lg bg-amber-500/30 border border-amber-300/30 flex items-center justify-center"><i class="fas fa-bell text-amber-200 text-sm"></i></div>
        <div class="flex-1 min-w-0">
            <p class="text-[13px] font-black text-white uppercase tracking-widest">Pengingat Opname</p>
            <p class="text-xs font-medium text-slate-200 leading-snug mt-0.5"><span data-ops-toast-n>0</span> SKU belum diopname bulan {{ $namaBulanOps ?? 'ini' }}. Input keluar/masuk tetap bisa — tapi segera isi opname ya.</p>
        </div>
        <button type="button" id="opsToastClose" class="shrink-0 text-slate-400 hover:text-white transition-colors p-1" aria-label="Tutup"><i class="fas fa-times text-xs"></i></button>
    </div>
    <script>
        (function () {
            var BELUM = {{ (int) ($belumOpnameOps ?? 0) }};
            if (!BELUM) return;
            var key = 'ops_opname_remind_{{ now()->format('Y-m') }}';
            try {
                if (sessionStorage.getItem(key)) return;
                sessionStorage.setItem(key, '1');
            } catch (e) { return; }
            var el = document.getElementById('opsToast');
            if (!el) return;
            var n = el.querySelector('[data-ops-toast-n]');
            if (n) n.textContent = BELUM;
            el.classList.remove('hidden');
            setTimeout(function () { el.classList.add('hidden'); }, 9000);
            var x = document.getElementById('opsToastClose');
            if (x) x.addEventListener('click', function () { el.classList.add('hidden'); });
        })();
    </script>
    @if(!$canManage)
        <x-ui.glass-card class="border-t-4 border-t-red-400 text-sm text-slate-600">Akun Anda tidak memiliki akses input barang. Hubungi pengelola stok.</x-ui.glass-card>
    @else
    <x-ui.glass-card class="w-full border-t-4 border-t-blue-500 shadow-lg !rounded-2xl !p-4 sj-form-card">
        <form action="{{ route('warehouse.mutation.store') }}" method="POST" id="pencatatan-form" data-clearable class="min-h-0">
            @csrf
            {{-- Penanda form Ops (mandiri, terpisah dari form Surat Jalan) --}}
            <input type="hidden" name="asal" value="ops">
            {{-- Penampung error validasi server (tidak masuk toast) agar kegagalan selalu terlihat --}}
            @if ($errors->any())
                <div class="mb-2 bg-red-50 border border-red-200 text-red-700 p-2.5 rounded-xl text-xs" role="alert">
                    <p class="font-bold mb-0.5 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Gagal menyimpan</p>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] font-semibold">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-2 bg-red-50 border border-red-200 text-red-700 p-2.5 rounded-xl text-xs font-semibold" role="alert">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif
            <div class="grid grid-cols-12 gap-x-2 gap-y-1.5 items-end shrink-0">
                <div class="col-span-6 md:col-span-2">
                    <label class="ui-label">Tanggal {{ $tab === 'masuk' ? 'Masuk' : 'Keluar' }} <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" required value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" class="ui-input-date !h-[30px] !text-xs">
                </div>
                @if($tab === 'masuk')
                <div class="col-span-6 md:col-span-3">
                    <label class="ui-label">No. SJ Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="doc_no" required maxlength="100" placeholder="cth. SJ-SPL-001" class="ui-input ui-input--sm" autocomplete="off">
                </div>
                <div class="col-span-12 md:col-span-4">
                    <label class="ui-label">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" id="partner-select" required class="ui-input ui-input--sm !bg-white">
                        <option value="">— pilih supplier —</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" data-addr="{{ $supplierAddressMap[$s->id] ?? '' }}">{{ $s->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="ui-label">No. PO Supplier</label>
                    <input type="text" name="po_no" maxlength="100" placeholder="No. PO supplier (opsional)" class="ui-input ui-input--sm" autocomplete="off">
                </div>
                @else
                <div class="col-span-6 md:col-span-2">
                    <label class="ui-label">No. SJ <span class="text-red-500">*</span></label>
                    <input type="text" name="doc_no" required maxlength="100" placeholder="cth. SJ-2026-001" class="ui-input ui-input--sm" autocomplete="off">
                </div>
                <div class="col-span-6 md:col-span-2">
                    <label class="ui-label">Keterangan <span class="text-red-500">*</span></label>
                    <input list="purpose-options" name="purpose" id="purpose-select" required maxlength="50" placeholder="pilih / ketik sendiri…" class="ui-input ui-input--sm" autocomplete="off">
                    <datalist id="purpose-options">
                        @foreach($purposes as $p)
                            @continue(in_array($p, ['Bonus', 'Internal'], true))
                            <option value="{{ $p }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-span-12 md:col-span-3" id="partner-wrap">
                    <label class="ui-label" id="partner-label">Customer <span class="text-red-500">*</span></label>
                    <select name="customer_id" id="partner-select" required class="ui-input ui-input--sm !bg-white">
                        <option value="">— pilih customer —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-addr="{{ $customerAddressMap[$c->id] ?? '' }}">{{ $c->customer_name }}</option>
                        @endforeach
                    </select>
                    <select id="supplier-src" class="hidden" aria-hidden="true" tabindex="-1">
                        <option value="">— pilih supplier —</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" data-addr="{{ $supplierAddressMap[$s->id] ?? '' }}">{{ $s->supplier_name }}</option>
                        @endforeach
                    </select>
                    <div id="partner-none" class="hidden">
                        <label class="ui-label">Nama PS <span class="text-red-500">*</span></label>
                        <select name="ps_name" id="ps-select" class="ui-input ui-input--sm !bg-white">
                            <option value="">— pilih PS —</option>
                            @foreach($psList ?? [] as $ps)
                                <option value="{{ $ps }}">{{ $ps }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="ui-label">No. PO / Referensi</label>
                    <input type="text" name="po_no" maxlength="100" placeholder="No. PO / referensi (opsional)" class="ui-input ui-input--sm" autocomplete="off">
                </div>
                @endif
            </div>
            <div class="mt-2 gap-2 items-end shrink-0" style="display:grid;grid-template-columns:minmax(120px,1fr) minmax(0,2fr);gap:8px;align-items:end;">
                <div>
                    <label class="ui-label">Ekspedisi</label>
                    <input list="expedition-options" name="expedition" maxlength="100" placeholder="cth. JNE / GoSend (opsional)" class="ui-input ui-input--sm" autocomplete="off">
                    <datalist id="expedition-options">
                        @foreach($expeditions as $e)
                            <option value="{{ $e }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="ui-label">Alamat {{ $tab === 'masuk' ? 'Supplier' : 'Kirim' }}</label>
                    <div class="relative">
                        <input type="text" name="ship_address" id="ship-address" maxlength="2000" placeholder="otomatis dari master, bisa diubah…" class="ui-input ui-input--sm" autocomplete="off">
                        <p id="addr-warn" class="hidden absolute left-0 z-10 text-[10px] text-amber-600 font-bold mt-0.5" style="top:100%"><i class="fas fa-exclamation-triangle mr-1"></i>Master belum punya alamat — ketik manual atau lengkapi di master.</p>
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 space-y-2 flex-1 min-h-0 flex flex-col">
                <div class="flex items-center gap-2 shrink-0">
                    <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider whitespace-nowrap">Rincian Barang {{ $tab === 'masuk' ? 'Masuk' : 'Keluar' }}</h4>
                    <div class="flex-1 h-px bg-slate-100"></div>
                    <span id="sum-info" class="ui-info !h-[26px] !text-[11px]">0 baris · total 0</span>
                </div>

                {{-- Search + tabel dalam satu blok fleksibel: hanya tabel yang scroll --}}
                <div class="relative shrink-0">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-blue-300 text-xs pointer-events-none"></i>
                        <input type="text" id="product-search" placeholder="Cari barang (nama / kode) — klik hasil untuk isi Qty, Batch & Exp…" title="Ketik nama / kode barang, lalu klik hasilnya untuk isi Qty, Batch & Exp lewat popup." class="ui-search !h-[32px] !pl-9 !bg-white hover:!bg-white !text-slate-700" autocomplete="off">
                    </div>
                    <div id="product-search-results" class="hidden absolute left-0 right-0 z-30 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden max-h-64 overflow-y-auto divide-y divide-slate-100"></div>
                </div>

                {{-- Tabel barang yang sudah disimpan (scroll internal, header sticky) --}}
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white flex-1 min-h-0 flex flex-col">
                    <div class="sj-items-wrap overflow-auto flex-1 min-h-0">
                        <table class="w-full text-xs min-w-[760px]">
                            <thead>
                                <tr class="bg-slate-50/80 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                                    <th class="px-3 py-2 text-center w-12 font-bold">No</th>
                                    <th class="px-3 py-2 text-left font-bold">Nama Barang</th>
                                    <th class="px-3 py-2 text-center w-24 font-bold">Kode</th>
                                    <th class="px-3 py-2 text-right w-24 font-bold">Qty</th>
                                    <th class="px-3 py-2 text-center w-20 font-bold">Satuan</th>
                                    <th class="px-3 py-2 text-left w-32 font-bold">No. Batch</th>
                                    <th class="px-3 py-2 text-center w-32 font-bold">Tgl Exp</th>
                                    <th class="px-3 py-2 text-center w-24 font-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="rows-container" class="divide-y divide-slate-100"></tbody>
                        </table>
                    <div id="empty-barang" class="px-4 py-3 text-center bg-slate-50/50 border-t border-dashed border-slate-200">
                        <p class="text-xs font-bold text-slate-500"><i class="fas fa-box-open text-slate-300 mr-1.5"></i>Belum ada barang — cari di atas lalu klik untuk menambahkan.</p>
                    </div>
                    </div>
                </div>
            </div>

            {{-- Popup isi rincian barang yang diklik (fixed overlay, tombol type=button agar tak submit form induk) --}}
            <div id="barang-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div id="barang-modal-backdrop" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm opacity-0 transition-opacity duration-200" data-modal-close></div>
                <div id="barang-modal-panel" class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col opacity-0 scale-95 transition-all duration-200">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm"><i class="fas fa-box text-xs"></i></span>
                            <div class="min-w-0">
                                <h5 id="modal-title" class="font-bold text-sm text-slate-800 leading-tight">Tambah Barang</h5>
                                <p class="text-[11px] text-slate-400 font-medium mt-0.5">Isi Qty & Batch, lalu simpan ke tabel.</p>
                            </div>
                        </div>
                        <button type="button" data-modal-close class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0" title="Tutup"><i class="fas fa-times text-xs"></i></button>
                    </div>
                    <div class="p-5 space-y-4 overflow-y-auto">
                        <div>
                            <label class="ui-label">Nama Barang</label>
                            <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                                <div id="m-nama" class="text-sm font-bold text-slate-800 leading-snug break-words">—</div>
                            </div>
                            <input type="hidden" id="m-product-id" value="">
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="ui-label">Kode Barang</label>
                                <input type="text" id="m-kode" readonly tabindex="-1" placeholder="—" class="ui-input ui-input--sm !bg-slate-50 !text-slate-500 !border-slate-200 text-center cursor-not-allowed">
                            </div>
                            <div>
                                <label class="ui-label">Satuan</label>
                                <input type="text" id="m-satuan" readonly tabindex="-1" placeholder="—" class="ui-input ui-input--sm !bg-slate-50 !text-slate-500 !border-slate-200 text-center cursor-not-allowed">
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <label class="ui-label !mb-0">Qty <span class="text-red-500">*</span></label>
                                <span id="m-stok-info" class="text-[10px] font-bold"></span>
                            </div>
                            <input type="number" id="m-qty" min="0.01" step="any" placeholder="0" class="ui-input !bg-white !h-[38px] text-right font-bold !text-slate-800">
                            <p id="m-batch-info" class="text-[10px] text-slate-400 font-medium mt-1.5 min-h-[14px]"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="ui-label">No. Batch {{ $tab === 'masuk' ? '*' : '' }}</label>
                                <input type="text" id="m-batch" list="m-batch-list" maxlength="100" placeholder="{{ $tab === 'masuk' ? 'wajib diisi' : 'opsional' }}" class="ui-input ui-input--sm !bg-white !text-slate-800" autocomplete="off">
                                <datalist id="m-batch-list"></datalist>
                            </div>
                            <div>
                                <label class="ui-label">Tgl Expired {{ $tab === 'masuk' ? '*' : '' }}</label>
                                <input type="date" id="m-exp" class="ui-input ui-input--sm !bg-white !text-slate-800">
                            </div>
                        </div>
                        <p id="m-error" class="hidden text-[11px] font-bold text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2.5"></p>
                    </div>
                    <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex gap-2 justify-end shrink-0">
                        <button type="button" data-modal-close class="h-[36px] px-4 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                        <button type="button" id="m-save" class="h-[36px] px-5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-colors"><i class="fas fa-check mr-1.5"></i>Simpan ke Tabel</button>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-100 flex justify-end shrink-0">
                <button type="submit" id="simpan-mutasi-btn" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold h-[34px] px-5 rounded-lg shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all w-full sm:w-auto disabled:opacity-60 disabled:cursor-not-allowed">
                    <i class="fas fa-save text-[11px]"></i> {{ $tab === 'masuk' ? 'Simpan Barang Masuk' : 'Simpan Barang Keluar' }}
                </button>
            </div>
        </form>
    </x-ui.glass-card>
    @endif
</div>

@push('scripts')
<script>
    // ---- Search + Modal + Tabel ----
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('rows-container');
        const emptyBox = document.getElementById('empty-barang');
        const form = document.getElementById('pencatatan-form');
        if (!container || !form) return;
        const productOptions = {!! $productOptionsJson !!};
        const TAB_ARAH = @json($tab);
        const DRAFT_KEY = 'pencatatan_draft_v3_' + TAB_ARAH;
        const BATCH_URL = '{{ route("warehouse.mutation.batches") }}';
        let isRestoring = false, isSubmitting = false;
        let editingRow = null;
        let modalBatches = [];

        function fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(n)); }
        function esc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

        function recalc() {
            let n = 0, q = 0;
            container.querySelectorAll('.row-barang').forEach(row => {
                n++;
                q += parseFloat(row.querySelector('.input-qty')?.value) || 0;
            });
            const sumEl = document.getElementById('sum-info');
            if (sumEl) sumEl.textContent = n + ' baris · total ' + fmt(q);
            if (emptyBox) emptyBox.style.display = n ? 'none' : 'block';
            saveDraft();
        }

        function saveDraft() {
            if (!form || isRestoring) return;
            const rows = [];
            container.querySelectorAll('.row-barang').forEach(row => {
                rows.push({
                    product_id: row.querySelector('.input-barang')?.value || '',
                    qty: row.querySelector('.input-qty')?.value || '',
                    batch_no: row.querySelector('.input-batch')?.value || '',
                    expiry_date: row.querySelector('.input-ed')?.value || '',
                    keterangan: '',
                });
            });
            const partnerSel = form.querySelector('#partner-select');
            localStorage.setItem(DRAFT_KEY, JSON.stringify({
                tanggal: form.querySelector('input[name="tanggal"]')?.value || '',
                doc_no: form.querySelector('input[name="doc_no"]')?.value || '',
                customer_id: form.querySelector('select[name="customer_id"]')?.value || '',
                supplier_id: form.querySelector('select[name="supplier_id"]')?.value || '',
                po_no: form.querySelector('input[name="po_no"]')?.value || '',
                purpose: form.querySelector('#purpose-select')?.value || '',
                ps_name: form.querySelector('#ps-select')?.value || '',
                ship_address: form.querySelector('#ship-address')?.value || '',
                expedition: form.querySelector('input[name="expedition"]')?.value || '',
                rows,
            }));
        }

        // ---- Header dinamis: purpose mengatur pasangan + alamat ----
        const purposeSel = document.getElementById('purpose-select');
        const partnerSel = document.getElementById('partner-select');
        const partnerLabel = document.getElementById('partner-label');
        const partnerWrap = document.getElementById('partner-wrap');
        const shipInput = document.getElementById('ship-address');
        const addrWarn = document.getElementById('addr-warn');
        const customerOptsHTML = partnerSel ? partnerSel.innerHTML : '';
        const supplierSrc = document.getElementById('supplier-src');

        function fillAddress() {
            if (!partnerSel || !shipInput || partnerSel.disabled) return;
            const opt = partnerSel.selectedOptions[0];
            const addr = opt?.dataset?.addr || '';
            if (addr) {
                shipInput.value = addr;
                if (addrWarn) addrWarn.classList.add('hidden');
            } else if (partnerSel.value) {
                if (addrWarn) addrWarn.classList.remove('hidden');
            } else {
                shipInput.value = '';
                if (addrWarn) addrWarn.classList.add('hidden');
            }
        }

        function applyPurpose() {
            if (!purposeSel || !partnerSel) return;
            const v = purposeSel.value;
            if (v === 'Retur Supplier' && supplierSrc) {
                partnerSel.name = 'supplier_id';
                partnerSel.innerHTML = supplierSrc.innerHTML;
                if (partnerLabel) partnerLabel.innerHTML = 'Supplier <span class="text-red-500">*</span>';
            } else {
                partnerSel.name = 'customer_id';
                partnerSel.innerHTML = customerOptsHTML;
                if (partnerLabel) partnerLabel.innerHTML = 'Customer <span class="text-red-500">*</span>';
            }
            const isInternal = v === 'Internal';
            const isSample = v === 'Sample';
            const psWrap = document.getElementById('partner-none');
            const psSel = document.getElementById('ps-select');
            // Internal = tanpa pihak: seluruh kolom partner disembunyikan (tanpa pengganti)
            if (partnerWrap) partnerWrap.classList.toggle('hidden', isInternal);
            if (partnerSel) {
                partnerSel.classList.toggle('hidden', isSample);
                partnerSel.disabled = isInternal || isSample;
                partnerSel.required = !isInternal && !isSample;
                if (isSample) partnerSel.value = '';
            }
            if (partnerLabel) partnerLabel.classList.toggle('hidden', isSample);
            // Sample = pihak nama PS: select customer disembunyikan, tampil dropdown PS
            if (psWrap) psWrap.classList.toggle('hidden', !isSample || isInternal);
            if (psSel) {
                psSel.required = isSample && !isInternal;
                if (!isSample) psSel.value = '';
            }
            if (isInternal && shipInput) { shipInput.value = ''; }
            if (addrWarn && (isInternal || isSample || !partnerSel.value)) addrWarn.classList.add('hidden');
        }

        if (purposeSel) purposeSel.addEventListener('change', () => { applyPurpose(); recalc(); });
        if (partnerSel) partnerSel.addEventListener('change', () => { fillAddress(); recalc(); });

        // ---- Search barang ----
        const searchInput = document.getElementById('product-search');
        const searchBox = document.getElementById('product-search-results');

        function renderSearch(keyword = '') {
            if (!searchBox) return;
            const kw = keyword.trim().toLowerCase();
            const list = productOptions.filter(p => {
                if (!kw) return false;
                return (p.nama || '').toLowerCase().includes(kw) || (p.kode || '').toLowerCase().includes(kw);
            }).slice(0, 20);
            if (!kw) { searchBox.classList.add('hidden'); searchBox.innerHTML = ''; return; }
            if (!list.length) {
                searchBox.innerHTML = '<div class="px-4 py-3 text-xs text-slate-400 font-semibold">Tidak ketemu — coba kata kunci lain.</div>';
            } else {
                searchBox.innerHTML = list.map(p =>
                    `<button type="button" data-pid="${p.id}" class="w-full text-left px-4 py-2.5 hover:bg-blue-50/70 flex items-center justify-between gap-3 transition-colors">` +
                    `<span class="min-w-0"><span class="block text-xs font-bold text-slate-800 truncate">${esc(p.nama)}${p.opnamed ? '' : ' <span class="text-[9px] font-black px-1.5 py-0.5 rounded-full bg-rose-100 text-rose-700 align-middle">belum opname</span>'}</span>` +
                    `<span class="block text-[10px] text-slate-400 font-semibold mt-0.5">${esc(p.kode || '—')} · ${esc(p.unit || '')}</span></span>` +
                    `<span class="text-[10px] font-bold ${parseFloat(p.stok) > 0 ? 'text-emerald-600 bg-emerald-50 border border-emerald-200' : 'text-red-500 bg-red-50 border border-red-200'} whitespace-nowrap px-2 py-1 rounded-lg">stok ${esc(p.stok)}</span></button>`
                ).join('');
            }
            searchBox.classList.remove('hidden');
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => renderSearch(searchInput.value));
            searchInput.addEventListener('focus', () => renderSearch(searchInput.value));
            document.addEventListener('click', (e) => {
                if (searchBox && !searchBox.classList.contains('hidden')) {
                    if (!searchBox.contains(e.target) && e.target !== searchInput) searchBox.classList.add('hidden');
                }
            });
        }
        if (searchBox) {
            searchBox.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-pid]');
                if (!btn) return;
                searchBox.classList.add('hidden');
                if (searchInput) searchInput.value = '';
                openModal(btn.dataset.pid, null);
            });
        }

        // ---- Modal ----
        const modal = document.getElementById('barang-modal');
        const modalPanel = document.getElementById('barang-modal-panel');
        const modalBackdrop = document.getElementById('barang-modal-backdrop');
        // Pindah ke body: parent card pakai backdrop-blur sehingga `fixed` di dalamnya rusak (menempel ke card, bukan layar)
        if (modal && modal.parentElement !== document.body) document.body.appendChild(modal);
        const mNama = document.getElementById('m-nama');
        const mPid = document.getElementById('m-product-id');
        const mKode = document.getElementById('m-kode');
        const mSatuan = document.getElementById('m-satuan');
        const mQty = document.getElementById('m-qty');
        const mBatch = document.getElementById('m-batch');
        const mBatchList = document.getElementById('m-batch-list');
        const mBatchInfo = document.getElementById('m-batch-info');
        const mExp = document.getElementById('m-exp');
        const mStok = document.getElementById('m-stok-info');
        const mErr = document.getElementById('m-error');
        const mSave = document.getElementById('m-save');
        const mTitle = document.getElementById('modal-title');

        function showErr(msg) {
            if (!mErr) return;
            if (!msg) { mErr.classList.add('hidden'); mErr.innerHTML = ''; return; }
            mErr.innerHTML = '<i class="fas fa-exclamation-circle mr-1.5"></i>' + esc(msg);
            mErr.classList.remove('hidden');
        }

        function stokPill(txt, tone) {
            const map = {
                red: 'text-red-700 bg-red-50 border-red-200',
                green: 'text-emerald-700 bg-emerald-50 border-emerald-200',
                slate: 'text-slate-500 bg-slate-100 border-slate-200',
            };
            return '<span class="inline-flex items-center gap-1 text-[10px] font-bold border rounded-lg px-2 py-0.5 ' + (map[tone] || map.slate) + '">' + esc(txt) + '</span>';
        }

        function refreshModalStock() {
            const p = productOptions.find(x => String(x.id) === String(mPid?.value));
            if (!p || !mStok) { if (mStok) mStok.innerHTML = ''; return; }
            const sisa = parseFloat(p.stok) || 0;
            const qty = parseFloat(mQty?.value) || 0;
            const sisaTxt = 'Sisa ' + fmt(sisa) + (p.unit ? ' ' + p.unit : '');
            if (TAB_ARAH !== 'masuk') {
                const over = qty > 0 && qty > sisa;
                const low = sisa <= 0;
                let extra = '';
                if (over) extra = ' · melebihi!';
                else if (low) extra = ' · habis';
                mStok.innerHTML = stokPill(sisaTxt + extra, (low || over) ? 'red' : 'green');
            } else {
                mStok.innerHTML = stokPill(sisaTxt, 'slate');
            }
        }

        function refreshModalBatchInfo() {
            if (!mBatchInfo || !mBatch) return;
            const val = (mBatch.value || '').trim();
            const found = modalBatches.find(b => String(b.batch_no) === val);
            if (!val) mBatchInfo.textContent = '';
            else if (found) mBatchInfo.textContent = 'Sisa ' + found.qty + (found.expiry_date ? ' — ED ' + found.expiry_label : ' — tanpa ED');
            else mBatchInfo.textContent = 'Batch tidak ada di daftar — pastikan no. batch benar.';
        }

        async function loadModalBatches(productId) {
            modalBatches = [];
            if (mBatchList) mBatchList.innerHTML = '';
            refreshModalBatchInfo();
            if (!productId || TAB_ARAH === 'masuk') return;
            try {
                const res = await fetch(BATCH_URL + '?product_id=' + encodeURIComponent(productId), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                modalBatches = json.data || [];
                modalBatches.forEach(b => {
                    const o = document.createElement('option');
                    o.value = b.batch_no;
                    o.textContent = b.batch_no + ' — sisa ' + b.qty + (b.expiry_date ? ' — ED ' + b.expiry_label : '');
                    mBatchList.appendChild(o);
                });
                refreshModalBatchInfo();
                // Auto-isi Tgl Exp dari batch terpilih bila cocok
                const cur = (mBatch?.value || '').trim();
                const hit = modalBatches.find(b => String(b.batch_no) === cur);
                if (hit?.expiry_date && mExp && !mExp.value) mExp.value = hit.expiry_date;
            } catch (e) { /* biarkan FEFO otomatis */ }
        }

        function openModal(productId, row = null) {
            const p = productOptions.find(x => String(x.id) === String(productId));
            if (!p) return;
            editingRow = row || null;
            if (mPid) mPid.value = p.id;
            if (mNama) mNama.textContent = p.nama || '—';
            if (mKode) mKode.value = p.kode || '';
            if (mSatuan) mSatuan.value = p.unit || '';
            if (mTitle) mTitle.textContent = (row ? 'Ubah Barang' : 'Tambah Barang');
            if (row) {
                if (mQty) mQty.value = row.querySelector('.input-qty')?.value || '';
                if (mBatch) mBatch.value = row.querySelector('.input-batch')?.value || '';
                if (mExp) mExp.value = row.querySelector('.input-ed')?.value || '';
            } else {
                if (mQty) mQty.value = '';
                if (mBatch) mBatch.value = '';
                if (mExp) mExp.value = '';
            }
            showErr('');
            refreshModalStock();
            refreshModalBatchInfo();
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    modalBackdrop?.classList.remove('opacity-0');
                    modalPanel?.classList.remove('opacity-0', 'scale-95');
                }));
            }
            loadModalBatches(p.id);
            setTimeout(() => { mQty?.focus(); mQty?.select?.(); }, 60);
        }

        function closeModal() {
            const done = () => {
                if (modal) modal.classList.add('hidden');
                document.body.style.overflow = '';
            };
            if (modal && !modal.classList.contains('hidden')) {
                modalBackdrop?.classList.add('opacity-0');
                modalPanel?.classList.add('opacity-0', 'scale-95');
                setTimeout(done, 180);
            } else done();
            editingRow = null;
            showErr('');
        }

        if (modal) {
            modal.querySelectorAll('[data-modal-close]').forEach(b => b.addEventListener('click', closeModal));
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
            // Enter di dalam popup = Simpan ke Tabel (jangan submit form utama)
            ['m-qty', 'm-batch', 'm-exp'].forEach(id => {
                document.getElementById(id)?.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') { e.preventDefault(); mSave?.click(); }
                });
            });
        }
        if (mQty) { mQty.addEventListener('input', refreshModalStock); mQty.addEventListener('change', refreshModalStock); }
        if (mBatch) {
            mBatch.addEventListener('input', () => {
                refreshModalBatchInfo();
                const hit = modalBatches.find(b => String(b.batch_no) === (mBatch.value || '').trim());
                if (hit?.expiry_date && mExp) mExp.value = hit.expiry_date;
            });
            mBatch.addEventListener('change', refreshModalBatchInfo);
        }

        function rowCells(p, d) {
            return { nama: p.nama || '—', kode: p.kode || '—', unit: p.unit || '' };
        }

        function addRow(data = null) {
            if (!data?.product_id) return;
            const p = productOptions.find(x => String(x.id) === String(data.product_id));
            if (!p) return;
            const c = rowCells(p, data);
            const tr = document.createElement('tr');
            tr.className = 'row-barang hover:bg-blue-50/50 transition-colors bg-white';
            const arahEsc = esc(TAB_ARAH);
            tr.innerHTML =
                '<td class="px-4 py-3 text-center text-slate-400 font-semibold row-no"></td>' +
                '<td class="px-4 py-3"><span class="font-bold text-slate-800 cell-nama"></span>' +
                '<input type="hidden" name="product_id[]" class="input-barang">' +
                '<input type="hidden" name="arah[]" value="' + arahEsc + '"></td>' +
                '<td class="px-4 py-3 text-center text-slate-500 font-semibold cell-kode"></td>' +
                '<td class="px-4 py-3 text-right font-bold text-slate-800 cell-qty"><span class="qty-txt"></span>' +
                '<input type="hidden" name="qty[]" class="input-qty"></td>' +
                '<td class="px-4 py-3 text-center text-slate-500 cell-unit"></td>' +
                '<td class="px-4 py-3 text-slate-600 font-semibold cell-batch"><span class="batch-txt"></span>' +
                '<input type="hidden" name="batch_no[]" class="input-batch"></td>' +
                '<td class="px-4 py-3 text-center text-slate-500 cell-exp"><span class="exp-txt"></span>' +
                '<input type="hidden" name="expiry_date[]" class="input-ed"></td>' +
                '<td class="px-4 py-3 text-center whitespace-nowrap">' +
                '<button type="button" title="Ubah" class="btn-edit inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 h-[26px] rounded-lg transition-colors mr-1"><i class="fas fa-pen text-[10px]"></i></button>' +
                '<button type="button" title="Hapus" class="btn-remove inline-flex items-center gap-1 text-[11px] font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 px-2 h-[26px] rounded-lg transition-colors"><i class="fas fa-trash-alt text-[10px]"></i></button></td>';
            // Isi nilai (textContent agar aman, value untuk hidden)
            tr.querySelector('.cell-nama').textContent = c.nama;
            tr.querySelector('.input-barang').value = p.id;
            tr.querySelector('.cell-kode').textContent = c.kode;
            tr.querySelector('.qty-txt').textContent = (data.qty || '') + '';
            tr.querySelector('.input-qty').value = data.qty || '';
            tr.querySelector('.cell-unit').textContent = c.unit;
            tr.querySelector('.batch-txt').textContent = data.batch_no || '-';
            tr.querySelector('.input-batch').value = data.batch_no || '';
            tr.querySelector('.exp-txt').textContent = data.expiry_date || '—';
            tr.querySelector('.input-ed').value = data.expiry_date || '';
            tr.querySelector('.btn-remove').addEventListener('click', () => { tr.remove(); renumber(); recalc(); });
            tr.querySelector('.btn-edit').addEventListener('click', () => openModal(p.id, tr));
            container.appendChild(tr);
            renumber(); recalc();
        }

        function collectModalData() {
            return {
                product_id: mPid?.value || '',
                qty: (mQty?.value || '').trim(),
                batch_no: (mBatch?.value || '').trim(),
                expiry_date: mExp?.value || '',
            };
        }

        if (mSave) {
            mSave.addEventListener('click', () => {
                const d = collectModalData();
                const p = productOptions.find(x => String(x.id) === String(d.product_id));
                if (!p) { showErr('Barang tidak valid — tutup lalu pilih ulang dari pencarian.'); return; }
                const qtyNum = parseFloat(d.qty);
                if (!d.qty || !(qtyNum > 0)) { showErr('Qty wajib diisi dan lebih dari 0.'); mQty?.focus(); return; }
                if (TAB_ARAH === 'masuk') {
                    if (!d.batch_no) { showErr('No. Batch wajib untuk barang masuk.'); mBatch?.focus(); return; }
                    if (!d.expiry_date) { showErr('Tgl Expired wajib untuk barang masuk.'); mExp?.focus(); return; }
                }
                if (TAB_ARAH !== 'masuk') {
                    const sisa = parseFloat(p.stok) || 0;
                    if (qtyNum > sisa) { showErr('Qty melebihi sisa stok (' + fmt(sisa) + '). Kurangi qty atau pilih barang lain.'); mQty?.focus(); return; }
                }
                showErr('');
                if (editingRow) {
                    // update baris yang sedang diubah
                    editingRow.querySelector('.input-qty').value = d.qty;
                    editingRow.querySelector('.qty-txt').textContent = d.qty;
                    editingRow.querySelector('.input-batch').value = d.batch_no;
                    editingRow.querySelector('.batch-txt').textContent = d.batch_no || '-';
                    editingRow.querySelector('.input-ed').value = d.expiry_date;
                    editingRow.querySelector('.exp-txt').textContent = d.expiry_date || '—';
                    recalc();
                } else {
                    addRow(d);
                }
                closeModal();
                searchInput?.focus();
            });
        }

        function renumber() {
            container.querySelectorAll('.row-barang').forEach((tr, i) => {
                const no = tr.querySelector('.row-no');
                if (no) no.textContent = (i + 1);
            });
        }

        // restore draft (terpisah per tab)
        try {
            isRestoring = true;
            const saved = JSON.parse(localStorage.getItem(DRAFT_KEY) || 'null');
            const setVal = (sel, v) => { const el = form.querySelector(sel); if (el && v) el.value = v; };
            if (saved) {
                setVal('input[name="tanggal"]', saved.tanggal);
                setVal('input[name="doc_no"]', saved.doc_no);
                setVal('input[name="po_no"]', saved.po_no);
                setVal('input[name="expedition"]', saved.expedition);
                setVal('#ship-address', saved.ship_address);
                const purEl = form.querySelector('#purpose-select');
                if (purEl && saved.purpose) purEl.value = saved.purpose;
                if (typeof applyPurpose === 'function') applyPurpose();
                const pSel = form.querySelector('#partner-select');
                if (pSel && !pSel.disabled) {
                    // Kembalikan pasangan sesuai purpose tersimpan (jangan tertukar customer/supplier)
                    if (saved.purpose === 'Retur Supplier' && saved.supplier_id) {
                        pSel.name = 'supplier_id';
                        pSel.value = saved.supplier_id;
                    } else if (saved.purpose !== 'Retur Supplier' && saved.customer_id) {
                        pSel.name = 'customer_id';
                        pSel.value = saved.customer_id;
                    }
                }
                const psS = form.querySelector('#ps-select');
                if (psS && saved.ps_name) psS.value = saved.ps_name;
                if (typeof fillAddress === 'function') {
                    const shipEl = form.querySelector('#ship-address');
                    if (shipEl && !shipEl.value) fillAddress();
                }
            }
            if (saved?.rows?.length) { saved.rows.forEach(r => addRow(r)); }
        } catch (e) { /* mulai kosong, user cari dulu */ }
        finally { isRestoring = false; recalc(); }

        // Submit: tolak bila tabel masih kosong + kunci klik ganda (mutasi stok tidak boleh duplikat).
        // Draft TIDAK dihapus di sini: sukses -> redirect + dibersihkan halaman tujuan,
        // gagal/offline -> tidak navigasi -> draft utuh, tinggal perbaiki & kirim ulang.
        // Tombol dibuka lagi otomatis bila ternyata tidak jadi navigasi (mis. offline).
        form.addEventListener('submit', function(e) {
            if (container.querySelectorAll('.row-barang').length === 0) {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                    renderSearch(searchInput.value || 'a');
                }
                alert('Belum ada barang — cari barang di atas, klik, isi Qty di popup, lalu Simpan ke Tabel.');
                return;
            }
            if (isSubmitting) { e.preventDefault(); return; }
            isSubmitting = true;
            const submitBtn = document.getElementById('simpan-mutasi-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.label = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-[11px]"></i> Menyimpan...';
                setTimeout(() => {
                    isSubmitting = false;
                    submitBtn.disabled = false;
                    if (submitBtn.dataset.label) submitBtn.innerHTML = submitBtn.dataset.label;
                }, 15000);
            }
        });

        // Flush terakhir anti mati-lampu (draft selalu disimpan ulang saat pergi).
        window.addEventListener('pagehide', saveDraft);
        form.querySelectorAll('input[name="tanggal"], input[name="doc_no"], #partner-select, input[name="po_no"], #purpose-select, #ship-address, input[name="expedition"]').forEach(el => {
            el.addEventListener('input', saveDraft);
            el.addEventListener('change', saveDraft);
        });
    });
</script>
@endpush
            @endif

            {{-- Modal edit baris --}}
            <div x-cloak x-show="showEditModal" class="relative z-[100]" role="dialog" aria-modal="true">
                <div x-show="showEditModal" x-transition.opacity class="fixed inset-0" style="background-color: rgba(15,23,42,0.75); backdrop-filter: blur(8px);" @click="showEditModal = false"></div>
                <div class="fixed inset-0 z-[110] w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="showEditModal" x-transition class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full" style="max-width: 560px;">
                            <form :action="editUrl" method="POST">
                                @csrf @method('PUT')
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="flex justify-between items-center mb-1">
                                        <h3 class="text-lg font-bold text-slate-800">Edit Baris <span class="text-blue-600" x-text="'#' + formData.id"></span></h3>
                                        <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-500"><i class="fas fa-times text-xl"></i></button>
                                    </div>
                                    <p class="text-xs text-slate-500 mb-4"><span class="font-bold" x-text="formData.barang"></span> · <span x-text="formData.arah"></span><span x-text="formData.pihak ? ' · ' + formData.pihak : ''"></span> — produk, arah & pihak tidak bisa diubah.</p>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div><label class="ui-label">Tanggal</label><input type="date" name="tanggal" x-model="formData.tanggal" max="{{ today()->toDateString() }}" class="ui-input ui-input--sm"></div>
                                        <div><label class="ui-label">Qty</label><input type="number" name="qty" x-model="formData.qty" min="0.01" step="any" class="ui-input ui-input--sm"></div>
                                        <div><label class="ui-label">No. Surat Jalan</label><input type="text" name="doc_no" x-model="formData.doc_no" maxlength="100" class="ui-input ui-input--sm"></div>
                                        <div><label class="ui-label">No. PO / Referensi</label><input type="text" name="po_no" x-model="formData.po_no" maxlength="100" class="ui-input ui-input--sm"></div>
                                        <div><label class="ui-label">Ekspedisi</label><input type="text" name="expedition" x-model="formData.expedition" maxlength="100" class="ui-input ui-input--sm"></div>
                                        <div><label class="ui-label">Alamat Kirim</label><input type="text" name="ship_address" x-model="formData.ship_address" maxlength="2000" class="ui-input ui-input--sm"></div>
                                        <div><label class="ui-label">Batch No.</label><input type="text" name="batch_no" x-model="formData.batch_no" maxlength="100" class="ui-input ui-input--sm"></div>
                                        <div><label class="ui-label">Expired Date</label><input type="date" name="expiry_date" x-model="formData.expiry_date" class="ui-input ui-input--sm"></div>
                                        <div class="col-span-2"><label class="ui-label">Keterangan</label><input type="text" name="keterangan" x-model="formData.keterangan" maxlength="1000" required class="ui-input ui-input--sm"></div>
                                    </div>
                                    <p class="text-[11px] text-amber-600 font-medium mt-3 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">Ubah qty/tanggal/expired = reversal + mutasi koreksi baru (jejak audit utuh, stok menyesuaikan).</p>
                                </div>
                                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Simpan</button>
                                    <button type="button" @click="showEditModal = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Popup detail dokumen --}}
            <div id="docModal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-3 sm:p-6">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
                    <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-start gap-3 bg-slate-50 shrink-0">
                        <div class="min-w-0">
                            <div id="docModalNo" class="font-mono text-[11px] font-bold tracking-wider text-slate-400"></div>
                            <h3 id="docModalPihak" class="text-[15px] font-black text-slate-800 leading-tight truncate">Dokumen</h3>
                            <p id="docModalMeta" class="text-[11px] text-slate-500 font-medium mt-0.5"></p>
                        </div>
                        <button type="button" onclick="closeDocModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center shrink-0"><i class="fas fa-times"></i></button>
                    </div>
                    <div id="docModalBody" class="p-4 overflow-y-auto space-y-3 flex-1"></div>
                </div>
            </div>

            {{-- Popup kartu stock --}}
            <div id="kartuModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-3 sm:p-6">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
                    <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-start gap-3 bg-slate-50 shrink-0">
                        <div class="min-w-0">
                            <div id="kartuModalKode" class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400"></div>
                            <h3 id="kartuModalNama" class="text-[15px] font-black text-slate-800 leading-tight truncate">Kartu Stock</h3>
                            <p class="text-[11px] text-slate-500 font-medium mt-0.5">Stok <span id="kartuModalStock" class="font-black text-slate-800">-</span> <span id="kartuModalUnit"></span> · Stok Awal <span id="kartuModalSaldo" class="font-black text-blue-700">-</span> · Update terakhir <span id="kartuModalUpdated" class="font-bold text-slate-600">-</span></p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a id="kartuModalFull" href="#" class="hidden sm:inline-flex items-center gap-1.5 h-[32px] px-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-bold" title="Buka di halaman penuh"><i class="fas fa-expand"></i> Halaman penuh</a>
                            <button type="button" onclick="closeKartuModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center shrink-0"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div id="kartuModalToast" class="hidden mx-4 mt-3 px-4 py-2.5 rounded-xl text-xs font-bold shrink-0"></div>
                    <div id="kartuModalBody" class="p-4 overflow-y-auto space-y-3 flex-1"></div>
                </div>
            </div>

            @include('users.warehouse.card-edit-modal')

        </div>
    </div>

    @push('scripts')
    {{-- SweetAlert2 sudah global via layout-users; blok ini hanya fungsi runtime. --}}
    <script>
        function pencatatanData() {
            return {
                showEditModal: false, editUrl: '',
                formData: { id: '', tanggal: '', qty: 0, batch_no: '', expiry_date: '', doc_no: '', po_no: '', ship_address: '', expedition: '', keterangan: '', arah: '', barang: '', pihak: '' },
                openEditModal(item) {
                    this.formData = { id: item.id || '', tanggal: item.tanggal || '', qty: item.qty || 0, batch_no: item.batch_no || '', expiry_date: item.expiry_date || '', doc_no: item.doc_no || '', po_no: item.po_no || '', ship_address: item.ship_address || '', expedition: item.expedition || '', keterangan: item.keterangan || '', arah: item.arah || '', barang: item.barang || '', pihak: item.pihak || '' };
                    this.editUrl = `{{ url('warehouse/stock/pencatatan') }}/${item.id}`;
                    this.showEditModal = true;
                }
            }
        }

        function confirmDeletePencatatan(id) {            Swal.fire({
                title: 'Batalkan baris ini?', text: 'Stok akan dikembalikan via reversal. Jejak audit tetap tersimpan.',
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8', confirmButtonText: 'Ya, batalkan!', cancelButtonText: 'Batal'
            }).then((r) => { if (r.isConfirmed) document.getElementById('form-del-' + id).submit(); });
        }

        function confirmBulkKelola() {
            const ids = Array.from(document.querySelectorAll('.row-checkbox-kelola:checked')).map(cb => cb.value);
            if (ids.length === 0) return;
            Swal.fire({
                title: 'Batalkan ' + ids.length + ' baris terpilih?',
                text: 'Stok tiap baris dikembalikan via reversal. Jejak audit tetap tersimpan.',
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8', confirmButtonText: 'Ya, batalkan semua!', cancelButtonText: 'Batal'
            }).then((r) => {
                if (!r.isConfirmed) return;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("warehouse.mutation.bulk_destroy") }}';
                const csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                const method = document.createElement('input');
                method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE';
                form.appendChild(method);
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'ids[]'; input.value = id;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('check-all-kelola');
            const btnBulk = document.getElementById('btn-bulk-kelola');
            const countEl = document.getElementById('kelola-selected-count');
            if (checkAll && btnBulk) {
                const boxes = () => document.querySelectorAll('.row-checkbox-kelola');
                function refresh() {
                    const n = document.querySelectorAll('.row-checkbox-kelola:checked').length;
                    btnBulk.classList.toggle('hidden', n === 0);
                    if (countEl) countEl.textContent = n;
                    checkAll.checked = boxes().length > 0 && n === boxes().length;
                }
                checkAll.addEventListener('change', () => { boxes().forEach(cb => cb.checked = checkAll.checked); refresh(); });
                boxes().forEach(cb => cb.addEventListener('change', refresh));
            }
        });

        // ---- Popup kartu stock ----
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

        function openKartuModal(embedUrl, nama, kode, fullUrl) {
            const modal = document.getElementById('kartuModal');
            if (!modal) return;
            document.getElementById('kartuModalNama').textContent = nama || 'Kartu Stock';
            document.getElementById('kartuModalKode').textContent = kode || '';
            document.getElementById('kartuModalFull').href = fullUrl || '#';
            document.getElementById('kartuModalSaldo').textContent = '-';
            document.getElementById('kartuModalToast').classList.add('hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            loadKartuEmbed(embedUrl);
        }

        function closeKartuModal() {
            const modal = document.getElementById('kartuModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            kartuCurrentUrl = null;
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
                openKartuModal(card.dataset.embedUrl, card.dataset.nama, card.dataset.kode, card.dataset.fullUrl);
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

        // ---- Popup detail dokumen ----
        function openDocModal(docId) {
            const modal = document.getElementById('docModal');
            const body = document.getElementById('docModalBody');
            if (!modal || !body) return;
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
            document.getElementById('docModalNo').textContent = d.doc_no || '-';
            document.getElementById('docModalPihak').textContent = d.pihak || '-';
            document.getElementById('docModalMeta').textContent =
                (d.direction === 'in' ? 'Masuk' : 'Keluar') + ' · ' + d.mutation_date +
                ' · ' + (d.purpose || '-') + ' · ' + d.status + ' · oleh ' + d.creator;
            let html = '<div class="grid grid-cols-2 gap-2 text-xs">' +
                '<div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2"><div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PO / Referensi</div><div class="font-bold text-slate-700">' + (d.po_no || '-') + '</div></div>' +
                '<div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2"><div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Ekspedisi</div><div class="font-bold text-slate-700">' + (d.expedition || '-') + '</div></div>' +
                '<div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 col-span-2"><div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat</div><div class="font-bold text-slate-700">' + (d.ship_address || '-') + '</div></div>' +
                '</div>';
            html += '<h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Rincian Barang (' + d.lines.length + ')</h4>';
            html += '<div class="overflow-x-auto border border-slate-200 rounded-xl"><table class="w-full text-xs text-left whitespace-nowrap"><thead class="bg-slate-50 text-[10px] uppercase text-slate-500"><tr><th class="px-3 py-2">Barang</th><th class="px-3 py-2 text-right">Qty</th><th class="px-3 py-2">Batch</th><th class="px-3 py-2">Expired</th></tr></thead><tbody class="divide-y divide-slate-100">';
            d.lines.forEach((ln) => {
                html += '<tr class="hover:bg-slate-50"><td class="px-3 py-2"><div class="font-bold text-slate-800">' + ln.barang + '</div><div class="font-mono text-[10px] text-slate-400">' + ln.kode + '</div></td>' +
                    '<td class="px-3 py-2 text-right font-black">' + ln.qty + ' ' + ln.unit + '</td>' +
                    '<td class="px-3 py-2 font-mono">' + ln.batch_no + '</td>' +
                    '<td class="px-3 py-2">' + ln.expiry_date + '</td></tr>';
            });
            html += '</tbody></table></div>';
            document.getElementById('docModalBody').innerHTML = html;
        }

        document.getElementById('docModal').addEventListener('click', function (e) {
            if (e.target === this) closeDocModal();
        });
    </script>
    @endpush
@push('scripts')
{{-- Setelah simpan sukses: buang draft lokal form masuk/keluar agar tak basi. --}}
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

