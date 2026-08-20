@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Sales Analytics & Target' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    @push('styles')
    <style>
        /* == Modern Back Button (Dark Mode) == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
            color: #e2e8f0;
            font-size: 0.9rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 0px;
            width: fit-content;
        }
        .btn-back-modern:hover { 
            background: rgba(51, 65, 85, 0.95);
            transform: translateY(-2px);
            color: #38bdf8;
        }
        .btn-back-modern .icon-circle {
            width: 32px; height: 32px;
            background: #0f172a;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #38bdf8;
            font-size: 0.85rem;
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover .icon-circle {
            transform: translateX(-3px);
            background: #1e293b;
        }

        .analytics-bg { background-color: #111827; color: #f8fafc; }
        .glass-panel { background: rgba(31, 41, 55, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); padding: 20px; }
        
        .main-tab-btn { padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 0.9rem; color: #9ca3af; transition: all 0.3s; cursor: pointer; border: 1px solid transparent; }
        .main-tab-btn:hover { color: #f8fafc; background: rgba(255,255,255,0.05); }
        .main-tab-btn.active { background: #3b82f6; color: white; border-color: #60a5fa; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4); }
        
        .main-tab-content { display: none; }
        .main-tab-content.active { display: block; animation: fadeIn 0.4s ease; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Power BI Styles */
        .pbi-slicer { background: #1e293b url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") no-repeat right 0.75rem center/1.5em 1.5em; border: 1px solid #334155; border-radius: 10px; padding: 8px 12px; padding-right: 2.5rem; font-size: 0.85rem; color: #f8fafc; font-weight: 600; outline: none; width: 100%; -webkit-appearance: none; -moz-appearance: none; appearance: none; cursor: pointer; }
        .pbi-label { display: block; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
        .pbi-kpi { border-radius: 14px; padding: 18px; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); transition: transform 0.2s; }
        .pbi-kpi:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(0,0,0,0.4); }
        table.pbi-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: right; font-size: 0.8rem; }
        table.pbi-table th { background: #0f172a; padding: 10px 12px; font-weight: 700; color: #38bdf8; border-bottom: 2px solid #334155; text-transform: uppercase; font-size: 0.7rem; }
        table.pbi-table td { padding: 10px 12px; border-bottom: 1px solid #334155; color: #cbd5e1; }
        table.pbi-table td.row-header { text-align: left; font-weight: 700; color: #f8fafc; background: rgba(15, 23, 42, 0.4); }
        .val-good { color: #34d399; font-weight: 700; }
        .val-bad { color: #f87171; font-weight: 700; }
        .val-neutral { color: #fbbf24; font-weight: 700; }

        /* Monitoring Styles (Adapted for Dark Mode) */
        .mod-select { background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 8px 12px; font-size: 0.85rem; color: #f8fafc; outline: none; transition: all 0.2s; height: 42px; width: 100%; }
        .mod-select:focus { border-color: #38bdf8; box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2); }
        
        /* Choices.js Overrides for Dark Mode */
        .choices[data-type*="select-multiple"] .choices__inner, .choices[data-type*="text"] .choices__inner { padding-bottom: 2px !important; padding-top: 2px !important; }
        .choices__inner { border-radius: 10px !important; border: 1px solid rgba(255, 255, 255, 0.1) !important; background: rgba(30, 41, 59, 0.7) !important; padding: 2px 8px !important; min-height: 42px !important; color: #f8fafc !important; }
        .choices__input { color: #f8fafc !important; background: transparent !important; margin-bottom: 0 !important; }
        .choices__list--dropdown { background: #1e293b !important; border: 1px solid #334155 !important; color: #f8fafc !important; z-index: 50 !important; }
        .choices__list--multiple .choices__item { background-color: #0ea5e9 !important; border: none !important; border-radius: 6px !important; font-size: 0.75rem !important; padding: 2px 6px !important; margin-top: 4px !important; }
        .choices[data-type*="select-multiple"] .choices__button { border-left: 1px solid rgba(255,255,255,0.2) !important; margin-left: 5px !important; }
        .choices__list--dropdown .choices__item--selectable.is-highlighted { background-color: rgba(59, 130, 246, 0.2) !important; color: #38bdf8 !important; }
        
        .mon-tab { padding: 8px 16px; border-radius: 8px; font-size: 0.8rem; color: #94a3b8; cursor: pointer; transition: 0.2s; border: 1px solid rgba(255,255,255,0.05); background: rgba(255,255,255,0.02); }
        .mon-tab:hover { background: rgba(255,255,255,0.05); color: #f8fafc; }
        .mon-tab.active { background: #3b82f6; color: white; border-color: #60a5fa; box-shadow: 0 2px 10px rgba(59, 130, 246, 0.3); }
        
        /* Monitoring Table */
        table.data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        table.data-table th { background: rgba(15, 23, 42, 0.8); color: #9ca3af; font-size: 0.75rem; text-transform: uppercase; border-bottom: 2px solid rgba(255,255,255,0.1); padding: 14px 16px; font-weight: 800; position: sticky; top: 0; z-index: 20; letter-spacing: 0.05em; white-space: nowrap; }
        table.data-table td { border-bottom: 1px solid rgba(255,255,255,0.05); padding: 12px 16px; color: #e2e8f0; font-size: 0.85rem; white-space: nowrap; }
        table.data-table tr:last-child td { border-bottom: none; }
        table.data-table tr:hover td { background: rgba(59, 130, 246, 0.1); }
        .sticky-col { position: sticky; left: 0; background: rgba(30, 41, 59, 0.95); z-index: 10; border-right: 2px solid rgba(255,255,255,0.05); font-weight: 700; color: #60a5fa !important; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen analytics-bg relative overflow-hidden">
        <div class="relative z-10 w-full max-w-[1400px] mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col gap-6">

            <a href="{{ route('sales.index') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Dashboard
            </a>

            {{-- HEADER & TABS --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-5 md:p-6 shadow-2xl border border-slate-700 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/20">
                        <i class="fas fa-chart-line text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-black text-white uppercase">Sales Analytics & Target</h1>
                        <p class="text-slate-400 text-sm mt-1">Pusat pemantauan performa penjualan, visualisasi, dan target.</p>
                    </div>
                </div>
                
                {{-- Main Tabs --}}
                <div class="flex gap-2 bg-slate-900/50 p-1.5 rounded-2xl border border-slate-700/50">
                    @php $activeTab = session('active_tab', 'tab-pbi'); @endphp
                    <div class="main-tab-btn {{ $activeTab == 'tab-pbi' ? 'active' : '' }}" data-target="tab-pbi" onclick="switchMainTab('tab-pbi', this)">
                        <i class="fas fa-chart-pie mr-2 text-sky-400"></i> Visualisasi
                    </div>
                    <div class="main-tab-btn {{ $activeTab == 'tab-mon' ? 'active' : '' }}" data-target="tab-mon" onclick="switchMainTab('tab-mon', this)">
                        <i class="fas fa-table-cells mr-2 text-emerald-400"></i> Matrix
                    </div>
                    <div class="main-tab-btn {{ $activeTab == 'tab-tgt' ? 'active' : '' }}" data-target="tab-tgt" onclick="switchMainTab('tab-tgt', this)">
                        <i class="fas fa-bullseye mr-2 text-amber-400"></i> Target Sales
                    </div>
                    <div class="main-tab-btn {{ $activeTab == 'tab-history' ? 'active' : '' }}" data-target="tab-history" onclick="switchMainTab('tab-history', this)">
                        <i class="fas fa-history mr-2 text-purple-400"></i> History Sales
                    </div>
                </div>
            </div>

            <!-- Tab 1 - Visualisasi -->
            <div id="tab-pbi" class="main-tab-content {{ $activeTab == 'tab-pbi' ? 'active' : '' }} space-y-6">
                <div class="glass-panel border-t-2 border-t-sky-500">
                    <form id="filterFormPbi" method="GET" action="{{ route('sales.analytics') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="pbi-label">Tahun</label>
                            <select name="tahun" id="filterTahun" class="pbi-slicer" onchange="this.form.submit()">
                                @foreach($listTahun as $t)
                                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="pbi-label">Sales (PS)</label>
                            <select name="ps" id="filterPs" class="pbi-slicer" onchange="this.form.submit()">
                                <option value="" {{ $psTerpilih == '' ? 'selected' : '' }}>All</option>
                                <option value="Sales Team" {{ $psTerpilih == 'Sales Team' ? 'selected' : '' }}>Sales Team</option>
                                <option value="Office" {{ $psTerpilih == 'Office' ? 'selected' : '' }}>Office</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('sales.analytics') }}" class="w-full text-center py-2 px-4 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs border border-slate-700 transition">
                                <i class="fas fa-undo mr-1"></i> Reset Filter
                            </a>
                        </div>
                    </form>
                </div>

                {{-- KPI Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="pbi-kpi bg-gradient-to-br from-slate-800 to-slate-900 border-l-4 border-l-slate-400">
                        <p class="pbi-label">Total Target ({{ $tahun }})</p>
                        <h3 class="text-xl md:text-2xl font-black text-white mt-1">Rp {{ number_format($summary['total_target'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="pbi-kpi bg-gradient-to-br from-slate-800 to-sky-900 border-l-4 border-l-sky-400">
                        <p class="pbi-label">Total Actual Sales</p>
                        <h3 class="text-xl md:text-2xl font-black text-sky-400 mt-1">Rp {{ number_format($summary['total_sales'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="pbi-kpi bg-gradient-to-br from-slate-800 to-emerald-900 border-l-4 border-l-emerald-400">
                        <p class="pbi-label">Achievement Rate % (Tahun)</p>
                        <h3 class="text-xl md:text-2xl font-black text-emerald-400 mt-1">{{ $summary['overall_achievement'] ?? 0 }}%</h3>
                        <div class="w-full bg-slate-800 rounded-full h-1.5 mt-2 overflow-hidden">
                            <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ min(100, $summary['overall_achievement'] ?? 0) }}%"></div>
                        </div>
                    </div>
                    <div class="pbi-kpi bg-gradient-to-br from-slate-800 to-indigo-900 border-l-4 border-l-indigo-400">
                        <p class="pbi-label">YoY Growth Rate %</p>
                        <h3 class="text-xl md:text-2xl font-black {{ ($summary['overall_growth'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-rose-400' }} mt-1">
                            {{ ($summary['overall_growth'] ?? 0) >= 0 ? '+' : '' }}{{ $summary['overall_growth'] ?? 0 }}%
                        </h3>
                    </div>
                </div>

                {{-- Chart 1 --}}
                <div class="glass-panel">
                    <h2 class="text-base font-black text-white mb-4"><i class="fas fa-chart-line text-sky-400 mr-2"></i> Monthly Achievement Rate & Growth Trend ({{ $tahun }})</h2>
                    <div class="relative h-[350px] w-full"><canvas id="chartMonthlyOverview"></canvas></div>
                </div>

                {{-- Chart 2: PS Achievement Rate & Growth Trend --}}
                <div class="glass-panel mt-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                        <h2 class="text-base font-black text-white"><i class="fas fa-users-viewfinder text-indigo-400 mr-2"></i> PS Achievement Rate & Growth Trend ({{ $tahun }})</h2>
                    </div>
                    <div class="relative h-[350px] w-full">
                        <div id="psRankBox" style="top: -35px; right: 0;" class="absolute bg-slate-800/80 p-2.5 rounded-lg border border-slate-700/60 text-[10px] sm:text-xs text-slate-300 z-20 shadow-lg pointer-events-none hidden backdrop-blur-sm min-w-[140px]">
                            <div class="font-bold text-white mb-1 border-b border-slate-600/50 pb-1">Rank Kontribusi</div>
                            <div id="psRankList" class="flex flex-col gap-1 mt-1"></div>
                        </div>
                        <canvas id="chartPsOverview"></canvas>
                    </div>
                </div>

                {{-- Chart Grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="glass-panel">
                        <h2 class="text-base font-black text-white mb-4"><i class="fas fa-chart-pie text-emerald-400 mr-2"></i> Top 10 Customers Sales Contribution</h2>
                        <div class="relative h-[300px] w-full"><canvas id="chartPsContribution"></canvas></div>
                    </div>
                    <div class="glass-panel">
                        <h2 class="text-base font-black text-white mb-4 flex justify-between items-center">
                            <span><i class="fas fa-chart-column text-purple-400 mr-2"></i> Monthly Sales per PS</span>
                        </h2>
                        <div class="relative h-[300px] w-full"><canvas id="chartMonthlySalesPs"></canvas></div>
                    </div>
                </div>

                <div class="glass-panel">
                    <h2 class="text-base font-black text-white mb-4"><i class="fas fa-boxes-stacked text-amber-400 mr-2"></i> Sales by Product Category per PS</h2>
                    <div class="w-full h-[400px] overflow-y-auto custom-scrollbar pr-2">
                        <div id="productChartContainer" class="relative w-full">
                            <canvas id="chartProductCategoryPs"></canvas>
                        </div>
                    </div>
                </div>

                {{-- POWER BI MATRIX TABLES (DRILL DOWN DATA) --}}
                <div class="glass-panel overflow-hidden" x-data="{ tabTable: 'monthly' }">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 pb-3 border-b border-slate-700/60">
                        <h2 class="text-base font-black text-white flex items-center gap-2">
                            <i class="fas fa-table-cells text-sky-400"></i> Power BI Detail Matrix Data
                        </h2>
                        <div class="flex flex-wrap gap-1 bg-slate-900 p-1 rounded-xl border border-slate-800">
                            <button @click="tabTable = 'monthly'" :class="tabTable === 'monthly' ? 'bg-sky-600 text-white font-bold' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1.5 text-xs rounded-lg transition">
                                Monthly Overview
                            </button>
                            <button @click="tabTable = 'ps_month'" :class="tabTable === 'ps_month' ? 'bg-sky-600 text-white font-bold' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1.5 text-xs rounded-lg transition">
                                Per PS (Bulan Aktif)
                            </button>
                            <button @click="tabTable = 'ps_cum'" :class="tabTable === 'ps_cum' ? 'bg-sky-600 text-white font-bold' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1.5 text-xs rounded-lg transition">
                                Per PS (Cumulative YTD)
                            </button>
                        </div>
                    </div>

                    {{-- TAB 1: MONTHLY OVERVIEW TABLE --}}
                    <div x-show="tabTable === 'monthly'" class="overflow-x-auto">
                        <table class="pbi-table whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th class="text-left">Metric / Bulan</th>
                                    @foreach($listBulan as $b)
                                        <th>{{ $b }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="row-header">Target Sales (IDR)</td>
                                    @foreach($listBulan as $b)
                                        <td>{{ number_format($monthlyOverview[$b]['target'] ?? 0, 0, ',', '.') }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="row-header">Actual Sales (IDR)</td>
                                    @foreach($listBulan as $b)
                                        <td>{{ number_format($monthlyOverview[$b]['sales'] ?? 0, 0, ',', '.') }}</td>
                                    @endforeach
                                </tr>
                                <tr class="bg-sky-950/20">
                                    <td class="row-header">Achievement Rate (%)</td>
                                    @foreach($listBulan as $b)
                                        @php $rate = $monthlyOverview[$b]['achievement_rate'] ?? 0; @endphp
                                        <td class="{{ $rate >= 100 ? 'val-good' : ($rate >= 80 ? 'text-sky-400 font-bold' : ($rate > 0 ? 'val-neutral' : 'text-slate-500')) }}">
                                            {{ $rate }}%
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="row-header">Growth Rate vs Last Year (%)</td>
                                    @foreach($listBulan as $b)
                                        @php $growth = $monthlyOverview[$b]['growth_rate'] ?? 0; @endphp
                                        <td class="{{ $growth > 0 ? 'val-good' : ($growth < 0 ? 'val-bad' : 'text-slate-500') }}">
                                            {{ $growth > 0 ? '+'.$growth : $growth }}%
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- TAB 2: PER PS MONTHLY TABLE --}}
                    <div x-show="tabTable === 'ps_month'" class="overflow-x-auto" style="display: none;">
                        <table class="pbi-table whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th class="text-left">Sales (PS)</th>
                                    <th>Target ({{ $bulanTerpilih ?: 'Terakhir' }})</th>
                                    <th>Sales ({{ $bulanTerpilih ?: 'Terakhir' }})</th>
                                    <th>Achievement Rate</th>
                                    <th>Sales Bulan Lalu</th>
                                    <th>Growth vs Bulan Lalu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($listPs as $ps)
                                    @php $dataPs = $psPerformance[$ps] ?? ['target'=>0, 'sales'=>0, 'achievement_rate'=>0, 'sales_last_month'=>0, 'growth_last_month'=>0]; @endphp
                                    <tr>
                                        <td class="row-header">{{ $ps }}</td>
                                        <td>Rp {{ number_format($dataPs['target'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($dataPs['sales'], 0, ',', '.') }}</td>
                                        <td class="{{ $dataPs['achievement_rate'] >= 100 ? 'val-good' : ($dataPs['achievement_rate'] >= 80 ? 'text-sky-400 font-bold' : ($dataPs['achievement_rate'] > 0 ? 'val-neutral' : 'text-slate-500')) }}">
                                            {{ $dataPs['achievement_rate'] }}%
                                        </td>
                                        <td>Rp {{ number_format($dataPs['sales_last_month'], 0, ',', '.') }}</td>
                                        <td class="{{ $dataPs['growth_last_month'] > 0 ? 'val-good' : ($dataPs['growth_last_month'] < 0 ? 'val-bad' : 'text-slate-500') }}">
                                            {{ $dataPs['growth_last_month'] > 0 ? '+'.$dataPs['growth_last_month'] : $dataPs['growth_last_month'] }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- TAB 3: PER PS CUMULATIVE TABLE --}}
                    <div x-show="tabTable === 'ps_cum'" class="overflow-x-auto" style="display: none;">
                        <table class="pbi-table whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th class="text-left">Sales (PS)</th>
                                    <th>Cumulative Target</th>
                                    <th>Cumulative Sales</th>
                                    <th>Cum. Achievement Rate</th>
                                    <th>YoY Cum. Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($listPs as $ps)
                                    @php $cumData = $psCumulative[$ps] ?? ['cum_target'=>0, 'cum_sales'=>0, 'cum_ach_rate'=>0, 'cum_growth_rate'=>0]; @endphp
                                    <tr>
                                        <td class="row-header">{{ $ps }}</td>
                                        <td>Rp {{ number_format($cumData['cum_target'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($cumData['cum_sales'], 0, ',', '.') }}</td>
                                        <td class="{{ $cumData['cum_ach_rate'] >= 100 ? 'val-good' : ($cumData['cum_ach_rate'] >= 80 ? 'text-sky-400 font-bold' : ($cumData['cum_ach_rate'] > 0 ? 'val-neutral' : 'text-slate-500')) }}">
                                            {{ $cumData['cum_ach_rate'] }}%
                                        </td>
                                        <td class="{{ $cumData['cum_growth_rate'] > 0 ? 'val-good' : ($cumData['cum_growth_rate'] < 0 ? 'val-bad' : 'text-slate-500') }}">
                                            {{ $cumData['cum_growth_rate'] > 0 ? '+'.$cumData['cum_growth_rate'] : $cumData['cum_growth_rate'] }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 2 - Matrix -->
            <div id="tab-mon" class="main-tab-content {{ $activeTab == 'tab-mon' ? 'active' : '' }} space-y-6">
                {{-- Filter Bar Monitoring --}}
                <div class="glass-panel border-t-2 border-t-emerald-500 relative z-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 items-end">
                        <div>
                            <label class="pbi-label">Tahun</label>
                            <select id="m-tahun" class="pbi-slicer">
                                <option value="">All</option>
                                @foreach($listTahun as $t) <option value="{{ $t }}" {{ $t == date('Y') ? 'selected' : '' }}>{{ $t }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="pbi-label">Bulan</label>
                            <select id="m-bulan" class="pbi-slicer">
                                <option value="">All (YTD)</option>
                                @foreach($listBulan as $b) <option value="{{ $b }}">{{ $b }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="pbi-label">Customer</label>
                            <select id="m-customer" class="pbi-slicer">
                                <option value="">All</option>
                                @foreach($listCustomer as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="pbi-label">Sales (PS)</label>
                            <select id="m-ps" class="pbi-slicer">
                                <option value="">All</option>
                                <option value="Sales Team">Sales Team</option>
                                <option value="Office">Office</option>
                            </select>
                        </div>
                        <div>
                            <label class="pbi-label">Produk</label>
                            <select id="m-produk" class="pbi-slicer">
                                <option value="">All</option>
                                @foreach($listProduk as $p) <option value="{{ $p }}">{{ $p }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="glass-panel text-center py-4">
                        <p class="text-xs text-slate-400 uppercase font-bold">Total Sales Nett</p>
                        <h3 id="m-sum-nett" class="text-2xl font-black text-emerald-400 mt-1">Rp 0</h3>
                    </div>
                    <div class="glass-panel text-center py-4">
                        <p class="text-xs text-slate-400 uppercase font-bold">Total Qty</p>
                        <h3 id="m-sum-qty" class="text-2xl font-black text-blue-400 mt-1">0</h3>
                    </div>
                    <div class="glass-panel text-center py-4">
                        <p class="text-xs text-slate-400 uppercase font-bold">Total Customer</p>
                        <h3 id="m-sum-customer" class="text-2xl font-black text-indigo-400 mt-1">0</h3>
                    </div>
                    <div class="glass-panel text-center py-4">
                        <p class="text-xs text-slate-400 uppercase font-bold">Total Produk</p>
                        <h3 id="m-sum-produk" class="text-2xl font-black text-purple-400 mt-1">0</h3>
                    </div>
                    <div class="glass-panel text-center py-4">
                        <p class="text-xs text-slate-400 uppercase font-bold">Achievement Rate (YTD)</p>
                        <h3 id="m-sum-rate" class="text-2xl font-black text-amber-400 mt-1">-</h3>
                    </div>
                </div>

                {{-- Tabel Matrix --}}
                <div class="glass-panel !p-0 overflow-hidden">
                    <div class="flex flex-wrap gap-8 px-6 pt-6 border-b border-slate-700 pb-4">
                        <div class="mon-tab active" data-mtab="tab-customer">By Customer</div>
                        <div class="mon-tab" data-mtab="tab-customer-produk">By Customer & Produk</div>
                        <div class="mon-tab" data-mtab="tab-produk">By Produk</div>
                        <div class="mon-tab" data-mtab="tab-produk-ps">By Produk & PS</div>
                        <div class="mon-tab" data-mtab="tab-ps">By Sales (PS)</div>
                        <div class="mon-tab" data-mtab="tab-forecast">Sales Forecast</div>
                    </div>
                    <div class="p-0">
                        <div id="tab-customer" class="mon-panel p-0 overflow-x-auto max-h-[500px]">
                            <table class="data-table w-full text-xs">
                                <thead id="head-customer"></thead>
                                <tbody id="body-customer"></tbody>
                            </table>
                        </div>
                        <div id="tab-customer-produk" class="mon-panel p-0 overflow-x-auto max-h-[500px] hidden">
                            <table class="data-table w-full text-xs">
                                <thead id="head-customer-produk"></thead>
                                <tbody id="body-customer-produk"></tbody>
                            </table>
                        </div>
                        <div id="tab-produk" class="mon-panel p-0 overflow-x-auto max-h-[500px] hidden">
                            <table class="data-table w-full text-xs">
                                <thead id="head-produk"></thead>
                                <tbody id="body-produk"></tbody>
                            </table>
                        </div>
                        <div id="tab-produk-ps" class="mon-panel p-0 overflow-x-auto max-h-[500px] hidden">
                            <table class="data-table w-full text-xs">
                                <thead id="head-produk-ps"></thead>
                                <tbody id="body-produk-ps"></tbody>
                            </table>
                        </div>
                        <div id="tab-ps" class="mon-panel p-0 overflow-x-auto max-h-[500px] hidden">
                            <table class="data-table w-full text-xs">
                                <thead id="head-ps"></thead>
                                <tbody id="body-ps"></tbody>
                            </table>
                        </div>
                        <div id="tab-forecast" class="mon-panel p-6 overflow-x-auto max-h-[500px] hidden">
                            <table class="data-table w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left">Nama Produk</th>
                                        <th class="text-right">Rata-rata Qty / Bulan</th>
                                        <th class="text-right">Estimasi Stok Depan (+20%)</th>
                                    </tr>
                                </thead>
                                <tbody id="body-forecast"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3 - Target Sales -->
            <div id="tab-tgt" class="main-tab-content {{ $activeTab == 'tab-tgt' ? 'active' : '' }} space-y-6">
                <div class="flex flex-col md:flex-row justify-end items-start md:items-center gap-4 mb-6">
                    <button onclick="document.getElementById('modal-target').classList.remove('hidden')" class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition shadow-lg shadow-amber-500/20">
                        <i class="fas fa-plus mr-1"></i> Set Target Individu
                    </button>
                </div>
                
                {{-- Target Keseluruhan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="glass-panel text-center py-5 border-t-2 border-t-indigo-400">
                        <p class="text-xs text-slate-400 uppercase font-bold">Target Keseluruhan (Tahun Ini)</p>
                        <h3 class="text-xl font-black text-indigo-400 mt-1">Rp {{ number_format($summary['total_target'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="glass-panel text-center py-5 border-t-2 border-t-sky-400">
                        <p class="text-xs text-slate-400 uppercase font-bold">Target Keseluruhan (Bulan Ini)</p>
                        <h3 class="text-xl font-black text-sky-400 mt-1">Rp {{ number_format($monthlyAll[date('n') > 0 ? $urutanBulan[date('n')-1] : 'Januari']['target'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="glass-panel text-center py-5 border-t-2 border-t-emerald-400">
                        <p class="text-xs text-slate-400 uppercase font-bold">Total Actual Sales (YTD)</p>
                        <h3 class="text-xl font-black text-emerald-400 mt-1">Rp {{ number_format($summary['total_sales'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="glass-panel text-center py-5 border-t-2 border-t-amber-400">
                        <p class="text-xs text-slate-400 uppercase font-bold">Achievement Rate % (Tahun)</p>
                        <h3 class="text-xl font-black text-amber-400 mt-1">{{ $summary['overall_achievement'] ?? 0 }}%</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Target Bulanan Keseluruhan --}}
                    <div class="glass-panel">
                        <h3 class="text-sm font-black text-white mb-4"><i class="fas fa-calendar-alt text-sky-400 mr-2"></i> Rekap Target per Bulan</h3>
                        <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
                            <table class="w-full text-sm text-left whitespace-nowrap">
                                <thead class="text-xs text-slate-400 uppercase bg-slate-800/50 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-3 rounded-tl-lg">Bulan</th>
                                        <th class="px-4 py-3 text-right">Target</th>
                                        <th class="px-4 py-3 text-right">Actual</th>
                                        <th class="px-4 py-3 text-center rounded-tr-lg">Achv %</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700/50">
                                    @foreach($urutanBulan as $bulan)
                                        @php
                                            $d = $monthlyAll[$bulan] ?? ['target'=>0, 'sales'=>0, 'achievement_rate'=>0];
                                        @endphp
                                        <tr class="hover:bg-slate-800/30 transition">
                                            <td class="px-4 py-3 font-bold text-slate-200">{{ $bulan }}</td>
                                            <td class="px-4 py-3 text-right text-slate-300">Rp {{ number_format($d['target'],0,',','.') }}</td>
                                            <td class="px-4 py-3 text-right text-emerald-400 font-bold">Rp {{ number_format($d['sales'],0,',','.') }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 py-1 rounded text-xs font-bold {{ $d['achievement_rate'] >= 100 ? 'bg-emerald-900/50 text-emerald-400' : 'bg-amber-900/50 text-amber-400' }}">
                                                    {{ $d['achievement_rate'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Target Individu (Per PS) --}}
                    <div class="glass-panel">
                        <h3 class="text-sm font-black text-white mb-4"><i class="fas fa-user-tie text-emerald-400 mr-2"></i> Target Individu (PS) - Keseluruhan</h3>
                        <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
                            <table class="w-full text-sm text-left whitespace-nowrap">
                                <thead class="text-xs text-slate-400 uppercase bg-slate-800/50 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-3 rounded-tl-lg">PS</th>
                                        <th class="px-4 py-3 text-right">Target Bulan Ini</th>
                                        <th class="px-4 py-3 text-right">Actual Bulan Ini</th>
                                        <th class="px-4 py-3 text-center">Achv % (Bulan)</th>
                                        <th class="px-4 py-3 text-right">Target 1 Tahun</th>
                                        <th class="px-4 py-3 text-right">Actual YTD</th>
                                        <th class="px-4 py-3 text-center">Achv % (YTD)</th>
                                        <th class="px-4 py-3 text-center rounded-tr-lg">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700/50">
                                    @foreach($listPs as $ps)
                                        @php
                                            $d = $allPsAchievement[$ps] ?? ['target'=>0, 'sales'=>0, 'rate'=>0];
                                            $currentMonthName = date('n') > 0 ? $urutanBulan[date('n')-1] : 'Januari';
                                            
                                            $psMonthlyData = $monthlyPerPs[$currentMonthName][$ps] ?? ['target' => 0, 'sales' => 0, 'rate' => 0];
                                            $targetBulanIni = $psMonthlyData['target'] ?? 0;
                                            $actualBulanIni = $psMonthlyData['sales'] ?? 0;
                                            $rateBulanIni = $psMonthlyData['rate'] ?? 0;
                                        @endphp
                                        <tr class="hover:bg-slate-800/30 transition">
                                            <td class="px-4 py-3 font-bold text-slate-200">{{ $ps }}</td>
                                            <td class="px-4 py-3 text-right text-sky-400">Rp {{ number_format($targetBulanIni,0,',','.') }}</td>
                                            <td class="px-4 py-3 text-right text-emerald-400 font-bold">Rp {{ number_format($actualBulanIni,0,',','.') }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 py-1 rounded text-xs font-bold {{ $rateBulanIni >= 100 ? 'bg-emerald-900/50 text-emerald-400' : 'bg-amber-900/50 text-amber-400' }}">
                                                    {{ $rateBulanIni }}%
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-slate-300">Rp {{ number_format($d['target'],0,',','.') }}</td>
                                            <td class="px-4 py-3 text-right text-emerald-400 font-bold">Rp {{ number_format($d['sales'],0,',','.') }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 py-1 rounded text-xs font-bold {{ $d['rate'] >= 100 ? 'bg-emerald-900/50 text-emerald-400' : 'bg-amber-900/50 text-amber-400' }}">
                                                    {{ $d['rate'] }}%
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button class="text-sky-400 hover:text-sky-300 transition" onclick="editTarget('{{ $ps }}')" title="Set Target Individu"><i class="fas fa-edit"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Detail Target Bulanan per PS --}}
                <div class="glass-panel">
                    <h3 class="text-sm font-black text-white mb-4"><i class="fas fa-table text-purple-400 mr-2"></i> Detail Target Bulanan per PS</h3>
                    <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
                        <table class="w-full text-xs text-center whitespace-nowrap">
                            <thead class="text-[10px] text-slate-400 uppercase bg-slate-800/50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-lg text-left">Sales (PS)</th>
                                    @foreach($urutanBulan as $b)
                                        <th class="px-3 py-3 border-l border-slate-700/50">{{ substr($b, 0, 3) }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 font-bold text-sky-400 border-l border-slate-700/50">Total 1 Tahun</th>
                                    <th class="px-4 py-3 font-bold text-slate-400 border-l border-slate-700/50 rounded-tr-lg">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                @php
                                    // Build Matrix
                                    $matrixTarget = [];
                                    foreach($listPs as $psName) {
                                        $matrixTarget[$psName] = array_fill_keys($urutanBulan, 0);
                                    }
                                    if(isset($targets) && is_iterable($targets)) {
                                        foreach($targets as $t) {
                                            if(isset($matrixTarget[$t->ps][$t->bulan])) {
                                                $matrixTarget[$t->ps][$t->bulan] = $t->target_amount;
                                            }
                                        }
                                    }
                                @endphp
                                
                                @foreach($listPs as $psName)
                                    <tr class="hover:bg-slate-800/30 transition">
                                        <td class="px-4 py-3 text-left font-bold text-slate-200">{{ $psName }}</td>
                                        @php $rowTotal = 0; @endphp
                                        @foreach($urutanBulan as $b)
                                            @php 
                                                $val = $matrixTarget[$psName][$b]; 
                                                $rowTotal += $val;
                                            @endphp
                                            <td class="px-3 py-3 border-l border-slate-700/50 {{ $val > 0 ? 'text-emerald-400 font-bold' : 'text-slate-600' }}">
                                                {{ $val > 0 ? number_format($val, 0, ',', '.') : '-' }}
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 font-black text-sky-400 border-l border-slate-700/50">
                                            Rp {{ number_format($rowTotal, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-center border-l border-slate-700/50">
                                            <button class="text-sky-400 hover:text-sky-300 transition" onclick="editTarget('{{ $psName }}')" title="Edit Target Bulanan">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 4 - History Sales -->
            <div id="tab-history" class="main-tab-content {{ $activeTab == 'tab-history' ? 'active' : '' }} space-y-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-black text-white"><i class="fas fa-history text-purple-400 mr-2"></i> Riwayat Sales Tahun Sebelumnya</h2>
                </div>

                <div class="glass-panel">
                    <p class="text-sm text-slate-400 mb-4">Data di tabel ini dihitung secara otomatis dari <b>akumulasi transaksi aktual penjualan</b> pada tahun-tahun sebelumnya.</p>
                    
                    <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
                        <table class="w-full text-xs text-center whitespace-nowrap">
                            <thead class="text-[10px] text-slate-400 uppercase bg-slate-800/50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-lg text-left">Tahun Riwayat</th>
                                    @foreach($urutanBulan as $b)
                                        <th class="px-3 py-3 border-l border-slate-700/50">{{ substr($b, 0, 3) }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 font-bold text-purple-400 border-l border-slate-700/50 rounded-tr-lg">Total 1 Tahun</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                @if($historySales && $historySales->count() > 0)
                                    @foreach($historySales as $history)
                                        <tr class="hover:bg-slate-800/30 transition">
                                            <td class="px-4 py-4 font-bold text-slate-200 text-left text-sm">{{ $history->tahun }}</td>
                                            @php 
                                                $totalHistory = 0; 
                                                $months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
                                            @endphp
                                            @foreach($months as $m)
                                                @php
                                                    $monthHistory = $history->$m ?? 0;
                                                    $totalHistory += $monthHistory;
                                                @endphp
                                                <td class="px-3 py-4 text-slate-300 border-l border-slate-700/30 {{ $monthHistory > 0 ? 'font-bold text-emerald-400' : '' }}">
                                                    {{ $monthHistory > 0 ? 'Rp ' . number_format($monthHistory, 0, ',', '.') : '-' }}
                                                </td>
                                            @endforeach
                                            <td class="px-4 py-4 font-black text-purple-400 bg-purple-900/10 border-l border-slate-700/50 text-sm">
                                                Rp {{ number_format($totalHistory, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="14" class="px-4 py-12 text-center text-slate-500 italic">Belum ada data riwayat penjualan.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('modals')
    {{-- Modal Set Target --}}
    <div id="modal-target" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" onclick="document.getElementById('modal-target').classList.add('hidden')"></div>
        <div class="relative bg-slate-800 border border-slate-600 rounded-2xl w-full max-w-xl p-6 shadow-2xl z-10 max-h-[85vh] overflow-y-auto">
            <h3 class="text-xl font-black text-white mb-6 border-b border-slate-700 pb-4">Set Target Sales</h3>
            <form action="{{ route('sales.target.store') }}" method="POST">
                @csrf
                <input type="hidden" name="form_type" value="target">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Tahun</label>
                            <input type="number" id="target_tahun" name="tahun" value="{{ $tahun }}" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg p-3 outline-none focus:border-sky-500 transition" required>
                            <span class="text-[10px] text-slate-500 mt-1 block">Tahun target</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Sales (PS)</label>
                            <div class="relative">
                                <select name="ps" id="target_ps" class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg p-3 appearance-none outline-none focus:border-sky-500 transition" required onchange="populateTargetInputs(this.value)">
                                    <option value="">-- Pilih PS --</option>
                                    @foreach($listPs as $p)
                                        <option value="{{ $p }}">{{ $p }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-3">Target per Bulan (Rp)</label>
                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($urutanBulan as $idx => $b)
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">{{ $b }}</label>
                                    <input type="text" id="display_target_{{ $idx }}" placeholder="0" class="w-full bg-slate-900 border border-slate-700 text-emerald-400 rounded-md p-2 outline-none focus:border-sky-500 transition text-sm font-bold" oninput="formatCurrency(this, 'raw_target_{{ $idx }}')">
                                    <input type="hidden" name="targets[{{ $b }}]" id="raw_target_{{ $idx }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-8 pt-4 border-t border-slate-700 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-target').classList.add('hidden')" class="px-4 py-2 text-slate-300 hover:text-white">Batal</button>
                    <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-lg font-bold transition">Simpan Target</button>
                </div>
            </form>
        </div>
    </div>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1"></script>
    <script>
        // Main Tab Switcher
        function switchMainTab(targetId, btnEl) {
            document.querySelectorAll('.main-tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.main-tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(targetId).classList.add('active');
            btnEl.classList.add('active');
        }

        // Currency Formatter
        function formatCurrency(inputEl, hiddenId) {
            // Remove non-numeric characters
            let rawValue = inputEl.value.replace(/[^0-9]/g, '');
            
            // Set hidden field value
            document.getElementById(hiddenId).value = rawValue;
            
            // Format and display in text field
            if (rawValue) {
                inputEl.value = new Intl.NumberFormat('id-ID').format(rawValue);
            } else {
                inputEl.value = '';
            }
        }

        const allTargetsData = @json($targets ?? []);
        const targetUrutanBulan = @json($urutanBulan ?? []);

        function populateTargetInputs(psName) {
            // clear first
            targetUrutanBulan.forEach((b, idx) => {
                let display = document.getElementById('display_target_' + idx);
                let raw = document.getElementById('raw_target_' + idx);
                if (display) display.value = '';
                if (raw) raw.value = '';
            });
            
            if(!psName) return;
            
            // find targets for this PS
            let psTargets = allTargetsData.filter(t => t.ps === psName);
            psTargets.forEach(t => {
                let idx = targetUrutanBulan.indexOf(t.bulan);
                if(idx !== -1 && t.target_amount > 0) {
                    let display = document.getElementById('display_target_' + idx);
                    let raw = document.getElementById('raw_target_' + idx);
                    if (display) display.value = new Intl.NumberFormat('id-ID').format(t.target_amount);
                    if (raw) raw.value = t.target_amount;
                }
            });
        }

        // Edit Target
        function editTarget(psName) {
            document.getElementById('modal-target').classList.remove('hidden');
            let psSelect = document.querySelector('#modal-target select[name="ps"]');
            if (psSelect) {
                psSelect.value = psName;
                populateTargetInputs(psName);
            }
        }

        // Monitoring Matrix Sub-Tabs
        document.querySelectorAll('.mon-tab').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.mon-tab').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.mon-panel').forEach(p => p.classList.add('hidden'));
                this.classList.add('active');
                document.getElementById(this.dataset.mtab).classList.remove('hidden');
            });
        });

        // Setup Power BI Charts (Chart.js)
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.08)';
            if (typeof ChartDataLabels !== 'undefined') {
                Chart.register(ChartDataLabels);
                Chart.defaults.plugins.datalabels = { display: false };
            }

            @if(isset($monthlyOverview))
                const listBulan = @json($listBulan);
                const listPs = @json($listPs);
                const monthlyOverview = @json($monthlyOverview);
                const psPerformance = @json($psPerformance);
                const psCumulative = @json($psCumulative);

                const allPsPerformanceByMonth = @json($allPsPerformanceByMonth);

                // PBI Chart 1: Overview
                const targetData1 = listBulan.map(b => monthlyOverview[b].target);
                const salesData1 = listBulan.map(b => monthlyOverview[b].sales);
                const achvData1 = listBulan.map(b => monthlyOverview[b].achievement_rate);
                const growthData1 = listBulan.map(b => monthlyOverview[b].growth_rate);

                new Chart(document.getElementById('chartMonthlyOverview'), {
                    type: 'bar',
                    data: {
                        labels: listBulan,
                        datasets: [
                            { 
                                label: 'Target', data: targetData1, backgroundColor: '#fbbf24', borderRadius: 4, order: 2,
                                datalabels: { display: false } 
                            },
                            { 
                                label: 'Sales', data: salesData1, backgroundColor: '#3b82f6', borderRadius: 4, order: 2,
                                datalabels: { 
                                    display: true, align: 'top', anchor: 'end', offset: 4, color: '#e2e8f0', font: { size: 10 },
                                    formatter: (v) => v > 0 ? new Intl.NumberFormat('id-ID').format(v) : ''
                                }
                            },
                            { 
                                label: 'Achv %', data: salesData1, type: 'line', borderColor: '#f43f5e', borderWidth: 3, pointBackgroundColor: '#f43f5e', pointBorderColor: '#fff', pointRadius: 5, fill: false, order: 1,
                                datalabels: { 
                                    display: true, align: 'bottom', anchor: 'center', offset: 6, color: '#f43f5e', font: { weight: 'bold', size: 11 },
                                    backgroundColor: 'rgba(30, 41, 59, 0.7)', borderRadius: 4,
                                    formatter: (v, ctx) => achvData1[ctx.dataIndex] + '%'
                                }
                            },
                            { 
                                label: 'Growth %', data: growthData1, yAxisID: 'y1', type: 'line', borderColor: '#a855f7', borderWidth: 3, pointBackgroundColor: '#a855f7', pointBorderColor: '#fff', pointRadius: 5, fill: false, order: 1,
                                datalabels: { 
                                    display: true, align: 'bottom', anchor: 'center', offset: 22, color: '#a855f7', font: { weight: 'bold', size: 11 },
                                    backgroundColor: 'rgba(30, 41, 59, 0.7)', borderRadius: 4,
                                    formatter: (v) => (v > 0 ? '+' : '') + v + '%'
                                }
                            }
                        ]
                    },
                    options: { 
                        responsive: true, maintainAspectRatio: false, 
                        scales: { 
                            y: { type: 'linear', position: 'left', beginAtZero: true },
                            y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } }
                        },
                        layout: { padding: { top: 30 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.dataset.label === 'Achv %') {
                                            label += achvData1[context.dataIndex] + '%';
                                        } else if (context.dataset.label === 'Growth %') {
                                            let g = growthData1[context.dataIndex];
                                            label += (g > 0 ? '+' : '') + g + '%';
                                        } else {
                                            label += new Intl.NumberFormat('id-ID').format(context.raw);
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });

                // PBI Chart: PS Performance (Cumulative)
                let chartPsOverview = null;
                
                function renderChartPsOverview() {
                    const ctx = document.getElementById('chartPsOverview');
                    if (!ctx) return;
                    
                    const activeLabels = listPs.filter(ps => {
                        const data = psCumulative[ps];
                        return data && (data.cum_target > 0 || data.cum_sales > 0);
                    });
                    
                    const targetData2 = activeLabels.map(ps => psCumulative[ps].cum_target);
                    const salesData2 = activeLabels.map(ps => psCumulative[ps].cum_sales);
                    const achvData2 = activeLabels.map(ps => psCumulative[ps].cum_ach_rate);
                    const growthData2 = activeLabels.map(ps => psCumulative[ps].cum_growth_rate);

                    const rankBox = document.getElementById('psRankBox');
                    const rankList = document.getElementById('psRankList');
                    if (rankBox && rankList && activeLabels.length > 0) {
                        let rankData = activeLabels.map((ps, idx) => ({ name: ps, sales: salesData2[idx] }));
                        rankData.sort((a, b) => b.sales - a.sales);
                        let totalSalesAll = rankData.reduce((sum, item) => sum + item.sales, 0);
                        let html = '';
                        rankData.forEach((item, idx) => {
                            let pct = totalSalesAll > 0 ? ((item.sales / totalSalesAll) * 100).toFixed(1) : 0;
                            let color = idx === 0 ? 'text-amber-400' : (idx === 1 ? 'text-slate-300' : (idx === 2 ? 'text-amber-600' : 'text-emerald-400'));
                            html += `<div class="flex justify-between gap-3 items-center">
                                <span class="font-medium">${idx + 1}. ${item.name}</span>
                                <span class="${color} font-bold">${pct}%</span>
                            </div>`;
                        });
                        rankList.innerHTML = html;
                        rankBox.classList.remove('hidden');
                    }

                    if (chartPsOverview) chartPsOverview.destroy();

                    chartPsOverview = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: activeLabels,
                            datasets: [
                                { label: 'Target', data: targetData2, backgroundColor: '#fbbf24', borderRadius: 4, order: 2, datalabels: { display: false } },
                                { 
                                    label: 'Sales', data: salesData2, backgroundColor: '#10b981', borderRadius: 4, order: 2,
                                    datalabels: { 
                                        display: true, align: 'top', anchor: 'end', offset: 4, color: '#e2e8f0', font: { size: 10 },
                                        textAlign: 'center',
                                        formatter: (v) => {
                                            if (v > 0) {
                                                let formatted = new Intl.NumberFormat('id-ID').format(v);
                                                let total = salesData2.reduce((a, b) => a + b, 0);
                                                let pct = total > 0 ? ((v / total) * 100).toFixed(1) + '%' : '0%';
                                                return [formatted, '(' + pct + ' Kontribusi)'];
                                            }
                                            return '';
                                        }
                                    } 
                                },
                                { 
                                    label: 'Achv %', data: salesData2, type: 'line', borderColor: '#f43f5e', borderWidth: 3, pointBackgroundColor: '#f43f5e', pointBorderColor: '#fff', pointRadius: 5, fill: false, order: 1,
                                    datalabels: { 
                                        display: true, align: 'bottom', anchor: 'center', offset: 6, color: '#f43f5e', font: { weight: 'bold', size: 11 },
                                        backgroundColor: 'rgba(30, 41, 59, 0.7)', borderRadius: 4,
                                        formatter: (v, ctx) => achvData2[ctx.dataIndex] + '%'
                                    }
                                },
                                { 
                                    label: 'Growth %', data: growthData2, yAxisID: 'y1', type: 'line', borderColor: '#a855f7', borderWidth: 3, pointBackgroundColor: '#a855f7', pointBorderColor: '#fff', pointRadius: 5, fill: false, order: 1,
                                    datalabels: { 
                                        display: true, align: 'bottom', anchor: 'center', offset: 22, color: '#a855f7', font: { weight: 'bold', size: 11 },
                                        backgroundColor: 'rgba(30, 41, 59, 0.7)', borderRadius: 4,
                                        formatter: (v) => (v > 0 ? '+' : '') + v + '%'
                                    }
                                }
                            ]
                        },
                        options: { 
                            responsive: true, maintainAspectRatio: false, 
                            scales: { 
                                y: { type: 'linear', position: 'left', beginAtZero: true },
                                y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } }
                            },
                            layout: { padding: { top: 30 } },
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) label += ': ';
                                            if (context.dataset.label === 'Achv %') {
                                                label += achvData2[context.dataIndex] + '%';
                                            } else if (context.dataset.label === 'Growth %') {
                                                let g = growthData2[context.dataIndex];
                                                label += (g > 0 ? '+' : '') + g + '%';
                                            } else if (context.dataset.label === 'Sales') {
                                                label += new Intl.NumberFormat('id-ID').format(context.raw);
                                                let totalSales = salesData2.reduce((a, b) => a + b, 0);
                                                let pct = totalSales > 0 ? ((context.raw / totalSales) * 100).toFixed(1) : 0;
                                                label += ` (${pct}% Kontribusi)`;
                                            } else {
                                                label += new Intl.NumberFormat('id-ID').format(context.raw);
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                
                renderChartPsOverview();


                // PBI Chart 2: Top Customers Contribution (Doughnut)
                const psColors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981', '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#f43f5e', '#64748b'];
                const topCustomers = @json($topCustomers ?? []);
                
                new Chart(document.getElementById('chartPsContribution'), {
                    type: 'doughnut',
                    data: {
                        labels: topCustomers.map(c => c.nama_customer.length > 20 ? c.nama_customer.substr(0, 17) + '...' : c.nama_customer),
                        datasets: [{
                            data: topCustomers.map(c => c.total_nett),
                            backgroundColor: topCustomers.map((_, i) => psColors[i % psColors.length]),
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: { 
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            datalabels: { display: false },
                            legend: { position: 'right', labels: { color: '#cbd5e1', font: { size: 11 } } },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const val = context.raw;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const perc = total > 0 ? Math.round((val / total) * 100) : 0;
                                        return context.label + ': ' + new Intl.NumberFormat('id-ID').format(val) + ' (' + perc + '%)';
                                    }
                                }
                            }
                        }
                    }
                });

                // PBI Chart 3: Monthly Sales PS
                new Chart(document.getElementById('chartMonthlySalesPs'), {
                    type: 'bar',
                    data: {
                        labels: listBulan,
                        datasets: listPs.map((ps, i) => ({
                            label: ps,
                            data: listBulan.map(b => psCumulative[ps]?.monthly_sales[b]||0),
                            backgroundColor: psColors[i % psColors.length],
                            datalabels: { display: false }
                        }))
                    },
                    options: { 
                        responsive: true, maintainAspectRatio: false, 
                        scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => context.dataset.label + ': ' + new Intl.NumberFormat('id-ID').format(context.raw)
                                }
                            }
                        }
                    }
                });

                // PBI Chart 4: Product Category PS
                const topProductCategoryPs = @json($topProductCategoryPs ?? []);
                const productLabels = topProductCategoryPs.map(p => p.nama_produk.length > 35 ? p.nama_produk.substr(0, 32) + '...' : p.nama_produk);
                
                // Set dynamic height based on number of products (e.g. 22px per product, minimum 350px)
                const dynamicHeight = Math.max(350, topProductCategoryPs.length * 22);
                document.getElementById('productChartContainer').style.height = dynamicHeight + 'px';

                new Chart(document.getElementById('chartProductCategoryPs'), {
                    type: 'bar',
                    data: {
                        labels: productLabels,
                        datasets: listPs.map((ps, i) => ({
                            label: ps,
                            data: topProductCategoryPs.map(p => p.per_ps[ps] || 0),
                            backgroundColor: psColors[i % psColors.length],
                            datalabels: { display: false }
                        }))
                    },
                    options: { 
                        indexAxis: 'y', 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        scales: { x:{stacked:true}, y:{stacked:true} },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => context.dataset.label + ': ' + new Intl.NumberFormat('id-ID').format(context.raw)
                                }
                            }
                        }
                    }
                });
            @endif

            // Monitoring Matrix Logic
            let choicesInstances = [];
            document.querySelectorAll('.choices-multiple').forEach(el => {
                choicesInstances.push(new Choices(el, { removeItemButton: true, placeholderValue: 'Pilih...' }));
            });

            function formatRupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n||0)); }
            function formatNum(n) { return new Intl.NumberFormat('id-ID').format(n||0); }

            function loadMonitoring() {
                const params = new URLSearchParams();
                if(document.getElementById('m-tahun').value) params.append('tahun', document.getElementById('m-tahun').value);
                Array.from(document.getElementById('m-bulan').selectedOptions).forEach(o => params.append('bulan[]', o.value));
                Array.from(document.getElementById('m-customer').selectedOptions).forEach(o => params.append('nama_customer[]', o.value));
                Array.from(document.getElementById('m-ps').selectedOptions).forEach(o => params.append('ps[]', o.value));
                Array.from(document.getElementById('m-produk').selectedOptions).forEach(o => params.append('nama_produk[]', o.value));

                fetch("{{ route('sales.monitoring.data') }}?" + params.toString())
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('m-sum-nett').innerText = formatRupiah(data.summary.total_nett);
                        document.getElementById('m-sum-qty').innerText = formatNum(data.summary.total_qty);
                        document.getElementById('m-sum-customer').innerText = formatNum(data.summary.total_customer);
                        document.getElementById('m-sum-produk').innerText = formatNum(data.summary.total_produk);
                        document.getElementById('m-sum-rate').innerText = data.summary.achievement_rate !== null ? data.summary.achievement_rate + '%' : 'Belum ada target';

                        const listBulan = data.list_bulan;
                        
                        function buildTable(bodyId, headId, rows, labelCol, nameKey) {
                            let th = `<tr><th class="text-left sticky-col">${labelCol}</th><th class="text-right text-emerald-400">Total Nett</th>`;
                            listBulan.forEach(b => th += `<th class="text-right">${b.substring(0,3)}</th>`);
                            th += `</tr>`;
                            document.getElementById(headId).innerHTML = th;

                            let html = rows.map(r => {
                                let td = `<tr><td class="sticky-col font-bold text-white">${r[nameKey]||'-'}</td><td class="text-right text-emerald-300 font-bold">${formatRupiah(r.total_nett)}</td>`;
                                listBulan.forEach(b => {
                                    td += `<td class="text-right">${formatRupiah(r.bulanan[b]?.nett||0)}</td>`;
                                });
                                return td + `</tr>`;
                            }).join('');
                            document.getElementById(bodyId).innerHTML = html || '<tr><td colspan="10" class="text-center p-4">Kosong</td></tr>';
                        }

                        function buildTableSubGroup(bodyId, headId, rows, groupLabel, subLabel) {
                            let th = `<tr><th class="text-left sticky-col">${groupLabel}</th><th class="text-left">${subLabel}</th><th class="text-right text-emerald-400">Total Nett</th>`;
                            listBulan.forEach(b => th += `<th class="text-right">${b.substring(0,3)}</th>`);
                            th += `</tr>`;
                            document.getElementById(headId).innerHTML = th;

                            let html = '';
                            rows.forEach(r => {
                                // baris header grup
                                html += `<tr class="bg-slate-800/80"><td class="sticky-col font-bold text-white border-b border-slate-700">${r.nama||'-'}</td><td class="border-b border-slate-700"></td><td class="text-right text-emerald-300 font-bold border-b border-slate-700">${formatRupiah(r.total_nett)}</td>`;
                                listBulan.forEach(b => {
                                    html += `<td class="text-right border-b border-slate-700 font-bold">${formatRupiah(r.bulanan[b]?.nett||0)}</td>`;
                                });
                                html += `</tr>`;
                                
                                // baris sub grup
                                if (r.sub && r.sub.length > 0) {
                                    r.sub.forEach(s => {
                                        html += `<tr><td class="sticky-col"></td><td class="text-slate-400 pl-4 border-l-2 border-slate-600">${s.nama||'-'}</td><td class="text-right text-emerald-400/70">${formatRupiah(s.total_nett)}</td>`;
                                        listBulan.forEach(b => {
                                            html += `<td class="text-right text-slate-400">${formatRupiah(s.bulanan[b]?.nett||0)}</td>`;
                                        });
                                        html += `</tr>`;
                                    });
                                }
                            });
                            document.getElementById(bodyId).innerHTML = html || '<tr><td colspan="10" class="text-center p-4">Kosong</td></tr>';
                        }

                        buildTable('body-customer', 'head-customer', data.per_customer, 'Customer', 'nama');
                        buildTableSubGroup('body-customer-produk', 'head-customer-produk', data.per_customer_produk, 'Customer', 'Produk');
                        buildTable('body-produk', 'head-produk', data.per_produk, 'Produk', 'nama');
                        buildTableSubGroup('body-produk-ps', 'head-produk-ps', data.pivot_produk_ps, 'Produk', 'Sales (PS)');
                        buildTable('body-ps', 'head-ps', data.per_ps, 'Sales', 'nama');


                        let fHtml = data.stock_forecast.map(r => `<tr><td>${r.nama_produk||'-'}</td><td class="text-right">${formatNum(r.avg_qty)}</td><td class="text-right text-sky-400 font-bold">${formatNum(r.forecast_qty)}</td></tr>`).join('');
                        document.getElementById('body-forecast').innerHTML = fHtml || '<tr><td colspan="3" class="text-center">Kosong</td></tr>';
                    });
            }

            ['m-tahun', 'm-bulan', 'm-customer', 'm-ps', 'm-produk'].forEach(id => {
                document.getElementById(id).addEventListener('change', loadMonitoring);
            });
            loadMonitoring();
        });
    </script>
    @endpush
</x-layout-users>
