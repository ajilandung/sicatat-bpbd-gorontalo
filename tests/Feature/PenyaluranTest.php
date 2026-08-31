<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Instansi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Penyaluran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji modul Penyaluran (FR-08 sampai FR-18).
 *
 * Dua aturan bisnis yang paling menentukan bentuk modul ini ikut diuji di sini:
 * satu kegiatan dapat mencakup beberapa desa dan beberapa instansi sekaligus,
 * dan data susulan untuk tanggal yang sudah lewat harus tetap bisa dimasukkan.
 */
class PenyaluranTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /**
     * Satu desa lengkap dengan kecamatan dan kabupatennya.
     */
    private function desa(?Kecamatan $kecamatan = null, string $nama = 'Tongo'): Desa
    {
        return Desa::factory()->create([
            'kecamatan_id' => ($kecamatan ?? Kecamatan::factory()->create())->id,
            'nama' => $nama,
        ]);
    }

    /**
     * Isian form yang valid, siap dikirim ke `POST /penyaluran`.
     *
     * @param  array<string, mixed>  $ubahan
     * @return array<string, mixed>
     */
    private function isianValid(array $ubahan = []): array
    {
        return array_merge([
            'tanggal_penyaluran' => now()->subDays(3)->format('Y-m-d'),
            'desa_id' => [$this->desa()->id],
            'instansi_id' => [Instansi::factory()->create()->id],
            'jumlah_kk' => 220,
            'jumlah_jiwa' => 459,
            'volume_liter' => 4000,
            'keterangan' => null,
        ], $ubahan);
    }

    public function test_semua_role_dapat_membuka_riwayat_penyaluran(): void
    {
        foreach ([
            User::factory()->admin(),
            User::factory()->petugas(),
            User::factory()->pimpinan(),
        ] as $pabrik) {
            $this->actingAs($pabrik->create())
                ->get('/penyaluran')
                ->assertOk();
        }
    }

    public function test_selain_admin_tidak_dapat_membuka_form_input(): void
    {
        foreach ([User::factory()->petugas(), User::factory()->pimpinan()] as $pabrik) {
            $this->actingAs($pabrik->create())
                ->get('/penyaluran/create')
                ->assertForbidden();
        }
    }

    public function test_admin_dapat_membuka_form_input(): void
    {
        Instansi::factory()->create(['nama' => 'BPBD Provinsi Gorontalo']);
        Kabupaten::factory()->create(['nama' => 'Bone Bolango']);

        $this->actingAs($this->admin())
            ->get('/penyaluran/create')
            ->assertOk()
            ->assertSee('BPBD Provinsi Gorontalo')
            ->assertSee('Kabupaten Bone Bolango');
    }

    public function test_selain_admin_tidak_dapat_menyimpan_penyaluran(): void
    {
        $this->actingAs(User::factory()->pimpinan()->create())
            ->post('/penyaluran', $this->isianValid(['volume_liter' => 9999]))
            ->assertForbidden();

        $this->assertDatabaseCount('penyalurans', 0);
    }

    public function test_admin_dapat_menambah_penyaluran_untuk_beberapa_desa_dan_instansi(): void
    {
        $kecamatan = Kecamatan::factory()->create();

        $desa = collect(['Batu Hijau', 'Tongo', 'Tolotio', 'Pinomontiga'])
            ->map(fn (string $nama) => $this->desa($kecamatan, $nama));

        $instansi = Instansi::factory()->count(2)->create();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/penyaluran', $this->isianValid([
                'tanggal_penyaluran' => '2026-08-12',
                'desa_id' => $desa->pluck('id')->all(),
                'instansi_id' => $instansi->pluck('id')->all(),
                'jumlah_kk' => 246,
                'jumlah_jiwa' => null,
                'volume_liter' => 16000,
            ]))
            ->assertRedirect();

        $penyaluran = Penyaluran::sole();

        $this->assertSame('2026-08-12', $penyaluran->tanggal_penyaluran->format('Y-m-d'));
        $this->assertSame(16000, $penyaluran->volume_liter);
        $this->assertNull($penyaluran->jumlah_jiwa);

        // Penginput terisi otomatis dari akun yang sedang login (Keputusan §12.1 #1).
        $this->assertSame($admin->id, $penyaluran->user_id);

        $this->assertCount(4, $penyaluran->desas);
        $this->assertCount(2, $penyaluran->instansis);
        $this->assertTrue($penyaluran->angkaGabungan());
        $this->assertSame(4000.0, $penyaluran->volumePerDesa());
    }

    public function test_data_susulan_untuk_tanggal_yang_sudah_lewat_tetap_diterima(): void
    {
        $this->actingAs($this->admin())
            ->post('/penyaluran', $this->isianValid([
                'tanggal_penyaluran' => now()->subMonths(2)->format('Y-m-d'),
            ]))
            ->assertRedirect();

        $penyaluran = Penyaluran::sole();

        // Tanggal kegiatan dan waktu input memang berbeda, dan itu disengaja.
        $this->assertTrue($penyaluran->tanggal_penyaluran->lt($penyaluran->created_at->startOfDay()));
    }

    public function test_tanggal_di_masa_depan_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post('/penyaluran', $this->isianValid([
                'tanggal_penyaluran' => now()->addDay()->format('Y-m-d'),
            ]))
            ->assertSessionHasErrors('tanggal_penyaluran');

        $this->assertDatabaseCount('penyalurans', 0);
    }

    public function test_jumlah_kk_dan_jiwa_boleh_dikosongkan_tetapi_volume_wajib(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/penyaluran', $this->isianValid([
                'jumlah_kk' => null,
                'jumlah_jiwa' => null,
            ]))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post('/penyaluran', $this->isianValid(['volume_liter' => null]))
            ->assertSessionHasErrors('volume_liter');

        $this->assertDatabaseCount('penyalurans', 1);
    }

    public function test_desa_dan_instansi_wajib_dipilih(): void
    {
        $this->actingAs($this->admin())
            ->post('/penyaluran', $this->isianValid([
                'desa_id' => [],
                'instansi_id' => [],
            ]))
            ->assertSessionHasErrors(['desa_id', 'instansi_id']);

        $this->assertDatabaseCount('penyalurans', 0);
    }

    public function test_daftar_dapat_dicari_menurut_nama_desa_dan_instansi(): void
    {
        $admin = $this->admin();

        $pertama = Penyaluran::factory()->create(['user_id' => $admin->id]);
        $pertama->desas()->attach($this->desa(nama: 'Mulyonegoro')->id);
        $pertama->instansis()->attach(Instansi::factory()->create(['nama' => 'BPBD Kabupaten Gorontalo'])->id);

        $kedua = Penyaluran::factory()->create(['user_id' => $admin->id]);
        $kedua->desas()->attach($this->desa(nama: 'Pinomontiga')->id);
        $kedua->instansis()->attach(Instansi::factory()->create(['nama' => 'PDAM Bone Bolango'])->id);

        $this->actingAs($admin)
            ->get('/penyaluran?cari=Mulyonegoro')
            ->assertOk()
            ->assertSee('Mulyonegoro')
            ->assertDontSee('Pinomontiga');

        $this->actingAs($admin)
            ->get('/penyaluran?cari=PDAM')
            ->assertOk()
            ->assertSee('Pinomontiga')
            ->assertDontSee('Mulyonegoro');
    }

    public function test_daftar_dapat_difilter_menurut_wilayah(): void
    {
        $admin = $this->admin();

        $kabupatenA = Kabupaten::factory()->create(['nama' => 'Bone Bolango']);
        $kecamatanA = Kecamatan::factory()->create(['kabupaten_id' => $kabupatenA->id]);
        $desaA = $this->desa($kecamatanA, 'Tolotio');

        $desaB = $this->desa(nama: 'Mulyonegoro');

        foreach ([$desaA, $desaB] as $desa) {
            $penyaluran = Penyaluran::factory()->create(['user_id' => $admin->id]);
            $penyaluran->desas()->attach($desa->id);
        }

        $this->actingAs($admin)
            ->get('/penyaluran?kabupaten_id='.$kabupatenA->id)
            ->assertOk()
            ->assertSee('Tolotio')
            ->assertDontSee('Mulyonegoro');

        $this->actingAs($admin)
            ->get('/penyaluran?desa_id='.$desaB->id)
            ->assertOk()
            ->assertSee('Mulyonegoro')
            ->assertDontSee('Tolotio');
    }

    public function test_filter_periode_memakai_tanggal_kegiatan_bukan_tanggal_input(): void
    {
        $admin = $this->admin();

        // Kedua data dimasukkan hari ini, tetapi kegiatannya terjadi pada
        // bulan yang berbeda — persis kondisi data susulan (§9.3).
        $agustus = Penyaluran::factory()->padaTanggal('2026-08-12')->create(['user_id' => $admin->id]);
        $agustus->desas()->attach($this->desa(nama: 'Tongo')->id);

        $september = Penyaluran::factory()->padaTanggal('2026-09-02')->create(['user_id' => $admin->id]);
        $september->desas()->attach($this->desa(nama: 'Pinomontiga')->id);

        $this->actingAs($admin)
            ->get('/penyaluran?tanggal_mulai=2026-08-01&tanggal_akhir=2026-08-31')
            ->assertOk()
            ->assertSee('Tongo')
            ->assertDontSee('Pinomontiga');
    }

    public function test_admin_dapat_membuka_dan_mengubah_data_penyaluran(): void
    {
        $admin = $this->admin();

        $penyaluran = Penyaluran::factory()->create(['user_id' => $admin->id, 'volume_liter' => 4000]);
        $penyaluran->desas()->attach($desaLama = $this->desa(nama: 'Tongo')->id);
        $penyaluran->instansis()->attach($instansi = Instansi::factory()->create()->id);

        $this->actingAs($admin)
            ->get("/penyaluran/{$penyaluran->id}/edit")
            ->assertOk();

        $desaBaru = $this->desa(nama: 'Tolotio');

        $this->actingAs($admin)
            ->put("/penyaluran/{$penyaluran->id}", [
                'tanggal_penyaluran' => '2026-08-24',
                'desa_id' => [$desaLama, $desaBaru->id],
                'instansi_id' => [$instansi],
                'jumlah_kk' => 300,
                'jumlah_jiwa' => 900,
                'volume_liter' => 6000,
                'keterangan' => 'Penyaluran lanjutan.',
            ])
            ->assertRedirect(route('penyaluran.show', $penyaluran));

        $penyaluran->refresh();

        $this->assertSame(6000, $penyaluran->volume_liter);
        $this->assertSame('2026-08-24', $penyaluran->tanggal_penyaluran->format('Y-m-d'));
        $this->assertCount(2, $penyaluran->desas);
    }

    public function test_form_ubah_menampilkan_desa_dan_instansi_yang_sudah_terpilih(): void
    {
        $admin = $this->admin();

        $penyaluran = Penyaluran::factory()->create(['user_id' => $admin->id]);
        $penyaluran->desas()->attach($this->desa(nama: 'Mulyonegoro')->id);
        $penyaluran->instansis()->attach(Instansi::factory()->create(['nama' => 'PDAM Bone Bolango'])->id);

        // Instansi lain tetap ditawarkan, tetapi tidak boleh ikut terpilih.
        Instansi::factory()->create(['nama' => 'Polsek Bone Pantai']);

        $halaman = $this->actingAs($admin)
            ->get("/penyaluran/{$penyaluran->id}/edit")
            ->assertOk();

        $isi = $halaman->getContent();

        // Keduanya muncul sebagai pilihan, tetapi hanya yang benar-benar
        // tercatat yang masuk ke daftar terpilih.
        $this->assertStringContainsString('PDAM Bone Bolango', $isi);
        $this->assertStringContainsString('Polsek Bone Pantai', $isi);
        $this->assertStringContainsString('Mulyonegoro', $isi);

        $terpilih = $this->antaraPenanda($isi, 'instansiTerpilih');
        $this->assertStringContainsString('PDAM Bone Bolango', $terpilih);
        $this->assertStringNotContainsString('Polsek Bone Pantai', $terpilih);
    }

    /**
     * Potongan JSON milik satu properti pada atribut `x-data`, dipakai untuk
     * memeriksa isi daftar terpilih tanpa ikut membaca daftar pilihan penuh.
     */
    private function antaraPenanda(string $isi, string $properti): string
    {
        $mulai = strpos($isi, $properti.':');

        $this->assertNotFalse($mulai, "Properti {$properti} tidak ditemukan pada halaman.");

        return substr($isi, $mulai, 600);
    }

    public function test_detail_penyaluran_terbuka_untuk_semua_role(): void
    {
        $penyaluran = Penyaluran::factory()->create(['user_id' => $this->admin()->id]);
        $penyaluran->desas()->attach($this->desa(nama: 'Tongo')->id);

        foreach ([User::factory()->petugas(), User::factory()->pimpinan()] as $pabrik) {
            $this->actingAs($pabrik->create())
                ->get("/penyaluran/{$penyaluran->id}")
                ->assertOk()
                ->assertSee('Tongo');
        }
    }

    public function test_kegiatan_serupa_diperingatkan_tetapi_tetap_boleh_disimpan(): void
    {
        $admin = $this->admin();
        $desa = $this->desa(nama: 'Tongo');
        $instansi = Instansi::factory()->create();

        $sudahAda = Penyaluran::factory()->padaTanggal('2026-08-12')->create(['user_id' => $admin->id]);
        $sudahAda->desas()->attach($desa->id);

        $isian = [
            'tanggal_penyaluran' => '2026-08-12',
            'desa_id' => [$desa->id],
            'instansi_id' => [$instansi->id],
            'volume_liter' => 5000,
        ];

        // Percobaan pertama hanya memunculkan peringatan, belum menyimpan.
        $this->actingAs($admin)
            ->post('/penyaluran', $isian)
            ->assertRedirect()
            ->assertSessionHas('duplikat');

        $this->assertDatabaseCount('penyalurans', 1);

        // Setelah admin menyetujui, data tetap boleh disimpan — satu desa
        // memang bisa menerima lebih dari satu kegiatan pada hari yang sama.
        $this->actingAs($admin)
            ->post('/penyaluran', $isian + ['konfirmasi_duplikat' => 1])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('penyalurans', 2);
    }

    public function test_endpoint_wilayah_bertingkat_mengikuti_induknya(): void
    {
        $kabupaten = Kabupaten::factory()->create();
        $kecamatan = Kecamatan::factory()->create(['kabupaten_id' => $kabupaten->id, 'nama' => 'Pulubala']);

        $this->desa($kecamatan, 'Mulyonegoro');
        Desa::factory()->nonaktif()->create(['kecamatan_id' => $kecamatan->id, 'nama' => 'Desa Lama']);

        // Kecamatan milik kabupaten lain tidak boleh ikut terbawa.
        Kecamatan::factory()->create(['nama' => 'Kecamatan Lain']);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->getJson('/options/kecamatan?kabupaten_id='.$kabupaten->id)
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['nama' => 'Pulubala']);

        // Tanpa induk, jawabannya kosong — bukan seluruh isi tabel.
        $this->actingAs($admin)
            ->getJson('/options/kecamatan')
            ->assertOk()
            ->assertJsonCount(0);

        // Form input hanya menawarkan desa aktif; panel filter tetap memuat
        // desa nonaktif agar riwayat lama masih bisa dicari.
        $this->actingAs($admin)
            ->getJson('/options/desa?kecamatan_id='.$kecamatan->id.'&hanya_aktif=1')
            ->assertOk()
            ->assertJsonCount(1);

        $this->actingAs($admin)
            ->getJson('/options/desa?kecamatan_id='.$kecamatan->id)
            ->assertOk()
            ->assertJsonCount(2);
    }
}
