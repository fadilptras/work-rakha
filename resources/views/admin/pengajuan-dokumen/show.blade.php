<x-layout-admin>
    <x-slot:title>Detail Pengajuan Dokumen</x-slot:title>

    <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700">
        {{-- Header --}}
        <div class="p-6 border-b border-zinc-700 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-white">Detail Pengajuan: {{ $pengajuanDana->jenis_dokumen }}</h2>
                <p class="text-sm text-zinc-400 mt-1">Diajukan oleh: {{ $pengajuanDana->user->name }}</p>
            </div>
            <a href="{{ route('admin.pengajuan-dokumen.index') }}" class="text-sm text-zinc-300 hover:text-white transition-colors duration-200 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
            </a>
        </div>
        
        @if (session('success'))
            <div class="m-6 bg-emerald-500/10 text-emerald-300 text-sm p-4 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- Body Content --}}
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-6">
            {{-- Kolom Kiri: Detail dari Karyawan --}}
            <div class="md:col-span-2 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Tanggal Pengajuan</label>
                    <p class="text-base text-zinc-200">{{ $pengajuanDana->created_at->translatedFormat('l, d F Y - H:i') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Jenis Dokumen Diminta</label>
                    <p class="text-base text-zinc-200 font-semibold">{{ $pengajuanDana->jenis_dokumen }}</p>
                </div>
                @if($pengajuanDana->deskripsi)
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Keterangan Tambahan dari Karyawan</label>
                    <p class="text-base text-zinc-300 bg-zinc-900/50 p-3 rounded-lg border border-zinc-700">{{ $pengajuanDana->deskripsi }}</p>
                </div>
                @endif
                @if($pengajuanDana->file_pendukung)
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">File Pendukung dari Karyawan</label>
                    <a href="{{ asset('storage/' . $pengajuanDana->file_pendukung) }}" target="_blank" class="text-indigo-400 hover:text-indigo-500 hover:underline inline-flex items-center">
                        <i class="fas fa-paperclip mr-2"></i> Lihat Lampiran
                    </a>
                </div>
                @endif
            </div>

            {{-- Kolom Kanan: Form Aksi Admin --}}
            <div class="md:col-span-1 space-y-6 border-t md:border-t-0 md:border-l border-zinc-700 pt-6 md:pt-0 md:pl-8">
                <h3 class="text-lg font-semibold text-white">Tindakan Admin</h3>
                <form action="{{ route('admin.pengajuan-dokumen.update', $pengajuanDana) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    {{-- Select Status --}}
                    <div>
                        <label for="status" class="block mb-2 text-sm font-medium text-zinc-400">Ubah Status</label>
                        <select id="status" name="status" class="bg-zinc-700 border border-zinc-600 text-white text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5">
                            <option value="diproses" @selected($pengajuanDana->status == 'diproses')>Diproses</option>
                            <option value="selesai" @selected($pengajuanDana->status == 'selesai')>Selesai (Terbitkan)</option>
                            <option value="ditolak" @selected($pengajuanDana->status == 'ditolak')>Ditolak</option>
                        </select>
                    </div>

                    {{-- Textarea Catatan --}}
                    <div>
                        <label for="catatan_admin" class="block mb-2 text-sm font-medium text-zinc-400">Catatan untuk Karyawan (Opsional)</label>
                        <textarea id="catatan_admin" name="catatan_admin" rows="3" class="bg-zinc-700 border border-zinc-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="Contoh: Dokumen sedang disiapkan...">{{ $pengajuanDana->catatan_admin }}</textarea>
                    </div>

                    {{-- Input File Hasil --}}
                    <div>
                        <label class="block mb-2 text-sm font-medium text-zinc-400" for="file_hasil">Upload Dokumen Hasil (PDF/DOC)</label>
                        <input class="block w-full text-sm text-zinc-400 border border-zinc-600 rounded-lg cursor-pointer bg-zinc-700 focus:outline-none" id="file_hasil" name="file_hasil" type="file">
                        @if($pengajuanDana->file_hasil)
                            <p class="mt-2 text-xs text-zinc-400">File saat ini: 
                                <a href="{{ asset('storage/' . $pengajuanDana->file_hasil) }}" target="_blank" class="text-indigo-400 hover:underline">Lihat Dokumen</a>
                            </p>
                        @endif
                    </div>
                    
                    <button type="submit" class="w-full text-white bg-amber-500 hover:bg-amber-600 focus:ring-4 focus:outline-none focus:ring-amber-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors duration-200">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layout-admin>