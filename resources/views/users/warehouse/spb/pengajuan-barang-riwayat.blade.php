@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        body { font-family: 'Outfit', sans-serif; }
        /* == Background (sama seperti halaman form) == */
        .mesh-bg {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0;
            background-color: #ede9fe;
            background-image:
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            pointer-events: none;
        }

        .history-card-mobile {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800 pb-16">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start">

            <div class="w-full flex justify-start">
                <x-ui.back-button href="{{ route('pengajuan_barang.index') }}" label="Back to Form SPB" />
            </div>

            <div class="hidden md:block">
                <x-ui.page-header title="Riwayat Pengajuan Barang" subtitle="Pantau status, detail, dan alur persetujuan permohonan barang yang telah Anda ajukan." icon="fa-history" />
            </div>

            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Riwayat SPB</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">
                        Status & alur persetujuan pengajuan.
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-history"></i>
                </div>
            </div>

            {{-- RIWAYAT CARD --}}
            <x-ui.glass-card class="space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 pb-3 md:pb-4 mb-3 md:mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-list-ul"></i></div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Daftar Pengajuan Anda</h3>
                            @if(!$isMobile)
                            <p class="text-xs text-slate-500 font-semibold" style="margin-top: 0; margin-bottom: 0;">Gunakan filter di sebelah kanan untuk menyaring status pengajuan.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Filter --}}
                    <form action="{{ route('pengajuan_barang.history') }}" method="GET" class="flex items-center justify-end gap-2.5 w-full md:w-auto m-0">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider whitespace-nowrap">Status:</label>
                        <x-ui.filter-select name="status" onchange="this.form.submit()" value="{{ request('status', 'semua') }}" :options="['semua' => 'Semua Status', 'diajukan' => 'Menunggu Appr 1', 'diproses' => 'Diproses', 'disetujui' => 'Disetujui', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan']" class="w-full md:w-56" />
                    </form>
                </div>

                {{-- Tabel Desktop --}}
                <div class="overflow-x-auto hidden md:block rounded-xl border border-slate-200">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
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
                                        'disetujui' => 'bg-teal-100 text-teal-700 border-teal-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    $statusLabel = match($pengajuan->status) {
                                        'diajukan' => 'Menunggu Appr 1',
                                        'diproses' => 'Diproses',
                                        'disetujui' => 'Disetujui',
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
                                'disetujui' => 'bg-teal-100 text-teal-700 border-teal-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                            $statusLabel = match($pengajuan->status) {
                                'diajukan' => 'Appr 1',
                                'diproses' => 'Diproses',
                                'disetujui' => 'Disetujui',
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
            </x-ui.glass-card>
        </div>
    </div>
</x-layout-users>

