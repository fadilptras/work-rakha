<x-layout-users title="{{ $title ?? 'Warehouse Dashboard' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @push('styles')
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #ede9fe;
        }
        .mesh-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-color: #ede9fe;
            background-image:
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            pointer-events: none;
        }
        /* Samakan dengan halaman stock/history: page-header biru */
        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            color: white;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
        }
        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(30deg);
            pointer-events: none;
        }
        .header-content { position: relative; z-index: 1; }

        /* Cards: horizontal ala manage-barang, tapi tetap light & konsisten */
        .wh-card {
            position: relative;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left-width: 4px;
            border-radius: 0.9rem;
            overflow: hidden;
            display: flex;
            flex-direction: row;
            align-items: stretch;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.25s cubic-bezier(0.16,1,0.3,1);
        }
        .wh-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.08);
            background: #ffffff;
        }
        .wh-card-orange { border-left-color: #f97316; }
        .wh-card-orange:hover { border-color: #e2e8f0; border-left-color: #f97316; box-shadow: 0 12px 28px rgba(249,115,22,0.14); }
        .wh-card-green { border-left-color: #10b981; }
        .wh-card-green:hover { border-color: #e2e8f0; border-left-color: #10b981; box-shadow: 0 12px 28px rgba(16,185,129,0.14); }
        .wh-card-blue { border-left-color: #3b82f6; }
        .wh-card-blue:hover { border-color: #e2e8f0; border-left-color: #3b82f6; box-shadow: 0 12px 28px rgba(59,130,246,0.14); }

        .wh-card-media {
            width: 96px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            position: relative;
        }
        .wh-card-orange .wh-card-media { background: #fff7ed; }
        .wh-card-green .wh-card-media { background: #ecfdf5; }
        .wh-card-blue .wh-card-media { background: #eff6ff; }

        .wh-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            border: 1px solid;
            transition: all 0.3s ease;
        }
        .wh-card-orange .wh-icon { background: white; color: #f97316; border-color: #ffedd5; }
        .wh-card-orange:hover .wh-icon { background: #f97316; color: white; transform: scale(1.05); }
        .wh-card-green .wh-icon { background: white; color: #10b981; border-color: #a7f3d0; }
        .wh-card-green:hover .wh-icon { background: #10b981; color: white; transform: scale(1.05); }
        .wh-card-blue .wh-icon { background: white; color: #3b82f6; border-color: #bfdbfe; }
        .wh-card-blue:hover .wh-icon { background: #3b82f6; color: white; transform: scale(1.05); }

        .wh-card-body {
            flex: 1;
            padding: 16px 18px 14px 18px;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .wh-card-kicker {
            font-size: 9px;
            letter-spacing: 0.12em;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .wh-card-orange .wh-card-kicker { color: #f97316; }
        .wh-card-green .wh-card-kicker { color: #10b981; }
        .wh-card-blue .wh-card-kicker { color: #3b82f6; }

        .wh-card h2 {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .wh-card p {
            font-size: 11.5px;
            line-height: 1.5;
            color: #64748b;
            font-weight: 500;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .wh-action {
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .wh-card-orange .wh-action { color: #f97316; }
        .wh-card-green .wh-action { color: #10b981; }
        .wh-card-blue .wh-action { color: #3b82f6; }
        .wh-action i {
            width: 20px;
            height: 20px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            transition: all 0.25s ease;
        }
        .wh-card:hover .wh-action i { background: currentColor; color: white; border-color: currentColor; transform: translateX(2px); }

        @media (max-width: 767px) {
            .wh-card { flex-direction: column; }
            .wh-card-media {
                width: 100%;
                height: 74px;
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
            }
            .wh-icon { width: 48px; height: 48px; font-size: 1.25rem; border-radius: 10px; }
            .wh-card-body { padding: 14px 16px; }
        }

        .wh-reveal {
            animation: whSlideIn 0.45s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0;
            transform: translateY(8px);
        }
        @keyframes whSlideIn {
            to { opacity: 1; transform: translateY(0); }
        }
        .wh-stagger-1 { animation-delay: 0.05s; }
        .wh-stagger-2 { animation-delay: 0.12s; }
        .wh-stagger-3 { animation-delay: 0.18s; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 pt-10 sm:pt-12 lg:pt-14 flex flex-col gap-6 md:gap-7">

            {{-- Header: samakan dengan stock/history - page-header biru --}}
            <div class="page-header wh-reveal mt-4 sm:mt-6">
                <div class="header-content flex flex-row items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-white/90"></span>
                            <span class="text-[10px] font-bold tracking-[0.14em] uppercase text-blue-100">Warehouse Operations</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white leading-tight">
                            Warehouse Dashboard
                        </h1>
                        <p class="text-blue-100 text-xs sm:text-sm mt-1.5 font-medium leading-relaxed max-w-2xl">
                            Pusat kontrol stok fisik, sinkronisasi Accurate, dan prediksi kebutuhan — selaras dengan halaman Stock &amp; Master Barang.
                        </p>
                    </div>
                    <div class="hidden md:flex w-14 h-14 rounded-xl bg-white/15 border border-white/20 items-center justify-center text-white text-xl shrink-0">
                        <i class="fas fa-warehouse"></i>
                    </div>
                </div>
            </div>

            {{-- 3 cards horizontal - susunan yang kamu suka, tapi styling light konsisten --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-5 mt-2">

                <a href="{{ route('sales.stock') }}" class="wh-card wh-card-orange wh-reveal wh-stagger-1 group">
                    <div class="wh-card-media">
                        <div class="wh-icon"><i class="fas fa-boxes-stacked"></i></div>
                    </div>
                    <div class="wh-card-body">
                        <div class="wh-card-kicker">01 — Inventory</div>
                        <h2>Stock Monitoring</h2>
                        <p>Pantau stok fisik, atur PO/stock dan sinkron dengan laporan Accurate.</p>
                        <div class="wh-action">Manage Stock <i class="fas fa-arrow-right"></i></div>
                    </div>
                </a>

                <a href="{{ route('sales.forecast') }}" class="wh-card wh-card-green wh-reveal wh-stagger-2 group">
                    <div class="wh-card-media">
                        <div class="wh-icon"><i class="fas fa-chart-area"></i></div>
                    </div>
                    <div class="wh-card-body">
                        <div class="wh-card-kicker">02 — Demand</div>
                        <h2>Sales Forecast</h2>
                        <p>Estimasi kebutuhan stok dari rata-rata 3 bulan agar tidak stockout.</p>
                        <div class="wh-action">Open Forecast <i class="fas fa-arrow-right"></i></div>
                    </div>
                </a>

                <a href="{{ route('sales.stock.barang.index') }}" class="wh-card wh-card-blue wh-reveal wh-stagger-3 group mt-3 lg:mt-4">
                    <div class="wh-card-media">
                        <div class="wh-icon"><i class="fas fa-cubes"></i></div>
                    </div>
                    <div class="wh-card-body">
                        <div class="wh-card-kicker">03 — Master</div>
                        <h2>Manage Barang</h2>
                        <p>Kelola master barang, aturan packaging &amp; clean name untuk pricing &amp; SPH.</p>
                        <div class="wh-action">Manage Items <i class="fas fa-arrow-right"></i></div>
                    </div>
                </a>

            </div>

        </div>
    </div>
</x-layout-users>
