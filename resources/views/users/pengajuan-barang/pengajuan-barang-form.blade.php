<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        html { scroll-behavior: smooth; }
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
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-float-delayed { animation: float 10s ease-in-out infinite; animation-delay: 2s; }
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9); border-radius: 9999px;
            color: #1e293b; font-size: 0.9rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px; width: fit-content;
        }
        .btn-back-modern:hover { background: rgba(255,255,255,0.95); box-shadow: 0 10px 15px -3px rgba(59,130,246,0.15); transform: translateY(-2px); color: #1d4ed8; }
        .btn-back-modern .icon-circle { width: 32px; height: 32px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #3b82f6; font-size: 0.85rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: transform 0.3s ease; }
        .btn-back-modern:hover .icon-circle { transform: translateX(-3px); background: #EFF6FF; }
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,1); border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); padding: 28px; }
        .modern-label { display: block; font-size: 0.8rem; font-weight: 800; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        .modern-input { width: 100%; background: rgba(255,255,255,0.95); border: 2px solid #e2e8f0; border-radius: 14px; padding: 11px 15px; font-size: 0.9rem; color: #1e293b; font-weight: 600; outline: none; transition: all 0.2s ease; }
        .modern-input:focus { border-color: #3b82f6; background: #fff; box-shadow: 0 0 0 4px rgba(59,130,246,0.15); }
        .modern-input-readonly { width: 100%; background: rgba(241,245,249,0.6); border: 2px solid #e2e8f0; border-radius: 14px; padding: 11px 15px; font-size: 0.9rem; color: #64748b; font-weight: 700; outline: none; cursor: not-allowed; }
        .modern-select { width: 100%; background: rgba(255,255,255,0.95); border: 2px solid #e2e8f0; border-radius: 14px; padding: 11px 15px; font-size: 0.9rem; color: #1e293b; font-weight: 600; outline: none; transition: all 0.2s ease; cursor: pointer; }
        .modern-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59,130,246,0.15); }
        .form-section-header { display: flex; align-items: center; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid rgba(226,232,240,0.8); margin-bottom: 24px; }
        .btn-action-primary { padding: 12px 28px; background: linear-gradient(135deg, #1d4ed8, #3b82f6); color: #fff; font-size: 0.9rem; font-weight: 800; border: none; border-radius: 14px; cursor: pointer; box-shadow: 0 6px 20px rgba(37,99,235,0.25); transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-action-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37,99,235,0.35); }
        .btn-action-primary:disabled { background: #cbd5e1; color: #64748b; box-shadow: none; cursor: not-allowed; }
        .btn-action-secondary { padding: 12px 28px; background: #ffffff; color: #475569; font-size: 0.9rem; font-weight: 800; border: 2px solid #e2e8f0; border-radius: 14px; cursor: pointer; transition: all 0.2s; }
        .btn-action-secondary:hover { border-color: #cbd5e1; background: #f8fafc; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[10%] left-[5%] w-32 h-32 bg-white/40 backdrop-blur-md border border-white/50 rounded-full animate-float"></div>
            <div class="absolute bottom-[15%] right-[10%] w-48 h-48 bg-white/30 backdrop-blur-md border border-white/40 rounded-full animate-float-delayed"></div>
            <div class="absolute inset-0" style="background-image: radial-gradient(rgba(100, 116, 139, 0.1) 1px, transparent 1px); background-size: 24px 24px;"></div>
        </div>

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 flex-1 flex flex-col">

            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl text-sm flex items-center gap-2 font-bold" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm flex items-center gap-2 font-bold" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm" role="alert">
                    <p class="font-bold mb-1 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs font-semibold">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('dashboard') }}" class="btn-back-modern">
                <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                Kembali ke Dashboard
            </a>

            {{-- HEADER --}}
            <div class="relative z-10 w-full bg-gradient-to-r from-blue-700 to-indigo-600 rounded-3xl p-6 md:p-8 shadow-xl mb-6 overflow-hidden border border-white/20">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute right-20 -bottom-10 w-24 h-24 bg-white/10 rounded-full blur-lg pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-5">
                        <div class="h-14 w-14 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20 flex-shrink-0">
                            <i class="fas fa-box text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-xl md:text-2xl font-black tracking-tight text-white uppercase">Form Pengajuan Barang</h1>
                            <p class="text-blue-100 text-xs md:text-sm mt-1 font-medium leading-relaxed max-w-xl">
                                Ajukan permohonan barang atau perlengkapan operasional, tambahkan rincian item, lampirkan dokumen pendukung, dan pantau status persetujuan.
                            </p>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider bg-white/90 border border-white text-blue-700 shadow-sm">
                            <i class="fas fa-check-circle text-green-500"></i>
                            {{ $totalPengajuan }} Pengajuan
                        </span>
                    </div>
                </div>
            </div>

            <div class="space-y-6 pb-10">
                <form action="{{ route('pengajuan_barang.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 m-0">
                    @csrf

                    {{-- 1. INFORMASI PEMOHON --}}
                    <div class="glass-card">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/60" style="padding-bottom: 16px; margin-bottom: 28px;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-user-tie"></i></div>
                                <div>
                                    <h3 class="text-lg font-black text-slate-800">1. Informasi Pemohon</h3>
                                    <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Detail data karyawan yang mengajukan barang</p>
                                </div>
                            </div>
                            <a href="{{ route('pengajuan_barang.history') }}" class="text-xs text-blue-600 font-bold hover:underline flex items-center gap-1.5 bg-blue-50 px-4 py-2 rounded-full border border-blue-100 transition-all hover:bg-blue-100 shadow-sm w-fit">
                                <i class="fas fa-history"></i> Lihat Riwayat Pengajuan
                            </a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="modern-label">Nama Pemohon</label>
                                <input type="text" class="modern-input-readonly" value="{{ Auth::user()->name }}" readonly>
                                <input type="hidden" name="nama_pemohon" value="{{ Auth::user()->name }}">
                            </div>
                            <div>
                                <label class="modern-label">Divisi</label>
                                <input type="text" class="modern-input-readonly" value="{{ Auth::user()->divisi }}" readonly>
                                <input type="hidden" name="divisi" value="{{ Auth::user()->divisi }}">
                            </div>
                            <div>
                                <label class="modern-label">Jabatan Pemohon</label>
                                <input type="text" class="modern-input-readonly" value="{{ Auth::user()->jabatan ?? '-' }}" readonly>
                            </div>
                            <div>
                                <label class="modern-label">Email Pemohon</label>
                                <input type="text" class="modern-input-readonly" value="{{ Auth::user()->email }}" readonly>
                            </div>
                            <div class="md:col-span-2">
                                <label class="modern-label" for="judul-pengajuan">Judul Pengajuan <span class="text-red-500">*</span></label>
                                <input type="text" id="judul-pengajuan" name="judul_pengajuan" class="modern-input" placeholder="Contoh: Pengadaan Perlengkapan Kantor & ATK" required>
                            </div>
                            <div>
                                <label class="modern-label">Tanggal Pengajuan</label>
                                <input type="text" class="modern-input-readonly" value="{{ date('d F Y') }}" readonly>
                                <input type="hidden" name="tanggal_pengajuan" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    {{-- 2. RINCIAN BARANG --}}
                    <div class="glass-card">
                        <div class="form-section-header">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-list-ol"></i></div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800">2. Rincian Barang yang Diajukan</h4>
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Tambahkan satu atau lebih item barang beserta satuan dan jumlahnya</p>
                            </div>
                        </div>
                        <div class="hidden md:block rounded-2xl border border-slate-200 mb-3" style="border-radius: 16px; overflow: hidden;">
                            <table class="min-w-full text-xs">
                                <thead class="bg-slate-50 text-slate-600 uppercase font-black border-b border-slate-200">
                                    <tr>
                                        <th class="px-5 py-3 text-left w-5/12">Deskripsi Barang</th>
                                        <th class="px-5 py-3 text-left w-3/12">Satuan</th>
                                        <th class="px-5 py-3 text-left w-3/12">Jumlah</th>
                                        <th class="px-5 py-3 text-center w-1/12"></th>
                                    </tr>
                                </thead>
                                <tbody id="rincian-barang-body" class="bg-white divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                        <div id="rincian-barang-container-mobile" class="block md:hidden space-y-3 mb-3"></div>
                        <button id="tambah-baris-btn" type="button" class="bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 font-black py-2 px-4 rounded-xl text-xs flex items-center gap-1.5 transition">
                            <i class="fas fa-plus"></i> Tambah Item Barang
                        </button>
                    </div>

                    {{-- 3. FILE PENDUKUNG --}}
                    <div class="glass-card">
                        <div class="form-section-header">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl"><i class="fas fa-paperclip"></i></div>
                            <div>
                                <h4 class="text-lg font-black text-slate-800">3. File Pendukung (Opsional)</h4>
                                <p class="text-xs text-slate-500 font-semibold" style="margin-top: 8px; margin-bottom: 8px;">Lampirkan nota, spesifikasi barang, atau dokumen pendukung lainnya</p>
                            </div>
                        </div>
                        <div class="bg-blue-50/50 border border-blue-100 p-3.5 rounded-2xl mb-4 flex gap-3">
                            <i class="fas fa-info-circle text-blue-600 text-sm mt-0.5"></i>
                            <div class="text-[11px] text-blue-800 leading-normal font-semibold">
                                Format file yang didukung: <span class="text-blue-900 font-black">JPG, JPEG, PNG, PDF, DOC, DOCX</span>. Ukuran berkas maksimal <span class="text-blue-900 font-black">5MB</span> per file.
                            </div>
                        </div>
                        <div id="file-pendukung-container" class="space-y-3"></div>
                        <button id="tambah-lampiran-btn" type="button" class="mt-3 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 font-black py-2 px-4 rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm">
                            <i class="fas fa-plus"></i> Tambah File Lampiran
                        </button>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" id="reset-form-btn" class="btn-action-secondary">Reset Formulir</button>
                        <button type="submit" id="submit-button" class="btn-action-primary">
                            <i class="fas fa-paper-plane"></i> Ajukan Barang Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tambahBarisBtn = document.getElementById('tambah-baris-btn');
        const rincianBarangBodyDesktop = document.getElementById('rincian-barang-body');
        const rincianBarangContainerMobile = document.getElementById('rincian-barang-container-mobile');

        const unitOptions = `<option value="Pcs">Pcs</option><option value="Box">Box</option><option value="Pack">Pack</option><option value="Unit">Unit</option><option value="Set">Set</option><option value="Lusin">Lusin</option><option value="Rim">Rim</option><option value="Buah">Buah</option><option value="Roll">Roll</option><option value="Lainnya">Lainnya</option>`;

        function addRow() {
            const isMobile = window.innerWidth < 768;
            const container = isMobile ? rincianBarangContainerMobile : rincianBarangBodyDesktop;
            const newRow = document.createElement(isMobile ? 'div' : 'tr');
            if (isMobile) {
                newRow.className = 'bg-white rounded-xl p-3 border border-slate-200 space-y-2';
                newRow.innerHTML = `<div><label class="block text-xs font-bold text-slate-500 mb-1 uppercase">Deskripsi</label><input type="text" name="rincian_deskripsi[]" class="modern-input" placeholder="Deskripsi barang" required></div><div class="grid grid-cols-2 gap-3"><div><label class="block text-xs font-bold text-slate-500 mb-1 uppercase">Satuan</label><select name="rincian_satuan[]" class="modern-select">${unitOptions}</select></div><div><label class="block text-xs font-bold text-slate-500 mb-1 uppercase">Jumlah</label><input type="number" name="rincian_jumlah[]" class="modern-input" placeholder="0" min="1" required></div></div><button type="button" class="delete-row-btn text-red-500 hover:text-red-700 text-[10px] font-bold uppercase tracking-wider block mt-1 border-t border-slate-100 pt-2 w-full text-right"><i class="fas fa-trash-alt mr-1"></i> Hapus Item</button>`;
            } else {
                newRow.innerHTML = `<td class="px-4 py-2"><input type="text" name="rincian_deskripsi[]" class="modern-input !py-2 !px-3.5 !rounded-xl !text-xs" placeholder="Masukkan deskripsi barang" required></td><td class="px-4 py-2"><select name="rincian_satuan[]" class="modern-select !py-2 !px-3.5 !rounded-xl !text-xs">${unitOptions}</select></td><td class="px-4 py-2"><input type="number" name="rincian_jumlah[]" class="modern-input !py-2 !px-3.5 !rounded-xl !text-xs" placeholder="0" min="1" required></td><td class="px-4 py-2 text-center"><button type="button" class="delete-row-btn text-slate-400 hover:text-red-600 hover:bg-red-50 p-2.5 rounded-xl text-sm transition-all"><i class="fas fa-trash-alt"></i></button></td>`;
            }
            container.appendChild(newRow);
            newRow.querySelector('.delete-row-btn').addEventListener('click', () => { newRow.remove(); });
        }

        if (tambahBarisBtn) { addRow(); tambahBarisBtn.addEventListener('click', addRow); }

        const tambahLampiranBtn = document.getElementById('tambah-lampiran-btn');
        const lampiranContainer = document.getElementById('file-pendukung-container');
        const mainForm = document.querySelector('form');
        const submitButton = document.getElementById('submit-button');

        function addLampiranInput() {
            const uniqueId = 'file_' + Date.now() + Math.random().toString(36).substr(2, 9);
            const newFileWrapper = document.createElement('div');
            newFileWrapper.className = 'bg-white p-3 rounded-2xl border border-slate-200 hover:border-blue-400 transition-all shadow-sm flex flex-col gap-2';
            newFileWrapper.innerHTML = `<div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 text-sm"><i class="fas fa-paperclip"></i></div><div class="flex-grow min-w-0"><input type="file" name="file_pendukung[]" id="${uniqueId}" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer cursor-pointer font-semibold" /></div><button type="button" class="delete-lampiran-btn flex-shrink-0 text-slate-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-xl text-base transition-all"><i class="fas fa-trash-alt text-sm"></i></button></div><div id="progress-wrapper-${uniqueId}" class="mt-1 hidden pl-11"><div class="flex justify-between items-center mb-1"><span id="file-name-${uniqueId}" class="text-[10px] font-bold text-slate-700 truncate pr-2 w-4/5"></span><span id="status-text-${uniqueId}" class="text-[10px] font-bold text-blue-700 w-1/5 text-right"></span></div><div class="w-full bg-slate-200 rounded-full h-1"><div id="progress-bar-${uniqueId}" class="h-1 rounded-full transition-all duration-300" style="width: 0%"></div></div></div>`;
            lampiranContainer.appendChild(newFileWrapper);
            const fileInput = newFileWrapper.querySelector(`#${uniqueId}`);
            const progressWrapper = newFileWrapper.querySelector(`#progress-wrapper-${uniqueId}`);
            const progressBar = newFileWrapper.querySelector(`#progress-bar-${uniqueId}`);
            const fileNameSpan = newFileWrapper.querySelector(`#file-name-${uniqueId}`);
            const statusTextSpan = newFileWrapper.querySelector(`#status-text-${uniqueId}`);
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileNameSpan.textContent = this.files[0].name;
                    statusTextSpan.textContent = 'Ready';
                    statusTextSpan.className = 'text-[10px] font-bold text-green-700 w-1/5 text-right';
                    progressBar.style.width = '100%';
                    progressBar.className = 'h-1 rounded-full transition-all duration-300 bg-green-500';
                    progressWrapper.classList.remove('hidden');
                } else { progressWrapper.classList.add('hidden'); }
            });
            newFileWrapper.querySelector('.delete-lampiran-btn').addEventListener('click', function() { newFileWrapper.remove(); });
        }

        if (tambahLampiranBtn) { tambahLampiranBtn.addEventListener('click', addLampiranInput); addLampiranInput(); }

        if (mainForm) {
            mainForm.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mengirim...`;
                submitButton.classList.add('inline-flex', 'items-center');
            });
        }

        const resetBtn = document.getElementById('reset-form-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                mainForm.reset();
                if (rincianBarangBodyDesktop) rincianBarangBodyDesktop.innerHTML = '';
                if (rincianBarangContainerMobile) rincianBarangContainerMobile.innerHTML = '';
                addRow();
                if (lampiranContainer) lampiranContainer.innerHTML = '';
                addLampiranInput();
            });
        }
    });
    </script>
    @endpush
</x-layout-users>
