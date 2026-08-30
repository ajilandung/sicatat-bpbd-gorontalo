{{-- Navigasi tiga tingkat data wilayah, dipakai ulang oleh ketiga halamannya. --}}
<x-ui.tab label="Bagian data wilayah" :item="[
    ['label' => 'Kabupaten/Kota', 'route' => 'wilayah.kabupaten.index', 'aktifJika' => 'wilayah.kabupaten.*'],
    ['label' => 'Kecamatan', 'route' => 'wilayah.kecamatan.index', 'aktifJika' => 'wilayah.kecamatan.*'],
    ['label' => 'Desa/Kelurahan', 'route' => 'wilayah.desa.index', 'aktifJika' => 'wilayah.desa.*'],
]"/>
