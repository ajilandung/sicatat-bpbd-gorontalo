<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\FotoPenyaluran;
use App\Models\Instansi;
use App\Models\Kecamatan;
use App\Models\Penyaluran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menguji hak akses role Petugas atas data penyaluran (FR-02).
 *
 * Petugas boleh menginput kegiatan dan mengoreksi kegiatan yang ia input
 * sendiri, tetapi tidak boleh menyentuh kegiatan milik pengguna lain — juga
 * tidak dengan mengetik URL secara langsung atau mengirim permintaan tanpa
 * pernah membuka formnya. Karena itu setiap larangan di sini diuji pada
 * jalur HTTP-nya, bukan sekadar pada tampilan tombolnya.
 */
class HakAksesPetugasTest extends TestCase
{
    use RefreshDatabase;

    private function desa(string $nama = 'Tongo'): Desa
    {
        return Desa::factory()->create([
            'kecamatan_id' => Kecamatan::factory()->create()->id,
            'nama' => $nama,
        ]);
    }

    /**
     * Satu kegiatan lengkap milik pengguna tertentu.
     */
    private function kegiatanMilik(User $pemilik, string $nama = 'Tongo'): Penyaluran
    {
        $penyaluran = Penyaluran::factory()->padaTanggal('2026-08-12')->create([
            'user_id' => $pemilik->id,
            'volume_liter' => 4000,
        ]);

        $penyaluran->desas()->attach($this->desa($nama)->id);
        $penyaluran->instansis()->attach(Instansi::factory()->create()->id);

        return $penyaluran;
    }

    /**
     * @return array<string, mixed>
     */
    private function isian(Penyaluran $penyaluran, array $ubahan = []): array
    {
        return array_merge([
            'tanggal_penyaluran' => '2026-08-12',
            'desa_id' => $penyaluran->desas->pluck('id')->all(),
            'instansi_id' => $penyaluran->instansis->pluck('id')->all(),
            'volume_liter' => $penyaluran->volume_liter,
            'konfirmasi_duplikat' => 1,
        ], $ubahan);
    }

    public function test_petugas_dapat_membuka_form_dan_menyimpan_penyaluran(): void
    {
        $petugas = User::factory()->petugas()->create();
        $desa = $this->desa();
        $instansi = Instansi::factory()->create();

        $this->actingAs($petugas)->get('/penyaluran/create')->assertOk();

        $this->actingAs($petugas)
            ->post('/penyaluran', [
                'tanggal_penyaluran' => '2026-08-12',
                'desa_id' => [$desa->id],
                'instansi_id' => [$instansi->id],
                'volume_liter' => 16000,
            ])
            ->assertSessionHasNoErrors();

        $penyaluran = Penyaluran::sole();

        // Kepemilikan terisi dari akun yang sedang login, bukan dari isian form.
        $this->assertSame($petugas->id, $penyaluran->user_id);
        $this->assertSame(16000, $penyaluran->volume_liter);
    }

    public function test_petugas_dapat_mengubah_data_miliknya_sendiri(): void
    {
        $petugas = User::factory()->petugas()->create();
        $penyaluran = $this->kegiatanMilik($petugas);

        $this->actingAs($petugas)->get("/penyaluran/{$penyaluran->id}/edit")->assertOk();

        $this->actingAs($petugas)
            ->put("/penyaluran/{$penyaluran->id}", $this->isian($penyaluran, ['volume_liter' => 6000]))
            ->assertRedirect("/penyaluran/{$penyaluran->id}")
            ->assertSessionHasNoErrors();

        $this->assertSame(6000, $penyaluran->fresh()->volume_liter);
    }

    /**
     * Inti permintaan: batas kepemilikan harus ditegakkan di backend, bukan
     * dengan menyembunyikan tombol.
     */
    public function test_petugas_tidak_dapat_mengubah_data_pengguna_lain(): void
    {
        $petugas = User::factory()->petugas()->create();

        $milikPetugasLain = $this->kegiatanMilik(User::factory()->petugas()->create(), 'Tolotio');
        $milikAdmin = $this->kegiatanMilik(User::factory()->admin()->create(), 'Batu Hijau');

        foreach ([$milikPetugasLain, $milikAdmin] as $penyaluran) {
            // Mengetik URL halaman ubah secara langsung.
            $this->actingAs($petugas)
                ->get("/penyaluran/{$penyaluran->id}/edit")
                ->assertForbidden();

            // Mengirim perubahan tanpa pernah membuka formnya.
            $this->actingAs($petugas)
                ->put("/penyaluran/{$penyaluran->id}", $this->isian($penyaluran, ['volume_liter' => 1]))
                ->assertForbidden();

            $this->assertSame(4000, $penyaluran->fresh()->volume_liter);
        }
    }

