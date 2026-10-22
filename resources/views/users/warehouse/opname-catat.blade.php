<x-layout-users title="Catat Opname" :fluid="true">
    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        /* Halaman ini: navbar ikut scroll (tidak sticky). */
        header.sticky { position: static !important; }
        /* Kunci: kartu header tidak sticky di halaman ini. */
        .ui-page-header { position: static !important; top: auto !important; }
        .mesh-bg {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0; pointer-events: none;
            background-color: #ede9fe;
            background-image:
                radial-gradient(at 0% 0%, rgba(255,255,255,0.6) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99,102,241,0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59,130,246,0.15) 0px, transparent 50%);
        }
        /* Halaman catat: 2 kolom murni via CSS biasa (tanpa rebuild Tailwind) */
        #catatGrid { display: grid; grid-template-columns: 1fr; gap: 12px; align-items: start; }
        #catatGridLeft, #catatGridRight { min-width: 0; min-height: 0; }
        /* Daftar produk: beri napas kiri-kanan agar kartu tidak mepet tepi & tidak tertutup scrollbar */
        #catatList { scrollbar-gutter: stable; overflow-x: hidden; margin-top: 16px; padding-top: 4px; padding-left: 4px; padding-right: 12px; padding-bottom: 4px; scroll-padding-top: 8px; }
        #catatList::-webkit-scrollbar { width: 8px; }
        #catatList::-webkit-scrollbar-thumb { border-radius: 8px; }
        @media (min-width: 900px) {
            #catatGrid { grid-template-columns: minmax(0, 7fr) minmax(0, 4fr); }
            /* Kiri (daftar produk) dikunci ke viewport + scroll di dalam.
               Dibuat lebih tinggi: rapat ke bawah viewport.
               Kanan (isi fisik) dibiarkan memanjang full tanpa scroll dalam. */
            #catatGridLeft { max-height: calc(100vh - 160px); max-height: calc(100dvh - 160px); min-height: 0; }
            #catatGridLeft { display: flex; flex-direction: column; overflow: hidden; }
            #catatGridRight { position: sticky; top: 12px; }
            #catatList { flex: 1 1 auto; min-height: 0 !important; max-height: none !important; }
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 relative overflow-x-clip text-slate-800 pb-16">
        <div class="mesh-bg"></div>
        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-4 flex-1 flex flex-col items-stretch">
            <div class="w-full flex justify-start">
                <x-ui.back-button id="catat-back-btn" :href="$backRoute ?? route('warehouse.stockopname.index', ['bulan' => $bulan ?? now()->format('Y-m')])" :label="$backText ?? 'Back to Opname Recap'" />
            </div>

            <div class="hidden md:block">
                <x-ui.page-header title="Catat Opname — {{ $namaTanggal }}" subtitle="Tanggal pelaksanaan {{ $namaTanggal }} · masuk rekap {{ $namaBulan }}. Yang selisih tidak mengubah stok sebelum Selesaikan." icon="fa-clipboard-list" :sticky="false" />
            </div>

            {{-- Header mobile ala halaman Opname --}}
            <div class="block md:hidden rounded-2xl px-5 py-6 text-white shadow-md flex items-center justify-between gap-4 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                <div class="relative z-10 flex-1 min-w-0">
                    <h2 class="text-sm font-black tracking-wider uppercase leading-snug truncate">Catat Opname</h2>
                    <p class="text-xs text-blue-100 font-medium leading-normal truncate mt-0.5">
                        Pilih barang &amp; simpan hasil hitung.
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white text-base shrink-0 shadow-inner relative z-10">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>

            {{-- Progress + filter --}}
            <x-ui.glass-card class="!rounded-3xl !bg-white border-t-4 border-t-indigo-500 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-black">
                        <button type="button" id="totalReset" title="Tampilkan semua data (lepas semua saringan)" class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:border-indigo-300 hover:text-indigo-600"><span id="cntAll">{{ $totalSku }}</span> All</button>
                        <button type="button" data-filter-state="belum" title="Tampilkan yang belum diisi (klik lagi untuk batal)" class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-500"><span id="cntBelum">{{ $belum }}</span> Belum</button>
                        <button type="button" data-filter-state="sesuai" title="Tampilkan hasil yang sesuai (klik lagi untuk batal)" class="px-3 py-1.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-700"><span id="cntSesuai">{{ $sesuaiTampil ?? 0 }}</span> Sesuai</button>
                        <button type="button" data-filter-state="selisih" title="Tampilkan hasil yang memiliki selisih (klik lagi untuk batal)" class="px-3 py-1.5 rounded-xl bg-orange-50 border border-orange-200 text-orange-700"><span id="cntSelisih">{{ $selisihTampil ?? 0 }}</span> Selisih</button>
                        <span id="cntMenunggu" class="hidden">{{ $menunggu ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 ml-auto self-end lg:self-auto text-[10px] font-black uppercase tracking-wider">
                        <span class="flex items-center gap-1.5 text-blue-600"><span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px]">1</span> Pilih</span>
                        <span class="text-slate-300">→</span>
                        <span class="flex items-center gap-1.5 text-blue-600"><span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px]">2</span> Isi</span>
                        <span class="text-slate-300">→</span>
                        <span class="flex items-center gap-1.5 text-slate-400"><span class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-[10px]">3</span> Selesaikan</span>
                    </div>
                </div>
                <div class="mt-3 flex flex-col sm:flex-row sm:items-center gap-2 rounded-2xl bg-amber-50 border border-amber-200 px-3 py-2">
                    <p class="text-[11px] font-bold text-amber-800 flex-1">Simpan hasil hitung dulu. Saat <b>Selesaikan</b>, stok pada tanggal opname diset sesuai hasil hitung dan Berita Acara terbit.</p>
                    <form method="GET" action="{{ route('warehouse.stockopname.catat') }}" class="inline-flex items-center shrink-0">
                        @if(request()->filled('from'))
                            <input type="hidden" name="from" value="{{ request('from') }}">
                        @endif
                        <input type="date" lang="id-ID" name="tanggal" value="{{ $tanggal }}" max="{{ now()->format('Y-m-d') }}" class="ui-input !bg-white !w-auto !h-[32px] !py-1 !text-[11px]" onchange="this.form.submit()" title="Tanggal pelaksanaan opname" />
                    </form>
                    <div class="flex flex-wrap items-center gap-1.5 sm:ml-auto shrink-0">
                        {{-- BA tampil setelah periode final; rekap Excel/PDF selalu tersedia. --}}
                        @if($baAvailable ?? false)
                        <a id="baWrap" href="{{ route('warehouse.stockopname.print-bulan', ['bulan' => $bulan]) }}" data-posted="1" title="Unduh Berita Acara bulan {{ $namaBulan }}" class="h-[32px] px-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm whitespace-nowrap inline-flex items-center gap-1.5 shrink-0"><i class="fas fa-download text-[10px]"></i>Download BA</a>
                        @endif
                        <a href="{{ route('warehouse.stockopname.export-rekap', ['bulan' => $bulan, 'format' => 'excel']) }}" title="Download rekap opname {{ $namaBulan }} dalam format Excel" class="h-[32px] px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm whitespace-nowrap inline-flex items-center gap-1.5 shrink-0"><i class="fas fa-file-excel text-[10px]"></i>Excel</a>
                        <a href="{{ route('warehouse.stockopname.export-rekap', ['bulan' => $bulan, 'format' => 'pdf']) }}" title="Download rekap opname {{ $namaBulan }} dalam format PDF" class="h-[32px] px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm whitespace-nowrap inline-flex items-center gap-1.5 shrink-0"><i class="fas fa-file-pdf text-[10px]"></i>PDF</a>
                        <span id="finishWrap" data-finished="{{ ($baAvailable ?? false) ? '1' : '0' }}" class="{{ ($baAvailable ?? false) ? 'hidden' : '' }}">
                            <button type="button" id="finishBtn" onclick="openFinishModal()" @disabled(($menunggu ?? 0) <= 0) title="{{ ($menunggu ?? 0) > 0 ? 'Selesaikan opname bulan ini' : 'Simpan minimal satu hasil hitung dulu sebelum menyelesaikan opname.' }}" class="h-[32px] px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-emerald-600">Selesaikan <span id="finishN">({{ $menunggu ?? 0 }})</span></button>
                        </span>
                    </div>
                </div>
            </x-ui.glass-card>

            {{-- Notifikasi dimunculkan di dalam card putih, sama seperti blok lain. --}}
            @if (session('success') || session('error') || $errors->any())
            <x-ui.glass-card class="!rounded-3xl !bg-white shadow-sm" padding="sm">
                @if (session('success'))
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-xs font-bold">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-xs font-bold {{ session('success') ? 'mt-2' : '' }}">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-xs font-bold {{ (session('success') || session('error')) ? 'mt-2' : '' }}">{{ $errors->first() }}</div>
                @endif
            </x-ui.glass-card>
            @endif

            <div id="catatGrid">
                {{-- Kiri: daftar produk --}}
                <x-ui.glass-card id="catatGridLeft" padding="none" class="!p-4 sm:!p-5 !rounded-3xl !bg-white border border-slate-200 shadow-sm flex flex-col min-h-0">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-[11px] font-black flex items-center justify-center shrink-0">1</span>
                        <div class="text-[13px] font-black text-slate-800">Pilih barang</div>
                        <span class="ml-auto text-[10px] font-bold text-slate-400">Abu = belum · Biru = sesuai · Oranye = selisih</span>
                    </div>
                    <x-ui.search-input id="catatSearch" placeholder="Cari kode / nama produk..." autocomplete="off" />
                    <div id="catatList" class="space-y-1 overflow-y-auto flex-1 min-h-[400px] max-h-[80vh]">
                        @forelse($productOptions as $o)
                            @php
                                $saved = $savedMap[$o->id] ?? null;
                                $st = $saved ? (abs((float) $saved['selisih']) < 0.005 ? 'sesuai' : 'selisih') : 'belum';
                                $filled = $st !== 'belum';
                                $pending = $saved ? !empty($saved['pending']) : false;
                            @endphp
                            <button type="button" data-catat-item data-id="{{ $o->id }}" data-stock="{{ $o->stock }}" data-unit="{{ $o->unit ?? 'Pcs' }}" data-nama="{{ $o->product_code }} — {{ $o->product_name }}" data-find="{{ strtolower($o->product_code . ' ' . $o->product_name) }}" data-opnamed="{{ $filled ? '1' : '0' }}" data-pending="{{ $pending ? '1' : '0' }}" data-state="{{ $st }}"
                                class="w-full min-w-0 text-left rounded-xl px-3 py-1.5 transition-all border {{ $st === 'sesuai' ? 'bg-sky-50 border-sky-300 ring-1 ring-sky-200' : ($st === 'selisih' ? 'bg-orange-50 border-orange-300' : 'bg-slate-50 border-slate-200 hover:bg-indigo-50 hover:border-indigo-300') }}">
                                <div class="flex items-center gap-2">
                                    <div class="text-[12px] font-bold leading-snug truncate flex-1 {{ $st === 'sesuai' ? 'text-sky-900' : ($st === 'selisih' ? 'text-orange-800' : 'text-slate-800') }}" title="{{ $o->product_code }} — {{ $o->product_name }}">{{ $o->product_code }} — {{ $o->product_name }}</div>
                                    <span data-badge class="shrink-0 text-[9px] font-black px-2 py-0.5 rounded-full {{ $st === 'sesuai' ? 'bg-sky-500 text-white' : ($st === 'selisih' ? 'bg-orange-500 text-white' : 'bg-slate-200 text-slate-500') }}">{{ $st === 'sesuai' ? 'SESUAI' : ($st === 'selisih' ? 'SELISIH' : 'BELUM') }}</span>
                                </div>
                                <div class="text-[10px] mt-0.5 {{ $st === 'sesuai' ? 'text-sky-600' : ($st === 'selisih' ? 'text-orange-600' : 'text-slate-400') }} font-bold" data-sys>Sistem: {{ $o->stock }} {{ $o->unit ?? 'Pcs' }}</div>
                            </button>
                        @empty
                            <div class="text-center py-8 text-slate-400 text-xs font-medium">Tidak ada produk.</div>
                        @endforelse
                        <div id="catatEmptyFilter" class="hidden text-center py-8 text-slate-400 text-xs font-medium">Tidak ada barang yang cocok dengan saringan. Klik pill status sekali lagi untuk menampilkan semua.</div>
                    </div>
                </x-ui.glass-card>

                {{-- Kanan: form isi fisik --}}
                <x-ui.glass-card id="catatGridRight" padding="none" class="!p-4 sm:!p-5 !rounded-3xl !bg-white border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white text-[11px] font-black flex items-center justify-center shrink-0">2</span>
                        <div class="text-[13px] font-black text-slate-800">Isi fisik <span class="font-medium text-slate-400">— klik Simpan</span></div>
                        <span class="ml-auto flex items-center gap-1.5">
                            <span id="catatModeBadge" class="text-[9px] font-black px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">MODE EDIT</span>
                            <button type="button" id="catatModeBtn" title="Beralih mode lihat / edit" class="h-[28px] px-3 rounded-lg bg-white border border-slate-300 hover:border-indigo-300 text-slate-600 hover:text-indigo-700 text-[11px] font-bold shadow-sm">Lihat</button>
                        </span>
                    </div>
                    <form method="POST" action="{{ route('warehouse.stockopname.store') }}" id="catatForm" class="space-y-3">
                        @csrf
                        <div class="rounded-xl bg-indigo-50 border border-indigo-200 px-3 py-2.5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">Barang terpilih</div>
                            <div id="catatNama" class="text-[13px] font-black text-indigo-900 leading-snug break-words">— pilih dari daftar —</div>
                        </div>
                        <div id="catatSavedInfo" class="hidden rounded-xl bg-orange-50 border border-orange-200 px-3 py-2 text-[11px] font-bold text-orange-700"></div>
                        <input type="hidden" name="product_id" id="catatProductId" value="">
                        <input type="hidden" name="expected_qty" id="catatExpected" value="">
                        <input type="hidden" name="reason" id="catatReason" value="">
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <div class="min-w-0"><label class="ui-label">Fisik <span class="text-red-500">*</span></label><input type="text" name="counted_qty" id="catatCounted" inputmode="decimal" autocomplete="off" spellcheck="false" enterkeyhint="done" maxlength="20" required placeholder="0" value="{{ old('counted_qty') }}" class="ui-input !bg-white text-center font-black !leading-normal !h-[48px] !text-base"></div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="min-w-0"><label class="ui-label">Tercatat</label><div id="catatRecorded" class="ui-input !bg-slate-50 font-black text-slate-700 flex items-center justify-center">-</div></div>
                            <div class="min-w-0"><label class="ui-label">Selisih</label><div id="catatDiff" class="ui-input !bg-slate-50 font-black text-slate-700 flex items-center justify-center">-</div></div>
                        </div>
                        <div id="catatStatus" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-black text-slate-500 text-center tracking-wide">PILIH BARANG & ISI FISIK</div>
                        <div class="hidden rounded-xl bg-amber-50 border border-amber-200 px-3 py-2 text-[11px] font-bold text-amber-700" id="catatTolWarn"></div>
                        <div id="catatNoteWrap" class="hidden"><label class="ui-label">Keterangan <span class="text-slate-400 font-medium">(wajib bila selisih melebihi toleransi)</span></label>
                            <textarea id="catatNote" rows="2" maxlength="1000" placeholder="Mis. barang jadi sample tapi gak kecatet..." style="overflow:hidden" class="ui-input !bg-white resize-none !h-auto min-h-[68px] !leading-relaxed py-2"></textarea></div>
                        <label class="hidden items-center gap-2 text-[11px] font-bold text-slate-600" id="catatRecountRow">
                            <input type="checkbox" name="recount" value="1" class="accent-blue-600"> Sudah hitung ulang oleh orang kedua
                        </label>
                        <div class="hidden rounded-xl px-3 py-2 text-[11px] font-bold" id="catatAjaxMsg"></div>
                        <button type="submit" id="catatSaveBtn" class="w-full h-[42px] rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">Simpan</button>
                        <p class="text-[10px] text-slate-400 font-medium leading-snug">Tanggal opname menentukan bulan & titik reset stok. BA baru terbit setelah <b>Selesaikan</b>.</p>
                    </form>
                </x-ui.glass-card>
            </div>
        </div>
    </div>

    {{-- Modal Selesaikan: kiri rekap selisih, kanan form Berita Acara --}}
    <x-ui.modal id="modal-selesai" title="Selesaikan Opname" subtitle="{{ ($hasExistingBa ?? false) ? 'Kiri: rekap selisih · Kanan: konfirmasi BA. Nama BA memakai data terakhir.' : 'Kiri: rekap selisih · Kanan: isi BA lalu posting = stok resmi berubah.' }}" icon="fa-stamp" iconClass="bg-emerald-600 text-white" maxWidth="max-w-4xl">
        <div class="grid gap-4 md:grid-cols-5">
            <div class="md:col-span-3 min-w-0">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-500 text-white text-[11px] font-black flex items-center justify-center shrink-0">1</span>
                    <div class="text-[12px] font-black text-slate-800 leading-tight">Rekap selisih</div>
                </div>
                <p id="selesaiSummary" class="text-[11px] text-slate-500 font-medium mb-2">Memuat data...</p>
                <div id="selesaiList" class="space-y-2 max-h-[38vh] md:max-h-[52vh] overflow-y-auto pr-0.5"></div>
            </div>
            <div class="md:col-span-2 min-w-0">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white text-[11px] font-black flex items-center justify-center shrink-0">2</span>
                    <div class="text-[12px] font-black text-slate-800 leading-tight">Berita Acara</div>
                </div>
                <form id="finishForm" method="POST" action="{{ route('warehouse.stockopname.finish') }}" class="space-y-2.5 rounded-2xl bg-slate-50 border border-slate-200 p-3.5" onsubmit="confirmSubmit(event, 'Selesaikan opname {{ $namaBulan }}? Stok resmi berubah + BA {{ ($hasExistingBa ?? false) ? 'diperbarui' : 'tersedia' }}.');">
                    @csrf
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <div class="text-[12px] font-black text-slate-800">Berita Acara Stock Opname</div>
                    @if($hasExistingBa ?? false)
                    <div class="rounded-xl bg-white border border-emerald-200 px-3 py-2.5">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Nama BA terakhir</div>
                        <div class="mt-1.5 grid gap-1.5">
                            <div><div class="text-[9px] font-bold uppercase text-slate-400">Pembuat BA</div><div class="text-[11px] font-black text-slate-700">{{ $baCreator }}</div></div>
                            <div><div class="text-[9px] font-bold uppercase text-slate-400">Pemeriksa</div><div class="text-[11px] font-black text-slate-700">{{ $baChecker }}</div></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500 font-medium leading-snug">Nama tidak ditanyakan lagi. Selesaikan untuk memperbarui BA dengan nama terakhir.</p>
                    @else
                    <div class="grid gap-2.5">
                        <div><label class="ui-label">Nama Pembuat BA <span class="text-red-500">*</span></label><input type="text" name="pembuat_ba" required maxlength="100" value="{{ old('pembuat_ba', Auth::user()->name ?? '') }}" placeholder="Nama pembuat..." class="ui-input !bg-white"></div>
                        <div><label class="ui-label">Nama Pemeriksa <span class="text-red-500">*</span></label><input type="text" name="pemeriksa_ba" required maxlength="100" value="{{ old('pemeriksa_ba') }}" placeholder="Nama pemeriksa..." class="ui-input !bg-white"></div>
                    </div>
                    @endif
                    <button type="submit" class="w-full h-[42px] rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-sm">Selesaikan & {{ ($hasExistingBa ?? false) ? 'Update BA' : 'Terbitkan BA' }}</button>
                </form>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" data-modal-close class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50">Tutup</button>
        </x-slot:footer>
    </x-ui.modal>
    <script id="draft-list-url" type="application/json">@json(route('warehouse.stockopname.drafts'))</script>

    <script id="catat-saved-data" type="application/json">@json($savedMap ?? [])</script>
    <script id="catat-back-urls" type="application/json">@json(['rekap' => route('warehouse.stockopname.index', ['bulan' => $bulan ?? now()->format('Y-m')]), 'dashboard' => route('warehouse.dashboard')])</script>
    <script>
        // Back button selaras konteks: ?from=dashboard diutamakan, lalu referrer, terakhir default server.
        (function fixCatatBack() {
            try {
                var btn = document.getElementById('catat-back-btn');
                if (!btn) return;
                var urls = JSON.parse(document.getElementById('catat-back-urls').textContent || '{}');
                var params = new URLSearchParams(window.location.search);
                var from = (params.get('from') || '').toLowerCase();
                var setBack = function (href, label) {
                    btn.setAttribute('href', href);
                    var spans = btn.querySelectorAll('span');
                    var labelEl = spans[spans.length - 1];
                    if (labelEl) labelEl.textContent = label;
                };
                if (from === 'dashboard') { setBack(urls.dashboard, 'Back to Warehouse Dashboard'); return; }
                if (from === 'rekap') { setBack(urls.rekap, 'Back to Opname Recap'); return; }
                var ref = document.referrer || '';
                if (ref.indexOf('/gudang/dashboard') !== -1) { setBack(urls.dashboard, 'Back to Warehouse Dashboard'); }
                else if (ref.indexOf('/opname') !== -1 && ref.indexOf('/opname/catat') === -1) { setBack(urls.rekap, 'Back to Opname Recap'); }
            } catch (e) {}
        })();
        (function () {
            var TOL_ABS = {{ \App\Http\Controllers\Warehouse\WsStockOpnameController::TOL_ABS }};
            var TOL_PCT = {{ \App\Http\Controllers\Warehouse\WsStockOpnameController::TOL_PCT }};
            var counted = document.getElementById('catatCounted');
            if (!counted) return;
            var pid = document.getElementById('catatProductId');
            var expected = document.getElementById('catatExpected');
            var recordedEl = document.getElementById('catatRecorded');
            var diffEl = document.getElementById('catatDiff');
            var statusEl = document.getElementById('catatStatus');
            var warnEl = document.getElementById('catatTolWarn');
            var recountRow = document.getElementById('catatRecountRow');
            var noteWrap = document.getElementById('catatNoteWrap');
            var note = document.getElementById('catatNote');
            var reason = document.getElementById('catatReason');
            var namaEl = document.getElementById('catatNama');
            var savedInfo = document.getElementById('catatSavedInfo');
            var modeBadge = document.getElementById('catatModeBadge');
            var modeBtn = document.getElementById('catatModeBtn');
            var form = document.getElementById('catatForm');
            var ajaxMsg = document.getElementById('catatAjaxMsg');
            var saveBtn = document.getElementById('catatSaveBtn');
            var selStock = null, selBtn = null;
            // Mode form: lihat (tampilkan data simpanan, kunci input) vs edit (bisa simpan).
            var editMode = true;
            function setMode(m) {
                editMode = (m === 'edit');
                if (counted) counted.disabled = !editMode;
                if (note) note.disabled = !editMode;
                var rc = form ? form.querySelector('input[name="recount"]') : null;
                if (rc) rc.disabled = !editMode;
                if (modeBadge) {
                    modeBadge.textContent = editMode ? 'MODE EDIT' : 'MODE LIHAT';
                    modeBadge.className = 'text-[9px] font-black px-2 py-0.5 rounded-full ' + (editMode ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-500');
                }
                if (modeBtn) modeBtn.textContent = editMode ? 'Lihat' : 'Edit';
                if (saveBtn) {
                    saveBtn.disabled = !editMode;
                    var hasSaved = !!(savedMap && pid.value && savedMap[pid.value]);
                    saveBtn.textContent = editMode ? (hasSaved ? 'Simpan Edit' : 'Simpan') : 'Simpan';
                }
            }
            if (modeBtn) modeBtn.addEventListener('click', function () { setMode(editMode ? 'view' : 'edit'); });
            // Data tersimpan per barang (entri terakhir bulan ini): dikirim server.
            var savedMap = {};
            try { savedMap = JSON.parse(document.getElementById('catat-saved-data').textContent || '{}') || {}; } catch (e) { savedMap = {}; }
            function hideSavedInfo() { if (savedInfo) savedInfo.classList.add('hidden'); }
            function showSavedInfo(pid) {
                if (!savedInfo) return;
                var sv = savedMap[pid];
                if (!sv) { hideSavedInfo(); return; }
                var sel = (sv.selisih !== null && sv.selisih !== undefined) ? (sv.selisih > 0 ? '+' + sv.selisih : '' + sv.selisih) : '-';
                var isSesuai = sv.selisih !== null && sv.selisih !== undefined && Math.abs(sv.selisih) < 0.005;
                savedInfo.className = 'rounded-xl border px-3 py-2 text-[11px] font-bold ' + (isSesuai
                    ? 'bg-sky-50 border-sky-200 text-sky-700'
                    : 'bg-orange-50 border-orange-200 text-orange-700');
                savedInfo.textContent = (isSesuai ? 'Tersimpan & sesuai: ' : 'Tersimpan dengan selisih: ')
                    + 'fisik ' + (sv.terhitung ?? '-') + ' • selisih ' + sel + ' • ' + (sv.waktu || '-') + ' • oleh ' + (sv.oleh || '-')
                    + (sv.pembuat ? ' • BA: ' + sv.pembuat + ' / ' + (sv.pemeriksa || '-') : '');
                savedInfo.classList.remove('hidden');
            }

            function tolerance(rec) { return Math.round(Math.max(TOL_ABS, Math.abs(rec) * TOL_PCT) * 100) / 100; }
            // Keterangan tampil full tanpa scroll: tinggi mengikuti isi otomatis.
            function growNote() {
                if (!note) return;
                note.style.overflowY = 'hidden';
                note.style.height = 'auto';
                note.style.height = note.scrollHeight + 'px';
            }
            function setStatus(mode, text) {
                var base = 'rounded-xl border px-3 py-2 text-[11px] font-black text-center tracking-wide ';
                if (mode === 'ok') statusEl.className = base + 'border-emerald-200 bg-emerald-50 text-emerald-700';
                else if (mode === 'diff') statusEl.className = base + 'border-orange-200 bg-orange-50 text-orange-700';
                else statusEl.className = base + 'border-slate-200 bg-slate-50 text-slate-500';
                statusEl.textContent = text;
            }
            // Basis selisih opname: qty tercatat saat pertama dihitung (jika ada),
            // bukan stok live yang mungkin sudah bergerak setelah tanggal opname.
            function recordedBase() {
                var sv = pid.value ? savedMap[pid.value] : null;
                if (sv && sv.tercatat !== null && sv.tercatat !== undefined && !isNaN(parseFloat(sv.tercatat))) {
                    return parseFloat(sv.tercatat);
                }
                return selStock === null ? null : parseFloat(selStock);
            }
            function refresh() {
                if (selStock === null) {
                    recordedEl.textContent = '-'; expected.value = ''; diffEl.textContent = '-';
                    setStatus('', 'PILIH BARANG & ISI FISIK');
                    warnEl.classList.add('hidden'); recountRow.classList.add('hidden'); recountRow.classList.remove('flex');
                    if (noteWrap) noteWrap.classList.add('hidden');
                    return;
                }
                expected.value = selStock;
                var rec = recordedBase();
                recordedEl.textContent = rec === null ? selStock : rec;
                var c = counted.value === '' || counted.value === '-' ? null : parseFloat(counted.value);
                if (c === null || isNaN(c)) {
                    diffEl.textContent = '-'; setStatus('', 'ISI JUMLAH FISIK');
                    warnEl.classList.add('hidden'); recountRow.classList.add('hidden'); recountRow.classList.remove('flex');
                    if (noteWrap) noteWrap.classList.add('hidden');
                    return;
                }
                var diff = Math.round((c - (rec === null ? selStock : rec)) * 100) / 100;
                var tol = tolerance(rec === null ? selStock : rec);
                diffEl.textContent = (diff > 0 ? '+' : '') + diff;
                if (diff === 0) {
                    setStatus('ok', 'SESUAI — FISIK SAMA DENGAN SISTEM');
                    // Tanpa selisih: keterangan otomatis "-" dan form disembunyikan.
                    if (note) note.value = '-';
                    if (noteWrap) noteWrap.classList.add('hidden');
                } else {
                    setStatus('diff', 'SELISIH ' + (diff > 0 ? '+' : '') + diff + ' — WAJIB ISI KETERANGAN');
                    // Ada selisih: tampilkan form keterangan; bersihkan nilai otomatis "-" bila ada.
                    if (noteWrap) { noteWrap.classList.remove('hidden'); growNote(); }
                    if (note && note.value.trim() === '-') { note.value = ''; growNote(); }
                }
                if (Math.abs(diff) > tol) {
                    warnEl.textContent = 'Selisih ' + diff + ' melebihi toleransi wajar (±' + tol + '). Wajib isi keterangan + centang sudah hitung ulang.';
                    warnEl.classList.remove('hidden'); recountRow.classList.remove('hidden'); recountRow.classList.add('flex');
                } else { warnEl.classList.add('hidden'); recountRow.classList.add('hidden'); recountRow.classList.remove('flex'); }
            }
            function bumpCounter(id, delta, min) {
                var el = document.getElementById(id);
                if (!el) return;
                el.textContent = Math.max(min === undefined ? 0 : min, parseInt(el.textContent || '0', 10) + delta);
            }
            function syncFinishButton() {
                var m = document.getElementById('cntMenunggu');
                var n = m ? parseInt(m.textContent || '0', 10) : 0;
                var fn = document.getElementById('finishN');
                if (fn) fn.textContent = '(' + n + ')';
                // Sebelum periode final: Selesaikan selalu terlihat, aktif setelah ada draf.
                // Setelah final: Selesaikan hilang dan Download BA yang tampil.
                var bw = document.getElementById('baWrap');
                var posted = bw ? bw.getAttribute('data-posted') === '1' : false;
                var finished = posted && n <= 0;
                var ff = document.getElementById('finishWrap');
                if (ff) ff.classList.toggle('hidden', finished);
                var fb = document.getElementById('finishBtn');
                if (fb) {
                    fb.disabled = n <= 0;
                    fb.title = n > 0
                        ? 'Selesaikan opname bulan ini'
                        : 'Simpan minimal satu hasil hitung dulu sebelum menyelesaikan opname.';
                }
                if (bw) bw.classList.toggle('hidden', !finished);
            }
            function markSaved(btn, diff) {
                if (!btn) return;
                var prev = btn.getAttribute('data-state');
                var wasPending = btn.getAttribute('data-pending') === '1';
                var isSesuai = Math.abs(Number(diff) || 0) < 0.005;
                var next = isSesuai ? 'sesuai' : 'selisih';
                btn.setAttribute('data-opnamed', '1');
                btn.setAttribute('data-pending', '1');
                btn.setAttribute('data-state', next);
                btn.className = 'w-full min-w-0 text-left rounded-xl px-3 py-1.5 transition-all border '
                    + (isSesuai
                        ? 'bg-sky-50 border-sky-300 ring-1 ring-sky-200'
                        : 'bg-orange-50 border-orange-300');
                var badge = btn.querySelector('[data-badge]');
                if (badge) {
                    badge.className = 'shrink-0 text-[9px] font-black px-2 py-0.5 rounded-full '
                        + (isSesuai ? 'bg-sky-500 text-white' : 'bg-orange-500 text-white');
                    badge.textContent = isSesuai ? 'SESUAI' : 'SELISIH';
                }
                var sys = btn.querySelector('[data-sys]');
                if (sys) sys.className = (isSesuai ? 'text-sky-600' : 'text-orange-600') + ' font-bold text-[10px] mt-0.5';
                if (prev === 'belum') bumpCounter('cntBelum', -1);
                else if (prev === 'sesuai') bumpCounter('cntSesuai', -1);
                else if (prev === 'selisih') bumpCounter('cntSelisih', -1);
                bumpCounter(isSesuai ? 'cntSesuai' : 'cntSelisih', 1);
                if (!wasPending) bumpCounter('cntMenunggu', 1);
                syncFinishButton();
            }
            function showAjaxMsg(mode, text) {
                if (!ajaxMsg) return;
                ajaxMsg.classList.remove('hidden');
                ajaxMsg.className = 'rounded-xl border px-3 py-2 text-[11px] font-bold ' + (mode === 'ok'
                    ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
                    : 'bg-rose-50 border-rose-200 text-rose-600');
                ajaxMsg.textContent = text;
            }
            function hideAjaxMsg() { if (ajaxMsg) ajaxMsg.classList.add('hidden'); }
            function visibleItems() {
                return Array.prototype.filter.call(document.querySelectorAll('[data-catat-item]'), function (b) { return !b.classList.contains('hidden'); });
            }
            function selectBtn(btn) {
                document.querySelectorAll('[data-catat-item]').forEach(function (b) { b.classList.remove('ring-2', 'ring-indigo-500'); });
                if (!btn) {
                    selBtn = null; selStock = null; pid.value = '';
                    namaEl.textContent = '— pilih dari daftar —'; counted.value = ''; hideSavedInfo(); setMode('edit'); refresh(); return;
                }
                btn.classList.add('ring-2', 'ring-indigo-500');
                selBtn = btn; selStock = parseFloat(btn.getAttribute('data-stock')) || 0;
                var curPid = btn.getAttribute('data-id') || '';
                pid.value = curPid;
                namaEl.textContent = btn.getAttribute('data-nama') || '-';
                counted.value = ''; hideAjaxMsg(); hideSavedInfo();
                // State baru per barang (keterangan & centang tidak terbawa).
                if (note) note.value = '';
                var rc0 = form ? form.querySelector('input[name="recount"]') : null;
                if (rc0) rc0.checked = false;
                // Kartu terisi (sesuai/selisih/sudah): tampilkan data simpanan dalam MODE LIHAT.
                // Kartu belum: langsung MODE EDIT untuk pendataan.
                var sv = savedMap[curPid];
                if (sv && sv.terhitung !== null && sv.terhitung !== undefined) {
                    counted.value = sv.terhitung;
                    if (note) note.value = (sv.catatan && sv.catatan !== '-') ? sv.catatan : '';
                    growNote();
                    showSavedInfo(curPid);
                    setMode('view');
                } else {
                    setMode('edit');
                }
                refresh();
                setTimeout(function () { counted.focus(); }, 100);
            }
            document.querySelectorAll('[data-catat-item]').forEach(function (btn) {
                btn.addEventListener('click', function () { selectBtn(btn); });
            });
            var s = document.getElementById('catatSearch');
            // Saringan gabungan: kata kunci + pill status (klik pill = saring, klik lagi = lepas).
            var stateFilter = null;
            function applyFilters() {
                var kw = '';
                if (s) kw = (s.value || '').toLowerCase();
                var shown = 0;
                document.querySelectorAll('[data-catat-item]').forEach(function (btn) {
                    var t = (btn.getAttribute('data-find') || '').toLowerCase();
                    var okKw = kw === '' || t.indexOf(kw) !== -1;
                    var okSt = !stateFilter || btn.getAttribute('data-state') === stateFilter;
                    var show = okKw && okSt;
                    btn.classList.toggle('hidden', !show);
                    if (show) shown++;
                });
                document.querySelectorAll('[data-filter-state]').forEach(function (p) {
                    var on = !!stateFilter && p.getAttribute('data-filter-state') === stateFilter;
                    p.classList.toggle('ring-2', on);
                    p.classList.toggle('ring-indigo-500', on);
                });
                var ef = document.getElementById('catatEmptyFilter');
                if (ef) ef.classList.toggle('hidden', shown > 0);
            }
            document.querySelectorAll('[data-filter-state]').forEach(function (p) {
                p.addEventListener('click', function () {
                    var v = p.getAttribute('data-filter-state');
                    stateFilter = (stateFilter === v) ? null : v;
                    applyFilters();
                });
            });
            // Deep-link "Catat Sisa" (?hanya_belum=1): langsung saring yang belum.
            try {
                if (new URLSearchParams(window.location.search).get('hanya_belum') === '1') {
                    stateFilter = 'belum';
                    applyFilters();
                }
            } catch (e) {}
            if (s) s.addEventListener('input', function () { applyFilters(); });
            var totalReset = document.getElementById('totalReset');
            if (totalReset) totalReset.addEventListener('click', function () {
                stateFilter = null;
                if (s) s.value = '';
                applyFilters();
                if (s) { try { s.focus({ preventScroll: true }); } catch (e) { try { s.focus(); } catch (e2) {} } }
            });
            function sanitizeCounted() {
                var raw = counted.value || '';
                var negative = raw.trim().charAt(0) === '-';
                var clean = raw.replace(/,/g, '.').replace(/[^0-9.]/g, '');
                var dot = clean.indexOf('.');
                if (dot !== -1) {
                    clean = clean.slice(0, dot + 1) + clean.slice(dot + 1).replace(/\./g, '');
                }
                if (negative) clean = '-' + clean;
                if (clean !== raw) counted.value = clean;
            }
            counted.addEventListener('input', function () { sanitizeCounted(); hideAjaxMsg(); refresh(); });
            var saveBusy = false;
            function saveNow() {
                if (!form || saveBusy) return;
                hideAjaxMsg();
                if (!editMode) {
                    showAjaxMsg('err', 'Klik Edit dulu bila ingin mengubah hasil yang sudah tersimpan.');
                    return;
                }
                if (!pid.value) {
                    showAjaxMsg('err', 'Pilih barang terlebih dahulu.');
                    return;
                }
                var recBase = recordedBase();
                var rec = recBase === null ? 0 : recBase;
                var c = parseFloat(counted.value);
                if (isNaN(c) || counted.value === '' || counted.value === '-') {
                    showAjaxMsg('err', 'Isi jumlah fisik terlebih dahulu.');
                    return;
                }
                reason.value = (note.value || '').trim();
                var diffNow = Math.round((c - rec) * 100) / 100;
                if (Math.abs(diffNow) >= 0.005 && (reason.value === '' || reason.value === '-')) {
                    showAjaxMsg('err', 'Ada selisih: keterangan penyebab wajib diisi sebelum menyimpan.');
                    return;
                }
                if (Math.abs(diffNow) > tolerance(rec)) {
                    var rc = form.querySelector('input[name="recount"]');
                    if (!(rc && rc.checked)) {
                        showAjaxMsg('err', 'Selisih melebihi toleransi: centang konfirmasi hitung ulang dulu.');
                        return;
                    }
                }

                saveBusy = true;
                if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Menyimpan…'; }
                setStatus('', 'MENYIMPAN…');
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: new FormData(form)
                })
                    .then(function (res) {
                        if (res.redirected) return { status: 419, data: { ok: false, message: 'Sesi habis. Muat ulang halaman lalu coba lagi.' } };
                        return res.json().then(function (data) { return { status: res.status, data: data }; }).catch(function () { return { status: res.status, data: { ok: false, message: 'Respons server tidak valid.' } }; });
                    })
                    .then(function (out) {
                        var data = out.data || {};
                        if (out.status === 422 || out.status === 419 || data.ok === false) {
                            if (data.stock !== undefined && data.stock !== null && selBtn) {
                                var ns = parseFloat(data.stock) || 0;
                                selStock = ns; expected.value = ns; recordedEl.textContent = ns;
                                selBtn.setAttribute('data-stock', ns);
                                var se = selBtn.querySelector('[data-sys]');
                                if (se) se.textContent = 'Sistem: ' + ns + ' ' + (selBtn.getAttribute('data-unit') || 'Pcs');
                                refresh();
                            }
                            showAjaxMsg('err', data.message || 'Gagal menyimpan.');
                            return;
                        }

                        var savedStock = (data.stock !== undefined && data.stock !== null) ? parseFloat(data.stock) || 0 : (parseFloat(counted.value) || 0);
                        var savedPid = pid.value;
                        var savedDiff = Math.round(((parseFloat(data.counted) || c) - (parseFloat(data.recorded) || (selStock === null ? 0 : selStock))) * 100) / 100;
                        if (selBtn) {
                            selBtn.setAttribute('data-stock', savedStock);
                            var se2 = selBtn.querySelector('[data-sys]');
                            if (se2) se2.textContent = 'Sistem: ' + savedStock + ' ' + (selBtn.getAttribute('data-unit') || 'Pcs');
                            markSaved(selBtn, savedDiff);
                            applyFilters();
                        }
                        var savedMsg = data.message || 'Tersimpan.';

                        if (savedPid) {
                            savedMap[savedPid] = {
                                terhitung: parseFloat(data.counted) || c,
                                tercatat: parseFloat(data.recorded) || (selStock === null ? 0 : selStock),
                                selisih: savedDiff,
                                catatan: reason.value || '-',
                                waktu: 'baru saja',
                                dok: '-',
                                oleh: '-',
                                pending: true,
                                status: 'draft'
                            };
                        }

                        note.value = '';
                        growNote();
                        var rc2 = form.querySelector('input[name="recount"]'); if (rc2) rc2.checked = false;

                        // Pindah otomatis ke produk berikutnya yang belum disimpan.
                        var items = visibleItems(), next = null;
                        if (selBtn) { var idx = items.indexOf(selBtn); next = items[idx + 1] || null; }
                        while (next && next.getAttribute('data-opnamed') === '1') { var ni = items.indexOf(next); next = items[ni + 1] || null; }
                        if (next) selectBtn(next); else { selectBtn(null); if (s) s.focus(); }
                        showAjaxMsg('ok', savedMsg);
                    })
                    .catch(function () { showAjaxMsg('err', 'Jaringan bermasalah, coba lagi.'); })
                    .finally(function () {
                        saveBusy = false;
                        if (saveBtn) {
                            saveBtn.disabled = !editMode;
                            saveBtn.textContent = editMode ? ((pid.value && savedMap[pid.value]) ? 'Simpan Edit' : 'Simpan') : 'Simpan';
                        }
                    });
            }
            if (note) note.addEventListener('input', function () { growNote(); });
            var rcBox = form ? form.querySelector('input[name="recount"]') : null;
            if (rcBox) rcBox.addEventListener('change', function () { refresh(); });
            if (form) form.addEventListener('submit', function (e) { e.preventDefault(); saveNow(); });
            // Kunci tinggi kartu KIRI ke sisa viewport (daftar produk scroll di dalam).
            // Kartu KANAN (isi fisik) dibiarkan memanjang full tanpa scroll dalam.
            function fitCatatGrid() {
                var left = document.getElementById('catatGridLeft');
                var right = document.getElementById('catatGridRight');
                var grid = document.getElementById('catatGrid');
                if (!left || !right || !grid) return;
                right.style.maxHeight = '';
                if (window.innerWidth < 900) {
                    left.style.height = '';
                    left.style.maxHeight = '';
                    return;
                }
                var top = grid.getBoundingClientRect().top;
                if (top < 0) top = 0;
                var avail = Math.max(520, Math.floor(window.innerHeight - top - 16));
                left.style.height = avail + 'px';
                left.style.maxHeight = avail + 'px';
                // Kartu kanan (isi fisik) TIDAK dikunci: biarkan memanjang mengikuti
                // isi (keterangan auto-grow, info simpanan, pesan Ajax) + sticky.
            }
            window.addEventListener('resize', fitCatatGrid);
            window.addEventListener('load', function () { setTimeout(fitCatatGrid, 60); });
            fitCatatGrid();
            growNote();
            sanitizeCounted();
            refresh();
        })();
    </script>
    <script>
        // Modal Selesaikan: ambil rekap draf terbaru lalu tampilkan + form BA.
        function escHtml(v) {
            return String(v === null || v === undefined ? '' : v).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
        window.openFinishModal = function () {
            var summary = document.getElementById('selesaiSummary');
            var list = document.getElementById('selesaiList');
            var finishForm = document.getElementById('finishForm');
            var bulan = 'bulan';
            try {
                var bInput = finishForm ? finishForm.querySelector('input[name="bulan"]') : null;
                if (bInput && bInput.value) bulan = bInput.value;
            } catch (e) {}
            var base = 'draf';
            try { base = JSON.parse(document.getElementById('draft-list-url').textContent || '"draf"'); } catch (e) {}
            if (summary) summary.textContent = 'Memuat data...';
            if (list) list.innerHTML = '';
            fetch(base + '?bulan=' + encodeURIComponent(bulan), { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    var items = (data && data.items) || [];
                    var totalDrafts = (data && data.total_drafts !== null && data.total_drafts !== undefined)
                        ? (parseInt(data.total_drafts, 10) || 0) : items.length;
                    var sameCount = (data && data.same_count !== null && data.same_count !== undefined)
                        ? (parseInt(data.same_count, 10) || 0) : Math.max(0, totalDrafts - items.length);
                    var moved = items.filter(function (it) { return !!it.moved_after; }).length;
                    if (summary) summary.textContent = (data.namaBulan || bulan) + ' · ' + items.length + ' selisih menunggu Selesaikan'
                        + (sameCount > 0 ? ' · ' + sameCount + ' sesuai tidak ditampilkan' : '')
                        + (moved > 0 ? ' · ' + moved + ' produk punya mutasi setelah dihitung (delta dihitung ulang otomatis).' : '.');
                    if (!items.length) {
                        if (list) list.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm font-medium">'
                            + (totalDrafts > 0
                                ? 'Tidak ada selisih. ' + totalDrafts + ' hasil sesuai tetap akan diselesaikan saat klik Selesaikan.'
                                : 'Belum ada hasil opname pada periode ini.')
                            + '</div>';
                    } else if (list) {
                        list.innerHTML = items.map(function (it) {
                            var badge = it.moved_after
                                ? '<span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-sky-100 text-sky-700">ADA MUTASI</span>'
                                : '<span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">SELISIH ' + (it.selisih > 0 ? '+' : '') + it.selisih + '</span>';
                            return '<div class="bg-white border ' + (it.moved_after ? 'border-sky-300' : 'border-orange-200') + ' rounded-xl p-2">'
                                + '<div class="flex items-center justify-between gap-2"><span class="text-[11px] font-bold text-slate-800 truncate">' + escHtml(it.produk) + '</span>' + badge + '</div>'
                                + '<div class="grid grid-cols-3 gap-1 mt-1.5 text-center">'
                                + '<div class="bg-slate-50 rounded-lg px-1 py-1"><div class="text-[9px] font-bold text-slate-400 uppercase">Sistem</div><div class="text-xs font-black text-slate-700">' + (it.tercatat ?? '-') + '</div></div>'
                                + '<div class="bg-slate-50 rounded-lg px-1 py-1"><div class="text-[9px] font-bold text-slate-400 uppercase">Fisik</div><div class="text-xs font-black text-slate-700">' + (it.terhitung ?? '-') + '</div></div>'
                                + '<div class="bg-orange-50 rounded-lg px-1 py-1"><div class="text-[9px] font-bold uppercase text-orange-500">Selisih</div><div class="text-xs font-black text-orange-600">' + ((it.selisih !== null && it.selisih > 0 ? '+' : '') + (it.selisih ?? '-')) + '</div></div>'
                                + '</div>'
                                + '<div class="mt-1 text-[10px] text-slate-400 font-medium">' + escHtml(it.oleh) + (it.catatan && it.catatan !== '-' ? ' · ' + escHtml(it.catatan) : '') + '</div>'
                                + '</div>';
                        }).join('');
                    }
                    if (window.uiModalOpen) window.uiModalOpen('modal-selesai');
                })
                .catch(function () {
                    if (summary) summary.textContent = 'Gagal memuat data, coba lagi.';
                    if (window.uiModalOpen) window.uiModalOpen('modal-selesai');
                });
        };
        @if($errors->any() && (old('pembuat_ba') || old('pemeriksa_ba')))
            window.addEventListener('DOMContentLoaded', function () { if (window.uiModalOpen) window.uiModalOpen('modal-selesai'); });
        @endif
    </script>
</x-layout-users>
