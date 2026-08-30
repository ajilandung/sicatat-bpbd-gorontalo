<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Pencarian bebas pada beberapa kolom sekaligus, dipakai kotak "Cari" di
 * halaman daftar. Setiap model menentukan sendiri kolom mana yang dicari.
 */
trait DapatDicari
{
    /**
     * Kolom yang ikut dicari, mis. ['nama', 'kode'].
     *
     * @return array<int, string>
     */
    abstract protected function kolomPencarian(): array;

    /**
     * Kata kunci kosong tidak menyaring apa pun, sehingga scope ini aman
     * dirangkai walau kotak pencarian tidak diisi.
     *
     * @param  Builder<static>  $query
     */
    public function scopeCari(Builder $query, ?string $kata): void
    {
        $kata = trim((string) $kata);

        $query->when($kata !== '', function (Builder $query) use ($kata) {
            $query->where(function (Builder $query) use ($kata) {
                foreach ($this->kolomPencarian() as $kolom) {
                    $query->orWhere($kolom, 'like', "%{$kata}%");
                }
            });
        });
    }
}
