<?php

namespace Database\Seeders;

use App\Models\Instansi;
use Illuminate\Database\Seeder;

/**
 * Instansi pelaksana yang benar-benar muncul pada dokumen operasional
 * "Update Kegiatan Penyaluran Bantuan Air Bersih 1-24 Agustus 2026" dan
 * "Laporan Sementara Distribusi Air Bersih, 25 Agustus 2026".
 * Selebihnya ditambahkan admin lewat menu Data Instansi.
 */
class InstansiSeeder extends Seeder
{
    public function run(): void
    {
        $instansi = [
            ['nama' => 'BPBD Provinsi Gorontalo', 'singkatan' => 'BPBD Provinsi'],
            ['nama' => 'BPBD Kabupaten Gorontalo', 'singkatan' => 'BPBD Kab. Gorontalo'],
            ['nama' => 'BPBD Kabupaten Bone Bolango', 'singkatan' => 'BPBD Bone Bolango'],
            ['nama' => 'BPBD Kabupaten Boalemo', 'singkatan' => 'BPBD Boalemo'],
            ['nama' => 'BPBD Kabupaten Gorontalo Utara', 'singkatan' => 'BPBD Gorontalo Utara'],
            ['nama' => 'BPBPK Provinsi Gorontalo', 'singkatan' => 'BPBPK'],
            ['nama' => 'PMI Provinsi Gorontalo', 'singkatan' => 'PMI Provinsi'],
            ['nama' => 'PMI Kabupaten Bone Bolango', 'singkatan' => 'PMI Bone Bolango'],
            ['nama' => 'Dinas Sosial Provinsi Gorontalo', 'singkatan' => 'Dinsos Provinsi'],
            ['nama' => 'Dinas Sosial Kabupaten Gorontalo', 'singkatan' => 'Dinsos Kab. Gorontalo'],
            ['nama' => 'PDAM Kabupaten Gorontalo', 'singkatan' => 'PDAM Kab. Gorontalo'],
            ['nama' => 'PDAM Kabupaten Bone Bolango', 'singkatan' => 'PDAM Bone Bolango'],
            ['nama' => 'Polsek Bone Pantai', 'singkatan' => 'Polsek Bone Pantai'],
            ['nama' => 'TNI-POLRI', 'singkatan' => 'TNI-POLRI'],
            ['nama' => 'BWS Gorontalo', 'singkatan' => 'BWS'],
            ['nama' => 'Balai Pemukiman Wilayah Gorontalo', 'singkatan' => 'Balai Pemukiman'],
        ];

        foreach ($instansi as $data) {
            Instansi::firstOrCreate(['nama' => $data['nama']], $data + ['aktif' => true]);
        }

        $this->command->info('Instansi pelaksana: '.count($instansi).' data.');
    }
}
