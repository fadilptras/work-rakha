<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Judul halaman dinamis dari Controller --}}
    <title>{{ $pageTitle ?? 'Admin Dashboard' }} - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <style>
        :root { font-family: 'Inter', sans-serif; }
        .modal { transition: opacity 0.25s ease; }
        /* Scrollbar kustom untuk modal */
        .modal-content::-webkit-scrollbar { width: 8px; }
        .modal-content::-webkit-scrollbar-track { background: #f1f1f1; }
        .modal-content::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        .modal-content::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>
<body class="bg-slate-100 font-sans">

<div class="flex h-screen bg-slate-100">

    <aside class="w-64 flex-shrink-0 bg-slate-800 text-white flex flex-col">
        <div class="h-20 flex items-center justify-center bg-slate-900">
            <h2 class="text-2xl font-bold">Admin Panel</h2>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            {{-- Tautan navigasi dengan status aktif dinamis --}}
            <a href="{{ route('admin.employees.index') }}" class="flex items-center px-4 py-2.5 font-semibold rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.employees.index') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197M15 21a6 6 0 00-9-5.197"></path></svg>
                Kelola Karyawan
            </a>
            <a href="{{ route('admin.admins.index') }}" class="flex items-center px-4 py-2.5 font-semibold rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.admins.index') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Kelola Admin
            </a>
        </nav>
        <div class="p-4 border-t border-slate-700">
             <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center px-4 py-2.5 font-semibold text-red-400 rounded-lg transition-colors duration-200 hover:bg-red-900/50 hover:text-white">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H3"></path></svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        <div class="p-6 md:p-8 flex-1 overflow-y-auto">
            
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-slate-800">{{ $pageTitle }}</h1>
                <button id="open-add-modal-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Tambah Akun
                </button>
            </div>
            
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md relative mb-4 shadow-sm" role="alert">
                    <p class="font-bold">Sukses!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
        
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md relative mb-4 shadow-sm" role="alert">
                    <strong class="font-bold">Oops! Terjadi kesalahan.</strong>
                    <ul class="list-disc ml-5 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="border-b-2 border-slate-200 bg-slate-50 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                <th class="px-5 py-3">Pengguna</th>
                                <th class="px-5 py-3">Jabatan</th>
                                <th class="px-5 py-3">Role</th>
                                <th class="px-5 py-3">Tgl Bergabung</th>
                                <th class="px-5 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr class="border-b border-slate-200 hover:bg-slate-50">
                                <td class="px-5 py-4 text-sm">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-10 h-10">
                                            <img class="w-full h-full rounded-full object-cover" 
                                                 src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=random&color=fff' }}" 
                                                 alt="Foto profil {{ $user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-slate-900 whitespace-no-wrap font-semibold">{{ $user->name }}</p>
                                            <p class="text-slate-600 whitespace-no-wrap">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    <p class="text-slate-900 whitespace-no-wrap">{{ $user->jabatan ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                     @if($user->role == 'admin')
                                        <span class="relative inline-block px-3 py-1 font-semibold text-blue-900 leading-tight">
                                            <span aria-hidden class="absolute inset-0 bg-blue-200 opacity-50 rounded-full"></span>
                                            <span class="relative">Admin</span>
                                        </span>
                                    @else
                                        <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                            <span aria-hidden class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                            <span class="relative">User</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    <p class="text-slate-900 whitespace-no-wrap">
                                        {{ $user->tanggal_bergabung ? \Carbon\Carbon::parse($user->tanggal_bergabung)->isoFormat('DD MMMM YYYY') : '-' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-sm text-center">
                                    <div class="flex items-center justify-center gap-4">
                                        <button class="open-edit-modal-btn text-yellow-600 hover:text-yellow-900 transition-colors duration-200" data-user='{{ $user->toJson() }}' title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                                        </button>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-slate-500">Tidak ada data akun ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 <div class="px-5 py-4 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
                     {{ $users->links() }}
                 </div>
            </div>

        </div>
    </main>
</div>

<div id="modal-container">
    {{-- Modal Tambah --}}
    <div id="add-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-full">
            <div class="p-6 border-b">
                <h3 class="text-xl font-bold text-slate-800">Tambah Akun Baru</h3>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data" class="modal-content overflow-y-auto" style="max-height: 80vh;">
                <div class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="add-name" class="block text-slate-700 text-sm font-bold mb-2">Nama</label>
                        <input type="text" name="name" id="add-name" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                    </div>
                    <div>
                        <label for="add-email" class="block text-slate-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" name="email" id="add-email" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                    </div>
                    <div>
                        <label for="add-jabatan" class="block text-slate-700 text-sm font-bold mb-2">Jabatan</label>
                        <input type="text" name="jabatan" id="add-jabatan" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label for="add-tanggal_bergabung" class="block text-slate-700 text-sm font-bold mb-2">Tanggal Bergabung</label>
                        <input type="date" name="tanggal_bergabung" id="add-tanggal_bergabung" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label for="add-profile_picture" class="block text-slate-700 text-sm font-bold mb-2">Foto Profil</label>
                        <img id="add-image-preview" src="" alt="Image Preview" class="w-20 h-20 rounded-full mb-2 object-cover hidden">
                        <input type="file" name="profile_picture" id="add-profile_picture" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700" accept="image/*">
                    </div>
                    <div>
                        <label for="add-role" class="block text-slate-700 text-sm font-bold mb-2">Role</label>
                        <select name="role" id="add-role" class="shadow-sm border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                            <option value="user" {{ $defaultRole === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ $defaultRole === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div>
                        <label for="add-password" class="block text-slate-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" name="password" id="add-password" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                    </div>
                </div>
                <div class="flex justify-end p-6 bg-slate-50 rounded-b-lg gap-2">
                    <button type="button" class="close-modal-btn bg-slate-200 hover:bg-slate-300 text-slate-800 py-2 px-4 rounded-lg font-semibold">Batal</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    {{-- Modal Edit --}}
    <div id="edit-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-full">
            <div class="p-6 border-b">
                 <h3 class="text-xl font-bold text-slate-800">Edit Akun</h3>
            </div>
            <form id="edit-form" method="POST" enctype="multipart/form-data" class="modal-content overflow-y-auto" style="max-height: 80vh;">
                <div class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit-name" class="block text-slate-700 text-sm font-bold mb-2">Nama</label>
                        <input type="text" name="name" id="edit-name" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                    </div>
                    <div>
                        <label for="edit-email" class="block text-slate-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" name="email" id="edit-email" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                    </div>
                    <div>
                        <label for="edit-jabatan" class="block text-slate-700 text-sm font-bold mb-2">Jabatan</label>
                        <input type="text" name="jabatan" id="edit-jabatan" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label for="edit-tanggal_bergabung" class="block text-slate-700 text-sm font-bold mb-2">Tanggal Bergabung</label>
                        <input type="date" name="tanggal_bergabung" id="edit-tanggal_bergabung" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label for="edit-profile_picture" class="block text-slate-700 text-sm font-bold mb-2">Ganti Foto Profil (Opsional)</label>
                        <img id="edit-image-preview" src="" alt="Image Preview" class="w-20 h-20 rounded-full mb-2 object-cover hidden">
                        <input type="file" name="profile_picture" id="edit-profile_picture" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700" accept="image/*">
                    </div>
                    <div>
                        <label for="edit-role" class="block text-slate-700 text-sm font-bold mb-2">Role</label>
                        <select name="role" id="edit-role" class="shadow-sm border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit-password" class="block text-slate-700 text-sm font-bold mb-2">Password Baru (Opsional)</label>
                        <input type="password" name="password" id="edit-password" class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400" placeholder="Kosongkan jika tidak diubah">
                    </div>
                </div>
                 <div class="flex justify-end p-6 bg-slate-50 rounded-b-lg gap-2">
                    <button type="button" class="close-modal-btn bg-slate-200 hover:bg-slate-300 text-slate-800 py-2 px-4 rounded-lg font-semibold">Batal</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg font-semibold">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addModal = document.getElementById('add-modal');
        const editModal = document.getElementById('edit-modal');
        
        // Logika untuk membuka modal
        document.getElementById('open-add-modal-btn').addEventListener('click', () => {
            addModal.classList.remove('hidden');
        });

        document.querySelectorAll('.open-edit-modal-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const user = JSON.parse(btn.dataset.user);
                const form = document.getElementById('edit-form');
                
                form.action = `{{ url('admin/users') }}/${user.id}`;
                
                form.querySelector('#edit-name').value = user.name;
                form.querySelector('#edit-email').value = user.email;
                form.querySelector('#edit-role').value = user.role;
                form.querySelector('#edit-jabatan').value = user.jabatan || '';
                
                if (user.tanggal_bergabung) {
                    form.querySelector('#edit-tanggal_bergabung').value = user.tanggal_bergabung.split('T')[0];
                } else {
                    form.querySelector('#edit-tanggal_bergabung').value = '';
                }
                
                const imagePreview = form.querySelector('#edit-image-preview');
                if (user.profile_picture) {
                    imagePreview.src = `{{ asset('storage') }}/${user.profile_picture}`;
                    imagePreview.classList.remove('hidden');
                } else {
                    imagePreview.src = '';
                    imagePreview.classList.add('hidden');
                }
                
                editModal.classList.remove('hidden');
            });
        });

        // Logika untuk menutup semua modal
        function closeModal() {
            document.querySelector('#add-modal form').reset();
            document.getElementById('add-image-preview').classList.add('hidden');
            document.querySelector('#edit-modal form').reset();
            document.getElementById('edit-image-preview').classList.add('hidden');
            
            addModal.classList.add('hidden');
            editModal.classList.add('hidden');
        }

        document.querySelectorAll('.close-modal-btn').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        window.addEventListener('click', (event) => {
            if (event.target == addModal || event.target == editModal) {
                closeModal();
            }
        });
        
        // Logika untuk pratinjau gambar saat di-upload
        function setupImagePreview(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            input.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        setupImagePreview('add-profile_picture', 'add-image-preview');
        setupImagePreview('edit-profile_picture', 'edit-image-preview');
    });
</script>

</body>
</html>