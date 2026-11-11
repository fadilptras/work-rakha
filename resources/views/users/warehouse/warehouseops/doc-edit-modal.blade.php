{{-- Modal Edit Dokumen Mutasi / Surat Jalan (Canvas Konten Terisolasi - Pilihan B) --}}
<div id="editDocModal" data-ui-modal class="modal-content-canvas hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-transparent" onclick="closeEditDocModal()"></div>
    <div class="relative z-10 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-100 w-full max-w-5xl my-auto mx-auto" style="max-height: calc(100% - 1rem);">
        {{-- Header Modal --}}
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/80 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm"><i class="fas fa-truck text-xs"></i></span>
                <div class="min-w-0">
                    <h3 class="text-base font-bold text-slate-800 leading-tight flex items-center gap-2">
                        <span>Edit Delivery Notes</span>
                        <span id="edit-doc-badge-no" class="font-mono text-xs text-blue-600 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-lg"></span>
                    </h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5 truncate">Perbarui data surat jalan dan rincian barang keluar langsung tanpa pindah halaman.</p>
                </div>
            </div>
            <button type="button" onclick="closeEditDocModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-colors flex items-center justify-center shrink-0" title="Tutup"><i class="fas fa-times text-xs"></i></button>
        </div>

        {{-- Loading Spinner --}}
        <div id="edit-doc-loading" class="py-16 flex flex-col items-center justify-center gap-3 text-slate-400">
            <div class="ks-spin"></div>
            <p class="text-xs font-bold">Memuat data dokumen...</p>
        </div>

        {{-- Form Content --}}
        <div id="edit-doc-content" class="hidden flex-1 flex flex-col min-h-0 overflow-hidden">
            <form id="edit-doc-form" method="POST" action="" class="flex-1 flex flex-col min-h-0">
                @csrf
                @method('PUT')
                <input type="hidden" name="asal" value="ops">
                <input type="hidden" name="tab" value="mutasi">

                <div class="p-6 overflow-y-auto space-y-4 flex-1">
                    {{-- Alert Error --}}
                    <div id="edit-doc-error-alert" class="hidden bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs font-semibold"></div>

                    {{-- Baris 1: Tanggal | No. SJ | Keterangan | Customer | No. PO --}}
                    <div class="grid grid-cols-12 gap-2 items-end">
                        <div class="col-span-6 md:col-span-2">
                            <label class="ui-label">Tanggal Keluar <span class="text-red-500">*</span></label>
                            <input type="date" lang="id-ID" name="tanggal" id="edit-doc-tanggal" required max="{{ today()->toDateString() }}" class="ui-input-date" style="height:30px;font-size:12px;">
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <label class="ui-label">No. SJ <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input type="text" name="doc_no" id="edit-doc-doc-no" placeholder="SJ.YYYYMMDD.XXX" class="ui-input ui-input--sm">
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <label class="ui-label">Keterangan <span class="text-red-500">*</span></label>
                            <input list="edit-doc-purpose-options" name="purpose" id="edit-doc-purpose-select" required maxlength="50" placeholder="Pilih / Ketik Keterangan" class="ui-input ui-input--sm !bg-white" autocomplete="off">
                            <datalist id="edit-doc-purpose-options">
                                @foreach($purposes as $p)
                                    <option value="{{ $p }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-span-12 md:col-span-3" id="edit-doc-partner-wrap">
                            <label class="ui-label" id="edit-doc-partner-label">Customer <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <div class="relative">
                                <input type="text" name="partner_name" id="edit-doc-partner-type" autocomplete="off" spellcheck="false" placeholder="Ketik Nama Customer (Opsional)" class="ui-input ui-input--sm !bg-white">
                                <select name="customer_id" id="edit-doc-partner-select" class="hidden" tabindex="-1" aria-hidden="true">
                                    <option value="">— Pilih Customer —</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" data-addr="{{ $customerAddressMap[$c->id] ?? '' }}">{{ $c->customer_name }}</option>
                                    @endforeach
                                </select>
                                <div id="edit-doc-partner-suggestions" class="hidden absolute left-0 right-0 z-40 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden max-h-56 overflow-y-auto divide-y divide-slate-100"></div>
                            </div>
                            <select id="edit-doc-supplier-src" class="hidden" aria-hidden="true" tabindex="-1">
                                <option value="">— Pilih Supplier —</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" data-addr="{{ $supplierAddressMap[$s->id] ?? '' }}">{{ $s->supplier_name }}</option>
                                @endforeach
                            </select>
                            <div id="edit-doc-partner-none" class="hidden">
                                <label class="ui-label">Nama PS <span class="text-red-500">*</span></label>
                                <select name="ps_name" id="edit-doc-ps-select" class="ui-input ui-input--sm !bg-white">
                                    <option value="">— Pilih Nama PS —</option>
                                    @foreach($psList ?? [] as $ps)
                                        <option value="{{ $ps }}">{{ $ps }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium mt-0.5">Opsional — boleh dikosongkan. Nama baru otomatis disimpan ke master.</p>
                        </div>
                        <div class="col-span-12 md:col-span-3">
                            <label class="ui-label">No. PO / Referensi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input type="text" name="po_no" id="edit-doc-po-no" maxlength="100" placeholder="Contoh: PO-REF-001 (Opsional)" class="ui-input ui-input--sm" autocomplete="off">
                        </div>
                    </div>

                    {{-- Baris 2: Ekspedisi + Alamat Kirim dalam 1 baris --}}
                    <div class="mt-2 sj-exp-addr">
                        <div style="min-width:0;">
                            <label class="ui-label">Ekspedisi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input list="edit-doc-expedition-options" name="expedition" id="edit-doc-expedition" maxlength="100" placeholder="Contoh: JNE / GoSend (Opsional)" class="ui-input ui-input--sm" autocomplete="off">
                            <datalist id="edit-doc-expedition-options">
                                @foreach($expeditions as $e)
                                    <option value="{{ $e }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div style="min-width:0;">
                            <label class="ui-label">Alamat Kirim <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <div class="relative">
                                <input type="text" name="ship_address" id="edit-doc-ship-address" maxlength="2000" placeholder="Contoh: Jl. Sudirman No. 1, Jakarta (Dapat Disesuaikan)" class="ui-input ui-input--sm" autocomplete="off">
                                <p id="edit-doc-addr-warn" class="hidden absolute left-0 z-10 text-[10px] text-amber-600 font-bold mt-0.5" style="top:100%"><i class="fas fa-exclamation-triangle mr-1"></i>Master belum punya alamat — ketik manual atau lengkapi di master.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Rincian barang --}}
                    <div class="mt-3 pt-3 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider whitespace-nowrap">Rincian Barang Keluar</h4>
                            <div class="flex-1 h-px bg-slate-100"></div>
                            <span id="edit-doc-sum-info" class="ui-info" style="height:26px;font-size:11px;">0 baris · total 0</span>
                        </div>

                        <div class="relative mt-2">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-blue-300 text-xs pointer-events-none"></i>
                                <input type="text" id="edit-doc-product-search" placeholder="Cari Nama atau Kode Barang (Klik Hasil Untuk Memilih)..." title="Ketik nama atau kode barang, lalu klik hasilnya untuk mengisi Qty, Batch & Exp lewat popup." class="ui-search !bg-white hover:!bg-white !text-slate-700" style="height:32px;padding-left:36px;" autocomplete="off">
                            </div>
                            <div id="edit-doc-product-search-results" class="hidden absolute left-0 right-0 z-30 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden max-h-64 overflow-y-auto divide-y divide-slate-100"></div>
                        </div>

                        {{-- Tabel scrollable --}}
                        <div class="mt-2 border border-slate-200 rounded-xl bg-white" style="overflow:hidden;">
                            <div class="sj-items-wrap">
                                <table class="w-full text-xs table-bordered" style="min-width:880px;">
                                    <thead>
                                        <tr class="bg-slate-50/80 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                                            <th class="px-3 py-2 text-center w-12 font-bold">No</th>
                                            <th class="px-3 py-2 text-left font-bold">Nama Barang</th>
                                            <th class="px-3 py-2 text-center w-24 font-bold">Kode</th>
                                            <th class="px-3 py-2 text-right w-24 font-bold">Qty</th>
                                            <th class="px-3 py-2 text-center w-20 font-bold">Satuan</th>
                                            <th class="px-3 py-2 text-left w-32 font-bold">No. Batch</th>
                                            <th class="px-3 py-2 text-center w-32 font-bold">Tgl Exp</th>
                                            <th class="px-3 py-2 text-left w-40 font-bold">Keterangan</th>
                                            <th class="px-3 py-2 text-center w-24 font-bold">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edit-doc-rows-container" class="divide-y divide-slate-100"></tbody>
                                </table>
                            </div>
                            <div id="edit-doc-empty-barang" class="px-4 py-3 text-center bg-slate-50/50 border-t border-dashed border-slate-200">
                                <p class="text-xs font-bold text-slate-500"><i class="fas fa-box-open text-slate-300 mr-1.5"></i>Belum ada barang — cari di atas lalu klik untuk menambahkan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Simpan / Batal --}}
                <div class="px-6 py-3.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between shrink-0">
                    <button type="button" onclick="closeEditDocModal()" class="h-[34px] px-4 rounded-lg bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-colors">Batal</button>
                    <button type="submit" id="edit-doc-submit-btn" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-5 rounded-lg shadow-sm hover:shadow-md transition-all h-[34px] disabled:opacity-60 disabled:cursor-not-allowed">
                        <i class="fas fa-save text-[11px]"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Sub-Modal Tambah / Ubah Rincian Barang Dokumen --}}
