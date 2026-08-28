<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Sicatat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100">

<div class="flex min-h-full">

    {{-- Panel identitas, disembunyikan pada layar kecil --}}
    <div class="hidden w-1/2 flex-col justify-between bg-slate-900 p-12 lg:flex">
        <div class="flex items-center gap-3">
            <div class="flex size-11 items-center justify-center rounded-lg bg-sky-500">
                <svg class="size-7 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 3.5c3.2 3.6 6 6.9 6 10a6 6 0 1 1-12 0c0-3.1 2.8-6.4 6-10Z"/>
                </svg>
            </div>
            <div>
                <p class="text-lg font-semibold text-white">Sicatat</p>
                <p class="text-sm text-slate-400">BPBD Provinsi Gorontalo</p>
            </div>
        </div>

        <div class="max-w-md">
            <h2 class="text-3xl font-semibold leading-tight text-white">
                Sistem Informasi Pencatatan Penyaluran Bantuan Air Bersih
            </h2>
            <p class="mt-4 text-slate-400">
                Mencatat, memantau, dan melaporkan kegiatan penyaluran bantuan air bersih
                di seluruh wilayah Provinsi Gorontalo secara terpusat.
            </p>
        </div>

        <p class="text-xs text-slate-500">
            Sistem internal. Akun dibuat oleh administrator.
        </p>
    </div>

    {{-- Form --}}
    <div class="flex w-full items-center justify-center p-6 lg:w-1/2">
        <div class="w-full max-w-sm">

            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <div class="flex size-10 items-center justify-center rounded-lg bg-sky-500">
                    <svg class="size-6 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 3.5c3.2 3.6 6 6.9 6 10a6 6 0 1 1-12 0c0-3.1 2.8-6.4 6-10Z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Sicatat</p>
                    <p class="text-xs text-slate-500">BPBD Provinsi Gorontalo</p>
                </div>
            </div>

            <h1 class="text-2xl font-semibold text-slate-900">Masuk ke sistem</h1>
            <p class="mt-1 text-sm text-slate-500">Gunakan username atau email yang terdaftar.</p>

            @if (session('status'))
                <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="login" class="block text-sm font-medium text-slate-700">Username atau Email</label>
                    <input id="login" name="login" type="text" value="{{ old('login') }}"
                           required autofocus autocomplete="username"
                           class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500
                                  @error('login') border-red-400 @enderror">
                    @error('login')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="mt-1.5 block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500
                                  @error('password') border-red-400 @enderror">
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    Ingat saya di perangkat ini
                </label>

                <button type="submit"
                        class="w-full rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm
                               transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                    Masuk
                </button>
            </form>

            <p class="mt-8 text-center text-xs text-slate-400">
                Lupa password? Hubungi administrator sistem.
            </p>
        </div>
    </div>
</div>

</body>
</html>
