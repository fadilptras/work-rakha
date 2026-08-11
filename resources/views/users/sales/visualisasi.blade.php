@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Visualisasi Analytics Sales (Power BI)' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

    @push('styles')
    <style>
        .pbi-bg { background-color: #0f172a; color: #f8fafc; }
        .pbi-card { background: rgba(30, 41, 59, 0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); padding: 20px; }
        .pbi-slicer { background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 8px 12px; font-size: 0.85rem; color: #f8fafc; font-weight: 600; outline: none; width: 100%; }
        .pbi-slicer:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2); }
        .pbi-label { display: block; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
        .pbi-kpi { border-radius: 14px; padding: 18px; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); transition: transform 0.2s, box-shadow 0.2s; }
        .pbi-kpi:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(0,0,0,0.4); }
        .nav-tab { padding: 8px 16px; border-radius: 10px; font-weight: 700; font-size: 0.8rem; color: #94a3b8; transition: all 0.2s; border: 1px solid transparent; }
        .nav-tab:hover { color: #f8fafc; background: rgba(255,255,255,0.05); }
        .nav-tab.active { background: #0284c7; color: white; border-color: #38bdf8; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4); }
        
        table.pbi-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: right; font-size: 0.8rem; }
        table.pbi-table th { background: #0f172a; padding: 10px 12px; font-weight: 700; color: #38bdf8; border-bottom: 2px solid #334155; text-transform: uppercase; font-size: 0.7rem; }
        table.pbi-table td { padding: 10px 12px; border-bottom: 1px solid #334155; color: #cbd5e1; }
        table.pbi-table td.row-header { text-align: left; font-weight: 700; color: #f8fafc; background: rgba(15, 23, 42, 0.4); }
        table.pbi-table tr:hover td { background: rgba(56, 189, 248, 0.05); }

        .val-good { color: #34d399; font-weight: 700; }
        .val-bad { color: #f87171; font-weight: 700; }
        .val-neutral { color: #fbbf24; font-weight: 700; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen pbi-bg relative overflow-hidden">
        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col gap-6">

            {{-- HEADER POWER BI --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 rounded-2xl md:rounded-3xl p-5 md:p-6 shadow-2xl overflow-hidden border border-sky-500/30 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="absolute -right-10 -top-10 w-48 h-48 bg-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex items-center gap-3 md:gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/30 border border-sky-300/30 flex-shrink-0">
                        <i class="fas fa-chart-pie text-xl text-white"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg md:text-2xl font-black tracking-tight text-white uppercase">Power BI Visual Analytics</h1>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-sky-500/20 text-sky-300 border border-sky-400/30">Executive Report</span>
                        </div>
                        <p class="text-slate-400 text-xs md:text-sm mt-0.5 font-medium">
                            Visualisasi data Sales & Target interaktif, grafik pencapaian PS, dan distribusi produk.
                        </p>
                    </div>
                </div>

                {{-- NAVIGATION MODULE TABS --}}
                <div class="flex flex-wrap gap-1.5 p-1.5 bg-slate-900/80 rounded-xl border border-slate-800 self-stretch md:self-auto justify-center">
                    <a href="{{ route('sales.index') }}" class="nav-tab">
                        <i class="fas fa-plus-circle mr-1.5"></i> Input Data
                    </a>
                    <a href="{{ route('sales.monitoring') }}" class="nav-tab">
                        <i class="fas fa-chart-line mr-1.5"></i> Monitoring
                    </a>
                    <a href="{{ route('sales.target') }}" class="nav-tab">
                        <i class="fas fa-bullseye mr-1.5"></i> Target
                    </a>
                    <a href="{{ route('sales.visualisasi') }}" class="nav-tab active">
                        <i class="fas fa-chart-pie mr-1.5"></i> Visualisasi
                    </a>
                    <a href="{{ route('sales.history') }}" class="nav-tab">
                        <i class="fas fa-history mr-1.5"></i> Riwayat
                    </a>
                </div>
            </div>

            {{-- POWER BI SLICER / FILTER PANEL --}}
            <div class="pbi-card border-t-2 border-t-sky-500">
                <form id="filterForm" method="GET" action="{{ route('sales.visualisasi') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="pbi-label"><i class="fas fa-calendar-alt text-sky-400 mr-1"></i> Filter Tahun</label>
                        <select name="tahun" id="filterTahun" class="pbi-slicer" onchange="this.form.submit()">
                            @foreach($listTahun as $t)
                                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>Tahun {{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="pbi-label"><i class="fas fa-calendar-week text-sky-400 mr-1"></i> Filter Bulan</label>
                        <select name="bulan" id="filterBulan" class="pbi-slicer" onchange="this.form.submit()">
                            <option value="">Semua Bulan (Kumulatif YTD)</option>
                            @foreach($listBulan as $b)
                                <option value="{{ $b }}" {{ $bulanTerpilih == $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="pbi-label"><i class="fas fa-user-tie text-sky-400 mr-1"></i> Filter Product Specialist (PS)</label>
                        <select name="ps" id="filterPs" class="pbi-slicer" onchange="this.form.submit()">
                            <option value="">Semua PS (Tim Overall)</option>
                            @foreach($listPs as $ps)
                                <option value="{{ $ps }}" {{ $psTerpilih == $ps ? 'selected' : '' }}>{{ $ps }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('sales.visualisasi') }}" class="w-full text-center py-2 px-4 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs border border-slate-700 transition flex items-center justify-center gap-1.5">
                            <i class="fas fa-undo"></i> Reset Filter
                        </a>
                    </div>
                </form>
            </div>

            {{-- SUMMARY KPI CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Card 1: Total Target --}}
                <div class="pbi-kpi bg-gradient-to-br from-slate-900 to-slate-800 border-l-4 border-l-slate-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="pbi-label">Total Target Sales ({{ $tahun }})</p>
                            <h3 class="text-xl md:text-2xl font-black text-white mt-1">Rp {{ number_format($summary['total_target'], 0, ',', '.') }}</h3>
                        </div>
                        <div class="p-2.5 bg-slate-800 rounded-xl text-slate-300 border border-slate-700">
                            <i class="fas fa-bullseye text-lg"></i>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-3 font-medium">Acuan target periode berjalan</p>
                </div>

                {{-- Card 2: Total Actual Sales --}}
                <div class="pbi-kpi bg-gradient-to-br from-slate-900 to-sky-950 border-l-4 border-l-sky-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="pbi-label">Total Actual Sales</p>
                            <h3 class="text-xl md:text-2xl font-black text-sky-400 mt-1">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</h3>
                        </div>
                        <div class="p-2.5 bg-sky-950 rounded-xl text-sky-400 border border-sky-800">
                            <i class="fas fa-coins text-lg"></i>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-3 font-medium">Total penjualan terrealisasi</p>
                </div>

                {{-- Card 3: Achievement Rate --}}
                <div class="pbi-kpi bg-gradient-to-br from-slate-900 to-emerald-950 border-l-4 border-l-emerald-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="pbi-label">Achievement Rate %</p>
                            <h3 class="text-xl md:text-2xl font-black {{ $summary['overall_achievement'] >= 100 ? 'text-emerald-400' : ($summary['overall_achievement'] >= 80 ? 'text-sky-400' : 'text-amber-400') }} mt-1">
                                {{ $summary['overall_achievement'] }}%
                            </h3>
                        </div>
                        <div class="p-2.5 bg-emerald-950 rounded-xl text-emerald-400 border border-emerald-800">
                            <i class="fas fa-trophy text-lg"></i>
                        </div>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 mt-3 overflow-hidden">
                        <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ min(100, $summary['overall_achievement']) }}%"></div>
                    </div>
                </div>

                {{-- Card 4: YoY Growth Rate --}}
                <div class="pbi-kpi bg-gradient-to-br from-slate-900 to-indigo-950 border-l-4 border-l-indigo-400">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="pbi-label">YoY Growth Rate %</p>
                            <h3 class="text-xl md:text-2xl font-black {{ $summary['overall_growth'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }} mt-1">
                                {{ $summary['overall_growth'] >= 0 ? '+'.$summary['overall_growth'] : $summary['overall_growth'] }}%
                            </h3>
                        </div>
                        <div class="p-2.5 bg-indigo-950 rounded-xl text-indigo-400 border border-indigo-800">
                            <i class="fas {{ $summary['overall_growth'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-lg"></i>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-3 font-medium">Dibandingkan tahun {{ $tahun - 1 }}</p>
                </div>
            </div>

            {{-- VISUAL 1: MONTHLY ACHIEVEMENT RATE & GROWTH TREND (DUAL AXIS) --}}
            <div class="pbi-card">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 pb-3 border-b border-slate-700/60 gap-2">
                    <div>
                        <h2 class="text-base font-black text-white flex items-center gap-2">
                            <i class="fas fa-chart-line text-sky-400"></i> Monthly Achievement Rate & Growth Trend ({{ $tahun }})
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Grafik kombinasi Target, Actual Sales, Achievement Rate (%), dan Growth Rate (%)</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1.5 text-slate-300 font-semibold"><span class="w-3 h-3 rounded bg-slate-500 inline-block"></span> Target</span>
                        <span class="flex items-center gap-1.5 text-sky-400 font-semibold"><span class="w-3 h-3 rounded bg-sky-500 inline-block"></span> Sales</span>
                        <span class="flex items-center gap-1.5 text-amber-400 font-semibold"><span class="w-3 h-1 bg-amber-400 inline-block"></span> Achievement %</span>
                        <span class="flex items-center gap-1.5 text-emerald-400 font-semibold"><span class="w-3 h-1 bg-emerald-400 inline-block"></span> YoY Growth %</span>
                    </div>
                </div>
                <div class="relative h-[320px] md:h-[380px] w-full">
                    <canvas id="chartMonthlyOverview"></canvas>
                </div>
            </div>

            {{-- GRID VISUALS: PS PERFORMANCE & CUMULATIVE --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- VISUAL 2: ACHIEVEMENT RATE PER PS --}}
                <div class="pbi-card">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-700/60">
                        <div>
                            <h2 class="text-sm md:text-base font-black text-white flex items-center gap-2">
                                <i class="fas fa-user-check text-emerald-400"></i> Achievement Rate per PS (Bulan: {{ $summary['bulan_aktif'] }})
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Target vs Actual Sales per PS dan Achievement Rate %</p>
                        </div>
                    </div>
                    <div class="relative h-[280px] md:h-[320px] w-full">
                        <canvas id="chartPsPerformance"></canvas>
                    </div>
                </div>

                {{-- VISUAL 3: CUMULATIVE ACHIEVEMENT RATE PER PS --}}
                <div class="pbi-card">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-700/60">
                        <div>
                            <h2 class="text-sm md:text-base font-black text-white flex items-center gap-2">
                                <i class="fas fa-layer-group text-indigo-400"></i> Cumulative Achievement Rate per PS (YTD)
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Akumulasi Target vs Sales & Rate Pencapaian s/d periode aktif</p>
                        </div>
                    </div>
                    <div class="relative h-[280px] md:h-[320px] w-full">
                        <canvas id="chartPsCumulative"></canvas>
                    </div>
                </div>
            </div>

            {{-- GRID VISUALS: MONTHLY SALES PER PS & PRODUCT BREAKDOWN --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- VISUAL 4: MONTHLY SALES BREAKDOWN PER PS --}}
                <div class="pbi-card">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-700/60">
                        <div>
                            <h2 class="text-sm md:text-base font-black text-white flex items-center gap-2">
                                <i class="fas fa-chart-column text-purple-400"></i> Monthly Sales per PS
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Distribusi hasil penjualan masing-masing PS per bulan</p>
                        </div>
                    </div>
                    <div class="relative h-[280px] md:h-[320px] w-full">
                        <canvas id="chartMonthlySalesPs"></canvas>
                    </div>
                </div>

                {{-- VISUAL 5: SALES BY PRODUCT CATEGORY PER PS --}}
                <div class="pbi-card">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-700/60">
                        <div>
                            <h2 class="text-sm md:text-base font-black text-white flex items-center gap-2">
                                <i class="fas fa-boxes-stacked text-amber-400"></i> Sales by Product Category per PS (Top 15)
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Penjualan 15 Produk teratas berdasarkan kontribusi PS</p>
                        </div>
                    </div>
                    <div class="relative h-[280px] md:h-[320px] w-full">
                        <canvas id="chartProductCategoryPs"></canvas>
                    </div>
                </div>
            </div>

            {{-- POWER BI MATRIX TABLES (DRILL DOWN DATA) --}}
            <div class="pbi-card overflow-hidden" x-data="{ tabTable: 'monthly' }">
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
                                    <td>{{ number_format($monthlyOverview[$b]['target'], 0, ',', '.') }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="row-header">Actual Sales (IDR)</td>
                                @foreach($listBulan as $b)
                                    <td>{{ number_format($monthlyOverview[$b]['sales'], 0, ',', '.') }}</td>
                                @endforeach
                            </tr>
                            <tr class="bg-sky-950/20">
                                <td class="row-header">Achievement Rate (%)</td>
                                @foreach($listBulan as $b)
                                    @php $rate = $monthlyOverview[$b]['achievement_rate']; @endphp
                                    <td class="{{ $rate >= 100 ? 'val-good' : ($rate >= 80 ? 'text-sky-400 font-bold' : ($rate > 0 ? 'val-neutral' : 'text-slate-500')) }}">
                                        {{ $rate }}%
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="row-header">Growth Rate vs Last Year (%)</td>
                                @foreach($listBulan as $b)
                                    @php $growth = $monthlyOverview[$b]['growth_rate']; @endphp
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
                                <th class="text-left">Product Specialist (PS)</th>
                                <th>Target ({{ $summary['bulan_aktif'] }})</th>
                                <th>Sales ({{ $summary['bulan_aktif'] }})</th>
                                <th>Achievement Rate</th>
                                <th>Sales Bulan Lalu</th>
                                <th>Growth vs Bulan Lalu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listPs as $ps)
                                @php $dataPs = $psPerformance[$ps]; @endphp
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
                                <th class="text-left">Product Specialist (PS)</th>
                                <th>Cumulative Target</th>
                                <th>Cumulative Sales</th>
                                <th>Cum. Achievement Rate</th>
                                <th>YoY Cum. Growth</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listPs as $ps)
                                @php $cumData = $psCumulative[$ps]; @endphp
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
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.08)';
            Chart.defaults.font.family = "'Inter', sans-serif";

            const listBulan = @json($listBulan);
            const listPs = @json($listPs);
            const monthlyOverview = @json($monthlyOverview);
            const psPerformance = @json($psPerformance);
            const psCumulative = @json($psCumulative);
            const topProductCategoryPs = @json($topProductCategoryPs);

            const psColors = [
                '#38bdf8', '#34d399', '#fbbf24', '#f472b6', '#a78bfa', 
                '#818cf8', '#f97316', '#06b6d4', '#10b981', '#ec4899'
            ];

            // -----------------------------------------------------------------
            // CHART 1: MONTHLY OVERVIEW (COMBO BAR + DUAL LINE AXIS)
            // -----------------------------------------------------------------
            const targetsArr = listBulan.map(b => monthlyOverview[b].target);
            const salesArr = listBulan.map(b => monthlyOverview[b].sales);
            const achRateArr = listBulan.map(b => monthlyOverview[b].achievement_rate);
            const growthRateArr = listBulan.map(b => monthlyOverview[b].growth_rate);

            new Chart(document.getElementById('chartMonthlyOverview'), {
                type: 'bar',
                data: {
                    labels: listBulan,
                    datasets: [
                        {
                            label: 'Achievement Rate (%)',
                            type: 'line',
                            data: achRateArr,
                            borderColor: '#fbbf24',
                            backgroundColor: '#fbbf24',
                            borderWidth: 3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            yAxisID: 'yRate',
                            tension: 0.3
                        },
                        {
                            label: 'YoY Growth Rate (%)',
                            type: 'line',
                            data: growthRateArr,
                            borderColor: '#34d399',
                            backgroundColor: '#34d399',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 3,
                            yAxisID: 'yRate',
                            tension: 0.3
                        },
                        {
                            label: 'Target (IDR)',
                            data: targetsArr,
                            backgroundColor: 'rgba(100, 116, 139, 0.4)',
                            borderColor: 'rgba(148, 163, 184, 0.6)',
                            borderWidth: 1,
                            borderRadius: 6,
                            yAxisID: 'ySales'
                        },
                        {
                            label: 'Actual Sales (IDR)',
                            data: salesArr,
                            backgroundColor: 'rgba(56, 189, 248, 0.85)',
                            borderColor: '#38bdf8',
                            borderWidth: 1,
                            borderRadius: 6,
                            yAxisID: 'ySales'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        ySales: {
                            type: 'linear',
                            position: 'left',
                            title: { display: true, text: 'Nominal (IDR)', color: '#94a3b8', font: { size: 10, weight: 'bold' } },
                            ticks: {
                                callback: function(val) {
                                    if (val >= 1000000000) return (val / 1000000000).toFixed(1) + ' B';
                                    if (val >= 1000000) return (val / 1000000).toFixed(0) + ' M';
                                    return val;
                                }
                            }
                        },
                        yRate: {
                            type: 'linear',
                            position: 'right',
                            title: { display: true, text: 'Percentage (%)', color: '#fbbf24', font: { size: 10, weight: 'bold' } },
                            grid: { drawOnChartArea: false },
                            ticks: {
                                callback: val => val + '%'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.dataset.yAxisID === 'yRate') {
                                        return label + context.raw + '%';
                                    }
                                    return label + 'Rp ' + context.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // -----------------------------------------------------------------
            // CHART 2: PS PERFORMANCE (MONTHLY)
            // -----------------------------------------------------------------
            const psTargets = listPs.map(p => psPerformance[p] ? psPerformance[p].target : 0);
            const psSales = listPs.map(p => psPerformance[p] ? psPerformance[p].sales : 0);
            const psRates = listPs.map(p => psPerformance[p] ? psPerformance[p].achievement_rate : 0);

            new Chart(document.getElementById('chartPsPerformance'), {
                type: 'bar',
                data: {
                    labels: listPs,
                    datasets: [
                        {
                            label: 'Achievement Rate (%)',
                            type: 'line',
                            data: psRates,
                            borderColor: '#f472b6',
                            backgroundColor: '#f472b6',
                            borderWidth: 3,
                            pointRadius: 5,
                            yAxisID: 'yRate'
                        },
                        {
                            label: 'Target (IDR)',
                            data: psTargets,
                            backgroundColor: 'rgba(100, 116, 139, 0.4)',
                            borderRadius: 6,
                            yAxisID: 'ySales'
                        },
                        {
                            label: 'Sales (IDR)',
                            data: psSales,
                            backgroundColor: 'rgba(52, 211, 153, 0.85)',
                            borderRadius: 6,
                            yAxisID: 'ySales'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        ySales: {
                            type: 'linear',
                            position: 'left',
                            ticks: {
                                callback: val => val >= 1000000 ? (val / 1000000).toFixed(0) + ' M' : val
                            }
                        },
                        yRate: {
                            type: 'linear',
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { callback: val => val + '%' }
                        }
                    }
                }
            });

            // -----------------------------------------------------------------
            // CHART 3: PS CUMULATIVE (YTD)
            // -----------------------------------------------------------------
            const psCumTargets = listPs.map(p => psCumulative[p] ? psCumulative[p].cum_target : 0);
            const psCumSales = listPs.map(p => psCumulative[p] ? psCumulative[p].cum_sales : 0);
            const psCumRates = listPs.map(p => psCumulative[p] ? psCumulative[p].cum_ach_rate : 0);

            new Chart(document.getElementById('chartPsCumulative'), {
                type: 'bar',
                data: {
                    labels: listPs,
                    datasets: [
                        {
                            label: 'Cum. Rate (%)',
                            type: 'line',
                            data: psCumRates,
                            borderColor: '#a78bfa',
                            backgroundColor: '#a78bfa',
                            borderWidth: 3,
                            pointRadius: 5,
                            yAxisID: 'yRate'
                        },
                        {
                            label: 'Cum. Target',
                            data: psCumTargets,
                            backgroundColor: 'rgba(100, 116, 139, 0.4)',
                            borderRadius: 6,
                            yAxisID: 'ySales'
                        },
                        {
                            label: 'Cum. Sales',
                            data: psCumSales,
                            backgroundColor: 'rgba(129, 140, 248, 0.85)',
                            borderRadius: 6,
                            yAxisID: 'ySales'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        ySales: {
                            type: 'linear',
                            position: 'left',
                            ticks: { callback: val => val >= 1000000 ? (val / 1000000).toFixed(0) + ' M' : val }
                        },
                        yRate: {
                            type: 'linear',
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { callback: val => val + '%' }
                        }
                    }
                }
            });

            // -----------------------------------------------------------------
            // CHART 4: MONTHLY SALES BREAKDOWN PER PS
            // -----------------------------------------------------------------
            const datasetsMonthlyPs = listPs.map((ps, idx) => {
                return {
                    label: ps,
                    data: listBulan.map(b => psCumulative[ps] ? psCumulative[ps].monthly_sales[b] : 0),
                    backgroundColor: psColors[idx % psColors.length],
                    borderRadius: 4
                };
            });

            new Chart(document.getElementById('chartMonthlySalesPs'), {
                type: 'bar',
                data: {
                    labels: listBulan,
                    datasets: datasetsMonthlyPs
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { 
                            stacked: true,
                            ticks: { callback: val => val >= 1000000 ? (val / 1000000).toFixed(0) + ' M' : val }
                        }
                    }
                }
            });

            // -----------------------------------------------------------------
            // CHART 5: SALES BY PRODUCT CATEGORY PER PS (TOP 15)
            // -----------------------------------------------------------------
            const productLabels = topProductCategoryPs.map(p => p.nama_produk.length > 25 ? p.nama_produk.substr(0, 22) + '...' : p.nama_produk);
            
            const datasetsProdPs = listPs.map((ps, idx) => {
                return {
                    label: ps,
                    data: topProductCategoryPs.map(p => p.per_ps[ps] || 0),
                    backgroundColor: psColors[idx % psColors.length]
                };
            });

            new Chart(document.getElementById('chartProductCategoryPs'), {
                type: 'bar',
                data: {
                    labels: productLabels,
                    datasets: datasetsProdPs
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { 
                            stacked: true,
                            ticks: { callback: val => val >= 1000000 ? (val / 1000000).toFixed(0) + ' M' : val }
                        },
                        y: { stacked: true }
                    }
                }
            });
        });
    </script>
    @endpush
</x-layout-users>
