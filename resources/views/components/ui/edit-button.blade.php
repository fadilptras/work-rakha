@props(['title' => 'Edit', 'label' => null])
<button {{ $attributes->merge(['class' => 'ui-btn ui-btn--edit', 'title' => $title, 'type' => 'button']) }}>
  <i class="fas fa-edit text-xs"></i>
  @if($label)
    <span class="ml-1.5 hidden sm:inline text-xs">{{ $label }}</span>
  @endif
</button>
