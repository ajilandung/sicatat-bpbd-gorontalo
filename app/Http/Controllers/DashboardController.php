<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Instansi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Penyaluran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard ringkasan (PRD 8.2).
 *
 * Fase 1 hanya menyiapkan halaman tujuan per role sebagai penanda hak akses.
 * Kartu statistik penyaluran dan grafik bulanan dikerjakan pada Fase 4.
 */
class DashboardController extends Controller
{
    /**
     * Pintu masuk umum: mengarahkan ke dashboard sesuai role.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->routeDashboard());
    }

    public function admin(): View
    {
        return view('dashboard.admin', [
            'jumlahKabupaten' => Kabupaten::count(),
            'jumlahKecamatan' => Kecamatan::count(),
            'jumlahDesa' => Desa::count(),
            'jumlahInstansi' => Instansi::aktif()->count(),
            'jumlahPenyaluran' => Penyaluran::count(),
            'totalLiter' => (int) Penyaluran::sum('volume_liter'),
            'jumlahPengguna' => User::count(),
            'jumlahPenggunaNonaktif' => User::where('aktif', false)->count(),
        ]);
    }

    public function petugas(): View
    {
        return view('dashboard.petugas');
    }

    public function pimpinan(): View
    {
        return view('dashboard.pimpinan');
    }
}
