<?php

namespace App\Http\Controllers\Crm;

use App\Models\Client;
use App\Models\ClientPicHistory;
use App\Models\Product;
use App\Models\Sales;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * CRUD data klien CRM (Sistem Informasi Sales).
 */
class ClientController extends BaseCrmController
{
    public function index(Request $request)
    {
        $year = $request->input('year', 'all');
        // Urut PIC (ps) dulu supaya klien dengan PIC sama berderet,
        // lalu nama RS dan nama user di dalam tiap grup PIC.
        if (Client::hasCustomerIdColumn()) {
            $query = Client::where('clients.is_deleted', 0)
                ->with(['customer', 'interactions'])
                ->leftJoin('customers', 'customers.id', '=', 'clients.customer_id')
                ->orderByRaw("COALESCE(NULLIF(TRIM(clients.ps), ''), 'zzz') ASC")
                ->orderBy('customers.customer_name', 'asc')
                ->orderBy('clients.client_name', 'asc')
                ->select('clients.*');
        } else {
            $query = Client::where('clients.is_deleted', 0)
                ->with('interactions')
                ->orderByRaw("COALESCE(NULLIF(TRIM(clients.ps), ''), 'zzz') ASC")
                ->orderBy('clients.customer_name', 'asc')
                ->orderBy('clients.client_name', 'asc')
                ->select('clients.*');
        }

        if (!$this->hasFullAccess()) {
            $query->where('clients.user_id', Auth::id());
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

        return view("users.sales.crm.client-directory-{$viewSuffix}", [
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
            'phone'                 => 'nullable|string|max:50',
            'birthdate'             => 'nullable|date',
            'address'               => 'nullable|string',
            'position'              => 'nullable|string|max:100',
            'hobby'                 => 'nullable|string|max:255',

            'commission_rate'       => 'nullable|numeric|min:0|max:100',

            'customer_name'         => 'required|string|max:255',
            'sales_customer_name'   => 'nullable|string|max:255',
            'company_address'       => 'nullable|string',
            'area'                  => 'nullable|string|max:100',
            'customer_type'         => 'nullable|string|max:50',
            'company_phone'         => 'nullable|string|max:50',
            'company_email'         => 'nullable|email|max:255',
            'anniversary_date'      => 'nullable|date',
            'npwp'                  => 'nullable|string|max:50',
            'pharmacist_name'       => 'nullable|string|max:255',
            'pharmacist_license_no' => 'nullable|string|max:255',
            'pharmacist_phone'      => 'nullable|string|max:50',

            'bank_name'             => 'nullable|string|max:50',
            'bank_account_number'   => 'nullable|string|max:50',
            'bank_account_name'     => 'nullable|string|max:100',
            'opening_balance'       => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator, 'createClient')->withInput();

        $data = $validator->validated();
        if (Client::hasCustomerIdColumn()) {
            $customer = $this->resolveCustomer($data);
            $client = Client::create($this->contactAttributes($data) + [
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'ps' => Auth::user()->name,
            ]);
        } else {
            $client = Client::create([
                'user_id' => Auth::id(),
                'ps' => Auth::user()->name,
                'client_name' => $data['client_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'contact_phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'contact_address' => $data['address'] ?? null,
                'birthdate' => $data['birthdate'] ?? null,
                'contact_birth_date' => $data['birthdate'] ?? null,
                'position' => $data['position'] ?? null,
                'contact_position' => $data['position'] ?? null,
                'hobby' => $data['hobby'] ?? null,
                'contact_hobby' => $data['hobby'] ?? null,
                'commission_rate' => $data['commission_rate'] ?? 0,
                'customer_name' => $data['customer_name'],
                'sales_customer_name' => $data['sales_customer_name'] ?? null,
                'company_address' => $data['company_address'] ?? null,
                'area' => $data['area'] ?? null,
                'customer_type' => $data['customer_type'] ?? null,
                'company_phone' => $data['company_phone'] ?? null,
                'company_email' => $data['company_email'] ?? null,
                'anniversary_date' => $data['anniversary_date'] ?? null,
                'company_founded_date' => $data['anniversary_date'] ?? null,
                'npwp' => $data['npwp'] ?? null,
                'pharmacist_name' => $data['pharmacist_name'] ?? null,
                'pharmacist_license_no' => $data['pharmacist_license_no'] ?? null,
                'pharmacist_phone' => $data['pharmacist_phone'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_number' => $data['bank_account_number'] ?? null,
                'bank_account_name' => $data['bank_account_name'] ?? null,
                'opening_balance' => $data['opening_balance'] ?? 0,
            ]);
        }

        // Catat entri PIC awal pada riwayat
        ClientPicHistory::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'pic_name' => Auth::user()->name,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => null,
            'handover_notes' => 'PIC awal saat pendaftaran klien',
            'created_by' => Auth::id(),
        ]);

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

        // 5. Data pelanggan dari Command Center, berbasis daftar PIC dalam riwayat & saat ini.
        // Kolom sales sudah EN (customer_name/ps) pasca migrasi Sep 2026.
        // PS dinormalisasi (trim + lowercase) karena sales.ps diketik manual.
        $loadRelations = ['user', 'picHistories.user', 'picHistories.creator'];
        if (Client::hasCustomerIdColumn()) {
            $loadRelations[] = 'customer';
        }
        $client->loadMissing($loadRelations);

        $lockedPs = trim((string) ($client->ps !== null && $client->ps !== '' ? $client->ps : ($client->user->name ?? '')));

        // Kumpulkan semua nama PIC yang pernah atau sedang memegang klien ini
        $picNames = $client->picHistories->pluck('pic_name')->toArray();
        if ($lockedPs !== '') {
            $picNames[] = $lockedPs;
        }
        $picNames = array_values(array_unique(array_filter(array_map('trim', $picNames))));

        $salesCustomersQuery = Sales::query()
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->where('ps', '!=', 'Office');
        if (!empty($picNames)) {
            $salesCustomersQuery->where(function ($q) use ($picNames) {
                foreach ($picNames as $pic) {
                    $q->orWhereRaw('LOWER(TRIM(ps)) = ?', [mb_strtolower($pic)]);
                }
            });
        }
        $salesCustomers = $salesCustomersQuery
            ->distinct()
            ->orderBy('customer_name')
            ->pluck('customer_name');

        // Daftar karyawan divisi Marketing & Operasional untuk dropdown serah terima PIC
        $availableUsers = $this->getMarketingOperationalUsers();

        return view("users.sales.crm.client-ledger-{$viewSuffix}", [
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
            'picNames' => $picNames,
            'picHistories' => $client->picHistories,
            'availableUsers' => $availableUsers,
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
            'phone'                 => 'nullable|string',
            'birthdate'             => 'nullable|date',
            'address'               => 'nullable|string',
            'position'              => 'nullable|string|max:100',
            'hobby'                 => 'nullable|string|max:255',

            'commission_rate'       => 'nullable|numeric|min:0|max:100',

            'customer_name'         => 'required|string|max:255',
            'sales_customer_name'   => 'nullable|string|max:255',
            'area'                  => 'nullable|string',
            'company_address'       => 'nullable|string',
            'customer_type'         => 'nullable|string|max:50',
            'company_phone'         => 'nullable|string|max:50',
            'company_email'         => 'nullable|email|max:255',
            'anniversary_date'      => 'nullable|date',
            'npwp'                  => 'nullable|string|max:50',
            'pharmacist_name'       => 'nullable|string|max:255',
            'pharmacist_license_no' => 'nullable|string|max:255',
            'pharmacist_phone'      => 'nullable|string|max:50',

            'bank_name'             => 'nullable|string',
            'bank_account_number'   => 'nullable|string',
            'bank_account_name'     => 'nullable|string',
            'opening_balance'       => 'nullable|numeric',
            'ps'                    => 'nullable|string|max:255',
            'handover_date'         => 'nullable|date',
            'handover_notes'        => 'nullable|string|max:1000',
            'user_id'               => 'nullable|exists:users,id',
        ]);
        if (Client::hasCustomerIdColumn()) {
            $customer = $this->resolveCustomer($validated);
            $client->update($this->contactAttributes($validated) + ['customer_id' => $customer->id]);
        } else {
            $client->update([
                'client_name' => $validated['client_name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'contact_phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'contact_address' => $validated['address'] ?? null,
                'birthdate' => $validated['birthdate'] ?? null,
                'contact_birth_date' => $validated['birthdate'] ?? null,
                'position' => $validated['position'] ?? null,
                'contact_position' => $validated['position'] ?? null,
                'hobby' => $validated['hobby'] ?? null,
                'contact_hobby' => $validated['hobby'] ?? null,
                'commission_rate' => $validated['commission_rate'] ?? 0,
                'customer_name' => $validated['customer_name'],
                'sales_customer_name' => $validated['sales_customer_name'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'area' => $validated['area'] ?? null,
                'customer_type' => $validated['customer_type'] ?? null,
                'company_phone' => $validated['company_phone'] ?? null,
                'company_email' => $validated['company_email'] ?? null,
                'anniversary_date' => $validated['anniversary_date'] ?? null,
                'company_founded_date' => $validated['anniversary_date'] ?? null,
                'npwp' => $validated['npwp'] ?? null,
                'pharmacist_name' => $validated['pharmacist_name'] ?? null,
                'pharmacist_license_no' => $validated['pharmacist_license_no'] ?? null,
                'pharmacist_phone' => $validated['pharmacist_phone'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_account_name' => $validated['bank_account_name'] ?? null,
                'opening_balance' => $validated['opening_balance'] ?? 0,
            ]);
        }

        // Cek apakah ada serah terima / penggantian PIC via form edit
        $oldPs = trim((string)($client->ps ?? ''));
        $newPs = $request->filled('ps') ? trim((string)$request->ps) : '';
        $handoverDate = $request->filled('handover_date') ? Carbon::parse($request->handover_date) : Carbon::today();
        $handoverUserId = $request->input('user_id');

        if ($newPs !== '' && strtolower($oldPs) !== strtolower($newPs)) {
            DB::transaction(function () use ($client, $newPs, $handoverUserId, $handoverDate, $request) {
                ClientPicHistory::where('client_id', $client->id)
                    ->whereNull('end_date')
                    ->update([
                        'end_date' => $handoverDate->copy()->subDay()->toDateString(),
                    ]);

                ClientPicHistory::create([
                    'client_id' => $client->id,
                    'user_id' => $handoverUserId ?: $client->user_id,
                    'pic_name' => $newPs,
                    'start_date' => $handoverDate->toDateString(),
                    'end_date' => null,
                    'handover_notes' => $request->input('handover_notes', 'Pembaruan PIC via edit profil klien'),
                    'created_by' => Auth::id(),
                ]);

                $client->ps = $newPs;
                if ($handoverUserId) {
                    $client->user_id = $handoverUserId;
                }
                $client->save();
            });
        }

        return redirect()->route('crm.show', $client->id)->with('success', 'Data klien berhasil diperbarui!');
    }

    public function handoverPic(Request $request, Client $client)
    {
        if ($client->user_id !== Auth::id() && !$this->hasFullAccess()) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki hak untuk mengganti PIC Klien ini.');
        }

        $validated = $request->validate([
            'new_ps' => 'required|string|max:255',
            'new_user_id' => 'nullable|exists:users,id',
            'handover_date' => 'required|date',
            'handover_notes' => 'nullable|string|max:1000',
        ]);

        $newPs = trim($validated['new_ps']);
        $newUserId = $validated['new_user_id'] ?? null;
        $handoverDate = Carbon::parse($validated['handover_date']);
        $oldPs = trim((string) ($client->ps ?? ''));

        if ($newUserId && empty($newPs)) {
            $u = User::find($newUserId);
            $newPs = $u ? $u->name : $newPs;
        }

        if (strtolower($oldPs) === strtolower($newPs)) {
            return redirect()->back()->with('warning', 'PIC baru sama dengan PIC saat ini.');
        }

        DB::transaction(function () use ($client, $newPs, $newUserId, $handoverDate, $validated) {
            ClientPicHistory::where('client_id', $client->id)
                ->whereNull('end_date')
                ->update([
                    'end_date' => $handoverDate->copy()->subDay()->toDateString(),
                ]);

            ClientPicHistory::create([
                'client_id' => $client->id,
                'user_id' => $newUserId ?: $client->user_id,
                'pic_name' => $newPs,
                'start_date' => $handoverDate->toDateString(),
                'end_date' => null,
                'handover_notes' => $validated['handover_notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $client->ps = $newPs;
            if ($newUserId) {
                $client->user_id = $newUserId;
            }
            $client->save();
        });

        return redirect()->route('crm.show', $client->id)->with('success', "Serah terima PIC ke '{$newPs}' efektif per " . $handoverDate->format('d/m/Y') . " berhasil disimpan!");
    }

    public function destroyClient(Client $client)
    {
        // Hanya yang memiliki akses penuh yang bisa menghapus Klien
        if (!$this->hasFullAccess()) return redirect()->back()->with('error', 'Akses Ditolak: Hanya Admin/Kepala Divisi yang dapat menghapus Klien.');
        $client->softDelete();
        return redirect()->route('crm.index')->with('success', 'Data klien berhasil dihapus.');
    }
}
