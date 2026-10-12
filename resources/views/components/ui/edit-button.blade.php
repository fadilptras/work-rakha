{{-- Kompatibilitas: wrapper x-ui.icon-button aksi edit (API tidak berubah). --}}
@props(['title' => 'Edit', 'label' => null])
<x-ui.icon-button action="edit" :title="$title" {{ $attributes }}>
  @if($label)<span class="ml-1.5 hidden sm:inline text-xs">{{ $label }}</span>@endif
</x-ui.icon-button>
