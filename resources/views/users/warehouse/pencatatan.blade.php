<x-layout-users title="{{ $title ?? 'Pencatatan Barang' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .mesh-bg {
            background-color: #f8fafc;
            background-image:
                radial-gradient(at 0% 0%, rgba(255,255,255,0.6) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99,102,241,0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59,130,246,0.15) 0px, transparent 50%);
            background-attachment: fixed;
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
            position: sticky; top: 0; z-index: 20; background-color: #f8fafc;
            background-clip: padding-box;
            box-shadow: 0 1px 0 #e2e8f0, 0 6px 10px -6px rgba(15,23,42,.18);
        }
        @media (max-width: 767px) { .manage-table-scroll { max-height: 55vh; } }
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
    </style>
    @endpush

    <div class="mesh-bg flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800">
        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start" x-data="pencatatanData()">

            <div class="w-full flex justify-start">
                <x-ui.back-button href="{{ route('warehouse.dashboard') }}" label="Back to Warehouse Dashboard" />
            </div>

            {{-- Header desktop --}}
            <div class="hidden md:block">
                <x-ui.page-header title="Stock Mutation" subtitle="Manage data, incoming goods & stock cards — auto-synced." icon="fa-clipboard-list" :sticky="false">
                    <x-slot:controls>
                        <div class="flex space-x-1 bg-white/10 p-1 rounded-full border border-white/20 backdrop-blur-md overflow-x-auto shrink-0 w-full xl:w-auto relative z-10">
                            <a href="{{ route('warehouse.mutation.index', array_merge(request()->except(['tab', 'page', 'kelolaPage']), ['tab' => 'kelola'])) }}" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab === 'kelola' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-table mr-2"></i> Kelola Data
                            </a>
                            <a href="{{ route('warehouse.mutation.index', array_merge(request()->except(['tab', 'page', 'kelolaPage']), ['tab' => 'masuk'])) }}" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab === 'masuk' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-arrow-down mr-2"></i> Barang Masuk
                            </a>
                            <a href="{{ route('warehouse.mutation.index', array_merge(request()->except(['tab', 'page', 'kelolaPage']), ['tab' => 'kartu'])) }}" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $tab === 'kartu' ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                                <i class="fas fa-id-card-clip mr-2"></i> Kartu Stock
                            </a>
                        </div>
                    </x-slot:controls>
                </x-ui.page-header>
            </div>
            {{-- Header mobile --}}
            <div class="md:hidden space-y-3">
                <div class="page-header-mobile flex items-center justify-between gap-3">
                    <div class="relative z-10 min-w-0">
                        <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Stock Mutation</h2>
                        <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">Data, incoming & cards.</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 relative z-10"><i class="fas fa-clipboard-list"></i></div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-1.5 grid grid-cols-3 gap-1">
                    <a href="{{ route('warehouse.mutation.index', array_merge(request()->except(['tab', 'page', 'kelolaPage']), ['tab' => 'kelola'])) }}" class="flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold {{ $tab === 'kelola' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500' }}">
                        <i class="fas fa-table text-sm"></i><span class="text-[10px] uppercase">Kelola</span>
                    </a>
                    <a href="{{ route('warehouse.mutation.index', array_merge(request()->except(['tab', 'page', 'kelolaPage']), ['tab' => 'masuk'])) }}" class="flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold {{ $tab === 'masuk' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500' }}">
                        <i class="fas fa-arrow-down text-sm"></i><span class="text-[10px] uppercase">Masuk</span>
                    </a>
                    <a href="{{ route('warehouse.mutation.index', array_merge(request()->except(['tab', 'page', 'kelolaPage']), ['tab' => 'kartu'])) }}" class="flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold {{ $tab === 'kartu' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500' }}">
                        <i class="fas fa-id-card-clip text-sm"></i><span class="text-[10px] uppercase">Kartu</span>
                    </a>
                </div>
            </div>

            @if($tab === 'masuk')
            @include('users.warehouse.partials.mutation-form')
            @elseif($tab === 'kelola')
            {{-- Kelola Data: semua yang diinput muncul di sini (ala Manage Sales) --}}
            <div class="space-y-4 flex flex-col w-full">
                {{-- Statistik ringkas hasil filter --}}
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
                        <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400">Net (masuk − keluar)</div>
                        <div class="text-2xl font-black leading-tight {{ ($qtyMasuk - $qtyKeluar) >= 0 ? 'text-slate-800' : 'text-red-600' }}">{{ number_format($qtyMasuk - $qtyKeluar, 0, ',', '.') }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">{{ $trxMasuk + $trxKeluar }} total baris</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl px-4 py-3 shadow-sm">
                        <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400">Barang tersentuh</div>
                        <div class="text-2xl font-black text-slate-800 leading-tight">{{ $ringkasan->count() }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">SKU pada filter ini</div>
                    </div>
                </div>

                {{-- Ringkasan per barang --}}
                <x-ui.glass-card padding="none" class="!p-0 overflow-clip w-full !rounded-3xl !bg-white border border-slate-200 shadow-lg border-t-4 border-t-blue-500">
                    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 leading-tight">Ringkasan per Barang</h3>
                            <p class="text-[11px] text-slate-500 font-medium">Masuk · Keluar · Net · Stok kini (mengikuti filter Kelola Data di bawah).</p>
                        </div>
                        <span class="text-[11px] font-bold text-slate-400 bg-slate-100 rounded-lg px-2.5 py-1">{{ $ringkasan->count() }} SKU</span>
                    </div>
                    <div class="overflow-x-auto max-h-[260px] overflow-y-auto">
                        <table class="w-full min-w-[720px] text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <tr><th class="py-2.5 px-4">Barang</th><th class="py-2.5 px-3 text-right">Masuk</th><th class="py-2.5 px-3 text-right">Keluar</th><th class="py-2.5 px-3 text-right">Net</th><th class="py-2.5 px-3 text-right">Stok Kini</th><th class="py-2.5 px-3 text-center">Kartu</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($ringkasan as $r)
                                <tr class="hover:bg-blue-50/60">
                                    <td class="py-2 px-4">
                                        <div class="font-bold text-xs text-slate-800">{{ $r['nama'] }}</div>
                                        <div class="font-mono text-[10px] text-slate-400">{{ $r['kode'] }} · {{ $r['trx'] }} trx</div>
                                    </td>
                                    <td class="py-2 px-3 text-right font-bold text-blue-600 text-xs">+{{ number_format($r['masuk'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 text-right font-bold text-red-600 text-xs">-{{ number_format($r['keluar'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 text-right font-black text-xs">{{ number_format($r['net'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 text-right font-black text-xs">{{ number_format($r['stok_kini'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 text-center">
                                        <a href="{{ route('warehouse.stockcard.show', $r['id']) }}" class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200" title="Buka kartu stock"><i class="fas fa-id-card-clip text-xs"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="py-5 text-center text-xs text-slate-400 font-medium">Belum ada mutasi pada filter ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.glass-card>

                {{-- Tabel Kelola Data (filter + bulk + aksi) --}}
                <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg" style="background:#ffffff;">
                    <form action="{{ route('warehouse.mutation.index') }}" method="GET" id="filterFormKelola" class="p-5 pb-4 border-b border-slate-100 bg-white">
                        <input type="hidden" name="tab" value="kelola">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end">
                            <div class="lg:col-span-4">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari barang / batch / keterangan</label>
                                <x-ui.search-input name="search" placeholder="cth. kasa / B-2026 / supplier X" value="{{ request('search') }}" autocomplete="off" class="w-full" :debounce="500" />
                            </div>
                            <div class="lg:col-span-3">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Barang</label>
                                <select name="product_id" onchange="document.getElementById('filterFormKelola').submit()" class="ui-input ui-input--sm !bg-white w-full">
                                    <option value="">Semua barang</option>
                                    @foreach($productOptions as $p)
                                        <option value="{{ $p->id }}" {{ (string) request('product_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->product_name_clean ?: $p->product_name }} (stok {{ $p->stock }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Arah</label>
                                <select name="kelola_arah" onchange="document.getElementById('filterFormKelola').submit()" class="ui-input ui-input--sm !bg-white w-full">
                                    <option value="">Masuk + Keluar</option>
                                    <option value="masuk" {{ request('kelola_arah') === 'masuk' ? 'selected' : '' }}>Masuk saja</option>
                                    <option value="keluar" {{ request('kelola_arah') === 'keluar' ? 'selected' : '' }}>Keluar saja</option>
                                </select>
                            </div>
                            <div class="lg:col-span-3">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal</label>
                                <x-ui.filter-date name="tanggal" value="{{ request('tanggal') }}" onchange="document.getElementById('filterFormKelola').submit()" class="w-full" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end mt-3">
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                                <select name="bulan" onchange="document.getElementById('filterFormKelola').submit()" class="ui-input ui-input--sm !bg-white w-full">
                                    <option value="">Semua</option>
                                    @for($b = 1; $b <= 12; $b++)
                                        <option value="{{ $b }}" {{ (string) request('bulan') === (string) $b ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $b, 1)->format('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
                                <select name="tahun" onchange="document.getElementById('filterFormKelola').submit()" class="ui-input ui-input--sm !bg-white w-full">
                                    <option value="">Semua</option>
                                    @foreach($tahunAda as $t)
                                        <option value="{{ $t }}" {{ (string) request('tahun') === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="lg:col-span-5 flex items-end">
                                <x-ui.info class="w-full" icon="fa-database" label="Total Baris:" :value="number_format($kelolaMoves->total(), 0, ',', '.')" />
                            </div>
                            <div class="lg:col-span-3 flex items-end">
                                @if(request()->hasAny(['search', 'product_id', 'kelola_arah', 'tanggal', 'bulan', 'tahun']))
                                    <a href="{{ route('warehouse.mutation.index', ['tab' => 'kelola']) }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center border border-slate-200 shadow-sm leading-none"><i class="fas fa-undo mr-1.5 text-[11px]"></i> Reset Filter</a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="flex-1 bg-white manage-table-scroll">
                        <table class="w-full text-sm text-left text-slate-600 bg-white">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                                <tr>
                                    <th class="px-4 py-4 w-10 text-center">
                                        <input type="checkbox" id="check-all-kelola" class="rounded border-slate-300 text-blue-600 shadow-sm cursor-pointer">
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
                                            <input type="checkbox" value="{{ $m->id }}" class="row-checkbox-kelola rounded border-slate-300 text-blue-600 shadow-sm cursor-pointer">
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
            @elseif($tab === 'kartu')
            {{-- Kartu Stock dalam halaman yang sama --}}
            <div class="space-y-4 flex flex-col w-full">
                <x-ui.glass-card class="w-full border-t-4 border-t-cyan-500 shadow-lg !rounded-3xl !p-5">
                    <form action="{{ route('warehouse.mutation.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <input type="hidden" name="tab" value="kartu">
                        <div class="md:col-span-6">
                            <label class="ui-label">Pilih Barang</label>
                            <select name="kartu_product" class="ui-input !bg-white w-full" onchange="this.form.submit()">
                                <option value="">— pilih barang untuk melihat kartunya —</option>
                                @foreach($productOptions as $p)
                                    <option value="{{ $p->id }}" {{ (string) request('kartu_product') === (string) $p->id ? 'selected' : '' }}>{{ $p->product_name_clean ?: $p->product_name }} (stok {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="ui-label">Dari</label>
                            <input type="date" lang="id-ID" name="kartu_from" value="{{ request('kartu_from', today()->startOfMonth()->toDateString()) }}" class="ui-input-date" onchange="this.form.submit()">
                        </div>
                        <div class="md:col-span-2">
                            <label class="ui-label">Sampai</label>
                            <input type="date" lang="id-ID" name="kartu_to" value="{{ request('kartu_to', today()->toDateString()) }}" class="ui-input-date" onchange="this.form.submit()">
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            @if($kartuProduct)
                                <a href="{{ route('warehouse.stockcard.show', $kartuProduct) }}" class="w-full bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200 font-bold h-[34px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center gap-1.5"><i class="fas fa-expand text-[11px]"></i> Halaman Penuh</a>
                            @endif
                        </div>
                    </form>
                </x-ui.glass-card>

                @if($kartuData && $kartuProduct)
                    @include('users.warehouse.card-detail', $kartuData)
                    @include('users.warehouse.card-edit-modal')
                @else
                    <x-ui.glass-card class="w-full text-center !py-10">
                        <div class="w-12 h-12 rounded-2xl bg-cyan-50 border border-cyan-100 text-cyan-500 flex items-center justify-center mx-auto mb-3"><i class="fas fa-id-card-clip"></i></div>
                        <h3 class="font-bold text-slate-700 text-sm">Pilih barang di atas</h3>
                        <p class="text-xs text-slate-400 font-medium mt-1">Kartu stock (Tanggal · Batch · Masuk · Keluar · Sisa) tampil langsung di sini.</p>
                    </x-ui.glass-card>
                @endif
            </div>
            @endif

            {{-- Modal edit --}}
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
                                         <div><label class="ui-label">Tanggal</label><input type="date" lang="id-ID" name="tanggal" x-model="formData.tanggal" max="{{ today()->toDateString() }}" class="ui-input ui-input--sm"></div>
                                         <div><label class="ui-label">Qty</label><input type="number" name="qty" x-model="formData.qty" min="0.01" step="any" class="ui-input ui-input--sm"></div>
                                         <div><label class="ui-label">No. Surat Jalan</label><input type="text" name="doc_no" x-model="formData.doc_no" maxlength="100" class="ui-input ui-input--sm"></div>
                                         <div><label class="ui-label">No. PO / Referensi</label><input type="text" name="po_no" x-model="formData.po_no" maxlength="100" class="ui-input ui-input--sm"></div>
                                         <div><label class="ui-label">Ekspedisi</label><input type="text" name="expedition" x-model="formData.expedition" maxlength="100" class="ui-input ui-input--sm"></div>
                                         <div><label class="ui-label">Alamat Kirim</label><input type="text" name="ship_address" x-model="formData.ship_address" maxlength="2000" class="ui-input ui-input--sm"></div>
                                         <div><label class="ui-label">Batch No.</label><input type="text" name="batch_no" x-model="formData.batch_no" maxlength="100" class="ui-input ui-input--sm"></div>
                                         <div><label class="ui-label">Expired Date</label><input type="date" lang="id-ID" name="expiry_date" x-model="formData.expiry_date" class="ui-input ui-input--sm"></div>
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

        </div>
    </div>

    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        function confirmDeletePencatatan(id) {
            Swal.fire({
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

        // Checkbox bulk tab Kelola
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('check-all-kelola');
            const btnBulk = document.getElementById('btn-bulk-kelola');
            const countEl = document.getElementById('kelola-selected-count');
            if (!checkAll || !btnBulk) return;
            const boxes = () => document.querySelectorAll('.row-checkbox-kelola');
            function refresh() {
                const n = document.querySelectorAll('.row-checkbox-kelola:checked').length;
                btnBulk.classList.toggle('hidden', n === 0);
                if (countEl) countEl.textContent = n;
                checkAll.checked = boxes().length > 0 && n === boxes().length;
            }
            checkAll.addEventListener('change', () => { boxes().forEach(cb => cb.checked = checkAll.checked); refresh(); });
            boxes().forEach(cb => cb.addEventListener('change', refresh));
    </script>
    @endpush
</x-layout-users>
