@extends('layouts.app')

@section('judul', 'Tambah Desa/Kelurahan')

@section('konten')

    <div class="mx-auto max-w-2xl">
        <x-ui.kepala-halaman
            judul="Tambah Desa/Kelurahan"
            deskripsi="Dipakai bila ada pemekaran wilayah atau desa penerima bantuan yang belum terdaftar."
            :kembali="route('wilayah.desa.index')"
            kembali-label="Kembali ke daftar desa"/>

        <x-ui.ringkasan-galat class="mb-6"/>

        <form method="POST" action="{{ route('wilayah.desa.store') }}">
            @csrf

            <x-ui.kartu judul="Data Wilayah"
                        deskripsi="Nama desa boleh sama dengan desa di kecamatan lain — di Provinsi Gorontalo ada 61 nama desa yang dipakai lebih dari satu wilayah. Yang tidak boleh sama adalah dua desa dengan nama sama di dalam satu kecamatan.">
                <div class="space-y-5">
                    <x-ui.kolom nama="kecamatan_id" label="Kecamatan" wajib
                                petunjuk="Kabupaten/kota mengikuti kecamatan yang dipilih.">
                        <x-ui.pilihan nama="kecamatan_id" :opsi="$opsiKecamatan" kosong="Pilih kecamatan" required autofocus/>
                    </x-ui.kolom>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.kolom nama="nama" label="Nama Desa/Kelurahan" wajib>
                            <x-ui.input nama="nama" required placeholder="misalnya: Tongo"/>
                        </x-ui.kolom>

                        <x-ui.kolom nama="jenis" label="Jenis Wilayah" wajib>
                            <x-ui.pilihan nama="jenis" :opsi="App\Models\Desa::daftarJenis()"
                                          :nilai="App\Models\Desa::JENIS_DESA"/>
                        </x-ui.kolom>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.kolom nama="kode" label="Kode Wilayah"
                                    petunjuk="Kode resmi bila ada, mis. 75.01.05.2003. Boleh dikosongkan.">
                            <x-ui.input nama="kode" placeholder="opsional"/>
                        </x-ui.kolom>

                        <x-ui.kolom nama="aktif" label="Status" wajib
                                    petunjuk="Hanya wilayah aktif yang muncul di form penyaluran.">
                            <x-ui.pilihan nama="aktif" :opsi="[1 => 'Aktif', 0 => 'Tidak Aktif']" :nilai="1"/>
                        </x-ui.kolom>
                    </div>
                </div>

                <x-slot:kaki>
                    <x-ui.tombol varian="sekunder" :href="route('wilayah.desa.index')">Batal</x-ui.tombol>
                    <x-ui.tombol>Simpan</x-ui.tombol>
                </x-slot:kaki>
            </x-ui.kartu>
        </form>
    </div>

@endsection
