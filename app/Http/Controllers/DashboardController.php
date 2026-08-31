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

    public function admin(Request $request): View
    {
        return view('dashboard.admin', $this->statistikPenyaluran($request) + [
            'jumlahKabupaten' => Kabupaten::count(),
            'jumlahKecamatan' => Kecamatan::count(),
            'jumlahDesa' => Desa::count(),
            'jumlahInstansi' => Instansi::aktif()->count(),
            'jumlahPengguna' => User::count(),
            'jumlahPenggunaNonaktif' => User::where('aktif', false)->count(),
        ]);
    }

    public function petugas(Request $request): View
    {
        return view('dashboard.petugas', $this->statistikPenyaluran($request));
    }

    public function pimpinan(Request $request): View
    {
        return view('dashboard.pimpinan', $this->statistikPenyaluran($request));
    }

    /**
     * Pilihan periode dashboard.
     *
     * Sengaja berupa preset, bukan dua kotak tanggal seperti halaman riwayat:
     * dashboard dibaca sekilas, sementara penelusuran rentang tertentu memang
     * tempatnya di halaman riwayat yang filternya jauh lebih lengkap.
     */
    private const PERIODE = [
        'semua' => 'Seluruh waktu',
        'bulan-ini' => 'Bulan ini',
        'tiga-bulan' => 'Tiga bulan terakhir',
        'tahun-ini' => 'Tahun ini',
    ];

    /**
     * Ringkasan penyaluran, tersaring menurut periode yang dipilih.
     *
     * @return array<string, mixed>
     */
    private function statistikPenyaluran(Request $request): array
    {
        // Nilai di luar daftar diperlakukan sebagai "seluruh waktu", sehingga
        // parameter yang diketik sembarangan pada URL tidak menghasilkan
        // dashboard kosong tanpa keterangan.
        $diminta = $request->string('periode')->toString();
        $periode = array_key_exists($diminta, self::PERIODE) ? $diminta : 'semua';

        $rekap = new RekapPenyaluran($this->filterPeriode($periode));

        return [
            'periodeAktif' => $periode,
            'opsiPeriode' => self::PERIODE,
            'labelPeriode' => $periode === 'semua'
                ? $rekap->labelPeriode()
                : self::PERIODE[$periode],
            'jumlahKecamatanPenerima' => $rekap->jumlahKecamatanPenerima(),
            'rekapKabupaten' => $rekap->rekapKabupaten(),
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

    /**
     * Menerjemahkan preset periode menjadi filter yang dimengerti
     * `Penyaluran::scopeSaring()`, memakai kunci yang sama persis dengan
     * halaman riwayat supaya rumusnya tidak bercabang.
     *
     * Batasnya `tanggal_penyaluran` — tanggal kegiatan di lapangan — bukan
     * waktu data dimasukkan, sehingga laporan susulan tetap masuk ke periode
     * kejadiannya (§9.3).
     *
     * @return array<string, string>
     */
    private function filterPeriode(string $periode): array
    {
        return match ($periode) {
            'bulan-ini' => [
                'tanggal_mulai' => now()->startOfMonth()->toDateString(),
                'tanggal_akhir' => now()->endOfMonth()->toDateString(),
            ],
            'tiga-bulan' => [
                'tanggal_mulai' => now()->startOfMonth()->subMonths(2)->toDateString(),
                'tanggal_akhir' => now()->endOfMonth()->toDateString(),
            ],
            'tahun-ini' => [
                'tanggal_mulai' => now()->startOfYear()->toDateString(),
                'tanggal_akhir' => now()->endOfYear()->toDateString(),
            ],
            default => [],
        };
    }
}
