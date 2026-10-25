@props(['title' => 'Dashboard', 'fluid' => false])
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
    
    @stack('styles')
    <style>
        html { margin: 0 !important; padding: 0 !important; background-color: #ede9fe; }
        body { margin: 0 !important; padding: 0 !important; }
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
        /* FIX gap di atas navbar */
        header.sticky { top: 0 !important; margin: 0 !important; padding-top: env(safe-area-inset-top) !important; }
        /* diagnostic - hapus setelah cek: html=red, body=ungu, header=biru */
        /* html{outline: 2px solid red} header{outline:2px solid yellow} */
    </style>
</head>

<body class="font-sans flex flex-col min-h-screen m-0 p-0" style="background-color: #ede9fe;">

    @php
        $activeGroup = request()->routeIs('warehouse.*')
            ? 'warehouse'
            : (request()->routeIs('sales.*')
                ? (request()->routeIs('sales.forecast') && request('from') === 'warehouse' ? 'warehouse' : 'sales')
                : null);

        $sidebarGroups = [
            'sales' => [
                'label'  => 'Sales Command Center',
                'icon'   => 'fa-solid fa-chart-simple',
                'accent' => '#2563eb',
                'items' => [
                    ['label' => 'Data Management',     'route' => 'sales.manage',            'icon' => 'fa-database',             'bg' => '#ecfdf5', 'border' => '#d1fae5', 'color' => '#10b981', 'active' => request()->routeIs('sales.manage')],
                    ['label' => 'Analytics & Insights', 'route' => 'sales.analytics',        'icon' => 'fa-chart-line',           'bg' => '#eff6ff', 'border' => '#dbeafe', 'color' => '#3b82f6', 'active' => request()->routeIs('sales.analytics')],
                    ['label' => 'Monthly Monitoring',  'route' => 'sales.monthly',            'icon' => 'fa-calendar-check',       'bg' => '#faf5ff', 'border' => '#f3e8ff', 'color' => '#a855f7', 'active' => request()->routeIs('sales.monthly')],
                    ['label' => 'Incentive Scheme',    'route' => 'sales.incentive',          'icon' => 'fa-hand-holding-dollar',  'bg' => '#fbf0ea', 'border' => '#f3d9ca', 'color' => '#7c2d12', 'active' => request()->routeIs('sales.incentive')],
                    ['label' => 'Stock Monitoring',    'route' => 'sales.monitoring.index',   'icon' => 'fa-boxes-stacked',        'bg' => '#fff7ed', 'border' => '#ffedd5', 'color' => '#f97316', 'active' => request()->routeIs('sales.monitoring.*')],
                    ['label' => 'Sales Forecast',      'route' => 'sales.forecast',           'icon' => 'fa-chart-area',           'bg' => '#f8fafc', 'border' => '#e2e8f0', 'color' => '#475569', 'active' => request()->routeIs('sales.forecast')],
                    ['label' => 'Product Price & SPH', 'route' => 'sales.pricing',            'icon' => 'fa-file-invoice-dollar',  'bg' => '#f0f9ff', 'border' => '#e0f2fe', 'color' => '#0ea5e9', 'active' => request()->routeIs('sales.pricing*')],
                ],
            ],
            'warehouse' => [
                'label'  => 'Warehouse Dashboard',
                'icon'   => 'fa-solid fa-warehouse',
                'accent' => '#059669',
                'items' => [
                    ['label' => 'Data Product',    'route' => 'warehouse.product.index',         'icon' => 'fa-cubes',           'bg' => '#eff6ff', 'border' => '#dbeafe', 'color' => '#3b82f6', 'active' => request()->routeIs('warehouse.product.*')],
                    ['label' => 'Warehouse Ops',   'route' => 'warehouse.index',                 'icon' => 'fa-warehouse',       'bg' => '#f0f9ff', 'border' => '#e0f2fe', 'color' => '#0ea5e9', 'active' => request()->routeIs('warehouse.index') || request()->routeIs('warehouse.document')],
                    ['label' => 'Stock Forecast',  'route' => 'sales.forecast',                  'icon' => 'fa-chart-area',      'bg' => '#eef2ff', 'border' => '#e0e7ff', 'color' => '#6366f1', 'active' => request()->routeIs('sales.forecast') && request('from') === 'warehouse', 'params' => ['from' => 'warehouse']],
                    ['label' => 'Analytics Gudang','route' => 'warehouse.analytics',             'icon' => 'fa-chart-line',      'bg' => '#f5f3ff', 'border' => '#ddd6fe', 'color' => '#7c3aed', 'active' => request()->routeIs('warehouse.analytics')],
                    ['label' => 'Stock Opname',    'route' => 'warehouse.stockopname.index',     'icon' => 'fa-clipboard-check', 'bg' => '#fff1f2', 'border' => '#fecdd3', 'color' => '#f43f5e', 'active' => request()->routeIs('warehouse.stockopname.*')],
                    ['label' => 'Form SJ',         'route' => 'warehouse.sj.index',              'icon' => 'fa-truck',           'bg' => '#fff7ed', 'border' => '#fed7aa', 'color' => '#ea580c', 'active' => request()->routeIs('warehouse.sj.*')],
                    ['label' => 'Form SPB',        'route' => 'pengajuan_barang.index',          'icon' => 'fa-file-signature',  'bg' => '#f7fee7', 'border' => '#d9f99d', 'color' => '#65a30d', 'active' => request()->routeIs('pengajuan_barang.*'), 'params' => ['from' => 'dashboard']],
                ],
            ],
        ];
    @endphp

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
                    <li><a href="{{ route('pengajuan_dana.index') }}" class="flex items-center justify-center p-3 rounded-lg {{ request()->routeIs('pengajuan_dana.*') ? 'bg-blue-800' : 'hover:bg-blue-700/50' }}" title="Pengajuan Dana"><i class="fas fa-coins text-xl"></i></a></li>
                    <li><a href="{{ route('crm.index') }}" class="flex items-center justify-center p-3 rounded-lg {{ request()->routeIs('crm.*') ? 'bg-blue-800' : 'hover:bg-blue-700/50' }}" title="CRM"><i class="fas fa-users text-xl"></i></a></li>

                    @foreach ($sidebarGroups as $key => $group)
                        <li>
                            <button type="button"
                                class="sidebar-group-btn relative w-full flex items-center justify-center p-3 rounded-lg hover:bg-blue-700/50 {{ $activeGroup === $key ? 'bg-blue-800' : '' }}"
                                data-group="{{ $key }}"
                                aria-controls="sidebar-submenu"
                                aria-expanded="{{ $activeGroup === $key ? 'true' : 'false' }}"
                                title="{{ $group['label'] }}">
                                <i class="{{ $group['icon'] }} text-xl"></i>
                                <i class="fas fa-chevron-right sidebar-group-chevron absolute right-1.5 text-[9px] opacity-50 transition-transform"></i>
                            </button>
                        </li>
                    @endforeach

                    <li><a href="{{ route('kpi.index') }}" class="flex items-center justify-center p-3 rounded-lg {{ request()->routeIs('kpi.*') ? 'bg-blue-800' : 'hover:bg-blue-700/50' }}" title="KPI"><i class="fas fa-chart-line text-xl"></i></a></li>
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
                    <h2 class="text-lg font-semibold text-slate-800 leading-tight sidebar-submenu-title">{{ $group['label'] }}</h2>
                    <button type="button" class="sidebar-submenu-close p-1.5 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup submenu">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <div class="h-[2px] w-full shrink-0" style="background-color: {{ $group['accent'] }};"></div>

                <div class="flex-grow min-h-0 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5">
                        @foreach ($group['items'] as $item)
                            <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                                class="submenu-tile {{ $item['active'] ? 'is-active' : '' }}"
                                style="--tile-bg: {{ $item['bg'] }}; --tile-border: {{ $item['border'] }}; --tile-color: {{ $item['color'] }};"
                                title="{{ $item['label'] }}">
                                <i class="fas {{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </aside>

    <div id="app-shell" class="{{ $fluid ? 'flex flex-col w-full relative m-0 p-0' : 'flex-1 flex flex-col min-h-screen relative m-0 p-0' }}">
        {{-- Navbar --}}
        <header class="bg-gradient-to-r from-blue-700 to-blue-600 shadow-lg sticky top-0 z-20 text-white shrink-0 m-0">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-3 flex items-center justify-between">
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

        {{-- Main Content --}}
        <main class="flex-1 relative m-0 p-0">
            {{ $slot }}
        </main>
    </div>

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
                    const isActive = btn.dataset.group === openGroup;
                    btn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
                    btn.classList.toggle('bg-blue-800', isActive);
                    const chevron = btn.querySelector('.sidebar-group-chevron');
                    if (chevron) chevron.style.transform = isActive ? 'rotate(180deg)' : '';
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

            // Tutup panel segera saat menu diklik (sebelum halaman berpindah),
            // dan jangan biarkan panel terbuka lagi setelah refresh.
            document.querySelectorAll('#sidebar a, #sidebar-submenu a').forEach(function (link) {
                link.addEventListener('click', function () {
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
    
    @stack('modals')
    @stack('scripts')
    
    {{-- AlpineJS CDN for UI reactivity (Tabs, Accordions, etc) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <x-toast />
    
    {{-- SweetAlert2 CDN & Global Confirm Function (Light Theme) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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