<?php

namespace App\Http\Controllers\Crm;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * CRUD data klien CRM (Sistem Informasi Sales).
 */
class ClientController extends BaseCrmController
{
    public function index(Request $request)
    {
        $year = $request->input('year', 'all');
        $query = Client::active()->with('interactions')->orderBy('client_name', 'asc');

        if (!$this->hasFullAccess()) {
            $query->where('user_id', Auth::id());
        }

        $clients = $query->get();

        // Inisialisasi variabel
        $totalAllBalance = 0;
        $totalUsage = 0;

        foreach($clients as $client) {
            // 1. Hitung Saldo (Net)
            if ($year !== 'all') {
                $balance = $client->opening_balance ?? 0;
                $yearInteractions = $client->interactions->filter(function($item) use ($year) {
                    return \Carbon\Carbon::parse($item->interaction_date)->format('Y') <= $year;
                });
                foreach($yearInteractions as $item) {
                    if ($item->transaction_type == 'OUT') {
                        $balance -= $item->amount;
                    } elseif ($item->transaction_type == 'IN') {
                        $rate = (float) ($item->commission_rate ?? 0);
                        $balance += ($item->sales_amount > 0 ? $item->sales_amount : $item->amount) * ($rate / 100);
                    }
                }
            } else {
                $balance = $this->calculateRealTimeBalance($client);
            }
            $client->current_balance = $balance;
            $totalAllBalance += $balance;

            // 2. Hitung Total Usage
            $usageQuery = $client->interactions->where('transaction_type', 'OUT');

            if ($year !== 'all') {
                $usageQuery = $usageQuery->filter(function($item) use ($year) {
                    return \Carbon\Carbon::parse($item->interaction_date)->format('Y') == $year;
                });
            }

            $clientUsage = $usageQuery->sum('amount');

            $totalUsage += $clientUsage;
        }

        $agent = new \Jenssegers\Agent\Agent();
        $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';

        return view("users.crm.crm_daftar_klien_{$viewSuffix}", [
            'title' => 'Sistem Informasi Sales (CRM)',
            'clients' => $clients,
            'totalAllBalance' => $totalAllBalance,
            'totalUsage' => $totalUsage,
            'selectedYear' => $year
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'commission_rate' => $request->filled('commission_rate') ? str_replace(',', '.', $request->commission_rate) : null
        ]);

        $validator = Validator::make($request->all(), [
            'client_name'           => 'required|string|max:255',
            'email'                 => 'nullable|email|max:255',
            'contact_phone'         => 'nullable|string|max:50',
            'contact_birth_date'    => 'nullable|date',
            'contact_address'       => 'nullable|string',
            'contact_position'      => 'nullable|string|max:100',
            'contact_hobby'         => 'nullable|string|max:255',

            // Pharmacist info
            'pharmacist_name'       => 'nullable|string|max:255',
            'pharmacist_license_no' => 'nullable|string|max:255',
            'pharmacist_phone'      => 'nullable|string|max:50',
            'commission_rate'       => 'nullable|numeric|min:0|max:100',

            'customer_name'         => 'required|string|max:255',
            'sales_customer_name'   => 'nullable|string|max:255',
            'company_founded_date'  => 'nullable|date',
            'area'                  => 'nullable|string|max:100',
            'company_address'       => 'nullable|string',
            'bank_name'             => 'nullable|string|max:50',
            'bank_account_number'   => 'nullable|string|max:50',
            'bank_account_name'     => 'nullable|string|max:100',
            'opening_balance'       => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator, 'createClient')->withInput();

        $data = $validator->validated();
        $data['user_id'] = Auth::id();
        $data['ps']      = Auth::user()->name;

        $client = Client::create($data);
        return redirect()->route('crm.show', $client->id)->with('success', 'Data Klien berhasil dibuat!');
    }

    public function show(Client $client, Request $request)
    {
        // 1. Cek Akses Halaman
        $hasAccess = $this->hasFullAccess();
        if ($client->user_id !== Auth::id() && !$hasAccess) abort(403, 'Akses Ditolak.');

        // 2. Tentukan Hak Edit (PIC Klien atau user dengan Full Access bisa edit data)
        $canEdit = ($client->user_id === Auth::id()) || $hasAccess;

        // 3. Data Rekap
        $year = $request->input('year', date('Y'));
        $calc = $this->calculateRecapData($client, $year);

        // 4. Data History
        $historyYear = $request->input('history_year');
        $interactionQuery = $client->interactions()->orderBy('interaction_date', 'desc');
        if ($historyYear) {
            $interactionQuery->whereYear('interaction_date', $historyYear);
        }
        $interactions = $interactionQuery->paginate(10)->withQueryString();

        // 5. Data Activity
        $activityYear = $request->input('activity_year');
        $activityQuery = $client->interactions()
                                ->where('transaction_type', 'ENTERTAIN')
                                ->orderBy('interaction_date', 'desc');
        if ($activityYear) {
            $activityQuery->whereYear('interaction_date', $activityYear);
        }
        $activities = $activityQuery->get();

        $currentBalance = $this->calculateRealTimeBalance($client);

        $agent = new \Jenssegers\Agent\Agent();
        $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';

        $productNames = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get(['product_name', 'product_name_clean'])
            ->map(fn ($p) => trim($p->product_name_clean ?: $p->product_name))
            ->filter()
            ->unique()
            ->values();

        // 5. Data pelanggan dari Command Center, dikunci ke PS klien.
        // Kolom sales sudah EN (customer_name/ps) pasca migrasi Sep 2026.
        // PS dinormalisasi (trim + lowercase) karena sales.ps diketik manual.
        $client->loadMissing('user');
        $lockedPs = trim((string) ($client->ps !== null && $client->ps !== '' ? $client->ps : ($client->user->name ?? '')));

        $salesCustomersQuery = Sales::query()
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->where('ps', '!=', 'Office');
        if ($lockedPs !== '') {
            $salesCustomersQuery->whereRaw('LOWER(TRIM(ps)) = ?', [mb_strtolower($lockedPs)]);
        }
        $salesCustomers = $salesCustomersQuery
            ->distinct()
            ->orderBy('customer_name')
            ->pluck('customer_name');

        return view("users.crm.crm_detail_klien_{$viewSuffix}", [
            'title' => 'Detail Sales: ' . $client->client_name,
            'client' => $client,
            'interactions' => $interactions,
            'activities' => $activities,
            'recap' => $calc['recap'],
            'year' => $year,
            'yearlyTotals' => $calc['totals'],
            'startingBalance' => $calc['starting_balance'],
            'startingLabel'   => $calc['starting_label'],
            'currentBalance' => $currentBalance,
            'historyYear' => $historyYear,
            'activityYear' => $activityYear,
            'canEdit' => $canEdit, // <--- Hak edit profil
            'hasFullAccess' => $hasAccess, // <--- Hak edit transaksi
            'productNames' => $productNames,
            'salesCustomers' => $salesCustomers,
            'lockedPs' => $lockedPs,
        ]);
    }

    public function edit(Client $client)
    {
        // Edit klien dilakukan via modal di halaman detail.
        // Route ini dipertahankan untuk kompatibilitas dan mengarah ke sana.
        if ($client->user_id !== Auth::id() && !$this->hasFullAccess()) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki hak untuk mengedit data Klien ini.');
        }
        return redirect()->route('crm.show', $client->id);
    }

