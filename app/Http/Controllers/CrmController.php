<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientAnnualExport;
use App\Exports\MatrixAnnualExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\PengajuanDana;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PengajuanDanaNotification;

class CrmController extends Controller
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
    // Helper Hak Akses
    private function hasFullAccess()
    {
        $user = Auth::user();
    
        if (!$user) {
            return false;
        }

        $emailClean   = strtolower(trim($user->email ?? ''));
        $jabatanClean = strtolower(trim($user->jabatan ?? ''));
        $divisiClean  = strtolower(trim($user->divisi ?? ''));
    
        // 1. Cek Hak Akses Berdasarkan Email Tertentu
        $allowedEmails = [
            'tmaujana@gmail.com',
            'Agungkuntohimawan81@gmail.com',
            'test@gmail.com'
        ];
        if (in_array($emailClean, $allowedEmails)) {
            return true;
        }

        // 2. Cek Hak Akses Berdasarkan Role 'Admin'
        if ($user->role === 'admin') {
            return true;
        }

        // 3. Cek Hak Akses Berdasarkan Jabatan/Posisi (pastikan menggunakan huruf kecil)
        $allowedJabatan = [
            'direktur', 
            'kepala operasional',
        ];
        if (in_array($jabatanClean, $allowedJabatan)) {
            return true;
        }
    
        // 4. Cek Hak Akses Berdasarkan Divisi & Status Kepala Divisi
        $allowedDivisi = [
            'marketing', 
            'operasional', 
            'marketing dan operasional'
        ];
        if ($user->is_kepala_divisi && in_array($divisiClean, $allowedDivisi)) {
            return true;
        }
    
        // 5. Jika tidak ada syarat yang terpenuhi, akses penuh ditolak
        return false;
    }

    // Cek Akses Dasar (Apakah boleh buka CRM sama sekali)
    private function hasAnyCrmAccess()
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

    public function index()
    {
        $query = Client::with('interactions')->orderBy('nama_user', 'asc');
        
        if (!$this->hasFullAccess()) {
            $query->where('user_id', Auth::id());
        }

        $clients = $query->get();
        
        // Inisialisasi variabel
        $totalAllBalance = 0;
        $totalGrossSales = 0; // Tambahkan variabel baru ini

        foreach($clients as $client) {
            // 1. Hitung Saldo (Net)
            $balance = $this->calculateRealTimeBalance($client);
            $client->current_balance = $balance;
            $totalAllBalance += $balance;

            // 2. Hitung Total Sales (Gross) - Hanya transaksi 'IN'
            $clientSales = $client->interactions
                ->where('jenis_transaksi', 'IN')
                ->sum(function($item) {
                    // Handle logika sales lama vs baru (jika nilai_sales 0, pakai nilai_kontribusi)
                    return $item->nilai_sales > 0 ? $item->nilai_sales : $item->nilai_kontribusi;
                });
                
            $totalGrossSales += $clientSales;
        }

        $agent = new \Jenssegers\Agent\Agent();
        $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';

        return view("users.crm.crm_daftar_klien_{$viewSuffix}", [
            'title' => 'Sistem Informasi Sales (CRM)', 
            'clients' => $clients,
            'totalAllBalance' => $totalAllBalance,
            'totalGrossSales' => $totalGrossSales 
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'komisi' => $request->filled('komisi') ? str_replace(',', '.', $request->komisi) : null
        ]);

        $validator = Validator::make($request->all(), [
            'nama_user'         => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'no_telpon'         => 'nullable|string|max:50',
            'tanggal_lahir'     => 'nullable|date',
            'alamat_user'       => 'nullable|string', 
            'jabatan'           => 'nullable|string|max:100', 
            'hobby_client'      => 'nullable|string|max:255', 
            
            // Apoteker Info
            'nama_apoteker'     => 'nullable|string|max:255',
            'nomor_sipa'        => 'nullable|string|max:255',
            'no_telpon_apoteker'=> 'nullable|string|max:50',
            'komisi'            => 'nullable|numeric|min:0|max:100',

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
        $data['user_id'] = Auth::id();
        $data['pic']     = Auth::user()->name; 

        $client = Client::create($data);
        return redirect()->route('crm.show', $client->id)->with('success', 'Data Klien berhasil dibuat!');
    }

    public function show(Client $client, Request $request)
    {
        // 1. Cek Akses Halaman
        $hasAccess = $this->hasFullAccess();
        if ($client->user_id !== Auth::id() && !$hasAccess) abort(403, 'Akses Ditolak.');

        // 2. Tentukan Hak Edit (HANYA user dengan Full Access yang bisa edit data & interaksi)
        $canEdit = $hasAccess;

        // 3. Data Rekap
        $year = $request->input('year', date('Y'));
        $calc = $this->calculateRecapData($client, $year);

        // 4. Data History
        $historyYear = $request->input('history_year');
        $interactionQuery = $client->interactions()->orderBy('tanggal_interaksi', 'desc');
        if ($historyYear) {
            $interactionQuery->whereYear('tanggal_interaksi', $historyYear);
        }
        $interactions = $interactionQuery->paginate(10)->withQueryString(); 

        // 5. Data Activity
        $activityYear = $request->input('activity_year');
        $activityQuery = $client->interactions()
                                ->where('jenis_transaksi', 'ENTERTAIN')
                                ->orderBy('tanggal_interaksi', 'desc');
        if ($activityYear) {
            $activityQuery->whereYear('tanggal_interaksi', $activityYear);
        }
        $activities = $activityQuery->get();

        $currentBalance = $this->calculateRealTimeBalance($client);

        $agent = new \Jenssegers\Agent\Agent();
        $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';

        $productNames = Interaction::query()->where('jenis_transaksi', 'IN')->whereNotNull('nama_produk')->distinct()->pluck('nama_produk');

        return view("users.crm.crm_detail_klien_{$viewSuffix}", [
            'title' => 'Detail Sales: ' . $client->nama_user,
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
            'canEdit' => $canEdit, // <--- Kirim variabel ini ke View
            'productNames' => $productNames
        ]);
    }

    public function edit(Client $client)
    {
        // Hanya yang memiliki akses penuh yang bisa masuk form edit Klien
        if (!$this->hasFullAccess()) abort(403, 'Akses Ditolak: Hanya Admin/Kepala Divisi yang dapat mengedit data Klien.');
        $agent = new \Jenssegers\Agent\Agent();
        $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';
        return view("users.crm.crm_detail_klien_{$viewSuffix}", ['title' => 'Edit Data Klien', 'client' => $client]);
    }

    public function update(Request $request, Client $client)
    {
        // Hanya yang memiliki akses penuh yang bisa menyimpan perubahan Klien
        if (!$this->hasFullAccess()) abort(403, 'Akses Ditolak: Hanya Admin/Kepala Divisi yang dapat mengubah data Klien.');
        $request->merge([
            'komisi' => $request->filled('komisi') ? str_replace(',', '.', $request->komisi) : null
        ]);

        $validated = $request->validate([
            'nama_user'         => 'required|string|max:255',
            'email'             => 'nullable|email',
            'no_telpon'         => 'nullable|string',
            'tanggal_lahir'     => 'nullable|date',
            'alamat_user'       => 'nullable|string',
            'jabatan'           => 'nullable|string|max:100', 
            'hobby_client'      => 'nullable|string|max:255', 

            // Apoteker Info
            'nama_apoteker'     => 'nullable|string|max:255',
            'nomor_sipa'        => 'nullable|string|max:255',
            'no_telpon_apoteker'=> 'nullable|string|max:50',
            'komisi'            => 'nullable|numeric|min:0|max:100',

            'nama_perusahaan'   => 'required|string|max:255',
            'tanggal_berdiri'   => 'nullable|date',
            'area'              => 'nullable|string',
            'alamat_perusahaan' => 'nullable|string',
            'bank'              => 'nullable|string',
            'no_rekening'       => 'nullable|string',
            'nama_di_rekening'  => 'nullable|string',
            'saldo_awal'        => 'nullable|numeric',
        ]);
        $client->update($validated);
        return redirect()->route('crm.show', $client->id)->with('success', 'Data klien berhasil diperbarui!');
    }

    public function updateInteraction(Request $request, Interaction $interaction)
    {
        // Cek Hak Akses: Hanya user dengan akses penuh yang bisa mengedit transaksi/interaksi
        if (!$this->hasFullAccess()) abort(403, 'Akses Ditolak: Hanya Admin/Kepala Divisi yang dapat mengubah data transaksi.');

        // Ambil input nominal (bisa dari field 'nilai_sales' atau 'nominal')
        $inputNominal = $request->input('nilai_sales') ?? $request->input('nominal');
        // Hilangkan titik ribuan pada nominal (misal: 1.000.000 jadi 1000000)
        $cleanNominal = str_replace('.', '', $inputNominal);

        // --- LOGIKA UNTUK TIPE: SALES (IN) ---
        if ($interaction->jenis_transaksi == 'IN') {
            
            // Masukkan data bersih kembali ke request agar lolos validasi
            $request->merge([
                'nilai_sales' => $cleanNominal
            ]);

            // Validasi
            $request->validate([
                'nama_produk'       => 'required|string|max:255',
                'nilai_sales'       => 'required|numeric|min:0',
                'tanggal_interaksi' => 'required|date',
                'catatan'           => 'nullable|string',
            ]);

            // Update Data ke Database
            $komisiClient = $interaction->client->komisi ?? 0;
            $interaction->update([
                'nama_produk'       => $request->nama_produk,
                'tanggal_interaksi' => $request->tanggal_interaksi,
                'nilai_sales'       => $request->nilai_sales,
                'nilai_kontribusi'  => $request->nilai_sales,
                'komisi'            => $komisiClient, // Ambil dari profil klien
                'catatan'           => "[Rate:" . $komisiClient . "] " . $request->catatan,
            ]);

        // --- LOGIKA UNTUK TIPE: PENGELUARAN (OUT) ---
        } elseif ($interaction->jenis_transaksi == 'OUT') {
            
            $request->merge(['nominal' => $cleanNominal]);
            
            $request->validate([
                'keperluan'         => 'required|string|max:255',
                'nominal'           => 'required|numeric|min:0',
                'tanggal_interaksi' => 'required|date',
                'catatan'           => 'nullable|string',
            ]);

            $interaction->update([
                'nama_produk'       => 'USAGE : ' . $request->keperluan,
                'tanggal_interaksi' => $request->tanggal_interaksi,
                'nilai_sales'       => 0,
                'nilai_kontribusi'  => $request->nominal,
                'catatan'           => $request->catatan,
            ]);

        // --- LOGIKA UNTUK TIPE: AKTIVITAS (ENTERTAIN) ---
        } elseif ($interaction->jenis_transaksi == 'ENTERTAIN') {
            
            $request->merge(['nominal' => $cleanNominal]);
            
            $request->validate([
                'nominal'           => 'required|numeric|min:0',
                'tanggal_interaksi' => 'required|date',
                'catatan'           => 'required|string',
                'lokasi'            => 'nullable|string|max:255',
                'peserta'           => 'nullable|string|max:255',
            ]);

            $interaction->update([
                'tanggal_interaksi' => $request->tanggal_interaksi,
                'nilai_sales'       => 0,
                'nilai_kontribusi'  => $request->nominal,
                'catatan'           => $request->catatan,
                'lokasi'            => $request->lokasi,
                'peserta'           => $request->peserta,
            ]);
        }

        return redirect()->back()->with('success', 'Data transaksi berhasil diperbarui!');
    }

    public function storeInteraction(Request $request)
    {
        $client = Client::findOrFail($request->client_id);
        if ($client->user_id !== Auth::id() && !$this->hasFullAccess()) abort(403);

        // TAMBAHAN: Bersihkan input nilai sales
        $cleanNominal = str_replace('.', '', $request->nilai_sales);

        $request->merge([
            'nilai_sales' => $cleanNominal
        ]);

        $request->validate([
            'client_id' => 'required|exists:clients,id', 
            'nama_produk' => 'required|string|max:255',
            'nilai_sales' => 'required|numeric|min:0', 
            'tanggal_interaksi' => 'required|date', 
            'catatan' => 'nullable|string',
        ]);

        $komisiClient = $client->komisi ?? 0;
        Interaction::create([
            'user_id' => Auth::id(), 
            'client_id' => $request->client_id, 
            'jenis_transaksi' => 'IN', 
            'nama_produk' => $request->nama_produk, 
            'tanggal_interaksi' => $request->tanggal_interaksi,
            'nilai_sales' => $request->nilai_sales, 
            'nilai_kontribusi' => $request->nilai_sales,
            'komisi' => $komisiClient, 
            'catatan' => "[Rate:" . $komisiClient . "] " . $request->catatan,
        ]);

        return redirect()->back()->with('success', 'Transaksi sales berhasil ditambahkan!');
    }

    public function storeSupport(Request $request)
    {
        $client = Client::findOrFail($request->client_id);
        if ($client->user_id !== Auth::id() && !$this->hasFullAccess()) abort(403);
        
        // Pengecekan rekening bank klien
        if (empty($client->bank) || empty($client->no_rekening) || empty($client->nama_di_rekening)) {
            return redirect()->back()->with('error', 'Gagal: Data rekening Klien belum lengkap. Silakan lengkapi via tombol Edit Detail terlebih dahulu.');
        }

        $user = Auth::user();
        
        // Ambil ID approver dari profil user
        $app1 = $user->approver_dana_1_id;
        $app2 = $user->approver_dana_2_id;
        $app3 = $user->approver_dana_3_id;
        $app4 = $user->approver_dana_4_id;

        if (!$app1 && !$app2 && !$app3 && !$app4) {
            return redirect()->back()->with('error', 'Gagal: Anda belum memiliki pengaturan Approver Dana. Harap hubungi Admin/HRD.');
        }

        $request->merge(['nominal' => str_replace('.', '', $request->nominal)]);
        $request->validate([
            'client_id' => 'required|exists:clients,id', 'keperluan' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0', 'tanggal_interaksi' => 'required|date',
            'catatan' => 'nullable|string',
        ]);
        
        // 1. Simpan Interaction (CRM)
        Interaction::create([
            'user_id'=> Auth::id(), 'client_id' => $request->client_id, 'jenis_transaksi' => 'OUT', 
            'nama_produk' => 'USAGE : ' . $request->keperluan, 'tanggal_interaksi' => $request->tanggal_interaksi,
            'nilai_sales' => 0, 'nilai_kontribusi' => $request->nominal, 'catatan' => $request->catatan,
        ]);

        // 2. Simpan Pengajuan Dana
        $st1 = $app1 ? 'menunggu' : 'skipped';
        $st2 = $app2 ? 'menunggu' : 'skipped';
        $st3 = $app3 ? 'menunggu' : 'skipped';
        $st4 = $app4 ? 'menunggu' : 'skipped';

        $judulPengajuan = 'Support Klien: ' . $client->nama_perusahaan;

        $rincian = [
            [
                'deskripsi' => $request->keperluan,
                'jumlah' => $request->nominal
            ]
        ];

        // 3. Generate File PDF Rekap Sales sebagai Lampiran
        $year = date('Y');
        $calc = $this->calculateRecapData($client, $year);
        $safeClientName = str_replace(['/', '\\', ' '], '_', $client->nama_perusahaan);
        $fileName = 'Rekap_Sales_' . $safeClientName . '_' . date('Ymd_His') . '.pdf';
        $filePath = 'lampiran_dana/' . $fileName;
        
        $pdf = Pdf::loadView('exports.client_recap_pdf', [
            'client' => $client,
            'recap' => $calc['recap'],
            'year' => $year,
            'totals' => $calc['totals']
        ]);
        
        Storage::disk('public')->put($filePath, $pdf->output());

        $lampiranArray = [$filePath];

        $pengajuanDana = PengajuanDana::create([
            'user_id' => $user->id,
            'judul_pengajuan' => $judulPengajuan,
            'divisi' => $user->divisi ?: 'Umum',
            'nama_bank' => $client->bank,
            'no_rekening' => $client->no_rekening,
            'nama_rek' => $client->nama_di_rekening,
            'total_dana' => $request->nominal,
            'rincian_dana' => $rincian,
            'lampiran' => $lampiranArray, // Lampiran otomatis dari sistem (Rekap Sales)
            
            'status' => 'diajukan',
            
            'approver_dana_1_id' => $app1, 'approver_1_status' => $st1,
            'approver_dana_2_id' => $app2, 'approver_2_status' => $st2,
            'approver_dana_3_id' => $app3, 'approver_3_status' => $st3,
            'approver_dana_4_id' => $app4, 'approver_4_status' => $st4,
        ]);

        // 4. Kirim Notifikasi ke Approver Pertama
        $firstApprover = null;
        $firstStage = null;
        if ($pengajuanDana->approverDana1 && $st1 === 'menunggu') {
            $firstApprover = $pengajuanDana->approverDana1;
            $firstStage = 1;
        } elseif ($pengajuanDana->approverDana2 && $st2 === 'menunggu') {
            $firstApprover = $pengajuanDana->approverDana2;
            $firstStage = 2;
        } elseif ($pengajuanDana->approverDana3 && $st3 === 'menunggu') {
            $firstApprover = $pengajuanDana->approverDana3;
            $firstStage = 3;
        } elseif ($pengajuanDana->approverDana4 && $st4 === 'menunggu') {
            $firstApprover = $pengajuanDana->approverDana4;
            $firstStage = 4;
        }
        
        if ($firstStage == 3) {
            $pengajuanDana->update(['status' => 'proses_pembayaran']);
        } elseif ($firstStage == 4) {
            $pengajuanDana->update(['status' => 'disetujui']);
        }

        if ($firstApprover) {
            Notification::send($firstApprover, new PengajuanDanaNotification($pengajuanDana, 'baru'));
        }

        return redirect()->back()->with('success', 'Dana support berhasil dicatat dan Pengajuan Dana otomatis dibuat!');
    }

    public function storeEntertain(Request $request)
    {
        $client = Client::findOrFail($request->client_id);
        if ($client->user_id !== Auth::id() && !$this->hasFullAccess()) abort(403);
        $request->merge(['nominal' => str_replace('.', '', $request->nominal)]);
        $request->validate([
            'client_id' => 'required|exists:clients,id', 'tanggal_interaksi' => 'required|date',
            'catatan' => 'required|string', 'nominal' => 'required|numeric|min:0',
            'lokasi' => 'nullable|string|max:255', 'peserta' => 'nullable|string|max:255',
        ]);
        Interaction::create([
            'user_id' => Auth::id(), 'client_id' => $request->client_id, 'jenis_transaksi' => 'ENTERTAIN', 
            'nama_produk' => 'Activity / Entertain', 'tanggal_interaksi' => $request->tanggal_interaksi,
            'nilai_sales' => 0, 'nilai_kontribusi' => $request->nominal, 'catatan' => $request->catatan,
            'lokasi' => $request->lokasi, 'peserta' => $request->peserta,
        ]);
        return redirect()->back()->with('success', 'Aktivitas berhasil dicatat.');
    }

    public function destroyClient(Client $client)
    {
        // Hanya yang memiliki akses penuh yang bisa menghapus Klien
        if (!$this->hasFullAccess()) return redirect()->back()->with('error', 'Akses Ditolak: Hanya Admin/Kepala Divisi yang dapat menghapus Klien.');
        Client::destroy($client->id); 
        return redirect()->route('crm.index')->with('success', 'Data klien berhasil dihapus.');
    }

    public function destroyInteraction(Interaction $interaction)
    {
        // Hanya yang memiliki akses penuh yang bisa menghapus transaksi/interaksi
        if (!$this->hasFullAccess()) abort(403, 'Akses Ditolak: Hanya Admin/Kepala Divisi yang dapat menghapus transaksi.');
        Interaction::destroy($interaction->id);
        return redirect()->back()->with('success', 'Data transaksi berhasil dihapus.');
    }

    public function matrix(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $query = Client::with('interactions')->orderBy('nama_user', 'asc');
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
                    return Carbon::parse($i->tanggal_interaksi)->month == $m 
                        && Carbon::parse($i->tanggal_interaksi)->year == $year; 
                });
                $income = 0;
                foreach($monthlyData->where('jenis_transaksi', 'IN') as $sale) {
                    $r = $sale->komisi ?? 0;
                    if(!$r && preg_match('/\[Rate:([\d\.]+)\]/', $sale->catatan, $matches)) $r = (float)$matches[1];
                    $nom = $sale->nilai_sales > 0 ? $sale->nilai_sales : $sale->nilai_kontribusi;
                    $income += $nom * ($r/100);
                }
                $usage = $monthlyData->where('jenis_transaksi', 'OUT')->sum('nilai_kontribusi');
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
        $query = Client::with('interactions')->orderBy('nama_user', 'asc');
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
        $safeClientName = str_replace(['/', '\\'], '_', $client->nama_user);
        
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

    private function calculateRecapData(Client $client, int $year)
    {
        $creationYear = $client->created_at->format('Y');
        $startingLabel = ($year > $creationYear) ? "Saldo Tahun " . ($year - 1) : "Saldo Awal";
        $startingBalance = $client->saldo_awal ?? 0;

        $pastInteractions = $client->interactions()->whereYear('tanggal_interaksi', '<', $year)->get();
        foreach($pastInteractions as $item) {
            if ($item->jenis_transaksi == 'OUT') {
                $startingBalance -= $item->nilai_kontribusi;
            } elseif ($item->jenis_transaksi == 'IN') {
                 $rate = $item->komisi ?? 0;
                 if (!$rate && preg_match('/\[Rate:([\d\.]+)\]/', $item->catatan, $matches)) $rate = floatval($matches[1]);
                 $nominal = $item->nilai_sales > 0 ? $item->nilai_sales : $item->nilai_kontribusi;
                 $startingBalance += $nominal * ($rate / 100);
            }
        }

        $yearlyInteractions = $client->interactions()->whereYear('tanggal_interaksi', $year)->get();
        $recap = [];
        $currentSaldo = $startingBalance; 

        for ($m = 1; $m <= 12; $m++) {
            $monthlyData = $yearlyInteractions->filter(fn($item) => Carbon::parse($item->tanggal_interaksi)->month == $m);
            $grossSales = $monthlyData->where('jenis_transaksi', 'IN')->sum(fn($item) => $item->nilai_sales > 0 ? $item->nilai_sales : $item->nilai_kontribusi);
            $usageOut = $monthlyData->where('jenis_transaksi', 'OUT')->sum('nilai_kontribusi');
            
            $netRevenue = 0; $komisiList = [];
            foreach($monthlyData->where('jenis_transaksi', 'IN') as $sale) {
                $rate = $sale->komisi ?? 0;
                if (!$rate && preg_match('/\[Rate:([\d\.]+)\]/', $sale->catatan, $matches)) $rate = floatval($matches[1]);
                $netRevenue += ($sale->nilai_sales > 0 ? $sale->nilai_sales : $sale->nilai_kontribusi) * ($rate / 100);
                if($rate > 0) $komisiList[] = (float)$rate . '%';
            }
            $currentSaldo += ($netRevenue - $usageOut);
            $komisiText = empty($komisiList) ? (empty($komisiList) && $grossSales > 0 ? 'Var' : '-') : implode(', ', array_unique($komisiList));
            
            $recap[] = ['month_name' => Carbon::create()->month($m)->translatedFormat('F'), 'komisi_text'=> $komisiText, 'gross_in' => $grossSales, 'net_value'  => $netRevenue, 'out' => $usageOut, 'saldo' => $currentSaldo];
        }

        return [
            'recap' => $recap, 
            'totals' => ['gross_in' => collect($recap)->sum('gross_in'), 'net_value' => collect($recap)->sum('net_value'), 'out' => collect($recap)->sum('out'), 'saldo' => $currentSaldo], 
            'starting_balance' => $startingBalance,
            'starting_label' => $startingLabel
        ];
    }

    private function calculateRealTimeBalance(Client $client)
    {
        $balance = $client->saldo_awal ?? 0;
        foreach($client->interactions as $item) {
            if ($item->jenis_transaksi == 'OUT') {
                $balance -= $item->nilai_kontribusi;
            } elseif ($item->jenis_transaksi == 'IN') {
                $rate = $item->komisi ?? 0;
                if (!$rate && preg_match('/\[Rate:([\d\.]+)\]/', $item->catatan, $matches)) $rate = floatval($matches[1]);
                $balance += ($item->nilai_sales > 0 ? $item->nilai_sales : $item->nilai_kontribusi) * ($rate / 100);
            }
        }
        return $balance;
    }
}