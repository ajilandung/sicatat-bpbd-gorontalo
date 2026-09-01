<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Satu foto dokumentasi milik sebuah kegiatan penyaluran.
 *
 * Foto tidak menyimpan tanggalnya sendiri. Tanggal dokumentasi selalu diambil
 * dari kegiatan induknya, sehingga admin tidak perlu mengisi tanggal ulang saat
 * mengunggah dan foto tidak mungkin tercatat pada tanggal yang berbeda dari
 * kegiatannya — bahkan bila tanggal kegiatan dikoreksi setelah foto diunggah.
 */
#[Fillable(['penyaluran_id', 'user_id', 'path'])]
class FotoPenyaluran extends Model
{
    public const UPDATED_AT = null;

    /**
     * Disk penyimpanan berkas. `local` berada di storage/app/private, di luar
     * jangkauan web, sehingga foto hanya dapat dibuka lewat route yang menjaga
     * login — sama seperti data penyaluran lainnya.
     */
    public const DISK = 'local';

    protected $table = 'foto_penyalurans';

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function penyaluran(): BelongsTo
    {
        return $this->belongsTo(Penyaluran::class);
    }

    /**
     * Pengguna yang mengunggah foto ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tanggal dokumentasi, yaitu tanggal kegiatan induknya.
     */
    public function tanggal(): ?Carbon
    {
        return $this->penyaluran?->tanggal_penyaluran;
    }

    public function url(): string
    {
        return route('penyaluran.foto.tampil', $this);
    }

    /**
     * Menghapus berkas fisiknya. Dipanggil saat baris foto dihapus; berkas
     * yang sudah tidak dirujuk baris mana pun tidak ada gunanya disimpan.
     */
    public function hapusBerkas(): void
    {
        Storage::disk(self::DISK)->delete($this->path);
    }
}
