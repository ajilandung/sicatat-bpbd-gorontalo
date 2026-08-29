@props(['varian' => 'utama', 'ukuran' => 'normal', 'href' => null, 'tipe' => 'submit'])

@php
    $dasar = 'inline-flex shrink-0 items-center justify-center gap-2 rounded-lg font-semibold whitespace-nowrap
              transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2
              disabled:cursor-not-allowed disabled:opacity-60';

    // Tinggi tetap per ukuran supaya tombol yang bersebelahan selalu sejajar.
    $dimensi = match ($ukuran) {
        'kecil' => 'h-9 px-3 text-sm',
        'lebar' => 'h-11 px-6 text-sm',
        default => 'h-10 px-4 text-sm',
    };

    // Teks putih di atas oranye baru lolos kontras AA mulai brand-700.
    $gaya = match ($varian) {
        'sekunder' => 'border border-slate-300 bg-white text-navy-800 shadow-kartu hover:border-slate-400 hover:bg-slate-50 focus-visible:ring-navy-400',
        'bahaya' => 'bg-red-600 text-white shadow-kartu hover:bg-red-700 focus-visible:ring-red-500',
        'halus' => 'text-slate-600 hover:bg-slate-100 hover:text-navy-900 focus-visible:ring-navy-400',
        'tautan' => 'h-auto px-0 text-brand-700 underline-offset-4 hover:text-brand-800 hover:underline focus-visible:ring-brand-500',
        default => 'bg-brand-700 text-white shadow-kartu hover:bg-brand-800 focus-visible:ring-brand-500',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $dasar.' '.$dimensi.' '.$gaya]) }}>{{ $slot }}</a>
@else
    <button type="{{ $tipe }}" {{ $attributes->merge(['class' => $dasar.' '.$dimensi.' '.$gaya]) }}>{{ $slot }}</button>
@endif
