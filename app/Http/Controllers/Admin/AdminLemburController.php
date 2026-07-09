<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminLemburController extends Controller
{
    /**
     * Menampilkan rekap lembur karyawan.
     */
    public function index(Request $request)
    {
        // Ambil input filter
        $tanggal = $request->input('tanggal');
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        // Data untuk dropdown filter
        $divisions = User::select('divisi')->whereNotNull('divisi')->distinct()->pluck('divisi');
        $users = User::orderBy('name')->get(); // Data user untuk dropdown

        // Query Dasar
        $query = Lembur::with('user');
        
        // 1. Filter Divisi
        if ($divisi) {
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        // 2. Filter User
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        // 3. Filter Tanggal (Hanya jika diisi, jika kosong tampilkan semua)
        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        // Ambil data, urutkan dari yang terbaru (tanggal descending)
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

        // Urutkan terbaru -> terlama
        $lemburRecords = $query->latest('tanggal')->get();

        // Siapkan variabel tanggal untuk judul PDF
        $dateLabel = $tanggal ? Carbon::parse($tanggal)->isoFormat('D MMMM YYYY') : 'Semua Periode';
        $dateForDays = $tanggal ? Carbon::parse($tanggal) : now(); // Fallback untuk header PDF jika perlu

        $pdf = PDF::loadView('admin.lembur.pdf', compact('lemburRecords', 'dateForDays', 'dateLabel'));
        
        $filename = 'rekap_lembur_'. ($tanggal ? $tanggal : 'all') .'.pdf';
        return $pdf->download($filename);
    }
}