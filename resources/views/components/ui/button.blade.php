{{-- Tombol aksi berlabel (pakai untuk: simpan/submit, tambah, batal, dsb).
     Varian: primary | secondary | soft-blue | soft-green | success | danger | ghost
     Ukuran : xs (28px) | sm (34px) | md (42px)
     Contoh: <x-ui.button variant="primary" size="md" type="submit" icon="fa-paper-plane" id="kirim">Kirim</x-ui.button> --}}
@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'icon' => null])
@php
  $variants = [
    'primary'   => 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm',
    'secondary' => 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm',
    'soft-blue' => 'bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 shadow-sm',
    'soft-green'=> 'bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 shadow-sm',
    'success'   => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm',
    'danger'    => 'bg-red-600 hover:bg-red-700 text-white shadow-sm',
    'ghost'     => 'text-slate-500 hover:bg-slate-100',
  ];
  $sizes = [
    'xs' => 'h-[28px] px-3 text-[11px] gap-1.5',
    'sm' => 'h-[34px] px-4 text-xs gap-1.5',
    'md' => 'h-[42px] px-6 text-sm gap-2',
  ];
@endphp
<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center font-bold rounded-xl transition-all whitespace-nowrap disabled:opacity-60 disabled:cursor-not-allowed ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md'])]) }}>
  @if($icon)<i class="fas {{ $icon }}"></i>@endif
  {{ $slot }}
</button>
