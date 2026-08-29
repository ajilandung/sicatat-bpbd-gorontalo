<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengunci pengguna di halaman Ubah Password selama masih memakai
 * password sementara dari admin.
 *
 * Route yang tetap boleh diakses hanya halaman ubah password itu sendiri
 * dan logout, supaya pengguna tidak terjebak tanpa jalan keluar.
 */
class PastikanPasswordSudahDiganti
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->harus_ganti_password && ! $request->routeIs('password.*', 'logout')) {
            return redirect()
                ->route('password.edit')
                ->with('error', 'Anda masih memakai password sementara. Silakan buat password baru terlebih dahulu.');
        }

        return $next($request);
    }
}
