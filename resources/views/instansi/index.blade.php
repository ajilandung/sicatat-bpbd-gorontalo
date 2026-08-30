@extends('layouts.app')

@section('judul', 'Data Instansi')

@section('konten')

    <x-ui.kepala-halaman
        judul="Data Instansi Pelaksana"
        deskripsi="Instansi yang melaksanakan penyaluran bantuan air bersih. Satu kegiatan dapat dikerjakan beberapa instansi sekaligus. Instansi yang tidak lagi terlibat cukup dinonaktifkan agar riwayat penyaluran lama tetap utuh.">
        <x-slot:aksi>
            <x-ui.tombol :href="route('instansi.create')">
                <x-ikon nama="plus" class="size-4"/>
                Tambah Instansi
            </x-ui.tombol>
        </x-slot:aksi>
    </x-ui.kepala-halaman>

    @php $adaFilter = $cari !== '' || $status !== ''; @endphp

    {{-- ── Pencarian dan filter ── --}}
    <form method="GET" action="{{ route('instansi.index') }}"
          class="rounded-xl border border-slate-200 bg-white p-4 shadow-kartu">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_11rem_auto]">
            <div>
                <label for="cari" class="sr-only">Cari instansi</label>
                <x-ui.input nama="cari" :nilai="$cari" ikon="cari" placeholder="Cari nama atau singkatan instansi"/>
            </div>

            <div>
                <label for="status" class="sr-only">Filter status</label>
                <x-ui.pilihan nama="status" :nilai="$status" :opsi="App\Models\Instansi::daftarStatus()" kosong="Semua status"/>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.tombol varian="sekunder" ukuran="lebar" class="flex-1 lg:flex-none">
                    <x-ikon nama="saring" class="size-4"/>
                    Terapkan
                </x-ui.tombol>

                @if ($adaFilter)
                    <x-ui.tombol-ikon :href="route('instansi.index')" ikon="silang" label="Hapus filter" ukuran="besar"/>
                @endif
            </div>
        </div>
    </form>

    <p class="mt-4 text-sm text-slate-500">
        Menampilkan <span class="font-medium text-navy-900">{{ $daftarInstansi->count() }}</span>
        dari <span class="font-medium text-navy-900">{{ $daftarInstansi->total() }}</span> instansi
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
                        <th scope="col" class="px-5 py-3">Instansi</th>
                        <th scope="col" class="hidden px-5 py-3 lg:table-cell">Kontak</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="px-5 py-3 text-right">Kegiatan</th>
                        <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarInstansi as $instansi)
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-navy-900">{{ $instansi->nama }}</p>
                                @if ($instansi->singkatan)
                                    <p class="text-xs text-slate-500">{{ $instansi->singkatan }}</p>
                                @endif
                            </td>

                            <td class="hidden px-5 py-3.5 text-slate-600 lg:table-cell">
                                <p class="max-w-xs truncate">{{ $instansi->alamat ?? '—' }}</p>
                                <p class="text-xs text-slate-500">{{ $instansi->telepon ?? '' }}</p>
                            </td>

                            <td class="px-5 py-3.5">
                                <x-ui.lencana :warna="$instansi->aktif ? 'hijau' : 'merah'">
                                    {{ $instansi->aktif ? 'Aktif' : 'Tidak Aktif' }}
                                </x-ui.lencana>
                            </td>

                            <td class="px-5 py-3.5 text-right text-slate-600">{{ $instansi->penyalurans_count }}</td>

                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <x-ui.tombol-ikon :href="route('instansi.edit', $instansi)" ikon="pensil" label="Edit instansi"/>

                                    @php
                                        $pesanStatus = $instansi->aktif
                                            ? "{$instansi->nama} tidak akan muncul lagi sebagai pilihan pada form penyaluran. Data penyaluran yang sudah menyebutnya tetap utuh."
                                            : "{$instansi->nama} akan muncul kembali sebagai pilihan pada form penyaluran.";
                                    @endphp

                                    <x-ui.konfirmasi
                                        :aksi="route('instansi.status', $instansi)"
                                        :ikon="$instansi->aktif ? 'gembok' : 'centang-bulat'"
                                        :label="$instansi->aktif ? 'Nonaktifkan instansi' : 'Aktifkan instansi'"
                                        :varian="$instansi->aktif ? 'bahaya' : 'netral'"
                                        :judul="$instansi->aktif ? 'Nonaktifkan instansi ini?' : 'Aktifkan instansi ini?'"
                                        :pesan="$pesanStatus"
                                        :label-konfirmasi="$instansi->aktif ? 'Ya, nonaktifkan' : 'Ya, aktifkan'"
                                        :varian-konfirmasi="$instansi->aktif ? 'bahaya' : 'utama'"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.kosong judul="Instansi tidak ditemukan"
                                             deskripsi="Ubah kata kunci pencarian atau filter yang dipakai.">
                                    @if ($adaFilter)
                                        <x-ui.tombol varian="sekunder" :href="route('instansi.index')">Hapus filter</x-ui.tombol>
                                    @endif
                                </x-ui.kosong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($daftarInstansi->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $daftarInstansi->links() }}
            </div>
        @endif
    </div>

    {{-- ── Daftar kartu: layar kecil ── --}}
    <div class="mt-3 space-y-3 md:hidden">
        @forelse ($daftarInstansi as $instansi)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-kartu">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium text-navy-900">{{ $instansi->nama }}</p>
                        @if ($instansi->singkatan)
                            <p class="text-xs text-slate-500">{{ $instansi->singkatan }}</p>
                        @endif
                        @if ($instansi->telepon)
                            <p class="mt-1 text-xs text-slate-500">{{ $instansi->telepon }}</p>
                        @endif
                    </div>

                    <x-ui.lencana :warna="$instansi->aktif ? 'hijau' : 'merah'">
                        {{ $instansi->aktif ? 'Aktif' : 'Tidak Aktif' }}
                    </x-ui.lencana>
                </div>

                <p class="mt-2 text-xs text-slate-400">
                    Tercatat pada {{ $instansi->penyalurans_count }} kegiatan penyaluran
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                    <x-ui.tombol varian="sekunder" ukuran="kecil" :href="route('instansi.edit', $instansi)">Edit</x-ui.tombol>

                    @php
                        $pesanStatusHp = $instansi->aktif
                            ? "{$instansi->nama} tidak akan muncul lagi sebagai pilihan pada form penyaluran. Data penyaluran yang sudah menyebutnya tetap utuh."
                            : "{$instansi->nama} akan muncul kembali sebagai pilihan pada form penyaluran.";
                    @endphp

                    <x-ui.konfirmasi
                        :aksi="route('instansi.status', $instansi)"
                        :label="$instansi->aktif ? 'Nonaktifkan' : 'Aktifkan'"
                        :judul="$instansi->aktif ? 'Nonaktifkan instansi ini?' : 'Aktifkan instansi ini?'"
                        :pesan="$pesanStatusHp"
                        :label-konfirmasi="$instansi->aktif ? 'Ya, nonaktifkan' : 'Ya, aktifkan'"
                        :varian-konfirmasi="$instansi->aktif ? 'bahaya' : 'utama'"/>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white shadow-kartu">
                <x-ui.kosong judul="Instansi tidak ditemukan"
                             deskripsi="Ubah kata kunci pencarian atau filter yang dipakai."/>
            </div>
        @endforelse

        @if ($daftarInstansi->hasPages())
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-kartu">
                {{ $daftarInstansi->links() }}
            </div>
        @endif
    </div>

@endsection
