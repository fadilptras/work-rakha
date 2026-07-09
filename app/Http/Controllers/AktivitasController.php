<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 

class AktivitasController extends Controller
{
    /**
     * Menampilkan halaman utama Aktivitas.
     * LOGIKA DIPERBARUI: Mengirim daftar tim untuk dipantau.
     */
    public function index()
    {
        $user = Auth::user(); //
        $tanggal = now()->toDateString(); //

        // 1. Ambil Aktivitas Pribadi (Seperti sebelumnya)
        $aktivitasDataPribadi = Aktivitas::where('user_id', $user->id)
                        ->whereDate('created_at', $tanggal) //
                        ->orderBy('created_at', 'asc') //
                        ->get(); //

        $aktivitasHariIni = $aktivitasDataPribadi->map(function ($item) {
            $photo_url = $item->lampiran ? asset('storage/' . $item->lampiran) : null;
            return (object) [
                'created_at' => $item->created_at, 
                'keterangan' => $item->keterangan, 
                'photo_url' => $photo_url,
                'latitude' => $item->latitude, 
                'longitude' => $item->longitude, 
            ];
        });

        $timYangDipantau = collect();
        $targetUserIds = [];

        if ($user->jabatan === 'Direktur') {
            $targetUserIds = User::where('id', '!=', $user->id)
                                 ->where('role', 'user') 
                                 ->pluck('id');
        } 
        elseif ($user->is_kepala_divisi == 1) { 
            $targetUserIds = User::where('divisi', $user->divisi)
                                 ->where('id', '!=', $user->id)
                                 ->where('role', 'user') 
                                 ->where('is_kepala_divisi', 0) 
                                 ->pluck('id');
        }

        // 3. Ambil data User (Foto, Nama, Jabatan)
        if (!empty($targetUserIds) && count($targetUserIds) > 0) {
            $timYangDipantau = User::whereIn('id', $targetUserIds)
                                ->select('id', 'name', 'jabatan', 'profile_picture') // Ambil hanya data yg perlu
                                ->get();
        }

        // 4. Kirim semua data ke view
        return view('users.aktivitas', [
            'title' => 'Catat Aktivitas', //
            'user' => $user,
            'aktivitasHariIni' => $aktivitasHariIni, //
            'timYangDipantau' => $timYangDipantau // Data baru untuk tim (berisi daftar user)
        ]);
    }

    /**
     * Menyimpan data aktivitas baru dari form.
     */
    public function store(Request $request)
    {
        // 1. Validasi data yang masuk
        $request->validate([
            'keterangan' => 'required|string', 
            'lampiran' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
            'latitude' => 'required', 
            'longitude' => 'required', 
        ]);

        $path = null;
        if ($request->hasFile('lampiran')) {
            // PERBAIKAN: Gunakan variabel $path, bukan $pathLampiran
            $path = $request->file('lampiran')->store('aktivitas', 'public');
        }

        // 3. Simpan data ke database
        Aktivitas::create([
            'user_id' => Auth::id(), 
            'title' => Str::limit($request->keterangan, 255), 
            'keterangan' => $request->keterangan, 
            'lampiran' => $path, // Sekarang $path berisi path file yang benar
            'latitude' => $request->latitude, 
            'longitude' => $request->longitude, 
        ]);

        // 4. Kembalikan ke halaman aktivitas dengan pesan sukses
        return redirect()->route('aktivitas.index')->with('success', 'Aktivitas berhasil dicatat!'); 
    }

    /**
     * Menyediakan data JSON untuk rekap aktivitas.
     * DIPERBARUI: Sekarang bisa mengambil data orang lain jika diizinkan (untuk modal).
     */
    public function getAktivitasJson(Request $request)
    {
        $user = Auth::user();
        $userIdToFetch = $user->id; // Default: ambil data diri sendiri
        $targetUserId = $request->query('user_id'); // Ambil ID dari request AJAX modal

        // Jika user meminta data orang lain
        if ($targetUserId && $targetUserId != $user->id) {
            
            $allowedUserIds = [];

            // LOGIK 1: Jika User adalah Direktur
            if ($user->jabatan === 'Direktur') { // <--- BARIS INI TADI HILANG
                $allowedUserIds = User::where('id', '!=', $user->id)
                              ->where('role', 'user')
                              ->pluck('id')
                              ->toArray();
            } 
            elseif ($user->is_kepala_divisi == 1) {
                $allowedUserIds = User::where('divisi', $user->divisi)
                                    ->where('id', '!=', $user->id)
                                    ->where('role', 'user')
                                    ->where('is_kepala_divisi', 0)
                                    ->pluck('id')
                                    ->toArray();
            }

            // Cek apakah ID yang diminta ada di dalam daftar yang diizinkan
            if (in_array($targetUserId, $allowedUserIds)) {
                $userIdToFetch = $targetUserId;
            } else {
                return response()->json(['error' => 'Anda tidak diizinkan melihat data ini.'], 403);
            }
        }

        // 1. Tentukan tanggal
        $tanggal = $request->query('start', now()->toDateString());

        // 2. Ambil data aktivitas
        $aktivitas = Aktivitas::where('user_id', $userIdToFetch)
                            ->whereDate('created_at', $tanggal)
                            ->orderBy('created_at', 'asc')
                            ->get();

        // 3. Format data
        $events = $aktivitas->map(function ($item) {
            $photo_url = $item->lampiran ? asset('storage/' . $item->lampiran) : null;
            return [
                'id' => $item->id,
                'title' => $item->title,
                'start' => $item->created_at->toIso8601String(),
                'extendedProps' => [
                    'keterangan' => $item->keterangan,
                    'photo_url' => $photo_url,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                ]
            ];
        });

        return response()->json($events); 
    }
}