<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\FotoPenyaluran;
use App\Models\Instansi;
use App\Models\Kecamatan;
use App\Models\Penyaluran;
use App\Models\RiwayatPenyaluran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji dokumentasi foto kegiatan penyaluran.
 *
 * Aturan pokoknya: foto selalu menempel pada satu kegiatan penyaluran, tidak
 * pernah berdiri sendiri, dan tidak pernah menyimpan tanggalnya sendiri —
 * tanggal dokumentasi dibaca dari kegiatan induknya. Dengan begitu foto yang
 * diunggah beberapa hari setelah kegiatan tetap terhitung sebagai dokumentasi
 * tanggal kejadiannya, bukan tanggal unggahnya.
 */
class DokumentasiPenyaluranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(FotoPenyaluran::DISK);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function penyaluran(string $tanggal = '2026-08-12', string $desa = 'Tongo'): Penyaluran
    {
        $penyaluran = Penyaluran::factory()->padaTanggal($tanggal)->create();

        $penyaluran->desas()->attach(Desa::factory()->create([
            'kecamatan_id' => Kecamatan::factory()->create()->id,
            'nama' => $desa,
        ])->id);

        $penyaluran->instansis()->attach(Instansi::factory()->create()->id);

        return $penyaluran;
    }

    public function test_admin_dapat_menambahkan_beberapa_foto_sekaligus(): void
    {
        $penyaluran = $this->penyaluran();

        $this->actingAs($this->admin())
            ->post("/penyaluran/{$penyaluran->id}/foto", [
                'foto' => [
                    UploadedFile::fake()->image('lapangan-1.jpg', 2400, 1800),
                    UploadedFile::fake()->image('lapangan-2.jpg', 1200, 900),
                ],
            ])
            ->assertRedirect("/penyaluran/{$penyaluran->id}")
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $penyaluran->fotos()->count());

        foreach ($penyaluran->fotos as $foto) {
            Storage::disk(FotoPenyaluran::DISK)->assertExists($foto->path);
            $this->assertStringStartsWith("dokumentasi/{$penyaluran->id}/", $foto->path);
        }
    }

    /**
     * Foto tidak punya tanggal sendiri: tanggalnya adalah tanggal kegiatan,
     * bukan tanggal unggah. Ini yang membuat foto susulan tetap masuk ke
     * tanggal kejadiannya.
     */
    public function test_tanggal_foto_mengikuti_tanggal_kegiatan(): void
    {
        $penyaluran = $this->penyaluran('2026-08-12');

        $this->travelTo('2026-08-20 09:00:00');

        $this->actingAs($this->admin())->post("/penyaluran/{$penyaluran->id}/foto", [
            'foto' => [UploadedFile::fake()->image('lapangan.jpg')],
        ])->assertSessionHasNoErrors();

        $foto = FotoPenyaluran::sole();

        $this->assertSame('2026-08-12', $foto->tanggal()?->toDateString());
        $this->assertSame('2026-08-20', $foto->created_at?->toDateString());

        // Koreksi tanggal kegiatan ikut membawa fotonya, tanpa menyentuh
        // baris fotonya sendiri.
        $penyaluran->update(['tanggal_penyaluran' => '2026-08-15']);

        $this->assertSame('2026-08-15', $foto->fresh()->tanggal()?->toDateString());
    }

    public function test_foto_dan_riwayatnya_tampil_di_halaman_detail(): void
    {
        $penyaluran = $this->penyaluran();
        $admin = $this->admin();

        $this->actingAs($admin)->post("/penyaluran/{$penyaluran->id}/foto", [
            'foto' => [UploadedFile::fake()->image('lapangan.jpg')],
        ]);

        $foto = FotoPenyaluran::sole();

        $this->actingAs($admin)
            ->get("/penyaluran/{$penyaluran->id}")
            ->assertOk()
            ->assertSee('Dokumentasi Kegiatan')
            ->assertSee(route('penyaluran.foto.tampil', $foto), escape: false)
            ->assertSee('Foto ditambahkan');

        $this->assertSame(
            RiwayatPenyaluran::AKSI_FOTO_DITAMBAH,
            RiwayatPenyaluran::query()->latest('id')->first()->aksi,
        );
    }

    public function test_berkas_foto_hanya_dapat_dibuka_setelah_login(): void
    {
        $penyaluran = $this->penyaluran();

        // Fotonya dibuat langsung, bukan lewat unggahan, supaya permintaan
        // pertama pada pengujian ini benar-benar datang sebagai tamu.
        $foto = $penyaluran->fotos()->create([
            'user_id' => $this->admin()->id,
            'path' => 'dokumentasi/'.$penyaluran->id.'/lapangan.jpg',
        ]);

        Storage::disk(FotoPenyaluran::DISK)->put($foto->path, 'isi berkas');

        $this->get(route('penyaluran.foto.tampil', $foto))->assertRedirect('/login');

        // Pimpinan hanya boleh melihat, dan memang harus bisa melihat.
        $this->actingAs(User::factory()->pimpinan()->create())
            ->get(route('penyaluran.foto.tampil', $foto))
            ->assertOk();
    }

    /**
     * Kegiatan pada pengujian ini diinput admin, sehingga petugas mana pun
     * bukan pemiliknya. Petugas yang menginput sendiri kegiatannya diuji pada
     * HakAksesPetugasTest.
     */
    public function test_bukan_pemilik_dan_bukan_admin_tidak_dapat_menambah_atau_menghapus_foto(): void
    {
        $penyaluran = $this->penyaluran();

        $this->actingAs($this->admin())->post("/penyaluran/{$penyaluran->id}/foto", [
            'foto' => [UploadedFile::fake()->image('lapangan.jpg')],
        ]);

        $foto = FotoPenyaluran::sole();

        foreach ([User::factory()->petugas()->create(), User::factory()->pimpinan()->create()] as $pengguna) {
            $this->actingAs($pengguna)
                ->post("/penyaluran/{$penyaluran->id}/foto", [
                    'foto' => [UploadedFile::fake()->image('lain.jpg')],
                ])
                ->assertForbidden();

            $this->actingAs($pengguna)
                ->delete(route('penyaluran.foto.destroy', $foto))
                ->assertForbidden();
        }

        $this->assertSame(1, FotoPenyaluran::count());
    }

    public function test_admin_dapat_menghapus_foto_yang_salah(): void
    {
        $penyaluran = $this->penyaluran();
        $admin = $this->admin();

        $this->actingAs($admin)->post("/penyaluran/{$penyaluran->id}/foto", [
            'foto' => [UploadedFile::fake()->image('salah.jpg')],
        ]);

        $foto = FotoPenyaluran::sole();

        $this->actingAs($admin)
            ->delete(route('penyaluran.foto.destroy', $foto))
            ->assertRedirect("/penyaluran/{$penyaluran->id}");

        $this->assertSame(0, FotoPenyaluran::count());
        Storage::disk(FotoPenyaluran::DISK)->assertMissing($foto->path);

        // Data kegiatannya sendiri tidak ikut terhapus, hanya tercatat pada riwayat.
        $this->assertNotNull($penyaluran->fresh());
        $this->assertSame(
            RiwayatPenyaluran::AKSI_FOTO_DIHAPUS,
            RiwayatPenyaluran::query()->latest('id')->first()->aksi,
        );
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        $penyaluran = $this->penyaluran();

        $this->actingAs($this->admin())
            ->post("/penyaluran/{$penyaluran->id}/foto", [
                'foto' => [UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf')],
            ])
            ->assertSessionHasErrors('foto.0');

        $this->assertSame(0, FotoPenyaluran::count());
    }

    /**
     * Lampiran laporan mengambil foto lewat kegiatannya, bukan lewat tanggal,
     * lalu mengelompokkannya menurut tanggal kegiatan tersebut.
     */
    public function test_lampiran_laporan_memuat_foto_kegiatan_pada_periodenya(): void
    {
        $admin = $this->admin();

        $agustus = $this->penyaluran('2026-08-12', 'Tongo');
        $september = $this->penyaluran('2026-09-01', 'Pinomontiga');

        foreach ([$agustus, $september] as $penyaluran) {
            $this->actingAs($admin)->post("/penyaluran/{$penyaluran->id}/foto", [
                'foto' => [UploadedFile::fake()->image('lapangan.jpg')],
            ]);
        }

        [$fotoAgustus, $fotoSeptember] = [$agustus->fotos()->sole(), $september->fotos()->sole()];

        $this->actingAs($admin)
            ->get(route('laporan.cetak', [
                'tanggal_mulai' => '2026-08-01',
                'tanggal_akhir' => '2026-08-31',
                'lampiran' => 1,
            ]))
            ->assertOk()
            ->assertSee('Lampiran Dokumentasi Kegiatan Penyaluran Air Bersih')
            ->assertSee('12 Agustus 2026')
            ->assertSee(route('penyaluran.foto.tampil', $fotoAgustus), escape: false)
            // Kegiatan di luar periode tidak ikut terbawa.
            ->assertDontSee(route('penyaluran.foto.tampil', $fotoSeptember), escape: false);
    }

    public function test_lampiran_tidak_dicetak_bila_tidak_diminta(): void
    {
        $penyaluran = $this->penyaluran();

        $this->actingAs($this->admin())->post("/penyaluran/{$penyaluran->id}/foto", [
            'foto' => [UploadedFile::fake()->image('lapangan.jpg')],
        ]);

        $this->actingAs($this->admin())
            ->get(route('laporan.cetak'))
            ->assertOk()
            ->assertDontSee('Lampiran Dokumentasi Kegiatan Penyaluran Air Bersih');
    }
}
