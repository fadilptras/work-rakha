<x-layout-users :title="'Client Directory'">

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

        /* Zebra striping baris tabel agar perbedaan tiap baris jelas */
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

            {{-- SECTION 1: HERO HEADER --}}
            <div class="rounded-2xl shadow-lg border border-blue-500/20 overflow-hidden relative text-white" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="px-5 py-3.5 md:px-6 md:py-4 relative z-10">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 md:gap-6">
                        
                        {{-- Title & Subtitle --}}
                        <div>
                            <h2 class="text-xl md:text-2xl font-extrabold tracking-tight drop-shadow-sm text-white leading-tight">
                                Client Directory
                            </h2>
                            <p class="text-white/90 text-xs mt-0.5 max-w-xl leading-snug font-medium">
                                Database of client relationships, operational areas, account representatives, and transaction summaries.
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex flex-wrap md:flex-nowrap justify-start md:justify-end gap-2 items-center w-full md:w-auto">
                            <form action="{{ route('crm.index') }}" method="GET" class="m-0 w-full sm:w-auto">
                                <div class="relative w-full sm:w-auto">
                                    <select name="year" onchange="this.form.submit()" class="w-full sm:w-auto bg-white/15 hover:bg-white/25 text-white font-bold border border-white/35 rounded-xl pl-3.5 pr-9 py-2 shadow-sm outline-none cursor-pointer transition-colors appearance-none focus:ring-2 focus:ring-white/50 text-xs sm:text-sm">
                                        <option value="all" class="text-gray-900" {{ isset($selectedYear) && $selectedYear == 'all' ? 'selected' : '' }}>All Years</option>
                                        @for($y = date('Y'); $y >= 2020; $y--)
                                            <option value="{{ $y }}" class="text-gray-900" {{ isset($selectedYear) && $selectedYear == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                                        @endfor
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-white">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </form>
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button type="button" onclick="uiModalOpen('createClientModal')" class="flex-1 sm:flex-none justify-center bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-2 px-4 rounded-xl shadow-md shadow-orange-900/20 transition-colors flex items-center border border-orange-400 text-xs sm:text-sm">
                                    <i class="fas fa-plus mr-2"></i> Add Client
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Mini Stats Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                        {{-- Stat 1: Total Active Clients --}}
                        <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-xl px-3.5 py-2 flex items-center gap-3 shadow-sm">
                            <div class="w-8 h-8 rounded-lg bg-white/25 flex items-center justify-center text-white text-sm shrink-0">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-white/90 text-[10px] font-bold uppercase tracking-wider leading-tight">Active Clients</p>
                                <p class="text-white text-lg md:text-xl font-extrabold leading-tight drop-shadow-sm">{{ $clients->count() }}</p>
                            </div>
                        </div>

                        {{-- Stat 2: Total Usage --}}
                        <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-xl px-3.5 py-2 flex items-center gap-3 shadow-sm">
                            <div class="w-8 h-8 rounded-lg bg-purple-500/90 flex items-center justify-center text-white text-sm shadow-sm shrink-0">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-white/90 text-[10px] font-bold uppercase tracking-wider leading-tight">Total Usage</p>
                                <p class="text-white text-lg md:text-xl font-mono font-extrabold leading-tight drop-shadow-sm">
                                    Rp {{ number_format($totalUsage, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        {{-- Stat 3: Total Balance --}}
                        <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-xl px-3.5 py-2 flex items-center gap-3 shadow-sm">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/90 flex items-center justify-center text-white text-sm shadow-sm shrink-0">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-white/90 text-[10px] font-bold uppercase tracking-wider leading-tight">Total Balance</p>
                                <p class="text-white text-lg md:text-xl font-mono font-extrabold leading-tight drop-shadow-sm">
                                    Rp {{ number_format($totalAllBalance, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: DATA LIST --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/90 overflow-hidden relative z-20">
                {{-- Table Header & Search --}}
                <div class="px-5 py-3 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-3 bg-white">
                    <h3 class="font-bold text-slate-800 text-base md:text-lg flex items-center gap-2">
                        <i class="fas fa-list-ul text-blue-600"></i> Client Database
                    </h3>
                    
                    {{-- Search Input --}}
                    <div class="relative w-full md:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text" id="searchInput" placeholder="Search client, institution, or area..." class="w-full pl-9 pr-4 py-1.5 rounded-lg border border-slate-200 text-sm focus:ring-blue-500 focus:border-blue-500 bg-slate-50 transition-colors">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="clientTable">
                        <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[11px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-2.5">Institution & Client</th>
                                <th class="px-5 py-2.5">Area & PIC</th>
                                <th class="px-5 py-2.5">Contact</th>
                                <th class="px-5 py-2.5 text-right">Available Balance</th>
                                <th class="px-5 py-2.5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/80" id="clientTableBody">
                            @forelse ($clients as $client)
                            <tr class="group transition duration-150 {{ $loop->odd ? 'crm-row-odd' : 'crm-row-even' }}" data-client-row>
                                {{-- Column 1: Profile --}}
                                <td class="px-5 py-2 align-middle">
                                    <div class="flex flex-col">
                                        <div class="font-bold text-gray-800 text-sm group-hover:text-blue-700 transition leading-tight">
                                            {{ $client->customer?->customer_name ?? '-' }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-medium flex items-center gap-1 leading-tight mt-0.5">
                                            <i class="fas fa-user-md text-blue-400"></i> {{ $client->client_name }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Column 2: Area & PIC --}}
                                <td class="px-5 py-2 align-middle">
                                    <div class="flex flex-col items-start gap-0.5">
                                        <span class="inline-flex items-center px-2 py-0 rounded text-[11px] font-medium bg-gray-100 text-gray-800 border border-gray-200 leading-4">
                                            {{ $client->customer?->area ?? 'No Area' }}
                                        </span>
                                        <span class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold leading-tight">
                                            PIC: {{ $client->ps ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Column 3: Contact --}}
                                <td class="px-5 py-2 align-middle">
                                    <div class="space-y-0.5">
                                        @if($client->phone)
                                            <div class="flex items-center gap-1.5 text-xs text-gray-600 leading-tight">
                                                <i class="fab fa-whatsapp text-emerald-500 w-4 text-center"></i> 
                                                <span>{{ $client->phone }}</span>
                                            </div>
                                        @endif
                                        @if($client->email)
                                            <div class="flex items-center gap-1.5 text-xs text-gray-600 leading-tight">
                                                <i class="fas fa-envelope text-blue-400 w-4 text-center"></i> 
                                                <span>{{ \Illuminate\Support\Str::limit($client->email, 25) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Column 4: Available Balance --}}
                                <td class="px-5 py-2 align-middle text-right">
                                    <span class="font-mono font-bold text-emerald-600 text-sm leading-tight">
                                        Rp {{ number_format($client->current_balance, 0, ',', '.') }}
                                    </span>
                                </td>

                                {{-- Column 5: Actions --}}
                                <td class="px-5 py-2 align-middle text-center">
                                    <a href="{{ route('crm.show', $client->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition shadow-sm group-hover:border-blue-200" title="View Details">
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center bg-gray-50/30">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-folder-open text-2xl opacity-50"></i>
                                        </div>
                                        <span class="font-medium">No client records found.</span>
                                        <p class="text-xs mt-1">Add a new client to get started.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    {{-- Empty Search State --}}
                    <div id="noResult" class="hidden px-6 py-12 text-center bg-gray-50/30 text-gray-400">
                        <i class="fas fa-search text-2xl opacity-50 mb-2"></i>
                        <p>No matching records found.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL: ADD NEW CLIENT --}}
    <x-ui.modal
        id="createClientModal"
        maxWidth="max-w-6xl"
        title="Add New Client"
        subtitle="Lengkapi profil personal, profil instansi, dan informasi rekening/komisi."
        icon="fa-user-plus"
        iconClass="bg-blue-600 text-white"
    >
        <form id="createClientForm" action="{{ route('crm.store') }}" method="POST">
            @csrf

            @if($errors->createClient->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">
                    <p class="font-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Periksa kembali input berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->createClient->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 3-COLUMN GRID --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">

                {{-- COLUMN 1: PERSONAL INFO --}}
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full">
                    <div class="bg-blue-50/80 px-4 py-2.5 border-b border-blue-200 flex items-center">
                        <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">1</span>
                        <h4 class="text-blue-800 text-xs font-bold uppercase tracking-wider">Personal Profile</h4>
                    </div>
                    <div class="p-4 space-y-3 flex-grow">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase">Client Name & Position <span class="text-red-500">*</span></label>
                            <div class="space-y-2">
                                <input type="text" name="client_name" value="{{ old('client_name') }}" required class="w-full border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm px-3 py-2 font-bold" placeholder="Full client name">
                                <input type="text" name="position" value="{{ old('position') }}" class="w-full border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-xs px-3 py-2" placeholder="Position / Role (e.g. Department Head)">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase">Personal Contact</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2" placeholder="Phone / WhatsApp">
                                <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2" placeholder="Email address">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase">Date of Birth</label>
                                <input type="date" name="birthdate" value="{{ old('birthdate') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase">Hobbies / Interests</label>
                                <input type="text" name="hobby" value="{{ old('hobby') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2" placeholder="e.g. Golf, Reading">
                            </div>
                        </div>

                        <div class="flex-grow">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase">Home Address</label>
                            <textarea name="address" rows="2" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2 resize-none" placeholder="Residential address...">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- COLUMN 2: COMPANY INFO --}}
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full">
                    <div class="bg-orange-50/80 px-4 py-2.5 border-b border-orange-200 flex items-center">
                        <span class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">2</span>
                        <h4 class="text-orange-800 text-xs font-bold uppercase tracking-wider">Company Profile</h4>
                    </div>
                    <div class="p-4 space-y-3 flex-grow">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase">Institution / Hospital Name <span class="text-red-500">*</span></label>
                            <input type="text" name="customer_name" required value="{{ old('customer_name') }}" class="w-full border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm px-3 py-2 font-semibold" placeholder="Institution / Hospital name">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1 uppercase">Sales System Name (Command Center)</label>
                            <input type="text" name="sales_customer_name" value="{{ old('sales_customer_name') }}" class="w-full border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 text-sm px-3 py-2 font-semibold" placeholder="Name in Sales system (optional, if different)">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase">Institution Details</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="area" value="{{ old('area') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2" placeholder="Area (e.g. South Jakarta)">
                                <select name="customer_type" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2 text-slate-700">
                                    <option value="">— Select Type —</option>
                                    @foreach(\App\Models\Customer::TYPES as $t)
                                        <option value="{{ $t }}" @selected(old('customer_type') == $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="company_phone" value="{{ old('company_phone') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2" placeholder="Office Phone">
                                <input type="email" name="company_email" value="{{ old('company_email') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2" placeholder="Office Email">
                                <input type="date" name="anniversary_date" value="{{ old('anniversary_date') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2" title="Institution Anniversary">
                                <input type="text" name="npwp" value="{{ old('npwp') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2" placeholder="Tax ID / NPWP (optional)">
                            </div>
                        </div>
                        <div class="flex-grow">
                            <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase">Company Address</label>
                            <textarea name="company_address" rows="2" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2 resize-none" placeholder="Office / facility address...">{{ old('company_address') }}</textarea>
                        </div>
                        <div class="mt-2 pt-2 border-t border-orange-100">
                            <label class="block text-[11px] font-bold text-orange-700 mb-1 uppercase">Pharmacist Information</label>
                            <div class="space-y-2">
                                <input type="text" name="pharmacist_name" value="{{ old('pharmacist_name') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-1.5" placeholder="Pharmacist Name">
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="pharmacist_license_no" value="{{ old('pharmacist_license_no') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-1.5" placeholder="License No. (SIPA)">
                                    <input type="text" name="pharmacist_phone" value="{{ old('pharmacist_phone') }}" class="w-full border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-1.5" placeholder="Pharmacist Phone">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- COLUMN 3: BANKING & FINANCIALS --}}
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full">
                    <div class="bg-emerald-50/80 px-4 py-2.5 border-b border-emerald-200 flex items-center">
                        <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">3</span>
                        <h4 class="text-emerald-800 text-xs font-bold uppercase tracking-wider">Banking & Financials</h4>
                    </div>
                    <div class="p-4 space-y-3 flex-grow">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase">Bank Name</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 px-3 py-2" placeholder="e.g. BCA / Mandiri">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase">Account Number</label>
                            <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 px-3 py-2 font-mono" placeholder="Account number">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase">Account Holder (A/N)</label>
                            <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" class="w-full border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 px-3 py-2" placeholder="Beneficiary name">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-emerald-700 mb-1 uppercase">Commission Rate (%)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate') }}" class="w-full border border-emerald-200 bg-emerald-50/40 rounded-lg text-sm font-bold text-emerald-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 px-3 py-1.5" placeholder="e.g. 2.5">
                                <span class="absolute right-3 top-2 text-emerald-600 font-bold text-xs">%</span>
                            </div>
                        </div>
                        
                        <div class="mt-auto pt-3 border-t border-emerald-100">
                            <label class="block text-[11px] font-bold text-emerald-700 mb-1 uppercase">Opening Balance</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-emerald-600 font-bold text-xs">Rp</span>
                                <input type="number" name="opening_balance" value="{{ old('opening_balance') }}" class="w-full pl-8 border border-emerald-200 bg-emerald-50/40 rounded-lg text-lg font-bold text-emerald-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 px-3 py-1.5" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="secondary" size="sm" type="button" data-modal-close>
                Cancel
            </x-ui.button>
            <x-ui.button variant="primary" size="sm" type="submit" form="createClientForm" icon="fa-save">
                Save Client
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    @push('scripts')
    <script>
        // Real-time Search filter dengan zebra striping dinamis
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#clientTableBody tr[data-client-row]');
            let hasResult = false;
            let visibleIndex = 0;

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(filter)) {
                    row.classList.remove('hidden', 'crm-row-odd', 'crm-row-even');
                    row.classList.add(visibleIndex % 2 === 0 ? 'crm-row-odd' : 'crm-row-even');
                    visibleIndex++;
                    hasResult = true;
                } else {
                    row.classList.add('hidden');
                }
            });

            const noResultDiv = document.getElementById('noResult');
            if (!hasResult && rows.length > 0) {
                noResultDiv.classList.remove('hidden');
            } else {
                noResultDiv.classList.add('hidden');
            }
        });

        @if($errors->createClient->any())
        document.addEventListener('DOMContentLoaded', function() {
            uiModalOpen('createClientModal');
        });
        @endif
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
    @endpush
</x-layout-users>