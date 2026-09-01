<?php

namespace App\Support;

use App\Models\Instansi;
use App\Models\Kabupaten;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Pencarian dan filter data penyaluran (FR-16, FR-17, FR-18).
 *
 * Dikumpulkan di satu tempat karena dipakai dua halaman sekaligus — Riwayat
 * Penyaluran dan Laporan — dan keduanya harus menyaring dengan cara yang persis
 * sama. Bentuk arraynya mengikuti `Penyaluran::scopeSaring()`, sehingga nilainya
 * dapat langsung dioper ke query maupun ke `RekapPenyaluran`.
 */
class FilterPenyaluran
{
    /**
     * @var array<int, string>
     */
    public const KUNCI = [
        'cari', 'tanggal_mulai', 'tanggal_akhir',
        'kabupaten_id', 'kecamatan_id', 'desa_id', 'instansi_id', 'user_id',
    ];

    /**
     * Nilai filter yang sedang aktif, dipakai query maupun tampilan form.
     *
     * @return array<string, string>
     */
    public static function dari(Request $request): array
    {
        return collect(self::KUNCI)
            ->mapWithKeys(fn (string $nama) => [$nama => trim((string) $request->query($nama, ''))])
            ->all();
    }

    /**
     * @param  array<string, string>  $filter
     */
    public static function aktif(array $filter): bool
    {
        return collect($filter)->contains(fn (string $nilai) => $nilai !== '');
    }

    /**
     * Pilihan untuk panel filter. Instansi dan pengguna nonaktif tetap
     * ditawarkan karena riwayat lama yang menyebutnya masih harus bisa dicari.
     *
     * @return array<string, mixed>
     */
    public static function opsi(): array
    {
        return [
            'opsiKabupaten' => Kabupaten::opsi(),
            'opsiInstansi' => Instansi::query()->orderBy('nama')->pluck('nama', 'id')->all(),
            'opsiPenginput' => User::query()->orderBy('name')->pluck('name', 'id')->all(),
        ];
    }
}
