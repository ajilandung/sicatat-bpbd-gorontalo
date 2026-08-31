@extends('layouts.app')

@section('judul', 'Riwayat Penyaluran')

@section('konten')

    <x-ui.kepala-halaman
        judul="Riwayat Penyaluran"
        deskripsi="Seluruh kegiatan penyaluran bantuan air bersih yang sudah tercatat. Tanggal yang ditampilkan adalah tanggal kegiatan terjadi di lapangan, bukan tanggal datanya dimasukkan ke sistem.">
        <x-slot:aksi>
            @if (auth()->user()->isAdmin())
                <x-ui.tombol varian="sekunder" :href="route('penyaluran.terhapus')">
                    <x-ikon nama="sampah" class="size-4"/>
                    Data Terhapus
                </x-ui.tombol>

                <x-ui.tombol :href="route('penyaluran.create')">
                    <x-ikon nama="plus" class="size-4"/>
                    Input Penyaluran
                </x-ui.tombol>
            @endif
        </x-slot:aksi>
    </x-ui.kepala-halaman>

    {{-- ── Pencarian dan filter (FR-16, FR-17, FR-18) ── --}}
    <form method="GET" action="{{ route('penyaluran.index') }}"
          class="rounded-xl border border-slate-200 bg-white p-4 shadow-kartu sm:p-5">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="cari" class="mb-1.5 block text-xs font-medium text-slate-500">Cari</label>
                <x-ui.input nama="cari" :nilai="$filter['cari']" ikon="cari"
                            placeholder="Nama desa, instansi, atau keterangan"/>
            </div>

            <div>
                <label for="tanggal_mulai" class="mb-1.5 block text-xs font-medium text-slate-500">Tanggal mulai</label>
                <x-ui.input nama="tanggal_mulai" tipe="date" :nilai="$filter['tanggal_mulai']"/>
            </div>

            <div>
                <label for="tanggal_akhir" class="mb-1.5 block text-xs font-medium text-slate-500">Tanggal akhir</label>
                <x-ui.input nama="tanggal_akhir" tipe="date" :nilai="$filter['tanggal_akhir']"/>
            </div>

            <x-ui.filter-wilayah
                :opsi-kabupaten="$opsiKabupaten"
                :kabupaten="$filter['kabupaten_id']"
                :kecamatan="$filter['kecamatan_id']"
                :desa="$filter['desa_id']"/>

            <div>
                <label for="instansi_id" class="mb-1.5 block text-xs font-medium text-slate-500">Instansi pelaksana</label>
                <x-ui.pilihan nama="instansi_id" :nilai="$filter['instansi_id']" :opsi="$opsiInstansi"
                              kosong="Semua instansi"/>
            </div>

            <div>
                <label for="user_id" class="mb-1.5 block text-xs font-medium text-slate-500">Penginput</label>
                <x-ui.pilihan nama="user_id" :nilai="$filter['user_id']" :opsi="$opsiPenginput"
                              kosong="Semua penginput"/>
            </div>

            <div class="flex items-end gap-2">
                <x-ui.tombol varian="sekunder" ukuran="lebar" class="flex-1">
                    <x-ikon nama="saring" class="size-4"/>
                    Terapkan
                </x-ui.tombol>

                @if ($adaFilter)
                    <x-ui.tombol-ikon :href="route('penyaluran.index')" ikon="silang"
                                      label="Hapus filter" ukuran="besar"/>
                @endif
            </div>
        </div>
    </form>

    <p class="mt-4 text-sm text-slate-500">
        Menampilkan <span class="font-medium text-navy-900">{{ $daftarPenyaluran->count() }}</span>
        dari <span class="font-medium text-navy-900">{{ $daftarPenyaluran->total() }}</span> kegiatan
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
                        <th scope="col" class="px-5 py-3">Tanggal</th>
                        <th scope="col" class="px-5 py-3">Wilayah Penerima</th>
                        <th scope="col" class="hidden px-5 py-3 lg:table-cell">Instansi Pelaksana</th>
                        <th scope="col" class="px-5 py-3 text-right">Volume</th>
                        <th scope="col" class="hidden px-5 py-3 text-right xl:table-cell">KK / Jiwa</th>
                        <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarPenyaluran as $penyaluran)
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <p class="font-medium text-navy-900">
                                    {{ $penyaluran->tanggal_penyaluran?->translatedFormat('d M Y') }}
                                </p>
                                <p class="text-xs text-slate-400">{{ $penyaluran->user?->name ?? '—' }}</p>
                            </td>

                            <td class="px-5 py-3.5">
                                <p class="text-navy-900">
                                    {{ $penyaluran->desas->map->namaLengkap()->implode(', ') }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $penyaluran->desas->map(fn ($desa) => $desa->kecamatan?->kabupaten?->namaLengkap())
                                        ->filter()->unique()->implode(', ') ?: '—' }}
                                </p>

                                @if ($penyaluran->angkaGabungan())
                                    <x-ui.lencana warna="kuning" class="mt-1.5">
                                        Angka gabungan {{ $penyaluran->desas->count() }} desa
                                    </x-ui.lencana>
                                @endif
                            </td>

                            <td class="hidden px-5 py-3.5 text-slate-600 lg:table-cell">
                                {{ $penyaluran->instansis->map->namaRingkas()->implode(', ') ?: '—' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <span class="font-medium text-navy-900">
                                    {{ number_format($penyaluran->volume_liter, 0, ',', '.') }}
                                </span>
                                <span class="text-xs text-slate-500">liter</span>
                            </td>

                            <td class="hidden whitespace-nowrap px-5 py-3.5 text-right text-slate-600 xl:table-cell">
                                {{ $penyaluran->jumlah_kk !== null ? number_format($penyaluran->jumlah_kk, 0, ',', '.') : '—' }}
                                /
                                {{ $penyaluran->jumlah_jiwa !== null ? number_format($penyaluran->jumlah_jiwa, 0, ',', '.') : '—' }}
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <x-ui.tombol-ikon :href="route('penyaluran.show', $penyaluran)"
                                                      ikon="mata" label="Lihat detail penyaluran"/>

                                    @if (auth()->user()->isAdmin())
                                        <x-ui.tombol-ikon :href="route('penyaluran.edit', $penyaluran)"
                                                          ikon="pensil" label="Ubah data penyaluran"/>

                                        <x-ui.konfirmasi
                                            :aksi="route('penyaluran.destroy', $penyaluran)"
                                            metode="DELETE"
                                            ikon="sampah"
                                            label="Hapus data penyaluran"
                                            varian="bahaya"
                                            judul="Hapus data penyaluran ini?"
                                            pesan="Data dipindahkan ke Data Terhapus dan tidak lagi ikut dihitung pada rekap maupun laporan. Bila ternyata keliru, data masih dapat dipulihkan dari sana."
                                            label-konfirmasi="Ya, hapus"
                                            varian-konfirmasi="bahaya"/>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.kosong
                                    judul="Belum ada data penyaluran"
                                    :deskripsi="$adaFilter
                                        ? 'Tidak ada kegiatan yang cocok dengan pencarian atau filter yang dipakai.'
                                        : 'Kegiatan penyaluran yang sudah dicatat akan muncul di sini.'">
                                    @if ($adaFilter)
                                        <x-ui.tombol varian="sekunder" :href="route('penyaluran.index')">Hapus filter</x-ui.tombol>
                                    @elseif (auth()->user()->isAdmin())
                                        <x-ui.tombol :href="route('penyaluran.create')">Input Penyaluran</x-ui.tombol>
                                    @endif
                                </x-ui.kosong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($daftarPenyaluran->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $daftarPenyaluran->links() }}
            </div>
        @endif
    </div>

    {{-- ── Daftar kartu: layar kecil ── --}}
    <div class="mt-3 space-y-3 md:hidden">
        @forelse ($daftarPenyaluran as $penyaluran)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-kartu">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-medium text-navy-900">
                            {{ $penyaluran->tanggal_penyaluran?->translatedFormat('d M Y') }}
                        </p>
                        <p class="mt-0.5 text-sm text-slate-600">
                            {{ $penyaluran->desas->map->namaLengkap()->implode(', ') }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ $penyaluran->instansis->map->namaRingkas()->implode(', ') ?: '—' }}
                        </p>
                    </div>

                    <div class="shrink-0 text-right">
                        <p class="font-semibold text-navy-900">
                            {{ number_format($penyaluran->volume_liter, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-slate-500">liter</p>
                    </div>
                </div>

                @if ($penyaluran->angkaGabungan())
                    <x-ui.lencana warna="kuning" class="mt-3">
                        Angka gabungan {{ $penyaluran->desas->count() }} desa
                    </x-ui.lencana>
                @endif

                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                    <x-ui.tombol varian="sekunder" ukuran="kecil" :href="route('penyaluran.show', $penyaluran)">
                        Lihat detail
                    </x-ui.tombol>

                    @if (auth()->user()->isAdmin())
                        <x-ui.tombol varian="sekunder" ukuran="kecil" :href="route('penyaluran.edit', $penyaluran)">
                            Ubah
                        </x-ui.tombol>

                        <x-ui.konfirmasi
                            :aksi="route('penyaluran.destroy', $penyaluran)"
                            metode="DELETE"
                            label="Hapus"
                            varian="bahaya"
                            judul="Hapus data penyaluran ini?"
                            pesan="Data dipindahkan ke Data Terhapus dan tidak lagi ikut dihitung pada rekap maupun laporan. Bila ternyata keliru, data masih dapat dipulihkan dari sana."
                            label-konfirmasi="Ya, hapus"
                            varian-konfirmasi="bahaya"/>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white shadow-kartu">
                <x-ui.kosong
                    judul="Belum ada data penyaluran"
                    :deskripsi="$adaFilter
                        ? 'Tidak ada kegiatan yang cocok dengan pencarian atau filter yang dipakai.'
                        : 'Kegiatan penyaluran yang sudah dicatat akan muncul di sini.'"/>
            </div>
        @endforelse

        @if ($daftarPenyaluran->hasPages())
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-kartu">
                {{ $daftarPenyaluran->links() }}
            </div>
        @endif
    </div>

@endsection
