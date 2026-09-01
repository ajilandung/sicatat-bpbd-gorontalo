@props(['item' => [], 'label' => 'Bagian halaman'])

{{-- Navigasi antar halaman yang bersaudara, mis. tiga tingkat data wilayah.
     Dibuat sebagai <nav> dengan aria-current agar pembaca layar tahu halaman
     mana yang sedang dibuka. --}}
<nav {{ $attributes->merge(['class' => 'mb-6 border-b border-tepi']) }} aria-label="{{ $label }}">
    <ul class="-mb-px flex flex-wrap gap-x-6 gap-y-1">
        @foreach ($item as $tab)
            @php $aktif = request()->routeIs($tab['aktifJika']); @endphp

            <li>
                <a href="{{ route($tab['route']) }}" @if ($aktif) aria-current="page" @endif
                   class="flex items-center gap-2 border-b-2 px-1 py-3 text-sm font-medium transition-colors
                          focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500
                          {{ $aktif
                                ? 'border-air-600 text-navy-900'
                                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-navy-800' }}">
                    {{ $tab['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
