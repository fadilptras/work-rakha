<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    {{-- Background disamakan dengan layout detail --}}
    <div class="bg-gradient-to-br from-sky-50 to-blue-100 p-0 md:p-0 min-h-screen">
        <div class="container mx-auto p-0 md:p-0">

            {{-- ALERT SUCCESS --}}
            @if (session('success'))
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-lg shadow-md mb-6" role="alert">
                    <p class="font-bold">Sukses!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            {{-- ALERT ERROR --}}
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md mb-6" role="alert">
                    <p class="font-bold">Error!</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            {{-- ALERT VALIDATION --}}
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md mb-6" role="alert">
                    <div class="flex">
                        <div class="py-1"><i class="fas fa-exclamation-triangle text-red-500 mr-3"></i></div>
                        <div>
                            <p class="font-bold">Oops! Ada yang salah dengan input Anda:</p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- TOMBOL KEMBALI --}}
            <div class="mb-6">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-[#043915] rounded-xl shadow-lg hover:bg-[#043915] hover:shadow-xl transition-all duration-300 w-full sm:w-auto">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Dashboard
                </a>
            </div>
            
            <form action="{{ route('pengajuan_dana.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-8">
                    
                    {{-- KARTU 1: DETAIL PENGAJUAN --}}
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        {{-- Header Hijau Tua --}}
                        <div class="p-6 md:p-8 bg-[#043915] text-white">
                            <div class="flex items-center gap-4">
                                <i class="fas fa-file-alt text-3xl opacity-80"></i>
                                <div>
                                    <h2 class="text-xl font-bold">1. Detail Pengajuan Dana</h2>
                                    <p class="text-sm text-slate-300">Informasi dasar mengenai pemohon dan judul pengajuan.</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 md:p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Pemohon</label>
                                    <input type="text" class="w-full p-3 bg-slate-100 border-transparent rounded-lg" value="{{ Auth::user()->name }}" readonly>
                                    <input type="hidden" name="nama_pemohon" value="{{ Auth::user()->name }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Divisi</label>
                                    <input type="text" class="w-full p-3 bg-slate-100 border-transparent rounded-lg" value="{{ Auth::user()->divisi }}" readonly>
                                    <input type="hidden" name="divisi" value="{{ Auth::user()->divisi }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Jabatan</label>
                                    <input type="text" class="w-full p-3 bg-slate-100 border-transparent rounded-lg" value="{{ Auth::user()->jabatan ?? '-' }}" readonly>
                                </div>
                                 <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                    <input type="email" class="w-full p-3 bg-slate-100 border-transparent rounded-lg" value="{{ Auth::user()->email }}" readonly>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1" for="judul-pengajuan">Judul Pengajuan <span class="text-red-500">*</span></label>
                                    {{-- Focus Ring diubah ke Hijau Tua --}}
                                    <input type="text" id="judul-pengajuan" name="judul_pengajuan" class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#043915] focus:shadow-inner transition-all" placeholder="Contoh: Pembelian Perlengkapan Kantor" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pengajuan</label>
                                    <input type="text" class="w-full p-3 bg-slate-100 border-transparent rounded-lg" value="{{ date('d F Y') }}" readonly>
                                    <input type="hidden" name="tanggal_pengajuan" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KARTU 2: INFORMASI REKENING --}}
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="p-6 md:p-8 bg-[#043915] text-white">
                            <div class="flex items-center gap-4">
                                <i class="fas fa-university text-3xl opacity-80"></i>
                                <div>
                                    <h2 class="text-xl font-bold">2. Informasi Rekening</h2>
                                    <p class="text-sm text-slate-300">Tujuan transfer dana jika pengajuan disetujui.</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 md:p-8">
                            {{-- Ubah Grid menjadi 3 Kolom di layar sedang (md) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                
                                {{-- Kolom 1: Bank --}}
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1" for="pilih-bank">Pilih Bank <span class="text-red-500">*</span></label>
                                    <select id="pilih-bank" name="nama_bank" class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#043915] focus:shadow-inner transition-all" required>
                                        <option value="" disabled selected>Pilih Bank</option>
                                        <option value="BCA">BCA</option>
                                        <option value="BRI">BRI</option>
                                        <option value="BNI">BNI</option>
                                        <option value="Mandiri">Mandiri</option>
                                        <option value="CIMB Niaga">CIMB Niaga</option>
                                        <option value="BSI">BSI</option>
                                        <option value="OCBC NISP">OCBC NISP</option>
                                        <option value="Permata">Permata</option>
                                        <option value="Jago">Jago</option>
                                        <option value="Seabank">Seabank</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                    
                                    {{-- Input Bank Lainnya (Hidden by default) --}}
                                    <div id="bank-lainnya-container" class="hidden mt-3">
                                        <input type="text" id="input-bank-lainnya" name="nama_bank_lainnya" class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:shadow-inner focus:ring-2 focus:ring-[#043915] transition-all" placeholder="Tulis Nama Bank">
                                    </div>
                                </div>

                                {{-- Kolom 2: Nomor Rekening --}}
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1" for="no-rekening">Nomor Rekening <span class="text-red-500">*</span></label>
                                    <input type="number" id="no-rekening" name="no_rekening" class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:shadow-inner focus:ring-2 focus:ring-[#043915] transition-all" placeholder="Contoh: 1234567890" required>
                                </div>

                                {{-- Kolom 3: Atas Nama (BARU) --}}
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1" for="nama-rek">Atas Nama (A/N) <span class="text-red-500">*</span></label>
                                    {{-- Default value diambil dari nama user agar cepat, tapi bisa diedit --}}
                                    <input type="text" id="nama-rek" name="nama_rek" class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:shadow-inner focus:ring-2 focus:ring-[#043915] transition-all" placeholder="Nama Pemilik Rekening" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KARTU 3: RINCIAN DANA --}}
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                         <div class="p-6 md:p-8 bg-[#043915] text-white">
                            <div class="flex items-center gap-4">
                                <i class="fas fa-list-ul text-3xl opacity-80"></i>
                                <div>
                                    <h2 class="text-xl font-bold">3. Rincian Penggunaan Dana <span class="text-red-500">*</span></h2>
                                    <p class="text-sm text-slate-300">Tambahkan satu atau lebih item pengeluaran.</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 md:p-8">
                            <div class="hidden md:block rounded-lg border border-slate-200 overflow-hidden">
                                <table class="min-w-full text-sm">
                                    {{-- Menggunakan warna #B0CE88 (Sage Green) seperti di file Detail --}}
                                    <thead class="bg-[#B0CE88] text-slate-800 uppercase">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-bold w-2/3">Deskripsi</th>
                                            <th class="px-4 py-3 text-left font-bold w-1/3">Jumlah (Rp)</th>
                                            <th class="px-4 py-3 text-center font-bold w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="rincian-dana-body" class="bg-white divide-y divide-slate-200"></tbody>
                                </table>
                            </div>
                            <div id="rincian-dana-container-mobile" class="block md:hidden space-y-3"></div>
                            
                            {{-- Tombol Tambah Item (Hijau Tua) --}}
                            <button id="tambah-baris-btn" type="button" class="mt-3 bg-[#043915] hover:bg-emerald-900 text-white font-semibold py-2 px-4 rounded-lg text-sm flex items-center gap-2 transition shadow-md">
                                <i class="fas fa-plus"></i> Tambah Item
                            </button>

                            <div class="mt-4 pt-4 border-t-2 border-slate-200 flex items-center justify-end">
                                <span class="text-slate-700 font-bold mr-4">Total:</span>
                                <span class="text-[#043915] font-extrabold text-2xl">Rp <span id="total-dana-display">0</span></span>
                                <input type="hidden" id="jumlah-dana-total" name="jumlah_dana_total">
                            </div>
                        </div>
                    </div>

                    {{-- KARTU 4: FILE PENDUKUNG --}}
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="p-6 md:p-8 bg-[#043915] text-white">
                            <div class="flex items-center gap-4">
                                <i class="fas fa-paperclip text-3xl opacity-80"></i>
                                <div>
                                    <h2 class="text-xl font-bold">4. File Pendukung (Opsional)</h2>
                                    <p class="text-sm text-slate-300">Anda bisa melampirkan lebih dari satu file (nota, invoice, dll).</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 md:p-8">
                            <div id="file-pendukung-container" class="space-y-3">
                                {{-- Input file akan ditambahkan secara dinamis oleh script --}}
                            </div>
                            {{-- Tombol Tambah File (Hijau Muda/Sage Style) --}}
                            <button id="tambah-lampiran-btn" type="button" class="mt-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold py-2 px-4 rounded-lg text-sm flex items-center gap-2 transition">
                                <i class="fas fa-plus"></i> Tambah File Lampiran
                            </button>
                            <p class="text-xs text-slate-500 mt-2">Tipe file: PDF, DOC, JPG, PNG, Excel (Maks. 5MB per file)</p>
                        </div>
                    </div>

                    {{-- TOMBOL AKSI FINAL --}}
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" id="reset-form-btn" class="bg-white hover:bg-slate-50 text-slate-700 font-semibold py-3 px-8 rounded-xl border border-slate-300 hover:border-emerald-500 transition-all">Reset</button>
                        {{-- Tombol Utama diubah ke Hijau Tua #043915 --}}
                        <button type="submit" class="bg-[#043915] hover:bg-emerald-900 text-white font-semibold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                            <i class="fas fa-paper-plane"></i> Ajukan Dana
                        </button>
                    </div>

                </div>
            </form>

            {{-- KARTU STATUS PENGAJUAN --}}
            <div class="mt-12 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-6 md:p-8">
                    <h2 class="text-2xl font-bold text-slate-800">Status Pengajuan Anda</h2>
                    <p class="text-sm text-slate-500 mt-1">Riwayat semua pengajuan dana yang telah Anda buat.</p>
                </div>
                
                <div class="overflow-x-auto">
                    {{-- Tabel Status Desktop --}}
                    <table class="hidden md:table min-w-full border-t border-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                                <th class="px-6 py-3 text-left font-semibold">Judul Pengajuan</th>
                                <th class="px-6 py-3 text-left font-semibold">Total Dana</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                                <th class="px-6 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($pengajuanDanas as $pengajuan)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600">{{ $pengajuan->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $pengajuan->judul_pengajuan }}</td>
                                <td class="px-6 py-4 font-medium font-mono">Rp {{ number_format($pengajuan->total_dana, 0, ',', '.') }}</td>
                                
                                <td class="px-6 py-4">
                                    @if ($pengajuan->status == 'diajukan')
                                        <span class="font-bold bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs shadow-sm">Menunggu Appr 1</span>
                                    @elseif ($pengajuan->status == 'diproses_appr_2')
                                        <span class="font-bold bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs shadow-sm">Menunggu Appr 2</span>
                                    @elseif ($pengajuan->status == 'proses_pembayaran')
                                        <span class="font-bold bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs shadow-sm">Proses Pembayaran</span>
                                    @elseif ($pengajuan->status == 'selesai')
                                        <span class="font-bold bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs shadow-sm">Selesai</span>
                                    @elseif ($pengajuan->status == 'ditolak')
                                        <span class="font-bold bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs shadow-sm">Ditolak</span>
                                    @elseif ($pengajuan->status == 'dibatalkan')
                                        <span class="font-bold bg-slate-100 text-slate-800 px-3 py-1 rounded-full text-xs shadow-sm">Dibatalkan</span>
                                    @else
                                        <span class="font-bold bg-slate-100 text-slate-800 px-3 py-1 rounded-full text-xs shadow-sm">{{ ucfirst($pengajuan->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{-- Link disesuaikan ke Hijau Tua --}}
                                    <a href="{{ route('pengajuan_dana.show', $pengajuan->id) }}" class="text-[#043915] hover:text-emerald-600 font-bold hover:underline transition-colors">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td class="px-6 py-10 text-center text-slate-500" colspan="5">Belum ada pengajuan dana yang dibuat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- List Status Mobile --}}
                    <div class="block md:hidden p-4 space-y-4 border-t border-slate-200">
                        @forelse ($pengajuanDanas as $pengajuan)
                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-200 shadow-sm">
                            <div class="flex justify-between items-start mb-2">
                                <div class="font-bold text-slate-800 text-base pr-4">{{ $pengajuan->judul_pengajuan }}</div>
                                @if ($pengajuan->status == 'diajukan')
                                    <span class="flex-shrink-0 font-bold bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-[10px]">Appr 1</span>
                                @elseif ($pengajuan->status == 'diproses_appr_2')
                                    <span class="flex-shrink-0 font-bold bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-[10px]">Appr 2</span>
                                @elseif ($pengajuan->status == 'proses_pembayaran')
                                    <span class="flex-shrink-0 font-bold bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-[10px]">Bayar</span>
                                @elseif ($pengajuan->status == 'selesai')
                                    <span class="flex-shrink-0 font-bold bg-green-100 text-green-800 px-2 py-1 rounded-full text-[10px]">Selesai</span>
                                @elseif ($pengajuan->status == 'ditolak')
                                    <span class="flex-shrink-0 font-bold bg-red-100 text-red-800 px-2 py-1 rounded-full text-[10px]">Ditolak</span>
                                @else
                                    <span class="flex-shrink-0 font-bold bg-slate-100 text-slate-800 px-2 py-1 rounded-full text-[10px]">{{ $pengajuan->status }}</span>
                                @endif
                            </div>
                            <div class="text-sm text-slate-500 mb-3">{{ $pengajuan->created_at->format('d M Y') }}</div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                                <div class="text-slate-600 text-xs">Total: <span class="font-bold text-slate-800 text-sm">Rp {{ number_format($pengajuan->total_dana, 0, ',', '.') }}</span></div>
                                <a href="{{ route('pengajuan_dana.show', $pengajuan->id) }}" class="text-[#043915] hover:text-emerald-600 text-sm font-bold">Lihat Detail</a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-slate-500 py-8">Belum ada pengajuan dana yang dibuat.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('pilih-bank')?.addEventListener('change', function() {const bankContainer = document.getElementById('bank-lainnya-container'); const otherBankInput = document.getElementById('input-bank-lainnya'); if (this.value === 'other') {bankContainer.classList.remove('hidden'); otherBankInput.setAttribute('required', 'required');} else {bankContainer.classList.add('hidden'); otherBankInput.removeAttribute('required');}});
        
        const tambahBarisBtn = document.getElementById('tambah-baris-btn'); 
        const rincianDanaBodyDesktop = document.getElementById('rincian-dana-body'); 
        const rincianDanaContainerMobile = document.getElementById('rincian-dana-container-mobile'); 
        const totalDanaDisplay = document.getElementById('total-dana-display'); 
        const jumlahDanaTotalInput = document.getElementById('jumlah-dana-total');
        
        function updateTotal() {
            let total = 0; 
            document.querySelectorAll('input[name="rincian_jumlah[]"]').forEach(input => {
                total += parseInt(input.value.replace(/[^0-9]/g, '')) || 0;
            }); 
            const formattedTotal = total.toLocaleString('id-ID'); 
            if (totalDanaDisplay) totalDanaDisplay.textContent = formattedTotal; 
            if (jumlahDanaTotalInput) jumlahDanaTotalInput.value = total;
        }
        
        function formatCurrency(input) {
            let value = input.value.replace(/[^0-9]/g, ''); 
            input.value = value ? parseInt(value).toLocaleString('id-ID') : '';
        }
        
        function addRow() {
            const isMobile = window.innerWidth < 768; 
            const container = isMobile ? rincianDanaContainerMobile : rincianDanaBodyDesktop; 
            const newRow = document.createElement(isMobile ? 'div' : 'tr');
            
            // Ubah border focus jadi hijau saat mengetik
            if (isMobile) {
                newRow.className = 'bg-white rounded-lg p-4 border border-slate-200 space-y-2'; 
                newRow.innerHTML = `
                    <div><input type="text" name="rincian_deskripsi[]" class="w-full p-2 border border-slate-300 rounded-lg text-sm focus:ring-[#043915] focus:outline-none focus:ring-2" placeholder="Deskripsi pengeluaran" required></div>
                    <div><input type="text" name="rincian_jumlah[]" class="w-full p-2 border border-slate-300 rounded-lg text-sm jumlah-input focus:ring-[#043915] focus:outline-none focus:ring-2" placeholder="Jumlah (Rp)" required></div>
                    <button type="button" class="delete-row-btn text-red-500 hover:text-red-700 text-xs font-semibold w-full text-left">HAPUS ITEM</button>`;
            } else {
                newRow.innerHTML = `
                    <td class="px-4 py-2"><input type="text" name="rincian_deskripsi[]" class="w-full p-2 border border-slate-300 rounded-lg text-sm focus:ring-[#043915] focus:outline-none focus:ring-2" placeholder="Masukkan deskripsi" required></td>
                    <td class="px-4 py-2"><div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span><input type="text" name="rincian_jumlah[]" class="w-full p-2 pl-8 border border-slate-300 rounded-lg text-sm jumlah-input focus:ring-[#043915] focus:outline-none focus:ring-2" placeholder="0" required></div></td>
                    <td class="px-4 py-2 text-center"><button type="button" class="delete-row-btn text-slate-400 hover:text-red-600 text-lg transition-colors"><i class="fas fa-trash-alt"></i></button></td>`;
            }
            container.appendChild(newRow); 
            const amountInput = newRow.querySelector('.jumlah-input'); 
            amountInput.addEventListener('input', () => { formatCurrency(amountInput); updateTotal(); }); 
            newRow.querySelector('.delete-row-btn').addEventListener('click', () => { newRow.remove(); updateTotal(); });
        }
        
        if (tambahBarisBtn) { 
            addRow(); 
            tambahBarisBtn.addEventListener('click', addRow); 
        }

        const tambahLampiranBtn = document.getElementById('tambah-lampiran-btn');
        const lampiranContainer = document.getElementById('file-pendukung-container');
        const mainForm = document.querySelector('form[action="{{ route('pengajuan_dana.store') }}"]');
        const submitButton = mainForm.querySelector('button[type="submit"]');

        function addLampiranInput() {
            const uniqueId = 'file_' + Date.now() + Math.random().toString(36).substr(2, 9);
            const newFileWrapper = document.createElement('div');
            newFileWrapper.className = 'bg-slate-50 p-3 rounded-lg border border-slate-200';
            
            // File input styles updated to Green theme
            const fileInputHtml = `
                <div class="flex items-center gap-3">
                    <div class="flex-grow">
                        <input type="file" name="file_pendukung[]" id="${uniqueId}" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                    </div>
                    <button type="button" class="delete-lampiran-btn flex-shrink-0 text-slate-400 hover:text-red-600 text-lg transition-colors">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                <div id="progress-wrapper-${uniqueId}" class="mt-2 hidden">
                    <div class="flex justify-between items-center mb-1">
                        <span id="file-name-${uniqueId}" class="text-xs font-medium text-slate-700 truncate pr-2 w-4/5"></span>
                        <span id="status-text-${uniqueId}" class="text-xs font-medium text-emerald-700 w-1/5 text-right"></span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-1.5">
                        <div id="progress-bar-${uniqueId}" class="h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>`;
            newFileWrapper.innerHTML = fileInputHtml;
            lampiranContainer.appendChild(newFileWrapper);
            
            const fileInput = newFileWrapper.querySelector(`#${uniqueId}`);
            const progressWrapper = newFileWrapper.querySelector(`#progress-wrapper-${uniqueId}`);
            const progressBar = newFileWrapper.querySelector(`#progress-bar-${uniqueId}`);
            const fileNameSpan = newFileWrapper.querySelector(`#file-name-${uniqueId}`);
            const statusTextSpan = newFileWrapper.querySelector(`#status-text-${uniqueId}`);
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileNameSpan.textContent = file.name;
                    statusTextSpan.textContent = 'Ready';
                    statusTextSpan.classList.remove('text-emerald-700');
                    statusTextSpan.classList.add('text-green-700');
                    progressBar.style.width = '100%';
                    progressBar.classList.remove('bg-emerald-600');
                    progressBar.classList.add('bg-green-500'); // Changed to green
                    progressWrapper.classList.remove('hidden');
                } else {
                    progressWrapper.classList.add('hidden');
                }
            });
            
            newFileWrapper.querySelector('.delete-lampiran-btn').addEventListener('click', function() {
                newFileWrapper.remove();
            });
        }
        
        if (tambahLampiranBtn) {
            tambahLampiranBtn.addEventListener('click', addLampiranInput);
            addLampiranInput();
        }
        
        mainForm.addEventListener('submit', function() {
            document.querySelectorAll('input[type="file"][name="file_pendukung[]"]').forEach(input => {
                if (input.files && input.files.length > 0) {
                    const uniqueId = input.id;
                    const statusText = document.getElementById(`status-text-${uniqueId}`);
                    const progressBar = document.getElementById(`progress-bar-${uniqueId}`);
                    if (statusText) {
                        statusText.textContent = 'Uploading...';
                        statusText.classList.remove('text-green-700');
                        statusText.classList.add('text-emerald-700');
                    }
                    if (progressBar) {
                        progressBar.classList.remove('bg-green-500');
                        progressBar.classList.add('bg-[#043915]'); // Dark Green loading bar
                    }
                }
            });
            
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mengirim...`;
            submitButton.classList.add('inline-flex', 'items-center');
        });
        
        const resetBtn = document.getElementById('reset-form-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                mainForm.reset();
                if (rincianDanaBodyDesktop) rincianDanaBodyDesktop.innerHTML = '';
                if (rincianDanaContainerMobile) rincianDanaContainerMobile.innerHTML = '';
                addRow();
                if (lampiranContainer) lampiranContainer.innerHTML = '';
                addLampiranInput();
                updateTotal();
                document.getElementById('pilih-bank').dispatchEvent(new Event('change'));
            });
        }
    });
    </script>
    @endpush
</x-layout-users>