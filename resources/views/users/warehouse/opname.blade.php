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
                <x-ui.page-header title="Stock Opname — {{ $namaBulan ?? '' }}" subtitle="Pantau hasil hitung fisik, draf yang menunggu diselesaikan, dan Berita Acara bulan ini." icon="fa-clipboard-check" :sticky="false" />
            </div>

            {{-- Header mobile ala sales command center --}}
            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Opname {{ $namaBulan ?? '' }}</h2>
                        <p class="text-xs text-blue-100 font-medium leading-normal mt-0.5">
                            Hasil hitung fisik, draf, dan Berita Acara.
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

            {{-- Rekap opname: kartu per-bulan, klik kartu = modal detail bulan --}}
            <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full max-w-full mx-auto !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-indigo-500 shadow-lg">
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div><h3 class="text-sm font-bold text-slate-800 leading-tight">Rekap Opname — {{ $tahun ?? '' }}</h3>
                    <p class="text-[11px] text-slate-500 font-medium leading-tight">Januari–Desember · klik kartu bulan untuk lihat datanya.</p></div>
                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                        <form method="GET" action="{{ route('warehouse.stockopname.index') }}">
                            <x-ui.filter-select name="tahun" value="{{ $tahun ?? '' }}" onchange="this.form.submit()" :options="$listTahun ?? []" class="w-28" />
                        </form>
                        @if($canManage)
                        {{-- Aksi utama memakai tinggi 30px agar sejajar dengan filter & tombol export global. --}}
                        <a href="{{ route('warehouse.stockopname.catat', ['bulan' => $bulan ?? now()->format('Y-m')]) }}" class="shrink-0 inline-flex items-center justify-center gap-1.5 h-[30px] px-3 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold leading-none shadow-sm transition-colors">
                            <i class="fas fa-plus text-[11px]"></i><span>Catat Opname</span>
                        </a>
                        <button type="button" onclick="uiModalOpen('modal-scrap')" class="shrink-0 inline-flex items-center justify-center gap-1.5 h-[30px] px-3 rounded-lg bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-bold leading-none shadow-sm transition-colors">
                            <i class="fas fa-trash-can text-[11px]"></i><span>Barang Rejected</span>
                        </button>
                        @endif
                    </div>
                </div>
                <div class="p-4 sm:p-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                    @forelse($rekapPerBulan ?? [] as $b)
                        @php
                            $adaDraf = ($b['menunggu'] ?? 0) > 0;
                            $selesaiN = (int) ($b['selesai'] ?? 0);
                            $lengkap = ($b['total'] ?? 0) > 0 && $selesaiN >= ($b['total'] ?? 0) && ! $adaDraf;
                            $terisi = $selesaiN > 0;
                            $baBulan = $selesaiN > 0 && ! $adaDraf;
                        @endphp
                        <button type="button" data-rekap-bulan="{{ $b['key'] }}" data-rekap-label="{{ $b['label'] }}" data-rekap-sudah="{{ $b['sudah'] ?? 0 }}" data-rekap-total="{{ $b['total'] ?? 0 }}" data-rekap-ba="{{ $baBulan ? route('warehouse.stockopname.print-bulan', ['bulan' => $b['key']]) : '' }}"
                            class="text-left bg-white border rounded-2xl p-3.5 shadow-sm hover:shadow-md transition-all {{ ($b['selected'] ?? false) ? 'border-indigo-400 ring-2 ring-indigo-200' : ($lengkap ? 'border-emerald-200' : (($terisi || $adaDraf) ? 'border-amber-200' : 'border-slate-200')) }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-black text-slate-700 uppercase tracking-wide">{{ $b['label'] }}</span>
                                @if($lengkap)
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">LENGKAP</span>
                                @elseif($adaDraf)
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">DRAF</span>
                                @elseif($terisi)
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-sky-100 text-sky-700">SELESAI</span>
                                @else
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">BELUM DIISI</span>
                                @endif
                            </div>
                            <div class="mt-1.5 text-sm font-black {{ $lengkap ? 'text-emerald-600' : (($terisi || $adaDraf) ? 'text-amber-600' : 'text-slate-400') }}">{{ $selesaiN }}<span class="text-xs font-bold text-slate-400"> / {{ $b['total'] ?? 0 }} SKU</span></div>
                            <div class="mt-0.5 text-[11px] text-slate-500 font-medium">
                                @if($lengkap)
                                    Semua SKU sudah diopname & selesai.
                                @elseif($terisi || $adaDraf)
                                    {{ $terisi ? 'Sebagian sudah selesai' : 'Belum ada yang selesai' }}{{ ($b['entries'] ?? 0) > $selesaiN ? ' · ' . $b['entries'] . ' entri' : '' }}@if($adaDraf)<span class="text-amber-600 font-bold"> · {{ $b['menunggu'] }} draf menunggu Selesaikan</span>@endif.
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

    {{-- Modal data opname per-bulan: hasil, ringkasan status, lalu aksi yang tersedia --}}
    <x-ui.modal id="modal-rekap-bulan" title="Detail Opname" subtitle="Hasil per barang, ringkasan status, dan aksi yang bisa dilakukan." icon="fa-calendar-days" iconClass="bg-indigo-600 text-white" maxWidth="max-w-4xl">
        <div class="grid gap-3 md:grid-cols-5">
            <div class="md:col-span-3 min-w-0">
                <div class="flex items-center gap-1.5 mb-1.5">
                    <span class="w-5 h-5 rounded-lg bg-indigo-600 text-white text-[10px] font-black flex items-center justify-center shrink-0">1</span>
                    <div class="text-[11px] font-black text-slate-800 leading-tight">Hasil opname</div>
                </div>
                <div id="rbList" class="space-y-1.5 max-h-[50vh] md:max-h-[55vh] overflow-y-auto pr-0.5"></div>
            </div>
            <div class="md:col-span-2 min-w-0 space-y-3">
                <div>
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="w-5 h-5 rounded-lg bg-emerald-600 text-white text-[10px] font-black flex items-center justify-center shrink-0">2</span>
                        <div class="text-[11px] font-black text-slate-800 leading-tight">Ringkasan</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 space-y-2.5">
                        <p id="rbSummary" class="text-[11px] font-bold text-slate-700 leading-snug">-</p>
                        <div class="grid grid-cols-2 gap-2 text-[10px] font-bold">
                            <div class="rounded-lg bg-white border border-slate-200 px-2 py-1.5">
                                <div class="text-slate-400 uppercase tracking-wider">Entri</div>
                                <div id="rbTotal" class="text-xs font-black text-slate-700">-</div>
                            </div>
                            <div class="rounded-lg bg-white border border-slate-200 px-2 py-1.5">
                                <div class="text-slate-400 uppercase tracking-wider">Selesai</div>
                                <div id="rbDone" class="text-xs font-black text-slate-700">-</div>
                            </div>
                            <div class="rounded-lg bg-white border border-slate-200 px-2 py-1.5">
                                <div class="text-slate-400 uppercase tracking-wider">Sesuai</div>
                                <div id="rbSesuai" class="text-xs font-black text-sky-600">-</div>
                            </div>
                            <div class="rounded-lg bg-white border border-slate-200 px-2 py-1.5">
                                <div class="text-slate-400 uppercase tracking-wider">Selisih</div>
                                <div id="rbSelisih" class="text-xs font-black text-emerald-600">-</div>
                            </div>
                            <div class="rounded-lg bg-white border border-slate-200 px-2 py-1.5">
                                <div class="text-slate-400 uppercase tracking-wider">Draf</div>
                                <div id="rbDraf" class="text-xs font-black text-amber-600">-</div>
                            </div>
                            <div class="rounded-lg bg-white border border-slate-200 px-2 py-1.5">
                                <div class="text-slate-400 uppercase tracking-wider">Berita Acara</div>
                                <div id="rbBaStatus" class="text-xs font-black text-slate-500">-</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="w-5 h-5 rounded-lg bg-blue-600 text-white text-[10px] font-black flex items-center justify-center shrink-0">3</span>
                        <div class="text-[11px] font-black text-slate-800 leading-tight">Aksi</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 space-y-2">
                        @if($canManage)
                        {{-- Aksi detail bulan: tinggi sama dengan tombol export/filter (30px). --}}
                        <a id="rbEdit" href="#" class="flex items-center justify-center h-[30px] px-3 bg-white border border-indigo-300 text-indigo-700 rounded-lg text-xs font-bold leading-none hover:bg-indigo-50">Catat</a>
                        <a id="rbBa" href="#" class="hidden flex items-center justify-center h-[30px] px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold leading-none">Download BA</a>
                        {{-- Rekap periode: isi sama, hanya berbeda format file. --}}
                        <div class="grid grid-cols-2 gap-2">
                            <a id="rbExportExcel" href="#" class="flex items-center justify-center gap-1.5 h-[30px] px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold leading-none" title="Download rekap opname dalam format Excel">
                                <i class="fas fa-file-excel text-[11px]"></i> Excel
                            </a>
                            <a id="rbExportPdf" href="#" class="flex items-center justify-center gap-1.5 h-[30px] px-3 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold leading-none" title="Download rekap opname dalam format PDF">
                                <i class="fas fa-file-pdf text-[11px]"></i> PDF
                            </a>
                        </div>
                        <span id="rbFinishWrap" class="block">
                            <button type="button" id="rbFinish" class="inline-flex items-center justify-center w-full h-[30px] px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold leading-none">Selesaikan <span id="rbFinishN"></span></button>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" data-modal-close class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50">Tutup</button>
        </x-slot:footer>
    </x-ui.modal>
    <script id="rekap-bulan-data" type="application/json">@json($rekapDetailPerBulan ?? [])</script>
    <script id="catat-base-url" type="application/json">@json(route('warehouse.stockopname.catat'))</script>
    <script id="rekap-export-excel-url" type="application/json">@json(route('warehouse.stockopname.export-rekap', ['bulan' => '__BULAN__', 'format' => 'excel']))</script>
    <script id="rekap-export-pdf-url" type="application/json">@json(route('warehouse.stockopname.export-rekap', ['bulan' => '__BULAN__', 'format' => 'pdf']))</script>

    @if($canManage)
    <script>
        (function () {
            'use strict';

            // Halaman rekap hanya menampilkan hasil. Pendataan tetap di halaman Catat Opname.
            function escHtml(value) {
                return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (char) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
                });
            }

            // Membaca data JSON yang ditempel server ke dalam <script type="application/json">.
            function readJson(id, fallback) {
                var el = document.getElementById(id);
                if (!el) return fallback;
                try {
                    var parsed = JSON.parse(el.textContent || 'null');
                    return parsed === null ? fallback : parsed;
                } catch (e) {
                    return fallback;
                }
            }

            function setText(id, value) {
                var el = document.getElementById(id);
                if (el) el.textContent = value;
            }

            // Format angka selisih: 0 tetap "0", nilai positif diberi tanda +.
            function signedNumber(value) {
                if (value === null || value === undefined || value === '' || isNaN(Number(value))) return '-';
                var number = Number(value);
                return (number > 0 ? '+' : '') + number;
            }

            function isNearZero(value) {
                return value !== null && value !== undefined && Math.abs(Number(value)) < 0.005;
            }

            var catatBase = readJson('catat-base-url', 'catat');
            var detailByMonth = readJson('rekap-bulan-data', {});
            var exportUrls = {
                excel: readJson('rekap-export-excel-url', ''),
                pdf: readJson('rekap-export-pdf-url', '')
            };

            // URL export berisi placeholder __BULAN__ agar bisa dipakai ulang untuk semua kartu.
            function setExportLink(id, format, key) {
                var link = document.getElementById(id);
                if (!link) return;
                var base = exportUrls[format] || '';
                link.href = base ? base.replace('__BULAN__', encodeURIComponent(key)) : '#';
            }

            // Klik kartu bulan: tampilkan rekap barang, ringkasan, dan aksi yang relevan.
            document.querySelectorAll('[data-rekap-bulan]').forEach(function (card) {
                card.addEventListener('click', function () {
                    var key = card.dataset.rekapBulan;
                    var label = card.dataset.rekapLabel || key;
                    var savedCount = parseInt(card.dataset.rekapSudah || '0', 10) || 0;
                    var totalSku = parseInt(card.dataset.rekapTotal || '0', 10) || 0;
                    var baUrl = card.dataset.rekapBa || '';
                    var catatUrl = catatBase + '?bulan=' + encodeURIComponent(key);
                    var unfinishedUrl = catatUrl + '&hanya_belum=1';

                    var items = detailByMonth[key] || [];
                    var doneItems = items.filter(function (item) { return !item.pending; });
                    var draftCount = items.length - doneItems.length;
                    var sameCount = doneItems.filter(function (item) { return isNearZero(item.selisih); }).length;
                    var diffCount = doneItems.length - sameCount;
                    var isFinished = !!baUrl; // BA sudah terbit = periode selesai.

                    // "Catat" untuk periode berjalan; "Edit" setelah BA terbit.
                    // Jika baru sebagian yang dihitung, buka langsung saringan "belum".
                    var editAction = document.getElementById('rbEdit');
                    if (editAction) {
                        var isPartial = !isFinished && savedCount > 0 && totalSku > 0 && savedCount < totalSku;
                        editAction.href = isPartial ? unfinishedUrl : catatUrl;
                        editAction.textContent = isFinished ? 'Edit' : 'Catat';
                    }

                    var baAction = document.getElementById('rbBa');
                    if (baAction) {
                        baAction.classList.toggle('hidden', !baUrl);
                        if (baUrl) baAction.href = baUrl;
                        else baAction.removeAttribute('href');
                    }

                    setExportLink('rbExportExcel', 'excel', key);
                    setExportLink('rbExportPdf', 'pdf', key);

                    setText('rbTotal', items.length);
                    setText('rbDone', doneItems.length);
                    setText('rbSesuai', sameCount);
                    setText('rbSelisih', diffCount);
                    setText('rbDraf', draftCount);

                    var baStatus = document.getElementById('rbBaStatus');
                    if (baStatus) {
                        baStatus.textContent = baUrl ? 'Tersedia' : (draftCount > 0 ? 'Menunggu' : 'Belum ada');
                        baStatus.className = 'text-xs font-black ' + (baUrl ? 'text-blue-600' : (draftCount > 0 ? 'text-amber-600' : 'text-slate-400'));
                    }

                    var skuPart = totalSku > 0 ? ' dari ' + totalSku + ' SKU' : '';
                    var summary;
                    if (items.length === 0) {
                        summary = label + ' · belum ada hasil opname pada periode ini.';
                    } else if (draftCount > 0) {
                        summary = label + ' · ' + items.length + skuPart + ' terhitung · '
                            + draftCount + ' draf menunggu Selesaikan.';
                    } else {
                        summary = label + ' · ' + items.length + skuPart + ' terhitung · semua entri selesai'
                            + (diffCount > 0 ? ' (' + sameCount + ' sesuai, ' + diffCount + ' selisih).' : ' dan sesuai sistem.');
                    }
                    setText('rbSummary', summary);

                    // Selesaikan di modal hanya membuka halaman Catat; proses final tetap di sana.
                    var finishAction = document.getElementById('rbFinish');
                    if (finishAction) {
                        finishAction.onclick = function () {
                            if (window.uiModalClose) window.uiModalClose('modal-rekap-bulan');
                            window.location.href = catatUrl;
                        };
                    }
                    setText('rbFinishN', '(' + draftCount + ')');

                    var finishWrap = document.getElementById('rbFinishWrap');
                    if (finishWrap) finishWrap.classList.toggle('hidden', draftCount <= 0);

                    var list = document.getElementById('rbList');
                    if (list) {
                        if (items.length === 0) {
                            list.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm font-medium">Belum ada opname bulan ' + escHtml(label) + '.</div>';
                        } else {
                            list.innerHTML = items.map(function (item) {
                                var isSame = !item.pending && isNearZero(item.selisih);
                                var badge = item.pending
                                    ? '<span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">DRAF ' + signedNumber(item.selisih) + '</span>'
                                    : (isSame
                                        ? '<span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-sky-100 text-sky-700">SESUAI</span>'
                                        : '<span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">SELISIH ' + signedNumber(item.selisih) + '</span>');
                                var border = item.pending ? 'border-amber-300 bg-amber-50/40' : (isSame ? 'border-sky-200' : 'border-emerald-200');
                                var boxBg = item.pending ? 'bg-amber-50' : (isSame ? 'bg-sky-50' : 'bg-emerald-50');
                                var boxText = item.pending ? 'text-amber-500' : (isSame ? 'text-sky-500' : 'text-emerald-500');
                                var boxNumber = item.pending ? 'text-amber-600' : (isSame ? 'text-sky-600' : 'text-emerald-600');
                                var note = item.catatan && item.catatan !== '-' ? ' · ' + escHtml(item.catatan) : '';
                                var baNames = (item.pembuat || item.pemeriksa)
                                    ? ' · BA: ' + escHtml(item.pembuat || '-') + ' / ' + escHtml(item.pemeriksa || '-')
                                    : '';

                                return '<div class="bg-white border ' + border + ' rounded-xl p-2">'
                                    + '<div class="flex items-center justify-between gap-2"><span class="text-[11px] font-bold text-slate-800 truncate">' + escHtml(item.produk) + '</span>' + badge + '</div>'
                                    + '<div class="grid grid-cols-3 gap-1 mt-1.5 text-center">'
                                    + '<div class="bg-slate-50 rounded-md px-1 py-1"><div class="text-[9px] font-bold text-slate-400 uppercase">Sistem</div><div class="text-xs font-black text-slate-700">' + (item.tercatat ?? '-') + '</div></div>'
                                    + '<div class="bg-slate-50 rounded-md px-1 py-1"><div class="text-[9px] font-bold text-slate-400 uppercase">Fisik</div><div class="text-xs font-black text-slate-700">' + (item.terhitung ?? '-') + '</div></div>'
                                    + '<div class="rounded-md px-1 py-1 ' + boxBg + '"><div class="text-[9px] font-bold uppercase ' + boxText + '">Selisih</div><div class="text-xs font-black ' + boxNumber + '">' + signedNumber(item.selisih) + '</div></div>'
                                    + '</div>'
                                    + '<div class="mt-1 text-[10px] text-slate-400 font-medium">' + escHtml(item.waktu) + ' · ' + escHtml(item.oleh) + note + baNames + '</div>'
                                    + '</div>';
                            }).join('');
                        }
                    }

                    if (window.uiModalOpen) window.uiModalOpen('modal-rekap-bulan');
                });
            });

            // Buka kembali modal rejected bila validasi server gagal.
            @if($errors->any() && old('qty'))
                window.addEventListener('DOMContentLoaded', function () {
                    if (window.uiModalOpen) window.uiModalOpen('modal-scrap');
                });
            @endif
        })();
    </script>
    @endif
</x-layout-users>
