{{-- CSS bersama halaman KPI (dipakai user + admin, di luar halaman PDF).
     Cara pakai: @include('components.kpi.styles') di dalam view (sudah termasuk @push('styles')). --}}
@push('styles')
<style>
    /* == Background == */
    .mesh-bg { background-color: #ede9fe; }

    /* == Modern Back Button == */
    .btn-back-modern {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 8px 18px 8px 8px;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 9999px;
        color: #1e293b;
        font-size: 0.9rem; font-weight: 700;
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
        width: fit-content;
    }
    .btn-back-modern:hover {
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.15);
        transform: translateY(-2px);
        color: #1d4ed8;
    }
    .btn-back-modern .icon-circle {
        width: 32px; height: 32px;
        background: #fff;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #3b82f6;
        font-size: 0.85rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        transition: transform 0.3s ease;
    }
    .btn-back-modern:hover .icon-circle {
        transform: translateX(-3px);
        background: #EFF6FF;
    }
</style>
@endpush
