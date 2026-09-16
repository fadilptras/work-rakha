{{-- Badge status evaluasi KPI di tabel index. Parameter: $evaluation (nullable). --}}
@if($evaluation)
    @if(in_array($evaluation->status, ['disetujui_direktur', 'acknowledged']))
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 shadow-sm">
            <i class="fas fa-check-circle mr-1.5"></i> {{ ucfirst($evaluation->status) }}
        </span>
    @elseif($evaluation->status === 'disetujui_kepala_divisi')
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-700 shadow-sm">
            <i class="fas fa-hourglass-half mr-1.5"></i> {{ ucfirst($evaluation->status) }}
        </span>
    @else
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 shadow-sm">
            <i class="fas fa-info-circle mr-1.5"></i> {{ ucfirst($evaluation->status) }}
        </span>
    @endif
@else
    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 shadow-sm">
        <i class="fas fa-clock mr-1.5"></i> Belum Dinilai
    </span>
@endif
