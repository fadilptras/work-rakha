<x-layout-admin :title="'Daftar KPI Karyawan'">
    @push('styles')
    <style>
        /* == Background == */
        .mesh-bg { background-color: #ede9fe; }
    </style>
    @endpush
    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden p-6 md:p-8">
        {{-- Background Animations --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[-10%] left-[-5%] w-64 h-64 bg-blue-400 opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-[-10%] right-[-5%] w-80 h-80 bg-indigo-500 opacity-10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-6xl mx-auto flex-1 flex flex-col">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Evaluasi KPI Karyawan</h1>
                    <p class="text-slate-500 text-sm mt-1">Kelola dan nilai indikator kinerja utama karyawan.</p>
                </div>
                <div>
                    <form action="{{ route('admin.kpi.index') }}" method="GET" class="flex items-center gap-2">
                        <label for="period" class="text-sm font-semibold text-slate-700">Periode:</label>
                        <select name="period" id="period" onchange="this.form.submit()" class="bg-white border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
                            @php
                                $currentYear = date('Y');
                                $periods = [];
                                for ($i = $currentYear - 1; $i <= $currentYear + 1; $i++) {
                                    $periods[] = "Semester 1 $i";
                                    $periods[] = "Semester 2 $i";
                                }
                            @endphp
                            @foreach($periods as $p)
                                <option value="{{ $p }}" {{ $period == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider font-semibold border-b border-slate-200">
                                <th class="px-6 py-4">Karyawan</th>
                                <th class="px-6 py-4">Divisi</th>
                                <th class="px-6 py-4 text-center">Status Terakhir</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($karyawans as $karyawan)
                                @php
                                    $eval = $evaluations->where('user_id', $karyawan->id)->first();
                                @endphp
                                <tr class="hover:bg-blue-50/30 transition duration-200 group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md">
                                                {{ substr($karyawan->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800">{{ $karyawan->name }}</div>
                                                <div class="text-xs text-slate-500">{{ $karyawan->jabatan }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                            {{ $karyawan->divisi }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($eval)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 shadow-sm">
                                                <i class="fas fa-check-circle mr-1.5"></i> {{ ucfirst($eval->status) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 shadow-sm">
                                                <i class="fas fa-clock mr-1.5"></i> Belum Dinilai
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                            <a href="{{ route('admin.kpi.evaluate', $karyawan->id) }}?period={{ urlencode($period) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-transform hover:-translate-y-0.5 shadow-md shadow-blue-500/30">
                                                <i class="fas fa-edit mr-2"></i> Nilai KPI
                                            </a>

                                        @if($eval)
                                            <a href="{{ route('kpi.exportPdf', $eval->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-lg transition-transform hover:-translate-y-0.5 shadow-md shadow-red-500/30">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if($karyawans->isEmpty())
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                        <i class="fas fa-users-slash text-4xl mb-3 opacity-30"></i>
                                        <p>Tidak ada data karyawan yang ditemukan.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layout-admin>
