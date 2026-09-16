@props(['active' => false])
@php $isActive = filter_var($active, FILTER_VALIDATE_BOOLEAN); @endphp
<button type="button" {{ $attributes->merge(['class' => $isActive ? 'ui-tab ui-tab--active' : 'ui-tab']) }}>
  {{ $slot }}
</button>