@props([
    'href' => '#',
    'embedUrl' => null,
    'kode' => '-',
    'nama' => '-',
    'stok' => 0,
    'satuan' => 'Pcs',
    'habis' => false,
    'kritis' => false,
    'theme' => 'blue',
])

<a href="{{ $href }}"
   @if($embedUrl) data-kartu-modal data-embed="{{ $embedUrl }}" @endif
   {{ $attributes->merge(['class' => 'ks-card ks-card--' . $theme . ' group']) }}>
    <div class="ks-card__kode">{{ $kode }}</div>
    <div class="ks-card__nama" title="{{ $nama }}">{{ $nama }}</div>
    <div class="ks-card__foot">
        <div class="ks-card__stok {{ $habis ? 'ks-card__stok--habis' : ($kritis ? 'ks-card__stok--kritis' : '') }}">{{ $stok }}</div>
        <div class="ks-card__satuan">{{ $satuan }}</div>
    </div>
    @if($habis)
        <span class="ks-card__badge ks-card__badge--habis">Habis</span>
    @elseif($kritis)
        <span class="ks-card__badge ks-card__badge--kritis">Menipis</span>
    @endif
</a>
