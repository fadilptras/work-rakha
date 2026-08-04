<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>
    
    <meta name="theme-color" content="#2563eb"> 
    {{-- [FIX] Meta CSRF Token Wajib Ada --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')

    <style>
        #sidebar { transition: transform 1.5s cubic-bezier(0.25, 1, 0.5, 1), translate 1.5s cubic-bezier(0.25, 1, 0.5, 1); will-change: transform, translate; }
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>

<body class="bg-gray-100 font-sans bg-gradient-to-br from-sky-50 to-blue-100 flex flex-col min-h-screen overflow-x-hidden">

    {{-- Overlay Sidebar --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden" style="background-color: rgba(0, 0, 0, 0.25);"></div>

    {{-- Sidebar (Z-Index 50) --}}
    <div id="sidebar" class="bg-blue-600 text-white h-full flex flex-col w-20 fixed top-0 left-0 z-50 transform -translate-x-full">
        <div class="p-4 border-b border-blue-500 flex items-center justify-center h-[68px]">
            <i class="fas fa-clinic-medical text-3xl"></i>
        </div>
        <div class="flex-grow overflow-y-auto">
            <nav class="p-4 pt-6">
                <ul class="space-y-4">
                    <li><a href="{{ route('dashboard') }}" class="flex items-center justify-center p-3 rounded-lg hover:bg-blue-700/50" title="Dashboard"><i class="fas fa-th-large text-xl"></i></a></li>
                    <li><a href="{{ route('pengajuan_dana.index') }}" class="flex items-center justify-center p-3 rounded-lg hover:bg-blue-700/50" title="Pengajuan Dana"><i class="fas fa-coins text-xl"></i></a></li>
                    <li><a href="{{ route('pengajuan_barang.index') }}" class="flex items-center justify-center p-3 rounded-lg hover:bg-blue-700/50" title="Pengajuan Barang"><i class="fas fa-box-open text-xl"></i></a></li>
                    <li><a href="{{ route('crm.index') }}" class="flex items-center justify-center p-3 rounded-lg hover:bg-blue-700/50" title="CRM"><i class="fas fa-users text-xl"></i></a></li>
                    <li><a href="{{ route('kpi.index') }}" class="flex items-center justify-center p-3 rounded-lg hover:bg-blue-700/50" title="KPI"><i class="fas fa-chart-line text-xl"></i></a></li>
                </ul>
            </nav>
        </div>
        <div class="p-4 border-t border-blue-500 space-y-4">
            <a href="{{ route('notifikasi.index') }}" class="flex items-center justify-center p-3 rounded-lg hover:bg-blue-700/50" title="Notifikasi"><i class="fas fa-envelope-open-text text-xl"></i></a>
            <a href="{{ route('profil.index') }}" class="flex items-center justify-center p-3 rounded-lg hover:bg-blue-700/50" title="Profil"><i class="fas fa-user-cog text-xl"></i></a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center p-3 rounded-lg hover:bg-red-500" title="Logout"><i class="fas fa-sign-out-alt text-xl"></i></button>
            </form>
        </div>
    </div>

    <div class="flex-1 flex flex-col min-h-screen relative">
        {{-- Navbar --}}
        <header class="bg-gradient-to-r from-blue-700 to-blue-600 shadow-lg sticky top-0 z-20 text-white shrink-0">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center">
                        <button id="sidebar-toggle" class="mr-3 p-2 rounded-md hover:bg-blue-800/50 focus:outline-none"><i class="fas fa-bars text-xl"></i></button>
                        <div class="flex items-center">
                            <img src="{{ asset('asset/images/logorakha.png') }}" alt="Logo" class="hidden sm:block h-8 w-8 mr-2 sm:h-10 sm:w-10 sm:mr-3">
                            <div>
                                <h1 class="text-sm sm:text-lg font-bold leading-tight tracking-wide">PT RAKHA NUSANTARA MEDIKA</h1>
                                <p class="text-xs text-blue-200 font-semibold leading-tight hidden sm:block">{{ $title }}</p>
                            </div>
                        </div>
                    </div>
                    {{-- Foto profil di navbar telah dihapus untuk mobile --}}
                </div>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1 relative z-0">
            {{ $slot }}
        </main>
    </div>

    {{-- Script Sidebar --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const toggleButton = document.getElementById('sidebar-toggle');
            const overlay = document.getElementById('sidebar-overlay');
            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
            toggleButton.addEventListener('click', function (e) { e.stopPropagation(); toggleSidebar(); });
            overlay.addEventListener('click', function () { toggleSidebar(); });
        });
    </script>
    
    @stack('modals')
    @stack('scripts')

    <x-toast />
    
    {{-- SweetAlert2 CDN & Global Confirm Function (Light Theme) --}}
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