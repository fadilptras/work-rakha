            {{-- TAB 1: Monitoring Product --}}
            <div class="flex-1 min-h-0 flex flex-col w-full overflow-hidden">
                <x-ui.glass-card padding="none" class="!p-0 overflow-hidden flex-1 min-h-0 flex flex-col w-full !rounded-2xl !bg-white border border-slate-200 shadow-sm border-t-4 border-t-blue-500">
                    <form action="{{ route('warehouse.index') }}" method="GET" class="p-4 sm:p-5 border-b border-slate-100 bg-white shrink-0">
                        <input type="hidden" name="tab" value="produk">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end">
                            <div class="lg:col-span-4 md:col-span-2">
                                <label class="filter-label-compact" style="font-size: 10px !important;">Cari Kode / Nama Produk</label>
                                <x-ui.search-input name="q" placeholder="Cari Nama atau Kode Produk (Contoh: Kasa, KSA-001)..." value="{{ request('q') }}" autocomplete="off" class="w-full" :debounce="500" :clear-submit="false" />
                            </div>
                            <div class="lg:col-span-3 md:col-span-1">
                                <label class="filter-label-compact" style="font-size: 10px !important;">Kebutuhan</label>
                                <x-ui.filter-select name="butuh" value="{{ $kebutuhan ?? 'semua' }}" onchange="this.form.submit()" class="w-full" :options="['semua' => 'Semua SKU', 'order' => 'Perlu Order']" />
                            </div>
                            <div class="lg:col-span-3 md:col-span-1 flex items-end">
                                <x-ui.info class="w-full" icon="fa-boxes-stacked" label="Tampil:" :value="number_format($products->count(), 0, ',', '.')" />
                            </div>
                            <div class="lg:col-span-2 md:col-span-1 flex items-end">
                                <a href="{{ route('warehouse.index', ['tab' => 'produk']) }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center border border-slate-200 shadow-sm leading-none"><i class="fas fa-undo mr-1.5 text-[11px]"></i> Reset</a>
                            </div>
                        </div>
                    </form>
                    <div class="flex-1 min-h-0 bg-white manage-table-scroll">
                        <table class="w-full text-sm text-left text-slate-600 bg-white table-bordered">
                            <thead class="text-[11px] text-slate-600 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                                <tr>
                                    <th class="px-2.5 py-2 w-10 text-center">No</th>
                                    <th class="px-3 py-2">Kode</th>
                                    <th class="px-2.5 py-2 text-center whitespace-nowrap bg-violet-100/80 border-l border-violet-200 text-violet-900">Pack/QTY</th>
                                    <th class="px-2.5 py-1.5 text-center bg-orange-100 border-l border-orange-200 text-orange-900 col-tooltip leading-tight" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <div class="inline-flex flex-col items-center cursor-help">
                                            <span>Stock Awal / Opname</span>
                                            <span class="text-[9px] font-bold text-orange-700 normal-case tracking-normal">({{ $namaBulanOps ?? now()->locale('id')->translatedFormat('F Y') }})</span>
                                        </div>
                                        <div x-show="open" x-cloak class="col-tooltip-popup text-center"><span class="font-bold text-orange-300">Stock Awal / Opname</span> - Hasil opname fisik atau stok awal bulan {{ $namaBulanOps ?? now()->locale('id')->translatedFormat('F Y') }}.</div>
                                    </th>
                                    <th class="px-2.5 py-2 text-center bg-blue-100 border-l border-blue-200 text-blue-900 col-tooltip min-w-[130px]" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <span class="inline-flex items-center justify-center gap-1 cursor-help">Stock Realtime</span>
                                        <div x-show="open" x-cloak class="col-tooltip-popup text-center"><span class="font-bold text-blue-200">Stock Realtime</span> - stok realtime kini.<br>Angka resmi, sama dengan halaman Data Product.</div>
                                    </th>
                                    <th class="px-2 py-1.5 text-center border-l border-emerald-200 text-emerald-900 bg-emerald-100 leading-tight col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <div class="inline-flex flex-col items-center cursor-help">
                                            <span>Forecast</span>
                                            <span class="text-[8px] font-medium normal-case tracking-normal opacity-70">{{ $forecastBulan ?? '' }}</span>
                                        </div>
                                        <div x-show="open" x-cloak class="col-tooltip-popup text-center"><span class="font-bold text-emerald-200">Forecast</span> - estimasi kebutuhan {{ $forecastBulan ?? 'bulan ini' }}.<br><span class="text-yellow-300">Forecast = Rata-rata + {{ rtrim(rtrim(number_format($forecastPct ?? 20, 2, '.', ''), '0'), '.') }}%</span></div>
                                    </th>
                                    <th class="px-2 py-1.5 text-center border-l border-indigo-200 text-indigo-900 bg-indigo-100 leading-tight col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <div class="inline-flex flex-col items-center cursor-help">
                                            <span>Order</span>
                                            <span class="text-[8px] font-medium normal-case tracking-normal opacity-70">(Production)</span>
                                        </div>
                                        <div x-show="open" x-cloak class="col-tooltip-popup text-center"><span class="font-bold text-indigo-200">Order</span> - saran tambah stok agar tetap di atas buffer.<br><span class="text-yellow-300">0 = aman, tidak perlu order.</span></div>
                                    </th>
                                    <th class="px-2 py-1.5 text-center border-l border-rose-200 text-rose-900 bg-rose-100 leading-tight col-tooltip" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click="open = !open">
                                        <div class="inline-flex flex-col items-center cursor-help">
                                            <span>DOI</span>
                                            <span class="text-[8px] font-medium normal-case tracking-normal opacity-70">days</span>
                                        </div>
                                        <div x-show="open" x-cloak class="col-tooltip-popup col-tooltip-popup--right text-center"><span class="font-bold text-rose-200">DOI</span> - daya tahan stok dalam hari.<br><span class="text-yellow-300">DOI = (Stok &divide; Rata-rata) &times; 30</span></div>
                                    </th>
                                    <th class="px-2.5 py-2 text-center border-l border-slate-200">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($products as $index => $p)
                                    @php
                                        $stok = (float) $p->stock;
                                        $isHabis = $stok <= 0;
                                        $nama = $p->product_name_clean ?: $p->product_name;
                                        $kemasan = $p->unit ?? 'Pcs';
                                        $fillUnit = $p->fill_unit ?? 'Pcs';
                                        // Stok dalam satuan pack, bukan isi.
                                        $satuan = $p->unit ?: $fillUnit;
                                        $fc = $p->forecast ?? null;
                                        $namaAsli = $p->product_name;
                                        $orderQty = (int) ($fc['order_qty'] ?? 0);
                                        $forecastQty = (int) ($fc['forecast_qty'] ?? 0);
                                    @endphp
                                    <tr class="hover:bg-blue-50/60 transition-colors {{ $isHabis ? 'bg-red-50/40' : '' }}">
                                        <td class="px-2.5 py-1.5 text-center text-slate-400 font-medium text-xs">{{ $index + 1 }}</td>
                                        <td class="px-3 py-1.5">
                                            <div class="font-mono font-bold text-[11px] text-sky-700 leading-none">{{ $p->product_code ?? '-' }}</div>
                                            <div class="flex items-center justify-between gap-2 mt-0.5">
                                                <div class="font-bold text-slate-900 text-xs leading-snug">{{ $nama }}</div>
                                                <div class="spb-marks shrink-0 inline-flex items-center gap-1 leading-none" data-marks-for="{{ $p->id }}"></div>
                                            </div>
                                        </td>
                                        <td class="px-2.5 py-1.5 text-center whitespace-nowrap bg-violet-50/60 border-l border-violet-100">
                                            <span class="inline-flex items-center gap-1 bg-violet-100 text-violet-800 border border-violet-200 text-[11px] font-bold px-2 py-0.5 rounded-full shadow-2xs">
                                                {{ \App\Support\PackagingCatalog::packagingPreview($p->unit, $p->pcs_per_unit, $p->fill_unit) }}
                                            </span>
                                        </td>
                                        <td class="px-2.5 py-1.5 text-center whitespace-nowrap bg-orange-50/70 border-l border-orange-100">
                                            <span class="font-black text-xs text-orange-700">{{ number_format($p->stock_awal_opname ?? 0, 0, ',', '.') }}</span>
                                            <span class="text-[10px] font-bold text-orange-600/80">{{ $satuan }}</span>
                                        </td>
                                        <td class="px-2.5 py-1.5 text-center whitespace-nowrap bg-blue-50/60 border-l border-blue-100">
                                            <span class="font-black text-xs {{ $isHabis ? 'text-rose-600' : 'text-blue-900' }}">{{ number_format($stok, 0, ',', '.') }} <span class="text-[10px] font-bold text-blue-700/80">{{ $satuan }}</span></span>
                                        </td>
                                        <td class="px-2 py-1.5 text-center text-xs font-black text-emerald-700 bg-emerald-50/60 border-l border-emerald-100">{{ $fc ? number_format($fc['forecast_qty'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-2 py-1.5 text-center text-xs font-black text-indigo-800 bg-indigo-50/60 border-l border-indigo-100">{{ $fc ? number_format($fc['order_qty'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-2 py-1.5 text-center text-xs font-bold text-rose-700 bg-rose-50/60 border-l border-rose-100">{{ $fc ? number_format($fc['doi_qty'], 0, ',', '.') : '-' }}</td>
                                        <td class="px-2.5 py-1.5 text-center whitespace-nowrap border-l border-slate-100">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <x-ui.icon-button action="view" icon="fa-id-card-clip" title="Buka kartu stock" data-kartu-modal data-product-id="{{ $p->id }}" data-embed-url="{{ route('warehouse.stockcard.show', $p) . '?embed=1' }}" data-nama="{{ $nama }}" data-kode="{{ $p->product_code }}" data-full-url="{{ route('warehouse.stockcard.show', $p) }}" class="!w-6 !h-6 !text-[10px]" />
                                                <a href="{{ route('warehouse.stockcard.print', $p) }}" target="_blank" rel="noopener" class="ui-btn ui-btn--view !w-6 !h-6 !text-[10px]" title="Cetak kartu stock (print browser)"><i class="fas fa-print text-[10px]"></i></a>
                                                <button type="button" class="btn-spb ui-btn ui-btn--spb !w-6 !h-6 !text-[10px]" title="Tambah ke draft SPB"
                                                    data-spb-pid="{{ $p->id }}"
                                                    data-spb-nama="{{ $namaAsli }}"
                                                    data-spb-kode="{{ $p->product_code }}"
                                                    data-spb-clean="{{ $nama }}"
                                                    data-spb-satuan="{{ $kemasan }}"
                                                    data-spb-order="{{ $orderQty }}"
                                                    data-spb-forecast="{{ $forecastQty }}"><i class="fas fa-file-signature text-[10px] pointer-events-none"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="p-0"><x-ui.empty icon="fa-box-open" title="Tidak ada barang" message="Coba kata kunci lain." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-1.5 border-t border-slate-100 bg-slate-50/60 flex items-center justify-end gap-3 shrink-0">
                        <span class="text-[11px] text-slate-400 font-semibold whitespace-nowrap leading-none">Total SKU: <b class="text-slate-700">{{ number_format($products->count(), 0, ',', '.') }}</b></span>
                    </div>
                </x-ui.glass-card>

                {{-- Floating bar draft SPB + rincian + toast (tetap di halaman, lompat ke form bila siap) --}}
                <div id="spb-floatbar" class="hidden fixed bottom-6 right-6 z-[9000] w-[440px] max-w-[92vw] bg-slate-900/95 backdrop-blur border border-white/10 shadow-2xl rounded-2xl px-4 py-3">
                    <div id="spb-floatlist" class="hidden mb-2 max-h-44 overflow-y-auto divide-y divide-white/10 border-b border-white/10 pb-2"></div>
                    <div class="flex items-center gap-3">
                        <div class="spb-fb-icon w-9 h-9 shrink-0 rounded-xl flex items-center justify-center"><i class="fas fa-file-signature text-sm"></i></div>
                        <button type="button" id="spb-float-toggle" class="flex-1 min-w-0 text-left" title="Klik untuk lihat rincian draft">
                            <p class="spb-fb-title text-[13px] font-black leading-tight">Draft SPB: <span id="spb-float-count">0</span> item <i id="spb-float-chev" class="fas fa-chevron-up text-[10px] text-slate-400 ml-1"></i></p>
                            <p id="spb-float-sub" class="spb-fb-sub text-[11px] font-medium leading-snug truncate">Siap diajukan ke form SPB.</p>
                        </button>
                        <a href="{{ route('pengajuan_barang.index', ['from' => 'ops']) }}" class="spb-fb-go shrink-0 inline-flex items-center gap-1.5 h-[34px] px-4 rounded-xl text-xs font-black transition-colors">Buka Form <i class="fas fa-arrow-right text-[10px]"></i></a>
                    </div>
                </div>
            </div>
            @push('scripts')
            <script>
                // Inisialisasi Draft SPB & Status
                (function initOpsSpb() {
                    function boot() {
                    try {
                    const SPB_DRAFT_KEY = 'pengajuan_barang_draft_{{ Auth::id() }}';
                    const SPB_STATUS_URL = '{{ route("pengajuan_barang.statusBatch") }}';
                    const floatbar = document.getElementById('spb-floatbar');
                    const floatCount = document.getElementById('spb-float-count');
                    const floatSub = document.getElementById('spb-float-sub');
                    const spbBtns = Array.from(document.querySelectorAll('.btn-spb[data-spb-pid]'));
                    if (!spbBtns.length) return;
                    let serverMarks = {};

                    function esc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
                    function fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(Number(n) || 0)); }
                    function readDraft() {
                        try {
                            const raw = localStorage.getItem(SPB_DRAFT_KEY);
                            if (!raw) return null;
                            const d = JSON.parse(raw);
                            if (!d || !Array.isArray(d.items)) return null;
                            return d;
                        } catch (e) { return null; }
                    }
                    function draftNameSet() {
                        const d = readDraft();
                        const set = new Set();
                        (d?.items || []).forEach(it => {
                            const n = (it.deskripsi || '').trim().toLowerCase();
                            if (n) set.add(n);
                        });
                        return { draft: d, set };
                    }

                    // Render Badge & Floating Bar
                    function renderMarks() {
                        const { draft, set } = draftNameSet();
                        const items = draft?.items || [];
                        const n = items.length;
                        if (floatbar) floatbar.classList.toggle('hidden', n === 0);
                        if (floatCount) floatCount.textContent = fmt(n);
                        if (floatSub) {
                            const tot = items.reduce((a, it) => a + (parseFloat(it.jumlah) || 0), 0);
                            floatSub.textContent = n
                                ? ('Total ' + fmt(tot) + ' unit • klik untuk rincian • tersimpan otomatis.')
                                : 'Siap diajukan ke form SPB.';
                        }
                        renderFloatList(items);
                        spbBtns.forEach(btn => {
                            const nama = (btn.dataset.spbNama || '').trim().toLowerCase();
                            const inDraft = nama && set.has(nama);
                            btn.classList.toggle('is-in-draft', !!inDraft);
                            const ico = btn.querySelector('i');
                            if (ico) { ico.classList.toggle('fa-check', !!inDraft); ico.classList.toggle('fa-file-signature', !inDraft); }
                            btn.title = inDraft ? 'Sudah di draft SPB — klik lagi untuk batalkan' : 'Tambah ke draft SPB';
                            const box = document.querySelector('[data-marks-for="' + btn.dataset.spbPid + '"]');
                            if (!box) return;
                            const info = serverMarks[btn.dataset.spbPid] || {};
                            const tot = info.total_bulan_ini || 0;
                            const akt = info.aktif_bulan_ini || 0;
                            const fin = info.selesai_bulan_ini || 0;
                            let html = '';
                            if (inDraft) html += '<span class="spb-badge spb-badge--draft">● Draft</span>';
                            if (akt > 0) html += '<span class="spb-badge spb-badge--aktif">⏳ ' + akt + '× proses</span>';
                            if (fin > 0) html += '<span class="spb-badge spb-badge--selesai">✔ ' + fin + '× selesai</span>';
                            if (tot > 0 && akt === 0 && fin === 0) html += '<span class="spb-badge spb-badge--draft">• ' + tot + '× bulan ini</span>';
                            if (info.ditolak_90h) html += '<span class="spb-badge spb-badge--tolak">✖ riwayat</span>';
                            box.innerHTML = html;
                            box.title = tot > 0
                                ? ('Diajukan ' + tot + '× bulan ini (proses ' + akt + ', selesai ' + fin + '). Terakhir: "' + (info.last?.judul || '-') + '" • ' + (info.last?.status || '-') + ' • ' + (info.last?.tanggal || '-') + (info.last?.oleh ? ' • ' + info.last.oleh : ''))
                                : (inDraft ? 'Ada di draft SPB, belum diajukan.' : 'Belum pernah diajukan bulan ini.');
                        });
                    }

                    // Rincian Baris SPB
                    function renderFloatList(items) {
                        const list = document.getElementById('spb-floatlist');
                        if (!list) return;
                        if (!items.length) { list.innerHTML = ''; list.classList.add('hidden'); return; }
                        list.innerHTML = items.map((it, i) =>
                            '<div class="flex items-center gap-2 py-1.5">' +
                            '<span class="spb-fb-name flex-1 min-w-0 text-[11px] font-bold truncate">' + esc(it.deskripsi || '-') + '</span>' +
                            '<span class="shrink-0 text-[11px] font-black" style="color:#bef264;">' + esc(fmt(it.jumlah)) + ' ' + esc(it.satuan || '') + '</span>' +
                            '<button type="button" data-spb-rm="' + i + '" class="spb-fb-rm shrink-0 w-6 h-6 rounded-lg flex items-center justify-center transition-colors" title="Hapus dari draft"><i class="fas fa-times text-[10px] pointer-events-none"></i></button>' +
                            '</div>'
                        ).join('');
                    }

                    // Request Status SPB ke Server
                    async function loadServerMarks() {
                        try {
                            const qs = spbBtns.map(b => 'ids[]=' + encodeURIComponent(b.dataset.spbPid)).join('&');
                            const res = await fetch(SPB_STATUS_URL + '?' + qs, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const json = await res.json();
                            serverMarks = json.map || {};
                        } catch (e) { serverMarks = {}; }
                        renderMarks();
                    }

                    // Event Handler SPB Click & Remove
                    function handleSpbClick(btn) {
                            const namaAsli = (btn.dataset.spbNama || '').trim();
                            if (!namaAsli) return;
                            const order = parseFloat(btn.dataset.spbOrder) || 0;
                            const fc = parseFloat(btn.dataset.spbForecast) || 0;
                            let qty = order > 0 ? order : fc;
                            let aman = false;
                            if (!(qty > 0)) { qty = 1; aman = true; }
                            const kode = (btn.dataset.spbKode || '').trim();
                            const clean = (btn.dataset.spbClean || '').trim();
                            const satuan = (btn.dataset.spbSatuan || 'Pcs').trim() || 'Pcs';
                            let ket = 'Dari Warehouse Ops • kode ' + (kode || '-');
                            if (clean && clean.toLowerCase() !== namaAsli.toLowerCase()) ket += ' / ' + clean;
                            if (aman) ket += ' • Stok aman (order 0), pengajuan manual';

                            const cur = readDraft() || { v: 1, judul_pengajuan: '', catatan_pemohon: '', items: [], saved_at: new Date().toISOString() };
                            const key = namaAsli.toLowerCase();
                            const foundIdx = (cur.items || []).findIndex(it => (it.deskripsi || '').trim().toLowerCase() === key);
                            if (foundIdx >= 0) {
                                cur.items.splice(foundIdx, 1);
                            } else {
                                cur.items.push({ deskripsi: namaAsli, supplier: '', jumlah: String(qty), satuan: satuan, keterangan: ket });
                            }
                            cur.v = 1;
                            cur.saved_at = new Date().toISOString();
                            try { localStorage.setItem(SPB_DRAFT_KEY, JSON.stringify(cur)); } catch (e) { console.error('[ops-spb] gagal simpan draft:', e); }
                            renderMarks();
                    }
                    document.addEventListener('click', function (e) {
                        if (!e.target || !e.target.closest) return;
                        const btn = e.target.closest('.btn-spb[data-spb-pid]');
                        if (btn) { e.preventDefault(); handleSpbClick(btn); return; }
                        const rm = e.target.closest('[data-spb-rm]');
                        if (rm) {
                            e.preventDefault();
                            const cur = readDraft();
                            const idx = parseInt(rm.getAttribute('data-spb-rm'), 10) || 0;
                            if (cur && cur.items && cur.items[idx]) {
                                cur.items.splice(idx, 1);
                                cur.saved_at = new Date().toISOString();
                                try { localStorage.setItem(SPB_DRAFT_KEY, JSON.stringify(cur)); } catch (err) {}
                                renderMarks();
                            }
                            return;
                        }
                        if (e.target.closest('#spb-float-toggle')) {
                            const list = document.getElementById('spb-floatlist');
                            const chev = document.getElementById('spb-float-chev');
                            if (list) list.classList.toggle('hidden');
                            if (chev) { chev.classList.toggle('fa-chevron-up'); chev.classList.toggle('fa-chevron-down'); }
                        }
                    });

                    window.addEventListener('storage', (e) => { if (e.key === SPB_DRAFT_KEY) renderMarks(); });
                    renderMarks();
                    loadServerMarks();
                    } catch (err) { console.error('[ops-spb] gagal init:', err); }
                    }
                    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
                    else boot();
                })();
            </script>
            @endpush
