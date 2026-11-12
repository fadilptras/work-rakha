<?php

namespace App\Http\Controllers\Crm;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\ClientPicHistory;
use App\Models\Product;
use App\Models\PengajuanDana;
use App\Models\Sales;
use App\Notifications\PengajuanDanaNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * Transaksi CRM: sales IN, support/usage OUT, entertain, dan hapus/ubahnya.
 */
class TransactionController extends BaseCrmController
{
    public function updateInteraction(Request $request, ClientInteraction $interaction)
    {
        // Cek Hak Akses: Hanya user dengan akses penuh yang bisa mengedit transaksi/interaksi
        if (!$this->hasFullAccess()) abort(403, 'Akses Ditolak: Hanya Admin/Kepala Divisi yang dapat mengubah data transaksi.');

        // Ambil input nominal (bisa dari field 'sales_amount' atau 'amount')
        $inputNominal = $request->input('sales_amount') ?? $request->input('amount');
        // Hilangkan titik ribuan pada nominal (misal: 1.000.000 jadi 1000000)
        $cleanNominal = str_replace('.', '', (string) $inputNominal);

        // --- LOGIKA UNTUK TIPE: SALES (IN) ---
        if ($interaction->transaction_type == 'IN') {

            // Masukkan data bersih kembali ke request agar lolos validasi
            $request->merge([
                'sales_amount' => $cleanNominal
            ]);

            // Validasi
            $request->validate([
                'product_name'     => 'required|string|max:255',
                'sales_amount'     => 'required|numeric|min:0',
                'interaction_date' => 'required|date',
                'notes'            => 'nullable|string',
            ]);

            // Update Data ke Database (commission_rate diambil dari profil klien)
            $clientRate = $interaction->client->commission_rate ?? 0;
            $interaction->update([
                'product_name'     => $request->product_name,
                'interaction_date' => $request->interaction_date,
                'sales_amount'     => $request->sales_amount,
                'amount'           => $request->sales_amount,
                'commission_rate'  => $clientRate,
                'notes'            => $request->notes,
            ]);

        // --- LOGIKA UNTUK TIPE: PENGELUARAN (OUT) ---
        } elseif ($interaction->transaction_type == 'OUT') {

            $request->merge(['amount' => $cleanNominal]);

            $request->validate([
                'purpose'          => 'required|string|max:255',
                'amount'           => 'required|numeric|min:0',
                'interaction_date' => 'required|date',
                'notes'            => 'nullable|string',
            ]);

            $interaction->update([
                'product_name'     => 'USAGE : ' . ($interaction->client->client_name ?? '') . ' - ' . ($interaction->client->customer?->customer_name ?? '') . ' (' . $request->purpose . ')',
                'interaction_date' => $request->interaction_date,
                'sales_amount'     => 0,
                'amount'           => $request->amount,
                'notes'            => $request->notes,
            ]);

        // --- LOGIKA UNTUK TIPE: AKTIVITAS (ENTERTAIN) ---
        } elseif ($interaction->transaction_type == 'ENTERTAIN') {

            $request->merge(['amount' => $cleanNominal]);

            $request->validate([
                'amount'           => 'required|numeric|min:0',
                'interaction_date' => 'required|date',
                'notes'            => 'required|string|max:2000',
            ]);

            $interaction->update([
                'product_name'     => 'ENTERTAIN : ' . \Illuminate\Support\Str::limit(trim((string) $request->notes), 200, ''),
                'interaction_date' => $request->interaction_date,
                'sales_amount'     => 0,
                'amount'           => $request->amount,
                'notes'            => $request->notes,
            ]);
        }

        return redirect()->back()->with('success', 'Data transaksi berhasil diperbarui!');
    }

