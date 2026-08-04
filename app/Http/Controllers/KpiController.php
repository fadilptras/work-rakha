<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\KpiIndicator;
use App\Models\KpiEvaluation;
use App\Models\KpiEvaluationItem;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class KpiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $is_evaluator = false;
        $karyawans = collect();
        $evaluations = collect();

        if ($user->role === 'direktur' || $user->role === 'admin') {
            $is_evaluator = true;
            $karyawans = User::all();
            $evaluations = KpiEvaluation::with(['user', 'evaluator'])->get();
        } 
        elseif (strtolower($user->divisi) === 'top management') {
            $is_evaluator = true;
            $karyawans = User::all();
            $evaluations = KpiEvaluation::with(['user', 'evaluator'])->get();
        }
        elseif ($user->email === 'tmaujana@gmail.com') {
            $is_evaluator = true;
            $karyawans = User::where('divisi', 'Marketing dan Operasional')->get();
            $evaluations = KpiEvaluation::with(['user', 'evaluator'])
                            ->whereHas('user', function($query) {
                                $query->where('divisi', 'Marketing dan Operasional');
                            })->get();
        }
        elseif ($user->is_kepala_divisi) {
            $is_evaluator = true;
            $karyawans = User::where('divisi', $user->divisi)->get();
            $evaluations = KpiEvaluation::with(['user', 'evaluator'])
                            ->whereHas('user', function($query) use ($user) {
                                $query->where('divisi', $user->divisi);
                            })->get();
        } 
        else {
            // Karyawan Biasa
            $is_evaluator = false;
            $karyawans = collect([$user]);
            $evaluations = KpiEvaluation::with(['user', 'evaluator'])->where('user_id', $user->id)->get();
        }

        return view('users.kpi.index', [
            'title' => 'Key Perfomance Indicator (KPI)',
            'karyawans' => $karyawans,
            'evaluations' => $evaluations,
            'is_evaluator' => $is_evaluator
        ]);
    }

    public function evaluate($id)
    {
        $targetUser = User::findOrFail($id);
        
        $divisi = strtolower($targetUser->divisi);
        // Define if division uses the frontliner (marketing) form or backoffice (umum) form
        // ONLY tmaujana@gmail.com uses marketing form for marketing & operasional members
        $is_frontliner = ($divisi === 'marketing dan operasional' && Auth::user()->email === 'tmaujana@gmail.com');
        
        // Asumsi tipe indikator di DB:
        $type = $is_frontliner ? 'marketing' : 'umum';

        // Fetch all indicators for this division type
        $indicators = KpiIndicator::where('type', $type)->get();
        
        // Group indicators by category
        $groupedIndicators = $indicators->groupBy('category');

        $evaluation = KpiEvaluation::with('items')->where('user_id', $id)
                        ->where('period', 'July 2026') // Example static period
                        ->first();

        // Pilih view berdasarkan format divisi
        $viewName = $is_frontliner ? 'users.kpi.kpi-form-marketing' : 'users.kpi.kpi-form-umum';
        
        return view($viewName, compact('targetUser', 'groupedIndicators', 'evaluation'));
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
            // Handle form Marketing / Operasional
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

        return redirect()->route('kpi.index')->with('success', 'Evaluation saved successfully');
    }

    public function exportPdf($id)
    {
        $evaluation = KpiEvaluation::with(['user', 'evaluator', 'items.indicator'])->findOrFail($id);
        
        $divisi = strtolower($evaluation->user->divisi);
        $is_frontliner = (strtolower($divisi) === 'marketing');
        
        $viewName = $is_frontliner ? 'users.kpi.kpi-pdf-marketing' : 'users.kpi.kpi-pdf-umum';
        
        $pdf = Pdf::loadView($viewName, compact('evaluation'));
        return $pdf->download('KPI-'.$evaluation->user->name.'.pdf');
    }
}
