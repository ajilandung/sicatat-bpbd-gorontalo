@props(['judul', 'deskripsi' => null, 'kembali' => null, 'kembaliLabel' => 'Kembali', 'aksi' => null])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    @if ($kembali)
        <a href="{{ $kembali }}"
           class="mb-3 inline-flex items-center gap-1.5 rounded text-sm font-medium text-slate-500 transition-colors
                  hover:text-brand-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
            <x-ikon nama="panah-kiri" class="size-4"/>
            {{ $kembaliLabel }}
        </a>
    @endif

    <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-4">
        <div class="min-w-0">
            {{-- Garis oranye pendek: penanda identitas yang berulang di tiap halaman. --}}
            <div class="mb-3 h-1 w-9 rounded-full bg-brand-600"></div>

            <h1 class="text-xl font-semibold tracking-tight text-navy-900 sm:text-2xl">{{ $judul }}</h1>

            @if ($deskripsi)
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">{{ $deskripsi }}</p>
            @endif
        </div>

        @if ($aksi)
            <div class="flex shrink-0 flex-wrap items-center gap-3">{{ $aksi }}</div>
        @endif
    </div>
</div>
