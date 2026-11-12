<x-layout-admin :title="'Client Details: ' . $client->client_name">

    <div class="w-full max-w-7xl mx-auto px-0 py-0 relative">
        
        {{-- BACK BUTTON & NAVIGATION --}}
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('admin.crm.index') }}" class="inline-flex items-center text-zinc-400 hover:text-amber-500 font-semibold transition-colors text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Sales Monitoring
            </a>
        </div>

        {{-- SECTION 1: HERO PROFILE --}}
        <div class="bg-gradient-to-r from-zinc-800 to-zinc-900 rounded-xl shadow-lg border border-zinc-700/50 p-6 mb-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-64 h-64 bg-amber-500/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>

            <div class="flex flex-col xl:flex-row justify-between items-start gap-6 relative z-10">
                <div class="flex-grow">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="bg-amber-500/10 text-amber-500 text-xs font-bold px-3 py-1 rounded-full border border-amber-500/20 uppercase tracking-wide">
                            <i class="fas fa-building mr-1"></i> {{ $client->customer?->customer_name ?? '-' }}
                        </span>
                        <span class="text-zinc-500 text-xs font-semibold flex items-center bg-zinc-800 px-3 py-1 rounded-full border border-zinc-700">
                            <i class="fas fa-user-tie mr-2 text-zinc-400"></i> PIC: <span class="text-zinc-300 ml-1 font-bold">{{ $client->ps ?? $client->user->name ?? 'Deleted User' }}</span>
                            @if(isset($picHistories) && $picHistories->count() > 1)
                                <span class="ml-2 bg-zinc-700 text-amber-400 text-[10px] font-bold px-2 py-0.5 rounded-full"><i class="fas fa-history mr-1"></i> {{ $picHistories->count() }} PIC</span>
                            @endif
                        </span>
                    </div>

                    <h1 class="text-4xl font-extrabold text-white tracking-tight mb-2">
                        {{ $client->client_name }}
                    </h1>
                    
                    <p class="text-zinc-400 text-sm flex items-center gap-4">
                        <span class="flex items-center">{{ $client->customer?->area ?? 'Area Not Set' }}</span>
                        <span class="text-zinc-600">|</span>
                        <span class="flex items-center"><i class="fas fa-calendar-alt mr-2 text-blue-500"></i> Joined: {{ $client->created_at->format('d M Y') }}</span>
                    </p>

                    <div class="flex items-center gap-3 mt-6">
                        <button onclick="toggleModal('handoverPicModal')" class="flex items-center text-xs font-bold text-indigo-400 bg-indigo-900/20 hover:bg-indigo-900/40 px-4 py-2 rounded-lg border border-indigo-800/50 transition">
                            <i class="fas fa-exchange-alt mr-2"></i> Serah Terima PIC
                        </button>
                        <button onclick="toggleModal('editClientModal')" class="flex items-center text-xs font-bold text-amber-400 bg-amber-900/20 hover:bg-amber-900/40 px-4 py-2 rounded-lg border border-amber-800/50 transition">
                            <i class="fas fa-edit mr-2"></i> Edit Full Details
                        </button>
                        <form action="{{ route('admin.crm.client.destroy', $client->id) }}" method="POST" onsubmit="confirmSubmit(event, 'ADMIN WARNING: Deleting this client will permanently remove all related transaction records. Continue?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="flex items-center text-xs font-bold text-red-400 bg-red-900/20 hover:bg-red-900/40 px-4 py-2 rounded-lg border border-red-800/50 transition">
                                <i class="fas fa-trash-alt mr-2"></i> Delete Client
                            </button>
                        </form>
                    </div>
                </div>

                @php
                    $total_realisasi = $client->interactions->where('transaction_type', 'OUT')->sum('amount');
                @endphp
                <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                    {{-- TOTAL USAGE / REALIZATION --}}
                    <div class="bg-zinc-950/50 p-5 rounded-xl border border-zinc-800 text-right min-w-[220px] flex-1 md:flex-none">
                        <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-1">Total Realization</p>
                        <div class="text-2xl md:text-3xl font-extrabold text-emerald-500">
                            <span class="text-sm text-zinc-600 mr-1">Rp</span>{{ number_format($total_realisasi, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- TOTAL NET BALANCE --}}
                    <div class="bg-zinc-950/50 p-5 rounded-xl border border-zinc-800 text-right min-w-[220px] flex-1 md:flex-none relative overflow-hidden group">
                        <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-1">Total Balance</p>
                        <div class="text-2xl md:text-3xl font-extrabold text-blue-400">
                            <span class="text-sm text-zinc-600 mr-1">Rp</span>{{ number_format($currentBalance, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CLIENT DETAILS GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Personal Profile Card --}}
            <div class="bg-zinc-800 rounded-xl border border-zinc-700/50 p-5 shadow-lg flex flex-col h-full">
                <div class="mb-4 pb-3 border-b border-zinc-700 flex items-center justify-between">
                    <h4 class="text-sm font-bold text-zinc-300 uppercase tracking-wide">
                        <i class="fas fa-address-book mr-2 text-blue-500"></i> Personal Profile
                    </h4>
                </div>
                <div class="space-y-4 flex-grow">
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Position</p>
                        <p class="text-zinc-200 text-sm font-medium">{{ $client->position ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Hobby / Interests</p>
                        <p class="text-zinc-200 text-sm font-medium">{{ $client->hobby ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Email & Phone</p>
                        <p class="text-zinc-200 text-sm font-medium">{{ $client->email ?? '-' }}</p>
                        <p class="text-zinc-200 text-sm font-medium flex items-center mt-1">
                            {{ $client->phone ?? '-' }}
                            @if($client->phone)
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $client->phone)) }}" target="_blank" class="ml-2 text-green-500 hover:text-green-400">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Date of Birth</p>
                        <p class="text-zinc-200 text-sm font-medium">{{ $client->birthdate ? \Carbon\Carbon::parse($client->birthdate)->format('d M Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Residential Address</p>
                        <p class="text-zinc-300 text-sm leading-relaxed">{{ $client->address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Institution Card --}}
            <div class="bg-zinc-800 rounded-xl border border-zinc-700/50 p-5 shadow-lg flex flex-col h-full">
                <div class="mb-4 pb-3 border-b border-zinc-700 flex items-center justify-between">
                    <h4 class="text-sm font-bold text-zinc-300 uppercase tracking-wide">
                        <i class="fas fa-hospital mr-2 text-amber-500"></i> Institution & Company
                    </h4>
                </div>
                <div class="space-y-4 flex-grow">
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Institution Name</p>
                        <p class="text-zinc-200 text-sm font-bold">{{ $client->customer?->customer_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Name in Sales / Command Center</p>
                        <p class="text-zinc-200 text-sm font-medium">{{ $client->customer?->sales_customer_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Area & Type</p>
                        <div class="flex items-center gap-2">
                            <span class="bg-zinc-700 text-zinc-300 text-[10px] px-2 py-0.5 rounded">{{ $client->customer?->area ?? 'Non-Area' }}</span>
                            <span class="bg-zinc-700 text-zinc-300 text-[10px] px-2 py-0.5 rounded">{{ $client->customer?->customer_type ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Office Address</p>
                        <p class="text-zinc-300 text-sm leading-relaxed">{{ $client->customer?->displayAddress() ?: '-' }}</p>
                    </div>
                    <div class="pt-3 border-t border-zinc-700/50">
                        <p class="text-[10px] text-amber-500/80 uppercase font-bold mb-2">
                            <i class="fas fa-user-md mr-1"></i> Pharmacist Information
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Name</p>
                                <p class="text-zinc-200 text-sm font-medium">{{ $client->customer?->pharmacist_name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">SIPA License</p>
                                <p class="text-zinc-200 text-sm font-medium">{{ $client->customer?->pharmacist_license_no ?? '-' }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Phone Number</p>
                                <p class="text-zinc-200 text-sm font-medium">{{ $client->customer?->pharmacist_phone ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Banking Card --}}
            <div class="bg-gradient-to-br from-zinc-800 to-zinc-900 rounded-xl border border-zinc-700/50 p-5 shadow-lg flex flex-col h-full relative overflow-hidden">
                <div class="mb-4 pb-3 border-b border-zinc-700 flex items-center justify-between relative z-10">
                    <h4 class="text-sm font-bold text-emerald-400 uppercase tracking-wide">
                        <i class="fas fa-coins mr-2"></i> Financials & Banking
                    </h4>
                </div>
                <div class="space-y-4 relative z-10">
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Bank Name</p>
                        <p class="text-white text-lg font-bold tracking-wide">{{ $client->bank_name ?? 'BANK -' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Account Number</p>
                        <p class="text-zinc-300 text-sm font-mono tracking-wider">{{ $client->bank_account_number ?? '----' }}</p>
                        <p class="text-zinc-500 text-xs mt-0.5">{{ $client->bank_account_name ? 'Beneficiary: '.$client->bank_account_name : '' }}</p>
                    </div>
                    <div class="pt-2 border-t border-zinc-700/50">
                        <p class="text-[10px] text-zinc-500 uppercase font-bold mb-0.5">Opening Balance</p>
                        <p class="text-emerald-400 text-xl font-bold font-mono">
                            <span class="text-sm text-emerald-700 mr-1">Rp</span>{{ number_format($client->opening_balance ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABS --}}
        <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="bg-zinc-900 p-1 rounded-lg inline-flex border border-zinc-700 w-full md:w-auto justify-center md:justify-start">
                <button id="btn-history" onclick="switchTab('history')" class="px-6 py-2 rounded-md text-sm font-bold bg-zinc-700 text-amber-500 shadow-sm transition-all flex-1 md:flex-none">
                    <i class="fas fa-history mr-2"></i> History
                </button>
                <button id="btn-recap" onclick="switchTab('recap')" class="px-6 py-2 rounded-md text-sm font-bold text-zinc-500 hover:text-zinc-300 transition-all flex-1 md:flex-none">
                    <i class="fas fa-chart-bar mr-2"></i> Yearly Summary
                </button>
            </div>
            <div class="bg-zinc-900 p-1 rounded-lg inline-flex border border-zinc-700 w-full md:w-auto justify-center md:justify-end gap-1 overflow-x-auto">
                <button id="btn-sales" onclick="switchTab('sales')" class="px-4 py-2 rounded-md text-sm font-bold text-zinc-500 hover:text-emerald-400 transition-all whitespace-nowrap">
                    <i class="fas fa-plus-circle mr-2"></i> Sales (In)
                </button>
                <button id="btn-support" onclick="switchTab('support')" class="px-4 py-2 rounded-md text-sm font-bold text-zinc-500 hover:text-red-400 transition-all whitespace-nowrap">
                    <i class="fas fa-hand-holding-usd mr-2"></i> Usage (Out)
                </button>
                <button id="btn-activity" onclick="switchTab('activity')" class="px-4 py-2 rounded-md text-sm font-bold text-zinc-500 hover:text-purple-400 transition-all whitespace-nowrap">
                    <i class="fas fa-glass-cheers mr-2"></i> Activity
                </button>
            </div>
        </div>

        {{-- [TAB 1] SALES (IN) --}}
        <div id="section-sales" class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700/50 overflow-hidden relative hidden">
            <div class="bg-emerald-900/20 px-6 py-4 border-b border-emerald-900/50 flex justify-between items-center">
                <h3 class="font-bold text-emerald-400 text-lg flex items-center">
                    <i class="fas fa-plus-circle mr-3"></i> Record Sales (Admin)
                </h3>
            </div>
            <div class="p-6 md:p-8">
                <form action="{{ route('admin.crm.interaction.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-bold text-zinc-400 mb-1">Transaction Date <span class="text-red-500">*</span></label>
                                <input type="date" name="interaction_date" value="{{ date('Y-m-d') }}" class="w-full bg-zinc-900 border-zinc-600 rounded-lg shadow-sm text-white focus:border-emerald-500 px-4 py-2.5 [color-scheme:dark]" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-zinc-400 mb-1">Product Name <span class="text-red-500">*</span></label>
                                <input type="text" name="product_name" class="w-full bg-zinc-900 border-zinc-600 rounded-lg shadow-sm text-white focus:border-emerald-500 px-4 py-2.5" placeholder="e.g. Sterile Gauze" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-zinc-500 mb-1">Notes</label>
                                <textarea name="notes" rows="3" class="w-full bg-zinc-900 border-zinc-600 rounded-lg shadow-sm text-white focus:border-emerald-500 px-4 py-2.5" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <div class="space-y-5 bg-zinc-900/50 p-6 rounded-xl border border-zinc-700">
                            <div>
                                <label class="block text-sm font-bold text-zinc-400 mb-1">Sales Amount (Rp) <span class="text-red-500">*</span></label>
                                <input type="text" id="inputSales" name="sales_amount" class="w-full bg-zinc-800 border-zinc-600 rounded-lg shadow-sm text-white focus:border-emerald-500 px-4 py-2.5 font-mono text-lg" placeholder="0" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-zinc-400 mb-1">Commission Rate (%) <span class="text-red-500">*</span></label>
                                <input type="number" name="commission_rate" step="0.1" max="100" class="w-full bg-zinc-800 border-zinc-600 rounded-lg shadow-sm text-white focus:border-emerald-500 px-4 py-2.5 font-mono" placeholder="10" required>
                            </div>
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition transform active:scale-95 flex justify-center items-center gap-2 mt-2">
                                <i class="fas fa-save"></i> Save Transaction
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- [TAB 2] SUPPORT (OUT) --}}
        <div id="section-support" class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700/50 overflow-hidden relative hidden">
            <div class="bg-red-900/20 px-6 py-4 border-b border-red-900/50 flex justify-between items-center">
                <h3 class="font-bold text-red-400 text-lg flex items-center">
                    <i class="fas fa-hand-holding-usd mr-3"></i> Record Usage / Support (Admin)
                </h3>
            </div>
            <div class="p-6 md:p-8">
                <form action="{{ route('admin.crm.interaction.support') }}" method="POST">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-bold text-zinc-400 mb-1">Transaction Date <span class="text-red-500">*</span></label>
                                <input type="date" name="interaction_date" value="{{ date('Y-m-d') }}" class="w-full bg-zinc-900 border-zinc-600 rounded-lg shadow-sm text-white focus:border-red-500 px-4 py-2.5 [color-scheme:dark]" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-zinc-400 mb-1">Purpose / Description <span class="text-red-500">*</span></label>
                                <input type="text" name="purpose" class="w-full bg-zinc-900 border-zinc-600 rounded-lg shadow-sm text-white focus:border-red-500 px-4 py-2.5" placeholder="e.g. Transport, Operational" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-zinc-500 mb-1">Notes</label>
                                <textarea name="notes" rows="3" class="w-full bg-zinc-900 border-zinc-600 rounded-lg shadow-sm text-white focus:border-red-500 px-4 py-2.5" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>
                        <div class="space-y-5 bg-zinc-900/50 p-6 rounded-xl border border-zinc-700">
                            <div>
                                <label class="block text-sm font-bold text-zinc-400 mb-1">Usage Amount (Rp) <span class="text-red-500">*</span></label>
                                <input type="text" id="inputSupport" name="amount" class="w-full bg-zinc-800 border-zinc-600 rounded-lg shadow-sm text-white focus:border-red-500 px-4 py-2.5 font-mono text-lg" placeholder="0" required>
                                <p class="text-xs text-red-400 mt-2 flex items-center">
                                    <i class="fas fa-info-circle mr-1"></i> Deducts client balance
                                </p>
                            </div>
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition transform active:scale-95 flex justify-center items-center gap-2 mt-8">
                                <i class="fas fa-minus-circle"></i> Save Usage
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- [TAB 3] ACTIVITY / ENTERTAIN --}}
        <div id="section-activity" class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700/50 overflow-hidden relative hidden">
            <div class="bg-purple-900/20 px-6 py-4 border-b border-purple-900/50 flex justify-between items-center">
                <h3 class="font-bold text-purple-400 text-lg flex items-center">
                    <i class="fas fa-glass-cheers mr-3"></i> Record Activity / Entertainment (Admin)
                </h3>
            </div>
            <div class="p-6 md:p-8">
                <form action="{{ route('admin.crm.interaction.entertain') }}" method="POST">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-bold text-zinc-400 mb-1">Activity Date <span class="text-red-500">*</span></label>
                                <input type="date" name="interaction_date" value="{{ date('Y-m-d') }}" class="w-full bg-zinc-900 border-zinc-600 rounded-lg shadow-sm text-white focus:border-purple-500 px-4 py-2.5 [color-scheme:dark]" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-zinc-400 mb-1">Activity Title / Event <span class="text-red-500">*</span></label>
                                <input type="text" name="notes" class="w-full bg-zinc-900 border-zinc-600 rounded-lg shadow-sm text-white focus:border-purple-500 px-4 py-2.5" placeholder="e.g. Dinner Meeting / Lunch" required>
                            </div>
                        </div>
                        <div class="space-y-5 bg-zinc-900/50 p-6 rounded-xl border border-zinc-700">
                            <div>
                                <label class="block text-sm font-bold text-zinc-400 mb-1">Cost / Expense (Rp) <span class="text-red-500">*</span></label>
                                <input type="text" id="inputEntertain" name="amount" class="w-full bg-zinc-800 border-zinc-600 rounded-lg shadow-sm text-white focus:border-purple-500 px-4 py-2.5 font-mono text-lg" placeholder="0" required>
                                <p class="text-xs text-purple-400 mt-2 flex items-center">
                                    <i class="fas fa-check-circle mr-1"></i> Does not deduct client balance
                                </p>
                            </div>
                            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition transform active:scale-95 flex justify-center items-center gap-2 mt-8">
                                <i class="fas fa-save"></i> Save Activity
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- [TAB 4] RIWAYAT (HISTORY) --}}
        <div id="section-history" class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-700 bg-zinc-800 flex flex-col sm:flex-row justify-between items-center gap-4">
                <h3 class="font-bold text-zinc-200">Transaction History</h3>
                <form action="{{ route('admin.crm.show', $client->id) }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="history">
                    <label class="text-xs font-bold text-zinc-500 uppercase">Filter:</label>
                    <div class="relative">
                        <select name="history_year" onchange="this.form.submit()" class="text-xs font-bold bg-zinc-900 border-zinc-700 text-zinc-300 rounded-lg shadow-sm focus:border-amber-500 py-1.5 pl-3 pr-8 appearance-none">
                            <option value="">All Years</option>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ (request('history_year') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-500">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left min-w-[900px] text-zinc-300">
                    <thead class="bg-zinc-900/50 text-zinc-400 uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Item / Description</th>
                            <th class="px-6 py-3 text-right">Sales (In)</th>
                            <th class="px-6 py-3 text-center">Commission</th>
                            <th class="px-6 py-3 text-right text-blue-400">Value (Net)</th>
                            <th class="px-6 py-3 text-right text-red-400">Usage (Out)</th>
                            <th class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-700">
                        @forelse ($interactions as $item)
                        @php
                            $isOut = $item->transaction_type == 'OUT'; 
                            $isEntertain = $item->transaction_type == 'ENTERTAIN';
                            $rate = (float)($item->commission_rate ?? 0);
                            $displayNote = trim(preg_replace('/\[Rate:[\d\.]+%?\]\s*/', '', $item->notes));
                            $gross = ($item->sales_amount > 0) ? $item->sales_amount : $item->amount; 
                            $valueNet = $isOut ? 0 : ($gross * ($rate/100));
                            $rowClass = $isEntertain ? "bg-purple-900/10 hover:bg-purple-900/20 transition border-l-4 border-purple-500" : "hover:bg-zinc-700/30 transition";
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="px-6 py-3 font-medium text-zinc-400">
                                {{ \Carbon\Carbon::parse($item->interaction_date)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-3">
                                @if($isEntertain)
                                    <div class="font-bold text-purple-400">Activity: {{ $displayNote }}</div>
                                @else
                                    <div class="font-bold {{ $isOut ? 'text-red-400' : 'text-zinc-200' }}">{{ $item->product_name }}</div>
                                    <div class="text-xs text-zinc-500 italic">{{ $displayNote }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-mono text-emerald-500">
                                {{ (!$isOut && !$isEntertain) ? number_format($gross, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if(!$isOut && !$isEntertain && $rate > 0)
                                    <span class="bg-zinc-700 text-zinc-300 text-[10px] font-bold px-2 py-0.5 rounded border border-zinc-600">{{ $rate }}%</span>
                                @else 
                                    - 
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-bold font-mono text-blue-400">
                                {{ (!$isOut && !$isEntertain) ? number_format($valueNet, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-3 text-right font-bold font-mono">
                                @if($isOut)
                                    <span class="text-red-400">{{ number_format($item->amount, 0, ',', '.') }}</span>
                                @elseif($isEntertain)
                                    <span class="text-purple-400 text-xs font-normal">({{ number_format($item->amount, 0, ',', '.') }})</span>
                                @else 
                                    - 
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="openEditTransactionModal({id: '{{ $item->id }}', jenis: '{{ $item->transaction_type }}', tanggal: '{{ $item->interaction_date }}', produk: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $item->product_name)) }}', nominal: '{{ ($item->transaction_type == 'IN') ? $item->sales_amount : $item->amount }}', rate: '{{ $rate }}', catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $displayNote)) }}'})" class="text-zinc-400 hover:text-amber-500 transition">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.crm.interaction.destroy', $item->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Are you sure you want to delete this transaction record?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-zinc-600 hover:text-red-500 transition">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-zinc-500">No transaction records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-zinc-800 border-t border-zinc-700">
                {{ $interactions->appends(request()->query())->links() }}
            </div>
        </div>

        {{-- [TAB 5] REKAP (RECAP) --}}
        <div id="section-recap" class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700/50 overflow-hidden hidden">
            <div class="px-6 py-4 border-b border-zinc-700 flex flex-col md:flex-row justify-between items-center gap-4 bg-zinc-800">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-zinc-200">Yearly Summary {{ $year }}</h3>
                    <a href="{{ route('admin.crm.client.export', ['client' => $client->id, 'year' => $year]) }}" class="text-xs bg-emerald-600 text-white font-bold py-1.5 px-3 rounded shadow-sm transition flex items-center">
                        <i class="fas fa-file-excel mr-1.5"></i> Export
                    </a>
                </div>
                <form action="{{ route('admin.crm.show', $client->id) }}" method="GET">
                    <select name="year" onchange="this.form.submit()" class="text-sm bg-zinc-900 border-zinc-700 text-zinc-300 py-1.5 px-3 rounded-lg">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left min-w-[900px] text-zinc-300">
                    <thead class="bg-zinc-900/50 text-zinc-400 uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Month</th>
                            <th class="px-6 py-3 text-right">Sales (In)</th>
                            <th class="px-6 py-3 text-center">Commission</th>
                            <th class="px-6 py-3 text-right text-blue-400 bg-blue-900/10">Value (Net)</th>
                            <th class="px-6 py-3 text-right text-red-400">Usage (Out)</th>
                            <th class="px-6 py-3 text-right text-zinc-200 border-l border-zinc-700">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-700">
                        <tr class="bg-amber-900/20">
                            <td colspan="5" class="px-6 py-3 font-bold text-amber-500 italic">{{ $startingLabel }}</td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-zinc-100 border-l border-zinc-700">{{ number_format($startingBalance, 0, ',', '.') }}</td>
                        </tr>
                        @foreach ($recap as $r)
                        <tr class="hover:bg-zinc-700/30 transition">
                            <td class="px-6 py-3 font-bold text-zinc-300">{{ $r['month_name'] }}</td>
                            <td class="px-6 py-3 text-right font-mono text-emerald-500">{{ $r['gross_in'] > 0 ? number_format($r['gross_in'], 0, ',', '.') : '-' }}</td>
                            <td class="px-6 py-3 text-center text-xs text-zinc-500">{{ $r['commission_text'] }}</td>
                            <td class="px-6 py-3 text-right font-mono font-bold bg-blue-900/10 text-blue-400">{{ $r['net_value'] > 0 ? number_format($r['net_value'], 0, ',', '.') : '-' }}</td>
                            <td class="px-6 py-3 text-right font-mono text-red-400">{{ $r['out'] > 0 ? number_format($r['out'], 0, ',', '.') : '-' }}</td>
                            <td class="px-6 py-3 text-right font-mono font-bold text-zinc-200 border-l border-zinc-700">{{ number_format($r['saldo'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-zinc-900 font-bold border-t-2 border-zinc-700 text-zinc-100">
                        <tr>
                            <td class="px-6 py-4 uppercase">Total</td>
                            <td class="px-6 py-4 text-right text-emerald-500">{{ number_format($yearlyTotals['gross_in'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">-</td>
                            <td class="px-6 py-4 text-right text-blue-400 bg-blue-900/20">{{ number_format($yearlyTotals['net_value'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-red-400">{{ number_format($yearlyTotals['out'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right border-l border-zinc-600">{{ number_format($yearlyTotals['saldo'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT KLIEN --}}
    <div id="editClientModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/20 backdrop-blur-md transition-opacity" onclick="toggleModal('editClientModal')"></div>
        <div class="flex items-center justify-center min-h-screen p-4 pointer-events-none">
            <div class="relative bg-zinc-900 w-full max-w-4xl rounded-xl shadow-2xl border border-zinc-700 pointer-events-auto max-h-[90vh] overflow-y-auto">
                <div class="bg-zinc-800 px-6 py-4 border-b border-zinc-700 flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-white font-bold text-lg"><i class="fas fa-edit mr-2 text-amber-500"></i> Edit Client Details</h3>
                    <button onclick="toggleModal('editClientModal')" class="text-zinc-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.crm.client.update', $client->id) }}" method="POST">
                        @csrf 
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Col 1: Personal --}}
                            <div class="space-y-4">
                                <h4 class="text-blue-400 text-xs font-bold uppercase pb-2 border-b border-zinc-700">Personal Profile</h4>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 mb-1">Assigned Sales (PIC) <span class="text-red-500">*</span></label>
                                    <select name="user_id" required class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm">
                                        <option value="">-- Select Assigned Sales --</option>
                                        @foreach(($users ?? []) as $u)
                                            <option value="{{ $u->id }}" @selected(old('user_id', $client->user_id) == $u->id)>{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-3 bg-zinc-800/40 p-2.5 rounded border border-zinc-700/50">
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 mb-1">Handover Date (jika ganti PIC)</label>
                                        <input type="date" name="handover_date" value="{{ date('Y-m-d') }}" class="w-full bg-zinc-800 border-zinc-600 px-2 py-1.5 rounded text-white text-xs [color-scheme:dark]">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-zinc-400 mb-1">Catatan Handover</label>
                                        <input type="text" name="handover_notes" placeholder="Catatan pergantian PIC..." class="w-full bg-zinc-800 border-zinc-600 px-2 py-1.5 rounded text-white text-xs">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 mb-1">Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="client_name" value="{{ old('client_name', $client->client_name) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" required>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-400 mb-1">Position</label>
                                        <input type="text" name="position" value="{{ old('position', $client->position) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-400 mb-1">Date of Birth</label>
                                        <input type="date" name="contact_birth_date" value="{{ old('birthdate', $client->birthdate ? \Carbon\Carbon::parse($client->birthdate)->format('Y-m-d') : '') }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm [color-scheme:dark]">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 mb-1">Personal Contact</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <input type="text" name="contact_phone" value="{{ old('phone', $client->phone) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="Phone / WA (08...)">
                                        </div>
                                        <div>
                                            <input type="email" name="email" value="{{ old('email', $client->email) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="Email address">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 mb-1">Hobby / Interests</label>
                                    <input type="text" name="contact_hobby" value="{{ old('hobby', $client->hobby) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="e.g. Golf, Culinary">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 mb-1">Home Address</label>
                                    <textarea name="contact_address" rows="2" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm resize-none">{{ old('address', $client->address) }}</textarea>
                                </div>
                            </div>

                            {{-- Col 2: Instansi & Keuangan --}}
                            <div class="space-y-4">
                                <h4 class="text-amber-500 text-xs font-bold uppercase pb-2 border-b border-zinc-700">Institution & Financials</h4>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 mb-1">Institution Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="customer_name" value="{{ old('customer_name', $client->customer?->customer_name ?? '') }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 mb-1">Institution Details</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <input type="text" name="area" value="{{ old('area', $client->customer?->area ?? '') }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="Area (e.g. South Jakarta)">
                                        </div>
                                        <div>
                                            <select name="customer_type" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm">
                                                <option value="">— Select Type —</option>
                                                @foreach(\App\Models\Customer::TYPES as $t)
                                                    <option value="{{ $t }}" @selected(old('customer_type', $client->customer?->customer_type) == $t)>{{ $t }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-zinc-400 mb-1">Anniversary Date</label>
                                            <input type="date" name="anniversary_date" value="{{ old('anniversary_date', $client->customer?->anniversary_date?->format('Y-m-d')) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm [color-scheme:dark]">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-zinc-400 mb-1">Office Phone</label>
                                            <input type="text" name="company_phone" value="{{ old('company_phone', $client->customer?->phone ?? '') }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-zinc-400 mb-1">Office Email</label>
                                            <input type="email" name="company_email" value="{{ old('company_email', $client->customer?->email ?? '') }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-zinc-400 mb-1">Tax ID (NPWP)</label>
                                            <input type="text" name="npwp" value="{{ old('npwp', $client->customer?->npwp ?? '') }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 mb-1">Office Address</label>
                                    <textarea name="company_address" rows="2" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm resize-none">{{ old('company_address', $client->customer?->displayAddress() ?? '') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 mb-1">Name in Sales / Command Center</label>
                                    <input type="text" name="sales_customer_name" value="{{ old('sales_customer_name', $client->customer?->sales_customer_name ?? '') }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="Sales institution name (optional)">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-amber-500/80 mb-1 uppercase">Pharmacist Information</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="col-span-2">
                                            <input type="text" name="pharmacist_name" value="{{ old('pharmacist_name', $client->customer?->pharmacist_name ?? '') }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="Pharmacist Name">
                                        </div>
                                        <div>
                                            <input type="text" name="pharmacist_license_no" value="{{ old('pharmacist_license_no', $client->pharmacist_license_no) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="SIPA License No.">
                                        </div>
                                        <div>
                                            <input type="text" name="pharmacist_phone" value="{{ old('pharmacist_phone', $client->pharmacist_phone) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="Pharmacist Phone">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-400 mb-1">Banking Information</label>
                                    <div class="space-y-3">
                                        <div>
                                            <input type="text" name="bank_name" value="{{ old('bank_name', $client->bank_name) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="e.g. Bank Mandiri / BCA">
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $client->bank_account_number) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm font-mono" placeholder="Account Number">
                                            </div>
                                            <div>
                                                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $client->bank_account_name) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="Beneficiary Name">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-zinc-400 mb-1 uppercase">Default Commission Rate (%)</label>
                                            <input type="number" step="0.01" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', $client->commission_rate) }}" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm" placeholder="e.g. 2.5">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="block text-[10px] font-bold text-emerald-500 uppercase">Opening Balance</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-zinc-500 text-xs">Rp</span>
                                        <input type="number" name="opening_balance" value="{{ old('opening_balance', $client->opening_balance) }}" class="w-full pl-8 bg-zinc-900 border-zinc-600 px-3 py-2 rounded text-white text-sm font-mono focus:border-emerald-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-6 border-t border-zinc-700 mt-4">
                            <button type="button" onclick="toggleModal('editClientModal')" class="px-4 py-2 bg-zinc-700 text-white font-bold rounded text-sm transition">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-amber-600 text-white font-bold rounded text-sm transition">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL HANDOVER PIC (ADMIN) --}}
    <div id="handoverPicModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="toggleModal('handoverPicModal')"></div>
        <div class="flex items-center justify-center min-h-screen p-4 pointer-events-none">
            <div class="relative bg-zinc-900 w-full max-w-lg rounded-xl shadow-2xl border border-zinc-700 pointer-events-auto max-h-[90vh] overflow-hidden flex flex-col">
                <div class="bg-zinc-800 px-6 py-4 border-b border-zinc-700 flex justify-between items-center shrink-0">
                    <h3 class="text-white font-bold text-base flex items-center">
                        <i class="fas fa-exchange-alt mr-2 text-indigo-400"></i> Serah Terima PIC (Admin)
                    </h3>
                    <button onclick="toggleModal('handoverPicModal')" class="text-zinc-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
                </div>
                <form action="{{ route('admin.crm.client.handover', $client->id) }}" method="POST" class="flex flex-col flex-grow overflow-hidden">
                    @csrf
                    <div class="p-6 space-y-4 overflow-y-auto flex-grow text-xs">
                        <div class="bg-zinc-800/80 border border-zinc-700 rounded-lg p-3">
                            <span class="text-zinc-500 font-bold block uppercase text-[10px]">PIC Aktif Saat Ini</span>
                            <span class="text-amber-400 font-extrabold text-sm block mt-0.5">{{ $client->ps ?: ($client->user->name ?? '-') }}</span>
                            <span class="text-zinc-400 text-[10px] block mt-1">Klien: {{ $client->client_name }} ({{ $client->customer?->customer_name ?? '-' }})</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-300 mb-1">Pilih PIC Baru <span class="text-red-500">*</span></label>
                            <select name="new_user_id" required class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm">
                                <option value="">-- Pilih Karyawan Marketing &amp; Operasional --</option>
                                @foreach(($availableUsers ?? $users ?? []) as $u)
                                    <option value="{{ $u->id }}" @selected(old('new_user_id') == $u->id)>{{ $u->name }} ({{ $u->divisi ?: 'Umum' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-300 mb-1">Tanggal Efektif Serah Terima <span class="text-red-500">*</span></label>
                            <input type="date" name="handover_date" value="{{ old('handover_date', date('Y-m-d')) }}" required class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-sm [color-scheme:dark]">
                            <span class="text-[10px] text-zinc-500 mt-1 block">Transaksi sales sebelum tanggal ini tetap milik PIC lama.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-300 mb-1">Catatan / Alasan Handover</label>
                            <textarea name="handover_notes" rows="2" class="w-full bg-zinc-800 border-zinc-600 px-3 py-2 rounded text-white text-xs resize-none" placeholder="Catatan serah terima tugas...">{{ old('handover_notes') }}</textarea>
                        </div>

                        <div class="bg-zinc-800/50 border border-zinc-700 rounded-lg p-3">
                            <h5 class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 mb-2">Riwayat Pergantian PIC</h5>
                            <div class="space-y-1.5 max-h-32 overflow-y-auto">
                                @forelse($client->picHistories ?? [] as $h)
                                    <div class="flex items-center justify-between text-[11px] p-1.5 rounded bg-zinc-800 border border-zinc-700/50">
                                        <span class="font-bold {{ is_null($h->end_date) ? 'text-emerald-400' : 'text-zinc-300' }}">
                                            {{ $h->pic_name }} {{ is_null($h->end_date) ? '(Aktif)' : '' }}
                                        </span>
                                        <span class="text-zinc-500 font-mono text-[10px]">
                                            {{ $h->start_date ? $h->start_date->format('d/m/Y') : '-' }} &rarr; {{ $h->end_date ? $h->end_date->format('d/m/Y') : 'Sekarang' }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-zinc-500 text-center py-1">Belum ada riwayat tercatat.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 p-4 bg-zinc-800/50 border-t border-zinc-700 shrink-0">
                        <button type="button" onclick="toggleModal('handoverPicModal')" class="px-4 py-2 bg-zinc-700 hover:bg-zinc-600 text-white font-bold rounded text-xs transition">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded text-xs transition">Simpan Serah Terima</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT TRANSAKSI --}}
    <div id="editTransactionModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/20 backdrop-blur-md transition-opacity" onclick="toggleModal('editTransactionModal')"></div>
        <div class="flex items-center justify-center min-h-screen p-4 pointer-events-none">
            <div class="relative bg-zinc-900 w-full max-w-3xl rounded-xl shadow-2xl border border-zinc-700 pointer-events-auto">
                <div id="editTransHeader" class="bg-zinc-800 px-6 py-4 border-b border-zinc-700 flex justify-between items-center rounded-t-xl">
                    <h3 class="text-white font-bold text-lg flex items-center">
                        <i class="fas fa-edit mr-3"></i> <span id="modalTitle">Edit Transaction</span>
                    </h3>
                    <button onclick="toggleModal('editTransactionModal')" class="text-zinc-400 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <form id="formEditTransaction" action="#" method="POST" class="p-6">
                    @csrf 
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-zinc-400 mb-1 uppercase">Date</label>
                                <input type="date" name="interaction_date" id="edit_tanggal" class="w-full bg-zinc-800 border-2 border-zinc-600 rounded-lg text-white px-3 py-2 text-sm [color-scheme:dark]" required>
                            </div>
                            <div id="wrapper_produk">
                                <label class="block text-xs font-bold text-zinc-400 mb-1 uppercase" id="label_produk">Product Name</label>
                                <input type="text" name="" id="edit_produk" class="w-full bg-zinc-800 border-2 border-zinc-600 rounded-lg text-white px-3 py-2 text-sm font-bold">
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-zinc-400 mb-1 uppercase">Amount</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-zinc-500 text-sm">Rp</span>
                                    <input type="text" name="" id="edit_nominal" onkeyup="formatRupiah(this)" class="w-full pl-9 bg-zinc-800 border-2 border-zinc-600 rounded-lg text-white px-3 py-2 font-mono font-bold text-lg" required>
                                </div>
                            </div>
                            <div id="wrapper_komisi" class="hidden">
                                <label class="block text-xs font-bold text-zinc-400 mb-1 uppercase">Commission (%)</label>
                                <div class="relative">
                                    <input type="number" name="commission_rate" id="edit_komisi" step="0.1" max="100" class="w-full bg-zinc-800 border-2 border-zinc-600 rounded-lg text-white px-3 py-2 text-sm" placeholder="0">
                                    <span class="absolute right-4 top-2 text-zinc-500 text-sm font-bold">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-zinc-600 mb-1 uppercase">Notes</label>
                                <textarea name="notes" id="edit_catatan" rows="3" class="w-full bg-zinc-800 border-2 border-zinc-600 rounded-lg text-white px-3 py-2 text-sm resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="pt-6 mt-2 flex justify-end gap-3 border-t border-zinc-700">
                        <button type="button" onclick="toggleModal('editTransactionModal')" class="px-4 py-2 bg-zinc-700 text-white font-bold rounded-lg text-sm transition">Cancel</button>
                        <button type="submit" id="btnUpdateTrans" class="px-4 py-2 bg-amber-600 text-white font-bold rounded-lg shadow-md flex items-center text-sm transition">
                            <i class="fas fa-save mr-2"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleModal(id) {
            const modal = document.getElementById(id);
            if (modal.classList.contains('hidden')) { 
                modal.classList.remove('hidden'); 
                document.body.style.overflow = 'hidden'; 
            } else { 
                modal.classList.add('hidden'); 
                document.body.style.overflow = 'auto'; 
            }
        }

        function openEditTransactionModal(data) {
            let baseUrl = "{{ url('admin/crm/interaction') }}"; 
            let form = document.getElementById('formEditTransaction');
            form.action = baseUrl + "/" + data.id + "/update"; 
            document.getElementById('edit_tanggal').value = data.tanggal; 
            document.getElementById('edit_catatan').value = data.catatan;
            let nominalVal = parseInt(data.nominal).toLocaleString('id-ID');
            document.getElementById('edit_nominal').value = nominalVal;
            let title = document.getElementById('modalTitle');
            let btn = document.getElementById('btnUpdateTrans');
            let wrapperProduk = document.getElementById('wrapper_produk'); 
            let labelProduk = document.getElementById('label_produk');
            let inputProduk = document.getElementById('edit_produk');
            let inputNominal = document.getElementById('edit_nominal');
            let wrapperKomisi = document.getElementById('wrapper_komisi');
            let inputKomisi = document.getElementById('edit_komisi');

            wrapperProduk.classList.remove('hidden');
            wrapperKomisi.classList.add('hidden'); 
            inputKomisi.removeAttribute('required'); 
            inputProduk.setAttribute('required', 'required');

            if (data.jenis === 'IN') {
                styleModal('emerald', 'Edit Sales Entry'); 
                labelProduk.innerText = "Product Name";
                inputProduk.name = "product_name"; 
                inputProduk.value = data.produk; 
                inputNominal.name = "sales_amount"; 
                wrapperKomisi.classList.remove('hidden'); 
                inputKomisi.value = data.rate || 0; 
                inputKomisi.setAttribute('required', 'required');
            } else if (data.jenis === 'OUT') {
                styleModal('red', 'Edit Usage Entry'); 
                labelProduk.innerText = "Purpose / Description";
                inputProduk.name = "purpose"; 
                inputProduk.value = data.produk.replace('USAGE : ', ''); 
                inputNominal.name = "amount"; 
            } else if (data.jenis === 'ENTERTAIN') {
                styleModal('purple', 'Edit Activity Entry'); 
                wrapperProduk.classList.add('hidden');
                inputProduk.removeAttribute('required'); 
                inputNominal.name = "amount"; 
            }

            function styleModal(color, textTitle) {
                title.innerText = textTitle; 
                inputNominal.className = `w-full pl-9 bg-zinc-800 border-2 border-zinc-600 rounded-lg text-white focus:border-${color}-500 px-3 py-2 font-mono font-bold text-lg`;
                btn.className = `px-4 py-2 bg-${color}-600 hover:bg-${color}-700 text-white font-bold rounded-lg shadow-md transition`;
            }
            toggleModal('editTransactionModal');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editClientModal'); 
            const modalTrans = document.getElementById('editTransactionModal');
            const modalHandover = document.getElementById('handoverPicModal');
            if (event.target == modal) toggleModal('editClientModal'); 
            if (event.target == modalTrans) toggleModal('editTransactionModal');
            if (event.target == modalHandover) toggleModal('handoverPicModal');
        }

        function switchTab(tabName) {
            const sections = ['history', 'recap', 'sales', 'support', 'activity'];
            sections.forEach(sec => { 
                const el = document.getElementById('section-' + sec); 
                if(el) el.classList.add('hidden'); 
            });
            const defaultStyleLeft = "px-6 py-2 rounded-md text-sm font-bold text-zinc-500 hover:text-zinc-300 transition-all flex-1 md:flex-none";
            document.getElementById('btn-history').className = defaultStyleLeft; 
            document.getElementById('btn-recap').className = defaultStyleLeft;
            const defaultStyleRight = "px-4 py-2 rounded-md text-sm font-bold text-zinc-500 hover:text-white transition-all whitespace-nowrap";
            document.getElementById('btn-sales').className = defaultStyleRight.replace('hover:text-white', 'hover:text-emerald-400');
            document.getElementById('btn-support').className = defaultStyleRight.replace('hover:text-white', 'hover:text-red-400');
            document.getElementById('btn-activity').className = defaultStyleRight.replace('hover:text-white', 'hover:text-purple-400');
            
            const targetSection = document.getElementById('section-' + tabName); 
            if(targetSection) targetSection.classList.remove('hidden');
            const activeBtn = document.getElementById('btn-' + tabName);
            if (tabName === 'history' || tabName === 'recap') {
                activeBtn.className = "px-6 py-2 rounded-md text-sm font-bold bg-zinc-700 text-amber-500 shadow-sm transition-all flex-1 md:flex-none";
            } else if (tabName === 'sales') {
                activeBtn.className = "px-4 py-2 rounded-md text-sm font-bold bg-emerald-900/30 text-emerald-400 border border-emerald-800 transition-all whitespace-nowrap";
            } else if (tabName === 'support') {
                activeBtn.className = "px-4 py-2 rounded-md text-sm font-bold bg-red-900/30 text-red-400 border border-red-800 transition-all whitespace-nowrap";
            } else if (tabName === 'activity') {
                activeBtn.className = "px-4 py-2 rounded-md text-sm font-bold bg-purple-900/30 text-purple-400 border border-purple-800 transition-all whitespace-nowrap";
            }
        }
        
        const formatRupiah = (input) => {
            let value = input.value.replace(/[^0-9]/g, '');
            if (value) value = parseInt(value, 10).toLocaleString('id-ID');
            input.value = value;
        };

        ['inputSales', 'inputSupport', 'inputEntertain'].forEach(id => {
            const el = document.getElementById(id); 
            if(el) el.addEventListener('keyup', function(e){ formatRupiah(this); });
        });

        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('year') || urlParams.get('tab') === 'recap') switchTab('recap');
        else if (urlParams.has('history_year') || urlParams.get('tab') === 'history') switchTab('history');
        else document.addEventListener("DOMContentLoaded", () => switchTab(localStorage.getItem('activeTab') || 'history'));
    </script>
</x-layout-admin>
