<x-layout-users title="{{ $title ?? 'Warehouse Analytics' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    @push('styles')
    <style>
        /* Paksa background halaman sepenuhnya dark murni agar tidak ada bayangan/warna lavender yang bocor */
        html, body, #app-shell, #app-main, .ui-tab-pane {
            background-color: #111827 !important;
        }
        body { font-family: 'Outfit', sans-serif; }

        .analytics-bg { background-color: #111827; color: #f8fafc; }
        .glass-panel {
            background: rgba(31, 41, 55, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 20px;
        }

        /* Power BI Filter Slicer */
        .pbi-slicer {
            background: #1e293b url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") no-repeat right 0.75rem center/1.5em 1.5em;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 8px 12px;
            padding-right: 2.5rem;
            font-size: 0.85rem;
            color: #f8fafc;
            font-weight: 600;
            outline: none;
            width: 100%;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            cursor: pointer;
        }
        .pbi-slicer:focus { border-color: #38bdf8; }
        .pbi-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        /* Power BI DOI Cards / Tab Buttons */
        .doi-card-btn {
            border-radius: 14px;
            padding: 16px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            text-align: left;
            width: 100%;
        }
        .doi-card-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.4);
        }
        .doi-card-btn.is-active {
            box-shadow: 0 0 0 2px var(--active-ring, #38bdf8), 0 12px 28px rgba(0, 0, 0, 0.5);
            background-color: var(--active-bg, rgba(30, 41, 59, 0.9)) !important;
        }

        /* Tabel Data Power BI */
        table.pbi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            text-align: right;
            font-size: 0.8rem;
        }
        table.pbi-table th {
            background: #0f172a;
            padding: 10px 12px;
            font-weight: 700;
            color: #38bdf8;
            border-bottom: 2px solid #334155;
            text-transform: uppercase;
            font-size: 0.7rem;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }
        table.pbi-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #334155;
            color: #cbd5e1;
            vertical-align: middle;
        }
        table.pbi-table tr:last-child td { border-bottom: none; }
        table.pbi-table tbody tr:hover td { background: rgba(56, 189, 248, 0.06); }

        .wh-rank {
            width: 24px;
            height: 24px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 11px;
            background: #1e293b;
            color: #94a3b8;
        }
        .wh-rank--hot { background: rgba(244, 63, 94, 0.16); color: #fb7185; }
        .wh-rank--cold { background: rgba(56, 189, 248, 0.16); color: #38bdf8; }
        .wh-product { text-align: left; font-weight: 700; color: #f8fafc; line-height: 1.35; }
        .wh-code { font-size: 10px; color: #64748b; font-weight: 600; margin-top: 1px; }
        .wh-unit { color: #64748b; font-size: 10px; font-weight: 600; margin-left: 2px; }

        .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #334155 transparent; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }

        @media (max-width: 767px) {
            .glass-panel { padding: 14px; border-radius: 14px; }
            table.pbi-table { font-size: 0.72rem; }
            table.pbi-table th, table.pbi-table td { padding: 8px 10px; }
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen analytics-bg relative overflow-x-clip">
        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-6 flex-1 flex flex-col items-stretch justify-start">

            {{-- HEADER & NAVIGATION --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-5 md:p-6 shadow-2xl border border-slate-700 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/20 shrink-0">
                        <i class="fas fa-chart-column text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-black text-white uppercase tracking-wide">Warehouse Analytics &amp; Outflow</h1>
                        <p class="text-slate-400 text-sm mt-1">Pergerakan produk keluar, sebaran 4 kategori DOI dengan rincian nama barang, dan pengajuan SPB.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-sky-950/90 text-sky-400 border border-sky-600/40">
                        <i class="fas fa-calendar-check"></i> {{ $periodeLabel }}
                    </span>
                    <a href="{{ route('warehouse.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs border border-slate-700 transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>

            {{-- FILTER PERIODE (GRID 4 KOLOM — STANDAR SALES ANALYTICS) --}}
            <div class="glass-panel border-t-2 border-t-sky-500">
                <form id="filterFormAnalytics" method="GET" action="{{ route('warehouse.analytics') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="pbi-label">Mode Periode</label>
                        <select name="periode" id="filterPeriode" class="pbi-slicer" onchange="togglePeriodeMode(this.value)">
                            <option value="bulan" {{ $periode === 'bulan' ? 'selected' : '' }}>Per Bulan</option>
                            <option value="tahun" {{ $periode === 'tahun' ? 'selected' : '' }}>Per Tahun (1 Tahun Penuh)</option>
                        </select>
                    </div>
                    <div>
                        <label class="pbi-label">Tahun</label>
                        <select name="tahun" id="filterTahun" class="pbi-slicer" onchange="this.form.submit()">
                            @foreach($listTahun as $t)
                                <option value="{{ $t }}" {{ (int) $tahun === (int) $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="wrapperBulan" class="{{ $periode === 'tahun' ? 'opacity-40 pointer-events-none' : '' }}">
                        <label class="pbi-label">Bulan {{ $periode === 'tahun' ? '(Semua Bulan)' : '' }}</label>
                        <select name="bulan" id="filterBulan" class="pbi-slicer" {{ $periode === 'tahun' ? 'disabled' : '' }} onchange="this.form.submit()">
                            @foreach($listBulan as $num => $label)
                                <option value="{{ $num }}" {{ (int) $bulan === (int) $num ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="w-1/2 py-2 px-4 rounded-lg bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-md shadow-sky-600/30">
                            <i class="fas fa-filter text-[10px]"></i> Terapkan
                        </button>
                        <a href="{{ route('warehouse.analytics') }}" class="w-1/2 text-center py-2 px-4 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs border border-slate-700 transition flex items-center justify-center gap-1.5">
                            <i class="fas fa-undo text-[10px]"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- BAGIAN 1: GRAFIK PRODUK KELUAR (TOP 10 TERBANYAK vs TOP 10 TERSEDIKIT) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Chart Kiri: Top 10 Fast-Moving --}}
                <div class="glass-panel">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-700/60">
                        <div>
                            <h2 class="text-base font-black text-white flex items-center gap-2">
                                <i class="fas fa-fire text-rose-400"></i> Top 10 Produk Keluar Terbanyak (Fast-Moving)
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Produk dengan total kuantitas mutasi keluar terbanyak dari gudang.</p>
                        </div>
                        <span class="text-xs text-rose-400 font-bold bg-rose-950/80 px-2.5 py-1 rounded-full border border-rose-600/40 whitespace-nowrap">
                            {{ $periode === 'tahun' ? 'Tahun ' . $tahun : $bulanLabel }}
                        </span>
                    </div>
                    <div class="relative h-[340px] w-full">
                        @if($topOut->count() > 0)
                            <canvas id="chartTopOut"></canvas>
                        @else
                            <div class="h-full flex items-center justify-center text-slate-500 text-xs italic">
                                Belum ada data mutasi keluar pada periode ini.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Chart Kanan: Under 10 Slow-Moving / Dead Stock --}}
                <div class="glass-panel">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-700/60">
                        <div>
                            <h2 class="text-base font-black text-white flex items-center gap-2">
                                <i class="fas fa-temperature-low text-sky-400"></i> Top 10 Produk Keluar Tersedikit (Slow-Moving)
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Produk pergerakan terendah / minim keluar gudang (potensi tertahan).</p>
                        </div>
                        <span class="text-xs text-sky-400 font-bold bg-sky-950/80 px-2.5 py-1 rounded-full border border-sky-600/40 whitespace-nowrap">
                            {{ $periode === 'tahun' ? 'Tahun ' . $tahun : $bulanLabel }}
                        </span>
                    </div>
                    <div class="relative h-[340px] w-full">
                        @if($leastOut->count() > 0)
                            <canvas id="chartLeastOut"></canvas>
                        @else
                            <div class="h-full flex items-center justify-center text-slate-500 text-xs italic">
                                Belum ada data mutasi keluar pada periode ini.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- BAGIAN 2: TREN PENGELUARAN 12 BULAN & TABEL TOP PENGAJUAN BARANG (SPB) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Kiri: Tren Pengeluaran 12 Bulan (5 Kolom) --}}
                <div class="glass-panel lg:col-span-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-700/60">
                            <div>
                                <h2 class="text-base font-black text-white flex items-center gap-2">
                                    <i class="fas fa-chart-line text-sky-400"></i> Tren Pengeluaran {{ $tahun }}
                                </h2>
                                <p class="text-xs text-slate-400 mt-0.5">Total mutasi barang keluar per bulan.</p>
                            </div>
                            <span class="text-xs font-bold text-sky-400 bg-sky-950/80 px-2.5 py-1 rounded-full border border-sky-600/40 whitespace-nowrap">
                                Total: {{ number_format(array_sum($trend), 0, ',', '.') }} Qty
                            </span>
                        </div>
                        <div class="relative h-[310px] w-full">
                            <canvas id="chartTrend"></canvas>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-700/50 flex items-center justify-between text-xs text-slate-400">
                        <span><i class="fas fa-circle-info mr-1 text-sky-400"></i> Angka tercetak langsung di atas setiap batang</span>
                        <span class="font-bold text-slate-300">Target DOI {{ $activeDoi }} Hari</span>
                    </div>
                </div>

                {{-- Kanan: Top Pengajuan Barang (SPB) by Quantity & DOI (7 Kolom) --}}
                <div class="glass-panel lg:col-span-7 overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-700/60">
                        <div>
                            <h2 class="text-base font-black text-white flex items-center gap-2">
                                <i class="fas fa-file-invoice text-amber-400"></i> Top 10 Pengajuan Barang (SPB) by Quantity &amp; DOI
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">Kuantitas pengajuan barang terbanyak disandingkan dengan DOI &amp; stok gudang riil.</p>
                        </div>
                        <span class="text-xs font-bold text-amber-400 bg-amber-950/80 px-2.5 py-1 rounded-full border border-amber-600/40 whitespace-nowrap">
                            <i class="fas fa-box-open mr-1"></i> {{ number_format($totalSpbQty, 0, ',', '.') }} Unit Diajukan
                        </span>
                    </div>

                    @php
                        $maxSpbQty = $topSpb->max('total_qty') ?: 1;
                    @endphp

                    <div class="max-h-[320px] overflow-y-auto custom-scrollbar overflow-x-auto">
                        <table class="pbi-table whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th class="text-left">Produk</th>
                                    <th>Total Diajukan</th>
                                    <th>Freq</th>
                                    <th>Stok Gudang</th>
                                    <th>DOI</th>
                                    <th class="text-center">Status Evaluasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSpb as $i => $spb)
                                    @php
                                        $barPct = min(100, round(($spb['total_qty'] / $maxSpbQty) * 100));
                                    @endphp
                                    <tr>
                                        <td><span class="wh-rank wh-rank--hot">{{ $i + 1 }}</span></td>
                                        <td>
                                            <div class="wh-product">{{ $spb['nama'] }}</div>
                                            <div class="wh-code">{{ $spb['kode'] }}</div>
                                        </td>
                                        <td>
                                            <div class="font-black text-amber-400 text-sm">
                                                {{ number_format($spb['total_qty'], 0, ',', '.') }}
                                                <span class="wh-unit text-slate-400 font-normal">{{ $spb['satuan'] }}</span>
                                            </div>
                                            <div class="w-full bg-slate-800 rounded-full h-1.5 mt-1 overflow-hidden">
                                                <div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ $barPct }}%;"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="inline-block px-2 py-0.5 rounded-md text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                                {{ $spb['spb_count'] }}x
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-bold text-slate-200">{{ number_format($spb['stok'], 0, ',', '.') }}</span>
                                            <span class="wh-unit">{{ $spb['satuan'] }}</span>
                                        </td>
                                        <td>
                                            @if($spb['doi'] !== null)
                                                <span class="font-black text-xs {{ $spb['doi'] >= 90 ? 'text-rose-400' : ($spb['doi'] < 30 ? 'text-amber-400' : 'text-emerald-400') }}">
                                                    {{ $spb['doi'] }} Hari
                                                </span>
                                            @else
                                                <span class="text-slate-500 text-xs italic">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($spb['status_type'] === 'overstock')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-950/90 text-rose-400 border border-rose-600/40" title="Diajukan banyak padahal stok masih aman untuk >90 hari">
                                                    <i class="fas fa-triangle-exclamation text-[10px]"></i> Overstock Risk
                                                </span>
                                            @elseif($spb['status_type'] === 'urgent')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-950/90 text-emerald-400 border border-emerald-600/40" title="Kebutuhan mendesak: stok menipis / DOI < 30 hari">
                                                    <i class="fas fa-fire text-[10px]"></i> Stok Kritis
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-sky-950/90 text-sky-400 border border-sky-600/40" title="Pengajuan seimbang sesuai perputaran">
                                                    <i class="fas fa-circle-check text-[10px]"></i> Aman / Terkendali
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <div class="py-12 text-center text-slate-500 text-xs">
                                                <i class="fas fa-file-invoice text-2xl text-slate-600 mb-2 block"></i>
                                                Belum ada data pengajuan barang (SPB) pada periode {{ $periodeLabel }}.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- BAGIAN 3: SEBARAN 4 KATEGORI DOI & RINCIAN NAMA BARANG (INTERAKTIF & JELAS) --}}
            <div class="glass-panel" x-data="{ activeDoiTab: 120, search: '' }">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5 pb-3 border-b border-slate-700/60">
                    <div>
                        <h2 class="text-base font-black text-white flex items-center gap-2">
                            <i class="fas fa-layer-group text-amber-400"></i> Sebaran 4 Kategori DOI (Days of Inventory) &amp; Rincian Nama Barang
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Formula: <b>DOI = (Stok Fisik &divide; Rata-Rata Penjualan Bulanan) &times; 30 hari</b>. Klik salah satu kategori kartu di bawah untuk memunculkan daftar nama barangnya.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-300 bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-700">
                            Total: {{ $doiTotalCount }} SKU Terhitung DOI
                        </span>
                    </div>
                </div>

                {{-- 4 KARTU SELECTOR TABS DOI (>30, >60, >90, >120) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
                    {{-- Tab 1: DOI > 30 --}}
                    <button type="button" @click="activeDoiTab = 30"
                        class="doi-card-btn bg-gradient-to-br from-slate-800 to-emerald-950/80 border-l-4 border-l-emerald-400"
                        :class="activeDoiTab === 30 ? 'is-active' : ''"
                        style="--active-ring: #34d399; --active-bg: rgba(6, 78, 59, 0.4);">
                        <div class="flex items-center justify-between">
                            <p class="pbi-label">DOI &gt; 30 Hari (30–59 Hari)</p>
                            <span x-show="activeDoiTab === 30" class="text-xs text-emerald-400 font-extrabold flex items-center gap-1">
                                <i class="fas fa-check-circle text-[11px]"></i> Aktif
                            </span>
                        </div>
                        <h3 class="text-xl md:text-2xl font-black text-emerald-400 mt-1">
                            {{ number_format($doi30Count, 0, ',', '.') }}
                            <span class="text-xs font-normal text-slate-400">SKU</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-1 font-medium">
                            {{ number_format(($doi30Count / $doiTotalCount) * 100, 1) }}% katalog &middot; <span class="text-emerald-400 font-bold">Perputaran Normal</span>
                        </p>
                        <div class="w-full bg-slate-800 rounded-full h-1.5 mt-2.5 overflow-hidden">
                            <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ min(100, ($doi30Count / $doiTotalCount) * 100) }}%"></div>
                        </div>
                    </button>

                    {{-- Tab 2: DOI > 60 --}}
                    <button type="button" @click="activeDoiTab = 60"
                        class="doi-card-btn bg-gradient-to-br from-slate-800 to-sky-950/80 border-l-4 border-l-sky-400"
                        :class="activeDoiTab === 60 ? 'is-active' : ''"
                        style="--active-ring: #38bdf8; --active-bg: rgba(12, 74, 110, 0.4);">
                        <div class="flex items-center justify-between">
                            <p class="pbi-label">DOI &gt; 60 Hari (60–89 Hari)</p>
                            <span x-show="activeDoiTab === 60" class="text-xs text-sky-400 font-extrabold flex items-center gap-1">
                                <i class="fas fa-check-circle text-[11px]"></i> Aktif
                            </span>
                        </div>
                        <h3 class="text-xl md:text-2xl font-black text-sky-400 mt-1">
                            {{ number_format($doi60Count, 0, ',', '.') }}
                            <span class="text-xs font-normal text-slate-400">SKU</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-1 font-medium">
                            {{ number_format(($doi60Count / $doiTotalCount) * 100, 1) }}% katalog &middot; <span class="text-sky-400 font-bold">Stok Cukup (2-3 Bln)</span>
                        </p>
                        <div class="w-full bg-slate-800 rounded-full h-1.5 mt-2.5 overflow-hidden">
                            <div class="bg-sky-400 h-1.5 rounded-full" style="width: {{ min(100, ($doi60Count / $doiTotalCount) * 100) }}%"></div>
                        </div>
                    </button>

                    {{-- Tab 3: DOI > 90 --}}
                    <button type="button" @click="activeDoiTab = 90"
                        class="doi-card-btn bg-gradient-to-br from-slate-800 to-amber-950/80 border-l-4 border-l-amber-400"
                        :class="activeDoiTab === 90 ? 'is-active' : ''"
                        style="--active-ring: #fbbf24; --active-bg: rgba(120, 53, 15, 0.4);">
                        <div class="flex items-center justify-between">
                            <p class="pbi-label">DOI &gt; 90 Hari (90–119 Hari)</p>
                            <span x-show="activeDoiTab === 90" class="text-xs text-amber-400 font-extrabold flex items-center gap-1">
                                <i class="fas fa-check-circle text-[11px]"></i> Aktif
                            </span>
                        </div>
                        <h3 class="text-xl md:text-2xl font-black text-amber-400 mt-1">
                            {{ number_format($doi90Count, 0, ',', '.') }}
                            <span class="text-xs font-normal text-slate-400">SKU</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-1 font-medium">
                            {{ number_format(($doi90Count / $doiTotalCount) * 100, 1) }}% katalog &middot; <span class="text-amber-400 font-bold">Buffer Tinggi / Melambat</span>
                        </p>
                        <div class="w-full bg-slate-800 rounded-full h-1.5 mt-2.5 overflow-hidden">
                            <div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ min(100, ($doi90Count / $doiTotalCount) * 100) }}%"></div>
                        </div>
                    </button>

                    {{-- Tab 4: DOI > 120 --}}
                    <button type="button" @click="activeDoiTab = 120"
                        class="doi-card-btn bg-gradient-to-br from-slate-800 to-rose-950/80 border-l-4 border-l-rose-500"
                        :class="activeDoiTab === 120 ? 'is-active' : ''"
                        style="--active-ring: #f43f5e; --active-bg: rgba(136, 19, 55, 0.4);">
                        <div class="flex items-center justify-between">
                            <p class="pbi-label">DOI &gt; 120 Hari (&ge; 120 Hari)</p>
                            <span x-show="activeDoiTab === 120" class="text-xs text-rose-400 font-extrabold flex items-center gap-1">
                                <i class="fas fa-check-circle text-[11px]"></i> Aktif
                            </span>
                        </div>
                        <h3 class="text-xl md:text-2xl font-black text-rose-400 mt-1">
                            {{ number_format($doi120Count, 0, ',', '.') }}
                            <span class="text-xs font-normal text-slate-400">SKU</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-1 font-medium">
                            {{ number_format(($doi120Count / $doiTotalCount) * 100, 1) }}% katalog &middot; <span class="text-rose-400 font-bold">Overstock / Tertahan</span>
                        </p>
                        <div class="w-full bg-slate-800 rounded-full h-1.5 mt-2.5 overflow-hidden">
                            <div class="bg-rose-500 h-1.5 rounded-full" style="width: {{ min(100, ($doi120Count / $doiTotalCount) * 100) }}%"></div>
                        </div>
                    </button>
                </div>

                {{-- TOOLBAR DAFTAR BARANG: JUDUL TAB AKTIF + PENCARIAN CEPAT --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black uppercase text-slate-300">
                            Daftar Barang Kategori:
                        </span>
                        <span x-show="activeDoiTab === 30" class="text-xs font-bold text-emerald-400 bg-emerald-950/80 px-2.5 py-0.5 rounded-md border border-emerald-600/40">
                            DOI &gt; 30 Hari ({{ $doi30Count }} Barang)
                        </span>
                        <span x-show="activeDoiTab === 60" class="text-xs font-bold text-sky-400 bg-sky-950/80 px-2.5 py-0.5 rounded-md border border-sky-600/40">
                            DOI &gt; 60 Hari ({{ $doi60Count }} Barang)
                        </span>
                        <span x-show="activeDoiTab === 90" class="text-xs font-bold text-amber-400 bg-amber-950/80 px-2.5 py-0.5 rounded-md border border-amber-600/40">
                            DOI &gt; 90 Hari ({{ $doi90Count }} Barang)
                        </span>
                        <span x-show="activeDoiTab === 120" class="text-xs font-bold text-rose-400 bg-rose-950/80 px-2.5 py-0.5 rounded-md border border-rose-600/40">
                            DOI &gt; 120 Hari ({{ $doi120Count }} Barang Overstock)
                        </span>
                    </div>

                    {{-- Search Input Realtime --}}
                    <div class="relative w-full sm:w-72">
                        <input type="text" x-model="search" placeholder="Cari nama barang di kategori ini..."
                            class="pbi-slicer !py-1.5 !pl-8 text-xs placeholder-slate-500">
                        <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    </div>
                </div>

                {{-- 4 TABEL RINCIAN NAMA BARANG BERDASARKAN KATEGORI --}}
                @foreach([
                    30 => ['badge' => 'bg-emerald-950/90 text-emerald-400 border-emerald-600/40', 'status' => 'Perputaran Normal', 'color' => 'text-emerald-400'],
                    60 => ['badge' => 'bg-sky-950/90 text-sky-400 border-sky-600/40', 'status' => 'Stok Cukup', 'color' => 'text-sky-400'],
                    90 => ['badge' => 'bg-amber-950/90 text-amber-400 border-amber-600/40', 'status' => 'Buffer Tinggi', 'color' => 'text-amber-400'],
                    120 => ['badge' => 'bg-rose-950/90 text-rose-400 border-rose-600/40', 'status' => 'Overstock Risk', 'color' => 'text-rose-400']
                ] as $threshold => $cfg)
                    <div x-show="activeDoiTab === {{ $threshold }}" class="max-h-[380px] overflow-y-auto custom-scrollbar overflow-x-auto border border-slate-700/60 rounded-xl">
                        <table class="pbi-table whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th style="width:38px;">#</th>
                                    <th class="text-left">Nama Produk</th>
                                    <th>Stok Fisik Gudang</th>
                                    <th>Rata-Rata Penjualan (Avg/Bln)</th>
                                    <th>Estimasi DOI</th>
                                    <th class="text-center">Status Evaluasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($doiBuckets[$threshold] as $idx => $item)
                                    <tr x-show="!search || '{{ strtolower(addslashes($item['nama'])) }}'.includes(search.toLowerCase())">
                                        <td><span class="wh-rank">{{ $idx + 1 }}</span></td>
                                        <td>
                                            <div class="wh-product">{{ $item['nama'] }}</div>
                                            <div class="wh-code">{{ $item['kode'] }}</div>
                                        </td>
                                        <td>
                                            <span class="font-bold text-slate-200 text-sm">{{ number_format($item['stok'], 0, ',', '.') }}</span>
                                            <span class="wh-unit">{{ $item['satuan'] }}</span>
                                        </td>
                                        <td>
                                            <span class="font-bold text-slate-300">{{ number_format($item['avg'], 2, ',', '.') }}</span>
                                            <span class="wh-unit">/bln</span>
                                        </td>
                                        <td>
                                            <span class="font-black text-sm {{ $cfg['color'] }}">
                                                {{ number_format($item['doi'], 0, ',', '.') }} Hari
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $cfg['badge'] }}">
                                                <i class="fas {{ $threshold >= 120 ? 'fa-triangle-exclamation' : ($threshold >= 90 ? 'fa-clock' : 'fa-check') }} text-[10px]"></i>
                                                {{ $cfg['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="py-12 text-center text-slate-500 text-xs italic">
                                                Tidak ada produk pada kategori DOI ini.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function togglePeriodeMode(mode) {
            const bulanWrap = document.getElementById('wrapperBulan');
            const bulanSelect = document.getElementById('filterBulan');
            if (mode === 'tahun') {
                bulanSelect.disabled = true;
                bulanWrap.classList.add('opacity-40', 'pointer-events-none');
            } else {
                bulanSelect.disabled = false;
                bulanWrap.classList.remove('opacity-40', 'pointer-events-none');
            }
            document.getElementById('filterFormAnalytics').submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Registrasi plugin ChartDataLabels persis Sales Analytics
            if (typeof ChartDataLabels !== 'undefined') {
                Chart.register(ChartDataLabels);
                Chart.defaults.plugins.datalabels = { display: false };
            }

            const gridColor = 'rgba(148, 163, 184, 0.08)';
            const tickColor = '#94a3b8';

            // 1. DATA CHART TOP 10 FAST-MOVING (DENGAN ANGKA DI SETIAP BATANG)
            const topOutRaw = @json($topOut);
            const topOutLabels = topOutRaw.map(item => item.nama.length > 22 ? item.nama.substring(0, 22) + '...' : item.nama);
            const topOutFullNames = topOutRaw.map(item => item.nama);
            const topOutData = topOutRaw.map(item => item.total_out);
            const topOutUnits = topOutRaw.map(item => item.satuan);
            const topOutStocks = topOutRaw.map(item => item.stok);

            const chartTopEl = document.getElementById('chartTopOut');
            if (chartTopEl && topOutData.length > 0) {
                new Chart(chartTopEl, {
                    type: 'bar',
                    data: {
                        labels: topOutLabels,
                        datasets: [{
                            label: 'Total Keluar',
                            data: topOutData,
                            borderRadius: 6,
                            backgroundColor: 'rgba(56, 189, 248, 0.85)',
                            hoverBackgroundColor: '#38bdf8',
                            borderWidth: 0,
                            maxBarThickness: 22,
                            datalabels: {
                                display: true,
                                anchor: 'end',
                                align: 'right',
                                offset: 6,
                                color: '#ffffff',
                                font: { weight: 'bold', size: 11 },
                                formatter: (val) => Number(val).toLocaleString('id-ID')
                            }
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: { right: 42 }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                borderColor: '#334155',
                                borderWidth: 1,
                                titleColor: '#f8fafc',
                                bodyColor: '#cbd5e1',
                                padding: 10,
                                callbacks: {
                                    title: (items) => topOutFullNames[items[0].dataIndex] || items[0].label,
                                    label: (ctx) => {
                                        const idx = ctx.dataIndex;
                                        return [
                                            ` Total Keluar: ${Number(ctx.raw).toLocaleString('id-ID')} ${topOutUnits[idx]}`,
                                            ` Sisa Stok: ${Number(topOutStocks[idx]).toLocaleString('id-ID')} ${topOutUnits[idx]}`
                                        ];
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grace: '12%',
                                grid: { color: gridColor },
                                ticks: {
                                    color: tickColor,
                                    font: { size: 10 },
                                    callback: (val) => Number(val).toLocaleString('id-ID')
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    color: '#e2e8f0',
                                    font: { size: 11, weight: '600' }
                                }
                            }
                        }
                    }
                });
            }

            // 2. DATA CHART UNDER 10 SLOW-MOVING (DENGAN ANGKA DI SETIAP BATANG)
            const leastOutRaw = @json($leastOut);
            const leastOutLabels = leastOutRaw.map(item => item.nama.length > 22 ? item.nama.substring(0, 22) + '...' : item.nama);
            const leastOutFullNames = leastOutRaw.map(item => item.nama);
            const leastOutData = leastOutRaw.map(item => item.total_out);
            const leastOutUnits = leastOutRaw.map(item => item.satuan);
            const leastOutStocks = leastOutRaw.map(item => item.stok);

            const chartLeastEl = document.getElementById('chartLeastOut');
            if (chartLeastEl && leastOutData.length > 0) {
                new Chart(chartLeastEl, {
                    type: 'bar',
                    data: {
                        labels: leastOutLabels,
                        datasets: [{
                            label: 'Total Keluar',
                            data: leastOutData,
                            borderRadius: 6,
                            backgroundColor: 'rgba(251, 113, 133, 0.85)',
                            hoverBackgroundColor: '#fb7185',
                            borderWidth: 0,
                            maxBarThickness: 22,
                            datalabels: {
                                display: true,
                                anchor: 'end',
                                align: 'right',
                                offset: 6,
                                color: '#ffffff',
                                font: { weight: 'bold', size: 11 },
                                formatter: (val) => Number(val).toLocaleString('id-ID')
                            }
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: { right: 42 }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                borderColor: '#334155',
                                borderWidth: 1,
                                titleColor: '#f8fafc',
                                bodyColor: '#cbd5e1',
                                padding: 10,
                                callbacks: {
                                    title: (items) => leastOutFullNames[items[0].dataIndex] || items[0].label,
                                    label: (ctx) => {
                                        const idx = ctx.dataIndex;
                                        return [
                                            ` Total Keluar: ${Number(ctx.raw).toLocaleString('id-ID')} ${leastOutUnits[idx]}`,
                                            ` Sisa Stok: ${Number(leastOutStocks[idx]).toLocaleString('id-ID')} ${leastOutUnits[idx]}`
                                        ];
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grace: '12%',
                                grid: { color: gridColor },
                                ticks: {
                                    color: tickColor,
                                    font: { size: 10 },
                                    callback: (val) => Number(val).toLocaleString('id-ID')
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    color: '#e2e8f0',
                                    font: { size: 11, weight: '600' }
                                }
                            }
                        }
                    }
                });
            }

            // 3. DATA CHART TREN 12 BULAN (DENGAN ANGKA DI ATAS SETIAP BATANG)
            const monthLabels = @json(array_values($listBulan));
            const trendData = @json(array_values($trend));
            const selectedMonth = {{ (int) $bulan }};
            const currentMode = '{{ $periode }}';

            const trendEl = document.getElementById('chartTrend');
            if (trendEl) {
                new Chart(trendEl, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                        datasets: [{
                            label: 'Qty Keluar',
                            data: trendData,
                            borderRadius: 6,
                            maxBarThickness: 26,
                            backgroundColor: monthLabels.map((_, i) => (currentMode === 'bulan' && (i + 1) === selectedMonth)
                                ? 'rgba(56, 189, 248, 0.95)'
                                : 'rgba(56, 189, 248, 0.35)'),
                            borderColor: monthLabels.map((_, i) => (currentMode === 'bulan' && (i + 1) === selectedMonth)
                                ? '#38bdf8'
                                : 'transparent'),
                            borderWidth: 1,
                            datalabels: {
                                display: true,
                                anchor: 'end',
                                align: 'top',
                                offset: 4,
                                color: (ctx) => {
                                    const isSelected = (currentMode === 'bulan' && (ctx.dataIndex + 1) === selectedMonth);
                                    return isSelected ? '#38bdf8' : '#cbd5e1';
                                },
                                font: { weight: 'bold', size: 10 },
                                formatter: (val) => val > 0 ? Number(val).toLocaleString('id-ID') : ''
                            }
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: { top: 26 }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                borderColor: '#334155',
                                borderWidth: 1,
                                titleColor: '#f8fafc',
                                bodyColor: '#cbd5e1',
                                callbacks: {
                                    label: (ctx) => ' ' + Number(ctx.raw).toLocaleString('id-ID') + ' unit keluar',
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: tickColor, font: { size: 10, weight: '700' } },
                            },
                            y: {
                                beginAtZero: true,
                                grace: '15%',
                                grid: { color: gridColor },
                                ticks: {
                                    color: tickColor,
                                    font: { size: 10 },
                                    callback: (val) => Number(val).toLocaleString('id-ID'),
                                },
                            },
                        },
                    },
                });
            }
        });
    </script>
    @endpush
</x-layout-users>
