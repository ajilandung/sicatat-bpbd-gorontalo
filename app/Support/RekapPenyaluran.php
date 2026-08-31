<?php

namespace App\Support;

use App\Models\Desa;
use App\Models\Penyaluran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Seluruh perhitungan agregasi data penyaluran dikumpulkan di sini
 * (Technical Architecture §8, Database Schema §8).
 *
 * Alasannya satu: dashboard, halaman laporan, dan kedua export harus memakai
 * rumus yang sama persis. Bila masing-masing menghitung sendiri, cepat atau
 * lambat akan muncul dua angka berbeda untuk hal yang sama, dan angka pada
 * laporan resmi BPBD tidak boleh bergantung pada halaman mana yang dibuka.
 *
 * Kelas ini menerima filter dengan bentuk yang sama seperti
 * `Penyaluran::scopeSaring()`, sehingga halaman laporan pada fase berikutnya
 * cukup mengoper filter yang sedang aktif tanpa menulis ulang perhitungannya.
 * Dashboard memakainya tanpa filter, yaitu seluruh data.
 *
 * Seluruh pengelompokan memakai `tanggal_penyaluran` — tanggal kegiatan
 * terjadi — bukan `created_at`, supaya data susulan masuk ke bulan kejadiannya
 * (§9.3).
 */
class RekapPenyaluran
{
    /**
     * @param  array<string, mixed>  $filter
     */
    public function __construct(private array $filter = []) {}

    /**
     * Query dasar yang sudah tersaring. Baris terhapus otomatis dikecualikan
     * oleh soft delete pada model.
     *
     * @return Builder<Penyaluran>
     */
    private function dasar(): Builder
    {
        return Penyaluran::query()->saring($this->filter);
    }

    public function jumlahKegiatan(): int
    {
        return $this->dasar()->count();
    }

    /**
     * Total air tersalur dalam liter (FR-19).
     */
    public function totalVolume(): int
    {
        return (int) $this->dasar()->sum('volume_liter');
    }

    /**
     * Jumlah desa/kelurahan yang pernah menerima bantuan (FR-20).
     *
     * Dihitung dari tabel penghubung karena satu kegiatan dapat mencakup
     * beberapa desa, dan satu desa dapat menerima berkali-kali.
     */
    public function jumlahWilayahPenerima(): int
    {
        return DB::table('desa_penyaluran')
            ->whereIn('penyaluran_id', $this->dasar()->select('penyalurans.id'))
            ->distinct()
            ->count('desa_id');
    }

    /**
     * Jumlah kecamatan yang wilayahnya pernah menerima bantuan.
     *
     * Tabel penyaluran hanya menyimpan id desa (§7), jadi kecamatannya
     * ditelusuri lewat desa, lalu dihitung berbeda — satu kecamatan dengan
     * tiga desa penerima tetap dihitung satu.
     */
    public function jumlahKecamatanPenerima(): int
    {
        return DB::table('desa_penyaluran')
            ->join('desas', 'desas.id', '=', 'desa_penyaluran.desa_id')
            ->whereIn('desa_penyaluran.penyaluran_id', $this->dasar()->select('penyalurans.id'))
            ->distinct()
            ->count('desas.kecamatan_id');
    }

    /**
     * Rekap per kabupaten/kota untuk pewarnaan peta, dikunci menurut kode
     * wilayah Kemendagri supaya cocok dengan berkas batas wilayah.
     *
     * @return Collection<string, object{kode: string, nama: string, jenis: string, jumlah_kegiatan: int, jumlah_desa: int, total_liter: int}>
     */
    public function rekapKabupaten(): Collection
    {
        return DB::table('desa_penyaluran')
            ->join('desas', 'desas.id', '=', 'desa_penyaluran.desa_id')
            ->join('kecamatans', 'kecamatans.id', '=', 'desas.kecamatan_id')
            ->join('kabupatens', 'kabupatens.id', '=', 'kecamatans.kabupaten_id')
            ->join('penyalurans', 'penyalurans.id', '=', 'desa_penyaluran.penyaluran_id')
            ->whereIn('desa_penyaluran.penyaluran_id', $this->dasar()->select('penyalurans.id'))
            ->groupBy('kabupatens.kode', 'kabupatens.nama', 'kabupatens.jenis')
            ->select('kabupatens.kode', 'kabupatens.nama', 'kabupatens.jenis')
            ->selectRaw('COUNT(DISTINCT desa_penyaluran.penyaluran_id) as jumlah_kegiatan')
            ->selectRaw('COUNT(DISTINCT desa_penyaluran.desa_id) as jumlah_desa')
            ->get()
            ->keyBy('kode');
    }

