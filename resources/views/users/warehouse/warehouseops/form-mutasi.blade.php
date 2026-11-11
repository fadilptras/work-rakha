{{-- Form input Barang Masuk / Keluar tab Warehouse Ops (inline, tanpa partial) --}}
<div class="w-full flex-1 min-h-0 flex flex-col space-y-3">
    {{-- Pengingat opname: menetap sampai semua SKU diopname bulan ini --}}
    @if(($belumOpnameOps ?? 0) > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-4 py-2.5 flex flex-col sm:flex-row sm:items-center gap-2 shadow-sm shrink-0" role="alert">
        <div class="flex items-center gap-2 text-amber-800 text-xs font-bold">
            <i class="fas fa-bell"></i>
            <span>{{ number_format($belumOpnameOps, 0, ',', '.') }} dari {{ number_format($totalAktifOps ?? 0, 0, ',', '.') }} SKU belum diopname bulan {{ $namaBulanOps ?? '' }}. Stok awal kartu memakai data terakhir — lakukan opname agar akurat.</span>
        </div>
        <a href="{{ route('warehouse.stockopname.index') }}" class="shrink-0 inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold px-3 py-1.5 rounded-xl transition shadow-sm sm:ml-auto">Buka halaman Opname <i class="fas fa-arrow-right text-[10px]"></i></a>
    </div>
    @else
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-2 text-emerald-700 text-xs font-bold shadow-sm shrink-0"><i class="fas fa-check-circle mr-1"></i>Semua SKU sudah diopname bulan {{ $namaBulanOps ?? 'ini' }}.</div>
    @endif
    {{-- Toast pengingat opname: sekali sehari per browser --}}
    <div id="opsToast" class="hidden fixed bottom-6 right-6 z-[9999] w-[340px] max-w-[85vw] bg-slate-900/90 backdrop-blur border border-white/10 shadow-2xl rounded-2xl p-4 flex items-start gap-3" role="alert">
        <div class="w-8 h-8 shrink-0 rounded-lg bg-amber-500/30 border border-amber-300/30 flex items-center justify-center"><i class="fas fa-bell text-amber-200 text-sm"></i></div>
        <div class="flex-1 min-w-0">
            <p class="text-[13px] font-black text-white uppercase tracking-widest">Pengingat Opname</p>
            <p class="text-xs font-medium text-slate-200 leading-snug mt-0.5"><span data-ops-toast-n>0</span> SKU belum diopname bulan {{ $namaBulanOps ?? 'ini' }}. Input keluar/masuk tetap bisa — tapi segera isi opname ya.</p>
        </div>
        <button type="button" id="opsToastClose" class="shrink-0 text-slate-400 hover:text-white transition-colors p-1" aria-label="Tutup"><i class="fas fa-times text-xs"></i></button>
    </div>
    <script>
        (function () {
            var BELUM = {{ (int) ($belumOpnameOps ?? 0) }};
            if (!BELUM) return;
            var key = 'ops_opname_remind_{{ now()->format('Y-m') }}';
            try {
                if (sessionStorage.getItem(key)) return;
                sessionStorage.setItem(key, '1');
            } catch (e) { return; }
            var el = document.getElementById('opsToast');
            if (!el) return;
            var n = el.querySelector('[data-ops-toast-n]');
            if (n) n.textContent = BELUM;
            el.classList.remove('hidden');
            setTimeout(function () { el.classList.add('hidden'); }, 9000);
            var x = document.getElementById('opsToastClose');
            if (x) x.addEventListener('click', function () { el.classList.add('hidden'); });
        })();
    </script>
    @if(!$canManage)
        <x-ui.glass-card class="border-t-4 border-t-red-400 text-sm text-slate-600">Akun Anda tidak memiliki akses input barang. Hubungi pengelola stok.</x-ui.glass-card>
    @else
    <x-ui.glass-card class="w-full flex-1 min-h-0 flex flex-col border-t-4 border-t-blue-500 shadow-sm !rounded-2xl !p-4 sj-form-card">
        <form action="{{ route('warehouse.mutation.store') }}" method="POST" id="pencatatan-form" data-clearable class="min-h-0 flex-1 flex flex-col">
            @csrf
            {{-- Penanda form Ops (mandiri, terpisah dari form Surat Jalan) --}}
            <input type="hidden" name="asal" value="ops">
            {{-- Penampung error validasi server (tidak masuk toast) agar kegagalan selalu terlihat --}}
            @if ($errors->any())
                <div class="mb-2 bg-red-50 border border-red-200 text-red-700 p-2.5 rounded-xl text-xs" role="alert">
                    <p class="font-bold mb-0.5 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Gagal menyimpan</p>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] font-semibold">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-2 bg-red-50 border border-red-200 text-red-700 p-2.5 rounded-xl text-xs font-semibold" role="alert">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif
            <div class="grid grid-cols-12 gap-x-2 gap-y-1.5 items-end shrink-0">
                <div class="col-span-6 md:col-span-2" id="wrap-tanggal">
                    <label class="ui-label">Tanggal {{ $tab === 'masuk' ? 'Masuk' : 'Keluar' }} <span class="text-red-500">*</span></label>
                    <input type="date" lang="id-ID" name="tanggal" required value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" class="ui-input-date !h-[30px] !text-xs">
                </div>
                @if($tab === 'masuk')
                <div class="col-span-6 md:col-span-2">
                    <label class="ui-label">No. SJ Supplier <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="doc_no" maxlength="100" placeholder="Contoh: SJ-SPL-001 (Opsional)" class="ui-input ui-input--sm" autocomplete="off">
                </div>
                <div class="col-span-6 md:col-span-2">
                    <label class="ui-label">Keterangan <span class="text-red-500">*</span></label>
                    <input list="purpose-in-options" name="purpose" id="purpose-select" value="Pembelian" placeholder="Pilih / Ketik Keterangan (Pembelian / Produksi / dll)" class="ui-input ui-input--sm !bg-white" autocomplete="off">
                    <datalist id="purpose-in-options">
                        <option value="Pembelian"></option>
                        <option value="Produksi"></option>
                        <option value="Retur Customer"></option>
                        <option value="Penyesuaian"></option>
                        <option value="Lainnya"></option>
                    </datalist>
                </div>
                <div class="col-span-12 md:col-span-3" id="partner-wrap">
                    <label class="ui-label" id="partner-label">Supplier <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <div class="relative">
                        <input type="text" name="partner_name" id="partner-type" autocomplete="off" spellcheck="false" placeholder="Ketik Nama Supplier (Opsional / Lain-lain)" class="ui-input ui-input--sm !bg-white">
                        <select name="supplier_id" id="partner-select" class="hidden" tabindex="-1" aria-hidden="true">
                            <option value="">— Pilih Supplier —</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" data-addr="{{ $supplierAddressMap[$s->id] ?? '' }}">{{ $s->supplier_name }}</option>
                            @endforeach
                        </select>
                        <div id="partner-suggestions" class="hidden absolute left-0 right-0 z-40 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden max-h-56 overflow-y-auto divide-y divide-slate-100"></div>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <label class="ui-label">No. PO Supplier <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="po_no" maxlength="100" placeholder="Contoh: PO-2026-001 (Opsional)" class="ui-input ui-input--sm" autocomplete="off">
                </div>
                @else
                <div class="col-span-6 md:col-span-2" id="wrap-doc-no">
                    <label class="ui-label">No. SJ Keluar <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="doc_no" maxlength="100" placeholder="Contoh: SJ-2026-001 (Opsional)" class="ui-input ui-input--sm" autocomplete="off">
                </div>
                <div class="col-span-6 md:col-span-2" id="wrap-purpose">
                    <label class="ui-label">Keterangan <span class="text-red-500">*</span></label>
                    <input list="purpose-options" name="purpose" id="purpose-select" required maxlength="50" placeholder="Pilih / Ketik Keterangan (Penjualan / Sample / Retur Barang)" class="ui-input ui-input--sm !bg-white" autocomplete="off">
                    <datalist id="purpose-options">
                        @foreach($purposes as $p)
                            <option value="{{ $p }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-span-12 md:col-span-3" id="partner-wrap">
                    <label class="ui-label" id="partner-label">Customer <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <div class="relative">
                        <input type="text" name="partner_name" id="partner-type" autocomplete="off" spellcheck="false" placeholder="Ketik Nama Customer (Opsional / Lain-lain)" class="ui-input ui-input--sm !bg-white">
                        <select name="customer_id" id="partner-select" class="hidden" tabindex="-1" aria-hidden="true">
                            <option value="">— Pilih Customer —</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" data-addr="{{ $customerAddressMap[$c->id] ?? '' }}">{{ $c->customer_name }}</option>
                            @endforeach
                        </select>
                        <div id="partner-suggestions" class="hidden absolute left-0 right-0 z-40 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden max-h-56 overflow-y-auto divide-y divide-slate-100"></div>
                    </div>
                    <select id="supplier-src" class="hidden" aria-hidden="true" tabindex="-1">
                        <option value="">— Pilih Supplier —</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" data-addr="{{ $supplierAddressMap[$s->id] ?? '' }}">{{ $s->supplier_name }}</option>
                        @endforeach
                    </select>
                    <div id="partner-none" class="hidden">
                        <label class="ui-label">Nama PS <span class="text-red-500">*</span></label>
                        <select name="ps_name" id="ps-select" class="ui-input ui-input--sm !bg-white">
                            <option value="">— Pilih Nama PS —</option>
                            @foreach($psList ?? [] as $ps)
                                <option value="{{ $ps }}">{{ $ps }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-3" id="wrap-po-no">
                    <label class="ui-label">No. PO / Referensi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="po_no" maxlength="100" placeholder="Contoh: PO-REF-001 (Opsional)" class="ui-input ui-input--sm" autocomplete="off">
                </div>
                @endif
            </div>
            <div id="wrap-expedition-address" class="mt-2 gap-2 items-end shrink-0" style="display:grid;grid-template-columns:minmax(120px,1fr) minmax(0,2fr);gap:8px;align-items:end;">
                <div>
                    <label class="ui-label">Ekspedisi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input list="expedition-options" name="expedition" maxlength="100" placeholder="Contoh: JNE / GoSend (Opsional)" class="ui-input ui-input--sm" autocomplete="off">
                    <datalist id="expedition-options">
                        @foreach($expeditions as $e)
                            <option value="{{ $e }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="ui-label">Alamat {{ $tab === 'masuk' ? 'Supplier' : 'Kirim' }} <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <div class="relative">
                        <input type="text" name="ship_address" id="ship-address" maxlength="2000" placeholder="Contoh: Jl. Sudirman No. 1, Jakarta (Dapat Disesuaikan)" class="ui-input ui-input--sm" autocomplete="off">
                        <p id="addr-warn" class="hidden absolute left-0 z-10 text-[10px] text-amber-600 font-bold mt-0.5" style="top:100%"><i class="fas fa-exclamation-triangle mr-1"></i>Master belum punya alamat — ketik manual atau lengkapi di master.</p>
                    </div>
                </div>
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-slate-100 space-y-2 flex-1 min-h-0 flex flex-col">
                <div class="flex items-center gap-2 shrink-0">
                    <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider whitespace-nowrap">Rincian Barang {{ $tab === 'masuk' ? 'Masuk' : 'Keluar' }}</h4>
                    <div class="flex-1 h-px bg-slate-100"></div>
                    <span id="sum-info" class="ui-info !h-[26px] !text-[11px]">0 baris · total 0</span>
                </div>

                {{-- Search + tabel dalam satu blok fleksibel: hanya tabel yang scroll --}}
                <div class="relative shrink-0">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-blue-300 text-xs pointer-events-none"></i>
                        <input type="text" id="product-search" placeholder="Cari Nama atau Kode Barang (Klik Hasil Untuk Memilih)..." title="Ketik nama atau kode barang, lalu klik hasilnya untuk mengisi Qty, Batch & Exp lewat popup." class="ui-search !h-[32px] !pl-9 !bg-white hover:!bg-white !text-slate-700" autocomplete="off">
                    </div>
                    <div id="product-search-results" class="hidden absolute left-0 right-0 z-50 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-2xl overflow-hidden max-h-72 overflow-y-auto divide-y divide-slate-100" style="top: 100%;"></div>
                </div>

                {{-- Tabel barang yang sudah disimpan (scroll internal, header sticky) --}}
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-xs">
                    <div class="sj-items-wrap ops-form-items-scroll">
                        <table class="w-full text-xs min-w-[760px] table-bordered">
                            <thead>
                                <tr class="bg-slate-50/80 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                                    <th class="px-3 py-2 text-center w-12 font-bold">No</th>
                                    <th class="px-3 py-2 text-left font-bold">Nama Barang</th>
                                    <th class="px-3 py-2 text-center w-24 font-bold">Kode</th>
                                    <th class="px-3 py-2 text-right w-24 font-bold">Qty</th>
                                    <th class="px-3 py-2 text-center w-20 font-bold">Satuan</th>
                                    <th class="px-3 py-2 text-left w-32 font-bold">No. Batch</th>
                                    <th class="px-3 py-2 text-center w-32 font-bold">Tgl Exp</th>
                                    <th class="px-3 py-2 text-center w-24 font-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="rows-container" class="divide-y divide-slate-100"></tbody>
                        </table>
                        <div id="empty-barang" class="px-4 py-7 text-center bg-slate-50/50 border-t border-dashed border-slate-200">
                            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-50 text-blue-500 mb-2">
                                <i class="fas fa-box-open text-lg"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-600">Belum ada barang di daftar rincian</p>
                            <p class="text-[11px] text-slate-400 mt-0.5 max-w-sm mx-auto">Ketik nama atau kode barang pada kolom pencarian di atas untuk menambahkan.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Popup isi rincian barang yang diklik (Canvas Konten Terisolasi - Pilihan B) --}}
            <div id="barang-modal" data-ui-modal class="modal-content-canvas hidden" role="dialog" aria-modal="true">
                <div id="barang-modal-backdrop" class="absolute inset-0 bg-transparent" data-modal-close></div>
                <div id="barang-modal-panel" class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden flex flex-col z-10 opacity-0 scale-95 transition-all duration-200 my-auto mx-auto" style="max-height: calc(100% - 1rem);">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm"><i class="fas fa-box text-xs"></i></span>
                            <div class="min-w-0">
                                <h5 id="modal-title" class="font-bold text-sm text-slate-800 leading-tight">Tambah Barang</h5>
                                <p class="text-[11px] text-slate-400 font-medium mt-0.5">Isi Jumlah Qty, No. Batch & Tgl Expired, lalu simpan ke tabel.</p>
                            </div>
                        </div>
                        <button type="button" data-modal-close class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0" title="Tutup"><i class="fas fa-times text-xs"></i></button>
                    </div>
                    <div class="p-5 space-y-4 overflow-y-auto">
                        <div>
                            <label class="ui-label">Nama Barang</label>
                            <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                                <div id="m-nama" class="text-sm font-bold text-slate-800 leading-snug break-words">—</div>
                            </div>
                            <input type="hidden" id="m-product-id" value="">
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="ui-label">Kode Barang</label>
                                <input type="text" id="m-kode" readonly tabindex="-1" placeholder="—" class="ui-input ui-input--sm !bg-slate-50 !text-slate-500 !border-slate-200 text-center cursor-not-allowed">
                            </div>
                            <div>
                                <label class="ui-label">Satuan</label>
                                <input type="text" id="m-satuan" readonly tabindex="-1" placeholder="—" class="ui-input ui-input--sm !bg-slate-50 !text-slate-500 !border-slate-200 text-center cursor-not-allowed">
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <label class="ui-label !mb-0">Qty <span class="text-red-500">*</span></label>
                                <span id="m-stok-info" class="text-[10px] font-bold"></span>
                            </div>
                            <input type="text" id="m-qty" inputmode="decimal" autocomplete="off" spellcheck="false" placeholder="0" class="ui-input !bg-white !h-[38px] text-right font-bold !text-slate-800">
                            <p id="m-batch-info" class="text-[10px] text-slate-400 font-medium mt-1.5 min-h-[14px]"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="ui-label">No. Batch <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                <input type="text" id="m-batch" list="m-batch-list" maxlength="100" placeholder="Contoh: B-2026-001 (Opsional)" class="ui-input ui-input--sm !bg-white !text-slate-800" autocomplete="off">
                                <datalist id="m-batch-list"></datalist>
                            </div>
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <label class="ui-label !mb-0">Tgl Expired <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                    <select id="m-exp-mode" title="Pilih format tanggal expired" class="h-[22px] max-w-[118px] px-1.5 rounded-md border border-slate-200 bg-white text-[10px] font-bold text-slate-600 outline-none focus:border-blue-400">
                                        <option value="my">Bulan / Tahun</option>
                                        <option value="dmy">Tgl / Bulan / Tahun</option>
                                    </select>
                                </div>
                                <div id="m-exp-wrap" class="grid grid-cols-2 gap-1">
                                    <select id="m-exp-day" class="hidden ui-input ui-input--sm !px-1.5 !bg-white !text-slate-800 text-[11px]">
                                        <option value="">Tgl</option>
                                        @for($d = 1; $d <= 31; $d++)
                                            <option value="{{ $d }}">{{ $d }}</option>
                                        @endfor
                                    </select>
                                    <select id="m-exp-month" class="ui-input ui-input--sm !px-1.5 !bg-white !text-slate-800 text-[11px]">
                                        <option value="">Bulan</option>
                                        @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $i => $bln)
                                            <option value="{{ $i + 1 }}">{{ $bln }}</option>
                                        @endforeach
                                    </select>
                                    <select id="m-exp-year" class="ui-input ui-input--sm !px-1.5 !bg-white !text-slate-800 text-[11px]">
                                        <option value="">Tahun</option>
                                        @for($y = now()->year - 20; $y <= now()->year + 30; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <input type="hidden" id="m-exp" value="">
                                <p id="m-exp-hint" class="text-[10px] text-slate-400 font-medium mt-1">Pilih bulan & tahun (Otomatis akhir bulan).</p>
                            </div>
                        </div>

                        <p id="m-error" class="hidden text-[11px] font-bold text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2.5"></p>
                    </div>
                    <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex gap-2 justify-end shrink-0">
                        <button type="button" data-modal-close class="h-[36px] px-4 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                        <button type="button" id="m-save" class="h-[36px] px-5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-colors"><i class="fas fa-check mr-1.5"></i>Simpan ke Tabel</button>
                    </div>
                </div>
            </div>

            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex justify-end shrink-0">
                <button type="submit" id="simpan-mutasi-btn" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold h-[34px] px-5 rounded-lg shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all w-full sm:w-auto disabled:opacity-60 disabled:cursor-not-allowed">
                    <i class="fas fa-save text-[11px]"></i> {{ $tab === 'masuk' ? 'Simpan Barang Masuk' : 'Simpan Barang Keluar' }}
                </button>
            </div>
        </form>
    </x-ui.glass-card>
    @endif
</div>

@push('scripts')
<script>
    // Inisialisasi Form & Konfigurasi
    function initPencatatanOps() {
        const container = document.getElementById('rows-container');
        const emptyBox = document.getElementById('empty-barang');
        const form = document.getElementById('pencatatan-form');
        if (!container || !form) return;
        const productOptions = @json($productOptionsData ?? []);
        const TAB_ARAH = @json($tab);
        const DRAFT_KEY = 'pencatatan_draft_v3_' + TAB_ARAH;
        const BATCH_URL = '{{ route("warehouse.mutation.batches") }}';
        let isRestoring = false, isSubmitting = false;
        let editingRow = null;
        let modalBatches = [];

        function fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(n)); }
        function esc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
        function pad2(n) { return String(n).padStart(2, '0'); }

        // Sanitasi Input Qty
        function sanitizeQty() {
            if (!mQty) return;
            const raw = mQty.value || '';
            let clean = raw.replace(/,/g, '.').replace(/[^0-9.]/g, '');
            const dot = clean.indexOf('.');
            if (dot !== -1) clean = clean.slice(0, dot + 1) + clean.slice(dot + 1).replace(/\./g, '');
            if (clean !== raw) mQty.value = clean;
        }

        // Parser Expired Date (Flexible)
        function isoFrom(y, mo, d) {
            const dt = new Date(y, mo - 1, d);
            if (dt.getFullYear() !== y || dt.getMonth() !== mo - 1 || dt.getDate() !== d) return null;
            return y + '-' + pad2(mo) + '-' + pad2(d);
        }
        function parseExpiry(raw) {
            const s = (raw || '').trim();
            if (!s) return '';
            let m = s.match(/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/);
            if (m) return isoFrom(+m[1], +m[2], +m[3]);
            m = s.match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/);
            if (m) return isoFrom(+m[3], +m[2], +m[1]);
            m = s.match(/^(\d{1,2})[-\/](\d{4})$/);
            if (m) {
                const mo = +m[1];
                if (mo < 1 || mo > 12) return null;
                const last = new Date(+m[2], mo, 0).getDate();
                return isoFrom(+m[2], mo, last);
            }
            return null;
        }
        function fmtExpDisplay(iso) {
            const s = (iso || '').trim();
            if (!s) return '';
            const m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            return m ? (m[3] + '/' + m[2] + '/' + m[1]) : s;
        }

        // Rekalkulasi Total & Autosave Draft
        function recalc() {
            let n = 0, q = 0;
            container.querySelectorAll('.row-barang').forEach(row => {
                n++;
                q += parseFloat(row.querySelector('.input-qty')?.value) || 0;
            });
            const sumEl = document.getElementById('sum-info');
            if (sumEl) sumEl.textContent = n + ' baris · total ' + fmt(q);
            if (emptyBox) emptyBox.style.display = n ? 'none' : 'block';
            saveDraft();
        }

        function saveDraft() {
            if (!form || isRestoring) return;
            const rows = [];
            container.querySelectorAll('.row-barang').forEach(row => {
                rows.push({
                    product_id: row.querySelector('.input-barang')?.value || '',
                    qty: row.querySelector('.input-qty')?.value || '',
                    batch_no: row.querySelector('.input-batch')?.value || '',
                    expiry_date: row.querySelector('.input-ed')?.value || '',
                    keterangan: row.querySelector('.input-note')?.value || '',
                });
            });
            const partnerSel = form.querySelector('#partner-select');
            localStorage.setItem(DRAFT_KEY, JSON.stringify({
                tanggal: form.querySelector('input[name="tanggal"]')?.value || '',
                doc_no: form.querySelector('input[name="doc_no"]')?.value || '',
                customer_id: form.querySelector('select[name="customer_id"]')?.value || '',
                supplier_id: form.querySelector('select[name="supplier_id"]')?.value || '',
                partner_name: form.querySelector('#partner-type')?.value || '',
                po_no: form.querySelector('input[name="po_no"]')?.value || '',
                purpose: form.querySelector('#purpose-select')?.value || '',
                ps_name: form.querySelector('#ps-select')?.value || '',
                ship_address: form.querySelector('#ship-address')?.value || '',
                expedition: form.querySelector('input[name="expedition"]')?.value || '',
                rows,
            }));
        }

        // Opsi Rekanan & Alamat
        const purposeSel = document.getElementById('purpose-select');
        const partnerSel = document.getElementById('partner-select');
        const partnerLabel = document.getElementById('partner-label');
        const partnerWrap = document.getElementById('partner-wrap');
        const shipInput = document.getElementById('ship-address');
        const addrWarn = document.getElementById('addr-warn');
        const customerOptsHTML = partnerSel ? partnerSel.innerHTML : '';
        const supplierSrc = document.getElementById('supplier-src');
        const partnerType = document.getElementById('partner-type');
        const partySuggestBox = document.getElementById('partner-suggestions');

        function fillAddress() {
            if (!partnerSel || !shipInput || partnerSel.disabled) return;
            const opt = partnerSel.selectedOptions[0];
            const addr = opt?.dataset?.addr || '';
            if (addr) {
                shipInput.value = addr;
                if (addrWarn) addrWarn.classList.add('hidden');
            } else if (partnerSel.value) {
                if (addrWarn) addrWarn.classList.remove('hidden');
            } else if (addrWarn) {
                addrWarn.classList.add('hidden');
            }
        }

        // Rekomendasi Nama Rekanan
        function partyOptions() {
            return partnerSel ? Array.from(partnerSel.options).filter(o => o.value !== '') : [];
        }

        function hidePartySuggestions() {
            if (partySuggestBox) partySuggestBox.classList.add('hidden');
        }

        function renderPartySuggestions(keyword = '') {
            if (!partySuggestBox || !partnerSel) return;
            const kw = String(keyword || '').trim().toLowerCase();
            const list = partyOptions()
                .filter(o => !kw || o.textContent.trim().toLowerCase().includes(kw))
                .slice(0, 20);
            if (!list.length) {
                partySuggestBox.innerHTML = '<div class="px-3 py-2.5 text-[11px] text-slate-400 font-semibold">Tidak ada hasil — nama baru akan otomatis disimpan ke master.</div>';
            } else {
                partySuggestBox.innerHTML = list.map(o =>
                    '<button type="button" data-value="' + esc(o.value) + '" class="w-full text-left px-3 py-2 hover:bg-blue-50/70 transition-colors">' +
                    '<span class="block text-xs font-bold text-slate-800 truncate">' + esc(o.textContent.trim()) + '</span>' +
                    (o.dataset.addr ? '<span class="block text-[10px] text-slate-400 truncate mt-0.5">' + esc(o.dataset.addr) + '</span>' : '') +
                    '</button>'
                ).join('');
            }
            partySuggestBox.classList.remove('hidden');
        }

        function chooseParty(value) {
            const opt = partyOptions().find(o => o.value === String(value));
            if (!opt) return;
            if (partnerSel) partnerSel.value = opt.value;
            if (partnerType) partnerType.value = opt.textContent.trim();
            hidePartySuggestions();
            fillAddress();
            recalc();
        }

        function syncTypeToSelect() {
            if (!partnerType || !partnerSel) return;
            const typed = (partnerType.value || '').trim().toLowerCase();
            let match = '';
            // Pencocokan Nama Rekanan Eksisting
            partyOptions().forEach(o => {
                if (o.textContent.trim().toLowerCase() === typed) match = o.value;
            });
            if (partnerSel.value !== match) partnerSel.value = match;
            fillAddress();
            recalc();
        }

        function syncSelectToType() {
            if (!partnerType || !partnerSel) return;
            const opt = partnerSel.selectedOptions[0];
            partnerType.value = (opt && opt.value) ? opt.textContent.trim() : '';
        }

        let lastPartyMode = null;
        function applyPurpose() {
            if (!purposeSel || !partnerSel) return;
            if (TAB_ARAH === 'masuk') {
                partnerSel.name = 'supplier_id';
                if (partnerLabel) partnerLabel.innerHTML = 'Supplier <span class="text-slate-400 font-normal">(Opsional)</span>';
                if (partnerType) {
                    partnerType.placeholder = 'Ketik Nama Supplier (Opsional / Lain-lain)';
                    partnerType.classList.remove('hidden');
                    partnerType.disabled = false;
                }
                if (partnerWrap) partnerWrap.classList.remove('hidden');
                return;
            }
            const v = (purposeSel.value || '').trim();
            const lower = v.toLowerCase();
            const isReturn = (lower === 'retur barang' || lower === 'return barang' || lower === 'retur' || lower === 'return' || lower === 'retur supplier');
            const partyMode = isReturn ? 'supplier' : 'customer';
            const modeChanged = lastPartyMode !== null && lastPartyMode !== partyMode;
            lastPartyMode = partyMode;
            if (isReturn && supplierSrc) {
                partnerSel.name = 'supplier_id';
                partnerSel.innerHTML = supplierSrc.innerHTML;
                if (partnerLabel) partnerLabel.innerHTML = 'Supplier <span class="text-slate-400 font-normal">(Opsional)</span>';
            } else {
                partnerSel.name = 'customer_id';
                partnerSel.innerHTML = customerOptsHTML;
                if (partnerLabel) partnerLabel.innerHTML = 'Customer <span class="text-slate-400 font-normal">(Opsional)</span>';
            }
            if (modeChanged) {
                partnerSel.value = '';
            }
            const isInternal = lower === 'internal';
            const isSample = (lower === 'sample' || lower === 'saple' || lower === 'sampel');
            const wrapDoc = document.getElementById('wrap-doc-no');
            const wrapPo = document.getElementById('wrap-po-no');
            const wrapExpAddr = document.getElementById('wrap-expedition-address');
            const wrapTanggal = document.getElementById('wrap-tanggal');
            const wrapPurpose = document.getElementById('wrap-purpose');
            const psWrap = document.getElementById('partner-none');
            const psSel = document.getElementById('ps-select');

            // Penyesuaian Form Sample
            if (wrapDoc) wrapDoc.classList.toggle('hidden', isSample);
            if (wrapPo) wrapPo.classList.toggle('hidden', isSample);
            if (wrapExpAddr) wrapExpAddr.classList.toggle('hidden', isSample);

            if (wrapTanggal) {
                wrapTanggal.className = isSample ? 'col-span-12 sm:col-span-4 md:col-span-4' : 'col-span-6 md:col-span-2';
            }
            if (wrapPurpose) {
                wrapPurpose.className = isSample ? 'col-span-12 sm:col-span-4 md:col-span-4' : 'col-span-6 md:col-span-2';
            }
            if (partnerWrap) {
                partnerWrap.className = isSample ? 'col-span-12 sm:col-span-4 md:col-span-4' : 'col-span-12 md:col-span-3';
                partnerWrap.classList.toggle('hidden', isInternal);
            }

            if (partnerSel) {
                partnerSel.classList.add('hidden');
                partnerSel.disabled = isInternal || isSample;
                partnerSel.required = false;
                if (isSample || isInternal) partnerSel.value = '';
            }
            if (partnerType) {
                partnerType.classList.toggle('hidden', isSample);
                partnerType.disabled = isInternal || isSample;
                if (isSample) partnerType.value = '';
            }
            hidePartySuggestions();
            if (partnerLabel) partnerLabel.classList.toggle('hidden', isSample);
            if (psWrap) psWrap.classList.toggle('hidden', !isSample || isInternal);
            if (psSel) {
                psSel.required = isSample && !isInternal;
                if (!isSample) psSel.value = '';
            }
            if (isInternal && shipInput) { shipInput.value = ''; }
            if (addrWarn && (isInternal || isSample || !partnerSel.value)) addrWarn.classList.add('hidden');
        }

        if (purposeSel) {
            purposeSel.addEventListener('input', () => { applyPurpose(); recalc(); });
            purposeSel.addEventListener('change', () => { applyPurpose(); recalc(); });
            applyPurpose();
        }
        if (partnerSel) partnerSel.addEventListener('change', () => { fillAddress(); recalc(); });
        if (partnerType) {
            partnerType.addEventListener('input', () => {
                syncTypeToSelect();
                renderPartySuggestions(partnerType.value);
            });
            partnerType.addEventListener('focus', () => renderPartySuggestions(partnerType.value));
            partnerType.addEventListener('change', () => { syncTypeToSelect(); hidePartySuggestions(); });
            partnerType.addEventListener('keydown', e => {
                if (e.key === 'Escape') { hidePartySuggestions(); return; }
                if (e.key === 'Enter' && partySuggestBox && !partySuggestBox.classList.contains('hidden')) {
                    const first = partySuggestBox.querySelector('[data-value]');
                    if (first) { e.preventDefault(); chooseParty(first.dataset.value); }
                }
            });
        }
        if (partySuggestBox) {
            partySuggestBox.addEventListener('mousedown', e => {
                const btn = e.target.closest('[data-value]');
                if (!btn) return;
                e.preventDefault();
                chooseParty(btn.dataset.value);
            });
        }
        document.addEventListener('click', e => {
            if (!partySuggestBox || partySuggestBox.classList.contains('hidden')) return;
            if (partySuggestBox.contains(e.target) || e.target === partnerType) return;
            hidePartySuggestions();
        });

        // Pencarian Produk
        const searchInput = document.getElementById('product-search');
        const searchBox = document.getElementById('product-search-results');

        function renderSearch(keyword = '') {
            if (!searchBox) return;
            const kw = (keyword || '').trim().toLowerCase();
            const tokens = kw ? kw.split(/\s+/).filter(Boolean) : [];
            const list = (productOptions || []).filter(p => {
                if (!tokens.length) return true; // Tampilkan produk awal saat klik / fokus
                const targetText = [
                    p.nama || '',
                    p.nama_clean || '',
                    p.nama_raw || '',
                    p.kode || '',
                    p.kode_clean || ''
                ].join(' ').toLowerCase();
                return tokens.every(t => targetText.includes(t));
            }).slice(0, 30);

            if (!list.length) {
                searchBox.innerHTML = '<div class="px-4 py-3 text-xs text-slate-400 font-semibold">Tidak ketemu — coba kata kunci lain.</div>';
            } else {
                searchBox.innerHTML = list.map(p =>
                    `<button type="button" data-pid="${p.id}" class="w-full text-left px-4 py-2.5 hover:bg-blue-50/70 flex items-center justify-between gap-3 transition-colors cursor-pointer">` +
                    `<span class="min-w-0"><span class="block text-xs font-bold text-slate-800 truncate">${esc(p.nama)}${p.opnamed ? '' : ' <span class="text-[9px] font-black px-1.5 py-0.5 rounded-full bg-rose-100 text-rose-700 align-middle">belum opname</span>'}</span>` +
                    `<span class="block text-[10px] text-slate-400 font-semibold mt-0.5">${esc(p.kode || '—')} · ${esc(p.unit || '')}</span></span>` +
                    `<span class="text-[10px] font-bold ${parseFloat(p.stok) > 0 ? 'text-emerald-600 bg-emerald-50 border border-emerald-200' : 'text-red-500 bg-red-50 border border-red-200'} whitespace-nowrap px-2 py-1 rounded-lg">stok ${esc(p.stok)}</span></button>`
                ).join('');
            }
            searchBox.classList.remove('hidden');
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => renderSearch(searchInput.value));
            searchInput.addEventListener('focus', () => renderSearch(searchInput.value));
            searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
                renderSearch(searchInput.value);
            });
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (searchBox) searchBox.classList.add('hidden');
                } else if (e.key === 'Enter') {
                    if (searchBox && !searchBox.classList.contains('hidden')) {
                        const firstBtn = searchBox.querySelector('[data-pid]');
                        if (firstBtn) {
                            e.preventDefault();
                            firstBtn.click();
                        }
                    }
                }
            });
            document.addEventListener('click', (e) => {
                if (searchBox && !searchBox.classList.contains('hidden')) {
                    if (!searchBox.contains(e.target) && e.target !== searchInput) searchBox.classList.add('hidden');
                }
            });
        }
        if (searchBox) {
            searchBox.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-pid]');
                if (!btn) return;
                searchBox.classList.add('hidden');
                if (searchInput) searchInput.value = '';
                openModal(btn.dataset.pid, null);
            });
        }

        // Modal Input Barang
        const modal = document.getElementById('barang-modal');
        const modalPanel = document.getElementById('barang-modal-panel');
        const modalBackdrop = document.getElementById('barang-modal-backdrop');
        // Relokasi Modal ke Body
        if (modal && modal.parentElement !== document.body) document.body.appendChild(modal);
        const mNama = document.getElementById('m-nama');
        const mPid = document.getElementById('m-product-id');
        const mKode = document.getElementById('m-kode');
        const mSatuan = document.getElementById('m-satuan');
        const mQty = document.getElementById('m-qty');
        const mBatch = document.getElementById('m-batch');
        const mBatchList = document.getElementById('m-batch-list');
        const mBatchInfo = document.getElementById('m-batch-info');
        const mExp = document.getElementById('m-exp');
        const mNote = document.getElementById('m-note');
        const mExpMode = document.getElementById('m-exp-mode');
        const mExpWrap = document.getElementById('m-exp-wrap');
        const mExpDay = document.getElementById('m-exp-day');
        const mExpMonth = document.getElementById('m-exp-month');
        const mExpYear = document.getElementById('m-exp-year');
        const mExpHint = document.getElementById('m-exp-hint');
        const mStok = document.getElementById('m-stok-info');
        const mErr = document.getElementById('m-error');
        const mSave = document.getElementById('m-save');
        const mTitle = document.getElementById('modal-title');

        function expLastDay(y, mo) {
            return new Date(y, mo, 0).getDate();
        }

        // Format Expired (Bulan/Tahun atau Harian)
        function applyExpMode() {
            const isDmy = (mExpMode?.value || 'my') === 'dmy';
            if (mExpDay) mExpDay.classList.toggle('hidden', !isDmy);
            if (mExpWrap) {
                mExpWrap.classList.toggle('grid-cols-3', isDmy);
                mExpWrap.classList.toggle('grid-cols-2', !isDmy);
            }
            if (mExpHint) {
                mExpHint.textContent = isDmy
                    ? 'Pilih tanggal, bulan & tahun.'
                    : 'Pilih bulan & tahun — otomatis akhir bulan.';
            }
        }

        function refreshExpDays() {
            if (!mExpDay) return;
            const prev = mExpDay.value;
            const mo = parseInt(mExpMonth?.value || '', 10);
            const y = parseInt(mExpYear?.value || '', 10) || new Date().getFullYear();
            const max = mo ? expLastDay(y, mo) : 31;
            let html = '<option value="">Tgl</option>';
            for (let d = 1; d <= max; d++) html += '<option value="' + d + '">' + d + '</option>';
            mExpDay.innerHTML = html;
            if (prev && parseInt(prev, 10) <= max) mExpDay.value = prev;
        }

        function syncExpHidden() {
            if (!mExp) return;
            const mode = mExpMode?.value || 'my';
            const mo = parseInt(mExpMonth?.value || '', 10);
            const y = parseInt(mExpYear?.value || '', 10);
            const d = parseInt(mExpDay?.value || '', 10);
            if (!mo || !y || (mode === 'dmy' && !d)) { mExp.value = ''; return; }
            mExp.value = mode === 'dmy' ? pad2(d) + '/' + pad2(mo) + '/' + y : pad2(mo) + '/' + y;
        }

        function setExpFromIso(iso) {
            const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(iso || '').trim());
            if (!m) {
                if (mExpMode) mExpMode.value = 'my';
                if (mExpMonth) mExpMonth.value = '';
                if (mExpYear) mExpYear.value = '';
                if (mExpDay) mExpDay.value = '';
                refreshExpDays();
                applyExpMode();
                if (mExp) mExp.value = '';
                return;
            }
            const y = parseInt(m[1], 10);
            const mo = parseInt(m[2], 10);
            const d = parseInt(m[3], 10);
            if (mExpMode) mExpMode.value = d === expLastDay(y, mo) ? 'my' : 'dmy';
            if (mExpYear) {
                if (!Array.from(mExpYear.options).some(o => o.value === String(y))) {
                    const o = document.createElement('option');
                    o.value = String(y);
                    o.textContent = String(y);
                    mExpYear.appendChild(o);
                }
                mExpYear.value = String(y);
            }
            if (mExpMonth) mExpMonth.value = String(mo);
            refreshExpDays();
            if (mExpDay) mExpDay.value = String(d);
            applyExpMode();
            syncExpHidden();
        }

        function showErr(msg, targetEl = null) {
            if (mQty) mQty.classList.remove('!border-red-500', '!ring-2', '!ring-red-200');
            const mExpWrap = document.getElementById('m-exp-wrap');
            if (mExpWrap) mExpWrap.classList.remove('!border-red-500', '!ring-2', '!ring-red-200');
            if (!mErr) return;
            if (!msg) { mErr.classList.add('hidden'); mErr.innerHTML = ''; return; }
            mErr.innerHTML = '<i class="fas fa-exclamation-circle mr-1.5"></i>' + esc(msg);
            mErr.classList.remove('hidden');
            if (targetEl) {
                targetEl.classList.add('!border-red-500', '!ring-2', '!ring-red-200');
                try { targetEl.focus(); } catch (e) {}
            }
        }

        function stokPill(txt, tone) {
            const map = {
                red: 'text-red-700 bg-red-50 border-red-200',
                green: 'text-emerald-700 bg-emerald-50 border-emerald-200',
                slate: 'text-slate-500 bg-slate-100 border-slate-200',
            };
            return '<span class="inline-flex items-center gap-1 text-[10px] font-bold border rounded-lg px-2 py-0.5 ' + (map[tone] || map.slate) + '">' + esc(txt) + '</span>';
        }

        function refreshModalStock() {
            const p = productOptions.find(x => String(x.id) === String(mPid?.value));
            if (!p || !mStok) { if (mStok) mStok.innerHTML = ''; return; }
            const sisa = parseFloat(p.stok) || 0;
            const qty = parseFloat(mQty?.value) || 0;
            const sisaTxt = 'Sisa ' + fmt(sisa) + (p.unit ? ' ' + p.unit : '');
            if (TAB_ARAH !== 'masuk') {
                const over = qty > 0 && qty > sisa;
                const low = sisa <= 0;
                let extra = '';
                if (over) extra = ' · melebihi!';
                else if (low) extra = ' · habis';
                mStok.innerHTML = stokPill(sisaTxt + extra, (low || over) ? 'red' : 'green');
            } else {
                mStok.innerHTML = stokPill(sisaTxt, 'slate');
            }
        }

        function refreshModalBatchInfo() {
            if (!mBatchInfo || !mBatch) return;
            const val = (mBatch.value || '').trim();
            const found = modalBatches.find(b => String(b.batch_no) === val);
            if (!val) mBatchInfo.textContent = '';
            else if (found) mBatchInfo.textContent = 'Sisa ' + found.qty + (found.expiry_date ? ' — ED ' + found.expiry_label : ' — tanpa ED');
            else mBatchInfo.textContent = 'Batch tidak ada di daftar — pastikan no. batch benar.';
        }

        async function loadModalBatches(productId) {
            modalBatches = [];
            if (mBatchList) mBatchList.innerHTML = '';
            refreshModalBatchInfo();
            if (!productId || TAB_ARAH === 'masuk') return;
            try {
                const res = await fetch(BATCH_URL + '?product_id=' + encodeURIComponent(productId), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                modalBatches = json.data || [];
                modalBatches.forEach(b => {
                    const o = document.createElement('option');
                    o.value = b.batch_no;
                    o.textContent = b.batch_no + ' — sisa ' + b.qty + (b.expiry_date ? ' — ED ' + b.expiry_label : '');
                    mBatchList.appendChild(o);
                });
                refreshModalBatchInfo();
                // Sinkronisasi ED dari Batch
                const cur = (mBatch?.value || '').trim();
                const hit = modalBatches.find(b => String(b.batch_no) === cur);
                if (hit?.expiry_date && !mExp?.value) setExpFromIso(hit.expiry_date);
            } catch (e) { /* biarkan FEFO otomatis */ }
        }

        function openModal(productId, row = null) {
            const p = productOptions.find(x => String(x.id) === String(productId));
            if (!p) return;
            editingRow = row || null;
            if (mPid) mPid.value = p.id;
            if (mNama) mNama.textContent = p.nama || '—';
            if (mKode) mKode.value = p.kode || '';
            if (mSatuan) mSatuan.value = p.unit || '';
            if (mTitle) mTitle.textContent = (row ? 'Ubah Barang' : 'Tambah Barang');
            if (row) {
                if (mQty) mQty.value = row.querySelector('.input-qty')?.value || '';
                if (mBatch) mBatch.value = row.querySelector('.input-batch')?.value || '';
                if (mNote) mNote.value = row.querySelector('.input-note')?.value || '';
                setExpFromIso(row.querySelector('.input-ed')?.value || '');
            } else {
                if (mQty) mQty.value = '';
                if (mBatch) mBatch.value = '';
                if (mNote) mNote.value = '';
                setExpFromIso('');
            }
            showErr('');
            refreshModalStock();
            refreshModalBatchInfo();
            if (modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    modalBackdrop?.classList.remove('opacity-0');
                    modalPanel?.classList.remove('opacity-0', 'scale-95');
                }));
            }
            loadModalBatches(p.id);
            setTimeout(() => { mQty?.focus(); mQty?.select?.(); }, 60);
        }

        function closeModal() {
            const done = () => {
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
                document.body.style.overflow = '';
            };
            if (modal && !modal.classList.contains('hidden')) {
                modalBackdrop?.classList.add('opacity-0');
                modalPanel?.classList.add('opacity-0', 'scale-95');
                setTimeout(done, 180);
            } else done();
            editingRow = null;
            showErr('');
        }

        if (modal) {
            modal.querySelectorAll('[data-modal-close]').forEach(b => b.addEventListener('click', closeModal));
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
            // Shortcut Enter Simpan Modal
            ['m-qty', 'm-batch'].forEach(id => {
                document.getElementById(id)?.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') { e.preventDefault(); mSave?.click(); }
                });
            });
        }
        if (mQty) {
            mQty.addEventListener('input', () => {
                mQty.classList.remove('!border-red-500', '!ring-2', '!ring-red-200');
                showErr('');
                sanitizeQty();
                refreshModalStock();
            });
            mQty.addEventListener('change', () => {
                mQty.classList.remove('!border-red-500', '!ring-2', '!ring-red-200');
                sanitizeQty();
                refreshModalStock();
            });
        }
        if (mExpMode) mExpMode.addEventListener('change', () => { applyExpMode(); syncExpHidden(); });
        if (mExpMonth) mExpMonth.addEventListener('change', () => {
            const mExpWrap = document.getElementById('m-exp-wrap');
            if (mExpWrap) mExpWrap.classList.remove('!border-red-500', '!ring-2', '!ring-red-200');
            showErr('');
            refreshExpDays(); syncExpHidden();
        });
        if (mExpYear) mExpYear.addEventListener('change', () => {
            const mExpWrap = document.getElementById('m-exp-wrap');
            if (mExpWrap) mExpWrap.classList.remove('!border-red-500', '!ring-2', '!ring-red-200');
            showErr('');
            refreshExpDays(); syncExpHidden();
        });
        if (mExpDay) mExpDay.addEventListener('change', syncExpHidden);
        if (mBatch) {
            mBatch.addEventListener('input', () => {
                refreshModalBatchInfo();
                const hit = modalBatches.find(b => String(b.batch_no) === (mBatch.value || '').trim());
                if (hit?.expiry_date) setExpFromIso(hit.expiry_date);
            });
            mBatch.addEventListener('change', refreshModalBatchInfo);
        }

        function rowCells(p, d) {
            return { nama: p.nama || '—', kode: p.kode || '—', unit: p.unit || '' };
        }

        function addRow(data = null) {
            if (!data?.product_id) return;
            const p = productOptions.find(x => String(x.id) === String(data.product_id));
            if (!p) return;
            const c = rowCells(p, data);
            const tr = document.createElement('tr');
            tr.className = 'row-barang hover:bg-blue-50/50 transition-colors bg-white';
            const arahEsc = esc(TAB_ARAH);
            const noteCell = '<input type="hidden" name="keterangan[]" class="input-note">';
            tr.innerHTML =
                '<td class="px-4 py-3 text-center text-slate-400 font-semibold row-no"></td>' +
                '<td class="px-4 py-3"><span class="font-bold text-slate-800 cell-nama"></span>' +
                '<input type="hidden" name="product_id[]" class="input-barang">' +
                '<input type="hidden" name="arah[]" value="' + arahEsc + '"></td>' +
                '<td class="px-4 py-3 text-center text-slate-500 font-semibold cell-kode"></td>' +
                '<td class="px-4 py-3 text-right font-bold text-slate-800 cell-qty"><span class="qty-txt"></span>' +
                '<input type="hidden" name="qty[]" class="input-qty"></td>' +
                '<td class="px-4 py-3 text-center text-slate-500 cell-unit"></td>' +
                '<td class="px-4 py-3 text-slate-600 font-semibold cell-batch"><span class="batch-txt"></span>' +
                '<input type="hidden" name="batch_no[]" class="input-batch"></td>' +
                '<td class="px-4 py-3 text-center text-slate-500 cell-exp"><span class="exp-txt"></span>' +
                '<input type="hidden" name="expiry_date[]" class="input-ed"></td>' +
                noteCell +
                '<td class="px-4 py-3 text-center whitespace-nowrap">' +
                '<button type="button" title="Ubah" class="btn-edit inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 h-[26px] rounded-lg transition-colors mr-1"><i class="fas fa-pen text-[10px]"></i></button>' +
                '<button type="button" title="Hapus" class="btn-remove inline-flex items-center gap-1 text-[11px] font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 px-2 h-[26px] rounded-lg transition-colors"><i class="fas fa-trash-alt text-[10px]"></i></button></td>';
            
            // Render Nilai Baris
            tr.querySelector('.cell-nama').textContent = c.nama;
            tr.querySelector('.input-barang').value = p.id;
            tr.querySelector('.cell-kode').textContent = c.kode;
            tr.querySelector('.qty-txt').textContent = (data.qty || '') + '';
            tr.querySelector('.input-qty').value = data.qty || '';
            tr.querySelector('.cell-unit').textContent = c.unit;
            tr.querySelector('.batch-txt').textContent = data.batch_no || '-';
            tr.querySelector('.input-batch').value = data.batch_no || '';
            tr.querySelector('.exp-txt').textContent = fmtExpDisplay(data.expiry_date) || '—';
            tr.querySelector('.input-ed').value = data.expiry_date || '';
            if (tr.querySelector('.input-note')) {
                tr.querySelector('.input-note').value = data.keterangan || '';
            }
            if (tr.querySelector('.note-txt')) {
                tr.querySelector('.note-txt').textContent = data.keterangan || '—';
            }
            tr.querySelector('.btn-remove').addEventListener('click', () => {
                uiConfirmDelete({
                    title: 'Hapus baris ini?',
                    text: 'Barang ini akan dihapus dari daftar rincian mutasi.',
                    confirmText: 'Ya, hapus!',
                    onConfirm: () => {
                        tr.remove();
                        renumber();
                        recalc();
                    }
                });
            });
            tr.querySelector('.btn-edit').addEventListener('click', () => openModal(p.id, tr));
            container.appendChild(tr);
            renumber(); recalc();
        }

        function collectModalData() {
            const rawExp = (mExp?.value || '').trim();
            const isoExp = parseExpiry(rawExp);
            const expTouched = !!(mExpMonth?.value || mExpYear?.value || (mExpMode?.value === 'dmy' && mExpDay?.value));
            return {
                product_id: mPid?.value || '',
                qty: (mQty?.value || '').trim().replace(/,/g, '.'),
                batch_no: (mBatch?.value || '').trim(),
                expiry_raw: rawExp,
                expiry_date: isoExp ?? '',
                expiry_invalid: rawExp !== '' && isoExp === null,
                expiry_incomplete: expTouched && rawExp === '',
                keterangan: (mNote?.value || '').trim(),
            };
        }

        let isModalSaving = false;
        if (mSave) {
            mSave.addEventListener('click', () => {
                if (isModalSaving) return;
                isModalSaving = true;
                try {
                    const d = collectModalData();
                    const p = productOptions.find(x => String(x.id) === String(d.product_id));
                    if (!p) { showErr('Barang tidak valid — tutup lalu pilih ulang dari pencarian.'); return; }
                    const qtyNum = parseFloat(d.qty);
                    if (!d.qty || isNaN(qtyNum) || qtyNum <= 0) {
                        showErr('Jumlah Qty wajib diisi angka lebih dari 0.', mQty);
                        mQty?.focus();
                        return;
                    }
                    if (d.expiry_incomplete) {
                        const mExpWrap = document.getElementById('m-exp-wrap');
                        showErr('Tgl Expired belum lengkap — pilih ' + ((mExpMode?.value || 'my') === 'dmy' ? 'tanggal, bulan & tahun.' : 'bulan & tahun.'), mExpWrap);
                        mExpMonth?.focus();
                        return;
                    }
                    if (d.expiry_invalid) {
                        const mExpWrap = document.getElementById('m-exp-wrap');
                        showErr('Tgl Expired tidak valid / tidak cocok pada kalender — pilih ulang bulan/tahun.', mExpWrap);
                        mExpMonth?.focus();
                        return;
                    }

                    if (TAB_ARAH !== 'masuk') {
                        const sisa = parseFloat(p.stok) || 0;
                        if (qtyNum > sisa) {
                            showErr('Qty (' + qtyNum + ') melebihi sisa stok (' + fmt(sisa) + '). Kurangi qty atau pilih barang lain.', mQty);
                            mQty?.focus();
                            return;
                        }
                    }
                    showErr('');
                    if (editingRow) {
                        // Update Baris Aktif
                        editingRow.querySelector('.input-qty').value = d.qty;
                        editingRow.querySelector('.qty-txt').textContent = d.qty;
                        editingRow.querySelector('.input-batch').value = d.batch_no;
                        editingRow.querySelector('.batch-txt').textContent = d.batch_no || '-';
                        editingRow.querySelector('.input-ed').value = d.expiry_date;
                        editingRow.querySelector('.exp-txt').textContent = fmtExpDisplay(d.expiry_date) || '—';
                        if (editingRow.querySelector('.input-note')) {
                            editingRow.querySelector('.input-note').value = d.keterangan;
                            editingRow.querySelector('.note-txt').textContent = d.keterangan || '—';
                        }
                        recalc();
                    } else {
                        // Gabung Qty Baris Duplikat
                        const existingRow = Array.from(container.querySelectorAll('.row-barang')).find(tr => {
                            const rPid = tr.querySelector('.input-barang')?.value;
                            const rBatch = (tr.querySelector('.input-batch')?.value || '').trim();
                            return String(rPid) === String(d.product_id) && rBatch.toLowerCase() === d.batch_no.toLowerCase();
                        });

                        if (existingRow) {
                            const oldQty = parseFloat(existingRow.querySelector('.input-qty')?.value || 0);
                            const mergedQty = Math.round((oldQty + qtyNum) * 100) / 100;
                            if (TAB_ARAH !== 'masuk') {
                                const sisa = parseFloat(p.stok) || 0;
                                if (mergedQty > sisa) {
                                    showErr('Barang ini sudah ada di tabel (Qty: ' + oldQty + '). Total jika digabung (' + mergedQty + ') melebihi stok (' + fmt(sisa) + ').');
                                    mQty?.focus();
                                    return;
                                }
                            }
                            existingRow.querySelector('.input-qty').value = mergedQty;
                            existingRow.querySelector('.qty-txt').textContent = mergedQty;
                            if (d.expiry_date) {
                                existingRow.querySelector('.input-ed').value = d.expiry_date;
                                existingRow.querySelector('.exp-txt').textContent = fmtExpDisplay(d.expiry_date) || '—';
                            }
                            recalc();
                        } else {
                            addRow(d);
                        }
                    }
                    closeModal();
                    searchInput?.focus();
                } finally {
                    setTimeout(() => { isModalSaving = false; }, 300);
                }
            });
        }

        function renumber() {
            container.querySelectorAll('.row-barang').forEach((tr, i) => {
                const no = tr.querySelector('.row-no');
                if (no) no.textContent = (i + 1);
            });
        }

        // Pemulihan Draft Lokal
        try {
            isRestoring = true;
            const saved = JSON.parse(localStorage.getItem(DRAFT_KEY) || 'null');
            const setVal = (sel, v) => { const el = form.querySelector(sel); if (el && v) el.value = v; };
            if (saved) {
                setVal('input[name="tanggal"]', saved.tanggal);
                setVal('input[name="doc_no"]', saved.doc_no);
                setVal('input[name="po_no"]', saved.po_no);
                setVal('input[name="expedition"]', saved.expedition);
                setVal('#ship-address', saved.ship_address);
                const purEl = form.querySelector('#purpose-select');
                if (purEl && saved.purpose) purEl.value = saved.purpose;
                if (typeof applyPurpose === 'function') applyPurpose();
                const pSel = form.querySelector('#partner-select');
                if (pSel && !pSel.disabled) {
                    // Pasangan Rekanan Tersimpan
                    if ((saved.purpose === 'Retur Barang' || saved.purpose === 'Retur Supplier') && saved.supplier_id) {
                        pSel.name = 'supplier_id';
                        pSel.value = saved.supplier_id;
                    } else if (saved.purpose !== 'Retur Barang' && saved.purpose !== 'Retur Supplier' && saved.customer_id) {
                        pSel.name = 'customer_id';
                        pSel.value = saved.customer_id;
                    }
                }
                if (partnerType && saved.partner_name) partnerType.value = saved.partner_name;
                if (typeof syncTypeToSelect === 'function') syncTypeToSelect();
                else if (typeof syncSelectToType === 'function') syncSelectToType();
                const psS = form.querySelector('#ps-select');
                if (psS && saved.ps_name) psS.value = saved.ps_name;
                if (typeof fillAddress === 'function') {
                    const shipEl = form.querySelector('#ship-address');
                    if (shipEl && !shipEl.value) fillAddress();
                }
            }
            if (saved?.rows?.length) { saved.rows.forEach(r => addRow(r)); }
        } catch (e) { /* mulai kosong */ }
        finally { isRestoring = false; recalc(); }

        function showFormAlert(title, message, targetEl = null) {
            if (targetEl) {
                targetEl.classList.add('!border-red-500', '!ring-2', '!ring-red-200');
                try { targetEl.focus(); } catch (e) {}
                const removeError = () => {
                    targetEl.classList.remove('!border-red-500', '!ring-2', '!ring-red-200');
                    targetEl.removeEventListener('input', removeError);
                    targetEl.removeEventListener('change', removeError);
                };
                targetEl.addEventListener('input', removeError);
                targetEl.addEventListener('change', removeError);
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: title || 'Peringatan Formulir',
                    text: message,
                    confirmButtonColor: '#3b82f6',
                    confirmButtonText: 'Periksa Formulir',
                    customClass: {
                        popup: '!rounded-2xl',
                        confirmButton: '!rounded-xl !px-5 !py-2 !font-bold !text-xs'
                    }
                });
            } else {
                alert(message);
            }
        }

        // Validasi & Pengiriman Formulir
        form.addEventListener('submit', function(e) {
            // 1. Validasi Tanggal
            const tglInput = form.querySelector('input[name="tanggal"]');
            if (!tglInput || !tglInput.value) {
                e.preventDefault();
                showFormAlert('Tanggal Belum Diisi', 'Tanggal mutasi wajib diisi.', tglInput);
                return;
            }
            const dNow = new Date();
            const todayStr = dNow.getFullYear() + '-' + String(dNow.getMonth() + 1).padStart(2, '0') + '-' + String(dNow.getDate()).padStart(2, '0');
            if (tglInput.value > todayStr) {
                e.preventDefault();
                showFormAlert('Tanggal Tidak Cocok', 'Tanggal mutasi tidak boleh melebihi tanggal hari ini (' + todayStr + ').', tglInput);
                return;
            }

            // 2. Validasi Keterangan Keluar
            if (TAB_ARAH === 'keluar') {
                const purEl = form.querySelector('#purpose-select');
                if (purEl && !purEl.value) {
                    e.preventDefault();
                    showFormAlert('Keterangan Belum Dipilih', 'Keterangan barang keluar wajib dipilih (Penjualan / Sample / Retur Barang).', purEl);
                    return;
                }
                if (purEl && purEl.value === 'Sample') {
                    const psEl = form.querySelector('#ps-select');
                    const pType = form.querySelector('#partner-type');
                    const pSel = form.querySelector('#partner-select');
                    const hasPartner = (pType && pType.value.trim() !== '') || (pSel && pSel.value !== '');
                    const hasPs = psEl && psEl.value.trim() !== '';
                    if (!hasPartner && !hasPs) {
                        e.preventDefault();
                        showFormAlert('Data Sample Belum Lengkap', 'Untuk transaksi Sample, harap pilih Nama PS atau masukkan Customer penerima.', psEl || pType);
                        return;
                    }
                }
            }

            // 3. Validasi Tabel Barang
            const rows = container.querySelectorAll('.row-barang');
            if (rows.length === 0) {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                    renderSearch(searchInput.value || 'a');
                }
                showFormAlert('Barang Masih Kosong', 'Belum ada barang di tabel — cari barang di atas, klik barang, isi Qty di popup, lalu Simpan ke Tabel.', searchInput);
                return;
            }

            // 4. Validasi Qty Tiap Baris
            let rowErrorMsg = null;
            let rowErrorEl = null;
            for (let i = 0; i < rows.length; i++) {
                const tr = rows[i];
                const qInput = tr.querySelector('.input-qty');
                const qVal = parseFloat(qInput?.value);
                const nama = tr.querySelector('.cell-nama')?.textContent?.trim() || ('Baris ke-' + (i + 1));
                if (!qInput || isNaN(qVal) || qVal <= 0) {
                    rowErrorMsg = 'Qty untuk barang "' + nama + '" tidak boleh kosong atau <= 0. Silakan klik ikon ubah (pensil) pada baris tersebut.';
                    rowErrorEl = tr;
                    break;
                }
                if (TAB_ARAH !== 'masuk') {
                    const pid = tr.querySelector('.input-barang')?.value;
                    const p = productOptions.find(x => String(x.id) === String(pid));
                    if (p) {
                        const sisa = parseFloat(p.stok) || 0;
                        if (qVal > sisa) {
                            rowErrorMsg = 'Qty barang "' + nama + '" (' + qVal + ') melebihi sisa stok di gudang (' + fmt(sisa) + '). Silakan sesuaikan jumlahnya.';
                            rowErrorEl = tr;
                            break;
                        }
                    }
                }
            }

            if (rowErrorMsg) {
                e.preventDefault();
                if (rowErrorEl) {
                    rowErrorEl.classList.add('!bg-red-50');
                    setTimeout(() => rowErrorEl.classList.remove('!bg-red-50'), 3000);
                }
                showFormAlert('Qty Tidak Cocok', rowErrorMsg);
                return;
            }

            if (isSubmitting) { e.preventDefault(); return; }
            isSubmitting = true;
            const submitBtn = document.getElementById('simpan-mutasi-btn');
            if (submitBtn) {
                submitBtn.dataset.label = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-[11px]"></i> Menyimpan...';
                setTimeout(() => {
                    submitBtn.disabled = true;
                }, 10);
                setTimeout(() => {
                    isSubmitting = false;
                    submitBtn.disabled = false;
                    if (submitBtn.dataset.label) submitBtn.innerHTML = submitBtn.dataset.label;
                }, 15000);
            }
        });

        // Anti Double-Click Button
        const submitBtnEl = document.getElementById('simpan-mutasi-btn');
        if (submitBtnEl) {
            submitBtnEl.addEventListener('click', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            });
        }

        // Autosave Draft saat Navigasi
        window.addEventListener('pagehide', saveDraft);
        form.querySelectorAll('input[name="tanggal"], input[name="doc_no"], #partner-select, input[name="po_no"], #purpose-select, #ship-address, input[name="expedition"]').forEach(el => {
            el.addEventListener('input', saveDraft);
            el.addEventListener('change', saveDraft);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPencatatanOps);
    } else {
        initPencatatanOps();
    }
</script>
@endpush
