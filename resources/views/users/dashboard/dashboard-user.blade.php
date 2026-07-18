<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* == Modern Glassmorphism Blue Theme == */
        
        /* Custom Scrollbar Modern */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #93c5fd; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #60a5fa; }
        
        /* == Kalender (Ukuran Kompak Dinonaktifkan Baris Ekstra) == */
        .fc {
            border: none !important;
            background: transparent !important;
            border-radius: 1rem;
            padding: 0.25rem;
        }
        .fc .fc-toolbar {
            margin-bottom: 0.25rem !important;
        }
        .fc .fc-toolbar-title { font-size: 1rem; font-weight: 800; color: #0f172a; }
        .fc .fc-button {
            background: rgba(255, 255, 255, 0.8) !important; border: 1px solid rgba(0, 0, 0, 0.08) !important; box-shadow: 0 2px 4px rgba(0,0,0,0.01) !important;
            color: #334155 !important; transition: all 0.2s; padding: 0 !important;
            width: 28px; height: 28px; display: flex; justify-content: center; align-items: center; border-radius: 9999px;
        }
        .fc .fc-button:hover { color: #166534 !important; background: #dcfce7 !important; transform: scale(1.1); }
        .fc .fc-button .fc-icon { font-size: 0.85rem; }
        .fc .fc-col-header-cell { border: none !important; padding: 2px 0; }
        .fc .fc-col-header-cell-cushion { color: #166534; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }
        .fc .fc-daygrid-day-frame {
            display: flex; 
            flex-direction: column;
            align-items: center;
            padding-top: 1px;
            min-height: unset !important;
        }
        .fc .fc-daygrid-day-number {
            width: 22px; height: 22px; line-height: 22px; text-align: center; border-radius: 9999px;
            font-weight: 600; transition: all 0.2s; font-size: 0.7rem; color: #1e293b;
            flex-shrink: 0; 
        }
        .fc .fc-day-other .fc-daygrid-day-number { color: #94a3b8; }
        .fc .fc-daygrid-day:not(.fc-day-other):hover .fc-daygrid-day-number { background-color: rgba(22, 101, 52, 0.1); color: #166534; }
        .fc .fc-day-today .fc-daygrid-day-number {
            font-weight: 700; color: #ffffff !important; background: linear-gradient(135deg, #15803d, #166534) !important;
            box-shadow: 0 1px 4px rgba(22, 101, 52, 0.15);
        }
        .fc .selected-date .fc-daygrid-day-number { background: #0f172a !important; color: #fff !important; font-weight: 700; }
        
        /* Jarak Scrollbar Kalender dibuat seimbang */
        .fc .fc-view-harness {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        /* ===== MODIFIKASI TAMPILAN AGAR LEBIH RAPIH (DESKTOP) ===== */

        /* 1. Atur container agenda agar rapi di bawah tanggal */
        .fc .fc-daygrid-day-events {
            margin-top: 4px; /* Beri jarak dari angka tanggal */
            width: 100%;
            padding: 0 4px; /* Beri sedikit padding horizontal */
        }
        
        /* 2. Rapikan tampilan setiap item agenda */
        .fc-daygrid-event {
            background-color: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            border-left-width: 3px !important;
            color: #374151 !important;
            font-size: 0.7rem !important;
            font-weight: 600;
            margin: 2px 0 !important; /* Rapikan margin, hanya atas-bawah */
            padding: 3px 6px !important; /* Sedikit tambah padding vertikal */
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease-in-out;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-align: left; /* Teks rata kiri */
        }

        .fc-daygrid-event:hover {
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }
        
        /* 3. Rapikan tampilan link "+ more" */
        .fc .fc-daygrid-more-link {
            color: #4338ca;
            font-size: 0.7rem; /* Samakan font size dengan agenda */
            font-weight: 600;
            text-decoration: none;
            padding: 3px 6px;
            border-radius: 6px;
            margin: 2px auto 0 auto; /* Posisi di tengah */
            display: inline-block; /* Agar bisa di-style */
        }
        .fc .fc-daygrid-more-link:hover {
            background-color: #e0e7ff;
            color: #312e81;
        }
        
        
        /* ===== MODIFIKASI TAMPILAN AGAR LEBIH RAPIH (MOBILE) ===== */
        @media (max-width: 768px) {

            /* Atur container agenda di mobile */
            .fc .fc-daygrid-day-events {
                margin-top: 2px; /* Jarak lebih kecil untuk mobile */
                padding: 0 2px;
            }

            /* Style dasar untuk Chip/Tag di mobile */
            .fc-daygrid-event {
                display: flex !important;
                align-items: center !important;
                background-color: #eef2ff !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 9999px !important;
                padding: 3px 8px 3px 4px !important;
                margin: 2px auto !important; /* Posisikan di tengah */
                width: 95%; /* Lebar konsisten */
                max-width: 120px; /* Batasi lebar maksimal */
                justify-content: flex-start;
            }

            /* Dot berwarna di dalam Chip */
            .fc-daygrid-event::before {
                content: '';
                display: inline-block;
                width: 6px;
                height: 6px;
                border-radius: 50%;
                margin-right: 6px;
                background-color: var(--fc-event-bg-color);
                flex-shrink: 0;
            }

            /* Teks di dalam Chip */
            .fc-daygrid-event .fc-event-title {
                font-size: 0.5rem !important;
                font-weight: 600;
                color: #4338ca !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .fc-daygrid-event:hover {
                background-color: #e0e7ff !important;
                transform: none !important;
                box-shadow: none !important;
            }

            /* Penyesuaian UI lainnya untuk mobile */
            .fc .fc-toolbar-title { font-size: 1.1rem; }
            .fc .fc-button { width: 32px; height: 32px; }
            .fc .fc-col-header-cell-cushion { font-size: 0.8rem; }
            .fc .fc-daygrid-day-number { width: 28px; height: 28px; line-height: 28px; font-size: 0.8rem; }
            .fc-event-time { display: none !important; }
        }

        /* == PENGECUALIAN KHUSUS HARI LIBUR == */
        .fc-daygrid-event.holiday-event {
            background-color: var(--fc-event-bg-color) !important; /* Kembalikan ke warna asli merah/oranye */
            border: none !important; /* Hilangkan border abu-abu */
            padding: 4px 6px !important;
            border-radius: 6px !important;
        }
        
        .fc-daygrid-event.holiday-event .fc-event-title {
            color: #ffffff !important; /* Paksa teks berwarna putih */
            font-weight: 700 !important;
        }

        /* Untuk Mobile: Sembunyikan dot/titik biru khusus hari libur */
        @media (max-width: 768px) {
            .fc-daygrid-event.holiday-event::before {
                display: none !important;
            }
        }

        /* == Notifikasi Bar Mobile == */
        .mobile-notif-bar {
            background: #1a1f2e;
            color: #e2e8f0;
            border-radius: 999px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.82rem;
            gap: 8px;
        }
        .mobile-notif-bar .notif-text { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mobile-notif-bar .notif-lihat { color: #93c5fd; font-weight: 600; white-space: nowrap; flex-shrink: 0; }

        /* == Weekly Strip Kalender Mobile == */
        .weekly-strip {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 1.5rem;
            padding: 14px 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(255, 255, 255, 1);
        }
        .weekly-strip .week-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .weekly-strip .week-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e3a5f;
            text-align: center;
            flex: 1;
        }
        .weekly-strip .week-nav-btn {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: none;
            background: transparent;
            color: #6b7280;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.15s;
        }
        .weekly-strip .week-nav-btn:hover { background: #dbeafe; color: #1d4ed8; }
        .weekly-strip .days-row {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            text-align: center;
        }
        .weekly-strip .day-label {
            font-size: 0.72rem;
            color: #9ca3af;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .weekly-strip .day-num {
            width: 34px; height: 34px;
            line-height: 34px;
            border-radius: 50%;
            font-size: 0.85rem;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: all 0.15s;
            margin: 0 auto;
        }
        .weekly-strip .day-num:hover { background: #dbeafe; }
        .weekly-strip .day-num.today {
            background: #1d4ed8;
            color: #fff;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(29,78,216,0.3);
        }
        .weekly-strip .day-num.selected {
            background: #111827;
            color: #fff;
            font-weight: 700;
        }
        .weekly-strip .day-num.has-event::after {
            content: '';
            display: block;
            width: 4px; height: 4px;
            border-radius: 50%;
            background: #3b82f6;
            margin: 1px auto 0;
        }
        .weekly-strip .day-num.today.has-event::after { background: #fff; }

        /* == Absensi Buttons Mobile == */
        .absensi-grid-mobile {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .absensi-btn-mobile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1f2937;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 1px 6px rgba(0,0,0,0.04);
        }
        .absensi-btn-mobile:hover { box-shadow: 0 4px 14px rgba(59,130,246,0.13); transform: translateY(-1px); }
        .absensi-btn-mobile.active-btn {
            background: #1d4ed8;
            color: #fff;
            border-color: #1d4ed8;
        }
        .absensi-btn-mobile.active-btn i { color: #fff; }
        .absensi-btn-mobile .btn-icon {
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
        }
        .absensi-btn-mobile.active-btn .btn-icon { background: rgba(255,255,255,0.2); }

        /* == Welcome Card Mobile == */
        .mobile-welcome-card {
            background: linear-gradient(135deg, #001BB7 0%, #1d4ed8 60%, #3b82f6 100%);
            border-radius: 1.5rem;
            padding: 18px 20px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .mobile-welcome-card::before {
            content: '';
            position: absolute;
            right: -30px; top: -30px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }
        .mobile-welcome-card::after {
            content: '';
            position: absolute;
            right: 30px; bottom: -40px;
            width: 90px; height: 90px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .mobile-welcome-card .avatar-circle {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: rgba(255,255,255,0.18);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            position: relative; z-index: 1;
            border: 2px solid rgba(255,255,255,0.3);
        }

        /* == Agenda Hari Ini Mobile == */
        .mobile-agenda-card {
            background: #fff;
            border-radius: 1rem;
            padding: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 12px rgba(59,130,246,0.07);
        }
        .mobile-agenda-empty {
            background: #f0f4ff;
            border-radius: 0.85rem;
            padding: 28px 16px;
            text-align: center;
            border: 1px solid #dbeafe;
        }

        /* == Add Agenda Button Override == */
        #add-agenda-btn, #mobile-add-agenda-btn {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(4, 120, 87, 0.25) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        #add-agenda-btn:hover, #mobile-add-agenda-btn:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%) !important;
            transform: scale(1.05) !important;
            box-shadow: 0 6px 16px rgba(4, 120, 87, 0.4) !important;
        }
        #add-agenda-btn i, #mobile-add-agenda-btn i {
            color: #ffffff !important;
        }

        /* == Modern Agenda & Detail Modals == */
        #agenda-modal, #agenda-detail-modal {
            z-index: 1000 !important;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            background: rgba(15, 23, 42, 0.3);
        }

        .modal-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid #e2e8f0;
            width: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: all 0.2s;
        }

        .modal-header {
            padding: 16px 20px;
            border-bottom: 1.5px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1e293b;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 14px 20px;
            border-top: 1.5px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            background: #f8fafc;
        }

        /* Form styling */
        .modal-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .modal-input {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.88rem;
            color: #1e293b;
            outline: none;
            transition: all 0.15s;
            box-sizing: border-box;
        }
        .modal-input:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* guest-list-container styling */
        .guest-list-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            padding: 10px;
            height: 150px;
            overflow-y: auto;
        }
        .guest-item {
            display: flex;
            align-items: center;
            padding: 7px 8px;
            border-radius: 8px;
            transition: background 0.1s;
        }
        .guest-item:hover { background: #f1f5f9; }
        .guest-item label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-left: 8px;
            cursor: pointer;
            width: 100%;
        }

        /* Color bar selector */
        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 12px;
        }
        .color-picker-input {
            width: 34px; height: 34px;
            padding: 0; border: none;
            background: none; cursor: pointer;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }
        .color-picker-input::-webkit-color-swatch-wrapper { padding: 0; }
        .color-picker-input::-webkit-color-swatch { border: none; border-radius: 50%; }

        .btn-modal-primary {
            padding: 10px 18px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #fff; font-size: 0.85rem; font-weight: 700;
            border: none; border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37,99,235,0.25);
            transition: all 0.15s;
        }
        .btn-modal-primary:hover { opacity: 0.92; transform: translateY(-1px); }

        .btn-modal-secondary {
            padding: 10px 18px;
            background: #f1f5f9;
            color: #475569; font-size: 0.85rem; font-weight: 700;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-modal-secondary:hover { background: #e2e8f0; }

        /* Detail Modal layout styles */
        .detail-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1.5px solid #f1f5f9;
        }
        .detail-icon-box {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: #eff6ff;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6; font-size: 0.88rem;
            flex-shrink: 0;
        }
        .detail-label { font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .detail-value { font-size: 0.85rem; font-weight: 800; color: #1e293b; }

        /* == Modern Mesh Background == */
        .mesh-bg {
            background-color: #f0f6fc;
            background-image: 
                radial-gradient(at 40% 20%, rgba(147, 197, 253, 0.45) 0px, transparent 50%),
                radial-gradient(at 80% 0%, rgba(167, 139, 250, 0.35) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(191, 219, 254, 0.45) 0px, transparent 50%),
                radial-gradient(at 80% 50%, rgba(139, 92, 246, 0.25) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(221, 214, 254, 0.4) 0px, transparent 50%),
                radial-gradient(at 80% 100%, rgba(96, 165, 250, 0.35) 0px, transparent 50%),
                radial-gradient(at 0% 0%, rgba(238, 242, 255, 0.6) 0px, transparent 50%);
            background-attachment: fixed;
        }

        /* Float animation for decorative elements */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float {
            animation: float 8s ease-in-out infinite;
        }
        .animate-float-delayed {
            animation: float 10s ease-in-out infinite;
            animation-delay: 2s;
        }

        /* == Custom Card Classes == */
        .card-pastel-green-cal {
            background: linear-gradient(135deg, #f7fdf9 0%, #e6f7ec 100%) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.9) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
        }
        .card-pastel-green-age {
            background: linear-gradient(135deg, #f7fdf9 0%, #e6f7ec 100%) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.9) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
        }
        .card-navy-blue {
            background: linear-gradient(135deg, #010825 0%, #06154c 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 10px 30px rgba(2, 11, 48, 0.25) !important;
        }

        /* == Custom Soft Pastel Buttons for Absensi == */
        .btn-absen-soft {
            background: #eff6ff !important;
            border: 1.5px solid #dbeafe !important;
            color: #1d4ed8 !important;
            box-shadow: 0 4px 10px rgba(29, 78, 216, 0.04) !important;
        }
        .btn-absen-soft:hover {
            background: #dbeafe !important;
            border-color: #bfdbfe !important;
            box-shadow: 0 6px 15px rgba(29, 78, 216, 0.08) !important;
        }
        .btn-aktivitas-soft {
            background: #faf5ff !important;
            border: 1.5px solid #e9d5ff !important;
            color: #7e22ce !important;
            box-shadow: 0 4px 10px rgba(126, 34, 206, 0.04) !important;
        }
        .btn-aktivitas-soft:hover {
            background: #f3e8ff !important;
            border-color: #d8b4fe !important;
            box-shadow: 0 6px 15px rgba(126, 34, 206, 0.08) !important;
        }
        .btn-cuti-soft {
            background: #f0fdf4 !important;
            border: 1.5px solid #bbf7d0 !important;
            color: #15803d !important;
            box-shadow: 0 4px 10px rgba(21, 128, 61, 0.04) !important;
        }
        .btn-cuti-soft:hover {
            background: #dcfce7 !important;
            border-color: #86efac !important;
            box-shadow: 0 6px 15px rgba(21, 128, 61, 0.08) !important;
        }
        .btn-rekap-soft {
            background: #fffbeb !important;
            border: 1.5px solid #fde68a !important;
            color: #b45309 !important;
            box-shadow: 0 4px 10px rgba(180, 83, 9, 0.04) !important;
        }
        .btn-rekap-soft:hover {
            background: #fef3c7 !important;
            border-color: #fcd34d !important;
            box-shadow: 0 6px 15px rgba(180, 83, 9, 0.08) !important;
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 h-full mesh-bg relative overflow-hidden">
        {{-- Dekorasi Latar Belakang Tambahan --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[10%] left-[5%] w-32 h-32 bg-white/40 backdrop-blur-md border border-white/50 rounded-full animate-float"></div>
            <div class="absolute bottom-[15%] right-[10%] w-48 h-48 bg-white/30 backdrop-blur-md border border-white/40 rounded-full animate-float-delayed"></div>
            <div class="absolute top-[40%] right-[30%] w-16 h-16 bg-white/40 backdrop-blur-sm border border-white/50 rounded-full animate-float" style="animation-delay: 1s;"></div>
            
            {{-- Pola Dot Grid --}}
            <div class="absolute inset-0" style="background-image: radial-gradient(rgba(100, 116, 139, 0.1) 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>
        <main class="flex-1 p-0 sm:p-6 lg:p-8 relative z-10">

            
            @if ($errors->any())
                <div class="mb-6 mx-6 lg:mx-0 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 text-sm rounded-md" role="alert">
                    <p class="font-bold">Terjadi Kesalahan</p>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $agent = new \Jenssegers\Agent\Agent();
            @endphp
            @if($agent->isMobile() || $agent->isTablet())
                {{-- ===== TAMPILAN MOBILE ===== --}}
                @include('users.dashboard.partials.mobile')
            @else
                {{-- ===== TAMPILAN DESKTOP ===== --}}
                @include('users.dashboard.partials.desktop')
            @endif

        </main>
    </div>

    {{-- KONTEN MODAL --}}
    <div id="agenda-modal" class="fixed inset-0 bg-black bg-opacity-60 z-40 hidden flex items-center justify-center p-4">
        <div class="modal-card max-w-3xl w-full max-h-[90vh]" id="agenda-modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title" id="modal-agenda-title-text">Buat Agenda Baru</h4>
                <button type="button" id="close-modal-btn" class="text-gray-400 hover:text-gray-700 transition"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form id="agenda-form" method="POST" class="flex flex-col flex-grow overflow-hidden">
                @csrf
                <div class="modal-body flex-grow custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Sisi Kiri: Detail Informasi & Waktu --}}
                        <div class="space-y-4">
                            {{-- Judul Agenda --}}
                            <div>
                                <label for="title" class="modal-label">Judul Agenda <span class="text-red-500">*</span></label>
                                <input type="text" id="title" name="title" required class="modal-input" placeholder="Contoh: Rapat Evaluasi Bulanan">
                                <small id="title-error" class="text-red-500 text-xs mt-1 hidden"></small>
                            </div>

                            {{-- Deskripsi --}}
                            <div>
                                <label for="description" class="modal-label">Deskripsi</label>
                                <textarea id="description" name="description" rows="3" class="modal-input" placeholder="Jelaskan detail agenda di sini..."></textarea>
                            </div>

                            {{-- Waktu Acara (Tanggal & Jam Side-by-Side) --}}
                            <div>
                                <label class="modal-label">Waktu Acara <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label for="agenda_date" class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal</label>
                                        <input type="text" id="agenda_date" required class="modal-input text-xs" placeholder="Tanggal">
                                    </div>
                                    <div>
                                        <label for="start_hour" class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Mulai</label>
                                        <input type="text" id="start_hour" required class="modal-input text-xs" placeholder="Jam">
                                    </div>
                                    <div>
                                        <label for="end_hour" class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Selesai</label>
                                        <input type="text" id="end_hour" required class="modal-input text-xs" placeholder="Jam">
                                    </div>
                                </div>
                                <small id="start_time-error" class="text-red-500 text-xs mt-1 hidden"></small>
                                <small id="end_time-error" class="text-red-500 text-xs mt-1 hidden"></small>
                            </div>
                        </div>

                        {{-- Sisi Kanan: Undang Tamu & Lokasi/Warna --}}
                        <div class="space-y-4">
                            <div>
                                <label class="modal-label">Undang Karyawan</label>
                                <div id="guest-list-container" class="guest-list-card custom-scrollbar" style="height: 148px;">
                                    <p class="text-gray-400 text-xs italic">Memuat karyawan...</p>
                                </div>
                            </div>

                            <div>
                                <label for="location" class="modal-label">Lokasi</label>
                                <input type="text" id="location" name="location" class="modal-input" placeholder="Contoh: Ruang Meeting Lt. 2">
                            </div>
                            <div>
                                <label for="color" class="modal-label">Warna Label</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" id="color" name="color" value="#3B82F6" class="color-picker-input">
                                    <span class="text-xs text-gray-500 font-semibold">Sentuh untuk memilih warna</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="cancel-btn" class="btn-modal-secondary">Batal</button>
                    <button type="submit" id="save-agenda-btn" class="btn-modal-primary">Simpan Agenda</button>
                </div>
            </form>
        </div>
    </div>

    <div id="agenda-detail-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden flex items-center justify-center p-4">
        <div class="modal-card max-w-md w-full max-h-[85vh] overflow-hidden" id="agenda-detail-content">
            {{-- KONTEN DETAIL AKAN DIISI OLEH JAVASCRIPT --}}
        </div>
    </div>

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('mini-calendar');
            const agendaListContainer = document.getElementById('agenda-list-container');
            const agendaListTitle = document.getElementById('agenda-list-title');
            let selectedDateEl = null;

            const detailModal = document.getElementById('agenda-detail-modal');
            const detailContent = document.getElementById('agenda-detail-content');
            const agendaModal = document.getElementById('agenda-modal');
            const addAgendaBtn = document.getElementById('add-agenda-btn');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const agendaForm = document.getElementById('agenda-form');
            const modalTitle = agendaModal.querySelector('h4');
            const saveButton = document.getElementById('save-agenda-btn');

            const agendaDate = flatpickr("#agenda_date", { dateFormat: "Y-m-d", altInput: true, altFormat: "d F Y", locale: "id" });
            const startHour = flatpickr("#start_hour", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true });
            const endHour = flatpickr("#end_hour", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true });
            
            function formatFullDate(date) { return date.toLocaleString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }
            function formatTime(date) { return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }); }

            function updateAgendaList(selectedDate) {
                const allEvents = calendar.getEvents();
                
                const startOfWeek = new Date(selectedDate);
                startOfWeek.setDate(selectedDate.getDate() - selectedDate.getDay() + (selectedDate.getDay() === 0 ? -6 : 1));
                startOfWeek.setHours(0, 0, 0, 0);

                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(startOfWeek.getDate() + 6);
                endOfWeek.setHours(23, 59, 59, 999);
                
                const startFormatted = startOfWeek.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                const endFormatted = endOfWeek.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                agendaListTitle.textContent = `Agenda (${startFormatted} - ${endFormatted})`;

                const eventsThisWeek = allEvents.filter(event => {
                    const eventDate = new Date(event.start);
                    return eventDate >= startOfWeek && eventDate <= endOfWeek;
                });

                agendaListContainer.innerHTML = '';
                
                if (eventsThisWeek.length > 0) {
                    eventsThisWeek.sort((a, b) => a.start - b.start).forEach(event => {
                        const startTime = event.allDay ? 'Seharian' : formatTime(event.start);
                        const endTime = (!event.allDay && event.end) ? formatTime(event.end) : '';
                        
                         const agendaHTML = `
                            <div data-event-id="${event.id}" class="agenda-item-clickable flex items-center gap-4 p-4 rounded-xl bg-white/90 border border-emerald-100 text-slate-800 transition-all duration-300 hover:bg-white hover:border-emerald-300 hover:shadow-lg cursor-pointer shadow-sm">
                                <div class="flex-shrink-0 text-center bg-emerald-50 text-emerald-800 rounded-lg px-3 py-2 w-20 border border-emerald-100">
                                    <p class="font-bold text-sm">${startTime}</p>
                                    ${endTime ? `<p class="text-xs opacity-75">${endTime}</p>` : ''}
                                </div>
                                <div class="flex-grow border-l-4 pl-4" style="border-color: ${event.backgroundColor || '#10B981'}">
                                    <p class="font-bold text-slate-800 text-base leading-snug">${event.extendedProps.fullTitle}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">${formatFullDate(event.start)}</p>
                                    ${event.extendedProps.location ? `<p class="text-sm text-slate-600 mt-1"><i class="fas fa-map-marker-alt mr-1 text-xs text-emerald-600"></i> ${event.extendedProps.location}</p>` : ''}
                                    ${event.extendedProps.type === 'holiday' ? `<p class="text-sm font-bold mt-1" style="color: ${event.backgroundColor};">${event.extendedProps.description}</p>` : ''}
                                </div>
                            </div>`;
                        agendaListContainer.innerHTML += agendaHTML;
                    });
                } else {
                     agendaListContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full text-center text-emerald-800 p-6 bg-white/60 rounded-xl border border-emerald-100/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-60 mb-2 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="font-bold text-sm">Tidak ada agenda</p>
                            <p class="text-xs opacity-70 mt-1 text-emerald-700">Pilih tanggal di kalender untuk melihat.</p>
                        </div>`;
                }
                
                document.querySelectorAll('.agenda-item-clickable').forEach(item => {
                    item.addEventListener('click', () => {
                        const eventId = item.dataset.eventId;
                        const event = calendar.getEventById(eventId);
                        if (event) showAgendaDetails(event);
                    });
                });
            }
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', 
                headerToolbar: { left: 'prev', center: 'title', right: 'next' },
                locale: 'id',
                buttonText: { today: 'hari ini' },
                events: "{{ route('agendas.index') }}",
                height: 275, 
                fixedWeekCount: false,
                dayMaxEvents: true,

                eventDidMount: function(info) {
                    if (info.event.extendedProps.type === 'holiday') {
                        // Paksa background menggunakan warna dari database (merah/oranye)
                        info.el.style.setProperty('background-color', info.event.backgroundColor, 'important');
                        info.el.style.setProperty('border', 'none', 'important');
                        
                        // Paksa teks menjadi putih agar terbaca
                        const titleEl = info.el.querySelector('.fc-event-title');
                        if (titleEl) {
                            titleEl.style.setProperty('color', '#ffffff', 'important');
                            titleEl.style.setProperty('font-weight', '700', 'important');
                        }
                    } else {
                        // Untuk Agenda: jadikan warna event sebagai garis border di sebelah kiri
                        info.el.style.setProperty('border-left-color', info.event.backgroundColor || '#3B82F6', 'important');
                    }
                },

                dateClick: function(info) {
                    if (selectedDateEl) {
                        selectedDateEl.classList.remove('selected-date');
                    }
                    info.dayEl.classList.add('selected-date');
                    selectedDateEl = info.dayEl;
                    
                    updateAgendaList(info.date);
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    showAgendaDetails(info.event);
                },
                eventsSet: function() {
                    updateAgendaList(calendar.getDate());

                    const urlParams = new URLSearchParams(window.location.search);
                    const agendaId = urlParams.get('agenda_id');

                    if (agendaId) {
                        const event = calendar.getEventById('agenda_' + agendaId);
                        
                        if (event) {
                            showAgendaDetails(event);
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }
                    }
                }
            });
            calendar.render();

            // Update kalender saat viewport berubah (mobile <-> desktop)
            window.addEventListener('resize', function() {
                calendar.updateSize();
            });

            function showAgendaDetails(event) {
                const props = event.extendedProps;
                const startTime = event.allDay ? 'Seharian' : formatTime(event.start);
                const endTime = (!event.allDay && event.end) ? formatTime(event.end) : '';
                const timeDisplay = event.allDay ? 'Seharian Penuh' : `${startTime} - ${endTime} WIB`;

                let organizerAndGuestsHTML = '';
                
                if (props.type === 'agenda') {
                    let guestsHTML = '<p class="text-gray-400 text-xs italic">Tidak ada tamu yang diundang.</p>';
                    if (props.guests && props.guests.length > 0) {
                        guestsHTML = `<div class="flex flex-wrap gap-1.5">${props.guests.map(guest => `<span class="bg-blue-50 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full border border-blue-100">${guest}</span>`).join('')}</div>`;
                    }

                    organizerAndGuestsHTML = `
                        <div class="detail-row">
                            <div class="detail-icon-box"><i class="fas fa-user-tie"></i></div>
                            <div>
                                <p class="detail-label">Penyelenggara</p>
                                <p class="detail-value">${props.organizer}</p>
                            </div>
                        </div>
                        <div class="detail-row" style="flex-direction: column; align-items: stretch; gap: 8px;">
                            <div class="flex items-center gap-3">
                                <div class="detail-icon-box"><i class="fas fa-users"></i></div>
                                <div>
                                    <p class="detail-label" style="margin-bottom: 0;">Tamu Undangan</p>
                                </div>
                            </div>
                            <div class="pt-2">${guestsHTML}</div>
                        </div>
                    `;
                }

                let actionButtonsHTML = '';
                if (props.type === 'agenda' && props.is_creator) {
                    const realId = String(event.id).replace('agenda_', ''); 
                    
                    const editButton = `<button type="button" id="edit-agenda-btn" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-lg text-sm transition">Edit</button>`;
                    const csrfToken = document.querySelector('form#agenda-form input[name="_token"]').value;
                    const deleteUrl = "{{ route('agendas.destroy', ['agenda' => ':id']) }}".replace(':id', realId);
                    const deleteForm = `
                        <form action="${deleteUrl}" method="POST" onsubmit="confirmSubmit(event, 'Apakah Anda yakin ingin menghapus agenda ini?')" class="ml-2">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition">Hapus</button>
                        </form>
                    `;
                    actionButtonsHTML = `
                        <button id="close-detail-modal-bottom-btn" class="btn-modal-secondary mr-auto">Tutup</button>
                        ${editButton}
                        ${deleteForm}
                    `;
                } else {
                    actionButtonsHTML = `<button id="close-detail-modal-bottom-btn" class="btn-modal-secondary ml-auto">Tutup</button>`;
                }

                const headerLabel = props.type === 'holiday' ? 'Informasi Libur' : 'Detail Agenda';

                const contentHTML = `
                    <div class="modal-header">
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-wider" style="color: ${event.backgroundColor || '#3B82F6'}">${headerLabel}</p>
                            <h4 class="text-lg font-bold text-gray-900 mt-0.5">${props.fullTitle}</h4>
                        </div>
                        <button type="button" id="close-detail-modal-btn" class="text-gray-400 hover:text-gray-700 transition"><i class="fas fa-times text-xl"></i></button>
                    </div>
                    
                    <div class="modal-body space-y-4 max-h-[55vh] overflow-y-auto custom-scrollbar">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="detail-row">
                                <div class="detail-icon-box"><i class="fas fa-calendar-alt"></i></div>
                                <div>
                                    <p class="detail-label">Waktu & Tanggal</p>
                                    <p class="detail-value">${formatFullDate(event.start)}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">${timeDisplay}</p>
                                </div>
                            </div>
                            
                            ${props.location ? `
                            <div class="detail-row">
                                <div class="detail-icon-box"><i class="fas fa-map-marker-alt"></i></div>
                                <div>
                                    <p class="detail-label">Lokasi</p>
                                    <p class="detail-value">${props.location}</p>
                                </div>
                            </div>` : ''}
                        </div>
                        
                        ${props.description ? `
                        <div class="detail-row" style="flex-direction: column; align-items: stretch; gap: 6px;">
                            <div class="flex items-center gap-3">
                                <div class="detail-icon-box"><i class="fas fa-info-circle"></i></div>
                                <span class="detail-label" style="margin-bottom: 0;">Keterangan</span>
                            </div>
                            <div class="text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100 text-xs leading-relaxed mt-1" style="white-space: pre-wrap;">${props.description}</div>
                        </div>` : ''}
                        
                        ${organizerAndGuestsHTML}
                    </div>
                    
                    <div class="modal-footer">
                        ${actionButtonsHTML}
                    </div>
                `;

                detailContent.innerHTML = contentHTML;
                detailModal.classList.remove('hidden');
                
                document.getElementById('close-detail-modal-btn').addEventListener('click', closeDetailModal);
                document.getElementById('close-detail-modal-bottom-btn').addEventListener('click', closeDetailModal);

                if (props.type === 'agenda' && props.is_creator) {
                    document.getElementById('edit-agenda-btn').addEventListener('click', () => openModalForEdit(event));
                }
            }
            
            function closeDetailModal() { detailModal.classList.add('hidden'); }
            detailModal.addEventListener('click', (e) => { if (e.target === detailModal) closeDetailModal(); });

            function openModalForCreate() {
                const existingMethodInput = agendaForm.querySelector('input[name="_method"]');
                if (existingMethodInput) existingMethodInput.remove();

                agendaForm.reset();
                agendaForm.setAttribute('action', "{{ route('agendas.store') }}"); 
                
                document.getElementById('modal-agenda-title-text').textContent = 'Buat Agenda Baru';
                saveButton.textContent = 'Simpan Agenda';
                document.getElementById('color').value = '#3B82F6';
                agendaDate.setDate(new Date());
                startHour.clear();
                endHour.clear();
                document.querySelectorAll('input[name="guests[]"]').forEach(cb => cb.checked = false);
                agendaModal.classList.remove('hidden');
            }

            function openModalForEdit(event) {
                closeDetailModal();
                const existingMethodInput = agendaForm.querySelector('input[name="_method"]');
                if (existingMethodInput) existingMethodInput.remove();

                agendaForm.reset();
                const realId = String(event.id).replace('agenda_', '');
                const updateUrl = "{{ route('agendas.update', ['agenda' => ':id']) }}".replace(':id', realId);
                agendaForm.setAttribute('action', updateUrl); 

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                agendaForm.appendChild(methodInput);
                
                document.getElementById('modal-agenda-title-text').textContent = 'Edit Agenda';
                saveButton.textContent = 'Update Agenda';
                
                document.getElementById('title').value = event.extendedProps.fullTitle;
                document.getElementById('description').value = event.extendedProps.description || '';
                document.getElementById('location').value = event.extendedProps.location || '';
                document.getElementById('color').value = event.backgroundColor || '#3B82F6';
                
                agendaDate.setDate(event.start, true, "Y-m-d");
                startHour.setDate(event.start, true, "H:i");
                if (event.end) endHour.setDate(event.end, true, "H:i");
                
                document.querySelectorAll('input[name="guests[]"]').forEach(cb => {
                    cb.checked = event.extendedProps.guest_ids.includes(parseInt(cb.value));
                });
                agendaModal.classList.remove('hidden');
            }

            function closeModal() { agendaModal.classList.add('hidden'); }

            addAgendaBtn.addEventListener('click', openModalForCreate);
            closeModalBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);
            agendaModal.addEventListener('click', (e) => { if (e.target === agendaModal) closeModal(); });

            agendaForm.addEventListener('submit', function(e) {
                this.querySelector('input[name="start_time"]')?.remove();
                this.querySelector('input[name="end_time"]')?.remove();

                const dateValue = document.getElementById('agenda_date')._flatpickr.input.value;
                const startHourValue = document.getElementById('start_hour')._flatpickr.input.value;
                const endHourValue = document.getElementById('end_hour')._flatpickr.input.value;

                if (dateValue && startHourValue) {
                    const startTimeInput = document.createElement('input');
                    startTimeInput.type = 'hidden';
                    startTimeInput.name = 'start_time';
                    startTimeInput.value = `${dateValue} ${startHourValue}`;
                    this.appendChild(startTimeInput);
                }

                if (dateValue && endHourValue) {
                    const endTimeInput = document.createElement('input');
                    endTimeInput.type = 'hidden';
                    endTimeInput.name = 'end_time';
                    endTimeInput.value = `${dateValue} ${endHourValue}`;
                    this.appendChild(endTimeInput);
                }
            });
            
            const guestContainer = document.getElementById('guest-list-container');
            fetch("{{ route('agendas.getUsers') }}")
                .then(response => response.json())
                .then(users => {
                    guestContainer.innerHTML = '';
                    if (users.length > 0) {
                        users.forEach(user => {
                            guestContainer.insertAdjacentHTML('beforeend', `
                                <div class="guest-item">
                                    <input id="guest-${user.id}" name="guests[]" value="${user.id}" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <label for="guest-${user.id}">${user.name}</label>
                                </div>
                            `);
                        });
                    } else {
                        guestContainer.innerHTML = '<p class="text-gray-400 text-xs italic">Tidak ada karyawan lain untuk diundang.</p>';
                    }
                });

            // == Mobile Add Agenda Button ==
            const mobileAddAgendaBtn = document.getElementById('mobile-add-agenda-btn');
            if (mobileAddAgendaBtn) {
                mobileAddAgendaBtn.addEventListener('click', openModalForCreate);
            }
        });
    </script>

    <script>
        // ============================================================
        // == MOBILE WEEKLY CALENDAR STRIP LOGIC ==
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            const prevBtn   = document.getElementById('mobile-prev-week');
            const nextBtn   = document.getElementById('mobile-next-week');
            const titleEl   = document.getElementById('mobile-week-title');
            const daysNumEl = document.getElementById('mobile-days-nums');
            const agendaTodayContainer = document.getElementById('mobile-agenda-today-container');

            if (!prevBtn || !nextBtn || !titleEl || !daysNumEl) return;

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Awal minggu berdasarkan Senin (ISO Week) - gambar mulai dari Min
            // Kita pakai Sunday-based week seperti gambar referensi (Min–Sab)
            let currentWeekStart = getSundayOfWeek(today);

            // Nama bulan singkat
            const monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

            function getSundayOfWeek(d) {
                const day = new Date(d);
                day.setHours(0,0,0,0);
                const dayOfWeek = day.getDay(); // 0=Sun
                day.setDate(day.getDate() - dayOfWeek);
                return day;
            }

            function formatShortDate(d) {
                return d.getDate() + ' ' + monthNames[d.getMonth()];
            }

            function isSameDay(a, b) {
                return a.getFullYear() === b.getFullYear() &&
                       a.getMonth() === b.getMonth() &&
                       a.getDate() === b.getDate();
            }

            // Ambil semua events dari server
            let allEvents = [];
            fetch("{{ route('agendas.index') }}")
                .then(r => r.json())
                .then(data => {
                    allEvents = data;
                    renderWeek(currentWeekStart);
                    renderTodayAgenda();
                })
                .catch(() => {
                    renderWeek(currentWeekStart);
                });

            function getEventDates() {
                return allEvents.map(ev => {
                    const d = new Date(ev.start);
                    d.setHours(0,0,0,0);
                    return d.getTime();
                });
            }

            function renderWeek(startDate) {
                // Title: "12 Jul - 18 Jul 2026"
                const endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);

                const startStr = startDate.getDate() + ' ' + monthNames[startDate.getMonth()];
                const endStr   = endDate.getDate() + ' ' + monthNames[endDate.getMonth()] + ' ' + endDate.getFullYear();
                titleEl.textContent = startStr + ' - ' + endStr;

                const eventTimes = getEventDates();
                daysNumEl.innerHTML = '';

                for (let i = 0; i < 7; i++) {
                    const day = new Date(startDate);
                    day.setDate(startDate.getDate() + i);

                    const isToday    = isSameDay(day, today);
                    const hasEvent   = eventTimes.includes(day.getTime());

                    const div = document.createElement('div');
                    div.style.display = 'flex';
                    div.style.flexDirection = 'column';
                    div.style.alignItems = 'center';

                    const span = document.createElement('span');
                    span.className = 'day-num' + (isToday ? ' today' : '') + (hasEvent ? ' has-event' : '');
                    span.textContent = day.getDate();

                    span.addEventListener('click', () => {
                        // Highlight selected
                        daysNumEl.querySelectorAll('.day-num').forEach(el => {
                            el.classList.remove('selected');
                        });
                        if (!isToday) span.classList.add('selected');
                        renderDayAgenda(day);
                    });

                    div.appendChild(span);
                    daysNumEl.appendChild(div);
                }
            }

            function renderTodayAgenda() {
                renderDayAgenda(today);
            }

            function formatTime(dateStr) {
                const d = new Date(dateStr);
                return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
            }

            function renderDayAgenda(day) {
                if (!agendaTodayContainer) return;

                const dayEvents = allEvents.filter(ev => {
                    const evDate = new Date(ev.start);
                    evDate.setHours(0,0,0,0);
                    return isSameDay(evDate, day);
                });

                if (dayEvents.length === 0) {
                    agendaTodayContainer.innerHTML = `
                        <div class="mobile-agenda-empty">
                            <i class="fas fa-calendar-alt text-3xl text-blue-300 mb-3"></i>
                            <p class="font-semibold text-blue-700 text-sm">Tidak ada agenda</p>
                            <p class="text-xs text-blue-400 mt-1">Tap + untuk menambah jadwal.</p>
                        </div>`;
                    return;
                }

                let html = '<div class="space-y-2">';
                dayEvents.forEach(ev => {
                    const color = ev.backgroundColor || '#3B82F6';
                    const timeStr = ev.allDay ? 'Seharian' : formatTime(ev.start);
                    html += `
                        <div class="flex items-center gap-3 p-3 rounded-xl border" style="border-left: 4px solid ${color}; background:#f9fafb;">
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">${ev.title || ev.extendedProps?.fullTitle || 'Agenda'}</p>
                                <p class="text-xs text-gray-500">${timeStr}</p>
                            </div>
                        </div>`;
                });
                html += '</div>';
                agendaTodayContainer.innerHTML = html;
            }

            prevBtn.addEventListener('click', () => {
                currentWeekStart.setDate(currentWeekStart.getDate() - 7);
                renderWeek(currentWeekStart);
            });

            nextBtn.addEventListener('click', () => {
                currentWeekStart.setDate(currentWeekStart.getDate() + 7);
                renderWeek(currentWeekStart);
            });
        });
    </script>
    @endpush
</x-layout-users>