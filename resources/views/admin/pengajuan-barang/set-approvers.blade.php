<x-layout-admin>
    <x-slot:title>{{ $title }}</x-slot:title>
    
    <div>
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $title }}</h1>
                <p class="text-sm text-zinc-400 mt-1">Mengatur urutan persetujuan (Approver 1 hingga 4) untuk pengajuan barang.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-emerald-500/10 text-emerald-400 rounded-lg border border-emerald-500/20">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.pengajuan_barang.set_approvers.save') }}" method="POST">
            @csrf
            <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-700">
                        <thead class="bg-zinc-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">Nama Karyawan</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">APPROVER BARANG 1</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">APPROVER BARANG 2</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">APPROVER BARANG 3</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">ADMIN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-700">
                            @forelse ($employees as $employee)
                                <tr class="hover:bg-zinc-700/50 approver-row">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">{{ $employee->name }}</div>
                                        <div class="text-xs text-zinc-400">{{ $employee->divisi ?? 'Belum ada divisi' }}</div>
                                    </td>
                                    
                                    {{-- Kolom Approver dengan Ikon Kustom --}}
                                    @for ($i = 1; $i <= 4; $i++)
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="relative w-full min-w-[150px] max-w-[180px]">
                                            {{-- Ikon panah manual agar tidak mepet --}}
                                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down text-[9px] text-zinc-400"></i>
                                            </div>
                                            {{-- Dropdown --}}
                                            <select name="approver_barang_{{ $i }}[{{ $employee->id }}]" 
                                                    class="w-full appearance-none p-2 pr-8 bg-zinc-700 border border-zinc-600 rounded-lg text-xs text-white focus:ring-sky-500 focus:border-sky-500 approver-select">
                                                <option value="">-- Tidak Ada --</option>
                                                @foreach ($approvers as $approver)
                                                    <option value="{{ $approver->id }}" @selected($employee->{"approver_barang_{$i}_id"} == $approver->id)>
                                                        {{ $approver->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    @endfor
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-zinc-500">Belum ada data karyawan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateApproverOptions(row) {
                const selects = row.querySelectorAll('.approver-select');
                const selectedValues = [];
                selects.forEach(select => { if (select.value !== "") selectedValues.push(select.value); });

                selects.forEach(currentSelect => {
                    const currentValue = currentSelect.value;
                    currentSelect.querySelectorAll('option').forEach(option => {
                        if (option.value === "") { option.disabled = false; return; }
                        const isSelectedElsewhere = selectedValues.includes(option.value) && option.value !== currentValue;
                        option.disabled = isSelectedElsewhere;
                    });
                });
            }

            const allRows = document.querySelectorAll('.approver-row');
            allRows.forEach(row => updateApproverOptions(row));

            document.querySelectorAll('.approver-select').forEach(select => {
                select.addEventListener('change', function(e) {
                    const row = e.target.closest('.approver-row');
                    if (row) updateApproverOptions(row);
                });
            });
        });
    </script>
    @endpush
</x-layout-admin>