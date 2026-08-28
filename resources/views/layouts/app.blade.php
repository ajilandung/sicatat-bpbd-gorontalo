<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Dashboard') — Sicatat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">

@php
    $user = auth()->user();

    // Menu yang belum dibangun ditandai dengan fase pengerjaannya,
    // supaya jelas mana yang sudah bisa dipakai.
    $menu = [
        [
            'judul' => null,
            'item' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'ikon' => 'grid', 'siap' => true],
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
                ['label' => 'Data Wilayah', 'ikon' => 'map', 'siap' => false, 'fase' => 2, 'admin' => true],
                ['label' => 'Data Instansi', 'ikon' => 'building', 'siap' => false, 'fase' => 2, 'admin' => true],
                ['label' => 'Manajemen Pengguna', 'ikon' => 'users', 'siap' => false, 'fase' => 2, 'admin' => true],
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

<div x-data="{ sidebarTerbuka: false }" class="min-h-full">

    {{-- Latar gelap saat sidebar terbuka di layar kecil --}}
    <div x-show="sidebarTerbuka" x-transition.opacity @click="sidebarTerbuka = false"
         class="fixed inset-0 z-30 bg-slate-900/60 lg:hidden" x-cloak></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 transition-transform duration-200 lg:translate-x-0"
           :class="sidebarTerbuka ? 'translate-x-0' : '-translate-x-full'">

        <div class="flex items-center gap-3 px-5 py-5">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-sky-500">
                <svg class="size-6 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 3.5c3.2 3.6 6 6.9 6 10a6 6 0 1 1-12 0c0-3.1 2.8-6.4 6-10Z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-base font-semibold tracking-tight text-white">Sicatat</p>
                <p class="truncate text-xs text-slate-400">BPBD Provinsi Gorontalo</p>
            </div>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-6">
            @foreach ($menu as $grup)
                <div>
                    @if ($grup['judul'])
                        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ $grup['judul'] }}
                        </p>
                    @endif

                    <ul class="space-y-1">
                        @foreach ($grup['item'] as $item)
                            @continue(($item['admin'] ?? false) && ! $user->isAdmin())

                            <li>
                                @if ($item['siap'])
                                    @php $aktif = request()->routeIs($item['route']); @endphp
                                    <a href="{{ route($item['route']) }}"
                                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                                              {{ $aktif ? 'bg-sky-500 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        <x-ikon :nama="$item['ikon']" class="size-5 shrink-0"/>
                                        {{ $item['label'] }}
                                    </a>
                                @else
                                    <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-500"
                                          title="Dikerjakan pada Fase {{ $item['fase'] }}">
                                        <x-ikon :nama="$item['ikon']" class="size-5 shrink-0"/>
                                        <span class="flex-1">{{ $item['label'] }}</span>
                                        <span class="rounded bg-slate-800 px-1.5 py-0.5 text-[10px] font-semibold text-slate-400">
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
    </aside>

    {{-- Konten --}}
    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
            <button type="button" @click="sidebarTerbuka = true"
                    class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                <span class="sr-only">Buka menu</span>
                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                </svg>
            </button>

            <h1 class="flex-1 truncate text-lg font-semibold text-slate-900">@yield('judul', 'Dashboard')</h1>

            <div x-data="{ buka: false }" class="relative">
                <button type="button" @click="buka = !buka" @click.outside="buka = false"
                        class="flex items-center gap-3 rounded-lg px-2 py-1.5 hover:bg-slate-100">
                    <div class="flex size-9 items-center justify-center rounded-full bg-sky-100 text-sm font-semibold text-sky-700">
                        {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                    </div>
                    <div class="hidden text-left sm:block">
                        <p class="text-sm font-medium leading-tight text-slate-900">{{ $user->name }}</p>
                        <p class="text-xs leading-tight text-slate-500">{{ $user->labelRole() }}</p>
                    </div>
                </button>

                <div x-show="buka" x-transition x-cloak
                     class="absolute right-0 mt-2 w-56 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                    <div class="border-b border-slate-100 px-4 py-2 sm:hidden">
                        <p class="text-sm font-medium text-slate-900">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $user->labelRole() }}</p>
                    </div>
                    <div class="px-4 py-2">
                        <p class="text-xs text-slate-500">Masuk sebagai</p>
                        <p class="truncate text-sm text-slate-700">{{ $user->username }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @yield('konten')
        </main>
    </div>
</div>

</body>
</html>
