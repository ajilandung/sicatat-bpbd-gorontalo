<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji Master Data Instansi Pelaksana (FR-07).
 *
 * Admin bebas menambah instansi baru, tetapi tidak dapat menghapusnya —
 * instansi yang tidak lagi terlibat cukup dinonaktifkan.
 */
class MasterDataInstansiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_selain_admin_tidak_dapat_membuka_data_instansi(): void
    {
        foreach ([User::factory()->petugas(), User::factory()->pimpinan()] as $pabrik) {
            $this->actingAs($pabrik->create())
                ->get('/instansi')
                ->assertForbidden();
        }
    }

    public function test_selain_admin_tidak_dapat_menambah_instansi(): void
    {
        $this->actingAs(User::factory()->pimpinan()->create())
            ->post('/instansi', [
                'nama' => 'Instansi Penyusup',
                'aktif' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('instansis', ['nama' => 'Instansi Penyusup']);
    }

    public function test_admin_dapat_membuka_daftar_instansi(): void
    {
        Instansi::factory()->create([
            'nama' => 'BPBD Provinsi Gorontalo',
            'singkatan' => 'BPBD Provinsi',
        ]);

        $this->actingAs($this->admin())
            ->get('/instansi')
            ->assertOk()
            ->assertSee('BPBD Provinsi Gorontalo')
            ->assertSee('BPBD Provinsi');
    }

    public function test_daftar_instansi_dapat_dicari_dan_difilter(): void
    {
        Instansi::factory()->create(['nama' => 'PMI Provinsi Gorontalo', 'singkatan' => 'PMI']);
        Instansi::factory()->nonaktif()->create(['nama' => 'Polsek Bone Pantai', 'singkatan' => 'Polsek']);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/instansi?cari=PMI')
            ->assertOk()
            ->assertSee('PMI Provinsi Gorontalo')
            ->assertDontSee('Polsek Bone Pantai');

        $this->actingAs($admin)
            ->get('/instansi?status=nonaktif')
            ->assertOk()
            ->assertSee('Polsek Bone Pantai')
            ->assertDontSee('PMI Provinsi Gorontalo');
    }

    public function test_admin_dapat_menambah_instansi_baru(): void
    {
        $this->actingAs($this->admin())
            ->post('/instansi', [
                'nama' => 'PDAM Kabupaten Bone Bolango',
                'singkatan' => 'PDAM Bone Bolango',
                'alamat' => 'Jl. Kesehatan No. 1, Suwawa',
                'telepon' => '0435-123456',
                'aktif' => 1,
            ])
            ->assertRedirect('/instansi')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('instansis', [
            'nama' => 'PDAM Kabupaten Bone Bolango',
            'singkatan' => 'PDAM Bone Bolango',
            'aktif' => true,
        ]);
    }

    public function test_nama_instansi_tidak_boleh_ganda(): void
    {
        Instansi::factory()->create(['nama' => 'BPBD Provinsi Gorontalo']);

        $this->actingAs($this->admin())
            ->post('/instansi', [
                'nama' => 'BPBD Provinsi Gorontalo',
                'aktif' => 1,
            ])
            ->assertSessionHasErrors('nama');

        $this->assertSame(1, Instansi::where('nama', 'BPBD Provinsi Gorontalo')->count());
    }

    public function test_nomor_telepon_harus_berupa_angka(): void
    {
        $this->actingAs($this->admin())
            ->post('/instansi', [
                'nama' => 'Instansi Uji',
                'telepon' => 'hubungi kantor',
                'aktif' => 1,
            ])
            ->assertSessionHasErrors('telepon');
    }

    public function test_admin_dapat_mengubah_data_instansi(): void
    {
        $instansi = Instansi::factory()->create(['nama' => 'BWS Gorontalo']);

        $this->actingAs($this->admin())
            ->get('/instansi/'.$instansi->id.'/edit')
            ->assertOk()
            ->assertSee('BWS Gorontalo');

        $this->actingAs($this->admin())
            ->put('/instansi/'.$instansi->id, [
                'nama' => 'Balai Wilayah Sungai Gorontalo',
                'singkatan' => 'BWS',
                'alamat' => null,
                'telepon' => null,
                'aktif' => 1,
            ])
            ->assertRedirect('/instansi')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('instansis', [
            'id' => $instansi->id,
            'nama' => 'Balai Wilayah Sungai Gorontalo',
            'singkatan' => 'BWS',
        ]);
    }

    public function test_admin_dapat_menonaktifkan_dan_mengaktifkan_instansi(): void
    {
        $instansi = Instansi::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch('/instansi/'.$instansi->id.'/status')
            ->assertRedirect();

        $this->assertFalse($instansi->fresh()->aktif);

        $this->actingAs($admin)
            ->patch('/instansi/'.$instansi->id.'/status')
            ->assertRedirect();

        $this->assertTrue($instansi->fresh()->aktif);
    }

    public function test_instansi_nonaktif_dikecualikan_dari_pilihan_penyaluran(): void
    {
        $aktif = Instansi::factory()->create();
        Instansi::factory()->nonaktif()->create();

        $this->assertSame([$aktif->id], Instansi::aktif()->pluck('id')->all());
    }

    public function test_instansi_tidak_dapat_dihapus(): void
    {
        $instansi = Instansi::factory()->create();

        // Route hapus sengaja tidak didaftarkan: instansi yang sudah tercatat
        // pada riwayat penyaluran tidak boleh hilang dari laporan lama.
        $this->actingAs($this->admin())
            ->delete('/instansi/'.$instansi->id)
            ->assertStatus(405);

        $this->assertDatabaseHas('instansis', ['id' => $instansi->id]);
    }
}
