<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk — Sicatat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-white text-slate-700 antialiased">

<div class="flex min-h-full flex-col lg:flex-row">

    {{-- ── Panel identitas. Disembunyikan pada layar kecil agar form tetap lapang. ── --}}
    <aside class="relative hidden shrink-0 flex-col justify-between overflow-hidden bg-navy-900 px-12 py-14 lg:flex lg:w-[52%] xl:px-16">
        {{-- Cahaya biru air yang tipis sebagai kedalaman, bukan hiasan. --}}
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(55rem_38rem_at_15%_-10%,rgba(56,189,248,0.16),transparent)]"></div>

        {{-- Garis air di dasar panel: motif halus, satu warna, tanpa gradient. --}}
        <svg class="pointer-events-none absolute inset-x-0 bottom-0 h-56 w-full text-air-400/25" viewBox="0 0 800 200"
             fill="none" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 120q100-38 200 0t200 0 200 0 200 0" stroke="currentColor" stroke-width="1.5"/>
            <path d="M0 148q100-38 200 0t200 0 200 0 200 0" stroke="currentColor" stroke-width="1.5" opacity=".7"/>
            <path d="M0 176q100-38 200 0t200 0 200 0 200 0" stroke="currentColor" stroke-width="1.5" opacity=".4"/>
        </svg>

        {{-- Pita oranye BPBD di tepi panel. --}}
        <div class="absolute inset-y-0 right-0 w-1.5 bg-brand-600"></div>

        <div class="relative flex items-center gap-3.5">
            <x-ui.logo ukuran="size-12"/>
            <div>
                <p class="text-lg font-semibold tracking-tight text-white">Sicatat</p>
                <p class="text-sm text-navy-300">BPBD Provinsi Gorontalo</p>
            </div>
        </div>

        <div class="relative max-w-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-400">Sistem Internal</p>

            <h2 class="mt-4 text-[2rem] font-semibold leading-[1.15] tracking-tight text-white xl:text-4xl">
                Pencatatan Penyaluran<br>Bantuan Air Bersih
            </h2>

            <p class="mt-5 max-w-md leading-relaxed text-navy-200">
                Mencatat, memantau, dan melaporkan kegiatan penyaluran bantuan air bersih
                di seluruh wilayah Provinsi Gorontalo secara terpusat.
            </p>

            <ul class="mt-9 space-y-3.5 text-sm text-navy-200">
                @foreach ([
                    'Data tersimpan terpusat, tidak lagi tersebar di berkas Excel',
                    'Rekapitulasi dan laporan tersusun otomatis',
                    'Akses dibedakan sesuai peran masing-masing pengguna',
                ] as $poin)
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-air-400/15 text-air-300">
                            <x-ikon nama="centang" class="size-3.5"/>
                        </span>
                        <span>{{ $poin }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs leading-relaxed text-navy-400">
            Sistem internal BPBD Provinsi Gorontalo.<br>
            Akun dibuat oleh administrator, bukan melalui pendaftaran mandiri.
        </p>
    </aside>

    {{-- ── Form masuk ── --}}
    <main class="flex flex-1 flex-col">
        {{-- Kop ringkas khusus layar kecil, menggantikan panel identitas. --}}
        <div class="border-b-2 border-brand-600 bg-navy-900 px-6 py-4 lg:hidden">
            <div class="flex items-center gap-3">
                <x-ui.logo ukuran="size-10"/>
                <div>
                    <p class="font-semibold tracking-tight text-white">Sicatat</p>
                    <p class="text-xs text-navy-300">BPBD Provinsi Gorontalo</p>
                </div>
            </div>
        </div>

        <div class="flex flex-1 items-center justify-center px-6 py-12 sm:px-10">
            <div class="w-full max-w-[25rem]">

                <div class="h-1 w-9 rounded-full bg-brand-600"></div>

                <h1 class="mt-5 text-2xl font-semibold tracking-tight text-navy-900 sm:text-[1.75rem]">Masuk ke Sistem</h1>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Gunakan username atau email beserta password yang diberikan administrator.
                </p>

                @if (session('status'))
                    <x-ui.notifikasi jenis="sukses" class="mt-6">{{ session('status') }}</x-ui.notifikasi>
                @endif

                @if (session('error'))
                    <x-ui.notifikasi jenis="galat" class="mt-6">{{ session('error') }}</x-ui.notifikasi>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
                    @csrf

                    <x-ui.kolom nama="login" label="Username atau Email">
                        <x-ui.input nama="login" ikon="users" autocomplete="username" required autofocus
                                    placeholder="misalnya: admin"/>
                    </x-ui.kolom>

                    <x-ui.kolom nama="password" label="Password">
                        <x-ui.password nama="password" autocomplete="current-password" required
                                       placeholder="Masukkan password"/>
                    </x-ui.kolom>

                    <div class="flex items-center justify-between gap-4 pt-1">
                        <label class="flex items-center gap-2.5 text-sm text-slate-600">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember'))
                                   class="size-4 rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500">
                            Ingat saya
                        </label>

                        <span class="text-xs text-slate-400">Sesi berakhir saat peramban ditutup</span>
                    </div>

                    <x-ui.tombol ukuran="lebar" class="w-full">
                        Masuk
                        <x-ikon nama="panah-kanan" class="size-4"/>
                    </x-ui.tombol>
                </form>

                <div class="mt-8 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5">
                    <p class="flex items-start gap-2.5 text-xs leading-relaxed text-slate-600">
                        <x-ikon nama="info" class="mt-px size-4 shrink-0 text-slate-400"/>
                        <span>Lupa password? Hubungi administrator sistem. Sistem ini tidak membuka pendaftaran akun mandiri.</span>
                    </p>
                </div>

                <p class="mt-8 text-center text-xs text-slate-400">
                    © {{ now()->year }} Badan Penanggulangan Bencana Daerah Provinsi Gorontalo
                </p>
            </div>
        </div>
    </main>
</div>

</body>
</html>
