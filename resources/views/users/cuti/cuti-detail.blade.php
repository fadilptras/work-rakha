<x-layout-users>
    <x-slot:title>Detail Pengajuan Cuti</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
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

        /* Float animation */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float-delayed { animation: float 10s ease-in-out infinite; animation-delay: 2s; }

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
            width: 100%;
            justify-content: center;
            min-height: 44px;
        }
        @media (min-width: 768px) {
            .btn-back-modern { width: fit-content; justify-content: flex-start; }
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

        /* == Modern PDF Button == */
        .btn-pdf-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 10px 22px 10px 10px;
            background: linear-gradient(135deg, #dc2626, #ef4444);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
            color: white;
            font-size: 0.9rem; font-weight: 800;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.25);
            width: 100%;
            justify-content: center;
            min-height: 44px;
        }
        @media (min-width: 768px) {
            .btn-pdf-modern { width: fit-content; justify-content: flex-start; }
        }
        .btn-pdf-modern:hover { 
            background: linear-gradient(135deg, #b91c1c, #dc2626);
            box-shadow: 0 12px 25px rgba(220, 38, 38, 0.4);
            transform: translateY(-2px);
            color: white;
        }
        .btn-pdf-modern .pdf-icon-circle {
            width: 32px; height: 32px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-size: 0.9rem;
            transition: transform 0.3s ease;
        }
        .btn-pdf-modern:hover .pdf-icon-circle {
            transform: scale(1.1) rotate(5deg);
            background: rgba(255, 255, 255, 0.3);
        }

        /* == Glass Cards == */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 16px;
        }
        @media (min-width: 768px) {
            .glass-card { border-radius: 24px; padding: 24px; }
        }

        /* Pulse animation for actions */
        @keyframes pulse-subtle {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.2); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        .animate-pulse-subtle {
            animation: pulse-subtle 3s infinite;
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">
        {{-- Background Animations --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[10%] left-[5%] w-32 h-32 bg-white/40 backdrop-blur-md border border-white/50 rounded-full animate-float"></div>
            <div class="absolute bottom-[15%] right-[10%] w-48 h-48 bg-white/30 backdrop-blur-md border border-white/40 rounded-full animate-float-delayed"></div>
            <div class="absolute inset-0" style="background-image: radial-gradient(rgba(100, 116, 139, 0.1) 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col">
            
            {{-- 1. NAVIGASI & AKSI --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 mb-6">
                <a href="{{ route('cuti.create') }}" class="btn-back-modern">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    Kembali ke Riwayat
                </a>

                <a href="{{ route('cuti.download', $cuti->id) }}" class="btn-pdf-modern">
                    <div class="pdf-icon-circle"><i class="fas fa-file-pdf"></i></div>
                    <span>Cetak PDF</span>
                </a>
            </div>

            {{-- 2. HEADER UTAMA --}}
            <div class="mb-6 overflow-hidden rounded-2xl md:rounded-3xl shadow-xl border border-white/20">
                <div class="p-5 md:p-8 bg-gradient-to-r from-blue-700 to-indigo-600 text-white relative">
                    {{-- Decorative circles --}}
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                    <div class="absolute right-20 -bottom-10 w-24 h-24 bg-white/10 rounded-full blur-lg pointer-events-none"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4 md:gap-5">
                            <div class="h-12 w-12 md:h-14 md:w-14 rounded-xl md:rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                                <i class="fas fa-calendar-alt text-xl md:text-2xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-xl md:text-2xl font-black tracking-tight uppercase leading-tight">Pengajuan Cuti {{ $cuti->jenis_cuti }}</h1>
                                <p class="text-blue-100 text-xs md:text-sm mt-1 font-medium flex flex-wrap items-center gap-1 md:gap-2">
                                    <i class="far fa-clock"></i>
                                    Diajukan pada {{ $cuti->created_at->translatedFormat('d M Y, H:i') }} WIB
                                </p>
                            </div>
                        </div>

                        <div class="w-full md:w-auto text-center md:text-right mt-2 md:mt-0">
                            @php
                            $statusBg = match($cuti->status) {
                                'disetujui'  => 'bg-green-100 text-green-700 border-green-200',
                                'ditolak'    => 'bg-red-100 text-red-700 border-red-200',
                                'dibatalkan' => 'bg-slate-100 text-slate-700 border-slate-200',
                                default      => 'bg-amber-100 text-amber-700 border-amber-200',
                            };
                            $statusIcon = match($cuti->status) {
                                'disetujui'  => 'fas fa-check-circle',
                                'ditolak'    => 'fas fa-times-circle',
                                'dibatalkan' => 'fas fa-ban',
                                default      => 'fas fa-spinner fa-spin',
                            };
                            $label = str_replace('_', ' ', $cuti->status);
                            @endphp
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-[10px] md:text-xs font-black uppercase tracking-wider bg-white/90 border border-white {{ $statusBg }} shadow-sm w-full md:w-auto justify-center">
                                <i class="{{ $statusIcon }}"></i>
                                {{ $label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. INFO PENGAJUAN & TIMELINE --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {{-- INFO DETAIL --}}
                <div class="lg:col-span-1">
                    <div class="glass-card h-full flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 md:gap-3 mb-5 md:mb-6">
                                <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg md:rounded-xl bg-blue-100 flex items-center justify-center text-blue-600"><i class="fas fa-info-circle text-lg md:text-xl"></i></div>
                                <h3 class="text-base md:text-lg font-black text-slate-800">Detail Pengajuan</h3>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="text-slate-400 text-[10px] md:text-xs font-extrabold uppercase tracking-widest block mb-1">Pemohon</span>
                                    <span class="font-bold text-slate-800 text-sm md:text-base">{{ $cuti->user->name ?? 'N/A' }}</span>
                                </div>
                                <hr class="border-slate-100">
                                <div>
                                    <span class="text-slate-400 text-[10px] md:text-xs font-extrabold uppercase tracking-widest block mb-1">Rentang Tanggal</span>
                                    <span class="font-bold text-slate-800 text-xs md:text-sm">
                                        {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                                    </span>
                                    <div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 font-extrabold text-[10px] md:text-xs border border-blue-100">
                                        <i class="far fa-calendar"></i>
                                        {{ $cuti->total_hari }} Hari Kerja
                                    </div>
                                </div>
                                <hr class="border-slate-100">
                                <div>
                                    <span class="text-slate-400 text-[10px] md:text-xs font-extrabold uppercase tracking-widest block mb-1">Alasan Cuti</span>
                                    <p class="text-slate-700 italic text-xs md:text-sm leading-relaxed bg-slate-50/50 p-3 rounded-xl border border-slate-100">"{{ $cuti->alasan }}"</p>
                                </div>
                            </div>
                        </div>

                        @if($cuti->lampiran)
                        <div class="mt-6 pt-4 border-t border-slate-100">
                            <a href="{{ asset('storage/' . $cuti->lampiran) }}" target="_blank" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 min-h-[44px] rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 font-bold text-xs transition-colors">
                                <i class="fas fa-paperclip"></i>
                                Lihat Lampiran Dokumen
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- TIMELINE PROGRESS (4 TAHAP DINAMIS) --}}
                <div class="lg:col-span-2">
                    <div class="glass-card h-full flex flex-col mt-6 lg:mt-0">
                        <div class="flex items-center gap-2 md:gap-3 mb-5 md:mb-6">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg md:rounded-xl bg-blue-100 flex items-center justify-center text-blue-600"><i class="fas fa-tasks text-lg md:text-xl"></i></div>
                            <h3 class="text-base md:text-lg font-black text-slate-800">Timeline Persetujuan</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-grow">
                            @foreach([
                            1 => ['nama' => 'Tahap 1', 'user' => $cuti->approver1, 'status' => $cuti->status_approver_1, 'catatan' => $cuti->catatan_approver_1],
                            2 => ['nama' => 'Tahap 2', 'user' => $cuti->approver2, 'status' => $cuti->status_approver_2, 'catatan' => $cuti->catatan_approver_2],
                            3 => ['nama' => 'Tahap 3', 'user' => $cuti->approver3, 'status' => $cuti->status_approver_3, 'catatan' => $cuti->catatan_approver_3],
                            4 => ['nama' => 'Tahap 4 (Admin)', 'user' => $cuti->approver4, 'status' => $cuti->status_approver_4, 'catatan' => $cuti->catatan_approver_4],
                            ] as $stage => $data)
                            @php
                            $theme = match($data['status']) {
                                'disetujui' => ['border' => 'border-l-green-500', 'bg' => 'bg-green-50/50', 'badge' => 'bg-green-100 text-green-700 border-green-200'],
                                'ditolak'   => ['border' => 'border-l-red-500', 'bg' => 'bg-red-50/50', 'badge' => 'bg-red-100 text-red-700 border-red-200'],
                                'skipped'   => ['border' => 'border-l-slate-300', 'bg' => 'bg-slate-50', 'badge' => 'bg-slate-200 text-slate-600 border-slate-300'],
                                default     => ['border' => 'border-l-amber-400', 'bg' => 'bg-amber-50/50', 'badge' => 'bg-amber-100 text-amber-700 border-amber-200'],
                            };
                            @endphp
                            <div class="rounded-2xl border border-slate-200 p-4 flex flex-col justify-between {{ $theme['bg'] }} {{ $theme['border'] }} border-l-[6px] shadow-sm transition-all hover:shadow-md">
                                <div>
                                    <div class="flex justify-between items-start mb-2 gap-2">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $data['nama'] }}</span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider border {{ $theme['badge'] }}">
                                            {{ $data['status'] }}
                                        </span>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-800 truncate">{{ $data['user']->name ?? 'Belum Ditentukan' }}</h4>
                                </div>

                                @if($data['catatan'])
                                <div class="mt-3 p-3 bg-white/80 backdrop-blur-sm rounded-xl border border-slate-100 text-[11px] text-slate-600 italic leading-relaxed">
                                    <span class="font-bold not-italic block text-[9px] text-slate-400 uppercase tracking-wider mb-0.5">Catatan:</span>
                                    "{{ $data['catatan'] }}"
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. FORM TINDAKAN (LOGIKA URUTAN KETAT) --}}
            @php
            $user = Auth::user();
            $app1Done = ($cuti->status_approver_1 != 'menunggu');
            $app2Done = ($cuti->status_approver_2 != 'menunggu');
            $app3Done = ($cuti->status_approver_3 != 'menunggu');

            $isAppr1 = ($user->id == $cuti->approver_cuti_1_id && $cuti->status_approver_1 == 'menunggu');
            $isAppr2 = ($user->id == $cuti->approver_cuti_2_id && $cuti->status_approver_2 == 'menunggu' && $app1Done);
            $isAppr3 = ($user->id == $cuti->approver_cuti_3_id && $cuti->status_approver_3 == 'menunggu' && $app1Done && $app2Done);
            $isAppr4 = ($user->id == $cuti->approver_cuti_4_id && $cuti->status_approver_4 == 'menunggu' && $app1Done && $app2Done && $app3Done);

            $showForm = $isAppr1 || $isAppr2 || $isAppr3 || $isAppr4;
            @endphp

            @if($showForm)
            <div class="glass-card border-t-4 border-t-blue-500 mb-8 animate-pulse-subtle">
                <div class="flex items-center gap-2 md:gap-3 mb-5 md:mb-6">
                    <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg md:rounded-xl bg-blue-100 flex items-center justify-center text-blue-600"><i class="fas fa-gavel text-lg md:text-xl"></i></div>
                    <h3 class="text-lg md:text-xl font-bold text-slate-800 flex flex-wrap items-center gap-1">Tindakan Persetujuan <span class="text-blue-600 text-xs md:text-sm font-semibold">(Giliran Anda)</span></h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- FORM SETUJU --}}
                    <form action="{{ route('cuti.updateStatus', $cuti->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-green-50/50 p-6 rounded-2xl border border-green-100 text-center h-full flex flex-col justify-between hover:shadow-md transition-all">
                            <div>
                                <label class="block text-sm font-bold text-green-800 mb-3">Setujui Pengajuan Cuti</label>
                                <textarea name="catatan" rows="3" class="w-full p-3 border border-green-200 rounded-xl mb-4 text-sm focus:ring-green-500 focus:border-green-500 bg-white" placeholder="Berikan catatan persetujuan (opsional)..."></textarea>
                                <input type="hidden" name="status" value="disetujui">
                            </div>
                            <button type="submit" class="w-full bg-green-600 text-white font-bold py-3.5 rounded-xl hover:bg-green-700 transition shadow-lg shadow-green-600/20 active:scale-95 flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> Setujui Sekarang
                            </button>
                        </div>
                    </form>

                    {{-- FORM TOLAK --}}
                    <form action="{{ route('cuti.updateStatus', $cuti->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-red-50/50 p-6 rounded-2xl border border-red-100 text-center h-full flex flex-col justify-between hover:shadow-md transition-all">
                            <div>
                                <label class="block text-sm font-bold text-red-800 mb-3">Tolak Pengajuan <span class="text-red-500">*</span></label>
                                <textarea name="catatan" rows="3" class="w-full p-3 border border-red-200 rounded-xl mb-4 text-sm focus:ring-red-500 focus:border-red-500 bg-white" placeholder="Alasan penolakan wajib diisi..." required></textarea>
                                <input type="hidden" name="status" value="ditolak">
                            </div>
                            <button type="submit" class="w-full bg-red-600 text-white font-bold py-3.5 rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-600/20 active:scale-95 flex items-center justify-center gap-2">
                                <i class="fas fa-times-circle"></i> Tolak Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- 5. BATALKAN (JIKA PEMILIK) --}}
            @if(Auth::id() == $cuti->user_id && in_array($cuti->status, ['diajukan', 'proses_finalisasi']))
            <div class="text-center mb-10 mt-6 px-0 md:px-4">
                <form action="{{ route('cuti.cancel', $cuti) }}" method="POST" onsubmit="confirmSubmit(event, 'Yakin ingin membatalkan pengajuan cuti ini?');" class="block w-full md:inline-block md:w-auto">
                    @csrf
                    <button type="submit" class="inline-flex w-full md:w-auto min-h-[44px] justify-center items-center gap-2 px-5 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 border border-red-100 text-red-700 text-xs font-bold transition-all uppercase tracking-widest shadow-sm">
                        <i class="fas fa-trash-alt"></i> Batalkan Pengajuan Cuti Saya
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-layout-users>