<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Crm\BaseCrmController;
use App\Models\Client;
use App\Models\User;
use App\Models\ClientInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $query = Client::active()->with(['user', 'interactions']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $clients = $query->orderBy('updated_at', 'desc')->paginate(15);

        $statsQuery = clone $query; 
        $statsQuery->getQuery()->orders = null;
        $statsQuery->getQuery()->limit = null;
        $statsQuery->getQuery()->offset = null;
        
        $allClients = $statsQuery->get();

        $totalOmset = 0; 
        $totalNet   = 0; 

        foreach($allClients as $c) {
            $c_gross_total = 0;
            $c_net_total   = 0; 
            $c_usage_total = 0; 

            foreach($c->interactions as $item) {
                if($item->transaction_type == 'IN') {
                    $gross = $item->sales_amount > 0 ? $item->sales_amount : $item->amount;
                    $rate = (float) ($item->commission_rate ?? 0);
                    $value = $gross * ($rate / 100);
                    $c_gross_total += $gross;
                    $c_net_total   += $value;
                } elseif ($item->transaction_type == 'OUT') {
                    $c_usage_total += $item->amount;
                }
            }
            
            $saldo_klien = ($c->opening_balance ?? 0) + $c_net_total - $c_usage_total;
            $totalOmset += $c_gross_total;
            $totalNet   += $saldo_klien;
        }

        $users = Cache::rememberForever('karyawan_list_dropdown', function () {
            return User::where('role', 'user')->orderBy('name', 'asc')->get(['id', 'name', 'divisi']);
        });

        return view('admin.crm.index', [
            'title'      => 'Monitoring Sales & CRM',
            'clients'    => $clients,
            'users'      => $users,
            'totalOmset' => $totalOmset,
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
        $validator = Validator::make($request->all(), [
            'user_id'               => 'required|exists:users,id',
            'client_name'          => 'required|string|max:255',
            'email'                 => 'nullable|email|max:255',
            'contact_phone'         => 'nullable|string|max:50',
            'contact_birth_date'    => 'nullable|date',
            'contact_address'       => 'nullable|string',
            'contact_position'      => 'nullable|string|max:100',
            'contact_hobby'         => 'nullable|string|max:255',
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

        $usersList = Cache::get('karyawan_list_dropdown', collect());
        $salesPerson = $usersList->firstWhere('id', $request->user_id) ?? User::findOrFail($request->user_id);

        $data['user_id'] = $salesPerson->id;
        $data['ps']      = $salesPerson->name;
        $client = Client::create($data);
        return redirect()->route('admin.crm.show', $client->id)->with('success', 'Data Klien berhasil dibuat!');
    }

    public function show(Client $client, Request $request)
    {
        $year = $request->input('year', date('Y'));
        $historyYear = $request->input('history_year');
        $queryInteractions = $client->interactions()->orderBy('interaction_date', 'desc');
        if ($historyYear) $queryInteractions->whereYear('interaction_date', $historyYear);
        $interactions = $queryInteractions->paginate(15)->withQueryString(); 

        $calc = $this->calculateRecapData($client, $year);
        $currentBalance = $this->calculateRealTimeBalance($client);

        return view('admin.crm.show', [
            'title'        => 'Detail Admin: ' . $client->client_name,
            'client'       => $client,
            'interactions' => $interactions,
            'recap'        => $calc['recap'],
            'year'         => $year,
            'yearlyTotals' => $calc['totals'],
            'startingBalance' => $calc['starting_balance'], 
            'startingLabel'   => $calc['starting_label'],
            'historyYear'     => $historyYear,
            'currentBalance'  => $currentBalance // Kirim variabel baru
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
        $validated = $request->validate([
            'client_name'           => 'required|string|max:255',
            'customer_name'         => 'required|string|max:255',
            'sales_customer_name'   => 'nullable|string|max:255',
            'area'                  => 'nullable|string|max:100',
            'email'                 => 'nullable|email',
            'contact_phone'         => 'nullable|string',
            'contact_address'       => 'nullable|string',
            'contact_position'      => 'nullable|string|max:100',
            'contact_hobby'         => 'nullable|string|max:255',
            'pharmacist_name'       => 'nullable|string|max:255',
            'pharmacist_license_no' => 'nullable|string|max:255',
            'pharmacist_phone'      => 'nullable|string|max:50',
            'commission_rate'       => 'nullable|numeric|min:0|max:100',
            'company_address'       => 'nullable|string',
            'bank_name'             => 'nullable|string',
            'bank_account_number'   => 'nullable|string',
            'bank_account_name'     => 'nullable|string',
            'opening_balance'       => 'nullable|numeric',
            'company_founded_date'  => 'nullable|date',
            'contact_birth_date'    => 'nullable|date',
        ]);
        $client->update($validated);
        return redirect()->back()->with('success', 'Data klien berhasil diperbarui!');
    }

    public function storeInteraction(Request $request)
    {
        $request->merge(['sales_amount' => str_replace('.', '', $request->sales_amount), 'commission_rate' => str_replace(',', '.', $request->commission_rate)]);
        $request->validate(['client_id' => 'required|exists:clients,id', 'product_name' => 'required|string|max:255', 'sales_amount' => 'required|numeric|min:0', 'commission_rate' => 'required|numeric|min:0|max:100', 'interaction_date' => 'required|date', 'notes' => 'nullable|string']);
        ClientInteraction::create(['client_id' => $request->client_id, 'transaction_type' => 'IN', 'product_name' => $request->product_name, 'interaction_date' => $request->interaction_date, 'sales_amount' => $request->sales_amount, 'amount' => $request->sales_amount, 'commission_rate' => $request->commission_rate, 'notes' => $request->notes]);
        return redirect()->back()->with('success', 'Transaksi sales berhasil ditambahkan!');
    }

    public function storeSupport(Request $request)
    {
        $request->merge(['amount' => str_replace('.', '', $request->amount)]);
        $request->validate(['client_id' => 'required|exists:clients,id', 'purpose' => 'required|string|max:255', 'amount' => 'required|numeric|min:0', 'interaction_date' => 'required|date', 'notes' => 'nullable|string']);
        $outClient = Client::active()->findOrFail($request->client_id);
        ClientInteraction::create(['client_id' => $request->client_id, 'transaction_type' => 'OUT', 'product_name' => 'USAGE : ' . $outClient->client_name . ' - ' . $outClient->customer_name . ' (' . $request->purpose . ')', 'interaction_date' => $request->interaction_date, 'sales_amount' => 0, 'amount' => $request->amount, 'notes' => $request->notes]);
        return redirect()->back()->with('success', 'Dana support berhasil dicatat!');
    }

    public function storeEntertain(Request $request)
    {
        $request->merge(['amount' => str_replace('.', '', $request->amount)]);
        $request->validate(['client_id' => 'required|exists:clients,id', 'interaction_date' => 'required|date', 'notes' => 'required|string|max:2000', 'amount' => 'required|numeric|min:0']);
        ClientInteraction::create(['client_id' => $request->client_id, 'transaction_type' => 'ENTERTAIN', 'product_name' => 'ENTERTAIN : ' . \Illuminate\Support\Str::limit(trim((string) $request->notes), 200, ''), 'interaction_date' => $request->interaction_date, 'sales_amount' => 0, 'amount' => $request->amount, 'notes' => $request->notes]);
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
        $cleanNominal = str_replace('.', '', $inputNominal);
        if ($interaction->transaction_type == 'IN') {
            $request->merge(['sales_amount' => $cleanNominal, 'commission_rate' => str_replace(',', '.', $request->input('commission_rate'))]);
            $request->validate(['product_name' => 'required|string|max:255', 'sales_amount' => 'required|numeric|min:0', 'commission_rate' => 'required|numeric|min:0|max:100', 'interaction_date' => 'required|date', 'notes' => 'nullable|string']);
            $interaction->update(['product_name' => $request->product_name, 'interaction_date' => $request->interaction_date, 'sales_amount' => $request->sales_amount, 'amount' => $request->sales_amount, 'commission_rate' => $request->commission_rate, 'notes' => $request->notes]);
        } elseif ($interaction->transaction_type == 'OUT') {
            $request->merge(['amount' => $cleanNominal]);
            $request->validate(['purpose' => 'required|string|max:255', 'amount' => 'required|numeric|min:0', 'interaction_date' => 'required|date', 'notes' => 'nullable|string']);
            $interaction->update(['product_name' => 'USAGE : ' . ($interaction->client->client_name ?? '') . ' - ' . ($interaction->client->customer_name ?? '') . ' (' . $request->purpose . ')', 'interaction_date' => $request->interaction_date, 'sales_amount' => 0, 'amount' => $request->amount, 'notes' => $request->notes]);
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