<x-layout-users title="Riwayat Pembaruan Stok">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .swal2-container { z-index: 100000 !important; }
        body { background-color: #ede9fe; }
        .mesh-bg { 
            background-color: #ede9fe;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
        }

        /* Modern Back Button */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 6px 16px 6px 6px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.85rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            width: fit-content;
        }
        .btn-back-modern:hover { 
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
            color: #1d4ed8;
        }
        .btn-back-modern .icon-circle {
            width: 28px; height: 28px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6;
            font-size: 0.8rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover .icon-circle {
            transform: translateX(-3px);
            background: #EFF6FF;
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

        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 1.5rem;
            padding: 1rem 1.5rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
        }

        /* Mobile Responsive */
        @media (max-width: 640px) {
            .glass-panel { padding: 0.9rem; border-radius: 1.1rem; }
        }

        /* Mobile History Cards */
        .mobile-history-card {
            background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.9rem;
            padding: 0.7rem 0.85rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
    </style>
    @endpush

    <div class="mesh-bg min-h-screen py-8 px-4 sm:px-6 lg:px-8" x-data="historyManager">
        <div class="max-w-6xl mx-auto space-y-4">
            
            <!-- Back Button -->
            <div class="w-full flex justify-start">
                <a href="{{ route('sales.stock') }}" class="btn-back-modern shrink-0">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    Kembali ke Stok
                </a>
            </div>

            <!-- Header Card -->
            <div class="glass-panel border-t-4 border-t-blue-500">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 text-lg shrink-0">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-black tracking-tight text-slate-800">Manajemen Riwayat Unggah Stok</h1>
                        <p class="text-[11px] text-slate-500 font-medium">Tinjau seluruh riwayat log pembaruan data stok sistem dan impor Accurate.</p>
                    </div>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="glass-panel">
                <form action="{{ route('sales.stock.history_index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Editor / File</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400 text-sm">
                                <i class="fas fa-search"></i>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama editor atau file..." class="bg-white border border-slate-200 text-slate-700 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-4 !py-2 shadow-sm transition-colors">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Riwayat</label>
                        <select name="status" class="bg-white border border-slate-200 text-slate-700 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 !py-2 shadow-sm transition-colors">
                            <option value="">Semua Status</option>
                            <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Sukses</option>
                            <option value="undone" {{ request('status') === 'undone' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold !py-2 px-4 rounded-xl transition-all shadow-md hover:shadow-blue-500/40 flex justify-center items-center text-xs">
                            <i class="fas fa-filter mr-2"></i> Filter
                        </button>
                        @if(request()->has('search') || request()->has('status'))
                            <a href="{{ route('sales.stock.history_index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold !py-2 px-4 rounded-xl transition-all flex justify-center items-center text-xs">
                                <i class="fas fa-sync-alt"></i> Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Mobile History Cards -->
            <div class="md:hidden space-y-2 max-h-[70vh] overflow-y-auto pb-4">
                @forelse ($logs as $log)
                    <div class="mobile-history-card cursor-pointer" @click="showLogDetails({{ $log->id }})">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-slate-800 leading-snug">{{ $log->nama_file ?? ($log->source === 'manual' ? 'Pembaruan Manual' : '-') }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
                                <div class="text-[10px] text-slate-500 font-medium mt-0.5">Editor: {{ $log->user->name ?? 'System' }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-black text-emerald-600 text-sm leading-tight">{{ $log->items_count }}</div>
                                <div class="mt-1">
                                    @if ($log->status === 'success')
                                        <span class="bg-emerald-100 text-emerald-700 text-[9px] font-black px-1.5 py-0.5 rounded-md">Sukses</span>
                                    @elseif ($log->status === 'undone')
                                        <span class="bg-amber-100 text-amber-700 text-[9px] font-black px-1.5 py-0.5 rounded-md">Dibatalkan</span>
                                    @else
                                        <span class="bg-rose-100 text-rose-700 text-[9px] font-black px-1.5 py-0.5 rounded-md">Gagal</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-sm font-medium">Belum ada riwayat log.</div>
                @endforelse
            </div>

            <!-- Desktop Table Container -->
            <div class="hidden md:flex glass-panel flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                                <th class="px-4 py-3">Tanggal & Waktu</th>
                                <th class="px-4 py-3">File / Sumber</th>
                                <th class="px-4 py-3">Editor</th>
                                <th class="px-4 py-3 text-right">Barang Diperbarui</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-slate-50/50 cursor-pointer transition-colors" @click="showLogDetails({{ $log->id }})">
                                    <td class="px-4 py-3 text-slate-700 font-bold text-xs">
                                        {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 font-semibold text-xs max-w-xs truncate" title="{{ $log->nama_file }}">
                                        {{ $log->nama_file ?? ($log->source === 'manual' ? 'Pembaruan Manual' : '-') }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 text-xs">{{ $log->user->name ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-emerald-600 font-black text-right text-xs">{{ $log->items_count }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($log->status === 'success')
                                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded-md">Sukses</span>
                                        @elseif ($log->status === 'undone')
                                            <span class="bg-amber-100 text-amber-700 text-[10px] font-black px-2 py-0.5 rounded-md">Dibatalkan</span>
                                        @else
                                            <span class="bg-rose-100 text-rose-700 text-[10px] font-black px-2 py-0.5 rounded-md">Gagal</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right" @click.stop>
                                        @if ($log->status === 'success' && $canManageStock)
                                            <button type="button" @click="undoLog({{ $log->id }})" class="text-red-600 hover:text-white font-bold text-[10px] bg-red-50 hover:bg-red-600 px-2 py-1.5 rounded-lg border border-red-100 hover:border-red-600 transition-all flex items-center gap-1 ml-auto">
                                                <i class="fas fa-undo"></i> Batalkan
                                            </button>
                                        @else
                                            <span class="text-slate-400 font-medium text-xs px-3">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-slate-400 font-medium text-sm">
                                        Belum ada riwayat log pembaruan stok.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Laravel Pagination Links -->
                <div class="mt-4 border-t border-slate-100 pt-4">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        <!-- Log Details Modal -->
        <div x-show="showLogDetailsModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showLogDetailsModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden transform transition-all flex flex-col" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                
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
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 sticky top-0 z-10">
                                <tr>
                                    <th class="px-4 py-2.5">Barang</th>
                                    <th class="px-4 py-2.5 text-center w-28">Mutasi Stok</th>
                                    <th class="px-4 py-2.5 text-center w-28">Mutasi PO</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="detail in filteredSelectedLogDetails" :key="detail.id">
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-4 py-2.5">
                                            <div class="font-extrabold text-slate-800" x-text="detail.nama"></div>
                                            <div class="text-[9px] text-slate-400 font-bold mt-0.5" x-text="detail.kode"></div>
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
            Alpine.data('historyManager', () => ({
                showLogDetailsModal: false,
                selectedLog: null,
                selectedLogDetails: [],
                logDetailsSearchQuery: '',

                get filteredSelectedLogDetails() {
                    if (this.logDetailsSearchQuery.trim() === '') {
                        return this.selectedLogDetails;
                    }
                    return this.selectedLogDetails.filter(detail => 
                        detail.nama.toLowerCase().includes(this.logDetailsSearchQuery.toLowerCase()) ||
                        detail.kode.toLowerCase().includes(this.logDetailsSearchQuery.toLowerCase())
                    );
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
                            Swal.fire({
                                title: 'Gagal!',
                                text: data.message || 'Gagal mengambil data detail log',
                                icon: 'error',
                                confirmButtonColor: '#3b82f6'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan koneksi.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    });
                },

                undoLog(logId) {
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: 'Pembaruan stok pada log ini akan dibatalkan (dikembalikan ke stok sebelum diupdate)!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Batalkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
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
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Log pembaruan stok berhasil dibatalkan!',
                                        icon: 'success',
                                        confirmButtonColor: '#3b82f6'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: data.message || 'Gagal membatalkan log',
                                        icon: 'error',
                                        confirmButtonColor: '#ef4444'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan koneksi.',
                                    icon: 'error',
                                    confirmButtonColor: '#ef4444'
                                });
                            });
                        }
                    });
                }
            }));
        });
    </script>
    @endpush
</x-layout-users>
