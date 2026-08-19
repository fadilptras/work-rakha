<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanBarang;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\PengajuanBarangNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class AdminPengajuanBarangController extends Controller
{
    /**
     * Menampilkan daftar pengajuan barang dengan sistem tabulasi.
     */
    public function index(Request $request)
    {
        $query = PengajuanBarang::with('user')->latest();
        $activeTab = $request->input('tab', 'pending'); 

        switch ($activeTab) {
            case 'pending':
                $query->whereIn('status', ['diajukan', 'diproses']);
                break;
            case 'approved':
                $query->where('status', 'disetujui');
                break;
            case 'selesai':
                $query->where('status', 'selesai');
                break;
            case 'rejected':
                $query->where('status', 'ditolak');
                break;
            case 'cancelled':
                $query->where('status', 'dibatalkan');
                break;
        }

        if ($request->filled('karyawan_id')) {
            $query->where('user_id', $request->karyawan_id);
        }

        $karyawanList = Cache::rememberForever('karyawan_list_dropdown', function () {
            return User::where('role', 'user')->orderBy('name')->get(['id', 'name']);
        });
        
        $divisiList = User::select('divisi')->whereNotNull('divisi')->distinct()->get();
        $pengajuanBarangs = $query->paginate(10);

        return view('admin.pengajuan-barang.index', compact('pengajuanBarangs', 'activeTab', 'karyawanList', 'divisiList'));
    }

    /**
     * Menampilkan halaman pengaturan 4 Approver per karyawan.
     * Filter Admin dihapus agar akun Admin bisa dipilih sebagai Approver.
     */
    public function setApprovers()
    {
        $employees = User::where('role', 'user')->orderBy('name')->get();
        
        $approvers = Cache::rememberForever('approvers_list_dropdown', function () {
            return User::where('name', '!=', 'Admin Rakha')->orderBy('name')->get(['id', 'name']);
        });

        $admins = Cache::rememberForever('admins_list_dropdown', function () {
            return User::orderBy('name')->get(['id', 'name', 'role']);
        });

        return view('admin.pengajuan-barang.set-approvers', [
            'employees' => $employees,
            'approvers' => $approvers, 
            'admins' => $admins, 
            'title' => 'Set Approver Pengajuan Barang'
        ]);
    }

    /**
     * Menyimpan pengaturan 4 Approver barang.
     */
    public function saveApprovers(Request $request)
    {
        $request->validate([
            'approver_barang_1.*' => 'nullable|exists:users,id',
            'approver_barang_2.*' => 'nullable|exists:users,id',
            'approver_barang_3.*' => 'nullable|exists:users,id',
            'approver_barang_4.*' => 'nullable|exists:users,id',
        ]);

        DB::transaction(function () use ($request) {
            if ($request->has('approver_barang_1')) {
                foreach ($request->approver_barang_1 as $userId => $val) {
                    User::where('id', $userId)->update([
                        'approver_barang_1_id' => $request->approver_barang_1[$userId] ?? null,
                        'approver_barang_2_id' => $request->approver_barang_2[$userId] ?? null,
                        'approver_barang_3_id' => $request->approver_barang_3[$userId] ?? null,
                        'approver_barang_4_id' => $request->approver_barang_4[$userId] ?? null,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Pengaturan 4 Approver Barang berhasil diperbarui.');
    }

    /**
     * Menampilkan detail pengajuan barang (Eager Load hingga Approver 4).
     */
    public function show($id)
    {
        $pengajuanBarang = PengajuanBarang::with(['user', 'approver1', 'approver2', 'approver3', 'approver4'])->findOrFail($id);
        return view('admin.pengajuan-barang.show', compact('pengajuanBarang'));
    }

    /**
     * Memproses persetujuan (Setujui/Tolak) oleh Admin secara berurutan (Strictly Sequential).
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

        // 1. Validasi urutan ketat: Pastikan tahap sebelumnya sudah selesai
        $currentStage = null;
        if ($user->id == $pengajuan->approver_barang_1_id && $pengajuan->status_appr_1 == 'menunggu') {
            $currentStage = 1;
        } elseif ($user->id == $pengajuan->approver_barang_2_id && $pengajuan->status_appr_2 == 'menunggu') {
            if ($pengajuan->status_appr_1 == 'menunggu') {
                return redirect()->back()->with('error', 'Giliran persetujuan belum sampai ke Anda. Menunggu Approver 1.');
            }
            $currentStage = 2;
        } elseif ($user->id == $pengajuan->approver_barang_3_id && $pengajuan->status_appr_3 == 'menunggu') {
            if (in_array('menunggu', [$pengajuan->status_appr_1, $pengajuan->status_appr_2])) {
                return redirect()->back()->with('error', 'Giliran persetujuan belum sampai ke Anda. Menunggu Approver sebelumnya.');
            }
            $currentStage = 3;
        } elseif ($user->id == $pengajuan->approver_barang_4_id && $pengajuan->status_appr_4 == 'menunggu') {
            if (in_array('menunggu', [$pengajuan->status_appr_1, $pengajuan->status_appr_2, $pengajuan->status_appr_3])) {
                return redirect()->back()->with('error', 'Giliran persetujuan belum sampai ke Anda. Menunggu Approver sebelumnya.');
            }
            $currentStage = 4;
        } else {
            return redirect()->back()->with('error', 'Giliran persetujuan belum sampai ke akun Admin Anda atau Anda tidak terdaftar sebagai approver aktif untuk tahap ini.');
        }

        // 2. Jika DITOLAK, langsung hentikan proses & tolak pengajuan utama
        if ($statusInput == 'ditolak') {
            $pengajuan->update([
                "status_appr_{$currentStage}" => 'ditolak',
                "catatan_approver_{$currentStage}" => $request->alasan,
                "tanggal_approved_{$currentStage}" => Carbon::now(),
                'status' => 'ditolak'
            ]);
            return redirect()->back()->with('success', 'Pengajuan barang telah ditolak dengan catatan.');
        }

        // 3. Jika DISETUJUI, update status dan tanggal approve tahap saat ini
        $pengajuan->update([
            "status_appr_{$currentStage}" => 'disetujui',
            "catatan_approver_{$currentStage}" => $request->alasan,
            "tanggal_approved_{$currentStage}" => Carbon::now(),
        ]);

        // 4. Periksa apakah setelah tahap ini masih ada Approver berikutnya yang berstatus 'menunggu' (hanya sampai Approver 3)
        $nextApprover = null;
        if ($currentStage < 2 && $pengajuan->status_appr_2 == 'menunggu') $nextApprover = $pengajuan->approver2;
        elseif ($currentStage < 3 && $pengajuan->status_appr_3 == 'menunggu') $nextApprover = $pengajuan->approver3;

        if ($nextApprover) {
            // Masih ada antrean berikutnya -> Ubah status jadi diproses
            $pengajuan->update(['status' => 'diproses']);
        } else {
            // Approver 1-3 selesai -> Ubah status utama jadi disetujui (siap diproses oleh Admin/Approver 4)
            $pengajuan->update(['status' => 'disetujui']);
            
            // Kirim notif ke seluruh approver bahwa pengajuan ini berhasil disetujui
            foreach ([$pengajuan->approver1, $pengajuan->approver2, $pengajuan->approver3, $pengajuan->approver4] as $appr) {
                if ($appr) $appr->notify(new PengajuanBarangNotification($pengajuan, 'disetujui_semua'));
            }
        }

        return redirect()->back()->with('success', 'Persetujuan berhasil disimpan!');
    }

    /**
     * Download PDF Pengajuan Barang (Admin).
     */
    public function downloadPdf($id)
    {
        $pengajuan = PengajuanBarang::with(['user', 'approver1', 'approver2', 'approver3', 'approver4'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.documents.pengajuan-barang', ['pengajuanBarang' => $pengajuan])->setPaper('a4', 'portrait');
        $filename = 'SPB_' . $pengajuan->nomor_surat . '_' . ($pengajuan->user->name ?? 'Unknown') . '_' . $pengajuan->judul_pengajuan . '.pdf';
        $filename = str_replace(['/', '\\'], '-', $filename);
        return $pdf->download($filename);
    }

    /**
     * Download Rekap PDF berdasarkan filter tanggal/karyawan.
     */
    public function downloadRekapPdf(Request $request)
    {
        $query = PengajuanBarang::with('user');
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [Carbon::parse($request->start_date)->startOfDay(), Carbon::parse($request->end_date)->endOfDay()]);
        }
        if ($request->filled('karyawan_id')) $query->where('user_id', $request->karyawan_id);
        $pengajuanBarangs = $query->get();
        return Pdf::loadView('admin.pengajuan-barang.pdf_rekap', compact('pengajuanBarangs'))->download('Rekap_Pengajuan_Barang.pdf');
    }

    /**
     * Menghapus pengajuan barang beserta file lampirannya.
     */
    public function destroy($id)
    {
        $pengajuan = PengajuanBarang::findOrFail($id);
        if ($pengajuan->lampiran) {
            foreach ($pengajuan->lampiran as $file) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($file)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
                }
            }
        }
        $pengajuan->delete();
        return redirect()->back()->with('success', 'Data pengajuan barang berhasil dihapus.');
    }

    /**
     * Memperbarui status monitoring & log pengiriman/proses barang oleh Admin.
     */
    public function updateMonitoring(Request $request, $id)
    {
        $request->validate([
            'status_monitoring' => $request->filled('tandai_selesai') ? 'nullable|string|max:255' : 'required|string|max:255',
            'catatan_monitoring' => 'nullable|string|max:1000',
            'tandai_selesai' => 'nullable|boolean',
            'lampiran_monitoring' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'termin_id' => 'required|integer',
        ]);

        $pengajuan = PengajuanBarang::findOrFail($id);
        $user = Auth::user();

        $statusMonitoring = $request->status_monitoring;
        if ($request->filled('tandai_selesai') && empty($statusMonitoring)) {
            $statusMonitoring = 'Selesai / Barang Diterima';
        }
        $catatan = $request->catatan_monitoring;
        $nowFormatted = Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm');

        $lampiranPath = null;
        if ($request->hasFile('lampiran_monitoring')) {
            $lampiranPath = $request->file('lampiran_monitoring')->store('lampiran_barang', 'public');
            
            $existingLampiran = is_array($pengajuan->lampiran) ? $pengajuan->lampiran : json_decode($pengajuan->lampiran, true) ?? [];
            $existingLampiran[] = $lampiranPath;
            $updateData['lampiran'] = $existingLampiran;
        }

        $riwayatEntry = [
            'status' => $statusMonitoring,
            'catatan' => $catatan ?: '-',
            'waktu' => $nowFormatted,
            'oleh' => $user->name,
            'lampiran' => $lampiranPath,
        ];

        $riwayat = $pengajuan->riwayat_monitoring ?? [];
        $riwayat[] = $riwayatEntry;

        $updateData['status_monitoring'] = $statusMonitoring;
        $updateData['riwayat_monitoring'] = $riwayat;

        // Update riwayat SPECIFIC TERMIN (ini yang ditampilkan di view UI baru)
        $terminId = $request->termin_id;
        $dataTermin = $pengajuan->data_termin ?? [];
        $terminFound = false;
        
        foreach ($dataTermin as &$termin) {
            if (isset($termin['id_termin']) && $termin['id_termin'] == $terminId) {
                $termin['status_monitoring'] = $statusMonitoring;
                if (!isset($termin['riwayat']) || !is_array($termin['riwayat'])) {
                    $termin['riwayat'] = [];
                }
                array_unshift($termin['riwayat'], $riwayatEntry);
                $terminFound = true;
                break;
            }
        }
        
        if ($terminFound) {
            $updateData['data_termin'] = $dataTermin;
        }

        // Jika tombol "Tandai Selesai" diklik atau status monitoring diset Selesai
        if ($request->filled('tandai_selesai') || in_array(strtolower($statusMonitoring), ['selesai', 'barang diterima', 'selesai / barang diterima'])) {
            $updateData['status'] = 'selesai';
            $updateData['status_monitoring'] = 'Selesai / Barang Diterima';
            $updateData['status_appr_4'] = 'selesai';
            $updateData['tanggal_approved_4'] = Carbon::now();
        }

        $pengajuan->update($updateData);

        // --- BLOK NOTIFIKASI ---
        if (isset($updateData['status']) && $updateData['status'] === 'selesai') {
            // Notifikasi Selesai Pengajuan
            $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'selesai_pengajuan'));
            foreach ([$pengajuan->approver1, $pengajuan->approver2, $pengajuan->approver3, $pengajuan->approver4] as $appr) {
                if ($appr) $appr->notify(new PengajuanBarangNotification($pengajuan, 'selesai_pengajuan'));
            }
        } else {
            // Notifikasi Update Pelacakan
            $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'update_pelacakan', [
                'status' => $statusMonitoring,
                'catatan' => $catatan ?: '-'
            ]));
        }
        // ------------------------

        return redirect()->back()->with('success', 'Status monitoring barang berhasil diperbarui!');
    }

    /**
     * Konfirmasi pemrosesan barang secara parsial (pengiriman bertahap) dengan Termin.
     */
    public function konfirmasiProses(Request $request, $id)
    {
        $request->validate([
            'jumlah_diproses' => 'required|array',
            'jumlah_diproses.*' => 'nullable|numeric|min:0',
        ]);

        $pengajuan = PengajuanBarang::findOrFail($id);
        $rincianBarang = $pengajuan->rincian_barang ?? [];
        $jumlahDiprosesData = $request->jumlah_diproses;
        $dataTermin = $pengajuan->data_termin ?? [];

        $hasChanges = false;
        
        $newTerminRincian = [];
        
        foreach ($rincianBarang as $index => &$item) {
            $inputJumlah = floatval($jumlahDiprosesData[$index] ?? 0);
            
            if ($inputJumlah > 0) {
                if (!isset($item['jumlah_diproses'])) $item['jumlah_diproses'] = 0;
                
                $sisa = ($item['jumlah'] ?? 0) - $item['jumlah_diproses'];
                $diprosesSekarang = min($inputJumlah, $sisa); 
                
                if ($diprosesSekarang > 0) {
                    $item['jumlah_diproses'] += $diprosesSekarang;
                    
                    $newTerminRincian[] = [
                        'index_barang' => $index,
                        'nama_barang' => $item['nama_barang'] ?? $item['deskripsi'] ?? 'Unknown',
                        'jumlah' => $diprosesSekarang,
                        'satuan' => $item['satuan'] ?? ''
                    ];
                    $hasChanges = true;
                }
            }
        }

        if ($hasChanges && count($newTerminRincian) > 0) {
            $terminId = count($dataTermin) + 1;
            $nowFormatted = Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
            
            $dataTermin[] = [
                'id_termin' => $terminId,
                'tanggal_dibuat' => $nowFormatted,
                'status_monitoring' => 'Diproses/Dipesan',
                'rincian' => $newTerminRincian,
                'riwayat' => [
                    [
                        'status' => 'Termin Dibuat',
                        'catatan' => 'Barang mulai diproses untuk termin ini.',
                        'waktu' => $nowFormatted,
                        'oleh' => Auth::user()->name,
                        'lampiran' => null
                    ]
                ]
            ];

            // Juga update riwayat global untuk memberitahu user ada termin baru
            $riwayatGlobal = $pengajuan->riwayat_monitoring ?? [];
            $riwayatGlobal[] = [
                'status' => 'Pengiriman Termin ' . $terminId,
                'catatan' => 'Admin telah membuat Termin ' . $terminId . ' untuk sebagian barang.',
                'waktu' => $nowFormatted,
                'oleh' => Auth::user()->name,
                'lampiran' => null,
            ];

            $pengajuan->update([
                'rincian_barang' => $rincianBarang,
                'data_termin' => $dataTermin,
                'riwayat_monitoring' => $riwayatGlobal,
            ]);

            return redirect()->back()->with('success', 'Termin ' . $terminId . ' berhasil dibuat.');
        }

        return redirect()->back()->with('info', 'Tidak ada data jumlah yang ditambahkan.');
    }

    /**
     * Migrasi data lama ke Termin 1 otomatis
     */
    public function migrasiTerminLama(Request $request, $id)
    {
        $pengajuan = PengajuanBarang::findOrFail($id);
        
        if (!empty($pengajuan->data_termin)) {
            return redirect()->back()->with('error', 'Pengajuan ini sudah memiliki termin.');
        }
        
        $rincian = $pengajuan->rincian_barang ?? [];
        $rincianTermin = [];
        
        // Tandai semua barang sebagai sudah diproses penuh
        foreach ($rincian as &$item) {
            $item['jumlah_diproses'] = $item['jumlah'] ?? 0;
            
            $rincianTermin[] = [
                'nama_barang' => $item['nama_barang'] ?? $item['deskripsi'] ?? '-',
                'jumlah' => $item['jumlah_diproses'],
                'satuan' => $item['satuan'] ?? ''
            ];
        }
        
        $nowFormatted = \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm');
        
        $terminBaru = [
            'id_termin' => 1,
            'tanggal_dibuat' => $nowFormatted,
            'status_monitoring' => $pengajuan->status_monitoring ?? 'Proses Purchasing',
            'rincian' => $rincianTermin,
            'riwayat' => [
                [
                    'status' => 'Migrasi Sistem',
                    'catatan' => 'Sistem secara otomatis merangkum semua data lama ke dalam Termin 1.',
                    'waktu' => $nowFormatted,
                    'oleh' => 'Sistem',
                    'lampiran' => null
                ]
            ]
        ];
        
        $pengajuan->rincian_barang = $rincian;
        $pengajuan->data_termin = [$terminBaru];
        $pengajuan->save();
        
        return redirect()->back()->with('success', 'Data lama berhasil dimigrasikan ke Termin 1. Silakan lanjutkan pelacakan termin.');
    }
}