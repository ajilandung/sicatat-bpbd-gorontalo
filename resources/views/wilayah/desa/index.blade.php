@extends('layouts.app')

@section('judul', 'Data Desa/Kelurahan')

@section('konten')

    <x-ui.kepala-halaman
        judul="Data Wilayah"
        deskripsi="Daftar desa dan kelurahan penerima bantuan. Desa yang tidak lagi dipakai cukup dinonaktifkan — datanya tetap tersimpan supaya riwayat penyaluran lama tidak kehilangan nama wilayahnya.">
        <x-slot:aksi>
            <x-ui.tombol :href="route('wilayah.desa.create')">
                <x-ikon nama="plus" class="size-4"/>
                Tambah Desa/Kelurahan
            </x-ui.tombol>
        </x-slot:aksi>
    </x-ui.kepala-halaman>

    @include('wilayah.partials.tab')

    @php $adaFilter = $cari !== '' || $kabupatenId !== '' || $kecamatanId !== '' || $status !== ''; @endphp

    {{-- ── Pencarian dan filter ── --}}
    <form method="GET" action="{{ route('wilayah.desa.index') }}"
          class="panel p-4">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_13rem_13rem_10rem_auto]">
            <div>
                <label for="cari" class="sr-only">Cari desa atau kelurahan</label>
                <x-ui.input nama="cari" :nilai="$cari" ikon="cari" placeholder="Cari nama atau kode wilayah"/>
            </div>

            <div>
                <label for="kabupaten_id" class="sr-only">Filter kabupaten/kota</label>
                <x-ui.pilihan nama="kabupaten_id" :nilai="$kabupatenId" :opsi="$opsiKabupaten" kosong="Semua kabupaten/kota"/>
            </div>

            <div>
                <label for="kecamatan_id" class="sr-only">Filter kecamatan</label>
                <x-ui.pilihan nama="kecamatan_id" :nilai="$kecamatanId" :opsi="$opsiKecamatan" kosong="Semua kecamatan"/>
            </div>

            <div>
                <label for="status" class="sr-only">Filter status</label>
                <x-ui.pilihan nama="status" :nilai="$status" :opsi="App\Models\Desa::daftarStatus()" kosong="Semua status"/>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.tombol varian="sekunder" ukuran="lebar" class="flex-1 lg:flex-none">
                    <x-ikon nama="saring" class="size-4"/>
                    Terapkan
                </x-ui.tombol>

                @if ($adaFilter)
                    <x-ui.tombol-ikon :href="route('wilayah.desa.index')" ikon="silang" label="Hapus filter" ukuran="besar"/>
                @endif
            </div>
        </div>
    </form>

    <p class="mt-4 text-sm text-slate-500">
        Menampilkan <span class="font-medium text-navy-900">{{ $daftarDesa->count() }}</span>
        dari <span class="font-medium text-navy-900">{{ $daftarDesa->total() }}</span> desa/kelurahan
        @if ($adaFilter)
            <span class="text-slate-400">(tersaring)</span>
        @endif
    </p>

    {{-- ── Tabel: layar sedang ke atas ── --}}
    <div class="mt-3 hidden overflow-hidden panel md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-tepi text-sm">
                <thead>
                    <tr class="bg-permukaan text-left text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">
                        <th scope="col" class="px-5 py-3">Desa/Kelurahan</th>
                        <th scope="col" class="px-5 py-3">Kecamatan</th>
                        <th scope="col" class="hidden px-5 py-3 lg:table-cell">Kabupaten/Kota</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-tepi/70">
                    @forelse ($daftarDesa as $desa)
                        <tr class="transition-colors hover:bg-permukaan">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-navy-900">{{ $desa->namaLengkap() }}</p>
                                <p class="font-mono text-xs text-slate-400">{{ $desa->kode ?? 'tanpa kode' }}</p>
                            </td>

                            <td class="px-5 py-3.5 text-slate-600">{{ $desa->kecamatan?->nama ?? '—' }}</td>

                            <td class="hidden px-5 py-3.5 text-slate-600 lg:table-cell">
                                {{ $desa->kecamatan?->kabupaten?->namaLengkap() ?? '—' }}
                            </td>

                            <td class="px-5 py-3.5">
                                <x-ui.lencana :warna="$desa->aktif ? 'hijau' : 'merah'">
                                    {{ $desa->aktif ? 'Aktif' : 'Tidak Aktif' }}
                                </x-ui.lencana>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <x-ui.tombol-ikon :href="route('wilayah.desa.edit', $desa)" ikon="pensil" label="Edit desa"/>

                                    @php
                                        $pesanStatus = $desa->aktif
                                            ? "{$desa->namaLengkap()} tidak akan muncul lagi sebagai pilihan pada form penyaluran. Data penyaluran yang sudah menyebutnya tetap utuh."
                                            : "{$desa->namaLengkap()} akan muncul kembali sebagai pilihan pada form penyaluran.";
                                    @endphp

                                    <x-ui.konfirmasi
                                        :aksi="route('wilayah.desa.status', $desa)"
                                        :ikon="$desa->aktif ? 'gembok' : 'centang-bulat'"
                                        :label="$desa->aktif ? 'Nonaktifkan desa' : 'Aktifkan desa'"
                                        :varian="$desa->aktif ? 'bahaya' : 'netral'"
                                        :judul="$desa->aktif ? 'Nonaktifkan wilayah ini?' : 'Aktifkan wilayah ini?'"
                                        :pesan="$pesanStatus"
                                        :label-konfirmasi="$desa->aktif ? 'Ya, nonaktifkan' : 'Ya, aktifkan'"
                                        :varian-konfirmasi="$desa->aktif ? 'bahaya' : 'utama'"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.kosong judul="Desa/kelurahan tidak ditemukan"
                                             deskripsi="Ubah kata kunci pencarian atau filter yang dipakai.">
                                    @if ($adaFilter)
                                        <x-ui.tombol varian="sekunder" :href="route('wilayah.desa.index')">Hapus filter</x-ui.tombol>
                                    @endif
                                </x-ui.kosong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($daftarDesa->hasPages())
            <div class="border-t border-tepi px-5 py-3">
                {{ $daftarDesa->links() }}
            </div>
        @endif
    </div>

    {{-- ── Daftar kartu: layar kecil ── --}}
    <div class="mt-3 space-y-3 md:hidden">
        @forelse ($daftarDesa as $desa)
            <div class="panel p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-medium text-navy-900">{{ $desa->namaLengkap() }}</p>
                        <p class="truncate text-xs text-slate-500">
                            Kec. {{ $desa->kecamatan?->nama ?? '—' }} · {{ $desa->kecamatan?->kabupaten?->namaLengkap() ?? '—' }}
                        </p>
                        <p class="font-mono text-xs text-slate-400">{{ $desa->kode ?? 'tanpa kode' }}</p>
                    </div>

                    <x-ui.lencana :warna="$desa->aktif ? 'hijau' : 'merah'">
                        {{ $desa->aktif ? 'Aktif' : 'Tidak Aktif' }}
                    </x-ui.lencana>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-tepi/70 pt-3">
                    <x-ui.tombol varian="sekunder" ukuran="kecil" :href="route('wilayah.desa.edit', $desa)">Edit</x-ui.tombol>

                    @php
                        $pesanStatusHp = $desa->aktif
                            ? "{$desa->namaLengkap()} tidak akan muncul lagi sebagai pilihan pada form penyaluran. Data penyaluran yang sudah menyebutnya tetap utuh."
                            : "{$desa->namaLengkap()} akan muncul kembali sebagai pilihan pada form penyaluran.";
                    @endphp

                    <x-ui.konfirmasi
                        :aksi="route('wilayah.desa.status', $desa)"
                        :label="$desa->aktif ? 'Nonaktifkan' : 'Aktifkan'"
                        :judul="$desa->aktif ? 'Nonaktifkan wilayah ini?' : 'Aktifkan wilayah ini?'"
                        :pesan="$pesanStatusHp"
                        :label-konfirmasi="$desa->aktif ? 'Ya, nonaktifkan' : 'Ya, aktifkan'"
                        :varian-konfirmasi="$desa->aktif ? 'bahaya' : 'utama'"/>
                </div>
            </div>
        @empty
            <div class="panel">
                <x-ui.kosong judul="Desa/kelurahan tidak ditemukan"
                             deskripsi="Ubah kata kunci pencarian atau filter yang dipakai."/>
            </div>
        @endforelse

        @if ($daftarDesa->hasPages())
            <div class="panel px-4 py-3">
                {{ $daftarDesa->links() }}
            </div>
        @endif
    </div>

@endsection