    public function test_petugas_tidak_dapat_menghapus_atau_memulihkan_data(): void
    {
        $petugas = User::factory()->petugas()->create();
        $milikSendiri = $this->kegiatanMilik($petugas);

        // Bahkan atas data yang ia input sendiri.
        $this->actingAs($petugas)
            ->delete("/penyaluran/{$milikSendiri->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted($milikSendiri);

        $terhapus = $this->kegiatanMilik(User::factory()->admin()->create(), 'Pinomontiga');
        $terhapus->delete();

        $this->actingAs($petugas)->get('/penyaluran/terhapus')->assertForbidden();
        $this->actingAs($petugas)->patch("/penyaluran/{$terhapus->id}/pulihkan")->assertForbidden();

        $this->assertSoftDeleted($terhapus);
    }

    public function test_petugas_tidak_dapat_membuka_laporan_master_data_dan_manajemen_pengguna(): void
    {
        $petugas = User::factory()->petugas()->create();

        foreach ([
            '/laporan',
            '/laporan/excel',
            '/wilayah/desa',
            '/instansi',
            '/pengguna',
        ] as $jalur) {
            $this->actingAs($petugas)->get($jalur)->assertForbidden();
        }
    }

    public function test_tombol_ubah_hanya_muncul_pada_data_milik_petugas_sendiri(): void
    {
        $petugas = User::factory()->petugas()->create();

        $milikSendiri = $this->kegiatanMilik($petugas, 'Tongo');
        $milikOrangLain = $this->kegiatanMilik(User::factory()->admin()->create(), 'Batu Hijau');

        $halaman = $this->actingAs($petugas)->get('/penyaluran')->assertOk();

        $halaman->assertSee(route('penyaluran.edit', $milikSendiri), escape: false);
        $halaman->assertDontSee(route('penyaluran.edit', $milikOrangLain), escape: false);

        // Menghapus tetap khusus admin, jadi tidak ada satu pun dialog hapus.
        // Dicocokkan ke teks dialognya, bukan ke URL-nya: alamat `destroy`
        // sama persis dengan alamat halaman detail yang memang ditampilkan.
        $halaman->assertDontSee('Hapus data penyaluran ini?');
    }

    public function test_petugas_dapat_mengelola_foto_pada_kegiatannya_sendiri(): void
    {
        Storage::fake(FotoPenyaluran::DISK);

        $petugas = User::factory()->petugas()->create();
        $milikSendiri = $this->kegiatanMilik($petugas);
        $milikOrangLain = $this->kegiatanMilik(User::factory()->admin()->create(), 'Batu Hijau');

        $this->actingAs($petugas)
            ->post("/penyaluran/{$milikSendiri->id}/foto", [
                'foto' => [UploadedFile::fake()->image('lapangan.jpg')],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $milikSendiri->fotos()->count());

        $this->actingAs($petugas)
            ->post("/penyaluran/{$milikOrangLain->id}/foto", [
                'foto' => [UploadedFile::fake()->image('lapangan.jpg')],
            ])
            ->assertForbidden();

        $this->assertSame(0, $milikOrangLain->fotos()->count());

        // Menghapus fotonya sendiri boleh; menghapus milik orang lain tidak.
        $foto = $milikSendiri->fotos()->sole();

        $milikOrangLain->fotos()->create(['user_id' => $milikOrangLain->user_id, 'path' => 'dokumentasi/x.jpg']);

        $this->actingAs($petugas)
            ->delete(route('penyaluran.foto.destroy', $milikOrangLain->fotos()->sole()))
            ->assertForbidden();

        $this->actingAs($petugas)
            ->delete(route('penyaluran.foto.destroy', $foto))
            ->assertRedirect("/penyaluran/{$milikSendiri->id}");

        $this->assertSame(0, $milikSendiri->fotos()->count());
    }

    public function test_petugas_melihat_riwayat_perubahan_hanya_pada_data_miliknya(): void
    {
        $petugas = User::factory()->petugas()->create();

        $milikSendiri = $this->kegiatanMilik($petugas);
        $milikOrangLain = $this->kegiatanMilik(User::factory()->admin()->create(), 'Batu Hijau');

        $this->actingAs($petugas)
            ->get("/penyaluran/{$milikSendiri->id}")
            ->assertOk()
            ->assertSee('Riwayat Perubahan');

        $this->actingAs($petugas)
            ->get("/penyaluran/{$milikOrangLain->id}")
            ->assertOk()
            ->assertDontSee('Riwayat Perubahan');
    }

    public function test_pimpinan_tetap_tidak_dapat_menginput_maupun_mengubah(): void
    {
        $pimpinan = User::factory()->pimpinan()->create();
        $penyaluran = $this->kegiatanMilik(User::factory()->admin()->create());

        $this->actingAs($pimpinan)->get('/penyaluran/create')->assertForbidden();
        $this->actingAs($pimpinan)->get("/penyaluran/{$penyaluran->id}/edit")->assertForbidden();
        $this->actingAs($pimpinan)
            ->put("/penyaluran/{$penyaluran->id}", $this->isian($penyaluran, ['volume_liter' => 1]))
            ->assertForbidden();

        $this->assertSame(4000, $penyaluran->fresh()->volume_liter);
    }
}
