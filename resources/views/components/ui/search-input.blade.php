@props(['placeholder' => 'Search', 'icon' => 'fa-search'])
@php
  $wrapperClass = $attributes->get('class');
  $wrapperStyle = $attributes->get('style');
  $hasXData = $attributes->has('x-data');
  $xData = $attributes->get('x-data');
  $inputAttrs = $attributes->except(['class', 'style', 'x-data']);
@endphp
<div class="relative {{ $wrapperClass }}" @if($wrapperStyle) style="{{ $wrapperStyle }}" @endif @if($hasXData) x-data="{{ $xData }}" @endif>
  <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-blue-400">
    <i class="fas {{ $icon }} text-[10px]"></i>
  </div>
  <input type="text" placeholder="{{ $placeholder }}" {{ $inputAttrs->merge(['class' => 'peer ui-search']) }} />
  <button type="button" onclick="this.previousElementSibling.value=''; this.previousElementSibling.dispatchEvent(new Event('input')); this.previousElementSibling.dispatchEvent(new Event('change')); if(this.previousElementSibling.hasAttribute('x-model')){ try{ Alpine.evaluate(this.closest('[x-data]'), this.previousElementSibling.getAttribute('x-model') + ' = \'\'') }catch(e){} }" class="absolute inset-y-0 right-0 hidden peer-[:not(:placeholder-shown)]:flex items-center pr-2.5 text-blue-700 hover:text-blue-900 transition-colors" title="Clear">
    <i class="fas fa-times-circle text-[10px]"></i>
  </button>
</div>
