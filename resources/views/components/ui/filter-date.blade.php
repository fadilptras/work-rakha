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
  <input type="date" x-ref="input" @input="filled = $el.value !== ''" {{ $inputAttrs->merge(array_merge(['class' => 'ui-date', ':class' => "filled ? 'is-filled' : ''"], $attrs)) }} />
  {{-- Clear X — kayak filter-select/combobox & search, muncul hanya saat terisi --}}
  <button type="button" x-cloak x-show="filled" @click="filled = false; $refs.input.value = ''; $refs.input.dispatchEvent(new Event('input')); $refs.input.dispatchEvent(new Event('change')); $nextTick(() => $refs.input.closest('form')?.submit())" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-blue-700 hover:text-blue-900 transition-colors" title="Clear">
    <i class="fas fa-times-circle text-xs"></i>
  </button>
</div>