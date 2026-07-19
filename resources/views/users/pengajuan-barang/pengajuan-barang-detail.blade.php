<x-layout-users>
    <x-slot:title>Detail Pengajuan Barang</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        html { scroll-behavior: smooth; }
        .mesh-bg {
            background-color: #f0f6fc;
            background-image:
                radial-gradient(at 40% 20%, rgba(147, 197, 253, 0.45) 0px, transparent 50%),
                radial-gradient(at 80% 0%, rgba(167, 139, 250, 0.35) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(191, 219, 254, 0.45) 0px, transparent 50%),
                radial-gradient(at 80% 50%, rgba(139, 92, 246, 0.25) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(221, 214, 254, 0.4) 0px, transparent 50%),
                radial-gradient(at 80% 100%, rgba(96, 165, 250, 0.35) 0px, transparent 50%),
                radial-gradient(at 0% 0%, rgba(238, 242, 255, 0.6) 0px, transparent 50%);
            background-attachment: fixed;
        }
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float-delayed { animation: float 10s ease-in-out infinite; animation-delay: 2s; }
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
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[10%] left-[5%] w-32 h-32 bg-white/40 backdrop-blur-md border border-white/50 rounded-full animate-float"></div>
            <div class="absolute bottom-[15%] right-[10%] w-48 h-48 bg-white/30 backdrop-blur-md border border-white/40 rounded-full animate-float-delayed"></div>
            <div class="absolute inset-0" style="background-image: radial-gradient(rgba(100, 116, 139, 0.1) 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">

            {{-- NAVIGASI --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <a href="{{ route('pengajuan_barang.history') }}" class="btn-back-modern">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    Kembali ke Riwayat
                </a>

                @if(Auth::id() == $pengajuanBarang->user_id || Auth::user()->role === 'admin')
                <a href="{{ route('pengajuan_barang.download', $pengajuanBarang) }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-black text-white bg-red-600 rounded-xl shadow-lg hover:bg-red-700 transition-all duration-300 w-full sm:w-auto">
                    <i class="fas fa-file-pdf"></i> Cetak PDF
                </a>
                @endif
            </div>

            {{-- HEADER UTAMA --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-3xl p-6 md:p-8 shadow-xl mb-6 overflow-hidden border border-white/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                @php
                    $badgeClass = match($pengajuanBarang->status) {
                        'selesai' => 'bg-green-100 text-green-700 border-green-200',
                        'ditolak' => 'bg-red-100 text-red-700 border-red-200',
                        'diproses' => 'bg-blue-100 text-blue-700 border-blue-200',
                        'dibatalkan' => 'bg-slate-200 text-slate-700 border-slate-300',
                        default => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    };
                    $badgeLabel = match($pengajuanBarang->status) {
                        'diajukan' => 'Menunggu Persetujuan',
                        'diproses' => 'Sedang Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                        'dibatalkan' => 'Dibatalkan',
                        default => ucfirst($pengajuanBarang->status),
                    };
                @endphp
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-5">
                        <div class="h-14 w-14 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                            <i class="fas fa-box text-2xl text-white"></i>
                        </div>
                        <div>
                            <div class="text-blue-200 text-[11px] font-black uppercase tracking-widest mb-1">
                                #BRG-{{ str_pad($pengajuanBarang->id, 4, '0', STR_PAD_LEFT) }}
                            </div>
                            <h1 class="text-xl md:text-2xl font-black tracking-tight text-white">{{ $pengajuanBarang->judul_pengajuan }}</h1>
                            <p class="text-blue-200 text-xs mt-1 font-semibold">
                                Diajukan: {{ $pengajuanBarang->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider border {{ $badgeClass }} shadow-sm">
                        {{ $badgeLabel }}
                    </span>
                </div>
            </div>

            {{-- SESSION MESSAGES --}}
            
            

            {{-- GRID: INFO PEMOHON + TIMELINE --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

                {{-- INFO PEMOHON --}}
                <div class="glass-card lg:col-span-1">
                    <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-user-circle"></i></div>
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Informasi Pemohon</h4>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center border-b border-slate-50 pb-2.5">
                            <span class="text-slate-500 font-semibold text-xs">Nama</span>
                            <span class="font-bold text-slate-800 text-xs text-right">{{ $pengajuanBarang->user->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-50 pb-2.5">
                            <span class="text-slate-500 font-semibold text-xs">Divisi</span>
                            <span class="font-bold text-slate-800 text-xs text-right">{{ $pengajuanBarang->divisi }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-50 pb-2.5">
                            <span class="text-slate-500 font-semibold text-xs">Email</span>
                            <span class="font-bold text-slate-800 text-xs text-right">{{ $pengajuanBarang->user->email ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-semibold text-xs">Tanggal</span>
                            <span class="font-bold text-slate-800 text-xs text-right">{{ $pengajuanBarang->created_at->format('d F Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- TIMELINE PERSETUJUAN --}}
                <div class="glass-card lg:col-span-2">
                    <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-history"></i></div>
                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Timeline Persetujuan</h4>
                    </div>
                    <div class="space-y-3">
                        @foreach ([
                            ['label' => 'Tahap 1', 'approver' => $pengajuanBarang->approver1, 'status' => $pengajuanBarang->status_appr_1],
                            ['label' => 'Tahap 2', 'approver' => $pengajuanBarang->approver2, 'status' => $pengajuanBarang->status_appr_2],
                            ['label' => 'Tahap 3', 'approver' => $pengajuanBarang->approver3, 'status' => $pengajuanBarang->status_appr_3],
                            ['label' => 'Tahap 4 (Final)', 'approver' => $pengajuanBarang->approver4, 'status' => $pengajuanBarang->status_appr_4],
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
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">{{ $tahap['label'] }}</span>
                                            <h4 class="text-xs font-black text-slate-800 leading-tight mt-0.5">{{ $tahap['approver']->name }}</h4>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider border {{ $badgeAppr }}">
                                            {{ ucfirst($tahap['status']) }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- RINCIAN BARANG (TABEL) --}}
            <div class="glass-card mb-6">
                <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-list-ul"></i></div>
                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Rincian Barang yang Diajukan</h4>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200/80" style="border-radius: 16px; overflow: hidden;">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-black border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-center w-16">No</th>
                                <th class="px-6 py-4 text-left">Deskripsi Barang</th>
                                <th class="px-6 py-4 text-center w-36">Satuan</th>
                                <th class="px-6 py-4 text-center w-28">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($pengajuanBarang->rincian_barang ?? [] as $index => $item)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4.5 text-center text-slate-500 font-extrabold">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4.5 text-slate-800 font-bold leading-normal">{{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}</td>
                                <td class="px-6 py-4.5 text-center text-slate-600 font-semibold">{{ $item['satuan'] ?? '-' }}</td>
                                <td class="px-6 py-4.5 text-center font-black text-blue-700">{{ $item['jumlah'] ?? 0 }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-slate-400 font-bold" style="padding: 32px 16px;">Data rincian tidak tersedia.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FORM TINDAKAN PERSETUJUAN --}}
            @php
                $user = Auth::user();
                $afterAppr1 = in_array($pengajuanBarang->status_appr_1, ['disetujui', 'skipped']);
                $afterAppr2 = in_array($pengajuanBarang->status_appr_2, ['disetujui', 'skipped']);
                $afterAppr3 = in_array($pengajuanBarang->status_appr_3, ['disetujui', 'skipped']);
                $isAppr1 = ($user->id == $pengajuanBarang->approver_barang_1_id && $pengajuanBarang->status_appr_1 == 'menunggu');
                $isAppr2 = ($user->id == $pengajuanBarang->approver_barang_2_id && $afterAppr1 && $pengajuanBarang->status_appr_2 == 'menunggu');
                $isAppr3 = ($user->id == $pengajuanBarang->approver_barang_3_id && $afterAppr2 && $pengajuanBarang->status_appr_3 == 'menunggu');
                $isAppr4 = ($user->id == $pengajuanBarang->approver_barang_4_id && $afterAppr3 && $pengajuanBarang->status_appr_4 == 'menunggu');
                $showForm = $isAppr1 || $isAppr2 || $isAppr3 || $isAppr4;
            @endphp

            @if($showForm)
            <div class="glass-card mb-10 border-t-4 border-blue-500">
                <div class="flex items-center gap-3 border-b border-slate-200/60" style="padding-bottom: 14px; margin-bottom: 20px;">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-gavel"></i></div>
                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                        Tindakan Persetujuan {{ $isAppr4 ? '(Final)' : '' }}
                    </h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <form action="{{ route('pengajuan_barang.updateStatus', $pengajuanBarang) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="bg-green-50 p-6 rounded-2xl border border-green-100 text-center h-full flex flex-col">
                            <label class="block text-sm font-bold text-green-800 mb-2">Setujui Pengajuan</label>
                            <textarea name="alasan" rows="3" class="w-full p-3 border border-green-200 rounded-xl mb-3 text-sm focus:ring-green-500 focus:border-green-500" placeholder="Catatan persetujuan (opsional)..."></textarea>
                            <input type="hidden" name="status" value="disetujui">
                            <button type="submit" class="mt-auto w-full bg-green-600 text-white font-bold py-3 rounded-xl hover:bg-green-700 transition shadow-md">Setujui Sekarang</button>
                        </div>
                    </form>
                    <form action="{{ route('pengajuan_barang.updateStatus', $pengajuanBarang) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="bg-red-50 p-6 rounded-2xl border border-red-100 text-center h-full flex flex-col">
                            <label class="block text-sm font-bold text-red-800 mb-2">Tolak Pengajuan <span class="text-red-500">*</span></label>
                            <textarea name="alasan" rows="3" class="w-full p-3 border border-red-200 rounded-xl mb-3 text-sm focus:ring-red-500 focus:border-red-500" placeholder="Wajib isi alasan penolakan..." required></textarea>
                            <input type="hidden" name="status" value="ditolak">
                            <button type="submit" class="mt-auto w-full bg-red-600 text-white font-bold py-3 rounded-xl hover:bg-red-700 transition shadow-md">Tolak Pengajuan</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- TOMBOL BATALKAN --}}
            @if(Auth::id() == $pengajuanBarang->user_id && in_array($pengajuanBarang->status, ['diajukan']))
            <div class="mb-10 flex justify-end">
                <form action="{{ route('pengajuan_barang.cancel', $pengajuanBarang) }}" method="POST" onsubmit="confirmSubmit(event, 'Yakin ingin membatalkan pengajuan ini?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-black text-white bg-slate-600 rounded-xl shadow hover:bg-slate-700 transition">
                        <i class="fas fa-times-circle"></i> Batalkan Pengajuan
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-layout-users>


