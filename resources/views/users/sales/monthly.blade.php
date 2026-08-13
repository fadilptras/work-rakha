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
            background-color: #ede9fe; /* Menyelaraskan dengan pengajuan dana / dashboard */
        }

        .mesh-bg { 
            background-color: #ede9fe;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
        }

        /* == Header Style == */
        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1.5rem; padding: 1.5rem 2.5rem; color: white;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3); position: relative; overflow: hidden;
        }
        .page-header::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(30deg); pointer-events: none;
        }
        .header-content { position: relative; z-index: 1; }

        /* == Modern Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.9rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 20px;
            width: fit-content;
        }
        .btn-back-modern:hover { 
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
            color: #1d4ed8;
        }
        .btn-back-modern .icon-circle {
            width: 32px; height: 32px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6;
            font-size: 0.85rem;
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
        
        .main-tab-content { display: block; animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        /* Custom Scrollbar for tables */
        .overflow-x-auto::-webkit-scrollbar, .overflow-y-auto::-webkit-scrollbar { width: 8px; height: 8px; }
        .overflow-x-auto::-webkit-scrollbar-track, .overflow-y-auto::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .overflow-x-auto::-webkit-scrollbar-thumb, .overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover, .overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Monthly Cards */
        .monthly-card {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.5rem;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            cursor: pointer;
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
        
        .monthly-card span { font-size: 0.875rem; color: #475569; font-weight: 800; transition: color 0.3s; }
        .monthly-card:hover span, .monthly-card.active span { color: #2563eb; }
        .monthly-card i { color: #cbd5e1; transition: color 0.3s; font-size: 0.875rem; margin-top: 0.5rem; }
        .monthly-card:hover i, .monthly-card.active i { color: #3b82f6; }

        /* Sub tabs logic - handled by Tailwind now, keeping this empty just in case */
        .month-sub-tab {
            padding: 0.5rem 1rem; font-size: 0.875rem; border-radius: 999px; font-weight: 700;
            color: #64748b; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 2px solid transparent;
        }
        @media (min-width: 768px) {
            .month-sub-tab { padding: 0.65rem 1.5rem; font-size: 1rem; }
        }
        .month-sub-tab:hover { color: #3b82f6; background: #f8fafc; }
        .month-sub-tab.active { 
            color: #ffffff; 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            border-color: transparent;
        }

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
    </style>
    @endpush

    <div class="mesh-bg min-h-screen">
        <div class="p-4 sm:p-6 lg:p-10 w-full max-w-7xl mx-auto space-y-4 relative z-10">
        
        {{-- Tombol Kembali --}}
        <a href="{{ route('sales.index') }}" class="btn-back-modern">
            <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
            Kembali ke Dashboard
        </a>

        {{-- Header Halaman --}}
        <div class="page-header flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="header-content">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2">Monthly Monitoring</h1>
                <p class="text-blue-100 text-sm md:text-base opacity-90 max-w-2xl font-medium">Pantau detail performa bulanan dan drill-down data penjualan.</p>
            </div>
        </div>

        {{-- PILIH BULAN --}}
        <div class="glass-panel border-t-4 border-t-blue-500">
            <div class="flex items-center justify-between mb-2 cursor-pointer group" onclick="toggleMonthGrid()">
                <h3 class="flex flex-wrap items-center gap-2 text-base font-black text-slate-700 uppercase tracking-wide group-hover:text-blue-600 transition-colors">
                    <span><i class="fas fa-calendar-alt text-blue-500 mr-2"></i> Pilih Bulan (Tahun {{ $tahun }})</span>
                    <span id="selectedMonthLabel" class="hidden px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-sm font-bold leading-none"></span>
                </h3>
                <i class="fas fa-chevron-up text-slate-500 transition-transform duration-300" id="monthGridToggleIcon"></i>
            </div>
            
            <div id="monthGridContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-2 mt-4 transition-all duration-500 origin-top">
                @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $m)
                    <button class="monthly-card group" onclick="loadMonthlyDetail('{{ $m }}', this)">
                        <span class="block uppercase">{{ $m }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- DETAIL CONTAINER --}}
        <div id="monthly-detail-container" class="hidden space-y-4 transition-all duration-500">
            <div id="monthly-loader" class="hidden w-max text-blue-500 bg-blue-100 px-4 py-1.5 rounded-full text-sm font-bold animate-pulse">
                <i class="fas fa-circle-notch fa-spin mr-2"></i> Memuat Data...
            </div>

            {{-- Tombol Navigasi Sub-Tab --}}
            <div class="flex space-x-2 bg-white p-2 rounded-full shadow-sm border border-slate-200 overflow-x-auto" id="sub-tabs-container">
                <button class="month-sub-tab active whitespace-nowrap flex items-center" data-target="m-view-pdu" onclick="switchMonthSubTab('m-view-pdu', this)">
                    <i class="fas fa-list mr-2"></i> PDU
                </button>
                <button class="month-sub-tab whitespace-nowrap flex items-center" data-target="m-view-outlet" onclick="switchMonthSubTab('m-view-outlet', this)">
                    <i class="fas fa-store mr-2"></i> By Customer (Outlet)
                </button>
                <button class="month-sub-tab whitespace-nowrap flex items-center" data-target="m-view-product" onclick="switchMonthSubTab('m-view-product', this)">
                    <i class="fas fa-box mr-2"></i> By Product
                </button>
            </div>

            <div class="glass-panel border-t-0 shadow-lg !rounded-3xl">
                {{-- Tampilan 1: Data PDU --}}
                <div id="m-view-pdu" class="month-view">
                    {{-- Wadah Grafik --}}
                    <div class="mb-6 p-4 border border-slate-200 rounded-xl bg-slate-50">
                        <div class="flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center mb-4">
                            <h4 class="font-bold text-slate-700">Grafik Penjualan PDU</h4>
                            <div class="relative inline-block">
                                <select id="filterPduPs" class="appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-sm pl-4 pr-10 py-2 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors" onchange="drawPduView()">
                                    <option value="all">Semua PS</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-700">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto w-full">
                            <div style="min-width: 800px;">
                                <canvas id="chartPdu" style="max-height: 400px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto max-h-[600px] border border-slate-200 rounded-xl">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="table-header sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-xl text-xs md:text-sm uppercase tracking-wider">Hirarki Data</th>
                                    <th class="px-4 py-3 text-right text-xs md:text-sm uppercase tracking-wider border-l border-slate-200">Sum of Qty</th>
                                    <th class="px-4 py-3 text-right rounded-tr-xl text-xs md:text-sm uppercase tracking-wider border-l border-slate-200">Sum of Harga Nett</th>
                                </tr>
                            </thead>
                            <tbody id="m-tbody-pdu" class="divide-y divide-slate-200 bg-white">
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tampilan 2: Data per Outlet --}}
                <div id="m-view-outlet" class="month-view hidden">
                    {{-- Chart Container --}}
                    <div class="mb-6 p-4 border border-slate-200 rounded-xl bg-slate-50">
                        <div class="flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center mb-4">
                            <h4 class="font-bold text-slate-700">Grafik Penjualan per Outlet</h4>
                            <div class="relative inline-block">
                                <select id="filterOutletPs" class="appearance-none border border-blue-200 bg-blue-50 hover:bg-blue-100 shadow-sm rounded-lg text-sm pl-4 pr-10 py-2 font-semibold text-blue-700 focus:ring-blue-500 focus:border-blue-500 cursor-pointer outline-none transition-colors" onchange="drawOutletChart()">
                                    <option value="all">Semua PS</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-700">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <div id="outletChartContainer" style="position: relative; height: 500px; width: 100%;">
                            <canvas id="chartOutlet"></canvas>
                        </div>
                    </div>

                    <div class="overflow-x-auto max-h-[600px] border border-slate-200 rounded-xl">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="table-header sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-xl text-xs md:text-sm uppercase tracking-wider">Sales & Outlet</th>
                                    <th class="px-4 py-3 text-right rounded-tr-xl text-xs md:text-sm uppercase tracking-wider border-l border-slate-200">Sum of Harga Nett</th>
                                </tr>
                            </thead>
                            <tbody id="m-tbody-outlet" class="divide-y divide-slate-200 bg-white">
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tampilan 3: Data per Produk --}}
                <div id="m-view-product" class="month-view hidden">
                    <div class="overflow-x-auto max-h-[600px] border border-slate-200 rounded-xl">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="table-header sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-xl text-xs md:text-sm uppercase tracking-wider">Sales & Produk</th>
                                    <th class="px-4 py-3 text-right text-xs md:text-sm uppercase tracking-wider border-l border-slate-200">Sum of Qty</th>
                                    <th class="px-4 py-3 text-right rounded-tr-xl text-xs md:text-sm uppercase tracking-wider border-l border-slate-200">Sum of Harga Nett</th>
                                </tr>
                            </thead>
                            <tbody id="m-tbody-product" class="divide-y divide-slate-200 bg-white">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    @push('scripts')
    <script>
        function switchMonthSubTab(targetId, btnEl) {
            document.querySelectorAll('.month-sub-tab').forEach(b => {
                b.classList.remove('active');
            });
            btnEl.classList.add('active');

            document.querySelectorAll('.month-view').forEach(v => v.classList.add('hidden'));
            document.getElementById(targetId).classList.remove('hidden');
        }

        function toggleMonthGrid() {
            const container = document.getElementById('monthGridContainer');
            const icon = document.getElementById('monthGridToggleIcon');
            
            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
                icon.classList.remove('rotate-180');
            } else {
                container.classList.add('hidden');
                icon.classList.add('rotate-180');
            }
        }

        function toggleRowGroup(groupId, iconEl) {
            const rows = document.querySelectorAll('.' + groupId);
            if (!rows.length) return;
            const isHidden = rows[0].classList.contains('hidden');
            rows.forEach(r => {
                if (isHidden) {
                    r.classList.remove('hidden');
                } else {
                    r.classList.add('hidden');
                }
            });
            
            if (iconEl) {
                if (isHidden) {
                    iconEl.classList.remove('fa-plus-square');
                    iconEl.classList.add('fa-minus-square');
                } else {
                    iconEl.classList.remove('fa-minus-square');
                    iconEl.classList.add('fa-plus-square');
                }
            }
        }

        let pduChartInstance = null;
        let outletChartInstance = null;
        let currentMonthlyData = null;

        function drawOutletChart() {
            if (!currentMonthlyData) return;
            const data = currentMonthlyData;
            const psFilter = document.getElementById('filterOutletPs').value;

            if (outletChartInstance) outletChartInstance.destroy();

            let outletLabels = [];
            let outletSales = [];
            let outletBgColors = [];

            const colorPalette = [
                '#3b82f6', // blue-500
                '#f97316', // orange-500
                '#10b981', // emerald-500
                '#8b5cf6', // violet-500
                '#ef4444', // red-500
                '#06b6d4', // cyan-500
                '#f59e0b', // amber-500
                '#ec4899', // pink-500
                '#64748b'  // slate-500
            ];
            let psColorMap = {};
            let colorIndex = 0;

            let htmlOutlet = '';
            const fRp = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n||0));

            data.outlet.forEach((ps, idx1) => {
                if (!psColorMap[ps.nama]) {
                    psColorMap[ps.nama] = colorPalette[colorIndex % colorPalette.length];
                    colorIndex++;
                }

                if (psFilter === 'all' || psFilter === ps.nama) {
                    ps.customer.forEach(c => {
                        outletLabels.push(`[${ps.nama}] ${c.nama}`);
                        outletSales.push(c.nett);
                        outletBgColors.push(psColorMap[ps.nama]);
                    });
                    
                    let gId = `mo-ps-${idx1}`;
                    htmlOutlet += `
                        <tr class="row-level-1 cursor-pointer" onclick="toggleRowGroup('${gId}', this.querySelector('i'))">
                            <td class="px-4 py-3"><i class="far fa-plus-square mr-2 text-blue-500 w-4"></i>${ps.nama}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 border-l border-slate-200">${fRp(ps.total_nett)}</td>
                        </tr>
                    `;
                    ps.customer.forEach(c => {
                        htmlOutlet += `
                            <tr class="${gId} row-level-2 hidden">
                                <td class="py-2 pr-4 pl-12">${c.nama}</td>
                                <td class="px-4 py-2 text-right border-l border-slate-200">${fRp(c.nett)}</td>
                            </tr>
                        `;
                    });
                }
            });
            document.getElementById('m-tbody-outlet').innerHTML = htmlOutlet || '<tr><td colspan="2" class="text-center p-6 text-slate-500 font-medium">Tidak ada data</td></tr>';

            // Sesuaikan tinggi grafik Outlet secara otomatis berdasarkan jumlah datanya
            const container = document.getElementById('outletChartContainer');
            const dynamicHeight = Math.max(300, outletLabels.length * 40 + 80);
            container.style.height = dynamicHeight + 'px';
            const ctxOutlet = document.getElementById('chartOutlet').getContext('2d');
            outletChartInstance = new Chart(ctxOutlet, {
                type: 'bar',
                data: {
                    labels: outletLabels,
                    datasets: [{
                        label: 'Total Sales (Harga Nett)',
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
                            ticks: {
                                callback: function(value) { return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact" , compactDisplay: "short" }).format(value); }
                            },
                            suggestedMax: Math.max(...outletSales) * 1.2 // Tambahkan jarak di sisi kanan agar angka tidak terpotong
                        },
                        y: {
                            ticks: { autoSkip: false, font: { size: 11 } }
                        }
                    }
                }
            });
        }

        function loadMonthlyDetail(bulan, btnEl) {
            document.querySelectorAll('.monthly-card').forEach(c => {
                c.classList.remove('active');
            });
            btnEl.classList.add('active');
            
            
            // Tutup otomatis pilihan bulan dan perbarui label judul
            const labelEl = document.getElementById('selectedMonthLabel');
            labelEl.innerText = bulan;
            labelEl.classList.remove('hidden');
            document.getElementById('monthGridContainer').classList.add('hidden');
            document.getElementById('monthGridToggleIcon').classList.add('rotate-180');
            
            document.getElementById('monthly-detail-container').classList.remove('hidden');
            document.getElementById('monthly-loader').classList.remove('hidden');

            let tahun = "{{ $tahun }}";
            fetch(`{{ route('sales.monthly.detail') }}?tahun=${tahun}&bulan=${bulan}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('monthly-loader').classList.add('hidden');
                    if (data.error) { alert(data.error); return; }
                    
                    const fRp = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n||0));
                    const fNum = (n) => new Intl.NumberFormat('id-ID').format(n||0);
                    
                    // Catatan: Pembuatan grafik Outlet (View 2) dan PDU (View 1) dipanggil secara terpisah

                    // Tampilkan View 3: Tabel berdasarkan Produk
                    let htmlProd = '';
                    data.product.forEach((ps, idx1) => {
                        let gId = `mp-ps-${idx1}`;
                        htmlProd += `
                            <tr class="row-level-1 cursor-pointer" onclick="toggleRowGroup('${gId}', this.querySelector('i'))">
                                <td class="px-4 py-3"><i class="far fa-plus-square mr-2 text-blue-500 w-4"></i>${ps.nama}</td>
                                <td class="px-4 py-3 text-right border-l border-slate-200">${fNum(ps.total_qty)}</td>
                                <td class="px-4 py-3 text-right text-emerald-600 border-l border-slate-200">${fRp(ps.total_nett)}</td>
                            </tr>
                        `;
                        ps.produk.forEach(p => {
                            htmlProd += `
                                <tr class="${gId} row-level-2 hidden">
                                    <td class="py-2 pr-4 pl-12">${p.nama}</td>
                                    <td class="px-4 py-2 text-right border-l border-slate-200">${fNum(p.qty)}</td>
                                    <td class="px-4 py-2 text-right border-l border-slate-200">${fRp(p.nett)}</td>
                                </tr>
                            `;
                        });
                    });
                    document.getElementById('m-tbody-product').innerHTML = htmlProd || '<tr><td colspan="3" class="text-center p-6 text-slate-500 font-medium">Tidak ada data</td></tr>';

                    // Tampilkan View 1: PDU (dipanggil terpisah)
                    
                    // Isi pilihan dropdown filter PS (Project Sales)
                    let psOutletSelect = document.getElementById('filterOutletPs');
                    let psPduSelect = document.getElementById('filterPduPs');
                    psOutletSelect.innerHTML = '<option value="all">Semua PS</option>';
                    psPduSelect.innerHTML = '<option value="all">Semua PS</option>';
                    
                    data.outlet.forEach(ps => {
                        psOutletSelect.innerHTML += `<option value="${ps.nama}">${ps.nama}</option>`;
                    });
                    data.pdu.forEach(ps => {
                        psPduSelect.innerHTML += `<option value="${ps.nama}">${ps.nama}</option>`;
                    });

                    // --- GAMBAR GRAFIK & TABEL ---
                    Chart.register(ChartDataLabels);

                    currentMonthlyData = data;
                    drawPduView();
                    drawOutletChart();

                }).catch(err => {
                    console.error("Failed to load monthly details", err);
                    document.getElementById('monthly-loader').classList.add('hidden');
                    alert('Gagal mengambil data bulanan.');
                });
        }
        
        function drawPduView() {
            if (!currentMonthlyData) return;
            const data = currentMonthlyData;
            const psFilter = document.getElementById('filterPduPs').value;

            // 1. Tampilkan Tabel PDU
            let htmlPdu = '';
            const fRp = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n||0));
            const fNum = (n) => new Intl.NumberFormat('id-ID').format(n||0);
            
            data.pdu.forEach((ps, idx1) => {
                if (psFilter !== 'all' && psFilter !== ps.nama) return;

                let gidPs = `mpd-ps-${idx1}`;
                htmlPdu += `
                    <tr class="row-level-1 cursor-pointer" onclick="toggleRowGroup('${gidPs}', this.querySelector('i'))">
                        <td class="px-4 py-3 text-[14px]"><i class="far fa-plus-square mr-2 text-blue-500 w-4"></i>${ps.nama}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-200">${fNum(ps.total_qty)}</td>
                        <td class="px-4 py-3 text-right text-emerald-600 border-l border-slate-200">${fRp(ps.total_nett)}</td>
                    </tr>
                `;
                ps.tanggal.forEach((tgl, idx2) => {
                    let gidTgl = `mpd-ps-${idx1}-tgl-${idx2}`;
                    htmlPdu += `
                        <tr class="${gidPs} row-level-2 hidden cursor-pointer" onclick="toggleRowGroup('${gidTgl}', this.querySelector('i'))">
                            <td class="py-2 pr-4 pl-12"><div class="flex items-start"><i class="far fa-plus-square mr-3 mt-1 text-blue-500 w-4 text-center"></i><span>${tgl.nama}</span></div></td>
                            <td class="px-4 py-2 text-right border-l border-slate-200">${fNum(tgl.total_qty)}</td>
                            <td class="px-4 py-2 text-right text-emerald-600 border-l border-slate-200">${fRp(tgl.total_nett)}</td>
                        </tr>
                    `;
                    tgl.customer.forEach((cust, idx3) => {
                        let gidCust = `mpd-ps-${idx1}-tgl-${idx2}-cust-${idx3}`;
                        htmlPdu += `
                            <tr class="${gidPs} ${gidTgl} row-level-3 hidden cursor-pointer" onclick="toggleRowGroup('${gidCust}', this.querySelector('i'))">
                                <td class="py-2 pr-4 pl-12"><div class="flex items-start"><i class="far fa-plus-square mr-3 mt-1 text-amber-500 w-4 text-center"></i><span>${cust.nama}</span></div></td>
                                <td class="px-4 py-2 text-right border-l border-slate-200">${fNum(cust.total_qty)}</td>
                                <td class="px-4 py-2 text-right text-emerald-600 border-l border-slate-200">${fRp(cust.total_nett)}</td>
                            </tr>
                        `;
                        cust.produk.forEach(prod => {
                            htmlPdu += `
                                <tr class="${gidPs} ${gidTgl} ${gidCust} row-level-4 hidden">
                                    <td class="py-2 pr-4 pl-12 break-words whitespace-normal leading-tight"><div class="flex items-start"><span class="w-4 mr-3 inline-block"></span><span>${prod.nama}</span></div></td>
                                    <td class="px-4 py-2 text-right border-l border-slate-200">${fNum(prod.qty)}</td>
                                    <td class="px-4 py-2 text-right border-l border-slate-200">${fRp(prod.nett)}</td>
                                </tr>
                            `;
                        });
                    });
                });
            });
            document.getElementById('m-tbody-pdu').innerHTML = htmlPdu || '<tr><td colspan="3" class="text-center p-6 text-slate-500 font-medium">Tidak ada data</td></tr>';

            // 2. Tampilkan Grafik PDU
            if (pduChartInstance) pduChartInstance.destroy();
            
            let filteredPdu = data.pdu.filter(p => psFilter === 'all' || p.nama === psFilter);
            let pduLabels = filteredPdu.map(p => p.nama);
            let pduTarget = filteredPdu.map(p => p.target_amount);
            let pduSales = filteredPdu.map(p => p.total_nett);
            let pduPerc = filteredPdu.map(p => p.target_amount > 0 ? ((p.total_nett / p.target_amount) * 100).toFixed(1) : null);

            const ctxPdu = document.getElementById('chartPdu').getContext('2d');
            pduChartInstance = new Chart(ctxPdu, {
                type: 'bar',

                        data: {
                            labels: pduLabels,
                            datasets: [
                                {
                                    type: 'line',
                                    label: 'Achievement %',
                                    data: pduSales,
                                    borderColor: '#9ca3af',
                                    borderWidth: 3,
                                    pointBackgroundColor: '#ef4444',
                                    pointBorderColor: '#fff',
                                    pointRadius: 5,
                                    fill: false,
                                    spanGaps: true
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
                                        return context.dataset.type === 'line' ? 'bottom' : 'top';
                                    },
                                    offset: function(context) {
                                        return context.dataset.type === 'line' ? 10 : 4;
                                    },
                                    color: function(context) {
                                        return context.dataset.type === 'line' ? '#ef4444' : '#64748b';
                                    },
                                    backgroundColor: function(context) {
                                        return context.dataset.type === 'line' ? 'rgba(255, 255, 255, 0.8)' : 'transparent';
                                    },
                                    borderRadius: 4,
                                    font: { size: 10, weight: 'bold' },
                                    formatter: function(value, context) {
                                        if (context.dataset.type === 'line') {
                                            let perc = pduPerc[context.dataIndex];
                                            return perc > 0 ? perc + '%' : '0%';
                                        }
                                        if (value === null || value === 0) return '';
                                        return new Intl.NumberFormat('id-ID').format(value);
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) label += ': ';
                                            if (context.dataset.type === 'line') {
                                                let perc = pduPerc[context.dataIndex];
                                                label += (perc > 0 ? perc : 0) + '%';
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
                                    ticks: {
                                        callback: function(value) { return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact" , compactDisplay: "short" }).format(value); }
                                    }
                                }
                            }
                        }
                    });
        }
    </script>
    @endpush
</x-layout-users>
