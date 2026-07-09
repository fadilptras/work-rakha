<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPengajuanDokumenController extends Controller
{
    public function index(Request $request)
    {
        $query = PengajuanDokumen::with('user');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate = $request->end_date . ' 23:59:59';
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $pengajuanDokumens = $query->latest()->get();
        
        return view('admin.pengajuan-dokumen.index', compact('pengajuanDokumens'));
    }

    public function show(PengajuanDokumen $dokumen)
    {
        // [FIX] Mengirim variabel dengan nama 'pengajuanDana' agar sesuai dengan view
        return view('admin.pengajuan-dokumen.show', ['pengajuanDana' => $dokumen]);
    }

    public function update(Request $request, PengajuanDokumen $dokumen)
    {
        $validated = $request->validate([
            'status' => 'required|in:diproses,selesai,ditolak',
            'catatan_admin' => 'nullable|string',
            'file_hasil' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($request->hasFile('file_hasil')) {
            if ($dokumen->file_hasil) {
                Storage::disk('public')->delete($dokumen->file_hasil);
            }
            $validated['file_hasil'] = $request->file('file_hasil')->store('dokumen_hasil', 'public');
        }
        
        // Menggunakan variabel $dokumen dari route model binding untuk update
        $dokumen->update($validated);

        // Redirect kembali dengan variabel $dokumen yang sama
        return redirect()->route('admin.pengajuan-dokumen.show', $dokumen)->with('success', 'Status pengajuan berhasil diperbarui!');
    }
}