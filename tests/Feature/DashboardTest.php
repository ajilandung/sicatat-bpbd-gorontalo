<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Instansi;
use App\Models\Kecamatan;
use App\Models\Penyaluran;
use App\Models\User;
use App\Support\RekapPenyaluran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Menguji dashboard dan perhitungan rekapnya (FR-19, FR-20, FR-21).
 *
 * Yang paling penting dijaga di sini: seluruh pengelompokan memakai
 * `tanggal_penyaluran`, sehingga data susulan masuk ke bulan kejadiannya dan
 * bukan ke bulan saat data itu diketik admin.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Waktu dikunci supaya "bulan ini" dan rentang 12 bulan selalu sama
        // setiap kali test dijalankan.
        Carbon::setTestNow('2026-08-31 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function desa(string $nama = 'Tongo', ?Kecamatan $kecamatan = null): Desa
    {
        return Desa::factory()->create([
            'kecamatan_id' => ($kecamatan ?? Kecamatan::factory()->create())->id,
            'nama' => $nama,
        ]);
    }

    /**
     * @param  array<int, Desa>  $desa
     */
    private function kegiatan(string $tanggal, int $volume, array $desa, ?User $admin = null): Penyaluran
    {
        $penyaluran = Penyaluran::factory()->padaTanggal($tanggal)->create([
            'user_id' => ($admin ?? User::factory()->admin()->create())->id,
            'volume_liter' => $volume,
        ]);

        $penyaluran->desas()->sync(collect($desa)->pluck('id')->all());
        $penyaluran->instansis()->sync([Instansi::factory()->create()->id]);

        return $penyaluran;
    }

    public function test_total_air_tersalur_menjumlahkan_seluruh_kegiatan(): void
    {
        $this->kegiatan('2026-08-01', 4000, [$this->desa()]);
        $this->kegiatan('2026-08-12', 16000, [$this->desa('Tolotio')]);

        $this->assertSame(20000, (new RekapPenyaluran)->totalVolume());
        $this->assertSame(2, (new RekapPenyaluran)->jumlahKegiatan());
    }

    public function test_data_terhapus_tidak_ikut_dihitung(): void
    {
        $this->kegiatan('2026-08-01', 4000, [$this->desa()]);
        $this->kegiatan('2026-08-02', 9000, [$this->desa('Tolotio')])->delete();

        $rekap = new RekapPenyaluran;

        $this->assertSame(4000, $rekap->totalVolume());
        $this->assertSame(1, $rekap->jumlahKegiatan());
        $this->assertSame(1, $rekap->jumlahWilayahPenerima());
    }

    public function test_wilayah_penerima_dihitung_tanpa_pengulangan(): void
    {
        $kecamatan = Kecamatan::factory()->create();
        $tongo = $this->desa('Tongo', $kecamatan);
        $tolotio = $this->desa('Tolotio', $kecamatan);

        // Tongo menerima dua kali, dan salah satunya kegiatan gabungan.
        $this->kegiatan('2026-08-01', 4000, [$tongo]);
        $this->kegiatan('2026-08-12', 16000, [$tongo, $tolotio]);

        // Dua desa berbeda, walaupun tercatat pada tiga baris penghubung.
        $this->assertSame(2, (new RekapPenyaluran)->jumlahWilayahPenerima());
    }

    public function test_grafik_mengelompokkan_menurut_tanggal_kegiatan_bukan_tanggal_input(): void
    {
        // Keduanya diinput hari ini, tetapi terjadi pada bulan berbeda —
        // persis kondisi laporan susulan.
        $this->kegiatan('2026-07-20', 5000, [$this->desa()]);
        $this->kegiatan('2026-08-12', 16000, [$this->desa('Tolotio')]);

        $grafik = (new RekapPenyaluran)->volumePerBulan()->keyBy('bulan');

        $this->assertSame(5000, $grafik['2026-07']['total_liter']);
        $this->assertSame(16000, $grafik['2026-08']['total_liter']);
        $this->assertSame(1, $grafik['2026-08']['jumlah_kegiatan']);
    }

    public function test_grafik_selalu_memuat_dua_belas_bulan_termasuk_yang_kosong(): void
    {
        $this->kegiatan('2026-08-12', 16000, [$this->desa()]);

        $grafik = (new RekapPenyaluran)->volumePerBulan();

        $this->assertCount(12, $grafik);

        // Rentangnya berakhir di bulan berjalan dan mundur sebelas bulan.
        $this->assertSame('2025-09', $grafik->first()['bulan']);
        $this->assertSame('2026-08', $grafik->last()['bulan']);

        // Bulan tanpa kegiatan tetap ada, bernilai nol, supaya sumbu grafik
        // tidak melompat dan jeda musim terlihat apa adanya.
        $kosong = $grafik->firstWhere('bulan', '2026-01');
        $this->assertSame(0, $kosong['total_liter']);
        $this->assertSame(0, $kosong['jumlah_kegiatan']);

        // Label sumbu dan keterangan memakai nama bulan bahasa Indonesia.
        $this->assertSame('Ags 26', $grafik->last()['label']);
        $this->assertSame('Agustus 2026', $grafik->last()['judul']);
    }

    public function test_kegiatan_bulan_ini_memakai_tanggal_kegiatan(): void
    {
        $this->kegiatan('2026-08-01', 4000, [$this->desa()]);
        $this->kegiatan('2026-08-31', 4000, [$this->desa('Tolotio')]);
        $this->kegiatan('2026-07-31', 4000, [$this->desa('Pinomontiga')]);

        $this->assertSame(2, (new RekapPenyaluran)->kegiatanBulanIni());
    }

    public function test_kelengkapan_kk_dan_jiwa_ditandai(): void
    {
        $this->kegiatan('2026-08-01', 4000, [$this->desa()]);

        Penyaluran::factory()->tanpaJumlahWarga()->padaTanggal('2026-08-02')->create([
            'user_id' => User::factory()->admin()->create()->id,
        ]);

        $rekap = new RekapPenyaluran;

        $this->assertSame(1, $rekap->kegiatanTanpaJumlahWarga());
        $this->assertGreaterThan(0, $rekap->totalKk());
    }

    public function test_wilayah_tersering_diurutkan_menurut_jumlah_kegiatan(): void
    {
        $kecamatan = Kecamatan::factory()->create();
        $tongo = $this->desa('Tongo', $kecamatan);
        $tolotio = $this->desa('Tolotio', $kecamatan);

        $this->kegiatan('2026-08-01', 4000, [$tongo]);
        $this->kegiatan('2026-08-03', 4000, [$tongo]);
        $this->kegiatan('2026-08-07', 4000, [$tongo, $tolotio]);

        $tersering = (new RekapPenyaluran)->wilayahTersering();

        $this->assertSame('Tongo', $tersering->first()->nama);
        $this->assertSame(3, $tersering->first()->jumlah_kegiatan);
        $this->assertSame('Tolotio', $tersering->last()->nama);
        $this->assertSame(1, $tersering->last()->jumlah_kegiatan);
    }

    public function test_penyaluran_terbaru_diurutkan_menurut_tanggal_kegiatan(): void
    {
        $this->kegiatan('2026-08-01', 4000, [$this->desa('Tongo')]);
        $this->kegiatan('2026-08-24', 9000, [$this->desa('Tolotio')]);

        $terbaru = (new RekapPenyaluran)->terbaru();

        $this->assertSame('2026-08-24', $terbaru->first()->tanggal_penyaluran->format('Y-m-d'));
    }

    public function test_ketiga_role_melihat_statistik_penyaluran_yang_sama(): void
    {
        $this->kegiatan('2026-08-12', 16000, [$this->desa('Mulyonegoro')]);

        foreach ([
            'admin' => '/dashboard/admin',
            'petugas' => '/dashboard/petugas',
            'pimpinan' => '/dashboard/pimpinan',
        ] as $role => $alamat) {
            $this->actingAs(User::factory()->state(['role' => $role])->create())
                ->get($alamat)
                ->assertOk()
                ->assertSee('Total air tersalur')
                ->assertSee('Wilayah penerima')
                ->assertSee('16.000')
                ->assertSee('Mulyonegoro')
                ->assertSee('Volume Air Tersalur per Bulan');
        }
    }

    public function test_panel_khusus_admin_hanya_tampil_untuk_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard/admin')
            ->assertOk()
            ->assertSee('Kesiapan Data Master')
            ->assertSee('Pengguna Sistem');

        $this->actingAs(User::factory()->pimpinan()->create())
            ->get('/dashboard/pimpinan')
            ->assertOk()
            ->assertDontSee('Kesiapan Data Master')
            ->assertDontSee('Pengguna Sistem');
    }

    public function test_dashboard_tetap_terbuka_saat_belum_ada_data(): void
    {
        $this->actingAs(User::factory()->pimpinan()->create())
            ->get('/dashboard/pimpinan')
            ->assertOk()
            ->assertSee('Belum ada data untuk digambarkan')
            ->assertSee('Belum ada wilayah penerima');
    }
}
