<?php

namespace App\Http\Controllers;

use App\Http\Requests\Instansi\PerbaruiInstansiRequest;
use App\Http\Requests\Instansi\SimpanInstansiRequest;
use App\Models\Instansi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data Instansi Pelaksana — khusus Admin (FR-07).
 *
 * Daftar awal berisi instansi yang muncul pada dokumen operasional kantor,
 * tetapi admin bebas menambah instansi baru: di lapangan pelaksananya bisa
 * bertambah sewaktu-waktu (Polsek, PDAM, relawan, dan lainnya).
 *
 * Tidak ada penghapusan. Instansi yang tidak lagi terlibat cukup dinonaktifkan
 * agar riwayat penyaluran yang menyebutnya tetap utuh.
 */
class InstansiController extends Controller
{
    public function index(Request $request): View
    {
        $daftar = Instansi::query()
            ->cari($request->query('cari'))
            ->status($request->query('status'))
            ->withCount('penyalurans')
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('instansi.index', [
            'daftarInstansi' => $daftar,
            'cari' => (string) $request->query('cari', ''),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function create(): View
    {
        return view('instansi.create');
    }

    public function store(SimpanInstansiRequest $request): RedirectResponse
    {
        $instansi = Instansi::create($request->validated());

        return redirect()
            ->route('instansi.index')
            ->with('status', "Instansi {$instansi->nama} berhasil ditambahkan.");
    }

    public function edit(Instansi $instansi): View
    {
        return view('instansi.edit', [
            'instansi' => $instansi,
            'jumlahPenyaluran' => $instansi->penyalurans()->count(),
        ]);
    }

    public function update(PerbaruiInstansiRequest $request, Instansi $instansi): RedirectResponse
    {
        $instansi->update($request->validated());

        return redirect()
            ->route('instansi.index')
            ->with('status', "Data {$instansi->nama} berhasil diperbarui.");
    }

    /**
     * Mengaktifkan atau menonaktifkan instansi.
     */
    public function ubahStatus(Instansi $instansi): RedirectResponse
    {
        $instansi->update(['aktif' => ! $instansi->aktif]);

        $keterangan = $instansi->aktif
            ? 'diaktifkan kembali dan muncul lagi pada form penyaluran'
            : 'dinonaktifkan dan tidak lagi ditawarkan pada form penyaluran';

        return back()->with('status', "Instansi {$instansi->nama} berhasil {$keterangan}.");
    }
}
