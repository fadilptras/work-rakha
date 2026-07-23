<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuti;
use App\Models\Absensi;
use App\Models\User;
use App\Models\Holiday;
use App\Notifications\PengajuanCutiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Carbon\CarbonPeriod; 
use Barryvdh\DomPDF\Facade\Pdf;

class AdminCutiController extends Controller
{
    public function index(Request $request)
    {
        $query = Cuti::with('user')->latest();
        $activeTab = $request->input('tab', 'pending'); 

        switch ($activeTab) {
            case 'pending':
                $query->whereIn('status', ['diajukan', 'proses_finalisasi']);
                break;
            case 'approved':
                $query->where('status', 'disetujui');
                break;
            case 'rejected':
                $query->where('status', 'ditolak');
                break;
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        $cutiRequests = $query->paginate(10)->withQueryString(); 
        
        $users = Cache::rememberForever('karyawan_list_dropdown', function () {
            return User::where('role', 'user')->orderBy('name')->get(['id', 'name']);
        });

        return view('admin.cuti.index', [
            'title' => 'Manajemen Pengajuan Cuti',
            'cutiRequests' => $cutiRequests,
            'users' => $users, 
            'activeTab' => $activeTab
        ]);
    }
    
    public function setApprovers()
    {
        $potentialApprovers = Cache::rememberForever('approvers_list_dropdown', function () {
            return User::where('name', '!=', 'Admin Rakha')->orderBy('name')->get(['id', 'name']);
        });
        
        $admins = Cache::rememberForever('admins_list_dropdown', function () {
            return User::orderBy('name')->get(['id', 'name', 'role']);
        });
        
        $employees = User::where('role', 'user')
            ->orderBy('name')
            ->get(['id', 'name', 'divisi', 'approver_cuti_1_id', 'approver_cuti_2_id', 'approver_cuti_3_id', 'approver_cuti_4_id']);

        return view('admin.cuti.set-approvers', [
            'employees' => $employees,
            'approvers' => $potentialApprovers, 
            'admins' => $admins, 
            'title' => 'Set Approver Pengajuan Cuti'
        ]);
    }

    public function saveApprovers(Request $request)
    {
        $request->validate([
            'approver_cuti_1.*' => 'nullable|exists:users,id',
            'approver_cuti_2.*' => 'nullable|exists:users,id',
            'approver_cuti_3.*' => 'nullable|exists:users,id',
            'approver_cuti_4.*' => 'nullable|exists:users,id',
        ]);

        DB::transaction(function () use ($request) {
            if ($request->has('approver_cuti_1')) {
                foreach ($request->approver_cuti_1 as $userId => $val) {
                    User::where('id', $userId)->update([
                        'approver_cuti_1_id' => $request->approver_cuti_1[$userId] ?? null,
                        'approver_cuti_2_id' => $request->approver_cuti_2[$userId] ?? null,
                        'approver_cuti_3_id' => $request->approver_cuti_3[$userId] ?? null,
                        'approver_cuti_4_id' => $request->approver_cuti_4[$userId] ?? null,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Pengaturan 4 Approver Cuti berhasil diperbarui.');
    }
    
    public function show(Cuti $cuti)
    {
        $title = 'Detail Pengajuan Cuti';
        return view('admin.cuti.show', compact('cuti', 'title'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'alasan' => 'nullable|string|max:255' 
        ]);

        return DB::transaction(function () use ($request, $id) {
            // lockForUpdate mencegah race condition jika dua approver approve bersamaan
            $cuti = Cuti::with(['user', 'approver1', 'approver2', 'approver3', 'approver4'])
                ->lockForUpdate()
                ->findOrFail($id);

            $user = Auth::user();
            $statusInput = $request->status;
            $catatanInput = $request->alasan ?? $request->catatan;

            $currentStage = null;
            if ($user->id == $cuti->approver_cuti_1_id && $cuti->status_approver_1 == 'menunggu') {
                $currentStage = 1;
            } elseif ($user->id == $cuti->approver_cuti_2_id && $cuti->status_approver_2 == 'menunggu') {
                if ($cuti->status_approver_1 == 'menunggu') return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
                $currentStage = 2;
            } elseif ($user->id == $cuti->approver_cuti_3_id && $cuti->status_approver_3 == 'menunggu') {
                if (in_array('menunggu', [$cuti->status_approver_1, $cuti->status_approver_2])) return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
                $currentStage = 3;
            } elseif ($user->id == $cuti->approver_cuti_4_id && $cuti->status_approver_4 == 'menunggu') {
                if (in_array('menunggu', [$cuti->status_approver_1, $cuti->status_approver_2, $cuti->status_approver_3])) return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
                $currentStage = 4;
            } else {
                return redirect()->back()->with('error', 'Otoritas tidak valid atau urutan persetujuan belum sampai ke Anda.');
            }

            if ($statusInput == 'ditolak') {
                $cuti->update([
                    "status_approver_{$currentStage}" => 'ditolak',
                    "catatan_approver_{$currentStage}" => $catatanInput,
                    'status' => 'ditolak'
                ]);
                Notification::send($cuti->user, new PengajuanCutiNotification($cuti, 'ditolak'));
                return redirect()->back()->with('success', 'Pengajuan cuti telah ditolak.');
            }

            $cuti->update([
                "status_approver_{$currentStage}" => 'disetujui',
                "catatan_approver_{$currentStage}" => $catatanInput,
            ]);

            // Logika next approver diubah menjadi berjenjang (<) agar tidak bypass jika ada yang skipped
            $nextApprover = null;
            if ($currentStage < 2 && $cuti->status_approver_2 == 'menunggu') $nextApprover = $cuti->approver2;
            elseif ($currentStage < 3 && $cuti->status_approver_3 == 'menunggu') $nextApprover = $cuti->approver3;
            elseif ($currentStage < 4 && $cuti->status_approver_4 == 'menunggu') $nextApprover = $cuti->approver4;

            if ($nextApprover) {
                $cuti->update(['status' => 'proses_finalisasi']);
                Notification::send($nextApprover, new PengajuanCutiNotification($cuti, 'baru'));
                Notification::send($cuti->user, new PengajuanCutiNotification($cuti, 'disetujui_parsial'));
            } else {
                $cuti->update(['status' => 'disetujui']);
                // lockForUpdate memastikan decrement tidak bisa terpanggil dua kali bersamaan
                $cuti->user->decrement('sisa_cuti', $cuti->total_hari);
                Notification::send($cuti->user, new PengajuanCutiNotification($cuti, 'disetujui'));
            }

            return redirect()->back()->with('success', 'Status persetujuan berhasil diperbarui.');
        });
    }

    public function download(Cuti $cuti)
    {
        // Gunakan relasi dari model Cuti (bukan dari user) agar data approver
        // mencerminkan siapa yang sesungguhnya menandatangani, bukan siapa approver user saat ini
        $cuti->load(['user', 'approver1', 'approver2', 'approver3', 'approver4']);

        $sisaCuti = $cuti->user->sisa_cuti ?? 0;

        $pdf = Pdf::loadView('pdf.cuti', [
            'cuti'      => $cuti,
            'approver1' => $cuti->approver1,
            'approver2' => $cuti->approver2,
            'approver3' => $cuti->approver3,
            'approver4' => $cuti->approver4,
            'sisaCuti'  => $sisaCuti
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download($cuti->nomor_pengajuan . '.pdf');
    }

    public function pengaturanCuti()
    {
        $tahunIni = Carbon::now()->year;

        $users = User::where('role', 'user')->orderBy('name')->get(['id', 'name', 'jabatan', 'jatah_cuti', 'sisa_cuti'])->map(function ($user) {
            $totalCuti = $user->jatah_cuti ?? 12;
            $sisaCuti = $user->sisa_cuti ?? 0;
            $user->cuti_terpakai = max(0, $totalCuti - $sisaCuti);
            $user->sisa_cuti = $sisaCuti; 
            return $user;
        });

        return view('admin.cuti.pengaturan', [
            'title' => 'Pengaturan Jatah Cuti (' . $tahunIni . ')',
            'users' => $users
        ]);
    }

    public function updatePengaturanCuti(Request $request)
    {
        $request->validate([
            'jatah_cuti'   => 'required|array',
            'jatah_cuti.*' => 'required|integer|min:0',
        ]);

        // Load semua user yang relevan sekaligus (anti N+1)
        $userIds  = array_keys($request->jatah_cuti);
        $users    = User::whereIn('id', $userIds)->get()->keyBy('id');

        DB::transaction(function () use ($request, $users) {
            foreach ($request->jatah_cuti as $userId => $newJatah) {
                $user = $users->get($userId);
                if (!$user) continue;

                $oldJatah  = $user->jatah_cuti ?? 12;
                $difference = $newJatah - $oldJatah;

                $updateData = ['jatah_cuti' => $newJatah];
                if ($difference != 0) {
                    $updateData['sisa_cuti'] = max(0, ($user->sisa_cuti ?? 0) + $difference);
                }

                $user->update($updateData);
            }
        });

        return redirect()->route('admin.cuti.pengaturan')->with('success', 'Jatah cuti berhasil diperbarui.');
    }

    public function downloadRekapPDF(Request $request)
    {
        $query = Cuti::with('user')->latest();
        $activeTab = $request->input('tab', 'pending'); 

        switch ($activeTab) {
            case 'pending': $query->whereIn('status', ['diajukan', 'proses_finalisasi']); break;
            case 'approved': $query->whereIn('status', ['disetujui', 'diterima']); break;
            case 'rejected': $query->where('status', 'ditolak'); break;
        }

        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        $cutiRequests = $query->get();
        $userName = 'Semua Karyawan';
        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);
            if ($user) $userName = $user->name;
        }
        
        $startDate = $request->tanggal_mulai;
        $endDate = $request->tanggal_akhir;

        $pdf = Pdf::loadView('admin.cuti.pdf_rekap', compact('cutiRequests', 'activeTab', 'userName', 'startDate', 'endDate'));
        $pdf->setPaper('a4', 'landscape'); 
        return $pdf->download("rekap-cuti-" . strtoupper($activeTab) . "-" . Carbon::now()->format('Y-m-d') . ".pdf");
    }

    public function downloadPengaturanPDF()
    {
        $tahunIni = Carbon::now()->year;

        $users = User::where('role', 'user')->orderBy('name')->get(['id', 'name', 'jabatan', 'jatah_cuti', 'sisa_cuti'])->map(function ($user) {
            $totalCuti = $user->jatah_cuti ?? 12;
            $sisaCuti = $user->sisa_cuti ?? 0;
            $user->cuti_terpakai = max(0, $totalCuti - $sisaCuti);
            $user->sisa_cuti = $sisaCuti;
            return $user;
        });

        $pdf = Pdf::loadView('admin.cuti.pdf_pengaturan', [
            'users' => $users,
            'tahun' => $tahunIni
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('Laporan-Sisa-Cuti-Karyawan-' . $tahunIni . '.pdf');
    }

    public function destroy($id)
    {
        $cuti = Cuti::findOrFail($id);

        if ($cuti->lampiran && \Illuminate\Support\Facades\Storage::disk('public')->exists($cuti->lampiran)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($cuti->lampiran);
        }

        if ($cuti->status == 'disetujui') {
            $cuti->user->increment('sisa_cuti', $cuti->total_hari);


        }

        $cuti->delete();

        return redirect()->back()->with('success', 'Data pengajuan cuti berhasil dihapus.');
    }
}