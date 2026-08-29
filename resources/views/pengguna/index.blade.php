@extends('layouts.app')

@section('judul', 'Manajemen Pengguna')

@section('konten')

    <x-ui.kepala-halaman
        judul="Manajemen Pengguna"
        deskripsi="Akun dibuat dan dikelola oleh administrator. Password yang dibuat di sini bersifat sementara — pengguna wajib menggantinya saat login pertama.">
        <x-slot:aksi>
            <x-ui.tombol :href="route('pengguna.create')">
                <x-ikon nama="plus" class="size-4"/>
                Tambah Pengguna
            </x-ui.tombol>
        </x-slot:aksi>
    </x-ui.kepala-halaman>

    @php $adaFilter = $cari !== '' || $role !== '' || $status !== ''; @endphp

    {{-- ── Pencarian dan filter ── --}}
    <form method="GET" action="{{ route('pengguna.index') }}"
          class="rounded-xl border border-slate-200 bg-white p-4 shadow-kartu">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_11rem_11rem_auto]">
            <div>
                <label for="cari" class="sr-only">Cari pengguna</label>
                <x-ui.input nama="cari" :nilai="$cari" ikon="cari" placeholder="Cari nama, username, atau email"/>
            </div>

            <div>
                <label for="role" class="sr-only">Filter role</label>
                <x-ui.pilihan nama="role" :nilai="$role" :opsi="App\Models\User::daftarRole()" kosong="Semua role"/>
            </div>

            <div>
                <label for="status" class="sr-only">Filter status</label>
                <x-ui.pilihan nama="status" :nilai="$status" :opsi="['aktif' => 'Aktif', 'nonaktif' => 'Tidak Aktif']" kosong="Semua status"/>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.tombol varian="sekunder" ukuran="lebar" class="flex-1 lg:flex-none">
                    <x-ikon nama="saring" class="size-4"/>
                    Terapkan
                </x-ui.tombol>

                @if ($adaFilter)
                    <x-ui.tombol-ikon :href="route('pengguna.index')" ikon="silang" label="Hapus filter" ukuran="besar"/>
                @endif
            </div>
        </div>
    </form>

    <p class="mt-4 text-sm text-slate-500">
        Menampilkan <span class="font-medium text-navy-900">{{ $daftarPengguna->count() }}</span>
        dari <span class="font-medium text-navy-900">{{ $daftarPengguna->total() }}</span> pengguna
        @if ($adaFilter)
            <span class="text-slate-400">(tersaring)</span>
        @endif
    </p>

    {{-- ── Tabel: layar sedang ke atas ── --}}
    <div class="mt-3 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-kartu md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">
                        <th scope="col" class="px-5 py-3">Pengguna</th>
                        <th scope="col" class="px-5 py-3">Role</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="hidden px-5 py-3 lg:table-cell">Terakhir Login</th>
                        <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarPengguna as $pengguna)
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :nama="$pengguna->name" ukuran="sm"
                                                 :warna="$pengguna->isAdmin() ? 'brand' : 'netral'"/>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-navy-900">{{ $pengguna->name }}</p>
                                        <p class="truncate text-xs text-slate-500">
                                            {{ $pengguna->username }} · {{ $pengguna->email }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-3.5">
                                <x-ui.lencana-role :role="$pengguna->role"/>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex flex-col items-start gap-1">
                                    <x-ui.lencana :warna="$pengguna->aktif ? 'hijau' : 'merah'">
                                        {{ $pengguna->aktif ? 'Aktif' : 'Tidak Aktif' }}
                                    </x-ui.lencana>

                                    @if ($pengguna->is(auth()->user()))
                                        <span class="text-[11px] text-slate-400">Akun Anda</span>
                                    @elseif ($pengguna->harus_ganti_password)
                                        <span class="text-[11px] text-amber-700">Password sementara</span>
                                    @endif
                                </div>
                            </td>

                            <td class="hidden px-5 py-3.5 text-slate-600 lg:table-cell">
                                {{ $pengguna->last_login_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <x-ui.tombol-ikon :href="route('pengguna.show', $pengguna)" ikon="mata" label="Lihat detail"/>
                                    <x-ui.tombol-ikon :href="route('pengguna.edit', $pengguna)" ikon="pensil" label="Edit pengguna"/>

                                    @can('resetPassword', $pengguna)
                                        <x-ui.tombol-ikon :href="route('pengguna.reset-password', $pengguna)" ikon="kunci"
                                                          label="Reset password" varian="aksen"/>
                                    @endcan

                                    @can('ubahStatus', $pengguna)
                                        @php
                                            $pesanStatus = $pengguna->aktif
                                                ? "Akun {$pengguna->name} tidak akan bisa masuk ke sistem sampai diaktifkan kembali."
                                                : "Akun {$pengguna->name} akan dapat masuk kembali ke sistem.";
                                        @endphp

                                        <x-ui.konfirmasi
                                            :aksi="route('pengguna.status', $pengguna)"
                                            :ikon="$pengguna->aktif ? 'gembok' : 'centang-bulat'"
                                            :label="$pengguna->aktif ? 'Nonaktifkan akun' : 'Aktifkan akun'"
                                            :varian="$pengguna->aktif ? 'bahaya' : 'netral'"
                                            :judul="$pengguna->aktif ? 'Nonaktifkan akun?' : 'Aktifkan akun?'"
                                            :pesan="$pesanStatus"
                                            :label-konfirmasi="$pengguna->aktif ? 'Ya, nonaktifkan' : 'Ya, aktifkan'"
                                            :varian-konfirmasi="$pengguna->aktif ? 'bahaya' : 'utama'"/>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.kosong judul="Pengguna tidak ditemukan"
                                             deskripsi="Ubah kata kunci pencarian atau filter yang dipakai.">
                                    @if ($adaFilter)
                                        <x-ui.tombol varian="sekunder" :href="route('pengguna.index')">Hapus filter</x-ui.tombol>
                                    @endif
                                </x-ui.kosong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($daftarPengguna->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $daftarPengguna->links() }}
            </div>
        @endif
    </div>

    {{-- ── Daftar kartu: layar kecil ── --}}
    <div class="mt-3 space-y-3 md:hidden">
        @forelse ($daftarPengguna as $pengguna)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-kartu">
                <div class="flex items-start gap-3">
                    <x-ui.avatar :nama="$pengguna->name" :warna="$pengguna->isAdmin() ? 'brand' : 'netral'"/>

                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-navy-900">{{ $pengguna->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $pengguna->username }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $pengguna->email }}</p>

                        <div class="mt-2.5 flex flex-wrap items-center gap-2">
                            <x-ui.lencana-role :role="$pengguna->role"/>
                            <x-ui.lencana :warna="$pengguna->aktif ? 'hijau' : 'merah'">
                                {{ $pengguna->aktif ? 'Aktif' : 'Tidak Aktif' }}
                            </x-ui.lencana>
                            @if ($pengguna->harus_ganti_password)
                                <x-ui.lencana warna="kuning">Password sementara</x-ui.lencana>
                            @endif
                        </div>

                        <p class="mt-2.5 text-xs text-slate-400">
                            Terakhir login: {{ $pengguna->last_login_at?->translatedFormat('d M Y, H:i') ?? 'belum pernah' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                    <x-ui.tombol varian="sekunder" ukuran="kecil" :href="route('pengguna.show', $pengguna)">Lihat</x-ui.tombol>
                    <x-ui.tombol varian="sekunder" ukuran="kecil" :href="route('pengguna.edit', $pengguna)">Edit</x-ui.tombol>

                    @can('resetPassword', $pengguna)
                        <x-ui.tombol varian="sekunder" ukuran="kecil" :href="route('pengguna.reset-password', $pengguna)">
                            Reset Password
                        </x-ui.tombol>
                    @endcan

                    @can('ubahStatus', $pengguna)
                        @php
                            $pesanStatusHp = $pengguna->aktif
                                ? "Akun {$pengguna->name} tidak akan bisa masuk ke sistem sampai diaktifkan kembali."
                                : "Akun {$pengguna->name} akan dapat masuk kembali ke sistem.";
                        @endphp

                        <x-ui.konfirmasi
                            :aksi="route('pengguna.status', $pengguna)"
                            :label="$pengguna->aktif ? 'Nonaktifkan' : 'Aktifkan'"
                            :judul="$pengguna->aktif ? 'Nonaktifkan akun?' : 'Aktifkan akun?'"
                            :pesan="$pesanStatusHp"
                            :label-konfirmasi="$pengguna->aktif ? 'Ya, nonaktifkan' : 'Ya, aktifkan'"
                            :varian-konfirmasi="$pengguna->aktif ? 'bahaya' : 'utama'"/>
                    @endcan
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white shadow-kartu">
                <x-ui.kosong judul="Pengguna tidak ditemukan"
                             deskripsi="Ubah kata kunci pencarian atau filter yang dipakai."/>
            </div>
        @endforelse

        @if ($daftarPengguna->hasPages())
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-kartu">
                {{ $daftarPengguna->links() }}
            </div>
        @endif
    </div>

@endsection
