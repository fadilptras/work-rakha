<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
// --- TAMBAHKAN USE STATEMENT INI ---
use Barryvdh\DomPDF\Facade\Pdf;
// ------------------------------------
// (Hapus use Auth jika tidak digunakan di method lain di controller ini)
use Illuminate\Support\Facades\Auth; 

class UserController extends Controller
{
    /**
     * Menampilkan user berdasarkan role (Admin atau Karyawan).
     */
    public function indexByRole($role)
    {
        if (!in_array($role, ['admin', 'user'])) {
            abort(404);
        }

        if ($role === 'admin') {
            $users = User::where('role', 'admin')->orderBy('name')->get();
            return view('admin.admin', [
                'users' => $users,
                'title' => 'Kelola Admin',
                'defaultRole' => 'admin'
            ]);
        }

        if ($role === 'user') {
            // --- EAGER LOADING DITAMBAHKAN DI SINI ---
            $users = User::where('role', 'user')
                         ->orderBy('divisi')
                         ->orderBy('name')
                         ->with('riwayatPendidikan', 'riwayatPekerjaan') // <-- Eager Load
                         ->get();
            // ----------------------------------------

            $usersByDivision = $users->groupBy(function ($user) {
                return $user->divisi ?: 'Tanpa Divisi';
            });

            return view('admin.karyawan', [
                'usersByDivision' => $usersByDivision,
                'title' => 'Kelola Karyawan',
                'defaultRole' => 'user'
            ]);
        }
    }

    /**
     * Menyimpan user baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'          => ['required', 'string', 'min:8'],
            'role'              => ['required', Rule::in(['admin', 'user'])],
            'jabatan'           => 'nullable|string|max:255',
            'tanggal_bergabung' => 'nullable|date',
            // 'profile_picture'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Dihapus karena foto tidak di-upload di form ini
            'divisi'            => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $validated['password'] = Hash::make($validated['password']);

            // Hapus logika upload foto jika tidak ada field-nya
            // if ($request->hasFile('profile_picture')) { ... }

            if (empty($validated['tanggal_bergabung'])) {
                $validated['tanggal_bergabung'] = Carbon::now();
            }

            User::create($validated);
        });

        $redirectRoute = $request->role === 'admin' ? 'admin.admins.index' : 'admin.employees.index';
        return redirect()->route($redirectRoute)->with('success', 'Akun berhasil ditambahkan.');
    }

    /**
     * Mengupdate data user.
     */
    public function update(Request $request)
    {
        // Pastikan user_id ada dalam request
        $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::findOrFail($request->user_id);

        $validated = $request->validate([
            // --- Akun Dasar ---
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'              => ['required', Rule::in(['admin', 'user'])],
            'password'          => ['nullable', 'string', 'min:8'],
            
            // --- Pekerjaan & Divisi ---
            'jabatan'           => 'nullable|string|max:255',
            'divisi'            => 'nullable|string|max:255',
            'tanggal_bergabung' => 'nullable|date',
            'nip'               => 'nullable|string|max:50',
            'status_karyawan'   => 'nullable|string|max:50',
            'lokasi_kerja'      => 'nullable|string|max:255',
            'tanggal_mulai_kontrak' => 'nullable|date',
            'tanggal_akhir_kontrak' => 'nullable|date',

            // --- Data Pribadi ---
            'nik'               => 'nullable|string|max:20',
            'nomor_telepon'     => 'nullable|string|max:20',
            'tempat_lahir'      => 'nullable|string|max:100',
            'tanggal_lahir'     => 'nullable|date',
            'jenis_kelamin'     => 'nullable|in:Laki-laki,Perempuan',
            'agama'             => 'nullable|string|max:50',
            'status_pernikahan' => 'nullable|string|max:50',
            'golongan_darah'    => 'nullable|string|max:5',
            'alamat_ktp'        => 'nullable|string',
            'alamat_domisili'   => 'nullable|string',

            // --- Administrasi & Bank ---
            'npwp'                  => 'nullable|string|max:50',
            'ptkp'                  => 'nullable|string|max:10',
            'bpjs_kesehatan'        => 'nullable|string|max:50',
            'bpjs_ketenagakerjaan'  => 'nullable|string|max:50',
            'nama_bank'             => 'nullable|string|max:100',
            'nomor_rekening'        => 'nullable|string|max:50',
            'pemilik_rekening'      => 'nullable|string|max:100',

            // --- Kontak Darurat ---
            'kontak_darurat_nama'     => 'nullable|string|max:100',
            'kontak_darurat_nomor'    => 'nullable|string|max:20',
            'kontak_darurat_hubungan' => 'nullable|string|max:50',

            // --- Foto Profil ---
            'profile_picture'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            'user_id'           => 'required|exists:users,id' 
        ]);

        DB::transaction(function () use ($request, $validated, $user) {
            // Logika Password
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Logika Foto Profil
            if ($request->hasFile('profile_picture')) {
                // Hapus foto lama dari storage jika sebelumnya ada foto
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                // Simpan foto baru ke folder 'profile_pictures'
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $validated['profile_picture'] = $path;
            } else {
                // Jangan timpa data foto di database jika admin tidak mengupload gambar baru
                unset($validated['profile_picture']); 
            }

            // Logika Tanggal Bergabung (Biarkan lama jika kosong)
            if (empty($validated['tanggal_bergabung'])) {
                 unset($validated['tanggal_bergabung']);
            }

            unset($validated['user_id']); // Hapus ID dari array data

            // Update semua data sekaligus
            $user->update($validated);
        });

        $redirectRoute = $request->role === 'admin' ? 'admin.admins.index' : 'admin.employees.index';
        return redirect()->route($redirectRoute)->with('success', 'Data profil karyawan berhasil diperbarui secara lengkap.');
    }


