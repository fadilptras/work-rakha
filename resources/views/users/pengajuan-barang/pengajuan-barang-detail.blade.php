@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users>
    <x-slot:title>Detail Pengajuan Barang</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        html { scroll-behavior: smooth; }
        /* == Background == */
        .mesh-bg { background-color: #ede9fe; }
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9); border-radius: 9999px;
            color: #1e293b; font-size: 0.9rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); width: fit-content;
        }
        .btn-back-modern:hover { background: rgba(255,255,255,0.95); box-shadow: 0 10px 15px -3px rgba(99,102,241,0.15); transform: translateY(-2px); color: #4338ca; }
        .btn-back-modern .icon-circle { width: 32px; height: 32px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6366f1; font-size: 0.85rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: transform 0.3s ease; }
        .btn-back-modern:hover .icon-circle { transform: translateX(-3px); background: #EEF2FF; }
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,1); border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); padding: 28px; }

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

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">

            {{-- NAVIGASI --}}
            <div class="flex justify-between items-center mb-6 relative z-10">
                @if(request('from') === 'monitoring')
                    <a href="{{ route('pengajuan_barang.monitoring_all') }}" class="btn-back-modern">
                        <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                        Kembali ke Monitoring
                    </a>
                @elseif(Auth::id() != $pengajuanBarang->user_id)
                    <a href="{{ route('notifikasi.index') }}" class="btn-back-modern">
                        <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                        Kembali ke Notifikasi
                    </a>
                @else
                    <a href="{{ route('pengajuan_barang.history') }}" class="btn-back-modern">
                        <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                        Kembali ke Riwayat
                    </a>
                @endif

                @if(Auth::id() == $pengajuanBarang->user_id || Auth::user()->role === 'admin')
                    <a href="{{ route('pengajuan_barang.download', $pengajuanBarang) }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-red-600 rounded-full shadow-md hover:bg-red-700 hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-file-pdf"></i>
                        <span>Cetak PDF</span>
                    </a>
                @endif
            </div>

            {{-- HEADER UTAMA --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-2xl md:rounded-3xl p-5 md:p-6 shadow-xl mb-4 md:mb-6 overflow-hidden border border-white/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute right-20 -bottom-10 w-24 h-24 bg-white/10 rounded-full blur-lg pointer-events-none"></div>
                @php
                    $badgeClass = match($pengajuanBarang->status) {
                        'selesai' => 'bg-green-500 text-white shadow-green-500/20',
                        'ditolak' => 'bg-red-500 text-white shadow-red-500/20',
                        'diproses' => 'bg-blue-500 text-white shadow-blue-500/20',
                        'dibatalkan' => 'bg-slate-500 text-white shadow-slate-500/20',
                        default => 'bg-yellow-500 text-white shadow-yellow-500/20',
                    };
                    $badgeLabel = match($pengajuanBarang->status) {
                        'diajukan' => 'Diajukan',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                        'dibatalkan' => 'Dibatalkan',
                        default => ucfirst($pengajuanBarang->status),
                    };
                @endphp
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3 md:gap-4">
                        <div class="h-10 w-10 md:h-12 md:w-12 rounded-xl md:rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                            <i class="fas fa-box text-lg md:text-xl text-white"></i>
                        </div>
                        <div>
                            <div class="text-blue-200 text-[10px] font-black uppercase tracking-widest mb-0.5">
                                #BRG-{{ str_pad($pengajuanBarang->id, 4, '0', STR_PAD_LEFT) }}
                            </div>
                            <h1 class="text-base md:text-xl font-black tracking-tight text-white uppercase leading-snug">{{ $pengajuanBarang->judul_pengajuan }}</h1>
                            <p class="text-blue-100 text-xs md:text-sm mt-0.5 font-medium leading-relaxed w-full">
                                Diajukan {{ $pengajuanBarang->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                            </p>
                        </div>
                    </div>
                    <div class="w-full flex justify-end sm:w-auto">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider border border-white/20 shadow-sm {{ $badgeClass }}">
                            {{ $badgeLabel }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- SESSION MESSAGES --}}
            
            

            {{-- INFORMASI PEMOHON --}}
            <div class="glass-card mb-6">
                <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 16px;">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-user-circle"></i></div>
                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Informasi Pemohon</h4>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 text-sm">
                    <div class="flex flex-col border-b md:border-b-0 md:border-r border-slate-100 pb-3 md:pb-0 md:pr-4">
                        <span class="text-slate-500 font-semibold text-[10px] uppercase mb-1">Nama</span>
                        <span class="font-bold text-slate-800 text-xs truncate" title="{{ $pengajuanBarang->user->name ?? '-' }}">{{ $pengajuanBarang->user->name ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col border-b md:border-b-0 md:border-r border-slate-100 pb-3 md:pb-0 md:pr-4">
                        <span class="text-slate-500 font-semibold text-[10px] uppercase mb-1">Divisi</span>
                        <span class="font-bold text-slate-800 text-xs truncate" title="{{ $pengajuanBarang->divisi }}">{{ $pengajuanBarang->divisi }}</span>
                    </div>
                    <div class="flex flex-col border-b md:border-b-0 md:border-r border-slate-100 pb-3 md:pb-0 md:pr-4">
                        <span class="text-slate-500 font-semibold text-[10px] uppercase mb-1">Email</span>
                        <span class="font-bold text-slate-800 text-xs truncate" title="{{ $pengajuanBarang->user->email ?? '-' }}">{{ $pengajuanBarang->user->email ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col pb-3 md:pb-0">
                        <span class="text-slate-500 font-semibold text-[10px] uppercase mb-1">Tanggal</span>
                        <span class="font-bold text-slate-800 text-xs">{{ $pengajuanBarang->created_at->locale('id')->isoFormat('D MMMM YYYY') }}</span>
                    </div>
                </div>
            </div>

            {{-- GRID: TIMELINE + RINCIAN BARANG --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                
                {{-- TIMELINE PERSETUJUAN --}}
                <div class="glass-card flex flex-col">
                    <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-history"></i></div>
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Timeline Persetujuan</h4>
                    </div>
                    <div class="space-y-3.5 flex-1 overflow-y-auto max-h-[500px] pr-2">
                        @foreach ([
                            ['label' => 'Tahap 1', 'approver' => $pengajuanBarang->approver1, 'status' => $pengajuanBarang->status_appr_1, 'catatan' => $pengajuanBarang->catatan_approver_1, 'tanggal' => $pengajuanBarang->tanggal_approved_1],
                            ['label' => 'Tahap 2', 'approver' => $pengajuanBarang->approver2, 'status' => $pengajuanBarang->status_appr_2, 'catatan' => $pengajuanBarang->catatan_approver_2, 'tanggal' => $pengajuanBarang->tanggal_approved_2],
                            ['label' => 'Tahap 3', 'approver' => $pengajuanBarang->approver3, 'status' => $pengajuanBarang->status_appr_3, 'catatan' => $pengajuanBarang->catatan_approver_3, 'tanggal' => $pengajuanBarang->tanggal_approved_3],
                            ['label' => 'Tahap 4 (Admin / Pengadaan)', 'approver' => $pengajuanBarang->approver4, 'status' => $pengajuanBarang->status_appr_4, 'catatan' => $pengajuanBarang->catatan_approver_4, 'tanggal' => $pengajuanBarang->tanggal_approved_4],
                        ] as $tahap)
                            @if($tahap['approver'])
                                @php
                                    $borderColor = match($tahap['status']) {
                                        'disetujui' => 'border-l-green-500',
                                        'ditolak' => 'border-l-red-500',
                                        'skipped' => 'border-l-slate-400',
                                        default => 'border-l-yellow-400',
                                    };
                                    $badgeAppr = match($tahap['status']) {
                                        'disetujui' => 'bg-green-100 text-green-700 border-green-200',
                                        'ditolak' => 'bg-red-100 text-red-700 border-red-200',
                                        'skipped' => 'bg-slate-200 text-slate-600 border-slate-300',
                                        default => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    };
                                @endphp
                                <div class="rounded-2xl border border-slate-200/80 p-4 bg-white {{ $borderColor }} border-l-[4px] shadow-sm">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">{{ $tahap['label'] }}</span>
                                                @if($tahap['tanggal'])
                                                    <span class="text-[9px] text-slate-400 font-semibold">• {{ $tahap['tanggal']->locale('id')->isoFormat('D MMMM YYYY, H:i') }}</span>
                                                @endif
                                            </div>
                                            <h4 class="text-xs font-black text-slate-800 leading-tight mt-1">{{ $tahap['approver']->name }}</h4>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider border {{ $badgeAppr }}">
                                            {{ ucfirst($tahap['status']) }}
                                        </span>
                                    </div>
                                    @if($tahap['catatan'])
                                        <div class="mt-2.5 bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-xs text-slate-600 italic flex items-start gap-2">
                                            <i class="fas fa-quote-left text-slate-300 text-[10px] mt-0.5"></i>
                                            <span>{{ $tahap['catatan'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- RINCIAN BARANG --}}
                <div class="glass-card flex flex-col">
                    <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-list-ul"></i></div>
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Rincian Barang</h4>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200/80 mb-4" style="border-radius: 16px;">
                        <table class="min-w-full text-xs">
                            <thead class="bg-slate-50 text-slate-600 uppercase font-black border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-center w-12">No</th>
                                    <th class="px-4 py-3 text-left min-w-[150px]">Nama Barang</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">Diminta</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">Status Pengiriman</th>
                                    <th class="px-4 py-3 text-left min-w-[120px]">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($pengajuanBarang->rincian_barang ?? [] as $index => $item)
                                @php
                                    $diminta = $item['jumlah'] ?? 0;
                                    $diproses = $item['jumlah_diproses'] ?? 0;
                                    $sisa = $diminta - $diproses;
                                    $satuan = $item['satuan'] ?? '';
                                    $riwayat = $item['riwayat_proses'] ?? [];
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-center text-slate-500 font-extrabold align-top">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-slate-800 font-bold leading-normal align-top">
                                        {{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}
                                        <div class="text-[10px] text-slate-500 font-normal mt-0.5"><i class="fas fa-store mr-1 text-slate-400"></i>{{ $item['supplier'] ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-black text-blue-700 whitespace-nowrap align-top">{{ $diminta }} {{ $satuan }}</td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="flex flex-col gap-1.5">
                                            <div class="flex justify-between items-center text-[11px]">
                                                <span class="text-slate-500 font-semibold">Terkirim:</span>
                                                <span class="font-black text-green-600">{{ $diproses }} {{ $satuan }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-[11px] border-b border-slate-100 pb-1.5 mb-0.5">
                                                <span class="text-slate-500 font-semibold">Sisa:</span>
                                                <span class="font-black {{ $sisa > 0 ? 'text-orange-500' : 'text-slate-400' }}">{{ $sisa }} {{ $satuan }}</span>
                                            </div>
                                            
                                            @if(count($riwayat) > 0)
                                                <div class="mt-1 space-y-1">
                                                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Riwayat:</span>
                                                    @foreach($riwayat as $log)
                                                    <div class="flex justify-between items-center bg-slate-50 border border-slate-100 p-1.5 rounded text-[9px]">
                                                        <span class="text-slate-500">{{ \Carbon\Carbon::parse($log['tanggal'])->format('d M') }}</span>
                                                        <span class="font-bold text-slate-700">+{{ $log['jumlah'] }}</span>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 font-medium align-top">{{ $item['keterangan'] ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-slate-400 font-bold" style="padding: 32px 16px;">Data rincian tidak tersedia.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($pengajuanBarang->catatan_pemohon))
                    <div class="mt-auto p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-xs font-bold text-slate-700 block mb-1"><i class="fas fa-sticky-note text-blue-600 mr-1"></i> Catatan Pemohon:</span>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $pengajuanBarang->catatan_pemohon }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- DAFTAR TERMIN & FORM PEMBUATAN TERMIN --}}
            <div class="mb-6 space-y-6">
                
                    {{-- FORM PEMBUATAN TERMIN BARU (KHUSUS ADMIN/APPROVER 4) --}}
                    @if(Auth::id() == $pengajuanBarang->approver_barang_4_id || (Auth::check() && Auth::user()->role === 'admin'))
                        @if(in_array($pengajuanBarang->status, ['disetujui', 'diproses']))
                            @php
                                // Cek apakah masih ada sisa barang yang belum diproses
                                $adaSisa = false;
                                foreach($pengajuanBarang->rincian_barang ?? [] as $item) {
                                    $sisaCheck = ($item['jumlah'] ?? 0) - ($item['jumlah_diproses'] ?? 0);
                                    if ($sisaCheck > 0) { $adaSisa = true; break; }
                                }
                            @endphp

                            <div class="glass-card relative overflow-hidden border-2 border-blue-400/30">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
                                
                                <div class="flex items-center gap-3 border-b border-blue-100/50 pb-4 mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg shadow-inner"><i class="fas fa-boxes"></i></div>
                                    <div>
                                        <h4 class="text-sm font-black text-blue-800 uppercase tracking-wider">Proses Barang (Buat Termin Baru)</h4>
                                        <p class="text-[10px] text-slate-500 mt-1">Pilih barang yang siap dikirim/diproses saat ini untuk membentuk Termin/Batch pengiriman baru.</p>
                                    </div>
                                </div>

                                @if($adaSisa)
                                <form action="{{ route('pengajuan_barang.konfirmasiProses', $pengajuanBarang->id) }}" method="POST">
                                    @csrf
                                    <div class="bg-slate-50/50 rounded-xl p-4 border border-slate-200/60 mb-4">
                                        <h5 class="text-[11px] font-bold text-slate-700 mb-3"><i class="fas fa-check-square text-blue-500 mr-1.5"></i> Masukkan jumlah barang yang diproses pada termin ini:</h5>
                                        <div class="space-y-2 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                                            @foreach($pengajuanBarang->rincian_barang ?? [] as $index => $item)
                                                @php
                                                    $diminta = $item['jumlah'] ?? 0;
                                                    $diproses = $item['jumlah_diproses'] ?? 0;
                                                    $sisa = $diminta - $diproses;
                                                    $satuan = $item['satuan'] ?? '';
                                                @endphp
                                                
                                                @if($sisa > 0)
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-lg border border-slate-200 shadow-sm hover:border-blue-300 transition-colors">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <p class="text-sm font-bold text-slate-800 truncate">{{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}</p>
                                                            <span class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">Diminta: {{ $diminta }}</span>
                                                        </div>
                                                        <p class="text-[11px] font-bold text-orange-500 mt-1">Belum diproses: {{ $sisa }} {{ $satuan }}</p>
                                                    </div>
                                                    <div class="flex items-center gap-3 w-full sm:w-auto mt-2 sm:mt-0">
                                                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Proses Sekarang:</label>
                                                        <div class="relative w-32">
                                                            <input type="number" name="jumlah_diproses[{{ $index }}]" value="0" min="0" max="{{ $sisa }}" class="w-full bg-blue-50/50 border border-blue-200 rounded-md text-sm text-blue-800 font-black py-1.5 pl-2 pr-10 text-center focus:ring-2 focus:ring-blue-500/30 transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" placeholder="0">
                                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[9px] text-blue-400 font-bold">{{ $satuan }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="w-full sm:w-auto px-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-sm ml-auto">
                                        <i class="fas fa-layer-group"></i> Simpan Sebagai Termin Baru
                                    </button>
                                </form>
                                @else
                                <div class="bg-green-50 rounded-xl p-5 border border-green-200 text-center flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xl mb-1"><i class="fas fa-check-double"></i></div>
                                    <h5 class="text-sm font-bold text-green-700">Semua Barang Telah Lunas Diproses!</h5>
                                    <p class="text-[11px] text-green-600">Tidak ada sisa barang yang perlu dibuatkan termin baru. Seluruh jumlah permintaan sudah masuk ke dalam termin pengiriman.</p>
                                </div>
                                @endif
                            </div>
                        @endif
                    @endif
                {{-- STATUS MONITORING & DAFTAR TERMIN (NEW UI) --}}
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shadow-inner"><i class="fas fa-shipping-fast"></i></div>
                            <div>
                                <h3 class="text-lg font-black text-slate-800 uppercase tracking-wider">Pemantauan & Pelacakan</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium">Lacak status setiap pengiriman barang (Termin).</p>
                            </div>
                        </div>
                        <span class="px-4 py-2 rounded-xl text-sm font-black bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-sm flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                            {{ $pengajuanBarang->status_monitoring ?? 'Menunggu Diproses' }}
                        </span>
                    </div>

                    {{-- GLOBAL SUMMARY --}}
                    @php
                        $totalDiminta = 0;
                        $totalDikirim = 0;
                        foreach($pengajuanBarang->rincian_barang ?? [] as $item) {
                            $totalDiminta += ($item['jumlah'] ?? 0);
                            $totalDikirim += ($item['jumlah_diproses'] ?? 0);
                        }
                        $totalSisa = $totalDiminta - $totalDikirim;
                        $percentProgress = $totalDiminta > 0 ? round(($totalDikirim / $totalDiminta) * 100) : 0;
                    @endphp
                    <div class="glass-card mb-6 border-l-4 border-l-indigo-500 p-5 md:p-6">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div class="flex-1">
                                <h4 class="text-sm font-black text-slate-800 mb-2 uppercase tracking-wide">Proses Keseluruhan</h4>
                                <div class="w-full bg-slate-100 rounded-full h-3 mb-2 overflow-hidden border border-slate-200">
                                    <div class="bg-indigo-500 h-3 rounded-full transition-all duration-1000 relative" style="width: {{ $percentProgress }}%">
                                        <div class="absolute inset-0 bg-white/20" style="background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent); background-size: 1rem 1rem;"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between text-[11px] font-bold">
                                    <span class="text-indigo-600">{{ $percentProgress }}% Diproses</span>
                                    <span class="text-slate-500">100%</span>
                                </div>
                            </div>
                            
                            <div class="flex gap-4 md:gap-6 shrink-0">
                                <div class="text-center">
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Diminta</span>
                                    <span class="text-lg font-black text-slate-800">{{ $totalDiminta }}</span>
                                </div>
                                <div class="w-px bg-slate-200"></div>
                                <div class="text-center">
                                    <span class="block text-[10px] font-bold text-green-500 uppercase tracking-wider mb-1">Dikirim</span>
                                    <span class="text-lg font-black text-green-600">{{ $totalDikirim }}</span>
                                </div>
                                <div class="w-px bg-slate-200"></div>
                                <div class="text-center">
                                    <span class="block text-[10px] font-bold text-orange-400 uppercase tracking-wider mb-1">Sisa</span>
                                    <span class="text-lg font-black text-orange-500">{{ $totalSisa }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FEED TERMIN --}}
                    @if(!empty($pengajuanBarang->data_termin) && is_array($pengajuanBarang->data_termin) && count($pengajuanBarang->data_termin) > 0)
                        <div class="space-y-6">
                            @foreach($pengajuanBarang->data_termin as $index => $termin)
                                <div class="glass-card !p-0 overflow-hidden relative group">
                                    {{-- Accent color line based on status --}}
                                    @php
                                        $terminStatus = strtolower($termin['status_monitoring'] ?? '');
                                        $accentColor = 'bg-blue-500';
                                        $iconStatus = 'fa-truck-loading';
                                        
                                        if (str_contains($terminStatus, 'selesai') || str_contains($terminStatus, 'diterima')) {
                                            $accentColor = 'bg-emerald-500';
                                            $iconStatus = 'fa-check-circle';
                                        } elseif (str_contains($terminStatus, 'kirim') || str_contains($terminStatus, 'perjalanan')) {
                                            $accentColor = 'bg-amber-500';
                                            $iconStatus = 'fa-truck-fast';
                                        }
                                    @endphp
                                    <div class="absolute top-0 left-0 w-1.5 h-full {{ $accentColor }}"></div>
                                    
                                    {{-- HEADER TERMIN --}}
                                    <div class="bg-white border-b border-slate-100 p-4 md:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 pl-6 md:pl-8">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full {{ str_replace('bg-', 'bg-', $accentColor) }}/10 text-{{ str_replace('bg-', '', $accentColor) }} flex items-center justify-center text-lg border border-{{ str_replace('bg-', '', $accentColor) }}/20">
                                                <i class="fas {{ $iconStatus }}"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                                                    Pengiriman #{{ $termin['id_termin'] ?? $loop->iteration }}
                                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold"><i class="far fa-calendar-alt mr-1"></i> {{ $termin['tanggal_dibuat'] ?? '-' }}</span>
                                                </h3>
                                                <p class="text-[11px] font-bold text-slate-500 mt-0.5">{{ count($termin['rincian'] ?? []) }} Item Barang</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="px-3 py-1.5 rounded-lg text-xs font-black {{ str_replace('bg-', 'bg-', $accentColor) }}/10 text-{{ str_replace('bg-', '', $accentColor) }} border border-{{ str_replace('bg-', '', $accentColor) }}/20 uppercase tracking-wider">
                                                {{ $termin['status_monitoring'] ?? 'Diproses' }}
                                            </span>
                                            
                                            @if(Auth::id() == $pengajuanBarang->approver_barang_4_id || (Auth::check() && Auth::user()->role === 'admin'))
                                                <button onclick="document.getElementById('modal_termin_{{ $index }}').classList.remove('hidden')" type="button" class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 text-slate-500 hover:text-indigo-600 transition-all shadow-sm group-hover:shadow" title="Update Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- BODY TERMIN --}}
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-0 bg-slate-50/30 pl-1.5 md:pl-2">
                                        {{-- LEFT: ITEMS --}}
                                        <div class="md:col-span-2 p-4 md:p-6 border-b md:border-b-0 md:border-r border-slate-100">
                                            <h6 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center"><i class="fas fa-box-open mr-2 text-slate-300"></i> Isi Pengiriman</h6>
                                            <div class="space-y-3">
                                                @foreach($termin['rincian'] ?? [] as $rincian)
                                                    <div class="bg-white border border-slate-200 p-3 rounded-xl flex items-center gap-3 shadow-sm hover:border-indigo-200 transition-colors">
                                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center shrink-0">
                                                            <i class="fas fa-cube text-xs"></i>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="font-bold text-slate-700 text-xs truncate" title="{{ $rincian['nama_barang'] ?? 'Item' }}">{{ $rincian['nama_barang'] ?? 'Item' }}</p>
                                                            <p class="font-black text-indigo-600 text-[11px] mt-0.5">{{ $rincian['jumlah'] ?? 0 }} <span class="text-slate-400 font-semibold">{{ $rincian['satuan'] ?? '' }}</span></p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        {{-- RIGHT: TIMELINE (COMPACT) --}}
                                        <div class="md:col-span-3 p-4 md:p-6 bg-white/50 relative">
                                            <h6 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center"><i class="fas fa-map-marker-alt mr-2 text-slate-300"></i> Riwayat Pelacakan</h6>
                                            
                                            <div class="relative pl-1 space-y-0">
                                                @forelse($termin['riwayat'] ?? [] as $idx => $log)
                                                    <div class="relative z-10 flex gap-4 group/timeline pb-5">
                                                        {{-- Dot & Line Column --}}
                                                        <div class="flex flex-col items-center relative w-4 shrink-0">
                                                            {{-- Dot --}}
                                                            <div class="z-10 w-2.5 h-2.5 rounded-full {{ $idx === 0 ? 'bg-indigo-600 ring-[3px] ring-indigo-100' : 'bg-slate-300' }} mt-1.5"></div>
                                                            {{-- Line connector --}}
                                                            @if(!$loop->last)
                                                                <div class="absolute top-4 bottom-[-16px] w-[2px] {{ $idx === 0 ? 'bg-indigo-200' : 'bg-slate-200' }} z-0"></div>
                                                            @endif
                                                        </div>
                                                        
                                                        {{-- Content Column --}}
                                                        <div class="flex-1 min-w-0 pb-1">
                                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-1">
                                                                <h5 class="text-xs font-black leading-tight {{ $idx === 0 ? 'text-indigo-800' : 'text-slate-600' }}">{{ $log['status'] ?? '-' }}</h5>
                                                                <span class="text-[9px] font-bold whitespace-nowrap {{ $idx === 0 ? 'text-indigo-500' : 'text-slate-400' }}">{{ $log['waktu'] ?? '-' }}</span>
                                                            </div>
                                                            @if(!empty($log['catatan']) && $log['catatan'] !== '-')
                                                                <div class="text-[11px] leading-snug font-medium {{ $idx === 0 ? 'text-indigo-700' : 'text-slate-500' }}">
                                                                    {{ $log['catatan'] }}
                                                                </div>
                                                            @endif
                                                            <div class="text-[9px] font-bold mt-1.5 {{ $idx === 0 ? 'text-indigo-400' : 'text-slate-400' }}">
                                                                <i class="fas fa-user-circle mr-1"></i> {{ $log['oleh'] ?? '-' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-xs text-slate-400 italic">Belum ada riwayat pelacakan.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-slate-50 py-12 rounded-3xl border border-slate-200 border-dashed flex flex-col items-center justify-center gap-4 text-center">
                            <div class="w-20 h-20 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center text-3xl shadow-inner"><i class="fas fa-box-open"></i></div>
                            <div>
                                <h5 class="text-base font-black text-slate-700 mb-1">Belum Ada Pengiriman</h5>
                                <p class="text-xs text-slate-500 max-w-sm mx-auto font-medium">Barang pada pengajuan ini belum mulai diproses. Mohon tunggu update dari tim terkait.</p>
                            </div>
                            
                            @if(in_array($pengajuanBarang->status, ['diproses', 'selesai']) && (Auth::id() == $pengajuanBarang->approver_barang_4_id || (Auth::check() && Auth::user()->role === 'admin')))
                            <form action="{{ route('pengajuan_barang.migrasiTerminLama', $pengajuanBarang->id) }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" onclick="return confirm('Anda akan merangkum semua barang di pengajuan ini menjadi Termin 1 secara otomatis. Lanjutkan?')" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all text-xs flex items-center gap-2">
                                    <i class="fas fa-magic"></i> Buat Termin Awal Otomatis
                                </button>
                            </form>
                            @endif
                        </div>
                    @endif
                        
                    <script>
                        function confirmTandaiSelesai(btn) {
                            Swal.fire({
                                position: 'center',
                                title: 'Konfirmasi',
                                text: 'Anda yakin ingin menandai seluruh pengajuan ini sebagai SELESAI?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Tandai Selesai',
                                cancelButtonText: 'Batal',
                                customClass: {
                                    popup: 'bg-white shadow-[0_15px_50px_rgba(0,0,0,0.15)] border border-gray-100 rounded-3xl p-6 text-center',
                                    title: 'text-lg font-black text-slate-800 tracking-tight mt-2 m-0',
                                    htmlContainer: 'text-sm text-slate-500 font-medium leading-relaxed m-0 mt-3 mb-6',
                                    icon: 'scale-75 m-0 mx-auto border-0 text-amber-500 -mt-2',
                                    actions: 'flex justify-center gap-3 w-full m-0',
                                    confirmButton: 'bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 m-0',
                                    cancelButton: 'bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold px-5 py-2.5 rounded-xl transition-all m-0'
                                },
                                width: '340px',
                                buttonsStyling: false,
                                background: '#ffffff',
                                backdrop: 'rgba(0,0,0,0.5)',
                                showClass: { popup: 'animate__animated animate__zoomIn animate__faster' },
                                hideClass: { popup: 'animate__animated animate__zoomOut animate__faster' }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = 'tandai_selesai';
                                    input.value = '1';
                                    btn.closest('form').appendChild(input);
                                    btn.closest('form').submit();
                                }
                            });
                        }
                    </script>
                </div>
            </div>

            {{-- FORM TINDAKAN PERSETUJUAN --}}
            @php
                $user = Auth::user();
                $afterAppr1 = in_array($pengajuanBarang->status_appr_1, ['disetujui', 'skipped']);
                $afterAppr2 = $afterAppr1 && in_array($pengajuanBarang->status_appr_2, ['disetujui', 'skipped']);
                $afterAppr3 = $afterAppr2 && in_array($pengajuanBarang->status_appr_3, ['disetujui', 'skipped']);
                $isAppr1 = ($user->id == $pengajuanBarang->approver_barang_1_id && $pengajuanBarang->status_appr_1 == 'menunggu');
                $isAppr2 = ($user->id == $pengajuanBarang->approver_barang_2_id && $afterAppr1 && $pengajuanBarang->status_appr_2 == 'menunggu');
                $isAppr3 = ($user->id == $pengajuanBarang->approver_barang_3_id && $afterAppr2 && $pengajuanBarang->status_appr_3 == 'menunggu');
                
                $showForm = $isAppr1 || $isAppr2 || $isAppr3;
            @endphp            {{-- AREA TINDAKAN / ACTION --}}
            <div class="space-y-6 mt-8 pb-12">
                
                {{-- A. APPROVAL --}}
                @if($showForm)
                <div class="glass-card border-t-4 border-t-blue-500">
                    <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center">
                        <i class="fas fa-gavel text-slate-700 mr-2"></i> Tindakan Persetujuan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <form action="{{ route('pengajuan_barang.updateStatus', $pengajuanBarang) }}" method="POST" class="bg-green-50/70 p-5 rounded-2xl border border-green-100 shadow-sm flex flex-col gap-3 h-full">
                            @csrf @method('PATCH')
                            
                            <label class="block text-xs font-black text-green-800">
                                Catatan Persetujuan <span class="font-semibold text-green-600">(Opsional)</span>
                            </label>
                            <textarea name="alasan" rows="3" 
                                class="w-full p-3 border border-green-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-xs font-semibold flex-1"
                                placeholder="Tulis catatan persetujuan jika perlu..."></textarea>
                            <input type="hidden" name="status" value="disetujui">
                            <button type="submit" class="mt-auto w-full bg-green-600 hover:bg-green-700 text-white font-black py-3 px-4 rounded-xl text-xs uppercase tracking-wider transition-colors shadow-md shadow-green-200 flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> Setujui Pengajuan
                            </button>
                        </form>
                        
                        <form action="{{ route('pengajuan_barang.updateStatus', $pengajuanBarang) }}" method="POST" class="bg-red-50/70 p-5 rounded-2xl border border-red-100 shadow-sm flex flex-col gap-3 h-full">
                            @csrf @method('PATCH')
                            <label class="block text-xs font-black text-red-800">
                                Catatan Penolakan <span class="text-red-600 font-bold">*</span>
                            </label>
                            <textarea name="alasan" rows="3" 
                                class="w-full p-3 border border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white text-xs font-semibold flex-1"
                                placeholder="Tulis alasan penolakan wajib..." required></textarea>
                            <input type="hidden" name="status" value="ditolak">
                            <button type="submit" class="mt-auto w-full bg-red-600 hover:bg-red-700 text-white font-black py-3 px-4 rounded-xl text-xs uppercase tracking-wider transition-colors shadow-md shadow-red-200 flex items-center justify-center gap-2">
                                <i class="fas fa-times-circle"></i> Tolak Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- B. BATALKAN --}}
                @if(Auth::id() == $pengajuanBarang->user_id && in_array($pengajuanBarang->status, ['diajukan']))
                <div class="bg-red-50/70 rounded-3xl border border-red-100 p-6 md:p-8 text-center shadow-sm">
                    <h3 class="text-base font-black text-red-800 mb-1 uppercase tracking-tight">Batalkan Pengajuan?</h3>
                    <p class="text-xs text-red-600 mb-4 font-semibold">Tindakan ini tidak dapat diurungkan. Pengajuan barang akan dibatalkan secara permanen.</p>
                    <form action="{{ route('pengajuan_barang.cancel', $pengajuanBarang) }}" method="POST" onsubmit="confirmSubmit(event, 'Apakah Anda benar-benar yakin ingin membatalkan pengajuan barang ini?');">
                        @csrf
                        <button type="submit" class="bg-white border border-red-200 text-red-600 hover:bg-red-600 hover:text-white font-black py-2.5 px-6 rounded-xl text-xs uppercase transition shadow-sm">
                            Batalkan Pengajuan
                        </button>
                    </form>
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- KUMPULAN MODAL UPDATE STATUS RENDERED DI LUAR CONTAINER --}}
    @if(!empty($pengajuanBarang->data_termin) && is_array($pengajuanBarang->data_termin) && count($pengajuanBarang->data_termin) > 0)
        @foreach($pengajuanBarang->data_termin as $index => $termin)
            @if(Auth::id() == $pengajuanBarang->approver_barang_4_id || (Auth::check() && Auth::user()->role === 'admin'))
                <div id="modal_termin_{{ $index }}" 
                     class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity">
                    
                    <!-- Backdrop -->
                    <div class="absolute inset-0" onclick="document.getElementById('modal_termin_{{ $index }}').classList.add('hidden')"></div>

                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 relative z-10 animate-[zoomIn_0.2s_ease-out]" style="animation: zoomIn 0.2s ease-out;">
                         
                        <div class="bg-indigo-600 p-4 md:p-5 flex justify-between items-center text-white">
                            <div>
                                <h3 class="font-black text-sm uppercase tracking-wider">Update Status & Resi</h3>
                                <p class="text-[10px] text-indigo-200 font-semibold mt-0.5">Pengiriman #{{ $termin['id_termin'] ?? $loop->iteration }}</p>
                            </div>
                            <button type="button" onclick="document.getElementById('modal_termin_{{ $index }}').classList.add('hidden')" class="w-8 h-8 rounded-lg bg-indigo-700/50 hover:bg-indigo-700 flex items-center justify-center transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <form action="{{ route('pengajuan_barang.updateMonitoring', $pengajuanBarang->id) }}" method="POST" enctype="multipart/form-data" class="p-5 md:p-6">
                            @csrf
                            <input type="hidden" name="termin_id" value="{{ $termin['id_termin'] }}">
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status Baru</label>
                                    <input type="text" name="status_monitoring" list="statusOptions_{{ $index }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl text-sm font-bold text-slate-800 p-2.5 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 outline-none transition-all" placeholder="Pilih atau ketik status..." required autocomplete="off">
                                    <datalist id="statusOptions_{{ $index }}">
                                        <option value="Proses Purchasing">
                                        <option value="PO Diterbitkan">
                                        <option value="Barang Dikirim/Ekspedisi">
                                        <option value="Barang Diterima & Selesai">
                                    </datalist>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Keterangan / Nomor Resi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                    <textarea name="catatan_monitoring" rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-700 p-3 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 outline-none transition-all placeholder-slate-400" placeholder="Tuliskan keterangan detail, posisi terkini, atau nomor resi pengiriman..."></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Lampiran <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                    <div class="relative border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 p-4 text-center hover:border-indigo-400 hover:bg-indigo-50/50 transition-colors group">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-slate-400 group-hover:text-indigo-400 mb-2"></i>
                                        <p class="text-[10px] font-semibold text-slate-500 mb-1">Klik untuk memilih file lampiran (PDF/JPG/PNG)</p>
                                        <input type="file" name="lampiran_monitoring" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex gap-3 pt-5 border-t border-slate-100">
                                <button type="button" onclick="document.getElementById('modal_termin_{{ $index }}').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors flex-1 text-center">Batal</button>
                                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl text-xs transition-colors flex-[2] flex items-center justify-center gap-2 shadow-md">
                                    <i class="fas fa-save"></i> Simpan Pembaruan
                                </button>
                            </div>
                            
                            @if($pengajuanBarang->status != 'selesai')
                            <div class="mt-3">
                                <button type="button" onclick="confirmTandaiSelesai(this)" class="w-full px-5 py-2.5 bg-white hover:bg-emerald-50 text-emerald-600 border border-emerald-200 font-black rounded-xl text-xs transition-colors flex items-center justify-center gap-2 shadow-sm">
                                    <i class="fas fa-check-double"></i> Selesaikan Seluruh Pengajuan
                                </button>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</x-layout-users>