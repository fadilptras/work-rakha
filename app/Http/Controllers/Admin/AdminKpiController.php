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
        $karyawans = User::all();
        $evaluations = KpiEvaluation::with(['user', 'evaluator'])->get();

        return view('admin.kpi.index', [
            'title' => 'Daftar Evaluasi KPI Karyawan',
            'karyawans' => $karyawans,
            'evaluations' => $evaluations
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

        $evaluation = KpiEvaluation::with('items')->where('user_id', $id)
                        ->where('period', 'July 2026') // or pass from request
                        ->first();
        
        // We will use a unified view that handles both formats
        return view('admin.kpi.evaluate', compact('targetUser', 'groupedIndicators', 'evaluation', 'is_frontliner'));
    }

    public function storeEvaluate(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);
        
        $evaluation = KpiEvaluation::updateOrCreate(
            ['user_id' => $id, 'period' => $request->period ?? 'July 2026'],
            [
                'evaluator_id' => Auth::id(),
                'evaluation_date' => now(),
                'evaluation_notes' => $request->evaluation_notes,
                'action_plan' => $request->action_plan,
                'status' => $request->status ?? 'submitted',
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
                    
                    KpiEvaluationItem::updateOrCreate(
                        ['kpi_evaluation_id' => $evaluation->id, 'kpi_indicator_id' => $indicator_id],
                        [
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
}
