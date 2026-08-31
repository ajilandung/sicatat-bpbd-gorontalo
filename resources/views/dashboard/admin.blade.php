@extends('layouts.app')

@section('judul', 'Dashboard Admin')

@section('konten')

    <x-ui.kepala-halaman
        judul="Dashboard Admin"
        :deskripsi="'Selamat datang, '.auth()->user()->name.'. Ringkasan kegiatan penyaluran bantuan air bersih dan kesiapan data sistem.'">
        <x-slot:aksi>
            <x-ui.tombol :href="route('penyaluran.create')">
                <x-ikon nama="plus" class="size-4"/>
                Input Penyaluran
            </x-ui.tombol>
        </x-slot:aksi>
    </x-ui.kepala-halaman>

    @include('dashboard.partials.statistik')

    <h2 class="mt-8 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Pengguna Sistem</h2>

    <div class="mt-3 grid gap-4 sm:grid-cols-2">
        <x-ui.kartu-statistik label="Total akun terdaftar" :nilai="$jumlahPengguna" ikon="users" warna="navy"
                              :href="route('pengguna.index')">
            Kelola pengguna
        </x-ui.kartu-statistik>

        <x-ui.kartu-statistik label="Akun nonaktif" :nilai="$jumlahPenggunaNonaktif" ikon="gembok"
                              catatan="Tidak dapat masuk ke sistem"/>
    </div>

    <h2 class="mt-8 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Kesiapan Data Master</h2>

    <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Kabupaten / Kota', 'nilai' => $jumlahKabupaten, 'ikon' => 'map'],
            ['label' => 'Kecamatan', 'nilai' => $jumlahKecamatan, 'ikon' => 'map'],
            ['label' => 'Desa / Kelurahan', 'nilai' => $jumlahDesa, 'ikon' => 'map'],
            ['label' => 'Instansi Pelaksana', 'nilai' => $jumlahInstansi, 'ikon' => 'building'],
        ] as $kartu)
            <x-ui.kartu-statistik :label="$kartu['label']" :nilai="$kartu['nilai']" :ikon="$kartu['ikon']"/>
        @endforeach
    </div>

    <p class="mt-4 max-w-3xl text-sm leading-relaxed text-slate-500">
        Data wilayah diambil dari daftar wilayah administratif Provinsi Gorontalo beserta kode resminya,
        sehingga nama desa pada laporan tidak lagi bergantung pada ketikan manual.
    </p>

@endsection
