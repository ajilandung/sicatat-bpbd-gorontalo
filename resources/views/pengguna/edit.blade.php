@extends('layouts.app')

@section('judul', 'Edit Pengguna')

@section('konten')

    @php $akunSendiri = $pengguna->is(auth()->user()); @endphp

    <div class="mx-auto max-w-2xl">
        <x-ui.kepala-halaman
            judul="Edit Pengguna"
            :deskripsi="'Memperbarui data akun '.$pengguna->name.'.'"
            :kembali="route('pengguna.index')"
            kembali-label="Kembali ke daftar pengguna"/>

        @if ($akunSendiri)
            <x-ui.notifikasi jenis="peringatan" judul="Ini akun Anda sendiri" class="mb-6">
                Role dan status akun tidak dapat diubah dari sini agar administrator tidak mengunci
                dirinya di luar sistem.
            </x-ui.notifikasi>
        @endif

        <x-ui.ringkasan-galat class="mb-6"/>

        <form method="POST" action="{{ route('pengguna.update', $pengguna) }}">
            @csrf
            @method('PUT')

            <x-ui.kartu judul="Data Akun"
                        deskripsi="Password tidak diubah dari halaman ini. Gunakan aksi Reset Password bila pengguna lupa passwordnya.">
                <div class="space-y-5">
                    <x-ui.kolom nama="name" label="Nama Lengkap" wajib>
                        <x-ui.input nama="name" :nilai="$pengguna->name" required autofocus/>
                    </x-ui.kolom>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.kolom nama="username" label="Username" wajib>
                            <x-ui.input nama="username" :nilai="$pengguna->username" required/>
                        </x-ui.kolom>

                        <x-ui.kolom nama="email" label="Email" wajib>
                            <x-ui.input nama="email" tipe="email" :nilai="$pengguna->email" required/>
                        </x-ui.kolom>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.kolom nama="role" label="Role" wajib
                                    :petunjuk="$akunSendiri ? 'Terkunci untuk akun sendiri.' : null">
                            <x-ui.pilihan nama="role" :opsi="App\Models\User::daftarRole()" :nilai="$pengguna->role"
                                          :disabled="$akunSendiri"/>
                            @if ($akunSendiri)
                                <input type="hidden" name="role" value="{{ $pengguna->role }}">
                            @endif
                        </x-ui.kolom>

                        <x-ui.kolom nama="aktif" label="Status Akun" wajib
                                    :petunjuk="$akunSendiri ? 'Terkunci untuk akun sendiri.' : null">
                            <x-ui.pilihan nama="aktif" :opsi="[1 => 'Aktif', 0 => 'Tidak Aktif']"
                                          :nilai="$pengguna->aktif ? 1 : 0" :disabled="$akunSendiri"/>
                            @if ($akunSendiri)
                                <input type="hidden" name="aktif" value="1">
                            @endif
                        </x-ui.kolom>
                    </div>
                </div>

                <x-slot:kaki>
                    <x-ui.tombol varian="sekunder" :href="route('pengguna.index')">Batal</x-ui.tombol>
                    <x-ui.tombol>Simpan Perubahan</x-ui.tombol>
                </x-slot:kaki>
            </x-ui.kartu>
        </form>
    </div>

@endsection
