            <x-ui.glass-card padding="none" class="!p-0 overflow-clip flex flex-col w-full !rounded-3xl !bg-white border border-slate-200 border-t-4 border-t-blue-500 shadow-lg" style="background:#ffffff;">
                <form action="{{ $sjFilterAction ?? route('warehouse.sj.index') }}" method="GET" class="p-5 pb-4 border-b border-slate-100 bg-white">
                    @if(request('tab'))<input type="hidden" name="tab" value="{{ request('tab') }}">@endif
                    <div class="grid grid-cols-2 lg:grid-cols-12 gap-2 md:gap-3 items-end">
                        <div class="lg:col-span-3 col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari SJ / customer / supplier</label>
                            <x-ui.search-input name="search" placeholder="cth. SJ-001 / RSUD / PT…" value="{{ request('search') }}" autocomplete="off" class="w-full" />
                        </div>
                        <div class="lg:col-span-2 col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tgl Mulai</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="ui-input ui-input--sm !bg-white w-full">
                        </div>
                        <div class="lg:col-span-2 col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tgl Selesai</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="ui-input ui-input--sm !bg-white w-full">
                        </div>
                        <div class="lg:col-span-3 col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Customer</label>
                            <select name="customer_id" onchange="this.form.submit()" class="ui-input ui-input--sm !bg-white w-full">
                                <option value="">Semua</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ (string) request('customer_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->customer_name }}</option>
                                @endforeach
                                <option value="__other" {{ request('customer_id') === '__other' ? 'selected' : '' }}>Bukan customer</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2 col-span-2 flex flex-row gap-2 items-end">
                            <button type="submit" formaction="{{ route('warehouse.sj.export.pdf') }}" class="ui-export ui-export--pdf flex-1 min-w-0" title="Export PDF rekap sesuai filter aktif">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                            <a href="{{ ($sjFilterAction ?? route('warehouse.sj.index')) . (request('tab') ? '?tab=' . request('tab') : '') }}" class="flex-1 min-w-0 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold h-[30px] px-2 rounded-lg text-xs transition-all inline-flex items-center justify-center border border-slate-200 shadow-sm leading-none" title="Reset filter"><i class="fas fa-undo mr-1 text-[11px]"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="flex-1 bg-white manage-table-scroll">
                    <table class="w-full text-sm text-left text-slate-600 bg-white">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider">
                            <tr>
                                <th class="px-4 py-4 w-12 text-center">No</th>
                                <th class="px-4 py-4">Tanggal</th>
                                <th class="px-4 py-4">No. SJ</th>
                                <th class="px-4 py-4">Pihak</th>
                                <th class="px-4 py-4">Product</th>
                                <th class="px-4 py-4">Dibuat Oleh</th>
                                <th class="px-4 py-4 text-center">Cetak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($docs as $index => $d)
                                @php
                                    $pihak = $d->customer?->customer_name ?? $d->supplier?->supplier_name ?? 'Pemakaian Internal';
                                    $alamatPihak = $d->customer?->displayAddress() ?? $d->supplier?->displayAddress() ?? '-';
                                    $moves = $d->moves ?? collect();
                                @endphp
                                <tr class="hover:bg-blue-50/60 transition-colors">
                                    <td class="px-4 py-3 text-center text-slate-400 font-medium">{{ $docs->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700 text-xs">{{ $d->mutation_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-mono font-bold text-slate-800 text-xs">{{ $d->doc_no }}</div>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @if($d->purpose)<span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-bold">{{ $d->purpose }}</span>@endif
                                        </div>
                                        @if($d->po_no)<div class="text-[10px] text-slate-500 font-semibold mt-1">PO: {{ $d->po_no }}</div>@endif
                                        @if($d->expedition)<div class="text-[10px] text-slate-500 font-semibold mt-0.5">Ekspedisi: {{ $d->expedition }}</div>@endif
                                    </td>
                                    <td class="px-4 py-3 text-xs max-w-[220px]">
                                        <div class="font-bold text-slate-700 truncate" title="{{ $pihak }}">{{ $pihak }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium mt-0.5 leading-snug" title="{{ $alamatPihak }}">{{ \Illuminate\Support\Str::limit($alamatPihak, 60) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-xs max-w-[240px]">
                                        @forelse($moves->take(4) as $m)
                                            <div class="truncate" title="{{ $m->product->product_name_clean ?? $m->product->product_name ?? '-' }}">
                                                <span class="font-bold text-slate-700">{{ $m->product->product_name_clean ?? $m->product->product_name ?? '-' }}</span>
                                                <span class="text-slate-400 font-semibold">× {{ rtrim(rtrim(number_format((float) $m->qty, 2, '.', ''), '0'), '.') }} {{ $m->uom ?? $m->product->unit ?? '' }}</span>
                                            </div>
                                        @empty
                                            <span class="text-slate-300">—</span>
                                        @endforelse
                                        @if($moves->count() > 4)
                                            <div class="text-[10px] text-blue-600 font-bold mt-0.5">+{{ $moves->count() - 4 }} item lainnya</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-600 font-semibold whitespace-nowrap">{{ $d->creator?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('warehouse.sj.print', $d->id) }}" class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 h-[30px] px-3 rounded-lg text-[11px] font-bold transition-colors whitespace-nowrap" title="Cetak / unduh PDF surat jalan">
                                            <i class="fas fa-print text-[10px]"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-0"><x-ui.empty icon="fa-file-pdf" title="Belum ada dokumen" message="Dokumen muncul otomatis setiap ada pencatatan barang masuk/keluar." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($docs->hasPages())
                <div class="px-6 py-3 border-t border-slate-200 bg-white flex items-center justify-between gap-3">
                    <div class="flex-1 flex justify-start">{{ $docs->links() }}</div>
                    <span class="text-[11px] text-slate-400 font-semibold whitespace-nowrap">Total Dokumen: <b class="text-slate-700">{{ number_format($docs->total(), 0, ',', '.') }}</b></span>
                </div>
                @else
                <div class="px-6 py-3 border-t border-slate-200 bg-white flex items-center justify-end">
                    <span class="text-[11px] text-slate-400 font-semibold whitespace-nowrap">Total Dokumen: <b class="text-slate-700">{{ number_format($docs->total(), 0, ',', '.') }}</b></span>
                </div>
                @endif
            </x-ui.glass-card>
