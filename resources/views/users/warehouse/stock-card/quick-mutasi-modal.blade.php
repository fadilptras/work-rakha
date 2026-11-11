{{-- Modal Input Cepat Barang Masuk & Keluar langsung dari popup Kartu Stock --}}
<div id="kartuQuickMutasiModal" data-ui-modal class="modal-content-canvas modal-nested hidden" style="z-index: 10050 !important;" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-transparent" onclick="closeKartuQuickMutasi()"></div>
    <div class="relative z-10 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200/90 w-full max-w-lg my-auto mx-auto" style="max-height: min(85vh, 560px);">
        {{-- Header Modal --}}
        <div id="qmHeader" class="px-5 py-3 border-b border-slate-100 flex justify-between items-center gap-3 shrink-0 bg-slate-50">
            <div class="flex items-center gap-2.5 min-w-0">
                <span id="qmIconWrap" class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 bg-emerald-100 text-emerald-700 shadow-2xs">
                    <i id="qmIcon" class="fas fa-plus text-xs"></i>
                </span>
                <div class="min-w-0">
                    <h3 id="qmTitle" class="text-sm font-black text-slate-800 leading-tight">Catat Transaksi</h3>
                    <p id="qmSubtitle" class="text-[11px] text-slate-500 font-medium mt-0.5">Tambah / keluarkan stok barang secara langsung.</p>
                </div>
            </div>
            <button type="button" onclick="closeKartuQuickMutasi()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center shrink-0 cursor-pointer" title="Tutup (Esc)">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        {{-- Product Summary Badge --}}
        <div class="px-5 py-2.5 bg-blue-50/50 border-b border-blue-100/70 flex items-center justify-between gap-3 shrink-0">
            <div class="min-w-0">
                <div id="qmKode" class="text-[10px] font-mono font-bold tracking-wider uppercase text-blue-700"></div>
                <div id="qmNama" class="text-xs font-black text-slate-900 truncate"></div>
            </div>
            <div class="text-right shrink-0 bg-white px-2.5 py-1 rounded-lg border border-blue-200 shadow-2xs">
                <div class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Stok Saat Ini</div>
                <div class="text-xs font-black text-slate-800"><span id="qmStok">0</span> <span id="qmUnit" class="text-[10px] font-bold text-slate-500"></span></div>
            </div>
        </div>

        {{-- Form Isi --}}
        <form id="kartuQuickMutasiForm" method="POST" class="p-4 sm:p-5 overflow-y-auto space-y-3 flex-1 min-h-0">
            @csrf
            <input type="hidden" name="arah" id="qmArah" value="masuk">
            <input type="hidden" name="product_id" id="qmProductId" value="">

            {{-- Alert error --}}
            <div id="qmAlert" class="hidden px-3.5 py-2 rounded-xl text-xs font-bold bg-rose-50 border border-rose-200 text-rose-700"></div>

            {{-- Row: Tanggal & Qty --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ui-label">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" lang="id-ID" name="tanggal" id="qmTanggal" required max="{{ today()->toDateString() }}" value="{{ today()->toDateString() }}" class="ui-input !bg-white">
                </div>
                <div>
                    <label class="ui-label">Jumlah (Qty) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="qty" id="qmQty" step="any" min="0.01" required placeholder="0" class="ui-input !bg-white pr-12 font-bold text-slate-800">
                        <span id="qmInputUnit" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 uppercase pointer-events-none"></span>
                    </div>
                </div>
            </div>

            {{-- Row: Tujuan / Jenis & Partner --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ui-label">Tujuan / Jenis <span class="text-red-500">*</span></label>
                    <select name="purpose" id="qmPurpose" class="ui-input !bg-white font-medium text-xs">
                        {{-- Opsi diganti secara dinamis oleh JS sesuai arah --}}
                    </select>
                </div>
                <div>
                    <label class="ui-label" id="qmPartnerLabel">Supplier / Pihak</label>
                    <input type="text" name="partner" id="qmPartner" maxlength="150" placeholder="Ketik nama..." list="qmPartnerList" autocomplete="off" class="ui-input !bg-white text-xs">
                    <datalist id="qmPartnerList"></datalist>
                </div>
            </div>

            {{-- Row: No. SJ/Dokumen & No. PO --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ui-label">No. Dokumen / No. SJ</label>
                    <input type="text" name="doc_no" id="qmDocNo" maxlength="100" placeholder="Contoh: SJ-2026-001 (Opsional)" class="ui-input !bg-white text-xs">
                </div>
                <div>
                    <label class="ui-label">No. PO <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="po_no" id="qmPoNo" maxlength="100" placeholder="Contoh: PO-001 (Opsional)" class="ui-input !bg-white text-xs">
                </div>
            </div>

            {{-- Row: Batch No & Expired Date --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ui-label">No. Batch / SN <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="batch_no" id="qmBatch" maxlength="100" placeholder="Contoh: B-2026-01 (Opsional)" class="ui-input !bg-white text-xs font-mono">
                </div>
                <div>
                    <label class="ui-label">Tgl Expired <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="date" lang="id-ID" name="expiry_date" id="qmExpiry" class="ui-input !bg-white text-xs">
                </div>
            </div>

            {{-- Row: Keterangan --}}
            <div>
                <label class="ui-label">Keterangan Tambahan <span class="text-slate-400 font-normal">(Opsional)</span></label>
                <input type="text" name="keterangan" id="qmKeterangan" maxlength="1000" placeholder="Catatan transaksi..." class="ui-input !bg-white text-xs">
            </div>

            {{-- Footer Tombol --}}
            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100 shrink-0">
                <button type="button" onclick="closeKartuQuickMutasi()" class="h-[34px] px-4 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="qmSubmitBtn" class="h-[34px] px-5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                    <i class="fas fa-check text-xs"></i>
                    <span id="qmSubmitLabel">Simpan Transaksi</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const purposeOptionsMasuk = [
            { val: 'Pembelian', label: 'Pembelian (Barang Masuk)' },
            { val: 'Produksi', label: 'Produksi' },
            { val: 'Retur Customer', label: 'Retur dari Customer' },
            { val: 'Penyesuaian', label: 'Penyesuaian Stok (Tambah)' },
            { val: 'Lainnya', label: 'Lainnya' }
        ];

        const purposeOptionsKeluar = [
            { val: 'Penjualan', label: 'Penjualan (Barang Keluar)' },
            { val: 'Pemakaian Internal', label: 'Pemakaian Internal / Operasional' },
            { val: 'Retur Barang', label: 'Retur ke Supplier' },
            { val: 'Sample', label: 'Sample Produk' },
            { val: 'Penyesuaian', label: 'Penyesuaian Stok (Kurang)' },
            { val: 'Lainnya', label: 'Lainnya' }
        ];

        window.triggerKartuQuickMutasi = function(arah) {
            const meta = document.getElementById('kartuDetailMeta');
            const kmModal = document.getElementById('kartuModal');
            const targetId = meta?.dataset?.productId || kmModal?.dataset?.productId || null;
            const targetNama = meta?.dataset?.nama || document.getElementById('kartuModalNama')?.textContent || '-';
            const targetKode = meta?.dataset?.kode || document.getElementById('kartuModalKode')?.textContent || '-';
            const targetStock = meta?.dataset?.stock ?? (document.getElementById('kartuModalStock')?.textContent || '0');
            const targetUnit = meta?.dataset?.unit || document.getElementById('kartuModalUnit')?.textContent || 'Pcs';
            if (typeof window.openKartuQuickMutasi === 'function') {
                window.openKartuQuickMutasi(arah, targetId, targetNama, targetKode, targetStock, targetUnit);
            }
        };

        window.openKartuQuickMutasi = function(arah, pId, pNama, pKode, pStock, pUnit) {
            const form = document.getElementById('kartuQuickMutasiForm');
            if (!form) return;
            form.reset();

            // Sembunyikan error alert
            const alertEl = document.getElementById('qmAlert');
            if (alertEl) {
                alertEl.classList.add('hidden');
                alertEl.textContent = '';
            }

            // Fallback baca dari metadata Kartu Stock yang sedang aktif jika parameter kosong
            const meta = document.getElementById('kartuDetailMeta');
            const kmModal = document.getElementById('kartuModal');
            const targetId = pId || meta?.dataset?.productId || kmModal?.dataset?.productId || null;
            const targetNama = pNama || meta?.dataset?.nama || document.getElementById('kartuModalNama')?.textContent || '-';
            const targetKode = pKode || meta?.dataset?.kode || document.getElementById('kartuModalKode')?.textContent || '-';
            const targetStock = pStock ?? (meta?.dataset?.stock ?? (document.getElementById('kartuModalStock')?.textContent || '0'));
            const targetUnit = pUnit || meta?.dataset?.unit || document.getElementById('kartuModalUnit')?.textContent || 'Pcs';

            if (!targetId) {
                alert('Produk tidak terdeteksi. Silakan buka kartu stock terlebih dahulu.');
                return;
            }

            document.getElementById('qmProductId').value = targetId;
            document.getElementById('qmArah').value = arah;
            document.getElementById('qmKode').textContent = targetKode;
            document.getElementById('qmNama').textContent = targetNama;
            document.getElementById('qmStok').textContent = targetStock;
            document.getElementById('qmUnit').textContent = targetUnit;
            document.getElementById('qmInputUnit').textContent = targetUnit;

            // Set tanggal hari ini format lokal YYYY-MM-DD
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            document.getElementById('qmTanggal').value = `${year}-${month}-${day}`;

            // Konfigurasi visual berdasarkan arah (Masuk vs Keluar)
            const isMasuk = arah === 'masuk';
            const iconWrap = document.getElementById('qmIconWrap');
            const icon = document.getElementById('qmIcon');
            const title = document.getElementById('qmTitle');
            const subtitle = document.getElementById('qmSubtitle');
            const partnerLabel = document.getElementById('qmPartnerLabel');
            const partnerInput = document.getElementById('qmPartner');
            const purposeSelect = document.getElementById('qmPurpose');
            const submitBtn = document.getElementById('qmSubmitBtn');
            const submitLabel = document.getElementById('qmSubmitLabel');

            // Render opsi tujuan
            purposeSelect.innerHTML = '';
            const options = isMasuk ? purposeOptionsMasuk : purposeOptionsKeluar;
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt.val;
                o.textContent = opt.label;
                purposeSelect.appendChild(o);
            });

            // Isi autocomplete partner dari select di halaman jika ada
            const partnerDatalist = document.getElementById('qmPartnerList');
            if (partnerDatalist) {
                partnerDatalist.innerHTML = '';
                let srcSelect = null;
                if (isMasuk) {
                    srcSelect = document.getElementById('supplier-src') || (document.getElementById('partner-label')?.textContent.includes('Supplier') ? document.getElementById('partner-select') : null);
                } else {
                    srcSelect = (document.getElementById('partner-label')?.textContent.includes('Customer') ? document.getElementById('partner-select') : null);
                }
                if (srcSelect && srcSelect.options) {
                    const seen = new Set();
                    for (let i = 0; i < srcSelect.options.length; i++) {
                        const t = (srcSelect.options[i].text || '').trim();
                        if (t && !t.startsWith('—') && !seen.has(t)) {
                            seen.add(t);
                            const opt = document.createElement('option');
                            opt.value = t;
                            partnerDatalist.appendChild(opt);
                        }
                    }
                }
            }

            if (isMasuk) {
                iconWrap.className = 'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 bg-emerald-100 text-emerald-700 shadow-2xs';
                icon.className = 'fas fa-plus text-xs';
                title.textContent = 'Catat Barang Masuk';
                subtitle.textContent = 'Tambah stok barang ke gudang secara langsung.';
                partnerLabel.textContent = 'Supplier / Pihak Pengirim (Opsional)';
                partnerInput.placeholder = 'Ketik nama supplier / pengirim...';
                submitBtn.className = 'h-[34px] px-5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold transition-all flex items-center gap-2 shadow-sm cursor-pointer';
                submitLabel.textContent = 'Simpan Barang Masuk';
            } else {
                iconWrap.className = 'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 bg-rose-100 text-rose-700 shadow-2xs';
                icon.className = 'fas fa-minus text-xs';
                title.textContent = 'Catat Barang Keluar';
                subtitle.textContent = 'Keluarkan stok barang dari gudang secara langsung.';
                partnerLabel.textContent = 'Customer / Rumah Sakit / Penerima (Opsional)';
                partnerInput.placeholder = 'Ketik nama customer / RS / penerima...';
                submitBtn.className = 'h-[34px] px-5 rounded-lg bg-rose-600 hover:bg-rose-700 active:scale-95 text-white text-xs font-bold transition-all flex items-center gap-2 shadow-sm cursor-pointer';
                submitLabel.textContent = 'Simpan Barang Keluar';
            }

            const modal = document.getElementById('kartuQuickMutasiModal');
            if (modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        };

        window.closeKartuQuickMutasi = function() {
            const modal = document.getElementById('kartuQuickMutasiModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        // Escape key listener untuk close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const qm = document.getElementById('kartuQuickMutasiModal');
                if (qm && !qm.classList.contains('hidden')) {
                    closeKartuQuickMutasi();
                    e.stopPropagation();
                }
            }
        });

        // Submit form via AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('kartuQuickMutasiForm');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const alertEl = document.getElementById('qmAlert');
                const submitBtn = document.getElementById('qmSubmitBtn');
                const submitLabel = document.getElementById('qmSubmitLabel');
                const targetId = document.getElementById('qmProductId').value;

                if (!targetId) {
                    alert('Produk tidak valid.');
                    return;
                }

                alertEl.classList.add('hidden');
                alertEl.textContent = '';
                submitBtn.disabled = true;
                const prevLabel = submitLabel.textContent;
                submitLabel.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

                try {
                    const formData = new FormData(form);
                    const storeUrl = '{{ url("warehouse/stock/card") }}/' + encodeURIComponent(targetId) + '/moves';
                    const res = await fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || formData.get('_token') || ''
                        },
                        body: formData
                    });

                    const json = await res.json().catch(() => null);

                    if (!res.ok || !json || !json.success) {
                        const errMsg = (json && json.message) ? json.message : ('Gagal mencatat mutasi (HTTP ' + res.status + ')');
                        alertEl.textContent = errMsg;
                        alertEl.classList.remove('hidden');
                        submitBtn.disabled = false;
                        submitLabel.textContent = prevLabel;
                        return;
                    }

                    // Sukses!
                    closeKartuQuickMutasi();
                    submitBtn.disabled = false;
                    submitLabel.textContent = prevLabel;

                    // Set penanda mutasi agar refresh induk saat kartuModal ditutup
                    window._kartuHasMutated = true;

                    // Tampilkan notifikasi toast
                    if (typeof kartuToast === 'function') {
                        kartuToast(json.message || 'Transaksi mutasi berhasil dicatat.', true);
                    } else if (typeof kartuPageToast === 'function') {
                        kartuPageToast(json.message || 'Transaksi mutasi berhasil dicatat.', true);
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: json.message,
                            timer: 2500,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }

                    // Update ringkasan stok & stok awal di header modal jika ada data
                    const kmStock = document.getElementById('kartuModalStock');
                    if (kmStock && json.stock !== undefined) {
                        kmStock.textContent = Number(json.stock).toLocaleString('id-ID');
                    }
                    const kmSaldo = document.getElementById('kartuModalSaldo');
                    if (kmSaldo && json.stock_awal_opname !== undefined) {
                        kmSaldo.textContent = Number(json.stock_awal_opname).toLocaleString('id-ID');
                    }

                    // Refresh isi kartu stock secara instan
                    if (typeof kartuCurrentUrl === 'string' && kartuCurrentUrl && typeof loadKartuEmbed === 'function') {
                        loadKartuEmbed(kartuCurrentUrl);
                    } else {
                        // Jika di halaman detail penuh (stock-card.show), reload halaman
                        window.location.reload();
                    }
                } catch (err) {
                    alertEl.textContent = 'Terjadi kesalahan jaringan atau server: ' + err.message;
                    alertEl.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitLabel.textContent = prevLabel;
                }
            });
        });
    })();
</script>
@endpush

