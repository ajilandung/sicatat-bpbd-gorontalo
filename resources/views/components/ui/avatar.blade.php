@props(['nama', 'ukuran' => 'md', 'warna' => 'netral'])

@php
    $dimensi = match ($ukuran) {
        'sm' => 'size-9 text-sm',
        'lg' => 'size-14 text-xl',
        default => 'size-10 text-base',
    };

    $gaya = match ($warna) {
        'navy' => 'bg-navy-100 text-navy-800 ring-navy-700/20',
        'air' => 'bg-air-50 text-air-800 ring-air-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-400/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center rounded-full font-semibold ring-1 ring-inset {$dimensi} {$gaya}"]) }}
      aria-hidden="true">
    {{ Str::upper(Str::substr(trim($nama), 0, 1)) }}
</span>
