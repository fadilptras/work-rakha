@props(['variant' => 'light', 'padding' => 'default'])
@php
  $base = $variant === 'dark' ? 'ui-glass ui-glass--dark' : 'ui-glass';
  $pad = match($padding) { 'none' => '!p-0', 'sm' => '!p-4', 'lg' => '!p-8', default => '' };
@endphp
<div {{ $attributes->merge(['class' => trim("$base $pad")]) }}>
  {{ $slot }}
</div>
