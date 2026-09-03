@php
    $mappedItems = $items->map(function($item) {
        return [
            'id' => $item->id,
            'kode' => $item->kode_barang ?? '-',
            'nama' => $item->nama_barang,
            'kategori' => $item->satuan ? 'Alkes / ' . $item->satuan : 'Alkes',
            'po' => $item->stok_po,
            'stokSistem' => $item->stok,
            'satuan' => $item->satuan ?? 'Pcs',
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
            $backRoute = route('sales.gudang.dashboard');
            $backText = 'Back to Dashboard Gudang';
        }
    }
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
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.6) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
            pointer-events: none;
        }
        
        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1.25rem; padding: 1rem 1.5rem; color: white;
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
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b; font-size: 0.9rem; font-weight: 700;
            transition: all 0.2s ease; width: fit-content;
        }
        .btn-back-modern:hover { 
            background: #fff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1); color: #1e40af;
        }
        .btn-back-modern .icon-circle {
            width: 32px; height: 32px; background: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 1.5rem;
            padding: 1.5rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        }
        .table-header { background: #f8fafc; color: #475569; font-weight: 800; }
        .row-item { background: #ffffff; color: #1e293b; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        .row-item:hover { background: #f1f5f9; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16" x-data="stockManager()">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-6 flex flex-col gap-2.5">
            
            <!-- Alert Messages -->
            @if (session('success'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl relative shadow-md" role="alert">
                    <span class="block sm:inline font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-xl relative shadow-md" role="alert">
                    <span class="block sm:inline font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Back Button (Unified with Text for both Mobile & Desktop) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <a href="{{ $backRoute }}" class="btn-back-modern shrink-0">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    {{ $backText }}
                </a>
            </div>

            <!-- Page Title Card (Desktop Style) -->
            <div class="hidden md:block page-header mb-2">
                <div class="header-content flex flex-row items-center justify-between gap-6">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Stock Monitoring</h1>
                        <p class="text-blue-100 text-sm mt-1">
                            Monitor physical stock levels, adjust quantities and units, and synchronize data with Accurate reports.
                        </p>
                    </div>
                    <div>
                        <i class="fas fa-boxes text-5xl opacity-20"></i>
                    </div>
                </div>
            </div>

            <!-- Page Title Card (Mobile Style - Icon Moved to Right) -->
            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 mb-1 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
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

            @if($canManageStock)
            <!-- Sync Center -->
            <div class="glass-panel border-t-4 border-t-blue-500 shadow-md">
                <!-- Header -->
                <div class="flex items-center gap-3">
                    <i class="fas fa-cloud-upload-alt text-blue-500 text-lg shrink-0"></i>
                    <h3 class="text-base font-black text-slate-700 uppercase tracking-wide">
                        Update Stock via Excel (Daily Upload)
                    </h3>
                </div>

                <!-- Content -->
                <div class="mt-2 pt-2 border-t border-slate-100">
                    <div class="grid grid-cols-1 {{ $canManageStock ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-6 items-stretch">
                        @if($canManageStock)
                        <!-- Drag & Drop Zone Form -->
                        <form @submit.prevent="uploadExcelForPreview($event)" class="flex flex-col h-full">
                            @csrf
                            <div class="flex-1 relative border-2 border-dashed border-emerald-200 rounded-2xl bg-emerald-50/50 hover:bg-emerald-50 transition-colors flex flex-col items-center justify-center text-center cursor-pointer min-h-[110px] py-3">
                                <input type="file" name="file" accept=".xlsx, .xls, .csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required onchange="document.getElementById('fileNameStock').textContent = this.files[0] ? this.files[0].name : 'No file chosen';">
                                <i class="fas fa-file-excel text-3xl text-emerald-400 mb-1.5"></i>
                                <p id="fileNameStock" class="text-sm font-bold text-slate-600 truncate px-2">Choose or drag Excel file here</p>
                            </div>
                            <button type="submit" :disabled="isParsing" class="w-full mt-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-md hover:shadow-emerald-500/40 flex justify-center items-center">
                                <template x-if="!isParsing">
                                    <span><i class="fas fa-upload mr-2"></i> Upload Excel File</span>
                                </template>
                                <template x-if="isParsing">
                                    <span><i class="fas fa-spinner fa-spin mr-2"></i> Processing Excel...</span>
                                </template>
                            </button>
                            <div class="flex items-center gap-2 mt-3 w-full">
                                <button type="button" @click="openAddBarangModal()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl transition-all shadow-md hover:shadow-blue-500/40 flex justify-center items-center text-sm">
                                    <i class="fas fa-plus mr-2 text-xs"></i> Add Item Data
                                </button>
                                <button type="button" @click="showInfoAccurateImport()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-600 transition-all duration-200 shrink-0 shadow-sm" title="Usage Information">
                                    <i class="fas fa-info text-xs"></i>
                                </button>
                            </div>
                        </form>
                        @endif

                        <!-- Log History -->
                        <div class="{{ $canManageStock ? 'lg:col-span-2' : '' }} flex flex-col">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-bold text-slate-700 text-sm flex items-center">Recent Upload History</h3>
                                <a href="{{ route('sales.stock.history_index') }}" class="text-xs text-blue-600 font-bold hover:underline">All History &rarr;</a>
                            </div>
                            <div class="overflow-x-auto overflow-y-hidden border border-slate-100 rounded-xl bg-white shadow-sm flex flex-col">
                                <table class="w-full text-left text-sm whitespace-nowrap min-w-[500px]">
                                    <thead class="bg-slate-50">
                                        <tr class="text-[10px] text-slate-500 border-b border-slate-200 tracking-wider">
                                            <th class="px-4 py-3 font-bold uppercase">Date & Time</th>
                                            <th class="px-4 py-3 font-bold uppercase hidden sm:table-cell">Editor</th>
                                            <th class="px-4 py-3 font-bold uppercase text-right">Items Updated</th>
                                            <th class="px-4 py-3 font-bold uppercase text-center">Status</th>
                                            <th class="px-4 py-3 font-bold uppercase text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($logs as $log)
                                            <tr class="hover:bg-slate-50/50 cursor-pointer transition-colors" @click="showLogDetails({{ $log->id }})">
                                                <td class="px-4 py-3 text-slate-700 font-medium">
                                                    {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                                </td>
                                                <td class="px-4 py-3 text-slate-600 hidden sm:table-cell">{{ $log->user->name ?? 'System' }}</td>
                                                <td class="px-4 py-3 text-emerald-600 font-black text-right">{{ $log->items_count }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    @if ($log->status === 'success')
                                                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-md">Success</span>
                                                    @elseif ($log->status === 'undone')
                                                        <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-1 rounded-md">Undone</span>
                                                    @else
                                                        <span class="bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-1 rounded-md">Failed</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-right" @click.stop>
                                                    @if ($log->status === 'success')
                                                        <button type="button" @click="undoLog({{ $log->id }})" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 hover:bg-red-100 px-2 py-1.5 rounded transition-colors">
                                                            <i class="fas fa-undo"></i> Undo
                                                        </button>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-3 text-center text-slate-500 font-medium">No stock update history available yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Content -->
            <div class="glass-panel border-t-4 border-t-blue-500">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-list text-blue-500"></i> Product List</h2>
                        <p class="text-xs text-slate-500 font-medium mt-1">Last updated: @if($logs->first()) {{ $logs->first()->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} @else - @endif</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                        <!-- Search Box -->
                        <div class="relative w-full sm:w-96">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400"></i>
                            </div>
                            <input type="text" x-model="searchQuery" placeholder="Search Product Name" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-8 px-3 py-2 shadow-sm transition-colors">
                            <button type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                        @if($canManageStock)
                        <a href="{{ route('sales.stock.export') }}" class="bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm flex justify-center items-center gap-2 whitespace-nowrap">
                            <i class="fas fa-file-export"></i> Export Excel
                        </a>
                        @endif
                    </div>
                </div>

                <div class="overflow-auto max-h-[800px] border border-slate-200 rounded-xl shadow-sm" @scroll.passive="scrollHandler($event)">
                    <table class="w-full text-sm text-left">
                        <thead class="table-header sticky top-0 z-10 shadow-sm bg-slate-50/80 backdrop-blur-sm">
                            <tr>
                                <th class="px-3 py-3 text-[11px] md:text-xs uppercase tracking-wider whitespace-nowrap hidden md:table-cell">Code</th>
                                <th class="px-3 py-3 text-[11px] md:text-xs uppercase tracking-wider">Product Name</th>
                                <th class="px-3 py-3 text-center text-[11px] md:text-xs uppercase tracking-wider w-20 md:w-28 whitespace-nowrap">
                                    <span class="hidden md:inline">Available Stock</span>
                                    <span class="md:hidden">Stock</span>
                                </th>
                                <th class="px-3 py-3 text-center text-[11px] md:text-xs uppercase tracking-wider whitespace-nowrap hidden md:table-cell">Unit</th>
                                <th class="px-3 py-3 text-center text-[11px] md:text-xs uppercase tracking-wider text-blue-600 w-20 md:w-32 whitespace-nowrap">
                                    <span class="hidden md:inline">In Delivery (PO)</span>
                                    <span class="md:hidden">PO</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white transition-opacity duration-200" :class="{'opacity-40 pointer-events-none': isLoadingHistory}">
                            <template x-for="item in displayedItems" :key="item.id">
                                <tr class="row-item hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5 text-slate-500 whitespace-nowrap hidden md:table-cell text-xs md:text-sm" x-text="item.kode"></td>
                                    <td class="px-3 py-2.5 font-bold text-slate-800 break-words max-w-sm">
                                        <div class="text-[9px] font-bold text-blue-500 uppercase md:hidden mb-0.5" x-text="item.kode"></div>
                                        <span class="text-xs md:text-sm" x-text="item.nama"></span>
                                    </td>
                                    <td class="px-3 py-2.5 text-center w-20 md:w-28 whitespace-nowrap">
                                        <span class="font-black text-sm md:text-base" :class="{'text-rose-600': item.stokSistem === 0}" x-text="item.stokSistem"></span>
                                        <div class="text-[9px] text-slate-400 md:hidden" x-text="item.satuan"></div>
                                    </td>
                                    <td class="px-3 py-2.5 text-center text-slate-600 font-medium whitespace-nowrap hidden md:table-cell text-xs md:text-sm" x-text="item.satuan"></td>
                                    <td class="px-3 py-2.5 text-center text-blue-500 font-medium w-20 md:w-32 whitespace-nowrap">
                                        <span class="text-xs md:text-sm" x-text="item.po"></span>
                                        <div class="text-[9px] text-slate-400 md:hidden font-normal" x-text="item.satuan"></div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Item Modal -->
        <div x-show="showAddBarangModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showAddBarangModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all flex flex-col" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800"><i class="fas fa-plus-circle text-blue-500 mr-2"></i> Add New Item</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Register a new item to the database for filtering and updating.</p>
                    </div>
                    <button @click="showAddBarangModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                 <!-- Modal Body -->
                <form @submit.prevent="saveNewBarang()" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Item Name</label>
                        <input type="text" x-model="addNama" required placeholder="Enter item name..." class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2 shadow-sm transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Item Code</label>
                        <input type="text" x-model="addKode" required placeholder="Enter item code (Accurate)..." class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2 shadow-sm transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Unit</label>
                        <select x-model="addSatuan" required class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2 shadow-sm transition-colors">
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

                    <!-- Modal Footer -->
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
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800"><i class="fas fa-file-excel text-emerald-500 mr-2"></i> Accurate Import Preview</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5" x-text="`File: ${previewFilename} (${previewTercetakDate || '-'})`"></p>
                    </div>
                    <button @click="showPreviewModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-6 overflow-y-auto space-y-4 flex-1" @scroll.passive="previewScrollHandler($event)">
                    <!-- Search inside modal -->
                    <div class="relative w-full max-w-md">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400 text-sm"></i>
                        </div>
                        <input type="text" x-model="previewSearchQuery" placeholder="Search items in details..." class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-8 px-3 py-2 shadow-sm transition-colors">
                        <button type="button" x-show="previewSearchQuery.length > 0" @click="previewSearchQuery = ''" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>

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
                    </div>
                </div>

                <!-- Modal Footer -->
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
                
                <!-- Modal Header -->
                <div class="px-4 py-2.5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-slate-800"><i class="fas fa-info-circle text-blue-500 mr-1.5"></i> Stock Update Details</h3>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5" x-text="'Update log history info #' + (selectedLog ? selectedLog.id : '')"></p>
                    </div>
                    <button @click="showLogDetailsModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-4 overflow-y-auto space-y-3 flex-1" x-show="selectedLog">
                    <!-- Log Info Grid -->
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

                    <!-- Search Box -->
                    <div class="flex items-center">
                        <div class="relative w-full sm:w-64">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400 text-xs"></i>
                            </div>
                            <input type="text" x-model="logDetailsSearchQuery" placeholder="Search items in details..." class="bg-white border border-slate-200 text-slate-700 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-8 py-1.5 shadow-sm transition-colors">
                            <button type="button" x-show="logDetailsSearchQuery.length > 0" @click="logDetailsSearchQuery = ''" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas fa-times-circle text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Items Updated Table -->
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
                                                <span class="font-bold" :class="{
                                                    'text-emerald-600': detail.new_stok > detail.old_stok,
                                                    'text-rose-600': detail.new_stok < detail.old_stok,
                                                    'text-slate-700': detail.new_stok === detail.old_stok
                                                }" x-text="detail.new_stok"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5 text-xs text-slate-700">
                                                <span class="text-slate-400 font-medium" x-text="detail.old_stok_po"></span>
                                                <span class="text-slate-300 font-medium">&rarr;</span>
                                                <span class="font-bold" :class="{
                                                    'text-emerald-600': detail.new_stok_po > detail.old_stok_po,
                                                    'text-rose-600': detail.new_stok_po < detail.old_stok_po,
                                                    'text-slate-700': detail.new_stok_po === detail.old_stok_po
                                                }" x-text="detail.new_stok_po"></span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex justify-between items-center shrink-0" x-show="selectedLog">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500">Status: </span>
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded-md" :class="{
                            'bg-emerald-100 text-emerald-700': selectedLog && selectedLog.status === 'success',
                            'bg-amber-100 text-amber-700': selectedLog && selectedLog.status === 'undone'
                        }" x-text="selectedLog ? (selectedLog.status === 'success' ? 'Active' : 'Undone') : ''"></span>
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
                searchQuery: '',
                previewSearchQuery: '',
                filterDate: new Date().toLocaleDateString('sv-SE', {timeZone: 'Asia/Jakarta'}),
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

                async fetchStockByDate() {
                    this.isLoadingHistory = true;
                    try {
                        const response = await fetch(`/sales/stock/data?date=${this.filterDate}`);
                        const data = await response.json();
                        if (data.success) {
                            this.items = this.items.map(item => {
                                const history = data.history[item.id];
                                return {
                                    ...item,
                                    stokSistem: history ? history.stok : 0,
                                    po: history ? history.stok_po : 0
                                };
                            });
                            this.limit = 50;
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.isLoadingHistory = false;
                    }
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

                get filteredTempItems() {
                    if (this.bulkSearchQuery.trim() === '') {
                        return this.tempItems;
                    }
                    return this.tempItems.filter(item => 
                        item.nama.toLowerCase().includes(this.bulkSearchQuery.toLowerCase()) ||
                        item.kode.toLowerCase().includes(this.bulkSearchQuery.toLowerCase())
                    );
                },

                get displayedTempItems() {
                    return this.filteredTempItems.slice(0, this.bulkLimit);
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
                    fetch('{{ route('sales.stock.add_barang') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            nama_barang: this.addNama,
                            kode_barang: this.addKode,
                            satuan: this.addSatuan
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

                    fetch('{{ route('sales.stock.parse_import') }}', {
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
                            fetch('{{ route('sales.stock.save_import') }}', {
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
                }
            }));
        });
    </script>
    @endpush
</x-layout-users>