<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <main class="bg-gradient-to-br from-sky-50 to-blue-100 p-0 md:p-0 min-h-screen" x-data="{ 
        openIdentitas: true, 
        openPribadi: false, 
        openPayroll: false, 
        openDokumen: false, 
        openKontak: false, 
        openPendidikan: false, 
        openKerja: false 
    }">
        <div class="max-w-6xl mx-auto px-4">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <a href="{{ route('dashboard') }}" class="flex items-center text-blue-600 hover:underline font-bold">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
                </a>
                <a href="{{ route('profile.downloadPdf') }}" class="bg-red-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg hover:bg-red-700 transition w-full sm:w-auto text-center">
                    <i class="fas fa-file-pdf mr-2"></i> Cetak CV / Profil
                </a>
            </div>

            {{-- ALERT SUCCESS --}}
            @if(session('success'))
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
                    <i class="fas fa-check-circle mr-3 text-emerald-500 text-xl"></i> 
                    <span class="font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            {{-- ALERT ERROR SYSTEM --}}
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
                    <i class="fas fa-times-circle mr-3 text-red-500 text-xl"></i> 
                    <span class="font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            {{-- ALERT VALIDATION ERRORS --}}
            @if ($errors->any())
                <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-800 p-4 rounded-xl mb-6 shadow-sm">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-triangle mr-3 text-orange-500 text-xl"></i>
                        <span class="font-bold">Gagal menyimpan! Periksa kembali isian Anda:</span>
                    </div>
                    <ul class="list-disc list-inside text-sm ml-7 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profil.update') }}" id="profile-form" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- CARD 1: HEADER & FOTO --}}
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden mb-6 border border-slate-200">
                    <div class="bg-gradient-to-r from-blue-800 to-indigo-600 p-8 flex flex-col md:flex-row items-center gap-8">
                        <div class="relative group">
                            <img id="preview-img" src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" 
                                 class="w-32 h-32 rounded-full border-4 border-white shadow-2xl object-cover">
                            <label class="absolute bottom-1 right-1 bg-white p-2.5 rounded-full shadow-lg cursor-pointer hover:scale-110 transition-transform">
                                <i class="fas fa-camera text-blue-600"></i>
                                <input type="file" name="profile_picture" class="hidden" onchange="previewImage(event)">
                            </label>
                        </div>
                        <div class="text-white text-center md:text-left">
                            <h2 class="text-3xl font-black">{{ $user->name }}</h2>
                            <p class="text-blue-100 text-lg font-medium">{{ $user->jabatan ?? '-' }} • {{ $user->divisi ?? '-' }}</p>
                            <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-4">
                                <span class="bg-white/20 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider">NIP: {{ $user->nip ?? '-' }}</span>
                                <span class="bg-white/20 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider">Status: {{ $user->status_karyawan ?? '-' }}</span>
                                <span class="bg-white/20 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider">Lokasi: {{ $user->lokasi_kerja ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100">
                        <button type="button" @click="openIdentitas = !openIdentitas" class="w-full flex justify-between items-center p-6 hover:bg-slate-50 transition text-left">
                            <h4 class="font-bold text-slate-800 flex items-center"><i class="fas fa-user-circle mr-3 text-blue-500"></i> Identitas Akun & Keamanan</h4>
                            <i class="fas fa-chevron-down transition-transform duration-300" :class="openIdentitas ? 'rotate-180' : ''"></i>
                        </button>
                        
                        <div x-show="openIdentitas" x-collapse>
                            <div class="p-8 pt-2 grid grid-cols-1 lg:grid-cols-2 gap-10">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-black text-slate-400 uppercase">Nama Lengkap <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm px-4 py-2.5 outline-none border">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-black text-slate-400 uppercase">Email Perusahaan <span class="text-red-500">*</span></label>
                                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm px-4 py-2.5 outline-none border">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-black text-slate-400 uppercase">Nomor Telepon Aktif</label>
                                            <input type="tel" name="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border" placeholder="0812xxxxxxxx">
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-4 bg-orange-50/50 p-6 rounded-2xl border border-orange-200">
                                    <h4 class="font-bold text-slate-800 flex items-center text-sm"><i class="fas fa-shield-alt mr-2 text-orange-500"></i> Keamanan Password</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-black text-orange-400 uppercase">Password Baru</label>
                                            <input type="password" name="password" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" class="w-full border-orange-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white" placeholder="Minimal 8 karakter">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-black text-orange-400 uppercase">Konfirmasi Password</label>
                                            <input type="password" name="password_confirmation" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');" class="w-full border-orange-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white" placeholder="Ulangi password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: INFO PERSONAL & ALAMAT --}}
                <div class="bg-white shadow-xl rounded-3xl mb-6 border border-slate-200 overflow-hidden">
                    <button type="button" @click="openPribadi = !openPribadi" class="w-full flex justify-between items-center p-6 hover:bg-slate-50 transition text-left">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fas fa-info-circle mr-3 text-blue-600"></i> Informasi Pribadi & Alamat
                        </h3>
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="openPribadi ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="openPribadi" x-collapse>
                        <div class="p-8 pt-2">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $user->tempat_lahir) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir ? (\Carbon\Carbon::parse($user->tanggal_lahir)->format('Y-m-d')) : '') }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                                        <option value="">-- Pilih --</option>
                                        <option value="Laki-laki" @selected(old('jenis_kelamin', $user->jenis_kelamin) == 'Laki-laki')>Laki-laki</option>
                                        <option value="Perempuan" @selected(old('jenis_kelamin', $user->jenis_kelamin) == 'Perempuan')>Perempuan</option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Agama</label>
                                    <select name="agama" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                                        <option value="">-- Pilih --</option>
                                        @foreach(['Islam', 'Kristen Protestan', 'Kristen Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm)
                                            <option value="{{ $agm }}" @selected(old('agama', $user->agama) == $agm)>{{ $agm }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Status Pernikahan</label>
                                    <select name="status_pernikahan" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                                        <option value="">-- Pilih --</option>
                                        @foreach(['Belum Menikah', 'Menikah', 'Cerai'] as $stt)
                                            <option value="{{ $stt }}" @selected(old('status_pernikahan', $user->status_pernikahan) == $stt)>{{ $stt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Golongan Darah</label>
                                    <select name="golongan_darah" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                                        <option value="">-- Pilih --</option>
                                        @foreach(['A', 'B', 'AB', 'O', 'Tidak Tahu'] as $goldar)
                                            <option value="{{ $goldar }}" @selected(old('golongan_darah', $user->golongan_darah) == $goldar)>{{ $goldar }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Alamat Sesuai KTP</label>
                                    <textarea name="alamat_ktp" rows="3" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border" placeholder="Alamat lengkap sesuai KTP...">{{ old('alamat_ktp', $user->alamat_ktp) }}</textarea>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Alamat Domisili Saat Ini</label>
                                    <textarea name="alamat_domisili" rows="3" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border" placeholder="Isi jika berbeda dengan alamat KTP...">{{ old('alamat_domisili', $user->alamat_domisili) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: ADMINISTRASI & PAYROLL --}}
                <div class="bg-white shadow-xl rounded-3xl mb-6 border border-slate-200 overflow-hidden">
                    <button type="button" @click="openPayroll = !openPayroll" class="w-full flex justify-between items-center p-6 hover:bg-slate-50 transition text-left">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fas fa-university mr-3 text-emerald-600"></i> Administrasi & Payroll
                        </h3>
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="openPayroll ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="openPayroll" x-collapse>
                        <div class="p-8 pt-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-slate-400 uppercase">Nomor NPWP</label>
                                        <input type="text" name="npwp" value="{{ old('npwp', $user->npwp) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border" placeholder="00.000.000.0-000.000">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-slate-400 uppercase flex justify-between">
                                            <span>Upload Scan NPWP</span>
                                            @if($user->file_npwp)
                                                <a href="{{ asset('storage/' . $user->file_npwp) }}" target="_blank" class="text-emerald-600 font-bold hover:underline"><i class="fas fa-check-circle"></i> File Tersimpan</a>
                                            @endif
                                        </label>
                                        <input type="file" name="file_npwp" class="block w-full text-[10px] text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-emerald-50 file:text-emerald-700">
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Status PTKP (Pajak)</label>
                                    <select name="ptkp" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                                        <option value="">-- Pilih Status --</option>
                                        @foreach(['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'] as $ptkp)
                                            <option value="{{ $ptkp }}" @selected(old('ptkp', $user->ptkp) == $ptkp)>{{ $ptkp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-slate-100">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Nama Bank</label>
                                    <input type="text" name="nama_bank" value="{{ old('nama_bank', $user->nama_bank) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border" placeholder="Contoh: BCA, Mandiri">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Nomor Rekening</label>
                                    <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $user->nomor_rekening) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Nama Pemilik Rekening</label>
                                    <input type="text" name="pemilik_rekening" value="{{ old('pemilik_rekening', $user->pemilik_rekening) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 4: DOKUMEN IDENTITAS --}}
                <div class="bg-white shadow-xl rounded-3xl mb-6 border border-slate-200 overflow-hidden">
                    <button type="button" @click="openDokumen = !openDokumen" class="w-full flex justify-between items-center p-6 hover:bg-slate-50 transition text-left">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fas fa-address-card mr-3 text-indigo-600"></i> Lampiran Dokumen Negara
                        </h3>
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="openDokumen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="openDokumen" x-collapse>
                        <div class="p-8 pt-2 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end border p-4 rounded-2xl border-slate-200 bg-slate-50/30">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-slate-400 uppercase">NIK (KTP)</label>
                                        <input type="text" name="nik" value="{{ old('nik', $user->nik) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white" placeholder="16 Digit NIK">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-slate-400 uppercase flex justify-between">
                                            <span>Upload Scan KTP</span>
                                            @if($user->file_ktp)
                                                <a href="{{ asset('storage/' . $user->file_ktp) }}" target="_blank" class="text-indigo-600 font-bold hover:underline"><i class="fas fa-check-circle"></i> File Tersimpan</a>
                                            @endif
                                        </label>
                                        <input type="file" name="file_ktp" class="block w-full text-[10px] text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end border p-4 rounded-2xl border-slate-200 bg-slate-50/30">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-slate-400 uppercase">No. BPJS Kesehatan</label>
                                        <input type="text" name="bpjs_kesehatan" value="{{ old('bpjs_kesehatan', $user->bpjs_kesehatan) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-slate-400 uppercase flex justify-between">
                                            <span>Upload Kartu BPJS</span>
                                            @if($user->file_bpjs_kesehatan)
                                                <a href="{{ asset('storage/' . $user->file_bpjs_kesehatan) }}" target="_blank" class="text-indigo-600 font-bold hover:underline"><i class="fas fa-check-circle"></i> File Tersimpan</a>
                                            @endif
                                        </label>
                                        <input type="file" name="file_bpjs_kesehatan" class="block w-full text-[10px] text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700">
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end border p-4 rounded-2xl border-slate-200 bg-slate-50/30">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-slate-400 uppercase">No. BPJS Ketenagakerjaan</label>
                                        <input type="text" name="bpjs_ketenagakerjaan" value="{{ old('bpjs_ketenagakerjaan', $user->bpjs_ketenagakerjaan) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-black text-slate-400 uppercase flex justify-between">
                                            <span>Upload Kartu BPJSTK</span>
                                            @if($user->file_bpjs_ketenagakerjaan)
                                                <a href="{{ asset('storage/' . $user->file_bpjs_ketenagakerjaan) }}" target="_blank" class="text-indigo-600 font-bold hover:underline"><i class="fas fa-check-circle"></i> File Tersimpan</a>
                                            @endif
                                        </label>
                                        <input type="file" name="file_bpjs_ketenagakerjaan" class="block w-full text-[10px] text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-700">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 5: KONTAK DARURAT --}}
                <div class="bg-white shadow-xl rounded-3xl mb-6 border border-slate-200 overflow-hidden">
                    <button type="button" @click="openKontak = !openKontak" class="w-full flex justify-between items-center p-6 hover:bg-slate-50 transition text-left">
                        <h3 class="font-bold text-slate-800 flex items-center"><i class="fas fa-phone-alt mr-3 text-red-500"></i> Kontak Darurat</h3>
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="openKontak ? 'rotate-180' : ''"></i>
                    </button>
                    
                    <div x-show="openKontak" x-collapse>
                        <div class="p-8 pt-2 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase">Nama Kontak</label>
                                <input type="text" name="kontak_darurat_nama" value="{{ old('kontak_darurat_nama', $user->kontak_darurat_nama) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase">Nomor Telepon</label>
                                <input type="text" name="kontak_darurat_nomor" value="{{ old('kontak_darurat_nomor', $user->kontak_darurat_nomor) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase">Hubungan</label>
                                <input type="text" name="kontak_darurat_hubungan" value="{{ old('kontak_darurat_hubungan', $user->kontak_darurat_hubungan) }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border" placeholder="Contoh: Orang Tua">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 6: PENDIDIKAN --}}
                <div class="bg-white shadow-xl rounded-3xl mb-6 border border-slate-200 overflow-hidden">
                    <div class="w-full flex justify-between items-center p-6 bg-white">
                        <h3 class="text-xl font-bold text-slate-800 flex items-center">
                            <i class="fas fa-graduation-cap mr-3 text-blue-600"></i> Pendidikan & Ijazah
                        </h3>
                        <div class="flex items-center gap-4">
                            <button type="button" id="add-pendidikan-btn" class="bg-blue-50 text-blue-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-100 transition border border-blue-200">+ Tambah</button>
                            <button type="button" @click="openPendidikan = !openPendidikan" class="p-2">
                                <i class="fas fa-chevron-down transition-transform duration-300" :class="openPendidikan ? 'rotate-180' : ''"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div x-show="openPendidikan" x-collapse>
                        <div id="pendidikan-container" class="p-8 pt-0 space-y-6">
                            @foreach($user->riwayatPendidikan ?? [] as $index => $pnd)
                            <div class="riwayat-item bg-slate-50 p-6 rounded-2xl border border-slate-200 relative group">
                                <input type="hidden" name="pendidikan[{{ $index }}][id]" value="{{ $pnd->id }}">
                                <button type="button" class="delete-riwayat-btn absolute top-4 right-4 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition"><i class="fas fa-trash"></i></button>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-black text-slate-400 uppercase">Jenjang</label>
                                            <select name="pendidikan[{{ $index }}][jenjang]" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white">
                                                @foreach(['SMA/K Sederajat', 'D3', 'D4/S1', 'S2', 'S3'] as $j)
                                                    <option value="{{ $j }}" {{ $pnd->jenjang == $j ? 'selected' : '' }}>{{ $j }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-black text-slate-400 uppercase">Tahun Lulus</label>
                                            <input type="text" name="pendidikan[{{ $index }}][tahun_lulus]" value="{{ $pnd->tahun_lulus }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white">
                                        </div>
                                        <div class="col-span-2 space-y-1">
                                            <label class="text-[10px] font-black text-slate-400 uppercase">Nama Institusi</label>
                                            <input type="text" name="pendidikan[{{ $index }}][nama_institusi]" value="{{ $pnd->nama_institusi }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white">
                                        </div>
                                        <div class="col-span-2 space-y-1">
                                            <label class="text-[10px] font-black text-slate-400 uppercase">Jurusan</label>
                                            <input type="text" name="pendidikan[{{ $index }}][jurusan]" value="{{ $pnd->jurusan }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white">
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-end space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase flex justify-between">
                                            <span>Lampiran Ijazah</span>
                                            @if($pnd->file_ijazah)
                                                <a href="{{ asset('storage/' . $pnd->file_ijazah) }}" target="_blank" class="text-blue-600 font-bold hover:underline"><i class="fas fa-check-circle"></i> File Tersimpan</a>
                                            @endif
                                        </label>
                                        <div class="flex items-center gap-3">
                                            <input type="file" name="pendidikan[{{ $index }}][file_ijazah]" class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- SECTION 7: PENGALAMAN KERJA --}}
                <div class="bg-white shadow-xl rounded-3xl mb-12 border border-slate-200 overflow-hidden">
                    <div class="w-full flex justify-between items-center p-6 bg-white">
                        <h3 class="text-xl font-bold text-slate-800 flex items-center">
                            <i class="fas fa-briefcase mr-3 text-orange-500"></i> Pengalaman Kerja
                        </h3>
                        <div class="flex items-center gap-4">
                            <button type="button" id="add-pekerjaan-btn" class="bg-orange-50 text-orange-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-orange-100 transition border border-orange-200">+ Tambah</button>
                            <button type="button" @click="openKerja = !openKerja" class="p-2">
                                <i class="fas fa-chevron-down transition-transform duration-300" :class="openKerja ? 'rotate-180' : ''"></i>
                            </button>
                        </div>
                    </div>

                    <div x-show="openKerja" x-collapse>
                        <div id="pekerjaan-container" class="p-8 pt-0 space-y-6">
                            @foreach($user->riwayatPekerjaan ?? [] as $index => $pkj)
                            <div class="riwayat-item bg-slate-50 p-6 rounded-2xl border border-slate-200 relative group">
                                <input type="hidden" name="pekerjaan[{{ $index }}][id]" value="{{ $pkj->id }}">
                                <button type="button" class="delete-riwayat-btn absolute top-4 right-4 text-slate-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition"><i class="fas fa-trash"></i></button>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1"><label class="text-[10px] font-black text-slate-400 uppercase">Nama Perusahaan</label><input type="text" name="pekerjaan[{{ $index }}][nama_perusahaan]" value="{{ $pkj->nama_perusahaan }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white"></div>
                                    <div class="space-y-1"><label class="text-[10px] font-black text-slate-400 uppercase">Posisi</label><input type="text" name="pekerjaan[{{ $index }}][posisi]" value="{{ $pkj->posisi }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white"></div>
                                    <div class="space-y-1"><label class="text-[10px] font-black text-slate-400 uppercase">Tanggal Mulai</label><input type="date" name="pekerjaan[{{ $index }}][tanggal_mulai]" value="{{ $pkj->tanggal_mulai ? (\Carbon\Carbon::parse($pkj->tanggal_mulai)->format('Y-m-d')) : '' }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white"></div>
                                    <div class="space-y-1"><label class="text-[10px] font-black text-slate-400 uppercase">Tanggal Selesai</label><input type="date" name="pekerjaan[{{ $index }}][tanggal_selesai]" value="{{ $pkj->tanggal_selesai ? (\Carbon\Carbon::parse($pkj->tanggal_selesai)->format('Y-m-d')) : '' }}" class="w-full border-slate-300 rounded-xl text-sm px-4 py-2.5 outline-none border bg-white"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- SUBMIT --}}
                <div class="flex justify-end gap-4 pb-16">
                    <button type="reset" class="px-10 py-4 rounded-2xl border border-slate-300 font-bold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" class="px-10 py-4 rounded-2xl bg-blue-600 text-white font-bold shadow-2xl hover:bg-blue-700 hover:-translate-y-1 transition active:scale-95">
                        <i class="fas fa-save mr-2"></i> Update Semua Data
                    </button>
                </div>
            </form>
        </div>
    </main>

    {{-- TEMPLATES --}}
    <template id="pendidikan-template">
        <div class="riwayat-item bg-blue-50/40 p-6 rounded-2xl border border-blue-200 relative mb-6">
            <button type="button" class="delete-riwayat-btn absolute top-4 right-4 text-red-400 hover:text-red-600 transition"><i class="fas fa-times-circle"></i></button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-blue-500 uppercase">Jenjang</label>
                        <select name="pendidikan[__NEW_INDEX__][jenjang]" class="w-full border-blue-300 rounded-xl text-sm px-4 py-2.5 border bg-white">
                            <option value="SMA/K Sederajat">SMA/K Sederajat</option>
                            <option value="D3">D3</option>
                            <option value="D4/S1">D4/S1</option>
                            <option value="S2">S2</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-blue-500 uppercase">Tahun Lulus</label>
                        <input type="text" name="pendidikan[__NEW_INDEX__][tahun_lulus]" class="w-full border-blue-300 rounded-xl text-sm px-4 py-2.5 border bg-white">
                    </div>
                    <div class="col-span-2 space-y-1">
                        <label class="text-[10px] font-black text-blue-500 uppercase">Nama Institusi</label>
                        <input type="text" name="pendidikan[__NEW_INDEX__][nama_institusi]" class="w-full border-blue-300 rounded-xl text-sm px-4 py-2.5 border bg-white">
                    </div>
                    <div class="col-span-2 space-y-1">
                        <label class="text-[10px] font-black text-blue-500 uppercase">Jurusan</label>
                        <input type="text" name="pendidikan[__NEW_INDEX__][jurusan]" class="w-full border-blue-300 rounded-xl text-sm px-4 py-2.5 border bg-white">
                    </div>
                </div>
                <div class="flex flex-col justify-end">
                    <label class="text-[10px] font-black text-blue-500 uppercase mb-2">Upload Ijazah</label>
                    <input type="file" name="pendidikan[__NEW_INDEX__][file_ijazah]" class="block w-full text-xs text-slate-400 file:mr-2 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-600 file:text-white">
                </div>
            </div>
        </div>
    </template>

    <template id="pekerjaan-template">
        <div class="riwayat-item bg-orange-50/40 p-6 rounded-2xl border border-orange-200 relative mb-6">
            <button type="button" class="delete-riwayat-btn absolute top-4 right-4 text-orange-400 hover:text-orange-600 transition"><i class="fas fa-times-circle"></i></button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1"><label class="text-[10px] font-black text-orange-500 uppercase">Nama Perusahaan</label><input type="text" name="pekerjaan[__NEW_INDEX__][nama_perusahaan]" class="w-full border-orange-300 rounded-xl text-sm px-4 py-2.5 border bg-white"></div>
                <div class="space-y-1"><label class="text-[10px] font-black text-orange-500 uppercase">Posisi</label><input type="text" name="pekerjaan[__NEW_INDEX__][posisi]" class="w-full border-orange-300 rounded-xl text-sm px-4 py-2.5 border bg-white"></div>
                <div class="space-y-1"><label class="text-[10px] font-black text-orange-500 uppercase">Mulai</label><input type="date" name="pekerjaan[__NEW_INDEX__][tanggal_mulai]" class="w-full border-orange-300 rounded-xl text-sm px-4 py-2.5 border bg-white"></div>
                <div class="space-y-1"><label class="text-[10px] font-black text-orange-500 uppercase">Selesai</label><input type="date" name="pekerjaan[__NEW_INDEX__][tanggal_selesai]" class="w-full border-orange-300 rounded-xl text-sm px-4 py-2.5 border bg-white"></div>
            </div>
        </div>
    </template>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() { document.getElementById('preview-img').src = reader.result; }
            reader.readAsDataURL(event.target.files[0]);
        }

        document.addEventListener('DOMContentLoaded', function() {
            function setupDynamicForm(containerId, btnId, templateId, deleteClass) {
                const container = document.getElementById(containerId);
                const btn = document.getElementById(btnId);
                const template = document.getElementById(templateId);
                if(!container || !btn || !template) return;

                btn.addEventListener('click', () => {
                    const index = 'new_' + Date.now();
                    const clone = template.content.cloneNode(true);
                    clone.querySelectorAll('[name*="__NEW_INDEX__"]').forEach(el => {
                        el.name = el.name.replace(/__NEW_INDEX__/g, index);
                    });
                    container.appendChild(clone);
                });

                container.addEventListener('click', (e) => {
                    if(e.target.closest(deleteClass)) e.target.closest('.riwayat-item').remove();
                });
            }
            setupDynamicForm('pendidikan-container', 'add-pendidikan-btn', 'pendidikan-template', '.delete-riwayat-btn');
            setupDynamicForm('pekerjaan-container', 'add-pekerjaan-btn', 'pekerjaan-template', '.delete-riwayat-btn');
        });
    </script>
</x-layout-users>