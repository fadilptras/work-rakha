<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

class PasswordResetController extends Controller
{
    /**
     * Mereset password pengguna kembali ke password default sistem (Rakha2026!).
     */
    public function resetToDefault(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat mereset password akun Anda sendiri dari menu ini.');
        }

        $defaultPassword = '#rakhA2022!';

        $user->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return back()->with('success', "Password a.n. {$user->name} berhasil direset ke default: {$defaultPassword}");
    }

    /**
     * Mereset password pengguna sesuai input custom dari Admin.
     */
    public function resetCustom(Request $request, User $user): RedirectResponse
    {
        // [FIX P1013] Menggunakan Auth::id()
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Gunakan menu Profil untuk mengubah password akun Anda sendiri.');
        }

        $request->validate([
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', "Password untuk {$user->name} berhasil diperbarui.");
    }
}