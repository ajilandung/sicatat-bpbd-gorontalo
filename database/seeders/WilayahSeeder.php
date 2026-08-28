<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

/**
 * Mengisi data wilayah Provinsi Gorontalo dari berkas
 * database/data/wilayah-gorontalo.csv.
 *
 * Sumber data: ekspor PENTAGON "Jumlah Penduduk Berdasarkan Jenis Kelamin"
 * (28 Agustus 2026), yang memuat daftar kabupaten/kota, kecamatan, dan
 * desa/kelurahan beserta kode wilayahnya.
 */
class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/wilayah-gorontalo.csv');

        if (! is_file($path)) {
            $this->command->error("Berkas data wilayah tidak ditemukan: {$path}");

            return;
        }

        $handle = fopen($path, 'r');
        fgetcsv($handle); // lewati baris header

        $kabupatenIds = [];
        $kecamatanIds = [];
        $jumlahDesa = 0;

        while (($row = fgetcsv($handle)) !== false) {
            [$kabKode, $kabNama, $kecKode, $kecNama, $desaKode, $desaNama] = $row;

            if (! isset($kabupatenIds[$kabKode])) {
                $kabupatenIds[$kabKode] = Kabupaten::firstOrCreate(
                    ['kode' => $kabKode],
                    [
                        'nama' => $this->rapikan($this->tanpaSebutan($kabNama)),
                        'jenis' => str_starts_with(strtoupper($kabNama), 'KOTA') ? 'kota' : 'kabupaten',
                    ]
                )->id;
            }

            if (! isset($kecamatanIds[$kecKode])) {
                $kecamatanIds[$kecKode] = Kecamatan::firstOrCreate(
                    ['kode' => $kecKode],
                    [
                        'kabupaten_id' => $kabupatenIds[$kabKode],
                        'nama' => $this->rapikan($kecNama),
                    ]
                )->id;
            }

            Desa::firstOrCreate(
                ['kode' => $desaKode],
                [
                    'kecamatan_id' => $kecamatanIds[$kecKode],
                    'nama' => $this->rapikan($desaNama),
                    'jenis' => $this->jenisDesa($desaKode),
                ]
            );

            $jumlahDesa++;
        }

        fclose($handle);

        $this->command->info(sprintf(
            'Wilayah: %d kabupaten/kota, %d kecamatan, %d desa/kelurahan.',
            count($kabupatenIds),
            count($kecamatanIds),
            $jumlahDesa
        ));
    }

    /**
     * Membuang sebutan "KABUPATEN"/"KOTA" dari nama, karena sudah
     * diwakili oleh kolom jenis.
     */
    private function tanpaSebutan(string $nama): string
    {
        return preg_replace('/^(KABUPATEN|KOTA)\s+/i', '', trim($nama));
    }

    /**
     * Data sumber ditulis dalam huruf kapital semua. Diubah menjadi
     * kapital di awal kata agar enak dibaca, dengan dua pengecualian:
     * angka romawi tetap kapital (Bongo II), dan ejaan "PAHUWATO" pada
     * data sumber dikembalikan ke ejaan resmi "Pohuwato".
     */
    private function rapikan(string $nama): string
    {
        $nama = trim($nama);

        if (strtoupper($nama) === 'PAHUWATO') {
            return 'Pohuwato';
        }

        $kata = preg_split('/\s+/', mb_strtolower($nama, 'UTF-8'));

        $hasil = array_map(function (string $k): string {
            if (preg_match('/^[ivxlcdm]+$/', $k)) {
                return mb_strtoupper($k, 'UTF-8');
            }

            return mb_strtoupper(mb_substr($k, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($k, 1, null, 'UTF-8');
        }, $kata);

        return implode(' ', $hasil);
    }

    /**
     * Pada kode wilayah Kemendagri, empat digit terakhir yang diawali 1
     * menandakan kelurahan, sedangkan 2 menandakan desa.
     */
    private function jenisDesa(string $kode): string
    {
        $akhir = substr($kode, -4);

        return str_starts_with($akhir, '1') ? 'kelurahan' : 'desa';
    }
}
