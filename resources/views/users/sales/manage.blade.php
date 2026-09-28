@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Manage Sales Data' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }

        /* == Background == */
        .mesh-bg { 
            background-color: #ede9fe;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
        }

        /* == Header Style (Dibuat Lebih Compact) == */
        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1.25rem; 
            padding: 1rem 1.5rem; 
            color: white;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3); 
            position: relative; 
            overflow: hidden;
        }
        .page-header::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(30deg); pointer-events: none;
        }
        .header-content { position: relative; z-index: 1; }

        /* == Cards & Tabs == */
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 1.5rem;
            padding: 1rem 1.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        }
        
        /* == Modern Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 6px 16px 6px 6px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.85rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 0;
            width: fit-content;
        }
        .btn-back-modern:hover { 
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
            color: #1d4ed8;
        }
        .btn-back-modern .icon-circle {
            width: 28px; height: 28px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6;
            font-size: 0.8rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover .icon-circle {
            transform: translateX(-3px);
            background: #EFF6FF;
        }

        /* == Forms (TETAP BESAR UNTUK TAB LAIN) == */
        .modern-input {
            width: 100%; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 0.75rem;
            padding: 0.75rem 1rem; color: #334155; font-size: 0.875rem; transition: all 0.2s ease;
        }
        .modern-input:focus { background: #ffffff; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); outline: none; }
        .modern-label { display: block; font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px; }
        
        select.modern-input {
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 1rem center;
            background-repeat: no-repeat;
            background-size: 1.2em 1.2em;
            padding-right: 2.5rem !important;
        }

        /* == Custom CSS Icons == */
        .search-wrapper { position: relative; width: 100%; display: block; }
        .icon-left { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5; }
        .icon-clear-search { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); z-index: 10; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        
        .icon-clear-datalist { position: absolute; right: 28px; top: 50%; transform: translateY(-50%); z-index: 10; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        
        input::-webkit-search-cancel-button { display: none; }
        
        .pl-icon { padding-left: 40px !important; }
        .pr-icon-search { padding-right: 40px !important; }
        .pr-icon-datalist { padding-right: 52px !important; }

        /* == Button == */
        .btn-primary { background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3); }
        .btn-primary:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 6px 10px -1px rgba(59, 130, 246, 0.4); }
        .btn-danger { background: #ef4444; color: white; padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s ease; }
        .btn-danger:hover { background: #dc2626; }
        .btn-edit { background: #f59e0b; color: white; padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s ease; }
        .btn-edit:hover { background: #d97706; }
    </style>
    @endpush

    <div class="mesh-bg flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800">
        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-10 space-y-4 flex-1 flex flex-col justify-start" x-data="manageData()">

        <div class="w-full flex justify-start mb-3 md:mb-4">
            <x-ui.back-button href="{{ route('sales.index') }}" label="Back to Sales Dashboard" />
        </div>

        <div class="hidden md:block">
            <x-ui.page-header title="Manage Sales Data" subtitle="One hub for all sales data. Manual input, import from Excel, and manage data history." icon="fa-database">
                <x-slot:controls>
                    <div class="flex space-x-1 bg-white/10 p-1 rounded-full border border-white/20 backdrop-blur-md overflow-x-auto shrink-0 w-full xl:w-auto relative z-10">
                        <button @click="activeTab = 'table'" :class="{ 'bg-white text-blue-700 shadow-md': activeTab === 'table', 'text-white hover:bg-white/20': activeTab !== 'table' }" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center">
                            <i class="fas fa-table mr-2"></i> Data History
                        </button>
                        <button @click="activeTab = 'input'" :class="{ 'bg-white text-blue-700 shadow-md': activeTab === 'input', 'text-white hover:bg-white/20': activeTab !== 'input' }" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center">
                            <i class="fas fa-keyboard mr-2"></i> Manual Input
                        </button>
                        <button @click="activeTab = 'import'" :class="{ 'bg-white text-blue-700 shadow-md': activeTab === 'import', 'text-white hover:bg-white/20': activeTab !== 'import' }" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center">
                            <i class="fas fa-cloud-upload-alt mr-2"></i> Import Data
                        </button>
                    </div>
                </x-slot:controls>
            </x-ui.page-header>
        </div>
        <div class="md:hidden space-y-3">
            <div class="mobile-page-header flex items-center justify-between gap-3">
                <div class="relative z-10 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Manage Sales Data</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">Manual, import & history.</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-database"></i>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-1.5 flex items-stretch gap-1">
                <button @click="activeTab = 'table'" :class="activeTab === 'table' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500'" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold">
                    <i class="fas fa-table text-sm"></i><span class="text-[10px] uppercase">History</span>
                </button>
                <button @click="activeTab = 'input'" :class="activeTab === 'input' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500'" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold">
                    <i class="fas fa-keyboard text-sm"></i><span class="text-[10px] uppercase">Input</span>
                </button>
                <button @click="activeTab = 'import'" :class="activeTab === 'import' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500'" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold">
                    <i class="fas fa-cloud-upload-alt text-sm"></i><span class="text-[10px] uppercase">Import</span>
                </button>
            </div>
        </div>

        {{-- [TAB 1] History Table --}}
        <div x-show="activeTab === 'table'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4 flex flex-col w-full">
            
            {{-- Filter Form — glass-card konsisten --}}
            <x-ui.glass-card padding="none" class="!p-6 relative z-10 w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg !rounded-3xl">
                <form action="{{ route('sales.manage') }}" method="GET" id="filterForm">
                    <button type="submit" class="hidden" aria-hidden="true"></button>
                    
                    {{-- Baris 1: 4 form (Search, Customer, Product, PS) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end">
                        <div class="lg:col-span-3 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">General Search</label>
                            <x-ui.search-input name="search" placeholder="Search Name" value="{{ request('search') }}" autocomplete="off" class="w-full" x-data="{ search: '{!! request('search') !!}' }" x-model="search" @input.debounce.1200ms="document.getElementById('filterForm').submit()" />
                        </div>

                        <div class="lg:col-span-3 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Customer</label>
                            <x-ui.filter-combobox name="nama_customer" listId="customer-list-options" placeholder="All Customers" value="{{ request('nama_customer') }}" onchange="document.getElementById('filterForm').submit()" class="w-full" />
                        </div>

                        <div class="lg:col-span-3 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Product</label>
                            <x-ui.filter-combobox name="nama_produk" listId="produk-list-options" placeholder="All Products" value="{{ request('nama_produk') }}" onchange="document.getElementById('filterForm').submit()" class="w-full" />
                        </div>

                        <div class="lg:col-span-3 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">PS</label>
                            <x-ui.filter-combobox name="ps" listId="ps-list-options" placeholder="All PS" value="{{ request('ps') }}" onchange="document.getElementById('filterForm').submit()" class="w-full" />
                        </div>
                    </div>

                    {{-- Baris 2: Date, Month, Year, info total records & aksi --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-12 gap-2 md:gap-3 items-end mt-4">
                        <div class="lg:col-span-3 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Date</label>
                            <x-ui.filter-date name="tanggal" value="{{ request('tanggal') }}" onchange="document.getElementById('filterForm').submit()" class="w-full" />
                        </div>

                        <div class="lg:col-span-2 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Month</label>
                            <x-ui.filter-select name="bulan" onchange="document.getElementById('filterForm').submit()" value="{{ request('bulan') }}" placeholder="All" :options="$listBulan" class="w-full" />
                        </div>
                        
                        <div class="lg:col-span-2 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Year</label>
                            <x-ui.filter-select name="tahun" onchange="document.getElementById('filterForm').submit()" value="{{ request('tahun') }}" placeholder="All" :options="$listTahun" class="w-full" />
                        </div>

                        <div class="lg:col-span-3 md:col-span-1 flex items-end">
                            <x-ui.info class="w-full" icon="fa-database" label="Total Records:" :value="number_format($sales->total(), 0, ',', '.')" />
                        </div>
                        
                        <div class="lg:col-span-2 md:col-span-1 flex gap-2 items-end">
                            @if(request()->hasAny(['search', 'tanggal', 'bulan', 'tahun', 'nama_customer', 'nama_produk', 'ps']))
                                <a href="{{ route('sales.manage') }}" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center border border-slate-200 shadow-sm leading-none" title="Reset Filters">
                                    <i class="fas fa-undo mr-1.5 text-[11px]"></i> Reset
                                </a>
                            @endif
                            <button type="submit" formaction="{{ route('sales.export') }}" class="{{ request()->hasAny(['search', 'tanggal', 'bulan', 'tahun', 'nama_customer', 'nama_produk', 'ps']) ? 'w-1/2' : 'w-full' }} bg-emerald-600 hover:bg-emerald-700 text-white font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center shadow-sm leading-none" title="Export Filtered Results to CSV">
                                <i class="fas fa-file-export mr-1.5 text-[11px]"></i> Export
                            </button>
                        </div>
                    </div>
                </form>
            </x-ui.glass-card>

            {{-- Data Table — putih solid 100% (fix gelap: ui-glass default bg-white/90 tembus ungu #ede9fe) --}}
            <x-ui.glass-card padding="none" class="!p-0 overflow-hidden flex flex-col w-full max-w-full mx-auto !rounded-3xl !bg-white border border-slate-200 shadow-sm" style="background:#ffffff;">
                <div class="overflow-x-auto flex-1 bg-white">
                        <table class="w-full text-sm text-left text-slate-600 bg-white">
                            <thead class="text-xs text-slate-500 uppercase bg-white border-b border-slate-200 font-bold tracking-wider">
                                <tr>
                                    <th class="px-4 py-4 w-10 text-center">
                                        <input type="checkbox" id="check-all" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 cursor-pointer">
                                    </th>
                                    <th class="px-4 py-4 w-12 text-center">No</th>
                                    <th class="px-4 py-4">Date</th>
                                    <th class="px-4 py-4">Customer</th>
                                    <th class="px-4 py-4 text-center">PS</th>
                                    <th class="px-4 py-4">Product</th>
                                    <th class="px-4 py-4">Qty</th>
                                    <th class="px-4 py-4 text-right bg-emerald-50/60 border-l border-emerald-100 text-emerald-700">Net Price</th>
                                    <th class="px-4 py-4 text-center">Action</th>
                                </tr>
                            </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($sales as $index => $item)
                            <tr class="hover:bg-indigo-50/50 transition-colors">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="row-checkbox rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 cursor-pointer">
                                </td>
                                <td class="px-4 py-3 text-center text-slate-400 font-medium">{{ $sales->firstItem() + $index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700">{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d/m/Y') : '-' }}</td>
                                <td class="px-4 py-3 font-bold text-slate-900">{{ $item->customer_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center font-bold text-indigo-600">{{ $item->ps ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-slate-800 font-medium">{{ $item->product_name ?? '-' }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">HNA: Rp {{ number_format($item->base_price, 0, ',', '.') }} | Discount: {{ $item->discount == floor($item->discount) ? number_format($item->discount, 0) : $item->discount }}%</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap"><span class="px-2.5 py-1 bg-slate-100 rounded-lg text-xs font-bold text-slate-600">{{ $item->qty ?? 0 }} {{ $item->unit }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap font-bold text-emerald-700 bg-emerald-50/60 border-l border-emerald-100">Rp {{ number_format($item->net_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-ui.edit-button data-item="{{ json_encode($item) }}" @click="openEditModal(JSON.parse($el.dataset.item))" title="Edit" />
                                        <form action="{{ route('sales.destroy', $item->id) }}" method="POST" id="form-delete-{{ $item->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.delete-button onclick="confirmDelete('{{ $item->id }}')" title="Delete" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="p-0">
                                    <x-ui.empty icon="fa-inbox" title="Tidak ada data" message="Coba ubah kata kunci pencarian, customer, produk, PS, atau filter tanggal/bulan/tahun." />
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 border-t border-slate-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-3">
                    <button type="button" onclick="confirmBulkDelete()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition shadow-sm hidden" id="btn-bulk-delete">
                        <i class="fas fa-trash-alt mr-1"></i> Delete (<span id="selected-count">0</span>)
                    </button>
                    @if($sales->hasPages())
                    <div class="w-full sm:w-auto">{{ $sales->links() }}</div>
                    @endif
                </div>
            </x-ui.glass-card>
        </div>

        {{-- [TAB 2] Manual Data Input — selaras components/ui (ui-label, ui-input, ui-glass) --}}
        <div x-show="activeTab === 'input'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="w-full">
            <x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg !rounded-3xl !p-6">
                <form action="{{ route('sales.store_manual') }}" method="POST" id="manual-sales-form" onsubmit="confirmSubmit(event, 'Save this sales data?')">
                    @csrf
                    <div class="space-y-5">
                        {{-- Baris 1: Date / PS / Customer — selaras filter (gap-3, label 10px) --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="ui-label">Date <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="date" name="tanggal" required class="ui-input-date">
                                </div>
                            </div>
                            <div>
                                <label class="ui-label">Customer Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input list="customer-list-options" type="text" name="nama_customer" required placeholder="Customer Name (e.g. Clinic ABC)" class="ui-input pr-9" autocomplete="off">
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-blue-700"><i class="fas fa-chevron-down text-[10px]"></i></span>
                                </div>
                            </div>
                            <div>
                                <label class="ui-label">PS Name</label>
                                <div class="relative">
                                    <input list="ps-input-options" type="text" name="ps" placeholder="e.g. John Doe" class="ui-input pr-9" autocomplete="off">
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-blue-700"><i class="fas fa-chevron-down text-[10px]"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t-2 border-slate-300 pt-5 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <h4 class="font-bold text-slate-800 text-sm flex items-center">
                                    <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-2.5 shrink-0"><i class="fas fa-box-open text-xs"></i></span>
                                    Product Details
                                </h4>
                                <button type="button" id="tambah-produk-btn" class="inline-flex items-center justify-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 h-[30px] px-3 rounded-lg text-xs font-bold shadow-sm transition-colors whitespace-nowrap">
                                    <i class="fas fa-plus text-[10px]"></i> Add Product Row
                                </button>
                            </div>
                            
                            <div class="space-y-3" id="rincian-produk-container">
                                <!-- Vanilla JS goes here -->
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-center gap-3 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-lg bg-white border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm"><i class="fas fa-coins text-sm"></i></span>
                                    <div>
                                        <h4 class="font-black text-slate-800 text-sm leading-none">Grand Total</h4>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-1">Total Sales Amount</p>
                                    </div>
                                </div>
                                <div class="bg-white border border-slate-200 rounded-lg px-4 h-[38px] inline-flex items-center shadow-sm w-full sm:w-auto justify-center sm:justify-end">
                                    <span class="text-base font-black text-blue-700 tracking-tight" id="grand-total-text">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold h-[38px] px-5 rounded-lg shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                            <i class="fas fa-save text-[11px]"></i> Save Manual Data
                        </button>
                    </div>
                </form>
            </x-ui.glass-card>
        </div>

        {{-- [TAB 3] Import & Export — compact & seimbang (2 col sama tinggi) --}}
        <div x-show="activeTab === 'import'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 w-full max-w-full mx-auto items-stretch">
                {{-- Kiri: Upload — stretch penuh, dropzone flex-1 biar tinggi ngisi --}}
                <x-ui.glass-card class="flex flex-col border-t-4 border-t-emerald-500 shadow-lg !rounded-3xl w-full !p-5 h-full">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center mb-3 shrink-0">
                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm mr-2.5 shrink-0"><i class="fas fa-cloud-upload-alt"></i></span>
                        Import from Excel/CSV
                    </h3>
                    <form action="{{ route('sales.import_excel') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3 flex-1" onsubmit="confirmSubmit(event, 'Are you sure you want to import data from this file?')">
                        @csrf
                        <div class="flex-1 flex flex-col">
                            <label class="ui-label mb-1">Choose File (.xlsx, .csv)</label>
                            <label class="flex-1 relative border border-dashed border-emerald-200 rounded-xl bg-emerald-50/40 hover:bg-emerald-50 transition-colors px-4 flex flex-col items-center justify-center text-center cursor-pointer overflow-hidden min-h-[148px] py-5 group">
                                <input type="file" name="file" accept=".xlsx, .xls, .csv" required
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                    onchange="document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : 'Click or Drop file here';">
                                <span class="w-10 h-10 rounded-xl bg-white border border-emerald-100 flex items-center justify-center text-emerald-500 mb-2.5 shadow-sm group-hover:scale-105 transition-transform"><i class="fas fa-file-excel text-base"></i></span>
                                <p id="fileName" class="text-xs font-bold text-slate-700 truncate max-w-full px-2">Click or Drop file here</p>
                                <p class="text-[10px] text-slate-400 font-medium mt-1">.xlsx, .xls, .csv — max 10MB</p>
                            </label>
                        </div>
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold h-[38px] px-4 rounded-lg text-xs transition-all shadow-sm hover:shadow-md inline-flex justify-center items-center gap-2 shrink-0">
                            <i class="fas fa-upload text-[11px]"></i> Upload Data
                        </button>
                    </form>
                </x-ui.glass-card>

                {{-- Kanan: 2 card ditumpuk, tinggi total = kiri (flex-1 biar seimbang) --}}
                <div class="flex flex-col gap-4 h-full">
                    <x-ui.glass-card class="bg-slate-50 border-slate-200 w-full !p-4 shrink-0">
                        <h4 class="font-bold text-slate-800 mb-2 flex items-center text-xs"><i class="fas fa-file-download mr-2 text-blue-500 text-[11px]"></i> Download Template</h4>
                        <p class="text-xs text-slate-500 mb-3 leading-relaxed">Download empty CSV template with column formats adjusted to the system.</p>
                        <a href="{{ route('sales.download_template') }}" class="inline-flex items-center justify-center w-full bg-white border border-blue-200 hover:border-blue-400 hover:bg-blue-50 text-blue-600 font-bold h-[36px] px-3 rounded-lg text-xs transition-all shadow-sm gap-1.5">
                            <i class="fas fa-download text-[11px]"></i> Download CSV Template
                        </a>
                    </x-ui.glass-card>

                    <x-ui.glass-card class="bg-blue-50 border-blue-200 w-full !p-4 flex-1 flex flex-col">
                        <h4 class="font-bold text-blue-800 mb-2.5 flex items-center text-xs shrink-0"><i class="fas fa-info-circle mr-2 text-[11px]"></i> Import Instructions</h4>
                        <ul class="text-[11px] text-blue-700 space-y-2 list-disc list-inside font-medium leading-relaxed">
                            <li>Use the <b>latest CSV template</b> from the button above.</li>
                            <li>Start from <b>Row 3 (Cell A3)</b> — Row 1 & 2 <b>auto-ignored</b>.</li>
                            <li><b class="text-blue-800">PASTE:</b> Right-click → <b>Paste Values & Number Formatting</b> in Excel.</li>
                            <li>Numbers support <b>free format</b> e.g. <code class="bg-white px-1 py-0.5 rounded border border-blue-100 text-[10px]">Rp 529.500</code> / <code class="bg-white px-1 py-0.5 rounded border border-blue-100 text-[10px]">12.69%</code>.</li>
                            <li class="bg-white/70 -mx-1 px-2 py-1.5 rounded-lg border border-blue-100"><b class="text-red-600">Auto-Sync:</b> Month detected from Date → <b>delete & replace</b> that month's data.</li>
                        </ul>
                    </x-ui.glass-card>
                </div>
            </div>
        </div>

        {{-- Edit Data Modal --}}
        <div x-cloak x-show="showEditModal" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Background backdrop --}}
            <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 transition-opacity" style="background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);" aria-hidden="true" @click="showEditModal = false"></div>

            <div class="fixed inset-0 z-[110] w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    
                    {{-- Modal panel --}}
                    <div x-show="showEditModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full" style="max-width: 800px;">
                        
                        <form :action="editUrl" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="flex justify-between items-center mb-5 pb-4 border-b border-slate-100">
                                    <h3 class="text-xl leading-6 font-bold text-slate-800" id="modal-title">Edit Sales Data</h3>
                                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                                        <i class="fas fa-times text-xl"></i>
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="modern-label">Date</label><input type="date" name="tanggal" x-model="formData.tanggal" class="modern-input"></div>
                                    <div><label class="modern-label">Customer</label><input list="customer-list-options" type="text" name="nama_customer" x-model="formData.nama_customer" class="modern-input" autocomplete="off"></div>
                                    
                                    <div><label class="modern-label">PS</label><input list="ps-list-options" type="text" name="ps" x-model="formData.ps" class="modern-input" autocomplete="off"></div>
                                    <div><label class="modern-label">Product</label><input list="produk-list-options" type="text" name="nama_produk" x-model="formData.nama_produk" class="modern-input" autocomplete="off"></div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div><label class="modern-label">Qty</label><input type="number" name="qty" x-model="formData.qty" @input="calculateNett()" class="modern-input"></div>
                                        <div><label class="modern-label">Unit</label><input list="satuan-list-options" type="text" name="satuan" x-model="formData.satuan" class="modern-input" autocomplete="off"></div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div><label class="modern-label">HNA (Rp)</label><input type="number" name="hna" x-model="formData.hna" @input="calculateNett()" step="0.01" class="modern-input"></div>
                                        <div><label class="modern-label">Discount (%)</label><input type="number" name="diskon" x-model="formData.diskon" @input="calculateNett()" step="0.01" class="modern-input"></div>
                                    </div>
                                    
                                    <div class="md:col-span-2 mt-2 border-t border-slate-100 pt-4">
                                        <label class="block text-sm font-bold text-indigo-600 uppercase mb-2">Total Net Price (Rp)</label>
                                        <input type="text" :value="formatRupiah(formData.harga_nett)" class="modern-input !bg-indigo-50 !border-indigo-200 font-black text-2xl !py-4" readonly>
                                        <input type="hidden" name="harga_nett" x-model="formData.harga_nett">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto transition-all">
                                    Save Changes
                                </button>
                                <button type="button" @click="showEditModal = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-all">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    {{-- Datalist Options --}}
    <datalist id="ps-list-options">
        @foreach($listPs as $ps_item)
            <option value="{{ $ps_item }}"></option>
        @endforeach
    </datalist>
    <datalist id="ps-input-options">
        @foreach($listPs as $ps_item)
            <option value="{{ $ps_item }}"></option>
        @endforeach
        @foreach($listUserPs as $ps_user)
            @if(!$listPs->contains($ps_user))
                <option value="{{ $ps_user }}"></option>
            @endif
        @endforeach
    </datalist>
    <datalist id="customer-list-options">
        @foreach($listCustomer as $cus)
            <option value="{{ $cus }}"></option>
        @endforeach
    </datalist>
    <datalist id="produk-list-options">
        @foreach($listProduk as $prod)
            <option value="{{ $prod }}"></option>
        @endforeach
    </datalist>
    <datalist id="satuan-list-options">
        @foreach($listSatuan as $sat)
            <option value="{{ $sat }}"></option>
        @endforeach
    </datalist>

    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function manageData() {
            return {
                activeTab: localStorage.getItem('sales_active_tab') || "{{ session('active_tab', request()->hasAny(['search', 'bulan', 'tahun', 'nama_customer', 'nama_produk', 'ps']) ? 'table' : 'table') }}",
                showEditModal: false,
                editUrl: '',
                formData: { id: '', tanggal: '', bulan: '', nama_customer: '', ps: '', nama_produk: '', qty: 0, satuan: '', hna: 0, diskon: 0, harga_nett: 0 },
                
                init() {
                    this.$watch('activeTab', value => {
                        localStorage.setItem('sales_active_tab', value);
                    });
                },
                openEditModal(item) {
                    this.formData = {
                        id: item.id || '',
                        tanggal: item.date || '',
                        bulan: item.month || '',
                        nama_customer: item.customer_name || '',
                        ps: item.ps || '',
                        nama_produk: item.product_name || '',
                        qty: item.qty || 0,
                        satuan: item.unit || '',
                        hna: item.base_price || 0,
                        diskon: item.discount || 0,
                        harga_nett: item.net_price || 0,
                    };
                    if(this.formData.tanggal) this.formData.tanggal = this.formData.tanggal.split('T')[0];
                    this.editUrl = `{{ url('sales') }}/${item.id}`;
                    this.showEditModal = true;
                },
                calculateNett() {
                    const qty = parseFloat(this.formData.qty) || 0;
                    const hna = parseFloat(this.formData.hna) || 0;
                    const diskon = parseFloat(this.formData.diskon) || 0;
                    const subtotal = qty * hna;
                    const diskonNominal = subtotal * (diskon / 100);
                    this.formData.harga_nett = subtotal - diskonNominal;
                },
                formatRupiah(number) {
                    if(!number && number !== 0) return '';
                    return new Intl.NumberFormat('id-ID').format(Math.round(number));
                }
            }
        }

        // Kalkulasi Harga Nett (Form Manual)
        document.addEventListener('DOMContentLoaded', function() {
            const qtyInput = document.getElementById('input-qty');
            const hnaInput = document.getElementById('input-hna');
            const displayHna = document.getElementById('display-hna');
            const diskonInput = document.getElementById('input-diskon');
            const hargaNettInput = document.getElementById('input-harga-nett');
            const displayHargaNett = document.getElementById('display-harga-nett');

            function formatRupiah(number) { return new Intl.NumberFormat('id-ID').format(number); }
            function parseRupiah(text) {
                if (!text) return 0;
                return parseFloat(text.replace(/[^0-9,-]+/g,"").replace(',', '.')) || 0;
            }

            if (displayHna) {
                displayHna.addEventListener('input', function(e) {
                    let val = parseRupiah(this.value);
                    this.value = val ? formatRupiah(val) : '';
                    hnaInput.value = val;
                    calculateHargaNett();
                });
            }

            function calculateHargaNett() {
                const qty = parseFloat(qtyInput.value) || 0;
                const hna = parseFloat(hnaInput.value) || 0;
                const diskon = parseFloat(diskonInput.value) || 0;
                const totalAwal = hna * qty;
                const potonganDiskon = totalAwal * (diskon / 100);
                const hargaNett = totalAwal - potonganDiskon;

                if (hargaNett > 0) {
                    hargaNettInput.value = hargaNett.toFixed(2);
                    displayHargaNett.value = formatRupiah(hargaNett);
                } else {
                    hargaNettInput.value = ''; displayHargaNett.value = '';
                }
            }
            if (qtyInput) qtyInput.addEventListener('input', calculateHargaNett);
            if (diskonInput) diskonInput.addEventListener('input', calculateHargaNett);
        });

        // SweetAlert Delete Confirmation
        function confirmDelete(id) {
            Swal.fire({
                title: 'Delete Data?',
                text: "Deleted sales data cannot be recovered!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-delete-' + id).submit();
                }
            });
        }

        // Vanilla JS Logic untuk Form Input Multi Produk
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('rincian-produk-container');
            const addBtn = document.getElementById('tambah-produk-btn');
            const grandTotalEl = document.getElementById('grand-total-text');
            const form = document.getElementById('manual-sales-form');
            let productCounter = 0;
            let isRestoring = false;

            function formatRupiah(num) {
                return new Intl.NumberFormat('id-ID').format(Math.round(num));
            }

            function calculateGrandTotal() {
                let total = 0;
                document.querySelectorAll('.input-harga-nett').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                if(grandTotalEl) grandTotalEl.textContent = 'Rp ' + formatRupiah(total);
                
                saveFormToLocalStorage();
            }

            function saveFormToLocalStorage() {
                if (!form || isRestoring) return;
                const products = [];
                container.querySelectorAll('.p-3').forEach(row => {
                    const namaInput = row.querySelector('input[name="nama_produk[]"]');
                    const qtyInput = row.querySelector('.input-qty');
                    const satuanInput = row.querySelector('input[name="satuan[]"]');
                    const hnaRawInput = row.querySelector('.input-hna-raw');
                    const diskonInput = row.querySelector('.input-diskon');
                    
                    if (namaInput) {
                        products.push({
                            nama_produk: namaInput.value,
                            qty: qtyInput ? qtyInput.value : '',
                            satuan: satuanInput ? satuanInput.value : '',
                            hna: hnaRawInput ? hnaRawInput.value : 0,
                            diskon: diskonInput ? diskonInput.value : ''
                        });
                    }
                });
                
                const formData = {
                    tanggal: form.querySelector('input[name="tanggal"]').value,
                    ps: form.querySelector('input[name="ps"]').value,
                    nama_customer: form.querySelector('input[name="nama_customer"]').value,
                    products: products
                };
                
                localStorage.setItem('manual_sales_draft', JSON.stringify(formData));
            }

            function restoreFormFromLocalStorage() {
                if (!form) return;
                const saved = localStorage.getItem('manual_sales_draft');
                if (!saved) {
                    addProductRow(); // Initialize with one empty row if no draft exists
                    return;
                }
                
                try {
                    isRestoring = true;
                    const formData = JSON.parse(saved);
                    form.querySelector('input[name="tanggal"]').value = formData.tanggal || '';
                    form.querySelector('input[name="ps"]').value = formData.ps || '';
                    form.querySelector('input[name="nama_customer"]').value = formData.nama_customer || '';
                    
                    container.innerHTML = ''; // Clear container
                    
                    if (formData.products && formData.products.length > 0) {
                        for (let i = formData.products.length - 1; i >= 0; i--) {
                            addProductRow(formData.products[i]);
                        }
                    } else {
                        addProductRow();
                    }
                } catch (e) {
                    console.error('Error restoring draft:', e);
                    addProductRow();
                } finally {
                    isRestoring = false;
                    calculateGrandTotal();
                }
            }

            function addProductRow(initialData = null) {
                productCounter++;
                const rowId = 'product-row-' + Date.now() + Math.random().toString(36).substr(2, 9);
                const row = document.createElement('div');
                row.className = 'p-3 bg-white border border-slate-200 rounded-xl relative shadow-sm hover:border-blue-200 transition-colors animate-[fadeIn_0.3s_ease-in-out]';
                row.id = rowId;
                
                row.innerHTML = `
                    <div class="flex justify-between items-center mb-2.5 border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-100 px-2 py-1 rounded-md product-number-badge">Product #${productCounter}</span>
                            <div class="flex items-center gap-1.5 bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-md text-[10px] shadow-sm">
                                <span class="font-bold text-slate-500 uppercase tracking-wider">Subtotal:</span>
                                <span class="text-blue-700 font-black subtotal-text">Rp 0</span>
                                <input type="hidden" name="harga_nett[]" value="0" class="input-harga-nett">
                            </div>
                        </div>
                        <button type="button" class="btn-remove-product inline-flex items-center gap-1 text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 px-2.5 h-[26px] rounded-lg transition-colors shadow-sm" title="Delete this product">
                            <i class="fas fa-trash-alt text-[10px]"></i> Delete
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                        <div>
                            <label class="ui-label">Product Name <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input list="produk-list-options" type="text" name="nama_produk[]" required placeholder="Product Name" class="ui-input ui-input--sm pr-8" autocomplete="off">
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-blue-700"><i class="fas fa-chevron-down text-[10px]"></i></span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="ui-label">Qty <span class="text-red-500">*</span></label>
                                <input type="number" name="qty[]" min="1" required placeholder="0" class="ui-input ui-input--sm input-qty">
                            </div>
                            <div>
                                <label class="ui-label">Unit</label>
                                <div class="relative">
                                    <input list="satuan-list-options" type="text" name="satuan[]" placeholder="Pcs/Box" class="ui-input ui-input--sm pr-8" autocomplete="off">
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-blue-700"><i class="fas fa-chevron-down text-[10px]"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="ui-label">HNA (Rp)</label>
                                <input type="text" placeholder="0" class="ui-input ui-input--sm input-hna-display" autocomplete="off">
                                <input type="hidden" name="hna[]" class="input-hna-raw" value="0">
                            </div>
                            <div>
                                <label class="ui-label">Discount (%)</label>
                                <input type="number" name="diskon[]" step="0.01" min="0" placeholder="0" class="ui-input ui-input--sm input-diskon">
                            </div>
                        </div>
                    </div>
                `;
                
                container.prepend(row);
                
                const qtyInput = row.querySelector('.input-qty');
                const hnaDisplay = row.querySelector('.input-hna-display');
                const hnaRaw = row.querySelector('.input-hna-raw');
                const diskonInput = row.querySelector('.input-diskon');
                const subtotalText = row.querySelector('.subtotal-text');
                const hargaNettInput = row.querySelector('.input-harga-nett');
                
                function calculateRow() {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const hna = parseFloat(hnaRaw.value) || 0;
                    const diskon = parseFloat(diskonInput.value) || 0;
                    const subtotal = qty * hna;
                    const diskonNominal = subtotal * (diskon / 100);
                    const finalNett = Math.max(0, subtotal - diskonNominal);
                    
                    hargaNettInput.value = finalNett;
                    subtotalText.textContent = 'Rp ' + formatRupiah(finalNett);
                    calculateGrandTotal();
                }
                
                hnaDisplay.addEventListener('input', function() {
                    let val = this.value.replace(/[^0-9]/g, '');
                    if (val !== "") {
                        this.value = formatRupiah(val);
                        hnaRaw.value = val;
                    } else {
                        this.value = "";
                        hnaRaw.value = 0;
                    }
                    calculateRow();
                });
                
                qtyInput.addEventListener('input', calculateRow);
                diskonInput.addEventListener('input', calculateRow);

                row.querySelector('input[name="nama_produk[]"]').addEventListener('input', saveFormToLocalStorage);
                row.querySelector('input[name="satuan[]"]').addEventListener('input', saveFormToLocalStorage);
                
                const removeBtn = row.querySelector('.btn-remove-product');
                removeBtn.addEventListener('click', function() {
                    row.remove();
                    updateProductNumbers();
                    calculateGrandTotal();
                    checkRemoveButtons();
                });

                if (initialData) {
                    row.querySelector('input[name="nama_produk[]"]').value = initialData.nama_produk || '';
                    row.querySelector('.input-qty').value = initialData.qty || '';
                    row.querySelector('input[name="satuan[]"]').value = initialData.satuan || '';
                    row.querySelector('.input-hna-raw').value = initialData.hna || 0;
                    row.querySelector('.input-hna-display').value = initialData.hna ? formatRupiah(initialData.hna) : '';
                    row.querySelector('.input-diskon').value = initialData.diskon || '';
                    calculateRow();
                }
                
                checkRemoveButtons();
            }
            
            function updateProductNumbers() {
                const badges = container.querySelectorAll('.product-number-badge');
                badges.forEach((badge, index) => {
                    badge.textContent = 'Product #' + (index + 1);
                });
                productCounter = badges.length;
            }
            
            function checkRemoveButtons() {
                const removeBtns = container.querySelectorAll('.btn-remove-product');
                if(removeBtns.length <= 1) {
                    removeBtns.forEach(btn => btn.style.display = 'none');
                } else {
                    removeBtns.forEach(btn => btn.style.display = 'flex');
                }
            }
            
            if(addBtn && container) {
                addBtn.addEventListener('click', () => addProductRow());
                restoreFormFromLocalStorage();
            }

            if (form) {
                form.addEventListener('input', function(e) {
                    if (e.target.name === 'tanggal' || e.target.name === 'ps' || e.target.name === 'nama_customer') {
                        saveFormToLocalStorage();
                    }
                });
                form.addEventListener('change', function(e) {
                    if (e.target.name === 'tanggal' || e.target.name === 'ps' || e.target.name === 'nama_customer') {
                        saveFormToLocalStorage();
                    }
                });
                form.addEventListener('submit', function() {
                    localStorage.removeItem('manual_sales_draft');
                });
            }
        });

        // Bulk Delete Logic
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('check-all');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const btnBulkDelete = document.getElementById('btn-bulk-delete');
            const selectedCount = document.getElementById('selected-count');

            function updateBulkDeleteButton() {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                if (btnBulkDelete && selectedCount) {
                    if (checkedCount > 0) {
                        btnBulkDelete.classList.remove('hidden');
                        selectedCount.textContent = checkedCount;
                    } else {
                        btnBulkDelete.classList.add('hidden');
                    }
                }
                
                if (checkAll && rowCheckboxes.length > 0) {
                    checkAll.checked = checkedCount === rowCheckboxes.length;
                }
            }

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    rowCheckboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                    updateBulkDeleteButton();
                });
            }

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const allChecked = document.querySelectorAll('.row-checkbox:checked').length === rowCheckboxes.length;
                    if (checkAll) checkAll.checked = allChecked;
                    updateBulkDeleteButton();
                });
            });
        });

        function confirmBulkDelete() {
            const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'Delete ' + selectedIds.length + ' Selected Data?',
                text: "Deleted sales data cannot be recovered!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete all!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("sales.bulk_destroy") }}';
                    
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    form.appendChild(method);
                    
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
    @endpush
</x-layout-users>