<?php

/*
|--------------------------------------------------------------------------
| Identitas Laporan Cetak
|--------------------------------------------------------------------------
|
| Kop surat dan isian bawaan halaman cetak laporan, mengikuti bentuk dokumen
| "Laporan Sementara Kejadian dan Dampak Bencana" milik Pusdalops PB BPBD
| Provinsi Gorontalo.
|
| Nilai di bawah ini hanya bawaan: admin masih dapat menyunting setiap isian
| pada form sebelum mencetak, dan isian terakhir yang dipakai akan diingat
| sistem untuk laporan berikutnya. Yang perlu diubah di berkas ini hanyalah
| hal yang jarang berganti, misalnya alamat kantor atau pejabat penanda
| tangan.
|
*/

return [

    'kop' => [
        // Lambang di kiri dan kanan kop, relatif terhadap folder `public`.
        // Dikosongkan bila salah satunya tidak ingin ditampilkan.
        'logo_kiri' => 'images/logo-provinsi-gorontalo.jpg',
        'logo_kanan' => 'images/logo-bpbd-gorontalo.jpg',

        'instansi' => 'BPBD PROVINSI GORONTALO',
        'unit' => 'KOORDALOPS PB',
        'keterangan_unit' => '(KOORDINATOR PENGENDALI OPERASI DARURAT PENANGGULANGAN BENCANA)',
        'alamat' => 'JL. SULTAN AMAY KEL. PADEBUOLO, KEC. KOTA TIMUR KOTA GORONTALO',
    ],

    'identitas' => [
        'jenis_bencana' => 'KEKERINGAN',
        'tanggal_kejadian' => null,
        'waktu_kejadian' => '',
        'lokasi_kejadian' => '',
        'update_ke' => '',
        'penandatangan_jabatan' => 'KEPALA PELAKSANA',
        'penandatangan_nama' => '',
        'penandatangan_pangkat' => '',
        'penandatangan_nip' => '',
    ],

];
