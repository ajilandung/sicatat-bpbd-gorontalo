<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

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
        ];

        foreach ($akun as $data) {
            User::firstOrCreate(
                ['username' => $data['username']],
                $data + ['password' => 'password', 'aktif' => true]
            );
        }

        $this->command->warn('Akun awal dibuat dengan password "password" — wajib diganti sebelum sistem dipakai.');
    }
}
