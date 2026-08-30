<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('judul', 'Dashboard') — Sicatat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 text-slate-700 antialiased">

@php
    $user = auth()->user();

    // Menu yang belum dibangun ditandai dengan fase pengerjaannya,
    // supaya jelas mana yang sudah bisa dipakai.
    $menu = [
        [
            'judul' => null,
            'item' => [
                ['label' => 'Dashboard', 'route' => $user->routeDashboard(), 'aktifJika' => 'dashboard.*', 'ikon' => 'grid', 'siap' => true],
            ],
        ],
        [
            'judul' => 'Penyaluran',
            'item' => [
                ['label' => 'Input Penyaluran', 'ikon' => 'plus', 'siap' => false, 'fase' => 3, 'admin' => true],
                ['label' => 'Riwayat Penyaluran', 'ikon' => 'list', 'siap' => false, 'fase' => 3],
            ],
        ],
        [
            'judul' => 'Master Data',
            'item' => [
                ['label' => 'Data Wilayah', 'route' => 'wilayah.desa.index', 'aktifJika' => 'wilayah.*', 'ikon' => 'map', 'siap' => true, 'admin' => true],
                ['label' => 'Data Instansi', 'route' => 'instansi.index', 'aktifJika' => 'instansi.*', 'ikon' => 'building', 'siap' => true, 'admin' => true],
                ['label' => 'Manajemen Pengguna', 'route' => 'pengguna.index', 'aktifJika' => 'pengguna.*', 'ikon' => 'users', 'siap' => true, 'admin' => true],
            ],
        ],
        [
            'judul' => 'Pelaporan',
            'item' => [
                ['label' => 'Laporan & Export', 'ikon' => 'document', 'siap' => false, 'fase' => 5],
            ],
        ],
    ];
@endphp

<a href="#konten-utama"
   class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg
          focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-navy-900 focus:shadow-naik">
    Lompat ke konten
</a>

