<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanDana;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PengajuanDanaNotification;
use Illuminate\Support\Facades\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PengajuanDanaController extends Controller
{
    public function index(Request $request)
    {
        $totalPengajuan = Auth::user()->pengajuanDanas()->count();

        return view('users.pengajuan-dana.pengajuan-dana-form', [
            'title' => 'Pengajuan Dana',
            'totalPengajuan' => $totalPengajuan,
        ]);
    }

    public function history(Request $request)
    {
        $query = Auth::user()->pengajuanDanas()->latest();
        if ($request->filled('status') && $request->status != 'semua') {
            if ($request->status == 'diproses') {
                $query->whereIn('status', ['diajukan', 'diproses', 'proses_pembayaran', 'disetujui']);
            } else {
                $query->where('status', $request->status);
            }
        }
        $pengajuanDanas = $query->paginate(15)->appends($request->query());

        return view('users.pengajuan-dana.pengajuan-dana-riwayat', [
            'title' => 'Riwayat Pengajuan Dana',
            'pengajuanDanas' => $pengajuanDanas,
        ]);
    }

    public function show(PengajuanDana $pengajuanDana)
    {
        $userId = Auth::id();
        $user   = Auth::user();

        // Hanya pemilik, approver yang ditugaskan, manager keuangan, atau admin yang boleh akses
        $isOwner    = $pengajuanDana->user_id == $userId;
        $isApprover = in_array($userId, array_filter([
            $pengajuanDana->approver_dana_1_id,
            $pengajuanDana->approver_dana_2_id,
            $pengajuanDana->approver_dana_3_id,
            $pengajuanDana->approver_dana_4_id,
        ]));
        $isAdmin    = $user->role === 'admin';

        if (!$isOwner && !$isApprover && !$isAdmin) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan dana ini.');
        }

        $pengajuanDana->load(['user', 'approverDana1', 'approverDana2', 'approverDana3', 'approverDana4']);

        return view('users.pengajuan-dana.pengajuan-dana-detail', [
            'title' => 'Detail Pengajuan Dana',
            'pengajuanDana' => $pengajuanDana,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('jumlah_dana_total')) $request->merge(['jumlah_dana_total' => preg_replace('/[^0-9]/', '', $request->jumlah_dana_total)]);
        if ($request->has('rincian_jumlah')) {
            $cleanedRincian = [];
            foreach ($request->rincian_jumlah as $jumlah) {
                $cleanedRincian[] = preg_replace('/[^0-9]/', '', $jumlah);
            }
            $request->merge(['rincian_jumlah' => $cleanedRincian]);
        }

        $validatedData = $request->validate([
            'judul_pengajuan' => 'required|string|max:255',
            'divisi' => 'required|string|max:255',
            'nama_bank' => 'required_if:nama_bank_lainnya,null|nullable|string|max:255',
            'nama_bank_lainnya' => 'required_if:nama_bank,other|nullable|string|max:255',
            'no_rekening' => 'required|string|max:255',
            'nama_rek' => 'required|string|max:255',
            'jumlah_dana_total' => 'required|numeric|min:1',
            'rincian_deskripsi.*' => 'required|string|max:1000',
            'rincian_jumlah.*' => 'required|numeric|min:0',
            'file_pendukung.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240',
        ]);

        $rincian = [];
        if (!empty($validatedData['rincian_deskripsi'])) {
            foreach ($validatedData['rincian_deskripsi'] as $key => $deskripsi) {
                $rincian[] = ['deskripsi' => $deskripsi, 'jumlah' => $validatedData['rincian_jumlah'][$key]];
            }
        }

        $pathFiles = [];
        if ($request->hasFile('file_pendukung')) {
            foreach ($request->file('file_pendukung') as $file) {
                $pathFiles[] = $file->store('lampiran_dana', 'public');
            }
        }

        $user = Auth::user();
        $app1 = $user->approver_dana_1_id;
        $app2 = $user->approver_dana_2_id;
        $app3 = $user->approver_dana_3_id;
        $app4 = $user->approver_dana_4_id;

        if (!$app1 && !$app2 && !$app3 && !$app4) {
            return redirect()->route('pengajuan_dana.index')->with('error', 'Alur persetujuan untuk akun Anda belum lengkap diatur. Harap hubungi Admin.');
        }

        $st1 = $app1 ? 'menunggu' : 'skipped';
        $st2 = $app2 ? 'menunggu' : 'skipped';
        $st3 = $app3 ? 'menunggu' : 'skipped';
        $st4 = $app4 ? 'menunggu' : 'skipped';

        $pengajuanDana = PengajuanDana::create([
            'user_id' => $user->id,
            'judul_pengajuan' => $validatedData['judul_pengajuan'],
            'divisi' => $validatedData['divisi'],
            'nama_bank' => $validatedData['nama_bank'] === 'other' ? $validatedData['nama_bank_lainnya'] : $validatedData['nama_bank'],
            'no_rekening' => $validatedData['no_rekening'],
            'nama_rek' => $validatedData['nama_rek'],
            'total_dana' => $validatedData['jumlah_dana_total'],
            'rincian_dana' => $rincian,
            'lampiran' => $pathFiles,
            
            'status' => 'diajukan',
            
            'approver_dana_1_id' => $app1, 'approver_1_status' => $st1,
            'approver_dana_2_id' => $app2, 'approver_2_status' => $st2,
            'approver_dana_3_id' => $app3, 'approver_3_status' => $st3,
            'approver_dana_4_id' => $app4, 'approver_4_status' => $st4,
        ]);

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
        
        // Sesuaikan status awal berdasarkan siapa approver pertamanya
        if ($firstStage == 3) {
            $pengajuanDana->update(['status' => 'proses_pembayaran']);
        } elseif ($firstStage == 4) {
            $pengajuanDana->update(['status' => 'disetujui']);
        }

        if ($firstApprover) {
            Notification::send($firstApprover, new PengajuanDanaNotification($pengajuanDana, 'baru'));
        }

        return redirect()->route('pengajuan_dana.index')->with('success', 'Pengajuan dana berhasil dikirim!');
    }

    public function approve(Request $request, PengajuanDana $pengajuanDana)
    {
        $user = Auth::user();
        
        $currentStage = null;
        if ($user->id == $pengajuanDana->approver_dana_1_id && $pengajuanDana->approver_1_status == 'menunggu') {
            $currentStage = 1;
        } elseif ($user->id == $pengajuanDana->approver_dana_2_id && $pengajuanDana->approver_2_status == 'menunggu') {
            if ($pengajuanDana->approver_1_status == 'menunggu') return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
            $currentStage = 2;
        } elseif ($user->id == $pengajuanDana->approver_dana_3_id && $pengajuanDana->approver_3_status == 'menunggu') {
            if (in_array('menunggu', [$pengajuanDana->approver_1_status, $pengajuanDana->approver_2_status])) return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
            $currentStage = 3;
        } elseif ($user->id == $pengajuanDana->approver_dana_4_id && $pengajuanDana->approver_4_status == 'menunggu') {
            if (in_array('menunggu', [$pengajuanDana->approver_1_status, $pengajuanDana->approver_2_status, $pengajuanDana->approver_3_status])) return redirect()->back()->with('error', 'Menunggu persetujuan dari Approver sebelumnya.');
            $currentStage = 4;
        } else {
            return redirect()->back()->with('error', 'Otoritas tidak valid atau urutan persetujuan salah.');
        }

        $request->validate([
            'bukti_transfer' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $updateData = [
            "approver_{$currentStage}_status" => 'disetujui',
            "approver_{$currentStage}_catatan" => $request->catatan_persetujuan ?? 'Disetujui',
            "approver_{$currentStage}_approved_at" => Carbon::now(),
        ];

        if ($currentStage == 3 && $request->hasFile('bukti_transfer')) {
            $path = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
            $updateData['bukti_transfer'] = $path;
        }

        $pengajuanDana->update($updateData);

        $nextApprover = null;
        $nextStage = null;
        if ($currentStage < 2 && $pengajuanDana->approver_2_status == 'menunggu') {
            $nextApprover = $pengajuanDana->approverDana2;
            $nextStage = 2;
        } elseif ($currentStage < 3 && $pengajuanDana->approver_3_status == 'menunggu') {
            $nextApprover = $pengajuanDana->approverDana3;
            $nextStage = 3;
        } elseif ($currentStage < 4 && $pengajuanDana->approver_4_status == 'menunggu') {
            $nextApprover = $pengajuanDana->approverDana4;
            $nextStage = 4;
        }

        if ($nextApprover) {
            $status = 'diproses';
            if ($nextStage == 3) $status = 'proses_pembayaran';
            if ($nextStage == 4) $status = 'disetujui';

            $pengajuanDana->update(['status' => $status]);
            Notification::send($nextApprover, new PengajuanDanaNotification($pengajuanDana, 'baru'));
            Notification::send($pengajuanDana->user, new PengajuanDanaNotification($pengajuanDana, 'disetujui_parsial'));
        } else {
            $pengajuanDana->update(['status' => 'selesai']);
            
            $notificationType = $currentStage == 3 ? 'bukti_transfer' : 'disetujui_final';
            Notification::send($pengajuanDana->user, new PengajuanDanaNotification($pengajuanDana, $notificationType));
        }

        return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('success', 'Pengajuan dana berhasil disetujui!');
    }

    public function reject(Request $request, PengajuanDana $pengajuanDana)
    {
        $user = Auth::user();
        
        $currentStage = null;
        if ($user->id == $pengajuanDana->approver_dana_1_id && $pengajuanDana->approver_1_status == 'menunggu') $currentStage = 1;
        elseif ($user->id == $pengajuanDana->approver_dana_2_id && $pengajuanDana->approver_2_status == 'menunggu') $currentStage = 2;
        elseif ($user->id == $pengajuanDana->approver_dana_3_id && $pengajuanDana->approver_3_status == 'menunggu') $currentStage = 3;
        elseif ($user->id == $pengajuanDana->approver_dana_4_id && $pengajuanDana->approver_4_status == 'menunggu') $currentStage = 4;
        else {
            return redirect()->back()->with('error', 'Otoritas tidak valid.');
        }

        $pengajuanDana->update([
            "approver_{$currentStage}_status" => 'ditolak',
            "approver_{$currentStage}_catatan" => $request->catatan_penolakan,
            "approver_{$currentStage}_approved_at" => Carbon::now(),
            'status' => 'ditolak'
        ]);

        Notification::send($pengajuanDana->user, new PengajuanDanaNotification($pengajuanDana, 'ditolak'));
        return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('success', 'Pengajuan dana berhasil ditolak!');
    }



    public function cancel(PengajuanDana $pengajuanDana)
    {
        $pengajuanDana->update(['status' => 'dibatalkan']);

        // Cari approver yang sedang bertugas (yang statusnya masih 'menunggu')
        $currentApprover = null;
        if ($pengajuanDana->approver_1_status == 'menunggu' && $pengajuanDana->approver_dana_1_id) {
            $currentApprover = $pengajuanDana->approverDana1;
        } elseif ($pengajuanDana->approver_2_status == 'menunggu' && $pengajuanDana->approver_dana_2_id) {
            $currentApprover = $pengajuanDana->approverDana2;
        } elseif ($pengajuanDana->approver_3_status == 'menunggu' && $pengajuanDana->approver_dana_3_id) {
            $currentApprover = $pengajuanDana->approverDana3;
        } elseif ($pengajuanDana->approver_4_status == 'menunggu' && $pengajuanDana->approver_dana_4_id) {
            $currentApprover = $pengajuanDana->approverDana4;
        }

        if ($currentApprover) {
            Notification::send($currentApprover, new PengajuanDanaNotification($pengajuanDana, 'dibatalkan'));
        }

        return redirect()->route('pengajuan_dana.index')->with('success', 'Pengajuan dana telah berhasil dibatalkan.');
    }

    public function downloadPDF(PengajuanDana $pengajuanDana)
    {
        $pengajuanDana->load(['user', 'approverDana1', 'approverDana2', 'approverDana3', 'approverDana4']);
        $pdf = PDF::loadView('pdf.pdf_pengajuan_dana', compact('pengajuanDana'));
        $namaJudul = \Illuminate\Support\Str::slug($pengajuanDana->judul_pengajuan, '-');
        $filename = "pengajuan-dana-{$pengajuanDana->id}-{$namaJudul}.pdf";
        return $pdf->download($filename);
    }
}