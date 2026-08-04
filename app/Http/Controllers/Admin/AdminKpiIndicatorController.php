<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KpiIndicator;

class AdminKpiIndicatorController extends Controller
{
    public function index()
    {
        // Group by type (division) and then by category
        $indicators = KpiIndicator::orderBy('type')->orderBy('category')->get();
        $groupedIndicators = $indicators->groupBy(['type', 'category']);

        return view('admin.kpi.indicators', [
            'title' => 'Kelola Indikator KPI',
            'groupedIndicators' => $groupedIndicators,
            'indicators' => $indicators // for flat list if needed
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'name' => 'required|string|max:255',
            'definition' => 'nullable|string',
            'target' => 'nullable|string',
            'weight_percentage' => 'required|numeric|min:0|max:100',
            'type' => 'required|string'
        ]);

        KpiIndicator::create($validated);

        return redirect()->route('admin.kpi.indicators.index')->with('success', 'Indikator KPI berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $indicator = KpiIndicator::findOrFail($id);

        $validated = $request->validate([
            'category' => 'required|string',
            'name' => 'required|string|max:255',
            'definition' => 'nullable|string',
            'target' => 'nullable|string',
            'weight_percentage' => 'required|numeric|min:0|max:100',
            'type' => 'required|string'
        ]);

        $indicator->update($validated);

        return redirect()->route('admin.kpi.indicators.index')->with('success', 'Indikator KPI berhasil diubah!');
    }

    public function destroy($id)
    {
        $indicator = KpiIndicator::findOrFail($id);
        $indicator->delete();

        return redirect()->route('admin.kpi.indicators.index')->with('success', 'Indikator KPI berhasil dihapus!');
    }
}
