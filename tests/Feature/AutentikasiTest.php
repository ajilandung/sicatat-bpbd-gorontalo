<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Menguji FR-01 (login) dan FR-02 (pembedaan hak akses).
 */
class AutentikasiTest extends TestCase
{
    use RefreshDatabase;

    private const VIDEO_LATAR = 'video/latar-login.mp4';

    public function test_halaman_login_dapat_diakses_tamu(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke Sistem')
            ->assertSee('Lupa password? Hubungi administrator sistem.');
    }

    public function test_halaman_login_tidak_menawarkan_pendaftaran_mandiri(): void
    {
        $halaman = $this->get('/login');

        $halaman->assertDontSee('Daftar</')
            ->assertDontSee('Register', false)
            ->assertDontSee('Google', false)
            ->assertDontSee('Facebook', false);
    }

    public function test_panel_identitas_memutar_video_latar_saat_berkasnya_tersedia(): void
    {
        $this->assertFileExists(public_path(self::VIDEO_LATAR), 'Berkas video latar login belum tersalin ke public/video.');

        $this->get('/login')
            ->assertOk()
            ->assertSee('data-video-latar', false)
            ->assertSee('video/latar-login.mp4', false);
    }

    public function test_panel_identitas_memasang_poster_sebelum_video_termuat(): void
    {
        // Tanpa poster, panel sempat gelap kosong selama video diunduh. Poster
        // dipasang sebagai latar wadahnya, bukan atribut `poster` pada <video>,
        // supaya tetap terlihat meski videonya sengaja tidak dimuat.
        $this->assertFileExists(public_path('video/latar-login.jpg'));

        $this->get('/login')
            ->assertOk()
            ->assertSee('video/latar-login.jpg', false)
            ->assertSee('background-image:', false);
    }

    public function test_video_latar_menyediakan_kendali_jeda(): void
    {
        // WCAG 2.2.2: gerakan yang berjalan sendiri lebih dari lima detik wajib
        // dapat dihentikan pengguna. Video latar berdurasi 15 detik dan berulang.
        $this->get('/login')
            ->assertOk()
            ->assertSee('data-kendali-video', false)
            ->assertSee('Jeda video latar');
    }

    public function test_tombol_tampilkan_password_dapat_dicapai_papan_ketik(): void
    {
        // WCAG 2.1.1: menampilkan password adalah fungsi tersendiri, jadi tombolnya
        // tidak boleh dikeluarkan dari urutan tab.
        $this->get('/login')
            ->assertOk()
            ->assertSee('Tampilkan password')
            ->assertDontSee('tabindex="-1"', false);
    }

    public function test_halaman_login_tetap_utuh_ketika_berkas_video_tidak_ada(): void
    {
        // Video latar hanya pelengkap. Pada server yang berkas videonya belum
        // ikut tersalin, halaman login wajib tetap tampil penuh — bukan berujung
        // galat atau menyisakan permintaan berkas yang gagal.
        $asli = public_path(self::VIDEO_LATAR);
        $titipan = $asli.'.titipan-pengujian';

        rename($asli, $titipan);

        try {
            $this->get('/login')
                ->assertOk()
                ->assertSee('Masuk ke Sistem')
                ->assertDontSee('data-video-latar', false)
                ->assertDontSee('data-kendali-video', false)
                ->assertDontSee('latar-login.mp4', false);
        } finally {
            rename($titipan, $asli);
        }
    }

    public function test_tamu_diarahkan_ke_login_saat_membuka_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_pengguna_dapat_masuk_menggunakan_username(): void
    {
        $user = User::factory()->create(['username' => 'petugas1']);

        $this->post('/login', [
            'login' => 'petugas1',
            'password' => 'password',
        ])->assertRedirect('/dashboard/petugas');

        $this->assertAuthenticatedAs($user);
    }

