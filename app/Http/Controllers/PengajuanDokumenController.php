<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengajuanDokumenController extends Controller
{
    public function index()
    {
        $title = 'Pengajuan Dokumen';
        // Ambil riwayat pengajuan dokumen milik user yang login
        $riwayatDokumen = Auth::user()->pengajuanDokumens()->latest()->get();
        
        return view('users.pengajuan-dokumen', compact('title', 'riwayatDokumen'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_dokumen' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file_pendukung' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('file_pendukung')) {
            $path = $request->file('file_pendukung')->store('dokumen_pendukung', 'public');
        }

        PengajuanDokumen::create([
            'user_id' => Auth::id(),
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'deskripsi' => $validated['deskripsi'],
            'file_pendukung' => $path,
        ]);

        return redirect()->route('pengajuan_dokumen.index')->with('success', 'Pengajuan dokumen berhasil dikirim!');
    }

    public function download(PengajuanDokumen $dokumen)
    {
        // Pastikan hanya pemilik dokumen yang bisa mengunduh
        if (Auth::id() !== $dokumen->user_id) {
            abort(403);
        }

        // Pastikan file hasil ada
        if (!$dokumen->file_hasil || !Storage::disk('public')->exists($dokumen->file_hasil)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($dokumen->file_hasil);
    }
}