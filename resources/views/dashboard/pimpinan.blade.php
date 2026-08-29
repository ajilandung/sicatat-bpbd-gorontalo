@extends('layouts.app')

@section('judul', 'Dashboard Pimpinan')

@section('konten')

    <x-ui.kepala-halaman
        judul="Dashboard Pimpinan"
        :deskripsi="'Selamat datang, '.auth()->user()->name.'. Ringkasan statistik penyaluran dan laporan akan tampil di halaman ini setelah fase pencatatan dan pelaporan selesai.'"/>

    <x-ui.kartu judul="Hak akses akun Anda">
        <ul class="space-y-3 text-sm text-slate-600">
            @foreach ([
                ['centang-bulat', 'text-emerald-600', 'Melihat dashboard, riwayat penyaluran, dan laporan.'],
                ['silang', 'text-slate-400', 'Tidak dapat menambah, mengubah, atau menghapus data.'],
                ['silang', 'text-slate-400', 'Tidak dapat mengelola pengguna sistem.'],
            ] as [$ikon, $warna, $poin])
                <li class="flex items-start gap-3">
                    <x-ikon :nama="$ikon" class="mt-0.5 size-4 shrink-0 {{ $warna }}"/>
                    <span>{{ $poin }}</span>
                </li>
            @endforeach
        </ul>
    </x-ui.kartu>

@endsection
