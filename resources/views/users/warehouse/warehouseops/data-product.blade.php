            {{-- TAB 0: Data Product --}}
            <div class="flex-1 min-h-0 flex flex-col w-full overflow-hidden">
                <x-ui.glass-card padding="none" class="!p-0 overflow-hidden flex-1 min-h-0 flex flex-col w-full max-w-full mx-auto border-t-4 border-t-blue-500 shadow-sm !rounded-2xl !bg-white">
                    <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm shrink-0"><i class="fas fa-list"></i></span>
                            <div class="min-w-0">
                                <h2 class="text-sm font-bold text-slate-800 leading-tight">Product List</h2>
                                <p class="text-[11px] text-slate-500 font-medium leading-tight">{{ $productItems->count() }} products</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 w-full sm:w-auto">
                            <form action="{{ route('warehouse.index') }}" method="GET" id="productFilterForm" class="flex-1 min-w-[140px] max-w-sm hidden sm:flex">
                                <input type="hidden" name="tab" value="data-product">
                                <x-ui.search-input name="search" value="{{ $searchProduct }}" placeholder="Search Code / Product Name" autocomplete="off" class="flex-1" :debounce="500" />
                            </form>
                            <a href="{{ route('warehouse.product.export.pdf') }}" class="shrink-0 inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-bold px-3 py-2.5 rounded-xl transition" title="Export PDF">
                                <i class="fas fa-file-pdf"></i><span class="hidden lg:inline">PDF</span>
                            </a>
                            <a href="{{ route('warehouse.product.export.excel') }}" class="shrink-0 inline-flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold px-3 py-2.5 rounded-xl transition" title="Export Excel">
                                <i class="fas fa-file-excel"></i><span class="hidden lg:inline">Excel</span>
                            </a>
                            @if($canManage)
                            <button type="button" onclick="openProductCreate()" class="shrink-0 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-sm">
                                <i class="fas fa-plus"></i><span class="hidden sm:inline">Add Product</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    @if($canManage)
                    <div id="product-bulk-bar" class="px-5 py-2.5 bg-rose-50 border-b border-rose-200 items-center justify-between gap-3 flex-wrap shrink-0" style="display:none">
                        <span class="text-xs font-bold text-rose-700">
                            <i class="fas fa-check-square mr-1"></i>
                            <span id="product-bulk-bar-count">0</span> products selected
                        </span>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="clearProductBulkSelection()" class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-600 text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                Cancel
                            </button>
                            <button type="button" onclick="openProductBulkDelete()" class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm">
                                <i class="fas fa-trash-can"></i> Delete Selected
                            </button>
                        </div>
                    </div>
                    @endif

                    <div class="sm:hidden px-3 pt-3 shrink-0">
                        <form action="{{ route('warehouse.index') }}" method="GET" id="productFilterFormMobile">
                            <input type="hidden" name="tab" value="data-product">
                            <x-ui.search-input name="search" value="{{ $searchProduct }}" placeholder="Search products..." :debounce="500" />
                        </form>
                    </div>

                    {{-- Mobile cards --}}
                    <div class="md:hidden px-3 pt-2 pb-3 space-y-2 flex-1 min-h-0 overflow-y-auto">
                        @forelse($productItems as $item)
                        <div class="mobile-product-card {{ ($item['stok_awal'] ?? 0) <= 0 ? 'mpc-zero' : '' }}">
                            <div class="flex items-start gap-2.5">
                                @if($canManage)
                                <input type="checkbox" class="product-row-check mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer shrink-0" value="{{ $item['id'] }}" aria-label="Pilih {{ $item['nama'] }}">
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="font-mono font-bold text-xs text-sky-700 uppercase tracking-wide">{{ $item['kode'] }}</div>
                                    <div class="font-bold text-slate-900 text-xs leading-snug break-words mt-1">{{ $item['nama'] }}</div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 bg-violet-100 text-violet-800 border border-violet-200 text-xs font-bold px-2.5 py-0.5 rounded-full shadow-2xs">
                                            {{ $item['kemasan_full'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-black text-sm leading-tight {{ ($item['stok_awal'] ?? 0) <= 0 ? 'text-slate-400' : 'text-blue-900' }}">
                                        {{ number_format($item['stok_awal'] ?? 0, 0, ',', '.') }}
                                        <span class="text-xs font-bold text-blue-700/70">{{ $item['satuan'] }}</span>
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-bold">Stok Awal</div>
                                    <div class="mt-0.5">
                                        @if(!empty($item['has_opname']))
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/70 px-1.5 py-0.2 rounded" title="Sudah pernah diopname — edit stok melalui Stock Opname">
                                                <i class="fas fa-clipboard-check text-[8px]"></i> Opnamed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[9px] font-medium text-slate-500 bg-slate-100 border border-slate-200/60 px-1.5 py-0.2 rounded" title="Produk baru — stok awal dapat diatur">
                                                <i class="fas fa-tag text-[8px]"></i> Stok Awal
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($canManage)
                            <div class="flex items-center justify-end gap-2 mt-2 pt-2 border-t border-slate-100">
                                <x-ui.edit-button title="Ubah" data-product-id="{{ $item['id'] }}" data-item="{{ json_encode($item) }}" onclick="openProductEdit(this)" />
                                <x-ui.delete-button title="Hapus" data-item="{{ json_encode($item) }}" onclick="openProductDelete(this)" />
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-8 text-slate-400 text-xs font-medium">No products found.</div>
                        @endforelse
                    </div>

                    {{-- Desktop table --}}
                    <div class="hidden md:block flex-1 min-h-0 bg-white manage-table-scroll">
                        <table class="w-full text-sm text-left text-slate-600 bg-white table-bordered">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200 font-bold tracking-wider sticky top-0 z-10 shadow-sm backdrop-blur-sm">
                                <tr>
                                    @if($canManage)
                                    <th class="px-2.5 py-2 text-center w-10 bg-slate-100/80">
                                        <input type="checkbox" id="product-check-all" class="product-row-check-all h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" aria-label="Pilih semua produk">
                                    </th>
                                    @endif
                                    <th class="px-2.5 py-2 text-center text-xs uppercase tracking-wider w-12 bg-slate-100/70 text-slate-500">No</th>
                                    <th class="px-3 py-2 text-xs uppercase tracking-wider whitespace-nowrap bg-sky-100/70 border-l border-sky-200 text-sky-700">Code</th>
                                    <th class="px-3 py-2 text-xs uppercase tracking-wider bg-indigo-100/60 border-l border-indigo-200 text-indigo-700">Product Name</th>
                                    <th class="px-2.5 py-2 text-center text-xs uppercase tracking-wider whitespace-nowrap bg-violet-100/80 border-l border-violet-200 text-violet-900">Pack / Qty</th>
                                    <th class="px-3 py-2 text-center text-xs uppercase tracking-wider whitespace-nowrap bg-blue-100/80 border-l border-blue-200 text-blue-900" style="min-width:180px;">Stok Awal</th>
                                    @if($canManage)
                                    <th class="px-2.5 py-2 text-center text-xs uppercase tracking-wider whitespace-nowrap bg-slate-100/80 border-l border-slate-200 text-slate-600">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($productItems as $i => $item)
                                <tr class="hover:bg-blue-50/60 transition-colors {{ ($item['stok_awal'] ?? 0) <= 0 ? 'bg-slate-50/60' : '' }}">
                                    @if($canManage)
                                    <td class="px-2.5 py-1.5 text-center bg-slate-50/70">
                                        <input type="checkbox" class="product-row-check h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" value="{{ $item['id'] }}" aria-label="Pilih {{ $item['nama'] }}">
                                    </td>
                                    @endif
                                    <td class="px-2.5 py-1.5 text-center text-slate-400 font-medium text-xs">{{ $i + 1 }}</td>
                                    <td class="px-3 py-1.5 whitespace-nowrap bg-sky-50/60 border-l border-sky-100">
                                        <div class="font-mono font-bold text-xs text-sky-700">{{ $item['kode'] }}</div>
                                    </td>
                                    <td class="px-3 py-1.5 bg-indigo-50/40 border-l border-indigo-100" style="max-width:260px;">
                                        <div class="font-bold text-slate-900 text-xs leading-snug truncate" title="{{ $item['nama'] }}">{{ $item['nama'] }}</div>
                                    </td>
                                    <td class="px-2.5 py-1.5 text-center whitespace-nowrap bg-violet-50/60 border-l border-violet-100">
                                        <span class="inline-flex items-center gap-1 bg-violet-100 text-violet-800 border border-violet-200 text-[11px] font-bold px-2 py-0.5 rounded-full shadow-2xs">
                                            {{ $item['kemasan_full'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-1.5 text-center whitespace-nowrap bg-blue-50/80 border-l border-blue-100" style="min-width:180px;">
                                        <div class="inline-flex items-center gap-1.5 justify-center">
                                            <span class="font-black text-xs {{ ($item['stok_awal'] ?? 0) <= 0 ? 'text-slate-400' : 'text-blue-900' }}">
                                                {{ number_format($item['stok_awal'] ?? 0, 0, ',', '.') }}
                                                <span class="text-[10px] font-bold text-blue-700/70">{{ $item['satuan'] }}</span>
                                            </span>
                                            @if(!empty($item['has_opname']))
                                                <span class="inline-flex items-center gap-0.5 text-[9px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded-full shadow-2xs" title="Produk sudah pernah di-opname (koreksi stok lewat Stock Opname)">
                                                    <i class="fas fa-clipboard-check text-[8px]"></i> Opnamed
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-0.5 text-[9px] font-medium text-slate-500 bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded-full shadow-2xs" title="Produk baru (stok awal dapat diedit)">
                                                    <i class="fas fa-tag text-[8px]"></i> Stok Awal
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    @if($canManage)
                                    <td class="px-2.5 py-1.5 text-center whitespace-nowrap bg-slate-50/70 border-l border-slate-200">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <x-ui.edit-button title="Ubah" data-product-id="{{ $item['id'] }}" data-item="{{ json_encode($item) }}" onclick="openProductEdit(this)" class="!w-6 !h-6 !text-[10px]" />
                                            <x-ui.delete-button title="Hapus" data-item="{{ json_encode($item) }}" onclick="openProductDelete(this)" class="!w-6 !h-6 !text-[10px]" />
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $canManage ? 7 : 5 }}" class="p-0">
                                        <x-ui.empty icon="fa-inbox" title="No data" message="No products yet. Click Add Product to create one." />
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-1.5 border-t border-slate-100 bg-slate-50/60 flex items-center justify-end gap-3 shrink-0">
                        <span class="text-[11px] text-slate-400 font-semibold whitespace-nowrap leading-none">Total SKU: <b class="text-slate-700">{{ number_format($productItems->count(), 0, ',', '.') }}</b></span>
                    </div>
                </x-ui.glass-card>
            </div>
