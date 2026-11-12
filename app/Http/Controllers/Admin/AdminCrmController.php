<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Crm\BaseCrmController;
use App\Models\Client;
use App\Models\ClientPicHistory;
use App\Models\User;
use App\Models\ClientInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientAnnualExport;
use App\Exports\MatrixAnnualExport;
use Illuminate\Support\Facades\Cache;

class AdminCrmController extends BaseCrmController
{
    public function __construct()
    {
        // Sengaja tidak memanggil parent::__construct(): guard hasAnyCrmAccess
        // hanya untuk sisi user. Sisi admin sudah dijaga middleware
        // auth + admin + admin.idle di routes, dan mewarisi helper
        // (kalkulator) dari BaseCrmController.
    }

    /**
     * Menampilkan daftar klien beserta ringkasan saldo/transaksi.
     */
    public function index(Request $request)
    {
        $userId = $request->input('user_id');
        $withRelations = ['user', 'interactions'];
        if (Client::hasCustomerIdColumn()) {
            $withRelations[] = 'customer';
        }
        $query = Client::active()->with($withRelations);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $clients = $query->orderBy('updated_at', 'desc')->paginate(15);

        $statsQuery = clone $query; 
        $statsQuery->getQuery()->orders = null;
        $statsQuery->getQuery()->limit = null;
        $statsQuery->getQuery()->offset = null;
        
        $allClients = $statsQuery->get();

        $totalUsage = 0;
        $totalNet   = 0;

        foreach($allClients as $c) {
            $c_net_total   = 0;
            $c_usage_total = 0;

            foreach($c->interactions as $item) {
                if($item->transaction_type == 'IN') {
                    $gross = $item->sales_amount > 0 ? $item->sales_amount : $item->amount;
                    $rate = (float) ($item->commission_rate ?? 0);
                    $c_net_total   += $gross * ($rate / 100);
                } elseif ($item->transaction_type == 'OUT') {
                    $c_usage_total += $item->amount;
                }
            }

            $saldo_klien = ($c->opening_balance ?? 0) + $c_net_total - $c_usage_total;
            $totalUsage += $c_usage_total;
            $totalNet   += $saldo_klien;
        }

        $users = Cache::rememberForever('karyawan_list_dropdown', function () {
            return User::where('role', 'user')->orderBy('name', 'asc')->get(['id', 'name', 'divisi']);
        });

        return view('admin.crm.index', [
            'title'      => 'Monitoring Sales & CRM',
            'clients'    => $clients,
            'users'      => $users,
            'totalUsage' => $totalUsage,
            'totalNet'   => $totalNet,
            'filterUser' => $userId
        ]);
    }

    /**
     * Mengunduh matriks penjualan tahunan seluruh klien ke format Excel.
     */
    public function exportMatrix(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $userId = $request->input('user_id');
        $query = Client::active()->with('interactions')->orderBy('client_name', 'asc');
        if ($userId) $query->where('user_id', $userId);
        $clients = $query->get();
        $months = [];
        for($m=1; $m<=12; $m++) $months[$m] = Carbon::create()->month($m)->translatedFormat('F');
        return Excel::download(new MatrixAnnualExport($clients, $months, $year), 'ADMIN_Laporan_Matrix_Sales_' . $year . '.xlsx');
    }