    /**
     * Rentang tanggal kegiatan yang benar-benar ada datanya, dituliskan
     * seperti judul infografis BPBD: "Juli – Agustus 2026".
     *
     * Dipakai sebagai keterangan periode ketika dashboard menampilkan seluruh
     * data, karena rentang sesungguhnya lebih jujur daripada menulis
     * "seluruh waktu".
     */
    public function labelPeriode(): string
    {
        $baris = $this->dasar()
            ->selectRaw('MIN(tanggal_penyaluran) as mulai, MAX(tanggal_penyaluran) as akhir')
            ->first();

        if (! $baris?->mulai) {
            return 'Belum ada data';
        }

        $mulai = Carbon::parse($baris->mulai);
        $akhir = Carbon::parse($baris->akhir);

        if ($mulai->isSameMonth($akhir)) {
            return $this->namaBulan($mulai, panjang: true);
        }

        // Tahun cukup ditulis sekali bila keduanya berada di tahun yang sama.
        return $mulai->year === $akhir->year
            ? $this->namaBulan($mulai, panjang: true, tanpaTahun: true).' – '.$this->namaBulan($akhir, panjang: true)
            : $this->namaBulan($mulai, panjang: true).' – '.$this->namaBulan($akhir, panjang: true);
    }

    /**
     * Kegiatan yang terjadi pada bulan berjalan.
     */
    public function kegiatanBulanIni(): int
    {
        // Memakai ulang scope `periode` milik model, bukan perbandingan
        // sendiri, supaya pencocokan tanggalnya persis sama dengan yang
        // dipakai filter riwayat dan laporan.
        return $this->dasar()
            ->periode(
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            )
            ->count();
    }

    public function totalKk(): int
    {
        return (int) $this->dasar()->sum('jumlah_kk');
    }

    public function totalJiwa(): int
    {
        return (int) $this->dasar()->sum('jumlah_jiwa');
    }

    /**
     * Berapa kegiatan yang belum mencantumkan jumlah KK atau jiwa.
     *
     * Angka ini ditampilkan berdampingan dengan total KK dan jiwa, karena
     * kedua kolom itu boleh kosong: banyak entri laporan lapangan hanya
     * mencantumkan volume air. Tanpa penanda ini, pembaca akan mengira
     * totalnya sudah mencakup seluruh kegiatan.
     */
    public function kegiatanTanpaJumlahWarga(): int
    {
        return $this->dasar()
            ->where(fn (Builder $query) => $query
                ->whereNull('jumlah_kk')
                ->orWhereNull('jumlah_jiwa'))
            ->count();
    }

    /**
     * Volume air dan jumlah kegiatan per bulan untuk grafik (FR-21).
     *
     * Bulan tanpa kegiatan tetap dikembalikan bernilai nol supaya sumbu
     * grafik tidak melompat dan jeda musim kemarau terlihat apa adanya.
     *
     * @return Collection<int, array{bulan: string, label: string, total_liter: int, jumlah_kegiatan: int}>
     */
    public function volumePerBulan(int $jumlahBulan = 12): Collection
    {
        [$mulai, $jumlahBulan] = $this->rentangGrafik($jumlahBulan);

        $tercatat = $this->dasar()
            ->periode($mulai->toDateString(), null)
            ->selectRaw($this->kolomBulan().' as bulan')
            ->selectRaw('SUM(volume_liter) as total_liter')
            ->selectRaw('COUNT(*) as jumlah_kegiatan')
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        // Hasil di atas hanya memuat bulan yang ada datanya; deret bulannya
        // dibangun sendiri agar rentangnya selalu utuh.
        return collect(range(0, $jumlahBulan - 1))
            ->map(function (int $mundur) use ($mulai, $tercatat) {
                $bulan = $mulai->copy()->addMonths($mundur);
                $baris = $tercatat->get($bulan->format('Y-m'));

                return [
                    'bulan' => $bulan->format('Y-m'),
                    'label' => $this->namaBulan($bulan),
                    'judul' => $this->namaBulan($bulan, panjang: true),
                    'total_liter' => (int) ($baris->total_liter ?? 0),
                    'jumlah_kegiatan' => (int) ($baris->jumlah_kegiatan ?? 0),
                ];
            });
    }

