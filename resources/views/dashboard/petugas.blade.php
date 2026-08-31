@extends('layouts.app')

@section('judul', 'Dashboard Petugas')

@section('konten')

    <x-ui.kepala-halaman
        judul="Dashboard Petugas"
        :deskripsi="'Selamat datang, '.auth()->user()->name.'. Ringkasan kegiatan penyaluran bantuan air bersih yang sudah tercatat.'">
        <x-slot:aksi>
            <x-ui.tombol varian="sekunder" :href="route('penyaluran.index')">
                <x-ikon nama="list" class="size-4"/>
                Riwayat Penyaluran
            </x-ui.tombol>
        </x-slot:aksi>
    </x-ui.kepala-halaman>

    @include('dashboard.partials.statistik')

@endsection
