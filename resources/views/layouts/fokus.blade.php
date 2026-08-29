<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('judul') — Sicatat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 text-slate-700 antialiased">

{{--
    Layout tanpa navigasi, dipakai saat pengguna harus menyelesaikan satu langkah
    lebih dahulu — misalnya mengganti password sementara pada login pertama.
--}}
<div class="flex min-h-full flex-col">
    <header class="border-b-2 border-brand-600 bg-navy-900">
        <div class="mx-auto flex max-w-3xl items-center justify-between gap-4 px-6 py-4">
            <div class="flex items-center gap-3">
                <x-ui.logo ukuran="size-10"/>
                <div>
                    <p class="font-semibold tracking-tight text-white">Sicatat</p>
                    <p class="text-xs text-navy-300">BPBD Provinsi Gorontalo</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-navy-200 transition-colors
                               hover:bg-white/5 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                    <x-ikon nama="keluar" class="size-4"/>
                    Keluar
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-10">
        @if (session('status'))
            <x-ui.notifikasi jenis="sukses" class="mb-6">{{ session('status') }}</x-ui.notifikasi>
        @endif

        @if (session('error'))
            <x-ui.notifikasi jenis="peringatan" class="mb-6">{{ session('error') }}</x-ui.notifikasi>
        @endif

        @yield('konten')
    </main>

    <footer class="px-6 py-6 text-center text-xs text-slate-400">
        © {{ now()->year }} Badan Penanggulangan Bencana Daerah Provinsi Gorontalo
    </footer>
</div>

</body>
</html>
