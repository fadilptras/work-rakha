{{-- Kompatibilitas: wrapper x-ui.icon-button aksi delete (API tidak berubah). --}}
@props(['title' => 'Delete', 'label' => null])
<x-ui.icon-button action="delete" :title="$title" {{ $attributes }}>
  @if($label)<span class="ml-1.5 hidden sm:inline text-xs">{{ $label }}</span>@endif
</x-ui.icon-button>
