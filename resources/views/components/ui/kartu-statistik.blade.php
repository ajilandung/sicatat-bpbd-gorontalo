@props(['label', 'nilai', 'satuan' => null, 'catatan' => null, 'ikon' => null, 'warna' => 'netral', 'href' => null])

@php
    $gayaIkon = match ($warna) {
        'navy' => 'bg-navy-100 text-navy-800',
        'air' => 'bg-air-50 text-air-700',
        default => 'bg-slate-100 text-slate-500',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col rounded-xl border border-slate-200 bg-white p-4 shadow-kartu sm:p-5']) }}>
    <div class="flex items-start justify-between gap-4">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>

        @if ($ikon)
            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $gayaIkon }}">
                <x-ikon :nama="$ikon" class="size-[18px]"/>
            </span>
        @endif
    </div>

    <p class="mt-2 text-2xl font-semibold tracking-tight text-navy-900 sm:text-3xl">
        {{ is_numeric($nilai) ? number_format($nilai, 0, ',', '.') : $nilai }}
        @if ($satuan)
            <span class="text-base font-normal text-slate-500">{{ $satuan }}</span>
        @endif
    </p>

    @if ($href)
        <a href="{{ $href }}"
           class="mt-auto inline-flex items-center gap-1 self-start rounded pt-3 text-sm font-medium text-air-700 transition-colors
                  hover:text-air-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500">
            {{ $slot }}
            <x-ikon nama="panah-kanan" class="size-3.5"/>
        </a>
    @elseif ($catatan)
        <p class="mt-auto pt-3 text-xs leading-relaxed text-slate-400">{{ $catatan }}</p>
    @endif
</div>
