@extends('layouts.app')

@section('judul', 'Tambah Pengguna')

@section('konten')

    <div class="mx-auto max-w-2xl">
        <x-ui.kepala-halaman
            judul="Tambah Pengguna"
            deskripsi="Buat akun baru untuk pegawai BPBD. Pengguna tidak dapat mendaftar sendiri."
            :kembali="route('pengguna.index')"
            kembali-label="Kembali ke daftar pengguna"/>

        <x-ui.ringkasan-galat class="mb-6"/>

        <form method="POST" action="{{ route('pengguna.store') }}">
            @csrf

            <x-ui.kartu judul="Data Akun Baru"
                        deskripsi="Identitas pengguna dan tingkat aksesnya di dalam sistem.">
                <div class="space-y-5">
                    <x-ui.kolom nama="name" label="Nama Lengkap" wajib>
                        <x-ui.input nama="name" required autofocus placeholder="misalnya: Rahmat Hasan"/>
                    </x-ui.kolom>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.kolom nama="username" label="Username" wajib
                                    petunjuk="Huruf, angka, titik, garis bawah, atau tanda hubung.">
                            <x-ui.input nama="username" required placeholder="tanpa spasi"/>
                        </x-ui.kolom>

                        <x-ui.kolom nama="email" label="Email" wajib>
                            <x-ui.input nama="email" tipe="email" required placeholder="nama@bpbd.gorontaloprov.go.id"/>
                        </x-ui.kolom>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.kolom nama="role" label="Role" wajib
                                    petunjuk="Admin mengelola sistem, Pimpinan hanya membaca, Petugas terbatas.">
                            <x-ui.pilihan nama="role" :opsi="App\Models\User::daftarRole()" :nilai="App\Models\User::ROLE_PETUGAS"/>
                        </x-ui.kolom>

                        <x-ui.kolom nama="aktif" label="Status Akun" wajib
                                    petunjuk="Akun tidak aktif tidak dapat masuk ke sistem.">
                            <x-ui.pilihan nama="aktif" :opsi="[1 => 'Aktif', 0 => 'Tidak Aktif']" :nilai="1"/>
                        </x-ui.kolom>
                    </div>
                </div>
            </x-ui.kartu>

            <x-ui.kartu judul="Password Sementara" class="mt-6"
                        deskripsi="Sampaikan password ini kepada pengguna. Sistem akan meminta ia menggantinya saat login pertama, sehingga password akhir tidak diketahui administrator.">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.kolom nama="password" label="Password Sementara" wajib petunjuk="Minimal 8 karakter.">
                        <x-ui.password nama="password" autocomplete="new-password" required/>
                    </x-ui.kolom>

                    <x-ui.kolom nama="password_confirmation" label="Konfirmasi Password" wajib>
                        <x-ui.password nama="password_confirmation" autocomplete="new-password" required/>
                    </x-ui.kolom>
                </div>

                <x-slot:kaki>
                    <x-ui.tombol varian="sekunder" :href="route('pengguna.index')">Batal</x-ui.tombol>
                    <x-ui.tombol>Simpan</x-ui.tombol>
                </x-slot:kaki>
            </x-ui.kartu>
        </form>
    </div>

@endsection
