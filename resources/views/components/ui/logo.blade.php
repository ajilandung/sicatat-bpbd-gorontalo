@props(['ukuran' => 'size-11'])

{{-- Tetesan air putih di atas biru: air sebagai layanan yang dicatat sistem ini. --}}
<div {{ $attributes->merge(['class' => 'flex '.$ukuran.' shrink-0 items-center justify-center rounded-xl bg-air-600 shadow-sm shadow-air-900/20']) }}>
    <svg class="size-[62%] text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3.5c3.2 3.6 6 6.9 6 10a6 6 0 1 1-12 0c0-3.1 2.8-6.4 6-10Z"/>
    </svg>
</div>
