@php
    $mappedItems = $items->map(function($item) {
        $unit = $item->unit;
        $fillUnit = $item->fill_unit ?: 'Pcs';
        $pcsPerUnit = (int) ($item->pcs_per_unit ?? 0);

        // Stok diinput per pack, jadi tampilkan langsung dalam unit pack.
        $displayUnit = $unit ?: $fillUnit;

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
@endphp
<x-layout-users title="Stock Monitoring">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .swal2-container { z-index: 100000 !important; }

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

            <!-- Page Title Card (Desktop Style) -->
            <div class="hidden md:block">
                <x-ui.page-header title="Stock Monitoring" subtitle="Monitor physical stock levels, adjust quantities and units, and synchronize data with Accurate reports." icon="fa-boxes-stacked" />
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

        <!-- Add Item Modal -->
        <div x-show="showAddBarangModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showAddBarangModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all flex flex-col" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800"><i class="fas fa-plus-circle text-blue-500 mr-2"></i> Add New Item</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Register a new item to the database for filtering and updating.</p>
                    </div>
                    <button @click="showAddBarangModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form @submit.prevent="saveNewBarang()" class="p-6 space-y-4">
                    <div>
                        <label class="ui-label">Item Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="addNama" required placeholder="Enter item name..." class="ui-input !bg-white">
                    </div>
                    <div>
                        <label class="ui-label">Item Code <span class="text-red-500">*</span></label>
                        <input type="text" x-model="addKode" required placeholder="Enter item code (Accurate)..." class="ui-input !bg-white">
                    </div>
                    <div>
                        <label class="ui-label">Unit <span class="text-red-500">*</span></label>
                        <select x-model="addSatuan" required class="ui-input !bg-white">
                            <option value="" disabled selected>-- Select Unit --</option>
                            <option value="pcs">pcs</option>
                            <option value="box">box</option>
                            <option value="botol">botol</option>
                            <option value="galon">galon</option>
                            <option value="Jerigen">Jerigen</option>
                            <option value="karton">karton</option>
                            <option value="pack">pack</option>
                            <option value="paket">paket</option>
                            <option value="polybag">polybag</option>
                            <option value="pouches">pouches</option>
                            <option value="roll">roll</option>
                        </select>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showAddBarangModal = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-500/20">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Excel Import Preview Modal -->
        <div x-show="showPreviewModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden transform transition-all flex flex-col" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800"><i class="fas fa-file-excel text-emerald-500 mr-2"></i> Accurate Import Preview</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5" x-text="`File: ${previewFilename} (${previewTercetakDate || '-'})`"></p>
                    </div>
                    <button @click="showPreviewModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto space-y-4 flex-1" @scroll.passive="previewScrollHandler($event)">
                    <x-ui.search-input x-model="previewSearchQuery" placeholder="Search items in details..." class="w-full max-w-md" />
                    <div class="overflow-hidden border border-slate-200 rounded-xl bg-white shadow-sm flex flex-col overflow-x-auto">
                        <table class="w-full text-sm text-left table-fixed min-w-[600px]">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-xs uppercase tracking-wider w-[280px] max-w-[280px] whitespace-normal">Item</th>
                                    <th class="px-4 py-3 text-center text-xs uppercase tracking-wider w-24">Current Stock</th>
                                    <th class="px-4 py-3 text-center text-xs uppercase tracking-wider w-32">Excel Stock (Accurate)</th>
                                    <th class="px-4 py-3 text-center text-xs uppercase tracking-wider w-28">Unit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="item in displayedPreviewItems" :key="item.id">
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-4 py-3 w-[280px] max-w-[280px] whitespace-normal">
                                            <div class="font-extrabold text-slate-800 text-xs sm:text-sm" x-text="item.nama"></div>
                                            <div class="text-[9px] text-slate-400 font-bold tracking-wider uppercase mt-0.5" x-text="item.kode"></div>
                                        </td>
                                        <td class="px-4 py-3 text-center text-slate-600 font-bold text-xs sm:text-sm" x-text="item.stok_db"></td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="text" inputmode="numeric" pattern="[0-9]*" x-model.number="item.stok_excel" class="mx-auto block w-20 text-center text-xs sm:text-sm font-black text-emerald-700 border border-slate-300 rounded-md focus:border-emerald-500 focus:ring-0 px-1.5 py-1 bg-slate-50/50 focus:bg-white transition-colors" placeholder="0">
                                        </td>
                                        <td class="px-4 py-3 text-center align-middle">
                                            <select x-model="item.satuan" class="mx-auto block w-24 text-center text-xs font-bold text-slate-700 border border-slate-300 rounded-lg focus:border-blue-500 focus:ring-0 px-1 py-1 bg-slate-50/50 focus:bg-white transition-colors">
                                                <option value="pcs">pcs</option>
                                                <option value="box">box</option>
                                                <option value="botol">botol</option>
                                                <option value="galon">galon</option>
                                                <option value="Jerigen">Jerigen</option>
                                                <option value="karton">karton</option>
                                                <option value="pack">pack</option>
                                                <option value="paket">paket</option>
                                                <option value="polybag">polybag</option>
                                                <option value="pouches">pouches</option>
                                                <option value="roll">roll</option>
                                            </select>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <div x-show="previewItems.length === 0" x-cloak class="px-6 py-10 text-center">
                            <i class="fas fa-search text-3xl text-slate-300 mb-3"></i>
                            <p class="text-sm font-bold text-slate-600">Tidak ada item yang bisa dipratinjau.</p>
                            <p class="text-xs text-slate-400 font-medium mt-1 leading-relaxed">
                                Excel terbaca, tetapi tidak ada kode/nama barang yang cocok dengan data di sistem.
                                <br>Pastikan barang sudah terdaftar lewat menu <b>&quot;Add Item Data&quot;</b> atau nama/kode barang di Excel sama persis dengan master barang.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center shrink-0">
                    <span class="text-xs font-bold text-slate-500" x-text="`Total: ${filteredPreviewItems.length} products ready to be updated.`"></span>
                    <div class="flex gap-2">
                        <button @click="showPreviewModal = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button @click="submitImportedData()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-500/20 flex items-center gap-1.5">
                            <i class="fas fa-check-circle"></i> Update Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Details Modal -->
        <div x-show="showLogDetailsModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showLogDetailsModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-hidden transform transition-all flex flex-col" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                <div class="px-4 py-2.5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-slate-800"><i class="fas fa-info-circle text-blue-500 mr-1.5"></i> Stock Update Details</h3>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5" x-text="'Update log history info #' + (selectedLog ? selectedLog.id : '')"></p>
                    </div>
                    <button @click="showLogDetailsModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <div class="p-4 overflow-y-auto space-y-3 flex-1" x-show="selectedLog">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase text-[10px] tracking-wider">Time</span>
                            <span class="font-bold text-slate-700 mt-0.5 block" x-text="selectedLog ? selectedLog.tanggal : ''"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase text-[10px] tracking-wider">Editor</span>
                            <span class="font-bold text-slate-700 mt-0.5 block" x-text="selectedLog ? selectedLog.editor : ''"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase text-[10px] tracking-wider">Source</span>
                            <span class="font-bold text-slate-700 mt-0.5 block" x-text="selectedLog ? selectedLog.sumber : ''"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase text-[10px] tracking-wider">Excel File</span>
                            <span class="font-bold text-slate-700 mt-0.5 block truncate" :title="selectedLog ? selectedLog.nama_file : ''" x-text="selectedLog ? selectedLog.nama_file : ''"></span>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <x-ui.search-input x-model="logDetailsSearchQuery" placeholder="Search items in details..." class="w-full sm:w-64" />
                    </div>
                    <div class="overflow-y-auto overflow-x-auto max-h-80 border border-slate-200 rounded-xl bg-white shadow-sm">
                        <table class="w-full text-xs text-left min-w-[450px]">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5 text-[10px] uppercase tracking-wider">Item Name</th>
                                    <th class="px-4 py-2.5 text-center text-[10px] uppercase tracking-wider w-36 leading-tight whitespace-nowrap">Stock<br><span class="text-[9px] text-slate-400 font-medium lowercase">(old &rarr; new)</span></th>
                                    <th class="px-4 py-2.5 text-center text-[10px] uppercase tracking-wider w-36 leading-tight whitespace-nowrap">PO Stock<br><span class="text-[9px] text-slate-400 font-medium lowercase">(old &rarr; new)</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="detail in filteredSelectedLogDetails" :key="detail.kode + detail.nama">
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-4 py-2 whitespace-normal">
                                            <div class="text-[9px] font-bold text-blue-500 uppercase" x-text="detail.kode"></div>
                                            <div class="text-xs font-bold text-slate-800 leading-normal" x-text="detail.nama"></div>
                                        </td>
                                        <td class="px-4 py-2 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5 text-xs text-slate-700">
                                                <span class="text-slate-400 font-medium" x-text="detail.old_stok"></span>
                                                <span class="text-slate-300 font-medium">&rarr;</span>
                                                <span class="font-bold" :class="{'text-emerald-600': detail.new_stok > detail.old_stok,'text-rose-600': detail.new_stok < detail.old_stok,'text-slate-700': detail.new_stok === detail.old_stok}" x-text="detail.new_stok"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5 text-xs text-slate-700">
                                                <span class="text-slate-400 font-medium" x-text="detail.old_stok_po"></span>
                                                <span class="text-slate-300 font-medium">&rarr;</span>
                                                <span class="font-bold" :class="{'text-emerald-600': detail.new_stok_po > detail.old_stok_po,'text-rose-600': detail.new_stok_po < detail.old_stok_po,'text-slate-700': detail.new_stok_po === detail.old_stok_po}" x-text="detail.new_stok_po"></span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex justify-between items-center shrink-0" x-show="selectedLog">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500">Status: </span>
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded-md" :class="{'bg-emerald-100 text-emerald-700': selectedLog && selectedLog.status === 'success','bg-amber-100 text-amber-700': selectedLog && selectedLog.status === 'undone'}" x-text="selectedLog ? (selectedLog.status === 'success' ? 'Active' : 'Undone') : ''"></span>
                    </div>
                    <div class="flex gap-2.5">
                        <button @click="showLogDetailsModal = false" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-50 transition-colors">
                            Close
                        </button>
                        <button type="button" x-show="selectedLog && selectedLog.status === 'success'" @click="undoLog(selectedLog.id); showLogDetailsModal = false" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition-colors shadow-sm shadow-red-500/30 flex items-center gap-1.5">
                            <i class="fas fa-undo"></i> Undo Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script id="mapped-items-data" type="application/json">@json($mappedItems)</script>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('stockManager', () => ({
                showAddBarangModal: false,
                showLogDetailsModal: false,
                showPreviewModal: false,
                syncOpen: false,
                searchQuery: '',
                previewSearchQuery: '',
                isLoadingHistory: false,
                isParsing: false,
                limit: 50,
                previewLimit: 50,
                items: JSON.parse(document.getElementById('mapped-items-data').textContent),
                previewItems: [],
                previewTercetakDate: '',
                previewFilename: '',
                selectedLog: null,
                selectedLogDetails: [],
                logDetailsSearchQuery: '',
                addNama: '',
                addKode: '',
                addSatuan: '',
                scrollTimeout: null,
                previewScrollTimeout: null,

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

                previewScrollHandler(e) {
                    if (this.previewScrollTimeout) return;
                    this.previewScrollTimeout = setTimeout(() => {
                        const el = e.target;
                        if (el.scrollTop + el.clientHeight >= el.scrollHeight - 250) {
                            this.previewLimit += 50;
                        }
                        this.previewScrollTimeout = null;
                    }, 50);
                },

                showToast(message, icon = 'error') {
                    let title = 'Notification';
                    if (icon === 'success') title = 'Success!';
                    if (icon === 'error') title = 'Failed!';
                    if (icon === 'warning') title = 'Warning!';
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: icon,
                        confirmButtonColor: icon === 'success' ? '#10b981' : (icon === 'warning' ? '#f59e0b' : '#ef4444')
                    });
                },

                init() {
                    // Default card Sync Center tertutup saat halaman dibuka
                    this.syncOpen = false;
                    this.$watch('searchQuery', () => {
                        this.limit = 50;
                    });
                    this.$watch('previewSearchQuery', () => {
                        this.previewLimit = 50;
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

                get filteredSelectedLogDetails() {
                    if (this.logDetailsSearchQuery.trim() === '') {
                        return this.selectedLogDetails;
                    }
                    return this.selectedLogDetails.filter(detail =>
                        detail.nama.toLowerCase().includes(this.logDetailsSearchQuery.toLowerCase()) ||
                        detail.kode.toLowerCase().includes(this.logDetailsSearchQuery.toLowerCase())
                    );
                },

                get filteredPreviewItems() {
                    if (this.previewSearchQuery.trim() === '') {
                        return this.previewItems;
                    }
                    return this.previewItems.filter(item =>
                        item.nama.toLowerCase().includes(this.previewSearchQuery.toLowerCase()) ||
                        item.kode.toLowerCase().includes(this.previewSearchQuery.toLowerCase())
                    );
                },

                get displayedPreviewItems() {
                    return this.filteredPreviewItems.slice(0, this.previewLimit);
                },

                openAddBarangModal() {
                    this.addNama = '';
                    this.addKode = '';
                    this.addSatuan = '';
                    this.showAddBarangModal = true;
                },

                saveNewBarang() {
                    fetch('{{ route('sales.monitoring.add_barang') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            product_name: this.addNama,
                            product_code: this.addKode,
                            unit: this.addSatuan
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#3b82f6'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Failed!',
                                text: data.message || 'Failed to add new item',
                                icon: 'error',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Connection error while adding item.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    });
                },

                showInfoAccurateImport() {
                    Swal.fire({
                        title: 'Usage Information',
                        text: 'The Add Item Data feature is used to register new items from Accurate so their codes are registered in the system. This is required so their stock can be automatically updated when you import Accurate Excel reports.',
                        icon: 'info',
                        confirmButtonColor: '#3b82f6'
                    });
                },

                undoLog(logId) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'Stock updates in this log will be undone (reverted to the stock before update)!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, Undo!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/sales/stock/undo/${logId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.showToast('Stock update log successfully undone!', 'success');
                                    setTimeout(() => window.location.reload(), 1500);
                                } else {
                                    this.showToast(data.message || 'Failed to undo log');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                this.showToast('Connection error occurred');
                            });
                        }
                    });
                },

                showLogDetails(logId) {
                    this.logDetailsSearchQuery = '';
                    fetch(`/sales/stock/log/${logId}/details`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.selectedLog = data.log;
                            this.selectedLogDetails = data.details;
                            this.showLogDetailsModal = true;
                        } else {
                            this.showToast(data.message || 'Failed to fetch log details');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showToast('Connection error occurred');
                    });
                },

                uploadExcelForPreview(event) {
                    const fileInput = event.target.querySelector('input[type="file"]');
                    if (!fileInput || !fileInput.files[0]) {
                        this.showToast('Please select an Excel file first.', 'warning');
                        return;
                    }
                    this.isParsing = true;
                    const formData = new FormData();
                    formData.append('file', fileInput.files[0]);
                    fetch('{{ route('sales.monitoring.parse_import') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(async response => {
                        const isJson = response.headers.get('content-type')?.includes('application/json');
                        const data = isJson ? await response.json() : null;
                        if (!response.ok) {
                            throw new Error(data?.message || `Error ${response.status}: Failed to read Excel file.`);
                        }
                        return data;
                    })
                    .then(data => {
                        this.previewItems = data.items;
                        this.previewTercetakDate = data.tercetak_date;
                        this.previewFilename = data.original_filename;
                        this.previewSearchQuery = '';
                        this.previewLimit = 50;
                        this.showPreviewModal = true;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showToast(error.message || 'Connection error while reading Excel file.');
                    })
                    .finally(() => {
                        this.isParsing = false;
                    });
                },

                submitImportedData() {
                    if (this.previewItems.length === 0) {
                        return;
                    }
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Stock data in the database will be updated according to this preview!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Yes, Update Data!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('{{ route('sales.monitoring.save_import') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    items: this.previewItems,
                                    original_filename: this.previewFilename,
                                    tercetak_date: this.previewTercetakDate
                                })
                            })
                            .then(async response => {
                                const isJson = response.headers.get('content-type')?.includes('application/json');
                                const data = isJson ? await response.json() : null;
                                if (!response.ok) {
                                    throw new Error(data?.message || `Error ${response.status}: Failed to save stock data.`);
                                }
                                return data;
                            })
                            .then(data => {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message || 'Stock data updated successfully!',
                                    icon: 'success',
                                    confirmButtonColor: '#10b981'
                                }).then(() => {
                                    window.location.reload();
                                });
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: error.message || 'Connection error while saving changes.',
                                    icon: 'error',
                                    confirmButtonColor: '#ef4444'
                                });
                            });
                        }
                    });
                },
            }));
        });
    </script>
    @endpush
</x-layout-users>