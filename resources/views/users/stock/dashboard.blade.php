<x-layout-users title="{{ $title ?? 'Warehouse Dashboard' }}">
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
            padding: 20px 24px;
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

        /* Desktop Layout (Default Besar & Proporsional) */
        @media (min-width: 768px) {
            .glass-card, .module-card {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }
            .module-card {
                flex-direction: column;
                justify-content: space-between;
                min-height: 300px;
                padding: 28px;
                border-radius: 20px;
            }
            .module-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
                background: rgba(255, 255, 255, 0.95);
            }
            
            /* Kontainer khusus desktop untuk memaksa turun ke bawah sesuai keinginan */
            .desktop-push-down {
                margin-top: 5rem !important; /* Ubah angka 5rem ini jika ingin lebih turun atau naik */
            }
        }

        /* Mobile Layout */
        @media (max-width: 767px) {
            .module-card {
                flex-direction: row;
                align-items: center;
                gap: 14px;
                padding: 14px 18px;
                min-height: auto;
                height: auto;
                border-radius: 16px;
            }
            .module-card:active {
                transform: scale(0.98);
                background-color: #f8fafc;
            }
            .icon-box {
                width: 48px !important;
                height: 48px !important;
                font-size: 1.15rem !important;
                margin-bottom: 0 !important;
                border-radius: 12px !important;
                flex-shrink: 0;
            }
            .card-content {
                display: flex;
                flex-direction: column;
                flex-grow: 1;
            }
            .card-action, .bg-decoration {
                display: none !important;
            }
            .mobile-chevron {
                display: flex !important;
                align-items: center;
                justify-content: center;
                color: #cbd5e1;
                font-size: 0.95rem;
            }
        }

        /* Hover Colors Config */
        .card-orange:hover { border-color: rgba(249, 115, 22, 0.3); }
        .card-green:hover { border-color: rgba(16, 185, 129, 0.3); }

        /* Icon Container */
        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 16px;
            position: relative;
            z-index: 2;
            transition: all 0.4s ease;
        }

        .card-orange .icon-box { background: #fff7ed; color: #f97316; border: 1px solid #ffedd5; }
        .card-orange:hover .icon-box { background: #f97316; color: white; box-shadow: 0 10px 20px rgba(249, 115, 22, 0.2); }

        .card-green .icon-box { background: #ecfdf5; color: #10b981; border: 1px solid #d1fae5; }
        .card-green:hover .icon-box { background: #10b981; color: white; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2); }
        
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
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16">
        
        <div class="mesh-bg"></div>
        
        {{-- Ditambahkan kelas desktop-push-down agar di layar besar bisa diatur turun terlepas dari layout utama --}}
        <div class="relative z-10 w-full max-w-4xl mx-auto p-4 sm:p-6 lg:pb-12 flex-1 flex flex-col gap-4 md:gap-5 justify-start pt-4 desktop-push-down">

            {{-- Bagian Header --}}
            <div class="text-center w-full title-reveal glass-card shadow-sm px-4 md:px-8 py-4 md:py-5">
                <h1 class="text-xl md:text-3xl font-black tracking-tight text-slate-800 mb-1 leading-tight">
                    Warehouse <span class="text-blue-600">Dashboard</span>
                </h1>
                <p class="text-slate-500 text-xs md:text-sm font-medium leading-relaxed max-w-xl mx-auto">
                    Your central hub for physical stock monitoring, inventory synchronization, and predictive forecasting.
                </p>
            </div>

            {{-- Kartu Modul (Grid 2 Kolom) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-5 relative z-10 w-full">
                
                {{-- Kartu 1: Stock Monitoring --}}
                <a href="{{ route('sales.stock') }}" class="module-card card-orange group title-reveal stagger-1">
                    <div class="icon-box">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    
                    <div class="card-content">
                        <h2 class="text-base md:text-xl font-bold text-slate-800 mb-1 tracking-tight group-hover:text-orange-600 transition-colors">Stock Monitoring</h2>
                        <p class="text-slate-500 text-xs md:text-sm leading-relaxed font-medium">
                            Track physical inventory levels, adjust unit quantities, and synchronize data seamlessly with Accurate system reports.
                        </p>
                        
                        <div class="card-action mt-4 pt-3 flex items-center gap-2 text-xs md:text-sm font-bold text-orange-600 opacity-80 group-hover:opacity-100 transform translate-y-1 group-hover:translate-y-0 transition-all duration-300">
                            <span>Manage Stock</span>
                            <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>
                    
                    <div class="bg-decoration absolute -bottom-6 -right-6 text-orange-100 text-8xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-box-open"></i>
                    </div>
                </a>
                
                {{-- Kartu 2: Sales Forecast --}}
                <a href="{{ route('sales.forecast') }}" class="module-card card-green group title-reveal stagger-2">
                    <div class="icon-box">
                        <i class="fas fa-chart-area"></i>
                    </div>
                    
                    <div class="card-content">
                        <h2 class="text-base md:text-xl font-bold text-slate-800 mb-1 tracking-tight group-hover:text-emerald-600 transition-colors">Sales Forecast</h2>
                        <p class="text-slate-500 text-xs md:text-sm leading-relaxed font-medium">
                            Estimate future stock requirements based on the average historical sales performance to prevent inventory shortages.
                        </p>
                        
                        <div class="card-action mt-4 pt-3 flex items-center gap-2 text-xs md:text-sm font-bold text-emerald-600 opacity-80 group-hover:opacity-100 transform translate-y-1 group-hover:translate-y-0 transition-all duration-300">
                            <span>Open Forecast</span>
                            <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                        </div>
                    </div>

                    <div class="mobile-chevron">
                        <i class="fas fa-chevron-right group-hover:translate-x-1 transition-transform"></i>
                    </div>

                    <div class="bg-decoration absolute -bottom-6 -right-6 text-emerald-100 text-8xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </a>

            </div>
            
        </div>
    </div>
</x-layout-users>