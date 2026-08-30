<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'videoLatar' => $this->berkasPublik('video/latar-login.mp4'),
            'posterLatar' => $this->berkasPublik('video/latar-login.jpg'),
        ]);
    }

    /**
     * URL sebuah berkas di direktori publik, atau null bila berkasnya tidak ada.
     *
     * Video latar panel identitas bersifat pelengkap. Bila berkasnya belum
     * disalin ke server, halaman login harus tetap tampil utuh dengan panel
     * navy polos seperti sedia kala, bukan menyisakan permintaan yang gagal.
     * Cap waktu berkas ditempelkan supaya penggantian video langsung terlihat
     * tanpa pengguna perlu mengosongkan cache peramban.
     */
    private function berkasPublik(string $relatif): ?string
    {
        $absolut = public_path($relatif);

        return file_exists($absolut)
            ? asset($relatif).'?v='.filemtime($absolut)
            : null;
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Cegah session fixation.
        $request->session()->regenerate();

        $user = $request->user();

        // Dicatat tanpa menyentuh kolom updated_at supaya jejak perubahan data
        // pengguna tidak tertimpa oleh aktivitas login.
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route($user->routeDashboard()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah keluar dari sistem.');
    }
}
