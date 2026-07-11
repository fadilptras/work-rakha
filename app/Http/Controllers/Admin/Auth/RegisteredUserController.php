<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class RegisteredUserController extends Controller
{
    /**
     * Memproses penyimpanan akun pengguna (Admin/Karyawan) baru oleh Admin.
     * Dipicu dari form/modal tambah karyawan di halaman admin lama.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'divisi' => ['required', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'nomor_telepon' => ['nullable', 'string', 'max:20'],
            'jatah_cuti' => ['nullable', 'integer', 'min:0'],
        ]);

        // 2. Tentukan password default sistem
        $defaultPassword = '#rakhA2022!';

        // 3. Simpan ke database
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'divisi' => $validated['divisi'],
            'jabatan' => $validated['jabatan'] ?? 'Staff',
            'nomor_telepon' => $validated['nomor_telepon'] ?? null,
            'password' => Hash::make($defaultPassword),
            'is_kepala_divisi' => false,
            'jatah_cuti' => $validated['jatah_cuti'] ?? 12,
        ]);

        $roleText = $user->role === 'admin' ? 'Admin' : 'Karyawan';

        Cache::forget('karyawan_list_dropdown');
        Cache::forget('admin_list_dropdown');
        Cache::forget('approvers_list_dropdown');

        return back()->with('success', "Akun {$roleText} a.n. {$user->name} berhasil dibuat! Password default: {$defaultPassword}");
    }
}