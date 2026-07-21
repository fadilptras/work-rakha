<x-layout-admin>
    <x-slot:title>Edit Karyawan: {{ $user->name }}</x-slot:title>

    {{-- Import CDN Cropper.js --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    

    {{-- Form Edit Data Utama --}}
    <form id="form-edit-karyawan" action="{{ route('admin.employees.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        {{-- Header dengan Tombol Simpan di Atas --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.employees.index') }}" class="text-zinc-400 hover:text-white transition">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-2xl font-bold text-white">Edit Biodata Karyawan</h1>
            </div>
            
            <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white font-bold px-6 py-2.5 rounded-lg shadow-lg flex items-center transition-transform hover:scale-105">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </div>

        {{-- Input hidden penampung base64 hasil crop foto --}}
        <input type="hidden" name="cropped_image" id="cropped_image">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Bagian Kiri: Manajemen Foto & Utama --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-zinc-800 p-6 rounded-xl border border-zinc-700 shadow-lg flex flex-col items-center">
                    <h2 class="text-base font-bold text-white mb-4 self-start border-b border-zinc-700 w-full pb-2">Foto Profil</h2>
                    
                    <div class="relative group cursor-pointer mb-3" onclick="document.getElementById('image_upload').click()">
                        @if($user->profile_picture)
                            <img id="profile-preview" src="{{ asset('storage/' . $user->profile_picture) }}" class="w-40 h-40 rounded-full object-cover border-4 border-sky-500 shadow-xl">
                        @else
                            <div id="profile-preview-fallback" class="w-40 h-40 rounded-full bg-zinc-700 flex items-center justify-center border-4 border-zinc-600 text-5xl font-bold text-white shadow-xl">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <img id="profile-preview" class="w-40 h-40 rounded-full object-cover border-4 border-sky-500 shadow-xl hidden">
                        @endif
                        <div class="absolute inset-0 bg-black/60 rounded-full flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-camera text-white text-xl mb-1"></i>
                            <span class="text-white text-xs font-semibold">Ganti Gambar</span>
                        </div>
                    </div>
                    
                    <input type="file" id="image_upload" accept="image/png, image/jpeg, image/jpg" class="hidden">
                    <p class="text-[11px] text-zinc-400 text-center leading-relaxed">Klik area lingkaran untuk upload.<br>Rasio simetris otomatis 1:1 diterapkan.</p>
                </div>

                <div class="bg-zinc-800 p-6 rounded-xl border border-zinc-700 shadow-lg">
                    <h2 class="text-base font-bold text-white mb-4 border-b border-zinc-700 pb-2">Status Penugasan</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">NIP Perusahaan</label>
                            <input type="text" name="nip" value="{{ old('nip', $user->nip) }}" placeholder="Misal: NIP-001" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Divisi Kerja <span class="text-red-500">*</span></label>
                            <input type="text" name="divisi" value="{{ old('divisi', $user->divisi) }}" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Jabatan Fungsional</label>
                            <input type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Tanggal Bergabung</label>
                            <input type="date" name="tanggal_bergabung" value="{{ old('tanggal_bergabung', $user->tanggal_bergabung ? \Carbon\Carbon::parse($user->tanggal_bergabung)->format('Y-m-d') : '') }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Jatah Cuti <span class="text-red-500">*</span></label>
                                <input type="number" name="jatah_cuti" value="{{ old('jatah_cuti', $user->jatah_cuti) }}" required min="0" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Sisa Cuti</label>
                                <input type="number" name="sisa_cuti" value="{{ old('sisa_cuti', $user->sisa_cuti) }}" min="0" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Status Keaktifan</label>
                            <input type="text" name="status_karyawan" value="{{ old('status_karyawan', $user->status_karyawan) }}" placeholder="Misal: Kontrak, Tetap, Magang" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Kanan: Input Form Seluruh Kolom Database --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Card Informasi Inti --}}
                <div class="bg-zinc-800 p-6 rounded-xl border border-zinc-700 shadow-lg">
                    <h2 class="text-base font-bold text-sky-400 mb-4 border-b border-zinc-700 pb-2"><i class="fas fa-id-card mr-2"></i> Identitas & Kontak Utama</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Nama Lengkap Karyawan <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Alamat Email<span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Nomor Induk Kependudukan (NIK)</label>
                            <input type="text" name="nik" value="{{ old('nik', $user->nik) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $user->tempat_lahir) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : '') }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                                <option value="" disabled {{ is_null($user->jenis_kelamin) ? 'selected' : '' }}>Pilih Gender</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Agama</label>
                            <input type="text" name="agama" value="{{ old('agama', $user->agama) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Golongan Darah</label>
                            <select name="golongan_darah" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                                @foreach(['A', 'B', 'AB', 'O', 'Tidak Tahu'] as $goldar)
                                    <option value="{{ $goldar }}" {{ old('golongan_darah', $user->golongan_darah) == $goldar ? 'selected' : '' }}>{{ $goldar }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Status Pernikahan</label>
                            <select name="status_pernikahan" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                                @foreach(['Belum Menikah', 'Menikah', 'Cerai'] as $stt)
                                    <option value="{{ $stt }}" {{ old('status_pernikahan', $user->status_pernikahan) == $stt ? 'selected' : '' }}>{{ $stt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Alamat Sesuai KTP</label>
                                <textarea name="alamat_ktp" rows="2" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none" placeholder="Alamat KTP...">{{ old('alamat_ktp', $user->alamat_ktp) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Alamat Domisili Saat Ini</label>
                                <textarea name="alamat_domisili" rows="2" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none" placeholder="Alamat Domisili...">{{ old('alamat_domisili', $user->alamat_domisili) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Keuangan & Legalitas Perusahaan --}}
                <div class="bg-zinc-800 p-6 rounded-xl border border-zinc-700 shadow-lg">
                    <h2 class="text-base font-bold text-yellow-500 mb-4 border-b border-zinc-700 pb-2"><i class="fas fa-wallet mr-2"></i> Rekening Bank & Pajak / BPJS</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Nama Bank</label>
                            <input type="text" name="nama_bank" value="{{ old('nama_bank', $user->nama_bank) }}" placeholder="Contoh: BCA" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Nomor Rekening</label>
                            <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $user->nomor_rekening) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Nama Pemilik Rekening</label>
                            <input type="text" name="pemilik_rekening" value="{{ old('pemilik_rekening', $user->pemilik_rekening) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Nomor NPWP</label>
                            <input type="text" name="npwp" value="{{ old('npwp', $user->npwp) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">No. BPJS Kesehatan</label>
                            <input type="text" name="bpjs_kesehatan" value="{{ old('bpjs_kesehatan', $user->bpjs_kesehatan) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">No. BPJS Ketenagakerjaan</label>
                            <input type="text" name="bpjs_ketenagakerjaan" value="{{ old('bpjs_ketenagakerjaan', $user->bpjs_ketenagakerjaan) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Card Kontak Darurat --}}
                <div class="bg-zinc-800 p-6 rounded-xl border border-zinc-700 shadow-lg mb-6">
                    <h2 class="text-base font-bold text-red-400 mb-4 border-b border-zinc-700 pb-2"><i class="fas fa-heartbeat mr-2"></i> Kontak Darurat</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Nama Kontak</label>
                            <input type="text" name="kontak_darurat_nama" value="{{ old('kontak_darurat_nama', $user->kontak_darurat_nama) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Nomor Telepon Kontak</label>
                            <input type="text" name="kontak_darurat_nomor" value="{{ old('kontak_darurat_nomor', $user->kontak_darurat_nomor) }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Hubungan Kekerabatan</label>
                            <input type="text" name="kontak_darurat_hubungan" value="{{ old('kontak_darurat_hubungan', $user->kontak_darurat_hubungan) }}" placeholder="Misal: Orang Tua / Pasangan" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-sm text-white focus:border-sky-500 focus:outline-none">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form> {{-- Batas Penutup Form Utama --}}

    {{-- ========================================== --}}
    {{-- CARD RESET PASSWORD (TERPISAH DI BAWAH)    --}}
    {{-- ========================================== --}}
    <div class="mt-8 bg-zinc-800 p-6 rounded-xl border border-red-900/40 shadow-lg">
        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            <div class="text-amber-500 bg-amber-500/10 p-4 rounded-full cursor-pointer hover:scale-110 transition-transform hidden md:block"
                 onclick="alert('MEKANISME RESET PASSWORD:\n\n1. Jika karyawan lupa password atau tidak bisa login, klik tombol \'Reset ke Password Default\'.\n2. Password karyawan tersebut akan langsung diubah paksa ke default sistem yaitu: #rakhA2022\n3. Beritahu karyawan untuk login menggunakan password tersebut.')">
                <i class="fas fa-exclamation-triangle text-3xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-red-400 mb-2 flex items-center">
                    <i class="fas fa-shield-alt mr-2"></i> Keamanan & Autentikasi
                </h2>
                <p class="text-sm text-gray-300 mb-2">
                    Gunakan fitur ini jika karyawan lupa password atau tidak bisa mengakses akunnya.
                </p>
                <div class="text-xs text-gray-400 flex items-center gap-2 mb-4">
                    <i class="fas fa-info-circle text-sky-400 cursor-pointer" onclick="alert('MEKANISME RESET PASSWORD:\n\n1. Jika karyawan lupa password atau tidak bisa login, klik tombol \'Reset ke Password Default\'.\n2. Password karyawan tersebut akan langsung diubah paksa ke default sistem yaitu: #rakhA2022\n3. Beritahu karyawan untuk login menggunakan password tersebut.')"></i>
                    Sistem akan mengatur ulang password karyawan ke default: <code class="bg-zinc-900 px-2 py-1 rounded text-amber-400 font-mono font-bold">#rakhA2022</code>
                </div>
            </div>
            <div>
                <form action="{{ route('admin.users.resetPassword', $user->id) }}" method="POST" onsubmit="confirmSubmit(event, 'Peringatan: Apakah Anda yakin ingin me-reset password akun ini ke password default sistem (#rakhA2022)?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-colors flex items-center justify-center whitespace-nowrap">
                        <i class="fas fa-key mr-2"></i> Reset ke Password Default
                    </button>
                </form>
            </div>
        </div>
    </div>


    {{-- POPUP KANVAS CROPPER (BASED ON STANDAR INDUSTRI) --}}
    <div id="crop-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex justify-center items-center hidden z-50 p-4">
        <div class="bg-zinc-800 rounded-xl shadow-2xl w-full max-w-xl p-6 border border-zinc-700">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center"><i class="fas fa-crop-alt text-sky-500 mr-2"></i>Sesuaikan Dimensi Foto</h3>
            <div class="w-full h-80 bg-zinc-900 overflow-hidden rounded-lg flex justify-center items-center">
                <img id="image_to_crop" src="" class="max-w-full max-h-full">
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" id="btn-cancel-crop" class="px-4 py-2 bg-zinc-600 text-white text-sm rounded-lg hover:bg-zinc-500">Batal</button>
                <button type="button" id="btn-save-crop" class="px-4 py-2 bg-sky-600 text-white text-sm rounded-lg hover:bg-sky-700 font-bold">Potong & Gunakan</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let cropper = null;
            const imageUpload = document.getElementById('image_upload');
            const cropModal = document.getElementById('crop-modal');
            const imageToCrop = document.getElementById('image_to_crop');
            const btnSaveCrop = document.getElementById('btn-save-crop');
            const btnCancelCrop = document.getElementById('btn-cancel-crop');
            const profilePreview = document.getElementById('profile-preview');
            const profilePreviewFallback = document.getElementById('profile-preview-fallback');
            const hiddenCroppedImage = document.getElementById('cropped_image');

            imageUpload.addEventListener('change', function(e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        imageToCrop.src = event.target.result;
                        cropModal.classList.remove('hidden');
                        
                        if (cropper) { cropper.destroy(); }
                        
                        cropper = new Cropper(imageToCrop, {
                            aspectRatio: 1, // Memaksa rasio 1:1 lingkaran/kotak
                            viewMode: 1,
                            autoCropArea: 1,
                            background: false
                        });
                    };
                    reader.readAsDataURL(files[0]);
                }
            });

            btnCancelCrop.addEventListener('click', function() {
                cropModal.classList.add('hidden');
                imageUpload.value = '';
                if (cropper) { cropper.destroy(); }
            });

            btnSaveCrop.addEventListener('click', function() {
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300,
                });
                
                const base64Image = canvas.toDataURL('image/png');
                
                profilePreview.src = base64Image;
                profilePreview.classList.remove('hidden');
                if(profilePreviewFallback) profilePreviewFallback.classList.add('hidden');
                
                hiddenCroppedImage.value = base64Image;
                
                cropModal.classList.add('hidden');
                if (cropper) { cropper.destroy(); }
            });
        });
    </script>
    @endpush
</x-layout-admin>