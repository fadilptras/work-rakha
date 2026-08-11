@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Monitoring Sales' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    @push('styles')
    <style>
        .mesh-bg { background-color: #ede9fe; }
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,1); border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); padding: 24px; }
        .modern-select { background: rgba(255,255,255,0.95); border: 2px solid #e2e8f0; border-radius: 14px; padding: 10px 14px; font-size: 0.8rem; color: #1e293b; font-weight: 700; outline: none; width: 100%; }
        .modern-select:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.15); }
        .modern-label { display: block; font-size: 0.7rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
        .summary-card { border-radius: 20px; padding: 20px; color: white; position: relative; overflow: hidden; }
        .tab-btn { padding: 10px 18px; border-radius: 12px; font-weight: 800; font-size: 0.8rem; color: #64748b; cursor: pointer; transition: all .2s; }
        .tab-btn.active { background: #4f46e5; color: white; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
        table.data-table th { position: sticky; top: 0; background: #f8fafc; }
        
        /* Choices.js Customization to match modern-select */
        .choices__inner { border-radius: 14px; border: 2px solid #e2e8f0; background: rgba(255,255,255,0.95); padding: 5px 8px; min-height: 42px; }
        .choices.is-focused .choices__inner { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.15); }
        .choices__list--multiple .choices__item { background-color: #4f46e5; border: none; border-radius: 8px; font-weight: bold; }
        
        @media (max-width: 767.98px) {
            .glass-card { padding: 14px; border-radius: 18px; backdrop-filter: none !important; -webkit-backdrop-filter: none !important; background: rgba(255,255,255,0.95) !important; }
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">
        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col">

            {{-- HEADER --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-2xl md:rounded-3xl p-5 md:p-6 shadow-xl mb-6 overflow-hidden border border-white/20 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="relative z-10 flex items-center gap-3 md:gap-4">
                    <div class="h-10 w-10 md:h-12 md:w-12 rounded-xl md:rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                        <i class="fas fa-chart-line text-lg md:text-xl text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-base md:text-xl font-black tracking-tight text-white uppercase">Monitoring Sales</h1>
                        @if(!$isMobile)
                        <p class="text-blue-100 text-xs md:text-sm mt-0.5 font-medium leading-relaxed">
                            Analisis trend, target, dan performa penjualan secara real-time.
                        </p>
                        @endif
                    </div>
                </div>
                <div class="mt-4 md:mt-0 relative z-10 flex flex-wrap gap-2">
                    <a href="{{ route('sales.visualisasi') }}" class="inline-flex items-center px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-xl font-bold transition-all border border-sky-400 text-sm shadow-lg hover:shadow-sky-500/50">
                        <i class="fas fa-chart-pie mr-2"></i> Power BI Visualisasi
                    </a>
                    <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl font-bold transition-all border border-white/30 text-sm backdrop-blur-md">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Input Data
                    </a>
                </div>
            </div>

            {{-- SUMMARY CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6 relative z-10">
                <div class="summary-card bg-gradient-to-br from-emerald-500 to-emerald-700">
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-100">Total Sales Nett</p>
                    <p id="sum-nett" class="text-2xl font-black mt-2">Rp 0</p>
                </div>
                <div class="summary-card bg-gradient-to-br from-blue-500 to-blue-700">
                    <p class="text-xs font-bold uppercase tracking-wide text-blue-100">Total Qty Terjual</p>
                    <p id="sum-qty" class="text-2xl font-black mt-2">0</p>
                </div>
                <div class="summary-card bg-gradient-to-br from-amber-500 to-amber-700">
                    <p class="text-xs font-bold uppercase tracking-wide text-amber-100">Achievement Rate (Bulan Terakhir)</p>
                    <a href="{{ route('sales.target') }}" id="sum-rate" class="block text-2xl font-black mt-2 hover:text-amber-200 transition-colors cursor-pointer">-</a>
                </div>
            </div>

            <style>
                .choices__list--dropdown .choices__item {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            </style>
            {{-- FILTER BAR --}}
            <div class="glass-card mb-6 border-t-4 border-t-indigo-500 relative z-50">
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="modern-label">Tahun</label>
                        <select id="f-tahun" class="modern-select">
                            <option value="">Semua Tahun</option>
                            @foreach($listTahun as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="modern-label">Bulan</label>
                        <select id="f-bulan" class="modern-select choices-multiple" multiple>
                            @foreach($listBulan as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="modern-label">Customer</label>
                        <select id="f-customer" class="modern-select choices-multiple" multiple>
                            @foreach($listCustomer as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="modern-label">Sales (PS)</label>
                        <select id="f-ps" class="modern-select choices-multiple" multiple>
                            @foreach($listPs as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="modern-label">Produk</label>
                        <select id="f-produk" class="modern-select choices-multiple" multiple>
                            @foreach($listProduk as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <button id="btn-reset" type="button" class="px-4 py-2 rounded-xl font-bold text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 transition-all">
                        <i class="fas fa-rotate-left mr-1"></i> Reset Filter
                    </button>
                    <button id="btn-apply" type="button" class="px-5 py-2 rounded-xl font-bold text-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-lg">
                        <i class="fas fa-filter mr-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>

            {{-- TABS: TABEL DETAIL --}}
            <div class="glass-card !p-0 overflow-hidden mb-6">
                <div class="flex flex-wrap gap-2 px-6 pt-6">
                    <div class="tab-btn active" data-tab="tab-customer">By Customer</div>
                    <div class="tab-btn" data-tab="tab-produk">By Produk</div>
                    <div class="tab-btn" data-tab="tab-customer-produk">By Customer & Produk</div>
                    <div class="tab-btn" data-tab="tab-ps">By Sales (PS)</div>
                    <div class="tab-btn" data-tab="tab-ps-produk">By Sales & Produk</div>
                    <div class="tab-btn" data-tab="tab-forecast">Sales Forecast</div>
                </div>

                <div class="p-6">
                    <div id="tab-customer" class="tab-panel active">
                        <div class="overflow-x-auto max-h-[480px] overflow-y-auto">
                            <table class="w-full text-sm data-table">
                                <thead id="head-customer" class="text-xs text-slate-500 uppercase bg-slate-50 border-b font-bold">
                                </thead>
                                <tbody id="body-customer" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div id="tab-customer-produk" class="tab-panel">
                        <div class="overflow-x-auto max-h-[480px] overflow-y-auto">
                            <table class="w-full text-sm data-table">
                                <thead id="head-customer-produk" class="text-xs text-slate-500 uppercase bg-slate-50 border-b font-bold">
                                </thead>
                                <tbody id="body-customer-produk" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="tab-produk" class="tab-panel">
                        <div class="overflow-x-auto max-h-[480px] overflow-y-auto">
                            <table class="w-full text-sm data-table">
                                <thead id="head-produk" class="text-xs text-slate-500 uppercase bg-slate-50 border-b font-bold">
                                </thead>
                                <tbody id="body-produk" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="tab-ps" class="tab-panel">
                        <div class="overflow-x-auto max-h-[480px] overflow-y-auto">
                            <table class="w-full text-sm data-table">
                                <thead id="head-ps" class="text-xs text-slate-500 uppercase bg-slate-50 border-b font-bold">
                                </thead>
                                <tbody id="body-ps" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="tab-ps-produk" class="tab-panel">
                        <div class="overflow-x-auto max-h-[480px] overflow-y-auto">
                            <table class="w-full text-sm data-table">
                                <thead id="head-ps-produk" class="text-xs text-slate-500 uppercase bg-slate-50 border-b font-bold">
                                </thead>
                                <tbody id="body-ps-produk" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="tab-forecast" class="tab-panel">
                        <p class="text-xs text-slate-500 font-semibold mb-4">
                            Estimasi kebutuhan stok bulan depan = rata-rata qty 7 bulan terakhir per produk × 1.2 (buffer 20%).
                        </p>
                        <div class="overflow-x-auto max-h-[480px] overflow-y-auto">
                            <table class="w-full text-sm data-table">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b font-bold">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Nama Produk</th>
                                        <th class="px-4 py-3 text-right">Rata-rata Qty / Bulan</th>
                                        <th class="px-4 py-3 text-right">Jumlah Bulan Data</th>
                                        <th class="px-4 py-3 text-right">Estimasi Stok Bulan Depan</th>
                                    </tr>
                                </thead>
                                <tbody id="body-forecast" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CHARTS --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="glass-card">
                    <h3 class="text-sm font-black text-slate-800 mb-4 uppercase flex items-center">
                        <i class="fas fa-chart-line text-indigo-500 mr-2"></i> Trend Sales Nett per Bulan
                    </h3>
                    <canvas id="chartTrend" height="220"></canvas>
                </div>
                <div class="glass-card">
                    <h3 class="text-sm font-black text-slate-800 mb-4 uppercase flex items-center">
                        <i class="fas fa-bullseye text-amber-500 mr-2"></i> Target vs Achievement
                    </h3>
                    <canvas id="chartTarget" height="220"></canvas>
                </div>
            </div>

        </div>
    </div>

    <script>
    (function () {
        const endpoint = "{{ route('sales.monitoring.data') }}";
        let chartTrend, chartTarget;

        function formatRupiah(n) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
        }
        function formatNumber(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
        }
        function getMultiSelectValues(id) {
            return Array.from(document.getElementById(id).selectedOptions).map(o => o.value);
        }

        function buildParams() {
            const params = new URLSearchParams();
            const tahun = document.getElementById('f-tahun').value;
            if (tahun) params.append('tahun', tahun);
            getMultiSelectValues('f-bulan').forEach(v => params.append('bulan[]', v));
            getMultiSelectValues('f-customer').forEach(v => params.append('nama_customer[]', v));
            getMultiSelectValues('f-ps').forEach(v => params.append('ps[]', v));
            getMultiSelectValues('f-produk').forEach(v => params.append('nama_produk[]', v));
            return params;
        }

        function renderSummary(summary) {
            document.getElementById('sum-nett').innerText = formatRupiah(summary.total_nett);
            document.getElementById('sum-qty').innerText = formatNumber(summary.total_qty);
            
            const rateEl = document.getElementById('sum-rate');
            if (summary.achievement_rate !== null) {
                rateEl.innerText = summary.achievement_rate + '%';
                rateEl.classList.remove('text-xl');
                rateEl.classList.add('text-2xl');
            } else {
                rateEl.innerText = 'Belum ada target';
                rateEl.classList.remove('text-2xl');
                rateEl.classList.add('text-xl');
            }
        }

        function renderTrendChart(trend) {
            const labels = trend.map(t => t.bulan);
            const data = trend.map(t => t.total);
            if (chartTrend) chartTrend.destroy();
            chartTrend = new Chart(document.getElementById('chartTrend'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Sales Nett',
                        data,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { ticks: { callback: v => formatRupiah(v) } } }
                }
            });
        }

        function renderTargetChart(rows) {
            const labels = rows.map(r => r.bulan);
            const target = rows.map(r => r.target);
            const actual = rows.map(r => r.actual);
            if (chartTarget) chartTarget.destroy();
            chartTarget = new Chart(document.getElementById('chartTarget'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Target', data: target, backgroundColor: '#e2e8f0' },
                        { label: 'Aktual', data: actual, backgroundColor: '#4f46e5' },
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { ticks: { callback: v => formatRupiah(v) } } }
                }
            });
        }

        function renderMatrixTable(headId, bodyId, rows, listBulan, firstColLabel, nameKey, qtyOnLeft = false) {
            const head = document.getElementById(headId);
            const body = document.getElementById(bodyId);
            
            // Build header
            let headHtml = `<tr>
                <th class="px-4 py-3 text-left w-72 min-w-[320px] sticky left-0 z-20 bg-slate-50 shadow-[1px_0_0_#e2e8f0]">${firstColLabel}</th>`;
            if (qtyOnLeft) {
                headHtml += `<th class="px-4 py-3 text-right whitespace-nowrap text-indigo-600 bg-slate-50 border-r border-slate-200">Total Qty</th>`;
            }
            headHtml += `<th class="px-4 py-3 text-right whitespace-nowrap text-emerald-600 bg-slate-50 border-r border-slate-200 shadow-[1px_0_0_#e2e8f0]">Total Sales Nett</th>`;
            listBulan.forEach(b => {
                headHtml += `<th class="px-4 py-3 text-right whitespace-nowrap min-w-[120px] w-[120px]">${b.substring(0,3)}</th>`;
            });
            if (!qtyOnLeft) {
                headHtml += `<th class="px-4 py-3 text-right whitespace-nowrap text-indigo-600 bg-slate-50 border-l border-slate-200 shadow-[-1px_0_0_#e2e8f0]">Total Qty</th>`;
            }
            headHtml += `</tr>`;
            head.innerHTML = headHtml;

            // Build body
            if (!rows || rows.length === 0) {
                const colspan = listBulan.length + 3;
                body.innerHTML = `<tr><td colspan="${colspan}" class="px-4 py-8 text-center text-slate-400">Tidak ada data</td></tr>`;
                return;
            }

            body.innerHTML = rows.map(r => {
                let unitLabel = r.satuan ? ` <span class="text-xs text-slate-400 font-normal">(${r.satuan})</span>` : '';
                let unitInline = r.satuan ? ` ${r.satuan}` : '';
                
                let rowHtml = `<tr class="group hover:bg-indigo-50/50 transition-colors">
                    <td class="px-4 py-3 font-bold text-slate-800 sticky left-0 z-10 bg-white group-hover:bg-indigo-50 shadow-[1px_0_0_#e2e8f0]">${r[nameKey] ?? '-'}${unitLabel}</td>`;
                
                if (qtyOnLeft) {
                    rowHtml += `<td class="px-4 py-3 text-right font-bold text-xs text-indigo-600 bg-slate-50/50 group-hover:bg-indigo-100/50 border-r border-slate-200 whitespace-nowrap">${formatNumber(r.total_qty)}${unitInline}</td>`;
                }
                rowHtml += `<td class="px-4 py-3 text-right font-bold text-xs text-emerald-600 bg-slate-50/50 group-hover:bg-indigo-100/50 border-r border-slate-200 shadow-[1px_0_0_#e2e8f0] whitespace-nowrap">${formatRupiah(r.total_nett)}</td>`;

                listBulan.forEach(b => {
                    const d = r.bulanan[b];
                    rowHtml += `<td class="px-4 py-3 text-right border-x border-slate-50">
                        <div class="text-[10px] text-slate-400 font-medium whitespace-nowrap">${formatNumber(d.qty)}${unitInline}</div>
                        <div class="text-[11px] font-bold text-slate-700 whitespace-nowrap">${formatRupiah(d.nett)}</div>
                    </td>`;
                });
                
                if (!qtyOnLeft) {
                    rowHtml += `<td class="px-4 py-3 text-right font-bold text-xs text-indigo-600 bg-slate-50/50 group-hover:bg-indigo-100/50 border-l border-slate-200 whitespace-nowrap">${formatNumber(r.total_qty)}${unitInline}</td>`;
                }

                rowHtml += `</tr>`;
                return rowHtml;
            }).join('');
        }

        function renderTableForecast(rows) {
            const body = document.getElementById('body-forecast');
            body.innerHTML = rows.length ? rows.map(r => `
                <tr class="hover:bg-indigo-50/50">
                    <td class="px-4 py-3 font-bold text-slate-800">${r.nama_produk ?? '-'}</td>
                    <td class="px-4 py-3 text-right">${formatNumber(r.avg_qty)}</td>
                    <td class="px-4 py-3 text-right">${r.jumlah_bulan}</td>
                    <td class="px-4 py-3 text-right font-bold text-indigo-600">${formatNumber(r.forecast_qty)}</td>
                </tr>`).join('') : `<tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Tidak ada data</td></tr>`;
        }

        function loadData() {
            const params = buildParams();
            fetch(endpoint + '?' + params.toString())
                .then(res => res.json())
                .then(json => {
                    renderSummary(json.summary);
                    renderTrendChart(json.trend);
                    renderTargetChart(json.target_vs_achievement);
                    renderMatrixTable('head-customer', 'body-customer', json.per_customer, json.list_bulan, 'Customer / RS', 'nama', false);
                    renderMatrixAccordionTable('head-customer-produk', 'body-customer-produk', json.per_customer_produk, json.list_bulan, 'Customer / RS', 'nama', false);
                    renderMatrixTable('head-produk', 'body-produk', json.per_produk, json.list_bulan, 'Nama Produk', 'nama', true);
                    renderMatrixTable('head-ps', 'body-ps', json.per_ps, json.list_bulan, 'Nama Sales (PS)', 'nama', false);
                    renderPivotTable('head-ps-produk', 'body-ps-produk', json.pivot_produk_ps, json.list_ps_pivot);
                    renderTableForecast(json.stock_forecast);
                })
                .catch(err => console.error('Gagal memuat data monitoring:', err));
        }

        function renderMatrixAccordionTable(headId, bodyId, rows, listBulan, firstColLabel, nameKey, qtyOnLeft = false) {
            const head = document.getElementById(headId);
            const body = document.getElementById(bodyId);
            
            // Build header
            let headHtml = `<tr>
                <th class="px-4 py-3 text-left w-72 min-w-[320px] sticky left-0 z-20 bg-slate-50 shadow-[1px_0_0_#e2e8f0]">${firstColLabel}</th>`;
            if (qtyOnLeft) {
                headHtml += `<th class="px-4 py-3 text-right whitespace-nowrap text-indigo-600 bg-slate-50 border-r border-slate-200">Total Qty</th>`;
            }
            headHtml += `<th class="px-4 py-3 text-right whitespace-nowrap text-emerald-600 bg-slate-50 border-r border-slate-200 shadow-[1px_0_0_#e2e8f0]">Total Sales Nett</th>`;
            listBulan.forEach(b => {
                headHtml += `<th class="px-4 py-3 text-right whitespace-nowrap min-w-[120px] w-[120px]">${b.substring(0,3)}</th>`;
            });
            if (!qtyOnLeft) {
                headHtml += `<th class="px-4 py-3 text-right whitespace-nowrap text-indigo-600 bg-slate-50 border-l border-slate-200 shadow-[-1px_0_0_#e2e8f0]">Total Qty</th>`;
            }
            headHtml += `</tr>`;
            head.innerHTML = headHtml;

            // Build body
            if (!rows || rows.length === 0) {
                const colspan = listBulan.length + 3;
                body.innerHTML = `<tr><td colspan="${colspan}" class="px-4 py-8 text-center text-slate-400">Tidak ada data</td></tr>`;
                return;
            }

            body.innerHTML = rows.map((r, idx) => {
                const hasSub = r.sub && r.sub.length > 0;
                let unitLabel = r.satuan ? ` <span class="text-xs text-slate-400 font-normal">(${r.satuan})</span>` : '';
                let unitInline = r.satuan ? ` ${r.satuan}` : '';
                
                let rowHtml = `<tr class="group hover:bg-indigo-50/50 transition-colors" ${hasSub ? `style="cursor: pointer;" onclick="window.toggleSub('sub-${headId}-${idx}')"` : ''}>
                    <td class="px-4 py-3 font-bold text-slate-800 sticky left-0 z-10 bg-white group-hover:bg-indigo-50 shadow-[1px_0_0_#e2e8f0]">
                        <div class="flex items-center">
                            ${hasSub ? `<i class="fas fa-chevron-right text-slate-400 mr-2 transition-transform duration-200" id="icon-sub-${headId}-${idx}"></i>` : ''}
                            ${r[nameKey] ?? '-'}${unitLabel}
                        </div>
                    </td>`;
                
                if (qtyOnLeft) {
                    rowHtml += `<td class="px-4 py-3 text-right font-bold text-xs text-indigo-600 bg-slate-50/50 group-hover:bg-indigo-100/50 border-r border-slate-200 whitespace-nowrap">${formatNumber(r.total_qty)}${unitInline}</td>`;
                }
                rowHtml += `<td class="px-4 py-3 text-right font-bold text-xs text-emerald-600 bg-slate-50/50 group-hover:bg-indigo-100/50 border-r border-slate-200 shadow-[1px_0_0_#e2e8f0] whitespace-nowrap">${formatRupiah(r.total_nett)}</td>`;

                listBulan.forEach(b => {
                    const d = r.bulanan[b];
                    rowHtml += `<td class="px-4 py-3 text-right border-x border-slate-50">
                        <div class="text-[10px] text-slate-400 font-medium whitespace-nowrap">${formatNumber(d.qty)}${unitInline}</div>
                        <div class="text-[11px] font-bold text-slate-700 whitespace-nowrap">${formatRupiah(d.nett)}</div>
                    </td>`;
                });
                
                if (!qtyOnLeft) {
                    rowHtml += `<td class="px-4 py-3 text-right font-bold text-xs text-indigo-600 bg-slate-50/50 group-hover:bg-indigo-100/50 border-l border-slate-200 whitespace-nowrap">${formatNumber(r.total_qty)}${unitInline}</td>`;
                }

                rowHtml += `</tr>`;

                if (hasSub) {
                    r.sub.forEach(subItem => {
                        let subUnitLabel = subItem.satuan ? ` <span class="text-xs text-slate-400 font-normal">(${subItem.satuan})</span>` : '';
                        let subUnitInline = subItem.satuan ? ` ${subItem.satuan}` : '';
                        
                        rowHtml += `<tr class="sub-${headId}-${idx} hidden bg-slate-50 hover:bg-indigo-50/30 transition-colors">
                            <td class="px-4 py-3 pl-8 sticky left-0 z-10 bg-slate-50 shadow-[1px_0_0_#e2e8f0]">
                                <div class="flex items-start gap-2 text-sm text-indigo-700">
                                    <i class="fas fa-level-up-alt rotate-90 text-indigo-300 mt-1 shrink-0"></i>
                                    <span class="font-medium leading-tight">${subItem.nama ?? '-'}${subUnitLabel}</span>
                                </div>
                            </td>`;
                            
                        if (qtyOnLeft) {
                            rowHtml += `<td class="px-4 py-2 text-right font-bold text-xs text-indigo-500 border-r border-white whitespace-nowrap">${formatNumber(subItem.total_qty)}${subUnitInline}</td>`;
                        }
                        rowHtml += `<td class="px-4 py-2 text-right font-bold text-xs text-emerald-500 border-r border-white shadow-[1px_0_0_#e2e8f0] whitespace-nowrap">${formatRupiah(subItem.total_nett)}</td>`;
                        
                        listBulan.forEach(b => {
                            const d = subItem.bulanan[b];
                            rowHtml += `<td class="px-4 py-2 text-right border-x border-white">
                                <div class="text-[10px] text-slate-400 font-medium whitespace-nowrap">${formatNumber(d.qty)}${subUnitInline}</div>
                                <div class="text-[11px] font-bold text-slate-700 whitespace-nowrap">${formatRupiah(d.nett)}</div>
                            </td>`;
                        });
                        
                        if (!qtyOnLeft) {
                            rowHtml += `<td class="px-4 py-2 text-right font-bold text-xs text-indigo-500 border-l border-white whitespace-nowrap">${formatNumber(subItem.total_qty)}${subUnitInline}</td>`;
                        }
                        
                        rowHtml += `</tr>`;
                    });
                }
                
                return rowHtml;
            }).join('');
        }

        function renderPivotTable(headId, bodyId, rows, listPs) {
            const head = document.getElementById(headId);
            const body = document.getElementById(bodyId);
            
            // Build header
            let headHtml = `<tr>
                <th class="px-4 py-3 text-left w-72 min-w-[320px] sticky left-0 z-20 bg-slate-50 shadow-[1px_0_0_#e2e8f0]">Nama Produk</th>
                <th class="px-4 py-3 text-right whitespace-nowrap text-indigo-600 bg-slate-50 border-r border-slate-200 shadow-[1px_0_0_#e2e8f0]">Total All PS</th>`;
            listPs.forEach(p => {
                headHtml += `<th class="px-4 py-3 text-right whitespace-nowrap min-w-[120px] bg-slate-50">${p}</th>`;
            });
            headHtml += `</tr>`;
            head.innerHTML = headHtml;

            // Build body
            if (!rows || rows.length === 0) {
                const colspan = listPs.length + 2;
                body.innerHTML = `<tr><td colspan="${colspan}" class="px-4 py-8 text-center text-slate-400">Tidak ada data</td></tr>`;
                return;
            }

            body.innerHTML = rows.map(r => {
                let unitLabel = r.satuan ? ` <span class="text-xs text-slate-400 font-normal">(${r.satuan})</span>` : '';
                
                let rowHtml = `<tr class="hover:bg-indigo-50/50 transition-colors">
                    <td class="px-4 py-3 font-bold text-slate-800 sticky left-0 z-10 bg-white shadow-[1px_0_0_#e2e8f0]">${r.nama ?? '-'}${unitLabel}</td>
                    <td class="px-4 py-3 text-right font-bold text-xs text-indigo-600 bg-slate-50/50 border-r border-slate-200 shadow-[1px_0_0_#e2e8f0] whitespace-nowrap">${formatRupiah(r.total_nett)}</td>`;
                
                // Map sub items by PS name
                const subMap = {};
                if (r.sub) {
                    r.sub.forEach(s => {
                        subMap[s.nama] = s.total_nett;
                    });
                }
                
                listPs.forEach(p => {
                    const val = subMap[p];
                    const valStr = val ? formatRupiah(val) : '-';
                    rowHtml += `<td class="px-4 py-3 text-right text-xs text-slate-700 whitespace-nowrap border-x border-slate-50">${valStr}</td>`;
                });

                rowHtml += `</tr>`;
                return rowHtml;
            }).join('');
        }

        window.toggleSub = function(className) {
            const rows = document.querySelectorAll('.' + className);
            const icon = document.getElementById('icon-' + className);
            let isHidden = true;
            rows.forEach(r => {
                r.classList.toggle('hidden');
                if (!r.classList.contains('hidden')) isHidden = false;
            });
            if (icon) {
                if (isHidden) {
                    icon.classList.remove('rotate-90');
                } else {
                    icon.classList.add('rotate-90');
                }
            }
        };

        let choicesInstances = [];

        // Initialize Choices.js
        document.querySelectorAll('.choices-multiple').forEach(function(el) {
            const instance = new Choices(el, {
                removeItemButton: true,
                searchEnabled: true,
                placeholderValue: 'Pilih...',
                noChoicesText: 'Tidak ada pilihan',
                noResultsText: 'Tidak ditemukan',
                itemSelectText: 'Pilih'
            });
            choicesInstances.push(instance);
        });

        document.getElementById('btn-apply').addEventListener('click', loadData);
        document.getElementById('btn-reset').addEventListener('click', function () {
            document.getElementById('f-tahun').value = '';
            choicesInstances.forEach(instance => instance.removeActiveItems());
            loadData();
        });

        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(this.dataset.tab).classList.add('active');
            });
        });

        // Initial load
        loadData();
    })();
    </script>
</x-layout-users>
