<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // <-- Import Auth

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login DAN rolenya adalah 'admin'
        if (Auth::check() && Auth::user()->role == 'admin') {
            return $next($request); // Lanjutkan ke halaman tujuan
        }

        // Jika bukan admin, tendang ke dashboard biasa dengan pesan error
        return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses admin.');
    }
}