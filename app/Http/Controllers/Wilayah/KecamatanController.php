<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data Kecamatan — khusus Admin (FR-05).
 *
 * Sama seperti kabupaten: daftar saja, tanpa form tambah atau ubah. Lihat
 * penjelasan di KabupatenController.
 */
class KecamatanController extends Controller
{
    public function index(Request $request): View
    {
        $daftar = Kecamatan::query()
            ->cari($request->query('cari'))
            ->diKabupaten($request->query('kabupaten_id'))
            ->with('kabupaten')
            ->withCount('desas')
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('wilayah.kecamatan.index', [
            'daftarKecamatan' => $daftar,
            'opsiKabupaten' => Kabupaten::opsi(),
            'cari' => (string) $request->query('cari', ''),
            'kabupatenId' => (string) $request->query('kabupaten_id', ''),
        ]);
    }
}
