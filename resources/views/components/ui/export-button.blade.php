@props(['variant' => 'excel', 'href' => '#', 'label' => null])
@php
  $variantClass = $variant === 'pdf' ? 'ui-export--pdf' : 'ui-export--excel';
  $icon = $variant === 'pdf' ? 'fa-file-pdf' : 'fa-file-excel';
  $defaultLabel = $variant === 'pdf' ? 'PDF' : 'Excel';
@endphp
<a href="{{ $href }}" {{ $attributes->merge(['class' => "ui-export $variantClass"]) }}>
  <i class="fas {{ $icon }}"></i> {{ $label ?? $defaultLabel }}
</a>
