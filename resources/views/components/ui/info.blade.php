@props(['icon' => 'fa-database', 'label' => 'Total Records:', 'value' => null])
<div {{ $attributes->merge(['class' => 'ui-info']) }}>
  <i class="fas {{ $icon }} text-[11px]"></i>
  <span>{{ $label }} <span class="ui-info__value">{{ $value }}</span></span>
</div>
