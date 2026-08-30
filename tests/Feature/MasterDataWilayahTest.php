<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji Master Data Wilayah (FR-04, FR-05, FR-06).
 *
 * Kabupaten dan kecamatan hanya dapat dilihat; desa/kelurahan dapat ditambah
 * dan diubah, tetapi tidak dapat dihapus — hanya dinonaktifkan.
 */
class MasterDataWilayahTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /**
     * Membuat satu desa lengkap dengan kecamatan dan kabupatennya. Kabupaten
     * dan kecamatan yang sudah ada dipakai ulang, sehingga beberapa desa dapat
     * dibuat di wilayah yang sama tanpa melanggar batasan unik.
     */
    private function buatDesa(string $namaDesa, string $namaKecamatan, string $namaKabupaten): Desa
    {
        $kabupaten = Kabupaten::firstWhere('nama', $namaKabupaten)
            ?? Kabupaten::factory()->create(['nama' => $namaKabupaten]);

        $kecamatan = Kecamatan::firstWhere(['nama' => $namaKecamatan, 'kabupaten_id' => $kabupaten->id])
            ?? Kecamatan::factory()->create([
                'nama' => $namaKecamatan,
                'kabupaten_id' => $kabupaten->id,
            ]);

        return Desa::factory()->create([
            'nama' => $namaDesa,
            'kecamatan_id' => $kecamatan->id,
        ]);
    }

    public function test_selain_admin_tidak_dapat_membuka_data_wilayah(): void
    {
        $halaman = ['/wilayah/kabupaten', '/wilayah/kecamatan', '/wilayah/desa'];

        foreach ([User::factory()->petugas(), User::factory()->pimpinan()] as $pabrik) {
            $pengguna = $pabrik->create();

            foreach ($halaman as $url) {
                $this->actingAs($pengguna)->get($url)->assertForbidden();
            }
        }
    }

    public function test_selain_admin_tidak_dapat_menambah_desa(): void
    {
        $kecamatan = Kecamatan::factory()->create();

        $this->actingAs(User::factory()->petugas()->create())
            ->post('/wilayah/desa', [
                'kecamatan_id' => $kecamatan->id,
                'nama' => 'Desa Penyusup',
                'jenis' => 'desa',
                'aktif' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('desas', ['nama' => 'Desa Penyusup']);
    }

    public function test_admin_dapat_membuka_daftar_kabupaten_beserta_cakupannya(): void
    {
        $desa = $this->buatDesa('Mulyonegoro', 'Pulubala', 'Gorontalo');
        $kabupatenId = $desa->kecamatan->kabupaten_id;

        $this->actingAs($this->admin())
            ->get('/wilayah/kabupaten')
            ->assertOk()
            ->assertSee('Kabupaten Gorontalo')
            // Cakupan wilayahnya ditautkan ke daftar kecamatan dan desa yang tersaring.
            ->assertSee('/wilayah/kecamatan?kabupaten_id='.$kabupatenId, false)
            ->assertSee('/wilayah/desa?kabupaten_id='.$kabupatenId, false);
    }

    public function test_daftar_kabupaten_dapat_disaring_menurut_jenis(): void
    {
        Kabupaten::factory()->create(['nama' => 'Bone Bolango']);
        Kabupaten::factory()->kota()->create(['nama' => 'Gorontalo']);

        $this->actingAs($this->admin())
            ->get('/wilayah/kabupaten?jenis=kota')
            ->assertOk()
            ->assertSee('Kota Gorontalo')
            ->assertDontSee('Kabupaten Bone Bolango');
    }

    public function test_daftar_kecamatan_dapat_dicari_dan_disaring_per_kabupaten(): void
    {
        $this->buatDesa('Tongo', 'Bone Pantai', 'Bone Bolango');
        $this->buatDesa('Mulyonegoro', 'Pulubala', 'Gorontalo');

        $admin = $this->admin();
        $boneBolango = Kabupaten::where('nama', 'Bone Bolango')->firstOrFail();

        $this->actingAs($admin)
            ->get('/wilayah/kecamatan?kabupaten_id='.$boneBolango->id)
            ->assertOk()
            ->assertSee('Bone Pantai')
            ->assertDontSee('Pulubala');

        $this->actingAs($admin)
            ->get('/wilayah/kecamatan?cari=Pulubala')
            ->assertOk()
            ->assertSee('Pulubala')
            ->assertDontSee('Bone Pantai');
    }

    public function test_daftar_desa_dapat_dicari_dan_difilter(): void
    {
        $tongo = $this->buatDesa('Tongo', 'Bone Pantai', 'Bone Bolango');
        $this->buatDesa('Mulyonegoro', 'Pulubala', 'Gorontalo');

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/wilayah/desa?cari=Tongo')
            ->assertOk()
            ->assertSee('Tongo')
            ->assertDontSee('Mulyonegoro');

        $this->actingAs($admin)
            ->get('/wilayah/desa?kecamatan_id='.$tongo->kecamatan_id)
            ->assertOk()
            ->assertSee('Tongo')
            ->assertDontSee('Mulyonegoro');

        $this->actingAs($admin)
            ->get('/wilayah/desa?kabupaten_id='.$tongo->kecamatan->kabupaten_id)
            ->assertOk()
            ->assertSee('Tongo')
            ->assertDontSee('Mulyonegoro');
    }

    public function test_daftar_desa_dapat_difilter_menurut_status(): void
    {
        $this->buatDesa('Tongo', 'Bone Pantai', 'Bone Bolango');

        $nonaktif = $this->buatDesa('Tolotio', 'Tilongkabila', 'Bone Bolango');
        $nonaktif->update(['aktif' => false]);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/wilayah/desa?status=nonaktif')
            ->assertOk()
            ->assertSee('Tolotio')
            ->assertDontSee('Tongo');

        $this->actingAs($admin)
            ->get('/wilayah/desa?status=aktif')
            ->assertOk()
            ->assertSee('Tongo')
            ->assertDontSee('Tolotio');
    }

    public function test_admin_dapat_membuka_form_tambah_desa(): void
    {
        Kecamatan::factory()->create(['nama' => 'Pulubala']);

        $this->actingAs($this->admin())
            ->get('/wilayah/desa/create')
            ->assertOk()
            ->assertSee('Pulubala');
    }

    public function test_admin_dapat_menambah_desa(): void
    {
        $kecamatan = Kecamatan::factory()->create();

        $this->actingAs($this->admin())
            ->post('/wilayah/desa', [
                'kecamatan_id' => $kecamatan->id,
                'kode' => '75.01.05.2003',
                'nama' => 'Pinomontiga',
                'jenis' => 'desa',
                'aktif' => 1,
            ])
            ->assertRedirect('/wilayah/desa')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('desas', [
            'kecamatan_id' => $kecamatan->id,
            'kode' => '75.01.05.2003',
            'nama' => 'Pinomontiga',
            'jenis' => 'desa',
            'aktif' => true,
        ]);
    }

    public function test_nama_desa_boleh_sama_asal_beda_kecamatan(): void
    {
        // 61 nama desa di Provinsi Gorontalo tidak unik — mis. ada dua
        // "Talumopatu" di kecamatan yang berbeda. Sistem harus mengizinkannya.
        $pertama = $this->buatDesa('Talumopatu', 'Mootilango', 'Gorontalo');
        $kecamatanLain = Kecamatan::factory()->create(['nama' => 'Tapa']);

        $this->actingAs($this->admin())
            ->post('/wilayah/desa', [
                'kecamatan_id' => $kecamatanLain->id,
                'nama' => 'Talumopatu',
                'jenis' => 'desa',
                'aktif' => 1,
            ])
            ->assertRedirect('/wilayah/desa')
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Desa::where('nama', 'Talumopatu')->count());
        $this->assertNotSame($pertama->kecamatan_id, $kecamatanLain->id);
    }

    public function test_nama_desa_tidak_boleh_ganda_dalam_satu_kecamatan(): void
    {
        $desa = $this->buatDesa('Tongo', 'Bone Pantai', 'Bone Bolango');

        $this->actingAs($this->admin())
            ->post('/wilayah/desa', [
                'kecamatan_id' => $desa->kecamatan_id,
                'nama' => 'Tongo',
                'jenis' => 'desa',
                'aktif' => 1,
            ])
            ->assertSessionHasErrors('nama');

        $this->assertSame(1, Desa::where('nama', 'Tongo')->count());
    }

    public function test_kode_wilayah_tidak_boleh_ganda(): void
    {
        Desa::factory()->create(['kode' => '75.01.05.2003']);
        $kecamatan = Kecamatan::factory()->create();

        $this->actingAs($this->admin())
            ->post('/wilayah/desa', [
                'kecamatan_id' => $kecamatan->id,
                'kode' => '75.01.05.2003',
                'nama' => 'Desa Baru',
                'jenis' => 'desa',
                'aktif' => 1,
            ])
            ->assertSessionHasErrors('kode');
    }

    public function test_admin_dapat_mengubah_data_desa(): void
    {
        $desa = $this->buatDesa('Tongo', 'Bone Pantai', 'Bone Bolango');

        $this->actingAs($this->admin())
            ->put('/wilayah/desa/'.$desa->id, [
                'kecamatan_id' => $desa->kecamatan_id,
                'kode' => $desa->kode,
                'nama' => 'Tongo Baru',
                'jenis' => 'kelurahan',
                'aktif' => 1,
            ])
            ->assertRedirect('/wilayah/desa')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('desas', [
            'id' => $desa->id,
            'nama' => 'Tongo Baru',
            'jenis' => 'kelurahan',
        ]);
    }

    public function test_admin_dapat_menonaktifkan_dan_mengaktifkan_desa(): void
    {
        $desa = $this->buatDesa('Tongo', 'Bone Pantai', 'Bone Bolango');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch('/wilayah/desa/'.$desa->id.'/status')
            ->assertRedirect();

        $this->assertFalse($desa->fresh()->aktif);

        $this->actingAs($admin)
            ->patch('/wilayah/desa/'.$desa->id.'/status')
            ->assertRedirect();

        $this->assertTrue($desa->fresh()->aktif);
    }

    public function test_desa_nonaktif_dikecualikan_dari_pilihan_penyaluran(): void
    {
        $aktif = Desa::factory()->create(['nama' => 'Tongo']);
        Desa::factory()->nonaktif()->create(['nama' => 'Tolotio']);

        $this->assertSame([$aktif->id], Desa::aktif()->pluck('id')->all());
    }

    public function test_desa_tidak_dapat_dihapus(): void
    {
        $desa = Desa::factory()->create();

        // Route hapus sengaja tidak didaftarkan: wilayah yang sudah dipakai
        // pada riwayat penyaluran tidak boleh hilang.
        $this->actingAs($this->admin())
            ->delete('/wilayah/desa/'.$desa->id)
            ->assertStatus(405);

        $this->assertDatabaseHas('desas', ['id' => $desa->id]);
    }

    public function test_kabupaten_dan_kecamatan_tidak_dapat_diubah_lewat_aplikasi(): void
    {
        $admin = $this->admin();

        // Hanya halaman daftar yang tersedia untuk dua tingkat wilayah ini.
        $this->actingAs($admin)->get('/wilayah/kabupaten/create')->assertNotFound();
        $this->actingAs($admin)->get('/wilayah/kecamatan/create')->assertNotFound();
        $this->actingAs($admin)->post('/wilayah/kabupaten', ['nama' => 'Wilayah Baru'])->assertStatus(405);
        $this->actingAs($admin)->post('/wilayah/kecamatan', ['nama' => 'Wilayah Baru'])->assertStatus(405);
    }
}
