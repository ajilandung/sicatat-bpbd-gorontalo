<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Satu kegiatan penyaluran bantuan air bersih.
 *
 * Satu kegiatan dapat mencakup beberapa desa dan dikerjakan beberapa
 * instansi sekaligus. Angka KK, jiwa, dan volume air berlaku untuk
 * seluruh desa pada kegiatan tersebut — persis seperti cara laporan
 * lapangan ditulis selama ini.
 *
 * `tanggal_penyaluran` adalah tanggal kegiatan terjadi, sedangkan `created_at`
 * adalah waktu data dimasukkan ke sistem. Keduanya kerap berbeda karena laporan
 * lapangan baru sampai ke admin belakangan, dan data susulan untuk tanggal yang
 * sudah lewat memang harus bisa ditambahkan. Karena itu setiap rekap, laporan,
 * filter, dan grafik dikelompokkan berdasarkan `tanggal_penyaluran`.
 */
#[Fillable([
    'tanggal_penyaluran',
    'user_id',
    'jumlah_kk',
    'jumlah_jiwa',
    'volume_liter',
    'keterangan',
])]
class Penyaluran extends Model
{
    use SoftDeletes;

    protected $table = 'penyalurans';

    protected function casts(): array
    {
        return [
            'tanggal_penyaluran' => 'date',
        ];
    }

    public function desas(): BelongsToMany
    {
        return $this->belongsToMany(Desa::class, 'desa_penyaluran');
    }

    public function instansis(): BelongsToMany
    {
        return $this->belongsToMany(Instansi::class, 'instansi_penyaluran');
    }

    /**
     * Pengguna yang menginput data ini (PRD 8.5 - Informasi Sistem).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Apakah angka pada kegiatan ini merupakan angka gabungan beberapa desa.
     */
    public function angkaGabungan(): bool
    {
        return $this->desas()->count() > 1;
    }

    /**
     * Volume air yang dibebankan ke satu desa. Karena laporan lapangan
     * hanya menyebut angka gabungan, volume dibagi rata ke seluruh desa
     * penerima pada kegiatan ini.
     */
    public function volumePerDesa(): float
    {
        $jumlahDesa = $this->desas()->count();

        return $jumlahDesa > 0 ? $this->volume_liter / $jumlahDesa : 0;
    }
}
