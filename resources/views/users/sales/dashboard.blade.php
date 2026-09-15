@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Sales Command Center' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @push('styles')
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .mesh-bg { 
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.6) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
            pointer-events: none;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            padding: 28px;
            transform: translate3d(0, 0, 0);
        }

        .module-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 1);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            text-decoration: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            z-index: 1;
            transform: translate3d(0, 0, 0);
            backface-visibility: hidden;
        }

        .card-content {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            width: 100%;
        }

        /* Desktop Layout (Default Besar) */
        @media (min-width: 768px) {
            .glass-card, .module-card {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }
            .module-card {
                flex-direction: column;
                justify-content: space-between;
                min-height: 340px;
                padding: 40px;
                border-radius: 24px;
            }
            .module-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
                background: rgba(255, 255, 255, 0.95);
            }
            /* FIX: Memaksa tinggi judul minimal 2 baris (64px) agar teks deskripsi merata */
            .card-content h2 {
                min-height: 4rem;
            }
        }

        /* Mobile Layout (Persegi Panjang yang Compact) */
        @media (max-width: 767px) {
            .module-card {
                flex-direction: row;
                align-items: center;
                gap: 16px;
                padding: 16px 20px;
                min-height: auto;
                height: auto;
                border-radius: 20px;
            }
            .module-card:active {
                transform: scale(0.98);
                background-color: #f8fafc;
            }
            .icon-box {
                width: 52px !important;
                height: 52px !important;
                font-size: 1.25rem !important;
                margin-bottom: 0 !important;
                border-radius: 14px !important;
                flex-shrink: 0;
            }
            .card-action, .bg-decoration {
                display: none !important;
            }
            .mobile-chevron {
                display: flex !important;
                align-items: center;
                justify-content: center;
                color: #cbd5e1;
                font-size: 1rem;
            }
        }

        /* Hover Colors Config */
        .card-green:hover { border-color: rgba(16, 185, 129, 0.3); }
        .card-blue:hover { border-color: rgba(59, 130, 246, 0.3); }
        .card-purple:hover { border-color: rgba(168, 85, 247, 0.3); }
        .card-orange:hover { border-color: rgba(249, 115, 22, 0.3); }
        .card-brown:hover { border-color: rgba(124, 45, 18, 0.3); }
        .card-slate:hover { border-color: rgba(71, 85, 105, 0.3); }
        .card-sky:hover { border-color: rgba(14, 165, 233, 0.3); }

        /* Icon Container */
        .icon-box {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 24px;
            position: relative;
            z-index: 2;
            transition: all 0.4s ease;
        }

        .card-green .icon-box { background: #ecfdf5; color: #10b981; border: 1px solid #d1fae5; }
        .card-green:hover .icon-box { background: #10b981; color: white; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2); }

        .card-blue .icon-box { background: #eff6ff; color: #3b82f6; border: 1px solid #dbeafe; }
        .card-blue:hover .icon-box { background: #3b82f6; color: white; box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2); }

        .card-purple .icon-box { background: #faf5ff; color: #a855f7; border: 1px solid #f3e8ff; }
        .card-purple:hover .icon-box { background: #a855f7; color: white; box-shadow: 0 10px 20px rgba(168, 85, 247, 0.2); }

        .card-orange .icon-box { background: #fff7ed; color: #f97316; border: 1px solid #ffedd5; }
        .card-orange:hover .icon-box { background: #f97316; color: white; box-shadow: 0 10px 20px rgba(249, 115, 22, 0.2); }

        .card-brown .icon-box { background: #fbf0ea; color: #7c2d12; border: 1px solid #f3d9ca; }
        .card-brown:hover .icon-box { background: #7c2d12; color: white; box-shadow: 0 10px 20px rgba(124, 45, 18, 0.2); }
        .card-brown:hover h2 { color: #7c2d12 !important; }

        .card-slate .icon-box { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
        .card-slate:hover .icon-box { background: #475569; color: white; box-shadow: 0 10px 20px rgba(71, 85, 105, 0.2); }
        .card-slate:hover h2 { color: #475569 !important; }

        .card-sky .icon-box { background: #f0f9ff; color: #0ea5e9; border: 1px solid #e0f2fe; }
        .card-sky:hover .icon-box { background: #0ea5e9; color: white; box-shadow: 0 10px 20px rgba(14, 165, 233, 0.2); }
        .card-sky:hover h2 { color: #0ea5e9 !important; }
        
        .mobile-chevron { display: none; }

        /* Animated Title */
        .title-reveal {
            animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        @keyframes slideUpFade {
            to { opacity: 1; transform: translateY(0); }
        }

        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16">
        
        <div class="mesh-bg"></div>
        
        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:px-12 lg:pb-8 flex-1 flex flex-col gap-4 md:gap-6 justify-center mt-2 md:mt-6">

            {{-- Bagian Header --}}
            <div class="text-center w-full title-reveal glass-card shadow-sm px-4 md:px-8 py-4 md:py-5">
                <h1 class="text-2xl md:text-4xl font-black tracking-tight text-slate-800 mb-1 leading-tight">
                    Sales <span class="text-blue-600">Command Center</span>
                </h1>
                <p class="text-slate-500 text-xs md:text-lg font-medium leading-relaxed max-w-none mx-auto">
                    Centralized access to manage, analyze, and comprehensively monitor company sales performance.
                </p>
            </div>

            {{-- Kartu Utama --}}
            @if(isset($hasAnyAccess) && $hasAnyAccess)
            {{-- Mengatur lg:grid-cols berdasarkan akses --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 {{ (isset($hasFullAccess) && $hasFullAccess) ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-2 md:gap-6 relative z-10">
                
                @if(isset($hasFullAccess) && $hasFullAccess)
                {{-- Kartu 1: Data Management --}}
                <a href="{{ route('sales.manage') }}" class="module-card card-green group title-reveal stagger-1">
                    <div class="icon-box">
                        <i class="fas fa-database"></i>
                    </div>
                    
                    <div class="card-content">
                        <h2 class="text-lg md:text-2xl font-bold text-slate-800 mb-0.5 md:mb-3 tracking-tight group-hover:text-emerald-600 transition-colors">Data Management</h2>
                        <p class="text-slate-500 text-[11px] md:text-base leading-snug md:leading-relaxed font-medium line-clamp-2 md:line-clamp-none">
                            Data control center. Input new sales data, upload batch files, or adjust existing data history.
                        </p>
                        
                        <div class="card-action mt-auto pt-8 flex items-center gap-2 text-sm font-bold text-emerald-600 opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                            <span>Manage Data</span>
                            <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>
                    
                    <div class="bg-decoration absolute -bottom-6 -right-6 text-emerald-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-server"></i>
                    </div>
                </a>
                
                {{-- Kartu 2: Analytics & Insights --}}
                <a href="{{ route('sales.analytics') }}" class="module-card card-blue group title-reveal stagger-2">
                    <div class="icon-box">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    
                    <div class="card-content">
                        <h2 class="text-lg md:text-2xl font-bold text-slate-800 mb-0.5 md:mb-3 tracking-tight group-hover:text-blue-600 transition-colors">Analytics & Insights</h2>
                        <p class="text-slate-500 text-[11px] md:text-base leading-snug md:leading-relaxed font-medium line-clamp-2 md:line-clamp-none">
                            Monitor performance. Access interactive dashboards, evaluate target achievements, and identify sales trends.
                        </p>
                        
                        <div class="card-action mt-auto pt-8 flex items-center gap-2 text-sm font-bold text-blue-600 opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                            <span>Open Analytics</span>
                            <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-blue-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </a>
                @endif

                {{-- Kartu 3: Monthly Monitoring --}}
                <a href="{{ route('sales.monthly') }}" class="module-card card-purple group title-reveal stagger-3">
                    <div class="icon-box">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    
                    <div class="card-content">
                        <h2 class="text-lg md:text-2xl font-bold text-slate-800 mb-0.5 md:mb-3 tracking-tight group-hover:text-purple-600 transition-colors">Monthly Monitoring</h2>
                        <p class="text-slate-500 text-[11px] md:text-base leading-snug md:leading-relaxed font-medium line-clamp-2 md:line-clamp-none">
                            Detailed monthly sales performance monitoring. Access drill-down data to product and outlet levels.
                        </p>
                        
                        <div class="card-action mt-auto pt-8 flex items-center gap-2 text-sm font-bold text-purple-600 opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                            <span>Open Monitoring</span>
                            <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-purple-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </a>

                {{-- Kartu 5: Incentive Scheme --}}
                <a href="{{ route('sales.incentive') }}" class="module-card card-brown group title-reveal stagger-2" style="animation-delay: 0.5s;">
                    <div class="icon-box">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                    
                    <div class="card-content">
                        <h2 class="text-lg md:text-2xl font-bold text-slate-800 mb-0.5 md:mb-3 tracking-tight transition-colors">Incentive Scheme</h2>
                        <p class="text-slate-500 text-[11px] md:text-base leading-snug md:leading-relaxed font-medium line-clamp-2 md:line-clamp-none">
                            Sales incentive schemes and calculations. View target achievements, incentive percentages, and payout simulations.
                        </p>
                        
                        <div class="card-action mt-auto pt-8 flex items-center gap-2 text-sm font-bold opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300" style="color: #7c2d12;">
                            <span>Open Incentive Scheme</span>
                            <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]" style="color: #f3d9ca;">
                        <i class="fas fa-coins"></i>
                    </div>
                </a>

                {{-- Kartu 4: Stock Monitoring --}}
                <a href="{{ route('sales.stock') }}" class="module-card card-orange group title-reveal stagger-1" style="animation-delay: 0.4s;">
                    <div class="icon-box">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    
                    <div class="card-content">
                        <h2 class="text-lg md:text-2xl font-bold text-slate-800 mb-0.5 md:mb-3 tracking-tight group-hover:text-orange-600 transition-colors">Stock Monitoring</h2>
                        <p class="text-slate-500 text-[11px] md:text-base leading-snug md:leading-relaxed font-medium line-clamp-2 md:line-clamp-none">
                            Monitor item availability. Access structured inventory data, safe or low stock status.
                        </p>
                        
                        <div class="card-action mt-auto pt-8 flex items-center gap-2 text-sm font-bold text-orange-600 opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                            <span>Open Monitoring</span>
                            <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-orange-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-box-open"></i>
                    </div>
                </a>


                {{-- Kartu 6: Sales Forecast --}}
                <a href="{{ route('sales.forecast') }}" class="module-card card-slate group title-reveal stagger-3" style="animation-delay: 0.6s;">
                    <div class="icon-box">
                        <i class="fas fa-chart-area"></i>
                    </div>
                    
                    <div class="card-content">
                        <h2 class="text-lg md:text-2xl font-bold text-slate-800 mb-0.5 md:mb-3 tracking-tight transition-colors">Sales <br>Forecast</h2>
                        <p class="text-slate-500 text-[11px] md:text-base leading-snug md:leading-relaxed font-medium line-clamp-2 md:line-clamp-none">
                            Estimate and predict product stock requirements based on average sales movement over the last 3 months.
                        </p>
                        
                        <div class="card-action mt-auto pt-8 flex items-center gap-2 text-sm font-bold text-slate-600 opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                            <span>Open Forecast</span>
                            <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-slate-200 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </div>
                </a>

                {{-- Kartu Baru: Product Price & SPH (Sky Blue / Biru Awan) --}}
                <a href="{{ route('sales.pricing') }}" class="module-card card-sky group title-reveal stagger-1" style="animation-delay: 0.7s;">
                    <div class="icon-box">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    
                    <div class="card-content">
                        <h2 class="text-lg md:text-2xl font-bold text-slate-800 mb-0.5 md:mb-3 tracking-tight transition-colors">Product Price & SPH</h2>
                        <p class="text-slate-500 text-[11px] md:text-base leading-snug md:leading-relaxed font-medium line-clamp-2 md:line-clamp-none">
                            Manage official product pricing lists and generate professional Sales Price Quotation (SPH) documents seamlessly.
                        </p>
                        
                        <div class="card-action mt-auto pt-8 flex items-center gap-2 text-sm font-bold text-sky-500 opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                            <span>Open Price & SPH</span>
                            <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-sky-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-tags"></i>
                    </div>
                </a>

            </div>
            @else
            <div class="glass-card text-center w-full title-reveal py-10 md:py-12 flex flex-col items-center justify-center mx-4 md:mx-0">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mb-4 md:mb-6 border border-rose-100">
                    <i class="fas fa-lock text-2xl md:text-3xl"></i>
                </div>
                <h2 class="text-xl md:text-2xl font-black text-slate-800 mb-2 md:mb-3 tracking-tight">Access Restricted</h2>
                <p class="text-slate-500 font-medium text-xs md:text-base max-w-2xl mx-auto leading-relaxed">
                    Sorry, you do not have permission to view the Sales Command Center. This feature is only available for Marketing & Operations Division and Top Management members.
                </p>
            </div>
            @endif
            
        </div>
    </div>
</x-layout-users>