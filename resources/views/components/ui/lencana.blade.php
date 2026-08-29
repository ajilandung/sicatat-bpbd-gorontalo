@props(['warna' => 'abu'])

@php
    $gaya = match ($warna) {
        'hijau' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'merah' => 'bg-red-50 text-red-700 ring-red-600/20',
        'biru' => 'bg-air-50 text-air-800 ring-air-600/20',
        'oranye' => 'bg-brand-50 text-brand-800 ring-brand-600/25',
        'kuning' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-500/20',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '.$gaya]) }}>
    {{ $slot }}
</span>
