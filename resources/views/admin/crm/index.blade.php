<x-layout-admin :title="'Monitoring Sales'">
    
    {{-- Notifikasi Sukses --}}
    

    {{-- Error Handling Modal --}}
    @if ($errors->any())
        <div class="mb-6 bg-red-900/50 border-l-4 border-red-500 text-red-200 p-4 rounded-r shadow-sm">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @if($errors->hasBag('createClient'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    toggleModal('createClientModal');
                });
            </script>
        @endif
    @endif

    {{-- Header & Toolbar --}}
    <div class="mb-6 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Monitoring Sales & Klien</h1>
            <p class="text-zinc-400 text-sm">Rekapitulasi seluruh aktivitas sales tim Rakha Medika.</p>
        </div>
        
        <div class="flex flex-wrap gap-3 w-full xl:w-auto items-center">
            <a href="{{ route('admin.crm.matrix.export', ['year' => request('year', date('Y')), 'user_id' => request('user_id')]) }}" 
               class="bg-emerald-700 hover:bg-emerald-600 text-white text-sm font-bold py-2.5 px-4 rounded-lg shadow-lg flex items-center transition-colors border border-emerald-600">
                <i class="fas fa-file-excel mr-2"></i> Export Matrix
            </a>

            <button onclick="toggleModal('createClientModal')" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold py-2.5 px-4 rounded-lg shadow-lg flex items-center transition-colors border border-amber-500/50">
                <i class="fas fa-plus mr-2"></i> Tambah Klien
            </button>

            <form action="{{ route('admin.crm.index') }}" method="GET" class="flex items-center gap-2">
                 <select name="user_id" onchange="this.form.submit()" class="bg-zinc-900 border border-zinc-700 text-zinc-300 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block p-2.5">
                    <option value="">-- Semua Sales --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ $filterUser == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- Statistik Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-zinc-800 p-6 rounded-xl shadow-lg border border-zinc-700/50 flex flex-col justify-between h-full">
            <div>
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Total Klien Aktif</p>
                <h3 class="text-3xl font-extrabold text-white">{{ $clients->total() }} <span class="text-sm font-medium text-zinc-500">Perusahaan</span></h3>
            </div>
            <div class="mt-4 flex items-center text-xs text-amber-500 font-bold">
                <i class="fas fa-users mr-1"></i> Data Terupdate
            </div>
        </div>

        <div class="bg-zinc-800 p-6 rounded-xl shadow-lg border border-zinc-700/50 flex flex-col justify-between h-full">
            <div>
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Total Realisasi</p>
                <h3 class="text-3xl font-extrabold text-emerald-500 truncate">
                    <span class="text-lg text-zinc-500 mr-1">Rp</span>{{ number_format($totalUsage, 0, ',', '.') }}
                </h3>
            </div>
            <div class="mt-4 flex items-center text-xs text-emerald-500 font-bold">
                <i class="fas fa-chart-line mr-1"></i> Akumulasi Usage (Out)
            </div>
        </div>

        <div class="bg-zinc-800 p-6 rounded-xl shadow-lg border border-zinc-700/50 flex flex-col justify-between h-full">
            <div>
                <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Total Saldo (Net)</p>
                <h3 class="text-3xl font-extrabold text-blue-400 truncate">
                    <span class="text-lg text-zinc-500 mr-1">Rp</span>{{ number_format($totalNet, 0, ',', '.') }}
                </h3>
            </div>
            <div class="mt-4 flex items-center text-xs text-blue-400 font-bold">
                <i class="fas fa-wallet mr-1"></i> Saldo - Usage
            </div>
        </div>
    </div>

    {{-- Tabel Monitoring --}}
    <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-700 bg-zinc-800/50 flex justify-between items-center">
            <h3 class="font-bold text-zinc-200">Daftar Klien & Sales</h3>
            
            <div class="relative w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" id="adminSearchInput" placeholder="Cari klien..." class="w-full pl-8 pr-3 py-1.5 rounded-md border border-zinc-700 bg-zinc-900 text-zinc-300 text-xs focus:ring-amber-500 focus:border-amber-500 placeholder-zinc-600">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left whitespace-nowrap text-zinc-300">
                <thead class="bg-zinc-900/50 text-zinc-400 uppercase text-xs font-bold tracking-wider border-b border-zinc-700">
                    <tr>
                        <th class="px-6 py-4">Profil Instansi & User</th>
                        <th class="px-6 py-4">Area & PIC</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4 text-right">Total Saldo</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-700" id="adminClientTableBody">
                    @forelse($clients as $client)
                    @php
                        $row_net_val = 0;
                        $row_usage = 0;

                        foreach($client->interactions as $i) {
                            if($i->transaction_type == 'IN') {
                                $gross = ($i->sales_amount > 0) ? $i->sales_amount : $i->amount;
                                $r = (float)($i->commission_rate ?? 0);
                                $row_net_val += $gross * ($r/100);
                            } elseif ($i->transaction_type == 'OUT') {
                                $row_usage += $i->amount;
                            }
                        }
                        
                        $row_saldo = ($client->opening_balance ?? 0) + $row_net_val - $row_usage;
                    @endphp

                    <tr class="hover:bg-zinc-700/30 transition duration-150">
                        <td class="px-6 py-4">
                            <div class="font-bold text-white text-base">{{ $client->customer_name }}</div>
                            <div class="text-xs text-zinc-400 font-medium flex items-center gap-1 mt-0.5">
                                <i class="fas fa-user-md text-blue-400"></i> {{ $client->client_name }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-start gap-1.5">
                                <span class="bg-zinc-700 text-zinc-300 text-xs font-medium px-2.5 py-0.5 rounded border border-zinc-600">
                                    {{ $client->area ?? 'Non-Area' }}
                                </span>
                                <span class="text-[10px] text-zinc-500 uppercase tracking-wide font-semibold">
                                    PIC: {{ $client->ps ?? $client->user->name ?? '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                @if($client->contact_phone)
                                    <div class="flex items-center gap-2 text-xs text-zinc-300">
                                        <i class="fab fa-whatsapp text-emerald-500 w-4 text-center"></i>
                                        <span>{{ $client->contact_phone }}</span>
                                    </div>
                                @endif
                                @if($client->email)
                                    <div class="flex items-center gap-2 text-xs text-zinc-300">
                                        <i class="fas fa-envelope text-blue-400 w-4 text-center"></i>
                                        <span>{{ \Illuminate\Support\Str::limit($client->email, 25) }}</span>
                                    </div>
                                @endif
                                @if(!$client->contact_phone && !$client->email)
                                    <span class="text-xs text-zinc-600">-</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4 text-right font-mono font-bold {{ $row_saldo < 0 ? 'text-red-400' : 'text-blue-400' }}">
                            Rp {{ number_format($row_saldo, 0, ',', '.') }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.crm.show', $client->id) }}" class="text-white bg-amber-600 hover:bg-amber-700 focus:ring-4 focus:ring-amber-900 font-medium rounded-lg text-xs px-3 py-2 transition shadow-sm">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-zinc-500 bg-zinc-800/50">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-folder-open text-4xl mb-3 text-zinc-600"></i>
                                <p>Belum ada data klien yang ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($clients->hasPages())
        <div class="bg-zinc-800 px-6 py-4 border-t border-zinc-700">
            {{ $clients->withQueryString()->links() }} 
        </div>
        @endif
    </div>

    {{-- MODAL INPUT CLIENT BARU (ADMIN STYLE: DARK) --}}
    <div id="createClientModal" class="hidden fixed inset-0 bg-black bg-opacity-80 z-[9999] flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        
        <div class="bg-zinc-900 w-full md:max-w-6xl rounded-2xl shadow-2xl overflow-hidden border border-zinc-700 flex flex-col max-h-[90vh]">
            
            <div class="bg-zinc-800 px-6 py-4 border-b border-zinc-700 flex justify-between items-center shadow-md z-10 shrink-0">
                <h3 class="font-bold text-lg text-white flex items-center">
                    <i class="fas fa-user-plus mr-3 text-amber-500"></i> Input Data Klien Baru (Admin)
                </h3>
                <button onclick="toggleModal('createClientModal')" class="text-zinc-400 hover:text-white transition text-2xl font-bold focus:outline-none">&times;</button>
            </div>
            
            <form action="{{ route('admin.crm.store') }}" method="POST" class="flex flex-col flex-grow overflow-hidden">
                @csrf
                
                <div class="overflow-y-auto p-6 custom-scrollbar flex-grow bg-zinc-900">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch h-full">

                        {{-- KOLOM 1: IDENTITAS --}}
                        <div class="bg-zinc-800 rounded-xl border border-zinc-700 shadow-sm overflow-hidden flex flex-col h-full">
                            <div class="bg-zinc-700/50 px-4 py-3 border-b border-zinc-600 flex items-center">
                                <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">1</span>
                                <h4 class="text-blue-300 text-xs font-bold uppercase tracking-wider">Identitas Personal</h4>
                            </div>
                            <div class="p-4 space-y-3 flex-grow">
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-400 mb-1 uppercase">Pilih Sales (PIC) <span class="text-red-500">*</span></label>
                                    <select name="user_id" required class="w-full bg-zinc-900 border border-zinc-600 rounded focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2 text-white font-semibold">
                                        <option value="">-- Pilih Sales Penanggung Jawab --</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-400 mb-1 uppercase">Nama Client / User <span class="text-red-500">*</span></label>
                                    <input type="text" name="client_name" required value="{{ old('client_name') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2 text-white font-semibold" placeholder="Nama Lengkap User">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-400 mb-1 uppercase">Jabatan</label>
                                    <input type="text" name="contact_position" value="{{ old('contact_position') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2 text-white" placeholder="Contoh: Direktur / Manager">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-500 mb-1 uppercase">Kontak Personal</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="08xxxx (WA)">
                                        <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Email">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[11px] font-bold text-zinc-500 mb-1 uppercase">Tanggal Lahir</label>
                                        <input type="date" name="contact_birth_date" value="{{ old('contact_birth_date') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 [color-scheme:dark] focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-zinc-500 mb-1 uppercase">Hobby / Minat</label>
                                        <input type="text" name="contact_hobby" value="{{ old('contact_hobby') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Golf">
                                    </div>
                                </div>

                                <div class="flex-grow">
                                    <label class="block text-[11px] font-bold text-zinc-500 mb-1 uppercase">Alamat Rumah</label>
                                    <textarea name="contact_address" rows="2" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 resize-none focus:ring-blue-500 focus:border-blue-500" placeholder="Alamat tempat tinggal...">{{ old('contact_address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- KOLOM 2: PERUSAHAAN --}}
                        <div class="bg-zinc-800 rounded-xl border border-zinc-700 shadow-sm overflow-hidden flex flex-col h-full">
                            <div class="bg-zinc-700/50 px-4 py-3 border-b border-zinc-600 flex items-center">
                                <span class="bg-amber-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">2</span>
                                <h4 class="text-amber-400 text-xs font-bold uppercase tracking-wider">Data Perusahaan</h4>
                            </div>
                            <div class="p-4 space-y-3 flex-grow">
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-400 mb-1 uppercase">Nama Perusahaan / PT <span class="text-red-500">*</span></label>
                                    <input type="text" name="customer_name" required value="{{ old('customer_name') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded focus:ring-amber-500 focus:border-amber-500 text-sm px-3 py-2 text-white font-semibold" placeholder="Nama Instansi">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-400 mb-1 uppercase">Nama di Sales / Command Center</label>
                                    <input type="text" name="sales_customer_name" value="{{ old('sales_customer_name') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded focus:ring-amber-500 focus:border-amber-500 text-sm px-3 py-2 text-white font-semibold" placeholder="Nama di Sales (opsional)">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-500 mb-1 uppercase">Detail Perusahaan</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="area" value="{{ old('area') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 focus:ring-amber-500 focus:border-amber-500" placeholder="Area (Ex: Jaksel)">
                                        <input type="date" name="company_founded_date" value="{{ old('company_founded_date') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 [color-scheme:dark] focus:ring-amber-500 focus:border-amber-500">
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <label class="block text-[11px] font-bold text-zinc-500 mb-1 uppercase">Alamat Perusahaan</label>
                                    <textarea name="company_address" rows="5" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 resize-none focus:ring-amber-500 focus:border-amber-500" placeholder="Lokasi kantor...">{{ old('company_address') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-500 mb-1 uppercase">Apoteker</label>
                                    <div class="space-y-2">
                                        <input type="text" name="pharmacist_name" value="{{ old('pharmacist_name') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 focus:ring-amber-500 focus:border-amber-500" placeholder="Nama Apoteker">
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" name="pharmacist_license_no" value="{{ old('pharmacist_license_no') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 focus:ring-amber-500 focus:border-amber-500" placeholder="Nomor SIPA">
                                            <input type="text" name="pharmacist_phone" value="{{ old('pharmacist_phone') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 focus:ring-amber-500 focus:border-amber-500" placeholder="Telp Apoteker">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KOLOM 3: BANK --}}
                        <div class="bg-zinc-800 rounded-xl border border-zinc-700 shadow-sm overflow-hidden flex flex-col h-full">
                            <div class="bg-zinc-700/50 px-4 py-3 border-b border-zinc-600 flex items-center">
                                <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mr-2">3</span>
                                <h4 class="text-emerald-400 text-xs font-bold uppercase tracking-wider">Keuangan & Bank</h4>
                            </div>
                            <div class="p-4 space-y-3 flex-grow">
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-500 mb-1 uppercase">Nama Bank</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Ex: BCA / Mandiri">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-500 mb-1 uppercase">No. Rekening</label>
                                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 font-mono focus:ring-emerald-500 focus:border-emerald-500" placeholder="123xxxxx">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-zinc-500 mb-1 uppercase">Atas Nama (A/N)</label>
                                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" class="w-full bg-zinc-900 border border-zinc-600 rounded text-sm text-white px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Pemilik Rekening">
                                </div>
                                
                                <div class="mt-auto pt-3 border-t border-zinc-700">
                                    <label class="block text-[11px] font-bold text-emerald-500 mb-1 uppercase">Saldo Awal</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-emerald-500 font-bold text-xs">Rp</span>
                                        <input type="number" name="opening_balance" value="{{ old('opening_balance') }}" class="w-full pl-8 bg-zinc-900 border border-zinc-600 rounded text-lg font-bold text-white focus:ring-emerald-500 px-3 py-1.5 placeholder-zinc-600" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- FOOTER MODAL --}}
                <div class="bg-zinc-800 px-6 py-4 border-t border-zinc-700 flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="toggleModal('createClientModal')" class="px-5 py-2.5 bg-zinc-700 hover:bg-zinc-600 text-white rounded-lg text-sm font-bold transition border border-zinc-600">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition flex items-center">
                        <i class="fas fa-save mr-2"></i> Simpan (Admin)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
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

        // Search Script Admin
        document.getElementById('adminSearchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#adminClientTableBody tr');

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                if(text.includes(filter)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        });
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #18181b; } 
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 3px; } 
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #52525b; }
    </style>

</x-layout-admin>