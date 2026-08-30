<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wilayah\PerbaruiDesaRequest;
use App\Http\Requests\Wilayah\SimpanDesaRequest;
use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data Desa/Kelurahan — khusus Admin (FR-06).
 *
 * Satu-satunya tingkat wilayah yang dapat ditambah dan diubah lewat aplikasi,
 * karena pemekaran desa dan perbaikan ejaan nama memang sesekali terjadi.
 * Tidak ada penghapusan: desa yang tidak lagi dipakai cukup dinonaktifkan
 * supaya riwayat penyaluran yang menyebutnya tetap utuh.
 */
class DesaController extends Controller
{
    public function index(Request $request): View
    {
        $daftar = Desa::query()
            ->cari($request->query('cari'))
            ->diKabupaten($request->query('kabupaten_id'))
            ->diKecamatan($request->query('kecamatan_id'))
            ->status($request->query('status'))
            ->with('kecamatan.kabupaten')
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('wilayah.desa.index', [
            'daftarDesa' => $daftar,
            'opsiKabupaten' => Kabupaten::opsi(),
            'opsiKecamatan' => Kecamatan::opsiPerKabupaten(),
            'cari' => (string) $request->query('cari', ''),
            'kabupatenId' => (string) $request->query('kabupaten_id', ''),
            'kecamatanId' => (string) $request->query('kecamatan_id', ''),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function create(): View
    {
        return view('wilayah.desa.create', [
            'opsiKecamatan' => Kecamatan::opsiPerKabupaten(),
        ]);
    }

    public function store(SimpanDesaRequest $request): RedirectResponse
    {
        $desa = Desa::create($request->validated());

        return redirect()
            ->route('wilayah.desa.index')
            ->with('status', "{$desa->namaLengkap()} berhasil ditambahkan.");
    }

    public function edit(Desa $desa): View
    {
        return view('wilayah.desa.edit', [
            'desa' => $desa->load('kecamatan.kabupaten'),
            'opsiKecamatan' => Kecamatan::opsiPerKabupaten(),
            'jumlahPenyaluran' => $desa->penyalurans()->count(),
        ]);
    }

    public function update(PerbaruiDesaRequest $request, Desa $desa): RedirectResponse
    {
        $desa->update($request->validated());

        return redirect()
            ->route('wilayah.desa.index')
            ->with('status', "{$desa->namaLengkap()} berhasil diperbarui.");
    }

    /**
     * Mengaktifkan atau menonaktifkan desa.
     */
    public function ubahStatus(Desa $desa): RedirectResponse
    {
        $desa->update(['aktif' => ! $desa->aktif]);

        $keterangan = $desa->aktif
            ? 'diaktifkan kembali dan muncul lagi pada form penyaluran'
            : 'dinonaktifkan dan tidak lagi ditawarkan pada form penyaluran';

        return back()->with('status', "{$desa->namaLengkap()} berhasil {$keterangan}.");
    }
}
