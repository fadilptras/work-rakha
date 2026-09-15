@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="Monthly Monitoring">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    @push('styles')
    <style>
        body {
            background-color: #ede9fe;
        }

        .mesh-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 0;
            pointer-events: none;
            background-color: #ede9fe;
            background-image:
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.6) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .header-content { position: relative; z-index: 1; }

        /* == Modern Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px 6px 6px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.8rem; font-weight: 700;
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
            width: 26px; height: 26px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6;
            font-size: 0.75rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover .icon-circle {
            transform: translateX(-3px);
            background: #EFF6FF;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 1.5rem;
            padding: 1.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        }
        @media (max-width: 640px) {
            .glass-panel { padding: 1.1rem !important; }
        }
        
        .main-tab-content { display: block; animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .overflow-x-auto::-webkit-scrollbar, .overflow-y-auto::-webkit-scrollbar { width: 8px; height: 8px; }
        .overflow-x-auto::-webkit-scrollbar-track, .overflow-y-auto::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .overflow-x-auto::-webkit-scrollbar-thumb, .overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover, .overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Hide number input spinners (arrows up/down) */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield;
        }

        @media (max-width: 640px) {
            .glass-panel { padding: 0.9rem 0.9rem; border-radius: 1.1rem; }
        }

        /* == Tampilan Mobile (BASE ASLI - Jangan diubah) == */
        .monthly-card {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.4rem 0.2rem;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 48px;
        }
        @media (min-width: 640px) {
            .monthly-card { padding: 0.5rem; min-height: 52px; }
        }
        .monthly-card:hover {
            transform: translateY(-3px);
            border-color: #3b82f6;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.15);
            background: #fdfaef;
        }
        .monthly-card.active {
            border-color: #3b82f6;
            background: #eff6ff;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.2);
        }
        .monthly-card span { font-size: 0.65rem; color: #475569; font-weight: 800; transition: color 0.3s; word-break: break-word; }
        @media (min-width: 640px) {
            .monthly-card span { font-size: 0.875rem; word-break: normal; }
        }
        .monthly-card:hover span, .monthly-card.active span { color: #2563eb; }

        .month-sub-tab {
            padding: 0.4rem 0.2rem; font-size: 0.65rem; border-radius: 1rem; font-weight: 700;
            color: #64748b; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid transparent;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
            line-height: 1.1; text-align: center;
        }
        @media (min-width: 640px) {
            .month-sub-tab { padding: 0.6rem 1rem; font-size: 0.85rem; flex-direction: row; gap: 8px; border-radius: 1.25rem; }
        }
        .month-sub-tab:hover { color: #3b82f6; background: #f8fafc; }
        .month-sub-tab.active { 
            color: #ffffff; 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
            border-color: transparent;
        }


        /* ===================== NEW UNIFIED DESKTOP HEADER ===================== 
           Hanya aktif di resolusi layar desktop (768px ke atas). */
        @media (min-width: 768px) {
            .desktop-unified-header {
                background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;
                border-radius: 1.25rem !important; 
                padding: 1.25rem 1.75rem !important; 
                color: white !important;
                box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4) !important;
            }
            
            #monthNavCombo { gap: 0.85rem !important; }
            
            /* 1. Form Select Month Pill (Sesuai Referensi) */
            #monthlySelectCard {
                background: rgba(255, 255, 255, 0.15) !important;
                backdrop-filter: blur(12px) !important; -webkit-backdrop-filter: blur(12px) !important;
                border: 1px solid rgba(255, 255, 255, 0.25) !important;
                border-radius: 9999px !important;
                padding: 0.35rem !important;
                box-shadow: none !important;
                display: flex !important;
                align-items: center !important;
            }
            
            /* Year Dropdown in Desktop */
            #monthlySelectCard select {
                background: #ffffff !important; color: #1e40af !important; border: none !important;
                border-radius: 9999px !important; 
                padding: 0.375rem 2.25rem 0.375rem 1rem !important;
                font-size: 13px !important; 
                font-weight: 800 !important; 
                box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
                line-height: 1.25 !important;
            }
            #monthlySelectCard .pointer-events-none i { color: #1e40af !important; }

            /* 2. Compact Floating Grid for Months */
            #monthGridContainer {
                position: absolute !important; 
                top: calc(100% + 14px) !important; 
                left: auto !important; right: 0 !important;
                width: 440px !important; 
                background: #ffffff !important; 
                border-radius: 1.25rem !important; 
                padding: 1.25rem !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0,0,0,0.05) !important;
                border: none !important; 
                z-index: 999 !important;
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 0.75rem !important;
            }
            #monthGridContainer.hidden {
                display: none !important;
            }
            
            .monthly-card {
                border-radius: 0.75rem !important;
                border: 1px solid #f1f5f9 !important;
                background: #f8fafc !important;
                padding: 0.6rem 0.2rem !important;
                min-height: 44px !important;
                box-shadow: none !important;
            }
            .monthly-card span { font-size: 0.75rem !important; font-weight: 700 !important; }
            .monthly-card:hover {
                background: #eff6ff !important; border-color: #bfdbfe !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1) !important;
            }
            .monthly-card.active { background: #3b82f6 !important; border-color: #2563eb !important; color: white !important; }
            .monthly-card.active span { color: white !important; }

            /* 3. Sub Tabs Pill Desktop */
            #monthNavExtra {
                display: flex !important;
                align-items: center;
                background: rgba(255, 255, 255, 0.15) !important;
                backdrop-filter: blur(12px) !important; -webkit-backdrop-filter: blur(12px) !important;
                border: 1px solid rgba(255, 255, 255, 0.25) !important;
                border-radius: 9999px !important; box-shadow: none !important;
                padding: 0.35rem !important; margin-top: 0 !important;
            }
            #monthNavExtra.hidden { display: none !important; }
            #monthNavExtra #sub-tabs-container {
                display: flex !important; background: transparent !important; border: none !important; box-shadow: none !important;
                padding: 0 !important; gap: 0.35rem !important;
            }
            .month-sub-tab {
                border-radius: 9999px !important; 
                padding: 0.375rem 1rem !important; 
                color: #e2e8f0 !important;
                background: transparent !important; border: none !important; 
                font-size: 13px !important; 
                font-weight: 800 !important; 
                transition: all 0.2s; display: flex !important; align-items: center !important; gap: 6px !important;
                line-height: 1.25 !important;
            }
            .month-sub-tab i { font-size: 13px !important; }
            .month-sub-tab:hover { background: rgba(255, 255, 255, 0.15) !important; color: white !important; }
            .month-sub-tab.active {
                background: #ffffff !important; color: #1e40af !important; box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            }
        }

        /* == Full-page Loading Overlay == */
        #monthly-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            animation: loaderFadeIn 0.2s ease-out;
        }
        #monthly-loader.hidden { display: none; }
        .loader-box { display: flex; flex-direction: column; align-items: center; gap: 14px; }
        .loader-spinner {
            width: 46px; height: 46px;
            border: 4px solid rgba(255, 255, 255, 0.25);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: loaderSpin 0.7s linear infinite;
        }
        .loader-text { color: #ffffff; font-weight: 800; font-size: 0.8rem; letter-spacing: 0.04em; }
        @keyframes loaderSpin { to { transform: rotate(360deg); } }
        @keyframes loaderFadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* Tables */
        .table-header { background: #f8fafc; color: #475569; font-weight: 800; }
        .row-level-1 { background: #ffffff; color: #1e293b; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        .row-level-1:hover { background: #f1f5f9; }
        .row-level-2 { background: #f8fafc; color: #334155; font-weight: 600; }
        .row-level-2:hover { background: #f1f5f9; }
        .row-level-3 { background: #ffffff; color: #475569; font-weight: 600; }
        .row-level-3:hover { background: #f1f5f9; }
        .row-level-4 { background: #fbfbfb; color: #64748b; font-weight: 500; }
        .row-level-4:hover { background: #f1f5f9; }

        /* Mobile cards */
        .mobile-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.9rem;
            padding: 0.8rem 0.9rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .mobile-card-level { border-left-width: 4px; }
        .mc-l1 { border-left-color: #3b82f6; }
        .mc-l2 { border-left-color: #f59e0b; }
        .mc-l3 { border-left-color: #94a3b8; }
        .mc-l4 { border-left-color: #cbd5e1; }

        #chartPduWrapper { -webkit-overflow-scrolling: touch; overflow-x: auto; }
        #chartPduWrapper .chart-inner { min-width: 100%; }
        @media (max-width: 640px) { #chartPduWrapper .chart-inner { min-width: 620px; } }

        /* ===================== MOBILE REDESIGN ===================== */
        .chart-mobile-toggle { display: none; }
        @media (max-width: 640px) {
            .chart-mobile-toggle {
                display: flex; align-items: center; justify-content: center; gap: 6px;
                width: 100%; padding: 0.55rem; margin-top: 0.5rem;
                background: #eff6ff; color: #2563eb; border: 1px dashed #93c5fd;
                border-radius: 0.75rem; font-size: 0.75rem; font-weight: 800;
                cursor: pointer;
            }
            .chart-collapsible { display: none; }
            .chart-collapsible.chart-open { display: block; }
        }

        .mobile-summary-bar { display: none; }
        @media (max-width: 640px) {
            .mobile-summary-bar {
                display: flex; align-items: center; justify-content: space-between; gap: 10px;
                background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
                border-radius: 1rem; padding: 0.8rem 1rem; color: #fff;
                box-shadow: 0 6px 16px -4px rgba(37, 99, 235, 0.35);
                margin-bottom: 0.75rem;
            }
            .mobile-summary-bar .ms-item { text-align: left; }
            .mobile-summary-bar .ms-label { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.06em; color: #bfdbfe; font-weight: 800; }
            .mobile-summary-bar .ms-value { font-size: 0.95rem; font-weight: 900; margin-top: 1px; }
            .mobile-summary-bar .ms-divider { width: 1px; align-self: stretch; background: rgba(255,255,255,0.25); }
        }

        .mobile-card-search { display: none; }
        @media (max-width: 640px) {
            .mobile-card-search {
                display: flex; align-items: center; gap: 8px;
                background: #fff; border: 1.5px solid #e2e8f0; border-radius: 0.85rem;
                padding: 0.6rem 0.85rem; 
                margin-top: 0.75rem; 
                margin-bottom: 0.75rem;
            }
            .mobile-card-search input {
                border: none; outline: none; flex: 1; font-size: 0.8rem; font-weight: 600; color: #334155;
                background: transparent;
            }
            .mobile-card-search i { color: #94a3b8; font-size: 0.8rem; }
        }

        .acc-clickable { cursor: pointer; user-select: none; }
        .acc-clickable:active { background: #f8fafc; }
        .acc-chevron { transition: transform 0.25s ease; }
        .acc-body {
            margin-top: 0.5rem;
            padding-left: 0.6rem;
            border-left: 2px dashed #dbeafe;
            display: flex; flex-direction: column; gap: 0.5rem;
        }
        .acc-body.hidden { display: none; }
        .acc-group-wrap { margin-bottom: 0.5rem; }
        .acc-empty-hint {
            font-size: 0.68rem; color: #94a3b8; font-weight: 700; text-align: center;
            padding: 0.3rem; font-style: italic;
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative">
        <div class="mesh-bg"></div>
        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-10 flex flex-col gap-2.5 md:gap-4">

        {{-- Back Button — unified component --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
            <x-ui.back-button href="{{ route('sales.index') }}" label="Back to Sales Dashboard" />
        </div>

        {{-- =========================================================================
             UNIFIED DESKTOP HEADER WRAPPER
             ========================================================================= --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 md:gap-4 relative z-20 desktop-unified-header">
            
            {{-- Background Motif Khusus Desktop --}}
            <div class="hidden md:block absolute inset-0 rounded-[1.25rem] overflow-hidden pointer-events-none z-0">
                <div class="absolute w-[200%] h-[200%] -top-[50%] -left-[50%] pointer-events-none" style="background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); transform: rotate(30deg);"></div>
            </div>

            {{-- 1. Mobile Title Card (Original Layout) --}}
            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Monthly Monitoring</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">Monitor monthly performance details.</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

            {{-- 2. Desktop Title --}}
            <div class="hidden md:block header-content shrink-0 pr-4">
                <h1 class="text-2xl lg:text-[1.7rem] font-bold tracking-tight text-white leading-tight">Monthly Monitoring</h1>
                <p class="text-blue-100 text-[0.8rem] mt-0.5 font-medium opacity-90">Monitor monthly performance details and drill down into sales data.</p>
            </div>

            {{-- 3. Container Kontrol --}}
            <div id="monthNavCombo" class="flex flex-col md:flex-row items-stretch md:items-center gap-2.5 md:gap-3 w-full md:w-auto header-content relative">
                
                {{-- Form Select Month (Tampilan Pill Sesuai Referensi Gambar) --}}
                <div id="monthlySelectCard" class="glass-panel border-t-4 border-t-blue-500 relative">
                    <div class="flex flex-wrap md:flex-nowrap items-stretch justify-between gap-1.5 md:gap-1.5 w-full">
                        
                        {{-- Trigger Area: Clickable (Bulan) --}}
                        <div class="flex items-center justify-center gap-2 cursor-pointer group px-4 py-1.5 bg-white hover:bg-blue-50 rounded-full transition-all shadow-sm border border-transparent h-full" onclick="toggleMonthGrid()">
                            <i class="fas fa-calendar-alt text-blue-600 text-[13px]"></i>
                            
                            {{-- Bulan Aktif --}}
                            <span id="selectedMonthLabel" class="text-[13px] font-extrabold text-blue-800 uppercase tracking-wider leading-none mt-0.5">{{ $currentMonth ?? date('F') }}</span>
                            
                            {{-- Toggle Chevron --}}
                            <div class="shrink-0 ml-1 flex items-center justify-center">
                                <i class="fas fa-chevron-down text-blue-700 text-[11px] transition-transform duration-300" id="monthGridToggleIconDesktop"></i>
                            </div>
                        </div>
                        
                        {{-- Year Form & Chevron --}}
                        <div class="flex items-center h-full">
                            <form method="GET" action="{{ route('sales.monthly') }}" class="relative h-full flex items-center">
                                <select name="tahun" class="appearance-none w-full bg-white hover:bg-blue-50 border border-transparent rounded-full text-[13px] pl-4 pr-9 py-1.5 font-extrabold text-blue-800 focus:ring-0 cursor-pointer outline-none transition-colors shadow-sm leading-none h-full" onchange="this.form.submit()">
                                    @foreach($listTahun ?? [date('Y')] as $t)
                                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>Year {{ $t }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-700">
                                    <i class="fas fa-chevron-down text-[11px]"></i>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    {{-- Box 12 Bulan (Mobile: Inline Expand, Desktop: Absolute Floating 3-Col Grid) --}}
                    <div id="monthGridContainer" class="grid grid-cols-4 gap-1.5 sm:gap-2 mt-4 md:mt-0 transition-all duration-300 origin-top hidden md:hidden">
                        @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $m)
                            <button class="monthly-card group" onclick="loadMonthlyDetail('{{ $m }}', this)">
                                <span class="block uppercase">{{ $m }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Sub Tabs --}}
                <div id="monthNavExtra" class="relative">
                    <div class="grid grid-cols-4 gap-1 sm:gap-2 bg-white p-1.5 sm:p-2.5 rounded-3xl shadow-sm border border-slate-200" id="sub-tabs-container">
                        <button class="month-sub-tab active" data-target="m-view-pdu" onclick="switchMonthSubTab('m-view-pdu', this)" title="by PDU — Sales per Date" aria-label="by PDU">
                            <i class="fas fa-list"></i> <span>PDU</span>
                        </button>
                        <button class="month-sub-tab" data-target="m-view-outlet" onclick="switchMonthSubTab('m-view-outlet', this)" title="by outlet — Sales per Customer" aria-label="by outlet">
                            <i class="fas fa-store"></i> <span class="md:hidden lg:inline">Customer</span><span class="hidden md:inline lg:hidden">Cust</span>
                        </button>
                        <button class="month-sub-tab" data-target="m-view-product" onclick="switchMonthSubTab('m-view-product', this)" title="by product — Sales per Product" aria-label="by product">
                            <i class="fas fa-box"></i> <span class="md:hidden lg:inline">Product</span><span class="hidden md:inline lg:hidden">Prod</span>
                        </button>
                        <button class="month-sub-tab" data-target="m-view-closing" onclick="switchMonthSubTab('m-view-closing', this)" title="Closing — Est. Closing" aria-label="Closing">
                            <i class="fas fa-clipboard-check text-[14px]"></i>
                        </button>
                    </div>
                </div>
            </div>

        {{-- Full-page loading overlay: dims the screen with a centered spinner while monthly data loads --}}
        <div id="monthly-loader" class="hidden">
            <div class="loader-box">
                <div class="loader-spinner"></div>
                <span class="loader-text">Memuat data...</span>
            </div>
        </div>
        </div>

        {{-- Container detail — hiasan biru konsisten dengan forecast --}}
        <div id="monthly-detail-container" class="space-y-4 transition-all duration-500">
            <x-ui.glass-card padding="none" class="border-t-4 border-t-blue-500 shadow-lg !rounded-3xl !p-6">
                {{-- Tampilan 1: Data PDU --}}
                <div id="m-view-pdu" class="month-view">
                    {{-- Ringkasan cepat (mobile only) --}}
                    <div class="mobile-summary-bar">
                        <div class="ms-item">
                            <div class="ms-label">Total Qty</div>
                            <div class="ms-value" id="grandQtyPdu">0</div>
                        </div>
                        <div class="ms-divider"></div>
                        <div class="ms-item">
                            <div class="ms-label">Total Sales</div>
                            <div class="ms-value" id="grandTotalPduMobile">Rp 0</div>
                        </div>
                    </div>

                    {{-- Wadah Grafik --}}
                    <div class="mb-4 sm:mb-6 p-4 border border-slate-200 rounded-xl bg-slate-50">
                        <div class="flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center mb-4">
                            <div class="w-full sm:w-auto mb-3 sm:mb-0">
                                <h4 class="font-bold text-slate-800 text-lg">PDU Sales Chart</h4>
                                <p class="text-sm text-slate-500 font-medium mt-1">Grand Total Sales: <span class="font-black text-blue-600 text-base ml-1" id="grandTotalPdu">Rp 0</span></p>
                            </div>
                            <div class="relative inline-block w-full sm:w-auto">
                                <select id="filterPduPs" class="w-full sm:w-auto appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-sm pl-4 pr-10 py-2 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors" onchange="drawPduView()">
                                    <option value="all">All</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-700">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="chart-mobile-toggle" onclick="toggleChartMobile('chartPduChartBox', this)">
                            <i class="fas fa-chart-bar"></i> <span>Lihat Grafik</span>
                        </button>
                        <div id="chartPduChartBox" class="chart-collapsible">
                            <div id="chartPduWrapper" class="w-full">
                                <div class="chart-inner">
                                    <canvas id="chartPdu" style="max-height: 400px; width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hidden md:block overflow-x-auto border border-slate-200 rounded-xl mt-4 md:mt-6">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="table-header sticky top-0 z-10 shadow-sm bg-slate-50/80 backdrop-blur-sm">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-xl text-xs md:text-sm uppercase tracking-wider">Data</th>
                                    <th class="px-4 py-3 text-right text-xs md:text-sm uppercase tracking-wider border-l border-slate-200">Sum of Qty</th>
                                    <th class="px-4 py-3 text-right rounded-tr-xl text-xs md:text-sm uppercase tracking-wider border-l border-slate-200">Sum of Net Price</th>
                                </tr>
                            </thead>
                            <tbody id="m-tbody-pdu" class="divide-y divide-slate-200 bg-white">
                            </tbody>
                        </table>
                    </div>
                    <div class="mobile-card-search md:hidden">
                        <i class="fas fa-search"></i>
                        <input type="text" id="m-search-pdu" placeholder="Cari tanggal / sales / customer..." oninput="filterAccCards('m-cards-pdu', this.value)">
                    </div>
                    <div id="m-cards-pdu" class="md:hidden space-y-2.5 pb-6"></div>
                </div>

                {{-- Tampilan 2: Data per Outlet --}}
                <div id="m-view-outlet" class="month-view hidden">
                    {{-- Ringkasan cepat (mobile only) --}}
                    <div class="mobile-summary-bar">
                        <div class="ms-item">
                            <div class="ms-label">Total Qty</div>
                            <div class="ms-value" id="grandQtyOutlet">0</div>
                        </div>
                        <div class="ms-divider"></div>
                        <div class="ms-item">
                            <div class="ms-label">Total Sales</div>
                            <div class="ms-value" id="grandTotalOutletMobile">Rp 0</div>
                        </div>
                    </div>

                    {{-- Chart Container --}}
                    <div class="mb-4 sm:mb-6 p-4 border border-slate-200 rounded-xl bg-slate-50">
                        <div class="flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center mb-4">
                            <div class="w-full sm:w-auto mb-3 sm:mb-0">
                                <h4 class="font-bold text-slate-800 text-lg">Sales Chart per Outlet</h4>
                                <p class="text-sm text-slate-500 font-medium mt-1">Grand Total Sales: <span class="font-black text-blue-600 text-base ml-1" id="grandTotalOutlet">Rp 0</span></p>
                            </div>
                            <div class="relative inline-block w-full sm:w-auto">
                                <select id="filterOutletPs" class="w-full sm:w-auto appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-sm pl-4 pr-10 py-2 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors" onchange="drawOutletChart()">
                                    <option value="all">All</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-700">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="chart-mobile-toggle" onclick="toggleChartMobile('outletChartBox', this)">
                            <i class="fas fa-chart-bar"></i> <span>Lihat Grafik</span>
                        </button>
                        <div id="outletChartBox" class="chart-collapsible">
                            <div id="outletChartContainer" style="position: relative; height: 500px; width: 100%;">
                                <canvas id="chartOutlet"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:block overflow-x-auto border border-slate-200 rounded-xl mt-4 md:mt-6">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="table-header sticky top-0 z-10 shadow-sm bg-slate-50/80 backdrop-blur-sm">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-xl text-xs md:text-sm uppercase tracking-wider min-w-[200px] max-w-[260px]">Sales & Outlet</th>
                                    <th class="px-4 py-3 text-right text-xs md:text-sm uppercase tracking-wider border-l border-slate-200 w-40">Sum of Qty</th>
                                    <th class="px-4 py-3 text-right text-xs md:text-sm uppercase tracking-wider border-l border-slate-200 w-44">Sum of Net Price</th>
                                </tr>
                            </thead>
                            <tbody id="m-tbody-outlet" class="divide-y divide-slate-200 bg-white">
                            </tbody>
                        </table>
                    </div>
                    <div class="mobile-card-search md:hidden">
                        <i class="fas fa-search"></i>
                        <input type="text" id="m-search-outlet" placeholder="Cari sales / outlet / produk..." oninput="filterAccCards('m-cards-outlet', this.value)">
                    </div>
                    <div id="m-cards-outlet" class="md:hidden space-y-2.5 pb-6"></div>
                </div>

                {{-- Tampilan 3: Data per Produk --}}
                <div id="m-view-product" class="month-view hidden">
                    {{-- Ringkasan cepat (mobile only) --}}
                    <div class="mobile-summary-bar">
                        <div class="ms-item">
                            <div class="ms-label">Total Qty</div>
                            <div class="ms-value" id="grandQtyProduct">0</div>
                        </div>
                        <div class="ms-divider"></div>
                        <div class="ms-item">
                            <div class="ms-label">Total Sales</div>
                            <div class="ms-value" id="grandTotalProductMobile">Rp 0</div>
                        </div>
                    </div>

                    <div class="mb-4 sm:mb-6 p-4 border border-slate-200 rounded-xl bg-slate-50 flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center">
                        <div class="w-full sm:w-auto mb-3 sm:mb-0">
                            <h4 class="font-bold text-slate-800 text-lg">Sales Table per Product</h4>
                            <p class="text-sm text-slate-500 font-medium mt-1">Grand Total Sales: <span class="font-black text-blue-600 text-base ml-1" id="grandTotalProduct">Rp 0</span></p>
                        </div>
                        <div class="relative inline-block w-full sm:w-auto">
                            <select id="filterProductPs" class="w-full sm:w-auto appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-sm pl-4 pr-10 py-2 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors" onchange="drawProductTable()">
                                <option value="all">All</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-700">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hidden md:block overflow-x-auto border border-slate-200 rounded-xl mt-4 md:mt-6">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="table-header sticky top-0 z-10 shadow-sm bg-slate-50/80 backdrop-blur-sm">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-xl text-xs md:text-sm uppercase tracking-wider">Sales & Product</th>
                                    <th class="px-4 py-3 text-right text-xs md:text-sm uppercase tracking-wider border-l border-slate-200">Sum of Qty</th>
                                    <th class="px-4 py-3 text-right rounded-tr-xl text-xs md:text-sm uppercase tracking-wider border-l border-slate-200">Sum of Net Price</th>
                                </tr>
                            </thead>
                            <tbody id="m-tbody-product" class="divide-y divide-slate-200 bg-white">
                            </tbody>
                        </table>
                    </div>
                    <div class="mobile-card-search md:hidden">
                        <i class="fas fa-search"></i>
                        <input type="text" id="m-search-product" placeholder="Cari nama produk..." oninput="filterAccCards('m-cards-product', this.value)">
                    </div>
                    <div id="m-cards-product" class="md:hidden space-y-2.5 pb-6"></div>
                </div>

                {{-- Tampilan 4: Closing — Input per Outlet, Produk Read-only Lengkap --}}
                <div id="m-view-closing" class="month-view hidden">
                    {{-- Ringkasan cepat (mobile only) --}}
                    <div class="mobile-summary-bar">
                        <div class="ms-item">
                            <div class="ms-label">Total Sales</div>
                            <div class="ms-value" id="grandTotalClosingMobile">Rp 0</div>
                        </div>
                        <div class="ms-divider"></div>
                        <div class="ms-item">
                            <div class="ms-label">Total Add</div>
                            <div class="ms-value" id="grandAddClosingMobile">Rp 0</div>
                        </div>
                        <div class="ms-divider"></div>
                        <div class="ms-item">
                            <div class="ms-label">Total Akhir</div>
                            <div class="ms-value" id="grandTotalAkhirMobile">Rp 0</div>
                        </div>
                    </div>

                    <div class="mb-4 sm:mb-6 p-4 border border-slate-200 rounded-xl bg-slate-50 flex flex-col lg:flex-row gap-3 justify-between items-start lg:items-center">
                        <div class="w-full lg:w-auto mb-3 lg:mb-0">
                            <h4 class="font-bold text-slate-800 text-lg">Closing Adjustment</h4>
                            <p class="text-sm text-slate-500 font-medium mt-1">Grand Total = Total Sum Nett + Total Est. Closing &nbsp;<br> Total Sum Nett: <span class="font-black text-blue-600 text-base ml-1" id="grandTotalClosing">Rp 0</span> <span class="text-slate-400 hidden sm:inline">| Total Est. Closing: <span id="grandAddClosing" class="font-bold text-purple-600">Rp 0</span> | Grand Total: <span id="grandTotalAkhir" class="font-black text-green-600">Rp 0</span></span></p>
                        </div>
                        <div class="relative inline-block w-full lg:w-auto">
                            <select id="filterClosingPs" class="w-full lg:w-auto appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-sm pl-4 pr-10 py-2 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors" onchange="drawClosingView()">
                                <option value="all">All</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-700">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block overflow-x-auto border border-slate-200 rounded-xl mt-4 md:mt-6">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="table-header sticky top-0 z-10 shadow-sm bg-slate-50/80 backdrop-blur-sm">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-xl text-xs md:text-sm uppercase tracking-wider">Sales & Outlet</th>
                                    <th class="px-4 py-3 text-right text-xs md:text-sm uppercase tracking-wider border-l border-slate-200 w-32">Sum of Qty</th>
                                    <th class="px-4 py-3 text-right text-xs md:text-sm uppercase tracking-wider border-l border-slate-200 w-44">Sum of Net Price</th>
                                    <th class="px-3 py-3 text-center text-xs md:text-sm uppercase tracking-wider border-l border-slate-200 w-36 bg-purple-50 text-purple-700">Est. Closing</th>
                                    <th class="px-4 py-3 text-right rounded-tr-xl text-xs md:text-sm uppercase tracking-wider border-l border-slate-200 w-44 bg-emerald-50 text-emerald-700">Total</th>
                                </tr>
                            </thead>
                            <tbody id="m-tbody-closing" class="divide-y divide-slate-200 bg-white">
                            </tbody>
                        </table>
                    </div>
                    <div class="mobile-card-search md:hidden">
                        <i class="fas fa-search"></i>
                        <input type="text" id="m-search-closing" placeholder="Cari sales / outlet / produk..." oninput="filterAccCards('m-cards-closing', this.value)">
                    </div>
                    <div id="m-cards-closing" class="md:hidden space-y-2.5 pb-6"></div>
                </div>
            </x-ui.glass-card>
        </div>
    </div>
    </div>

    @push('scripts')
    <script>
        const hasFullAccess = @json(isset($hasFullAccess) && $hasFullAccess);

        // Auto load current month ketika halaman selesai dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const initialMonth = "{{ $currentMonth ?? date('F') }}";
            let targetBtn = null;
            
            // Cari tombol yang text-nya sama persis dengan bulan ini
            document.querySelectorAll('.monthly-card').forEach(c => {
                if (c.innerText.trim().toUpperCase() === initialMonth.toUpperCase()) {
                    targetBtn = c;
                }
            });
            
            // Langsung load datanya
            loadMonthlyDetail(initialMonth, targetBtn);

            // Inisiasi rotasi chevron di mobile bila grid bulan disembunyikan
            const iconMobile = document.getElementById('monthGridToggleIcon');
            const container = document.getElementById('monthGridContainer');
            if (iconMobile && container && container.classList.contains('hidden')) {
                iconMobile.classList.add('rotate-180');
            }
        });

        // Ubah tampilan toggle grid grid bulan agar smooth & sinkron
        function toggleMonthGrid() {
            const container = document.getElementById('monthGridContainer');
            const iconMobile = document.getElementById('monthGridToggleIcon');
            const iconDesktop = document.getElementById('monthGridToggleIconDesktop');
            
            if (!container.classList.contains('hidden') || container.style.display === 'grid') {
                container.classList.add('hidden');
                if (iconMobile) iconMobile.classList.add('rotate-180');
                if (iconDesktop) iconDesktop.classList.remove('rotate-180');
            } else {
                container.classList.remove('hidden');
                if (iconMobile) iconMobile.classList.remove('rotate-180');
                if (iconDesktop) iconDesktop.classList.add('rotate-180');
            }
        }

        // Close dropdown when clicking outside on desktop
        document.addEventListener('click', function(event) {
            const combo = document.getElementById('monthNavCombo');
            const container = document.getElementById('monthGridContainer');
            const iconDesktop = document.getElementById('monthGridToggleIconDesktop');
            const iconMobile = document.getElementById('monthGridToggleIcon');
            if (combo && container && !combo.contains(event.target)) {
                container.classList.add('hidden');
                if (iconDesktop) iconDesktop.classList.remove('rotate-180');
                if (iconMobile) iconMobile.classList.add('rotate-180');
            }
        });

        function switchMonthSubTab(targetId, btnEl) {
            document.querySelectorAll('.month-sub-tab').forEach(b => {
                b.classList.remove('active');
            });
            btnEl.classList.add('active');

            document.querySelectorAll('.month-view').forEach(v => v.classList.add('hidden'));
            document.getElementById(targetId).classList.remove('hidden');
        }

        let pduChartInstance = null;
        let outletChartInstance = null;
        let closingChartInstance = null;
        let currentMonthlyData = null;
        let currentSelectedBulan = "{{ $currentMonth }}";
        const HAS_FULL_ACCESS = {{ json_encode($hasFullAccess ?? false) }};

        function escapeJsStr(str) {
            if (!str) return '';
            return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }

        function formatNumberWithDots(val) {
            if (val === null || val === undefined || val === '') return '';
            const num = Math.round(Number(val));
            if (isNaN(num)) return '';
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function parseRawNumber(valStr) {
            if (valStr === null || valStr === undefined || valStr === '') return null;
            let clean = String(valStr).replace(/[^\d]/g, '');
            if (!clean) return null;
            return parseInt(clean, 10);
        }

        function onClosingInputFormat(inputEl) {
            let cursorPos = inputEl.selectionStart;
            let oldLen = inputEl.value.length;
            let raw = inputEl.value.replace(/[^\d]/g, '');
            if (!raw) {
                inputEl.value = '';
                return;
            }
            let formatted = new Intl.NumberFormat('id-ID').format(parseInt(raw, 10));
            inputEl.value = formatted;
            let newLen = inputEl.value.length;
            cursorPos = cursorPos + (newLen - oldLen);
            if (cursorPos < 0) cursorPos = 0;
            try { inputEl.setSelectionRange(cursorPos, cursorPos); } catch(e) {}
        }

        // Tab 2 (Outlet) — sekarang READ-ONLY (input pindah ke Tab Closing)
        function renderClosingRateCell(ps, customerName, rateVal) {
            const displayVal = (rateVal !== null && rateVal !== undefined && rateVal !== '') ? `Rp ${formatNumberWithDots(rateVal)}` : '-';
            return `<td class="px-3 py-2 text-right border-l border-slate-200 text-xs font-semibold text-slate-600 w-36">${displayVal}</td>`;
        }
        function renderClosingCountCell(ps, customerName, countVal) {
            const displayVal = (countVal !== null && countVal !== undefined && countVal !== '') ? formatNumberWithDots(countVal) : '-';
            return `<td class="px-3 py-2 text-center border-l border-slate-200 text-xs font-semibold text-slate-700 w-32">${displayVal}</td>`;
        }
        // Tab 4 (Closing) — input per outlet, hanya editable di tab ini
        function renderAddClosingCell(ps, customerName, val) {
            if (!HAS_FULL_ACCESS) {
                const displayVal = (val !== null && val !== undefined && val !== '') ? `Rp ${formatNumberWithDots(val)}` : '-';
                return `<td class="px-3 py-2 text-right border-l border-slate-200 text-xs font-semibold text-slate-700 w-36">${displayVal}</td>`;
            }
            const v = (val !== null && val !== undefined && val !== '') ? formatNumberWithDots(val) : '';
            const safePs = escapeJsStr(ps);
            const safeCust = escapeJsStr(customerName);
            return `<td class="px-2 py-1 text-center border-l border-slate-200 w-36">
                <input type="text" placeholder="0" value="${v}"
                    class="w-24 text-right text-xs py-1 px-2 border border-amber-300 rounded focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-bold text-slate-800 bg-amber-50 mx-auto block"
                    oninput="onClosingInputFormat(this)"
                    onchange="saveOutletClosing('${safePs}', '${safeCust}', 'closing_rate', this.value, this)">
            </td>`;
        }
        function renderAddClosingSalesCell(ps, customerName, val) {
            if (!HAS_FULL_ACCESS) {
                const displayVal = (val !== null && val !== undefined && val !== '') ? formatNumberWithDots(val) : '-';
                return `<td class="px-3 py-2 text-center border-l border-slate-200 text-xs font-bold text-purple-700 bg-purple-50/40 w-36">${displayVal}</td>`;
            }
            const v = (val !== null && val !== undefined && val !== '') ? formatNumberWithDots(val) : '';
            const safePs = escapeJsStr(ps);
            const safeCust = escapeJsStr(customerName);
            return `<td class="px-2 py-1 text-center border-l border-slate-200 w-36 bg-purple-50/30">
                <input type="text" placeholder="0" value="${v}"
                    class="w-20 text-center text-xs py-1 px-1.5 border border-purple-300 rounded focus:border-purple-500 focus:ring-1 focus:ring-purple-300 font-bold text-purple-800 bg-white mx-auto block"
                    oninput="onClosingInputFormat(this)"
                    onchange="saveOutletClosing('${safePs}', '${safeCust}', 'closing_count', this.value, this)">
            </td>`;
        }
        function renderTotalAkhirCell(nett, addClosing) {
            const add = parseInt(addClosing||0,10) || 0;
            const total = (parseFloat(nett)||0) + add;
            return `<td class="px-4 py-2 text-right border-l border-slate-200 font-black text-emerald-700 bg-emerald-50/40 text-xs w-44 closing-total-cell">${fRp(total)}</td>`;
        }

        function saveOutletClosing(ps, customerName, field, value, inputEl) {
            const year = document.getElementById('filterTahun')?.value || "{{ $tahun }}";
            const month = currentSelectedBulan || "{{ $currentMonth }}";

            if (inputEl) {
                inputEl.classList.remove('border-emerald-500', 'border-red-500', 'border-slate-300');
                inputEl.classList.add('border-blue-500', 'ring-2', 'ring-blue-200');
            }

            const tr = inputEl ? inputEl.closest('tr') : null;
            let rateInput = tr ? tr.querySelector('input[onchange*="closing_rate"]') : null;
            let countInput = tr ? tr.querySelector('input[onchange*="closing_count"]') : null;

            const rateVal = rateInput ? rateInput.value : (field === 'closing_rate' ? value : null);
            const countVal = countInput ? countInput.value : (field === 'closing_count' ? value : null);

            const payload = {
                _token: '{{ csrf_token() }}',
                year: parseInt(year),
                month: month,
                ps: ps || null,
                customer_name: customerName,
                closing_rate: parseRawNumber(rateVal),
                closing_count: parseRawNumber(countVal)
            };

            fetch('{{ route("sales.monthly.update-closing") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (inputEl) {
                        inputEl.classList.remove('border-blue-500', 'ring-2', 'ring-blue-200', 'border-red-500');
                        inputEl.classList.add('border-emerald-500', 'ring-2', 'ring-emerald-200');
                        setTimeout(() => {
                            inputEl.classList.remove('border-emerald-500', 'ring-2', 'ring-emerald-200');
                            inputEl.classList.add('border-slate-300');
                        }, 1500);
                    }
                    if (currentMonthlyData && currentMonthlyData.outlet) {
                        let matchedNett = 0;
                        let matchedAdd = 0;
                        currentMonthlyData.outlet.forEach(p => {
                            if (p.customer) {
                                p.customer.forEach(c => {
                                    const psMatch = !ps || String(p.nama).toLowerCase().trim() === String(ps).toLowerCase().trim();
                                    if (c.nama === customerName && psMatch) {
                                        c.closing_rate = payload.closing_rate;
                                        c.closing_count = payload.closing_count;
                                        c.add_closing = payload.closing_rate;
                                        c.add_closing_sales = payload.closing_count;
                                        const addVal = payload.closing_count ?? payload.closing_rate ?? 0;
                                        c.total_akhir = (parseFloat(c.nett)||0) + (parseFloat(addVal)||0);
                                        matchedNett = c.nett;
                                        matchedAdd = addVal;
                                    }
                                });
                            }
                        });
                        // update Total Akhir cell in Closing tab inline without full redraw
                        if (tr) {
                            const totalCell = tr.querySelector('.closing-total-cell');
                            if (totalCell) {
                                const totalVal = (parseFloat(matchedNett)||0) + (parseFloat(matchedAdd)||0);
                                totalCell.innerHTML = fRp(totalVal);
                                totalCell.classList.add('bg-purple-100');
                                setTimeout(()=> totalCell.classList.remove('bg-purple-100'), 1200);
                            }
                        }
                        // update grand totals for Closing tab without full redraw (recalc) — only Add. Closing Sales
                        try {
                            let gNett=0,gAdd=0,gAkhir=0,gSales=0;
                            currentMonthlyData.outlet.forEach(p=> p.customer.forEach(c=>{ const add = c.add_closing_sales ?? c.closing_count ?? c.add_closing ?? c.closing_rate ?? 0; gNett+=parseFloat(c.nett)||0; gAdd+=parseFloat(add)||0; gAkhir+=(parseFloat(c.nett)||0)+(parseFloat(add)||0); gSales+=parseFloat(add)||0; }));
                            const setT=(id,v)=>{const el=document.getElementById(id); if(el) el.innerText=v;};
                            setT('grandTotalClosing', fRp(gNett));
                            setT('grandAddClosing', fRp(gAdd));
                            setT('grandTotalAkhir', fRp(gAkhir));
                            setT('grandTotalClosingMobile', fRp(gNett));
                            setT('grandAddClosingMobile', fRp(gAdd));
                            setT('grandTotalAkhirMobile', fRp(gAkhir));
                            // also refresh outlet tab grand totals if visible
                            // lightweight: call drawOutletChart only if needed? skip to avoid flicker
                        } catch(e){}
                    }
                } else {
                    alert(data.error || 'Gagal menyimpan data closing.');
                    if (inputEl) {
                        inputEl.classList.remove('border-blue-500', 'ring-2', 'ring-blue-200');
                        inputEl.classList.add('border-red-500', 'ring-2', 'ring-red-200');
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (inputEl) {
                    inputEl.classList.remove('border-blue-500', 'ring-2', 'ring-blue-200');
                    inputEl.classList.add('border-red-500', 'ring-2', 'ring-red-200');
                }
            });
        }

        const fRp = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n||0));
        const fNum = (n) => new Intl.NumberFormat('id-ID').format(n||0);
        
        const fQtyStr = (q, s) => {
            if (s) {
                return `${fNum(q)} <span class="font-semibold text-[10px] text-slate-500 ml-0.5">${s}</span>`;
            }
            return `${fNum(q)}`;
        };

        let accSeq = 0;
        function accNode(icon, label, qtyStr, nett, level, parentId, parentAncestors, hasChildren, closingRate, closingCount) {
            level = Math.min(level || 1, 4);
            const id = 'acc' + (accSeq++);
            const ancestors = parentId ? (parentAncestors || []).concat([parentId]) : [];
            const border = ['mc-l1', 'mc-l2', 'mc-l3', 'mc-l4'][level - 1];
            const weight = level === 1 ? 'font-black' : 'font-semibold';
            const textColor = level === 1 ? 'text-slate-800' : 'text-slate-600';
            const startsHidden = level > 1;
            const safeLabel = String(label).toLowerCase().replace(/"/g, '&quot;');
            const chevron = hasChildren
                ? `<i class="fas fa-chevron-right acc-chevron text-slate-300 text-[10px] ml-1 shrink-0" id="${id}-chev"></i>`
                : '';
            const clickAttr = hasChildren ? ` onclick="toggleAcc('${id}')"` : '';
            const classes = [
                'mobile-card', 'mobile-card-level', 'acc-node', border,
                hasChildren ? 'acc-clickable' : '',
                startsHidden ? 'hidden' : '',
            ].filter(Boolean).join(' ');

            let closingBadge = '';
            if (closingRate !== undefined && closingRate !== null || closingCount !== undefined && closingCount !== null) {
                const hasRate = closingRate !== null && closingRate !== undefined && closingRate !== '';
                const hasCount = closingCount !== null && closingCount !== undefined && closingCount !== '';
                const rateText = hasRate ? `Rp ${formatNumberWithDots(closingRate)}` : '-';
                const countText = hasCount ? `${formatNumberWithDots(closingCount)}` : '-';
                if (hasRate || hasCount) {
                    closingBadge = `<div class="text-[10px] font-semibold mt-0.5 flex flex-wrap gap-1"><span class="bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-200">Add: ${rateText}</span><span class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded border border-slate-200">Sales: ${countText}</span></div>`;
                }
            }

            const html = `<div id="${id}" class="${classes}" data-parent="${parentId || ''}" data-ancestors="${ancestors.join(',')}" data-label="${safeLabel}"${clickAttr}>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex flex-col min-w-0 pr-2">
                        <div class="flex items-center gap-2.5">
                            ${icon ? `<i class="${icon} text-slate-400 text-xs shrink-0"></i>` : `<span class="w-1 shrink-0"></span>`}
                            <span class="${weight} ${textColor} text-[13px] leading-snug break-words">${label}</span>
                            ${chevron}
                        </div>
                        ${closingBadge}
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-black text-slate-800 text-[13px] leading-tight">${qtyStr}</div>
                        <div class="font-black text-emerald-600 text-[11px] leading-tight mt-1">${nett}</div>
                    </div>
                </div>
            </div>`;

            return { id, ancestors, html };
        }

        function mcEmpty(msg) {
            return `<div class="text-center p-6 text-slate-500 font-medium text-sm bg-white border border-slate-200 rounded-xl">${msg}</div>`;
        }

        function toggleAcc(id) {
            const children = document.querySelectorAll(`[data-parent="${id}"]`);
            if (!children.length) return;
            const chev = document.getElementById(id + '-chev');
            const isCollapsed = children[0].classList.contains('hidden');

            if (isCollapsed) {
                children.forEach(c => c.classList.remove('hidden'));
                if (chev) chev.style.transform = 'rotate(90deg)';
            } else {
                children.forEach(c => c.classList.add('hidden'));
                document.querySelectorAll('[data-ancestors]').forEach(n => {
                    const anc = (n.getAttribute('data-ancestors') || '').split(',').filter(Boolean);
                    if (anc.includes(id)) {
                        n.classList.add('hidden');
                        const cc = document.getElementById(n.id + '-chev');
                        if (cc) cc.style.transform = 'rotate(0deg)';
                    }
                });
                if (chev) chev.style.transform = 'rotate(0deg)';
            }
        }

        function filterAccCards(containerId, query) {
            const container = document.getElementById(containerId);
            if (!container) return;
            query = (query || '').trim().toLowerCase();
            const nodes = container.querySelectorAll('.acc-node');

            if (!query) {
                nodes.forEach(n => {
                    n.classList.toggle('hidden', !!n.getAttribute('data-parent'));
                    const chev = document.getElementById(n.id + '-chev');
                    if (chev) chev.style.transform = 'rotate(0deg)';
                });
                return;
            }

            nodes.forEach(n => n.classList.add('hidden'));
            nodes.forEach(n => {
                const label = n.getAttribute('data-label') || '';
                if (!label.includes(query)) return;
                n.classList.remove('hidden');
                const chev = document.getElementById(n.id + '-chev');
                if (chev) chev.style.transform = 'rotate(90deg)';
                (n.getAttribute('data-ancestors') || '').split(',').filter(Boolean).forEach(aid => {
                    const anode = document.getElementById(aid);
                    if (!anode) return;
                    anode.classList.remove('hidden');
                    const achev = document.getElementById(aid + '-chev');
                    if (achev) achev.style.transform = 'rotate(90deg)';
                });
            });
        }

        function toggleChartMobile(boxId, btnEl) {
            const box = document.getElementById(boxId);
            if (!box) return;
            const isOpen = box.classList.toggle('chart-open');
            const label = btnEl.querySelector('span');
            if (label) label.textContent = isOpen ? 'Sembunyikan Grafik' : 'Lihat Grafik';
            const icon = btnEl.querySelector('i');
            if (icon) icon.classList.toggle('fa-chevron-up', isOpen);
        }

        function drawOutletChart() {
            if (!currentMonthlyData) return;
            const data = currentMonthlyData;
            const psFilter = document.getElementById('filterOutletPs').value;
            let n1, n2;

            if (outletChartInstance) outletChartInstance.destroy();

            let outletLabels = [];
            let outletSales = [];
            let outletBgColors = [];

            const colorPalette = ['#3b82f6', '#f97316', '#10b981', '#8b5cf6', '#ef4444', '#06b6d4', '#f59e0b', '#ec4899', '#64748b'];
            let psColorMap = {};
            let colorIndex = 0;

            let htmlOutlet = '';
            let htmlCards = '';
            let totalQtyOutlet = 0;
            let totalNettOutlet = 0;

            if (psFilter === 'all' || psFilter === 'Sales Team') {
                // General: tampilkan per PS + per Customer terpisah (jangan merge customer nama sama beda PS)
                let allEntries = [];
                data.outlet.forEach(ps => {
                    if (psFilter === 'Sales Team' && ps.nama.toLowerCase() === 'office') return;
                    totalQtyOutlet += ps.total_qty;
                    totalNettOutlet += ps.total_nett;
                    if (!psColorMap[ps.nama]) {
                        psColorMap[ps.nama] = colorPalette[colorIndex % colorPalette.length];
                        colorIndex++;
                    }
                    ps.customer.forEach(c => {
                        outletLabels.push(`[${ps.nama}] ${c.nama}`);
                        outletSales.push(c.nett);
                        outletBgColors.push(psColorMap[ps.nama]);
                        allEntries.push({ ps: ps.nama, c: c });
                    });
                });
                // sort global by nett desc biar yang terbesar di atas, tapi tetap pisah per PS
                allEntries.sort((a,b)=> b.c.nett - a.c.nett);
                allEntries.forEach(entry => {
                    const psName = entry.ps;
                    const c = entry.c;
                    htmlOutlet += `
                        <tr class="row-level-1 hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3"><div class="flex items-start"><i class="fas fa-store mr-3 mt-1 text-amber-500 w-4 text-center"></i><span class="font-bold text-slate-700">${c.nama}</span><span class="ml-2 text-[10px] bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100 text-blue-600">${psName}</span></div></td>
                            <td class="px-4 py-3 text-right border-l border-slate-200 font-semibold">${fNum(c.total_qty)}</td>
                            <td class="px-4 py-3 text-right border-l border-slate-200 font-semibold text-emerald-600">${fRp(c.nett)}</td>
                        </tr>
                    `;
                    htmlCards += (n1 = accNode('fas fa-store', `${c.nama} [${psName}]`, fQtyStr(c.total_qty), fRp(c.nett), 1, null, [], c.produk && c.produk.length > 0)).html;
                    [...c.produk].sort((a,b)=>b.nett-a.nett).forEach(p => {
                        let tableQtyStr = p.satuan ? `${fNum(p.qty)} <span class="text-xs text-slate-400 font-semibold ml-1">${p.satuan}</span>` : fNum(p.qty);
                        htmlOutlet += `
                            <tr class="row-level-2 hover:bg-slate-50 transition-colors">
                                <td class="py-2 pr-4 pl-8 md:pl-10 break-words whitespace-normal leading-tight"><div class="flex items-start"><span class="w-4 mr-3 inline-block"></span><span class="text-slate-500">${p.nama}</span></div></td>
                                <td class="px-4 py-2 text-right border-l border-slate-200">${tableQtyStr}</td>
                                <td class="px-4 py-2 text-right border-l border-slate-200 text-slate-500">${fRp(p.nett)}</td>
                            </tr>
                        `;
                        htmlCards += accNode('', p.nama, fQtyStr(p.qty, p.satuan), fRp(p.nett), 2, n1.id, n1.ancestors, false).html;
                    });
                });
            } else {
                data.outlet.forEach(ps => {
                    if (!psColorMap[ps.nama]) {
                        psColorMap[ps.nama] = colorPalette[colorIndex % colorPalette.length];
                        colorIndex++;
                    }

                    if (psFilter === ps.nama) {
                        totalQtyOutlet += ps.total_qty;
                        totalNettOutlet += ps.total_nett;
                        ps.customer.forEach(c => {
                            outletLabels.push(`[${ps.nama}] ${c.nama}`);
                            outletSales.push(c.nett);
                            outletBgColors.push(psColorMap[ps.nama]);
                        });
                        
                        htmlOutlet += `
                            <tr class="row-level-1 hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3"><div class="flex items-start"><i class="fas fa-users mr-2 mt-1 text-blue-500 w-4 text-center"></i><span class="font-bold text-slate-700">${ps.nama}</span></div></td>
                                <td class="px-4 py-3 text-right border-l border-slate-200 font-semibold">${fNum(ps.total_qty)}</td>
                                <td class="px-4 py-3 text-right border-l border-slate-200 font-semibold text-emerald-600">${fRp(ps.total_nett)}</td>
                            </tr>
                        `;
                        htmlCards += (n1 = accNode('fas fa-users', ps.nama, fQtyStr(ps.total_qty), fRp(ps.total_nett), 1, null, [], ps.customer && ps.customer.length > 0)).html;
                        ps.customer.forEach(c => {
                            htmlOutlet += `
                                <tr class="row-level-2 hover:bg-slate-50 transition-colors">
                                    <td class="py-2 pr-4 pl-6 md:pl-10"><div class="flex items-start"><i class="fas fa-store mr-3 mt-1 text-amber-500 w-4 text-center"></i><span class="font-bold text-slate-700">${c.nama}</span></div></td>
                                    <td class="px-4 py-2 text-right border-l border-slate-200 font-semibold">${fNum(c.total_qty)}</td>
                                    <td class="px-4 py-2 text-right border-l border-slate-200 font-semibold text-emerald-600">${fRp(c.nett)}</td>
                                </tr>
                            `;
                            htmlCards += (n2 = accNode('fas fa-store', c.nama, fQtyStr(c.total_qty), fRp(c.nett), 2, n1.id, n1.ancestors, c.produk && c.produk.length > 0)).html;
                            c.produk.forEach(p => {
                                let tableQtyStr = p.satuan ? `${fNum(p.qty)} <span class="text-xs text-slate-400 font-semibold ml-1">${p.satuan}</span>` : fNum(p.qty);
                                htmlOutlet += `
                                    <tr class="row-level-3 hover:bg-slate-50 transition-colors">
                                        <td class="py-2 pr-4 pl-8 md:pl-12 break-words whitespace-normal leading-tight"><div class="flex items-start"><span class="w-4 mr-3 inline-block"></span><span>${p.nama}</span></div></td>
                                        <td class="px-4 py-2 text-right border-l border-slate-200">${tableQtyStr}</td>
                                        <td class="px-4 py-2 text-right border-l border-slate-200 text-slate-500">${fRp(p.nett)}</td>
                                    </tr>
                                `;
                                htmlCards += accNode('', p.nama, fQtyStr(p.qty, p.satuan), fRp(p.nett), 3, n2.id, n2.ancestors, false).html;
                            });
                        });
                    }
                });
            }
            
            if (htmlOutlet) {
                htmlOutlet += `
                    <tr class="bg-blue-50 font-bold border-t-2 border-blue-200">
                        <td class="px-4 py-3">GRAND TOTAL</td>
                        <td class="px-4 py-3 text-right border-l border-slate-200">${fNum(totalQtyOutlet)}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-200 text-emerald-700">${fRp(totalNettOutlet)}</td>
                    </tr>
                `;
            }
            document.getElementById('m-tbody-outlet').innerHTML = htmlOutlet || '<tr><td colspan="3" class="text-center p-6 text-slate-500 font-medium">No data</td></tr>';
            document.getElementById('m-cards-outlet').innerHTML = (htmlCards ? htmlCards + accNode('fas fa-flag-checkered', 'GRAND TOTAL', fQtyStr(totalQtyOutlet), fRp(totalNettOutlet), 1, null, [], false).html : mcEmpty('No data'));
            document.getElementById('m-search-outlet').value = '';

            let gtOutletEl = document.getElementById('grandTotalOutlet');
            if (gtOutletEl) gtOutletEl.innerText = fRp(totalNettOutlet);
            let gtOutletMobileEl = document.getElementById('grandTotalOutletMobile');
            if (gtOutletMobileEl) gtOutletMobileEl.innerText = fRp(totalNettOutlet);
            let gqOutletEl = document.getElementById('grandQtyOutlet');
            if (gqOutletEl) gqOutletEl.innerText = fNum(totalQtyOutlet);

            const container = document.getElementById('outletChartContainer');
            const dynamicHeight = Math.max(300, outletLabels.length * 40 + 80);
            container.style.height = dynamicHeight + 'px';
            const ctxOutlet = document.getElementById('chartOutlet').getContext('2d');
            outletChartInstance = new Chart(ctxOutlet, {
                type: 'bar',
                data: {
                    labels: outletLabels,
                    datasets: [{
                        label: 'Total Sales (Net Price)',
                        data: outletSales,
                        backgroundColor: outletBgColors,
                        borderRadius: 4,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    events: ['mousemove', 'mouseout', 'click', 'touchstart'],
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'end',
                            color: '#475569',
                            font: { size: 10, weight: 'bold' },
                            formatter: function(value) {
                                if (value === null || value === 0) return '';
                                return new Intl.NumberFormat('id-ID').format(value);
                            }
                        },
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) { return 'Sales: ' + fRp(context.raw); }
                            }
                        }
                    },
                    scales: {
                        x: {
                            min: 0,
                            suggestedMax: Math.max(0, ...outletSales) + 15000000,
                            ticks: {
                                callback: function(value) { return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact" , compactDisplay: "short" }).format(value); },
                                font: { size: 10 },
                                maxTicksLimit: 7
                            },
                            grid: { color: 'rgba(148,163,184,0.12)' },
                            border: { display: false }
                        },
                        y: {
                            ticks: {
                                autoSkip: false,
                                font: { size: 11, weight: '600' },
                                padding: 14,
                                color: '#1e293b',
                                maxWidth: 220,
                                callback: function(value) {
                                    const label = this.getLabelForValue(value);
                                    if (String(label).length > 42) return String(label).substring(0,42) + '…';
                                    return label;
                                }
                            },
                            grid: { display: false },
                            border: { display: false }
                        }
                    },
                    layout: { padding: { left: 18, right: 18 } }
                }
            });
        }

        function drawClosingView() {
            if (!currentMonthlyData) return;
            const data = currentMonthlyData;
            const psFilter = document.getElementById('filterClosingPs').value;
            let htmlClosing = '';
            let htmlCards = '';
            let totalQty = 0, totalNett = 0, totalAdd = 0, totalAkhir = 0, totalAddSales = 0;
            let closingLabels = [], closingSales = [], closingAdd = [], closingBg = [];
            const palette = ['#3b82f6','#f97316','#10b981','#8b5cf6','#ef4444','#06b6d4','#f59e0b','#ec4899','#64748b'];
            let colorMap = {}, cIdx=0;
            if (closingChartInstance) closingChartInstance.destroy();

            const buildRow = (psName, c, level) => {
                const addSalesVal = c.add_closing_sales ?? c.closing_count ?? c.add_closing ?? c.closing_rate ?? null;
                const total = (parseFloat(c.nett)||0) + (parseFloat(addSalesVal)||0);
                let salesCell = renderAddClosingSalesCell(psName, c.nama, addSalesVal);
                let totalCell = renderTotalAkhirCell(c.nett, addSalesVal);
                return {salesCell, totalCell, total, addSalesVal};
            };

            if (psFilter === 'all' || psFilter === 'Sales Team') {
                // General: pisah per PS + Customer (jangan merge nama sama beda PS)
                let allEntries = [];
                data.outlet.forEach(ps => {
                    if (psFilter === 'Sales Team' && ps.nama.toLowerCase() === 'office') return;
                    if (!colorMap[ps.nama]) { colorMap[ps.nama]=palette[cIdx%palette.length]; cIdx++; }
                    ps.customer.forEach(c => {
                        allEntries.push({ ps: ps.nama, c: c });
                        totalQty+=c.total_qty;
                        totalNett+=c.nett;
                    });
                });
                totalAdd = 0; totalAkhir = 0; totalAddSales = 0;
                // hitung totalAdd/totalAkhir dulu untuk grand total
                allEntries.forEach(e => {
                    const add = e.c.add_closing_sales ?? e.c.closing_count ?? e.c.add_closing ?? e.c.closing_rate ?? 0;
                    totalAdd += parseFloat(add)||0;
                    totalAkhir += (parseFloat(e.c.nett)||0) + (parseFloat(add)||0);
                    totalAddSales += parseFloat(add)||0;
                });
                allEntries.sort((a,b)=> {
                    const totA = (parseFloat(a.c.nett)||0)+(parseFloat(a.c.add_closing_sales ?? a.c.closing_count ?? a.c.add_closing ?? a.c.closing_rate)||0);
                    const totB = (parseFloat(b.c.nett)||0)+(parseFloat(b.c.add_closing_sales ?? b.c.closing_count ?? b.c.add_closing ?? b.c.closing_rate)||0);
                    return totB - totA;
                });
                allEntries.forEach(entry => {
                    const psName = entry.ps;
                    const c = entry.c;
                    if (!colorMap[psName]) { colorMap[psName]=palette[cIdx%palette.length]; cIdx++; }
                    const {salesCell, totalCell, total, addSalesVal} = buildRow(psName, c, 1);
                    closingLabels.push(`[${psName}] ${c.nama}`);
                    closingSales.push(c.nett);
                    closingAdd.push(parseFloat(addSalesVal)||0);
                    closingBg.push(colorMap[psName]||'#3b82f6');
                    htmlClosing += `<tr class="row-level-1 hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3"><div class="flex items-start"><i class="fas fa-store mr-3 mt-1 text-amber-500 w-4"></i><span class="font-bold text-slate-700">${c.nama}</span><span class="ml-2 text-[10px] bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100 text-blue-600">${psName}</span></div></td>
                        <td class="px-4 py-3 text-right border-l border-slate-200 font-semibold">${fNum(c.total_qty)}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-200 font-semibold text-slate-600">${fRp(c.nett)}</td>
                        ${salesCell}
                        ${totalCell}
                    </tr>`;
                    let n1 = accNode('fas fa-store', `${c.nama} <span class="text-[10px] text-blue-500 font-bold ml-1">${psName}</span>`, fQtyStr(c.total_qty), `Total: ${fRp(total)}`, 1, null, [], c.produk && c.produk.length>0, null, addSalesVal);
                    htmlCards += n1.html;
                    [...c.produk].sort((a,b)=>b.nett-a.nett).forEach(p=>{
                        let qtyStr = p.satuan? `${fNum(p.qty)} <span class="text-xs text-slate-400 font-semibold ml-1">${p.satuan}</span>` : fNum(p.qty);
                        htmlClosing += `<tr class="row-level-2 hover:bg-slate-50 transition-colors">
                            <td class="py-2 pr-4 pl-8 md:pl-10 break-words whitespace-normal"><div class="flex items-start"><span class="w-4 mr-3 inline-block"></span><span class="text-slate-500">${p.nama}</span></div></td>
                            <td class="px-4 py-2 text-right border-l border-slate-200">${qtyStr}</td>
                            <td class="px-4 py-2 text-right border-l border-slate-200 text-slate-500">${fRp(p.nett)}</td>
                            <td class="px-3 py-2 text-center border-l border-slate-200 text-slate-300 text-xs">—</td>
                            <td class="px-4 py-2 text-right border-l border-slate-200 text-slate-300 text-xs">—</td>
                        </tr>`;
                        htmlCards += accNode('', p.nama, fQtyStr(p.qty, p.satuan), fRp(p.nett), 2, n1.id, n1.ancestors, false).html;
                    });
                });
            } else {
                data.outlet.forEach(ps=>{
                    if (!colorMap[ps.nama]) { colorMap[ps.nama]=palette[cIdx%palette.length]; cIdx++; }
                    if (psFilter !== ps.nama) return;
                    totalQty+=ps.total_qty; totalNett+=ps.total_nett;
                    // sum Add. Closing Sales for ps header (cukup 1 kolom)
                    let psAddSales=0; ps.customer.forEach(c=>{ psAddSales+=parseFloat(c.add_closing_sales ?? c.closing_count ?? c.add_closing ?? c.closing_rate ?? 0); });
                    totalAdd+=psAddSales; totalAddSales+=psAddSales; totalAkhir+=ps.total_nett+psAddSales;
                    htmlClosing += `<tr class="row-level-1 bg-slate-50 font-bold">
                        <td class="px-4 py-3"><div class="flex items-start"><i class="fas fa-users mr-2 mt-1 text-blue-500 w-4"></i><span>${ps.nama}</span></div></td>
                        <td class=\"px-4 py-3 text-right border-l border-slate-200\">${fNum(ps.total_qty)}</td>
                        <td class=\"px-4 py-3 text-right border-l border-slate-200\">${fRp(ps.total_nett)}</td>
                        <td class="px-3 py-3 text-center border-l border-slate-200 text-purple-700 bg-purple-50/60">${psAddSales? fRp(psAddSales):'-'}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-200 text-emerald-700 bg-emerald-50/40">${fRp(ps.total_nett+psAddSales)}</td>
                    </tr>`;
                    let n1 = accNode('fas fa-users', ps.nama, fQtyStr(ps.total_qty), `Total: ${fRp(ps.total_nett+psAddSales)}`, 1, null, [], ps.customer.length>0);
                    htmlCards += n1.html;
                    ps.customer.forEach(c=>{
                        const {salesCell, totalCell, total, addSalesVal} = buildRow(ps.nama, c, 2);
                        totalQty+=0; // already counted via ps
                        closingLabels.push(`${c.nama}`); closingSales.push(c.nett); closingAdd.push(parseFloat(addSalesVal)||0); closingBg.push(colorMap[ps.nama]);
                        htmlClosing += `<tr class="row-level-2 hover:bg-slate-50">
                            <td class="py-2 pr-4 pl-6 md:pl-10"><div class="flex items-start"><i class="fas fa-store mr-3 mt-1 text-amber-500 w-4"></i><span class="font-bold text-slate-700">${c.nama}</span></div></td>
                            <td class=\"px-4 py-2 text-right border-l border-slate-200 font-semibold\">${fNum(c.total_qty)}</td>
                            <td class=\"px-4 py-2 text-right border-l border-slate-200 font-semibold text-slate-600\">${fRp(c.nett)}</td>
                            ${salesCell}
                            ${totalCell}
                        </tr>`;
                        let n2 = accNode('fas fa-store', c.nama, fQtyStr(c.total_qty), `Total: ${fRp(total)}`, 2, n1.id, n1.ancestors, c.produk.length>0, null, addSalesVal);
                        htmlCards += n2.html;
                        c.produk.forEach(p=>{
                            let qtyStr = p.satuan? `${fNum(p.qty)} <span class="text-xs text-slate-400 font-semibold ml-1">${p.satuan}</span>` : fNum(p.qty);
                            htmlClosing += `<tr class="row-level-3">
                                <td class="py-2 pr-4 pl-8 md:pl-12 whitespace-normal"><span class="text-slate-500">${p.nama}</span></td>
                                <td class=\"px-4 py-2 text-right border-l border-slate-200\">${qtyStr}</td>
                            <td class=\"px-4 py-2 text-right border-l border-slate-200 text-slate-500\">${fRp(p.nett)}</td>
                                <td class="px-3 py-2 text-center border-l border-slate-200 text-slate-300">—</td>
                                <td class="px-4 py-2 text-right border-l border-slate-200 text-slate-300">—</td>
                            </tr>`;
                            htmlCards += accNode('', p.nama, fQtyStr(p.qty, p.satuan), fRp(p.nett), 3, n2.id, n2.ancestors, false).html;
                        });
                    });
                    // de-duplicate totals already counted
                });
                // When specific ps, we already added ps totals; need adjust closingLabels already
            }

            if (htmlClosing) {
                htmlClosing += `<tr class="bg-blue-50 font-bold border-t-2 border-blue-200">
                    <td class="px-4 py-3">GRAND TOTAL</td>
                    <td class=\"px-4 py-3 text-right border-l border-slate-200\">${fNum(totalQty)}</td>
                    <td class=\"px-4 py-3 text-right border-l border-slate-200\">${fRp(totalNett)}</td>
                    <td class="px-3 py-3 text-center border-l border-slate-200 text-amber-700">${fRp(totalAddSales)}</td>
                    <td class="px-4 py-3 text-right border-l border-slate-200 text-emerald-700">${fRp(totalAkhir)}</td>
                </tr>`;
            }
            document.getElementById('m-tbody-closing').innerHTML = htmlClosing || '<tr><td colspan="5" class="text-center p-6 text-slate-500">No data</td></tr>';
            document.getElementById('m-cards-closing').innerHTML = htmlCards ? htmlCards + accNode('fas fa-flag-checkered','GRAND TOTAL', fQtyStr(totalQty), `Akhir: ${fRp(totalAkhir)}`,1,null,[],false).html : mcEmpty('No data');
            document.getElementById('m-search-closing').value='';

            const setText = (id,val)=>{ const el=document.getElementById(id); if(el) el.innerText=val; };
            setText('grandTotalClosing', fRp(totalNett));
            setText('grandAddClosing', fRp(totalAdd));
            setText('grandTotalAkhir', fRp(totalAkhir));
            setText('grandTotalClosingMobile', fRp(totalNett));
            setText('grandAddClosingMobile', fRp(totalAdd));
            setText('grandTotalAkhirMobile', fRp(totalAkhir));

            const ctx = document.getElementById('chartClosing');
            if (ctx) {
                const ctn = document.getElementById('closingChartContainer');
                ctn.style.height = Math.max(320, closingLabels.length*42+80)+'px';
                closingChartInstance = new Chart(ctx.getContext('2d'), {
                    type:'bar',
                    data:{ labels: closingLabels, datasets:[
                        {label:'Sales', data:closingSales, backgroundColor:closingBg, borderRadius:4, maxBarThickness:36},
                        {label:'Add. Closing', data:closingAdd, backgroundColor:'#f59e0b', borderRadius:4, maxBarThickness:36}
                    ]},
                    options:{
                        indexAxis:'y', responsive:true, maintainAspectRatio:false,
                        plugins:{ datalabels:{anchor:'end',align:'end',color:'#475569',font:{size:10,weight:'bold'},formatter:v=> v? new Intl.NumberFormat('id-ID').format(v):''}, legend:{display:true,position:'top'}, tooltip:{callbacks:{label:ctx=> ctx.dataset.label+': '+fRp(ctx.raw)}}},
                        scales:{
                            x:{
                                min:0,
                                suggestedMax: Math.max(0, ...closingSales, ...closingAdd) + 15000000,
                                stacked:false,
                                ticks:{ stepSize:10000000, callback:v=>'Rp '+new Intl.NumberFormat('id-ID',{notation:"compact",compactDisplay:"short"}).format(v), font:{size:10}, maxTicksLimit:7 },
                                grid:{ color:'rgba(148,163,184,0.12)' },
                                border:{ display:false }
                            },
                            y:{
                                ticks:{
                                    autoSkip:false,
                                    font:{size:11, weight:'600'},
                                    padding:14,
                                    color:'#1e293b',
                                    maxWidth: 220,
                                    callback:function(value){ const label=this.getLabelForValue(value); if(String(label).length>42) return String(label).substring(0,42)+'…'; return label; }
                                },
                                grid:{ display:false },
                                border:{ display:false }
                            }
                        },
                        layout:{ padding:{ left:18, right:18 } }
                    }
                });
            }
        }

        function loadMonthlyDetail(bulan, btnEl) {
            currentSelectedBulan = bulan;
            document.querySelectorAll('.monthly-card').forEach(c => {
                c.classList.remove('active');
            });
            
            // Highlight list bulan jika btnEl dikirim (dari klik user)
            if (btnEl) {
                btnEl.classList.add('active');
                if(window.innerWidth < 768 && btnEl.scrollIntoView) {
                    btnEl.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                }
            } else {
                // Highlight berdasarkan nama bulan bila dipanggil manual
                document.querySelectorAll('.monthly-card').forEach(c => {
                    if (c.innerText.trim().toUpperCase() === bulan.toUpperCase()) {
                        c.classList.add('active');
                    }
                });
            }
            
            const labelEl = document.getElementById('selectedMonthLabel');
            if(labelEl) labelEl.innerText = bulan;
            
            const container = document.getElementById('monthGridContainer');
            if(container) container.classList.add('hidden');
            
            const iconMobile = document.getElementById('monthGridToggleIcon');
            const iconDesktop = document.getElementById('monthGridToggleIconDesktop');
            if (iconMobile) iconMobile.classList.add('rotate-180');
            if (iconDesktop) iconDesktop.classList.remove('rotate-180');
            
            document.getElementById('monthly-loader').classList.remove('hidden');

            let tahun = "{{ $tahun }}";
            fetch(`{{ route('sales.monthly.detail') }}?tahun=${tahun}&bulan=${bulan}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('monthly-loader').classList.add('hidden');
                    if (data.error) { alert(data.error); return; }
                    
                    let defaultOption = !hasFullAccess ? '<option value="Sales Team">Sales Team</option>' : '<option value="all">All</option><option value="Sales Team">Sales Team</option>';
                    
                    let psProductSelect = document.getElementById('filterProductPs');
                    psProductSelect.innerHTML = defaultOption;
                    data.product.forEach(ps => {
                        psProductSelect.innerHTML += `<option value="${ps.nama}">${ps.nama}</option>`;
                    });
                    
                    let psOutletSelect = document.getElementById('filterOutletPs');
                    let psPduSelect = document.getElementById('filterPduPs');
                    let psClosingSelect = document.getElementById('filterClosingPs');
                    psOutletSelect.innerHTML = defaultOption;
                    psPduSelect.innerHTML = defaultOption;
                    if (psClosingSelect) psClosingSelect.innerHTML = defaultOption;
                    
                    data.outlet.forEach(ps => {
                        psOutletSelect.innerHTML += `<option value="${ps.nama}">${ps.nama}</option>`;
                        if (psClosingSelect) psClosingSelect.innerHTML += `<option value="${ps.nama}">${ps.nama}</option>`;
                    });
                    data.pdu.forEach(ps => {
                        psPduSelect.innerHTML += `<option value="${ps.nama}">${ps.nama}</option>`;
                    });

                    Chart.register(ChartDataLabels);

                    currentMonthlyData = data;
                    drawPduView();
                    drawOutletChart();
                    drawProductTable();
                    drawClosingView();

                }).catch(err => {
                    console.error("Failed to load monthly details", err);
                    document.getElementById('monthly-loader').classList.add('hidden');
                    alert('Failed to load monthly data.');
                });
        }
        
        function drawProductTable() {
            if (!currentMonthlyData) return;
            const data = currentMonthlyData;
            const psFilter = document.getElementById('filterProductPs').value;
            let htmlProd = '';
            let htmlCards = '';
            let totalQtyProd = 0;
            let totalNettProd = 0;
            let n1; 

            if (psFilter === 'all' || psFilter === 'Sales Team') {
                let aggProd = {};
                data.product.forEach(ps => {
                    if (psFilter === 'Sales Team' && ps.nama.toLowerCase() === 'office') return;
                    totalQtyProd += ps.total_qty;
                    totalNettProd += ps.total_nett;
                    ps.produk.forEach(p => {
                        if (!aggProd[p.nama]) aggProd[p.nama] = { nama: p.nama, qty: 0, nett: 0, satuan: p.satuan || '' };
                        aggProd[p.nama].qty += p.qty;
                        aggProd[p.nama].nett += p.nett;
                    });
                });
                Object.values(aggProd).sort((a,b)=>b.nett-a.nett).forEach((p, idx1) => {
                    let tableQtyStr = p.satuan ? `${fNum(p.qty)} <span class="text-xs text-slate-400 font-semibold ml-1">${p.satuan}</span>` : fNum(p.qty);
                    htmlProd += `
                        <tr class="row-level-1 hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3"><div class="flex items-start"><span class="w-4 mr-3 inline-block"></span><span class="font-bold text-slate-700">${p.nama}</span></div></td>
                            <td class="px-4 py-3 text-right border-l border-slate-200">${tableQtyStr}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 border-l border-slate-200">${fRp(p.nett)}</td>
                        </tr>
                    `;
                    htmlCards += accNode('fas fa-box-open', p.nama, fQtyStr(p.qty, p.satuan), fRp(p.nett), 1, null, [], false).html;
                });
            } else {
                data.product.forEach(ps => {
                    if (psFilter !== ps.nama) return;
                    totalQtyProd += ps.total_qty;
                    totalNettProd += ps.total_nett;
                    htmlProd += `
                        <tr class="row-level-1 hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3"><div class="flex items-start"><i class="fas fa-users mr-2 mt-1 text-blue-500 w-4 text-center"></i><span class="font-bold text-slate-700">${ps.nama}</span></div></td>
                            <td class="px-4 py-3 text-right border-l border-slate-200">${fNum(ps.total_qty)}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 border-l border-slate-200">${fRp(ps.total_nett)}</td>
                        </tr>
                    `;
                    htmlCards += (n1 = accNode('fas fa-users', ps.nama, fQtyStr(ps.total_qty), fRp(ps.total_nett), 1, null, [], ps.produk && ps.produk.length > 0)).html;
                    ps.produk.forEach(p => {
                        let tableQtyStr = p.satuan ? `${fNum(p.qty)} <span class="text-xs text-slate-400 font-semibold ml-1">${p.satuan}</span>` : fNum(p.qty);
                        htmlProd += `
                            <tr class="row-level-2 hover:bg-slate-50 transition-colors">
                                <td class="py-2 pr-4 pl-6 md:pl-10"><div class="flex items-start"><span class="w-4 mr-3 inline-block"></span><span>${p.nama}</span></div></td>
                                <td class="px-4 py-2 text-right border-l border-slate-200">${tableQtyStr}</td>
                                <td class="px-4 py-2 text-right border-l border-slate-200">${fRp(p.nett)}</td>
                            </tr>
                        `;
                        htmlCards += accNode('', p.nama, fQtyStr(p.qty, p.satuan), fRp(p.nett), 2, n1.id, n1.ancestors, false).html;
                    });
                });
            }

            if (htmlProd) {
                htmlProd += `
                    <tr class="bg-blue-50 font-bold border-t-2 border-blue-200">
                        <td class="px-4 py-3">GRAND TOTAL</td>
                        <td class="px-4 py-3 text-right border-l border-slate-200">${fNum(totalQtyProd)}</td>
                        <td class="px-4 py-3 text-right text-emerald-700 border-l border-slate-200">${fRp(totalNettProd)}</td>
                    </tr>
                `;
            }
            document.getElementById('m-tbody-product').innerHTML = htmlProd || '<tr><td colspan="3" class="text-center p-6 text-slate-500 font-medium">No data</td></tr>';
            document.getElementById('m-cards-product').innerHTML = (htmlCards ? htmlCards + accNode('fas fa-flag-checkered', 'GRAND TOTAL', fQtyStr(totalQtyProd), fRp(totalNettProd), 1, null, [], false).html : mcEmpty('No data'));
            document.getElementById('m-search-product').value = '';

            let gtProductEl = document.getElementById('grandTotalProduct');
            if (gtProductEl) gtProductEl.innerText = fRp(totalNettProd);
            let gtProductMobileEl = document.getElementById('grandTotalProductMobile');
            if (gtProductMobileEl) gtProductMobileEl.innerText = fRp(totalNettProd);
            let gqProductEl = document.getElementById('grandQtyProduct');
            if (gqProductEl) gqProductEl.innerText = fNum(totalQtyProd);
        }
        
        function drawPduView() {
            if (!currentMonthlyData) return;
            const data = currentMonthlyData;
            const psFilter = document.getElementById('filterPduPs').value;

            let htmlPdu = '';
            let htmlCards = '';
            let totalQtyPdu = 0;
            let totalNettPdu = 0;
            let n1, n2, n3; 
            
            if (psFilter === 'all' || psFilter === 'Sales Team') {
                // General: 1 baris per tanggal (tidak duplikat), customer dipisah per PS via badge
                let aggTgl = {};
                data.pdu.forEach(ps => {
                    if (psFilter === 'Sales Team' && ps.nama.toLowerCase() === 'office') return;
                    totalQtyPdu += ps.total_qty;
                    totalNettPdu += ps.total_nett;
                    ps.tanggal.forEach(tgl => {
                        if (!aggTgl[tgl.nama]) aggTgl[tgl.nama] = { nama: tgl.nama, total_qty: 0, total_nett: 0, customer: {} };
                        aggTgl[tgl.nama].total_qty += tgl.total_qty;
                        aggTgl[tgl.nama].total_nett += tgl.total_nett;
                        tgl.customer.forEach(cust => {
                            const key = ps.nama + '|' + cust.nama;
                            if (!aggTgl[tgl.nama].customer[key]) aggTgl[tgl.nama].customer[key] = { nama: cust.nama, ps: ps.nama, total_qty: 0, total_nett: 0, produk: {} };
                            aggTgl[tgl.nama].customer[key].total_qty += cust.total_qty;
                            aggTgl[tgl.nama].customer[key].total_nett += cust.total_nett;
                            cust.produk.forEach(prod => {
                                if (!aggTgl[tgl.nama].customer[key].produk[prod.nama]) aggTgl[tgl.nama].customer[key].produk[prod.nama] = { nama: prod.nama, qty: 0, nett: 0, satuan: prod.satuan || '' };
                                aggTgl[tgl.nama].customer[key].produk[prod.nama].qty += prod.qty;
                                aggTgl[tgl.nama].customer[key].produk[prod.nama].nett += prod.nett;
                            });
                        });
                    });
                });
                let sortedTgl = Object.values(aggTgl).sort((a,b)=>{
                    let pa=a.nama.split('/'), pb=b.nama.split('/');
                    if(pa.length===3 && pb.length===3){
                        let da=new Date(pa[2],pa[1]-1,pa[0]), db=new Date(pb[2],pb[1]-1,pb[0]);
                        return db - da;
                    }
                    return b.nama.localeCompare(a.nama);
                });
                sortedTgl.forEach(tgl => {
                    htmlPdu += `
                        <tr class="row-level-1 hover:bg-slate-50 transition-colors">
                            <td class="py-2 pr-4 pl-4 md:pl-6"><div class="flex items-start"><i class="far fa-calendar-alt mr-3 mt-1 text-blue-500 w-4"></i><span class="font-bold text-slate-700">${tgl.nama}</span></div></td>
                            <td class="px-4 py-2 text-right border-l border-slate-200">${fNum(tgl.total_qty)}</td>
                            <td class="px-4 py-2 text-right text-emerald-600 border-l border-slate-200">${fRp(tgl.total_nett)}</td>
                        </tr>
                    `;
                    htmlCards += (n1 = accNode('far fa-calendar-alt', tgl.nama, fQtyStr(tgl.total_qty), fRp(tgl.total_nett), 1, null, [], Object.keys(tgl.customer).length>0)).html;
                    Object.values(tgl.customer).sort((a,b)=>b.total_nett-a.total_nett).forEach(cust => {
                        htmlPdu += `
                            <tr class="row-level-2 hover:bg-slate-50 transition-colors">
                                <td class="py-2 pr-4 pl-6 md:pl-8"><div class="flex items-start"><i class="fas fa-store mr-3 mt-1 text-amber-500 w-4"></i><span>${cust.nama}</span><span class="ml-2 text-[10px] bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100 text-blue-600">${cust.ps}</span></div></td>
                                <td class="px-4 py-2 text-right border-l border-slate-200">${fNum(cust.total_qty)}</td>
                                <td class="px-4 py-2 text-right text-emerald-600 border-l border-slate-200">${fRp(cust.total_nett)}</td>
                            </tr>
                        `;
                        htmlCards += (n2 = accNode('fas fa-store', `${cust.nama} [${cust.ps}]`, fQtyStr(cust.total_qty), fRp(cust.total_nett), 2, n1.id, n1.ancestors, Object.keys(cust.produk).length>0)).html;
                        Object.values(cust.produk).sort((a,b)=>b.nett-a.nett).forEach(prod => {
                            let tableQtyStr = prod.satuan ? `${fNum(prod.qty)} <span class="text-xs text-slate-400 font-semibold ml-1">${prod.satuan}</span>` : fNum(prod.qty);
                            htmlPdu += `
                                <tr class="row-level-3 hover:bg-slate-50 transition-colors">
                                    <td class="py-2 pr-4 pl-8 md:pl-10 break-words whitespace-normal leading-tight"><div class="flex items-start"><span class="w-4 mr-3 inline-block"></span><span>${prod.nama}</span></div></td>
                                    <td class="px-4 py-2 text-right border-l border-slate-200">${tableQtyStr}</td>
                                    <td class="px-4 py-2 text-right text-emerald-600 border-l border-slate-200">${fRp(prod.nett)}</td>
                                </tr>
                            `;
                            htmlCards += accNode('', prod.nama, fQtyStr(prod.qty, prod.satuan), fRp(prod.nett), 3, n2.id, n2.ancestors, false).html;
                        });
                    });
                });

            } else {
                data.pdu.forEach(ps => {
                    if (psFilter !== ps.nama) return;
                    totalQtyPdu += ps.total_qty;
                    totalNettPdu += ps.total_nett;

                    htmlPdu += `
                        <tr class="row-level-1 hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-[14px]"><i class="fas fa-users mr-2 text-blue-500 w-4"></i><span class="font-bold text-slate-700">${ps.nama}</span></td>
                            <td class="px-4 py-3 text-right border-l border-slate-200">${fNum(ps.total_qty)}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 border-l border-slate-200">${fRp(ps.total_nett)}</td>
                        </tr>
                    `;
                    htmlCards += (n1 = accNode('fas fa-users', ps.nama, fQtyStr(ps.total_qty), fRp(ps.total_nett), 1, null, [], ps.tanggal && ps.tanggal.length > 0)).html;
                    
                    let sortedTgl = [...ps.tanggal].sort((a, b) => {
                        let partsA = a.nama.split('/');
                        let partsB = b.nama.split('/');
                        if (partsA.length === 3 && partsB.length === 3) {
                            let dateA = new Date(partsA[2], partsA[1]-1, partsA[0]);
                            let dateB = new Date(partsB[2], partsB[1]-1, partsB[0]);
                            return dateB - dateA;
                        }
                        return b.nama.localeCompare(a.nama);
                    });

                    sortedTgl.forEach(tgl => {
                        htmlPdu += `
                            <tr class="row-level-2 hover:bg-slate-50 transition-colors">
                                <td class="py-2 pr-4 pl-4 md:pl-6"><div class="flex items-start"><i class="far fa-calendar-alt mr-3 mt-1 text-blue-500 w-4 text-center"></i><span>${tgl.nama}</span></div></td>
                                <td class="px-4 py-2 text-right border-l border-slate-200">${fNum(tgl.total_qty)}</td>
                                <td class="px-4 py-2 text-right text-emerald-600 border-l border-slate-200">${fRp(tgl.total_nett)}</td>
                            </tr>
                        `;
                        htmlCards += (n2 = accNode('far fa-calendar-alt', tgl.nama, fQtyStr(tgl.total_qty), fRp(tgl.total_nett), 2, n1.id, n1.ancestors, tgl.customer && tgl.customer.length > 0)).html;
                        tgl.customer.forEach(cust => {
                            htmlPdu += `
                                <tr class="row-level-3 hover:bg-slate-50 transition-colors">
                                    <td class="py-2 pr-4 pl-4 md:pl-6"><div class="flex items-start"><i class="fas fa-store mr-3 mt-1 text-amber-500 w-4 text-center"></i><span>${cust.nama}</span></div></td>
                                    <td class="px-4 py-2 text-right border-l border-slate-200">${fNum(cust.total_qty)}</td>
                                    <td class="px-4 py-2 text-right text-emerald-600 border-l border-slate-200">${fRp(cust.total_nett)}</td>
                                </tr>
                            `;
                            htmlCards += (n3 = accNode('fas fa-store', cust.nama, fQtyStr(cust.total_qty), fRp(cust.total_nett), 3, n2.id, n2.ancestors, cust.produk && cust.produk.length > 0)).html;
                            cust.produk.forEach(prod => {
                                let tableQtyStr = prod.satuan ? `${fNum(prod.qty)} <span class="text-xs text-slate-400 font-semibold ml-1">${prod.satuan}</span>` : fNum(prod.qty);
                                htmlPdu += `
                                    <tr class="row-level-4 hover:bg-slate-50 transition-colors">
                                        <td class="py-2 pr-4 pl-8 md:pl-10 break-words whitespace-normal leading-tight"><div class="flex items-start"><span class="w-4 mr-3 inline-block"></span><span>${prod.nama}</span></div></td>
                                        <td class="px-4 py-2 text-right border-l border-slate-200">${tableQtyStr}</td>
                                        <td class="px-4 py-2 text-right text-emerald-600 border-l border-slate-200">${fRp(prod.nett)}</td>
                                    </tr>
                                `;
                                htmlCards += accNode('', prod.nama, fQtyStr(prod.qty, prod.satuan), fRp(prod.nett), 4, n3.id, n3.ancestors, false).html;
                            });
                        });
                    });
                });
            }
            
            if (htmlPdu) {
                htmlPdu += `
                    <tr class="bg-blue-50 font-bold border-t-2 border-blue-200">
                        <td class="px-4 py-3 text-[14px]">GRAND TOTAL</td>
                        <td class="px-4 py-3 text-right border-l border-slate-200">${fNum(totalQtyPdu)}</td>
                        <td class="px-4 py-3 text-right text-emerald-700 border-l border-slate-200">${fRp(totalNettPdu)}</td>
                    </tr>
                `;
            }
            document.getElementById('m-tbody-pdu').innerHTML = htmlPdu || '<tr><td colspan="3" class="text-center p-6 text-slate-500 font-medium">No data</td></tr>';
            document.getElementById('m-cards-pdu').innerHTML = (htmlCards ? htmlCards + accNode('fas fa-flag-checkered', 'GRAND TOTAL', fQtyStr(totalQtyPdu), fRp(totalNettPdu), 1, null, [], false).html : mcEmpty('No data'));
            document.getElementById('m-search-pdu').value = '';
            
            let gtPduEl = document.getElementById('grandTotalPdu');
            if (gtPduEl) gtPduEl.innerText = fRp(totalNettPdu);
            let gtPduMobileEl = document.getElementById('grandTotalPduMobile');
            if (gtPduMobileEl) gtPduMobileEl.innerText = fRp(totalNettPdu);
            let gqPduEl = document.getElementById('grandQtyPdu');
            if (gqPduEl) gqPduEl.innerText = fNum(totalQtyPdu);

            if (pduChartInstance) pduChartInstance.destroy();
            
            let filteredPdu = data.pdu.filter(p => {
                if (psFilter === 'all') return true;
                if (psFilter === 'Sales Team') return p.nama.toLowerCase() !== 'office';
                return p.nama === psFilter;
            });
            
            let pduLabels = filteredPdu.map(p => p.nama);
            let pduTarget = filteredPdu.map(p => p.target_amount);
            let pduSales = filteredPdu.map(p => p.total_nett);
            let pduPerc = filteredPdu.map(p => p.target_amount > 0 ? ((p.total_nett / p.target_amount) * 100).toFixed(1) : null);
            let pduGrowth = filteredPdu.map(p => p.growth_rate !== undefined ? p.growth_rate : 0);
            let pduAvgYtd = filteredPdu.map(p => p.avg_ytd !== undefined ? p.avg_ytd : 0);

            const ctxPdu = document.getElementById('chartPdu').getContext('2d');
            pduChartInstance = new Chart(ctxPdu, {
                type: 'bar',
                data: {
                    labels: pduLabels,
                    datasets: [
                        {
                            type: 'line',
                            label: 'Achievement %',
                            data: pduPerc,
                            borderColor: '#ef4444',
                            borderWidth: 3,
                            pointBackgroundColor: '#ef4444',
                            pointBorderColor: '#fff',
                            pointRadius: 5,
                            fill: false,
                            spanGaps: true,
                            yAxisID: 'y1'
                        },
                        {
                            type: 'line',
                            label: 'Growth MoM %',
                            data: pduGrowth,
                            borderColor: '#10b981',
                            borderWidth: 3,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointRadius: 5,
                            fill: false,
                            spanGaps: true,
                            yAxisID: 'y1'
                        },
                        {
                            type: 'line',
                            label: 'Avg YTD',
                            data: pduAvgYtd,
                            borderColor: '#8b5cf6',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointBackgroundColor: '#8b5cf6',
                            pointBorderColor: '#fff',
                            pointRadius: 4,
                            fill: false,
                            yAxisID: 'y'
                        },
                        {
                            type: 'bar',
                            label: 'Target',
                            data: pduTarget,
                            backgroundColor: '#3b82f6',
                            barPercentage: 0.6,
                            categoryPercentage: 0.8,
                            yAxisID: 'y'
                        },
                        {
                            type: 'bar',
                            label: 'Sales',
                            data: pduSales,
                            backgroundColor: '#f97316',
                            barPercentage: 0.6,
                            categoryPercentage: 0.8,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    events: ['mousemove', 'mouseout', 'click', 'touchstart'],
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: function(context) {
                                if (context.dataset.label === 'Achievement %') return 'bottom';
                                if (context.dataset.label === 'Growth MoM %') return 'top';
                                return 'top';
                            },
                            offset: function(context) {
                                if (context.dataset.type === 'line') return 8;
                                return 4;
                            },
                            color: function(context) {
                                if (context.dataset.label === 'Achievement %') return '#ef4444';
                                if (context.dataset.label === 'Growth MoM %') return '#059669';
                                return '#64748b';
                            },
                            backgroundColor: function(context) {
                                return context.dataset.type === 'line' ? 'rgba(255, 255, 255, 0.9)' : 'transparent';
                            },
                            borderRadius: 4,
                            padding: 2,
                            font: { size: 9, weight: 'bold' },
                            formatter: function(value, context) {
                                if (context.dataset.label === 'Avg YTD') return '';
                                if (context.dataset.label === 'Achievement %' || context.dataset.label === 'Growth MoM %') {
                                    return (value > 0 || value < 0) ? value + '%' : '0%';
                                }
                                if (value === null || value === 0) return '';
                                return new Intl.NumberFormat('id-ID', { notation: "compact", maximumFractionDigits: 1 }).format(value);
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.dataset.label === 'Achievement %') {
                                        let perc = pduPerc[context.dataIndex];
                                        label += (perc > 0 ? perc : 0) + '%';
                                    } else if (context.dataset.label === 'Growth MoM %') {
                                        label += context.raw + '%';
                                    } else {
                                        label += fRp(context.raw);
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            suggestedMax: Math.max(0, ...pduSales, ...pduTarget) + 15000000,
                            ticks: {
                                callback: function(value) { return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact" , compactDisplay: "short" }).format(value); }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: {
                                callback: function(value) { return value + '%'; }
                            }
                        }
                    }
                }
            });
        }
    </script>
    @endpush
</x-layout-users>