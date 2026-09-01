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
        @if ($videoLatar)
            {{--
                Video suasana kegiatan penyaluran, di lapisan paling bawah panel.
                Sumbernya sengaja disimpan pada `data-src`, bukan `src`: berkas baru
                diunduh oleh skrip di bawah halaman setelah dipastikan panel ini
                memang tampil dan pengguna tidak meminta animasi dikurangi.
            --}}
            <div class="pointer-events-none absolute inset-0 bg-navy-900 bg-cover bg-center"
                 @if ($posterLatar) style="background-image: url('{{ $posterLatar }}')" @endif>
                <video class="video-latar size-full object-cover" data-video-latar
                       muted loop playsinline preload="none" aria-hidden="true">
                    <source data-src="{{ $videoLatar }}" type="video/mp4">
                </video>
            </div>

            {{-- Tirai berarah; angkanya dijelaskan di `resources/css/app.css`. --}}
            <div class="tirai-video pointer-events-none absolute inset-0"></div>

            {{--
                WCAG 2.2.2 (Pause, Stop, Hide): gerakan yang berjalan sendiri
                lebih dari lima detik wajib bisa dihentikan pengguna. Tombol ini
                disembunyikan sampai skrip memastikan videonya memang diputar,
                supaya tidak pernah ada kendali yang menganggur.
            --}}
            <button type="button" data-kendali-video aria-label="Jeda video latar"
                    class="kendali-video absolute bottom-14 right-12 size-11 items-center justify-center rounded-full
                           border border-white/15 bg-navy-950/60 text-navy-100 backdrop-blur-sm transition-colors
                           hover:border-white/30 hover:bg-navy-950/85 hover:text-white
                           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500
                           focus-visible:ring-offset-2 focus-visible:ring-offset-navy-950 xl:right-16">
                <x-ikon nama="jeda" class="size-[18px]" data-ikon-jeda/>
                <x-ikon nama="putar" class="size-[18px]" data-ikon-putar hidden/>
            </button>
        @else
            {{--
                Ornamen pengganti, hanya saat tidak ada video. Ketika video ada,
                gambar kegiatan yang sesungguhnya sudah mengisi panel ini — cahaya
                dan garis air di bawah justru menumpuk di atasnya tanpa menambah
                makna, jadi keduanya ditiadakan.
            --}}

            {{-- Cahaya biru air yang tipis sebagai kedalaman, bukan hiasan. --}}
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(55rem_38rem_at_15%_-10%,rgba(56,189,248,0.16),transparent)]"></div>

            {{-- Garis air di dasar panel: motif halus, satu warna, tanpa gradient. --}}
            <svg class="pointer-events-none absolute inset-x-0 bottom-0 h-56 w-full text-air-400/25" viewBox="0 0 800 200"
                 fill="none" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0 120q100-38 200 0t200 0 200 0 200 0" stroke="currentColor" stroke-width="1.5"/>
                <path d="M0 148q100-38 200 0t200 0 200 0 200 0" stroke="currentColor" stroke-width="1.5" opacity=".7"/>
                <path d="M0 176q100-38 200 0t200 0 200 0 200 0" stroke="currentColor" stroke-width="1.5" opacity=".4"/>
            </svg>
        @endif

        {{-- Pita biru di tepi panel; air-500 dipilih agar tetap terbaca di atas navy. --}}
        <div class="absolute inset-y-0 right-0 w-1.5 bg-air-500"></div>

        <div class="relative flex items-center gap-3.5">
            <x-ui.logo ukuran="size-12"/>
            <div>
                <p class="text-lg font-semibold tracking-tight text-white">Sicatat</p>
                <p class="text-sm text-navy-300">BPBD Provinsi Gorontalo</p>
            </div>
        </div>

        <div class="relative max-w-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-air-400">Sistem Internal</p>

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

        <p class="relative text-xs leading-relaxed text-navy-300">
            Sistem internal BPBD Provinsi Gorontalo.<br>
            Akun dibuat oleh administrator, bukan melalui pendaftaran mandiri.
        </p>
    </aside>

    {{-- ── Form masuk ── --}}
    <main class="flex flex-1 flex-col">
        {{-- Kop ringkas khusus layar kecil, menggantikan panel identitas. --}}
        <div class="border-b-2 border-air-500 bg-navy-900 px-6 py-4 lg:hidden">
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

                <div class="h-1 w-9 rounded-full bg-air-600"></div>

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
                                   class="size-4 rounded border-slate-300 text-air-600 shadow-sm focus:ring-air-500">
                            Ingat saya
                        </label>

                        <span class="text-xs text-slate-400">Sesi berakhir saat peramban ditutup</span>
                    </div>

                    <x-ui.tombol ukuran="lebar" class="w-full">
                        Masuk
                        <x-ikon nama="panah-kanan" class="size-4"/>
                    </x-ui.tombol>
                </form>

                <div class="mt-8 rounded-xl border border-tepi bg-permukaan px-4 py-3.5">
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

@if ($videoLatar)
    <script>
        (() => {
            const video = document.querySelector('[data-video-latar]');
            const kendali = document.querySelector('[data-kendali-video]');

            if (! video || ! kendali) return;

            // Video baru diunduh bila panel identitas benar-benar tampil (lebar `lg`
            // ke atas) dan pengguna tidak meminta animasi dikurangi. Petugas yang
            // membuka Sicatat dari ponsel tidak ikut menanggung ongkos kuotanya.
            const layakDiputar = window.matchMedia('(min-width: 64rem)').matches
                && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (! layakDiputar) return;

            // Muncul perlahan setelah frame pertama siap, supaya tidak menyentak.
            video.addEventListener('loadeddata', () => video.dataset.siap = '', { once: true });

            video.querySelectorAll('source[data-src]').forEach(sumber => sumber.src = sumber.dataset.src);
            video.load();

            // Sebagian peramban tetap menolak autoplay; poster yang sudah terpasang
            // sebagai latar tetap tampil, jadi penolakan ini tidak perlu dilaporkan.
            video.play().catch(() => {});

            // ── Kendali jeda ──────────────────────────────────────────────────
            const ikonJeda = kendali.querySelector('[data-ikon-jeda]');
            const ikonPutar = kendali.querySelector('[data-ikon-putar]');

            const perbaruiKendali = () => {
                const berjalan = ! video.paused;

                ikonJeda.hidden = ! berjalan;
                ikonPutar.hidden = berjalan;
                kendali.setAttribute('aria-label', berjalan ? 'Jeda video latar' : 'Putar video latar');
            };

            // Dibedakan dari jeda otomatis di bawah: pilihan pengguna untuk
            // menghentikan video tidak boleh dibatalkan diam-diam oleh sistem.
            let dijedaPengguna = false;

            kendali.addEventListener('click', () => {
                dijedaPengguna = ! video.paused;

                dijedaPengguna ? video.pause() : video.play().catch(() => {});
            });

            video.addEventListener('play', perbaruiKendali);
            video.addEventListener('pause', perbaruiKendali);

            kendali.dataset.aktif = '';
            perbaruiKendali();

            // Tab yang sedang tidak dilihat tidak perlu memutar apa pun.
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    video.pause();
                } else if (! dijedaPengguna) {
                    video.play().catch(() => {});
                }
            });
        })();
    </script>
@endif

</body>
</html>
