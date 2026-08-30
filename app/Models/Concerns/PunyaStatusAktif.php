<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Perilaku bersama untuk data master yang tidak pernah dihapus, melainkan
 * dinonaktifkan: desa dan instansi. Data nonaktif tetap utuh pada riwayat
 * penyaluran, hanya tidak lagi ditawarkan pada form input.
 */
trait PunyaStatusAktif
{
    /**
     * Pilihan untuk filter status pada halaman daftar.
     *
     * @return array<string, string>
     */
    public static function daftarStatus(): array
    {
        return [
            'aktif' => 'Aktif',
            'nonaktif' => 'Tidak Aktif',
        ];
    }

    /**
     * Hanya data aktif yang boleh muncul di form penyaluran.
     *
     * @param  Builder<static>  $query
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    /**
     * Filter status: 'aktif' atau 'nonaktif'. Nilai lain diabaikan sehingga
     * query string yang tidak dikenal tidak menyaring apa pun.
     *
     * @param  Builder<static>  $query
     */
    public function scopeStatus(Builder $query, ?string $status): void
    {
        $query->when(
            array_key_exists((string) $status, static::daftarStatus()),
            fn (Builder $query) => $query->where('aktif', $status === 'aktif'),
        );
    }
}
