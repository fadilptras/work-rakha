<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class AdminUserController extends Controller
{
    /**
     * Menampilkan daftar user berdasarkan role ('admin' or 'user').
     */
    public function indexByRole(string $role): View
    {
        if (!in_array($role, ['admin', 'user'])) {
            abort(404);
        }

        if ($role === 'admin') {
            $users = User::query()->where('role', 'admin')->orderBy('name')->get();
            
            return view('admin.admin', [
                'users' => $users,
                'title' => 'Kelola Admin',
                'defaultRole' => 'admin'
            ]);
        }

        $usersByDivision = User::query()
                        ->where('role', 'user')
                        ->with(['riwayatPendidikan', 'riwayatPekerjaan']) 
                        ->orderBy('divisi')
                        ->orderByDesc('is_kepala_divisi')
                        ->orderBy('name')
                        ->get()
                        ->groupBy('divisi');

        return view('admin.karyawan', [
            'usersByDivision' => $usersByDivision, 
            'title' => 'Kelola Karyawan',
            'defaultRole' => 'user'
        ]);
    }

    /**
     * Menampilkan halaman edit terpisah untuk karyawan tertentu.
     */
    public function edit(User $user): View
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        return view('admin.edit_karyawan', [
            'user' => $user,
            'title' => 'Edit Data Karyawan'
        ]);
    }

    /**
     * Memperbarui data biodata, divisi, jabatan, jatah cuti, beserta foto hasil crop.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'divisi' => ['required', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'tanggal_bergabung' => ['nullable', 'date'],
            'jatah_cuti' => ['nullable', 'integer', 'min:0'],
            'sisa_cuti' => ['nullable', 'integer', 'min:0'],
            'status_karyawan' => ['nullable', 'string', 'max:100'],
            
            // Validasi Data Tambahan Lengkap
            'nip' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:20'],
            'nomor_telepon' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'in:Laki-laki,Perempuan'],
            'agama' => ['nullable', 'string', 'max:50'],
            'golongan_darah' => ['nullable', 'string', 'max:10'],
            'status_pernikahan' => ['nullable', 'string', 'max:50'],
            'alamat_ktp' => ['nullable', 'string'],
            'alamat_domisili' => ['nullable', 'string'],
            
            // Finansial & Bank
            'nama_bank' => ['nullable', 'string', 'max:50'],
            'nomor_rekening' => ['nullable', 'string', 'max:50'],
            'pemilik_rekening' => ['nullable', 'string', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'bpjs_kesehatan' => ['nullable', 'string', 'max:50'],
            'bpjs_ketenagakerjaan' => ['nullable', 'string', 'max:50'],
            
            // Kontak Darurat
            'kontak_darurat_nama' => ['nullable', 'string', 'max:100'],
            'kontak_darurat_nomor' => ['nullable', 'string', 'max:20'],
            'kontak_darurat_hubungan' => ['nullable', 'string', 'max:50'],

            // Penampung file base64 dari Cropper.js
            'cropped_image' => ['nullable', 'string'],
        ]);

        // Memproses Upload Gambar jika ada string base64 baru dari cropper
        if ($request->filled('cropped_image')) {
            try {
                $image_parts = explode(";base64,", $request->cropped_image);
                $image_base64 = base64_decode($image_parts[1]);
                
                $fileName = 'profile_pictures/' . uniqid() . '.png';
                
                // Hapus foto lama di storage jika ada sebelumnya
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                Storage::disk('public')->put($fileName, $image_base64);
                $validated['profile_picture'] = $fileName;
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal memproses crop foto profil. Silakan coba lagi.');
            }
        }

        // Singkirkan input cropped_image agar tidak error mass-assignment ke DB
        unset($validated['cropped_image']);

        $user->update($validated);

        Cache::forget('karyawan_list_dropdown');
        Cache::forget('admin_list_dropdown');
        Cache::forget('approvers_list_dropdown');

        return redirect()->route('admin.employees.index')->with('success', "Seluruh data profil a.n. {$user->name} berhasil diperbarui.");
    }

    /**
     * Memperbarui data admin (termasuk password jika diisi).
     */
    public function updateAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($request->user_id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'profile_picture' => ['nullable', 'image', 'max:2048']
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user->update($validated);

        Cache::forget('karyawan_list_dropdown');
        Cache::forget('admin_list_dropdown');
        Cache::forget('approvers_list_dropdown');

        return back()->with('success', "Data admin a.n. {$user->name} berhasil diperbarui.");
    }

    /**
     * Menghapus akun pengguna dari sistem secara permanen.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Hapus file foto dari storage sebelum delete records
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $nama = $user->name;
        $user->delete();

        Cache::forget('karyawan_list_dropdown');
        Cache::forget('admin_list_dropdown');
        Cache::forget('approvers_list_dropdown');

        return back()->with('success', "Akun a.n. {$nama} berhasil dihapus permanen.");
    }

    /**
     * Mengatur seorang karyawan menjadi Kepala Divisi (menggantikan kepala divisi lama di divisi yang sama).
     */
    public function setAsDivisionHead(User $user): RedirectResponse
    {
        DB::transaction(function () use ($user) {
            User::query()
                ->where('divisi', $user->divisi)
                ->where('id', '!=', $user->id)
                ->update(['is_kepala_divisi' => false]);

            $user->update(['is_kepala_divisi' => true]);
        });

        return redirect()->route('admin.employees.index')->with('success', "{$user->name} telah diatur sebagai Kepala Divisi {$user->divisi}.");
    }

    /**
     * Menghasilkan dan mengunduh PDF profil karyawan spesifik.
     */
    public function downloadProfilePdf(User $user): Response
    {
        $user->load(['riwayatPendidikan', 'riwayatPekerjaan']);

        $admin = Auth::user();

        $data = [
            'user' => $user,
            'pencetak' => $admin ? $admin->name : 'Admin',
            'tanggal_cetak' => now()->translatedFormat('d F Y H:i')
        ];

        $pdf = Pdf::loadView('pdf.profile', $data)->setPaper('a4', 'portrait');

        return $pdf->download('Profil_' . str_replace(' ', '_', $user->name) . '.pdf');
    }

    /**
     * Menyediakan data JSON profil karyawan untuk modal detail AJAX.
     */
    public function ajaxDetail(User $user)
    {
        $user->load(['riwayatPendidikan', 'riwayatPekerjaan']);
        return response()->json($user);
    }
}