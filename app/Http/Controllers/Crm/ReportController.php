<?php

namespace App\Http\Controllers\Crm;

use App\Exports\ClientAnnualExport;
use App\Exports\MatrixAnnualExport;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Laporan CRM: matrix tahunan + export rekap per klien.
 */
class ReportController extends BaseCrmController
{
    public function matrix(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $query = Client::active()->with('interactions')->orderBy('client_name', 'asc');
        if (!$this->hasFullAccess()) $query->where('user_id', Auth::id());
        $clients = $query->get();

        $months = [];
        for($m=1; $m<=12; $m++) $months[$m] = Carbon::create()->month($m)->translatedFormat('F');

        $monthlyTotals = [];
        $grandTotalYear = 0;

        foreach($months as $m => $name) {
            $sumMonth = 0;
            foreach($clients as $c) {
                $monthlyData = $c->interactions->filter(function($i) use ($m, $year){
                    return Carbon::parse($i->interaction_date)->month == $m
                        && Carbon::parse($i->interaction_date)->year == $year;
                });
                $income = 0;
                foreach($monthlyData->where('transaction_type', 'IN') as $sale) {
                    $r = (float) ($sale->commission_rate ?? 0);
                    $nom = $sale->sales_amount > 0 ? $sale->sales_amount : $sale->amount;
                    $income += $nom * ($r/100);
                }
                $usage = $monthlyData->where('transaction_type', 'OUT')->sum('amount');
                $sumMonth += ($income - $usage);
            }
            $monthlyTotals[$m] = $sumMonth;
            $grandTotalYear += $sumMonth;
        }

        $agent = new \Jenssegers\Agent\Agent();
        $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';

        return view("users.crm.crm_matriks_data_{$viewSuffix}", [
            'title' => 'Matrix Sales ' . $year, 'clients' => $clients, 'year' => $year,
            'months' => $months, 'monthlyTotals' => $monthlyTotals, 'grandTotalYear' => $grandTotalYear
        ]);
    }

    public function exportMatrix(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $query = Client::active()->with('interactions')->orderBy('client_name', 'asc');
        if (!$this->hasFullAccess()) $query->where('user_id', Auth::id());
        $clients = $query->get();
        $months = [];
        for($m=1; $m<=12; $m++) $months[$m] = Carbon::create()->month($m)->translatedFormat('F');
        return Excel::download(new MatrixAnnualExport($clients, $months, $year), 'Laporan_Matrix_Sales_' . $year . '.xlsx');
    }

    public function exportClientRecap(Client $client, Request $request)
    {
        // 1. Cek Hak Akses
        if ($client->user_id !== Auth::id() && !$this->hasFullAccess()) abort(403);

        // 2. Ambil tahun dari request
        $year = $request->input('year', date('Y'));

        // 3. Hitung data rekap
        $calc = $this->calculateRecapData($client, $year);

        // 4. BERSIHKAN NAMA FILE (Sanitization)
        // Gunakan str_replace untuk membuang karakter / dan \ agar tidak error
        $safeClientName = str_replace(['/', '\\'], '_', $client->client_name);

        // Gunakan str_replace lagi untuk mengubah spasi menjadi underscore agar nama file lebih rapi
        $fileName = 'Rekap_' . str_replace(' ', '_', $safeClientName) . '_' . $year . '.xlsx';

        // 5. Download file
        return Excel::download(new ClientAnnualExport(
            $client,
            $calc['recap'],
            $year,
            $calc['totals']
        ), $fileName);
    }
}