<div id="editDocItemModal" class="hidden fixed inset-0 z-[120] flex items-center justify-center p-4">
    <div id="editDocItemModalBackdrop" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm opacity-0 transition-opacity duration-200" onclick="closeEditDocItemModal()"></div>
    <div id="editDocItemModalPanel" class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col opacity-0 scale-95 transition-all duration-200">
        <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm"><i class="fas fa-box text-xs"></i></span>
                <div class="min-w-0">
                    <h5 id="edit-doc-item-title" class="font-bold text-sm text-slate-800 leading-tight">Ubah Rincian Barang</h5>
                    <p class="text-[11px] text-slate-400 font-medium mt-0.5">Isi Jumlah Qty, No. Batch & Tgl Expired, lalu simpan ke tabel.</p>
                </div>
            </div>
            <button type="button" onclick="closeEditDocItemModal()" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0" title="Tutup"><i class="fas fa-times text-xs"></i></button>
        </div>
        <div class="p-5 space-y-4 overflow-y-auto">
            <div>
                <label class="ui-label">Nama Barang</label>
                <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                    <div id="edit-doc-m-nama" class="text-sm font-bold text-slate-800 leading-snug break-words">—</div>
                </div>
                <input type="hidden" id="edit-doc-m-product-id" value="">
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="ui-label">Kode Barang</label>
                    <input type="text" id="edit-doc-m-kode" readonly tabindex="-1" placeholder="—" class="ui-input ui-input--sm !bg-slate-50 !text-slate-500 !border-slate-200 text-center cursor-not-allowed">
                </div>
                <div>
                    <label class="ui-label">Satuan</label>
                    <input type="text" id="edit-doc-m-satuan" readonly tabindex="-1" placeholder="—" class="ui-input ui-input--sm !bg-slate-50 !text-slate-500 !border-slate-200 text-center cursor-not-allowed">
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <label class="ui-label !mb-0">Qty <span class="text-red-500">*</span></label>
                    <span id="edit-doc-m-stok-info" class="text-[10px] font-bold"></span>
                </div>
                <input type="number" id="edit-doc-m-qty" min="0.01" step="any" placeholder="0" class="ui-input !bg-white text-right font-bold !text-slate-800" style="height:38px;">
                <p id="edit-doc-m-batch-info" class="text-[10px] text-slate-400 font-medium mt-1.5 min-h-[14px]"></p>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="ui-label">No. Batch <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" id="edit-doc-m-batch" list="edit-doc-m-batch-list" maxlength="100" placeholder="Contoh: B-2026-001" class="ui-input ui-input--sm !bg-white !text-slate-800" autocomplete="off">
                    <datalist id="edit-doc-m-batch-list"></datalist>
                </div>
                <div>
                    <label class="ui-label">Tgl Expired <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="date" lang="id-ID" id="edit-doc-m-exp" class="ui-input ui-input--sm !bg-white !text-slate-800">
                </div>
            </div>
            <div>
                <label class="ui-label">Keterangan Baris <span class="text-slate-400 font-normal">(Opsional)</span></label>
                <input type="text" id="edit-doc-m-note" maxlength="500" placeholder="Keterangan khusus baris ini..." class="ui-input ui-input--sm !bg-white !text-slate-800" autocomplete="off">
            </div>

            <p id="edit-doc-m-error" class="hidden text-[11px] font-bold text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2.5"></p>
        </div>
        <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex gap-2 justify-end shrink-0">
            <button type="button" onclick="closeEditDocItemModal()" class="h-[36px] px-4 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
            <button type="button" id="edit-doc-m-save" class="h-[36px] px-5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-colors"><i class="fas fa-check mr-1.5"></i>Simpan ke Tabel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    // ---- Edit Document Modal Runtime ----
    const docModalEl = document.getElementById('editDocModal');
    const loadingEl = document.getElementById('edit-doc-loading');
    const contentEl = document.getElementById('edit-doc-content');
    const formEl = document.getElementById('edit-doc-form');
    const rowsContainer = document.getElementById('edit-doc-rows-container');
    const emptyBox = document.getElementById('edit-doc-empty-barang');
    const errAlert = document.getElementById('edit-doc-error-alert');
    const submitBtn = document.getElementById('edit-doc-submit-btn');

    // Sub-modal item elements
    const itemModal = document.getElementById('editDocItemModal');
    const itemModalPanel = document.getElementById('editDocItemModalPanel');
    const itemModalBackdrop = document.getElementById('editDocItemModalBackdrop');
    const mTitle = document.getElementById('edit-doc-item-title');
    const mNama = document.getElementById('edit-doc-m-nama');
    const mPid = document.getElementById('edit-doc-m-product-id');
    const mKode = document.getElementById('edit-doc-m-kode');
    const mSatuan = document.getElementById('edit-doc-m-satuan');
    const mQty = document.getElementById('edit-doc-m-qty');
    const mBatch = document.getElementById('edit-doc-m-batch');
    const mBatchList = document.getElementById('edit-doc-m-batch-list');
    const mBatchInfo = document.getElementById('edit-doc-m-batch-info');
    const mExp = document.getElementById('edit-doc-m-exp');
    const mNote = document.getElementById('edit-doc-m-note');
    const mStok = document.getElementById('edit-doc-m-stok-info');
    const mErr = document.getElementById('edit-doc-m-error');
    const mSave = document.getElementById('edit-doc-m-save');

    // Header dynamic elements
    const purposeSel = document.getElementById('edit-doc-purpose-select');
    const partnerSel = document.getElementById('edit-doc-partner-select');
    const partnerLabel = document.getElementById('edit-doc-partner-label');
    const partnerWrap = document.getElementById('edit-doc-partner-wrap');
    const shipInput = document.getElementById('edit-doc-ship-address');
    const addrWarn = document.getElementById('edit-doc-addr-warn');
    const customerOptsHTML = partnerSel ? partnerSel.innerHTML : '';
    const supplierSrc = document.getElementById('edit-doc-supplier-src');
    const partnerType = document.getElementById('edit-doc-partner-type');
    const partySuggestBox = document.getElementById('edit-doc-partner-suggestions');
    const psWrap = document.getElementById('edit-doc-partner-none');
    const psSel = document.getElementById('edit-doc-ps-select');

    // Search bar elements
    const searchInput = document.getElementById('edit-doc-product-search');
    const searchBox = document.getElementById('edit-doc-product-search-results');

    const BATCH_URL = '{{ route("warehouse.mutation.batches") }}';
    const productsData = @json($productOptionsData ?? []);

    let editingTr = null;
    let modalBatches = [];

    function fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(n)); }
    function esc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

    function editDocRecalc() {
        let n = 0, q = 0;
        if (rowsContainer) {
            rowsContainer.querySelectorAll('.edit-doc-row').forEach(row => {
                n++;
                q += parseFloat(row.querySelector('.input-qty')?.value) || 0;
            });
        }
        const sumEl = document.getElementById('edit-doc-sum-info');
        if (sumEl) sumEl.textContent = n + ' baris · total ' + fmt(q);
        if (emptyBox) emptyBox.style.display = n ? 'none' : 'block';
    }

    function editDocRenumber() {
        if (!rowsContainer) return;
        rowsContainer.querySelectorAll('.edit-doc-row').forEach((row, i) => {
            const noCell = row.querySelector('.row-no');
            if (noCell) noCell.textContent = i + 1;
        });
    }

    function showDocError(msg) {
        if (!errAlert) return;
        if (!msg) {
            errAlert.classList.add('hidden');
            errAlert.innerHTML = '';
        } else {
            errAlert.innerHTML = '<i class="fas fa-exclamation-circle mr-1.5"></i>' + msg;
            errAlert.classList.remove('hidden');
        }
    }

    // Dynamic Partner / Address Logic
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
            partySuggestBox.innerHTML = '<div class="px-3 py-2.5 text-[11px] text-slate-400 font-semibold">Tidak ada hasil — nama baru akan disimpan otomatis.</div>';
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
    }

    function syncTypeToSelect() {
        if (!partnerType || !partnerSel) return;
        const typed = (partnerType.value || '').trim().toLowerCase();
        let match = '';
        partyOptions().forEach(o => {
            if (o.textContent.trim().toLowerCase() === typed) match = o.value;
        });
        if (partnerSel.value !== match) partnerSel.value = match;
        fillAddress();
    }

    let lastPartyMode = null;
    function applyPurpose() {
        if (!purposeSel || !partnerSel) return;
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
            if (partnerType) partnerType.value = '';
        }
        const isInternal = lower === 'internal';
        const isSample = (lower === 'sample' || lower === 'saple' || lower === 'sampel');
        if (partnerWrap) partnerWrap.classList.toggle('hidden', isInternal);
        if (partnerSel) {
            partnerSel.classList.add('hidden');
            partnerSel.disabled = isInternal || isSample;
            partnerSel.required = false;
            if (isSample || isInternal) partnerSel.value = '';
        }
        if (partnerType) {
            partnerType.classList.toggle('hidden', isSample);
            partnerType.disabled = isInternal || isSample;
            if (isInternal || isSample) partnerType.value = '';
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
        purposeSel.addEventListener('input', applyPurpose);
        purposeSel.addEventListener('change', applyPurpose);
    }
    if (partnerSel) partnerSel.addEventListener('change', fillAddress);
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
        partySuggestBox.addEventListener('click', e => {
            const btn = e.target.closest('[data-value]');
            if (btn) chooseParty(btn.dataset.value);
        });
    }
    document.addEventListener('click', e => {
        if (partySuggestBox && !partySuggestBox.classList.contains('hidden') &&
            !partySuggestBox.contains(e.target) && e.target !== partnerType) {
            hidePartySuggestions();
        }
    });

    // Product Search
    function renderProductSearch(kw = '') {
        if (!searchBox) return;
        const keyword = kw.trim().toLowerCase();
        const list = (productsData || []).filter(p => {
            if (!keyword) return false;
            return (p.nama || '').toLowerCase().includes(keyword) || (p.kode || '').toLowerCase().includes(keyword);
        }).slice(0, 20);
        if (!keyword) {
            searchBox.classList.add('hidden');
            searchBox.innerHTML = '';
            return;
        }
        if (!list.length) {
            searchBox.innerHTML = '<div class="px-4 py-3 text-xs text-slate-400 font-semibold">Tidak ketemu — coba kata kunci lain.</div>';
        } else {
            searchBox.innerHTML = list.map(p =>
                `<button type="button" data-pid="${p.id}" class="w-full text-left px-4 py-2.5 hover:bg-blue-50/70 flex items-center justify-between gap-3 transition-colors">` +
                `<span class="min-w-0"><span class="block text-xs font-bold text-slate-800 truncate">${esc(p.nama)}</span>` +
                `<span class="block text-[10px] text-slate-400 font-semibold mt-0.5">${esc(p.kode || '—')} · ${esc(p.unit || '')}</span></span>` +
                `<span class="text-[10px] font-bold ${parseFloat(p.stok) > 0 ? 'text-emerald-600 bg-emerald-50 border border-emerald-200' : 'text-red-500 bg-red-50 border border-red-200'} whitespace-nowrap px-2 py-1 rounded-lg">stok ${esc(p.stok)}</span></button>`
            ).join('');
        }
        searchBox.classList.remove('hidden');
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => renderProductSearch(searchInput.value));
        searchInput.addEventListener('focus', () => renderProductSearch(searchInput.value));
        document.addEventListener('click', e => {
            if (searchBox && !searchBox.classList.contains('hidden')) {
                if (!searchBox.contains(e.target) && e.target !== searchInput) searchBox.classList.add('hidden');
            }
        });
    }
    if (searchBox) {
        searchBox.addEventListener('click', e => {
            const btn = e.target.closest('[data-pid]');
            if (!btn) return;
            searchBox.classList.add('hidden');
            if (searchInput) searchInput.value = '';
            openEditDocItemModal(btn.dataset.pid, null);
        });
    }

    // Row Table Renderer
    function editDocAddRow(data) {
        if (!data || !data.product_id) return;
        const p = productsData.find(x => String(x.id) === String(data.product_id)) || {
            id: data.product_id,
            nama: data.barang || 'Produk',
            kode: data.kode || '-',
            unit: data.unit || '',
            stok: 0
        };

        const tr = document.createElement('tr');
        tr.className = 'edit-doc-row hover:bg-blue-50/50 transition-colors bg-white';
        tr.innerHTML =
            '<td class="px-3 py-2 text-center text-slate-400 font-semibold row-no"></td>' +
            '<td class="px-3 py-2"><span class="font-bold text-slate-800 cell-nama"></span>' +
            '<input type="hidden" name="product_id[]" class="input-barang">' +
            '<input type="hidden" name="arah[]" value="keluar"></td>' +
            '<td class="px-3 py-2 text-center text-slate-500 font-semibold cell-kode"></td>' +
            '<td class="px-3 py-2 text-right font-bold text-slate-800 cell-qty"><span class="qty-txt"></span>' +
            '<input type="hidden" name="qty[]" class="input-qty"></td>' +
            '<td class="px-3 py-2 text-center text-slate-500 cell-unit"></td>' +
            '<td class="px-3 py-2 text-slate-600 font-semibold cell-batch"><span class="batch-txt"></span>' +
            '<input type="hidden" name="batch_no[]" class="input-batch"></td>' +
            '<td class="px-3 py-2 text-center text-slate-500 cell-exp"><span class="exp-txt"></span>' +
            '<input type="hidden" name="expiry_date[]" class="input-ed"></td>' +
            '<td class="px-3 py-2 text-slate-500 text-[11px] cell-note"><span class="note-txt"></span>' +
            '<input type="hidden" name="keterangan[]" class="input-note"></td>' +
            '<td class="px-3 py-2 text-center whitespace-nowrap">' +
            '<button type="button" title="Ubah" class="btn-edit inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 h-[26px] rounded-lg transition-colors mr-1 cursor-pointer"><i class="fas fa-pen text-[10px]"></i></button>' +
            '<button type="button" title="Hapus" class="btn-remove inline-flex items-center gap-1 text-[11px] font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 px-2 h-[26px] rounded-lg transition-colors cursor-pointer"><i class="fas fa-trash-alt text-[10px]"></i></button></td>';

        tr.querySelector('.cell-nama').textContent = p.nama;
        tr.querySelector('.input-barang').value = p.id;
        tr.querySelector('.cell-kode').textContent = p.kode;
        tr.querySelector('.qty-txt').textContent = (data.qty || '') + '';
        tr.querySelector('.input-qty').value = data.qty || '';
        tr.querySelector('.cell-unit').textContent = p.unit;
        tr.querySelector('.batch-txt').textContent = (data.batch_no && String(data.batch_no).trim()) ? data.batch_no : '-';
        tr.querySelector('.input-batch').value = (data.batch_no && String(data.batch_no).trim() !== '-') ? data.batch_no : '';
        tr.querySelector('.exp-txt').textContent = data.expiry_date || '—';
        tr.querySelector('.input-ed').value = data.expiry_date || '';
        tr.querySelector('.note-txt').textContent = data.keterangan || '—';
        tr.querySelector('.input-note').value = data.keterangan || '';

        tr.querySelector('.btn-remove').addEventListener('click', () => {
            tr.remove();
            editDocRenumber();
            editDocRecalc();
        });
        tr.querySelector('.btn-edit').addEventListener('click', () => {
            openEditDocItemModal(p.id, tr);
        });

        rowsContainer.appendChild(tr);
        editDocRenumber();
        editDocRecalc();
    }

    // Sub-Modal Item Logic
    function showItemErr(msg, targetEl = null) {
        if (mQty) mQty.classList.remove('!border-red-500', '!ring-2', '!ring-red-200');
        if (!mErr) return;
        if (!msg) {
            mErr.classList.add('hidden');
            mErr.innerHTML = '';
        } else {
            mErr.innerHTML = '<i class="fas fa-exclamation-circle mr-1.5"></i>' + esc(msg);
            mErr.classList.remove('hidden');
            if (targetEl) {
                targetEl.classList.add('!border-red-500', '!ring-2', '!ring-red-200');
                try { targetEl.focus(); } catch (e) {}
            }
        }
    }

    function refreshModalStock() {
        const p = productsData.find(x => String(x.id) === String(mPid?.value));
        if (!p || !mStok) { if (mStok) mStok.innerHTML = ''; return; }
        const sisa = parseFloat(p.stok) || 0;
        const qty = parseFloat(mQty?.value) || 0;
        const sisaTxt = 'Sisa master: ' + fmt(sisa) + (p.unit ? ' ' + p.unit : '');
        mStok.innerHTML = '<span class="inline-flex items-center gap-1 text-[10px] font-bold border rounded-lg px-2 py-0.5 ' + (sisa <= 0 ? 'text-red-700 bg-red-50 border-red-200' : 'text-emerald-700 bg-emerald-50 border-emerald-200') + '">' + esc(sisaTxt) + '</span>';
    }

    function refreshModalBatchInfo() {
        if (!mBatchInfo || !mBatch) return;
        const val = (mBatch.value || '').trim();
        const found = modalBatches.find(b => String(b.batch_no) === val);
        if (!val) mBatchInfo.textContent = '';
        else if (found) mBatchInfo.textContent = 'Sisa ' + found.qty + (found.expiry_date ? ' — ED ' + found.expiry_label : ' — tanpa ED');
        else mBatchInfo.textContent = 'Batch baru / tidak ada di riwayat.';
    }

    async function loadModalBatches(productId) {
        modalBatches = [];
        if (mBatchList) mBatchList.innerHTML = '';
        refreshModalBatchInfo();
        if (!productId) return;
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
            const cur = (mBatch?.value || '').trim();
            const hit = modalBatches.find(b => String(b.batch_no) === cur);
            if (hit?.expiry_date && mExp && !mExp.value) mExp.value = hit.expiry_date;
        } catch (e) {}
    }

    window.openEditDocItemModal = function(productId, tr = null) {
        const p = productsData.find(x => String(x.id) === String(productId));
        if (!p) return;
        editingTr = tr || null;
        if (itemModal && itemModal.parentElement !== document.body) {
            document.body.appendChild(itemModal);
        }
        if (mPid) mPid.value = p.id;
        if (mNama) mNama.textContent = p.nama || '—';
        if (mKode) mKode.value = p.kode || '';
        if (mSatuan) mSatuan.value = p.unit || '';
        if (mTitle) mTitle.textContent = (tr ? 'Ubah Rincian Barang' : 'Tambah Barang ke Dokumen');

        if (tr) {
            if (mQty) mQty.value = tr.querySelector('.input-qty')?.value || '';
            if (mBatch) mBatch.value = tr.querySelector('.input-batch')?.value || '';
            if (mExp) mExp.value = tr.querySelector('.input-ed')?.value || '';
            if (mNote) mNote.value = tr.querySelector('.input-note')?.value || '';
        } else {
            if (mQty) mQty.value = '';
            if (mBatch) mBatch.value = '';
            if (mExp) mExp.value = '';
            if (mNote) mNote.value = '';
        }
        showItemErr('');
        refreshModalStock();
        refreshModalBatchInfo();
        if (itemModal) {
            itemModal.classList.remove('hidden');
            requestAnimationFrame(() => requestAnimationFrame(() => {
                itemModalBackdrop?.classList.remove('opacity-0');
                itemModalPanel?.classList.remove('opacity-0', 'scale-95');
            }));
        }
        loadModalBatches(p.id);
        setTimeout(() => { mQty?.focus(); mQty?.select?.(); }, 60);
    };

    window.closeEditDocItemModal = function() {
        if (!itemModal) return;
        itemModalBackdrop?.classList.add('opacity-0');
        itemModalPanel?.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            itemModal.classList.add('hidden');
            editingTr = null;
            showItemErr('');
        }, 180);
    };

    if (mQty) {
        mQty.addEventListener('input', () => {
            mQty.classList.remove('!border-red-500', '!ring-2', '!ring-red-200');
            showItemErr('');
        });
    }
    if (mBatch) {
        mBatch.addEventListener('input', () => {
            refreshModalBatchInfo();
            const hit = modalBatches.find(b => String(b.batch_no) === (mBatch.value || '').trim());
            if (hit?.expiry_date && mExp) mExp.value = hit.expiry_date;
        });
        mBatch.addEventListener('change', refreshModalBatchInfo);
    }
    if (mSave) {
        mSave.addEventListener('click', () => {
            const pid = mPid?.value;
            const p = productsData.find(x => String(x.id) === String(pid));
            if (!p) { showItemErr('Barang tidak valid.'); return; }
            const qVal = (mQty?.value || '').trim();
            const qNum = parseFloat(qVal);
            if (!qVal || isNaN(qNum) || qNum <= 0) {
                showItemErr('Jumlah Qty wajib diisi angka lebih dari 0.', mQty);
                return;
            }
            const bVal = (mBatch?.value || '').trim();
            const eVal = mExp?.value || '';
            const nVal = (mNote?.value || '').trim();

            if (editingTr) {
                editingTr.querySelector('.qty-txt').textContent = qVal;
                editingTr.querySelector('.input-qty').value = qVal;
                editingTr.querySelector('.batch-txt').textContent = bVal || '-';
                editingTr.querySelector('.input-batch').value = bVal;
                editingTr.querySelector('.exp-txt').textContent = eVal || '—';
                editingTr.querySelector('.input-ed').value = eVal;
                editingTr.querySelector('.note-txt').textContent = nVal || '—';
                editingTr.querySelector('.input-note').value = nVal;
                editDocRecalc();
            } else {
                editDocAddRow({
                    product_id: p.id,
                    qty: qVal,
                    batch_no: bVal,
                    expiry_date: eVal,
                    keterangan: nVal
                });
            }
            closeEditDocItemModal();
        });
    }

    // Modal Document Open & Close
    window.openEditDocModal = async function(docId) {
        if (!docModalEl) return;
        if (docModalEl.parentElement !== document.body) {
            document.body.appendChild(docModalEl);
        }
        showDocError('');
        loadingEl.classList.remove('hidden');
        contentEl.classList.add('hidden');
        docModalEl.classList.remove('hidden');
        docModalEl.classList.add('flex');
        document.body.style.overflow = 'hidden';

        try {
            const res = await fetch('{{ route("warehouse.document", 0) }}'.replace('/0', '/' + docId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal memuat data dokumen.');
            }
            const d = data.data;
            if (!d.can_edit) {
                throw new Error('Dokumen ini tidak dapat diedit (hanya dokumen keluar berstatus aktif yang bisa diedit).');
            }

            // Populate form
            formEl.action = d.update_url || '{{ route("warehouse.mutation.doc.update", 0) }}'.replace('/0', '/' + d.id);
            const badgeEl = document.getElementById('edit-doc-badge-no');
            if (badgeEl) badgeEl.textContent = (d.doc_no && d.doc_no !== '-') ? ('NO. ' + d.doc_no) : '';

            document.getElementById('edit-doc-tanggal').value = d.mutation_date_raw || '';
            document.getElementById('edit-doc-doc-no').value = (d.doc_no && d.doc_no !== '-') ? d.doc_no : '';
            document.getElementById('edit-doc-purpose-select').value = d.purpose_raw || (d.purpose !== '-' ? d.purpose : '');
            document.getElementById('edit-doc-partner-type').value = d.partner_name || '';
            document.getElementById('edit-doc-partner-select').value = d.customer_id || '';
            document.getElementById('edit-doc-supplier-src').value = d.supplier_id || '';
            document.getElementById('edit-doc-ps-select').value = d.ps_name || '';
            document.getElementById('edit-doc-po-no').value = (d.po_no && d.po_no !== '-') ? d.po_no : '';
            document.getElementById('edit-doc-expedition').value = (d.expedition && d.expedition !== '-') ? d.expedition : '';
            document.getElementById('edit-doc-ship-address').value = (d.ship_address && d.ship_address !== '-') ? d.ship_address : '';

            applyPurpose();
            if (d.customer_id && partnerSel) {
                partnerSel.value = d.customer_id;
            } else if (d.supplier_id && partnerSel) {
                partnerSel.value = d.supplier_id;
            }

            // Populate lines
            rowsContainer.innerHTML = '';
            if (d.lines && d.lines.length) {
                d.lines.forEach(line => {
                    editDocAddRow({
                        product_id: line.product_id,
                        barang: line.barang,
                        kode: line.kode,
                        unit: line.unit,
                        qty: line.qty,
                        batch_no: line.batch_no_raw || (line.batch_no !== '-' ? line.batch_no : ''),
                        expiry_date: line.expiry_date_raw || '',
                        keterangan: line.keterangan_raw || (line.keterangan !== '-' ? line.keterangan : '')
                    });
                });
            } else {
                editDocRecalc();
            }

            loadingEl.classList.add('hidden');
            contentEl.classList.remove('hidden');
        } catch (err) {
            loadingEl.innerHTML = '<div class="py-16 text-center px-4"><p class="text-sm font-bold text-red-600">' + esc(err.message || 'Gagal memuat dokumen.') + '</p><button type="button" onclick="closeEditDocModal()" class="mt-3 px-4 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">Tutup</button></div>';
        }
    };

    window.closeEditDocModal = function() {
        if (!docModalEl) return;
        docModalEl.classList.add('hidden');
        docModalEl.classList.remove('flex');
        document.body.style.overflow = '';
        closeEditDocItemModal();
    };

    // Form Submit Handler (AJAX)
    if (formEl) {
        formEl.addEventListener('submit', async function(e) {
            e.preventDefault();
            const rows = rowsContainer ? rowsContainer.querySelectorAll('.edit-doc-row') : [];
            if (!rows.length) {
                showDocError('Tabel barang masih kosong. Silakan tambahkan minimal 1 barang.');
                return;
            }
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="ks-spin !w-3.5 !h-3.5 !border-2 mr-1.5 inline-block"></div> Menyimpan...';
            }
            showDocError('');
            try {
                const formData = new FormData(formEl);
                const res = await fetch(formEl.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    let errMsg = data.message || 'Gagal menyimpan perubahan dokumen.';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    throw new Error(errMsg);
                }
                closeEditDocModal();
                // Refresh halaman aktif dengan cepat
                window.location.reload();
            } catch (err) {
                showDocError(err.message || 'Terjadi kesalahan saat menyimpan.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save text-[11px]"></i> Simpan Perubahan';
                }
            }
        });
    }

    // Helper Dispatcher for Mutasi Row Click
    window.handleEditMutationClick = function(btn) {
        const editData = JSON.parse(btn.dataset.edit || '{}');
        if (btn.dataset.docId && (!editData.arah || editData.arah === 'Keluar')) {
            openEditDocModal(btn.dataset.docId);
        } else {
            openEditMutasiModal(editData);
        }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (itemModal && !itemModal.classList.contains('hidden')) {
                closeEditDocItemModal();
                return;
            }
            if (docModalEl && !docModalEl.classList.contains('hidden')) {
                closeEditDocModal();
            }
        }
    });
})();
</script>
@endpush
