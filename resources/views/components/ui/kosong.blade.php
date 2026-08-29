@props(['judul', 'deskripsi' => null, 'ikon' => 'cari'])

<div {{ $attributes->merge(['class' => 'px-6 py-14 text-center']) }}>
    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
        <x-ikon :nama="$ikon" class="size-6"/>
    </div>

    <p class="mt-4 text-sm font-semibold text-navy-900">{{ $judul }}</p>

    @if ($deskripsi)
        <p class="mx-auto mt-1 max-w-sm text-sm leading-relaxed text-slate-500">{{ $deskripsi }}</p>
    @endif

    @if (trim($slot) !== '')
        <div class="mt-5 flex justify-center gap-3">{{ $slot }}</div>
    @endif
</div>
