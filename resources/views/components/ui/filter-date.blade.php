@props(['name' => null, 'value' => null, 'onchange' => null])
@php
  $attrs = [];
  if ($name) $attrs['name'] = $name;
  if ($value !== null && $value !== '') $attrs['value'] = $value;
  if ($onchange) $attrs['onchange'] = $onchange;
@endphp
@php
  $wrapperClass = $attributes->get('class');
  $inputAttrs = $attributes->except('class');
@endphp
<div class="relative {{ $wrapperClass }}" x-data="{ filled: @js(($value ?? '') !== '') }">
  <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-blue-400">
    <i class="fas fa-calendar text-[11px]"></i>
  </div>
  <input type="date" @input="filled = $el.value !== ''" {{ $inputAttrs->merge(array_merge(['class' => 'ui-date', ':class' => "filled ? 'is-filled' : ''"], $attrs)) }} />
</div>