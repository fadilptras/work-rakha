@props(['href' => '#', 'label' => 'Back', 'size' => 'md'])
@php
  $sizeClasses = $size === 'sm' ? 'text-[0.75rem] py-1 pl-1 pr-3 gap-1.5' : 'text-[0.8rem] py-1.5 pl-1.5 pr-3.5 gap-2.5';
  $iconSize = $size === 'sm' ? 'w-6 h-6 text-[0.7rem]' : 'w-7 h-7 text-xs';
@endphp
<a href="{{ $href }}" {{ $attributes->merge(['class' => "ui-back $sizeClasses"]) }}>
  <span class="ui-back__icon {{ $iconSize }}"><i class="fas fa-arrow-left"></i></span>
  <span>{{ $label }}</span>
</a>
