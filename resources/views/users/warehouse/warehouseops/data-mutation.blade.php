            {{-- TAB 2: Data Mutation (tabel + rekap SJ + export) --}}
            <div class="flex-1 min-h-0 flex flex-col w-full overflow-hidden">
                <x-ui.glass-card padding="none" class="!p-0 overflow-hidden flex-1 min-h-0 flex flex-col w-full !rounded-2xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-sm" style="background:#ffffff;">
                    <form action="{{ route('warehouse.index') }}" method="GET" id="filterFormMutasi" class="px-4 py-2 border-b border-slate-100 bg-white shrink-0">
                        <input type="hidden" name="tab" value="mutasi">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-2 items-end">
                            <div class="lg:col-span-4">
                                <label class="filter-label-compact" style="font-size: 10px !important;">Cari Barang / No. Batch / Pihak</label>
                                <x-ui.search-input name="search" placeholder="Cari Nama Barang, No. Batch, atau Pihak..." value="{{ request('search') }}" autocomplete="off" class="w-full" :debounce="500" />
                            </div>
                            <div class="lg:col-span-3">
                                <label class="filter-label-compact" style="font-size: 10px !important;">Barang</label>
                                <x-ui.filter-select name="product_id" onchange="document.getElementById('filterFormMutasi').submit()" value="{{ request('product_id') }}" placeholder="Semua Barang" class="w-full">
                                    @foreach($productOptions as $p)
                                        <option value="{{ $p->id }}" {{ (string) request('product_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->product_name ?: $p->product_name_clean }} (stok {{ $p->stock }})</option>
                                    @endforeach
                                </x-ui.filter-select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="filter-label-compact" style="font-size: 10px !important;">Arah</label>
                                <x-ui.filter-select name="kelola_arah" onchange="document.getElementById('filterFormMutasi').submit()" value="{{ request('kelola_arah') }}" placeholder="Semua Arah (Masuk & Keluar)" class="w-full">
                                    <option value="masuk" {{ request('kelola_arah') === 'masuk' ? 'selected' : '' }}>Masuk Saja</option>
                                    <option value="keluar" {{ request('kelola_arah') === 'keluar' ? 'selected' : '' }}>Keluar Saja</option>
                                </x-ui.filter-select>
                            </div>
                            <div class="lg:col-span-3">
                                <label class="filter-label-compact" style="font-size: 10px !important;">Tanggal</label>
                                <x-ui.filter-date name="tanggal" value="{{ request('tanggal') }}" onchange="document.getElementById('filterFormMutasi').submit()" class="w-full" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-12 gap-2 items-end mt-2">
                            <div class="lg:col-span-2">
                                <label class="filter-label-compact" style="font-size: 10px !important;">Bulan</label>
                                <x-ui.filter-select name="bulan" onchange="document.getElementById('filterFormMutasi').submit()" value="{{ request('bulan') }}" placeholder="Semua Bulan" class="w-full">
                                    @for($b = 1; $b <= 12; $b++)
                                        <option value="{{ $b }}" {{ (string) request('bulan') === (string) $b ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $b, 1)->format('F') }}</option>
                                    @endfor
                                </x-ui.filter-select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="filter-label-compact" style="font-size: 10px !important;">Tahun</label>
                                <x-ui.filter-select name="tahun" onchange="document.getElementById('filterFormMutasi').submit()" value="{{ request('tahun') }}" placeholder="Semua Tahun" class="w-full">
                                    @foreach($tahunAda as $t)
                                        <option value="{{ $t }}" {{ (string) request('tahun') === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </x-ui.filter-select>
                            </div>
                            <div class="lg:col-span-4 flex items-end">
                                <x-ui.info class="w-full" icon="fa-database" label="Total Baris:" :value="number_format(($kelolaMoves ?? collect())->count(), 0, ',', '.')" />
                            </div>
                            <div class="lg:col-span-4 flex gap-2 items-end">
                                <a href="{{ route('warehouse.export-mutasi', request()->except(['kelolaPage', 'sjPage', 'page'])) }}" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center shadow-sm leading-none" title="Export PDF rekap mutasi (filter aktif)">
                                    <i class="fas fa-file-pdf mr-1.5" style="font-size: 11px;"></i> PDF Mutasi
                                </a>
                                <a href="{{ route('warehouse.index', ['tab' => 'mutasi']) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold h-[30px] px-3 rounded-lg text-xs transition-all inline-flex items-center justify-center border border-slate-200 shadow-sm leading-none" title="Reset Filters">
                                    <i class="fas fa-undo" style="font-size: 11px;"></i>
                                </a>
                            </div>
                        </div>
                    </form>

                    @if($canManage)
                    <div id="bulk-bar-mutasi" class="px-5 py-2 bg-rose-50 border-b border-rose-200 items-center justify-between gap-3 flex-wrap" style="display:none">
                        <span class="text-xs font-bold text-rose-700">
                            <i class="fas fa-check-square mr-1"></i>
                            <span id="bulk-bar-mutasi-count">0</span> baris mutasi dipilih
                        </span>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="clearBulkMutasiSelection()" class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-600 text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                Batal
                            </button>
                            <button type="button" onclick="openBulkDeleteMutasi()" class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm">
                                <i class="fas fa-trash-can"></i> Hapus Terpilih
                            </button>
                        </div>
                    </div>
                    <form id="bulk-delete-mutasi-form" action="{{ route('warehouse.mutation.bulk_destroy') }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                        <div id="bulk-delete-mutasi-ids"></div>
                    </form>
                    @endif

                    <div class="flex-1 min-h-0 bg-white manage-table-scroll">
                        <table class="w-full text-left text-slate-600 bg-white table-bordered table-mutation">
                            <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 z-10 shadow-xs" style="font-size: 10px !important;">
                                <tr>
                                    <th class="w-8 text-center bg-slate-100/80">
                                        <input type="checkbox" id="check-all-kelola" class="row-check-all h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" aria-label="Pilih semua baris">
                                    </th>
                                    <th class="w-9 text-center bg-slate-100/70 text-slate-500">No</th>
                                    <th class="whitespace-nowrap bg-slate-50">Tanggal & Dokumen</th>
                                    <th class="bg-slate-50">Customer / Supplier</th>
                                    <th class="bg-slate-50">Barang</th>
                                    <th class="text-right bg-blue-50/70 text-blue-700 border-l border-blue-100 w-[80px]">
                                        <span class="inline-flex items-center justify-end gap-1"><i class="fas fa-arrow-down" style="font-size: 8.5px;"></i> Masuk</span>
                                    </th>
                                    <th class="text-right bg-rose-50/70 text-rose-700 w-[80px]">
                                        <span class="inline-flex items-center justify-end gap-1">Keluar <i class="fas fa-arrow-up" style="font-size: 8.5px;"></i></span>
                                    </th>
                                    <th class="whitespace-nowrap bg-slate-50">Batch / ED</th>
                                    <th class="bg-slate-50">Keterangan</th>
                                    <th class="bg-slate-50">Petugas</th>
                                    <th class="text-center w-24 bg-slate-100/80">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($kelolaMoves as $index => $m)
                                    @php $masuk = $isMasuk($m); @endphp
                                    <tr class="hover:bg-blue-50/60 transition-colors">
                                        <td class="text-center bg-slate-50/50" onclick="event.stopPropagation()">
                                            <input type="checkbox" value="{{ $m->id }}" class="row-checkbox-kelola h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" aria-label="Pilih baris {{ $m->id }}">
                                        </td>
                                        <td class="text-center text-slate-400 font-medium" style="font-size: 11px;">{{ $index + 1 }}</td>
                                        <td class="whitespace-nowrap">
                                            <div class="font-medium text-slate-700 leading-tight" style="font-size: 11px;">{{ $m->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                            <div class="font-mono font-bold text-slate-900 leading-snug mt-0.5" style="font-size: 11.5px;">{{ $m->doc_no ?? $m->d_doc ?? '-' }}</div>
                                            @if($m->d_source === 'faktur')<div class="mt-0.5"><span class="cell-badge inline-flex items-center rounded bg-sky-50 border border-sky-200 text-sky-700 font-bold">Faktur</span></div>@endif
                                            @if($m->po_no ?? $m->d_po)<div class="font-mono cell-sub">PO: {{ $m->po_no ?? $m->d_po }}</div>@endif
                                            @if($m->expedition ?? $m->d_exped)<div class="cell-sub"><i class="fas fa-truck mr-1" style="font-size: 8px;"></i>{{ $m->expedition ?? $m->d_exped }}</div>@endif
                                        </td>
                                        @php
                                            $pihak = $m->cu_name ?: ($m->su_name ?: ($m->partner ?: null));
                                            if (!$pihak) {
                                                if ($m->ref_type === 'master_stock' || $m->ref_type === 'master_stock_edit') {
                                                    $pihak = 'Stok Awal Produk';
                                                } elseif (str_starts_with((string) $m->reason, 'Opname')) {
                                                    $pihak = 'Penyesuaian Opname';
                                                } elseif ($m->purpose && strcasecmp((string) $m->purpose, 'Internal') === 0) {
                                                    $pihak = 'Pemakaian Internal';
                                                } else {
                                                    $pihak = '-';
                                                }
                                            }
                                        @endphp
                                        <td class="text-slate-600 max-w-[150px] truncate" style="font-size: 11px;" title="{{ $pihak }}{{ ($m->ship_address ?? $m->d_ship) ? ' — ' . ($m->ship_address ?? $m->d_ship) : '' }}">{{ $pihak }}</td>
                                        <td>
                                            <div class="font-bold text-slate-900 leading-snug max-w-[200px] truncate" style="font-size: 11.5px;" title="{{ $m->product->product_name_clean ?: ($m->product->product_name ?? 'Produk Terhapus') }}">{{ $m->product->product_name_clean ?: ($m->product->product_name ?? 'Produk Terhapus') }}</div>
                                            <div class="font-mono cell-sub">{{ $m->product->product_code ?? '-' }}</div>
                                        </td>
                                        <td class="text-right align-top border-l border-blue-50">
                                            @if($masuk)
                                                <div class="font-black text-blue-600 leading-tight" style="font-size: 11.5px;">+{{ number_format($m->qty, 0, ',', '.') }}</div>
                                                @if($m->purpose)
                                                    <div class="mt-0.5 ml-auto max-w-[75px] truncate cell-sub font-bold uppercase tracking-wide text-blue-500" title="{{ $m->purpose }}">{{ $m->purpose }}</div>
                                                @endif
                                            @else
                                                <span class="text-slate-300">&ndash;</span>
                                            @endif
                                        </td>
                                        <td class="text-right align-top">
                                            @if(!$masuk)
                                                <div class="font-black text-red-600 leading-tight" style="font-size: 11.5px;">-{{ number_format($m->qty, 0, ',', '.') }}</div>
                                                @if($m->purpose)
                                                    <div class="mt-0.5 ml-auto max-w-[75px] truncate cell-sub font-bold uppercase tracking-wide text-rose-500" title="{{ $m->purpose }}">{{ $m->purpose }}</div>
                                                @endif
                                            @else
                                                <span class="text-slate-300">&ndash;</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <div class="font-mono text-slate-700 leading-snug" style="font-size: 11px;">{{ trim((string)($m->batch_no ?? '')) !== '' ? $m->batch_no : '-' }}</div>
                                            <div class="cell-sub {{ $m->expiry_date && $m->expiry_date->isPast() ? '!text-red-600 font-bold' : '' }}">
                                                ED: {{ $m->expiry_date?->format('d/m/Y') ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="max-w-[180px] truncate text-slate-600" style="font-size: 11px;" title="{{ $m->reason }}">{{ $m->reason ?: '-' }}</td>
                                        <td class="whitespace-nowrap text-slate-500" style="font-size: 11px;" title="{{ $m->user->name ?? 'System' }}">{{ \Illuminate\Support\Str::words($m->user->name ?? 'System', 2, '') }}</td>
                                        <td class="text-center whitespace-nowrap">
                                            @if($canManage)
                                            <div class="flex items-center justify-center gap-1">
                                                @if($m->d_id)
                                                <button type="button" data-doc="{{ $m->d_id }}" onclick="openDocModal(this.dataset.doc)" class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200 transition" title="Detail dokumen"><i class="fas fa-eye" style="font-size: 10px;"></i></button>
                                                @endif
                                                <button type="button" class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 cursor-pointer transition" title="Edit"
                                                    @php $cQty = (float) $m->qty; $editQty = $masuk ? $cQty : -$cQty; @endphp
                                                    data-doc-id="{{ $m->d_id }}"
                                                    data-edit="{{ json_encode(['id' => $m->id, 'doc_id' => $m->d_id, 'tanggal' => $m->created_at?->format('Y-m-d'), 'qty' => $editQty, 'batch_no' => $m->batch_no, 'expiry_date' => $m->expiry_date?->format('Y-m-d'), 'doc_no' => $m->doc_no ?? $m->d_doc, 'po_no' => $m->po_no ?? $m->d_po, 'ship_address' => $m->ship_address ?? $m->d_ship, 'expedition' => $m->expedition ?? $m->d_exped, 'keterangan' => $m->reason, 'arah' => $masuk ? 'Masuk' : 'Keluar', 'pihak' => $m->cu_name ?: ($m->su_name ?: ($m->partner ?: '-')), 'barang' => ($m->product->product_name_clean ?: ($m->product->product_name ?? ''))]) }}"
                                                    onclick="handleEditMutationClick(this)"><i class="fas fa-pen" style="font-size: 10px;"></i></button>
                                                <form action="{{ route('warehouse.mutation.destroy', $m->id) }}" method="POST" id="form-del-{{ $m->id }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="button" onclick="confirmDeletePencatatan('{{ $m->id }}')" class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 transition" title="Hapus (reversal)"><i class="fas fa-trash" style="font-size: 10px;"></i></button>
                                                </form>
                                            </div>
                                            @else
                                                <span class="text-slate-300" style="font-size: 11px;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="p-0"><x-ui.empty icon="fa-inbox" title="Belum ada pencatatan" message="Semua barang yang diinput dari tab Masuk / Keluar akan muncul di sini." /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-1.5 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between gap-3 shrink-0">
                        <div class="flex items-center gap-1.5 text-slate-400" style="font-size: 11px;">
                            <i class="fas fa-arrows-up-down" style="font-size: 9.5px;"></i>
                            <span>Scroll tabel untuk melihat semua data</span>
                        </div>
                        <span class="text-slate-400 font-semibold whitespace-nowrap leading-none" style="font-size: 11px;">Total Baris: <b class="text-slate-700">{{ number_format(($kelolaMoves ?? collect())->count(), 0, ',', '.') }}</b></span>
                    </div>
                </x-ui.glass-card>
            </div>
