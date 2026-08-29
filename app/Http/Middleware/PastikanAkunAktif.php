<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengeluarkan pengguna yang akunnya dinonaktifkan admin di tengah sesi.
 *
 * Pemeriksaan saat login saja tidak cukup: tanpa ini, akun yang baru
 * dinonaktifkan masih bisa dipakai sampai sesinya kedaluwarsa.
 */
class PastikanAkunAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->aktif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi administrator sistem.');
        }

        return $next($request);
    }
}
