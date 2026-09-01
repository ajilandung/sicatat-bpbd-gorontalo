<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Satu baris riwayat perubahan pada data penyaluran (Technical Architecture §9.3).
 *
 * Data penyaluran boleh dikoreksi belakangan — laporan lapangan kerap baru
 * sampai ke admin beberapa hari setelah kegiatannya. Karena itu setiap
 * pembuatan, perubahan, penghapusan, dan pemulihan dicatat lengkap dengan
 * nilai sebelum dan sesudahnya, sehingga koreksi tetap dapat ditelusuri.
 *
 * Baris riwayat tidak pernah diubah setelah tercatat, jadi hanya `created_at`
 * yang dipakai.
 */
#[Fillable(['penyaluran_id', 'user_id', 'aksi', 'perubahan'])]
class RiwayatPenyaluran extends Model
{
    public const AKSI_DIBUAT = 'dibuat';

    public const AKSI_DIUBAH = 'diubah';

    public const AKSI_DIHAPUS = 'dihapus';

    public const AKSI_DIPULIHKAN = 'dipulihkan';

    public const AKSI_FOTO_DITAMBAH = 'foto_ditambah';

    public const AKSI_FOTO_DIHAPUS = 'foto_dihapus';

    public const UPDATED_AT = null;

    protected $table = 'riwayat_penyalurans';

    /**
     * Sebutan tiap aksi beserta warna lencananya di layar.
     *
     * @return array<string, array{label: string, warna: string}>
     */
    public static function daftarAksi(): array
    {
        return [
            self::AKSI_DIBUAT => ['label' => 'Dibuat', 'warna' => 'biru'],
            self::AKSI_DIUBAH => ['label' => 'Diubah', 'warna' => 'kuning'],
            self::AKSI_DIHAPUS => ['label' => 'Dihapus', 'warna' => 'merah'],
            self::AKSI_DIPULIHKAN => ['label' => 'Dipulihkan', 'warna' => 'hijau'],
            self::AKSI_FOTO_DITAMBAH => ['label' => 'Foto ditambahkan', 'warna' => 'biru'],
            self::AKSI_FOTO_DIHAPUS => ['label' => 'Foto dihapus', 'warna' => 'merah'],
        ];
    }

    /**
     * Sebutan tiap kolom pada tampilan riwayat. Kunci yang tidak terdaftar
     * ditampilkan apa adanya sehingga penambahan kolom baru tidak memutus
     * riwayat lama.
     *
     * @return array<string, string>
     */
    public static function daftarLabelKolom(): array
    {
        return [
            'tanggal_penyaluran' => 'Tanggal penyaluran',
            'jumlah_kk' => 'Jumlah KK',
            'jumlah_jiwa' => 'Jumlah jiwa',
            'volume_liter' => 'Volume air',
            'keterangan' => 'Keterangan',
            'desa' => 'Desa penerima',
            'instansi' => 'Instansi pelaksana',
            'foto' => 'Foto dokumentasi',
        ];
    }

    protected function casts(): array
    {
        return [
            'perubahan' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function penyaluran(): BelongsTo
    {
        return $this->belongsTo(Penyaluran::class);
    }

    /**
     * Pengguna yang melakukan perubahan ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mencatat satu peristiwa pada sebuah kegiatan penyaluran.
     *
     * @param  array<string, array{dari: mixed, ke: mixed}>  $perubahan
     */
    public static function catat(
        Penyaluran $penyaluran,
        string $aksi,
        array $perubahan = [],
        ?User $pelaku = null,
    ): self {
        return self::create([
            'penyaluran_id' => $penyaluran->getKey(),
            'user_id' => ($pelaku ?? auth()->user())?->getKey(),
            'aksi' => $aksi,
            'perubahan' => $perubahan === [] ? null : $perubahan,
        ]);
    }

    /**
     * Membandingkan dua rekaman data penyaluran dan mengembalikan hanya
     * kolom yang benar-benar berubah, lengkap dengan nilai sebelum-sesudah.
     *
     * @param  array<string, mixed>  $sebelum
     * @param  array<string, mixed>  $sesudah
     * @return array<string, array{dari: mixed, ke: mixed}>
     */
    public static function selisih(array $sebelum, array $sesudah): array
    {
        $selisih = [];

        foreach ($sesudah as $kolom => $nilaiBaru) {
            $nilaiLama = $sebelum[$kolom] ?? null;

            if ($nilaiLama !== $nilaiBaru) {
                $selisih[$kolom] = ['dari' => $nilaiLama, 'ke' => $nilaiBaru];
            }
        }

        return $selisih;
    }

    public function labelAksi(): string
    {
        return self::daftarAksi()[$this->aksi]['label'] ?? ucfirst((string) $this->aksi);
    }

    public function warnaAksi(): string
    {
        return self::daftarAksi()[$this->aksi]['warna'] ?? 'abu';
    }

    /**
     * Perubahan dalam bentuk siap tampil: label kolom beserta nilai lama dan
     * baru yang sudah diformat sebagai teks.
     *
     * @return array<int, array{label: string, dari: string, ke: string}>
     */
    public function daftarPerubahan(): array
    {
        $label = self::daftarLabelKolom();

        return collect($this->perubahan ?? [])
            ->map(fn (array $nilai, string $kolom) => [
                'label' => $label[$kolom] ?? $kolom,
                'dari' => self::format($kolom, $nilai['dari'] ?? null),
                'ke' => self::format($kolom, $nilai['ke'] ?? null),
            ])
            ->values()
            ->all();
    }

    /**
     * Mengubah nilai mentah pada riwayat menjadi teks yang enak dibaca.
     */
    private static function format(string $kolom, mixed $nilai): string
    {
        if ($nilai === null || $nilai === '' || $nilai === []) {
            return '—';
        }

        if (is_array($nilai)) {
            return implode(', ', $nilai);
        }

        return match ($kolom) {
            'tanggal_penyaluran' => Carbon::parse((string) $nilai)->translatedFormat('j F Y'),
            'volume_liter' => number_format((float) $nilai, 0, ',', '.').' liter',
            'jumlah_kk' => number_format((float) $nilai, 0, ',', '.').' KK',
            'jumlah_jiwa' => number_format((float) $nilai, 0, ',', '.').' jiwa',
            'foto' => number_format((float) $nilai, 0, ',', '.').' foto',
            default => (string) $nilai,
        };
    }
}
