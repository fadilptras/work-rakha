<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    protected const CRM_DIVISIONS = [
        'marketing',
        'operasional',
        'marketing dan operasional',
    ];
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

        if (\App\Models\User::isSuperUserEmail($user->email ?? '')) return true;

        $jabatan = strtolower(trim($user->jabatan ?? ''));
        $divisi  = strtolower(trim($user->divisi ?? ''));
        $email   = strtolower(trim($user->email ?? ''));

        $isTopManagement = Str::contains($jabatan, 'direktur') || $divisi === 'top management';
        $isKepalaDivisi  = (($user->is_kepala_divisi == 1) || Str::contains($jabatan, 'kepala'))
            && in_array($divisi, ['marketing', 'operasional', 'marketing dan operasional']);
        $isAdminSupport  = Str::contains($jabatan, 'admin support');
        $isSalesSupervisor = Str::contains($jabatan, 'sales supervisor')
            || in_array($email, ['paladin_arief@yahoo.com'], true);
        $isTest          = Str::contains($jabatan, 'test');

        return $isTopManagement || $isKepalaDivisi || $isAdminSupport || $isSalesSupervisor || $isTest;
    }

    // Cek Akses Dasar (Apakah boleh buka CRM sama sekali)
    protected function hasAnyCrmAccess()
    {
        if ($this->hasFullAccess()) return true;

        $user = Auth::user();
        if (!$user) return false;

        $divisi = strtolower(trim($user->divisi ?? ''));

        return in_array($divisi, self::CRM_DIVISIONS, true);
    }

    /**
     * Daftar karyawan pada divisi Marketing / Operasional untuk dropdown PIC CRM.
     */
    protected function getMarketingOperationalUsers()
    {
        return User::whereIn(DB::raw('LOWER(TRIM(divisi))'), self::CRM_DIVISIONS)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'divisi', 'jabatan']);
    }

    // ---------- Helper split kontak + instansi ----------

    /**
     * Cari atau buatkan Customer dari nama instansi, lalu sinkronkan
     * field instansi yang disertakan (alias, area, apoteker, alamat kantor).
     */
    protected function resolveCustomer(array $input): Customer
    {
        $name = trim((string) ($input['customer_name'] ?? ''));
        abort_if($name === '', 422, 'Nama instansi wajib diisi.');
        $customer = Customer::firstOrCreate(['customer_name' => $name]);

        $attrs = [];
        foreach (['sales_customer_name', 'area', 'pharmacist_name', 'pharmacist_license_no', 'pharmacist_phone'] as $f) {
            if (array_key_exists($f, $input) && trim((string) $input[$f]) !== '') {
                $attrs[$f] = trim((string) $input[$f]);
            }
        }
        // Field form bernama company_* dipetakan ke kolom customer.
        $fieldMap = [
            'customer_type' => 'customer_type',
            'company_phone' => 'phone',
            'company_email' => 'email',
            'anniversary_date' => 'anniversary_date',
            'npwp' => 'npwp',
        ];
        foreach ($fieldMap as $in => $col) {
            if (array_key_exists($in, $input) && trim((string) $input[$in]) !== '') {
                $attrs[$col] = trim((string) $input[$in]);
            }
        }
        if ($attrs) $customer->update($attrs);
        if (array_key_exists('company_address', $input)) {
            $this->syncCustomerOfficeAddress($customer, $input['company_address']);
        }

        return $customer->fresh();
    }

    /**
     * Tulis/bersihkan entri alamat berlabel Kantor di customer_address (JSON).
     * Entri berlabel lain (Gudang/Cabang) tidak disentuh.
     */
    protected function syncCustomerOfficeAddress(Customer $customer, $address): void
    {
        $address = trim((string) ($address ?? ''));
        $list = array_values(array_filter((array) ($customer->customer_address ?? []), 'is_array'));
        $found = false;
        foreach ($list as $i => $a) {
            if (($a['label'] ?? '') === 'Kantor') {
                $list[$i]['address'] = $address;
                $found = true;
                break;
            }
        }
        if (!$found && $address !== '') {
            $list[] = ['label' => 'Kantor', 'address' => $address, 'city' => '', 'phone' => '', 'is_default' => empty($list)];
        }
        $customer->update(['customer_address' => array_values($list)]);
    }

    /**
     * Ambil hanya field milik kontak dari input tervalidasi.
     */
    protected function contactAttributes(array $validated): array
    {
        $keys = ['client_name', 'email', 'phone', 'address', 'birthdate', 'position', 'hobby', 'commission_rate', 'bank_name', 'bank_account_number', 'bank_account_name', 'opening_balance'];

        return array_intersect_key($validated, array_flip($keys));
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
