<x-layout-users title="{{ $title ?? 'Warehouse Command Center' }}">
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
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            padding: 28px;
            transform: translate3d(0, 0, 0);
        }
        .header-card {
            display: flex;
            flex-direction: column;
            gap: 2px;
            text-align: center;
            align-items: center;
            justify-content: center;
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

        /* Desktop Layout */
        @media (min-width: 768px) {
            .glass-card, .module-card {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }
            .module-card {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
                justify-content: flex-start;
                min-height: 290px;
                padding: 28px 28px 24px;
                border-radius: 20px;
                gap: 20px;
            }
            .module-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
                background: rgba(255, 255, 255, 0.95);
            }
            .icon-box {
                margin-top: 6px;
            }
            .card-content {
                gap: 10px;
                align-items: flex-start;
                text-align: left;
                flex-grow: 1;
                justify-content: flex-start;
                width: 100%;
                padding-top: 14px;
            }
            .card-content h2 {
                line-height: 1.25;
                margin-bottom: 0;
                text-align: left;
                width: 100%;
            }
            .card-content p {
                line-height: 1.6;
                font-size: 15px;
                margin-top: 0;
                text-align: left;
                max-width: 100%;
            }
            .card-action {
                margin-top: auto;
                padding-top: 18px !important;
                border-top: 1px solid rgba(226, 232, 240, 0.8);
                width: 100%;
                justify-content: flex-start;
            }
        }

        /* Mobile Layout */
        @media (max-width: 767px) {
            .module-card {
                flex-direction: row;
                align-items: center;
                gap: 16px;
                padding: 20px 18px;
                min-height: auto;
                height: auto;
                border-radius: 18px;
            }
            .card-content {
                gap: 6px;
            }
            .card-content h2 {
                line-height: 1.3;
            }
            .card-content p {
                line-height: 1.5;
                margin-top: 2px;
                font-size: 13px !important;
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

        /* Icon Container */
        .icon-box {
            width: 62px;
            height: 62px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.55rem;
            margin-bottom: 0;
            position: relative;
            z-index: 2;
            transition: all 0.4s ease;
            flex-shrink: 0;
        }

        /* Warehouse palette (selaras Sales — biru) */
        .card-blue .icon-box { background: #eff6ff; color: #3b82f6; border: 1px solid #dbeafe; }
        .card-blue:hover { border-color: rgba(59, 130, 246, 0.3); }
        .card-blue:hover .icon-box { background: #3b82f6; color: white; box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2); }

        .card-sky .icon-box { background: #f0f9ff; color: #0ea5e9; border: 1px solid #e0f2fe; }
        .card-sky:hover { border-color: rgba(14, 165, 233, 0.3); }
        .card-sky:hover .icon-box { background: #0ea5e9; color: white; box-shadow: 0 10px 20px rgba(14, 165, 233, 0.2); }

        .card-indigo .icon-box { background: #eef2ff; color: #6366f1; border: 1px solid #e0e7ff; }
        .card-indigo:hover { border-color: rgba(99, 102, 241, 0.3); }
        .card-indigo:hover .icon-box { background: #6366f1; color: white; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2); }

        .card-cyan .icon-box { background: #ecfeff; color: #06b6d4; border: 1px solid #a5f3fc; }
        .card-cyan:hover { border-color: rgba(6, 182, 212, 0.3); }
        .card-cyan:hover .icon-box { background: #06b6d4; color: white; box-shadow: 0 10px 20px rgba(6, 182, 212, 0.2); }

        .card-rose .icon-box { background: #fff1f2; color: #f43f5e; border: 1px solid #fecdd3; }
        .card-rose:hover { border-color: rgba(244, 63, 94, 0.3); }
        .card-rose:hover .icon-box { background: #f43f5e; color: white; box-shadow: 0 10px 20px rgba(244, 63, 94, 0.2); }

        .card-lime .icon-box { background: #f7fee7; color: #65a30d; border: 1px solid #d9f99d; }
        .card-lime:hover { border-color: rgba(132, 204, 22, 0.3); }
        .card-lime:hover .icon-box { background: #84cc16; color: white; box-shadow: 0 10px 20px rgba(132, 204, 22, 0.2); }

        .card-violet .icon-box { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
        .card-violet:hover { border-color: rgba(124, 58, 237, 0.3); }
        .card-violet:hover .icon-box { background: #7c3aed; color: white; box-shadow: 0 10px 20px rgba(124, 58, 237, 0.2); }
        .card-orange .icon-box { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
        .card-orange:hover { border-color: rgba(234, 88, 12, 0.3); }
        .card-orange:hover .icon-box { background: #ea580c; color: white; box-shadow: 0 10px 20px rgba(234, 88, 12, 0.2); }

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

            {{-- Header --}}
            <x-ui.glass-card class="w-full title-reveal header-card shadow-sm !p-2.5 md:!py-2.5 md:!px-6" style="padding-top:10px !important; padding-bottom:10px !important;">
                <h1 class="text-2xl md:text-4xl font-black tracking-tight text-slate-800 leading-tight">
                    Warehouse <span class="text-blue-600">Command Center</span>
                </h1>
                <p class="text-slate-500 text-xs md:text-lg font-medium leading-relaxed max-w-none w-full">
                    Pusat kontrol stok fisik, kartu stock, opname, dan faktur — selaras Accurate.
                </p>
            </x-ui.glass-card>

            {{-- Kartu Utama --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-5 relative z-10">

                {{-- Card 1: Data Product --}}
                <a href="{{ route('warehouse.product.index') }}" class="module-card card-blue group title-reveal stagger-1">
                    <div class="icon-box">
                        <i class="fas fa-cubes"></i>
                    </div>

                    <div class="card-content">
                        <h2 class="text-lg md:text-[19px] font-bold text-slate-800 tracking-tight group-hover:text-blue-600 transition-colors leading-tight">Data Product</h2>
                        <p class="text-slate-500 text-[13px] md:text-[15px] leading-relaxed font-medium line-clamp-2">
                            Manage products, packaging rules & clean names.
                        </p>

                        <div class="card-action mt-auto flex items-center gap-2 text-xs md:text-[13px] font-bold text-blue-600 opacity-80 group-hover:opacity-100 transition-all duration-300">
                            <span>Manage Products</span>
                            <i class="fas fa-arrow-right text-[11px] transition-transform group-hover:translate-x-1.5"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-blue-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-box-open"></i>
                    </div>
                </a>

                {{-- Card 2: Warehouse Ops (halaman gabungan) --}}
                <a href="{{ route('warehouse.index') }}" class="module-card card-sky group title-reveal stagger-1" style="animation-delay: 0.1s;">
                    <div class="icon-box">
                        <i class="fas fa-warehouse"></i>
                    </div>

                    <div class="card-content">
                        <h2 class="text-lg md:text-[19px] font-bold text-slate-800 tracking-tight group-hover:text-sky-600 transition-colors leading-tight">Warehouse Ops</h2>
                        <p class="text-slate-500 text-[13px] md:text-[15px] leading-relaxed font-medium line-clamp-2">
                            Daily operations: product data, mutation records, goods in & out.
                        </p>

                        <div class="card-action mt-auto flex items-center gap-2 text-xs md:text-[13px] font-bold text-sky-600 opacity-80 group-hover:opacity-100 transition-all duration-300">
                            <span>Open Operations</span>
                            <i class="fas fa-arrow-right text-[11px] transition-transform group-hover:translate-x-1.5"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-sky-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                </a>

                {{-- Card 3: Stock Forecast --}}
                <a href="{{ route('sales.forecast') }}" class="module-card card-indigo group title-reveal stagger-2" style="animation-delay: 0.2s;">
                    <div class="icon-box">
                        <i class="fas fa-chart-area"></i>
                    </div>

                    <div class="card-content">
                        <h2 class="text-lg md:text-[19px] font-bold text-slate-800 tracking-tight group-hover:text-indigo-600 transition-colors leading-tight">Stock Forecast</h2>
                        <p class="text-slate-500 text-[13px] md:text-[15px] leading-relaxed font-medium line-clamp-2">
                            Estimate stock needs from 3-month average.
                        </p>

                        <div class="card-action mt-auto flex items-center gap-2 text-xs md:text-[13px] font-bold text-indigo-600 opacity-80 group-hover:opacity-100 transition-all duration-300">
                            <span>Open Forecast</span>
                            <i class="fas fa-arrow-right text-[11px] transition-transform group-hover:translate-x-1.5"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-indigo-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </div>
                </a>

                {{-- Card 4: Stock Opname --}}
                <a href="{{ route('warehouse.stockopname.index') }}" class="module-card card-rose group title-reveal stagger-2" style="animation-delay: 0.3s;">
                    <div class="icon-box">
                        <i class="fas fa-clipboard-check"></i>
                    </div>

                    <div class="card-content">
                        <h2 class="text-lg md:text-[19px] font-bold text-slate-800 tracking-tight group-hover:text-rose-600 transition-colors leading-tight">Stock Opname</h2>
                        <p class="text-slate-500 text-[13px] md:text-[15px] leading-relaxed font-medium line-clamp-2">
                            Periodic physical count & rejected items.
                        </p>

                        <div class="card-action mt-auto flex items-center gap-2 text-xs md:text-[13px] font-bold text-rose-600 opacity-80 group-hover:opacity-100 transition-all duration-300">
                            <span>Open Opname</span>
                            <i class="fas fa-arrow-right text-[11px] transition-transform group-hover:translate-x-1.5"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-rose-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                </a>

                {{-- Card 5: Surat Jalan --}}
                <a href="{{ route('warehouse.sj.index') }}" class="module-card card-orange group title-reveal stagger-2" style="animation-delay: 0.4s;">
                    <div class="icon-box">
                        <i class="fas fa-truck"></i>
                    </div>

                    <div class="card-content">
                        <h2 class="text-lg md:text-[19px] font-bold text-slate-800 tracking-tight group-hover:text-orange-600 transition-colors leading-tight">Form SJ</h2>
                        <p class="text-slate-500 text-[13px] md:text-[15px] leading-relaxed font-medium line-clamp-2">
                            Record outgoing goods & delivery note archive.
                        </p>

                        <div class="card-action mt-auto flex items-center gap-2 text-xs md:text-[13px] font-bold text-orange-600 opacity-80 group-hover:opacity-100 transition-all duration-300">
                            <span>Buat SJ</span>
                            <i class="fas fa-arrow-right text-[11px] transition-transform group-hover:translate-x-1.5"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-orange-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-truck"></i>
                    </div>
                </a>

                {{-- Card 6: Form SPB --}}
                <a href="{{ route('pengajuan_barang.index', ['from' => 'dashboard']) }}" class="module-card card-lime group title-reveal stagger-1" style="animation-delay: 0.5s;">
                    <div class="icon-box">
                        <i class="fas fa-file-signature"></i>
                    </div>

                    <div class="card-content">
                        <h2 class="text-lg md:text-[19px] font-bold text-slate-800 tracking-tight group-hover:text-lime-600 transition-colors leading-tight">Form SPB</h2>
                        <p class="text-slate-500 text-[13px] md:text-[15px] leading-relaxed font-medium line-clamp-2">
                            Buat surat permintaan barang (SPB) dengan rekomendasi qty forecast.
                        </p>

                        <div class="card-action mt-auto flex items-center gap-2 text-xs md:text-[13px] font-bold text-lime-600 opacity-80 group-hover:opacity-100 transition-all duration-300">
                            <span>Buat SPB</span>
                            <i class="fas fa-arrow-right text-[11px] transition-transform group-hover:translate-x-1.5"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-lime-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-inbox"></i>
                    </div>
                </a>

            </div>

        </div>
    </div>
</x-layout-users>
