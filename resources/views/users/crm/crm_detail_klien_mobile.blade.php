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
                                <i class="fas fa-hospital mr-1.5 opacity-80"></i> {{ $client->nama_perusahaan }}
                            </span>
                            <div class="hidden sm:block w-1 h-1 rounded-full bg-blue-300/50"></div>
                            <div class="flex items-center text-[10px] text-blue-100 font-medium">
                                <i class="fas fa-map-marker-alt mr-1.5 text-blue-300"></i> {{ $client->area ?? 'Belum set Area' }}
                            </div>
                            <div class="hidden sm:block w-1 h-1 rounded-full bg-blue-300/50"></div>
                            <div class="flex items-center text-[10px] text-blue-100 font-medium">
                                <i class="fas fa-user-tie mr-1.5 text-blue-300"></i> PIC: {{ $client->pic }}
                            </div>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white drop-shadow-sm leading-tight pt-1">
                            {{ $client->nama_user }}
                        </h2>
                    </div>

                    {{-- Kanan: Statistik & Aksi --}}
                    <div class="flex flex-wrap sm:flex-nowrap items-center justify-start xl:justify-end gap-3 w-full xl:w-auto mt-2 xl:mt-0">
                        
                        <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md rounded-xl px-4 py-2 border border-white/20 shadow-sm flex-1 sm:flex-initial justify-between sm:justify-start">
                            <div class="flex flex-col">
                                <span class="text-blue-200 text-[8px] font-bold uppercase tracking-wider mb-0.5">Total Realisasi</span>
                                <div class="flex items-start">
                                    <span class="text-[9px] text-blue-100 mr-1 mt-0.5">Rp</span>
                                    <span class="text-base md:text-lg font-bold text-white leading-none">{{ number_format($client->interactions->where('jenis_transaksi', 'OUT')->sum('nilai_kontribusi'), 0, ',', '.') }}</span>
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
                    @if($client->jabatan)
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-blue-300"><i class="fas fa-id-badge"></i></div>
                        <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Jabatan</p><p class="font-bold text-sm tracking-wide">{{ $client->jabatan }}</p></div>
                    </div>
                    @endif
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-blue-200"><i class="fas fa-envelope"></i></div>
                        <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Email</p><p class="font-medium text-sm break-all">{{ $client->email ?? '-' }}</p></div>
                    </div>
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-green-300"><i class="fab fa-whatsapp text-lg -ml-0.5"></i></div>
                        <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Telepon / WA</p><p class="font-medium text-sm">{{ $client->no_telpon ?? '-' }}</p></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex items-start relative pl-8">
                            <div class="absolute left-0 top-1 text-pink-200"><i class="fas fa-birthday-cake"></i></div>
                            <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Tgl Lahir</p><div class="font-medium text-sm">{{ $client->tanggal_lahir ? \Carbon\Carbon::parse($client->tanggal_lahir)->format('d M Y') : '-' }}</div></div>
                        </div>
                        <div class="flex items-start relative pl-6">
                            <div class="absolute left-0 top-1 text-yellow-300"><i class="fas fa-star"></i></div>
                            <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Hobi</p><p class="font-medium text-sm">{{ $client->hobby_client ?? '-' }}</p></div>
                        </div>
                    </div>
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-white/50"><i class="fas fa-home"></i></div>
                        <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Alamat Rumah</p><p class="text-sm leading-relaxed opacity-90">{{ $client->alamat_user ?? '-' }}</p></div>
                    </div>
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-emerald-300"><i class="fas fa-percent"></i></div>
                        <div><p class="text-[10px] text-blue-200 font-bold uppercase mb-0.5">Komisi / Rate</p><p class="font-medium text-sm">{{ $client->komisi ? (float)$client->komisi . '%' : '-' }}</p></div>
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
                        <p class="font-bold text-lg leading-tight">{{ $client->nama_perusahaan }}</p>
                    </div>
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-white/80"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <p class="text-[10px] text-orange-200 font-bold uppercase mb-0.5">Tanggal Berdiri</p>
                            <div class="flex items-center font-medium text-sm">
                                @if($client->tanggal_berdiri)
                                    <span>{{ \Carbon\Carbon::parse($client->tanggal_berdiri)->format('d F Y') }}</span>
                                    <span class="ml-2 text-[10px] bg-white text-orange-600 px-2 py-0.5 rounded-full font-bold shadow-sm">{{ \Carbon\Carbon::parse($client->tanggal_berdiri)->age }} Th</span>
                                @else <span class="italic opacity-70">Belum diisi</span> @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start relative pl-8">
                        <div class="absolute left-0 top-1 text-white/80"><i class="fas fa-map-marked-alt"></i></div>
                        <div><p class="text-[10px] text-orange-200 font-bold uppercase mb-0.5">Alamat Kantor</p><p class="text-sm leading-relaxed opacity-90">{{ $client->alamat_perusahaan ?? '-' }}</p></div>
                    </div>

                    {{-- Data Apoteker --}}
                    <div class="mt-4 pt-3 border-t border-white/20">
                        <p class="text-[10px] text-orange-200 font-bold uppercase mb-2"><i class="fas fa-user-md mr-1"></i> Data Apoteker Penanggung Jawab</p>
                        <div class="grid grid-cols-2 gap-y-2 text-sm">
                            <div><p class="text-[9px] text-orange-300 uppercase">Nama</p><p class="font-bold">{{ $client->nama_apoteker ?? '-' }}</p></div>
                            <div><p class="text-[9px] text-orange-300 uppercase">SIPA</p><p class="font-bold">{{ $client->nomor_sipa ?? '-' }}</p></div>
                            <div class="col-span-2"><p class="text-[9px] text-orange-300 uppercase">No. Telp</p><p class="font-bold">{{ $client->no_telpon_apoteker ?? '-' }}</p></div>
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
                            <span class="font-bold text-2xl tracking-wide text-white leading-tight mb-1">{{ $client->bank ?? 'BANK -' }}</span>
                            <p class="text-xs text-gray-400 mb-2">{{ $client->nama_di_rekening ? 'A/n '.$client->nama_di_rekening : '' }}</p>
                            <div class="flex items-center gap-2 font-mono text-emerald-400 tracking-widest text-base bg-white/5 px-3 py-1.5 rounded-lg w-fit border border-white/5 shadow-inner">
                                <i class="fas fa-credit-card text-xs opacity-70"></i> <span class="font-bold">{{ $client->no_rekening ?? '----' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-700/50 pt-3 mt-auto">
                        <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Saldo Awal</p>
                        <p class="text-3xl font-mono font-bold text-emerald-400 tracking-tight text-shadow-sm flex items-baseline">
                            <span class="text-sm text-gray-500 mr-2 font-normal">IDR</span> {{ number_format($client->saldo_awal ?? 0, 0, ',', '.') }}
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
                <form action="{{ route($routePrefix . 'interaction.store') }}" method="POST">
                    <div class="grid grid-cols-1 gap-5 mb-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Rumah Sakit (Data Command Center) <span class="text-red-500">*</span></label>
                            <select id="client_id_in_mobile" class="w-full border-2 border-blue-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 py-2" required>
                                <option value="" disabled selected>Pilih Rumah Sakit...</option>
                                @foreach($salesCustomers as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Pilih Tanggal <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="date" id="tanggal_interaksi_in_mobile" class="w-full border-2 border-blue-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 py-2" required>
                                <button type="button" onclick="fetchSalesDataMobile()" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 font-bold px-3 py-2 rounded-lg shadow-sm transition whitespace-nowrap" title="Ambil data dari Command Center">
                                    <i class="fas fa-cloud-download-alt mr-1"></i> Tarik & Tambah
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Anda bisa menarik data dari beberapa tanggal & RS berbeda.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Catatan (Opsional)</label>
                            <input type="text" name="catatan" class="w-full border-2 border-blue-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-4 py-2" placeholder="Catatan opsional...">
                        </div>
                    </div>

                    <div class="mb-2 flex justify-between items-end border-b pb-2">
                        <h4 class="font-bold text-gray-700 text-sm flex items-center"><i class="fas fa-box-open mr-2 text-blue-500"></i> Antrean Data Sales <span id="queue_count_mobile" class="ml-2 bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">0</span></h4>
                    </div>

                    <div id="product_rows_container_mobile" class="space-y-4 mb-6 max-h-[45vh] overflow-y-auto custom-scrollbar pr-1">
                        <div id="empty_queue_msg_mobile" class="text-center py-6 text-gray-400 italic text-sm">
                            Belum ada data ditarik. Silakan pilih tanggal dan klik "Tarik & Tambah".
                        </div>
                    </div>

                    <datalist id="produk-list-mobile">
                        @foreach($productNames as $prod)
                            <option value="{{ $prod }}"></option>
                        @endforeach
                    </datalist>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition transform active:scale-95 text-base flex justify-center items-center gap-2"><i class="fas fa-save"></i> Simpan Data Sales</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 2. INPUT SUPPORT --}}
        <div id="section-support" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden relative hidden">
            <div class="bg-red-600 px-6 py-4 border-b border-red-100 flex justify-between items-center">
                <h3 class="font-bold text-white text-lg flex items-center"><span class="w-8 h-8 bg-white text-red-600 rounded-lg flex items-center justify-center mr-3 text-sm shadow"><i class="fas fa-hand-holding-usd"></i></span> Pengeluaran</h3>
            </div>
            <div class="p-5 md:p-6">
                <form action="{{ route($routePrefix . 'interaction.support') }}" method="POST" onsubmit="return checkBankDataSupport(event)">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
                        <!-- Baris 1: Tanggal & Nominal -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_interaksi" class="w-full border-2 border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-3 py-2 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nominal Keluar (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 text-xs font-bold">Rp</span>
                                </div>
                                <input type="text" name="nominal" onkeyup="formatRupiah(this)" class="w-full border-2 border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 pl-9 px-3 py-2 font-mono font-bold text-base text-red-700" placeholder="0" required>
                            </div>
                        </div>

                        <!-- Baris 2: Keperluan & Button -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Keperluan Support <span class="text-red-500">*</span></label>
                            <input type="text" name="keperluan" class="w-full border-2 border-red-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 px-3 py-2 text-sm" placeholder="Contoh: Transport" required>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition transform active:scale-95 text-sm flex items-center justify-center gap-2 h-[40px]"><i class="fas fa-paper-plane"></i> Ajukan Dana</button>
                        </div>
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
                </div>
                <div class="p-6 md:p-8">
                    <form action="{{ route($routePrefix . 'interaction.entertain') }}" method="POST">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="space-y-4">
                                <div><label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Tanggal Kegiatan <span class="text-red-500">*</span></label><input type="date" name="tanggal_interaksi" class="w-full border-2 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 px-3 py-2" required></div>
                                <div><label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Lokasi / Venue</label><div class="relative"><div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-map-marker-alt text-xs"></i></div><input type="text" name="lokasi" class="w-full border-2 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pl-8 px-3 py-2" placeholder="Contoh: Restoran X"></div></div>
                                <div><label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Partisipan / Klien</label><div class="relative"><div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-users text-xs"></i></div><input type="text" name="peserta" class="w-full border-2 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pl-8 px-3 py-2" placeholder="Sebutkan nama..."></div></div>
                            </div>
                            <div class="space-y-4 bg-orange-50/40 p-5 rounded-xl border border-orange-100 flex flex-col h-full">
                                <div class="flex-grow"><label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Keterangan <span class="text-red-500">*</span></label><textarea name="catatan" rows="3" class="w-full border-2 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 px-3 py-2 text-sm" placeholder="Contoh: Makan siang membahas proyek baru, dll." required></textarea></div>
                                <div><label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Nominal Biaya (Rp) <span class="text-red-500">*</span></label><div class="relative rounded-md shadow-sm"><div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-gray-500 sm:text-xs font-bold">Rp</span></div><input type="text" name="nominal" onkeyup="formatRupiah(this)" class="w-full border-2 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pl-8 px-3 py-2 font-mono font-bold text-lg text-orange-700" placeholder="0" required></div></div>
                                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md transition transform active:scale-95 flex items-center justify-center gap-2 mt-2"><i class="fas fa-save"></i> Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABEL AKTIVITAS + FILTER TAHUN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                @php
                    $totalActivity = $activities->sum('nilai_kontribusi');
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
                                <th class="px-5 py-3">Lokasi & Partisipan</th>
                                <th class="px-5 py-3 text-right">Biaya</th>
                                <th class="px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($activities as $act)
                            <tr class="hover:bg-orange-50/30 transition">
                                <td class="px-5 py-3 whitespace-nowrap font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($act->tanggal_interaksi)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-bold text-gray-800">{{ $act->nama_produk }}</div>
                                    <div class="text-xs text-gray-500">{{ $act->catatan }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center text-xs text-gray-600 mb-0.5">
                                        <i class="fas fa-map-marker-alt w-4 text-center mr-1 text-gray-400"></i> {{ $act->lokasi ?? '-' }}
                                    </div>
                                    <div class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-users w-4 text-center mr-1 text-gray-400"></i> {{ $act->peserta ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-orange-600">
                                    {{ number_format($act->nilai_kontribusi, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" 
                                            onclick="openEditTransactionModal({
                                                id: '{{ $act->id }}',
                                                jenis: 'ENTERTAIN',
                                                tanggal: '{{ $act->tanggal_interaksi }}',
                                                produk: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->nama_produk)) }}', 
                                                nominal: '{{ $act->nilai_kontribusi }}',
                                                catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->catatan)) }}',
                                                lokasi: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->lokasi ?? '')) }}',
                                                peserta: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->peserta ?? '')) }}'
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
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400 italic bg-gray-50/50">Belum ada data aktivitas</td></tr>
                            @endforelse
                        </tbody>
                        @if($activities->count() > 0)
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="px-5 py-3 text-right font-bold text-gray-600 uppercase text-xs tracking-wider">Total Pengeluaran Entertain</td>
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
                            <th class="px-4 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($interactions as $item)
                            @if($item->jenis_transaksi == 'ENTERTAIN') @continue @endif
                            @php
                                $isOut = $item->jenis_transaksi == 'OUT';
                                $rate = 0; if(preg_match('/\[Rate:([\d\.]+)\]/', $item->catatan, $m)) { $rate = $m[1]; }
                                $displayNote = trim(preg_replace('/\[Rate:[\d\.]+\]/', '', $item->catatan));
                                $valueNet = (!$isOut) ? ($item->nilai_kontribusi * ($rate/100)) : 0;
                            @endphp
                        <tr class="{{ $isOut ? 'bg-red-50/50' : 'hover:bg-blue-50/50' }} transition">
                            <td class="px-4 py-3 whitespace-nowrap font-bold text-gray-700">{{ \Carbon\Carbon::parse($item->tanggal_interaksi)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3"><div class="font-bold {{ $isOut ? 'text-red-800' : 'text-blue-900' }}">{{ $item->nama_produk }}</div><div class="text-xs text-gray-500 italic">{{ $displayNote }}</div></td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">{{ (!$isOut) ? number_format($item->nilai_kontribusi, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-center">@if(!$isOut && $rate > 0) <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded text-xs font-bold shadow-sm border border-gray-300">{{ $rate }}%</span> @else <span class="text-gray-300">-</span> @endif</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-blue-700">{{ (!$isOut) ? number_format($valueNet, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-red-600">{{ $isOut ? number_format($item->nilai_kontribusi, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="openEditTransactionModal({id: '{{ $item->id }}', jenis: '{{ $item->jenis_transaksi }}', tanggal: '{{ $item->tanggal_interaksi }}', produk: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $item->nama_produk)) }}', nominal: '{{ ($item->jenis_transaksi == 'IN') ? $item->nilai_sales : $item->nilai_kontribusi }}', rate: '{{ $rate }}', catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $displayNote)) }}'})" class="text-blue-400 hover:text-blue-600 transition" title="Edit Data"><i class="fas fa-edit"></i></button>
                                    <form action="{{ route($routePrefix . 'interaction.destroy', $item->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Hapus transaksi ini?');" class="inline">@csrf @method('DELETE')<button class="text-gray-300 hover:text-red-600 transition" title="Hapus Data"><i class="fas fa-trash-alt"></i></button></form>
                                </div>
                            </td>
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
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-bold text-gray-700">{{ $r['month_name'] }}</td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">{{ $r['gross_in'] > 0 ? number_format($r['gross_in'], 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-center font-mono text-xs text-gray-500">{{ $r['komisi_text'] }}</td>
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
                                            <input type="text" name="nama_user" value="{{ old('nama_user', $client->nama_user) }}" required class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md shadow-sm text-sm px-3 py-1.5 font-bold">
                                            <input type="text" name="jabatan" value="{{ old('jabatan', $client->jabatan) }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md shadow-sm text-xs px-3 py-1.5" placeholder="Jabatan">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Kontak Personal</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" name="no_telpon" value="{{ old('no_telpon', $client->no_telpon) }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md text-sm px-3 py-1.5" placeholder="WA">
                                            <input type="email" name="email" value="{{ old('email', $client->email) }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md text-sm px-3 py-1.5" placeholder="Email">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Tgl Lahir</label><input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($client->tanggal_lahir)->format('Y-m-d')) }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md text-sm px-3 py-1.5"></div>
                                        <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Hobi</label><input type="text" name="hobby_client" value="{{ old('hobby_client', $client->hobby_client) }}" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md text-sm px-3 py-1.5" placeholder="Hobi"></div>
                                    </div>
                                    <div class="flex-grow"><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Alamat Rumah</label><textarea name="alamat_user" rows="2" class="w-full border-2 border-gray-300 focus:border-blue-500 rounded-md text-sm px-3 py-1.5 resize-none">{{ old('alamat_user', $client->alamat_user) }}</textarea></div>
                                </div>
                            </div>
                            {{-- KOLOM 2 --}}
                            <div class="bg-white rounded-xl border border-orange-100 shadow-sm overflow-hidden flex flex-col h-full">
                                <div class="bg-orange-50/80 px-4 py-2 border-b border-orange-100 flex items-center">
                                    <span class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">2</span><h4 class="text-orange-800 text-xs font-bold uppercase tracking-wider">Data Perusahaan</h4>
                                </div>
                                <div class="p-3 space-y-3 flex-grow">
                                    <div><label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase">Nama Perusahaan <span class="text-red-500">*</span></label><input type="text" name="nama_perusahaan" value="{{ old('nama_perusahaan', $client->nama_perusahaan) }}" required class="w-full border-2 border-gray-300 focus:border-orange-500 rounded-md text-sm px-3 py-1.5 font-semibold"></div>
                                    <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Detail Perusahaan</label><div class="grid grid-cols-2 gap-2"><input type="text" name="area" value="{{ old('area', $client->area) }}" class="w-full border-2 border-gray-300 focus:border-orange-500 rounded-md text-sm px-3 py-1.5" placeholder="Area"><input type="date" name="tanggal_berdiri" value="{{ old('tanggal_berdiri', optional($client->tanggal_berdiri)->format('Y-m-d')) }}" class="w-full border-2 border-gray-300 focus:border-orange-500 rounded-md text-sm px-3 py-1.5"></div></div>
                                    <div class="flex-grow"><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Alamat Kantor</label><textarea name="alamat_perusahaan" rows="2" class="w-full border-2 border-gray-300 focus:border-orange-500 rounded-md text-sm px-3 py-1.5 resize-none">{{ old('alamat_perusahaan', $client->alamat_perusahaan) }}</textarea></div>
                                    <div class="mt-2 pt-2 border-t border-orange-100">
                                        <label class="block text-[11px] font-bold text-orange-700 mb-1 uppercase">Data Apoteker</label>
                                        <div class="space-y-2">
                                            <input type="text" name="nama_apoteker" value="{{ old('nama_apoteker', $client->nama_apoteker) }}" class="w-full border-2 border-gray-300 rounded-md text-xs focus:border-orange-500 px-3 py-1.5" placeholder="Nama Apoteker">
                                            <div class="grid grid-cols-2 gap-2">
                                                <input type="text" name="nomor_sipa" value="{{ old('nomor_sipa', $client->nomor_sipa) }}" class="w-full border-2 border-gray-300 rounded-md text-xs focus:border-orange-500 px-3 py-1.5" placeholder="Nomor SIPA">
                                                <input type="text" name="no_telpon_apoteker" value="{{ old('no_telpon_apoteker', $client->no_telpon_apoteker) }}" class="w-full border-2 border-gray-300 rounded-md text-xs focus:border-orange-500 px-3 py-1.5" placeholder="Telp Apoteker">
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
                                    <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Nama Bank</label><input type="text" name="bank" value="{{ old('bank', $client->bank) }}" class="w-full border-2 border-gray-300 focus:border-emerald-500 rounded-md text-sm px-3 py-1.5"></div>
                                    <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">No. Rekening</label><input type="text" name="no_rekening" value="{{ old('no_rekening', $client->no_rekening) }}" class="w-full border-2 border-gray-300 focus:border-emerald-500 rounded-md text-sm px-3 py-1.5 font-mono"></div>
                                    <div><label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Atas Nama</label><input type="text" name="nama_di_rekening" value="{{ old('nama_di_rekening', $client->nama_di_rekening) }}" class="w-full border-2 border-gray-300 focus:border-emerald-500 rounded-md text-sm px-3 py-1.5"></div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-emerald-700 mb-1 uppercase">Komisi / Rate (%)</label>
                                        <div class="relative">
                                            <input type="number" step="0.01" name="komisi" value="{{ old('komisi', $client->komisi) }}" class="w-full border-2 border-emerald-100 bg-emerald-50/30 rounded-md text-sm font-bold text-emerald-800 focus:border-emerald-500 px-3 py-1.5" placeholder="Misal: 2.5">
                                            <span class="absolute right-3 top-2 text-emerald-600 font-bold text-xs">%</span>
                                        </div>
                                    </div>
                                    <div class="mt-auto pt-3 border-t border-emerald-50">
                                        <label class="block text-[10px] font-bold text-emerald-700 mb-1 uppercase">Saldo Awal</label>
                                        <div class="relative"><span class="absolute left-3 top-2 text-emerald-600 font-bold text-xs">Rp</span><input type="number" name="saldo_awal" value="{{ old('saldo_awal', $client->saldo_awal) }}" class="w-full pl-8 border-2 border-emerald-100 bg-emerald-50/30 rounded-md text-lg font-bold text-emerald-800 focus:border-emerald-500 px-3 py-1.5"></div>
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
                        <input type="date" name="tanggal_interaksi" id="edit_tanggal" class="w-full border-2 border-gray-300 rounded-md shadow-sm focus:border-blue-500 px-3 py-2 text-sm" required>
                    </div>
                    <div id="wrapper_produk">
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase" id="label_produk">Nama Produk / Keperluan</label>
                        <input type="text" name="" id="edit_produk" class="w-full border-2 border-gray-300 rounded-md shadow-sm focus:border-blue-500 px-3 py-2 text-sm font-bold">
                    </div>
                    <div id="wrapper_entertain" class="hidden space-y-3">
                        <div><label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Lokasi / Venue</label><input type="text" name="lokasi" id="edit_lokasi" class="w-full border-2 border-gray-300 rounded-md shadow-sm focus:border-orange-500 px-3 py-2 text-sm" placeholder="Lokasi"></div>
                        <div><label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Partisipan / Klien</label><input type="text" name="peserta" id="edit_peserta" class="w-full border-2 border-gray-300 rounded-md shadow-sm focus:border-orange-500 px-3 py-2 text-sm" placeholder="Peserta"></div>
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
                        <textarea name="catatan" id="edit_catatan" rows="2" class="w-full border-2 border-gray-300 rounded-md shadow-sm focus:border-blue-500 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="pt-2 flex justify-end gap-3">
                        <button type="button" onclick="toggleModal('editTransactionModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-bold hover:bg-gray-200">Batal</button>
                        <button type="submit" id="btnUpdateTrans" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-bold hover:bg-blue-700 shadow-md flex items-center"><i class="fas fa-save mr-2"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    @endpush

    {{-- Script untuk Modal --}}
    @push('scripts')
    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow = 'hidden'; 
            } else {
                modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.style.overflow = 'auto'; 
            }
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
            let wrapperEntertain = document.getElementById('wrapper_entertain'); 
            let inputLokasi = document.getElementById('edit_lokasi');
            let inputPeserta = document.getElementById('edit_peserta');
            let inputNominal = document.getElementById('edit_nominal');

            wrapperProduk.classList.remove('hidden');
            wrapperEntertain.classList.add('hidden');
            inputProduk.setAttribute('required', 'required');

            if (data.jenis === 'IN') {
                setupModalStyle('blue', 'Edit Sales');
                labelProduk.innerText = "Nama Produk";
                inputProduk.name = "nama_produk";
                inputProduk.value = data.produk; 
                inputNominal.name = "nilai_sales"; 
            } else if (data.jenis === 'OUT') {
                setupModalStyle('red', 'Edit Pengeluaran');
                labelProduk.innerText = "Keperluan Support";
                inputProduk.name = "keperluan";
                inputProduk.value = data.produk.replace('USAGE : ', '');
                inputNominal.name = "nominal"; 
            } else if (data.jenis === 'ENTERTAIN') {
                setupModalStyle('orange', 'Edit Aktivitas');
                wrapperProduk.classList.add('hidden');
                inputProduk.removeAttribute('required');
                wrapperEntertain.classList.remove('hidden');
                inputLokasi.value = data.lokasi;
                inputPeserta.value = data.peserta;
                inputNominal.name = "nominal"; 
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
            if (event.target == modalClient) toggleModal('editClientModal');
            if (event.target == modalTrans) toggleModal('editTransactionModal');
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

        function fetchSalesDataMobile() {
            const dateInput = document.getElementById('tanggal_interaksi_in_mobile').value;
            const salesCustomer = document.getElementById('client_id_in_mobile').value;
            
            if (!salesCustomer) {
                alert('Silakan pilih Rumah Sakit terlebih dahulu.');
                return;
            }
            if (!dateInput) {
                alert('Silakan pilih Tanggal Transaksi terlebih dahulu.');
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
                        const emptyMsg = document.getElementById('empty_queue_msg_mobile');
                        if (emptyMsg) emptyMsg.remove();
                        
                        let countAdded = 0;
                        resData.data.forEach((item) => {
                            addProductRow('mobile', item.nama_produk, item.nilai_sales, dateInput, item.client_id, item.client_name);
                            countAdded++;
                        });
                        
                        let badge = document.getElementById('queue_count_mobile');
                        badge.innerText = parseInt(badge.innerText) + countAdded;
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

            if (view === 'mobile') {
                row.className = 'product-row bg-blue-50/50 p-3 rounded-lg border border-blue-100 relative shadow-sm';
                row.innerHTML = `
                    <input type="hidden" name="tanggal_interaksi[]" value="${date}">
                    <input type="hidden" name="client_id[]" value="${clientId}">
                    <div class="mb-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Rumah Sakit</label>
                        <input type="text" class="w-full border border-gray-200 bg-gray-50 rounded-md shadow-sm px-3 py-2 text-xs text-gray-500 font-semibold" value="${clientName}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_produk[]" class="w-full border border-gray-200 bg-gray-50 rounded-md shadow-sm px-3 py-2 text-sm text-gray-600" value="${produk}" readonly>
                    </div>
                    <div class="flex justify-between items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nilai Sales (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2"><span class="text-gray-500 text-xs font-bold">Rp</span></div>
                                <input type="text" name="nilai_sales[]" class="w-full border border-gray-200 bg-gray-50 rounded-md shadow-sm pl-7 px-3 py-2 font-mono text-sm font-bold text-gray-600" value="${valNominal}" readonly>
                            </div>
                        </div>
                        <div>
                            <button type="button" onclick="removeProductRow(this)" class="text-red-500 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-md transition" title="Hapus Baris"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                `;
            }
            container.appendChild(row);
        }

        function removeProductRow(btn) {
            btn.closest('.product-row').remove();
            let badge = document.getElementById('queue_count_mobile');
            let current = parseInt(badge.innerText);
            if (current > 0) badge.innerText = current - 1;
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
