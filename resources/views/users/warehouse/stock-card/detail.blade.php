{{-- Partial isi Kartu Stock — dipakai halaman penuh (stock-card.show), embed AJAX (WsStockCardController), dan tab Kartu (pencatatan). --}}
@php
    $nama = $nama ?? ($product->product_name_clean ?: $product->product_name);
    $satuan = $satuan ?? ($product->unit ?: ($product->fill_unit ?: 'Pcs'));
    $sisaKini = count($rows) > 0 ? $rows[count($rows) - 1]['sisa'] : ($product->stock ?? $saldoAwal);
@endphp

{{-- Meta untuk sinkron header popup modal (dibaca JS setelah embed dimuat). --}}
<div id="kartuDetailMeta" class="hidden"
    data-product-id="{{ $product->id }}"
    data-nama="{{ $nama }}"
    data-kode="{{ $product->product_code }}"
    data-stock="{{ $sisaKini }}"
    data-unit="{{ $satuan }}"
    data-saldo="{{ number_format($stockAwalOpname ?? $saldoAwal, 0, ',', '.') }}"
    data-saldo-awal-periode="{{ $saldoAwal }}"
    data-updated="{{ $product->updated_at?->format('d M Y H:i') ?? '-' }}"></div>

{{-- Info produk (disembunyikan di popup — datanya sudah di header modal). --}}
@if(empty($embed))
<x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg !rounded-3xl !bg-white">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="min-w-0">
            <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400">{{ $product->product_code }}</div>
            <h2 class="text-lg font-black text-slate-800 leading-tight">{{ $nama }}</h2>
            <p class="text-[11px] text-slate-500 font-medium">Satuan: <span class="font-bold text-slate-700">{{ $satuan }}</span>
                @if($product->pcs_per_unit > 1) · Isi {{ $product->pcs_per_unit }} {{ $product->fill_unit ?? 'Pcs' }}@endif
                · Min-stock {{ $product->min_stock ?? '-' }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <div class="bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-right">
                <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400">Stok Awal / Opname</div>
                <div class="text-xl font-black text-blue-700 leading-tight">{{ number_format($stockAwalOpname ?? $saldoAwal, 0, ',', '.') }} <span class="text-xs text-slate-500">{{ $satuan }}</span></div>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-right">
                <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400">Stok Kini</div>
                <div class="text-2xl font-black text-slate-800 leading-tight" data-stok-kini>{{ $sisaKini }}</div>
            </div>
        </div>
    </div>
</x-ui.glass-card>
@endif

{{-- Tabel kartu (filter Dari/Sampai + Filter Arah Keluar/Masuk + Export PDF menyatu di header; tanpa garis biru di popup) --}}
<div data-stock-card-root class="flex flex-col flex-1 min-h-0 w-full {{ empty($embed) ? 'ui-glass !p-0 !rounded-2xl !bg-white border border-slate-200 shadow-sm border-t-4 border-t-blue-500 overflow-clip' : '!bg-transparent !border-0 !shadow-none !rounded-none overflow-hidden' }}">
    <div class="px-4 py-1.5 border-b border-slate-200 bg-slate-50/90 flex flex-col sm:flex-row sm:items-center justify-between gap-2 shrink-0 text-xs">
        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 text-xs font-black text-slate-800 shrink-0">
                <i class="fas fa-list-check text-blue-600 text-xs"></i> 
                <span>Riwayat Mutasi</span>
            </span>

            {{-- Filter Arah (Keluar & Masuk) --}}
            <div class="flex items-center gap-1 pl-2 border-l border-slate-200 text-[11px] font-bold text-slate-600">
                <span class="text-[10px] text-slate-400 uppercase tracking-wider mr-0.5">Filter:</span>
                <button type="button" 
                    data-kartu-filter-btn="keluar"
                    class="btn-kartu-arah h-[28px] px-2.5 rounded-lg border border-slate-200 bg-white hover:bg-red-50 hover:text-red-600 hover:border-red-200 text-slate-600 text-[11px] font-bold transition-all flex items-center gap-1 shadow-2xs cursor-pointer" 
                    title="Filter hanya barang keluar">
                    <i class="fas fa-arrow-up text-red-500 text-[10px]"></i> <span>Keluar</span>
                </button>
                <button type="button" 
                    data-kartu-filter-btn="masuk"
                    class="btn-kartu-arah h-[28px] px-2.5 rounded-lg border border-slate-200 bg-white hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 text-slate-600 text-[11px] font-bold transition-all flex items-center gap-1 shadow-2xs cursor-pointer" 
                    title="Filter hanya barang masuk">
                    <i class="fas fa-arrow-down text-blue-500 text-[10px]"></i> <span>Masuk</span>
                </button>
            </div>

            @if(empty($embed) && $canManage)
            {{-- Tombol Cepat Input Masuk / Keluar (Mode Full Page show.blade.php) --}}
            <div class="flex items-center gap-1.5 pl-2 border-l border-slate-200">
                <button type="button" 
                    onclick="if(typeof openKartuQuickMutasi === 'function') { openKartuQuickMutasi('masuk', {{ $product->id }}, '{{ addslashes($nama) }}', '{{ $product->product_code }}', {{ $sisaKini }}, '{{ $satuan }}'); }"
                    class="h-[28px] px-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer" 
                    title="Catat barang masuk untuk produk ini">
                    <i class="fas fa-plus-circle text-emerald-200 text-xs"></i> <span>Masuk</span>
                </button>
                <button type="button" 
                    onclick="if(typeof openKartuQuickMutasi === 'function') { openKartuQuickMutasi('keluar', {{ $product->id }}, '{{ addslashes($nama) }}', '{{ $product->product_code }}', {{ $sisaKini }}, '{{ $satuan }}'); }"
                    class="h-[28px] px-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 active:scale-95 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer" 
                    title="Catat barang keluar untuk produk ini">
                    <i class="fas fa-minus-circle text-rose-200 text-xs"></i> <span>Keluar</span>
                </button>
            </div>
            @endif
        </div>

        {{-- Sisi Kanan: Form Filter Periode Tanggal + Terapkan + Tahun Ini + PDF --}}
        <div class="flex items-center flex-wrap gap-2 shrink-0">
            <form method="GET" action="{{ route('warehouse.stockcard.show', $product) }}" data-remote="filter" class="flex items-center gap-1.5">
                <div class="flex items-center gap-1">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Dari</span>
                    <input type="date" lang="id-ID" name="from" value="{{ $from }}" class="ui-input !h-[28px] !text-xs !py-0.5 !px-2 !bg-white">
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Sampai</span>
                    <input type="date" lang="id-ID" name="to" value="{{ $to }}" class="ui-input !h-[28px] !text-xs !py-0.5 !px-2 !bg-white">
                </div>
                <button type="submit" class="h-[28px] px-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer transition-colors">Terapkan</button>
                <button type="button" onclick="const y = new Date().getFullYear(); const f = this.form; f.from.value = y + '-01-01'; f.to.value = y + '-12-31'; f.requestSubmit ? f.requestSubmit() : f.submit();" class="h-[28px] px-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold transition-all shadow-2xs cursor-pointer" title="Tampilkan seluruh tanggal di tahun berjalan">Tahun Ini</button>
            </form>
            <div class="pb-0"><x-ui.export-button variant="pdf" label="PDF" :href="route('warehouse.stockcard.export.pdf', $product) . '?from=' . $from . '&to=' . $to" title="Export PDF periode ini" class="!h-[28px] !text-xs !px-2.5" /></div>
        </div>
    </div>
    <div class="overflow-x-auto overflow-y-auto flex-1 min-h-0 pb-2">
        <table class="w-full min-w-[1060px] text-left text-sm whitespace-nowrap table-bordered border-separate border-spacing-0">
            <thead class="bg-slate-50 text-[10px] sm:text-[11px] uppercase tracking-wider text-slate-600 border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                <tr class="bg-slate-50">
                    <th class="py-2 px-4 text-center bg-slate-50 border-b border-slate-200 w-[140px] min-w-[130px]">Tanggal</th>
                    <th class="py-2 px-3 text-center bg-slate-50 border-b border-slate-200 w-[120px] min-w-[100px]">Batch No./SN</th>
                    <th class="py-2 px-4 text-left bg-slate-50 border-b border-slate-200 min-w-[320px]">Keterangan</th>
                    <th class="py-2 px-3 text-center bg-slate-50 border-b border-slate-200 w-[85px] min-w-[75px]">Masuk</th>
                    <th class="py-2 px-3 text-center bg-slate-50 border-b border-slate-200 w-[85px] min-w-[75px]">Keluar</th>
                    <th class="py-2 px-3 text-center bg-slate-50 border-b border-slate-200 w-[95px] min-w-[85px]">Sisa</th>
                    <th class="py-2 px-3 text-center bg-slate-50 border-b border-slate-200 w-[110px] min-w-[100px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr class="bg-blue-50/50" data-row-kind="saldo">
                    <td class="py-2 px-4 text-xs font-bold" colspan="3">
                        <div class="flex items-center flex-wrap gap-2">
                            <span class="text-slate-800 font-extrabold">{{ $saldoLabel }}</span>
                            @if(!empty($saldoBadge))
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200/80 align-middle">
                                    <i class="fas fa-info-circle text-[9px] text-blue-600"></i> {{ $saldoBadge }}
                                </span>
                            @elseif(!empty($tanpaOpname))
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200/80 align-middle">
                                    <i class="fas fa-exclamation-triangle text-[9px] text-amber-500"></i> Belum opname — data terakhir {{ \Carbon\Carbon::parse($to)->locale('id')->translatedFormat('d M Y') }}
                                </span>
                            @endif
                        </div>
                        @if(!empty($saldoSubtext))
                            <div class="text-[11px] text-slate-500 font-normal mt-0.5 flex items-center gap-1.5">
                                <i class="fas fa-history text-[10px] text-blue-500"></i>
                                <span>{{ $saldoSubtext }}</span>
                            </div>
                        @endif
                    </td>
                    <td class="py-2 px-3 text-center text-xs text-slate-400 font-medium">-</td>
                    <td class="py-2 px-3 text-center text-xs text-slate-400 font-medium">-</td>
                    <td class="py-2 px-3 text-center font-black text-slate-900 text-sm bg-blue-100/40">{{ $saldoAwal }}</td>
                    <td class="py-2 px-3 text-center whitespace-nowrap">
                        @if($canManage)
                            @if(!empty($tanpaOpname))
                                <button type="button"
                                    onclick="if (typeof openEditBarangFromKartu === 'function') { openEditBarangFromKartu({{ $product->id }}); } else { window.location.href = '{{ route('warehouse.index', ['tab' => 'data-product', 'edit_id' => $product->id]) }}'; }"
                                    class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors shadow-2xs cursor-pointer"
                                    title="Edit stok awal di Data Produk">
                                    <i class="fas fa-pen text-xs leading-none shrink-0"></i>
                                </button>
                            @else
                                <a href="{{ route('warehouse.stockopname.index') }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition-colors shadow-2xs"
                                    title="Buka rekap Stock Opname">
                                    <i class="fas fa-external-link-alt text-xs leading-none shrink-0"></i>
                                </a>
                            @endif
                        @endif
                    </td>
                </tr>
                @forelse($rows as $r)
                    @if(!empty($r['is_saldo_row']))
                        {{-- Baris Khusus Hasil Opname Fisik / Perubahan Stok Awal Tiap Bulan (Gaya Highlight Baris Pertama) --}}
                        <tr class="bg-blue-50/70 hover:bg-blue-100/60 transition-colors border-y-2 border-blue-200/80" data-row-kind="saldo" data-row-arah="saldo">
                            <td class="py-2.5 px-4 text-xs font-bold text-center text-slate-700">
                                {{ $r['tanggal_display'] ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 font-mono text-xs text-center text-slate-500">
                                {{ $r['batch_no'] ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-xs">
                                <div class="flex items-center flex-wrap gap-2">
                                    <span class="font-bold text-slate-900">{{ $r['label'] }}</span>
                                    @if(!empty($r['badge']))
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $r['badge_class'] ?? 'bg-emerald-50 text-emerald-700 border-emerald-200' }} align-middle">
                                            <i class="fas {{ $r['kind'] === 'opname' ? 'fa-clipboard-check text-emerald-600' : 'fa-history text-amber-600' }} text-[9px]"></i> {{ $r['badge'] }}
                                        </span>
                                    @endif
                                </div>
                                @if(!empty($r['subtext']))
                                    <div class="text-[11px] text-slate-600 font-normal mt-0.5 flex items-center gap-1.5">
                                        <i class="fas fa-info-circle text-[10px] text-blue-500 shrink-0"></i>
                                        <span>{{ $r['subtext'] }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center text-xs text-slate-400 font-medium">-</td>
                            <td class="py-2.5 px-3 text-center text-xs text-slate-400 font-medium">-</td>
                            <td class="py-2.5 px-3 text-center font-black text-slate-900 text-sm bg-blue-100/50">
                                <span class="px-2 py-0.5 rounded bg-blue-100/60 border border-blue-200 text-blue-900 font-black">{{ $r['sisa'] }}</span>
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                @if($r['kind'] === 'opname')
                                    @php
                                        $opPeriod = $r['opname']->period ?? substr($r['tanggal_date'] ?? $to, 0, 7);
                                        $catatUrl = route('warehouse.stockopname.catat', ['bulan' => $opPeriod, 'product_id' => $product->id]);
                                    @endphp
                                    <div class="inline-flex items-center gap-1.5 justify-center">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200" title="Saldo resmi di-reset dari hasil opname fisik">
                                            <i class="fas fa-lock text-[8px] text-emerald-500"></i> Opname Fisik
                                        </span>
                                        @if($canManage)
                                            <a href="{{ $catatUrl }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-300 transition-colors shadow-2xs"
                                                title="Edit hasil fisik di modul Stock Opname">
                                                <i class="fas fa-pen text-xs leading-none shrink-0"></i>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    @php
                                        $editProdukUrl = route('warehouse.index', ['tab' => 'data-product', 'edit_id' => $product->id]);
                                    @endphp
                                    <div class="inline-flex items-center gap-1.5 justify-center">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200" title="Saldo di-reset dari stok awal produk">
                                            <i class="fas fa-lock text-[8px] text-amber-500"></i> Stok Awal
                                        </span>
                                        @if($canManage)
                                            <button type="button"
                                                onclick="if (typeof openEditBarangFromKartu === 'function') { openEditBarangFromKartu({{ $product->id }}); } else { window.location.href = '{{ $editProdukUrl }}'; }"
                                                class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-300 transition-colors shadow-2xs cursor-pointer"
                                                title="Edit stok awal di Data Produk">
                                                <i class="fas fa-pen text-xs leading-none shrink-0"></i>
                                            </button>
                                            @if(!empty($r['move']))
                                                <button type="button" data-kartu-delete
                                                    data-url="{{ route('warehouse.stockcard.moves.destroy', $r['move']->id) }}"
                                                    class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-lg bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 transition-colors shadow-2xs cursor-pointer"
                                                    title="Hapus / batalkan pencatatan stok awal ini">
                                                    <i class="fas fa-trash-alt text-xs leading-none shrink-0"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @else
                        @php 
                            $m = $r['move']; 
                            $isKeluar = ($r['keluar'] > 0);
                            $isMasuk = ($r['masuk'] > 0);
                            $rowArah = $isKeluar ? 'keluar' : ($isMasuk ? 'masuk' : 'other');
                            $ketDisplay = $r['keterangan'] ?? ($m->reason ?: ($m->ref_type ? $m->ref_type . ($m->ref_id ? ' #' . $m->ref_id : '') : '-'));
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors" data-row-kind="mutasi" data-row-arah="{{ $rowArah }}">
                            <td class="py-2 px-4 text-xs font-medium text-center">{{ $r['tanggal_display'] ?? \Carbon\Carbon::parse($m->created_at)->format('d M Y H:i') }}</td>
                            <td class="py-2 px-3 font-mono text-xs text-center">{{ trim((string)($m->batch_no ?? '')) !== '' ? $m->batch_no : '-' }}</td>
                            <td class="py-2 px-4 text-xs max-w-[650px] 2xl:max-w-[950px] truncate" title="{{ $ketDisplay }}">{{ $ketDisplay }}</td>
                            <td class="py-2 px-3 text-center font-bold text-blue-600">{{ $r['masuk'] > 0 ? $r['masuk'] : '-' }}</td>
                            <td class="py-2 px-3 text-center font-bold text-red-600">{{ $r['keluar'] > 0 ? $r['keluar'] : '-' }}</td>
                            <td class="py-2 px-3 text-center font-black">{{ $r['sisa'] }}</td>
                            <td class="py-2 px-3 text-center whitespace-nowrap">
                                @if($canManage)
                                    <button type="button" data-kartu-edit
                                        data-id="{{ $m->id }}"
                                        data-url="{{ route('warehouse.stockcard.moves.update', $m->id) }}"
                                        data-tanggal="{{ \Carbon\Carbon::parse($m->created_at)->format('Y-m-d') }}"
                                        data-batch="{{ $m->batch_no ?? '' }}"
                                        data-keterangan="{{ $ketDisplay }}"
                                        @php $cQty = (float) $m->qty; $qVal = $isKeluar ? -$cQty : $cQty; @endphp
                                        data-qty="{{ $qVal }}"
                                        data-arah="{{ $isKeluar ? 'Keluar' : 'Masuk' }}"
                                        class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200" title="Edit baris"><i class="fas fa-pen text-xs leading-none shrink-0"></i></button>
                                    <button type="button" data-kartu-delete
                                        data-url="{{ route('warehouse.stockcard.moves.destroy', $m->id) }}"
                                        class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-lg bg-red-50 hover:bg-red-100 text-red-600 border border-red-200" title="Hapus baris (reversal; riwayat opname terkait ikut dihapus)"><i class="fas fa-trash text-xs leading-none shrink-0"></i></button>
                                @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr class="kartu-empty-row"><td colspan="7" class="py-6 text-center text-xs text-slate-400 font-medium">Belum ada mutasi pada periode ini.</td></tr>
                @endforelse
                <tr id="kartuEmptyFilterRow" class="hidden"><td colspan="7" class="py-6 text-center text-xs text-slate-400 font-medium">Tidak ada transaksi barang <span id="kartuEmptyFilterLabel" class="font-bold"></span> pada periode ini.</td></tr>
            </tbody>
        </table>
    </div>
</div>
