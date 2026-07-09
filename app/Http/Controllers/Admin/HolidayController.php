<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Menampilkan daftar hari libur.
     */
    public function index()
    {
        // FIX: Urutan ASC agar dari Januari dulu
        $holidays = Holiday::orderBy('tanggal', 'asc')->paginate(10);
        return view('admin.holidays.index', compact('holidays'));
    }

    /**
     * Menyimpan data hari libur baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:holidays,tanggal',
            'keterangan' => 'required|string|max:255',
            'is_cuti_bersama' => 'nullable|boolean',
        ]);

        Holiday::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'is_cuti_bersama' => $request->has('is_cuti_bersama') ? 1 : 0,
        ]);

        return redirect()->back()->with('success', 'Hari libur berhasil ditambahkan');
    }

    /**
     * Mengupdate data hari libur (NEW).
     */
    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            // Unique ignore ID saat ini agar tidak error kalau tanggal tidak diubah
            'tanggal' => 'required|date|unique:holidays,tanggal,' . $holiday->id,
            'keterangan' => 'required|string|max:255',
            'is_cuti_bersama' => 'nullable|boolean',
        ]);

        $holiday->update([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'is_cuti_bersama' => $request->has('is_cuti_bersama') ? 1 : 0,
        ]);

        return redirect()->back()->with('success', 'Hari libur berhasil diperbarui');
    }

    /**
     * Menghapus data hari libur.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->back()->with('success', 'Hari libur berhasil dihapus');
    }
}