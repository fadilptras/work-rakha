<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanDana;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Notification; 
use App\Notifications\PengajuanDanaNotification;

class AdminPengajuanDanaController extends Controller
{
    /**
     * Menampilkan daftar semua pengajuan dana (untuk admin).
     */
    public function index(Request $request)
    {
        $query = PengajuanDana::with('user')->latest();

        // [BARU] Logika Tabulasi Status
        // Default tab adalah 'pending' (Diproses) agar admin fokus ke tugas aktif
        $activeTab = $request->input('tab', 'pending'); 

        switch ($activeTab) {
            case 'pending':
                // Menampilkan yang sedang berjalan
                $query->whereIn('status', ['diajukan', 'diproses_appr_2', 'proses_pembayaran']);
                break;
            case 'approved':
                // Menampilkan yang sudah sukses
                $query->where('status', 'selesai');
                break;
            case 'rejected':
                // Menampilkan yang gagal/batal
                $query->whereIn('status', ['ditolak', 'dibatalkan']);
                break;
            default:
                // Jika tab='all', tidak ada filter status (tampilkan semua)
                break;
        }

        // --- Filter Lama Tetap Ada ---
        if ($request->filled('karyawan_id')) {
            $query->where('user_id', $request->karyawan_id);
        }
        if ($request->filled('divisi')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('divisi', $request->divisi);
            });
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $pengajuanDanas = $query->paginate(10)->appends($request->query());

        $karyawanList = User::where('role', 'user')->orderBy('name')->get();
        $divisiList = User::where('role', 'user')
                            ->whereNotNull('divisi')
                            ->select('divisi')
                            ->distinct()
                            ->orderBy('divisi')
                            ->get();

