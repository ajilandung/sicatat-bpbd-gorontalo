<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk — Sicatat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-permukaan text-slate-700 antialiased">

<div class="flex min-h-full flex-col lg:flex-row">

    {{--
        ── Panel identitas ──────────────────────────────────────────────────
        Di layar besar panel ini mengisi separuh kiri layar. Di ponsel ia
        menyusut menjadi kop setinggi logo saja: fotonya tetap ada sebagai
        suasana, tetapi tidak mendorong form masuk turun sampai perlu digulir.
    --}}
    <aside class="relative flex shrink-0 flex-col justify-between overflow-hidden bg-navy-900 px-6 py-5
                  lg:w-1/2 lg:px-12 lg:py-14 xl:px-16">
        @if ($slides)
            {{--
                Slideshow foto dokumentasi kegiatan penyaluran, di lapisan
                paling bawah panel. Seluruh foto ditumpuk pada posisi yang sama
                lalu bergantian dengan pudar; hanya foto pertama yang diunduh
                sejak awal, sisanya menyusul saat dibutuhkan.
            --}}
            <div class="pointer-events-none absolute inset-0 bg-navy-950" aria-hidden="true">
                @foreach ($slides as $nomor => $slide)
                    <img src="{{ $slide }}" alt="" data-slide
                         @class(['slide-latar size-full object-cover', 'is-aktif' => $nomor === 0])
                         @if ($nomor > 0) loading="lazy" @endif>
                @endforeach
            </div>

            {{-- Tirai berarah; angkanya dijelaskan di `resources/css/app.css`. --}}
            <div class="tirai-slide pointer-events-none absolute inset-0"></div>

            {{--
                Indikator sekaligus kendali. Titiknya menunjukkan foto keberapa
                yang sedang tampil dan bisa ditekan untuk melompat; tombol di
                sebelahnya menghentikan pergantian otomatis — WCAG 2.2.2
                mewajibkan gerakan yang berjalan sendiri lebih dari lima detik
                dapat dihentikan pengguna. Keduanya baru ditampilkan oleh skrip
                di bawah halaman ketika slideshow-nya memang berjalan.
            --}}
            <div class="kendali-slide absolute bottom-4 right-6 items-center gap-4 lg:bottom-14 lg:right-12 xl:right-16"
                 data-kendali-slide>
                <div class="flex items-center gap-2" role="tablist" aria-label="Pilih foto">
                    @foreach ($slides as $nomor => $slide)
                        <button type="button" data-titik role="tab"
                                aria-label="Foto {{ $nomor + 1 }}" aria-selected="{{ $nomor === 0 ? 'true' : 'false' }}"
                                @class(['titik-slide size-2.5 rounded-full transition-colors focus-visible:outline-none
                                         focus-visible:ring-2 focus-visible:ring-air-400 focus-visible:ring-offset-2
                                         focus-visible:ring-offset-navy-950', 'is-aktif' => $nomor === 0])></button>
                    @endforeach
                </div>

                <button type="button" data-jeda-slide aria-label="Jeda pergantian foto"
                        class="flex size-9 items-center justify-center rounded-full border border-white/15
                               bg-navy-950/60 text-navy-100 backdrop-blur-sm transition-colors
                               hover:border-white/30 hover:bg-navy-950/85 hover:text-white
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500
                               focus-visible:ring-offset-2 focus-visible:ring-offset-navy-950 lg:size-11">
                    <x-ikon nama="jeda" class="size-[18px]" data-ikon-jeda/>
                    <x-ikon nama="putar" class="size-[18px]" data-ikon-putar hidden/>
                </button>
            </div>
        @else
            {{--
                Ornamen pengganti, hanya saat foto dokumentasi tidak ada. Ketika
                fotonya ada, gambar kegiatan yang sesungguhnya sudah mengisi
                panel ini — cahaya dan garis air di bawah justru menumpuk di
                atasnya tanpa menambah makna, jadi keduanya ditiadakan.
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

        {{-- Pita biru pembatas panel: di bawah pada ponsel, di tepi kanan pada layar besar. --}}
        <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-air-500 lg:left-auto lg:top-0 lg:h-auto lg:w-1.5"></div>

        <div class="relative flex items-center gap-3.5">
            <x-ui.logo ukuran="size-10 lg:size-12"/>
            <div>
                <p class="font-semibold tracking-tight text-white lg:text-lg">Sicatat</p>
                <p class="text-xs text-navy-300 lg:text-sm">BPBD Provinsi Gorontalo</p>
            </div>
        </div>

        <div class="relative hidden max-w-xl lg:block">
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

        <p class="relative hidden text-xs leading-relaxed text-navy-300 lg:block">
            Sistem internal BPBD Provinsi Gorontalo.<br>
            Akun dibuat oleh administrator, bukan melalui pendaftaran mandiri.
        </p>
    </aside>

    {{-- ── Form masuk ── --}}
    <main class="flex flex-1 flex-col bg-white">
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

@if (count($slides) > 1)
    <script>
        (() => {
            const foto = [...document.querySelectorAll('[data-slide]')];
            const titik = [...document.querySelectorAll('[data-titik]')];
            const kendali = document.querySelector('[data-kendali-slide]');
            const jeda = document.querySelector('[data-jeda-slide]');

            if (foto.length < 2 || ! kendali || ! jeda) return;

            const JEDA_ANTAR_FOTO = 6000;

            let indeks = 0;
            let jam = null;

            // Dibedakan dari jeda otomatis saat tab ditinggalkan: pilihan pengguna
            // untuk menghentikan slideshow tidak boleh dibatalkan diam-diam.
            let dijedaPengguna = false;

            const tampilkan = (nomor) => {
                indeks = (nomor + foto.length) % foto.length;

                foto.forEach((gambar, n) => gambar.classList.toggle('is-aktif', n === indeks));
                titik.forEach((tombol, n) => {
                    tombol.classList.toggle('is-aktif', n === indeks);
                    tombol.setAttribute('aria-selected', n === indeks ? 'true' : 'false');
                });
            };

            const perbaruiTombol = () => {
                jeda.querySelector('[data-ikon-jeda]').hidden = ! jam;
                jeda.querySelector('[data-ikon-putar]').hidden = !! jam;
                jeda.setAttribute('aria-label', jam ? 'Jeda pergantian foto' : 'Lanjutkan pergantian foto');
            };

            const berhenti = () => {
                clearInterval(jam);
                jam = null;
                perbaruiTombol();
            };

            const berjalan = () => {
                clearInterval(jam);
                jam = setInterval(() => tampilkan(indeks + 1), JEDA_ANTAR_FOTO);
                perbaruiTombol();
            };

            // Menekan titik berarti pengguna memilih sendiri fotonya. Hitungan
            // diulang dari nol supaya foto pilihannya tidak langsung tergeser oleh
            // sisa hitungan yang sedang berjalan — kecuali slideshow memang sedang
            // dijeda, yang berarti pengguna ingin diam di satu foto.
            titik.forEach((tombol, n) => tombol.addEventListener('click', () => {
                tampilkan(n);

                if (! dijedaPengguna) berjalan();
            }));

            jeda.addEventListener('click', () => {
                dijedaPengguna = !! jam;

                dijedaPengguna ? berhenti() : berjalan();
            });

            // Tab yang sedang tidak dilihat tidak perlu mengganti apa pun.
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    clearInterval(jam);
                    jam = null;
                } else if (! dijedaPengguna) {
                    berjalan();
                }
            });

            kendali.dataset.aktif = '';
            tampilkan(0);

            // Pengguna yang meminta animasi dikurangi tetap mendapat seluruh foto
            // lewat titik indikator, hanya saja tidak ada yang berganti sendiri.
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                dijedaPengguna = true;
                berhenti();
            } else {
                berjalan();
            }
        })();
    </script>
@endif

</body>
</html>
