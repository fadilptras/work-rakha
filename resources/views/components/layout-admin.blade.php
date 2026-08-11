<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>
    
    {{-- Vite & Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Alpine.js untuk fitur dropdown --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')

    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #27272a; }
        ::-webkit-scrollbar-thumb { background: #52525b; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #71717a; }
    </style>
</head>
<body class="bg-zinc-900 text-zinc-300 font-sans h-screen flex">

    {{-- Sidebar --}}
    <aside id="sidebar" class="bg-zinc-800 h-full flex flex-col w-64 flex-shrink-0 fixed top-0 left-0 z-50">
        <div class="p-4 border-b border-zinc-700/50 flex items-center justify-center h-[68px]">
            <!-- <i class="fas fa-user-shield text-3xl mr-3 text-sky-400"></i> -->
            <span class="font-bold text-xl text-sky-600">ADMIN RAKHA</span>
        </div>
        
        <div class="flex-grow overflow-y-auto">
            <nav class="p-4 pt-6">
                <ul class="space-y-2">

                    {{-- Admin --}}
                    <li>
                        <a href="{{ route('admin.admins.index') }}" class="flex items-center p-3 rounded-lg transition-colors duration-200
                            {{ request()->routeIs('admin.admins.*') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-zinc-700' }}">
                            <i class="fas fa-user-cog text-xl w-8 text-center"></i>
                            <span class="ml-3 font-semibold">Kelola Admin</span>
                        </a>
                    </li>

                    {{-- Karyawan --}}
                    <li>
                        <a href="{{ route('admin.employees.index') }}" class="flex items-center p-3 rounded-lg transition-colors duration-200 
                            {{ request()->routeIs('admin.employees.*') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-zinc-700' }}">
                            <i class="fas fa-users text-xl w-8 text-center"></i>
                            <span class="ml-3 font-semibold">Kelola Karyawan</span>
                        </a>
                    </li>

                    {{-- CRM --}}
                    <li>
                        <a href="{{ route('admin.crm.index') }}" class="flex items-center p-3 rounded-lg transition-colors duration-200
                            {{ request()->routeIs('admin.crm.*') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-zinc-700' }}">
                            <i class="fas fa-chart-line text-xl w-8 text-center"></i>
                            <span class="ml-3 font-semibold">CRM</span>
                        </a>
                    </li>

                    {{-- KPI --}}
                    <li x-data="{ open: {{ request()->routeIs('admin.kpi.*') ? 'true' : 'false' }} }">
                        <a @click.prevent="open = !open" href="#" 
                            class="flex items-center p-3 rounded-lg transition-colors duration-200 cursor-pointer 
                            {{ request()->routeIs('admin.kpi.*') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-zinc-700' }}">
                            <i class="fas fa-chart-bar text-xl w-8 text-center"></i> 
                            <span class="ml-3 font-semibold flex-1">Manajemen KPI</span>
                            <i class="fas fa-chevron-down text-sm transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </a>
                        
                        <div x-show="open" x-collapse>
                            <ul class="ml-12 mt-2 space-y-1">
                                <li>
                                    <a href="{{ route('admin.kpi.index') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm
                                        {{ request()->routeIs('admin.kpi.index') || request()->routeIs('admin.kpi.evaluate') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Evaluasi KPI
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.kpi.indicators.index') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm
                                        {{ request()->routeIs('admin.kpi.indicators.*') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Kelola Indikator
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Absensi --}}
                    <li x-data="{ open: {{ request()->routeIs('admin.absensi.*') || request()->routeIs('admin.lembur.*') || request()->routeIs('admin.aktivitas.*') ? 'true' : 'false' }} }">
                        <a @click.prevent="open = !open" href="#" 
                            class="flex items-center p-3 rounded-lg transition-colors duration-200 cursor-pointer 
                            {{ request()->routeIs('admin.absensi.*') || request()->routeIs('admin.lembur.*') || request()->routeIs('admin.aktivitas.*') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-zinc-700' }}">
                            <i class="fas fa-calendar-check text-xl w-8 text-center"></i> 
                            <span class="ml-3 font-semibold flex-1">Kelola Kehadiran</span>
                            <i class="fas fa-chevron-down text-sm transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </a>
                        
                        <div x-show="open" x-collapse>
                            <ul class="ml-12 mt-2 space-y-1">
                                <li>
                                    <a href="{{ route('admin.absensi.index') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm
                                        {{ request()->routeIs('admin.absensi.index', 'admin.lembur.index') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Absensi & Lembur
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.aktivitas.index') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm
                                        {{ request()->routeIs('admin.aktivitas.index') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Aktivitas
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.absensi.rekap') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm
                                        {{ request()->routeIs('admin.absensi.rekap') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Rekap Absensi
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Cuti --}}
                    <li x-data="{ open: {{ request()->routeIs('admin.cuti.*') ? 'true' : 'false' }} }">
                        <a @click.prevent="open = !open" href="#"
                            class="flex items-center p-3 rounded-lg transition-colors duration-200 cursor-pointer
                            {{ request()->routeIs('admin.cuti.*') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-zinc-700' }}">
                            <i class="fas fa-calendar-alt text-xl w-8 text-center"></i>
                            <span class="ml-3 font-semibold flex-1">Kelola Cuti</span>
                            @if(isset($pending_cuti_count) && $pending_cuti_count > 0)
                                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $pending_cuti_count }}</span>
                            @endif
                            <i class="fas fa-chevron-down text-sm transition-transform ml-2" :class="open ? 'rotate-180' : ''"></i>
                        </a>
                        <div x-show="open" x-collapse>
                            <ul class="ml-12 mt-2 space-y-1">
                                <li>
                                    <a href="{{ route('admin.cuti.index') }}"
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm
                                        {{ request()->routeIs('admin.cuti.index') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Pengajuan Cuti
                                    </a>
                                </li>                                
                                <li>
                                    <a href="{{ route('admin.cuti.pengaturan') }}"
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm
                                        {{ request()->routeIs('admin.cuti.pengaturan') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Pengaturan Jatah Cuti
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.cuti.set_approvers') }}" method="POST"
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm
                                        {{ request()->routeIs('admin.cuti.set_approvers') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Set Approvers
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Pengajuan Dana --}}
                    <li x-data="{ open: {{ request()->routeIs('admin.pengajuan_dana.*') ? 'true' : 'false' }} }">
                        <a @click.prevent="open = !open" href="#" 
                            class="flex items-center p-3 rounded-lg transition-colors duration-200 cursor-pointer 
                            {{ request()->routeIs('admin.pengajuan_dana.*') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-zinc-700' }}">
                            <i class="fas fa-money-bill-wave text-xl w-8 text-center"></i> 
                            <span class="ml-3 font-semibold flex-1">Kelola Dana</span>
                            @if(isset($pending_dana_count) && $pending_dana_count > 0)
                                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $pending_dana_count }}</span>
                            @endif
                            <i class="fas fa-chevron-down text-sm transition-transform ml-2" :class="open ? 'rotate-180' : ''"></i>
                        </a>
                        <div x-show="open" x-collapse>
                            <ul class="ml-12 mt-2 space-y-1">
                                <li>
                                    <a href="{{ route('admin.pengajuan_dana.index') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm 
                                        {{ request()->routeIs('admin.pengajuan_dana.index') || request()->routeIs('admin.pengajuan_dana.show') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Pengajuan Dana
                                        @if(isset($pending_dana_count) && $pending_dana_count > 0)
                                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full ml-auto">{{ $pending_dana_count }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.pengajuan_dana.set_approvers.index') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm 
                                        {{ request()->routeIs('admin.pengajuan_dana.set_approvers.index') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Set Approvers
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Pengajuan Barang, Barang & Supplier --}}
                    <li x-data="{ open: {{ request()->routeIs('admin.pengajuan_barang.*') || request()->routeIs('admin.suppliers.*') || request()->routeIs('admin.barangs.*') ? 'true' : 'false' }} }">
                        <a @click.prevent="open = !open" href="#" 
                            class="flex items-center p-3 rounded-lg transition-colors duration-200 cursor-pointer 
                            {{ request()->routeIs('admin.pengajuan_barang.*') || request()->routeIs('admin.suppliers.*') || request()->routeIs('admin.barangs.*') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-zinc-700' }}">
                            <i class="fas fa-box text-xl w-8 text-center"></i>
                            <span class="ml-3 font-semibold flex-1">Kelola Barang</span>
                            @if(isset($pending_barang_count) && $pending_barang_count > 0)
                                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full ml-auto">{{ $pending_barang_count }}</span>
                            @endif
                            <i class="fas fa-chevron-down text-sm transition-transform ml-2" :class="open ? 'rotate-180' : ''"></i>
                        </a>
                        <div x-show="open" x-collapse>
                            <ul class="ml-12 mt-2 space-y-1">
                                <li>
                                    <a href="{{ route('admin.pengajuan_barang.index') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm 
                                        {{ request()->routeIs('admin.pengajuan_barang.index') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Pengajuan Barang
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.barangs.index') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm 
                                        {{ request()->routeIs('admin.barangs.*') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Data Barang
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.suppliers.index') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm 
                                        {{ request()->routeIs('admin.suppliers.*') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Data Supplier
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.pengajuan_barang.set_approvers') }}" 
                                        class="flex items-center p-2 rounded-lg transition-colors duration-200 text-sm 
                                        {{ request()->routeIs('admin.pengajuan_barang.set_approvers') ? 'text-sky-400 font-bold' : 'hover:bg-zinc-700' }}">
                                        Set Approvers
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Agenda --}}
                    <li>
                        <a href="{{ route('admin.agenda.index') }}" class="flex items-center p-3 rounded-lg transition-colors duration-200 
                            {{ request()->routeIs('admin.agenda.*') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-zinc-700' }}">
                            <i class="fas fa-calendar-week text-xl w-8 text-center"></i>
                            <span class="ml-3 font-semibold">Kelola Agenda</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.holidays.index') }}" class="flex items-center p-3 rounded-lg transition-colors duration-200 
                        {{ request()->routeIs('admin.holidays.*') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-zinc-700' }}">
                            {{-- Icon Libur (Payung Pantai / Kalender) --}}
                            <i class="fas fa-umbrella-beach text-xl w-8 text-center"></i>
                            <span class="ml-3 font-semibold">Kelola Hari Libur</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        
        <div class="p-4 border-t border-zinc-700/50">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center p-3 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-200">
                    <i class="fas fa-sign-out-alt text-xl w-8 text-center"></i>
                    <span class="ml-3 font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Konten Utama --}}
    <div class="flex-1 flex flex-col ml-64">


        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
    <x-toast />
    
    {{-- SweetAlert2 CDN & Global Confirm Function (Dark Theme) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmSubmit(event, message) {
            event.preventDefault();
            const form = event.target;
            
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
</body>
</html>
