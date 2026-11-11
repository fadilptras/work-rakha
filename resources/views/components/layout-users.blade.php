@props(['title' => 'Dashboard', 'fluid' => false])
@php
    $isTabEmbed = request()->boolean('tab_embed')
        || request()->header('Sec-Fetch-Dest') === 'iframe'
        || request()->header('X-Tab-Embed');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $title ?? 'Dashboard' }}</title>
    
    <meta name="theme-color" content="#2563eb"> 
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        if (window.self !== window.top) {
            document.documentElement.classList.add('is-tab-frame');
        }
    </script>
    @stack('styles')
    <style>
        html { margin: 0 !important; padding: 0 !important; height: 100% !important; background-color: #ede9fe; }
        body { margin: 0 !important; padding: 0 !important; height: 100% !important; }
        @media (min-width: 768px) {
            body {
                overflow: hidden !important;
                height: 100dvh !important;
                max-height: 100dvh !important;
            }
            #app-shell {
                height: 100dvh !important;
                max-height: 100dvh !important;
                overflow: hidden !important;
            }
        }
        html.is-tab-frame,
        html.is-tab-frame body {
            height: 100% !important;
            max-height: 100% !important;
            overflow: hidden !important;
            background-color: #ede9fe !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        html.is-tab-frame #sidebar,
        html.is-tab-frame #sidebar-submenu,
        html.is-tab-frame #sidebar-overlay,
        html.is-tab-frame #sidebar-submenu-backdrop,
        html.is-tab-frame #app-topbar { display: none !important; }
        html.is-tab-frame #app-shell {
            padding-left: 0 !important;
            height: 100% !important;
            max-height: 100% !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }
        html.is-tab-frame #app-main {
            height: 100% !important;
            max-height: 100% !important;
            min-height: 0 !important;
            overflow: hidden !important;
            flex: 1 1 0% !important;
        }
        .ui-tab-pane { width: 100%; height: 100%; min-height: 0; }
        #tab-pane-initial { display: flex; flex-direction: column; flex: 1 1 0% !important; min-height: 0 !important; height: 100% !important; max-height: 100% !important; overflow: hidden !important; }
        .ui-tab-frame {
            width: 100% !important;
            height: 100% !important;
            max-height: 100% !important;
            min-height: 0 !important;
            flex: 1 1 0% !important;
            border: 0;
            display: block;
            background-color: #ede9fe;
        }
        #app-main {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
        }
        /* Tab Loading Spinner */
        .ui-tab-loader {
            position: absolute;
            inset: 0;
            z-index: 35;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(237, 233, 254, 0.7);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            pointer-events: none;
            transition: opacity .2s ease;
        }
        .ui-tab-loader.hidden {
            display: none !important;
        }
        .ui-tab-loader__box {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9999px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
        }
        .ui-tab-loader__icon {
            color: #2563eb;
            font-size: 14px;
        }
        .ui-tab-loader__text {
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        #sidebar { transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1), translate .3s cubic-bezier(0.4, 0, 0.2, 1); will-change: transform, translate; }

        /* Panel submenu melayang (gaya Accurate), konten di bawahnya tidak bergeser */
        #sidebar-submenu {
            top: 4.5rem;
            max-height: calc(100dvh - 5.25rem);
            transform: translateX(-.75rem) scale(.98);
            transform-origin: top left;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .18s ease, transform .2s ease, visibility .2s;
            will-change: transform, opacity;
        }
        #sidebar-submenu.is-open { transform: none; opacity: 1; visibility: visible; pointer-events: auto; }

        @media (min-width: 1024px) {
            #app-shell { padding-left: 5rem; }
        }
        @media (max-width: 1023.98px) {
            #sidebar-submenu { width: min(38rem, calc(100vw - 5.5rem)); }
        }
        .sidebar-submenu-group[hidden] { display: none; }

        /* Kotak menu ala Accurate */
        .submenu-tile {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            min-height: 5rem;
            padding: .7rem .5rem;
            border: 1.5px solid var(--tile-border, #e2e8f0);
            border-radius: .55rem;
            background: var(--tile-bg, #fff);
            color: var(--tile-color, #334155);
            text-align: center;
            font-size: .72rem;
            font-weight: 600;
            line-height: 1.2;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }
        .submenu-tile i { font-size: 1.25rem; }
        .submenu-tile:hover { transform: translateY(-2px); border-color: currentColor; box-shadow: 0 8px 20px rgba(15, 23, 42, .1); }
        .submenu-tile.is-active { border-width: 2px; box-shadow: 0 6px 16px rgba(15, 23, 42, .1); }
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        /* FIX gap di atas topbar (navbar + tab) */
        #app-topbar { top: 0 !important; margin: 0 !important; }
        #app-topbar > header { padding-top: env(safe-area-inset-top) !important; }

        /* Jarak rapi & presisi antara multiple tabs/topbar dan konten halaman */
        #app-shell > main {
            padding-top: 0 !important;
        }

        /* Jarak mengambang (floating gap) untuk header sticky saat scroll agar tidak menempel rapat ke navbar/tabbar */
        .ui-page-header {
            top: calc(var(--app-topbar-h, 72px) + 0.75rem) !important;
        }

        /* Posisi panah kanan (chevron) pada sidebar group button: berada di sisi paling kanan tombol */
        .sidebar-group-btn {
            position: relative !important;
        }
        .sidebar-group-chevron {
            position: absolute !important;
            right: 4px !important;
            top: 50% !important;
            transform: translateY(-50%);
            font-size: 8px !important;
            line-height: 1 !important;
            opacity: 0.7 !important;
            pointer-events: none !important;
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .sidebar-group-btn[aria-expanded="true"] .sidebar-group-chevron,
        .sidebar-group-chevron.is-open {
            transform: translateY(-50%) rotate(180deg) !important;
        }

        /* Modal Backdrop & Canvas — Fullscreen, bersih, bayangan seragam tanpa offset */
        .modal-content-canvas {
            position: fixed !important;
            inset: 0 !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            min-height: 100vh !important;
            z-index: 9999 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            overflow-y: auto !important;
            background-color: rgba(15, 23, 42, 0.55) !important;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            padding: 1rem !important;
            box-sizing: border-box !important;
        }
        .modal-content-canvas.hidden,
        .modal-content-canvas[style*="display: none"] {
            display: none !important;
        }
        .modal-content-canvas:not(.hidden):not([style*="display: none"]) {
            display: flex !important;
        }
        /* Hindari penggandaan backdrop gelap pada modal bertumpuk */
        .modal-content-canvas .modal-content-canvas,
        .modal-content-canvas.modal-nested {
            background-color: rgba(15, 23, 42, 0.25) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        /* Dialog konfirmasi SweetAlert2: Fullscreen menutup 100% seluruh layar dengan bayangan backdrop rapi */
        .swal2-container {
            position: fixed !important;
            inset: 0 !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            z-index: 100100 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 1rem !important;
            box-sizing: border-box !important;
        }
        .swal2-container:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
            background-color: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
        }
        body.swal2-toast-shown .swal2-container {
            background-color: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            pointer-events: none !important;
        }
        body.swal2-toast-shown .swal2-popup {
            pointer-events: auto !important;
        }
        .swal2-popup {
            margin: auto !important;
        }
    </style>
</head>

<body class="font-sans flex flex-col min-h-screen m-0 p-0" style="background-color: #ede9fe;">

    @php
        $activeGroup = request('from') === 'admin'
            ? 'admin'
            : (request('from') === 'warehouse'
                ? 'warehouse'
                : (request('from') === 'purchasing'
                    ? 'purchasing'
                    : (request()->routeIs('hrd.*') || request()->routeIs('kpi.*')
                        ? 'hrd'
                        : (request()->routeIs('suppliers.*') || request()->routeIs('pengajuan_barang.monitoring_all')
                            ? 'purchasing'
                            : (request()->routeIs('warehouse.product.*')
                                ? (request('from') === 'purchasing' ? 'purchasing' : 'warehouse')
                                : (request()->routeIs('warehouse.*')
                                    ? 'warehouse'
                                    : (request()->routeIs('pengajuan_dana.*')
                                        ? 'dana'
                                        : ((request()->routeIs('pengajuan.center') || request()->routeIs('admin.approvers.*') || request()->routeIs('admin.holidays.*') || request()->routeIs('admin.agenda.*') || request()->routeIs('admin.*'))
                                            ? 'admin'
                                            : ((request()->routeIs('sales.*') || request()->routeIs('crm.*'))
                                                ? 'sales'
                                                : null)))))))));

        $sidebarGroups = [
            'dana' => [
                'label'  => 'Pengajuan Dana',
                'icon'   => 'fa-solid fa-coins',
                'accent' => '#2563eb',
                'items'  => [
                    ['label' => 'Form Pengajuan Dana',   'route' => 'pengajuan_dana.index',          'icon' => 'fa-file-invoice-dollar', 'bg' => '#eff6ff', 'border' => '#dbeafe', 'color' => '#2563eb', 'active' => request()->routeIs('pengajuan_dana.index')],
                    ['label' => 'Monitoring Pengajuan',  'route' => 'pengajuan_dana.monitoring_all', 'icon' => 'fa-desktop',             'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'color' => '#16a34a', 'active' => request()->routeIs('pengajuan_dana.monitoring_all')],
                ],
            ],
            'admin' => [
                'label'    => 'General Affair (Admin)',
                'icon'     => 'fa-solid fa-briefcase',
                'accent'   => '#dc2626',
                'disabled' => true,
                'badge'    => 'Tahap Pengembangan',
                'items' => [
                    ['label' => 'Pusat Pengajuan',          'route' => 'pengajuan.center',      'icon' => 'fa-clipboard-check', 'bg' => '#f8fafc', 'border' => '#e2e8f0', 'color' => '#475569', 'active' => request()->routeIs('pengajuan.center')],
                    ['label' => 'Pengaturan Approver',      'route' => 'admin.approvers.index', 'icon' => 'fa-user-check',      'bg' => '#eff6ff', 'border' => '#dbeafe', 'color' => '#2563eb', 'active' => request()->routeIs('admin.approvers.*')],
                    ['label' => 'Hari Libur & Operasional', 'route' => 'admin.holidays.index',  'icon' => 'fa-umbrella-beach',  'bg' => '#fff1f2', 'border' => '#fecdd3', 'color' => '#e11d48', 'active' => request()->routeIs('admin.holidays.*')],
                    ['label' => 'Agenda Kegiatan',          'route' => 'admin.agenda.index',    'icon' => 'fa-calendar-week',   'bg' => '#fefce8', 'border' => '#fef08a', 'color' => '#ca8a04', 'active' => request()->routeIs('admin.agenda.*') || request()->routeIs('agendas.*')],
                ],
            ],
            'hrd' => [
                'label'     => 'HRD & People Operations',
                'icon'      => 'fa-solid fa-user-tie',
                'text_icon' => 'HRD',
                'accent'    => '#6366f1',
                'items' => [
                    ['label' => 'Data Karyawan', 'route' => 'hrd.karyawan.index',  'params' => ['from' => 'hrd'], 'icon' => 'fa-users',          'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'color' => '#16a34a', 'active' => request()->routeIs('hrd.karyawan.*')],
                    ['label' => 'Rekap Absen',   'route' => 'hrd.absensi.index',   'params' => ['from' => 'hrd'], 'icon' => 'fa-calendar-check', 'bg' => '#eef2ff', 'border' => '#e0e7ff', 'color' => '#4f46e5', 'active' => request()->routeIs('hrd.absensi.*')],
                    ['label' => 'Rekap Cuti',    'route' => 'hrd.cuti.index',      'params' => ['from' => 'hrd'], 'icon' => 'fa-umbrella-beach',  'bg' => '#faf5ff', 'border' => '#f3e8ff', 'color' => '#9333ea', 'active' => request()->routeIs('hrd.cuti.*')],
                    ['label' => 'Rekap Lembur',  'route' => 'hrd.lembur.index',    'params' => ['from' => 'hrd'], 'icon' => 'fa-business-time',   'bg' => '#fffbeb', 'border' => '#fde68a', 'color' => '#d97706', 'active' => request()->routeIs('hrd.lembur.*')],
                    ['label' => 'Log Aktivitas', 'route' => 'hrd.aktivitas.index', 'params' => ['from' => 'hrd'], 'icon' => 'fa-clipboard-list', 'bg' => '#f0fdfa', 'border' => '#ccfbf1', 'color' => '#0d9488', 'active' => request()->routeIs('hrd.aktivitas.*')],
                    ['label' => 'Evaluasi KPI',  'route' => 'hrd.kpi.index',       'params' => ['from' => 'hrd'], 'icon' => 'fa-chart-bar',       'bg' => '#eff6ff', 'border' => '#dbeafe', 'color' => '#2563eb', 'active' => request()->routeIs('hrd.kpi.index') || request()->routeIs('kpi.*')],
                    ['label' => 'Pengaturan KPI', 'route' => 'hrd.kpi.indicators.index', 'params' => ['from' => 'hrd'], 'icon' => 'fa-sliders', 'bg' => '#f8fafc', 'border' => '#e2e8f0', 'color' => '#475569', 'active' => request()->routeIs('hrd.kpi.indicators.*') || request()->routeIs('hrd.kpi.evaluators.*')],
                    ['label' => 'Rekap Agenda',  'route' => 'hrd.agenda.index',    'params' => ['from' => 'hrd'], 'icon' => 'fa-calendar-week',   'bg' => '#fefce8', 'border' => '#fef08a', 'color' => '#ca8a04', 'active' => request()->routeIs('hrd.agenda.*') || (request()->routeIs('admin.agenda.*') && request('from') === 'hrd')],
                ],
            ],
            'purchasing' => [
                'label'    => 'Purchasing',
                'icon'     => 'fa-solid fa-cart-shopping',
                'accent'   => '#ea580c',
                'disabled' => true,
                'badge'    => 'Tahap Pengembangan',
                'items' => [
                    ['label' => 'Monitoring SPB', 'route' => 'pengajuan_barang.monitoring_all', 'params' => ['from' => 'purchasing'], 'icon' => 'fa-boxes-packing', 'bg' => '#fffbeb', 'border' => '#fde68a', 'color' => '#d97706', 'active' => request()->routeIs('pengajuan_barang.monitoring_all')],
                    ['label' => 'Data Product',   'route' => 'warehouse.index',         'params' => ['from' => 'purchasing', 'tab' => 'data-product'], 'icon' => 'fa-boxes-stacked',  'bg' => '#eff6ff', 'border' => '#dbeafe', 'color' => '#2563eb', 'active' => request()->routeIs('warehouse.product.*') || (request()->routeIs('warehouse.index') && request('tab') === 'data-product' && request('from') === 'purchasing')],
                    ['label' => 'Data Supplier',  'route' => 'suppliers.index',                 'params' => ['from' => 'purchasing'], 'icon' => 'fa-truck',          'bg' => '#f0fdf4', 'border' => '#bbf7d0', 'color' => '#16a34a', 'active' => request()->routeIs('suppliers.*')],
                ],
            ],
            'sales' => [
                'label'  => 'Sales Command Center',
                'icon'   => 'fa-solid fa-chart-simple',
                'accent' => '#2563eb',
                'items' => [
                    ['label' => 'Data Management',     'route' => 'sales.manage',            'icon' => 'fa-database',             'bg' => '#ecfdf5', 'border' => '#d1fae5', 'color' => '#10b981', 'active' => request()->routeIs('sales.manage')],
                    ['label' => 'Analytics & Insights', 'route' => 'sales.analytics',        'icon' => 'fa-chart-line',           'bg' => '#eff6ff', 'border' => '#dbeafe', 'color' => '#3b82f6', 'active' => request()->routeIs('sales.analytics')],
                    ['label' => 'Monthly Monitoring',  'route' => 'sales.monthly',            'icon' => 'fa-calendar-check',       'bg' => '#faf5ff', 'border' => '#f3e8ff', 'color' => '#a855f7', 'active' => request()->routeIs('sales.monthly')],
                    ['label' => 'CRM Klien',           'route' => 'crm.index',                'icon' => 'fa-users',                'bg' => '#fdf2f8', 'border' => '#fbcfe8', 'color' => '#db2777', 'active' => request()->routeIs('crm.*')],
                    ['label' => 'Incentive Scheme',    'route' => 'sales.incentive',          'icon' => 'fa-hand-holding-dollar',  'bg' => '#fbf0ea', 'border' => '#f3d9ca', 'color' => '#7c2d12', 'active' => request()->routeIs('sales.incentive')],
                    ['label' => 'Stock Monitoring',    'route' => 'sales.monitoring.index',   'icon' => 'fa-boxes-stacked',        'bg' => '#fff7ed', 'border' => '#ffedd5', 'color' => '#f97316', 'active' => request()->routeIs('sales.monitoring.*')],
                    ['label' => 'Sales Forecast',      'route' => 'sales.forecast',           'icon' => 'fa-chart-area',           'bg' => '#f8fafc', 'border' => '#e2e8f0', 'color' => '#475569', 'active' => request()->routeIs('sales.forecast')],
                    ['label' => 'Product Price & SPH', 'route' => 'sales.pricing',            'icon' => 'fa-file-invoice-dollar',  'bg' => '#f0f9ff', 'border' => '#e0f2fe', 'color' => '#0ea5e9', 'active' => request()->routeIs('sales.pricing*')],
                    ['label' => 'Data PS',             'route' => null,                       'icon' => 'fa-user-tag',             'bg' => '#eef2ff', 'border' => '#e0e7ff', 'color' => '#4f46e5', 'active' => false],
                    ['label' => 'Pengajuan Sample',    'route' => null,                       'icon' => 'fa-flask',                'bg' => '#f5f3ff', 'border' => '#ddd6fe', 'color' => '#7c3aed', 'active' => false],
                ],
            ],
            'warehouse' => [
                'label'  => 'Warehouse Dashboard',
                'icon'   => 'fa-solid fa-warehouse',
                'accent' => '#059669',
                'items' => [
                    ['label' => 'Warehouse Operations', 'route' => 'warehouse.index',                 'icon' => 'fa-warehouse',       'bg' => '#f0f9ff', 'border' => '#e0f2fe', 'color' => '#0ea5e9', 'active' => request()->routeIs('warehouse.index') || request()->routeIs('warehouse.document') || request()->routeIs('warehouse.product.*')],
                    ['label' => 'Stock Forecast',       'route' => 'sales.forecast',                  'icon' => 'fa-chart-area',      'bg' => '#eef2ff', 'border' => '#e0e7ff', 'color' => '#6366f1', 'active' => request()->routeIs('sales.forecast') && request('from') === 'warehouse', 'params' => ['from' => 'warehouse']],
                    ['label' => 'Analytics Gudang',     'route' => 'warehouse.analytics',             'icon' => 'fa-chart-line',      'bg' => '#f5f3ff', 'border' => '#ddd6fe', 'color' => '#7c3aed', 'active' => request()->routeIs('warehouse.analytics')],
                    ['label' => 'Stock Opname',         'route' => 'warehouse.stockopname.index',     'icon' => 'fa-clipboard-check', 'bg' => '#fff1f2', 'border' => '#fecdd3', 'color' => '#f43f5e', 'active' => request()->routeIs('warehouse.stockopname.*')],
                    ['label' => 'Form SJ',              'route' => 'warehouse.sj.index',              'icon' => 'fa-truck',           'bg' => '#fff7ed', 'border' => '#fed7aa', 'color' => '#ea580c', 'active' => request()->routeIs('warehouse.sj.*')],
                    ['label' => 'Form SPB',             'route' => 'pengajuan_barang.index',          'icon' => 'fa-file-signature',  'bg' => '#f7fee7', 'border' => '#d9f99d', 'color' => '#65a30d', 'active' => request()->routeIs('pengajuan_barang.*') && !request()->routeIs('pengajuan_barang.monitoring_all'), 'params' => ['from' => 'dashboard']],
                ],
            ],
        ];

        // Tab navigation aktif di area warehouse, purchasing, hrd, sales, dan crm.
        $warehouseTabsEnabled = request()->routeIs('warehouse.*')
            || request()->routeIs('pengajuan_barang.*')
            || request()->routeIs('pengajuan_dana.*')
            || request()->routeIs('suppliers.*')
            || request()->routeIs('hrd.*')
            || request()->routeIs('kpi.*')
            || request()->routeIs('sales.*')
            || request()->routeIs('crm.*');

        $currentRouteName = request()->route()?->getName();
        $currentPath = '/'.ltrim(request()->path(), '/');
        $currentPath = $currentPath === '/' ? '/' : rtrim($currentPath, '/');

        // Khusus seluruh area stock opname (termasuk catat opname), tetap berada di tab Stock Opname
        $isStockOpnameSection = request()->routeIs('warehouse.stockopname.*');
        $tabPath = $currentPath;
        if ($isStockOpnameSection) {
            $opnameIndexPath = '/'.ltrim(parse_url(route('warehouse.stockopname.index'), PHP_URL_PATH), '/');
            $tabPath = $opnameIndexPath === '/' ? '/' : rtrim($opnameIndexPath, '/');
        }

        $pageTitle = $title ?? null;
        if ($pageTitle instanceof \Illuminate\View\ComponentSlot) {
            $pageTitle = $pageTitle->toHtml();
        }
        $pageTitle = is_string($pageTitle)
            ? trim(preg_replace('/\s+/', ' ', strip_tags($pageTitle)))
            : '';
        $pageTitle = $pageTitle !== '' ? $pageTitle : 'Halaman';

        $defaultTabIcon = 'fa-warehouse';
        $defaultTabColor = '#0ea5e9';
        if (request()->routeIs('sales.*') || request()->routeIs('crm.*')) {
            $defaultTabIcon = 'fa-chart-simple';
            $defaultTabColor = '#2563eb';
        } elseif (request()->routeIs('hrd.*')) {
            $defaultTabIcon = 'fa-user-tie';
            $defaultTabColor = '#6366f1';
        } elseif (request()->routeIs('pengajuan_dana.*')) {
            $defaultTabIcon = 'fa-coins';
            $defaultTabColor = '#2563eb';
        } elseif (request()->routeIs('suppliers.*') || request()->routeIs('pengajuan_barang.monitoring_all') || request('from') === 'purchasing') {
            $defaultTabIcon = 'fa-cart-shopping';
            $defaultTabColor = '#ea580c';
        }

        $pageTab = [
            'path' => $tabPath,
            'label' => $isStockOpnameSection ? 'Stock Opname' : $pageTitle,
            'icon' => $isStockOpnameSection ? 'fa-clipboard-check' : $defaultTabIcon,
            'color' => $isStockOpnameSection ? '#f43f5e' : $defaultTabColor,
            'matched' => $isStockOpnameSection,
        ];

        if ($warehouseTabsEnabled && !$isStockOpnameSection) {
            $navCandidateItems = array_merge(
                $sidebarGroups['dana']['items'] ?? [],
                $sidebarGroups['purchasing']['items'] ?? [],
                $sidebarGroups['warehouse']['items'] ?? [],
                $sidebarGroups['hrd']['items'] ?? [],
                $sidebarGroups['sales']['items'] ?? [],
                [
                    ['label' => 'Form Pengajuan Dana', 'route' => 'pengajuan_dana.index', 'icon' => 'fa-file-invoice-dollar', 'color' => '#2563eb'],
                    ['label' => 'Monitoring Pengajuan', 'route' => 'pengajuan_dana.monitoring_all', 'icon' => 'fa-desktop', 'color' => '#16a34a'],
                    ['label' => 'Riwayat Pengajuan Dana', 'route' => 'pengajuan_dana.history', 'icon' => 'fa-history', 'color' => '#2563eb'],
                    ['label' => 'Detail Pengajuan Dana', 'route' => 'pengajuan_dana.show', 'icon' => 'fa-file-invoice-dollar', 'color' => '#2563eb'],
                    ['label' => 'Sales Command Center', 'route' => 'sales.index', 'icon' => 'fa-chart-simple', 'color' => '#2563eb'],
                    ['label' => 'Product Price & SPH', 'route' => 'sales.sph.index', 'icon' => 'fa-file-invoice-dollar', 'color' => '#0ea5e9'],
                    ['label' => 'Detail SPH', 'route' => 'sales.sph.show', 'icon' => 'fa-file-invoice-dollar', 'color' => '#0ea5e9'],
                    ['label' => 'Detail Klien', 'route' => 'crm.show', 'icon' => 'fa-users', 'color' => '#db2777'],
                    ['label' => 'Edit Klien', 'route' => 'crm.client.edit', 'icon' => 'fa-user-pen', 'color' => '#db2777'],
                ]
            );
            foreach ($navCandidateItems as $candidateItem) {
                if (!empty($candidateItem['route']) && $candidateItem['route'] === $currentRouteName) {
                    if (isset($candidateItem['params']['from']) && request('from') !== $candidateItem['params']['from']) {
                        continue;
                    }
                    if (!isset($candidateItem['params']['from']) && request('from') && in_array($candidateItem['route'], ['sales.forecast', 'warehouse.product.index'], true)) {
                        continue;
                    }
                    $pageTab = [
                        'path' => $currentPath,
                        'label' => $candidateItem['label'],
                        'icon' => $candidateItem['icon'],
                        'color' => $candidateItem['color'],
                        'matched' => true,
                    ];
                    break;
                }
            }

            if (!$pageTab['matched'] && in_array($pageTab['label'], [null, '', 'Dashboard'], true)) {
                $pageTab['label'] = $currentRouteName
                    ? \Illuminate\Support\Str::of($currentRouteName)->afterLast('.')->replace(['-', '_'], ' ')->title()->toString()
                    : 'Halaman';
            }
        }

        $tabUserKey = auth()->id() ?? 'guest';
        $tabStorageKey = 'app.tabs.v1.'.$tabUserKey;
        $tabActiveKey = 'app.tabs.active.v1.'.$tabUserKey;
        $tabPendingKey = 'app.tabs.pending.v1.'.$tabUserKey;
        $tabKeys = [
            'storageKey' => $tabStorageKey,
            'activeKey' => $tabActiveKey,
            'pendingKey' => $tabPendingKey,
        ];
    @endphp

    <script>
        window.__APP_TAB_KEYS__ = @json($tabKeys);
    </script>

    @if (!$isTabEmbed)
    {{-- Overlay Sidebar (mobile) --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden" style="background-color: rgba(0, 0, 0, 0.25);"></div>

    {{-- Backdrop blur saat panel submenu terbuka --}}
    <div id="sidebar-submenu-backdrop" class="fixed inset-0 z-[44] hidden" style="background-color: rgba(15, 23, 42, 0.18); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);"></div>

    {{-- Rail Sidebar (Z-Index 50) --}}
    <div id="sidebar" class="bg-blue-600 text-white h-full flex flex-col w-20 fixed top-0 left-0 z-50 transform -translate-x-full lg:translate-x-0">
        <div class="p-4 border-b border-blue-500 flex items-center justify-center h-[68px] shrink-0">
            <i class="fas fa-clinic-medical text-3xl"></i>
        </div>
        <div class="flex-grow overflow-y-auto">
            <nav class="p-3 pt-5">
                <ul class="space-y-3">
                    <li><a href="{{ route('dashboard') }}" class="flex items-center justify-center p-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-800' : 'hover:bg-blue-700/50' }}" title="Dashboard"><i class="fas fa-th-large text-xl"></i></a></li>

                    @foreach ($sidebarGroups as $key => $group)
                        <li>
                            <button type="button"
                                class="sidebar-group-btn relative w-full flex items-center justify-center p-3 rounded-lg hover:bg-blue-700/50 {{ $activeGroup === $key ? 'bg-blue-800' : '' }}"
                                data-group="{{ $key }}"
                                data-current-active="{{ $activeGroup === $key ? 'true' : 'false' }}"
                                aria-controls="sidebar-submenu"
                                aria-expanded="{{ $activeGroup === $key ? 'true' : 'false' }}"
                                title="{{ $group['label'] }}">
                                @if(!empty($group['text_icon']))
                                    <span class="font-black text-xs tracking-wider uppercase leading-none py-1.5 px-1 rounded bg-white/20 text-white shadow-xs">{{ $group['text_icon'] }}</span>
                                @else
                                    <i class="{{ $group['icon'] }} text-xl"></i>
                                @endif
                                <i class="fas fa-chevron-right sidebar-group-chevron"></i>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
        <div class="p-3 space-y-3 border-t border-blue-500 shrink-0" style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">
            <a href="{{ route('notifikasi.index') }}" class="flex items-center justify-center p-3 rounded-lg {{ request()->routeIs('notifikasi.*') ? 'bg-blue-800' : 'hover:bg-blue-700/50' }}" title="Notifikasi"><i class="fas fa-envelope-open-text text-xl"></i></a>
            <a href="{{ route('profil.index') }}" class="flex items-center justify-center p-3 rounded-lg {{ request()->routeIs('profil.*') ? 'bg-blue-800' : 'hover:bg-blue-700/50' }}" title="Profil"><i class="fas fa-user-cog text-xl"></i></a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center p-3 rounded-lg hover:bg-red-500" title="Logout"><i class="fas fa-sign-out-alt text-xl"></i></button>
            </form>
        </div>
    </div>

    {{-- Panel Submenu Melayang (Z-Index 45) --}}
    <aside id="sidebar-submenu" class="fixed left-20 w-[38rem] bg-white border border-slate-200 rounded-l-none rounded-r-2xl shadow-xl z-[45] flex flex-col overflow-hidden" aria-hidden="true">
        @foreach ($sidebarGroups as $key => $group)
            <div class="sidebar-submenu-group flex flex-col h-full min-h-0" data-group-panel="{{ $key }}" @if($activeGroup !== $key) hidden @endif>
                <div class="px-5 pt-4 pb-2.5 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-slate-800 leading-tight sidebar-submenu-title">{{ $group['label'] }}</h2>
                        @if(!empty($group['disabled']))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="fas fa-hammer mr-1 text-[9px]"></i> {{ $group['badge'] ?? 'Tahap Pengembangan' }}
                            </span>
                        @endif
                    </div>
                    <button type="button" class="sidebar-submenu-close p-1.5 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup submenu">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <div class="h-[2px] w-full shrink-0" style="background-color: {{ $group['accent'] }};"></div>

                <div class="flex-grow min-h-0 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5">
                        @php
                            $isGroupDisabled = !empty($group['disabled']);
                        @endphp
                        @foreach ($group['items'] as $item)
                            @php
                                $itemRoute = $item['route'] ?? null;
                                $hasRoute = !$isGroupDisabled && !empty($itemRoute) && \Illuminate\Support\Facades\Route::has($itemRoute);
                                $targetUrl = $hasRoute ? route($itemRoute, $item['params'] ?? []) : 'javascript:void(0);';
                                $isActive = $hasRoute && !empty($item['active']);
                                $isItemDisabled = $isGroupDisabled || !$hasRoute;
                            @endphp
                            <a href="{{ $targetUrl }}"
                                @if($isItemDisabled)
                                    onclick="event.preventDefault(); event.stopPropagation();"
                                    data-disabled="true"
                                @endif
                                @if(in_array($key, ['warehouse', 'purchasing', 'hrd', 'sales', 'dana'], true) && !$isItemDisabled)
                                    data-tab-link="1"
                                    data-tab-label="{{ $item['label'] }}"
                                    data-tab-icon="{{ $item['icon'] }}"
                                    data-tab-color="{{ $item['color'] }}"
                                @endif
                                class="submenu-tile {{ $isActive ? 'is-active' : '' }} {{ $isItemDisabled ? 'opacity-75 cursor-not-allowed select-none hover:translate-y-0 shadow-none' : '' }}"
                                style="--tile-bg: {{ $item['bg'] }}; --tile-border: {{ $item['border'] }}; --tile-color: {{ $item['color'] }};"
                                title="{{ $item['label'] }}{{ $isItemDisabled ? ' (Tahap Pengembangan)' : '' }}">
                                <i class="fas {{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                                @if($isItemDisabled)
                                    <span class="inline-block text-[9px] font-semibold text-amber-700 bg-amber-100/90 px-1.5 py-0.5 rounded-full border border-amber-300/60 mt-0.5 tracking-tight leading-none">Pengembangan</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </aside>
    @endif

    <div id="app-shell" class="{{ $fluid ? 'flex flex-col w-full min-h-screen relative m-0 p-0' : 'flex-1 flex flex-col min-h-screen relative m-0 p-0' }}">
        @if (!$isTabEmbed)
        {{-- Topbar: navbar + tab halaman --}}
        <div id="app-topbar" class="sticky top-0 z-20 shrink-0">
            {{-- Navbar --}}
            <header class="bg-gradient-to-r from-blue-700 to-blue-600 shadow-lg text-white m-0">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="py-3.5 sm:py-4 min-h-[58px] sm:min-h-[64px] flex items-center justify-between">
                        <div class="flex items-center">
                            <button id="sidebar-toggle" class="mr-3 p-2 rounded-md hover:bg-blue-800/50 focus:outline-none lg:hidden" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false"><i class="fas fa-bars text-xl"></i></button>
                            <div class="flex items-center">
                                <img src="{{ asset('asset/images/logorakha.png') }}" alt="Logo" class="hidden sm:block h-8 w-8 mr-2 sm:h-10 sm:w-10 sm:mr-3">
                                <div>
                                    <h1 class="text-sm sm:text-lg font-bold leading-tight tracking-wide">PT RAKHA NUSANTARA MEDIKA</h1>
                                    <p class="text-xs text-blue-200 font-semibold leading-tight hidden sm:block">{{ $title }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            @if ($warehouseTabsEnabled)
                <x-ui.nav-tabs
                    :current="$pageTab"
                    :home-url="route('dashboard')"
                    :storage-key="$tabStorageKey"
                    :active-key="$tabActiveKey"
                    :pending-key="$tabPendingKey"
                    :max="10"
                />
            @endif
        </div>
        <script>
            (function () {
                function syncTopbarHeight() {
                    var tb = document.getElementById('app-topbar');
                    if (tb) {
                        var h = tb.offsetHeight;
                        if (h > 0) document.documentElement.style.setProperty('--app-topbar-h', h + 'px');
                    }
                }
                syncTopbarHeight();
                window.addEventListener('resize', syncTopbarHeight);
                window.addEventListener('DOMContentLoaded', syncTopbarHeight);
            })();
        </script>
        @endif

        {{-- Main Content --}}
        <main id="app-main" class="flex-1 relative m-0 p-0 w-full min-h-0">
            @if ($isTabEmbed)
                {{ $slot }}
            @else
                <div id="app-tab-loader" class="ui-tab-loader hidden" aria-live="polite" aria-label="Memuat halaman">
                    <div class="ui-tab-loader__box">
                        <i class="fas fa-circle-notch fa-spin ui-tab-loader__icon"></i>
                        <span class="ui-tab-loader__text" id="app-tab-loader-text">Memuat halaman...</span>
                    </div>
                </div>
                <div id="tab-pane-initial" class="ui-tab-pane w-full" data-tab-pane-path="{{ $tabPath }}">
                    {{ $slot }}
                </div>
            @endif
        </main>
    </div>

    @if (!$isTabEmbed)
    {{-- Script Sidebar --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const submenu = document.getElementById('sidebar-submenu');
            const submenuBackdrop = document.getElementById('sidebar-submenu-backdrop');
            const submenuCloseButtons = document.querySelectorAll('.sidebar-submenu-close');
            const overlay = document.getElementById('sidebar-overlay');
            const toggleButton = document.getElementById('sidebar-toggle');
            const groupButtons = document.querySelectorAll('.sidebar-group-btn');
            const groupPanels = document.querySelectorAll('.sidebar-submenu-group');
            const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

            let openGroup = null;

            function setSidebar(open) {
                sidebar.classList.toggle('-translate-x-full', !open);
                overlay.classList.toggle('hidden', !open);
                toggleButton.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function syncGroupButtons() {
                groupButtons.forEach(function (btn) {
                    const isOpen = btn.dataset.group === openGroup;
                    const isCurrent = btn.dataset.currentActive === 'true';
                    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    btn.classList.toggle('bg-blue-800', isOpen || (!openGroup && isCurrent));
                    const chevron = btn.querySelector('.sidebar-group-chevron');
                    if (chevron) {
                        chevron.classList.toggle('is-open', isOpen);
                    }
                });
            }

            function positionSubmenu(anchor) {
                if (!anchor) return;
                const margin = 12;
                const rect = anchor.getBoundingClientRect();
                const maxTop = window.innerHeight - submenu.offsetHeight - margin;
                let top = rect.top;
                top = Math.max(margin, Math.min(top, maxTop));
                submenu.style.top = top + 'px';
            }

            function openSubmenu(group, anchor) {
                openGroup = group;
                groupPanels.forEach(function (panel) {
                    panel.hidden = panel.dataset.groupPanel !== group;
                });
                positionSubmenu(anchor || document.querySelector('.sidebar-group-btn[data-group="' + group + '"]'));
                submenu.classList.add('is-open');
                submenu.setAttribute('aria-hidden', 'false');
                submenuBackdrop.classList.remove('hidden');
                syncGroupButtons();
            }

            function closeSubmenu() {
                openGroup = null;
                submenu.classList.remove('is-open');
                submenu.setAttribute('aria-hidden', 'true');
                submenuBackdrop.classList.add('hidden');
                syncGroupButtons();
            }

            function toggleGroup(group, anchor) {
                if (openGroup === group && submenu.classList.contains('is-open')) {
                    closeSubmenu();
                } else {
                    openSubmenu(group, anchor);
                }
            }

            groupButtons.forEach(function (btn) {
                btn.addEventListener('click', function () { toggleGroup(btn.dataset.group, btn); });
            });

            toggleButton.addEventListener('click', function (e) {
                e.stopPropagation();
                const willOpen = sidebar.classList.contains('-translate-x-full');
                setSidebar(willOpen);
            });

            overlay.addEventListener('click', function () {
                closeSubmenu();
                setSidebar(false);
            });

            submenuCloseButtons.forEach(function (btn) {
                btn.addEventListener('click', function () { closeSubmenu(); });
            });

            // Tutup panel segera saat menu aktif diklik (sebelum halaman berpindah).
            // Menu yang belum aktif (disabled / javascript void / tahap pengembangan) tidak berpindah dan menampilkan info toast.
            document.querySelectorAll('#sidebar a, #sidebar-submenu a').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    const href = link.getAttribute('href');
                    if (!href || href === '#' || href.startsWith('javascript:') || link.dataset.disabled === 'true') {
                        e.preventDefault();
                        e.stopPropagation();
                        if (link.dataset.disabled === 'true' && typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'info',
                                title: 'Halaman sedang dalam tahap pengembangan',
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });
                        }
                        return;
                    }
                    closeSubmenu();
                    if (!isDesktop()) setSidebar(false);
                });
            });

            document.addEventListener('click', function (e) {
                if (!submenu.classList.contains('is-open')) return;
                if (submenu.contains(e.target)) return;
                if (e.target.closest('.sidebar-group-btn')) return;
                closeSubmenu();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                if (submenu.classList.contains('is-open')) {
                    closeSubmenu();
                } else if (!isDesktop()) {
                    setSidebar(false);
                }
            });

            window.addEventListener('resize', function () {
                if (isDesktop()) {
                    overlay.classList.add('hidden');
                    sidebar.classList.remove('-translate-x-full');
                    if (openGroup) openSubmenu(openGroup);
                } else {
                    closeSubmenu();
                    setSidebar(false);
                }
            });
        });
    </script>
    @endif
    
    @stack('modals')
    @stack('scripts')
    
    {{-- AlpineJS CDN for UI reactivity (Tabs, Accordions, etc) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <x-toast />
    
    {{-- SweetAlert2 CDN & Global Confirm Function (Light Theme) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-ui.confirm />
    <script>
        function confirmSubmit(event, message) {
            event.preventDefault();
            // event.currentTarget = form saat dipanggil via onsubmit;
            // event.target bisa berupa tombol saat dipanggil via onclick — cari form terdekat.
            var el = event.currentTarget || event.target;
            var form = (el && el.tagName === 'FORM')
                ? el
                : (el && el.closest ? el.closest('form') : null);
            if (!form || typeof form.submit !== 'function') {
                return;
            }

            // Fallback jika CDN SweetAlert2 gagal dimuat: pakai confirm() bawaan browser
            // agar form tetap bisa tersubmit (sebelumnya macet total karena preventDefault jalan duluan).
            if (typeof Swal === 'undefined') {
                if (confirm(message)) {
                    form.submit();
                }
                return;
            }
            
            Swal.fire({
                position: 'center',
                title: 'Konfirmasi',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'bg-white shadow-[0_15px_50px_rgba(0,0,0,0.15)] border border-gray-100 rounded-3xl p-6 text-center',
                    title: 'text-lg font-black text-slate-800 tracking-tight mt-2 m-0',
                    htmlContainer: 'text-sm text-slate-500 font-medium leading-relaxed m-0 mt-3 mb-6',
                    icon: 'scale-75 m-0 mx-auto border-0 text-amber-500 -mt-2',
                    actions: 'flex justify-center gap-3 w-full m-0',
                    confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 m-0',
                    cancelButton: 'bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold px-5 py-2.5 rounded-xl transition-all m-0'
                },
                width: '340px',
                buttonsStyling: false,
                background: '#ffffff',
                backdrop: 'rgba(0,0,0,0.5)',
                showClass: {
                    popup: 'animate__animated animate__zoomIn animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut animate__faster'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
    {{-- Tombol X untuk input ketikan (opt-in: tambah data-clearable pada form) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form[data-clearable] input[type="text"]').forEach(function (input) {
                if (input.dataset.clearReady) return;
                input.dataset.clearReady = '1';
                var wrap = document.createElement('span');
                wrap.style.cssText = 'position:relative;display:block;';
                input.parentNode.insertBefore(wrap, input);
                wrap.appendChild(input);
                input.style.paddingRight = '28px';
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.title = 'Hapus';
                btn.setAttribute('aria-label', 'Hapus isian');
                btn.innerHTML = '<i class="fas fa-times-circle"></i>';
                btn.style.cssText = 'position:absolute;right:8px;top:50%;transform:translateY(-50%);display:none;align-items:center;color:#1d4ed8;font-size:11px;padding:2px;background:transparent;border:none;cursor:pointer;';
                function sync() { btn.style.display = input.value ? 'flex' : 'none'; }
                btn.addEventListener('click', function () {
                    input.value = '';
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    if (window.Alpine && input.hasAttribute('x-model')) { try { Alpine.evaluate(input.closest('[x-data]'), input.getAttribute('x-model') + " = ''"); } catch (e) {} }
                    input.focus();
                    sync();
                });
                input.addEventListener('input', sync);
                wrap.appendChild(btn);
                sync();
            });
        });
    </script>
</body>
</html>