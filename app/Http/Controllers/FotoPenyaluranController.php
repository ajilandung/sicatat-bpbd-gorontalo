<?php

namespace App\Http\Controllers;

use App\Models\FotoPenyaluran;
use App\Models\Penyaluran;
use App\Models\RiwayatPenyaluran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Dokumentasi foto kegiatan penyaluran.
 *
 * Foto selalu diunggah ke sebuah kegiatan yang sudah tersimpan, lewat halaman
 * Detail Penyaluran — bukan lewat form tambah kegiatan. Karena itu foto tidak
 * pernah meminta tanggal: tanggalnya mengikuti `tanggal_penyaluran` milik
 * kegiatan induknya, sehingga laporan per periode cukup mengambil foto dari
 * kegiatan yang sudah tersaring.
 *
 * Menambah dan menghapus foto mengikuti aturan yang sama dengan mengoreksi
 * datanya (`PenyaluranPolicy::kelolaFoto`): admin atas seluruh kegiatan,
 * petugas atas kegiatan yang ia input sendiri. Melihat foto terbuka untuk
 * seluruh role yang login.
 */
class FotoPenyaluranController extends Controller
{
    /**
     * Lebar maksimal foto yang disimpan. Foto kamera ponsel biasanya 3000–4000
     * piksel dan berukuran beberapa megabita; 1600 piksel sudah lebih dari
     * cukup untuk dicetak dua kolom pada kertas A4, sementara berkas laporan
     * tetap ringan dibuka.
     */
    private const LEBAR_MAKS = 1600;

