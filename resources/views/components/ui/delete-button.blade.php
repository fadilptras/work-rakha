@props(['title' => 'Delete', 'label' => null])
<button {{ $attributes->merge(['class' => 'ui-btn ui-btn--delete', 'title' => $title, 'type' => 'button']) }}>
  <i class="fas fa-trash-can text-[11px]"></i>
  @if($label)
    <span class="ml-1.5 hidden sm:inline text-xs">{{ $label }}</span>
  @endif
</button>
