<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['kecamatan_id', 'kode', 'nama', 'jenis'])]
class Desa extends Model
{
    protected $table = 'desas';

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
        return ($this->jenis === 'kelurahan' ? 'Kelurahan ' : 'Desa ').$this->nama;
    }
}