    /**
     * Menghapus user.
     */
    public function destroy(User $user)
    {
        if ($user->email === 'admin@rakha.com') {
            return redirect()->back()->with('error', 'Aksi Ditolak! Akun admin utama tidak dapat dihapus.');
        }

        $role = $user->role;

        DB::transaction(function () use ($user) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->delete();
        });

        $redirectRoute = $role === 'admin' ? 'admin.admins.index' : 'admin.employees.index';
        return redirect()->route($redirectRoute)->with('success', 'Akun berhasil dihapus.');
    }

    /**
     * Mengatur user sebagai Kepala Divisi.
     */
    public function setAsDivisionHead(User $user)
    {
        if (empty($user->divisi)) {
            return redirect()->back()->with('error', 'Tidak bisa mengatur kepala divisi untuk karyawan tanpa divisi.');
        }

        DB::transaction(function () use ($user) {
            User::where('divisi', $user->divisi)
                ->where('id', '!=', $user->id)
                ->update(['is_kepala_divisi' => false]);
            $user->update(['is_kepala_divisi' => true]);
        });

        return redirect()->route('admin.employees.index')->with('success', "{$user->name} telah diatur sebagai Kepala Divisi {$user->divisi}.");
    }

    // --- === METHOD BARU UNTUK DOWNLOAD PDF === ---
    /**
     * Menghasilkan dan mengunduh PDF profil karyawan spesifik.
     */
    public function downloadProfilePdf(User $user)
    {
        // 1. Muat relasi yang dibutuhkan (sudah dimuat di indexByRole, tapi kita muat lagi untuk keamanan)
        $user->load('riwayatPendidikan', 'riwayatPekerjaan');

        // 2. Siapkan data untuk view
        $data = [
            'user' => $user,
            // Ambil nama user yang mencetak (admin yang sedang login)
            'pencetak' => Auth::user()->name,
            'tanggal_cetak' => now()->translatedFormat('d F Y H:i')
        ];

        // 3. Load view PDF ('pdf.profile' yang sama dengan ProfileController)
        $pdf = Pdf::loadView('pdf.profile', $data)->setPaper('a4', 'portrait');

        // 4. Buat nama file
        $namaFile = 'Profil Karyawan - ' . $user->name . ' - ' . now()->format('Ymd') . '.pdf';

        // 5. Kembalikan sebagai download
        return $pdf->download($namaFile);
    }
    // --- === AKHIR METHOD BARU === ---
}