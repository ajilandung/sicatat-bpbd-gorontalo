<?php

namespace App\Models;

use App\Models\Concerns\DapatDicari;
use Database\Factories\KabupatenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['kode', 'nama', 'jenis'])]
class Kabupaten extends Model
{
    /** @use HasFactory<KabupatenFactory> */
    use DapatDicari, HasFactory;

    public const JENIS_KABUPATEN = 'kabupaten';

    public const JENIS_KOTA = 'kota';

    protected $table = 'kabupatens';

    /**
     * @return array<string, string>
     */
    public static function daftarJenis(): array
    {
        return [
            self::JENIS_KABUPATEN => 'Kabupaten',
            self::JENIS_KOTA => 'Kota',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function kolomPencarian(): array
    {
        return ['nama', 'kode'];
    }

    public function kecamatans(): HasMany
    {
        return $this->hasMany(Kecamatan::class);
    }

    /**
     * Desa di seluruh kecamatan pada kabupaten ini. Dipakai untuk menghitung
     * cakupan wilayah tanpa menyimpan kabupaten_id di tabel desas.
     */
    public function desas(): HasManyThrough
    {
        return $this->hasManyThrough(Desa::class, Kecamatan::class);
    }

    /**
     * Nama beserta sebutan wilayahnya, mis. "Kabupaten Bone Bolango"
     * atau "Kota Gorontalo".
     */
    public function namaLengkap(): string
    {
        return ($this->jenis === self::JENIS_KOTA ? 'Kota ' : 'Kabupaten ').$this->nama;
    }

    /**
     * Filter jenis wilayah: 'kabupaten' atau 'kota'. Nilai lain diabaikan.
     *
     * @param  Builder<Kabupaten>  $query
     */
    public function scopeJenis(Builder $query, ?string $jenis): void
    {
        $query->when(
            array_key_exists((string) $jenis, self::daftarJenis()),
            fn (Builder $query) => $query->where('jenis', $jenis),
        );
    }

    /**
     * Daftar untuk pilihan pada form dan filter, mis. "Kabupaten Boalemo".
     *
     * @return array<int, string>
     */
    public static function opsi(): array
    {
        return static::query()
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn (Kabupaten $kabupaten) => [$kabupaten->id => $kabupaten->namaLengkap()])
            ->all();
    }
}
