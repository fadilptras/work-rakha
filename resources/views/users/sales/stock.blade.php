@php
    $mappedItems = $items->map(function($item) {
        return [
            'id' => $item->id,
            'kode' => $item->kode_barang ?? '-',
            'nama' => $item->nama_barang,
            'kategori' => $item->satuan ? 'Alkes / ' . $item->satuan : 'Alkes',
            'po' => $item->stok_po,
            'stokSistem' => $item->stok,
            'satuan' => $item->satuan ?? 'Pcs',
        ];
    });
@endphp
<x-layout-users title="Monitoring Stock Barang">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        body { background-color: #ede9fe; }
        .mesh-bg { 
            background-color: #ede9fe;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
        }
        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1.25rem; padding: 1rem 1.5rem; color: white;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3); position: relative; overflow: hidden;
        }
        .page-header::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(30deg); pointer-events: none;
        }
        .header-content { position: relative; z-index: 1; }
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px 6px 6px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9); border-radius: 9999px;
            color: #1e293b; font-size: 0.8rem; font-weight: 700;
            text-decoration: none; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 0; width: fit-content;
        }
        .btn-back-modern:hover { 
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px); color: #1d4ed8;
        }
        .btn-back-modern .icon-circle {
            width: 26px; height: 26px; background: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6; font-size: 0.75rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover .icon-circle {
            transform: translateX(-3px); background: #EFF6FF;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 1.5rem;
            padding: 1.5rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        }
        .table-header { background: #f8fafc; color: #475569; font-weight: 800; }
        .row-item { background: #ffffff; color: #1e293b; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        .row-item:hover { background: #f1f5f9; }
        .status-badge {
            padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;
            display: inline-flex; align-items: center; gap: 0.25rem;
        }
        .status-aman { background-color: #dcfce7; color: #166534; }
        .status-menipis { background-color: #fef08a; color: #854d0e; }
        .status-kosong { background-color: #fee2e2; color: #991b1b; }
    </style>
    @endpush

    <div class="min-h-screen mesh-bg pb-12" x-data="stockManager()">
        <div class="p-4 sm:p-6 lg:p-10 w-full max-w-7xl mx-auto space-y-4 relative z-10">
            
            <!-- Alert Messages -->
            @if (session('success'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl relative shadow-md" role="alert">
                    <span class="block sm:inline font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-xl relative shadow-md" role="alert">
                    <span class="block sm:inline font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Header -->
            <div class="page-header flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="header-content">
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2">Pantau Stok Barang</h1>
                    <p class="text-blue-100 text-sm md:text-base opacity-90 max-w-2xl font-medium">Lihat dan sesuaikan jumlah stok barang</p>
                </div>
                <a href="{{ route('sales.index') }}" class="btn-back-modern shrink-0 mb-0 self-end md:self-auto">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    Kembali ke Dashboard
                </a>
            </div>

            @if($canManageStock)
            <!-- Sync Center -->
            <div class="glass-panel border-t-4 border-t-blue-500 transition-all duration-300" x-data="{ showSync: false }">
                <!-- Header (Clickable Toggle) -->
                <div class="flex items-center justify-between mb-0 cursor-pointer group" @click="showSync = !showSync">
                    <div class="flex-1 flex items-start gap-3">
                        <i class="fas fa-cloud-upload-alt text-blue-500 text-lg mt-0.5"></i>
                        <div class="flex flex-col justify-center">
                            <h3 class="text-base font-black text-slate-700 uppercase tracking-wide group-hover:text-blue-600 transition-colors">
                                Update Stok via Excel (Unggah Harian)
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5" x-show="!showSync">Klik untuk mengunggah file Excel atau melihat riwayat upload.</p>
                        </div>
                    </div>
                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition-transform duration-300 shrink-0">
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="{ 'rotate-180': showSync }"></i>
                    </button>
                </div>

                <!-- Content (Collapsible) -->
                <div x-show="showSync" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="mt-6 pt-6 border-t border-slate-100">
                    <div class="grid grid-cols-1 {{ $canManageStock ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-6 items-stretch">
                        @if($canManageStock)
                        <!-- Drag & Drop Zone Form -->
                        <form action="{{ route('sales.stock.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col h-full">
                            @csrf
                            <div class="flex-1 relative border-2 border-dashed border-emerald-200 rounded-2xl bg-emerald-50/50 hover:bg-emerald-50 transition-colors flex flex-col items-center justify-center text-center cursor-pointer min-h-[140px]">
                                <input type="file" name="file" accept=".xlsx, .xls, .csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required onchange="document.getElementById('fileNameStock').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih';">
                                <i class="fas fa-file-excel text-4xl text-emerald-400 mb-2"></i>
                                <p id="fileNameStock" class="text-sm font-bold text-slate-600 truncate px-2">Pilih atau tarik file Excel ke sini</p>
                            </div>
                            <button type="submit" class="w-full mt-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-md hover:shadow-emerald-500/40 flex justify-center items-center">
                                <i class="fas fa-upload mr-2"></i> Unggah File Excel
                            </button>
                            <button type="button" @click="openBulkModal()" class="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-md hover:shadow-blue-500/40 flex justify-center items-center">
                                <i class="fas fa-sliders-h mr-2"></i> Edit Stok Manual (Semua)
                            </button>
                        </form>
                        @endif

                        <!-- Log History -->
                        <div class="{{ $canManageStock ? 'lg:col-span-2' : '' }} flex flex-col h-full">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-bold text-slate-700 text-sm flex items-center">Riwayat Unggah Terakhir</h3>
                                <a href="{{ route('sales.stock.template') }}" class="text-xs text-blue-600 font-bold hover:underline"><i class="fas fa-download mr-1"></i> Template Excel</a>
                            </div>
                            <div class="overflow-hidden border border-slate-100 rounded-xl bg-white shadow-sm flex-1 flex flex-col">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-slate-50">
                                        <tr class="text-[10px] text-slate-500 border-b border-slate-200 tracking-wider">
                                            <th class="px-4 py-3 font-bold uppercase">Tanggal & Waktu</th>
                                            <th class="px-4 py-3 font-bold uppercase hidden sm:table-cell">Editor</th>
                                            <th class="px-4 py-3 font-bold uppercase text-right">Barang Diperbarui</th>
                                            <th class="px-4 py-3 font-bold uppercase text-center">Status</th>
                                            <th class="px-4 py-3 font-bold uppercase text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($logs as $log)
                                            <tr class="hover:bg-slate-50/50 cursor-pointer transition-colors" @click="showLogDetails({{ $log->id }})">
                                                <td class="px-4 py-3 text-slate-700 font-medium">
                                                    {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                                </td>
                                                <td class="px-4 py-3 text-slate-600 hidden sm:table-cell">{{ $log->user->name ?? 'System' }}</td>
                                                <td class="px-4 py-3 text-emerald-600 font-black text-right">{{ $log->items_count }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    @if ($log->status === 'success')
                                                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-md">Sukses</span>
                                                    @elseif ($log->status === 'undone')
                                                        <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-1 rounded-md">Dibatalkan</span>
                                                    @else
                                                        <span class="bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-1 rounded-md">Gagal</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-right" @click.stop>
                                                    @if ($log->status === 'success')
                                                        <button type="button" @click="undoLog({{ $log->id }})" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 hover:bg-red-100 px-2 py-1.5 rounded transition-colors">
                                                            <i class="fas fa-undo"></i> Batalkan
                                                        </button>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-3 text-center text-slate-500 font-medium">Belum ada riwayat pembaruan stok.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Content -->
            <div class="glass-panel border-t-4 border-t-blue-500">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-list text-blue-500"></i> Daftar Stok Barang</h2>
                        <p class="text-xs text-slate-500 font-medium mt-1"><i class="fas fa-clock text-slate-400 mr-1"></i> Terakhir diperbarui: @if($logs->first()) {{ $logs->first()->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} @else - @endif</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                        <!-- Date Filter -->
                        <div class="relative w-full sm:w-auto">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-slate-400"></i>
                            </div>
                            <input type="date" x-model="filterDate" @change="fetchStockByDate()" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-3 py-2 shadow-sm transition-colors w-full sm:w-44 font-medium">
                        </div>
                        <!-- Search Box -->
                        <div class="relative w-full sm:w-auto">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400"></i>
                            </div>
                            <input type="text" x-model="searchQuery" placeholder="Cari barang..." class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-8 px-3 py-2 shadow-sm transition-colors sm:w-96">
                            <button type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                        @if($canManageStock)
                        <a href="{{ route('sales.stock.export') }}" class="bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                            <i class="fas fa-file-export"></i> Export Excel
                        </a>
                        @endif
                    </div>
                </div>

                <div class="overflow-auto max-h-[600px] border border-slate-200 rounded-xl shadow-sm" @scroll.passive="if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 150) { limit += 50; }">
                    <table class="w-full text-xs text-left">
                        <thead class="table-header sticky top-0 z-10 shadow-sm bg-slate-50/80 backdrop-blur-sm">
                            <tr>
                                <th class="px-3 py-2.5 text-[10px] uppercase tracking-wider whitespace-nowrap hidden md:table-cell">Kode</th>
                                <th class="px-3 py-2.5 text-[10px] uppercase tracking-wider">Nama Barang</th>
                                <th class="px-3 py-2.5 text-center text-[10px] uppercase tracking-wider text-blue-600 w-20 md:w-32 whitespace-nowrap">
                                    <span class="hidden md:inline">Sedang Dikirim (PO)</span>
                                    <span class="md:hidden">PO</span>
                                </th>
                                <th class="px-3 py-2.5 text-center text-[10px] uppercase tracking-wider w-20 md:w-28 whitespace-nowrap">
                                    <span class="hidden md:inline">Stok Tersedia</span>
                                    <span class="md:hidden">Stok</span>
                                </th>
                                <th class="px-3 py-2.5 text-center text-[10px] uppercase tracking-wider whitespace-nowrap hidden md:table-cell">Satuan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white transition-opacity duration-200" :class="{'opacity-40 pointer-events-none': isLoadingHistory}">
                            <template x-for="item in displayedItems" :key="item.id">
                                <tr class="row-item hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2 text-slate-500 whitespace-nowrap hidden md:table-cell" x-text="item.kode"></td>
                                    <td class="px-3 py-2 font-bold text-slate-800 break-words max-w-sm">
                                        <div class="text-[9px] font-bold text-blue-500 uppercase md:hidden mb-0.5" x-text="item.kode"></div>
                                        <span class="text-xs" x-text="item.nama"></span>
                                    </td>
                                    <td class="px-3 py-2 text-center text-blue-500 font-medium w-20 md:w-32 whitespace-nowrap">
                                        <span class="text-xs" x-text="item.po"></span>
                                        <div class="text-[9px] text-slate-400 md:hidden font-normal" x-text="item.satuan"></div>
                                    </td>
                                    <td class="px-3 py-2 text-center w-20 md:w-28 whitespace-nowrap">
                                        <span class="font-black text-sm" :class="{'text-rose-600': item.stokSistem === 0}" x-text="item.stokSistem"></span>
                                        <div class="text-[9px] text-slate-400 md:hidden" x-text="item.satuan"></div>
                                    </td>
                                    <td class="px-3 py-2 text-center text-slate-600 font-medium whitespace-nowrap hidden md:table-cell" x-text="item.satuan"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bulk Update Manual Modal -->
        <div x-show="showBulkModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="closeBulkModal()" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden transform transition-all flex flex-col" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800"><i class="fas fa-sliders-h text-blue-500 mr-2"></i> Edit Stok Barang</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Ubah jumlah stok asli untuk semua barang sekaligus.</p>
                    </div>
                    <button @click="closeBulkModal()" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                 <!-- Modal Body (Scrollable) -->
                <div class="p-6 overflow-y-auto space-y-4 flex-1" @scroll.passive="if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 150) { bulkLimit += 50; }">
                    <!-- Search inside modal -->
                    <div class="relative w-full max-w-md">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400 text-sm"></i>
                        </div>
                        <input type="text" x-model="bulkSearchQuery" placeholder="Cari barang di modal..." class="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-8 px-3 py-2 shadow-sm transition-colors">
                        <button type="button" x-show="bulkSearchQuery.length > 0" @click="bulkSearchQuery = ''" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>

                    <div class="overflow-hidden border border-slate-200 rounded-xl bg-white shadow-sm">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-xs uppercase tracking-wider">Barang</th>
                                    <th class="px-4 py-3 text-center text-xs uppercase tracking-wider w-32">Stok di Sistem</th>
                                    <th class="px-4 py-3 text-center text-xs uppercase tracking-wider w-40">Stok Baru</th>
                                    <th class="px-4 py-3 text-center text-xs uppercase tracking-wider w-48">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(tempItem, index) in displayedTempItems" :key="tempItem.id">
                                    <tr class="hover:bg-slate-50/50 transition-colors" :class="{'bg-blue-50/30': tempItem.stokBaru !== tempItem.stokSistem && tempItem.stokBaru !== ''}">
                                        <!-- Barang Info -->
                                        <td class="px-4 py-3 whitespace-normal">
                                            <div class="text-[10px] font-bold text-blue-500 uppercase" x-text="tempItem.kode"></div>
                                            <div class="text-sm font-bold text-slate-800" x-text="tempItem.nama"></div>
                                            <div class="text-xs text-slate-400 mt-0.5" x-text="tempItem.kategori"></div>
                                        </td>
                                        <!-- Stok Sistem -->
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-sm font-black text-slate-600" x-text="tempItem.stokSistem"></span>
                                            <span class="text-xs text-slate-400 ml-0.5" x-text="tempItem.satuan"></span>
                                        </td>
                                        <!-- Input Stok Baru -->
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <input type="number" x-model.number="tempItem.stokBaru" class="w-20 text-center text-sm font-black text-blue-700 border border-slate-300 rounded-md focus:border-blue-500 focus:ring-0 px-1 py-1 bg-slate-50/50 focus:bg-white transition-colors" placeholder="0">
                                                <span class="text-xs font-bold text-slate-500" x-text="tempItem.satuan"></span>
                                            </div>
                                        </td>
                                        <!-- Selisih -->
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center">
                                                <!-- Selisih Label -->
                                                <div x-show="getTempSelisih(tempItem) !== null && tempItem.stokBaru !== ''" class="text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1" :class="{
                                                    'bg-slate-100 text-slate-500': getTempSelisih(tempItem) === 0,
                                                    'bg-rose-100 text-rose-700': getTempSelisih(tempItem) < 0,
                                                    'bg-emerald-100 text-emerald-700': getTempSelisih(tempItem) > 0
                                                }">
                                                    <i class="fas" :class="{
                                                        'fa-check': getTempSelisih(tempItem) === 0,
                                                        'fa-arrow-down': getTempSelisih(tempItem) < 0,
                                                        'fa-arrow-up': getTempSelisih(tempItem) > 0
                                                    }"></i>
                                                    <span x-text="getTempSelisihText(tempItem)"></span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 shrink-0">
                    <button @click="closeBulkModal()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg text-sm font-bold hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button @click="saveBulkUpdates()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-500/30 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Semua Perubahan
                    </button>
                </div>
            </div>
        </div>

        <!-- Log Details Modal -->
        <div x-show="showLogDetailsModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showLogDetailsModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-hidden transform transition-all flex flex-col" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
                <!-- Modal Header -->
                <div class="px-4 py-2.5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                    <div>
                        <h3 class="text-base font-bold text-slate-800"><i class="fas fa-info-circle text-blue-500 mr-1.5"></i> Rincian Pembaruan Stok</h3>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5" x-text="'Informasi riwayat log pembaruan #' + (selectedLog ? selectedLog.id : '')"></p>
                    </div>
                    <button @click="showLogDetailsModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-4 overflow-y-auto space-y-3 flex-1" x-show="selectedLog">
                    <!-- Log Info Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase text-[10px] tracking-wider">Waktu</span>
                            <span class="font-bold text-slate-700 mt-0.5 block" x-text="selectedLog ? selectedLog.tanggal : ''"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase text-[10px] tracking-wider">Editor</span>
                            <span class="font-bold text-slate-700 mt-0.5 block" x-text="selectedLog ? selectedLog.editor : ''"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase text-[10px] tracking-wider">Sumber</span>
                            <span class="font-bold text-slate-700 mt-0.5 block" x-text="selectedLog ? selectedLog.sumber : ''"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase text-[10px] tracking-wider">File Excel</span>
                            <span class="font-bold text-slate-700 mt-0.5 block truncate" :title="selectedLog ? selectedLog.nama_file : ''" x-text="selectedLog ? selectedLog.nama_file : ''"></span>
                        </div>
                    </div>

                    <!-- Search Box -->
                    <div class="flex items-center">
                        <div class="relative w-64">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400 text-xs"></i>
                            </div>
                            <input type="text" x-model="logDetailsSearchQuery" placeholder="Cari barang di rincian..." class="bg-white border border-slate-200 text-slate-700 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-8 py-1.5 shadow-sm transition-colors">
                            <button type="button" x-show="logDetailsSearchQuery.length > 0" @click="logDetailsSearchQuery = ''" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fas fa-times-circle text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Items Updated Table -->
                    <div class="overflow-y-auto max-h-80 border border-slate-200 rounded-xl bg-white shadow-sm">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5 text-[10px] uppercase tracking-wider">Nama Barang</th>
                                    <th class="px-4 py-2.5 text-center text-[10px] uppercase tracking-wider w-36 leading-tight whitespace-nowrap">Stok<br><span class="text-[9px] text-slate-400 font-medium lowercase">(lama &rarr; baru)</span></th>
                                    <th class="px-4 py-2.5 text-center text-[10px] uppercase tracking-wider w-36 leading-tight whitespace-nowrap">Stok PO<br><span class="text-[9px] text-slate-400 font-medium lowercase">(lama &rarr; baru)</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="detail in filteredSelectedLogDetails" :key="detail.kode + detail.nama">
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-4 py-2 whitespace-normal">
                                            <div class="text-[9px] font-bold text-blue-500 uppercase" x-text="detail.kode"></div>
                                            <div class="text-xs font-bold text-slate-800 leading-normal" x-text="detail.nama"></div>
                                        </td>
                                        <td class="px-4 py-2 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5 text-xs text-slate-700">
                                                <span class="text-slate-400 font-medium" x-text="detail.old_stok"></span>
                                                <span class="text-slate-300 font-medium">&rarr;</span>
                                                <span class="font-bold" :class="{
                                                    'text-emerald-600': detail.new_stok > detail.old_stok,
                                                    'text-rose-600': detail.new_stok < detail.old_stok,
                                                    'text-slate-700': detail.new_stok === detail.old_stok
                                                }" x-text="detail.new_stok"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5 text-xs text-slate-700">
                                                <span class="text-slate-400 font-medium" x-text="detail.old_stok_po"></span>
                                                <span class="text-slate-300 font-medium">&rarr;</span>
                                                <span class="font-bold" :class="{
                                                    'text-emerald-600': detail.new_stok_po > detail.old_stok_po,
                                                    'text-rose-600': detail.new_stok_po < detail.old_stok_po,
                                                    'text-slate-700': detail.new_stok_po === detail.old_stok_po
                                                }" x-text="detail.new_stok_po"></span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex justify-between items-center shrink-0" x-show="selectedLog">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500">Status: </span>
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded-md" :class="{
                            'bg-emerald-100 text-emerald-700': selectedLog && selectedLog.status === 'success',
                            'bg-amber-100 text-amber-700': selectedLog && selectedLog.status === 'undone'
                        }" x-text="selectedLog ? (selectedLog.status === 'success' ? 'Aktif' : 'Dibatalkan') : ''"></span>
                    </div>
                    <div class="flex gap-2.5">
                        <button @click="showLogDetailsModal = false" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-50 transition-colors">
                            Tutup
                        </button>
                        <button type="button" x-show="selectedLog && selectedLog.status === 'success'" @click="undoLog(selectedLog.id); showLogDetailsModal = false" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700 transition-colors shadow-sm shadow-red-500/30 flex items-center gap-1.5">
                            <i class="fas fa-undo"></i> Batalkan Perubahan (Undo)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('stockManager', () => ({
                 showBulkModal: false,
                showLogDetailsModal: false,
                searchQuery: '',
                bulkSearchQuery: '',
                filterDate: new Date().toLocaleDateString('sv-SE', {timeZone: 'Asia/Jakarta'}), // Get YYYY-MM-DD local time format
                isLoadingHistory: false,
                limit: 50,
                bulkLimit: 50,
                items: {!! json_encode($mappedItems) !!},
                tempItems: [],
                selectedLog: null,
                selectedLogDetails: [],
                logDetailsSearchQuery: '',

                init() {
                    this.$watch('searchQuery', () => {
                        this.limit = 50;
                    });
                    this.$watch('bulkSearchQuery', () => {
                        this.bulkLimit = 50;
                    });
                },

                async fetchStockByDate() {
                    this.isLoadingHistory = true;
                    try {
                        const response = await fetch(`/sales/stock/data?date=${this.filterDate}`);
                        const data = await response.json();
                        if (data.success) {
                            this.items = this.items.map(item => {
                                const history = data.history[item.id];
                                return {
                                    ...item,
                                    stokSistem: history ? history.stok : 0,
                                    po: history ? history.stok_po : 0
                                };
                            });
                            this.limit = 50;
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.isLoadingHistory = false;
                    }
                },

                get filteredItems() {
                    if (this.searchQuery.trim() === '') {
                        return this.items;
                    }
                    return this.items.filter(item => 
                        item.nama.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        item.kode.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                },

                get displayedItems() {
                    return this.filteredItems.slice(0, this.limit);
                },

                get filteredTempItems() {
                    if (this.bulkSearchQuery.trim() === '') {
                        return this.tempItems;
                    }
                    return this.tempItems.filter(item => 
                        item.nama.toLowerCase().includes(this.bulkSearchQuery.toLowerCase()) ||
                        item.kode.toLowerCase().includes(this.bulkSearchQuery.toLowerCase())
                    );
                },

                get displayedTempItems() {
                    return this.filteredTempItems.slice(0, this.bulkLimit);
                },

                get filteredSelectedLogDetails() {
                    if (this.logDetailsSearchQuery.trim() === '') {
                        return this.selectedLogDetails;
                    }
                    return this.selectedLogDetails.filter(detail => 
                        detail.nama.toLowerCase().includes(this.logDetailsSearchQuery.toLowerCase()) ||
                        detail.kode.toLowerCase().includes(this.logDetailsSearchQuery.toLowerCase())
                    );
                },

                openBulkModal() {
                    this.bulkSearchQuery = '';
                    this.tempItems = this.items.map(item => ({
                        ...item,
                        stokBaru: item.stokSistem
                    }));
                    this.showBulkModal = true;
                },

                closeBulkModal() {
                    this.showBulkModal = false;
                },

                getTempSelisih(tempItem) {
                    if (tempItem.stokBaru === '' || isNaN(tempItem.stokBaru)) return null;
                    return parseInt(tempItem.stokBaru) - tempItem.stokSistem;
                },

                getTempSelisihText(tempItem) {
                    const selisih = this.getTempSelisih(tempItem);
                    if (selisih === null) return '';
                    if (selisih === 0) return 'Sesuai';
                    if (selisih > 0) return `+${selisih}`;
                    return `${selisih}`;
                },

                saveBulkUpdates() {
                    const changedItems = this.tempItems.filter(tempItem => {
                        const newStock = (tempItem.stokBaru === '' || isNaN(tempItem.stokBaru)) ? tempItem.stokSistem : parseInt(tempItem.stokBaru);
                        return newStock !== tempItem.stokSistem;
                    });

                    if (changedItems.length === 0) {
                        this.closeBulkModal();
                        return;
                    }

                    const updates = changedItems.map(tempItem => {
                        return {
                            id: tempItem.id,
                            stok: parseInt(tempItem.stokBaru)
                        };
                    });

                    fetch('{{ route('sales.stock.update_bulk') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ updates })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal menyimpan perubahan');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan koneksi');
                    });
                },

                undoLog(logId) {
                    if (confirm('Apakah Anda yakin ingin membatalkan log perubahan ini?')) {
                        fetch(`/sales/stock/undo/${logId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert(data.message || 'Gagal membatalkan log');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan koneksi');
                        });
                    }
                },

                showLogDetails(logId) {
                    this.logDetailsSearchQuery = '';
                    fetch(`/sales/stock/log/${logId}/details`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.selectedLog = data.log;
                            this.selectedLogDetails = data.details;
                            this.showLogDetailsModal = true;
                        } else {
                            alert(data.message || 'Gagal mengambil data detail log');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan koneksi');
                    });
                }
            }));
        });
    </script>
    @endpush
</x-layout-users>
