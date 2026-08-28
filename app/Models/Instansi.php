<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nama', 'singkatan', 'alamat', 'telepon', 'aktif'])]
class Instansi extends Model
{
    protected $table = 'instansis';

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function penyalurans(): BelongsToMany
    {
        return $this->belongsToMany(Penyaluran::class, 'instansi_penyaluran');
    }

    /**
     * Hanya instansi aktif yang boleh muncul di form penyaluran.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    /**
     * Singkatan dipakai di tabel dan laporan agar kolom tidak terlalu lebar.
     */
    public function namaRingkas(): string
    {
        return $this->singkatan ?: $this->nama;
    }
}