    public function storeInteraction(Request $request)
    {
        // Input sales (fetch + simpan) hanya untuk full-access.
        // PIC melihat keterangan transaksi pada kartu riwayat (read-only).
        if (!$this->hasFullAccess()) abort(403, 'Akses Ditolak: Hanya Admin/Kepala Divisi yang dapat menginput sales.');

        $clientIds = is_array($request->client_id) ? $request->client_id : [$request->client_id];
        $clients = Client::active()->whereIn('id', $clientIds)->get()->keyBy('id');
        if ($clients->count() !== count(array_unique($clientIds))) abort(404, 'Data klien tidak ditemukan.');

        $nilaiSales = $request->sales_amount;
        if (is_array($nilaiSales)) {
            $nilaiSales = array_map(fn($val) => preg_replace('/[^0-9]/', '', (string) $val), $nilaiSales);
        } else {
            $nilaiSales = preg_replace('/[^0-9]/', '', (string) $nilaiSales);
        }

        $request->merge([
            'sales_amount' => $nilaiSales
        ]);

        // Cek apakah input array atau string tunggal
        $isArray = is_array($request->product_name);

        if ($isArray) {
            $request->validate([
                'client_id' => 'required|array',
                'client_id.*' => 'required|exists:clients,id',
                'product_name' => 'required|array',
                'product_name.*' => 'required|string|max:255',
                'sales_amount' => 'required|array',
                'sales_amount.*' => 'required|numeric|min:0',
                'interaction_date' => 'required|array',
                'interaction_date.*' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            // Antrean bulk harus sejajar panjangnya
            $rowCount = count($request->product_name);
            if (count($request->client_id) !== $rowCount
                || count($request->sales_amount) !== $rowCount
                || count($request->interaction_date) !== $rowCount) {
                return redirect()->back()->with('error', 'Data antrean tidak valid (jumlah baris tidak sejajar).')->withInput();
            }
        } else {
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'product_name' => 'required|string|max:255',
                'sales_amount' => 'required|numeric|min:0',
                'interaction_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);
        }

        $rows = $isArray
            ? array_map(fn($i) => [
                'client_id' => $request->client_id[$i],
                'product_name' => trim((string) $request->product_name[$i]),
                'sales_amount' => (float) $request->sales_amount[$i],
                'interaction_date' => $request->interaction_date[$i],
            ], array_keys($request->product_name))
            : [[
                'client_id' => $request->client_id,
                'product_name' => trim((string) $request->product_name),
                'sales_amount' => (float) $request->sales_amount,
                'interaction_date' => $request->interaction_date,
            ]];

        [$created, $skipped] = DB::transaction(function () use ($rows, $clients, $request) {
            $created = 0;
            $skipped = 0;
            $productMap = Product::whereNotNull('match_key')->pluck('id', 'match_key');
            foreach ($rows as $row) {
                $rowClient = $clients->get($row['client_id']);
                $rowRate = $rowClient ? (float) $rowClient->commission_rate : 0;

                // Lewati baris yang sudah terimport (client + produk + tanggal + nominal + IN)
                $exists = ClientInteraction::where('client_id', $row['client_id'])
                    ->where('transaction_type', 'IN')
                    ->where('product_name', $row['product_name'])
                    ->where('interaction_date', $row['interaction_date'])
                    ->where('sales_amount', $row['sales_amount'])
                    ->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }

ClientInteraction::create([
                    'client_id' => $row['client_id'],
                    'customer_id' => $rowClient?->customer_id,
                    'product_id' => $productMap[Product::buildMatchKey($row['product_name'])] ?? null,
                    'transaction_type' => 'IN',
                    'product_name' => $row['product_name'],
                    'interaction_date' => $row['interaction_date'],
                    'sales_amount' => $row['sales_amount'],
                    'amount' => $row['sales_amount'],
                    'commission_rate' => $rowRate,
                    'notes' => $request->notes,
                ]);
                $created++;
            }
            return [$created, $skipped];
        });

        $message = $created > 0
            ? "Transaksi sales berhasil ditambahkan ({$created} baris" . ($skipped > 0 ? ", {$skipped} dilewati karena sudah ada" : "") . ")."
            : 'Tidak ada data baru — semua baris sudah pernah diimport.';

        return redirect()->back()->with('success', $message);
    }

    public function storeSupport(Request $request)
    {
        $client = Client::active()->findOrFail($request->client_id);
        if ($client->user_id !== Auth::id() && !$this->hasFullAccess()) abort(403);

        // Cek jika pencatatan langsung (direct usage) & memiliki akses penuh
        if ($request->has('direct_usage') && $request->direct_usage && $this->hasFullAccess()) {
            $request->merge(['amount' => str_replace('.', '', (string) $request->amount)]);
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'purpose' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'interaction_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);
            ClientInteraction::create([
                'client_id' => $request->client_id,
                'customer_id' => $client->customer_id,
                'transaction_type' => 'OUT',
                'product_name' => 'USAGE : ' . $client->client_name . ' - ' . ($client->customer?->customer_name ?? '') . ' (' . $request->purpose . ')',
                'interaction_date' => $request->interaction_date,
                'sales_amount' => 0,
                'amount' => $request->amount,
                'notes' => $request->notes
            ]);
            return redirect()->back()->with('success', 'Dana support (Direct Usage) berhasil dicatat langsung!');
        }

        // Validasi rekening dari form
        $request->validate([
            'nama_bank' => 'required|string',
            'no_rekening' => 'required|string',
            'nama_rek' => 'required|string',
        ]);

        $user = Auth::user();

        // Ambil ID approver dari profil user
        $app1 = $user->approver_dana_1_id;
        $app2 = $user->approver_dana_2_id;
        $app3 = $user->approver_dana_3_id;
        $app4 = $user->approver_dana_4_id;

        if (!$app1 && !$app2 && !$app3 && !$app4) {
            return redirect()->back()->with('error', 'Gagal: Anda belum memiliki pengaturan Approver Dana. Harap hubungi Admin/HRD.');
        }

        $request->merge(['amount' => str_replace('.', '', (string) $request->amount)]);
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'purpose' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'interaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'lampiran_tambahan' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,xls|max:5120', // Upload file (opsional) maks 5MB
        ]);

