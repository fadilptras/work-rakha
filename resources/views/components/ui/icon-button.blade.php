{{-- Tombol aksi ikon 28px untuk kolom Aksi tabel (pakai untuk: ubah/hapus/lihat per baris).
     Aksi  : edit | delete | view (ikon + warna + title otomatis, bisa dioverride via prop icon/title)
     Contoh: <x-ui.icon-button action="edit" title="Ubah" class="mr-1" /> --}}
@props(['action' => 'edit', 'title' => null, 'icon' => null])
@php
  $presets = [
    'edit'   => ['icon' => 'fa-pen',       'class' => 'ui-btn--edit',   'label' => 'Ubah'],
    'delete' => ['icon' => 'fa-trash-can', 'class' => 'ui-btn--delete', 'label' => 'Hapus'],
    'view'   => ['icon' => 'fa-eye',       'class' => 'ui-btn--view',   'label' => 'Lihat'],
  ];
  $p = $presets[$action] ?? $presets['edit'];
@endphp
<button type="button" title="{{ $title ?? $p['label'] }}" {{ $attributes->merge(['class' => 'ui-btn ' . $p['class']]) }}>
  <i class="fas {{ $icon ?? $p['icon'] }} text-[11px]"></i>
  {{ $slot }}
</button>
