<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data Kabupaten/Kota — khusus Admin (FR-04).
 *
 * Hanya menampilkan daftar. Data kabupaten dan kota berasal dari sumber resmi
 * (ekspor PENTAGON) dan praktis tidak pernah berubah, sehingga tidak ada form
 * tambah maupun ubah di aplikasi. Perubahan wilayah tingkat ini dilakukan lewat
 * seeder agar kode wilayah resminya tetap terjaga.
 */
class KabupatenController extends Controller
{
    public function index(Request $request): View
    {
        $daftar = Kabupaten::query()
            ->cari($request->query('cari'))
            ->jenis($request->query('jenis'))
            ->withCount(['kecamatans', 'desas'])
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('wilayah.kabupaten.index', [
            'daftarKabupaten' => $daftar,
            'cari' => (string) $request->query('cari', ''),
            'jenis' => (string) $request->query('jenis', ''),
        ]);
    }
}
