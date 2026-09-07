@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Product Price & SPH' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }

        /* == Background == */
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

        /* == Header Style == */
        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1.25rem; padding: 1.25rem 1.75rem; color: white;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3); position: relative; overflow: hidden;
        }
        .page-header::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(30deg); pointer-events: none;
        }
        .header-content { position: relative; z-index: 1; }

        /* == Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9); border-radius: 9999px;
            color: #1e293b; font-size: 0.9rem; font-weight: 700;
            text-decoration: none; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 0; width: fit-content;
        }
        .btn-back-modern:hover { 
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px); color: #1d4ed8;
        }
        .btn-back-modern .icon-circle {
            width: 32px; height: 32px; background: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6; font-size: 0.9rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover .icon-circle { transform: translateX(-3px); background: #EFF6FF; }

        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }

        .modern-label { display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }

        /* == Cards == */
        .glass-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px); border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 1rem; padding: 1.25rem 1.5rem;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
        }

        /* == Hide Scrollbar for Single Row Action Bar == */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* == Icon Position Fix == */
        .icon-left { 
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%); 
            pointer-events: none; z-index: 5; display: flex; align-items: center; justify-content: center;
        }
        .icon-clear-search { 
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%); 
            background: transparent; border: none; padding: 0; cursor: pointer; 
            z-index: 5; display: flex; align-items: center; justify-content: center;
        }
        
        /* == SPH Sections == */
        .modern-section { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
        .modern-section .sec-icon {
            width: 2.25rem; height: 2.25rem; border-radius: 0.5rem;
            display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;
        }
        .modern-section h4 { font-size: 0.85rem; font-weight: 800; letter-spacing: 0.03em; text-transform: uppercase; color: #334155; margin: 0; line-height: 1.2; }
        .modern-section p { font-size: 0.75rem; font-weight: 600; color: #94a3b8; margin: 0; mt-0.5; }

        /* == Tab Scroller == */

        .tab-scroller-wrap { position: relative; }
        @keyframes swipeHint { 0%, 100% { opacity: 0.45; } 50% { opacity: 1; } }
        .tab-hint-text { animation: swipeHint 1.4s ease-in-out infinite; font-size: 0.7rem; font-weight: 600; color: rgba(255, 255, 255, 0.9); }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16" x-data="pricingManager({{ ($hasFullAccess ?? false) ? 'true' : 'false' }})">
        <div class="mesh-bg"></div>

        {{-- MAIN WRAPPER: Di sini z-index utama untuk layout dijaga --}}
        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-10 space-y-4 flex-1 flex flex-col justify-start">
            
            <div class="w-full flex justify-start mb-3 md:mb-4">
                <a href="{{ route('sales.index') }}" class="btn-back-modern shrink-0">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    Back to Sales Dashboard
                </a>
            </div>

            <div class="page-header flex flex-col xl:flex-row justify-between items-start xl:items-center gap-5">
                <div class="header-content">
                    <h1 class="text-3xl font-extrabold tracking-tight mb-1.5 text-white">Product Price & <span class="text-blue-200">SPH Manager</span></h1>
                    <p class="text-blue-100 text-sm opacity-90 max-w-2xl font-medium">Manage official catalog pricing, create quotation forms, and inspect SPH history records.</p>
                </div>
                
                <div class="tab-scroller-wrap w-full xl:w-auto shrink-0 mt-3 xl:mt-0">
                    <div x-ref="tabScroller" @scroll="checkTabOverflow()" class="flex space-x-1 bg-white/10 p-1.5 rounded-full border border-white/20 backdrop-blur-md overflow-x-auto relative z-10 w-full">
                        <button @click="activeTab = 'pricing'" :class="{ 'bg-white text-blue-700 shadow-md': activeTab === 'pricing', 'text-white hover:bg-white/20': activeTab !== 'pricing' }" class="px-5 py-2.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center">
                            <i class="fas fa-tags mr-2"></i> Price List
                        </button>
                        <button x-show="hasFullAccess" x-cloak @click="activeTab = 'sph'; if(!isEditMode) resetForm();" :class="{ 'bg-white text-blue-700 shadow-md': activeTab === 'sph', 'text-white hover:bg-white/20': activeTab !== 'sph' }" class="px-5 py-2.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center">
                            <i class="fas fa-file-contract mr-2"></i> <span x-text="isEditMode ? 'Edit SPH' : 'SPH Form'"></span>
                        </button>
                        <button @click="activeTab = 'history'" :class="{ 'bg-white text-blue-700 shadow-md': activeTab === 'history', 'text-white hover:bg-white/20': activeTab !== 'history' }" class="px-5 py-2.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center">
                            <i class="fas fa-history mr-2"></i> History
                        </button>
                    </div>
                    <div x-show="showTabHint" x-cloak class="tab-hint-text xl:hidden text-right mt-1.5" x-transition.opacity.duration.300ms>
                        <i class="fas fa-arrow-right mr-1"></i> swipe to view more tabs
                    </div>
                </div>
            </div>

            {{-- TAB 1: PRODUCT PRICE LIST --}}
            <div x-show="activeTab === 'pricing'" class="space-y-5 flex-1 flex flex-col" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass-card !p-0 overflow-hidden flex-1 flex flex-col">
                    
                    {{-- HEADER TABEL YANG SUDAH BERSIH DAN RAPI --}}
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4">
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg"><i class="fas fa-tags"></i></div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Official Catalogue Pricing</h3>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">Manage active pricing & SPH forms.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-start xl:justify-end gap-2.5 w-full xl:flex-1 min-w-0 overflow-x-auto hide-scrollbar pb-2 xl:pb-0">
                        
                        <!-- FIX: Hapus shrink-0, ganti dengan flex-1 dan batasan min/max width agar elastis -->
                        <div class="relative flex-1 min-w-[140px] max-w-sm">
                            <div class="icon-left text-slate-400"><i class="fas fa-search text-sm"></i></div>
                            <!-- FIX: Placeholder disingkat agar aman saat kolom menyusut -->
                            <input type="text" x-model="searchQuery" placeholder="Search Product Name" class="w-full pl-10 pr-9 py-2 text-sm border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm transition-colors" autocomplete="off">
                            <button type="button" x-cloak x-show="searchQuery.length > 0" @click="searchQuery = ''" class="icon-clear-search text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas fa-times-circle text-sm"></i>
                            </button>
                        </div>
                        
                        <button x-show="hasFullAccess" x-cloak @click="openManageModal()" class="shrink-0 inline-flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-700 font-bold py-2 px-4 rounded-lg text-sm transition-all border border-indigo-200 hover:border-indigo-300 hover:shadow-md hover:shadow-indigo-100 hover:-translate-y-0.5 cursor-pointer" title="Add a new product with its selling price to the price list catalog">
                            <i class="fas fa-plus"></i> Add to Pricing
                        </button>

                        <div class="shrink-0 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm flex items-center gap-1.5" title="Total Products">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total:</span>
                            <span class="text-sm font-black text-blue-600" x-text="filteredProducts.length"></span>
                        </div>

                        <a href="{{ route('sales.pricing.export.pdf') }}" class="shrink-0 inline-flex items-center justify-center w-9 h-9 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 rounded-lg text-base transition-all border border-red-200 hover:border-red-300 hover:shadow-md hover:shadow-red-100 hover:-translate-y-0.5 cursor-pointer" title="Download Price List (PDF)">
                            <i class="fas fa-file-pdf"></i>
                        </a>

                        <a href="{{ route('sales.pricing.export.excel') }}" class="shrink-0 inline-flex items-center justify-center w-9 h-9 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-base transition-all border border-emerald-200 cursor-pointer" title="Download Price List (Excel)">
                            <i class="fas fa-file-excel"></i>
                        </a>
                    </div>
                </div>
                    
                    <div class="overflow-x-auto flex-1 bg-white">
                        <table class="w-full text-left text-slate-600">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-200 font-bold tracking-wider">
                                <tr>
                                    <th class="px-5 py-3">Product Name</th>
                                    <th class="px-5 py-3">Packaging / Qty</th>
                                    <th class="px-5 py-3 text-right">HNA Price</th>
                                    <th class="px-5 py-3 text-right">HNA Price / Pcs</th>
                                    <th class="px-5 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <template x-for="item in filteredProducts" :key="item.id">
                                    <tr class="hover:bg-indigo-50/50 transition-colors">
                                        <td class="px-5 py-3 font-bold text-slate-900" x-text="item.product_name"></td>
                                        <td class="px-5 py-3">
                                            <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md font-semibold text-xs" x-text="item.presentation"></span>
                                        </td>
                                        <td class="px-5 py-3 text-right font-black text-blue-600" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.base_price)"></td>
                                        <td class="px-5 py-3 text-right font-semibold text-slate-700" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.unit_price)"></td>
                                        <td class="px-5 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button x-show="hasFullAccess" x-cloak @click="openEditProduct(item)" title="Edit Product & Price" class="w-8 h-8 rounded-md bg-amber-50 hover:bg-amber-100 text-amber-600 hover:text-amber-700 flex items-center justify-center transition-all text-xs border border-amber-200 hover:border-amber-300 hover:shadow-md hover:shadow-amber-100 hover:-translate-y-0.5 cursor-pointer">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button x-show="hasFullAccess" x-cloak @click="deleteProduct(item)" title="Remove Product from Catalog" class="w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-500 hover:text-rose-600 flex items-center justify-center transition-all text-xs border border-rose-200 hover:border-rose-300 hover:shadow-md hover:shadow-rose-100 hover:-translate-y-0.5 cursor-pointer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <span x-show="!hasFullAccess" x-cloak class="text-slate-300 text-xs font-bold">-</span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredProducts.length === 0">
                                    <tr>
                                        <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                                            <i class="fas fa-box-open text-4xl mb-4 text-slate-300 block"></i>
                                            <h4 class="font-bold text-base text-slate-600">No Products Found</h4>
                                            <p class="text-sm mt-1">No matching products found.</p>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TAB 2: SPH FORM GENERATOR / EDITOR --}}
            <div x-show="activeTab === 'sph' && hasFullAccess" class="space-y-5 flex-1 flex flex-col" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass-card !p-0 overflow-hidden flex-1 flex flex-col">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg"><i class="fas fa-file-contract"></i></div>
                            <div>
                                <h3 class="text-lg font-black text-slate-800" x-text="isEditMode ? 'Edit Price Quotation' : 'SPH Form'"></h3>
                                <p class="text-xs text-slate-500 font-semibold">Configure client details, PS, and items.</p>
                            </div>
                        </div>
                        <template x-if="isEditMode">
                            <button @click="resetForm()" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-sm rounded-lg transition-colors cursor-pointer">
                                <i class="fas fa-times"></i> Cancel Edit
                            </button>
                        </template>
                    </div>

                    <div class="p-6 space-y-6 bg-white">
                        {{-- 1 & 2. Client & Sales Representative Data --}}
                        <div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <div class="lg:col-span-1">
                                    <label class="modern-label">Customer Name <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-user text-sm"></i></div>
                                        <input type="text" x-model="customerName" placeholder="Client Name" class="w-full pl-10 pr-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                                    </div>
                                </div>
                                <div class="lg:col-span-1">
                                    <label class="modern-label">Company / Institution</label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-building text-sm"></i></div>
                                        <input type="text" x-model="customerCompany" placeholder="Company / Institution" class="w-full pl-10 pr-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                                    </div>
                                </div>
                                <div class="lg:col-span-1">
                                    <label class="modern-label">Contact Person (PS)</label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-id-badge text-sm"></i></div>
                                        <select x-model="selectedPs" @change="updatePsPhone()" class="w-full appearance-none border border-slate-200 bg-white hover:bg-slate-50 shadow-sm rounded-lg text-sm pl-10 pr-10 py-2.5 font-bold text-slate-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            <option value="">-- Select PS --</option>
                                            <template x-for="ps in psList" :key="ps.name">
                                                <option :value="ps.name" x-text="ps.name"></option>
                                            </template>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                            <i class="fas fa-chevron-down text-sm"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="lg:col-span-1">
                                    <label class="modern-label">Phone Number</label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-phone text-sm"></i></div>
                                        <input type="text" x-model="psPhone" placeholder="Auto-populated" class="w-full pl-10 pr-4 py-2.5 text-sm font-semibold border border-slate-200 bg-slate-100 rounded-lg outline-none text-slate-500 cursor-not-allowed" readonly>
                                    </div>
                                </div>
                                <div class="lg:col-span-1">
                                    <label class="modern-label">Tax / PPN Setting</label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-percent text-sm"></i></div>
                                        <select x-model.number="ppnOption" class="w-full appearance-none border border-slate-200 bg-white hover:bg-slate-50 shadow-sm rounded-lg text-sm pl-10 pr-10 py-2.5 font-bold text-slate-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            <option value="11">PPN 11%</option>
                                            <option value="0">Non-PPN (0%)</option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                            <i class="fas fa-chevron-down text-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Quotation Items --}}
                        <div>
                            <div class="modern-section mb-3 flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4">
                                <div class="flex gap-3">
                                    <div class="sec-icon bg-pink-100 text-pink-600 shrink-0"><i class="fas fa-boxes text-base"></i></div>
                                    <div>
                                        <h4>Quotation Items</h4>
                                        <p>HNA and HNA/Pcs from catalog; net after discount &amp; PPN per pack and per Pcs.</p>
                                    </div>
                                </div>
                                
                                <!-- Bungkus kedua tombol dalam flex row agar sejajar -->
                                <div class="flex items-center gap-2.5 shrink-0 w-full md:w-auto">
                                    <button type="button" @click="showProductModal = true; modalSearch = ''" class="flex-1 md:flex-none justify-center inline-flex items-center gap-2 px-4 py-2 text-indigo-600 bg-indigo-50 border border-indigo-200 text-sm font-bold rounded-lg shadow-sm hover:bg-indigo-100 transition-colors cursor-pointer">
                                        <i class="fas fa-plus"></i> Add Product
                                    </button>
                                    <button type="button" @click="saveAndGenerateSph()" :disabled="selectedItems.length === 0 || savingSph" class="flex-1 md:flex-none justify-center inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-bold shadow-md shadow-blue-500/30 transition-all cursor-pointer">
                                        <i class="fas text-sm" :class="savingSph ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                                        <span x-text="savingSph ? 'Saving...' : (isEditMode ? 'Update SPH' : 'Save & Create SPH')"></span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="rounded-xl border border-slate-200 overflow-x-auto bg-white">
                                <table class="w-full text-left border-collapse whitespace-nowrap">
                                    <thead>
                                        <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wider border-b border-slate-200">
                                            <th class="py-3 px-4 text-center w-10 text-slate-500">No</th>
                                            <th class="py-3 px-3 text-slate-500">Product Name</th>
                                            <th class="py-3 px-3 text-slate-500">Presentation</th>
                                            <th class="py-3 px-3 text-right text-blue-600">HNA Price</th>
                                            <th class="py-3 px-3 text-right text-slate-600">HNA/Pcs</th>
                                            <th class="py-3 px-3 text-center w-24 text-amber-600">Discount</th>
                                            <th class="py-3 px-3 text-right text-slate-600">Net+PPN</th>
                                            <th class="py-3 px-3 text-right bg-blue-50/60 text-blue-600">Net+PPN / Pcs</th>
                                            <th class="py-3 px-3 text-center w-12 text-slate-500"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                        <template x-for="(item, index) in selectedItems" :key="index">
                                            <tr class="hover:bg-slate-50 group transition-colors">
                                                <td class="py-2.5 px-4 text-center font-bold text-slate-400" x-text="index + 1"></td>
                                                <td class="py-2.5 px-3 font-bold text-slate-800 whitespace-normal leading-tight" x-text="item.product_name"></td>
                                                <td class="py-2.5 px-3"><span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md font-semibold text-xs" x-text="item.presentation"></span></td>
                                                <td class="py-2.5 px-3 text-right border-l border-slate-100 font-bold text-slate-600" x-text="'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(itemHna(item))"></td>
                                                <td class="py-2.5 px-3 text-right border-l border-slate-100 font-semibold text-slate-500" x-text="'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(hnaPerPcs(item))"></td>
                                                
                                                {{-- FIX: Layout Icon Persen Diperbaiki --}}
                                                {{-- FIX: Layout Icon Persen Diperbaiki --}}
                                                <td class="py-2.5 px-3 text-center border-l border-slate-100">
                                                    <div class="relative flex items-center w-24 mx-auto">
                                                        <!-- Ubah pr-7 menjadi pr-8 agar angka tidak menabrak ikon -->
                                                        <input type="number" x-model.number="item.discount" min="0" max="100" class="w-full pl-2 pr-8 text-right font-bold text-sm text-amber-700 bg-white border border-amber-200 rounded-md py-1 outline-none focus:border-amber-500 hover:border-amber-400 transition-colors appearance-none">
                                                        
                                                        <!-- Ubah pr-1 menjadi pr-3 agar ikon % tidak terlalu mepet ke kanan -->
                                                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-[12px] text-slate-500 font-bold pointer-events-none">%</span>
                                                    </div>
                                                </td>
                                                
                                                <td class="py-2.5 px-3 text-right border-l border-slate-100 font-bold text-slate-700" x-text="'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(netPpn(item))"></td>
                                                <td class="py-2.5 px-3 text-right border-l border-slate-100 bg-blue-50/40 font-black text-blue-600" x-text="'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(netPpnPerPcs(item))"></td>
                                                <td class="py-2.5 px-3 text-center border-l border-slate-100">
                                                    <button @click="selectedItems.splice(index, 1)" class="w-8 h-8 rounded-md bg-rose-50 text-rose-500 hover:text-rose-600 flex items-center justify-center mx-auto hover:bg-rose-100 transition-all opacity-60 group-hover:opacity-100 cursor-pointer"><i class="fas fa-times text-sm"></i></button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="selectedItems.length === 0">
                                            <tr><td colspan="9" class="py-10 text-center text-slate-400 text-sm">No items added. Click "Add Product" to choose from catalog.</td></tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- 4. Ringkasan --}}
                        <div x-show="selectedItems.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                            <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-center">
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Items</div>
                                <div class="text-xl font-black text-slate-700" x-text="selectedItems.length"></div>
                            </div>
                            <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-center">
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Qty</div>
                                <div class="text-xl font-black text-slate-700" x-text="selectedItems.reduce((a,b) => a + b.qty, 0)"></div>
                            </div>
                            <div class="bg-amber-50 rounded-xl border border-amber-200 p-4 text-center">
                                <div class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-1">Total Discount</div>
                                <div class="text-xl font-black text-amber-600" x-text="'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(totalDiscount)"></div>
                            </div>
                            <div class="bg-blue-50 rounded-xl border border-blue-200 p-4 text-center">
                                <div class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Subtotal</div>
                                <div class="text-xl font-black text-blue-600" x-text="'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(subtotal)"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: SPH HISTORY SECTION --}}
            <div x-show="activeTab === 'history'" class="space-y-5 flex-1 flex flex-col" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass-card !p-0 overflow-hidden flex-1 flex flex-col">
                    
                    {{-- Header Table --}}
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg"><i class="fas fa-history"></i></div>
                            <div>
                                <h3 class="text-lg font-black text-slate-800">Saved SPH History</h3>
                                <p class="text-xs text-slate-500 font-semibold mt-0.5">Total <span class="text-blue-600 font-bold" x-text="filteredHistory.length"></span> records.</p>
                            </div>
                        </div>
                        
                        <!-- Wrapper dikembalikan ke aslinya (lg:w-auto) -->
                        <div class="flex items-center gap-2.5 w-full lg:w-auto overflow-x-auto hide-scrollbar shrink-0 pb-2 lg:pb-0 lg:mt-3 lg:translate-y-1">
                            
                            <!-- Pakai inline style width agar lebar fix tanpa nembus batas 100% container -->
                            <div class="relative w-full sm:w-80" style="width: 480px; max-width: 100%;">
                                <div class="icon-left text-slate-400"><i class="fas fa-search text-sm"></i></div>
                                <input type="text" x-model="historySearchQuery" placeholder="Search Client, Company or SPH Number" class="w-full pl-10 pr-9 py-2 text-sm border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm transition-colors" autocomplete="off">
                                <button type="button" x-cloak x-show="historySearchQuery.length > 0" @click="historySearchQuery = ''" class="icon-clear-search text-slate-400 hover:text-slate-600 transition-colors">
                                    <i class="fas fa-times-circle text-sm"></i>
                                </button>
                            </div>
                            
                        </div>
                    </div>

                    {{-- FIX: Hapus whitespace-nowrap pada tag table utama agar nama panjang bisa wrap --}}
                    <div class="overflow-x-auto flex-1 bg-white">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wider border-b border-slate-200 whitespace-nowrap">
                                    <th class="py-3 px-5 text-slate-500">SPH Number</th>
                                    <th class="py-3 px-4 text-slate-500">Date</th>
                                    <th class="py-3 px-4 text-slate-500">Client / Company</th>
                                    <th class="py-3 px-4 text-slate-500">Sales Person (PS)</th>
                                    <th class="py-3 px-4 text-center text-slate-500">Items</th>
                                    <th class="py-3 px-5 text-center text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                <template x-if="loadingHistory">
                                    <tr>
                                        <td colspan="8" class="py-12 text-center text-slate-400 bg-slate-50">
                                            <i class="fas fa-spinner fa-spin text-3xl mb-3 text-blue-400 block"></i>
                                            <p class="text-sm font-medium">Loading SPH history...</p>
                                        </td>
                                    </tr>
                                </template>
                                <template x-for="history in filteredHistory" :key="history.id">
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-3 px-5 font-bold text-blue-600 whitespace-nowrap">
                                            <a :href="'/sales/sph/' + history.id" class="inline-flex items-center gap-1.5 hover:underline cursor-pointer" title="View SPH details">
                                                <span x-text="history.sphNumber"></span>
                                                <i class="fas fa-external-link-alt text-[10px] text-blue-400"></i>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-slate-500 whitespace-nowrap" x-text="history.date"></td>
                                        <td class="py-3 px-4 border-l border-slate-100">
                                            <div>
                                                <a :href="'/sales/sph/' + history.id" class="hover:underline cursor-pointer">
                                                    <div class="font-bold text-slate-800 whitespace-normal leading-snug" x-text="history.customerName"></div>
                                                </a>
                                                <div class="text-xs text-slate-500 font-medium mt-0.5 whitespace-normal" x-text="history.customerCompany"></div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 border-l border-slate-100 whitespace-nowrap">
                                            <div class="font-semibold text-slate-700" x-text="history.selectedPs"></div>
                                            <div class="text-xs text-slate-500 mt-0.5" x-text="history.psPhone"></div>
                                        </td>
                                        <td class="py-3 px-4 text-center border-l border-slate-100 whitespace-nowrap">
                                            <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-bold text-xs" x-text="history.items.length + ' Items'"></span>
                                        </td>
                                        <td class="py-3 px-5 text-center border-l border-slate-100 whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-2">
                                                <button x-show="hasFullAccess" x-cloak @click="editSph(history)" class="w-8 h-8 rounded-md bg-amber-50 hover:bg-amber-100 text-amber-600 hover:text-amber-700 flex items-center justify-center transition-all text-xs border border-amber-200 hover:border-amber-300 hover:shadow-md hover:shadow-amber-100 hover:-translate-y-0.5 cursor-pointer" title="Edit SPH">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a :href="'/sales/sph/' + history.id + '/export/pdf'" class="w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-500 hover:text-red-600 flex items-center justify-center transition-all text-xs border border-red-200 hover:border-red-300 hover:shadow-md hover:shadow-red-100 hover:-translate-y-0.5 cursor-pointer" title="Export SPH PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <a :href="'/sales/sph/' + history.id + '/export/excel'" class="w-8 h-8 rounded-md bg-green-50 hover:bg-green-500 hover:text-white text-green-600 flex items-center justify-center transition-all text-xs border border-green-200 cursor-pointer" title="Export SPH Excel">
                                                    <i class="fas fa-file-excel"></i>
                                                </a>
                                                <button x-show="hasFullAccess" x-cloak @click="deleteHistory(history.id)" class="w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-500 hover:text-rose-600 flex items-center justify-center transition-all text-xs border border-rose-200 hover:border-rose-300 hover:shadow-md hover:shadow-rose-100 hover:-translate-y-0.5 cursor-pointer" title="Delete Record">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredHistory.length === 0">
                                    <tr>
                                        <td colspan="8" class="py-16 text-center text-slate-400 bg-slate-50">
                                            <i class="fas fa-box-open text-4xl mb-4 text-slate-300 block"></i>
                                            <h4 class="font-bold text-base text-slate-600">No SPH History</h4>
                                            <p class="text-sm mt-1">Create a new quotation from the SPH Form tab.</p>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ========================================================= --}}
            {{-- FIX: MODALS DIPINDAHKAN KE SINI (Di dalam z-10 wrapper) --}}
            {{-- Menggunakan style inline z-index agar anti gagal compile --}}
            {{-- ========================================================= --}}

            {{-- MODAL 1: EDIT PRODUCT PRICE --}}
            <div x-show="showEditProductModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" style="z-index: 99999;" x-transition.opacity>
                <div @click.away="showEditProductModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 shrink-0">
                        <div>
                            <h3 class="text-base font-bold text-slate-800"><i class="fas fa-edit text-amber-500 mr-2"></i> Edit Product & Price</h3>
                            <p class="text-xs text-slate-500 font-semibold mt-1">Change the price or unit/packaging contents. When unit/contents change, HNA/Pcs is recalculated automatically from HNA Price.</p>
                        </div>
                        <button @click="showEditProductModal = false" class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer text-lg"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="p-5 overflow-y-auto flex-1 bg-white">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-wrap gap-4">
                                <div style="flex: 5 1 0; min-width: 280px;">
                                    <label class="modern-label">Product Name <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-box text-sm"></i></div>
                                        <input type="text" x-model="editingProduct.product_name" list="product-name-suggestions" autocomplete="off" class="w-full pl-10 pr-4 py-2 text-sm font-semibold border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                                    </div>
                                </div>
                                <div style="flex: 4 1 0; min-width: 240px;">
                                    <label class="modern-label">HNA Price <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-money-bill-wave text-sm"></i></div>
                                        <input type="text" inputmode="numeric" :value="formatPrice(editingProduct.base_price)" @input="handlePriceInput(editingProduct, 'base_price', $event)" placeholder="Enter HNA price" class="w-full pl-10 pr-4 py-2 text-sm font-bold border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-blue-600 shadow-sm transition-colors">
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-4">
                                <div style="flex: 5 1 0; min-width: 240px;">
                                    <label class="modern-label">Packaging</label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-box-open text-sm"></i></div>
                                        <input type="text" x-model="editingProduct.unit" list="packaging-suggestions" @input="onPackagingChange()" placeholder="Pcs / Pack / Box / Roll / Polybag..." class="w-full pl-10 pr-9 py-2 text-sm font-semibold border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                                        <button type="button" x-cloak x-show="(editingProduct.unit || '').length > 0" @click="editingProduct.unit = ''; onPackagingChange()" class="icon-clear-search text-slate-400 hover:text-slate-600 transition-colors" title="Clear packaging">
                                            <i class="fas fa-times-circle text-sm"></i>
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        <template x-for="pkg in packagingSuggestions" :key="pkg">
                                            <button type="button" @click="editingProduct.unit = pkg; onPackagingChange()" :class="editingProduct.unit === pkg ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300'" class="px-2 py-0.5 rounded-md border text-[10px] font-bold transition-colors cursor-pointer" x-text="pkg"></button>
                                        </template>
                                    </div>
                                </div>
                                <div style="flex: 4 1 0; min-width: 240px;">
                                    <label class="modern-label">QTY Per Packaging</label>
                                    <div x-show="packagingCanFill">
                                        <div class="flex items-center gap-2">
                                            <div class="relative flex-1 min-w-0">
                                                <div class="icon-left text-slate-400"><i class="fas fa-hashtag text-sm"></i></div>
                                                <input type="number" x-model.number="editingProduct.pcs_pack" min="0" placeholder="e.g. 100" @input="syncUnitPrice()" class="w-full pl-10 pr-9 py-2 text-sm font-bold border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                                                <button type="button" x-cloak x-show="editingProduct.pcs_pack !== null && editingProduct.pcs_pack !== ''" @click="editingProduct.pcs_pack = null; syncUnitPrice()" class="icon-clear-search text-slate-400 hover:text-slate-600 transition-colors" title="Clear contents">
                                                    <i class="fas fa-times-circle text-sm"></i>
                                                </button>
                                            </div>
                                            <template x-if="packagingFillUnits.length > 1">
                                                <select x-model="editingProduct.fill_unit" class="w-24 shrink-0 appearance-none border border-slate-200 bg-white rounded-lg pl-2 pr-6 py-2 text-sm font-bold text-slate-700 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm cursor-pointer">
                                                    <template x-for="u in packagingFillUnits" :key="u">
                                                        <option :value="u" x-text="u"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="packagingFillUnits.length <= 1">
                                                <span class="w-24 shrink-0 inline-flex items-center justify-center bg-slate-100 border border-slate-200 rounded-lg py-2 text-sm font-bold text-slate-500" x-text="packagingFillUnits[0] || 'Pcs'"></span>
                                            </template>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            <template x-for="qty in packQtyPresets" :key="qty">
                                                <button type="button" @click="editingProduct.pcs_pack = qty; syncUnitPrice()" :class="editingProduct.pcs_pack == qty ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300'" class="px-2 py-0.5 rounded-md border text-[10px] font-bold transition-colors cursor-pointer" x-text="qty"></button>
                                            </template>
                                        </div>
                                    </div>
                                    <p x-show="!packagingCanFill" class="text-[10px] text-slate-400 font-semibold mt-1">Single unit (Pcs / Roll / Bottle) — no contents, stored automatically as 1.</p>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-1.5">Stored: <span class="font-black text-slate-600" x-text="packagingPreview"></span></p>
                                </div>
                                <div style="flex: 3 1 0; min-width: 180px;">
                                    <label class="modern-label">HNA/Pcs <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-coins text-sm"></i></div>
                                        <input type="text" inputmode="numeric" :value="formatPrice(editingProduct.unit_price)" @input="handlePriceInput(editingProduct, 'unit_price', $event)" placeholder="Auto: HNA ÷ Qty" class="w-full pl-10 pr-4 py-2 text-sm font-bold border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-1.5">Auto: HNA Price ÷ Qty/Pcs, still editable.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                            <button @click="showEditProductModal = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg text-sm font-bold hover:bg-slate-50 transition-colors cursor-pointer">Cancel</button>
                            <button @click="saveProductEdit()" :disabled="editSaving" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg text-sm font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                                <i class="fas text-sm" :class="editSaving ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                                <span x-text="editSaving ? 'Saving...' : 'Save'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <datalist id="product-name-suggestions">
                @foreach($nameSuggestions ?? [] as $sug)
                    <option value="{{ $sug }}"></option>
                @endforeach
            </datalist>

            <datalist id="packaging-suggestions">
                @foreach(['Pcs', 'Pack', 'Box', 'Roll', 'Botol', 'Polybag', 'Bag', 'Pouches', 'Karton'] as $pkg)
                    <option value="{{ $pkg }}"></option>
                @endforeach
            </datalist>

            {{-- MODAL 3: TAMBAH BARANG BARU --}}
            <div x-show="showManageModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" style="z-index: 99999;" x-transition.opacity @keydown.escape.window="showManageModal = false">
                <div @click.away="showManageModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 shrink-0">
                        <div>
                            <h3 class="text-base font-bold text-slate-800"><i class="fas fa-plus-circle text-indigo-500 mr-2"></i> Add Product to Pricing</h3>
                            <p class="text-xs text-slate-500 font-semibold mt-1">Add a new product to this page's price list catalog. All form data (name, packaging/presentation, contents qty/multiplier, HNA &amp; HNA/Pcs) is stored directly in the pricing table (<span class="font-mono font-bold text-indigo-500">product_prices</span>); products without a price do not appear in the catalog.</p>
                        </div>
                        <button @click="showManageModal = false" class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer text-lg"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <div class="p-5 overflow-y-auto flex-1 bg-white">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-wrap gap-4">
                                <div style="flex: 5 1 0; min-width: 280px;">
                                    <label class="modern-label">Product Name <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-box text-sm"></i></div>
                                        <input type="text" x-model="manageForm.product_name" list="product-name-suggestions" autocomplete="off" class="w-full pl-10 pr-4 py-2 text-sm font-semibold border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                                    </div>
                                </div>
                                <div style="flex: 4 1 0; min-width: 240px;">
                                    <label class="modern-label">HNA Price <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-money-bill-wave text-sm"></i></div>
                                        <input type="text" inputmode="numeric" :value="formatPrice(manageForm.base_price)" @input="handlePriceInput(manageForm, 'base_price', $event)" placeholder="Enter HNA price" class="w-full pl-10 pr-4 py-2 text-sm font-bold border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-blue-600 shadow-sm transition-colors">
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-4">
                                <div style="flex: 5 1 0; min-width: 240px;">
                                    <label class="modern-label">Packaging</label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-box-open text-sm"></i></div>
                                        <input type="text" x-model="manageForm.unit" list="packaging-suggestions" @input="onPackagingChange()" placeholder="Pcs / Pack / Box / Roll / Polybag..." class="w-full pl-10 pr-9 py-2 text-sm font-semibold border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                                        <button type="button" x-cloak x-show="(manageForm.unit || '').length > 0" @click="manageForm.unit = ''; onPackagingChange()" class="icon-clear-search text-slate-400 hover:text-slate-600 transition-colors" title="Clear packaging">
                                            <i class="fas fa-times-circle text-sm"></i>
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        <template x-for="pkg in packagingSuggestions" :key="pkg">
                                            <button type="button" @click="manageForm.unit = pkg; onPackagingChange()" :class="manageForm.unit === pkg ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300'" class="px-2 py-0.5 rounded-md border text-[10px] font-bold transition-colors cursor-pointer" x-text="pkg"></button>
                                        </template>
                                    </div>
                                </div>
                                <div style="flex: 4 1 0; min-width: 240px;">
                                    <label class="modern-label">QTY Per Packaging</label>
                                    <div x-show="packagingCanFill">
                                        <div class="flex items-center gap-2">
                                            <div class="relative flex-1 min-w-0">
                                                <div class="icon-left text-slate-400"><i class="fas fa-hashtag text-sm"></i></div>
                                                <input type="number" x-model.number="manageForm.pcs_pack" min="0" placeholder="e.g. 100" @input="syncUnitPrice()" class="w-full pl-10 pr-9 py-2 text-sm font-bold border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                                                <button type="button" x-cloak x-show="manageForm.pcs_pack !== null && manageForm.pcs_pack !== ''" @click="manageForm.pcs_pack = null; syncUnitPrice()" class="icon-clear-search text-slate-400 hover:text-slate-600 transition-colors" title="Clear contents">
                                                    <i class="fas fa-times-circle text-sm"></i>
                                                </button>
                                            </div>
                                            <template x-if="packagingFillUnits.length > 1">
                                                <select x-model="manageForm.fill_unit" class="w-24 shrink-0 appearance-none border border-slate-200 bg-white rounded-lg pl-2 pr-6 py-2 text-sm font-bold text-slate-700 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm cursor-pointer">
                                                    <template x-for="u in packagingFillUnits" :key="u">
                                                        <option :value="u" x-text="u"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="packagingFillUnits.length <= 1">
                                                <span class="w-24 shrink-0 inline-flex items-center justify-center bg-slate-100 border border-slate-200 rounded-lg py-2 text-sm font-bold text-slate-500" x-text="packagingFillUnits[0] || 'Pcs'"></span>
                                            </template>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            <template x-for="qty in packQtyPresets" :key="qty">
                                                <button type="button" @click="manageForm.pcs_pack = qty; syncUnitPrice()" :class="manageForm.pcs_pack == qty ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-300'" class="px-2 py-0.5 rounded-md border text-[10px] font-bold transition-colors cursor-pointer" x-text="qty"></button>
                                            </template>
                                        </div>
                                    </div>
                                    <p x-show="!packagingCanFill" class="text-[10px] text-slate-400 font-semibold mt-1">Single unit (Pcs / Roll / Bottle) — no contents, stored automatically as 1.</p>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-1.5">Stored: <span class="font-black text-slate-600" x-text="packagingPreview"></span></p>
                                </div>
                                <div style="flex: 3 1 0; min-width: 180px;">
                                    <label class="modern-label">HNA/Pcs <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="icon-left text-slate-400"><i class="fas fa-coins text-sm"></i></div>
                                        <input type="text" inputmode="numeric" :value="formatPrice(manageForm.unit_price)" @input="handlePriceInput(manageForm, 'unit_price', $event)" placeholder="Auto: HNA ÷ Qty" class="w-full pl-10 pr-4 py-2 text-sm font-bold border border-slate-200 bg-white rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-1.5">Auto: HNA Price ÷ Qty/Pcs, still editable.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                            <button @click="showManageModal = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg text-sm font-bold hover:bg-slate-50 transition-colors cursor-pointer">Cancel</button>
                            <button @click="saveManage()" :disabled="manageSaving" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg text-sm font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                                <i class="fas text-sm" :class="manageSaving ? 'fa-spinner fa-spin' : 'fa-plus'"></i>
                                <span x-text="manageSaving ? 'Saving...' : 'Add Product'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MODAL: QUICK PRODUCT PICKER --}}
            <div x-show="showProductModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" style="z-index: 99999;" x-transition.opacity>
                <div @click.away="showProductModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[85vh]" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 shrink-0">
                        <div><h3 class="text-base font-bold text-slate-800"><i class="fas fa-search text-blue-500 mr-2"></i> Quick Product Picker</h3></div>
                        <button @click="showProductModal = false" class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer text-lg"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="p-4 border-b border-slate-200 bg-white">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400"><i class="fas fa-search text-sm"></i></div>
                            <input type="text" x-model="modalSearch" placeholder="Type product name..." class="w-full pl-11 pr-4 py-2.5 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                        </div>
                    </div>
                    <div class="overflow-y-auto flex-1 p-4 divide-y divide-slate-100">
                        <template x-for="prod in modalFilteredProducts" :key="prod.id">
                            <div class="py-3 flex items-center justify-between hover:bg-slate-50 px-3 rounded-xl transition-colors">
                                <div>
                                    <div class="font-bold text-slate-800 text-sm leading-snug" x-text="prod.product_name"></div>
                                    <div class="text-xs text-slate-500 font-medium mt-1">Packaging: <span x-text="prod.presentation"></span></div>
                                </div>
                                <div class="flex items-center gap-4 shrink-0">
                                    <div class="text-right">
                                        <div class="font-black text-blue-600 text-sm" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(prod.base_price)"></div>
                                        <div class="text-[10px] text-slate-400 font-medium">HNA</div>
                                    </div>
                                    <button @click="addItem(prod)" class="inline-flex items-center gap-1.5 px-4 py-2 text-white text-xs font-bold rounded-lg shadow-sm hover:opacity-90 transition-opacity cursor-pointer whitespace-nowrap bg-indigo-600">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="modalFilteredProducts.length === 0">
                            <div class="py-12 text-center text-slate-400">
                                <i class="fas fa-box-open text-3xl mb-3 text-slate-300 block"></i>
                                <p class="text-sm font-medium">No products found.</p>
                            </div>
                        </template>
                    </div>
                    <div class="px-5 py-4 border-t border-slate-200 bg-slate-50 flex justify-between items-center shrink-0">
                        <span class="text-xs font-bold text-slate-500"><span class="text-blue-600" x-text="modalFilteredProducts.length"></span> products available</span>
                        <button @click="showProductModal = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg text-sm font-bold hover:bg-slate-50 transition-colors cursor-pointer">Done Selecting</button>
                    </div>
                </div>
            </div>

        </div> {{-- Akhir dari z-10 w-full konten wrapper utama --}}
    </div> {{-- Akhir dari x-data Alpine container --}}

    @push('scripts')
    <script>
        function pricingManager(hasFullAccess) {
            return {
                hasFullAccess: hasFullAccess,
                activeTab: 'pricing',
                showTabHint: true,
                checkTabOverflow() {
                    const el = this.$refs.tabScroller;
                    if (!el) return;
                    const hasOverflow = el.scrollWidth > el.clientWidth + 4;
                    const atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4;
                    this.showTabHint = hasOverflow && !atEnd;
                },
                searchQuery: '',
                historySearchQuery: '',
                rawProducts: @js($products),
                
                isEditMode: false,
                editingSphId: null,
                customerName: '',
                customerCompany: '',
                selectedPs: '',
                psPhone: '',
                ppnOption: 11, 
                selectedItems: [],

                showProductModal: false,
                modalSearch: '',

                showEditProductModal: false,
                editingProduct: { id: null, product_name: '', unit: '', pcs_pack: null, fill_unit: 'Pcs', base_price: 0, unit_price: 0 },
                
                showManageModal: false,
                manageSaving: false,
                editSaving: false,
                manageForm: { product_name: '', unit: '', pcs_pack: null, fill_unit: 'Pcs', base_price: 0, unit_price: 0 },
                packQtyPresets: [1, 10, 100, 1000],
                packagingSuggestions: ['Pcs', 'Pack', 'Box', 'Roll', 'Botol', 'Polybag', 'Bag', 'Pouches', 'Karton'],
                packagingCatalog: {
                    'pcs': { type: 'single', units: [] },
                    'pack': { type: 'flex', units: ['Pcs'] },
                    'box': { type: 'flex', units: ['Pcs', 'Pasang'] },
                    'roll': { type: 'single', units: [] },
                    'botol': { type: 'single', units: [] },
                    'polybag': { type: 'fixed', units: ['Pcs'] },
                    'bag': { type: 'fixed', units: ['Pcs'] },
                    'pouches': { type: 'fixed', units: ['Pcs'] },
                    'karton': { type: 'fixed', units: ['Pcs'] },
                },
                
                sphHistory: [],
                loadingHistory: false,
                savingSph: false,

                psList: [
                    { name: 'Arief Natanael Haryanto', phone: '081298765432' },
                    { name: 'Eko Sigit Nugroho', phone: '081388112233' },
                    { name: 'Rusiman Hendra Dipraja', phone: '081877665544' },
                    { name: 'Karsono Nu Haeman', phone: '085699221144' },
                    { name: 'Surachman', phone: '081233445566' }
                ],

                updatePsPhone() {
                    let found = this.psList.find(p => p.name === this.selectedPs);
                    this.psPhone = found ? found.phone : '';
                },

                init() {
                    // Pulihkan tab terakhir (pricing / sph / history) saat refresh.
                    const savedTab = localStorage.getItem('pricing_active_tab');
                    if (['pricing', 'sph', 'history'].includes(savedTab)) {
                        this.activeTab = (savedTab === 'sph' && !this.hasFullAccess) ? 'pricing' : savedTab;
                    }
                    // Simpan posisi tab setiap kali berpindah (termasuk setelah Simpan/Edit SPH).
                    this.$watch('activeTab', value => {
                        localStorage.setItem('pricing_active_tab', value);
                        this.$nextTick(() => this.checkTabOverflow());
                    });
                    this.fetchHistory();
                    this.$nextTick(() => this.checkTabOverflow());
                    window.addEventListener('resize', () => this.checkTabOverflow());
                },

                toast(message, type = 'success', title = '') {
                    const titles = { success: 'Success!', error: 'Failed!', warning: 'Warning!', info: 'Info' };
                    const colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
                    Swal.fire({
                        title: title || titles[type] || 'Notification',
                        text: message,
                        icon: type,
                        confirmButtonColor: colors[type] || '#3b82f6'
                    });
                },

                confirmDialog(message, title = 'Konfirmasi', confirmText = 'Ya, Lanjutkan') {
                    return Swal.fire({
                        position: 'center',
                        title: title,
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: confirmText,
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'bg-white shadow-[0_15px_50px_rgba(0,0,0,0.15)] border border-gray-100 rounded-3xl p-6 text-center',
                            title: 'text-lg font-black text-slate-800 tracking-tight mt-2 m-0',
                            htmlContainer: 'text-sm text-slate-500 font-medium leading-relaxed m-0 mt-3 mb-6',
                            icon: 'scale-75 m-0 mx-auto border-0 text-amber-500 -mt-2',
                            actions: 'flex justify-center gap-3 w-full m-0',
                            confirmButton: 'bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all shadow-md shadow-rose-200 m-0',
                            cancelButton: 'bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold px-5 py-2.5 rounded-xl transition-all m-0'
                        },
                        width: '340px',
                        buttonsStyling: false,
                        background: '#ffffff',
                        backdrop: 'rgba(0,0,0,0.5)',
                        showClass: { popup: 'animate__animated animate__zoomIn animate__faster' },
                        hideClass: { popup: 'animate__animated animate__zoomOut animate__faster' }
                    }).then((result) => result.isConfirmed);
                },

                csrfToken() {
                    let meta = document.querySelector('meta[name="csrf-token"]');
                    return meta ? meta.getAttribute('content') : '';
                },

                async fetchHistory() {
                    this.loadingHistory = true;
                    try {
                        const res = await fetch("{{ route('sales.sph.index') }}", {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) throw new Error('Failed to load SPH history.');
                        this.sphHistory = await res.json();
                    } catch (e) {
                        console.error(e);
                        this.toast('Failed to load SPH history dari server.', 'error');
                    } finally {
                        this.loadingHistory = false;
                    }
                },
                
                get filteredProducts() {
                    if (!this.searchQuery) return this.rawProducts;
                    return this.rawProducts.filter(p => 
                        p.product_name.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                },

                get modalFilteredProducts() {
                    if (!this.modalSearch) return this.rawProducts;
                    return this.rawProducts.filter(p => 
                        p.product_name.toLowerCase().includes(this.modalSearch.toLowerCase())
                    );
                },

                get filteredHistory() {
                    if (!this.historySearchQuery) return this.sphHistory;
                    return this.sphHistory.filter(h => 
                        h.customerName.toLowerCase().includes(this.historySearchQuery.toLowerCase()) || 
                        h.customerCompany.toLowerCase().includes(this.historySearchQuery.toLowerCase()) ||
                        h.sphNumber.toLowerCase().includes(this.historySearchQuery.toLowerCase())
                    );
                },

                addItem(product) {
                    let existing = this.selectedItems.find(i => {
                        return i.product_name === product.product_name;
                    });
                    if (existing) {
                        existing.qty += 1;
                        existing.pack_qty = product.pack_qty || existing.pack_qty || 1;
                        existing.base_price = product.base_price ?? existing.base_price;
                    } else {
                        this.selectedItems.push({
                            product_name: product.product_name,
                            presentation: product.presentation,
                            base_price: product.base_price ?? product.unit_price,
                            unit_price: product.unit_price,
                            pack_qty: product.pack_qty || 1,
                            discount: 0,
                            qty: 1
                        });
                    }
                },

                itemHna(item) {
                    return (item.base_price ?? item.unit_price) || 0;
                },

                hnaPerPcs(item) {
                    return this.itemHna(item) / (item.pack_qty || 1);
                },

                netPpn(item) {
                    let discounted = this.itemHna(item) * (1 - ((item.discount || 0) / 100));
                    let ppnMultiplier = 1 + (this.ppnOption / 100);
                    return discounted * ppnMultiplier;
                },

                netPpnPerPcs(item) {
                    return this.netPpn(item) / (item.pack_qty || 1);
                },

                get subtotal() {
                    return this.selectedItems.reduce((acc, item) => acc + (this.netPpn(item) * item.qty), 0);
                },

                get totalDiscount() {
                    return this.selectedItems.reduce((acc, item) => acc + (this.itemHna(item) * item.qty * ((item.discount || 0) / 100)), 0);
                },

                get packagingForm() {
                    return this.showManageModal ? this.manageForm : this.editingProduct;
                },

                get packagingMeta() {
                    const pkg = (this.packagingForm.unit || '').trim().toLowerCase();
                    return this.packagingCatalog[pkg] || { type: 'flex', units: ['Pcs'] };
                },

                get packagingType() { return this.packagingMeta.type; },
                get packagingFillUnits() { return this.packagingMeta.units || ['Pcs']; },
                get packagingCanFill() { return this.packagingType !== 'single'; },
                get packagingNeedsContent() { return this.packagingType === 'fixed'; },

                onPackagingChange() {
                    const form = this.packagingForm;
                    const meta = this.packagingMeta;
                    if (this.packagingType === 'single') {
                        form.pcs_pack = 1;
                    }
                    if (!meta.units.includes(form.fill_unit)) {
                        form.fill_unit = meta.units[0] || 'Pcs';
                    }
                    this.syncUnitPrice();
                },

                get packagingPreview() {
                    const form = this.packagingForm;
                    const pkg = (form.unit || '').trim();
                    const isi = parseInt(form.pcs_pack, 10) || 0;
                    const fillUnit = form.fill_unit || 'Pcs';
                    if (!pkg) return isi > 0 ? `${isi} ${fillUnit}` : 'General';
                    if (this.packagingType === 'single' || isi < 1) return pkg;
                    return `${pkg}/ ${isi} ${fillUnit}`;
                },

                syncUnitPrice() {
                    const form = this.packagingForm;
                    const isi = parseInt(form.pcs_pack, 10);
                    if (!isi || isi <= 0) return;
                    const base = Math.floor(Number(form.base_price) || 0);
                    form.unit_price = Math.round(base / isi);
                },

                formatPrice(value) {
                    const num = Math.floor(Number(value) || 0);
                    return num ? num.toLocaleString('id-ID') : '';
                },

                resolvePackQty() {
                    const form = this.packagingForm;
                    const isi = parseInt(form.pcs_pack, 10) || 0;
                    return (!this.packagingCanFill || isi < 1) ? 1 : isi;
                },

                parsePresentation(presentation) {
                    if (!presentation) return { unit: '', pcs_pack: null, fill_unit: 'Pcs' };
                    const idx = presentation.indexOf('/');
                    if (idx === -1) return { unit: presentation.trim(), pcs_pack: null, fill_unit: 'Pcs' };
                    const unit = presentation.slice(0, idx).trim();
                    const m = presentation.slice(idx + 1).trim().match(/(\d+)\s*([a-zA-Z]*)/);
                    const pcs_pack = m ? parseInt(m[1], 10) : null;
                    const fill_unit = (m && m[2]) ? m[2] : 'Pcs';
                    return { unit, pcs_pack, fill_unit };
                },

                handlePriceInput(target, field, event) {
                    const digits = String(event.target.value).replace(/[^0-9]/g, '');
                    const raw = digits ? parseInt(digits, 10) : 0;
                    target[field] = raw;
                    event.target.value = this.formatPrice(raw);
                    if (field === 'base_price') {
                        this.syncUnitPrice();
                    }
                },

                openEditProduct(product) {
                    const parsed = this.parsePresentation(product.presentation);
                    this.editingProduct = {
                        id: product.id,
                        product_name: product.raw_product_name || product.product_name,
                        unit: parsed.unit || '',
                        pcs_pack: parsed.pcs_pack,
                        fill_unit: parsed.fill_unit,
                        base_price: product.base_price,
                        unit_price: product.unit_price,
                    };
                    if (!this.packagingCanFill) this.editingProduct.pcs_pack = 1;
                    this.showEditProductModal = true;
                },

                async saveProductEdit() {
                    if (!this.editingProduct.id) {
                        this.showEditProductModal = false;
                        return;
                    }
                    if (this.editSaving) return;

                    if (!this.editingProduct.product_name || this.editingProduct.product_name.trim() === '') {
                        this.toast('Nama produk harus diisi.', 'error');
                        return;
                    }
                    if (this.packagingNeedsContent && (!this.editingProduct.pcs_pack || parseInt(this.editingProduct.pcs_pack, 10) < 1)) {
                        this.toast('Qty isi harus diisi untuk packaging ' + (this.editingProduct.unit || '').trim() + '.', 'error');
                        return;
                    }

                    this.editSaving = true;
                    const formData = new FormData();
                    formData.append('product_name', this.editingProduct.product_name.trim());
                    formData.append('presentation', this.packagingPreview);
                    formData.append('pack_qty', this.resolvePackQty());
                    formData.append('base_price', this.editingProduct.base_price);
                    formData.append('unit_price', this.editingProduct.unit_price);
                    formData.append('_method', 'PUT');

                    try {
                        const res = await fetch("{{ url('sales/pricing/barang') }}/" + this.editingProduct.id, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                            body: formData,
                        });

                        const result = await res.json();
                        if (!res.ok) {
                            const firstError = result.errors ? Object.values(result.errors)[0][0] : (result.message || 'Gagal menyimpan.');
                            this.toast(firstError, 'error');
                            return;
                        }

                        this.showEditProductModal = false;
                        this.toast(result.message || 'Data produk dan harga berhasil diupdate!', 'success');
                        await this.refreshProducts();
                    } catch (e) {
                        console.error(e);
                        this.toast('Terjadi kesalahan saat menyimpan perubahan.', 'error');
                    } finally {
                        this.editSaving = false;
                    }
                },

                async saveAndGenerateSph() {
                    if (!this.customerName || this.selectedItems.length === 0) {
                        this.toast('Silakan masukkan nama klien dan pilih minimal 1 produk!', 'warning');
                        return;
                    }

                    const payload = {
                        customerName: this.customerName,
                        customerCompany: this.customerCompany || null,
                        selectedPs: this.selectedPs || null,
                        psPhone: this.psPhone || null,
                        ppnOption: this.ppnOption,
                        items: this.selectedItems,
                        grandTotal: this.subtotal,
                    };

                    const isEdit = this.isEditMode && this.editingSphId;
                    const url = isEdit
                        ? "{{ url('sales/sph') }}/" + this.editingSphId
                        : "{{ route('sales.sph.store') }}";

                    this.savingSph = true;
                    try {
                        const res = await fetch(url, {
                            method: isEdit ? 'PUT' : 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                            body: JSON.stringify(payload),
                        });

                        const result = await res.json();

                        if (!res.ok) {
                            const firstError = result.errors ? Object.values(result.errors)[0][0] : (result.message || 'Gagal menyimpan SPH.');
                            this.toast(firstError, 'error');
                            return;
                        }

                        this.toast(result.message || 'Dokumen SPH berhasil disimpan!', 'success');
                        await this.fetchHistory();
                        this.resetForm();
                        this.activeTab = 'history';
                    } catch (e) {
                        console.error(e);
                        this.toast('Terjadi kesalahan saat menyimpan SPH ke server.', 'error');
                    } finally {
                        this.savingSph = false;
                    }
                },

                editSph(history) {
                    this.isEditMode = true;
                    this.editingSphId = history.id;
                    this.customerName = history.customerName;
                    this.customerCompany = history.customerCompany;
                    this.selectedPs = history.selectedPs;
                    this.psPhone = history.psPhone;
                    this.ppnOption = history.ppnOption || 11;
                    this.selectedItems = JSON.parse(JSON.stringify(history.items)).map(item => {
                        const prod = this.rawProducts.find(p => p.product_name === item.product_name);
                        return {
                            ...item,
                            base_price: (prod && prod.base_price != null) ? prod.base_price : (item.base_price != null ? item.base_price : item.unit_price),
                            pack_qty: item.pack_qty || 1,
                            discount: item.discount || 0
                        };
                    });
                    this.activeTab = 'sph';
                },

                resetForm() {
                    this.isEditMode = false;
                    this.editingSphId = null;
                    this.customerName = '';
                    this.customerCompany = '';
                    this.selectedPs = '';
                    this.psPhone = '';
                    this.ppnOption = 11;
                    this.selectedItems = [];
                },

                openManageModal() {
                    this.showManageModal = true;
                    this.resetManageForm();
                },

                resetManageForm() {
                    this.manageForm = { product_name: '', unit: '', pcs_pack: null, fill_unit: 'Pcs', base_price: 0, unit_price: 0 };
                },

                async saveManage() {
                    if (this.manageSaving) return;
                    if (!this.manageForm.product_name || this.manageForm.product_name.trim() === '') {
                        this.toast('Nama produk wajib diisi.', 'error');
                        return;
                    }
                    if (this.manageForm.base_price < 0 || this.manageForm.unit_price < 0) {
                        this.toast('Harga tidak boleh negatif.', 'error');
                        return;
                    }
                    if (this.packagingNeedsContent && (!this.manageForm.pcs_pack || parseInt(this.manageForm.pcs_pack, 10) < 1)) {
                        this.toast('Qty isi wajib diisi untuk packaging ' + (this.manageForm.unit || '').trim() + '.', 'error');
                        return;
                    }

                    this.manageSaving = true;
                    try {
                        const formData = new FormData();
                        formData.append('product_name', this.manageForm.product_name.trim());
                        formData.append('presentation', this.packagingPreview);
                        formData.append('pack_qty', this.resolvePackQty());
                        formData.append('base_price', this.manageForm.base_price);
                        formData.append('unit_price', this.manageForm.unit_price);

                        const res = await fetch("{{ url('sales/pricing/barang') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                            body: formData,
                        });

                        const result = await res.json();

                        if (!res.ok) {
                            const firstError = result.errors ? Object.values(result.errors)[0][0] : (result.message || 'Gagal menyimpan produk.');
                            this.toast(firstError, 'error');
                            return;
                        }

                        this.toast(result.message || 'Produk berhasil ditambahkan.', 'success');
                        this.resetManageForm();
                        this.showManageModal = false;
                        await this.refreshProducts();
                    } catch (e) {
                        console.error(e);
                        this.toast('Terjadi kesalahan saat menambahkan produk.', 'error');
                    } finally {
                        this.manageSaving = false;
                    }
                },

                async deleteProduct(p) {
                    if (!(await this.confirmDialog('Yakin ingin menghapus produk ini dari katalog?\n"' + p.product_name + '"', 'Hapus Produk', 'Ya, Hapus'))) return;
                    try {
                        const res = await fetch("{{ url('sales/pricing/barang') }}/" + p.id, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                        });
                        const result = await res.json();
                        if (!res.ok) throw new Error(result.message || 'Gagal menghapus.');
                        this.toast(result.message || 'Produk berhasil dihapus.', 'success');
                        await this.refreshProducts();
                    } catch (e) {
                        console.error(e);
                        this.toast('Gagal menghapus produk di server.', 'error');
                    }
                },

                async refreshProducts() {
                    try {
                        const res = await fetch("{{ route('sales.pricing.barang.list') }}", {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) throw new Error('Gagal refresh list produk.');
                        this.rawProducts = await res.json();
                    } catch (e) {
                        console.error(e);
                        this.toast('Gagal mereload daftar produk.', 'error');
                    }
                },

                async deleteHistory(id) {
                    if (!(await this.confirmDialog('Yakin ingin menghapus riwayat SPH ini?', 'Hapus Riwayat SPH', 'Ya, Hapus'))) return;

                    try {
                        const res = await fetch("{{ url('sales/sph') }}/" + id, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                        });
                        if (!res.ok) throw new Error('Gagal menghapus.');
                        this.sphHistory = this.sphHistory.filter(h => h.id !== id);
                        this.toast('Riwayat SPH berhasil dihapus.', 'success');
                    } catch (e) {
                        console.error(e);
                        this.toast('Gagal menghapus riwayat SPH di server.', 'error');
                    }
                }
            }
        }
    </script>
    @endpush
</x-layout-users>