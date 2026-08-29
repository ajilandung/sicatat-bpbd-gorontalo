<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Menguji penggantian password mandiri dan alur password sementara
 * pada login pertama.
 */
class UbahPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengguna_dapat_mengubah_passwordnya_sendiri(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/ubah-password', [
                'password_sekarang' => 'password',
                'password' => 'RahasiaBaru123',
                'password_confirmation' => 'RahasiaBaru123',
            ])
            ->assertRedirect('/dashboard/petugas')
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('RahasiaBaru123', $user->refresh()->password));
    }

    public function test_password_lama_yang_salah_ditolak(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/ubah-password', [
                'password_sekarang' => 'bukan-password',
                'password' => 'RahasiaBaru123',
                'password_confirmation' => 'RahasiaBaru123',
            ])
            ->assertSessionHasErrors('password_sekarang');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_konfirmasi_password_harus_sama(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/ubah-password', [
                'password_sekarang' => 'password',
                'password' => 'RahasiaBaru123',
                'password_confirmation' => 'BedaSendiri123',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_password_baru_tidak_boleh_sama_dengan_yang_lama(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/ubah-password', [
                'password_sekarang' => 'password',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_pemakai_password_sementara_dipaksa_ke_halaman_ubah_password(): void
    {
        $user = User::factory()->wajibGantiPassword()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/ubah-password');

        $this->actingAs($user)
            ->get('/ubah-password')
            ->assertOk()
            ->assertSee('Buat Password Baru');
    }

    public function test_penanda_password_sementara_hilang_setelah_diganti(): void
    {
        $user = User::factory()->wajibGantiPassword()->admin()->create();

        $this->actingAs($user)
            ->put('/ubah-password', [
                'password_sekarang' => 'password',
                'password' => 'RahasiaBaru123',
                'password_confirmation' => 'RahasiaBaru123',
            ])
            ->assertRedirect('/dashboard/admin');

        $this->assertFalse($user->refresh()->harus_ganti_password);

        $this->actingAs($user)->get('/dashboard/admin')->assertOk();
    }

    public function test_pemakai_password_sementara_tetap_dapat_keluar(): void
    {
        $user = User::factory()->wajibGantiPassword()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_tamu_tidak_dapat_membuka_halaman_ubah_password(): void
    {
        $this->get('/ubah-password')->assertRedirect('/login');
    }
}
