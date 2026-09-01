<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Instansi;
use App\Models\Kecamatan;
use App\Models\Penyaluran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Menguji halaman laporan beserta kedua exportnya (FR-22, FR-23, FR-24).
 *
 * Dua hal yang paling dijaga di sini: angka laporan selalu dikelompokkan
 * menurut `tanggal_penyaluran` sehingga data susulan masuk ke tanggal
 * kejadiannya, dan laporan hanya terbuka untuk role yang memang berhak
 * melihatnya (§9).
 */
class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
    private function kegiatan(string $tanggal, int $volume, array $desa, ?Instansi $instansi = null): Penyaluran
    {
        $penyaluran = Penyaluran::factory()->padaTanggal($tanggal)->create([
            'user_id' => User::factory()->admin()->create()->id,
            'volume_liter' => $volume,
            'jumlah_kk' => 100,
            'jumlah_jiwa' => 400,
        ]);

        $penyaluran->desas()->sync(collect($desa)->pluck('id')->all());
        $penyaluran->instansis()->sync([($instansi ?? Instansi::factory()->create())->id]);

        return $penyaluran;
    }

    public function test_admin_dan_pimpinan_dapat_membuka_laporan_petugas_tidak(): void
    {
        foreach (['admin', 'pimpinan'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('laporan.index'))
                ->assertOk();
        }

        $petugas = User::factory()->create(['role' => User::ROLE_PETUGAS]);

        $this->actingAs($petugas)->get(route('laporan.index'))->assertForbidden();
        $this->actingAs($petugas)->get(route('laporan.cetak'))->assertForbidden();
        $this->actingAs($petugas)->get(route('laporan.excel'))->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get(route('laporan.index'))->assertRedirect(route('login'));
    }

    public function test_laporan_menjumlahkan_air_tersalur_dan_merinci_per_tanggal_kejadian(): void
    {
        $kecamatan = Kecamatan::factory()->create(['nama' => 'Bone Pantai']);

        // Dua kegiatan pada hari yang sama, salah satunya baru diinput belakangan.
        $this->kegiatan('2026-08-03', 60000, [$this->desa('Batu Hijau', $kecamatan)]);
        $this->kegiatan('2026-08-03', 12000, [$this->desa('Tongo', $kecamatan)]);
        $this->kegiatan('2026-08-10', 25000, [$this->desa('Tolotio', $kecamatan)]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('laporan.index'))
            ->assertOk()
            // Total seluruh periode: 60.000 + 12.000 + 25.000.
            ->assertSee('97.000')
            ->assertSee('Senin, 3 Agustus 2026')
            ->assertSee('Senin, 10 Agustus 2026')
            // Kedua kegiatan 3 Agustus dijumlahkan pada tanggal kejadiannya.
            ->assertSee('72.000 liter')
            ->assertSee('Batu Hijau')
            ->assertSee('Tolotio');
    }

    public function test_laporan_mengikuti_filter_periode(): void
    {
        $this->kegiatan('2026-07-20', 5000, [$this->desa('Saripi')]);
        $this->kegiatan('2026-08-12', 16000, [$this->desa('Tutuwota')]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('laporan.index', ['tanggal_mulai' => '2026-08-01', 'tanggal_akhir' => '2026-08-31']))
            ->assertOk()
            ->assertSee('Tutuwota')
            ->assertDontSee('Saripi');
    }

    public function test_data_terhapus_tidak_ikut_dilaporkan(): void
    {
        $terhapus = $this->kegiatan('2026-08-05', 9000, [$this->desa('Hulawa')]);
        $terhapus->delete();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertDontSee('Hulawa');
    }

    public function test_halaman_cetak_memuat_kop_info_kejadian_tabel_dan_tanda_tangan(): void
    {
        $this->kegiatan('2026-08-24', 32000, [$this->desa('Parungi')]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('laporan.cetak', [
                'jenis_bencana' => 'KEKERINGAN',
                'tanggal_kejadian' => '2026-07-27',
                'waktu_kejadian' => '11.30 WITA',
                'lokasi_kejadian' => 'Kab. Bone Bolango',
                'update_ke' => 'XV',
                'penandatangan_nama' => 'Ir. Rusli Wahjudewey Nusi, M.T., M.M.',
                'penandatangan_nip' => '196612041994031006',
            ]))
            ->assertOk()
            ->assertSee(config('laporan.kop.instansi'))
            ->assertSee('KOORDALOPS PB')
            // Lambang provinsi di kiri kop dan logo BPBD di kanannya.
            ->assertSee(asset(config('laporan.kop.logo_kiri')))
            ->assertSee(asset(config('laporan.kop.logo_kanan')))
            ->assertSee('KEKERINGAN')
            ->assertSee('Senin, 27 Juli 2026')
            ->assertSee('Update XV')
            ->assertSee('Total air tersalur')
            ->assertSee('32.000 Liter')
            ->assertSee('Parungi')
            ->assertSee('Ir. Rusli Wahjudewey Nusi, M.T., M.M.')
            ->assertSee('NIP 196612041994031006');
    }

    public function test_isian_identitas_terakhir_diingat_untuk_laporan_berikutnya(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('laporan.cetak', ['lokasi_kejadian' => 'Kab. Gorontalo Utara', 'update_ke' => 'XVI']))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Kab. Gorontalo Utara')
            ->assertSee('XVI');
    }

    public function test_tanggal_kejadian_pada_kop_tidak_boleh_melewati_hari_ini(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('laporan.cetak', ['tanggal_kejadian' => '2026-09-30']))
            ->assertSessionHasErrors('tanggal_kejadian');
    }

    public function test_export_excel_berisi_satu_baris_per_kegiatan(): void
    {
        $kecamatan = Kecamatan::factory()->create(['nama' => 'Bone Pantai']);
        $instansi = Instansi::factory()->create(['nama' => 'BPBD Provinsi Gorontalo']);

        // Satu kegiatan, dua desa, satu angka gabungan — persis seperti laporan
        // lapangan. Volumenya tidak boleh dipecah menjadi dua baris.
        $this->kegiatan('2026-08-12', 16000, [
            $this->desa('Tongo', $kecamatan),
            $this->desa('Batu Hijau', $kecamatan),
        ], $instansi);

        $isi = $this->actingAs(User::factory()->admin()->create())
            ->get(route('laporan.excel'))
            ->assertOk()
            ->assertDownload()
            ->streamedContent();

        $baris = array_values(array_filter(explode("\n", trim($isi))));

        // Baris 1 penanda pemisah kolom, baris 2 judul kolom, baris 3 data.
        $this->assertCount(3, $baris);
        $this->assertStringContainsString('Volume (liter)', $baris[1]);
        $this->assertStringContainsString('12/08/2026', $baris[2]);
        $this->assertStringContainsString('Desa Batu Hijau; Desa Tongo', $baris[2]);
        $this->assertStringContainsString('BPBD Provinsi Gorontalo', $baris[2]);
        $this->assertStringContainsString('16000', $baris[2]);
    }

    public function test_export_excel_mengikuti_filter_dan_mengabaikan_data_terhapus(): void
    {
        $this->kegiatan('2026-07-20', 5000, [$this->desa('Saripi')]);
        $this->kegiatan('2026-08-12', 16000, [$this->desa('Tutuwota')]);
        $this->kegiatan('2026-08-15', 4000, [$this->desa('Buata')])->delete();

        $isi = $this->actingAs(User::factory()->admin()->create())
            ->get(route('laporan.excel', ['tanggal_mulai' => '2026-08-01']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Tutuwota', $isi);
        $this->assertStringNotContainsString('Saripi', $isi);
        $this->assertStringNotContainsString('Buata', $isi);
    }
}
