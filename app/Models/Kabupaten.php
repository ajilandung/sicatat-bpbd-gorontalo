<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kode', 'nama', 'jenis'])]
class Kabupaten extends Model
{
    protected $table = 'kabupatens';

    public function kecamatans(): HasMany
    {
        return $this->hasMany(Kecamatan::class);
    }

    /**
     * Nama beserta sebutan wilayahnya, mis. "Kabupaten Bone Bolango"
     * atau "Kota Gorontalo".
     */
    public function namaLengkap(): string
    {
        return ($this->jenis === 'kota' ? 'Kota ' : 'Kabupaten ').$this->nama;
    }
}
