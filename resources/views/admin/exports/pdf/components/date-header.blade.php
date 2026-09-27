{{-- Header kolom tanggal seragam (dipakai tabel mingguan rekap absensi & lembur).
    Variabel: $tanggal (Carbon), $holidays (array Y-m-d => keterangan, opsional) --}}
@php
    $thKey = $tanggal->format('Y-m-d');
    $thLibur = $tanggal->isSunday() || (isset($holidays) && isset($holidays[$thKey]));
    $thSabtu = !$tanggal->isSunday() && $tanggal->isSaturday();
@endphp
<th class="{{ $thLibur ? 'th-libur' : ($thSabtu ? 'th-sabtu' : '') }}">
    <span class="day-num">{{ $tanggal->day }}</span>
    <span class="day-name">{{ $tanggal->isoFormat('ddd') }}</span>
</th>
