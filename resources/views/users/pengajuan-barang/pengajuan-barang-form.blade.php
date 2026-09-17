@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();

    $user = Auth::user();
    $isApprover = \App\Models\User::where('approver_barang_1_id', $user->id)
        ->orWhere('approver_barang_2_id', $user->id)
        ->orWhere('approver_barang_3_id', $user->id)
        ->orWhere('approver_barang_4_id', $user->id)
        ->exists();
    $canMonitor = $isApprover || $user->role === 'admin';
@endphp
<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        html { scroll-behavior: smooth; }
        /* == Background == */
        .mesh-bg { background-color: #ede9fe; }
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9); border-radius: 9999px;
            color: #1e293b; font-size: 0.9rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px; width: fit-content;
        }
        .btn-back-modern:hover { background: rgba(255,255,255,0.95); box-shadow: 0 10px 15px -3px rgba(59,130,246,0.15); transform: translateY(-2px); color: #1d4ed8; }
        .btn-back-modern .icon-circle { width: 32px; height: 32px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #3b82f6; font-size: 0.85rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: transform 0.3s ease; }
        .btn-back-modern:hover .icon-circle { transform: translateX(-3px); background: #EFF6FF; }
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,1); border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); padding: 28px; }
        .modern-label { display: block; font-size: 0.8rem; font-weight: 800; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        .modern-input { width: 100%; background: rgba(255,255,255,0.95); border: 2px solid #e2e8f0; border-radius: 14px; padding: 11px 15px; font-size: 0.9rem; color: #1e293b; font-weight: 600; outline: none; transition: all 0.2s ease; }
        .modern-input:focus { border-color: #3b82f6; background: #fff; box-shadow: 0 0 0 4px rgba(59,130,246,0.15); }
        .modern-input-readonly { width: 100%; background: rgba(241,245,249,0.6); border: 2px solid #e2e8f0; border-radius: 14px; padding: 11px 15px; font-size: 0.9rem; color: #64748b; font-weight: 700; outline: none; cursor: not-allowed; }
        .modern-select { width: 100%; background: rgba(255,255,255,0.95); border: 2px solid #e2e8f0; border-radius: 14px; padding: 11px 15px; font-size: 0.9rem; color: #1e293b; font-weight: 600; outline: none; transition: all 0.2s ease; cursor: pointer; }
        .modern-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59,130,246,0.15); }
        
        .form-section-header { display: flex; align-items: center; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid rgba(226,232,240,0.8); margin-bottom: 24px; }
        
        .btn-action-primary { padding: 12px 28px; background: linear-gradient(135deg, #1d4ed8, #3b82f6); color: #fff; font-size: 0.9rem; font-weight: 800; border: none; border-radius: 14px; cursor: pointer; box-shadow: 0 6px 20px rgba(37,99,235,0.25); transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-action-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37,99,235,0.35); }
        .btn-action-primary:disabled { background: #cbd5e1; color: #64748b; box-shadow: none; cursor: not-allowed; }
        .btn-action-secondary { padding: 12px 28px; background: #ffffff; color: #475569; font-size: 0.9rem; font-weight: 800; border: 2px solid #e2e8f0; border-radius: 14px; cursor: pointer; transition: all 0.2s; }
        .btn-action-secondary:hover { border-color: #cbd5e1; background: #f8fafc; }

        /* Custom Dynamic Row Grid Layouts */
        .rincian-row-desktop {
            display: grid !important;
            grid-template-columns: 3fr 3fr 2fr 3fr 40px !important;
            gap: 12px !important;
            padding: 12px 16px !important;
            align-items: start !important;
            background: #ffffff !important;
        }
        .rincian-row-mobile {
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
            padding: 12px !important;
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 14px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
            margin-bottom: 8px !important;
        }
        .rincian-row-mobile-second-row {
            display: grid !important;
            grid-template-columns: 1fr 1fr 40px !important;
            gap: 8px !important;
            align-items: center !important;
        }
        /* Hint riwayat: selalu reservasi 1 baris agar tidak menggeser layout */
        .riwayat-hint {
            min-height: 16px !important;
            line-height: 16px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            display: block !important;
        }

        @media (max-width: 767.98px) {
            .glass-card {
                padding: 18px;
                border-radius: 18px;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                background: rgba(255, 255, 255, 0.95) !important;
            }
            .modern-input, .modern-input-readonly, .modern-select {
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

            
            
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm" role="alert">
                    <p class="font-bold mb-1 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs font-semibold">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('dashboard') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Dashboard
            </a>

            {{-- HEADER --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-2xl md:rounded-3xl p-5 md:p-6 shadow-xl mb-4 md:mb-6 overflow-hidden border border-white/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute right-20 -bottom-10 w-24 h-24 bg-white/10 rounded-full blur-lg pointer-events-none"></div>
                <div class="relative z-10 flex items-center gap-3 md:gap-4">
                    <div class="h-10 w-10 md:h-12 md:w-12 rounded-xl md:rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                        <i class="fas fa-box text-lg md:text-xl text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-base md:text-xl font-black tracking-tight text-white uppercase">Form Permintaan Barang</h1>
                        @if(!$isMobile)
                        <p class="text-blue-100 text-xs md:text-sm mt-0.5 font-medium leading-relaxed w-full">
                            Ajukan permohonan barang atau perlengkapan operasional, tambahkan rincian item, lampirkan dokumen pendukung, dan pantau status persetujuan.
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-4 md:space-y-6 pb-10">
                @if($isMobile)
                <div class="flex flex-col gap-2 mb-4">
                    @if($canMonitor)
                    <a href="{{ route('pengajuan_barang.monitoring_all') }}" class="text-sm text-emerald-700 font-bold hover:underline flex items-center justify-center gap-2 bg-emerald-50 px-5 py-2.5 rounded-full border border-emerald-200 transition-all hover:bg-emerald-100 shadow-sm w-full">
                        <i class="fas fa-desktop"></i> Monitoring Seluruh Pengajuan
                    </a>
                    @endif
                    <a href="{{ route('pengajuan_barang.history') }}" class="text-sm text-blue-600 font-bold hover:underline flex items-center justify-center gap-2 bg-blue-50 px-5 py-2.5 rounded-full border border-blue-100 transition-all hover:bg-blue-100 shadow-sm w-full">
                        <i class="fas fa-history"></i> Lihat Riwayat Pengajuan
                    </a>
                </div>
                @endif

                <form id="pengajuan-barang-form" action="{{ route('pengajuan_barang.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 md:space-y-6 m-0">
                    @csrf

                    {{-- 1. INFORMASI PEMOHON --}}
                    <div class="glass-card">
                        {{-- Perhatikan class mb-3 md:mb-4 di ujung baris bawah ini untuk mengurangi jarak --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/60 pb-3 md:pb-4 mb-3 md:mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-user-tie"></i></div>
                                <div>
                                    <h3 class="text-lg font-black text-slate-800">1. Informasi Pemohon</h3>
                                    @if(!$isMobile)
                                    <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Detail data karyawan yang mengajukan barang</p>
                                    @endif
                                </div>
                            </div>
                            @if(!$isMobile)
                            <div class="w-full flex justify-end gap-2 sm:w-auto">
                                @if($canMonitor)
                                <a href="{{ route('pengajuan_barang.monitoring_all') }}" class="text-xs text-emerald-700 font-bold hover:underline flex items-center gap-1.5 bg-emerald-50 px-4 py-2 rounded-full border border-emerald-200 transition-all hover:bg-emerald-100 shadow-sm w-fit">
                                    <i class="fas fa-desktop"></i> Monitoring Seluruh Pengajuan
                                </a>
                                @endif
                                <a href="{{ route('pengajuan_barang.history') }}" class="text-xs text-blue-600 font-bold hover:underline flex items-center gap-1.5 bg-blue-50 px-4 py-2 rounded-full border border-blue-100 transition-all hover:bg-blue-100 shadow-sm w-fit">
                                    <i class="fas fa-history"></i> Lihat Riwayat Pengajuan
                                </a>
                            </div>
                            @endif
                        </div>
                        
                        <input type="hidden" name="divisi" value="{{ Auth::user()->divisi ?? 'Umum' }}">
                        <div class="grid grid-cols-2 gap-3 md:gap-5">
                            <div class="col-span-2">
                                <label class="modern-label" for="judul-pengajuan">Judul Pengajuan <span class="text-red-500">*</span></label>
                                <input type="text" id="judul-pengajuan" name="judul_pengajuan" class="modern-input" placeholder="Pengadaan Alat dan Barang ..." value="{{ old('judul_pengajuan') }}" required>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="modern-label">Tanggal Pengajuan</label>
                                <input type="text" class="modern-input-readonly" value="{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}" readonly>
                                <input type="hidden" name="tanggal_pengajuan" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    {{-- 2. RINCIAN BARANG --}}
<div class="glass-card">
    {{-- Perhatikan margin-bottom di ujung baris bawah --}}
    <div class="flex items-center gap-3 border-b border-slate-200/60 pb-3 md:pb-4 mb-3 md:mb-4">
        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-list-ol"></i></div>
        <div>
            <h4 class="text-lg font-black text-slate-800">2. Rincian Barang yang Diajukan</h4>
                                @if(!$isMobile)
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Tambahkan satu atau lebih item barang beserta supplier, satuan, jumlah, dan keterangan</p>
                                @endif
                            </div>
                        </div>
                        @if($isMobile)
                            <div id="rincian-barang-body" class="space-y-2 mb-3">
                                {{-- Rows appended here dynamically --}}
                            </div>
                        @else
                            <div class="rounded-2xl border border-slate-200 overflow-hidden mb-5">
                                {{-- Header: Visible as grid on desktop --}}
                                <div class="hidden md:grid bg-slate-50 text-slate-600 uppercase font-black text-xs border-b border-slate-200 text-center" style="grid-template-columns: 3fr 3fr 2fr 3fr 40px; gap: 12px; padding: 16px;">
                                    <div>Nama Barang</div>
                                    <div>Supplier</div>
                                    <div>Jumlah & Satuan</div>
                                    <div>Keterangan</div>
                                    <div>Aksi</div>
                                </div>
                                {{-- Container for rows --}}
                                <div id="rincian-barang-body" class="bg-white divide-y divide-slate-100">
                                    {{-- Rows appended here dynamically --}}
                                </div>
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <button id="tambah-baris-btn" type="button" class="bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 font-black py-2 px-4 rounded-xl text-xs flex items-center gap-1.5 transition">
                                <i class="fas fa-plus"></i> Tambah Item Barang
                            </button>
                            <span id="draft-status" class="text-[11px] font-bold text-slate-400">Draft tersimpan otomatis di perangkat ini</span>
                        </div>

                        {{-- Form Note / Catatan di bawah poin 2 --}}
                        <div class="mt-5 pt-4 border-t border-slate-200/80">
                            <label for="catatan_pemohon" class="modern-label flex items-center gap-1.5 text-slate-700 font-bold mb-1.5">
                                <i class="fas fa-sticky-note text-blue-600"></i> Catatan / Note Pengajuan <span class="text-slate-400 font-normal text-xs">(Opsional)</span>
                            </label>
                            <textarea id="catatan_pemohon" name="catatan_pemohon" rows="3" class="modern-input w-full" placeholder="Contoh: Pemenuhan PO untuk vendor PT Maju Jaya...">{{ old('catatan_pemohon') }}</textarea>
                        </div>
                    </div>

                    {{-- Datalist Supplier dari Database --}}
                    <datalist id="supplier-list-options">
                        @foreach($supplierList as $sup)
                            <option value="{{ $sup }}"></option>
                        @endforeach
                    </datalist>

                    {{-- Datalist Barang dari Database --}}
                    <datalist id="barang-list-options">
                        @isset($barangList)
                            @foreach($barangList as $brg)
                                <option value="{{ $brg }}"></option>
                            @endforeach
                        @endisset
                    </datalist>

                    {{-- Datalist Satuan (saran, tetap bisa ketik bebas untuk tambah baru) --}}
                    <datalist id="satuan-list-options">
                        <option value="box"></option>
                        <option value="botol"></option>
                        <option value="galon"></option>
                        <option value="jerigen"></option>
                        <option value="karton"></option>
                        <option value="pack"></option>
                        <option value="paket"></option>
                        <option value="pcs"></option>
                        <option value="polybag"></option>
                        <option value="pouches"></option>
                        <option value="roll"></option>
                    </datalist>

                    {{-- 3. FILE PENDUKUNG --}}
                    <div class="glass-card">
                        <div class="form-section-header">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-paperclip"></i></div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800">3. File Pendukung (Opsional)</h4>
                                @if(!$isMobile)
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Lampirkan nota, spesifikasi barang, atau dokumen pendukung lainnya</p>
                                @endif
                            </div>
                        </div>
                        <div class="bg-blue-50/50 border border-blue-100 p-3 md:p-3.5 rounded-2xl mb-3 md:mb-4 flex gap-3">
                            <i class="fas fa-info-circle text-blue-600 text-sm mt-0.5"></i>
                            <div class="text-[11px] text-blue-800 leading-normal font-semibold">
                                Format file yang didukung: <span class="text-blue-900 font-black">JPG, JPEG, PNG, PDF, DOC, DOCX, XLS, XLSX</span>. Ukuran berkas maksimal <span class="text-blue-900 font-black">10MB</span> per file. <span class="text-blue-900 font-black">Maksimal 10 file lampiran.</span>
                            </div>
                        </div>
                        <div id="file-pendukung-container" class="space-y-3"></div>
                        <button id="tambah-lampiran-btn" type="button" class="mt-3 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 font-black py-2 px-4 rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm">
                            <i class="fas fa-plus"></i> Tambah File Lampiran
                        </button>
                    </div>

                    @if($isMobile)
                        <div class="flex flex-col-reverse gap-3 pt-2">
                            <button type="button" id="reset-form-btn" class="btn-action-secondary">Reset Formulir</button>
                            <button type="submit" id="submit-button" class="btn-action-primary">
                                <i class="fas fa-paper-plane"></i> Ajukan Barang Sekarang
                            </button>
                        </div>
                    @else
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" id="reset-form-btn" class="btn-action-secondary">Reset Formulir</button>
                            <button type="submit" id="submit-button" class="btn-action-primary">
                                <i class="fas fa-paper-plane"></i> Ajukan Barang Sekarang
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
        const tambahBarisBtn = document.getElementById('tambah-baris-btn');
        const rincianBarangBody = document.getElementById('rincian-barang-body');

        const isMobile = @json($isMobile);

        function escapeAttr(val = '') {
            return String(val ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;');
        }

        function addRow(deskripsi = '', supplier = '', satuan = '', jumlah = '', keterangan = '') {
            const newRow = document.createElement('div');
            const escDeskripsi = escapeAttr(deskripsi);
            const escSupplier = escapeAttr(supplier);
            const escSatuan = escapeAttr(satuan);
            const escJumlah = escapeAttr(jumlah);
            const escKeterangan = escapeAttr(keterangan);

            if (isMobile) {
                newRow.className = 'rincian-row-mobile';
                newRow.innerHTML = `
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Nama Barang</label>
                        <input list="barang-list-options" type="text" name="rincian_deskripsi[]" value="${escDeskripsi}" class="modern-input !py-1.5 !px-2.5 !rounded-lg !text-xs" placeholder="Pilih / Ketik Nama Barang..." required autocomplete="off">
                        <div class="riwayat-hint text-[10px] font-semibold mt-0.5 text-slate-400 invisible truncate">&nbsp;</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Supplier</label>
                        <input list="supplier-list-options" name="rincian_supplier[]" value="${escSupplier}" class="modern-input !py-1.5 !px-2.5 !rounded-lg !text-xs" placeholder="Pilih / Ketik Supplier..." autocomplete="off">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Jumlah</label>
                            <input type="number" name="rincian_jumlah[]" value="${escJumlah}" class="modern-input !py-1.5 !px-2.5 !rounded-lg !text-xs" placeholder="Jumlah" min="1" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Satuan</label>
                            <input list="satuan-list-options" type="text" name="rincian_satuan[]" value="${escSatuan}" class="modern-input !py-1.5 !px-2.5 !rounded-lg !text-xs" placeholder="Pilih / Ketik Satuan..." required autocomplete="off">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase">Keterangan Item</label>
                            <input type="text" name="rincian_keterangan[]" value="${escKeterangan}" class="modern-input !py-1.5 !px-2.5 !rounded-lg !text-xs" placeholder="Keterangan item">
                        </div>
                        <div class="pt-4">
                            <button type="button" class="delete-row-btn text-red-500 hover:text-red-700 p-1.5 text-base">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
            } else {
                newRow.className = 'rincian-row-desktop';
                newRow.innerHTML = `
                    <div>
                        <input list="barang-list-options" type="text" name="rincian_deskripsi[]" value="${escDeskripsi}" class="modern-input !py-2 !px-3 !rounded-xl !text-xs" placeholder="Pilih / Ketik Nama Barang..." required autocomplete="off">
                        <div class="riwayat-hint text-[10px] font-semibold mt-0.5 text-slate-400 invisible truncate">&nbsp;</div>
                    </div>
                    <div>
                        <input list="supplier-list-options" name="rincian_supplier[]" value="${escSupplier}" class="modern-input !py-2 !px-3 !rounded-xl !text-xs" placeholder="Pilih / Ketik Supplier..." autocomplete="off">
                    </div>
                    <div class="flex gap-1.5">
                        <input type="number" name="rincian_jumlah[]" value="${escJumlah}" class="modern-input !py-2 !px-2.5 !rounded-xl !text-xs w-1/2" placeholder="0" min="1" required>
                        <input list="satuan-list-options" type="text" name="rincian_satuan[]" value="${escSatuan}" class="modern-input !py-2 !px-2.5 !rounded-xl !text-xs w-1/2" placeholder="Satuan..." required autocomplete="off">
                    </div>
                    <div>
                        <input type="text" name="rincian_keterangan[]" value="${escKeterangan}" class="modern-input !py-2 !px-3 !rounded-xl !text-xs" placeholder="Keterangan item">
                    </div>
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <button type="button" class="delete-row-btn text-slate-400 hover:text-red-600 hover:bg-red-50 p-2.5 rounded-xl text-sm transition-all">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;
            }
            rincianBarangBody.appendChild(newRow);
            newRow.querySelectorAll('input').forEach(inp => {
                inp.addEventListener('input', savePengajuanDraft);
                inp.addEventListener('change', savePengajuanDraft);
            });
            const namaInput = newRow.querySelector('input[name="rincian_deskripsi[]"]');
            if (namaInput) {
                namaInput.addEventListener('input', () => scheduleRiwayatCheck(namaInput));
                namaInput.addEventListener('change', () => scheduleRiwayatCheck(namaInput));
                if ((namaInput.value || '').trim().length >= 3) scheduleRiwayatCheck(namaInput);
            }
            newRow.querySelector('.delete-row-btn').addEventListener('click', () => { newRow.remove(); savePengajuanDraft(); });
        }

        // ===== Auto-save draft ala manage sales (localStorage, per-user) =====
        const DRAFT_KEY = 'pengajuan_barang_draft_{{ Auth::id() }}';
        // Bersihkan kunci global lama agar draft user A tidak bocor ke user B di perangkat yang sama
        try {
            if (DRAFT_KEY !== 'pengajuan_barang_draft') localStorage.removeItem('pengajuan_barang_draft');
        } catch (e) {}
        const mainForm = document.getElementById('pengajuan-barang-form');
        const judulInput = document.getElementById('judul-pengajuan');
        const catatanInput = document.getElementById('catatan_pemohon');
        const draftStatus = document.getElementById('draft-status');
        let isRestoring = false;

        function updateDraftStatus(text) {
            if (draftStatus) draftStatus.textContent = text;
        }

        function collectDraft() {
            const items = [];
            rincianBarangBody.querySelectorAll('.rincian-row-desktop, .rincian-row-mobile').forEach(row => {
                const d = row.querySelector('input[name="rincian_deskripsi[]"]');
                const s = row.querySelector('input[name="rincian_supplier[]"]');
                const j = row.querySelector('input[name="rincian_jumlah[]"]');
                const st = row.querySelector('input[name="rincian_satuan[]"]');
                const k = row.querySelector('input[name="rincian_keterangan[]"]');
                items.push({
                    deskripsi: d ? d.value : '',
                    supplier: s ? s.value : '',
                    jumlah: j ? j.value : '',
                    satuan: st ? st.value : '',
                    keterangan: k ? k.value : '',
                });
            });
            return {
                judul_pengajuan: judulInput ? judulInput.value : '',
                catatan_pemohon: catatanInput ? catatanInput.value : '',
                items: items,
                saved_at: new Date().toISOString(),
            };
        }

        function savePengajuanDraft() {
            if (!mainForm || isRestoring) return;
            try {
                localStorage.setItem(DRAFT_KEY, JSON.stringify(collectDraft()));
                const t = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                updateDraftStatus('Draft tersimpan otomatis • ' + t);
            } catch (e) { /* storage penuh / private mode, abaikan */ }
        }

        function readDraft() {
            try {
                const raw = localStorage.getItem(DRAFT_KEY);
                if (!raw) return null;
                const parsed = JSON.parse(raw);
                if (!parsed || !Array.isArray(parsed.items)) return null;
                return parsed;
            } catch (e) {
                return null;
            }
        }

        function clearPengajuanDraft() {
            try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
            updateDraftStatus('Draft tersimpan otomatis di perangkat ini');
        }

        // ===== Info riwayat 30 hari (milik sendiri, status aktif, info saja) =====
        const checkRiwayatUrl = "{{ route('pengajuan_barang.checkRiwayat') }}";
        const riwayatTimers = new WeakMap();

        function setRiwayatHint(input, state, text, fullTitle) {
            const row = input.closest('.rincian-row-desktop, .rincian-row-mobile') || input.parentElement;
            const hint = row ? row.querySelector('.riwayat-hint') : null;
            if (!hint) return;
            const base = 'riwayat-hint text-[10px] font-semibold mt-0.5 truncate ';
            if (!text) {
                hint.textContent = '\u00A0';
                hint.className = base + 'text-slate-400 invisible';
                hint.removeAttribute('title');
                return;
            }
            hint.textContent = text;
            hint.className = base
                + (state === 'found' ? 'text-amber-600 visible' : state === 'empty' ? 'text-emerald-600 visible' : 'text-slate-400 visible');
            hint.setAttribute('title', fullTitle || 'Filter: milik sendiri • 30 hari • status aktif');
        }

        function checkRiwayatForInput(input) {
            const nama = (input.value || '').trim();
            if (nama.length < 3) {
                setRiwayatHint(input, '', '');
                return;
            }
            setRiwayatHint(input, 'checking', '••• mengecek...');
            fetch(checkRiwayatUrl + '?nama=' + encodeURIComponent(nama), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(d => {
                    if (d && d.found) {
                        const tgl = (d.last && d.last.tanggal) ? d.last.tanggal : '';
                        const st = (d.last && d.last.status) ? d.last.status : '';
                        const short = '● Sudah pengajuan ' + d.count + 'x' + (tgl ? ' • ' + tgl : '') + (st ? ' (' + st + ')' : '');
                        const full = 'Sudah pengajuan ' + d.count + 'x dalam 30 hari terakhir'
                            + (tgl ? ' • terakhir ' + tgl : '') + (st ? ' (' + st + ')' : '')
                            + ' • Filter: milik sendiri, status aktif';
                        setRiwayatHint(input, 'found', short, full);
                    } else {
                        setRiwayatHint(input, 'empty', '○ Belum ada pengajuan (30 hari)', 'Belum ada pengajuan dalam 30 hari terakhir • milik sendiri • status aktif');
                    }
                })
                .catch(() => setRiwayatHint(input, '', ''));
        }

        function scheduleRiwayatCheck(input) {
            if (riwayatTimers.has(input)) clearTimeout(riwayatTimers.get(input));
            riwayatTimers.set(input, setTimeout(() => checkRiwayatForInput(input), 400));
        }

        function checkAllRiwayat() {
            if (!rincianBarangBody) return;
            rincianBarangBody.querySelectorAll('input[name="rincian_deskripsi[]"]').forEach(inp => {
                if ((inp.value || '').trim().length >= 3) scheduleRiwayatCheck(inp);
            });
        }

        const oldRincianDeskripsi = @json(old('rincian_deskripsi', []));
        const oldRincianSupplier = @json(old('rincian_supplier', []));
        const oldRincianSatuan = @json(old('rincian_satuan', []));
        const oldRincianJumlah = @json(old('rincian_jumlah', []));
        const oldRincianKeterangan = @json(old('rincian_keterangan', []));
        const hasOld = oldRincianDeskripsi && oldRincianDeskripsi.length > 0;

        function renderOld() {
            oldRincianDeskripsi.forEach((deskripsi, index) => {
                const supplier = oldRincianSupplier[index] !== undefined ? oldRincianSupplier[index] : '';
                const satuan = oldRincianSatuan[index] !== undefined ? oldRincianSatuan[index] : '';
                const jumlah = oldRincianJumlah[index] !== undefined ? oldRincianJumlah[index] : '';
                const keterangan = oldRincianKeterangan[index] !== undefined ? oldRincianKeterangan[index] : '';
                addRow(deskripsi, supplier, satuan, jumlah, keterangan);
            });
        }

        if (tambahBarisBtn) {
            isRestoring = true;
            if (hasOld) {
                // Prioritas 1: old() Laravel saat validasi gagal
                renderOld();
            } else {
                // Prioritas 2: draft localStorage (tahan refresh / pindah halaman)
                const draft = readDraft();
                const hasDraftContent = draft && (
                    (draft.judul_pengajuan && draft.judul_pengajuan.trim() !== '') ||
                    (draft.catatan_pemohon && draft.catatan_pemohon.trim() !== '') ||
                    draft.items.some(it => (it.deskripsi || it.supplier || it.jumlah || it.satuan || it.keterangan))
                );
                if (hasDraftContent) {
                    if (judulInput && draft.judul_pengajuan) judulInput.value = draft.judul_pengajuan;
                    if (catatanInput && draft.catatan_pemohon) catatanInput.value = draft.catatan_pemohon;
                    if (draft.items.length > 0) {
                        draft.items.forEach(it => addRow(it.deskripsi || '', it.supplier || '', it.satuan || '', it.jumlah || '', it.keterangan || ''));
                    } else {
                        addRow();
                    }
                    updateDraftStatus('Draft dipulihkan otomatis');
                } else {
                    addRow();
                }
            }
            isRestoring = false;
            savePengajuanDraft();
            checkAllRiwayat();
            tambahBarisBtn.addEventListener('click', () => { addRow(); savePengajuanDraft(); });
        }

        if (rincianBarangBody) {
            rincianBarangBody.addEventListener('input', savePengajuanDraft);
            rincianBarangBody.addEventListener('change', savePengajuanDraft);
            rincianBarangBody.addEventListener('input', (e) => {
                if (e.target && e.target.name === 'rincian_deskripsi[]') scheduleRiwayatCheck(e.target);
            });
            rincianBarangBody.addEventListener('change', (e) => {
                if (e.target && e.target.name === 'rincian_deskripsi[]') scheduleRiwayatCheck(e.target);
            });
        }
        if (judulInput) {
            judulInput.addEventListener('input', savePengajuanDraft);
            judulInput.addEventListener('change', savePengajuanDraft);
        }
        if (catatanInput) {
            catatanInput.addEventListener('input', savePengajuanDraft);
            catatanInput.addEventListener('change', savePengajuanDraft);
        }

        const tambahLampiranBtn = document.getElementById('tambah-lampiran-btn');
        const lampiranContainer = document.getElementById('file-pendukung-container');
        const submitButton = document.getElementById('submit-button');

        function updateLampiranButtonState() {
            const count = lampiranContainer.childElementCount;
            if (count >= 10) {
                tambahLampiranBtn.style.display = 'none';
            } else {
                tambahLampiranBtn.style.display = 'inline-flex';
            }
        }

        function addLampiranInput() {
            const count = lampiranContainer.childElementCount;
            if (count >= 10) {
                alert('Maksimal lampiran adalah 10 file.');
                return;
            }

            const uniqueId = 'file_' + Date.now() + Math.random().toString(36).substr(2, 9);
            const newFileWrapper = document.createElement('div');
            newFileWrapper.className = 'bg-white p-3 rounded-2xl border border-slate-200 hover:border-blue-400 transition-all shadow-sm flex flex-col gap-2';
            newFileWrapper.innerHTML = `<div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 text-sm"><i class="fas fa-paperclip"></i></div><div class="flex-grow min-w-0"><input type="file" name="file_pendukung[]" id="${uniqueId}" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer cursor-pointer font-semibold" /></div><button type="button" class="delete-lampiran-btn flex-shrink-0 text-slate-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-xl text-base transition-all"><i class="fas fa-trash-alt text-sm"></i></button></div><div id="progress-wrapper-${uniqueId}" class="mt-1 hidden pl-11"><div class="flex justify-between items-center mb-1"><span id="file-name-${uniqueId}" class="text-[10px] font-bold text-slate-700 truncate pr-2 w-4/5"></span><span id="status-text-${uniqueId}" class="text-[10px] font-bold text-blue-700 w-1/5 text-right"></span></div><div class="w-full bg-slate-200 rounded-full h-1"><div id="progress-bar-${uniqueId}" class="h-1 rounded-full transition-all duration-300" style="width: 0%"></div></div></div>`;
            lampiranContainer.appendChild(newFileWrapper);
            
            updateLampiranButtonState();

            const fileInput = newFileWrapper.querySelector(`#${uniqueId}`);
            const progressWrapper = newFileWrapper.querySelector(`#progress-wrapper-${uniqueId}`);
            const progressBar = newFileWrapper.querySelector(`#progress-bar-${uniqueId}`);
            const fileNameSpan = newFileWrapper.querySelector(`#file-name-${uniqueId}`);
            const statusTextSpan = newFileWrapper.querySelector(`#status-text-${uniqueId}`);
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileNameSpan.textContent = this.files[0].name;
                    statusTextSpan.textContent = 'Ready';
                    statusTextSpan.className = 'text-[10px] font-bold text-green-700 w-1/5 text-right';
                    progressBar.style.width = '100%';
                    progressBar.className = 'h-1 rounded-full transition-all duration-300 bg-green-500';
                    progressWrapper.classList.remove('hidden');
                } else { progressWrapper.classList.add('hidden'); }
            });
            newFileWrapper.querySelector('.delete-lampiran-btn').addEventListener('click', function() { 
                newFileWrapper.remove(); 
                updateLampiranButtonState();
            });
        }

        if (tambahLampiranBtn) { tambahLampiranBtn.addEventListener('click', addLampiranInput); addLampiranInput(); }

        if (mainForm) {
            mainForm.addEventListener('submit', function(e) {
                let tooLarge = false;
                document.querySelectorAll('input[type="file"][name="file_pendukung[]"]').forEach(input => {
                    if (input.files && input.files.length > 0) {
                        const file = input.files[0];
                        const maxBytes = 10 * 1024 * 1024; // 10MB
                        if (file.size > maxBytes) {
                            tooLarge = true;
                        }
                    }
                });

                if (tooLarge) {
                    e.preventDefault();
                    alert('Ukuran salah satu file lampiran melebihi batas maksimal 10MB. Silakan pilih berkas yang lebih kecil agar formulir tidak perlu diisi ulang.');
                    return false;
                }

                clearPengajuanDraft();

                submitButton.disabled = true;
                submitButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mengirim...`;
                submitButton.classList.add('inline-flex', 'items-center');
            });
        }

        const resetBtn = document.getElementById('reset-form-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                mainForm.reset();
                if (rincianBarangBody) rincianBarangBody.innerHTML = '';
                addRow();
                if (lampiranContainer) lampiranContainer.innerHTML = '';
                addLampiranInput();
                clearPengajuanDraft();
            });
        }
    });
    </script>
    @endpush
</x-layout-users>
