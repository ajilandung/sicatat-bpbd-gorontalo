@props(['ikon', 'label', 'href' => null, 'varian' => 'netral', 'ukuran' => 'kecil', 'tipe' => 'button'])

@php
    // Tombol aksi ringkas untuk baris tabel: ikon + label tersembunyi bagi
    // pembaca layar, plus tooltip bawaan peramban lewat atribut title.
    $dimensi = match ($ukuran) {
        'besar' => 'size-11',
        'normal' => 'size-10',
        default => 'size-9',
    };

    $dasar = "inline-flex {$dimensi} shrink-0 items-center justify-center rounded-lg border transition-colors
              focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1";

    $gaya = match ($varian) {
        'bahaya' => 'border-slate-200 bg-white text-slate-500 hover:border-red-300 hover:bg-red-50 hover:text-red-700 focus-visible:ring-red-500',
        'aksen' => 'border-slate-200 bg-white text-slate-500 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 focus-visible:ring-brand-500',
        default => 'border-slate-200 bg-white text-slate-500 hover:border-slate-300 hover:bg-slate-50 hover:text-navy-900 focus-visible:ring-navy-400',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" title="{{ $label }}" {{ $attributes->merge(['class' => $dasar.' '.$gaya]) }}>
        <x-ikon :nama="$ikon" class="size-[18px]"/>
        <span class="sr-only">{{ $label }}</span>
    </a>
@else
    <button type="{{ $tipe }}" title="{{ $label }}" {{ $attributes->merge(['class' => $dasar.' '.$gaya]) }}>
        <x-ikon :nama="$ikon" class="size-[18px]"/>
        <span class="sr-only">{{ $label }}</span>
    </button>
@endif
