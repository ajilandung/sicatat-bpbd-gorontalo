<?php

namespace App\Http\Controllers;

use App\Models\Penyaluran;
use App\Support\FilterPenyaluran;
use App\Support\RekapPenyaluran;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Laporan dan export (FR-22, FR-23, FR-24).
 *
 * Halaman ini memakai filter dan perhitungan yang sama persis dengan Riwayat
 * Penyaluran dan dashboard — lewat `FilterPenyaluran` dan `RekapPenyaluran` —
 * supaya angka pada laporan resmi tidak pernah berbeda dengan angka yang
 * dilihat pengguna di layar.
 *
 * Bentuk halaman cetaknya mengikuti dokumen "Laporan Sementara Kejadian dan
 * Dampak Bencana" milik Pusdalops PB BPBD Provinsi Gorontalo: kop instansi,
 * info kejadian, tabel kegiatan per tanggal beserta total air tersalur, lalu
 * penutup dan tanda tangan. Bagian dokumen asli yang datanya tidak dipegang
 * sistem — kronologi, wilayah terdampak, kendala, sarana prasarana — sengaja
 * tidak dicetak agar laporan tidak memuat keterangan yang tidak dapat
 * dipertanggungjawabkan isinya.
 */
class LaporanController extends Controller
{
    /**
     * Kunci penyimpanan isian kop yang terakhir dipakai. Isian ini bukan data
     * kegiatan, hanya kenyamanan agar admin tidak mengetik ulang identitas yang
     * sama setiap kali mencetak.
     */
    private const KUNCI_IDENTITAS = 'laporan.identitas';

    public function index(Request $request): View
    {
        $filter = FilterPenyaluran::dari($request);
        $rekap = new RekapPenyaluran($filter);

        return view('laporan.index', [
            'filter' => $filter,
            'adaFilter' => FilterPenyaluran::aktif($filter),
            'rekap' => $rekap,
            'perTanggal' => $rekap->perTanggal(),
            'identitas' => $this->identitas(),
        ] + FilterPenyaluran::opsi());
    }

    /**
     * Halaman siap cetak. Berkas PDF-nya dihasilkan lewat dialog cetak
     * peramban ("Simpan sebagai PDF"), bukan pustaka pembuat PDF, supaya tata
     * letak yang tampil di layar benar-benar sama dengan yang tercetak dan
     * dapat diperiksa admin sebelum dicetak.
     */
    public function cetak(Request $request): View
    {
        $filter = FilterPenyaluran::dari($request);
        $rekap = new RekapPenyaluran($filter);

        return view('laporan.cetak', [
            'filter' => $filter,
            'adaFilter' => FilterPenyaluran::aktif($filter),
            'rekap' => $rekap,
            'perTanggal' => $rekap->perTanggal(),
            'identitas' => $this->simpanIdentitas($request),
            // Lampiran foto dapat dimatikan bila laporan hanya perlu angkanya.
            'lampiran' => $request->boolean('lampiran'),
        ]);
    }

    /**
     * Export Excel (FR-24) berupa CSV satu baris per kegiatan.
     *
     * Satu baris per kegiatan, bukan per desa, karena angka KK, jiwa, dan
     * volume air pada laporan lapangan memang berlaku gabungan untuk seluruh
     * desa dalam satu kegiatan. Memecahnya per desa akan membuat penjumlahan
     * di Excel menghitung volume yang sama berkali-kali.
     */
    public function excel(Request $request): StreamedResponse
    {
        $filter = FilterPenyaluran::dari($request);
        $berkas = 'laporan-penyaluran-'.now()->format('Ymd-Hi').'.csv';

        return response()->streamDownload(function () use ($filter) {
            $keluaran = fopen('php://output', 'w');

            // BOM supaya huruf beraksen tidak rusak di Excel, dan penanda
            // pemisah kolom supaya Excel tetap membaca koma sebagai pemisah
            // walaupun setelan wilayah komputernya memakai titik koma.
            fwrite($keluaran, "\u{FEFF}sep=,\n");

            fputcsv($keluaran, [
                'Tanggal Kegiatan', 'Kabupaten/Kota', 'Kecamatan', 'Desa/Kelurahan',
                'Jumlah KK', 'Jumlah Jiwa', 'Instansi Pelaksana', 'Volume (liter)',
                'Keterangan', 'Diinput Oleh', 'Waktu Input',
            ], escape: '');

            Penyaluran::query()
                ->saring($filter)
                ->with(['desas.kecamatan.kabupaten', 'instansis', 'user'])
                ->orderBy('tanggal_penyaluran')
                ->orderBy('id')
                ->chunk(200, function (Collection $kegiatan) use ($keluaran) {
                    foreach ($kegiatan as $penyaluran) {
                        fputcsv($keluaran, $this->barisExcel($penyaluran), escape: '');
                    }
                });

            fclose($keluaran);
        }, $berkas, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<int, string|int|null>
     */
    private function barisExcel(Penyaluran $penyaluran): array
    {
        $kecamatan = $penyaluran->desas
            ->map(fn ($desa) => $desa->kecamatan?->nama)
            ->filter()->unique()->sort()->implode('; ');

        return [
            $penyaluran->tanggal_penyaluran?->format('d/m/Y'),
            $penyaluran->kabupatenTersentuh()->implode('; '),
            $kecamatan,
            $penyaluran->desas->map->namaLengkap()->sort()->implode('; '),
            // Dibiarkan kosong, bukan diisi nol, karena banyak laporan lapangan
            // memang tidak mencantumkan jumlah warga — nol akan terbaca sebagai
            // "tidak ada warga terdampak".
            $penyaluran->jumlah_kk,
            $penyaluran->jumlah_jiwa,
            $penyaluran->instansis->pluck('nama')->sort()->implode('; '),
            $penyaluran->volume_liter,
            $penyaluran->keterangan,
            $penyaluran->user?->name,
            $penyaluran->created_at?->format('d/m/Y H:i'),
        ];
    }

    /**
     * Isian kop yang terakhir dipakai, jatuh kembali ke bawaan pada
     * `config/laporan.php` bila belum pernah ada laporan yang dicetak.
     *
     * @return array<string, mixed>
     */
    private function identitas(): array
    {
        return Cache::get(self::KUNCI_IDENTITAS, []) + config('laporan.identitas');
    }

    /**
     * @return array<string, mixed>
     */
    private function simpanIdentitas(Request $request): array
    {
        $diisi = $request->validate([
            'jenis_bencana' => ['nullable', 'string', 'max:100'],
            'tanggal_kejadian' => ['nullable', 'date', 'before_or_equal:today'],
            'waktu_kejadian' => ['nullable', 'string', 'max:50'],
            'lokasi_kejadian' => ['nullable', 'string', 'max:255'],
            'update_ke' => ['nullable', 'string', 'max:20'],
            'penandatangan_jabatan' => ['nullable', 'string', 'max:100'],
            'penandatangan_nama' => ['nullable', 'string', 'max:100'],
            'penandatangan_pangkat' => ['nullable', 'string', 'max:100'],
            'penandatangan_nip' => ['nullable', 'string', 'max:30'],
        ]);

        $identitas = collect($diisi)->map(fn ($nilai) => is_string($nilai) ? trim($nilai) : $nilai)->all()
            + $this->identitas();

        Cache::forever(self::KUNCI_IDENTITAS, $identitas);

        return $identitas;
    }
}
