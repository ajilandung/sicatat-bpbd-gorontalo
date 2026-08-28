@extends('layouts.app')

@section('judul', 'Dashboard')

@section('konten')

    <div class="rounded-xl border border-sky-200 bg-sky-50 p-5">
        <h2 class="text-base font-semibold text-sky-900">Selamat datang, {{ auth()->user()->name }}</h2>
        <p class="mt-1 text-sm text-sky-800">
            Sistem sedang dibangun bertahap. <strong>Fase 1</strong> — basis data, autentikasi, dan hak akses —
            sudah selesai. Kartu statistik penyaluran dan grafik bulanan menyusul pada Fase 4.
        </p>
    </div>

    <h3 class="mt-8 text-sm font-semibold uppercase tracking-wider text-slate-500">Data Penyaluran</h3>

    <div class="mt-3 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Total kegiatan penyaluran</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">{{ number_format($jumlahPenyaluran, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">Pencatatan dimulai pada Fase 3</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Total air tersalur</p>
            <p class="mt-1 text-3xl font-semibold text-slate-900">
                {{ number_format($totalLiter, 0, ',', '.') }}
                <span class="text-lg font-normal text-slate-500">liter</span>
            </p>
            <p class="mt-1 text-xs text-slate-400">Dihitung dari seluruh kegiatan tercatat</p>
        </div>
    </div>

    <h3 class="mt-8 text-sm font-semibold uppercase tracking-wider text-slate-500">Kesiapan Data Master</h3>

    <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Kabupaten / Kota', 'nilai' => $jumlahKabupaten, 'ikon' => 'map'],
            ['label' => 'Kecamatan', 'nilai' => $jumlahKecamatan, 'ikon' => 'map'],
            ['label' => 'Desa / Kelurahan', 'nilai' => $jumlahDesa, 'ikon' => 'map'],
            ['label' => 'Instansi Pelaksana', 'nilai' => $jumlahInstansi, 'ikon' => 'building'],
        ] as $kartu)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-slate-500">{{ $kartu['label'] }}</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">
                            {{ number_format($kartu['nilai'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-slate-100 p-2 text-slate-500">
                        <x-ikon :nama="$kartu['ikon']" class="size-5"/>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <p class="mt-4 text-sm text-slate-500">
        Data wilayah diambil dari daftar wilayah administratif Provinsi Gorontalo beserta kode resminya,
        sehingga nama desa pada laporan tidak lagi bergantung pada ketikan manual.
    </p>

@endsection
