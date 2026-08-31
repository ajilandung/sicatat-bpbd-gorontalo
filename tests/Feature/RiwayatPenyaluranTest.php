<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Instansi;
use App\Models\Kecamatan;
use App\Models\Penyaluran;
use App\Models\RiwayatPenyaluran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji riwayat perubahan data penyaluran dan penghapusannya
 * (FR-10, Technical Architecture §9.3).
 *
 * Data historis boleh dikoreksi kapan saja karena laporan lapangan kerap baru
 * lengkap beberapa hari kemudian. Konsekuensinya: koreksi harus dapat
 * ditelusuri, dan penghapusan tidak boleh benar-benar menghilangkan data.
 */
class RiwayatPenyaluranTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function desa(string $nama = 'Tongo'): Desa
    {
        return Desa::factory()->create([
            'kecamatan_id' => Kecamatan::factory()->create()->id,
            'nama' => $nama,
        ]);
    }

    /**
     * Satu kegiatan lengkap dengan desa dan instansinya.
     */
    private function penyaluran(User $admin, ?Desa $desa = null): Penyaluran
    {
        $penyaluran = Penyaluran::factory()->padaTanggal('2026-08-12')->create([
            'user_id' => $admin->id,
            'volume_liter' => 4000,
            'jumlah_kk' => 220,
            'jumlah_jiwa' => 459,
        ]);

        $penyaluran->desas()->attach(($desa ?? $this->desa())->id);
        $penyaluran->instansis()->attach(Instansi::factory()->create()->id);

        return $penyaluran;
    }

    public function test_penambahan_data_tercatat_pada_riwayat(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/penyaluran', [
            'tanggal_penyaluran' => '2026-08-12',
            'desa_id' => [$this->desa()->id],
            'instansi_id' => [Instansi::factory()->create()->id],
            'volume_liter' => 4000,
        ])->assertSessionHasNoErrors();

        $riwayat = RiwayatPenyaluran::sole();

        $this->assertSame(RiwayatPenyaluran::AKSI_DIBUAT, $riwayat->aksi);
        $this->assertSame($admin->id, $riwayat->user_id);
        $this->assertSame(Penyaluran::sole()->id, $riwayat->penyaluran_id);
    }

    public function test_perubahan_dicatat_beserta_nilai_sebelum_dan_sesudahnya(): void
    {
        $admin = $this->admin();
        $penyaluran = $this->penyaluran($admin);

        $desaTambahan = $this->desa('Tolotio');

        $this->actingAs($admin)->put("/penyaluran/{$penyaluran->id}", [
            'tanggal_penyaluran' => '2026-08-12',
            'desa_id' => [$penyaluran->desas->first()->id, $desaTambahan->id],
            'instansi_id' => $penyaluran->instansis->pluck('id')->all(),
            'jumlah_kk' => 246,
            'jumlah_jiwa' => 459,
            'volume_liter' => 16000,
        ])->assertSessionHasNoErrors();

        $riwayat = RiwayatPenyaluran::where('aksi', RiwayatPenyaluran::AKSI_DIUBAH)->sole();
        $perubahan = $riwayat->perubahan;

        $this->assertSame(['dari' => 4000, 'ke' => 16000], $perubahan['volume_liter']);
        $this->assertSame(['dari' => 220, 'ke' => 246], $perubahan['jumlah_kk']);

        // Perubahan daftar desa ikut tercatat, bukan hanya kolom angka.
        $this->assertCount(1, $perubahan['desa']['dari']);
        $this->assertCount(2, $perubahan['desa']['ke']);

        // Kolom yang tidak berubah tidak ikut memenuhi riwayat.
        $this->assertArrayNotHasKey('jumlah_jiwa', $perubahan);
        $this->assertArrayNotHasKey('tanggal_penyaluran', $perubahan);
    }

    public function test_menyimpan_tanpa_mengubah_apa_pun_tidak_menambah_riwayat(): void
    {
        $admin = $this->admin();
        $penyaluran = $this->penyaluran($admin);

        $this->actingAs($admin)->put("/penyaluran/{$penyaluran->id}", [
            'tanggal_penyaluran' => '2026-08-12',
            'desa_id' => $penyaluran->desas->pluck('id')->all(),
            'instansi_id' => $penyaluran->instansis->pluck('id')->all(),
            'jumlah_kk' => 220,
            'jumlah_jiwa' => 459,
            'volume_liter' => 4000,
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, RiwayatPenyaluran::where('aksi', RiwayatPenyaluran::AKSI_DIUBAH)->count());
    }

    public function test_riwayat_perubahan_hanya_terlihat_oleh_admin(): void
    {
        $admin = $this->admin();
        $penyaluran = $this->penyaluran($admin);

        RiwayatPenyaluran::catat($penyaluran, RiwayatPenyaluran::AKSI_DIBUAT, [], $admin);

        $this->actingAs($admin)
            ->get("/penyaluran/{$penyaluran->id}")
            ->assertOk()
            ->assertSee('Riwayat Perubahan');

        $this->actingAs(User::factory()->pimpinan()->create())
            ->get("/penyaluran/{$penyaluran->id}")
            ->assertOk()
            ->assertDontSee('Riwayat Perubahan');
    }

    public function test_penghapusan_memakai_soft_delete_dan_hilang_dari_riwayat_penyaluran(): void
    {
        $admin = $this->admin();
        $penyaluran = $this->penyaluran($admin, $this->desa('Mulyonegoro'));

        $this->actingAs($admin)
            ->delete("/penyaluran/{$penyaluran->id}")
            ->assertRedirect(route('penyaluran.index'));

        // Baris tetap ada di basis data, hanya ditandai terhapus.
        $this->assertSoftDeleted('penyalurans', ['id' => $penyaluran->id]);

        $this->actingAs($admin)
            ->get('/penyaluran')
            ->assertOk()
            ->assertDontSee('Mulyonegoro');

        $this->assertDatabaseHas('riwayat_penyalurans', [
            'penyaluran_id' => $penyaluran->id,
            'aksi' => RiwayatPenyaluran::AKSI_DIHAPUS,
            'user_id' => $admin->id,
        ]);
    }

    public function test_admin_dapat_memulihkan_data_yang_terhapus(): void
    {
        $admin = $this->admin();
        $penyaluran = $this->penyaluran($admin, $this->desa('Mulyonegoro'));
        $penyaluran->delete();

        $this->actingAs($admin)
            ->get('/penyaluran/terhapus')
            ->assertOk()
            ->assertSee('Mulyonegoro');

        $this->actingAs($admin)
            ->patch("/penyaluran/{$penyaluran->id}/pulihkan")
            ->assertRedirect(route('penyaluran.show', $penyaluran));

        $this->assertNotSoftDeleted('penyalurans', ['id' => $penyaluran->id]);

        $this->assertDatabaseHas('riwayat_penyalurans', [
            'penyaluran_id' => $penyaluran->id,
            'aksi' => RiwayatPenyaluran::AKSI_DIPULIHKAN,
        ]);

        $this->actingAs($admin)
            ->get('/penyaluran')
            ->assertOk()
            ->assertSee('Mulyonegoro');
    }

    public function test_selain_admin_tidak_dapat_membuka_atau_memulihkan_data_terhapus(): void
    {
        $penyaluran = $this->penyaluran($this->admin());
        $penyaluran->delete();

        $pimpinan = User::factory()->pimpinan()->create();

        $this->actingAs($pimpinan)->get('/penyaluran/terhapus')->assertForbidden();
        $this->actingAs($pimpinan)->patch("/penyaluran/{$penyaluran->id}/pulihkan")->assertForbidden();

        // Detail data terhapus pun tidak boleh terlihat oleh role lain.
        $this->actingAs($pimpinan)->get("/penyaluran/{$penyaluran->id}")->assertNotFound();

        $this->assertSoftDeleted('penyalurans', ['id' => $penyaluran->id]);
    }

    public function test_selain_admin_tidak_dapat_menghapus_data_penyaluran(): void
    {
        $penyaluran = $this->penyaluran($this->admin());

        $this->actingAs(User::factory()->petugas()->create())
            ->delete("/penyaluran/{$penyaluran->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted('penyalurans', ['id' => $penyaluran->id]);
    }
}
