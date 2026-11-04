@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
    
    $user = \Illuminate\Support\Facades\Auth::user();
    $isApprover = \App\Models\User::query()
        ->where('approver_dana_1_id', $user->id)
        ->orWhere('approver_dana_2_id', $user->id)
        ->orWhere('approver_dana_3_id', $user->id)
        ->orWhere('approver_dana_4_id', $user->id)
        ->exists();
    $isSuperUser = $user && (method_exists($user, 'isSuperUser') ? $user->isSuperUser() : \App\Models\User::isSuperUserEmail($user->email ?? ''));
    $canMonitor = $isApprover || $user->role === 'admin' || $isSuperUser;
    $batasEntertain = $batasEntertain ?? \App\Models\PengajuanDana::batasEntertainForUser($user);
    $batasEntertainFormatted = number_format($batasEntertain, 0, ',', '.');
@endphp
<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Outfit', sans-serif; }
        /* == Background (sama seperti halaman SPB) == */
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

        .form-section-header { display: flex; align-items: center; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid rgba(226,232,240,0.8); margin-bottom: 24px; }

        /* Input nominal dengan prefix Rp */
        .input-wrapper-rp { position: relative; display: block; width: 100%; }
        .label-rp {
            position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
            font-size: 11px; font-weight: 700; color: #94a3b8;
            pointer-events: none; z-index: 10;
        }
        .input-with-rp { padding-left: 32px !important; }

        /* Tabel rincian: hanya area ini yang scroll */
        .dana-items-wrap { overflow: auto; max-height: 38vh; min-height: 120px; }
        .dana-items-wrap table { border-collapse: separate; border-spacing: 0; }
        .dana-items-wrap thead th {
            position: sticky; top: 0; z-index: 10; background-color: #f8fafc;
            background-clip: padding-box;
            box-shadow: 0 1px 0 #e2e8f0;
        }
        @media (max-width: 767.98px) {
            .dana-items-wrap { max-height: 44vh; }
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800 pb-16">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start">

            @php
                $fromSales = request('from') === 'sales';
                $backRoute = $fromSales ? route('sales.index') : route('dashboard');
                $backText = $fromSales ? 'Kembali ke Sales Dashboard' : 'Kembali ke Dashboard';
            @endphp
            <div class="w-full flex justify-start">
                <x-ui.back-button :href="$backRoute" :label="$backText" />
            </div>

            <div class="hidden md:block">
                <x-ui.page-header title="Form Pengajuan Dana" subtitle="Ajukan permohonan dana operasional atau pengeluaran kantor, tambahkan rincian item, lampirkan dokumen bukti, dan pantau status persetujuan." icon="fa-hand-holding-usd" />
            </div>

            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Form Dana</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">
                        Pengajuan dana + lampiran bukti.
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>

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

            {{-- STACKED VERTICAL LAYOUT --}}
            <div class="space-y-4 md:space-y-6 pb-10">

                @if($isMobile)
                <div class="flex flex-col gap-2 mb-4">
                    @if($canMonitor)
                    <a href="{{ request('from') === 'sales' ? route('pengajuan_dana.monitoring_all', ['from' => 'sales']) : route('pengajuan_dana.monitoring_all') }}" class="text-sm text-emerald-700 font-bold hover:underline flex items-center justify-center gap-2 bg-emerald-50 px-5 py-2.5 rounded-full border border-emerald-200 transition-all hover:bg-emerald-100 shadow-sm w-full">
                        <i class="fas fa-desktop"></i> Monitoring Seluruh Pengajuan
                    </a>
                    @endif
                    <a href="{{ request('from') === 'sales' ? route('pengajuan_dana.history', ['from' => 'sales']) : route('pengajuan_dana.history') }}" class="text-sm text-blue-600 font-bold hover:underline flex items-center justify-center gap-2 bg-blue-50 px-5 py-2.5 rounded-full border border-blue-100 transition-all hover:bg-blue-100 shadow-sm w-full">
                        <i class="fas fa-history"></i> Lihat Riwayat Pengajuan
                    </a>
                </div>
                @endif

                <form action="{{ route('pengajuan_dana.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 md:space-y-6 m-0">
                    @csrf
                    @if(request('from') === 'sales')
                    <input type="hidden" name="from" value="sales">
                    @endif

                    {{-- 1. INFORMASI PEMOHON --}}
                    <x-ui.glass-card>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/60 pb-3 md:pb-4 mb-3 md:mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-user-tie"></i></div>
                                <div>
                                    <h3 class="text-lg font-black text-slate-800">1. Informasi Pemohon</h3>
                                    @if(!$isMobile)
                                    <p class="text-xs text-slate-500 font-semibold" style="margin-top: 0; margin-bottom: 0;">Detail data karyawan yang mengajukan dana</p>
                                    @endif
                                </div>
                            </div>
                            @if(!$isMobile)
                            <div class="w-full flex justify-end gap-2 sm:w-auto">
                                @if($canMonitor)
                                <a href="{{ request('from') === 'sales' ? route('pengajuan_dana.monitoring_all', ['from' => 'sales']) : route('pengajuan_dana.monitoring_all') }}" class="text-xs text-emerald-700 font-bold hover:underline flex items-center gap-1.5 bg-emerald-50 px-4 py-2 rounded-full border border-emerald-200 transition-all hover:bg-emerald-100 shadow-sm w-fit">
                                    <i class="fas fa-desktop"></i> Monitoring Seluruh Pengajuan
                                </a>
                                @endif
                                <a href="{{ request('from') === 'sales' ? route('pengajuan_dana.history', ['from' => 'sales']) : route('pengajuan_dana.history') }}" class="text-xs text-blue-600 font-bold hover:underline flex items-center gap-1.5 bg-blue-50 px-4 py-2 rounded-full border border-blue-100 transition-all hover:bg-blue-100 shadow-sm w-fit">
                                    <i class="fas fa-history"></i> Lihat Riwayat Pengajuan
                                </a>
                            </div>
                            @endif
                        </div>

                        <input type="hidden" name="nama_pemohon" value="{{ Auth::user()->name }}">
                        <input type="hidden" name="divisi" value="{{ Auth::user()->divisi ?? 'Umum' }}">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-5">
                            <div class="col-span-1 md:col-span-2">
                                <label class="ui-label" for="judul-pengajuan">Judul Pengajuan <span class="text-red-500">*</span></label>
                                <input type="text" id="judul-pengajuan" name="judul_pengajuan" class="ui-input" placeholder="Contoh: Pembelian Perlengkapan Kantor & ATK" value="{{ old('judul_pengajuan') }}" required>
                            </div>
                            <div class="col-span-1">
                                <label class="ui-label">Tanggal Pengajuan</label>
                                <input type="text" class="ui-input !bg-slate-100 !text-slate-500 cursor-not-allowed" value="{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}" readonly tabindex="-1">
                                <input type="hidden" name="tanggal_pengajuan" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </x-ui.glass-card>

                    {{-- 2. INFORMASI REKENING TRANSFER --}}
                    <x-ui.glass-card>
                        <div class="form-section-header">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-university"></i></div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800">2. Tujuan Transfer Rekening</h4>
                                @if(!$isMobile)
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 0; margin-bottom: 0;">Informasi rekening untuk proses pencairan dana</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-5">
                            <div class="col-span-1">
                                <label class="ui-label" for="pilih-bank">Nama Bank <span class="text-red-500">*</span></label>
                                <select id="pilih-bank" name="nama_bank" class="ui-input !bg-white" required>
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
                                    <input type="text" id="input-bank-lainnya" name="nama_bank_lainnya" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="Nama Bank Lainnya" value="{{ old('nama_bank_lainnya') }}" {{ old('nama_bank') == 'other' ? 'required' : '' }}>
                                </div>
                            </div>
                            <div class="col-span-1">
                                <label class="ui-label" for="no-rekening">Nomor Rekening <span class="text-red-500">*</span></label>
                                <input type="number" id="no-rekening" name="no_rekening" class="ui-input !bg-white !text-slate-800" placeholder="Contoh: 1234567890" value="{{ old('no_rekening') }}" required>
                            </div>
                            <div class="col-span-1">
                                <label class="ui-label" for="nama-rek">Atas Nama (A/N) <span class="text-red-500">*</span></label>
                                <input type="text" id="nama-rek" name="nama_rek" class="ui-input !bg-white !text-slate-800" placeholder="Nama Pemilik Rekening" value="{{ old('nama_rek') }}" required>
                            </div>
                        </div>
                    </x-ui.glass-card>

                    {{-- 3. RINCIAN DANA PER TIPE --}}
                    <x-ui.glass-card>
                        <div class="form-section-header">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-list-ol"></i></div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800">3. Rincian Kebutuhan Dana</h4>
                                @if(!$isMobile)
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 0; margin-bottom: 0;">Tambahkan deskripsi barang/jasa beserta nominal estimasi untuk tiap tipe yang dipilih</p>
                                @endif
                            </div>
                        </div>

                        <div class="text-[10px] font-bold text-amber-600 mb-2">Batas Entertain: maksimal Rp {{ $batasEntertainFormatted }} per pengajuan</div>
                        <div id="warning-ENTERTAIN" class="hidden text-[10px] font-black text-red-600 mb-2">Melebihi batas Rp {{ $batasEntertainFormatted }} — kurangi item/nominal sebelum kirim.</div>

                        <div class="rounded-xl border border-slate-200 bg-white mb-3" style="overflow:hidden;">
                            <div class="dana-items-wrap">
                                <table class="w-full text-xs" style="min-width:620px;">
                                    <thead>
                                        <tr class="bg-slate-50/80 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                                            <th class="px-3 py-2 text-left font-bold" style="width:150px;">Tipe</th>
                                            <th class="px-3 py-2 text-left font-bold">Deskripsi Item</th>
                                            <th class="px-3 py-2 text-right font-bold" style="width:170px;">Jumlah Estimasi (Rp)</th>
                                            <th class="px-3 py-2 text-center font-bold" style="width:76px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rincian-dana-body" class="divide-y divide-slate-100"></tbody>
                                </table>
                            </div>
                        </div>

                        <button type="button" id="tambah-item-btn" class="w-full justify-center bg-blue-600 hover:bg-blue-700 text-white font-black py-2.5 px-4 rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm">
                            <i class="fas fa-plus"></i> Tambah Item
                        </button>
                        <div class="mt-2 flex items-center justify-end">
                            <span id="draft-status" class="text-[11px] font-bold text-slate-400">Draft tersimpan otomatis di perangkat ini</span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-1.5">
                            @foreach(\App\Models\PengajuanDana::TIPE_LIST as $tKey => $tLabel)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-50 text-teal-700 border border-teal-200">
                                {{ $tLabel }}: <span class="font-mono">Rp <span id="total-dana-display-{{ $tKey }}">0</span></span>
                            </span>
                            @endforeach
                        </div>

                        <div class="mt-5 pt-4 border-t border-slate-200/80 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
                            <span class="text-xs text-blue-700 font-extrabold uppercase tracking-wider">Total Estimasi Keseluruhan:</span>
                            <div class="text-left sm:text-right">
                                <span class="text-blue-700 font-black text-xl sm:text-2xl font-mono">Rp <span id="total-dana-display-grand">0</span></span>
                            </div>
                        </div>
                    </x-ui.glass-card>

                    {{-- 4. FILE PENDUKUNG --}}
                    <x-ui.glass-card>
                        <div class="form-section-header">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-paperclip"></i></div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800">4. File Pendukung (Opsional)</h4>
                                @if(!$isMobile)
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 0; margin-bottom: 0;">Lampirkan file nota, invoice, proposal, dll. jika ada</p>
                                @endif
                            </div>
                        </div>

                        {{-- Info Banner Format Dokumen --}}
                        <div class="bg-blue-50/50 border border-blue-100 p-3 md:p-3.5 rounded-xl mb-3 md:mb-4 flex gap-3">
                            <i class="fas fa-info-circle text-blue-600 text-sm mt-0.5"></i>
                            <div class="text-[11px] text-blue-800 leading-normal font-semibold">
                                Format file yang didukung: <span class="text-blue-900 font-black">JPG, JPEG, PNG, PDF, DOC, DOCX, XLS, XLSX</span>. Ukuran berkas maksimal <span class="text-blue-900 font-black">10MB</span> per file.
                            </div>
                        </div>

                        <div id="file-pendukung-container" class="space-y-3"></div>
                        <button id="tambah-lampiran-btn" type="button" class="mt-3 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 font-black py-2 px-4 rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm">
                            <i class="fas fa-plus"></i> Tambah File Lampiran
                        </button>
                    </x-ui.glass-card>

                    {{-- SUBMIT BUTTONS --}}
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                        <x-ui.button type="button" id="reset-form-btn" variant="secondary" size="md" class="w-full sm:w-auto">Reset Formulir</x-ui.button>
                        <x-ui.button type="submit" id="submit-button" variant="primary" size="md" icon="fa-paper-plane" class="w-full sm:w-auto" style="background:linear-gradient(135deg, #1d4ed8, #3b82f6);">Ajukan Dana Sekarang</x-ui.button>
                    </div>
                </form>
            </div>

            {{-- Modal Tambah/Edit Item Rincian (dipakai semua tipe) --}}
            <x-ui.modal id="dana-item-modal" maxWidth="max-w-md" title="Tambah Item" subtitle="Isi deskripsi dan nominal estimasi, lalu simpan ke tabel." icon="fa-list-ol" iconClass="bg-blue-600 text-white">
                <div>
                    <label class="ui-label" for="m-item-tipe-select">Tipe Pengajuan <span class="text-red-500">*</span></label>
                    <select id="m-item-tipe-select" class="ui-input ui-input--sm !bg-white !text-slate-800"></select>
                </div>
                <div>
                    <label class="ui-label" for="m-item-deskripsi">Deskripsi <span class="text-red-500">*</span></label>
                    <input type="text" id="m-item-deskripsi" class="ui-input ui-input--sm !bg-white !text-slate-800" placeholder="Contoh: Belanja ATK bulanan" autocomplete="off">
                </div>
                <div>
                    <label class="ui-label" for="m-item-jumlah">Jumlah Estimasi (Rp) <span class="text-red-500">*</span></label>
                    <div class="input-wrapper-rp">
                        <span class="label-rp">Rp</span>
                        <input type="text" id="m-item-jumlah" class="ui-input ui-input--sm !bg-white !text-slate-800 input-with-rp" placeholder="0" inputmode="numeric" autocomplete="off">
                    </div>
                    <p id="m-item-entertain-hint" class="hidden text-[10px] font-bold text-amber-600 mt-1">Maksimal Rp {{ $batasEntertainFormatted }} per pengajuan Entertain</p>
                </div>
                <p id="m-item-error" class="hidden text-[11px] font-bold text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2.5"></p>
                <x-slot:footer>
                    <x-ui.button variant="secondary" size="sm" data-modal-close>Batal</x-ui.button>
                    <x-ui.button variant="primary" size="sm" icon="fa-check" id="m-item-save">Simpan ke Tabel</x-ui.button>
                </x-slot:footer>
            </x-ui.modal>
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
        
        const grandTotalDisplay = document.getElementById('total-dana-display-grand');

        const TIPE_LABELS = { UMUM: 'Umum', ATK: 'ATK', UP_COUNTRY: 'Up Country', ENTERTAIN: 'Entertain' };
        const BULAN_INI = new Date().toLocaleString('id-ID', { month: 'long' });
        function defaultDeskripsi(tipe) {
            if (tipe === 'UMUM') return '';
            return `${TIPE_LABELS[tipe] || tipe} ${BULAN_INI}`;
        }

        // Tipe tidak lagi dipilih di card 1 — semua tipe tersedia di dropdown modal.
        function getSelectedTipes() {
            return Object.keys(TIPE_LABELS);
        }

        // Ambil tipe dari nama hidden input rincian[TIPE][...]
        function tipeOfInput(input) {
            const m = (input.name || '').match(/^rincian\[([A-Z_]+)\]/);
            return m ? m[1] : null;
        }

        // Tipe yang benar-benar punya baris item (dipakai judul otomatis & submit)
        function tipesWithRows() {
            const ada = [];
            document.querySelectorAll('#rincian-dana-body input.jumlah-input').forEach(input => {
                const t = tipeOfInput(input);
                if (t && !ada.includes(t)) ada.push(t);
            });
            return Object.keys(TIPE_LABELS).filter(t => ada.includes(t));
        }

        const BATAS_ENTERTAIN = {{ (int) $batasEntertain }};

        // ===== Auto-save draft ala pengajuan barang (localStorage, per-user) =====
        const DRAFT_KEY = 'pengajuan_dana_draft_{{ Auth::id() }}';
        try {
            if (DRAFT_KEY !== 'pengajuan_dana_draft') localStorage.removeItem('pengajuan_dana_draft');
        } catch (e) {}
        let isRestoring = false;
        let isSubmitting = false;

        function updateDraftStatus(text) {
            const el = document.getElementById('draft-status');
            if (el) el.textContent = text;
        }

        function collectDanaDraft() {
            const items = [];
            document.querySelectorAll('#rincian-dana-body tr.row-dana').forEach(tr => {
                const d = getRowData(tr);
                items.push({ tipe: d.tipe, deskripsi: d.deskripsi, jumlah: d.jumlah });
            });
            const bankSel = document.getElementById('pilih-bank');
            const bankLain = document.getElementById('input-bank-lainnya');
            const noRek = document.getElementById('no-rekening');
            const namaRek = document.getElementById('nama-rek');
            return {
                v: 1,
                judul_pengajuan: judulInput ? judulInput.value : '',
                nama_bank: bankSel ? bankSel.value : '',
                nama_bank_lainnya: bankLain ? bankLain.value : '',
                no_rekening: noRek ? noRek.value : '',
                nama_rek: namaRek ? namaRek.value : '',
                lastUsedTipe: lastUsedTipe,
                items: items,
                saved_at: new Date().toISOString(),
            };
        }

        function saveDanaDraft() {
            if (isRestoring) return;
            try {
                localStorage.setItem(DRAFT_KEY, JSON.stringify(collectDanaDraft()));
                const t = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                updateDraftStatus('Draft tersimpan otomatis • ' + t);
            } catch (e) { /* storage penuh / private mode, abaikan */ }
        }

        function readDanaDraft() {
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

        function clearDanaDraft() {
            try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
            updateDraftStatus('Draft tersimpan otomatis di perangkat ini');
        }

        function entertainSubtotal() {
            let s = 0;
            document.querySelectorAll('input[name="rincian[ENTERTAIN][jumlah][]"]').forEach(input => {
                s += parseInt(String(input.value).replace(/[^0-9]/g, '')) || 0;
            });
            return s;
        }

        function updateTotal() {
            const subs = { UMUM: 0, ATK: 0, UP_COUNTRY: 0, ENTERTAIN: 0 };
            let grand = 0;
            document.querySelectorAll('#rincian-dana-body input.jumlah-input').forEach(input => {
                const t = tipeOfInput(input);
                const v = parseInt(String(input.value).replace(/[^0-9]/g, '')) || 0;
                if (t && subs.hasOwnProperty(t)) subs[t] += v;
                grand += v;
            });
            Object.keys(subs).forEach(tipe => {
                const el = document.getElementById(`total-dana-display-${tipe}`);
                if (el) {
                    el.textContent = subs[tipe].toLocaleString('id-ID');
                    el.classList.toggle('!text-red-600', tipe === 'ENTERTAIN' && subs[tipe] > BATAS_ENTERTAIN);
                }
            });
            if (grandTotalDisplay) grandTotalDisplay.textContent = grand.toLocaleString('id-ID');
            const warn = document.getElementById('warning-ENTERTAIN');
            if (warn) warn.classList.toggle('hidden', !(entertainSubtotal() > BATAS_ENTERTAIN));
        }

        function isEntertainOverLimit() {
            return entertainSubtotal() > BATAS_ENTERTAIN;
        }

        function formatCurrency(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            input.value = value ? parseInt(value).toLocaleString('id-ID') : '';
        }

        function fmtRp(num) {
            return (parseInt(num) || 0).toLocaleString('id-ID');
        }

        function refreshEmptyState() {
            const body = document.getElementById('rincian-dana-body');
            if (!body) return;
            const hasRows = body.querySelector('tr.row-dana') !== null;
            let emptyRow = body.querySelector('tr.empty-state');
            if (!hasRows && !emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.className = 'empty-state';
                emptyRow.innerHTML = '<td colspan="4" class="px-3 py-5 text-center text-slate-400 text-[11px] font-bold">Belum ada item — klik <span class="text-blue-600">Tambah Item</span> di bawah.</td>';
                body.appendChild(emptyRow);
            } else if (hasRows && emptyRow) {
                emptyRow.remove();
            }
        }

        function addRow(tipe, deskripsi = '', jumlah = '') {
            const body = document.getElementById('rincian-dana-body');
            if (!body) return null;
            const cleanJumlah = parseInt(String(jumlah).replace(/[^0-9]/g, '')) || 0;
            const tr = document.createElement('tr');
            tr.className = 'row-dana hover:bg-blue-50/50 transition-colors bg-white';
            tr.innerHTML =
                '<td class="px-3 py-2 align-top" style="width:150px;">' +
                    '<span class="tipe-badge inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-50 text-teal-700 border border-teal-200 whitespace-nowrap"></span>' +
                    `<input type="hidden" name="rincian[${tipe}][deskripsi][]" class="desc-input">` +
                    `<input type="hidden" name="rincian[${tipe}][jumlah][]" value="${cleanJumlah}" class="jumlah-input">` +
                '</td>' +
                '<td class="px-3 py-2 align-top">' +
                    '<span class="desc-text block text-xs font-bold text-slate-800 leading-normal"></span>' +
                '</td>' +
                '<td class="px-3 py-2 align-top text-right" style="width:170px;">' +
                    '<span class="amount-text block font-mono font-black text-slate-700 text-xs"></span>' +
                '</td>' +
                '<td class="px-3 py-2 align-top text-center" style="width:76px;">' +
                    '<div class="flex items-center justify-center gap-1">' +
                        '<button type="button" class="edit-row-btn ui-btn ui-btn--edit" title="Ubah item"><i class="fas fa-pen text-[11px]"></i></button>' +
                        '<button type="button" class="delete-row-btn ui-btn ui-btn--delete" title="Hapus item"><i class="fas fa-trash-can text-[11px]"></i></button>' +
                    '</div>' +
                '</td>';
            body.appendChild(tr);
            setRowData(tr, tipe, deskripsi, cleanJumlah);
            tr.querySelector('.edit-row-btn').addEventListener('click', () => openItemModal(tr.dataset.tipe, tr));
            tr.querySelector('.delete-row-btn').addEventListener('click', () => { tr.remove(); refreshEmptyState(); updateTotal(); saveDanaDraft(); });
            refreshEmptyState();
            updateTotal();
            saveDanaDraft();
            return tr;
        }

        function setRowData(tr, tipe, deskripsi, jumlah) {
            const cleanJumlah = parseInt(jumlah) || 0;
            tr.dataset.tipe = tipe;
            const badge = tr.querySelector('.tipe-badge');
            const descText = tr.querySelector('.desc-text');
            const descInput = tr.querySelector('.desc-input');
            const amountText = tr.querySelector('.amount-text');
            const jumlahInput = tr.querySelector('.jumlah-input');
            badge.textContent = TIPE_LABELS[tipe] || tipe;
            descInput.name = `rincian[${tipe}][deskripsi][]`;
            jumlahInput.name = `rincian[${tipe}][jumlah][]`;
            descText.textContent = String(deskripsi).trim() === '' ? '-' : deskripsi;
            descInput.value = deskripsi;
            jumlahInput.value = cleanJumlah;
            amountText.textContent = 'Rp ' + fmtRp(cleanJumlah);
        }

        function getRowData(tr) {
            return {
                tipe: tr.dataset.tipe || tipeOfInput(tr.querySelector('.jumlah-input')),
                deskripsi: tr.querySelector('.desc-input').value,
                jumlah: parseInt(tr.querySelector('.jumlah-input').value) || 0,
            };
        }

        // ===== Modal Tambah/Edit Item (ala sales-item-modal di manage) =====
        const itemModalTitle = document.querySelector('#dana-item-modal-title');
        const itemTipeSelect = document.getElementById('m-item-tipe-select');
        const itemDeskripsi = document.getElementById('m-item-deskripsi');
        const itemJumlah = document.getElementById('m-item-jumlah');
        const itemError = document.getElementById('m-item-error');
        const itemEntertainHint = document.getElementById('m-item-entertain-hint');
        const itemSaveBtn = document.getElementById('m-item-save');
        const tambahItemBtn = document.getElementById('tambah-item-btn');
        let itemModalTipe = 'UMUM';
        let itemModalOriginTipe = 'UMUM';
        let itemModalEditRow = null;
        let lastUsedTipe = 'UMUM';

        function refreshTipeOptions(selected) {
            const keep = itemTipeSelect.value;
            itemTipeSelect.innerHTML = '';
            getSelectedTipes().forEach(t => {
                const opt = document.createElement('option');
                opt.value = t;
                opt.textContent = TIPE_LABELS[t] || t;
                itemTipeSelect.appendChild(opt);
            });
            if (selected && getSelectedTipes().includes(selected)) {
                itemTipeSelect.value = selected;
            } else if (keep && getSelectedTipes().includes(keep)) {
                itemTipeSelect.value = keep;
            }
            itemModalTipe = itemTipeSelect.value;
        }

        function syncModalTipeUI() {
            const tipe = itemTipeSelect.value;
            itemModalTipe = tipe;
            const label = TIPE_LABELS[tipe] || tipe;
            if (itemModalTitle) itemModalTitle.textContent = (itemModalEditRow ? 'Ubah Item ' : 'Tambah Item ') + label;
            if (itemEntertainHint) itemEntertainHint.classList.toggle('hidden', tipe !== 'ENTERTAIN');
        }

        function showItemError(msg) {
            if (!msg) { itemError.classList.add('hidden'); itemError.textContent = ''; return; }
            itemError.textContent = msg;
            itemError.classList.remove('hidden');
        }

        function openItemModal(tipe, editRow = null) {
            itemModalEditRow = editRow || null;
            refreshTipeOptions(tipe);
            itemModalOriginTipe = itemTipeSelect.value;
            if (editRow) {
                const d = getRowData(editRow);
                itemDeskripsi.value = d.deskripsi;
                itemJumlah.value = d.jumlah ? fmtRp(d.jumlah) : '';
            } else {
                // Tambah baru: form selalu dikosongkan, tidak membawa sisa isian sebelumnya
                itemDeskripsi.value = '';
                itemJumlah.value = '';
            }
            syncModalTipeUI();
            showItemError('');
            if (typeof uiModalOpen === 'function') uiModalOpen('dana-item-modal');
            else { const m = document.getElementById('dana-item-modal'); if (m) { m.classList.remove('hidden'); m.classList.add('flex'); } }
            setTimeout(() => itemDeskripsi.focus(), 220);
        }

        function closeItemModal() {
            if (typeof uiModalClose === 'function') uiModalClose('dana-item-modal');
            else { const m = document.getElementById('dana-item-modal'); if (m) { m.classList.add('hidden'); m.classList.remove('flex'); } }
        }

        itemJumlah.addEventListener('input', () => formatCurrency(itemJumlah));

        function saveItemModal() {
            const tipe = itemTipeSelect.value;
            const deskripsi = itemDeskripsi.value.trim();
            const jumlah = parseInt(itemJumlah.value.replace(/[^0-9]/g, '')) || 0;
            if (deskripsi === '') { showItemError('Deskripsi item wajib diisi.'); itemDeskripsi.focus(); return; }
            if (jumlah < 1) { showItemError('Nominal item minimal Rp 1.'); itemJumlah.focus(); return; }
            if (tipe === 'ENTERTAIN') {
                let subLain = 0;
                document.querySelectorAll('input[name="rincian[ENTERTAIN][jumlah][]"]').forEach(input => {
                    if (input.disabled) return;
                    if (itemModalEditRow && input.closest('tr') === itemModalEditRow && itemModalOriginTipe === 'ENTERTAIN') return;
                    subLain += parseInt(input.value) || 0;
                });
                if (subLain + jumlah > BATAS_ENTERTAIN) {
                    showItemError('Melebihi batas Rp {{ $batasEntertainFormatted }} untuk Entertain — kurangi nominalnya.');
                    itemJumlah.focus();
                    return;
                }
            }
            if (itemModalEditRow) {
                setRowData(itemModalEditRow, tipe, deskripsi, jumlah);
            } else {
                addRow(tipe, deskripsi, jumlah);
            }
            lastUsedTipe = tipe;
            updateTotal();
            saveDanaDraft();
            closeItemModal();
        }

        itemTipeSelect.addEventListener('change', syncModalTipeUI);

        itemSaveBtn.addEventListener('click', saveItemModal);
        itemDeskripsi.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); itemJumlah.focus(); } });
        itemJumlah.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); saveItemModal(); } });

        function syncTipeBlocks() {
            // Semua blok tipe selalu tampil; tipe dipilih lewat dropdown modal.
            updateTotal();
        }

        const judulInput = document.getElementById('judul-pengajuan');
        let judulAuto = !judulInput || judulInput.value.trim() === '';
        if (judulInput) {
            judulInput.addEventListener('input', () => { judulAuto = judulInput.value.trim() === ''; });
        }
        function autoJudul() {
            const ada = tipesWithRows();
            if (ada.length === 0 || (ada.length === 1 && ada[0] === 'UMUM')) return 'Pengajuan Dana Umum';
            const names = { ATK: 'ATK', UP_COUNTRY: 'Up Country', ENTERTAIN: 'Entertain' };
            return 'Pengajuan ' + ada.filter(t => t !== 'UMUM').map(t => names[t] || t).join(' + ');
        }
        function maybeAutofillJudul() {
            if (judulInput && (judulAuto || judulInput.value.trim() === '')) {
                judulInput.value = autoJudul();
                judulAuto = true;
            }
        }

        if (tambahItemBtn) {
            tambahItemBtn.addEventListener('click', () => {
                openItemModal(lastUsedTipe);
            });
        }

        const oldRincian = @json(old('rincian', []));
        const oldLegacyDeskripsi = @json(old('rincian_deskripsi', []));
        const oldLegacyJumlah = @json(old('rincian_jumlah', []));

        (function initRincian() {
            isRestoring = true;
            const selected = getSelectedTipes();
            let restored = false;
            if (oldRincian && typeof oldRincian === 'object') {
                Object.keys(oldRincian).forEach(tipe => {
                    const rows = oldRincian[tipe] || {};
                    const descs = rows.deskripsi || [];
                    const jmls = rows.jumlah || [];
                    if (selected.includes(tipe) && descs.length > 0) {
                        restored = true;
                        descs.forEach((d, i) => addRow(tipe, d, jmls[i] !== undefined ? jmls[i] : ''));
                    }
                });
            }
            if (!restored && oldLegacyDeskripsi && oldLegacyDeskripsi.length > 0) {
                restored = true;
                oldLegacyDeskripsi.forEach((d, i) => addRow('UMUM', d, oldLegacyJumlah[i] !== undefined ? oldLegacyJumlah[i] : ''));
            }
            if (!restored) {
                // Prioritas 3: draft localStorage (tahan refresh / pindah halaman)
                const draft = readDanaDraft();
                const hasDraftContent = draft && (
                    (draft.judul_pengajuan && draft.judul_pengajuan.trim() !== '') ||
                    (draft.nama_bank && draft.nama_bank !== '') ||
                    (draft.no_rekening && String(draft.no_rekening).trim() !== '') ||
                    (draft.nama_rek && draft.nama_rek.trim() !== '') ||
                    (draft.items || []).some(it => (it.deskripsi && String(it.deskripsi).trim() !== '') || (parseInt(it.jumlah) || 0) > 0)
                );
                if (hasDraftContent) {
                    if (judulInput && draft.judul_pengajuan) {
                        judulInput.value = draft.judul_pengajuan;
                        judulAuto = false;
                    }
                    const bankSel = document.getElementById('pilih-bank');
                    if (bankSel && draft.nama_bank) {
                        bankSel.value = draft.nama_bank;
                        bankSel.dispatchEvent(new Event('change'));
                    }
                    const bankLain = document.getElementById('input-bank-lainnya');
                    if (bankLain && draft.nama_bank_lainnya) bankLain.value = draft.nama_bank_lainnya;
                    const noRek = document.getElementById('no-rekening');
                    if (noRek && draft.no_rekening) noRek.value = draft.no_rekening;
                    const namaRek = document.getElementById('nama-rek');
                    if (namaRek && draft.nama_rek) namaRek.value = draft.nama_rek;
                    if (draft.lastUsedTipe && selected.includes(draft.lastUsedTipe)) lastUsedTipe = draft.lastUsedTipe;
                    (draft.items || []).forEach(it => {
                        if ((it.deskripsi && String(it.deskripsi).trim() !== '') || (parseInt(it.jumlah) || 0) > 0) {
                            addRow(selected.includes(it.tipe) ? it.tipe : 'UMUM', it.deskripsi || '', it.jumlah || 0);
                        }
                    });
                    restored = true;
                    updateDraftStatus('Draft dipulihkan otomatis');
                }
            }
            if (!restored) {
                refreshEmptyState();
            }
            isRestoring = false;
            syncTipeBlocks();
            maybeAutofillJudul();
            saveDanaDraft();
        })();

        const tambahLampiranBtn = document.getElementById('tambah-lampiran-btn');
        const lampiranContainer = document.getElementById('file-pendukung-container');
        const mainForm = document.querySelector('form[action="{{ route('pengajuan_dana.store') }}"]');
        const submitButton = document.getElementById('submit-button');

        // Setiap ketikan di field utama ikut menyimpan draft
        if (judulInput) {
            judulInput.addEventListener('input', saveDanaDraft);
            judulInput.addEventListener('change', saveDanaDraft);
        }
        ['pilih-bank', 'input-bank-lainnya', 'no-rekening', 'nama-rek'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', saveDanaDraft);
            el.addEventListener('change', saveDanaDraft);
        });
 
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
                        <input type="file" name="file_pendukung[]" id="${uniqueId}" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer cursor-pointer font-semibold" />
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
            
            const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const fileName = file.name;
                    const ext = fileName.split('.').pop().toLowerCase();

                    if (!allowedExtensions.includes(ext)) {
                        alert('Format berkas "' + fileName + '" tidak didukung. Harap upload berkas dengan format: JPG, JPEG, PNG, PDF, DOC, DOCX, XLS, atau XLSX.');
                        this.value = '';
                        progressWrapper.classList.add('hidden');
                        return;
                    }

                    const maxBytesPerFile = 10 * 1024 * 1024; // 10MB
                    if (file.size > maxBytesPerFile) {
                        alert('Ukuran berkas "' + fileName + '" (' + (file.size / (1024 * 1024)).toFixed(2) + 'MB) melebihi batas maksimal 10MB per file. Berkas dibatalkan.');
                        this.value = '';
                        progressWrapper.classList.add('hidden');
                        return;
                    }

                    fileNameSpan.textContent = fileName;
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
            updateTotal();
            // Minimal 1 item rincian secara keseluruhan
            const adaTipe = tipesWithRows();
            if (adaTipe.length === 0) {
                e.preventDefault();
                alert('Belum ada item rincian. Klik tombol "Tambah Item" dulu ya.');
                return false;
            }
            // Tipe Umum tidak bisa digabung tipe Expense
            if (adaTipe.includes('UMUM') && adaTipe.length > 1) {
                e.preventDefault();
                alert('Tipe Umum tidak bisa digabung dengan tipe Expense (ATK / Up Country / Entertain). Hapus salah satunya dulu ya.');
                return false;
            }
            // Pastikan tiap baris lengkap
            const allRows = document.querySelectorAll('#rincian-dana-body tr.row-dana');
            for (const tr of allRows) {
                const d = getRowData(tr);
                const label = TIPE_LABELS[d.tipe] || d.tipe;
                if (!String(d.deskripsi).trim() || d.jumlah < 1) {
                    e.preventDefault();
                    alert(`Ada item ${label} yang belum lengkap. Lengkapi dulu ya.`);
                    openItemModal(d.tipe, tr);
                    return false;
                }
            }
            // Kirim daftar tipe yang dipakai (pengganti pills yang dihapus)
            mainForm.querySelectorAll('input[name="tipe_expend[]"]').forEach(i => i.remove());
            adaTipe.forEach(t => {
                const h = document.createElement('input');
                h.type = 'hidden';
                h.name = 'tipe_expend[]';
                h.value = t;
                mainForm.appendChild(h);
            });
            if (isEntertainOverLimit()) {
                e.preventDefault();
                alert('Total pengajuan Entertain maksimal Rp {{ $batasEntertainFormatted }}. Kurangi item/nominalnya sebelum kirim.');
                return false;
            }
            let invalidExt = false;
            let tooLarge = false;
            const maxPerFile = 10 * 1024 * 1024; // 10MB per file
            const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

            document.querySelectorAll('input[type="file"][name="file_pendukung[]"]').forEach(input => {
                if (input.files && input.files.length > 0) {
                    for (let i = 0; i < input.files.length; i++) {
                        const file = input.files[i];
                        const ext = file.name.split('.').pop().toLowerCase();
                        if (!allowedExtensions.includes(ext)) {
                            invalidExt = true;
                        }
                        if (file.size > maxPerFile) {
                            tooLarge = true;
                        }
                    }
                }
            });

            if (invalidExt) {
                e.preventDefault();
                alert('Terdapat berkas lampiran dengan format yang tidak didukung. Harap gunakan format: JPG, JPEG, PNG, PDF, DOC, DOCX, XLS, atau XLSX.');
                return false;
            }

            if (tooLarge) {
                e.preventDefault();
                alert('Ukuran salah satu berkas lampiran melebihi batas maksimal 10MB per file. Silakan pilih berkas yang lebih kecil.');
                return false;
            }

            document.querySelectorAll('input[type="file"][name="file_pendukung[]"]').forEach(input => {
                if (!input.files || input.files.length === 0) {
                    input.disabled = true;
                }
            });

            document.querySelectorAll('input[type="file"][name="file_pendukung[]"]').forEach(input => {
                if (!input.disabled && input.files && input.files.length > 0) {
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
            isSubmitting = true;
        });

        // Flush terakhir anti mati-lampu: sukses navigasi -> draft dibersihkan,
        // selain itu (refresh/tutup/gagal) -> draft disimpan ulang.
        window.addEventListener('pagehide', function () {
            if (isSubmitting) clearDanaDraft();
            else saveDanaDraft();
        });
        
        const resetBtn = document.getElementById('reset-form-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                mainForm.reset();
                document.getElementById('rincian-dana-body').innerHTML = '';
                judulAuto = true;
                lastUsedTipe = 'UMUM';
                maybeAutofillJudul();
                refreshEmptyState();
                if (lampiranContainer) lampiranContainer.innerHTML = '';
                addLampiranInput();
                syncTipeBlocks();
                document.getElementById('pilih-bank').dispatchEvent(new Event('change'));
                clearDanaDraft();
            });
        }
    });
    </script>
    @endpush
</x-layout-users>