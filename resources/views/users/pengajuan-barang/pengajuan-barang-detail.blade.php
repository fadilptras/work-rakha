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
                @if(Auth::id() != $pengajuanBarang->user_id)
                    <a href="{{ route('notifikasi.index') }}" class="btn-back-modern">
                        <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                        Kembali ke Notifikasi
                    </a>
                @elseif(request('from') === 'monitoring')
                    <a href="{{ route('pengajuan_barang.monitoring_all') }}" class="btn-back-modern">
                        <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                        Kembali ke Monitoring
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
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-2xl md:rounded-3xl p-4 md:p-8 shadow-xl mb-4 md:mb-6 overflow-hidden border border-white/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
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
                    <div class="flex items-center gap-3 md:gap-5">
                        <div class="h-10 w-10 md:h-14 md:w-14 rounded-xl md:rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                            <i class="fas fa-box text-lg md:text-2xl text-white"></i>
                        </div>
                        <div>
                            <div class="text-blue-200 text-[10px] font-black uppercase tracking-widest mb-0.5">
                                #BRG-{{ str_pad($pengajuanBarang->id, 4, '0', STR_PAD_LEFT) }}
                            </div>
                            <h1 class="text-sm md:text-xl font-black tracking-tight text-white uppercase leading-snug">{{ $pengajuanBarang->judul_pengajuan }}</h1>
                            <p class="text-blue-100 text-[10px] md:text-xs mt-1 font-semibold leading-relaxed">
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
                                    <th class="px-4 py-3 text-center whitespace-nowrap">Jumlah</th>
                                    <th class="px-4 py-3 text-left min-w-[120px]">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($pengajuanBarang->rincian_barang ?? [] as $index => $item)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 text-center text-slate-500 font-extrabold">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-slate-800 font-bold leading-normal">
                                        {{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}
                                        <div class="text-[10px] text-slate-500 font-normal mt-0.5"><i class="fas fa-store mr-1 text-slate-400"></i>{{ $item['supplier'] ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-black text-blue-700 whitespace-nowrap">{{ $item['jumlah'] ?? 0 }} {{ $item['satuan'] ?? '' }}</td>
                                    <td class="px-4 py-3 text-slate-600 font-medium">{{ $item['keterangan'] ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate-400 font-bold" style="padding: 32px 16px;">Data rincian tidak tersedia.</td>
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

            {{-- LACAK PENGIRIMAN & MONITORING --}}
            @if(!empty($pengajuanBarang->status_monitoring) || !empty($pengajuanBarang->riwayat_monitoring) || Auth::id() == $pengajuanBarang->approver_barang_4_id || (Auth::check() && Auth::user()->role === 'admin'))
                <div class="glass-card mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/60 pb-3.5 mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-truck-loading"></i></div>
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Lacak Pengadaan & Status Monitoring</h4>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[11px] font-black bg-blue-50 text-blue-700 border border-blue-200 w-fit">
                            <i class="fas fa-shipping-fast mr-1 text-blue-500"></i> {{ $pengajuanBarang->status_monitoring ?? 'Belum Diproses' }}
                        </span>
                    </div>

                    @if(Auth::id() == $pengajuanBarang->approver_barang_4_id || (Auth::check() && Auth::user()->role === 'admin'))
                        <form action="{{ route('pengajuan_barang.updateMonitoring', $pengajuanBarang->id) }}" method="POST" enctype="multipart/form-data" class="mb-6 space-y-4 bg-slate-50/80 p-4 rounded-xl border border-slate-200 shadow-sm">
                            @csrf
                            <h5 class="text-xs font-bold text-slate-700 mb-2"><i class="fas fa-edit text-blue-500 mr-1"></i> Perbarui Status (Oleh Admin / Purchasing)</h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase">Status Monitoring</label>
                                    <select name="status_monitoring" class="w-full bg-white border border-slate-300 rounded-lg text-xs text-slate-700 p-2.5 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="Proses Purchasing" {{ ($pengajuanBarang->status_monitoring == 'Proses Purchasing') ? 'selected' : '' }}>Proses Purchasing</option>
                                        <option value="PO Diterbitkan" {{ ($pengajuanBarang->status_monitoring == 'PO Diterbitkan') ? 'selected' : '' }}>PO Diterbitkan</option>
                                        <option value="Sedang Diproses Vendor" {{ ($pengajuanBarang->status_monitoring == 'Sedang Diproses Vendor') ? 'selected' : '' }}>Sedang Diproses Vendor</option>
                                        <option value="Dalam Pengiriman (ekspedisi)" {{ ($pengajuanBarang->status_monitoring == 'Dalam Pengiriman (ekspedisi)') ? 'selected' : '' }}>Dalam Pengiriman (ekspedisi)</option>
                                        <option value="Barang Tiba di Gudang Rakha" {{ ($pengajuanBarang->status_monitoring == 'Barang Tiba di Gudang Rakha') ? 'selected' : '' }}>Barang Tiba di Gudang Rakha</option>
                                        <option value="Selesai / Barang Diterima" {{ ($pengajuanBarang->status_monitoring == 'Selesai / Barang Diterima') ? 'selected' : '' }}>Selesai / Barang Diterima</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase">Catatan / Keterangan</label>
                                    <input type="text" name="catatan_monitoring" class="w-full bg-white border border-slate-300 rounded-lg text-xs text-slate-700 p-2.5 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Resi JNE: 12345678, PO: 001...">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase">Lampiran (Foto Resi, PO, Faktur dll) <span class="font-normal text-slate-400">- Opsional (Maks. 2MB)</span></label>
                                    <input type="file" name="lampiran_monitoring" 
                                        class="w-full bg-white border border-slate-300 rounded-lg text-xs text-slate-700 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:cursor-pointer file:mr-4 file:py-1.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition-all cursor-pointer" 
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 pt-2">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2 px-4 rounded-lg transition flex items-center gap-2">
                                    <i class="fas fa-sync-alt"></i> Update Status
                                </button>
                                @if($pengajuanBarang->status != 'selesai')
                                <button type="button" onclick="confirmTandaiSelesai(this)" class="bg-green-600 hover:bg-green-700 text-white font-bold text-xs py-2 px-4 rounded-lg transition flex items-center gap-2">
                                    <i class="fas fa-check-double"></i> Tandai Selesai & Diterima
                                </button>
                                @endif
                            </div>
                        </form>
                        
                        <script>
                            function confirmTandaiSelesai(btn) {
                                Swal.fire({
                                    position: 'center',
                                    title: 'Konfirmasi',
                                    text: 'Anda yakin ingin menandai pengajuan ini sebagai SELESAI?',
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
                                        btn.form.appendChild(input);
                                        btn.form.submit();
                                    }
                                });
                            }
                        </script>
                    @endif

                    @if(!empty($pengajuanBarang->riwayat_monitoring))
                        <div class="relative border-l-2 border-blue-400 ml-3 space-y-5 py-2 mt-4">
                            @foreach(array_reverse($pengajuanBarang->riwayat_monitoring) as $log)
                            <div class="relative pl-6">
                                <div class="absolute w-3.5 h-3.5 rounded-full bg-blue-500 border-2 border-white" style="left: -8px; top: 3px;"></div>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-1">
                                    <span class="font-black text-blue-800">{{ $log['status'] }}</span>
                                    <span class="text-[10px] text-slate-400 font-semibold"><i class="fas fa-clock mr-1"></i> {{ $log['waktu'] }} • {{ $log['oleh'] }}</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1.5 font-medium bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">{{ $log['catatan'] }}</p>
                                @if(!empty($log['lampiran']))
                                <div class="mt-2">
                                    <a href="{{ Storage::url($log['lampiran']) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-[10px] font-bold text-slate-600 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition-all shadow-sm w-fit">
                                        <i class="fas fa-paperclip text-slate-400"></i> Lihat Lampiran
                                    </a>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

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
</x-layout-users>