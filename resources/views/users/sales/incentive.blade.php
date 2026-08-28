@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Skema Insentif Sales' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        body { background-color: #ede9fe; }

        /* == Background == */
        .mesh-bg { 
            background-color: #ede9fe;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
        }

        /* == Header Style == */
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

        /* == Cards == */
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 1.5rem;
            padding: 1.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        }

        /* == Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.9rem; font-weight: 700;
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
            color: #1e40af;
        }
        .btn-back-modern .icon-circle {
            width: 32px; height: 32px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6;
            font-size: 0.85rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover .icon-circle {
            transform: translateX(-3px);
            background: #eff6ff;
        }

        .tab-btn {
            padding: 0.6rem 1.25rem; font-size: 0.95rem; border-radius: 1rem; font-weight: 700;
            color: #64748b; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid transparent;
            background: rgba(255, 255, 255, 0.6);
            cursor: pointer;
        }
        .tab-btn:hover { color: #2563eb; background: #eff6ff; }
        .tab-btn.active { 
            color: #ffffff; 
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); 
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            border-color: transparent;
        }

        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        @media (max-width: 767px) {
            .tab-btn {
                padding: 0.5rem 0.85rem;
                font-size: 0.8rem;
                border-radius: 0.75rem;
            }
        }

        .tier-badge {
            font-size: 0.8rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Tier Styling */
        .tier-bronze { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .tier-silver { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .tier-gold { background: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
        .tier-platinum { background: #ecfeff; color: #0891b2; border: 1px solid #cffafe; }
        .tier-diamond { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 h-full mesh-bg relative overflow-hidden text-slate-800 p-4 sm:p-6 lg:p-6" x-data="{ activeTab: '{{ request('tab', 'perbulan') }}', showInfoModal: false, showInfoModalTriwulan: false, showInfoModalOutlet: false }">
        <div class="w-full max-w-6xl mx-auto flex flex-col gap-4">
            
            {{-- Navigation/Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <a href="{{ route('sales.index') }}" class="btn-back-modern shrink-0 mb-0 self-start md:self-auto">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    Kembali ke Dashboard Sales
                </a>
            </div>

            {{-- Page Title Card (Mobile - Stock Style) --}}
            <div class="block md:hidden rounded-2xl p-4 text-white shadow-lg flex items-center gap-3 mb-2" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-lg shrink-0">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black uppercase tracking-wider">SKEMA INSENTIF SALES</h2>
                </div>
            </div>

            {{-- Page Title Card (Desktop - Original Style) --}}
            <div class="hidden md:block page-header mb-2">
                <div class="header-content flex flex-row items-center justify-between gap-6">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">Skema Insentif Sales</h1>
                        <p class="text-blue-100 text-xs md:text-sm mt-1">
                            Simulasikan dan pantau perhitungan insentif performa penjualan sales berdasarkan skema dan target resmi.
                        </p>
                    </div>
                    <div>
                        <i class="fas fa-hand-holding-dollar text-5xl opacity-20"></i>
                    </div>
                </div>
            </div>

            {{-- Tabs Navigation --}}
            <div class="flex flex-nowrap overflow-x-auto gap-2 pb-2 scrollbar-none whitespace-nowrap border-b border-slate-200">
                <button @click="activeTab = 'perbulan'" :class="activeTab === 'perbulan' ? 'active' : ''" class="tab-btn flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Skema Perbulan</span>
                </button>
                <button @click="activeTab = 'pertriwulan'" :class="activeTab === 'pertriwulan' ? 'active' : ''" class="tab-btn flex items-center gap-2">
                    <i class="fas fa-calendar-days"></i>
                    <span>Skema Pertriwulan</span>
                </button>
                <button @click="activeTab = 'outlet'" :class="activeTab === 'outlet' ? 'active' : ''" class="tab-btn flex items-center gap-2">
                    <i class="fas fa-store"></i>
                    <span>Bonus Outlet Baru</span>
                </button>
            </div>

            {{-- SECTION 1: PERBULAN (MONTHLY) --}}
            <div x-show="activeTab === 'perbulan'" class="flex flex-col gap-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                
                {{-- Card Realisasi & Payout (With Merged Filters) --}}
                <div class="glass-card">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4 border-b border-slate-100 pb-3 w-full">
                        <div class="flex items-center justify-between w-full lg:w-auto">
                            <div>
                                <h3 class="text-lg md:text-xl font-bold text-slate-800"><span class="md:inline hidden">Actual Sales & Estimasi </span>Insentif Bulanan</h3>
                                <p class="text-slate-500 text-xs md:text-sm mt-0.5 md:mt-1">Periode: {{ $bulan }} {{ $tahun }}</p>
                            </div>
                            
                            <!-- Info Button Top Right (Mobile only) -->
                            <button type="button" @click="showInfoModal = true" class="lg:hidden w-8 h-8 rounded-full bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-100 hover:text-blue-700 shadow-sm shrink-0 transition-colors" title="Lihat Aturan Skema Insentif">
                                <i class="fas fa-info-circle text-base"></i>
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-3 w-full lg:w-auto">
                            <form method="GET" action="{{ route('sales.incentive') }}" class="grid grid-cols-2 gap-3 w-full lg:flex lg:w-auto lg:items-center lg:gap-3">
                                <input type="hidden" name="tab" value="perbulan">
                                <div class="col-span-1 flex flex-col gap-0.5 relative lg:w-28">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Bulan</label>
                                    <div class="relative w-full">
                                        <select name="bulan" onchange="this.form.submit()" class="w-full appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            @foreach($listBulan as $b)
                                                <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>{{ $b }}</option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-1 flex flex-col gap-0.5 relative lg:w-24">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tahun</label>
                                    <div class="relative w-full">
                                        <select name="tahun" onchange="this.form.submit()" class="w-full appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            @foreach($listTahun as $t)
                                                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-2 lg:col-span-auto flex flex-col gap-0.5 relative lg:w-36">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">PS</label>
                                    <div class="relative w-full">
                                        <select name="ps" onchange="this.form.submit()" class="w-full appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            <option value="">All</option>
                                            @foreach($listPs as $p)
                                                <option value="{{ $p }}" {{ $psTerpilih == $p ? 'selected' : '' }}>{{ $p }}</option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Info Button (Desktop only) -->
                            <button type="button" @click="showInfoModal = true" class="hidden lg:flex w-8 h-8 rounded-full bg-blue-50 border border-blue-100 text-blue-600 items-center justify-center hover:bg-blue-100 hover:text-blue-700 shadow-sm shrink-0 transition-colors" title="Lihat Aturan Skema Insentif">
                                <i class="fas fa-info-circle text-base"></i>
                            </button>
                        </div>
                    </div>

                        @if(count($payouts) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 text-[11px] sm:text-xs font-bold uppercase tracking-wider">
                                        <th class="py-2.5 px-2 whitespace-nowrap">Sales Person (PS)</th>
                                        <th class="py-2.5 px-2 text-right whitespace-nowrap">Target</th>
                                        <th class="py-2.5 px-2 text-right whitespace-nowrap">Actual Sales</th>
                                        <th class="py-2.5 px-2 text-center whitespace-nowrap">Ach %</th>
                                        <th class="py-2.5 px-2 text-center whitespace-nowrap">Rate</th>
                                        <th class="py-2.5 px-2 text-right whitespace-nowrap">Est. Insentif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payouts as $payout)
                                    <tr class="border-b border-slate-100/50 hover:bg-slate-50/50 transition-colors text-slate-700 text-[11px] sm:text-xs">
                                        <td class="py-2.5 px-2 font-bold text-slate-800 whitespace-nowrap">{{ $payout['ps'] }}</td>
                                        <td class="py-2.5 px-2 text-right font-medium whitespace-nowrap">Rp {{ number_format($payout['target'], 0, ',', '.') }}</td>
                                        <td class="py-2.5 px-2 text-right font-bold text-slate-800 whitespace-nowrap">Rp {{ number_format($payout['sales'], 0, ',', '.') }}</td>
                                        <td class="py-2.5 px-2 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded-full text-[10px] sm:text-xs
                                                @if($payout['achievement_rate'] >= 200) bg-sky-50 text-sky-700
                                                @elseif($payout['achievement_rate'] >= 150) bg-indigo-50 text-indigo-700
                                                @elseif($payout['achievement_rate'] >= 130) bg-emerald-50 text-emerald-700
                                                @elseif($payout['achievement_rate'] >= 100) bg-blue-50 text-blue-700
                                                @elseif($payout['achievement_rate'] >= 95) bg-amber-50 text-amber-700
                                                @else bg-rose-50 text-rose-700 @endif">
                                                {{ number_format($payout['achievement_rate'], 1, ',', '.') }}%
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-2 text-center font-bold text-slate-600 whitespace-nowrap">
                                            {{ number_format($payout['incentive_rate'], 1, ',', '.') }}%
                                        </td>
                                        <td class="py-2.5 px-2 text-right font-black text-emerald-600 whitespace-nowrap">
                                            Rp {{ number_format($payout['incentive_amount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="py-12 flex flex-col items-center justify-center text-center text-slate-400">
                            <i class="fas fa-folder-open text-5xl mb-4 text-slate-300"></i>
                            <h4 class="font-bold text-lg text-slate-600">Data Tidak Ditemukan</h4>
                            <p class="text-sm max-w-sm mt-1">Belum ada data target atau actual sales untuk periode ini.</p>
                        </div>
                        @endif
                    </div>

            </div>

            {{-- Modal Aturan Skema --}}
            <div x-show="showInfoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showInfoModal = false"></div>
                <div class="glass-card w-full max-w-3xl shadow-2xl z-10 relative overflow-hidden" 
                     x-transition:enter="transition ease-out duration-300 transform" 
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    

                    
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                        <h3 class="text-lg font-bold flex items-center gap-2 text-slate-800">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            <span>Aturan Skema Perbulan</span>
                        </h3>
                        <button type="button" @click="showInfoModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-diamond mb-2">
                                <i class="fas fa-circle-chevron-up"></i> &ge; 200%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-sky-600 text-lg">3.0%</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-platinum mb-2">
                                <i class="fas fa-circle-chevron-up"></i> &ge; 150%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-indigo-600 text-lg">2.0%</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-gold mb-2">
                                <i class="fas fa-circle-check"></i> &ge; 130%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-yellow-600 text-lg">1.5%</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-silver mb-2">
                                <i class="fas fa-circle-check"></i> &ge; 100%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-slate-700 text-lg">1.0%</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-bronze mb-2">
                                <i class="fas fa-circle-check"></i> &ge; 95%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-amber-600 text-lg">0.5%</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all opacity-60 text-center">
                            <span class="tier-badge tier-silver mb-2">
                                <i class="fas fa-circle-xmark"></i> &lt; 95%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-rose-600 text-lg">0%</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 leading-relaxed">
                        * Persentase insentif dikalikan langsung dari total nilai actual sales bersih (Nett Sales) yang dibukukan.
                    </p>
                </div>
            </div>

            {{-- SECTION 2: PERTRIWULAN (QUARTERLY) --}}
            <div x-show="activeTab === 'pertriwulan'" class="flex flex-col gap-4" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                
                {{-- Card Realisasi & Payout Triwulan --}}
                <div class="glass-card">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4 border-b border-slate-100 pb-3 w-full">
                        <div class="flex items-center justify-between w-full lg:w-auto">
                            <div>
                                <h3 class="text-lg md:text-xl font-bold text-slate-800"><span class="md:inline hidden">Actual Sales & Estimasi </span>Insentif Triwulan</h3>
                                <p class="text-slate-500 text-xs md:text-sm mt-0.5 md:mt-1">Periode: {{ $triwulan }} {{ $tahun }}</p>
                            </div>
                            
                            <!-- Info Button Top Right (Mobile only) -->
                            <button type="button" @click="showInfoModalTriwulan = true" class="lg:hidden w-8 h-8 rounded-full bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-100 hover:text-blue-700 shadow-sm shrink-0 transition-colors" title="Lihat Aturan Skema Insentif">
                                <i class="fas fa-info-circle text-base"></i>
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-3 w-full lg:w-auto">
                            <form method="GET" action="{{ route('sales.incentive') }}" class="grid grid-cols-2 gap-3 w-full lg:flex lg:w-auto lg:items-center lg:gap-3">
                                <input type="hidden" name="tab" value="pertriwulan">
                                <div class="col-span-1 flex flex-col gap-0.5 relative lg:w-28">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Triwulan</label>
                                    <div class="relative w-full">
                                        <select name="triwulan" onchange="this.form.submit()" class="w-full appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            @foreach(['Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'] as $q)
                                                <option value="{{ $q }}" {{ $triwulan == $q ? 'selected' : '' }}>{{ $q }}</option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-1 flex flex-col gap-0.5 relative lg:w-24">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tahun</label>
                                    <div class="relative w-full">
                                        <select name="tahun" onchange="this.form.submit()" class="w-full appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            @foreach($listTahun as $t)
                                                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-2 lg:col-span-auto flex flex-col gap-0.5 relative lg:w-36">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">PS</label>
                                    <div class="relative w-full">
                                        <select name="ps" onchange="this.form.submit()" class="w-full appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            <option value="">All</option>
                                            @foreach($listPs as $p)
                                                <option value="{{ $p }}" {{ $psTerpilih == $p ? 'selected' : '' }}>{{ $p }}</option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Info Button (Desktop only) -->
                            <button type="button" @click="showInfoModalTriwulan = true" class="hidden lg:flex w-8 h-8 rounded-full bg-blue-50 border border-blue-100 text-blue-600 items-center justify-center hover:bg-blue-100 hover:text-blue-700 shadow-sm shrink-0 transition-colors" title="Lihat Aturan Skema Insentif">
                                <i class="fas fa-info-circle text-base"></i>
                            </button>
                        </div>
                    </div>

                    @if(count($payoutsTriwulan) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 text-[11px] sm:text-xs font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-2 whitespace-nowrap">Sales Person (PS)</th>
                                    <th class="py-2.5 px-2 text-right whitespace-nowrap">Target Triwulan</th>
                                    <th class="py-2.5 px-2 text-right whitespace-nowrap">Actual Sales</th>
                                    <th class="py-2.5 px-2 text-center whitespace-nowrap">Ach %</th>
                                    <th class="py-2.5 px-2 text-right whitespace-nowrap">Est. Insentif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payoutsTriwulan as $payout)
                                <tr class="border-b border-slate-100/50 hover:bg-slate-50/50 transition-colors text-slate-700 text-[11px] sm:text-xs">
                                    <td class="py-2.5 px-2 font-bold text-slate-800 whitespace-nowrap">{{ $payout['ps'] }}</td>
                                    <td class="py-2.5 px-2 text-right font-medium whitespace-nowrap">Rp {{ number_format($payout['target'], 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-2 text-right font-bold text-slate-800 whitespace-nowrap">Rp {{ number_format($payout['sales'], 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-2 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center font-bold px-2.5 py-1 rounded-full text-[10px] sm:text-xs
                                            @if($payout['achievement_rate'] >= 200) bg-sky-50 text-sky-700
                                            @elseif($payout['achievement_rate'] >= 150) bg-indigo-50 text-indigo-700
                                            @elseif($payout['achievement_rate'] >= 130) bg-emerald-50 text-emerald-700
                                            @elseif($payout['achievement_rate'] >= 100) bg-blue-50 text-blue-700
                                            @elseif($payout['achievement_rate'] >= 95) bg-amber-50 text-amber-700
                                            @else bg-rose-50 text-rose-700 @endif">
                                            {{ number_format($payout['achievement_rate'], 1, ',', '.') }}%
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2 text-right font-black text-emerald-600 whitespace-nowrap">
                                        Rp {{ number_format($payout['incentive_amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="py-12 flex flex-col items-center justify-center text-center text-slate-400">
                        <i class="fas fa-folder-open text-5xl mb-4 text-slate-300"></i>
                        <h4 class="font-bold text-lg text-slate-600">Data Tidak Ditemukan</h4>
                        <p class="text-sm max-w-sm mt-1">Belum ada data target atau actual sales untuk periode ini.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Modal Aturan Skema Triwulan --}}
            <div x-show="showInfoModalTriwulan" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showInfoModalTriwulan = false"></div>
                <div class="glass-card w-full max-w-3xl shadow-2xl z-10 relative overflow-hidden" 
                     x-transition:enter="transition ease-out duration-300 transform" 
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    

                    
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                        <h3 class="text-lg font-bold flex items-center gap-2 text-slate-800">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            <span>Aturan Skema Pertriwulan</span>
                        </h3>
                        <button type="button" @click="showInfoModalTriwulan = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-diamond mb-2">
                                <i class="fas fa-circle-chevron-up"></i> &ge; 200%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-sky-600 text-base">Rp 6.000.000</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-platinum mb-2">
                                <i class="fas fa-circle-chevron-up"></i> &ge; 150%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-indigo-600 text-base">Rp 4.500.000</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-gold mb-2">
                                <i class="fas fa-circle-check"></i> &ge; 130%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-yellow-600 text-base">Rp 3.000.000</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-silver mb-2">
                                <i class="fas fa-circle-check"></i> &ge; 100%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-slate-700 text-base">Rp 1.500.000</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-bronze mb-2">
                                <i class="fas fa-circle-check"></i> &ge; 95%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-amber-600 text-base">Rp 1.000.000</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all opacity-60 text-center">
                            <span class="tier-badge tier-silver mb-2">
                                <i class="fas fa-circle-xmark"></i> &lt; 95%
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-rose-600 text-base">Rp 0</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 leading-relaxed">
                        * Insentif triwulan diberikan sebagai bonus flat tetap berdasarkan tingkat akumulasi target pencapaian sales.
                    </p>
                </div>
            </div>

            {{-- SECTION 3: BONUS OUTLET BARU (NEW OUTLET BONUS) --}}
            <div x-show="activeTab === 'outlet'" class="flex flex-col gap-4" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                
                {{-- Card Realisasi & Payout Outlet Baru --}}
                <div class="glass-card">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4 border-b border-slate-100 pb-3 w-full">
                        <div class="flex items-center justify-between w-full lg:w-auto">
                            <div>
                                <h3 class="text-lg md:text-xl font-bold text-slate-800"><span class="md:inline hidden">Actual Sales & Estimasi </span>Bonus Outlet Baru</h3>
                                <p class="text-slate-500 text-xs md:text-sm mt-0.5 md:mt-1">Periode: {{ $bulan }} {{ $tahun }}</p>
                            </div>
                            
                            <!-- Info Button Top Right (Mobile only) -->
                            <button type="button" @click="showInfoModalOutlet = true" class="lg:hidden w-8 h-8 rounded-full bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-100 hover:text-blue-700 shadow-sm shrink-0 transition-colors" title="Lihat Aturan Skema Insentif">
                                <i class="fas fa-info-circle text-base"></i>
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-3 w-full lg:w-auto">
                            <form method="GET" action="{{ route('sales.incentive') }}" class="grid grid-cols-2 gap-3 w-full lg:flex lg:w-auto lg:items-center lg:gap-3">
                                <input type="hidden" name="tab" value="outlet">
                                <div class="col-span-1 flex flex-col gap-0.5 relative lg:w-28">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Bulan</label>
                                    <div class="relative w-full">
                                        <select name="bulan" onchange="this.form.submit()" class="w-full appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            @foreach($listBulan as $b)
                                                <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>{{ $b }}</option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-1 flex flex-col gap-0.5 relative lg:w-24">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tahun</label>
                                    <div class="relative w-full">
                                        <select name="tahun" onchange="this.form.submit()" class="w-full appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            @foreach($listTahun as $t)
                                                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-2 lg:col-span-auto flex flex-col gap-0.5 relative lg:w-36">
                                    <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">PS</label>
                                    <div class="relative w-full">
                                        <select name="ps" onchange="this.form.submit()" class="w-full appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                            <option value="">All</option>
                                            @foreach($listPs as $p)
                                                <option value="{{ $p }}" {{ $psTerpilih == $p ? 'selected' : '' }}>{{ $p }}</option>
                                            @endforeach
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Info Button (Desktop only) -->
                            <button type="button" @click="showInfoModalOutlet = true" class="hidden lg:flex w-8 h-8 rounded-full bg-blue-50 border border-blue-100 text-blue-600 items-center justify-center hover:bg-blue-100 hover:text-blue-700 shadow-sm shrink-0 transition-colors" title="Lihat Aturan Skema Insentif">
                                <i class="fas fa-info-circle text-base"></i>
                            </button>
                        </div>
                    </div>

                    @if(count($payoutsOutlet) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 text-[11px] sm:text-xs font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-2 whitespace-nowrap">Sales Person (PS)</th>
                                    <th class="py-2.5 px-2 text-center whitespace-nowrap">Jumlah Outlet Baru</th>
                                    <th class="py-2.5 px-2 whitespace-nowrap">Daftar Outlet Baru</th>
                                    <th class="py-2.5 px-2 text-right whitespace-nowrap">Est. Bonus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payoutsOutlet as $payout)
                                <tr class="border-b border-slate-100/50 hover:bg-slate-50/50 transition-colors text-slate-700 text-[11px] sm:text-xs">
                                    <td class="py-2.5 px-2 font-bold text-slate-800 whitespace-nowrap">{{ $payout['ps'] }}</td>
                                    <td class="py-2.5 px-2 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center font-bold px-2.5 py-1 rounded-full text-[10px] sm:text-xs
                                            @if($payout['new_outlets_count'] >= 21) bg-sky-50 text-sky-700
                                            @elseif($payout['new_outlets_count'] >= 16) bg-indigo-50 text-indigo-700
                                            @elseif($payout['new_outlets_count'] >= 11) bg-emerald-50 text-emerald-700
                                            @elseif($payout['new_outlets_count'] >= 6) bg-blue-50 text-blue-700
                                            @else bg-amber-50 text-amber-700 @endif">
                                            {{ $payout['new_outlets_count'] }} Outlet
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2">
                                        <div class="flex flex-wrap gap-1 max-w-md">
                                            @foreach($payout['new_outlets_list'] as $outlet)
                                                <span class="inline-block bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 rounded-md font-medium border border-slate-200/50">
                                                    {{ $outlet }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-2 text-right font-black text-emerald-600 whitespace-nowrap">
                                        Rp {{ number_format($payout['incentive_amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="py-12 flex flex-col items-center justify-center text-center text-slate-400">
                        <i class="fas fa-folder-open text-5xl mb-4 text-slate-300"></i>
                        <h4 class="font-bold text-lg text-slate-600">Data Tidak Ditemukan</h4>
                        <p class="text-sm max-w-sm mt-1">Belum ada transaksi dari outlet baru pada periode ini.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Modal Aturan Skema Outlet Baru --}}
            <div x-show="showInfoModalOutlet" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showInfoModalOutlet = false"></div>
                <div class="glass-card w-full max-w-3xl shadow-2xl z-10 relative overflow-hidden" 
                     x-transition:enter="transition ease-out duration-300 transform" 
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                        <h3 class="text-lg font-bold flex items-center gap-2 text-slate-800">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            <span>Aturan Bonus Outlet Baru</span>
                        </h3>
                        <button type="button" @click="showInfoModalOutlet = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-diamond mb-2">
                                <i class="fas fa-circle-chevron-up"></i> &ge; 21 Outlet
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-sky-600 text-base">Rp 1.000.000</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-platinum mb-2">
                                <i class="fas fa-circle-chevron-up"></i> 16 - 20 Outlet
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-indigo-600 text-base">Rp 800.000</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-gold mb-2">
                                <i class="fas fa-circle-check"></i> 11 - 15 Outlet
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-yellow-600 text-base">Rp 500.000</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-silver mb-2">
                                <i class="fas fa-circle-check"></i> 6 - 10 Outlet
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-slate-700 text-base">Rp 300.000</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-bronze mb-2">
                                <i class="fas fa-circle-check"></i> 1 - 5 Outlet
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-amber-600 text-base">Rp 150.000</span>
                        </div>

                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all opacity-60 text-center">
                            <span class="tier-badge tier-silver mb-2">
                                <i class="fas fa-circle-xmark"></i> 0 Outlet
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-rose-600 text-base">Rp 0</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 leading-relaxed">
                        * Bonus outlet baru diberikan secara flat jika nama customer tersebut belum pernah tercatat bertransaksi sama sekali di database sebelum periode terpilih.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-layout-users>
