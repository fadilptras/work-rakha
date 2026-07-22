<x-layout-admin>
    <x-slot:title>Kelola Karyawan</x-slot:title>

    {{-- Header Halaman --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Manajemen Akun Karyawan</h1>
        <button id="open-add-modal-btn" 
            class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
            <i class="fas fa-plus mr-2"></i> Tambah Karyawan
        </button>
    </div>

    {{-- Notifikasi --}}
    
    
    @if ($errors->any())
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Data Karyawan (Dikelompokkan Berdasarkan Divisi) --}}
    <div class="space-y-6">
        @forelse ($usersByDivision as $divisi => $karyawanList)
            <div class="bg-zinc-800 rounded-lg shadow-lg overflow-hidden border border-zinc-700">
                
                {{-- Header Divisi (Accordion Toggle) --}}
                <div class="bg-zinc-700 px-6 py-4 flex justify-between items-center cursor-pointer toggle-divisi" data-target="divisi-{{ Str::slug($divisi) }}">
                    <h2 class="text-lg font-bold text-sky-400 flex items-center">
                        <i class="fas fa-users mr-3"></i> {{ $divisi }}
                    </h2>
                    <div class="flex items-center space-x-4">
                        <span class="bg-sky-600 text-white text-xs px-3 py-1 rounded-full font-semibold">
                            {{ $karyawanList->count() }} Karyawan
                        </span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300 icon-chevron"></i>
                    </div>
                </div>

                {{-- Tabel Karyawan per Divisi --}}
                <div id="divisi-{{ Str::slug($divisi) }}" class="overflow-x-auto transition-all duration-300">
                    <table class="min-w-full divide-y divide-zinc-700">
                        <thead class="bg-zinc-800/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Profil</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kontak</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Jabatan / Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Cuti</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-zinc-800 divide-y divide-zinc-700">
                            @foreach ($karyawanList as $user)
                                <tr class="hover:bg-zinc-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($user->profile_picture)
                                                    <img class="h-10 w-10 rounded-full object-cover border border-zinc-600" src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}" loading="lazy">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-zinc-600 flex items-center justify-center text-white font-bold border border-zinc-500">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-white">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-400">Id App: #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-300"><i class="fas fa-envelope mr-1 text-gray-500"></i> {{ $user->email }}</div>
                                        <div class="text-sm text-gray-400 mt-1"><i class="fas fa-phone mr-1 text-gray-500"></i> {{ $user->nomor_telepon ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-white font-semibold">{{ $user->jabatan ?? 'Staff' }}</div>
                                        @if($user->is_kepala_divisi)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mt-1">
                                                Kepala Divisi
                                            </span>
                                        @else
                                            <form action="{{ route('admin.employees.setAsHead', $user->id) }}" method="POST" class="mt-1">
                                                @csrf
                                                <button type="submit" class="text-xs text-sky-400 hover:text-sky-300 underline" onclick="return confirm('Jadikan {{ $user->name }} sebagai Kepala Divisi {{ $divisi }}?')">
                                                    Set Sebagai Kepala
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        <span class="bg-zinc-700 px-2 py-1 rounded text-xs font-bold">{{ $user->jatah_cuti }} Hari</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="open-detail-modal-btn text-emerald-400 hover:text-emerald-300 mr-3" data-id="{{ $user->id }}" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <a href="{{ route('admin.employees.edit', $user->id) }}" class="inline-block text-sky-400 hover:text-sky-300 mr-3" title="Edit Data & Reset Password">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('admin.employees.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="confirmSubmit(event, 'Apakah Anda yakin ingin menghapus akun ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300" title="Hapus Karyawan">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-zinc-800 p-8 rounded-lg text-center border border-zinc-700">
                <i class="fas fa-users-slash text-4xl text-gray-500 mb-3"></i>
                <p class="text-gray-400 text-lg">Belum ada data karyawan.</p>
            </div>
        @endforelse
    </div>

    {{-- ========================================== --}}
    {{-- MODAL TAMBAH KARYAWAN                      --}}
    {{-- ========================================== --}}
    <div id="add-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center hidden z-50 transition-opacity duration-300 opacity-0">
        <div class="bg-zinc-800 rounded-xl shadow-2xl w-full max-w-2xl transform scale-95 transition-transform duration-300 max-h-[90vh] flex flex-col border border-zinc-700">
            <div class="flex justify-between items-center p-6 border-b border-zinc-700">
                <h3 class="text-xl font-bold text-white flex items-center">
                    Tambah Karyawan Baru
                </h3>
                <button type="button" class="close-modal text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar">
                
                {{-- INFO BOX: Penjelasan Mekanisme Password Default dengan Icon Clickable --}}
                <div class="col-span-1 md:col-span-2 mb-6 bg-sky-900/30 border border-sky-700 rounded-lg p-4 flex items-start gap-3 shadow-inner">
                    <div class="text-sky-400 mt-0.5 cursor-pointer hover:scale-110 transition-transform" 
                         onclick="alert('INFORMASI PASSWORD:\n\n1. Karyawan yang ditambahkan tidak perlu membuat password.\n2. Untuk buat akun baru default passwordnya yaitu: #rakhA2022!\n3. Karyawan dapat langsung login menggunakan Email dan Password default tersebut.')">
                        <i class="fas fa-info-circle text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-sky-300 cursor-pointer" onclick="alert('INFORMASI PASSWORD:\n\n1. Karyawan yang ditambahkan tidak perlu membuat password.\n2. Untuk buat akun baru default passwordnya yaitu: #rakhA2022\n3. Karyawan dapat langsung login menggunakan Email dan Password default tersebut.')">
                            Sistem Password Otomatis
                        </h4>
                        <p class="text-xs text-gray-300 mt-1">Karyawan baru tidak perlu diisikan password. Sistem otomatis memberikan password default: <span class="text-amber-400 font-mono font-bold">#rakhA2022</span>.</p>
                    </div>
                </div>

                <form id="add-form" action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="role" value="user">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Masukkan nama lengkap">
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="nama@perusahaan.com">
                        </div>

                        <div class="mb-4">
                            <label for="divisi" class="block text-sm font-medium text-gray-300 mb-1">Divisi <span class="text-red-500">*</span></label>
                            <input type="text" name="divisi" id="divisi" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Misal: IT, HRD, Marketing">
                        </div>

                        <div class="mb-4">
                            <label for="jabatan" class="block text-sm font-medium text-gray-300 mb-1">Jabatan</label>
                            <input type="text" name="jabatan" id="jabatan" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Opsional (Default: Staff)">
                        </div>

                        <div class="mb-4">
                            <label for="tanggal_bergabung" class="block text-sm font-medium text-gray-300 mb-1">Tanggal Bergabung</label>
                            <input type="date" name="tanggal_bergabung" id="tanggal_bergabung" value="{{ date('Y-m-d') }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                        </div>

                        <div class="mb-4">
                            <label for="nomor_telepon" class="block text-sm font-medium text-gray-300 mb-1">No. Telepon / WA</label>
                            <input type="text" name="nomor_telepon" id="nomor_telepon" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Contoh: 081234567890">
                        </div>

                        <div class="mb-4">
                            <label for="jatah_cuti" class="block text-sm font-medium text-gray-300 mb-1">Jatah Cuti Tahunan <span class="text-red-500">*</span></label>
                            <input type="number" name="jatah_cuti" id="jatah_cuti" required min="0" value="12" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                        </div>
                    </div>
                </form>
            </div>
            <div class="p-6 border-t border-zinc-700 bg-zinc-800 rounded-b-xl flex justify-end space-x-3">
                <button type="button" class="close-modal px-4 py-2 bg-zinc-600 text-white rounded-lg hover:bg-zinc-500 transition-colors">Batal</button>
                <button type="submit" form="add-form" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 shadow-md transition-colors font-semibold flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL DETAIL KARYAWAN (ALL DATA FIELD)     --}}
    {{-- ========================================== --}}
    <div id="detail-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center hidden z-50 transition-opacity duration-300 opacity-0">
        <div class="bg-zinc-800 rounded-xl shadow-2xl w-full max-w-5xl transform scale-95 transition-transform duration-300 max-h-[90vh] flex flex-col border border-zinc-700">
            <div class="flex justify-between items-center p-6 border-b border-zinc-700">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-address-card text-sky-400 mr-3 text-2xl"></i> Detail Lengkap Profil Karyawan
                </h3>
                <div class="flex items-center gap-3">
                    <a id="btn-download-pdf" href="#" class="px-3 py-1.5 bg-red-600 text-white text-sm rounded hover:bg-red-700 flex items-center gap-2">
                        <i class="fas fa-file-pdf"></i> Cetak Profil
                    </a>
                    <button type="button" class="close-modal text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
                </div>
            </div>
            
            <div class="p-0 overflow-y-auto custom-scrollbar flex-grow" id="detail-modal-body">
                {{-- Diisi secara dinamis oleh JavaScript --}}
            </div>
            
            <div class="p-6 border-t border-zinc-700 bg-zinc-800 rounded-b-xl flex justify-end">
                <button type="button" class="close-modal px-6 py-2 bg-sky-600 text-white font-bold rounded-lg hover:bg-sky-700 transition-colors">Tutup</button>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            document.querySelectorAll('.toggle-divisi').forEach(header => {
                header.addEventListener('click', () => {
                    const targetId = header.getAttribute('data-target');
                    const targetDiv = document.getElementById(targetId);
                    const icon = header.querySelector('.icon-chevron');
                    
                    if (targetDiv.classList.contains('hidden')) {
                        targetDiv.classList.remove('hidden');
                        icon.style.transform = 'rotate(0deg)';
                    } else {
                        targetDiv.classList.add('hidden');
                        icon.style.transform = 'rotate(-90deg)';
                    }
                });
            });

            function toggleModal(modal, show) {
                if (show) {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                        modal.querySelector('div').classList.remove('scale-95');
                    }, 10);
                } else {
                    modal.classList.add('opacity-0');
                    modal.querySelector('div').classList.add('scale-95');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                }
            }

            const addModal = document.getElementById('add-modal');
            const detailModal = document.getElementById('detail-modal');
            
            document.getElementById('open-add-modal-btn')?.addEventListener('click', () => {
                toggleModal(addModal, true);
            });

            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modal = btn.closest('.fixed.inset-0');
                    if (modal) toggleModal(modal, false);
                });
            });

            window.addEventListener('click', (e) => {
                if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
                    toggleModal(e.target, false);
                }
            });

            document.querySelectorAll('.open-detail-modal-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const userId = btn.getAttribute('data-id');
                    const modalBody = document.getElementById('detail-modal-body');
                    
                    modalBody.innerHTML = '<div class="flex justify-center items-center h-48"><i class="fas fa-spinner fa-spin text-4xl text-sky-400"></i></div>';
                    toggleModal(detailModal, true);

                    try {
                        const response = await fetch(`/admin/employees/${userId}/ajax-detail`);
                        if (!response.ok) throw new Error('Gagal memuat profil');
                        const user = await response.json();
                        
                        const baseUrl = "{{ url('storage') }}";
                        
                        const tglGabung = user.tanggal_bergabung ? new Date(user.tanggal_bergabung).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-';
                        const tglLahir = user.tanggal_lahir ? new Date(user.tanggal_lahir).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-';
                        const tglMulaiKontrak = user.tanggal_mulai_kontrak ? new Date(user.tanggal_mulai_kontrak).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-';
                        const tglAkhirKontrak = user.tanggal_akhir_kontrak ? new Date(user.tanggal_akhir_kontrak).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-';

                        document.getElementById('btn-download-pdf').href = `/admin/employees/${user.id}/download-pdf`;
                        
                        let profileImg = user.profile_picture ? `${baseUrl}/${user.profile_picture}` : null;
                        let initial = user.name.charAt(0).toUpperCase();
                        let imgHTML = profileImg 
                            ? `<img src="${profileImg}" alt="Foto ${user.name}" class="w-32 h-32 rounded-full object-cover border-4 border-sky-500 shadow-xl">` 
                            : `<div class="w-32 h-32 rounded-full bg-zinc-700 flex items-center justify-center border-4 border-zinc-600 shadow-xl"><span class="text-5xl text-white font-bold">${initial}</span></div>`;

                        let pendHTML = '<p class="text-gray-400 italic text-sm">Belum ada riwayat pendidikan.</p>';
                        if (user.riwayat_pendidikan && user.riwayat_pendidikan.length > 0) {
                            pendHTML = user.riwayat_pendidikan.map(p => `
                                <div class="relative pl-6 border-l-2 border-sky-500 mb-3">
                                    <span class="absolute -left-[6px] top-1 w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                                    <h5 class="font-bold text-white text-sm">${p.jenjang ?? p.tingkat} - ${p.nama_institusi}</h5>
                                    <p class="text-sky-300 text-xs">${p.jurusan ?? '-'} (${p.tahun_lulus})</p>
                                </div>
                            `).join('');
                        }

                        let pekjHTML = '<p class="text-gray-400 italic text-sm">Belum ada pengalaman kerja.</p>';
                        if (user.riwayat_pekerjaan && user.riwayat_pekerjaan.length > 0) {
                            pekjHTML = user.riwayat_pekerjaan.map(p => `
                                <div class="relative pl-6 border-l-2 border-emerald-500 mb-3">
                                    <span class="absolute -left-[6px] top-1 w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <h5 class="font-bold text-white text-sm">${p.posisi} di ${p.nama_perusahaan}</h5>
                                    <p class="text-emerald-300 text-xs">${p.tanggal_mulai ?? p.tahun_mulai} - ${p.tanggal_selesai ?? p.tahun_selesai}</p>
                                </div>
                            `).join('');
                        }

                        const bodyHTML = `
                            <div class="grid grid-cols-1 md:grid-cols-3">
                                <div class="bg-zinc-900/50 p-6 flex flex-col items-center border-r border-zinc-700">
                                    <div class="mb-4 relative">
                                        ${imgHTML}
                                        ${user.is_kepala_divisi ? `<div class="absolute -bottom-1 -right-1 bg-purple-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold border border-zinc-800"><i class="fas fa-crown"></i> KADIV</div>` : ''}
                                    </div>
                                    <h4 class="text-lg font-bold text-white text-center mb-0.5">${user.name}</h4>
                                    <p class="text-sky-400 font-medium text-xs text-center mb-4">${user.jabatan ?? 'Staff'} - ${user.divisi}</p>
                                    
                                    <div class="w-full space-y-3">
                                        <div class="bg-zinc-800/80 p-2.5 rounded border border-zinc-700/50">
                                            <p class="text-[10px] text-gray-400 uppercase tracking-wider">Email Utama</p>
                                            <p class="text-white text-xs break-all"><i class="fas fa-envelope text-gray-500 mr-1.5"></i>${user.email}</p>
                                        </div>
                                        <div class="bg-zinc-800/80 p-2.5 rounded border border-zinc-700/50">
                                            <p class="text-[10px] text-gray-400 uppercase tracking-wider">No. Telepon / WA</p>
                                            <p class="text-white text-xs"><i class="fas fa-phone text-gray-500 mr-1.5"></i>${user.nomor_telepon ?? '-'}</p>
                                        </div>
                                        <div class="bg-zinc-800/80 p-2.5 rounded border border-zinc-700/50">
                                            <p class="text-[10px] text-gray-400 uppercase tracking-wider">Status / Tgl Gabung</p>
                                            <p class="text-white text-xs"><i class="fas fa-calendar-alt text-gray-500 mr-1.5"></i>${tglGabung} (${user.status_karyawan ?? 'Aktif'})</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-span-2 p-6 grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[65vh] overflow-y-auto custom-scrollbar">
                                    <div>
                                        <h4 class="text-sm font-bold text-sky-400 mb-3 border-b border-zinc-700 pb-1"><i class="fas fa-user-circle mr-1.5"></i>Biodata Pribadi</h4>
                                        <table class="w-full text-xs space-y-2">
                                            <tr><td class="text-gray-400 py-1 w-28">NIK KTP</td><td class="text-white">: ${user.nik ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">Tempat Lahir</td><td class="text-white">: ${user.tempat_lahir ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">Tanggal Lahir</td><td class="text-white">: ${tglLahir}</td></tr>
                                            <tr><td class="text-gray-400 py-1">Gender / Darah</td><td class="text-white">: ${user.jenis_kelamin ?? '-'} (${user.golongan_darah ?? '-'})</td></tr>
                                            <tr><td class="text-gray-400 py-1">Agama</td><td class="text-white">: ${user.agama ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">Status Nikah</td><td class="text-white">: ${user.status_pernikahan ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">Alamat KTP</td><td class="text-white break-words">: ${user.alamat_ktp ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">Alamat Domisili</td><td class="text-white break-words">: ${user.alamat_domisili ?? '-'}</td></tr>
                                        </table>
                                    </div>

                                     <div>
                                        <h4 class="text-sm font-bold text-emerald-400 mb-3 border-b border-zinc-700 pb-1"><i class="fas fa-briefcase mr-1.5"></i>Ketenagakerjaan & Kepegawaian</h4>
                                        <table class="w-full text-xs space-y-2">
                                            <tr><td class="text-gray-400 py-1 w-28">NIP Perusahaan</td><td class="text-white">: ${user.nip ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">Tgl Bergabung</td><td class="text-white">: ${tglGabung}</td></tr>
                                            <tr><td class="text-gray-400 py-1">Jatah Cuti Tahunan</td><td class="text-white font-bold">: ${user.jatah_cuti ?? 12} Hari</td></tr>
                                            <tr><td class="text-gray-400 py-1">Sisa Kuota Cuti</td><td class="text-emerald-400 font-bold">: ${user.sisa_cuti ?? 0} Hari</td></tr>
                                        </table>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-bold text-yellow-500 mb-3 border-b border-zinc-700 pb-1"><i class="fas fa-credit-card mr-1.5"></i>Finansial & Legalitas</h4>
                                        <table class="w-full text-xs space-y-2">
                                            <tr><td class="text-gray-400 py-1 w-28">Nama Bank</td><td class="text-white">: ${user.nama_bank ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">No. Rekening</td><td class="text-white">: ${user.nomor_rekening ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">Pemilik Rekening</td><td class="text-white">: ${user.pemilik_rekening ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">NPWP</td><td class="text-white">: ${user.npwp ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">BPJS Kesehatan</td><td class="text-white">: ${user.bpjs_kesehatan ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">BPJS Ketenagakerjaan</td><td class="text-white">: ${user.bpjs_ketenagakerjaan ?? '-'}</td></tr>
                                        </table>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-bold text-red-400 mb-3 border-b border-zinc-700 pb-1"><i class="fas fa-heartbeat mr-1.5"></i>Kontak Darurat</h4>
                                        <table class="w-full text-xs space-y-2">
                                            <tr><td class="text-gray-400 py-1 w-28">Nama Kontak</td><td class="text-white">: ${user.kontak_darurat_nama ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">No. Telepon / HP</td><td class="text-white">: ${user.kontak_darurat_nomor ?? '-'}</td></tr>
                                            <tr><td class="text-gray-400 py-1">Hubungan Relasi</td><td class="text-white">: ${user.kontak_darurat_hubungan ?? '-'}</td></tr>
                                        </table>
                                    </div>

                                    <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                                        <div>
                                            <h4 class="text-xs font-bold text-gray-300 mb-2 uppercase tracking-wider">Riwayat Pendidikan</h4>
                                            ${pendHTML}
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-gray-300 mb-2 uppercase tracking-wider">Pengalaman Kerja</h4>
                                            ${pekjHTML}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        document.getElementById('detail-modal-body').innerHTML = bodyHTML;
                    } catch (error) {
                        modalBody.innerHTML = '<div class="flex justify-center items-center h-48 text-red-500 font-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Gagal memuat profil</div>';
                    }
                });
            });
        });
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 9999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #6b7280; }
    </style>
    @endpush
</x-layout-admin>