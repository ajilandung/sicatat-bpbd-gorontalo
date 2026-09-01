@props(['nama', 'tipe' => 'text', 'nilai' => null, 'ikon' => null])

@php
    $galat = $errors->has($nama);

    $kelas = 'block h-11 w-full rounded-lg border-slate-300 text-base text-navy-900 shadow-kartu sm:text-sm transition-colors
              placeholder:text-slate-400 focus:border-air-500 focus:ring-1 focus:ring-air-500
              disabled:bg-slate-100 disabled:text-slate-500'
        .($ikon ? ' pl-10' : '')
        .($galat ? ' border-red-400 focus:border-red-500 focus:ring-red-500' : '');
@endphp

<div class="relative">
    @if ($ikon)
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <x-ikon :nama="$ikon" class="size-[18px]"/>
        </span>
    @endif

    <input type="{{ $tipe }}"
           name="{{ $nama }}"
           id="{{ $attributes->get('id', $nama) }}"
           value="{{ old($nama, $nilai) }}"
           @if ($galat) aria-invalid="true" aria-describedby="{{ $nama }}-galat" @endif
           {{ $attributes->except('id')->merge(['class' => $kelas]) }}>
</div>