    /**
     * Menyimpan data klien baru ke database.
     */
    public function store(Request $request)
    {
        $request->merge([
            'commission_rate' => $request->filled('commission_rate') ? str_replace(',', '.', $request->commission_rate) : null,
        ]);

        $validator = Validator::make($request->all(), [
            'user_id'               => 'required|exists:users,id',
            'client_name'          => 'required|string|max:255',
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

        $usersList = Cache::get('karyawan_list_dropdown', collect());
        $salesPerson = $usersList->firstWhere('id', $request->user_id) ?? User::findOrFail($request->user_id);

        if (Client::hasCustomerIdColumn()) {
            $customer = $this->resolveCustomer($data);
            $client = Client::create($this->contactAttributes($data) + [
                'customer_id' => $customer->id,
                'user_id' => $salesPerson->id,
                'ps' => $salesPerson->name,
            ]);
        } else {
            $client = Client::create([
                'user_id' => $salesPerson->id,
                'ps' => $salesPerson->name,
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

        ClientPicHistory::create([
            'client_id' => $client->id,
            'user_id' => $salesPerson->id,
            'pic_name' => $salesPerson->name,
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => null,
            'handover_notes' => 'PIC awal saat pendaftaran klien oleh Admin',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.crm.show', $client->id)->with('success', 'Data Klien berhasil dibuat!');
    }

    public function show(Client $client, Request $request)
    {
        $year = $request->input('year', date('Y'));
        $historyYear = $request->input('history_year');
        $withRelations = ['user', 'interactions', 'picHistories.user', 'picHistories.creator'];
        if (Client::hasCustomerIdColumn()) {
            $withRelations[] = 'customer';
        }
        $client->loadMissing($withRelations);
        $queryInteractions = $client->interactions()->orderBy('interaction_date', 'desc');
        if ($historyYear) $queryInteractions->whereYear('interaction_date', $historyYear);
        $interactions = $queryInteractions->paginate(15)->withQueryString(); 

        $calc = $this->calculateRecapData($client, $year);
        $currentBalance = $this->calculateRealTimeBalance($client);

        $users = Cache::rememberForever('karyawan_list_dropdown', function () {
            return User::where('role', 'user')->orderBy('name', 'asc')->get(['id', 'name', 'divisi']);
        });
        $availableUsers = $this->getMarketingOperationalUsers();

        return view('admin.crm.show', [
            'title'        => 'Detail Admin: ' . $client->client_name,
            'client'       => $client,
            'users'        => $users,
            'availableUsers' => $availableUsers,
            'interactions' => $interactions,
            'recap'        => $calc['recap'],
            'year'         => $year,
            'yearlyTotals' => $calc['totals'],
            'startingBalance' => $calc['starting_balance'], 
            'startingLabel'   => $calc['starting_label'],
            'historyYear'     => $historyYear,
            'currentBalance'  => $currentBalance,
            'picHistories'    => $client->picHistories,
        ]);
    }

    public function edit(Client $client)
    {
        // Edit klien dilakukan via modal di halaman detail.
        // Route ini dipertahankan untuk kompatibilitas dan mengarah ke sana.
        return redirect()->route('admin.crm.show', $client->id);
    }

    public function update(Request $request, Client $client)
    {
        $request->merge([
            'commission_rate' => $request->filled('commission_rate') ? str_replace(',', '.', $request->commission_rate) : null,
        ]);

        $validated = $request->validate([
            'user_id'               => 'required|exists:users,id',
            'client_name'           => 'required|string|max:255',
            'customer_name'         => 'required|string|max:255',
            'sales_customer_name'   => 'nullable|string|max:255',
            'area'                  => 'nullable|string|max:100',
            'email'                 => 'nullable|email',
            'phone'                 => 'nullable|string',
            'address'               => 'nullable|string',
            'birthdate'             => 'nullable|date',
            'position'              => 'nullable|string|max:100',
            'hobby'                 => 'nullable|string|max:255',
            'pharmacist_name'       => 'nullable|string|max:255',
            'pharmacist_license_no' => 'nullable|string|max:255',
            'pharmacist_phone'      => 'nullable|string|max:50',
            'commission_rate'       => 'nullable|numeric|min:0|max:100',
            'company_address'       => 'nullable|string',
            'customer_type'         => 'nullable|string|max:50',
            'company_phone'         => 'nullable|string|max:50',
            'company_email'         => 'nullable|email|max:255',
            'anniversary_date'      => 'nullable|date',
            'npwp'                  => 'nullable|string|max:50',
            'bank_name'             => 'nullable|string',
            'bank_account_number'   => 'nullable|string',
            'bank_account_name'     => 'nullable|string',
            'opening_balance'       => 'nullable|numeric',
            'handover_date'         => 'nullable|date',
            'handover_notes'        => 'nullable|string|max:1000',
        ]);

        $pic = User::find($validated['user_id']);
        $newPs = $pic ? trim($pic->name) : trim((string)$client->ps);
        $oldPs = trim((string)($client->ps ?? ''));
        $handoverDate = $request->filled('handover_date') ? Carbon::parse($request->handover_date) : Carbon::today();

        if (Client::hasCustomerIdColumn()) {
            $customer = $this->resolveCustomer($validated);
            $client->update($this->contactAttributes($validated) + [
                'customer_id' => $customer->id,
                'user_id' => $pic ? $pic->id : $client->user_id,
                'ps' => $newPs,
            ]);
        } else {
            $client->update([
                'user_id' => $pic ? $pic->id : $client->user_id,
                'ps' => $newPs,
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

        // Jika PIC berubah, catat riwayat serah terima
        if ($newPs !== '' && strtolower($oldPs) !== strtolower($newPs)) {
            DB::transaction(function () use ($client, $pic, $newPs, $handoverDate, $request) {
                ClientPicHistory::where('client_id', $client->id)
                    ->whereNull('end_date')
                    ->update([
                        'end_date' => $handoverDate->copy()->subDay()->toDateString(),
                    ]);

                ClientPicHistory::create([
                    'client_id' => $client->id,
                    'user_id' => $pic ? $pic->id : $client->user_id,
                    'pic_name' => $newPs,
                    'start_date' => $handoverDate->toDateString(),
                    'end_date' => null,
                    'handover_notes' => $request->input('handover_notes', 'Pembaruan PIC via Admin CRM'),
                    'created_by' => Auth::id(),
                ]);
            });
        }

        return redirect()->back()->with('success', 'Data klien berhasil diperbarui!');
    }

    public function handoverPic(Request $request, Client $client)
    {
        $validated = $request->validate([
            'new_user_id' => 'required|exists:users,id',
            'handover_date' => 'required|date',
            'handover_notes' => 'nullable|string|max:1000',
        ]);

        $pic = User::findOrFail($validated['new_user_id']);
        $newPs = trim($pic->name);
        $oldPs = trim((string) ($client->ps ?? ''));
        $handoverDate = Carbon::parse($validated['handover_date']);

        if (strtolower($oldPs) === strtolower($newPs)) {
            return redirect()->back()->with('warning', 'PIC baru sama dengan PIC saat ini.');
        }

        DB::transaction(function () use ($client, $pic, $newPs, $handoverDate, $validated) {
            ClientPicHistory::where('client_id', $client->id)
                ->whereNull('end_date')
                ->update([
                    'end_date' => $handoverDate->copy()->subDay()->toDateString(),
                ]);

            ClientPicHistory::create([
                'client_id' => $client->id,
                'user_id' => $pic->id,
                'pic_name' => $newPs,
                'start_date' => $handoverDate->toDateString(),
                'end_date' => null,
                'handover_notes' => $validated['handover_notes'] ?? 'Serah terima PIC via Admin CRM',
                'created_by' => Auth::id(),
            ]);

            $client->ps = $newPs;
            $client->user_id = $pic->id;
            $client->save();
        });

        return redirect()->route('admin.crm.show', $client->id)->with('success', "Serah terima PIC ke '{$newPs}' efektif per " . $handoverDate->format('d/m/Y') . " berhasil disimpan!");
    }

    public function storeInteraction(Request $request)
    {
        $request->merge(['sales_amount' => str_replace('.', '', (string) $request->sales_amount), 'commission_rate' => str_replace(',', '.', (string) $request->commission_rate)]);
        $request->validate(['client_id' => 'required|exists:clients,id', 'product_name' => 'required|string|max:255', 'sales_amount' => 'required|numeric|min:0', 'interaction_date' => 'required|date', 'notes' => 'nullable|string']);
        // Komisi dikunci dari master kontak (keputusan: single source = clients).
        $rateClient = Client::active()->findOrFail($request->client_id);
        ClientInteraction::create(['client_id' => $request->client_id, 'customer_id' => $rateClient->customer_id, 'transaction_type' => 'IN', 'product_name' => $request->product_name, 'interaction_date' => $request->interaction_date, 'sales_amount' => $request->sales_amount, 'amount' => $request->sales_amount, 'commission_rate' => (float) $rateClient->commission_rate, 'notes' => $request->notes]);
        return redirect()->back()->with('success', 'Transaksi sales berhasil ditambahkan!');
    }

    public function storeSupport(Request $request)
    {
        $request->merge(['amount' => str_replace('.', '', (string) $request->amount)]);
        $request->validate(['client_id' => 'required|exists:clients,id', 'purpose' => 'required|string|max:255', 'amount' => 'required|numeric|min:0', 'interaction_date' => 'required|date', 'notes' => 'nullable|string']);
        $outClient = Client::active()->findOrFail($request->client_id);
        ClientInteraction::create(['client_id' => $request->client_id, 'customer_id' => $outClient->customer_id, 'transaction_type' => 'OUT', 'product_name' => 'USAGE : ' . $outClient->client_name . ' - ' . ($outClient->customer?->customer_name ?? '') . ' (' . $request->purpose . ')', 'interaction_date' => $request->interaction_date, 'sales_amount' => 0, 'amount' => $request->amount, 'notes' => $request->notes]);
        return redirect()->back()->with('success', 'Dana support berhasil dicatat!');
    }

    public function storeEntertain(Request $request)
    {
        $request->merge(['amount' => str_replace('.', '', (string) $request->amount)]);
        $request->validate(['client_id' => 'required|exists:clients,id', 'interaction_date' => 'required|date', 'notes' => 'required|string|max:2000', 'amount' => 'required|numeric|min:0']);
        $entClient = Client::active()->findOrFail($request->client_id);
        ClientInteraction::create(['client_id' => $request->client_id, 'customer_id' => $entClient->customer_id, 'transaction_type' => 'ENTERTAIN', 'product_name' => 'ENTERTAIN : ' . \Illuminate\Support\Str::limit(trim((string) $request->notes), 200, ''), 'interaction_date' => $request->interaction_date, 'sales_amount' => 0, 'amount' => $request->amount, 'notes' => $request->notes]);
        return redirect()->back()->with('success', 'Aktivitas berhasil dicatat.');
    }

    public function destroyClient(Client $client)
    {
        $client->softDelete();
        return redirect()->route('admin.crm.index')->with('success', 'Data klien berhasil dihapus.');
    }

    public function destroyInteraction(ClientInteraction $interaction)
    {
        $interaction->delete();
        return redirect()->back()->with('success', 'Transaksi berhasil dihapus.');
    }

    public function updateInteraction(Request $request, ClientInteraction $interaction)
    {
        $inputNominal = $request->input('sales_amount') ?? $request->input('amount');
        $cleanNominal = str_replace('.', '', (string) $inputNominal);
        if ($interaction->transaction_type == 'IN') {
            $request->merge(['sales_amount' => $cleanNominal]);
            $request->validate(['product_name' => 'required|string|max:255', 'sales_amount' => 'required|numeric|min:0', 'interaction_date' => 'required|date', 'notes' => 'nullable|string']);
            // Komisi dikunci dari master kontak.
            $interaction->update(['product_name' => $request->product_name, 'interaction_date' => $request->interaction_date, 'sales_amount' => $request->sales_amount, 'amount' => $request->sales_amount, 'commission_rate' => (float) ($interaction->client->commission_rate ?? 0), 'notes' => $request->notes]);
        } elseif ($interaction->transaction_type == 'OUT') {
            $request->merge(['amount' => $cleanNominal]);
            $request->validate(['purpose' => 'required|string|max:255', 'amount' => 'required|numeric|min:0', 'interaction_date' => 'required|date', 'notes' => 'nullable|string']);
            $interaction->update(['product_name' => 'USAGE : ' . ($interaction->client->client_name ?? '') . ' - ' . ($interaction->client->customer?->customer_name ?? '') . ' (' . $request->purpose . ')', 'interaction_date' => $request->interaction_date, 'sales_amount' => 0, 'amount' => $request->amount, 'notes' => $request->notes]);
        } elseif ($interaction->transaction_type == 'ENTERTAIN') {
            $request->merge(['amount' => $cleanNominal]);
            $request->validate(['amount' => 'required|numeric|min:0', 'interaction_date' => 'required|date', 'notes' => 'required|string|max:2000']);
            $interaction->update(['product_name' => 'ENTERTAIN : ' . \Illuminate\Support\Str::limit(trim((string) $request->notes), 200, ''), 'interaction_date' => $request->interaction_date, 'sales_amount' => 0, 'amount' => $request->amount, 'notes' => $request->notes]);
        }
        return redirect()->back()->with('success', 'Data transaksi berhasil diperbarui.');
    }

    // Kalkulator rekap/saldo diwarisi dari BaseCrmController (sumber tunggal).

    /**
     * Mengekspor rekapitulasi data spesifik klien ke Excel.
     */
    public function exportClientRecap(Client $client, Request $request)
    {
        $year = $request->input('year', date('Y'));
        $calc = $this->calculateRecapData($client, $year);
    
        // Pembersihan karakter khusus pada nama klien untuk nama file aman
        $safeName = str_replace(['/', '\\'], '_', $client->client_name);
        $fileName = 'Laporan_Sales_' . str_replace(' ', '_', $safeName) . '_' . $year . '.xlsx';
    
        return Excel::download(new ClientAnnualExport(
            $client,
            $calc['recap'],
            $year,
            $calc['totals']
        ), $fileName);
    }
}
