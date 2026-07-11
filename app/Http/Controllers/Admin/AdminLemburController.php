<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;

class AdminLemburController extends Controller
{
    /**
     * Menampilkan rekap lembur karyawan.
     */
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        $users = Cache::rememberForever('karyawan_list_dropdown', function () {
            return User::where('role', 'user')->orderBy('name')->get(['id', 'name', 'divisi']);
        });
        
        $divisions = $users->pluck('divisi')->filter()->unique()->values();

        $query = Lembur::with('user');
        
        if ($divisi) {
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        $lemburRecords = $query->latest('tanggal')->paginate(15);
        
        return view('admin.lembur.index', [
            'title' => 'Rekap Lembur Karyawan',
            'lemburRecords' => $lemburRecords,
            'divisions' => $divisions,
            'users' => $users, // Kirim data user ke view
            'tanggal' => $tanggal,
            'divisi' => $divisi,
            'userId' => $userId,
        ]);
    }
    
    /**
     * Download rekap lembur sebagai PDF.
     */
    public function downloadPdf(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        $query = Lembur::with('user');
        
        if ($divisi) {
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        $lemburRecords = $query->latest('tanggal')->get();

        $dateLabel = $tanggal ? Carbon::parse($tanggal)->isoFormat('D MMMM YYYY') : 'Semua Periode';
        $dateForDays = $tanggal ? Carbon::parse($tanggal) : now(); 

        $pdf = PDF::loadView('admin.lembur.pdf', compact('lemburRecords', 'dateForDays', 'dateLabel'));
        
        $filename = 'rekap_lembur_'. ($tanggal ? $tanggal : 'all') .'.pdf';
        return $pdf->download($filename);
    }
}