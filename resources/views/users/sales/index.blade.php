@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobile = $agent->isMobile();
@endphp
<x-layout-users title="{{ $title ?? 'Input Data Sales' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @push('styles')
    <style>
        /* == Background == */
        .mesh-bg { background-color: #ede9fe; }
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,1); border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); padding: 28px; }
        
        .modern-input { background: rgba(255,255,255,0.95); border: 2px solid #e2e8f0; border-radius: 14px; padding: 12px 16px; font-size: 0.85rem; color: #1e293b; font-weight: 700; outline: none; transition: all 0.2s ease; width: 100%; }
        .modern-input:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.15); }
        .modern-label { display: block; font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px; }

        @media (max-width: 767.98px) {
            .glass-card {
                padding: 16px;
                border-radius: 18px;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                background: rgba(255, 255, 255, 0.95) !important;
            }
        }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">
        <div class="relative z-10 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col">
            
            {{-- HEADER --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-2xl md:rounded-3xl p-5 md:p-6 shadow-xl mb-6 overflow-hidden border border-white/20 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="relative z-10 flex items-center gap-3 md:gap-4">
                    <div class="h-10 w-10 md:h-12 md:w-12 rounded-xl md:rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                        <i class="fas fa-file-invoice-dollar text-lg md:text-xl text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-base md:text-xl font-black tracking-tight text-white uppercase">Manajemen Data Sales</h1>
                        @if(!$isMobile)
                        <p class="text-blue-100 text-xs md:text-sm mt-0.5 font-medium leading-relaxed w-full">
                            Input data penjualan secara manual atau unggah file CSV secara bulk.
                        </p>
                        @endif
                    </div>
                </div>
                <div class="mt-4 md:mt-0 relative z-10 flex flex-wrap gap-2">
                    <a href="{{ route('sales.visualisasi') }}" class="inline-flex items-center px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-xl font-bold transition-all border border-sky-400 text-sm shadow-lg hover:shadow-sky-500/50">
                        <i class="fas fa-chart-pie mr-2"></i> Power BI Visualisasi
                    </a>
                    <a href="{{ route('sales.monitoring') }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl font-bold transition-all border border-indigo-400 text-sm shadow-lg hover:shadow-indigo-500/50">
                        <i class="fas fa-chart-line mr-2"></i> Monitoring
                    </a>
                    <a href="{{ route('sales.download_template') }}" class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl font-bold transition-all border border-white/30 text-sm backdrop-blur-md shadow-lg hover:shadow-indigo-500/30">
                        <i class="fas fa-file-excel mr-2"></i> Template CSV
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
                {{-- Column 1: Import Excel --}}
                <div class="lg:col-span-4 flex flex-col gap-8">
                    <div class="glass-card !p-8 border-t-4 border-t-emerald-500">
                        <h3 class="text-lg font-black text-slate-800 flex items-center mb-6">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg mr-3"><i class="fas fa-cloud-upload-alt"></i></div>
                            Import dari Excel/CSV
                        </h3>
                        <form action="{{ route('sales.import_excel') }}" method="POST" enctype="multipart/form-data" onsubmit="confirmSubmit(event, 'Apakah Anda yakin ingin mengimpor data dari file ini?')">
                            @csrf
                            <div class="mb-6">
                                <label class="modern-label mb-2">Pilih File (.xlsx, .csv)</label>
                                <div class="relative border-2 border-dashed border-emerald-200 rounded-2xl bg-emerald-50/50 hover:bg-emerald-50 transition-colors p-4 text-center cursor-pointer overflow-hidden">
                                    <input type="file" name="file" accept=".xlsx, .xls, .csv" required
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                        onchange="document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih';">
                                    <i class="fas fa-file-excel text-3xl text-emerald-400 mb-2"></i>
                                    <p id="fileName" class="text-sm font-bold text-slate-600 truncate px-2">Klik atau Drop file di sini</p>
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-lg hover:shadow-emerald-500/40 flex justify-center items-center">
                                <i class="fas fa-upload mr-2"></i> Upload Data
                            </button>
                        </form>
                    </div>

                    <div class="glass-card !p-8 !bg-blue-50/80 !border-blue-200">
                        <h4 class="font-bold text-blue-800 mb-4 flex items-center text-sm"><i class="fas fa-info-circle mr-2"></i> Petunjuk Import</h4>
                        <ul class="text-xs text-blue-700 space-y-3 list-disc list-inside font-medium leading-relaxed">
                            <li>Gunakan template <b>XLSX / CSV</b>.</li>
                            <li>Pastikan <b>tidak ada baris kosong</b>.</li>
                            <li>Sesuaikan <b>judul kolom</b> dengan template.</li>
                            <li>Maksimal ukuran file <b>30MB</b>.</li>
                        </ul>
                    </div>
                </div>

                {{-- Column 2: Form Manual --}}
                <div class="lg:col-span-8">
                    <div class="glass-card !p-8 border-t-4 border-t-blue-500 h-full">
                        <h3 class="text-lg font-black text-slate-800 flex items-center mb-6">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg mr-3"><i class="fas fa-keyboard"></i></div>
                            Input Data Manual
                        </h3>
                        <form action="{{ route('sales.store_manual') }}" method="POST" onsubmit="confirmSubmit(event, 'Simpan data sales ini?')">
                            @csrf
                            <div class="grid grid-cols-1 gap-y-6">
                                {{-- Baris 1 --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="modern-label">Tanggal</label>
                                        <input type="date" name="tanggal" class="modern-input py-2.5">
                                    </div>
                                    <div>
                                        <label class="modern-label">Bulan</label>
                                        <input type="text" name="bulan" placeholder="Contoh: Agustus" class="modern-input py-2.5">
                                    </div>
                                    <div>
                                        <label class="modern-label">Nama PS</label>
                                        <input type="text" name="ps" placeholder="Daffa" class="modern-input py-2.5">
                                    </div>
                                </div>
                                
                                {{-- Baris 2 --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="modern-label">Nama Customer <span class="text-red-500">*</span></label>
                                        <input type="text" name="nama_customer" required placeholder="Nama Customer" class="modern-input py-2.5">
                                    </div>
                                    <div>
                                        <label class="modern-label">Nama Produk <span class="text-red-500">*</span></label>
                                        <input type="text" name="nama_produk" required placeholder="Nama Barang" class="modern-input py-2.5">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="modern-label">Qty</label>
                                            <input type="number" id="input-qty" name="qty" min="1" placeholder="0" class="modern-input py-2.5">
                                        </div>
                                        <div>
                                            <label class="modern-label">Satuan</label>
                                            <input type="text" name="satuan" placeholder="Pcs/Box" class="modern-input py-2.5">
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Baris 3 --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="modern-label">HNA (Rp)</label>
                                        <input type="text" id="display-hna" placeholder="0" class="modern-input py-2.5">
                                        <input type="hidden" id="input-hna" name="hna" value="0">
                                    </div>
                                    <div>
                                        <label class="modern-label">Diskon (%)</label>
                                        <input type="number" id="input-diskon" name="diskon" min="0" step="0.01" placeholder="0" class="modern-input py-2.5">
                                    </div>
                                    <div>
                                        <label class="modern-label text-indigo-700">Harga Nett (Rp)</label>
                                        <input type="text" id="display-harga-nett" placeholder="0" class="modern-input py-2.5 font-bold !bg-indigo-50 !border-indigo-200" readonly>
                                        <input type="hidden" id="input-harga-nett" name="harga_nett" value="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-lg hover:shadow-blue-500/40 flex items-center">
                                    <i class="fas fa-save mr-2"></i> Simpan Data Manual
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tabel Data --}}
            <div class="glass-card !p-0 overflow-hidden mb-8 flex-1 flex flex-col">
                <div class="px-6 py-5 border-b border-slate-100 bg-white/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl"><i class="fas fa-database"></i></div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Riwayat Data Sales</h3>
                            @if(!$isMobile)
                            <p class="text-xs text-slate-500 font-semibold mt-1">Total: {{ $sales->total() }} data yang tersimpan di sistem.</p>
                            @endif
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('sales.history') }}" class="w-10 h-10 rounded-full bg-indigo-50 hover:bg-indigo-100 text-indigo-600 flex items-center justify-center transition-colors shadow-sm" title="Kelola Keseluruhan Data">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50/80 border-b border-slate-200 font-bold tracking-wider">
                            <tr>
                                <th class="px-4 py-4">Tanggal</th>
                                <th class="px-4 py-4">Bulan</th>
                                <th class="px-4 py-4">Customer</th>
                                <th class="px-4 py-4 text-center">PS</th>
                                <th class="px-4 py-4">Produk</th>
                                <th class="px-4 py-4">Qty & Satuan</th>
                                <th class="px-4 py-4 text-right">HNA</th>
                                <th class="px-4 py-4 text-right">Diskon</th>
                                <th class="px-4 py-4 text-right">Harga Nett</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white/40">
                            @forelse($sales as $item)
                            <tr class="hover:bg-indigo-50/50 transition-colors text-xs md:text-sm">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-700">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                                <td class="px-4 py-3 font-medium text-slate-700">{{ $item->bulan ?? '-' }}</td>
                                <td class="px-4 py-3 font-bold text-slate-900">{{ $item->nama_customer ?? '-' }}</td>
                                <td class="px-4 py-3 text-center font-bold text-indigo-600">{{ $item->ps ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->nama_produk ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap"><span class="px-2.5 py-1 bg-slate-100 rounded-lg text-xs font-bold text-slate-600">{{ $item->qty ?? 0 }} {{ $item->satuan }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">Rp {{ number_format($item->hna, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap text-red-500">{{ $item->diskon == floor($item->diskon) ? number_format($item->diskon, 0) : $item->diskon }}%</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap font-bold text-emerald-600">Rp {{ number_format($item->harga_nett, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-inbox text-5xl text-slate-200 mb-4"></i>
                                        <p class="font-medium">Belum ada data sales yang tersimpan.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($sales->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-white/50">
                    {{ $sales->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qtyInput = document.getElementById('input-qty');
            
            // HNA
            const hnaInput = document.getElementById('input-hna');
            const displayHna = document.getElementById('display-hna');
            
            // Diskon
            const diskonInput = document.getElementById('input-diskon');
            
            // Harga Nett
            const hargaNettInput = document.getElementById('input-harga-nett');
            const displayHargaNett = document.getElementById('display-harga-nett');

            // Format angka ke format ribuan Indonesia
            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            // Parsing teks format ribuan kembali ke float murni
            function parseRupiah(text) {
                if (!text) return 0;
                return parseFloat(text.replace(/[^0-9,-]+/g,"").replace(',', '.')) || 0;
            }

            // Saat user mengetik di field HNA (Display)
            if (displayHna) {
                displayHna.addEventListener('input', function(e) {
                    let val = parseRupiah(this.value);
                    this.value = val ? formatRupiah(val) : '';
                    hnaInput.value = val;
                    calculateHargaNett();
                });
            }

            function calculateHargaNett() {
                // Ambil nilai murni
                const qty = parseFloat(qtyInput.value) || 0;
                const hna = parseFloat(hnaInput.value) || 0;
                const diskon = parseFloat(diskonInput.value) || 0;

                const totalAwal = hna * qty;
                const potonganDiskon = totalAwal * (diskon / 100);
                const hargaNett = totalAwal - potonganDiskon;

                if (hargaNett > 0) {
                    // Update hidden input untuk dikirim ke database
                    hargaNettInput.value = hargaNett.toFixed(2);
                    // Update display input agar enak dilihat user
                    displayHargaNett.value = formatRupiah(hargaNett);
                } else {
                    hargaNettInput.value = '';
                    displayHargaNett.value = '';
                }
            }

            if (qtyInput) qtyInput.addEventListener('input', calculateHargaNett);
            if (diskonInput) diskonInput.addEventListener('input', calculateHargaNett);
        });
    </script>
</x-layout-users>
