@extends('layouts.app')

@section('judul', 'Data Kecamatan')

@section('konten')

    <x-ui.kepala-halaman
        judul="Data Wilayah"
        deskripsi="Daftar kecamatan di Provinsi Gorontalo. Sama seperti kabupaten/kota, data tingkat ini berasal dari sumber resmi dan hanya dapat dilihat."/>

    @include('wilayah.partials.tab')

    @php $adaFilter = $cari !== '' || $kabupatenId !== ''; @endphp

    {{-- ── Pencarian dan filter ── --}}
    <form method="GET" action="{{ route('wilayah.kecamatan.index') }}"
          class="rounded-xl border border-slate-200 bg-white p-4 shadow-kartu">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_15rem_auto]">
            <div>
                <label for="cari" class="sr-only">Cari kecamatan</label>
                <x-ui.input nama="cari" :nilai="$cari" ikon="cari" placeholder="Cari nama atau kode wilayah"/>
            </div>

            <div>
                <label for="kabupaten_id" class="sr-only">Filter kabupaten/kota</label>
                <x-ui.pilihan nama="kabupaten_id" :nilai="$kabupatenId" :opsi="$opsiKabupaten" kosong="Semua kabupaten/kota"/>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.tombol varian="sekunder" ukuran="lebar" class="flex-1 lg:flex-none">
                    <x-ikon nama="saring" class="size-4"/>
                    Terapkan
                </x-ui.tombol>

                @if ($adaFilter)
                    <x-ui.tombol-ikon :href="route('wilayah.kecamatan.index')" ikon="silang" label="Hapus filter" ukuran="besar"/>
                @endif
            </div>
        </div>
    </form>

    <p class="mt-4 text-sm text-slate-500">
        Menampilkan <span class="font-medium text-navy-900">{{ $daftarKecamatan->count() }}</span>
        dari <span class="font-medium text-navy-900">{{ $daftarKecamatan->total() }}</span> kecamatan
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
                        <th scope="col" class="px-5 py-3">Kecamatan</th>
                        <th scope="col" class="px-5 py-3">Kabupaten/Kota</th>
                        <th scope="col" class="px-5 py-3">Kode</th>
                        <th scope="col" class="px-5 py-3 text-right">Desa/Kelurahan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarKecamatan as $kecamatan)
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-navy-900">{{ $kecamatan->nama }}</p>
                            </td>

                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $kecamatan->kabupaten?->namaLengkap() ?? '—' }}
                            </td>

                            <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $kecamatan->kode ?? '—' }}</td>

                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('wilayah.desa.index', ['kecamatan_id' => $kecamatan->id]) }}"
                                   class="rounded font-medium text-air-700 underline-offset-4 hover:underline
                                          focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500">
                                    {{ $kecamatan->desas_count }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-ui.kosong judul="Kecamatan tidak ditemukan"
                                             deskripsi="Ubah kata kunci pencarian atau filter yang dipakai.">
                                    @if ($adaFilter)
                                        <x-ui.tombol varian="sekunder" :href="route('wilayah.kecamatan.index')">Hapus filter</x-ui.tombol>
                                    @endif
                                </x-ui.kosong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($daftarKecamatan->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $daftarKecamatan->links() }}
            </div>
        @endif
    </div>

    {{-- ── Daftar kartu: layar kecil ── --}}
    <div class="mt-3 space-y-3 md:hidden">
        @forelse ($daftarKecamatan as $kecamatan)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-kartu">
                <p class="font-medium text-navy-900">{{ $kecamatan->nama }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $kecamatan->kabupaten?->namaLengkap() ?? '—' }}</p>
                <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $kecamatan->kode ?? 'tanpa kode' }}</p>

                <div class="mt-3 border-t border-slate-100 pt-3">
                    <a href="{{ route('wilayah.desa.index', ['kecamatan_id' => $kecamatan->id]) }}"
                       class="inline-block rounded-lg bg-slate-50 px-2.5 py-1 text-xs font-medium text-air-700">
                        {{ $kecamatan->desas_count }} desa/kelurahan
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white shadow-kartu">
                <x-ui.kosong judul="Kecamatan tidak ditemukan"
                             deskripsi="Ubah kata kunci pencarian atau filter yang dipakai."/>
            </div>
        @endforelse

        @if ($daftarKecamatan->hasPages())
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-kartu">
                {{ $daftarKecamatan->links() }}
            </div>
        @endif
    </div>

@endsection
