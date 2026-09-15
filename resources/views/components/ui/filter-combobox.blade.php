@props(['name' => null, 'placeholder' => null, 'value' => null, 'options' => [], 'listId' => null, 'onchange' => null])
@php
  $wrapperClass = $attributes->get('class');
  $inputAttrs = $attributes->except(['class', 'style']);
  $wrapperStyle = $attributes->get('style');
  $listId = $listId ?? 'list-'.uniqid();
  $inputName = $name;
@endphp
<div class="relative {{ $wrapperClass }}" @if($wrapperStyle) style="{{ $wrapperStyle }}" @endif x-data="{ val: @js($value ?? '') }">
  <input list="{{ $listId }}" type="text" name="{{ $inputName }}" x-model="val" placeholder="{{ $placeholder ?? '' }}" @if($onchange) @change="{{ $onchange }}" @endif {{ $inputAttrs->merge(['class' => 'ui-combobox']) }} autocomplete="off" />
  {{-- Clear (x) — hanya tampil saat ada value, di posisi yang sama dengan chevron biar tidak double --}}
  <button type="button" x-cloak x-show="val.length > 0" @click="val = ''; $nextTick(() => $el.closest('form')?.submit())" class="absolute inset-y-0 right-0 flex items-center pr-2.5 pl-1 text-blue-700 hover:text-blue-900 transition-colors" title="Clear">
    <i class="fas fa-times-circle text-xs"></i>
  </button>
  {{-- Chevron — hanya tampil saat kosong, hide native arrow via CSS di components.css --}}
  <div x-cloak x-show="!val || val.length === 0" class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-blue-700">
    <i class="fas fa-chevron-down text-[10px]"></i>
  </div>
  {{ $slot }}
</div>