    public function update(Request $request, Client $client)
    {
        // PIC atau yang memiliki akses penuh bisa menyimpan perubahan Klien
        if ($client->user_id !== Auth::id() && !$this->hasFullAccess()) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki hak untuk mengubah data Klien ini.');
        }
        $request->merge([
            'commission_rate' => $request->filled('commission_rate') ? str_replace(',', '.', $request->commission_rate) : null
        ]);

        $validated = $request->validate([
            'client_name'           => 'required|string|max:255',
            'email'                 => 'nullable|email',
            'contact_phone'         => 'nullable|string',
            'contact_birth_date'    => 'nullable|date',
            'contact_address'       => 'nullable|string',
            'contact_position'      => 'nullable|string|max:100',
            'contact_hobby'         => 'nullable|string|max:255',

            // Pharmacist info
            'pharmacist_name'       => 'nullable|string|max:255',
            'pharmacist_license_no' => 'nullable|string|max:255',
            'pharmacist_phone'      => 'nullable|string|max:50',
            'commission_rate'       => 'nullable|numeric|min:0|max:100',

            'customer_name'         => 'required|string|max:255',
            'sales_customer_name'   => 'nullable|string|max:255',
            'company_founded_date'  => 'nullable|date',
            'area'                  => 'nullable|string',
            'company_address'       => 'nullable|string',
            'bank_name'             => 'nullable|string',
            'bank_account_number'   => 'nullable|string',
            'bank_account_name'     => 'nullable|string',
            'opening_balance'       => 'nullable|numeric',
        ]);
        $client->update($validated);
        return redirect()->route('crm.show', $client->id)->with('success', 'Data klien berhasil diperbarui!');
    }

    public function destroyClient(Client $client)
    {
        // Hanya yang memiliki akses penuh yang bisa menghapus Klien
        if (!$this->hasFullAccess()) return redirect()->back()->with('error', 'Akses Ditolak: Hanya Admin/Kepala Divisi yang dapat menghapus Klien.');
        $client->softDelete();
        return redirect()->route('crm.index')->with('success', 'Data klien berhasil dihapus.');
    }
}