        return view('admin.pengajuan-dana.index', [
            'title' => 'Kelola Pengajuan Dana',
            'pengajuanDanas' => $pengajuanDanas,
            'karyawanList' => $karyawanList, 
            'divisiList' => $divisiList,
            'activeTab' => $activeTab // [BARU] Kirim info tab aktif ke view
        ]);
    }

    /**
     * Menampilkan detail pengajuan dana (untuk admin).
     */
    public function show(PengajuanDana $pengajuanDana)
    {
        $pengajuanDana->load(['user', 'approver1', 'approver2', 'financeProcessor', 'user.managerKeuangan']);
        
        return view('admin.pengajuan-dana.show', [
            'title' => 'Detail Pengajuan Dana',
            'pengajuanDana' => $pengajuanDana,
        ]);
    }

    /**
     * Mengunduh PDF detail pengajuan dana (untuk admin).
     */
    public function downloadPDF(PengajuanDana $pengajuanDana)
    {
        // [FIX] Gunakan nama relasi yang benar dari Model PengajuanDana
        $pengajuanDana->load(['user', 'approver1', 'approver2', 'financeProcessor', 'user.managerKeuangan']);
        
        $pdf = PDF::loadView('pdf.pdf_pengajuan_dana', compact('pengajuanDana'));
        $namaJudul = \Illuminate\Support\Str::slug($pengajuanDana->judul_pengajuan, '-');
        $filename = "pengajuan-dana-{$pengajuanDana->id}-{$namaJudul}.pdf";
        return $pdf->download($filename);
    }

    /**
     * [UPDATE] Mengunduh rekap PDF sesuai Tab yang dipilih (Pending/Selesai/Ditolak).
     */
    public function downloadRekapPDF(Request $request)
    {
        $query = PengajuanDana::with('user')->latest();
        
        // [BARU] 1. Terapkan Filter TAB (Status)
        // Ambil 'tab' dari request, default 'pending' jika tidak ada (sesuai default index)
        $activeTab = $request->input('tab', 'pending'); 

        switch ($activeTab) {
            case 'pending':
                // Hanya yang sedang berjalan
                $query->whereIn('status', ['diajukan', 'diproses_appr_2', 'proses_pembayaran']);
                break;
            case 'approved':
                // Hanya yang selesai
                $query->where('status', 'selesai');
                break;
            case 'rejected':
                // Hanya yang ditolak/batal
                $query->whereIn('status', ['ditolak', 'dibatalkan']);
                break;
            default:
                // Jika tab='all', ambil semua data (tidak ada where status)
                break;
        }

        // 2. Filter Tanggal, Karyawan, Divisi (Logika Lama)
        $startDate = null; $endDate = null;
        $karyawanId = $request->input('karyawan_id');
        $divisi = $request->input('divisi');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        if ($karyawanId) {
            $query->where('user_id', $karyawanId);
        }
        if ($divisi) {
            $query->whereHas('user', function($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }
        
        $pengajuanDanas = $query->get();

        // 3. Persiapan Data Tampilan PDF
        $karyawanName = 'Semua Karyawan'; 
        if ($karyawanId) {
            $karyawan = User::find($karyawanId);
            if ($karyawan) $karyawanName = $karyawan->name;
        }
        $divisiName = $divisi ?: 'Semua Divisi';

        // [OPTIONAL] Ubah judul file agar admin tau ini rekap apa
        $fileTag = strtoupper($activeTab); 

        $pdf = Pdf::loadView('admin.pengajuan-dana.pdf_rekap', compact(
            'pengajuanDanas', 
            'startDate', 
            'endDate', 
            'karyawanName', 
            'divisiName',
            'activeTab' // Kirim juga tab nya jika ingin ditampilkan di judul PDF
        ));
        
        $filename = "rekap-pengajuan-dana-{$fileTag}-" . Carbon::now()->format('Y-m-d') . ".pdf";
        return $pdf->download($filename);
    }

    /**
     * Menampilkan halaman pengaturan approver (untuk admin).
     */
    public function showSetApprovers()
    {
        $employees = User::where('role', 'user')->orderBy('name')->get();

        $approvers =  User::where('name', '!=', 'Admin Rakha')
                                  ->orderBy('name')
                                  ->get();

        $financeManagers = $approvers; // Samakan list finance manager dengan approver

        return view('admin.pengajuan-dana.set-approvers', [
            'title' => 'Atur Alur Persetujuan Karyawan',
            'employees' => $employees,
            'approvers' => $approvers,
            'financeManagers' => $financeManagers,
        ]);
        
    }

    /**
     * Menyimpan pengaturan approver (untuk admin).
     */
    public function saveSetApprovers(Request $request)
    {
        $request->validate([
            'approver_1' => 'required|array',
            'approver_2' => 'required|array',
            'manager_keuangan' => 'required|array', 
            'approver_1.*' => 'nullable|exists:users,id',
            'approver_2.*' => 'nullable|exists:users,id|different:approver_1.*', 
            'manager_keuangan.*' => 'nullable|exists:users,id', 
        ], [
            'approver_2.*.different' => 'Approver 1 dan Approver 2 tidak boleh orang yang sama.',
        ]);

        $approver1Data = $request->input('approver_1');
        $approver2Data = $request->input('approver_2');
        $managerKeuanganData = $request->input('manager_keuangan'); 

        DB::beginTransaction();
        try {
            foreach ($approver1Data as $userId => $approver1Id) {
                $user = User::find($userId);
                if ($user) {
                    $user->approver_1_id = $approver1Id; // Akan null jika "-- Tidak Ada --"

                    // Pastikan key ada sebelum mengakses
                    $user->approver_2_id = $approver2Data[$userId] ?? null; 
                    $user->manager_keuangan_id = $managerKeuanganData[$userId] ?? null;
                    
                    $user->save();
                }
            }
            DB::commit();
            return redirect()->route('admin.pengajuan_dana.set_approvers.index')->with('success', 'Pengaturan alur persetujuan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Sebaiknya log error ini
            // Log::error('Gagal simpan approver: ' . $e->getMessage());
            return redirect()->route('admin.pengajuan_dana.set_approvers.index')->with('error', 'Terjadi kesalahan. Perubahan dibatalkan.');
        }
    }

    /**
     * [UPDATE] Admin mengambil alih proses pembayaran (Bukti Transfer Opsional).
     */
    public function markAsPaid(Request $request, PengajuanDana $pengajuanDana)
    {
        // Validasi status harus dalam tahap pembayaran
        if (!in_array($pengajuanDana->status, ['proses_pembayaran', 'diproses_appr_2'])) {
             return back()->with('error', 'Pengajuan ini tidak dalam status menunggu pembayaran.');
        }

        $request->validate([
            'bukti_transfer' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'catatan_admin' => 'nullable|string|max:255',
        ]);

        // Siapkan data update dasar
        $updateData = [
            'status' => 'selesai',           
            'payment_status' => 'selesai',   
            'finance_id' => Auth::id(),      
            'finance_processed_at' => Carbon::now(),
            'catatan_finance' => $request->catatan_admin ?? 'Selesai',
        ];


        if ($request->hasFile('bukti_transfer')) {
            $path = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
            $updateData['bukti_transfer'] = $path;
        }

        // Lakukan update
        $pengajuanDana->update($updateData);

        // Kirim notifikasi
        Notification::send($pengajuanDana->user, new PengajuanDanaNotification($pengajuanDana, 'bukti_transfer'));

        return back()->with('success', 'Pembayaran berhasil diselesaikan oleh Admin.');
    }
    
    
    /**
     * Menghapus pengajuan dana.
     */
    public function destroy($id)
    {
        $pengajuan = PengajuanDana::findOrFail($id);
    
        // 1. Hapus file Bukti Transfer (ini biasanya string tunggal, jadi aman)
        if ($pengajuan->bukti_transfer && \Illuminate\Support\Facades\Storage::disk('public')->exists($pengajuan->bukti_transfer)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($pengajuan->bukti_transfer);
        }
        
        // 2. Hapus file Lampiran Pendukung (KARENA INI ARRAY, GUNAKAN LOOP)
        if ($pengajuan->lampiran && is_array($pengajuan->lampiran)) {
            foreach ($pengajuan->lampiran as $file) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($file)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
                }
            }
        }
    
        // 3. Hapus Record Database
        $pengajuan->delete();
    
        return back()->with('success', 'Data pengajuan dan file terkait berhasil dihapus.');
    }
}