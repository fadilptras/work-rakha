<x-layout-users title="Opname" :fluid="true">
    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        /* Halaman ini: navbar ikut scroll (tidak sticky). */
        header.sticky { position: static !important; }
        /* Kunci: kartu header tidak sticky di halaman ini. */
        .ui-page-header { position: static !important; top: auto !important; }
        .mesh-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 0;
            pointer-events: none;
            background-color: #ede9fe;
            background-image:
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.6) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.15) 0px, transparent 50%);
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 relative overflow-x-clip text-slate-800 pb-16">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start">
            <div class="w-full flex justify-start mb-3 md:mb-4">
                <x-ui.back-button :href="route('warehouse.dashboard')" label="Back to Warehouse Dashboard" />
            </div>

            <div class="hidden md:block">
                <x-ui.page-header title="Opname {{ $namaBulan ?? '' }}" subtitle="Opname bulan {{ $namaBulan ?? '' }}: catat draf → selesaikan jadi penyesuaian + berita acara." icon="fa-clipboard-check" :sticky="false" />
            </div>

            {{-- Header mobile ala sales command center --}}
            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Opname {{ $namaBulan ?? '' }}</h2>
                        <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">
                            Hitung fisik &amp; berita acara.
                        </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-clipboard-check"></i>
                </div>
            </div>

            @if (session('success'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-xs font-bold">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-xs font-bold">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-xs font-bold">{{ $errors->first() }}</div>
            @endif

            {{-- Toolbar aksi + alur ala Accurate: Perintah (hitung) → Hasil (simpan) → Penyesuaian + BA --}}
            <x-ui.glass-card class="!rounded-3xl !bg-white border-t-4 border-t-blue-500 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    @if($canManage)
                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('warehouse.stockopname.catat', ['bulan' => $bulan ?? now()->format('Y-m')]) }}" class="h-[38px] px-4 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm flex items-center justify-center gap-2">
                                <i class="fas fa-clipboard-list text-[11px]"></i> Catat Opname
                            </a>
                            <button type="button" onclick="uiModalOpen('modal-scrap')" class="h-[38px] px-4 rounded-lg bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-bold shadow-sm flex items-center justify-center gap-2">
                                <i class="fas fa-trash-can text-[11px]"></i> Barang Rejected
                            </button>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 font-medium">Hanya pengelola stok yang bisa input opname.</p>
                    @endif
                    <div class="flex items-center gap-1.5 sm:ml-auto text-[10px] font-black uppercase tracking-wider">
                        <span class="flex items-center gap-1.5 text-blue-600"><span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px]">1</span> Hitung</span>
                        <span class="text-slate-300">→</span>
                        <span class="flex items-center gap-1.5 text-blue-600"><span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px]">2</span> Simpan</span>
                        <span class="text-slate-300">→</span>
                        <span class="flex items-center gap-1.5 text-slate-400"><span class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[10px]">3</span> BA</span>
                    </div>
                </div>
            </x-ui.glass-card>

            {{-- Rekap opname: kartu per-bulan, klik kartu = modal detail bulan --}}
            <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full max-w-full mx-auto !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-indigo-500 shadow-lg">
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div><h3 class="text-sm font-bold text-slate-800 leading-tight">Rekap Opname — {{ $tahun ?? '' }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium leading-tight">Januari–Desember · klik kartu bulan untuk lihat datanya.</p></div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <form method="GET" action="{{ route('warehouse.stockopname.index') }}">
                            <x-ui.filter-select name="tahun" value="{{ $tahun ?? '' }}" onchange="this.form.submit()" :options="$listTahun ?? []" class="w-28" />
                        </form>
                        <x-ui.export-button variant="excel" href="#" title="Export Excel (segera hadir)" onclick="return false;" />
                        <x-ui.export-button variant="pdf" href="#" title="Export PDF (segera hadir)" onclick="return false;" />
                        @if($canManage)
                        <a href="{{ route('warehouse.stockopname.catat', ['bulan' => $bulan ?? now()->format('Y-m')]) }}" class="shrink-0 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-sm">
                            <i class="fas fa-plus"></i><span>Catat Opname</span>
                        </a>
                        @endif
                    </div>
                </div>
                <div class="p-4 sm:p-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                    @forelse($rekapPerBulan ?? [] as $b)
                        @php
                            $adaDraf = ($b['menunggu'] ?? 0) > 0;
                            $lengkap = ($b['total'] ?? 0) > 0 && ($b['sudah'] ?? 0) >= ($b['total'] ?? 0) && ! $adaDraf;
                            $terisi = ($b['sudah'] ?? 0) > 0;
                        @endphp
                        <button type="button" data-rekap-bulan="{{ $b['key'] }}" data-rekap-label="{{ $b['label'] }}" data-rekap-sudah="{{ $b['sudah'] ?? 0 }}" data-rekap-total="{{ $b['total'] ?? 0 }}" data-rekap-menunggu="{{ $b['menunggu'] ?? 0 }}"
                            class="text-left bg-white border rounded-2xl p-3.5 shadow-sm hover:shadow-md transition-all {{ ($b['selected'] ?? false) ? 'border-indigo-400 ring-2 ring-indigo-200' : ($lengkap ? 'border-emerald-200' : (($terisi || $adaDraf) ? 'border-amber-200' : 'border-slate-200')) }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-black text-slate-700 uppercase tracking-wide">{{ $b['label'] }}</span>
                                @if($lengkap)
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">LENGKAP</span>
                                @elseif($adaDraf)
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">SELISIH</span>
                                @elseif($terisi)
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">SUDAH DIISI</span>
                                @else
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">BELUM DIISI</span>
                                @endif
                            </div>
                            <div class="mt-1.5 text-sm font-black {{ $lengkap ? 'text-emerald-600' : (($terisi || $adaDraf) ? 'text-amber-600' : 'text-slate-400') }}">{{ $b['sudah'] ?? 0 }}<span class="text-xs font-bold text-slate-400"> / {{ $b['total'] ?? 0 }} SKU</span></div>
                            <div class="mt-0.5 text-[11px] text-slate-500 font-medium">
                                @if($lengkap)
                                    Semua SKU sudah diopname & selesai.
                                @elseif($terisi || $adaDraf)
                                    {{ $terisi ? 'Sebagian sudah diopname' : 'Belum ada yang selesai' }}{{ ($b['entries'] ?? 0) > ($b['sudah'] ?? 0) ? ' · ' . $b['entries'] . ' entri' : '' }}@if($adaDraf)<span class="text-amber-600 font-bold"> · {{ $b['menunggu'] }} selisih menunggu</span>@endif.
                                @else
                                    Belum ada opname bulan ini.
                                @endif
                            </div>
                        </button>
                    @empty
                        <div class="col-span-full text-center py-8 text-slate-400 text-sm font-medium">Belum ada data rekap.</div>
                    @endforelse
                </div>
            </x-ui.glass-card>
        </div>
    </div>


    {{-- Modal Barang Rejected --}}
    <x-ui.modal id="modal-scrap" title="Barang Rejected" subtitle="Rusak / expired / retur — wajib alasan, stok langsung dikurangi." icon="fa-trash-can" iconClass="bg-rose-600 text-white" maxWidth="max-w-md">
        @if($canManage)
        <form method="POST" action="{{ route('warehouse.stockopname.scrap') }}" class="space-y-3" onsubmit="confirmSubmit(event, 'Catat rejected? Stok akan dikurangi permanen.');">
            @csrf
            <div><label class="ui-label">Barang <span class="text-red-500">*</span></label>
                <select name="product_id" required class="ui-input !bg-white"><option value="">-- Pilih barang --</option>@foreach($productOptions as $o)<option value="{{ $o->id }}">{{ $o->product_code }} — {{ $o->product_name }} (stok: {{ $o->stock }})</option>@endforeach</select></div>
            <div><label class="ui-label">Qty <span class="text-red-500">*</span></label><input type="number" name="qty" min="0.01" step="any" required class="ui-input !bg-white"></div>
            <div><label class="ui-label">Alasan <span class="text-red-500">*</span></label><input type="text" name="reason" required class="ui-input !bg-white" placeholder="Mis. rusak, expired"></div>
            <x-slot:footer>
                <button type="button" data-modal-close class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-500/20">Catat Rejected</button>
            </x-slot:footer>
        </form>
        @endif
    </x-ui.modal>

    {{-- Modal detail riwayat (klik baris) --}}
    <x-ui.modal id="modal-opname-detail" title="Detail Hasil Opname" subtitle="Rincian penyesuaian per dokumen." icon="fa-circle-info" iconClass="bg-slate-600 text-white" maxWidth="max-w-md">
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-xs">
            <div><dt class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Waktu</dt><dd id="odWaktu" class="font-bold text-slate-700 mt-0.5">-</dd></div>
            <div><dt class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">No. Dok</dt><dd id="odDok" class="font-mono font-bold text-slate-700 mt-0.5">-</dd></div>
            <div class="col-span-2"><dt class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Produk</dt><dd id="odProduk" class="font-bold text-slate-800 mt-0.5">-</dd></div>
            <div><dt class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Tercatat</dt><dd id="odTercatat" class="font-black text-slate-700 mt-0.5">-</dd></div>
            <div><dt class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Terhitung</dt><dd id="odTerhitung" class="font-black text-slate-700 mt-0.5">-</dd></div>
            <div><dt class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Selisih</dt><dd id="odSelisih" class="font-black mt-0.5">-</dd></div>
            <div><dt class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Oleh / Validasi</dt><dd id="odOleh" class="font-bold text-slate-700 mt-0.5">-</dd></div>
            <div class="col-span-2"><dt class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Catatan</dt><dd id="odCatatan" class="font-medium text-slate-600 mt-0.5">-</dd></div>
        </dl>
        <x-slot:footer>
            <button type="button" data-modal-close class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50">Tutup</button>
            <a id="odBa" href="#" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold">Cetak BA</a>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Modal data opname per-bulan: kiri daftar hasil, kanan ringkasan + aksi --}}
    <x-ui.modal id="modal-rekap-bulan" title="Detail Opname" subtitle="Kiri: hasil per barang · Kanan: ringkasan + aksi." icon="fa-calendar-days" iconClass="bg-indigo-600 text-white" maxWidth="max-w-4xl">
        <div class="grid gap-3 md:grid-cols-5">
            <div class="md:col-span-3 min-w-0">
                <div class="flex items-center gap-1.5 mb-1.5">
                    <span class="w-5 h-5 rounded-lg bg-indigo-600 text-white text-[10px] font-black flex items-center justify-center shrink-0">1</span>
                    <div class="text-[11px] font-black text-slate-800 leading-tight">Hasil opname</div>
                </div>
                <div id="rbList" class="space-y-1.5 max-h-[50vh] md:max-h-[55vh] overflow-y-auto pr-0.5"></div>
            </div>
            <div class="md:col-span-2 min-w-0">
                <div class="flex items-center gap-1.5 mb-1.5">
                    <span class="w-5 h-5 rounded-lg bg-emerald-600 text-white text-[10px] font-black flex items-center justify-center shrink-0">2</span>
                    <div class="text-[11px] font-black text-slate-800 leading-tight">Ringkasan & aksi</div>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 space-y-2">
                    <p id="rbSummary" class="text-[11px] text-slate-500 font-medium">-</p>
                    <div class="flex gap-2">
                        <x-ui.export-button variant="excel" href="#" title="Export Excel (segera hadir)" onclick="return false;" />
                        <x-ui.export-button variant="pdf" href="#" title="Export PDF (segera hadir)" onclick="return false;" />
                    </div>
                    @if($canManage)
                    <a id="rbEdit" href="#" class="block text-center px-4 py-2 bg-white border border-indigo-300 text-indigo-700 rounded-xl text-xs font-bold hover:bg-indigo-50">Edit</a>
                    <span id="rbFinishWrap" class="block">
                        <button type="button" id="rbFinish" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold">Selesaikan <span id="rbFinishN"></span></button>
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" data-modal-close class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50">Tutup</button>
        </x-slot:footer>
    </x-ui.modal>
    <script id="rekap-bulan-data" type="application/json">@json($rekapDetailPerBulan ?? [])</script>
    <script id="catat-base-url" type="application/json">@json(route('warehouse.stockopname.catat'))</script>

    @if($canManage)
    <script>
        (function () {
            // Halaman rekap: hanya lihat hasil. Pendataan pindah ke halaman Catat Opname.
            function escHtml(v) {
                return String(v === null || v === undefined ? '' : v).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }
            // Detail riwayat: klik baris -> modal
            document.querySelectorAll('[data-opname-detail]').forEach(function (row) {
                row.addEventListener('click', function () {
                    var d = row.dataset;
                    document.getElementById('odWaktu').textContent = d.waktu || '-';
                    document.getElementById('odDok').textContent = d.dok || '-';
                    document.getElementById('odProduk').textContent = d.produk || '-';
                    document.getElementById('odTercatat').textContent = d.tercatat || '-';
                    document.getElementById('odTerhitung').textContent = d.terhitung || '-';
                    var s = document.getElementById('odSelisih');
                    s.textContent = d.selisih || '-';
                    s.className = 'font-black mt-0.5 ' + (parseFloat(d.selisih) > 0 ? 'text-emerald-600' : (parseFloat(d.selisih) < 0 ? 'text-rose-600' : 'text-slate-700'));
                    document.getElementById('odOleh').textContent = (d.oleh || '-') + (d.validator && d.validator !== '-' ? ' / ' + d.validator : '');
                    document.getElementById('odCatatan').textContent = d.catatan || '-';
                    document.getElementById('odBa').href = d.ba || '#';
                    if (window.uiModalOpen) window.uiModalOpen('modal-opname-detail');
                });
            });
            // Modal data per-bulan: klik kartu bulan -> popup hasil + tombol aksi
            document.querySelectorAll('[data-rekap-bulan]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var key = btn.getAttribute('data-rekap-bulan');
                    var label = btn.getAttribute('data-rekap-label') || key;
                    var sudahAttr = parseInt(btn.getAttribute('data-rekap-sudah') || '0', 10);
                    var total = parseInt(btn.getAttribute('data-rekap-total') || '0', 10);
                    var catatBase = 'catat';
                    try { catatBase = JSON.parse(document.getElementById('catat-base-url').textContent || '"catat"'); } catch (e) { catatBase = 'catat'; }
                    var urlCatat = catatBase + '?bulan=' + encodeURIComponent(key);
                    var urlSisa = urlCatat + '&hanya_belum=1';
                    var all = {};
                    try { all = JSON.parse(document.getElementById('rekap-bulan-data').textContent || '{}'); } catch (e) { all = {}; }
                    var items = all[key] || [];
                    var sesuai = items.filter(function (it) { return !it.pending && it.selisih !== null && Math.abs(it.selisih) < 0.005; }).length;
                    var draf = items.filter(function (it) { return !!it.pending; }).length;
                    var sudahDone = items.length - sesuai - draf;
                    // Satu tombol: belum selesai = "Catat", sudah selesai = "Edit".
                    // Bulan sebagian → buka halaman Catat dengan saringan "belum" aktif.
                    var finished = total > 0 && sudahAttr >= total && draf <= 0;
                    var rbEdit = document.getElementById('rbEdit');
                    if (rbEdit) {
                        rbEdit.href = (!finished && sudahAttr > 0 && total > 0 && sudahAttr < total) ? urlSisa : urlCatat;
                        rbEdit.textContent = finished ? 'Edit' : 'Catat';
                    }
                    var titleEl = document.getElementById('modal-rekap-bulan-title');
                    if (titleEl) titleEl.textContent = 'Detail Opname — ' + label;
                    document.getElementById('rbSummary').textContent = label + ' · ' + items.length + ' entri · ' + sesuai + ' sesuai · ' + sudahDone + ' selisih · ' + draf + ' menunggu.';
                    // Tombol Selesaikan: arahkan ke halaman Catat (pengisian nama BA + rekap draf di sana).
                    var rbFinishWrap = document.getElementById('rbFinishWrap');
                    var rbFinish = document.getElementById('rbFinish');
                    var rbFinishN = document.getElementById('rbFinishN');
                    if (rbFinish) rbFinish.onclick = function () {
                        if (window.uiModalClose) window.uiModalClose('modal-rekap-bulan');
                        window.location.href = urlCatat;
                    };
                    if (rbFinishN) rbFinishN.textContent = '(' + draf + ')';
                    if (rbFinishWrap) rbFinishWrap.classList.toggle('hidden', draf <= 0);
                    var list = document.getElementById('rbList');
                    if (!items.length) {
                        list.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm font-medium">Belum ada opname bulan ' + label + '.</div>';
                    } else {
                        list.innerHTML = items.map(function (it) {
                            var isSesuai = !it.pending && it.selisih !== null && Math.abs(it.selisih) < 0.005;
                            var badge = it.pending
                                ? '<span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">SELISIH ' + (it.selisih > 0 ? '+' : '') + it.selisih + '</span>'
                                : (isSesuai
                                    ? '<span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-sky-100 text-sky-700">SESUAI</span>'
                                    : '<span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">SELISIH ' + (it.selisih > 0 ? '+' : '') + (it.selisih ?? '-') + '</span>');
                            var border = it.pending ? 'border-amber-300 bg-amber-50/40' : (isSesuai ? 'border-sky-200' : 'border-emerald-200');
                            var boxBg = it.pending ? 'bg-amber-50' : (isSesuai ? 'bg-sky-50' : 'bg-emerald-50');
                            var boxTx = it.pending ? 'text-amber-500' : (isSesuai ? 'text-sky-500' : 'text-emerald-500');
                            var boxNum = it.pending ? 'text-amber-600' : (isSesuai ? 'text-sky-600' : 'text-emerald-600');
                            return '<div class="bg-white border ' + border + ' rounded-xl p-2">'
                                + '<div class="flex items-center justify-between gap-2"><span class="text-[11px] font-bold text-slate-800 truncate">' + escHtml(it.produk) + '</span>' + badge + '</div>'
                                + '<div class="grid grid-cols-3 gap-1 mt-1.5 text-center">'
                                + '<div class="bg-slate-50 rounded-md px-1 py-1"><div class="text-[9px] font-bold text-slate-400 uppercase">Sistem</div><div class="text-xs font-black text-slate-700">' + (it.tercatat ?? '-') + '</div></div>'
                                + '<div class="bg-slate-50 rounded-md px-1 py-1"><div class="text-[9px] font-bold text-slate-400 uppercase">Fisik</div><div class="text-xs font-black text-slate-700">' + (it.terhitung ?? '-') + '</div></div>'
                                + '<div class="rounded-md px-1 py-1 ' + boxBg + '"><div class="text-[9px] font-bold uppercase ' + boxTx + '">Selisih</div><div class="text-xs font-black ' + boxNum + '">' + ((it.selisih !== null && it.selisih > 0 ? '+' : '') + (it.selisih ?? '-')) + '</div></div>'
                                + '</div>'
                                + '<div class="mt-1 text-[10px] text-slate-400 font-medium">' + escHtml(it.waktu) + ' · ' + escHtml(it.oleh) + (it.catatan && it.catatan !== '-' ? ' · ' + escHtml(it.catatan) : '') + ((it.pembuat || it.pemeriksa) ? ' · BA: ' + escHtml(it.pembuat || '-') + ' / ' + escHtml(it.pemeriksa || '-') : '') + '</div>'
                                + '</div>';
                        }).join('');
                    }
                    if (window.uiModalOpen) window.uiModalOpen('modal-rekap-bulan');
                });
            });
            // Buka kembali modal scrap bila validasi rejected gagal
            @if($errors->any() && old('qty'))
                window.addEventListener('DOMContentLoaded', function () { if (window.uiModalOpen) window.uiModalOpen('modal-scrap'); });
            @endif
        })();
    </script>
    @endif
</x-layout-users>
