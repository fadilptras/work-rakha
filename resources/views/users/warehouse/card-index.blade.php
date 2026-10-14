<x-layout-users title="Kartu Stock">
    @push('styles')
    <style>
        /* Compact stock cards — 7 per baris di layar lebar, warna bergantian */
        .ks-card {
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid;
            border-radius: 1rem;
            padding: 0.55rem 0.65rem;
            min-height: 104px;
            text-align: left;
            width: 100%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: all 0.22s cubic-bezier(0.16,1,0.3,1);
        }
        .ks-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(15,23,42,0.14);
            filter: brightness(0.99);
        }
        /* WAJIB: kalahkan display:flex .ks-card saat live-filter menyembunyikan card.
           Tanpa ini, class `hidden` (Tailwind) kalah prioritas dan filter terlihat "tidak jalan". */
        .ks-card.hidden { display: none; }
        .ks-card--blue   { background: #eff6ff; border-color: #bfdbfe; }
        .ks-card--blue:hover { border-color: #60a5fa; }
        .ks-card--sky   { background: #f0f9ff; border-color: #bae6fd; }
        .ks-card--sky:hover { border-color: #38bdf8; }
        .ks-card--orange { background: #fff7ed; border-color: #fed7aa; }
        .ks-card--orange:hover { border-color: #fb923c; }
        .ks-card--violet { background: #f5f3ff; border-color: #ddd6fe; }
        .ks-card--violet:hover { border-color: #a78bfa; }
        .ks-card--rose   { background: #fff1f2; border-color: #fecdd3; }
        .ks-card--rose:hover { border-color: #fb7185; }
        .ks-card--cyan   { background: #ecfeff; border-color: #a5f3fc; }
        .ks-card--cyan:hover { border-color: #22d3ee; }
        .ks-card--amber  { background: #fffbeb; border-color: #fde68a; }
        .ks-card--amber:hover { border-color: #fbbf24; }
        .ks-card--blue .ks-card__stok { color: #1d4ed8; }
        .ks-card--sky .ks-card__stok { color: #0369a1; }
        .ks-card--orange .ks-card__stok { color: #c2410c; }
        .ks-card--violet .ks-card__stok { color: #6d28d9; }
        .ks-card--rose .ks-card__stok { color: #be123c; }
        .ks-card--cyan .ks-card__stok { color: #0e7490; }
        .ks-card--amber .ks-card__stok { color: #b45309; }
        .ks-card--blue .ks-card__foot { border-color: #bfdbfe; }
        .ks-card--sky .ks-card__foot { border-color: #bae6fd; }
        .ks-card--orange .ks-card__foot { border-color: #fed7aa; }
        .ks-card--violet .ks-card__foot { border-color: #ddd6fe; }
        .ks-card--rose .ks-card__foot { border-color: #fecdd3; }
        .ks-card--cyan .ks-card__foot { border-color: #a5f3fc; }
        .ks-card--amber .ks-card__foot { border-color: #fde68a; }
        .ks-card__kode {
            font-family: ui-monospace, monospace;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ks-card__nama {
            font-size: 11.5px;
            font-weight: 800;
            line-height: 1.35;
            color: #1e293b;
            margin-top: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.7em;
        }
        .ks-card__foot {
            margin-top: auto;
            padding-top: 6px;
            border-top: 2px dashed #e2e8f0;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }
        .ks-card__stok { font-size: 1.15rem; font-weight: 900; color: #1e293b; line-height: 1; }
        .ks-card__stok--kritis { color: #d97706; }
        .ks-card__stok--habis { color: #e11d48; }
        .ks-card__satuan { font-size: 10px; font-weight: 700; color: #64748b; }
        .ks-card__badge {
            position: absolute;
            bottom: 7px;
            right: 7px;
            font-size: 7px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 1px 6px;
            border-radius: 9999px;
            border: 1px solid;
        }
        .ks-card__badge--habis { color: #e11d48; background: #fff1f2; border-color: #fecdd3; }
        .ks-card__badge--kritis { color: #d97706; background: #fffbeb; border-color: #fde68a; }

        /* Popup modal kartu */
        #kartuModal { transition: opacity 0.2s ease; }
        .ks-spin {
            width: 36px; height: 36px;
            border: 3px solid #e2e8f0;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: ksSpin 0.8s linear infinite;
        }
        @keyframes ksSpin { to { transform: rotate(360deg); } }
    </style>
    @endpush

    <div class="mesh-bg flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800">
        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 lg:pt-8 pb-4 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start">
            <div class="w-full flex justify-start mb-3 md:mb-4">
                <x-ui.back-button :href="route('warehouse.dashboard')" label="Back to Warehouse Dashboard" />
            </div>

            <x-ui.page-header title="Kartu Stock" subtitle="Daftar barang — klik kartu untuk membuka pencatatan Masuk / Keluar per Batch No." icon="fa-id-card-clip" :sticky="false" />

            <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full max-w-full mx-auto !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg">
                <div class="p-4 sm:p-5">
                    <div class="flex flex-row flex-wrap items-center gap-x-3 gap-y-2">
                        <x-ui.search-input id="ksSearch" value="{{ $search }}" placeholder="Cari kode / nama barang..." class="flex-1 min-w-[160px] max-w-md" />
                        <p id="ksCount" class="text-[11px] text-slate-500 font-bold ml-auto shrink-0 text-right"></p>
                    </div>
                </div>

                <div class="px-4 sm:px-5 pb-4 sm:pb-5">
                    @if($products->isNotEmpty())
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 xl:grid-cols-7 gap-3">
                            @php $ksThemes = ['blue','sky','orange','violet','rose','cyan','amber']; @endphp
                            @foreach($products as $p)
                                @php
                                    $isHabis = (float) $p->stock <= 0;
                                    $isKritis = ! $isHabis && $p->min_stock !== null && (float) $p->stock <= (float) $p->min_stock;
                                @endphp
                                <x-warehouse.stock-card
                                    :href="route('warehouse.stockcard.show', $p)"
                                    :embedUrl="route('warehouse.stockcard.show', $p) . '?embed=1'"
                                    :kode="$p->product_code"
                                    :nama="$p->product_name_clean ?: $p->product_name"
                                    :stok="$p->stock"
                                    :satuan="$p->unit ?: 'Pcs'"
                                    :habis="$isHabis"
                                    :kritis="$isKritis"
                                    :theme="$ksThemes[$loop->index % count($ksThemes)]"
                                    :data-search="mb_strtolower(($p->product_code ?? '') . ' ' . ($p->product_name_clean ?: $p->product_name))" />
                            @endforeach
                        </div>
                        <div id="ksNoResult" class="hidden">
                            <x-ui.empty title="Tidak ketemu" message="Coba kata kunci lain." />
                        </div>
                    @else
                        <x-ui.empty title="Tidak ada barang" message="Belum ada produk aktif." />
                    @endif
                </div>
            </x-ui.glass-card>
        </div>
    </div>

    {{-- Popup detail kartu (diperlebar) --}}
    <div id="kartuModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-3 sm:p-6">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-start gap-3 bg-slate-50 shrink-0">
                <div class="min-w-0">
                    <div id="kartuModalKode" class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400"></div>
                    <h3 id="kartuModalNama" class="text-[15px] font-black text-slate-800 leading-tight truncate">Kartu Stock</h3>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">Stok <span id="kartuModalStock" class="font-black text-slate-800">-</span> <span id="kartuModalUnit"></span> · Stok Awal <span id="kartuModalSaldo" class="font-black text-blue-700">-</span> · Update terakhir <span id="kartuModalUpdated" class="font-bold text-slate-600">-</span></p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a id="kartuModalFull" href="#" class="hidden sm:inline-flex items-center gap-1.5 h-[32px] px-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-bold" title="Buka di halaman penuh"><i class="fas fa-expand"></i> Halaman penuh</a>
                    <button type="button" onclick="closeKartuModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div id="kartuModalToast" class="hidden mx-4 mt-3 px-4 py-2.5 rounded-xl text-xs font-bold shrink-0"></div>
            <div id="kartuModalBody" class="p-4 overflow-y-auto space-y-3 flex-1"></div>
        </div>
    </div>

    @include('users.warehouse.card-edit-modal')

    @push('scripts')
    <script>
        let kartuCurrentUrl = null;

        function kartuToast(msg, ok = true) {
            const el = document.getElementById('kartuModalToast');
            el.classList.remove('hidden');
            el.className = 'mx-4 mt-3 px-4 py-2.5 rounded-xl text-xs font-bold shrink-0 ' + (ok
                ? 'bg-blue-50 border border-blue-200 text-blue-700'
                : 'bg-red-50 border border-red-200 text-red-600');
            el.textContent = msg;
            clearTimeout(el._t);
            el._t = setTimeout(() => el.classList.add('hidden'), 5000);
        }

        // Live filter daftar barang — instan saat diketik, tanpa reload.
        // (Tombol × bawaan x-ui.search-input me-reset + dispatch event input.)
        (function () {
            const input = document.getElementById('ksSearch');
            const count = document.getElementById('ksCount');
            const noResult = document.getElementById('ksNoResult');
            if (!input) return;
            const cards = Array.from(document.querySelectorAll('[data-kartu-modal][data-search]'));

            function applyFilter() {
                const q = input.value.trim().toLowerCase();
                let shown = 0;
                cards.forEach(c => {
                    const hit = q === '' || (c.dataset.search || '').includes(q);
                    c.classList.toggle('hidden', !hit);
                    if (hit) shown++;
                });
                if (noResult) noResult.classList.toggle('hidden', shown > 0);
                if (count && cards.length) count.textContent = 'Menampilkan ' + shown + ' dari ' + cards.length + ' barang';
            }

            input.addEventListener('input', applyFilter);
            applyFilter();
        })();

        function openKartuModal(embedUrl, nama, kode, fullUrl) {
            const modal = document.getElementById('kartuModal');
            document.getElementById('kartuModalNama').textContent = nama || 'Kartu Stock';
            document.getElementById('kartuModalKode').textContent = kode || '';
            document.getElementById('kartuModalFull').href = fullUrl || '#';
            document.getElementById('kartuModalSaldo').textContent = '-';
            document.getElementById('kartuModalToast').classList.add('hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            loadKartuEmbed(embedUrl);
        }

        function closeKartuModal() {
            const modal = document.getElementById('kartuModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            kartuCurrentUrl = null;
        }

        function kartuLoading() {
            document.getElementById('kartuModalBody').innerHTML =
                '<div class="py-16 flex flex-col items-center justify-center gap-3 text-slate-400">' +
                '<div class="ks-spin"></div><p class="text-xs font-bold">Memuat kartu...</p></div>';
        }

        async function loadKartuEmbed(url) {
            kartuCurrentUrl = url;
            kartuLoading();
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const body = document.getElementById('kartuModalBody');
                body.innerHTML = await res.text();
                body.scrollTop = 0;
                // Sinkron header modal dari meta partial (stok/satuan/update terkini).
                const meta = body.querySelector('#kartuDetailMeta');
                if (meta) {
                    if (meta.dataset.nama) document.getElementById('kartuModalNama').textContent = meta.dataset.nama;
                    if (meta.dataset.kode) document.getElementById('kartuModalKode').textContent = meta.dataset.kode;
                    document.getElementById('kartuModalStock').textContent = meta.dataset.stock ?? '-';
                    document.getElementById('kartuModalUnit').textContent = meta.dataset.unit ?? '';
                    document.getElementById('kartuModalSaldo').textContent = meta.dataset.saldo ?? '-';
                    document.getElementById('kartuModalUpdated').textContent = meta.dataset.updated ?? '-';
                }
            } catch (e) {
                document.getElementById('kartuModalBody').innerHTML =
                    '<div class="py-16 text-center"><p class="text-sm font-bold text-slate-600">Gagal memuat kartu.</p>' +
                    '<button type="button" onclick="loadKartuEmbed(kartuCurrentUrl)" class="mt-3 h-[34px] px-4 rounded-lg bg-blue-600 text-white text-xs font-bold">Coba lagi</button></div>';
            }
        }

        // Klik kartu -> buka popup (fallback: navigasi biasa bila JS mati)
        document.addEventListener('click', function (e) {
            const card = e.target.closest('[data-kartu-modal]');
            if (!card) return;
            e.preventDefault();
            const namaEl = card.querySelector('.ks-card__nama');
            const kodeEl = card.querySelector('.ks-card__kode');
            openKartuModal(card.dataset.embed, namaEl ? namaEl.textContent.trim() : 'Kartu Stock', kodeEl ? kodeEl.textContent.trim() : '', card.href);
        });

        // Tutup via backdrop & Esc
        document.getElementById('kartuModal').addEventListener('click', function (e) {
            if (e.target === this) closeKartuModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            const edit = document.getElementById('kartuEditModal');
            if (edit && !edit.classList.contains('hidden')) return; // modal edit yang urus
            if (!document.getElementById('kartuModal').classList.contains('hidden')) closeKartuModal();
        });

        // Form di dalam popup: filter (GET) & mutasi (POST) via fetch
        document.getElementById('kartuModalBody').addEventListener('submit', async function (e) {
            const form = e.target.closest('form[data-remote]');
            if (!form) return;
            e.preventDefault();
            const mode = form.dataset.remote;

            if (mode === 'filter') {
                const params = new URLSearchParams(new FormData(form));
                params.set('embed', '1');
                loadKartuEmbed(form.action + '?' + params.toString());
                return;
            }

            if (mode === 'move') {
                const btn = form.querySelector('button[type="submit"], button:not([type])');
                if (btn) { btn.disabled = true; btn.dataset.label = btn.innerHTML; btn.innerHTML = 'Menyimpan...'; }
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || data.success === false) {
                        const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Gagal menyimpan mutasi.');
                        kartuToast(msg, false);
                    } else {
                        kartuToast(data.message || 'Mutasi tercatat.', true);
                        form.reset();
                        if (kartuCurrentUrl) await loadKartuEmbed(kartuCurrentUrl);
                    }
                } catch (err) {
                    kartuToast('Gagal menyimpan mutasi. Periksa koneksi.', false);
                } finally {
                    if (btn) { btn.disabled = false; btn.innerHTML = btn.dataset.label; }
                }
            }
        });
    </script>
    @endpush
</x-layout-users>
