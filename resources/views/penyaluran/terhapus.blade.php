@extends('layouts.app')

@section('judul', 'Data Penyaluran Terhapus')

@section('konten')

    <x-ui.kepala-halaman
        judul="Data Terhapus"
        deskripsi="Data penyaluran yang dihapus tidak langsung hilang. Karena catatan ini satu-satunya rekaman kegiatan penyaluran, data yang terlanjur terhapus masih dapat dipulihkan dari sini."
        :kembali="route('penyaluran.index')"
        kembali-label="Kembali ke riwayat penyaluran"/>

    <form method="GET" action="{{ route('penyaluran.terhapus') }}"
          class="panel p-4">
        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
            <div>
                <label for="cari" class="sr-only">Cari data terhapus</label>
                <x-ui.input nama="cari" :nilai="$cari" ikon="cari"
                            placeholder="Cari nama desa, instansi, atau keterangan"/>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.tombol varian="sekunder" ukuran="lebar" class="flex-1 sm:flex-none">
                    <x-ikon nama="saring" class="size-4"/>
                    Cari
                </x-ui.tombol>

                @if ($cari !== '')
                    <x-ui.tombol-ikon :href="route('penyaluran.terhapus')" ikon="silang"
                                      label="Hapus pencarian" ukuran="besar"/>
                @endif
            </div>
        </div>
    </form>

    {{-- ── Tabel: layar sedang ke atas ── --}}
    <div class="mt-4 hidden overflow-hidden panel md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-tepi text-sm">
                <thead>
                    <tr class="bg-permukaan text-left text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">
                        <th scope="col" class="px-5 py-3">Tanggal Kegiatan</th>
                        <th scope="col" class="px-5 py-3">Wilayah Penerima</th>
                        <th scope="col" class="px-5 py-3 text-right">Volume</th>
                        <th scope="col" class="hidden px-5 py-3 lg:table-cell">Dihapus Pada</th>
                        <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-tepi/70">
                    @forelse ($daftarPenyaluran as $penyaluran)
                        <tr class="transition-colors hover:bg-permukaan">
                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-navy-900">
                                {{ $penyaluran->tanggal_penyaluran?->translatedFormat('d M Y') }}
                            </td>

                            <td class="px-5 py-3.5">
                                <p class="text-navy-900">{{ $penyaluran->desas->map->namaLengkap()->implode(', ') }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $penyaluran->instansis->map->namaRingkas()->implode(', ') ?: '—' }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <span class="font-medium text-navy-900">
                                    {{ number_format($penyaluran->volume_liter, 0, ',', '.') }}
                                </span>
                                <span class="text-xs text-slate-500">liter</span>
                            </td>

                            <td class="hidden whitespace-nowrap px-5 py-3.5 text-slate-600 lg:table-cell">
                                {{ $penyaluran->deleted_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <x-ui.tombol-ikon :href="route('penyaluran.show', $penyaluran)"
                                                      ikon="mata" label="Lihat detail penyaluran"/>

                                    <x-ui.konfirmasi
                                        :aksi="route('penyaluran.pulihkan', $penyaluran)"
                                        ikon="pulihkan"
                                        label="Pulihkan data penyaluran"
                                        judul="Pulihkan data ini?"
                                        pesan="Data akan kembali muncul pada riwayat penyaluran dan ikut dihitung lagi pada rekap serta laporan."
                                        label-konfirmasi="Ya, pulihkan"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.kosong ikon="sampah" judul="Tidak ada data terhapus"
                                             deskripsi="Data penyaluran yang dihapus admin akan muncul di sini dan masih dapat dipulihkan."/>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($daftarPenyaluran->hasPages())
            <div class="border-t border-tepi px-5 py-3">
                {{ $daftarPenyaluran->links() }}
            </div>
        @endif
    </div>

    {{-- ── Kartu: layar kecil ──
         Tabel lima kolom tidak terbaca di layar HP tanpa digeser ke samping,
         jadi datanya disusun ulang sebagai kartu — sama seperti halaman
         Riwayat Penyaluran. Seluruh keterangan tetap ditampilkan, termasuk
         waktu penghapusan yang di tabel baru muncul mulai layar lebar. --}}
    <div class="mt-4 space-y-3 md:hidden">
        @forelse ($daftarPenyaluran as $penyaluran)
            <div class="panel p-4">
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                    <p class="font-medium text-navy-900">
                        {{ $penyaluran->tanggal_penyaluran?->translatedFormat('d M Y') }}
                    </p>

                    <p class="text-sm">
                        <span class="font-medium text-navy-900">
                            {{ number_format($penyaluran->volume_liter, 0, ',', '.') }}
                        </span>
                        <span class="text-xs text-slate-500">liter</span>
                    </p>
                </div>

                <p class="mt-2 text-sm text-navy-900">
                    {{ $penyaluran->desas->map->namaLengkap()->implode(', ') }}
                </p>
                <p class="text-xs text-slate-500">
                    {{ $penyaluran->instansis->map->namaRingkas()->implode(', ') ?: '—' }}
                </p>

                <p class="mt-2 text-xs text-slate-400">
                    Dihapus {{ $penyaluran->deleted_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-tepi/70 pt-3">
                    <x-ui.tombol varian="sekunder" ukuran="kecil" :href="route('penyaluran.show', $penyaluran)">
                        Lihat detail
                    </x-ui.tombol>

                    <x-ui.konfirmasi
                        :aksi="route('penyaluran.pulihkan', $penyaluran)"
                        label="Pulihkan"
                        judul="Pulihkan data ini?"
                        pesan="Data akan kembali muncul pada riwayat penyaluran dan ikut dihitung lagi pada rekap serta laporan."
                        label-konfirmasi="Ya, pulihkan"/>
                </div>
            </div>
        @empty
            <div class="panel">
                <x-ui.kosong ikon="sampah" judul="Tidak ada data terhapus"
                             deskripsi="Data penyaluran yang dihapus admin akan muncul di sini dan masih dapat dipulihkan."/>
            </div>
        @endforelse

        @if ($daftarPenyaluran->hasPages())
            <div class="panel px-4 py-3">
                {{ $daftarPenyaluran->links() }}
            </div>
        @endif
    </div>

@endsection
