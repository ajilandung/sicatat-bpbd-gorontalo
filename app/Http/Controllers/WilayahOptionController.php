<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Dua endpoint JSON internal untuk dropdown wilayah bertingkat (§7).
 *
 * Dipakai form input penyaluran dan panel filter riwayat. Keduanya berada di
 * belakang middleware `auth` — ini bukan API publik. Sengaja dibuat bertingkat
 * karena memuat 77 kecamatan dan 729 desa sekaligus ke satu halaman membuat
 * pilihan sulit ditelusuri, sementara 61 nama desa di Provinsi Gorontalo
 * dipakai lebih dari satu wilayah sehingga nama saja tidak cukup.
 */
class WilayahOptionController extends Controller
{
    /**
     * Kecamatan pada satu kabupaten/kota.
     */
    public function kecamatan(Request $request): JsonResponse
    {
        $kabupatenId = $request->query('kabupaten_id');

        // Tanpa induk yang jelas, jawabannya kosong — bukan seluruh isi tabel.
        if (blank($kabupatenId)) {
            return response()->json([]);
        }

        $daftar = Kecamatan::query()
            ->diKabupaten($kabupatenId)
            ->orderBy('nama')
            ->get(['id', 'nama'])
            ->map(fn (Kecamatan $kecamatan) => [
                'id' => $kecamatan->id,
                'nama' => $kecamatan->nama,
            ]);

        return response()->json($daftar);
    }

    /**
     * Desa/kelurahan pada satu kecamatan.
     *
     * Form input meminta `hanya_aktif=1` supaya wilayah yang sudah
     * dinonaktifkan tidak lagi ditawarkan. Panel filter sengaja tidak
     * memakainya: desa nonaktif tetap harus bisa dicari pada riwayat lama.
     */
    public function desa(Request $request): JsonResponse
    {
        $kecamatanId = $request->query('kecamatan_id');

        if (blank($kecamatanId)) {
            return response()->json([]);
        }

        $daftar = Desa::query()
            ->diKecamatan($kecamatanId)
            ->when($request->boolean('hanya_aktif'), fn ($query) => $query->aktif())
            ->orderBy('nama')
            ->get(['id', 'nama', 'jenis', 'aktif'])
            ->map(fn (Desa $desa) => [
                'id' => $desa->id,
                'nama' => $desa->namaLengkap(),
                'aktif' => (bool) $desa->aktif,
            ]);

        return response()->json($daftar);
    }
}
