@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="Dashboard Pencapaian (Power BI)">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @push('styles')
    <style>
        .mesh-bg { background-color: #f8fafc; }
        .glass-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 24px; margin-bottom: 24px; border: 1px solid #e2e8f0; overflow-x: auto;}
        
        table.matrix-table { w-full border-collapse border border-slate-200 text-sm; }
        table.matrix-table th { background: #f1f5f9; padding: 10px; font-weight: 700; border: 1px solid #e2e8f0; color: #334155; text-transform: uppercase; font-size: 0.75rem; text-align: center;}
        table.matrix-table td { padding: 10px; border: 1px solid #e2e8f0; color: #475569; text-align: right; }
        table.matrix-table td.row-header { text-align: left; font-weight: 700; background: #f8fafc; width: 250px;}
        table.matrix-table tr:hover td { background: #f8fafc; }
        
        .val-good { color: #10b981; font-weight: bold; }
        .val-bad { color: #ef4444; font-weight: bold; }

        .modern-input { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 0.875rem;}
        .modern-input:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 2px rgba(99,102,241,0.2); }
    </style>
    @endpush

    <div class="p-6 max-w-7xl mx-auto" x-data="{ showModal: false }">
        {{-- Header & Controls --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800">Dashboard Pencapaian</h1>
                <p class="text-slate-500 font-medium">Monitoring Achievement Rate dan Target Sales (Tahun {{ $tahun }})</p>
            </div>
            
            <div class="flex flex-wrap gap-3 w-full md:w-auto items-center">
                <a href="{{ route('sales.visualisasi') }}" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg shadow transition flex items-center text-sm">
                    <i class="fas fa-chart-pie mr-2"></i> Power BI Visualisasi
                </a>
                
                {{-- Year Filter Form --}}
                <form action="{{ route('sales.target') }}" method="GET" class="flex gap-2">
                    <select name="tahun" class="modern-input !w-32" onchange="this.form.submit()">
                        @foreach($listTahun as $t)
                            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>Tahun {{ $t }}</option>
                        @endforeach
                    </select>
                </form>

                <button @click="showModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition flex items-center text-sm">
                    <i class="fas fa-bullseye mr-2"></i> Set Target
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Tabel 1: Monthly Achievement Rate --}}
        <div class="glass-card">
            <h2 class="text-lg font-black text-slate-800 mb-4 border-b pb-2"><i class="fas fa-table text-indigo-500 mr-2"></i> Monthly Achievement Rate</h2>
            <div class="overflow-x-auto">
                <table class="w-full matrix-table whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="text-left">Unit: IDR</th>
                            @foreach($urutanBulan as $bulan)
                                <th>{{ $bulan }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="row-header">Target</td>
                            @foreach($urutanBulan as $bulan)
                                <td>{{ number_format($monthlyAll[$bulan]['target'], 0, ',', '.') }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="row-header">Sales</td>
                            @foreach($urutanBulan as $bulan)
                                <td>{{ number_format($monthlyAll[$bulan]['sales'], 0, ',', '.') }}</td>
                            @endforeach
                        </tr>
                        <tr class="bg-indigo-50/30">
                            <td class="row-header">Achievement Rate</td>
                            @foreach($urutanBulan as $bulan)
                                @php $rate = $monthlyAll[$bulan]['achievement_rate']; @endphp
                                <td class="{{ $rate >= 100 ? 'val-good' : ($rate > 0 ? 'text-indigo-600 font-bold' : 'text-slate-400') }}">
                                    {{ $rate }}%
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="row-header">Growth Rate Compared to Last Year</td>
                            @foreach($urutanBulan as $bulan)
                                @php $growth = $monthlyAll[$bulan]['growth_rate']; @endphp
                                <td class="{{ $growth > 0 ? 'val-good' : ($growth < 0 ? 'val-bad' : 'text-slate-400') }}">
                                    {{ $growth > 0 ? '+'.$growth : $growth }}%
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="row-header">{{ $tahunLalu }} Sales (Last Year)</td>
                            @foreach($urutanBulan as $bulan)
                                <td>{{ number_format($monthlyAll[$bulan]['sales_last_year'], 0, ',', '.') }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabel 2: Monthly Achievement Sales per PS Rate --}}
        <div class="glass-card">
            <h2 class="text-lg font-black text-slate-800 mb-4 border-b pb-2"><i class="fas fa-users text-indigo-500 mr-2"></i> Monthly Achievement Sales per PS Rate</h2>
            <div class="overflow-x-auto">
                <table class="w-full matrix-table whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="text-left">Bulan</th>
                            <th>All</th>
                            @foreach($listPs as $ps)
                                <th>{{ $ps }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($urutanBulan as $bulan)
                        <tr>
                            <td class="row-header">{{ $bulan }}</td>
                            
                            @php $rateAll = $monthlyPerPs[$bulan]['All']; @endphp
                            <td class="{{ $rateAll >= 100 ? 'val-good' : 'text-slate-700 font-bold' }} bg-slate-50">{{ $rateAll }}%</td>
                            
                            @foreach($listPs as $ps)
                                @php $ratePs = $monthlyPerPs[$bulan][$ps] ?? 0; @endphp
                                <td class="{{ $ratePs >= 100 ? 'val-good' : ($ratePs > 0 ? 'text-indigo-600' : 'text-slate-400') }}">
                                    {{ $ratePs }}%
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabel 3: All achievement sales & target per PS --}}
        <div class="glass-card">
            <h2 class="text-lg font-black text-slate-800 mb-4 border-b pb-2"><i class="fas fa-bullseye text-indigo-500 mr-2"></i> All Achievement Sales & Target per PS (Tahun {{ $tahun }})</h2>
            <div class="overflow-x-auto">
                <table class="w-full matrix-table whitespace-nowrap" style="max-width: 800px;">
                    <thead>
                        <tr>
                            <th class="text-left">Product Specialist (PS)</th>
                            <th class="text-right">Total Target</th>
                            <th class="text-right">Total Sales</th>
                            <th class="text-center">Achievement Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allPsAchievement as $ps => $data)
                        <tr>
                            <td class="row-header">{{ $ps }}</td>
                            <td>{{ number_format($data['target'], 0, ',', '.') }}</td>
                            <td class="font-bold text-slate-700">{{ number_format($data['sales'], 0, ',', '.') }}</td>
                            <td class="text-center {{ $data['rate'] >= 100 ? 'val-good' : ($data['rate'] > 0 ? 'text-indigo-600 font-bold' : 'text-slate-400') }}">
                                {{ $data['rate'] }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


        {{-- Modal Input Target --}}
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                {{-- Backdrop --}}
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900 bg-opacity-75 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('sales.target.store') }}" method="POST">
                        @csrf
                        <div class="bg-white px-6 pt-6 pb-6">
                            <h3 class="text-xl font-bold text-slate-800 mb-6 border-b pb-3">Input Target Sales</h3>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tahun</label>
                                    <input type="number" name="tahun" value="{{ date('Y') }}" required class="modern-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bulan</label>
                                    <select name="bulan" required class="modern-input">
                                        @foreach($urutanBulan as $bulan)
                                            <option value="{{ $bulan }}">{{ $bulan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Product Specialist (PS)</label>
                                <select name="ps" required class="modern-input">
                                    <option value="">-- Pilih PS --</option>
                                    @foreach($listPs as $ps)
                                        <option value="{{ $ps }}">{{ $ps }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Target Amount (IDR)</label>
                                <input type="number" name="target_amount" required min="0" step="1" placeholder="Misal: 435000000" class="modern-input font-bold text-indigo-600">
                                <p class="text-[10px] text-slate-400 mt-1">Masukkan angka tanpa titik/koma.</p>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm transition">
                                Simpan Target
                            </button>
                            <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layout-users>
