<x-layout-admin>
    <x-slot:title>Kelola Admin</x-slot:title>

    {{-- Header Halaman --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Manajemen Akun Admin</h1>
        <button id="open-add-modal-btn" 
            class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
            <i class="fas fa-plus mr-2"></i> Tambah Admin
        </button>
    </div>

    {{-- Notifikasi --}}
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
    @if ($errors->any())
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Data Admin --}}
    <div class="bg-zinc-800 rounded-lg shadow-lg overflow-hidden border border-zinc-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-700">
                <thead class="bg-zinc-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Profil</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tanggal Dibuat</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-zinc-800 divide-y divide-zinc-700">
                    @forelse ($users as $user)
                        <tr class="hover:bg-zinc-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($user->profile_picture)
                                            <img class="h-10 w-10 rounded-full object-cover border border-zinc-600" src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-zinc-600 flex items-center justify-center text-white font-bold border border-zinc-500">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-400">Id Admin: #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300"><i class="fas fa-envelope mr-1 text-gray-500"></i> {{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300"><i class="fas fa-calendar-alt mr-1 text-gray-500"></i> {{ $user->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button class="open-edit-modal-btn text-amber-400 hover:text-amber-300 mr-3" data-user="{{ json_encode($user) }}" title="Edit Admin">
                                    <i class="fas fa-edit fa-lg"></i>
                                </button>

                                <form action="{{ route('admin.admins.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300" title="Hapus Admin">
                                        <i class="fas fa-trash-alt fa-lg"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                                <i class="fas fa-users-slash text-4xl text-gray-500 mb-3"></i>
                                <p class="text-lg">Belum ada data admin.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL TAMBAH ADMIN                         --}}
    {{-- ========================================== --}}
    <div id="add-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center hidden z-50 transition-opacity duration-300 opacity-0">
        <div class="bg-zinc-800 rounded-xl shadow-2xl w-full max-w-lg transform scale-95 transition-transform duration-300 max-h-[90vh] flex flex-col border border-zinc-700">
            <div class="flex justify-between items-center p-6 border-b border-zinc-700">
                <h3 class="text-xl font-bold text-white flex items-center">
                    Tambah Admin Baru
                </h3>
                <button type="button" class="close-modal text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar">
                <form id="add-form" action="{{ route('admin.admins.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="role" value="admin">

                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Masukkan nama lengkap">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="nama@email.com">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" id="password" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Masukkan password">
                        </div>

                        <div>
                            <label for="profile_picture" class="block text-sm font-medium text-gray-300 mb-1">Foto Profil (Opsional)</label>
                            <input type="file" name="profile_picture" id="profile_picture" class="w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-500/10 file:text-sky-300 hover:file:bg-sky-500/20">
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
    {{-- MODAL EDIT ADMIN                           --}}
    {{-- ========================================== --}}
    <div id="edit-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center hidden z-50 transition-opacity duration-300 opacity-0">
        <div class="bg-zinc-800 rounded-xl shadow-2xl w-full max-w-lg transform scale-95 transition-transform duration-300 max-h-[90vh] flex flex-col border border-zinc-700">
            <div class="flex justify-between items-center p-6 border-b border-zinc-700">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-user-edit text-amber-400 mr-3"></i> Edit Data Admin
                </h3>
                <button type="button" class="close-modal text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar">
                <form id="edit-form" method="POST" action="{{ route('admin.admins.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="user_id" id="edit-user-id">
                    <input type="hidden" name="role" value="admin">

                    <div class="space-y-4">
                        <div>
                            <label for="edit-name" class="block text-sm font-medium text-gray-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="edit-name" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                        </div>

                        <div>
                            <label for="edit-email" class="block text-sm font-medium text-gray-300 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="edit-email" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                        </div>

                        <div>
                            <label for="edit-password" class="block text-sm font-medium text-gray-300 mb-1">Password Baru (Opsional)</label>
                            <input type="password" name="password" id="edit-password" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" placeholder="Biarkan kosong jika tidak ingin mengubah password">
                        </div>

                        <div>
                            <label for="edit-profile_picture" class="block text-sm font-medium text-gray-300 mb-1">Ganti Foto Profil (Opsional)</label>
                            <input type="file" name="profile_picture" id="edit-profile_picture" class="w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-500/10 file:text-amber-300 hover:file:bg-amber-500/20">
                        </div>
                    </div>
                </form>
            </div>
            <div class="p-6 border-t border-zinc-700 bg-zinc-800 rounded-b-xl flex justify-end space-x-3">
                <button type="button" class="close-modal px-4 py-2 bg-zinc-600 text-white rounded-lg hover:bg-zinc-500 transition-colors">Batal</button>
                <button type="submit" form="edit-form" class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 shadow-md transition-colors font-semibold flex items-center">
                    <i class="fas fa-save mr-2"></i> Update Data
                </button>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
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
            const editModal = document.getElementById('edit-modal');
            
            document.getElementById('open-add-modal-btn')?.addEventListener('click', () => {
                toggleModal(addModal, true);
            });

            document.querySelectorAll('.open-edit-modal-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const user = JSON.parse(btn.getAttribute('data-user'));
                    
                    if (editModal) {
                        const form = document.getElementById('edit-form');
                        form.querySelector('#edit-user-id').value = user.id;
                        form.querySelector('#edit-name').value = user.name;
                        form.querySelector('#edit-email').value = user.email;
                        form.querySelector('#edit-password').value = '';
                        form.querySelector('#edit-profile_picture').value = '';
                        
                        toggleModal(editModal, true);
                    }
                });
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