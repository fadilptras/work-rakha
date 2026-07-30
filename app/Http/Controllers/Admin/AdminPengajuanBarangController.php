<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanBarang;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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

        // 4. Periksa apakah setelah tahap ini masih ada Approver berikutnya yang berstatus 'menunggu'
        $nextApprover = null;
        if ($currentStage < 2 && $pengajuan->status_appr_2 == 'menunggu') $nextApprover = $pengajuan->approver2;
        elseif ($currentStage < 3 && $pengajuan->status_appr_3 == 'menunggu') $nextApprover = $pengajuan->approver3;
        elseif ($currentStage < 4 && $pengajuan->status_appr_4 == 'menunggu') $nextApprover = $pengajuan->approver4;

        if ($nextApprover) {
            // Masih ada antrean berikutnya -> Ubah status jadi diproses
            $pengajuan->update(['status' => 'diproses']);
        } else {
            // Admin adalah approver terakhir -> Tutup status utama jadi selesai!
            $pengajuan->update(['status' => 'selesai']);
        }

        return redirect()->back()->with('success', 'Persetujuan berhasil disimpan!');
    }

    /**
     * Download PDF Pengajuan Barang (Admin).
     */
    public function downloadPdf($id)
    {
        $pengajuan = PengajuanBarang::with(['user', 'approver1', 'approver2', 'approver3', 'approver4'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.pengajuan-barang', ['pengajuanBarang' => $pengajuan])->setPaper('a4', 'portrait');
        return $pdf->download($pengajuan->nomor_pengajuan . '.pdf');
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
}