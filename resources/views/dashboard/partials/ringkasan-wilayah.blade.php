{{-- Ringkasan wilayah bergaya infografis BPBD: peta provinsi sebagai gambaran
     utama, lima angka kunci di bawahnya.

     Seluruh angkanya berasal dari `RekapPenyaluran` dengan filter periode yang
     sedang aktif — sama seperti kartu ringkasan di atasnya — sehingga peta dan
     angka tidak pernah bercerita berbeda. --}}

@php
    $angkaWilayah = [
        ['label' => 'Kecamatan', 'nilai' => $jumlahKecamatanPenerima, 'ikon' => 'building', 'catatan' => 'menerima bantuan'],
        ['label' => 'Desa / Kelurahan', 'nilai' => $jumlahWilayahPenerima, 'ikon' => 'rumah', 'catatan' => 'menerima bantuan'],
        ['label' => 'KK Terdampak', 'nilai' => $totalKk, 'ikon' => 'users', 'catatan' => 'kepala keluarga'],
        ['label' => 'Jiwa Terdampak', 'nilai' => $totalJiwa, 'ikon' => 'orang', 'catatan' => 'jiwa'],
        ['label' => 'Liter Tersalur', 'nilai' => $totalVolume, 'ikon' => 'tetesan', 'catatan' => 'liter air bersih'],
    ];
@endphp

<x-ui.kartu class="mt-4">
    <div class="text-center">
        <h2 class="text-base font-semibold uppercase tracking-[0.08em] text-navy-900 sm:text-lg">
            Ringkasan Wilayah Penyaluran Air Bersih
        </h2>
        <p class="mt-1 text-sm font-medium uppercase tracking-[0.12em] text-slate-500">
            Provinsi Gorontalo
        </p>

        <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-air-50 px-3 py-1
                  text-xs font-semibold text-air-800">
            <x-ikon nama="kalender" class="size-3.5"/>
            {{ $labelPeriode }}
        </p>
    </div>

    <x-ui.peta-gorontalo :rekap="$rekapKabupaten" class="mx-auto mt-6 max-w-4xl"/>

    <dl class="mt-6 grid grid-cols-2 gap-x-4 gap-y-6 border-t border-slate-100 pt-6
               sm:grid-cols-3 lg:grid-cols-5">
        @foreach ($angkaWilayah as $angka)
            <div class="flex flex-col items-center text-center">
                <span class="flex size-10 items-center justify-center rounded-full bg-air-50 text-air-700">
                    <x-ikon :nama="$angka['ikon']" class="size-5"/>
                </span>

                <dd class="mt-2 text-2xl font-semibold tracking-tight text-navy-900 sm:text-[1.75rem]">
                    {{ number_format($angka['nilai'], 0, ',', '.') }}
                </dd>

                <dt class="text-xs font-semibold uppercase tracking-[0.1em] text-navy-900">
                    {{ $angka['label'] }}
                </dt>

                <p class="mt-0.5 text-xs text-slate-400">{{ $angka['catatan'] }}</p>
            </div>
        @endforeach
    </dl>
</x-ui.kartu>
