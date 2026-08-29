<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun awal sistem. Tidak ada pendaftaran mandiri — akun berikutnya
 * dibuat administrator lewat menu Manajemen Pengguna.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $akun = [
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@bpbd.gorontaloprov.go.id',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Pimpinan BPBD',
                'username' => 'pimpinan',
                'email' => 'pimpinan@bpbd.gorontaloprov.go.id',
                'role' => User::ROLE_PIMPINAN,
            ],
            [
                'name' => 'Petugas Lapangan',
                'username' => 'petugas',
                'email' => 'petugas@bpbd.gorontaloprov.go.id',
                'role' => User::ROLE_PETUGAS,
            ],
        ];

        foreach ($akun as $data) {
            User::firstOrCreate(
                ['username' => $data['username']],
                $data + [
                    'password' => 'password',
                    'aktif' => true,
                    // Password awal bersifat sementara: sistem memaksa penggantian
                    // pada login pertama.
                    'harus_ganti_password' => true,
                ]
            );
        }

        $this->command->warn('Akun awal dibuat dengan password sementara "password" — sistem akan meminta penggantian saat login pertama.');
    }
}
