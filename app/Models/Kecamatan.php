<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kabupaten_id', 'kode', 'nama'])]
class Kecamatan extends Model
{
    protected $table = 'kecamatans';

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class);
    }

    public function desas(): HasMany
    {
        return $this->hasMany(Desa::class);
    }
}
