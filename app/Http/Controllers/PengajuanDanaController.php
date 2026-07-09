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
    /**
     * Menampilkan daftar pengajuan dana milik user yang login.
     */
    public function index(Request $request)
    {

        $query = Auth::user()->pengajuanDanas()->latest();

        // Tambahkan filter status sederhana untuk user
        if ($request->filled('status') && $request->status != 'semua') {
            $query->where('status', $request->status);
        }

        $pengajuanDanas = $query->paginate(10)->appends($request->query());

        // Mengarah ke view 'resources/views/users/pengajuan-dana.blade.php'
        return view('users.pengajuan-dana', [
            'title' => 'Pengajuan Dana',
            'pengajuanDanas' => $pengajuanDanas,
        ]);
    }

    /**
     * Menampilkan detail pengajuan dana.
     */
    public function show(PengajuanDana $pengajuanDana)
    {
        $this->authorize('view', $pengajuanDana);
        
        $pengajuanDana->load(['user', 'approver1', 'approver2', 'financeProcessor', 'user.managerKeuangan']); 
        
        return view('users.detail-pengajuan-dana', [
            'title' => 'Detail Pengajuan Dana',
            'pengajuanDana' => $pengajuanDana,
        ]);
    }

    /**
     * Menyimpan pengajuan dana baru.
     */
    public function store(Request $request)
    {
        // 1. Membersihkan input numerik
        if ($request->has('jumlah_dana_total')) {
            $request->merge(['jumlah_dana_total' => preg_replace('/[^0-9]/', '', $request->jumlah_dana_total)]);
        }
        if ($request->has('rincian_jumlah')) {
            $cleanedRincian = [];
            foreach ($request->rincian_jumlah as $jumlah) {
                $cleanedRincian[] = preg_replace('/[^0-9]/', '', $jumlah);
            }
            $request->merge(['rincian_jumlah' => $cleanedRincian]);
        }

        // 2. Validasi data
        $validatedData = $request->validate([
            'judul_pengajuan' => 'required|string|max:255',
            'divisi' => 'required|string|max:255',
            'nama_bank' => 'required_if:nama_bank_lainnya,null|nullable|string|max:255',
            'nama_bank_lainnya' => 'required_if:nama_bank,other|nullable|string|max:255',
            'no_rekening' => 'required|string|max:255',
            'nama_rek' => 'required|string|max:255',
            'jumlah_dana_total' => 'required|numeric|min:1',
            'rincian_deskripsi.*' => 'required|string|max:1000',
            'rincian_jumlah.*' => 'required|numeric|min:1',
            'file_pendukung.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:5120',
        ]);

        // 3. Proses Rincian
        $rincian = [];
        if (!empty($validatedData['rincian_deskripsi'])) {
            foreach ($validatedData['rincian_deskripsi'] as $key => $deskripsi) {
                $rincian[] = ['deskripsi' => $deskripsi, 'jumlah' => $validatedData['rincian_jumlah'][$key]];
            }
        }

        // 4. Proses Upload File
        $pathFiles = [];
        if ($request->hasFile('file_pendukung')) {
            foreach ($request->file('file_pendukung') as $file) {
                $pathFiles[] = $file->store('lampiran_dana', 'public');
            }
        }

        // 5. Tentukan Alur Persetujuan
        $user = Auth::user();
        $approver1_id = $user->approver_1_id;
        $approver2_id = $user->approver_2_id;
        $manager_keuangan_id = $user->manager_keuangan_id;

        // Validasi jika semua alur kosong
        if (!$approver1_id && !$approver2_id && !$manager_keuangan_id) {
            return redirect()->route('pengajuan_dana.index')->with('error', 'Alur persetujuan (Approver 1, Approver 2, atau Manager Keuangan) untuk akun Anda belum lengkap diatur. Harap hubungi Admin.');
        }

        // Tentukan status awal berdasarkan alur yang ada (logika skip)
        $approver1_status = $approver1_id ? 'menunggu' : 'skipped';
        $approver2_status = $approver2_id ? 'menunggu' : 'skipped';
        $payment_status = $manager_keuangan_id ? 'menunggu' : 'skipped';

        $status_awal = 'diajukan'; // Default (Menunggu Appr 1)
        $penerimaNotif = User::find($approver1_id);

        if ($approver1_status === 'skipped' && $approver2_status !== 'skipped') {
            $status_awal = 'diproses_appr_2'; // Langsung ke Appr 2
            $penerimaNotif = User::find($approver2_id);
        } elseif ($approver1_status === 'skipped' && $approver2_status === 'skipped' && $payment_status !== 'skipped') {
            $status_awal = 'proses_pembayaran'; // Langsung ke Finance
            $penerimaNotif = User::find($manager_keuangan_id);
        } elseif ($approver1_status === 'skipped' && $approver2_status === 'skipped' && $payment_status === 'skipped') {
            return redirect()->route('pengajuan_dana.index')->with('error', 'Alur persetujuan tidak valid (kosong). Hubungi Admin.');
        }

        // 6. Buat Pengajuan Dana
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
            
            'status' => $status_awal,
            
            'approver_1_id' => $approver1_id,
            'approver_1_status' => $approver1_status,
            
            'approver_2_id' => $approver2_id,
            'approver_2_status' => $approver2_status,
            
            'payment_status' => $payment_status,
        ]);

        // 7. Kirim Notifikasi
        if ($penerimaNotif) {
            Notification::send($penerimaNotif, new PengajuanDanaNotification($pengajuanDana, 'baru'));
        }

        $hrd = User::where('jabatan', 'HRD')->first();
        if ($hrd && $penerimaNotif && $hrd->id != $penerimaNotif->id) {
             Notification::send($hrd, new PengajuanDanaNotification($pengajuanDana, 'baru'));
        }

        return redirect()->route('pengajuan_dana.index')->with('success', 'Pengajuan dana berhasil dikirim!');
    }

    /**
     * Menyetujui pengajuan dana (oleh Approver 1 atau 2).
     */
    public function approve(Request $request, PengajuanDana $pengajuanDana)
    {
        $this->authorize('approve', $pengajuanDana);
        $user = Auth::user();
        $pemohon = $pengajuanDana->user;
        $updateData = [];
        $tipeNotifikasiUntukPemohon = '';
        $penerimaNotifBerikutnya = null;

        // KASUS 1: Disetujui oleh Approver 1
        if ($user->id == $pengajuanDana->approver_1_id && $pengajuanDana->status == 'diajukan') {
            $updateData['approver_1_status'] = 'disetujui';
            $updateData['approver_1_catatan'] = $request->catatan_persetujuan;
            $updateData['approver_1_approved_at'] = Carbon::now();
            
            if ($pengajuanDana->approver_2_status === 'menunggu') {
                $updateData['status'] = 'diproses_appr_2';
                $penerimaNotifBerikutnya = User::find($pengajuanDana->approver_2_id);
            } elseif ($pengajuanDana->payment_status === 'menunggu') {
                $updateData['status'] = 'proses_pembayaran';
                $penerimaNotifBerikutnya = User::find($pemohon->manager_keuangan_id); 
            } else {
                $updateData['status'] = 'selesai'; 
            }
            $tipeNotifikasiUntukPemohon = 'disetujui_atasan';
        }
        
        // KASUS 2: Disetujui oleh Approver 2
        elseif ($user->id == $pengajuanDana->approver_2_id && $pengajuanDana->status == 'diproses_appr_2') {
            if ($pengajuanDana->approver_1_status === 'menunggu') {
                 return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('error', 'Pengajuan ini harus disetujui oleh Approver 1 terlebih dahulu.');
            }
            $updateData['approver_2_status'] = 'disetujui';
            $updateData['approver_2_catatan'] = $request->catatan_persetujuan;
            $updateData['approver_2_approved_at'] = Carbon::now();

            if ($pengajuanDana->payment_status === 'menunggu') {
                $updateData['status'] = 'proses_pembayaran';
                $penerimaNotifBerikutnya = User::find($pemohon->manager_keuangan_id); 
            } else {
                $updateData['status'] = 'selesai'; 
            }
            $tipeNotifikasiUntukPemohon = 'disetujui_finance';
        }
        else {
            return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('error', 'Anda tidak memiliki wewenang untuk menyetujui pengajuan ini pada tahap ini.');
        }

        $pengajuanDana->update($updateData);
        Notification::send($pemohon, new PengajuanDanaNotification($pengajuanDana, $tipeNotifikasiUntukPemohon));
        
        if ($penerimaNotifBerikutnya) {
            Notification::send($penerimaNotifBerikutnya, new PengajuanDanaNotification($pengajuanDana, 'baru'));
        }
        
        return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('success', 'Pengajuan dana berhasil disetujui!');
    }

    /**
     * Menolak pengajuan dana (oleh Approver 1 atau 2).
     */
    public function reject(Request $request, PengajuanDana $pengajuanDana)
    {
        $this->authorize('approve', $pengajuanDana);
        $user = Auth::user();
        $pemohon = $pengajuanDana->user; 
        $updateData = ['status' => 'ditolak'];

        if ($user->id == $pengajuanDana->approver_1_id && $pengajuanDana->status == 'diajukan') {
            $updateData['approver_1_status'] = 'ditolak';
            $updateData['approver_1_catatan'] = $request->catatan_penolakan;
            $updateData['approver_1_approved_at'] = Carbon::now();
        }
        elseif ($user->id == $pengajuanDana->approver_2_id && $pengajuanDana->status == 'diproses_appr_2') {
            $updateData['approver_2_status'] = 'ditolak';
            $updateData['approver_2_catatan'] = $request->catatan_penolakan;
            $updateData['approver_2_approved_at'] = Carbon::now();
        }
        // Blok reject untuk Manager Keuangan sudah dihapus
        else {
            return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('error', 'Anda tidak memiliki wewenang untuk menolak pengajuan ini pada tahap ini.');
        }

        $pengajuanDana->update($updateData);
        Notification::send($pemohon, new PengajuanDanaNotification($pengajuanDana, 'ditolak'));
        return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('success', 'Pengajuan dana berhasil ditolak!');
    }

    /**
     * Menandai pengajuan sebagai "Sedang Diproses" oleh Finance.
     */
    public function prosesPembayaran(Request $request, PengajuanDana $pengajuanDana)
    {
        $this->authorize('prosesPembayaran', $pengajuanDana);

        if ($pengajuanDana->status !== 'proses_pembayaran' || $pengajuanDana->payment_status !== 'menunggu') {
            return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('error', 'Pengajuan ini tidak dalam status menunggu proses pembayaran.');
        }

        $pengajuanDana->update([
            'payment_status' => 'diproses',
            'finance_id' => Auth::id(), 
            'finance_processed_at' => Carbon::now(),
            'catatan_finance' => $request->catatan_proses ?? 'Pembayaran sedang diproses.'
        ]);
        
        Notification::send($pengajuanDana->user, new PengajuanDanaNotification($pengajuanDana, 'bukti_transfer'));
        return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('success', 'Status pembayaran diubah ke "Diproses".');
    }

    /**
     * Mengunggah bukti transfer oleh Finance dan menyelesaikan pengajuan.
     */
    public function uploadBuktiTransfer(Request $request, PengajuanDana $pengajuanDana)
    {
        $this->authorize('uploadBuktiTransfer', $pengajuanDana);
        $request->validate([
            'bukti_transfer' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($pengajuanDana->status !== 'proses_pembayaran' || $pengajuanDana->payment_status !== 'diproses') {
             return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('error', 'Pengajuan belum ditandai sedang diproses atau sudah selesai.');
        }

        $path = $request->file('bukti_transfer')->store('bukti_transfer', 'public');

        $pengajuanDana->update([
            'bukti_transfer' => $path,
            'status' => 'selesai',
            'payment_status' => 'selesai',
            'finance_id' => Auth::id(), 
        ]);
        
        Notification::send($pengajuanDana->user, new PengajuanDanaNotification($pengajuanDana, 'bukti_transfer'));
        return redirect()->route('pengajuan_dana.show', $pengajuanDana)->with('success', 'Bukti transfer berhasil diunggah! Pengajuan selesai.');
    }

    /**
     * Membatalkan pengajuan oleh pemohon.
     */
    public function cancel(PengajuanDana $pengajuanDana)
    {
        $this->authorize('cancel', $pengajuanDana);
        $pengajuanDana->update(['status' => 'dibatalkan']);

        $approver1 = $pengajuanDana->approver1;
        $approver2 = $pengajuanDana->approver2;
        $managerKeuanganDitugaskan = $pengajuanDana->user->managerKeuangan; 

        if ($approver1) {
            Notification::send($approver1, new PengajuanDanaNotification($pengajuanDana, 'dibatalkan'));
        }
        if ($approver2 && (!$approver1 || $approver2->id != $approver1->id)) {
            Notification::send($approver2, new PengajuanDanaNotification($pengajuanDana, 'dibatalkan'));
        }
        if ($managerKeuanganDitugaskan && $pengajuanDana->status == 'proses_pembayaran') {
             if ((!$approver1 || $managerKeuanganDitugaskan->id != $approver1->id) && (!$approver2 || $managerKeuanganDitugaskan->id != $approver2->id)) {
                Notification::send($managerKeuanganDitugaskan, new PengajuanDanaNotification($pengajuanDana, 'dibatalkan'));
            }
        }

        return redirect()->route('pengajuan_dana.index')->with('success', 'Pengajuan dana telah berhasil dibatalkan.');
    }

    /**
     * Download PDF pengajuan dana.
     */
    public function downloadPDF(PengajuanDana $pengajuanDana)
    {
        $this->authorize('view', $pengajuanDana);
        $pengajuanDana->load(['user.managerKeuangan', 'approver1', 'approver2', 'financeProcessor']);
        $pdf = PDF::loadView('pdf.pdf_pengajuan_dana', compact('pengajuanDana'));
        $namaJudul = \Illuminate\Support\Str::slug($pengajuanDana->judul_pengajuan, '-');
        $filename = "pengajuan-dana-{$pengajuanDana->id}-{$namaJudul}.pdf";
        return $pdf->download($filename);
    }
}