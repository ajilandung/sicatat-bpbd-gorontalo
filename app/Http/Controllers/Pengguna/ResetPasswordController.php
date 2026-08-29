<?php

namespace App\Http\Controllers\Pengguna;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengguna\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin menetapkan password sementara baru bagi pengguna yang lupa password.
 *
 * MVP tidak memakai reset lewat email — sesuai keputusan pada dokumen
 * arsitektur, pemulihan akun ditangani administrator secara langsung.
 */
class ResetPasswordController extends Controller
{
    public function edit(User $pengguna): View
    {
        $this->authorize('resetPassword', $pengguna);

        return view('pengguna.reset-password', ['pengguna' => $pengguna]);
    }

    public function update(ResetPasswordRequest $request, User $pengguna): RedirectResponse
    {
        $pengguna->forceFill([
            'password' => $request->validated('password'),
            'harus_ganti_password' => true,
            'remember_token' => null,
        ])->save();

        return redirect()
            ->route('pengguna.index')
            ->with('status', "Password {$pengguna->name} berhasil direset. Pengguna wajib menggantinya saat login berikutnya.");
    }
}
