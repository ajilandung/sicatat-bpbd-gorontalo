@props(['label', 'nilai', 'satuan' => null, 'catatan' => null, 'ikon' => null, 'warna' => 'netral', 'href' => null])

@php
    $gayaIkon = match ($warna) {
        'brand' => 'bg-brand-50 text-brand-700',
        'air' => 'bg-air-50 text-air-700',
        default => 'bg-slate-100 text-slate-500',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white p-5 shadow-kartu']) }}>
    <div class="flex items-start justify-between gap-4">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>

        @if ($ikon)
            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $gayaIkon }}">
                <x-ikon :nama="$ikon" class="size-[18px]"/>
            </span>
        @endif
    </div>

    <p class="mt-3 text-3xl font-semibold tracking-tight text-navy-900">
        {{ is_numeric($nilai) ? number_format($nilai, 0, ',', '.') : $nilai }}
        @if ($satuan)
            <span class="text-base font-normal text-slate-500">{{ $satuan }}</span>
        @endif
    </p>

    @if ($href)
        <a href="{{ $href }}"
           class="mt-3 inline-flex items-center gap-1 rounded text-sm font-medium text-brand-700 transition-colors
                  hover:text-brand-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
            {{ $slot }}
            <x-ikon nama="panah-kanan" class="size-3.5"/>
        </a>
    @elseif ($catatan)
        <p class="mt-3 text-xs text-slate-400">{{ $catatan }}</p>
    @endif
</div>
