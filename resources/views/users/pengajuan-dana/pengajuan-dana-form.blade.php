@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }

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

        /* == Glass Cards == */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            padding: 28px;
        }

        /* Inputs & Selection */
        .modern-label {
            display: block; font-size: 0.8rem; font-weight: 800; color: #475569; margin-bottom: 6px;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .modern-input {
            width: 100%; background: rgba(255, 255, 255, 0.95);
            border: 2px solid #e2e8f0; border-radius: 14px;
            padding: 11px 15px; font-size: 0.9rem; color: #1e293b; font-weight: 600;
            outline: none; transition: all 0.2s ease;
        }
        .modern-input:focus {
            border-color: #3b82f6; background: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        .modern-input-readonly {
            width: 100%; background: rgba(241, 245, 249, 0.6);
            border: 2px solid #e2e8f0; border-radius: 14px;
            padding: 11px 15px; font-size: 0.9rem; color: #64748b; font-weight: 700;
            outline: none; cursor: not-allowed;
        }

        /* Form Subheaders */
        .form-section-header {
            display: flex; align-items: center; gap: 12px;
            padding-bottom: 16px; border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            margin-bottom: 24px;
        }

        /* Buttons styles */
        .btn-action-primary {
            padding: 12px 28px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff; font-size: 0.9rem; font-weight: 800;
            border: none; border-radius: 14px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(37,99,235,0.25);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-action-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37,99,235,0.35);
        }
        .btn-action-primary:disabled {
            background: #cbd5e1; color: #64748b;
            box-shadow: none; cursor: not-allowed;
        }

        .btn-action-secondary {
            padding: 12px 28px;
            background: #ffffff;
            color: #475569; font-size: 0.9rem; font-weight: 800;
            border: 2px solid #e2e8f0; border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-action-secondary:hover {
            border-color: #cbd5e1; background: #f8fafc;
        }

        /* Custom Dynamic Row Grid Layouts */
        .rincian-row-desktop {
            display: grid !important;
            grid-template-columns: 8fr 3fr 1fr !important;
            gap: 16px !important;
            padding: 16px !important;
            align-items: center !important;
            background: #ffffff !important;
        }
        .rincian-row-mobile {
            display: grid !important;
            grid-template-columns: 7fr 4fr 1fr !important;
            gap: 8px !important;
            padding: 10px !important;
            align-items: center !important;
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 12px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
            margin-bottom: 8px !important;
        }
        .input-wrapper-rp {
            position: relative !important;
            display: block !important;
            width: 100% !important;
        }
        .label-rp {
            position: absolute !important;
            left: 12px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            color: #94a3b8 !important;
            pointer-events: none !important;
            z-index: 10 !important;
        }
        .input-with-rp {
            padding-left: 32px !important;
        }

        @media (max-width: 767.98px) {
            .glass-card {
                padding: 18px;
                border-radius: 18px;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                background: rgba(255, 255, 255, 0.95) !important;
            }
            .modern-input, .modern-input-readonly {
                padding: 9px 12px;
                font-size: 0.85rem;
                border-radius: 10px;
            }
            .modern-label {
                font-size: 0.72rem;
                margin-bottom: 4px;
            }
            .btn-action-primary, .btn-action-secondary {
                padding: 10px 20px;
                font-size: 0.85rem;
                border-radius: 10px;
                width: 100%;
                justify-content: center;
            }
            .form-section-header {
                padding-bottom: 12px;
                margin-bottom: 20px;
            }
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col">

            {{-- NOTIFIKASI SUCCESS --}}
            

            {{-- NOTIFIKASI ERROR --}}
            

            {{-- NOTIFIKASI VALIDATION --}}
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm" role="alert">
                    <p class="font-bold mb-1 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs font-semibold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif

            {{-- TOMBOL KEMBALI MODERN --}}
            <a href="{{ route('dashboard') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Dashboard
            </a>

            {{-- CARD ATAS PENJELAS HALAMAN --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-2xl md:rounded-3xl p-4 md:p-8 shadow-xl mb-4 md:mb-6 overflow-hidden border border-white/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute right-20 -bottom-10 w-24 h-24 bg-white/10 rounded-full blur-lg pointer-events-none"></div>
                
                <div class="relative z-10 flex items-center gap-3 md:gap-5">
                    <div class="h-10 w-10 md:h-14 md:w-14 rounded-xl md:rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                        <i class="fas fa-hand-holding-usd text-lg md:text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-base md:text-2xl font-black tracking-tight text-white uppercase">Form Pengajuan Dana</h1>
                        @if(!$isMobile)
                        <p class="text-blue-100 text-xs md:text-sm mt-1 font-medium leading-relaxed max-w-xl">
                            Ajukan permohonan dana operasional atau pengeluaran kantor, tambahkan rincian item, lampirkan dokumen bukti, dan pantau status persetujuan.
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- STACKED VERTICAL LAYOUT --}}
            <div class="space-y-4 md:space-y-6 pb-10">

                <form action="{{ route('pengajuan_dana.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 md:space-y-6 m-0">
                    @csrf
                    
                    {{-- 1. INFORMASI PEMOHON (CARD SENDIRI) --}}
                    <div class="glass-card">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/60 pb-3 md:pb-4 mb-4 md:mb-7">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-user-tie"></i></div>
                                <div>
                                    <h3 class="text-lg font-black text-slate-800">1. Informasi Pemohon</h3>
                                    @if(!$isMobile)
                                    <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Detail data karyawan yang mengajukan dana</p>
                                    @endif
                                </div>
                            </div>
                            <div class="w-full flex justify-end sm:w-auto">
                                <a href="{{ route('pengajuan_dana.history') }}" class="text-xs text-blue-600 font-bold hover:underline flex items-center gap-1.5 bg-blue-50 px-4 py-2 rounded-full border border-blue-100 transition-all hover:bg-blue-100 shadow-sm w-fit">
                                    <i class="fas fa-history"></i>
                                    Lihat Riwayat Pengajuan
                                </a>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 md:gap-5">
                            <div>
                                <label class="modern-label">Nama Pemohon</label>
                                <input type="text" class="modern-input-readonly" value="{{ Auth::user()->name }}" readonly>
                                <input type="hidden" name="nama_pemohon" value="{{ Auth::user()->name }}">
                            </div>
                            <div>
                                <label class="modern-label">Divisi</label>
                                <input type="text" class="modern-input-readonly" value="{{ Auth::user()->divisi }}" readonly>
                                <input type="hidden" name="divisi" value="{{ Auth::user()->divisi }}">
                            </div>
                            <div>
                                <label class="modern-label">Jabatan Pemohon</label>
                                <input type="text" class="modern-input-readonly" value="{{ Auth::user()->jabatan ?? '-' }}" readonly>
                            </div>
                            <div>
                                <label class="modern-label">Email Pemohon</label>
                                <input type="text" class="modern-input-readonly" value="{{ Auth::user()->email }}" readonly>
                            </div>
                            <div class="col-span-2">
                                <label class="modern-label" for="judul-pengajuan">Judul Pengajuan <span class="text-red-500">*</span></label>
                                <input type="text" id="judul-pengajuan" name="judul_pengajuan" class="modern-input" placeholder="Contoh: Pembelian Perlengkapan Kantor & ATK" value="{{ old('judul_pengajuan') }}" required>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="modern-label">Tanggal Pengajuan</label>
                                <input type="text" class="modern-input-readonly" value="{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}" readonly>
                                <input type="hidden" name="tanggal_pengajuan" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    {{-- 2. INFORMASI REKENING TRANSFER (CARD SENDIRI) --}}
                    <div class="glass-card">
                        <div class="form-section-header">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-university"></i></div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800">2. Tujuan Transfer Rekening</h4>
                                @if(!$isMobile)
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Informasi rekening untuk proses pencairan dana</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 md:gap-5">
                            <div class="col-span-1">
                                <label class="modern-label" for="pilih-bank">Nama Bank <span class="text-red-500">*</span></label>
                                <select id="pilih-bank" name="nama_bank" class="modern-input" required>
                                    <option value="" disabled {{ !old('nama_bank') ? 'selected' : '' }}>Pilih Bank</option>
                                    <option value="BCA" {{ old('nama_bank') == 'BCA' ? 'selected' : '' }}>BCA</option>
                                    <option value="BRI" {{ old('nama_bank') == 'BRI' ? 'selected' : '' }}>BRI</option>
                                    <option value="BNI" {{ old('nama_bank') == 'BNI' ? 'selected' : '' }}>BNI</option>
                                    <option value="Mandiri" {{ old('nama_bank') == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                                    <option value="CIMB Niaga" {{ old('nama_bank') == 'CIMB Niaga' ? 'selected' : '' }}>CIMB Niaga</option>
                                    <option value="BSI" {{ old('nama_bank') == 'BSI' ? 'selected' : '' }}>BSI</option>
                                    <option value="OCBC NISP" {{ old('nama_bank') == 'OCBC NISP' ? 'selected' : '' }}>OCBC NISP</option>
                                    <option value="Permata" {{ old('nama_bank') == 'Permata' ? 'selected' : '' }}>Permata</option>
                                    <option value="Jago" {{ old('nama_bank') == 'Jago' ? 'selected' : '' }}>Jago</option>
                                    <option value="Seabank" {{ old('nama_bank') == 'Seabank' ? 'selected' : '' }}>Seabank</option>
                                    <option value="other" {{ old('nama_bank') == 'other' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                <div id="bank-lainnya-container" class="{{ old('nama_bank') == 'other' ? '' : 'hidden' }} mt-2">
                                    <input type="text" id="input-bank-lainnya" name="nama_bank_lainnya" class="modern-input" placeholder="Nama Bank Lainnya" value="{{ old('nama_bank_lainnya') }}" {{ old('nama_bank') == 'other' ? 'required' : '' }}>
                                </div>
                            </div>
                            <div class="col-span-1">
                                <label class="modern-label" for="no-rekening">Nomor Rekening <span class="text-red-500">*</span></label>
                                <input type="number" id="no-rekening" name="no_rekening" class="modern-input" placeholder="Contoh: 1234567890" value="{{ old('no_rekening') }}" required>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="modern-label" for="nama-rek">Atas Nama (A/N) <span class="text-red-500">*</span></label>
                                <input type="text" id="nama-rek" name="nama_rek" class="modern-input" placeholder="Nama Pemilik Rekening" value="{{ old('nama_rek') }}" required>
                            </div>
                        </div>
                    </div>

                    {{-- 3. RINCIAN DANA (CARD SENDIRI) --}}
                    <div class="glass-card">
                        <div class="form-section-header">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-list-ol"></i></div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800">3. Rincian Kebutuhan Dana</h4>
                                @if(!$isMobile)
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Tambahkan deskripsi barang/jasa beserta nominal estimasi</p>
                                @endif
                            </div>
                        </div>
                        
                        @if($isMobile)
                            <div id="rincian-dana-body" class="space-y-4 mb-4">
                                {{-- Rows appended here dynamically --}}
                            </div>
                        @else
                            <div class="rounded-2xl border border-slate-200 overflow-hidden mb-3">
                                {{-- Header: Hidden on mobile, visible as grid on desktop --}}
                                <div class="hidden md:grid grid-cols-12 bg-slate-50 text-slate-600 uppercase font-black text-xs border-b border-slate-200 p-4 gap-4">
                                    <div class="col-span-8">Deskripsi Item</div>
                                    <div class="col-span-3">Jumlah Estimasi (Rp)</div>
                                    <div class="col-span-1 text-center">Aksi</div>
                                </div>
                                {{-- Container for rows --}}
                                <div id="rincian-dana-body" class="bg-white divide-y divide-slate-100">
                                    {{-- Rows appended here dynamically --}}
                                </div>
                            </div>
                        @endif
                        
                        <button id="tambah-baris-btn" type="button" class="bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 font-black py-2 px-4 rounded-xl text-xs flex items-center gap-1.5 transition">
                            <i class="fas fa-plus"></i> Tambah Item Kebutuhan
                        </button>

                        <div class="mt-5 pt-4 border-t border-slate-200/80 flex items-center justify-between bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                            <span class="text-xs text-blue-700 font-extrabold uppercase tracking-wider">Total Estimasi Keseluruhan:</span>
                            <div class="text-right">
                                <span class="text-blue-700 font-black text-2xl font-mono">Rp <span id="total-dana-display">0</span></span>
                                <input type="hidden" id="jumlah-dana-total" name="jumlah_dana_total">
                            </div>
                        </div>
                    </div>

                    {{-- 4. FILE PENDUKUNG (CARD SENDIRI) --}}
                    <div class="glass-card">
                        <div class="form-section-header">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-paperclip"></i></div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800">4. File Pendukung (Opsional)</h4>
                                @if(!$isMobile)
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Lampirkan file nota, invoice, proposal, dll. jika ada</p>
                                @endif
                            </div>
                        </div>

                        {{-- Info Banner Format Dokumen --}}
                        <div class="bg-blue-50/50 border border-blue-100 p-3 md:p-3.5 rounded-2xl mb-3 md:mb-4 flex gap-3">
                            <i class="fas fa-info-circle text-blue-600 text-sm mt-0.5"></i>
                            <div class="text-[11px] text-blue-800 leading-normal font-semibold">
                                Format file yang didukung: <span class="text-blue-900 font-black">JPG, JPEG, PNG, PDF, DOC, DOCX, XLS, XLSX</span>. Ukuran berkas maksimal <span class="text-blue-900 font-black">5MB</span> per file.
                            </div>
                        </div>

                        <div id="file-pendukung-container" class="space-y-3"></div>
                        <button id="tambah-lampiran-btn" type="button" class="mt-3 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 font-black py-2 px-4 rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm">
                            <i class="fas fa-plus"></i> Tambah File Lampiran
                        </button>
                    </div>

                    {{-- SUBMIT BUTTONS --}}
                    @if($isMobile)
                        <div class="flex flex-col-reverse gap-3 pt-2">
                            <button type="button" id="reset-form-btn" class="btn-action-secondary">Reset Formulir</button>
                            <button type="submit" id="submit-button" class="btn-action-primary">
                                <i class="fas fa-paper-plane"></i> Ajukan Dana Sekarang
                            </button>
                        </div>
                    @else
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" id="reset-form-btn" class="btn-action-secondary">Reset Formulir</button>
                            <button type="submit" id="submit-button" class="btn-action-primary">
                                <i class="fas fa-paper-plane"></i> Ajukan Dana Sekarang
                            </button>
                        </div>
                    @endif
                </form>


            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('pilih-bank')?.addEventListener('change', function() {
            const bankContainer = document.getElementById('bank-lainnya-container'); 
            const otherBankInput = document.getElementById('input-bank-lainnya'); 
            if (this.value === 'other') {
                bankContainer.classList.remove('hidden'); 
                otherBankInput.setAttribute('required', 'required');
            } else {
                bankContainer.classList.add('hidden'); 
                otherBankInput.removeAttribute('required');
            }
        });
        
        const tambahBarisBtn = document.getElementById('tambah-baris-btn'); 
        const rincianDanaBody = document.getElementById('rincian-dana-body'); 
        const totalDanaDisplay = document.getElementById('total-dana-display'); 
        const jumlahDanaTotalInput = document.getElementById('jumlah-dana-total');
        
        function updateTotal() {
            let total = 0; 
            document.querySelectorAll('input[name="rincian_jumlah[]"]').forEach(input => {
                total += parseInt(input.value.replace(/[^0-9]/g, '')) || 0;
            }); 
            const formattedTotal = total.toLocaleString('id-ID'); 
            if (totalDanaDisplay) totalDanaDisplay.textContent = formattedTotal; 
            if (jumlahDanaTotalInput) jumlahDanaTotalInput.value = total;
        }
        
        function formatCurrency(input) {
            let value = input.value.replace(/[^0-9]/g, ''); 
            input.value = value ? parseInt(value).toLocaleString('id-ID') : '';
        }
        
        const isMobile = @json($isMobile);

        function addRow(deskripsi = '', jumlah = '') {
            const newRow = document.createElement('div');
            
            let formattedJumlah = '';
            if (jumlah !== '') {
                let cleanVal = jumlah.toString().replace(/[^0-9]/g, '');
                formattedJumlah = cleanVal ? parseInt(cleanVal).toLocaleString('id-ID') : '';
            }
            
            if (isMobile) {
                newRow.className = 'rincian-row-mobile';
                newRow.innerHTML = `
                    <div>
                        <input type="text" name="rincian_deskripsi[]" value="${deskripsi}" class="modern-input !py-1.5 !px-2.5 !rounded-lg !text-xs" placeholder="Deskripsi item" required>
                    </div>
                    <div>
                        <div class="input-wrapper-rp">
                            <span class="label-rp">Rp</span>
                            <input type="text" name="rincian_jumlah[]" value="${formattedJumlah}" class="modern-input !py-1.5 !rounded-lg !text-xs jumlah-input input-with-rp" placeholder="Jumlah" required>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <button type="button" class="delete-row-btn text-red-500 hover:text-red-700 p-1.5 text-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;
            } else {
                newRow.className = 'rincian-row-desktop';
                newRow.innerHTML = `
                    <div>
                        <input type="text" name="rincian_deskripsi[]" value="${deskripsi}" class="modern-input !py-2 !px-3.5 !rounded-xl !text-xs" placeholder="Masukkan deskripsi" required>
                    </div>
                    <div>
                        <div class="input-wrapper-rp">
                            <span class="label-rp">Rp</span>
                            <input type="text" name="rincian_jumlah[]" value="${formattedJumlah}" class="modern-input !py-2 !rounded-xl !text-xs jumlah-input input-with-rp" placeholder="0" required>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <button type="button" class="delete-row-btn text-slate-400 hover:text-red-600 hover:bg-red-50 p-2.5 rounded-xl text-sm transition-all">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;
            }
            
            rincianDanaBody.appendChild(newRow); 
            const amountInput = newRow.querySelector('.jumlah-input'); 
            amountInput.addEventListener('input', () => { formatCurrency(amountInput); updateTotal(); }); 
            newRow.querySelector('.delete-row-btn').addEventListener('click', () => { newRow.remove(); updateTotal(); });
        }
        
        const oldRincianDeskripsi = @json(old('rincian_deskripsi', []));
        const oldRincianJumlah = @json(old('rincian_jumlah', []));

        if (tambahBarisBtn) { 
            if (oldRincianDeskripsi && oldRincianDeskripsi.length > 0) {
                oldRincianDeskripsi.forEach((deskripsi, index) => {
                    const jumlah = oldRincianJumlah[index] !== undefined ? oldRincianJumlah[index] : '';
                    addRow(deskripsi, jumlah);
                });
                updateTotal();
            } else {
                addRow(); 
            }
            tambahBarisBtn.addEventListener('click', () => addRow()); 
        }

        const tambahLampiranBtn = document.getElementById('tambah-lampiran-btn');
        const lampiranContainer = document.getElementById('file-pendukung-container');
        const mainForm = document.querySelector('form[action="{{ route('pengajuan_dana.store') }}"]');
        const submitButton = document.getElementById('submit-button');
 
        function addLampiranInput() {
            const uniqueId = 'file_' + Date.now() + Math.random().toString(36).substr(2, 9);
            const newFileWrapper = document.createElement('div');
            newFileWrapper.className = 'bg-white p-3 rounded-2xl border border-slate-200 hover:border-blue-400 transition-all shadow-sm flex flex-col gap-2';
            
            const fileInputHtml = `
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 text-sm">
                        <i class="fas fa-paperclip"></i>
                    </div>
                    <div class="flex-grow min-w-0">
                        <input type="file" name="file_pendukung[]" id="${uniqueId}" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer cursor-pointer font-semibold" />
                    </div>
                    <button type="button" class="delete-lampiran-btn flex-shrink-0 text-slate-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-xl text-base transition-all">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </button>
                </div>
                <div id="progress-wrapper-${uniqueId}" class="mt-1 hidden pl-11">
                    <div class="flex justify-between items-center mb-1">
                        <span id="file-name-${uniqueId}" class="text-[10px] font-bold text-slate-700 truncate pr-2 w-4/5"></span>
                        <span id="status-text-${uniqueId}" class="text-[10px] font-bold text-blue-700 w-1/5 text-right"></span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-1">
                        <div id="progress-bar-${uniqueId}" class="h-1 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>`;
            newFileWrapper.innerHTML = fileInputHtml;
            lampiranContainer.appendChild(newFileWrapper);
            
            const fileInput = newFileWrapper.querySelector(`#${uniqueId}`);
            const progressWrapper = newFileWrapper.querySelector(`#progress-wrapper-${uniqueId}`);
            const progressBar = newFileWrapper.querySelector(`#progress-bar-${uniqueId}`);
            const fileNameSpan = newFileWrapper.querySelector(`#file-name-${uniqueId}`);
            const statusTextSpan = newFileWrapper.querySelector(`#status-text-${uniqueId}`);
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileNameSpan.textContent = file.name;
                    statusTextSpan.textContent = 'Ready';
                    statusTextSpan.classList.remove('text-blue-700');
                    statusTextSpan.classList.add('text-green-700');
                    progressBar.style.width = '100%';
                    progressBar.classList.remove('bg-blue-600');
                    progressBar.classList.add('bg-green-500');
                    progressWrapper.classList.remove('hidden');
                } else {
                    progressWrapper.classList.add('hidden');
                }
            });
            
            newFileWrapper.querySelector('.delete-lampiran-btn').addEventListener('click', function() {
                newFileWrapper.remove();
            });
        }
        
        if (tambahLampiranBtn) {
            tambahLampiranBtn.addEventListener('click', addLampiranInput);
            addLampiranInput();
        }
        
        mainForm.addEventListener('submit', function(e) {
            let tooLarge = false;
            document.querySelectorAll('input[type="file"][name="file_pendukung[]"]').forEach(input => {
                if (input.files && input.files.length > 0) {
                    const file = input.files[0];
                    const maxBytes = 5 * 1024 * 1024; // 5MB
                    if (file.size > maxBytes) {
                        tooLarge = true;
                    }
                }
            });

            if (tooLarge) {
                e.preventDefault();
                alert('Ukuran salah satu file lampiran melebihi batas maksimal 5MB. Silakan pilih berkas yang lebih kecil agar formulir tidak perlu diisi ulang.');
                return false;
            }

            document.querySelectorAll('input[type="file"][name="file_pendukung[]"]').forEach(input => {
                if (input.files && input.files.length > 0) {
                    const uniqueId = input.id;
                    const statusText = document.getElementById(`status-text-${uniqueId}`);
                    const progressBar = document.getElementById(`progress-bar-${uniqueId}`);
                    if (statusText) {
                        statusText.textContent = 'Uploading...';
                        statusText.classList.remove('text-green-700');
                        statusText.classList.add('text-blue-700');
                    }
                    if (progressBar) {
                        progressBar.classList.remove('bg-green-500');
                        progressBar.classList.add('bg-blue-600');
                    }
                }
            });
            
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mengirim...`;
            submitButton.classList.add('inline-flex', 'items-center');
        });
        
        const resetBtn = document.getElementById('reset-form-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                mainForm.reset();
                if (rincianDanaBody) rincianDanaBody.innerHTML = '';
                addRow();
                if (lampiranContainer) lampiranContainer.innerHTML = '';
                addLampiranInput();
                updateTotal();
                document.getElementById('pilih-bank').dispatchEvent(new Event('change'));
            });
        }
    });
    </script>
    @endpush
</x-layout-users>