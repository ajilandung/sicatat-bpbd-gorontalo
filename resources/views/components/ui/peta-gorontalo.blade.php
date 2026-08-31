@props(['rekap' => null])

@php
    // Batas wilayahnya data, bukan gambar: satu path per kabupaten/kota,
    // dikunci dengan kode Kemendagri yang sama dengan tabel `kabupatens`,
    // sehingga pewarnaannya dihitung dari isi basis data — bukan ditanam
    // di dalam berkas peta.
    $peta = require resource_path('peta/gorontalo-kabupaten.php');
    $rekap = collect($rekap);

    $sebutan = fn (array $wilayah) => ($wilayah['jenis'] === 'kota' ? 'Kota ' : 'Kab. ').$wilayah['nama'];

    $angka = fn (int|string $nilai) => number_format((int) $nilai, 0, ',', '.');

    $keterangan = fn (?object $data) => $data
        ? $angka($data->jumlah_kegiatan).' kegiatan di '.$angka($data->jumlah_desa).' desa/kelurahan'
        : 'belum ada penyaluran tercatat';
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <svg viewBox="{{ $peta['viewBox'] }}" class="h-auto w-full" role="img"
         aria-label="Peta Provinsi Gorontalo. Kabupaten dan kota yang menerima bantuan air bersih ditandai biru; rinciannya tercantum pada daftar di bawah peta.">
        @foreach ($peta['wilayah'] as $wilayah)
            @php $data = $rekap->get($wilayah['kode']); @endphp

            <path d="{{ $wilayah['d'] }}"
                  class="{{ $data ? 'fill-air-600' : 'fill-slate-200' }} stroke-white transition-colors"
                  stroke-width="1.5" stroke-linejoin="round">
                <title>{{ $sebutan($wilayah) }}: {{ $keterangan($data) }}</title>
            </path>
        @endforeach
    </svg>

    {{-- Keterangan wilayah ditulis sebagai daftar, bukan hanya sebagai
         keterangan yang muncul saat kursor diarahkan: pada layar sentuh
         keterangan itu tidak pernah terlihat. --}}
    <ul class="mt-4 flex flex-wrap justify-center gap-2">
        @foreach ($peta['wilayah'] as $wilayah)
            @php $data = $rekap->get($wilayah['kode']); @endphp

            <li class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs
                       {{ $data ? 'border-air-200 bg-air-50 text-air-900' : 'border-slate-200 bg-slate-50 text-slate-500' }}">
                <span class="size-2 shrink-0 rounded-full {{ $data ? 'bg-air-600' : 'bg-slate-300' }}"></span>
                <span class="font-medium">{{ $sebutan($wilayah) }}</span>
                <span>{{ $data ? $angka($data->jumlah_kegiatan).' kegiatan' : 'belum ada' }}</span>
            </li>
        @endforeach
    </ul>
</div>
