<x-layout-users title="Inventory Command Center">
    @php
        $backRoute = route('warehouse.dashboard');
        $tabs = ['overview' => 'Overview', 'stok' => 'Stok', 'operations' => 'Operations', 'opname' => 'Opname', 'replenishment' => 'Replenishment', 'laporan' => 'Laporan'];
        $tabIcons = ['overview' => 'fa-table-cells-large', 'stok' => 'fa-boxes-stacked', 'operations' => 'fa-arrow-right-arrow-left', 'opname' => 'fa-clipboard-check', 'replenishment' => 'fa-cart-plus', 'laporan' => 'fa-file-lines'];
        $currentTab = $tab ?? 'overview';
        $typeLabels = ['receipt' => 'Penerimaan', 'delivery' => 'Pengeluaran', 'transfer' => 'Transfer', 'adjustment' => 'Penyesuaian', 'scrap' => 'Scrap'];
        $statusLabels = ['waiting' => 'Menunggu', 'ready' => 'Siap', 'done' => 'Selesai', 'cancelled' => 'Batal'];
        $tabUrl = fn($key) => route('warehouse.inventory', array_merge(request()->except(['tab', 'stockPage', 'movePage']), ['tab' => $key]));
    @endphp

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
            background-attachment: fixed;
            pointer-events: none;
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
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800">
        <div class="mesh-bg"></div>
        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start">

            <div class="w-full flex justify-start mb-3 md:mb-4">
                <x-ui.back-button :href="$backRoute" label="Back to Warehouse Dashboard" />
            </div>

            {{-- Header desktop — tab pill di dalam page-header ala sales manage --}}
            <div class="hidden md:block">
                <x-ui.page-header title="Inventory Command Center" subtitle="Stok, mutasi, opname, dan replenishment dalam satu tempat — ala Odoo." icon="fa-warehouse" :sticky="false">
                    <x-slot:controls>
                        <div class="flex space-x-1 bg-white/10 p-1 rounded-full border border-white/20 backdrop-blur-md overflow-x-auto shrink-0 w-full xl:w-auto relative z-10">
                            @foreach($tabs as $key => $label)
                                <a href="{{ $tabUrl($key) }}" class="px-3 py-1.5 text-xs rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center {{ $currentTab === $key ? 'bg-white text-blue-700 shadow-md' : 'text-white hover:bg-white/20' }}">
                                    <i class="fas {{ $tabIcons[$key] }} mr-1.5"></i> {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </x-slot:controls>
                </x-ui.page-header>
            </div>
            {{-- Header mobile --}}
            <div class="md:hidden space-y-3">
                <div class="page-header-mobile flex items-center justify-between gap-3">
                    <div class="relative z-10 min-w-0">
                        <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Inventory Command Center</h2>
                        <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">Stok, mutasi & opname.</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 relative z-10"><i class="fas fa-warehouse"></i></div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-1.5 grid grid-cols-3 gap-1">
                    @foreach($tabs as $key => $label)
                        <a href="{{ $tabUrl($key) }}" class="flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold {{ $currentTab === $key ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500' }}">
                            <i class="fas {{ $tabIcons[$key] }} text-sm"></i><span class="text-[10px] uppercase">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- KPI strip --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white border border-slate-200 rounded-2xl px-4 py-3 shadow-sm">
                    <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400">Total SKU Aktif</div>
                    <div class="text-2xl font-black text-slate-800 leading-tight">{{ number_format($totalSku ?? 0) }}</div>
                </div>
                <div class="bg-white border rounded-2xl px-4 py-3 shadow-sm {{ ($stokHabis + $menipis) > 0 ? 'border-red-200' : 'border-slate-200' }}">
                    <div class="text-[10px] font-bold tracking-[0.12em] uppercase {{ ($stokHabis + $menipis) > 0 ? 'text-red-500' : 'text-slate-400' }}">Stok Kritis</div>
                    <div class="text-2xl font-black leading-tight {{ ($stokHabis + $menipis) > 0 ? 'text-red-600' : 'text-slate-800' }}">{{ number_format(($stokHabis ?? 0) + ($menipis ?? 0)) }}</div>
                </div>
                <div class="bg-white border rounded-2xl px-4 py-3 shadow-sm {{ ($expired30 ?? 0) > 0 ? 'border-amber-200' : 'border-slate-200' }}">
                    <div class="text-[10px] font-bold tracking-[0.12em] uppercase {{ ($expired30 ?? 0) > 0 ? 'text-amber-500' : 'text-slate-400' }}">Expired ≤ 30 Hari</div>
                    <div class="text-2xl font-black leading-tight {{ ($expired30 ?? 0) > 0 ? 'text-amber-600' : 'text-slate-800' }}">{{ number_format($expired30 ?? 0) }}</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl px-4 py-3 shadow-sm">
                    <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400">Menunggu Validasi</div>
                    <div class="text-2xl font-black text-slate-800 leading-tight">{{ number_format($movesWaiting ?? 0) }}</div>
                </div>
            </div>

            {{-- ================= TAB OVERVIEW ================= --}}
            @if($currentTab === 'overview')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg !rounded-3xl !p-6 !bg-white">
                        <h3 class="text-sm font-bold text-slate-800 mb-1">Perlu Perhatian</h3>
                        <p class="text-[11px] text-slate-500 font-medium mb-3">SKU di bawah min-stock dan expired terdekat.</p>
                        @if($belowMin->isEmpty() && $expiredBatches->isEmpty())
                            <p class="text-xs text-slate-400 font-medium">Semua aman. Tidak ada SKU kritis.</p>
                        @endif
                        <div class="space-y-2">
                            @foreach($belowMin->take(5) as $p)
                                <div class="flex items-center justify-between gap-2 text-xs bg-red-50/60 border border-red-100 rounded-xl px-3 py-2">
                                    <span class="font-bold text-slate-700 truncate">{{ $p->product_name }}</span>
                                    <span class="font-black text-red-600 shrink-0">stok {{ $p->stock }} &lt; min {{ $p->min_stock }}</span>
                                </div>
                            @endforeach
                            @foreach($expiredBatches->take(5) as $b)
                                <div class="flex items-center justify-between gap-2 text-xs bg-amber-50/60 border border-amber-100 rounded-xl px-3 py-2">
                                    <span class="font-bold text-slate-700 truncate">{{ $b->product->product_name ?? '-' }}</span>
                                    <span class="font-black text-amber-600 shrink-0">exp {{ $b->expiry_date?->format('d M Y') }} ({{ $b->qty_current }})</span>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.glass-card>
                    <x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-cyan-500 shadow-lg !rounded-3xl !p-6 !bg-white">
                        <h3 class="text-sm font-bold text-slate-800 mb-1">Mutasi Terakhir</h3>
                        <p class="text-[11px] text-slate-500 font-medium mb-3">5 dokumen mutasi terbaru.</p>
                        @if($moves->isEmpty())
                            <p class="text-xs text-slate-400 font-medium">Belum ada mutasi. Buat dokumen pertama di tab Operations.</p>
                        @endif
                        <div class="space-y-2">
                            @foreach($moves->take(5) as $m)
                                <div class="flex items-center justify-between gap-2 text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <span class="font-bold text-slate-700 truncate">#{{ $m->id }} · {{ $m->product->product_name ?? '-' }} · {{ $m->qty }}</span>
                                    <span class="font-bold text-blue-700 shrink-0">{{ $statusLabels[$m->status] ?? $m->status }}</span>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('warehouse.inventory', ['tab' => 'operations']) }}" class="inline-flex items-center gap-1.5 mt-3 text-xs font-bold text-blue-600 hover:underline">Buka Operations <i class="fas fa-arrow-right text-[10px]"></i></a>
                    </x-ui.glass-card>
                </div>
            @endif

            {{-- ================= TAB STOK ================= --}}
            @if($currentTab === 'stok')
                <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full max-w-full mx-auto !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg" style="background:#ffffff;">
                    <div class="p-5 pb-4 border-b border-slate-100 bg-white">
                        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 leading-tight">Stok per Produk</h3>
                                <p class="text-[11px] text-slate-500 font-medium leading-tight">Atur min-stock langsung dari tabel.</p>
                            </div>
                            <form method="GET" action="{{ route('warehouse.inventory') }}" class="flex items-end gap-2 w-full sm:w-auto">
                                <input type="hidden" name="tab" value="stok">
                                <div class="flex-1 sm:w-64">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari kode / nama</label>
                                    <x-ui.search-input name="search" value="{{ $search }}" placeholder="Cari kode / nama..." class="w-full" />
                                </div>
                                @if(request()->filled('search'))
                                    <a href="{{ route('warehouse.inventory', ['tab' => 'stok']) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center border border-slate-200 shadow-sm leading-none" title="Reset filter"><i class="fas fa-undo mr-1.5 text-[11px]"></i> Reset</a>
                                @endif
                            </form>
                        </div>
                    </div>
                    <div class="flex-1 bg-white manage-table-scroll">
                        <table class="w-full min-w-[860px] text-sm text-left text-slate-600 bg-white">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                                <tr><th class="px-4 py-4">Kode</th><th class="px-4 py-4">Nama</th><th class="px-4 py-4 text-right">Stok</th><th class="px-4 py-4 text-right">PO</th><th class="px-4 py-4 text-right">Min</th><th class="px-4 py-4">Expired Terdekat</th><th class="px-4 py-4 text-center">Status</th><th class="px-4 py-4 text-center">Aksi</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($products as $p)
                                    @php
                                        $isKritis = $p->min_stock !== null && $p->stock <= $p->min_stock;
                                        $isHabis = $p->stock <= 0;
                                        $exp = $p->batches->first()?->expiry_date;
                                    @endphp
                                    <tr class="hover:bg-blue-50/60 transition-colors">
                                        <td class="px-4 py-3 font-mono text-xs">{{ $p->product_code }}</td>
                                        <td class="px-4 py-3 font-bold text-xs text-slate-800 truncate max-w-[220px]">{{ $p->product_name }}</td>
                                        <td class="px-4 py-3 text-right font-black text-slate-800">{{ $p->stock }}</td>
                                        <td class="px-4 py-3 text-right text-xs">{{ $p->stock_po }}</td>
                                        <td class="px-4 py-3 text-right text-xs">{{ $p->min_stock ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs">{{ $exp?->format('d M Y') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($isHabis)<span class="text-[10px] font-bold text-white bg-red-500 rounded-full px-2 py-0.5">HABIS</span>
                                            @elseif($isKritis)<span class="text-[10px] font-bold text-red-600 bg-red-50 border border-red-200 rounded-full px-2 py-0.5">MENIPIS</span>
                                            @else<span class="text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-200 rounded-full px-2 py-0.5">AMAN</span>@endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($canManage)
                                                <form method="POST" action="{{ route('warehouse.inventory.min-stock.update', $p) }}" class="inline-flex items-center gap-1">
                                                    @csrf @method('PUT')
                                                    <input type="number" name="min_stock" min="0" step="any" value="{{ $p->min_stock }}" placeholder="Min" class="w-20 h-[30px] text-xs border border-slate-200 rounded-lg px-2 outline-none focus:border-blue-500">
                                                    <button class="h-[30px] px-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold" title="Simpan min-stock"><i class="fas fa-check"></i></button>
                                                </form>
                                            @else
                                                <span class="text-[11px] text-slate-300">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="p-0"><x-ui.empty icon="fa-boxes-stacked" title="Tidak ada produk" message="Coba ubah kata kunci pencarian." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3 border-t border-slate-200 bg-white flex items-center justify-between gap-3">
                        <div class="flex-1 flex justify-start">{{ $products->links() }}</div>
                        <span class="text-[11px] text-slate-400 font-semibold whitespace-nowrap">Total Produk: <b class="text-slate-700">{{ number_format($products->total(), 0, ',', '.') }}</b></span>
                    </div>
                </x-ui.glass-card>
            @endif

            {{-- ================= TAB OPERATIONS ================= --}}
            @if($currentTab === 'operations')
                <div class="space-y-4 flex flex-col w-full">
                    @if($canManage)
                        <x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg !rounded-3xl !p-6 !bg-white">
                            <h3 class="text-sm font-bold text-slate-800 mb-1">Buat Dokumen Mutasi</h3>
                            <p class="text-[11px] text-slate-500 font-medium mb-4">Dokumen berstatus Menunggu — stok berubah setelah divalidasi.</p>
                            @if ($errors->any())
                                <div class="mb-3 bg-red-50 border border-red-200 text-red-700 p-2.5 rounded-xl text-xs" role="alert">
                                    <p class="font-bold mb-0.5 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Gagal menyimpan</p>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px] font-semibold">
                                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                                    </ul>
                                </div>
                            @endif
                            <form method="POST" action="{{ route('warehouse.inventory.moves.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end">
                                @csrf
                                <div class="lg:col-span-4 md:col-span-2">
                                    <label class="ui-label">Produk <span class="text-red-500">*</span></label>
                                    <select name="product_id" required class="ui-input ui-input--sm !bg-white w-full"><option value="">— pilih produk —</option>@foreach($productOptions as $o)<option value="{{ $o->id }}">{{ $o->product_code }} — {{ $o->product_name }}</option>@endforeach</select>
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="ui-label">Tipe <span class="text-red-500">*</span></label>
                                    <select name="type" required class="ui-input ui-input--sm !bg-white w-full"><option value="receipt">Penerimaan</option><option value="delivery">Pengeluaran</option><option value="transfer">Transfer</option><option value="adjustment">Penyesuaian</option><option value="scrap">Scrap</option></select>
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="ui-label">Qty <span class="text-red-500">*</span></label>
                                    <input type="number" name="qty" min="0.01" step="any" required placeholder="0" class="ui-input ui-input--sm !bg-white w-full">
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="ui-label">Expired (receipt)</label>
                                    <input type="date" name="expiry_date" class="ui-input ui-input--sm !bg-white w-full">
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="ui-label">Alasan / Catatan</label>
                                    <input type="text" name="reason" placeholder="Opsional" maxlength="1000" class="ui-input ui-input--sm !bg-white w-full" autocomplete="off">
                                </div>
                                <div class="lg:col-span-12 flex justify-end">
                                    <button class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold h-[34px] px-5 rounded-lg shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all w-full sm:w-auto"><i class="fas fa-plus text-[11px]"></i> Buat Dokumen</button>
                                </div>
                            </form>
                        </x-ui.glass-card>
                    @endif

                    <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full max-w-full mx-auto !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg" style="background:#ffffff;">
                        <form method="GET" action="{{ route('warehouse.inventory') }}" class="p-5 pb-4 border-b border-slate-100 bg-white">
                            <input type="hidden" name="tab" value="operations">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end">
                                <div class="lg:col-span-3 md:col-span-1">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                                    <x-ui.filter-select name="mstatus" onchange="this.form.submit()" value="{{ request('mstatus') }}" placeholder="Semua status" :options="$statusLabels" class="w-full" />
                                </div>
                                <div class="lg:col-span-3 md:col-span-1">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tipe</label>
                                    <x-ui.filter-select name="mtype" onchange="this.form.submit()" value="{{ request('mtype') }}" placeholder="Semua tipe" :options="$typeLabels" class="w-full" />
                                </div>
                                <div class="lg:col-span-3 md:col-span-1 flex items-end">
                                    <x-ui.info class="w-full" icon="fa-file-lines" label="Total Dokumen:" :value="number_format(method_exists($moves, 'total') ? $moves->total() : $moves->count(), 0, ',', '.')" />
                                </div>
                                <div class="lg:col-span-3 md:col-span-1 flex items-end">
                                    @if(request()->hasAny(['mstatus', 'mtype']))
                                        <a href="{{ route('warehouse.inventory', ['tab' => 'operations']) }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center border border-slate-200 shadow-sm leading-none"><i class="fas fa-undo mr-1.5 text-[11px]"></i> Reset Filter</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                        <div class="flex-1 bg-white manage-table-scroll">
                            <table class="w-full min-w-[900px] text-sm text-left text-slate-600 bg-white">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                                    <tr><th class="px-4 py-4">ID</th><th class="px-4 py-4">Produk</th><th class="px-4 py-4">Tipe</th><th class="px-4 py-4 text-right">Qty</th><th class="px-4 py-4">Dari → Ke</th><th class="px-4 py-4">Oleh</th><th class="px-4 py-4 text-center">Status</th><th class="px-4 py-4 text-center">Aksi</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($moves as $m)
                                        <tr class="hover:bg-blue-50/60 transition-colors">
                                            <td class="px-4 py-3 font-mono text-xs">#{{ $m->id }}</td>
                                            <td class="px-4 py-3 font-bold text-xs text-slate-800 truncate max-w-[220px]">{{ $m->product->product_name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-xs">{{ $typeLabels[$m->type] ?? $m->type }}</td>
                                            <td class="px-4 py-3 text-right font-black text-slate-800">{{ $m->qty }}</td>
                                            <td class="px-4 py-3 text-xs">{{ $m->from_location }} → {{ $m->to_location }}</td>
                                            <td class="px-4 py-3 text-xs">{{ $m->user->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-center text-xs font-bold">{{ $statusLabels[$m->status] ?? $m->status }}</td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                @if($canManage && in_array($m->status, ['waiting', 'ready']))
                                                    <form action="{{ route('warehouse.inventory.moves.validate', $m) }}" method="POST" class="inline" onsubmit="confirmSubmit(event, 'Validasi mutasi #{{ $m->id }}? Stok akan diperbarui.');">@csrf<button class="h-[30px] px-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold">Validasi</button></form>
                                                    <form action="{{ route('warehouse.inventory.moves.cancel', $m) }}" method="POST" class="inline" onsubmit="confirmSubmit(event, 'Batalkan mutasi #{{ $m->id }}?');">@csrf<button class="h-[30px] px-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-bold">Batal</button></form>
                                                @elseif($canManage && $m->status === 'done')
                                                    <form action="{{ route('warehouse.inventory.moves.reverse', $m) }}" method="POST" class="inline" onsubmit="confirmSubmit(event, 'Reversal mutasi #{{ $m->id }} via adjustment?');">@csrf<button class="h-[30px] px-3 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-[11px] font-bold">Reversal</button></form>
                                                @else
                                                    <span class="text-[11px] text-slate-300">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="p-0"><x-ui.empty icon="fa-file-lines" title="Belum ada dokumen mutasi" message="Buat dokumen pertama dari form di atas, lalu validasi untuk menerapkan ke stok." /></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-3 border-t border-slate-200 bg-white flex items-center justify-between gap-3">
                            <div class="flex-1 flex justify-start">@if(method_exists($moves, 'links')){{ $moves->links() }}@endif</div>
                            <span class="text-[11px] text-slate-400 font-semibold whitespace-nowrap">Total Dokumen: <b class="text-slate-700">{{ number_format(method_exists($moves, 'total') ? $moves->total() : $moves->count(), 0, ',', '.') }}</b></span>
                        </div>
                    </x-ui.glass-card>
                </div>
            @endif

            {{-- ================= TAB OPNAME ================= --}}
            @if($currentTab === 'opname')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg !rounded-3xl !p-6 !bg-white">
                        <h3 class="text-sm font-bold text-slate-800 mb-1">Stock Opname</h3>
                        <p class="text-[11px] text-slate-500 font-medium mb-3">Input hasil hitung fisik — selisih otomatis jadi adjustment tervalidasi.</p>
                        @if($canManage)
                            <form method="POST" action="{{ route('warehouse.stockopname.store') }}" class="grid grid-cols-2 gap-3">
                                @csrf
                                <div class="col-span-2"><label class="ui-label">Produk <span class="text-red-500">*</span></label>
                                    <select name="product_id" required class="ui-input ui-input--sm !bg-white w-full"><option value="">— pilih produk —</option>@foreach($productOptions as $o)<option value="{{ $o->id }}">{{ $o->product_code }} — {{ $o->product_name }}</option>@endforeach</select></div>
                                <div><label class="ui-label">Hasil Hitung <span class="text-red-500">*</span></label><input type="number" name="counted_qty" min="0" step="any" required class="ui-input ui-input--sm !bg-white w-full"></div>
                                <div class="flex items-end"><button class="w-full h-[30px] rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm">Simpan Opname</button></div>
                            </form>
                        @else
                            <p class="text-xs text-slate-400 font-medium">Hanya pengelola stok yang bisa input opname.</p>
                        @endif
                    </x-ui.glass-card>
                    <x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-rose-500 shadow-lg !rounded-3xl !p-6 !bg-white">
                        <h3 class="text-sm font-bold text-slate-800 mb-1">Scrap (Rusak / Buang)</h3>
                        <p class="text-[11px] text-slate-500 font-medium mb-3">Wajib isi alasan — tercatat permanen.</p>
                        @if($canManage)
                            <form method="POST" action="{{ route('warehouse.stockopname.scrap') }}" class="grid grid-cols-2 gap-3" onsubmit="confirmSubmit(event, 'Catat scrap? Stok akan dikurangi permanen.');">
                                @csrf
                                <div class="col-span-2"><label class="ui-label">Produk <span class="text-red-500">*</span></label>
                                    <select name="product_id" required class="ui-input ui-input--sm !bg-white w-full"><option value="">— pilih produk —</option>@foreach($productOptions as $o)<option value="{{ $o->id }}">{{ $o->product_code }} — {{ $o->product_name }}</option>@endforeach</select></div>
                                <div><label class="ui-label">Qty <span class="text-red-500">*</span></label><input type="number" name="qty" min="0.01" step="any" required class="ui-input ui-input--sm !bg-white w-full"></div>
                                <div><label class="ui-label">Alasan <span class="text-red-500">*</span></label><input type="text" name="reason" required placeholder="Mis. rusak, kedaluwarsa" class="ui-input ui-input--sm !bg-white w-full" autocomplete="off"></div>
                                <div class="col-span-2 flex justify-end"><button class="h-[30px] px-4 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-sm">Catat Scrap</button></div>
                            </form>
                        @else
                            <p class="text-xs text-slate-400 font-medium">Hanya pengelola stok yang bisa mencatat scrap.</p>
                        @endif
                    </x-ui.glass-card>
                </div>
                <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full max-w-full mx-auto !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg" style="background:#ffffff;">
                    <div class="p-5 pb-4 border-b border-slate-100 bg-white"><h3 class="text-sm font-bold text-slate-800 leading-tight">Prioritas Opname</h3>
                    <p class="text-[11px] text-slate-500 font-medium leading-tight">SKU kritis &amp; tanpa min-stock dihitung lebih dulu.</p></div>
                    <div class="flex-1 bg-white manage-table-scroll">
                        <table class="w-full min-w-[640px] text-sm text-left text-slate-600 bg-white">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                                <tr><th class="px-4 py-4">Kode</th><th class="px-4 py-4">Nama</th><th class="px-4 py-4 text-right">Tercatat</th><th class="px-4 py-4 text-center">Status</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($belowMin->take(20) as $p)
                                    <tr class="hover:bg-blue-50/60 transition-colors"><td class="px-4 py-3 font-mono text-xs">{{ $p->product_code }}</td><td class="px-4 py-3 font-bold text-xs text-slate-800">{{ $p->product_name }}</td><td class="px-4 py-3 text-right font-black text-slate-800">{{ $p->stock }}</td><td class="px-4 py-3 text-center text-[10px] font-bold text-red-600">PRIORITAS</td></tr>
                                @empty
                                    <tr><td colspan="4" class="p-0"><x-ui.empty icon="fa-clipboard-check" title="Tidak ada prioritas opname" message="Semua SKU di atas min-stock." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.glass-card>
            @endif

            {{-- ================= TAB REPLENISHMENT ================= --}}
            @if($currentTab === 'replenishment')
                <x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg !rounded-3xl !p-6 !bg-white">
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Aturan Reorder</h3>
                    <p class="text-[11px] text-slate-500 font-medium mb-4">Min/max per produk — pemicu draft pengajuan otomatis.</p>
                    @if($canManage)
                        <form method="POST" action="{{ route('warehouse.inventory.rules.store') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end">
                            @csrf
                            <div class="lg:col-span-6 md:col-span-2"><label class="ui-label">Produk <span class="text-red-500">*</span></label>
                                <select name="product_id" required class="ui-input ui-input--sm !bg-white w-full"><option value="">— pilih produk —</option>@foreach($productOptions as $o)<option value="{{ $o->id }}">{{ $o->product_code }} — {{ $o->product_name }}</option>@endforeach</select></div>
                            <div class="lg:col-span-2"><label class="ui-label">Min <span class="text-red-500">*</span></label><input type="number" name="min_qty" min="0" step="any" required class="ui-input ui-input--sm !bg-white w-full"></div>
                            <div class="lg:col-span-2"><label class="ui-label">Max</label><input type="number" name="max_qty" min="0" step="any" class="ui-input ui-input--sm !bg-white w-full"></div>
                            <div class="lg:col-span-2 flex items-end"><button class="w-full h-[30px] rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm">Simpan Aturan</button></div>
                        </form>
                    @endif
                    @if($rules->isNotEmpty())
                        <div class="mt-3 space-y-2">
                            @foreach($rules as $r)
                                <div class="flex items-center justify-between gap-2 text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                                    <span class="font-bold text-slate-700 truncate">{{ $r->product->product_name ?? '-' }}</span>
                                    <span class="font-medium text-slate-500 shrink-0">min {{ $r->min_qty }} · max {{ $r->max_qty ?? '-' }} · {{ $r->active ? 'aktif' : 'nonaktif' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.glass-card>

                <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full max-w-full mx-auto !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg" style="background:#ffffff;">
                    <div class="p-5 pb-4 border-b border-slate-100 bg-white"><h3 class="text-sm font-bold text-slate-800 leading-tight">Di Bawah Ambang — Siap Diajukan</h3>
                    <p class="text-[11px] text-slate-500 font-medium leading-tight">Centang lalu buatkan pengajuan barang (masuk alur approval normal).</p></div>
                    @if($belowMin->isEmpty())
                        <x-ui.empty icon="fa-cart-plus" title="Semua stok aman" message="Semua stok di atas ambang. Atur min-stock/rule untuk mengaktifkan pantauan." />
                    @else
                        <form method="POST" action="{{ route('warehouse.inventory.draft-order') }}" onsubmit="confirmSubmit(event, 'Buatkan pengajuan barang untuk item tercentang?');">
                            @csrf
                            <div class="flex-1 bg-white manage-table-scroll">
                                <table class="w-full min-w-[640px] text-sm text-left text-slate-600 bg-white">
                                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                                        <tr><th class="px-4 py-4 text-center">Pilih</th><th class="px-4 py-4">Produk</th><th class="px-4 py-4 text-right">Stok</th><th class="px-4 py-4 text-right">Min</th><th class="px-4 py-4 text-right">Saran Order</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($belowMin as $p)
                                            @php $need = max(0, (float) (($rules->firstWhere('product_id', $p->id)?->max_qty) ?? $p->reorder_qty ?? ($p->min_stock * 2)) - (float) $p->stock); @endphp
                                            <tr class="hover:bg-blue-50/60 transition-colors">
                                                <td class="px-4 py-3 text-center"><input type="checkbox" name="product_ids[]" value="{{ $p->id }}" checked class="w-4 h-4 accent-blue-600"></td>
                                                <td class="px-4 py-3 font-bold text-xs text-slate-800">{{ $p->product_name }}</td>
                                                <td class="px-4 py-3 text-right font-black text-red-600">{{ $p->stock }}</td>
                                                <td class="px-4 py-3 text-right text-xs">{{ $p->min_stock }}</td>
                                                <td class="px-4 py-3 text-right font-bold text-blue-700">{{ $need }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($canManage)
                                <div class="px-6 py-3 border-t border-slate-200 bg-white flex justify-end"><button class="h-[30px] px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm"><i class="fas fa-file-export text-[11px] mr-1.5"></i> Buatkan Pengajuan Barang</button></div>
                            @endif
                        </form>
                    @endif
                </x-ui.glass-card>
            @endif

            {{-- ================= TAB LAPORAN ================= --}}
            @if($currentTab === 'laporan')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-amber-500 shadow-lg" style="background:#ffffff;">
                        <div class="p-5 pb-4 border-b border-slate-100 bg-white"><h3 class="text-sm font-bold text-slate-800 leading-tight">Expired Terdekat (FEFO)</h3>
                        <p class="text-[11px] text-slate-500 font-medium leading-tight">Keluarkan yang paling dekat kedaluwarsa lebih dulu.</p></div>
                        <div class="flex-1 bg-white manage-table-scroll">
                            <table class="w-full min-w-[480px] text-sm text-left text-slate-600 bg-white">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                                    <tr><th class="px-4 py-4">Produk</th><th class="px-4 py-4">Expired</th><th class="px-4 py-4 text-right">Sisa</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($expiredBatches as $b)
                                        <tr class="hover:bg-blue-50/60 transition-colors"><td class="px-4 py-3 font-bold text-xs text-slate-800">{{ $b->product->product_name ?? '-' }}</td><td class="px-4 py-3 text-xs font-bold text-amber-600">{{ $b->expiry_date?->format('d M Y') }}</td><td class="px-4 py-3 text-right font-black text-slate-800">{{ $b->qty_current }}</td></tr>
                                    @empty
                                        <tr><td colspan="3" class="p-0"><x-ui.empty icon="fa-hourglass-half" title="Belum ada batch ber-expired" message="Batch tercatat otomatis dari penerimaan." /></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-ui.glass-card>
                    <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg" style="background:#ffffff;">
                        <div class="p-5 pb-4 border-b border-slate-100 bg-white"><h3 class="text-sm font-bold text-slate-800 leading-tight">Aging Stok (Batch Tertua)</h3>
                        <p class="text-[11px] text-slate-500 font-medium leading-tight">Batch yang paling lama mengendap per produk.</p></div>
                        <div class="flex-1 bg-white manage-table-scroll">
                            <table class="w-full min-w-[480px] text-sm text-left text-slate-600 bg-white">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                                    <tr><th class="px-4 py-4">Produk</th><th class="px-4 py-4">Masuk</th><th class="px-4 py-4 text-right">Sisa</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($agingBatches as $b)
                                        <tr class="hover:bg-blue-50/60 transition-colors"><td class="px-4 py-3 font-bold text-xs text-slate-800">{{ $b->product->product_name ?? '-' }}</td><td class="px-4 py-3 text-xs">{{ $b->receipt_date?->format('d M Y') ?? '-' }}</td><td class="px-4 py-3 text-right font-black text-slate-800">{{ $b->qty_current }}</td></tr>
                                    @empty
                                        <tr><td colspan="3" class="p-0"><x-ui.empty icon="fa-boxes-stacked" title="Belum ada batch tercatat" message="Batch tercatat otomatis dari penerimaan." /></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-ui.glass-card>
                </div>
            @endif
        </div>
    </div>
</x-layout-users>
