<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Instansi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Penyaluran;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard ringkasan (PRD 8.2).
     *
     * Fase 1 baru menampilkan kesiapan data master. Kartu statistik
     * penyaluran dan grafik bulanan dikerjakan pada Fase 4.
     */
    public function index(): View
    {
        return view('dashboard.index', [
            'jumlahKabupaten' => Kabupaten::count(),
            'jumlahKecamatan' => Kecamatan::count(),
            'jumlahDesa' => Desa::count(),
            'jumlahInstansi' => Instansi::aktif()->count(),
            'jumlahPenyaluran' => Penyaluran::count(),
            'totalLiter' => (int) Penyaluran::sum('volume_liter'),
        ]);
    }
}
