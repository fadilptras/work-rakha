{{-- Header halaman monitoring evaluasi KPI versi dark (selaras kelola indikator).
     Parameter: $targetUser, $period, $evaluation (nullable),
                $backRoute (string), $evaluatorName (string|null). --}}
<a href="{{ route($backRoute) }}" class="inline-flex items-center gap-2 px-4 py-2 mb-6 bg-zinc-800 border border-zinc-700 text-zinc-200 text-sm font-bold rounded-lg hover:bg-zinc-700 transition w-fit">
    <i class="fas fa-arrow-left"></i> Kembali
</a>

<div class="bg-zinc-800 rounded-lg shadow-lg overflow-hidden border border-zinc-700 mb-6">
    <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-sky-500 to-sky-700 flex items-center justify-center text-white shadow-lg shrink-0">
                <i class="fas fa-user-tie text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">{{ $targetUser->name }}</h2>
                <div class="flex flex-wrap gap-2 mt-2">
                    <span class="bg-zinc-700 px-3 py-1 rounded-full text-xs font-semibold text-zinc-300 flex items-center gap-1.5"><i class="fas fa-briefcase text-sky-400"></i> {{ $targetUser->jabatan ?? '-' }}</span>
                    <span class="bg-zinc-700 px-3 py-1 rounded-full text-xs font-semibold text-zinc-300 flex items-center gap-1.5"><i class="fas fa-sitemap text-sky-400"></i> {{ ucfirst($targetUser->divisi ?? '-') }}</span>
                    @if(!empty($evaluatorName ?? null))
                        <span class="bg-amber-500/15 border border-amber-500/40 text-amber-300 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5"><i class="fas fa-user-check"></i> Dinilai oleh: {{ $evaluatorName }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="text-left md:text-right">
            <p class="text-zinc-400 text-xs font-bold tracking-wider mb-1 uppercase">Periode Penilaian</p>
            <p class="text-lg font-bold bg-zinc-700 text-white px-4 py-1.5 rounded-lg inline-block border border-zinc-600">
                {{ $period }}
            </p>
            @if($evaluation && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged']))
                <div class="mt-3">
                    <span class="bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1.5">
                        <i class="fas fa-check-circle"></i> Approved
                    </span>
                </div>
            @elseif($evaluation && $evaluation->status == 'disetujui_kepala_divisi')
                <div class="mt-3">
                    <span class="bg-amber-500/15 border border-amber-500/40 text-amber-300 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1.5">
                        <i class="fas fa-clock"></i> Menunggu Approval
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="mb-6 flex items-center gap-3 bg-sky-500/10 border border-sky-500/30 text-sky-200 px-5 py-3.5 rounded-lg text-sm">
    <i class="fas fa-eye text-lg"></i>
    <div><span class="font-bold">Mode Monitoring.</span> Halaman admin read-only — penilaian hanya oleh penilai yang ditunjuk.</div>
</div>
