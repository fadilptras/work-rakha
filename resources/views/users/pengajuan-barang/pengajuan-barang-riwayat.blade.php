@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
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
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px; width: fit-content;
        }
        .btn-back-modern:hover { background: rgba(255,255,255,0.95); box-shadow: 0 10px 15px -3px rgba(99,102,241,0.15); transform: translateY(-2px); color: #4338ca; }
        .btn-back-modern .icon-circle { width: 32px; height: 32px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6366f1; font-size: 0.85rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: transform 0.3s ease; }
        .btn-back-modern:hover .icon-circle { transform: translateX(-3px); background: #EEF2FF; }
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,1); border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); padding: 28px; }
        .modern-select { background: rgba(255,255,255,0.95); border: 2px solid #e2e8f0; border-radius: 14px; padding: 9px 15px; font-size: 0.85rem; color: #1e293b; font-weight: 700; outline: none; transition: all 0.2s ease; cursor: pointer; }
        .modern-select:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.15); }

        .history-card-mobile {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
        }

        @media (max-width: 767.98px) {
            .glass-card {
                padding: 16px;
                border-radius: 18px;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                background: rgba(255, 255, 255, 0.95) !important;
            }
            .modern-select {
                padding: 7px 12px;
                font-size: 0.8rem;
                border-radius: 10px;
                width: auto !important;
                flex: 1 !important;
            }
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col">

            <a href="{{ route('pengajuan_barang.index') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Form Pengajuan
            </a>

            {{-- HEADER --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-2xl md:rounded-3xl p-5 md:p-6 shadow-xl mb-4 md:mb-6 overflow-hidden border border-white/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="relative z-10 flex items-center gap-3 md:gap-4">
                    <div class="h-10 w-10 md:h-12 md:w-12 rounded-xl md:rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                        <i class="fas fa-history text-lg md:text-xl text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-base md:text-xl font-black tracking-tight text-white uppercase">Riwayat Pengajuan Barang</h1>
                        @if(!$isMobile)
                        <p class="text-blue-100 text-xs md:text-sm mt-0.5 font-medium leading-relaxed w-full">
                            Pantau status, detail, dan alur persetujuan permohonan barang yang telah Anda ajukan.
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIWAYAT CARD --}}
            <div class="glass-card space-y-6 mb-10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 pb-4 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-list-ul"></i></div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Daftar Pengajuan Anda</h3>
                            @if(!$isMobile)
                            <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Gunakan filter di sebelah kanan untuk menyaring status pengajuan.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Filter --}}
                    <form action="{{ route('pengajuan_barang.history') }}" method="GET" class="flex items-center justify-between md:justify-end gap-2.5 w-full md:w-auto m-0">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mt-0.5 whitespace-nowrap">Status Filter:</label>
                        <select name="status" class="modern-select" onchange="this.form.submit()">
                            <option value="semua" {{ request('status') == 'semua' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                            <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Menunggu Appr 1</option>
                            <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </form>
                </div>

                {{-- Tabel Desktop --}}
                <div class="overflow-x-auto hidden md:block rounded-2xl border border-slate-200/80" style="border-radius: 16px; overflow: hidden;">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50/80 text-slate-600 uppercase font-black border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left w-[15%]">Tanggal</th>
                                <th class="px-6 py-4 text-left w-[40%]">Judul Pengajuan</th>
                                <th class="px-6 py-4 text-left w-[15%]">Total Item</th>
                                <th class="px-6 py-4 text-left w-[20%]">Status</th>
                                <th class="px-6 py-4 text-center w-[10%]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($pengajuanBarangs as $pengajuan)
                                @php
                                    $statusClass = match($pengajuan->status) {
                                        'selesai' => 'bg-green-100 text-green-700 border-green-200',
                                        'ditolak' => 'bg-red-100 text-red-700 border-red-200',
                                        'dibatalkan' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'diajukan' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'diproses' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    $statusLabel = match($pengajuan->status) {
                                        'diajukan' => 'Menunggu Appr 1',
                                        'diproses' => 'Diproses',
                                        'selesai' => 'Selesai',
                                        'ditolak' => 'Ditolak',
                                        'dibatalkan' => 'Dibatalkan',
                                        default => ucfirst($pengajuan->status),
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4.5 text-slate-500 font-extrabold">{{ $pengajuan->created_at->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                                    <td class="px-6 py-4.5 text-slate-800 font-bold leading-normal">{{ $pengajuan->judul_pengajuan }}</td>
                                    <td class="px-6 py-4.5 font-black text-slate-700">{{ count($pengajuan->rincian_barang ?? []) }} Item</td>
                                    <td class="px-6 py-4.5">
                                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $statusClass }} shadow-sm">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4.5 text-center">
                                        <a href="{{ route('pengajuan_barang.show', $pengajuan->id) }}" class="inline-flex items-center gap-1 text-xs text-blue-600 font-black hover:underline bg-blue-50/70 border border-blue-100 px-3.5 py-2 rounded-xl transition hover:bg-blue-100 shadow-sm">
                                            Detail <i class="fas fa-chevron-right text-[8px] ml-0.5"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-slate-400" style="padding: 56px 24px;">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-folder-open text-4xl text-slate-200" style="margin-bottom: 12px;"></i>
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Belum ada data pengajuan barang yang sesuai.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- List Mobile --}}
                <div class="block md:hidden space-y-4">
                    @forelse ($pengajuanBarangs as $pengajuan)
                        @php
                            $statusClass = match($pengajuan->status) {
                                'selesai' => 'bg-green-100 text-green-700 border-green-200',
                                'ditolak' => 'bg-red-100 text-red-700 border-red-200',
                                'dibatalkan' => 'bg-slate-100 text-slate-700 border-slate-200',
                                'diajukan' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                'diproses' => 'bg-blue-100 text-blue-700 border-blue-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                            $statusLabel = match($pengajuan->status) {
                                'diajukan' => 'Appr 1',
                                'diproses' => 'Diproses',
                                'selesai' => 'Selesai',
                                'ditolak' => 'Ditolak',
                                'dibatalkan' => 'Dibatalkan',
                                default => ucfirst($pengajuan->status),
                            };
                        @endphp
                        <div class="history-card-mobile rounded-2xl p-4 border shadow-sm space-y-3">
                            <div class="flex justify-between items-start gap-2">
                                <div class="font-bold text-slate-800 text-sm leading-snug">{{ $pengajuan->judul_pengajuan }}</div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider border {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-bold">{{ $pengajuan->created_at->locale('id')->isoFormat('D MMMM YYYY') }}</div>
                            <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                                <div class="text-[11px] text-slate-500 font-bold">Total: <span class="text-slate-800 text-xs font-black">{{ count($pengajuan->rincian_barang ?? []) }} item</span></div>
                                <a href="{{ route('pengajuan_barang.show', $pengajuan->id) }}" class="text-xs text-blue-600 font-bold hover:underline">
                                    Detail <i class="fas fa-chevron-right text-[8px] ml-0.5"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 py-10 bg-white rounded-2xl border border-slate-200">
                            <i class="fas fa-folder-open text-3xl text-slate-200 mb-2"></i>
                            <p class="text-xs font-semibold text-slate-500">Belum ada data pengajuan barang yang sesuai.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if ($pengajuanBarangs->hasPages())
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        {{ $pengajuanBarangs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout-users>

