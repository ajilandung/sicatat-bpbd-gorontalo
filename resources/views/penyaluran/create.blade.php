@extends('layouts.app')

@section('judul', 'Input Penyaluran')

@section('konten')

    <div class="mx-auto max-w-3xl">
        <x-ui.kepala-halaman
            judul="Input Penyaluran"
            deskripsi="Catat satu kegiatan penyaluran bantuan air bersih. Satu kegiatan boleh mencakup beberapa desa dan dikerjakan beberapa instansi sekaligus."
            :kembali="route('penyaluran.index')"
            kembali-label="Kembali ke riwayat penyaluran"/>

        @include('penyaluran.partials.form', [
            'aksi' => route('penyaluran.store'),
            'batal' => route('penyaluran.index'),
        ])
    </div>

@endsection
