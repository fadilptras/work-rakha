@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users>
    <x-slot:title>Detail Pengajuan Dana</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        /* == Background == */
        .mesh-bg { background-color: #ede9fe; }

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
            font-size: 0.85rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
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

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            padding: 28px;
        }

        @media (max-width: 767.98px) {
            .glass-card {
                padding: 16px;
                border-radius: 18px;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                background: rgba(255, 255, 255, 0.95) !important;
            }
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col">

            {{-- 1. NAVIGASI (BACK & DOWNLOAD) --}}
            <div class="flex justify-between items-center mb-6 relative z-10">
                @if(Auth::id() != $pengajuanDana->user_id)
                    <a href="{{ route('notifikasi.index') }}" class="btn-back-modern">
                        <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                        Kembali ke Notifikasi
                    </a>
                @else
                    <a href="{{ route('pengajuan_dana.history') }}" class="btn-back-modern">
                        <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                        Kembali ke Riwayat
                    </a>
                @endif

                @if(Auth::id() == $pengajuanDana->user_id || Auth::user()->role == 'admin' || Auth::user()->can('approve', $pengajuanDana))
                    <a href="{{ route('pengajuan_dana.download', $pengajuanDana) }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-red-600 rounded-full shadow-md hover:bg-red-700 hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-file-pdf"></i>
                        <span>Cetak PDF</span>
                    </a>
                @endif
            </div>

            {{-- 2. HEADER UTAMA --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-2xl md:rounded-3xl p-4 md:p-8 shadow-xl mb-4 md:mb-6 overflow-hidden border border-white/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute right-20 -bottom-10 w-24 h-24 bg-white/10 rounded-full blur-lg pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3 md:gap-5">
                        <div class="h-10 w-10 md:h-14 md:w-14 rounded-xl md:rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                            <i class="fas fa-hand-holding-usd text-lg md:text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-sm md:text-xl font-black tracking-tight text-white uppercase leading-snug">{{ $pengajuanDana->judul_pengajuan }}</h1>
                            <p class="text-blue-100 text-[10px] md:text-xs mt-1 font-semibold leading-relaxed">
                                Diajukan {{ $pengajuanDana->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                            </p>
                        </div>
                    </div>
                    
                    {{-- Status Badge Global --}}
                    <div class="w-full flex justify-end sm:w-auto">
                        @php
                            $statusClass = match($pengajuanDana->status) {
                                'selesai' => 'bg-green-500 text-white shadow-green-500/20',
                                'ditolak' => 'bg-red-500 text-white shadow-red-500/20',
                                'proses_pembayaran' => 'bg-blue-500 text-white shadow-blue-500/20',
                                'diproses' => 'bg-indigo-500 text-white shadow-indigo-500/20',
                                'dibatalkan' => 'bg-slate-500 text-white shadow-slate-500/20',
                                'disetujui' => 'bg-teal-500 text-white shadow-teal-500/20',
                                default => 'bg-yellow-500 text-white shadow-yellow-500/20',
                            };
                            $statusText = match($pengajuanDana->status) {
                                'selesai' => 'Selesai',
                                'ditolak' => 'Ditolak',
                                'proses_pembayaran' => 'Proses Pembayaran',
                                'diproses' => 'Diproses',
                                'dibatalkan' => 'Dibatalkan',
                                'disetujui' => 'Menunggu Final',
                                'diajukan' => 'Diajukan',
                                default => 'Menunggu',
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider border border-white/20 shadow-sm {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- 3. GRID CONTENT (INFO & TIMELINE) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                
                {{-- KOLOM KIRI: INFO PEMOHON & REKENING --}}
                <div class="lg:col-span-1 space-y-6">
                    
                    {{-- Info User --}}
                    <div class="glass-card">
                        <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-user-circle"></i></div>
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Informasi Pemohon</h4>
                        </div>
                        
                        <div class="text-xs space-y-3">
                            <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                                <span class="text-slate-500 font-semibold">Nama Pemohon</span>
                                <span class="font-bold text-slate-800 text-right">{{ $pengajuanDana->user->name }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                                <span class="text-slate-500 font-semibold">Divisi</span>
                                <span class="font-bold text-slate-800 text-right">{{ $pengajuanDana->user->divisi }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-semibold">ID Pengajuan</span>
                                <span class="font-mono font-black text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg text-[10px]">
                                    #{{ str_pad($pengajuanDana->id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Info Bank --}}
                    <div class="glass-card">
                        <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-university"></i></div>
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Rekening Tujuan</h4>
                        </div>
                        
                        <div class="space-y-3.5">
                            <div class="bg-blue-50/50 rounded-2xl border border-blue-100/70 p-3.5 flex items-center justify-between gap-3 shadow-sm">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex-shrink-0 flex items-center justify-center text-blue-700">
                                        <i class="fas fa-wallet text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider">Bank</p>
                                        <p class="font-black text-slate-800 text-xs truncate">{{ $pengajuanDana->nama_bank }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider">No. Rek</p>
                                    <p class="font-mono font-black text-slate-800 text-xs">{{ $pengajuanDana->no_rekening }}</p>
                                </div>
                            </div>
                            <div class="bg-blue-50/50 rounded-2xl border border-blue-100/70 p-3.5 flex items-center justify-between gap-3 shadow-sm">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex-shrink-0 flex items-center justify-center text-blue-700">
                                        <i class="fas fa-user-tag text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[9px] text-slate-400 uppercase font-black tracking-wider">Nama Rekening</p>
                                        <p class="font-black text-slate-800 text-xs truncate">{{ $pengajuanDana->nama_rek }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: TIMELINE --}}
                <div class="lg:col-span-2">
                    <div class="glass-card h-full flex flex-col">
                        <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-history"></i></div>
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Timeline Persetujuan</h4>
                        </div>

                        {{-- Isi Timeline --}}
                        <div class="space-y-3.5 flex-1 overflow-y-auto">
                            
                            {{-- 1. APPROVER 1 --}}
                            @if ($pengajuanDana->approver_dana_1_id)
                                @php
                                    $s1 = $pengajuanDana->approver_1_status;
                                    $c1 = $pengajuanDana->approver_1_catatan;
                                    $t1 = $pengajuanDana->approver_1_approved_at;
                                    
                                    $theme1 = match($s1) {
                                        'disetujui' => ['border' => 'border-l-green-500', 'bg' => 'bg-white', 'badge' => 'bg-green-100 text-green-700 border-green-200'],
                                        'ditolak' => ['border' => 'border-l-red-500', 'bg' => 'bg-white', 'badge' => 'bg-red-100 text-red-700 border-red-200'],
                                        'skipped' => ['border' => 'border-l-slate-400', 'bg' => 'bg-white', 'badge' => 'bg-slate-200 text-slate-600 border-slate-300'],
                                        default => ['border' => 'border-l-yellow-400', 'bg' => 'bg-white', 'badge' => 'bg-yellow-100 text-yellow-700 border-yellow-200'],
                                    };
                                @endphp
                                <div class="rounded-2xl border border-slate-200/80 p-4 {{ $theme1['bg'] }} {{ $theme1['border'] }} border-l-[4px] shadow-sm">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Tahap 1</span>
                                                @if($t1)
                                                    <span class="text-[9px] text-slate-400 font-semibold">• {{ $t1->translatedFormat('d M Y, H:i') }}</span>
                                                @endif
                                            </div>
                                            <h4 class="text-xs font-black text-slate-800 leading-tight mt-1">{{ $pengajuanDana->approverDana1->name ?? 'Approver 1' }}</h4>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider border {{ $theme1['badge'] }}">
                                            {{ ucfirst($s1) }}
                                        </span>
                                    </div>
                                    @if($c1)
                                        <div class="mt-2.5 bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-xs text-slate-600 italic flex items-start gap-2">
                                            <i class="fas fa-quote-left text-slate-300 text-[10px] mt-0.5"></i>
                                            <span>{{ $c1 }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- 2. APPROVER 2 --}}
                            @if ($pengajuanDana->approver_dana_2_id)
                                @php
                                    $s2 = $pengajuanDana->approver_2_status;
                                    $c2 = $pengajuanDana->approver_2_catatan;
                                    $t2 = $pengajuanDana->approver_2_approved_at;
                                    
                                    $theme2 = match($s2) {
                                        'disetujui' => ['border' => 'border-l-green-500', 'bg' => 'bg-white', 'badge' => 'bg-green-100 text-green-700 border-green-200'],
                                        'ditolak' => ['border' => 'border-l-red-500', 'bg' => 'bg-white', 'badge' => 'bg-red-100 text-red-700 border-red-200'],
                                        'skipped' => ['border' => 'border-l-slate-400', 'bg' => 'bg-white', 'badge' => 'bg-slate-200 text-slate-600 border-slate-300'],
                                        default => ['border' => 'border-l-yellow-400', 'bg' => 'bg-white', 'badge' => 'bg-yellow-100 text-yellow-700 border-yellow-200'],
                                    };
                                @endphp
                                <div class="rounded-2xl border border-slate-200/80 p-4 {{ $theme2['bg'] }} {{ $theme2['border'] }} border-l-[4px] shadow-sm">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Tahap 2</span>
                                                @if($t2)
                                                    <span class="text-[9px] text-slate-400 font-semibold">• {{ $t2->translatedFormat('d M Y, H:i') }}</span>
                                                @endif
                                            </div>
                                            <h4 class="text-xs font-black text-slate-800 leading-tight mt-1">{{ $pengajuanDana->approverDana2->name ?? 'Approver 2' }}</h4>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider border {{ $theme2['badge'] }}">
                                            {{ ucfirst($s2) }}
                                        </span>
                                    </div>
                                    @if($c2)
                                        <div class="mt-2.5 bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-xs text-slate-600 italic flex items-start gap-2">
                                            <i class="fas fa-quote-left text-slate-300 text-[10px] mt-0.5"></i>
                                            <span>{{ $c2 }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- 3. FINANCE / KEUANGAN --}}
                            @if ($pengajuanDana->approver_dana_3_id)
                                @php
                                    $sF = $pengajuanDana->approver_3_status; 
                                    $cF = $pengajuanDana->approver_3_catatan;
                                    $tF = $pengajuanDana->approver_3_approved_at;
                                    if ($sF === 'disetujui' && $pengajuanDana->status === 'selesai') $tF = $pengajuanDana->updated_at;

                                    $themeF = match($sF) {
                                        'disetujui' => ['border' => 'border-l-green-500', 'bg' => 'bg-white', 'badge' => 'bg-green-100 text-green-700 border-green-200'],
                                        'ditolak' => ['border' => 'border-l-red-500', 'bg' => 'bg-white', 'badge' => 'bg-red-100 text-red-700 border-red-200'],
                                        'skipped' => ['border' => 'border-l-slate-400', 'bg' => 'bg-white', 'badge' => 'bg-slate-200 text-slate-600 border-slate-300'],
                                        default => ['border' => 'border-l-yellow-400', 'bg' => 'bg-white', 'badge' => 'bg-yellow-100 text-yellow-700 border-yellow-200'],
                                    };
                                    
                                    $textF = match($sF) {
                                        'disetujui' => 'Selesai',
                                        default => ucfirst($sF)
                                    };
                                @endphp
                                <div class="rounded-2xl border border-slate-200/80 p-4 {{ $themeF['bg'] }} {{ $themeF['border'] }} border-l-[4px] shadow-sm">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Tahap 3 (Final)</span>
                                                @if($tF)
                                                    <span class="text-[9px] text-slate-400 font-semibold">• {{ $tF->translatedFormat('d M Y, H:i') }}</span>
                                                @endif
                                            </div>
                                            <h4 class="text-xs font-black text-slate-800 leading-tight mt-1">{{ $pengajuanDana->approverDana3->name ?? 'Finance' }}</h4>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider border {{ $themeF['badge'] }}">
                                            {{ $textF }}
                                        </span>
                                    </div>
                                    @if($cF)
                                        <div class="mt-2.5 bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-xs text-slate-600 italic flex items-start gap-2">
                                            <i class="fas fa-quote-left text-slate-300 text-[10px] mt-0.5"></i>
                                            <span>{{ $cF }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- 4. DIREKTUR / FINAL --}}
                            @if ($pengajuanDana->approver_dana_4_id)
                                @php
                                    $s4 = $pengajuanDana->approver_4_status; 
                                    $c4 = $pengajuanDana->approver_4_catatan;
                                    $t4 = $pengajuanDana->approver_4_approved_at;
                                    if ($s4 === 'disetujui' && $pengajuanDana->status === 'selesai') $t4 = $pengajuanDana->updated_at;

                                    $theme4 = match($s4) {
                                        'disetujui' => ['border' => 'border-l-green-500', 'bg' => 'bg-white', 'badge' => 'bg-green-100 text-green-700 border-green-200'],
                                        'ditolak' => ['border' => 'border-l-red-500', 'bg' => 'bg-white', 'badge' => 'bg-red-100 text-red-700 border-red-200'],
                                        'skipped' => ['border' => 'border-l-slate-400', 'bg' => 'bg-white', 'badge' => 'bg-slate-200 text-slate-600 border-slate-300'],
                                        default => ['border' => 'border-l-yellow-400', 'bg' => 'bg-white', 'badge' => 'bg-yellow-100 text-yellow-700 border-yellow-200'],
                                    };
                                    
                                    $text4 = match($s4) {
                                        'disetujui' => 'Selesai',
                                        default => ucfirst($s4)
                                    };
                                @endphp
                                <div class="rounded-2xl border border-slate-200/80 p-4 {{ $theme4['bg'] }} {{ $theme4['border'] }} border-l-[4px] shadow-sm mt-3.5">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Tahap 4 (Final)</span>
                                                @if($t4)
                                                    <span class="text-[9px] text-slate-400 font-semibold">• {{ $t4->translatedFormat('d M Y, H:i') }}</span>
                                                @endif
                                            </div>
                                            <h4 class="text-xs font-black text-slate-800 leading-tight mt-1">{{ $pengajuanDana->approverDana4->name ?? 'Direktur' }}</h4>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider border {{ $theme4['badge'] }}">
                                            {{ $text4 }}
                                        </span>
                                    </div>
                                    @if($c4)
                                        <div class="mt-2.5 bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-xs text-slate-600 italic flex items-start gap-2">
                                            <i class="fas fa-quote-left text-slate-300 text-[10px] mt-0.5"></i>
                                            <span>{{ $c4 }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. RINCIAN DANA (TABEL) --}}
            <div class="glass-card mb-6">
                <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-list-ul"></i></div>
                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Rincian Penggunaan Dana</h4>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200/80" style="border-radius: 16px; overflow: hidden;">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-black border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-center w-16">No</th>
                                <th class="px-6 py-4 text-left">Deskripsi Kebutuhan</th>
                                <th class="px-6 py-4 text-right w-48">Nominal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($pengajuanDana->rincian_dana as $index => $rincian)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4.5 text-center text-slate-500 font-extrabold">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4.5 text-slate-800 font-bold leading-normal">{{ $rincian['deskripsi'] ?? '-' }}</td>
                                <td class="px-6 py-4.5 text-right font-black font-mono text-slate-700 text-sm">
                                    Rp {{ number_format($rincian['jumlah'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-slate-400 font-bold" style="padding: 32px 16px;">Data rincian tidak tersedia.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-blue-50/50 border-t border-blue-100">
                            <tr>
                                <td colspan="2" class="px-6 py-4.5 text-right font-black text-slate-700">TOTAL PENGAJUAN</td>
                                <td class="px-6 py-4.5 text-right font-black font-mono text-blue-700 text-base">
                                    Rp {{ number_format($pengajuanDana->total_dana, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- 5. DOKUMEN & BUKTI --}}
            <div class="glass-card mb-6">
                <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-paperclip"></i></div>
                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Dokumen Pendukung</h4>
                </div>

                @if(empty($pengajuanDana->lampiran) && !$pengajuanDana->bukti_transfer && !$pengajuanDana->invoice)
                    <div class="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                        <i class="fas fa-file-excel text-4xl text-slate-200 mb-3"></i>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Tidak ada dokumen yang dilampirkan.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        {{-- Lampiran Array --}}
                        @if ($pengajuanDana->lampiran)
                            @foreach ($pengajuanDana->lampiran as $lampiran)
                            <div class="relative group">
                                <a href="{{ asset('storage/' . $lampiran) }}"
                                   class="flex flex-col items-center p-6 bg-slate-50 border border-slate-200 rounded-2xl hover:bg-blue-50/50 hover:border-blue-300 transition-all text-center h-full shadow-sm">
                                    <i class="fas fa-file-alt text-3xl text-blue-600 mb-3 group-hover:scale-110 transition-transform"></i>
                                    <p class="text-xs font-bold text-slate-700 truncate w-full">{{ basename($lampiran) }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold mt-1">Lampiran {{ $loop->iteration }}</p>
                                </a>
                                <a href="{{ asset('storage/' . $lampiran) }}" download
                                   class="absolute top-2 right-2 p-1.5 bg-slate-200 text-slate-600 rounded-lg hover:bg-blue-600 hover:text-white transition shadow-sm"
                                   title="Download">
                                    <i class="fas fa-download text-xs"></i>
                                </a>
                            </div>
                            @endforeach
                        @endif

                        {{-- Bukti Transfer --}}
                        @if($pengajuanDana->bukti_transfer)
                            <div class="relative group">
                                <a href="{{ asset('storage/' . $pengajuanDana->bukti_transfer) }}"
                                   class="flex flex-col items-center p-6 bg-green-50 border border-green-200 rounded-2xl hover:bg-green-100/50 hover:border-green-300 transition-all text-center h-full shadow-sm">
                                    <i class="fas fa-receipt text-3xl text-green-600 mb-3 group-hover:scale-110 transition-transform"></i>
                                    <p class="text-xs font-bold text-green-800">Bukti Transfer</p>
                                    <p class="text-[10px] text-green-600 font-bold mt-1">Telah Diunggah</p>
                                </a>
                                <a href="{{ asset('storage/' . $pengajuanDana->bukti_transfer) }}" download
                                   class="absolute top-2 right-2 p-1.5 bg-green-200 text-green-700 rounded-lg hover:bg-green-600 hover:text-white transition shadow-sm">
                                    <i class="fas fa-download text-xs"></i>
                                </a>
                            </div>
                        @endif

                        {{-- Invoice --}}
                        @if($pengajuanDana->invoice)
                            <div class="relative group">
                                <a href="{{ asset('storage/' . $pengajuanDana->invoice) }}"
                                   class="flex flex-col items-center p-6 bg-purple-50 border border-purple-200 rounded-2xl hover:bg-purple-100/50 hover:border-purple-300 transition-all text-center h-full shadow-sm">
                                    <i class="fas fa-file-invoice-dollar text-3xl text-purple-600 mb-3 group-hover:scale-110 transition-transform"></i>
                                    <p class="text-xs font-bold text-purple-800">Invoice Final</p>
                                    <p class="text-[10px] text-purple-600 font-bold mt-1">Telah Diunggah</p>
                                </a>
                                <a href="{{ asset('storage/' . $pengajuanDana->invoice) }}" download
                                   class="absolute top-2 right-2 p-1.5 bg-purple-200 text-purple-700 rounded-lg hover:bg-purple-600 hover:text-white transition shadow-sm">
                                    <i class="fas fa-download text-xs"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- 6. AREA TINDAKAN / ACTION --}}
            <div class="space-y-6 mt-8 pb-12">
                
                {{-- A. APPROVAL --}}
                @can('approve', $pengajuanDana)
                <div class="glass-card border-t-4 border-t-blue-500">
                    <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center">
                        <i class="fas fa-gavel text-slate-700 mr-2"></i> Tindakan Persetujuan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <form action="{{ route('pengajuan_dana.approve', $pengajuanDana) }}" method="POST" enctype="multipart/form-data" class="bg-green-50/70 p-5 rounded-2xl border border-green-100 shadow-sm flex flex-col gap-3">
                            @csrf
                            
                            @php
                                $user = Auth::user();
                                $currentStage = null;
                                if ($user->id == $pengajuanDana->approver_dana_1_id && $pengajuanDana->approver_1_status == 'menunggu') $currentStage = 1;
                                elseif ($user->id == $pengajuanDana->approver_dana_2_id && $pengajuanDana->approver_2_status == 'menunggu') $currentStage = 2;
                                elseif ($user->id == $pengajuanDana->approver_dana_3_id && $pengajuanDana->approver_3_status == 'menunggu') $currentStage = 3;
                                elseif ($user->id == $pengajuanDana->approver_dana_4_id && $pengajuanDana->approver_4_status == 'menunggu') $currentStage = 4;
                            @endphp

                            @if($currentStage == 3)
                            <div>
                                <label class="block text-xs font-black text-green-800 mb-1">
                                    Berkas Bukti Transfer <span class="font-semibold text-green-600">(Opsional)</span>
                                </label>
                                <input type="file" name="bukti_transfer" class="block w-full text-xs text-slate-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-xl file:border-0
                                    file:text-xs file:font-black
                                    file:bg-green-100 file:text-green-700
                                    hover:file:bg-green-200 cursor-pointer">
                            </div>
                            @endif

                            <label class="block text-xs font-black text-green-800">
                                Catatan Persetujuan <span class="font-semibold text-green-600">(Opsional)</span>
                            </label>
                            <textarea name="catatan_persetujuan" rows="3" 
                                class="w-full p-3 border border-green-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-xs font-semibold"
                                placeholder="Tulis catatan persetujuan jika perlu..."></textarea>
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-black py-3 px-4 rounded-xl text-xs uppercase tracking-wider transition-colors shadow-md shadow-green-200 flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> Setujui Pengajuan
                            </button>
                        </form>
                        
                        <form action="{{ route('pengajuan_dana.reject', $pengajuanDana) }}" method="POST" class="bg-red-50/70 p-5 rounded-2xl border border-red-100 shadow-sm flex flex-col gap-3">
                            @csrf
                            <label class="block text-xs font-black text-red-800">
                                Catatan Penolakan <span class="text-red-600 font-bold">*</span>
                            </label>
                            <textarea name="catatan_penolakan" rows="3" 
                                class="w-full p-3 border border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white text-xs font-semibold"
                                placeholder="Tulis alasan penolakan wajib..." required></textarea>
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-black py-3 px-4 rounded-xl text-xs uppercase tracking-wider transition-colors shadow-md shadow-red-200 flex items-center justify-center gap-2">
                                <i class="fas fa-times-circle"></i> Tolak Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
                @endcan



                {{-- D. BATALKAN --}}
                @can('cancel', $pengajuanDana)
                <div class="bg-red-50/70 rounded-3xl border border-red-100 p-6 md:p-8 text-center shadow-sm">
                    <h3 class="text-base font-black text-red-800 mb-1 uppercase tracking-tight">Batalkan Pengajuan?</h3>
                    <p class="text-xs text-red-600 mb-4 font-semibold">Tindakan ini tidak dapat diurungkan. Pengajuan dana akan dibatalkan secara permanen.</p>
                    <form action="{{ route('pengajuan_dana.cancel', $pengajuanDana) }}" method="POST" onsubmit="confirmSubmit(event, 'Apakah Anda benar-benar yakin ingin membatalkan pengajuan dana ini?');">
                        @csrf
                        <button type="submit" class="bg-white border border-red-200 text-red-600 hover:bg-red-600 hover:text-white font-black py-2.5 px-6 rounded-xl text-xs uppercase transition shadow-sm">
                            Batalkan Pengajuan
                        </button>
                    </form>
                </div>
                @endcan

            </div>

        </div>
    </div>
</x-layout-users>