<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="bg-slate-100 min-h-screen p-0 md:p-0">
        <div class="container mx-auto">
            
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow" role="alert">
                    <p class="font-bold">Berhasil!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="space-y-8">
                {{-- KARTU FORM PENGAJUAN --}}
                <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                    <div class="p-6 md:p-8 bg-[#333446] text-white">
                        <div class="flex items-center gap-4">
                            <i class="fas fa-file-signature text-3xl opacity-80"></i>
                            <div>
                                <h2 class="text-xl font-bold">Formulir Pengajuan Dokumen</h2>
                                <p class="text-sm text-slate-300">Isi detail di bawah untuk meminta dokumen resmi.</p>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('pengajuan_dokumen.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                            {{-- Nama Pemohon (Otomatis) --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Pemohon</label>
                                <input type="text" class="w-full p-3 bg-slate-100 border-transparent rounded-lg" value="{{ Auth::user()->name }}" readonly>
                            </div>

                            {{-- Departemen (Otomatis) --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Departemen</label>
                                <input type="text" class="w-full p-3 bg-slate-100 border-transparent rounded-lg" value="{{ Auth::user()->divisi ?? 'Belum Diatur' }}" readonly>
                            </div>

                            {{-- Jenis Dokumen --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1" for="jenis-dokumen">
                                    Jenis Dokumen <span class="text-red-500">*</span>
                                </label>
                                <select id="jenis-dokumen" name="jenis_dokumen" class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:shadow-inner" required>
                                    <option value="">Pilih Dokumen</option>
                                    <option value="Slip Gaji">Slip Gaji</option>
                                    <option value="Surat Keterangan Kerja">Surat Keterangan Kerja</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            {{-- Deskripsi Keterangan --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1" for="deskripsi">
                                    Keterangan Tambahan
                                </label>
                                <textarea id="deskripsi" name="deskripsi" class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:shadow-inner" rows="3" placeholder="Contoh: Untuk keperluan pengajuan KPR, mohon sertakan slip gaji 3 bulan terakhir."></textarea>
                            </div>

                            {{-- Upload Dokumen Pendukung --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1" for="file-pendukung">
                                    Upload Dokumen Pendukung (Opsional)
                                </label>
                                <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center bg-slate-50 hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('file-pendukung').click()">
                                    <input type="file" id="file-pendukung" name="file_pendukung" class="hidden">
                                    <div id="file-upload-ui">
                                        <div class="w-12 h-12 mx-auto bg-slate-200 rounded-full flex items-center justify-center shadow-inner"><i class="fas fa-cloud-upload-alt text-2xl text-slate-500"></i></div>
                                        <p class="text-slate-600 mt-3 font-semibold">Klik untuk <span class="text-indigo-600 font-bold">pilih file</span></p><p class="text-xs text-slate-500 mt-1">PDF, DOC, JPG, PNG (max. 5MB)</p>
                                    </div>
                                    <div id="file-name-display" class="hidden font-semibold text-slate-700"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex justify-end space-x-4 pt-6 mt-6 border-t border-slate-200">
                            <button type="reset" class="bg-white hover:bg-slate-100 text-slate-700 font-semibold py-2 px-6 rounded-lg border border-slate-300 transition">Reset</button>
                            <button type="submit" class="bg-gradient-to-r from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 text-white font-semibold py-2 px-6 rounded-lg shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">Ajukan Dokumen</button>
                        </div>
                    </form>
                </div>

                {{-- KARTU RIWAYAT PENGAJUAN --}}
                <div class="mt-12 bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                    <div class="p-6 md:p-8">
                        <h2 class="text-2xl font-bold text-slate-800">Riwayat Pengajuan Dokumen Anda</h2>
                        <p class="text-sm text-slate-500 mt-1">Daftar semua pengajuan dokumen yang telah Anda buat.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-t border-slate-200 text-sm">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                                    <th class="px-6 py-3 text-left font-semibold">Jenis Dokumen</th>
                                    <th class="px-6 py-3 text-left font-semibold">Status</th>
                                    <th class="px-6 py-3 text-center font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($riwayatDokumen as $dokumen)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 text-slate-600">{{ $dokumen->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-800">{{ $dokumen->jenis_dokumen }}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold px-2 py-1 rounded-full text-xs capitalize
                                            @if($dokumen->status == 'diajukan') bg-yellow-100 text-yellow-800
                                            @elseif($dokumen->status == 'diproses') bg-blue-100 text-blue-800
                                            @elseif($dokumen->status == 'selesai') bg-green-100 text-green-800
                                            @elseif($dokumen->status == 'ditolak') bg-red-100 text-red-800
                                            @endif">
                                            {{ $dokumen->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($dokumen->status === 'selesai' && $dokumen->file_hasil)
                                            <a href="{{ route('pengajuan_dokumen.download', $dokumen) }}" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg shadow-md text-xs flex-shrink-0 inline-flex items-center gap-2">
                                                <i class="fas fa-download"></i> Unduh
                                            </a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td class="px-6 py-10 text-center text-slate-500" colspan="4">Belum ada pengajuan dokumen yang dibuat.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    {{-- Script untuk menampilkan nama file setelah dipilih --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('file-pendukung');
        const fileUploadUI = document.getElementById('file-upload-ui');
        const fileNameDisplay = document.getElementById('file-name-display');

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    if(fileUploadUI) fileUploadUI.classList.add('hidden');
                    if(fileNameDisplay) {
                        fileNameDisplay.innerHTML = `<i class="fas fa-check-circle text-green-500 mr-2"></i> ${this.files[0].name}`;
                        fileNameDisplay.classList.remove('hidden');
                    }
                }
            });
        }
    });
    </script>
    @endpush
</x-layout-users>