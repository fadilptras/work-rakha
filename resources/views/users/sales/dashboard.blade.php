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
            background-color: #ede9fe;
        }

        .mesh-bg { 
            background-color: #ede9fe;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            padding: 28px;
        }

        .module-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 24px;
            padding: 40px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 320px;
            text-decoration: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            z-index: 1;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
            background: rgba(255, 255, 255, 0.95);
        }

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

        .card-green .icon-box {
            background: #ecfdf5;
            color: #10b981;
            border: 1px solid #d1fae5;
        }
        .card-green:hover .icon-box {
            background: #10b981;
            color: white;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }

        .card-blue .icon-box {
            background: #eff6ff;
            color: #3b82f6;
            border: 1px solid #dbeafe;
        }
        .card-blue:hover .icon-box {
            background: #3b82f6;
            color: white;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        .card-purple .icon-box {
            background: #faf5ff;
            color: #a855f7;
            border: 1px solid #f3e8ff;
        }
        .card-purple:hover .icon-box {
            background: #a855f7;
            color: white;
            box-shadow: 0 10px 20px rgba(168, 85, 247, 0.2);
        }

        .card-orange .icon-box {
            background: #fff7ed;
            color: #f97316;
            border: 1px solid #ffedd5;
        }
        .card-orange:hover .icon-box {
            background: #f97316;
            color: white;
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.2);
        }

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

    <div class="flex flex-col flex-1 h-full mesh-bg relative overflow-hidden text-slate-800">
        
        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:px-12 lg:pb-8 flex-1 flex flex-col gap-6 justify-center mt-6 lg:mt-6">

            {{-- Bagian Header --}}
            <div class="text-center w-full title-reveal glass-card shadow-sm" style="padding: 1.25rem 2rem;">
                <h1 class="text-4xl md:text-5xl font-black tracking-tight text-slate-800 mb-2 leading-tight">
                    Sales <span class="text-blue-600">Command Center</span>
                </h1>
                <p class="text-slate-500 text-lg font-medium leading-relaxed max-w-4xl mx-auto">
                    Akses terpusat untuk mengelola, menganalisis, dan memantau performa penjualan perusahaan secara komprehensif.
                </p>
            </div>

            {{-- Kartu Utama --}}
            @if(isset($hasAnyAccess) && $hasAnyAccess)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 relative z-10">
                
                @if(isset($hasFullAccess) && $hasFullAccess)
                {{-- Kartu 1: Kelola Data --}}
                <a href="{{ route('sales.manage') }}" class="module-card card-green group title-reveal stagger-1">
                    <div>
                        <div class="icon-box">
                            <i class="fas fa-database"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-3 tracking-tight group-hover:text-emerald-600 transition-colors">Data Management</h2>
                        <p class="text-slate-500 text-base leading-relaxed font-medium">
                            Pusat kendali data. Input data penjualan baru, unggah file batch secara massal, atau lakukan penyesuaian pada riwayat data yang ada.
                        </p>
                    </div>
                    
                    <div class="mt-8 flex items-center gap-2 text-sm font-bold text-emerald-600 opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                        <span>Kelola Data</span>
                        <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                    </div>
                    
                    {{-- Dekorasi latar belakang kartu --}}
                    <div class="absolute -bottom-6 -right-6 text-emerald-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-server"></i>
                    </div>
                </a>
                
                {{-- Kartu 2: Analitik & Wawasan --}}
                <a href="{{ route('sales.analytics') }}" class="module-card card-blue group title-reveal stagger-2">
                    <div>
                        <div class="icon-box">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-3 tracking-tight group-hover:text-blue-600 transition-colors">Analytics & Insights</h2>
                        <p class="text-slate-500 text-base leading-relaxed font-medium">
                            Pantau performa. Akses dashboard Power BI interaktif, evaluasi pencapaian target, dan identifikasi tren penjualan.
                        </p>
                    </div>
                    
                    <div class="mt-8 flex items-center gap-2 text-sm font-bold text-blue-600 opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                        <span>Buka Analitik</span>
                        <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                    </div>

                    {{-- Dekorasi latar belakang kartu --}}
                    <div class="absolute -bottom-6 -right-6 text-blue-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </a>
                @endif

                {{-- Kartu 3: Pantauan Bulanan --}}
                <a href="{{ route('sales.monthly') }}" class="module-card card-purple group title-reveal stagger-3">
                    <div>
                        <div class="icon-box">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-3 tracking-tight group-hover:text-purple-600 transition-colors">Monthly Monitoring</h2>
                        <p class="text-slate-500 text-base leading-relaxed font-medium">
                            Pantau secara rinci performa penjualan per bulan. Akses data drill-down ke level produk dan outlet.
                        </p>
                    </div>
                    
                    <div class="mt-8 flex items-center gap-2 text-sm font-bold text-purple-600 opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                        <span>Buka Monitoring</span>
                        <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                    </div>

                    {{-- Dekorasi latar belakang kartu --}}
                    <div class="absolute -bottom-6 -right-6 text-purple-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </a>

                {{-- Kartu 4: Monitoring Stock --}}
                <a href="{{ route('sales.stock') }}" class="module-card card-orange group title-reveal stagger-1" style="animation-delay: 0.4s;">
                    <div>
                        <div class="icon-box">
                            <i class="fas fa-boxes-stacked"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800 mb-3 tracking-tight group-hover:text-orange-600 transition-colors">Stock Monitoring</h2>
                        <p class="text-slate-500 text-base leading-relaxed font-medium">
                            Pantau ketersediaan barang. Akses data inventori, status aman, atau menipis secara terstruktur.
                        </p>
                    </div>
                    
                    <div class="mt-8 flex items-center gap-2 text-sm font-bold text-orange-600 opacity-80 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                        <span>Buka Monitoring</span>
                        <i class="fas fa-arrow-right transition-transform group-hover:translate-x-2"></i>
                    </div>

                    {{-- Dekorasi latar belakang kartu --}}
                    <div class="absolute -bottom-6 -right-6 text-orange-100 text-9xl group-hover:scale-125 group-hover:-translate-x-2 group-hover:-translate-y-2 group-hover:-rotate-12 transition-all duration-700 z-[-1]">
                        <i class="fas fa-box-open"></i>
                    </div>
                </a>

            </div>
            @else
            <div class="glass-card text-center w-full title-reveal py-12 flex flex-col items-center justify-center">
                <div class="w-20 h-20 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mb-6 border border-rose-100">
                    <i class="fas fa-lock text-3xl"></i>
                </div>
                <h2 class="text-2xl font-black text-slate-800 mb-3 tracking-tight">Akses Dibatasi</h2>
                <p class="text-slate-500 font-medium text-base max-w-2xl mx-auto leading-relaxed">
                    Maaf, Anda tidak memiliki hak akses untuk melihat menu Sales Command Center. Fitur ini hanya tersedia untuk anggota Divisi Marketing dan Operasional & Top Management.
                </p>
            </div>
            @endif
            
        </div>
    </div>
</x-layout-users>