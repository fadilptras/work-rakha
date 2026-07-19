<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanBarang;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PengajuanBarangNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PengajuanBarangController extends Controller
{
    public function index()
    {
        $title = 'Pengajuan Barang';
        $totalPengajuan = Auth::user()->pengajuanBarangs()->count();
        return view('users.pengajuan-barang.pengajuan-barang-form', compact('title', 'totalPengajuan'));
    }

    public function history(Request $request)
    {
        $query = Auth::user()->pengajuanBarangs()->latest();
        if ($request->filled('status') && $request->status != 'semua') {
            $query->where('status', $request->status);
        }
        $pengajuanBarangs = $query->paginate(15)->appends($request->query());

        return view('users.pengajuan-barang.pengajuan-barang-riwayat', [
            'title' => 'Riwayat Pengajuan Barang',
            'pengajuanBarangs' => $pengajuanBarangs,
        ]);
    }

    /**
     * Menyimpan pengajuan barang baru dengan dukungan hingga 4 Approver Dinamis.
     */
    public function store(Request $request)
    {
        // 1. Validasi input dari form
        $request->validate([
            'judul_pengajuan' => 'required|string|max:255',
            'divisi' => 'required|string|max:255',
            'rincian_deskripsi.*' => 'required|string',
            'rincian_jumlah.*' => 'required|integer|min:1',
            'rincian_satuan.*' => 'required|string',
            'file_pendukung.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

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

        // 4. Periksa apakah setelah tahap ini masih ada Approver berikutnya yang berstatus 'menunggu'
        $nextApprover = null;
        if ($currentStage < 2 && $pengajuan->status_appr_2 == 'menunggu') {
            $nextApprover = $pengajuan->approver2;
        } elseif ($currentStage < 3 && $pengajuan->status_appr_3 == 'menunggu') {
            $nextApprover = $pengajuan->approver3;
        } elseif ($currentStage < 4 && $pengajuan->status_appr_4 == 'menunggu') {
            $nextApprover = $pengajuan->approver4;
        }

        // 5. Eksekusi lanjutan berdasarkan ketersediaan Approver berikutnya
        if ($nextApprover) {
            // MASIH ADA APPROVER SELANJUTNYA -> Ubah status jadi diproses & kirim notif ke approver berikutnya
            $pengajuan->update(['status' => 'diproses']);
            $nextApprover->notify(new PengajuanBarangNotification($pengajuan, 'baru'));
        } else {
            // TIDAK ADA LAGI YANG MENUNGGU (FINISH 100%) -> Sahkan barang menjadi selesai!
            $pengajuan->update(['status' => 'selesai']);
            $pengajuan->user->notify(new PengajuanBarangNotification($pengajuan, 'disetujui_final'));
        }

        return redirect()->back()->with('success', 'Status pengajuan barang berhasil diperbarui.');
    }

    /**
     * Download PDF Pengajuan Barang (Mendukung hingga Approver 4)
     */
    public function download(PengajuanBarang $pengajuanBarang)
    {
        $pdf = Pdf::loadView('pdf.pengajuan-barang', [
            'pengajuanBarang' => $pengajuanBarang,
            'approver1' => User::find($pengajuanBarang->approver_barang_1_id),
            'approver2' => User::find($pengajuanBarang->approver_barang_2_id),
            'approver3' => User::find($pengajuanBarang->approver_barang_3_id),
            'approver4' => User::find($pengajuanBarang->approver_barang_4_id), // Ditambahkan
        ]);
        return $pdf->download('Pengajuan_' . $pengajuanBarang->id . '.pdf');
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
                    'deskripsi' => $deskripsi,
                    'satuan' => $request->rincian_satuan[$index] ?? '-',
                    'jumlah' => $request->rincian_jumlah[$index] ?? 0,
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

        if (!in_array($pengajuanBarang->status, ['diajukan', 'diproses'])) {
            return redirect()->back()->with('error', 'Pengajuan sudah selesai diproses, tidak bisa dibatalkan.');
        }

        $pengajuanBarang->update(['status' => 'dibatalkan']);

        return redirect()->back()->with('success', 'Pengajuan barang berhasil dibatalkan.');
    }
}