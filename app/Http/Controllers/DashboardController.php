<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Instansi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\User;
use App\Support\RekapPenyaluran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard ringkasan (PRD 8.2, FR-19 sampai FR-21).
 *
 * Ketiga role melihat statistik penyaluran yang sama — peta route memberi
 * FR-19 sampai FR-21 kepada semuanya. Yang membedakan hanya panel tambahan
 * milik admin: kesiapan data master dan pengguna sistem.
 *
 * Seluruh angkanya dihitung oleh `RekapPenyaluran` supaya dashboard dan
 * laporan tidak pernah memakai dua versi rumus yang berbeda.
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
        return view('dashboard.admin', $this->statistikPenyaluran() + [
            'jumlahKabupaten' => Kabupaten::count(),
            'jumlahKecamatan' => Kecamatan::count(),
            'jumlahDesa' => Desa::count(),
            'jumlahInstansi' => Instansi::aktif()->count(),
            'jumlahPengguna' => User::count(),
            'jumlahPenggunaNonaktif' => User::where('aktif', false)->count(),
        ]);
    }

    public function petugas(): View
    {
        return view('dashboard.petugas', $this->statistikPenyaluran());
    }

    public function pimpinan(): View
    {
        return view('dashboard.pimpinan', $this->statistikPenyaluran());
    }

    /**
     * Ringkasan penyaluran untuk seluruh waktu, tanpa filter.
     *
     * @return array<string, mixed>
     */
    private function statistikPenyaluran(): array
    {
        $rekap = new RekapPenyaluran;

        return [
            'jumlahKegiatan' => $rekap->jumlahKegiatan(),
            'totalVolume' => $rekap->totalVolume(),
            'jumlahWilayahPenerima' => $rekap->jumlahWilayahPenerima(),
            'kegiatanBulanIni' => $rekap->kegiatanBulanIni(),
            'totalKk' => $rekap->totalKk(),
            'totalJiwa' => $rekap->totalJiwa(),
            'kegiatanTanpaJumlahWarga' => $rekap->kegiatanTanpaJumlahWarga(),
            'grafikBulanan' => $rekap->volumePerBulan(),
            'wilayahTersering' => $rekap->wilayahTersering(),
            'penyaluranTerbaru' => $rekap->terbaru(),
        ];
    }
}