    public function test_pengguna_dapat_masuk_menggunakan_email(): void
    {
        $user = User::factory()->create(['email' => 'petugas@bpbd.test']);

        $this->post('/login', [
            'login' => 'petugas@bpbd.test',
            'password' => 'password',
        ])->assertRedirect('/dashboard/petugas');

        $this->assertAuthenticatedAs($user);
    }

    public function test_setiap_role_diarahkan_ke_dashboardnya_sendiri(): void
    {
        $tujuan = [
            'admin' => '/dashboard/admin',
            'petugas' => '/dashboard/petugas',
            'pimpinan' => '/dashboard/pimpinan',
        ];

        foreach ($tujuan as $role => $url) {
            $user = User::factory()->create(['role' => $role, 'username' => "akun-{$role}"]);

            $this->post('/login', [
                'login' => "akun-{$role}",
                'password' => 'password',
            ])->assertRedirect($url);

            $this->assertAuthenticatedAs($user);
            $this->post('/logout');
        }
    }

    public function test_waktu_login_terakhir_dicatat(): void
    {
        $user = User::factory()->create(['username' => 'pencatat']);

        $this->assertNull($user->last_login_at);

        $this->post('/login', ['login' => 'pencatat', 'password' => 'password']);

        $this->assertNotNull($user->refresh()->last_login_at);
    }

    public function test_password_salah_ditolak(): void
    {
        User::factory()->create(['username' => 'admin']);

        $this->post('/login', [
            'login' => 'admin',
            'password' => 'salah',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_dapat_masuk(): void
    {
        User::factory()->nonaktif()->create(['username' => 'mantan']);

        $this->post('/login', [
            'login' => 'mantan',
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_akun_yang_dinonaktifkan_saat_sesi_berjalan_langsung_dikeluarkan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect('/dashboard/petugas');

        $user->update(['aktif' => false]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_pengguna_dapat_keluar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_dashboard_dapat_dibuka_setelah_masuk(): void
    {
        $user = User::factory()->create(['name' => 'Administrator']);

        $this->actingAs($user)->get('/dashboard/petugas')
            ->assertOk()
            ->assertSee('Administrator');
    }

    public function test_setiap_halaman_dashboard_role_dapat_dirender(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/dashboard/admin')
            ->assertOk()->assertSee('Dashboard Admin');

        $this->actingAs(User::factory()->petugas()->create())->get('/dashboard/petugas')
            ->assertOk()->assertSee('Dashboard Petugas');

        $this->actingAs(User::factory()->pimpinan()->create())->get('/dashboard/pimpinan')
            ->assertOk()->assertSee('Dashboard Pimpinan');
    }

    public function test_menu_manajemen_pengguna_hanya_tampil_untuk_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/dashboard/admin')
            ->assertSee('Manajemen Pengguna');

        $this->actingAs(User::factory()->pimpinan()->create())->get('/dashboard/pimpinan')
            ->assertDontSee('Manajemen Pengguna');
    }

    public function test_dashboard_milik_role_lain_ditolak(): void
    {
        $this->actingAs(User::factory()->petugas()->create())
            ->get('/dashboard/admin')
            ->assertForbidden();

        $this->actingAs(User::factory()->pimpinan()->create())
            ->get('/dashboard/admin')
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard/pimpinan')
            ->assertForbidden();
    }

    public function test_password_disimpan_dalam_bentuk_hash(): void
    {
        $user = User::factory()->create();

        $this->assertNotSame('password', $user->password);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_route_khusus_admin_menolak_role_lain(): void
    {
        Route::middleware(['web', 'auth', 'role:admin'])
            ->get('/uji-khusus-admin', fn () => 'ok');

        $this->actingAs(User::factory()->admin()->create())
            ->get('/uji-khusus-admin')
            ->assertOk();

        $this->actingAs(User::factory()->pimpinan()->create())
            ->get('/uji-khusus-admin')
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->get('/uji-khusus-admin')
            ->assertForbidden();
    }
}
