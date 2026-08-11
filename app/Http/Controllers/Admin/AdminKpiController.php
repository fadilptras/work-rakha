<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\KpiIndicator;
use App\Models\KpiEvaluation;
use App\Models\KpiEvaluationItem;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminKpiController extends Controller
{
    public function index()
    {
        // For Admin, fetch all users and their evaluations
        $currentMonth = date('n');
        $currentYear = date('Y');
        $defaultPeriod = $currentMonth <= 6 ? "Semester 2 " . ($currentYear - 1) : "Semester 1 " . $currentYear;
        if ($currentMonth == 12) {
            $defaultPeriod = "Semester 2 " . $currentYear;
        }
        $period = request()->get('period', $defaultPeriod);

        $karyawans = User::all();
        $evaluations = KpiEvaluation::with(['user', 'evaluator'])->where('period', $period)->get();

        return view('admin.kpi.index', [
            'title' => 'Daftar Evaluasi KPI Karyawan',
            'karyawans' => $karyawans,
            'evaluations' => $evaluations,
            'period' => $period
        ]);
    }

    public function evaluate($id)
    {
        $targetUser = User::findOrFail($id);
        
        $divisi = strtolower($targetUser->divisi);
        $is_frontliner = (strtolower($divisi) === 'marketing');
        
        $type = $is_frontliner ? 'marketing' : 'umum';

        $indicators = KpiIndicator::where('type', $type)->get();
        $groupedIndicators = $indicators->groupBy('category');

        $currentMonth = date('n');
        $currentYear = date('Y');
        $defaultPeriod = $currentMonth <= 6 ? "Semester 2 " . ($currentYear - 1) : "Semester 1 " . $currentYear;
        if ($currentMonth == 12) {
            $defaultPeriod = "Semester 2 " . $currentYear;
        }
        $period = request()->get('period', $defaultPeriod);

        $evaluation = KpiEvaluation::with('items')->where('user_id', $id)
                        ->where('period', $period) // or pass from request
                        ->first();
        
        // We will use a unified view that handles both formats
        return view('admin.kpi.evaluate', compact('targetUser', 'groupedIndicators', 'evaluation', 'is_frontliner', 'period'));
    }

    public function storeEvaluate(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);
        
        $evaluation = KpiEvaluation::updateOrCreate(
            ['user_id' => $id, 'period' => $request->period],
            [
                'evaluator_id' => Auth::id(),
                'evaluation_date' => now(),
                'evaluation_notes' => $request->evaluation_notes,
                'action_plan' => $request->action_plan,
                'status' => $request->status ?? 'disetujui_kepala_divisi',
                'total_score' => $request->total_score 
            ]
        );

        if ($request->has('is_marketing_format')) {
            // Handle form Marketing
            if($request->has('achievement_values')) {
                foreach($request->achievement_values as $indicator_id => $achieve) {
                    $index = $request->result_indexes[$indicator_id] ?? 1;
                    $final = $request->final_scores[$indicator_id] ?? 0;
                    $hasil = $request->hasil_values[$indicator_id] ?? '';
                    $target = $request->target_values[$indicator_id] ?? null;
                    
                    KpiEvaluationItem::updateOrCreate(
                        ['kpi_evaluation_id' => $evaluation->id, 'kpi_indicator_id' => $indicator_id],
                        [
                            'target_value' => $target,
                            'achievement_value' => $achieve,
                            'hasil_value' => $hasil,
                            'result_index' => $index,
                            'final_score' => $final
                        ]
                    );
                }
            }
        } else {
            // Handle form Umum
            if($request->has('achievements')) {
                foreach($request->achievements as $indicator_id => $achieve) {
                    $index = $request->result_indexes[$indicator_id] ?? 1;
                    $final = $request->final_scores[$indicator_id] ?? 0;
                    
                    KpiEvaluationItem::updateOrCreate(
                        ['kpi_evaluation_id' => $evaluation->id, 'kpi_indicator_id' => $indicator_id],
                        [
                            'achievement_value' => $achieve,
                            'result_index' => $index,
                            'final_score' => $final
                        ]
                    );
                }
            }
        }

        return redirect()->route('admin.kpi.index')->with('success', 'Evaluasi KPI berhasil disimpan!');
    }

    public function approve($id)
    {
        $evaluation = KpiEvaluation::findOrFail($id);
        $evaluation->update(['status' => 'disetujui_direktur']);
        return redirect()->back()->with('success', 'Evaluasi KPI berhasil disetujui.');
    }
}
