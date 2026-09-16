@props(['active' => false])
@php $isActive = filter_var($active, FILTER_VALIDATE_BOOLEAN); @endphp
<div {{ $attributes->merge(['class' => 'ui-tabs']) }}>
  {{ $slot }}
</div>