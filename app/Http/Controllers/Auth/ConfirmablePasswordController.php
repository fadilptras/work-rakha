<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConfirmablePasswordController extends Controller
{
    /**
     * Tampilkan form konfirmasi password.
     * Middleware 'password.confirm' bawaan Laravel akan otomatis
     * mengarahkan ke sini kalau konfirmasi belum ada / sudah kedaluwarsa.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Proses konfirmasi password. Kalau benar, catat waktunya di session
     * supaya middleware 'password.confirm' tidak minta konfirmasi lagi
     * sampai batas waktu di config/auth.php ('password_timeout') habis.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->input('password'), Auth::user()->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password yang Anda masukkan salah.',
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('admin.employees.index'));
    }
}