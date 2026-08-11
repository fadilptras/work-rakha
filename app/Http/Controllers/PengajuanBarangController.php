<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanBarang;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PengajuanBarangNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class PengajuanBarangController extends Controller
{
    public function index()
    {
        $title = 'Pengajuan Barang';
        $totalPengajuan = Auth::user()->pengajuanBarangs()->count();

        // Skema Ringan: Ambil murni daftar supplier dari database (tabel suppliers)
        $supplierList = Cache::remember('supplier_list_dropdown', 300, function () {
            if (Schema::hasTable('suppliers')) {
                $dbSuppliers = \App\Models\Supplier::orderBy('nama_supplier')->pluck('nama_supplier')->toArray();
                if (!empty($dbSuppliers)) {
                    return $dbSuppliers;
                }
            }
            return [];
        });

        // Skema Ringan: Ambil murni daftar barang dari database (tabel barangs)
        $barangList = Cache::remember('barang_list_dropdown', 300, function () {
            if (Schema::hasTable('barangs')) {
                $dbBarangs = \App\Models\Barang::orderBy('nama_barang')->pluck('nama_barang')->toArray();
                if (!empty($dbBarangs)) {
                    return $dbBarangs;
                }
            }
            return [];
        });

        return view('users.pengajuan-barang.pengajuan-barang-form', compact('title', 'totalPengajuan', 'supplierList', 'barangList'));
    }

    public function history(Request $request)
    {
        $query = Auth::user()->pengajuanBarangs()->latest();
        if ($request->filled('status') && $request->status != 'semua') {
            if ($request->status == 'diproses') {
                $query->whereIn('status', ['diajukan', 'diproses']);
            } else {
                $query->where('status', $request->status);
            }
        }
        $pengajuanBarangs = $query->paginate(15)->appends($request->query());

        return view('users.pengajuan-barang.pengajuan-barang-riwayat', [
            'title' => 'Riwayat Pengajuan Barang',
            'pengajuanBarangs' => $pengajuanBarangs,
        ]);
    }

    /**
     * Menampilkan seluruh pengajuan barang untuk keperluan pemantauan khusus oleh manajemen.
     * Hanya dapat diakses oleh divisi/jabatan tertentu sesuai kebijakan perusahaan.
     */
    public function monitoringAll(Request $request)
    {
        $user = Auth::user();
        
        // 1. Validasi Otorisasi: Cek apakah user berhak mengakses fitur monitoring
        $isTopManagement = ($user->divisi === 'Top Management');
        $isKadivMO = ($user->is_kepala_divisi == 1 && $user->divisi === 'Marketing dan Operasional');
        $isKadivFG = ($user->is_kepala_divisi == 1 && $user->divisi === 'Finance dan Gudang');

        if (!($isTopManagement || $isKadivMO || $isKadivFG || $user->role === 'admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // 2. Inisialisasi Query: Ambil seluruh data pengajuan urut dari yang terbaru
        $query = PengajuanBarang::with('user')->latest();
        
        // 3. Filter berdasarkan Status (jika ada)
        if ($request->filled('status') && $request->status != 'semua') {
            if ($request->status == 'diproses') {
                $query->whereIn('status', ['diajukan', 'diproses']);
            } else {
                $query->where('status', $request->status);
            }
        }
        
        // 4. Pencarian berdasarkan kata kunci (Judul, Nomor Surat, atau Nama Pemohon)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul_pengajuan', 'like', "%{$search}%")
                  ->orWhere('nomor_surat', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 5. Paginate hasil query dan pertahankan parameter filter/pencarian di URL
        $pengajuanBarangs = $query->paginate(15)->appends($request->query());

        return view('users.pengajuan-barang.pengajuan-barang-monitoring', [
            'title' => 'Monitoring Seluruh Pengajuan Barang',
            'pengajuanBarangs' => $pengajuanBarangs,
        ]);
    }

    /**
     * Menyimpan pengajuan barang baru dengan dukungan hingga 4 Approver Dinamis.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Jika divisi tidak dikirim dari form, ambil dari profil user
        if (!$request->filled('divisi')) {
            $request->merge(['divisi' => $user->divisi ?: 'Umum']);
        }

        // 1. Validasi input dari form
        $request->validate([
            'judul_pengajuan' => 'required|string|max:255',
            'divisi' => 'required|string|max:255',
            'rincian_deskripsi.*' => 'required|string',
            'rincian_supplier.*' => 'nullable|string',
            'rincian_jumlah.*' => 'required|integer|min:1',
            'rincian_satuan.*' => 'required|string',
            'rincian_keterangan.*' => 'nullable|string',
            'catatan_pemohon' => 'nullable|string',
            'file_pendukung' => 'nullable|array|max:10',
            'file_pendukung.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240',
        ]);

        // Auto-save supplier baru ke tabel suppliers database jika belum ada
        if ($request->has('rincian_supplier') && is_array($request->rincian_supplier)) {
            $hasNew = false;
            foreach ($request->rincian_supplier as $sup) {
                $supName = trim($sup ?? '');
                if (!empty($supName) && !in_array($supName, ['-', 'Lainnya'])) {
                    if (Schema::hasTable('suppliers')) {
                        \App\Models\Supplier::firstOrCreate(['nama_supplier' => $supName]);
                        $hasNew = true;
                    }
                }
            }
            if ($hasNew) {
                Cache::forget('supplier_list_dropdown');
            }
        }

        // Auto-save barang baru ke tabel barangs database jika belum ada
        if ($request->has('rincian_deskripsi') && is_array($request->rincian_deskripsi)) {
            $hasNewBarang = false;
            foreach ($request->rincian_deskripsi as $brg) {
                $brgName = trim($brg ?? '');
                if (!empty($brgName) && !in_array(strtolower($brgName), ['-', 'lainnya'])) {
                    if (Schema::hasTable('barangs')) {
                        \App\Models\Barang::firstOrCreate(['nama_barang' => $brgName]);
                        $hasNewBarang = true;
                    }
                }
            }
            if ($hasNewBarang) {
                Cache::forget('barang_list_dropdown');
            }
        }

        $user = Auth::user();

        // Validasi keselamatan: Karyawan wajib minimal punya 1 approver barang
        if (!$user->approver_barang_1_id && !$user->approver_barang_2_id && !$user->approver_barang_3_id && !$user->approver_barang_4_id) {
            return redirect()->back()->with('error', 'Anda belum memiliki Approver Barang yang diatur oleh Admin. Silakan hubungi Admin/HRD.');
        }

        // 2. Ambil ID approver dari data user
        $app1 = $user->approver_barang_1_id;
        $app2 = $user->approver_barang_2_id;
        $app3 = $user->approver_barang_3_id;
        $app4 = $user->approver_barang_4_id;

        // 3. Identifikasi status awal: jika ID kosong, langsung tandai 'skipped'
        $st1 = $app1 ? 'menunggu' : 'skipped';
        $st2 = $app2 ? 'menunggu' : 'skipped';
        $st3 = $app3 ? 'menunggu' : 'skipped';
        $st4 = $app4 ? 'menunggu' : 'skipped';

        // 4. Buat data pengajuan barang
        $pengajuan = PengajuanBarang::create([
            'user_id' => $user->id,
            'judul_pengajuan' => $request->judul_pengajuan,
            'divisi' => $request->divisi,
            'catatan_pemohon' => $request->catatan_pemohon,
            'rincian_barang' => $this->parseRincian($request),
            'lampiran' => $this->uploadFiles($request),
            'status' => 'diajukan',
            'approver_barang_1_id' => $app1,
            'status_appr_1' => $st1,
            'approver_barang_2_id' => $app2,
            'status_appr_2' => $st2,
            'approver_barang_3_id' => $app3,
            'status_appr_3' => $st3,
            'approver_barang_4_id' => $app4,
            'status_appr_4' => $st4,
        ]);

        // 5. Logika Notifikasi Pertama (Cari Approver aktif pertama)
        $firstApprover = null;
        if ($pengajuan->approver1 && $st1 === 'menunggu') $firstApprover = $pengajuan->approver1;
        elseif ($pengajuan->approver2 && $st2 === 'menunggu') $firstApprover = $pengajuan->approver2;
        elseif ($pengajuan->approver3 && $st3 === 'menunggu') $firstApprover = $pengajuan->approver3;
        elseif ($pengajuan->approver4 && $st4 === 'menunggu') $firstApprover = $pengajuan->approver4;

        if ($firstApprover) {
            $firstApprover->notify(new PengajuanBarangNotification($pengajuan, 'baru'));
        }

        return redirect()->route('pengajuan_barang.index')->with('success', 'Pengajuan berhasil dikirim.');
    }

    public function show(PengajuanBarang $pengajuanBarang)
    {
        // Load relasi agar view tidak error saat menampilkan detail approver
        $pengajuanBarang->load(['user', 'approver1', 'approver2', 'approver3', 'approver4']);
        return view('users.pengajuan-barang.pengajuan-barang-detail', compact('pengajuanBarang'));
    }

    /**
     * Update Status Barang - ALGORITMA DINAMIS (Anti-Stuck untuk 1, 2, 3, atau 4 Approver)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'alasan' => 'nullable|string|max:255'
        ]);

        $pengajuan = PengajuanBarang::with(['user', 'approver1', 'approver2', 'approver3', 'approver4'])->findOrFail($id);
        $user = Auth::user();
        $statusInput = $request->status;

        // 1. Tentukan pada tahap ke berapa User ini bertindak
        $currentStage = null;
        if ($user->id == $pengajuan->approver_barang_1_id && $pengajuan->status_appr_1 == 'menunggu') {
            $currentStage = 1;
        } elseif ($user->id == $pengajuan->approver_barang_2_id && $pengajuan->status_appr_2 == 'menunggu') {
            if ($pengajuan->status_appr_1 == 'menunggu') return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
            $currentStage = 2;
        } elseif ($user->id == $pengajuan->approver_barang_3_id && $pengajuan->status_appr_3 == 'menunggu') {
            if (in_array('menunggu', [$pengajuan->status_appr_1, $pengajuan->status_appr_2])) return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
            $currentStage = 3;
        } elseif ($user->id == $pengajuan->approver_barang_4_id && $pengajuan->status_appr_4 == 'menunggu') {
            if (in_array('menunggu', [$pengajuan->status_appr_1, $pengajuan->status_appr_2, $pengajuan->status_appr_3])) return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
            $currentStage = 4;
        } else {
            return redirect()->back()->with('error', 'Otoritas tidak valid atau urutan persetujuan salah.');
        }

        // 2. Jika DITOLAK, langsung hentikan proses & tolak pengajuan utama
        if ($statusInput == 'ditolak') {
            $pengajuan->update([
                "status_appr_{$currentStage}" => 'ditolak',
                "catatan_approver_{$currentStage}" => $request->alasan,
                "tanggal_approved_{$currentStage}" => Carbon::now(),
                'status' => 'ditolak'
            ]);
            $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'ditolak'));
            return redirect()->back()->with('success', 'Pengajuan barang telah ditolak.');
        }

        // 3. Jika DISETUJUI, update status dan tanggal approve tahap saat ini terlebih dahulu
        $pengajuan->update([
            "status_appr_{$currentStage}" => 'disetujui',
            "catatan_approver_{$currentStage}" => $request->alasan,
            "tanggal_approved_{$currentStage}" => Carbon::now(),
        ]);

        // 4. Periksa apakah setelah tahap ini masih ada Approver berikutnya yang berstatus 'menunggu' (hanya sampai Approver 3)
        $nextApprover = null;
        if ($currentStage < 4) {
            if ($pengajuan->status_appr_2 == 'menunggu') {
                $nextApprover = $pengajuan->approver2;
            } elseif ($pengajuan->status_appr_3 == 'menunggu') {
                $nextApprover = $pengajuan->approver3;
            }
        }

        // 5. Eksekusi lanjutan berdasarkan ketersediaan Approver berikutnya
        if ($nextApprover) {
            // MASIH ADA APPROVER SELANJUTNYA -> Ubah status jadi diproses & kirim notif ke approver berikutnya
            $pengajuan->update(['status' => 'diproses']);
            $nextApprover->notify(new PengajuanBarangNotification($pengajuan, 'baru'));
            $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'disetujui_parsial'));
        } else {
            if ($currentStage < 4) {
                // SEMUA APPROVER (1-3) SUDAH SETUJU -> Ubah status utama menjadi disetujui untuk diproses Admin
                $pengajuan->update(['status' => 'disetujui']);
                $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'disetujui_final'));
                
                // Beri notif ke admin (approver 4) jika ada
                if ($pengajuan->approver4 && $pengajuan->status_appr_4 == 'menunggu') {
                    $pengajuan->approver4->notify(new PengajuanBarangNotification($pengajuan, 'baru'));
                }
            } else {
                // Jika stage 4 (Admin) menyetujui dari form ini, tapi biasanya admin update via updateMonitoring
                // Kita biarkan logika ini berjaga-jaga jika Admin menyetujui langsung
                $pengajuan->update(['status' => 'disetujui']);
            }
        }

        return redirect()->back()->with('success', 'Status pengajuan barang berhasil diperbarui.');
    }

    /**
     * Download PDF Pengajuan Barang (Mendukung hingga Approver 4)
     */
    public function download(PengajuanBarang $pengajuanBarang)
    {
        $pengajuanBarang->load(['user', 'approver1', 'approver2', 'approver3', 'approver4']);
        $pdf = Pdf::loadView('pdf.documents.pengajuan-barang', [
            'pengajuanBarang' => $pengajuanBarang,
        ]);
        $filename = 'SPB_' . str_replace('/', '-', $pengajuanBarang->nomor_surat) . '_' . ($pengajuanBarang->user->name ?? 'Unknown') . '_' . $pengajuanBarang->judul_pengajuan . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Fungsi Pembantu: Mengolah rincian barang dari form ke format array (JSON)
     */
    private function parseRincian(Request $request)
    {
        $rincian = [];
        if ($request->has('rincian_deskripsi')) {
            foreach ($request->rincian_deskripsi as $index => $deskripsi) {
                $rincian[] = [
                    'deskripsi'  => $deskripsi,
                    'supplier'   => $request->rincian_supplier[$index] ?? '-',
                    'satuan'     => $request->rincian_satuan[$index] ?? 'Pcs',
                    'jumlah'     => $request->rincian_jumlah[$index] ?? 0,
                    'keterangan' => $request->rincian_keterangan[$index] ?? '-',
                ];
            }
        }
        return $rincian;
    }

    /**
     * Fungsi Pembantu: Mengurus upload banyak file lampiran
     */
    private function uploadFiles(Request $request)
    {
        $pathFiles = [];
        if ($request->hasFile('file_pendukung')) {
            foreach ($request->file('file_pendukung') as $file) {
                $pathFiles[] = $file->store('lampiran_barang', 'public');
            }
        }
        return $pathFiles;
    }

    /**
     * Membatalkan pengajuan barang (hanya pemilik, hanya jika belum diproses final).
     */
    public function cancel(PengajuanBarang $pengajuanBarang)
    {
        if (Auth::id() !== $pengajuanBarang->user_id) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan pengajuan ini.');
        }

        // Ketika approver 1 sudah approve tidak bisa dibatalkan
        if ($pengajuanBarang->status_appr_1 === 'disetujui') {
            return redirect()->back()->with('error', 'Pengajuan sudah disetujui oleh Approver 1 dan tidak dapat dibatalkan.');
        }

        if (!in_array($pengajuanBarang->status, ['diajukan', 'diproses'])) {
            return redirect()->back()->with('error', 'Pengajuan sudah selesai diproses, tidak bisa dibatalkan.');
        }

        $pengajuanBarang->update([
            'status' => 'dibatalkan',
            'status_appr_1' => $pengajuanBarang->status_appr_1 === 'menunggu' ? 'dibatalkan' : $pengajuanBarang->status_appr_1,
            'status_appr_2' => $pengajuanBarang->status_appr_2 === 'menunggu' ? 'dibatalkan' : $pengajuanBarang->status_appr_2,
            'status_appr_3' => $pengajuanBarang->status_appr_3 === 'menunggu' ? 'dibatalkan' : $pengajuanBarang->status_appr_3,
            'status_appr_4' => $pengajuanBarang->status_appr_4 === 'menunggu' ? 'dibatalkan' : $pengajuanBarang->status_appr_4,
        ]);

        return redirect()->back()->with('success', 'Pengajuan barang berhasil dibatalkan.');
    }

    /**
     * Memperbarui status monitoring & log pengiriman/proses barang oleh Approver 4 dari User side.
     */
    public function updateMonitoring(Request $request, $id)
    {
        $request->validate([
            'status_monitoring' => 'required|string|max:255',
            'catatan_monitoring' => 'nullable|string|max:1000',
            'tandai_selesai' => 'nullable|boolean',
            'lampiran_monitoring' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $pengajuan = PengajuanBarang::findOrFail($id);
        $user = Auth::user();
        
        // Ensure only approver 4 (or admin) can update this
        if ($user->id != $pengajuan->approver_barang_4_id && $user->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $statusMonitoring = $request->status_monitoring;
        $catatan = $request->catatan_monitoring;
        $nowFormatted = Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm');

        $lampiranPath = null;
        if ($request->hasFile('lampiran_monitoring')) {
            // Simpan di folder lampiran_barang sesuai permintaan
            $lampiranPath = $request->file('lampiran_monitoring')->store('lampiran_barang', 'public');
            
            // Tambahkan ke array lampiran utama
            $existingLampiran = is_array($pengajuan->lampiran) ? $pengajuan->lampiran : json_decode($pengajuan->lampiran, true) ?? [];
            $existingLampiran[] = $lampiranPath;
            $updateData['lampiran'] = $existingLampiran;
        }

        $riwayat = $pengajuan->riwayat_monitoring ?? [];
        $riwayat[] = [
            'status' => $statusMonitoring,
            'catatan' => $catatan ?: '-',
            'waktu' => $nowFormatted,
            'oleh' => $user->name,
            'lampiran' => $lampiranPath, // tetap disimpan di riwayat juga untuk history
        ];

        $updateData['status_monitoring'] = $statusMonitoring;
        $updateData['riwayat_monitoring'] = $riwayat;

        // Jika tombol "Tandai Selesai" diklik atau status monitoring diset Selesai
        if ($request->filled('tandai_selesai') || in_array(strtolower($statusMonitoring), ['selesai', 'barang diterima', 'selesai / barang diterima'])) {
            $updateData['status'] = 'selesai';
            $updateData['status_monitoring'] = 'Selesai / Barang Diterima';
            $updateData['status_appr_4'] = 'selesai';
            $updateData['tanggal_approved_4'] = Carbon::now();
        }

        $pengajuan->update($updateData);

        return redirect()->back()->with('success', 'Status monitoring barang berhasil diperbarui!');
    }
}