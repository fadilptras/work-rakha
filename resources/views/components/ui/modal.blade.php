{{-- Modal generik: <x-ui.modal id="..." title="..." subtitle="..." icon="fa-box" maxWidth="max-w-lg"> body <x-slot:footer>…</x-ui.modal>
     Cara pakai dari JS: uiModalOpen('id-modal') / uiModalClose('id-modal').
     Tombol dengan atribut data-modal-close otomatis menutup modal. --}}
@props([
    'id' => 'ui-modal',
    'maxWidth' => 'max-w-md',
    'title' => '',
    'subtitle' => '',
    'icon' => null,
    'iconClass' => 'bg-blue-600 text-white',
    'closeable' => true,
])
<div id="{{ $id }}" data-ui-modal {{ $attributes->merge(['class' => 'hidden fixed inset-0 z-[100] items-center justify-center p-4']) }} role="dialog" aria-modal="true">
    <div data-ui-modal-backdrop class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm opacity-0 transition-opacity duration-200" @if($closeable) data-modal-close @endif></div>
    <div data-ui-modal-panel class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 w-full {{ $maxWidth }} overflow-hidden max-h-[90vh] flex flex-col opacity-0 scale-95 transition-all duration-200">
        @if($title || $icon)
        <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                @if($icon)
                <span class="w-9 h-9 rounded-xl {{ $iconClass }} flex items-center justify-center shrink-0 shadow-sm"><i class="fas {{ $icon }} text-xs"></i></span>
                @endif
                <div class="min-w-0">
                    <h5 id="{{ $id }}-title" class="font-bold text-sm text-slate-800 leading-tight">{{ $title }}</h5>
                    @if($subtitle)<p class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $subtitle }}</p>@endif
                </div>
            </div>
            @if($closeable)
            <button type="button" data-modal-close class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0" title="Tutup"><i class="fas fa-times text-xs"></i></button>
            @endif
        </div>
        @endif
        <div class="p-5 space-y-4 overflow-y-auto">
            {{ $slot }}
        </div>
        @if(isset($footer))
        <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex gap-2 justify-end shrink-0">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>

@once
@push('scripts')
<script>
// Pengendali generik x-ui.modal (dimuat sekali walau komponen dipakai berkali-kali).
// Pakai: uiModalOpen('id-modal') / uiModalClose('id-modal'), atau tombol [data-modal-close] di dalam modal.
(function () {
    if (window.__uiModalInit) return;
    window.__uiModalInit = true;

    function backdropOf(m) { return m.querySelector('[data-ui-modal-backdrop]'); }
    function panelOf(m) { return m.querySelector('[data-ui-modal-panel]'); }

    function openModal(m) {
        if (!m || !m.classList.contains('hidden')) return;
        // Pindah ke body: parent ber-backdrop-blur merusak posisi `fixed`.
        if (m.parentElement !== document.body) document.body.appendChild(m);
        m.classList.remove('hidden');
        m.classList.add('flex');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(function () { requestAnimationFrame(function () {
            var b = backdropOf(m), p = panelOf(m);
            if (b) b.classList.remove('opacity-0');
            if (p) p.classList.remove('opacity-0', 'scale-95');
        }); });
    }

    function closeModal(m) {
        if (!m || m.classList.contains('hidden')) return;
        var b = backdropOf(m), p = panelOf(m);
        if (b) b.classList.add('opacity-0');
        if (p) p.classList.add('opacity-0', 'scale-95');
        setTimeout(function () {
            m.classList.add('hidden');
            m.classList.remove('flex');
            document.body.style.overflow = '';
        }, 180);
    }

    window.uiModalOpen = function (id) { var m = document.getElementById(id); if (m) openModal(m); };
    window.uiModalClose = function (id) { var m = document.getElementById(id); if (m) closeModal(m); };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-ui-modal]').forEach(function (m) {
            m._uiOpen = function () { openModal(m); };
            m._uiClose = function () { closeModal(m); };
            m.querySelectorAll('[data-modal-close]').forEach(function (btn) {
                btn.addEventListener('click', function () { closeModal(m); });
            });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('[data-ui-modal]:not(.hidden)').forEach(closeModal);
            }
        });
    });
})();
</script>
@endpush
@endonce
