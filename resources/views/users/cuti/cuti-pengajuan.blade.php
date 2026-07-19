<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

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
            margin-bottom: 24px;
            width: 100%;
            justify-content: center;
            min-height: 44px;
        }
        @media (min-width: 768px) {
            .btn-back-modern {
                width: fit-content;
                justify-content: flex-start;
            }
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

        /* == Highlight Card Sisa Cuti == */
        .card-sisa-cuti {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.25);
            border: 1px solid rgba(255,255,255,0.2);
        }
        @media (min-width: 768px) {
            .card-sisa-cuti { border-radius: 24px; padding: 24px; }
        }
        .card-sisa-cuti::after {
            content: ''; position: absolute; top: -30%; right: -10%;
            width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,0.1);
        }
        .card-sisa-cuti::before {
            content: ''; position: absolute; bottom: -20%; right: 20%;
            width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.08);
        }

        /* == Forms & Inputs == */
        .modern-label { display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 8px; }
        .modern-input {
            width: 100%; background: rgba(255, 255, 255, 0.95);
            border: 2px solid #e2e8f0; border-radius: 14px;
            padding: 12px 16px; font-size: 0.95rem; color: #1e293b;
            outline: none; transition: all 0.2s ease;
        }
        .modern-input:focus {
            border-color: #3b82f6; background: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        .modern-input.error { border-color: #ef4444; }
        
        .modern-file-label {
            display: flex; align-items: center; gap: 12px;
            padding: 14px; background: #f8fafc;
            border: 2px dashed #cbd5e1; border-radius: 14px;
            cursor: pointer; font-size: 0.9rem; color: #64748b;
            transition: all 0.2s ease;
        }
        .modern-file-label:hover { border-color: #3b82f6; color: #1d4ed8; background: #eff6ff; }
        
        .btn-gradient {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white; border: none; padding: 12px 16px; border-radius: 14px;
            font-weight: 700; font-size: 0.9rem;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; transition: all 0.2s;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3); cursor: pointer;
            min-height: 44px;
        }
        @media (min-width: 768px) {
            .btn-gradient { padding: 16px 24px; font-size: 1rem; }
        }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4); }

        /* == Ringkasan Item == */
        .ringkasan-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 14px; background: rgba(248, 250, 252, 0.8);
            border-radius: 12px; margin-bottom: 10px; border: 1px solid #e2e8f0;
        }
        @media (min-width: 768px) {
            .ringkasan-item { padding: 14px 16px; border-radius: 14px; margin-bottom: 12px; }
        }
        .ringkasan-icon {
            width: 32px; height: 32px; border-radius: 8px; background: #eff6ff;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6; font-size: 0.85rem; margin-right: 10px; flex-shrink: 0;
        }
        @media (min-width: 768px) {
            .ringkasan-icon { width: 36px; height: 36px; border-radius: 10px; font-size: 0.95rem; margin-right: 12px; }
        }

        /* == Riwayat Item == */
        .riwayat-item {
            display: flex; flex-direction: column; gap: 10px; padding: 14px;
            background: rgba(255,255,255,0.8); border: 1.5px solid #e2e8f0; border-radius: 16px;
            text-decoration: none; margin-bottom: 12px; transition: all 0.2s;
        }
        @media (min-width: 640px) {
            .riwayat-item { flex-direction: row; align-items: center; gap: 14px; padding: 16px; }
        }
        .riwayat-item:hover { border-color: #bfdbfe; background: #eff6ff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .riwayat-badge { font-size: 0.75rem; font-weight: 800; padding: 6px 12px; border-radius: 999px; white-space: nowrap; width: fit-content; }
        @media (min-width: 640px) {
            .riwayat-badge { margin-left: auto; }
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
            
            {{-- TOMBOL KEMBALI MODERN --}}
            <a href="{{ route('dashboard') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Dashboard
            </a>
            <input type="hidden" id="libur-nasional-data" value='@json($liburNasional ?? [])'>

            {{-- GRID LAYOUT UTAMA --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

                {{-- KOLOM KIRI (Form Pengajuan) --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="glass-card">
                        <div class="flex items-center justify-between mb-5 md:mb-6">
                            <div class="flex items-center gap-2 md:gap-3">
                                <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg md:rounded-xl bg-blue-100 flex items-center justify-center text-blue-600"><i class="fas fa-file-signature text-lg md:text-xl"></i></div>
                                <h3 class="text-lg md:text-xl font-bold text-slate-800">Form Pengajuan Cuti</h3>
                            </div>
                            <a href="#riwayat-card" class="text-[10px] md:text-xs text-blue-600 font-bold hover:underline flex items-center gap-1.5 bg-blue-50 px-2 md:px-3 py-1.5 rounded-full border border-blue-100 transition-all hover:bg-blue-100">
                                <i class="fas fa-history"></i>
                                Lihat Riwayat
                            </a>
                        </div>

                        <form action="{{ route('cuti.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            <input type="hidden" name="jenis_cuti" value="tahunan">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="tanggal_mulai" class="modern-label">Tanggal Mulai</label>
                                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" min="{{ \Carbon\Carbon::now()->toDateString() }}" value="{{ old('tanggal_mulai') }}" class="modern-input {{ $errors->has('tanggal_mulai') ? 'error' : '' }}">
                                </div>
                                <div>
                                    <label for="tanggal_selesai" class="modern-label">Tanggal Selesai</label>
                                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" min="{{ \Carbon\Carbon::now()->toDateString() }}" value="{{ old('tanggal_selesai') }}" class="modern-input {{ $errors->has('tanggal_selesai') ? 'error' : '' }}">
                                </div>
                            </div>

                            <div>
                                <label for="alasan" class="modern-label">Alasan Cuti</label>
                                <textarea id="alasan" name="alasan" rows="3" class="modern-input {{ $errors->has('alasan') ? 'error' : '' }}" placeholder="Tuliskan alasan pengajuan cuti...">{{ old('alasan') }}</textarea>
                            </div>

                            <div>
                                <label class="modern-label">Lampiran Pendukung <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                <label for="lampiran" class="modern-file-label w-full flex-col sm:flex-row text-center sm:text-left">
                                    <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 flex-shrink-0 mx-auto sm:mx-0">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <div class="flex-1 w-full overflow-hidden text-ellipsis whitespace-nowrap">
                                        <p id="file-name-label" class="font-semibold text-slate-700 truncate">Pilih File</p>
                                        <p class="text-[10px] md:text-xs text-slate-400 mt-1 whitespace-normal">Format didukung: JPG, PNG, PDF (Maks 2MB)</p>
                                    </div>
                                    <input type="file" id="lampiran" name="lampiran" class="hidden" accept="image/*,application/pdf" onchange="document.getElementById('file-name-label').textContent = this.files[0]?.name || 'Pilih File'">
                                </label>
                            </div>

                            @if ($errors->any())
                                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                                    <p class="font-bold text-sm mb-2 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Gagal Mengajukan Cuti</p>
                                    <ul class="list-disc list-inside space-y-1 text-sm">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <button type="submit" class="btn-gradient mt-4">
                                <span>Kirim Pengajuan</span>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- KOLOM KANAN (Info & Riwayat) --}}
                <div class="lg:col-span-5 space-y-6">
                    
                    {{-- Card Sisa Cuti --}}
                    <div class="card-sisa-cuti">
                        <div class="relative z-10">
                            <p class="text-blue-100 font-semibold mb-1 text-xs md:text-sm uppercase tracking-wider">Sisa Cuti Tahunan</p>
                            <div class="flex items-baseline gap-1.5 md:gap-2">
                                <span class="text-4xl md:text-5xl font-black">{{ $sisaCuti ?? 0 }}</span>
                                <span class="text-lg md:text-xl font-medium text-blue-200">/ {{ $totalCuti ?? 0 }} Hari</span>
                            </div>
                        </div>
                        <div class="relative z-10 w-12 h-12 md:w-16 md:h-16 rounded-xl md:rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-2xl md:text-3xl">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>

                    {{-- Card Estimasi Durasi --}}
                    <div class="glass-card">
                        <h4 class="font-bold text-slate-800 mb-3 md:mb-4 text-sm md:text-base">Estimasi Durasi</h4>
                        
                        <div class="ringkasan-item">
                            <div class="flex items-center">
                                <div class="ringkasan-icon"><i class="fas fa-plane-departure"></i></div>
                                <span class="text-sm font-semibold text-slate-600">Mulai</span>
                            </div>
                            <span id="ringkasan-mulai" class="font-bold text-slate-900">-</span>
                        </div>
                        <div class="ringkasan-item">
                            <div class="flex items-center">
                                <div class="ringkasan-icon"><i class="fas fa-plane-arrival"></i></div>
                                <span class="text-sm font-semibold text-slate-600">Selesai</span>
                            </div>
                            <span id="ringkasan-selesai" class="font-bold text-slate-900">-</span>
                        </div>
                        <div class="ringkasan-item bg-blue-50/50 border-blue-100">
                            <div class="flex items-center">
                                <div class="ringkasan-icon bg-blue-600 text-white"><i class="fas fa-stopwatch"></i></div>
                                <span class="text-sm font-semibold text-blue-800">Total Durasi</span>
                            </div>
                            <span id="total-hari" class="font-black text-blue-700 text-base md:text-lg">- Hari</span>
                        </div>
                        <div class="flex items-start gap-3 p-3 mt-3 bg-amber-50 rounded-xl border border-amber-100">
                            <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                            <p class="text-xs text-amber-800 font-medium leading-relaxed">Sabtu, Minggu, dan Hari Libur Nasional tidak dapat dipilih sebagai tanggal cuti karena sudah termasuk hari libur.</p>
                        </div>
                    </div>

                    {{-- Card Riwayat Terakhir --}}
                    <div id="riwayat-card" class="glass-card border-t-4 border-t-blue-500 scroll-mt-6">
                        <div class="flex items-center justify-between mb-3 md:mb-4">
                            <h4 class="font-bold text-slate-800 flex items-center gap-2 text-sm md:text-base">
                                <i class="fas fa-history text-blue-600"></i> Riwayat Terbaru
                            </h4>
                            <a href="javascript:void(0)" onclick="window.location.reload()" class="text-sm text-blue-600 font-semibold hover:underline">Muat Ulang</a>
                        </div>
                        
                        <div class="space-y-3">
                            @forelse ($cutiRequests->take(3) as $cuti)
                                @php
                                    $status = $cuti->status;
                                    $iconClass = match($status) {
                                        'disetujui'  => 'fas fa-check',
                                        'ditolak'    => 'fas fa-times',
                                        'dibatalkan' => 'fas fa-ban',
                                        default      => 'fas fa-clock',
                                    };
                                    $iconBox = match($status) {
                                        'disetujui'  => 'bg-green-100 text-green-600',
                                        'ditolak'    => 'bg-red-100 text-red-600',
                                        'dibatalkan' => 'bg-slate-100 text-slate-600',
                                        default      => 'bg-amber-100 text-amber-600',
                                    };
                                    $badgeStyle = match($status) {
                                        'disetujui'  => 'bg-green-100 text-green-700 border border-green-200',
                                        'ditolak'    => 'bg-red-100 text-red-700 border-red-200',
                                        'dibatalkan' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        default      => 'bg-amber-100 text-amber-700 border-amber-200',
                                    };
                                @endphp
                                <a href="{{ route('cuti.show', $cuti) }}" class="riwayat-item">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $iconBox }} flex-shrink-0">
                                            <i class="{{ $iconClass }}"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-slate-800 text-sm truncate">
                                                {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M y') }} - {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M y') }}
                                            </p>
                                            <p class="text-xs text-slate-400 mt-0.5 truncate">Diajukan: {{ \Carbon\Carbon::parse($cuti->created_at)->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    <span class="riwayat-badge {{ $badgeStyle }} w-full sm:w-auto text-center">{{ ucfirst($status) }}</span>
                                </a>
                            @empty
                                <div class="p-8 text-center border-2 border-dashed border-slate-200 rounded-2xl">
                                    <div class="w-12 h-12 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-3 text-xl">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                    <p class="text-sm text-slate-500 font-medium">Belum ada riwayat.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tglMulai  = document.getElementById('tanggal_mulai');
        const tglSelesai = document.getElementById('tanggal_selesai');
        const totalHariElem       = document.getElementById('total-hari');
        const ringkasanMulaiElem  = document.getElementById('ringkasan-mulai');
        const ringkasanSelesaiElem = document.getElementById('ringkasan-selesai');

        const liburNasional = JSON.parse(document.getElementById('libur-nasional-data').value || '[]');

        function formatTanggal(tanggalStr) {
            if (!tanggalStr) return '-';
            const options = { day: 'numeric', month: 'short', year: 'numeric' };
            const date = new Date(tanggalStr + 'T00:00:00');
            return date.toLocaleDateString('id-ID', options);
        }

        function hitungDurasi() {
            const startDateStr = tglMulai.value;
            const endDateStr   = tglSelesai.value;

            ringkasanMulaiElem.textContent  = formatTanggal(startDateStr);
            ringkasanSelesaiElem.textContent = formatTanggal(endDateStr);

            if (startDateStr && endDateStr) {
                const start = new Date(startDateStr);
                const end   = new Date(endDateStr);
                start.setHours(0,0,0,0);
                end.setHours(0,0,0,0);

                if (end < start) {
                    totalHariElem.textContent = 'Tanggal Tidak Valid';
                    totalHariElem.style.color = '#ef4444';
                    return;
                }

                let countDays = 0;
                let currentDate = new Date(start);

                while (currentDate <= end) {
                    const dayOfWeek = currentDate.getDay();
                    const year  = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const day   = String(currentDate.getDate()).padStart(2, '0');
                    const dateString = `${year}-${month}-${day}`;

                    const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                    const isHoliday = liburNasional.includes(dateString);

                    if (!isWeekend && !isHoliday) countDays++;
                    currentDate.setDate(currentDate.getDate() + 1);
                }

                if (countDays === 0) {
                    totalHariElem.textContent = '0 Hari (Full Libur)';
                    totalHariElem.style.color = '#ef4444';
                } else {
                    totalHariElem.textContent = `${countDays} Hari`;
                    totalHariElem.style.color = '#1d4ed8';
                }
            } else {
                totalHariElem.textContent = '- Hari';
                totalHariElem.style.color = '#1d4ed8';
            }
        }

        tglMulai.addEventListener('change', hitungDurasi);
        tglSelesai.addEventListener('change', hitungDurasi);
        hitungDurasi();
    });
    </script>
    @endpush
</x-layout-users>