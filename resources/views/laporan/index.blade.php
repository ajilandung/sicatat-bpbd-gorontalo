@extends('layouts.app')

@section('judul', 'Laporan & Export')

@section('konten')

    @php $filterAktif = array_filter($filter, fn ($nilai) => $nilai !== ''); @endphp

    <x-ui.kepala-halaman
        judul="Laporan &amp; Export"
        deskripsi="Rekap kegiatan penyaluran bantuan air bersih menurut tanggal kejadiannya, siap dicetak sebagai laporan Pusdalops atau diunduh untuk diolah di Excel. Data susulan yang dimasukkan belakangan otomatis ikut ke tanggal kegiatannya.">
        <x-slot:aksi>
            <x-ui.tombol varian="sekunder" :href="route('laporan.excel', $filterAktif)">
                <x-ikon nama="unduh" class="size-4"/>
                Unduh Excel
            </x-ui.tombol>
        </x-slot:aksi>
    </x-ui.kepala-halaman>

    {{-- ── Filter laporan: sama persis dengan Riwayat Penyaluran (FR-16, FR-17, FR-18) ── --}}
    <form method="GET" action="{{ route('laporan.index') }}"
          class="panel p-4 sm:p-5">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="cari" class="mb-1.5 block text-xs font-medium text-slate-500">Cari</label>
                <x-ui.input nama="cari" :nilai="$filter['cari']" ikon="cari"
                            placeholder="Nama desa, instansi, atau keterangan"/>
            </div>

            <div>
                <label for="tanggal_mulai" class="mb-1.5 block text-xs font-medium text-slate-500">Tanggal mulai</label>
                <x-ui.tanggal nama="tanggal_mulai" :nilai="$filter['tanggal_mulai']"/>
            </div>

            <div>
                <label for="tanggal_akhir" class="mb-1.5 block text-xs font-medium text-slate-500">Tanggal akhir</label>
                <x-ui.tanggal nama="tanggal_akhir" :nilai="$filter['tanggal_akhir']"/>
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
                    <x-ui.tombol-ikon :href="route('laporan.index')" ikon="silang"
                                      label="Hapus filter" ukuran="besar"/>
                @endif
            </div>
        </div>
    </form>

    {{-- ── Ringkasan laporan (PRD 8.8) ── --}}
    <p class="mt-6 text-sm text-slate-500">
        Periode data: <span class="font-medium text-navy-900">{{ $rekap->labelPeriode() }}</span>
        @if ($adaFilter)
            <span class="text-slate-400">(tersaring)</span>
        @endif
    </p>

    <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.kartu-statistik label="Total air tersalur" :nilai="$rekap->totalVolume()" satuan="liter"
                              ikon="tetesan" warna="air"/>

        <x-ui.kartu-statistik label="Jumlah kegiatan" :nilai="$rekap->jumlahKegiatan()" satuan="kegiatan"
                              ikon="list" warna="navy"/>

        <x-ui.kartu-statistik label="Wilayah penerima" :nilai="$rekap->jumlahWilayahPenerima()" satuan="desa"
                              ikon="map" warna="navy"
                              :catatan="$rekap->jumlahKecamatanPenerima().' kecamatan'"/>

        <x-ui.kartu-statistik label="Warga terdampak"
                              :nilai="number_format($rekap->totalKk(), 0, ',', '.').' KK'"
                              ikon="users" warna="navy"
                              :catatan="number_format($rekap->totalJiwa(), 0, ',', '.').' jiwa. '
                                  .$rekap->kegiatanTanpaJumlahWarga().' kegiatan belum mencantumkan jumlah warga.'"/>
    </div>

    {{-- ── Identitas laporan cetak ──
         Bagian kop dan tanda tangan tidak berasal dari basis data, jadi diketik
         di sini. Isian terakhir diingat sistem supaya tidak perlu diketik ulang
         setiap kali mencetak. --}}
    <form method="GET" action="{{ route('laporan.cetak') }}" target="_blank" class="mt-6">
        @foreach ($filterAktif as $nama => $nilai)
            <input type="hidden" name="{{ $nama }}" value="{{ $nilai }}">
        @endforeach

        <x-ui.kartu judul="Identitas laporan cetak"
                    deskripsi="Keterangan yang muncul pada kop dan penutup laporan. Isian ini tidak tersimpan sebagai data kegiatan — hanya diingat sebagai isian bawaan laporan berikutnya.">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="jenis_bencana" class="mb-1.5 block text-xs font-medium text-slate-500">Jenis bencana</label>
                    <x-ui.input nama="jenis_bencana" :nilai="$identitas['jenis_bencana']" placeholder="Kekeringan"/>
                </div>

                <div>
                    <label for="tanggal_kejadian" class="mb-1.5 block text-xs font-medium text-slate-500">Tanggal kejadian</label>
                    <x-ui.tanggal nama="tanggal_kejadian" :nilai="$identitas['tanggal_kejadian']" :max="now()->toDateString()"/>
                </div>

                <div>
                    <label for="waktu_kejadian" class="mb-1.5 block text-xs font-medium text-slate-500">Waktu kejadian</label>
                    <x-ui.input nama="waktu_kejadian" :nilai="$identitas['waktu_kejadian']" placeholder="11.30 WITA"/>
                </div>

                <div class="sm:col-span-2">
                    <label for="lokasi_kejadian" class="mb-1.5 block text-xs font-medium text-slate-500">Lokasi kejadian</label>
                    <x-ui.input nama="lokasi_kejadian" :nilai="$identitas['lokasi_kejadian']"
                                placeholder="Kab. Bone Bolango, Kab. Gorontalo, Kab. Gorontalo Utara"/>
                </div>

                <div>
                    <label for="update_ke" class="mb-1.5 block text-xs font-medium text-slate-500">Update ke-</label>
                    <x-ui.input nama="update_ke" :nilai="$identitas['update_ke']" placeholder="XV"/>
                </div>

                <div>
                    <label for="penandatangan_jabatan" class="mb-1.5 block text-xs font-medium text-slate-500">Jabatan penanda tangan</label>
                    <x-ui.input nama="penandatangan_jabatan" :nilai="$identitas['penandatangan_jabatan']"
                                placeholder="Kepala Pelaksana"/>
                </div>

                <div>
                    <label for="penandatangan_nama" class="mb-1.5 block text-xs font-medium text-slate-500">Nama penanda tangan</label>
                    <x-ui.input nama="penandatangan_nama" :nilai="$identitas['penandatangan_nama']"/>
                </div>

                <div>
                    <label for="penandatangan_pangkat" class="mb-1.5 block text-xs font-medium text-slate-500">Pangkat</label>
                    <x-ui.input nama="penandatangan_pangkat" :nilai="$identitas['penandatangan_pangkat']"
                                placeholder="Pembina Utama Madya"/>
                </div>

                <div>
                    <label for="penandatangan_nip" class="mb-1.5 block text-xs font-medium text-slate-500">NIP</label>
                    <x-ui.input nama="penandatangan_nip" :nilai="$identitas['penandatangan_nip']"/>
                </div>
            </div>

            {{-- Lampiran foto. Foto tidak dipilih sendiri di sini: yang ikut
                 tercetak adalah foto milik kegiatan yang sudah tersaring,
                 dikelompokkan menurut tanggal kegiatannya. --}}
            @php $jumlahFoto = $rekap->jumlahFoto(); @endphp

            <label class="mt-5 flex items-start gap-3 rounded-lg bg-permukaan px-4 py-3 text-sm
                          {{ $jumlahFoto > 0 ? 'cursor-pointer' : 'opacity-60' }}">
                <input type="checkbox" name="lampiran" value="1" class="mt-0.5 size-4 rounded border-slate-300 text-air-700 focus:ring-air-500"
                       @checked($jumlahFoto > 0) @disabled($jumlahFoto === 0)>

                <span>
                    <span class="font-medium text-navy-900">Sertakan lampiran dokumentasi foto</span>
                    <span class="mt-0.5 block text-xs leading-relaxed text-slate-500">
                        @if ($jumlahFoto > 0)
                            {{ $jumlahFoto }} foto dari kegiatan pada periode ini akan dicetak di halaman terpisah,
                            dikelompokkan menurut tanggal kegiatannya.
                        @else
                            Belum ada foto dokumentasi pada kegiatan yang tersaring. Foto ditambahkan dari halaman
                            detail masing-masing kegiatan.
                        @endif
                    </span>
                </span>
            </label>

            <x-slot:kaki>
                <p class="mr-auto text-xs leading-relaxed text-slate-500">
                    Laporan terbuka di tab baru. Untuk menyimpannya sebagai PDF, pilih
                    <span class="font-medium text-navy-900">Simpan sebagai PDF</span> pada dialog cetak.
                </p>

                <x-ui.tombol>
                    <x-ikon nama="printer" class="size-4"/>
                    Buat Laporan Cetak
                </x-ui.tombol>
            </x-slot:kaki>
        </x-ui.kartu>
    </form>

    {{-- ── Rincian kegiatan per tanggal ── --}}
    <x-ui.kartu class="mt-6" padat
                judul="Rincian penyaluran per tanggal"
                deskripsi="Susunan yang sama dengan bagian “Upaya yang Dilakukan” pada laporan cetak.">
        @forelse ($perTanggal as $hari)
            <div class="border-b border-tepi/70 px-5 py-5 last:border-b-0 sm:px-6">
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-navy-900">
                        {{ $hari['tanggal']->translatedFormat('l, j F Y') }}
                    </h3>

                    <p class="text-sm text-slate-500">
                        {{ $hari['jumlah_kegiatan'] }} kegiatan ·
                        <span class="font-medium text-navy-900">
                            {{ number_format($hari['total_liter'], 0, ',', '.') }} liter
                        </span>
                    </p>
                </div>

                @foreach ($hari['kelompok'] as $kelompok)
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">
                        {{ $kelompok['kabupaten'] }}
                    </p>

                    <ul class="mt-1.5 space-y-2">
                        @foreach ($kelompok['kegiatan'] as $penyaluran)
                            <li class="flex flex-wrap items-start justify-between gap-x-4 gap-y-1 rounded-lg bg-permukaan px-3 py-2">
                                <div class="min-w-0">
                                    @foreach ($penyaluran->wilayahPerKecamatan() as $wilayah)
                                        <p class="text-sm text-navy-900">
                                            {{ $wilayah['kecamatan'] }}:
                                            <span class="text-slate-600">{{ implode(', ', $wilayah['desa']) }}</span>
                                        </p>
                                    @endforeach

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $penyaluran->instansis->map->namaRingkas()->implode(', ') ?: 'Pelaksana tidak dicatat' }}
                                    </p>
                                </div>

                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-medium text-navy-900">
                                        {{ number_format($penyaluran->volume_liter, 0, ',', '.') }} liter
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ $penyaluran->jumlah_kk !== null ? number_format($penyaluran->jumlah_kk, 0, ',', '.').' KK' : '— KK' }}
                                        /
                                        {{ $penyaluran->jumlah_jiwa !== null ? number_format($penyaluran->jumlah_jiwa, 0, ',', '.').' jiwa' : '— jiwa' }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        @empty
            <x-ui.kosong
                judul="Belum ada data untuk dilaporkan"
                :deskripsi="$adaFilter
                    ? 'Tidak ada kegiatan yang cocok dengan filter yang dipakai. Ubah periode atau wilayahnya, lalu terapkan ulang.'
                    : 'Kegiatan penyaluran yang sudah dicatat akan langsung muncul di sini dan ikut tercetak pada laporan.'">
                @if ($adaFilter)
                    <x-ui.tombol varian="sekunder" :href="route('laporan.index')">Hapus filter</x-ui.tombol>
                @endif
            </x-ui.kosong>
        @endforelse
    </x-ui.kartu>

@endsection
