<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // <-- DITAMBAHKAN

class InteractionController extends Controller
{
    /**
     * Menyimpan data interaksi baru dari modal.
     */
    public function store(Request $request)
    {
        // 1. Validasi data
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'interaction_date' => 'required|date',
            'interaction_type' => 'required|string|max:255',
            'our_role' => 'nullable|string|max:255',
            'client_role' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }

        // 2. Tambahkan user_id sebelum disimpan
        $data = $validator->validated();
        $data['user_id'] = Auth::id(); // <-- DITAMBAHKAN INI

        Interaction::create($data); // <-- DIUBAH

        // 3. Kembali ke halaman detail klien
        return redirect()->route('crm.show', $request->client_id)
                         ->with('success', 'Interaksi baru berhasil ditambahkan!');
    }

    /**
     * DITAMBAHKAN: Menghapus Interaksi
     * Hanya bisa dilakukan oleh user yang membuat interaksi tersebut.
     */
    public function destroy(Interaction $interaction)
    {
        // Pengecekan Kepemilikan
        if ($interaction->user_id !== Auth::id()) {
            return redirect()->back()
                             ->with('error', 'Akses ditolak: Anda bukan yang menginput interaksi ini.');
        }

        // Simpan client_id untuk redirect sebelum dihapus
        $clientId = $interaction->client_id;

        // Hapus interaksi
        $interaction->delete();

        return redirect()->route('crm.show', $clientId)
                         ->with('success', 'Interaksi berhasil dihapus.');
    }
}