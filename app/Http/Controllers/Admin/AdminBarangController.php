<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminBarangController extends Controller
{
    public function index()
    {
        $barangs = Barang::latest()->get();
        return view('admin.barangs.index', compact('barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'nullable|string|max:50|unique:barangs,kode_barang',
            'nama_barang' => 'required|string|max:255|unique:barangs,nama_barang',
            'satuan' => 'nullable|string|max:50',
        ]);

        Barang::create($request->all());
        
        Cache::forget('barang_list_dropdown');

        return redirect()->route('admin.barangs.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'kode_barang' => 'nullable|string|max:50|unique:barangs,kode_barang,' . $barang->id,
            'nama_barang' => 'required|string|max:255|unique:barangs,nama_barang,' . $barang->id,
            'satuan' => 'nullable|string|max:50',
        ]);

        $barang->update($request->all());

        Cache::forget('barang_list_dropdown');

        return redirect()->route('admin.barangs.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();
        Cache::forget('barang_list_dropdown');
        return redirect()->route('admin.barangs.index')->with('success', 'Barang berhasil dihapus.');
    }
}
