@php
    $catatanKelengkapan = $kegiatanTanpaJumlahWarga > 0
        ? number_format($kegiatanTanpaJumlahWarga, 0, ',', '.').' kegiatan belum mencantumkan KK atau jiwa'
        : 'Seluruh kegiatan sudah mencantumkan KK dan jiwa';
@endphp

{{-- Ringkasan penyaluran yang sama untuk seluruh role (FR-19 sampai FR-21).

     Angkanya dihitung `App\Support\RekapPenyaluran`, satu-satunya tempat rumus
     agregasi berada, supaya dashboard dan laporan tidak pernah menampilkan dua
     angka berbeda untuk hal yang sama. --}}

<div class="flex flex-wrap items-center justify-between gap-3">
    <h2 class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Ringkasan Penyaluran</h2>

    {{-- Filter periode berlaku untuk seluruh isi dashboard: kartu, peta,
         grafik, dan kedua daftar di bawahnya. Formulirnya mengarah ke URL
         yang sedang dibuka, sehingga satu berkas ini melayani dashboard
         ketiga role tanpa perlu tahu route mana yang aktif. --}}
    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="periode" class="text-xs font-medium text-slate-500">Periode</label>

        <div class="w-48">
            <x-ui.pilihan nama="periode" :nilai="$periodeAktif" :opsi="$opsiPeriode"
                          onchange="this.form.submit()"/>
        </div>

        {{-- Tanpa JavaScript, pilihan tetap dapat diterapkan. --}}
        <noscript>
            <x-ui.tombol varian="sekunder">Terapkan</x-ui.tombol>
        </noscript>
    </form>
</div>

<div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-ui.kartu-statistik label="Total air tersalur" :nilai="$totalVolume" satuan="liter"
                          ikon="tetesan" warna="air" :href="route('penyaluran.index')">
        Lihat riwayat
    </x-ui.kartu-statistik>

    <x-ui.kartu-statistik label="Wilayah penerima" :nilai="$jumlahWilayahPenerima"
                          ikon="map" warna="air"
                          catatan="Desa/kelurahan yang pernah menerima bantuan"/>

    <x-ui.kartu-statistik label="Total kegiatan penyaluran" :nilai="$jumlahKegiatan"
                          ikon="list" warna="navy"
                          catatan="Dihitung menurut tanggal kegiatan di lapangan"/>

    <x-ui.kartu-statistik label="Kegiatan bulan ini" :nilai="$kegiatanBulanIni"
                          ikon="kalender" warna="navy"
                          :catatan="'Bulan berjalan, termasuk data susulan'"/>
</div>

@include('dashboard.partials.ringkasan-wilayah')

{{-- Grafik bulanan (FR-21) berdampingan dengan dua angka pendukung: satu baris
     penuh, sehingga ruang di samping grafik tidak terbuang. --}}
<div class="mt-4 grid gap-4 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <x-ui.grafik-bulanan :data="$grafikBulanan"
                             deskripsi="Mengikuti periode yang dipilih, dikelompokkan menurut tanggal kegiatan di lapangan. Arahkan kursor ke sebuah batang untuk melihat jumlah kegiatannya."/>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
        <x-ui.kartu-statistik label="Total KK terdampak" :nilai="$totalKk" satuan="KK"
                              ikon="users" warna="air" :catatan="$catatanKelengkapan"/>

        <x-ui.kartu-statistik label="Total jiwa terdampak" :nilai="$totalJiwa" satuan="jiwa"
                              ikon="users" warna="navy" :catatan="$catatanKelengkapan"/>
    </div>
</div>

@if ($kegiatanTanpaJumlahWarga > 0)
    <x-ui.notifikasi jenis="peringatan" class="mt-4">
        Angka KK dan jiwa dihitung hanya dari kegiatan yang datanya terisi. Laporan lapangan memang kerap
        hanya mencantumkan volume air, sehingga totalnya belum mencakup seluruh kegiatan.
    </x-ui.notifikasi>
@endif

<div class="mt-4 grid gap-4 lg:grid-cols-2">
    {{-- Wilayah paling sering menerima bantuan --}}
    <x-ui.kartu judul="Wilayah Paling Sering Menerima"
                deskripsi="Lima desa/kelurahan dengan kegiatan penyaluran terbanyak."
                padat>
        @forelse ($wilayahTersering as $desa)
            <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-3.5 last:border-0 sm:px-6">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-air-50
                             text-sm font-semibold text-air-800">
                    {{ $loop->iteration }}
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium text-navy-900">{{ $desa->namaLengkap() }}</p>
                    <p class="truncate text-xs text-slate-500">
                        Kec. {{ $desa->kecamatan?->nama ?? '—' }},
                        {{ $desa->kecamatan?->kabupaten?->namaLengkap() ?? '—' }}
                    </p>
                </div>

                <x-ui.lencana warna="biru">{{ $desa->jumlah_kegiatan }} kegiatan</x-ui.lencana>
            </div>
        @empty
            <x-ui.kosong ikon="map" judul="Belum ada wilayah penerima"
                         deskripsi="Daftar ini terisi setelah ada kegiatan penyaluran yang tercatat."/>
        @endforelse
    </x-ui.kartu>

    {{-- Data penyaluran terbaru --}}
    <x-ui.kartu judul="Penyaluran Terbaru"
                deskripsi="Lima kegiatan dengan tanggal kejadian paling akhir."
                padat>
        <x-slot:aksi>
            <x-ui.tombol varian="tautan" :href="route('penyaluran.index')">Lihat semua</x-ui.tombol>
        </x-slot:aksi>

        @forelse ($penyaluranTerbaru as $penyaluran)
            <a href="{{ route('penyaluran.show', $penyaluran) }}"
               class="flex items-start gap-4 border-b border-slate-100 px-5 py-3.5 transition-colors
                      last:border-0 hover:bg-slate-50/70 focus-visible:outline-none focus-visible:bg-slate-50 sm:px-6">
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium text-navy-900">
                        {{ $penyaluran->desas->map->namaLengkap()->implode(', ') ?: 'Tanpa desa' }}
                    </p>
                    <p class="truncate text-xs text-slate-500">
                        {{ $penyaluran->tanggal_penyaluran?->format('d/m/Y') }} ·
                        {{ $penyaluran->instansis->map->namaRingkas()->implode(', ') ?: 'tanpa instansi' }}
                    </p>
                </div>

                <div class="shrink-0 text-right">
                    <p class="font-semibold text-navy-900">
                        {{ number_format($penyaluran->volume_liter, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-500">liter</p>
                </div>
            </a>
        @empty
            <x-ui.kosong ikon="tetesan" judul="Belum ada data penyaluran"
                         deskripsi="Kegiatan yang sudah dicatat akan muncul di sini."/>
        @endforelse
    </x-ui.kartu>
</div>
