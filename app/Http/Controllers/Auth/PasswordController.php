<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UbahPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pengguna mengganti passwordnya sendiri (bagian 7 & 8).
 */
class PasswordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('auth.ubah-password', [
            'wajibGanti' => (bool) $request->user()->harus_ganti_password,
        ]);
    }

    public function update(UbahPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'password' => $request->validated('password'),
            'harus_ganti_password' => false,
        ])->save();

        // Identitas sesi diputar ulang setelah kredensial berubah.
        $request->session()->regenerate();

        return redirect()
            ->route($user->routeDashboard())
            ->with('status', 'Password berhasil diperbarui.');
    }
}
