<x-layout-users :title="'Evaluasi KPI Karyawan'">
    @push('styles')
    <style>
        /* == Background == */
        .mesh-bg { background-color: #ede9fe; }

        /* == Modern Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px 8px 8px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 9999px;
            color: #1e293b;
            font-size: 0.9rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
            width: fit-content;
        }
        .btn-back-modern:hover { 
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
            color: #1d4ed8;
        }
        .btn-back-modern .icon-circle {
            width: 32px; height: 32px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6;
            font-size: 0.85rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover .icon-circle {
            transform: translateX(-3px);
            background: #EFF6FF;
        }
    </style>
    @endpush
    <div class="flex flex-col flex-1 min-h-screen mesh-bg relative overflow-hidden p-4 sm:p-6 md:p-8">
        {{-- Background Animations --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 top-[-10%] left-[-10%] animate-blob"></div>
            <div class="absolute w-96 h-96 bg-cyan-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 top-[20%] right-[-10%] animate-blob animation-delay-2000"></div>
            <div class="absolute w-80 h-80 bg-sky-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 bottom-[-10%] left-[20%] animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-7xl w-full mx-auto relative z-10">
            
            <a href="{{ route('kpi.index') }}" class="btn-back-modern">
                <div class="icon-circle">
                    <i class="fas fa-arrow-left"></i>
                </div>
                Kembali
            </a>
            
            {{-- Header Card --}}
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6 border border-gray-100 transition-all duration-300 hover:shadow-2xl">
                <div class="bg-gradient-to-r from-blue-700 via-blue-600 to-sky-600 p-6 sm:p-8 text-white flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-full opacity-10 pointer-events-none" style="background-image: radial-gradient(circle, white 2px, transparent 2px); background-size: 20px 20px;"></div>
                    
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center border-2 border-white/50 shadow-inner">
                            <i class="fas fa-user-tie text-3xl sm:text-4xl text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-black tracking-tight drop-shadow-md">{{ $targetUser->name }}</h2>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-semibold tracking-wide shadow-sm border border-white/30 flex items-center gap-1.5"><i class="fas fa-briefcase"></i> {{ $targetUser->jabatan ?? '-' }}</span>
                                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-semibold tracking-wide shadow-sm border border-white/30 flex items-center gap-1.5"><i class="fas fa-sitemap"></i> {{ ucfirst($targetUser->divisi ?? '-') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-left md:text-right relative z-10">
                        <p class="text-blue-100 text-sm font-medium tracking-wide mb-1 uppercase">Periode Penilaian</p>
                        <p class="text-xl sm:text-2xl font-bold bg-white text-blue-700 px-4 py-1.5 rounded-lg inline-block shadow-md border-b-4 border-blue-200">
                            {{ $evaluation ? $evaluation->period : 'Juli 2026' }}
                        </p>
                        @if($evaluation && $evaluation->status == 'approved')
                            <div class="mt-3">
                                <span class="bg-emerald-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-md flex items-center gap-1.5 w-fit md:ml-auto">
                                    <i class="fas fa-check-circle"></i> Approved
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm mb-6 animate-pulse">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle mt-1 mr-3"></i>
                        <div>
                            <p class="font-bold">Mohon periksa kembali form Anda:</p>
                            <ul class="list-disc pl-5 mt-1 space-y-1 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Main Form --}}
            <form action="{{ route('kpi.storeEvaluate', $targetUser->id) }}" method="POST" id="kpi-form-umum" class="space-y-6">
                @csrf
                <input type="hidden" name="period" value="{{ $evaluation->period ?? 'July 2026' }}">
                
                {{-- KINERJA (60%) --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden group hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-slate-50 border-b border-gray-100 p-5 sm:p-6 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center shadow-md shadow-blue-500/30 transform group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-briefcase text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">1. PENILAIAN KINERJA (60%)</h3>
                            <p class="text-sm text-slate-500 mt-0.5">Penilaian atas hasil kerja berdasarkan kualitas, kuantitas, dan waktu.</p>
                        </div>
                    </div>
                    
                    <div class="p-5 sm:p-6">
                        @php $kinerjaIndicators = $groupedIndicators->get('kinerja', collect()); @endphp
                        
                        @if($kinerjaIndicators->isEmpty())
                            <div class="bg-slate-50 rounded-xl p-6 text-center border border-dashed border-slate-300">
                                <i class="fas fa-folder-open text-4xl text-slate-300 mb-3 block"></i>
                                <span class="text-slate-500 font-medium">Tidak ada indikator Kinerja.</span>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                                <table class="w-full text-left border-collapse min-w-[700px]">
                                    <thead>
                                        <tr class="bg-slate-100 text-slate-600 text-sm">
                                            <th class="p-3 font-semibold border-b border-slate-200 w-12 text-center">No</th>
                                            <th class="p-3 font-semibold border-b border-slate-200">Aspek Kinerja</th>
                                            <th class="p-3 font-semibold border-b border-slate-200 text-center w-40">Indeks (1-5)</th>
                                            <th class="p-3 font-semibold border-b border-slate-200 text-center w-24">Bobot</th>
                                            <th class="p-3 font-semibold border-b border-slate-200 text-center w-28">Nilai Akhir</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($kinerjaIndicators as $index => $indicator)
                                            @php
                                                $item = $evaluation ? $evaluation->items->where('kpi_indicator_id', $indicator->id)->first() : null;
                                                $resultIndex = $item ? $item->result_index : '';
                                                $finalScore = $item ? $item->final_score : '';
                                            @endphp
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="p-3 text-center text-slate-500 font-medium">{{ $index + 1 }}</td>
                                                <td class="p-3">
                                                    <div class="font-bold text-slate-800">{{ $indicator->name }}</div>
                                                    <div class="text-xs text-slate-500 mt-1">{{ $indicator->definition }}</div>
                                                </td>
                                                <td class="p-3">
                                                    <select name="result_indexes[{{ $indicator->id }}]" 
                                                            class="kpi-input-umum w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm font-medium text-slate-700 bg-white"
                                                            data-weight="{{ $indicator->weight_percentage }}"
                                                            data-group="kinerja"
                                                            {{ ($evaluation && $evaluation->status == 'approved') ? 'disabled' : 'required' }}>
                                                        <option value="" disabled selected>Pilih 1-5</option>
                                                        <option value="1" {{ $resultIndex == '1' ? 'selected' : '' }}>1 - Jauh di bawah harapan</option>
                                                        <option value="2" {{ $resultIndex == '2' ? 'selected' : '' }}>2 - Perlu peningkatan</option>
                                                        <option value="3" {{ $resultIndex == '3' ? 'selected' : '' }}>3 - Sesuai harapan</option>
                                                        <option value="4" {{ $resultIndex == '4' ? 'selected' : '' }}>4 - Melebihi harapan</option>
                                                        <option value="5" {{ $resultIndex == '5' ? 'selected' : '' }}>5 - Jauh melebihi harapan</option>
                                                    </select>
                                                </td>
                                                <td class="p-3 text-center">
                                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 font-bold text-xs border border-blue-100">
                                                        {{ (float)$indicator->weight_percentage }}%
                                                    </span>
                                                </td>
                                                <td class="p-3 text-center">
                                                    <input type="text" name="final_scores[{{ $indicator->id }}]" 
                                                        class="final-score-umum w-full bg-slate-50 border border-slate-200 rounded-md py-1.5 px-2 text-center text-sm font-bold text-slate-700" 
                                                        value="{{ $finalScore }}" readonly tabindex="-1">
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-blue-50/50 font-bold border-t-2 border-blue-100">
                                            <td colspan="4" class="p-3 text-right text-blue-900">Total Kinerja (100% dari 60%)</td>
                                            <td class="p-3 text-center text-blue-700" id="total-kinerja-display">0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- PERILAKU (20%) --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden group hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-slate-50 border-b border-gray-100 p-5 sm:p-6 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shadow-md shadow-emerald-500/30 transform group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-heart text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">2. PENILAIAN PERILAKU (20%)</h3>
                            <p class="text-sm text-slate-500 mt-0.5">Penilaian atas sikap, kompetensi, komitmen, dan kepedulian.</p>
                        </div>
                    </div>
                    
                    <div class="p-5 sm:p-6">
                        @php 
                            $perilakuGroups = [
                                'perilaku_terbaik' => 'Berusaha Meraih Yang Terbaik',
                                'perilaku_profesional' => 'Berperilaku Profesional',
                                'perilaku_peduli' => 'Bersikap Peduli'
                            ];
                        @endphp

                        @foreach($perilakuGroups as $catKey => $catName)
                            @php $indicators = $groupedIndicators->get($catKey, collect()); @endphp
                            
                            @if($indicators->isNotEmpty())
                                <h4 class="font-bold text-emerald-800 bg-emerald-50 py-2 px-4 rounded-lg mb-3 mt-4 first:mt-0">{{ $catName }}</h4>
                                <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm mb-6">
                                    <table class="w-full text-left border-collapse min-w-[700px]">
                                        <thead class="bg-slate-100 text-slate-600 text-sm">
                                            <tr>
                                                <th class="p-3 font-semibold border-b border-slate-200 w-12 text-center">No</th>
                                                <th class="p-3 font-semibold border-b border-slate-200">Aspek Perilaku</th>
                                                <th class="p-3 font-semibold border-b border-slate-200 text-center w-40">Indeks (1-4)</th>
                                                <th class="p-3 font-semibold border-b border-slate-200 text-center w-24">Bobot</th>
                                                <th class="p-3 font-semibold border-b border-slate-200 text-center w-28">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($indicators as $index => $indicator)
                                                @php
                                                    $item = $evaluation ? $evaluation->items->where('kpi_indicator_id', $indicator->id)->first() : null;
                                                    $resultIndex = $item ? $item->result_index : '';
                                                    $finalScore = $item ? $item->final_score : '';
                                                @endphp
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    <td class="p-3 text-center text-slate-500 font-medium">{{ $index + 1 }}</td>
                                                    <td class="p-3">
                                                        <div class="font-bold text-slate-800">{{ $indicator->name }}</div>
                                                        <div class="text-xs text-slate-500 mt-1">{{ $indicator->definition }}</div>
                                                    </td>
                                                    <td class="p-3">
                                                        <select name="result_indexes[{{ $indicator->id }}]" 
                                                                class="kpi-input-umum w-full border-gray-300 rounded-lg shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 text-sm font-medium text-slate-700 bg-white"
                                                                data-weight="{{ $indicator->weight_percentage }}"
                                                                data-group="perilaku"
                                                                {{ ($evaluation && $evaluation->status == 'approved') ? 'disabled' : 'required' }}>
                                                            <option value="" disabled selected>Pilih 1-4</option>
                                                            <option value="1" {{ $resultIndex == '1' ? 'selected' : '' }}>1 - Kurang</option>
                                                            <option value="2" {{ $resultIndex == '2' ? 'selected' : '' }}>2 - Cukup</option>
                                                            <option value="3" {{ $resultIndex == '3' ? 'selected' : '' }}>3 - Baik</option>
                                                            <option value="4" {{ $resultIndex == '4' ? 'selected' : '' }}>4 - Istimewa</option>
                                                        </select>
                                                    </td>
                                                    <td class="p-3 text-center">
                                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 font-bold text-xs border border-emerald-100">
                                                            {{ (float)$indicator->weight_percentage }}%
                                                        </span>
                                                    </td>
                                                    <td class="p-3 text-center">
                                                        <input type="text" name="final_scores[{{ $indicator->id }}]" 
                                                            class="final-score-umum w-full bg-slate-50 border border-slate-200 rounded-md py-1.5 px-2 text-center text-sm font-bold text-slate-700" 
                                                            value="{{ $finalScore }}" readonly tabindex="-1">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endforeach
                        <div class="bg-emerald-50/50 font-bold border-t-2 border-emerald-100 p-3 rounded-lg flex justify-between items-center text-emerald-900">
                            <span>Total Perilaku (Avg x 20%)</span>
                            <span class="text-emerald-700 text-lg" id="total-perilaku-display">0.00</span>
                        </div>
                    </div>
                </div>

                {{-- KEHADIRAN (20%) --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden group hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-slate-50 border-b border-gray-100 p-5 sm:p-6 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 text-white flex items-center justify-center shadow-md shadow-amber-500/30 transform group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800">3. PENILAIAN KEHADIRAN (20%)</h3>
                            <p class="text-sm text-slate-500 mt-0.5">Penilaian atas kehadiran dan ketepatan waktu bekerja.</p>
                        </div>
                    </div>
                    
                    <div class="p-5 sm:p-6">
                        @php $kehadiranIndicators = $groupedIndicators->get('kehadiran', collect()); @endphp
                        
                        @if($kehadiranIndicators->isNotEmpty())
                            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                                <table class="w-full text-left border-collapse min-w-[700px]">
                                    <thead class="bg-slate-100 text-slate-600 text-sm">
                                        <tr>
                                            <th class="p-3 font-semibold border-b border-slate-200 w-12 text-center">No</th>
                                            <th class="p-3 font-semibold border-b border-slate-200">Aspek Kehadiran</th>
                                            <th class="p-3 font-semibold border-b border-slate-200 text-center w-40">Indeks (1-4)</th>
                                            <th class="p-3 font-semibold border-b border-slate-200 text-center w-28">Nilai Akhir</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($kehadiranIndicators as $index => $indicator)
                                            @php
                                                $item = $evaluation ? $evaluation->items->where('kpi_indicator_id', $indicator->id)->first() : null;
                                                $resultIndex = $item ? $item->result_index : '';
                                                $finalScore = $item ? $item->final_score : '';
                                            @endphp
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="p-3 text-center text-slate-500 font-medium">{{ $index + 1 }}</td>
                                                <td class="p-3">
                                                    <div class="font-bold text-slate-800">{{ $indicator->name }}</div>
                                                    <div class="text-xs text-slate-500 mt-1">1: < 50%, 2: > 70%, 3: > 80%, 4: > 90%</div>
                                                </td>
                                                <td class="p-3">
                                                    <select name="result_indexes[{{ $indicator->id }}]" 
                                                            class="kpi-input-umum w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 text-sm font-medium text-slate-700 bg-white"
                                                            data-weight="100"
                                                            data-group="kehadiran"
                                                            {{ ($evaluation && $evaluation->status == 'approved') ? 'disabled' : 'required' }}>
                                                        <option value="" disabled selected>Pilih 1-4</option>
                                                        <option value="1" {{ $resultIndex == '1' ? 'selected' : '' }}>1 - Kurang</option>
                                                        <option value="2" {{ $resultIndex == '2' ? 'selected' : '' }}>2 - Cukup</option>
                                                        <option value="3" {{ $resultIndex == '3' ? 'selected' : '' }}>3 - Baik</option>
                                                        <option value="4" {{ $resultIndex == '4' ? 'selected' : '' }}>4 - Istimewa</option>
                                                    </select>
                                                </td>
                                                <td class="p-3 text-center">
                                                    <input type="text" name="final_scores[{{ $indicator->id }}]" 
                                                        class="final-score-umum w-full bg-slate-50 border border-slate-200 rounded-md py-1.5 px-2 text-center text-sm font-bold text-slate-700" 
                                                        value="{{ $finalScore }}" readonly tabindex="-1">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- CATATAN & TOTAL --}}
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 space-y-6">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <label class="flex items-center text-sm font-bold text-slate-700 mb-3 uppercase tracking-wider">
                                <i class="fas fa-comment-dots text-sky-500 mr-2 text-lg"></i> Catatan Evaluasi
                            </label>
                            <textarea name="evaluation_notes" rows="3" class="w-full border-gray-300 rounded-xl shadow-sm focus:border-sky-500 focus:ring focus:ring-sky-200 bg-slate-50 transition-colors duration-200" {{ ($evaluation && $evaluation->status == 'approved') ? 'readonly' : '' }} placeholder="Tuliskan catatan evaluasi di sini...">{{ $evaluation->evaluation_notes ?? '' }}</textarea>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <label class="flex items-center text-sm font-bold text-slate-700 mb-3 uppercase tracking-wider">
                                <i class="fas fa-tasks text-sky-500 mr-2 text-lg"></i> Rencana Tindak Lanjut
                            </label>
                            <textarea name="action_plan" rows="3" class="w-full border-gray-300 rounded-xl shadow-sm focus:border-sky-500 focus:ring focus:ring-sky-200 bg-slate-50 transition-colors duration-200" {{ ($evaluation && $evaluation->status == 'approved') ? 'readonly' : '' }} placeholder="Tuliskan rencana perbaikan ke depan...">{{ $evaluation->action_plan ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-2xl shadow-xl border border-indigo-700 p-6 sm:p-8 text-white relative overflow-hidden flex flex-col justify-between">
                        <div class="absolute -right-6 -top-6 text-indigo-500/20">
                            <i class="fas fa-chart-pie text-9xl"></i>
                        </div>
                        
                        <div class="relative z-10">
                            <h3 class="text-indigo-300 font-bold uppercase tracking-widest text-sm mb-6 flex items-center">
                                <i class="fas fa-calculator mr-2"></i> Rekapitulasi Nilai
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between items-center text-indigo-100 pb-3 border-b border-indigo-700/50">
                                    <span class="font-medium text-sm">Kinerja (60%)</span>
                                    <span class="font-bold" id="summary-kinerja">0.00</span>
                                </div>
                                <div class="flex justify-between items-center text-indigo-100 pb-3 border-b border-indigo-700/50">
                                    <span class="font-medium text-sm">Perilaku (20%)</span>
                                    <span class="font-bold" id="summary-perilaku">0.00</span>
                                </div>
                                <div class="flex justify-between items-center text-indigo-100 pb-3 border-b border-indigo-700/50">
                                    <span class="font-medium text-sm">Kehadiran (20%)</span>
                                    <span class="font-bold" id="summary-kehadiran">0.00</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 relative z-10">
                            <label class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-2 block">Total Skor Akhir</label>
                            <input type="text" name="total_score" id="total_score" class="w-full bg-black/30 border-2 border-indigo-500/50 rounded-xl py-4 px-4 text-center text-4xl font-black text-white shadow-inner focus:outline-none" value="{{ $evaluation->total_score ?? '0.00' }}" readonly>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('kpi.index') }}" class="px-6 py-3 bg-white text-slate-700 border border-slate-300 rounded-xl hover:bg-slate-50 font-bold text-center transition-colors shadow-sm flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    
                    @if(Auth::user()->id !== $targetUser->id && (!$evaluation || $evaluation->status != 'approved'))
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-sky-500 text-white rounded-xl hover:from-blue-700 hover:to-sky-600 font-bold shadow-lg shadow-blue-500/30 transform transition-all hover:-translate-y-0.5 text-center flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Simpan Evaluasi
                        </button>
                    @endif
                    
                    @if($evaluation && Auth::user()->id === $targetUser->id && $evaluation->status == 'approved')
                        <button type="button" class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-400 text-white rounded-xl hover:from-emerald-600 hover:to-teal-500 font-bold shadow-lg shadow-emerald-500/30 transform transition-all hover:-translate-y-0.5 text-center flex items-center justify-center gap-2">
                            <i class="fas fa-check-double"></i> Saya Mengetahui Hasil Ini
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.kpi-input-umum');
            const totalScoreInput = document.getElementById('total_score');
            
            const displayKinerja = document.getElementById('total-kinerja-display');
            const displayPerilaku = document.getElementById('total-perilaku-display');
            
            const sumKinerja = document.getElementById('summary-kinerja');
            const sumPerilaku = document.getElementById('summary-perilaku');
            const sumKehadiran = document.getElementById('summary-kehadiran');

            function calculateTotals() {
                let totalKinerjaScore = 0;
                let sumPerilakuIndex = 0;
                let countPerilaku = 0;
                let sumKehadiranIndex = 0;
                let countKehadiran = 0;

                inputs.forEach(input => {
                    const val = parseFloat(input.value) || 0;
                    const weight = parseFloat(input.dataset.weight) || 0;
                    const group = input.dataset.group;
                    const row = input.closest('tr');
                    const finalInput = row.querySelector('.final-score-umum');

                    if (group === 'kinerja') {
                        // Kinerja is weighted internally (50, 30, 20) and max index is 5.
                        // Wait, form says Nilai = Indeks * Bobot? Yes, for Kinerja it's simple weighted sum of index.
                        const final = (val * weight) / 100;
                        if(finalInput) finalInput.value = final.toFixed(2);
                        totalKinerjaScore += final;
                    } 
                    else if (group === 'perilaku') {
                        // For perilaku, just record the index. We will average them.
                        if(finalInput) finalInput.value = val;
                        if(val > 0) {
                            sumPerilakuIndex += val;
                            countPerilaku++;
                        }
                    }
                    else if (group === 'kehadiran') {
                        // Kehadiran index
                        if(finalInput) finalInput.value = val;
                        if(val > 0) {
                            sumKehadiranIndex += val;
                            countKehadiran++;
                        }
                    }
                });

                // Kinerja Total (Max 5.0) -> scaled to 60% of max 5?
                // Wait, if max index is 5, total max Kinerja is 5. 60% of 5 is 3.
                const kpiKinerja = totalKinerjaScore * 0.6; // 60% weight

                // Perilaku Avg (Max 4) -> Scaled to 5? 
                // Let's keep it simple: index max 4. To scale it to match 5, we can multiply by 1.25. Or just use the raw score.
                // Assuming it's directly mapped: (Avg / 4 * 5) * 20%
                let kpiPerilaku = 0;
                if(countPerilaku > 0) {
                    let avgPerilaku = sumPerilakuIndex / countPerilaku; // max 4
                    kpiPerilaku = (avgPerilaku / 4 * 5) * 0.2; // scale to 5, then 20%
                }

                // Kehadiran Avg (Max 4) -> Scaled to 5 -> 20%
                let kpiKehadiran = 0;
                if(countKehadiran > 0) {
                    let avgKehadiran = sumKehadiranIndex / countKehadiran;
                    kpiKehadiran = (avgKehadiran / 4 * 5) * 0.2;
                }

                if(displayKinerja) displayKinerja.innerText = totalKinerjaScore.toFixed(2);
                if(displayPerilaku) displayPerilaku.innerText = (sumPerilakuIndex / (countPerilaku||1)).toFixed(2);

                if(sumKinerja) sumKinerja.innerText = kpiKinerja.toFixed(2);
                if(sumPerilaku) sumPerilaku.innerText = kpiPerilaku.toFixed(2);
                if(sumKehadiran) sumKehadiran.innerText = kpiKehadiran.toFixed(2);

                const finalGrandTotal = kpiKinerja + kpiPerilaku + kpiKehadiran;
                if(totalScoreInput) totalScoreInput.value = finalGrandTotal.toFixed(2);
            }

            inputs.forEach(input => {
                input.addEventListener('change', calculateTotals);
            });

            // Initial calculation
            calculateTotals();
        });
    </script>
    @endpush
</x-layout-users>
