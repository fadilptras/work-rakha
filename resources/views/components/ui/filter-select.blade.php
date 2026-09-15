@props(['name' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'onchange' => null])
@php
  $attrs = [];
  if ($name) $attrs['name'] = $name;
  if ($onchange) $attrs['onchange'] = $onchange;
@endphp
@php
  $wrapperClass = $attributes->get('class');
  $selectAttrs = $attributes->except('class');
@endphp
<div class="relative {{ $wrapperClass }}">
  <select {{ $selectAttrs->merge(array_merge(['class' => 'ui-filter'], $attrs)) }}>
    @if($placeholder !== null)
      <option value="" class="text-blue-400 font-normal">{{ $placeholder }}</option>
    @endif
    @foreach($options as $optValue => $optLabel)
      @if(is_int($optValue))
        @php $optValue = $optLabel; @endphp
      @endif
      <option value="{{ $optValue }}" @selected((string)$value === (string)$optValue)>{{ $optLabel }}</option>
    @endforeach
    {{ $slot }}
  </select>
  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-blue-700">
    <i class="fas fa-chevron-down text-[10px]"></i>
  </div>
</div>
