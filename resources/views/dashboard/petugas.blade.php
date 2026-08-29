@extends('layouts.app')

@section('judul', 'Dashboard Petugas')

@section('konten')

    <x-ui.kepala-halaman
        judul="Dashboard Petugas"
        :deskripsi="'Selamat datang, '.auth()->user()->name.'. Halaman ini masih berupa penanda hak akses — menu penyaluran dan laporan dibuka pada fase berikutnya.'"/>

    <x-ui.kartu judul="Yang dapat Anda lakukan saat ini">
        <ul class="space-y-3 text-sm text-slate-600">
            @foreach ([
                'Mengganti password akun Anda melalui menu profil di kanan atas.',
                'Menghubungi administrator bila data akun Anda perlu diperbarui.',
            ] as $poin)
                <li class="flex items-start gap-3">
                    <x-ikon nama="centang-bulat" class="mt-0.5 size-4 shrink-0 text-air-600"/>
                    <span>{{ $poin }}</span>
                </li>
            @endforeach
        </ul>
    </x-ui.kartu>

@endsection
