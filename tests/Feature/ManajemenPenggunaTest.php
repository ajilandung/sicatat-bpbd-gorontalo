<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Menguji Manajemen Pengguna: hanya Admin yang boleh mengelola akun (FR-02).
 */
class ManajemenPenggunaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_petugas_tidak_dapat_membuka_manajemen_pengguna(): void
    {
        $this->actingAs(User::factory()->petugas()->create())
            ->get('/pengguna')
            ->assertForbidden();
    }

    public function test_pimpinan_tidak_dapat_membuka_manajemen_pengguna(): void
    {
        $this->actingAs(User::factory()->pimpinan()->create())
            ->get('/pengguna')
            ->assertForbidden();
    }

    public function test_selain_admin_tidak_dapat_membuat_pengguna(): void
    {
        foreach ([User::factory()->petugas(), User::factory()->pimpinan()] as $pabrik) {
            $this->actingAs($pabrik->create())
                ->post('/pengguna', [
                    'name' => 'Penyusup',
                    'username' => 'penyusup',
                    'email' => 'penyusup@bpbd.test',
                    'role' => User::ROLE_ADMIN,
                    'password' => 'RahasiaBaru123',
                    'password_confirmation' => 'RahasiaBaru123',
                    'aktif' => 1,
                ])
                ->assertForbidden();
        }

        $this->assertDatabaseMissing('users', ['username' => 'penyusup']);
    }

    public function test_admin_dapat_membuka_daftar_pengguna(): void
    {
        User::factory()->create(['name' => 'Rahmat Hasan']);

        $this->actingAs($this->admin())
            ->get('/pengguna')
            ->assertOk()
            ->assertSee('Rahmat Hasan');
    }

    public function test_dialog_konfirmasi_punya_nama_yang_terbaca_dan_id_unik(): void
    {
        // Satu halaman daftar memuat banyak dialog sekaligus — satu per baris,
        // dikali dua karena ada tampilan tabel dan tampilan kartu. Bila id-nya
        // bertabrakan, aria-labelledby menunjuk judul milik baris lain dan
        // pembaca layar mengumumkan konfirmasi untuk pengguna yang keliru.
        User::factory()->count(3)->create();

        $halaman = $this->actingAs($this->admin())->get('/pengguna')->assertOk();

        $isi = $halaman->getContent();

        preg_match_all('/aria-labelledby="(konfirmasi-[^"]+)"/', $isi, $cocok);

        $this->assertNotEmpty($cocok[1], 'Dialog konfirmasi tidak memiliki aria-labelledby.');
        $this->assertSame(
            count($cocok[1]),
            count(array_unique($cocok[1])),
            'Ada id dialog konfirmasi yang dipakai lebih dari sekali.'
        );

        // Setiap nama yang dirujuk harus benar-benar ada sebagai id di halaman.
        foreach ($cocok[1] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $isi);
        }
    }

    public function test_sidebar_dikeluarkan_dari_urutan_tab_saat_tertutup_di_layar_kecil(): void
    {
        // Sidebar yang tertutup hanya digeser keluar layar dan tetap ada di DOM,
        // jadi tanpa penanda ini pengguna papan ketik di layar kecil bisa masuk
        // ke menu yang tidak terlihat olehnya.
        $this->actingAs($this->admin())
            ->get('/pengguna')
            ->assertOk()
            ->assertSee('x-effect="$el.inert = layarKecil', false);
    }

    public function test_daftar_pengguna_dapat_dicari_dan_difilter(): void
    {
        User::factory()->create(['name' => 'Rahmat Hasan', 'username' => 'rahmat']);
        User::factory()->pimpinan()->create(['name' => 'Sitti Ahmad', 'username' => 'sitti']);
        User::factory()->nonaktif()->create(['name' => 'Yusuf Lamada', 'username' => 'yusuf']);

        $admin = $this->admin();

        $this->actingAs($admin)->get('/pengguna?cari=rahmat')
            ->assertSee('Rahmat Hasan')
            ->assertDontSee('Sitti Ahmad');

        $this->actingAs($admin)->get('/pengguna?role='.User::ROLE_PIMPINAN)
            ->assertSee('Sitti Ahmad')
            ->assertDontSee('Rahmat Hasan');

        $this->actingAs($admin)->get('/pengguna?status=nonaktif')
            ->assertSee('Yusuf Lamada')
            ->assertDontSee('Rahmat Hasan');
    }

    public function test_admin_dapat_membuat_pengguna_baru(): void
    {
        $this->actingAs($this->admin())
            ->post('/pengguna', [
                'name' => 'Petugas Baru',
                'username' => 'petugas-baru',
                'email' => 'petugas.baru@bpbd.test',
                'role' => User::ROLE_PETUGAS,
                'password' => 'RahasiaBaru123',
                'password_confirmation' => 'RahasiaBaru123',
                'aktif' => 1,
            ])
            ->assertRedirect('/pengguna')
            ->assertSessionHas('status');

        $baru = User::where('username', 'petugas-baru')->firstOrFail();

        $this->assertSame(User::ROLE_PETUGAS, $baru->role);
        $this->assertTrue($baru->aktif);
        $this->assertTrue($baru->harus_ganti_password);
        $this->assertNotSame('RahasiaBaru123', $baru->password);
        $this->assertTrue(Hash::check('RahasiaBaru123', $baru->password));
    }

    public function test_username_dan_email_tidak_boleh_ganda(): void
    {
        $ada = User::factory()->create(['username' => 'rahmat', 'email' => 'rahmat@bpbd.test']);

        $this->actingAs($this->admin())
            ->post('/pengguna', [
                'name' => 'Rahmat Kedua',
                'username' => $ada->username,
                'email' => $ada->email,
                'role' => User::ROLE_PETUGAS,
                'password' => 'RahasiaBaru123',
                'password_confirmation' => 'RahasiaBaru123',
                'aktif' => 1,
            ])
            ->assertSessionHasErrors(['username', 'email']);
    }

    public function test_admin_dapat_mengubah_data_pengguna(): void
    {
        $pengguna = User::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($this->admin())
            ->put("/pengguna/{$pengguna->id}", [
                'name' => 'Nama Baru',
                'username' => $pengguna->username,
                'email' => $pengguna->email,
                'role' => User::ROLE_PIMPINAN,
                'aktif' => 1,
            ])
            ->assertRedirect('/pengguna');

        $pengguna->refresh();

        $this->assertSame('Nama Baru', $pengguna->name);
        $this->assertSame(User::ROLE_PIMPINAN, $pengguna->role);
    }

    public function test_admin_dapat_menonaktifkan_dan_mengaktifkan_akun(): void
    {
        $admin = $this->admin();
        $pengguna = User::factory()->create();

        $this->actingAs($admin)
            ->patch("/pengguna/{$pengguna->id}/status")
            ->assertRedirect();

        $this->assertFalse($pengguna->refresh()->aktif);

        $this->actingAs($admin)->patch("/pengguna/{$pengguna->id}/status");

        $this->assertTrue($pengguna->refresh()->aktif);
    }

    public function test_admin_tidak_dapat_menonaktifkan_akunnya_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch("/pengguna/{$admin->id}/status")
            ->assertForbidden();

        $this->assertTrue($admin->refresh()->aktif);
    }

    public function test_admin_tidak_dapat_mengubah_role_atau_status_akunnya_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put("/pengguna/{$admin->id}", [
                'name' => $admin->name,
                'username' => $admin->username,
                'email' => $admin->email,
                'role' => User::ROLE_PETUGAS,
                'aktif' => 0,
            ])
            ->assertSessionHasErrors(['role', 'aktif']);

        $admin->refresh();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->aktif);
    }

    public function test_admin_dapat_mereset_password_pengguna(): void
    {
        $pengguna = User::factory()->create();

        $this->actingAs($this->admin())
            ->put("/pengguna/{$pengguna->id}/reset-password", [
                'password' => 'PasswordSementara9',
                'password_confirmation' => 'PasswordSementara9',
            ])
            ->assertRedirect('/pengguna');

        $pengguna->refresh();

        $this->assertTrue(Hash::check('PasswordSementara9', $pengguna->password));
        $this->assertTrue($pengguna->harus_ganti_password);
    }

    public function test_admin_tidak_dapat_mereset_passwordnya_sendiri_dari_manajemen_pengguna(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put("/pengguna/{$admin->id}/reset-password", [
                'password' => 'PasswordSementara9',
                'password_confirmation' => 'PasswordSementara9',
            ])
            ->assertForbidden();
    }

    public function test_halaman_form_pengguna_dapat_dibuka_admin(): void
    {
        $admin = $this->admin();
        $pengguna = User::factory()->create(['name' => 'Rahmat Hasan']);

        $this->actingAs($admin)->get('/pengguna/create')
            ->assertOk()
            ->assertSee('Data Akun Baru');

        $this->actingAs($admin)->get("/pengguna/{$pengguna->id}/edit")
            ->assertOk()
            ->assertSee('Rahmat Hasan');

        $this->actingAs($admin)->get("/pengguna/{$pengguna->id}/reset-password")
            ->assertOk()
            ->assertSee('Reset password untuk Rahmat Hasan');
    }

    public function test_pilihan_role_dan_status_terkunci_hanya_pada_akun_sendiri(): void
    {
        $admin = $this->admin();
        $lain = User::factory()->create();

        $this->actingAs($admin)->get("/pengguna/{$admin->id}/edit")
            ->assertSee('disabled="disabled"', false)
            ->assertSee('Ini akun Anda sendiri');

        $this->actingAs($admin)->get("/pengguna/{$lain->id}/edit")
            ->assertDontSee('disabled="disabled"', false);
    }

    public function test_admin_dapat_melihat_detail_pengguna(): void
    {
        $pengguna = User::factory()->create(['name' => 'Rahmat Hasan']);

        $this->actingAs($this->admin())
            ->get("/pengguna/{$pengguna->id}")
            ->assertOk()
            ->assertSee('Rahmat Hasan')
            ->assertDontSee($pengguna->password);
    }
}
