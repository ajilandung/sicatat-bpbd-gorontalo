@props(['jenis' => 'info', 'judul' => null, 'dapatDitutup' => false])

@php
    [$gaya, $gayaIkon, $ikon] = match ($jenis) {
        'sukses' => ['border-emerald-200 bg-emerald-50 text-emerald-900', 'text-emerald-600', 'centang-bulat'],
        'galat' => ['border-red-200 bg-red-50 text-red-900', 'text-red-600', 'galat'],
        'peringatan' => ['border-amber-200 bg-amber-50 text-amber-900', 'text-amber-600', 'peringatan'],
        default => ['border-air-200 bg-air-50 text-air-900', 'text-air-600', 'info'],
    };
@endphp

<div role="alert"
     @if ($dapatDitutup) x-data="{ tampil: true }" x-show="tampil" x-transition @endif
     {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border px-4 py-3 text-sm '.$gaya]) }}>

    <x-ikon :nama="$ikon" class="mt-0.5 size-5 shrink-0 {{ $gayaIkon }}"/>

    <div class="min-w-0 flex-1 leading-relaxed">
        @if ($judul)
            <p class="font-semibold">{{ $judul }}</p>
            <div class="mt-0.5 opacity-90">{{ $slot }}</div>
        @else
            {{ $slot }}
        @endif
    </div>

    @if ($dapatDitutup)
        <button type="button" @click="tampil = false"
                class="-m-1 shrink-0 rounded-lg p-1 opacity-60 transition hover:opacity-100
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current">
            <x-ikon nama="silang" class="size-4"/>
            <span class="sr-only">Tutup pemberitahuan</span>
        </button>
    @endif
</div>
