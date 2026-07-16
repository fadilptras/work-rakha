<x-layout-users :title="'Laporan Sales Contribution (' . $year . ')'">

    <div class="min-h-screen bg-gradient-to-br from-sky-50 to-blue-100 font-sans text-sm pb-20">

        {{-- CONTAINER UTAMA --}}
        <div class="max-w-[95%] mx-auto pt-6">

            {{-- BAGIAN 1: HEADER --}}
            <div class="bg-[#001BB7] rounded-3xl shadow-xl shadow-blue-900/20 mb-8 overflow-hidden relative border border-blue-900/10">
                
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl pointer-events-none z-0"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-blue-400 opacity-20 rounded-full blur-2xl pointer-events-none z-0"></div>
                
                <div class="flex flex-col md:flex-row justify-between items-center px-8 py-6 relative z-50 gap-6">
                    <div class="flex items-center gap-5 w-full md:w-auto">
                        <a href="{{ route('crm.index') }}" class="group flex items-center justify-center w-12 h-12 rounded-2xl bg-white/10 text-white hover:bg-white/20 transition backdrop-blur-sm border border-white/10 shadow-sm">
                            <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                        </a>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="bg-white/20 backdrop-blur-md text-white text-[10px] font-bold px-3 py-0.5 rounded-full uppercase tracking-wider border border-white/30 shadow-sm">
                                    Laporan Tahunan
                                </span>
                            </div>
                            <h1 class="text-2xl font-extrabold text-white tracking-tight">
                                Sales Contribution
                            </h1>
                            <p class="text-sm text-blue-100 font-medium opacity-80 mt-0.5">Periode Tahun Anggaran {{ $year }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto justify-end">
                        <div class="flex items-center bg-blue-950/30 border border-blue-400/30 rounded-xl p-1.5 backdrop-blur-sm w-full sm:w-auto justify-between sm:justify-start shadow-inner">
                            <a href="{{ route('crm.matrix', ['year' => $year - 1]) }}" class="w-9 h-9 flex items-center justify-center text-blue-200 hover:text-white hover:bg-white/10 rounded-lg transition cursor-pointer z-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <span class="px-5 font-bold text-white font-mono text-lg">{{ $year }}</span>
                            <a href="{{ route('crm.matrix', ['year' => $year + 1]) }}" class="w-9 h-9 flex items-center justify-center text-blue-200 hover:text-white hover:bg-white/10 rounded-lg transition cursor-pointer z-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                        
                        <a href="{{ route('crm.matrix.export', ['year' => $year]) }}" class="w-full sm:w-auto h-12 px-6 flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-900/20 transition border border-emerald-400/50 transform active:scale-95 cursor-pointer z-50">
                            <i class="fas fa-file-excel text-lg"></i> <span>Export Excel</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- BAGIAN 2: CONTENT LOOP --}}
            @php $gtGross = 0; $gtBudget = 0; $gtUsage = 0; $gtRemain = 0; @endphp

            <div class="space-y-8"> 
            
            @forelse ($clients as $index => $client)
                @php
                    // --- [PERBAIKAN LOGIC SALDO] ---
                    
                    // 1. Hitung Saldo Awal (Carry Forward) dari tahun-tahun sebelumnya
                    // Pastikan di Controller query client tidak di-filter whereYear, tapi eager loading interaction yang di filter
                    // atau jika controller memuat semua, kita filter manual disini.
                    // Asumsi: $client->interactions memuat SEMUA history (sesuai perbaikan controller)

                    $pastInteractions = $client->interactions->filter(fn($i) => \Carbon\Carbon::parse($i->tanggal_interaksi)->year < $year);
                    $carryOverBalance = $client->saldo_awal ?? 0;

                    foreach($pastInteractions as $pastItem) {
                        if ($pastItem->jenis_transaksi == 'IN') {
                            $r = $pastItem->komisi ?? 0;
                            if (!$r && preg_match('/\[Rate:([\d\.]+)\]/', $pastItem->catatan, $m)) $r = (float)$m[1];
                            $nom = $pastItem->nilai_sales > 0 ? $pastItem->nilai_sales : $pastItem->nilai_kontribusi;
                            $carryOverBalance += ($nom * ($r / 100));
                        } elseif ($pastItem->jenis_transaksi == 'OUT') {
                            $carryOverBalance -= $pastItem->nilai_kontribusi;
                        }
                    }

                    // 2. Logic Data Tahun Ini (Current Year)
                    $yearInts = $client->interactions->filter(fn($i) => \Carbon\Carbon::parse($i->tanggal_interaksi)->year == $year);
                    $incomeTransactions = $yearInts->where('jenis_transaksi', 'IN')->sortBy('tanggal_interaksi');
                    
                    $clientTotalBudget = 0; $clientTotalGross = 0;
                    foreach($incomeTransactions as $s) {
                        $r = $s->komisi ?? 0;
                        if (!$r && preg_match('/\[Rate:([\d\.]+)\]/', $s->catatan, $m)) $r = (float)$m[1];
                        $nom = $s->nilai_sales > 0 ? $s->nilai_sales : $s->nilai_kontribusi;
                        $clientTotalGross += $nom;
                        $clientTotalBudget += ($nom * ($r / 100));
                    }

                    $usageInts = $yearInts->where('jenis_transaksi', 'OUT');
                    $clientTotalUsage = $usageInts->sum('nilai_kontribusi');
                    
                    // 3. Sisa Saldo Akhir = Saldo Bawaan + Income Tahun Ini - Usage Tahun Ini
                    $clientRemain = $carryOverBalance + $clientTotalBudget - $clientTotalUsage;

                    // Grand Totals Accumulation
                    $gtGross += $clientTotalGross; 
                    $gtBudget += $clientTotalBudget;
                    $gtUsage += $clientTotalUsage; 
                    // gtRemain otomatis mengakumulasi saldo bawaan karena clientRemain sudah mengandung carryOver
                    $gtRemain += $clientRemain;
                @endphp

                {{-- === KARTU CLIENT === --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden relative group hover:shadow-md transition-all duration-300">
                    
                    {{-- A. HEADER CLIENT --}}
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center px-6 py-5 border-b border-slate-100 gap-3">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 text-white font-bold text-sm shadow-md ring-4 ring-slate-50">{{ $index + 1 }}</span>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 leading-tight">{{ $client->nama_user }}</h3>
                                <div class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-2">
                                    <span class="flex items-center"><i class="far fa-building mr-1.5 opacity-70"></i> {{ $client->nama_perusahaan }}</span>
                                    @if($client->area) 
                                        <span class="text-slate-300">|</span> 
                                        <span class="flex items-center"><i class="fas fa-map-marker-alt mr-1.5 opacity-70"></i> {{ $client->area }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($client->pic)
                            <span class="self-start sm:self-center text-[10px] font-bold px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase tracking-wide shadow-sm">
                                <i class="fas fa-user-tie mr-1.5"></i> {{ $client->pic }}
                            </span>
                        @endif
                    </div>

                    {{-- B. STATS BAR WARNA-WARNI --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-5 bg-slate-50/50">
                        {{-- 1. INCOME --}}
                        <div class="bg-blue-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-200 relative overflow-hidden group/card transition hover:-translate-y-1">
                            <div class="absolute top-0 right-0 -mt-6 -mr-6 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
                            <div class="relative z-10">
                                <span class="text-[10px] font-bold uppercase tracking-wider opacity-80 block mb-1">Income (Thn {{ $year }})</span>
                                <span class="text-2xl font-mono font-bold">{{ number_format($clientTotalBudget, 0, ',', '.') }}</span>
                            </div>
                            <i class="fas fa-wallet absolute right-4 bottom-4 text-white opacity-20 text-4xl group-hover/card:scale-110 transition transform"></i>
                        </div>

                        {{-- 2. USAGE --}}
                        <div class="bg-red-500 rounded-2xl p-5 text-white shadow-lg shadow-red-200 relative overflow-hidden group/card transition hover:-translate-y-1">
                            <div class="absolute top-0 right-0 -mt-6 -mr-6 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
                            <div class="relative z-10">
                                <span class="text-[10px] font-bold uppercase tracking-wider opacity-80 block mb-1">Usage (Thn {{ $year }})</span>
                                <span class="text-2xl font-mono font-bold">{{ number_format($clientTotalUsage, 0, ',', '.') }}</span>
                            </div>
                            <i class="fas fa-fire absolute right-4 bottom-4 text-white opacity-20 text-4xl group-hover/card:scale-110 transition transform"></i>
                        </div>

                        {{-- 3. SALDO --}}
                        <div class="bg-emerald-500 rounded-2xl p-5 text-white shadow-lg shadow-emerald-200 relative overflow-hidden group/card transition hover:-translate-y-1">
                            <div class="absolute bottom-0 left-0 -mb-6 -ml-6 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
                            <div class="relative z-10">
                                <span class="text-[10px] font-bold uppercase tracking-wider opacity-80 block mb-1">Sisa Saldo (Akumulasi)</span>
                                <span class="text-2xl font-mono font-bold">{{ number_format($clientRemain, 0, ',', '.') }}</span>
                            </div>
                            @if($carryOverBalance != 0)
                                <div class="absolute top-3 right-3 text-[9px] bg-emerald-600/50 px-2 py-0.5 rounded text-emerald-50">
                                    Incl. Saldo Awal: {{ number_format($carryOverBalance,0,',','.') }}
                                </div>
                            @endif
                            <i class="fas fa-coins absolute right-4 bottom-4 text-white opacity-20 text-4xl group-hover/card:scale-110 transition transform"></i>
                        </div>
                    </div>

                    {{-- C. CONTENT BODY (Auto Height) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-12 border-t border-slate-200 h-auto"> 
                        
                        {{-- LIST PEMASUKAN --}}
                        <div class="lg:col-span-4 bg-white flex flex-col border-r border-slate-100">
                            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                                <span class="text-[10px] font-bold text-slate-500 uppercase flex items-center gap-2">
                                    <i class="fas fa-list text-slate-400"></i> Rincian Pemasukan
                                </span>
                            </div>
                            
                            {{-- Gunakan max-h untuk scroll --}}
                            <div class="flex-1 overflow-y-auto p-4 custom-scrollbar space-y-3 max-h-[350px]">
                                @forelse($incomeTransactions as $trans)
                                    @php
                                        $r = $trans->komisi ?? 0;
                                        if (!$r && preg_match('/\[Rate:([\d\.]+)\]/', $trans->catatan, $m)) $r = (float)$m[1];
                                        $nominal = $trans->nilai_sales > 0 ? $trans->nilai_sales : $trans->nilai_kontribusi;
                                        $net = $nominal * ($r / 100);
                                    @endphp
                                    <div class="relative pl-3 py-0.5 group/item">
                                        {{-- Garis indikator --}}
                                        <div class="absolute left-0 top-1 bottom-1 w-1 bg-slate-200 rounded-full group-hover/item:bg-blue-500 transition-colors"></div>
                                        
                                        <div class="flex justify-between items-start">
                                            <div class="min-w-0 pr-2">
                                                <div class="text-[10px] text-slate-400 font-medium mb-0.5">
                                                    {{ \Carbon\Carbon::parse($trans->tanggal_interaksi)->isoFormat('D MMM') }}
                                                </div>
                                                <div class="font-bold text-slate-700 truncate text-xs mb-1" title="{{ $trans->nama_produk }}">
                                                    {{ $trans->nama_produk }}
                                                </div>
                                                <span class="inline-flex items-center text-[9px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded border border-slate-200">
                                                    Rate <span class="font-bold text-blue-600 ml-1">{{ $r }}%</span>
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-mono font-bold text-blue-600 text-sm">{{ number_format($net, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="border-slate-50 last:hidden">
                                @empty
                                    <div class="py-8 flex flex-col items-center justify-center text-slate-300 text-xs italic">
                                        <i class="fas fa-inbox text-2xl mb-2 opacity-20"></i>
                                        Belum ada pemasukan tahun ini
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- KALENDER PENGELUARAN --}}
                        <div class="lg:col-span-8 bg-white flex flex-col">
                            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 shrink-0">
                                <span class="text-[10px] font-bold text-slate-500 uppercase flex items-center gap-2">
                                    <i class="far fa-calendar-alt text-slate-400"></i> Kalender Pengeluaran
                                </span>
                            </div>

                            <div class="flex-1 p-5">
                                <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                                    @foreach($months as $mNum => $mName)
                                        @php
                                            $mVal = $usageInts->filter(fn($i) => \Carbon\Carbon::parse($i->tanggal_interaksi)->month == $mNum)->sum('nilai_kontribusi');
                                            $isActive = $mVal > 0;
                                        @endphp
                                        <div class="flex flex-col rounded-xl border text-center overflow-hidden transition-all duration-200 h-24
                                            {{ $isActive 
                                                ? 'bg-white border-red-200 shadow-sm ring-2 ring-red-50 hover:shadow-md' 
                                                : 'bg-slate-50/50 border-slate-100 text-slate-300 opacity-60' }}">
                                            
                                            <div class="py-2 text-[9px] uppercase font-bold tracking-wider border-b border-dashed
                                                {{ $isActive ? 'bg-red-50/50 text-red-500 border-red-100' : 'bg-transparent border-transparent' }}">
                                                {{ substr($mName, 0, 3) }}
                                            </div>
                                            
                                            <div class="flex-1 flex items-center justify-center px-1">
                                                @if($isActive)
                                                    <span class="text-xs lg:text-sm font-mono font-bold text-red-600 truncate w-full">
                                                        {{ number_format($mVal, 0, ',', '.') }}
                                                    </span>
                                                @else
                                                    <span class="text-xl opacity-30 font-light">-</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="text-center py-24 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-folder-open text-3xl text-slate-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700">Belum ada data</h3>
                    <p class="text-slate-500 text-sm mt-1">Belum ada transaksi.</p>
                </div>
            @endforelse
            </div>

            {{-- BAGIAN 3: GRAND TOTAL --}}
            <div class="mt-10 mb-8 bg-[#001BB7] rounded-3xl shadow-xl shadow-blue-900/20 relative overflow-hidden text-white border border-blue-900/10">
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-48 h-48 bg-blue-400 opacity-10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-white opacity-5 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex flex-col xl:flex-row justify-between items-center gap-8 relative z-10 px-8 py-8">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white border border-white/10 shadow-inner">
                            <i class="fas fa-chart-line text-3xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-extrabold text-white tracking-tight">GRAND TOTAL</h2>
                            <p class="text-blue-200 text-sm opacity-80">Akumulasi performa keuangan seluruh klien aktif.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 w-full xl:w-auto">
                        {{-- Budget (Net Income Tahun Ini) --}}
                        <div class="bg-blue-800/40 backdrop-blur-sm rounded-2xl p-5 border border-blue-400/20 text-center min-w-[180px] shadow-sm">
                            <div class="text-[10px] text-blue-200 uppercase font-bold mb-1 tracking-wider">Total Income ({{ $year }})</div>
                            <div class="font-mono font-bold text-2xl text-white tracking-tight">{{ number_format($gtBudget, 0, ',', '.') }}</div>
                        </div>
                        {{-- Usage Tahun Ini --}}
                        <div class="bg-red-900/40 backdrop-blur-sm rounded-2xl p-5 border border-red-400/20 text-center min-w-[180px] shadow-sm">
                            <div class="text-[10px] text-red-200 uppercase font-bold mb-1 tracking-wider">Total Usage ({{ $year }})</div>
                            <div class="font-mono font-bold text-2xl text-white tracking-tight">{{ number_format($gtUsage, 0, ',', '.') }}</div>
                        </div>
                        {{-- Sisa Saldo (Akumulasi) --}}
                        <div class="bg-emerald-600/30 backdrop-blur-sm rounded-2xl p-5 border border-emerald-400/30 text-center min-w-[180px] shadow-lg">
                            <div class="text-[10px] text-emerald-200 uppercase font-bold mb-1 tracking-wider">Total Sisa Saldo</div>
                            <div class="font-mono font-extrabold text-3xl text-emerald-300 tracking-tight drop-shadow-sm">{{ number_format($gtRemain, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div> 
    </div>
</x-layout-users>