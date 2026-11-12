<x-layout-users :title="'Client Ledger - ' . $client->client_name">
@php $routePrefix = request()->is('admin/*') ? 'admin.crm.' : 'crm.'; @endphp

    @push('styles')
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }

        /* == Modern Mesh Background == */
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
            background-attachment: fixed;
            pointer-events: none;
        }

        /* == Modern Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px 6px 6px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.8rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 0;
            width: fit-content;
            position: relative;
            z-index: 10;
        }
        .btn-back-modern:hover { 
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-1px);
            color: #1d4ed8;
        }
        .btn-back-modern .icon-circle {
            width: 24px; height: 24px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6;
            font-size: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        /* Hide scrollbars */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* == Premium Tab Navigation == */
        .tab-nav-wrap {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 6px;
            box-shadow: 0 4px 16px rgba(0, 27, 183, 0.08), 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin-bottom: 0;
        }
        .nav-tab {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 9px 0;
            border-radius: 12px;
            font-size: 11.5px;
            font-weight: 700;
            border: 1.5px solid #c8d3e0;
            background: #dde3ec;
            color: #334155;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            box-shadow: none;
            position: relative;
            overflow: hidden;
        }
        .nav-tab:active { transform: scale(0.97); }
        .nav-tab .tab-icon {
            font-size: 13px;
            opacity: 0.85;
            transition: opacity 0.2s, transform 0.2s;
        }
        .nav-tab.active-sales {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.40);
            font-weight: 700;
        }
        .nav-tab.active-sales .tab-icon { opacity: 1; transform: scale(1.15); }
        .nav-tab.active-support {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.38);
            font-weight: 700;
        }
        .nav-tab.active-support .tab-icon { opacity: 1; transform: scale(1.15); }
        .nav-tab.active-activity {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.38);
            font-weight: 700;
        }
        .nav-tab.active-activity .tab-icon { opacity: 1; transform: scale(1.15); }
        .nav-tab.active-history {
            background: linear-gradient(135deg, #001BB7 0%, #2563eb 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(0, 27, 183, 0.38);
            font-weight: 700;
        }
        .nav-tab.active-history .tab-icon { opacity: 1; transform: scale(1.15); }
        .nav-tab.active-recap {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(15, 118, 110, 0.38);
            font-weight: 700;
        }
        .nav-tab.active-recap .tab-icon { opacity: 1; transform: scale(1.15); }

        input:focus, input:focus-visible,
        select:focus, select:focus-visible,
        textarea:focus, textarea:focus-visible,
        button:focus, button:focus-visible {
            outline: none !important;
            box-shadow: none;
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16">
        <div class="mesh-bg"></div>
        
        <div class="relative z-10 w-full max-w-none mx-0 px-3 sm:px-4 py-3 sm:py-4 space-y-3 sm:space-y-4 flex-1 flex flex-col items-stretch justify-start pb-20">
            
            {{-- Back Button --}}
            <div>
                <x-ui.back-button href="{{ route($routePrefix . 'index') }}" label="Back" />
            </div>

            {{-- SECTION 1: HERO PROFILE --}}
            <div class="rounded-2xl shadow-md border border-blue-700/30 overflow-hidden relative" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="p-4 sm:p-5 text-white relative z-10">
                    <div class="flex flex-col gap-3">
                        {{-- Client Identity --}}
                        <div class="flex justify-between items-start gap-3">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="bg-white/20 backdrop-blur-md text-white text-[9px] font-bold px-2.5 py-0.5 rounded-full uppercase border border-white/25 shadow-sm inline-flex items-center">
                                        <i class="fas fa-hospital mr-1 opacity-90"></i> {{ \Illuminate\Support\Str::limit($client->customer?->customer_name ?? '-', 22) }}
                                    </span>
                                    <span class="bg-white/20 backdrop-blur-md text-white text-[9px] font-bold px-2.5 py-0.5 rounded-full uppercase border border-white/25 shadow-sm inline-flex items-center">
                                        <i class="fas fa-map-marker-alt mr-1 opacity-90"></i> {{ $client->customer?->area ?? 'Area Not Set' }}
                                    </span>
                                </div>
                                <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-white drop-shadow-sm pt-0.5">
                                    {{ $client->client_name }}
                                </h2>
                                <button type="button" onclick="uiModalOpen('handoverPicModal')" class="text-[10px] text-white/90 font-bold uppercase inline-flex items-center gap-1 hover:text-white transition">
                                    <span>PIC: <strong class="underline decoration-white/60 text-white">{{ $client->ps ?: ($client->user->name ?? '-') }}</strong></span>
                                    @if(isset($picHistories) && $picHistories->count() > 1)
                                        <span class="ml-0.5 bg-white/20 px-1.5 py-0.5 rounded text-[9px] text-white"><i class="fas fa-history"></i> {{ $picHistories->count() }} PIC</span>
                                    @endif
                                </button>
                            </div>
                            <div class="flex gap-1.5 flex-shrink-0">
                                <button type="button" onclick="openClientDetailSheet()" class="bg-white/20 hover:bg-white/30 text-white text-xs w-8 h-8 rounded-full flex items-center justify-center transition border border-white/25 shadow-sm outline-none" title="Client Details">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                @if($canEdit) 
                                    <button type="button" onclick="uiModalOpen('handoverPicModal')" class="bg-white/20 hover:bg-white/30 text-white text-xs w-8 h-8 rounded-full flex items-center justify-center transition border border-white/25 shadow-sm outline-none" title="Ganti PIC">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <button type="button" onclick="openEditClientSheet()" class="bg-amber-400 hover:bg-amber-300 text-amber-950 text-xs w-8 h-8 rounded-full flex items-center justify-center transition shadow-md outline-none" title="Edit Client">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="flex items-center justify-around gap-2 bg-white/15 backdrop-blur-md rounded-xl px-3 py-2.5 border border-white/25 shadow-sm">
                            <div class="flex flex-col items-center flex-1">
                                <span class="text-white/90 text-[9px] font-bold uppercase tracking-wider mb-0.5">Total Usage</span>
                                <div class="flex items-baseline">
                                    <span class="text-[10px] text-white/90 mr-1 font-bold">Rp</span>
                                    <span class="text-xs sm:text-sm font-mono font-extrabold text-white">{{ number_format($client->interactions->where('transaction_type', 'OUT')->sum('amount'), 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="w-px h-7 bg-white/25"></div>
                            <div class="flex flex-col items-center flex-1">
                                <span class="text-white/90 text-[9px] font-bold uppercase tracking-wider mb-0.5">Total Balance</span>
                                <div class="flex items-baseline">
                                    <span class="text-[10px] text-emerald-200 mr-1 font-bold">Rp</span>
                                    <span class="text-xs sm:text-sm font-mono font-extrabold text-white">{{ number_format($currentBalance, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: NAVIGATION TABS --}}
            <div class="tab-nav-wrap">
                <button id="btn-sales" onclick="switchTab('sales')" class="nav-tab active-sales" style="width:calc(33.33% - 4px)">
                    <i class="fas fa-plus-circle tab-icon"></i>
                    <span>Sales</span>
                </button>
                <button id="btn-support" onclick="switchTab('support')" class="nav-tab" style="width:calc(33.33% - 4px)">
                    <i class="fas fa-hand-holding-usd tab-icon"></i>
                    <span>Usage</span>
                </button>
                <button id="btn-activity" onclick="switchTab('activity')" class="nav-tab" style="width:calc(33.33% - 4px)">
                    <i class="fas fa-handshake tab-icon"></i>
                    <span>Activities</span>
                </button>
                <button id="btn-history" onclick="switchTab('history')" class="nav-tab" style="width:calc(50% - 4px)">
                    <i class="fas fa-history tab-icon"></i>
                    <span>History</span>
                </button>
                <button id="btn-recap" onclick="switchTab('recap')" class="nav-tab" style="width:calc(50% - 4px)">
                    <i class="fas fa-calendar-check tab-icon"></i>
                    <span>Recap</span>
                </button>
            </div>
            
            {{-- TAB 1: INPUT SALES (IN) --}}
            <div id="section-sales" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
                <div class="bg-white px-3.5 py-2 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-xs font-extrabold text-blue-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-plus-circle text-sm text-blue-600"></i> Input Sales Inflow
                    </h3>
                </div>
                <div class="p-3 space-y-2.5">
                    @if($hasFullAccess)
                    <form action="{{ route($routePrefix . 'interaction.store') }}" method="POST" class="space-y-2.5" onsubmit="const b=this.querySelector('[type=submit]');if(b){b.disabled=true;b.classList.add('opacity-60');}">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Hospital / Client <span class="text-red-500 font-bold">*</span></label>
                            <select id="client_id_in_mobile" class="w-full h-[34px] bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3 py-1 rounded-lg font-bold text-slate-800 transition-all" required>
                                <option value="" disabled {{ $client->customer?->sales_customer_name ? '' : 'selected' }}>Select Hospital / Institution...</option>
                                @foreach($salesCustomers as $c)
                                    <option value="{{ $c }}" @selected($client->customer?->sales_customer_name == $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1">
                                Active PIC: <strong class="text-blue-700 font-bold">{{ ($lockedPs ?? null) ?: '-' }}</strong>
                                @if(!empty($picNames) && count($picNames) > 1)
                                    &bull; Riwayat PIC: <strong class="text-slate-700">{{ implode(', ', $picNames) }}</strong>
                                @endif
                                @if($client->customer?->sales_customer_name) &bull; Linked to: <strong class="text-slate-700">{{ $client->customer?->sales_customer_name }}</strong>@endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Select Date <span class="text-red-500 font-bold">*</span></label>
                            <div class="flex gap-2">
                                <input type="date" id="tanggal_interaksi_in_mobile" class="w-full h-[34px] bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3 py-1 rounded-lg font-bold text-slate-800 transition-all" required>
                                <button type="button" onclick="fetchSalesDataMobile()" class="h-[34px] bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 font-extrabold px-3.5 rounded-lg text-xs transition whitespace-nowrap shadow-sm flex items-center">
                                    <i class="fas fa-cloud-download-alt mr-1"></i> Fetch
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Notes</label>
                            <input type="text" name="notes" class="w-full h-[34px] bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3 py-1 rounded-lg font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Optional notes...">
                        </div>

                        <div class="pt-1.5 border-t border-gray-100">
                            <h4 class="font-bold text-gray-700 text-xs flex items-center mb-2"><i class="fas fa-box-open mr-1.5 text-blue-500"></i> Sales Inflow Queue <span id="queue_count_mobile" class="ml-2 bg-blue-100 text-blue-800 text-[10px] font-semibold px-2 py-0.5 rounded-full">0</span></h4>
                            <div id="product_rows_container_mobile" class="space-y-2 max-h-[28vh] overflow-y-auto custom-scrollbar pr-1">
                                <div id="empty_queue_msg_mobile" class="text-center py-2.5 text-gray-400 italic text-xs">
                                    No data fetched yet. Click "Fetch".
                                </div>
                            </div>
                        </div>

                        <datalist id="produk-list-mobile">
                            @foreach($productNames as $prod)
                                <option value="{{ $prod }}"></option>
                            @endforeach
                        </datalist>

                        <div class="pt-0.5">
                            <button type="submit" class="w-full h-[34px] bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 rounded-lg shadow-sm transition transform active:scale-95 text-xs flex justify-center items-center gap-2"><i class="fas fa-save"></i> Save Sales Data</button>
                        </div>
                    </form>
                    @else
                    <div class="flex flex-col items-center justify-center py-5 px-3 text-center bg-gray-50/50 rounded-xl border border-dashed border-gray-200 text-xs">
                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-2 text-sm shadow-sm">
                            <i class="fas fa-sync-alt animate-spin-slow"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 text-xs mb-1">Automated Sales Synchronization</h4>
                        <p class="text-[10px] text-gray-500 leading-relaxed max-w-xs">
                            Sales transaction records sync automatically from Command Center. For special adjustments, contact <strong>Admin</strong>.
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- TAB 2: INPUT USAGE (OUT) --}}
            <div id="section-support" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative hidden">
                <div class="bg-white px-3.5 py-2 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-xs font-extrabold text-red-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-hand-holding-usd text-sm text-red-600"></i> Record Support Fund (Usage)
                    </h3>
                    <button type="button" onclick="uiModalOpen('usage-info-modal')" class="text-red-500 hover:text-red-700 hover:bg-slate-100 transition text-base flex items-center justify-center w-7 h-7 rounded-full outline-none">
                        <i class="fas fa-info-circle text-sm"></i>
                    </button>
                </div>

                <div class="p-3">
                    <form action="{{ route($routePrefix . 'interaction.support') }}" method="POST" enctype="multipart/form-data" class="space-y-2.5">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Date <span class="text-red-500 font-bold">*</span></label>
                            <input type="date" name="interaction_date" class="w-full h-[34px] bg-white border border-slate-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 text-xs px-3 py-1 rounded-lg font-bold text-slate-800 transition-all" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Disbursed Amount (Rp) <span class="text-red-500 font-bold">*</span></label>
                            <div class="flex h-[34px] rounded-lg overflow-hidden bg-white border border-slate-300 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-100 transition-all">
                                <span class="bg-slate-100 border-r border-slate-200 text-slate-600 font-bold text-[11px] px-2.5 flex items-center select-none shrink-0">Rp</span>
                                <input type="text" name="amount" onkeyup="formatRupiah(this)" class="flex-grow min-w-0 border-0 focus:outline-none text-xs font-bold text-red-800 px-3 py-1 bg-transparent font-mono" placeholder="0" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Support Purpose <span class="text-red-500 font-bold">*</span></label>
                            <input type="text" name="purpose" class="w-full h-[34px] bg-white border border-slate-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 text-xs px-3 py-1 rounded-lg font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="e.g. Transport, Clinical meeting" required>
                        </div>

                        @if($hasFullAccess)
                        <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 rounded-lg px-2.5 py-2 shadow-inner">
                            <input type="checkbox" name="direct_usage" id="direct_usage_mobile" value="1" onchange="toggleDirectUsageMobile(this)" class="w-4 h-4 text-red-600 border-slate-300 rounded focus:ring-red-500">
                            <label for="direct_usage_mobile" class="text-[11px] font-bold text-slate-700 cursor-pointer select-none">Directly record to CRM (Bypass Fund Request to Finance)</label>
                        </div>
                        @endif

                        <div id="bank-info-container-mobile" class="space-y-2.5">
                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Beneficiary Bank <span class="text-red-500 font-bold">*</span></label>
                                <input type="text" name="nama_bank" value="{{ $client->bank_name }}" class="w-full h-[34px] bg-white border border-slate-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 text-xs px-3 py-1 rounded-lg font-bold text-slate-800 transition-all" required>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Account Number <span class="text-red-500 font-bold">*</span></label>
                                    <input type="text" name="no_rekening" value="{{ $client->bank_account_number }}" class="w-full h-[34px] bg-white border border-slate-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 text-xs px-3 py-1 rounded-lg font-mono font-bold text-slate-800 transition-all" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Account Name <span class="text-red-500 font-bold">*</span></label>
                                    <input type="text" name="nama_rek" value="{{ $client->bank_account_name }}" class="w-full h-[34px] bg-white border border-slate-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 text-xs px-3 py-1 rounded-lg font-bold text-slate-800 transition-all" required>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-paperclip text-red-500"></i>
                                <span>Attachment</span>
                                <span class="text-slate-400 text-[9px] font-bold">(Max 5MB)</span>
                            </label>
                            <div class="relative flex items-center justify-between border border-slate-300 rounded-lg bg-white px-2.5 h-[34px] transition-all focus-within:border-red-500">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <i class="fas fa-cloud-upload-alt text-red-400 text-xs flex-shrink-0"></i>
                                    <span id="file-name-support-mobile" class="text-xs font-semibold text-slate-500 truncate max-w-[150px]">Select file...</span>
                                </div>
                                <label class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold text-[9px] py-0.5 px-2 rounded cursor-pointer transition shadow-sm flex items-center gap-1 flex-shrink-0">
                                    <i class="fas fa-folder-open"></i> Browse
                                    <input type="file" name="lampiran_tambahan" class="hidden" onchange="updateFileNameSupportMobile(this)">
                                </label>
                            </div>
                        </div>

                        <div class="pt-0.5">
                            <button type="submit" id="submit-btn-support-mobile" class="w-full h-[34px] bg-red-600 hover:bg-red-700 text-white font-bold px-4 rounded-lg shadow-sm transition transform active:scale-95 text-xs flex items-center justify-center gap-2"><i class="fas fa-paper-plane"></i> Submit Fund Request</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TAB 3: INPUT ACTIVITIES & ENTERTAIN --}}
            <div id="section-activity" class="space-y-2.5 hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
                    <div class="bg-white px-3.5 py-2 border-b border-slate-200 flex justify-between items-center">
                        <h3 class="text-xs font-extrabold text-orange-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-handshake text-sm text-orange-500"></i> Record Activity &amp; Entertainment
                        </h3>
                        <button type="button" onclick="uiModalOpen('activity-info-modal')" class="text-orange-500 hover:text-orange-700 hover:bg-slate-100 transition text-base flex items-center justify-center w-7 h-7 rounded-full outline-none">
                            <i class="fas fa-info-circle text-sm"></i>
                        </button>
                    </div>

                    <div class="p-3">
                        <form action="{{ route($routePrefix . 'interaction.entertain') }}" method="POST" class="space-y-2.5">
                            @csrf
                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Activity Date <span class="text-red-500 font-bold">*</span></label>
                                <input type="date" name="interaction_date" class="w-full h-[34px] bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-1 rounded-lg font-bold text-slate-800 transition-all" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Description &amp; Agenda <span class="text-red-500 font-bold">*</span></label>
                                <textarea name="notes" rows="2" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-1.5 rounded-lg font-bold text-slate-800 transition-all resize-none placeholder-slate-400" placeholder="Activity description &amp; notes..." required></textarea>
                            </div>
                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 mb-1 uppercase tracking-wider">Expense Amount (Rp) <span class="text-red-500 font-bold">*</span></label>
                                <div class="flex h-[34px] rounded-lg overflow-hidden bg-white border border-slate-300 focus-within:border-orange-500 focus-within:ring-2 focus-within:ring-orange-100 transition-all">
                                    <span class="bg-slate-100 border-r border-slate-200 text-slate-600 font-bold text-[11px] px-2.5 flex items-center select-none shrink-0">Rp</span>
                                    <input type="text" name="amount" onkeyup="formatRupiah(this)" class="flex-grow min-w-0 border-0 focus:outline-none text-xs font-bold text-orange-800 px-3 py-1 bg-transparent font-mono" placeholder="0" required>
                                </div>
                            </div>
                            <button type="submit" class="w-full h-[34px] bg-orange-600 hover:bg-orange-700 text-white font-bold px-4 rounded-lg shadow-sm transition transform active:scale-95 flex items-center justify-center gap-2 text-xs"><i class="fas fa-save"></i> Save Activity</button>
                        </form>
                    </div>
                </div>

                {{-- ACTIVITY LIST --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    @php $totalActivity = $activities->sum('amount'); @endphp
                    <div class="px-3.5 py-2 border-b border-gray-150 bg-gray-50 flex justify-between items-center flex-wrap gap-2">
                        <h4 class="font-bold text-gray-700 text-xs uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-history text-orange-500"></i> Activity History
                        </h4>
                        <div class="bg-orange-100 border border-orange-200 text-orange-800 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold shadow-sm">
                            Rp {{ number_format($totalActivity, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    {{-- Mini Filter --}}
                    <div class="px-3.5 py-1.5 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Year</span>
                        <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="m-0">
                            <input type="hidden" name="tab" value="activity"> 
                            <select name="activity_year" onchange="this.form.submit()" class="pl-2 pr-7 py-1 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-orange-500 cursor-pointer">
                                <option value="">All</option>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ (request('activity_year') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>

                    <div class="p-3 space-y-2.5">
                        @forelse($activities as $act)
                        <div class="bg-white rounded-xl border border-gray-100 p-2.5 shadow-sm relative flex flex-col gap-1.5">
                            <div class="flex justify-between items-start gap-1">
                                <span class="text-[10px] text-gray-400 font-bold font-mono">{{ \Carbon\Carbon::parse($act->interaction_date)->format('d M Y') }}</span>
                                <span class="font-mono font-bold text-orange-600 text-xs">Rp {{ number_format($act->amount, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <button type="button" onclick="openViewTransactionModal({
                                    id: '{{ $act->id }}',
                                    jenis: 'ENTERTAIN',
                                    tanggal: '{{ \Carbon\Carbon::parse($act->interaction_date)->translatedFormat('d F Y') }}',
                                    produk: '{{ addslashes($act->product_name) }}',
                                    nominal: '{{ number_format($act->amount, 0, ',', '.') }}',
                                    rate: '0',
                                    valueNet: '0',
                                    catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->notes)) }}'
                                })" class="text-left font-bold text-gray-800 hover:text-orange-600 hover:underline text-xs">
                                    {{ $act->product_name }}
                                </button>
                                <p class="text-[11px] text-gray-500 mt-0.5 leading-snug">{{ $act->notes }}</p>
                            </div>
                            <div class="flex items-center justify-end pt-1.5 border-t border-gray-50 text-[10px] text-gray-500">
                                @if($hasFullAccess)
                                <div class="flex gap-2">
                                    <button type="button" 
                                        onclick="openEditTransactionModal({
                                            id: '{{ $act->id }}',
                                            jenis: 'ENTERTAIN',
                                            tanggal: '{{ $act->interaction_date }}',
                                            produk: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->product_name)) }}', 
                                            nominal: '{{ $act->amount }}',
                                            catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->notes)) }}'
                                        })"
                                        class="text-orange-400 hover:text-orange-600 text-xs transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route($routePrefix . 'interaction.destroy', $act->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Delete this activity?');" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-gray-300 hover:text-red-500 text-xs transition"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-gray-400 italic text-xs">No activity records found.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- TAB 4: TRANSACTION HISTORY --}}
            <div id="section-history" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hidden">
                <div class="px-3.5 py-2 border-b border-slate-200 flex justify-between items-center bg-white flex-wrap gap-2">
                    <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-history text-slate-500"></i> Transaction History
                    </h3>
                    {{-- Year Filter --}}
                    <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="m-0">
                        <input type="hidden" name="tab" value="history"> 
                        <select name="history_year" onchange="this.form.submit()" class="pl-2 pr-7 py-1 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm focus:border-blue-500 cursor-pointer">
                            <option value="">All</option>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ (request('history_year') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
                
                <div class="p-3 space-y-2.5">
                    @forelse ($interactions as $item)
                        @if($item->transaction_type == 'ENTERTAIN') @continue @endif
                        @php
                            $isOut = $item->transaction_type == 'OUT';
                            $rate = (float)($item->commission_rate ?? 0);
                            $displayNote = trim(preg_replace('/\[Rate:[\d\.]+%?\]\s*/', '', $item->notes ?? ''));
                            $valueNet = (!$isOut) ? (($item->sales_amount > 0 ? $item->sales_amount : $item->amount) * ($rate/100)) : 0;
                        @endphp
                        
                        <div class="rounded-2xl border {{ $isOut ? 'border-rose-200/70 bg-rose-50/40' : ($loop->odd ? 'border-blue-200/70 bg-blue-50/40' : 'border-slate-200 bg-white') }} p-3 shadow-sm relative flex flex-col gap-1.5">
                            <div class="flex justify-between items-center pb-1.5 border-b border-slate-200/50">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] text-slate-500 font-bold font-mono"><i class="far fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($item->interaction_date)->format('d M Y') }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[8px] font-bold tracking-wider uppercase {{ $isOut ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                        {{ $isOut ? 'OUT' : 'IN' }}
                                    </span>
                                </div>
                                @if($hasFullAccess)
                                <div class="flex items-center gap-3">
                                     <button type="button" onclick="openEditTransactionModal({id: '{{ $item->id }}', jenis: '{{ $item->transaction_type }}', tanggal: '{{ $item->interaction_date }}', produk: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $item->product_name)) }}', nominal: '{{ ($item->transaction_type == 'IN') ? $item->sales_amount : $item->amount }}', rate: '{{ $rate }}', catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $displayNote)) }}'})" class="text-blue-500 hover:text-blue-700 text-xs transition" title="Edit Record"><i class="fas fa-edit"></i></button>
                                    <form action="{{ route($routePrefix . 'interaction.destroy', $item->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Delete this transaction?');" class="inline">@csrf @method('DELETE')<button class="text-slate-300 hover:text-rose-600 text-xs transition" title="Delete Record"><i class="fas fa-trash-alt"></i></button></form>
                                </div>
                                @endif
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-start">
                                    <button type="button" onclick="openViewTransactionModal({
                                        id: '{{ $item->id }}',
                                        jenis: '{{ $item->transaction_type }}',
                                        tanggal: '{{ \Carbon\Carbon::parse($item->interaction_date)->translatedFormat('d F Y') }}',
                                        produk: '{{ addslashes($item->product_name) }}',
                                        nominal: '{{ number_format(($item->transaction_type == 'IN') ? $item->sales_amount : $item->amount, 0, ',', '.') }}',
                                        rate: '{{ $rate }}',
                                        valueNet: '{{ number_format($valueNet, 0, ',', '.') }}',
                                        catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $displayNote)) }}'
                                    })" class="text-left font-extrabold text-[11px] text-slate-800 hover:underline focus:outline-none mb-1 flex items-center gap-1">
                                        <i class="fas fa-info-circle text-[10px] text-slate-400"></i> Transaction Details
                                    </button>
                                </div>
                                
                                <div class="flex flex-wrap gap-1 mt-0.5">
                                    @foreach(explode(',', $item->product_name) as $subItem)
                                        @if(trim($subItem) != '')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-white text-slate-700 border border-slate-200 shadow-sm leading-tight">
                                                <i class="fas {{ $isOut ? 'fa-file-invoice-dollar text-rose-500' : 'fa-box text-blue-500' }} text-[9px] mr-1 shrink-0"></i> {{ trim($subItem) }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>

                                @if($displayNote)
                                    <div class="mt-1.5 text-[10px] text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 flex items-start gap-1">
                                        <i class="fas fa-comment-dots text-slate-400 mt-0.5"></i>
                                        <span>{{ $displayNote }}</span>
                                    </div>
                                @endif
                            </div>

                            @if(!$isOut)
                            <div class="grid grid-cols-2 gap-2 pt-1.5 border-t border-slate-200/50">
                                <div class="bg-white/80 rounded-lg px-2.5 py-1.5 border border-slate-200">
                                    <span class="block text-[8px] text-slate-400 uppercase font-extrabold tracking-wider">Gross Sales</span>
                                    <span class="font-mono text-xs font-extrabold text-slate-700">Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="bg-blue-50/50 rounded-lg px-2.5 py-1.5 border border-blue-100 text-right">
                                    <span class="block text-[8px] text-blue-500 uppercase font-extrabold tracking-wider">Net ({{ $rate }}%)</span>
                                    <span class="font-mono text-xs font-extrabold text-blue-700">Rp {{ number_format($valueNet, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            @else
                            <div class="pt-1.5 border-t border-slate-200/50">
                                <div class="bg-rose-50/50 rounded-lg px-2.5 py-1.5 border border-rose-100 flex items-center justify-between">
                                    <span class="block text-[8px] text-rose-500 uppercase font-extrabold tracking-wider">Disbursed Usage (OUT)</span>
                                    <span class="font-mono text-xs font-extrabold text-rose-700">Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-gray-400 text-xs">No transaction records found.</div>
                    @endforelse
                </div>
                @if($interactions->hasPages())
                    <div class="bg-gray-50 px-3.5 py-2 border-t border-gray-200 text-xs">{{ $interactions->links() }}</div>
                @endif
            </div>

            {{-- TAB 5: YEARLY RECAP --}}
            <div id="section-recap" class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hidden">
                <div class="px-3.5 py-2 border-b border-gray-150 flex justify-between items-center bg-gray-50/50 flex-wrap gap-2">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider flex items-center gap-1">
                        <i class="fas fa-chart-bar text-blue-600 mr-1"></i>
                        Recap {{ $year }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="m-0">
                            <input type="hidden" name="tab" value="recap"> 
                            <select name="year" onchange="this.form.submit()" class="pl-2 pr-7 py-1 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm cursor-pointer">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                        <a href="{{ route($routePrefix . 'client.export', ['client' => $client->id, 'year' => $year]) }}" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold py-1 px-2.5 rounded-lg shadow-sm border border-emerald-700"><i class="fas fa-file-excel mr-1"></i> Export</a>
                    </div>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-xs text-left min-w-[340px]">
                        <thead class="bg-slate-50 text-slate-600 uppercase text-[9px] font-bold tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-2.5 py-2">Month</th>
                                <th class="px-2 py-2 text-right text-blue-700 bg-blue-50/30">Net (IN)</th>
                                <th class="px-2 py-2 text-right text-red-600">OUT</th>
                                <th class="px-2.5 py-2 text-right text-gray-800">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="bg-yellow-50/70 text-[10px] border-b border-yellow-100">
                                <td class="px-2.5 py-1.5 font-bold text-gray-700 italic" colspan="3"><i class="fas fa-forward text-yellow-600 mr-1"></i> {{ $startingLabel }} </td>
                                <td class="px-2.5 py-1.5 text-right font-mono font-bold text-gray-900 bg-yellow-50">{{ number_format($startingBalance, 0, ',', '.') }} </td>
                            </tr>
                            @foreach ($recap as $r)
                            <tr class="{{ $loop->odd ? 'bg-blue-50/50' : 'bg-white' }} hover:bg-blue-100/50 border-b border-gray-100 text-[11px] transition">
                                <td class="px-2.5 py-1.5">
                                    <button type="button" onclick="showMonthlyDetail('{{ $r['month_name'] }}', {{ $loop->iteration }}, {{ $year }})" class="font-bold text-blue-600 hover:text-blue-800 hover:underline text-left focus:outline-none flex items-center gap-1">
                                        <i class="far fa-calendar-alt text-[10px] text-blue-400"></i>
                                        <span>{{ $r['month_name'] }}</span>
                                    </button>
                                </td>
                                <td class="px-2 py-1.5 text-right font-mono font-bold text-blue-800 bg-blue-50/20">{{ $r['net_value'] > 0 ? number_format($r['net_value'], 0, ',', '.') : '-' }}</td>
                                <td class="px-2 py-1.5 text-right font-mono text-red-600">{{ $r['out'] > 0 ? number_format($r['out'], 0, ',', '.') : '-' }}</td>
                                <td class="px-2.5 py-1.5 text-right font-mono font-bold text-gray-900">{{ number_format($r['saldo'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 font-extrabold text-[10px] text-gray-800 border-t-2 border-gray-200">
                            <tr>
                                <td class="px-2.5 py-2 uppercase">Total</td>
                                <td class="px-2 py-2 text-right text-blue-900 bg-blue-100">{{ number_format($yearlyTotals['net_value'], 0, ',', '.') }}</td>
                                <td class="px-2 py-2 text-right text-red-700">{{ number_format($yearlyTotals['out'], 0, ',', '.') }}</td>
                                <td class="px-2.5 py-2 text-right font-mono text-gray-900">{{ number_format($yearlyTotals['saldo'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('modals')
        <!-- BOTTOM SHEET: CLIENT PROFILE DETAILS -->
        <div id="client-detail-sheet" class="fixed inset-0 z-[9999] flex items-end justify-center bg-slate-950/70 opacity-0 pointer-events-none transition-opacity duration-300">
            <div id="client-detail-sheet-card" class="bg-white w-full max-h-[85vh] rounded-t-[2.5rem] shadow-2xl border-t border-slate-200 overflow-hidden transform translate-y-full transition-transform duration-300 flex flex-col">
                <div class="w-full flex justify-center py-3 bg-white border-b border-slate-100 shrink-0">
                    <div class="w-12 h-1.5 bg-slate-300 rounded-full"></div>
                </div>
                
                <div class="px-6 py-4 bg-white border-b border-slate-200 flex justify-between items-center shrink-0 relative z-10 shadow-sm">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-600"></i> Client Profile Details
                    </h3>
                    <button type="button" onclick="closeClientDetailSheet()" class="w-7 h-7 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 rounded-full transition outline-none">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                
                <div class="p-5 overflow-y-auto space-y-5 text-slate-700 pb-10">
                    <!-- 1. PERSONAL INFORMATION -->
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 shadow-inner space-y-3">
                        <h4 class="text-[11px] font-extrabold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-200 pb-2">
                            <i class="fas fa-user"></i> Personal Profile
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            @if($client->position)
                            <div class="col-span-2 flex items-start gap-2.5">
                                <i class="fas fa-id-badge text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-400 uppercase font-bold">Position / Role</p><p class="font-bold text-slate-800">{{ $client->position }}</p></div>
                            </div>
                            @endif
                            <div class="col-span-2 flex items-start gap-2.5">
                                <i class="fas fa-envelope text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-400 uppercase font-bold">Email</p><p class="font-medium text-slate-800 break-all">{{ $client->email ?? '-' }}</p></div>
                            </div>
                            <div class="col-span-2 flex items-start gap-2.5">
                                <i class="fab fa-whatsapp text-emerald-500 mt-0.5 w-4 text-center text-sm"></i>
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-bold">Phone / WhatsApp</p>
                                    @if($client->phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->phone) }}" target="_blank" class="font-bold text-blue-600 hover:underline">{{ $client->phone }}</a>
                                    @else
                                        <p class="font-medium text-slate-500">-</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-birthday-cake text-pink-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-400 uppercase font-bold">Date of Birth</p><div class="font-medium text-slate-800">{{ $client->birthdate ? \Carbon\Carbon::parse($client->birthdate)->format('d M Y') : '-' }}</div></div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-star text-yellow-500 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-400 uppercase font-bold">Hobbies</p><p class="font-medium text-slate-800">{{ $client->hobby ?? '-' }}</p></div>
                            </div>
                            <div class="col-span-2 flex items-start gap-2.5">
                                <i class="fas fa-home text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-400 uppercase font-bold">Home Address</p><p class="leading-relaxed text-slate-700">{{ $client->address ?? '-' }}</p></div>
                            </div>
                            <div class="col-span-2 flex items-start gap-2.5">
                                <i class="fas fa-percent text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-400 uppercase font-bold">Commission Rate</p><p class="font-bold text-slate-800">{{ $client->commission_rate ? (float)$client->commission_rate . '%' : '-' }}</p></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 2. INSTITUTION INFORMATION -->
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 shadow-inner space-y-3">
                        <h4 class="text-[11px] font-extrabold text-orange-600 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-200 pb-2">
                            <i class="fas fa-building"></i> Institution Profile
                        </h4>
                        <div class="space-y-3.5 text-xs">
                            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                                <p class="text-[8px] text-slate-400 font-bold uppercase mb-0.5">Institution / Hospital Name</p>
                                <p class="font-extrabold text-sm leading-tight text-slate-800">{{ $client->customer?->customer_name ?? '-' }}</p>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-calendar-alt text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-bold">Anniversary Date</p>
                                    <div class="flex items-center font-medium text-slate-800">
                                        @if($client->customer?->anniversary_date)
                                            <span>{{ \Carbon\Carbon::parse($client->customer->anniversary_date)->format('d F Y') }}</span>
                                            <span class="ml-2 text-[8px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full font-bold shadow-sm">{{ \Carbon\Carbon::parse($client->customer->anniversary_date)->age }} Yrs</span>
                                        @else <span class="italic text-slate-400">Not set</span> @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-map-marked-alt text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-400 uppercase font-bold">Office Address</p><p class="leading-relaxed text-slate-700">{{ $client->customer?->displayAddress() ?: '-' }}</p></div>
                            </div>
                            
                            <!-- Pharmacist -->
                            <div class="mt-3 pt-3 border-t border-slate-200 space-y-2">
                                <p class="text-[9px] text-slate-400 font-bold uppercase flex items-center gap-1"><i class="fas fa-user-md text-slate-400"></i> Pharmacist-in-Charge</p>
                                <div class="grid grid-cols-2 gap-3 text-xs bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                                    <div><p class="text-[8px] text-slate-400 uppercase font-semibold">Name</p><p class="font-bold text-slate-800">{{ $client->customer?->pharmacist_name ?? '-' }}</p></div>
                                    <div><p class="text-[8px] text-slate-400 uppercase font-semibold">SIPA</p><p class="font-bold text-slate-800 font-mono">{{ $client->customer?->pharmacist_license_no ?? '-' }}</p></div>
                                    <div class="col-span-2"><p class="text-[8px] text-slate-400 uppercase font-semibold">Phone</p><p class="font-bold text-slate-800">{{ $client->customer?->pharmacist_phone ?? '-' }}</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 3. BANKING INFORMATION -->
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 shadow-inner space-y-3">
                        <h4 class="text-[11px] font-extrabold text-emerald-600 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-200 pb-2">
                            <i class="fas fa-wallet"></i> Banking Information
                        </h4>
                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="text-[8px] text-slate-400 uppercase tracking-wider mb-0.5">Bank & Account</p>
                                <div class="flex flex-col">
                                    <span class="font-extrabold text-sm text-slate-800">{{ $client->bank_name ?? 'BANK -' }}</span>
                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $client->bank_account_name ? 'A/C Holder: '.$client->bank_account_name : '' }}</p>
                                    <div class="flex items-center gap-1.5 font-mono text-emerald-600 font-bold text-xs bg-emerald-100/60 px-2.5 py-1 rounded-lg w-fit border border-emerald-200 shadow-sm mt-1.5">
                                        <i class="fas fa-credit-card text-[9px] opacity-70"></i> <span>{{ $client->bank_account_number ?? '----' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-slate-200 pt-2.5 flex justify-between items-center">
                                <span class="text-[8px] text-slate-400 uppercase tracking-wider">Opening Balance</span>
                                <span class="text-sm font-mono font-bold text-slate-800">
                                    Rp {{ number_format($client->opening_balance ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            #client-detail-sheet {
                transition: opacity 0.3s ease;
            }
            #client-detail-sheet-card {
                transition: transform 0.4s cubic-bezier(0.32, 0.94, 0.6, 1);
            }
        </style>

        {{-- MODAL: EDIT CLIENT PROFILE (MOBILE) --}}
        <x-ui.modal
            id="editClientModal"
            maxWidth="max-w-lg"
            title="Edit Client Profile"
            subtitle="Perbarui data profil personal, instansi, apoteker, dan informasi rekening."
            icon="fa-edit"
            iconClass="bg-blue-600 text-white"
        >
            <form id="editClientFormMobile" action="{{ route($routePrefix . 'client.update', $client->id) }}" method="POST" class="space-y-3.5">
                @csrf @method('PUT')

                @if($canEdit)
                <div class="flex items-center justify-between gap-2 rounded-xl border border-indigo-100 bg-indigo-50/80 p-2.5 text-xs">
                    <div>
                        <span class="block text-[9px] font-bold uppercase text-indigo-500">PIC Saat Ini</span>
                        <span class="font-bold text-indigo-950">{{ $client->ps ?: ($client->user->name ?? '-') }}</span>
                    </div>
                    <x-ui.button variant="soft-blue" size="xs" type="button" icon="fa-exchange-alt" onclick="uiModalClose('editClientModal'); uiModalOpen('handoverPicModal');">
                        Ganti PIC
                    </x-ui.button>
                </div>
                @endif

                {{-- 1. PERSONAL INFORMATION --}}
                <div class="bg-white rounded-xl border border-blue-200/80 shadow-sm p-3.5 space-y-2.5">
                    <h4 class="text-blue-900 text-xs font-bold uppercase tracking-wider border-b border-blue-100 pb-1.5 flex items-center">
                        <span class="w-4 h-4 bg-blue-600 text-white text-[9px] rounded-full flex items-center justify-center mr-1.5 font-bold">1</span>
                        Personal Profile
                    </h4>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase">Client Name <span class="text-red-500">*</span></label>
                        <input type="text" name="client_name" value="{{ old('client_name', $client->client_name) }}" required class="w-full border border-slate-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-xs px-3 py-2 font-semibold outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Position / Role</label>
                        <input type="text" name="position" value="{{ old('position', $client->position) }}" class="w-full border border-slate-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 text-xs px-3 py-2 outline-none" placeholder="Position / Role">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Phone (WA)</label>
                            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-blue-500 px-3 py-2 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Email</label>
                            <input type="email" name="email" value="{{ old('email', $client->email) }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-blue-500 px-3 py-2 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Date of Birth</label>
                            <input type="date" name="birthdate" value="{{ old('birthdate', optional($client->birthdate)->format('Y-m-d')) }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-blue-500 px-2.5 py-2 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Hobbies</label>
                            <input type="text" name="hobby" value="{{ old('hobby', $client->hobby) }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-blue-500 px-3 py-2 outline-none" placeholder="Hobbies">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Home Address</label>
                        <textarea name="address" rows="2" class="w-full border border-slate-300 rounded-lg text-xs focus:border-blue-500 px-3 py-2 resize-none outline-none">{{ old('address', $client->address) }}</textarea>
                    </div>
                </div>

                {{-- 2. COMPANY INFORMATION --}}
                <div class="bg-white rounded-xl border border-orange-200/80 shadow-sm p-3.5 space-y-2.5">
                    <h4 class="text-orange-900 text-xs font-bold uppercase tracking-wider border-b border-orange-100 pb-1.5 flex items-center">
                        <span class="w-4 h-4 bg-orange-500 text-white text-[9px] rounded-full flex items-center justify-center mr-1.5 font-bold">2</span>
                        Company Profile
                    </h4>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase">Institution / Hospital Name <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', $client->customer?->customer_name ?? '') }}" required class="w-full border border-slate-300 rounded-lg focus:border-orange-500 text-xs px-3 py-2 font-semibold outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase">Sales System Name (Command Center)</label>
                        <input type="text" name="sales_customer_name" value="{{ old('sales_customer_name', $client->customer?->sales_customer_name) }}" class="w-full border border-slate-300 rounded-lg focus:border-orange-500 text-xs px-3 py-2 font-semibold outline-none" placeholder="Name in Sales system (optional)">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Area</label>
                            <input type="text" name="area" value="{{ old('area', $client->customer?->area ?? '') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 px-3 py-2 outline-none" placeholder="Area">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Institution Type</label>
                            <select name="customer_type" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 px-2.5 py-2 text-slate-700 bg-white outline-none">
                                <option value="">— Select Type —</option>
                                @foreach(\App\Models\Customer::TYPES as $t)
                                    <option value="{{ $t }}" @selected(old('customer_type', $client->customer?->customer_type) == $t)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Office Phone</label>
                            <input type="text" name="company_phone" value="{{ old('company_phone', $client->customer?->phone ?? '') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 px-3 py-2 outline-none" placeholder="Phone">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Anniversary Date</label>
                            <input type="date" name="anniversary_date" value="{{ old('anniversary_date', $client->customer?->anniversary_date?->format('Y-m-d')) }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 px-2.5 py-2 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Tax ID / NPWP</label>
                            <input type="text" name="npwp" value="{{ old('npwp', $client->customer?->npwp ?? '') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 px-3 py-2 outline-none" placeholder="Tax ID">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Office Email</label>
                            <input type="email" name="company_email" value="{{ old('company_email', $client->customer?->email ?? '') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 px-3 py-2 outline-none" placeholder="Email">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Office Address</label>
                        <textarea name="company_address" rows="2" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 px-3 py-2 resize-none outline-none">{{ old('company_address', $client->customer?->displayAddress() ?? '') }}</textarea>
                    </div>
                    <div class="border-t border-orange-100 pt-2.5">
                        <label class="block text-[10px] font-bold text-orange-700 mb-2 uppercase"><i class="fas fa-user-md mr-1"></i> Pharmacist Information</label>
                        <div class="space-y-2">
                            <input type="text" name="pharmacist_name" value="{{ old('pharmacist_name', $client->customer?->pharmacist_name ?? '') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 px-3 py-1.5 outline-none" placeholder="Pharmacist Name">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="pharmacist_license_no" value="{{ old('pharmacist_license_no', $client->customer?->pharmacist_license_no ?? '') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 px-2.5 py-1.5 outline-none" placeholder="License No. (SIPA)">
                                <input type="text" name="pharmacist_phone" value="{{ old('pharmacist_phone', $client->customer?->pharmacist_phone ?? '') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 px-2.5 py-1.5 outline-none" placeholder="Phone">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. BANKING & FINANCIALS --}}
                <div class="bg-white rounded-xl border border-emerald-200/80 shadow-sm p-3.5 space-y-2.5">
                    <h4 class="text-emerald-900 text-xs font-bold uppercase tracking-wider border-b border-emerald-100 pb-1.5 flex items-center">
                        <span class="w-4 h-4 bg-emerald-600 text-white text-[9px] rounded-full flex items-center justify-center mr-1.5 font-bold">3</span>
                        Banking &amp; Financials
                    </h4>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $client->bank_name) }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-emerald-500 px-3 py-2 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Account Number</label>
                            <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $client->bank_account_number) }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-emerald-500 px-3 py-2 font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Account Holder (A/N)</label>
                            <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $client->bank_account_name) }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-emerald-500 px-3 py-2 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 border-t border-emerald-100 pt-2.5">
                        <div>
                            <label class="block text-[10px] font-bold text-emerald-700 mb-1 uppercase">Commission Rate (%)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate', $client->commission_rate) }}" class="w-full border border-emerald-200 bg-emerald-50/30 rounded-lg text-xs font-bold text-emerald-800 focus:border-emerald-500 px-3 py-2 outline-none">
                                <span class="absolute right-2.5 top-2 text-emerald-600 font-bold text-[10px]">%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-emerald-700 mb-1 uppercase">Opening Balance</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-2 text-emerald-600 font-bold text-[9px]">Rp</span>
                                <input type="number" name="opening_balance" value="{{ old('opening_balance', $client->opening_balance) }}" class="w-full pl-6 border border-emerald-200 bg-emerald-50/30 rounded-lg text-xs font-bold text-emerald-800 focus:border-emerald-500 px-3 py-2 outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Cancel</x-ui.button>
                <x-ui.button variant="primary" size="sm" type="submit" form="editClientFormMobile" icon="fa-save">Update Profile</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endpush

    {{-- MODAL: EDIT TRANSACTION --}}
    @push('modals')
        <x-ui.modal
            id="editTransactionModal"
            maxWidth="max-w-md"
            title="Edit Transaction"
            subtitle="Perbarui tanggal, produk/keperluan, nominal, atau catatan."
            icon="fa-edit"
            iconClass="bg-blue-600 text-white"
        >
            <form id="formEditTransaction" action="#" method="POST" class="space-y-3.5">
                @csrf @method('PUT')
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Transaction Type</span>
                    <span id="editTransTypeBanner" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Sales (IN)</span>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase">Date</label>
                    <input type="date" name="interaction_date" id="edit_tanggal" class="w-full border border-slate-300 rounded-lg focus:border-blue-500 px-3 py-2 text-xs outline-none" required>
                </div>
                <div id="wrapper_produk">
                    <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase" id="label_produk">Product / Purpose</label>
                    <input type="text" name="" id="edit_produk" class="w-full border border-slate-300 rounded-lg focus:border-blue-500 px-3 py-2 text-xs font-bold outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase">Amount (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-slate-500 text-xs">Rp</span>
                        <input type="text" name="" id="edit_nominal" onkeyup="formatRupiah(this)" class="w-full pl-8 border border-slate-300 rounded-lg focus:border-blue-500 px-3 py-2 font-mono font-bold text-sm outline-none" required>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 mb-1 uppercase">Notes</label>
                    <textarea name="notes" id="edit_catatan" rows="2" class="w-full border border-slate-300 rounded-lg focus:border-blue-500 px-3 py-2 text-xs resize-none outline-none"></textarea>
                </div>
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Cancel</x-ui.button>
                <x-ui.button variant="primary" size="sm" type="submit" form="formEditTransaction" id="btnUpdateTrans" icon="fa-save">Update Transaction</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL: TRANSACTION DETAILS (READ-ONLY) --}}
        <x-ui.modal
            id="viewTransactionModal"
            maxWidth="max-w-md"
            title="Transaction Details"
            subtitle="Detail rincian transaksi tercatat."
            icon="fa-info-circle"
            iconClass="bg-blue-600 text-white"
        >
            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Date</span>
                        <span id="view_tanggal" class="text-xs font-semibold text-slate-800"></span>
                    </div>
                    <div>
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Type</span>
                        <span id="view_jenis" class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold shadow-sm border"></span>
                    </div>
                </div>

                <div>
                    <span id="view_label_produk" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Product / Purpose</span>
                    <span id="view_produk" class="text-sm font-extrabold text-slate-950 block mt-0.5"></span>
                </div>

                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/80 grid grid-cols-2 gap-4">
                    <div>
                        <span id="view_label_nominal" class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Amount</span>
                        <span id="view_nominal" class="text-base font-mono font-bold text-slate-800"></span>
                    </div>
                    <div id="view_wrapper_commission" class="hidden">
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Commission (Rate)</span>
                        <span id="view_rate" class="text-xs font-bold text-blue-700"></span>
                    </div>
                </div>

                <div>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Notes</span>
                    <p id="view_catatan" class="text-xs text-slate-600 bg-slate-50 border border-slate-200/80 rounded-lg p-2.5 mt-1 whitespace-pre-line italic"></p>
                </div>
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Close</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL: MONTHLY TRANSACTION DETAILS --}}
        <x-ui.modal
            id="monthlyDetailModal"
            maxWidth="max-w-md"
            title="Monthly Transactions"
            subtitle="Rincian seluruh transaksi pada bulan terpilih."
            icon="fa-calendar-alt"
            iconClass="bg-slate-800 text-white"
        >
            <div class="space-y-3 text-xs" id="monthly_detail_body">
                {{-- Filled dynamically via JS --}}
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Close</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL: SERAH TERIMA / GANTI PIC --}}
        <x-ui.modal
            id="handoverPicModal"
            maxWidth="max-w-md"
            title="Serah Terima PIC"
            subtitle="Pilih karyawan Marketing atau Operasional sebagai PIC baru."
            icon="fa-exchange-alt"
            iconClass="bg-indigo-600 text-white"
        >
            <form id="handoverPicFormMobile" action="{{ route($routePrefix . 'client.handover', $client->id) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                {{-- Info Klien & PIC Saat Ini --}}
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 space-y-1.5">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <span class="text-slate-500 font-bold block uppercase text-[9px]">Instansi</span>
                            <span class="font-bold text-indigo-800 text-xs">{{ $client->customer?->customer_name ?? '-' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-500 font-bold block uppercase text-[9px]">Klien</span>
                            <span class="font-bold text-slate-800 text-xs">{{ $client->client_name }}</span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-indigo-200/70 flex items-center justify-between">
                        <div>
                            <span class="text-slate-500 font-bold block uppercase text-[9px]">PIC Saat Ini</span>
                            <span class="font-extrabold text-indigo-900">{{ $client->ps ?: ($client->user->name ?? '-') }}</span>
                        </div>
                        <span class="bg-emerald-100 text-emerald-800 text-[9px] font-bold px-2 py-0.5 rounded-full">Aktif</span>
                    </div>
                </div>

                {{-- Form Input Handover --}}
                <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-700">PIC Baru</span>
                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">Marketing &amp; Operasional</span>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 mb-1">Pilih Karyawan (Marketing / Operasional) <span class="text-red-500">*</span></label>
                        <select id="handover_user_select_mobile" name="new_user_id" required onchange="const sel = this.options[this.selectedIndex]; if(sel.getAttribute('data-name')) document.getElementById('handover_pic_name_mobile').value = sel.getAttribute('data-name');" class="w-full bg-white border border-slate-300 focus:border-indigo-500 rounded-lg text-xs px-2.5 py-2 font-medium outline-none">
                            <option value="">— Pilih Karyawan Marketing / Operasional —</option>
                            @foreach($availableUsers ?? [] as $u)
                                <option value="{{ $u->id }}" data-name="{{ $u->name }}" @selected(old('new_user_id') == $u->id)>
                                    {{ $u->name }} ({{ $u->divisi ?: 'Marketing & Operasional' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 mb-1">Nama PIC di Command Center <span class="text-red-500">*</span></label>
                        <input type="text" id="handover_pic_name_mobile" name="new_ps" required value="{{ old('new_ps') }}" class="w-full border border-slate-300 focus:border-indigo-500 rounded-lg text-xs px-2.5 py-2 font-bold outline-none" placeholder="Misal: Indah">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 mb-1">Tanggal Efektif <span class="text-red-500">*</span></label>
                        <input type="date" name="handover_date" value="{{ old('handover_date', date('Y-m-d')) }}" required class="w-full border border-slate-300 focus:border-indigo-500 rounded-lg text-xs px-2.5 py-2 font-semibold outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 mb-1">Catatan Handover</label>
                        <textarea name="handover_notes" rows="2" class="w-full border border-slate-300 focus:border-indigo-500 rounded-lg text-xs px-2.5 py-1.5 resize-none outline-none" placeholder="Alasan serah terima...">{{ old('handover_notes') }}</textarea>
                    </div>
                </div>

                {{-- Riwayat --}}
                <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Riwayat PIC</h4>
                    <div class="space-y-1.5 max-h-28 overflow-y-auto custom-scrollbar">
                        @forelse($client->picHistories ?? [] as $hist)
                            <div class="flex items-center justify-between text-[11px] p-1.5 rounded-lg {{ is_null($hist->end_date) ? 'bg-emerald-50 text-emerald-900 font-bold border border-emerald-200' : 'bg-slate-50 text-slate-700' }}">
                                <span>{{ $hist->pic_name }} {{ is_null($hist->end_date) ? '(Aktif)' : '' }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $hist->start_date ? $hist->start_date->format('d/m/y') : '-' }} &rarr; {{ $hist->end_date ? $hist->end_date->format('d/m/y') : 'Now' }}</span>
                            </div>
                        @empty
                            <div class="text-slate-400 text-center py-1">Belum ada riwayat.</div>
                        @endforelse
                    </div>
                </div>
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Batal</x-ui.button>
                <x-ui.button variant="primary" size="sm" type="submit" form="handoverPicFormMobile" icon="fa-check">Simpan</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL: USAGE WORKFLOW INFO --}}
        <x-ui.modal
            id="usage-info-modal"
            maxWidth="max-w-md"
            title="Disbursement Workflow Info"
            subtitle="Alur pencatatan dukungan dana & pengajuan ke Finance."
            icon="fa-info-circle"
            iconClass="bg-red-600 text-white"
        >
            <div class="space-y-2.5 text-xs text-slate-600 leading-relaxed">
                <p class="font-bold text-red-600 flex items-center gap-1.5"><i class="fas fa-paper-plane"></i> Automated Fund Request Creation</p>
                <p>Submitting this usage form will automatically create a Fund Request (Finance). Funds will be processed upon approval by the Division Head and Finance.</p>
            </div>
            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Got it</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL: ACTIVITY INFO --}}
        <x-ui.modal
            id="activity-info-modal"
            maxWidth="max-w-md"
            title="Activity &amp; Entertainment Info"
            subtitle="Panduan pencatatan aktivitas &amp; hospitality klien."
            icon="fa-handshake"
            iconClass="bg-orange-500 text-white"
        >
            <div class="space-y-2.5 text-xs text-slate-600 leading-relaxed">
                <p class="font-bold text-orange-600 flex items-center gap-1.5"><i class="fas fa-handshake"></i> Activity &amp; Hospitality Logging</p>
                <p>Use this form to document client meetings, lunches, and hospitality. Cost entries here are for tracking purposes and do not generate an automated fund request to Finance.</p>
            </div>
            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Got it</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endpush

    {{-- Script Helpers --}}
    @push('scripts')
    <script>
        const clientInteractions = @json($client->interactions);

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            if (modal.classList.contains('hidden')) {
                if (typeof uiModalOpen === 'function') uiModalOpen(modalId);
            } else {
                if (typeof uiModalClose === 'function') uiModalClose(modalId);
            }
        }

        function showMonthlyDetail(monthName, monthNum, year) {
            const filtered = clientInteractions.filter(item => {
                const date = new Date(item.interaction_date);
                return date.getFullYear() === year && (date.getMonth() + 1) === monthNum;
            });
            
            filtered.sort((a, b) => new Date(a.interaction_date) - new Date(b.interaction_date));
            
            const monthlyTitleEl = document.getElementById('monthlyDetailModal-title');
            if (monthlyTitleEl) monthlyTitleEl.innerText = `${monthName} ${year}`;
            
            const container = document.getElementById('monthly_detail_body');
            container.innerHTML = '';
            
            if (filtered.length === 0) {
                container.innerHTML = '<div class="text-center py-6 text-gray-400 italic">No transactions recorded for this month.</div>';
            } else {
                filtered.forEach(item => {
                    const card = document.createElement('div');
                    card.className = "bg-white rounded-xl border border-gray-150 p-3 shadow-sm flex flex-col gap-1.5 text-[11px]";
                    
                    const dateObj = new Date(item.interaction_date);
                    const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                    
                    let typeBadge = '';
                    let details = `<strong>${item.product_name}</strong>`;
                    let nominal = 0;
                    
                    if (item.transaction_type === 'IN') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[8px] font-bold bg-blue-100 text-blue-800">Sales (IN)</span>';
                        nominal = item.sales_amount > 0 ? item.sales_amount : item.amount;
                        
                        let rate = parseFloat(item.commission_rate ?? 0) || 0;
                        const note = item.notes || '';
                        if (rate > 0) {
                            const valueNet = nominal * (rate / 100);
                            details += `<div class="text-[9px] text-gray-500 italic mt-0.5">${note}</div>`;
                            details += `<div class="text-[9px] text-blue-700 font-bold mt-0.5">Rate: ${rate}% (Net: Rp ${valueNet.toLocaleString('id-ID')})</div>`;
                        } else if (note) {
                            details += `<div class="text-[9px] text-gray-500 italic mt-0.5">${note}</div>`;
                        }
                    } else if (item.transaction_type === 'OUT') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[8px] font-bold bg-red-100 text-red-800">Usage (OUT)</span>';
                        nominal = item.amount;
                        if (item.notes) {
                            details += `<div class="text-[9px] text-gray-500 italic mt-0.5">${item.notes}</div>`;
                        }
                    } else if (item.transaction_type === 'ENTERTAIN') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[8px] font-bold bg-orange-100 text-orange-800">Activity</span>';
                        nominal = item.amount;
                        if (item.notes) {
                            details += `<div class="text-[9px] text-gray-500 italic mt-0.5">${item.notes}</div>`;
                        }
                    }
                    
                    card.innerHTML = `
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-semibold font-mono">${formattedDate}</span>
                            ${typeBadge}
                        </div>
                        <div>${details}</div>
                        <div class="flex justify-between items-center border-t border-gray-50 pt-1.5">
                            <span class="text-gray-400 text-[9px] uppercase tracking-wider font-bold">Amount</span>
                            <span class="font-mono font-extrabold text-gray-800">Rp ${parseFloat(nominal).toLocaleString('id-ID')}</span>
                        </div>
                    `;
                    container.appendChild(card);
                });
            }
            
            toggleModal('monthlyDetailModal');
        }

        function toggleDirectUsageMobile(checkbox) {
            const container = document.getElementById('bank-info-container-mobile');
            const inputs = container.querySelectorAll('input');
            const submitBtn = document.getElementById('submit-btn-support-mobile');
            
            if (checkbox.checked) {
                container.classList.add('hidden');
                inputs.forEach(input => {
                    input.removeAttribute('required');
                });
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Record Usage Directly';
                submitBtn.className = "w-full h-[34px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 rounded-lg shadow-sm transition transform active:scale-95 text-xs flex items-center justify-center gap-2";
            } else {
                container.classList.remove('hidden');
                inputs.forEach(input => {
                    input.setAttribute('required', 'required');
                });
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Fund Request';
                submitBtn.className = "w-full h-[34px] bg-red-600 hover:bg-red-700 text-white font-bold px-4 rounded-lg shadow-sm transition transform active:scale-95 text-xs flex items-center justify-center gap-2";
            }
        }

        function openViewTransactionModal(data) {
            document.getElementById('view_tanggal').innerText = data.tanggal;
            document.getElementById('view_produk').innerText = data.produk;
            document.getElementById('view_catatan').innerText = data.catatan || '-';
            
            const jenisBadge = document.getElementById('view_jenis');
            const wrapperCommission = document.getElementById('view_wrapper_commission');
            const labelNominal = document.getElementById('view_label_nominal');
            const labelProduk = document.getElementById('view_label_produk');

            wrapperCommission.classList.add('hidden');
            
            if (data.jenis === 'IN') {
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-blue-100 text-blue-800 shadow-sm border border-blue-200";
                jenisBadge.innerText = "Sales (IN)";
                labelNominal.innerText = "Gross Sales Amount";
                labelProduk.innerText = "Product Name";
                
                wrapperCommission.classList.remove('hidden');
                document.getElementById('view_rate').innerText = data.rate + '% (Net: Rp ' + data.valueNet + ')';
                document.getElementById('view_nominal').innerText = 'Rp ' + data.nominal;
            } else if (data.jenis === 'OUT') {
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-red-100 text-red-800 shadow-sm border border-red-200";
                jenisBadge.innerText = "Usage (OUT)";
                labelNominal.innerText = "Support Amount";
                labelProduk.innerText = "Support Purpose";
                
                document.getElementById('view_nominal').innerText = 'Rp ' + data.nominal;
            } else if (data.jenis === 'ENTERTAIN') {
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-orange-100 text-orange-800 shadow-sm border border-orange-200";
                jenisBadge.innerText = "Activity";
                labelNominal.innerText = "Activity Expense";
                labelProduk.innerText = "Activity Description";

                document.getElementById('view_nominal').innerText = 'Rp ' + data.nominal;
            }
            
            uiModalOpen('viewTransactionModal');
        }

        function openEditTransactionModal(data) {
            let baseUrl = "{{ (request()->is('admin/*') ? url('admin/crm/interaction') : url('crm/interaction')) }}"; 
            let form = document.getElementById('formEditTransaction');
            form.action = baseUrl + "/" + data.id + "/update"; 

            document.getElementById('edit_tanggal').value = data.tanggal; 
            document.getElementById('edit_catatan').value = data.catatan;
            
            let nominalVal = parseInt(data.nominal).toLocaleString('id-ID');
            document.getElementById('edit_nominal').value = nominalVal;

            let titleEl = document.getElementById('editTransactionModal-title');
            let typeBanner = document.getElementById('editTransTypeBanner');
            let wrapperProduk = document.getElementById('wrapper_produk'); 
            let labelProduk = document.getElementById('label_produk');
            let inputProduk = document.getElementById('edit_produk');
            let inputNominal = document.getElementById('edit_nominal');

            wrapperProduk.classList.remove('hidden');
            inputProduk.setAttribute('required', 'required');

            if (data.jenis === 'IN') {
                if (titleEl) titleEl.innerText = 'Edit Sales Entry';
                if (typeBanner) {
                    typeBanner.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200';
                    typeBanner.innerText = 'Sales (IN)';
                }
                labelProduk.innerText = "Product Name";
                inputProduk.name = "product_name";
                inputProduk.value = data.produk; 
                inputNominal.name = "sales_amount"; 
            } else if (data.jenis === 'OUT') {
                if (titleEl) titleEl.innerText = 'Edit Usage Entry';
                if (typeBanner) {
                    typeBanner.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200';
                    typeBanner.innerText = 'Usage (OUT)';
                }
                labelProduk.innerText = "Support Purpose";
                inputProduk.name = "purpose";
                inputProduk.value = data.produk.replace('USAGE : ', '');
                inputNominal.name = "amount"; 
            } else if (data.jenis === 'ENTERTAIN') {
                if (titleEl) titleEl.innerText = 'Edit Activity Entry';
                if (typeBanner) {
                    typeBanner.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-200';
                    typeBanner.innerText = 'Activity (ENTERTAIN)';
                }
                wrapperProduk.classList.add('hidden');
                inputProduk.removeAttribute('required');
                inputNominal.name = "amount"; 
            }

            uiModalOpen('editTransactionModal');
        }

        window.onclick = function(event) {
            const sheetClientDetail = document.getElementById('client-detail-sheet');
            if (sheetClientDetail && event.target == sheetClientDetail) closeClientDetailSheet();
        }

        function switchTab(tabName) {
            const sections = { 'sales': 'section-sales', 'support': 'section-support', 'activity': 'section-activity', 'history': 'section-history', 'recap': 'section-recap' };
            const buttons  = { 'sales': 'btn-sales', 'support': 'btn-support', 'activity': 'btn-activity', 'history': 'btn-history', 'recap': 'btn-recap' };
            const activeClass = { 'sales': 'active-sales', 'support': 'active-support', 'activity': 'active-activity', 'history': 'active-history', 'recap': 'active-recap' };

            for (const k in buttons) {
                const s = document.getElementById(sections[k]);
                const b = document.getElementById(buttons[k]);
                if (s) s.classList.add('hidden');
                if (b) {
                    b.classList.remove('active-sales','active-support','active-activity','active-history','active-recap');
                }
            }

            const activeSection = document.getElementById(sections[tabName]);
            const activeBtn    = document.getElementById(buttons[tabName]);
            if (activeSection) activeSection.classList.remove('hidden');
            if (activeBtn)    activeBtn.classList.add(activeClass[tabName]);

            localStorage.setItem('activeTab', tabName);
        }

        function openClientDetailSheet() {
            const sheet = document.getElementById('client-detail-sheet');
            const card = document.getElementById('client-detail-sheet-card');
            sheet.classList.remove('opacity-0', 'pointer-events-none');
            sheet.classList.add('opacity-100', 'pointer-events-auto');
            card.classList.remove('translate-y-full');
            card.classList.add('translate-y-0');
            document.body.style.overflow = 'hidden';
        }

        function closeClientDetailSheet() {
            const sheet = document.getElementById('client-detail-sheet');
            const card = document.getElementById('client-detail-sheet-card');
            sheet.classList.remove('opacity-100', 'pointer-events-auto');
            sheet.classList.add('opacity-0', 'pointer-events-none');
            card.classList.remove('translate-y-0');
            card.classList.add('translate-y-full');
            document.body.style.overflow = 'auto';
        }

        function openEditClientSheet() {
            if (typeof uiModalOpen === 'function') uiModalOpen('editClientModal');
        }

        function closeEditClientSheet() {
            if (typeof uiModalClose === 'function') uiModalClose('editClientModal');
        }

        function formatRupiah(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            if (value) value = parseInt(value, 10).toLocaleString('id-ID');
            input.value = value;
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('year') || urlParams.get('tab') === 'recap') switchTab('recap');
        else if (urlParams.has('history_year') || urlParams.get('tab') === 'history') switchTab('history');
        else if (urlParams.has('activity_year') || urlParams.get('tab') === 'activity') switchTab('activity');
        else document.addEventListener("DOMContentLoaded", () => switchTab(localStorage.getItem('activeTab') || 'sales'));

        function fetchSalesDataMobile() {
            const dateInput = document.getElementById('tanggal_interaksi_in_mobile').value;
            const salesCustomer = document.getElementById('client_id_in_mobile').value;
            
            if (!salesCustomer) {
                alert('Please select a hospital / client first.');
                return;
            }
            if (!dateInput) {
                alert('Please select a transaction date first.');
                return;
            }
            
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            btn.disabled = true;

            fetch(`/crm/client/{{ $client->id }}/fetch-sales?date=${dateInput}&sales_customer=${encodeURIComponent(salesCustomer)}`)
                .then(res => res.json())
                .then(resData => {
                    if (resData.success && resData.data.length > 0) {
                        const emptyMsg = document.getElementById('empty_queue_msg_mobile');
                        if (emptyMsg) emptyMsg.remove();
                        
                        let countAdded = 0;
                        resData.data.forEach((item) => {
                            addProductRow('mobile', item.product_name, item.sales_amount, (item.date || dateInput), item.client_id, item.customer_name);
                            countAdded++;
                        });

                        let badge = document.getElementById('queue_count_mobile');
                        badge.innerText = parseInt(badge.innerText) + countAdded;
                        if (resData.truncated) {
                            alert(`Showing 500 of ${resData.total} rows — please narrow the month/client if records are missing.`);
                        }
                    } else {
                        alert(resData.message || 'Failed to fetch sales data.');
                    }
                })
                .catch(err => {
                    alert('A network or server error occurred.');
                    console.error(err);
                })
                .finally(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                });
        }

        function addProductRow(view, produk = '', nominal = '', date = '', clientId = '', clientName = '') {
            const containerId = view === 'mobile' ? 'product_rows_container_mobile' : 'product_rows_container_desktop';
            const container = document.getElementById(containerId);
            const row = document.createElement('div');
            
            let valNominal = nominal ? parseInt(nominal, 10).toLocaleString('id-ID') : '';

            if (view === 'mobile') {
                row.className = 'product-row bg-blue-50/50 p-2.5 rounded-lg border border-blue-100 relative shadow-sm text-xs flex flex-col gap-2';
                row.innerHTML = `
                    <input type="hidden" name="interaction_date[]" value="${date}">
                    <input type="hidden" name="client_id[]" value="${clientId}">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 mb-0.5">Hospital / Client</label>
                        <input type="text" class="w-full border border-gray-200 bg-gray-50 rounded-md px-2.5 py-1.5 text-xs text-gray-500 font-semibold" value="${clientName}" readonly>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 mb-0.5">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="product_name[]" class="w-full border border-gray-200 bg-gray-50 rounded-md px-2.5 py-1.5 text-xs text-gray-700" value="${produk}" readonly>
                    </div>
                    <div class="flex justify-between items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-gray-500 mb-0.5">Sales Amount (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative rounded-md">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2"><span class="text-gray-500 text-[10px] font-bold">Rp</span></div>
                                <input type="text" name="sales_amount[]" class="w-full border border-gray-200 bg-gray-50 rounded-md pl-7 px-2.5 py-1.5 font-mono text-xs font-bold text-gray-700" value="${valNominal}" readonly>
                            </div>
                        </div>
                        <div>
                            <button type="button" onclick="removeProductRow(this)" class="text-red-500 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 border border-red-200 rounded-md transition" title="Remove"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                `;
            }
            container.appendChild(row);
        }

        function removeProductRow(btn) {
            btn.closest('.product-row').remove();
            let badge = document.getElementById('queue_count_mobile');
            let current = parseInt(badge.innerText);
            if (current > 0) badge.innerText = current - 1;
        }

        function updateFileNameSupportMobile(input) {
            const label = document.getElementById('file-name-support-mobile');
            if (input.files && input.files[0]) {
                label.innerText = input.files[0].name;
            } else {
                label.innerText = 'Select file...';
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 2px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
    @endpush

</x-layout-users>