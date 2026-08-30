<?php

namespace App\Models;

use App\Models\Concerns\DapatDicari;
use Database\Factories\KecamatanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kabupaten_id', 'kode', 'nama'])]
class Kecamatan extends Model
{
    /** @use HasFactory<KecamatanFactory> */
    use DapatDicari, HasFactory;

    protected $table = 'kecamatans';

    /**
     * @return array<int, string>
     */
    protected function kolomPencarian(): array
    {
        return ['nama', 'kode'];
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class);
    }

    public function desas(): HasMany
    {
        return $this->hasMany(Desa::class);
    }

    /**
     * @param  Builder<Kecamatan>  $query
     */
    public function scopeDiKabupaten(Builder $query, int|string|null $kabupatenId): void
    {
        $query->when(
            filled($kabupatenId),
            fn (Builder $query) => $query->where('kabupaten_id', $kabupatenId),
        );
    }

    /**
     * Pilihan kecamatan yang dikelompokkan per kabupaten. Dipakai form dan
     * filter desa: 77 kecamatan dalam satu daftar datar sulit ditelusuri, dan
     * ada nama kecamatan yang mirip antar kabupaten.
     *
     * @return array<string, array<int, string>>
     */
    public static function opsiPerKabupaten(): array
    {
        return static::query()
            ->with('kabupaten')
            ->orderBy('nama')
            ->get()
            ->groupBy(fn (Kecamatan $kecamatan) => $kecamatan->kabupaten?->namaLengkap() ?? 'Tanpa kabupaten')
            ->sortKeys()
            ->map(fn ($grup) => $grup->pluck('nama', 'id')->all())
            ->all();
    }
}
