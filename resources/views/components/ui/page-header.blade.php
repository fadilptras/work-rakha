@props(['title' => '', 'subtitle' => '', 'icon' => null])
<div {{ $attributes->merge(['class' => 'ui-page-header']) }}>
  <div class="relative z-10 flex items-center gap-4 min-w-0">
    @if($icon)
      <div class="hidden md:flex w-11 h-11 rounded-xl bg-white/20 items-center justify-center text-white text-base shrink-0 shadow-inner">
        <i class="fas {{ $icon }}"></i>
      </div>
    @endif
    <div class="min-w-0">
      <h1 class="text-xl md:text-2xl font-extrabold tracking-tight leading-tight truncate">{{ $title }}</h1>
      @if($subtitle)
        <p class="text-blue-100 text-[0.8rem] mt-0.5 font-medium opacity-90 leading-snug">{{ $subtitle }}</p>
      @endif
    </div>
  </div>
  @if(trim($controls ?? '') !== '' || isset($actions))
    <div class="relative z-10 flex items-center gap-2.5 shrink-0 w-full md:w-auto">
      {{ $controls ?? '' }}
      {{ $actions ?? '' }}
      {{ $slot }}
    </div>
  @endif
</div>
