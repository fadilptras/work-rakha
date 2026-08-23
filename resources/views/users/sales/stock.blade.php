<x-layout-users title="Monitoring Stock Barang">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @push('styles')
    <style>
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

    <div class="min-h-screen mesh-bg pb-12">
        <div class="p-4 sm:p-6 lg:p-10 w-full max-w-7xl mx-auto space-y-4 relative z-10">
            <!-- Header -->
            <div class="page-header flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="header-content">
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2">Monitoring Stock Barang</h1>
                    <p class="text-blue-100 text-sm md:text-base opacity-90 max-w-2xl font-medium">Pantau ketersediaan barang (Preview Statis)</p>
                </div>
                <a href="{{ route('sales.index') }}" class="btn-back-modern shrink-0 mb-0 self-end md:self-auto">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    Kembali ke Dashboard
                </a>
            </div>

            <!-- Sync Center -->
            <div class="glass-panel border-t-4 border-t-blue-500 transition-all duration-300" x-data="{ showSync: false }">
                <!-- Header (Clickable Toggle) -->
                <div class="flex items-center justify-between mb-0 cursor-pointer group" @click="showSync = !showSync">
                    <div class="flex-1 flex items-start gap-3">
                        <i class="fas fa-cloud-upload-alt text-blue-500 text-lg mt-0.5"></i>
                        <div class="flex flex-col justify-center">
                            <h3 class="text-base font-black text-slate-700 uppercase tracking-wide group-hover:text-blue-600 transition-colors">
                                Sync Center (Upload Harian)
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5" x-show="!showSync">Klik untuk membuka area sinkronisasi dan riwayat upload.</p>
                        </div>
                    </div>
                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition-transform duration-300 shrink-0">
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="{ 'rotate-180': showSync }"></i>
                    </button>
                </div>

                <!-- Content (Collapsible) -->
                <div x-show="showSync" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="mt-6 pt-6 border-t border-slate-100">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                        <!-- Drag & Drop Zone -->
                        <div class="flex flex-col h-full">
                            <div class="flex-1 relative border-2 border-dashed border-emerald-200 rounded-2xl bg-emerald-50/50 hover:bg-emerald-50 transition-colors flex flex-col items-center justify-center text-center cursor-pointer min-h-[140px]">
                                <input type="file" name="file" accept=".xlsx, .xls, .csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="document.getElementById('fileNameStock').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih';">
                                <i class="fas fa-file-excel text-4xl text-emerald-400 mb-2"></i>
                                <p id="fileNameStock" class="text-sm font-bold text-slate-600 truncate px-2">Klik atau Drop Excel Harian di sini</p>
                            </div>
                            <button type="button" class="w-full mt-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-md hover:shadow-emerald-500/40 flex justify-center items-center">
                                <i class="fas fa-sync-alt mr-2"></i> Mulai Sinkronisasi
                            </button>
                        </div>

                        <!-- Log History -->
                        <div class="lg:col-span-2 flex flex-col h-full">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-bold text-slate-700 text-sm flex items-center"><i class="fas fa-history mr-2 text-slate-400"></i> Riwayat Sinkronisasi Terakhir</h3>
                                <button class="text-xs text-blue-600 font-bold hover:underline"><i class="fas fa-download mr-1"></i> Template XLS</button>
                            </div>
                            <div class="overflow-hidden border border-slate-100 rounded-xl bg-white shadow-sm flex-1 flex flex-col">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-slate-50">
                                        <tr class="text-[10px] text-slate-500 border-b border-slate-200 tracking-wider">
                                            <th class="px-4 py-3 font-bold uppercase">Tanggal & Waktu</th>
                                            <th class="px-4 py-3 font-bold uppercase">User</th>
                                            <th class="px-4 py-3 font-bold uppercase text-right">Barang Terupdate</th>
                                            <th class="px-4 py-3 font-bold uppercase text-center">Status</th>
                                            <th class="px-4 py-3 font-bold uppercase text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3 text-slate-700 font-medium">Hari ini, 08:15 WIB</td>
                                            <td class="px-4 py-3 text-slate-600">Admin</td>
                                            <td class="px-4 py-3 text-emerald-600 font-black text-right">+450</td>
                                            <td class="px-4 py-3 text-center"><span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-md">Sukses</span></td>
                                            <td class="px-4 py-3 text-right">
                                                <button type="button" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 hover:bg-red-100 px-2 py-1.5 rounded transition-colors">
                                                    <i class="fas fa-undo"></i> Batalkan
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3 text-slate-700 font-medium">Kemarin, 17:30 WIB</td>
                                            <td class="px-4 py-3 text-slate-600">Sales Spv</td>
                                            <td class="px-4 py-3 text-emerald-600 font-black text-right">+120</td>
                                            <td class="px-4 py-3 text-center"><span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-md">Sukses</span></td>
                                            <td class="px-4 py-3 text-right">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="glass-panel border-t-4 border-t-blue-500">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2"><i class="fas fa-list text-blue-500"></i> Inventory Saat Ini</h2>
                        <p class="text-xs text-slate-500 font-medium mt-1"><i class="fas fa-clock text-slate-400 mr-1"></i> Terakhir diupdate pada: Hari ini, 08:15 WIB</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-slate-400"></i>
                            </div>
                            <input type="text" placeholder="Cari kode atau nama barang..." class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 px-3 py-2 shadow-sm transition-colors w-80 sm:w-96">
                        </div>
                        <button type="button" class="bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                            <i class="fas fa-file-export"></i> Export Excel
                        </button>
                        <div class="text-xs text-slate-500 bg-slate-100 px-3 py-2 rounded-lg border border-slate-200 shadow-sm font-semibold hidden md:block">
                            <i class="fas fa-info-circle text-blue-500 mr-1"></i> Preview
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[600px] border border-slate-200 rounded-xl">
                    <table class="w-full text-sm text-left whitespace-nowrap">
                        <thead class="table-header sticky top-0 z-10 shadow-sm bg-slate-50/80 backdrop-blur-sm">
                            <tr>
                                <th class="px-4 py-3 rounded-tl-xl text-xs md:text-sm uppercase tracking-wider">Kode</th>
                                <th class="px-4 py-3 text-xs md:text-sm uppercase tracking-wider">Nama Barang</th>
                                <th class="px-4 py-3 text-xs md:text-sm uppercase tracking-wider">Kategori</th>
                                <th class="px-4 py-3 text-right text-xs md:text-sm uppercase tracking-wider text-blue-600">Dalam Perjalanan (PO)</th>
                                <th class="px-4 py-3 text-right text-xs md:text-sm uppercase tracking-wider">Stok Siap Dijual</th>
                                <th class="px-4 py-3 text-center text-xs md:text-sm uppercase tracking-wider">Satuan</th>
                                <th class="px-4 py-3 text-center rounded-tr-xl text-xs md:text-sm uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <!-- Data Dummy 1 -->
                            <tr class="row-item hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-slate-500">GLV-001</td>
                                <td class="px-4 py-3 font-bold text-slate-800">Latex Powdered Examination Gloves S</td>
                                <td class="px-4 py-3 text-slate-600">Alkes / Gloves</td>
                                <td class="px-4 py-3 text-right text-blue-500 font-medium">50</td>
                                <td class="px-4 py-3 text-right font-black text-lg">550</td>
                                <td class="px-4 py-3 text-center text-slate-600 font-medium">Box</td>
                                <td class="px-4 py-3 text-center"><span class="status-badge status-aman"><i class="fas fa-check"></i> Aman</span></td>
                            </tr>
                            <!-- Data Dummy 2 -->
                            <tr class="row-item hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-slate-500">KAS-001</td>
                                <td class="px-4 py-3 font-bold text-slate-800">RAKHA Kasa katun Lipat 6 x 6</td>
                                <td class="px-4 py-3 text-slate-600">Alkes / Kasa</td>
                                <td class="px-4 py-3 text-right text-blue-500 font-medium">100</td>
                                <td class="px-4 py-3 text-right font-black text-lg">25</td>
                                <td class="px-4 py-3 text-center text-slate-600 font-medium">Pcs</td>
                                <td class="px-4 py-3 text-center"><span class="status-badge status-menipis"><i class="fas fa-exclamation"></i> Menipis</span></td>
                            </tr>
                            <!-- Data Dummy 3 -->
                            <tr class="row-item hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-slate-500">GLV-002</td>
                                <td class="px-4 py-3 font-bold text-slate-800">Latex Powder Free Examination Gloves S</td>
                                <td class="px-4 py-3 text-slate-600">Alkes / Gloves</td>
                                <td class="px-4 py-3 text-right text-blue-500 font-medium">0</td>
                                <td class="px-4 py-3 text-right font-black text-lg text-rose-600">0</td>
                                <td class="px-4 py-3 text-center text-slate-600 font-medium">Box</td>
                                <td class="px-4 py-3 text-center"><span class="status-badge status-kosong"><i class="fas fa-times"></i> Kosong</span></td>
                            </tr>
                            <!-- Data Dummy 4 -->
                            <tr class="row-item hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-slate-500">UND-001</td>
                                <td class="px-4 py-3 font-bold text-slate-800">Underpad 60 x 90</td>
                                <td class="px-4 py-3 text-slate-600">Alkes / Underpad</td>
                                <td class="px-4 py-3 text-right text-blue-500 font-medium">0</td>
                                <td class="px-4 py-3 text-right font-black text-lg">1,450</td>
                                <td class="px-4 py-3 text-center text-slate-600 font-medium">Pcs</td>
                                <td class="px-4 py-3 text-center"><span class="status-badge status-aman"><i class="fas fa-check"></i> Aman</span></td>
                            </tr>
                            <!-- Data Dummy 5 -->
                            <tr class="row-item hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-slate-500">KAS-002</td>
                                <td class="px-4 py-3 font-bold text-slate-800">RAKHA Kasa Katun Premium 5 x 5</td>
                                <td class="px-4 py-3 text-slate-600">Alkes / Kasa</td>
                                <td class="px-4 py-3 text-right text-blue-500 font-medium">500</td>
                                <td class="px-4 py-3 text-right font-black text-lg">100</td>
                                <td class="px-4 py-3 text-center text-slate-600 font-medium">Pcs</td>
                                <td class="px-4 py-3 text-center"><span class="status-badge status-menipis"><i class="fas fa-exclamation"></i> Menipis</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layout-users>