    /**
     * Bulan mana saja yang digambar grafik.
     *
     * Tanpa filter: dua belas bulan terakhir. Bila dashboard sedang disaring
     * ke suatu periode, grafik mengikuti periode itu — memaksakan dua belas
     * bulan hanya akan menghasilkan deretan batang nol di luar filter.
     * Dibatasi 24 bulan supaya sumbunya tetap terbaca.
     *
     * @return array{0: Carbon, 1: int}
     */
    private function rentangGrafik(int $bawaan): array
    {
        $mulaiFilter = $this->filter['tanggal_mulai'] ?? null;

        if (blank($mulaiFilter)) {
            return [now()->startOfMonth()->subMonths($bawaan - 1), $bawaan];
        }

        $mulai = Carbon::parse($mulaiFilter)->startOfMonth();
        $akhir = Carbon::parse($this->filter['tanggal_akhir'] ?? null ?: now())->startOfMonth();

        return [$mulai, min(max((int) $mulai->diffInMonths($akhir) + 1, 1), 24)];
    }

    /**
     * Desa yang paling sering menerima bantuan (PRD 8.2).
     *
     * @return Collection<int, Desa>
     */
    public function wilayahTersering(int $batas = 5): Collection
    {
        return Desa::query()
            ->whereHas('penyalurans', fn (Builder $query) => $query->saring($this->filter))
            ->withCount([
                'penyalurans as jumlah_kegiatan' => fn (Builder $query) => $query->saring($this->filter),
            ])
            ->with('kecamatan.kabupaten')
            ->orderByDesc('jumlah_kegiatan')
            ->orderBy('nama')
            ->limit($batas)
            ->get();
    }

    /**
     * Kegiatan terbaru menurut tanggal kejadiannya.
     *
     * @return Collection<int, Penyaluran>
     */
    public function terbaru(int $batas = 5): Collection
    {
        return $this->dasar()
            ->with(['desas.kecamatan.kabupaten', 'instansis'])
            ->orderByDesc('tanggal_penyaluran')
            ->orderByDesc('id')
            ->limit($batas)
            ->get();
    }

    /**
     * Pengelompokan per bulan dilakukan di basis data, dan fungsi tanggalnya
     * berbeda antara MySQL yang dipakai aplikasi dan SQLite yang dipakai
     * pengujian. Hanya bagian ini yang perlu tahu perbedaannya.
     */
    private function kolomBulan(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', tanggal_penyaluran)"
            : "DATE_FORMAT(tanggal_penyaluran, '%Y-%m')";
    }

    /**
     * Nama bulan dalam bahasa Indonesia: ringkas untuk sumbu grafik
     * ("Ags 26") dan panjang untuk keterangan yang muncul saat kursor
     * diarahkan ke batangnya ("Agustus 2026").
     *
     * Ditulis sendiri, bukan lewat `translatedFormat`, karena locale aplikasi
     * masih `en` sehingga nama bulan bawaan Carbon berbahasa Inggris.
     */
    private function namaBulan(Carbon $bulan, bool $panjang = false, bool $tanpaTahun = false): string
    {
        $ringkas = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        $lengkap = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        if (! $panjang) {
            return $ringkas[$bulan->month - 1].' '.$bulan->format('y');
        }

        return $lengkap[$bulan->month - 1].($tanpaTahun ? '' : ' '.$bulan->format('Y'));
    }
}
