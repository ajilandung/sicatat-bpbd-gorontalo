<?php

namespace App\Http\Controllers;

use App\Http\Requests\Penyaluran\PerbaruiPenyaluranRequest;
use App\Http\Requests\Penyaluran\SimpanPenyaluranRequest;
use App\Models\Desa;
use App\Models\Instansi;
use App\Models\Kabupaten;
use App\Models\Penyaluran;
use App\Models\RiwayatPenyaluran;
use App\Support\FilterPenyaluran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Modul Penyaluran — inti sistem (FR-08 sampai FR-18).
 *
 * Daftar dan detail terbuka untuk seluruh role; menambah, mengubah, menghapus,
 * dan memulihkan data hanya untuk admin. Penghapusan memakai *soft delete*
 * karena data ini adalah satu-satunya catatan kegiatan penyaluran, sehingga
 * kesalahan hapus masih dapat dipulihkan lewat halaman Data Terhapus.
 *
 * Setiap perubahan dicatat pada riwayat perubahan (§9.3): data historis boleh
 * dikoreksi kapan saja, dan koreksi seperti itu harus tetap dapat ditelusuri.
 */
class PenyaluranController extends Controller
{
    /**
     * Riwayat penyaluran beserta pencarian dan filternya
     * (FR-15, FR-16, FR-17, FR-18).
     */
    public function index(Request $request): View
    {
        $filter = FilterPenyaluran::dari($request);

        $daftar = Penyaluran::query()
            ->saring($filter)
            ->with(['desas.kecamatan.kabupaten', 'instansis', 'user'])
            ->orderByDesc('tanggal_penyaluran')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('penyaluran.index', [
            'daftarPenyaluran' => $daftar,
            'filter' => $filter,
            'adaFilter' => FilterPenyaluran::aktif($filter),
        ] + FilterPenyaluran::opsi());
    }

    public function create(): View
    {
        return view('penyaluran.create', [
            'opsiKabupaten' => Kabupaten::opsi(),
            'daftarInstansi' => $this->instansiUntukForm(),
            'desaTerpilih' => $this->desaTerpilih(),
        ]);
    }

    public function store(SimpanPenyaluranRequest $request): RedirectResponse
    {
        $serupa = Penyaluran::serupa($request->validated('tanggal_penyaluran'), $request->desaIds());

        if ($serupa->isNotEmpty() && ! $request->duplikatDikonfirmasi()) {
            return $this->mintaKonfirmasiDuplikat($serupa);
        }

        $penyaluran = DB::transaction(function () use ($request) {
            $penyaluran = Penyaluran::create(
                $request->dataPenyaluran() + ['user_id' => $request->user()->id],
            );

            $penyaluran->desas()->sync($request->desaIds());
            $penyaluran->instansis()->sync($request->instansiIds());

            RiwayatPenyaluran::catat(
                $penyaluran,
                RiwayatPenyaluran::AKSI_DIBUAT,
                [],
                $request->user(),
            );

            return $penyaluran;
        });

        return redirect()
            ->route('penyaluran.show', $penyaluran)
            ->with('status', 'Data penyaluran berhasil ditambahkan.');
    }

    public function show(Request $request, Penyaluran $penyaluran): View
    {
        // Data terhapus tetap dapat diperiksa admin sebelum dipulihkan,
        // tetapi tidak boleh muncul bagi role lain.
        abort_if($penyaluran->trashed() && ! $request->user()->isAdmin(), 404);

        $penyaluran->load(['desas.kecamatan.kabupaten', 'instansis', 'user']);

        // Riwayat perubahan hanya untuk admin: yang membacanya adalah pihak
        // yang juga berwenang mengoreksi datanya.
        $riwayats = $request->user()->isAdmin()
            ? $penyaluran->riwayats()->with('user')->get()
            : collect();

        return view('penyaluran.show', [
            'penyaluran' => $penyaluran,
            'riwayats' => $riwayats,
        ]);
    }

    public function edit(Penyaluran $penyaluran): View
    {
        $penyaluran->load(['desas.kecamatan.kabupaten', 'instansis']);

        return view('penyaluran.edit', [
            'penyaluran' => $penyaluran,
            'opsiKabupaten' => Kabupaten::opsi(),
            'daftarInstansi' => $this->instansiUntukForm($penyaluran),
            'desaTerpilih' => $this->desaTerpilih($penyaluran),
        ]);
    }

    public function update(PerbaruiPenyaluranRequest $request, Penyaluran $penyaluran): RedirectResponse
    {
        $serupa = Penyaluran::serupa(
            $request->validated('tanggal_penyaluran'),
            $request->desaIds(),
            $penyaluran->id,
        );

        if ($serupa->isNotEmpty() && ! $request->duplikatDikonfirmasi()) {
            return $this->mintaKonfirmasiDuplikat($serupa);
        }

        DB::transaction(function () use ($request, $penyaluran) {
            $sebelum = $penyaluran->rekaman();

            $penyaluran->update($request->dataPenyaluran());
            $penyaluran->desas()->sync($request->desaIds());
            $penyaluran->instansis()->sync($request->instansiIds());

            $penyaluran->refresh();

            $selisih = RiwayatPenyaluran::selisih($sebelum, $penyaluran->rekaman());

            // Menyimpan tanpa mengubah apa pun bukan peristiwa yang perlu
            // dicatat — riwayat hanya memuat perubahan yang benar-benar terjadi.
            if ($selisih !== []) {
                RiwayatPenyaluran::catat(
                    $penyaluran,
                    RiwayatPenyaluran::AKSI_DIUBAH,
                    $selisih,
                    $request->user(),
                );
            }
        });

        return redirect()
            ->route('penyaluran.show', $penyaluran)
            ->with('status', 'Data penyaluran berhasil diperbarui.');
    }

