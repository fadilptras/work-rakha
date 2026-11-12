<?php

namespace App\Http\Controllers\Crm;

use App\Exports\ClientAnnualExport;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Laporan CRM: export rekap per klien.
 */
class ReportController extends BaseCrmController
{
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
