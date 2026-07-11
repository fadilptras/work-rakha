<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AdminAktivitasController extends Controller
{
    /**
     * Menampilkan halaman daftar aktivitas karyawan.
     */
    public function index(Request $request)
    {
        $users = Cache::rememberForever('karyawan_list_dropdown', function () {
            return User::where('role', 'user')->orderBy('name')->get(['id', 'name', 'divisi']);
        });

        $divisions = $users->pluck('divisi')->filter()->unique()->values();

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

        if ($userId) {
            $query->where('user_id', $userId);
        }

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
        $defaultStart = now()->subDays(6)->toDateString(); 
        $defaultEnd   = now()->toDateString();

        $startDate = $request->input('start_date', $defaultStart);
        $endDate   = $request->input('end_date', $defaultEnd);

        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

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

        $filterInfo = 'Semua Karyawan';
        if ($userId) {
            $usersList = Cache::get('karyawan_list_dropdown', collect());
            $user = $usersList->firstWhere('id', $userId);
            $filterInfo = $user ? $user->name : '-';
        } elseif ($divisi) {
            $filterInfo = 'Divisi ' . $divisi;
        }

        $pdf = Pdf::loadView('admin.aktivitas.pdf', compact('aktivitas', 'filterInfo', 'startDate', 'endDate'));
        return $pdf->download('laporan_aktivitas_' . now()->format('Ymd') . '.pdf');
    }
}