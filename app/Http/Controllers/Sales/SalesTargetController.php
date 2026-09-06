<?php

namespace App\Http\Controllers\Sales;

use App\Models\SalesTarget;
use Illuminate\Http\Request;

class SalesTargetController extends BaseSalesController
{
    public function storeTarget(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'nullable|string',
            'targets' => 'nullable|array',
            'ps' => 'nullable|string',
            'target_amount' => 'nullable|numeric|min:0',
            'sales_last_year_amount' => 'nullable|numeric|min:0',
            'form_type' => 'nullable|string'
        ]);

        $targetTahun = $request->form_type === 'history' ? (int)$request->tahun + 1 : $request->tahun;
        $ps = $request->ps;

        if ($request->has('targets') && is_array($request->targets)) {
            // input multiple bulan (form target)
            foreach ($request->targets as $bulan => $amount) {
                $bulanAngka = array_search($bulan, $this->urutanBulan) + 1;
                // clean up amount from non-numeric characters if necessary, but we format via JS and store raw hidden
                $targetAmount = $amount !== null && $amount !== '' ? (float)$amount : 0;

                $existing = SalesTarget::where([
                    'year' => $targetTahun,
                    'month' => $bulan,
                    'ps' => $ps,
                ])->first();

                // simpan riwayat tahun lalu
                $lastYearAmount = $existing->last_year_amount ?? 0;

                SalesTarget::updateOrCreate(
                    [
                        'year' => $targetTahun,
                        'month' => $bulan,
                        'ps' => $ps,
                    ],
                    [
                        'month_number' => $bulanAngka,
                        'target_amount' => $targetAmount,
                        'last_year_amount' => $lastYearAmount
                    ]
                );
            }
        } else {
            // input single bulan (form history)
            $bulanAngka = array_search($request->bulan, $this->urutanBulan) + 1;

            $existing = SalesTarget::where([
                'year' => $targetTahun,
                'month' => $request->bulan,
                'ps' => $ps,
            ])->first();

            $targetAmount = $request->has('target_amount') && $request->target_amount !== null ? $request->target_amount : ($existing->target_amount ?? 0);
            $lastYearAmount = $request->has('sales_last_year_amount') && $request->sales_last_year_amount !== null ? $request->sales_last_year_amount : ($existing->last_year_amount ?? 0);

            SalesTarget::updateOrCreate(
                [
                    'year' => $targetTahun,
                    'month' => $request->bulan,
                    'ps' => $ps,
                ],
                [
                    'month_number' => $bulanAngka,
                    'target_amount' => $targetAmount,
                    'last_year_amount' => $lastYearAmount
                ]
            );
        }

        $msg = $request->form_type == 'history' ? 'Riwayat Sales Tahun Lalu berhasil disimpan!' : 'Target berhasil disimpan!';
        $activeTab = $request->form_type == 'history' ? 'tab-history' : 'tab-tgt';
        return redirect()->back()->with('success', $msg)->with('active_tab', $activeTab);
    }
}
