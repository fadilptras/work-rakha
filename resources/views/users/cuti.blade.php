<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        /* ===== CUTI PAGE — MOBILE FIRST STYLES ===== */

        .cuti-page-wrapper {
            padding: 16px 16px 48px;
        }
        @media (min-width: 768px) {
            .cuti-page-wrapper { padding: 24px 24px 56px; }
        }

        /* Back Button */
        .cuti-back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 18px;
            background: #fff;
            border: 1.5px solid #dbeafe;
            border-radius: 999px;
            color: #1d4ed8;
            font-size: 0.82rem; font-weight: 600;
            text-decoration: none;
            transition: all 0.18s;
            margin-bottom: 16px;
        }
        .cuti-back-btn:hover { background: #eff6ff; }

        /* Sisa Cuti Card */
        .cuti-sisa-card {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%);
            border-radius: 18px;
            padding: 18px 20px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
            position: relative;
            overflow: hidden;
        }
        .cuti-sisa-card::before {
            content: '';
            position: absolute;
            top: -30px; right: -30px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }
        .cuti-sisa-label {
            font-size: 0.78rem; font-weight: 500;
            color: rgba(255,255,255,0.8);
            margin-bottom: 4px;
        }
        .cuti-sisa-num {
            font-size: 2.4rem; font-weight: 900;
            letter-spacing: -0.03em; line-height: 1;
        }
        .cuti-sisa-total {
            font-size: 1.1rem; font-weight: 500;
            color: rgba(255,255,255,0.75);
        }
        .cuti-sisa-icon {
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            width: 44px; height: 44px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        /* Section Card */
        .cuti-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 20px 18px;
            margin-bottom: 14px;
        }
        .cuti-card-title {
            font-size: 1rem; font-weight: 800; color: #111827;
            margin-bottom: 16px;
        }

        /* Form Fields */
        .cuti-label {
            display: block;
            font-size: 0.8rem; font-weight: 600; color: #374151;
            margin-bottom: 6px;
        }
        .cuti-input {
            width: 100%;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.88rem;
            color: #1f2937;
            outline: none;
            transition: border-color 0.15s;
            box-sizing: border-box;
        }
        .cuti-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        .cuti-input.error { border-color: #ef4444; }
        .cuti-input-group { margin-bottom: 14px; }

        /* File input */
        .cuti-file-label {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px;
            background: #f9fafb;
            border: 1.5px dashed #d1d5db;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.82rem; color: #6b7280;
            transition: all 0.15s;
        }
        .cuti-file-label:hover { border-color: #3b82f6; color: #1d4ed8; }

        /* Submit Button */
        .cuti-submit-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #fff;
            font-size: 0.92rem; font-weight: 700;
            border: none; border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(37,99,235,0.35);
            transition: all 0.2s;
            margin-top: 6px;
        }
        .cuti-submit-btn:hover { opacity: 0.92; transform: translateY(-1px); }

        /* Ringkasan Rows */
        .ringkasan-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 11px 14px;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 8px;
        }
        .ringkasan-row-left {
            display: flex; align-items: center; gap: 10px;
        }
        .ringkasan-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: #eff6ff;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6; font-size: 0.85rem;
            flex-shrink: 0;
        }
        .ringkasan-label {
            font-size: 0.82rem; color: #6b7280; font-weight: 500;
        }
        .ringkasan-value {
            font-size: 0.85rem; font-weight: 700; color: #111827;
        }
        .ringkasan-note {
            display: flex; align-items: flex-start; gap: 8px;
            padding: 10px 12px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            font-size: 0.75rem; color: #92400e;
            margin-top: 4px;
        }

        /* Riwayat Section */
        .riwayat-title {
            font-size: 1rem; font-weight: 800; color: #111827;
            margin-bottom: 14px;
        }
        .riwayat-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 14px;
            background: #fff;
            border: 1.5px solid #f1f5f9;
            border-radius: 12px;
            text-decoration: none;
            margin-bottom: 8px;
            transition: all 0.15s;
        }
        .riwayat-item:hover {
            border-color: #bfdbfe;
            background: #f0f7ff;
        }
        .riwayat-icon-box {
            width: 40px; height: 40px;
            border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
        }
        .riwayat-dates {
            font-size: 0.85rem; font-weight: 700; color: #1f2937;
            line-height: 1.3;
        }
        .riwayat-sub {
            font-size: 0.73rem; color: #9ca3af; margin-top: 2px;
        }
        .riwayat-badge {
            font-size: 0.7rem; font-weight: 700;
            padding: 4px 10px; border-radius: 999px;
            white-space: nowrap;
            margin-left: auto; flex-shrink: 0;
        }
        .riwayat-empty {
            text-align: center; padding: 32px 16px;
            border: 2px dashed #e5e7eb; border-radius: 14px;
        }
    </style>
    @endpush

    <div class="bg-gray-50 sm:bg-gradient-to-br sm:from-sky-50 sm:to-blue-100 min-h-screen">
        <div class="max-w-5xl mx-auto cuti-page-wrapper">

            {{-- TOMBOL KEMBALI --}}
            <a href="{{ route('dashboard') }}" class="cuti-back-btn">
                <i class="fas fa-arrow-left text-xs"></i>
                Kembali
            </a>

            {{-- GRID: di mobile jadi kolom tunggal, di desktop 5 kolom --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 lg:gap-6">

                {{-- 1. SISA CUTI CARD (mobile: order 1, desktop: kanan atas) --}}
                <div class="order-1 lg:order-2 lg:col-span-2">
                    <div class="cuti-sisa-card">
                        <div style="position:relative;z-index:1;">
                            <p class="cuti-sisa-label">Sisa Cuti Tahun Ini</p>
                            <p>
                                <span class="cuti-sisa-num">{{ $sisaCuti ?? 0 }}</span>
                                <span class="cuti-sisa-total"> / {{ $totalCuti ?? 0 }} Hari</span>
                            </p>
                        </div>
                        <div class="cuti-sisa-icon" style="position:relative;z-index:1;">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                </div>

                {{-- 2. FORM PENGAJUAN CUTI (mobile: order 2, desktop: kiri, span 3) --}}
                <div class="order-2 lg:order-1 lg:col-span-3 lg:row-span-2">
                    <div class="cuti-card">
                        <h3 class="cuti-card-title">Ajukan Cuti Baru</h3>

                        <form action="{{ route('cuti.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="jenis_cuti" value="tahunan">

                            {{-- Tanggal Mulai --}}
                            <div class="cuti-input-group">
                                <label for="tanggal_mulai" class="cuti-label">Tanggal Mulai</label>
                                <input type="date"
                                       id="tanggal_mulai"
                                       name="tanggal_mulai"
                                       min="{{ \Carbon\Carbon::now()->toDateString() }}"
                                       value="{{ old('tanggal_mulai') }}"
                                       class="cuti-input {{ $errors->has('tanggal_mulai') ? 'error' : '' }}">
                            </div>

                            {{-- Tanggal Selesai --}}
                            <div class="cuti-input-group">
                                <label for="tanggal_selesai" class="cuti-label">Tanggal Selesai</label>
                                <input type="date"
                                       id="tanggal_selesai"
                                       name="tanggal_selesai"
                                       min="{{ \Carbon\Carbon::now()->toDateString() }}"
                                       value="{{ old('tanggal_selesai') }}"
                                       class="cuti-input {{ $errors->has('tanggal_selesai') ? 'error' : '' }}">
                            </div>

                            {{-- Alasan --}}
                            <div class="cuti-input-group">
                                <label for="alasan" class="cuti-label">Alasan</label>
                                <textarea id="alasan"
                                          name="alasan"
                                          rows="4"
                                          class="cuti-input {{ $errors->has('alasan') ? 'error' : '' }}"
                                          placeholder="Jelaskan alasan Anda mengajukan cuti...">{{ old('alasan') }}</textarea>
                            </div>

                            {{-- Lampiran --}}
                            <div class="cuti-input-group">
                                <label class="cuti-label">Lampiran <span style="color:#9ca3af; font-weight:400;">(Opsional)</span></label>
                                <label for="lampiran" class="cuti-file-label">
                                    <i class="fas fa-paperclip text-blue-400"></i>
                                    <span id="file-name-label">Pilih file...</span>
                                    <input type="file"
                                           id="lampiran"
                                           name="lampiran"
                                           class="hidden"
                                           accept="image/*,application/pdf"
                                           onchange="document.getElementById('file-name-label').textContent = this.files[0]?.name || 'Pilih file...'">
                                </label>
                                <p class="mt-1.5" style="font-size:0.72rem; color:#9ca3af;">Format: JPG, PNG, PDF (Max. 2MB)</p>
                            </div>

                            {{-- Error --}}
                            @if ($errors->any())
                                <div class="mb-4 p-3 rounded-xl text-sm" style="background:#fee2e2; border:1px solid #fca5a5; color:#b91c1c;">
                                    <p class="font-bold mb-1">Gagal Mengajukan Cuti</p>
                                    <ul class="list-disc list-inside space-y-0.5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Submit --}}
                            <button type="submit" class="cuti-submit-btn">
                                <i class="fas fa-paper-plane"></i>
                                Kirim Pengajuan
                            </button>
                        </form>
                    </div>
                </div>

                {{-- 3. RINGKASAN (mobile: order 3, desktop: kanan bawah) --}}
                <div class="order-3 lg:order-3 lg:col-span-2">
                    <div class="cuti-card">
                        <h3 class="cuti-card-title">Ringkasan</h3>

                        <div class="ringkasan-row">
                            <div class="ringkasan-row-left">
                                <div class="ringkasan-icon"><i class="fas fa-calendar-day"></i></div>
                                <span class="ringkasan-label">Mulai Cuti</span>
                            </div>
                            <span id="ringkasan-mulai" class="ringkasan-value">-</span>
                        </div>

                        <div class="ringkasan-row">
                            <div class="ringkasan-row-left">
                                <div class="ringkasan-icon"><i class="fas fa-calendar-check"></i></div>
                                <span class="ringkasan-label">Selesai Cuti</span>
                            </div>
                            <span id="ringkasan-selesai" class="ringkasan-value">-</span>
                        </div>

                        <div class="ringkasan-row">
                            <div class="ringkasan-row-left">
                                <div class="ringkasan-icon"><i class="fas fa-hourglass-half"></i></div>
                                <span class="ringkasan-label">Total Durasi</span>
                            </div>
                            <span id="total-hari" class="ringkasan-value">- Hari</span>
                        </div>

                        <div class="ringkasan-note">
                            <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
                            <span>Sabtu, Minggu, &amp; Tanggal Merah tidak dihitung.</span>
                        </div>
                    </div>
                </div>

                {{-- 4. RIWAYAT PENGAJUAN (full width) --}}
                <div class="order-4 lg:col-span-5">
                    <div class="cuti-card">
                        <h3 class="riwayat-title">Riwayat Pengajuan</h3>

                        @forelse ($cutiRequests as $cuti)
                            @php
                                $status = $cuti->status;
                                $iconClass = match($status) {
                                    'disetujui'  => 'fas fa-check',
                                    'ditolak'    => 'fas fa-times',
                                    'dibatalkan' => 'fas fa-ban',
                                    default      => 'fas fa-clock',
                                };
                                $iconStyle = match($status) {
                                    'disetujui'  => 'background:#dcfce7; color:#16a34a;',
                                    'ditolak'    => 'background:#fee2e2; color:#dc2626;',
                                    'dibatalkan' => 'background:#f3f4f6; color:#6b7280;',
                                    default      => 'background:#fef9c3; color:#ca8a04;',
                                };
                                $badgeStyle = match($status) {
                                    'disetujui'  => 'background:#dcfce7; color:#15803d;',
                                    'ditolak'    => 'background:#fee2e2; color:#dc2626;',
                                    'dibatalkan' => 'background:#f3f4f6; color:#374151;',
                                    default      => 'background:#fef9c3; color:#92400e;',
                                };
                            @endphp
                            <a href="{{ route('cuti.show', $cuti) }}" class="riwayat-item">
                                <div class="riwayat-icon-box" style="{{ $iconStyle }}">
                                    <i class="{{ $iconClass }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="riwayat-dates">
                                        {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }}
                                        &ndash;
                                        {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                                    </p>
                                    <p class="riwayat-sub">Diajukan: {{ \Carbon\Carbon::parse($cuti->created_at)->format('d M Y') }}</p>
                                </div>
                                <span class="riwayat-badge" style="{{ $badgeStyle }}">{{ ucfirst($status) }}</span>
                            </a>
                        @empty
                            <div class="riwayat-empty">
                                <i class="fas fa-box-open text-3xl text-gray-300 mb-3 block"></i>
                                <p class="text-gray-500 font-medium text-sm">Belum ada riwayat pengajuan cuti.</p>
                            </div>
                        @endforelse
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

        const liburNasional = @json($liburNasional ?? []);

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
                    totalHariElem.textContent = 'Tanggal Invalid';
                    totalHariElem.style.color = '#dc2626';
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
                    totalHariElem.style.color = '#dc2626';
                } else {
                    totalHariElem.textContent = `${countDays} Hari Kerja`;
                    totalHariElem.style.color = '#111827';
                }
            } else {
                totalHariElem.textContent = '- Hari';
                totalHariElem.style.color = '#111827';
            }
        }

        tglMulai.addEventListener('change', hitungDurasi);
        tglSelesai.addEventListener('change', hitungDurasi);
        hitungDurasi();
    });
    </script>
    @endpush
</x-layout-users>