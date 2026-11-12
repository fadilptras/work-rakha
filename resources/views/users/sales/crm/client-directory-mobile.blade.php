<x-layout-users :title="'Client Directory'">

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

        /* Float animation */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float-delayed { animation: float 10s ease-in-out infinite; animation-delay: 2s; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16">
        <div class="mesh-bg"></div>
        
        <div class="relative z-10 w-full max-w-none mx-0 px-3 sm:px-4 py-3 space-y-3 flex-1 flex flex-col items-stretch justify-start pb-20">

            {{-- SECTION 1: HERO HEADER --}}
            <div class="rounded-2xl shadow-md border border-blue-700/30 overflow-hidden relative text-white" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="p-3.5 relative z-10">
                    <div class="flex flex-col gap-2.5">
                        {{-- Title & Subtitle --}}
                        <div class="text-white">
                            <h2 class="text-lg font-extrabold tracking-tight drop-shadow-sm leading-tight">
                                <i class="fas fa-address-book mr-1.5 text-white/80"></i> Client Directory
                            </h2>
                            <p class="text-white/90 text-[11px] leading-snug mt-0.5 font-medium">
                                Database of client relationships, operational areas, and transaction summaries.
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="grid grid-cols-2 gap-2">
                            <form action="{{ route('crm.index') }}" method="GET" class="m-0">
                                <div class="relative w-full">
                                    <select name="year" onchange="this.form.submit()" class="w-full bg-white/15 hover:bg-white/25 text-white font-bold border border-white/35 rounded-lg pl-3 pr-7 py-2 shadow outline-none cursor-pointer transition-colors appearance-none focus:ring-2 focus:ring-white/50 text-xs">
                                        <option value="all" class="text-gray-900" {{ isset($selectedYear) && $selectedYear == 'all' ? 'selected' : '' }}>All Years</option>
                                        @for($y = date('Y'); $y >= 2020; $y--)
                                             <option value="{{ $y }}" class="text-gray-900" {{ isset($selectedYear) && $selectedYear == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                                        @endfor
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-white">
                                        <i class="fas fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </form>
                            <button type="button" onclick="openCreateClientModalMobile()" class="w-full justify-center bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-2 rounded-lg shadow transition-colors flex items-center border border-orange-400 text-xs">
                                <i class="fas fa-plus mr-1.5"></i> Add Client
                            </button>
                        </div>
                    </div>

                    {{-- Mini Stats Cards --}}
                    <div class="grid grid-cols-1 gap-2 mt-2.5">
                        {{-- Stat 1: Total Clients --}}
                        <div class="bg-white/15 backdrop-blur-lg rounded-xl px-3 py-2 flex items-center justify-between gap-2 shadow-sm border border-white/25">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-white/25 flex items-center justify-center text-white text-xs shadow-inner shrink-0">
                                    <i class="fas fa-users"></i>
                                </div>
                                <p class="text-white/90 text-[10px] font-bold uppercase tracking-wider">Active Clients</p>
                            </div>
                            <p class="text-white text-sm font-extrabold leading-none drop-shadow-sm">{{ $clients->count() }}</p>
                        </div>

                        {{-- Stat 2: Total Usage --}}
                        <div class="bg-white/15 backdrop-blur-lg rounded-xl px-3 py-2 flex items-center justify-between gap-2 shadow-sm border border-white/25">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-purple-500/90 flex items-center justify-center text-white text-xs shadow-inner shrink-0">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <p class="text-white/90 text-[10px] font-bold uppercase tracking-wider">Total Usage</p>
                            </div>
                            <p class="text-white text-sm font-mono font-extrabold leading-none drop-shadow-sm">
                                Rp {{ number_format($totalUsage, 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Stat 3: Total Balance --}}
                        <div class="bg-white/15 backdrop-blur-lg rounded-xl px-3 py-2 flex items-center justify-between gap-2 shadow-sm border border-white/25">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/90 flex items-center justify-center text-white text-xs shadow-inner shrink-0">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <p class="text-white/90 text-[10px] font-bold uppercase tracking-wider">Total Balance</p>
                            </div>
                            <p class="text-white text-sm font-mono font-extrabold leading-none drop-shadow-sm">
                                Rp {{ number_format($totalAllBalance, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: DATA LIST --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden relative z-20">
                {{-- Header List & Search --}}
                <div class="px-5 pt-5 pb-2 flex flex-col gap-3 bg-white">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <i class="fas fa-list-ul text-blue-600"></i> Client Database
                    </h3>
                    
                    {{-- Search Bar --}}
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" id="searchInput" placeholder="Search client, institution, or area..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border-2 border-slate-200 text-xs focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white transition-all shadow-sm placeholder-slate-400 font-semibold text-slate-700">
                    </div>
                </div>

                {{-- Cards Container --}}
                <div class="px-4 pb-4 pt-2 space-y-3" id="clientCardContainer">
                    @forelse ($clients as $client)
                    <div class="client-card bg-blue-100/70 hover:bg-blue-200/60 rounded-2xl p-4 transition duration-200 flex flex-col gap-3">
                        {{-- Header Card --}}
                        <div class="flex justify-between items-start gap-3">
                            <div class="space-y-1">
                                <h4 class="font-extrabold text-gray-800 text-sm leading-snug">
                                    {{ $client->customer?->customer_name ?? '-' }}
                                </h4>
                                <div class="text-xs text-slate-500 font-semibold flex items-center gap-1.5">
                                    <i class="fas fa-user-md text-blue-500"></i>
                                    <span>{{ $client->client_name }}</span>
                                </div>
                            </div>
                            <a href="{{ route('crm.show', $client->id) }}" class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center transition shadow-md" title="View Details">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>

                        {{-- Metadata --}}
                        <div class="grid grid-cols-2 gap-4 pt-2.5 border-t border-slate-200/60 text-xs">
                            <div class="space-y-2">
                                <div>
                                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-1">Area</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200/50">
                                        {{ $client->customer?->area ?? 'No Area' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Account PIC</span>
                                    <span class="font-bold text-slate-700 text-[11px]">{{ $client->ps ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-1">Client Contact</span>
                                    @if($client->phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->phone) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 font-bold hover:underline">
                                            <i class="fab fa-whatsapp text-xs"></i> {{ $client->phone }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">No phone</span>
                                    @endif
                                </div>
                                @if($client->email)
                                <div>
                                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Email</span>
                                    <span class="text-slate-600 text-[11px] block truncate max-w-[130px]" title="{{ $client->email }}">{{ $client->email }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Footer Card --}}
                        <div class="flex justify-between items-center pt-2.5 border-t border-slate-200/60 bg-emerald-50/50 -mx-4 -mb-4 px-4 py-2.5 rounded-b-2xl border-t-0">
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1">
                                <i class="fas fa-wallet text-emerald-600"></i> Available Balance
                            </span>
                            <span class="font-mono font-extrabold text-emerald-700 text-sm">
                                Rp {{ number_format($client->current_balance, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-10 text-gray-400">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-folder-open text-xl opacity-50"></i>
                        </div>
                        <span class="font-medium text-xs">No client records found.</span>
                        <p class="text-[10px] mt-0.5">Add a new client to get started.</p>
                    </div>
                    @endforelse
                </div>

                {{-- Empty Search Result --}}
                <div id="noResult" class="hidden px-6 py-10 text-center text-gray-400">
                    <i class="fas fa-search text-xl opacity-50 mb-2"></i>
                    <p class="text-xs">No matching records found.</p>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL: ADD CLIENT (Mobile Tabbed using x-ui.modal) --}}
    <x-ui.modal
        id="createClientModal"
        maxWidth="max-w-md"
        title="Add New Client"
        subtitle="Lengkapi profil personal, instansi, dan finansial."
        icon="fa-user-plus"
        iconClass="bg-blue-600 text-white"
    >
        {{-- Tab Switcher --}}
        <div class="bg-slate-100 p-1 rounded-xl flex gap-1 -mt-1">
            <button type="button" onclick="switchModalTab('personal')" id="btn-tab-personal" class="modal-tab-btn flex-1 py-2 text-xs font-bold rounded-lg transition-all text-center bg-white text-blue-600 shadow-sm">
                Personal
            </button>
            <button type="button" onclick="switchModalTab('company')" id="btn-tab-company" class="modal-tab-btn flex-1 py-2 text-xs font-bold rounded-lg transition-all text-center text-slate-500 hover:text-slate-700">
                Company
            </button>
            <button type="button" onclick="switchModalTab('finance')" id="btn-tab-finance" class="modal-tab-btn flex-1 py-2 text-xs font-bold rounded-lg transition-all text-center text-slate-500 hover:text-slate-700">
                Finance
            </button>
        </div>

        <form id="createClientFormMobile" action="{{ route('crm.store') }}" method="POST" class="space-y-4">
            @csrf

            @if($errors->createClient->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs text-red-700">
                    <p class="font-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Periksa input berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->createClient->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 1. PERSONAL PROFILE --}}
            <div id="modal-sec-personal" class="modal-sec-content space-y-3">
                <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
                    <span class="text-xs font-extrabold text-blue-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-user-circle text-sm text-blue-600"></i> Personal Profile
                    </span>
                    <span class="text-[10px] text-slate-400 font-bold">Step 1 of 3</span>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Client Name <span class="text-red-500">*</span></label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Full client name">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Position / Title</label>
                    <input type="text" name="position" value="{{ old('position') }}" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Position (e.g. Department Head)">
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Phone (WA)</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="08xxx">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Email">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Date of Birth</label>
                        <input type="date" name="birthdate" value="{{ old('birthdate') }}" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Hobbies</label>
                        <input type="text" name="hobby" value="{{ old('hobby') }}" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="e.g. Golf">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Home Address</label>
                    <textarea name="address" rows="2" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 resize-none placeholder-slate-400" placeholder="Residential address...">{{ old('address') }}</textarea>
                </div>
            </div>

            {{-- 2. COMPANY PROFILE --}}
            <div id="modal-sec-company" class="modal-sec-content space-y-3 hidden">
                <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
                    <span class="text-xs font-extrabold text-orange-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-building text-sm text-orange-600"></i> Institution Profile
                    </span>
                    <span class="text-[10px] text-slate-400 font-bold">Step 2 of 3</span>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Institution / Hospital Name <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" required value="{{ old('customer_name') }}" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Institution or Hospital name">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Sales System Name (Command Center)</label>
                    <input type="text" name="sales_customer_name" value="{{ old('sales_customer_name') }}" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Name in Sales system (optional)">
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Area</label>
                        <input type="text" name="area" value="{{ old('area') }}" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Area">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Institution Type</label>
                        <select name="customer_type" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800">
                            <option value="">— Select Type —</option>
                            @foreach(\App\Models\Customer::TYPES as $t)
                                <option value="{{ $t }}" @selected(old('customer_type') == $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Office Phone</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone') }}" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Phone">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Anniversary Date</label>
                        <input type="date" name="anniversary_date" value="{{ old('anniversary_date') }}" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Tax ID / NPWP</label>
                        <input type="text" name="npwp" value="{{ old('npwp') }}" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Tax ID">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Office Email</label>
                        <input type="email" name="company_email" value="{{ old('company_email') }}" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Email">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Company Address</label>
                    <textarea name="company_address" rows="2" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 resize-none placeholder-slate-400" placeholder="Office location...">{{ old('company_address') }}</textarea>
                </div>
                <div class="border-t border-slate-100 pt-3">
                    <label class="block text-[11px] font-bold text-orange-700 mb-2 uppercase flex items-center gap-1.5"><i class="fas fa-user-md text-sm text-orange-600"></i> Pharmacist Information</label>
                    <div class="space-y-2">
                        <input type="text" name="pharmacist_name" value="{{ old('pharmacist_name') }}" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Pharmacist Name">
                        <div class="grid grid-cols-2 gap-2.5">
                            <input type="text" name="pharmacist_license_no" value="{{ old('pharmacist_license_no') }}" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="License No. (SIPA)">
                            <input type="text" name="pharmacist_phone" value="{{ old('pharmacist_phone') }}" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Phone">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. BANKING & FINANCIALS --}}
            <div id="modal-sec-finance" class="modal-sec-content space-y-3 hidden">
                <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
                    <span class="text-xs font-extrabold text-emerald-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-wallet text-sm text-emerald-600"></i> Banking & Financials
                    </span>
                    <span class="text-[10px] text-slate-400 font-bold">Step 3 of 3</span>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="w-full bg-white border border-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="e.g. BCA / Mandiri">
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Account Number</label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" class="w-full bg-white border border-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 text-xs px-3 py-2 rounded-xl font-mono font-bold text-slate-700 placeholder-slate-400" placeholder="Account number">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Account Holder (A/N)</label>
                        <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" class="w-full bg-white border border-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 text-xs px-3 py-2 rounded-xl font-bold text-slate-800 placeholder-slate-400" placeholder="Beneficiary name">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2.5 border-t border-slate-100 pt-3">
                    <div>
                        <label class="block text-[11px] font-bold text-emerald-800 mb-1 uppercase tracking-wider">Commission (%)</label>
                        <div class="flex rounded-xl overflow-hidden bg-white border border-slate-300 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-100">
                            <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate') }}" class="flex-grow min-w-0 border-0 focus:outline-none text-xs font-bold text-emerald-800 px-3 py-2 bg-transparent" placeholder="e.g. 2.5">
                            <span class="bg-slate-100 border-l border-slate-200 text-slate-600 font-bold text-xs px-2.5 flex items-center shrink-0">%</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-emerald-800 mb-1 uppercase tracking-wider">Opening Balance</label>
                        <div class="flex rounded-xl overflow-hidden bg-white border border-slate-300 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-100">
                            <span class="bg-slate-100 border-r border-slate-200 text-slate-600 font-bold text-[11px] px-2.5 flex items-center shrink-0">Rp</span>
                            <input type="number" name="opening_balance" value="{{ old('opening_balance') }}" class="flex-grow min-w-0 border-0 focus:outline-none text-xs font-bold text-emerald-800 px-3 py-2 bg-transparent" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <div class="flex justify-between w-full gap-2">
                <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>Cancel</x-ui.button>
                <div class="flex gap-2">
                    <x-ui.button variant="secondary" size="sm" type="button" id="modal-prev-btn" onclick="navigateModalTab(-1)" style="display:none;">Previous</x-ui.button>
                    <x-ui.button variant="primary" size="sm" type="button" id="modal-next-btn" onclick="navigateModalTab(1)">Next</x-ui.button>
                    <x-ui.button variant="primary" size="sm" type="submit" form="createClientFormMobile" id="modal-submit-btn" icon="fa-save" style="display:none;">Save Client</x-ui.button>
                </div>
            </div>
        </x-slot:footer>
    </x-ui.modal>

    @push('scripts')
    <script>
        // Tab system for Add Client Modal
        let activeModalTab = 'personal';
        const modalTabOrder = ['personal', 'company', 'finance'];

        function switchModalTab(tabId) {
            const currentIndex = modalTabOrder.indexOf(activeModalTab);
            const targetIndex = modalTabOrder.indexOf(tabId);
            const secMap = { 'personal': 'modal-sec-personal', 'company': 'modal-sec-company', 'finance': 'modal-sec-finance' };

            if (targetIndex > currentIndex) {
                for (let idx = currentIndex; idx < targetIndex; idx++) {
                    const secEl = document.getElementById(secMap[modalTabOrder[idx]]);
                    const requiredInputs = secEl.querySelectorAll('[required]');
                    for (let input of requiredInputs) {
                        if (!input.checkValidity()) {
                            if (idx !== currentIndex) {
                                switchModalTab(modalTabOrder[idx]);
                            }
                            input.reportValidity();
                            return;
                        }
                    }
                }
            }

            activeModalTab = tabId;
            document.querySelectorAll('.modal-sec-content').forEach(el => el.classList.add('hidden'));
            document.getElementById(secMap[tabId]).classList.remove('hidden');
            
            document.querySelectorAll('.modal-tab-btn').forEach(btn => {
                btn.className = "modal-tab-btn flex-1 py-2 text-xs font-bold rounded-lg transition-all text-center text-slate-500 hover:text-slate-700";
            });
            
            let activeColorClass = "bg-white text-blue-600 shadow-sm";
            if (tabId === 'company') activeColorClass = "bg-white text-orange-600 shadow-sm";
            else if (tabId === 'finance') activeColorClass = "bg-white text-emerald-600 shadow-sm";
            
            document.getElementById('btn-tab-' + tabId).className = "modal-tab-btn flex-1 py-2 text-xs font-bold rounded-lg transition-all text-center " + activeColorClass;
            
            const prevBtn = document.getElementById('modal-prev-btn');
            const nextBtn = document.getElementById('modal-next-btn');
            const submitBtn = document.getElementById('modal-submit-btn');
            
            prevBtn.style.display = targetIndex === 0 ? 'none' : '';
            nextBtn.style.display = targetIndex === modalTabOrder.length - 1 ? 'none' : '';
            submitBtn.style.display = targetIndex === modalTabOrder.length - 1 ? '' : 'none';
        }

        function navigateModalTab(dir) {
            const currentIndex = modalTabOrder.indexOf(activeModalTab);
            const targetIndex = currentIndex + dir;
            if (targetIndex >= 0 && targetIndex < modalTabOrder.length) {
                switchModalTab(modalTabOrder[targetIndex]);
            }
        }

        function openCreateClientModalMobile() {
            activeModalTab = 'personal';
            switchModalTab('personal');
            uiModalOpen('createClientModal');
        }

        @if($errors->createClient->any())
        document.addEventListener('DOMContentLoaded', function() {
            openCreateClientModalMobile();
        });
        @endif

        // Live search cards for mobile
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let cards = document.querySelectorAll('.client-card');
            let hasResult = false;

            cards.forEach(card => {
                let text = card.innerText.toLowerCase();
                if(text.includes(filter)) {
                    card.classList.remove('hidden');
                    hasResult = true;
                } else {
                    card.classList.add('hidden');
                }
            });

            const noResultDiv = document.getElementById('noResult');
            if (!hasResult && cards.length > 0) {
                noResultDiv.classList.remove('hidden');
            } else {
                noResultDiv.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-layout-users>