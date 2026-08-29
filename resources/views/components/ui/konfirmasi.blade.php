@props([
    'judul',
    'pesan',
    'aksi',
    'metode' => 'PATCH',
    'label',
    'ikon' => null,
    'varian' => 'sekunder',
    'labelKonfirmasi' => 'Ya, lanjutkan',
    'varianKonfirmasi' => 'utama',
])

{{-- Aksi yang meminta konfirmasi lewat dialog sebelum form dikirim.
     Dialognya memakai posisi fixed sehingga tidak terpotong kontainer tabel. --}}
<div x-data="{ buka: false }" class="inline-block">
    @if ($ikon)
        <x-ui.tombol-ikon :ikon="$ikon" :label="$label" :varian="$varian === 'bahaya' ? 'bahaya' : 'netral'"
                          @click="buka = true" {{ $attributes }}/>
    @else
        <x-ui.tombol tipe="button" :varian="$varian" ukuran="kecil" @click="buka = true" {{ $attributes }}>
            {{ $label }}
        </x-ui.tombol>
    @endif

    <div x-show="buka" x-cloak class="relative z-50" role="dialog" aria-modal="true"
         @keydown.escape.window="buka = false">
        <div x-show="buka" x-transition.opacity class="fixed inset-0 bg-navy-950/50 backdrop-blur-[1px]"></div>

        <div class="fixed inset-0 flex items-end justify-center p-4 sm:items-center">
            <div x-show="buka" x-transition @click.outside="buka = false"
                 class="w-full max-w-md rounded-xl bg-white p-6 text-left shadow-naik">
                <h2 class="text-base font-semibold text-navy-900">{{ $judul }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $pesan }}</p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-ui.tombol tipe="button" varian="sekunder" @click="buka = false">Batal</x-ui.tombol>

                    <form method="POST" action="{{ $aksi }}">
                        @csrf
                        @method($metode)
                        <x-ui.tombol :varian="$varianKonfirmasi" class="w-full sm:w-auto">{{ $labelKonfirmasi }}</x-ui.tombol>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
