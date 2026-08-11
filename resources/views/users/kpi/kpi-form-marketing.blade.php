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
                            {{ $period }}
                        </p>
                        @if($evaluation && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged']))
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
            <form action="{{ route('kpi.storeEvaluate', $targetUser->id) }}" method="POST" id="kpi-form-marketing" class="space-y-6">
                @csrf
                <input type="hidden" name="period" value="{{ $period }}">
                <input type="hidden" name="is_marketing_format" value="1">
                
                {{-- KINERJA SECTION (100%) --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden group hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-slate-50 border-b border-gray-100 p-5 sm:p-6 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center shadow-md shadow-blue-500/30 transform group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-chart-line text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-800">A. PENILAIAN KINERJA</h3>
                                <p class="text-sm text-slate-500 mt-0.5">Penilaian atas target pencapaian divisi utama.</p>
                            </div>
                        </div>
                        
                        <div class="hidden sm:block text-right text-xs text-slate-500 bg-white px-4 py-2 border border-slate-200 rounded-lg shadow-sm">
                            <strong class="block text-slate-700 mb-1">Keterangan Nilai (Indeks):</strong>
                            5 = 90 &le; x &le; 100 <br>
                            4 = 80 &le; x &lt; 90 <br>
                            3 = 60 &le; x &lt; 80 <br>
                            2 = 50 &le; x &lt; 60 <br>
                            1 = &lt; 50
                        </div>
                    </div>
                    
                    <div class="p-5 sm:p-6">
                        @php $kinerjaIndicators = $groupedIndicators->get('kinerja', collect()); @endphp
                        
                        @if($kinerjaIndicators->isEmpty())
                            <div class="bg-slate-50 rounded-xl p-6 text-center border border-dashed border-slate-300">
                                <i class="fas fa-folder-open text-4xl text-slate-300 mb-3 block"></i>
                                <span class="text-slate-500 font-medium">Tidak ada indikator KPI Kinerja yang ditemukan.</span>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                                <table class="w-full text-left border-collapse min-w-[900px]">
                                    <thead>
                                        <tr class="bg-blue-600 text-white text-sm text-center">
                                            <th class="p-3 font-semibold border-b border-blue-700 w-12">No</th>
                                            <th class="p-3 font-semibold border-b border-blue-700">Nama KPI & Definisi</th>
                                            <th class="p-3 font-semibold border-b border-blue-700 w-32">Target</th>
                                            <th class="p-3 font-semibold border-b border-blue-700 w-36">Achievement</th>
                                            <th class="p-3 font-semibold border-b border-blue-700 w-32">Hasil</th>
                                            <th class="p-3 font-semibold border-b border-blue-700 w-36">Evaluasi (1-5)</th>
                                            <th class="p-3 font-semibold border-b border-blue-700 w-24">Bobot</th>
                                            <th class="p-3 font-semibold border-b border-blue-700 w-28">Nilai Akhir</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($kinerjaIndicators as $index => $indicator)
                                            @php
                                                $item = $evaluation ? $evaluation->items->where('kpi_indicator_id', $indicator->id)->first() : null;
                                                $achievementText = $item ? $item->achievement_value : '';
                                                $hasilValue = $item ? $item->hasil_value : '';
                                                $resultIndex = $item ? $item->result_index : '';
                                                $finalScore = $item ? $item->final_score : '';
                                            @endphp
                                            <tr class="hover:bg-blue-50/30 transition-colors">
                                                <td class="p-3 text-center text-slate-500 font-medium">{{ $index + 1 }}</td>
                                                <td class="p-3">
                                                    <div class="font-bold text-slate-800">{{ $indicator->name }}</div>
                                                    <div class="text-xs text-slate-500 mt-1 leading-tight">{{ $indicator->definition }}</div>
                                                </td>
                                                <td class="p-3 text-center">
                                                    @php
                                                        $unit = '';
                                                        if(stripos($indicator->name, 'Net Sales') !== false) $unit = 'jt';
                                                        elseif(stripos($indicator->name, '% Grw') !== false || stripos($indicator->name, 'Rasio') !== false) $unit = '%';
                                                        elseif(stripos($indicator->name, 'Basic Operation') !== false) $unit = 'Outlet/hari';
                                                    @endphp
                                                    <div class="flex items-center gap-2">
                                                        <input type="text" name="target_values[{{ $indicator->id }}]" 
                                                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm p-2 text-center font-bold text-slate-700" 
                                                            placeholder="Target"
                                                            value="{{ $item ? $item->target_value : ($indicator->target ?? '') }}"
                                                            {{ ($evaluation && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged'])) ? 'readonly' : 'required' }}>
                                                        @if($unit)
                                                            <span class="text-xs font-bold text-slate-500 w-16 text-left">{{ $unit }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="p-3 text-center">
                                                    <input type="text" name="achievement_values[{{ $indicator->id }}]" 
                                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm p-2 text-center" 
                                                        placeholder="Misal: 493,994,772"
                                                        value="{{ $achievementText }}"
                                                        {{ ($evaluation && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged'])) ? 'readonly' : 'required' }}>
                                                </td>
                                                <td class="p-3 text-center">
                                                    <input type="text" name="hasil_values[{{ $indicator->id }}]" 
                                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm p-2 text-center" 
                                                        placeholder="Misal: 82%"
                                                        value="{{ $hasilValue }}"
                                                        {{ ($evaluation && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged'])) ? 'readonly' : 'required' }}>
                                                </td>
                                                <td class="p-3 text-center">
                                                    <select name="result_indexes[{{ $indicator->id }}]" 
                                                            class="kpi-eval-input w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm p-2 text-center bg-white font-bold"
                                                            data-weight="{{ $indicator->weight_percentage }}"
                                                            {{ ($evaluation && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged'])) ? 'disabled' : 'required' }}>
                                                        <option value="" disabled selected>-</option>
                                                        <option value="1" {{ $resultIndex == '1' ? 'selected' : '' }}>1</option>
                                                        <option value="2" {{ $resultIndex == '2' ? 'selected' : '' }}>2</option>
                                                        <option value="3" {{ $resultIndex == '3' ? 'selected' : '' }}>3</option>
                                                        <option value="4" {{ $resultIndex == '4' ? 'selected' : '' }}>4</option>
                                                        <option value="5" {{ $resultIndex == '5' ? 'selected' : '' }}>5</option>
                                                    </select>
                                                </td>
                                                <td class="p-3 text-center bg-slate-50">
                                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-blue-100 text-blue-800 font-bold text-xs border border-blue-200">
                                                        {{ (float)$indicator->weight_percentage }}%
                                                    </span>
                                                </td>
                                                <td class="p-3 text-center">
                                                    <input type="text" name="final_scores[{{ $indicator->id }}]" 
                                                        class="final-score-input w-full bg-slate-100 border border-slate-200 rounded-md py-1.5 px-2 text-center text-sm font-bold text-slate-700" 
                                                        value="{{ $finalScore }}" readonly tabindex="-1">
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-blue-100 font-bold border-t-2 border-blue-200">
                                            <td colspan="6" class="p-3 text-right text-blue-900 tracking-wider">TOTAL</td>
                                            <td class="p-3 text-center text-blue-800">100%</td>
                                            <td class="p-3 text-center text-blue-900 text-lg" id="total-kinerja-display">
                                                {{ $evaluation->total_score ?? '0.00' }}
                                            </td>
                                        </tr>
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
                            <textarea name="evaluation_notes" rows="3" class="w-full border-gray-300 rounded-xl shadow-sm focus:border-sky-500 focus:ring focus:ring-sky-200 bg-slate-50 transition-colors duration-200 p-4" {{ ($evaluation && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged'])) ? 'readonly' : '' }} placeholder="Tuliskan catatan evaluasi di sini...">{{ $evaluation->evaluation_notes ?? '' }}</textarea>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <label class="flex items-center text-sm font-bold text-slate-700 mb-3 uppercase tracking-wider">
                                <i class="fas fa-tasks text-sky-500 mr-2 text-lg"></i> Rencana Tindak Lanjut
                            </label>
                            <textarea name="action_plan" rows="3" class="w-full border-gray-300 rounded-xl shadow-sm focus:border-sky-500 focus:ring focus:ring-sky-200 bg-slate-50 transition-colors duration-200 p-4" {{ ($evaluation && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged'])) ? 'readonly' : '' }} placeholder="Tuliskan rencana perbaikan ke depan...">{{ $evaluation->action_plan ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-2xl shadow-xl border border-indigo-700 p-6 sm:p-8 text-white relative overflow-hidden flex flex-col justify-between">
                        <div class="absolute -right-6 -top-6 text-indigo-500/20">
                            <i class="fas fa-trophy text-9xl"></i>
                        </div>
                        
                        <div class="relative z-10 flex-1 flex flex-col justify-center">
                            <h3 class="text-indigo-300 font-bold uppercase tracking-widest text-sm mb-2 text-center">
                                Total Nilai Akhir
                            </h3>
                            <div class="text-center mt-4">
                                <input type="text" name="total_score" id="total_score" class="w-full bg-transparent border-none text-center text-6xl font-black text-white focus:outline-none focus:ring-0" value="{{ $evaluation->total_score ?? '0.00' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('kpi.index') }}" class="px-6 py-3 bg-white text-slate-700 border border-slate-300 rounded-xl hover:bg-slate-50 font-bold text-center transition-colors shadow-sm flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    
                    @if(Auth::user()->id !== $targetUser->id && (!$evaluation || !in_array($evaluation->status, ['disetujui_direktur', 'acknowledged'])))
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-sky-500 text-white rounded-xl hover:from-blue-700 hover:to-sky-600 font-bold shadow-lg shadow-blue-500/30 transform transition-all hover:-translate-y-0.5 text-center flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Simpan Evaluasi
                        </button>
                    @endif
                    
                    @if($evaluation && Auth::user()->id === $targetUser->id && in_array($evaluation->status, ['disetujui_direktur', 'acknowledged']))
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
            const evalInputs = document.querySelectorAll('.kpi-eval-input');
            const totalScoreInput = document.getElementById('total_score');
            const displayTotal = document.getElementById('total-kinerja-display');

            function calculateTotals() {
                let grandTotal = 0;

                evalInputs.forEach(input => {
                    const val = parseFloat(input.value) || 0;
                    const weight = parseFloat(input.dataset.weight) || 0;
                    const row = input.closest('tr');
                    const finalInput = row.querySelector('.final-score-input');

                    // Calculation based on PDF: Nilai Akhir = Evaluasi Penilaian (val) * Bobot (%)
                    // Since Bobot is in percentage, multiply by (weight/100)
                    const finalScore = (val * weight) / 100;
                    if(finalInput) {
                        finalInput.value = finalScore.toFixed(2);
                    }
                    grandTotal += finalScore;
                });

                if(displayTotal) displayTotal.innerText = grandTotal.toFixed(2);
                if(totalScoreInput) totalScoreInput.value = grandTotal.toFixed(2);
            }

            evalInputs.forEach(input => {
                input.addEventListener('change', calculateTotals);
            });

            // No initial calc needed if it's already filled, but we can do it to be safe
            // IF we aren't showing pre-calculated data
            if(totalScoreInput.value == '0.00' || !totalScoreInput.value) {
                calculateTotals();
            }
        });
    </script>
    @endpush
</x-layout-users>