        // 1. (Dihapus) Tidak lagi menyimpan Interaction (CRM) langsung di sini.
        // Akan disimpan saat Pengajuan Dana mencapai status 'selesai'.

        // 2. Simpan Pengajuan Dana
        $st1 = $app1 ? 'menunggu' : 'skipped';
        $st2 = $app2 ? 'menunggu' : 'skipped';
        $st3 = $app3 ? 'menunggu' : 'skipped';
        $st4 = $app4 ? 'menunggu' : 'skipped';

        $judulPengajuan = 'Support Klien: ' . ($client->customer?->customer_name ?? '') . ' - ' . $client->client_name;

        // Titipkan data CRM di rincian_dana (format: client - customer (keperluan))
        $rincian = [
            [
                'deskripsi' => $client->client_name . ' - ' . ($client->customer?->customer_name ?? '') . ' (' . $request->purpose . ')',
                'jumlah' => $request->amount,
                'client_id' => $request->client_id, // Disisipkan untuk pencatatan riwayat nanti
                'customer_id' => $client->customer_id,
                'interaction_date' => $request->interaction_date,
                'crm_notes' => $request->notes,
            ]
        ];

        // 3. Generate File PDF Rekap Sales sebagai Lampiran
        $year = date('Y');
        $calc = $this->calculateRecapData($client, $year);
        $safeClientName = str_replace(['/', '\\', ' '], '_', $client->customer?->customer_name ?? 'klien');
        $fileName = 'Rekap_Sales_' . $safeClientName . '_' . date('Ymd_His') . '.pdf';
        $filePath = 'lampiran_dana/' . $fileName;

        $pdf = Pdf::loadView('exports.client_recap_pdf', [
            'client' => $client,
            'recap' => $calc['recap'],
            'year' => $year,
            'totals' => $calc['totals'],
            'submittedBy' => $user->name,
            'submittedDate' => \Carbon\Carbon::parse($request->interaction_date)->translatedFormat('d F Y'),
            'submittedAmount' => (float) $request->amount,
            'purpose' => $request->purpose,
        ]);

        Storage::disk('public')->put($filePath, $pdf->output());

        // Menyimpan daftar lampiran (PDF rekap sales otomatis)
        $lampiranArray = [$filePath];

        // Jika terdapat upload lampiran tambahan dari pengguna, simpan dan gabungkan ke array
        if ($request->hasFile('lampiran_tambahan')) {
            $uploadedPath = $request->file('lampiran_tambahan')->store('lampiran_dana', 'public');
            $lampiranArray[] = $uploadedPath;
        }

