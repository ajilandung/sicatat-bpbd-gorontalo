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
            'slides' => $this->slidesLatar(),
        ]);
    }

    /**
     * URL foto dokumentasi untuk slideshow panel identitas.
     *
     * Isinya dibaca langsung dari direktori, bukan didaftar satu per satu di
     * kode: menambah atau mengganti foto cukup dengan menaruh berkas baru di
     * `public/images/login/`, tanpa menyentuh controller. Urutannya mengikuti
     * nama berkas supaya tampilannya tetap sama setiap kali halaman dibuka.
     *
     * Slideshow ini pelengkap. Bila direktorinya kosong — misalnya berkas foto
     * belum ikut tersalin ke server — halaman login tetap tampil utuh dengan
     * panel navy berornamen seperti sedia kala, bukan menyisakan permintaan
     * gambar yang gagal. Cap waktu berkas ditempelkan agar penggantian foto
     * langsung terlihat tanpa pengguna perlu mengosongkan cache peramban.
     *
     * @return list<string>
     */
    private function slidesLatar(): array
    {
        $berkas = glob(public_path('images/login/*.jpg')) ?: [];

        sort($berkas);

        return array_map(
            fn (string $absolut) => asset('images/login/'.basename($absolut)).'?v='.filemtime($absolut),
            $berkas,
        );
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
