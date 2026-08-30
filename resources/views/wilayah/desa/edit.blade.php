@extends('layouts.app')

@section('judul', 'Ubah Desa/Kelurahan')

@section('konten')

    <div class="mx-auto max-w-2xl">
        <x-ui.kepala-halaman
            :judul="$desa->namaLengkap()"
            :deskripsi="$desa->alamatWilayah()"
            :kembali="route('wilayah.desa.index')"
            kembali-label="Kembali ke daftar desa"/>

        <x-ui.ringkasan-galat class="mb-6"/>

        @if ($jumlahPenyaluran > 0)
            <x-ui.notifikasi jenis="info" class="mb-6">
                Wilayah ini sudah tercatat pada {{ $jumlahPenyaluran }} kegiatan penyaluran.
                Karena itu datanya tidak dapat dihapus — bila sudah tidak dipakai, ubah statusnya menjadi tidak aktif.
            </x-ui.notifikasi>
        @endif

        <form method="POST" action="{{ route('wilayah.desa.update', $desa) }}">
            @csrf
            @method('PUT')

            <x-ui.kartu judul="Data Wilayah"
                        deskripsi="Nama desa boleh sama dengan desa di kecamatan lain, tetapi tidak boleh sama dengan desa lain di dalam satu kecamatan.">
                <div class="space-y-5">
                    <x-ui.kolom nama="kecamatan_id" label="Kecamatan" wajib>
                        <x-ui.pilihan nama="kecamatan_id" :opsi="$opsiKecamatan" :nilai="$desa->kecamatan_id" required/>
                    </x-ui.kolom>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.kolom nama="nama" label="Nama Desa/Kelurahan" wajib>
                            <x-ui.input nama="nama" :nilai="$desa->nama" required/>
                        </x-ui.kolom>

                        <x-ui.kolom nama="jenis" label="Jenis Wilayah" wajib>
                            <x-ui.pilihan nama="jenis" :opsi="App\Models\Desa::daftarJenis()" :nilai="$desa->jenis"/>
                        </x-ui.kolom>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.kolom nama="kode" label="Kode Wilayah" petunjuk="Boleh dikosongkan.">
                            <x-ui.input nama="kode" :nilai="$desa->kode"/>
                        </x-ui.kolom>

                        <x-ui.kolom nama="aktif" label="Status" wajib
                                    petunjuk="Hanya wilayah aktif yang muncul di form penyaluran.">
                            <x-ui.pilihan nama="aktif" :opsi="[1 => 'Aktif', 0 => 'Tidak Aktif']" :nilai="$desa->aktif ? 1 : 0"/>
                        </x-ui.kolom>
                    </div>
                </div>

                <x-slot:kaki>
                    <x-ui.tombol varian="sekunder" :href="route('wilayah.desa.index')">Batal</x-ui.tombol>
                    <x-ui.tombol>Simpan Perubahan</x-ui.tombol>
                </x-slot:kaki>
            </x-ui.kartu>
        </form>
    </div>

@endsection
