{{-- Partial isi Kartu Stock — dipakai halaman penuh (card-show) & popup modal (embed=1). --}}
@php
    $nama = $nama ?? ($product->product_name_clean ?: $product->product_name);
    $satuan = $satuan ?? ($product->unit ?: ($product->fill_unit ?: 'Pcs'));
@endphp

{{-- Meta untuk sinkron header popup modal (dibaca JS setelah embed dimuat). --}}
<div id="kartuDetailMeta" class="hidden"
    data-nama="{{ $nama }}"
    data-kode="{{ $product->product_code }}"
    data-stock="{{ $product->stock }}"
    data-unit="{{ $satuan }}"
    data-saldo="{{ $saldoAwal }}"
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
                <div class="text-[10px] font-bold tracking-[0.12em] uppercase text-slate-400">Stok Kini</div>
                <div class="text-2xl font-black text-slate-800 leading-tight" data-stok-kini>{{ $product->stock }}</div>
            </div>
        </div>
    </div>
</x-ui.glass-card>
@endif

{{-- Semua mutasi bermula dari Pencatatan Barang (tak ada input langsung dari kartu). --}}
@if($canManage)
    <x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg !rounded-3xl !bg-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="min-w-0">
                <h3 class="text-[13px] font-bold text-slate-800 leading-tight">Tambah Mutasi?</h3>
                <p class="text-[11px] text-slate-500 font-medium">Catat lewat Pencatatan Barang — otomatis masuk ke kartu ini.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('warehouse.mutation.index', ['tab' => 'masuk']) }}" class="h-[34px] px-4 inline-flex items-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm"><i class="fas fa-arrow-down text-[11px] mr-1.5"></i> Barang Masuk</a>
                <a href="{{ route('warehouse.sj.index', ['tab' => 'keluar']) }}" class="h-[34px] px-4 inline-flex items-center rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs font-bold shadow-sm"><i class="fas fa-arrow-up text-[11px] mr-1.5"></i> Barang Keluar</a>
            </div>
        </div>
    </x-ui.glass-card>
@endif

