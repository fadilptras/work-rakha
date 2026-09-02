<x-layout-users :title="'Detail Klien & Sales'">
@php $routePrefix = request()->is('admin/*') ? 'admin.crm.' : 'crm.'; @endphp

    @push('styles')
    <style>
        /* == Modern Mesh Background == */
        .mesh-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            background-color: #f0f6fc;
            background-image: 
                radial-gradient(at 40% 20%, rgba(147, 197, 253, 0.4) 0px, transparent 50%),
                radial-gradient(at 80% 0%, rgba(167, 139, 250, 0.3) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(191, 219, 254, 0.4) 0px, transparent 50%),
                radial-gradient(at 80% 50%, rgba(139, 92, 246, 0.2) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(221, 214, 254, 0.3) 0px, transparent 50%),
                radial-gradient(at 80% 100%, rgba(96, 165, 250, 0.3) 0px, transparent 50%);
            transform: translate3d(0, 0, 0);
            will-change: transform;
            pointer-events: none;
        }

        /* == Modern Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px 6px 6px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.8rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 16px;
            width: fit-content;
            position: relative;
            z-index: 10;
        }
        .btn-back-modern:hover { 
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-1px);
            color: #1d4ed8;
        }
        .btn-back-modern .icon-circle {
            width: 24px; height: 24px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6;
            font-size: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        /* == Premium Tab Navigation == */
        .tab-nav-wrap {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 8px;
            box-shadow: 0 4px 20px rgba(0, 27, 183, 0.10), 0 1px 4px rgba(0,0,0,0.06);
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin-bottom: 0.75rem;
        }
        .nav-tab {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 10px 0;
            border-radius: 12px;
            font-size: 11.5px;
            font-weight: 700;
            border: 1.5px solid #c8d3e0;
            background: #dde3ec;
            color: #334155;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            box-shadow: none;
            position: relative;
            overflow: hidden;
        }
        .nav-tab:active { transform: scale(0.97); }
        .nav-tab .tab-icon {
            font-size: 13px;
            opacity: 0.85;
            transition: opacity 0.2s, transform 0.2s;
        }
        /* Active states per tab */
        .nav-tab.active-sales {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.40);
            font-weight: 700;
        }
        .nav-tab.active-sales .tab-icon { opacity: 1; transform: scale(1.15); }
        .nav-tab.active-support {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.38);
            font-weight: 700;
        }
        .nav-tab.active-support .tab-icon { opacity: 1; transform: scale(1.15); }
        .nav-tab.active-activity {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.38);
            font-weight: 700;
        }
        .nav-tab.active-activity .tab-icon { opacity: 1; transform: scale(1.15); }
        .nav-tab.active-history {
            background: linear-gradient(135deg, #001BB7 0%, #2563eb 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(0, 27, 183, 0.38);
            font-weight: 700;
        }
        .nav-tab.active-history .tab-icon { opacity: 1; transform: scale(1.15); }
        .nav-tab.active-recap {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(15, 118, 110, 0.38);
            font-weight: 700;
        }
        .nav-tab.active-recap .tab-icon { opacity: 1; transform: scale(1.15); }

        /* == Remove browser default black outline on focus == */
        input:focus, input:focus-visible,
        select:focus, select:focus-visible,
        textarea:focus, textarea:focus-visible,
        button:focus, button:focus-visible {
            outline: none !important;
            box-shadow: none;
        }

    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800">
        {{-- GPU-Accelerated Background --}}
        <div class="mesh-bg"></div>
        
        <div class="relative z-10 w-full max-w-md mx-auto p-4 flex-1 flex flex-col pb-24">
            
            {{-- Tombol Kembali --}}
            <a href="{{ route($routePrefix . 'index') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali
            </a>

            {{-- BAGIAN 1: HEADER PROFIL --}}
            <div class="bg-[#001BB7] rounded-3xl shadow-xl shadow-blue-900/20 mb-3 overflow-hidden relative">
                <div class="p-6 text-white relative z-10">
                    <div class="flex flex-col gap-4">
                        {{-- Identitas Klien --}}
                        <div class="flex justify-between items-start gap-3">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="bg-white/20 backdrop-blur-md text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase border border-white/20 shadow-sm inline-flex items-center">
                                        <i class="fas fa-hospital mr-1 opacity-80"></i> {{ \Illuminate\Support\Str::limit($client->nama_perusahaan, 20) }}
                                    </span>
                                    <span class="bg-white/20 backdrop-blur-md text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase border border-white/20 shadow-sm inline-flex items-center">
                                        <i class="fas fa-map-marker-alt mr-1 opacity-80"></i> {{ $client->area ?? 'Non-Area' }}
                                    </span>
                                </div>
                                <h2 class="text-xl font-extrabold tracking-tight text-white drop-shadow-sm pt-1">
                                    {{ $client->nama_user }}
                                </h2>
                                <p class="text-[10px] text-blue-200 font-bold uppercase">
                                    PIC: {{ $client->pic }}
                                </p>
                            </div>
                            <div class="flex gap-2 flex-shrink-0">
                                <button onclick="openClientDetailSheet()" class="bg-white/20 hover:bg-white/35 text-white text-xs w-8 h-8 rounded-full flex items-center justify-center transition border border-white/20 shadow-sm outline-none" title="Detail Info">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                @if($canEdit) 
                                    <button onclick="openEditClientSheet()" class="bg-yellow-500 hover:bg-yellow-400 text-yellow-950 text-xs w-8 h-8 rounded-full flex items-center justify-center transition shadow-md outline-none" title="Edit Klien">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="flex items-center justify-around gap-3 bg-white/10 backdrop-blur-md rounded-2xl px-4 py-3 border border-white/20 shadow-sm">
                            <div class="flex flex-col items-center flex-1">
                                <span class="text-blue-200 text-[8px] font-bold uppercase tracking-wider mb-0.5">Total Realisasi</span>
                                <div class="flex items-baseline">
                                    <span class="text-[9px] text-blue-100 mr-1.5 font-bold">Rp</span>
                                    <span class="text-sm font-mono font-bold text-white">{{ number_format($client->interactions->where('jenis_transaksi', 'OUT')->sum('nilai_kontribusi'), 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="w-px h-8 bg-white/20"></div>
                            <div class="flex flex-col items-center flex-1">
                                <span class="text-emerald-300 text-[8px] font-bold uppercase tracking-wider mb-0.5">Total Saldo</span>
                                <div class="flex items-baseline">
                                    <span class="text-[9px] text-emerald-200 mr-1.5 font-bold">Rp</span>
                                    <span class="text-sm font-mono font-bold text-emerald-300">{{ number_format($currentBalance, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            {{-- MENU NAVIGASI Premium --}}
            <div class="tab-nav-wrap">
                <button id="btn-sales" onclick="switchTab('sales')" class="nav-tab active-sales" style="width:calc(33.33% - 4px)">
                    <i class="fas fa-plus-circle tab-icon"></i>
                    <span>Sales</span>
                </button>
                <button id="btn-support" onclick="switchTab('support')" class="nav-tab" style="width:calc(33.33% - 4px)">
                    <i class="fas fa-hand-holding-usd tab-icon"></i>
                    <span>Usage</span>
                </button>
                <button id="btn-activity" onclick="switchTab('activity')" class="nav-tab" style="width:calc(33.33% - 4px)">
                    <i class="fas fa-handshake tab-icon"></i>
                    <span>Aktivitas</span>
                </button>
                <button id="btn-history" onclick="switchTab('history')" class="nav-tab" style="width:calc(50% - 4px)">
                    <i class="fas fa-history tab-icon"></i>
                    <span>Riwayat</span>
                </button>
                <button id="btn-recap" onclick="switchTab('recap')" class="nav-tab" style="width:calc(50% - 4px)">
                    <i class="fas fa-calendar-check tab-icon"></i>
                    <span>Rekap</span>
                </button>
            </div>
            
            {{-- 1. INPUT SALES --}}
            <div id="section-sales" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden relative">
                <div class="bg-white px-5 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-xs font-extrabold text-blue-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-plus-circle text-sm text-blue-600"></i> Input Sales
                    </h3>
                </div>
                <div class="p-4 space-y-4">
                    @if($hasFullAccess)
                    <form action="{{ route($routePrefix . 'interaction.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Rumah Sakit <span class="text-red-500 font-bold">*</span></label>
                            <select id="client_id_in_mobile" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all" required>
                                <option value="" disabled selected>Pilih Rumah Sakit...</option>
                                @foreach($salesCustomers as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Pilih Tanggal <span class="text-red-500 font-bold">*</span></label>
                            <div class="flex gap-2">
                                <input type="date" id="tanggal_interaksi_in_mobile" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all" required>
                                <button type="button" onclick="fetchSalesDataMobile()" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 font-extrabold px-4 py-2.5 rounded-xl text-xs transition whitespace-nowrap shadow-sm">
                                    <i class="fas fa-cloud-download-alt mr-1"></i> Tarik
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Catatan</label>
                            <input type="text" name="catatan" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Catatan opsional...">
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <h4 class="font-bold text-gray-700 text-xs flex items-center mb-3"><i class="fas fa-box-open mr-1.5 text-blue-500"></i> Antrean Data Sales <span id="queue_count_mobile" class="ml-2 bg-blue-100 text-blue-800 text-[10px] font-semibold px-2 py-0.5 rounded-full">0</span></h4>
                            <div id="product_rows_container_mobile" class="space-y-3 max-h-[30vh] overflow-y-auto custom-scrollbar pr-1">
                                <div id="empty_queue_msg_mobile" class="text-center py-6 text-gray-400 italic text-xs">
                                    Belum ada data ditarik. Silakan klik "Tarik".
                                </div>
                            </div>
                        </div>

                        <datalist id="produk-list-mobile">
                            @foreach($productNames as $prod)
                                <option value="{{ $prod }}"></option>
                            @endforeach
                        </datalist>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition transform active:scale-95 text-xs flex justify-center items-center gap-2"><i class="fas fa-save"></i> Simpan Data Sales</button>
                        </div>
                    </form>
                    @else
                    <div class="flex flex-col items-center justify-center py-8 px-3 text-center bg-gray-50/50 rounded-xl border border-dashed border-gray-200 text-xs">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-2.5 text-base shadow-sm">
                            <i class="fas fa-sync-alt animate-spin-slow"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 text-xs mb-1">Sinkronisasi Data Sales Otomatis</h4>
                        <p class="text-[10px] text-gray-500 leading-relaxed max-w-xs">
                            Data transaksi sales sinkron secara otomatis dari Command Center. Untuk penyesuaian khusus silakan hubungi <strong>Admin</strong>.
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- 2. INPUT SUPPORT (Out / Pengeluaran) --}}
            <div id="section-support" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden relative hidden">
                <div class="bg-white px-5 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-xs font-extrabold text-red-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-hand-holding-usd text-sm text-red-600"></i> Pengeluaran
                    </h3>
                    <button type="button" onclick="openUsageInfoModal()" class="text-red-500 hover:text-red-700 hover:bg-slate-100 transition text-lg flex items-center justify-center w-7 h-7 rounded-full outline-none">
                        <i class="fas fa-info-circle text-base"></i>
                    </button>
                </div>

                <!-- Modal Alert Info Alur -->
                <div id="usage-info-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
                    <div id="usage-info-modal-card" class="bg-white rounded-2xl shadow-2xl max-w-xs w-full overflow-hidden border border-gray-150 transform scale-90 transition-transform duration-300">
                        <div class="bg-red-600 px-4 py-3 flex items-center gap-2 text-white">
                            <i class="fas fa-info-circle"></i>
                            <h3 class="font-bold text-sm">Info Alur Pengeluaran</h3>
                        </div>
                        <div class="p-4 text-xs space-y-2 text-gray-600">
                            <p class="font-bold text-red-600 flex items-center gap-1"><i class="fas fa-paper-plane"></i> Pengajuan Dana Otomatis</p>
                            <p class="leading-relaxed">Form pengeluaran ini otomatis menjadi Pengajuan Dana (Finance). Dana akan diproses setelah disetujui Kepala Divisi & Finance.</p>
                            <div class="mt-4 flex justify-end">
                                <button type="button" onclick="closeUsageInfoModal()" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-[10px] rounded-lg shadow-sm transition">Ok, Paham</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <form action="{{ route($routePrefix . 'interaction.support') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Tanggal <span class="text-red-500 font-bold">*</span></label>
                            <input type="date" name="tanggal_interaksi" class="w-full bg-white border border-slate-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Nominal Keluar (Rp) <span class="text-red-500 font-bold">*</span></label>
                            <div class="flex rounded-xl overflow-hidden bg-white border border-slate-300 focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-100 transition-all">
                                <span class="bg-slate-100 border-r border-slate-200 text-slate-655 font-bold text-[11px] px-3 flex items-center select-none shrink-0">Rp</span>
                                <input type="text" name="nominal" onkeyup="formatRupiah(this)" class="flex-grow min-w-0 border-0 focus:outline-none text-xs font-bold text-red-800 px-3.5 py-2.5 bg-transparent font-mono" placeholder="0" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Keperluan Support <span class="text-red-500 font-bold">*</span></label>
                            <input type="text" name="keperluan" class="w-full bg-white border border-slate-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Contoh: Transport" required>
                        </div>

                        @if($hasFullAccess)
                        <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 rounded-xl p-3 shadow-inner">
                            <input type="checkbox" name="direct_usage" id="direct_usage_mobile" value="1" onchange="toggleDirectUsageMobile(this)" class="w-4 h-4 text-red-650 border-slate-300 rounded focus:ring-red-500">
                            <label for="direct_usage_mobile" class="text-[11px] font-bold text-slate-700 cursor-pointer select-none">Catat Langsung (Tanpa Pengajuan Dana)</label>
                        </div>
                        @endif

                        <div id="bank-info-container-mobile" class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Nama Bank <span class="text-red-500 font-bold">*</span></label>
                                <input type="text" name="nama_bank" value="{{ $client->bank }}" class="w-full bg-white border border-slate-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all" required>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">No. Rekening <span class="text-red-500 font-bold">*</span></label>
                                    <input type="text" name="no_rekening" value="{{ $client->no_rekening }}" class="w-full bg-white border border-slate-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 text-xs px-3.5 py-2.5 rounded-xl font-mono font-bold text-slate-800 transition-all" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Atas Nama <span class="text-red-500 font-bold">*</span></label>
                                    <input type="text" name="nama_rek" value="{{ $client->nama_di_rekening }}" class="w-full bg-white border border-slate-300 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all" required>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-paperclip text-red-500"></i>
                                <span>Lampiran Tambahan</span>
                                <span class="text-slate-400 text-[9px] font-bold">(Maks 5MB)</span>
                            </label>
                            <div class="relative flex items-center justify-between border border-slate-300 rounded-xl bg-white px-3 py-2.5 h-[38px] transition-all focus-within:border-red-500">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <i class="fas fa-cloud-upload-alt text-red-400 text-xs flex-shrink-0"></i>
                                    <span id="file-name-support-mobile" class="text-xs font-semibold text-slate-500 truncate max-w-[150px]">Belum ada berkas...</span>
                                </div>
                                <label class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold text-[9px] py-1 px-2.5 rounded-lg cursor-pointer transition shadow-sm flex items-center gap-1 flex-shrink-0">
                                    <i class="fas fa-folder-open"></i> Cari
                                    <input type="file" name="lampiran_tambahan" class="hidden" onchange="updateFileNameSupportMobile(this)">
                                </label>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" id="submit-btn-support-mobile" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition transform active:scale-95 text-xs flex items-center justify-center gap-2"><i class="fas fa-paper-plane"></i> Ajukan Dana</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. INPUT AKTIVITAS & ENTERTAIN (Daftar Timeline / Card) --}}
            <div id="section-activity" class="space-y-4 hidden">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden relative">
                    <div class="bg-white px-5 py-4 border-b border-slate-200 flex justify-between items-center">
                        <h3 class="text-xs font-extrabold text-orange-850 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-handshake text-sm text-orange-500"></i> Input Aktivitas
                        </h3>
                        <button type="button" onclick="openActivityInfoModal()" class="text-orange-500 hover:text-orange-700 hover:bg-slate-100 transition text-lg flex items-center justify-center w-7 h-7 rounded-full outline-none">
                            <i class="fas fa-info-circle text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Info Aktivitas -->
                    <div id="activity-info-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
                        <div id="activity-info-modal-card" class="bg-white rounded-2xl shadow-2xl max-w-xs w-full overflow-hidden border border-gray-150 transform scale-90 transition-transform duration-300">
                            <div class="bg-orange-500 px-4 py-3 flex items-center gap-2 text-white">
                                <i class="fas fa-info-circle"></i>
                                <h3 class="font-bold text-sm">Info Alur Aktivitas</h3>
                            </div>
                            <div class="p-4 text-xs space-y-2 text-gray-600">
                                <p class="font-bold text-orange-600 flex items-center gap-1"><i class="fas fa-handshake"></i> Pencatatan Aktivitas & Entertain</p>
                                <p class="leading-relaxed">Form ini untuk mencatat makan siang, pertemuan, entertain klien, dll. Pengeluaran di sini bersifat pencatatan mandiri dan tidak terhubung otomatis dengan pengajuan dana ke keuangan.</p>
                                <div class="mt-4 flex justify-end">
                                    <button type="button" onclick="closeActivityInfoModal()" class="px-4 py-2 bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold text-[10px] rounded-lg shadow-sm transition">Ok, Paham</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <form action="{{ route($routePrefix . 'interaction.entertain') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Tanggal Kegiatan <span class="text-red-500 font-bold">*</span></label>
                                <input type="date" name="tanggal_interaksi" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Lokasi / Venue</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="fas fa-location-dot text-[11px] w-3 text-center"></i>
                                    </div>
                                    <input type="text" name="lokasi" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 pl-9 pr-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400 text-xs" placeholder="Contoh: Restoran X">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Partisipan / Klien</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="fas fa-user text-[11px] w-3 text-center"></i>
                                    </div>
                                    <input type="text" name="peserta" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 pl-9 pr-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400 text-xs" placeholder="Nama partisipan...">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Keterangan <span class="text-red-500 font-bold">*</span></label>
                                <textarea name="catatan" rows="2" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all resize-none placeholder-slate-400" placeholder="Deskripsi kegiatan..." required></textarea>
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Nominal Biaya (Rp) <span class="text-red-500 font-bold">*</span></label>
                                <div class="flex rounded-xl overflow-hidden bg-white border border-slate-300 focus-within:border-orange-500 focus-within:ring-2 focus-within:ring-orange-100 transition-all">
                                    <span class="bg-slate-100 border-r border-slate-200 text-slate-655 font-bold text-[11px] px-3 flex items-center select-none shrink-0">Rp</span>
                                    <input type="text" name="nominal" onkeyup="formatRupiah(this)" class="flex-grow min-w-0 border-0 focus:outline-none text-xs font-bold text-orange-800 px-3.5 py-2.5 bg-transparent font-mono" placeholder="0" required>
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition transform active:scale-95 flex items-center justify-center gap-2 text-xs"><i class="fas fa-save"></i> Simpan Aktivitas</button>
                        </form>
                    </div>
                </div>

                {{-- LIST HISTORI AKTIVITAS (Timeline Mobile) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    @php $totalActivity = $activities->sum('nilai_kontribusi'); @endphp
                    <div class="px-4 py-3.5 border-b border-gray-150 bg-gray-50 flex justify-between items-center flex-wrap gap-2">
                        <h4 class="font-bold text-gray-700 text-xs uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-history text-orange-500"></i> Riwayat Aktivitas
                        </h4>
                        <div class="bg-orange-100 border border-orange-200 text-orange-800 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold shadow-sm">
                            Rp {{ number_format($totalActivity, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    {{-- Mini Filter --}}
                    <div class="p-3 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Tahun</span>
                        <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="m-0">
                            <input type="hidden" name="tab" value="activity"> 
                            <select name="activity_year" onchange="this.form.submit()" class="pl-2 pr-7 py-1 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-orange-500 cursor-pointer">
                                <option value="">Semua</option>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ (request('activity_year') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                    </div>

                    <div class="p-4 space-y-4">
                        @forelse($activities as $act)
                        <div class="bg-white rounded-xl border border-gray-100 p-3.5 shadow-sm relative flex flex-col gap-2">
                            <div class="flex justify-between items-start gap-1">
                                <span class="text-[10px] text-gray-400 font-bold font-mono">{{ \Carbon\Carbon::parse($act->tanggal_interaksi)->format('d M Y') }}</span>
                                <span class="font-mono font-bold text-orange-600 text-xs">Rp {{ number_format($act->nilai_kontribusi, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <button type="button" onclick="openViewTransactionModal({
                                    id: '{{ $act->id }}',
                                    jenis: 'ENTERTAIN',
                                    tanggal: '{{ \Carbon\Carbon::parse($act->tanggal_interaksi)->translatedFormat('d F Y') }}',
                                    produk: '{{ addslashes($act->nama_produk) }}',
                                    nominal: '{{ number_format($act->nilai_kontribusi, 0, ',', '.') }}',
                                    rate: '0',
                                    valueNet: '0',
                                    catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->catatan)) }}',
                                    lokasi: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->lokasi ?? "")) }}',
                                    peserta: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $act->peserta ?? "")) }}'
                                })" class="text-left font-bold text-gray-800 hover:text-orange-600 hover:underline text-xs">
                                    {{ $act->nama_produk }}
                                </button>
                                <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed">{{ $act->catatan }}</p>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-gray-50 text-[10px] text-gray-500">
                                <div class="flex flex-col gap-0.5">
                                    <span><i class="fas fa-map-marker-alt text-gray-400 mr-1"></i> {{ $act->lokasi ?? '-' }}</span>
                                    <span><i class="fas fa-users text-gray-400 mr-1"></i> {{ $act->peserta ?? '-' }}</span>
                                </div>
                                @if($hasFullAccess)
                                <div class="flex gap-2">
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
                                        class="text-orange-400 hover:text-orange-600 text-xs transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route($routePrefix . 'interaction.destroy', $act->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Hapus aktivitas ini?');" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-gray-300 hover:text-red-500 text-xs transition"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-400 italic text-xs">Belum ada data aktivitas</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- 4. RIWAYAT TRANSAKSI UTAMA (Daftar Kartu Mobile) --}}
            <div id="section-history" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center bg-white flex-wrap gap-2">
                    <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-history text-slate-500"></i> Riwayat Transaksi
                    </h3>
                    {{-- Filter Tahun --}}
                    <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="m-0">
                        <input type="hidden" name="tab" value="history"> 
                        <select name="history_year" onchange="this.form.submit()" class="pl-2 pr-7 py-1 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-xl shadow-sm focus:border-blue-500 cursor-pointer">
                            <option value="">Semua</option>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ (request('history_year') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
                
                <div class="p-4 space-y-4">
                    @forelse ($interactions as $item)
                        @if($item->jenis_transaksi == 'ENTERTAIN') @continue @endif
                        @php
                            $isOut = $item->jenis_transaksi == 'OUT';
                            $rate = 0; if(preg_match('/\[Rate:([\d\.]+)\]/', $item->catatan, $m)) { $rate = $m[1]; }
                            $displayNote = trim(preg_replace('/\[Rate:[\d\.]+\]/', '', $item->catatan));
                            $valueNet = (!$isOut) ? ($item->nilai_kontribusi * ($rate/100)) : 0;
                        @endphp
                        
                        <div class="rounded-3xl border {{ $isOut ? 'border-rose-100 bg-rose-50/20' : 'border-blue-100 bg-blue-50/10' }} p-4 shadow-sm relative flex flex-col gap-3">
                            <div class="flex justify-between items-center pb-2 border-b border-slate-200/50">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] text-slate-400 font-bold font-mono"><i class="far fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_interaksi)->format('d M Y') }}</span>
                                    <span class="px-2 py-0.5 rounded-lg text-[8px] font-bold tracking-wider uppercase {{ $isOut ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                        {{ $isOut ? 'OUT' : 'IN' }}
                                    </span>
                                </div>
                                @if($hasFullAccess)
                                <div class="flex items-center gap-3">
                                     <button type="button" onclick="openEditTransactionModal({id: '{{ $item->id }}', jenis: '{{ $item->jenis_transaksi }}', tanggal: '{{ $item->tanggal_interaksi }}', produk: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $item->nama_produk)) }}', nominal: '{{ ($item->jenis_transaksi == 'IN') ? $item->nilai_sales : $item->nilai_kontribusi }}', rate: '{{ $rate }}', catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $displayNote)) }}'})" class="text-blue-500 hover:text-blue-700 text-xs transition" title="Edit Data"><i class="fas fa-edit"></i></button>
                                    <form action="{{ route($routePrefix . 'interaction.destroy', $item->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Hapus transaksi ini?');" class="inline">@csrf @method('DELETE')<button class="text-slate-300 hover:text-rose-600 text-xs transition" title="Hapus Data"><i class="fas fa-trash-alt"></i></button></form>
                                </div>
                                @endif
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-start">
                                    <button type="button" onclick="openViewTransactionModal({
                                        id: '{{ $item->id }}',
                                        jenis: '{{ $item->jenis_transaksi }}',
                                        tanggal: '{{ \Carbon\Carbon::parse($item->tanggal_interaksi)->translatedFormat('d F Y') }}',
                                        produk: '{{ addslashes($item->nama_produk) }}',
                                        nominal: '{{ number_format(($item->jenis_transaksi == 'IN') ? $item->nilai_sales : $item->nilai_kontribusi, 0, ',', '.') }}',
                                        rate: '{{ $rate }}',
                                        valueNet: '{{ number_format($valueNet, 0, ',', '.') }}',
                                        catatan: '{{ addslashes(str_replace(["\r", "\n"], ["\\r", "\\n"], $displayNote)) }}'
                                    })" class="text-left font-extrabold text-[11px] text-slate-800 hover:underline focus:outline-none mb-1 flex items-center gap-1">
                                        <i class="fas fa-info-circle text-[10px] text-slate-400"></i> Detail Transaksi
                                    </button>
                                </div>
                                
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach(explode(',', $item->nama_produk) as $subItem)
                                        @if(trim($subItem) != '')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-white text-slate-700 border border-slate-200 shadow-sm leading-tight">
                                                <i class="fas {{ $isOut ? 'fa-file-invoice-dollar text-rose-500' : 'fa-box text-blue-500' }} text-[9px] mr-1 shrink-0"></i> {{ trim($subItem) }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>

                                @if($displayNote)
                                    <div class="mt-2 text-[10px] text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 flex items-start gap-1">
                                        <i class="fas fa-comment-dots text-slate-400 mt-0.5"></i>
                                        <span>{{ $displayNote }}</span>
                                    </div>
                                @endif
                            </div>

                            @if(!$isOut)
                            <div class="grid grid-cols-2 gap-2.5 pt-2.5 border-t border-slate-200/50">
                                <div class="bg-white/80 rounded-xl p-2 border border-slate-200">
                                    <span class="block text-[8px] text-slate-400 uppercase font-extrabold tracking-wider">Bruto / Sales</span>
                                    <span class="font-mono text-xs font-extrabold text-slate-750">Rp {{ number_format($item->nilai_kontribusi, 0, ',', '.') }}</span>
                                </div>
                                <div class="bg-blue-50/50 rounded-xl p-2 border border-blue-100 text-right">
                                    <span class="block text-[8px] text-blue-500 uppercase font-extrabold tracking-wider">Netto ({{ $rate }}%)</span>
                                    <span class="font-mono text-xs font-extrabold text-blue-700">Rp {{ number_format($valueNet, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            @else
                            <div class="pt-2 border-t border-slate-200/50">
                                <div class="bg-rose-50/50 rounded-xl p-2.5 border border-rose-100">
                                    <span class="block text-[8px] text-rose-500 uppercase font-extrabold tracking-wider mb-0.5">Biaya Pengeluaran (Out)</span>
                                    <span class="font-mono text-xs font-extrabold text-rose-700">Rp {{ number_format($item->nilai_kontribusi, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 text-xs">Belum ada riwayat transaksi.</div>
                    @endforelse
                </div>
                @if($interactions->hasPages())
                    <div class="bg-gray-50 px-4 py-3.5 border-t border-gray-200 text-xs">{{ $interactions->links() }}</div>
                @endif
            </div>

            {{-- 5. REKAP TAHUNAN (Compact Table & Export) --}}
            <div id="section-recap" class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hidden">
                <div class="px-4 py-3.5 border-b border-gray-150 flex justify-between items-center bg-gray-50/50 flex-wrap gap-2">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider flex items-center gap-1">
                        <i class="fas fa-chart-bar text-blue-600 mr-1"></i>
                        Rekap {{ $year }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <form action="{{ route($routePrefix . 'show', $client->id) }}" method="GET" class="m-0">
                            <input type="hidden" name="tab" value="recap"> 
                            <select name="year" onchange="this.form.submit()" class="pl-2 pr-7 py-1 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm cursor-pointer">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                                @endfor
                            </select>
                        </form>
                        <a href="{{ route($routePrefix . 'client.export', ['client' => $client->id, 'year' => $year]) }}" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold py-1.5 px-3 rounded-lg shadow-sm border border-emerald-700"><i class="fas fa-file-excel mr-1"></i> Export</a>
                    </div>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-xs text-left min-w-[340px]">
                        <thead class="bg-gray-100 text-gray-600 uppercase text-[9px] font-bold tracking-wider border-b border-gray-200">
                            <tr>
                                <th class="px-3 py-2.5">Bulan</th>
                                <th class="px-2 py-2.5 text-right text-blue-700 bg-blue-50/30">Net (IN)</th>
                                <th class="px-2 py-2.5 text-right text-red-600">OUT</th>
                                <th class="px-3 py-2.5 text-right text-gray-800">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="bg-yellow-50/70 text-[10px] border-b border-yellow-100">
                                <td class="px-3 py-2.5 font-bold text-gray-700 italic" colspan="3"><i class="fas fa-forward text-yellow-600 mr-1"></i> {{ $startingLabel }} </td>
                                <td class="px-3 py-2.5 text-right font-mono font-bold text-gray-900 bg-yellow-50">{{ number_format($startingBalance, 0, ',', '.') }} </td>
                            </tr>
                            @foreach ($recap as $r)
                            <tr class="hover:bg-gray-50/50 border-b border-gray-100 text-[11px]">
                                <td class="px-3 py-2.5">
                                    <button type="button" onclick="showMonthlyDetail('{{ $r['month_name'] }}', {{ $loop->iteration }}, {{ $year }})" class="font-bold text-blue-600 hover:text-blue-800 hover:underline text-left focus:outline-none flex items-center gap-1">
                                        <i class="far fa-calendar-alt text-[10px] text-blue-400"></i>
                                        <span>{{ $r['month_name'] }}</span>
                                    </button>
                                </td>
                                <td class="px-2 py-2.5 text-right font-mono font-bold text-blue-800 bg-blue-50/20">{{ $r['net_value'] > 0 ? number_format($r['net_value'], 0, ',', '.') : '-' }}</td>
                                <td class="px-2 py-2.5 text-right font-mono text-red-600">{{ $r['out'] > 0 ? number_format($r['out'], 0, ',', '.') : '-' }}</td>
                                <td class="px-3 py-2.5 text-right font-mono font-bold text-gray-900">{{ number_format($r['saldo'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 font-extrabold text-[10px] text-gray-800 border-t-2 border-gray-200">
                            <tr>
                                <td class="px-3 py-3 uppercase">Total</td>
                                <td class="px-2 py-3 text-right text-blue-900 bg-blue-100">{{ number_format($yearlyTotals['net_value'], 0, ',', '.') }}</td>
                                <td class="px-2 py-3 text-right text-red-700">{{ number_format($yearlyTotals['out'], 0, ',', '.') }}</td>
                                <td class="px-3 py-3 text-right font-mono text-gray-900">{{ number_format($yearlyTotals['saldo'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('modals')
        <!-- BOTTOM SHEET DETAIL INFO (iOS Style Drawer) -->
        <div id="client-detail-sheet" class="fixed inset-0 z-[9999] flex items-end justify-center bg-slate-950/70 opacity-0 pointer-events-none transition-opacity duration-300">
            <!-- Sheet Container -->
            <div id="client-detail-sheet-card" class="bg-white w-full max-h-[85vh] rounded-t-[2.5rem] shadow-2xl border-t border-slate-200 overflow-hidden transform translate-y-full transition-transform duration-300 flex flex-col">
                <!-- Drag Handle Indicator -->
                <div class="w-full flex justify-center py-3 bg-white border-b border-slate-100 shrink-0">
                    <div class="w-12 h-1.5 bg-slate-350 rounded-full"></div>
                </div>
                
                <!-- Sheet Header -->
                <div class="px-6 py-4 bg-white border-b border-slate-200 flex justify-between items-center shrink-0 relative z-10 shadow-sm">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-650"></i> Detail Informasi Klien
                    </h3>
                    <button type="button" onclick="closeClientDetailSheet()" class="w-7 h-7 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 rounded-full transition outline-none">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                
                <!-- Sheet Body (Scrollable content) -->
                <div class="p-5 overflow-y-auto space-y-5 text-slate-700 pb-10">
                    
                    <!-- 1. INFORMASI CLIENT -->
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 shadow-inner space-y-3">
                        <h4 class="text-[11px] font-extrabold text-blue-600 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-200 pb-2">
                            <i class="fas fa-user"></i> Informasi Client
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            @if($client->jabatan)
                            <div class="col-span-2 flex items-start gap-2.5">
                                <i class="fas fa-id-badge text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-405 uppercase font-bold">Jabatan</p><p class="font-bold text-slate-800">{{ $client->jabatan }}</p></div>
                            </div>
                            @endif
                            <div class="col-span-2 flex items-start gap-2.5">
                                <i class="fas fa-envelope text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-405 uppercase font-bold">Email</p><p class="font-medium text-slate-800 break-all">{{ $client->email ?? '-' }}</p></div>
                            </div>
                            <div class="col-span-2 flex items-start gap-2.5">
                                <i class="fab fa-whatsapp text-emerald-500 mt-0.5 w-4 text-center text-sm"></i>
                                <div>
                                    <p class="text-[9px] text-slate-405 uppercase font-bold">Telepon / WA</p>
                                    @if($client->no_telpon)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->no_telpon) }}" target="_blank" class="font-bold text-blue-600 hover:underline">{{ $client->no_telpon }}</a>
                                    @else
                                        <p class="font-medium text-slate-500">-</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-birthday-cake text-pink-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-405 uppercase font-bold">Tgl Lahir</p><div class="font-medium text-slate-800">{{ $client->tanggal_lahir ? \Carbon\Carbon::parse($client->tanggal_lahir)->format('d M Y') : '-' }}</div></div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-star text-yellow-555 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-405 uppercase font-bold">Hobi</p><p class="font-medium text-slate-800">{{ $client->hobby_client ?? '-' }}</p></div>
                            </div>
                            <div class="col-span-2 flex items-start gap-2.5">
                                <i class="fas fa-home text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-405 uppercase font-bold">Alamat Rumah</p><p class="leading-relaxed text-slate-700">{{ $client->alamat_user ?? '-' }}</p></div>
                            </div>
                            <div class="col-span-2 flex items-start gap-2.5">
                                <i class="fas fa-percent text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-405 uppercase font-bold">Komisi / Rate</p><p class="font-bold text-slate-800">{{ $client->komisi ? (float)$client->komisi . '%' : '-' }}</p></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 2. INFORMASI PERUSAHAAN -->
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 shadow-inner space-y-3">
                        <h4 class="text-[11px] font-extrabold text-orange-600 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-200 pb-2">
                            <i class="fas fa-building"></i> Informasi Perusahaan
                        </h4>
                        <div class="space-y-3.5 text-xs">
                            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                                <p class="text-[8px] text-slate-450 font-bold uppercase mb-0.5">Nama Instansi / RS</p>
                                <p class="font-extrabold text-sm leading-tight text-slate-800">{{ $client->nama_perusahaan }}</p>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-calendar-alt text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div>
                                    <p class="text-[9px] text-slate-405 uppercase font-bold">Tanggal Berdiri</p>
                                    <div class="flex items-center font-medium text-slate-800">
                                        @if($client->tanggal_berdiri)
                                            <span>{{ \Carbon\Carbon::parse($client->tanggal_berdiri)->format('d F Y') }}</span>
                                            <span class="ml-2 text-[8px] bg-slate-250 text-slate-700 px-2 py-0.5 rounded-full font-bold shadow-sm">{{ \Carbon\Carbon::parse($client->tanggal_berdiri)->age }} Th</span>
                                        @else <span class="italic text-slate-400">Belum diisi</span> @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-map-marked-alt text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div><p class="text-[9px] text-slate-405 uppercase font-bold">Alamat Kantor</p><p class="leading-relaxed text-slate-700">{{ $client->alamat_perusahaan ?? '-' }}</p></div>
                            </div>
                            
                            <!-- Apoteker -->
                            <div class="mt-3 pt-3 border-t border-slate-200 space-y-2">
                                <p class="text-[9px] text-slate-450 font-bold uppercase flex items-center gap-1"><i class="fas fa-user-md text-slate-400"></i> Apoteker Penanggung Jawab</p>
                                <div class="grid grid-cols-2 gap-3 text-xs bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                                    <div><p class="text-[8px] text-slate-400 uppercase font-semibold">Nama</p><p class="font-bold text-slate-800">{{ $client->nama_apoteker ?? '-' }}</p></div>
                                    <div><p class="text-[8px] text-slate-400 uppercase font-semibold">SIPA</p><p class="font-bold text-slate-800 font-mono">{{ $client->nomor_sipa ?? '-' }}</p></div>
                                    <div class="col-span-2"><p class="text-[8px] text-slate-400 uppercase font-semibold">No. Telp</p><p class="font-bold text-slate-800">{{ $client->no_telpon_apoteker ?? '-' }}</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 3. INFORMASI BANK -->
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 shadow-inner space-y-3">
                        <h4 class="text-[11px] font-extrabold text-emerald-600 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-200 pb-2">
                            <i class="fas fa-wallet"></i> Informasi Bank
                        </h4>
                        <div class="space-y-3 text-xs">
                            <div>
                                <p class="text-[8px] text-slate-400 uppercase tracking-wider mb-0.5">Bank & Rekening</p>
                                <div class="flex flex-col">
                                    <span class="font-extrabold text-sm text-slate-800">{{ $client->bank ?? 'BANK -' }}</span>
                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $client->nama_di_rekening ? 'A/n '.$client->nama_di_rekening : '' }}</p>
                                    <div class="flex items-center gap-1.5 font-mono text-emerald-600 font-bold text-xs bg-emerald-100/60 px-2.5 py-1 rounded-lg w-fit border border-emerald-200 shadow-sm mt-1.5">
                                        <i class="fas fa-credit-card text-[9px] opacity-70"></i> <span>{{ $client->no_rekening ?? '----' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-slate-200 pt-2.5 flex justify-between items-center">
                                <span class="text-[8px] text-slate-400 uppercase tracking-wider">Saldo Awal</span>
                                <span class="text-sm font-mono font-bold text-slate-800">
                                    Rp {{ number_format($client->saldo_awal ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        <style>
            #client-detail-sheet, #edit-client-sheet {
                transition: opacity 0.3s ease;
            }
            #client-detail-sheet-card, #edit-client-sheet-card {
                transition: transform 0.4s cubic-bezier(0.32, 0.94, 0.6, 1);
            }
        </style>

        <!-- BOTTOM SHEET EDIT DATA KLIEN (iOS Style Drawer) -->
        <div id="edit-client-sheet" class="fixed inset-0 z-[9999] flex items-end justify-center bg-slate-950/70 opacity-0 pointer-events-none transition-opacity duration-300">
            <!-- Sheet Container -->
            <div id="edit-client-sheet-card" class="bg-white w-full max-h-[85vh] rounded-t-[2.5rem] shadow-2xl border-t border-slate-200 overflow-hidden transform translate-y-full transition-transform duration-300 flex flex-col">
                <!-- Drag Handle Indicator -->
                <div class="w-full flex justify-center py-3 bg-white border-b border-slate-100 shrink-0">
                    <div class="w-12 h-1.5 bg-slate-350 rounded-full"></div>
                </div>
                
                <!-- Sheet Header -->
                <div class="px-6 py-4 bg-white border-b border-slate-200 flex justify-between items-center shrink-0 relative z-10 shadow-sm">
                    <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fas fa-edit text-yellow-600"></i> Edit Data Klien
                    </h3>
                    <button type="button" onclick="closeEditClientSheet()" class="w-7 h-7 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 rounded-full transition outline-none">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                
                <form action="{{ route($routePrefix . 'client.update', $client->id) }}" method="POST" class="flex flex-col flex-grow overflow-hidden">
                    @csrf @method('PUT')
                    <div class="overflow-y-auto p-5 custom-scrollbar flex-grow bg-slate-50 space-y-4 pb-10">
                        
                        {{-- 1. IDENTITAS PERSONAL --}}
                        <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-3.5 space-y-3">
                            <h4 class="text-blue-800 text-xs font-bold uppercase tracking-wider border-b border-blue-50 pb-1.5 flex items-center">
                                <span class="w-4 h-4 bg-blue-600 text-white text-[9px] rounded-full flex items-center justify-center mr-1.5 font-bold">1</span>
                                Identitas Personal
                            </h4>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase">Nama User <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_user" value="{{ old('nama_user', $client->nama_user) }}" required class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-xs px-3 py-2 font-semibold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Jabatan</label>
                                <input type="text" name="jabatan" value="{{ old('jabatan', $client->jabatan) }}" class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-xs px-3 py-2" placeholder="Jabatan">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">No. Telp (WA)</label>
                                    <input type="text" name="no_telpon" value="{{ old('no_telpon', $client->no_telpon) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-blue-500 px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $client->email) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-blue-500 px-3 py-2">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Tgl Lahir</label>
                                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($client->tanggal_lahir)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-blue-500 px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Hobi</label>
                                    <input type="text" name="hobby_client" value="{{ old('hobby_client', $client->hobby_client) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-blue-500 px-3 py-2" placeholder="Hobi">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Alamat Rumah</label>
                                <textarea name="alamat_user" rows="2" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-blue-500 px-3 py-2 resize-none">{{ old('alamat_user', $client->alamat_user) }}</textarea>
                            </div>
                        </div>

                        {{-- 2. DATA PERUSAHAAN --}}
                        <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-3.5 space-y-3">
                            <h4 class="text-orange-800 text-xs font-bold uppercase tracking-wider border-b border-orange-50 pb-1.5 flex items-center">
                                <span class="w-4 h-4 bg-orange-500 text-white text-[9px] rounded-full flex items-center justify-center mr-1.5 font-bold">2</span>
                                Data Perusahaan
                            </h4>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase">Nama Instansi/RS <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_perusahaan" value="{{ old('nama_perusahaan', $client->nama_perusahaan) }}" required class="w-full border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-xs px-3 py-2 font-semibold">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Area</label>
                                    <input type="text" name="area" value="{{ old('area', $client->area) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-orange-500 px-3 py-2" placeholder="Area">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Tgl Berdiri</label>
                                    <input type="date" name="tanggal_diri" value="{{ old('tanggal_berdiri', optional($client->tanggal_berdiri)->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-orange-500 px-3 py-2">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Alamat Kantor</label>
                                <textarea name="alamat_perusahaan" rows="2" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-orange-500 px-3 py-2 resize-none">{{ old('alamat_perusahaan', $client->alamat_perusahaan) }}</textarea>
                            </div>
                            <div class="border-t border-orange-50 pt-2.5">
                                <label class="block text-[10px] font-bold text-orange-700 mb-2 uppercase"><i class="fas fa-user-md mr-1"></i> Data Apoteker</label>
                                <div class="space-y-2">
                                    <input type="text" name="nama_apoteker" value="{{ old('nama_apoteker', $client->nama_apoteker) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-orange-500 px-3 py-1.5" placeholder="Nama Apoteker">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="nomor_sipa" value="{{ old('nomor_sipa', $client->nomor_sipa) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-orange-500 px-3 py-1.5" placeholder="SIPA">
                                        <input type="text" name="no_telpon_apoteker" value="{{ old('no_telpon_apoteker', $client->no_telpon_apoteker) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-orange-500 px-3 py-1.5" placeholder="Telp">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. DATA KEUANGAN --}}
                        <div class="bg-white rounded-xl border border-emerald-100 shadow-sm p-3.5 space-y-3">
                            <h4 class="text-emerald-800 text-xs font-bold uppercase tracking-wider border-b border-emerald-50 pb-1.5 flex items-center">
                                <span class="w-4 h-4 bg-emerald-600 text-white text-[9px] rounded-full flex items-center justify-center mr-1.5 font-bold">3</span>
                                Keuangan & Bank
                            </h4>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Nama Bank</label>
                                <input type="text" name="bank" value="{{ old('bank', $client->bank) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-emerald-500 px-3 py-2">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">No. Rekening</label>
                                    <input type="text" name="no_rekening" value="{{ old('no_rekening', $client->no_rekening) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-emerald-500 px-3 py-2 font-mono">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Atas Nama</label>
                                    <input type="text" name="nama_di_rekening" value="{{ old('nama_di_rekening', $client->nama_di_rekening) }}" class="w-full border border-gray-300 rounded-lg text-xs focus:ring-emerald-500 px-3 py-2">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 border-t border-emerald-50 pt-2.5">
                                <div>
                                    <label class="block text-[10px] font-bold text-emerald-700 mb-1 uppercase">Rate (%)</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" name="komisi" value="{{ old('komisi', $client->komisi) }}" class="w-full border border-emerald-250 bg-emerald-50/20 rounded-lg text-xs font-bold text-emerald-800 focus:ring-emerald-500 px-3 py-2">
                                        <span class="absolute right-2.5 top-2 text-emerald-600 font-bold text-[10px]">%</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-emerald-700 mb-1 uppercase">Saldo Awal</label>
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-emerald-600 font-bold text-[9px]">Rp</span>
                                        <input type="number" name="saldo_awal" value="{{ old('saldo_awal', $client->saldo_awal) }}" class="w-full pl-6 border border-emerald-250 bg-emerald-50/20 rounded-lg text-xs font-bold text-emerald-800 focus:ring-emerald-500 px-3 py-2">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="bg-white px-5 py-4 border-t border-slate-200 flex justify-end gap-2 shrink-0">
                        <button type="button" onclick="closeEditClientSheet()" class="px-4 py-2 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition"><i class="fas fa-save mr-1.5"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    @endpush

    {{-- MODAL EDIT TRANSAKSI (Mobile Optimized) --}}
    @push('modals')
        <div id="editTransactionModal" class="hidden fixed inset-0 bg-gray-900/60 z-[9999] items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
            <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                <div id="editTransHeader" class="bg-gray-150 px-4 py-3.5 border-b border-gray-200 flex justify-between items-center text-gray-800">
                    <h3 class="font-bold text-sm flex items-center"><i class="fas fa-edit mr-2"></i> Edit Transaksi</h3>
                    <button onclick="toggleModal('editTransactionModal')" class="text-gray-500 hover:text-red-500 transition text-xl font-bold focus:outline-none">&times;</button>
                </div>
                <form id="formEditTransaction" action="#" method="POST" class="p-4 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase">Tanggal</label>
                        <input type="date" name="tanggal_interaksi" id="edit_tanggal" class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 px-3 py-2 text-xs" required>
                    </div>
                    <div id="wrapper_produk">
                        <label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase" id="label_produk">Nama Produk / Keperluan</label>
                        <input type="text" name="" id="edit_produk" class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 px-3 py-2 text-xs font-bold">
                    </div>
                    <div id="wrapper_entertain" class="hidden space-y-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase">Lokasi / Venue</label>
                            <input type="text" name="lokasi" id="edit_lokasi" class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-orange-500 px-3 py-2 text-xs" placeholder="Lokasi">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase">Partisipan / Klien</label>
                            <input type="text" name="peserta" id="edit_peserta" class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-orange-500 px-3 py-2 text-xs" placeholder="Peserta">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-700 mb-1 uppercase">Nominal (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500 text-xs">Rp</span>
                            <input type="text" name="" id="edit_nominal" onkeyup="formatRupiah(this)" class="w-full pl-8 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 px-3 py-2 font-mono font-bold text-sm" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-1 uppercase">Catatan</label>
                        <textarea name="catatan" id="edit_catatan" rows="2" class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 px-3 py-2 text-xs resize-none"></textarea>
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" onclick="toggleModal('editTransactionModal')" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-100">Batal</button>
                        <button type="submit" id="btnUpdateTrans" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 shadow-md flex items-center"><i class="fas fa-save mr-1.5"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL DETAIL TRANSAKSI (READ-ONLY, Mobile Optimized) --}}
        <div id="viewTransactionModal" class="hidden fixed inset-0 bg-gray-900/60 z-[9999] items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
            <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
                <div id="viewTransHeader" class="bg-blue-600 px-4 py-4 text-white flex justify-between items-center shrink-0">
                    <h3 class="font-bold text-base flex items-center"><i class="fas fa-info-circle mr-2"></i> Detail Transaksi</h3>
                    <button onclick="toggleModal('viewTransactionModal')" class="text-white hover:text-gray-200 transition text-xl font-bold focus:outline-none">&times;</button>
                </div>
                <div class="p-5 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Tanggal</span>
                            <span id="view_tanggal" class="text-xs font-semibold text-gray-800"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Tipe</span>
                            <span id="view_jenis" class="inline-block px-2 py-0.5 rounded text-[9px] font-bold shadow-sm border"></span>
                        </div>
                    </div>
                    
                    <div>
                        <span id="view_label_produk" class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Produk / Keperluan</span>
                        <span id="view_produk" class="text-sm font-extrabold text-gray-950 block mt-0.5"></span>
                    </div>

                    <div id="view_wrapper_entertain" class="hidden grid grid-cols-2 gap-2 bg-orange-50 border border-orange-100 rounded-xl p-2.5">
                        <div>
                            <span class="block text-[9px] font-bold text-orange-700 uppercase tracking-wider">Lokasi / Venue</span>
                            <span id="view_lokasi" class="text-xs font-bold text-gray-800"></span>
                        </div>
                        <div>
                            <span class="block text-[9px] font-bold text-orange-700 uppercase tracking-wider">Partisipan</span>
                            <span id="view_peserta" class="text-xs font-bold text-gray-800"></span>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 grid grid-cols-2 gap-4">
                        <div>
                            <span id="view_label_nominal" class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Nominal</span>
                            <span id="view_nominal" class="text-base font-mono font-bold text-gray-800"></span>
                        </div>
                        <div id="view_wrapper_commission" class="hidden">
                            <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Komisi (Rate)</span>
                            <span id="view_rate" class="text-xs font-bold text-blue-700"></span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-[9px] font-bold text-gray-400 uppercase tracking-wider">Catatan</span>
                        <p id="view_catatan" class="text-xs text-gray-600 bg-gray-50 border border-gray-100 rounded-lg p-2.5 mt-1 whitespace-pre-line italic"></p>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="button" onclick="toggleModal('viewTransactionModal')" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold active:scale-95 transition text-xs">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL DETAIL TRANSAKSI BULANAN (Mobile Friendly Table) --}}
        <div id="monthlyDetailModal" class="hidden fixed inset-0 bg-gray-900/60 z-[9999] items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
            <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden border border-gray-200 flex flex-col max-h-[80vh]">
                <div class="bg-gray-800 px-5 py-4 text-white flex justify-between items-center shrink-0">
                    <h3 class="font-bold text-base flex items-center"><i class="far fa-calendar-alt mr-2"></i> <span id="monthly_detail_title">Transaksi Bulanan</span></h3>
                    <button onclick="toggleModal('monthlyDetailModal')" class="text-white hover:text-gray-200 transition text-xl font-bold focus:outline-none">&times;</button>
                </div>
                <div class="p-4 overflow-y-auto flex-grow text-xs space-y-4">
                    <div class="space-y-3" id="monthly_detail_body">
                        {{-- Diisi dinamis via JS dalam bentuk list kartu --}}
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex justify-end shrink-0">
                    <button type="button" onclick="toggleModal('monthlyDetailModal')" class="w-full py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold active:scale-95 transition text-xs">Tutup</button>
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
                const date = new Date(item.tanggal_interaksi);
                return date.getFullYear() === year && (date.getMonth() + 1) === monthNum;
            });
            
            // Sort by date ascending
            filtered.sort((a, b) => new Date(a.tanggal_interaksi) - new Date(b.tanggal_interaksi));
            
            document.getElementById('monthly_detail_title').innerText = `${monthName} ${year}`;
            
            const container = document.getElementById('monthly_detail_body');
            container.innerHTML = '';
            
            if (filtered.length === 0) {
                container.innerHTML = '<div class="text-center py-6 text-gray-400 italic">Tidak ada transaksi pada bulan ini.</div>';
            } else {
                filtered.forEach(item => {
                    const card = document.createElement('div');
                    card.className = "bg-white rounded-xl border border-gray-150 p-3 shadow-sm flex flex-col gap-1.5 text-[11px]";
                    
                    // Format Date
                    const dateObj = new Date(item.tanggal_interaksi);
                    const formattedDate = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                    
                    // Type Badge
                    let typeBadge = '';
                    let details = `<strong>${item.nama_produk}</strong>`;
                    let nominal = 0;
                    
                    if (item.jenis_transaksi === 'IN') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[8px] font-bold bg-blue-100 text-blue-800">Sales (IN)</span>';
                        nominal = item.nilai_sales > 0 ? item.nilai_sales : item.nilai_kontribusi;
                        
                        // Parse Rate if any
                        let rate = 0;
                        const match = item.catatan ? item.catatan.match(/\[Rate:([\d\.]+)%?\]/) : null;
                        if (match) rate = parseFloat(match[1]);
                        const note = item.catatan ? item.catatan.replace(/\[Rate:[\d\.]+%?\]\s*/, '') : '';
                        if (rate > 0) {
                            const valueNet = nominal * (rate / 100);
                            details += `<div class="text-[9px] text-gray-500 italic mt-0.5">${note}</div>`;
                            details += `<div class="text-[9px] text-blue-700 font-bold mt-0.5">Rate: ${rate}% (Net: Rp ${valueNet.toLocaleString('id-ID')})</div>`;
                        } else if (note) {
                            details += `<div class="text-[9px] text-gray-500 italic mt-0.5">${note}</div>`;
                        }
                    } else if (item.jenis_transaksi === 'OUT') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[8px] font-bold bg-red-100 text-red-800">Support (OUT)</span>';
                        nominal = item.nilai_kontribusi;
                        if (item.catatan) {
                            details += `<div class="text-[9px] text-gray-500 italic mt-0.5">${item.catatan}</div>`;
                        }
                    } else if (item.jenis_transaksi === 'ENTERTAIN') {
                        typeBadge = '<span class="px-2 py-0.5 rounded text-[8px] font-bold bg-orange-100 text-orange-800">Aktivitas</span>';
                        nominal = item.nilai_kontribusi;
                        if (item.lokasi || item.peserta) {
                            details += `<div class="text-[9px] text-orange-700 mt-0.5"><i class="fas fa-map-marker-alt mr-1"></i>${item.lokasi || '-'} | <i class="fas fa-users mr-1"></i>${item.peserta || '-'}</div>`;
                        }
                        if (item.catatan) {
                            details += `<div class="text-[9px] text-gray-500 italic mt-0.5">${item.catatan}</div>`;
                        }
                    }
                    
                    card.innerHTML = `
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-semibold font-mono">${formattedDate}</span>
                            ${typeBadge}
                        </div>
                        <div>${details}</div>
                        <div class="flex justify-between items-center border-t border-gray-50 pt-1.5">
                            <span class="text-gray-400 text-[9px] uppercase tracking-wider font-bold">Nominal</span>
                            <span class="font-mono font-extrabold text-gray-800">Rp ${parseFloat(nominal).toLocaleString('id-ID')}</span>
                        </div>
                    `;
                    container.appendChild(card);
                });
            }
            
            toggleModal('monthlyDetailModal');
        }

        function toggleDirectUsageMobile(checkbox) {
            const container = document.getElementById('bank-info-container-mobile');
            const inputs = container.querySelectorAll('input');
            const submitBtn = document.getElementById('submit-btn-support-mobile');
            
            if (checkbox.checked) {
                container.classList.add('hidden');
                inputs.forEach(input => {
                    input.removeAttribute('required');
                });
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Catat Dana';
                submitBtn.className = "w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition transform active:scale-95 text-xs flex items-center justify-center gap-2";
            } else {
                container.classList.remove('hidden');
                inputs.forEach(input => {
                    input.setAttribute('required', 'required');
                });
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Ajukan Dana';
                submitBtn.className = "w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition transform active:scale-95 text-xs flex items-center justify-center gap-2";
            }
        }

        function openViewTransactionModal(data) {
            document.getElementById('view_tanggal').innerText = data.tanggal;
            document.getElementById('view_produk').innerText = data.produk;
            document.getElementById('view_catatan').innerText = data.catatan || '-';
            
            const header = document.getElementById('viewTransHeader');
            const jenisBadge = document.getElementById('view_jenis');
            const wrapperEntertain = document.getElementById('view_wrapper_entertain');
            const wrapperCommission = document.getElementById('view_wrapper_commission');
            const labelNominal = document.getElementById('view_label_nominal');
            const labelProduk = document.getElementById('view_label_produk');
            
            // Default hidden
            wrapperEntertain.classList.add('hidden');
            wrapperCommission.classList.add('hidden');
            
            if (data.jenis === 'IN') {
                header.className = "bg-blue-600 px-4 py-4 text-white flex justify-between items-center shrink-0";
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-blue-100 text-blue-800 shadow-sm border border-blue-200";
                jenisBadge.innerText = "Sales (IN)";
                labelNominal.innerText = "Nilai Sales (Gross)";
                labelProduk.innerText = "Nama Produk";
                
                // Show commission
                wrapperCommission.classList.remove('hidden');
                document.getElementById('view_rate').innerText = data.rate + '% (Net: Rp ' + data.valueNet + ')';
                document.getElementById('view_nominal').innerText = 'Rp ' + data.nominal;
            } else if (data.jenis === 'OUT') {
                header.className = "bg-red-600 px-4 py-4 text-white flex justify-between items-center shrink-0";
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-red-100 text-red-800 shadow-sm border border-red-200";
                jenisBadge.innerText = "Support (OUT)";
                labelNominal.innerText = "Nominal Support";
                labelProduk.innerText = "Keperluan Support";
                
                document.getElementById('view_nominal').innerText = 'Rp ' + data.nominal;
            } else if (data.jenis === 'ENTERTAIN') {
                header.className = "bg-orange-550 px-4 py-4 text-white flex justify-between items-center shrink-0";
                jenisBadge.className = "inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-orange-100 text-orange-850 shadow-sm border border-orange-200";
                jenisBadge.innerText = "Aktivitas";
                labelNominal.innerText = "Biaya Aktivitas";
                labelProduk.innerText = "Keterangan Aktivitas";
                
                wrapperEntertain.classList.remove('hidden');
                document.getElementById('view_lokasi').innerText = data.lokasi || '-';
                document.getElementById('view_peserta').innerText = data.peserta || '-';
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
                header.className = `bg-${color}-600 px-5 py-3 border-b border-${color}-500 flex justify-between items-center text-white shrink-0`;
                header.querySelector('h3').className = "font-bold text-sm text-white flex items-center";
                header.querySelector('h3').innerHTML = `<i class="fas fa-edit mr-2"></i> ${title}`;
                header.querySelector('button').className = "text-white hover:text-red-200 transition text-xl font-bold focus:outline-none";
                btn.className = `px-4 py-2 bg-${color}-600 text-white rounded-xl text-xs font-bold hover:bg-${color}-700 shadow-md flex items-center`;
                inputNominal.className = `w-full pl-8 border border-gray-300 rounded-lg shadow-sm focus:border-${color}-500 px-3 py-2 font-mono font-bold text-sm`;
            }

            toggleModal('editTransactionModal');
        }

        window.onclick = function(event) {
            const modalTrans = document.getElementById('editTransactionModal');
            const modalUsageInfo = document.getElementById('usage-info-modal');
            const modalActivityInfo = document.getElementById('activity-info-modal');
            const sheetClientDetail = document.getElementById('client-detail-sheet');
            const sheetEditClient = document.getElementById('edit-client-sheet');
            if (event.target == modalTrans) toggleModal('editTransactionModal');
            if (event.target == modalUsageInfo) closeUsageInfoModal();
            if (event.target == modalActivityInfo) closeActivityInfoModal();
            if (event.target == sheetClientDetail) closeClientDetailSheet();
            if (event.target == sheetEditClient) closeEditClientSheet();
        }

        function switchTab(tabName) {
            const sections = { 'sales': 'section-sales', 'support': 'section-support', 'activity': 'section-activity', 'history': 'section-history', 'recap': 'section-recap' };
            const buttons  = { 'sales': 'btn-sales', 'support': 'btn-support', 'activity': 'btn-activity', 'history': 'btn-history', 'recap': 'btn-recap' };
            const activeClass = { 'sales': 'active-sales', 'support': 'active-support', 'activity': 'active-activity', 'history': 'active-history', 'recap': 'active-recap' };

            // Reset semua tab ke state default
            for (const k in buttons) {
                const s = document.getElementById(sections[k]);
                const b = document.getElementById(buttons[k]);
                if (s) s.classList.add('hidden');
                if (b) {
                    b.classList.remove('active-sales','active-support','active-activity','active-history','active-recap');
                }
            }

            // Aktifkan tab yang dipilih
            const activeSection = document.getElementById(sections[tabName]);
            const activeBtn    = document.getElementById(buttons[tabName]);
            if (activeSection) activeSection.classList.remove('hidden');
            if (activeBtn)    activeBtn.classList.add(activeClass[tabName]);

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
            const editTransModal = document.getElementById('editTransactionModal');
            const sheetClientDetail = document.getElementById('client-detail-sheet');
            const sheetEditClient = document.getElementById('edit-client-sheet');
            if (editTransModal.classList.contains('hidden') && sheetClientDetail.classList.contains('pointer-events-none') && sheetEditClient.classList.contains('pointer-events-none')) {
                document.body.style.overflow = 'auto';
            }
        }

        function openClientDetailSheet() {
            const sheet = document.getElementById('client-detail-sheet');
            const card = document.getElementById('client-detail-sheet-card');
            sheet.classList.remove('opacity-0', 'pointer-events-none');
            sheet.classList.add('opacity-100', 'pointer-events-auto');
            card.classList.remove('translate-y-full');
            card.classList.add('translate-y-0');
            document.body.style.overflow = 'hidden';
        }

        function closeClientDetailSheet() {
            const sheet = document.getElementById('client-detail-sheet');
            const card = document.getElementById('client-detail-sheet-card');
            sheet.classList.remove('opacity-100', 'pointer-events-auto');
            sheet.classList.add('opacity-0', 'pointer-events-none');
            card.classList.remove('translate-y-0');
            card.classList.add('translate-y-full');
            
            const editTransModal = document.getElementById('editTransactionModal');
            const sheetEditClient = document.getElementById('edit-client-sheet');
            if (editTransModal.classList.contains('hidden') && sheetEditClient.classList.contains('pointer-events-none')) {
                document.body.style.overflow = 'auto';
            }
        }

        function openEditClientSheet() {
            const sheet = document.getElementById('edit-client-sheet');
            const card = document.getElementById('edit-client-sheet-card');
            sheet.classList.remove('opacity-0', 'pointer-events-none');
            sheet.classList.add('opacity-100', 'pointer-events-auto');
            card.classList.remove('translate-y-full');
            card.classList.add('translate-y-0');
            document.body.style.overflow = 'hidden';
        }

        function closeEditClientSheet() {
            const sheet = document.getElementById('edit-client-sheet');
            const card = document.getElementById('edit-client-sheet-card');
            sheet.classList.remove('opacity-100', 'pointer-events-auto');
            sheet.classList.add('opacity-0', 'pointer-events-none');
            card.classList.remove('translate-y-0');
            card.classList.add('translate-y-full');
            
            const editTransModal = document.getElementById('editTransactionModal');
            const sheetClientDetail = document.getElementById('client-detail-sheet');
            if (editTransModal.classList.contains('hidden') && sheetClientDetail.classList.contains('pointer-events-none')) {
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
                row.className = 'product-row bg-blue-50/50 p-3 rounded-xl border border-blue-100 relative shadow-sm text-xs flex flex-col gap-2';
                row.innerHTML = `
                    <input type="hidden" name="tanggal_interaksi[]" value="${date}">
                    <input type="hidden" name="client_id[]" value="${clientId}">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 mb-0.5">Rumah Sakit</label>
                        <input type="text" class="w-full border border-gray-200 bg-gray-50 rounded-md px-2.5 py-1.5 text-xs text-gray-500 font-semibold" value="${clientName}" readonly>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 mb-0.5">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_produk[]" class="w-full border border-gray-200 bg-gray-50 rounded-md px-2.5 py-1.5 text-xs text-gray-700" value="${produk}" readonly>
                    </div>
                    <div class="flex justify-between items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-gray-500 mb-0.5">Nilai Sales (Rp) <span class="text-red-500">*</span></label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2"><span class="text-gray-500 text-[10px] font-bold">Rp</span></div>
                                <input type="text" name="nilai_sales[]" class="w-full border border-gray-200 bg-gray-50 rounded-md pl-7 px-2.5 py-1.5 font-mono text-xs font-bold text-gray-750" value="${valNominal}" readonly>
                            </div>
                        </div>
                        <div>
                            <button type="button" onclick="removeProductRow(this)" class="text-red-500 bg-red-50 hover:bg-red-100 px-3 py-1.5 border border-red-200 rounded-lg transition" title="Hapus"><i class="fas fa-trash-alt"></i></button>
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

        function updateFileNameSupportMobile(input) {
            const label = document.getElementById('file-name-support-mobile');
            if (input.files && input.files[0]) {
                label.innerText = input.files[0].name;
            } else {
                label.innerText = 'Belum ada berkas...';
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 2px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
    @endpush

</x-layout-users>
