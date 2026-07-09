<x-layout-admin>
    {{-- Mengatur judul halaman --}}
    <x-slot:title>Kelola Admin</x-slot:title>

    {{-- Header Halaman --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Manajemen Akun Admin</h1>
        <button id="open-add-modal-btn" 
            class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
            <i class="fas fa-plus mr-2"></i> Tambah Admin
        </button>
    </div>

    {{-- Notifikasi Sukses atau Error --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabel untuk Menampilkan Data Admin --}}
    <div class="bg-zinc-800 rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full text-zinc-300">
            <thead class="bg-zinc-700 text-left text-xs font-semibold uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3">Admin</th>
                    <th class="px-5 py-3">Tanggal Dibuat</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-700">
                @forelse($users as $user)
                <tr class="hover:bg-zinc-700/50">
                    <td class="px-5 py-4">
                        <div class="flex items-center">
                            <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=0891b2&color=f0f9ff' }}"
                                 alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover mr-4">
                            <div>
                                <p class="font-semibold text-white">{{ $user->name }}</p>
                                <p class="text-sm text-zinc-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex justify-center gap-4">
                            <button class="open-edit-modal-btn text-amber-400 hover:text-amber-300 transition-colors" data-user='@json($user)' title="Edit">
                                <i class="fas fa-edit fa-lg"></i>
                            </button>
                            <form action="{{ route('admin.admins.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus admin ini?')">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-400 transition-colors" title="Hapus">
                                    <i class="fas fa-trash fa-lg"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-10 text-zinc-400">Belum ada data admin.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="add-modal" class="modal fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-zinc-800 rounded-lg w-full max-w-lg p-6 shadow-lg border border-zinc-700">
            <h2 class="text-xl font-bold mb-6 text-white">Formulir Tambah Admin Baru</h2>
            <form action="{{ route('admin.admins.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="role" value="admin">
                
                <div class="space-y-4">
                    <div>
                        <label for="add-name" class="block text-sm font-medium text-zinc-300">Nama Lengkap</label>
                        <input type="text" id="add-name" name="name" class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white" required>
                    </div>
                    <div>
                        <label for="add-email" class="block text-sm font-medium text-zinc-300">Alamat Email</label>
                        <input type="email" id="add-email" name="email" class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white" required>
                    </div>
                    <div>
                        <label for="add-password" class="block text-sm font-medium text-zinc-300">Password</label>
                        <input type="password" id="add-password" name="password" class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white" required>
                    </div>
                     <div>
                        <label for="add-profile_picture" class="block text-sm font-medium text-zinc-300">Foto Profil (Opsional)</label>
                        <input type="file" id="add-profile_picture" name="profile_picture" class="mt-1 w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-500/10 file:text-sky-300 hover:file:bg-sky-500/20">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" class="close-modal bg-zinc-600 hover:bg-zinc-500 text-white px-4 py-2 rounded-lg">Batal</button>
                    <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg">Simpan Admin</button>
                </div>
            </form>
        </div>
    </div>

    <div id="edit-modal" class="modal fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-zinc-800 rounded-lg w-full max-w-lg p-6 shadow-lg border border-zinc-700">
            <h2 class="text-xl font-bold mb-6 text-white">Edit Data Admin</h2>
            <form id="edit-form" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_id" id="edit-user-id">
                <input type="hidden" name="role" value="admin">

                <div class="space-y-4">
                    <div>
                        <label for="edit-name" class="block text-sm font-medium text-zinc-300">Nama Lengkap</label>
                        <input type="text" id="edit-name" name="name" class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white" required>
                    </div>
                    <div>
                        <label for="edit-email" class="block text-sm font-medium text-zinc-300">Alamat Email</label>
                        <input type="email" id="edit-email" name="email" class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white" required>
                    </div>
                    <div>
                        <label for="edit-password" class="block text-sm font-medium text-zinc-300">Password Baru (Opsional)</label>
                        <input type="password" id="edit-password" name="password" class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white" placeholder="Kosongkan jika tidak diubah">
                    </div>
                     <div>
                        <label for="edit-profile_picture" class="block text-sm font-medium text-zinc-300">Ganti Foto Profil (Opsional)</label>
                        <input type="file" id="edit-profile_picture" name="profile_picture" class="mt-1 w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-500/10 file:text-sky-300 hover:file:bg-sky-500/20">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-8">
                    <button type="button" class="close-modal bg-zinc-600 hover:bg-zinc-500 text-white px-4 py-2 rounded-lg">Batal</button>
                    <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg">Update Data</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Script untuk mengelola Modal (disederhanakan untuk admin) --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addModal = document.getElementById('add-modal');
            const editModal = document.getElementById('edit-modal');
            
            document.getElementById('open-add-modal-btn')?.addEventListener('click', () => {
                addModal?.classList.remove('hidden');
            });

            document.querySelectorAll('.open-edit-modal-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const user = JSON.parse(btn.getAttribute('data-user'));
                    
                    if (editModal) {
                        const form = editModal.querySelector('#edit-form');
                        // Ubah baris ini untuk rute baru
                        form.action = `{{ route('admin.admins.update') }}`;

                        // Tambahkan baris ini untuk mengisi ID pengguna
                        form.querySelector('#edit-user-id').value = user.id;
                        form.querySelector('#edit-email').value = user.email;
                        form.querySelector('#edit-password').value = '';
                        form.querySelector('#edit-profile_picture').value = '';
                        
                        editModal.classList.remove('hidden');
                    }
                });
            });

            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', () => {
                    btn.closest('.modal')?.classList.add('hidden');
                });
            });
        });
    </script>
    @endpush
</x-layout-admin>