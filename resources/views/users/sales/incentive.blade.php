@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Sales Incentive Scheme' }}">
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

        /* Tier Styling */
        .tier-badge {
            font-size: 0.8rem; font-weight: 700; padding: 4px 10px; border-radius: 8px;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .tier-bronze { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .tier-silver { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .tier-gold { background: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
        .tier-platinum { background: #ecfeff; color: #0891b2; border: 1px solid #cffafe; }
        .tier-diamond { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }

        /* Sticky Column PS for Table */
        .table-sticky-container { position: relative; overflow-x: auto; }
        .table-sticky-container table { width: 100%; border-collapse: collapse; }
        @media (max-width: 767px) {
            .table-sticky-container th:first-child, .table-sticky-container td:first-child {
                position: sticky; left: 0; z-index: 10;
                background-color: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(8px); border-right: 1px solid #e2e8f0;
                box-shadow: 4px 0 8px -3px rgba(0, 0, 0, 0.03);
            }
            .table-sticky-container tr:hover th:first-child, .table-sticky-container tr:hover td:first-child {
                background-color: rgba(248, 250, 252, 0.95);
            }
        }

        /* == Mobile Responsive == */
        @media (max-width: 640px) {
            .glass-card { padding: 0.9rem; border-radius: 1.1rem; }
        }

        /* Mobile Payout Cards */
        .mobile-payout-card {
            background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.9rem;
            padding: 0.75rem 0.85rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .mobile-payout-card + .mobile-payout-card { margin-top: 0.5rem; }

        /* Desktop table hidden on mobile, mobile cards hidden on desktop */
        .desktop-table-wrap { display: block; }
        .mobile-cards-wrap { display: none; }
        @media (max-width: 640px) {
            .desktop-table-wrap { display: none; }
            .mobile-cards-wrap { display: flex; }
        }

        @media (max-width: 767px) {
            .mobile-auto-h { flex: 0 1 auto !important; min-height: 0 !important; }
        }

        /* ===== Tab scroll hint (mobile) ===== */
        .tab-scroller-wrap { position: relative; }
        @keyframes swipeHint {
            0%, 100% { opacity: 0.45; }
            50% { opacity: 1; }
        }
        .tab-hint-text {
            animation: swipeHint 1.4s ease-in-out infinite;
            font-size: 0.6rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16 mobile-auto-h" x-data="{
        activeTab: (() => {
            const validTabs = ['monthly', 'quarterly', 'outlet'@if($hasFullAccess), 'settings'@endif];
            const urlTab = new URLSearchParams(window.location.search).get('tab');
            if (urlTab && validTabs.includes(urlTab)) return urlTab;
            const stored = localStorage.getItem('active_incentive_tab');
            if (stored && validTabs.includes(stored)) return stored;
            return 'monthly';
        })(),
        showTabHint: true,
        checkTabOverflow() {
            const el = this.$refs.tabScroller;
            if (!el) return;
            const hasOverflow = el.scrollWidth > el.clientWidth + 4;
            const atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4;
            this.showTabHint = hasOverflow && !atEnd;
        },
        showInfoModal: false,
        showInfoModalTriwulan: false,
        showInfoModalOutlet: false,
        showFormModalBulan: false,
        showFormModalTriwulan: false,
        formTahun: '{{ $tahun }}',
        formBulan: '{{ $bulan }}',
        formTriwulan: '{{ $triwulan }}',
        formBasisBulan: '{{ $activeBasis }}',
        formBasisTriwulan: '{{ $activeBasisTriwulan }}',
        settingsBulanNominal: {{ json_encode($settingsBulanNominal->map(fn($s) => ['min_achievement' => (float)$s->min_achievement, 'incentive_value' => (float)$s->incentive_value])) }},
        settingsTriwulanNominal: {{ json_encode($settingsTriwulanNominal->map(fn($s) => ['min_achievement' => (float)$s->min_achievement, 'incentive_value' => (float)$s->incentive_value])) }},
        settingsBulanPercent: {{ json_encode($settingsBulanPercent->map(fn($s) => ['min_achievement' => (float)$s->min_achievement, 'incentive_value' => (float)$s->incentive_value])) }},
        settingsTriwulanPercent: {{ json_encode($settingsTriwulanPercent->map(fn($s) => ['min_achievement' => (float)$s->min_achievement, 'incentive_value' => (float)$s->incentive_value])) }},
        
        historySearchTahun: '', historyFilterType: '', historyFilterBasis: '',
        copySourceBulan: '', copySourceTriwulan: '',
        showHistoryDetailModal: false, historyDetailTitle: '', historyDetailSub: '', historyDetailTiers: [], historyDetailType: '',
        historyList: [
            @foreach($settingsHistory as $key => $tiers)
                @php [$hTahun, $hBulan, $hType, $hBasis] = explode('|', $key); @endphp
                {
                    tahun: '{{ $hTahun }}', bulan: '{{ $hBulan }}', type: '{{ $hType }}', basis: '{{ $hBasis }}', tierCount: {{ count($tiers) }},
                    tiers: {{ json_encode($tiers->map(fn($t) => ['min_achievement' => (float)$t->min_achievement, 'incentive_value' => (float)$t->incentive_value, 'basis' => $t->basis])) }}
                },
            @endforeach
        ],
        filteredHistoryList(type = '') {
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'];
            return this.historyList.filter(row => {
                if (type && row.type !== type) return false;
                if (this.historySearchTahun && !row.tahun.includes(this.historySearchTahun)) return false;
                if (this.historyFilterBasis && row.basis !== this.historyFilterBasis) return false;
                return true;
            }).sort((a, b) => {
                if (b.tahun !== a.tahun) return b.tahun - a.tahun;
                return months.indexOf(a.bulan) - months.indexOf(b.bulan);
            });
        },
        formatRupiahShorthand(value) {
            if (!value || isNaN(value)) return '';
            const num = parseFloat(value);
            if (num >= 1000000000) return 'Rp ' + (num / 1000000000).toLocaleString('id-ID', {maximumFractionDigits: 2}) + ' Miliar';
            if (num >= 1000000) return 'Rp ' + (num / 1000000).toLocaleString('id-ID', {maximumFractionDigits: 2}) + ' Juta';
            if (num >= 1000) return 'Rp ' + (num / 1000).toLocaleString('id-ID', {maximumFractionDigits: 2}) + ' Ribu';
            return 'Rp ' + num.toLocaleString('id-ID');
        },
        getTierRangeLabel(tiers, idx, basis) {
            const sorted = [...tiers].sort((a, b) => b.min_achievement - a.min_achievement);
            const current = sorted[idx];
            if (!current) return '';
            const fmt = (num) => new Intl.NumberFormat('id-ID').format(num);
            if (basis !== 'nominal') return `Achievement ≥ ${current.min_achievement}%`;
            if (idx === 0) {
                if (current.min_achievement % 1000000 === 1) return `Actual Sales > Rp ${fmt(current.min_achievement - 1)}`;
                return `Actual Sales ≥ Rp ${fmt(current.min_achievement)}`;
            } else {
                const nextHigher = sorted[idx - 1];
                const maxVal = nextHigher.min_achievement % 1000000 === 1 ? nextHigher.min_achievement - 1 : (nextHigher.min_achievement % 1000000 === 0 ? nextHigher.min_achievement - 1000000 : nextHigher.min_achievement - 1);
                return `Rp ${fmt(current.min_achievement)} - Rp ${fmt(maxVal)}`;
            }
        },
        init() {
            localStorage.setItem('active_incentive_tab', this.activeTab);
            const url = new URL(window.location); url.searchParams.set('tab', this.activeTab); window.history.replaceState({}, '', url);
            this.$watch('activeTab', value => {
                localStorage.setItem('active_incentive_tab', value);
                const url = new URL(window.location); url.searchParams.set('tab', value); window.history.replaceState({}, '', url);
            });
            this.$nextTick(() => this.checkTabOverflow());
            window.addEventListener('resize', () => this.checkTabOverflow());
        }
    }">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-10 space-y-4 flex-1 flex flex-col justify-start mobile-auto-h">
            
            <div class="w-full flex justify-start">
                <x-ui.back-button href="{{ route('sales.index') }}" label="Back to Sales Dashboard" />
            </div>

            <div class="hidden md:block">
                <x-ui.page-header title="Sales Incentive Scheme" subtitle="Monitor sales performance incentive calculations based on official schemes and targets." icon="fa-hand-holding-dollar">
                    <x-slot:controls>
                        <div x-ref="tabScroller" @scroll="checkTabOverflow()" class="flex space-x-1 bg-white/10 p-1 rounded-full border border-white/20 backdrop-blur-md overflow-x-auto relative z-10 w-full">
                            <button @click="activeTab = 'monthly'" :class="{ 'bg-white text-blue-700 shadow-md': activeTab === 'monthly', 'text-white hover:bg-white/20': activeTab !== 'monthly' }" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center">
                                <i class="fas fa-calendar-alt mr-2"></i> Monthly Scheme
                            </button>
                            <button @click="activeTab = 'quarterly'" :class="{ 'bg-white text-blue-700 shadow-md': activeTab === 'quarterly', 'text-white hover:bg-white/20': activeTab !== 'quarterly' }" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center">
                                <i class="fas fa-calendar-days mr-2"></i> Quarterly Scheme
                            </button>
                            <button @click="activeTab = 'outlet'" :class="{ 'bg-white text-blue-700 shadow-md': activeTab === 'outlet', 'text-white hover:bg-white/20': activeTab !== 'outlet' }" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center">
                                <i class="fas fa-store mr-2"></i> New Outlet Bonus
                            </button>
                            @if($hasFullAccess)
                            <button @click="activeTab = 'settings'" :class="{ 'bg-white text-blue-700 shadow-md': activeTab === 'settings', 'text-white hover:bg-white/20': activeTab !== 'settings' }" class="px-4 py-1.5 text-sm rounded-full font-bold transition-all whitespace-nowrap flex items-center flex-1 justify-center">
                                <i class="fas fa-cog mr-2"></i> Rules Settings
                            </button>
                            @endif
                        </div>
                    </x-slot:controls>
                </x-ui.page-header>
            </div>
            <div class="md:hidden space-y-3">
                <div class="mobile-page-header flex items-center justify-between gap-3">
                    <div class="relative z-10 min-w-0">
                        <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Incentive Scheme</h2>
                        <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">Monitor incentive calculations.</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-1.5 flex items-stretch gap-1 overflow-x-auto">
                    <button @click="activeTab = 'monthly'" :class="activeTab === 'monthly' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500'" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold whitespace-nowrap">
                        <i class="fas fa-calendar-alt text-sm"></i><span class="text-[10px] uppercase">Monthly</span>
                    </button>
                    <button @click="activeTab = 'quarterly'" :class="activeTab === 'quarterly' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500'" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold whitespace-nowrap">
                        <i class="fas fa-calendar-days text-sm"></i><span class="text-[10px] uppercase">Quarterly</span>
                    </button>
                    <button @click="activeTab = 'outlet'" :class="activeTab === 'outlet' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-slate-500'" class="flex-1 flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl font-bold whitespace-nowrap">
                        <i class="fas fa-store text-sm"></i><span class="text-[10px] uppercase">Outlet</span>
                    </button>
                </div>
            </div>

            {{-- SECTION 1: MONTHLY SCHEME --}}
            <div x-show="activeTab === 'monthly'" class="flex flex-col w-full" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                
                <x-ui.glass-card padding="none" class="!p-0 overflow-hidden flex flex-col w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg">
                    
                    {{-- Header Table + Filter (Rata Kiri & Kanan Balance) --}}
                    <div class="px-4 sm:px-6 py-4 border-b border-slate-200 bg-slate-50/80 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full">
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-sm"><i class="fas fa-table"></i></div>
                            <div>
                                <h3 class="text-base sm:text-lg font-black text-slate-800">Actual Sales & Monthly Incentive</h3>
                                <p class="text-[11px] sm:text-xs text-slate-500 font-semibold mt-0.5">Period: {{ $bulan }} {{ $tahun }}</p>
                            </div>
                        </div>

                        {{-- Form Filter Flat, dipaksa nempel ke pojok kanan persis --}}
                        <form method="GET" action="{{ route('sales.incentive') }}" id="filterMonthly" class="flex flex-wrap items-center justify-start md:justify-end gap-2 w-full md:w-auto">
                            <input type="hidden" name="tab" value="monthly">
                            
                            <div class="relative w-[110px] shrink-0">
                                <select name="bulan" onchange="document.getElementById('filterMonthly').submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    @foreach($listBulan as $b)
                                        <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>{{ $b }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            
                            <div class="relative w-[90px] shrink-0">
                                <select name="tahun" onchange="document.getElementById('filterMonthly').submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    @foreach($listTahun as $t)
                                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            
                            @if($hasFullAccess)
                            <div class="relative w-[160px] shrink-0">
                                <select name="ps" onchange="document.getElementById('filterMonthly').submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    <option value="">All PS</option>
                                    <option value="Sales Team" {{ $psTerpilih == 'Sales Team' ? 'selected' : '' }}>Sales Team</option>
                                    @foreach($listPs as $p)
                                        <option value="{{ $p }}" {{ $psTerpilih == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            @endif
                            
                            <button type="button" @click="showInfoModal = true" class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold py-1.5 px-3 rounded-lg text-xs transition-all border border-slate-200 flex items-center justify-center shrink-0 shadow-sm" title="View Scheme Rules">
                                <i class="fas fa-info-circle mr-1.5"></i> Rules
                            </button>
                        </form>
                    </div>

                    @if(count($payouts) > 0)
                    {{-- Mobile Cards --}}
                    <div class="mobile-cards-wrap flex-col gap-0 p-3">
                        @foreach($payouts as $payout)
                        <div class="mobile-payout-card">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-slate-800 text-sm truncate">{{ $payout['ps'] }}</div>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                        <span class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded-full text-[10px]
                                            @if($payout['achievement_rate'] >= 200) bg-sky-50 text-sky-700
                                            @elseif($payout['achievement_rate'] >= 150) bg-indigo-50 text-indigo-700
                                            @elseif($payout['achievement_rate'] >= 130) bg-emerald-50 text-emerald-700
                                            @elseif($payout['achievement_rate'] >= 100) bg-blue-50 text-blue-700
                                            @elseif($payout['achievement_rate'] >= 95) bg-amber-50 text-amber-700
                                            @else bg-rose-50 text-rose-700 @endif">
                                            {{ number_format($payout['achievement_rate'], 1, ',', '.') }}%
                                        </span>
                                        <span class="text-[10px] font-semibold text-slate-500">Rate: {{ number_format($payout['incentive_rate'], 1, ',', '.') }}%</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-black text-emerald-600 text-sm leading-tight">Rp {{ number_format($payout['incentive_amount'], 0, ',', '.') }}</div>
                                    <div class="text-[9px] font-semibold text-slate-400 mt-0.5">Insentif</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-slate-100 text-[10px] font-semibold text-slate-500">
                                <div>Target: <span class="font-bold text-slate-700">Rp {{ number_format($payout['target'], 0, ',', '.') }}</span></div>
                                <div>Actual: <span class="font-bold text-slate-700">Rp {{ number_format($payout['sales'], 0, ',', '.') }}</span></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Desktop Table --}}
                    <div class="overflow-x-auto flex-1 table-sticky-container desktop-table-wrap">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-200 font-bold tracking-wider">
                                <tr>
                                    <th class="px-4 py-4 text-left whitespace-nowrap">Sales Person (PS)</th>
                                    <th class="px-4 py-4 text-right whitespace-nowrap">Target</th>
                                    <th class="px-4 py-4 text-right whitespace-nowrap">Actual Sales</th>
                                    <th class="px-4 py-4 text-center whitespace-nowrap">Ach %</th>
                                    <th class="px-4 py-4 text-center whitespace-nowrap">Rate</th>
                                    <th class="px-4 py-4 text-right whitespace-nowrap">Est. Incentive</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($payouts as $payout)
                                <tr class="hover:bg-indigo-50/50 transition-colors">
                                    <td class="px-4 py-3 font-bold text-slate-800 whitespace-nowrap">{{ $payout['ps'] }}</td>
                                    <td class="px-4 py-3 text-right font-medium whitespace-nowrap">Rp {{ number_format($payout['target'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800 whitespace-nowrap">Rp {{ number_format($payout['sales'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded-full text-xs
                                            @if($payout['achievement_rate'] >= 200) bg-sky-50 text-sky-700
                                            @elseif($payout['achievement_rate'] >= 150) bg-indigo-50 text-indigo-700
                                            @elseif($payout['achievement_rate'] >= 130) bg-emerald-50 text-emerald-700
                                            @elseif($payout['achievement_rate'] >= 100) bg-blue-50 text-blue-700
                                            @elseif($payout['achievement_rate'] >= 95) bg-amber-50 text-amber-700
                                            @else bg-rose-50 text-rose-700 @endif">
                                            {{ number_format($payout['achievement_rate'], 1, ',', '.') }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-600 whitespace-nowrap">
                                        {{ number_format($payout['incentive_rate'], 1, ',', '.') }}%
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-emerald-600 whitespace-nowrap">
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
                        <h4 class="font-bold text-lg text-slate-600">Data Not Found</h4>
                        <p class="text-sm max-w-sm mt-1">No target or actual sales data available for this period.</p>
                    </div>
                    @endif
                </x-ui.glass-card>
            </div>

            {{-- SECTION 2: QUARTERLY SCHEME --}}
            <div x-show="activeTab === 'quarterly'" class="flex flex-col w-full" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                
                <x-ui.glass-card padding="none" class="!p-0 overflow-hidden flex flex-col w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg">
                    
                    {{-- Header Table + Filter --}}
                    <div class="px-4 sm:px-6 py-4 border-b border-slate-200 bg-slate-50/80 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full">
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-sm"><i class="fas fa-table"></i></div>
                            <div>
                                <h3 class="text-base sm:text-lg font-black text-slate-800">Actual Sales & Quarterly Incentive</h3>
                                <p class="text-[11px] sm:text-xs text-slate-500 font-semibold mt-0.5">Period: {{ $triwulan }} {{ $tahun }}</p>
                            </div>
                        </div>

                        {{-- Form Filter Flat --}}
                        <form method="GET" action="{{ route('sales.incentive') }}" id="filterQuarterly" class="flex flex-wrap items-center justify-start md:justify-end gap-2 w-full md:w-auto">
                            <input type="hidden" name="tab" value="quarterly">
                            
                            <div class="relative w-[110px] shrink-0">
                                <select name="triwulan" onchange="document.getElementById('filterQuarterly').submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    @foreach(['Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'] as $q)
                                        <option value="{{ $q }}" {{ $triwulan == $q ? 'selected' : '' }}>{{ $q }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            
                            <div class="relative w-[90px] shrink-0">
                                <select name="tahun" onchange="document.getElementById('filterQuarterly').submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    @foreach($listTahun as $t)
                                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            
                            @if($hasFullAccess)
                            <div class="relative w-[160px] shrink-0">
                                <select name="ps" onchange="document.getElementById('filterQuarterly').submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    <option value="">All PS</option>
                                    <option value="Sales Team" {{ $psTerpilih == 'Sales Team' ? 'selected' : '' }}>Sales Team</option>
                                    @foreach($listPs as $p)
                                        <option value="{{ $p }}" {{ $psTerpilih == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            @endif
                            
                            <button type="button" @click="showInfoModalTriwulan = true" class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold py-1.5 px-3 rounded-lg text-xs transition-all border border-slate-200 flex items-center justify-center shrink-0 shadow-sm" title="View Scheme Rules">
                                <i class="fas fa-info-circle mr-1.5"></i> Rules
                            </button>
                        </form>
                    </div>

                    @if(count($payoutsTriwulan) > 0)
                    {{-- Mobile Cards --}}
                    <div class="mobile-cards-wrap flex-col gap-0 p-3">
                        @foreach($payoutsTriwulan as $payout)
                        <div class="mobile-payout-card">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-slate-800 text-sm truncate">{{ $payout['ps'] }}</div>
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded-full text-[10px]
                                            @if($payout['achievement_rate'] >= 200) bg-sky-50 text-sky-700
                                            @elseif($payout['achievement_rate'] >= 150) bg-indigo-50 text-indigo-700
                                            @elseif($payout['achievement_rate'] >= 130) bg-emerald-50 text-emerald-700
                                            @elseif($payout['achievement_rate'] >= 100) bg-blue-50 text-blue-700
                                            @elseif($payout['achievement_rate'] >= 95) bg-amber-50 text-amber-700
                                            @else bg-rose-50 text-rose-700 @endif">
                                            {{ number_format($payout['achievement_rate'], 1, ',', '.') }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-black text-emerald-600 text-sm leading-tight">Rp {{ number_format($payout['incentive_amount'], 0, ',', '.') }}</div>
                                    <div class="text-[9px] font-semibold text-slate-400 mt-0.5">Insentif</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-slate-100 text-[10px] font-semibold text-slate-500">
                                <div>Target: <span class="font-bold text-slate-700">Rp {{ number_format($payout['target'], 0, ',', '.') }}</span></div>
                                <div>Actual: <span class="font-bold text-slate-700">Rp {{ number_format($payout['sales'], 0, ',', '.') }}</span></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Desktop Table --}}
                    <div class="overflow-x-auto flex-1 table-sticky-container desktop-table-wrap">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-200 font-bold tracking-wider">
                                <tr>
                                    <th class="px-4 py-4 text-left whitespace-nowrap">Sales Person (PS)</th>
                                    <th class="px-4 py-4 text-right whitespace-nowrap">Quarterly Target</th>
                                    <th class="px-4 py-4 text-right whitespace-nowrap">Actual Sales</th>
                                    <th class="px-4 py-4 text-center whitespace-nowrap">Ach %</th>
                                    <th class="px-4 py-4 text-right whitespace-nowrap">Est. Incentive</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($payoutsTriwulan as $payout)
                                <tr class="hover:bg-indigo-50/50 transition-colors">
                                    <td class="px-4 py-3 font-bold text-slate-800 whitespace-nowrap">{{ $payout['ps'] }}</td>
                                    <td class="px-4 py-3 text-right font-medium whitespace-nowrap">Rp {{ number_format($payout['target'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800 whitespace-nowrap">Rp {{ number_format($payout['sales'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center font-bold px-2.5 py-1 rounded-full text-xs
                                            @if($payout['achievement_rate'] >= 200) bg-sky-50 text-sky-700
                                            @elseif($payout['achievement_rate'] >= 150) bg-indigo-50 text-indigo-700
                                            @elseif($payout['achievement_rate'] >= 130) bg-emerald-50 text-emerald-700
                                            @elseif($payout['achievement_rate'] >= 100) bg-blue-50 text-blue-700
                                            @elseif($payout['achievement_rate'] >= 95) bg-amber-50 text-amber-700
                                            @else bg-rose-50 text-rose-700 @endif">
                                            {{ number_format($payout['achievement_rate'], 1, ',', '.') }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-emerald-600 whitespace-nowrap">
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
                        <h4 class="font-bold text-lg text-slate-600">Data Not Found</h4>
                        <p class="text-sm max-w-sm mt-1">No target or actual sales data available for this period.</p>
                    </div>
                    @endif
                </x-ui.glass-card>
            </div>

            {{-- SECTION 3: NEW OUTLET BONUS --}}
            <div x-show="activeTab === 'outlet'" class="flex flex-col w-full" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                
                <x-ui.glass-card padding="none" class="!p-0 overflow-hidden flex flex-col w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg">
                    
                    {{-- Header Table + Filter --}}
                    <div class="px-4 sm:px-6 py-4 border-b border-slate-200 bg-slate-50/80 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full">
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-sm"><i class="fas fa-table"></i></div>
                            <div>
                                <h3 class="text-base sm:text-lg font-black text-slate-800">Actual Sales & New Outlet Bonus</h3>
                                <p class="text-[11px] sm:text-xs text-slate-500 font-semibold mt-0.5">Period: {{ $bulan }} {{ $tahun }}</p>
                            </div>
                        </div>

                        {{-- Form Filter Flat --}}
                        <form method="GET" action="{{ route('sales.incentive') }}" id="filterOutlet" class="flex flex-wrap items-center justify-start md:justify-end gap-2 w-full md:w-auto">
                            <input type="hidden" name="tab" value="outlet">
                            
                            <div class="relative w-[110px] shrink-0">
                                <select name="bulan" onchange="document.getElementById('filterOutlet').submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    @foreach($listBulan as $b)
                                        <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>{{ $b }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            
                            <div class="relative w-[90px] shrink-0">
                                <select name="tahun" onchange="document.getElementById('filterOutlet').submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    @foreach($listTahun as $t)
                                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            
                            @if($hasFullAccess)
                            <div class="relative w-[160px] shrink-0">
                                <select name="ps" onchange="document.getElementById('filterOutlet').submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-xs pl-3 pr-8 py-1.5 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    <option value="">All PS</option>
                                    <option value="Sales Team" {{ $psTerpilih == 'Sales Team' ? 'selected' : '' }}>Sales Team</option>
                                    @foreach($listPs as $p)
                                        <option value="{{ $p }}" {{ $psTerpilih == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            @endif
                            
                            <button type="button" @click="showInfoModalOutlet = true" class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold py-1.5 px-3 rounded-lg text-xs transition-all border border-slate-200 flex items-center justify-center shrink-0 shadow-sm" title="View Scheme Rules">
                                <i class="fas fa-info-circle mr-1.5"></i> Rules
                            </button>
                        </form>
                    </div>

                    @if(count($payoutsOutlet) > 0)
                    {{-- Mobile Cards --}}
                    <div class="mobile-cards-wrap flex-col gap-0 p-3">
                        @foreach($payoutsOutlet as $payout)
                        <div class="mobile-payout-card">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-slate-800 text-sm truncate">{{ $payout['ps'] }}</div>
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded-full text-[10px]
                                            @if($payout['new_outlets_count'] >= 21) bg-sky-50 text-sky-700
                                            @elseif($payout['new_outlets_count'] >= 16) bg-indigo-50 text-indigo-700
                                            @elseif($payout['new_outlets_count'] >= 11) bg-emerald-50 text-emerald-700
                                            @elseif($payout['new_outlets_count'] >= 6) bg-blue-50 text-blue-700
                                            @else bg-amber-50 text-amber-700 @endif">
                                            {{ $payout['new_outlets_count'] }} Outlets
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-black text-emerald-600 text-sm leading-tight">Rp {{ number_format($payout['incentive_amount'], 0, ',', '.') }}</div>
                                    <div class="text-[9px] font-semibold text-slate-400 mt-0.5">Bonus</div>
                                </div>
                            </div>
                            @if(count($payout['new_outlets_list']) > 0)
                            <div class="flex flex-wrap gap-1 mt-2 pt-2 border-t border-slate-100">
                                @foreach($payout['new_outlets_list'] as $outlet)
                                    <span class="inline-block bg-slate-100 text-slate-600 text-[9px] px-2 py-0.5 rounded-md font-medium border border-slate-200/50">{{ $outlet }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    {{-- Desktop Table --}}
                    <div class="overflow-x-auto flex-1 table-sticky-container desktop-table-wrap">
                        <table class="w-full text-sm text-left text-slate-600">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-200 font-bold tracking-wider">
                                <tr>
                                    <th class="px-4 py-4 text-left whitespace-nowrap">Sales Person (PS)</th>
                                    <th class="px-4 py-4 text-center whitespace-nowrap">New Outlets Count</th>
                                    <th class="px-4 py-4 text-left whitespace-nowrap">New Outlets List</th>
                                    <th class="px-4 py-4 text-right whitespace-nowrap">Est. Bonus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($payoutsOutlet as $payout)
                                <tr class="hover:bg-indigo-50/50 transition-colors">
                                    <td class="px-4 py-3 font-bold text-slate-800 whitespace-nowrap">{{ $payout['ps'] }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center font-bold px-2.5 py-1 rounded-full text-xs
                                            @if($payout['new_outlets_count'] >= 21) bg-sky-50 text-sky-700
                                            @elseif($payout['new_outlets_count'] >= 16) bg-indigo-50 text-indigo-700
                                            @elseif($payout['new_outlets_count'] >= 11) bg-emerald-50 text-emerald-700
                                            @elseif($payout['new_outlets_count'] >= 6) bg-blue-50 text-blue-700
                                            @else bg-amber-50 text-amber-700 @endif">
                                            {{ $payout['new_outlets_count'] }} Outlets
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1 max-w-md">
                                            @foreach($payout['new_outlets_list'] as $outlet)
                                                <span class="inline-block bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 rounded-md font-medium border border-slate-200/50">
                                                    {{ $outlet }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-emerald-600 whitespace-nowrap">
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
                        <h4 class="font-bold text-lg text-slate-600">Data Not Found</h4>
                        <p class="text-sm max-w-sm mt-1">No transactions from new outlets for this period.</p>
                    </div>
                    @endif
                </x-ui.glass-card>
            </div>

            {{-- SECTION 4: RULES SETTINGS --}}
            @if($hasFullAccess)
            <div x-show="activeTab === 'settings'" class="flex flex-col gap-4" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3">
                        <i class="fas fa-circle-check text-xl"></i>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <button type="button" @click="
                        formTahun = '{{ $tahun }}';
                        formBulan = '{{ $bulan }}';
                        formBasisBulan = '{{ $activeBasis }}';
                        showFormModalBulan = true;
                    " class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all hover:scale-[1.02] active:scale-[0.98]">
                        <i class="fas fa-calendar-day"></i> Configure Monthly Rules
                    </button>
                    <button type="button" @click="
                        formTahun = '{{ $tahun }}';
                        formTriwulan = '{{ $triwulan }}';
                        formBasisTriwulan = '{{ $activeBasisTriwulan }}';
                        showFormModalTriwulan = true;
                    " class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-all hover:scale-[1.02] active:scale-[0.98]">
                        <i class="fas fa-calendar-minus"></i> Configure Quarterly Rules
                    </button>
                </div>

                {{-- Global Filters for History --}}
                <div class="glass-card border-t-4 border-t-blue-500 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-800">Incentive Rules History</h3>
                        <p class="text-slate-500 text-xs mt-0.5">List of all monthly and quarterly incentive rules saved in the system</p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1">
                            <i class="fas fa-search text-slate-400 text-[10px]"></i>
                            <input type="text" x-model="historySearchTahun" placeholder="Search Year..." class="bg-transparent text-xs font-semibold text-slate-700 outline-none w-20">
                        </div>
                        <select x-model="historyFilterBasis" class="border border-slate-200 bg-slate-50 rounded-lg text-xs font-semibold text-slate-700 px-2 py-1 outline-none cursor-pointer">
                            <option value="">All Scheme Models</option>
                            <option value="nominal">Scheme 1 (Nominal Rp)</option>
                            <option value="percentage">Scheme 2 (Percentage %)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {{-- Card Riwayat Bulanan --}}
                    <div class="glass-card border-t-4 border-t-blue-500 flex flex-col gap-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-calendar-alt text-indigo-500"></i>
                                    <span>Monthly Rules History</span>
                                </h4>
                            </div>
                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full" x-text="filteredHistoryList('bulan').length + ' data'"></span>
                        </div>

                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                        <th class="py-2.5 px-2">Year</th>
                                        <th class="py-2.5 px-2">Month</th>
                                        <th class="py-2.5 px-2">Scheme Model</th>
                                        <th class="py-2.5 px-2 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, index) in filteredHistoryList('bulan')" :key="index">
                                        <tr class="border-b border-slate-100/50 hover:bg-slate-50/50 transition-colors text-slate-700 text-xs">
                                            <td class="py-3 px-2 font-bold text-slate-800" x-text="row.tahun"></td>
                                            <td class="py-3 px-2 font-semibold text-blue-600" x-text="row.bulan"></td>
                                            <td class="py-3 px-2">
                                                <span class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded-full text-[9px]"
                                                    :class="row.basis === 'nominal' ? 'bg-teal-50 text-teal-700' : 'bg-violet-50 text-violet-700'"
                                                    x-text="row.basis === 'nominal' ? 'Scheme 1: Nominal' : 'Scheme 2: Percentage'">
                                                </span>
                                            </td>
                                            <td class="py-3 px-2 text-center flex items-center justify-center gap-1">
                                                <button type="button" @click="
                                                    historyDetailTitle = row.bulan + ' ' + row.tahun;
                                                    historyDetailSub = 'Monthly Incentive - ' + (row.basis === 'nominal' ? 'Scheme 1 (Nominal Actual Sales)' : 'Scheme 2 (Percentage vs Target)');
                                                    historyDetailTiers = row.tiers;
                                                    historyDetailType = row.type;
                                                    showHistoryDetailModal = true;
                                                " class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold text-[9px] transition-colors border border-slate-200">
                                                    <i class="fas fa-eye text-[8px]"></i> Detail
                                                </button>
                                                @if($hasFullAccess)
                                                    <button type="button" @click="
                                                        formTahun = row.tahun;
                                                        formBulan = row.bulan;
                                                        formBasisBulan = row.basis;
                                                        if (row.basis === 'nominal') {
                                                            settingsBulanNominal = JSON.parse(JSON.stringify(row.tiers));
                                                        } else {
                                                            settingsBulanPercent = JSON.parse(JSON.stringify(row.tiers));
                                                        }
                                                        showFormModalBulan = true;
                                                    " class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-[9px] transition-colors border border-blue-100">
                                                        <i class="fas fa-edit text-[8px]"></i> Edit
                                                    </button>
                                                    <button type="button" @click="
                                                        if (confirm('Are you sure you want to delete this incentive rule?')) {
                                                            fetch('{{ route('sales.incentive.settings.delete') }}', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Content-Type': 'application/json',
                                                                    'X-Requested-With': 'XMLHttpRequest',
                                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                                },
                                                                body: JSON.stringify({
                                                                    _token: '{{ csrf_token() }}',
                                                                    tahun: row.tahun,
                                                                    bulan: row.bulan,
                                                                    type: row.type,
                                                                    basis: row.basis
                                                                })
                                                            })
                                                            .then(res => res.json())
                                                            .then(data => {
                                                                if (data.success) {
                                                                    const u = new URL(window.location); u.searchParams.set('tab', 'settings'); window.location.href = u.toString();
                                                                } else {
                                                                    alert(data.message || 'Failed to delete data.');
                                                                }
                                                            })
                                                            .catch(err => {
                                                                console.error(err);
                                                                alert('Connection error or expired token. Please refresh the page.');
                                                            });
                                                        }
                                                    " class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[9px] transition-colors border border-rose-100">
                                                        <i class="fas fa-trash-can text-[8px]"></i> Delete
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="filteredHistoryList('bulan').length === 0">
                                        <td colspan="4" class="py-8 text-center text-slate-400">
                                            <i class="fas fa-folder-open text-2xl mb-1 text-slate-300"></i>
                                            <p class="text-[11px] font-semibold">No monthly history found.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Card Riwayat Triwulan --}}
                    <div class="glass-card border-t-4 border-t-blue-500 flex flex-col gap-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-calendar-minus text-amber-500"></i>
                                    <span>Quarterly Rules History</span>
                                </h4>
                            </div>
                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full" x-text="filteredHistoryList('triwulan').length + ' data'"></span>
                        </div>

                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                        <th class="py-2.5 px-2">Year</th>
                                        <th class="py-2.5 px-2">Quarter</th>
                                        <th class="py-2.5 px-2">Scheme Model</th>
                                        <th class="py-2.5 px-2 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, index) in filteredHistoryList('triwulan')" :key="index">
                                        <tr class="border-b border-slate-100/50 hover:bg-slate-50/50 transition-colors text-slate-700 text-xs">
                                            <td class="py-3 px-2 font-bold text-slate-800" x-text="row.tahun"></td>
                                            <td class="py-3 px-2 font-semibold text-blue-600" x-text="row.bulan"></td>
                                            <td class="py-3 px-2">
                                                <span class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded-full text-[9px]"
                                                    :class="row.basis === 'nominal' ? 'bg-teal-50 text-teal-700' : 'bg-violet-50 text-violet-700'"
                                                    x-text="row.basis === 'nominal' ? 'Scheme 1: Nominal' : 'Scheme 2: Percentage'">
                                                </span>
                                            </td>
                                            <td class="py-3 px-2 text-center flex items-center justify-center gap-1">
                                                <button type="button" @click="
                                                    historyDetailTitle = row.bulan + ' ' + row.tahun;
                                                    historyDetailSub = 'Quarterly Bonus - ' + (row.basis === 'nominal' ? 'Scheme 1 (Nominal Actual Sales)' : 'Scheme 2 (Percentage vs Target)');
                                                    historyDetailTiers = row.tiers;
                                                    historyDetailType = row.type;
                                                    showHistoryDetailModal = true;
                                                " class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold text-[9px] transition-colors border border-slate-200">
                                                    <i class="fas fa-eye text-[8px]"></i> Detail
                                                </button>
                                                @if($hasFullAccess)
                                                    <button type="button" @click="
                                                        formTahun = row.tahun;
                                                        formTriwulan = row.bulan;
                                                        formBasisTriwulan = row.basis;
                                                        if (row.basis === 'nominal') {
                                                            settingsTriwulanNominal = JSON.parse(JSON.stringify(row.tiers));
                                                        } else {
                                                            settingsTriwulanPercent = JSON.parse(JSON.stringify(row.tiers));
                                                        }
                                                        showFormModalTriwulan = true;
                                                    " class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-[9px] transition-colors border border-blue-100">
                                                        <i class="fas fa-edit text-[8px]"></i> Edit
                                                    </button>
                                                    <button type="button" @click="
                                                        if (confirm('Are you sure you want to delete this incentive rule?')) {
                                                            fetch('{{ route('sales.incentive.settings.delete') }}', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Content-Type': 'application/json',
                                                                    'X-Requested-With': 'XMLHttpRequest',
                                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                                },
                                                                body: JSON.stringify({
                                                                    _token: '{{ csrf_token() }}',
                                                                    tahun: row.tahun,
                                                                    bulan: row.bulan,
                                                                    type: row.type,
                                                                    basis: row.basis
                                                                })
                                                            })
                                                            .then(res => res.json())
                                                            .then(data => {
                                                                if (data.success) {
                                                                    const u = new URL(window.location); u.searchParams.set('tab', 'settings'); window.location.href = u.toString();
                                                                } else {
                                                                    alert(data.message || 'Failed to delete data.');
                                                                }
                                                            })
                                                            .catch(err => {
                                                                console.error(err);
                                                                alert('Connection error or expired token. Please refresh the page.');
                                                            });
                                                        }
                                                    " class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[9px] transition-colors border border-rose-100">
                                                        <i class="fas fa-trash-can text-[8px]"></i> Delete
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="filteredHistoryList('triwulan').length === 0">
                                        <td colspan="4" class="py-8 text-center text-slate-400">
                                            <i class="fas fa-folder-open text-2xl mb-1 text-slate-300"></i>
                                            <p class="text-[11px] font-semibold">No quarterly history found.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- --- MODALS (Settings Modals) --- --}}
            {{-- Modal Aturan Skema Bulanan Info --}}
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
                            <span>Monthly Scheme Rules</span>
                        </h3>
                        <button type="button" @click="showInfoModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-diamond mb-2"><i class="fas fa-circle-chevron-up"></i> &ge; 200%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-sky-600 text-lg">3.0%</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-platinum mb-2"><i class="fas fa-circle-chevron-up"></i> &ge; 150%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-indigo-600 text-lg">2.0%</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-gold mb-2"><i class="fas fa-circle-check"></i> &ge; 130%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-yellow-600 text-lg">1.5%</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-silver mb-2"><i class="fas fa-circle-check"></i> &ge; 100%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-slate-700 text-lg">1.0%</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-bronze mb-2"><i class="fas fa-circle-check"></i> &ge; 95%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-amber-600 text-lg">0.5%</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all opacity-60 text-center">
                            <span class="tier-badge tier-silver mb-2"><i class="fas fa-circle-xmark"></i> &lt; 95%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-rose-600 text-lg">0%</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 leading-relaxed">* Incentive percentage is calculated directly from the total net actual sales recorded.</p>
                </div>
            </div>

            {{-- Modal Aturan Skema Triwulan Info --}}
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
                            <span>Quarterly Scheme Rules</span>
                        </h3>
                        <button type="button" @click="showInfoModalTriwulan = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-diamond mb-2"><i class="fas fa-circle-chevron-up"></i> &ge; 200%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-sky-600 text-base">Rp 6.000.000</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-platinum mb-2"><i class="fas fa-circle-chevron-up"></i> &ge; 150%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-indigo-600 text-base">Rp 4.500.000</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-gold mb-2"><i class="fas fa-circle-check"></i> &ge; 130%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-yellow-600 text-base">Rp 3.000.000</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-silver mb-2"><i class="fas fa-circle-check"></i> &ge; 100%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-slate-700 text-base">Rp 1.500.000</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-bronze mb-2"><i class="fas fa-circle-check"></i> &ge; 95%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-amber-600 text-base">Rp 1.000.000</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all opacity-60 text-center">
                            <span class="tier-badge tier-silver mb-2"><i class="fas fa-circle-xmark"></i> &lt; 95%</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sales Achieved</span>
                            <span class="font-bold text-rose-600 text-base">Rp 0</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 leading-relaxed">* Quarterly incentive is awarded as a fixed flat bonus based on the accumulated sales target achievement level.</p>
                </div>
            </div>

            {{-- Modal Aturan Skema Outlet Baru Info --}}
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
                            <span>New Outlet Bonus Rules</span>
                        </h3>
                        <button type="button" @click="showInfoModalOutlet = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-diamond mb-2"><i class="fas fa-circle-chevron-up"></i> &ge; 21 Outlets</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-sky-600 text-base">Rp 1.000.000</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-platinum mb-2"><i class="fas fa-circle-chevron-up"></i> 16 - 20 Outlets</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-indigo-600 text-base">Rp 800.000</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-gold mb-2"><i class="fas fa-circle-check"></i> 11 - 15 Outlets</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-yellow-600 text-base">Rp 500.000</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-silver mb-2"><i class="fas fa-circle-check"></i> 6 - 10 Outlets</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-slate-700 text-base">Rp 300.000</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all text-center">
                            <span class="tier-badge tier-bronze mb-2"><i class="fas fa-circle-check"></i> 1 - 5 Outlets</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-amber-600 text-base">Rp 150.000</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50/50 rounded-xl border border-slate-100 hover:border-slate-200/80 transition-all opacity-60 text-center">
                            <span class="tier-badge tier-silver mb-2"><i class="fas fa-circle-xmark"></i> 0 Outlets</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">New Outlet Bonus</span>
                            <span class="font-bold text-rose-600 text-base">Rp 0</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 leading-relaxed">* New outlet bonus is awarded as a flat amount if the customer name has never been recorded in transactions prior to the selected period.</p>
                </div>
            </div>

            {{-- Modal Form Aturan Bulanan --}}
            <div x-show="showFormModalBulan" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showFormModalBulan = false"></div>
                <div class="glass-card w-full max-w-2xl shadow-2xl z-10 relative overflow-hidden flex flex-col max-h-[92vh]" 
                     x-transition:enter="transition ease-out duration-300 transform" 
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3 shrink-0">
                        <h3 class="text-base sm:text-lg font-black flex items-center gap-2 text-slate-800">
                            <i class="fas fa-calendar-day text-blue-500"></i>
                            <span>Configure Monthly Incentive Rules</span>
                        </h3>
                        <button type="button" @click="showFormModalBulan = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <div class="overflow-y-auto flex-1 pr-1">
                        <form class="flex flex-col gap-4 pb-2">
                            <div class="bg-blue-50/50 p-3 rounded-xl border border-blue-100 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-copy text-blue-500"></i>
                                    <span class="font-bold text-slate-700">Copy from Other Period:</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select x-model="copySourceBulan" class="border border-slate-200 bg-white rounded-lg text-xs font-semibold text-slate-700 px-3 py-1 cursor-pointer outline-none">
                                        <option value="">-- Select Month --</option>
                                        <template x-for="item in historyList.filter(h => h.type === 'bulan')" :key="item.bulan + '|' + item.tahun + '|' + item.basis">
                                            <option :value="item.bulan + '|' + item.tahun + '|' + item.basis" x-text="item.bulan + ' ' + item.tahun + ' (' + (item.basis === 'nominal' ? 'Scheme 1' : 'Scheme 2') + ')'"></option>
                                        </template>
                                    </select>
                                    <button type="button" @click="
                                        if (copySourceBulan) {
                                            const [cBul, cTah, cBas] = copySourceBulan.split('|');
                                            const source = historyList.find(h => h.bulan === cBul && h.tahun === cTah && h.type === 'bulan' && h.basis === cBas);
                                            if (source) {
                                                formBasisBulan = source.basis;
                                                if (source.basis === 'nominal') {
                                                    settingsBulanNominal = JSON.parse(JSON.stringify(source.tiers));
                                                } else {
                                                    settingsBulanPercent = JSON.parse(JSON.stringify(source.tiers));
                                                }
                                            }
                                        }
                                    " class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors shadow-sm">
                                        Copy
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <div class="flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Target Year</label>
                                    <select name="tahun" x-model="formTahun" class="w-full border border-slate-200 bg-white rounded-lg text-xs font-semibold text-slate-700 px-3 py-1.5 cursor-pointer outline-none focus:ring-blue-500 focus:border-blue-500">
                                        @foreach($listTahun as $t)
                                            <option value="{{ $t }}">{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Target Month</label>
                                    <select name="bulan" x-model="formBulan" class="w-full border border-slate-200 bg-white rounded-lg text-xs font-semibold text-slate-700 px-3 py-1.5 cursor-pointer outline-none focus:ring-blue-500 focus:border-blue-500">
                                        @foreach($listBulan as $b)
                                            <option value="{{ $b }}">{{ $b }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Scheme Model</label>
                                    <select x-model="formBasisBulan" class="w-full border border-slate-200 bg-white rounded-lg text-xs font-semibold text-slate-700 px-3 py-1.5 cursor-pointer outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="nominal">Scheme 1 (Nominal Actual Sales Rp)</option>
                                        <option value="percentage">Scheme 2 (Percentage % vs Target)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                            <th class="py-2 px-1 w-7/12" x-text="formBasisBulan === 'nominal' ? 'Min. Sales (Rp)' : 'Min. Achievement (%)'"></th>
                                            <th class="py-2 px-1 w-4/12">Incentive (%)</th>
                                            <th class="py-2 px-1 w-1/12 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(tier, index) in (formBasisBulan === 'nominal' ? settingsBulanNominal : settingsBulanPercent)" :key="index">
                                            <tr class="border-b border-slate-100/50">
                                                <td class="py-1.5 px-1">
                                                    <div class="relative">
                                                        <span x-show="formBasisBulan === 'nominal'" class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400 text-[11px] font-bold">Rp</span>
                                                        <input type="number" name="min_achievement[]" x-model="tier.min_achievement" required class="w-full border border-slate-200 bg-white rounded-lg py-1 text-xs font-semibold text-slate-800 focus:ring-blue-500 focus:border-blue-500 transition-colors" :class="formBasisBulan === 'nominal' ? 'pl-7 pr-3' : 'pl-3 pr-6'">
                                                        <span x-show="formBasisBulan === 'percentage'" class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400 text-[11px] font-bold">%</span>
                                                    </div>
                                                    <div x-show="formBasisBulan === 'nominal' && tier.min_achievement" class="text-[10px] text-blue-600 font-bold mt-0.5 px-1" x-text="formatRupiahShorthand(tier.min_achievement)"></div>
                                                </td>
                                                <td class="py-1.5 px-1">
                                                    <div class="relative">
                                                        <input type="number" step="0.01" name="incentive_value[]" x-model="tier.incentive_value" required class="w-full border border-slate-200 bg-white rounded-lg pl-3 pr-6 py-1 text-xs font-semibold text-slate-800 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400 text-[11px] font-bold">%</span>
                                                    </div>
                                                </td>
                                                <td class="py-1.5 px-1 text-center">
                                                    <button type="button" @click="(formBasisBulan === 'nominal' ? settingsBulanNominal : settingsBulanPercent).splice(index, 1)" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 inline-flex items-center justify-center transition-colors border border-rose-100">
                                                        <i class="fas fa-trash-can text-[10px]"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <button type="button" @click="(formBasisBulan === 'nominal' ? settingsBulanNominal : settingsBulanPercent).push({min_achievement: 0, incentive_value: 0})" class="self-start inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 font-bold text-[10px] transition-colors shadow-sm">
                                    <i class="fas fa-plus"></i> Add Tier
                                </button>
                            </div>

                            <div class="flex items-center justify-end gap-3 mt-4 pt-3 border-t border-slate-100">
                                <button type="button" @click="showFormModalBulan = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition-colors">Cancel</button>
                                <button type="button" @click="
                                    const currentBulanList = formBasisBulan === 'nominal' ? settingsBulanNominal : settingsBulanPercent;
                                    const payloadBulan = {
                                        _token: '{{ csrf_token() }}', tahun: formTahun, bulan: formBulan, type: 'bulan', basis: formBasisBulan,
                                        min_achievement: currentBulanList.map(t => t.min_achievement),
                                        incentive_value: currentBulanList.map(t => t.incentive_value)
                                    };
                                    fetch('{{ route('sales.incentive.settings.save') }}', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                        body: JSON.stringify(payloadBulan)
                                    }).then(res => {
                                        if (res.ok) {
                                            const u = new URL(window.location); u.searchParams.set('tab', 'settings'); window.location.href = u.toString();
                                        } else {
                                            res.json().then(data => alert(data.message || 'Failed to save rules.'));
                                        }
                                    });
                                " class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-colors">
                                    <i class="fas fa-save"></i> Save & Activate
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal Form Aturan Triwulan --}}
            <div x-show="showFormModalTriwulan" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showFormModalTriwulan = false"></div>
                <div class="glass-card w-full max-w-2xl shadow-2xl z-10 relative overflow-hidden flex flex-col max-h-[92vh]" 
                     x-transition:enter="transition ease-out duration-300 transform" 
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3 shrink-0">
                        <h3 class="text-base sm:text-lg font-black flex items-center gap-2 text-slate-800">
                            <i class="fas fa-calendar-minus text-indigo-500"></i>
                            <span>Configure Quarterly Bonus Rules</span>
                        </h3>
                        <button type="button" @click="showFormModalTriwulan = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <div class="overflow-y-auto flex-1 pr-1">
                        <form class="flex flex-col gap-4 pb-2">
                            <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-copy text-indigo-500"></i>
                                    <span class="font-bold text-slate-700">Copy from Other Period:</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select x-model="copySourceTriwulan" class="border border-slate-200 bg-white rounded-lg text-xs font-semibold text-slate-700 px-3 py-1 cursor-pointer outline-none">
                                        <option value="">-- Select Quarter --</option>
                                        <template x-for="item in historyList.filter(h => h.type === 'triwulan')" :key="item.bulan + '|' + item.tahun + '|' + item.basis">
                                            <option :value="item.bulan + '|' + item.tahun + '|' + item.basis" x-text="item.bulan + ' ' + item.tahun + ' (' + (item.basis === 'nominal' ? 'Scheme 1' : 'Scheme 2') + ')'"></option>
                                        </template>
                                    </select>
                                    <button type="button" @click="
                                        if (copySourceTriwulan) {
                                            const [cTri, cTah, cBas] = copySourceTriwulan.split('|');
                                            const source = historyList.find(h => h.bulan === cTri && h.tahun === cTah && h.type === 'triwulan' && h.basis === cBas);
                                            if (source) {
                                                formBasisTriwulan = source.basis;
                                                if (source.basis === 'nominal') {
                                                    settingsTriwulanNominal = JSON.parse(JSON.stringify(source.tiers));
                                                } else {
                                                    settingsTriwulanPercent = JSON.parse(JSON.stringify(source.tiers));
                                                }
                                            }
                                        }
                                    " class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition-colors shadow-sm">
                                        Copy
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <div class="flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Target Year</label>
                                    <select name="tahun" x-model="formTahun" class="w-full border border-slate-200 bg-white rounded-lg text-xs font-semibold text-slate-700 px-3 py-1.5 cursor-pointer outline-none focus:ring-blue-500 focus:border-blue-500">
                                        @foreach($listTahun as $t)
                                            <option value="{{ $t }}">{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Target Quarter</label>
                                    <select name="triwulan" x-model="formTriwulan" class="w-full border border-slate-200 bg-white rounded-lg text-xs font-semibold text-slate-700 px-3 py-1.5 cursor-pointer outline-none focus:ring-blue-500 focus:border-blue-500">
                                        @foreach(['Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'] as $q)
                                            <option value="{{ $q }}">{{ $q }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Scheme Model</label>
                                    <select x-model="formBasisTriwulan" class="w-full border border-slate-200 bg-white rounded-lg text-xs font-semibold text-slate-700 px-3 py-1.5 cursor-pointer outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="nominal">Scheme 1 (Nominal Actual Sales Rp)</option>
                                        <option value="percentage">Scheme 2 (Percentage % vs Target)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                            <th class="py-2 px-1 w-6/12" x-text="formBasisTriwulan === 'nominal' ? 'Min. Sales (Rp)' : 'Min. Achievement (%)'"></th>
                                            <th class="py-2 px-1 w-5/12">Bonus Nominal (Rp)</th>
                                            <th class="py-2 px-1 w-1/12 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(tier, index) in (formBasisTriwulan === 'nominal' ? settingsTriwulanNominal : settingsTriwulanPercent)" :key="index">
                                            <tr class="border-b border-slate-100/50">
                                                <td class="py-1.5 px-1">
                                                    <div class="relative">
                                                        <span x-show="formBasisTriwulan === 'nominal'" class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400 text-[11px] font-bold">Rp</span>
                                                        <input type="number" name="min_achievement_triwulan[]" x-model="tier.min_achievement" required class="w-full border border-slate-200 bg-white rounded-lg py-1 text-xs font-semibold text-slate-800 focus:ring-blue-500 focus:border-blue-500 transition-colors" :class="formBasisTriwulan === 'nominal' ? 'pl-7 pr-3' : 'pl-3 pr-6'">
                                                        <span x-show="formBasisTriwulan === 'percentage'" class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400 text-[11px] font-bold">%</span>
                                                    </div>
                                                    <div x-show="formBasisTriwulan === 'nominal' && tier.min_achievement" class="text-[10px] text-blue-600 font-bold mt-0.5 px-1" x-text="formatRupiahShorthand(tier.min_achievement)"></div>
                                                </td>
                                                <td class="py-1.5 px-1">
                                                    <div class="relative">
                                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400 text-[11px] font-bold">Rp</span>
                                                        <input type="number" name="incentive_value_triwulan[]" x-model="tier.incentive_value" required class="w-full border border-slate-200 bg-white rounded-lg pl-7 pr-3 py-1 text-xs font-semibold text-slate-800 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                                    </div>
                                                    <div x-show="tier.incentive_value" class="text-[10px] text-indigo-600 font-bold mt-0.5 px-1" x-text="formatRupiahShorthand(tier.incentive_value)"></div>
                                                </td>
                                                <td class="py-1.5 px-1 text-center">
                                                    <button type="button" @click="(formBasisTriwulan === 'nominal' ? settingsTriwulanNominal : settingsTriwulanPercent).splice(index, 1)" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 inline-flex items-center justify-center transition-colors border border-rose-100">
                                                        <i class="fas fa-trash-can text-[10px]"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <button type="button" @click="(formBasisTriwulan === 'nominal' ? settingsTriwulanNominal : settingsTriwulanPercent).push({min_achievement: 0, incentive_value: 0})" class="self-start inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 font-bold text-[10px] transition-colors shadow-sm">
                                    <i class="fas fa-plus"></i> Add Tier
                                </button>
                            </div>

                            <div class="flex items-center justify-end gap-3 mt-4 pt-3 border-t border-slate-100">
                                <button type="button" @click="showFormModalTriwulan = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition-colors">Cancel</button>
                                <button type="button" @click="
                                    const currentTriwulanList = formBasisTriwulan === 'nominal' ? settingsTriwulanNominal : settingsTriwulanPercent;
                                    const payloadTriwulan = {
                                        _token: '{{ csrf_token() }}', tahun: formTahun, bulan: formTriwulan, type: 'triwulan', basis: formBasisTriwulan,
                                        min_achievement: currentTriwulanList.map(t => t.min_achievement),
                                        incentive_value: currentTriwulanList.map(t => t.incentive_value)
                                    };
                                    fetch('{{ route('sales.incentive.settings.save') }}', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                        body: JSON.stringify(payloadTriwulan)
                                    }).then(res => {
                                        if (res.ok) {
                                            const u = new URL(window.location); u.searchParams.set('tab', 'settings'); window.location.href = u.toString();
                                        } else {
                                            res.json().then(data => alert(data.message || 'Failed to save rules.'));
                                        }
                                    });
                                " class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-colors">
                                    <i class="fas fa-save"></i> Save & Activate
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal History Detail --}}
            <div x-show="showHistoryDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showHistoryDetailModal = false"></div>
                <div class="glass-card w-full max-w-lg shadow-2xl z-10 relative overflow-hidden flex flex-col max-h-[80vh]" 
                     x-transition:enter="transition ease-out duration-300 transform" 
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3 shrink-0">
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-slate-800" x-text="historyDetailTitle"></h3>
                            <p class="text-slate-500 text-xs mt-0.5" x-text="historyDetailSub"></p>
                        </div>
                        <button type="button" @click="showHistoryDetailModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto pr-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                    <th class="py-2 px-1">Min. Target</th>
                                    <th class="py-2 px-1 text-right">Incentive / Bonus Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(tier, idx) in historyDetailTiers" :key="idx">
                                    <tr class="border-b border-slate-100/50 hover:bg-slate-50/50 transition-colors text-xs text-slate-700">
                                        <td class="py-2.5 px-1 font-semibold" x-text="getTierRangeLabel(historyDetailTiers, idx, tier.basis)">
                                        </td>
                                        <td class="py-2.5 px-1 text-right font-black text-emerald-600">
                                            <template x-if="historyDetailType === 'bulan'">
                                                <span>+<span x-text="tier.incentive_value"></span>%</span>
                                            </template>
                                            <template x-if="historyDetailType !== 'bulan'">
                                                <span>Rp <span x-text="new Intl.NumberFormat('id-ID').format(tier.incentive_value)"></span></span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end mt-4 pt-3 border-t border-slate-100 shrink-0">
                        <button type="button" @click="showHistoryDetailModal = false" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layout-users>