{{-- Header halaman form evaluasi KPI (tombol kembali + kartu identitas karyawan + periode).
     Parameter: $targetUser, $period, $evaluation (nullable),
                $backRoute (string, mis. 'kpi.index'),
                $showPendingBadge (bool, default false — true hanya di view admin),
                $evaluatorName (string|null, default null — nama penilai),
                $viewMode (string: 'edit'|'approve'|'view', default 'edit'). --}}
<a href="{{ route($backRoute) }}" class="btn-back-modern">
    <div class="icon-circle">
        <i class="fas fa-arrow-left"></i>
    </div>
    Kembali
</a>
{{-- Header Card --}}
<div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6 border border-gray-100 transition-all duration-300 hover:shadow-2xl">
    <div class="bg-gradient-to-r from-blue-700 via-blue-600 to-sky-600 p-6 sm:p-8 text-white flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-full opacity-10 pointer-events-none" style="background-image: radial-gradient(circle, white 2px, transparent 2px); background-size: 20px 20px;"></div>

        <div class="flex items-center gap-5 relative z-10">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center border-2 border-white/50 shadow-inner">
                <i class="fas fa-user-tie text-3xl sm:text-4xl text-white"></i>
            </div>
            <div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight drop-shadow-md">{{ $targetUser->name }}</h2>
                <div class="flex flex-wrap gap-2 mt-2">
                    <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-semibold tracking-wide shadow-sm border border-white/30 flex items-center gap-1.5"><i class="fas fa-briefcase"></i> {{ $targetUser->jabatan ?? '-' }}</span>
                    <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-semibold tracking-wide shadow-sm border border-white/30 flex items-center gap-1.5"><i class="fas fa-sitemap"></i> {{ ucfirst($targetUser->divisi ?? '-') }}</span>
                    @if(!empty($evaluatorName ?? null))
                        <span class="bg-amber-400/90 text-amber-950 px-3 py-1 rounded-full text-xs font-bold tracking-wide shadow-sm flex items-center gap-1.5"><i class="fas fa-user-check"></i> Dinilai oleh: {{ $evaluatorName }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="text-left md:text-right relative z-10">
            <p class="text-blue-100 text-sm font-medium tracking-wide mb-1 uppercase">Periode Penilaian</p>
            <p class="text-xl sm:text-2xl font-bold bg-white text-blue-700 px-4 py-1.5 rounded-lg inline-block shadow-md border-b-4 border-blue-200">
                {{ $period }}
            </p>
            @if($evaluation && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged']))
                <div class="mt-3">
                    <span class="bg-emerald-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-md flex items-center gap-1.5 w-fit md:ml-auto">
                        <i class="fas fa-check-circle"></i> Approved
                    </span>
                </div>
            @elseif(!empty($showPendingBadge) && $evaluation && $evaluation->status == 'disetujui_kepala_divisi')
                <div class="mt-3">
                    <span class="bg-amber-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-md flex items-center gap-1.5 w-fit md:ml-auto">
                        <i class="fas fa-clock"></i> Menunggu Approval Direktur
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Penanda mode halaman: edit / approve (read-only) / view (read-only) --}}
@php $mode = $viewMode ?? 'edit'; @endphp
@if($evaluation && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged']))
    <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-2xl shadow-sm">
        <i class="fas fa-lock text-lg"></i>
        <div class="text-sm"><span class="font-bold">Sudah disetujui.</span> Halaman ini read-only, tidak dapat diubah lagi.</div>
    </div>
@elseif($mode === 'approve')
    <div class="mb-6 flex items-center gap-3 bg-amber-50 border border-amber-300 text-amber-900 px-5 py-3.5 rounded-2xl shadow-sm">
        <i class="fas fa-stamp text-lg"></i>
        <div class="text-sm"><span class="font-bold">Mode Persetujuan.</span> Anda hanya dapat memeriksa hasil lalu menyetujui — nilai tidak dapat diubah.</div>
    </div>
@elseif($mode === 'view')
    <div class="mb-6 flex items-center gap-3 bg-slate-100 border border-slate-300 text-slate-700 px-5 py-3.5 rounded-2xl shadow-sm">
        <i class="fas fa-eye text-lg"></i>
        <div class="text-sm"><span class="font-bold">Mode Lihat.</span> Anda hanya dapat melihat hasil penilaian ini.</div>
    </div>
@endif
