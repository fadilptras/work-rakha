<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="bg-gradient-to-br from-sky-50 to-blue-100 min-h-screen">
        <div class="container mx-auto p-0 md:p-0">

            {{-- 1. NOTIFIKASI / ALERT (DITAMBAHKAN) --}}
            @if (session('success'))
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-800 p-4 rounded-lg shadow-md mb-6" role="alert">
                    <p class="font-bold">Sukses!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-lg shadow-md mb-6" role="alert">
                    <p class="font-bold">Error!</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 rounded-lg shadow-md mb-6" role="alert">
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
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-[#001A6E] rounded-xl shadow-lg hover:bg-[#0B1D51] hover:shadow-xl transition-all duration-300 w-full sm:w-auto">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Dashboard
                </a>
            </div>

            <form action="{{ route('pengajuan_barang.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-8">

                    {{-- KARTU 1: DETAIL PENGAJUAN --}}
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="p-6 md:p-8 bg-[#001A6E] text-white">
                            <div class="flex items-center gap-4">
                                <i class="fas fa-box text-3xl opacity-80"></i>
                                <div>
                                    <h2 class="text-xl font-bold">1. Detail Pengajuan Barang</h2>
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
                                    <input type="text" id="judul-pengajuan" name="judul_pengajuan" class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#001A6E] focus:shadow-inner transition-all" placeholder="Contoh: Penggunaan Kasa Roll 3 Box" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pengajuan</label>
                                    <input type="text" class="w-full p-3 bg-slate-100 border-transparent rounded-lg" value="{{ date('d F Y') }}" readonly>
                                    <input type="hidden" name="tanggal_pengajuan" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KARTU 2: RINCIAN BARANG --}}
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                         <div class="p-6 md:p-8 bg-[#001A6E] text-white">
                            <div class="flex items-center gap-4">
                                <i class="fas fa-list-ul text-3xl opacity-80"></i>
                                <div>
                                    <h2 class="text-xl font-bold">2. Rincian Barang <span class="text-red-500">*</span></h2>
                                    <p class="text-sm text-slate-300">Tambahkan satu atau lebih item barang yang diajukan.</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 md:p-8">
                            <div class="hidden md:block rounded-lg border border-slate-200 overflow-hidden">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-blue-50 text-[#001A6E] uppercase font-bold">
                                        <tr>
                                            <th class="px-4 py-3 text-left w-5/12">Deskripsi Barang</th>
                                            <th class="px-4 py-3 text-left w-3/12">Satuan</th>
                                            <th class="px-4 py-3 text-left w-3/12">Jumlah</th>
                                            <th class="px-4 py-3 text-center w-1/12"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="rincian-barang-body" class="bg-white divide-y divide-slate-200"></tbody>
                                </table>
                            </div>
                            <div id="rincian-barang-container-mobile" class="block md:hidden space-y-3"></div>
                            
                            <button id="tambah-baris-btn" type="button" class="mt-3 bg-[#001A6E] hover:bg-[#0B1D51] text-white font-semibold py-2 px-4 rounded-lg text-sm flex items-center gap-2 transition shadow-md">
                                <i class="fas fa-plus"></i> Tambah Item
                            </button>
                        </div>
                    </div>

                    {{-- KARTU 3: FILE PENDUKUNG (UPDATED SCRIPT DI BAWAH) --}}
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                        <div class="p-6 md:p-8 bg-[#001A6E] text-white">
                            <div class="flex items-center gap-4">
                                <i class="fas fa-paperclip text-3xl opacity-80"></i>
                                <div>
                                    <h2 class="text-xl font-bold">3. File Pendukung (Opsional)</h2>
                                    <p class="text-sm text-slate-300">Anda bisa melampirkan lebih dari satu file (nota, spesifikasi, dll).</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 md:p-8">
                            <div id="file-pendukung-container" class="space-y-3">
                                {{-- Input file akan ditambahkan secara dinamis oleh script --}}
                            </div>
                            
                            <button id="tambah-lampiran-btn" type="button" class="mt-3 bg-blue-50 hover:bg-blue-100 text-[#001A6E] border border-blue-100 font-semibold py-2 px-4 rounded-lg text-sm flex items-center gap-2 transition">
                                <i class="fas fa-plus"></i> Tambah File Lampiran
                            </button>
                            <p class="text-xs text-slate-500 mt-2">Tipe file: PDF, DOC, JPG, PNG (Maks. 5MB per file)</p>
                        </div>
                    </div>

                    {{-- TOMBOL AKSI FINAL --}}
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" id="reset-form-btn" class="bg-white hover:bg-slate-50 text-slate-700 font-semibold py-3 px-8 rounded-lg border border-slate-300 hover:border-[#001A6E] transition-all">Reset</button>
                        <button type="submit" class="bg-[#001A6E] hover:bg-[#0B1D51] text-white font-semibold py-3 px-8 rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center gap-2">
                            <i class="fas fa-paper-plane"></i> Ajukan Barang
                        </button>
                    </div>

                </div>
            </form>

            {{-- KARTU STATUS PENGAJUAN --}}
            <div class="mt-12 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-6 md:p-8">
                    <h2 class="text-2xl font-bold text-slate-800">Status Pengajuan Barang Anda</h2>
                    <p class="text-sm text-slate-500 mt-1">Riwayat semua pengajuan barang yang telah Anda buat.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="hidden md:table min-w-full border-t border-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                                <th class="px-6 py-3 text-left font-semibold">Judul Pengajuan</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                                <th class="px-6 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($pengajuanBarangs as $pengajuan)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600">{{ $pengajuan->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $pengajuan->judul_pengajuan }}</td>
                                <td class="px-6 py-4">
                                    <span class="font-bold px-3 py-1 rounded-full text-xs capitalize shadow-sm
                                        @if($pengajuan->status == 'diajukan') bg-yellow-100 text-yellow-800
                                        @elseif($pengajuan->status == 'diproses') bg-blue-100 text-blue-800
                                        @elseif($pengajuan->status == 'selesai') bg-green-100 text-green-800
                                        @elseif($pengajuan->status == 'ditolak') bg-red-100 text-red-800
                                        @elseif($pengajuan->status == 'dibatalkan') bg-slate-200 text-slate-800
                                        @endif">
                                        {{ $pengajuan->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('pengajuan_barang.show', $pengajuan->id) }}" class="text-[#001A6E] hover:text-[#0B1D51] hover:underline font-bold transition-colors">Lihat Detail</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td class="px-6 py-10 text-center text-slate-500" colspan="4">Belum ada pengajuan barang yang dibuat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="block md:hidden p-4 space-y-4 border-t border-slate-200">
                        @forelse ($pengajuanBarangs as $pengajuan)
                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-200 shadow-sm">
                            <div class="flex justify-between items-start mb-2">
                                <div class="font-bold text-slate-800 text-base pr-4">{{ $pengajuan->judul_pengajuan }}</div>
                                <span class="flex-shrink-0 font-bold px-2 py-1 rounded-full text-xs capitalize
                                    @if($pengajuan->status == 'diajukan') bg-yellow-100 text-yellow-800
                                    @elseif($pengajuan->status == 'diproses') bg-blue-100 text-blue-800
                                    @elseif($pengajuan->status == 'selesai') bg-green-100 text-green-800
                                    @elseif($pengajuan->status == 'ditolak') bg-red-100 text-red-800
                                    @elseif($pengajuan->status == 'dibatalkan') bg-slate-200 text-slate-800
                                    @endif">
                                    {{ $pengajuan->status }}
                                </span>
                            </div>
                            <div class="text-sm text-slate-500 mb-3">{{ $pengajuan->created_at->format('d M Y') }}</div>
                            <div class="flex justify-between items-center border-t border-slate-200 pt-2">
                                <div class="text-slate-600 text-xs">Total Item: <span class="font-bold text-slate-800">{{ count($pengajuan->rincian_barang) }}</span></div>
                                <a href="{{ route('pengajuan_barang.show', $pengajuan->id) }}" class="text-[#001A6E] hover:underline text-sm font-bold">Lihat Detail</a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-slate-500 py-8">Belum ada pengajuan barang yang dibuat.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tambahBarisBtn = document.getElementById('tambah-baris-btn');
        const rincianBarangBodyDesktop = document.getElementById('rincian-barang-body');
        const rincianBarangContainerMobile = document.getElementById('rincian-barang-container-mobile');

        // Pilihan Satuan
        const unitOptions = `
            <option value="Pcs">Pcs</option>
            <option value="Box">Box</option>
            <option value="Pack">Pack</option>
            <option value="Unit">Unit</option>
            <option value="Set">Set</option>
            <option value="Lusin">Lusin</option>
            <option value="Rim">Rim</option>
            <option value="Buah">Buah</option>
            <option value="Roll">Roll</option>
            <option value="Lainnya">Lainnya</option>
        `;

        function addRow() {
            const isMobile = window.innerWidth < 768;
            const container = isMobile ? rincianBarangContainerMobile : rincianBarangBodyDesktop;
            const newRow = document.createElement(isMobile ? 'div' : 'tr');
            
            if (isMobile) {
                newRow.className = 'bg-white rounded-lg p-4 border border-slate-200 space-y-3';
                newRow.innerHTML = `
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Deskripsi</label>
                        <input type="text" name="rincian_deskripsi[]" class="w-full p-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#001A6E]" placeholder="Deskripsi barang" required>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Satuan</label>
                            <select name="rincian_satuan[]" class="w-full p-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#001A6E] bg-white">
                                ${unitOptions}
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Jumlah</label>
                            <input type="number" name="rincian_jumlah[]" class="w-full p-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#001A6E]" placeholder="0" min="1" required>
                        </div>
                    </div>
                    <button type="button" class="delete-row-btn text-red-500 hover:text-red-700 text-xs font-bold w-full text-right mt-1 border-t border-slate-100 pt-2">
                        <i class="fas fa-trash-alt mr-1"></i> HAPUS ITEM
                    </button>`;
            } else {
                newRow.innerHTML = `
                    <td class="px-4 py-2">
                        <input type="text" name="rincian_deskripsi[]" class="w-full p-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#001A6E]" placeholder="Masukkan deskripsi barang" required>
                    </td>
                    <td class="px-4 py-2">
                        <select name="rincian_satuan[]" class="w-full p-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#001A6E] bg-white">
                            ${unitOptions}
                        </select>
                    </td>
                    <td class="px-4 py-2">
                        <input type="number" name="rincian_jumlah[]" class="w-full p-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#001A6E]" placeholder="0" min="1" required>
                    </td>
                    <td class="px-4 py-2 text-center">
                        <button type="button" class="delete-row-btn text-slate-400 hover:text-red-600 text-lg transition-colors">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>`;
            }
            container.appendChild(newRow);
            newRow.querySelector('.delete-row-btn').addEventListener('click', () => { newRow.remove(); });
        }

        if (tambahBarisBtn) {
            addRow();
            tambahBarisBtn.addEventListener('click', addRow);
        }

        // --- UPDATE LOGIC UPLOAD DOKUMEN & SUBMIT (SEPERTI PENGAJUAN DANA) ---
        const tambahLampiranBtn = document.getElementById('tambah-lampiran-btn');
        const lampiranContainer = document.getElementById('file-pendukung-container');
        const mainForm = document.querySelector('form[action="{{ route('pengajuan_barang.store') }}"]');
        const submitButton = mainForm.querySelector('button[type="submit"]');

        function addLampiranInput() {
            const uniqueId = 'file_' + Date.now() + Math.random().toString(36).substr(2, 9);
            const newFileWrapper = document.createElement('div');
            newFileWrapper.className = 'bg-slate-50 p-3 rounded-lg border border-slate-200';
            
            // Menggunakan warna Biru (#001A6E) sesuai tema Barang
            const fileInputHtml = `
                <div class="flex items-center gap-3">
                    <div class="flex-grow">
                        <input type="file" name="file_pendukung[]" id="${uniqueId}" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-800 hover:file:bg-blue-100 cursor-pointer" />
                    </div>
                    <button type="button" class="delete-lampiran-btn flex-shrink-0 text-slate-400 hover:text-red-600 text-lg transition-colors">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                <div id="progress-wrapper-${uniqueId}" class="mt-2 hidden">
                    <div class="flex justify-between items-center mb-1">
                        <span id="file-name-${uniqueId}" class="text-xs font-medium text-slate-700 truncate pr-2 w-4/5"></span>
                        <span id="status-text-${uniqueId}" class="text-xs font-medium text-blue-700 w-1/5 text-right"></span>
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
                    statusTextSpan.textContent = 'Siap';
                    statusTextSpan.classList.remove('text-blue-700');
                    statusTextSpan.classList.add('text-green-600');
                    progressBar.style.width = '100%';
                    progressBar.classList.remove('bg-[#001A6E]');
                    progressBar.classList.add('bg-green-500'); 
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

        // Logic Submit Button Loading & Fake Progress Upload
        mainForm.addEventListener('submit', function() {
            document.querySelectorAll('input[type="file"][name="file_pendukung[]"]').forEach(input => {
                if (input.files && input.files.length > 0) {
                    const uniqueId = input.id;
                    const statusText = document.getElementById(`status-text-${uniqueId}`);
                    const progressBar = document.getElementById(`progress-bar-${uniqueId}`);
                    if (statusText) {
                        statusText.textContent = 'Mengupload...';
                        statusText.classList.remove('text-green-600');
                        statusText.classList.add('text-blue-700');
                    }
                    if (progressBar) {
                        progressBar.classList.remove('bg-green-500');
                        progressBar.classList.add('bg-[#001A6E]'); // Biru Loading
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
            submitButton.classList.add('inline-flex', 'items-center', 'cursor-not-allowed', 'opacity-75');
        });

        const resetBtn = document.getElementById('reset-form-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                mainForm.reset();
                if (rincianBarangBodyDesktop) rincianBarangBodyDesktop.innerHTML = '';
                if (rincianBarangContainerMobile) rincianBarangContainerMobile.innerHTML = '';
                addRow(); // Kembalikan 1 baris kosong
                if (lampiranContainer) lampiranContainer.innerHTML = '';
                addLampiranInput(); // Kembalikan 1 input file kosong
            });
        }
    });
    </script>
    @endpush
</x-layout-users>