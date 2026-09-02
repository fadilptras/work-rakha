<x-layout-users :title="'Sistem Informasi Sales (CRM)'">

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

        /* Float animation */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float-delayed { animation: float 10s ease-in-out infinite; animation-delay: 2s; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden">
        {{-- GPU-Accelerated Background --}}
        <div class="mesh-bg"></div>
        
        <div class="relative z-10 w-full max-w-md mx-auto p-4 flex-1 flex flex-col pb-24">

            {{-- BAGIAN 1: HERO HEADER --}}
            <div class="bg-[#001BB7] rounded-3xl shadow-xl shadow-blue-900/20 mb-6 overflow-hidden relative">
                <div class="p-6 relative z-10">
                    <div class="flex flex-col gap-4">
                        {{-- Judul & Deskripsi --}}
                        <div class="text-white">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-white/20 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-white/30">
                                    Dashboard CRM
                                </span>
                            </div>
                            <h2 class="text-2xl font-extrabold tracking-tight drop-shadow-sm mb-1">
                                Overview Sales & Klien
                            </h2>
                            <p class="text-blue-100 opacity-95 text-xs leading-relaxed">
                                Monitor performa area, PIC, dan database customer relationship management.
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex flex-col gap-3">
                            <form action="{{ route('crm.index') }}" method="GET" class="m-0 w-full">
                                <div class="relative w-full">
                                    <select name="year" onchange="this.form.submit()" class="w-full bg-white/10 hover:bg-white/20 text-white font-bold border border-white/30 rounded-xl pl-4 pr-10 py-3 shadow-lg outline-none cursor-pointer transition-colors appearance-none focus:ring-2 focus:ring-white/50 text-sm">
                                        <option value="all" class="text-gray-900" {{ isset($selectedYear) && $selectedYear == 'all' ? 'selected' : '' }}>Semua Tahun</option>
                                        @for($y = date('Y'); $y >= 2020; $y--)
                                            <option value="{{ $y }}" class="text-gray-900" {{ isset($selectedYear) && $selectedYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                                        @endfor
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-white">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </form>
                            <div class="flex gap-2 w-full">
                                <a href="{{ route('crm.matrix') }}" class="flex-1 justify-center bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-sm text-white font-bold py-3 rounded-xl shadow-lg transition-colors flex items-center text-sm">
                                    <i class="fas fa-table mr-2"></i> Matrix
                                </a>
                                <button onclick="toggleModal('createClientModal')" class="flex-1 justify-center bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-orange-900/20 transition-colors flex items-center border border-orange-400 text-sm">
                                    <i class="fas fa-plus mr-2"></i> Tambah Klien
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Mini Stats Cards (Stacked/Slim for Mobile) --}}
                    <div class="flex flex-col gap-3 mt-6">
                        {{-- Stat 1: Total Klien --}}
                        <div class="bg-white/20 backdrop-blur-lg rounded-2xl p-3.5 flex items-center gap-3.5 shadow-sm" style="border: 1px solid rgba(255, 255, 255, 0.25);">
                            <div class="w-10 h-10 rounded-xl bg-white/25 flex items-center justify-center text-white text-lg shadow-inner">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <p class="text-blue-100 text-[10px] font-bold uppercase tracking-wider opacity-90">Total Klien Aktif</p>
                                <p class="text-white text-lg font-extrabold leading-none mt-1">{{ $clients->count() }}</p>
                            </div>
                        </div>

                        {{-- Stat 2: Total Realisasi --}}
                        <div class="bg-white/20 backdrop-blur-lg rounded-2xl p-3.5 flex items-center gap-3.5 shadow-sm" style="border: 1px solid rgba(255, 255, 255, 0.25);">
                            <div class="w-10 h-10 rounded-xl bg-purple-550/80 flex items-center justify-center text-white text-lg shadow-lg">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <div>
                                <p class="text-blue-100 text-[10px] font-bold uppercase tracking-wider opacity-90">Total Realisasi</p>
                                <p class="text-emerald-300 text-base font-mono font-bold leading-none mt-1">
                                    Rp {{ number_format($totalUsage, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        {{-- Stat 3: Total Saldo --}}
                        <div class="bg-white/20 backdrop-blur-lg rounded-2xl p-3.5 flex items-center gap-3.5 shadow-sm" style="border: 1px solid rgba(255, 255, 255, 0.25);">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/80 flex items-center justify-center text-white text-lg shadow-lg">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div>
                                <p class="text-blue-100 text-[10px] font-bold uppercase tracking-wider opacity-90">Total Saldo</p>
                                <p class="text-emerald-300 text-base font-mono font-bold leading-none mt-1">
                                    Rp {{ number_format($totalAllBalance, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BAGIAN 2: DATA LIST --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden relative z-20">
                {{-- Header List & Search --}}
                <div class="px-5 pt-5 pb-2 flex flex-col gap-3 bg-white">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <i class="fas fa-list-ul text-blue-600"></i> Database Klien
                    </h3>
                    
                    {{-- Search Bar --}}
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" id="searchInput" placeholder="Cari nama, RS, atau area..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border-2 border-slate-200 text-xs focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white transition-all shadow-sm placeholder-slate-400 font-semibold text-slate-700">
                    </div>
                </div>

                {{-- Cards Container --}}
                <div class="px-4 pb-4 pt-2 space-y-3" id="clientCardContainer">
                    @forelse ($clients as $client)
                    <div class="client-card bg-blue-100/70 hover:bg-blue-200/60 rounded-2xl p-4 transition duration-200 flex flex-col gap-3">
                        {{-- Header Card --}}
                        <div class="flex justify-between items-start gap-3">
                            <div class="space-y-1">
                                <h4 class="font-extrabold text-gray-800 text-sm leading-snug">
                                    {{ $client->nama_perusahaan }}
                                </h4>
                                <div class="text-xs text-slate-500 font-semibold flex items-center gap-1.5">
                                    <i class="fas fa-user-md text-blue-500"></i>
                                    <span>{{ $client->nama_user }}</span>
                                </div>
                            </div>
                            <a href="{{ route('crm.show', $client->id) }}" class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center transition shadow-md" title="Lihat Detail">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>

                        {{-- Metadata --}}
                        <div class="grid grid-cols-2 gap-4 pt-2.5 border-t border-slate-200/60 text-xs">
                            <div class="space-y-2">
                                <div>
                                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-1">Area</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200/50">
                                        {{ $client->area ?? 'Non-Area' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">PIC Sales</span>
                                    <span class="font-bold text-slate-700 text-[11px]">{{ $client->pic ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-1">Kontak Klien</span>
                                    @if($client->no_telpon)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->no_telpon) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 font-bold hover:underline">
                                            <i class="fab fa-whatsapp text-xs"></i> {{ $client->no_telpon }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Tidak ada telp</span>
                                    @endif
                                </div>
                                @if($client->email)
                                <div>
                                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Email</span>
                                    <span class="text-slate-650 text-[11px] block truncate max-w-[130px]" title="{{ $client->email }}">{{ $client->email }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Footer Card --}}
                        <div class="flex justify-between items-center pt-2.5 border-t border-slate-200/60 bg-emerald-50/50 -mx-4 -mb-4 px-4 py-2.5 rounded-b-2xl border-t-0">
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1">
                                <i class="fas fa-wallet text-emerald-600"></i> Total Saldo
                            </span>
                            <span class="font-mono font-extrabold text-emerald-700 text-sm">
                                Rp {{ number_format($client->current_balance, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-10 text-gray-400">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-folder-open text-xl opacity-50"></i>
                        </div>
                        <span class="font-medium text-xs">Belum ada data klien.</span>
                        <p class="text-[10px] mt-0.5">Silakan tambahkan klien baru.</p>
                    </div>
                    @endforelse
                </div>

                {{-- Pesan pencarian nihil --}}
                <div id="noResult" class="hidden px-6 py-10 text-center text-gray-400">
                    <i class="fas fa-search text-xl opacity-50 mb-2"></i>
                    <p class="text-xs">Data tidak ditemukan.</p>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL INPUT CLIENT BARU (Mobile Step/Tabbed Optimized) --}}
    @push('modals')
    <div id="createClientModal" class="hidden fixed inset-0 bg-gray-900/60 z-[9999] items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white w-full max-w-[92%] sm:max-w-md rounded-3xl shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[85vh]">
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-700 to-blue-600 px-5 py-4 border-b border-blue-500 flex justify-between items-center shrink-0">
                <h3 class="font-bold text-base text-white flex items-center">
                    <i class="fas fa-user-plus mr-2 text-blue-200"></i> Klien Baru
                </h3>
                <button onclick="toggleModal('createClientModal')" class="text-white hover:text-red-200 transition text-2xl font-bold focus:outline-none leading-none">&times;</button>
            </div>

            {{-- Underlined Tab Switcher --}}
            <div class="bg-white flex border-b border-slate-200 shrink-0">
                <button type="button" onclick="switchModalTab('personal')" id="btn-tab-personal" class="modal-tab-btn flex-1 py-3 text-xs font-bold transition-all text-center focus:outline-none border-b-2 border-blue-600 text-blue-600">
                    Personal
                </button>
                <button type="button" onclick="switchModalTab('company')" id="btn-tab-company" class="modal-tab-btn flex-1 py-3 text-xs font-bold transition-all text-center focus:outline-none border-b-2 border-transparent text-slate-500 hover:text-slate-700">
                    Perusahaan
                </button>
                <button type="button" onclick="switchModalTab('finance')" id="btn-tab-finance" class="modal-tab-btn flex-1 py-3 text-xs font-bold transition-all text-center focus:outline-none border-b-2 border-transparent text-slate-500 hover:text-slate-700">
                    Keuangan
                </button>
            </div>
            
            {{-- Form --}}
            <form action="{{ route('crm.store') }}" method="POST" class="flex flex-col flex-grow overflow-hidden">
                @csrf
                
                {{-- Scroll area --}}
                <div class="overflow-y-auto p-4 custom-scrollbar flex-grow bg-slate-200/60">
                    
                    {{-- 1. IDENTITAS PERSONAL --}}
                    <div id="modal-sec-personal" class="modal-sec-content modal-sec-content bg-white rounded-3xl p-5 space-y-4 shadow-sm border border-slate-200">
                        <div class="border-b border-slate-200 pb-2.5 mb-1 flex items-center justify-between">
                            <span class="text-xs font-extrabold text-blue-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-user-circle text-sm text-blue-600"></i> Data Diri User
                            </span>
                            <span class="text-[10px] text-slate-500 font-bold">Langkah 1 dari 3</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Nama User <span class="text-red-500 font-bold">*</span></label>
                            <input type="text" name="nama_user" required class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Nama Lengkap User">
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Jabatan</label>
                            <input type="text" name="jabatan" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Jabatan (Ex: Kepala Ruangan)">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">No. Telp (WA)</label>
                                <input type="text" name="no_telpon" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="08xxx">
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Email</label>
                                <input type="email" name="email" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Email">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Tgl Lahir</label>
                                <input type="date" name="tanggal_lahir" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all">
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Hobi</label>
                                <input type="text" name="hobby_client" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Ex: Golf">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Alamat Rumah</label>
                            <textarea name="alamat_user" rows="2" class="w-full bg-white border border-slate-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all resize-none placeholder-slate-400" placeholder="Alamat rumah..."></textarea>
                        </div>
                    </div>

                    {{-- 2. DATA PERUSAHAAN --}}
                    <div id="modal-sec-company" class="modal-sec-content modal-sec-content bg-white rounded-3xl p-5 space-y-4 shadow-sm border border-slate-100 hidden">
                        <div class="border-b border-slate-200 pb-2.5 mb-1 flex items-center justify-between">
                            <span class="text-xs font-extrabold text-orange-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-building text-sm text-orange-600"></i> Profil Instansi/RS
                            </span>
                            <span class="text-[10px] text-slate-500 font-bold">Langkah 2 dari 3</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Nama Instansi/RS <span class="text-red-500 font-bold">*</span></label>
                            <input type="text" name="nama_perusahaan" required class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Nama Perusahaan / RS">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Area</label>
                                <input type="text" name="area" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Area">
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Tgl Berdiri</label>
                                <input type="date" name="tanggal_berdiri" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Alamat Perusahaan</label>
                            <textarea name="alamat_perusahaan" rows="2" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all resize-none placeholder-slate-400" placeholder="Lokasi kantor..."></textarea>
                        </div>
                        <div class="border-t border-slate-200 pt-4">
                            <label class="block text-[11px] font-bold text-orange-800 mb-2.5 uppercase flex items-center gap-1.5"><i class="fas fa-user-md text-sm text-orange-600"></i> Data Apoteker</label>
                            <div class="space-y-2.5">
                                <input type="text" name="nama_apoteker" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Nama Apoteker">
                                <div class="grid grid-cols-2 gap-3">
                                    <input type="text" name="nomor_sipa" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="SIPA">
                                    <input type="text" name="no_telpon_apoteker" class="w-full bg-white border border-slate-300 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Telp">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. DATA KEUANGAN --}}
                    <div id="modal-sec-finance" class="modal-sec-content modal-sec-content bg-white rounded-3xl p-5 space-y-4 shadow-sm border border-slate-100 hidden">
                        <div class="border-b border-slate-200 pb-2.5 mb-1 flex items-center justify-between">
                            <span class="text-xs font-extrabold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-wallet text-sm text-emerald-600"></i> Rekening & Finansial
                            </span>
                            <span class="text-[10px] text-slate-500 font-bold">Langkah 3 dari 3</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Nama Bank</label>
                            <input type="text" name="bank" class="w-full bg-white border border-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="BCA / Mandiri">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">No. Rekening</label>
                                <input type="text" name="no_rekening" class="w-full bg-white border border-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 text-xs px-3.5 py-2.5 rounded-xl font-mono font-bold text-slate-750 transition-all placeholder-slate-400" placeholder="Rekening">
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-700 mb-1.5 uppercase tracking-wider">Atas Nama (A/N)</label>
                                <input type="text" name="nama_di_rekening" class="w-full bg-white border border-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 text-xs px-3.5 py-2.5 rounded-xl font-bold text-slate-800 transition-all placeholder-slate-400" placeholder="Nama Pemilik">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 border-t border-slate-150 pt-4">
                            <div>
                                <label class="block text-[11px] font-extrabold text-emerald-800 mb-1.5 uppercase tracking-wider">Komisi (%)</label>
                                <div class="flex rounded-xl overflow-hidden bg-white border border-slate-300 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-100 transition-all">
                                    <input type="number" step="0.01" name="komisi" class="flex-grow min-w-0 border-0 focus:outline-none text-xs font-bold text-emerald-800 px-3.5 py-2.5 bg-transparent" placeholder="Komisi">
                                    <span class="bg-slate-100 border-l border-slate-200 text-slate-600 font-bold text-xs px-3 flex items-center select-none shrink-0">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-extrabold text-emerald-800 mb-1.5 uppercase tracking-wider">Saldo Awal</label>
                                <div class="flex rounded-xl overflow-hidden bg-white border border-slate-300 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-100 transition-all">
                                    <span class="bg-slate-100 border-r border-slate-200 text-slate-600 font-bold text-[11px] px-3 flex items-center select-none shrink-0">Rp</span>
                                    <input type="number" name="saldo_awal" class="flex-grow min-w-0 border-0 focus:outline-none text-xs font-bold text-emerald-800 px-3.5 py-2.5 bg-transparent" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-4 py-3.5 border-t border-gray-200 flex justify-between gap-2 shrink-0">
                    <button type="button" onclick="toggleModal('createClientModal')" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-100 transition shadow-sm active:scale-95">
                        Batal
                    </button>
                    <div class="flex gap-2">
                        <button type="button" id="modal-prev-btn" onclick="navigateModalTab(-1)" class="hidden px-3.5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition shadow-sm active:scale-95">
                            Sebelumnya
                        </button>
                        <button type="button" id="modal-next-btn" onclick="navigateModalTab(1)" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition active:scale-95">
                            Lanjut
                        </button>
                        <button type="submit" id="modal-submit-btn" class="hidden px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition transform active:scale-95 flex items-center">
                            <i class="fas fa-save mr-1.5 text-blue-200"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endpush
    
    @push('scripts')
    <script>
        // Tab system for Add Client Modal
        let activeModalTab = 'personal';
        const modalTabOrder = ['personal', 'company', 'finance'];

        function switchModalTab(tabId) {
            // Validate if moving forward to a future tab
            const currentIndex = modalTabOrder.indexOf(activeModalTab);
            const targetIndex = modalTabOrder.indexOf(tabId);
            
            if (targetIndex > currentIndex) {
                const currentSecMap = { 'personal': 'modal-sec-personal', 'company': 'modal-sec-company', 'finance': 'modal-sec-finance' };
                const currentSection = document.getElementById(currentSecMap[activeModalTab]);
                const requiredInputs = currentSection.querySelectorAll('[required]');
                
                let isValid = true;
                for (let input of requiredInputs) {
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        isValid = false;
                        break;
                    }
                }
                if (!isValid) return; // Prevent switching tabs
            }

            activeModalTab = tabId;
            
            // Hide all sections
            document.querySelectorAll('.modal-sec-content').forEach(el => el.classList.add('hidden'));
            
            // Show target section
            const targetSecMap = { 'personal': 'modal-sec-personal', 'company': 'modal-sec-company', 'finance': 'modal-sec-finance' };
            document.getElementById(targetSecMap[tabId]).classList.remove('hidden');
            
            // Update tabs style
            document.querySelectorAll('.modal-tab-btn').forEach(btn => {
                btn.className = "modal-tab-btn flex-1 py-3 text-xs font-bold transition-all text-center focus:outline-none border-b-2 border-transparent text-slate-500 hover:text-slate-700";
            });
            
            let activeColorClass = "";
            if(tabId === 'personal') activeColorClass = "border-blue-600 text-blue-600";
            else if(tabId === 'company') activeColorClass = "border-orange-500 text-orange-600";
            else if(tabId === 'finance') activeColorClass = "border-emerald-600 text-emerald-600";
            
            document.getElementById('btn-tab-' + tabId).className = "modal-tab-btn flex-1 py-3 text-xs font-bold transition-all text-center focus:outline-none border-b-2 " + activeColorClass;
            
            // Update navigation buttons
            const prevBtn = document.getElementById('modal-prev-btn');
            const nextBtn = document.getElementById('modal-next-btn');
            const submitBtn = document.getElementById('modal-submit-btn');
            
            if (targetIndex === 0) {
                prevBtn.classList.add('hidden');
            } else {
                prevBtn.classList.remove('hidden');
            }
            
            if (targetIndex === modalTabOrder.length - 1) {
                nextBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            } else {
                nextBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        function navigateModalTab(dir) {
            const currentIndex = modalTabOrder.indexOf(activeModalTab);
            const targetIndex = currentIndex + dir;
            if (targetIndex >= 0 && targetIndex < modalTabOrder.length) {
                switchModalTab(modalTabOrder[targetIndex]);
            }
        }

        // Modal toggling helper
        function toggleModal(id) {
            const modal = document.getElementById(id);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
                // Reset to first tab on open
                if (id === 'createClientModal') {
                    activeModalTab = 'personal'; // Reset variable directly to skip validation check yul
                    switchModalTab('personal');
                }
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        }

        // Live search cards for mobile
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let cards = document.querySelectorAll('.client-card');
            let hasResult = false;

            cards.forEach(card => {
                let text = card.innerText.toLowerCase();
                if(text.includes(filter)) {
                    card.classList.remove('hidden');
                    hasResult = true;
                } else {
                    card.classList.add('hidden');
                }
            });

            const noResultDiv = document.getElementById('noResult');
            if (!hasResult && cards.length > 0) {
                noResultDiv.classList.remove('hidden');
            } else {
                noResultDiv.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-layout-users>