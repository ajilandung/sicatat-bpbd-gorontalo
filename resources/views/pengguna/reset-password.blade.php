@extends('layouts.app')

@section('judul', 'Reset Password')

@section('konten')

    <div class="mx-auto max-w-xl">
        <x-ui.kepala-halaman
            judul="Reset Password"
            :kembali="route('pengguna.index')"
            kembali-label="Kembali ke daftar pengguna"/>

        <x-ui.ringkasan-galat class="mb-6"/>

        <form method="POST" action="{{ route('pengguna.reset-password.update', $pengguna) }}">
            @csrf
            @method('PUT')

            <x-ui.kartu :judul="'Reset password untuk '.$pengguna->name"
                        deskripsi="Password lama tidak dapat dilihat dan tidak dapat dikembalikan. Isi password sementara baru, sampaikan kepada yang bersangkutan, lalu ia wajib menggantinya saat login berikutnya.">
                <div class="space-y-5">
                    <x-ui.kolom nama="password" label="Password Sementara Baru" wajib petunjuk="Minimal 8 karakter.">
                        <x-ui.password nama="password" autocomplete="new-password" required autofocus/>
                    </x-ui.kolom>

                    <x-ui.kolom nama="password_confirmation" label="Konfirmasi Password" wajib>
                        <x-ui.password nama="password_confirmation" autocomplete="new-password" required/>
                    </x-ui.kolom>
                </div>

                <x-slot:kaki>
                    <x-ui.tombol varian="sekunder" :href="route('pengguna.index')">Batal</x-ui.tombol>
                    <x-ui.tombol varian="bahaya">Reset Password</x-ui.tombol>
                </x-slot:kaki>
            </x-ui.kartu>
        </form>
    </div>

@endsection
