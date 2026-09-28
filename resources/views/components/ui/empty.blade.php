@props([
    'icon' => 'fa-inbox',
    'title' => 'Tidak ada data',
    'message' => 'Coba ubah kata kunci pencarian atau filter.',
    'compact' => false,
])

@php
  $py = $compact ? 'py-8' : 'py-12';
@endphp

<div {{ $attributes->merge(['class' => "flex flex-col items-center justify-center $py px-6 text-center"]) }}>
    {{-- Simple abu-abu, tanpa kotak/biru --}}
    <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
        <i class="fas {{ $icon }} text-xl"></i>
    </div>
    <h3 class="text-sm font-bold text-slate-600">{{ $title }}</h3>
    <p class="text-xs text-slate-500 mt-1 max-w-sm">{{ $message }}</p>
    @if(trim($slot) !== '')
        <div class="mt-4 flex items-center justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
