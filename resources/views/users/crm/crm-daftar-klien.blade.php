<x-layout-users :title="'Sistem Informasi Sales (CRM)'">

    {{-- Notifikasi Sukses --}}
    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r shadow-sm flex items-center animate-fade-in-down">
            <i class="fas fa-check-circle mr-3 text-lg"></i>
            <div>
                <p class="font-bold">Berhasil</p>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- BAGIAN 1: HERO HEADER --}}
    <div class="bg-[#001BB7] rounded-3xl shadow-xl shadow-blue-900/20 mb-10 overflow-hidden relative">
        
        {{-- Dekorasi Background --}}
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-blue-400 opacity-20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="p-8 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                
                {{-- Judul & Deskripsi --}}
                <div class="text-white">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-white/20 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-white/30">
                            Dashboard CRM
                        </span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight drop-shadow-sm mb-2">
                        Overview Sales & Klien
                    </h2>
                    <p class="text-blue-100 opacity-90 text-sm max-w-xl leading-relaxed">
                        Monitor performa area, PIC, dan database customer relationship management.
                    </p>
                </div>

                {{-- Action Buttons (Tanpa Animasi Naik) --}}
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('crm.matrix') }}" class="bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-sm text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-colors flex items-center">
                        <i class="fas fa-table mr-2"></i> Matrix Laporan
                    </a>
                    <button onclick="toggleModal('createClientModal')" class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-orange-900/20 transition-colors flex items-center border border-orange-400">
                        <i class="fas fa-plus mr-2"></i> Tambah Klien
                    </button>
                </div>
            </div>

            {{-- Mini Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
                {{-- Stat 1: Total Klien --}}
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center text-white text-xl">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-blue-200 text-xs font-bold uppercase tracking-wider">Total Klien Aktif</p>
                        <p class="text-white text-2xl font-extrabold">{{ $clients->count() }}</p>
                    </div>
                </div>

                {{-- Stat 2: Top Area --}}
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-purple-500/80 flex items-center justify-center text-white text-xl shadow-lg">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div>
                        <p class="text-blue-200 text-xs font-bold uppercase tracking-wider">Total Nilai Sales</p>
                        <p class="text-emerald-300 text-2xl font-mono font-bold">
                            Rp {{ number_format($totalGrossSales, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                 {{-- Stat 3: Total Saldo (NET) --}}
                 <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-emerald-500/80 flex items-center justify-center text-white text-xl shadow-lg">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        {{-- Ubah Label --}}
                        <p class="text-blue-200 text-xs font-bold uppercase tracking-wider">
                            Total Saldo 
                        </p>
                        
                        {{-- Ubah Value ke variabel baru --}}
                        <p class="text-emerald-300 text-2xl font-mono font-bold">
                            Rp {{ number_format($totalAllBalance, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BAGIAN 2: DATA LIST --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden -mt-6 relative z-20 mx-2 md:mx-0">
        {{-- Header Tabel & Search --}}
        <div class="px-8 py-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-white">
            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-list-ul text-blue-600"></i> Database Klien
            </h3>
            
            {{-- Search Bar Fungsional --}}
            <div class="relative w-full md:w-72">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="text" id="searchInput" placeholder="Cari nama, RS, atau area..." class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 transition-colors">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="clientTable">
                <thead class="bg-gray-50/50 text-gray-500 uppercase font-bold text-[11px] tracking-widest border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Profil Instansi & User</th>
                        <th class="px-6 py-4">Area & PIC</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4 text-right">Total Saldo</th>
                        <th class="px-6 py-4 text-center">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="clientTableBody">
                    @forelse ($clients as $client)
                    <tr class="group hover:bg-blue-50/40 transition duration-200">
                        {{-- Kolom 1: Profil (Tanpa Avatar) --}}
                        <td class="px-6 py-4 align-middle">
                            <div class="flex flex-col">
                                <div class="font-bold text-gray-800 text-base group-hover:text-blue-700 transition mb-1">
                                    {{ $client->nama_perusahaan }}
                                </div>
                                <div class="text-xs text-gray-500 font-medium flex items-center gap-1">
                                    <i class="fas fa-user-md text-blue-400"></i> {{ $client->nama_user }}
                                </div>
                            </div>
                        </td>

                        {{-- Kolom 2: Area --}}
                        <td class="px-6 py-4 align-middle">
                            <div class="flex flex-col items-start gap-1.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $client->area ?? 'Non-Area' }}
                                </span>
                                <span class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold">
                                    PIC: {{ $client->pic ?? '-' }}
                                </span>
                            </div>
                        </td>

                        {{-- Kolom 3: Kontak --}}
                        <td class="px-6 py-4 align-middle">
                            <div class="space-y-1">
                                @if($client->no_telpon)
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <i class="fab fa-whatsapp text-emerald-500 w-4 text-center"></i> 
                                        <span>{{ $client->no_telpon }}</span>
                                    </div>
                                @endif
                                @if($client->email)
                                    <div class="flex items-center gap-2 text-xs text-gray-600">
                                        <i class="fas fa-envelope text-blue-400 w-4 text-center"></i> 
                                        <span>{{ \Illuminate\Support\Str::limit($client->email, 25) }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Kolom 4: Sales (Tanpa Teks 'Lifetime Sales') --}}
                        <td class="px-6 py-4 align-middle text-right">
                            <span class="font-mono font-bold text-emerald-600 text-sm">
                                Rp {{ number_format($client->current_balance, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- Kolom 5: Aksi --}}
                        <td class="px-6 py-4 align-middle text-center">
                            <a href="{{ route('crm.show', $client->id) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-gray-200 text-gray-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition shadow-sm group-hover:border-blue-200" title="Lihat Detail">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center bg-gray-50/30">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-folder-open text-2xl opacity-50"></i>
                                </div>
                                <span class="font-medium">Belum ada data klien.</span>
                                <p class="text-xs mt-1">Silahkan tambahkan klien baru.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            {{-- Pesan jika pencarian tidak ditemukan --}}
            <div id="noResult" class="hidden px-6 py-12 text-center bg-gray-50/30 text-gray-400">
                <i class="fas fa-search text-2xl opacity-50 mb-2"></i>
                <p>Data tidak ditemukan.</p>
            </div>
        </div>
    </div>

    {{-- MODAL INPUT CLIENT BARU --}}
    @push('modals')
    <div id="createClientModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 z-[9999] flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        
        {{-- MODAL CONTAINER: Lebar (6xl) tapi Max Tinggi Terbatas agar tetap terlihat seperti 'Pop-up' --}}
        <div class="bg-white w-full md:max-w-6xl rounded-2xl shadow-2xl overflow-hidden border border-gray-200 flex flex-col max-h-[90vh]">
            
            {{-- HEADER MODAL --}}
            <div class="bg-gradient-to-r from-blue-700 to-blue-600 px-6 py-4 border-b border-blue-500 flex justify-between items-center shadow-md z-10 shrink-0">
                <h3 class="font-bold text-lg text-white flex items-center">
                    <i class="fas fa-user-plus mr-3"></i> Input Data Klien Baru
                </h3>
                <button onclick="toggleModal('createClientModal')" class="text-white hover:text-red-200 transition text-2xl font-bold focus:outline-none">&times;</button>
            </div>
            
            {{-- FORM BODY --}}
            <form action="{{ route('crm.store') }}" method="POST" class="flex flex-col flex-grow overflow-hidden">
                @csrf
                
                {{-- Scrollable Content Area --}}
                <div class="overflow-y-auto p-6 custom-scrollbar flex-grow bg-gray-50/30">
                    
                    {{-- GRID 3 KOLOM (Landscape) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch h-full">

                        {{-- KOLOM 1: INFO CLIENT (Style Biru) --}}
                        <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden flex flex-col h-full">
                            <div class="bg-blue-50/80 px-4 py-3 border-b border-blue-300 flex items-center">
                                <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">1</span>
                                <h4 class="text-blue-800 text-xs font-bold uppercase tracking-wider">Identitas Personal</h4>
                            </div>
                            <div class="p-4 space-y-3 flex-grow">
                                {{-- NAMA & JABATAN --}}
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 mb-1 uppercase">Nama & Jabatan <span class="text-red-500">*</span></label>
                                    <div class="space-y-2">
                                        <input type="text" name="nama_user" required class="w-full border-2 border-blue-100 rounded focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2 font-bold" placeholder="Nama Lengkap User">
                                        <input type="text" name="jabatan" class="w-full border-2 border-blue-100 rounded focus:ring-blue-500 focus:border-blue-500 text-xs px-3 py-2" placeholder="Jabatan (Ex: Kepala Ruangan / Manager)">
                                    </div>
                                </div>

                                {{-- KONTAK --}}
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 mb-1 uppercase">Kontak Personal</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="no_telpon" class="w-full border-2 border-blue-100 rounded text-sm focus:ring-blue-500 px-3 py-2" placeholder="08xxxx (WA)">
                                        <input type="email" name="email" class="w-full border-2 border-blue-100 rounded text-sm focus:ring-blue-500 px-3 py-2" placeholder="Email">
                                    </div>
                                </div>

                                {{-- TGL LAHIR & HOBI --}}
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-500 mb-1 uppercase">Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" class="w-full border-2 border-blue-100 rounded text-sm focus:ring-blue-500 px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-500 mb-1 uppercase">Hobi / Minat</label>
                                        <input type="text" name="hobby_client" class="w-full border-2 border-blue-100 rounded text-sm focus:ring-blue-500 px-3 py-2" placeholder="Ex: Golf, Kopi">
                                    </div>
                                </div>

                                {{-- ALAMAT --}}
                                <div class="flex-grow">
                                    <label class="block text-[11px] font-bold text-gray-500 mb-1 uppercase">Alamat Rumah</label>
                                    <textarea name="alamat_user" rows="2" class="w-full border-2 border-blue-100 rounded text-sm focus:ring-blue-500 px-3 py-2 resize-none" placeholder="Alamat tempat tinggal..."></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- KOLOM 2: INFO PERUSAHAAN (Style Orange) --}}
                        <div class="bg-white rounded-xl border border-orange-100 shadow-sm overflow-hidden flex flex-col h-full">
                            <div class="bg-orange-50/80 px-4 py-3 border-b border-orange-100 flex items-center">
                                <span class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">2</span>
                                <h4 class="text-orange-800 text-xs font-bold uppercase tracking-wider">Data Perusahaan</h4>
                            </div>
                            <div class="p-4 space-y-3 flex-grow">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 mb-1 uppercase">Nama Perusahaan / PT <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama_perusahaan" required class="w-full border-2 border-orange-100 rounded focus:ring-orange-500 focus:border-orange-500 text-sm px-3 py-2 font-semibold" placeholder="Nama Instansi">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 mb-1 uppercase">Detail Perusahaan</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="area" class="w-full border-2 border-orange-100 rounded text-sm focus:ring-orange-500 px-3 py-2" placeholder="Area (Ex: Jaksel)">
                                        <input type="date" name="tanggal_berdiri" class="w-full border-2 border-orange-100 rounded text-sm focus:ring-orange-500 px-3 py-2" title="Tanggal Berdiri">
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <label class="block text-[11px] font-bold text-gray-500 mb-1 uppercase">Alamat Perusahaan</label>
                                    <textarea name="alamat_perusahaan" rows="5" class="w-full border-2 border-orange-100 rounded text-sm focus:ring-orange-500 px-3 py-2 resize-none" placeholder="Lokasi kantor..."></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- KOLOM 3: INFO BANK (Style Hijau) --}}
                        <div class="bg-white rounded-xl border border-emerald-100 shadow-sm overflow-hidden flex flex-col h-full">
                            <div class="bg-emerald-50/80 px-4 py-3 border-b border-emerald-100 flex items-center">
                                <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">3</span>
                                <h4 class="text-emerald-800 text-xs font-bold uppercase tracking-wider">Keuangan & Bank</h4>
                            </div>
                            <div class="p-4 space-y-3 flex-grow">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 mb-1 uppercase">Nama Bank</label>
                                    <input type="text" name="bank" class="w-full border-2 border-emerald-100 rounded text-sm focus:ring-emerald-500 px-3 py-2" placeholder="Ex: BCA / Mandiri">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 mb-1 uppercase">No. Rekening</label>
                                    <input type="text" name="no_rekening" class="w-full border-2 border-emerald-100 rounded text-sm focus:ring-emerald-500 px-3 py-2 font-mono" placeholder="123xxxxx">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-500 mb-1 uppercase">Atas Nama (A/N)</label>
                                    <input type="text" name="nama_di_rekening" class="w-full border-2 border-emerald-100 rounded text-sm focus:ring-emerald-500 px-3 py-2" placeholder="Pemilik Rekening">
                                </div>
                                
                                <div class="mt-auto pt-3 border-t border-emerald-50">
                                    <label class="block text-[11px] font-bold text-emerald-700 mb-1 uppercase">Saldo Awal</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-emerald-600 font-bold text-xs">Rp</span>
                                        <input type="number" name="saldo_awal" class="w-full pl-8 border-2 border-emerald-100 bg-emerald-50/30 rounded text-lg font-bold text-emerald-800 focus:ring-emerald-500 px-3 py-1.5" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- FOOTER MODAL --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="toggleModal('createClientModal')" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition transform active:scale-95 flex items-center">
                        <i class="fas fa-save mr-2"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endpush

    @push('scripts')
    <script>
        // Script untuk Modal
        function toggleModal(id) {
            const modal = document.getElementById(id);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        }

        // Script Search Real-time (Sesuai request)
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#clientTableBody tr');
            let hasResult = false;

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                if(text.includes(filter)) {
                    row.classList.remove('hidden');
                    hasResult = true;
                } else {
                    row.classList.add('hidden');
                }
            });

            const noResultDiv = document.getElementById('noResult');
            if (!hasResult && rows.length > 0) {
                noResultDiv.classList.remove('hidden');
            } else {
                noResultDiv.classList.add('hidden');
            }
        });
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
    @endpush
</x-layout-users>