<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Models\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientAnnualExport;
use App\Exports\MatrixAnnualExport;

class AdminCrmController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->input('user_id');
        $query = Client::with(['user', 'interactions']);

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
                if($item->jenis_transaksi == 'IN') {
                    $gross = $item->nilai_sales > 0 ? $item->nilai_sales : $item->nilai_kontribusi;
                    $rate = $item->komisi ?? 0;
                    if (!$rate && preg_match('/\[Rate:([\d\.]+)\]/', $item->catatan, $m)) {
                        $rate = floatval($m[1]);
                    }
                    $value = $gross * ($rate / 100);
                    $c_gross_total += $gross;
                    $c_net_total   += $value;
                } elseif ($item->jenis_transaksi == 'OUT') {
                    $c_usage_total += $item->nilai_kontribusi;
                }
            }
            
            $saldo_klien = ($c->saldo_awal ?? 0) + $c_net_total - $c_usage_total;
            $totalOmset += $c_gross_total;
            $totalNet   += $saldo_klien;
        }

        $users = User::orderBy('name', 'asc')->get(); 

        return view('admin.crm.index', [
            'title'      => 'Monitoring Sales & CRM',
            'clients'    => $clients,
            'users'      => $users,
            'totalOmset' => $totalOmset,
            'totalNet'   => $totalNet,
            'filterUser' => $userId
        ]);
    }

    public function exportMatrix(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $userId = $request->input('user_id');
        $query = Client::with('interactions')->orderBy('nama_user', 'asc');
        if ($userId) $query->where('user_id', $userId);
        $clients = $query->get();
        $months = [];
        for($m=1; $m<=12; $m++) $months[$m] = Carbon::create()->month($m)->translatedFormat('F');
        return Excel::download(new MatrixAnnualExport($clients, $months, $year), 'ADMIN_Laporan_Matrix_Sales_' . $year . '.xlsx');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
            'nama_user'         => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'no_telpon'         => 'nullable|string|max:50',
            'tanggal_lahir'     => 'nullable|date',
            'alamat_user'       => 'nullable|string', 
            'jabatan'           => 'nullable|string|max:100',
            'hobby_client'      => 'nullable|string|max:255',
            'nama_perusahaan'   => 'required|string|max:255',
            'tanggal_berdiri'   => 'nullable|date',
            'area'              => 'nullable|string|max:100',
            'alamat_perusahaan' => 'nullable|string', 
            'bank'              => 'nullable|string|max:50',
            'no_rekening'       => 'nullable|string|max:50',
            'nama_di_rekening'  => 'nullable|string|max:100',   
            'saldo_awal'        => 'nullable|numeric|min:0',
        ]);
        if ($validator->fails()) return redirect()->back()->withErrors($validator, 'createClient')->withInput();
        $data = $validator->validated();
        $salesPerson = User::findOrFail($request->user_id);
        $data['user_id'] = $salesPerson->id;
        $data['pic']     = $salesPerson->name;
        $client = Client::create($data);
        return redirect()->route('admin.crm.show', $client->id)->with('success', 'Data Klien berhasil dibuat!');
    }

    public function show(Client $client, Request $request)
    {
        $year = $request->input('year', date('Y'));
        $historyYear = $request->input('history_year');
        $queryInteractions = $client->interactions()->orderBy('tanggal_interaksi', 'desc');
        if ($historyYear) $queryInteractions->whereYear('tanggal_interaksi', $historyYear);
        $interactions = $queryInteractions->paginate(15)->withQueryString(); 

        $calc = $this->calculateRecapData($client, $year);
        
        // TAMBAHAN: Hitung saldo real-time untuk header
        $currentBalance = $this->calculateRealTimeBalance($client);

        return view('admin.crm.show', [
            'title'        => 'Detail Admin: ' . $client->nama_user,
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

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'nama_user'       => 'required|string|max:255',
            'nama_perusahaan' => 'required|string|max:255',
            'area'            => 'nullable|string|max:100',
            'email'           => 'nullable|email',
            'no_telpon'       => 'nullable|string',
            'alamat_user'     => 'nullable|string',
            'jabatan'         => 'nullable|string|max:100',
            'hobby_client'    => 'nullable|string|max:255',
            'alamat_perusahaan' => 'nullable|string',
            'bank'            => 'nullable|string',
            'no_rekening'     => 'nullable|string',
            'nama_di_rekening'=> 'nullable|string',
            'saldo_awal'      => 'nullable|numeric',
            'tanggal_berdiri' => 'nullable|date',
            'tanggal_lahir'   => 'nullable|date',
        ]);
        $client->update($validated);
        return redirect()->back()->with('success', 'Data klien berhasil diperbarui!');
    }

    public function storeInteraction(Request $request)
    {
        $request->merge(['nilai_sales' => str_replace('.', '', $request->nilai_sales), 'komisi' => str_replace(',', '.', $request->komisi)]);
        $request->validate(['client_id' => 'required|exists:clients,id', 'nama_produk' => 'required|string|max:255', 'nilai_sales' => 'required|numeric|min:0', 'komisi' => 'required|numeric|min:0|max:100', 'tanggal_interaksi' => 'required|date', 'catatan' => 'nullable|string']);
        Interaction::create(['user_id' => Auth::id(), 'client_id' => $request->client_id, 'jenis_transaksi' => 'IN', 'nama_produk' => $request->nama_produk, 'tanggal_interaksi' => $request->tanggal_interaksi, 'nilai_sales' => $request->nilai_sales, 'nilai_kontribusi' => $request->nilai_sales, 'komisi' => $request->komisi, 'catatan' => "[Rate:" . $request->komisi . "] " . $request->catatan]);
        return redirect()->back()->with('success', 'Transaksi sales berhasil ditambahkan!');
    }

    public function storeSupport(Request $request)
    {
        $request->merge(['nominal' => str_replace('.', '', $request->nominal)]);
        $request->validate(['client_id' => 'required|exists:clients,id', 'keperluan' => 'required|string|max:255', 'nominal' => 'required|numeric|min:0', 'tanggal_interaksi' => 'required|date', 'catatan' => 'nullable|string']);
        Interaction::create(['user_id' => Auth::id(), 'client_id' => $request->client_id, 'jenis_transaksi' => 'OUT', 'nama_produk' => 'USAGE : ' . $request->keperluan, 'tanggal_interaksi' => $request->tanggal_interaksi, 'nilai_sales' => 0, 'nilai_kontribusi' => $request->nominal, 'catatan' => $request->catatan]);
        return redirect()->back()->with('success', 'Dana support berhasil dicatat!');
    }

    public function storeEntertain(Request $request)
    {
        $request->merge(['nominal' => str_replace('.', '', $request->nominal)]);
        $request->validate(['client_id' => 'required|exists:clients,id', 'tanggal_interaksi' => 'required|date', 'catatan' => 'required|string', 'nominal' => 'required|numeric|min:0', 'lokasi' => 'nullable|string|max:255', 'peserta' => 'nullable|string|max:255']);
        Interaction::create(['user_id' => Auth::id(), 'client_id' => $request->client_id, 'jenis_transaksi' => 'ENTERTAIN', 'nama_produk' => 'Activity / Entertain', 'tanggal_interaksi' => $request->tanggal_interaksi, 'nilai_sales' => 0, 'nilai_kontribusi' => $request->nominal, 'catatan' => $request->catatan, 'lokasi' => $request->lokasi, 'peserta' => $request->peserta]);
        return redirect()->back()->with('success', 'Aktivitas berhasil dicatat.');
    }

    public function destroyClient(Client $client)
    {
        $client->delete();
        return redirect()->route('admin.crm.index')->with('success', 'Data klien berhasil dihapus.');
    }

    public function destroyInteraction(Interaction $interaction)
    {
        $interaction->delete();
        return redirect()->back()->with('success', 'Transaksi berhasil dihapus.');
    }

    public function updateInteraction(Request $request, Interaction $interaction)
    {
        $inputNominal = $request->input('nilai_sales') ?? $request->input('nominal');
        $cleanNominal = str_replace('.', '', $inputNominal);
        if ($interaction->jenis_transaksi == 'IN') {
            $request->merge(['nilai_sales' => $cleanNominal, 'komisi' => str_replace(',', '.', $request->input('komisi'))]);
            $request->validate(['nama_produk' => 'required|string|max:255', 'nilai_sales' => 'required|numeric|min:0', 'komisi' => 'required|numeric|min:0|max:100', 'tanggal_interaksi' => 'required|date', 'catatan' => 'nullable|string']);
            $interaction->update(['nama_produk' => $request->nama_produk, 'tanggal_interaksi' => $request->tanggal_interaksi, 'nilai_sales' => $request->nilai_sales, 'nilai_kontribusi' => $request->nilai_sales, 'komisi' => $request->komisi, 'catatan' => "[Rate:" . $request->komisi . "] " . $request->catatan]);
        } elseif ($interaction->jenis_transaksi == 'OUT') {
            $request->merge(['nominal' => $cleanNominal]);
            $request->validate(['keperluan' => 'required|string|max:255', 'nominal' => 'required|numeric|min:0', 'tanggal_interaksi' => 'required|date', 'catatan' => 'nullable|string']);
            $interaction->update(['nama_produk' => 'USAGE : ' . $request->keperluan, 'tanggal_interaksi' => $request->tanggal_interaksi, 'nilai_sales' => 0, 'nilai_kontribusi' => $request->nominal, 'catatan' => $request->catatan]);
        } elseif ($interaction->jenis_transaksi == 'ENTERTAIN') {
            $request->merge(['nominal' => $cleanNominal]);
            $request->validate(['nominal' => 'required|numeric|min:0', 'tanggal_interaksi' => 'required|date', 'catatan' => 'required|string', 'lokasi' => 'nullable|string|max:255', 'peserta' => 'nullable|string|max:255']);
            $interaction->update(['tanggal_interaksi' => $request->tanggal_interaksi, 'nilai_sales' => 0, 'nilai_kontribusi' => $request->nominal, 'catatan' => $request->catatan, 'lokasi' => $request->lokasi, 'peserta' => $request->peserta]);
        }
        return redirect()->back()->with('success', 'Data transaksi berhasil diperbarui.');
    }

    private function calculateRecapData(Client $client, $year)
    {
        $creationYear = $client->created_at->format('Y');
        $startingLabel = ($year > $creationYear) ? "Saldo Tahun " . ($year - 1) : "Saldo Awal";
        $startingBalance = $client->saldo_awal ?? 0;
        $pastInteractions = $client->interactions()->whereYear('tanggal_interaksi', '<', $year)->get();
        foreach($pastInteractions as $item) {
            if ($item->jenis_transaksi == 'OUT') $startingBalance -= $item->nilai_kontribusi;
            elseif ($item->jenis_transaksi == 'IN') {
                 $rate = $item->komisi ?? 0;
                 if (!$rate && preg_match('/\[Rate:([\d\.]+)\]/', $item->catatan, $m)) $rate = floatval($m[1]);
                 $nominal = $item->nilai_sales > 0 ? $item->nilai_sales : $item->nilai_kontribusi;
                 $startingBalance += $nominal * ($rate / 100);
            }
        }
        $yearlyInteractions = $client->interactions()->whereYear('tanggal_interaksi', $year)->get();
        $recap = []; $currentSaldo = $startingBalance; 
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData = $yearlyInteractions->filter(fn($item) => Carbon::parse($item->tanggal_interaksi)->month == $m);
            $grossSales = $monthlyData->where('jenis_transaksi', 'IN')->sum(fn($item) => $item->nilai_sales > 0 ? $item->nilai_sales : $item->nilai_kontribusi);
            $usageOut = $monthlyData->where('jenis_transaksi', 'OUT')->sum('nilai_kontribusi');
            $netRevenue = 0; $komisiList = [];
            foreach($monthlyData->where('jenis_transaksi', 'IN') as $sale) {
                $rate = $sale->komisi ?? 0;
                if (!$rate && preg_match('/\[Rate:([\d\.]+)\]/', $sale->catatan, $m)) $rate = floatval($m[1]);
                $netRevenue += ($sale->nilai_sales > 0 ? $sale->nilai_sales : $sale->nilai_kontribusi) * ($rate / 100);
                if($rate > 0) $komisiList[] = $rate.'%';
            }
            $currentSaldo += ($netRevenue - $usageOut);
            $komisiText = empty($komisiList) ? (empty($komisiList) && $grossSales > 0 ? 'Var' : '-') : implode(', ', array_unique($komisiList));
            $recap[] = ['month_name' => Carbon::create()->month($m)->translatedFormat('F'), 'komisi_text'=> $komisiText, 'gross_in' => $grossSales, 'net_value'  => $netRevenue, 'out' => $usageOut, 'saldo' => $currentSaldo];
        }
        return ['recap' => $recap, 'totals' => ['gross_in' => collect($recap)->sum('gross_in'), 'net_value' => collect($recap)->sum('net_value'), 'out' => collect($recap)->sum('out'), 'saldo' => $currentSaldo], 'starting_balance' => $startingBalance, 'starting_label' => $startingLabel];
    }

    // TAMBAHAN: Helper hitung saldo untuk header admin
    private function calculateRealTimeBalance($client)
    {
        $balance = $client->saldo_awal ?? 0;
        foreach($client->interactions as $item) {
            if ($item->jenis_transaksi == 'OUT') {
                $balance -= $item->nilai_kontribusi;
            } elseif ($item->jenis_transaksi == 'IN') {
                $rate = $item->komisi ?? 0;
                if (!$rate && preg_match('/\[Rate:([\d\.]+)\]/', $item->catatan, $matches)) {
                    $rate = floatval($matches[1]);
                }
                $nominal = $item->nilai_sales > 0 ? $item->nilai_sales : $item->nilai_kontribusi;
                $balance += ($nominal * ($rate / 100));
            }
        }
        return $balance;
    }
    
    public function exportClientRecap(Client $client, Request $request)
    {
        // 1. Ambil tahun
        $year = $request->input('year', date('Y'));
    
        // 2. Hitung data rekap
        $calc = $this->calculateRecapData($client, $year);
    
        // 3. BERSIHKAN NAMA FILE (Hapus / dan \)
        // Kita hapus karakter yang dilarang menggunakan str_replace atau preg_replace
        $safeName = str_replace(['/', '\\'], '_', $client->nama_user);
        $fileName = 'Laporan_Sales_' . str_replace(' ', '_', $safeName) . '_' . $year . '.xlsx';
    
        // 4. Download
        return Excel::download(new ClientAnnualExport(
            $client,
            $calc['recap'],
            $year,
            $calc['totals']
        ), $fileName);
    }
}