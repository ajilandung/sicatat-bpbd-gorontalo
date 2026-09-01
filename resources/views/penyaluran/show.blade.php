@extends('layouts.app')

@section('judul', 'Detail Penyaluran')

@section('konten')

    <div class="mx-auto max-w-3xl space-y-6">
        <x-ui.kepala-halaman
            judul="Detail Penyaluran"
            :kembali="route('penyaluran.index')"
            kembali-label="Kembali ke riwayat penyaluran"/>

        @if ($penyaluran->trashed())
            <x-ui.notifikasi jenis="peringatan" judul="Data ini sudah dihapus">
                Data tidak lagi ikut dihitung pada rekap maupun laporan. Admin dapat memulihkannya dari halaman
                Data Terhapus.
            </x-ui.notifikasi>
        @endif

        <x-ui.kartu padat>
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="flex items-center gap-2 text-sm text-slate-500">
                            <x-ikon nama="kalender" class="size-4"/>
                            Tanggal kegiatan
                        </p>

                        <h2 class="mt-1 text-lg font-semibold text-navy-900">
                            {{ $penyaluran->tanggal_penyaluran?->translatedFormat('d F Y') }}
                        </h2>

                        <p class="mt-1 text-xs text-slate-400">
                            Dimasukkan ke sistem {{ $penyaluran->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                            oleh {{ $penyaluran->user?->name ?? '—' }}
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-sm text-slate-500">Air tersalur</p>
                        <p class="text-2xl font-semibold tracking-tight text-navy-900">
                            {{ number_format($penyaluran->volume_liter, 0, ',', '.') }}
                            <span class="text-base font-normal text-slate-500">liter</span>
                        </p>
                    </div>
                </div>

                @if ($penyaluran->angkaGabungan())
                    <div class="mt-4">
                        <x-ui.notifikasi jenis="peringatan">
                            <span class="font-semibold">Angka gabungan.</span>
                            Jumlah KK, jiwa, dan volume air di halaman ini berlaku untuk seluruh
                            {{ $penyaluran->desas->count() }} desa penerima, bukan per desa. Bila dibagi rata,
                            setiap desa menerima sekitar
                            {{ number_format($penyaluran->volumePerDesa(), 0, ',', '.') }} liter.
                        </x-ui.notifikasi>
                    </div>
                @endif
            </div>

            <dl class="divide-y divide-slate-100 text-sm">
                <div class="grid gap-1 px-5 py-3.5 sm:grid-cols-3 sm:px-6">
                    <dt class="text-slate-500">Desa/kelurahan penerima</dt>
                    <dd class="sm:col-span-2">
                        <ul class="space-y-1.5">
                            @foreach ($penyaluran->desas as $desa)
                                <li class="font-medium text-navy-900">
                                    {{ $desa->namaLengkap() }}
                                    <span class="block text-xs font-normal text-slate-500">
                                        Kec. {{ $desa->kecamatan?->nama ?? '—' }},
                                        {{ $desa->kecamatan?->kabupaten?->namaLengkap() ?? '—' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </dd>
                </div>

                <div class="grid gap-1 px-5 py-3.5 sm:grid-cols-3 sm:px-6">
                    <dt class="text-slate-500">Instansi pelaksana</dt>
                    <dd class="sm:col-span-2">
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($penyaluran->instansis as $instansi)
                                <x-ui.lencana warna="biru">{{ $instansi->nama }}</x-ui.lencana>
                            @empty
                                <span class="text-slate-400">—</span>
                            @endforelse
                        </div>
                    </dd>
                </div>

                @foreach ([
                    'Jumlah KK terdampak' => $penyaluran->jumlah_kk !== null
                        ? number_format($penyaluran->jumlah_kk, 0, ',', '.').' KK'
                        : 'Tidak tercatat pada laporan',
                    'Jumlah jiwa terdampak' => $penyaluran->jumlah_jiwa !== null
                        ? number_format($penyaluran->jumlah_jiwa, 0, ',', '.').' jiwa'
                        : 'Tidak tercatat pada laporan',
                    'Keterangan' => $penyaluran->keterangan ?: '—',
                ] as $label => $nilai)
                    <div class="grid gap-1 px-5 py-3.5 sm:grid-cols-3 sm:px-6">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="font-medium text-navy-900 sm:col-span-2">{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>

            @if (! $penyaluran->trashed() && (auth()->user()->can('update', $penyaluran) || auth()->user()->can('delete', $penyaluran)))
                <x-slot:kaki>
                    @can('delete', $penyaluran)
                    <x-ui.konfirmasi
                        :aksi="route('penyaluran.destroy', $penyaluran)"
                        metode="DELETE"
                        label="Hapus"
                        varian="bahaya"
                        judul="Hapus data penyaluran ini?"
                        pesan="Data dipindahkan ke Data Terhapus dan tidak lagi ikut dihitung pada rekap maupun laporan. Bila ternyata keliru, data masih dapat dipulihkan dari sana."
                        label-konfirmasi="Ya, hapus"
                        varian-konfirmasi="bahaya"/>
                    @endcan

                    @can('update', $penyaluran)
                        <x-ui.tombol :href="route('penyaluran.edit', $penyaluran)">
                            <x-ikon nama="pensil" class="size-4"/>
                            Ubah Data
                        </x-ui.tombol>
                    @endcan
                </x-slot:kaki>
            @endif
        </x-ui.kartu>

        {{-- Dokumentasi kegiatan. Foto menempel pada kegiatan ini, bukan pada
             tanggalnya: tanggal dokumentasi selalu dibaca dari tanggal kegiatan
             di atas, sehingga admin tidak perlu mengisi tanggal lagi per foto
             dan foto ikut berpindah dengan sendirinya bila tanggal kegiatan
             dikoreksi belakangan. --}}
        <x-ui.kartu
            judul="Dokumentasi Kegiatan"
            :deskripsi="'Foto kegiatan tanggal '.$penyaluran->tanggal_penyaluran?->translatedFormat('d F Y').'. Foto boleh ditambahkan kapan saja setelah kegiatan tercatat, dan ikut tercetak pada lampiran laporan periode yang memuat tanggal ini.'">

            @can('kelolaFoto', $penyaluran)
                <x-slot:aksi>
                    <form method="POST" action="{{ route('penyaluran.foto.store', $penyaluran) }}"
                          enctype="multipart/form-data">
                        @csrf

                        {{-- Berkas langsung terkirim begitu dipilih: tidak ada
                             isian lain yang perlu diisi admin untuk sebuah foto. --}}
                        <label class="inline-flex h-9 cursor-pointer items-center justify-center gap-2 rounded-lg
                                      bg-air-700 px-3 text-sm font-semibold whitespace-nowrap text-white shadow-kartu
                                      transition-colors hover:bg-air-800
                                      focus-within:ring-2 focus-within:ring-air-500 focus-within:ring-offset-2">
                            <x-ikon nama="plus" class="size-4"/>
                            Tambah Foto

                            <input type="file" name="foto[]" multiple class="sr-only"
                                   accept="image/jpeg,image/png,image/webp"
                                   onchange="if (this.files.length) this.form.submit()">
                        </label>
                    </form>
                </x-slot:aksi>
            @endcan

            <x-ui.ringkasan-galat class="mb-5"/>

            @if ($penyaluran->fotos->isEmpty())
                <x-ui.kosong ikon="kamera" judul="Belum ada foto dokumentasi"
                             deskripsi="Foto lapangan untuk kegiatan ini belum diunggah. Satu kegiatan dapat memuat beberapa foto sekaligus."/>
            @else
                <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($penyaluran->fotos as $foto)
                        <li class="group relative">
                            <a href="{{ $foto->url() }}" target="_blank" rel="noopener"
                               class="block overflow-hidden rounded-lg border border-slate-200
                                      focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-air-500
                                      focus-visible:ring-offset-2">
                                <img src="{{ $foto->url() }}" loading="lazy"
                                     alt="Dokumentasi kegiatan {{ $penyaluran->tanggal_penyaluran?->translatedFormat('d F Y') }}, foto {{ $loop->iteration }}"
                                     class="aspect-[4/3] w-full bg-slate-100 object-cover">
                            </a>

                            @can('kelolaFoto', $penyaluran)
                                <div class="absolute right-1.5 top-1.5">
                                    <x-ui.konfirmasi
                                        :aksi="route('penyaluran.foto.destroy', $foto)"
                                        metode="DELETE"
                                        ikon="sampah"
                                        label="Hapus foto ini"
                                        varian="bahaya"
                                        judul="Hapus foto dokumentasi ini?"
                                        pesan="Foto dihapus permanen dan tidak lagi muncul pada lampiran laporan. Data kegiatan penyalurannya sendiri tidak ikut terhapus."
                                        label-konfirmasi="Ya, hapus foto"
                                        varian-konfirmasi="bahaya"/>
                                </div>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.kartu>

        {{-- Riwayat perubahan (§9.3) — hanya untuk pihak yang juga berwenang
             mengoreksi datanya: admin atas seluruh kegiatan, petugas atas
             kegiatan yang ia input sendiri. --}}
        @can('lihatRiwayat', $penyaluran)
            <x-ui.kartu judul="Riwayat Perubahan"
                        deskripsi="Data penyaluran boleh dikoreksi kapan saja karena laporan lapangan kerap baru lengkap beberapa hari kemudian. Setiap koreksi tercatat di sini beserta nilai sebelum dan sesudahnya.">
                @forelse ($riwayats as $riwayat)
                    <div class="flex gap-4 {{ ! $loop->last ? 'pb-5' : '' }}">
                        <div class="flex flex-col items-center">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                <x-ikon nama="jam" class="size-4"/>
                            </span>

                            @unless ($loop->last)
                                <span class="mt-1 w-px flex-1 bg-slate-200"></span>
                            @endunless
                        </div>

                        <div class="min-w-0 flex-1 pt-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.lencana :warna="$riwayat->warnaAksi()">{{ $riwayat->labelAksi() }}</x-ui.lencana>

                                <span class="text-sm text-slate-500">
                                    oleh {{ $riwayat->user?->name ?? 'pengguna terhapus' }}
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $riwayat->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                            </p>

                            @php $perubahan = $riwayat->daftarPerubahan(); @endphp

                            @if ($perubahan !== [])
                                <ul class="mt-3 space-y-2">
                                    @foreach ($perubahan as $baris)
                                        <li class="rounded-lg bg-slate-50 px-3 py-2 text-sm">
                                            <p class="text-xs font-medium text-slate-500">{{ $baris['label'] }}</p>
                                            <p class="mt-0.5 text-navy-900">
                                                <span class="text-slate-400 line-through">{{ $baris['dari'] }}</span>
                                                <span class="mx-1 text-slate-400">→</span>
                                                <span class="font-medium">{{ $baris['ke'] }}</span>
                                            </p>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-ui.kosong ikon="jam" judul="Belum ada riwayat perubahan"
                                 deskripsi="Setiap perubahan pada data ini akan tercatat di sini."/>
                @endforelse
            </x-ui.kartu>
        @endcan
    </div>

@endsection