        $pengajuanDana = PengajuanDana::create([
            'user_id' => $user->id,
            'judul_pengajuan' => $judulPengajuan,
            'divisi' => $user->divisi ?: 'Umum',
            'nama_bank' => $request->nama_bank,
            'no_rekening' => $request->no_rekening,
            'nama_rek' => $request->nama_rek,
            'total_dana' => $request->amount,
            'rincian_dana' => $rincian,
            'lampiran' => $lampiranArray, // Gabungan lampiran otomatis & opsional dari pengguna

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

        if ($firstStage == 2 || $firstStage == 3) {
            $pengajuanDana->update(['status' => 'diproses']);
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
        $client = Client::active()->findOrFail($request->client_id);
        if ($client->user_id !== Auth::id() && !$this->hasFullAccess()) abort(403);
        $request->merge(['amount' => str_replace('.', '', (string) $request->amount)]);
        $request->validate([
            'client_id' => 'required|exists:clients,id', 'interaction_date' => 'required|date',
            'notes' => 'required|string|max:2000', 'amount' => 'required|numeric|min:0',
        ]);
        ClientInteraction::create([
            'client_id' => $request->client_id, 'transaction_type' => 'ENTERTAIN',
            'customer_id' => $client->customer_id,
            'product_name' => 'ENTERTAIN : ' . \Illuminate\Support\Str::limit(trim((string) $request->notes), 200, ''),
            'interaction_date' => $request->interaction_date,
            'sales_amount' => 0, 'amount' => $request->amount, 'notes' => $request->notes,
        ]);
        return redirect()->back()->with('success', 'Aktivitas berhasil dicatat.');
    }

    public function fetchSalesData(Client $client, Request $request)
    {
        // 1. Fetch data sales hanya untuk full-access (PIC read-only via kartu riwayat)
        if (!$this->hasFullAccess()) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak.']);
        }

        $month = substr((string) $request->input('date'), 0, 7); // Normalisasi YYYY-MM-DD (mobile) maupun YYYY-MM (desktop) ke YYYY-MM
        $salesCustomer = trim((string) $request->input('sales_customer'));

        // Alias nama customer di sales: bila instansi punya pemetaan eksplisit,
        // pakai alias tersebut (nama instansi tidak selalu sama persis dengan di sales).
        if (Client::hasCustomerIdColumn()) {
            $client->loadMissing('customer');
        }
        $salesAlias = trim((string) ($client->customer?->sales_customer_name ?? ''));
        if ($salesAlias !== '') {
            $salesCustomer = $salesAlias;
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $month) || !$salesCustomer) {
            return response()->json(['success' => false, 'message' => 'Bulan atau Rumah Sakit tidak valid.']);
        }

        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth()->toDateString();

        // 1. Cari PIC yang pernah aktif di rentang bulan tersebut
        $activePics = $client->picHistories()
            ->where('start_date', '<=', $endOfMonth)
            ->where(function ($q) use ($startOfMonth) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $startOfMonth);
            })
            ->pluck('pic_name')
            ->toArray();

        // Fallback ke PIC saat ini / nama user jika belum ada di riwayat
        $client->loadMissing('user');
        $currentPs = trim((string) ($client->ps !== null && $client->ps !== '' ? $client->ps : ($client->user->name ?? '')));
        if (empty($activePics) && $currentPs !== '') {
            $activePics[] = $currentPs;
        }
        $activePics = array_values(array_unique(array_filter(array_map('trim', $activePics))));

        // 2. Query data di Sales Command Center milik PIC aktif tersebut
        $query = Sales::query()
            ->where('date', 'LIKE', $month . '%')
            ->where('customer_name', $salesCustomer)
            ->where('ps', '!=', 'Office');

        if (!empty($activePics)) {
            $query->where(function ($q) use ($activePics) {
                foreach ($activePics as $pic) {
                    $q->orWhereRaw('LOWER(TRIM(ps)) = ?', [mb_strtolower($pic)]);
                }
            });
        }

        $total = (clone $query)->count();
        $limit = 500;
        $sales = $query
            ->select(['product_name', 'net_price', 'date', 'qty', 'unit', 'customer_name', 'ps'])
            ->orderBy('date')
            ->limit($limit)
            ->get();

        if ($sales->isEmpty()) {
            $picInfo = !empty($activePics) ? ' (' . implode(', ', $activePics) . ')' : '';
            return response()->json([
                'success' => false,
                'message' => "Data sales tidak ditemukan untuk bulan tersebut di bawah PIC{$picInfo}."
            ]);
        }

        // Ambil data sales yang sesuai dan mapping langsung ke Client yang sedang dibuka
        $result = $sales->map(function ($sale) use ($client) {
            return [
                'product_name' => $sale->product_name,
                'sales_amount' => $sale->net_price,
                'date' => $sale->date,
                'qty' => $sale->qty,
                'unit' => $sale->unit,
                'client_id' => $client->id,
                'customer_name' => $sale->customer_name,
                'ps' => $sale->ps,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'total' => $total,
            'truncated' => $total > $limit,
            'customer' => $salesCustomer,
            'aliased' => $salesAlias !== '',
            'active_pics' => $activePics,
        ]);
    }

    public function destroyInteraction(ClientInteraction $interaction)
    {
        // Hanya yang memiliki akses penuh yang bisa menghapus transaksi/interaksi
        if (!$this->hasFullAccess()) abort(403, 'Akses Ditolak: Hanya Admin/Kepala Divisi yang dapat menghapus transaksi.');
        ClientInteraction::destroy($interaction->id);
        return redirect()->back()->with('success', 'Data transaksi berhasil dihapus.');
    }
}
