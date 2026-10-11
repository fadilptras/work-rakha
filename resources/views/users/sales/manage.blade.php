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

        /* Panel riwayat harga: bottom-sheet di mobile, melayang di kanan modal pada desktop.
           Posisi desktop dihitung JS dari posisi modal (fixed + left/top eksplisit).
           CSS biasa (tidak tergantung hasil build Tailwind). */
        #sales-item-history { position: fixed; left: 1rem; right: 1rem; bottom: 1rem; z-index: 5; }
        #sales-item-history .hist-card { max-height: 42vh; }
        #price-history-body { min-height: 64px; }
        @media (min-width: 1024px) {
            #sales-item-history { width: 21rem; }
            #sales-item-history .hist-card { max-height: calc(100vh - 260px); }
        }

        /* == Pola forecast murni: filter & thead NEMPEL MATI dengan card,
           tabel scroll di dalam box-nya sendiri — tidak ada elemen melayang == */
        .manage-table-scroll { overflow: auto; max-height: 72vh; min-height: 380px; }

        /* Loader infinite scroll: overlay melayang (tidak menambah tinggi tabel = tidak menggeser). */
        #manage-table-scroll { position: relative; }
        #scroll-loader { position: absolute; left: 50%; bottom: 14px; transform: translateX(-50%); z-index: 20; opacity: 0; visibility: hidden; transition: opacity .25s ease, visibility .25s; pointer-events: none; }
        #scroll-loader.show { opacity: 1; visibility: visible; transform: translateX(-50%); }
        #scroll-loader .pill { display: inline-flex; align-items: center; gap: 8px; background: rgba(30, 41, 59, .92); color: #e2e8f0; font-size: 11px; font-weight: 700; padding: 8px 16px; border-radius: 9999px; box-shadow: 0 8px 24px rgba(0, 0, 0, .25); }
        @keyframes rowIn { from { opacity: 0; } to { opacity: 1; } }
        .row-appended { animation: rowIn .3s ease both; }
        @media (prefers-reduced-motion: reduce) { .row-appended { animation: none; } #scroll-loader { transition: none; } }
        .manage-table-scroll table.manage-table { border-collapse: separate; border-spacing: 0; }
        .manage-table-scroll thead { background-color: #f8fafc; }
        .manage-table-scroll thead th {
            position: sticky; top: 0; z-index: 20;
            background-color: #f8fafc;
            background-clip: padding-box;
            box-shadow: 0 1px 0 #e2e8f0, 0 6px 10px -6px rgba(15,23,42,.18);
            isolation: isolate;
        }
        .manage-table-scroll thead th.bg-emerald-50\/60 { background-color: #ecfdf5 !important; }
        @media (max-width: 767px) {
            .manage-table-scroll { max-height: 60vh; min-height: 240px; }
        }

        /* == Button == */
        .btn-primary { background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3); }
        .btn-primary:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 6px 10px -1px rgba(59, 130, 246, 0.4); }
        .btn-danger { background: #ef4444; color: white; padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s ease; }
        .btn-danger:hover { background: #dc2626; }
        .btn-edit { background: #f59e0b; color: white; padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s ease; }
        .btn-edit:hover { background: #d97706; }
    </style>
    @endpush

    <div class="mesh-bg flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800">
        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start" x-data="manageData()">

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
            
            {{-- Filter + Tabel SATU card — filter sticky, thead sticky, yang jalan cuma baris data --}}
            <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full max-w-full mx-auto !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg" style="background:#ffffff;">
                <div class="bg-white">
                <form action="{{ route('sales.manage') }}" method="GET" id="filterForm" class="p-6 pb-5 border-b border-slate-100">
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
                </div>

                <div class="flex-1 bg-white manage-table-scroll" id="manage-table-scroll">
                        <table class="manage-table w-full text-sm text-left text-slate-600 bg-white">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
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
                        <tbody id="sales-table-body" data-last-page="{{ $sales->lastPage() }}" class="divide-y divide-slate-100">
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
                    <div id="scroll-loader" aria-hidden="true"><span class="pill"><i class="fas fa-spinner fa-spin"></i>Memuat baris berikutnya…</span></div>
                </div>

                <div id="manage-table-footer" class="px-6 py-4 border-t border-slate-200 bg-white hidden">
                    <button type="button" onclick="confirmBulkDelete()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition shadow-sm" id="btn-bulk-delete">
                        <i class="fas fa-trash-alt mr-1"></i> Delete (<span id="selected-count">0</span>)
                    </button>
                </div>
            </x-ui.glass-card>
        </div>

        {{-- [TAB 2] Manual Data Input — selaras components/ui (ui-label, ui-input, ui-glass) --}}
        <div x-show="activeTab === 'input'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="w-full">
            <x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg !rounded-3xl !p-6">
                <form action="{{ route('sales.store_manual') }}" method="POST" id="manual-sales-form">
                    @csrf
                    <div class="space-y-5">
                        {{-- Baris 1: Date / PS / Customer — selaras filter (gap-3, label 10px) --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="ui-label">Date <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="date" name="tanggal" required value="{{ old('tanggal', date('Y-m-d')) }}" class="ui-input-date">
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
                                    <i class="fas fa-plus text-[10px]"></i> Add Product
                                </button>
                            </div>

                            {{-- Cari produk: klik hasil -> popup detail --}}
                            <div class="relative">
                                <div class="relative">
                                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-blue-300 text-xs pointer-events-none"></i>
                                    <input type="text" id="product-search" placeholder="Type product name, then click a result…" title="Type a product name, then click a result to fill details via popup." class="ui-search !h-[32px] !bg-white hover:!bg-white !text-slate-700" style="padding-left:36px;" autocomplete="off">
                                </div>
                                <div id="product-search-results" class="hidden absolute left-0 right-0 z-30 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden max-h-64 overflow-y-auto divide-y divide-slate-100"></div>
                            </div>

                            <div class="border border-slate-200 rounded-xl bg-white" style="overflow:hidden;">
                                <div style="max-height:46vh;overflow:auto;">
                                    <table class="w-full text-xs" style="min-width:640px;">
                                        <thead>
                                            <tr class="bg-slate-50/80 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200" style="position:sticky;top:0;background-color:#f8fafc;z-index:10;">
                                                <th class="px-3 py-2 text-center font-bold" style="width:36px;">No</th>
                                                <th class="px-3 py-2 text-left font-bold">Product</th>
                                                <th class="px-3 py-2 text-right font-bold" style="width:80px;">Qty</th>
                                                <th class="px-3 py-2 text-right font-bold" style="width:110px;">HNA</th>
                                                <th class="px-3 py-2 text-center font-bold" style="width:70px;">Disc%</th>
                                                <th class="px-3 py-2 text-right font-bold" style="width:110px;">Subtotal</th>
                                                <th class="px-3 py-2 text-center font-bold" style="width:76px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rincian-produk-container" class="divide-y divide-slate-100"></tbody>
                                    </table>
                                </div>
                                <div id="empty-produk" class="px-4 py-3 text-center bg-slate-50/50 border-t border-dashed border-slate-200">
                                    <p class="text-xs font-bold text-slate-500"><i class="fas fa-box-open text-slate-300 mr-1.5"></i>Belum ada produk — cari di atas lalu klik untuk menambahkan.</p>
                                </div>
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

                    {{-- Popup detail produk (dibuka dari hasil pencarian / tombol tambah / ubah) --}}
                    <x-ui.modal id="sales-item-modal" maxWidth="max-w-lg" title="Tambah Produk" subtitle="Isi jumlah, satuan, HNA & diskon, lalu simpan ke tabel." icon="fa-box-open">
                        <div>
                            <label class="ui-label" for="m-nama">Nama Produk <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input list="produk-list-options" type="text" id="m-nama" class="ui-input ui-input--sm !bg-white !text-slate-800 pr-icon-datalist" placeholder="Pilih / ketik nama produk…" autocomplete="off">
                                <button type="button" id="m-nama-clear" class="icon-clear-datalist hidden text-slate-300 hover:text-slate-500 transition-colors" title="Bersihkan nama produk"><i class="fas fa-times-circle text-sm"></i></button>
                            </div>
                            <p class="text-[10px] text-slate-400 font-semibold mt-1">Pilih produk untuk melihat riwayat harga ke customer ini di panel kanan.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="ui-label" for="m-qty">Jumlah (Qty) <span class="text-red-500">*</span></label>
                                <input type="number" id="m-qty" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="0" min="1">
                            </div>
                            <div>
                                <label class="ui-label" for="m-satuan">Satuan</label>
                                <input list="satuan-list-options" type="text" id="m-satuan" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="Pcs / Box…" autocomplete="off">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="ui-label" for="m-hna">HNA (Rp)</label>
                                <input type="text" id="m-hna" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="0" inputmode="numeric" autocomplete="off">
                            </div>
                            <div>
                                <label class="ui-label" for="m-diskon">Diskon (%)</label>
                                <input type="number" id="m-diskon" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="0" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="rounded-xl border border-blue-100 bg-blue-50/60 px-4 py-2.5 flex items-center justify-between gap-2">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nett</span>
                            <span id="m-nett" class="text-base font-black text-blue-700">Rp 0</span>
                        </div>
                        <p id="m-error" class="hidden text-[11px] font-bold text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2.5"></p>
                        <x-slot:footer>
                            <x-ui.button variant="secondary" size="sm" data-modal-close>Batal</x-ui.button>
                            <x-ui.button variant="primary" size="sm" icon="fa-check" id="m-save">Simpan ke Tabel</x-ui.button>
                        </x-slot:footer>
                    </x-ui.modal>

                    {{-- Panel riwayat harga: dipindah ke dalam container popup via JS agar se-layer
                         (tidak tertutup blur) — di kanan modal pada desktop, bottom-sheet di mobile --}}
                    <div id="sales-item-history" class="hidden">
                        <div class="hist-card bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
                            <div class="px-4 py-3 border-b border-slate-100 flex items-start justify-between gap-2 bg-amber-50/60 shrink-0">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0"><i class="fas fa-clock-rotate-left text-xs"></i></span>
                                    <div class="min-w-0">
                                        <h5 class="font-bold text-xs text-slate-800 leading-tight">Riwayat Harga</h5>
                                        <p id="price-history-sub" class="text-[10px] text-slate-400 font-medium truncate mt-0.5">—</p>
                                    </div>
                                </div>
                                <button type="button" id="price-history-close" class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0" title="Tutup"><i class="fas fa-times text-xs"></i></button>
                            </div>
                            <div id="price-history-body" class="overflow-y-auto divide-y divide-slate-100"></div>
                            <div class="px-4 py-2 border-t border-slate-100 bg-slate-50/60 shrink-0">
                                <p class="text-[10px] text-slate-400 font-semibold">6 transaksi terakhir. Klik <span class="text-blue-600 font-bold">Pakai</span> untuk mengisi HNA.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Draft manual hanya dibersihkan saat save SUKSES di tab ini.
                        Validasi gagal / sukses import tidak ikut menghapus (data tetap bisa dipulihkan). --}}
                    @if(session('success') && session('active_tab') === 'input')
                    <script>
                        try { localStorage.removeItem('manual_sales_draft'); } catch (e) {}
                    </script>
                    @endif

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
                                    
                                    <div><label class="modern-label">PS</label><input list="ps-input-options" type="text" name="ps" x-model="formData.ps" class="modern-input" autocomplete="off"></div>
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
                    window._manageAlpine = this;
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

        // Dipakai tombol Ubah pada baris hasil infinite scroll (di luar jangkauan Alpine).
        function openEditItem(btn) {
            try {
                const item = JSON.parse(btn.getAttribute('data-item') || '{}');
                if (window._manageAlpine && typeof window._manageAlpine.openEditModal === 'function') {
                    window._manageAlpine.openEditModal(item);
                }
            } catch (e) {}
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
            const emptyProduk = document.getElementById('empty-produk');
            let isRestoring = false;
            let editingRow = null;

            const mNama = document.getElementById('m-nama');
            const mQty = document.getElementById('m-qty');
            const mSatuan = document.getElementById('m-satuan');
            const mHnaDisplay = document.getElementById('m-hna');
            const mHnaRaw = { value: '0' };
            const mDiskon = document.getElementById('m-diskon');
            const mNett = document.getElementById('m-nett');
            const mErr = document.getElementById('m-error');
            const mSave = document.getElementById('m-save');
            const searchInput = document.getElementById('product-search');
            const searchBox = document.getElementById('product-search-results');
            const namaMaster = Array.from(document.querySelectorAll('#produk-list-options option')).map(o => o.value).filter(Boolean);

            function updateEmpty() {
                if (!emptyProduk || !container) return;
                emptyProduk.style.display = container.querySelectorAll('.row-manual').length ? 'none' : 'block';
            }

            function showModalErr(msg) {
                if (!mErr) return;
                if (!msg) { mErr.classList.add('hidden'); mErr.textContent = ''; return; }
                mErr.textContent = msg;
                mErr.classList.remove('hidden');
            }

            function setModalTitle(t) {
                const el = document.getElementById('sales-item-modal-title');
                if (el) el.textContent = t;
            }

            function refreshModalNett() {
                const qty = parseFloat(mQty && mQty.value) || 0;
                const hna = parseFloat(mHnaRaw.value) || 0;
                const disc = parseFloat(mDiskon && mDiskon.value) || 0;
                const nett = Math.max(0, qty * hna * (1 - disc / 100));
                if (mNett) mNett.textContent = 'Rp ' + formatRupiah(nett);
                return nett;
            }

            function openItemModal(prefill, row) {
                editingRow = row || null;
                setModalTitle(editingRow ? 'Ubah Produk' : 'Tambah Produk');
                const p = prefill || {};
                const hidden = function (name) {
                    const el = editingRow ? editingRow.querySelector('input[name="' + name + '"]') : null;
                    return el ? el.value : '';
                };
                if (mNama) mNama.value = p.nama_produk !== undefined ? p.nama_produk : hidden('nama_produk[]');
                if (mQty) mQty.value = p.qty !== undefined ? p.qty : hidden('qty[]');
                if (mSatuan) mSatuan.value = p.satuan !== undefined ? p.satuan : hidden('satuan[]');
                const hnaVal = p.hna !== undefined ? p.hna : hidden('hna[]');
                mHnaRaw.value = hnaVal || '0';
                if (mHnaDisplay) mHnaDisplay.value = hnaVal ? formatRupiah(hnaVal) : '';
                if (mDiskon) mDiskon.value = p.diskon !== undefined ? p.diskon : hidden('diskon[]');
                showModalErr('');
                refreshModalNett();
                syncNamaClear();
                if (typeof uiModalOpen === 'function') uiModalOpen('sales-item-modal');
                else { const m = document.getElementById('sales-item-modal'); if (m) { m.classList.remove('hidden'); m.classList.add('flex'); } }
                setTimeout(function () { if (mNama) mNama.focus(); }, 250);
                scheduleHistoryLoad();
            }

            function closeItemModal() {
                if (typeof uiModalClose === 'function') uiModalClose('sales-item-modal');
                else { const m = document.getElementById('sales-item-modal'); if (m) { m.classList.add('hidden'); m.classList.remove('flex'); } }
                hideHistory();
                editingRow = null;
            }

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
                container.querySelectorAll('.row-manual').forEach(row => {
                    const namaInput = row.querySelector('input[name="nama_produk[]"]');
                    const qtyInput = row.querySelector('input[name="qty[]"]');
                    const satuanInput = row.querySelector('input[name="satuan[]"]');
                    const hnaRawInput = row.querySelector('input[name="hna[]"]');
                    const diskonInput = row.querySelector('input[name="diskon[]"]');
                    
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
                if (!saved) { updateEmpty(); calculateGrandTotal(); return; }
                
                try {
                    isRestoring = true;
                    const formData = JSON.parse(saved);
                    if (formData.tanggal) form.querySelector('input[name="tanggal"]').value = formData.tanggal;
                    form.querySelector('input[name="ps"]').value = formData.ps || '';
                    form.querySelector('input[name="nama_customer"]').value = formData.nama_customer || '';
                    
                    container.innerHTML = ''; // Clear container
                    
                    if (formData.products && formData.products.length > 0) {
                        formData.products.forEach(p => addProductRow(p));
                    }
                    updateEmpty();
                } catch (e) {
                    console.error('Error restoring draft:', e);
                    updateEmpty();
                } finally {
                    isRestoring = false;
                    calculateGrandTotal();
                }
            }

            function calcNett(qty, hna, diskon) {
                const subtotal = (parseFloat(qty) || 0) * (parseFloat(hna) || 0);
                return Math.max(0, subtotal - subtotal * ((parseFloat(diskon) || 0) / 100));
            }

            function fillRowValues(tr, vals) {
                const set = function (name, v) { const el = tr.querySelector('input[name="' + name + '"]'); if (el) el.value = v === undefined || v === null ? '' : v; };
                set('nama_produk[]', vals.nama_produk);
                set('qty[]', vals.qty);
                set('satuan[]', vals.satuan);
                set('hna[]', vals.hna);
                set('diskon[]', vals.diskon);
                set('harga_nett[]', vals.nett);
                tr.querySelector('.cell-nama').textContent = vals.nama_produk || '—';
                tr.querySelector('.cell-sub').textContent = vals.satuan || '';
                tr.querySelector('.qty-txt').textContent = vals.qty || '';
                tr.querySelector('.hna-txt').textContent = vals.hna !== '' && vals.hna !== undefined ? 'Rp ' + formatRupiah(vals.hna) : '';
                tr.querySelector('.disc-txt').textContent = (vals.diskon !== '' && vals.diskon !== undefined) ? vals.diskon + '%' : '';
                tr.querySelector('.nett-txt').textContent = 'Rp ' + formatRupiah(vals.nett || 0);
            }

            function renumber() {
                if (!container) return;
                container.querySelectorAll('.row-manual').forEach(function (tr, i) {
                    const no = tr.querySelector('.row-no');
                    if (no) no.textContent = i + 1;
                });
            }

            function addProductRow(initialData) {
                if (!container) return;
                const d = initialData || {};
                const nett = calcNett(d.qty, d.hna, d.diskon);
                const tr = document.createElement('tr');
                tr.className = 'row-manual hover:bg-blue-50/50 transition-colors bg-white';
                tr.innerHTML =
                    '<td class="px-3 py-2 text-center text-slate-400 font-semibold row-no"></td>' +
                    '<td class="px-3 py-2 align-top">' +
                        '<div class="font-bold text-slate-800 cell-nama leading-snug"></div>' +
                        '<div class="text-[11px] text-slate-400 font-semibold mt-0.5 cell-sub leading-snug"></div>' +
                        '<input type="hidden" name="nama_produk[]">' +
                        '<input type="hidden" name="satuan[]">' +
                    '</td>' +
                    '<td class="px-3 py-2 align-top text-right whitespace-nowrap">' +
                        '<span class="font-black text-slate-800 qty-txt"></span>' +
                        '<input type="hidden" name="qty[]">' +
                    '</td>' +
                    '<td class="px-3 py-2 align-top text-right whitespace-nowrap">' +
                        '<span class="font-bold text-slate-600 hna-txt"></span>' +
                        '<input type="hidden" name="hna[]">' +
                    '</td>' +
                    '<td class="px-3 py-2 align-top text-center whitespace-nowrap">' +
                        '<span class="font-bold text-slate-500 disc-txt"></span>' +
                        '<input type="hidden" name="diskon[]">' +
                    '</td>' +
                    '<td class="px-3 py-2 align-top text-right whitespace-nowrap">' +
                        '<span class="font-black text-blue-700 nett-txt"></span>' +
                        '<input type="hidden" name="harga_nett[]" class="input-harga-nett">' +
                    '</td>' +
                    '<td class="px-3 py-2 align-top text-center whitespace-nowrap">' +
                        '<button type="button" title="Ubah" class="btn-edit ui-btn ui-btn--edit mr-1"><i class="fas fa-pen text-[11px]"></i></button>' +
                        '<button type="button" title="Hapus" class="btn-del ui-btn ui-btn--delete"><i class="fas fa-trash-can text-[11px]"></i></button>' +
                    '</td>';
                fillRowValues(tr, {
                    nama_produk: d.nama_produk || '',
                    qty: d.qty || '',
                    satuan: d.satuan || '',
                    hna: d.hna || '',
                    diskon: d.diskon || '',
                    nett: d.nett !== undefined ? d.nett : nett
                });
                tr.querySelector('.btn-del').addEventListener('click', function () { tr.remove(); renumber(); calculateGrandTotal(); updateEmpty(); saveFormToLocalStorage(); });
                tr.querySelector('.btn-edit').addEventListener('click', function () { openItemModal(null, tr); });
                container.appendChild(tr);
                renumber();
                updateEmpty();
                calculateGrandTotal();
                saveFormToLocalStorage();
            }

            // Cari produk dari master (datalist): klik hasil -> buka popup
            function renderSearch(keyword) {
                if (!searchBox) return;
                const kw = (keyword || '').trim().toLowerCase();
                if (!kw) { searchBox.classList.add('hidden'); searchBox.innerHTML = ''; return; }
                const list = namaMaster.filter(function (n) { return (n || '').toLowerCase().includes(kw); }).slice(0, 12);
                let html = list.map(function (n) {
                    return '<button type="button" data-nama="' + n.replace(/"/g, '&quot;') + '" class="w-full text-left px-4 py-2.5 hover:bg-blue-50/70 flex items-center gap-3 transition-colors">' +
                        '<i class="fas fa-box text-blue-300 text-xs shrink-0"></i>' +
                        '<span class="block text-xs font-bold text-slate-800 truncate">' + n.replace(/</g, '&lt;') + '</span></button>';
                }).join('');
                html += '<button type="button" data-nama="' + (keyword || '').trim().replace(/"/g, '&quot;') + '" class="w-full text-left px-4 py-2.5 bg-slate-50 hover:bg-blue-50/70 border-t border-slate-100 flex items-center gap-2 transition-colors">' +
                    '<i class="fas fa-plus text-emerald-500 text-xs shrink-0"></i>' +
                    '<span class="block text-xs font-bold text-slate-600 truncate">Pakai "' + (keyword || '').trim().replace(/</g, '&lt;') + '" sebagai produk baru</span></button>';
                searchBox.innerHTML = html;
                searchBox.classList.remove('hidden');
            }

            if (searchInput) {
                searchInput.addEventListener('input', function () { renderSearch(searchInput.value); });
                searchInput.addEventListener('focus', function () { renderSearch(searchInput.value); });
                document.addEventListener('click', function (e) {
                    if (searchBox && !searchBox.classList.contains('hidden')) {
                        if (!searchBox.contains(e.target) && e.target !== searchInput) searchBox.classList.add('hidden');
                    }
                });
            }
            if (searchBox) {
                searchBox.addEventListener('click', function (e) {
                    const btn = e.target && e.target.closest ? e.target.closest('[data-nama]') : null;
                    if (!btn) return;
                    const nama = btn.getAttribute('data-nama') || '';
                    searchBox.classList.add('hidden');
                    if (searchInput) searchInput.value = '';
                    openItemModal({ nama_produk: nama });
                });
            }

            // Popup: hitung nett live, Enter = simpan
            function syncHnaFromDisplay() {
                const clean = ((mHnaDisplay && mHnaDisplay.value) || '').replace(/[^0-9]/g, '');
                mHnaRaw.value = clean === '' ? '0' : clean;
                if (mHnaDisplay && clean !== '') mHnaDisplay.value = formatRupiah(clean);
                refreshModalNett();
            }
            if (mHnaDisplay) mHnaDisplay.addEventListener('input', syncHnaFromDisplay);
            [mNama, mQty, mSatuan, mDiskon].forEach(function (el) {
                if (!el) return;
                el.addEventListener('input', refreshModalNett);
                el.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') { e.preventDefault(); if (mSave) mSave.click(); }
                });
            });
            if (mHnaDisplay) mHnaDisplay.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); if (mSave) mSave.click(); }
            });
            if (mSave) {
                mSave.addEventListener('click', function () {
                    const nama = ((mNama && mNama.value) || '').trim();
                    const qty = ((mQty && mQty.value) || '').trim();
                    const satuan = ((mSatuan && mSatuan.value) || '').trim();
                    const hna = (mHnaRaw.value || '').trim();
                    const diskon = ((mDiskon && mDiskon.value) || '').trim();
                    if (!nama) { showModalErr('Nama produk wajib diisi.'); if (mNama) mNama.focus(); return; }
                    const q = parseFloat(qty);
                    if (!qty || !(q >= 1)) { showModalErr('Qty wajib diisi dan minimal 1.'); if (mQty) mQty.focus(); return; }
                    showModalErr('');
                    const vals = { nama_produk: nama, qty: qty, satuan: satuan, hna: hna, diskon: diskon, nett: calcNett(qty, hna, diskon) };
                    if (editingRow) fillRowValues(editingRow, vals);
                    else addProductRow(vals);
                    calculateGrandTotal();
                    saveFormToLocalStorage();
                    updateEmpty();
                    closeItemModal();
                });
            }

            if (addBtn && container) {
                addBtn.addEventListener('click', function () { openItemModal(); });
                restoreFormFromLocalStorage();
            }

            // Panel riwayat harga produk ke customer ini (di kanan popup item).
            // Dipindah ke dalam container modal agar se-layer dengan popup (tidak tertutup blur)
            // dan ikut terbawa saat modal dipindah ke body.
            const histPanel = document.getElementById('sales-item-history');
            const itemModalBox = document.getElementById('sales-item-modal');
            if (itemModalBox && histPanel && histPanel.parentElement !== itemModalBox) itemModalBox.appendChild(histPanel);
            const histBody = document.getElementById('price-history-body');
            const histSub = document.getElementById('price-history-sub');
            const histClose = document.getElementById('price-history-close');
            const customerInput = form ? form.querySelector('input[name="nama_customer"]') : null;
            const mNamaClear = document.getElementById('m-nama-clear');
            let histTimer = null;
            let histSeq = 0;

            function syncNamaClear() { if (mNamaClear && mNama) mNamaClear.classList.toggle('hidden', !mNama.value); }
            if (mNamaClear) mNamaClear.addEventListener('click', function () {
                if (!mNama) return;
                mNama.value = '';
                mNama.focus();
                syncNamaClear();
                scheduleHistoryLoad();
            });

            function escHtml(s) { return String(s === undefined || s === null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
            function isItemModalOpen() { const m = document.getElementById('sales-item-modal'); return !!(m && !m.classList.contains('hidden')); }
            function hideHistory() { if (histPanel) { histPanel.classList.add('hidden'); histPanel.removeAttribute('style'); } }
            function currentCustomer() { return ((customerInput && customerInput.value) || '').trim(); }

            function renderHistory(rows, product, customer) {
                if (!histBody) return;
                if (histSub) histSub.textContent = product + ' → ' + customer;
                if (!rows || !rows.length) {
                    histBody.innerHTML = '<div class="px-4 py-6 text-center"><i class="fas fa-receipt text-slate-200 text-2xl mb-2 block"></i><p class="text-[11px] font-bold text-slate-400">Belum ada riwayat harga<br>produk ini ke customer tersebut.</p></div>';
                    return;
                }
                histBody.innerHTML = rows.map(function (r) {
                    const disc = parseFloat(r.discount) || 0;
                    return '<div class="px-4 py-2.5 flex items-center gap-3">' +
                        '<div class="min-w-0 flex-1">' +
                            '<div class="text-[11px] font-black text-slate-700">Rp ' + formatRupiah(r.base_price) + (disc > 0 ? ' <span class="text-amber-600 font-bold">-' + disc + '%</span>' : '') + '</div>' +
                            '<div class="text-[10px] text-slate-400 font-semibold mt-0.5">' + escHtml(r.date) + ' · ' + escHtml(r.qty) + ' ' + escHtml(r.unit) + ' · Nett Rp ' + formatRupiah(r.net_price) + '</div>' +
                        '</div>' +
                        '<button type="button" data-hna="' + r.base_price + '" class="hist-use shrink-0 text-[10px] font-black text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-2.5 py-1.5 transition-colors">Pakai</button>' +
                    '</div>';
                }).join('');
            }

            function loadHistory() {
                const product = ((mNama && mNama.value) || '').trim();
                const customer = currentCustomer();
                if (!histPanel || !isItemModalOpen() || !product || !customer) { hideHistory(); return; }
                histPanel.classList.remove('hidden');
                alignHistory();
                if (histSub) histSub.textContent = product + ' → ' + customer;
                if (histBody) histBody.innerHTML = '<div class="px-4 py-6 text-center text-[11px] font-bold text-slate-400"><i class="fas fa-spinner fa-spin mr-1.5"></i>Memuat riwayat…</div>';
                const seq = ++histSeq;
                fetch('{{ route('sales.price_history') }}?customer=' + encodeURIComponent(customer) + '&product=' + encodeURIComponent(product) + '&limit=6')
                    .then(function (res) { if (!res.ok) throw new Error('http ' + res.status); return res.json(); })
                    .then(function (json) {
                        if (seq !== histSeq) return;
                        if (((mNama && mNama.value) || '').trim() !== product || currentCustomer() !== customer) return;
                        renderHistory(json.data || [], product, customer);
                    })
                    .catch(function () {
                        if (seq !== histSeq) return;
                        if (histBody) histBody.innerHTML = '<div class="px-4 py-6 text-center text-[11px] font-bold text-rose-400">Gagal memuat riwayat.</div>';
                    });
            }

            // Posisikan panel dari koordinat modal yang terukur (deterministik di semua layar).
            // Desktop: melayang di kanan modal, sisi atas rata persis dengan kartu form.
            // Mobile: ikut CSS bottom-sheet.
            function alignHistory() {
                if (!histPanel || !isItemModalOpen()) return;
                histPanel.removeAttribute('style');
                if (!window.matchMedia('(min-width: 1024px)').matches) return;
                const panel = itemModalBox ? itemModalBox.querySelector('[data-ui-modal-panel]') : null;
                if (!panel || !panel.getBoundingClientRect) return;
                const r = panel.getBoundingClientRect();
                const w = 336; // 21rem
                let left = Math.round(r.right + 12);
                if (left + w > window.innerWidth - 12) left = Math.max(12, window.innerWidth - w - 12);
                histPanel.style.position = 'fixed';
                histPanel.style.left = left + 'px';
                histPanel.style.top = Math.round(r.top) + 'px';
                histPanel.style.right = 'auto';
                histPanel.style.bottom = 'auto';
                histPanel.style.width = w + 'px';
                histPanel.style.margin = '0';
                histPanel.style.zIndex = '5';
            }

            function scheduleHistoryLoad() {
                if (histTimer) clearTimeout(histTimer);
                histTimer = setTimeout(loadHistory, 350);
            }

            if (mNama) mNama.addEventListener('input', scheduleHistoryLoad);
            if (mNama) mNama.addEventListener('input', syncNamaClear);
            if (customerInput) customerInput.addEventListener('input', function () { if (isItemModalOpen()) scheduleHistoryLoad(); });
            if (histClose) histClose.addEventListener('click', hideHistory);
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') hideHistory(); });
            window.addEventListener('resize', function () { if (histPanel && !histPanel.classList.contains('hidden')) alignHistory(); });
            if (histBody) histBody.addEventListener('click', function (e) {
                const btn = e.target && e.target.closest ? e.target.closest('.hist-use') : null;
                if (!btn || !mHnaDisplay) return;
                mHnaRaw.value = btn.getAttribute('data-hna') || '0';
                mHnaDisplay.value = mHnaRaw.value !== '0' ? formatRupiah(mHnaRaw.value) : '';
                refreshModalNett();
                if (mQty) mQty.focus();
            });

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
                form.addEventListener('submit', function(e) {
                    if (container && container.querySelectorAll('.row-manual').length === 0) {
                        e.preventDefault();
                        alert('Belum ada produk — cari produk di atas, klik, isi detail di popup, lalu Simpan ke Tabel.');
                        openItemModal();
                        return false;
                    }
                    // Lanjut ke konfirmasi global; cegah submit ganda agar tidak duplikat.
                    e.preventDefault();
                    if (typeof confirmSubmit === 'function') {
                        confirmSubmit({ preventDefault: function () {}, currentTarget: form, target: document.getElementById('submit-button') }, 'Save this sales data?');
                    } else if (confirm('Save this sales data?')) {
                        form.submit();
                    }
                });
            }
        });

        // Infinite scroll tabel kelola data: saat scroll mentok bawah, 30 baris
        // berikutnya ditempel otomatis tanpa refresh (filter ikut query string).
        document.addEventListener('DOMContentLoaded', function() {
            const scrollBox = document.getElementById('manage-table-scroll');
            const tbody = document.getElementById('sales-table-body');
            const loader = document.getElementById('scroll-loader');
            if (!scrollBox || !tbody) return;
            const urlParams = new URLSearchParams(window.location.search);
            let nextPage = (parseInt(urlParams.get('page') || '1', 10) || 1) + 1;
            let lastPage = parseInt(tbody.dataset.lastPage || '1', 10);
            let loading = false;
            let tick = false;
            let endShown = false;
            const salesBaseUrl = "{{ url('sales') }}";
            const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

            function esc(s) { return String(s === undefined || s === null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
            function fmtRp(n) { return new Intl.NumberFormat('id-ID').format(Math.round(parseFloat(n) || 0)); }

            function salesRowHtml(r) {
                const itemJson = JSON.stringify(r.item || {}).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
                const d = parseFloat(r.discount) || 0;
                return '<tr class="hover:bg-indigo-50/50 transition-colors row-appended">' +
                    '<td class="px-4 py-3 text-center"><input type="checkbox" name="ids[]" value="' + r.id + '" class="row-checkbox rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 cursor-pointer"></td>' +
                    '<td class="px-4 py-3 text-center text-slate-400 font-medium">' + r.no + '</td>' +
                    '<td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700">' + esc(r.date) + '</td>' +
                    '<td class="px-4 py-3 font-bold text-slate-900">' + esc(r.customer_name) + '</td>' +
                    '<td class="px-4 py-3 text-center font-bold text-indigo-600">' + esc(r.ps) + '</td>' +
                    '<td class="px-4 py-3"><div class="text-slate-800 font-medium">' + esc(r.product_name) + '</div>' +
                        '<div class="text-xs text-slate-400 mt-0.5">HNA: Rp ' + fmtRp(r.base_price) + ' | Discount: ' + d + '%</div></td>' +
                    '<td class="px-4 py-3 whitespace-nowrap"><span class="px-2.5 py-1 bg-slate-100 rounded-lg text-xs font-bold text-slate-600">' + esc(String(r.qty)) + ' ' + esc(r.unit) + '</span></td>' +
                    '<td class="px-4 py-3 text-right whitespace-nowrap font-bold text-emerald-700 bg-emerald-50/60 border-l border-emerald-100">Rp ' + fmtRp(r.net_price) + '</td>' +
                    '<td class="px-4 py-3 text-center whitespace-nowrap"><div class="flex items-center justify-center gap-2">' +
                        '<button type="button" title="Edit" data-item="' + itemJson + '" onclick="openEditItem(this)" class="ui-btn ui-btn--edit"><i class="fas fa-pen text-[11px]"></i></button>' +
                        '<form action="' + salesBaseUrl + '/' + r.id + '" method="POST" id="form-delete-' + r.id + '" class="inline">' +
                            '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                            '<input type="hidden" name="_method" value="DELETE">' +
                            '<button type="button" title="Delete" onclick="confirmDelete(\'' + r.id + '\')" class="ui-btn ui-btn--delete"><i class="fas fa-trash-can text-[11px]"></i></button>' +
                        '</form>' +
                    '</div></td></tr>';
            }

            function fetchNext() {
                if (loading || nextPage > lastPage) return;
                const oldRetry = document.getElementById('scroll-retry');
                if (oldRetry) oldRetry.remove();
                // Browser tanpa fetch: tampilkan tombol muat manual (pindah halaman penuh).
                if (typeof fetch !== 'function') {
                    if (!document.getElementById('scroll-fallback')) {
                        const fp = new URLSearchParams(window.location.search);
                        fp.set('page', nextPage);
                        tbody.insertAdjacentHTML('beforeend', '<tr id="scroll-fallback"><td colspan="9" class="px-4 py-4 text-center"><a href="' + window.location.pathname + '?' + fp.toString() + '" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-4 py-2 transition-colors">Tampilkan 30 berikutnya</a></td></tr>');
                    }
                    return;
                }
                loading = true;
                if (loader) loader.classList.add('show');
                const params = new URLSearchParams(window.location.search);
                params.set('page', nextPage);
                const ctrl = ('AbortController' in window) ? new AbortController() : null;
                const timer = ctrl ? setTimeout(function () { ctrl.abort(); }, 20000) : null;
                const fetchOpts = { headers: { 'X-Requested-With': 'XMLHttpRequest' } };
                if (ctrl) fetchOpts.signal = ctrl.signal;
                fetch('{{ route('sales.table_data') }}?' + params.toString(), fetchOpts)
                    .then(function (res) { if (!res.ok) throw new Error('http ' + res.status); return res.json(); })
                    .then(function (json) {
                        if (timer) clearTimeout(timer);
                        if (loader) loader.classList.remove('show');
                        loading = false;
                        (json.rows || []).forEach(function (r) { tbody.insertAdjacentHTML('beforeend', salesRowHtml(r)); });
                        nextPage = (json.current_page || nextPage) + 1;
                        lastPage = json.last_page || lastPage;
                        if (nextPage > lastPage && !endShown && (json.rows || []).length) {
                            endShown = true;
                            tbody.insertAdjacentHTML('beforeend', '<tr><td colspan="9" class="px-4 py-3 text-center text-[11px] font-bold text-slate-300">— Semua data sudah tampil —</td></tr>');
                        }
                    }, function () {
                        if (timer) clearTimeout(timer);
                        if (loader) loader.classList.remove('show');
                        loading = false;
                        if (!document.getElementById('scroll-retry')) {
                            tbody.insertAdjacentHTML('beforeend', '<tr id="scroll-retry"><td colspan="9" class="px-4 py-4 text-center"><button type="button" onclick="retrySalesScroll()" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-4 py-2 transition-colors"><i class="fas fa-rotate-right mr-1.5"></i>Gagal memuat — coba lagi</button></td></tr>');
                        }
                    });
            }

            window.retrySalesScroll = function () {
                const r = document.getElementById('scroll-retry');
                if (r) r.remove();
                fetchNext();
            };

            scrollBox.addEventListener('scroll', function () {
                if (tick) return;
                tick = true;
                requestAnimationFrame(function () {
                    tick = false;
                    if (scrollBox.scrollTop + scrollBox.clientHeight >= scrollBox.scrollHeight - 320) fetchNext();
                });
            });
        });

        // Bulk Delete Logic
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('check-all');
            const btnBulkDelete = document.getElementById('btn-bulk-delete');
            const selectedCount = document.getElementById('selected-count');

            function updateBulkDeleteButton() {
                const boxes = document.querySelectorAll('.row-checkbox');
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                if (btnBulkDelete && selectedCount) {
                    if (checkedCount > 0) {
                        btnBulkDelete.classList.remove('hidden');
                        selectedCount.textContent = checkedCount;
                    } else {
                        btnBulkDelete.classList.add('hidden');
                    }
                }
                const footer = document.getElementById('manage-table-footer');
                if (footer) footer.classList.toggle('hidden', checkedCount === 0);

                if (checkAll) {
                    checkAll.checked = boxes.length > 0 && checkedCount === boxes.length;
                }
            }

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    document.querySelectorAll('.row-checkbox').forEach(cb => {
                        cb.checked = this.checked;
                    });
                    updateBulkDeleteButton();
                });
            }

            const salesTbody = document.getElementById('sales-table-body');
            if (salesTbody) {
                salesTbody.addEventListener('change', function (e) {
                    if (e.target && e.target.classList && e.target.classList.contains('row-checkbox')) {
                        updateBulkDeleteButton();
                    }
                });
            }
            updateBulkDeleteButton();
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