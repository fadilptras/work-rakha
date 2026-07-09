<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdminAktivitasController extends Controller
{
    /**
     * Menampilkan halaman daftar aktivitas karyawan.
     */
    public function index(Request $request)
    {
        // --- Ambil Data Untuk Dropdown Filter ---\
        $divisions = User::select('divisi')
            ->whereNotNull('divisi')
            ->where('divisi', '!=', '')
            ->distinct()
            ->orderBy('divisi')
            ->pluck('divisi');

        $users = User::orderBy('name')->get(['id', 'name']);

        // --- Proses Filter ---\
        // UPDATE: Default range jadi seminggu (7 hari terakhir)
        $defaultStart = now()->subDays(6)->toDateString(); 
        $defaultEnd   = now()->toDateString();

        $startDate = $request->input('start_date', $defaultStart);
        $endDate   = $request->input('end_date', $defaultEnd);
        
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        // Query Builder
        $query = Aktivitas::with('user')
                    // Filter Range Tanggal
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    // Urutkan dari yang terbaru (DESC) untuk tampilan web dashboard
                    ->orderBy('created_at', 'desc'); 

        // Filter Pilihan Divisi
        if ($divisi) {
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        // Filter Pilihan Karyawan Spesifik
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Eksekusi dengan Pagination (contoh: 15 data per halaman)
        $aktivitasHarian = $query->paginate(15)->withQueryString();

        return view('admin.aktivitas.index', compact(
            'aktivitasHarian', 
            'divisions', 
            'users', 
            'startDate', 
            'endDate', 
            'divisi', 
            'userId'
        ));
    }

    /**
     * Mengunduh laporan PDF berdasarkan filter yang sedang aktif.
     */
    public function downloadPdf(Request $request)
    {
        // Ambil filter yang sama dari request (sama dengan index)
        $defaultStart = now()->subDays(6)->toDateString(); 
        $defaultEnd   = now()->toDateString();

        $startDate = $request->input('start_date', $defaultStart);
        $endDate   = $request->input('end_date', $defaultEnd);

        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        // Query
        $query = Aktivitas::with('user')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'asc'); // Urutkan ASC (lama ke baru) untuk laporan PDF

        if ($divisi) {
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $aktivitas = $query->get();

        // Info Filter untuk Header PDF
        $filterInfo = 'Semua Karyawan';
        if($userId) {
            $user = User::find($userId);
            $filterInfo = $user ? $user->name : '-';
        } elseif($divisi) {
            $filterInfo = 'Divisi ' . $divisi;
        }

        // FIX: Mengirimkan variabel $startDate dan $endDate ke dalam view PDF
        $pdf = Pdf::loadView('admin.aktivitas.pdf', compact('aktivitas', 'filterInfo', 'startDate', 'endDate'));
        return $pdf->download('laporan_aktivitas_' . now()->format('Ymd') . '.pdf');
    }
}