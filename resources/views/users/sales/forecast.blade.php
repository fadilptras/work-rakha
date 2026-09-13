@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();

    // Logic Tombol Back Dinamis & Cek Role Gudang / Purchasing
    $user = Auth::user();
    $jabatan = strtolower($user->jabatan ?? '');
    $isAdminGudang = \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang';
    $isLegalPurchasing = \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing') || \Illuminate\Support\Str::contains($jabatan, 'purchasing');
    $isRestrictedViewOnly = $isAdminGudang || $isLegalPurchasing;
    
    $backRoute = $isRestrictedViewOnly ? route('sales.gudang.dashboard') : route('sales.index');
    $backText = $isRestrictedViewOnly ? 'Back to Dashboard Gudang' : 'Back to Sales Dashboard';
@endphp
<x-layout-users :title="$title ?? 'Sales Forecast'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }

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

        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 1.5rem;
            padding: 1.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        }

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

        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; margin: 0; 
        }
        input[type=number] { -moz-appearance: textfield; }

        .col-tooltip { position: relative; }
        .col-tooltip-popup {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 8px;
            width: 300px;
            background: #1e293b;
            color: #fff;
            font-size: 11px;
            line-height: 1.5;
            font-weight: 500;
            text-transform: none;
            letter-spacing: normal;
            border-radius: 10px;
            padding: 12px 14px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.18);
            z-index: 50;
            pointer-events: none;
            white-space: normal;
        }
        .col-tooltip-popup::after {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: transparent transparent #1e293b transparent;
        }
        .col-tooltip-popup--right {
            left: auto;
            right: 0;
            transform: none;
        }
        .col-tooltip-popup--right::after {
            left: auto;
            right: 18px;
            transform: none;
        }
        @media (max-width: 768px) {
            .col-tooltip-popup { width: 260px; font-size: 10px; }
        }

        .forecast-table tbody tr.striped { background-color: #f1f5f9; }
        .forecast-table tbody tr.striped td:nth-child(1),
        .forecast-table tbody tr.striped td:nth-child(2),
        .forecast-table tbody tr.striped td:nth-child(3),
        .forecast-table tbody tr.striped td:nth-child(10) { background-color: rgba(226,232,240,0.82) !important; }
        .forecast-table tbody tr.striped td.bg-slate-50\/30 { background-color: rgba(226,232,240,0.82) !important; }
        .forecast-table tbody tr.striped td.bg-blue-50\/60 { background-color: rgba(219,234,254,0.82) !important; }
        .forecast-table tbody tr.striped td.bg-emerald-50\/60 { background-color: rgba(209,250,229,0.82) !important; }
        .forecast-table tbody tr.striped td.bg-amber-50\/60 { background-color: rgba(254,243,199,0.82) !important; }
        .forecast-table tbody tr.striped td.bg-orange-50\/60 { background-color: rgba(255,237,213,0.82) !important; }
        .forecast-table tbody tr.striped td.bg-rose-50\/60 { background-color: rgba(255,228,230,0.82) !important; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16" x-data="{ showForecastModal: false, showRefMonthsModal: false, showDoiModal: false }">
        <div id="global-moq-toast" class="fixed top-6 left-1/2 -translate-x-1/2 bg-emerald-600 text-white text-xs font-bold px-4 py-2 rounded-full shadow-xl opacity-0 pointer-events-none transition-opacity duration-300 z-[9999] flex items-center gap-2">
            <i class="fas fa-check-circle"></i> Tersimpan
        </div>
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-10 space-y-4 flex-1 flex flex-col justify-start">
            
            <div class="w-full flex justify-start">
                <a href="{{ $backRoute }}" class="btn-back-modern shrink-0">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    {{ $backText }}
                </a>
            </div>  

            <div class="hidden md:block page-header">
                <div class="header-content flex flex-row items-center justify-between gap-6">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Sales Forecast & Stock Estimation</h1>
                        <p class="text-blue-100 text-sm mt-1.5 leading-relaxed">
                            Estimating future stock requirements based on the average sales performance from the last <span class="font-bold text-white">{{ $activeRefMonths }} months</span> (<span class="font-semibold">@foreach($tigaBulanTerakhir as $index => $b){{ $monthTranslations[$b] ?? $b }}{{ !$loop->last ? ', ' : '' }}@endforeach</span>).
                        </p>
                    </div>
                    <div>
                        <i class="fas fa-chart-area text-4xl opacity-20"></i>
                    </div>
                </div>
            </div>

            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Product Forecast</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">
                        Est. stock based on last {{ $activeRefMonths }} months.
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-chart-area"></i>
                </div>
            </div>

            <div class="glass-panel border-t-4 border-t-blue-500">
                <div class="flex flex-col md:flex-row md:items-center md:flex-nowrap justify-between gap-4 mb-6">
                    
                    <form method="GET" action="{{ route('sales.forecast') }}" id="filterForm" class="flex flex-col md:flex-row md:flex-nowrap items-stretch md:items-center gap-3 w-full md:w-auto">
                        <div class="relative w-full md:w-72 shrink-0">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                <i class="fas fa-search text-sm"></i>
                            </div>
                            <input type="text" id="searchInput" onkeyup="handleSearchInput()" placeholder="Search Product Name" class="w-full pl-9 pr-9 py-2 text-sm font-semibold border border-slate-200 bg-white hover:bg-slate-50 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none text-slate-700 shadow-sm transition-colors">
                            <button type="button" id="clearSearchBtn" onclick="clearSearch()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors hidden">
                                <i class="fas fa-times-circle text-base"></i>
                            </button>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <div class="relative flex-1 md:w-36">
                                <select name="bulan_akhir" onchange="this.form.submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-sm pl-3 pr-8 py-2 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    @foreach($bulanTersediaUrut as $bln)
                                        <option value="{{ $bln }}" {{ (isset($bulanAktif) && $bulanAktif == $bln) ? 'selected' : '' }}>
                                            {{ $monthTranslations[$bln] ?? $bln }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-700">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                                <input type="hidden" name="tahun" value="{{ $tahun }}">
                            </div>

                            <div class="relative w-24 shrink-0">
                                <select name="tahun" onchange="this.form.submit()" class="w-full appearance-none border border-slate-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-sm pl-3 pr-8 py-2 font-bold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors">
                                    @foreach($listTahun as $t)
                                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-700">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="flex items-center gap-2 shrink-0 w-full md:w-auto md:flex-nowrap mt-2 md:mt-0">
                        @if(isset($hasFullAccess) && $hasFullAccess)
                            <button type="button" @click="showForecastModal = true" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);" class="flex-1 md:flex-none justify-center inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-bold rounded-lg shadow-md hover:opacity-90 transition-opacity cursor-pointer whitespace-nowrap">
                                <i class="fas fa-sliders-h"></i> Set Persentase
                            </button>
                            <button type="button" @click="showRefMonthsModal = true" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);" class="flex-1 md:flex-none justify-center inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-bold rounded-lg shadow-md hover:opacity-90 transition-opacity cursor-pointer whitespace-nowrap">
                                <i class="fas fa-calendar-week"></i> Ref Bulan
                            </button>
                            <button type="button" @click="showDoiModal = true" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);" class="flex-1 md:flex-none justify-center inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-bold rounded-lg shadow-md hover:opacity-90 transition-opacity cursor-pointer whitespace-nowrap">
                                <i class="fas fa-clock"></i> Set DOI
                            </button>
                        @endif
                        <a href="{{ route('sales.forecast.export.excel', ['tahun' => $tahun, 'bulan_akhir' => $bulanAktif]) }}" class="flex-1 md:flex-none justify-center inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a href="{{ route('sales.forecast.export.pdf', ['tahun' => $tahun, 'bulan_akhir' => $bulanAktif]) }}" class="flex-1 md:flex-none justify-center inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>

                <div id="dataContainer">
                    @if(count($stockForecast) > 0)
                    
                    {{-- 1. TAMPILAN DESKTOP --}}
                    <div class="hidden md:block rounded-xl border border-slate-200 bg-white overflow-visible">
                        <div class="overflow-x-auto rounded-xl">
                        <table class="forecast-table w-full text-left border-collapse whitespace-nowrap table-fixed">
                            <colgroup>
                                <col style="width: 26%; min-width: 170px;">
                                <col style="width: 50px;">
                                <col style="width: 150px;">
                                <col style="width: 68px;">
                                <col style="width: 70px;">
                                <col style="width: 80px;">
                                <col style="width: 68px;">
                                <col style="width: 76px;">
                                <col style="width: 68px;">
                                <col style="width: 78px;">
                            </colgroup>
                            @php $realEx = $stockForecast[0] ?? null; @endphp
                            <thead>
                                <tr class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                                    <th class="py-4 px-4 text-slate-500 text-left">Product Name & Contributor</th>
                                    <th class="py-4 px-2 text-center border-l border-slate-100 text-slate-500 leading-tight col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <span class="inline-flex items-center justify-center gap-1 cursor-help">MOQ</span><br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">(input)</span>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="col-tooltip-popup text-center">
                                            <span class="font-bold text-slate-200">MOQ</span> - Minimum Order Quantity. The smallest quantity that can be ordered. Final Order is rounded up to the nearest MOQ. Example: need 25, MOQ 10 -&gt; Order 30.
                                        </div>
                                    </th>
                                    <th class="py-4 px-3 text-center border-l border-slate-100 text-blue-600 leading-tight">Monthly Qty<br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">(Last {{ $activeRefMonths }} months)</span></th>
                                    <th class="py-4 px-3 text-center border-l border-slate-100 text-slate-600">Total</th>
                                    <th class="py-4 px-3 text-center border-l border-slate-100 text-blue-600 bg-blue-50/60 leading-tight col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <span class="inline-flex items-center justify-center gap-1 cursor-help">Average</span><br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">/month</span>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="col-tooltip-popup">
                                            <span class="font-bold text-blue-200">Average</span> - typical sales per month.<br><span class="text-yellow-300">Average = Total / {{ $activeRefMonths }} months</span> @if($realEx)<br><span class="text-blue-100">Real: {{ number_format($realEx['total_qty'],0,',','.') }} / {{ $activeRefMonths }} = {{ number_format($realEx['avg_qty'],2,',','.') }} /month</span>@endif
                                        </div>
                                    </th>
                                    <th class="py-4 px-3 text-center border-l border-slate-100 text-emerald-600 bg-emerald-50/60 leading-tight col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <span class="inline-flex items-center justify-center gap-1 cursor-help">Forecast</span><br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">(+{{ rtrim(rtrim(number_format($activePercentage, 2, '.', ''), '0'), '.') }}%)</span>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="col-tooltip-popup">
                                            <span class="font-bold text-emerald-200">Forecast</span> - predicted demand.<br><span class="text-yellow-300">Forecast = Average + {{ rtrim(rtrim(number_format($activePercentage, 2, '.', ''), '0'), '.') }}%</span> @if($realEx)<br><span class="text-emerald-100">Real: {{ number_format($realEx['avg_qty'],2,',','.') }} + {{ rtrim(rtrim(number_format($activePercentage, 2, '.', ''), '0'), '.') }}% = {{ number_format($realEx['forecast_qty'],0,',','.') }}</span>@endif
                                        </div>
                                    </th>
                                    <th class="py-4 px-3 text-center border-l border-slate-100 text-amber-600 leading-tight bg-amber-50/60 col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <span class="inline-flex items-center justify-center gap-1 cursor-help">Buffer</span><br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">(DOI {{ $activeDoi }} days)</span>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="col-tooltip-popup">
                                            <span class="font-bold text-amber-200">Buffer</span> - safety stock needed to cover the target coverage period.<br><span class="text-yellow-300">Buffer = Average x (Target DOI / 30)</span><br><span class="opacity-80">Example:</span> Average 90, Target DOI {{ $activeDoi }} days -&gt; Buffer = 90 x ({{ $activeDoi }}/30) = {{ number_format(90 * $activeDoi / 30, 0) }}.
                                        </div>
                                    </th>
                                    <th class="py-4 px-3 text-center border-l border-slate-100 text-orange-600 leading-tight bg-orange-50/60 col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <span class="inline-flex items-center justify-center gap-1 cursor-help">End Stock</span><br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">({{ $teksStokAkhir }})</span>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="col-tooltip-popup">
                                            <span class="font-bold text-orange-200">End Stock</span> - stock on {{ $teksStokAkhir }}.@if($realEx)<br><span class="text-orange-100">Real: {{ number_format($realEx['stok_tersedia'],0,',','.') }} {{ $realEx['satuan_stok'] }}</span>@endif<br><span class="opacity-80">Source: latest daily snapshot <= end of last reference month.</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-3 text-center border-l border-slate-100 text-rose-600 leading-tight bg-rose-50/60 col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <span class="inline-flex items-center justify-center gap-1 cursor-help">DOI</span><br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">days</span>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="col-tooltip-popup">
                                            <span class="font-bold text-rose-200">DOI - Days of Inventory</span> - how long stock will last.<br><span class="text-yellow-300">DOI = (End Stock / Average) x 30</span> @if($realEx)<br><span class="text-rose-100">Real: ({{ number_format($realEx['stok_tersedia'],0,',','.') }} / {{ number_format($realEx['avg_qty'],2,',','.') }}) x 30 = {{ number_format($realEx['doi_qty'],0,',','.') }} days</span>@endif
                                        </div>
                                    </th>
                                    <th class="py-4 px-3 text-center border-l border-slate-100 text-indigo-700 leading-tight col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <span class="inline-flex items-center justify-center gap-1 cursor-help">Order</span><br><span class="text-[8px] font-medium normal-case tracking-normal opacity-70">(Production)</span>
                                        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="col-tooltip-popup col-tooltip-popup--right">
                                            <span class="font-bold text-indigo-200">Order / Production</span> - quantity to keep stock above Buffer.<br><span class="text-yellow-300">IF (End Stock - Forecast) &lt; Buffer THEN Order = CEILING(Buffer - (End Stock - Forecast), MOQ) ELSE 0</span> @if($realEx)<br><span class="text-indigo-100">Real: ({{ number_format($realEx['stok_tersedia'],0,',','.') }} - {{ number_format($realEx['forecast_qty'],0,',','.') }}) = {{ number_format($realEx['stok_tersedia'] - $realEx['forecast_qty'],0,',','.') }} &lt; {{ number_format($realEx['buffer_qty'],0,',','.') }} ? Order {{ number_format($realEx['order_qty'],0,',','.') }}</span>@endif
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($stockForecast as $item)
                                    <tr class="searchable-item hover:bg-blue-50/80 transition-colors text-xs text-slate-700" data-product="{{ $item['nama_produk'] }}" data-forecast="{{ $item['forecast_qty'] }}" data-buffer="{{ $item['buffer_qty'] }}" data-endstock="{{ $item['stok_tersedia'] }}">
                                        <td class="py-4 px-4 text-left">
                                            <div class="font-bold text-slate-800 text-sm mb-1.5 whitespace-normal nama-produk leading-snug">
                                                {{ $item['nama_produk'] }}
                                            </div>
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                @foreach($item['ps_breakdown'] as $ps => $qty)
                                                    <div class="inline-flex items-center gap-1 bg-white border border-slate-200/70 rounded-lg px-2 py-0.5">
                                                        <span class="font-medium text-slate-500 text-[10px]">{{ explode(' ', trim($ps))[0] }}</span>
                                                        <span class="font-bold text-blue-600 text-[11px]">{{ number_format($qty, 0, ',', '.') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>

                                        <td class="py-4 px-2 border-l border-slate-100 align-middle">
                                            <div class="flex items-center justify-center relative w-[64px] mx-auto">
                                                <input type="text" inputmode="decimal" placeholder="1" 
                                                    value="{{ $item['moq'] > 0 ? number_format($item['moq'], 0, ',', '.') : '' }}" 
                                                    @if($isRestrictedViewOnly) disabled @else oninput="formatNumberInput(this, true)" @endif 
                                                    class="relative w-full px-1 py-1.5 text-center font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl focus:ring-0 focus:border-slate-400 outline-none shadow-sm text-xs hover:border-slate-300 transition-colors @if($isRestrictedViewOnly) opacity-70 cursor-not-allowed bg-slate-100 @endif">
                                            </div>
                                        </td>

                                        <td class="py-4 px-3 border-l border-slate-100 align-middle">
                                            <div class="grid grid-cols-3 gap-2 min-w-[150px]">
                                                @foreach($tigaBulanTerakhir as $bulan)
                                                    @php
                                                        $shortBulan = strtoupper(substr($monthTranslations[$bulan] ?? $bulan, 0, 3));
                                                        $qtyBulan = $item['detail_bulan'][$bulan] ?? 0;
                                                    @endphp
                                                    <div class="flex flex-col min-w-0 text-center border border-slate-200 rounded-lg py-1.5 px-1 bg-slate-50/50">
                                                        <span class="text-[8px] font-bold text-blue-600 uppercase tracking-wider leading-none">{{ $shortBulan }}</span>
                                                        <span class="text-[11px] font-bold text-slate-800 mt-1 leading-none">{{ number_format($qtyBulan, 0, ',', '.') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        
                                        <td class="py-4 px-3 text-center border-l border-slate-100 bg-slate-50/30 align-middle">
                                            <div class="flex flex-col items-center justify-center leading-none">
                                                <div class="font-bold text-slate-700 text-sm text-center leading-tight">{{ number_format($item['total_qty'], 0, ',', '.') }}</div>
                                                <div class="text-[9px] font-medium text-slate-400 mt-1 text-center leading-none tracking-wide">{{ $item['satuan_sales'] }}</div>
                                            </div>
                                        </td>

                                        <td class="py-4 px-3 text-center border-l border-slate-100 bg-blue-50/60 align-middle">
                                            <div class="flex flex-col items-center justify-center leading-none">
                                                <div class="font-bold text-blue-600 text-sm text-center leading-tight">{{ number_format($item['avg_qty'], 2, ',', '.') }}</div>
                                                <div class="text-[9px] font-medium text-blue-400 mt-1 text-center leading-none tracking-wide">{{ $item['satuan_sales'] }}</div>
                                            </div>
                                        </td>
                                         
                                        <td class="py-4 px-3 text-center border-l border-slate-100 bg-emerald-50/60 align-middle">
                                            <div class="flex flex-col items-center justify-center leading-none">
                                                <div class="font-black text-emerald-600 text-sm text-center leading-tight">{{ number_format($item['forecast_qty'], 0, ',', '.') }}</div>
                                                <div class="text-[9px] font-medium text-emerald-500 mt-1 text-center leading-none tracking-wide">{{ $item['satuan_sales'] }}</div>
                                            </div>
                                        </td>

                                        <td class="py-4 px-3 text-center border-l border-slate-100 bg-amber-50/60 align-middle">
                                            <div class="flex flex-col items-center justify-center leading-none">
                                                <div class="font-bold text-amber-600 text-sm text-center leading-tight">{{ number_format($item['buffer_qty'], 0, ',', '.') }}</div>
                                                <div class="text-[9px] font-medium text-amber-500 mt-1 text-center leading-none tracking-wide">{{ $item['satuan_sales'] }}</div>
                                            </div>
                                        </td>

                                        <td class="py-4 px-3 text-center border-l border-slate-100 bg-orange-50/60 align-middle">
                                            <div class="font-bold text-orange-600 text-sm text-center">{{ number_format($item['stok_tersedia'], 0, ',', '.') }}</div>
                                            <div class="text-[9px] font-medium text-orange-400 mt-0.5 text-center">{{ $item['satuan_stok'] }}</div>
                                        </td>

                                        <td class="py-4 px-3 text-center border-l border-slate-100 bg-rose-50/60 align-middle">
                                            <div class="font-bold text-rose-600 text-sm text-center">{{ number_format($item['doi_qty'], 0, ',', '.') }}</div>
                                            <div class="text-[9px] font-medium text-rose-400 mt-0.5 text-center">days</div>
                                        </td>

                                        <td class="py-4 px-3 text-center border-l border-slate-100 align-middle order-cell">
                                            <div class="font-black text-indigo-700 text-sm order-value text-center">{{ number_format($item['order_qty'], 0, ',', '.') }}</div>
                                            <div class="text-[9px] font-medium text-indigo-400 mt-0.5 text-center">{{ $item['satuan_sales'] }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>

                    {{-- 2. TAMPILAN MOBILE --}}
                    <div class="block md:hidden flex flex-col gap-3">
                        @foreach($stockForecast as $item)
                        <div class="searchable-item rounded-2xl shadow-md border-t-4 border-t-blue-500 overflow-hidden" style="background: linear-gradient(180deg, #eff6ff 0%, #ffffff 18%);" data-product="{{ $item['nama_produk'] }}" data-forecast="{{ $item['forecast_qty'] }}" data-buffer="{{ $item['buffer_qty'] }}" data-endstock="{{ $item['stok_tersedia'] }}">
                            
                            <div class="px-3.5 pt-3 pb-2 flex flex-col gap-1.5 border-b border-slate-100">
                                <h4 class="font-extrabold text-slate-800 text-[13px] leading-snug nama-produk break-words">{{ $item['nama_produk'] }}</h4>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach($item['ps_breakdown'] as $ps => $qty)
                                        <div class="inline-flex items-center gap-1 bg-slate-50 border border-slate-200/70 rounded-lg px-1.5 py-0.5 max-w-full">
                                            <span class="font-medium text-slate-500 text-[9px] truncate">{{ explode(' ', trim($ps))[0] }}</span>
                                            <span class="font-bold text-blue-600 text-[10px] shrink-0">{{ number_format($qty, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="bg-slate-50/60 border-b border-slate-100 px-3.5 py-2 flex flex-col gap-1.5">
                                <div class="grid grid-cols-3 gap-1.5 pb-1.5 border-b border-slate-200/60 text-center">
                                    @foreach($item['detail_bulan'] as $qty)
                                        @php 
                                            $bulanKey = $tigaBulanTerakhir[$loop->index]; 
                                            $shortBulan = strtoupper(substr($monthTranslations[$bulanKey] ?? $bulanKey, 0, 3));
                                        @endphp
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider truncate">{{ $shortBulan }}</span>
                                            <span class="text-[11px] font-bold text-slate-700 mt-0.5 truncate">{{ number_format($qty, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="grid grid-cols-2 gap-1.5 text-center pt-0.5">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Total Sales</span>
                                        <span class="text-[10px] font-bold text-slate-800 mt-0.5 truncate">
                                            {{ number_format($item['total_qty'], 0, ',', '.') }} <span class="text-[8px] font-medium text-slate-500">{{ $item['satuan_sales'] }}</span>
                                        </span>
                                    </div>
                                    <div class="flex flex-col min-w-0 border-l border-slate-200/80">
                                        <span class="text-[8px] font-bold text-blue-500 uppercase tracking-wider">Average / Mo</span>
                                        <span class="text-[10px] font-bold text-blue-600 mt-0.5 truncate">
                                            {{ number_format($item['avg_qty'], 2, ',', '.') }} <span class="text-[8px] font-medium text-blue-400">{{ $item['satuan_sales'] }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-6 gap-1.5 px-3.5 py-2.5 bg-white items-end text-center">
                                <div class="flex flex-col min-w-0 items-center text-center">
                                    <span class="text-[8px] font-bold text-emerald-600 uppercase tracking-wider mb-0.5 truncate text-center">Forecast</span>
                                    <span class="text-xs font-black text-emerald-600 leading-tight truncate text-center">
                                        {{ number_format($item['forecast_qty'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-[8px] font-semibold text-emerald-500 truncate text-center">{{ $item['satuan_sales'] }}</span>
                                </div>
                                <div class="flex flex-col min-w-0 border-l border-slate-200 pl-1.5 items-center text-center">
                                    <span class="text-[8px] font-bold text-amber-600 uppercase tracking-wider mb-0.5 truncate text-center">Buffer</span>
                                    <span class="text-xs font-black text-amber-600 leading-tight truncate text-center">
                                        {{ number_format($item['buffer_qty'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-[8px] font-semibold text-amber-500 truncate text-center">{{ $item['satuan_sales'] }}</span>
                                </div>
                                <div class="flex flex-col min-w-0 border-l border-slate-200 pl-1.5 items-center text-center">
                                    <span class="text-[8px] font-bold text-orange-600 uppercase tracking-wider mb-0.5 truncate text-center">Stock</span>
                                    <span class="text-xs font-black text-orange-600 leading-tight truncate text-center">
                                        {{ number_format($item['stok_tersedia'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-[8px] font-semibold text-orange-500 truncate text-center">{{ $item['satuan_stok'] }}</span>
                                </div>
                                <div class="flex flex-col min-w-0 border-l border-slate-200 pl-1.5 items-center text-center">
                                    <span class="text-[8px] font-bold text-rose-600 uppercase tracking-wider mb-0.5 truncate text-center">DOI</span>
                                    <span class="text-xs font-black text-rose-600 leading-tight truncate text-center">
                                        {{ number_format($item['doi_qty'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-[8px] font-semibold text-rose-400 truncate text-center">days</span>
                                </div>
                                <div class="min-w-0 flex flex-col items-center">
                                    <div class="text-[8px] font-bold text-slate-500 uppercase leading-tight mb-0.5 truncate">MOQ</div>
                                    <input type="text" inputmode="decimal" placeholder="1" 
                                        value="{{ $item['moq'] > 0 ? number_format($item['moq'], 0, ',', '.') : '' }}" 
                                        @if($isRestrictedViewOnly) disabled @else oninput="formatNumberInput(this, true)" @endif 
                                        class="w-full min-w-0 px-1 py-1.5 text-center font-bold text-slate-700 bg-white border-2 border-slate-200 rounded-xl focus:ring-0 focus:border-slate-400 outline-none shadow-sm text-xs hover:border-slate-300 transition-colors @if($isRestrictedViewOnly) opacity-70 cursor-not-allowed bg-slate-100 @endif">
                                </div>
                                <div class="flex flex-col min-w-0 border-l border-slate-200 pl-1.5 order-cell">
                                    <div class="text-[8px] font-bold text-indigo-600 uppercase leading-tight mb-0.5 text-right">Order</div>
                                    <span class="text-xs font-black text-indigo-700 leading-tight truncate text-right order-value">
                                        {{ number_format($item['order_qty'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-[8px] font-semibold text-indigo-400 truncate text-right">{{ $item['satuan_sales'] }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @endif
                </div>

                <div id="searchEmptyState" class="py-12 flex-col items-center justify-center text-center text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200 hidden mt-4">
                    <i class="fas fa-search text-4xl mb-3 text-slate-300"></i>
                    <h4 class="font-bold text-base text-slate-600">No results found</h4>
                    <p class="text-xs mt-1" id="searchEmptyText">No products matched your search.</p>
                </div>

                @if(count($stockForecast) == 0)
                <div class="py-12 flex flex-col items-center justify-center text-center text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200 mt-4">
                    <i class="fas fa-box-open text-4xl mb-3 text-slate-300"></i>
                    <h4 class="font-bold text-base text-slate-600">No data available</h4>
                    <p class="text-xs mt-1 max-w-sm px-4 mx-auto">No sales data available for the selected period and filters. Try changing the month or year.</p>
                </div>
                @endif
            </div>
        </div>

        @if(isset($hasFullAccess) && $hasFullAccess)
        <div x-show="showRefMonthsModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showRefMonthsModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all flex flex-col" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-slate-800"><i class="fas fa-calendar-week text-blue-600 mr-2"></i> Set Reference Months</h3>
                    </div>
                    <button @click="showRefMonthsModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form action="{{ route('sales.forecast.settings.save') }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">Set the number of recent months used as the basis for forecast calculation for the reference period <b>{{ $monthTranslations[$bulanAktif] ?? $bulanAktif }} {{ $tahun }}</b>. Example: 3 or 6 months.</p>
                    
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan_acuan" value="{{ $bulanAktif }}">
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Reference Months</label>
                        <div class="relative">
                            <input type="number" min="1" max="12" step="1" name="ref_months" value="{{ $activeRefMonths }}" required class="w-full px-3 py-2 pr-12 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold text-slate-700 outline-none text-sm bg-slate-50 hover:bg-white transition-colors">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 text-xs font-bold pointer-events-none">months</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showRefMonthsModal = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-500/20">
                            Save Reference
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @if(isset($hasFullAccess) && $hasFullAccess)
        <div x-show="showDoiModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showDoiModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all flex flex-col" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-slate-800"><i class="fas fa-clock text-amber-500 mr-2"></i> Set DOI</h3>
                    </div>
                    <button @click="showDoiModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form action="{{ route('sales.forecast.settings.save') }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">Set the target Days of Inventory (in days) for the period <b>{{ $monthTranslations[$bulanAktif] ?? $bulanAktif }} {{ $tahun }}</b>. Used to calculate Buffer.</p>
                    
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan_acuan" value="{{ $bulanAktif }}">
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Target DOI (days)</label>
                        <div class="relative">
                            <input type="number" min="1" max="365" step="1" name="doi" value="{{ $activeDoi }}" required class="w-full px-3 py-2 pr-12 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-bold text-slate-700 outline-none text-sm bg-slate-50 hover:bg-white transition-colors">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 text-xs font-bold pointer-events-none">days</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showDoiModal = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-amber-500/20">
                            Save DOI
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @if(isset($hasFullAccess) && $hasFullAccess)
        <div x-show="showForecastModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showForecastModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all flex flex-col" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-slate-800"><i class="fas fa-sliders-h text-indigo-500 mr-2"></i> Set Persentase</h3>
                    </div>
                    <button @click="showForecastModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form action="{{ route('sales.forecast.settings.save') }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">Set the additional forecast percentage for the reference period <b>{{ $monthTranslations[$bulanAktif] ?? $bulanAktif }} {{ $tahun }}</b>.</p>
                    
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan_acuan" value="{{ $bulanAktif }}">
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Forecast Percentage (%)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="percentage" value="{{ $activePercentage }}" required class="w-full px-3 py-2 pr-8 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-slate-700 outline-none text-sm bg-slate-50 hover:bg-white transition-colors">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 text-xs font-bold pointer-events-none">%</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showForecastModal = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-500/20">
                            Save Percentage
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function handleSearchInput() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let clearBtn = document.getElementById("clearSearchBtn");
            
            if (input.length > 0) {
                clearBtn.classList.remove("hidden");
            } else {
                clearBtn.classList.add("hidden");
            }

            let items = document.querySelectorAll(".searchable-item");
            let visibleCount = 0;

            items.forEach(item => {
                let nameEl = item.querySelector('.nama-produk');
                if (nameEl) {
                    if (nameEl.innerText.toLowerCase().indexOf(input) > -1) {
                        item.style.display = "";
                        visibleCount++;
                    } else {
                        item.style.display = "none";
                    }
                }
            });

            let dataContainer = document.getElementById("dataContainer");
            let emptyStateDiv = document.getElementById("searchEmptyState");
            let emptyText = document.getElementById("searchEmptyText");

            if (visibleCount === 0 && items.length > 0) {
                if (dataContainer) dataContainer.style.display = "none";
                if (emptyStateDiv) {
                    emptyStateDiv.classList.remove("hidden");
                    emptyStateDiv.classList.add("flex");
                    emptyText.innerHTML = `No products found for "<span class="font-bold">${document.getElementById("searchInput").value}</span>".`;
                }
            } else {
                if (dataContainer) dataContainer.style.display = "block";
                if (emptyStateDiv) {
                    emptyStateDiv.classList.remove("flex");
                    emptyStateDiv.classList.add("hidden");
                }
            }

            applyZebra();
        }

        function applyZebra() {
            const tableRows = document.querySelectorAll("#dataContainer table tbody tr.searchable-item");
            let visibleIndex = 0;
            tableRows.forEach(row => {
                if (row.style.display === "none") {
                    row.classList.remove("striped");
                } else {
                    if (visibleIndex % 2 === 1) row.classList.add("striped");
                    else row.classList.remove("striped");
                    visibleIndex++;
                }
            });
        }

        document.addEventListener("DOMContentLoaded", applyZebra);
        if (document.readyState !== "loading") applyZebra();

        function clearSearch() {
            let input = document.getElementById("searchInput");
            input.value = "";
            handleSearchInput();
            input.focus();
        }

        function formatNumberInput(input, isMoq = false) {
            let value = input.value.replace(/\D/g, "");
            let rawValue = value;
            if (value !== "") {
                value = Number(value).toLocaleString("id-ID");
            }
            input.value = value;

            clearTimeout(input.saveTimer);
            input.saveTimer = setTimeout(() => {
                saveSuggestedOrder(input, rawValue, isMoq);
            }, 600);
        }

        function saveSuggestedOrder(inputElement, rawValue, isMoq = false) {
            let container = inputElement.closest('.searchable-item');
            if(!container) return;

            let productName = container.getAttribute('data-product');
            let forecastQty = container.getAttribute('data-forecast');
            let tahun = "{{ $tahun }}";
            let bulanAcuan = "{{ $bulanAktif }}";

            inputElement.classList.add("border-amber-400");

            let payload = {
                tahun: tahun,
                bulan_acuan: bulanAcuan,
                nama_produk: productName,
                forecast_qty: forecastQty
            };
            if (isMoq) {
                payload.moq = rawValue;
            } else {
                payload.suggested_qty = rawValue;
            }

            fetch("{{ route('sales.forecast.store-order') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    inputElement.classList.remove("border-amber-400");
                    inputElement.classList.add("border-emerald-500");

                    const globalToast = document.getElementById('global-moq-toast');
                    if (globalToast) {
                        globalToast.classList.remove("opacity-0");
                        globalToast.classList.add("opacity-100");
                        setTimeout(() => {
                            globalToast.classList.remove("opacity-100");
                            globalToast.classList.add("opacity-0");
                        }, 1500);
                    }

                    if (isMoq) {
                        const forecast = parseInt(container.getAttribute('data-forecast')) || 0;
                        const buffer = parseInt(container.getAttribute('data-buffer')) || 0;
                        const endStock = parseInt(container.getAttribute('data-endstock')) || 0;
                        const moqVal = parseInt(rawValue) || 1;
                        let order = 0;
                        if ((endStock - forecast) < buffer) {
                            const diff = buffer - (endStock - forecast);
                            order = Math.ceil(diff / (moqVal || 1)) * (moqVal || 1);
                        }
                        const orderEl = container.querySelector('.order-value');
                        if (orderEl) orderEl.textContent = order.toLocaleString('id-ID');
                    }

                    setTimeout(() => {
                        inputElement.classList.remove("border-emerald-500");
                    }, 1500);
                }
            })
            .catch(error => {
                console.error("Error saving forecast order:", error);
                inputElement.classList.remove("border-amber-400");
                inputElement.classList.add("border-red-400");
            });
        }
    </script>
    @endpush
</x-layout-users>




