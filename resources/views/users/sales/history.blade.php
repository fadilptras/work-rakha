@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Riwayat Data Sales' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @push('styles')
    <style>
        /* == Background == */
        body, html {
            background-color: #f8fafc;
            min-height: 100vh;
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
        }

        /* == Header Style == */
        .page-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 1.5rem;
            padding: 2.5rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
        }
        .page-header::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(30deg);
            pointer-events: none;
        }
        .header-content {
            position: relative;
            z-index: 1;
        }

        /* == Cards == */
        .glass-card {
            background: white;
            border-radius: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            padding: 1.5rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        /* == Forms == */
        .modern-input {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            color: #334155;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .modern-input:focus {
            background: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* == Button == */
        .btn-primary {
            background: #3b82f6;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 6px 10px -1px rgba(59, 130, 246, 0.4);
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-edit {
            background: #f59e0b;
            color: white;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .btn-edit:hover {
            background: #d97706;
        }
    </style>
    @endpush

    <div class="main-container" x-data="salesData()">
        {{-- Pesan Sukses / Error --}}
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl mr-2"></i>
                    <h3 class="text-sm font-bold text-red-800">Terdapat kesalahan:</h3>
                </div>
                <ul class="list-disc list-inside text-sm text-red-700 ml-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="page-header flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="header-content">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2">Riwayat & Kelola Sales</h1>
                <p class="text-blue-100 text-sm md:text-base opacity-90 max-w-2xl font-medium">Lihat, cari, ubah, atau hapus seluruh data sales yang telah tersimpan di sistem.</p>
            </div>
            <div class="header-content flex flex-wrap gap-2">
                <a href="{{ route('sales.visualisasi') }}" class="bg-sky-500 hover:bg-sky-600 text-white font-bold py-2.5 px-5 rounded-xl transition-all shadow-lg flex items-center text-sm">
                    <i class="fas fa-chart-pie mr-2"></i> Power BI Visualisasi
                </a>
                <a href="{{ route('sales.index') }}" class="bg-white/20 hover:bg-white/30 text-white border border-white/40 font-bold py-2.5 px-5 rounded-xl transition-all shadow-lg flex items-center backdrop-blur-sm text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Filter & Search Form --}}
        <div class="glass-card mb-6 relative z-10 overflow-visible p-4">
            <form action="{{ route('sales.history') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 xl:grid-cols-12 gap-3 items-end">
                    
                    {{-- Pencarian --}}
                    <div class="xl:col-span-3 lg:col-span-2 md:col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian Umum</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400 text-xs"></i>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, dsb..." class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-8 pr-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                        </div>
                    </div>

                    {{-- Filter Customer --}}
                    <div class="xl:col-span-2 lg:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Customer</label>
                        <select name="nama_customer" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                            <option value="">Semua Customer</option>
                            @foreach($listCustomer as $customer)
                                <option value="{{ $customer }}" {{ request('nama_customer') == $customer ? 'selected' : '' }}>{{ $customer }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Produk --}}
                    <div class="xl:col-span-2 lg:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Produk</label>
                        <select name="nama_produk" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                            <option value="">Semua Produk</option>
                            @foreach($listProduk as $produk)
                                <option value="{{ $produk }}" {{ request('nama_produk') == $produk ? 'selected' : '' }}>{{ $produk }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter PS --}}
                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">PS</label>
                        <select name="ps" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-1 lg:px-2 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                            <option value="">Semua</option>
                            @foreach($listPs as $ps)
                                <option value="{{ $ps }}" {{ request('ps') == $ps ? 'selected' : '' }}>{{ $ps }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Filter Bulan --}}
                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bulan</label>
                        <select name="bulan" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-1 lg:px-2 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                            <option value="">Semua</option>
                            @foreach($listBulan as $bulan)
                                <option value="{{ $bulan }}" {{ request('bulan') == $bulan ? 'selected' : '' }}>{{ $bulan }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Filter Tahun --}}
                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
                        <select name="tahun" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 px-1 lg:px-2 text-sm text-slate-700 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                            <option value="">Semua</option>
                            @foreach($listTahun as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="xl:col-span-2 lg:col-span-3 md:col-span-3 flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg text-sm transition-all shadow-sm flex items-center justify-center flex-1">
                            <i class="fas fa-filter mr-1.5 text-xs"></i> Terapkan
                        </button>
                        @if(request()->hasAny(['search', 'bulan', 'tahun', 'nama_customer', 'nama_produk', 'ps']))
                            <a href="{{ route('sales.history') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 px-3 rounded-lg text-sm transition-all shadow-sm flex items-center justify-center" title="Reset Filter">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Data Table --}}
        <div class="glass-card !p-0 overflow-hidden mb-8 flex-1 flex flex-col">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl"><i class="fas fa-table"></i></div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Daftar Data</h3>
                        <p class="text-xs text-slate-500 font-semibold mt-1">Total: {{ $sales->total() }} data ditemukan.</p>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-200 font-bold tracking-wider">
                        <tr>
                            <th class="px-4 py-4 w-12 text-center">No</th>
                            <th class="px-4 py-4">Tanggal</th>
                            <th class="px-4 py-4">Customer</th>
                            <th class="px-4 py-4 text-center">PS</th>
                            <th class="px-4 py-4">Produk</th>
                            <th class="px-4 py-4">Qty</th>
                            <th class="px-4 py-4 text-right">Harga Nett</th>
                            <th class="px-4 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sales as $index => $item)
                        <tr class="hover:bg-indigo-50/50 transition-colors">
                            <td class="px-4 py-3 text-center text-slate-400 font-medium">{{ $sales->firstItem() + $index }}</td>
                            <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $item->nama_customer ?? '-' }}</td>
                            <td class="px-4 py-3 text-center font-bold text-indigo-600">{{ $item->ps ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="text-slate-800 font-medium">{{ $item->nama_produk ?? '-' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">HNA: Rp {{ number_format($item->hna, 0, ',', '.') }} | Diskon: {{ $item->diskon == floor($item->diskon) ? number_format($item->diskon, 0) : $item->diskon }}%</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap"><span class="px-2.5 py-1 bg-slate-100 rounded-lg text-xs font-bold text-slate-600">{{ $item->qty ?? 0 }} {{ $item->satuan }}</span></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap font-bold text-emerald-600">Rp {{ number_format($item->harga_nett, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" @click="openEditModal({{ json_encode($item) }})" class="btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('sales.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-search text-5xl text-slate-200 mb-4"></i>
                                    <p class="font-medium text-lg">Data tidak ditemukan.</p>
                                    <p class="text-sm mt-1">Coba sesuaikan kata kunci pencarian atau filter bulan/tahun.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($sales->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $sales->links() }}
            </div>
            @endif
        </div>

        {{-- Modal Edit --}}
        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                {{-- Backdrop --}}
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900 bg-opacity-75 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="showEditModal = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                {{-- Modal Panel --}}
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                    <form :action="editUrl" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-slate-100">
                                <h3 class="text-xl leading-6 font-bold text-slate-800" id="modal-title">Edit Data Sales</h3>
                                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Row 1 --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                                    <input type="date" name="tanggal" x-model="formData.tanggal" class="modern-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Bulan</label>
                                    <select name="bulan" x-model="formData.bulan" class="modern-input">
                                        @foreach($listBulan as $bulan)
                                            <option value="{{ $bulan }}">{{ $bulan }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Row 2 --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Customer</label>
                                    <input type="text" name="nama_customer" x-model="formData.nama_customer" class="modern-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">PS</label>
                                    <input type="text" name="ps" x-model="formData.ps" class="modern-input">
                                </div>

                                {{-- Row 3 --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Produk</label>
                                    <input type="text" name="nama_produk" x-model="formData.nama_produk" class="modern-input">
                                </div>

                                {{-- Row 4 --}}
                                <div class="grid grid-cols-2 gap-4 md:col-span-2">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Qty</label>
                                        <input type="number" name="qty" x-model="formData.qty" @input="calculateNett()" class="modern-input">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Satuan</label>
                                        <select name="satuan" x-model="formData.satuan" class="modern-input">
                                            <option value="">-- Pilih --</option>
                                            <option value="box">box</option>
                                            <option value="botol">botol</option>
                                            <option value="galon">galon</option>
                                            <option value="Jerigen">Jerigen</option>
                                            <option value="karton">karton</option>
                                            <option value="pack">pack</option>
                                            <option value="paket">paket</option>
                                            <option value="pcs">pcs</option>
                                            <option value="polybag">polybag</option>
                                            <option value="pouches">pouches</option>
                                            <option value="roll">roll</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Row 5 --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">HNA (Rp)</label>
                                    <input type="number" name="hna" x-model="formData.hna" @input="calculateNett()" step="0.01" class="modern-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Diskon (%)</label>
                                    <input type="number" name="diskon" x-model="formData.diskon" @input="calculateNett()" step="0.01" class="modern-input">
                                </div>

                                {{-- Row 6 --}}
                                <div class="md:col-span-2 mt-2">
                                    <label class="block text-xs font-bold text-indigo-600 uppercase mb-1">Harga Nett (Rp)</label>
                                    <input type="text" x-bind:value="formatRupiah(formData.harga_nett)" class="modern-input !bg-indigo-50 !border-indigo-200 font-bold text-lg" readonly>
                                    <input type="hidden" name="harga_nett" x-model="formData.harga_nett">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                                Simpan Perubahan
                            </button>
                            <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function salesData() {
            return {
                showEditModal: false,
                editUrl: '',
                formData: {
                    id: '',
                    tanggal: '',
                    bulan: '',
                    nama_customer: '',
                    ps: '',
                    nama_produk: '',
                    qty: 0,
                    satuan: '',
                    hna: 0,
                    diskon: 0,
                    harga_nett: 0
                },
                openEditModal(item) {
                    this.formData = { ...item };
                    // Format tanggal for input type="date"
                    if(this.formData.tanggal) {
                        this.formData.tanggal = this.formData.tanggal.split('T')[0];
                    }
                    this.editUrl = `{{ url('sales') }}/${item.id}`;
                    this.showEditModal = true;
                },
                calculateNett() {
                    const qty = parseFloat(this.formData.qty) || 0;
                    const hna = parseFloat(this.formData.hna) || 0;
                    const diskon = parseFloat(this.formData.diskon) || 0;
                    
                    const subtotal = qty * hna;
                    const diskonNominal = subtotal * (diskon / 100);
                    this.formData.harga_nett = subtotal - diskonNominal;
                },
                formatRupiah(number) {
                    if(!number && number !== 0) return '';
                    return new Intl.NumberFormat('id-ID').format(Math.round(number));
                }
            }
        }
    </script>
    @endpush
</x-layout-users>
