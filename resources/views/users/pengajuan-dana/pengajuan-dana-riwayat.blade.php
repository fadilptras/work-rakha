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

        .modern-select {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid #e2e8f0; border-radius: 14px;
            padding: 9px 15px; font-size: 0.85rem; color: #1e293b; font-weight: 700;
            outline: none; transition: all 0.2s ease;
            cursor: pointer;
        }
        .modern-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
    </style>
    @endpush

    @if($isMobile)
        @include('users.pengajuan-dana.mobile.riwayat')
    @else
    <div class="flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800 pb-16">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-5 lg:py-6 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start">

            @php
                $fromSales = request('from') === 'sales';
                $backRoute = $fromSales ? route('pengajuan_dana.index', ['from' => 'sales']) : route('pengajuan_dana.index');
                $backText = 'Kembali ke Form Pengajuan';
            @endphp
            <div class="w-full flex justify-start">
                <x-ui.back-button :href="$backRoute" :label="$backText" />
            </div>

            <div class="hidden md:block">
                <x-ui.page-header title="Riwayat Pengajuan Dana" subtitle="Pantau status, detail, dan alur persetujuan permohonan dana operasional yang telah Anda ajukan." icon="fa-history" />
            </div>

            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Riwayat Dana</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">
                        Status & alur persetujuan pengajuan.
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-history"></i>
                </div>
            </div>

            {{-- TABS TIPE --}}
            @php
                $tipeAktif = request('tipe', 'semua');
                $tabParams = array_filter(['status' => request('status'), 'from' => request('from')]);
            @endphp
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('pengajuan_dana.history', $tabParams + ['tipe' => 'semua']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full border text-xs font-black transition-all {{ $tipeAktif === 'semua' ? 'bg-teal-600 text-white border-teal-600 shadow-md' : 'bg-white text-slate-600 border-slate-300 hover:border-teal-400' }}">
                    Semua Tipe
                </a>
                @foreach(\App\Models\PengajuanDana::TIPE_LIST as $tKey => $tLabel)
                <a href="{{ route('pengajuan_dana.history', $tabParams + ['tipe' => $tKey]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full border text-xs font-black transition-all {{ $tipeAktif === $tKey ? 'bg-teal-600 text-white border-teal-600 shadow-md' : 'bg-white text-slate-600 border-slate-300 hover:border-teal-400' }}">
                    {{ $tLabel }}
                </a>
                @endforeach
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
                    
                    {{-- Filter Form --}}
                    <form action="{{ route('pengajuan_dana.history') }}" method="GET" class="flex flex-wrap items-center justify-between md:justify-end gap-2.5 w-full md:w-auto m-0">
                        @if(request('from') === 'sales')
                        <input type="hidden" name="from" value="sales">
                        @endif
                        @if(request()->filled('tipe'))
                        <input type="hidden" name="tipe" value="{{ request('tipe') }}">
                        @endif
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mt-0.5 whitespace-nowrap">Status Filter:</label>
                        <select name="status" class="modern-select flex-1 min-w-[160px] md:flex-none" onchange="this.form.submit()">
                            <option value="semua" {{ request('status') == 'semua' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                            <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Menunggu Approver 1</option>
                            <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Menunggu Approver 2 / 3</option>
                            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </form>
                </div>

                {{-- Tabel Riwayat Desktop --}}
                <div class="overflow-x-auto hidden md:block rounded-xl border border-slate-200">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left w-[15%]">Tanggal</th>
                                <th class="px-6 py-4 text-left w-[45%]">Judul Pengajuan</th>
                                <th class="px-6 py-4 text-left w-[20%]">Total Dana</th>
                                <th class="px-6 py-4 text-left w-[15%]">Status</th>
                                <th class="px-6 py-4 text-center w-[5%]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($pengajuanDanas as $pengajuan)
                                @php
                                    $statusClass = match($pengajuan->status) {
                                        'selesai' => 'bg-green-100 text-green-700 border-green-200',
                                        'ditolak' => 'bg-red-100 text-red-700 border-red-200',
                                        'dibatalkan' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'diajukan' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'diproses' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                        'disetujui' => 'bg-teal-100 text-teal-700 border-teal-200',
                                        'proses_pembayaran' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    $statusLabel = match($pengajuan->status) {
                                        'diajukan' => 'Menunggu Approver 1',
                                        'diproses' => ($pengajuan->approver_2_status === 'menunggu')
                                            ? 'Menunggu Approver 2'
                                            : (($pengajuan->approver_3_status === 'menunggu')
                                                ? 'Menunggu Approver 3'
                                                : 'Diproses'),
                                        'disetujui', 'proses_pembayaran' => 'Menunggu Pembayaran',
                                        'selesai' => 'Selesai',
                                        'ditolak' => 'Ditolak',
                                        'dibatalkan' => 'Dibatalkan',
                                        default => ucfirst($pengajuan->status),
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4.5 text-slate-500 font-extrabold">{{ $pengajuan->created_at->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                                    <td class="px-6 py-4.5 text-slate-800 font-bold leading-normal">
                                        <div>{{ $pengajuan->judul_pengajuan }}</div>
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-teal-50 text-teal-700 border border-teal-200">{{ \App\Models\PengajuanDana::TIPE_LIST[$pengajuan->tipe_expend] ?? $pengajuan->tipe_expend }}</span>
                                    </td>
                                    <td class="px-6 py-4.5 font-black font-mono text-slate-700 text-sm">Rp {{ number_format($pengajuan->total_dana, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4.5">
                                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $statusClass }} shadow-sm">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4.5 text-center">
                                        <a href="{{ request('from') === 'sales' ? route('pengajuan_dana.show', ['pengajuanDana' => $pengajuan->id, 'from' => 'sales']) : route('pengajuan_dana.show', $pengajuan->id) }}" class="inline-flex items-center gap-1 text-xs text-blue-600 font-black hover:underline bg-blue-50/70 border border-blue-100 px-3.5 py-2 rounded-xl transition hover:bg-blue-100 shadow-sm">
                                            Detail <i class="fas fa-chevron-right text-[8px] ml-0.5"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-slate-400" style="padding: 56px 24px;">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-folder-open text-4xl text-slate-200" style="margin-bottom: 12px;"></i>
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Belum ada data pengajuan dana yang sesuai.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- List Riwayat Mobile --}}
                <div class="block md:hidden space-y-4">
                    @forelse ($pengajuanDanas as $pengajuan)
                        @php
                            $statusClass = match($pengajuan->status) {
                                'selesai' => 'bg-green-100 text-green-700 border-green-200',
                                'ditolak' => 'bg-red-100 text-red-700 border-red-200',
                                'dibatalkan' => 'bg-slate-100 text-slate-700 border-slate-200',
                                'diajukan' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                'diproses' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                'disetujui' => 'bg-teal-100 text-teal-700 border-teal-200',
                                'proses_pembayaran' => 'bg-blue-100 text-blue-700 border-blue-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                            $statusLabel = match($pengajuan->status) {
                                'diajukan' => 'Menunggu Approver 1',
                                'diproses' => ($pengajuan->approver_2_status === 'menunggu')
                                    ? 'Menunggu Approver 2'
                                    : (($pengajuan->approver_3_status === 'menunggu')
                                        ? 'Menunggu Approver 3'
                                        : 'Diproses'),
                                'disetujui', 'proses_pembayaran' => 'Menunggu Pembayaran',
                                'selesai' => 'Selesai',
                                'ditolak' => 'Ditolak',
                                'dibatalkan' => 'Dibatalkan',
                                default => ucfirst($pengajuan->status),
                            };
                        @endphp
                        <div class="history-card-mobile rounded-2xl p-4 border shadow-sm space-y-3">
                            <div class="flex justify-between items-start gap-2">
                                <div class="font-bold text-slate-800 text-sm leading-snug">
                                    <div>{{ $pengajuan->judul_pengajuan }}</div>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider bg-teal-50 text-teal-700 border border-teal-200">{{ \App\Models\PengajuanDana::TIPE_LIST[$pengajuan->tipe_expend] ?? $pengajuan->tipe_expend }}</span>
                                </div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider border {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-bold font-mono">
                                {{ $pengajuan->created_at->locale('id')->isoFormat('D MMMM YYYY') }}
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                                <div class="text-[11px] text-slate-500 font-bold">Total: <span class="text-slate-800 text-xs font-black font-mono">Rp {{ number_format($pengajuan->total_dana, 0, ',', '.') }}</span></div>
                                <a href="{{ request('from') === 'sales' ? route('pengajuan_dana.show', ['pengajuanDana' => $pengajuan->id, 'from' => 'sales']) : route('pengajuan_dana.show', $pengajuan->id) }}" class="text-xs text-blue-600 font-bold hover:underline">
                                    Detail <i class="fas fa-chevron-right text-[8px] ml-0.5"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 py-10 bg-white rounded-2xl border border-slate-200">
                            <i class="fas fa-folder-open text-3xl text-slate-200 mb-2"></i>
                            <p class="text-xs font-semibold text-slate-500">Belum ada data pengajuan dana yang sesuai.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination Links --}}
                @if ($pengajuanDanas->hasPages())
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        {{ $pengajuanDanas->links() }}
                    </div>
                @endif
            </x-ui.glass-card>
        </div>
    </div>
    @endif
</x-layout-users>
