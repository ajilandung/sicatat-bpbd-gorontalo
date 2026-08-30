@extends('layouts.app')

@section('judul', 'Tambah Instansi')

@section('konten')

    <div class="mx-auto max-w-2xl">
        <x-ui.kepala-halaman
            judul="Tambah Instansi Pelaksana"
            deskripsi="Dipakai bila ada instansi, lembaga, atau kelompok relawan baru yang ikut menyalurkan bantuan air bersih."
            :kembali="route('instansi.index')"
            kembali-label="Kembali ke daftar instansi"/>

        <x-ui.ringkasan-galat class="mb-6"/>

        <form method="POST" action="{{ route('instansi.store') }}">
            @csrf

            <x-ui.kartu judul="Data Instansi"
                        deskripsi="Nama ditulis lengkap agar tidak rancu di laporan; singkatan dipakai pada tabel dan export supaya kolomnya tidak terlalu lebar.">
                <div class="space-y-5">
                    <x-ui.kolom nama="nama" label="Nama Instansi" wajib>
                        <x-ui.input nama="nama" required autofocus placeholder="misalnya: BPBD Kabupaten Gorontalo"/>
                    </x-ui.kolom>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.kolom nama="singkatan" label="Singkatan" petunjuk="Boleh dikosongkan.">
                            <x-ui.input nama="singkatan" placeholder="misalnya: BPBD Kab. Gorontalo"/>
                        </x-ui.kolom>

                        <x-ui.kolom nama="telepon" label="Nomor Telepon" petunjuk="Boleh dikosongkan.">
                            <x-ui.input nama="telepon" placeholder="misalnya: 0435-123456"/>
                        </x-ui.kolom>
                    </div>

                    <x-ui.kolom nama="alamat" label="Alamat" petunjuk="Boleh dikosongkan.">
                        <x-ui.input nama="alamat" placeholder="opsional"/>
                    </x-ui.kolom>

                    <x-ui.kolom nama="aktif" label="Status" wajib
                                petunjuk="Hanya instansi aktif yang muncul di form penyaluran.">
                        <x-ui.pilihan nama="aktif" :opsi="[1 => 'Aktif', 0 => 'Tidak Aktif']" :nilai="1"/>
                    </x-ui.kolom>
                </div>

                <x-slot:kaki>
                    <x-ui.tombol varian="sekunder" :href="route('instansi.index')">Batal</x-ui.tombol>
                    <x-ui.tombol>Simpan</x-ui.tombol>
                </x-slot:kaki>
            </x-ui.kartu>
        </form>
    </div>

@endsection
