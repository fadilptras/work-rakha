<x-layout-admin>
    <x-slot:title>{{ $title }}</x-slot:title>

    @push('styles')
    <style>
        .evaluator-select option:disabled { color: #71717a; }
    </style>
    @endpush

    <div>
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $title }}</h1>
                <p class="text-sm text-zinc-400 mt-1">Tentukan siapa yang menilai KPI (Evaluator) dan siapa penyetuju (Approver 1 & 2) untuk setiap karyawan pada periode penilaian.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.kpi.evaluators.save') }}" method="POST">
            @csrf
            <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-700">
                        <thead class="bg-zinc-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">Karyawan</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">EVALUATOR (PENILAI)</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">APPROVER 1</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-zinc-400 uppercase tracking-wider">APPROVER 2</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-700">
                            @forelse ($employees as $employee)
                                <tr class="hover:bg-zinc-700/50 evaluator-row" data-employee-id="{{ $employee->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <div class="text-sm font-medium text-white">{{ $employee->name }}</div>
                                        <div class="text-xs text-zinc-400">{{ $employee->divisi ?? 'Belum ada divisi' }}<br>{{ $employee->jabatan ?? '' }}</div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap align-top">
                                        <div class="relative w-full min-w-[180px] max-w-[220px]">
                                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down text-[9px] text-zinc-400"></i>
                                            </div>
                                            <select name="evaluator[{{ $employee->id }}]"
                                                    class="w-full appearance-none p-2 pr-8 bg-zinc-700 border border-zinc-600 rounded-lg text-xs text-white focus:ring-sky-500 focus:border-sky-500 evaluator-select">
                                                <option value="">-- Pilih Penilai --</option>
                                                @foreach ($evaluators as $evaluator)
                                                    <option value="{{ $evaluator->id }}" @selected($employee->kpi_evaluator_id == $evaluator->id && $employee->kpi_evaluator_id != null)>
                                                        {{ $evaluator->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap align-top">
                                        <div class="relative w-full min-w-[180px] max-w-[220px]">
                                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down text-[9px] text-zinc-400"></i>
                                            </div>
                                            <select name="approver_1[{{ $employee->id }}]"
                                                    class="w-full appearance-none p-2 pr-8 bg-zinc-700 border border-zinc-600 rounded-lg text-xs text-white focus:ring-sky-500 focus:border-sky-500 evaluator-select">
                                                <option value="">-- Pilih Approver --</option>
                                                @foreach ($evaluators as $evaluator)
                                                    <option value="{{ $evaluator->id }}" @selected($employee->kpi_approver_1_id == $evaluator->id && $employee->kpi_approver_1_id != null)>
                                                        {{ $evaluator->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap align-top">
                                        <div class="relative w-full min-w-[180px] max-w-[220px]">
                                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down text-[9px] text-zinc-400"></i>
                                            </div>
                                            <select name="approver_2[{{ $employee->id }}]"
                                                    class="w-full appearance-none p-2 pr-8 bg-zinc-700 border border-zinc-600 rounded-lg text-xs text-white focus:ring-sky-500 focus:border-sky-500 evaluator-select">
                                                <option value="">-- Pilih Approver --</option>
                                                @foreach ($evaluators as $evaluator)
                                                    <option value="{{ $evaluator->id }}" @selected($employee->kpi_approver_2_id == $evaluator->id && $employee->kpi_approver_2_id != null)>
                                                        {{ $evaluator->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-zinc-500">Belum ada data karyawan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateOptions(row) {
                const selects = row.querySelectorAll('.evaluator-select');
                const selectedValues = [];
                selects.forEach(select => { if (select.value !== "") selectedValues.push(select.value); });
                const selfId = row.dataset.employeeId || "";

                selects.forEach(currentSelect => {
                    const currentValue = currentSelect.value;
                    currentSelect.querySelectorAll('option').forEach(option => {
                        if (option.value === "") { option.disabled = false; return; }
                        // Cegah menilai diri sendiri
                        if (selfId !== "" && option.value === selfId) { option.disabled = true; return; }
                        const isSelectedElsewhere = selectedValues.includes(option.value) && option.value !== currentValue;
                        option.disabled = isSelectedElsewhere;
                    });
                });
            }

            const allRows = document.querySelectorAll('.evaluator-row');
            allRows.forEach(row => updateOptions(row));

            document.querySelectorAll('.evaluator-select').forEach(select => {
                select.addEventListener('change', function(e) {
                    const row = e.target.closest('.evaluator-row');
                    if (row) updateOptions(row);
                });
            });
        });
    </script>
    @endpush
</x-layout-admin>
