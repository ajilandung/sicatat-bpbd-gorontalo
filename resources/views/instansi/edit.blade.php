@extends('layouts.app')

@section('judul', 'Ubah Instansi')

@section('konten')

    <div class="mx-auto max-w-2xl">
        <x-ui.kepala-halaman
            :judul="$instansi->nama"
            deskripsi="Perbaiki nama, singkatan, atau kontak instansi pelaksana."
            :kembali="route('instansi.index')"
            kembali-label="Kembali ke daftar instansi"/>

        <x-ui.ringkasan-galat class="mb-6"/>

        @if ($jumlahPenyaluran > 0)
            <x-ui.notifikasi jenis="info" class="mb-6">
                Instansi ini sudah tercatat pada {{ $jumlahPenyaluran }} kegiatan penyaluran.
                Karena itu datanya tidak dapat dihapus — bila sudah tidak terlibat, ubah statusnya menjadi tidak aktif.
            </x-ui.notifikasi>
        @endif

        <form method="POST" action="{{ route('instansi.update', $instansi) }}">
            @csrf
            @method('PUT')

            <x-ui.kartu judul="Data Instansi"
                        deskripsi="Nama ditulis lengkap agar tidak rancu di laporan; singkatan dipakai pada tabel dan export.">
                <div class="space-y-5">
                    <x-ui.kolom nama="nama" label="Nama Instansi" wajib>
                        <x-ui.input nama="nama" :nilai="$instansi->nama" required autofocus/>
                    </x-ui.kolom>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.kolom nama="singkatan" label="Singkatan" petunjuk="Boleh dikosongkan.">
                            <x-ui.input nama="singkatan" :nilai="$instansi->singkatan"/>
                        </x-ui.kolom>

                        <x-ui.kolom nama="telepon" label="Nomor Telepon" petunjuk="Boleh dikosongkan.">
                            <x-ui.input nama="telepon" :nilai="$instansi->telepon"/>
                        </x-ui.kolom>
                    </div>

                    <x-ui.kolom nama="alamat" label="Alamat" petunjuk="Boleh dikosongkan.">
                        <x-ui.input nama="alamat" :nilai="$instansi->alamat"/>
                    </x-ui.kolom>

                    <x-ui.kolom nama="aktif" label="Status" wajib
                                petunjuk="Hanya instansi aktif yang muncul di form penyaluran.">
                        <x-ui.pilihan nama="aktif" :opsi="[1 => 'Aktif', 0 => 'Tidak Aktif']" :nilai="$instansi->aktif ? 1 : 0"/>
                    </x-ui.kolom>
                </div>

                <x-slot:kaki>
                    <x-ui.tombol varian="sekunder" :href="route('instansi.index')">Batal</x-ui.tombol>
                    <x-ui.tombol>Simpan Perubahan</x-ui.tombol>
                </x-slot:kaki>
            </x-ui.kartu>
        </form>
    </div>

@endsection
