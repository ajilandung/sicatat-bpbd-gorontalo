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
        return view('auth.login');
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
