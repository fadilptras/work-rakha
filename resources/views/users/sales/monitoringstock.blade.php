@php
    $mappedItems = $items->map(function($item) {
        $unit = $item->unit;
        $fillUnit = $item->fill_unit ?: 'Pcs';
        $pcsPerUnit = (int) ($item->pcs_per_unit ?? 0);

        // Packaging type single (tanpa isian) -> stock/po tampil dalam unit itu sendiri.
        // Multi-pack (pcs_per_unit > 1) -> tampil dalam fill_unit (isi per kemasan).
        $isSingle = $pcsPerUnit <= 1;
        $displayUnit = $isSingle ? ($unit ?: $fillUnit) : $fillUnit;

        return [
            'id' => $item->id,
            'kode' => $item->product_code ?? '-',
            'nama' => $item->product_name_clean ?: $item->product_name,
            'po' => $item->stock_po,
            'stokSistem' => $item->stock,
            'satuan' => $displayUnit,
            'packaging' => $unit,
            'pcs_per_unit' => $pcsPerUnit,
            'fill_unit' => $fillUnit,
        ];
    });

    $user = Auth::user();
    $backRoute = route('dashboard');
    $backText = 'Back to Dashboard';
    if ($user) {
        $jabatan = strtolower($user->jabatan ?? '');
        $divisi = strtolower($user->divisi ?? '');
        $isTopManagement = \Illuminate\Support\Str::contains($jabatan, 'direktur') || $divisi === 'top management';
        $isMarketing = in_array($divisi, ['marketing dan operasional']);
        $isAdminGudang = \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang';
        $isLegalPurchasing = \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing') || \Illuminate\Support\Str::contains($jabatan, 'purchasing');

        if ($isTopManagement || $isMarketing) {
            $backRoute = route('sales.index');
            $backText = 'Back to Sales Dashboard';
        } elseif ($isAdminGudang || $isLegalPurchasing) {
            $backRoute = route('warehouse.dashboard');
            $backText = 'Back to Dashboard Gudang';
        }
    }
