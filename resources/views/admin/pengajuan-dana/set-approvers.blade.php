<x-layout-admin>
    <x-slot:title>{{ $title }}</x-slot:title>
    
    <div>
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $title }}</h1>
                <p class="text-sm text-zinc-400 mt-1">Atur Approver 1, Approver 2, dan Manager Keuangan untuk setiap karyawan.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-emerald-500/10 text-emerald-400 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-500/10 text-red-400 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.pengajuan_dana.set_approvers.save') }}" method="POST">
            @csrf

            <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-700">
                        <thead class="bg-zinc-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">Nama Karyawan</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">Approver 1</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">Approver 2</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">Manager Keuangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-700">
                            @forelse ($employees as $employee)
                                {{-- [PERUBAHAN 1] Tambahkan class 'approver-row' --}}
                                <tr class="hover:bg-zinc-700/50 approver-row">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">{{ $employee->name }}</div>
                                        <div class="text-xs text-zinc-400">{{ $employee->divisi ?? 'Belum ada divisi' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{-- [PERUBAHAN 2] Tambahkan class 'approver-select' --}}
                                        <select name="approver_1[{{ $employee->id }}]" class="w-full max-w-xs p-2 bg-zinc-700 border border-zinc-600 rounded-lg text-sm text-white focus:ring-amber-500 focus:border-amber-500 approver-select">
                                            
                                            <option value="">-- Tidak Ada --</option>
                                            
                                            @foreach ($approvers as $approver)
                                                <option value="{{ $approver->id }}" @selected($employee->approver_1_id == $approver->id)>
                                                    {{ $approver->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('approver_1.' . $employee->id) <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{-- [PERUBAHAN 2] Tambahkan class 'approver-select' --}}
                                        <select name="approver_2[{{ $employee->id }}]" class="w-full max-w-xs p-2 bg-zinc-700 border border-zinc-600 rounded-lg text-sm text-white focus:ring-amber-500 focus:border-amber-500 approver-select">
                                            
                                            <option value="">-- Tidak Ada --</option>
                                            
                                            @foreach ($approvers as $approver)
                                                <option value="{{ $approver->id }}" @selected($employee->approver_2_id == $approver->id)>
                                                    {{ $approver->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                         @error('approver_2.' . $employee->id) <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{-- [PERUBAHAN 2] Tambahkan class 'approver-select' --}}
                                        <select name="manager_keuangan[{{ $employee->id }}]" class="w-full max-w-xs p-2 bg-zinc-700 border border-zinc-600 rounded-lg text-sm text-white focus:ring-amber-500 focus:border-amber-500 approver-select">
                                            
                                            <option value="">-- Tidak Ada --</option>
                                            
                                            @foreach ($financeManagers as $finance)
                                                <option value="{{ $finance->id }}" @selected($employee->manager_keuangan_id == $finance->id)>
                                                    {{ $finance->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                         @error('manager_keuangan.' . $employee->id) <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-zinc-500">
                                        Belum ada data karyawan (role: user).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>

    {{-- [PERUBAHAN 3] Tambahkan script jQuery (asumsi layout kamu sudah ada jQuery) --}}
    @push('scripts')
    <script>
        // Tunggu sampai semua HTML selesai dimuat
        document.addEventListener('DOMContentLoaded', function() {
            
            /**
             * Fungsi untuk mengupdate opsi dropdown dalam satu baris.
             * @param {HTMLElement} row - Elemen <tr> (baris)
             */
            function updateApproverOptions(row) {
                // 1. Temukan semua select di dalam baris ini
                const selects = row.querySelectorAll('.approver-select');
                
                // 2. Kumpulkan semua nilai yang TERPILIH (kecuali yang kosong "-- Tidak Ada --")
                const selectedValues = [];
                selects.forEach(select => {
                    if (select.value !== "") {
                        selectedValues.push(select.value);
                    }
                });

                // 3. Loop setiap select lagi untuk mengatur opsi 'disabled'
                selects.forEach(currentSelect => {
                    const currentValue = currentSelect.value; // Nilai select ini sendiri

                    // 4. Loop setiap <option> di dalam select ini
                    currentSelect.querySelectorAll('option').forEach(option => {
                        const optionValue = option.value;

                        // Jangan disable opsi "-- Tidak Ada --"
                        if (optionValue === "") {
                            option.disabled = false; // Pastikan -- Tidak Ada -- selalu bisa dipilih
                            return; // Lanjut ke opsi berikutnya
                        }

                        // 5. Cek: Apakah nilai opsi ini ada di daftar 'selectedValues'
                        //    DAN apakah nilai itu BUKAN nilai dari select ini sendiri?
                        const isSelectedElsewhere = selectedValues.includes(optionValue) && optionValue !== currentValue;

                        // 6. Set properti 'disabled'
                        option.disabled = isSelectedElsewhere;
                    });
                });
            }

            // --- EKSEKUSI ---

            // 1. Jalankan fungsi di atas untuk setiap baris saat halaman pertama kali dimuat
            //    Ini untuk mengatur state awal jika data sudah ada dari database
            const allRows = document.querySelectorAll('.approver-row');
            allRows.forEach(row => {
                updateApproverOptions(row);
            });

            // 2. Pasang 'event listener' setiap kali ada select yang berubah
            const allSelects = document.querySelectorAll('.approver-select');
            allSelects.forEach(select => {
                select.addEventListener('change', function(event) {
                    // Temukan <tr> terdekat (parent) dari select yang baru saja berubah
                    const currentRow = event.target.closest('.approver-row');
                    
                    // Panggil fungsi update untuk baris tersebut
                    if (currentRow) {
                        updateApproverOptions(currentRow);
                    }
                });
            });

        });
    </script>
    @endpush
</x-layout-admin>