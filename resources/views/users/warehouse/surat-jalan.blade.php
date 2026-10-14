<x-layout-users title="{{ $title ?? 'Surat Jalan' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }
        .mesh-bg {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0;
            background-color: #ede9fe;
            background-image:
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            pointer-events: none;
        }
        /* Arsip SJ: scroll internal tabel */
        .manage-table-scroll { overflow: auto; max-height: 62vh; min-height: 240px; }
        .manage-table-scroll table { border-collapse: separate; border-spacing: 0; }
        .manage-table-scroll thead th {
            position: sticky; top: 0; z-index: 20; background-color: #f8fafc;
            background-clip: padding-box;
            box-shadow: 0 1px 0 #e2e8f0, 0 6px 10px -6px rgba(15,23,42,.18);
        }
        /* Form SJ: Ekspedisi + Alamat selalu 1 baris (inline-grid mandiri, tanpa tergantung build Tailwind) */
        .sj-exp-addr {
            display: grid;
            grid-template-columns: minmax(110px, 1fr) minmax(0, 2fr);
            gap: 8px;
            align-items: end;
        }
        /* Form SJ: hanya tabel barang yang scroll, tinggi dibatasi agar halaman tidak memanjang */
        .sj-items-wrap { overflow: auto; max-height: 40vh; min-height: 140px; }
        .sj-items-wrap table { border-collapse: separate; border-spacing: 0; }
        .sj-items-wrap thead th {
            position: sticky; top: 0; z-index: 10; background-color: #f8fafc;
            background-clip: padding-box;
            box-shadow: 0 1px 0 #e2e8f0;
        }
        @media (min-width: 1024px) {
            .sj-items-wrap { max-height: 36vh; }
        }
        /* Textarea alamat: 1 baris, tinggi sejajar input */
        .addr-input {
            height: 30px !important;
            line-height: 28px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            white-space: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 relative overflow-x-clip text-slate-800">
        <div class="mesh-bg"></div>
        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 pt-3 pb-4 flex flex-col gap-3">

            <div class="w-full flex justify-start">
                <x-ui.back-button href="{{ route('warehouse.dashboard') }}" label="Back to Warehouse Dashboard" />
            </div>

            <x-ui.page-header title="Delivery Notes" subtitle="Record outgoing goods + delivery note archive & PDF." icon="fa-truck" :sticky="false" class="!p-3 md:!px-4 md:!py-3">
                <x-slot:controls>
                    <div class="flex space-x-1 bg-white/10 p-1 rounded-full border border-white/20 backdrop-blur-md overflow-x-auto shrink-0 w-full xl:w-auto relative z-10">
                        <a href="{{ route('warehouse.sj.index', ['tab' => 'keluar']) }}" class="px-3 py-1 text-xs rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab === 'keluar' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                            <i class="fas fa-arrow-up mr-1.5"></i> Form SJ
                        </a>
                        <a href="{{ route('warehouse.sj.index') }}" class="px-3 py-1 text-xs rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab !== 'keluar' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                            <i class="fas fa-file-pdf mr-1.5"></i> Surat Jalan
                        </a>
                    </div>
                </x-slot:controls>
            </x-ui.page-header>

            @if($tab === 'keluar')
            {{-- ==================== FORM SJ (berdiri sendiri, tanpa partial) ==================== --}}
            @if(!$canManage)
                <x-ui.glass-card class="border-t-4 border-t-red-400 text-sm text-slate-600">Akun Anda tidak memiliki akses input barang. Hubungi pengelola stok.</x-ui.glass-card>
            @else
            <x-ui.glass-card class="w-full border-t-4 border-t-blue-500 shadow-lg" style="border-radius:16px;padding:16px;">
                <form action="{{ route('warehouse.mutation.store') }}" method="POST" id="pencatatan-form" data-clearable>
                    @csrf
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

                    {{-- Baris 1: Tanggal | No. SJ | Keterangan | Customer | No. PO --}}
                    <div class="grid grid-cols-12 gap-2 items-end">
                        <div class="col-span-6 md:col-span-2">
                            <label class="ui-label">Tanggal Keluar <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal" required value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" class="ui-input-date" style="height:30px;font-size:12px;">
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <label class="ui-label">No. SJ</label>
                            <input type="text" value="{{ $sjPreviewNo ?? '' }}" readonly tabindex="-1" title="Nomor otomatis saat disimpan" class="ui-input ui-input--sm !bg-slate-100 !text-slate-500 cursor-not-allowed">
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <label class="ui-label">Keterangan <span class="text-red-500">*</span></label>
                            <input list="purpose-options" name="purpose" id="purpose-select" required maxlength="50" placeholder="pilih / ketik sendiri…" class="ui-input ui-input--sm" autocomplete="off">
                            <datalist id="purpose-options">
                                @foreach($purposes as $p)
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
                        </div>
                        <div class="col-span-12 md:col-span-3">
                            <label class="ui-label">No. PO / Referensi</label>
                            <input type="text" name="po_no" maxlength="100" placeholder="No. PO / referensi (opsional)" class="ui-input ui-input--sm" autocomplete="off">
                        </div>
                    </div>

                    {{-- Baris 2: Ekspedisi + Alamat Kirim dalam 1 baris --}}
                    <div class="mt-2 sj-exp-addr">
                        <div style="min-width:0;">
                            <label class="ui-label">Ekspedisi</label>
                            <input list="expedition-options" name="expedition" maxlength="100" placeholder="cth. JNE / GoSend (opsional)" class="ui-input ui-input--sm" autocomplete="off">
                            <datalist id="expedition-options">
                                @foreach($expeditions as $e)
                                    <option value="{{ $e }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div style="min-width:0;">
                            <label class="ui-label">Alamat Kirim</label>
                            <div class="relative">
                                <input type="text" name="ship_address" id="ship-address" maxlength="2000" placeholder="otomatis dari master, bisa diubah…" class="ui-input ui-input--sm" autocomplete="off">
                                <p id="addr-warn" class="hidden absolute left-0 z-10 text-[10px] text-amber-600 font-bold mt-0.5" style="top:100%"><i class="fas fa-exclamation-triangle mr-1"></i>Master belum punya alamat — ketik manual atau lengkapi di master.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Rincian barang --}}
                    <div class="mt-3 pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider whitespace-nowrap">Rincian Barang Keluar</h4>
                            <div class="flex-1 h-px bg-slate-100"></div>
                            <span id="sum-info" class="ui-info" style="height:26px;font-size:11px;">0 baris · total 0</span>
                        </div>

                        <div class="relative mt-2">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-blue-300 text-xs pointer-events-none"></i>
                                <input type="text" id="product-search" placeholder="Cari barang (nama / kode) — klik hasil untuk isi Qty, Batch & Exp…" title="Ketik nama / kode barang, lalu klik hasilnya untuk isi Qty, Batch & Exp lewat popup." class="ui-search !bg-white hover:!bg-white !text-slate-700" style="height:32px;padding-left:36px;" autocomplete="off">
                            </div>
                            <div id="product-search-results" class="hidden absolute left-0 right-0 z-30 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden max-h-64 overflow-y-auto divide-y divide-slate-100"></div>
                        </div>

                        {{-- Hanya tabel ini yang scroll (tinggi dibatasi .sj-items-wrap) --}}
                        <div class="mt-2 border border-slate-200 rounded-xl bg-white" style="overflow:hidden;">
                            <div class="sj-items-wrap">
                                <table class="w-full text-xs" style="min-width:760px;">
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
                            </div>
                            <div id="empty-barang" class="px-4 py-3 text-center bg-slate-50/50 border-t border-dashed border-slate-200">
                                <p class="text-xs font-bold text-slate-500"><i class="fas fa-box-open text-slate-300 mr-1.5"></i>Belum ada barang — cari di atas lalu klik untuk menambahkan.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Popup isi rincian barang (dipindah ke body via JS agar fixed tidak rusak) --}}
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
                                    <input type="number" id="m-qty" min="0.01" step="any" placeholder="0" class="ui-input !bg-white text-right font-bold !text-slate-800" style="height:38px;">
                                    <p id="m-batch-info" class="text-[10px] text-slate-400 font-medium mt-1.5 min-h-[14px]"></p>
                                </div>
                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <label class="ui-label">No. Batch</label>
                                        <input type="text" id="m-batch" list="m-batch-list" maxlength="100" placeholder="opsional" class="ui-input ui-input--sm !bg-white !text-slate-800" autocomplete="off">
                                        <datalist id="m-batch-list"></datalist>
                                    </div>
                                    <div>
                                        <label class="ui-label">Tgl Expired</label>
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

                    <div class="mt-3 pt-3 border-t border-slate-100 flex justify-end">
                        <button type="submit" id="simpan-mutasi-btn" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-5 rounded-lg shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all w-full sm:w-auto disabled:opacity-60 disabled:cursor-not-allowed" style="height:34px;">
                            <i class="fas fa-save text-[11px]"></i> Buat Surat Jalan
                        </button>
                    </div>
                </form>
            </x-ui.glass-card>
            @endif
            @else
            {{-- ==================== ARSIP SURAT JALAN (berdiri sendiri, tanpa partial) ==================== --}}
            <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg" style="background:#ffffff;">
                <form action="{{ $sjFilterAction ?? route('warehouse.sj.index') }}" method="GET" class="p-5 pb-4 border-b border-slate-100 bg-white">
                    @if(request('tab'))<input type="hidden" name="tab" value="{{ request('tab') }}">@endif
                    <div class="grid grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end">
                        <div class="lg:col-span-3 col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari SJ / customer / supplier</label>
                            <x-ui.search-input name="search" placeholder="cth. SJ-001 / RSUD / PT…" value="{{ request('search') }}" autocomplete="off" class="w-full" />
                        </div>
                        <div class="lg:col-span-2 col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tgl Mulai</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="ui-input ui-input--sm !bg-white w-full">
                        </div>
                        <div class="lg:col-span-2 col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tgl Selesai</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="ui-input ui-input--sm !bg-white w-full">
                        </div>
                        <div class="lg:col-span-3 col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Customer</label>
                            <select name="customer_id" onchange="this.form.submit()" class="ui-input ui-input--sm !bg-white w-full">
                                <option value="">Semua</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ (string) request('customer_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->customer_name }}</option>
                                @endforeach
                                <option value="__other" {{ request('customer_id') === '__other' ? 'selected' : '' }}>Bukan customer</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2 col-span-2 flex flex-row gap-2 items-end">
                            <button type="submit" formaction="{{ route('warehouse.sj.export.pdf') }}" class="ui-export ui-export--pdf flex-1 min-w-0" title="Export PDF rekap sesuai filter aktif">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                            <a href="{{ ($sjFilterAction ?? route('warehouse.sj.index')) . (request('tab') ? '?tab=' . request('tab') : '') }}" class="flex-1 min-w-0 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold h-[30px] px-2 rounded-lg text-xs transition-all inline-flex items-center justify-center border border-slate-200 shadow-sm leading-none" title="Reset filter"><i class="fas fa-undo mr-1 text-[11px]"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="flex-1 bg-white manage-table-scroll">
                    <table class="w-full text-sm text-left text-slate-600 bg-white">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                            <tr>
                                <th class="px-4 py-4 w-12 text-center">No</th>
                                <th class="px-4 py-4">Tanggal</th>
                                <th class="px-4 py-4">No. SJ</th>
                                <th class="px-4 py-4">Pihak</th>
                                <th class="px-4 py-4">Product</th>
                                <th class="px-4 py-4">Dibuat Oleh</th>
                                <th class="px-4 py-4 text-center">Cetak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($docs as $index => $d)
                                @php
                                    $pihak = $d->customer?->customer_name ?? $d->supplier?->supplier_name ?? 'Pemakaian Internal';
                                    $alamatPihak = $d->customer?->displayAddress() ?? $d->supplier?->displayAddress() ?? '-';
                                    $moves = $d->moves ?? collect();
                                @endphp
                                <tr class="hover:bg-blue-50/60 transition-colors">
                                    <td class="px-4 py-3 text-center text-slate-400 font-medium">{{ $docs->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700 text-xs">{{ $d->mutation_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-mono font-bold text-slate-800 text-xs">{{ $d->doc_no }}</div>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @if($d->purpose)<span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-bold">{{ $d->purpose }}</span>@endif
                                        </div>
                                        @if($d->po_no)<div class="text-[10px] text-slate-500 font-semibold mt-1">PO: {{ $d->po_no }}</div>@endif
                                        @if($d->expedition)<div class="text-[10px] text-slate-500 font-semibold mt-0.5">Ekspedisi: {{ $d->expedition }}</div>@endif
                                    </td>
                                    <td class="px-4 py-3 text-xs max-w-[220px]">
                                        <div class="font-bold text-slate-700 truncate" title="{{ $pihak }}">{{ $pihak }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium mt-0.5 leading-snug" title="{{ $alamatPihak }}">{{ \Illuminate\Support\Str::limit($alamatPihak, 60) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-xs max-w-[240px]">
                                        @forelse($moves->take(4) as $m)
                                            <div class="truncate" title="{{ $m->product->product_name_clean ?? $m->product->product_name ?? '-' }}">
                                                <span class="font-bold text-slate-700">{{ $m->product->product_name_clean ?? $m->product->product_name ?? '-' }}</span>
                                                <span class="text-slate-400 font-semibold">× {{ rtrim(rtrim(number_format((float) $m->qty, 2, '.', ''), '0'), '.') }} {{ $m->uom ?? $m->product->unit ?? '' }}</span>
                                            </div>
                                        @empty
                                            <span class="text-slate-300">—</span>
                                        @endforelse
                                        @if($moves->count() > 4)
                                            <div class="text-[10px] text-blue-600 font-bold mt-0.5">+{{ $moves->count() - 4 }} item lainnya</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600 font-semibold whitespace-nowrap">{{ $d->creator?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('warehouse.sj.print', $d->id) }}" class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 h-[30px] px-3 rounded-lg text-[11px] font-bold transition-colors whitespace-nowrap" title="Cetak / unduh PDF surat jalan">
                                            <i class="fas fa-print text-[10px]"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-0"><x-ui.empty icon="fa-file-pdf" title="Belum ada dokumen" message="Dokumen muncul otomatis setiap ada pencatatan barang masuk/keluar." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($docs->hasPages())
                <div class="px-6 py-3 border-t border-slate-200 bg-white flex items-center justify-between gap-3">
                    <div class="flex-1 flex justify-start">{{ $docs->links() }}</div>
                    <span class="text-[11px] text-slate-400 font-semibold whitespace-nowrap">Total Dokumen: <b class="text-slate-700">{{ number_format($docs->total(), 0, ',', '.') }}</b></span>
                </div>
                @else
                <div class="px-6 py-3 border-t border-slate-200 bg-white flex items-center justify-end">
                    <span class="text-[11px] text-slate-400 font-semibold whitespace-nowrap">Total Dokumen: <b class="text-slate-700">{{ number_format($docs->total(), 0, ',', '.') }}</b></span>
                </div>
                @endif
            </x-ui.glass-card>
            @endif
        </div>
    </div>

    @push('scripts')
    @if($tab === 'keluar')
    <script>
    // ---- Search + Modal + Tabel Form SJ ----
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('rows-container');
        const emptyBox = document.getElementById('empty-barang');
        const form = document.getElementById('pencatatan-form');
        if (!container || !form) return;
        const productOptions = {!! $productOptionsJson !!};
        const TAB_ARAH = 'keluar';
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
            // Internal = tanpa pihak: seluruh kolom partner disembunyikan (tanpa pengganti)
            if (partnerWrap) partnerWrap.classList.toggle('hidden', isInternal);
            partnerSel.disabled = isInternal;
            partnerSel.required = !isInternal;
            if (isInternal && shipInput) { shipInput.value = ''; }
            if (addrWarn && (isInternal || !partnerSel.value)) addrWarn.classList.add('hidden');
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
                    `<span class="min-w-0"><span class="block text-xs font-bold text-slate-800 truncate">${esc(p.nama)}</span>` +
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

        // restore draft (kunci draft sama seperti sebelumnya: pencatatan_draft_v3_keluar)
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
    @endif
    {{-- Mendarat di sini setelah sukses buat SJ: hapus draft form agar tak basi & tak duplikat. --}}
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