    /**
     * Menghapus data penyaluran (FR-10). Baris hanya ditandai terhapus,
     * sehingga masih dapat dipulihkan dari halaman Data Terhapus.
     */
    public function destroy(Request $request, Penyaluran $penyaluran): RedirectResponse
    {
        DB::transaction(function () use ($request, $penyaluran) {
            RiwayatPenyaluran::catat(
                $penyaluran,
                RiwayatPenyaluran::AKSI_DIHAPUS,
                [],
                $request->user(),
            );

            $penyaluran->delete();
        });

        return redirect()
            ->route('penyaluran.index')
            ->with('status', 'Data penyaluran dipindahkan ke Data Terhapus dan masih dapat dipulihkan.');
    }

    /**
     * Daftar data penyaluran yang sudah dihapus, khusus admin.
     */
    public function terhapus(Request $request): View
    {
        $daftar = Penyaluran::onlyTrashed()
            ->cari($request->query('cari'))
            ->with(['desas.kecamatan.kabupaten', 'instansis', 'user'])
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('penyaluran.terhapus', [
            'daftarPenyaluran' => $daftar,
            'cari' => (string) $request->query('cari', ''),
        ]);
    }

    /**
     * Mengembalikan data yang terlanjur dihapus.
     */
    public function pulihkan(Request $request, int $penyaluran): RedirectResponse
    {
        $data = Penyaluran::onlyTrashed()->findOrFail($penyaluran);

        DB::transaction(function () use ($request, $data) {
            $data->restore();

            RiwayatPenyaluran::catat(
                $data,
                RiwayatPenyaluran::AKSI_DIPULIHKAN,
                [],
                $request->user(),
            );
        });

        return redirect()
            ->route('penyaluran.show', $data)
            ->with('status', 'Data penyaluran berhasil dipulihkan.');
    }

    /**
     * Mengembalikan admin ke form dengan isian yang sama, disertai daftar
     * kegiatan serupa. Duplikat tidak dilarang — satu desa memang bisa
     * menerima lebih dari satu kegiatan pada hari yang sama — jadi sistem
     * hanya memastikan admin sudah melihatnya lebih dulu.
     *
     * @param  Collection<int, Penyaluran>  $serupa
     */
    private function mintaKonfirmasiDuplikat(Collection $serupa): RedirectResponse
    {
        return back()
            ->withInput()
            ->with('duplikat', $serupa->map(fn (Penyaluran $penyaluran) => [
                'tanggal' => $penyaluran->tanggal_penyaluran?->translatedFormat('j F Y'),
                'desa' => $penyaluran->desas->map->namaLengkap()->implode(', '),
                'instansi' => $penyaluran->instansis->map->namaRingkas()->implode(', '),
                'volume' => number_format((float) $penyaluran->volume_liter, 0, ',', '.').' liter',
                'url' => route('penyaluran.show', $penyaluran),
            ])->all());
    }

    /**
     * Desa yang sudah tercentang pada form, siap dipakai Alpine sebagai
     * daftar terpilih. Isian yang baru saja gagal divalidasi didahulukan
     * supaya pilihan admin tidak hilang saat form ditampilkan ulang.
     *
     * @return Collection<int, array{id: int, nama: string, wilayah: string}>
     */
    private function desaTerpilih(?Penyaluran $penyaluran = null): Collection
    {
        $ids = old('desa_id', $penyaluran?->desas->pluck('id')->all() ?? []);

        if (! is_array($ids) || $ids === []) {
            return collect();
        }

        return Desa::query()
            ->whereIn('id', $ids)
            ->with('kecamatan.kabupaten')
            ->orderBy('nama')
            ->get()
            ->map(fn (Desa $desa) => [
                'id' => $desa->id,
                'nama' => $desa->namaLengkap(),
                'wilayah' => $desa->alamatWilayah(),
            ])
            ->values();
    }

    /**
     * Instansi yang boleh dicentang pada form. Hanya yang aktif ditawarkan,
     * tetapi instansi nonaktif yang sudah terlanjur tercatat pada kegiatan
     * yang sedang diubah tetap ikut agar tidak hilang diam-diam saat disimpan.
     *
     * @return Collection<int, Instansi>
     */
    private function instansiUntukForm(?Penyaluran $penyaluran = null): Collection
    {
        return Instansi::query()
            ->where(function ($query) use ($penyaluran) {
                $query->where('aktif', true);

                if ($penyaluran) {
                    $query->orWhereIn('id', $penyaluran->instansis->pluck('id'));
                }
            })
            ->orderBy('nama')
            ->get();
    }
}