    /**
     * Menambahkan satu atau beberapa foto sekaligus ke sebuah kegiatan.
     */
    public function store(Request $request, Penyaluran $penyaluran): RedirectResponse
    {
        abort_if($penyaluran->trashed(), 404);

        // Petugas hanya boleh menambah foto pada kegiatan yang ia input sendiri.
        $this->authorize('kelolaFoto', $penyaluran);

        $request->validate([
            'foto' => ['required', 'array', 'max:10'],
            'foto.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], attributes: ['foto' => 'foto', 'foto.*' => 'foto']);

        $berkas = $request->file('foto');
        $sebelum = $penyaluran->fotos()->count();

        DB::transaction(function () use ($berkas, $penyaluran, $request, $sebelum) {
            foreach ($berkas as $satu) {
                $penyaluran->fotos()->create([
                    'user_id' => $request->user()->id,
                    'path' => $this->simpan($satu, $penyaluran),
                ]);
            }

            RiwayatPenyaluran::catat(
                $penyaluran,
                RiwayatPenyaluran::AKSI_FOTO_DITAMBAH,
                ['foto' => ['dari' => $sebelum, 'ke' => $sebelum + count($berkas)]],
                $request->user(),
            );
        });

        return redirect()
            ->route('penyaluran.show', $penyaluran)
            ->with('status', count($berkas) === 1
                ? 'Foto dokumentasi berhasil ditambahkan.'
                : count($berkas).' foto dokumentasi berhasil ditambahkan.');
    }

    /**
     * Menghapus satu foto yang salah unggah. Berkas ikut dihapus karena tidak
     * ada lagi yang merujuknya; data kegiatannya sendiri tidak tersentuh.
     */
    public function destroy(Request $request, FotoPenyaluran $foto): RedirectResponse
    {
        $penyaluran = $foto->penyaluran;

        $this->authorize('kelolaFoto', $penyaluran);

        DB::transaction(function () use ($foto, $penyaluran, $request) {
            $sebelum = $penyaluran->fotos()->count();

            $foto->hapusBerkas();
            $foto->delete();

            RiwayatPenyaluran::catat(
                $penyaluran,
                RiwayatPenyaluran::AKSI_FOTO_DIHAPUS,
                ['foto' => ['dari' => $sebelum, 'ke' => $sebelum - 1]],
                $request->user(),
            );
        });

        return redirect()
            ->route('penyaluran.show', $penyaluran)
            ->with('status', 'Foto dokumentasi berhasil dihapus.');
    }

    /**
     * Menyajikan berkas fotonya. Berkas disimpan di luar folder publik, jadi
     * satu-satunya jalan membukanya adalah lewat route ini yang tetap menjaga
     * login dan aturan akses data terhapus.
     */
    public function tampil(Request $request, FotoPenyaluran $foto): StreamedResponse
    {
        $penyaluran = $foto->penyaluran()->withTrashed()->first();

        abort_if($penyaluran?->trashed() && ! $request->user()->isAdmin(), 404);
        abort_unless(Storage::disk(FotoPenyaluran::DISK)->exists($foto->path), 404);

        return Storage::disk(FotoPenyaluran::DISK)->response($foto->path);
    }

    /**
     * Menyimpan satu berkas foto dan mengembalikan jalurnya.
     */
    private function simpan(UploadedFile $berkas, Penyaluran $penyaluran): string
    {
        $folder = 'dokumentasi/'.$penyaluran->getKey();
        $kecil = $this->kecilkan($berkas);

        if ($kecil === null) {
            return $berkas->store($folder, FotoPenyaluran::DISK);
        }

        $jalur = $folder.'/'.Str::random(40).'.jpg';

        Storage::disk(FotoPenyaluran::DISK)->put($jalur, $kecil);

        return $jalur;
    }

    /**
     * Mengecilkan foto ke lebar maksimal dan menyeragamkannya menjadi JPEG,
     * memakai GD bawaan PHP sehingga tidak perlu pustaka tambahan.
     *
     * Mengembalikan null bila GD tidak tersedia atau berkasnya tidak terbaca —
     * pada keadaan itu foto tetap disimpan apa adanya, karena kehilangan
     * dokumentasi lebih merugikan daripada berkas yang besar.
     */
    private function kecilkan(UploadedFile $berkas): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $isi = @file_get_contents($berkas->getRealPath());
        $asli = $isi === false ? false : @imagecreatefromstring($isi);

        if ($asli === false) {
            return null;
        }

        $asli = $this->tegakkan($asli, $berkas);

        $lebar = imagesx($asli);
        $tinggi = imagesy($asli);
        $skala = min(1, self::LEBAR_MAKS / max($lebar, 1));

        $lebarBaru = max(1, (int) round($lebar * $skala));
        $tinggiBaru = max(1, (int) round($tinggi * $skala));

        // Latar putih dulu, baru gambarnya disalin: PNG berlatar transparan
        // akan menjadi hitam pekat bila langsung disimpan sebagai JPEG.
        $kanvas = imagecreatetruecolor($lebarBaru, $tinggiBaru);
        imagefill($kanvas, 0, 0, imagecolorallocate($kanvas, 255, 255, 255));
        imagecopyresampled($kanvas, $asli, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);

        ob_start();
        imagejpeg($kanvas, null, 82);
        $hasil = ob_get_clean();

        imagedestroy($kanvas);
        imagedestroy($asli);

        return $hasil === false ? null : $hasil;
    }

    /**
     * Memutar foto sesuai penanda orientasi EXIF-nya.
     *
     * Kamera ponsel kerap menyimpan foto dalam posisi mendatar disertai
     * catatan "sebenarnya diputar 90°". GD mengabaikan catatan itu, sehingga
     * tanpa langkah ini foto potret akan tercetak miring pada laporan.
     */
    private function tegakkan(\GdImage $gambar, UploadedFile $berkas): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $gambar;
        }

        $exif = @exif_read_data($berkas->getRealPath());
        $sudut = match ($exif['Orientation'] ?? null) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($sudut === 0) {
            return $gambar;
        }

        $diputar = imagerotate($gambar, $sudut, 0);

        if ($diputar === false) {
            return $gambar;
        }

        imagedestroy($gambar);

        return $diputar;
    }
}