{{--
    `layarKecil` dipantau lewat matchMedia, bukan sekadar kelas `lg:`, karena
    sidebar yang tertutup hanya digeser keluar layar dan tetap ada di DOM.
    Tanpa penanda ini, pengguna papan ketik di layar kecil akan menekan Tab dan
    masuk ke menu yang tidak terlihat olehnya.
--}}
<div x-data="{
        sidebarTerbuka: false,
        layarKecil: window.matchMedia('(max-width: 63.99rem)').matches,
        tutupSidebar() {
            if (! this.sidebarTerbuka) return;
            this.sidebarTerbuka = false;
            this.$refs.tombolBukaSidebar?.focus();
        },
     }"
     x-init="window.matchMedia('(max-width: 63.99rem)')
                .addEventListener('change', e => layarKecil = e.matches)"
     @keydown.escape.window="tutupSidebar()"
     class="min-h-full">

    {{-- Latar gelap saat sidebar terbuka di layar kecil --}}
    <div x-show="sidebarTerbuka" x-transition.opacity @click="tutupSidebar()"
         class="fixed inset-0 z-30 bg-navy-950/60 lg:hidden" x-cloak></div>

    {{-- ── Sidebar ── --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-navy-900 transition-transform duration-200 lg:w-64 lg:translate-x-0"
           :class="sidebarTerbuka ? 'translate-x-0' : '-translate-x-full'"
           x-effect="$el.inert = layarKecil && ! sidebarTerbuka">

        <div class="flex items-center gap-3 border-b border-white/5 px-5 py-5">
            <x-ui.logo ukuran="size-10"/>
            <div class="min-w-0 flex-1">
                <p class="text-base font-semibold tracking-tight text-white">Sicatat</p>
                <p class="truncate text-xs text-navy-300">BPBD Provinsi Gorontalo</p>
            </div>

            <button type="button" x-ref="tombolTutupSidebar" @click="tutupSidebar()"
                    class="-mr-1 rounded-lg p-1.5 text-navy-300 transition-colors hover:bg-white/5 hover:text-white
                           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500 lg:hidden">
                <x-ikon nama="silang" class="size-5"/>
                <span class="sr-only">Tutup menu</span>
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
            @foreach ($menu as $grup)
                <div>
                    @if ($grup['judul'])
                        <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-navy-400">
                            {{ $grup['judul'] }}
                        </p>
                    @endif

                    <ul class="space-y-0.5">
                        @foreach ($grup['item'] as $item)
                            @continue(($item['admin'] ?? false) && ! $user->isAdmin())

                            <li>
                                @if ($item['siap'])
                                    @php $aktif = request()->routeIs($item['aktifJika'] ?? $item['route']); @endphp
                                    <a href="{{ route($item['route']) }}" @if ($aktif) aria-current="page" @endif
                                       class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                                              focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500 focus-visible:ring-offset-2 focus-visible:ring-offset-navy-900
                                              {{ $aktif
                                                    ? "bg-navy-800 text-white before:absolute before:inset-y-2 before:left-0 before:w-1 before:rounded-r-full before:bg-air-400 before:content-['']"
                                                    : 'text-navy-200 hover:bg-navy-800/70 hover:text-white' }}">
                                        <x-ikon :nama="$item['ikon']"
                                                class="size-5 shrink-0 {{ $aktif ? 'text-air-400' : 'text-navy-300' }}"/>
                                        {{ $item['label'] }}
                                    </a>
                                @else
                                    <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-navy-400"
                                          title="Dikerjakan pada Fase {{ $item['fase'] }}">
                                        <x-ikon :nama="$item['ikon']" class="size-5 shrink-0 opacity-70"/>
                                        <span class="flex-1">{{ $item['label'] }}</span>
                                        <span class="rounded bg-white/5 px-1.5 py-0.5 text-[10px] font-semibold text-navy-300">
                                            Fase {{ $item['fase'] }}
                                        </span>
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        {{-- Identitas peran: pengguna selalu tahu ia sedang masuk sebagai apa. --}}
        <div class="border-t border-white/5 p-3">
            <div class="flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2.5">
                <x-ui.avatar :nama="$user->name" ukuran="sm" warna="navy"/>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs text-navy-300">{{ $user->labelRole() }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── Konten ── --}}
    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
            <button type="button" x-ref="tombolBukaSidebar"
                    @click="sidebarTerbuka = true; $nextTick(() => $refs.tombolTutupSidebar.focus())"
                    class="-ml-1 rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-navy-900
                           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500 lg:hidden">
                <x-ikon nama="menu" class="size-6"/>
                <span class="sr-only">Buka menu</span>
            </button>

            {{-- Penanda posisi, bukan judul halaman: judul sebenarnya ada di badan halaman. --}}
            <p class="flex-1 truncate text-sm font-medium text-slate-500">
                <span class="hidden text-slate-400 sm:inline">Sicatat</span>
                <span class="hidden text-slate-300 sm:inline"> / </span>
                <span class="text-navy-800">@yield('judul', 'Dashboard')</span>
            </p>

            <div x-data="{ buka: false }" class="relative"
                 @keydown.escape="buka = false; $refs.tombolAkun.focus()">
                <button type="button" x-ref="tombolAkun" @click="buka = ! buka" @click.outside="buka = false"
                        :aria-expanded="buka.toString()" aria-haspopup="true"
                        class="flex items-center gap-2.5 rounded-lg py-1.5 pl-1.5 pr-2 transition-colors hover:bg-slate-100
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500">
                    <x-ui.avatar :nama="$user->name" ukuran="sm" warna="navy"/>
                    <span class="hidden text-left sm:block">
                        <span class="block text-sm font-medium leading-tight text-navy-900">{{ $user->name }}</span>
                        <span class="block text-xs leading-tight text-slate-500">{{ $user->labelRole() }}</span>
                    </span>
                    <span class="sr-only">Buka menu akun</span>
                </button>

                <div x-show="buka" x-transition x-cloak
                     class="absolute right-0 mt-2 w-60 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-naik">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <p class="truncate text-sm font-medium text-navy-900">{{ $user->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $user->username }}</p>
                        <x-ui.lencana-role :role="$user->role" class="mt-2"/>
                    </div>

                    <a href="{{ route('password.edit') }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-navy-900">
                        <x-ikon nama="kunci" class="size-4 text-slate-400"/>
                        Ubah Password
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-red-700 transition-colors hover:bg-red-50">
                            <x-ikon nama="keluar" class="size-4 text-red-400"/>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main id="konten-utama" class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto max-w-6xl">
                @if (session('status'))
                    <x-ui.notifikasi jenis="sukses" dapat-ditutup class="mb-6">{{ session('status') }}</x-ui.notifikasi>
                @endif

                @if (session('error'))
                    <x-ui.notifikasi jenis="galat" dapat-ditutup class="mb-6">{{ session('error') }}</x-ui.notifikasi>
                @endif

                @yield('konten')
            </div>
        </main>
    </div>
</div>

</body>
</html>
