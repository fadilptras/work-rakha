{{-- Kop surat resmi perusahaan (sama dengan dokumen sisi user) + judul laporan.
    Variabel: $kopJudul, $kopPeriode, $kopInfo (opsional). --}}
@include('pdf.partials.kop-surat')
<div class="kop-laporan">
    <div class="kop-laporan-judul">{{ $kopJudul }}</div>
    <div class="kop-laporan-periode">Periode: <strong>{{ $kopPeriode }}</strong></div>
</div>
@if(!empty($kopInfo))
<div class="kop-info-box">
    <span>{{ $kopInfo }}</span>
    <span class="kop-info-sep">|</span>
    <span>Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
</div>
@endif
