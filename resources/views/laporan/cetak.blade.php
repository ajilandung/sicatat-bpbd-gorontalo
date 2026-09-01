{{-- Halaman siap cetak, mengikuti bentuk laporan Pusdalops PB BPBD Provinsi
     Gorontalo. Sengaja berdiri sendiri di luar `layouts.app` dan memakai CSS
     sendiri, bukan kelas Tailwind aplikasi: yang dibutuhkan halaman ini adalah
     ukuran titik, garis tabel, dan aturan pemenggalan halaman kertas — bukan
     tampilan layar. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Laporan Penyaluran Bantuan Air Bersih — Sicatat</title>

    <style>
        @page { size: A4; margin: 1.5cm 1.6cm; }

        * { box-sizing: border-box; }

        /* Jarak antarbaris diatur sendiri per bagian, bukan lewat margin bawaan
           paragraf, supaya tinggi baris di dalam sel tabel tetap rapat. */
        p { margin: 0; }

        body {
            margin: 0;
            background: #e2e8f0;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            line-height: 1.45;
        }

        .kertas {
            width: 21cm;
            min-height: 29.7cm;
            margin: 1.5cm auto;
            padding: 1.5cm 1.6cm;
            background: #fff;
            box-shadow: 0 10px 30px rgb(15 23 42 / 0.18);
        }

        /* ── Kop ──
           Disusun sebagai tabel tiga kolom, bukan flexbox, karena tabel adalah
           satu-satunya tata letak yang diperlakukan sama oleh mesin cetak semua
           peramban. Kedua kolom lambang berlebar tetap dan sama, sehingga blok
           teks di tengah benar-benar berada di tengah kertas berapa pun lebar
           gambar yang dipasang. */
        /* Dua ukuran yang menentukan kop, dikumpulkan di sini supaya mudah
           disetel ulang: tinggi lambang, dan lebar kolom tempat lambang itu
           diletakkan. Lambang diletakkan di tengah kolomnya, sehingga titik
           tengah lambang kiri dan kanan selalu berjarak sama dari tepi kertas
           — simetris berapa pun perbedaan lebar kedua gambar. */
        :root {
            --tinggi-logo: 1.8cm;
            --kolom-logo: 3.4cm;
        }

        .kop { text-align: center; line-height: 1.3; }

        table.kop-atas { width: 100%; border-collapse: collapse; table-layout: fixed; }

        /* `vertical-align: middle` membuat titik tengah kedua lambang berada
           pada garis horizontal yang sama. */
        table.kop-atas td { padding: 0; vertical-align: middle; }
        table.kop-atas td.logo { width: var(--kolom-logo); text-align: center; }

        /* `print-color-adjust` menahan peramban memucatkan lambang saat opsi
           "grafik latar" dimatikan pada dialog cetak. Hanya tinggi yang
           dipatok, lebarnya mengikuti — rasio asli tiap lambang terjaga. */
        table.kop-atas img {
            height: var(--tinggi-logo); width: auto; max-width: 100%;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }

        .kop .instansi { font-size: 14pt; font-weight: 700; letter-spacing: .02em; }
        .kop .unit { font-size: 12pt; font-weight: 700; }

        /* Dua baris ini melebar penuh di bawah lambang, bukan terjepit di kolom
           tengah: pada 8,5pt lebarnya 15,4 cm dan 14,2 cm, sedangkan kolom di
           antara kedua lambang hanya 11 cm — kalau dipaksa masuk ke sana,
           keduanya patah menjadi dua baris dan kop kehilangan bentuknya. */
        .kop .keterangan { font-size: 8.5pt; margin-top: .1cm; }
        .kop .alamat { font-size: 8.5pt; margin-top: .15cm; }

        .garis-kop { border: 0; border-top: 3px solid #000; border-bottom: 1px solid #000; height: 3px; margin: .3cm 0 .5cm; }

        .judul { text-align: center; font-size: 12pt; font-weight: 700; text-transform: uppercase; }
        .periode { text-align: center; font-size: 9.5pt; margin-top: .1cm; }

        /* ── Bagian ── */
        .bagian { margin-top: .6cm; }
        .bagian > h2 { font-size: 11pt; font-weight: 700; text-transform: uppercase; margin: 0 0 .2cm; }

        .info { border-collapse: collapse; }
        .info th { text-align: left; font-weight: 400; text-transform: uppercase; vertical-align: top; padding: .05cm .2cm .05cm .6cm; }
        .info td { vertical-align: top; padding: .05cm 0; text-transform: uppercase; font-weight: 700; }
        .info td::before { content: ': '; font-weight: 400; }

        /* ── Tabel kegiatan ── */
        .tanggal { margin: .45cm 0 .15cm; font-weight: 700; text-transform: uppercase; }

        table.kegiatan { width: 100%; border-collapse: collapse; font-size: 9.5pt; page-break-inside: auto; }
        table.kegiatan th,
        table.kegiatan td { border: 1px solid #000; padding: .12cm .18cm; vertical-align: top; }
        table.kegiatan thead th { text-align: center; text-transform: uppercase; background: #e5e7eb; }
        table.kegiatan thead { display: table-header-group; }
        table.kegiatan tr { page-break-inside: avoid; }

        .no { width: 1.1cm; text-align: center; }
        .warga { width: 3.4cm; text-align: center; }
        .pelaksana { width: 4.6cm; }
        .volume { width: 3cm; text-align: right; white-space: nowrap; }

        .kabupaten td { font-weight: 700; text-transform: uppercase; background: #f3f4f6; }
        .kecamatan { text-transform: uppercase; }
        .desa { margin: 0 0 0 .45cm; }
        .total td { font-weight: 700; text-transform: uppercase; }
        .kosong td { text-align: center; padding: .5cm; text-transform: uppercase; }

        .rekap { margin-top: .35cm; font-weight: 700; text-transform: uppercase; }

        /* ── Tanda tangan ── */
        .ttd { margin-top: .8cm; width: 100%; page-break-inside: avoid; }
        .ttd .blok { width: 8cm; margin-left: auto; text-align: center; text-transform: uppercase; }
        .ttd .jarak { height: 2.2cm; }
        .ttd .nama { font-weight: 700; text-decoration: underline; }

        /* ── Batang alat, hanya tampil di layar ── */
        .alat {
            position: sticky; top: 0; z-index: 10;
            display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;
            padding: .75rem 1rem; background: #0f2540; color: #fff;
            font-size: 10pt;
        }
        .alat p { margin: 0 auto 0 0; }
        .alat a, .alat button {
            font: inherit; font-weight: 700; cursor: pointer;
            padding: .45rem .9rem; border-radius: .4rem; border: 1px solid rgb(255 255 255 / .35);
            background: transparent; color: #fff; text-decoration: none;
        }
        .alat button { background: #fff; color: #0f2540; border-color: #fff; }

        /* ── Lampiran dokumentasi ──
           Foto diambil dari kegiatan yang sama dengan tabel di atas, lalu
           dikelompokkan menurut tanggal kegiatannya. Dua foto sejajar per baris
           agar muat pada kertas A4 tanpa mengecil sampai sulit dilihat. */
        .lampiran { page-break-before: always; }

        .dok-tanggal {
            margin-top: 0.5cm;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            font-weight: bold;
            page-break-after: avoid;
        }

        .dok-kegiatan { margin-top: 0.3cm; }
        .dok-lokasi { font-weight: bold; font-size: 10pt; page-break-after: avoid; }
        .dok-desa { font-size: 9.5pt; color: #333; page-break-after: avoid; }

        .dok-foto {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3cm;
            margin-top: 0.25cm;
        }

        /* Tiap foto utuh dalam satu halaman: gambar yang terpotong garis
           pemenggalan kertas membuat lampiran tidak dapat dipakai. */
        .dok-foto figure {
            width: calc(50% - 0.15cm);
            margin: 0;
            page-break-inside: avoid;
        }

        .dok-foto img {
            display: block;
            width: 100%;
            height: 5.6cm;
            object-fit: cover;
            border: 1px solid #64748b;
        }

        @media print {
            body { background: #fff; }
            .kertas { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
            .alat { display: none; }

            /* Foto harus ikut tercetak walaupun peramban sedang menghemat
               tinta; tanpa ini sebagian peramban mengosongkan gambar. */
            .dok-foto img { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="alat">
    <p>Pilih <strong>Simpan sebagai PDF</strong> pada dialog cetak untuk menyimpan laporan ini sebagai berkas PDF.</p>
    <a href="{{ route('laporan.index', array_filter($filter, fn ($nilai) => $nilai !== '')) }}">Kembali</a>
    <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</div>

<div class="kertas">

    @php
        $kop = config('laporan.kop');

        // Penanda versi berkas supaya peramban tidak menampilkan lambang lama
        // dari cache setelah gambarnya diganti.
        $berkasLogo = fn (?string $jalur) => $jalur && file_exists(public_path($jalur))
            ? asset($jalur).'?v='.filemtime(public_path($jalur))
            : null;
    @endphp

    <div class="kop">
        <table class="kop-atas">
            <tr>
                <td class="logo">
                    @if ($berkasLogo($kop['logo_kiri']))
                        <img src="{{ $berkasLogo($kop['logo_kiri']) }}" alt="Lambang Provinsi Gorontalo">
                    @endif
                </td>

                <td class="teks">
                    <p class="instansi">{{ $kop['instansi'] }}</p>
                    <p class="unit">{{ $kop['unit'] }}</p>
                </td>

                <td class="logo">
                    @if ($berkasLogo($kop['logo_kanan']))
                        <img src="{{ $berkasLogo($kop['logo_kanan']) }}" alt="Logo BPBD Provinsi Gorontalo">
                    @endif
                </td>
            </tr>
        </table>

        <p class="keterangan">{{ $kop['keterangan_unit'] }}</p>
        <p class="alamat">{{ $kop['alamat'] }}</p>
    </div>

    <hr class="garis-kop">

    <p class="judul">Laporan Penyaluran Bantuan Air Bersih</p>
    <p class="periode">Periode data: {{ $rekap->labelPeriode() }}</p>

    {{-- ── A. Info kejadian ── --}}
    <div class="bagian">
        <h2>A. Info Kejadian</h2>

        <table class="info">
            @if ($identitas['jenis_bencana'])
                <tr><th>Jenis bencana</th><td>{{ $identitas['jenis_bencana'] }}</td></tr>
            @endif

            @if ($identitas['tanggal_kejadian'])
                <tr>
                    <th>Tanggal kejadian</th>
                    <td>{{ \Illuminate\Support\Carbon::parse($identitas['tanggal_kejadian'])->translatedFormat('l, j F Y') }}</td>
                </tr>
            @endif

            @if ($identitas['waktu_kejadian'])
                <tr><th>Waktu kejadian</th><td>Pukul {{ $identitas['waktu_kejadian'] }}</td></tr>
            @endif

            @if ($identitas['lokasi_kejadian'])
                <tr><th>Lokasi</th><td>{{ $identitas['lokasi_kejadian'] }}</td></tr>
            @endif

            <tr>
                <th>{{ $identitas['update_ke'] ? 'Update '.$identitas['update_ke'] : 'Laporan dibuat' }}</th>
                <td>{{ now()->translatedFormat('l, j F Y') }} Pukul {{ now()->format('H:i') }} WITA</td>
            </tr>
        </table>
    </div>

    {{-- ── B. Upaya yang dilakukan ── --}}
    <div class="bagian">
        <h2>B. Upaya yang Dilakukan</h2>

        @forelse ($perTanggal as $hari)
            <p class="tanggal">{{ $hari['tanggal']->translatedFormat('l, j F Y') }}</p>

            <table class="kegiatan">
                <thead>
                    <tr>
                        <th class="no">No</th>
                        <th>Lokasi</th>
                        <th class="warga">Jumlah KK / Jiwa</th>
                        <th class="pelaksana">Pelaksana</th>
                        <th class="volume">Jumlah Air Tersalur</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($hari['kelompok'] as $urutanKabupaten => $kelompok)
                        <tr class="kabupaten">
                            <td class="no">{{ chr(65 + $urutanKabupaten) }}</td>
                            <td colspan="4">{{ $kelompok['kabupaten'] }}</td>
                        </tr>

                        @foreach ($kelompok['kegiatan'] as $urutan => $penyaluran)
                            <tr>
                                <td class="no">{{ $urutan + 1 }}</td>

                                <td>
                                    @foreach ($penyaluran->wilayahPerKecamatan() as $wilayah)
                                        <p class="kecamatan">Kec. {{ $wilayah['kecamatan'] }}</p>
                                        @foreach ($wilayah['desa'] as $desa)
                                            <p class="desa">- {{ $desa }}</p>
                                        @endforeach
                                    @endforeach
                                </td>

                                <td class="warga">
                                    {{ $penyaluran->jumlah_kk !== null ? number_format($penyaluran->jumlah_kk, 0, ',', '.') : '-' }} KK /
                                    {{ $penyaluran->jumlah_jiwa !== null ? number_format($penyaluran->jumlah_jiwa, 0, ',', '.') : '-' }} Jiwa
                                </td>

                                <td class="pelaksana">
                                    @forelse ($penyaluran->instansis as $instansi)
                                        <p>- {{ $instansi->nama }}</p>
                                    @empty
                                        <p>-</p>
                                    @endforelse
                                </td>

                                <td class="volume">
                                    {{ number_format($penyaluran->volume_liter, 0, ',', '.') }} Liter
                                </td>
                            </tr>
                        @endforeach
                    @endforeach

                    <tr class="total">
                        <td colspan="4">Total air tersalur</td>
                        <td class="volume">{{ number_format($hari['total_liter'], 0, ',', '.') }} Liter</td>
                    </tr>
                </tbody>
            </table>
        @empty
            <table class="kegiatan">
                <tbody>
                    <tr class="kosong">
                        <td>Tidak ada kegiatan penyaluran pada periode yang dipilih</td>
                    </tr>
                </tbody>
            </table>
        @endforelse

        @if ($perTanggal->isNotEmpty())
            <p class="rekap">
                Total keseluruhan:
                {{ number_format($rekap->jumlahKegiatan(), 0, ',', '.') }} kegiatan,
                {{ number_format($rekap->jumlahKecamatanPenerima(), 0, ',', '.') }} kecamatan,
                {{ number_format($rekap->jumlahWilayahPenerima(), 0, ',', '.') }} desa/kelurahan,
                {{ number_format($rekap->totalVolume(), 0, ',', '.') }} liter air tersalur.
            </p>

            {{-- Jumlah KK dan jiwa disertai penanda kelengkapannya: banyak laporan
                 lapangan tidak mencantumkan jumlah warga, dan tanpa keterangan ini
                 totalnya akan terbaca seolah mencakup seluruh kegiatan. --}}
            <p class="rekap">
                Jumlah warga terlayani yang tercatat:
                {{ number_format($rekap->totalKk(), 0, ',', '.') }} KK /
                {{ number_format($rekap->totalJiwa(), 0, ',', '.') }} Jiwa
                @if ($rekap->kegiatanTanpaJumlahWarga() > 0)
                    ({{ $rekap->kegiatanTanpaJumlahWarga() }} kegiatan belum mencantumkan jumlah warga)
                @endif
            </p>
        @endif
    </div>

    {{-- ── C. Penutup ── --}}
    <div class="bagian">
        <h2>C. Penutup</h2>
        <p>Demikian laporan Pusdalops PB BPBD Provinsi Gorontalo, terima kasih.</p>

        <div class="ttd">
            <div class="blok">
                <p>Mengetahui :</p>
                <p>{{ $identitas['penandatangan_jabatan'] }}</p>
                <div class="jarak"></div>
                <p class="nama">{{ $identitas['penandatangan_nama'] ?: '.....................................' }}</p>
                @if ($identitas['penandatangan_pangkat'])
                    <p>{{ $identitas['penandatangan_pangkat'] }}</p>
                @endif
                @if ($identitas['penandatangan_nip'])
                    <p>NIP {{ $identitas['penandatangan_nip'] }}</p>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Lampiran dokumentasi ──
     Foto tidak dicari berdasarkan tanggal, melainkan diambil dari kegiatan
     yang sudah tersaring pada laporan ini, lalu dikelompokkan menurut tanggal
     kegiatannya. Dengan begitu foto susulan yang diunggah beberapa hari setelah
     kegiatan tetap muncul di bawah tanggal kejadiannya. --}}
@php
    $hariBerfoto = $perTanggal
        ->map(fn (array $hari) => [
            'tanggal' => $hari['tanggal'],
            'kegiatan' => $hari['kelompok']
                ->flatMap(fn (array $kelompok) => $kelompok['kegiatan'])
                ->filter(fn ($penyaluran) => $penyaluran->fotos->isNotEmpty())
                ->values(),
        ])
        ->filter(fn (array $hari) => $hari['kegiatan']->isNotEmpty())
        ->values();
@endphp

@if ($lampiran && $hariBerfoto->isNotEmpty())
    <div class="kertas lampiran">
        <p class="judul">Lampiran Dokumentasi Kegiatan Penyaluran Air Bersih</p>

        @foreach ($hariBerfoto as $hari)
            <p class="dok-tanggal">{{ $hari['tanggal']->translatedFormat('j F Y') }}</p>

            @foreach ($hari['kegiatan'] as $penyaluran)
                <div class="dok-kegiatan">
                    <p class="dok-lokasi">
                        {{ $penyaluran->kabupatenTersentuh()->implode(' / ') ?: '—' }}
                        @php
                            $kecamatan = $penyaluran->wilayahPerKecamatan()
                                ->pluck('kecamatan')
                                ->map(fn (string $nama) => 'Kec. '.$nama)
                                ->implode(', ');
                        @endphp
                        @if ($kecamatan !== '')
                            &rarr; {{ $kecamatan }}
                        @endif
                    </p>

                    <p class="dok-desa">{{ $penyaluran->desas->map->namaLengkap()->implode(', ') }}</p>

                    <div class="dok-foto">
                        @foreach ($penyaluran->fotos as $foto)
                            <figure>
                                <img src="{{ $foto->url() }}"
                                     alt="Dokumentasi kegiatan {{ $hari['tanggal']->translatedFormat('j F Y') }}">
                            </figure>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endforeach
    </div>
@endif

</body>
</html>
