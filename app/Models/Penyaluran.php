<?php

namespace App\Models;

use Database\Factories\PenyaluranFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

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
    /** @use HasFactory<PenyaluranFactory> */
    use HasFactory, SoftDeletes;

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
     * Riwayat perubahan data ini, terbaru lebih dulu (§9.3).
     */
    public function riwayats(): HasMany
    {
        return $this->hasMany(RiwayatPenyaluran::class)->latest('id');
    }

    /**
     * Apakah angka pada kegiatan ini merupakan angka gabungan beberapa desa.
     */
    public function angkaGabungan(): bool
    {
        return $this->desas->count() > 1;
    }

    /**
     * Volume air yang dibebankan ke satu desa. Karena laporan lapangan
     * hanya menyebut angka gabungan, volume dibagi rata ke seluruh desa
     * penerima pada kegiatan ini.
     */
    public function volumePerDesa(): float
    {
        $jumlahDesa = $this->desas->count();

        return $jumlahDesa > 0 ? $this->volume_liter / $jumlahDesa : 0;
    }

    /**
     * Rekaman isi data untuk keperluan riwayat perubahan. Desa dan instansi
     * dicatat sebagai nama, bukan id, supaya riwayat lama tetap terbaca
     * walaupun master datanya berubah nama di kemudian hari.
     *
     * @return array<string, mixed>
     */
    public function rekaman(): array
    {
        $this->loadMissing(['desas.kecamatan.kabupaten', 'instansis']);

        return [
            'tanggal_penyaluran' => $this->tanggal_penyaluran?->format('Y-m-d'),
            'jumlah_kk' => $this->jumlah_kk,
            'jumlah_jiwa' => $this->jumlah_jiwa,
            'volume_liter' => $this->volume_liter,
            'keterangan' => $this->keterangan,
            'desa' => $this->desas->map(fn (Desa $desa) => $desa->alamatWilayah())->sort()->values()->all(),
            'instansi' => $this->instansis->pluck('nama')->sort()->values()->all(),
        ];
    }

    /**
     * Kegiatan lain pada tanggal yang sama yang menyentuh salah satu desa
     * berikut. Dipakai untuk memperingatkan admin sebelum data disimpan.
     *
     * Duplikat sengaja tidak dilarang: satu desa memang bisa menerima lebih
     * dari satu kegiatan pada hari yang sama, dan data nyata menunjukkannya
     * (Desa Tongo menerima bantuan pada 1, 3, 7, 10, 12, dan 24 Agustus 2026).
     * Sistem hanya memberi tahu; keputusan tetap di tangan admin.
     *
     * @param  array<int, int|string>  $desaIds
     * @return Collection<int, Penyaluran>
     */
    public static function serupa(?string $tanggal, array $desaIds, ?int $kecualiId = null): Collection
    {
        if (blank($tanggal) || $desaIds === []) {
            return collect();
        }

        return static::query()
            ->whereDate('tanggal_penyaluran', $tanggal)
            ->whereHas('desas', fn (Builder $desa) => $desa->whereIn('desas.id', $desaIds))
            ->when($kecualiId, fn (Builder $query) => $query->whereKeyNot($kecualiId))
            ->with(['desas.kecamatan.kabupaten', 'instansis'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Pencarian bebas pada keterangan, nama desa penerima, dan nama instansi
     * pelaksana.
     *
     * @param  Builder<Penyaluran>  $query
     */
    public function scopeCari(Builder $query, ?string $kata): void
    {
        $kata = trim((string) $kata);

        $query->when($kata !== '', fn (Builder $query) => $query->where(
            fn (Builder $query) => $query
                ->where('keterangan', 'like', "%{$kata}%")
                ->orWhereHas('desas', fn (Builder $desa) => $desa->where('nama', 'like', "%{$kata}%"))
                ->orWhereHas('instansis', fn (Builder $instansi) => $instansi
                    ->where('nama', 'like', "%{$kata}%")
                    ->orWhere('singkatan', 'like', "%{$kata}%")),
        ));
    }

    /**
     * Rentang tanggal selalu dicocokkan ke `tanggal_penyaluran`, bukan
     * `created_at`, sehingga kegiatan yang datanya baru masuk beberapa hari
     * kemudian tetap muncul pada tanggal kejadiannya (§9.3).
     *
     * @param  Builder<Penyaluran>  $query
     */
    public function scopePeriode(Builder $query, ?string $mulai, ?string $akhir): void
    {
        $query
            ->when(filled($mulai), fn (Builder $query) => $query->whereDate('tanggal_penyaluran', '>=', $mulai))
            ->when(filled($akhir), fn (Builder $query) => $query->whereDate('tanggal_penyaluran', '<=', $akhir));
    }

    /**
     * @param  Builder<Penyaluran>  $query
     */
    public function scopeDiDesa(Builder $query, int|string|null $desaId): void
    {
        $query->when(filled($desaId), fn (Builder $query) => $query->whereHas(
            'desas',
            fn (Builder $desa) => $desa->where('desas.id', $desaId),
        ));
    }

    /**
     * @param  Builder<Penyaluran>  $query
     */
    public function scopeDiKecamatan(Builder $query, int|string|null $kecamatanId): void
    {
        $query->when(filled($kecamatanId), fn (Builder $query) => $query->whereHas(
            'desas',
            fn (Builder $desa) => $desa->where('kecamatan_id', $kecamatanId),
        ));
    }

    /**
     * Filter sampai tingkat kabupaten lewat relasi desa → kecamatan, karena
     * yang benar-benar disimpan hanya id desa (§7).
     *
     * @param  Builder<Penyaluran>  $query
     */
    public function scopeDiKabupaten(Builder $query, int|string|null $kabupatenId): void
    {
        $query->when(filled($kabupatenId), fn (Builder $query) => $query->whereHas(
            'desas',
            fn (Builder $desa) => $desa->whereHas(
                'kecamatan',
                fn (Builder $kecamatan) => $kecamatan->where('kabupaten_id', $kabupatenId),
            ),
        ));
    }

    /**
     * @param  Builder<Penyaluran>  $query
     */
    public function scopeDariInstansi(Builder $query, int|string|null $instansiId): void
    {
        $query->when(filled($instansiId), fn (Builder $query) => $query->whereHas(
            'instansis',
            fn (Builder $instansi) => $instansi->where('instansis.id', $instansiId),
        ));
    }

    /**
     * @param  Builder<Penyaluran>  $query
     */
    public function scopeDiinputOleh(Builder $query, int|string|null $userId): void
    {
        $query->when(filled($userId), fn (Builder $query) => $query->where('user_id', $userId));
    }

    /**
     * Seluruh filter halaman riwayat dikumpulkan di satu tempat (§9.1),
     * supaya halaman laporan dan export nanti memakai penyaringan yang sama
     * persis dan tidak ada dua versi rumus yang bisa berbeda hasilnya.
     *
     * @param  Builder<Penyaluran>  $query
     * @param  array<string, mixed>  $filter
     */
    public function scopeSaring(Builder $query, array $filter): void
    {
        $query
            ->cari($filter['cari'] ?? null)
            ->periode($filter['tanggal_mulai'] ?? null, $filter['tanggal_akhir'] ?? null)
            ->diKabupaten($filter['kabupaten_id'] ?? null)
            ->diKecamatan($filter['kecamatan_id'] ?? null)
            ->diDesa($filter['desa_id'] ?? null)
            ->dariInstansi($filter['instansi_id'] ?? null)
            ->diinputOleh($filter['user_id'] ?? null);
    }
}
