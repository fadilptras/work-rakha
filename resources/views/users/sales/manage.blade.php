@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Kelola Data Sales' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @push('styles')
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }

        /* == Background == */
        .mesh-bg { 
            background-color: #ede9fe;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
        }

        /* == Header Style == */
        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1.5rem; padding: 1.5rem 2.5rem; color: white; margin-bottom: 2rem;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3); position: relative; overflow: hidden;
        }
        .page-header::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(30deg); pointer-events: none;
        }
        .header-content { position: relative; z-index: 1; }

        /* == Cards & Tabs == */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            padding: 28px;
        }
        /* == Modern Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.9rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 20px;
            width: fit-content;
        }
        .btn-back-modern:hover { 
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
            color: #1d4ed8;
        }
        .btn-back-modern .icon-circle {
            width: 32px; height: 32px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6;
            font-size: 0.85rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover .icon-circle {
            transform: translateX(-3px);
            background: #EFF6FF;
        }

        .tab-btn {
            padding: 0.65rem 1.5rem; border-radius: 999px; font-weight: 700;
            color: #64748b; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 2px solid transparent;
        }
        .tab-btn:hover { color: #3b82f6; background: #f8fafc; }
        .tab-btn.active { 
            color: #ffffff; 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            border-color: transparent;
        }

        /* == Forms == */
        .modern-input {
            width: 100%; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 0.75rem;
            padding: 0.75rem 1rem; color: #334155; font-size: 0.875rem; transition: all 0.2s ease;
        }
        .modern-input:focus { background: #ffffff; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); outline: none; }
        .modern-label { display: block; font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px; }

        /* == Button == */
        .btn-primary { background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; transition: all 0.2s ease; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3); }
        .btn-primary:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 6px 10px -1px rgba(59, 130, 246, 0.4); }
        .btn-danger { background: #ef4444; color: white; padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s ease; }
        .btn-danger:hover { background: #dc2626; }
        .btn-edit { background: #f59e0b; color: white; padding: 0.5rem; border-radius: 0.5rem; transition: all 0.2s ease; }
        .btn-edit:hover { background: #d97706; }
    </style>
    @endpush

    <div class="mesh-bg flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800">
        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-12 flex-1 flex flex-col justify-start" x-data="manageData()">

        {{-- Tombol Kembali --}}
        <a href="{{ route('sales.index') }}" class="btn-back-modern">
            <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
            Kembali ke Dashboard
        </a>

        {{-- Header Halaman --}}
        <div class="page-header flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="header-content">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2">Kelola Data Sales</h1>
                <p class="text-blue-100 text-sm md:text-base opacity-90 max-w-2xl font-medium">Satu pusat untuk semua data sales. Input manual, import dari Excel, dan kelola riwayat data.</p>
            </div>
        </div>

        {{-- Navigasi Tab --}}
        <div class="flex space-x-2 mb-6 bg-white p-2 rounded-full shadow-sm border border-slate-200 overflow-x-auto">
            <button @click="activeTab = 'table'" :class="{ 'active': activeTab === 'table' }" class="tab-btn whitespace-nowrap flex items-center">
                <i class="fas fa-table mr-2"></i> Riwayat Data
            </button>
            <button @click="activeTab = 'input'" :class="{ 'active': activeTab === 'input' }" class="tab-btn whitespace-nowrap flex items-center">
                <i class="fas fa-keyboard mr-2"></i> Input Manual
            </button>
            <button @click="activeTab = 'import'" :class="{ 'active': activeTab === 'import' }" class="tab-btn whitespace-nowrap flex items-center">
                <i class="fas fa-cloud-upload-alt mr-2"></i> Import / Export
            </button>
        </div>

        {{-- [TAB 1] Tabel Riwayat Data --}}
        <div x-show="activeTab === 'table'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            {{-- Form Filter Pencarian --}}
            <div class="glass-card mb-6 relative z-10 p-4">
                <form action="{{ route('sales.manage') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 md:gap-4 items-end">
                        {{-- Pencarian --}}
                        <div class="lg:col-span-4 md:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian Umum</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 flex items-center pointer-events-none" style="left: 1rem;">
                                    <i class="fas fa-search text-slate-400 text-sm"></i>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..." class="modern-input" style="padding-left: 2.75rem;">
                            </div>
                        </div>

                        {{-- Filter Customer --}}
                        <div class="lg:col-span-4 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Customer</label>
                            <select name="nama_customer" class="modern-input">
                                <option value="">Semua Customer</option>
                                @foreach($listCustomer as $customer)
                                    <option value="{{ $customer }}" {{ request('nama_customer') == $customer ? 'selected' : '' }}>{{ $customer }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Produk --}}
                        <div class="lg:col-span-4 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Produk</label>
                            <select name="nama_produk" class="modern-input">
                                <option value="">Semua Produk</option>
                                @foreach($listProduk as $produk)
                                    <option value="{{ $produk }}" {{ request('nama_produk') == $produk ? 'selected' : '' }}>{{ $produk }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter PS --}}
                        <div class="lg:col-span-2 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">PS</label>
                            <select name="ps" class="modern-input !px-2">
                                <option value="">Semua</option>
                                @foreach($listPs as $ps)
                                    <option value="{{ $ps }}" {{ request('ps') == $ps ? 'selected' : '' }}>{{ $ps }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Filter Tanggal --}}
                        <div class="lg:col-span-3 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="modern-input !px-2 text-sm">
                        </div>

                        {{-- Filter Bulan --}}
                        <div class="lg:col-span-2 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bulan</label>
                            <select name="bulan" class="modern-input !px-2">
                                <option value="">Semua</option>
                                @foreach($listBulan as $bulan)
                                    <option value="{{ $bulan }}" {{ request('bulan') == $bulan ? 'selected' : '' }}>{{ $bulan }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Filter Tahun --}}
                        <div class="lg:col-span-2 md:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
                            <select name="tahun" class="modern-input !px-2">
                                <option value="">Semua</option>
                                @foreach($listTahun as $tahun)
                                    <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Action Buttons --}}
                        <div class="lg:col-span-3 md:col-span-2 flex gap-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg text-sm transition-all shadow-sm flex items-center justify-center flex-1">
                                <i class="fas fa-filter mr-1.5 text-xs"></i> Terapkan
                            </button>
                            @if(request()->hasAny(['search', 'tanggal', 'bulan', 'tahun', 'nama_customer', 'nama_produk', 'ps']))
                                <a href="{{ route('sales.manage') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 px-3 rounded-lg text-sm transition-all shadow-sm flex items-center justify-center w-10 shrink-0" title="Reset Filter">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Tabel Data --}}
            <div class="glass-card !p-0 overflow-hidden mb-8 flex-1 flex flex-col">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl"><i class="fas fa-table"></i></div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Daftar Data Sales</h3>
                            <p class="text-xs text-slate-500 font-semibold mt-1">Total: {{ $sales->total() }} data ditemukan.</p>
                        </div>
                    </div>
                    <div>
                        <select onchange="window.location.href=this.value" class="modern-input !py-1.5 !px-3 !w-auto text-xs font-semibold text-slate-600 bg-white border-slate-200 cursor-pointer shadow-sm rounded-lg hover:border-blue-400 transition-colors focus:ring-2 focus:ring-blue-100">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'terbaru']) }}" {{ request('sort', 'terbaru') == 'terbaru' ? 'selected' : '' }}>Urutkan: Terbaru</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'terlama']) }}" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Urutkan: Terlama</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'tertinggi']) }}" {{ request('sort') == 'tertinggi' ? 'selected' : '' }}>Penjualan Tertinggi</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'terendah']) }}" {{ request('sort') == 'terendah' ? 'selected' : '' }}>Penjualan Terendah</option>
                        </select>
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
                                        <button type="button" data-item="{{ json_encode($item) }}" @click="openEditModal(JSON.parse($el.dataset.item))" class="btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('sales.destroy', $item->id) }}" method="POST" id="form-delete-{{ $item->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete({{ $item->id }})" class="btn-danger" title="Hapus">
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
        </div>

        {{-- [TAB 2] Input Data Manual --}}
        <div x-show="activeTab === 'input'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="glass-card">
                <h3 class="text-lg font-black text-slate-800 flex items-center mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg mr-3"><i class="fas fa-keyboard"></i></div>
                    Input Data Manual
                </h3>
                <form action="{{ route('sales.store_manual') }}" method="POST" onsubmit="confirmSubmit(event, 'Simpan data sales ini?')">
                    @csrf
                    <div class="grid grid-cols-1 gap-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="modern-label">Tanggal</label>
                                <input type="date" name="tanggal" class="modern-input">
                            </div>
                            <div>
                                <label class="modern-label">Nama PS</label>
                                <input list="ps-list-options" type="text" name="ps" placeholder="Daffa" class="modern-input" autocomplete="off">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="modern-label">Nama Customer <span class="text-red-500">*</span></label>
                                <input list="customer-list-options" type="text" name="nama_customer" required placeholder="Nama Customer" class="modern-input" autocomplete="off">
                            </div>
                            <div>
                                <label class="modern-label">Nama Produk <span class="text-red-500">*</span></label>
                                <input list="produk-list-options" type="text" name="nama_produk" required placeholder="Nama Barang" class="modern-input" autocomplete="off">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="modern-label">Qty</label>
                                    <input type="number" id="input-qty" name="qty" min="1" placeholder="0" class="modern-input">
                                </div>
                                <div>
                                    <label class="modern-label">Satuan</label>
                                    <input list="satuan-list-options" type="text" name="satuan" placeholder="Pcs/Box" class="modern-input" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="modern-label">HNA (Rp)</label>
                                <input type="text" id="display-hna" placeholder="0" class="modern-input">
                                <input type="hidden" id="input-hna" name="hna" value="0">
                            </div>
                            <div>
                                <label class="modern-label">Diskon (%)</label>
                                <input type="number" id="input-diskon" name="diskon" min="0" step="0.01" placeholder="0" class="modern-input">
                            </div>
                            <div>
                                <label class="modern-label text-indigo-700">Harga Nett (Rp)</label>
                                <input type="text" id="display-harga-nett" placeholder="0" class="modern-input font-bold !bg-indigo-50 !border-indigo-200" readonly>
                                <input type="hidden" id="input-harga-nett" name="harga_nett" value="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="btn-primary flex items-center">
                            <i class="fas fa-save mr-2"></i> Simpan Data Manual
                        </button>
                    </div>
                </form>

                {{-- Datalist Options dari Database --}}
                <datalist id="ps-list-options">
                    @foreach($listPs as $ps_item)
                        <option value="{{ $ps_item }}"></option>
                    @endforeach
                </datalist>
                <datalist id="customer-list-options">
                    @foreach($listCustomer as $cus)
                        <option value="{{ $cus }}"></option>
                    @endforeach
                </datalist>
                <datalist id="produk-list-options">
                    @foreach($listProduk as $prod)
                        <option value="{{ $prod }}"></option>
                    @endforeach
                </datalist>
                <datalist id="satuan-list-options">
                    @foreach($listSatuan as $sat)
                        <option value="{{ $sat }}"></option>
                    @endforeach
                </datalist>
            </div>
        </div>

        {{-- [TAB 3] Import & Export --}}
        <div x-show="activeTab === 'import'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-7 glass-card border-t-4 border-t-emerald-500 flex flex-col h-full">
                    <h3 class="text-lg font-black text-slate-800 flex items-center mb-6">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg mr-3"><i class="fas fa-cloud-upload-alt"></i></div>
                        Import dari Excel/CSV
                    </h3>
                    <form action="{{ route('sales.import_excel') }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col" onsubmit="confirmSubmit(event, 'Apakah Anda yakin ingin mengimpor data dari file ini?')">
                        @csrf
                        <div class="mb-6 flex-1 flex flex-col">
                            <label class="modern-label mb-2">Pilih File (.xlsx, .csv)</label>
                            <div class="flex-1 relative border-2 border-dashed border-emerald-200 rounded-2xl bg-emerald-50/50 hover:bg-emerald-50 transition-colors py-12 px-6 flex flex-col items-center justify-center text-center cursor-pointer overflow-hidden min-h-[200px]">
                                <input type="file" name="file" accept=".xlsx, .xls, .csv" required
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                    onchange="document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih';">
                                <i class="fas fa-file-excel text-4xl text-emerald-400 mb-3"></i>
                                <p id="fileName" class="text-sm font-bold text-slate-600 truncate px-2">Klik atau Drop file di sini</p>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-lg hover:shadow-emerald-500/40 flex justify-center items-center">
                            <i class="fas fa-upload mr-2"></i> Upload Data
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-5 flex flex-col gap-6">
                    <div class="glass-card bg-slate-50 border-slate-200">
                        <h4 class="font-bold text-slate-800 mb-4 flex items-center text-sm"><i class="fas fa-file-download mr-2 text-blue-500"></i> Download Template</h4>
                        <p class="text-sm text-slate-600 mb-4">Unduh template CSV kosong dengan format kolom yang sudah disesuaikan dengan sistem.</p>
                        <a href="{{ route('sales.download_template') }}" class="inline-flex items-center justify-center w-full bg-white border-2 border-blue-200 hover:border-blue-500 text-blue-600 font-bold py-3 px-4 rounded-xl transition-all shadow-sm">
                            <i class="fas fa-download mr-2"></i> Download Template CSV
                        </a>
                    </div>

                    <div class="glass-card bg-blue-50 border-blue-200">
                        <h4 class="font-bold text-blue-800 mb-3 flex items-center text-sm"><i class="fas fa-info-circle mr-2"></i> Petunjuk Import</h4>
                        <ul class="text-xs text-blue-700 space-y-2 list-disc list-inside font-medium leading-relaxed">
                            <li>Gunakan template <b>XLSX / CSV</b> terbaru yang didownload dari tombol di atas (tanpa kolom Bulan).</li>
                            <li>Pastikan format <b>Tanggal</b> valid. Sistem akan otomatis mengisi bulan.</li>
                            <li>Kolom angka (Qty, HNA, Diskon, Harga Nett) bisa <b>dibiarkan kosong</b> jika tidak ada datanya.</li>
                            <li>Maksimal ukuran file <b>10MB</b>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Edit Data --}}
        <div x-cloak x-show="showEditModal" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Background backdrop --}}
            <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 transition-opacity" style="background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);" aria-hidden="true" @click="showEditModal = false"></div>

            <div class="fixed inset-0 z-[110] w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    
                    {{-- Modal panel --}}
                    <div x-show="showEditModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full" style="max-width: 800px;">
                        
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
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="modern-label">Tanggal</label><input type="date" name="tanggal" x-model="formData.tanggal" class="modern-input"></div>
                                    <div><label class="modern-label">Customer</label><input list="customer-list-options" type="text" name="nama_customer" x-model="formData.nama_customer" class="modern-input" autocomplete="off"></div>
                                    
                                    <div><label class="modern-label">PS</label><input list="ps-list-options" type="text" name="ps" x-model="formData.ps" class="modern-input" autocomplete="off"></div>
                                    <div><label class="modern-label">Produk</label><input list="produk-list-options" type="text" name="nama_produk" x-model="formData.nama_produk" class="modern-input" autocomplete="off"></div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div><label class="modern-label">Qty</label><input type="number" name="qty" x-model="formData.qty" @input="calculateNett()" class="modern-input"></div>
                                        <div><label class="modern-label">Satuan</label><input list="satuan-list-options" type="text" name="satuan" x-model="formData.satuan" class="modern-input" autocomplete="off"></div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div><label class="modern-label">HNA (Rp)</label><input type="number" name="hna" x-model="formData.hna" @input="calculateNett()" step="0.01" class="modern-input"></div>
                                        <div><label class="modern-label">Diskon (%)</label><input type="number" name="diskon" x-model="formData.diskon" @input="calculateNett()" step="0.01" class="modern-input"></div>
                                    </div>
                                    
                                    <div class="md:col-span-2 mt-2 border-t border-slate-100 pt-4">
                                        <label class="block text-sm font-bold text-indigo-600 uppercase mb-2">Total Harga Nett (Rp)</label>
                                        <input type="text" :value="formatRupiah(formData.harga_nett)" class="modern-input !bg-indigo-50 !border-indigo-200 font-black text-2xl !py-4" readonly>
                                        <input type="hidden" name="harga_nett" x-model="formData.harga_nett">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto transition-all">
                                    Simpan Perubahan
                                </button>
                                <button type="button" @click="showEditModal = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-all">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function manageData() {
            return {
                activeTab: '{{ request()->hasAny(['search', 'bulan', 'tahun', 'nama_customer', 'nama_produk', 'ps']) ? 'table' : 'table' }}',
                showEditModal: false,
                editUrl: '',
                formData: { id: '', tanggal: '', bulan: '', nama_customer: '', ps: '', nama_produk: '', qty: 0, satuan: '', hna: 0, diskon: 0, harga_nett: 0 },
                
                openEditModal(item) {
                    this.formData = { ...item };
                    if(this.formData.tanggal) this.formData.tanggal = this.formData.tanggal.split('T')[0];
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

        // Kalkulasi Harga Nett (Form Manual)
        document.addEventListener('DOMContentLoaded', function() {
            const qtyInput = document.getElementById('input-qty');
            const hnaInput = document.getElementById('input-hna');
            const displayHna = document.getElementById('display-hna');
            const diskonInput = document.getElementById('input-diskon');
            const hargaNettInput = document.getElementById('input-harga-nett');
            const displayHargaNett = document.getElementById('display-harga-nett');

            function formatRupiah(number) { return new Intl.NumberFormat('id-ID').format(number); }
            function parseRupiah(text) {
                if (!text) return 0;
                return parseFloat(text.replace(/[^0-9,-]+/g,"").replace(',', '.')) || 0;
            }

            if (displayHna) {
                displayHna.addEventListener('input', function(e) {
                    let val = parseRupiah(this.value);
                    this.value = val ? formatRupiah(val) : '';
                    hnaInput.value = val;
                    calculateHargaNett();
                });
            }

            function calculateHargaNett() {
                const qty = parseFloat(qtyInput.value) || 0;
                const hna = parseFloat(hnaInput.value) || 0;
                const diskon = parseFloat(diskonInput.value) || 0;
                const totalAwal = hna * qty;
                const potonganDiskon = totalAwal * (diskon / 100);
                const hargaNett = totalAwal - potonganDiskon;

                if (hargaNett > 0) {
                    hargaNettInput.value = hargaNett.toFixed(2);
                    displayHargaNett.value = formatRupiah(hargaNett);
                } else {
                    hargaNettInput.value = ''; displayHargaNett.value = '';
                }
            }
            if (qtyInput) qtyInput.addEventListener('input', calculateHargaNett);
            if (diskonInput) diskonInput.addEventListener('input', calculateHargaNett);
        });

        // SweetAlert Delete Confirmation
        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Data?',
                text: "Data sales yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-delete-' + id).submit();
                }
            });
        }
    </script>
    @endpush
</x-layout-users>
