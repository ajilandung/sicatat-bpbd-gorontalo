<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Menguji FR-01 (login) dan FR-02 (pembedaan hak akses).
 */
class AutentikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_dapat_diakses_tamu(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke sistem');
    }

    public function test_tamu_diarahkan_ke_login_saat_membuka_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_pengguna_dapat_masuk_menggunakan_username(): void
    {
        $user = User::factory()->create(['username' => 'admin']);

        $this->post('/login', [
            'login' => 'admin',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_pengguna_dapat_masuk_menggunakan_email(): void
    {
        $user = User::factory()->create(['email' => 'petugas@bpbd.test']);

        $this->post('/login', [
            'login' => 'petugas@bpbd.test',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
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

    public function test_pengguna_dapat_keluar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_dashboard_dapat_dibuka_setelah_masuk(): void
    {
        $user = User::factory()->create(['name' => 'Administrator']);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Administrator');
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
