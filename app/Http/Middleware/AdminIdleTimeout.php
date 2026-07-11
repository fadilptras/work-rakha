<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminIdleTimeout
{
    /**
     * Cek apakah admin sudah tidak aktif melebihi batas waktu (config/security.php).
     * Kalau iya, logout paksa. Kalau belum, update timestamp aktivitas terakhir.
     *
     * Middleware ini hanya dipasang di grup route admin, jadi Auth::user() di sini
     * pasti sudah lolos middleware 'auth' dan 'admin' sebelumnya.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timeout = (int) config('security.admin_idle_timeout', 600);

        $lastActivity = $request->session()->get('admin_last_activity');

        if ($lastActivity !== null && (time() - $lastActivity) > $timeout) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Sesi admin Anda berakhir karena tidak ada aktivitas. Silakan login kembali.');
        }

        $request->session()->put('admin_last_activity', time());

        return $next($request);
    }
}