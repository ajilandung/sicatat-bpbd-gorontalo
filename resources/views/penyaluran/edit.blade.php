@extends('layouts.app')

@section('judul', 'Ubah Data Penyaluran')

@section('konten')

    <div class="mx-auto max-w-3xl">
        <x-ui.kepala-halaman
            judul="Ubah Data Penyaluran"
            deskripsi="Koreksi data historis memang diperbolehkan — laporan lapangan kerap baru lengkap beberapa hari kemudian. Setiap perubahan tercatat pada riwayat data ini. Foto dokumentasi kegiatan ditambahkan dari halaman detail."
            :kembali="route('penyaluran.show', $penyaluran)"
            kembali-label="Kembali ke detail penyaluran"/>

        @include('penyaluran.partials.form', [
            'aksi' => route('penyaluran.update', $penyaluran),
            'batal' => route('penyaluran.show', $penyaluran),
        ])
    </div>

@endsection
