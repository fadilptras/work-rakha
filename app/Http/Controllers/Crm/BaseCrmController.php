<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Controller induk modul CRM (Sistem Informasi Sales).
 *
 * Menampung helper hak akses + kalkulator saldo/rekap yang dipakai
 * bersama oleh controller CRM sisi user maupun sisi admin, supaya rumus
 * hanya dipelihara di satu tempat (seperti BaseSalesController di Sales).
 */
abstract class BaseCrmController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$this->hasAnyCrmAccess()) {
                abort(403, 'Anda tidak memiliki hak akses ke modul Sistem Informasi Sales (CRM).');
            }
            return $next($request);
        });
    }

    // ---------- Hak akses ----------

    /**
     * Hak akses penuh CRM — sama polanya dengan hasFullSalesAccess
     * di BaseSalesController (substring via Str::contains).
     */
    protected function hasFullAccess()
    {
        $user = Auth::user();
        if (!$user) return false;

        $jabatan = strtolower(trim($user->jabatan ?? ''));
        $divisi  = strtolower(trim($user->divisi ?? ''));

        $isTopManagement = Str::contains($jabatan, 'direktur') || $divisi === 'top management';
        $isKepalaDivisi  = (($user->is_kepala_divisi == 1) || Str::contains($jabatan, 'kepala'))
            && in_array($divisi, ['marketing', 'operasional', 'marketing dan operasional']);
        $isAdminSupport  = Str::contains($jabatan, 'admin support');
        $isTest          = Str::contains($jabatan, 'test');

        return $isTopManagement || $isKepalaDivisi || $isAdminSupport || $isTest;
    }

    // Cek Akses Dasar (Apakah boleh buka CRM sama sekali)
    protected function hasAnyCrmAccess()
    {
        if ($this->hasFullAccess()) return true;

        $user = Auth::user();
        if (!$user) return false;

        $divisi = strtolower(trim($user->divisi ?? ''));

        $allowedDivisi = [
            'marketing',
            'operasional',
            'marketing dan operasional'
        ];

        return in_array($divisi, $allowedDivisi);
    }

    // ---------- Kalkulator saldo / rekap ----------
    // Saldo = opening_balance + Σ(IN: sales_amount × commission_rate%) − Σ(OUT: amount).
    // Tipe ENTERTAIN tidak memengaruhi saldo.

    protected function calculateRecapData(Client $client, int $year)
    {
        $creationYear = $client->created_at->format('Y');
        $startingLabel = ($year > $creationYear) ? "Saldo Tahun " . ($year - 1) : "Saldo Awal";
        $startingBalance = $client->opening_balance ?? 0;

        $pastInteractions = $client->interactions()->whereYear('interaction_date', '<', $year)->get();
        foreach($pastInteractions as $item) {
            if ($item->transaction_type == 'OUT') {
                $startingBalance -= $item->amount;
            } elseif ($item->transaction_type == 'IN') {
                 $rate = (float) ($item->commission_rate ?? 0);
                 $nominal = $item->sales_amount > 0 ? $item->sales_amount : $item->amount;
                 $startingBalance += $nominal * ($rate / 100);
            }
        }

        $yearlyInteractions = $client->interactions()->whereYear('interaction_date', $year)->get();
        $recap = [];
        $currentSaldo = $startingBalance;

        for ($m = 1; $m <= 12; $m++) {
            $monthlyData = $yearlyInteractions->filter(fn($item) => Carbon::parse($item->interaction_date)->month == $m);
            $grossSales = $monthlyData->where('transaction_type', 'IN')->sum(fn($item) => $item->sales_amount > 0 ? $item->sales_amount : $item->amount);
            $usageOut = $monthlyData->where('transaction_type', 'OUT')->sum('amount');

            $netRevenue = 0; $rateList = [];
            foreach($monthlyData->where('transaction_type', 'IN') as $sale) {
                $rate = (float) ($sale->commission_rate ?? 0);
                $netRevenue += ($sale->sales_amount > 0 ? $sale->sales_amount : $sale->amount) * ($rate / 100);
                if($rate > 0) $rateList[] = $rate . '%';
            }
            $currentSaldo += ($netRevenue - $usageOut);
            $commissionText = empty($rateList) ? (($grossSales > 0) ? 'Var' : '-') : implode(', ', array_unique($rateList));

            $recap[] = ['month_name' => Carbon::create()->month($m)->translatedFormat('F'), 'commission_text' => $commissionText, 'gross_in' => $grossSales, 'net_value'  => $netRevenue, 'out' => $usageOut, 'saldo' => $currentSaldo];
        }

        return [
            'recap' => $recap,
            'totals' => ['gross_in' => collect($recap)->sum('gross_in'), 'net_value' => collect($recap)->sum('net_value'), 'out' => collect($recap)->sum('out'), 'saldo' => $currentSaldo],
            'starting_balance' => $startingBalance,
            'starting_label' => $startingLabel
        ];
    }

    protected function calculateRealTimeBalance(Client $client)
    {
        $balance = $client->opening_balance ?? 0;
        foreach($client->interactions as $item) {
            if ($item->transaction_type == 'OUT') {
                $balance -= $item->amount;
            } elseif ($item->transaction_type == 'IN') {
                $rate = (float) ($item->commission_rate ?? 0);
                $balance += ($item->sales_amount > 0 ? $item->sales_amount : $item->amount) * ($rate / 100);
            }
        }
        return $balance;
    }
}
