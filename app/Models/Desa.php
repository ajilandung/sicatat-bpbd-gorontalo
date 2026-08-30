<?php

namespace App\Models;

use App\Models\Concerns\DapatDicari;
use App\Models\Concerns\PunyaStatusAktif;
use Database\Factories\DesaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['kecamatan_id', 'kode', 'nama', 'jenis', 'aktif'])]
class Desa extends Model
{
    /** @use HasFactory<DesaFactory> */
    use DapatDicari, HasFactory, PunyaStatusAktif;

    public const JENIS_DESA = 'desa';

    public const JENIS_KELURAHAN = 'kelurahan';

    protected $table = 'desas';

    /**
     * Sebutan wilayah tingkat desa. Dipakai untuk pilihan pada form dan filter.
     *
     * @return array<string, string>
     */
    public static function daftarJenis(): array
    {
        return [
            self::JENIS_DESA => 'Desa',
            self::JENIS_KELURAHAN => 'Kelurahan',
        ];
    }

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function kolomPencarian(): array
    {
        return ['nama', 'kode'];
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function penyalurans(): BelongsToMany
    {
        return $this->belongsToMany(Penyaluran::class, 'desa_penyaluran');
    }

    /**
     * Nama beserta sebutan wilayahnya, mis. "Desa Tongo".
     */
    public function namaLengkap(): string
    {
        return ($this->jenis === self::JENIS_KELURAHAN ? 'Kelurahan ' : 'Desa ').$this->nama;
    }

    /**
     * Alamat wilayah lengkap sampai kabupaten. Diperlukan karena 61 nama desa
     * di Provinsi Gorontalo tidak unik — nama saja tidak cukup untuk mengenali
     * desa yang dimaksud.
     */
    public function alamatWilayah(): string
    {
        $kecamatan = $this->kecamatan;

        if (! $kecamatan) {
            return $this->namaLengkap();
        }

        return $this->namaLengkap().', Kec. '.$kecamatan->nama
            .($kecamatan->kabupaten ? ', '.$kecamatan->kabupaten->namaLengkap() : '');
    }

    /**
     * @param  Builder<Desa>  $query
     */
    public function scopeDiKecamatan(Builder $query, int|string|null $kecamatanId): void
    {
        $query->when(
            filled($kecamatanId),
            fn (Builder $query) => $query->where('kecamatan_id', $kecamatanId),
        );
    }

    /**
     * Filter sampai tingkat kabupaten lewat relasi kecamatan, sehingga tidak
     * perlu menyimpan kabupaten_id yang bisa saling bertentangan.
     *
     * @param  Builder<Desa>  $query
     */
    public function scopeDiKabupaten(Builder $query, int|string|null $kabupatenId): void
    {
        $query->when(
            filled($kabupatenId),
            fn (Builder $query) => $query->whereHas(
                'kecamatan',
                fn (Builder $kecamatan) => $kecamatan->where('kabupaten_id', $kabupatenId),
            ),
        );
    }
}