@endphp
<x-layout-users title="Stock Monitoring">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @push('styles')
    <style>
        [x-cloak] { display: none !important; }

        .mesh-bg { 
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
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

        /* Mobile Product Cards */
        .mobile-product-card {
            background: #ffffff; border: 1px solid #e2e8f0; border-left: 4px solid #3b82f6; border-radius: 0.9rem;
            padding: 0.8rem 0.9rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .mobile-product-card.mpc-zero { border-left-color: #f43f5e; }
        .mobile-product-card .mpc-packaging {
            display: inline-flex; align-items: center; gap: 4px;
            background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
            font-size: 0.6rem; font-weight: 800; padding: 1px 7px; border-radius: 9999px;
            margin-top: 4px;
        }
        .mobile-product-card.mpc-zero .mpc-packaging { display: none; }

    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800 pb-16" x-data="stockManager()">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start">
            
            <!-- Alert Messages -->
            @if (session('success'))
                <div class="bg-blue-100 border border-blue-300 text-blue-700 px-4 py-3 rounded-xl relative shadow-md" role="alert">
                    <span class="block sm:inline font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-xl relative shadow-md" role="alert">
                    <span class="block sm:inline font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Back Button -->
            <div class="w-full flex justify-start">
                <x-ui.back-button :href="$backRoute" :label="$backText" />
            </div>

            <!-- Page Title Card (Desktop Style) -->
            <div class="hidden md:block">
                <x-ui.page-header title="Stock Monitoring" subtitle="Monitor physical stock levels — stok otomatis via Surat Jalan & halaman gudang." icon="fa-boxes-stacked" />
            </div>

            <!-- Page Title Card (Mobile Style - Icon Moved to Right) -->
            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Stock Monitoring</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">
                        Monitor physical inventory levels.
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
            </div>

            <!-- Content -->
            <x-ui.glass-card padding="none" class="!p-0 overflow-hidden flex flex-col w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-sm !rounded-3xl !bg-white">
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm shrink-0"><i class="fas fa-list"></i></span>
                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-slate-800 leading-tight">Product List</h2>
                            <p class="text-[11px] text-slate-500 font-medium leading-tight">Last updated: @if($lastMutationAt) {{ $lastMutationAt->timezone('Asia/Jakarta')->format('d M Y, H:i') }} @else - @endif</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 w-full sm:w-auto sm:flex-1 sm:justify-end min-w-0">
                        <x-ui.search-input x-model="searchQuery" placeholder="Search Product Name" autocomplete="off" class="flex-1 min-w-[140px] max-w-sm hidden sm:flex" />
                        @if($canManageStock)
                        <x-ui.export-button variant="excel" label="Export Excel" href="{{ route('sales.monitoring.export') }}" title="Export Stock to Excel" class="shrink-0" />
                        @endif
                    </div>
                </div>

                <!-- Mobile Card Search -->
                <div class="sm:hidden px-3 pt-3">
                    <x-ui.search-input x-model="searchQuery" placeholder="Cari produk..." />
                </div>

                <!-- Mobile Product Cards -->
                <div class="md:hidden px-3 pt-2 pb-3 space-y-2 max-h-[70vh] overflow-y-auto" @scroll.passive="mobileScrollHandler($event)">
                    <template x-for="item in displayedItems" :key="'m-' + item.id">
                        <div class="mobile-product-card" :class="{'mpc-zero': item.stokSistem === 0}">
                            <div class="flex items-start gap-2.5">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <div class="text-[9px] font-bold text-blue-500 uppercase tracking-wide" x-text="item.kode"></div>
                                        <template x-if="item.packaging">
                                            <span class="mpc-packaging">
                                                <span x-text="item.packaging"></span>
                                                <template x-if="item.pcs_per_unit > 1">
                                                    <span><span class="text-slate-400/70">/</span> <span x-text="item.pcs_per_unit"></span> <span x-text="item.fill_unit"></span></span>
                                                </template>
                                            </span>
                                        </template>
                                    </div>
                                    <div class="text-[13px] font-bold text-slate-800 leading-snug break-words mt-1" x-text="item.nama"></div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-black text-lg leading-tight" :class="{'text-rose-600': item.stokSistem === 0}">
                                        <span x-text="item.stokSistem"></span>
                                        <span class="text-xs font-bold text-slate-500" x-text="item.satuan"></span>
                                    </div>
                                    <template x-if="item.pcs_per_unit > 1 && item.packaging">
                                        <div class="text-[11px] font-black text-emerald-600" x-text="'= ' + Math.floor(item.stokSistem / item.pcs_per_unit).toLocaleString('id-ID') + ' ' + item.packaging"></div>
                                    </template>
                                    <div class="text-[10px] text-blue-500 font-bold" x-text="'PO: ' + item.po + ' ' + item.satuan"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="displayedItems.length === 0" class="text-center py-8 text-slate-400 text-sm font-medium">
                        Tidak ada produk ditemukan.
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block overflow-auto max-h-[800px] flex-1 bg-white" @scroll.passive="scrollHandler($event)">
                    <table class="w-full text-sm text-left">
                        <thead class="table-header sticky top-0 z-10 shadow-sm bg-slate-50/80 backdrop-blur-sm">
                            <tr>
                                <th class="px-5 py-3 text-left text-[11px] md:text-xs uppercase tracking-wider whitespace-nowrap hidden md:table-cell">Code</th>
                                <th class="px-3 py-3 text-[11px] md:text-xs uppercase tracking-wider">Product Name</th>
                                <th class="px-3 py-3 text-center text-[11px] md:text-xs uppercase tracking-wider whitespace-nowrap bg-blue-50/60 border-l border-blue-100 text-blue-700" style="width:200px;">Available Stock</th>
                                <th class="px-3 py-3 text-center text-[11px] md:text-xs uppercase tracking-wider whitespace-nowrap hidden md:table-cell">Packaging</th>
                                <th class="px-3 py-3 text-center text-[11px] md:text-xs uppercase tracking-wider text-blue-600 w-20 md:w-32 whitespace-nowrap">
                                    <span class="hidden md:inline">In Delivery (PO)</span>
                                    <span class="md:hidden">PO</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <template x-for="item in displayedItems" :key="item.id">
                                <tr class="row-item hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-2.5 text-slate-500 whitespace-nowrap hidden md:table-cell text-xs md:text-sm" x-text="item.kode"></td>
                                    <td class="px-3 py-2.5 font-bold text-slate-800" style="max-width:230px;">
                                        <div class="text-[9px] font-bold text-blue-500 uppercase md:hidden mb-0.5" x-text="item.kode"></div>
                                        <span class="text-xs md:text-sm block truncate" :title="item.nama" x-text="item.nama"></span>
                                    </td>
                                    <td class="px-3 py-2.5 text-center whitespace-nowrap bg-blue-50/60 border-l border-blue-100" style="width:200px;">
                                        <span class="font-black text-sm md:text-base" :class="{'text-rose-600': item.stokSistem === 0}"><span x-text="item.stokSistem"></span> <span class="text-xs font-bold text-slate-500" x-text="item.satuan"></span></span>
                                        <template x-if="item.pcs_per_unit > 1 && item.packaging">
                                            <span><span class="text-xs font-bold text-slate-400"> = </span><span class="font-black text-sm md:text-base text-emerald-600"><span x-text="Math.floor(item.stokSistem / item.pcs_per_unit).toLocaleString('id-ID')"></span> <span x-text="item.packaging"></span></span><span x-show="item.stokSistem % item.pcs_per_unit > 0" class="text-xs font-bold text-slate-500" x-text="'+' + (item.stokSistem % item.pcs_per_unit) + ' ' + item.satuan"></span></span>
                                        </template>
                                    </td>
                                    <td class="px-3 py-2.5 text-center text-slate-600 font-medium whitespace-nowrap hidden md:table-cell">
                                        <template x-if="item.packaging">
                                            <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                                <span x-text="item.packaging"></span>
                                                <template x-if="item.pcs_per_unit > 1">
                                                    <span><span class="text-slate-400/50">/</span> <span x-text="item.pcs_per_unit"></span> <span x-text="item.fill_unit"></span></span>
                                                </template>
                                            </span>
                                        </template>
                                        <template x-if="!item.packaging">
                                            <span class="text-slate-400 text-[10px]">—</span>
                                        </template>
                                    </td>
                                    <td class="px-3 py-2.5 text-center text-blue-500 font-medium w-20 md:w-32 whitespace-nowrap">
                                        <span class="text-xs md:text-sm" x-text="item.po"></span>
                                        <div class="text-[9px] text-slate-400 md:hidden font-normal" x-text="item.satuan"></div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </x-ui.glass-card>
        </div>

    </div>

    <script id="mapped-items-data" type="application/json">@json($mappedItems)</script>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('stockManager', () => ({
                searchQuery: '',
                limit: 50,
                items: JSON.parse(document.getElementById('mapped-items-data').textContent),
                scrollTimeout: null,

                scrollHandler(e) {
                    if (this.scrollTimeout) return;
                    this.scrollTimeout = setTimeout(() => {
                        const el = e.target;
                        if (el.scrollTop + el.clientHeight >= el.scrollHeight - 250) {
                            this.limit += 50;
                        }
                        this.scrollTimeout = null;
                    }, 50);
                },

                mobileScrollHandler(e) {
                    if (this.scrollTimeout) return;
                    this.scrollTimeout = setTimeout(() => {
                        const el = e.target;
                        if (el.scrollTop + el.clientHeight >= el.scrollHeight - 250) {
                            this.limit += 50;
                        }
                        this.scrollTimeout = null;
                    }, 50);
                },

                init() {
                    this.$watch('searchQuery', () => {
                        this.limit = 50;
                    });

                    window.addEventListener('scroll', () => {
                        if (this.scrollTimeout) return;
                        this.scrollTimeout = setTimeout(() => {
                            if ((window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 300)) {
                                this.limit += 50;
                            }
                            this.scrollTimeout = null;
                        }, 50);
                    }, { passive: true });
                },

                get filteredItems() {
                    if (this.searchQuery.trim() === '') {
                        return this.items;
                    }
                    return this.items.filter(item => 
                        item.nama.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        item.kode.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                },

                get displayedItems() {
                    return this.filteredItems.slice(0, this.limit);
                },
            }));
        });
    </script>
    @endpush
</x-layout-users>