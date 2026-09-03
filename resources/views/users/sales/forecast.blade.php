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
        body { background-color: #f8fafc; }

        .mesh-bg { 
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.6) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
            pointer-events: none;
        }

        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1rem; padding: 1.25rem 1.75rem; color: white;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3); position: relative; overflow: hidden;
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
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b; font-size: 0.9rem; font-weight: 700;
            transition: all 0.2s ease; width: fit-content;
        }
        .btn-back-modern:hover { 
            background: #fff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1); color: #1e40af;
        }
        .btn-back-modern .icon-circle {
            width: 32px; height: 32px; background: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; margin: 0; 
        }
        input[type=number] { -moz-appearance: textfield; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16" x-data="{ showForecastModal: false }">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-6 flex flex-col gap-2.5">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-1">
                <a href="{{ $backRoute }}" class="btn-back-modern shrink-0">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    {{ $backText }}
                </a>
            </div>

            <div class="hidden md:block page-header mb-1">
                <div class="header-content flex flex-row items-center justify-between gap-6">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-white">Sales Forecast & Stock Estimation</h1>
                        <p class="text-blue-100 text-sm mt-1.5">
                            Estimating future stock requirements based on the average sales performance from the last 3 months (<span class="font-bold">@foreach($tigaBulanTerakhir as $index => $b){{ $monthTranslations[$b] ?? $b }}{{ !$loop->last ? ', ' : '' }}@endforeach</span>).
                        </p>
                    </div>
                    <div>
                        <i class="fas fa-chart-area text-4xl opacity-20"></i>
                    </div>
                </div>
            </div>

            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 mb-1 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Product Forecast</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">
                        Est. stock based on last 3 months.
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
                                <i class="fas fa-sliders-h"></i> Set Persentase (+{{ $activePercentage }}%)
                            </button>
                        @endif
                        <button type="button" onclick="alert('Export to Excel feature is coming soon!')" class="flex-1 md:flex-none justify-center inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors cursor-pointer whitespace-nowrap">
                            <i class="fas fa-file-excel"></i> Export
                        </button>
                    </div>
                </div>

                <div id="dataContainer">
                    @if(count($stockForecast) > 0)
                    
                    {{-- 1. TAMPILAN DESKTOP --}}
                    <div class="hidden md:block rounded-xl border border-slate-200 overflow-hidden bg-white">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                                    <th class="py-3.5 px-4 text-slate-500 text-left w-1/4">Product Name & Contributor</th>
                                    @foreach($tigaBulanTerakhir as $bulan)
                                        <th class="py-3.5 px-3 text-center border-l border-slate-100 text-blue-600">{{ strtoupper(substr($monthTranslations[$bulan] ?? $bulan, 0, 3)) }}</th>
                                    @endforeach
                                    <th class="py-3.5 px-3 text-center border-l border-slate-100 text-slate-600">Total</th>
                                    <th class="py-3.5 px-3 text-center border-l border-slate-100 text-blue-600 bg-blue-50/60">Average</th>
                                    <th class="py-3.5 px-3 text-center border-l border-slate-100 text-emerald-600 bg-emerald-50/60">Forecast (+{{ $activePercentage }}%)</th>
                                    <th class="py-3.5 px-3 text-center border-l border-slate-100 text-orange-600 leading-tight bg-orange-50/60">Available Stock<br><span class="text-[8px] font-medium opacity-80">(As of {{ $teksStokAkhir }})</span></th>
                                    <th class="py-3.5 px-4 text-center border-l border-slate-100 text-indigo-700 leading-tight">Suggested Order<br><span class="text-[8px] font-medium opacity-80">(Manual Input)</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($stockForecast as $item)
                                    <tr class="searchable-item hover:bg-slate-50/70 transition-colors text-xs text-slate-700" data-product="{{ $item['nama_produk'] }}" data-forecast="{{ $item['forecast_qty'] }}">
                                        <td class="py-3.5 px-4 text-left">
                                            <div class="font-bold text-slate-800 text-sm mb-1.5 whitespace-normal nama-produk leading-snug">
                                                {{ $item['nama_produk'] }}
                                            </div>
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                @foreach($item['ps_breakdown'] as $ps => $qty)
                                                    <div class="inline-flex items-center gap-1 bg-white border border-slate-200/70 rounded-lg px-2 py-0.5">
                                                        <span class="font-medium text-slate-500 text-[10px]">{{ $ps }}</span>
                                                        <span class="font-bold text-blue-600 text-[11px]">{{ number_format($qty, 0, ',', '.') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        
                                        @foreach($item['detail_bulan'] as $qty)
                                            <td class="py-3.5 px-3 text-center border-l border-slate-100 font-bold align-middle">
                                                {{ number_format($qty, 0, ',', '.') }}
                                            </td>
                                        @endforeach
                                        
                                        <td class="py-3.5 px-3 text-center border-l border-slate-100 bg-slate-50/30 align-middle">
                                            <div class="font-bold text-slate-700 text-sm">{{ number_format($item['total_qty'], 0, ',', '.') }}</div>
                                            <div class="text-[9px] font-medium text-slate-400 mt-0.5">{{ $item['satuan_sales'] }}</div>
                                        </td>

                                        <td class="py-3.5 px-3 text-center border-l border-slate-100 bg-blue-50/60 align-middle">
                                            <div class="font-bold text-blue-600 text-sm">{{ number_format($item['avg_qty'], 2, ',', '.') }}</div>
                                            <div class="text-[9px] font-medium text-blue-400 mt-0.5">{{ $item['satuan_sales'] }}</div>
                                        </td>
                                        
                                        <td class="py-3.5 px-3 text-center border-l border-slate-100 bg-emerald-50/60 align-middle">
                                            <div class="font-black text-emerald-600 text-sm">{{ number_format($item['forecast_qty'], 0, ',', '.') }}</div>
                                            <div class="text-[9px] font-medium text-emerald-500 mt-0.5">{{ $item['satuan_sales'] }}</div>
                                        </td>

                                        <td class="py-3.5 px-3 text-center border-l border-slate-100 bg-orange-50/60 align-middle">
                                            <div class="font-bold text-orange-600 text-sm">{{ number_format($item['stok_tersedia'], 0, ',', '.') }}</div>
                                            <div class="text-[9px] font-medium text-orange-400 mt-0.5">{{ $item['satuan_stok'] }}</div>
                                        </td>

                                        <td class="py-3.5 px-4 text-center border-l border-slate-100 align-middle">
                                            <div class="flex items-center justify-center relative w-[120px] mx-auto">
                                                <input type="text" inputmode="numeric" placeholder="0" 
                                                    value="{{ $item['suggested_order'] !== '' ? number_format($item['suggested_order'], 0, ',', '.') : '' }}" 
                                                    @if($isRestrictedViewOnly) disabled @else oninput="formatNumberInput(this)" @endif 
                                                    class="relative w-full px-2 py-1.5 text-center font-black text-indigo-700 bg-white border-2 border-indigo-100 rounded-xl focus:ring-0 focus:border-indigo-400 outline-none shadow-sm text-sm hover:border-indigo-300 transition-colors @if($isRestrictedViewOnly) opacity-70 cursor-not-allowed bg-slate-100 @endif">
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 2. TAMPILAN MOBILE --}}
                    <div class="block md:hidden flex flex-col gap-3">
                        @foreach($stockForecast as $item)
                        <div class="searchable-item rounded-2xl shadow-md border-t-4 border-t-blue-500 overflow-hidden" style="background: linear-gradient(180deg, #eff6ff 0%, #ffffff 18%);" data-product="{{ $item['nama_produk'] }}" data-forecast="{{ $item['forecast_qty'] }}">
                            
                            <div class="px-3.5 pt-3 pb-2 flex flex-col gap-1.5 border-b border-slate-100">
                                <h4 class="font-extrabold text-slate-800 text-[13px] leading-snug nama-produk break-words">{{ $item['nama_produk'] }}</h4>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach($item['ps_breakdown'] as $ps => $qty)
                                        <div class="inline-flex items-center gap-1 bg-slate-50 border border-slate-200/70 rounded-lg px-1.5 py-0.5 max-w-full">
                                            <span class="font-medium text-slate-500 text-[9px] truncate">{{ $ps }}</span>
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

                            <div class="grid grid-cols-3 gap-2 px-3.5 py-2.5 bg-white items-end">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[8px] font-bold text-emerald-600 uppercase tracking-wider mb-0.5 truncate">Forecast</span>
                                    <span class="text-xs font-black text-emerald-600 leading-tight truncate">
                                        {{ number_format($item['forecast_qty'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-[8px] font-semibold text-emerald-500 truncate">{{ $item['satuan_sales'] }}</span>
                                </div>
                                <div class="flex flex-col min-w-0 border-l border-slate-200 pl-2">
                                    <span class="text-[8px] font-bold text-orange-600 uppercase tracking-wider mb-0.5 truncate">Stock</span>
                                    <span class="text-xs font-black text-orange-600 leading-tight truncate">
                                        {{ number_format($item['stok_tersedia'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-[8px] font-semibold text-orange-500 truncate">{{ $item['satuan_stok'] }}</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[8px] font-bold text-indigo-600 uppercase leading-tight mb-0.5 text-right">Suggested<br>Order</div>
                                    <input type="text" inputmode="numeric" placeholder="0" 
                                        value="{{ $item['suggested_order'] !== '' ? number_format($item['suggested_order'], 0, ',', '.') : '' }}" 
                                        @if($isRestrictedViewOnly) disabled @else oninput="formatNumberInput(this)" @endif 
                                        class="w-full min-w-0 px-1.5 py-1.5 text-center font-black text-indigo-700 bg-white border-2 border-indigo-100 rounded-xl focus:ring-0 focus:border-indigo-400 outline-none shadow-sm text-xs hover:border-indigo-300 transition-colors @if($isRestrictedViewOnly) opacity-70 cursor-not-allowed bg-slate-100 @endif">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @endif
                </div>

                <div id="searchEmptyState" class="py-12 flex-col items-center justify-center text-center text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200 hidden mt-4">
                    <i class="fas fa-box-open text-4xl mb-3 text-slate-300"></i>
                    <h4 class="font-bold text-base text-slate-600">No Data Found</h4>
                    <p class="text-xs mt-1" id="searchEmptyText">Produk tidak ditemukan.</p>
                </div>

                @if(count($stockForecast) == 0)
                <div class="py-12 flex flex-col items-center justify-center text-center text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200 mt-4">
                    <i class="fas fa-box-open text-4xl mb-3 text-slate-300"></i>
                    <h4 class="font-bold text-base text-slate-600">No Data Found</h4>
                    <p class="text-xs mt-1 max-w-sm px-4 mx-auto">Belum ada data produk atau penjualan yang tersedia untuk rentang bulan dan tahun yang dipilih.</p>
                </div>
                @endif
            </div>
        </div>

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
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">Pengaturan ini akan mengubah persentase penambahan forecast untuk periode bulan acuan <b>{{ $bulanAktif }} {{ $tahun }}</b>.</p>
                    
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan_acuan" value="{{ $bulanAktif }}">
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Persentase Penambahan (%)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="percentage" value="{{ $activePercentage }}" required class="w-full px-3 py-2 pr-8 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-slate-700 outline-none text-sm bg-slate-50 hover:bg-white transition-colors">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 text-xs font-bold pointer-events-none">%</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showForecastModal = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-500/20">
                            Simpan
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
                    emptyText.innerHTML = `Produk dengan kata kunci "<span class="font-bold">${document.getElementById("searchInput").value}</span>" tidak ditemukan.`;
                }
            } else {
                if (dataContainer) dataContainer.style.display = "block";
                if (emptyStateDiv) {
                    emptyStateDiv.classList.remove("flex");
                    emptyStateDiv.classList.add("hidden");
                }
            }
        }

        function clearSearch() {
            let input = document.getElementById("searchInput");
            input.value = "";
            handleSearchInput();
            input.focus();
        }

        function formatNumberInput(input) {
            let value = input.value.replace(/\D/g, "");
            let rawValue = value;
            if (value !== "") {
                value = Number(value).toLocaleString("id-ID");
            }
            input.value = value;

            clearTimeout(input.saveTimer);
            input.saveTimer = setTimeout(() => {
                saveSuggestedOrder(input, rawValue);
            }, 600);
        }

        function saveSuggestedOrder(inputElement, rawValue) {
            let container = inputElement.closest('.searchable-item');
            if(!container) return;

            let productName = container.getAttribute('data-product');
            let forecastQty = container.getAttribute('data-forecast');
            let tahun = "{{ $tahun }}";
            let bulanAcuan = "{{ $bulanAktif }}";

            inputElement.classList.add("border-amber-400");

            fetch("{{ route('sales.forecast.store-order') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    tahun: tahun,
                    bulan_acuan: bulanAcuan,
                    nama_produk: productName,
                    forecast_qty: forecastQty,
                    suggested_qty: rawValue
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    inputElement.classList.remove("border-amber-400");
                    inputElement.classList.add("border-emerald-500");
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