{{-- Tabel kartu (filter Dari/Sampai + Export PDF menyatu di header; tanpa garis biru di popup) --}}
<x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full max-w-full mx-auto !rounded-3xl !bg-white border border-slate-200 shadow-lg{{ empty($embed) ? ' border-t-4 border-t-blue-500' : '' }}">
    <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex flex-col lg:flex-row lg:items-center justify-between gap-2">
        <div>
            <h3 class="text-[13px] font-bold text-slate-800 leading-tight">Tabel Pencatatan</h3>
            <p class="text-[11px] text-slate-500 font-medium leading-tight">Tanggal · Batch No./SN · Keterangan · Masuk · Keluar · Sisa</p>
        </div>
        <div class="flex items-end gap-2">
            <form method="GET" action="{{ route('warehouse.stockcard.show', $product) }}" data-remote="filter" class="flex items-end gap-2">
                <div><label class="ui-label">Dari</label><input type="date" lang="id-ID" name="from" value="{{ $from }}" class="ui-input !bg-white"></div>
                <div><label class="ui-label">Sampai</label><input type="date" lang="id-ID" name="to" value="{{ $to }}" class="ui-input !bg-white"></div>
                <div><button class="h-[34px] px-4 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold">Terapkan</button></div>
            </form>
            <div class="pb-0"><x-ui.export-button variant="pdf" label="PDF" :href="route('warehouse.stockcard.export.pdf', $product) . '?from=' . $from . '&to=' . $to" title="Export PDF periode ini" /></div>
        </div>
    </div>
    <div class="overflow-x-auto pb-4">
        <table class="w-full min-w-[940px] text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                <tr><th class="py-2.5 px-4">Tanggal</th><th class="py-2.5 px-3">Batch No./SN</th><th class="py-2.5 px-3">Keterangan</th><th class="py-2.5 px-3 text-right">Masuk</th><th class="py-2.5 px-3 text-right">Keluar</th><th class="py-2.5 px-3 text-right">Sisa</th><th class="py-2.5 px-3 text-center">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr class="bg-blue-50/50">
                    <td class="py-2 px-4 text-xs font-bold" colspan="3">{{ $saldoLabel }}@if(!empty($tanpaOpname)) <span class="ml-1 text-[9px] font-black px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 align-middle">Belum opname — data terakhir {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span>@endif</td>
                    <td class="py-2 px-3 text-right text-xs">-</td>
                    <td class="py-2 px-3 text-right text-xs">-</td>
                    <td class="py-2 px-3 text-right font-black">{{ $saldoAwal }}</td>
                    <td class="py-2 px-3"></td>
                </tr>
                @forelse($rows as $r)
                    @php $m = $r['move']; @endphp
                    <tr class="hover:bg-slate-50/60">
                        <td class="py-2 px-4 text-xs font-medium">{{ \Carbon\Carbon::parse($m->created_at)->format('d M Y H:i') }}</td>
                        <td class="py-2 px-3 font-mono text-xs">{{ $m->batch_no ?? '-' }}</td>
                        <td class="py-2 px-3 text-xs max-w-[320px] truncate" title="{{ $m->reason }}">{{ $m->reason ?: ($m->ref_type ? $m->ref_type . ($m->ref_id ? ' #' . $m->ref_id : '') : '-') }}</td>
                        <td class="py-2 px-3 text-right font-bold text-blue-600">{{ $r['masuk'] > 0 ? $r['masuk'] : '-' }}</td>
                        <td class="py-2 px-3 text-right font-bold text-red-600">{{ $r['keluar'] > 0 ? $r['keluar'] : '-' }}</td>
                        <td class="py-2 px-3 text-right font-black">{{ $r['sisa'] }}</td>
                        <td class="py-2 px-3 text-center whitespace-nowrap">
                            @if($canManage)
                                <button type="button" data-kartu-edit
                                    data-id="{{ $m->id }}"
                                    data-url="{{ route('warehouse.stockcard.moves.update', $m->id) }}"
                                    data-tanggal="{{ \Carbon\Carbon::parse($m->created_at)->format('Y-m-d') }}"
                                    data-batch="{{ $m->batch_no ?? '' }}"
                                    data-keterangan="{{ $m->reason }}"
                                    data-qty="{{ $m->qty }}"
                                    data-arah="{{ $r['masuk'] > 0 ? 'Masuk' : ($r['keluar'] > 0 ? 'Keluar' : '-') }}"
                                    class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200" title="Edit baris"><i class="fas fa-pen text-xs leading-none shrink-0"></i></button>
                                <button type="button" data-kartu-delete
                                    data-url="{{ route('warehouse.stockcard.moves.destroy', $m->id) }}"
                                    class="inline-flex items-center justify-center w-[28px] h-[28px] rounded-lg bg-red-50 hover:bg-red-100 text-red-600 border border-red-200" title="Hapus baris (reversal)"><i class="fas fa-trash text-xs leading-none shrink-0"></i></button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-xs text-slate-400 font-medium">Belum ada mutasi pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.glass-card>

@if($opnameMoves->isNotEmpty())
    <x-ui.glass-card class="w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-lg !rounded-3xl !bg-white">
        <h3 class="text-[13px] font-bold text-slate-800 mb-1">Acuan Opname Terakhir</h3>
        <div class="space-y-1.5">
            @foreach($opnameMoves as $o)
                <div class="flex items-center justify-between gap-2 text-xs bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5">
                    <span class="font-medium text-slate-600 truncate">Fisik {{ $o->counted_qty }} · tercatat {{ $o->recorded_qty }} · selisih {{ $o->diff_qty > 0 ? '+' : '' }}{{ $o->diff_qty }}@if($o->note) · {{ $o->note }}@endif</span>
                    <span class="font-bold text-slate-700 shrink-0">{{ ($o->opname_at ?? $o->opname_date)?->format('d M Y') ?? '-' }}</span>
                </div>
            @endforeach
        </div>
    </x-ui.glass-card>
@endif
