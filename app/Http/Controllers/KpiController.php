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

        // Get requested period or set default based on current month
        $currentMonth = date('n');
        $currentYear = date('Y');
        // If Jan-Jun, evaluate previous year's Semester 2 or current year's Semester 1 depending on policy.
        // As per request: July = Semester 1, December = Semester 2.
        $defaultPeriod = $currentMonth <= 6 ? "Semester 2 " . ($currentYear - 1) : "Semester 1 " . $currentYear;
        if ($currentMonth == 12) {
            $defaultPeriod = "Semester 2 " . $currentYear;
        }
        $period = request()->get('period', $defaultPeriod);

        if ($user->role === 'direktur' || $user->role === 'admin') {
            $is_evaluator = true;
            $karyawans = User::all();
            $evaluations = KpiEvaluation::with(['user', 'evaluator'])->where('period', $period)->get();
        } 
        elseif (strtolower($user->divisi) === 'top management') {
            $is_evaluator = true;
            $karyawans = User::all();
            $evaluations = KpiEvaluation::with(['user', 'evaluator'])->where('period', $period)->get();
        }
        elseif ($user->email === 'tmaujana@gmail.com') {
            $is_evaluator = true;
            $karyawans = User::where('divisi', 'Marketing dan Operasional')->get();
            $evaluations = KpiEvaluation::with(['user', 'evaluator'])
                            ->whereHas('user', function($query) {
                                $query->where('divisi', 'Marketing dan Operasional');
                            })->where('period', $period)->get();
        }
        elseif ($user->is_kepala_divisi) {
            $is_evaluator = true;
            $karyawans = User::where('divisi', $user->divisi)->get();
            $evaluations = KpiEvaluation::with(['user', 'evaluator'])
                            ->whereHas('user', function($query) use ($user) {
                                $query->where('divisi', $user->divisi);
                            })->where('period', $period)->get();
        } 
        else {
            // Karyawan Biasa
            $is_evaluator = false;
            $karyawans = collect([$user]);
            $evaluations = KpiEvaluation::with(['user', 'evaluator'])->where('user_id', $user->id)->where('period', $period)->get();
        }

        return view('users.kpi.index', [
            'title' => 'Key Perfomance Indicator (KPI)',
            'karyawans' => $karyawans,
            'evaluations' => $evaluations,
            'is_evaluator' => $is_evaluator,
            'period' => $period
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

        $currentMonth = date('n');
        $currentYear = date('Y');
        $defaultPeriod = $currentMonth <= 6 ? "Semester 2 " . ($currentYear - 1) : "Semester 1 " . $currentYear;
        if ($currentMonth == 12) {
            $defaultPeriod = "Semester 2 " . $currentYear;
        }
        $period = request()->get('period', $defaultPeriod);

        $evaluation = KpiEvaluation::with('items')->where('user_id', $id)
                        ->where('period', $period)
                        ->first();

        // Pilih view berdasarkan format divisi
        $viewName = $is_frontliner ? 'users.kpi.kpi-form-marketing' : 'users.kpi.kpi-form-umum';
        
        return view($viewName, compact('targetUser', 'groupedIndicators', 'evaluation', 'period'));
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
            // Handle form Marketing / Operasional
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

        return redirect()->route('kpi.index')->with('success', 'Evaluation saved successfully');
    }

    public function approve($id)
    {
        $evaluation = KpiEvaluation::findOrFail($id);
        $evaluation->update(['status' => 'disetujui_direktur']);
        return redirect()->back()->with('success', 'Evaluasi KPI berhasil disetujui.');
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
