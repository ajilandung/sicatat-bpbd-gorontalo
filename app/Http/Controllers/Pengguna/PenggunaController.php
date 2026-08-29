<?php

namespace App\Http\Controllers\Pengguna;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengguna\PerbaruiPenggunaRequest;
use App\Http\Requests\Pengguna\SimpanPenggunaRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manajemen Pengguna — khusus Admin (FR-02).
 *
 * Route sudah dijaga middleware `role:admin`; pemeriksaan policy di sini
 * adalah lapis kedua sekaligus penjaga aturan yang bergantung objek.
 */
class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $pengguna = User::query()
            ->cari($request->query('cari'))
            ->role($request->query('role'))
            ->status($request->query('status'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('pengguna.index', [
            'daftarPengguna' => $pengguna,
            'cari' => (string) $request->query('cari', ''),
            'role' => (string) $request->query('role', ''),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('pengguna.create');
    }

    public function store(SimpanPenggunaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Akun baru selalu memakai password sementara: pengguna wajib
        // menggantinya saat login pertama sehingga admin tidak pernah
        // mengetahui password akhir milik pengguna.
        $data['harus_ganti_password'] = true;

        $pengguna = User::create($data);

        return redirect()
            ->route('pengguna.index')
            ->with('status', "Akun {$pengguna->name} berhasil dibuat. Sampaikan password sementara kepada yang bersangkutan.");
    }

    public function show(User $pengguna): View
    {
        $this->authorize('view', $pengguna);

        return view('pengguna.show', ['pengguna' => $pengguna]);
    }

    public function edit(User $pengguna): View
    {
        $this->authorize('update', $pengguna);

        return view('pengguna.edit', ['pengguna' => $pengguna]);
    }

    public function update(PerbaruiPenggunaRequest $request, User $pengguna): RedirectResponse
    {
        $pengguna->update($request->validated());

        return redirect()
            ->route('pengguna.index')
            ->with('status', "Data {$pengguna->name} berhasil diperbarui.");
    }

    /**
     * Mengaktifkan atau menonaktifkan akun.
     */
    public function ubahStatus(User $pengguna): RedirectResponse
    {
        $this->authorize('ubahStatus', $pengguna);

        $pengguna->update(['aktif' => ! $pengguna->aktif]);

        $keterangan = $pengguna->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('status', "Akun {$pengguna->name} berhasil {$keterangan}.");
    }
}
