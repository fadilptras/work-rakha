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

    // Back button: pakai asal halaman dari controller (?from=ops|dashboard), fallback role lama.
    $backRoute = $backRoute ?? null;
    $backText = $backText ?? null;
    if (!$backRoute || !$backText) {
        $isWarehouseUser = in_array(strtolower($user->divisi ?? ''), ['finance dan gudang', 'fianance dan gudang'])
            || \Illuminate\Support\Str::contains(strtolower($user->jabatan ?? ''), ['admin gudang', 'gudang', 'legal', 'legal & purchasing']);
        $backRoute = $backRoute ?: ($isWarehouseUser ? route('warehouse.dashboard') : route('dashboard'));
        $backText = $backText ?: ($isWarehouseUser ? 'Back to Warehouse Dashboard' : 'Back to Dashboard');
    }
@endphp
<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Outfit', sans-serif; }
        /* == Background (sama seperti halaman forecast) == */
        .mesh-bg {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0;
            background-color: #ede9fe;
            background-image:
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            pointer-events: none;
        }
        [x-cloak] { display: none !important; }
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none; margin: 0;
        }
        input[type=number] { -moz-appearance: textfield; }

        .form-section-header { display: flex; align-items: center; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid rgba(226,232,240,0.8); margin-bottom: 24px; }

        /* Baris atas SPB: selalu 1 baris 3 kolom (CSS mandiri, tidak tergantung hasil build Tailwind) */
        .spb-top-row { display: grid !important; grid-template-columns: 5fr 4fr 3fr !important; gap: 12px !important; }
        @media (min-width: 768px) {
            .spb-top-row { gap: 20px !important; }
        }
        @media (max-width: 639.98px) {
            .spb-top-row { grid-template-columns: 1fr 1fr 1fr !important; gap: 8px !important; }
            .spb-top-row .ui-label { font-size: 9px !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
            .spb-top-row .ui-input { font-size: 11px !important; padding-left: 8px !important; padding-right: 8px !important; height: 34px !important; }
        }

        /* Tabel item SPB: hanya area ini yang scroll (halaman tidak memanjang) */
        .spb-items-wrap { overflow: auto; max-height: 38vh; min-height: 140px; }
        .spb-items-wrap table { border-collapse: separate; border-spacing: 0; }
        .spb-items-wrap thead th {
            position: sticky; top: 0; z-index: 10; background-color: #f8fafc;
            background-clip: padding-box;
            box-shadow: 0 1px 0 #e2e8f0;
        }
        @media (max-width: 767.98px) {
            .spb-items-wrap { max-height: 44vh; }
        }
        /* Kotak info di dalam popup item (riwayat + forecast) */
        .spb-info-box {
            border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc;
            padding: 8px 12px; font-size: 11px; font-weight: 600; line-height: 1.5;
            color: #64748b;
        }
        .spb-info-box.is-warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }
        .spb-info-box.is-ok { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
        .spb-info-box.is-fc { background: #f5f3ff; border-color: #ddd6fe; color: #5b21b6; }
        /* Badge keterangan di kolom Produk popup Tabel Forecast (mandiri, ala Warehouse Ops) */
        .fc-marks { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
        .fc-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 800; padding: 1px 8px; border-radius: 9999px; border: 1px solid; line-height: 1.5; white-space: nowrap; }
        .fc-badge--sudah { background: #fffbeb; border-color: #f59e0b; color: #b45309; }
        .fc-badge--ditabel { background: #f1f5f9; border-color: #94a3b8; color: #475569; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800 pb-16">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start">

            <div class="w-full flex justify-start">
                <x-ui.back-button id="spb-back-btn" :href="$backRoute" :label="$backText" />
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm" role="alert">
                    <p class="font-bold mb-1 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs font-semibold">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="hidden md:block">
                <x-ui.page-header title="Form Permintaan Barang (SPB)" subtitle="Ajukan permohonan barang atau perlengkapan operasional, tambahkan rincian item dengan rekomendasi qty forecast, lampirkan dokumen pendukung, dan pantau status persetujuan." icon="fa-box" />
            </div>

            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Form SPB</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">
                        Pengajuan barang + rekomendasi forecast.
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-box"></i>
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
                    <x-ui.glass-card>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/60 pb-3 md:pb-4 mb-3 md:mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-user-tie"></i></div>
                                <div>
                                    <h3 class="text-lg font-black text-slate-800">1. Informasi Pemohon</h3>
                                    @if(!$isMobile)
                                    <p class="text-xs text-slate-500 font-semibold" style="margin-top: 0; margin-bottom: 0;">Detail data karyawan yang mengajukan barang</p>
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
                        <div class="spb-top-row">
                            <div>
                                <label class="ui-label" for="judul-pengajuan">Judul Pengajuan <span class="text-red-500">*</span></label>
                                <input type="text" id="judul-pengajuan" name="judul_pengajuan" class="ui-input" placeholder="Pengadaan Alat dan Barang ..." value="{{ old('judul_pengajuan') }}" required>
                            </div>
                            <div>
                                <label class="ui-label">Tanggal Pengajuan</label>
                                <input type="text" class="ui-input !bg-slate-100 !text-slate-500 cursor-not-allowed" value="{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMM YYYY') }}" readonly tabindex="-1">
                                <input type="hidden" name="tanggal_pengajuan" value="{{ date('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="ui-label">No. SPB (Otomatis)</label>
                                <input type="text" class="ui-input !bg-slate-100 !text-slate-500 cursor-not-allowed" value="{{ $previewNomorSpb ?? '-' }}" readonly tabindex="-1">
                            </div>
                        </div>
                    </x-ui.glass-card>

                    {{-- 2. RINCIAN BARANG (alur: cari -> popup -> simpan ke tabel) --}}
<x-ui.glass-card>
    <div class="flex items-center gap-3 border-b border-slate-200/60 pb-3 md:pb-4 mb-3 md:mb-4">
        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-list-ol"></i></div>
        <div class="flex-1 min-w-0">
            <h4 class="text-lg font-black text-slate-800">2. Rincian Barang yang Diajukan</h4>
                                @if(!$isMobile)
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 0; margin-bottom: 0;">Klik Tambah Item Barang lalu isi detail lewat popup — qty otomatis dapat rekomendasi forecast, tetap bisa diubah manual</p>
                                @endif
                            </div>
                            <x-ui.button id="buka-forecast-btn" variant="soft-green" size="sm" icon="fa-table" class="shrink-0" title="Lihat tabel forecast sebagai referensi"><span class="hidden sm:inline">Tabel Forecast</span><span class="sm:hidden">Forecast</span></x-ui.button>
                        </div>

                        {{-- Tabel item yang sudah disimpan (hanya area ini yang scroll).
                            Tambah item lewat tombol di bawah / popup — nama dibantu datalist master. --}}
                        <div class="mt-2 rounded-xl border border-slate-200 bg-white" style="overflow:hidden;">
                            <div class="spb-items-wrap">
                                <table class="w-full text-xs table-fixed" style="min-width:770px;">
                                    <thead>
                                        <tr class="bg-slate-50/80 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                                            <th class="px-3 py-2 text-left font-bold" style="width:92px;">Kode</th>
                                            <th class="px-3 py-2 text-left font-bold">Barang</th>
                                            <th class="px-3 py-2 text-right font-bold" style="width:130px;">Jumlah</th>
                                            <th class="px-3 py-2 text-left font-bold" style="width:280px;">Info</th>
                                            <th class="px-3 py-2 text-center font-bold" style="width:80px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rincian-barang-body" class="divide-y divide-slate-100"></tbody>
                                </table>
                            </div>
                            <div id="empty-barang" class="px-4 py-3 text-center bg-slate-50/50 border-t border-dashed border-slate-200">
                                <p class="text-xs font-bold text-slate-500"><i class="fas fa-box-open text-slate-300 mr-1.5"></i>Belum ada item — klik Tambah Item Barang untuk menambahkan.</p>
                            </div>
                        </div>
                        
                        <div class="mt-2 flex items-center justify-between flex-wrap gap-2">
                            <x-ui.button id="tambah-baris-btn" variant="soft-blue" size="sm" icon="fa-plus">Tambah Item Barang</x-ui.button>
                            <span id="draft-status" class="text-[11px] font-bold text-slate-400">Draft tersimpan otomatis di perangkat ini</span>
                        </div>

                        {{-- Form Note / Catatan di bawah poin 2 --}}
                        <div class="mt-5 pt-4 border-t border-slate-200/80">
                            <label for="catatan_pemohon" class="ui-label !flex !items-center !gap-1.5">
                                <i class="fas fa-sticky-note text-blue-600"></i> Catatan / Note Pengajuan <span class="text-slate-400 font-normal normal-case">(Opsional)</span>
                            </label>
                            <textarea id="catatan_pemohon" name="catatan_pemohon" rows="3" class="ui-input !h-auto !py-2.5 min-h-[84px] !leading-relaxed" placeholder="Contoh: Pemenuhan PO untuk vendor PT Maju Jaya...">{{ old('catatan_pemohon') }}</textarea>
                        </div>
                    </x-ui.glass-card>

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

                    {{-- Popup detail item barang (dibuka dari hasil pencarian / tombol tambah / ubah) --}}
                    <x-ui.modal id="spb-item-modal" maxWidth="max-w-lg" title="Tambah Barang" subtitle="Isi supplier, jumlah & keterangan, lalu simpan ke tabel." icon="fa-box">
                        <div>
                            <label class="ui-label" for="m-spb-nama">Nama Barang <span class="text-red-500">*</span></label>
                            <input list="barang-list-options" type="text" id="m-spb-nama" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="Pilih / ketik nama barang…" autocomplete="off">
                            <div id="m-spb-riwayat" class="spb-info-box mt-2" style="display:none;"></div>
                        </div>
                        <div style="display:grid;grid-template-columns:110px minmax(0,1fr);gap:10px;">
                            <div>
                                <label class="ui-label" for="m-spb-kode">Kode</label>
                                <input type="text" id="m-spb-kode" class="ui-input ui-input--sm !bg-slate-100 !text-slate-500 cursor-not-allowed text-center font-mono" placeholder="—" readonly tabindex="-1" title="Kode master terisi otomatis dari nama barang">
                            </div>
                            <div>
                                <label class="ui-label" for="m-spb-supplier">Supplier</label>
                                <input list="supplier-list-options" type="text" id="m-spb-supplier" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="Pilih / ketik supplier…" autocomplete="off">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="ui-label" for="m-spb-jumlah">Jumlah <span class="text-red-500">*</span></label>
                                <input type="number" id="m-spb-jumlah" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="0" min="1">
                            </div>
                            <div>
                                <label class="ui-label" for="m-spb-satuan">Satuan <span class="text-red-500">*</span></label>
                                <input list="satuan-list-options" type="text" id="m-spb-satuan" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="Pilih / ketik…" autocomplete="off">
                            </div>
                        </div>
                        <div id="m-spb-forecast" class="spb-info-box is-fc" style="display:none;"></div>
                        <div>
                            <label class="ui-label" for="m-spb-ket">Keterangan Item</label>
                            <input type="text" id="m-spb-ket" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="Keterangan item (opsional)" autocomplete="off">
                        </div>
                        <p id="m-spb-error" class="hidden text-[11px] font-bold text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2.5"></p>
                        <x-slot:footer>
                            <x-ui.button variant="secondary" size="sm" data-modal-close>Batal</x-ui.button>
                            <x-ui.button variant="primary" size="sm" icon="fa-check" id="m-spb-save">Simpan ke Tabel</x-ui.button>
                        </x-slot:footer>
                    </x-ui.modal>

                    {{-- 3. FILE PENDUKUNG --}}
                    <x-ui.glass-card>
                        <div class="form-section-header">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-paperclip"></i></div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800">3. File Pendukung (Opsional)</h4>
                                @if(!$isMobile)
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 0; margin-bottom: 0;">Lampirkan nota, spesifikasi barang, atau dokumen pendukung lainnya</p>
                                @endif
                            </div>
                        </div>
                        <div class="bg-blue-50/50 border border-blue-100 p-3 md:p-3.5 rounded-xl mb-3 md:mb-4 flex gap-3">
                            <i class="fas fa-info-circle text-blue-600 text-sm mt-0.5"></i>
                            <div class="text-[11px] text-blue-800 leading-normal font-semibold">
                                Format file yang didukung: <span class="text-blue-900 font-black">JPG, JPEG, PNG, PDF, DOC, DOCX, XLS, XLSX</span>. Ukuran berkas maksimal <span class="text-blue-900 font-black">10MB</span> per file. <span class="text-blue-900 font-black">Maksimal 10 file lampiran.</span>
                            </div>
                        </div>
                        <div id="file-pendukung-container" class="space-y-3"></div>
                        <x-ui.button id="tambah-lampiran-btn" variant="secondary" size="sm" icon="fa-plus" class="mt-3">Tambah File Lampiran</x-ui.button>
                    </x-ui.glass-card>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                        <x-ui.button type="button" id="reset-form-btn" variant="secondary" size="md" class="w-full sm:w-auto">Reset Formulir</x-ui.button>
                        <x-ui.button type="submit" id="submit-button" variant="primary" size="md" icon="fa-paper-plane" class="w-full sm:w-auto" style="background:linear-gradient(135deg, #1d4ed8, #3b82f6);">Ajukan Barang Sekarang</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Tabel Forecast (referensi) --}}
    <div id="forecast-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <div class="min-w-0">
                    <h3 class="text-base font-black text-slate-800"><i class="fas fa-chart-line text-emerald-600 mr-2"></i>Tabel Forecast <span class="text-xs font-bold text-slate-400">— referensi pengajuan</span></h3>
                    <p id="forecast-modal-meta" class="text-[11px] text-slate-500 font-semibold mt-0.5 truncate">Memuat data...</p>
                </div>
                <button type="button" id="tutup-forecast-btn" class="text-slate-400 hover:text-red-500 transition-colors shrink-0 ml-3" title="Tutup">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="px-5 py-3 border-b border-slate-100 shrink-0">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input id="forecast-search" type="text" class="ui-input ui-input--sm !pl-8" placeholder="Cari nama produk..." autocomplete="off">
                </div>
            </div>
            <div class="overflow-auto flex-1 min-h-[200px]">
                <table class="w-full text-left border-separate border-spacing-0 whitespace-nowrap">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                            <th class="py-3 px-4 text-left">Produk</th>
                            <th class="py-3 px-3 text-center">Forecast</th>
                            <th class="py-3 px-3 text-center">Order</th>
                            <th class="py-3 px-3 text-center">Stok</th>
                            <th class="py-3 px-3 text-center">DOI</th>
                            <th class="py-3 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="forecast-tbody" class="divide-y divide-slate-100 text-xs text-slate-700">
                        <tr><td colspan="6" class="py-10 text-center text-slate-400 font-semibold">Memuat data forecast...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-100 flex justify-between items-center shrink-0">
                <span id="forecast-count" class="text-[11px] font-bold text-slate-400"></span>
                <button type="button" id="tutup-forecast-btn-2" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-colors">Tutup</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Back button ikut asal halaman: ?from=ops|dashboard diutamakan, lalu referrer, terakhir default server.
        (function fixSpbBack() {
            try {
                var btn = document.getElementById('spb-back-btn');
                if (!btn) return;
                var opsUrl = @json(route('warehouse.index', ['tab' => 'produk']));
                var dashUrl = @json(route('warehouse.dashboard'));
                var params = new URLSearchParams(window.location.search);
                var from = (params.get('from') || '').toLowerCase();
                var setBack = function (href, label) {
                    btn.setAttribute('href', href);
                    var spans = btn.querySelectorAll('span');
                    var labelEl = spans[spans.length - 1];
                    if (labelEl) labelEl.textContent = label;
                };
                if (from === 'ops') { setBack(opsUrl, 'Back to Warehouse Ops'); return; }
                if (from === 'dashboard') { setBack(dashUrl, 'Back to Warehouse Dashboard'); return; }
                var ref = document.referrer || '';
                if (ref.indexOf('/stock/warehouse') !== -1) { setBack(opsUrl, 'Back to Warehouse Ops'); }
                else if (ref.indexOf('/gudang/dashboard') !== -1) { setBack(dashUrl, 'Back to Warehouse Dashboard'); }
            } catch (e) {}
        })();

        const tambahBarisBtn = document.getElementById('tambah-baris-btn');
        const rincianBarangBody = document.getElementById('rincian-barang-body');

        const isMobile = @json($isMobile);

        function escapeAttr(val = '') {
            return String(val ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;');
        }

        // ===== Item barang ala Form SJ: cari -> popup -> simpan ke tabel =====
        const emptyBarang = document.getElementById('empty-barang');
        const barangKodeMap = @json($barangKodeMap ?? []);

        function kodeOf(nama) {
            const key = (nama || '').trim();
            if (!key || !barangKodeMap) return '';
            if (barangKodeMap[key]) return barangKodeMap[key];
            const lower = key.toLowerCase();
            for (const k in barangKodeMap) {
                if ((k || '').toLowerCase() === lower) return barangKodeMap[k];
            }
            return '';
        }
        let editingRow = null;
        const modalState = { fcAuto: null, rwData: null, fcData: null };
        const modalTimers = { rw: null, fc: null };

        const mSpbNama = document.getElementById('m-spb-nama');
        const mSpbKode = document.getElementById('m-spb-kode');
        const mSpbSupplier = document.getElementById('m-spb-supplier');
        const mSpbJumlah = document.getElementById('m-spb-jumlah');
        const mSpbSatuan = document.getElementById('m-spb-satuan');
        const mSpbKet = document.getElementById('m-spb-ket');
        const mSpbRwBox = document.getElementById('m-spb-riwayat');
        const mSpbFcBox = document.getElementById('m-spb-forecast');
        const mSpbErr = document.getElementById('m-spb-error');
        const mSpbSave = document.getElementById('m-spb-save');

        function updateEmpty() {
            if (!emptyBarang || !rincianBarangBody) return;
            const n = rincianBarangBody.querySelectorAll('.row-spb').length;
            emptyBarang.style.display = n ? 'none' : 'block';
        }

        function showModalErr(msg) {
            if (!mSpbErr) return;
            if (!msg) { mSpbErr.classList.add('hidden'); mSpbErr.textContent = ''; return; }
            mSpbErr.textContent = msg;
            mSpbErr.classList.remove('hidden');
        }

        function setModalBox(kind, state, html) {
            const box = kind === 'rw' ? mSpbRwBox : mSpbFcBox;
            if (!box) return;
            if (!html) { box.style.display = 'none'; box.innerHTML = ''; return; }
            box.style.display = 'block';
            box.innerHTML = html;
            box.classList.remove('is-warn', 'is-ok', 'is-fc');
            if (state === 'warn') box.classList.add('is-warn');
            else if (state === 'ok') box.classList.add('is-ok');
            else if (state === 'fc') box.classList.add('is-fc');
        }

        function rowHidden(row, name) {
            const el = row ? row.querySelector('input[name="' + name + '"]') : null;
            return el ? el.value : '';
        }

        function openItemModal(prefill, row) {
            editingRow = row || null;
            const titleEl = document.getElementById('spb-item-modal-title');
            if (titleEl) titleEl.textContent = editingRow ? 'Ubah Barang' : 'Tambah Barang';
            const p = prefill || {};
            if (mSpbNama) mSpbNama.value = p.deskripsi !== undefined ? p.deskripsi : rowHidden(editingRow, 'rincian_deskripsi[]');
            if (mSpbSupplier) mSpbSupplier.value = p.supplier !== undefined ? p.supplier : rowHidden(editingRow, 'rincian_supplier[]');
            if (mSpbJumlah) mSpbJumlah.value = p.jumlah !== undefined ? p.jumlah : rowHidden(editingRow, 'rincian_jumlah[]');
            if (mSpbSatuan) mSpbSatuan.value = p.satuan !== undefined ? p.satuan : rowHidden(editingRow, 'rincian_satuan[]');
            if (mSpbKet) mSpbKet.value = p.keterangan !== undefined ? p.keterangan : rowHidden(editingRow, 'rincian_keterangan[]');
            if (mSpbKode) mSpbKode.value = kodeOf(mSpbNama ? mSpbNama.value : '');
            modalState.fcAuto = null;
            modalState.rwData = null;
            modalState.fcData = null;
            showModalErr('');
            setModalBox('rw', '', '');
            setModalBox('fc', '', '');
            if (typeof uiModalOpen === 'function') uiModalOpen('spb-item-modal');
            else {
                const m = document.getElementById('spb-item-modal');
                if (m) { m.classList.remove('hidden'); m.classList.add('flex'); }
            }
            if (((mSpbNama && mSpbNama.value) || '').trim().length >= 3) scheduleModalChecks();
            setTimeout(function () { if (mSpbNama) mSpbNama.focus(); }, 250);
        }

        function closeItemModal() {
            if (typeof uiModalClose === 'function') uiModalClose('spb-item-modal');
            else {
                const m = document.getElementById('spb-item-modal');
                if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
            }
            editingRow = null;
        }

        function scheduleModalChecks() {
            if (!mSpbNama) return;
            clearTimeout(modalTimers.rw);
            clearTimeout(modalTimers.fc);
            const nama = (mSpbNama.value || '').trim();
            if (mSpbKode) mSpbKode.value = kodeOf(nama);
            if (nama.length < 3) {
                setModalBox('rw', '', '');
                setModalBox('fc', '', '');
                modalState.fcAuto = null;
                return;
            }
            modalTimers.rw = setTimeout(function () { refreshModalRiwayat(nama); }, 400);
            modalTimers.fc = setTimeout(function () { refreshModalForecast(nama); }, 500);
        }

        function refreshModalRiwayat(nama) {
            setModalBox('rw', '', '••• mengecek pengajuan 30 hari terakhir…');
            fetchRiwayat(nama).then(function (r) {
                if (((mSpbNama.value || '').trim()) !== nama) return;
                if (r && r.found) {
                    modalState.rwData = r;
                    const tgl = (r.last && r.last.tanggal) ? ' • ' + r.last.tanggal : '';
                    const st = (r.last && r.last.status) ? ' (' + r.last.status + ')' : '';
                    setModalBox('rw', 'warn', '● Sudah pengajuan <b>' + r.count + 'x</b> dalam 30 hari terakhir' + escapeAttr(tgl + st) + ' — semua user, status aktif.');
                } else {
                    modalState.rwData = { found: false };
                    setModalBox('rw', 'ok', '○ Belum ada pengajuan dalam 30 hari terakhir.');
                }
            }).catch(function () { setModalBox('rw', '', ''); });
        }

        function refreshModalForecast(nama) {
            setModalBox('fc', 'fc', '••• menghitung rekomendasi forecast…');
            fetchForecast(nama).then(function (d) {
                if (((mSpbNama.value || '').trim()) !== nama) return;
                if (d && d.found) {
                    modalState.fcData = d;
                    const satuan = d.satuan_sales || 'Pcs';
                    const suggest = d.order_qty;
                    if (suggest > 0) {
                        setModalBox('fc', 'fc', '<b>Rekomendasi order: ' + fmtNum(suggest) + ' ' + escapeAttr(satuan) + '</b> — forecast ' + fmtNum(d.forecast_qty) + ', stok ' + fmtNum(d.stok) + ', DOI ' + fmtNum(d.doi) + ' hari. Angka sudah terisi otomatis, tetap bisa diedit.');
                        const cur = ((mSpbJumlah && mSpbJumlah.value) || '').trim();
                        if (mSpbJumlah && (cur === '' || cur === String(modalState.fcAuto || ''))) {
                            mSpbJumlah.value = suggest;
                            if (mSpbSatuan && !(mSpbSatuan.value || '').trim()) mSpbSatuan.value = satuan;
                            modalState.fcAuto = String(suggest);
                            if (mSpbJumlah) { mSpbJumlah.style.borderColor = '#10b981'; setTimeout(function () { mSpbJumlah.style.borderColor = ''; }, 1200); }
                        }
                    } else {
                        setModalBox('fc', 'ok', '<b>Tidak perlu order</b> — stok ' + fmtNum(d.stok) + ' ' + escapeAttr(d.satuan_stok || 'Pcs') + ' mencukupi untuk "' + escapeAttr(d.product) + '".');
                    }
                } else {
                    modalState.fcData = null;
                    setModalBox('fc', '', 'Tidak ada data order forecast untuk "' + escapeAttr(nama) + '".');
                }
            }).catch(function () { setModalBox('fc', '', ''); });
        }

        function fillRowValues(tr, vals) {
            const set = function (name, v) { const el = tr.querySelector('input[name="' + name + '"]'); if (el) el.value = v || ''; };
            set('rincian_deskripsi[]', vals.deskripsi);
            set('rincian_supplier[]', vals.supplier);
            set('rincian_jumlah[]', vals.jumlah);
            set('rincian_satuan[]', vals.satuan);
            set('rincian_keterangan[]', vals.keterangan);
            const kode = kodeOf(vals.deskripsi);
            const kodeEl = tr.querySelector('.cell-kode');
            if (kodeEl) { kodeEl.textContent = kode || '—'; kodeEl.setAttribute('title', kode || 'Kode tidak ada di master'); }
            const namaEl = tr.querySelector('.cell-nama');
            if (namaEl) { namaEl.textContent = vals.deskripsi || '—'; namaEl.setAttribute('title', vals.deskripsi || ''); }
            const sub = [vals.supplier, vals.keterangan].filter(function (x) { return (x || '').trim() !== ''; }).join(' • ');
            const subEl = tr.querySelector('.cell-sub');
            if (subEl) { subEl.textContent = sub; subEl.setAttribute('title', sub); }
            tr.querySelector('.qty-txt').textContent = vals.jumlah || '';
            tr.querySelector('.cell-sat').textContent = vals.satuan || '';
        }

        // Info 1 baris: ringkas riwayat + forecast, detail penuh di tooltip
        function renderRowInfo(tr) {
            const box = tr.querySelector('.cell-info');
            if (!box) return;
            const info = tr._info || {};
            const parts = [];
            const full = [];
            if (info.rw) {
                if (info.rw.found) {
                    const tgl = (info.rw.last && info.rw.last.tanggal) ? ' • ' + info.rw.last.tanggal : '';
                    const st = (info.rw.last && info.rw.last.status) ? ' (' + info.rw.last.status + ')' : '';
                    const oleh = (info.rw.last && info.rw.last.oleh) ? ' oleh ' + info.rw.last.oleh : '';
                    const judul = (info.rw.last && info.rw.last.judul) ? ' "' + info.rw.last.judul + '"' : '';
                    parts.push('<span class="text-amber-600 font-bold">● Sudah ' + info.rw.count + 'x</span>');
                    full.push('Sudah diajukan ' + info.rw.count + 'x dalam 30 hari (semua user)' + judul + tgl + st + oleh);
                } else {
                    parts.push('<span class="text-emerald-600 font-semibold">○ Belum pernah</span>');
                    full.push('Belum ada pengajuan siapapun (30 hari)');
                }
            }
            if (info.fc && info.fc.found) {
                const satuan = info.fc.satuan_sales || 'Pcs';
                if (info.fc.order_qty > 0) {
                    parts.push('<span class="text-violet-700 font-bold">Order ' + fmtNum(info.fc.order_qty) + ' ' + escapeAttr(satuan) + '</span>');
                    full.push('Rekomendasi order ' + fmtNum(info.fc.order_qty) + ' ' + satuan + ' • forecast ' + fmtNum(info.fc.forecast_qty) + ' • stok ' + fmtNum(info.fc.stok));
                } else {
                    parts.push('<span class="text-slate-400 font-semibold">Stok cukup</span>');
                    full.push('Stok mencukupi, tidak perlu order');
                }
            }
            box.innerHTML = parts.length ? parts.join('<span class="text-slate-300 font-normal"> • </span>') : '<span class="text-slate-300">—</span>';
            if (full.length) box.setAttribute('title', full.join(' | '));
            else box.removeAttribute('title');
        }

        function addRow(deskripsi, supplier, satuan, jumlah, keterangan, info) {
            if (!rincianBarangBody) return;
            const tr = document.createElement('tr');
            tr.className = 'row-spb hover:bg-blue-50/50 transition-colors bg-white';
            tr.innerHTML =
                '<td class="px-3 py-2.5 align-top">' +
                    '<div class="font-mono font-bold text-slate-500 cell-kode truncate" style="font-size:11px;"></div>' +
                '</td>' +
                '<td class="px-3 py-2.5 align-top" style="min-width:0;">' +
                    '<div class="font-bold text-slate-800 cell-nama leading-snug truncate"></div>' +
                    '<div class="text-[11px] text-slate-400 font-semibold mt-0.5 cell-sub leading-snug truncate"></div>' +
                    '<input type="hidden" name="rincian_deskripsi[]">' +
                    '<input type="hidden" name="rincian_supplier[]">' +
                    '<input type="hidden" name="rincian_keterangan[]">' +
                '</td>' +
                '<td class="px-3 py-2.5 align-top text-right whitespace-nowrap">' +
                    '<span class="font-black text-slate-800 qty-txt"></span> <span class="text-slate-500 font-bold cell-sat"></span>' +
                    '<input type="hidden" name="rincian_jumlah[]">' +
                    '<input type="hidden" name="rincian_satuan[]">' +
                '</td>' +
                '<td class="px-3 py-2.5 align-top"><div class="cell-info whitespace-nowrap overflow-hidden text-ellipsis text-[10px] leading-relaxed"></div></td>' +
                '<td class="px-3 py-2.5 align-top text-center whitespace-nowrap">' +
                    '<button type="button" title="Ubah" class="btn-edit ui-btn ui-btn--edit mr-1"><i class="fas fa-pen text-[11px]"></i></button>' +
                    '<button type="button" title="Hapus" class="btn-del ui-btn ui-btn--delete"><i class="fas fa-trash-can text-[11px]"></i></button>' +
                '</td>';
            fillRowValues(tr, { deskripsi: deskripsi, supplier: supplier, satuan: satuan, jumlah: jumlah, keterangan: keterangan });
            tr._info = info || { rw: null, fc: null };
            renderRowInfo(tr);
            tr.querySelector('.btn-del').addEventListener('click', function () { tr.remove(); savePengajuanDraft(); updateEmpty(); });
            tr.querySelector('.btn-edit').addEventListener('click', function () { openItemModal(null, tr); });
            rincianBarangBody.appendChild(tr);
            updateEmpty();
            savePengajuanDraft();
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
        let isSubmitting = false;

        function updateDraftStatus(text) {
            if (draftStatus) draftStatus.textContent = text;
        }

        function collectDraft() {
            const items = [];
            rincianBarangBody.querySelectorAll('.row-spb').forEach(row => {
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
                v: 1,
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
                if (parsed.v !== undefined && parsed.v !== 1) return null;
                return parsed;
            } catch (e) {
                return null;
            }
        }

        function clearPengajuanDraft() {
            try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
            updateDraftStatus('Draft tersimpan otomatis di perangkat ini');
        }

        // ===== Info riwayat 30 hari (semua user, status aktif, info saja) =====
        const checkRiwayatUrl = "{{ route('pengajuan_barang.checkRiwayat') }}";

        function fetchRiwayat(nama) {
            return fetch(checkRiwayatUrl + '?nama=' + encodeURIComponent(nama), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); });
        }

        function recheckRowRiwayat(row) {
            const nama = (rowHidden(row, 'rincian_deskripsi[]') || '').trim();
            if (nama.length < 3) return Promise.resolve();
            return fetchRiwayat(nama).then(function (r) {
                row._info = row._info || {};
                row._info.rw = (r && r.found) ? r : { found: false };
                renderRowInfo(row);
            }).catch(function () {});
        }

        // ===== Angka Order dari sales forecast (sumber: kolom Order, bukan Forecast) =====
        // Alur: ketik nama barang di popup -> fetch cek-forecast -> angka Order
        // langsung masuk qty popup (boleh diedit) + badge info di tabel.
        // Auto-fill tidak menimpa ketikan manual; order 0 = qty dibiarkan kosong.
        const checkForecastUrl = "{{ route('pengajuan_barang.checkForecast') }}";

        function fmtNum(n) {
            const v = Number(n);
            if (!isFinite(v)) return n;
            return v.toLocaleString('id-ID');
        }

        function fetchForecast(nama) {
            return fetch(checkForecastUrl + '?nama=' + encodeURIComponent(nama), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); });
        }

        function recheckRowForecast(row) {
            const nama = (rowHidden(row, 'rincian_deskripsi[]') || '').trim();
            if (nama.length < 3) return Promise.resolve();
            return fetchForecast(nama).then(function (d) {
                row._info = row._info || {};
                row._info.fc = (d && d.found) ? d : null;
                renderRowInfo(row);
            }).catch(function () {});
        }

        function recheckAllRows() {
            if (!rincianBarangBody) return;
            const rows = rincianBarangBody.querySelectorAll('.row-spb');
            rows.forEach(function (row, i) {
                setTimeout(function () { recheckRowRiwayat(row); }, i * 250);
                setTimeout(function () { recheckRowForecast(row); }, i * 250 + 120);
            });
        }

        // ===== Modal tabel forecast (referensi, data live dari endpoint) =====
        const forecastTableUrl = "{{ route('sales.forecast.data') }}";
        const forecastModal = document.getElementById('forecast-modal');
        const forecastTbody = document.getElementById('forecast-tbody');
        const forecastSearch = document.getElementById('forecast-search');
        const forecastMeta = document.getElementById('forecast-modal-meta');
        const forecastCount = document.getElementById('forecast-count');
        let fcTableCache = null;

        function escapeHtml(s) {
            return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function openForecastModal() {
            if (!forecastModal) return;
            forecastModal.classList.remove('hidden');
            forecastModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            if (!fcTableCache) loadForecastTable();
            else renderForecastRows(forecastSearch ? forecastSearch.value : '');
        }

        function closeForecastModal() {
            if (!forecastModal) return;
            forecastModal.classList.add('hidden');
            forecastModal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function loadForecastTable() {
            if (!forecastTbody) return;
            forecastTbody.innerHTML = '<tr><td colspan="6" class="py-10 text-center text-slate-400 font-semibold">Memuat data forecast...</td></tr>';
            fetch(forecastTableUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json().then(d => ({ status: r.status, body: d })))
                .then(({ status, body }) => {
                    if (status === 403 || !body || body.success !== true) {
                        const msg = (body && body.message) || 'Anda tidak memiliki akses ke data forecast.';
                        forecastTbody.innerHTML = '<tr><td colspan="6" class="py-10 text-center text-slate-400 font-semibold">' + escapeHtml(msg) + '</td></tr>';
                        if (forecastMeta) forecastMeta.textContent = 'Akses ditolak.';
                        return;
                    }
                    fcTableCache = body.data || [];
                    const m = body.meta || {};
                    if (forecastMeta) {
                        forecastMeta.textContent = 'Periode ' + (m.bulan_aktif || '') + ' ' + (m.tahun || '')
                            + ' • acuan ' + (m.ref_months || '-') + ' bln • +' + (m.percentage || 0) + '% • DOI ' + (m.doi || '-') + ' hr • stok ' + (m.label_stok || '-');
                    }
                    renderForecastRows(forecastSearch ? forecastSearch.value : '');
                })
                .catch(() => {
                    forecastTbody.innerHTML = '<tr><td colspan="6" class="py-10 text-center text-slate-400 font-semibold">Gagal memuat data. Tutup lalu buka kembali.</td></tr>';
                });
        }

        function renderForecastRows(filter) {
            if (!forecastTbody || !fcTableCache) return;
            const key = (filter || '').trim().toLowerCase();
            const rows = key ? fcTableCache.filter(d => (d.nama_produk || '').toLowerCase().includes(key)) : fcTableCache;
            if (forecastCount) forecastCount.textContent = rows.length + ' dari ' + fcTableCache.length + ' produk';
            if (rows.length === 0) {
                forecastTbody.innerHTML = '<tr><td colspan="6" class="py-10 text-center text-slate-400 font-semibold">Tidak ada produk yang cocok.</td></tr>';
                return;
            }
            forecastTbody.innerHTML = rows.map(d =>
                '<tr class="hover:bg-emerald-50/60 transition-colors">'
                + '<td class="py-2.5 px-4 font-bold text-slate-800 whitespace-normal min-w-[180px]">' + escapeHtml(d.nama_produk) + '<div class="text-[10px] font-semibold text-slate-400">avg ' + fmtNum(d.avg_qty) + ' ' + escapeHtml(d.satuan_sales || '') + ' /bln</div><div class="fc-marks" data-fc-marks="' + escapeAttr(d.nama_produk) + '"></div></td>'
                + '<td class="py-2.5 px-3 text-center font-black text-emerald-600">' + fmtNum(d.forecast_qty) + '</td>'
                + '<td class="py-2.5 px-3 text-center font-black text-indigo-700">' + fmtNum(d.order_qty) + '</td>'
                + '<td class="py-2.5 px-3 text-center font-bold text-orange-600">' + fmtNum(d.stok_tersedia) + '</td>'
                + '<td class="py-2.5 px-3 text-center font-bold text-rose-600">' + fmtNum(d.doi_qty) + '</td>'
                + '<td class="py-2.5 px-3 text-center"><button type="button" class="fc-pilih-btn text-[11px] font-black text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg px-3 py-1.5 transition-colors" data-produk="' + escapeAttr(d.nama_produk) + '">Pilih</button></td>'
                + '</tr>'
            ).join('');
            paintFcMarks(rows);
        }

        // ===== Keterangan pengajuan di kolom Produk popup Tabel Forecast =====
        // Badge "Sudah Nx" = pernah diajukan siapapun 30 hari terakhir (1 request batch).
        // Badge "Di tabel" = nama sudah masuk rincian SPB (baca live, anti double).
        const statusBatchNamesUrl = "{{ route('pengajuan_barang.statusBatchNames') }}";
        const fcStatusCache = {};
        let fcStatusInflight = null;

        function tableNameSet() {
            const set = new Set();
            if (!rincianBarangBody) return set;
            rincianBarangBody.querySelectorAll('.row-spb').forEach(function (row) {
                const d = row.querySelector('input[name="rincian_deskripsi[]"]');
                const n = (d ? d.value : '').trim().toLowerCase();
                if (n) set.add(n);
            });
            return set;
        }

        function fcMarkHtml(nama, inTable) {
            const info = fcStatusCache[nama];
            let html = '';
            if (inTable) html += '<span class="fc-badge fc-badge--ditabel">● Di tabel</span>';
            if (info && info.found) {
                const last = info.last || {};
                const tip = 'Sudah diajukan ' + info.count + 'x dalam 30 hari (semua user)'
                    + (last.judul ? ' "' + last.judul + '"' : '')
                    + (last.tanggal ? ' • ' + last.tanggal : '')
                    + (last.status ? ' (' + last.status + ')' : '')
                    + (last.oleh ? ' oleh ' + last.oleh : '');
                html += '<span class="fc-badge fc-badge--sudah" title="' + escapeAttr(tip) + '">● Sudah ' + info.count + 'x</span>';
            }
            return html;
        }

        function paintFcMarks(rows) {
            if (!forecastTbody) return;
            const inTable = tableNameSet();
            const missing = [];
            (rows || []).forEach(function (d) {
                const nama = d.nama_produk || '';
                if (!nama || fcStatusCache[nama]) return;
                if (missing.indexOf(nama) === -1) missing.push(nama);
            });
            forecastTbody.querySelectorAll('[data-fc-marks]').forEach(function (box) {
                const nama = box.getAttribute('data-fc-marks') || '';
                box.innerHTML = fcMarkHtml(nama, inTable.has(nama.trim().toLowerCase()));
            });
            if (missing.length) fetchFcStatus(missing);
        }

        function fetchFcStatus(names) {
            const fresh = names.filter(n => !fcStatusCache[n]);
            if (!fresh.length) return;
            const run = function (batch) {
                const token = document.querySelector('meta[name="csrf-token"]');
                return fetch(statusBatchNamesUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(token ? { 'X-CSRF-TOKEN': token.getAttribute('content') } : {}),
                    },
                    body: JSON.stringify({ names: batch }),
                }).then(r => r.json()).then(json => {
                    const map = (json && json.map) || {};
                    batch.forEach(n => { fcStatusCache[n] = map[n] || { found: false, count: 0, last: null }; });
                    if (forecastModal && !forecastModal.classList.contains('hidden')) {
                        const inTable = tableNameSet();
                        forecastTbody.querySelectorAll('[data-fc-marks]').forEach(function (box) {
                            const nama = box.getAttribute('data-fc-marks') || '';
                            if (batch.indexOf(nama) !== -1) box.innerHTML = fcMarkHtml(nama, inTable.has(nama.trim().toLowerCase()));
                        });
                    }
                }).catch(() => {
                    batch.forEach(n => { fcStatusCache[n] = { found: false, count: 0, last: null }; });
                });
            };
            // Antrekan berantai agar 1 request dalam penerbangan (hemat + anti race).
            const chain = fcStatusInflight || Promise.resolve();
            fcStatusInflight = chain.then(() => run(fresh)).finally(() => { if (fcStatusInflight) fcStatusInflight = null; });
        }

        // Pilih produk dari tabel forecast: buka popup item dengan nama terisi,
        // info riwayat + rekomendasi forecast dimuat otomatis di dalam popup.
        function pilihForecastProduk(namaProduk) {
            if (!namaProduk || !rincianBarangBody) return;
            closeForecastModal();
            openItemModal({ deskripsi: namaProduk });
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
                    }
                    updateDraftStatus('Draft dipulihkan otomatis');
                }
            }
            isRestoring = false;
            savePengajuanDraft();
            updateEmpty();
            recheckAllRows();
            // Tombol tambah kini membuka popup, bukan menambah baris inline
            tambahBarisBtn.addEventListener('click', () => { openItemModal(); });
        }

        // Popup item: cek riwayat + forecast saat nama berubah, Enter = simpan
        if (mSpbNama) {
            mSpbNama.addEventListener('input', scheduleModalChecks);
            mSpbNama.addEventListener('change', scheduleModalChecks);
        }
        [mSpbNama, mSpbSupplier, mSpbJumlah, mSpbSatuan, mSpbKet].forEach(function (el) {
            if (!el) return;
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); if (mSpbSave) mSpbSave.click(); }
            });
        });
        if (mSpbSave) {
            mSpbSave.addEventListener('click', function () {
                const deskripsi = ((mSpbNama && mSpbNama.value) || '').trim();
                const supplier = ((mSpbSupplier && mSpbSupplier.value) || '').trim();
                const jumlah = ((mSpbJumlah && mSpbJumlah.value) || '').trim();
                const satuan = ((mSpbSatuan && mSpbSatuan.value) || '').trim();
                const keterangan = ((mSpbKet && mSpbKet.value) || '').trim();
                if (!deskripsi) { showModalErr('Nama barang wajib diisi.'); if (mSpbNama) mSpbNama.focus(); return; }
                const q = parseFloat(jumlah);
                if (!jumlah || !(q > 0)) { showModalErr('Jumlah wajib diisi dan lebih dari 0.'); if (mSpbJumlah) mSpbJumlah.focus(); return; }
                if (!satuan) { showModalErr('Satuan wajib diisi.'); if (mSpbSatuan) mSpbSatuan.focus(); return; }
                // Anti double: tolak nama yang sudah ada di baris lain (tiap barang cukup 1 baris).
                const dupeRow = Array.from(rincianBarangBody.querySelectorAll('.row-spb')).find(row => {
                    if (editingRow && row === editingRow) return false;
                    const d = row.querySelector('input[name="rincian_deskripsi[]"]');
                    return d && (d.value || '').trim().toLowerCase() === deskripsi.toLowerCase();
                });
                if (dupeRow) { showModalErr('"' + deskripsi + '" sudah ada di tabel — ubah baris yang ada saja, jangan tambah dobel.'); if (mSpbNama) mSpbNama.focus(); return; }
                showModalErr('');
                const info = { rw: modalState.rwData || null, fc: modalState.fcData || null };
                if (editingRow) {
                    fillRowValues(editingRow, { deskripsi: deskripsi, supplier: supplier, satuan: satuan, jumlah: jumlah, keterangan: keterangan });
                    editingRow._info = info;
                    renderRowInfo(editingRow);
                } else {
                    addRow(deskripsi, supplier, satuan, jumlah, keterangan, info);
                }
                savePengajuanDraft();
                updateEmpty();
                closeItemModal();
            });
        }
        // Wiring modal tabel forecast
        const bukaForecastBtn = document.getElementById('buka-forecast-btn');
        if (bukaForecastBtn) bukaForecastBtn.addEventListener('click', openForecastModal);
        ['tutup-forecast-btn', 'tutup-forecast-btn-2'].forEach(id => {
            const b = document.getElementById(id);
            if (b) b.addEventListener('click', closeForecastModal);
        });
        if (forecastModal) {
            forecastModal.addEventListener('click', (e) => {
                if (e.target === forecastModal) closeForecastModal();
            });
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && forecastModal && !forecastModal.classList.contains('hidden')) closeForecastModal();
        });
        if (forecastSearch) forecastSearch.addEventListener('input', () => renderForecastRows(forecastSearch.value));
        if (forecastTbody) {
            forecastTbody.addEventListener('click', (e) => {
                const b = e.target && e.target.closest ? e.target.closest('.fc-pilih-btn') : null;
                if (b) pilihForecastProduk(b.getAttribute('data-produk') || '');
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

        // ===== File lampiran (maks 10 file @ 10MB, dicek ulang saat submit) =====
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

        // ===== Submit: tolak file over-limit, kunci klik ganda.
        // Draft baru dibersihkan saat halaman benar-benar bernavigasi (submit sukses).
        // Submit gagal / offline = tidak ada navigasi = draft utuh, tinggal coba lagi.
        if (mainForm) {
            mainForm.addEventListener('submit', function(e) {
                if (rincianBarangBody && rincianBarangBody.querySelectorAll('.row-spb').length === 0) {
                    e.preventDefault();
                    alert('Belum ada item — klik Tambah Item Barang, isi detail di popup, lalu Simpan ke Tabel.');
                    openItemModal();
                    return false;
                }
                // Anti double: satu pengajuan tidak boleh memuat barang yang sama 2x.
                // (Samakan dengan toggle Add to SPB di Warehouse Ops.)
                const seen = {};
                const dupes = [];
                rincianBarangBody.querySelectorAll('input[name="rincian_deskripsi[]"]').forEach(input => {
                    const nama = (input.value || '').trim();
                    const key = nama.toLowerCase();
                    if (!key) return;
                    if (seen[key]) { if (!dupes.includes(nama)) dupes.push(nama); }
                    else seen[key] = true;
                });
                if (dupes.length > 0) {
                    e.preventDefault();
                    alert('Ada barang yang dobel dalam pengajuan ini:\n• ' + dupes.join('\n• ') + '\n\nHapus salah satunya dulu — tiap barang cukup 1 baris (gabung qty-nya bila perlu).');
                    return false;
                }
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

                isSubmitting = true;

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mengirim...`;
                    submitButton.classList.add('inline-flex', 'items-center');
                }
            });
        }

        // Flush terakhir anti mati-lampu: sukses navigasi -> draft dibersihkan,
        // selain itu (refresh/tutup/gagal) -> draft disimpan ulang.
        window.addEventListener('pagehide', function () {
            if (isSubmitting) clearPengajuanDraft();
            else savePengajuanDraft();
        });

        const resetBtn = document.getElementById('reset-form-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                mainForm.reset();
                if (rincianBarangBody) rincianBarangBody.innerHTML = '';
                updateEmpty();
                if (lampiranContainer) lampiranContainer.innerHTML = '';
                addLampiranInput();
                clearPengajuanDraft();
            });
        }
    });
    </script>
    @endpush
</x-layout-users>
