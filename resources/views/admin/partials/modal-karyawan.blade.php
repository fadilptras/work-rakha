{{-- modal tambah karyawan --}}
<div id="add-modal" class="modal fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-zinc-800 rounded-lg w-full max-w-4xl p-8 shadow-lg border border-zinc-700">
        <h2 class="text-xl font-bold mb-6 text-white">Formulir Tambah Karyawan Baru</h2>
        
        <form id="add-form" action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="role" value="user">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <div class="space-y-6">
                    <div>
                        <label for="add-name" class="block text-sm font-medium text-zinc-300">Nama Lengkap</label>
                        <input type="text" id="add-name" name="name"
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white" required>
                    </div>
                    <div>
                        <label for="add-email" class="block text-sm font-medium text-zinc-300">Alamat Email</label>
                        <input type="email" id="add-email" name="email"
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white" required>
                    </div>
                    <div>
                        <label for="add-password" class="block text-sm font-medium text-zinc-300">Password</label>
                        <div class="relative mt-1">
                            
                            {{-- Atribut validasi ditambahkan --}}
                            <input type="password" id="add-password" name="password"
                                class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 pr-10 text-white"
                                required minlength="8" title="Password minimal 8 karakter.">
                            <button type="button" class="toggle-password-btn absolute inset-y-0 right-0 px-3 flex items-center text-zinc-400 hover:text-zinc-200">
                                <i class="fas fa-eye"></i>
                                <i class="fas fa-eye-slash hidden"></i>
                            </button>
                        </div>
                        {{-- elemen untuk notif error --}}
                        <p id="add-password-error" class="hidden text-red-400 text-sm mt-1"></p>
                    </div>
                </div>
                {{-- Kolom Kanan --}}
                <div class="space-y-6">
                    <div>
                        <label for="add-jabatan" class="block text-sm font-medium text-zinc-300">Jabatan</label>
                        <input type="text" id="add-jabatan" name="jabatan"
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label for="add-divisi-select" class="block text-sm font-medium text-zinc-300">Divisi</label>
                        <select id="add-divisi-select" name="divisi"
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white">
                            <option value="Top Management">Top Management (Direktur / CEO)</option>
                            <option value="Finance dan Gudang">Finance dan Gudang</option>
                            <option value="Marketing dan Operasional">Marketing dan Operasional</option>
                            <option value="Human Resource Development">Human Resource Development</option>
                            <option value="IT dan Administrasi">IT dan Administrasi</option>
                            <option value="lainnya">Lainnya ...</option>
                        </select>
                        <div id="add-divisi-input-container" class="hidden mt-1">
                            <div class="flex items-center gap-2">
                                <input type="text" id="add-divisi-input" name="divisi-disabled"
                                    class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white"
                                    placeholder="Ketik nama divisi baru..." disabled>
                                <button type="button" id="add-divisi-cancel-btn"
                                    class="bg-zinc-600 hover:bg-zinc-500 text-white px-3 py-2 rounded-lg text-sm">Batal</button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="add-tanggal_bergabung" class="block text-sm font-medium text-zinc-300">Tanggal Bergabung</label>
                        <input type="date" id="add-tanggal_bergabung" name="tanggal_bergabung"
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-zinc-700">
                <button type="button" class="close-modal bg-zinc-600 hover:bg-zinc-500 text-white px-4 py-2 rounded-lg">Batal</button>
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Simpan Karyawan</button>
            </div>
        </form>
    </div>
</div>

{{-- modal edit karyawan --}}
<div id="edit-modal" class="modal fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-zinc-800 rounded-lg w-full max-w-4xl p-8 shadow-lg border border-zinc-700">
        <h2 class="text-xl font-bold mb-6 text-white">Edit Data Karyawan</h2>
        <form id="edit-form" action="{{ route('admin.employees.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" id="edit-user-id">
            <input type="hidden" name="role" value="user">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                {{-- Kolom Kiri --}}
                <div class="space-y-6">
                    <div>
                        <label for="edit-name" class="block text-sm font-medium text-zinc-300">Nama Lengkap</label>
                        <input type="text" id="edit-name" name="name"
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white" required>
                    </div>
                    <div>
                        <label for="edit-email" class="block text-sm font-medium text-zinc-300">Alamat Email</label>
                        <input type="email" id="edit-email" name="email"
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white" required>
                    </div>
                    <div>
                        <label for="edit-password" class="block text-sm font-medium text-zinc-300">Password Baru (Opsional)</label>
                        <div class="relative mt-1">
                            {{-- Atribut validasi ditambahkan --}}
                            <input type="password" id="edit-password" name="password"
                                class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 pr-10 text-white"
                                placeholder="Kosongkan jika tidak diubah" minlength="8" title="Jika diisi, minimal 8 karakter.">
                            <button type="button" class="toggle-password-btn absolute inset-y-0 right-0 px-3 flex items-center text-zinc-400 hover:text-zinc-200">
                                <i class="fas fa-eye"></i>
                                <i class="fas fa-eye-slash hidden"></i>
                            </button>
                        </div>
                        {{-- Elemen untuk notifikasi error --}}
                        <p id="edit-password-error" class="hidden text-red-400 text-sm mt-1"></p>
                    </div>
                </div>
                {{-- Kolom Kanan --}}
                <div class="space-y-6">
                    <div>
                        <label for="edit-jabatan" class="block text-sm font-medium text-zinc-300">Jabatan</label>
                        <input type="text" id="edit-jabatan" name="jabatan"
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white">
                    </div>
                    <div>
                        <label for="edit-divisi-select" class="block text-sm font-medium text-zinc-300">Divisi</label>
                        <select id="edit-divisi-select" name="divisi"
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white">
                            <option value="Top Management">Top Management (Direktur / CEO)</option>
                            <option value="Finance dan Gudang">Finance dan Gudang</option>
                            <option value="Marketing dan Operasional">Marketing dan Operasional</option>
                            <option value="Human Resource Development">Human Resource Development</option>
                            <option value="IT dan Administrasi">IT dan Administrasi</option>
                            <option value="lainnya">Lainnya ...</option>
                        </select>
                        <div id="edit-divisi-input-container" class="hidden mt-1">
                            <div class="flex items-center gap-2">
                                <input type="text" id="edit-divisi-input" name="divisi-disabled"
                                    class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white"
                                    placeholder="Ketik nama divisi..." disabled>
                                <button type="button" id="edit-divisi-cancel-btn"
                                    class="bg-zinc-600 hover:bg-zinc-500 text-white px-3 py-2 rounded-lg text-sm">Batal</button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="edit-tanggal_bergabung" class="block text-sm font-medium text-zinc-300">Tanggal Bergabung</label>
                        <input type="date" id="edit-tanggal_bergabung" name="tanggal_bergabung"
                            class="mt-1 w-full bg-zinc-700 border border-zinc-600 rounded-lg px-3 py-2 text-white">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-zinc-700">
                <button type="button" class="close-modal bg-zinc-600 hover:bg-zinc-500 text-white px-4 py-2 rounded-lg">Batal</button>
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Update Data</button>
            </div>
        </form>
    </div>
</div>