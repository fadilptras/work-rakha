<x-layout-users :title="'Detail Klien & Sales'">
@php $routePrefix = request()->is('admin/*') ? 'admin.crm.' : 'crm.'; @endphp

    @push('styles')
    <style>
        /* == Modern Mesh Background == */
        .mesh-bg {
            background-color: #f0f6fc;
            background-image: 
                radial-gradient(at 40% 20%, rgba(147, 197, 253, 0.45) 0px, transparent 50%),
                radial-gradient(at 80% 0%, rgba(167, 139, 250, 0.35) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(191, 219, 254, 0.45) 0px, transparent 50%),
                radial-gradient(at 80% 50%, rgba(139, 92, 246, 0.25) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(221, 214, 254, 0.4) 0px, transparent 50%),
                radial-gradient(at 80% 100%, rgba(96, 165, 250, 0.35) 0px, transparent 50%),
                radial-gradient(at 0% 0%, rgba(238, 242, 255, 0.6) 0px, transparent 50%);
            background-attachment: fixed;
        }

        /* Float animation */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float-delayed { animation: float 10s ease-in-out infinite; animation-delay: 2s; }

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
            margin-bottom: 24px;
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
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">
        {{-- Background Animations --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            
            
            
        </div>

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col">
            
            {{-- Tombol Kembali --}}
            <a href="{{ route($routePrefix . 'index') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Data Sales
            </a>

        {{-- BAGIAN 1: HEADER PROFIL --}}
        <div class="bg-[#001BB7] rounded-2xl shadow-xl shadow-blue-900/10 border border-blue-900/10 mb-6 overflow-hidden relative">
            
            

            <div class="py-4 px-6 md:py-5 md:px-8 text-white relative z-10">
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-4">
                    
                    {{-- Kiri: Identitas Klien --}}
                    <div class="space-y-1.5 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="bg-white/20 backdrop-blur-md text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider border border-white/30 shadow-sm inline-flex items-center">
                                <i class="fas fa-hospital mr-1.5 opacity-80"></i> {{ $client->customer_name }}
                            </span>
                            <div class="hidden sm:block w-1 h-1 rounded-full bg-blue-300/50"></div>
                            <div class="flex items-center text-[10px] text-blue-100 font-medium">
                                <i class="fas fa-map-marker-alt mr-1.5 text-blue-300"></i> {{ $client->area ?? 'Belum set Area' }}
                            </div>
                            <div class="hidden sm:block w-1 h-1 rounded-full bg-blue-300/50"></div>
                            <div class="flex items-center text-[10px] text-blue-100 font-medium">
                                <i class="fas fa-user-tie mr-1.5 text-blue-300"></i> PIC: {{ $client->ps }}
                            </div>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white drop-shadow-sm leading-tight pt-1">
                            {{ $client->client_name }}
                        </h2>
                    </div>

                    {{-- Kanan: Statistik & Aksi --}}
                    <div class="flex flex-wrap sm:flex-nowrap items-center justify-start xl:justify-end gap-3 w-full xl:w-auto mt-2 xl:mt-0">
                        
                        <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md rounded-xl px-4 py-2 border border-white/20 shadow-sm flex-1 sm:flex-initial justify-between sm:justify-start">
                            <div class="flex flex-col">
                                <span class="text-blue-200 text-[8px] font-bold uppercase tracking-wider mb-0.5">Total Realisasi</span>
                                <div class="flex items-start">
                                    <span class="text-[9px] text-blue-100 mr-1 mt-0.5">Rp</span>
                                    <span class="text-base md:text-lg font-bold text-white leading-none">{{ number_format($client->interactions->where('transaction_type', 'OUT')->sum('amount'), 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="w-px h-8 bg-white/20 mx-1"></div>
                            <div class="flex flex-col">
                                <span class="text-emerald-300 text-[8px] font-bold uppercase tracking-wider mb-0.5">Total Saldo</span>
                                <div class="flex items-start">
                                    <span class="text-[9px] text-emerald-200 mr-1 mt-0.5">Rp</span>
                                    <span class="text-base md:text-lg font-bold text-emerald-50 leading-none">{{ number_format($currentBalance, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        @if($canEdit) 
                            <button onclick="toggleModal('editClientModal')" class="flex-shrink-0 flex items-center justify-center bg-yellow-500 hover:bg-yellow-400 text-yellow-900 text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg w-full sm:w-auto mt-2 sm:mt-0">
                                <i class="fas fa-edit mr-2"></i> Edit
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: GRID KARTU DETAIL --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- INFO CLIENT --}}
            <details class="sync-details bg-blue-600 p-6 rounded-2xl shadow-lg text-white relative overflow-hidden group hover:shadow-xl transition-all h-full">
                <summary onclick="toggleAllDetails(event, this.parentElement)" class="cursor-pointer list-none [&::-webkit-details-marker]:hidden flex justify-between items-center outline-none text-blue-100 text-[11px] font-bold uppercase tracking-widest relative z-10 border-b border-white/20 pb-2 mb-2 group-open:mb-4">
                    <div class="flex items-center"><i class="fas fa-user mr-2 text-white"></i> Informasi Client</div>
                    <i class="fas fa-chevron-down transition-transform duration-300 group-open:rotate-180"></i>
                </summary>
                <div class="space-y-3 relative z-10">
                    @if($client->contact_position)
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-blue-300"><i class="fas fa-id-badge"></i></div>
                        <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Jabatan</p><p class="font-bold text-sm tracking-wide">{{ $client->contact_position }}</p></div>
                    </div>
                    @endif
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-blue-200"><i class="fas fa-envelope"></i></div>
                        <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Email</p><p class="font-medium text-sm break-all">{{ $client->email ?? '-' }}</p></div>
                    </div>
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-green-300"><i class="fab fa-whatsapp text-lg -ml-0.5"></i></div>
                        <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Telepon / WA</p><p class="font-medium text-sm">{{ $client->contact_phone ?? '-' }}</p></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex items-start relative pl-8">
                            <div class="absolute left-0 top-1 text-pink-200"><i class="fas fa-birthday-cake"></i></div>
                            <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Tgl Lahir</p><div class="font-medium text-sm">{{ $client->contact_birth_date ? \Carbon\Carbon::parse($client->contact_birth_date)->format('d M Y') : '-' }}</div></div>
                        </div>
                        <div class="flex items-start relative pl-6">
                            <div class="absolute left-0 top-1 text-yellow-300"><i class="fas fa-star"></i></div>
                            <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Hobi</p><p class="font-medium text-sm">{{ $client->contact_hobby ?? '-' }}</p></div>
                        </div>
                    </div>
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-white/50"><i class="fas fa-home"></i></div>
                        <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Alamat Rumah</p><p class="text-sm leading-relaxed opacity-90">{{ $client->contact_address ?? '-' }}</p></div>
                    </div>
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-emerald-300"><i class="fas fa-percent"></i></div>
                        <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Komisi / Rate</p><p class="font-medium text-sm">{{ $client->commission_rate ? (float)$client->commission_rate . '%' : '-' }}</p></div>
                    </div>
                </div>
            </details>

            {{-- INFO PERUSAHAAN --}}
            <details class="sync-details bg-orange-500 p-6 rounded-2xl shadow-lg text-white relative overflow-hidden group hover:shadow-xl transition-all h-full">
                <summary onclick="toggleAllDetails(event, this.parentElement)" class="cursor-pointer list-none [&::-webkit-details-marker]:hidden flex justify-between items-center outline-none text-orange-100 text-[11px] font-bold uppercase tracking-widest relative z-10 border-b border-white/20 pb-2 mb-2 group-open:mb-4">
                    <div class="flex items-center"><i class="fas fa-building mr-2 text-white"></i> Informasi Perusahaan</div>
                    <i class="fas fa-chevron-down transition-transform duration-300 group-open:rotate-180"></i>
                </summary>
                <div class="space-y-3 relative z-10">
                    <div class="p-4 bg-white/20 backdrop-blur-md rounded-lg border border-white/20 relative overflow-hidden">
                        <i class="fas fa-hospital absolute right-2 bottom-2 text-5xl text-white/20 -rotate-12 pointer-events-none"></i>
                        <p class="text-[10px] text-orange-100 font-bold uppercase mb-1">Nama Instansi / RS</p>
                        <p class="font-bold text-lg leading-tight">{{ $client->customer_name }}</p>
                    </div>
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-white/80"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <p class="text-[10px] text-orange-200 font-bold uppercase mb-0.5">Tanggal Berdiri</p>
                            <div class="flex items-center font-medium text-sm">
                                @if($client->company_founded_date)
                                    <span>{{ \Carbon\Carbon::parse($client->company_founded_date)->format('d F Y') }}</span>
                                    <span class="ml-2 text-[10px] bg-white text-orange-600 px-2 py-0.5 rounded-full font-bold shadow-sm">{{ \Carbon\Carbon::parse($client->company_founded_date)->age }} Th</span>
                                @else <span class="italic opacity-70">Belum diisi</span> @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-white/80"><i class="fas fa-map-marked-alt"></i></div>
                        <div><p class="text-[10px] text-orange-200 font-bold uppercase mb-0.5">Alamat Kantor</p><p class="text-sm leading-relaxed opacity-90">{{ $client->company_address ?? '-' }}</p></div>
                    </div>

                    {{-- Data Apoteker --}}
                    <div class="mt-4 pt-3 border-t border-white/20">
                        <p class="text-[10px] text-orange-200 font-bold uppercase mb-2"><i class="fas fa-user-md mr-1"></i> Data Apoteker Penanggung Jawab</p>
                        <div class="grid grid-cols-2 gap-y-2 text-sm">
                            <div><p class="text-[9px] text-orange-300 uppercase">Nama</p><p class="font-bold">{{ $client->pharmacist_name ?? '-' }}</p></div>
                            <div><p class="text-[9px] text-orange-300 uppercase">SIPA</p><p class="font-bold">{{ $client->pharmacist_license_no ?? '-' }}</p></div>
                            <div class="col-span-2"><p class="text-[9px] text-orange-300 uppercase">No. Telp</p><p class="font-bold">{{ $client->pharmacist_phone ?? '-' }}</p></div>
                        </div>
                    </div>
                </div>
            </details>

            {{-- INFO BANK --}}
            <details class="sync-details bg-gradient-to-br from-gray-900 via-slate-800 to-gray-900 p-6 rounded-2xl shadow-lg border border-gray-700 text-white relative overflow-hidden group hover:shadow-2xl transition duration-500 h-full">
                <summary onclick="toggleAllDetails(event, this.parentElement)" class="cursor-pointer list-none [&::-webkit-details-marker]:hidden flex justify-between items-center outline-none relative z-10 border-b border-gray-700 pb-2 mb-2 group-open:mb-4">
                    <div class="text-gray-400 text-xs font-bold uppercase tracking-widest flex items-center">
                        <span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center mr-3 text-emerald-400"><i class="fas fa-wallet"></i></span> Informasi Bank
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300 group-open:rotate-180"></i>
                </summary>
                <div class="relative z-10 flex flex-col h-full">
                    <div class="mt-1">
                        <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Bank & Rekening</p>
                        <div class="flex flex-col">
                            <span class="font-bold text-2xl tracking-wide text-white leading-tight mb-1">{{ $client->bank_name ?? 'BANK -' }}</span>
                            <p class="text-xs text-gray-400 mb-2">{{ $client->bank_account_name ? 'A/n '.$client->bank_account_name : '' }}</p>
                            <div class="flex items-center gap-2 font-mono text-emerald-400 tracking-widest text-base bg-white/5 px-3 py-1.5 rounded-lg w-fit border border-white/5 shadow-inner">
                                <i class="fas fa-credit-card text-xs opacity-70"></i> <span class="font-bold">{{ $client->bank_account_number ?? '----' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-700/50 pt-3 mt-auto">
                        <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Saldo Awal</p>
                        <p class="text-3xl font-mono font-bold text-emerald-400 tracking-tight text-shadow-sm flex items-baseline">
                            <span class="text-sm text-gray-500 mr-2 font-normal">IDR</span> {{ number_format($client->opening_balance ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </details>
        </div>

        <script>
            function toggleAllDetails(event, element) {
                event.preventDefault();
                const details = document.querySelectorAll('.sync-details');
                const isOpening = !element.open;
                details.forEach(d => {
                    d.open = isOpening;
                });
            }
        </script>

        {{-- MENU NAVIGASI --}}
        <div class="mb-4 flex justify-center md:justify-start">
            <div class="bg-white rounded-xl grid grid-cols-2 md:inline-flex gap-1 border border-gray-200 shadow-sm">
                <button id="btn-sales" onclick="switchTab('sales')" class="nav-btn px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <i class="fas fa-plus-circle text-sm"></i> Sales (In)
                </button>
                <button id="btn-support" onclick="switchTab('support')" class="nav-btn px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <i class="fas fa-hand-holding-usd text-sm"></i> Usage (Out)
                </button>
                <button id="btn-activity" onclick="switchTab('activity')" class="nav-btn px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <i class="fas fa-glass-cheers text-sm"></i> Aktivitas
                </button>
                <button id="btn-history" onclick="switchTab('history')" class="nav-btn px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <i class="fas fa-history text-sm"></i> Riwayat
                </button>
                <button id="btn-recap" onclick="switchTab('recap')" class="nav-btn px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none">
                    <i class="fas fa-calendar-check text-sm"></i> Rekap Sales
                </button>
            </div>
        </div>
        
        {{-- 1. INPUT SALES --}}
        <div id="section-sales" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden relative">
            <div class="bg-blue-600 px-6 py-4 border-b border-blue-100 flex justify-between items-center">
                <h3 class="font-bold text-white text-lg flex items-center"><span class="w-8 h-8 bg-white text-blue-600 rounded-lg flex items-center justify-center mr-3 text-sm shadow"><i class="fas fa-plus"></i></span> Input Sales</h3>
            </div>
            <div class="p-6 md:p-8">
                @if($hasFullAccess)
                <form action="{{ route($routePrefix . 'interaction.store') }}" method="POST" onsubmit="const b=this.querySelector('[type=submit]');if(b){b.disabled=true;b.classList.add('opacity-60');}">
                    @csrf
                    <div class="mb-4 grid grid-cols-1 lg:grid-cols-12 gap-3">
                        <div class="lg:col-span-4">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Rumah Sakit (Data Command Center) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="client_id_in" class="w-full border border-blue-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 pl-3 pr-8 py-1.5 text-sm appearance-none bg-white" required>
                                    @if($salesCustomers->isEmpty())
                                        <option value="" disabled selected>Belum ada data (Cek PIC Sales)</option>
                                    @else
                                        <option value="" disabled {{ $client->sales_customer_name ? '' : 'selected' }}>Pilih Rumah Sakit...</option>
                                        @foreach($salesCustomers as $c)
                                            <option value="{{ $c }}" @selected($client->sales_customer_name == $c)>{{ $c }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-600">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">Terkunci ke PS: <strong class="text-gray-700">{{ ($lockedPs ?? null) ?: '-' }}</strong>@if($client->sales_customer_name) &bull; Terhubung ke: <strong class="text-gray-700">{{ $client->sales_customer_name }}</strong>@endif</p>
                        </div>
                        <div class="lg:col-span-5">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Bulan <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="month" id="tanggal_interaksi_in" class="w-full border border-blue-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-1.5 text-sm" required>
                                <button type="button" onclick="fetchSalesData()" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 font-bold px-3 py-2 rounded-lg shadow-sm transition whitespace-nowrap" title="Ambil data dari Command Center">
                                    <i class="fas fa-cloud-download-alt mr-1"></i> Tarik & Tambah
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Anda bisa menarik data dari beberapa tanggal & RS berbeda.</p>
                        </div>
                        <div class="lg:col-span-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan (Opsional)</label>
                            <input type="text" name="notes" class="w-full border border-blue-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-1.5 text-sm" placeholder="Catatan opsional...">
                        </div>
                    </div>

                    <div class="mb-2 flex justify-between items-end border-b pb-2">
                        <h4 class="font-bold text-gray-700 flex items-center"><i class="fas fa-box-open mr-2 text-blue-500"></i> Antrean Data Sales <span id="queue_count_desktop" class="ml-2 bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">0</span></h4>
                    </div>

                    <div id="product_rows_container_desktop" class="space-y-3 mb-6 max-h-[40vh] overflow-y-auto custom-scrollbar pr-2">
                        <div id="empty_queue_msg_desktop" class="text-center py-6 text-gray-400 italic text-sm">
                             Belum ada data ditarik. Silakan pilih tanggal dan klik "Tarik & Tambah".
                        </div>
                    </div>

                    <datalist id="produk-list">
                        @foreach($productNames as $prod)
                            <option value="{{ $prod }}"></option>
                        @endforeach
                    </datalist>

                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition transform active:scale-95 text-sm md:text-base flex justify-center items-center gap-2"><i class="fas fa-save"></i> Simpan Data Sales</button>
                    </div>
                </form>
                @else
                <div class="flex flex-col items-center justify-center py-10 px-4 text-center bg-gray-50/50 rounded-xl border-2 border-dashed border-gray-200">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-3 text-xl shadow-sm">
                        <i class="fas fa-sync-alt animate-spin-slow"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 text-base mb-1">Sinkronisasi Data Sales Otomatis</h4>
                    <p class="text-xs text-gray-500 max-w-lg leading-relaxed">
                        Sebagai PIC, Anda dapat memantau seluruh riwayat transaksi masuk pada halaman ini. Proses penginputan data baru berjalan secara otomatis dan disesuaikan langsung dengan data transaksi sales di Command Center secara <strong>realtime</strong>. Jika Anda memerlukan penyesuaian khusus atau rekonsiliasi data manual, silakan berkoordinasi dengan <strong>Admin / Kepala Divisi</strong>.
                    </p>
                </div>
                @endif
            </div>
        </div>

        {{-- 2. INPUT SUPPORT --}}
        <div id="section-support" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden relative hidden">
            <style>
                @media (min-width: 768px) {
                    .custom-w-tanggal { width: 15% !important; }
                    .custom-w-nominal { width: 15% !important; }
                    .custom-w-keperluan { width: 50% !important; }
                    .custom-w-lampiran { width: 20% !important; }
                }
            </style>
            <div class="bg-red-600 px-6 py-4 border-b border-red-100 flex justify-between items-center">
                <h3 class="font-bold text-white text-lg flex items-center"><span class="w-8 h-8 bg-white text-red-600 rounded-lg flex items-center justify-center mr-3 text-sm shadow"><i class="fas fa-hand-holding-usd"></i></span> Pengeluaran</h3>
                <button type="button" onclick="openUsageInfoModal()" class="text-white hover:text-red-100 transition text-xl flex items-center justify-center w-8 h-8 rounded-full hover:bg-white/10 outline-none" title="Informasi Alur Pengeluaran">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>

            <!-- Modal Alert Info Alur (Centered, Smooth, Bigger) -->
            <div id="usage-info-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 ease-in-out">
                <div id="usage-info-modal-card" class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-100 transform scale-90 transition-transform duration-300 ease-out">
                    <div class="bg-red-600 px-6 py-4 flex items-center gap-3">
                        <span class="w-10 h-10 bg-white/20 text-white rounded-lg flex items-center justify-center text-lg"><i class="fas fa-info-circle"></i></span>
                        <h3 class="font-bold text-white text-lg">Info Alur Pengeluaran</h3>
                    </div>
                    <div class="p-6">
                        <div class="font-bold text-red-600 mb-3 flex items-center gap-2 text-base">
                            <i class="fas fa-paper-plane"></i>
                            <span>Pengajuan Dana Otomatis</span>
                        </div>
                        <p class="leading-relaxed text-sm text-gray-600">
                            Pengisian form pengeluaran (usage) ini akan secara <strong>otomatis terbuat sebagai Pengajuan Dana (Finance)</strong>. Dana akan diproses setelah disetujui oleh Kepala Divisi dan diselesaikan oleh bagian Finance.
                        </p>
                        <div class="mt-6 flex justify-end">
                            <button type="button" onclick="closeUsageInfoModal()" class="px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-xs rounded-xl shadow-sm transition active:scale-95">Ok, Paham</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-5 md:p-6">
                <form action="{{ route($routePrefix . 'interaction.support') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <div class="flex flex-col md:flex-row gap-4 mb-4">
                        <!-- Baris 1: Detail Pengajuan (1 Baris Sleek) -->
                        <div class="w-full custom-w-tanggal">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" name="interaction_date" class="w-full border-2 border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-3 py-2 text-sm" required>
                        </div>
                        <div class="w-full custom-w-nominal">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nominal Keluar <span class="text-red-500">*</span></label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 text-xs font-bold">Rp</span>
                                </div>
                                <input type="text" name="amount" onkeyup="formatRupiah(this)" class="w-full border-2 border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 pl-9 px-3 py-2 font-mono font-bold text-base text-red-700" placeholder="0" required>
                            </div>
                        </div>
                        <div class="w-full custom-w-keperluan">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Keperluan Support <span class="text-red-500">*</span></label>
                            <input type="text" name="purpose" class="w-full border-2 border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-3 py-2 text-sm" placeholder="Contoh: Transport" required>
                        </div>
                        <div class="w-full custom-w-lampiran">
                            <label class="block text-xs font-bold text-gray-700 mb-1 flex items-center gap-1.5">
                                <i class="fas fa-paperclip text-red-500 text-xs"></i>
                                <span>Lampiran Tambahan</span>
                                <span class="text-gray-400 text-[10px] font-normal">(Opsional)</span>
                            </label>
                            <div class="relative flex items-center justify-between border-2 border-red-300 rounded-lg shadow-sm bg-white px-3 py-1.5 h-[38px]">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i class="fas fa-cloud-upload-alt text-red-400 text-xs flex-shrink-0"></i>
                                    <span id="file-name-support-desktop" class="text-xs font-semibold text-gray-500 truncate max-w-[80px] md:max-w-[120px]">Pilih berkas...</span>
                                </div>
                                <label class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold text-[10px] py-1 px-2.5 rounded cursor-pointer transition shadow-sm flex items-center gap-1 active:scale-95 flex-shrink-0">
                                    <i class="fas fa-folder-open"></i> Cari
                                    <input type="file" name="lampiran_tambahan" class="hidden" onchange="updateFileNameSupportDesktop(this)">
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    @if($hasFullAccess)
                    <div class="mb-4 flex items-center gap-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <input type="checkbox" name="direct_usage" id="direct_usage_desktop" value="1" onchange="toggleDirectUsageDesktop(this)" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <label for="direct_usage_desktop" class="text-xs font-bold text-gray-700 cursor-pointer">Catat Langsung ke CRM (Tanpa Pengajuan Dana ke Finance)</label>
                    </div>
                    @endif

                    <div id="bank-info-container-desktop" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                        <!-- Baris 2: Informasi Bank Penerima (3 Kolom) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Bank <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_bank" value="{{ $client->bank_name }}" class="w-full border-2 border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-3 py-2 text-sm" placeholder="Contoh: BCA, Mandiri" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No. Rekening <span class="text-red-500">*</span></label>
                            <input type="text" name="no_rekening" value="{{ $client->bank_account_number }}" class="w-full border-2 border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-3 py-2 text-sm font-mono font-bold" placeholder="0987654321" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama di Rekening <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_rek" value="{{ $client->bank_account_name }}" class="w-full border-2 border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-3 py-2 text-sm" placeholder="Atas Nama..." required>
                        </div>
                    </div>
                    <div class="flex">
                        <button type="submit" id="submit-btn-support-desktop" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition transform active:scale-95 text-sm md:text-base flex items-center justify-center gap-2"><i class="fas fa-paper-plane"></i> Ajukan Dana</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 3. INPUT AKTIVITAS & ENTERTAIN (Dengan FILTER TAHUN) --}}
        <div id="section-activity" class="space-y-8 hidden">
            {{-- FORM INPUT --}}
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden relative">
                <div class="bg-orange-500 px-6 py-4 border-b border-orange-100 flex justify-between items-center">
                    <h3 class="font-bold text-white text-lg flex items-center">
                        <span class="w-8 h-8 bg-white text-orange-600 rounded-lg flex items-center justify-center mr-3 text-sm shadow">
                            <i class="fas fa-glass-cheers"></i>
                        </span>
                        Input Aktivitas
                    </h3>
                    <button type="button" onclick="openActivityInfoModal()" class="text-white hover:text-orange-100 transition text-xl flex items-center justify-center w-8 h-8 rounded-full hover:bg-white/10 outline-none" title="Informasi Alur Aktivitas">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>

                <!-- Modal Alert Info Aktivitas (Centered, Smooth, Bigger) -->
                <div id="activity-info-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 ease-in-out">
                    <div id="activity-info-modal-card" class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-100 transform scale-90 transition-transform duration-300 ease-out">
                        <div class="bg-orange-500 px-6 py-4 flex items-center gap-3">
                            <span class="w-10 h-10 bg-white/20 text-white rounded-lg flex items-center justify-center text-lg"><i class="fas fa-info-circle"></i></span>
                            <h3 class="font-bold text-white text-lg">Info Alur Aktivitas</h3>
                        </div>
                        <div class="p-6">
                            <div class="font-bold text-orange-600 mb-3 flex items-center gap-2 text-base">
                                <i class="fas fa-glass-cheers"></i>
                                <span>Pencatatan Aktivitas & Entertain</span>
                            </div>
                            <p class="leading-relaxed text-sm text-gray-600">
                                Pengisian form aktivitas ini digunakan untuk <strong>mencatat kunjungan lapangan, makan siang, pertemuan, entertain klien, maupun kegiatan operasional lainnya</strong>. Pengeluaran biaya untuk aktivitas ini merupakan pencatatan mandiri dan tidak terhubung otomatis dengan alur pengajuan dana ke bagian keuangan/finance.
                            </p>
                            <div class="mt-6 flex justify-end">
                                <button type="button" onclick="closeActivityInfoModal()" class="px-5 py-2.5 bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold text-xs rounded-xl shadow-sm transition active:scale-95">Ok, Paham</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6 md:p-8">
                    <form action="{{ route($routePrefix . 'interaction.entertain') }}" method="POST">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="space-y-4">
                                <div><label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Tanggal Kegiatan <span class="text-red-500">*</span></label><input type="date" name="interaction_date" class="w-full border-2 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 px-3 py-2" required></div>
                            </div>
                            <div class="space-y-4 bg-orange-50/40 p-5 rounded-xl border border-orange-100 flex flex-col h-full">
                                <div class="flex-grow"><label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Keterangan <span class="text-red-500">*</span></label><textarea name="notes" rows="3" class="w-full border-2 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 px-3 py-2 text-sm" placeholder="Contoh: Makan siang membahas proyek baru, dll." required></textarea></div>
                                <div><label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Nominal Biaya (Rp) <span class="text-red-500">*</span></label><div class="relative rounded-md shadow-sm"><div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 sm:text-xs font-bold">Rp</span></div><input type="text" name="amount" onkeyup="formatRupiah(this)" class="w-full border-2 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pl-8 px-3 py-2 font-mono font-bold text-lg text-orange-700" placeholder="0" required></div></div>
                                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md transition transform active:scale-95 flex items-center justify-center gap-2 mt-2"><i class="fas fa-save"></i> Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABEL AKTIVITAS + FILTER TAHUN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                @php
                    $totalActivity = $activities->sum('amount');
                @endphp

                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wide flex items-center gap-2">
                            <i class="fas fa-history text-orange-500"></i> Riwayat Aktivitas & Entertain
                        </h3>
                        <div class="bg-orange-100 border border-orange-200 text-orange-700 px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                            Total: Rp {{ number_format($totalActivity, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- FILTER TAHUN AKTIVITAS --}}
                    <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="activity"> 
                        <label class="text-xs font-bold text-gray-500 uppercase">Filter:</label>
                        <div class="relative">
                            <select name="activity_year" onchange="this.form.submit()" class="pl-3 pr-8 py-1.5 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring-orange-500 cursor-pointer hover:bg-gray-50 transition appearance-none">
                                <option value="">Semua Tahun</option>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ (request('activity_year') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-orange-50 text-orange-800 uppercase text-xs font-bold tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Aktivitas / Keterangan</th>
                                <th class="px-5 py-3 text-right">Biaya</th>
                                @if($hasFullAccess)
                                <th class="px-5 py-3 text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($activities as $act)
                            <tr class="hover:bg-orange-50/30 transition">
                                <td class="px-5 py-3 whitespace-nowrap font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($act->interaction_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3">
                                    <button type="button" onclick="openViewTransactionModal({
                                        id: '{{ $act->id }}',
                                        jenis: 'ENTERTAIN',
                                        tanggal: '{{ \Carbon\Carbon::parse($act->interaction_date)->translatedFormat('d F Y') }}',
                                        produk: '{{ addslashes($act->product_name) }}',
                                        nominal: '{{ number_format($act->amount, 0, ',', '.') }}',
                                        rate: '0',
                                        valueNet: '0',
                                        catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->notes)) }}'
                                    })" class="text-left font-bold text-gray-800 hover:text-orange-600 hover:underline focus:outline-none">
                                        {{ $act->product_name }}
                                    </button>
                                    <div class="text-xs text-gray-500">{{ $act->notes }}</div>
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-orange-600">
                                    {{ number_format($act->amount, 0, ',', '.') }}
                                </td>
                                @if($hasFullAccess)
                                <td class="px-5 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" 
                                            onclick="openEditTransactionModal({
                                                id: '{{ $act->id }}',
                                                jenis: 'ENTERTAIN',
                                                tanggal: '{{ $act->interaction_date }}',
                                                produk: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->product_name)) }}', 
                                                nominal: '{{ $act->amount }}',
                                                catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->notes)) }}'
                                            })"
                                            class="text-orange-400 hover:text-orange-600 transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route($routePrefix . 'interaction.destroy', $act->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Hapus aktivitas ini?');" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="text-gray-300 hover:text-red-500 transition"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400 italic bg-gray-50/50">Belum ada data aktivitas</td></tr>
                            @endforelse
                        </tbody>
                        @if($activities->count() > 0)
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="2" class="px-5 py-3 text-right font-bold text-gray-600 uppercase text-xs tracking-wider">Total Pengeluaran Entertain</td>
                                <td class="px-5 py-3 text-right font-mono font-extrabold text-orange-700 text-base">{{ number_format($totalActivity, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- 4. RIWAYAT TRANSAKSI UTAMA --}}
        <div id="section-history" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center bg-gray-50/50 gap-4">
                <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                    <i class="fas fa-history text-gray-400"></i> Riwayat Transaksi
                </h3>
                {{-- FILTER TAHUN HISTORY --}}
                <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="history"> 
                    <label class="text-xs font-bold text-gray-500 uppercase">Filter:</label>
                    <div class="relative">
                        <select name="history_year" onchange="this.form.submit()" class="pl-3 pr-8 py-1.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 cursor-pointer hover:bg-gray-50 transition appearance-none">
                            <option value="">Semua Tahun</option>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ (request('history_year') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left min-w-[800px]">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="px-4 py-4">Tanggal</th>
                            <th class="px-4 py-4">Produk / Keterangan</th>
                            <th class="px-4 py-4 text-right">Nilai Sales (Gross)</th>
                            <th class="px-4 py-4 text-center w-16">Komisi</th>
                            <th class="px-4 py-4 text-right text-blue-800">Value (Net)</th>
                            <th class="px-4 py-4 text-right text-red-600">Usage (Out)</th>
                            @if($hasFullAccess)
                            <th class="px-4 py-4 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($interactions as $item)
                            @if($item->transaction_type == 'ENTERTAIN') @continue @endif
                            @php
                                $isOut = $item->transaction_type == 'OUT';
                                $rate = (float)($item->commission_rate ?? 0);
                                $displayNote = trim(preg_replace('/\[Rate:[\d\.]+%?\]\s*/', '', $item->notes ?? ''));
                                $nominal = $item->sales_amount > 0 ? $item->sales_amount : $item->amount;
                                $valueNet = (!$isOut) ? ($nominal * ($rate/100)) : 0;
                            @endphp
                        <tr class="{{ $isOut ? 'bg-red-50/50' : 'hover:bg-blue-50/50' }} transition">
                            <td class="px-4 py-3 whitespace-nowrap font-bold text-gray-700">{{ \Carbon\Carbon::parse($item->interaction_date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <button type="button" onclick="openViewTransactionModal({
                                    id: '{{ $item->id }}',
                                    jenis: '{{ $item->transaction_type }}',
                                    tanggal: '{{ \Carbon\Carbon::parse($item->interaction_date)->translatedFormat('d F Y') }}',
                                    produk: '{{ addslashes($item->product_name) }}',
                                    nominal: '{{ number_format(($item->transaction_type == 'IN') ? $item->sales_amount : $item->amount, 0, ',', '.') }}',
                                    rate: '{{ $rate }}',
                                    valueNet: '{{ number_format($valueNet, 0, ',', '.') }}',
                                    catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $displayNote)) }}'
                                })" class="text-left font-bold {{ $isOut ? 'text-red-800 hover:text-red-950' : 'text-blue-900 hover:text-blue-950' }} hover:underline focus:outline-none">
                                    {{ $item->product_name }}
                                </button>
                                <div class="text-xs text-gray-500 italic">{{ $displayNote }}</div>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">{{ (!$isOut) ? number_format($nominal, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-center">@if(!$isOut && $rate > 0) <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded text-xs font-bold shadow-sm border border-gray-300">{{ (float)$rate }}%</span> @else <span class="text-gray-300">-</span> @endif</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-blue-700">{{ (!$isOut) ? number_format($valueNet, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-red-600">{{ $isOut ? number_format($item->amount, 0, ',', '.') : '-' }}</td>
                            @if($hasFullAccess)
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="openEditTransactionModal({id: '{{ $item->id }}', jenis: '{{ $item->transaction_type }}', tanggal: '{{ $item->interaction_date }}', produk: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $item->product_name)) }}', nominal: '{{ ($item->transaction_type == 'IN') ? $item->sales_amount : $item->amount }}', rate: '{{ $rate }}', catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $displayNote)) }}'})" class="text-blue-400 hover:text-blue-600 transition" title="Edit Data"><i class="fas fa-edit"></i></button>
                                    <form action="{{ route($routePrefix . 'interaction.destroy', $item->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Hapus transaksi ini?');" class="inline">@csrf @method('DELETE')<button class="text-gray-300 hover:text-red-600 transition" title="Hapus Data"><i class="fas fa-trash-alt"></i></button></form>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-8 text-gray-400">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($interactions->hasPages())
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">{{ $interactions->links() }}</div>
            @endif
        </div>

        {{-- 5. REKAP TAHUNAN --}}
        <div id="section-recap" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4 bg-gray-50/50">
                <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                    <span class="bg-white p-1.5 rounded-lg shadow-sm border border-gray-100"><i class="fas fa-chart-bar text-blue-600"></i></span>
                    Rekapitulasi Tahun {{ $year }}
                </h3>
                <div class="flex items-center gap-2">
                    <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="flex items-center">
                        <input type="hidden" name="tab" value="recap"> 
                        <div class="relative">
                            <select name="year" onchange="this.form.submit()" class="pl-4 pr-10 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 cursor-pointer hover:bg-gray-50 transition appearance-none">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                                @endfor
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                        </div>
                    </form>
                    <a href="{{ route($routePrefix . 'client.export', ['client' => $client->id, 'year' => $year]) }}" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2 px-4 rounded-lg shadow-sm transition hover:shadow-md border border-emerald-700"><i class="fas fa-file-excel mr-2"></i> Export Excel</a>
                </div>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left min-w-[800px]">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs font-bold tracking-wider border-b border-gray-200">
                        <tr><th class="px-4 py-3">Bulan</th><th class="px-4 py-3 text-right">Sales (In)</th><th class="px-4 py-3 text-center w-16">Komisi</th><th class="px-4 py-3 text-right text-blue-700 bg-blue-50/50">Value (Net)</th><th class="px-4 py-3 text-right text-red-600">Usage (Out)</th><th class="px-4 py-3 text-right text-gray-800 border-l border-gray-200 bg-gray-50">Saldo</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="bg-yellow-50 hover:bg-yellow-100 transition border-b border-yellow-200">
                            <td class="px-4 py-3 font-bold text-gray-800 italic" colspan="5"><div class="flex items-center"><span class="w-6 h-6 rounded-full bg-yellow-200 text-yellow-700 flex items-center justify-center mr-2 text-xs"><i class="fas fa-forward"></i></span> {{ $startingLabel }} </div></td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 border-l border-yellow-200 bg-yellow-100">{{ number_format($startingBalance, 0, ',', '.') }} </td>
                        </tr>
                        @foreach ($recap as $r)
                        <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                            <td class="px-4 py-3">
                                <button type="button" onclick="showMonthlyDetail('{{ $r['month_name'] }}', {{ $loop->iteration }}, {{ $year }})" class="font-bold text-blue-600 hover:text-blue-800 hover:underline text-left focus:outline-none flex items-center gap-1.5">
                                    <i class="far fa-calendar-alt text-xs text-blue-400"></i>
                                    <span>{{ $r['month_name'] }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">{{ $r['gross_in'] > 0 ? number_format($r['gross_in'], 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-center font-mono text-xs text-gray-500">{{ $r['commission_text'] }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-blue-800 bg-blue-50/30">{{ $r['net_value'] > 0 ? number_format($r['net_value'], 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono text-red-600">{{ $r['out'] > 0 ? number_format($r['out'], 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 border-l border-gray-200 bg-gray-50/30">{{ number_format($r['saldo'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-bold text-gray-800 border-t-2 border-gray-200">
                        <tr><td class="px-4 py-4 uppercase">Total</td><td class="px-4 py-4 text-right text-gray-600">{{ number_format($yearlyTotals['gross_in'], 0, ',', '.') }}</td><td class="px-4 py-4 text-center">-</td> <td class="px-4 py-4 text-right text-blue-900 bg-blue-100">{{ number_format($yearlyTotals['net_value'], 0, ',', '.') }}</td><td class="px-4 py-4 text-right text-red-700">{{ number_format($yearlyTotals['out'], 0, ',', '.') }}</td><td class="px-4 py-4 text-right border-l border-gray-300">{{ number_format($yearlyTotals['saldo'], 0, ',', '.') }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

    {{-- MODAL EDIT DATA KLIEN --}}
    @push('modals')
        <div id="editClientModal" class="hidden fixed inset-0 bg-gray-900/10 z-[9999] flex items-center justify-center p-4 backdrop-blur-md transition-opacity duration-300">
            {{-- MAX-W-5XL agar lebih compact --}}
            <div class="bg-white w-full md:max-w-5xl rounded-2xl shadow-2xl overflow-hidden border border-gray-200 flex flex-col max-h-[90vh]">
                <div class="bg-gradient-to-r from-blue-700 to-blue-600 px-5 py-3 border-b border-blue-500 flex justify-between items-center shadow-md z-10 shrink-0">
                    <h3 class="font-bold text-lg text-white flex items-center"><i class="fas fa-edit mr-3"></i> Edit Data Klien</h3>
                    <button onclick="toggleModal('editClientModal')" class="text-white hover:text-red-200 transition text-2xl font-bold focus:outline-none">&times;</button>
                </div>
                
                <form action="{{ route($routePrefix . 'client.update', $client->id) }}" method="POST" class="flex flex-col flex-grow overflow-hidden">
                    @csrf @method('PUT')
                    <div class="overflow-y-auto p-5 custom-scrollbar flex-grow bg-gray-50/30">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch h-full">
                            {{-- KOLOM 1 --}}
                            <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden flex flex-col h-full">
                                <div class="bg-blue-50/80 px-4 py-2 border-b border-blue-300 flex items-center">
                                    <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">1</span><h4 class="text-blue-800 text-xs font-bold uppercase tracking-wider">Identitas Personal</h4>
                                </div>
                                <div class="p-3 space-y-3 flex-grow">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase">Nama & Jabatan <span class="text-red-500">*</span></label>
                                        <div class="space-y-2">
                                            <input type="text" name="client_name" value="{{ old('client_name', $client->client_name) }}" required class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md shadow-sm text-sm px-3 py-1.5 font-bold">
                                            <input type="text" name="contact_position" value="{{ old('contact_position', $client->contact_position) }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md shadow-sm text-xs px-3 py-1.5" placeholder="Jabatan">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Kontak Personal</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" name="contact_phone" value="{{ old('contact_phone', $client->contact_phone) }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md text-sm px-3 py-1.5" placeholder="WA">
                                            <input type="email" name="email" value="{{ old('email', $client->email) }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md text-sm px-3 py-1.5" placeholder="Email">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Tgl Lahir</label><input type="date" name="contact_birth_date" value="{{ old('contact_birth_date', optional($client->contact_birth_date)->format('Y-m-d')) }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md text-sm px-3 py-1.5"></div>
                                        <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Hobi</label><input type="text" name="contact_hobby" value="{{ old('contact_hobby', $client->contact_hobby) }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md text-sm px-3 py-1.5" placeholder="Hobi"></div>
                                    </div>
                                    <div class="flex-grow"><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Alamat Rumah</label><textarea name="contact_address" rows="2" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md text-sm px-3 py-1.5 resize-none">{{ old('contact_address', $client->contact_address) }}</textarea></div>
                                </div>
                            </div>
                            {{-- KOLOM 2 --}}
                            <div class="bg-white rounded-xl border border-orange-100 shadow-sm overflow-hidden flex flex-col h-full">
                                <div class="bg-orange-50/80 px-4 py-2 border-b border-orange-100 flex items-center">
                                    <span class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">2</span><h4 class="text-orange-800 text-xs font-bold uppercase tracking-wider">Data Perusahaan</h4>
                                </div>
                                <div class="p-3 space-y-3 flex-grow">
                                    <div><label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase">Nama Perusahaan <span class="text-red-500">*</span></label><input type="text" name="customer_name" value="{{ old('customer_name', $client->customer_name) }}" required class="w-full border-2 border-gray-300 focus:border-orange-500 rounded-md text-sm px-3 py-1.5 font-semibold"></div><div><label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase">Nama di Sales / Command Center</label><input type="text" name="sales_customer_name" value="{{ old('sales_customer_name', $client->sales_customer_name) }}" class="w-full border-2 border-gray-300 focus:border-orange-500 rounded-md text-sm px-3 py-1.5 font-semibold" placeholder="Nama di Sales (opsional, bila beda)"></div>
                                    <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Detail Perusahaan</label><div class="grid grid-cols-2 gap-2"><input type="text" name="area" value="{{ old('area', $client->area) }}" class="w-full border-2 border-gray-300 focus:border-orange-500 rounded-md text-sm px-3 py-1.5" placeholder="Area"><input type="date" name="company_founded_date" value="{{ old('company_founded_date', optional($client->company_founded_date)->format('Y-m-d')) }}" class="w-full border-2 border-gray-300 focus:border-orange-500 rounded-md text-sm px-3 py-1.5"></div></div>
                                    <div class="flex-grow"><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Alamat Kantor</label><textarea name="company_address" rows="2" class="w-full border-2 border-gray-300 focus:border-orange-500 rounded-md text-sm px-3 py-1.5 resize-none">{{ old('company_address', $client->company_address) }}</textarea></div>
                                    <div class="mt-2 pt-2 border-t border-orange-100">
                                        <label class="block text-[11px] font-bold text-orange-700 mb-1 uppercase">Data Apoteker</label>
                                        <div class="space-y-2">
                                            <input type="text" name="pharmacist_name" value="{{ old('pharmacist_name', $client->pharmacist_name) }}" class="w-full border-2 border-gray-300 rounded-md text-xs focus:border-orange-500 px-3 py-1.5" placeholder="Nama Apoteker">
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="text" name="pharmacist_license_no" value="{{ old('pharmacist_license_no', $client->pharmacist_license_no) }}" class="w-full border-2 border-gray-300 rounded-md text-xs focus:border-orange-500 px-3 py-1.5" placeholder="Nomor SIPA">
                                                <input type="text" name="pharmacist_phone" value="{{ old('pharmacist_phone', $client->pharmacist_phone) }}" class="w-full border-2 border-gray-300 rounded-md text-xs focus:border-orange-500 px-3 py-1.5" placeholder="Telp Apoteker">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- KOLOM 3 --}}
                            <div class="bg-white rounded-xl border border-emerald-100 shadow-sm overflow-hidden flex flex-col h-full">
                                <div class="bg-emerald-50/80 px-4 py-2 border-b border-emerald-100 flex items-center">
                                    <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">3</span><h4 class="text-emerald-800 text-xs font-bold uppercase tracking-wider">Keuangan</h4>
                                </div>
                                <div class="p-3 space-y-3 flex-grow">
                                    <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Nama Bank</label><input type="text" name="bank_name" value="{{ old('bank_name', $client->bank_name) }}" class="w-full border-2 border-gray-300 focus:border-emerald-500 rounded-md text-sm px-3 py-1.5"></div>
                                    <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">No. Rekening</label><input type="text" name="bank_account_number" value="{{ old('bank_account_number', $client->bank_account_number) }}" class="w-full border-2 border-gray-300 focus:border-emerald-500 rounded-md text-sm px-3 py-1.5 font-mono"></div>
                                    <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Atas Nama</label><input type="text" name="bank_account_name" value="{{ old('bank_account_name', $client->bank_account_name) }}" class="w-full border-2 border-gray-300 focus:border-emerald-500 rounded-md text-sm px-3 py-1.5"></div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-emerald-700 mb-1 uppercase">Komisi / Rate (%)</label>
                                        <div class="relative">
                                            <input type="number" step="0.01" name="commission_rate" value="{{ old('commission_rate', $client->commission_rate) }}" class="w-full border-2 border-emerald-100 bg-emerald-50/30 rounded-md text-sm font-bold text-emerald-800 focus:border-emerald-500 px-3 py-1.5" placeholder="Misal: 2.5">
                                            <span class="absolute right-3 top-2 text-emerald-600 font-bold text-xs">%</span>
                                        </div>
                                    </div>
                                    <div class="mt-auto pt-3 border-t border-emerald-50">
                                        <label class="block text-[10px] font-bold text-emerald-700 mb-1 uppercase">Saldo Awal</label>
                                        <div class="relative"><span class="absolute left-3 top-2 text-emerald-600 font-bold text-xs">Rp</span><input type="number" name="opening_balance" value="{{ old('opening_balance', $client->opening_balance) }}" class="w-full pl-8 border-2 border-emerald-100 bg-emerald-50/30 rounded-md text-lg font-bold text-emerald-800 focus:border-emerald-500 px-3 py-1.5"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3 border-t border-gray-200 flex justify-end gap-3 shrink-0">
                        <button type="button" onclick="toggleModal('editClientModal')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-bold hover:bg-gray-100">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-bold shadow-md"><i class="fas fa-save mr-2"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL EDIT TRANSAKSI --}}
        <div id="editTransactionModal" class="hidden fixed inset-0 bg-gray-900/10 z-[9999] flex items-center justify-center p-4 backdrop-blur-md transition-opacity duration-300">
            {{-- MAX-W-MD agar lebih kecil dan rapi --}}
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                <div id="editTransHeader" class="bg-gray-100 px-5 py-3 border-b border-gray-300 flex justify-between items-center">
                    <h3 class="font-bold text-lg text-gray-800 flex items-center"><i class="fas fa-edit mr-3"></i> Edit Transaksi</h3>
                    <button onclick="toggleModal('editTransactionModal')" class="text-gray-500 hover:text-red-500 transition text-2xl font-bold">&times;</button>
                </div>
                <form id="formEditTransaction" action="#" method="POST" class="p-5 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Tanggal</label>
                        <input type="date" name="interaction_date" id="edit_tanggal" class="w-full border-2 border-gray-300 rounded-md shadow-sm focus:border-blue-500 px-3 py-2 text-sm" required>
                    </div>
                    <div id="wrapper_produk">
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase" id="label_produk">Nama Produk / Keperluan</label>
                        <input type="text" name="" id="edit_produk" class="w-full border-2 border-gray-300 rounded-md shadow-sm focus:border-blue-500 px-3 py-2 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Nominal (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500 text-sm">Rp</span>
                            <input type="text" name="" id="edit_nominal" onkeyup="formatRupiah(this)" class="w-full pl-9 border-2 border-gray-300 rounded-md shadow-sm focus:border-blue-500 px-3 py-2 font-mono font-bold text-lg" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1 uppercase">Catatan</label>
                        <textarea name="notes" id="edit_catatan" rows="2" class="w-full border-2 border-gray-300 rounded-md shadow-sm focus:border-blue-500 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="pt-2 flex justify-end gap-3">
                        <button type="button" onclick="toggleModal('editTransactionModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-bold hover:bg-gray-200">Batal</button>
                        <button type="submit" id="btnUpdateTrans" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-bold hover:bg-blue-700 shadow-md flex items-center"><i class="fas fa-save mr-2"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL DETAIL TRANSAKSI (READ-ONLY) --}}
        <div id="viewTransactionModal" class="hidden fixed inset-0 bg-gray-900/10 z-[9999] flex items-center justify-center p-4 backdrop-blur-md transition-opacity duration-300">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                <div id="viewTransHeader" class="bg-blue-600 px-5 py-4 text-white flex justify-between items-center">
                    <h3 class="font-bold text-lg flex items-center"><i class="fas fa-info-circle mr-3"></i> Detail Transaksi</h3>
                    <button onclick="toggleModal('viewTransactionModal')" class="text-white hover:text-gray-200 transition text-2xl font-bold">&times;</button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tanggal</span>
                            <span id="view_tanggal" class="text-sm font-semibold text-gray-800"></span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tipe Transaksi</span>
                            <span id="view_jenis" class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold shadow-sm"></span>
                        </div>
                    </div>
                    
                    <div>
                        <span id="view_label_produk" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Produk / Keperluan</span>
                        <span id="view_produk" class="text-base font-bold text-gray-900 block mt-0.5"></span>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-100 grid grid-cols-2 gap-4">
                        <div>
                            <span id="view_label_nominal" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nominal</span>
                            <span id="view_nominal" class="text-lg font-mono font-bold text-gray-800"></span>
                        </div>
                        <div id="view_wrapper_commission" class="hidden">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Komisi (Rate)</span>
                            <span id="view_rate" class="text-xs font-bold text-blue-700"></span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catatan</span>
                        <p id="view_catatan" class="text-sm text-gray-600 bg-gray-50 border border-gray-100 rounded-lg p-3 mt-1 whitespace-pre-line italic"></p>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="button" onclick="toggleModal('viewTransactionModal')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-200 active:scale-95 transition">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL DETAIL TRANSAKSI BULANAN --}}
        <div id="monthlyDetailModal" class="hidden fixed inset-0 bg-gray-900/10 z-[9999] flex items-center justify-center p-4 backdrop-blur-md transition-opacity duration-300">
            <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden border border-gray-200 flex flex-col max-h-[85vh]">
                <div class="bg-gray-800 px-6 py-4 text-white flex justify-between items-center shrink-0">
                    <h3 class="font-bold text-lg flex items-center"><i class="far fa-calendar-alt mr-3"></i> <span id="monthly_detail_title">Detail Transaksi Bulanan</span></h3>
                    <button onclick="toggleModal('monthlyDetailModal')" class="text-white hover:text-gray-200 transition text-2xl font-bold">&times;</button>
                </div>
                <div class="p-6 overflow-y-auto flex-grow">
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-100 text-gray-600 uppercase text-xs font-bold tracking-wider border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 w-28">Tanggal</th>
                                    <th class="px-4 py-3 w-28">Tipe</th>
                                    <th class="px-4 py-3">Keterangan / Produk</th>
                                    <th class="px-4 py-3 text-right w-36">Nominal</th>
                                </tr>
                            </thead>
                            <tbody id="monthly_detail_body" class="divide-y divide-gray-100">
                                {{-- Diisi dinamis via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end shrink-0">
                    <button type="button" onclick="toggleModal('monthlyDetailModal')" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-100 active:scale-95 transition">Tutup</button>
                </div>
            </div>
        </div>
    @endpush

    {{-- Script untuk Modal --}}
    @push('scripts')
    <script>
        const clientInteractions = @json($client->interactions);

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow = 'hidden'; 
            } else {
                modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.style.overflow = 'auto'; 
            }
        }

        function showMonthlyDetail(monthName, monthNum, year) {
            // Filter interactions for this month and year
            const filtered = clientInteractions.filter(item => {
                const date = new Date(item.interaction_date);
                return date.getFullYear() === year && (date.getMonth() + 1) === monthNum;
            });
            
            // Sort by date ascending
            filtered.sort((a, b) => new Date(a.interaction_date) - new Date(b.interaction_date));
            
            document.getElementById('monthly_detail_title').innerText = `Detail Transaksi - ${monthName} ${year}`;
            
            const tbody = document.getElementById('monthly_detail_body');
            tbody.innerHTML = '';
            
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-gray-400">Tidak ada transaksi pada bulan ini.</td></tr>';
            } else {
                filtered.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.className = "hover:bg-gray-50 transition border-b border-gray-100";
                    
                    // Format Date
                    const dateObj = new Date(item.interaction_date);
                    const formattedDate = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    
                    // Type Badge
                    let typeBadge = '';
                    let details = `<strong>${item.product_name}</strong>`;
                    let nominal = 0;
                    
                    if (item.transaction_type === 'IN') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Sales (IN)</span>';
                        nominal = item.sales_amount > 0 ? item.sales_amount : item.amount;
                        
                        // Rate from commission_rate column (single source of truth)
                        let rate = parseFloat(item.commission_rate ?? 0) || 0;
                        const note = item.notes || '';
                        if (rate > 0) {
                            const valueNet = nominal * (rate / 100);
                            details += `<div class="text-[10px] text-gray-500 italic mt-0.5">${note}</div>`;
                            details += `<div class="text-[10px] text-blue-700 font-semibold mt-0.5">Rate: ${rate}% (Net: Rp ${valueNet.toLocaleString('id-ID')})</div>`;
                        } else if (note) {
                            details += `<div class="text-[10px] text-gray-500 italic mt-0.5">${note}</div>`;
                        }
                    } else if (item.transaction_type === 'OUT') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Support (OUT)</span>';
                        nominal = item.amount;
                        if (item.notes) {
                            details += `<div class="text-[10px] text-gray-500 italic mt-0.5">${item.notes}</div>`;
                        }
                    } else if (item.transaction_type === 'ENTERTAIN') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800">Aktivitas</span>';
                        nominal = item.amount;
                        if (item.notes) {
                            details += `<div class="text-[10px] text-gray-500 italic mt-0.5">${item.notes}</div>`;
                        }
                    }
                    
                    tr.innerHTML = `
                        <td class="px-4 py-3 font-semibold text-gray-700">${formattedDate}</td>
                        <td class="px-4 py-3">${typeBadge}</td>
                        <td class="px-4 py-3">${details}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-gray-800">Rp ${parseFloat(nominal).toLocaleString('id-ID')}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
            
            toggleModal('monthlyDetailModal');
        }

        function toggleDirectUsageDesktop(checkbox) {
            const container = document.getElementById('bank-info-container-desktop');
            const inputs = container.querySelectorAll('input');
            const submitBtn = document.getElementById('submit-btn-support-desktop');
            
            if (checkbox.checked) {
                container.classList.add('hidden');
                inputs.forEach(input => {
                    input.removeAttribute('required');
                });
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Catat Dana';
                submitBtn.className = "w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition transform active:scale-95 text-sm md:text-base flex items-center justify-center gap-2";
            } else {
                container.classList.remove('hidden');
                inputs.forEach(input => {
                    input.setAttribute('required', 'required');
                });
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Ajukan Dana';
                submitBtn.className = "w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition transform active:scale-95 text-sm md:text-base flex items-center justify-center gap-2";
            }
        }

        function openViewTransactionModal(data) {
            document.getElementById('view_tanggal').innerText = data.tanggal;
            document.getElementById('view_produk').innerText = data.produk;
            document.getElementById('view_catatan').innerText = data.catatan || '-';
            
            const header = document.getElementById('viewTransHeader');
            const jenisBadge = document.getElementById('view_jenis');
            const wrapperCommission = document.getElementById('view_wrapper_commission');
            const labelNominal = document.getElementById('view_label_nominal');
            const labelProduk = document.getElementById('view_label_produk');

            // Default hidden
            wrapperCommission.classList.add('hidden');
            
            if (data.jenis === 'IN') {
                header.className = "bg-blue-600 px-5 py-4 text-white flex justify-between items-center";
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 shadow-sm";
                jenisBadge.innerText = "Sales (IN)";
                labelNominal.innerText = "Nilai Sales (Gross)";
                labelProduk.innerText = "Nama Produk";
                
                // Show commission
                wrapperCommission.classList.remove('hidden');
                document.getElementById('view_rate').innerText = data.rate + '% (Net: Rp ' + data.valueNet + ')';
                document.getElementById('view_nominal').innerText = 'Rp ' + data.nominal;
            } else if (data.jenis === 'OUT') {
                header.className = "bg-red-600 px-5 py-4 text-white flex justify-between items-center";
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 shadow-sm";
                jenisBadge.innerText = "Support (OUT)";
                labelNominal.innerText = "Nominal Support";
                labelProduk.innerText = "Keperluan Support";
                
                document.getElementById('view_nominal').innerText = 'Rp ' + data.nominal;
            } else if (data.jenis === 'ENTERTAIN') {
                header.className = "bg-orange-500 px-5 py-4 text-white flex justify-between items-center";
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-800 shadow-sm";
                jenisBadge.innerText = "Aktivitas (ENTERTAIN)";
                labelNominal.innerText = "Biaya Aktivitas";
                labelProduk.innerText = "Keterangan Aktivitas";

                document.getElementById('view_nominal').innerText = 'Rp ' + data.nominal;
            }
            
            toggleModal('viewTransactionModal');
        }

        function openEditTransactionModal(data) {
            let baseUrl = "{{ (request()->is('admin/*') ? url('admin/crm/interaction') : url('crm/interaction')) }}"; 
            let form = document.getElementById('formEditTransaction');
            form.action = baseUrl + "/" + data.id + "/update"; 

            document.getElementById('edit_tanggal').value = data.tanggal; 
            document.getElementById('edit_catatan').value = data.catatan;
            
            let nominalVal = parseInt(data.nominal).toLocaleString('id-ID');
            document.getElementById('edit_nominal').value = nominalVal;

            let header = document.getElementById('editTransHeader');
            let btn = document.getElementById('btnUpdateTrans');
            let wrapperProduk = document.getElementById('wrapper_produk'); 
            let labelProduk = document.getElementById('label_produk');
            let inputProduk = document.getElementById('edit_produk');
            let inputNominal = document.getElementById('edit_nominal');

            wrapperProduk.classList.remove('hidden');
            inputProduk.setAttribute('required', 'required');

            if (data.jenis === 'IN') {
                setupModalStyle('blue', 'Edit Sales');
                labelProduk.innerText = "Nama Produk";
                inputProduk.name = "product_name";
                inputProduk.value = data.produk; 
                inputNominal.name = "sales_amount"; 
            } else if (data.jenis === 'OUT') {
                setupModalStyle('red', 'Edit Pengeluaran');
                labelProduk.innerText = "Keperluan Support";
                inputProduk.name = "purpose";
                inputProduk.value = data.produk.replace('USAGE : ', '');
                inputNominal.name = "amount"; 
            } else if (data.jenis === 'ENTERTAIN') {
                setupModalStyle('orange', 'Edit Aktivitas');
                wrapperProduk.classList.add('hidden');
                inputProduk.removeAttribute('required');
                inputNominal.name = "amount"; 
            }

            function setupModalStyle(color, title) {
                header.className = `bg-${color}-600 px-5 py-3 border-b border-${color}-500 flex justify-between items-center`;
                header.querySelector('h3').className = "font-bold text-lg text-white flex items-center";
                header.querySelector('h3').innerHTML = `<i class="fas fa-edit mr-3"></i> ${title}`;
                header.querySelector('button').className = `text-white hover:text-${color}-200 transition text-2xl font-bold`;
                btn.className = `px-4 py-2 bg-${color}-600 text-white rounded-md text-sm font-bold hover:bg-${color}-700 shadow-md flex items-center`;
                inputNominal.className = `w-full pl-9 border-2 border-gray-300 rounded-md shadow-sm focus:border-${color}-500 px-3 py-2 font-mono font-bold text-lg`;
            }

            toggleModal('editTransactionModal');
        }

        window.onclick = function(event) {
            const modalClient = document.getElementById('editClientModal');
            const modalTrans = document.getElementById('editTransactionModal');
            const modalUsageInfo = document.getElementById('usage-info-modal');
            const modalActivityInfo = document.getElementById('activity-info-modal');
            if (event.target == modalClient) toggleModal('editClientModal');
            if (event.target == modalTrans) toggleModal('editTransactionModal');
            if (event.target == modalUsageInfo) closeUsageInfoModal();
            if (event.target == modalActivityInfo) closeActivityInfoModal();
        }

        function switchTab(tabName) {
            const sections = { 'sales': 'section-sales', 'support': 'section-support', 'activity': 'section-activity', 'history': 'section-history', 'recap': 'section-recap' };
            const buttons = { 'sales': 'btn-sales', 'support': 'btn-support', 'activity': 'btn-activity', 'history': 'btn-history', 'recap': 'btn-recap' };
            const inactiveClass = "nav-btn px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none text-gray-500 hover:text-gray-700 hover:bg-gray-50";
            const activeBase = "nav-btn px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 focus:outline-none shadow-md ring-1 ring-inset";

            for (const k in sections) {
                document.getElementById(sections[k]).classList.add('hidden');
                document.getElementById(buttons[k]).className = inactiveClass;
            }
            document.getElementById(sections[tabName]).classList.remove('hidden');
            
            let specificActiveClass = "";
            if (tabName === 'sales') specificActiveClass = "bg-blue-600 text-white ring-blue-700";
            else if (tabName === 'support') specificActiveClass = "bg-red-600 text-white ring-red-700";
            else if (tabName === 'activity') specificActiveClass = "bg-orange-500 text-white ring-orange-600";
            else specificActiveClass = "bg-gray-800 text-white ring-gray-900";
            
            document.getElementById(buttons[tabName]).className = activeBase + " " + specificActiveClass;
            localStorage.setItem('activeTab', tabName);
        }

        function openUsageInfoModal() {
            const modal = document.getElementById('usage-info-modal');
            const card = document.getElementById('usage-info-modal-card');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
            card.classList.remove('scale-90');
            card.classList.add('scale-100');
            document.body.style.overflow = 'hidden';
        }

        function closeUsageInfoModal() {
            const modal = document.getElementById('usage-info-modal');
            const card = document.getElementById('usage-info-modal-card');
            modal.classList.remove('opacity-100', 'pointer-events-auto');
            modal.classList.add('opacity-0', 'pointer-events-none');
            card.classList.remove('scale-100');
            card.classList.add('scale-90');
            const editClientModal = document.getElementById('editClientModal');
            const editTransModal = document.getElementById('editTransactionModal');
            if (editClientModal.classList.contains('hidden') && editTransModal.classList.contains('hidden')) {
                document.body.style.overflow = 'auto';
            }
        }

        function openActivityInfoModal() {
            const modal = document.getElementById('activity-info-modal');
            const card = document.getElementById('activity-info-modal-card');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
            card.classList.remove('scale-90');
            card.classList.add('scale-100');
            document.body.style.overflow = 'hidden';
        }

        function closeActivityInfoModal() {
            const modal = document.getElementById('activity-info-modal');
            const card = document.getElementById('activity-info-modal-card');
            modal.classList.remove('opacity-100', 'pointer-events-auto');
            modal.classList.add('opacity-0', 'pointer-events-none');
            card.classList.remove('scale-100');
            card.classList.add('scale-90');
            const editClientModal = document.getElementById('editClientModal');
            const editTransModal = document.getElementById('editTransactionModal');
            if (editClientModal.classList.contains('hidden') && editTransModal.classList.contains('hidden')) {
                document.body.style.overflow = 'auto';
            }
        }

        function formatRupiah(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            if (value) value = parseInt(value, 10).toLocaleString('id-ID');
            input.value = value;
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('year') || urlParams.get('tab') === 'recap') switchTab('recap');
        else if (urlParams.has('history_year') || urlParams.get('tab') === 'history') switchTab('history');
        else if (urlParams.has('activity_year') || urlParams.get('tab') === 'activity') switchTab('activity');
        else document.addEventListener("DOMContentLoaded", () => switchTab(localStorage.getItem('activeTab') || 'sales'));

        function fetchSalesData() {
            const dateInput = document.getElementById('tanggal_interaksi_in').value;
            const salesCustomer = document.getElementById('client_id_in').value;
            
            if (!salesCustomer) {
                alert('Silakan pilih Rumah Sakit terlebih dahulu.');
                return;
            }
            if (!dateInput) {
                alert('Silakan pilih Bulan Transaksi terlebih dahulu.');
                return;
            }
            
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            btn.disabled = true;

            fetch(`/crm/client/{{ $client->id }}/fetch-sales?date=${dateInput}&sales_customer=${encodeURIComponent(salesCustomer)}`)
                .then(res => res.json())
                .then(resData => {
                    if (resData.success && resData.data.length > 0) {
                        const container = document.getElementById('product_rows_container_desktop');
                        container.innerHTML = ''; // Hapus antrean sebelumnya
                        
                        let countAdded = 0;
                        resData.data.forEach((item) => {
                            addProductRow('desktop', item.product_name, item.sales_amount, (item.date || dateInput), item.client_id, item.customer_name);
                            countAdded++;
                        });

                        document.getElementById('queue_count_desktop').innerText = countAdded;
                        if (resData.truncated) {
                            alert(`Menampilkan 500 dari ${resData.total} baris — persempit bulan/RS bila data kurang lengkap.`);
                        }
                    } else {
                        alert(resData.message || 'Gagal menarik data sales.');
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan koneksi.');
                    console.error(err);
                })
                .finally(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                });
        }

        function addProductRow(view, produk = '', nominal = '', date = '', clientId = '', clientName = '') {
            const containerId = view === 'mobile' ? 'product_rows_container_mobile' : 'product_rows_container_desktop';
            const container = document.getElementById(containerId);
            const row = document.createElement('div');
            
            let valNominal = nominal ? parseInt(nominal, 10).toLocaleString('id-ID') : '';

            row.className = 'product-row flex items-center gap-3 bg-blue-50/50 p-3 rounded-lg border border-blue-100 hover:shadow-sm transition-all';
            row.innerHTML = `
                <input type="hidden" name="client_id[]" value="${clientId}">
                <div class="flex-1 max-w-[120px]">
                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="interaction_date[]" class="w-full border border-gray-200 bg-gray-50 rounded-md shadow-sm px-2 py-1.5 text-[11px] text-gray-500 font-semibold" value="${date}" readonly>
                </div>
                <div class="flex-1 max-w-[150px]">
                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Rumah Sakit</label>
                    <input type="text" class="w-full border border-gray-200 bg-gray-50 rounded-md shadow-sm px-2 py-1.5 text-[11px] text-gray-500 font-semibold" value="${clientName}" readonly>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="product_name[]" class="w-full border border-gray-200 bg-gray-50 rounded-md shadow-sm px-3 py-1.5 text-sm text-gray-600" value="${produk}" readonly>
                </div>
                <div class="flex-1 max-w-[160px]">
                    <label class="block text-[10px] font-bold text-gray-700 mb-1">Nilai Sales (Rp) <span class="text-red-500">*</span></label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 text-xs font-bold">Rp</span></div>
                        <input type="text" name="sales_amount[]" class="w-full border border-gray-200 bg-gray-50 rounded-md shadow-sm pl-9 pr-3 py-1.5 font-mono text-sm font-bold text-gray-600" value="${valNominal}" readonly>
                    </div>
                </div>
                <div>
                    <button type="button" onclick="removeProductRow(this)" class="text-red-500 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition" title="Hapus Baris"><i class="fas fa-trash-alt"></i></button>
                </div>
            `;
            container.appendChild(row);
        }

        function removeProductRow(btn) {
            btn.closest('.product-row').remove();
            let badge = document.getElementById('queue_count_desktop');
            let current = parseInt(badge.innerText);
            if (current > 0) badge.innerText = current - 1;
        }

        function updateFileNameSupportDesktop(input) {
            const label = document.getElementById('file-name-support-desktop');
            if (input.files && input.files[0]) {
                label.innerText = input.files[0].name;
            } else {
                label.innerText = 'Belum ada berkas dipilih...';
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
    @endpush

</x-layout-users>
