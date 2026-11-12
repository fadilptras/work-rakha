<x-layout-users :title="'Client Ledger - ' . $client->client_name">
@php $routePrefix = request()->is('admin/*') ? 'admin.crm.' : 'crm.'; @endphp

    @push('styles')
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }

        /* == Mesh Background == */
        .mesh-bg { 
            background-color: #ede9fe;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0;
            pointer-events: none;
        }

        /* Float animation */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float-delayed { animation: float 10s ease-in-out infinite; animation-delay: 2s; }

        /* Zebra striping baris tabel CRM */
        .crm-row-odd { background-color: #eff6ff; }
        .crm-row-even { background-color: #ffffff; }
        .crm-row-odd:hover,
        .crm-row-even:hover { background-color: #dbeafe; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800">
        <div class="mesh-bg"></div>

        {{-- MAIN WRAPPER --}}
        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-3 sm:py-4 space-y-3.5 flex-1 flex flex-col items-stretch justify-start">
            
            {{-- Back Button --}}
            <div class="w-full flex justify-start">
                <x-ui.back-button href="{{ route($routePrefix . 'index') }}" label="Back to Client Directory" />
            </div>

            {{-- SECTION 1: HERO PROFILE --}}
            <div class="rounded-2xl shadow-lg border border-blue-500/20 overflow-hidden relative text-white" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="px-5 py-3.5 md:px-6 md:py-4 relative z-10">
                    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-3.5">
                        
                        {{-- Left: Client Identity --}}
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="bg-white/15 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider border border-white/25 shadow-sm inline-flex items-center">
                                    <i class="fas fa-hospital mr-1.5 opacity-90"></i> {{ $client->customer?->customer_name ?? '-' }}
                                </span>
                                <div class="hidden sm:block w-1 h-1 rounded-full bg-white/40"></div>
                                <div class="flex items-center text-[11px] text-white/90 font-medium">
                                    <i class="fas fa-map-marker-alt mr-1.5 text-white/75"></i> {{ $client->customer?->area ?? 'Area Not Set' }}
                                </div>
                                <div class="hidden sm:block w-1 h-1 rounded-full bg-white/40"></div>
                                <button type="button" onclick="uiModalOpen('handoverPicModal')" class="flex items-center text-[11px] text-white/95 font-medium hover:text-white transition" title="Klik untuk melihat riwayat atau ganti PIC">
                                    <i class="fas fa-user-tie mr-1.5 text-white/80"></i> PIC: <strong class="ml-1 underline decoration-white/60 underline-offset-2">{{ $client->ps ?? '-' }}</strong>
                                    @if(isset($picHistories) && $picHistories->count() > 1)
                                        <span class="ml-1.5 bg-white/20 text-[9px] font-bold px-1.5 py-0.5 rounded-full border border-white/25"><i class="fas fa-history mr-0.5"></i> {{ $picHistories->count() }} PIC</span>
                                    @endif
                                </button>
                            </div>
                            <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-white drop-shadow-sm leading-tight">
                                {{ $client->client_name }}
                            </h2>
                        </div>

                        {{-- Right: Key Stats & Action --}}
                        <div class="flex flex-wrap sm:flex-nowrap items-center justify-start xl:justify-end gap-2.5 w-full xl:w-auto">
                            
                            <div class="flex items-center gap-4 bg-white/15 backdrop-blur-md rounded-xl px-4 py-2 border border-white/25 shadow-sm flex-1 sm:flex-initial justify-between sm:justify-start">
                                <div class="flex flex-col">
                                    <span class="text-white/90 text-[9px] font-bold uppercase tracking-wider mb-0.5">Total Usage</span>
                                    <div class="flex items-baseline">
                                        <span class="text-[10px] text-white/85 mr-1 font-bold">Rp</span>
                                        <span class="text-base md:text-lg font-mono font-extrabold text-white leading-none drop-shadow-sm">{{ number_format($client->interactions->where('transaction_type', 'OUT')->sum('amount'), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="w-px h-7 bg-white/25 mx-1"></div>
                                <div class="flex flex-col">
                                    <span class="text-white/90 text-[9px] font-bold uppercase tracking-wider mb-0.5">Total Balance</span>
                                    <div class="flex items-baseline">
                                        <span class="text-[10px] text-white/85 mr-1 font-bold">Rp</span>
                                        <span class="text-base md:text-lg font-mono font-extrabold text-white leading-none drop-shadow-sm">{{ number_format($currentBalance, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($canEdit) 
                                <button type="button" onclick="uiModalOpen('handoverPicModal')" class="flex-shrink-0 flex items-center justify-center bg-white/15 hover:bg-white/25 text-white border border-white/35 text-xs font-bold px-3.5 py-2 rounded-xl transition-all shadow-sm w-full sm:w-auto" title="Serah Terima PIC & Riwayat">
                                    <i class="fas fa-exchange-alt mr-1.5"></i> Ganti PIC
                                </button>
                                <button type="button" onclick="uiModalOpen('editClientModal')" class="flex-shrink-0 flex items-center justify-center bg-amber-400 hover:bg-amber-300 text-slate-900 text-xs font-bold px-3.5 py-2 rounded-xl transition-all shadow-md w-full sm:w-auto">
                                    <i class="fas fa-edit mr-1.5"></i> Edit Client
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: CLIENT INFO CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 items-stretch">
            {{-- CLIENT PROFILE --}}
            <details class="sync-details bg-blue-600 p-4 rounded-2xl shadow-sm text-white relative overflow-hidden group hover:shadow-md transition-all">
                <summary onclick="toggleAllDetails(event, this.parentElement)" class="cursor-pointer list-none [&::-webkit-details-marker]:hidden flex justify-between items-center outline-none text-white/95 text-[11px] font-bold uppercase tracking-widest relative z-10 border-b border-white/20 pb-2 mb-0 group-open:mb-3">
                    <div class="flex items-center"><i class="fas fa-user mr-2 text-white"></i> Client Profile</div>
                    <i class="fas fa-chevron-down transition-transform duration-300 group-open:rotate-180"></i>
                </summary>
                <div class="space-y-2.5 relative z-10 text-sm">
                    @if($client->position)
                    <div class="flex items-start relative pl-7">
                        <div class="absolute left-0 top-0.5 text-white/80"><i class="fas fa-id-badge text-xs"></i></div>
                        <div><p class="text-[10px] text-white/80 font-bold uppercase mb-0.5">Position / Role</p><p class="font-bold text-sm leading-snug">{{ $client->position }}</p></div>
                    </div>
                    @endif
                    <div class="flex items-start relative pl-7">
                        <div class="absolute left-0 top-0.5 text-white/80"><i class="fas fa-envelope text-xs"></i></div>
                        <div><p class="text-[10px] text-white/80 font-bold uppercase mb-0.5">Email</p><p class="font-medium text-sm break-all leading-snug">{{ $client->email ?? '-' }}</p></div>
                    </div>
                    <div class="flex items-start relative pl-7">
                        <div class="absolute left-0 top-0.5 text-emerald-200"><i class="fab fa-whatsapp text-sm"></i></div>
                        <div><p class="text-[10px] text-white/80 font-bold uppercase mb-0.5">Phone / WhatsApp</p><p class="font-medium text-sm leading-snug">{{ $client->phone ?? '-' }}</p></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex items-start relative pl-7">
                            <div class="absolute left-0 top-0.5 text-pink-200"><i class="fas fa-birthday-cake text-xs"></i></div>
                            <div><p class="text-[10px] text-white/80 font-bold uppercase mb-0.5">Date of Birth</p><div class="font-medium text-sm leading-snug">{{ $client->birthdate ? \Carbon\Carbon::parse($client->birthdate)->format('d M Y') : '-' }}</div></div>
                        </div>
                        <div class="flex items-start relative pl-6">
                            <div class="absolute left-0 top-0.5 text-yellow-200"><i class="fas fa-star text-xs"></i></div>
                            <div><p class="text-[10px] text-white/80 font-bold uppercase mb-0.5">Hobbies</p><p class="font-medium text-sm leading-snug">{{ $client->hobby ?? '-' }}</p></div>
                        </div>
                    </div>
                    <div class="flex items-start relative pl-7">
                        <div class="absolute left-0 top-0.5 text-white/75"><i class="fas fa-home text-xs"></i></div>
                        <div><p class="text-[10px] text-white/80 font-bold uppercase mb-0.5">Home Address</p><p class="text-xs leading-relaxed text-white/95">{{ $client->address ?? '-' }}</p></div>
                    </div>
                    <div class="flex items-start relative pl-7">
                        <div class="absolute left-0 top-0.5 text-emerald-200"><i class="fas fa-percent text-xs"></i></div>
                        <div><p class="text-[10px] text-white/80 font-bold uppercase mb-0.5">Commission Rate</p><p class="font-bold text-sm leading-snug">{{ $client->commission_rate ? (float)$client->commission_rate . '%' : '-' }}</p></div>
                    </div>
                </div>
            </details>

            {{-- COMPANY PROFILE --}}
            <details class="sync-details bg-orange-500 p-4 rounded-2xl shadow-sm text-white relative overflow-hidden group hover:shadow-md transition-all">
                <summary onclick="toggleAllDetails(event, this.parentElement)" class="cursor-pointer list-none [&::-webkit-details-marker]:hidden flex justify-between items-center outline-none text-white/95 text-[11px] font-bold uppercase tracking-widest relative z-10 border-b border-white/20 pb-2 mb-0 group-open:mb-3">
                    <div class="flex items-center"><i class="fas fa-building mr-2 text-white"></i> Institution Profile</div>
                    <i class="fas fa-chevron-down transition-transform duration-300 group-open:rotate-180"></i>
                </summary>
                <div class="space-y-2.5 relative z-10">
                    <div class="p-3 bg-white/15 backdrop-blur-md rounded-xl border border-white/20 relative overflow-hidden">
                        <i class="fas fa-hospital absolute right-2 bottom-2 text-4xl text-white/20 -rotate-12 pointer-events-none"></i>
                        <p class="text-[10px] text-white/85 font-bold uppercase mb-0.5">Institution / Hospital Name</p>
                        <p class="font-bold text-base leading-tight">{{ $client->customer?->customer_name ?? '-' }}</p>
                    </div>
                    <div class="flex items-start relative pl-7">
                        <div class="absolute left-0 top-0.5 text-white/85"><i class="fas fa-calendar-alt text-xs"></i></div>
                        <div>
                            <p class="text-[10px] text-white/80 font-bold uppercase mb-0.5">Anniversary Date</p>
                            <div class="flex items-center font-medium text-sm">
                                @if($client->customer?->anniversary_date)
                                    <span>{{ \Carbon\Carbon::parse($client->customer->anniversary_date)->format('d F Y') }}</span>
                                    <span class="ml-2 text-[10px] bg-white text-orange-600 px-2 py-0.5 rounded-full font-bold shadow-sm">{{ \Carbon\Carbon::parse($client->customer->anniversary_date)->age }} Yrs</span>
                                @else <span class="italic opacity-80">Not set</span> @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start relative pl-7">
                        <div class="absolute left-0 top-0.5 text-white/85"><i class="fas fa-map-marked-alt text-xs"></i></div>
                        <div><p class="text-[10px] text-white/80 font-bold uppercase mb-0.5">Office Address</p><p class="text-xs leading-relaxed text-white/95">{{ $client->customer?->displayAddress() ?: '-' }}</p></div>
                    </div>

                    {{-- Pharmacist --}}
                    <div class="pt-2.5 border-t border-white/20">
                        <p class="text-[10px] text-white/90 font-bold uppercase mb-1.5"><i class="fas fa-user-md mr-1"></i> Pharmacist-in-Charge</p>
                        <div class="grid grid-cols-2 gap-y-1.5 text-xs">
                            <div><p class="text-[9px] text-white/75 uppercase">Name</p><p class="font-bold">{{ $client->customer?->pharmacist_name ?? '-' }}</p></div>
                            <div><p class="text-[9px] text-white/75 uppercase">SIPA</p><p class="font-bold">{{ $client->customer?->pharmacist_license_no ?? '-' }}</p></div>
                            <div class="col-span-2"><p class="text-[9px] text-white/75 uppercase">Phone</p><p class="font-bold">{{ $client->customer?->pharmacist_phone ?? '-' }}</p></div>
                        </div>
                    </div>
                </div>
            </details>

            {{-- BANKING INFORMATION --}}
            <details class="sync-details bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-4 rounded-2xl shadow-sm border border-slate-700 text-white relative overflow-hidden group hover:shadow-md transition-all">
                <summary onclick="toggleAllDetails(event, this.parentElement)" class="cursor-pointer list-none [&::-webkit-details-marker]:hidden flex justify-between items-center outline-none relative z-10 border-b border-slate-700 pb-2 mb-0 group-open:mb-3">
                    <div class="text-slate-200 text-[11px] font-bold uppercase tracking-widest flex items-center">
                        <i class="fas fa-wallet mr-2 text-emerald-400"></i> Banking Information
                    </div>
                    <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300 group-open:rotate-180"></i>
                </summary>
                <div class="relative z-10 flex flex-col gap-3">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1 font-bold">Bank & Account</p>
                        <div class="flex flex-col">
                            <span class="font-bold text-lg tracking-wide text-white leading-tight mb-0.5">{{ $client->bank_name ?? 'BANK -' }}</span>
                            <p class="text-xs text-slate-300 mb-2">{{ $client->bank_account_name ? 'A/C Holder: '.$client->bank_account_name : '' }}</p>
                            <div class="flex items-center gap-2 font-mono text-emerald-300 tracking-widest text-sm bg-white/10 px-3 py-1.5 rounded-lg w-fit border border-white/10">
                                <i class="fas fa-credit-card text-xs opacity-80"></i> <span class="font-bold">{{ $client->bank_account_number ?? '----' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-slate-700/80 pt-2.5">
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5 font-bold">Opening Balance</p>
                        <p class="text-xl md:text-2xl font-mono font-bold text-emerald-400 tracking-tight flex items-baseline">
                            <span class="text-xs text-slate-400 mr-1.5 font-normal">IDR</span> {{ number_format($client->opening_balance ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </details>
        </div>

        <script>
            function toggleAllDetails(event, element) {
                event.preventDefault();
                const details = document.querySelectorAll('.sync-details');
                const isOpening = !element.open;
                details.forEach(d => {
                    d.open = isOpening;
                });
            }
        </script>

        {{-- SECTION 3: NAVIGATION TABS --}}
        <div class="flex justify-center md:justify-start">
            <div class="bg-white rounded-xl grid grid-cols-2 md:inline-flex gap-1 border border-slate-200 shadow-sm p-1">
                <button id="btn-sales" onclick="switchTab('sales')" class="nav-btn px-4 py-2 rounded-lg text-xs md:text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <i class="fas fa-plus-circle text-xs"></i> Sales (IN)
                </button>
                <button id="btn-support" onclick="switchTab('support')" class="nav-btn px-4 py-2 rounded-lg text-xs md:text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <i class="fas fa-hand-holding-usd text-xs"></i> Usage (OUT)
                </button>
                <button id="btn-activity" onclick="switchTab('activity')" class="nav-btn px-4 py-2 rounded-lg text-xs md:text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <i class="fas fa-glass-cheers text-xs"></i> Activities
                </button>
                <button id="btn-history" onclick="switchTab('history')" class="nav-btn px-4 py-2 rounded-lg text-xs md:text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <i class="fas fa-history text-xs"></i> History
                </button>
                <button id="btn-recap" onclick="switchTab('recap')" class="nav-btn px-4 py-2 rounded-lg text-xs md:text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <i class="fas fa-calendar-check text-xs"></i> Yearly Recap
                </button>
            </div>
        </div>
        
        {{-- TAB 1: INPUT SALES (IN) --}}
        <div id="section-sales" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden relative">
            <div class="bg-blue-600 px-4 py-2 border-b border-blue-500 flex justify-between items-center">
                <h3 class="font-bold text-white text-sm md:text-[15px] flex items-center"><span class="w-6 h-6 bg-white text-blue-600 rounded-md flex items-center justify-center mr-2 text-xs shadow-sm"><i class="fas fa-plus"></i></span> Input Sales Inflow</h3>
            </div>
            <div class="px-4 py-3.5">
                @if($hasFullAccess)
                <form action="{{ route($routePrefix . 'interaction.store') }}" method="POST" onsubmit="const b=this.querySelector('[type=submit]');if(b){b.disabled=true;b.classList.add('opacity-60');}">
                    @csrf
                    <div class="mb-2.5 grid grid-cols-1 lg:grid-cols-12 gap-2.5">
                        <div class="lg:col-span-4">
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">Hospital / Client (Command Center Data) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="client_id_in" class="w-full h-[34px] border border-blue-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 pl-2.5 pr-7 py-1 text-xs appearance-none bg-white" required>
                                    @if($salesCustomers->isEmpty())
                                        <option value="" disabled selected>No data available (Check Sales PIC)</option>
                                    @else
                                        <option value="" disabled {{ $client->customer?->sales_customer_name ? '' : 'selected' }}>Select Hospital / Institution...</option>
                                        @foreach($salesCustomers as $c)
                                            <option value="{{ $c }}" @selected($client->customer?->sales_customer_name == $c)>{{ $c }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-600">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1 leading-tight">
                                Active PIC: <strong class="text-blue-700 font-bold">{{ ($lockedPs ?? null) ?: '-' }}</strong>
                                @if(!empty($picNames) && count($picNames) > 1)
                                    &bull; Riwayat PIC: <span class="text-gray-700 font-semibold">{{ implode(', ', $picNames) }}</span>
                                @endif
                                @if($client->customer?->sales_customer_name) &bull; Linked to: <strong class="text-gray-700">{{ $client->customer?->sales_customer_name }}</strong>@endif
                            </p>
                        </div>
                        <div class="lg:col-span-5">
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">Select Month <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="month" id="tanggal_interaksi_in" class="w-full h-[34px] border border-blue-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2.5 py-1 text-xs" required>
                                <button type="button" onclick="fetchSalesData()" class="h-[34px] bg-indigo-100 text-indigo-700 hover:bg-indigo-200 font-bold px-3 rounded-lg text-xs shadow-sm transition whitespace-nowrap flex items-center" title="Fetch data from Command Center">
                                    <i class="fas fa-cloud-download-alt mr-1.5"></i> Sync &amp; Fetch
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">You can fetch transactions across multiple dates and hospitals.</p>
                        </div>
                        <div class="lg:col-span-3">
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Notes (Optional)</label>
                            <input type="text" name="notes" class="w-full h-[34px] border border-blue-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2.5 py-1 text-xs" placeholder="Optional notes...">
                        </div>
                    </div>

                    <div class="mb-1.5 flex justify-between items-center border-b border-gray-100 pb-1.5">
                        <h4 class="font-bold text-xs text-gray-700 flex items-center"><i class="fas fa-box-open mr-1.5 text-blue-500"></i> Sales Inflow Queue <span id="queue_count_desktop" class="ml-2 bg-blue-100 text-blue-800 text-[11px] font-semibold px-2 py-0.5 rounded-full">0</span></h4>
                    </div>

                    <div id="product_rows_container_desktop" class="space-y-1.5 mb-2.5 max-h-[32vh] overflow-y-auto custom-scrollbar pr-1">
                        <div id="empty_queue_msg_desktop" class="text-center py-2.5 text-gray-400 italic text-xs">
                             No data fetched yet. Select a month and click "Sync &amp; Fetch".
                        </div>
                    </div>

                    <datalist id="produk-list">
                        @foreach($productNames as $prod)
                            <option value="{{ $prod }}"></option>
                        @endforeach
                    </datalist>

                    <div class="flex items-end">
                        <button type="submit" class="w-full h-[34px] bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 rounded-lg shadow-sm hover:shadow transition transform active:scale-95 text-xs md:text-sm flex justify-center items-center gap-2"><i class="fas fa-save"></i> Save Sales Data</button>
                    </div>
                </form>
                @else
                <div class="flex flex-col items-center justify-center py-5 px-4 text-center bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-2 text-base shadow-sm">
                        <i class="fas fa-sync-alt animate-spin-slow"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 text-sm mb-0.5">Automated Sales Synchronization</h4>
                    <p class="text-xs text-gray-500 max-w-lg leading-relaxed">
                        As the assigned PIC, you can monitor all sales transactions on this page. New records are synced automatically with the sales data from Command Center in <strong>real time</strong>. For manual adjustments or data reconciliations, please coordinate with your <strong>Admin / Division Head</strong>.
                    </p>
                </div>
                @endif
            </div>
        </div>

        {{-- TAB 2: INPUT USAGE (OUT) --}}
        <div id="section-support" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden relative hidden">
            <style>
                @media (min-width: 768px) {
                    .custom-w-tanggal { width: 15% !important; }
                    .custom-w-nominal { width: 15% !important; }
                    .custom-w-keperluan { width: 50% !important; }
                    .custom-w-lampiran { width: 20% !important; }
                }
            </style>
            <div class="bg-red-600 px-4 py-2 border-b border-red-500 flex justify-between items-center">
                <h3 class="font-bold text-white text-sm md:text-[15px] flex items-center"><span class="w-6 h-6 bg-white text-red-600 rounded-md flex items-center justify-center mr-2 text-xs shadow-sm"><i class="fas fa-hand-holding-usd"></i></span> Record Support Fund (Usage)</h3>
                <button type="button" onclick="uiModalOpen('usage-info-modal')" class="text-white hover:text-red-100 transition text-base flex items-center justify-center w-7 h-7 rounded-full hover:bg-white/10 outline-none" title="Usage Disbursement Workflow">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
            <div class="px-4 py-3.5">
                <form action="{{ route($routePrefix . 'interaction.support') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <div class="flex flex-col md:flex-row gap-2.5 mb-2.5">
                        <div class="w-full custom-w-tanggal">
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="interaction_date" class="w-full h-[34px] border border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-2.5 py-1 text-xs" required>
                        </div>
                        <div class="w-full custom-w-nominal">
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">Disbursed Amount <span class="text-red-500">*</span></label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                                    <span class="text-gray-500 text-xs font-bold">Rp</span>
                                </div>
                                <input type="text" name="amount" onkeyup="formatRupiah(this)" class="w-full h-[34px] border border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 pl-8 px-2.5 py-1 font-mono font-bold text-sm text-red-700" placeholder="0" required>
                            </div>
                        </div>
                        <div class="w-full custom-w-keperluan">
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">Support Purpose <span class="text-red-500">*</span></label>
                            <input type="text" name="purpose" class="w-full h-[34px] border border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-2.5 py-1 text-xs" placeholder="e.g. Transport, Clinical meeting" required>
                        </div>
                        <div class="w-full custom-w-lampiran">
                            <label class="block text-[11px] font-bold text-gray-700 mb-1 flex items-center gap-1">
                                <i class="fas fa-paperclip text-red-500 text-[11px]"></i>
                                <span>Attachment</span>
                                <span class="text-gray-400 text-[10px] font-normal">(Optional)</span>
                            </label>
                            <div class="relative flex items-center justify-between border border-red-300 rounded-lg shadow-sm bg-white px-2.5 h-[34px]">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <i class="fas fa-cloud-upload-alt text-red-400 text-xs flex-shrink-0"></i>
                                    <span id="file-name-support-desktop" class="text-xs font-semibold text-gray-500 truncate max-w-[80px] md:max-w-[110px]">Select file...</span>
                                </div>
                                <label class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold text-[10px] py-0.5 px-2 rounded cursor-pointer transition shadow-sm flex items-center gap-1 active:scale-95 flex-shrink-0">
                                    <i class="fas fa-folder-open"></i> Browse
                                    <input type="file" name="lampiran_tambahan" class="hidden" onchange="updateFileNameSupportDesktop(this)">
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    @if($hasFullAccess)
                    <div class="mb-2.5 flex items-center gap-2 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-1.5">
                        <input type="checkbox" name="direct_usage" id="direct_usage_desktop" value="1" onchange="toggleDirectUsageDesktop(this)" class="w-3.5 h-3.5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <label for="direct_usage_desktop" class="text-xs font-bold text-gray-700 cursor-pointer">Directly record to CRM (Bypass Fund Request to Finance)</label>
                    </div>
                    @endif

                    <div id="bank-info-container-desktop" class="grid grid-cols-1 md:grid-cols-3 gap-2.5 mb-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">Beneficiary Bank <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_bank" value="{{ $client->bank_name }}" class="w-full h-[34px] border border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-2.5 py-1 text-xs" placeholder="e.g. BCA, Mandiri" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">Account Number <span class="text-red-500">*</span></label>
                            <input type="text" name="no_rekening" value="{{ $client->bank_account_number }}" class="w-full h-[34px] border border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-2.5 py-1 text-xs font-mono font-bold" placeholder="0987654321" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">Account Holder Name <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_rek" value="{{ $client->bank_account_name }}" class="w-full h-[34px] border border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-2.5 py-1 text-xs" placeholder="Beneficiary name..." required>
                        </div>
                    </div>
                    <div class="flex">
                        <button type="submit" id="submit-btn-support-desktop" class="w-full h-[34px] bg-red-600 hover:bg-red-700 text-white font-bold px-4 rounded-lg shadow-sm transition transform active:scale-95 text-xs md:text-sm flex items-center justify-center gap-2"><i class="fas fa-paper-plane"></i> Submit Fund Request</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- TAB 3: INPUT ACTIVITIES & ENTERTAIN --}}
        <div id="section-activity" class="space-y-3 hidden">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden relative">
                <div class="bg-orange-500 px-4 py-2 border-b border-orange-400 flex justify-between items-center">
                    <h3 class="font-bold text-white text-sm md:text-[15px] flex items-center">
                        <span class="w-6 h-6 bg-white text-orange-600 rounded-md flex items-center justify-center mr-2 text-xs shadow-sm">
                            <i class="fas fa-glass-cheers"></i>
                        </span>
                        Record Activity &amp; Client Entertainment
                    </h3>
                    <button type="button" onclick="uiModalOpen('activity-info-modal')" class="text-white hover:text-orange-100 transition text-base flex items-center justify-center w-7 h-7 rounded-full hover:bg-white/10 outline-none" title="Activity Workflow Info">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>
                <div class="px-4 py-3.5">
                    <form action="{{ route($routePrefix . 'interaction.entertain') }}" method="POST">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                            <div class="md:col-span-4 space-y-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">Activity Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="interaction_date" class="w-full h-[34px] border border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 px-2.5 py-1 text-xs" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">Expense Amount (Rp) <span class="text-red-500">*</span></label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5"><span class="text-gray-500 text-xs font-bold">Rp</span></div>
                                        <input type="text" name="amount" onkeyup="formatRupiah(this)" class="w-full h-[34px] border border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pl-8 px-2.5 py-1 font-mono font-bold text-sm text-orange-700" placeholder="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="md:col-span-8 bg-orange-50/40 p-3 rounded-xl border border-orange-100 flex flex-col gap-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wide mb-1">Description &amp; Agenda <span class="text-red-500">*</span></label>
                                    <textarea name="notes" rows="2" class="w-full border border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 px-2.5 py-1.5 text-xs resize-none" placeholder="e.g. Lunch meeting discussing upcoming purchase..." required></textarea>
                                </div>
                                <button type="submit" class="w-full h-[34px] bg-orange-600 hover:bg-orange-700 text-white font-bold px-4 rounded-lg shadow-sm transition transform active:scale-95 text-xs md:text-sm flex items-center justify-center gap-2"><i class="fas fa-save"></i> Save Activity</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ACTIVITY TABLE --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                @php
                    $totalActivity = $activities->sum('amount');
                @endphp

                <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-between items-center gap-2">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-gray-700 text-xs md:text-sm uppercase tracking-wide flex items-center gap-1.5">
                            <i class="fas fa-history text-orange-500"></i> Activity &amp; Entertainment History
                        </h3>
                        <div class="bg-orange-100 border border-orange-200 text-orange-700 px-2.5 py-0.5 rounded-full text-[11px] font-bold shadow-sm">
                            Total: Rp {{ number_format($totalActivity, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- FILTER YEAR --}}
                    <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="activity"> 
                        <label class="text-[11px] font-bold text-gray-500 uppercase">Filter:</label>
                        <div class="relative">
                            <select name="activity_year" onchange="this.form.submit()" class="h-[30px] pl-2.5 pr-7 py-0.5 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring-orange-500 cursor-pointer hover:bg-gray-50 transition appearance-none">
                                <option value="">All Years</option>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ (request('activity_year') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs md:text-sm text-left">
                        <thead class="bg-orange-50/80 text-orange-800 uppercase text-[11px] font-bold tracking-wider border-b border-orange-100">
                            <tr>
                                <th class="px-3.5 py-2">Date</th>
                                <th class="px-3.5 py-2">Activity / Description</th>
                                <th class="px-3.5 py-2 text-right">Amount</th>
                                @if($hasFullAccess)
                                <th class="px-3.5 py-2 text-center">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($activities as $act)
                            <tr class="{{ $loop->odd ? 'crm-row-odd' : 'crm-row-even' }} transition">
                                <td class="px-3.5 py-1.5 whitespace-nowrap font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($act->interaction_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-3.5 py-1.5">
                                    <button type="button" onclick="openViewTransactionModal({
                                        id: '{{ $act->id }}',
                                        jenis: 'ENTERTAIN',
                                        tanggal: '{{ \Carbon\Carbon::parse($act->interaction_date)->translatedFormat('d F Y') }}',
                                        produk: '{{ addslashes($act->product_name) }}',
                                        nominal: '{{ number_format($act->amount, 0, ',', '.') }}',
                                        rate: '0',
                                        valueNet: '0',
                                        catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->notes)) }}'
                                    })" class="text-left font-bold text-gray-800 hover:text-orange-600 hover:underline focus:outline-none">
                                        {{ $act->product_name }}
                                    </button>
                                    <div class="text-[11px] text-gray-500 leading-tight">{{ $act->notes }}</div>
                                </td>
                                <td class="px-3.5 py-1.5 text-right font-mono font-bold text-orange-600">
                                    {{ number_format($act->amount, 0, ',', '.') }}
                                </td>
                                @if($hasFullAccess)
                                <td class="px-3.5 py-1.5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" 
                                            onclick="openEditTransactionModal({
                                                id: '{{ $act->id }}',
                                                jenis: 'ENTERTAIN',
                                                tanggal: '{{ $act->interaction_date }}',
                                                produk: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->product_name)) }}', 
                                                nominal: '{{ $act->amount }}',
                                                catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->notes)) }}'
                                            })"
                                            class="text-orange-400 hover:text-orange-600 transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route($routePrefix . 'interaction.destroy', $act->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Delete this activity?');" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="text-gray-300 hover:text-red-500 transition"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-4 text-center text-gray-400 italic text-xs bg-gray-50/50">No activity records found.</td></tr>
                            @endforelse
                        </tbody>
                        @if($activities->count() > 0)
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="2" class="px-3.5 py-2 text-right font-bold text-gray-600 uppercase text-[11px] tracking-wider">Total Entertainment Expenses</td>
                                <td class="px-3.5 py-2 text-right font-mono font-extrabold text-orange-700 text-sm">{{ number_format($totalActivity, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 4: TRANSACTION HISTORY --}}
        <div id="section-history" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden">
            <div class="px-4 py-2 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center bg-gray-50/50 gap-2">
                <h3 class="font-bold text-gray-800 text-sm md:text-[15px] flex items-center gap-2">
                    <i class="fas fa-history text-gray-400"></i> Transaction History
                </h3>
                {{-- FILTER YEAR --}}
                <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="history"> 
                    <label class="text-[11px] font-bold text-gray-500 uppercase">Filter:</label>
                    <div class="relative">
                        <select name="history_year" onchange="this.form.submit()" class="h-[30px] pl-2.5 pr-7 py-0.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 cursor-pointer hover:bg-gray-50 transition appearance-none">
                            <option value="">All Years</option>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ (request('history_year') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="overflow-x-auto w-full">
                <table class="w-full text-xs md:text-sm text-left min-w-[780px]">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-3.5 py-2">Date</th>
                            <th class="px-3.5 py-2">Product / Description</th>
                            <th class="px-3.5 py-2 text-right">Gross Sales</th>
                            <th class="px-3.5 py-2 text-center w-16">Commission</th>
                            <th class="px-3.5 py-2 text-right text-blue-800">Net Value</th>
                            <th class="px-3.5 py-2 text-right text-red-600">Usage (OUT)</th>
                            @if($hasFullAccess)
                            <th class="px-3.5 py-2 text-center">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($interactions as $item)
                            @if($item->transaction_type == 'ENTERTAIN') @continue @endif
                            @php
                                $isOut = $item->transaction_type == 'OUT';
                                $rate = (float)($item->commission_rate ?? 0);
                                $displayNote = trim(preg_replace('/\[Rate:[\d\.]+%?\]\s*/', '', $item->notes ?? ''));
                                $nominal = $item->sales_amount > 0 ? $item->sales_amount : $item->amount;
                                $valueNet = (!$isOut) ? ($nominal * ($rate/100)) : 0;
                            @endphp
                        <tr class="{{ $isOut ? 'bg-red-50/70 hover:bg-red-100/60' : ($loop->odd ? 'crm-row-odd' : 'crm-row-even') }} transition">
                            <td class="px-3.5 py-1.5 whitespace-nowrap font-bold text-gray-700">{{ \Carbon\Carbon::parse($item->interaction_date)->format('d/m/Y') }}</td>
                            <td class="px-3.5 py-1.5">
                                <button type="button" onclick="openViewTransactionModal({
                                    id: '{{ $item->id }}',
                                    jenis: '{{ $item->transaction_type }}',
                                    tanggal: '{{ \Carbon\Carbon::parse($item->interaction_date)->translatedFormat('d F Y') }}',
                                    produk: '{{ addslashes($item->product_name) }}',
                                    nominal: '{{ number_format(($item->transaction_type == 'IN') ? $item->sales_amount : $item->amount, 0, ',', '.') }}',
                                    rate: '{{ $rate }}',
                                    valueNet: '{{ number_format($valueNet, 0, ',', '.') }}',
                                    catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $displayNote)) }}'
                                })" class="text-left font-bold {{ $isOut ? 'text-red-800 hover:text-red-950' : 'text-blue-900 hover:text-blue-950' }} hover:underline focus:outline-none">
                                    {{ $item->product_name }}
                                </button>
                                <div class="text-[11px] text-gray-500 italic leading-tight">{{ $displayNote }}</div>
                            </td>
                            <td class="px-3.5 py-1.5 text-right font-mono text-gray-600">{{ (!$isOut) ? number_format($nominal, 0, ',', '.') : '-' }}</td>
                            <td class="px-3.5 py-1.5 text-center">@if(!$isOut && $rate > 0) <span class="bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded text-[11px] font-bold border border-gray-300">{{ (float)$rate }}%</span> @else <span class="text-gray-300">-</span> @endif</td>
                            <td class="px-3.5 py-1.5 text-right font-mono font-bold text-blue-700">{{ (!$isOut) ? number_format($valueNet, 0, ',', '.') : '-' }}</td>
                            <td class="px-3.5 py-1.5 text-right font-mono font-bold text-red-600">{{ $isOut ? number_format($item->amount, 0, ',', '.') : '-' }}</td>
                            @if($hasFullAccess)
                            <td class="px-3.5 py-1.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="openEditTransactionModal({id: '{{ $item->id }}', jenis: '{{ $item->transaction_type }}', tanggal: '{{ $item->interaction_date }}', produk: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $item->product_name)) }}', nominal: '{{ ($item->transaction_type == 'IN') ? $item->sales_amount : $item->amount }}', rate: '{{ $rate }}', catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $displayNote)) }}'})" class="text-blue-400 hover:text-blue-600 transition" title="Edit Record"><i class="fas fa-edit"></i></button>
                                    <form action="{{ route($routePrefix . 'interaction.destroy', $item->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Delete this transaction?');" class="inline">@csrf @method('DELETE')<button class="text-gray-300 hover:text-red-600 transition" title="Delete Record"><i class="fas fa-trash-alt"></i></button></form>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-xs text-gray-400">No transaction records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($interactions->hasPages())
                <div class="bg-gray-50 px-4 py-2 border-t border-gray-200 text-xs">{{ $interactions->links() }}</div>
            @endif
        </div>

        {{-- TAB 5: YEARLY RECAP --}}
        <div id="section-recap" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden">
            <div class="px-4 py-2 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-2 bg-gray-50/50">
                <h3 class="font-bold text-gray-800 text-sm md:text-[15px] flex items-center gap-2">
                    <span class="bg-white px-1.5 py-1 rounded-md shadow-sm border border-gray-100 text-xs"><i class="fas fa-chart-bar text-blue-600"></i></span>
                    Financial Recap - Year {{ $year }}
                </h3>
                <div class="flex items-center gap-2">
                    <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="flex items-center">
                        <input type="hidden" name="tab" value="recap"> 
                        <div class="relative">
                            <select name="year" onchange="this.form.submit()" class="h-[30px] pl-2.5 pr-7 py-0.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 cursor-pointer hover:bg-gray-50 transition appearance-none">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                                @endfor
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500"><i class="fas fa-chevron-down text-[10px]"></i></div>
                        </div>
                    </form>
                    <a href="{{ route($routePrefix . 'client.export', ['client' => $client->id, 'year' => $year]) }}" class="h-[30px] inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 rounded-lg shadow-sm transition hover:shadow border border-emerald-700"><i class="fas fa-file-excel mr-1.5"></i> Export Excel</a>
                </div>
            </div>
            
            <div class="overflow-x-auto w-full">
                <table class="w-full text-xs md:text-sm text-left min-w-[780px]">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                        <tr><th class="px-3.5 py-2">Month</th><th class="px-3.5 py-2 text-right">Sales (IN)</th><th class="px-3.5 py-2 text-center w-16">Commission</th><th class="px-3.5 py-2 text-right text-blue-700 bg-blue-50/50">Net Value</th><th class="px-3.5 py-2 text-right text-red-600">Usage (OUT)</th><th class="px-3.5 py-2 text-right text-gray-800 border-l border-gray-200 bg-gray-50">Balance</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="bg-yellow-50 hover:bg-yellow-100 transition border-b border-yellow-200">
                            <td class="px-3.5 py-1.5 font-bold text-gray-800 italic" colspan="5"><div class="flex items-center"><span class="w-5 h-5 rounded-full bg-yellow-200 text-yellow-700 flex items-center justify-center mr-2 text-[10px]"><i class="fas fa-forward"></i></span> {{ $startingLabel }} </div></td>
                            <td class="px-3.5 py-1.5 text-right font-mono font-bold text-gray-900 border-l border-yellow-200 bg-yellow-100">{{ number_format($startingBalance, 0, ',', '.') }} </td>
                        </tr>
                        @foreach ($recap as $r)
                        <tr class="{{ $loop->odd ? 'crm-row-odd' : 'crm-row-even' }} transition border-b border-gray-100">
                            <td class="px-3.5 py-1.5">
                                <button type="button" onclick="showMonthlyDetail('{{ $r['month_name'] }}', {{ $loop->iteration }}, {{ $year }})" class="font-bold text-blue-600 hover:text-blue-800 hover:underline text-left focus:outline-none flex items-center gap-1.5">
                                    <i class="far fa-calendar-alt text-xs text-blue-400"></i>
                                    <span>{{ $r['month_name'] }}</span>
                                </button>
                            </td>
                            <td class="px-3.5 py-1.5 text-right font-mono text-gray-600">{{ $r['gross_in'] > 0 ? number_format($r['gross_in'], 0, ',', '.') : '-' }}</td>
                            <td class="px-3.5 py-1.5 text-center font-mono text-xs text-gray-500">{{ $r['commission_text'] }}</td>
                            <td class="px-3.5 py-1.5 text-right font-mono font-bold text-blue-800 bg-blue-50/30">{{ $r['net_value'] > 0 ? number_format($r['net_value'], 0, ',', '.') : '-' }}</td>
                            <td class="px-3.5 py-1.5 text-right font-mono text-red-600">{{ $r['out'] > 0 ? number_format($r['out'], 0, ',', '.') : '-' }}</td>
                            <td class="px-3.5 py-1.5 text-right font-mono font-bold text-gray-900 border-l border-gray-200 bg-gray-50/30">{{ number_format($r['saldo'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold text-gray-800 border-t-2 border-gray-200">
                        <tr><td class="px-3.5 py-2 uppercase">Total</td><td class="px-3.5 py-2 text-right text-gray-600">{{ number_format($yearlyTotals['gross_in'], 0, ',', '.') }}</td><td class="px-3.5 py-2 text-center">-</td> <td class="px-3.5 py-2 text-right text-blue-900 bg-blue-100">{{ number_format($yearlyTotals['net_value'], 0, ',', '.') }}</td><td class="px-3.5 py-2 text-right text-red-700">{{ number_format($yearlyTotals['out'], 0, ',', '.') }}</td><td class="px-3.5 py-2 text-right border-l border-gray-300">{{ number_format($yearlyTotals['saldo'], 0, ',', '.') }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

    {{-- MODAL: EDIT CLIENT PROFILE --}}
    @push('modals')
        <x-ui.modal
            id="editClientModal"
            maxWidth="max-w-5xl"
            title="Edit Client Profile"
            subtitle="Perbarui data profil personal, instansi, apoteker, serta informasi rekening & komisi."
            icon="fa-edit"
            iconClass="bg-blue-600 text-white"
        >
            <form id="editClientForm" action="{{ route($routePrefix . 'client.update', $client->id) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')

                @if($canEdit)
                <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-indigo-100 bg-indigo-50/70 px-3.5 py-2.5 text-xs">
                    <div class="flex items-center gap-2 text-indigo-900">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-white text-[11px]">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-indigo-500">PIC Saat Ini (Aktif)</span>
                            <span class="font-bold text-indigo-950">{{ $client->ps ?: ($client->user->name ?? '-') }}</span>
                        </div>
                    </div>
                    <x-ui.button variant="soft-blue" size="xs" type="button" icon="fa-exchange-alt" onclick="uiModalClose('editClientModal'); uiModalOpen('handoverPicModal');">
                        Ganti PIC (Marketing / Operasional)
                    </x-ui.button>
                </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
                    {{-- COLUMN 1 --}}
                    <div class="bg-white rounded-xl border border-blue-200/80 shadow-sm overflow-hidden flex flex-col h-full">
                        <div class="bg-blue-50/90 px-4 py-2.5 border-b border-blue-200 flex items-center">
                            <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">1</span>
                            <h4 class="text-blue-900 text-xs font-bold uppercase tracking-wider">Personal Profile</h4>
                        </div>
                        <div class="p-3.5 space-y-3 flex-grow">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase">Client Name &amp; Position <span class="text-red-500">*</span></label>
                                <div class="space-y-2">
                                    <input type="text" name="client_name" value="{{ old('client_name', $client->client_name) }}" required class="w-full border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-lg text-sm px-3 py-1.5 font-bold text-slate-900 outline-none transition">
                                    <input type="text" name="position" value="{{ old('position', $client->position) }}" class="w-full border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-lg text-xs px-3 py-1.5 text-slate-700 outline-none transition" placeholder="Position / Role">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Personal Contact</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="w-full border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-lg text-xs px-3 py-1.5 outline-none transition" placeholder="Phone (WA)">
                                    <input type="email" name="email" value="{{ old('email', $client->email) }}" class="w-full border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-lg text-xs px-3 py-1.5 outline-none transition" placeholder="Email">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Date of Birth</label>
                                    <input type="date" name="birthdate" value="{{ old('birthdate', optional($client->birthdate)->format('Y-m-d')) }}" class="w-full border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-lg text-xs px-2.5 py-1.5 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Hobbies</label>
                                    <input type="text" name="hobby" value="{{ old('hobby', $client->hobby) }}" class="w-full border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-lg text-xs px-3 py-1.5 outline-none transition" placeholder="Hobbies">
                                </div>
                            </div>
                            <div class="flex-grow">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Home Address</label>
                                <textarea name="address" rows="2" class="w-full border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-lg text-xs px-3 py-1.5 resize-none outline-none transition">{{ old('address', $client->address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- COLUMN 2 --}}
                    <div class="bg-white rounded-xl border border-orange-200/80 shadow-sm overflow-hidden flex flex-col h-full">
                        <div class="bg-orange-50/90 px-4 py-2.5 border-b border-orange-200 flex items-center">
                            <span class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">2</span>
                            <h4 class="text-orange-900 text-xs font-bold uppercase tracking-wider">Company Profile</h4>
                        </div>
                        <div class="p-3.5 space-y-2.5 flex-grow">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase">Institution / Hospital Name <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_name" value="{{ old('customer_name', $client->customer?->customer_name ?? '') }}" required class="w-full border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 rounded-lg text-xs px-3 py-1.5 font-semibold text-slate-900 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase">Sales System Name (Command Center)</label>
                                <input type="text" name="sales_customer_name" value="{{ old('sales_customer_name', $client->customer?->sales_customer_name) }}" class="w-full border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 rounded-lg text-xs px-3 py-1.5 font-semibold text-slate-800 outline-none transition" placeholder="Name in Sales system (optional)">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Company Details</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="area" value="{{ old('area', $client->customer?->area ?? '') }}" class="w-full border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 rounded-lg text-xs px-2.5 py-1.5 outline-none transition" placeholder="Area">
                                    <select name="customer_type" class="w-full border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 rounded-lg text-xs px-2.5 py-1.5 text-slate-700 bg-white outline-none transition">
                                        <option value="">— Select Type —</option>
                                        @foreach(\App\Models\Customer::TYPES as $t)
                                            <option value="{{ $t }}" @selected(old('customer_type', $client->customer?->customer_type) == $t)>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="company_phone" value="{{ old('company_phone', $client->customer?->phone ?? '') }}" class="w-full border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 rounded-lg text-xs px-2.5 py-1.5 outline-none transition" placeholder="Office Phone">
                                    <input type="email" name="company_email" value="{{ old('company_email', $client->customer?->email ?? '') }}" class="w-full border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 rounded-lg text-xs px-2.5 py-1.5 outline-none transition" placeholder="Office Email">
                                    <input type="date" name="anniversary_date" value="{{ old('anniversary_date', $client->customer?->anniversary_date?->format('Y-m-d')) }}" class="w-full border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 rounded-lg text-xs px-2.5 py-1.5 outline-none transition" title="Anniversary Date">
                                    <input type="text" name="npwp" value="{{ old('npwp', $client->customer?->npwp ?? '') }}" class="w-full border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 rounded-lg text-xs px-2.5 py-1.5 outline-none transition" placeholder="Tax ID / NPWP">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Office Address</label>
                                <textarea name="company_address" rows="2" class="w-full border border-slate-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-100 rounded-lg text-xs px-3 py-1.5 resize-none outline-none transition">{{ old('company_address', $client->customer?->displayAddress() ?? '') }}</textarea>
                            </div>
                            <div class="pt-2 border-t border-orange-100">
                                <label class="block text-[10px] font-bold text-orange-700 mb-1.5 uppercase">Pharmacist Information</label>
                                <div class="space-y-2">
                                    <input type="text" name="pharmacist_name" value="{{ old('pharmacist_name', $client->customer?->pharmacist_name ?? '') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 focus:ring-2 focus:ring-orange-100 px-3 py-1.5 outline-none transition" placeholder="Pharmacist Name">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="pharmacist_license_no" value="{{ old('pharmacist_license_no', $client->customer?->pharmacist_license_no ?? '') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 focus:ring-2 focus:ring-orange-100 px-2.5 py-1.5 outline-none transition" placeholder="License No. (SIPA)">
                                        <input type="text" name="pharmacist_phone" value="{{ old('pharmacist_phone', $client->customer?->pharmacist_phone ?? '') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:border-orange-500 focus:ring-2 focus:ring-orange-100 px-2.5 py-1.5 outline-none transition" placeholder="Pharmacist Phone">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- COLUMN 3 --}}
                    <div class="bg-white rounded-xl border border-emerald-200/80 shadow-sm overflow-hidden flex flex-col h-full">
                        <div class="bg-emerald-50/90 px-4 py-2.5 border-b border-emerald-200 flex items-center">
                            <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">3</span>
                            <h4 class="text-emerald-900 text-xs font-bold uppercase tracking-wider">Banking &amp; Financials</h4>
                        </div>
                        <div class="p-3.5 space-y-3 flex-grow flex flex-col">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Bank Name</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name', $client->bank_name) }}" class="w-full border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 rounded-lg text-xs px-3 py-1.5 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Account Number</label>
                                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $client->bank_account_number) }}" class="w-full border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 rounded-lg text-xs px-3 py-1.5 font-mono outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase">Account Holder (A/N)</label>
                                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $client->bank_account_name) }}" class="w-full border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 rounded-lg text-xs px-3 py-1.5 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-emerald-700 mb-1 uppercase">Commission Rate (%)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate', $client->commission_rate) }}" class="w-full border border-emerald-200 bg-emerald-50/40 rounded-lg text-sm font-bold text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 px-3 py-1.5 outline-none transition" placeholder="e.g. 2.5">
                                    <span class="absolute right-3 top-2 text-emerald-600 font-bold text-xs">%</span>
                                </div>
                            </div>
                            <div class="mt-auto pt-3 border-t border-emerald-100">
                                <label class="block text-[10px] font-bold text-emerald-700 mb-1 uppercase">Opening Balance</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-emerald-600 font-bold text-xs">Rp</span>
                                    <input type="number" name="opening_balance" value="{{ old('opening_balance', $client->opening_balance) }}" class="w-full pl-9 border border-emerald-200 bg-emerald-50/40 rounded-lg text-base font-bold font-mono text-emerald-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 px-3 py-1.5 outline-none transition">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Cancel</x-ui.button>
                <x-ui.button variant="primary" size="sm" type="submit" form="editClientForm" icon="fa-save">Update Profile</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL: EDIT TRANSACTION --}}
        <x-ui.modal
            id="editTransactionModal"
            maxWidth="max-w-md"
            title="Edit Transaction"
            subtitle="Perbarui tanggal, produk/keperluan, nominal, atau catatan transaksi."
            icon="fa-edit"
            iconClass="bg-blue-600 text-white"
        >
            <form id="formEditTransaction" action="#" method="POST" class="space-y-3.5">
                @csrf @method('PUT')
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Transaction Type</span>
                    <span id="editTransTypeBanner" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Sales (IN)</span>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase">Date</label>
                    <input type="date" name="interaction_date" id="edit_tanggal" class="w-full border border-slate-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 py-2 text-sm outline-none transition" required>
                </div>
                <div id="wrapper_produk">
                    <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase" id="label_produk">Product / Purpose</label>
                    <input type="text" name="" id="edit_produk" class="w-full border border-slate-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 py-2 text-sm font-bold outline-none transition">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase">Amount (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-slate-500 text-sm font-semibold">Rp</span>
                        <input type="text" name="" id="edit_nominal" onkeyup="formatRupiah(this)" class="w-full pl-9 border border-slate-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 py-2 font-mono font-bold text-base outline-none transition" required>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1 uppercase">Notes</label>
                    <textarea name="notes" id="edit_catatan" rows="2" class="w-full border border-slate-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 py-2 text-sm outline-none transition resize-none"></textarea>
                </div>
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Cancel</x-ui.button>
                <x-ui.button variant="primary" size="sm" type="submit" form="formEditTransaction" id="btnUpdateTrans" icon="fa-save">Update Transaction</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL: VIEW TRANSACTION (READ-ONLY) --}}
        <x-ui.modal
            id="viewTransactionModal"
            maxWidth="max-w-md"
            title="Transaction Details"
            subtitle="Detail rincian transaksi tercatat pada buku besar klien."
            icon="fa-info-circle"
            iconClass="bg-blue-600 text-white"
        >
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Date</span>
                        <span id="view_tanggal" class="text-sm font-semibold text-slate-800"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Transaction Type</span>
                        <span id="view_jenis" class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold shadow-sm mt-0.5"></span>
                    </div>
                </div>

                <div>
                    <span id="view_label_produk" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Product / Purpose</span>
                    <span id="view_produk" class="text-base font-bold text-slate-900 block mt-0.5"></span>
                </div>

                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200/80 grid grid-cols-2 gap-4">
                    <div>
                        <span id="view_label_nominal" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Amount</span>
                        <span id="view_nominal" class="text-lg font-mono font-bold text-slate-900"></span>
                    </div>
                    <div id="view_wrapper_commission" class="hidden">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Commission (Rate)</span>
                        <span id="view_rate" class="text-xs font-bold text-blue-700"></span>
                    </div>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Notes</span>
                    <p id="view_catatan" class="text-sm text-slate-600 bg-slate-50 border border-slate-200/80 rounded-xl p-3 mt-1 whitespace-pre-line italic"></p>
                </div>
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Close</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL: MONTHLY TRANSACTION DETAILS --}}
        <x-ui.modal
            id="monthlyDetailModal"
            maxWidth="max-w-3xl"
            title="Monthly Transaction Details"
            subtitle="Rincian transaksi Sales, Usage, dan Activity pada bulan terpilih."
            icon="fa-calendar-alt"
            iconClass="bg-slate-800 text-white"
        >
            <div class="overflow-x-auto w-full rounded-xl border border-slate-200">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2.5 w-28">Date</th>
                            <th class="px-4 py-2.5 w-28">Type</th>
                            <th class="px-4 py-2.5">Description / Product</th>
                            <th class="px-4 py-2.5 text-right w-36">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="monthly_detail_body" class="divide-y divide-slate-100">
                        {{-- Filled dynamically via JS --}}
                    </tbody>
                </table>
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Close</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL: SERAH TERIMA / GANTI PIC --}}
        <x-ui.modal
            id="handoverPicModal"
            maxWidth="max-w-2xl"
            title="Serah Terima PIC (Handover)"
            subtitle="Pilih karyawan divisi Marketing atau Operasional sebagai PIC baru klien ini."
            icon="fa-exchange-alt"
            iconClass="bg-indigo-600 text-white"
        >
            <form id="handoverPicForm" action="{{ route($routePrefix . 'client.handover', $client->id) }}" method="POST" class="space-y-4">
                @csrf
                {{-- Info Klien & PIC Saat Ini --}}
                <div class="bg-indigo-50/80 border border-indigo-200 rounded-xl p-3.5 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <span class="text-slate-500 font-bold block uppercase text-[10px]">Nama Klien</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $client->client_name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-bold block uppercase text-[10px]">Rumah Sakit / Instansi</span>
                            <span class="font-bold text-indigo-700 text-sm">{{ $client->customer?->customer_name ?? '-' }}</span>
                        </div>
                        <div class="sm:col-span-2 pt-2.5 border-t border-indigo-200/70 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="text-slate-500 font-bold block uppercase text-[10px]">PIC Saat Ini (Aktif)</span>
                                <span class="inline-flex items-center gap-1.5 font-extrabold text-indigo-900 bg-white px-2.5 py-1 rounded-lg border border-indigo-200 mt-0.5 shadow-sm">
                                    <i class="fas fa-user-check text-emerald-600 text-xs"></i> {{ $client->ps ?: ($client->user->name ?? '-') }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-500 text-[10px] font-semibold block">Mulai aktif sejak:</span>
                                <span class="font-bold text-slate-700">{{ optional($client->activePicHistory)->start_date ? $client->activePicHistory->start_date->format('d M Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Input Handover --}}
                <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center">
                            <i class="fas fa-user-plus text-indigo-600 mr-2"></i> Pengalihan ke PIC Baru
                        </h4>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">Divisi Marketing &amp; Operasional</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Pilih Karyawan (Marketing / Operasional) <span class="text-red-500">*</span>
                            </label>
                            <select id="handover_user_select" name="new_user_id" required onchange="const sel = this.options[this.selectedIndex]; if(sel.getAttribute('data-name')) document.getElementById('handover_pic_name').value = sel.getAttribute('data-name');" class="w-full border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 rounded-lg text-xs px-3 py-2 text-slate-800 font-medium bg-white outline-none transition">
                                <option value="">— Pilih Karyawan Marketing / Operasional —</option>
                                @foreach($availableUsers ?? [] as $u)
                                    <option value="{{ $u->id }}" data-name="{{ $u->name }}" @selected(old('new_user_id') == $u->id)>
                                        {{ $u->name }} ({{ $u->divisi ?: 'Marketing & Operasional' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Nama PIC di Command Center <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="handover_pic_name" name="new_ps" required value="{{ old('new_ps') }}" class="w-full border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 rounded-lg text-xs px-3 py-2 font-bold text-slate-800 outline-none transition" placeholder="Misal: Indah">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Harus sesuai nama PS di Command Center.</span>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Tanggal Efektif Serah Terima <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="handover_date" value="{{ old('handover_date', date('Y-m-d')) }}" required class="w-full border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 rounded-lg text-xs px-3 py-2 font-semibold text-slate-800 outline-none transition">
                            <span class="text-[10px] text-slate-500 mt-0.5 block">Transaksi sebelum tanggal ini tetap diatribusikan ke PIC sebelumnya.</span>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan / Alasan Handover</label>
                            <textarea name="handover_notes" rows="2" class="w-full border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 rounded-lg text-xs px-3 py-1.5 resize-none outline-none transition" placeholder="Contoh: Rolling wilayah kerja / penugasan baru">{{ old('handover_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Riwayat PIC Sebelumnya --}}
                <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2.5 flex items-center">
                        <i class="fas fa-history text-slate-500 mr-2"></i> Riwayat PIC Klien Ini
                    </h4>
                    <div class="space-y-2 max-h-40 overflow-y-auto custom-scrollbar">
                        @forelse($client->picHistories ?? [] as $hist)
                            <div class="flex items-center justify-between text-xs p-2.5 rounded-lg {{ is_null($hist->end_date) ? 'bg-emerald-50 border border-emerald-200' : 'bg-slate-50 border border-slate-200/70' }}">
                                <div>
                                    <div class="font-bold {{ is_null($hist->end_date) ? 'text-emerald-900' : 'text-slate-800' }}">
                                        {{ $hist->pic_name }}
                                        @if(is_null($hist->end_date))
                                            <span class="ml-1.5 bg-emerald-600 text-white text-[9px] font-extrabold px-1.5 py-0.5 rounded uppercase">Aktif</span>
                                        @endif
                                    </div>
                                    @if($hist->handover_notes)
                                        <div class="text-[11px] text-slate-500 italic">{{ $hist->handover_notes }}</div>
                                    @endif
                                </div>
                                <div class="text-right text-[11px] text-slate-600 font-mono">
                                    <span>{{ $hist->start_date ? $hist->start_date->format('d/m/Y') : '-' }}</span>
                                    <span class="mx-1">&rarr;</span>
                                    <span>{{ $hist->end_date ? $hist->end_date->format('d/m/Y') : 'Sekarang' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-slate-400 text-xs py-3 italic">Belum ada riwayat tercatat.</div>
                        @endforelse
                    </div>
                </div>
            </form>

            <x-slot:footer>
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Batal</x-ui.button>
                <x-ui.button variant="primary" size="sm" type="submit" form="handoverPicForm" icon="fa-check">Simpan Serah Terima</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL: USAGE WORKFLOW INFO --}}
        <x-ui.modal
            id="usage-info-modal"
            maxWidth="max-w-md"
            title="Disbursement Workflow Info"
            subtitle="Alur pencatatan dukungan dana & pengajuan dana ke Finance."
            icon="fa-info-circle"
            iconClass="bg-red-600 text-white"
        >
            <div class="space-y-3 text-sm text-slate-600 leading-relaxed">
                <p class="font-bold text-red-600 flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Automated Fund Request Creation
                </p>
                <p>Submitting this form will automatically generate a Fund Request (Pengajuan Dana) in the Finance system with status <span class="font-bold text-slate-800">Draft</span>.</p>
                <p>The expense will be formally processed once approved by the Division Head and Finance team. You do not need to submit a separate manual request.</p>
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
            <div class="space-y-3 text-sm text-slate-600 leading-relaxed">
                <p class="font-bold text-orange-600 flex items-center gap-2">
                    <i class="fas fa-handshake"></i> Activity &amp; Hospitality Logging
                </p>
                <p>Use this form to document client meetings, lunches, entertainment, and hospitality activities.</p>
                <p>Cost entries recorded here are for tracking &amp; engagement reporting only and <span class="font-bold text-slate-800">do not</span> create an automated fund request in Finance.</p>
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
            if (monthlyTitleEl) monthlyTitleEl.innerText = `Transaction Details - ${monthName} ${year}`;
            
            const tbody = document.getElementById('monthly_detail_body');
            tbody.innerHTML = '';
            
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-gray-400">No transactions recorded for this month.</td></tr>';
            } else {
                filtered.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.className = "hover:bg-slate-50 transition border-b border-slate-100";
                    
                    const dateObj = new Date(item.interaction_date);
                    const formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    
                    let typeBadge = '';
                    let details = `<strong>${item.product_name}</strong>`;
                    let nominal = 0;
                    
                    if (item.transaction_type === 'IN') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Sales (IN)</span>';
                        nominal = item.sales_amount > 0 ? item.sales_amount : item.amount;
                        
                        let rate = parseFloat(item.commission_rate ?? 0) || 0;
                        const note = item.notes || '';
                        if (rate > 0) {
                            const valueNet = nominal * (rate / 100);
                            details += `<div class="text-[10px] text-gray-500 italic mt-0.5">${note}</div>`;
                            details += `<div class="text-[10px] text-blue-700 font-semibold mt-0.5">Rate: ${rate}% (Net: Rp ${valueNet.toLocaleString('id-ID')})</div>`;
                        } else if (note) {
                            details += `<div class="text-[10px] text-gray-500 italic mt-0.5">${note}</div>`;
                        }
                    } else if (item.transaction_type === 'OUT') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Usage (OUT)</span>';
                        nominal = item.amount;
                        if (item.notes) {
                            details += `<div class="text-[10px] text-gray-500 italic mt-0.5">${item.notes}</div>`;
                        }
                    } else if (item.transaction_type === 'ENTERTAIN') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800">Activity</span>';
                        nominal = item.amount;
                        if (item.notes) {
                            details += `<div class="text-[10px] text-gray-500 italic mt-0.5">${item.notes}</div>`;
                        }
                    }
                    
                    tr.innerHTML = `
                        <td class="px-4 py-2.5 font-semibold text-slate-700">${formattedDate}</td>
                        <td class="px-4 py-2.5">${typeBadge}</td>
                        <td class="px-4 py-2.5">${details}</td>
                        <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-800">Rp ${parseFloat(nominal).toLocaleString('id-ID')}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
            
            uiModalOpen('monthlyDetailModal');
        }

        function toggleDirectUsageDesktop(checkbox) {
            const container = document.getElementById('bank-info-container-desktop');
            const inputs = container.querySelectorAll('input');
            const submitBtn = document.getElementById('submit-btn-support-desktop');
            
            if (checkbox.checked) {
                container.classList.add('hidden');
                inputs.forEach(input => {
                    input.removeAttribute('required');
                });
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Record Usage Directly';
                submitBtn.className = "w-full h-[34px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 rounded-lg shadow-sm transition transform active:scale-95 text-xs md:text-sm flex items-center justify-center gap-2";
            } else {
                container.classList.remove('hidden');
                inputs.forEach(input => {
                    input.setAttribute('required', 'required');
                });
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Fund Request';
                submitBtn.className = "w-full h-[34px] bg-red-600 hover:bg-red-700 text-white font-bold px-4 rounded-lg shadow-sm transition transform active:scale-95 text-xs md:text-sm flex items-center justify-center gap-2";
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
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200";
                jenisBadge.innerText = "Sales (IN)";
                labelNominal.innerText = "Gross Sales Amount";
                labelProduk.innerText = "Product Name";
                
                wrapperCommission.classList.remove('hidden');
                document.getElementById('view_rate').innerText = data.rate + '% (Net: Rp ' + data.valueNet + ')';
                document.getElementById('view_nominal').innerText = 'Rp ' + data.nominal;
            } else if (data.jenis === 'OUT') {
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200";
                jenisBadge.innerText = "Usage (OUT)";
                labelNominal.innerText = "Support Amount";
                labelProduk.innerText = "Support Purpose";
                
                document.getElementById('view_nominal').innerText = 'Rp ' + data.nominal;
            } else if (data.jenis === 'ENTERTAIN') {
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200";
                jenisBadge.innerText = "Activity (ENTERTAIN)";
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
                    typeBanner.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200';
                    typeBanner.innerText = 'Sales (IN)';
                }
                labelProduk.innerText = "Product Name";
                inputProduk.name = "product_name";
                inputProduk.value = data.produk; 
                inputNominal.name = "sales_amount"; 
            } else if (data.jenis === 'OUT') {
                if (titleEl) titleEl.innerText = 'Edit Usage Entry';
                if (typeBanner) {
                    typeBanner.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200';
                    typeBanner.innerText = 'Usage (OUT)';
                }
                labelProduk.innerText = "Support Purpose";
                inputProduk.name = "purpose";
                inputProduk.value = data.produk.replace('USAGE : ', '');
                inputNominal.name = "amount"; 
            } else if (data.jenis === 'ENTERTAIN') {
                if (titleEl) titleEl.innerText = 'Edit Activity Entry';
                if (typeBanner) {
                    typeBanner.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-orange-50 text-orange-700 border border-orange-200';
                    typeBanner.innerText = 'Activity (ENTERTAIN)';
                }
                wrapperProduk.classList.add('hidden');
                inputProduk.removeAttribute('required');
                inputNominal.name = "amount"; 
            }

            uiModalOpen('editTransactionModal');
        }

        function switchTab(tabName) {
            const sections = { 'sales': 'section-sales', 'support': 'section-support', 'activity': 'section-activity', 'history': 'section-history', 'recap': 'section-recap' };
            const buttons = { 'sales': 'btn-sales', 'support': 'btn-support', 'activity': 'btn-activity', 'history': 'btn-history', 'recap': 'btn-recap' };
            const inactiveClass = "nav-btn px-4 py-2 rounded-lg text-xs md:text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none text-gray-500 hover:text-gray-700 hover:bg-gray-50";
            const activeBase = "nav-btn px-4 py-2 rounded-lg text-xs md:text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none shadow-md ring-1 ring-inset";

            for (const k in sections) {
                document.getElementById(sections[k]).classList.add('hidden');
                document.getElementById(buttons[k]).className = inactiveClass;
            }
            document.getElementById(sections[tabName]).classList.remove('hidden');
            
            let specificActiveClass = "";
            if (tabName === 'sales') specificActiveClass = "bg-blue-600 text-white ring-blue-700";
            else if (tabName === 'support') specificActiveClass = "bg-red-600 text-white ring-red-700";
            else if (tabName === 'activity') specificActiveClass = "bg-orange-500 text-white ring-orange-600";
            else specificActiveClass = "bg-gray-800 text-white ring-gray-900";
            
            document.getElementById(buttons[tabName]).className = activeBase + " " + specificActiveClass;
            localStorage.setItem('activeTab', tabName);
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

        function fetchSalesData() {
            const dateInput = document.getElementById('tanggal_interaksi_in').value;
            const salesCustomer = document.getElementById('client_id_in').value;
            
            if (!salesCustomer) {
                alert('Please select a hospital / client first.');
                return;
            }
            if (!dateInput) {
                alert('Please select a transaction month first.');
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
                        const container = document.getElementById('product_rows_container_desktop');
                        container.innerHTML = '';
                        
                        let countAdded = 0;
                        resData.data.forEach((item) => {
                            addProductRow('desktop', item.product_name, item.sales_amount, (item.date || dateInput), item.client_id, item.customer_name);
                            countAdded++;
                        });

                        document.getElementById('queue_count_desktop').innerText = countAdded;
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

            row.className = 'product-row flex items-center gap-2.5 bg-blue-50/50 p-2.5 rounded-lg border border-blue-100 hover:shadow-sm transition-all';
            row.innerHTML = `
                <input type="hidden" name="client_id[]" value="${clientId}">
                <div class="flex-1 max-w-[120px]">
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Date</label>
                    <input type="date" name="interaction_date[]" class="w-full border border-gray-200 bg-gray-50 rounded-md px-2 py-1.5 text-[11px] text-gray-500 font-semibold" value="${date}" readonly>
                </div>
                <div class="flex-1 max-w-[150px]">
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Hospital / Client</label>
                    <input type="text" class="w-full border border-gray-200 bg-gray-50 rounded-md px-2 py-1.5 text-[11px] text-gray-500 font-semibold" value="${clientName}" readonly>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="product_name[]" class="w-full border border-gray-200 bg-gray-50 rounded-md px-2.5 py-1.5 text-xs md:text-sm text-gray-700" value="${produk}" readonly>
                </div>
                <div class="flex-1 max-w-[160px]">
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Sales Amount (Rp) <span class="text-red-500">*</span></label>
                    <div class="relative rounded-md">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5"><span class="text-gray-500 text-xs font-bold">Rp</span></div>
                        <input type="text" name="sales_amount[]" class="w-full border border-gray-200 bg-gray-50 rounded-md pl-8 pr-2.5 py-1.5 font-mono text-xs md:text-sm font-bold text-gray-700" value="${valNominal}" readonly>
                    </div>
                </div>
                <div>
                    <button type="button" onclick="removeProductRow(this)" class="text-red-500 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 rounded-md text-xs transition" title="Remove Row"><i class="fas fa-trash-alt"></i></button>
                </div>
            `;
            container.appendChild(row);
        }

        function removeProductRow(btn) {
            btn.closest('.product-row').remove();
            let badge = document.getElementById('queue_count_desktop');
            let current = parseInt(badge.innerText);
            if (current > 0) badge.innerText = current - 1;
        }

        function updateFileNameSupportDesktop(input) {
            const label = document.getElementById('file-name-support-desktop');
            if (input.files && input.files[0]) {
                label.innerText = input.files[0].name;
            } else {
                label.innerText = 'Select file...';
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
    @endpush

</x-layout-users>