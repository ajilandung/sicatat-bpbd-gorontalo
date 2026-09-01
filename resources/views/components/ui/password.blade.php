@props(['nama' => 'password', 'autocomplete' => 'current-password'])

@php
    $galat = $errors->has($nama);

    $kelas = 'block h-11 w-full rounded-lg border-slate-300 pl-10 pr-11 text-base text-navy-900 shadow-kartu sm:text-sm transition-colors
              placeholder:text-slate-400 focus:border-air-500 focus:ring-1 focus:ring-air-500'
        .($galat ? ' border-red-400 focus:border-red-500 focus:ring-red-500' : '');
@endphp

{{-- Input password dengan tombol tampil/sembunyikan. --}}
<div x-data="{ terlihat: false }" class="relative">
    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
        <x-ikon nama="gembok" class="size-[18px]"/>
    </span>

    <input type="password"
           :type="terlihat ? 'text' : 'password'"
           name="{{ $nama }}"
           id="{{ $attributes->get('id', $nama) }}"
           autocomplete="{{ $autocomplete }}"
           @if ($galat) aria-invalid="true" aria-describedby="{{ $nama }}-galat" @endif
           {{ $attributes->except('id')->merge(['class' => $kelas]) }}>

    {{-- Ikut dalam urutan tab: menampilkan password adalah fungsi tersendiri,
         dan WCAG 2.1.1 mensyaratkannya bisa dijalankan lewat papan ketik. --}}
    <button type="button" @click="terlihat = ! terlihat"
            class="absolute inset-y-0 right-0 flex items-center rounded-r-lg px-3 text-slate-400 transition-colors
                   hover:text-air-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-air-500"
            aria-label="Tampilkan password"
            :aria-label="terlihat ? 'Sembunyikan password' : 'Tampilkan password'">
        <x-ikon x-show="! terlihat" nama="mata" class="size-[18px]"/>
        <x-ikon x-show="terlihat" x-cloak nama="mata-tutup" class="size-[18px]"/>
    </button>
</div>
