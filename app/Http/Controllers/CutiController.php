<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\User;
use App\Models\Absensi;
use App\Notifications\CutiNotification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class CutiController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $title = 'Pengajuan Cuti';
        $tahunIni = Carbon::now()->year;

        $totalCuti = $user->jatah_cuti ?? 12; 
        $sisaCuti = $user->sisa_cuti ?? 0;    
        
        $terpakaiTahunan = max(0, $totalCuti - $sisaCuti);

        $cutiRequests = Cuti::where('user_id', $user->id)
            ->whereYear('created_at', $tahunIni)
            ->latest()
            ->get();

        return view('users.cuti', compact('title', 'sisaCuti', 'terpakaiTahunan', 'cutiRequests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_cuti' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = Auth::user();
        
        if (!$user->approver_cuti_1_id && !$user->approver_cuti_2_id && !$user->approver_cuti_3_id && !$user->approver_cuti_4_id) {
            return redirect()->back()->with('error', 'Anda belum memiliki Approver Cuti yang diatur oleh Admin. Silakan hubungi Admin/HRD.');
        }

        $totalHari = Carbon::parse($request->tanggal_mulai)->diffInDays(Carbon::parse($request->tanggal_selesai)) + 1;

        $sisaCuti = $user->sisa_cuti ?? 0;
        if ($sisaCuti < $totalHari) {
            return redirect()->back()->with('error', 'Sisa cuti Anda tidak mencukupi untuk pengajuan ini. Sisa saldo Anda: ' . $sisaCuti . ' hari.');
        }

        $path = $request->hasFile('lampiran') ? $request->file('lampiran')->store('lampiran_cuti', 'public') : null;

        $cuti = Cuti::create([
            'user_id' => $user->id,
            'jenis_cuti' => $request->jenis_cuti,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'total_hari' => $totalHari,
            'alasan' => $request->alasan,
            'lampiran' => $path,
            'status' => 'diajukan',
            'approver_cuti_1_id' => $user->approver_cuti_1_id,
            'approver_cuti_2_id' => $user->approver_cuti_2_id,
            'approver_cuti_3_id' => $user->approver_cuti_3_id,
            'approver_cuti_4_id' => $user->approver_cuti_4_id,
            'status_approver_1' => $user->approver_cuti_1_id ? 'menunggu' : 'skipped',
            'status_approver_2' => $user->approver_cuti_2_id ? 'menunggu' : 'skipped',
            'status_approver_3' => $user->approver_cuti_3_id ? 'menunggu' : 'skipped',
            'status_approver_4' => $user->approver_cuti_4_id ? 'menunggu' : 'skipped',
        ]);

        $firstApprover = null;
        if ($cuti->approver1) $firstApprover = $cuti->approver1;
        elseif ($cuti->approver2) $firstApprover = $cuti->approver2;
        elseif ($cuti->approver3) $firstApprover = $cuti->approver3;
        elseif ($cuti->approver4) $firstApprover = $cuti->approver4;

        if ($firstApprover) {
            Notification::send($firstApprover, new CutiNotification($cuti, 'baru'));
        }

        return redirect()->route('cuti.create')->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $cuti = Cuti::with(['user', 'approver1', 'approver2', 'approver3', 'approver4'])->findOrFail($id);
        
        $isOwner = $user->id === $cuti->user_id;
        $isAdmin = $user->role === 'admin';
        
        $isApprover = in_array($user->id, [
            $cuti->approver_cuti_1_id, 
            $cuti->approver_cuti_2_id, 
            $cuti->approver_cuti_3_id,
            $cuti->approver_cuti_4_id
        ]);

        if (!$isOwner && !$isAdmin && !$isApprover) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $title = 'Detail Pengajuan Cuti';
        $sisaCuti = $cuti->user->sisa_cuti ?? 0;

        return view('users.detail-cuti', compact('cuti', 'sisaCuti', 'title'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'catatan' => 'nullable|string|max:255'
        ]);

        $cuti = Cuti::with(['user', 'approver1', 'approver2', 'approver3', 'approver4'])->findOrFail($id);
        $user = Auth::user();
        $statusInput = $request->status;

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
                "catatan_approver_{$currentStage}" => $request->catatan,
                'status' => 'ditolak'
            ]);
            Notification::send($cuti->user, new CutiNotification($cuti, 'ditolak'));
            return redirect()->back()->with('success', 'Pengajuan cuti telah ditolak.');
        }

        $cuti->update([
            "status_approver_{$currentStage}" => 'disetujui',
            "catatan_approver_{$currentStage}" => $request->catatan,
        ]);

        $nextApprover = null;
        if ($currentStage < 2 && $cuti->status_approver_2 == 'menunggu') {
            $nextApprover = $cuti->approver2;
        } elseif ($currentStage < 3 && $cuti->status_approver_3 == 'menunggu') {
            $nextApprover = $cuti->approver3;
        } elseif ($currentStage < 4 && $cuti->status_approver_4 == 'menunggu') {
            $nextApprover = $cuti->approver4;
        }

        if ($nextApprover) {
            $cuti->update(['status' => 'proses_finalisasi']);
            Notification::send($nextApprover, new CutiNotification($cuti, 'baru'));
        } else {
            $cuti->update(['status' => 'disetujui']);
            $cuti->user->decrement('sisa_cuti', $cuti->total_hari);
            


            Notification::send($cuti->user, new CutiNotification($cuti, 'disetujui'));
        }

        return redirect()->back()->with('success', 'Status pengajuan berhasil diperbarui.');
    }

    public function downloadPdf($id)
    {
        $cuti = Cuti::with(['user', 'approver1', 'approver2', 'approver3', 'approver4'])->findOrFail($id);
        $sisaCuti = $cuti->user->sisa_cuti ?? 0;

        $pdf = Pdf::loadView('pdf.cuti', [
            'cuti' => $cuti,
            'sisaCuti' => $sisaCuti 
        ])->setPaper('a4', 'portrait');

        $fileName = 'Cuti_' . ($cuti->user->name ?? 'User') . '_' . $cuti->id . '.pdf';
        return $pdf->download($fileName);
    }

    public function cancel(Cuti $cuti)
    {
        if (Auth::id() !== $cuti->user_id) abort(403);
        
        if (!in_array($cuti->status, ['diajukan', 'proses_finalisasi'])) {
            return redirect()->back()->with('error', 'Pengajuan sudah selesai diproses, tidak bisa dibatalkan.');
        }

        $cuti->status = 'dibatalkan';
        $cuti->save();

        return redirect()->back()->with('success', 'Pengajuan berhasil dibatalkan.');
    }
}