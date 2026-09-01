@props(['judul' => null, 'deskripsi' => null, 'aksi' => null, 'kaki' => null, 'padat' => false])

<div {{ $attributes->merge(['class' => 'panel overflow-hidden']) }}>
    @if ($judul || $aksi)
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-tepi/70 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                @if ($judul)
                    <h2 class="text-base font-semibold text-navy-900">{{ $judul }}</h2>
                @endif
                @if ($deskripsi)
                    <p class="mt-1 max-w-prose text-sm leading-relaxed text-slate-500">{{ $deskripsi }}</p>
                @endif
            </div>

            @if ($aksi)
                <div class="flex shrink-0 items-center gap-2">{{ $aksi }}</div>
            @endif
        </div>
    @endif

    <div class="{{ $padat ? '' : 'px-5 py-5 sm:px-6' }}">
        {{ $slot }}
    </div>

    @if ($kaki)
        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-tepi/70 bg-permukaan px-5 py-4 sm:px-6">
            {{ $kaki }}
        </div>
    @endif
</div>
