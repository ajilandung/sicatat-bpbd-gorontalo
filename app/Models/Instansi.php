<?php

namespace App\Models;

use App\Models\Concerns\DapatDicari;
use App\Models\Concerns\PunyaStatusAktif;
use Database\Factories\InstansiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nama', 'singkatan', 'alamat', 'telepon', 'aktif'])]
class Instansi extends Model
{
    /** @use HasFactory<InstansiFactory> */
    use DapatDicari, HasFactory, PunyaStatusAktif;

    protected $table = 'instansis';

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
        return ['nama', 'singkatan'];
    }

    public function penyalurans(): BelongsToMany
    {
        return $this->belongsToMany(Penyaluran::class, 'instansi_penyaluran');
    }

    /**
     * Singkatan dipakai di tabel dan laporan agar kolom tidak terlalu lebar.
     */
    public function namaRingkas(): string
    {
        return $this->singkatan ?: $this->nama;
    }
}
