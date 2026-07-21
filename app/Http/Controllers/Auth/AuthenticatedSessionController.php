<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan halaman form login.
     */
    public function create(): View
    {
        $agent = new \Jenssegers\Agent\Agent();
        $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';
        return view('auth.login_' . $viewSuffix);
    }

    /**
     * Memproses autentikasi sesi pengguna (Login).
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. Proteksi Brute-Force (Rate Limiting 5x gagal)
        $this->ensureIsNotRateLimited($request);

        // 3. Coba Autentikasi + Remember Me
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }

        // 4. Bersihkan Rate Limiter & Regenerasi Sesi (Anti Session-Fixation)
        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        // 5. (Dihapus karena memberatkan proses login dan tidak digunakan)
        $user = Auth::user();

        // 6. Redirect Sesuai Role
        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.employees.index'));
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Menghancurkan sesi autentikasi (Logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Helper: Memastikan pengguna tidak sedang diblokir karena terlalu banyak gagal login.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
        ]);
    }

    /**
     * Helper: Membuat kunci unik untuk Rate Limiter berdasarkan Email + IP.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());
    }
}